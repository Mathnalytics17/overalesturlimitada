<?php

namespace app\Controllers\admin\leads;

use app\Core\Controller;
use app\Core\Request;
use app\Core\AdminAuth;
use app\Models\AdminUser;
use app\Models\Lead;
use app\Models\LeadInteraction;
use app\Models\LeadTask;
use app\Services\Crm\LeadService;
use app\Core\Flash;

class LeadController extends Controller
{
    protected LeadService $service;

    public function __construct()
    {
        $this->service = new LeadService();
    }

    public function index(Request $request)
    {
        $filters = [
            'status' => trim((string) $request->input('status', '')),
            'source_type' => trim((string) $request->input('source_type', '')),
            'q' => trim((string) $request->input('q', '')),
        ];

        $pagination = Lead::paginate(
            $filters,
            (int) $request->input('page', 1),
            (int) $request->input('per_page', 25)
        );
        $leads = $pagination['items'];
        $admins = AdminUser::all();

        $adminMap = [];
        foreach ($admins as $admin) {
            $adminMap[(int) $admin->id] = $admin;
        }

        foreach ($leads as $lead) {
            $lead->sales_opportunity = null;

            if (!empty($lead->sales_opportunity_id)) {
                $lead->sales_opportunity = \app\Models\SalesOpportunity::find((int) $lead->sales_opportunity_id);
            }
        }

        return $this->render('admin/leads/index', [
            'leads' => $leads,
            'filters' => $filters,
            'pagination' => $pagination,
            'admins' => $admins,
            'adminMap' => $adminMap,
            'counts' => Lead::countsByStatus(),
            'currentAdminId' => AdminAuth::id(),
        ], 'adminUserLayout');
    }

    public function show(Request $request)
    {
        $id = (int) $request->input('id', 0);
        $lead = Lead::find($id);

        if (!$lead) {
            http_response_code(404);
            return $this->render('_404_admin', ['page_title' => 'Página no encontrada', 'page_subtitle' => 'El registro solicitado no existe.'], 'adminUserLayout');
        }

        $admins = AdminUser::all();
        $assignedAdvisor = null;

        foreach ($admins as $admin) {
            if ((int) $admin->id === (int) ($lead->assigned_admin_user_id ?? 0)) {
                $assignedAdvisor = $admin;
                break;
            }
        }

        $currentAdminId = AdminAuth::id();
        $returnTo = trim((string) $request->input('return_to', '/admin/leads'));

        if ($returnTo === '') {
            $returnTo = '/admin/leads';
        }

        return $this->render('admin/leads/show', [
            'lead' => $lead,
            'interactions' => LeadInteraction::byLead($id),
            'tasks' => LeadTask::byLead($id),
            'assignedAdvisor' => $assignedAdvisor,
            'currentAdminId' => $currentAdminId,
            'publicWhatsappUrl' => $this->service->buildWhatsAppUrl($lead),
            'advisorWhatsappUrl' => $this->service->buildAdvisorWhatsAppUrl($lead),
            'returnTo' => $returnTo,
            'focusNote' => (int) $request->input('focus_note', 0) === 1,
        ], 'adminUserLayout');
    }

    public function updateStatus(Request $request)
    {
        $leadId = (int) $request->input('lead_id', 0);
        $status = trim((string) $request->input('status', 'new'));
        $returnTo = trim((string) $request->input('return_to', '/admin/leads'));

        $this->service->updateStatus($leadId, $status, AdminAuth::id());

        redirect('/admin/leads/show?id=' . $leadId . '&return_to=' . urlencode($returnTo));
    }

   public function take(Request $request)
{
    $leadId = (int) $request->input('lead_id', 0);
    $currentAdminId = AdminAuth::id();
    $returnTo = trim((string) $request->input('return_to', '/admin/leads'));
    $action = trim((string) $request->input('action', 'stay'));

    if ($leadId > 0 && $currentAdminId) {
        $this->service->takeLead($leadId, (int) $currentAdminId);
        Flash::success('Lead tomado correctamente.');
    } else {
        Flash::error('No fue posible tomar el lead.');
    }

    if ($action === 'back') {
        redirect($returnTo !== '' ? $returnTo : '/admin/leads');
        exit;
    }

    redirect('/admin/leads/show?id=' . $leadId . '&return_to=' . urlencode($returnTo) . '&focus_note=1');
    exit;
}

   public function release(Request $request)
{
    $leadId = (int) $request->input('lead_id', 0);
    $currentAdminId = AdminAuth::id();
    $returnTo = trim((string) $request->input('return_to', '/admin/leads'));
    $action = trim((string) $request->input('action', 'back'));

    if ($leadId > 0 && $currentAdminId) {
        $this->service->releaseLead($leadId, (int) $currentAdminId);
        Flash::success('Lead liberado correctamente.');
    } else {
        Flash::error('No fue posible liberar el lead.');
    }

    if ($action === 'stay') {
        redirect('/admin/leads/show?id=' . $leadId . '&return_to=' . urlencode($returnTo));
        exit;
    }

    redirect($returnTo !== '' ? $returnTo : '/admin/leads');
    exit;
}

    public function note(Request $request)
    {
        $leadId = (int) $request->input('lead_id', 0);
        $message = (string) $request->input('message', '');
        $returnTo = trim((string) $request->input('return_to', '/admin/leads'));

        $this->service->addNote($leadId, $message, AdminAuth::id());

        redirect('/admin/leads/show?id=' . $leadId . '&return_to=' . urlencode($returnTo));
    }

    public function task(Request $request)
    {
        $leadId = (int) $request->input('lead_id', 0);
        $title = (string) $request->input('title', '');
        $description = (string) $request->input('description', '');
        $dueAt = (string) $request->input('due_at', '');
        $returnTo = trim((string) $request->input('return_to', '/admin/leads'));

        $this->service->createTask(
            $leadId,
            $title,
            $description,
            $dueAt !== '' ? $dueAt : null,
            AdminAuth::id()
        );

        redirect('/admin/leads/show?id=' . $leadId . '&return_to=' . urlencode($returnTo));
    }

    public function completeTask(Request $request)
    {
        $taskId = (int) $request->input('task_id', 0);
        $leadId = (int) $request->input('lead_id', 0);
        $returnTo = trim((string) $request->input('return_to', '/admin/leads'));

        $this->service->completeTask($taskId, AdminAuth::id());

        redirect('/admin/leads/show?id=' . $leadId . '&return_to=' . urlencode($returnTo));
    }
}
