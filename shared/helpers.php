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

if (!function_exists('env_int')) {
    function env_int(string $key, int $default = 0): int
    {
        $value = env($key, null);

        if ($value === null || $value === '') {
            return $default;
        }

        if (is_int($value)) {
            return $value;
        }

        return is_numeric($value) ? (int) $value : $default;
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

if (!function_exists('app_url')) {
    function app_url(string $path = ''): string
    {
        $base = rtrim((string) env('APP_URL', ''), '/');

        if ($base === '') {
            $scheme = is_https() ? 'https' : 'http';
            $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
            $base = $scheme . '://' . $host;
        }

        $path = ltrim($path, '/');
        return $path === '' ? $base : $base . '/' . $path;
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
        if (env_bool('FORCE_HTTPS', false)) {
            return true;
        }

        if (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') {
            return true;
        }

        if ((string) ($_SERVER['SERVER_PORT'] ?? '') === '443') {
            return true;
        }

        $trustedProxyHeaders = env_bool('TRUST_PROXY_HEADERS', true);

        if ($trustedProxyHeaders) {
            $forwardedProto = strtolower(trim((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')));
            if ($forwardedProto !== '') {
                $parts = array_map('trim', explode(',', $forwardedProto));
                if (in_array('https', $parts, true)) {
                    return true;
                }
            }

            $forwardedSsl = strtolower(trim((string) ($_SERVER['HTTP_X_FORWARDED_SSL'] ?? '')));
            if (in_array($forwardedSsl, ['1', 'on', 'true'], true)) {
                return true;
            }

            $frontEndHttps = strtolower(trim((string) ($_SERVER['HTTP_FRONT_END_HTTPS'] ?? '')));
            if ($frontEndHttps === 'on') {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('client_ip')) {
    function client_ip(): ?string
    {
        $trustedProxyHeaders = env_bool('TRUST_PROXY_HEADERS', true);

        if ($trustedProxyHeaders) {
            $forwardedFor = trim((string) ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? ''));
            if ($forwardedFor !== '') {
                foreach (explode(',', $forwardedFor) as $candidate) {
                    $candidate = trim($candidate);
                    if (filter_var($candidate, FILTER_VALIDATE_IP)) {
                        return $candidate;
                    }
                }
            }
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        return is_string($ip) && filter_var($ip, FILTER_VALIDATE_IP) ? $ip : null;
    }
}

if (!function_exists('rate_limit_key')) {
    function rate_limit_key(string $action, ?string $ipAddress = null, ?string $identifier = null): string
    {
        $parts = ['rate_limit', trim($action)];
        $parts[] = $ipAddress !== null && $ipAddress !== '' ? $ipAddress : 'unknown';

        if ($identifier !== null && $identifier !== '') {
            $parts[] = trim(mb_strtolower($identifier));
        }

        return implode('|', $parts);
    }
}

if (!function_exists('turnstile_enabled')) {
    function turnstile_enabled(): bool
    {
        return trim((string) env('TURNSTILE_SITE_KEY', '')) !== ''
            && trim((string) env('TURNSTILE_SECRET_KEY', '')) !== '';
    }
}

if (!function_exists('turnstile_widget_html')) {
    function turnstile_widget_html(): string
    {
        if (!turnstile_enabled()) {
            return '';
        }

        static $scriptRendered = false;

        $siteKey = e((string) env('TURNSTILE_SITE_KEY', ''));
        $theme = e((string) env('TURNSTILE_THEME', 'auto'));
        $html = '<div class="cf-turnstile" data-sitekey="' . $siteKey . '" data-theme="' . $theme . '"></div>';

        if (!$scriptRendered) {
            $html .= '<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>';
            $scriptRendered = true;
        }

        return $html;
    }
}

if (!function_exists('validate_turnstile')) {
    function validate_turnstile(?string $token = null, ?string $ipAddress = null): array
    {
        $service = new \app\Services\Security\TurnstileService();
        return $service->verify($token, $ipAddress);
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

        $sessionLifetimeMinutes = max(1, env_int('SESSION_LIFETIME_MINUTES', 120));
        $cookieLifetime = $sessionLifetimeMinutes * 60;
        $sameSite = (string) env('SESSION_SAMESITE', 'Lax');
        $sameSite = in_array($sameSite, ['Lax', 'Strict', 'None'], true) ? $sameSite : 'Lax';
        $cookieSecure = env_bool('SESSION_SECURE_COOKIE', is_https());

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_secure', $cookieSecure ? '1' : '0');
        ini_set('session.gc_maxlifetime', (string) $cookieLifetime);

        session_start([
            'cookie_lifetime' => $cookieLifetime,
            'cookie_path' => '/',
            'cookie_httponly' => true,
            'cookie_secure' => $cookieSecure,
            'cookie_samesite' => $sameSite,
            'use_strict_mode' => true,
            'use_only_cookies' => true,
        ]);

        $lastActivity = (int) ($_SESSION['_last_activity_at'] ?? 0);
        $now = time();

        if ($lastActivity > 0 && ($now - $lastActivity) > $cookieLifetime) {
            $_SESSION = [];

            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', [
                    'expires' => time() - 42000,
                    'path' => $params['path'] ?: '/',
                    'domain' => $params['domain'] ?: '',
                    'secure' => (bool) ($params['secure'] ?? false),
                    'httponly' => (bool) ($params['httponly'] ?? true),
                    'samesite' => $params['samesite'] ?? $sameSite,
                ]);
            }

            session_destroy();
            session_start([
                'cookie_lifetime' => $cookieLifetime,
                'cookie_path' => '/',
                'cookie_httponly' => true,
                'cookie_secure' => $cookieSecure,
                'cookie_samesite' => $sameSite,
                'use_strict_mode' => true,
                'use_only_cookies' => true,
            ]);
        }

        $_SESSION['_last_activity_at'] = $now;
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

if (!function_exists('security_event')) {
    function security_event(string $event, array $context = []): void
    {
        $payload = [
            'event' => $event,
            'time' => date('c'),
            'ip' => client_ip(),
            'context' => $context,
        ];

        app_log('security', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
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
