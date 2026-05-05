<?php

namespace App\Console\Commands;

use App\Models\ListingCategory;
use App\Services\ListingCategoryFaqDefaultsApplier;
use Illuminate\Console\Command;

class SeedListingCategoryFaqDefaultsCommand extends Command
{
    protected $signature = 'listing:seed-category-faq-defaults';

    protected $description = 'Insert default FAQ templates for each listing category that has none (by business type / slug).';

    public function handle(): int
    {
        $totalRows = 0;
        $categoriesTouched = 0;

        foreach (ListingCategory::query()->cursor() as $category) {
            $added = ListingCategoryFaqDefaultsApplier::applyIfEmpty($category);
            if ($added > 0) {
                $categoriesTouched++;
                $totalRows += $added;
                $this->line("Category #{$category->id} ({$category->slug}): {$added} FAQs");
            }
        }

        $this->info("Done. Filled {$categoriesTouched} categories ({$totalRows} FAQ rows total). Skipped categories that already had templates.");

        return self::SUCCESS;
    }
}
