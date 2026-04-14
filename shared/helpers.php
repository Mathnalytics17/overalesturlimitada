<?php

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        return $value === false || $value === null ? $default : $value;
    }
}


if (!function_exists('env_bool')) {
    function env_bool(string $key, bool $default = false): bool
    {
        $value = env($key, null);

        if ($value === null) {
            return $default;
        }

        if (is_bool($value)) {
            return $value;
        }

        $normalized = strtolower(trim((string) $value));
        return in_array($normalized, ['1', 'true', 'yes', 'on'], true);
    }
}

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        $base = dirname(__DIR__);
        return $path ? $base . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : $base;
    }
}

if (!function_exists('public_path')) {
    function public_path(string $path = ''): string
    {
        $base = base_path('public');
        return $path ? $base . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : $base;
    }
}

if (!function_exists('view_path')) {
    function view_path(string $path = ''): string
    {
        $base = base_path('views');
        return $path ? $base . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : $base;
    }
}

if (!function_exists('uuid')) {
    function uuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}

if (!function_exists('random_token')) {
    function random_token(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }
}

if (!function_exists('e')) {
    function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('now')) {
    function now(): string
    {
        return date('Y-m-d H:i:s');
    }
}

if (!function_exists('is_post')) {
    function is_post(): bool
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
    }
}

if (!function_exists('is_https')) {
    function is_https(): bool
    {
        if (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') {
            return true;
        }

        return (string) ($_SERVER['SERVER_PORT'] ?? '') === '443';
    }
}

if (!function_exists('secure_session_start')) {
    function secure_session_start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $sessionName = env('SESSION_NAME', 'ALESTURSESSID');
        if (is_string($sessionName) && $sessionName !== '') {
            session_name($sessionName);
        }

        session_start([
            'cookie_httponly' => true,
            'cookie_secure' => is_https(),
            'cookie_samesite' => env('SESSION_SAMESITE', 'Lax'),
            'use_strict_mode' => true,
            'use_only_cookies' => true,
        ]);
    }
}

if (!function_exists('runtime_path')) {
    function runtime_path(string $path = ''): string
    {
        $base = base_path('runtime');
        return $path ? $base . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : $base;
    }
}

if (!function_exists('app_log')) {
    function app_log(string $channel, string $message): void
    {
        $channel = preg_replace('/[^a-zA-Z0-9_-]/', '-', $channel) ?: 'app';
        $logDir = runtime_path('logs');

        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        $line = sprintf("[%s] %s\n", date('Y-m-d H:i:s'), $message);
        @file_put_contents($logDir . DIRECTORY_SEPARATOR . $channel . '.log', $line, FILE_APPEND | LOCK_EX);
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url): never
    {
        header('Location: ' . $url, true, 302);
        exit;
    }
}


if (!function_exists('normalize_phone')) {
    function normalize_phone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        return trim($digits);
    }
}
