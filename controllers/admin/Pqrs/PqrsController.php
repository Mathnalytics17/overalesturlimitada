<?php

namespace app\Controllers\admin\pqrs;

use app\Core\Controller;
use app\Core\Request;
use app\Core\AdminAuth;
use app\Core\Csrf;
use app\Core\Flash;
use app\Models\PqrsCase;
use app\Models\PqrsAttachment;
use app\Models\PqrsCaseEvent;
use app\Models\PqrsCaseTask;
use app\Models\AdminUser;
use app\Services\Pqrs\PqrsService;

class PqrsController extends Controller
{
    protected PqrsService $service;

    public function __construct()
    {
        $this->service = new PqrsService();
    }

    public function index(Request $request)
    {
        $filters = [
            'status' => trim((string) $request->input('status', '')),
            'request_type' => trim((string) $request->input('request_type', '')),
            'q' => trim((string) $request->input('q', '')),
        ];

        $cases = PqrsCase::filter($filters, 200);

        return $this->render('admin/pqrs/index', [
            'cases' => $cases,
            'filters' => $filters,
        ], 'adminUserLayout');
    }

    public function show(Request $request)
    {
        $id = (int) $request->input('id', 0);
        $case = PqrsCase::find($id);

        if (!$case) {
            http_response_code(404);
            return $this->render('_404', [], 'adminUserLayout');
        }

        $assignedAdvisor = null;

        if (!empty($case->assigned_admin_user_id)) {
            $assignedAdvisor = AdminUser::find((int) $case->assigned_admin_user_id);
        }

        return $this->render('admin/pqrs/show', [
            'case' => $case,
            'attachments' => PqrsAttachment::byCase($id),
            'events' => PqrsCaseEvent::byCase($id),
            'tasks' => PqrsCaseTask::byCase($id),
            'assignedAdvisor' => $assignedAdvisor,
            'currentAdminId' => AdminAuth::id(),
        ], 'adminUserLayout');
    }

    public function take(Request $request)
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $caseId = (int) $request->input('case_id', 0);
        $currentAdminId = (int) (AdminAuth::id() ?? 0);

        if ($caseId <= 0 || $currentAdminId <= 0) {
            Flash::error('No fue posible tomar el caso PQRS.');
            redirect('/admin/pqrs');
            exit;
        }

        $ok = $this->service->takeCase($caseId, $currentAdminId);

        if ($ok) {
            Flash::success('Caso PQRS tomado correctamente.');
        } else {
            Flash::error('No fue posible tomar el caso PQRS. Puede que ya esté asignado a otro asesor.');
        }

