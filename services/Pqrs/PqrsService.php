<?php

namespace app\Services\Pqrs;

use app\Models\PqrsCase;
use app\Models\PqrsAttachment;
use app\Models\PqrsCaseEvent;
use app\Models\PqrsCaseTask;

class PqrsService
{
    protected const MAX_ATTACHMENTS = 5;
    protected const MAX_ATTACHMENT_SIZE = 10 * 1024 * 1024;

    public function createFromForm(array $payload, array $files = []): array
    {
        $normalized = $this->normalizePayload($payload);
        $errors = $this->validate($normalized, $payload);
        $attachmentErrors = $this->validateAttachments($files);

        if ($attachmentErrors !== []) {
            $errors = array_merge($errors, $attachmentErrors);
        }

        if ($errors !== []) {
            return [
                'success' => false,
                'errors' => $errors,
                'old' => $payload,
            ];
        }

        $radicado = $this->generateRadicado();

        $case = PqrsCase::create([
            'radicado' => $radicado,
            'full_name' => $normalized['full_name'],
            'email' => $normalized['email'],
            'phone' => $normalized['phone'],
            'request_type' => $normalized['request_type'],
            'subject' => $normalized['subject'],
            'message' => $normalized['message'],
            'status' => 'new',
            'priority' => $this->resolvePriority($normalized['request_type']),
            'assigned_admin_user_id' => null,
            'has_attachments' => !empty($files['attachments']['name'][0]) ? 1 : 0,
            'consent_accepted_at' => !empty($payload['acepta']) ? now() : null,
            'last_contact_at' => now(),
        ]);

        if (!$case) {
            return [
                'success' => false,
                'errors' => ['No fue posible registrar la PQRS.'],
                'old' => $payload,
            ];
        }

        $this->logEvent((int) $case->id, null, 'case_created', 'PQRS creada desde formulario web.');

        $uploaded = $this->storeAttachments((int) $case->id, $files);

        if ($uploaded > 0) {
            $this->logEvent((int) $case->id, null, 'attachment_uploaded', 'Se cargaron ' . $uploaded . ' archivo(s).');
        }

        return [
            'success' => true,
            'case' => $case,
        ];
    }

    public function updateStatus(int $caseId, string $status, ?int $adminUserId = null): bool
    {
        $case = PqrsCase::find($caseId);
        if (!$case) {
            return false;
        }

        $allowed = ['new', 'in_progress', 'waiting_customer', 'resolved', 'closed'];
        if (!in_array($status, $allowed, true)) {
            return false;
        }

        $oldStatus = (string) $case->status;

        $data = [
            'status' => $status,
            'last_contact_at' => now(),
        ];

        if ($status === 'resolved') {
            $data['resolved_at'] = now();
        }

        if ($status === 'closed') {
            $data['closed_at'] = now();
        }

        if ($status === 'in_progress' && empty($case->first_response_at)) {
            $data['first_response_at'] = now();
        }

        $ok = $case->update($data);

        if ($ok) {
            $this->logEvent(
                $caseId,
                $adminUserId,
                'status_changed',
                "Estado cambiado de {$oldStatus} a {$status}."
            );
        }

        return $ok;
    }

    public function takeCase(int $caseId, int $adminUserId): bool
    {
        $case = PqrsCase::find($caseId);
        if (!$case) {
            return false;
        }

        if (!empty($case->assigned_admin_user_id) && (int) $case->assigned_admin_user_id !== $adminUserId) {
            return false;
        }

        $data = [
            'assigned_admin_user_id' => $adminUserId,
            'status' => $case->status === 'new' ? 'in_progress' : $case->status,
            'last_contact_at' => now(),
        ];

        if (empty($case->first_response_at)) {
            $data['first_response_at'] = now();
        }

        $ok = $case->update($data);

        if ($ok) {
            $this->logEvent(
                $caseId,
                $adminUserId,
                'taken_by_agent',
                'Caso tomado por el asesor #' . $adminUserId . '.'
            );
        }

        return $ok;
    }

    public function releaseCase(int $caseId, int $adminUserId): bool
    {
        $case = PqrsCase::find($caseId);
        if (!$case) {
            return false;
        }

        if ((int) ($case->assigned_admin_user_id ?? 0) !== $adminUserId) {
            return false;
        }

        $newStatus = in_array((string) ($case->status ?? ''), ['resolved', 'closed'], true)
            ? (string) $case->status
            : 'new';

        $ok = $case->update([
            'assigned_admin_user_id' => null,
            'status' => $newStatus,
            'last_contact_at' => now(),
        ]);

        if ($ok) {
            $this->logEvent(
                $caseId,
                $adminUserId,
                'released_by_agent',
                'Caso liberado por el asesor #' . $adminUserId . '.'
            );
        }

        return $ok;
    }

