<?php

namespace app\Core;

use app\Models\CustomerAccount;
use app\Models\CustomerLoginLog;
use app\Models\CustomerSession;

class CustomerAuth
{
    protected const SESSION_USER_KEY = 'customer_auth_account_id';
    protected const SESSION_TOKEN_KEY = 'customer_auth_session_token';

    public static function attempt(string $email, string $password, ?string $ipAddress = null, ?string $userAgent = null): bool
    {
        self::ensureSessionStarted();

        $account = CustomerAccount::findByEmail($email);

        if (!$account) {
            CustomerLoginLog::log(null, $email, $ipAddress, $userAgent, 'failed', 'account_not_found');
            return false;
        }

        if (!empty($account->deleted_at)) {
            CustomerLoginLog::log($account->id, $email, $ipAddress, $userAgent, 'failed', 'deleted_account');
            return false;
        }

        if ($account->status === 'blocked') {
            CustomerLoginLog::log($account->id, $email, $ipAddress, $userAgent, 'locked', 'blocked_status');
            return false;
        }

        if ($account->isLocked()) {
            CustomerLoginLog::log($account->id, $email, $ipAddress, $userAgent, 'locked', 'locked_until');
            return false;
        }

        if (!$account->verifyPassword($password)) {
            $account->incrementFailedAttempts();
            CustomerLoginLog::log($account->id, $email, $ipAddress, $userAgent, 'failed', 'invalid_password');
            return false;
        }

$verifiedAt = $account->email_verified_at ?? null;

if ($account->status !== 'active' || $verifiedAt === null || $verifiedAt === '') {
    CustomerLoginLog::log($account->id, $email, $ipAddress, $userAgent, 'failed', 'email_not_verified');
    return false;
}

        $account->resetFailedAttempts();
        $account->updateLastLogin();

        session_regenerate_id(true);

        $plainToken = random_token(32);

        $dbSession = CustomerSession::createSession(
            customerAccountId: (int) $account->id,
            plainToken: $plainToken,
            ipAddress: $ipAddress,
            userAgent: $userAgent,
            ttlHours: 24
        );

        if (!$dbSession) {
            CustomerLoginLog::log($account->id, $email, $ipAddress, $userAgent, 'failed', 'session_creation_failed');
            return false;
        }

        $_SESSION[self::SESSION_USER_KEY] = (int) $account->id;
        $_SESSION[self::SESSION_TOKEN_KEY] = $plainToken;

        CustomerLoginLog::log($account->id, $email, $ipAddress, $userAgent, 'success', 'login_success');
        return true;
    }

    public static function check(): bool
    {
        self::ensureSessionStarted();

        $accountId = $_SESSION[self::SESSION_USER_KEY] ?? null;
        $plainToken = $_SESSION[self::SESSION_TOKEN_KEY] ?? null;

        if (!$accountId || !$plainToken) {
            return false;
        }

        $dbSession = CustomerSession::findValidSession($plainToken);

        if (!$dbSession) {
            self::logout();
            return false;
        }

        if ((int) $dbSession->customer_account_id !== (int) $accountId) {
            self::logout();
            return false;
        }

        $idleTimeoutMinutes = max(1, env_int('CUSTOMER_SESSION_IDLE_MINUTES', 120));
        if ($dbSession->isIdleExpired($idleTimeoutMinutes)) {
            $dbSession->revoke();
            self::logout();
            return false;
        }

        $dbSession->touchActivity();

        return true;
    }

    public static function user(): ?CustomerAccount
    {
        if (!self::check()) {
            return null;
        }

        $accountId = $_SESSION[self::SESSION_USER_KEY] ?? null;
        if (!$accountId) {
            return null;
        }

        return CustomerAccount::find((int) $accountId);
    }

    public static function id(): ?int
    {
        $user = self::user();
        return $user ? (int) $user->id : null;
    }

    public static function logout(?string $ipAddress = null, ?string $userAgent = null): void
    {
        self::ensureSessionStarted();

        $accountId = $_SESSION[self::SESSION_USER_KEY] ?? null;
        $plainToken = $_SESSION[self::SESSION_TOKEN_KEY] ?? null;

        if ($plainToken) {
            $dbSession = CustomerSession::findValidSession($plainToken);
            if ($dbSession) {
                $dbSession->revoke();
            }
        }

        if ($accountId) {
            $account = CustomerAccount::find((int) $accountId);
            CustomerLoginLog::log(
                $account ? (int) $account->id : null,
                $account?->email,
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

    public static function logoutAllDevices(int $customerAccountId): bool
    {
        return CustomerSession::revokeAllByAccount($customerAccountId);
    }

    protected static function ensureSessionStarted(): void
    {
        secure_session_start();
    }
}
