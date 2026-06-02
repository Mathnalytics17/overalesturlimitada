<?php

namespace app\Services\Crm;

use app\Models\Lead;
use app\Models\LeadInteraction;
use app\Models\LeadTask;
use app\Models\ExtraService;
use app\Services\Mail\InboxMailService;

class LeadService
{
    public function createFromWebForm(string $sourceType, array $payload): array
    {
        $normalized = $this->normalizePayload($sourceType, $payload);
        $errors = $this->validate($sourceType, $normalized, $payload);

        if ($errors !== []) {
            return [
                'success' => false,
                'errors' => $errors,
                'old' => $payload,
            ];
        }

        $leadData = [
            'full_name' => $normalized['full_name'],
            'email' => $normalized['email'],
            'phone' => $normalized['phone'],
            'source_type' => $sourceType,
            'channel' => 'web',
            'subject' => $normalized['subject'],
            'message' => $normalized['message'],
            'package_id' => $normalized['package_id'],
            'package_slug' => $normalized['package_slug'],
            'status' => 'new',
            'priority' => 'medium',
            'whatsapp_opt_in' => !empty($payload['acepta']) ? 1 : 0,
            'consent_accepted_at' => !empty($payload['acepta']) ? now() : null,
            'metadata_json' => !empty($normalized['metadata'])
                ? json_encode($normalized['metadata'], JSON_UNESCAPED_UNICODE)
                : null,
            'last_contact_at' => now(),
        ];

        $lead = Lead::create($leadData);

        if (!$lead) {
            return [
                'success' => false,
                'errors' => [
                    'general' => ['No fue posible guardar el caso.']
                ],
                'old' => $payload,
            ];
        }

        $this->logInteraction((int) $lead->id, null, 'web', 'inbound', 'lead_created', 'Caso creado desde formulario web.', [
            'source_type' => $sourceType,
        ]);

        $mail = new InboxMailService();
        $mailResult = $mail->sendWebsiteFormSubmission($sourceType, $payload);

        $this->logInteraction(
            (int) $lead->id,
            null,
            'email',
            'system',
            $mailResult['success'] ? 'internal_email_sent' : 'internal_email_failed',
            $mailResult['message'] ?? 'Notificación interna procesada.'
        );

        $waUrl = $this->buildWhatsAppUrl($lead);

        $this->logInteraction((int) $lead->id, null, 'whatsapp', 'system', 'whatsapp_link_generated', 'Se generó enlace de continuidad por WhatsApp.', [
            'url' => $waUrl,
        ]);

        return [
            'success' => true,
            'lead' => $lead,
            'whatsapp_url' => $waUrl,
            'whatsapp_message' => $this->buildWhatsAppMessage($lead),
        ];
    }

    public function updateStatus(int $leadId, string $status, ?int $adminUserId = null): bool
    {
        $lead = Lead::find($leadId);
        if (!$lead) {
            return false;
        }

        $oldStatus = (string) $lead->status;

        $ok = $lead->update([
            'status' => $status,
            'closed_at' => $status === 'closed' ? now() : null,
            'last_contact_at' => now(),
        ]);

        if ($ok) {
            $this->logInteraction($leadId, $adminUserId, 'internal', 'internal', 'status_changed', "Estado cambiado de {$oldStatus} a {$status}.");
        }

        return $ok;
    }

    public function takeLead(int $leadId, int $adminUserId): bool
    {
        $lead = Lead::find($leadId);
        if (!$lead) {
            return false;
        }

        if (!empty($lead->assigned_admin_user_id) && (int) $lead->assigned_admin_user_id !== $adminUserId) {
            return false;
        }

        $ok = $lead->update([
            'assigned_admin_user_id' => $adminUserId,
            'status' => $lead->status === 'new' ? 'in_progress' : $lead->status,
            'last_contact_at' => now(),
        ]);

        if ($ok) {
            $this->logInteraction($leadId, $adminUserId, 'internal', 'internal', 'taken_by_agent', 'Caso tomado por el asesor #' . $adminUserId . '.');
        }

        return $ok;
    }

