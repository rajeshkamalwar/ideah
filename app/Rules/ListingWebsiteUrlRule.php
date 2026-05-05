<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * Optional listing website: empty is OK; if filled, must normalize to a valid URL (same logic as storage).
 */
class ListingWebsiteUrlRule implements Rule
{
    public function passes($attribute, $value): bool
    {
        if ($value === null || trim((string) $value) === '') {
            return true;
        }

        return normalizeListingWebsiteUrl($value) !== null;
    }

    public function message(): string
    {
        return __('Please enter a valid website URL (e.g. https://example.com or example.com).');
    }
}