    public function addNote(int $caseId, string $message, ?int $adminUserId = null): bool
    {
        if (trim($message) === '') {
            return false;
        }

        $event = PqrsCaseEvent::create([
            'pqrs_case_id' => $caseId,
            'admin_user_id' => $adminUserId,
            'event_type' => 'note_added',
            'message' => trim($message),
            'meta_json' => null,
        ]);

        if ($event) {
            $case = PqrsCase::find($caseId);
            if ($case) {
                $case->update(['last_contact_at' => now()]);
            }
        }

        return $event !== null;
    }

    public function createTask(int $caseId, string $title, string $description = '', ?string $dueAt = null, ?int $adminUserId = null): ?PqrsCaseTask
    {
        if (trim($title) === '') {
            return null;
        }

        $task = PqrsCaseTask::create([
            'pqrs_case_id' => $caseId,
            'admin_user_id' => $adminUserId,
            'title' => trim($title),
            'description' => trim($description),
            'status' => 'pending',
            'due_at' => $dueAt ?: null,
            'completed_at' => null,
        ]);

        if ($task) {
            $this->logEvent(
                $caseId,
                $adminUserId,
                'task_created',
                'Se creó tarea: ' . $task->title,
                [
                    'task_id' => $task->id,
                    'due_at' => $task->due_at,
                ]
            );
        }

        return $task;
    }

    public function completeTask(int $taskId, ?int $adminUserId = null): bool
    {
        $task = PqrsCaseTask::find($taskId);
        if (!$task) {
            return false;
        }

        $ok = $task->update([
            'status' => 'done',
            'completed_at' => now(),
        ]);

        if ($ok) {
            $this->logEvent(
                (int) $task->pqrs_case_id,
                $adminUserId,
                'task_completed',
                'Se completó la tarea: ' . $task->title,
                [
                    'task_id' => $task->id,
                ]
            );
        }

        return $ok;
    }

    protected function normalizePayload(array $payload): array
    {
        return [
            'full_name' => trim((string) ($payload['name'] ?? '')),
            'email' => trim((string) ($payload['email'] ?? '')),
            'phone' => normalize_phone((string) ($payload['telefono'] ?? '')),
            'request_type' => trim((string) ($payload['type'] ?? '')),
            'subject' => trim((string) ($payload['subject'] ?? '')),
            'message' => trim((string) ($payload['message'] ?? '')),
        ];
    }

    protected function validate(array $data, array $payload): array
    {
        $errors = [];

        if ($data['full_name'] === '') {
            $errors[] = 'El nombre completo es obligatorio.';
        }

        if ($data['email'] === '' || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Debes ingresar un correo válido.';
        }

        if ($data['phone'] === '') {
            $errors[] = 'Debes ingresar un teléfono válido.';
        }

        if (!in_array($data['request_type'], ['peticion', 'queja', 'reclamo', 'sugerencia'], true)) {
            $errors[] = 'Debes seleccionar un tipo de solicitud válido.';
        }

        if ($data['subject'] === '') {
            $errors[] = 'El asunto es obligatorio.';
        }

        if ($data['message'] === '') {
            $errors[] = 'La descripción es obligatoria.';
        }

        if (empty($payload['acepta'])) {
            $errors[] = 'Debes aceptar el tratamiento de datos.';
        }

        return $errors;
    }

