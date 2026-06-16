<?php
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = '/' . ltrim($path, '/');

if (str_starts_with($path, '/admin')) {
    include __DIR__ . '/_404_admin.php';
    return;
}

include __DIR__ . '/_404_public.php';
