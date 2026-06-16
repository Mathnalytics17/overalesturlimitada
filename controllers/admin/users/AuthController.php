<?php

namespace app\Controllers\admin\users;

use app\Core\Controller;
use app\Core\Csrf;
use app\Core\RateLimiter;
use app\Services\Admin\Users\AdminAuthService;
class AuthController extends Controller
{
    public function showLogin()
    {
        return $this->render('admin/users/login', [
            'errors' => [],
            'message' => null,
            'old' => [],
        ], null);
    }

    public function login()
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $ipAddress = client_ip();
        $email = trim(mb_strtolower((string) ($_POST['email'] ?? '')));
        $rateLimitKey = rate_limit_key('admin_login', $ipAddress, $email);

        if (RateLimiter::tooManyAttempts($rateLimitKey, 5, 900)) {
            return $this->render('admin/users/login', [
                'errors' => [
                    'auth' => ['Demasiados intentos. Espera unos minutos antes de volver a intentar.'],
                ],
                'message' => 'Demasiados intentos de inicio de sesion.',
                'old' => [
                    'email' => $_POST['email'] ?? '',
                ],
            ], null);
        }

        $turnstile = validate_turnstile($_POST['cf-turnstile-response'] ?? null, $ipAddress);
        if (!$turnstile['success']) {
            return $this->render('admin/users/login', [
                'errors' => [
                    'auth' => [$turnstile['message'] ?? 'Debes completar la verificacion de seguridad.'],
                ],
                'message' => $turnstile['message'] ?? null,
                'old' => [
                    'email' => $_POST['email'] ?? '',
                ],
            ], null);
        }

        $service = new AdminAuthService();

        $result = $service->login(
            email: $email,
            password: $_POST['password'] ?? '',
            ipAddress: $ipAddress,
            userAgent: $_SERVER['HTTP_USER_AGENT'] ?? null
        );

        if ($result['success']) {
            RateLimiter::clear($rateLimitKey);
            \redirect('/admin');
        }

        RateLimiter::hit($rateLimitKey, 900);

        return $this->render('admin/users/login', [
            'errors' => $result['errors'] ?? [],
            'message' => $result['message'] ?? null,
            'old' => [
                'email' => $_POST['email'] ?? '',
            ],
        ],null);
    }

    public function logout()
    {
        // Cerrar sesión debe ser tolerante a token CSRF vencido: si el usuario
        // quiere salir, destruimos la sesión y lo enviamos al login.
        $service = new AdminAuthService();

        $service->logout(
            ipAddress: client_ip(),
            userAgent: $_SERVER['HTTP_USER_AGENT'] ?? null
        );

        \redirect('/admin/users/login');
    }
}