    protected function validateAttachments(array $files): array
    {
        if (empty($files['attachments']) || !is_array($files['attachments']['name'] ?? null)) {
            return [];
        }

        $errors = [];
        $names = $files['attachments']['name'] ?? [];
        $tmpNames = $files['attachments']['tmp_name'] ?? [];
        $sizes = $files['attachments']['size'] ?? [];
        $uploadErrors = $files['attachments']['error'] ?? [];

        $providedFiles = 0;

        foreach ($names as $index => $originalName) {
            if (($uploadErrors[$index] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            $providedFiles++;
            $validation = $this->validateAttachmentFile([
                'name' => $originalName,
                'tmp_name' => $tmpNames[$index] ?? null,
                'size' => $sizes[$index] ?? 0,
                'error' => $uploadErrors[$index] ?? UPLOAD_ERR_NO_FILE,
            ]);

            if ($validation['success']) {
                continue;
            }

            $errors = array_merge($errors, $validation['errors']);
        }

        if ($providedFiles > self::MAX_ATTACHMENTS) {
            $errors[] = 'Solo se permiten hasta ' . self::MAX_ATTACHMENTS . ' adjuntos por solicitud.';
        }

        return $errors;
    }

    protected function generateRadicado(): string
    {
        return 'PQRS-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
    }

    protected function resolvePriority(string $type): string
    {
        return match ($type) {
            'queja', 'reclamo' => 'high',
            'peticion' => 'medium',
            'sugerencia' => 'low',
            default => 'medium',
        };
    }

    protected function logEvent(int $caseId, ?int $adminUserId, string $eventType, string $message, ?array $meta = null): void
    {
        PqrsCaseEvent::create([
            'pqrs_case_id' => $caseId,
            'admin_user_id' => $adminUserId,
            'event_type' => $eventType,
            'message' => $message,
            'meta_json' => !empty($meta) ? json_encode($meta, JSON_UNESCAPED_UNICODE) : null,
        ]);
    }

    protected function storeAttachments(int $caseId, array $files): int
    {
        if (empty($files['attachments'])) {
            return 0;
        }

        $count = 0;
        $uploadDir = base_path('runtime/uploads/pqrs/' . date('Y/m'));

        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        $names = $files['attachments']['name'] ?? [];
        $tmpNames = $files['attachments']['tmp_name'] ?? [];
        $sizes = $files['attachments']['size'] ?? [];
        $errors = $files['attachments']['error'] ?? [];

        foreach ($names as $i => $originalName) {
            $file = [
                'name' => $originalName,
                'tmp_name' => $tmpNames[$i] ?? null,
                'size' => $sizes[$i] ?? 0,
                'error' => $errors[$i] ?? UPLOAD_ERR_NO_FILE,
            ];

            if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                continue;
            }

            $validation = $this->validateAttachmentFile($file);
            if (!$validation['success']) {
                continue;
            }

            $tmp = $file['tmp_name'] ?? null;
            if (!$tmp || !is_uploaded_file($tmp)) {
                continue;
            }

            $extension = $validation['extension'];
            $mimeType = $validation['mime_type'];

            $storedName = 'pqrs_' . $caseId . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
            $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $storedName;

            if (!move_uploaded_file($tmp, $targetPath)) {
                continue;
            }

            PqrsAttachment::create([
                'pqrs_case_id' => $caseId,
                'original_name' => (string) $originalName,
                'stored_name' => $storedName,
                'file_path' => 'runtime/uploads/pqrs/' . date('Y/m') . '/' . $storedName,
                'mime_type' => $mimeType,
                'file_size' => (int) ($file['size'] ?? 0),
                'created_at' => now(),
            ]);

            $count++;
        }

        return $count;
    }

    protected function validateAttachmentFile(array $file): array
    {
        $errorCode = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($errorCode === UPLOAD_ERR_NO_FILE) {
            return [
                'success' => true,
                'errors' => [],
            ];
        }

        if ($errorCode !== UPLOAD_ERR_OK) {
            return [
                'success' => false,
                'errors' => ['Uno de los adjuntos no pudo cargarse correctamente.'],
            ];
        }

        $tmpName = (string) ($file['tmp_name'] ?? '');
        if ($tmpName === '' || !is_file($tmpName)) {
            return [
                'success' => false,
                'errors' => ['Uno de los adjuntos no es valido.'],
            ];
        }

        $size = (int) ($file['size'] ?? 0);
        if ($size <= 0) {
            return [
                'success' => false,
                'errors' => ['Uno de los adjuntos esta vacio o no es valido.'],
            ];
        }

        if ($size > self::MAX_ATTACHMENT_SIZE) {
            return [
                'success' => false,
                'errors' => ['Cada adjunto debe pesar maximo 10MB.'],
            ];
        }

        $originalName = (string) ($file['name'] ?? '');
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        $allowedByExtension = [
            'pdf' => ['application/pdf'],
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
            'doc' => ['application/msword', 'application/octet-stream'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'],
        ];

        if (!isset($allowedByExtension[$extension])) {
            return [
                'success' => false,
                'errors' => ['Solo se permiten adjuntos PDF, JPG, JPEG, PNG, DOC o DOCX.'],
            ];
        }

        $mimeType = $this->detectMimeType($tmpName);
        if ($mimeType === null || !in_array($mimeType, $allowedByExtension[$extension], true)) {
            return [
                'success' => false,
                'errors' => ['Uno de los adjuntos no coincide con el tipo de archivo permitido.'],
            ];
        }

        if (str_starts_with($mimeType, 'image/')) {
            $imageInfo = @getimagesize($tmpName);
            if ($imageInfo === false) {
                return [
                    'success' => false,
                    'errors' => ['Uno de los adjuntos de imagen no es valido.'],
                ];
            }
        }

        return [
            'success' => true,
            'errors' => [],
            'extension' => $extension,
            'mime_type' => $mimeType,
        ];
    }

    protected function detectMimeType(string $path): ?string
    {
        if (!is_file($path)) {
            return null;
        }

        if (class_exists(\finfo::class)) {
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($path);
            if (is_string($mimeType) && $mimeType !== '') {
                return $mimeType;
            }
        }

        $mimeType = mime_content_type($path);
        return is_string($mimeType) && $mimeType !== '' ? $mimeType : null;
    }
}
