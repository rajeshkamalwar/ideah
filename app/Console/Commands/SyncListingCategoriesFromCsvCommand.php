<?php

namespace App\Console\Commands;

use App\Models\Language;
use App\Models\Listing\Listing;
use App\Models\Listing\ListingContent;
use App\Models\ListingCategory;
use App\Models\Vendor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class SyncListingCategoriesFromCsvCommand extends Command
{
    protected $signature = 'listings:sync-categories-from-csv
                            {path? : Path to newlist.csv (default: storage/app/newlist.csv)}
                            {--dry-run : Show plan only; no database writes}';

    protected $description = 'Reads column D (Industry / Primary Focus Area) from the CSV, merges with existing listing categories (same slug or same name per language), creates missing categories across all languages (shared serial_number), and assigns each vendor listing\'s listing_contents to the matching category.';

    /** @var array<string, array{name: string, emails: array<int, string>}> */
    private array $industryGroups = [];

    public function handle(): int
    {
        $rawPath = $this->argument('path') ?: 'storage/app/newlist.csv';
        $path = $this->resolvePath($rawPath);
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
            $this->error('No languages found.');

            return self::FAILURE;
        }

        $defaultLang = $languages->firstWhere('is_default', 1) ?? $languages->first();

        $this->loadCsv($path);
        if ($this->industryGroups === []) {
            $this->error('No industry rows parsed from CSV.');

            return self::FAILURE;
        }

        $this->info('Unique industry labels (after merge by slug): ' . count($this->industryGroups));

        /** @var array<string, array<int, int>> slug => [ language_id => listing_category_id ] */
        $slugToCategoryIds = [];

        if ($dryRun) {
            $rows = [];
            foreach ($this->industryGroups as $slug => $group) {
                $match = $this->findCategoryMatch($defaultLang, $group['name']);
                $rows[] = [
                    $slug,
                    Str::limit($group['name'], 55),
                    $match ? 'id ' . $match->id . ' (' . Str::limit($match->name, 40) . ')' : '(would create)',
                ];
            }
            $this->table(['Slug', 'Canonical name', 'Merge / default EN'], $rows);
            $this->info('Dry run complete. Re-run without --dry-run to apply.');

            return self::SUCCESS;
        }

        $updated = 0;
        $skipped = 0;

        try {
            DB::transaction(function () use ($languages, &$slugToCategoryIds, &$updated, &$skipped) {
                foreach ($this->industryGroups as $slug => $group) {
                    $canonicalName = $group['name'];
                    $slugToCategoryIds[$slug] = $this->ensureCategoryGroupForAllLanguages(
                        $languages,
                        $canonicalName,
                        $slug
                    );
                }

                foreach ($this->industryGroups as $slug => $group) {
                    $catByLang = $slugToCategoryIds[$slug] ?? null;
                    if ($catByLang === null) {
                        continue;
                    }

                    foreach ($group['emails'] as $email) {
                        $this->assignListingCategoriesForEmail($languages, $catByLang, $email, $updated, $skipped);
                    }
                }
            });
            $this->newLine();
            $this->info("Listing content rows updated: {$updated}. Rows skipped (no vendor/listing): {$skipped}.");
        } catch (Throwable $e) {
            $this->error($e->getMessage());
            if ($this->output->isVerbose()) {
                $this->line($e->getTraceAsString());
            }

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function assignListingCategoriesForEmail($languages, array $catByLang, string $email, int &$updated, int &$skipped): void
    {
        $emailKey = strtolower($email);
        $vendor = Vendor::query()->whereRaw('LOWER(email) = ?', [$emailKey])->first();
        if ($vendor === null) {
            $skipped++;
            $this->line("SKIP no vendor for email: {$email}");

            return;
        }

        $listings = Listing::query()->where('vendor_id', $vendor->id)->get();
        if ($listings->isEmpty()) {
            $skipped++;
            $this->line("SKIP no listings for vendor {$vendor->id} ({$email})");

            return;
        }

        foreach ($listings as $listing) {
            foreach ($languages as $lang) {
                $catId = $catByLang[$lang->id] ?? null;
                if ($catId === null) {
                    continue;
                }
                $n = ListingContent::query()
                    ->where('listing_id', $listing->id)
                    ->where('language_id', $lang->id)
                    ->update(['category_id' => $catId]);
                $updated += $n;
            }
        }
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

    private function loadCsv(string $path): void
    {
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            return;
        }

        $header = fgetcsv($handle);
        if ($header === false) {
            fclose($handle);

            return;
        }

        $emailIdx = $this->headerIndex($header, ['Email Address']);
        $industryIdx = $this->headerIndex($header, ['Industry / Primary Focus Area']);
        if ($emailIdx === null || $industryIdx === null) {
            fclose($handle);
            $this->error('Required columns not found in CSV header.');

            return;
        }

        $lineNo = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $lineNo++;
            $emailRaw = trim((string) ($row[$emailIdx] ?? ''));
            $industryRaw = trim((string) ($row[$industryIdx] ?? ''));

            if ($industryRaw === '') {
                continue;
            }

            $email = $this->parsePrimaryEmail($emailRaw);
            if ($email === null) {
                continue;
            }

            $canonical = $this->canonicalIndustryName($industryRaw);
            $slug = $this->industrySlug($canonical);

            if (! isset($this->industryGroups[$slug])) {
                $this->industryGroups[$slug] = ['name' => $canonical, 'emails' => []];
            } else {
                if (mb_strlen($canonical) > mb_strlen($this->industryGroups[$slug]['name'])) {
                    $this->industryGroups[$slug]['name'] = $canonical;
                }
            }

            $this->industryGroups[$slug]['emails'][strtolower($email)] = $email;
        }

        fclose($handle);
    }

    private function headerIndex(array $header, array $candidates): ?int
    {
        foreach ($header as $i => $label) {
            $h = strtolower(trim((string) $label));
            foreach ($candidates as $c) {
                if ($h === strtolower($c)) {
                    return (int) $i;
                }
            }
        }

        return null;
    }

    private function canonicalIndustryName(string $raw): string
    {
        $s = preg_replace('/\s+/u', ' ', trim($raw)) ?? trim($raw);

        return Str::limit($s, 255, '');
    }

    private function industrySlug(string $canonicalName): string
    {
        $slug = createSlug($canonicalName);
        if ($slug === '') {
            $slug = 'industry';
        }

        return $slug;
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

    private function findCategoryMatch(Language $lang, string $canonicalName): ?ListingCategory
    {
        $slug = createSlug($canonicalName);
        if ($slug === '') {
            $slug = 'industry';
        }

        $q = ListingCategory::query()
            ->where('language_id', $lang->id)
            ->where('status', 1)
            ->where(function ($query) use ($slug, $canonicalName) {
                $query->where('slug', $slug)
                    ->orWhereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower(trim($canonicalName))]);
            });

        return $q->orderBy('id')->first();
    }

    /**
     * @return array<int, int> language_id => listing_category_id
     */
    private function ensureCategoryGroupForAllLanguages(
        $languages,
        string $canonicalName,
        string $groupSlug
    ): array {
        $ids = [];
        $serial = null;

        foreach ($languages as $lang) {
            $existing = $this->findCategoryMatch($lang, $canonicalName);
            if ($existing !== null) {
                $ids[$lang->id] = (int) $existing->id;
                if ($serial === null) {
                    $serial = $existing->serial_number !== null ? (int) $existing->serial_number : null;
                }
            }
        }

        if (count($ids) === $languages->count()) {
            return $ids;
        }

        if ($serial === null) {
            $max = (int) ListingCategory::query()->max('serial_number');
            $serial = $max > 0 ? $max + 1 : 1;
        }

        foreach ($languages as $lang) {
            if (isset($ids[$lang->id])) {
                continue;
            }

            $slug = $this->uniqueCategorySlug((int) $lang->id, createSlug($canonicalName) ?: $groupSlug);
            $cat = ListingCategory::query()->create([
                'language_id' => $lang->id,
                'name' => $canonicalName,
                'slug' => $slug,
                'serial_number' => $serial,
                'status' => 1,
                'icon' => 'fas fa-briefcase',
                'mobile_image' => null,
            ]);
            $ids[$lang->id] = (int) $cat->id;
            $this->line("Created category [{$lang->code}] #{$cat->id} \"{$canonicalName}\" (serial {$serial})");
        }

        return $ids;
    }

    private function uniqueCategorySlug(int $languageId, string $base): string
    {
        $slug = mb_substr($base, 0, 240);
        if ($slug === '') {
            $slug = 'category';
        }
        $candidate = $slug;
        $n = 1;
        while (
            ListingCategory::query()
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
