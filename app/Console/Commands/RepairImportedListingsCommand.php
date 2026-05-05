<?php

namespace App\Console\Commands;

use App\Models\Aminite;
use App\Models\Language;
use App\Models\Listing\Listing;
use App\Models\Listing\ListingContent;
use App\Models\Listing\ListingFeature;
use App\Models\Listing\ListingFeatureContent;
use App\Models\ListingCategory;
use App\Models\Location\City;
use App\Models\Location\Country;
use App\Models\Location\State;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class RepairImportedListingsCommand extends Command
{
    protected $signature = 'ideah:repair-imported-listings
                            {--skip-locations : Do not sync country/state/city from legacy}
                            {--skip-features : Do not build listing_feature rows from listing_contents.features}
                            {--skip-aminities : Do not infer aminities from category + offered_services text}
                            {--force-aminities : Overwrite existing aminities selections}';

    protected $description = 'Backfill listing location IDs from ideahhub businesses, sync Features tab, and infer aminities from text/category.';

    private const COUNTRY_EN = [
        'NL' => 'Netherlands',
        'IN' => 'India',
        'AS' => 'American Samoa',
        'DZ' => 'Algeria',
        'AM' => 'Armenia',
        'AG' => 'Antigua and Barbuda',
    ];

    private const NL_PROVINCE_EN = [
        'NH' => 'North Holland',
        'UT' => 'Utrecht',
        'ZH' => 'South Holland',
        'GR' => 'Groningen',
        'LI' => 'Limburg',
        'GE' => 'Gelderland',
        'NB' => 'North Brabant',
        'OV' => 'Overijssel',
        'DR' => 'Drenthe',
        'FR' => 'Friesland',
        'ZE' => 'Zeeland',
    ];

    private const IN_STATE_EN = [
        'CH' => 'Chandigarh',
        'AR' => 'Arunachal Pradesh',
        'PB' => 'Punjab',
        'HR' => 'Haryana',
        'KA' => 'Karnataka',
        'TN' => 'Tamil Nadu',
        'DL' => 'Delhi',
        'KT' => 'Karnataka',
    ];

    /** English aminite id => Arabic aminite id (installer pairs). */
    private const EN_TO_AR_AMENITY = [
        1 => 2,
        3 => 4,
        5 => 6,
        8 => 9,
        10 => 11,
        12 => 13,
        15 => 16,
        17 => 18,
        19 => 20,
        21 => 22,
    ];

    /** Default English amenity ids when category slug matches (sensible venue types). */
    private const CATEGORY_AMENITIES_EN = [
        'restaurants' => [3, 5, 8, 15],
        'cafes' => [3, 5, 8, 15],
        'catering-service' => [3, 5, 8, 15, 17],
        'hotels' => [1, 3, 5, 8],
        'shops' => [3, 5, 8],
        'financial-services' => [5, 8, 14],
        'property-dealers' => [5, 8, 14],
        'real-estate-agency' => [5, 8, 14],
        'travel-agency-tour-operator' => [5, 8, 14],
        'medical-services' => [5, 8, 12],
        'education' => [5, 8, 14],
        'entertainment-center' => [3, 5, 8],
        'banquet-hall-services' => [3, 5, 8, 15, 17],
        'vehicle-services' => [5, 8],
        'digital-service' => [5, 14],
        'educational-institutes' => [5, 8, 14],
        'camera-surveillance' => [5, 8, 14],
        'other' => [5, 8],
    ];

    /**
     * @var list<array{0: list<string>, 1: int}>
     */
    private const AMENITY_KEYWORD_RULES = [
        [['swimming', 'pool'], 1],
        [['wifi', 'wi-fi', 'wireless', 'internet'], 5],
        [['parking', 'car park', 'parking facilities'], 8],
        [['seating', 'comfortable seat'], 3],
        [['prayer', 'mosque'], 10],
        [['pharmacy', 'prescription'], 12],
        [['multilingual', 'bilingual', 'speaks english', 'speaks dutch'], 14],
        [
            [
                'restaurant', 'dining', 'catering', 'kitchen', 'menu', 'chef', 'curry', 'cafe',
                'breakfast', 'lunch', 'dinner', 'buffet', 'banquet', 'wedding hall', 'food',
                'tandoori', 'thai', 'dosa', 'indian restaurant',
            ],
            15,
        ],
        [['private dining', 'private room'], 17],
        [['gym', 'fitness', 'exercise studio'], 19],
        [['locker'], 21],
    ];

    public function handle(): int
    {
        $skipLoc = (bool) $this->option('skip-locations');
        $skipFeat = (bool) $this->option('skip-features');
        $skipAm = (bool) $this->option('skip-aminities');
        $forceAm = (bool) $this->option('force-aminities');

        if (!$skipLoc) {
            $this->syncLocations();
        }

        if (!$skipFeat) {
            $this->syncFeaturesFromListingContent();
        }

        if (!$skipAm) {
            $this->syncAminitiesFromContent($forceAm);
        }

        $this->normalizeEmptyAminities();

        $this->info('Repair finished.');

        return self::SUCCESS;
    }

    private function syncLocations(): void
    {
        try {
            DB::connection('ideahhub_legacy')->getPdo();
        } catch (Throwable $e) {
            $this->warn('Skipping locations: cannot connect to ideahhub_legacy (' . $e->getMessage() . ').');

            return;
        }

        $legacy = DB::connection('ideahhub_legacy');
        $businesses = $legacy->table('businesses')->whereNull('deleted_at')->get()->keyBy('id');

        $updated = 0;
        $skipped = 0;

        foreach (ListingContent::query()->orderBy('id')->cursor() as $lc) {
            $bizId = $this->legacyBusinessIdFromSlug((string) $lc->slug);
            if ($bizId === null || !$businesses->has($bizId)) {
                $skipped++;

                continue;
            }

            $biz = $businesses->get($bizId);
            $countryCode = strtoupper(trim((string) $biz->country));
            $stateCode = trim((string) $biz->state);
            $cityName = trim((string) $biz->city);

            $langId = (int) $lc->language_id;
            $countryName = self::COUNTRY_EN[$countryCode] ?? ($countryCode !== '' ? $countryCode : 'Unknown');
            $stateName = $this->stateDisplayName($countryCode, $stateCode);
            if ($cityName === '') {
                $cityName = 'Unknown';
            }

            $country = Country::query()->firstOrCreate(
                ['language_id' => $langId, 'name' => $countryName],
                []
            );

            $state = State::query()->firstOrCreate(
                [
                    'language_id' => $langId,
                    'country_id' => $country->id,
                    'name' => $stateName,
                ],
                []
            );

            $slugBase = createSlug($cityName);
            if ($slugBase === '') {
                $slugBase = 'city';
            }
            $slug = mb_substr($slugBase, 0, 240);

            $city = City::query()->firstOrCreate(
                [
                    'language_id' => $langId,
                    'state_id' => $state->id,
                    'name' => $cityName,
                ],
                [
                    'country_id' => $country->id,
                    'slug' => $slug,
                    'feature_image' => null,
                ]
            );

            if ($city->slug === null || $city->slug === '') {
                $city->forceFill(['slug' => $slug])->save();
            }

            $lc->forceFill([
                'country_id' => $country->id,
                'state_id' => $state->id,
                'city_id' => $city->id,
            ])->save();

            $updated++;
        }

        $this->info("Locations: updated {$updated} listing content row(s), skipped {$skipped} (no legacy id in slug).");
    }

    private function legacyBusinessIdFromSlug(string $slug): ?int
    {
        if (preg_match('/-(\d+)$/', $slug, $m)) {
            return (int) $m[1];
        }

        return null;
    }

    private function stateDisplayName(string $countryCode, string $stateCode): string
    {
        $cc = strtoupper(trim($countryCode));
        $sc = strtoupper(trim($stateCode));
        if ($sc === '') {
            return 'Unknown';
        }
        if ($cc === 'NL' && isset(self::NL_PROVINCE_EN[$sc])) {
            return self::NL_PROVINCE_EN[$sc];
        }
        if ($cc === 'IN' && isset(self::IN_STATE_EN[$sc])) {
            return self::IN_STATE_EN[$sc];
        }
        if (preg_match('/^\d+$/', $stateCode)) {
            return 'Region ' . $stateCode;
        }

        return $stateCode;
    }

    private function syncFeaturesFromListingContent(): void
    {
        $languages = Language::query()->orderBy('id')->pluck('id')->all();
        $heading = 'Offered services';

        $rows = 0;

        foreach (Listing::query()->orderBy('id')->cursor() as $listing) {
            $hasAnyFeature = ListingFeature::query()->where('listing_id', $listing->id)->exists();
            if ($hasAnyFeature) {
                continue;
            }

            foreach ($languages as $langId) {
                $lc = ListingContent::query()
                    ->where('listing_id', $listing->id)
                    ->where('language_id', $langId)
                    ->first();
                if ($lc === null || $lc->features === null || trim((string) $lc->features) === '') {
                    continue;
                }

                $decoded = json_decode((string) $lc->features, true);
                if (!is_array($decoded) || $decoded === []) {
                    continue;
                }

                $values = [];
                foreach ($decoded as $item) {
                    if (is_string($item) && $item !== '') {
                        $values[] = $item;
                    } elseif (is_scalar($item)) {
                        $values[] = (string) $item;
                    }
                }
                if ($values === []) {
                    continue;
                }

                $lf = ListingFeature::query()->firstOrCreate(
                    [
                        'listing_id' => $listing->id,
                        'indx' => '0',
                    ],
                    []
                );

                ListingFeatureContent::query()->updateOrCreate(
                    [
                        'listing_feature_id' => $lf->id,
                        'language_id' => $langId,
                    ],
                    [
                        'feature_heading' => $heading,
                        'feature_value' => json_encode(array_values($values), JSON_UNESCAPED_UNICODE),
                    ]
                );

                $rows++;
            }
        }

        $this->info("Features: synced {$rows} listing_feature_content row(s) (listings with no listing_features rows only).");
    }

    /**
     * Fill listing_contents.aminities from listing category + keywords in features/description/title.
     */
    private function syncAminitiesFromContent(bool $force): void
    {
        $defaultEn = [5, 8];
        $validByLang = [];
        foreach (Language::query()->orderBy('id')->pluck('id')->all() as $lid) {
            $validByLang[$lid] = Aminite::query()->where('language_id', $lid)->pluck('id')->flip()->all();
        }

        $updated = 0;
        foreach (ListingContent::query()->orderBy('id')->cursor() as $lc) {
            $current = $lc->aminities;
            if (!$this->shouldRewriteAminities($current, $force)) {
                continue;
            }

            $langId = (int) $lc->language_id;
            $cat = ListingCategory::query()
                ->where('id', $lc->category_id)
                ->where('language_id', $langId)
                ->first();

            $slug = $cat !== null ? (string) $cat->slug : '';
            $haystack = $this->amenityHaystack($lc, $cat);

            $enIds = [];
            if ($slug !== '' && isset(self::CATEGORY_AMENITIES_EN[$slug])) {
                $enIds = self::CATEGORY_AMENITIES_EN[$slug];
            }
            if ($enIds === []) {
                $enIds = $defaultEn;
            }

            foreach (self::AMENITY_KEYWORD_RULES as [$needles, $enId]) {
                foreach ($needles as $needle) {
                    if ($needle !== '' && str_contains($haystack, mb_strtolower($needle, 'UTF-8'))) {
                        $enIds[] = $enId;
                        break;
                    }
                }
            }

            $enIds = array_values(array_unique($enIds));
            $mapped = $this->mapAmenityIdsForLanguage($enIds, $langId, $validByLang);
            if ($mapped === []) {
                $mapped = $this->mapAmenityIdsForLanguage($defaultEn, $langId, $validByLang);
            }

            $json = json_encode(array_values(array_map('strval', $mapped)), JSON_UNESCAPED_UNICODE);
            $lc->forceFill(['aminities' => $json])->save();
            $updated++;
        }

        $this->info("Aminities: updated {$updated} listing_content row(s) from category + text heuristics.");
    }

    private function shouldRewriteAminities(?string $current, bool $force): bool
    {
        if ($force) {
            return true;
        }
        if ($current === null || trim((string) $current) === '') {
            return true;
        }
        $decoded = json_decode((string) $current, true);
        if (!is_array($decoded) || $decoded === []) {
            return true;
        }

        return false;
    }

    private function amenityHaystack(ListingContent $lc, ?ListingCategory $cat): string
    {
        $parts = [
            (string) $lc->title,
            strip_tags((string) ($lc->description ?? '')),
        ];
        if ($cat !== null) {
            $parts[] = (string) $cat->name;
            $parts[] = (string) $cat->slug;
        }
        if ($lc->features !== null && trim((string) $lc->features) !== '') {
            $decoded = json_decode((string) $lc->features, true);
            if (is_array($decoded)) {
                foreach ($decoded as $item) {
                    if (is_string($item)) {
                        $parts[] = $item;
                    }
                }
            }
        }

        return mb_strtolower(implode(' ', $parts), 'UTF-8');
    }

    /**
     * @param  array<int, int>  $enIds
     * @param  array<int, array<int, int>>  $validByLang
     * @return list<int>
     */
    private function mapAmenityIdsForLanguage(array $enIds, int $languageId, array $validByLang): array
    {
        $valid = $validByLang[$languageId] ?? [];

        if ($languageId === 21) {
            $out = [];
            foreach (array_unique($enIds) as $eid) {
                if (isset(self::EN_TO_AR_AMENITY[$eid])) {
                    $aid = self::EN_TO_AR_AMENITY[$eid];
                    if (isset($valid[$aid])) {
                        $out[] = $aid;
                    }
                }
            }

            return array_values(array_unique($out));
        }

        $out = [];
        foreach (array_unique($enIds) as $eid) {
            if (isset($valid[$eid])) {
                $out[] = $eid;
            }
        }

        return array_values(array_unique($out));
    }

    /**
     * Ensure aminities is valid JSON array string so listing details / filters do not break on null.
     */
    private function normalizeEmptyAminities(): void
    {
        $n = ListingContent::query()
            ->where(function ($q): void {
                $q->whereNull('aminities')
                    ->orWhere('aminities', '');
            })
            ->update(['aminities' => '[]']);

        if ($n > 0) {
            $this->info("Aminities: normalized {$n} listing_content row(s) to [].");
        }
    }
}
