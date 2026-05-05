<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class WritePublicFavicon extends Command
{
  protected $signature = 'favicon:write';

  protected $description = 'Write minimal public/favicon.ico so the web server can serve it without PHP';

  public function handle(): int
  {
    $path = public_path('favicon.ico');
    $b64 = 'AAABAAEAEBAAAAEAIABoBAAAFgAAACgAAAAQAAAAIAAAAAEAIAAAAAAAAAQAAAAAAAAAAAAAAAAAAAAA';
    file_put_contents($path, base64_decode($b64));
    $this->info('Wrote ' . $path);

    return 0;
  }
}
