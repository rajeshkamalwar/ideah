<?php

namespace App\Support;

use App\Http\Helpers\GeoSearch;
use App\Models\BasicSettings\Basic;
use App\Models\Language;
use App\Models\Listing\Listing;
use App\Models\Listing\ListingContent;
use App\Models\Location\City;
use App\Models\Location\Country;
use App\Models\Location\State;

class ListingGeocoder
{
    public static function mapsGeocodingEnabled(): bool
    {
        $basic = Basic::query()->select('google_map_api_key_status', 'google_map_api_key')->first();

        return $basic
            && (int) $basic->google_map_api_key_status === 1
            && trim((string) $basic->google_map_api_key) !== '';
    }

    /**
     * Geocode the listing using the default language address line + city/state/country names.
     * Updates listings.latitude / listings.longitude when the Geocoding API returns OK.
     */
    public static function syncFromDefaultLanguageAddress(Listing $listing): bool
    {
        if (!self::mapsGeocodingEnabled()) {
            return false;
        }

        $defaultLang = Language::query()->where('is_default', 1)->first();
        if (!$defaultLang) {
            return false;
        }

        $content = ListingContent::query()
            ->where('listing_id', $listing->id)
            ->where('language_id', $defaultLang->id)
            ->first();

        if (!$content) {
            return false;
        }

        $address = self::buildGeocodeQueryString($content);
        if ($address === '') {
            return false;
        }

        $apiKey = Basic::query()->value('google_map_api_key');
        $coords = GeoSearch::getCoordinates($address, (string) $apiKey);

        if (!isset($coords['lat'], $coords['lng'])) {
            return false;
        }

        $listing->latitude = (string) $coords['lat'];
        $listing->longitude = (string) $coords['lng'];
        $listing->save();

        return true;
    }

    public static function buildGeocodeQueryString(ListingContent $content): string
    {
        $parts = [];

        $street = trim((string) $content->address);
        if ($street !== '') {
            $parts[] = $street;
        }

        if ($content->city_id) {
            $name = City::query()->find($content->city_id)?->name;
            if ($name) {
                $parts[] = $name;
            }
        }

        if ($content->state_id) {
            $name = State::query()->find($content->state_id)?->name;
            if ($name) {
                $parts[] = $name;
            }
        }

        if ($content->country_id) {
            $name = Country::query()->find($content->country_id)?->name;
            if ($name) {
                $parts[] = $name;
            }
        }

        return trim(implode(', ', array_filter($parts)));
    }
}
