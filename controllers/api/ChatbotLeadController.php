<?php

namespace app\Controllers\api;

use app\Services\Crm\LeadService;

class ChatbotLeadController
{
    public function whatsappAccepted($request = null): void
    {
        $this->authorize();

        $payload = $this->jsonBody();

        if (($payload['accepted'] ?? null) !== true) {
            $this->json([
                'ok' => false,
                'message' => 'Solo se crean leads cuando accepted=true.',
            ], 422);
            return;
        }

        try {
            $service = new LeadService();
            $result = $service->upsertFromWhatsApp($payload);

            $this->json([
                'ok' => true,
                'created' => $result['created'] ?? false,
                'lead_id' => $result['lead_id'] ?? null,
            ], 200);
            return;
        } catch (\Throwable $e) {
            $this->json([
                'ok' => false,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
            return;
        }
    }

    private function authorize(): void
    {
        $expected = (string) (
            getenv('CHATBOT_INBOUND_TOKEN')
            ?: getenv('CHATBOT_API_TOKEN')
            ?: ''
        );

        if ($expected === '') {
            $this->json([
                'ok' => false,
                'message' => 'CHATBOT_INBOUND_TOKEN no está configurado en la página PHP.',
            ], 500);
            exit;
        }

        $header = $_SERVER['HTTP_AUTHORIZATION']
            ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
            ?? '';

        $token = '';

        if (preg_match('/Bearer\s+(.+)/i', $header, $m)) {
            $token = trim($m[1]);
        }

        if ($token === '' || !hash_equals($expected, $token)) {
            $this->json([
                'ok' => false,
                'message' => 'No autorizado.',
            ], 401);
            exit;
        }
    }

    private function jsonBody(): array
    {
        $raw = file_get_contents('php://input') ?: '';
        $data = json_decode($raw, true);

        if (!is_array($data)) {
            $this->json([
                'ok' => false,
                'message' => 'JSON inválido.',
                'raw' => $raw,
            ], 400);
            exit;
        }

        return $data;
    }

    private function json(array $data, int $status = 200): void
    {
        if (!headers_sent()) {
            http_response_code($status);
            header('Content-Type: application/json; charset=utf-8');
        }

        echo json_encode(
            $data,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }
}
