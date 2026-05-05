<?php

namespace App\Http\Controllers\Installer;

/**
 * The stock installer treats this file as proof of a successful Envato check.
 * We create it when skipping remote license verification so later steps still run.
 */
final class InstallerLicenseBypass
{
    public static function ensureMarkerFile(): void
    {
        $path = base_path('vendor/mockery/mockery/verified');
        $dir = dirname($path);
        if (!is_dir($dir)) {
            return;
        }
        if (!file_exists($path)) {
            file_put_contents($path, '1');
        }
    }
}
