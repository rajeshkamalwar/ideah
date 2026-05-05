<?php

namespace App\Support;

/**
 * Font Awesome 5 class names for listing category slugs (admin + front).
 */
final class ListingCategoryIcon
{
    private const DEFAULT = 'fas fa-tag';

    /** Longest needle wins only if no exact slug match; substring of slug */
    private const SLUG_CONTAINS_ICONS = [
        'restaurant' => 'fas fa-utensils',
        'catering' => 'fas fa-concierge-bell',
        'caf' => 'fas fa-coffee',
        'hotel' => 'fas fa-hotel',
        'shop' => 'fas fa-store',
        'retail' => 'fas fa-shopping-bag',
        'medical' => 'fas fa-hospital',
        'health' => 'fas fa-heartbeat',
        'travel' => 'fas fa-plane',
        'tour' => 'fas fa-route',
        'property' => 'fas fa-key',
        'estate' => 'fas fa-building',
        'real-estate' => 'fas fa-city',
        'financial' => 'fas fa-landmark',
        'mortgage' => 'fas fa-hand-holding-usd',
        'tax' => 'fas fa-file-invoice-dollar',
        'vehicle' => 'fas fa-car',
        'auto' => 'fas fa-car-side',
        'education' => 'fas fa-graduation-cap',
        'school' => 'fas fa-school',
        'digital' => 'fas fa-laptop-code',
        'tech' => 'fas fa-microchip',
        'entertainment' => 'fas fa-theater-masks',
        'event' => 'fas fa-calendar-alt',
        'banquet' => 'fas fa-glass-cheers',
        'wedding' => 'fas fa-ring',
        'camera' => 'fas fa-video',
        'surveillance' => 'fas fa-shield-alt',
        'gym' => 'fas fa-dumbbell',
    ];

    /** @var array<string, string> slug => "fas fa-..." */
    private const SLUG_ICONS = [
        'hotels' => 'fas fa-hotel',
        'restaurants' => 'fas fa-utensils',
        'cafes' => 'fas fa-coffee',
        'shops' => 'fas fa-store',
        'financial-services' => 'fas fa-landmark',
        'property-dealers' => 'fas fa-key',
        'real-estate-agency' => 'fas fa-building',
        'travel-agency-tour-operator' => 'fas fa-plane-departure',
        'banquet-hall-services' => 'fas fa-glass-cheers',
        'camera-surveillance' => 'fas fa-video',
        'vehicle-services' => 'fas fa-car',
        'medical-services' => 'fas fa-hospital',
        'educational-institutes' => 'fas fa-graduation-cap',
        'education' => 'fas fa-book',
        'catering-service' => 'fas fa-concierge-bell',
        'entertainment-center' => 'fas fa-theater-masks',
        'digital-service' => 'fas fa-laptop-code',
        'other' => 'fas fa-th-large',
    ];

    public static function forSlug(string $slug): string
    {
        $slug = strtolower(trim($slug));
        if ($slug === '') {
            return self::DEFAULT;
        }

        if (isset(self::SLUG_ICONS[$slug])) {
            return self::SLUG_ICONS[$slug];
        }

        foreach (self::SLUG_CONTAINS_ICONS as $needle => $icon) {
            if (str_contains($slug, $needle)) {
                return $icon;
            }
        }

        return self::DEFAULT;
    }
}
