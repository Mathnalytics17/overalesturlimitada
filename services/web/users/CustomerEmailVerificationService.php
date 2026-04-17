<?php

namespace app\Services\Web\Users;

use app\Models\CustomerAccount;
use app\Models\CustomerEmailVerification;
use app\Services\Mail\CustomerMailService;

class CustomerEmailVerificationService
{
    public function verify(string $token): array
    {
        $token = trim($token);

        if ($token === '') {
            return [
                'success' => false,
                'message' => 'Token invalido.',
                'errors' => [
                    'token' => ['El token es obligatorio.'],
                ],
                'data' => [],
            ];
        }

        $verification = CustomerEmailVerification::findValidToken($token);

        if (!$verification) {
            return [
                'success' => false,
                'message' => 'El enlace de verificacion no es valido o ha expirado.',
                'errors' => [
                    'token' => ['Token invalido o expirado.'],
                ],
                'data' => [],
            ];
        }

        $account = CustomerAccount::find((int) $verification->customer_account_id);

        if (!$account) {
            return [
                'success' => false,
                'message' => 'No se encontro la cuenta asociada.',
                'errors' => [
                    'account' => ['Cuenta no encontrada.'],
                ],
                'data' => [],
            ];
        }

        $verification->markAsVerified();
        $account->markEmailVerified();
        security_event('customer_email_verified', [
            'account_id' => (int) $account->id,
            'email' => (string) ($account->email ?? ''),
        ]);

        return [
            'success' => true,
            'message' => 'Correo verificado correctamente.',
            'errors' => [],
            'data' => [
                'account' => $account->toArray(),
            ],
        ];
    }

    public function resend(string $email): array
    {
        $email = trim(mb_strtolower($email));

        if ($email === '') {
            return [
                'success' => false,
                'message' => 'Debes ingresar un correo.',
                'errors' => [
                    'email' => ['El correo es obligatorio.'],
                ],
                'data' => [],
            ];
        }

        $account = CustomerAccount::findByEmail($email);

        if (!$account) {
            return [
                'success' => true,
                'message' => 'Si la cuenta existe, se enviara un nuevo enlace de verificacion.',
                'errors' => [],
                'data' => [],
            ];
        }

        if (!empty($account->email_verified_at)) {
            return [
                'success' => true,
                'message' => 'La cuenta ya esta verificada.',
                'errors' => [],
                'data' => [],
            ];
        }

        $plainToken = random_token(32);

        CustomerEmailVerification::createToken(
            customerAccountId: (int) $account->id,
            plainToken: $plainToken,
            ttlHours: 24
        );

        $verificationUrl = app_url('/users/confirmUser?token=' . urlencode($plainToken));
        $mailService = new CustomerMailService();
        $mailService->sendVerificationEmail(
            email: $account->email,
            name: 'Usuario',
            verificationUrl: $verificationUrl
        );
        security_event('customer_verification_resent', [
            'account_id' => (int) $account->id,
            'email' => (string) ($account->email ?? ''),
        ]);

        return [
            'success' => true,
            'message' => 'Si la cuenta existe, se enviara un nuevo enlace de verificacion.',
            'errors' => [],
            'data' => [],
        ];
    }
}