        redirect('/admin/pqrs/show?id=' . $caseId);
        exit;
    }

    public function release(Request $request)
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $caseId = (int) $request->input('case_id', 0);
        $currentAdminId = (int) (AdminAuth::id() ?? 0);

        if ($caseId <= 0 || $currentAdminId <= 0) {
            Flash::error('No fue posible liberar el caso PQRS.');
            redirect('/admin/pqrs');
            exit;
        }

        $ok = $this->service->releaseCase($caseId, $currentAdminId);

        if ($ok) {
            Flash::success('Caso PQRS liberado correctamente.');
        } else {
            Flash::error('No fue posible liberar el caso PQRS.');
        }

        redirect('/admin/pqrs/show?id=' . $caseId);
        exit;
    }

    public function updateStatus(Request $request)
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $caseId = (int) $request->input('case_id', 0);
        $status = trim((string) $request->input('status', 'new'));

        if ($caseId <= 0) {
            Flash::error('Caso inválido.');
            redirect('/admin/pqrs');
            exit;
        }

        $ok = $this->service->updateStatus($caseId, $status, AdminAuth::id());

        if ($ok) {
            Flash::success('Estado del caso actualizado correctamente.');
        } else {
            Flash::error('No fue posible actualizar el estado del caso.');
        }

        redirect('/admin/pqrs/show?id=' . $caseId);
        exit;
    }

    public function note(Request $request)
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $caseId = (int) $request->input('case_id', 0);
        $message = trim((string) $request->input('message', ''));

        if ($caseId <= 0) {
            Flash::error('Caso inválido.');
            redirect('/admin/pqrs');
            exit;
        }

        if ($message === '') {
            Flash::error('Debes escribir una nota antes de guardar.');
            redirect('/admin/pqrs/show?id=' . $caseId);
            exit;
        }

        $ok = $this->service->addNote($caseId, $message, AdminAuth::id());

        if ($ok) {
            Flash::success('Nota agregada correctamente.');
        } else {
            Flash::error('No fue posible guardar la nota.');
        }

        redirect('/admin/pqrs/show?id=' . $caseId);
        exit;
    }

    public function task(Request $request)
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $caseId = (int) $request->input('case_id', 0);
        $title = trim((string) $request->input('title', ''));
        $description = trim((string) $request->input('description', ''));
        $dueAt = trim((string) $request->input('due_at', ''));

        if ($caseId <= 0) {
            Flash::error('Caso inválido.');
            redirect('/admin/pqrs');
            exit;
        }

        if ($title === '') {
            Flash::error('Debes ingresar al menos el título de la tarea.');
            redirect('/admin/pqrs/show?id=' . $caseId);
            exit;
        }

        $task = $this->service->createTask(
            $caseId,
            $title,
            $description,
            $dueAt !== '' ? $dueAt : null,
            AdminAuth::id()
        );

        if ($task) {
            Flash::success('Tarea creada correctamente.');
        } else {
            Flash::error('No fue posible crear la tarea.');
        }

        redirect('/admin/pqrs/show?id=' . $caseId);
        exit;
    }

    public function completeTask(Request $request)
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $taskId = (int) $request->input('task_id', 0);
        $caseId = (int) $request->input('case_id', 0);

        if ($taskId <= 0 || $caseId <= 0) {
            Flash::error('No fue posible completar la tarea.');
            redirect('/admin/pqrs');
            exit;
        }

        $ok = $this->service->completeTask($taskId, AdminAuth::id());

        if ($ok) {
            Flash::success('Tarea marcada como completada.');
        } else {
            Flash::error('No fue posible completar la tarea.');
        }

        redirect('/admin/pqrs/show?id=' . $caseId);
        exit;
    }

    public function attachment(Request $request)
    {
        $id = (int) $request->input('id', 0);
        $attachment = PqrsAttachment::find($id);

        if (!$attachment) {
            http_response_code(404);
            echo 'Archivo no encontrado.';
            return;
        }

        $fullPath = base_path((string) ($attachment->file_path ?? ''));

        if (!is_file($fullPath) || !is_readable($fullPath)) {
            http_response_code(404);
            echo 'El archivo no existe en el servidor.';
            return;
        }

        $mimeType = $this->detectDownloadMimeType($fullPath, (string) ($attachment->mime_type ?? 'application/octet-stream'));
        $downloadName = (string) ($attachment->original_name ?? basename($fullPath));
        $size = filesize($fullPath);

        if (!headers_sent()) {
            header('Content-Description: File Transfer');
            header('Content-Type: ' . $mimeType);
            header('Content-Disposition: attachment; filename="' . rawurlencode($downloadName) . '"');
            header('Content-Length: ' . ($size !== false ? $size : 0));
            header('X-Content-Type-Options: nosniff');
            header('Cache-Control: private, no-store, max-age=0');
        }

        security_event('pqrs_attachment_downloaded', [
            'attachment_id' => (int) $attachment->id,
            'case_id' => (int) ($attachment->pqrs_case_id ?? 0),
            'admin_user_id' => (int) (AdminAuth::id() ?? 0),
            'file_name' => $downloadName,
        ]);

        readfile($fullPath);
        exit;
    }

    protected function detectDownloadMimeType(string $fullPath, string $fallbackMimeType): string
    {
        if (class_exists(\finfo::class)) {
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $detected = $finfo->file($fullPath);
            if (is_string($detected) && $detected !== '') {
                return $detected;
            }
        }

        $detected = mime_content_type($fullPath);
        if (is_string($detected) && $detected !== '') {
            return $detected;
        }

        return $fallbackMimeType !== '' ? $fallbackMimeType : 'application/octet-stream';
    }
}
