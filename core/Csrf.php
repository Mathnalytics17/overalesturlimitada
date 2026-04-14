<?php

namespace app\Core;

class Csrf
{
    protected const SESSION_KEY = '_csrf_token';

    public static function token(): string
    {
        secure_session_start();

        if (empty($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::SESSION_KEY];
    }

    public static function input(): string
    {
        return '<input type="hidden" name="_csrf" value="' . e(self::token()) . '">';
    }

    public static function validate(?string $token): bool
    {
        secure_session_start();

        $sessionToken = $_SESSION[self::SESSION_KEY] ?? null;

        if (!is_string($sessionToken) || !is_string($token) || $token === '') {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }

    public static function regenerate(): void
    {
        secure_session_start();
        $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
    }
}
