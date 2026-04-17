<?php

namespace app\Core;

class RateLimiter
{
    public static function tooManyAttempts(string $key, int $maxAttempts, int $decaySeconds): bool
    {
        $record = self::getRecord($key, $decaySeconds);

        return ($record['attempts'] ?? 0) >= $maxAttempts;
    }

    public static function hit(string $key, int $decaySeconds): int
    {
        $record = self::getRecord($key, $decaySeconds);
        $record['attempts'] = (int) ($record['attempts'] ?? 0) + 1;
        $record['expires_at'] = time() + $decaySeconds;

        self::putRecord($key, $record);

        return $record['attempts'];
    }

    public static function clear(string $key): void
    {
        $path = self::pathForKey($key);
        if (is_file($path)) {
            @unlink($path);
        }
    }

    public static function availableIn(string $key, int $decaySeconds): int
    {
        $record = self::getRecord($key, $decaySeconds);
        $expiresAt = (int) ($record['expires_at'] ?? 0);

        return max(0, $expiresAt - time());
    }

    protected static function getRecord(string $key, int $decaySeconds): array
    {
        $path = self::pathForKey($key);

        if (!is_file($path)) {
            return [
                'attempts' => 0,
                'expires_at' => time() + $decaySeconds,
            ];
        }

        $contents = @file_get_contents($path);
        $record = is_string($contents) ? json_decode($contents, true) : null;

        if (!is_array($record)) {
            return [
                'attempts' => 0,
                'expires_at' => time() + $decaySeconds,
            ];
        }

        if ((int) ($record['expires_at'] ?? 0) < time()) {
            self::clear($key);

            return [
                'attempts' => 0,
                'expires_at' => time() + $decaySeconds,
            ];
        }

        return $record;
    }

    protected static function putRecord(string $key, array $record): void
    {
        $directory = runtime_path('cache/rate_limits');
        if (!is_dir($directory)) {
            @mkdir($directory, 0755, true);
        }

        @file_put_contents(self::pathForKey($key), json_encode($record), LOCK_EX);
    }

    protected static function pathForKey(string $key): string
    {
        return runtime_path('cache/rate_limits/' . hash('sha256', $key) . '.json');
    }
}
