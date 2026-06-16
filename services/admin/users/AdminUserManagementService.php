<?php

namespace app\Services\Admin\Users;

use PDOException;
use app\Models\AdminEmailVerification;
use app\Models\AdminPasswordReset;
use app\Models\AdminSession;
use app\Models\AdminUser;
use app\Services\Mail\AdminMailService;

class AdminUserManagementService
{
    public function list(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        return AdminUser::paginate($filters, $page, $perPage);
    }

    public function create(array $payload, ?AdminUser $actor): array
    {
        if (!$actor || !$actor->isSuperAdmin()) {
            return [
                'success' => false,
                'message' => 'Solo un super administrador puede crear usuarios.',
                'errors' => [
                    'general' => ['Solo un super administrador puede crear usuarios.'],
                ],
                'old' => $payload,
            ];
        }

        $data = $this->normalize($payload);
        $errors = $this->validateCreate($data);

        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => 'Revisa los campos del formulario.',
                'errors' => $errors,
                'old' => $payload,
            ];
        }

        if (AdminUser::findByEmail($data['email'])) {
            return [
                'success' => false,
                'message' => 'Ya existe un usuario con ese correo.',
                'errors' => [
                    'email' => ['Ya existe un usuario con ese correo.'],
                ],
                'old' => $payload,
            ];
        }

        $passwordWasProvided = $data['password'] !== '';
        $plainPassword = $passwordWasProvided ? $data['password'] : $this->generateTemporaryPassword(32);

        try {
            $user = AdminUser::create([
                'uuid' => uuid(),
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'full_name' => AdminUser::buildFullName($data['first_name'], $data['last_name']),
                'email' => $data['email'],
                'phone' => $data['phone'],
                'role' => $data['role'],
                'password_hash' => AdminUser::hashPassword($plainPassword),
                'status' => 'pending_verification',
                'email_verified_at' => null,
                'last_login_at' => null,
                'failed_login_attempts' => 0,
                'locked_until' => null,
            ]);
        } catch (PDOException $e) {
            if ($this->isDuplicateEmailException($e)) {
                return [
                    'success' => false,
                    'message' => 'Ya existe un usuario con ese correo.',
                    'errors' => [
                        'email' => ['Ese correo ya está registrado.'],
                    ],
                    'old' => $payload,
                ];
            }

            return [
                'success' => false,
                'message' => 'No fue posible crear el usuario.',
                'errors' => [
                    'general' => ['Ocurrió un error inesperado al crear el usuario.'],
                ],
                'old' => $payload,
            ];
        }

        if (!$user) {
            return [
                'success' => false,
                'message' => 'No fue posible crear el usuario.',
                'errors' => [
                    'general' => ['No fue posible crear el usuario.'],
                ],
                'old' => $payload,
            ];
        }

        try {
            $plainVerificationToken = random_token(32);

            AdminEmailVerification::createToken(
                adminUserId: (int) $user->id,
                plainToken: $plainVerificationToken,
                ttlHours: 72
            );

            $verificationUrl = app_url('/admin/users/confirmUser?token=' . urlencode($plainVerificationToken));
            $passwordSetupUrl = null;

            if (!$passwordWasProvided) {
                AdminPasswordReset::invalidateAllByEmail((string) $user->email);
                $plainPasswordResetToken = random_token(32);

                AdminPasswordReset::createToken(
                    adminUserId: (int) $user->id,
                    email: (string) $user->email,
                    plainToken: $plainPasswordResetToken,
                    ttlMinutes: 10080
                );

                $passwordSetupUrl = app_url('/admin/users/resetPassword?token=' . urlencode($plainPasswordResetToken));
            }

            $mailService = new AdminMailService();

            $mailResult = $mailService->sendInvitationEmail(
                email: $user->email,
                name: $user->full_name ?? $data['first_name'],
                verificationUrl: $verificationUrl,
                passwordSetupUrl: $passwordSetupUrl,
                passwordWasProvided: $passwordWasProvided
            );

            $message = $mailResult['success']
                ? ($passwordWasProvided
                    ? 'Usuario creado correctamente. Se envió correo de verificación.'
                    : 'Usuario creado correctamente. Se envió invitación para verificar correo y crear contraseña.')
                : 'Usuario creado, pero no fue posible enviar el correo de invitación.';

            return [
                'success' => true,
                'message' => $message,
                'errors' => [],
                'old' => [],
                'user' => $user,
                'mail_result' => $mailResult,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => true,
                'message' => 'Usuario creado correctamente, pero ocurrió un problema al generar o enviar la invitación.',
                'errors' => [],
                'old' => [],
                'user' => $user,
            ];
        }
    }

    public function update(int $userId, array $payload, ?AdminUser $actor): array
    {
        if (!$actor || !$actor->can('manage_users')) {
            return [
                'success' => false,
                'message' => 'No tienes permisos para editar usuarios.',
                'errors' => [
                    'general' => ['No tienes permisos para editar usuarios.'],
                ],
            ];
        }

        $user = AdminUser::find($userId);

        if (!$user) {
            return [
                'success' => false,
                'message' => 'Usuario no encontrado.',
                'errors' => [
                    'general' => ['Usuario no encontrado.'],
                ],
            ];
        }

        if ($user->isSuperAdmin() && !$actor->isSuperAdmin()) {
            return [
                'success' => false,
                'message' => 'No tienes permisos para editar este usuario.',
                'errors' => [
                    'general' => ['No tienes permisos para editar este usuario.'],
                ],
                'user' => $user,
            ];
        }

        $data = $this->normalize($payload);
        $errors = $this->validateUpdate($data, $userId);

        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => 'Revisa los campos del formulario.',
                'errors' => $errors,
                'old' => $payload,
                'user' => $user,
            ];
        }

        $emailChanged = strcasecmp((string) ($user->email ?? ''), $data['email']) !== 0;

        if ($emailChanged && (int) ($actor->id ?? 0) === $userId) {
            return [
                'success' => false,
                'message' => 'No puedes cambiar tu propio correo desde administración. Usa tu perfil o solicita apoyo técnico.',
                'errors' => [
                    'email' => ['No puedes cambiar tu propio correo desde esta vista.'],
                ],
                'old' => $payload,
                'user' => $user,
            ];
        }

        $updateData = [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'full_name' => AdminUser::buildFullName($data['first_name'], $data['last_name']),
            'email' => $data['email'],
            'phone' => $data['phone'],
            'role' => $data['role'],
        ];

        if ($actor->isSuperAdmin() && in_array($data['status'], ['active', 'inactive', 'blocked', 'pending_verification'], true)) {
            $updateData['status'] = $data['status'];
        }

        if ($emailChanged) {
            $updateData['email_verified_at'] = null;
            $updateData['status'] = 'pending_verification';
        }

        if ($data['password'] !== '') {
            $updateData['password_hash'] = AdminUser::hashPassword($data['password']);
        }

        try {
            $ok = $user->update($updateData);
        } catch (PDOException $e) {
            if ($this->isDuplicateEmailException($e)) {
                return [
                    'success' => false,
                    'message' => 'Ya existe un usuario con ese correo.',
                    'errors' => [
                        'email' => ['Ese correo ya está registrado.'],
                    ],
                    'old' => $payload,
                    'user' => $user,
                ];
            }

            return [
                'success' => false,
                'message' => 'No fue posible actualizar el usuario.',
                'errors' => [
                    'general' => ['Ocurrió un error inesperado al actualizar el usuario.'],
                ],
                'old' => $payload,
                'user' => $user,
            ];
        }

        if (!$ok) {
            return [
                'success' => false,
                'message' => 'No fue posible actualizar el usuario.',
                'errors' => ['general' => ['No fue posible actualizar el usuario.']],
                'user' => $user,
            ];
        }

        $message = 'Usuario actualizado correctamente.';

        if ($data['password'] !== '') {
            AdminSession::revokeAllByUser($userId);
            $message = 'Usuario actualizado correctamente. Se cerraron sus sesiones activas por cambio de contraseña.';
        }

        if ($emailChanged) {
            AdminSession::revokeAllByUser($userId);

            try {
                $plainVerificationToken = random_token(32);
                AdminEmailVerification::createToken(
                    adminUserId: $userId,
                    plainToken: $plainVerificationToken,
                    ttlHours: 72
                );

                $verificationUrl = app_url('/admin/users/confirmUser?token=' . urlencode($plainVerificationToken));
                $mailService = new AdminMailService();
                $mailResult = $mailService->sendVerificationEmail(
                    email: $data['email'],
                    name: AdminUser::buildFullName($data['first_name'], $data['last_name']) ?: 'Administrador',
                    verificationUrl: $verificationUrl
                );

                $message = $mailResult['success']
                    ? 'Usuario actualizado. El correo cambió y se envió una nueva verificación. La cuenta queda pendiente hasta verificar el nuevo correo.'
                    : 'Usuario actualizado. El correo cambió, pero no fue posible enviar la nueva verificación. La cuenta queda pendiente de verificación.';
            } catch (\Throwable $e) {
                $message = 'Usuario actualizado. El correo cambió, pero ocurrió un problema al generar la nueva verificación. La cuenta queda pendiente.';
            }
        }

        return [
            'success' => true,
            'message' => $message,
            'errors' => [],
            'user' => AdminUser::find($userId) ?? $user,
        ];
    }

    public function delete(int $userId, int $currentUserId, ?AdminUser $actor): array
    {
        if (!$actor || !$actor->can('manage_users')) {
            return [
                'success' => false,
                'message' => 'No tienes permisos para eliminar usuarios.',
            ];
        }

        $user = AdminUser::find($userId);

        if (!$user) {
            return [
                'success' => false,
                'message' => 'Usuario no encontrado.',
            ];
        }

        if ($userId === $currentUserId) {
            return [
                'success' => false,
                'message' => 'No puedes eliminar tu propia cuenta.',
            ];
        }

        if ($user->isSuperAdmin()) {
            return [
                'success' => false,
                'message' => 'No puedes eliminar otro super administrador.',
            ];
        }

        try {
            // Este proyecto usa soft delete para admin_users. Antes de ocultar el registro,
            // dejamos la cuenta inactiva, revocamos sesiones y liberamos el correo único
            // para que pueda volver a invitarse el mismo correo si es necesario.
            $deletedEmail = sprintf('deleted.user.%d.%s@deleted.local', $userId, time());

            AdminSession::revokeAllByUser($userId);

            $updated = $user->update([
                'email' => $deletedEmail,
                'phone' => null,
                'status' => 'inactive',
                'locked_until' => null,
                'failed_login_attempts' => 0,
            ]);

            $ok = $updated && $user->delete();
        } catch (\Throwable $e) {
            $ok = false;
        }

        return [
            'success' => $ok,
            'message' => $ok
                ? 'Usuario eliminado correctamente. La cuenta quedó inactiva, oculta y con sesiones revocadas.'
                : 'No fue posible eliminar el usuario.',
        ];
    }

    public function changeStatus(int $userId, string $status, int $currentUserId, ?AdminUser $actor): array
    {
        if (!$actor || !$actor->can('manage_users')) {
            return [
                'success' => false,
                'message' => 'No tienes permisos para cambiar estados de usuarios.',
            ];
        }

        $user = AdminUser::find($userId);

        if (!$user) {
            return [
                'success' => false,
                'message' => 'Usuario no encontrado.',
            ];
        }

        if (!in_array($status, ['active', 'inactive', 'blocked'], true)) {
            return [
                'success' => false,
                'message' => 'El estado seleccionado no es válido.',
            ];
        }

        if ($userId === $currentUserId && $status !== 'active') {
            return [
                'success' => false,
                'message' => 'No puedes desactivar o bloquear tu propia cuenta.',
            ];
        }

        if ($user->isSuperAdmin() && !$actor->isSuperAdmin()) {
            return [
                'success' => false,
                'message' => 'No tienes permisos para cambiar el estado de este usuario.',
            ];
        }

        $ok = $user->update([
            'status' => $status,
        ]);

        return [
            'success' => $ok,
            'message' => $ok
                ? 'Estado actualizado correctamente.'
                : 'No fue posible actualizar el estado.',
        ];
    }

    protected function normalize(array $payload): array
    {
        return [
            'first_name' => mb_substr(trim((string) ($payload['first_name'] ?? '')), 0, 100),
            'last_name' => mb_substr(trim((string) ($payload['last_name'] ?? '')), 0, 100),
            'email' => mb_substr(mb_strtolower(trim((string) ($payload['email'] ?? ''))), 0, 150),
            'phone' => mb_substr(trim((string) ($payload['phone'] ?? '')), 0, 30),
            'role' => trim((string) ($payload['role'] ?? 'seller')),
            'status' => trim((string) ($payload['status'] ?? 'pending_verification')),
            'password' => (string) ($payload['password'] ?? ''),
        ];
    }

    protected function validateCreate(array $data): array
    {
        $errors = [];

        if ($data['first_name'] === '') {
            $errors['first_name'][] = 'El nombre es obligatorio.';
        }

        if ($data['last_name'] === '') {
            $errors['last_name'][] = 'El apellido es obligatorio.';
        }

        if ($data['email'] === '' || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'][] = 'Debes ingresar un correo válido.';
        }

        if ($data['phone'] === '') {
            $errors['phone'][] = 'El teléfono es obligatorio.';
        }

        if (!in_array($data['role'], ['super_admin', 'seller'], true)) {
            $errors['role'][] = 'El rol seleccionado no es válido.';
        }

        if ($data['password'] !== '' && mb_strlen($data['password']) < 8) {
            $errors['password'][] = 'La contraseña debe tener mínimo 8 caracteres.';
        }

        return $errors;
    }

    protected function validateUpdate(array $data, int $userId): array
    {
        $errors = [];

        if ($data['first_name'] === '') {
            $errors['first_name'][] = 'El nombre es obligatorio.';
        }

        if ($data['last_name'] === '') {
            $errors['last_name'][] = 'El apellido es obligatorio.';
        }

        if ($data['email'] === '' || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'][] = 'Debes ingresar un correo válido.';
        }

        if ($data['phone'] === '') {
            $errors['phone'][] = 'El teléfono es obligatorio.';
        }

        if (!in_array($data['role'], ['super_admin', 'seller'], true)) {
            $errors['role'][] = 'El rol seleccionado no es válido.';
        }

        if ($data['password'] !== '' && mb_strlen($data['password']) < 8) {
            $errors['password'][] = 'La nueva contraseña debe tener mínimo 8 caracteres.';
        }

        $existing = AdminUser::findByEmail($data['email']);
        if ($existing && (int) $existing->id !== $userId) {
            $errors['email'][] = 'Ya existe otro usuario con ese correo.';
        }

        return $errors;
    }

    protected function generateTemporaryPassword(int $length = 12): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%';
        $max = strlen($alphabet) - 1;
        $password = '';

        for ($i = 0; $i < $length; $i++) {
            $password .= $alphabet[random_int(0, $max)];
        }

        return $password;
    }

    protected function isDuplicateEmailException(PDOException $e): bool
    {
        $message = $e->getMessage();

        return str_contains($message, '1062')
            && str_contains($message, 'admin_users.email');
    }
}
