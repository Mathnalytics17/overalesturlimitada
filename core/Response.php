<?php

namespace app\Core;

class Response
{
    public function setStatusCode(int $code): void
    {
        http_response_code($code);
    }

    public function header(string $name, string $value, bool $replace = true): void
    {
        header($name . ': ' . $value, $replace);
    }

    public function redirect(string $url, int $statusCode = 302): never
    {
        header('Location: ' . $url, true, $statusCode);
        exit;
    }

    public function json(array $payload, int $statusCode = 200): never
    {
        $this->setStatusCode($statusCode);
        $this->header('Content-Type', 'application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public function applySecurityHeaders(): void
    {
        if (headers_sent()) {
            return;
        }

        $csp = [
            "default-src 'self'",
            "base-uri 'self'",
            "frame-ancestors 'self'",
            "form-action 'self'",
            "object-src 'none'",
            "script-src 'self' 'unsafe-inline' https://challenges.cloudflare.com",
            "style-src 'self' 'unsafe-inline'",
            "img-src 'self' data: blob:",
            "font-src 'self' data:",
            "connect-src 'self' https://challenges.cloudflare.com https:",
            "media-src 'self' blob:",
            "frame-src 'self' https://challenges.cloudflare.com",
        ];

        if (is_https()) {
            $csp[] = 'upgrade-insecure-requests';
        }

        $this->header('Content-Security-Policy', implode('; ', $csp));
        $this->header('X-Frame-Options', 'SAMEORIGIN');
        $this->header('X-Content-Type-Options', 'nosniff');
        $this->header('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->header('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
        $this->header('Cross-Origin-Opener-Policy', 'same-origin');
        $this->header('Cross-Origin-Resource-Policy', 'same-site');
        $this->header('X-Permitted-Cross-Domain-Policies', 'none');
        $this->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $this->header('Pragma', 'no-cache');
        $this->header('Expires', '0');

        if (is_https()) {
            $this->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }
    }
}