    public function releaseLead(int $leadId, int $adminUserId): bool
    {
        $lead = Lead::find($leadId);
        if (!$lead) {
            return false;
        }

        if ((int) ($lead->assigned_admin_user_id ?? 0) !== $adminUserId) {
            return false;
        }

        $newStatus = in_array((string) ($lead->status ?? ''), ['closed', 'lost'], true)
            ? (string) $lead->status
            : 'new';

        $ok = $lead->update([
            'assigned_admin_user_id' => null,
            'status' => $newStatus,
            'last_contact_at' => now(),
        ]);

        if ($ok) {
            $this->logInteraction($leadId, $adminUserId, 'internal', 'internal', 'released_by_agent', 'Caso liberado por el asesor #' . $adminUserId . '.');
        }

        return $ok;
    }

    public function addNote(int $leadId, string $message, ?int $adminUserId = null): bool
    {
        if (trim($message) === '') {
            return false;
        }

        $result = LeadInteraction::create([
            'lead_id' => $leadId,
            'admin_user_id' => $adminUserId,
            'channel' => 'internal',
            'direction' => 'internal',
            'event_type' => 'note',
            'message' => trim($message),
            'meta_json' => null,
        ]);

        if ($result) {
            $lead = Lead::find($leadId);
            if ($lead) {
                $lead->update(['last_contact_at' => now()]);
            }
        }

        return $result !== null;
    }

    public function createTask(int $leadId, string $title, string $description = '', ?string $dueAt = null, ?int $adminUserId = null): ?LeadTask
    {
        if (trim($title) === '') {
            return null;
        }

        $task = LeadTask::create([
            'lead_id' => $leadId,
            'admin_user_id' => $adminUserId,
            'title' => trim($title),
            'description' => trim($description),
            'status' => 'pending',
            'due_at' => $dueAt ?: null,
            'completed_at' => null,
        ]);

        if ($task) {
            $this->logInteraction($leadId, $adminUserId, 'internal', 'internal', 'task_created', 'Se creó tarea: ' . $task->title, [
                'task_id' => $task->id,
                'due_at' => $task->due_at,
            ]);
        }

        return $task;
    }

    public function completeTask(int $taskId, ?int $adminUserId = null): bool
    {
        $task = LeadTask::find($taskId);
        if (!$task) {
            return false;
        }

        $ok = $task->update([
            'status' => 'done',
            'completed_at' => now(),
        ]);

        if ($ok) {
            $this->logInteraction((int) $task->lead_id, $adminUserId, 'internal', 'internal', 'task_completed', 'Se completó la tarea: ' . $task->title, [
                'task_id' => $task->id,
            ]);
        }

        return $ok;
    }

    public function buildWhatsAppUrl(Lead $lead): string
    {
        $number = preg_replace('/\D+/', '', (string) env('WHATSAPP_NUMBER', ''));
        $message = rawurlencode($this->buildWhatsAppMessage($lead));

        if ($number === '') {
            return '';
        }

        return 'https://wa.me/' . $number . '?text=' . $message;
    }

    public function buildExtraServiceWhatsAppUrl($service, array $payload = []): string
    {
        $number = preg_replace('/\D+/', '', (string) env('WHATSAPP_NUMBER', ''));

        if ($number === '' || !$service) {
            return '';
        }

        $name = trim((string) ($payload['nombre'] ?? ''));
        $msg = $service->defaultWhatsAppMessage($name);

        return 'https://wa.me/' . $number . '?text=' . rawurlencode($msg);
    }

    public function buildAdvisorWhatsAppUrl(Lead $lead): string
    {
        $clientNumber = normalize_phone((string) ($lead->phone ?? ''));
        if ($clientNumber === '') {
            return '';
        }

        $message = rawurlencode($this->buildAdvisorWhatsAppMessage($lead));

        return 'https://wa.me/' . $clientNumber . '?text=' . $message;
    }

