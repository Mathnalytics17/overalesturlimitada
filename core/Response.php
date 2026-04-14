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

        $this->header('X-Frame-Options', 'SAMEORIGIN');
        $this->header('X-Content-Type-Options', 'nosniff');
        $this->header('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->header('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        if (is_https()) {
            $this->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }
    }
}
