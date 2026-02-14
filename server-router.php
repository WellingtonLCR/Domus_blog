<?php

/**
 * Router para o PHP Built-in Server.
 *
 * Uso:
 *   php -S 127.0.0.1:8001 server-router.php
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/');

$publicPath = __DIR__ . DIRECTORY_SEPARATOR . 'public';
$publicFile = realpath($publicPath . $uri);

// Se existir arquivo estático dentro de /public, serve ele diretamente.
if ($publicFile !== false && str_starts_with($publicFile, realpath($publicPath)) && is_file($publicFile)) {
    $mimeType = function_exists('mime_content_type') ? @mime_content_type($publicFile) : null;
    if (is_string($mimeType) && $mimeType !== '') {
        header('Content-Type: ' . $mimeType);
    }

    $size = @filesize($publicFile);
    if ($size !== false) {
        header('Content-Length: ' . $size);
    }

    readfile($publicFile);
    exit;
}

// Caso contrário, deixa o Laravel tratar via public/index.php.
require $publicPath . DIRECTORY_SEPARATOR . 'index.php';
