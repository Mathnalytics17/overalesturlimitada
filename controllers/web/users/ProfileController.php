<?php

namespace app\Controllers\web\users;

use app\Core\Controller;
use app\Core\Csrf;
use app\Core\CustomerAuth;
use app\Models\Customer;
use app\Services\Web\Users\CustomerProfileService;
use app\Core\Flash;

class ProfileController extends Controller
{
    public function index()
    {
        $account = CustomerAuth::user();

        if (!$account) {
            \redirect('/users/login');
        }

        $customer = Customer::find((int) $account->customer_id);

        return $this->render('users/user', [
            'account' => $account,
            'customer' => $customer,
        ], 'mainUserLayout');
    }

    public function showChangePassword()
    {
        return $this->render('users/changePassword', [
            'errors' => [],
            'message' => null,
        ], 'mainUserLayout');
    }

    public function changePassword()
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $account = CustomerAuth::user();

        if (!$account) {
            \redirect('/users/login');
        }

        $service = new CustomerProfileService();

        $result = $service->changePassword(
            accountId: (int) $account->id,
            currentPassword: $_POST['current_password'] ?? '',
            newPassword: $_POST['new_password'] ?? '',
            newPasswordConfirmation: $_POST['new_password_confirmation'] ?? ''
        );

        if (!empty($result['success']) && !empty($result['data']['force_relogin'])) {
            CustomerAuth::logout(
                ipAddress: $_SERVER['REMOTE_ADDR'] ?? null,
                userAgent: $_SERVER['HTTP_USER_AGENT'] ?? null
            );

            return $this->render('users/login', [
                'errors' => [],
                'message' => 'Tu contrasena fue actualizada. Inicia sesion nuevamente.',
                'old' => [],
            ]);
        }

        return $this->render('users/changePassword', [
            'errors' => $result['errors'] ?? [],
            'message' => $result['message'] ?? null,
        ], 'mainUserLayout');
    }

     public function showEditProfile()
    {
        $account = CustomerAuth::user();

        if (!$account) {
            \redirect('/users/login');
        }

        $customer = Customer::find((int) $account->customer_id);

        return $this->render('users/editUser', [
            'account' => $account,
            'customer' => $customer,
            'errors' => [],
            'message' => null,
            'old' => [],
        ], 'mainUserLayout');
    }

    public function updateProfile()
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $account = CustomerAuth::user();

        if (!$account) {
            \redirect('/users/login');
        }

        $service = new CustomerProfileService();

        $result = $service->updateProfile(
            customerId: (int) $account->customer_id,
            input: $_POST
        );

        $customer = Customer::find((int) $account->customer_id);

        return $this->render('users/editUser', [
            'account' => $account,
            'customer' => $customer,
            'errors' => $result['errors'] ?? [],
            'message' => $result['message'] ?? null,
            'old' => $_POST,
        ], 'mainUserLayout');
    }
}
