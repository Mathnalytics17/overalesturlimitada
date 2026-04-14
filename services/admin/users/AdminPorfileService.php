<?php

namespace app\Services\Admin\Users;

use app\Models\AdminUser;

class AdminProfileService
{
    public function updateProfile(int $adminUserId, array $input): array
    {
        $user = AdminUser::find($adminUserId);

        if (!$user) {
            return [
                'success' => false,
                'message' => 'Administrador no encontrado.',
                'errors' => [
                    'user' => ['Administrador no encontrado.'],
                ],
                'data' => [],
            ];
        }

        $firstName = trim($input['first_name'] ?? '');
        $lastName = trim($input['last_name'] ?? '');

        $errors = [];

        if ($firstName === '') {
            $errors['first_name'][] = 'Los nombres son obligatorios.';
        }

        if ($lastName === '') {
            $errors['last_name'][] = 'Los apellidos son obligatorios.';
        }

        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => 'Error de validación.',
                'errors' => $errors,
                'data' => [],
            ];
        }

        $updated = $user->update([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'full_name' => trim($firstName . ' ' . $lastName),
        ]);

        if (!$updated) {
            return [
                'success' => false,
                'message' => 'No fue posible actualizar el perfil.',
                'errors' => [
                    'system' => ['Error interno al actualizar el perfil.'],
                ],
                'data' => [],
            ];
        }

        return [
            'success' => true,
            'message' => 'Perfil actualizado correctamente.',
            'errors' => [],
            'data' => [],
        ];
    }

    public function changePassword(
        int $adminUserId,
        string $currentPassword,
        string $newPassword,
        string $newPasswordConfirmation
    ): array {
        $user = AdminUser::find($adminUserId);

        if (!$user) {
            return [
                'success' => false,
                'message' => 'Administrador no encontrado.',
                'errors' => [
                    'user' => ['Administrador no encontrado.'],
                ],
                'data' => [],
            ];
        }

        $errors = [];

        if ($currentPassword === '') {
            $errors['current_password'][] = 'La contraseña actual es obligatoria.';
        } elseif (!$user->verifyPassword($currentPassword)) {
            $errors['current_password'][] = 'La contraseña actual no es correcta.';
        }

        if ($newPassword === '') {
            $errors['new_password'][] = 'La nueva contraseña es obligatoria.';
        } elseif (strlen($newPassword) < 8) {
            $errors['new_password'][] = 'La nueva contraseña debe tener al menos 8 caracteres.';
        }

        if ($newPassword !== $newPasswordConfirmation) {
            $errors['new_password_confirmation'][] = 'Las contraseñas no coinciden.';
        }

        if ($currentPassword !== '' && $newPassword !== '' && $currentPassword === $newPassword) {
            $errors['new_password'][] = 'La nueva contraseña debe ser diferente a la actual.';
        }

        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => 'Error de validación.',
                'errors' => $errors,
                'data' => [],
            ];
        }

        $updated = $user->update([
            'password_hash' => AdminUser::hashPassword($newPassword),
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ]);

        if (!$updated) {
            return [
                'success' => false,
                'message' => 'No fue posible actualizar la contraseña.',
                'errors' => [
                    'system' => ['Error interno al actualizar la contraseña.'],
                ],
                'data' => [],
            ];
        }

        return [
            'success' => true,
            'message' => 'Contraseña actualizada correctamente.',
            'errors' => [],
            'data' => [],
        ];
    }
}