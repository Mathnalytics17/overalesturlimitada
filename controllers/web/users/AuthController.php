<?php

namespace app\Controllers\Web\Users;

use app\Core\Controller;
use app\Core\Csrf;
use app\Core\RateLimiter;
use app\Services\Web\Users\CustomerAuthService;

class AuthController extends Controller
{
    public function showLogin()
    {
        return $this->render('users/login', [
            'errors' => [],
            'message' => null,
        ]);
    }

    public function login()
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $ipAddress = client_ip();
        $email = trim(mb_strtolower((string) ($_POST['email'] ?? '')));
        $rateLimitKey = rate_limit_key('customer_login', $ipAddress, $email);

        if (RateLimiter::tooManyAttempts($rateLimitKey, 5, 900)) {
            return $this->render('users/login', [
                'errors' => [
                    'auth' => ['Demasiados intentos. Espera unos minutos antes de volver a intentar.'],
                ],
                'message' => 'Demasiados intentos de inicio de sesion.',
                'old' => [
                    'email' => $_POST['email'] ?? '',
                ],
            ]);
        }

        $turnstile = validate_turnstile($_POST['cf-turnstile-response'] ?? null, $ipAddress);
        if (!$turnstile['success']) {
            return $this->render('users/login', [
                'errors' => [
                    'auth' => [$turnstile['message'] ?? 'Debes completar la verificacion de seguridad.'],
                ],
                'message' => $turnstile['message'] ?? null,
                'old' => [
                    'email' => $_POST['email'] ?? '',
                ],
            ]);
        }

        $service = new CustomerAuthService();

        $result = $service->login(
            email: $email,
            password: $_POST['password'] ?? '',
            ipAddress: $ipAddress,
            userAgent: $_SERVER['HTTP_USER_AGENT'] ?? null
        );

        if ($result['success']) {
            RateLimiter::clear($rateLimitKey);
            redirect('/users/user');
        }

        RateLimiter::hit($rateLimitKey, 900);

        return $this->render('users/login', [
            'errors' => $result['errors'] ?? [],
            'message' => $result['message'] ?? null,
            'old' => [
                'email' => $_POST['email'] ?? '',
            ],
        ]);
    }

    public function logout()
    {
        $service = new CustomerAuthService();

        $service->logout(
            ipAddress: client_ip(),
            userAgent: $_SERVER['HTTP_USER_AGENT'] ?? null
        );

        redirect('/users/login');
    }
}
