<?php

namespace app\Services\Security;

class TurnstileService
{
    public function verify(?string $token, ?string $ipAddress = null): array
    {
        if (!turnstile_enabled()) {
            return [
                'success' => true,
                'message' => null,
            ];
        }

        $token = trim((string) $token);
        if ($token === '') {
            return [
                'success' => false,
                'message' => 'Debes completar la verificacion de seguridad.',
            ];
        }

        $secretKey = (string) env('TURNSTILE_SECRET_KEY', '');
        if ($secretKey === '') {
            return [
                'success' => false,
                'message' => 'La verificacion de seguridad no esta configurada correctamente.',
            ];
        }

        $payload = http_build_query([
            'secret' => $secretKey,
            'response' => $token,
            'remoteip' => $ipAddress ?? '',
        ]);

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => $payload,
                'timeout' => 10,
            ],
        ]);

        $response = @file_get_contents('https://challenges.cloudflare.com/turnstile/v0/siteverify', false, $context);
        if (!is_string($response) || $response === '') {
            app_log('security', 'Turnstile verification failed: empty response');

            return [
                'success' => false,
                'message' => 'No fue posible validar la verificacion de seguridad.',
            ];
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded) || !($decoded['success'] ?? false)) {
            $errorCodes = is_array($decoded['error-codes'] ?? null) ? implode(',', $decoded['error-codes']) : 'unknown';
            app_log('security', 'Turnstile verification rejected: ' . $errorCodes);

            return [
                'success' => false,
                'message' => 'La verificacion de seguridad no fue valida. Intenta nuevamente.',
            ];
        }

        return [
            'success' => true,
            'message' => null,
        ];
    }
}
