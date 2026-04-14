<?php

namespace app\Controllers\admin\pqrs;

use app\Core\Controller;
use app\Core\Request;
use app\Core\AdminAuth;
use app\Models\PqrsCase;
use app\Models\PqrsAttachment;
use app\Models\PqrsCaseEvent;
use app\Models\PqrsCaseTask;
use app\Models\AdminUser;
use app\Services\Pqrs\PqrsService;
use app\Core\Flash;
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
        $caseId = (int) $request->input('case_id', 0);
        $currentAdminId = AdminAuth::id();

        if ($caseId > 0 && $currentAdminId) {
            $this->service->takeCase($caseId, (int) $currentAdminId);
        }

        redirect('/admin/pqrs/show?id=' . $caseId);
    }

    public function release(Request $request)
    {
        $caseId = (int) $request->input('case_id', 0);
        $currentAdminId = AdminAuth::id();

        if ($caseId > 0 && $currentAdminId) {
            $this->service->releaseCase($caseId, (int) $currentAdminId);
        }

        redirect('/admin/pqrs/show?id=' . $caseId);
    }

    public function updateStatus(Request $request)
    {
        $caseId = (int) $request->input('case_id', 0);
        $status = trim((string) $request->input('status', 'new'));

        $this->service->updateStatus($caseId, $status, AdminAuth::id());
        redirect('/admin/pqrs/show?id=' . $caseId);
    }

    public function note(Request $request)
    {
        $caseId = (int) $request->input('case_id', 0);
        $message = (string) $request->input('message', '');

        $this->service->addNote($caseId, $message, AdminAuth::id());
        redirect('/admin/pqrs/show?id=' . $caseId);
    }

    public function task(Request $request)
    {
        $caseId = (int) $request->input('case_id', 0);
        $title = (string) $request->input('title', '');
        $description = (string) $request->input('description', '');
        $dueAt = (string) $request->input('due_at', '');

        $this->service->createTask($caseId, $title, $description, $dueAt !== '' ? $dueAt : null, AdminAuth::id());
        redirect('/admin/pqrs/show?id=' . $caseId);
    }

    public function completeTask(Request $request)
    {
        $taskId = (int) $request->input('task_id', 0);
        $caseId = (int) $request->input('case_id', 0);

        $this->service->completeTask($taskId, AdminAuth::id());
        redirect('/admin/pqrs/show?id=' . $caseId);
    }

    public function attachment(Request $request)
{
    $id = (int) $request->input('id', 0);
    $attachment = \app\Models\PqrsAttachment::find($id);

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

    $mimeType = (string) ($attachment->mime_type ?? 'application/octet-stream');
    $downloadName = (string) ($attachment->original_name ?? basename($fullPath));
    $size = filesize($fullPath);

    if (!headers_sent()) {
        header('Content-Description: File Transfer');
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: inline; filename="' . rawurlencode($downloadName) . '"');
        header('Content-Length: ' . ($size !== false ? $size : 0));
        header('X-Content-Type-Options: nosniff');
    }

    readfile($fullPath);
    exit;
}
}