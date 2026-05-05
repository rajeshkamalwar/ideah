<?php

/**
 * Alternative entry point when the FULL Laravel app lives in the web root
 * (e.g. Hostinger public_html contains vendor/, app/, bootstrap/, index.php together).
 *
 * On the server: back up index.php, then replace it with this file (rename to index.php).
 * Standard public/index.php uses ../vendor and breaks that layout.
 */

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists(__DIR__ . '/storage/framework/maintenance.php')) {
    require __DIR__ . '/storage/framework/maintenance.php';
}

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = tap($kernel->handle(
    $request = Request::capture()
))->send();

$kernel->terminate($request, $response);
