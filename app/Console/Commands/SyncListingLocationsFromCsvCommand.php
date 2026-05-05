<?php

namespace App\Console\Commands;

use App\Models\Language;
use App\Models\Listing\Listing;
use App\Models\Listing\ListingContent;
use App\Models\Location\City;
use App\Models\Location\Country;
use App\Models\Location\State;
use App\Models\Vendor;
use App\Models\VendorInfo;
use App\Support\BusinessLocationResolver;
use App\Support\ListingGeocoder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SyncListingLocationsFromCsvCommand extends Command
{
    protected $signature = 'listings:sync-locations-from-csv
                            {path : CSV path (absolute or relative to project root)}
                            {--dry-run : Show actions only; no writes}';

    protected $description = 'Read City/Primary Location (column F) from an IdeahHub CSV, match vendors by email, ensure India/State/City records, set listing_content location FKs + address.';

    public function handle(): int
    {
        $path = $this->resolvePath((string) $this->argument('path'));
        if (! is_readable($path)) {
            $this->error("File not readable: {$path}");

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        if ($dryRun) {
            $this->warn('Dry run — no database writes.');
        }

        $languages = Language::query()->orderBy('is_default', 'desc')->orderBy('id')->get();
        if ($languages->isEmpty()) {
            $this->error('No languages configured.');

            return self::FAILURE;
        }

        $defaultLang = $languages->firstWhere('is_default', 1) ?? $languages->first();

        $handle = fopen($path, 'rb');
        if ($handle === false) {
            $this->error('Could not open CSV.');

            return self::FAILURE;
        }

        $header = fgetcsv($handle);
        if ($header === false) {
            fclose($handle);
            $this->error('CSV is empty.');

            return self::FAILURE;
        }

        $col = $this->mapHeaderIndexes($header);
        if ($col['email'] === null) {
            fclose($handle);
            $this->error('Could not find Email Address column.');

            return self::FAILURE;
        }

        $resolver = new BusinessLocationResolver();

        $updated = 0;
        $skipped = 0;
        $missingVendor = 0;
        $lineNo = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $lineNo++;
            if ($this->rowIsEmpty($row)) {
                continue;
            }

            $email = $this->parsePrimaryEmail($this->cell($row, $col['email']));
            $locationRaw = $this->cell($row, $col['city']);

            if ($email === null) {
                $this->line("[L{$lineNo}] SKIP no valid email");
                $skipped++;

                continue;
            }

            if ($locationRaw === '') {
                $this->line("[L{$lineNo}] SKIP empty location for {$email}");
                $skipped++;

                continue;
            }

            $vendor = Vendor::query()->whereRaw('LOWER(email) = ?', [strtolower($email)])->first();
            if ($vendor === null) {
                $this->line("[L{$lineNo}] SKIP vendor not found: {$email}");
                $missingVendor++;
                $skipped++;

                continue;
            }

            $resolved = $resolver->resolve($locationRaw);
            if ($resolved === null) {
                $this->line("[L{$lineNo}] SKIP could not parse location for {$email}");
                $skipped++;

                continue;
            }

            if ($dryRun) {
                $this->line("[L{$lineNo}] {$email} → {$resolved['city']}, {$resolved['state']}, {$resolved['country']} | address: " . Str::limit($resolved['address'], 60));
                $updated++;

                continue;
            }

            try {
                DB::transaction(function () use ($vendor, $languages, $defaultLang, $resolved, $locationRaw) {
                    $country = $this->ensureCountry((int) $defaultLang->id, $resolved['country']);
                    $state = $this->ensureState((int) $defaultLang->id, (int) $country->id, $resolved['state']);
                    $city = $this->ensureCity(
                        (int) $defaultLang->id,
                        (int) $country->id,
                        (int) $state->id,
                        $resolved['city']
                    );

                    foreach ($languages as $lang) {
                        VendorInfo::query()
                            ->where('vendor_id', $vendor->id)
                            ->where('language_id', $lang->id)
                            ->update(['city' => Str::limit($resolved['address'], 255, '')]);
                    }

                    $listings = Listing::query()->where('vendor_id', $vendor->id)->get();
                    foreach ($listings as $listing) {
                        ListingContent::query()
                            ->where('listing_id', $listing->id)
                            ->update([
                                'country_id' => $country->id,
                                'state_id' => $state->id,
                                'city_id' => $city->id,
                                'address' => Str::limit($resolved['address'], 500, ''),
                            ]);

                        ListingGeocoder::syncFromDefaultLanguageAddress($listing->fresh());
                    }
                });

                $this->line("[L{$lineNo}] OK {$email} → city_id synced");
                $updated++;
            } catch (\Throwable $e) {
                $this->error("[L{$lineNo}] FAIL {$email}: " . $e->getMessage());
                $skipped++;
            }
        }

        fclose($handle);

        $this->newLine();
        $this->info(($dryRun ? 'Would update' : 'Updated') . " rows: {$updated}. Skipped: {$skipped}. Vendors not found: {$missingVendor}.");

        return self::SUCCESS;
    }

    private function ensureCountry(int $languageId, string $name): Country
    {
        $existing = Country::query()
            ->where('language_id', $languageId)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->first();
        if ($existing) {
            return $existing;
        }

        return Country::query()->create([
            'language_id' => $languageId,
            'name' => $name,
        ]);
    }

    private function ensureState(int $languageId, int $countryId, string $name): State
    {
        $existing = State::query()
            ->where('language_id', $languageId)
            ->where('country_id', $countryId)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->first();
        if ($existing) {
            return $existing;
        }

        return State::query()->create([
            'language_id' => $languageId,
            'country_id' => $countryId,
            'name' => $name,
        ]);
    }

    private function ensureCity(int $languageId, int $countryId, int $stateId, string $cityName): City
    {
        $existing = City::query()
            ->where('language_id', $languageId)
            ->where('state_id', $stateId)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($cityName))])
            ->first();
        if ($existing) {
            return $existing;
        }

        $baseSlug = mb_substr(createSlug($cityName), 0, 220) ?: 'city';
        $slug = $baseSlug;
        $n = 1;
        while (
            City::query()
                ->where('language_id', $languageId)
                ->where('state_id', $stateId)
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $n;
            $n++;
        }

        return City::query()->create([
            'language_id' => $languageId,
            'country_id' => $countryId,
            'state_id' => $stateId,
            'name' => trim($cityName),
            'slug' => $slug,
            'feature_image' => null,
        ]);
    }

    private function resolvePath(string $rawPath): string
    {
        $rawPath = trim($rawPath);
        if ($rawPath === '') {
            return $rawPath;
        }
        if (preg_match('#^[A-Za-z]:[\\\\/]#', $rawPath) || str_starts_with($rawPath, '/') || str_starts_with($rawPath, '\\')) {
            return $rawPath;
        }

        return base_path(trim($rawPath, '/\\'));
    }

    /**
     * @return array<string, int|null>
     */
    private function mapHeaderIndexes(array $header): array
    {
        $norm = [];
        foreach ($header as $i => $label) {
            $norm[$i] = strtolower(trim((string) $label));
        }

        $findExact = function (array $needles) use ($norm): ?int {
            foreach ($norm as $i => $h) {
                foreach ($needles as $needle) {
                    if ($h === strtolower($needle)) {
                        return (int) $i;
                    }
                }
            }

            return null;
        };

        $findContains = function (array $substrings) use ($norm): ?int {
            foreach ($norm as $i => $h) {
                $ok = true;
                foreach ($substrings as $sub) {
                    if (! str_contains($h, strtolower($sub))) {
                        $ok = false;
                        break;
                    }
                }
                if ($ok) {
                    return (int) $i;
                }
            }

            return null;
        };

        return [
            'email' => $findExact(['Email Address']),
            'city' => $findExact(['City / Primary Location']),
        ];
    }

    private function cell(array $row, ?int $index): string
    {
        if ($index === null) {
            return '';
        }

        return trim((string) ($row[$index] ?? ''));
    }

    private function rowIsEmpty(array $row): bool
    {
        foreach ($row as $cell) {
            if (trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }

    private function parsePrimaryEmail(string $raw): ?string
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }
        $parts = preg_split('/[\s,;]+/', $raw);
        if ($parts === false) {
            $parts = [$raw];
        }
        foreach ($parts as $part) {
            $part = trim($part, " \t\n\r\0\x0B\"'");
            if ($part === '') {
                continue;
            }
            if (filter_var($part, FILTER_VALIDATE_EMAIL)) {
                return strtolower($part);
            }
        }

        return filter_var($raw, FILTER_VALIDATE_EMAIL) ? strtolower($raw) : null;
    }
}
