<?php

namespace App\Console\Commands;

use App\Models\Admin;
use App\Models\Language;
use App\Models\Vendor;
use App\Models\VendorInfo;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ImportVendorsFromCsv extends Command
{
    protected $signature = 'vendors:import
                            {path : CSV file path (absolute, or relative to project root, e.g. storage/app/newlist.csv)}
                            {--dry-run : Parse and validate only; no database writes}';

    protected $description = 'Import vendor accounts from a CSV (IdeahHub-style sheet). Maps Full Name to vendor display name, Email to login email, common password 1234567890 (hashed). Use --dry-run first. Example: php artisan vendors:import storage/app/newlist.csv --dry-run';

    public function handle(): int
    {
        $rawPath = $this->argument('path');
        $path = $this->resolvePath($rawPath);
        if (! is_readable($path)) {
            $this->error("File not readable: {$path}");

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        if ($dryRun) {
            $this->warn('Dry run: no rows will be written to the database.');
        }

        $languages = Language::query()->orderBy('is_default', 'desc')->orderBy('id')->get();
        if ($languages->isEmpty()) {
            $this->error('No languages found. Seed languages before importing.');

            return self::FAILURE;
        }

        $adminUsername = strtolower((string) (Admin::query()->value('username') ?? ''));
        $passwordHash = Hash::make('1234567890');

        $handle = fopen($path, 'rb');
        if ($handle === false) {
            $this->error('Could not open CSV for reading.');

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
            $this->error('Could not find an "Email Address" column in the header row.');

            return self::FAILURE;
        }

        $imported = 0;
        $skipped = 0;
        $seenEmails = [];
        $lineNo = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $lineNo++;
            if ($this->rowIsEmpty($row)) {
                continue;
            }

            $emailRaw = $col['email'] !== null ? ($row[$col['email']] ?? '') : '';
            $email = $this->parsePrimaryEmail($emailRaw);
            if ($email === null) {
                $this->line("[L{$lineNo}] SKIP invalid email: " . Str::limit(trim($emailRaw), 60));
                $skipped++;

                continue;
            }

            $emailKey = strtolower($email);
            if (isset($seenEmails[$emailKey])) {
                $this->line("[L{$lineNo}] SKIP duplicate email in CSV: {$email}");
                $skipped++;

                continue;
            }
            $seenEmails[$emailKey] = true;

            if (Vendor::query()->whereRaw('LOWER(email) = ?', [$emailKey])->exists()) {
                $this->line("[L{$lineNo}] SKIP email already exists in vendors: {$email}");
                $skipped++;

                continue;
            }

            $fullName = $this->cell($row, $col['full_name']);
            $business = $this->cell($row, $col['business']);
            $industry = $this->cell($row, $col['industry']);
            $years = $this->cell($row, $col['years']);
            $city = $this->cell($row, $col['city']);
            $whyJoin = $this->cell($row, $col['why_join']);
            $membership = $this->cell($row, $col['membership']);
            $website = $this->cell($row, $col['website']);
            $instagram = $this->cell($row, $col['instagram']);
            $phone = $this->normalizePhone($this->cell($row, $col['phone']));
            $note1 = $this->cell($row, $col['note1']);
            $note2 = $this->cell($row, $col['note2']);

            $displayName = $fullName !== '' ? $fullName : Str::before($email, '@');
            $details = $this->buildDetails(
                $business,
                $industry,
                $years,
                $whyJoin,
                $membership,
                $website,
                $instagram,
                $note1,
                $note2
            );

            $username = $this->allocateUniqueUsername($email, $adminUsername);

            if ($dryRun) {
                $this->line("[L{$lineNo}] OK  {$email} → username `{$username}` | " . Str::limit($displayName, 40));
                $imported++;

                continue;
            }

            try {
                DB::transaction(function () use (
                    $username,
                    $email,
                    $passwordHash,
                    $phone,
                    $languages,
                    $displayName,
                    $city,
                    $details
                ) {
                    $vendor = Vendor::query()->create([
                        'username' => $username,
                        'email' => $email,
                        'password' => $passwordHash,
                        'phone' => $phone,
                        'status' => 1,
                        'email_verified_at' => Carbon::now(),
                    ]);

                    foreach ($languages as $language) {
                        VendorInfo::query()->create([
                            'vendor_id' => $vendor->id,
                            'language_id' => $language->id,
                            'name' => $displayName,
                            'city' => $city,
                            'details' => $details,
                        ]);
                    }
                });
                $this->line("[L{$lineNo}] IMPORTED {$email} ({$username})");
                $imported++;
            } catch (\Throwable $e) {
                $this->error("[L{$lineNo}] FAIL {$email}: " . $e->getMessage());
                $skipped++;
            }
        }

        fclose($handle);

        $this->newLine();
        $this->info(($dryRun ? 'Would import' : 'Imported') . ": {$imported}. Skipped/failed: {$skipped}.");

        return self::SUCCESS;
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
            'full_name' => $findExact(['Full Name']),
            'business' => $findExact(['Business Name']),
            'email' => $findExact(['Email Address']),
            'industry' => $findExact(['Industry / Primary Focus Area']),
            'years' => $findExact(['Years in Business']),
            'city' => $findExact(['City / Primary Location']),
            'why_join' => $findExact([
                "Why do you want to join IdeahHub's curated global network, and what specific value would you bring to other members?",
            ]) ?? $findContains(['why do you want to join', 'curated global network']),
            'membership' => $findExact([
                'If you received 1–2 quality international leads, partnerships, or trade opportunities per month through IdeahHub, would you be open to contributing a small monthly membership fee (e.g., ₹1500–₹3000) to sustain the network?',
                'If you received 1-2 quality international leads, partnerships, or trade opportunities per month through IdeahHub, would you be open to contributing a small monthly membership fee (e.g., ₹1500–₹3000) to sustain the network?',
            ]) ?? $findContains(['if you received', 'membership fee', 'sustain the network']),
            'website' => $findExact(['Website link']),
            'instagram' => $findExact(['Instagram Handle link']),
            'phone' => $findExact(['Phone Number']),
            'note1' => $findExact(['Column 1']),
            'note2' => $findExact(['Column 2']),
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

    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^\d+]/', '', $phone) ?? '';

        return Str::limit($phone, 64, '');
    }

    private function buildDetails(
        string $business,
        string $industry,
        string $years,
        string $whyJoin,
        string $membership,
        string $website,
        string $instagram,
        string $note1,
        string $note2
    ): string {
        $lines = [];
        if ($business !== '') {
            $lines[] = __('Business') . ': ' . $business;
        }
        if ($industry !== '') {
            $lines[] = __('Industry') . ': ' . $industry;
        }
        if ($years !== '') {
            $lines[] = __('Years in business') . ': ' . $years;
        }
        if ($whyJoin !== '') {
            $lines[] = __('Why join') . ":\n" . $whyJoin;
        }
        if ($membership !== '') {
            $lines[] = __('Membership interest') . ': ' . $membership;
        }
        if ($website !== '') {
            $lines[] = __('Website') . ': ' . $website;
        }
        if ($instagram !== '') {
            $lines[] = __('Instagram') . ': ' . $instagram;
        }
        if ($note1 !== '') {
            $lines[] = __('Note') . ' 1: ' . $note1;
        }
        if ($note2 !== '') {
            $lines[] = __('Note') . ' 2: ' . $note2;
        }

        return implode("\n\n", $lines);
    }

    private function allocateUniqueUsername(string $email, string $adminUsername): string
    {
        $local = Str::before(strtolower(trim($email)), '@');
        $base = Str::slug($local, '-');
        if ($base === '') {
            $base = 'vendor';
        }
        $base = Str::limit($base, 80, '');

        $candidate = $base;
        $n = 0;
        while ($this->usernameTaken($candidate, $adminUsername)) {
            $n++;
            $suffix = '-' . $n;
            $candidate = Str::limit($base, 80 - strlen($suffix), '') . $suffix;
        }

        return $candidate;
    }

    private function usernameTaken(string $username, string $adminUsername): bool
    {
        $lower = strtolower($username);
        if ($adminUsername !== '' && $lower === $adminUsername) {
            return true;
        }

        return Vendor::query()->whereRaw('LOWER(username) = ?', [$lower])->exists();
    }
}
