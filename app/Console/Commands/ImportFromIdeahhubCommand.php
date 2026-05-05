<?php

namespace App\Console\Commands;

use App\Models\Language;
use App\Models\ListingCategory;
use App\Models\Listing\Listing;
use App\Models\Listing\ListingContent;
use App\Models\Listing\ListingImage;
use App\Models\Listing\ListingProduct;
use App\Models\Listing\ListingProductContent;
use App\Models\Listing\ListingProductImage;
use App\Models\Listing\ListingSocialMedia;
use App\Support\ListingCategoryIcon;
use App\Models\Vendor;
use App\Models\VendorInfo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class ImportFromIdeahhubCommand extends Command
{
    protected $signature = 'ideah:import-from-ideahhub
                            {--dry-run : Show what would be imported without writing}
                            {--media-source= : Absolute path to legacy site public folder (e.g. ideahhub/public)}';

    protected $description = 'Import listings, vendors, and categories from legacy ideahhub MySQL into this app.';

    private const SOCIAL_ICONS = [
        'facebook' => 'fab fa-facebook-square iconpicker-component',
        'x' => 'fab fa-twitter iconpicker-component',
        'linkedin' => 'fab fa-linkedin-in iconpicker-component',
        'instagram' => 'fab fa-instagram iconpicker-component',
        'youtube' => 'fab fa-youtube iconpicker-component',
    ];

    /** @var array<int, int> legacy business_category_id -> listing_category id (default language) */
    private array $categoryMap = [];

    /** @var array<int, int> legacy user_id -> vendor id */
    private array $userVendorMap = [];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $mediaSource = $this->option('media-source');
        $mediaSource = is_string($mediaSource) && $mediaSource !== '' ? rtrim($mediaSource, '/\\') : null;

        if ($mediaSource !== null && !is_dir($mediaSource)) {
            $this->error('media-source is not a directory: ' . $mediaSource);

            return self::FAILURE;
        }

        try {
            DB::connection('ideahhub_legacy')->getPdo();
        } catch (Throwable $e) {
            $this->error('Cannot connect to ideahhub_legacy. Configure IDEAHUB_LEGACY_* in .env and config/database.php.');
            $this->line($e->getMessage());

            return self::FAILURE;
        }

        $legacy = DB::connection('ideahhub_legacy');

        $defaultLang = Language::query()->where('is_default', 1)->orderBy('id')->first()
            ?? Language::query()->orderBy('id')->first();

        if ($defaultLang === null) {
            $this->error('No languages found in target database. Seed languages first.');

            return self::FAILURE;
        }

        $languages = Language::query()->orderBy('id')->get();
        $listingsHasFeatured = Schema::hasColumn('listings', 'is_featured');
        $vendorsHasSocial = Schema::hasColumn('vendors', 'facebook')
            && Schema::hasColumn('vendors', 'twitter')
            && Schema::hasColumn('vendors', 'linkedin');

        $this->info('Default language: ' . $defaultLang->name . ' (id ' . $defaultLang->id . ')');
        if ($dryRun) {
            $this->warn('Dry run — no writes.');
            $this->dryRunReport($legacy);

            return self::SUCCESS;
        }

        $import = function () use ($legacy, $defaultLang, $languages, $mediaSource, $listingsHasFeatured, $vendorsHasSocial): void {
            $this->mapCategories($legacy, $defaultLang, $languages, $mediaSource);
            $this->mapVendors($legacy, $languages, $mediaSource);
            $this->importBusinesses(
                $legacy,
                $languages,
                $defaultLang,
                $mediaSource,
                $listingsHasFeatured,
                $vendorsHasSocial
            );
        };

        try {
            DB::connection()->transaction($import);
        } catch (Throwable $e) {
            $this->error($e->getMessage());
            if ($this->output->isVerbose()) {
                $this->line($e->getTraceAsString());
            }

            return self::FAILURE;
        }

        $this->info('Import transaction committed.');
        $this->call('ideah:ensure-listing-vendor-memberships');
        $this->call('ideah:repair-imported-listings');
        $this->info('Done.');

        return self::SUCCESS;
    }

    private function dryRunReport($legacy): void
    {
        $cats = $legacy->table('business_categories')->where('status', 1)->count();
        $biz = $legacy->table('businesses')->whereNull('deleted_at')->count();
        $users = $legacy->table('businesses')->whereNull('deleted_at')->whereNotNull('user_id')->distinct()->count('user_id');
        $products = $legacy->table('business_products')
            ->join('businesses', 'business_products.business_id', '=', 'businesses.id')
            ->whereNull('businesses.deleted_at')
            ->count();

        $this->table(
            ['Metric', 'Count'],
            [
                ['Active business_categories', (string) $cats],
                ['Non-deleted businesses', (string) $biz],
                ['Distinct user_id on businesses', (string) $users],
                ['Business products (for those businesses)', (string) $products],
            ]
        );
    }

    private function mapCategories($legacy, Language $defaultLang, $languages, ?string $mediaSource): void
    {
        $usedCategoryIds = $legacy->table('businesses')
            ->whereNull('deleted_at')
            ->whereNotNull('business_category_id')
            ->distinct()
            ->pluck('business_category_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $rows = $legacy->table('business_categories')
            ->where(function ($q) use ($usedCategoryIds) {
                $q->where('status', 1);
                if ($usedCategoryIds !== []) {
                    $q->orWhereIn('id', $usedCategoryIds);
                }
            })
            ->orderBy('id')
            ->get();
        $destDir = 'assets/img/listing/category';

        foreach ($rows as $row) {
            $slug = (string) $row->category_slug;
            $existing = ListingCategory::query()
                ->where('language_id', $defaultLang->id)
                ->where('slug', $slug)
                ->first();

            if ($existing !== null) {
                $this->categoryMap[(int) $row->id] = (int) $existing->id;
                foreach ($languages as $lang) {
                    if ((int) $lang->id === (int) $defaultLang->id) {
                        continue;
                    }
                    $this->ensureListingCategoryForLanguage($lang, $slug, (string) $row->category_name);
                }

                continue;
            }

            $maxSerial = (int) (ListingCategory::query()->where('language_id', $defaultLang->id)->max('serial_number') ?? 0);
            $serial = $maxSerial + 1;

            $mobileImage = null;
            if (!empty($row->image) && $mediaSource !== null) {
                $mobileImage = $this->copyPublicFile($mediaSource, [
                    'admin/uploads/businessCategory/' . ltrim((string) $row->image, '/'),
                ], $destDir);
            }

            $cat = ListingCategory::query()->create([
                'language_id' => $defaultLang->id,
                'name' => (string) $row->category_name,
                'slug' => $slug,
                'serial_number' => $serial,
                'status' => 1,
                'icon' => ListingCategoryIcon::forSlug($slug),
                'mobile_image' => $mobileImage,
            ]);
            $this->categoryMap[(int) $row->id] = (int) $cat->id;

            foreach ($languages as $lang) {
                if ((int) $lang->id === (int) $defaultLang->id) {
                    continue;
                }
                $this->ensureListingCategoryForLanguage($lang, $slug, (string) $row->category_name);
            }
        }
    }

    private function ensureListingCategoryForLanguage(Language $lang, string $slug, string $name): int
    {
        $found = ListingCategory::query()->where('language_id', $lang->id)->where('slug', $slug)->first();
        if ($found !== null) {
            return (int) $found->id;
        }
        $maxSerial = (int) (ListingCategory::query()->where('language_id', $lang->id)->max('serial_number') ?? 0);

        $cat = ListingCategory::query()->create([
            'language_id' => $lang->id,
            'name' => $name,
            'slug' => $slug,
            'serial_number' => $maxSerial + 1,
            'status' => 1,
            'icon' => ListingCategoryIcon::forSlug($slug),
            'mobile_image' => null,
        ]);

        return (int) $cat->id;
    }

    private function categoryIdForLanguage(int $legacyBusinessCategoryId, Language $lang, Language $defaultLang): ?int
    {
        if (!isset($this->categoryMap[$legacyBusinessCategoryId])) {
            return null;
        }
        $defaultId = $this->categoryMap[$legacyBusinessCategoryId];
        if ((int) $lang->id === (int) $defaultLang->id) {
            return $defaultId;
        }
        $defaultRow = ListingCategory::query()->find($defaultId);
        if ($defaultRow === null) {
            return null;
        }

        $other = ListingCategory::query()
            ->where('language_id', $lang->id)
            ->where('slug', $defaultRow->slug)
            ->first();

        return $other !== null ? (int) $other->id : null;
    }

    private function mapVendors($legacy, $languages, ?string $mediaSource): void
    {
        $userIds = $legacy->table('businesses')
            ->whereNull('deleted_at')
            ->whereNotNull('user_id')
            ->distinct()
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $vendorProfileDir = 'assets/img/vendor/profile';

        foreach ($userIds as $uid) {
            $user = $legacy->table('users')->where('id', $uid)->first();
            if ($user === null) {
                $this->warn('Skipping missing legacy user id ' . $uid);

                continue;
            }
            $email = strtolower(trim((string) $user->email));
            $existing = Vendor::query()->where('email', $email)->first();
            if ($existing !== null) {
                $this->userVendorMap[$uid] = (int) $existing->id;

                continue;
            }

            $username = $this->uniqueVendorUsername($this->usernameBaseFromUser($user));
            $photo = null;
            if (!empty($user->image) && $mediaSource !== null && !str_starts_with((string) $user->image, 'http')) {
                $photo = $this->copyPublicFile($mediaSource, [
                    ltrim((string) $user->image, '/'),
                    'admin/uploads/user/' . basename((string) $user->image),
                ], $vendorProfileDir);
            }

            $vendor = Vendor::query()->create([
                'email' => $email,
                'to_mail' => $email,
                'phone' => (string) ($user->mobile_no ?? ''),
                'username' => $username,
                'password' => (string) $user->password,
                'status' => (int) $user->status === 1 ? 1 : 0,
                'photo' => $photo,
                'avg_rating' => 0,
            ]);

            $this->userVendorMap[$uid] = (int) $vendor->id;

            foreach ($languages as $lang) {
                VendorInfo::query()->create([
                    'vendor_id' => $vendor->id,
                    'language_id' => $lang->id,
                    'name' => (string) $user->name,
                ]);
            }
        }
    }

    private function usernameBaseFromUser(object $user): string
    {
        $name = trim((string) $user->name);
        if ($name !== '') {
            $base = createSlug($name);
            $base = preg_replace('/[^a-z0-9\-]/', '', $base) ?? 'vendor';

            return $base !== '' ? $base : 'vendor';
        }
        $email = (string) $user->email;
        $local = strstr($email, '@', true) ?: $email;

        return preg_replace('/[^a-z0-9]/', '', strtolower($local)) ?: 'vendor';
    }

    private function uniqueVendorUsername(string $base): string
    {
        $slug = mb_substr($base, 0, 190);
        $candidate = $slug;
        $n = 1;
        while (Vendor::query()->where('username', $candidate)->exists()) {
            $candidate = $slug . '-' . $n;
            $n++;
        }

        return $candidate;
    }

    private function uniqueListingSlug(int $languageId, string $base, ?int $ignoreContentId = null): string
    {
        $slug = mb_substr(createSlug($base), 0, 240);
        if ($slug === '') {
            $slug = 'listing';
        }
        $candidate = $slug;
        $n = 1;
        while ($this->listingSlugTaken($languageId, $candidate, $ignoreContentId)) {
            $candidate = $slug . '-' . $n;
            $n++;
        }

        return $candidate;
    }

    private function listingSlugTaken(int $languageId, string $slug, ?int $ignoreContentId): bool
    {
        $q = ListingContent::query()->where('language_id', $languageId)->where('slug', $slug);
        if ($ignoreContentId !== null) {
            $q->where('id', '!=', $ignoreContentId);
        }

        return $q->exists();
    }

    private function uniqueProductSlug(int $languageId, int $listingId, string $base): string
    {
        $slug = mb_substr(createSlug($base), 0, 240);
        if ($slug === '') {
            $slug = 'product';
        }
        $candidate = $slug;
        $n = 1;
        while (
            ListingProductContent::query()
                ->where('language_id', $languageId)
                ->where('listing_id', $listingId)
                ->where('slug', $candidate)
                ->exists()
        ) {
            $candidate = $slug . '-' . $n;
            $n++;
        }

        return $candidate;
    }

    private function importBusinesses(
        $legacy,
        $languages,
        Language $defaultLang,
        ?string $mediaSource,
        bool $listingsHasFeatured,
        bool $vendorsHasSocial
    ): void {
        $businesses = $legacy->table('businesses')->whereNull('deleted_at')->orderBy('id')->get();

        foreach ($businesses as $biz) {
            $bizId = (int) $biz->id;
            $contact = $legacy->table('business_contact_details')->where('business_id', $bizId)->first();

            $vendorId = $this->resolveVendorForBusiness($legacy, $biz, $languages, $vendorsHasSocial);
            if ($vendorId === null) {
                continue;
            }

            $lat = null;
            $lng = null;
            if ($contact !== null && !empty($contact->google_map_location)) {
                [$lat, $lng] = $this->parseLatLngFromEmbed((string) $contact->google_map_location);
            }

            $visibility = ((string) $biz->business_approval_status === 'Approved' && (int) $biz->status === 1) ? 1 : 0;

            $mail = $contact !== null ? (string) $contact->primary_email_id : '';
            $phone = $contact !== null ? (string) $contact->primary_contact_no : '';

            $featureImage = null;
            if (!empty($biz->business_logo) && $mediaSource !== null) {
                $featureImage = $this->copyPublicFile($mediaSource, [
                    'admin/uploads/business/' . ltrim((string) $biz->business_logo, '/'),
                    'admin/uploads/business/' . basename((string) $biz->business_logo),
                ], 'assets/img/listing');
            }

            $offered = $biz->offered_services ?? null;
            $featuresStr = null;
            if ($offered !== null) {
                $featuresStr = is_string($offered) ? $offered : json_encode($offered, JSON_UNESCAPED_UNICODE);
            }

            $metaKeywords = $biz->meta_keywords ?? null;
            $metaKeywordStr = null;
            if ($metaKeywords !== null) {
                $metaKeywordStr = is_string($metaKeywords) ? $metaKeywords : json_encode($metaKeywords, JSON_UNESCAPED_UNICODE);
            }

            $listingData = [
                'feature_image' => $featureImage,
                'vendor_id' => $vendorId,
                'mail' => $mail,
                'phone' => $phone,
                'average_rating' => (string) ((int) ($biz->business_rating ?? 0)),
                'latitude' => $lat !== null ? (string) $lat : null,
                'longitude' => $lng !== null ? (string) $lng : null,
                'video_url' => null,
                'video_background_image' => null,
                'status' => (int) $biz->status === 1 ? 1 : 0,
                'visibility' => $visibility,
                'min_price' => null,
                'max_price' => null,
            ];

            $listing = new Listing();
            $listing->forceFill($listingData);
            if ($listingsHasFeatured) {
                $listing->setAttribute('is_featured', (int) ($biz->is_featured ?? 0) === 1 ? 1 : 0);
            }
            $listing->save();

            foreach ($languages as $lang) {
                $catId = $this->categoryIdForLanguage((int) $biz->business_category_id, $lang, $defaultLang);
                if ($catId === null) {
                    $catId = ListingCategory::query()->where('language_id', $lang->id)->orderBy('id')->value('id');
                }

                $title = (string) $biz->business_name;
                $slug = $this->uniqueListingSlug((int) $lang->id, $title . '-' . $bizId);

                ListingContent::query()->create([
                    'language_id' => $lang->id,
                    'listing_id' => $listing->id,
                    'category_id' => $catId,
                    'country_id' => null,
                    'state_id' => null,
                    'city_id' => null,
                    'title' => $title,
                    'slug' => $slug,
                    'description' => (string) $biz->business_description,
                    'address' => $contact !== null ? (string) $contact->primary_contact_address : '',
                    'meta_keyword' => $metaKeywordStr,
                    'meta_description' => $biz->meta_description !== null ? (string) $biz->meta_description : null,
                    'summary' => null,
                    'features' => $featuresStr,
                    // Legacy ideahhub has no amenities; empty JSON avoids null issues on listing details.
                    'aminities' => '[]',
                ]);
            }

            if ($contact !== null) {
                $this->syncListingSocial($listing->id, $contact);
                if ($vendorsHasSocial) {
                    $this->fillVendorSocialFromContact((int) $vendorId, $contact);
                }
            }

            $galleryRows = $legacy->table('business_images')->where('business_id', $bizId)->orderBy('id')->get();
            foreach ($galleryRows as $g) {
                $imgName = null;
                if ($mediaSource !== null) {
                    $imgName = $this->copyPublicFile($mediaSource, [
                        'admin/uploads/businessImages/' . basename((string) $g->business_image),
                        'admin/uploads/business_images/' . basename((string) $g->business_image),
                        'admin/uploads/businessImages/' . ltrim((string) $g->business_image, '/'),
                    ], 'assets/img/listing-gallery');
                }
                if ($imgName !== null) {
                    ListingImage::query()->create([
                        'listing_id' => $listing->id,
                        'image' => $imgName,
                    ]);
                }
            }

            $products = $legacy->table('business_products')->where('business_id', $bizId)->orderBy('id')->get();
            foreach ($products as $prod) {
                $feat = null;
                if (!empty($prod->image) && $mediaSource !== null) {
                    $feat = $this->copyPublicFile($mediaSource, [
                        'admin/uploads/business/product/' . ltrim((string) $prod->image, '/'),
                        'admin/uploads/business/product/' . basename((string) $prod->image),
                    ], 'assets/img/listing/product');
                }

                $lp = ListingProduct::query()->create([
                    'listing_id' => $listing->id,
                    'vendor_id' => $vendorId,
                    'feature_image' => $feat,
                    'status' => (int) $prod->status === 1 ? '1' : '0',
                    'current_price' => (string) (float) ($prod->price_after_discount ?? $prod->price ?? 0),
                    'previous_price' => isset($prod->price) ? (string) (float) $prod->price : null,
                ]);

                foreach ($languages as $lang) {
                    $pSlug = $this->uniqueProductSlug((int) $lang->id, (int) $listing->id, (string) $prod->name);
                    $metaKw = $prod->meta_keywords ?? null;
                    $mk = $metaKw !== null ? (is_string($metaKw) ? $metaKw : json_encode($metaKw, JSON_UNESCAPED_UNICODE)) : null;

                    ListingProductContent::query()->create([
                        'language_id' => $lang->id,
                        'listing_id' => $listing->id,
                        'listing_product_id' => $lp->id,
                        'title' => (string) $prod->name,
                        'slug' => $pSlug,
                        'content' => (string) $prod->description,
                        'meta_keyword' => $mk,
                        'meta_description' => $prod->meta_description !== null ? (string) $prod->meta_description : null,
                    ]);
                }

                $pImages = $legacy->table('business_product_images')->where('business_product_id', (int) $prod->id)->get();
                foreach ($pImages as $pi) {
                    $pin = null;
                    if ($mediaSource !== null) {
                        $pin = $this->copyPublicFile($mediaSource, [
                            'admin/uploads/business/product/' . basename((string) $pi->product_image),
                        ], 'assets/img/listing/product-gallery');
                    }
                    if ($pin !== null) {
                        ListingProductImage::query()->create([
                            'listing_id' => $listing->id,
                            'listing_product_id' => $lp->id,
                            'image' => $pin,
                        ]);
                    }
                }
            }
        }
    }

    private function resolveVendorForBusiness(
        $legacy,
        object $biz,
        $languages,
        bool $vendorsHasSocial
    ): ?int {
        $bizId = (int) $biz->id;
        $contact = $legacy->table('business_contact_details')->where('business_id', $bizId)->first();

        if (!empty($biz->user_id)) {
            $uid = (int) $biz->user_id;
            if (isset($this->userVendorMap[$uid])) {
                return $this->userVendorMap[$uid];
            }

            $email = 'legacy-missing-user-' . $uid . '-' . $bizId . '@import.local';
            $this->warn('Legacy user id ' . $uid . ' missing for business ' . $bizId . '; creating placeholder vendor ' . $email);
            $existing = Vendor::query()->where('email', $email)->first();
            if ($existing !== null) {
                return (int) $existing->id;
            }

            $username = $this->uniqueVendorUsername('legacy-missing-user-' . $uid . '-' . $bizId);
            $vendor = Vendor::query()->create([
                'email' => $email,
                'to_mail' => $email,
                'phone' => $contact !== null ? (string) $contact->primary_contact_no : '',
                'username' => $username,
                'password' => bcrypt('imported-placeholder-missing-user-' . $uid . '-' . $bizId),
                'status' => 1,
                'photo' => null,
                'avg_rating' => 0,
            ]);

            foreach ($languages as $lang) {
                VendorInfo::query()->create([
                    'vendor_id' => $vendor->id,
                    'language_id' => $lang->id,
                    'name' => (string) $biz->business_name,
                ]);
            }

            if ($vendorsHasSocial && $contact !== null) {
                $this->fillVendorSocialFromContact((int) $vendor->id, $contact);
            }

            return (int) $vendor->id;
        }

        $email = 'legacy-business-' . $bizId . '@import.local';
        $existing = Vendor::query()->where('email', $email)->first();
        if ($existing !== null) {
            return (int) $existing->id;
        }

        $username = $this->uniqueVendorUsername('legacy-business-' . $bizId);
        $vendor = Vendor::query()->create([
            'email' => $email,
            'to_mail' => $email,
            'phone' => $contact !== null ? (string) $contact->primary_contact_no : '',
            'username' => $username,
            'password' => bcrypt('imported-placeholder-' . $bizId),
            'status' => 1,
            'photo' => null,
            'avg_rating' => 0,
        ]);

        foreach ($languages as $lang) {
            VendorInfo::query()->create([
                'vendor_id' => $vendor->id,
                'language_id' => $lang->id,
                'name' => (string) $biz->business_name,
            ]);
        }

        if ($vendorsHasSocial && $contact !== null) {
            $this->fillVendorSocialFromContact((int) $vendor->id, $contact);
        }

        return (int) $vendor->id;
    }

    private function syncListingSocial(int $listingId, object $contact): void
    {
        $pairs = [
            'facebook' => $contact->facebook_url ?? null,
            'x' => $contact->x_url ?? null,
            'linkedin' => $contact->linkedin_url ?? null,
            'instagram' => $contact->instagram_url ?? null,
            'youtube' => $contact->youtube_url ?? null,
        ];
        foreach ($pairs as $key => $url) {
            $url = $url !== null ? trim((string) $url) : '';
            if ($url === '') {
                continue;
            }
            $icon = self::SOCIAL_ICONS[$key] ?? 'fab fa-link';
            ListingSocialMedia::query()->create([
                'listing_id' => $listingId,
                'link' => $url,
                'icon' => $icon,
            ]);
        }
    }

    private function fillVendorSocialFromContact(int $vendorId, object $contact): void
    {
        $fb = isset($contact->facebook_url) ? trim((string) $contact->facebook_url) : '';
        $tw = isset($contact->x_url) ? trim((string) $contact->x_url) : '';
        $li = isset($contact->linkedin_url) ? trim((string) $contact->linkedin_url) : '';

        $data = [];
        if ($fb !== '') {
            $data['facebook'] = $fb;
        }
        if ($tw !== '') {
            $data['twitter'] = $tw;
        }
        if ($li !== '') {
            $data['linkedin'] = $li;
        }
        if ($data !== []) {
            Vendor::query()->where('id', $vendorId)->update($data);
        }
    }

    /**
     * @return array{0: ?float, 1: ?float} latitude, longitude
     */
    private function parseLatLngFromEmbed(string $html): array
    {
        if (preg_match('/!2d([\d.+-]+)!3d([\d.+-]+)/', $html, $m)) {
            return [(float) $m[2], (float) $m[1]];
        }
        if (preg_match('/!3d([\d.+-]+)!2d([\d.+-]+)/', $html, $m)) {
            return [(float) $m[1], (float) $m[2]];
        }
        if (preg_match('/@(-?\d+\.?\d*),(-?\d+\.?\d*),\d+z/', $html, $m)) {
            return [(float) $m[1], (float) $m[2]];
        }

        return [null, null];
    }

    /**
     * Copy a file from the legacy public tree into this app's public/ directory.
     *
     * @param  string[]  $sourceRelativePaths
     */
    private function copyPublicFile(?string $mediaSource, array $sourceRelativePaths, string $destinationPublicDir): ?string
    {
        if ($mediaSource === null || $sourceRelativePaths === []) {
            return null;
        }

        $destAbs = public_path($destinationPublicDir);
        if (!is_dir($destAbs)) {
            @mkdir($destAbs, 0775, true);
        }

        foreach ($sourceRelativePaths as $rel) {
            $rel = ltrim(str_replace('\\', '/', (string) $rel), '/');
            if ($rel === '') {
                continue;
            }
            $candidates = [
                rtrim($mediaSource, '/\\') . '/' . $rel,
                rtrim($mediaSource, '/\\') . '/public/' . $rel,
            ];
            foreach ($candidates as $abs) {
                if (!is_file($abs) || !is_readable($abs)) {
                    continue;
                }
                $basename = basename($abs);
                $destFile = $destAbs . DIRECTORY_SEPARATOR . $basename;
                $i = 1;
                $stem = pathinfo($basename, PATHINFO_FILENAME);
                $ext = pathinfo($basename, PATHINFO_EXTENSION);
                while (file_exists($destFile)) {
                    $suffix = $ext !== '' ? $stem . '-' . $i . '.' . $ext : $stem . '-' . $i;
                    $destFile = $destAbs . DIRECTORY_SEPARATOR . $suffix;
                    $i++;
                }
                if (!@copy($abs, $destFile)) {
                    return null;
                }

                return basename($destFile);
            }
        }

        return null;
    }
}
