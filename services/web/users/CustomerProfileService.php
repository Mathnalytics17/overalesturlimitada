<?php

namespace app\Services\Web\Users;

use app\Core\CustomerAuth;
use app\Models\CustomerAccount;
use app\Models\Customer;
use app\Services\Profile\ProfilePhotoUploadService;

class CustomerProfileService
{
    public function changePassword(
        int $accountId,
        string $currentPassword,
        string $newPassword,
        string $newPasswordConfirmation
    ): array {
        $account = CustomerAccount::find($accountId);

        if (!$account) {
            return [
                'success' => false,
                'message' => 'Cuenta no encontrada.',
                'errors' => [
                    'account' => ['Cuenta no encontrada.'],
                ],
                'data' => [],
            ];
        }

        $errors = [];

        if ($currentPassword === '') {
            $errors['current_password'][] = 'La contraseña actual es obligatoria.';
        } elseif (!$account->verifyPassword($currentPassword)) {
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

        $updated = $account->update([
            'password_hash' => CustomerAccount::hashPassword($newPassword),
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

        CustomerAuth::logoutAllDevices((int) $account->id);

        return [
            'success' => true,
            'message' => 'Contraseña actualizada correctamente.',
            'errors' => [],
            'data' => [
                'force_relogin' => true,
            ],
        ];
    }
    
public function updateProfile(int $customerId, array $input, array $files = []): array
    {
        $customer = Customer::find($customerId);

        if (!$customer) {
            return [
                'success' => false,
                'message' => 'Cliente no encontrado.',
                'errors' => [
                    'customer' => ['Cliente no encontrado.'],
                ],
                'data' => [],
            ];
        }

        $firstName = trim($input['first_name'] ?? '');
        $lastName = trim($input['last_name'] ?? '');
        $phone = trim($input['phone'] ?? '');
        $whatsapp = trim($input['whatsapp'] ?? '');
        $documentType = trim($input['document_type'] ?? '');
        $documentNumber = trim($input['document_number'] ?? '');
        $birthDate = trim($input['birth_date'] ?? '');
        $address = trim($input['address'] ?? '');

        $errors = [];

        if ($firstName === '') {
            $errors['first_name'][] = 'Los nombres son obligatorios.';
        }

        if ($lastName === '') {
            $errors['last_name'][] = 'Los apellidos son obligatorios.';
        }

        if (!empty($birthDate) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthDate)) {
            $errors['birth_date'][] = 'La fecha de nacimiento no es válida.';
        }

        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => 'Error de validación.',
                'errors' => $errors,
                'data' => [],
            ];
        }

        $photoService = new ProfilePhotoUploadService();
        $photoResult = $photoService->upload($files['profile_photo'] ?? [], 'customer', $customerId);

        if (!$photoResult['success']) {
            return [
                'success' => false,
                'message' => 'Revisa la foto de perfil.',
                'errors' => $photoResult['errors'] ?? [],
                'data' => [],
            ];
        }

        $newPhotoPath = $photoResult['path'] ?? null;
        $oldPhotoPath = $customer->profile_photo_path ?? null;
        $data = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'full_name' => trim($firstName . ' ' . $lastName),
            'phone' => $phone !== '' ? $phone : null,
            'whatsapp' => $whatsapp !== '' ? $whatsapp : null,
            'document_type' => $documentType !== '' ? $documentType : null,
            'document_number' => $documentNumber !== '' ? $documentNumber : null,
            'birth_date' => $birthDate !== '' ? $birthDate : null,
            'address' => $address !== '' ? $address : null,
        ];

        if ($newPhotoPath !== null) {
            $data['profile_photo_path'] = $newPhotoPath;
        }

        $updated = $customer->update($data);

        if (!$updated) {
            $photoService->deleteStored($newPhotoPath);

            return [
                'success' => false,
                'message' => 'No fue posible actualizar el perfil.',
                'errors' => [
                    'system' => ['Error interno al actualizar el perfil.'],
                ],
                'data' => [],
            ];
        }

        if ($newPhotoPath !== null) {
            $photoService->deleteStored($oldPhotoPath);
        }

        return [
            'success' => true,
            'message' => 'Perfil actualizado correctamente.',
            'errors' => [],
            'data' => [],
        ];
    }

}
