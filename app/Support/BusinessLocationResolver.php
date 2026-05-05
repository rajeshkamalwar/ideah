<?php

namespace App\Support;

/**
 * Parses free-text "City / Primary Location" cells (IdeahHub CSV column F) into
 * India-focused city + state for listing_content country/state/city FKs.
 */
final class BusinessLocationResolver
{
    private const COUNTRY = 'India';

    /** @var array<string, string> lowercase keyword in raw text => "City|State" */
    private const CITY_KEYWORD_TO = [
        'panchkula' => 'Panchkula|Haryana',
        'zirakpur' => 'Zirakpur|Punjab',
        'mohali' => 'Mohali|Punjab',
        'kurukshetra' => 'Kurukshetra|Haryana',
        'ludhiana' => 'Ludhiana|Punjab',
        'jalandhar' => 'Jalandhar|Punjab',
        'patiala' => 'Patiala|Punjab',
        'kapurthala' => 'Kapurthala|Punjab',
        'ambala' => 'Ambala|Haryana',
        'chd' => 'Chandigarh|Chandigarh',
        'chandigarh' => 'Chandigarh|Chandigarh',
        'navi mumbai' => 'Navi Mumbai|Maharashtra',
        'mumbai' => 'Mumbai|Maharashtra',
        'delhi' => 'Delhi|Delhi',
        'new delhi' => 'New Delhi|Delhi',
        'mohalu' => 'Mohali|Punjab', // common typo
        'derabassi' => 'Dera Bassi|Punjab',
        'dera bassi' => 'Dera Bassi|Punjab',
        'tricity' => 'Mohali|Punjab',
    ];

    /** Lowercase last-token state names (common CSV endings) */
    private const STATE_NAMES_LOWER = [
        'punjab', 'haryana', 'maharashtra', 'delhi', 'gujarat', 'karnataka',
        'tamil nadu', 'telangana', 'kerala', 'rajasthan', 'uttar pradesh',
        'west bengal', 'bihar', 'odisha', 'madhya pradesh', 'himachal pradesh',
        'uttarakhand', 'jharkhand', 'assam', 'goa', 'chandigarh',
    ];

    /**
     * @return array{city: string, state: string, country: string, address: string}|null
     *         null if input empty after cleanup
     */
    public function resolve(string $raw): ?array
    {
        $address = $this->normalizeRaw($raw);
        if ($address === '') {
            return null;
        }

        $fromKeyword = $this->matchKeyword($address);

        $comma = $this->tryCommaSplit($address);
        if ($comma !== null) {
            return $this->finalize($comma['city'], $comma['state'], $address, $fromKeyword);
        }

        $trailingState = $this->tryTrailingState($address);
        if ($trailingState !== null) {
            return $this->finalize($trailingState['city'], $trailingState['state'], $address, $fromKeyword);
        }

        if ($fromKeyword !== null) {
            [$city, $state] = explode('|', $fromKeyword, 2);

            return [
                'country' => self::COUNTRY,
                'state' => $state,
                'city' => $city,
                'address' => $address,
            ];
        }

        $title = $this->titleCaseWords($address);

        return [
            'country' => self::COUNTRY,
            'state' => 'Other',
            'city' => $title,
            'address' => $address,
        ];
    }

    private function finalize(string $city, string $state, string $address, ?string $fromKeyword): array
    {
        $city = trim($city);
        $state = trim($state);
        if ($city === '') {
            if ($fromKeyword !== null) {
                [$city, $state] = explode('|', $fromKeyword, 2);
            } else {
                $city = $this->titleCaseWords($address);
                $state = 'Other';
            }
        }
        if ($state === '') {
            $state = 'Other';
        }

        return [
            'country' => self::COUNTRY,
            'state' => $this->normalizeStateName($state),
            'city' => $this->titleCaseWords($city),
            'address' => $address,
        ];
    }

