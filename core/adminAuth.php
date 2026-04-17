<?php

namespace app\Core;

use app\Models\AdminLoginLog;
use app\Models\AdminSession;
use app\Models\AdminUser;

class AdminAuth
{
    protected const SESSION_USER_KEY = 'admin_auth_user_id';
    protected const SESSION_TOKEN_KEY = 'admin_auth_session_token';

    public static function attempt(string $email, string $password, ?string $ipAddress = null, ?string $userAgent = null): bool
    {
        self::ensureSessionStarted();

        $user = AdminUser::findByEmail($email);

        if (!$user) {
            AdminLoginLog::log(null, $email, $ipAddress, $userAgent, 'failed', 'account_not_found');
            return false;
        }

        if (!empty($user->deleted_at)) {
            AdminLoginLog::log($user->id, $email, $ipAddress, $userAgent, 'failed', 'deleted_account');
            return false;
        }

        if ($user->status === 'blocked') {
            AdminLoginLog::log($user->id, $email, $ipAddress, $userAgent, 'locked', 'blocked_status');
            return false;
        }

        if ($user->isLocked()) {
            AdminLoginLog::log($user->id, $email, $ipAddress, $userAgent, 'locked', 'locked_until');
            return false;
        }

        if (!$user->verifyPassword($password)) {
            $user->incrementFailedAttempts();
            AdminLoginLog::log($user->id, $email, $ipAddress, $userAgent, 'failed', 'invalid_password');
            return false;
        }

        $verifiedAt = $user->email_verified_at ?? null;

        if ($user->status !== 'active' || $verifiedAt === null || $verifiedAt === '') {
            AdminLoginLog::log($user->id, $email, $ipAddress, $userAgent, 'failed', 'email_not_verified');
            return false;
        }

        $user->resetFailedAttempts();
        $user->updateLastLogin();

        session_regenerate_id(true);

        $plainToken = \random_token(32);

        $dbSession = AdminSession::createSession(
            adminUserId: (int) $user->id,
            plainToken: $plainToken,
            ipAddress: $ipAddress,
            userAgent: $userAgent,
            ttlHours: 24
        );

        if (!$dbSession) {
            AdminLoginLog::log($user->id, $email, $ipAddress, $userAgent, 'failed', 'session_creation_failed');
            return false;
        }

        $_SESSION[self::SESSION_USER_KEY] = (int) $user->id;
        $_SESSION[self::SESSION_TOKEN_KEY] = $plainToken;

        AdminLoginLog::log($user->id, $email, $ipAddress, $userAgent, 'success', 'login_success');
        return true;
    }

    public static function check(): bool
    {
        self::ensureSessionStarted();

        $userId = $_SESSION[self::SESSION_USER_KEY] ?? null;
        $plainToken = $_SESSION[self::SESSION_TOKEN_KEY] ?? null;

        if (!$userId || !$plainToken) {
            return false;
        }

        $dbSession = AdminSession::findValidSession($plainToken);

        if (!$dbSession) {
            self::logout();
            return false;
        }

        if ((int) $dbSession->admin_user_id !== (int) $userId) {
            self::logout();
            return false;
        }

        $idleTimeoutMinutes = max(1, env_int('ADMIN_SESSION_IDLE_MINUTES', 30));
        if ($dbSession->isIdleExpired($idleTimeoutMinutes)) {
            $dbSession->revoke();
            self::logout();
            return false;
        }

        $dbSession->touchActivity();

        return true;
    }

    public static function user(): ?AdminUser
    {
        if (!self::check()) {
            return null;
        }

        $userId = $_SESSION[self::SESSION_USER_KEY] ?? null;
        if (!$userId) {
            return null;
        }

        return AdminUser::find((int) $userId);
    }

    public static function id(): ?int
    {
        $user = self::user();
        return $user ? (int) $user->id : null;
    }

    public static function logout(?string $ipAddress = null, ?string $userAgent = null): void
    {
        self::ensureSessionStarted();

        $userId = $_SESSION[self::SESSION_USER_KEY] ?? null;
        $plainToken = $_SESSION[self::SESSION_TOKEN_KEY] ?? null;

        if ($plainToken) {
            $dbSession = AdminSession::findValidSession($plainToken);
            if ($dbSession) {
                $dbSession->revoke();
            }
        }

        if ($userId) {
            $user = AdminUser::find((int) $userId);
            AdminLoginLog::log(
                $user ? (int) $user->id : null,
                $user?->email,
                $ipAddress,
                $userAgent,
                'logout',
                'manual_logout'
            );
        }

        unset($_SESSION[self::SESSION_USER_KEY], $_SESSION[self::SESSION_TOKEN_KEY]);

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'] ?: '/',
                'domain' => $params['domain'] ?: '',
                'secure' => (bool) ($params['secure'] ?? false),
                'httponly' => (bool) ($params['httponly'] ?? true),
                'samesite' => $params['samesite'] ?? 'Lax',
            ]);
        }

        session_destroy();
        secure_session_start();
        session_regenerate_id(true);
    }

    public static function logoutAllDevices(int $adminUserId): bool
    {
        return AdminSession::revokeAllByUser($adminUserId);
    }

    protected static function ensureSessionStarted(): void
    {
        secure_session_start();
    }

   public static function hasRole(string $role): bool
{
    $user = static::user();

    return $user ? $user->hasRole($role) : false;
}

public static function can(string $permission): bool
{
    $user = static::user();

    return $user ? $user->can($permission) : false;
}

public static function requirePermission(string $permission): void
{
    $user = static::user();

    if (!$user) {
        redirect('/admin/users/login');
    }

    if (!$user->can($permission)) {
        http_response_code(403);
        exit('No autorizado.');
    }
}
}
