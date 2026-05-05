<?php

namespace App\Http\Helpers;

/**
 * Public listing visibility.
 * When subscription plans are enabled: admin listings, or vendor listings with active membership
 * or an assigned active package (listings.package_id).
 * When subscription plans are disabled (backend): vendor listings need only an active vendor record.
 */
class ListingVisibility
{
    public static function applyListingPublicFilters($query): void
    {
        $query->where(function ($q) {
            $q->where('listings.vendor_id', '=', 0)
                ->orWhereExists(function ($sub) {
                    $sub->selectRaw('1')
                        ->from('vendors')
                        ->whereColumn('vendors.id', 'listings.vendor_id')
                        ->where('vendors.status', 1);
                });
        });

        if (!VendorPermissionHelper::subscriptionPlansEnabled()) {
            return;
        }

        $query->where(function ($q) {
            $q->where('listings.vendor_id', '=', 0)
                ->orWhere(function ($q2) {
                    $q2->whereExists(function ($sub) {
                        $sub->selectRaw('1')
                            ->from('memberships')
                            ->whereColumn('memberships.vendor_id', 'listings.vendor_id')
                            ->where('memberships.status', 1)
                            ->where('memberships.start_date', '<=', now()->format('Y-m-d'))
                            ->where('memberships.expire_date', '>=', now()->format('Y-m-d'));
                    })
                        ->orWhereExists(function ($sub) {
                            $sub->selectRaw('1')
                                ->from('packages')
                                ->whereColumn('packages.id', 'listings.package_id')
                                ->whereNotNull('listings.package_id')
                                ->where('packages.status', 1);
                        });
                });
        });
    }

    /** Vendor listing detail: active vendor; membership/package only when subscription plans are enabled. */
    public static function applyVendorListingDetailVisibility($query): void
    {
        $query->whereExists(function ($sub) {
            $sub->selectRaw('1')
                ->from('vendors')
                ->whereColumn('vendors.id', 'listings.vendor_id')
                ->where('vendors.status', 1);
        });

        if (!VendorPermissionHelper::subscriptionPlansEnabled()) {
            return;
        }

        $query->where(function ($q) {
            $q->whereExists(function ($sub) {
                $sub->selectRaw('1')
                    ->from('memberships')
                    ->whereColumn('memberships.vendor_id', 'listings.vendor_id')
                    ->where('memberships.status', 1)
                    ->where('memberships.start_date', '<=', now()->format('Y-m-d'))
                    ->where('memberships.expire_date', '>=', now()->format('Y-m-d'));
            })
                ->orWhereExists(function ($sub) {
                    $sub->selectRaw('1')
                        ->from('packages')
                        ->whereColumn('packages.id', 'listings.package_id')
                        ->whereNotNull('listings.package_id')
                        ->where('packages.status', 1);
                });
        });
    }
}
