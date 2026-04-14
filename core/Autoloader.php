<?php

namespace app\Core;

class Autoloader
{
    public static function register(string $basePath): void
    {
        spl_autoload_register(function (string $class) use ($basePath): void {
            $prefix = 'app\\';

            if (!str_starts_with($class, $prefix)) {
                return;
            }

            $relative = substr($class, strlen($prefix));
            $segments = explode('\\', $relative);

            if ($segments === []) {
                return;
            }

            $segments[0] = lcfirst($segments[0]);
            $path = rtrim($basePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $segments) . '.php';

            if (is_file($path)) {
                require_once $path;
                return;
            }

            $resolved = self::resolveCaseInsensitivePath($path);
            if ($resolved !== null) {
                require_once $resolved;
            }
        });
    }

    protected static function resolveCaseInsensitivePath(string $path): ?string
    {
        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        $parts = array_values(array_filter(explode(DIRECTORY_SEPARATOR, $path), static fn ($part) => $part !== ''));

        if ($parts === []) {
            return null;
        }

        $prefix = DIRECTORY_SEPARATOR;
        if (preg_match('/^[A-Za-z]:$/', $parts[0]) === 1) {
            $prefix = array_shift($parts) . DIRECTORY_SEPARATOR;
        }

        $current = rtrim($prefix, DIRECTORY_SEPARATOR);
        foreach ($parts as $part) {
            $directory = $current === '' ? DIRECTORY_SEPARATOR : $current;

            if (!is_dir($directory)) {
                return null;
            }

            $matches = glob($directory . DIRECTORY_SEPARATOR . '*') ?: [];
            $found = null;

            foreach ($matches as $match) {
                if (strcasecmp(basename($match), $part) === 0) {
                    $found = $match;
                    break;
                }
            }

            if ($found === null) {
                return null;
            }

            $current = $found;
        }

        return is_file($current) ? $current : null;
    }
}
