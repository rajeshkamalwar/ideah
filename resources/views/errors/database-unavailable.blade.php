<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Service unavailable | {{ config('app.name') }}</title>
  <style>
    body { font-family: system-ui, sans-serif; max-width: 40rem; margin: 3rem auto; padding: 0 1rem; line-height: 1.5; color: #222; }
    h1 { font-size: 1.25rem; }
    code { background: #f4f4f4; padding: 0.2em 0.4em; border-radius: 4px; font-size: 0.9em; }
    .hint { color: #555; font-size: 0.95rem; margin-top: 1.5rem; }
  </style>
</head>
<body>
  <h1>Unable to connect to the database</h1>
  <p>The site cannot reach MySQL. On shared hosting, check <code>.env</code> (database name, user, password, host, often <code>localhost</code>) and that the database exists in the control panel.</p>
  @if (config('app.debug') && isset($message))
    <p class="hint"><strong>Debug:</strong> {{ $message }}</p>
  @endif
  <p class="hint">After fixing credentials, run <code>php artisan config:clear</code> on the server (SSH) or delete <code>bootstrap/cache/config.php</code> if present.</p>
</body>
</html>
