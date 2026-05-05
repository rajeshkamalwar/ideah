<?php

namespace App\Console\Commands;

use App\Models\BusinessHour;
use App\Models\Language;
use App\Models\Listing\Listing;
use App\Models\Listing\ListingContent;
use App\Models\Listing\ListingImage;
use App\Models\ListingCategory;
use App\Models\Vendor;
use App\Models\VendorInfo;
use App\Support\ListingGeocoder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class CreateListingsForVendorsWithoutCommand extends Command
{
    protected $signature = 'vendors:create-listings
                            {--dry-run : Show vendors that would get a listing; no writes}
                            {--vendor-id= : Only process this vendor ID}
                            {--published : Set status=1 and visibility=1 (default is draft: status=0, visibility=0)}';

    protected $description = 'Create one listing per vendor that has zero listings, using vendor email/phone and vendor_infos (name, city, details). Safe defaults: placeholder feature image (GD), Chandigarh lat/lng, first category per language, draft visibility unless --published.';

    private const DEFAULT_LAT = '30.7333';

    private const DEFAULT_LNG = '76.7794';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $onlyVendorId = $this->option('vendor-id');
        $onlyVendorId = $onlyVendorId !== null && $onlyVendorId !== '' ? (int) $onlyVendorId : null;
        $published = (bool) $this->option('published');

        $languages = Language::query()->orderBy('is_default', 'desc')->orderBy('id')->get();
        if ($languages->isEmpty()) {
            $this->error('No languages configured.');

            return self::FAILURE;
        }

        $defaultLang = $languages->firstWhere('is_default', 1) ?? $languages->first();

        $q = Vendor::query()
            ->where('id', '>', 0)
            ->whereDoesntHave('listings');

        if ($onlyVendorId !== null) {
            $q->where('id', $onlyVendorId);
        }

        $vendors = $q->orderBy('id')->get();
        if ($vendors->isEmpty()) {
            $this->info('No vendors without listings found.');

            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->warn('Dry run — no database writes.');
        }

        $created = 0;
        $failed = 0;

        foreach ($vendors as $vendor) {
            $defaultInfo = VendorInfo::query()
                ->where('vendor_id', $vendor->id)
                ->where('language_id', $defaultLang->id)
                ->first();

            if ($defaultInfo === null) {
                $this->line("SKIP vendor #{$vendor->id} ({$vendor->username}): no vendor_info for default language.");
                $failed++;

                continue;
            }

            $titleBase = $this->listingTitleFromVendorInfo($defaultInfo);
            $descriptionBase = $this->listingDescriptionFromDetails((string) $defaultInfo->details, (string) $defaultInfo->name);
            $addressLine = trim((string) $defaultInfo->city) !== ''
                ? (string) $defaultInfo->city
                : 'Location to be confirmed';

            $websiteUrl = $this->websiteFromDetails((string) $defaultInfo->details);

            if ($dryRun) {
                $this->line("Would create listing for vendor #{$vendor->id} ({$vendor->email}): «{$titleBase}»");
                $created++;

                continue;
            }

            try {
                DB::transaction(function () use (
                    $vendor,
                    $languages,
                    $defaultLang,
                    $titleBase,
                    $descriptionBase,
                    $addressLine,
                    $websiteUrl,
                    $published
                ) {
                    $featureImage = $this->makePlaceholderFeatureImage();
                    $listingAttrs = [
                        'feature_image' => $featureImage,
                        'vendor_id' => $vendor->id,
                        'mail' => $vendor->email,
                        'phone' => (string) ($vendor->phone ?? ''),
                        'average_rating' => '0',
                        'latitude' => self::DEFAULT_LAT,
                        'longitude' => self::DEFAULT_LNG,
                        'video_url' => null,
                        'video_background_image' => null,
                        'status' => $published ? 1 : 0,
                        'visibility' => $published ? 1 : 0,
                        'min_price' => null,
                        'max_price' => null,
                    ];
                    if (Schema::hasColumn('listings', 'website_url')) {
                        $listingAttrs['website_url'] = $websiteUrl;
                    }
                    if (Schema::hasColumn('listings', 'package_id')) {
                        $listingAttrs['package_id'] = null;
                    }

                    $listing = Listing::query()->create($listingAttrs);

                    if ($featureImage !== null) {
                        ListingImage::query()->create([
                            'listing_id' => $listing->id,
                            'image' => $featureImage,
                        ]);
                    }

                    foreach ($languages as $lang) {
                        $info = VendorInfo::query()
                            ->where('vendor_id', $vendor->id)
                            ->where('language_id', $lang->id)
                            ->first();
                        $title = $titleBase;
                        $description = $info !== null
                            ? $this->listingDescriptionFromDetails((string) $info->details, (string) $info->name)
                            : $descriptionBase;
                        $address = $info !== null && trim((string) $info->city) !== ''
                            ? (string) $info->city
                            : $addressLine;

                        $categoryId = ListingCategory::query()
                            ->where('language_id', $lang->id)
                            ->where('status', 1)
                            ->orderBy('serial_number')
                            ->orderBy('id')
                            ->value('id');

                        if ($categoryId === null) {
                            throw new \RuntimeException("No listing category for language_id={$lang->id}");
                        }

                        $slug = $this->uniqueListingSlug((int) $lang->id, $title . '-' . $vendor->id . '-' . $listing->id);

                        ListingContent::query()->create([
                            'language_id' => $lang->id,
                            'listing_id' => $listing->id,
                            'category_id' => $categoryId,
                            'country_id' => null,
                            'state_id' => null,
                            'city_id' => null,
                            'title' => Str::limit($title, 255, ''),
                            'slug' => $slug,
                            'description' => $description,
                            'address' => Str::limit($address, 500, ''),
                            'meta_keyword' => null,
                            'meta_description' => Str::limit(strip_tags($description), 300, ''),
                            'summary' => Str::limit(strip_tags($description), 500, ''),
                            'features' => null,
                            'aminities' => '[]',
                        ]);
                    }

                    $days = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
                    foreach ($days as $day) {
                        BusinessHour::query()->create([
                            'listing_id' => $listing->id,
                            'day' => $day,
                            'start_time' => '10:00 AM',
                            'end_time' => '07:00 PM',
                            'holiday' => 1,
                        ]);
                    }

                    ListingGeocoder::syncFromDefaultLanguageAddress($listing);
                });

                $this->line("Created listing for vendor #{$vendor->id} ({$vendor->email}).");
                $created++;
            } catch (Throwable $e) {
                $this->error("Vendor #{$vendor->id}: " . $e->getMessage());
                $failed++;
            }
        }

        $this->newLine();
        $this->info(($dryRun ? 'Would create' : 'Created') . ": {$created}. Skipped/failed: {$failed}.");

        if (! $dryRun && $created > 0) {
            $this->comment('Tip: run php artisan ideah:ensure-listing-vendor-memberships if vendors need active membership for public listing visibility.');
        }

        return self::SUCCESS;
    }

    private function listingTitleFromVendorInfo(VendorInfo $info): string
    {
        $details = (string) $info->details;
        if (preg_match('/^Business:\s*(.+)$/m', $details, $m)) {
            $t = trim($m[1]);
            if ($t !== '') {
                return Str::limit($t, 255, '');
            }
        }

        return Str::limit(trim((string) $info->name) ?: 'Listing', 255, '');
    }

    private function listingDescriptionFromDetails(string $details, string $fallbackName): string
    {
        $details = trim($details);
        if (mb_strlen($details) >= 15) {
            return $details;
        }
        $pad = 'Profile listing created from vendor registration. Contact and business details may be updated in admin.';
        if ($details !== '') {
            return $details . "\n\n" . $pad;
        }

        return 'Business: ' . $fallbackName . ". \n\n" . $pad;
    }

    private function websiteFromDetails(string $details): ?string
    {
        if (preg_match('/^Website:\s*(.+)$/m', $details, $m)) {
            $raw = trim($m[1]);
            if ($raw !== '' && strcasecmp($raw, 'na') !== 0) {
                return normalizeListingWebsiteUrl($raw);
            }
        }

        return null;
    }

    private function makePlaceholderFeatureImage(): ?string
    {
        if (! function_exists('imagecreatetruecolor')) {
            return null;
        }

        $dir = public_path('assets/img/listing');
        if (! is_dir($dir) && ! @mkdir($dir, 0775, true)) {
            return null;
        }

        $w = 600;
        $h = 400;
        $im = @imagecreatetruecolor($w, $h);
        if ($im === false) {
            return null;
        }
        $fill = imagecolorallocate($im, 238, 242, 246);
        imagefilledrectangle($im, 0, 0, $w, $h, $fill);
        $name = 'vendor-import-' . uniqid('', true) . '.jpg';
        $path = $dir . DIRECTORY_SEPARATOR . $name;
        if (! @imagejpeg($im, $path, 82)) {
            imagedestroy($im);

            return null;
        }
        imagedestroy($im);

        return $name;
    }

    private function uniqueListingSlug(int $languageId, string $base): string
    {
        $slug = mb_substr(createSlug($base), 0, 240);
        if ($slug === '') {
            $slug = 'listing';
        }
        $candidate = $slug;
        $n = 1;
        while (
            ListingContent::query()
                ->where('language_id', $languageId)
                ->where('slug', $candidate)
                ->exists()
        ) {
            $candidate = $slug . '-' . $n;
            $n++;
        }

        return $candidate;
    }
}
