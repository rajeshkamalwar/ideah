<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add columns to products table
        if (!Schema::hasColumn('products', 'listing_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->unsignedBigInteger('listing_id')->nullable();
            });
        }

        if (!Schema::hasColumn('products', 'placement_type')) {
            Schema::table('products', function (Blueprint $table) {
                $table->tinyInteger('placement_type')->unsigned()->nullable();
            });
        }

        // Add columns to product_orders table
        if (!Schema::hasColumn('product_orders', 'vendor_id')) {
            Schema::table('product_orders', function (Blueprint $table) {
                $table->unsignedBigInteger('vendor_id')->nullable();
            });
        }

        if (!Schema::hasColumn('product_orders', 'total_commission')) {
            Schema::table('product_orders', function (Blueprint $table) {
                $table->decimal('total_commission', 12, 2)->nullable();
            });
        }

        if (!Schema::hasColumn('product_orders', 'admin_amount_with_commission')) {
            Schema::table('product_orders', function (Blueprint $table) {
                $table->decimal('admin_amount_with_commission', 16, 2)->nullable();
            });
        }

        if (!Schema::hasColumn('product_orders', 'vendor_net_amount')) {
            Schema::table('product_orders', function (Blueprint $table) {
                $table->json('vendor_net_amount')->nullable();
            });
        }

        if (!Schema::hasColumn('product_orders', 'per_vendor_discount_and_commission')) {
            Schema::table('product_orders', function (Blueprint $table) {
                $table->json('per_vendor_discount_and_commission')->nullable();
            });
        }

        // Add columns to basic_settings table
        if (!Schema::hasColumn('basic_settings', 'commission_amount')) {
            Schema::table('basic_settings', function (Blueprint $table) {
                $table->decimal('commission_amount', 8, 2)->nullable();
            });
        }

        if (!Schema::hasColumn('basic_settings', 'redeem_token_expire_days')) {
            Schema::table('basic_settings', function (Blueprint $table) {
                $table->unsignedSmallInteger('redeem_token_expire_days')
                    ->default(3)
                    ->after('website_title');
            });
        }

        // Add columns to product_purchase_items table
        if (!Schema::hasColumn('product_purchase_items', 'vendor_id')) {
            Schema::table('product_purchase_items', function (Blueprint $table) {
                $table->unsignedBigInteger('vendor_id')->nullable();
            });
        }

        if (!Schema::hasColumn('product_purchase_items', 'vendor_net_amount')) {
            Schema::table('product_purchase_items', function (Blueprint $table) {
                $table->decimal('vendor_net_amount', 16, 2)->nullable();
            });
        }

        // Modify vendors table amount column
        if (Schema::hasColumn('vendors', 'amount')) {
            Schema::table('vendors', function (Blueprint $table) {
                $table->decimal('amount', 16, 2)->nullable()->change();
            });
        }

        // Add columns to memberships table
        if (!Schema::hasColumn('memberships', 'claim_id')) {
            Schema::table('memberships', function (Blueprint $table) {
                $table->unsignedSmallInteger('claim_id')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove columns from products table
        if (Schema::hasColumn('products', 'listing_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('listing_id');
            });
        }

        if (Schema::hasColumn('products', 'placement_type')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('placement_type');
            });
        }

        // Remove columns from product_orders table
        if (Schema::hasColumn('product_orders', 'vendor_id')) {
            Schema::table('product_orders', function (Blueprint $table) {
                $table->dropColumn('vendor_id');
            });
        }

        if (Schema::hasColumn('product_orders', 'total_commission')) {
            Schema::table('product_orders', function (Blueprint $table) {
                $table->dropColumn('total_commission');
            });
        }

        if (Schema::hasColumn('product_orders', 'admin_amount_with_commission')) {
            Schema::table('product_orders', function (Blueprint $table) {
                $table->dropColumn('admin_amount_with_commission');
            });
        }

        if (Schema::hasColumn('product_orders', 'vendor_net_amount')) {
            Schema::table('product_orders', function (Blueprint $table) {
                $table->dropColumn('vendor_net_amount');
            });
        }

        if (Schema::hasColumn('product_orders', 'per_vendor_discount_and_commission')) {
            Schema::table('product_orders', function (Blueprint $table) {
                $table->dropColumn('per_vendor_discount_and_commission');
            });
        }

        // Remove columns from basic_settings table
        if (Schema::hasColumn('basic_settings', 'commission_amount')) {
            Schema::table('basic_settings', function (Blueprint $table) {
                $table->dropColumn('commission_amount');
            });
        }

        if (Schema::hasColumn('basic_settings', 'redeem_token_expire_days')) {
            Schema::table('basic_settings', function (Blueprint $table) {
                $table->dropColumn('redeem_token_expire_days');
            });
        }

        // Remove columns from product_purchase_items table
        if (Schema::hasColumn('product_purchase_items', 'vendor_id')) {
            Schema::table('product_purchase_items', function (Blueprint $table) {
                $table->dropColumn('vendor_id');
            });
        }

        if (Schema::hasColumn('product_purchase_items', 'vendor_net_amount')) {
            Schema::table('product_purchase_items', function (Blueprint $table) {
                $table->dropColumn('vendor_net_amount');
            });
        }

        // Remove columns from memberships table
        if (Schema::hasColumn('memberships', 'claim_id')) {
            Schema::table('memberships', function (Blueprint $table) {
                $table->dropColumn('claim_id');
            });
        }
    }
};
