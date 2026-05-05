<?php

namespace App\Console\Commands;

use App\Models\Vendor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RemoveDemoDataCommand extends Command
{
    protected $signature = 'ideah:remove-demo-data
                            {--dry-run : Show what would be removed without deleting}
                            {--force : Run without confirmation}';

    protected $description = 'Remove installer demo vendors (@example.com / @example.org) and their listings/shop data. Does not touch other accounts.';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');

        $demoVendorIds = Vendor::query()
            ->whereRaw('LOWER(email) LIKE ?', ['%@example.com'])
            ->orWhereRaw('LOWER(email) LIKE ?', ['%@example.org'])
            ->orderBy('id')
            ->pluck('id')
            ->all();

        if ($demoVendorIds === []) {
            $this->info('No demo vendors found (emails ending with @example.com or @example.org). Nothing to do.');

            return self::SUCCESS;
        }

        $listingIds = DB::table('listings')
            ->whereIn('vendor_id', $demoVendorIds)
            ->pluck('id')
            ->all();

        $this->table(
            ['Item', 'Count'],
            [
                ['Demo vendor IDs', (string) count($demoVendorIds)],
                ['Listings owned by demo vendors', (string) count($listingIds)],
            ]
        );

        $this->line('Demo vendor IDs: ' . implode(', ', $demoVendorIds));

        if ($dry) {
            $this->warn('Dry run — no changes.');

            return self::SUCCESS;
        }

        $force = (bool) $this->option('force');
        if (!$force && !$this->confirm('Delete these demo vendors and all of their listings/products/orders?', false)) {
            $this->warn('Aborted.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($demoVendorIds, $listingIds): void {
            $this->purgeListingTrees($listingIds);
            $this->purgeDemoVendorShopAndAccount($demoVendorIds);
        });

        $this->info('Demo data removed.');

        return self::SUCCESS;
    }

    /**
     * @param  list<int>  $listingIds
     */
    private function purgeListingTrees(array $listingIds): void
    {
        if ($listingIds === []) {
            return;
        }

        Schema::disableForeignKeyConstraints();

        try {
            $lpIds = DB::table('listing_products')->whereIn('listing_id', $listingIds)->pluck('id')->all();
            if ($lpIds !== []) {
                DB::table('listing_product_images')->whereIn('listing_product_id', $lpIds)->delete();
            }
            DB::table('listing_product_images')->whereIn('listing_id', $listingIds)->delete();
            DB::table('listing_product_contents')->whereIn('listing_id', $listingIds)->delete();
            DB::table('listing_products')->whereIn('listing_id', $listingIds)->delete();

            $lfIds = DB::table('listing_features')->whereIn('listing_id', $listingIds)->pluck('id')->all();
            if ($lfIds !== []) {
                DB::table('listing_feature_contents')->whereIn('listing_feature_id', $lfIds)->delete();
            }
            DB::table('listing_features')->whereIn('listing_id', $listingIds)->delete();

            $listingChildren = [
                'listing_faqs',
                'listing_reviews',
                'listing_messages',
                'listing_socail_medias',
                'listing_images',
                'listing_contents',
                'business_hours',
                'claim_listings',
                'feature_orders',
                'wishlists',
                'listing_sections',
            ];

            foreach ($listingChildren as $table) {
                if (Schema::hasTable($table) && Schema::hasColumn($table, 'listing_id')) {
                    DB::table($table)->whereIn('listing_id', $listingIds)->delete();
                }
            }

            $shopProductIds = DB::table('products')->whereIn('listing_id', $listingIds)->pluck('id')->all();
            if ($shopProductIds !== [] && Schema::hasTable('product_contents')) {
                DB::table('product_contents')->whereIn('product_id', $shopProductIds)->delete();
            }
            if ($shopProductIds !== [] && Schema::hasTable('product_reviews')) {
                DB::table('product_reviews')->whereIn('product_id', $shopProductIds)->delete();
            }
            DB::table('products')->whereIn('listing_id', $listingIds)->delete();

            DB::table('listings')->whereIn('id', $listingIds)->delete();
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }

    /**
     * @param  list<int>  $demoVendorIds
     */
    private function purgeDemoVendorShopAndAccount(array $demoVendorIds): void
    {
        if ($demoVendorIds === []) {
            return;
        }

        Schema::disableForeignKeyConstraints();

        try {
            if (Schema::hasTable('product_messages')) {
                DB::table('product_messages')->whereIn('vendor_id', $demoVendorIds)->delete();
            }

            if (Schema::hasTable('product_purchase_items') && Schema::hasTable('product_orders')) {
                $orderIds = DB::table('product_orders')->whereIn('vendor_id', $demoVendorIds)->pluck('id')->all();
                if ($orderIds !== []) {
                    DB::table('product_purchase_items')->whereIn('product_order_id', $orderIds)->delete();
                }
            }

            if (Schema::hasTable('product_orders')) {
                DB::table('product_orders')->whereIn('vendor_id', $demoVendorIds)->delete();
            }

            if (Schema::hasTable('product_coupons')) {
                DB::table('product_coupons')->whereIn('vendor_id', $demoVendorIds)->delete();
            }

            $orphanProductIds = DB::table('products')->whereIn('vendor_id', $demoVendorIds)->pluck('id')->all();
            if ($orphanProductIds !== [] && Schema::hasTable('product_contents')) {
                DB::table('product_contents')->whereIn('product_id', $orphanProductIds)->delete();
            }
            if ($orphanProductIds !== [] && Schema::hasTable('product_reviews')) {
                DB::table('product_reviews')->whereIn('product_id', $orphanProductIds)->delete();
            }
            DB::table('products')->whereIn('vendor_id', $demoVendorIds)->delete();

            if (Schema::hasTable('withdraws')) {
                DB::table('withdraws')->whereIn('vendor_id', $demoVendorIds)->delete();
            }
            if (Schema::hasTable('forms')) {
                DB::table('forms')->whereIn('vendor_id', $demoVendorIds)->delete();
            }
            if (Schema::hasTable('memberships')) {
                DB::table('memberships')->whereIn('vendor_id', $demoVendorIds)->delete();
            }
            if (Schema::hasTable('vendor_infos')) {
                DB::table('vendor_infos')->whereIn('vendor_id', $demoVendorIds)->delete();
            }

            Vendor::query()->whereIn('id', $demoVendorIds)->delete();
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }
}