    private function normalizeRaw(string $raw): string
    {
        $s = preg_replace('/[\r\n]+/', ' ', $raw) ?? $raw;
        $s = preg_replace('/\s+/', ' ', trim($s)) ?? trim($s);
        $s = preg_replace('/\b\d{6}\b/', '', $s) ?? $s;

        return trim($s);
    }

    private function matchKeyword(string $address): ?string
    {
        $lower = mb_strtolower($address);
        foreach (self::CITY_KEYWORD_TO as $kw => $pair) {
            if (str_contains($lower, $kw)) {
                return $pair;
            }
        }

        return null;
    }

    /**
     * First comma splits "City, State" (e.g. "Kapurthala, Punjab", "Navi Mumbai, Maharashtra").
     *
     * @return array{city: string, state: string}|null
     */
    private function tryCommaSplit(string $address): ?array
    {
        $pos = mb_strpos($address, ',');
        if ($pos === false) {
            return null;
        }
        $left = trim(mb_substr($address, 0, $pos));
        $right = trim(mb_substr($address, $pos + 1));
        $right = preg_replace('/\s+(india|ind)\s*$/iu', '', $right) ?? $right;
        $right = trim((string) $right);
        if ($left === '' || $right === '') {
            return null;
        }

        return ['city' => $left, 'state' => $this->normalizeStateName($right)];
    }

    private const COMPOUND_STATES = [
        'Tamil Nadu', 'West Bengal', 'Himachal Pradesh', 'Andhra Pradesh',
        'Arunachal Pradesh', 'Madhya Pradesh', 'Uttar Pradesh', 'Uttarakhand',
    ];

    /**
     * @return array{city: string, state: string}|null
     */
    private function tryTrailingState(string $address): ?array
    {
        $lower = mb_strtolower($address);
        foreach (self::COMPOUND_STATES as $st) {
            $suffix = mb_strtolower($st);
            if (str_ends_with($lower, $suffix)) {
                $cityPart = trim(mb_substr($address, 0, mb_strlen($address) - mb_strlen($st)));
                $cityPart = preg_replace('/\s+$/u', '', $cityPart) ?? $cityPart;
                if ($cityPart !== '') {
                    return ['city' => $cityPart, 'state' => $this->normalizeStateName($st)];
                }
            }
        }

        $parts = preg_split('/\s+/u', $address) ?: [];
        if (count($parts) < 2) {
            return null;
        }

        $last = mb_strtolower((string) array_pop($parts));
        if (! in_array($last, self::STATE_NAMES_LOWER, true)) {
            return null;
        }

        $cityPart = trim(implode(' ', $parts));
        if ($cityPart === '') {
            return null;
        }

        return ['city' => $cityPart, 'state' => $this->normalizeStateName($last)];
    }

    private function normalizeStateName(string $name): string
    {
        $n = trim($name);
        $map = [
            'chandigarh' => 'Chandigarh',
            'punjab' => 'Punjab',
            'haryana' => 'Haryana',
            'maharashtra' => 'Maharashtra',
            'delhi' => 'Delhi',
            'gujarat' => 'Gujarat',
            'karnataka' => 'Karnataka',
            'tamil nadu' => 'Tamil Nadu',
            'telangana' => 'Telangana',
            'kerala' => 'Kerala',
            'rajasthan' => 'Rajasthan',
            'uttar pradesh' => 'Uttar Pradesh',
            'west bengal' => 'West Bengal',
            'bihar' => 'Bihar',
            'odisha' => 'Odisha',
            'madhya pradesh' => 'Madhya Pradesh',
            'himachal pradesh' => 'Himachal Pradesh',
            'uttarakhand' => 'Uttarakhand',
            'jharkhand' => 'Jharkhand',
            'assam' => 'Assam',
            'goa' => 'Goa',
            'other' => 'Other',
        ];

        $key = mb_strtolower($n);

        return $map[$key] ?? $this->titleCaseWords($n);
    }

    private function titleCaseWords(string $s): string
    {
        $s = trim($s);
        if ($s === '') {
            return $s;
        }

        return mb_convert_case($s, MB_CASE_TITLE, 'UTF-8');
    }
}
