<?php

namespace app\Controllers\admin\users;

use app\Core\Controller;
use app\Core\Csrf;
use app\Services\Admin\Users\AdminEmailVerificationService;
use app\Core\Flash;
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

        $service = new AdminEmailVerificationService();
        $result = $service->resend($_POST['email'] ?? '');

        return $this->render('admin/users/resendVerification', [
            'errors' => $result['errors'] ?? [],
            'message' => $result['message'] ?? null,
            'old' => [
                'email' => $_POST['email'] ?? '',
            ],
        ], 'adminUserLayout');
    }
}