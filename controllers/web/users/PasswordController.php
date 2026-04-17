<?php

namespace app\Controllers\web\users;

use app\Core\Controller;
use app\Core\Csrf;
use app\Core\RateLimiter;
use app\Services\Web\Users\CustomerPasswordService;

class PasswordController extends Controller
{
    public function showForgotPassword()
    {
        return $this->render('users/forgotPassword', [
            'errors' => [],
            'message' => null,
            'old' => [],
        ]);
    }

    public function sendResetLink()
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $ipAddress = client_ip();
        $email = trim(mb_strtolower((string) ($_POST['email'] ?? '')));
        $rateLimitKey = rate_limit_key('customer_password_reset_request', $ipAddress, $email);

        if (RateLimiter::tooManyAttempts($rateLimitKey, 3, 900)) {
            return $this->render('users/forgotPassword', [
                'errors' => [
                    'email' => ['Demasiadas solicitudes. Espera unos minutos antes de intentarlo de nuevo.'],
                ],
                'message' => 'Demasiadas solicitudes de recuperacion.',
                'old' => [
                    'email' => $_POST['email'] ?? '',
                ],
            ]);
        }

        $turnstile = validate_turnstile($_POST['cf-turnstile-response'] ?? null, $ipAddress);
        if (!$turnstile['success']) {
            return $this->render('users/forgotPassword', [
                'errors' => [
                    'email' => [$turnstile['message'] ?? 'Debes completar la verificacion de seguridad.'],
                ],
                'message' => $turnstile['message'] ?? null,
                'old' => [
                    'email' => $_POST['email'] ?? '',
                ],
            ]);
        }

        $service = new CustomerPasswordService();
        $result = $service->requestReset($email);
        RateLimiter::hit($rateLimitKey, 900);

        return $this->render('users/forgotPassword', [
            'errors' => $result['errors'] ?? [],
            'message' => $result['message'] ?? null,
            'old' => [
                'email' => $_POST['email'] ?? '',
            ],
        ]);
    }

    public function showResetPassword()
    {
        $token = $_GET['token'] ?? '';

        $service = new CustomerPasswordService();
        $result = $service->validateResetToken($token);

        return $this->render('users/newPassword', [
            'errors' => $result['errors'] ?? [],
            'message' => $result['message'] ?? null,
            'token' => $token,
        ]);
    }

    public function resetPassword()
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $ipAddress = client_ip();
        $token = (string) ($_POST['token'] ?? '');
        $rateLimitKey = rate_limit_key('customer_password_reset_submit', $ipAddress, hash('sha256', $token));

        if (RateLimiter::tooManyAttempts($rateLimitKey, 5, 1800)) {
            return $this->render('users/newPassword', [
                'errors' => [
                    'token' => ['Demasiados intentos. Solicita un nuevo enlace o espera unos minutos.'],
                ],
                'message' => 'Demasiados intentos de restablecimiento.',
                'token' => $token,
            ]);
        }

        $service = new CustomerPasswordService();
        $result = $service->resetPassword(
            token: $token,
            password: $_POST['password'] ?? '',
            passwordConfirmation: $_POST['password_confirmation'] ?? ''
        );

        if ($result['success']) {
            RateLimiter::clear($rateLimitKey);
            \redirect('/users/login');
        }

        RateLimiter::hit($rateLimitKey, 1800);

        return $this->render('users/newPassword', [
            'errors' => $result['errors'] ?? [],
            'message' => $result['message'] ?? null,
            'token' => $_POST['token'] ?? '',
        ]);
    }
}
