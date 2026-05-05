<?php

namespace App\Http\Helpers;

class GeoSearch
{
  public static function getCoordinates($address, $apiKey)
  {
    $encodedAddress = urlencode($address);
    $url = "https://maps.googleapis.com/maps/api/geocode/json?address={$encodedAddress}&key={$apiKey}";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);

    if (!is_array($data) || ($data['status'] ?? '') !== 'OK') {
      return [
        'error' => is_array($data) ? ($data['status'] ?? 'INVALID_RESPONSE') : 'INVALID_RESPONSE'
      ];
    }

    $location = $data['results'][0]['geometry']['location'] ?? null;
    if (!is_array($location) || !isset($location['lat'], $location['lng'])) {
      return ['error' => 'NO_LOCATION'];
    }

    return [
      'lat' => $location['lat'],
      'lng' => $location['lng']
    ];
  }

  public static function getDistance($lat1, $lon1, $lat2, $lon2)
  {
    $earthRadius = 6371; // Radius of Earth in kilometers

    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a = sin($dLat / 2) * sin($dLat / 2) +
      cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
      sin($dLon / 2) * sin($dLon / 2);

    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    $distance = $earthRadius * $c;
    return floatval($distance); // in kilometers
  }
}