    public function buildWhatsAppMessage(Lead $lead): string
    {
        $metadata = $lead->metadata_json ?? [];
        if (is_string($metadata)) {
            $metadata = json_decode($metadata, true) ?: [];
        }

        $parts = [
            'Hola, vengo desde la web de Alestur.',
            'Mi nombre es ' . trim((string) $lead->full_name) . '.',
        ];

        if ((string) $lead->source_type === 'tickets') {
            $parts[] = 'Quiero consultar disponibilidad de tiquetes.';

            if (!empty($metadata['country'])) {
                $parts[] = 'País destino: ' . trim((string) $metadata['country']) . '.';
            }

            if (!empty($metadata['city'])) {
                $parts[] = 'Ciudad destino: ' . trim((string) $metadata['city']) . '.';
            }

            if (!empty($metadata['departureDate'])) {
                $parts[] = 'Fecha de ida: ' . trim((string) $metadata['departureDate']) . '.';
            }

            if (!empty($metadata['returnDate']) && empty($metadata['oneWay'])) {
                $parts[] = 'Fecha de regreso: ' . trim((string) $metadata['returnDate']) . '.';
            }

            if (!empty($metadata['oneWay'])) {
                $parts[] = 'Trayecto: solo ida.';
            }

            $adults = (int) ($metadata['adults'] ?? 1);
            $children = (int) ($metadata['children'] ?? 0);
            $babies = (int) ($metadata['babies'] ?? 0);

            $parts[] = 'Viajeros: ' . $adults . ' adulto(s), ' . $children . ' niño(s), ' . $babies . ' bebé(s).';

            if (!empty($metadata['travelsWithPet'])) {
                $parts[] = 'Viaja con mascota.';
            }

            if (!empty($metadata['needsWheelchair'])) {
                $parts[] = 'Necesita silla de ruedas.';
            }

            if (!empty($metadata['sportsEquipment'])) {
                $parts[] = 'Artículo deportivo: ' . trim((string) $metadata['sportsEquipment']) . '.';
            }
        } elseif ((string) $lead->source_type === 'package') {
            $parts[] = 'Quiero recibir información sobre un paquete turístico.';

            if (!empty($metadata['package_title'])) {
                $parts[] = 'Paquete: ' . trim((string) $metadata['package_title']) . '.';
            }

            if (!empty($lead->package_slug)) {
                $parts[] = 'Referencia: ' . trim((string) $lead->package_slug) . '.';
            }

            if (!empty($metadata['travel_month'])) {
                $parts[] = 'Mes estimado de viaje: ' . trim((string) $metadata['travel_month']) . '.';
            }

            if (!empty($metadata['travelers'])) {
                $parts[] = 'Viajeros: ' . trim((string) $metadata['travelers']) . '.';
            }
        } else {
            if (!empty($lead->subject)) {
                $parts[] = 'Motivo: ' . trim((string) $lead->subject) . '.';
            }
        }

        if (!empty($lead->message)) {
            $parts[] = 'Mensaje: ' . trim((string) $lead->message);
        }

        return implode("\n", array_filter($parts));
    }
    public function buildAdvisorWhatsAppMessage(Lead $lead): string
    {
        $metadata = $lead->metadata_json ?? [];
        if (is_string($metadata)) {
            $metadata = json_decode($metadata, true) ?: [];
        }

        $parts = [
            'Hola, te saluda un asesor de Alestur.',
            'Recibimos tu solicitud desde nuestra web.',
        ];

        if ((string) $lead->source_type === 'tickets') {
            $parts[] = 'Solicitud: consulta de tiquetes.';

            if (!empty($metadata['country'])) {
                $parts[] = 'País destino: ' . trim((string) $metadata['country']) . '.';
            }

            if (!empty($metadata['city'])) {
                $parts[] = 'Ciudad destino: ' . trim((string) $metadata['city']) . '.';
            }
        } elseif ((string) $lead->source_type === 'package') {
            $parts[] = 'Solicitud: paquete turístico.';

            if (!empty($metadata['package_title'])) {
                $parts[] = 'Paquete: ' . trim((string) $metadata['package_title']) . '.';
            }

            if (!empty($metadata['travel_month'])) {
                $parts[] = 'Mes estimado de viaje: ' . trim((string) $metadata['travel_month']) . '.';
            }

            if (!empty($metadata['travelers'])) {
                $parts[] = 'Viajeros: ' . trim((string) $metadata['travelers']) . '.';
            }

            $adults = (int) ($metadata['adults'] ?? 1);
            $children = (int) ($metadata['children'] ?? 0);
            $babies = (int) ($metadata['babies'] ?? 0);

            $parts[] = 'Viajeros: ' . $adults . ' adulto(s), ' . $children . ' niño(s), ' . $babies . ' bebé(s).';

            if (!empty($metadata['travelsWithPet'])) {
                $parts[] = 'Viaja con mascota.';
            }

            if (!empty($metadata['needsWheelchair'])) {
                $parts[] = 'Necesita silla de ruedas.';
            }

            if (!empty($metadata['sportsEquipment'])) {
                $parts[] = 'Artículo deportivo: ' . trim((string) $metadata['sportsEquipment']) . '.';
            }
        } elseif (!empty($lead->subject)) {
            $parts[] = 'Asunto: ' . trim((string) $lead->subject) . '.';
        }

        $parts[] = 'Quedo atento para ayudarte con tu solicitud.';

        return implode("\n", array_filter($parts));
    }

