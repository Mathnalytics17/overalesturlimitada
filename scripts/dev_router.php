<?php

$publicRoot = realpath(__DIR__ . '/../public');
$requestPath = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$requestedFile = realpath($publicRoot . str_replace('/', DIRECTORY_SEPARATOR, $requestPath));

if (
    $requestPath !== '/'
    && is_string($requestedFile)
    && str_starts_with($requestedFile, $publicRoot . DIRECTORY_SEPARATOR)
    && is_file($requestedFile)
) {
    $extension = strtolower(pathinfo($requestedFile, PATHINFO_EXTENSION));
    $mime = match ($extension) {
        'css' => 'text/css; charset=UTF-8',
        'js' => 'application/javascript; charset=UTF-8',
        'svg' => 'image/svg+xml',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        default => mime_content_type($requestedFile),
    };

    if (is_string($mime) && $mime !== '') {
        header('Content-Type: ' . $mime);
    }

    header('Content-Length: ' . filesize($requestedFile));
    readfile($requestedFile);
    exit;
}

require $publicRoot . DIRECTORY_SEPARATOR . 'index.php';
