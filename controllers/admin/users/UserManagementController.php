<?php

namespace app\Controllers\admin\users;

use app\Core\Controller;
use app\Core\Csrf;
use app\Core\AdminAuth;
use app\Core\Flash;
use app\Services\Admin\Users\AdminUserManagementService;

class UserManagementController extends Controller
{
    protected AdminUserManagementService $service;

    public function __construct()
    {
        $this->service = new AdminUserManagementService();
    }

    public function index()
    {
        $filters = [
            'status' => trim((string) ($_GET['status'] ?? '')),
            'role' => trim((string) ($_GET['role'] ?? '')),
            'q' => trim((string) ($_GET['q'] ?? '')),
        ];

        $users = $this->service->list($filters);

        return $this->render('admin/users/userList', [
            'users' => $users,
            'filters' => $filters,
            'editingUser' => null,
            'currentAdminId' => (int) (AdminAuth::id() ?? 0),
            'currentAdmin' => AdminAuth::user(),
        ], 'adminUserLayout');
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/users');
            exit;
        }

        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $actor = AdminAuth::user();
        $result = $this->service->create($_POST, $actor);

        if (!empty($result['success'])) {
            Flash::success($result['message'] ?? 'Usuario creado correctamente.');

            Flash::set('createdUserInfo', [
                'email' => $result['user']->email ?? null,
                'verification_url' => $result['verification_url'] ?? null,
                'generated_password' => $result['generated_password'] ?? null,
            ]);

            redirect('/admin/users');
            exit;
        }

        Flash::error($result['message'] ?? 'No fue posible crear el usuario.');
        Flash::set('old', $result['old'] ?? $_POST);
        Flash::set('errors', $result['errors'] ?? []);

        redirect('/admin/users?open_modal=1');
        exit;
    }

    public function edit()
    {
        $id = (int) ($_GET['id'] ?? 0);
        $actor = AdminAuth::user();

        $user = \app\Models\AdminUser::find($id);

        if (!$user) {
            Flash::error('Usuario no encontrado.');
            redirect('/admin/users');
            exit;
        }

        $filters = [
            'status' => trim((string) ($_GET['status'] ?? '')),
            'role' => trim((string) ($_GET['role'] ?? '')),
            'q' => trim((string) ($_GET['q'] ?? '')),
        ];

        $users = $this->service->list($filters);

        return $this->render('admin/users/userList', [
            'users' => $users,
            'filters' => $filters,
            'editingUser' => $user,
            'currentAdminId' => (int) (AdminAuth::id() ?? 0),
            'currentAdmin' => $actor,
        ], 'adminUserLayout');
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/users');
            exit;
        }

        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $actor = AdminAuth::user();
        $result = $this->service->update($id, $_POST, $actor);

        if (!empty($result['success'])) {
            Flash::success($result['message'] ?? 'Usuario actualizado correctamente.');
            redirect('/admin/users');
            exit;
        }

        Flash::error($result['message'] ?? 'No fue posible actualizar el usuario.');
        Flash::set('old', $_POST);
        Flash::set('errors', $result['errors'] ?? []);

        redirect('/admin/users/edit?id=' . $id);
        exit;
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/users');
            exit;
        }

        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $actor = AdminAuth::user();

        $result = $this->service->delete($id, (int) (AdminAuth::id() ?? 0), $actor);

        if (!empty($result['success'])) {
            Flash::success($result['message'] ?? 'Usuario eliminado correctamente.');
        } else {
            Flash::error($result['message'] ?? 'No fue posible eliminar el usuario.');
        }

        redirect('/admin/users');
        exit;
    }

    public function changeStatus()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/users');
            exit;
        }

        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $status = trim((string) ($_POST['status'] ?? ''));
        $actor = AdminAuth::user();

        $result = $this->service->changeStatus($id, $status, (int) (AdminAuth::id() ?? 0), $actor);

        if (!empty($result['success'])) {
            Flash::success($result['message'] ?? 'Estado actualizado correctamente.');
        } else {
            Flash::error($result['message'] ?? 'No fue posible actualizar el estado.');
        }

        redirect('/admin/users');
        exit;
    }
}