    protected function logInteraction(int $leadId, ?int $adminUserId, string $channel, string $direction, string $eventType, string $message, ?array $meta = null): void
    {
        LeadInteraction::create([
            'lead_id' => $leadId,
            'admin_user_id' => $adminUserId,
            'channel' => $channel,
            'direction' => $direction,
            'event_type' => $eventType,
            'message' => $message,
            'meta_json' => !empty($meta)
                ? json_encode($meta, JSON_UNESCAPED_UNICODE)
                : null,
        ]);
    }

    protected function normalizePayload(string $sourceType, array $payload): array
    {
        $fullName = trim((string) ($payload['full_name'] ?? $payload['nombre'] ?? $payload['firstLastName'] ?? $payload['name'] ?? ''));
        $subject = trim((string) ($payload['subject'] ?? ''));
        $subject = mb_substr($subject, 0, 255);

        $message = trim((string) ($payload['message'] ?? $payload['mensaje'] ?? ''));

        $extraService = null;
        if ($sourceType === 'extra_service' && !empty($payload['extra_service_id'])) {
            $extraService = ExtraService::findActiveById((int) $payload['extra_service_id']);
            if ($extraService) {
                $subject = $extraService->leadSubject();
                $subject = mb_substr($subject, 0, 255);
            }
        }

        if ($subject === '' && $sourceType === 'tickets') {
            $country = trim((string) ($payload['country'] ?? ''));
            $city = trim((string) ($payload['city'] ?? ''));
            $subject = 'Solicitud de tiquetes';

            if ($country !== '' || $city !== '') {
                $subject .= ' - ' . trim($country . ' ' . $city);
                $subject = mb_substr($subject, 0, 255);
            }
        }

        if ($subject === '' && $sourceType === 'package') {
            $packageTitle = trim((string) ($payload['package_title'] ?? ''));
            $packageSlug = trim((string) ($payload['package_slug'] ?? ''));

            $subject = $packageTitle !== ''
                ? 'Consulta de paquete - ' . $packageTitle
                : 'Consulta de paquete';

            if ($packageTitle === '' && $packageSlug !== '') {
                $subject .= ' - ' . $packageSlug;
            }

            $subject = mb_substr($subject, 0, 255);
        }

        if ($message === '' && $sourceType === 'tickets') {
            $messageParts = [];

            if (!empty($payload['country'])) {
                $messageParts[] = 'País destino: ' . trim((string) $payload['country']);
            }

            if (!empty($payload['city'])) {
                $messageParts[] = 'Ciudad destino: ' . trim((string) $payload['city']);
            }

            if (!empty($payload['departureDate'])) {
                $messageParts[] = 'Fecha de ida: ' . trim((string) $payload['departureDate']);
            }

            if (!empty($payload['returnDate']) && empty($payload['oneWay'])) {
                $messageParts[] = 'Fecha de regreso: ' . trim((string) $payload['returnDate']);
            }

            if (!empty($payload['oneWay'])) {
                $messageParts[] = 'Trayecto: solo ida';
            }

            $adults = max(1, (int) ($payload['adults'] ?? 1));
            $children = max(0, (int) ($payload['children'] ?? 0));
            $babies = max(0, (int) ($payload['babies'] ?? 0));

            $messageParts[] = 'Viajeros: ' . $adults . ' adulto(s), ' . $children . ' niño(s), ' . $babies . ' bebé(s)';

            if (!empty($payload['travelsWithPet'])) {
                $messageParts[] = 'Viaja con mascota: sí';
            }

            if (!empty($payload['needsWheelchair'])) {
                $messageParts[] = 'Necesita silla de ruedas: sí';
            }

            if (!empty($payload['sportsEquipment'])) {
                $messageParts[] = 'Artículo deportivo: ' . trim((string) $payload['sportsEquipment']);
            }

            $message = implode(' | ', $messageParts);
        }

        if ($message === '' && $sourceType === 'extra_service' && $extraService) {
            $message = 'Hola, quiero recibir información sobre ' . trim((string) $extraService->titulo) . '.';
        }

        if ($message === '' && $sourceType === 'package') {
            $packageTitle = trim((string) ($payload['package_title'] ?? ''));
            $travelMonth = trim((string) ($payload['travel_month'] ?? ''));
            $travelers = trim((string) ($payload['travelers'] ?? ''));

            $messageParts = [];

            $messageParts[] = $packageTitle !== ''
                ? 'Hola, me interesa el paquete ' . $packageTitle . ' y quiero continuar la atención por WhatsApp.'
                : 'Hola, me interesa este paquete y quiero continuar la atención por WhatsApp.';

            if ($travelMonth !== '') {
                $messageParts[] = 'Mes estimado de viaje: ' . $travelMonth . '.';
            }

            if ($travelers !== '') {
                $messageParts[] = 'Viajeros: ' . $travelers . '.';
            }

            $message = implode(' ', $messageParts);
        }

        return [
            'full_name' => $fullName,
            'email' => trim((string) ($payload['email'] ?? '')),
            'phone' => normalize_phone((string) ($payload['telefono'] ?? $payload['phone'] ?? '')),
            'subject' => $subject,
            'message' => $message,
            'package_id' => !empty($payload['package_id']) ? (int) $payload['package_id'] : null,
            'package_slug' => trim((string) ($payload['package_slug'] ?? '')),
            'metadata' => [
                'country' => $payload['country'] ?? null,
                'city' => $payload['city'] ?? null,
                'departureDate' => $payload['departureDate'] ?? null,
                'returnDate' => $payload['returnDate'] ?? null,
                'oneWay' => !empty($payload['oneWay']),
                'adults' => max(1, (int) ($payload['adults'] ?? 1)),
                'children' => max(0, (int) ($payload['children'] ?? 0)),
                'babies' => max(0, (int) ($payload['babies'] ?? 0)),
                'travelsWithPet' => !empty($payload['travelsWithPet']),
                'needsWheelchair' => !empty($payload['needsWheelchair']),
                'sportsEquipment' => trim((string) ($payload['sportsEquipment'] ?? '')),
                'extra_service_id' => !empty($payload['extra_service_id']) ? (int) $payload['extra_service_id'] : null,
                'extra_service_title' => $extraService->titulo ?? null,
                'extra_service_slug' => $extraService->slug ?? null,

                'package_title' => $payload['package_title'] ?? null,
                'travel_month' => $payload['travel_month'] ?? null,
                'travelers' => !empty($payload['travelers']) ? (int) $payload['travelers'] : null,
            ],
        ];
    }
    protected function validate(string $sourceType, array $data, array $payload = []): array
    {
        $errors = [];

        if ($data['full_name'] === '') {
            $errors['nombre'][] = 'El nombre completo es obligatorio.';
        }

        if ($data['email'] === '' || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'][] = 'Debes ingresar un correo válido.';
        }

        if ($data['phone'] === '') {
            $errors['telefono'][] = 'Debes ingresar un teléfono válido.';
        }

        if ($sourceType === 'tickets') {

            $adults = (int) ($payload['adults'] ?? 1);
            $children = (int) ($payload['children'] ?? 0);
            $babies = (int) ($payload['babies'] ?? 0);

            if ($adults < 1) {
                $errors['adults'][] = 'Debe viajar al menos un adulto.';
            }

            if ($children < 0) {
                $errors['children'][] = 'La cantidad de niños no puede ser negativa.';
            }

            if ($babies < 0) {
                $errors['babies'][] = 'La cantidad de bebés no puede ser negativa.';
            }
            if (trim((string) ($payload['country'] ?? '')) === '') {
                $errors['country'][] = 'Debes seleccionar un país destino.';
            }

            if (trim((string) ($payload['city'] ?? '')) === '') {
                $errors['city'][] = 'Debes seleccionar una ciudad destino.';
            }

            if (trim((string) ($payload['departureDate'] ?? '')) === '') {
                $errors['departureDate'][] = 'Debes ingresar la fecha de ida.';
            }

            if (empty($payload['oneWay']) && !empty($payload['returnDate']) && !empty($payload['departureDate'])) {
                if ((string) $payload['returnDate'] < (string) $payload['departureDate']) {
                    $errors['returnDate'][] = 'La fecha de regreso no puede ser anterior a la fecha de ida.';
                }
            }
        }

        if ($sourceType !== 'tickets' && trim((string) $data['subject']) === '') {
            $errors['subject'][] = 'El asunto es obligatorio.';
        }

        if ($sourceType === 'extra_service' && empty($payload['extra_service_id'])) {
            $errors['general'][] = 'No se identificó el servicio extra.';
        }

        if (empty($payload['acepta'])) {
            $errors['acepta'][] = 'Debes aceptar el tratamiento de datos personales para continuar.';
        }

        return $errors;
    }




