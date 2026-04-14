<?php

namespace app\Core;

class Flash
{
    protected static function ensureSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public static function add(string $type, string $message, ?string $title = null): void
    {
        self::ensureSession();

        if (!isset($_SESSION['flash_toasts'])) {
            $_SESSION['flash_toasts'] = [];
        }

        $_SESSION['flash_toasts'][] = [
            'type' => $type,
            'title' => $title,
            'message' => $message,
        ];
    }

    public static function success(string $message, ?string $title = 'Éxito'): void
    {
        self::add('success', $message, $title);
    }

    public static function error(string $message, ?string $title = 'Error'): void
    {
        self::add('error', $message, $title);
    }

    public static function warning(string $message, ?string $title = 'Atención'): void
    {
        self::add('warning', $message, $title);
    }

    public static function info(string $message, ?string $title = 'Información'): void
    {
        self::add('info', $message, $title);
    }

    public static function set(string $key, mixed $value): void
    {
        self::ensureSession();

        if (!isset($_SESSION['flash_data'])) {
            $_SESSION['flash_data'] = [];
        }

        $_SESSION['flash_data'][$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::ensureSession();

        if (!array_key_exists($key, $_SESSION['flash_data'] ?? [])) {
            return $default;
        }

        $value = $_SESSION['flash_data'][$key];
        unset($_SESSION['flash_data'][$key]);

        return $value;
    }

    public static function has(string $key): bool
    {
        self::ensureSession();
        return array_key_exists($key, $_SESSION['flash_data'] ?? []);
    }

    public static function getAll(): array
    {
        self::ensureSession();

        $toasts = $_SESSION['flash_toasts'] ?? [];
        unset($_SESSION['flash_toasts']);

        return is_array($toasts) ? $toasts : [];
    }

    public static function clearData(): void
    {
        self::ensureSession();
        unset($_SESSION['flash_data']);
    }
}