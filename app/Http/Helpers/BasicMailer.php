<?php

namespace App\Http\Helpers;

use App\Models\BasicSettings\Basic;
use Illuminate\Support\Facades\Config;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class BasicMailer
{
  public static function sendMail($data)
  {
    // get the website title & mail's smtp information from db
    $info = Basic::select('website_title', 'smtp_status', 'smtp_host', 'smtp_port', 'encryption', 'smtp_username', 'smtp_password', 'from_mail', 'from_name')
      ->first();

    // if smtp status == 1, then set some value for PHPMailer
    if ($info->smtp_status == 1) {
      try {
        $smtp = [
          'transport' => 'smtp',
          'host' => $info->smtp_host,
          'port' => $info->smtp_port,
          'encryption' => $info->encryption,
          'username' => $info->smtp_username,
          'password' => $info->smtp_password,
          'timeout' => null,
          'auth_mode' => null,
        ];
        Config::set('mail.mailers.smtp', $smtp);
      } catch (\Exception $e) {
        Session::flash('error', $e->getMessage());
        return back();
      }
    }

    try {
      $recipients = $data['recipient'];
      if (is_string($recipients)) {
        $recipients = AdminNotificationEmails::parseList($recipients);
      }
      if (! is_array($recipients)) {
        $recipients = [$recipients];
      }
      if ($recipients === []) {
        return;
      }

      $body = static::embedLocalImages((string) ($data['body'] ?? ''));

      Mail::send([], [], function (Message $message) use ($data, $info, $recipients, $body) {
        $fromMail = $info->from_mail;
        $fromName = $info->from_name;
        $message->to($recipients)
          ->subject($data['subject'])
          ->from($fromMail, $fromName)
          ->html($body);

        if (array_key_exists('invoice', $data)) {
          $message->attach($data['invoice'], [
            'as' => 'Invoice',
            'mime' => 'application/pdf',
          ]);
        }
      });
      if (array_key_exists('sessionMessage', $data)) {
        Session::flash('success', $data['sessionMessage']);
      }
    } catch (\Exception $e) {
      Session::flash('warning', 'Mail could not be sent. Mailer Error: ' . $e);
    }
    return;
  }

  /**
   * Inline local site images as data URIs so they render without remote image loading.
   * Does not modify stored campaign HTML — only used in the send path.
   */
  public static function embedLocalImages(string $html): string
  {
    if ($html === '') {
      return $html;
    }

    $publicRoot = realpath((string) public_path());
    if ($publicRoot === false) {
      return $html;
    }

    return (string) preg_replace_callback(
      '/<img\s[^>]*\bsrc\s*=\s*(["\'])([^"\']+)\1/i',
      function (array $m) use ($publicRoot) {
        $quote = $m[1];
        $srcAttr = $m[2];
        $rawSrc = html_entity_decode($srcAttr, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $dataUri = static::resolveLocalImageDataUri($rawSrc, $publicRoot);
        if ($dataUri === null) {
          return $m[0];
        }
        $old = $quote . $srcAttr . $quote;
        $new = $quote . $dataUri . $quote;
        $pos = strpos($m[0], $old);
        if ($pos === false) {
          return $m[0];
        }

        return substr_replace($m[0], $new, $pos, strlen($old));
      },
      $html
    );
  }

  private static function resolveLocalImageDataUri(string $src, string $publicRoot): ?string
  {
    $src = trim($src);
    if ($src === '' || stripos($src, 'data:') === 0) {
      return null;
    }

    $relative = static::urlToPublicRelativePath($src);
    if ($relative === null || $relative === '') {
      return null;
    }

    $relative = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relative);
    $candidate = $publicRoot . DIRECTORY_SEPARATOR . $relative;
    $fullPath = realpath($candidate);

    if ($fullPath === false || strpos($fullPath, $publicRoot . DIRECTORY_SEPARATOR) !== 0) {
      return null;
    }

    $ext = strtolower((string) pathinfo($fullPath, PATHINFO_EXTENSION));
    $mimeMap = [
      'jpg'  => 'image/jpeg',
      'jpeg' => 'image/jpeg',
      'png'  => 'image/png',
      'gif'  => 'image/gif',
      'webp' => 'image/webp',
    ];
    if (! isset($mimeMap[$ext])) {
      return null;
    }

    if (! is_readable($fullPath)) {
      return null;
    }

    $binary = @file_get_contents($fullPath);
    if ($binary === false) {
      return null;
    }

    // Avoid huge mails (host/SMTP limits)
    if (strlen($binary) > 2_500_000) {
      return null;
    }

    return 'data:' . $mimeMap[$ext] . ';base64,' . base64_encode($binary);
  }

  /**
   * Map absolute site URL or root-relative path to path relative to public/.
   */
  private static function urlToPublicRelativePath(string $src): ?string
  {
    $base = rtrim((string) config('app.url'), '/');
    $assetBase = rtrim((string) (config('app.asset_url') ?: ''), '/');
    $bases = array_unique(array_filter([
      $base,
      $assetBase,
      $base !== '' ? preg_replace('#^https:#i', 'http:', $base) : '',
      $base !== '' ? preg_replace('#^http:#i', 'https:', $base) : '',
      $assetBase !== '' ? preg_replace('#^https:#i', 'http:', $assetBase) : '',
      $assetBase !== '' ? preg_replace('#^http:#i', 'https:', $assetBase) : '',
    ]));

    foreach ($bases as $b) {
      if ($b !== '' && str_starts_with($src, $b . '/')) {
        return ltrim(substr($src, strlen($b)), '/');
      }
    }

    if (str_starts_with($src, '//')) {
      $host = parse_url($base, PHP_URL_HOST);
      $scheme = parse_url($base, PHP_URL_SCHEME) ?: 'https';
      if ($host) {
        $abs = $scheme . ':' . $src;
        foreach ($bases as $b) {
          if (str_starts_with($abs, $b . '/')) {
            return ltrim(substr($abs, strlen($b)), '/');
          }
        }
      }
    }

    if (str_starts_with($src, '/')) {
      return ltrim($src, '/');
    }

    return null;
  }
}