    /**
     * Crea o actualiza un lead proveniente del chatbot de WhatsApp.
     * Regla: cuando el usuario acepta tratamiento de datos, entra como
     * Contacto externo / WhatsApp en el módulo de clientes potenciales.
     */
    public function upsertFromWhatsApp(array $payload): array
    {
        $phone = trim((string) ($payload['phone'] ?? $payload['phone_number'] ?? ''));
        $phoneJid = trim((string) ($payload['phone_jid'] ?? $payload['jid'] ?? ''));
        $name = trim((string) ($payload['name'] ?? $payload['notify_name'] ?? $payload['pushname'] ?? ''));
        $botSession = trim((string) ($payload['bot_session'] ?? $payload['session'] ?? ''));
        $accepted = array_key_exists('accepted', $payload) ? (bool) $payload['accepted'] : null;
        $lastMessage = trim((string) ($payload['last_message'] ?? $payload['message'] ?? ''));

        if ($phone === '' && $phoneJid === '') {
            throw new \InvalidArgumentException('El teléfono o JID de WhatsApp es obligatorio.');
        }

        $displayPhone = $phone !== '' ? $phone : $phoneJid;
        $leadName = $name !== '' ? $name : 'Contacto externo / WhatsApp';

        /*
     * WhatsApp normalmente no entrega email.
     * Como la tabla leads exige email NOT NULL, generamos un email técnico único.
     */
        $email = trim((string) ($payload['email'] ?? ''));

        if ($email === '') {
            $emailBase = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '', $displayPhone));
            if ($emailBase === '') {
                $emailBase = 'whatsapp' . substr(sha1($displayPhone . $phoneJid . $botSession), 0, 12);
            }

