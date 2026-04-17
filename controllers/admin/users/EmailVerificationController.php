<?php

namespace app\Controllers\admin\users;

use app\Core\Controller;
use app\Core\Csrf;
use app\Core\RateLimiter;
use app\Services\Admin\Users\AdminEmailVerificationService;
class EmailVerificationController extends Controller
{
    public function confirm()
    {
        $token = $_GET['token'] ?? '';

        $service = new AdminEmailVerificationService();
        $result = $service->verify($token);

        return $this->render('admin/users/confirmAccount', [
            'errors' => $result['errors'] ?? [],
            'message' => $result['message'] ?? null,
            'success' => $result['success'] ?? false,
        ], null);
    }

    public function showResendForm()
    {
        return $this->render('admin/users/resendVerification', [
            'errors' => [],
            'message' => null,
            'old' => [],
        ], 'adminUserLayout');
    }

    public function resend()
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $ipAddress = client_ip();
        $email = trim(mb_strtolower((string) ($_POST['email'] ?? '')));
        $rateLimitKey = rate_limit_key('admin_verification_resend', $ipAddress, $email);

        if (RateLimiter::tooManyAttempts($rateLimitKey, 3, 1800)) {
            return $this->render('admin/users/resendVerification', [
                'errors' => [
                    'email' => ['Demasiadas solicitudes. Espera unos minutos antes de reenviar otro enlace.'],
                ],
                'message' => 'Demasiadas solicitudes de verificacion.',
                'old' => [
                    'email' => $_POST['email'] ?? '',
                ],
            ], 'adminUserLayout');
        }

        $turnstile = validate_turnstile($_POST['cf-turnstile-response'] ?? null, $ipAddress);
        if (!$turnstile['success']) {
            return $this->render('admin/users/resendVerification', [
                'errors' => [
                    'email' => [$turnstile['message'] ?? 'Debes completar la verificacion de seguridad.'],
                ],
                'message' => $turnstile['message'] ?? null,
                'old' => [
                    'email' => $_POST['email'] ?? '',
                ],
            ], 'adminUserLayout');
        }

        $service = new AdminEmailVerificationService();
        $result = $service->resend($email);
        RateLimiter::hit($rateLimitKey, 1800);

        return $this->render('admin/users/resendVerification', [
            'errors' => $result['errors'] ?? [],
            'message' => $result['message'] ?? null,
            'old' => [
                'email' => $_POST['email'] ?? '',
            ],
        ], 'adminUserLayout');
    }
}
