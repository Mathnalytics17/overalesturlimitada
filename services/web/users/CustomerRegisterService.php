<?php

namespace app\Services\Web\Users;

use app\Models\Customer;
use app\Models\CustomerAccount;
use app\Models\CustomerEmailVerification;
use app\Services\Mail\CustomerMailService;


class CustomerRegisterService
{
    public function register(array $input): array
    {
        $firstName = trim($input['first_name'] ?? '');
        $lastName = trim($input['last_name'] ?? '');
        $email = trim(mb_strtolower($input['email'] ?? ''));
        $phone = trim($input['phone'] ?? '');
        $whatsapp = trim($input['whatsapp'] ?? '');
        $password = $input['password'] ?? '';
        $passwordConfirmation = $input['password_confirmation'] ?? '';
        $acceptsMarketing = !empty($input['accepts_marketing']) ? 1 : 0;

        $errors = [];

        if ($firstName === '') {
            $errors['first_name'][] = 'El nombre es obligatorio.';
        }

        if ($email === '') {
            $errors['email'][] = 'El correo es obligatorio.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'][] = 'El correo no es válido.';
        }

        if ($password === '') {
            $errors['password'][] = 'La contraseña es obligatoria.';
        } elseif (strlen($password) < 8) {
            $errors['password'][] = 'La contraseña debe tener al menos 8 caracteres.';
        }

        if ($password !== $passwordConfirmation) {
            $errors['password_confirmation'][] = 'Las contraseñas no coinciden.';
        }

        $existingAccount = CustomerAccount::findByEmail($email);
        if ($existingAccount) {
            $errors['email'][] = 'Ya existe una cuenta con este correo.';
        }

        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => 'Error de validación.',
                'data' => [],
                'errors' => $errors,
            ];
        }

        $fullName = trim($firstName . ' ' . $lastName);

        $customer = Customer::findByEmail($email);

        if (!$customer) {
            $customer = Customer::create([
                'uuid' => uuid(),
                'first_name' => $firstName,
                'last_name' => $lastName !== '' ? $lastName : null,
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone !== '' ? $phone : null,
                'whatsapp' => $whatsapp !== '' ? $whatsapp : null,
                'document_type' => null,
                'document_number' => null,
                'birth_date' => null,
                'country_id' => null,
                'city_id' => null,
                'address' => null,
                'status' => 'active',
                'source' => 'web_register',
                'notes' => null,
                'accepts_marketing' => $acceptsMarketing,
            ]);
        }

        if (!$customer) {
            return [
                'success' => false,
                'message' => 'No fue posible crear el cliente.',
                'data' => [],
                'errors' => [
                    'system' => ['Error interno al registrar el cliente.'],
                ],
            ];
        }

        $account = CustomerAccount::create([
            'customer_id' => (int) $customer->id,
            'username' => $email,
            'email' => $email,
            'password_hash' => CustomerAccount::hashPassword($password),
            'status' => 'pending_verification',
            'email_verified_at' => null,
            'last_login_at' => null,
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ]);

        if (!$account) {
            return [
                'success' => false,
                'message' => 'No fue posible crear la cuenta.',
                'data' => [],
                'errors' => [
                    'system' => ['Error interno al registrar la cuenta.'],
                ],
            ];
        }

        $plainVerificationToken = \random_token(32);

        CustomerEmailVerification::createToken(
            customerAccountId: (int) $account->id,
            plainToken: $plainVerificationToken,
            ttlHours: 24
        );

        $verificationUrl = app_url('/users/confirmUser?token=' . urlencode($plainVerificationToken));

        $mailService = new CustomerMailService();
        $mailResult = $mailService->sendVerificationEmail(
            email: $email,
            name: $fullName !== '' ? $fullName : $firstName,
            verificationUrl: $verificationUrl
        );

        return [
            'success' => true,
            'message' => $mailResult['success']
                ? 'Cuenta creada correctamente. Revisa tu correo para verificarla.'
                : 'Cuenta creada correctamente, pero no fue posible enviar el correo de verificación.',
            'data' => [
                'customer' => $customer->toArray(),
                'account' => $account->toArray(),
                'mail_result' => $mailResult,
            ],
            'errors' => [],
        ];
    }
}
