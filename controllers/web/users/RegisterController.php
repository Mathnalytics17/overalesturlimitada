<?php

namespace app\Controllers\Web\Users;

use app\Core\Controller;
use app\Core\Csrf;
use app\Services\Web\Users\CustomerRegisterService;
use app\Core\Flash;

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

        $service = new CustomerRegisterService();
        $result = $service->register($_POST);

        if ($result['success']) {
            redirect('/users/login');
        }

        return $this->render('users/register', [
            'errors' => $result['errors'] ?? [],
            'message' => $result['message'] ?? null,
            'old' => $_POST,
        ]);
    }
}