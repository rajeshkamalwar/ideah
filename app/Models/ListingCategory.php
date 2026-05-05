<?php

namespace App\Models;

use App\Http\Helpers\ListingVisibility;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Listing\ListingCategoryFaqTemplate;
use App\Models\Listing\ListingContent;

class ListingCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'language_id',
        'name',
        'slug',
        'serial_number',
        'status',
        'icon',
        'mobile_image'
    ];
    public function listing_contents()
    {
        return $this->hasMany(ListingContent::class, 'category_id');
    }

    public function faqTemplates()
    {
        return $this->hasMany(ListingCategoryFaqTemplate::class, 'listing_category_id');
    }

    /** Listing contents in this language tied to a listing that passes public directory filters. */
    public function listedListingContents()
    {
        return $this->hasMany(ListingContent::class, 'category_id')
            ->whereColumn('listing_contents.language_id', 'listing_categories.language_id')
            ->whereNotNull('listing_contents.listing_id')
            ->whereHas('listing', function ($query) {
                $query->where('listings.status', '1')
                    ->where('listings.visibility', '1');
                ListingVisibility::applyListingPublicFilters($query);
            });
    }
}
