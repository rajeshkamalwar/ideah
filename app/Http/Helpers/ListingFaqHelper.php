<?php

namespace App\Http\Helpers;

use App\Models\Listing\ListingCategoryFaqTemplate;
use App\Models\Listing\ListingContent;
use App\Models\Listing\ListingFaq;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class ListingFaqHelper
{
    /**
     * Category FAQ templates (for the listing's business type in this language) first,
     * then the listing's own FAQs. Serial numbers are renumbered for display.
     */
    public static function mergedFaqsForListingView(int $listingId, int $languageId): Collection
    {
        $content = ListingContent::query()
            ->where('listing_id', $listingId)
            ->where('language_id', $languageId)
            ->first();

        $categoryRows = collect();
        if (
            $content
            && $content->category_id
            && Schema::hasTable('listing_category_faq_templates')
        ) {
            $categoryRows = ListingCategoryFaqTemplate::query()
                ->where('listing_category_id', $content->category_id)
                ->orderBy('serial_number', 'asc')
                ->get();
        }

        $listingRows = ListingFaq::query()
            ->where('listing_id', $listingId)
            ->where('language_id', $languageId)
            ->orderBy('serial_number', 'asc')
            ->get();

        $merged = collect();
        $n = 1;

        foreach ($categoryRows as $row) {
            $merged->push((object) [
                'id' => 900000000 + (int) $row->id,
                'question' => $row->question,
                'answer' => $row->answer,
                'serial_number' => $n++,
            ]);
        }

        foreach ($listingRows as $row) {
            $merged->push((object) [
                'id' => $row->id,
                'question' => $row->question,
                'answer' => $row->answer,
                'serial_number' => $n++,
            ]);
        }

        return $merged;
    }
}
