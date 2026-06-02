<?php

namespace app\Controllers\api;

use app\Core\Response;
use app\Services\Crm\LeadService;

class ChatbotLeadController
{
    public function whatsappAccepted(): void
    {
        $this->authorize();
        $payload = $this->jsonBody();

        if (($payload['accepted'] ?? null) !== true) {
            Response::json([
                'ok' => false,
                'message' => 'Solo se crean leads cuando accepted=true.',
            ], 422);
            return;
        }

        try {
            $service = new LeadService();
            $result = $service->upsertFromWhatsApp($payload);

            Response::json([
                'ok' => true,
                'created' => $result['created'],
                'lead_id' => $result['lead_id'],
            ]);
        } catch (\Throwable $e) {
            Response::json([
                'ok' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    private function authorize(): void
    {
        $expected = (string) (getenv('CHATBOT_INBOUND_TOKEN') ?: getenv('CHATBOT_API_TOKEN') ?: '');

        if ($expected === '') {
            Response::json([
                'ok' => false,
                'message' => 'CHATBOT_INBOUND_TOKEN no está configurado en la página PHP.',
            ], 500);
            exit;
        }

        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
        $token = '';
        if (preg_match('/Bearer\s+(.+)/i', $header, $m)) {
            $token = trim($m[1]);
        }

        if (!hash_equals($expected, $token)) {
            Response::json([
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
            Response::json([
                'ok' => false,
                'message' => 'JSON inválido.',
            ], 400);
            exit;
        }

        return $data;
    }
}
