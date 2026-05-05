<?php

namespace App\Support;

use App\Models\Language;
use App\Models\Listing\Listing;
use App\Models\Listing\ListingContent;
use App\Models\Location\City;

/**
 * Ensures listing rows passed to the front-end listing map have numeric latitude/longitude.
 * The Leaflet map (map.js) skips rows without valid coords; Google geocoding may be disabled.
 */
final class MapListingCoordinates
{
    private const DEFAULT_LAT = 30.7333;

    private const DEFAULT_LNG = 76.7794;

    /** @var array<string, array{0: float, 1: float}> key: lowercase city name fragment */
    private const CITY_CENTROIDS = [
        'chandigarh' => [30.7333, 76.7794],
        'mohali' => [30.7046, 76.7179],
        'panchkula' => [30.6942, 76.8606],
        'zirakpur' => [30.6425, 76.8173],
        'ludhiana' => [30.9010, 75.8573],
        'jalandhar' => [31.3260, 75.5762],
        'patiala' => [30.3398, 76.3869],
        'amritsar' => [31.6340, 74.8723],
        'bathinda' => [30.2110, 74.9455],
        'kapurthala' => [31.3800, 75.3850],
        'kurukshetra' => [29.9695, 76.8783],
        'ambala' => [30.3786, 76.7725],
        'gurgaon' => [28.4595, 77.0266],
        'gurugram' => [28.4595, 77.0266],
        'delhi' => [28.6139, 77.2090],
        'new delhi' => [28.6139, 77.2090],
        'noida' => [28.5355, 77.3910],
        'mumbai' => [19.0760, 72.8777],
        'navi mumbai' => [19.0330, 73.0297],
        'pune' => [18.5204, 73.8567],
        'lucknow' => [26.8467, 80.9462],
        'bangalore' => [12.9716, 77.5946],
        'bengaluru' => [12.9716, 77.5946],
        'chennai' => [13.0827, 80.2707],
        'kolkata' => [22.5726, 88.3639],
        'hyderabad' => [17.3850, 78.4867],
        'solan' => [30.9045, 77.0964],
        'derabassi' => [30.8393, 76.8664],
        'dera bassi' => [30.8393, 76.8664],
        'malout' => [30.1920, 74.4990],
    ];

    public static function ensureOnRow(object $row): void
    {
        $lat = $row->latitude ?? null;
        $lng = $row->longitude ?? null;
        if (self::coordsUsable($lat, $lng)) {
            return;
        }

        [$la, $ln] = self::approximateFromRowCityAndAddress(
            isset($row->city_id) ? (int) $row->city_id : null,
            (string) ($row->address ?? '')
        );

        $row->latitude = (string) $la;
        $row->longitude = (string) $ln;
    }

    /**
     * Writes approximate coordinates to the listing row when lat/lng are missing or invalid.
     * Uses default-language listing_content (else first content row) for city + address.
     */
    public static function persistApproximateIfMissing(Listing $listing): bool
    {
        if (self::coordsUsable($listing->latitude, $listing->longitude)) {
            return false;
        }

        $content = self::primaryListingContent($listing);
        if ($content === null) {
            return false;
        }

        [$la, $ln] = self::approximateFromRowCityAndAddress(
            $content->city_id !== null ? (int) $content->city_id : null,
            (string) ($content->address ?? '')
        );

        $listing->latitude = (string) $la;
        $listing->longitude = (string) $ln;
        $listing->save();

        return true;
    }

    /**
     * @return array{0: float, 1: float}
     */
    public static function approximateCoordinatesForListing(Listing $listing): ?array
    {
        $content = self::primaryListingContent($listing);
        if ($content === null) {
            return null;
        }

        return self::approximateFromRowCityAndAddress(
            $content->city_id !== null ? (int) $content->city_id : null,
            (string) ($content->address ?? '')
        );
    }

    private static function primaryListingContent(Listing $listing): ?ListingContent
    {
        $defaultLang = Language::query()->where('is_default', 1)->first();
        if ($defaultLang) {
            $c = ListingContent::query()
                ->where('listing_id', $listing->id)
                ->where('language_id', $defaultLang->id)
                ->first();
            if ($c) {
                return $c;
            }
        }

        return ListingContent::query()
            ->where('listing_id', $listing->id)
            ->orderBy('language_id')
            ->first();
    }

    /**
     * @return array{0: float, 1: float}
     */
    private static function approximateFromRowCityAndAddress(?int $cityId, string $address): array
    {
        $cityName = '';
        if ($cityId) {
            $cityName = (string) (City::query()->find($cityId)?->name ?? '');
        }

        return self::resolveApproximate($cityName, $address);
    }

    public static function shouldBackfillLatitudeLongitude($lat, $lng): bool
    {
        return ! self::coordsUsable($lat, $lng);
    }

    private static function coordsUsable($lat, $lng): bool
    {
        if ($lat === null || $lng === null || $lat === '' || $lng === '') {
            return false;
        }
        $la = (float) $lat;
        $ln = (float) $lng;
        if (! is_finite($la) || ! is_finite($ln)) {
            return false;
        }
        if ($la < -90 || $la > 90 || $ln < -180 || $ln > 180) {
            return false;
        }
        if (abs($la) < 0.0001 && abs($ln) < 0.0001) {
            return false;
        }

        return true;
    }

    /**
     * @return array{0: float, 1: float}
     */
    private static function resolveApproximate(string $cityName, string $address): array
    {
        $haystack = mb_strtolower(trim($cityName . ' ' . $address));
        foreach (self::CITY_CENTROIDS as $needle => $coords) {
            if ($needle !== '' && str_contains($haystack, $needle)) {
                return $coords;
            }
        }

        return [self::DEFAULT_LAT, self::DEFAULT_LNG];
    }
}
