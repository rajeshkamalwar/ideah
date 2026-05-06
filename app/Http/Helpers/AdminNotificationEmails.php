<?php

namespace App\Http\Helpers;

/**
 * Parses admin "Mail To Admin" settings: multiple emails separated by commas, semicolons, pipes, or new lines.
 */
class AdminNotificationEmails
{
    /**
     * @return list<string>
     */
    public static function parseList(?string $value): array
    {
        if ($value === null || trim($value) === '') {
            return [];
        }

        $parts = preg_split('/[\r\n,;|]+/', $value, -1, PREG_SPLIT_NO_EMPTY);
        $out = [];

        foreach ($parts as $part) {
            $e = trim($part);
            if ($e === '') {
                continue;
            }
            if (filter_var($e, FILTER_VALIDATE_EMAIL)) {
                $out[] = $e;
            }
        }

        return array_values(array_unique($out));
    }

    public static function normalizeToStorage(string $input): string
    {
        return implode(', ', self::parseList($input));
    }

    public static function formatForForm(?string $stored): string
    {
        $list = self::parseList($stored);

        return implode("\n", $list);
    }
}
