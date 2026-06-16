<?php

namespace app\Services;

class ChatbotCrmApiClient
{
    private string $baseUrl;
    private string $token;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) (getenv('CHATBOT_API_BASE_URL') ?: 'https://alesturslimitadaapi.top'), '/');
        $this->token = (string) (getenv('CHATBOT_API_TOKEN') ?: '');
        $this->timeout = (int) (getenv('CHATBOT_API_TIMEOUT') ?: 20);
    }

    public function contacts(array $filters = []): array
    {
        return $this->get('/api/crm/contacts', $filters);
    }

    public function contact(string|int $userId, ?string $botSession = null): array
    {
        return $this->get('/api/crm/contacts/' . urlencode((string) $userId), ['bot_session' => $botSession]);
    }

    public function messages(string|int $userId, ?string $botSession = null): array
    {
        return $this->get('/api/crm/contacts/' . urlencode((string) $userId) . '/messages', ['bot_session' => $botSession]);
    }

    public function conversation(string|int $userId, ?string $botSession = null): array
    {
        return $this->get('/api/crm/contacts/' . urlencode((string) $userId) . '/conversation', ['bot_session' => $botSession]);
    }

    public function exportContactsUrl(array $filters = []): string
    {
        $url = $this->baseUrl . '/api/crm/contacts/export';

        if ($filters) {
            $url .= '?' . http_build_query(array_filter($filters, static fn($v) => $v !== null && $v !== ''));
        }

        return $url;
    }

    public function exportMessagesUrl(string|int $userId): string
    {
        return $this->baseUrl . '/api/crm/contacts/' . urlencode((string) $userId) . '/messages/export';
    }

    public function health(): array
    {
        return $this->get('/api/crm/health');
    }

    private function get(string $path, array $query = []): array
    {
        if ($this->token === '') {
            return [
                'ok' => false,
                'error' => 'Falta configurar CHATBOT_API_TOKEN en el .env de la página PHP.'
            ];
        }

        $url = $this->baseUrl . $path;

        if ($query) {
            $url .= '?' . http_build_query(array_filter($query, static fn($v) => $v !== null && $v !== ''));
        }

        if (function_exists('curl_init')) {
            $ch = curl_init($url);

            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => $this->timeout,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTPHEADER => [
                    'Accept: application/json',
                    'Authorization: Bearer ' . $this->token,
                ],
            ]);

            $body = curl_exec($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);

            curl_close($ch);

            if ($body === false) {
                return [
                    'ok' => false,
                    'error' => $error ?: 'No fue posible conectar con la API del chatbot.'
                ];
            }
        } else {
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'timeout' => $this->timeout,
                    'header' => "Accept: application/json\r\nAuthorization: Bearer {$this->token}\r\n",
                    'ignore_errors' => true,
                ],
            ]);

            $body = file_get_contents($url, false, $context);

            $status = 0;

            if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $m)) {
                $status = (int) $m[1];
            }

            if ($body === false) {
                return [
                    'ok' => false,
                    'error' => 'No fue posible conectar con la API del chatbot.'
                ];
            }
        }

        $json = json_decode($body, true);

        if (!is_array($json)) {
            return [
                'ok' => false,
                'error' => "Respuesta inválida de la API del chatbot. HTTP {$status}",
                'raw' => $body,
                'url' => $url,
            ];
        }

        if ($status < 200 || $status >= 300) {
            return [
                'ok' => false,
                'error' => $json['message'] ?? $json['error'] ?? "Error HTTP {$status}",
                'raw' => $json,
                'url' => $url,
            ];
        }

        $json['ok'] = $json['ok'] ?? true;

        return $json;
    }
}
