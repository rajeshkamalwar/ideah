<?php

namespace App\Console\Commands;

use App\Models\ListingCategory;
use App\Support\ListingCategoryIcon;
use Illuminate\Console\Command;

class SyncListingCategoryIconsCommand extends Command
{
    protected $signature = 'ideah:sync-listing-category-icons';

    protected $description = 'Apply slug-based Font Awesome icons to all listing categories (every language).';

    public function handle(): int
    {
        $updated = 0;
        foreach (ListingCategory::query()->orderBy('id')->cursor() as $cat) {
            $icon = ListingCategoryIcon::forSlug((string) $cat->slug);
            if ((string) $cat->icon !== $icon) {
                $cat->forceFill(['icon' => $icon])->save();
                $updated++;
            }
        }

        $this->info("Listing category icons synced: {$updated} row(s) updated.");

        return self::SUCCESS;
    }
}
