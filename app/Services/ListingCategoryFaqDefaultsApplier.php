<?php

namespace App\Services;

use App\Models\Listing\ListingCategoryFaqTemplate;
use App\Models\ListingCategory;
use Illuminate\Support\Facades\Schema;

class ListingCategoryFaqDefaultsApplier
{
    /**
     * Map category slug (or alias) to a template key in config/listing_category_faq_defaults.php.
     */
    public static function resolveTemplateKey(ListingCategory $category): string
    {
        $aliases = config('listing_category_faq_defaults.slug_aliases', []);
        foreach ($aliases as $from => $to) {
            if ((string) $category->slug === (string) $from) {
                return $to;
            }
        }

        $slug = strtolower(trim((string) $category->slug));
        $bySlug = config('listing_category_faq_defaults.by_slug', []);

        if (isset($bySlug[$slug])) {
            return $slug;
        }

        foreach (array_keys($bySlug) as $key) {
            if ($key !== 'default' && strtolower((string) $key) === $slug) {
                return $key;
            }
        }

        return 'default';
    }

    /**
     * @return array<int, array{question: string, answer: string}>
     */
    public static function templatesForKey(string $key): array
    {
        $bySlug = config('listing_category_faq_defaults.by_slug', []);

        return $bySlug[$key] ?? $bySlug['default'] ?? [];
    }

    /**
     * Insert default templates for this category row if the table exists and none exist yet.
     *
     * @return int Number of FAQ rows created
     */
    public static function applyIfEmpty(ListingCategory $category): int
    {
        if (!Schema::hasTable('listing_category_faq_templates')) {
            return 0;
        }

        if (ListingCategoryFaqTemplate::query()->where('listing_category_id', $category->id)->exists()) {
            return 0;
        }

        $key = self::resolveTemplateKey($category);
        $faqs = self::templatesForKey($key);
        $n = 1;

        foreach ($faqs as $row) {
            ListingCategoryFaqTemplate::query()->create([
                'listing_category_id' => $category->id,
                'question' => $row['question'],
                'answer' => $row['answer'],
                'serial_number' => $n++,
            ]);
        }

        return count($faqs);
    }

    public static function applyForListingCategoryId(int $listingCategoryId): int
    {
        $category = ListingCategory::query()->find($listingCategoryId);

        return $category ? self::applyIfEmpty($category) : 0;
    }
}
