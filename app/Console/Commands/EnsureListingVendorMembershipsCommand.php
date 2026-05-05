<?php

namespace App\Console\Commands;

use App\Models\BasicSettings\Basic;
use App\Models\Listing\Listing;
use App\Models\Membership;
use App\Models\Package;
use Illuminate\Console\Command;

class EnsureListingVendorMembershipsCommand extends Command
{
    protected $signature = 'ideah:ensure-listing-vendor-memberships
                            {--years=1 : Length of the generated membership period}';

    protected $description = 'Create active memberships for vendors that own listings but lack a current membership (required for frontend listing visibility).';

    public function handle(): int
    {
        $packageId = Package::query()->where('status', 1)->orderBy('id')->value('id');
        if ($packageId === null) {
            $this->error('No active package found. Add a package in Admin first.');

            return self::FAILURE;
        }

        $basic = Basic::query()->first();
        $currencyText = $basic->base_currency_text ?? 'USD';
        $currencySymbol = $basic->base_currency_symbol ?? '$';

        $years = max(1, (int) $this->option('years'));
        $start = now()->format('Y-m-d');
        $end = now()->addYears($years)->format('Y-m-d');

        $vendorIds = Listing::query()
            ->whereNotNull('vendor_id')
            ->where('vendor_id', '>', 0)
            ->distinct()
            ->pluck('vendor_id')
            ->all();

        $created = 0;
        foreach ($vendorIds as $vid) {
            $hasActive = Membership::query()
                ->where('vendor_id', $vid)
                ->where('status', 1)
                ->where('start_date', '<=', $start)
                ->where('expire_date', '>=', $start)
                ->exists();

            if ($hasActive) {
                continue;
            }

            Membership::query()->create([
                'vendor_id' => $vid,
                'package_id' => $packageId,
                'status' => 1,
                'start_date' => $start,
                'expire_date' => $end,
                'price' => 0,
                'currency' => $currencyText,
                'currency_symbol' => $currencySymbol,
                'payment_method' => 'Legacy import / admin bootstrap',
                'transaction_id' => 'bootstrap-' . $vid . '-' . time(),
                'is_trial' => 1,
                'trial_days' => 365 * $years,
                'modified' => 1,
            ]);
            $created++;
            $this->line("Membership created for vendor #{$vid}");
        }

        $this->info("Finished. New memberships: {$created}. Vendors checked: " . count($vendorIds));

        return self::SUCCESS;
    }
}