            $sessionSlug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '', $botSession));
            if ($sessionSlug === '') {
                $sessionSlug = 'general';
            }

            $email = 'whatsapp+' . $emailBase . '+' . $sessionSlug . '@no-email.alestur.local';
        }

        $existingRow = Lead::query()
            ->where('phone', '=', $displayPhone)
            ->where('channel', '=', 'whatsapp')
            ->first();

        $existing = $existingRow ? new Lead($existingRow) : null;

        $metadata = [
            'source' => 'chatbot_whatsapp',
            'bot_session' => $botSession,
            'phone' => $phone,
            'phone_jid' => $phoneJid,
            'accepted_policy' => $accepted,
            'chatbot_user_id' => $payload['chatbot_user_id'] ?? null,
            'chatbot_session_id' => $payload['chatbot_session_id'] ?? null,
            'consent_at' => $payload['consent_at'] ?? date('c'),
            'raw' => $payload,
        ];

        $message = $this->buildWhatsAppNotes(
            $leadName,
            $displayPhone,
            $botSession,
            $accepted,
            $lastMessage
        );

        if ($existing) {
            $oldMetadata = [];

            if (!empty($existing->metadata_json)) {
                $oldMetadata = is_array($existing->metadata_json)
                    ? $existing->metadata_json
                    : (json_decode((string) $existing->metadata_json, true) ?: []);
            }

            $existing->update([
                'full_name' => $existing->full_name ?: $leadName,
                'email' => $existing->email ?: $email,
                'phone' => $displayPhone,
                'source_type' => 'contact',
                'channel' => 'whatsapp',
                'subject' => $existing->subject ?: 'Contacto externo / WhatsApp',
                'message' => $message,
                'whatsapp_opt_in' => $accepted === true ? 1 : (int) ($existing->whatsapp_opt_in ?? 0),
                'consent_accepted_at' => $accepted === true
                    ? ($existing->consent_accepted_at ?: date('Y-m-d H:i:s'))
                    : $existing->consent_accepted_at,
                'last_contact_at' => date('Y-m-d H:i:s'),
                'metadata_json' => json_encode(array_merge($oldMetadata, $metadata), JSON_UNESCAPED_UNICODE),
            ]);

            $lead = Lead::find((int) $existing->id) ?: $existing;
            $created = false;
        } else {
            $lead = Lead::create([
                'customer_id' => null,
                'full_name' => $leadName,
                'email' => $email,
                'phone' => $displayPhone,
                'source_type' => 'contact',
                'channel' => 'whatsapp',
                'subject' => 'Contacto externo / WhatsApp',
                'message' => $message,
                'package_id' => null,
                'package_slug' => null,
                'sales_opportunity_id' => null,
                'status' => 'new',
                'priority' => 'normal',
                'assigned_admin_user_id' => null,
                'whatsapp_opt_in' => $accepted === true ? 1 : 0,
                'consent_accepted_at' => $accepted === true ? date('Y-m-d H:i:s') : null,
                'metadata_json' => json_encode($metadata, JSON_UNESCAPED_UNICODE),
                'last_contact_at' => date('Y-m-d H:i:s'),
                'closed_at' => null,
            ]);

            if (!$lead) {
                throw new \RuntimeException('No fue posible crear el lead de WhatsApp.');
            }

            $created = true;
        }

        $interactionBody = $lastMessage !== ''
            ? $lastMessage
            : ($accepted === true
                ? 'Aceptó política de tratamiento de datos personales por WhatsApp.'
                : 'Registro desde WhatsApp.');

        LeadInteraction::create([
            'lead_id' => (int) $lead->id,
            'admin_user_id' => null,
            'channel' => 'whatsapp',
            'direction' => 'in',
            'event_type' => $accepted === true ? 'policy_accepted' : 'chatbot_event',
            'message' => $interactionBody,
            'meta_json' => json_encode($metadata, JSON_UNESCAPED_UNICODE),
        ]);

        return [
            'created' => $created,
            'lead_id' => (int) $lead->id,
            'lead' => $lead,
        ];
    }

    private function buildWhatsAppNotes(string $name, string $phone, string $botSession, ?bool $accepted, string $lastMessage): string
    {
        $lines = [
            'Origen: Contacto externo / WhatsApp',
            'Nombre detectado: ' . ($name ?: 'No disponible'),
            'Identificador WhatsApp: ' . $phone,
        ];

        if ($botSession !== '') {
            $lines[] = 'Cuenta chatbot: ' . $botSession;
        }

        if ($accepted !== null) {
            $lines[] = 'Política de datos: ' . ($accepted ? 'Aceptó' : 'No aceptó');
        }

        if ($lastMessage !== '') {
            $lines[] = 'Último mensaje: ' . $lastMessage;
        }

        return implode("\n", $lines);
    }
}
