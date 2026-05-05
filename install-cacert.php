<?php
/**
 * One-time: downloads Mozilla CA bundle into storage/certs/cacert.pem
 * Run: C:\xampp\php\php.exe install-cacert.php
 */
$dir = __DIR__ . '/storage/certs';
$dest = $dir . '/cacert.pem';
$url = 'https://curl.se/ca/cacert.pem';

if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => 0,
    CURLOPT_TIMEOUT => 120,
]);
$data = curl_exec($ch);
$err = curl_error($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($data === false || $code >= 400) {
    fwrite(STDERR, "Download failed: HTTP $code $err\n");
    exit(1);
}

if (strlen($data) < 10000 || strpos($data, 'BEGIN CERTIFICATE') === false) {
    fwrite(STDERR, "Downloaded file does not look like a CA bundle.\n");
    exit(1);
}

file_put_contents($dest, $data);
echo "OK: $dest (" . strlen($data) . " bytes)\n";
