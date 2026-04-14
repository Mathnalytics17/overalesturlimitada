<?php

namespace app\Controllers\web\users;

use app\Core\Controller;
use app\Core\Csrf;
use app\Services\Web\Users\CustomerEmailVerificationService;
use app\Core\Flash;

class EmailVerificationController extends Controller
{
    public function confirm()
    {
        $token = $_GET['token'] ?? '';

        $service = new CustomerEmailVerificationService();
        $result = $service->verify($token);

        return $this->render('users/confirmAccount', [
            'errors' => $result['errors'] ?? [],
            'message' => $result['message'] ?? null,
            'success' => $result['success'] ?? false,
        ]);
    }

    public function resend()
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $service = new CustomerEmailVerificationService();
        $result = $service->resend($_POST['email'] ?? '');

        return $this->render('users/resendVerification', [
            'errors' => $result['errors'] ?? [],
            'message' => $result['message'] ?? null,
            'old' => [
                'email' => $_POST['email'] ?? '',
            ],
            'debug_verification_url' => $result['data']['verification_url'] ?? null,
        ]);
    }

    public function showResendForm()
    {
        return $this->render('users/resendVerification', [
            'errors' => [],
            'message' => null,
            'old' => [],
        ]);
    }
}