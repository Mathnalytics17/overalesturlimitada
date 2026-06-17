<?php

namespace app\Controllers\Web\Users;

use app\Core\Controller;
use app\Core\Csrf;
use app\Core\RateLimiter;
use app\Core\Flash;
use app\Services\Web\Users\CustomerRegisterService;

class RegisterController extends Controller
{
    public function showRegister()
    {
        return $this->render('users/register', [
            'errors' => [],
            'message' => null,
        ]);
    }

    public function register()
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $ipAddress = client_ip();
        $email = trim(mb_strtolower((string) ($_POST['email'] ?? '')));
        $rateLimitKey = rate_limit_key('customer_register', $ipAddress, $email);

        if (RateLimiter::tooManyAttempts($rateLimitKey, 3, 3600)) {
            return $this->render('users/register', [
                'errors' => [
                    'email' => ['Demasiados intentos de registro. Espera un tiempo antes de volver a intentarlo.'],
                ],
                'message' => 'Demasiados intentos de registro.',
                'old' => $_POST,
            ]);
        }

        $turnstile = validate_turnstile($_POST['cf-turnstile-response'] ?? null, $ipAddress);
        if (!$turnstile['success']) {
            return $this->render('users/register', [
                'errors' => [
                    'email' => [$turnstile['message'] ?? 'Debes completar la verificacion de seguridad.'],
                ],
                'message' => $turnstile['message'] ?? null,
                'old' => $_POST,
            ]);
        }

        $service = new CustomerRegisterService();
        $result = $service->register($_POST);

        if ($result['success']) {
            RateLimiter::clear($rateLimitKey);

            $registeredEmail = trim((string) ($_POST['email'] ?? ''));
            $mailResult = $result['data']['mail_result'] ?? [];

            if (($mailResult['success'] ?? false) === true) {
                Flash::set('customer_login_notice', [
                    'type' => 'success',
                    'title' => 'Cuenta creada correctamente',
                    'message' => 'Te enviamos un correo de verificación a ' . $registeredEmail . '. Debes confirmar tu cuenta desde ese correo antes de iniciar sesión.',
                    'email' => $registeredEmail,
                ]);
            } else {
                Flash::set('customer_login_notice', [
                    'type' => 'warning',
                    'title' => 'Cuenta creada, verificación pendiente',
                    'message' => 'Tu cuenta fue creada, pero no pudimos enviar el correo de verificación. Solicita un nuevo enlace desde esta pantalla antes de iniciar sesión.',
                    'email' => $registeredEmail,
                ]);
            }

            redirect('/users/login');
        }

        RateLimiter::hit($rateLimitKey, 3600);

        return $this->render('users/register', [
            'errors' => $result['errors'] ?? [],
            'message' => $result['message'] ?? null,
            'old' => $_POST,
        ]);
    }
}
