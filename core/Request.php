<?php

namespace app\Core;

class Request
{
    protected array $routeParams = [];

    public function getPath(): string
    {
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        $position = strpos($path, '?');

        if ($position !== false) {
            $path = substr($path, 0, $position);
        }

        $path = rawurldecode($path);
        $path = '/' . ltrim($path, '/');

        if ($path === '') {
            return '/';
        }

        $normalized = rtrim($path, '/');
        return $normalized === '' ? '/' : $normalized;
    }

    public function getMethod(): string
    {
        return strtolower($_SERVER['REQUEST_METHOD'] ?? 'get');
    }

    public function isMethod(string $method): bool
    {
        return $this->getMethod() === strtolower($method);
    }

    public function getQueryParams(): array
    {
        return $this->normalizeArray($_GET);
    }

    public function getPostParams(): array
    {
        return $this->normalizeArray($_POST);
    }

    public function getBody(): array
    {
        if ($this->isMethod('get')) {
            return $this->getQueryParams();
        }

        if ($this->isMethod('post')) {
            return $this->getPostParams();
        }

        return [];
    }

    public function all(): array
    {
        return array_merge($this->getQueryParams(), $this->getPostParams());
    }

    public function input(string $key, mixed $default = null): mixed
    {
        $data = $this->all();
        return $data[$key] ?? $default;
    }

    public function files(): array
    {
        return $_FILES;
    }

    public function file(string $key): mixed
    {
        return $_FILES[$key] ?? null;
    }

    public function setRouteParams(array $params): void
    {
        $this->routeParams = $params;
    }

    public function route(string $key, mixed $default = null): mixed
    {
        return $this->routeParams[$key] ?? $default;
    }

    public function routeParams(): array
    {
        return $this->routeParams;
    }

    public function getIpAddress(): ?string
    {
        return client_ip();
    }

    public function getUserAgent(): ?string
    {
        $agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        return is_string($agent) ? trim($agent) : null;
    }

    public function isJson(): bool
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        return is_string($contentType) && str_contains(strtolower($contentType), 'application/json');
    }

    protected function normalizeArray(array $data): array
    {
        $normalized = [];

        foreach ($data as $key => $value) {
            $normalized[$key] = $this->normalizeValue($value);
        }

        return $normalized;
    }

    protected function normalizeValue(mixed $value): mixed
    {
        if (is_array($value)) {
            $normalized = [];
            foreach ($value as $key => $item) {
                $normalized[$key] = $this->normalizeValue($item);
            }
            return $normalized;
        }

        if (is_string($value)) {
            return trim(str_replace("\0", '', $value));
        }

        return $value;
    }
}
