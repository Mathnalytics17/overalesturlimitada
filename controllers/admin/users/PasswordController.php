<?php

namespace app\Controllers\admin\users;

use app\Core\Controller;
use app\Core\Csrf;
use app\Services\Admin\Users\AdminPasswordService;
use app\Core\Flash;
class PasswordController extends Controller
{
    public function showForgotPassword()
    {
        return $this->render('admin/users/forgotPassword', [
            'errors' => [],
            'message' => null,
            'old' => [],
        ], 'adminUserLayout');
    }

    public function sendResetLink()
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $service = new AdminPasswordService();
        $result = $service->requestReset($_POST['email'] ?? '');

        return $this->render('admin/users/forgotPassword', [
            'errors' => $result['errors'] ?? [],
            'message' => $result['message'] ?? null,
            'old' => [
                'email' => $_POST['email'] ?? '',
            ],
        ], 'adminUserLayout');
    }

    public function showResetPassword()
    {
        $token = $_GET['token'] ?? '';

        return $this->render('admin/users/newPassword', [
            'token' => $token,
            'errors' => [],
            'message' => null,
        ], 'adminUserLayout');
    }

    public function resetPassword()
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $service = new AdminPasswordService();
        $result = $service->resetPassword(
            token: $_POST['token'] ?? '',
            password: $_POST['password'] ?? '',
            passwordConfirmation: $_POST['password_confirmation'] ?? ''
        );

        if ($result['success']) {
            \redirect('/admin/users/login');
        }

        return $this->render('admin/users/newPassword', [
            'token' => $_POST['token'] ?? '',
            'errors' => $result['errors'] ?? [],
            'message' => $result['message'] ?? null,
        ], 'adminUserLayout');
    }
}