<?php

namespace app\Controllers\Web\Users;

use app\Core\Controller;
use app\Core\Csrf;
use app\Services\Web\Users\CustomerAuthService;
use app\Core\Flash;

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

        $service = new CustomerAuthService();

        $result = $service->login(
            email: $_POST['email'] ?? '',
            password: $_POST['password'] ?? '',
            ipAddress: $_SERVER['REMOTE_ADDR'] ?? null,
            userAgent: $_SERVER['HTTP_USER_AGENT'] ?? null
        );

        if ($result['success']) {
            redirect('/users/user');
        }

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
            ipAddress: $_SERVER['REMOTE_ADDR'] ?? null,
            userAgent: $_SERVER['HTTP_USER_AGENT'] ?? null
        );

        redirect('/users/login');
    }
}