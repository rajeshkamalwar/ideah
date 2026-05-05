<?php
/**
 * One-off sync: copy Laravel `public/` → `_public_html/` for Hostinger web-root uploads.
 * Run from project: php sync-public-to-public-html.php
 */
$base = __DIR__;
$src = $base . DIRECTORY_SEPARATOR . 'public';
$dst = $base . DIRECTORY_SEPARATOR . '_public_html';

if (! is_dir($src)) {
    fwrite(STDERR, "Missing directory: {$src}\n");
    exit(1);
}

if (! is_dir($dst)) {
    mkdir($dst, 0755, true);
}

$it = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($src, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

$copied = 0;
$dirs = 0;

foreach ($it as $item) {
    $rel = substr($item->getPathname(), strlen($src) + 1);
    $target = $dst . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);

    if ($item->isDir()) {
        if (! is_dir($target)) {
            mkdir($target, 0755, true);
            $dirs++;
        }
        continue;
    }

    $parent = dirname($target);
    if (! is_dir($parent)) {
        mkdir($parent, 0755, true);
    }
    if (! copy($item->getPathname(), $target)) {
        fwrite(STDERR, "Copy failed: {$target}\n");
        exit(1);
    }
    $copied++;
}

echo "Synced public/ → _public_html/ (files: {$copied}, dirs created: {$dirs}).\n";
