<?php

namespace app\Controllers\admin\chatbot;

use app\core\Controller;
use app\Services\ChatbotCrmApiClient;

class ChatbotController extends Controller
{
    private ChatbotCrmApiClient $api;

    public function __construct()
    {
        $this->api = new ChatbotCrmApiClient();
    }

    public function index(): string
    {
        $filters = [
            'q' => trim((string) ($_GET['q'] ?? '')),
            'bot_session' => trim((string) ($_GET['bot_session'] ?? '')),
            'consent' => trim((string) ($_GET['consent'] ?? '')),
            'page' => max(1, (int) ($_GET['page'] ?? 1)),
            'per_page' => min(100, max(10, (int) ($_GET['per_page'] ?? 50))),
        ];

        $response = $this->api->contacts($filters);
        $contacts = $this->normalizeList($response, ['contacts', 'data', 'results']);

        return $this->render('admin/chatbot/index', [
            'title' => 'Chatbot WhatsApp',
            'active' => 'chatbot',
            'page_title' => 'Chatbot WhatsApp',
            'page_subtitle' => 'Contactos, políticas y conversaciones',
            'contacts' => $contacts,
            'filters' => $filters,
            'error' => ($response['ok'] ?? false) ? null : ($response['error'] ?? 'No se pudo cargar la información.'),
            'summary' => $response['summary'] ?? $response['meta'] ?? [],
        ], 'adminUserLayout');
    }

    public function show(): string
    {
        $id = (string) ($_GET['id'] ?? '');
        $botSession = trim((string) ($_GET['bot_session'] ?? '')) ?: null;

        if ($id === '') {
            redirect('/admin/chatbot');
        }

        $contactResponse = $this->api->contact($id, $botSession);
        $messagesResponse = $this->api->messages($id, $botSession);

        return $this->render('admin/chatbot/show', [
            'title' => 'Detalle de conversación',
            'active' => 'chatbot',
            'page_title' => 'Detalle de conversación',
            'page_subtitle' => 'Mensajes del contacto',
            'contact' => $contactResponse['contact'] ?? $contactResponse['data'] ?? $contactResponse,
            'messages' => $this->normalizeList($messagesResponse, ['messages', 'data', 'results']),
            'error' => ($contactResponse['ok'] ?? false) ? null : ($contactResponse['error'] ?? 'No se pudo cargar el contacto.'),
            'messagesError' => ($messagesResponse['ok'] ?? false) ? null : ($messagesResponse['error'] ?? 'No se pudieron cargar los mensajes.'),
            'id' => $id,
            'botSession' => $botSession,
        ], 'adminUserLayout');
    }

    public function export(): void
    {
        $filters = [
            'q' => trim((string) ($_GET['q'] ?? '')),
            'bot_session' => trim((string) ($_GET['bot_session'] ?? '')),
            'consent' => trim((string) ($_GET['consent'] ?? '')),
            'page' => 1,
            'per_page' => 10000,
        ];

        $response = $this->api->contacts($filters);
        $contacts = $this->normalizeList($response, ['contacts', 'data', 'results']);

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="chatbot_contactos_' . date('Ymd_His') . '.csv"');
        echo "\xEF\xBB\xBF";

        $out = fopen('php://output', 'w');
        fputcsv($out, ['ID', 'Teléfono/JID', 'Nombre', 'Sesión', 'Aceptó política', 'Estado', 'Último mensaje', 'Fecha registro']);

        foreach ($contacts as $contact) {
            fputcsv($out, [
                $contact['id'] ?? '',
                $contact['phone_number'] ?? $contact['phone'] ?? $contact['from'] ?? '',
                $contact['name'] ?? $contact['notify_name'] ?? '',
                $contact['bot_session'] ?? '',
                $this->consentLabel($contact),
                $contact['state_name'] ?? $contact['current_state'] ?? $contact['status'] ?? '',
                $contact['last_message_at'] ?? $contact['updated_at'] ?? '',
                $contact['created_at'] ?? '',
            ]);
        }
        fclose($out);
        exit;
    }

    public function exportMessages(): void
    {
        $id = (string) ($_GET['id'] ?? '');
        $botSession = trim((string) ($_GET['bot_session'] ?? '')) ?: null;
        $response = $this->api->messages($id, $botSession);
        $messages = $this->normalizeList($response, ['messages', 'data', 'results']);

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="chatbot_mensajes_' . date('Ymd_His') . '.csv"');
        echo "\xEF\xBB\xBF";

        $out = fopen('php://output', 'w');
        fputcsv($out, ['Fecha', 'Dirección', 'Tipo', 'Mensaje', 'Sesión']);

        foreach ($messages as $message) {
            fputcsv($out, [
                $message['created_at'] ?? $message['timestamp'] ?? '',
                $this->directionLabel($message),
                $message['message_type'] ?? $message['type'] ?? '',
                $message['body'] ?? $message['message'] ?? $message['content'] ?? '',
                $message['bot_session'] ?? $botSession ?? '',
            ]);
        }
        fclose($out);
        exit;
    }

    private function normalizeList(array $response, array $keys): array
    {
        foreach ($keys as $key) {
            if (isset($response[$key]) && is_array($response[$key])) {
                return $response[$key];
            }
        }
        return [];
    }

    private function consentLabel(array $contact): string
    {
        $value = $contact['accepted_policy'] ?? $contact['policy_accepted'] ?? $contact['consent'] ?? $contact['accepted'] ?? null;
        if ($value === true || $value === 1 || $value === '1' || $value === 'accepted' || $value === 'Acepto') {
            return 'Aceptó';
        }
        if ($value === false || $value === 0 || $value === '0' || $value === 'rejected' || $value === 'No acepto') {
            return 'No aceptó';
        }
        return 'Pendiente';
    }

    private function directionLabel(array $message): string
    {
        $fromMe = $message['from_me'] ?? $message['fromMe'] ?? null;
        if ($fromMe === true || $fromMe === 1 || $fromMe === '1') {
            return 'Bot / Alestur';
        }
        return 'Cliente';
    }
}
