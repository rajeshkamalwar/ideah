<?php

namespace App\Models\Listing;

use App\Models\ListingCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingCategoryFaqTemplate extends Model
{
    protected $fillable = [
        'listing_category_id',
        'question',
        'answer',
        'serial_number',
    ];

    protected $casts = [
        'serial_number' => 'integer',
    ];

    public function listingCategory(): BelongsTo
    {
        return $this->belongsTo(ListingCategory::class, 'listing_category_id');
    }
}
