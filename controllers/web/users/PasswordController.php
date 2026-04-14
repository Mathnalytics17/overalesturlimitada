<?php

namespace app\Controllers\web\users;

use app\Core\Controller;
use app\Core\Csrf;
use app\Services\Web\Users\CustomerPasswordService;
use app\Core\Flash;

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

        $service = new CustomerPasswordService();
        $result = $service->requestReset($_POST['email'] ?? '');

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

        $service = new CustomerPasswordService();
        $result = $service->resetPassword(
            token: $_POST['token'] ?? '',
            password: $_POST['password'] ?? '',
            passwordConfirmation: $_POST['password_confirmation'] ?? ''
        );

        if ($result['success']) {
            \redirect('/users/login');
        }

        return $this->render('users/newPassword', [
            'errors' => $result['errors'] ?? [],
            'message' => $result['message'] ?? null,
            'token' => $_POST['token'] ?? '',
        ]);
    }
}