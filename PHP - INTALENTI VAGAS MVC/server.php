<?php

/**
 * Laravel - A PHP Framework For Web Artisans
 *
 * @package  Laravel
 * @author   Taylor Otwell <taylor@laravel.com>
 */

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

// This file allows us to emulate Apache's "mod_rewrite" functionality from the
// built-in PHP web server. This provides a convenient way to test the
// application without having installed a "real" web server software here.
// Emulate serving static files from the "public" directory
if ($uri !== '/' && file_exists(__DIR__ . '/public' . $uri)) {
    $filePath = __DIR__ . '/public' . $uri;
    $ext = pathinfo($filePath, PATHINFO_EXTENSION);

    $mimes = [
        'css' => 'text/css',
        'js'  => 'application/javascript',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
        'eot' => 'application/vnd.ms-fontobject'
    ];

    $contentType = $mimes[$ext] ?? mime_content_type($filePath);
    header("Content-Type: $contentType");
    readfile($filePath);
    exit;
}

$_GET['url'] = ltrim($uri, '/');

require_once __DIR__ . '/public/index.php';
