<?php

namespace app\Controllers\admin\sales;

use app\Core\AdminAuth;
use app\Core\Controller;
use app\Core\Csrf;
use app\Models\SalesOpportunity;
use app\Models\SalesOpportunityEvent;
use app\Services\Admin\Sales\SalesOpportunityService;
use app\Models\AdminUser;

class SalesOpportunityController extends Controller
{
    protected SalesOpportunityService $service;

    public function __construct()
    {
        $this->service = new SalesOpportunityService();
    }

    protected function safeReturnTo(?string $returnTo, string $fallback): string
    {
        $returnTo = trim((string) $returnTo);

        if ($returnTo === '') {
            return $fallback;
        }

        if (str_starts_with($returnTo, '/admin/')) {
            return $returnTo;
        }

        return $fallback;
    }

    public function index()
    {
        $search = trim($_GET['q'] ?? '');
        $stage = trim($_GET['stage'] ?? '');
        $assignedAdminId = (int) ($_GET['assigned_admin_id'] ?? 0);

        $items = SalesOpportunity::adminList(
            $search !== '' ? $search : null,
            $stage !== '' ? $stage : null,
            $assignedAdminId > 0 ? $assignedAdminId : null
        );

        $admins = AdminUser::query()->get();
        $admins = array_map(fn($row) => new AdminUser($row), $admins ?: []);

        return $this->render('admin/sales/index', [
            'items' => $items,
            'search' => $search,
            'stage' => $stage,
            'assignedAdminId' => $assignedAdminId,
            'admins' => $admins,
        ], 'adminUserLayout');
    }

    public function create()
    {
        return $this->render('admin/sales/create', [
            'errors' => [],
            'message' => null,
            'old' => [],
        ], 'adminUserLayout');
    }

    public function store()
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $adminId = (int) (AdminAuth::id() ?? 0);

        $result = $this->service->createManual(
            $_POST,
            $adminId > 0 ? $adminId : null
        );

        if (!empty($result['success']) && !empty($result['opportunity'])) {
            \redirect('/admin/sales/show?id=' . (int) $result['opportunity']->id);
        }

        return $this->render('admin/sales/create', [
            'errors' => $result['errors'] ?? [],
            'message' => $result['message'] ?? null,
            'old' => $result['old'] ?? $_POST,
        ], 'adminUserLayout');
    }

    public function show()
    {
        $id = (int) ($_GET['id'] ?? 0);
        $item = SalesOpportunity::find($id);

        if (!$item) {
            \redirect('/admin/sales');
        }

        $events = SalesOpportunityEvent::byOpportunity((int) $item->id);

        $returnTo = trim((string) ($_GET['return_to'] ?? '/admin/sales'));
        $returnTo = $this->safeReturnTo($returnTo, '/admin/sales');
        $focusNote = (int) ($_GET['focus_note'] ?? 0) === 1;

        $currentAdminId = (int) (AdminAuth::id() ?? 0);
        $assignedAdvisor = null;

        if (!empty($item->assigned_admin_user_id)) {
            $assignedAdvisor = AdminUser::find((int) $item->assigned_admin_user_id);
        }

        return $this->render('admin/sales/show', [
            'item' => $item,
            'events' => $events,
            'returnTo' => $returnTo,
            'focusNote' => $focusNote,
            'currentAdminId' => $currentAdminId,
            'assignedAdvisor' => $assignedAdvisor,
        ], 'adminUserLayout');
    }

    public function createFromLead()
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $leadId = (int) ($_POST['lead_id'] ?? 0);
        $returnTo = trim((string) ($_POST['return_to'] ?? '/admin/leads'));
        $adminId = (int) (AdminAuth::id() ?? 0);

        $result = $this->service->createFromLead($leadId, $adminId > 0 ? $adminId : null);

        if (!empty($result['opportunity'])) {
            \redirect('/admin/sales/show?id=' . (int) $result['opportunity']->id . '&return_to=' . urlencode($this->safeReturnTo($returnTo, '/admin/leads')));
        }

        \redirect($this->safeReturnTo($returnTo, '/admin/leads'));
    }

    public function assign()
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $returnTo = trim((string) ($_POST['return_to'] ?? ''));
        $action = trim((string) ($_POST['action'] ?? 'stay'));
        $adminId = (int) (AdminAuth::id() ?? 0);

        if ($id > 0 && $adminId > 0) {
            $this->service->assign($id, $adminId);
        }

        $safeReturnTo = $this->safeReturnTo($returnTo, '/admin/sales');
        $showUrl = '/admin/sales/show?id=' . $id . '&return_to=' . urlencode($safeReturnTo);

        if ($action === 'back') {
            \redirect($safeReturnTo);
        }

        \redirect($showUrl . '&focus_note=1');
    }

    public function changeStage()
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $stage = trim((string) ($_POST['sales_stage'] ?? ''));
        $message = trim((string) ($_POST['message'] ?? ''));
        $returnTo = trim((string) ($_POST['return_to'] ?? ''));
        $adminId = (int) (AdminAuth::id() ?? 0);

        $this->service->changeStage(
            $id,
            $stage,
            $adminId > 0 ? $adminId : null,
            $message !== '' ? $message : null
        );

        $fallback = '/admin/sales/show?id=' . $id . '&return_to=' . urlencode($this->safeReturnTo($returnTo, '/admin/sales'));
        \redirect($fallback);
    }

    public function scheduleFollowUp()
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $datetime = trim((string) ($_POST['next_follow_up_at'] ?? ''));
        $message = trim((string) ($_POST['message'] ?? ''));
        $returnTo = trim((string) ($_POST['return_to'] ?? ''));
        $adminId = (int) (AdminAuth::id() ?? 0);

        if ($datetime !== '') {
            $this->service->scheduleFollowUp($id, $datetime, $adminId > 0 ? $adminId : null, $message);
        }

        $fallback = '/admin/sales/show?id=' . $id . '&return_to=' . urlencode($this->safeReturnTo($returnTo, '/admin/sales'));
        \redirect($fallback);
    }

    public function addNote()
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $message = trim((string) ($_POST['message'] ?? ''));
        $returnTo = trim((string) ($_POST['return_to'] ?? ''));
        $adminId = (int) (AdminAuth::id() ?? 0);

        if ($message !== '') {
            $this->service->addNote($id, $message, $adminId > 0 ? $adminId : null);
        }

        $fallback = '/admin/sales/show?id=' . $id . '&return_to=' . urlencode($this->safeReturnTo($returnTo, '/admin/sales'));
        \redirect($fallback);
    }

    public function markWon()
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $returnTo = trim((string) ($_POST['return_to'] ?? ''));
        $adminId = (int) (AdminAuth::id() ?? 0);

        $this->service->markWon($id, $adminId > 0 ? $adminId : null);

        $fallback = '/admin/sales/show?id=' . $id . '&return_to=' . urlencode($this->safeReturnTo($returnTo, '/admin/sales'));
        \redirect($fallback);
    }

    public function markLost()
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $reasonCode = trim((string) ($_POST['lost_reason_code'] ?? ''));
        $reasonDetail = trim((string) ($_POST['lost_reason_detail'] ?? ''));
        $returnTo = trim((string) ($_POST['return_to'] ?? ''));
        $adminId = (int) (AdminAuth::id() ?? 0);

        if ($reasonCode !== '') {
            $this->service->markLost(
                $id,
                $reasonCode,
                $reasonDetail !== '' ? $reasonDetail : null,
                $adminId > 0 ? $adminId : null
            );
        }

        $fallback = '/admin/sales/show?id=' . $id . '&return_to=' . urlencode($this->safeReturnTo($returnTo, '/admin/sales'));
        \redirect($fallback);
    }

    public function kanban()
    {
        $search = trim($_GET['q'] ?? '');
        $assignedAdminId = (int) ($_GET['assigned_admin_id'] ?? 0);

        $groups = \app\Models\SalesOpportunity::groupedByStage(
            $search !== '' ? $search : null,
            $assignedAdminId > 0 ? $assignedAdminId : null
        );

        $admins = AdminUser::query()->get();
        $admins = array_map(fn($row) => new AdminUser($row), $admins ?: []);

        return $this->render('admin/sales/kanban', [
            'groups' => $groups,
            'search' => $search,
            'assignedAdminId' => $assignedAdminId,
            'admins' => $admins,
        ], 'adminUserLayout');
    }

    public function edit()
    {
        $id = (int) ($_GET['id'] ?? 0);
        $item = SalesOpportunity::find($id);
        $returnTo = trim((string) ($_GET['return_to'] ?? '/admin/sales'));

        if (!$item) {
            \redirect('/admin/sales');
        }

        return $this->render('admin/sales/edit', [
            'item' => $item,
            'errors' => [],
            'message' => null,
            'returnTo' => $this->safeReturnTo($returnTo, '/admin/sales'),
            'old' => [
                'customer_name' => (string) ($item->customer_name ?? ''),
                'customer_phone' => (string) ($item->customer_phone ?? ''),
                'customer_email' => (string) ($item->customer_email ?? ''),
                'interest_type' => (string) ($item->interest_type ?? 'other'),
                'source_origin' => (string) ($item->source_origin ?? 'direct_whatsapp'),
                'source_channel' => (string) ($item->source_channel ?? 'whatsapp'),
                'source_reference' => (string) ($item->source_reference ?? ''),
                'package_slug' => (string) ($item->package_slug ?? ''),
                'extra_service_slug' => (string) ($item->extra_service_slug ?? ''),
                'sales_temperature' => (string) ($item->sales_temperature ?? 'warm'),
                'closing_probability' => (string) ($item->closing_probability ?? '15'),
                'travelers_count' => (string) ($item->travelers_count ?? ''),
                'travel_date_estimate' => (string) ($item->travel_date_estimate ?? ''),
                'return_date_estimate' => (string) ($item->return_date_estimate ?? ''),
                'budget_min' => (string) ($item->budget_min ?? ''),
                'budget_max' => (string) ($item->budget_max ?? ''),
                'next_follow_up_at' => !empty($item->next_follow_up_at)
                    ? date('Y-m-d\TH:i', strtotime((string) $item->next_follow_up_at))
                    : '',
                'notes_summary' => (string) ($item->notes_summary ?? ''),
            ],
        ], 'adminUserLayout');
    }

    public function update()
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $returnTo = trim((string) ($_POST['return_to'] ?? '/admin/sales'));
        $adminId = (int) (AdminAuth::id() ?? 0);

        $result = $this->service->updateProfile(
            $id,
            $_POST,
            $adminId > 0 ? $adminId : null
        );

        if (!empty($result['success'])) {
            \redirect('/admin/sales/show?id=' . $id . '&return_to=' . urlencode($this->safeReturnTo($returnTo, '/admin/sales')));
        }

        $item = SalesOpportunity::find($id);

        return $this->render('admin/sales/edit', [
            'item' => $item,
            'errors' => $result['errors'] ?? [],
            'message' => $result['message'] ?? null,
            'returnTo' => $this->safeReturnTo($returnTo, '/admin/sales'),
            'old' => $result['old'] ?? $_POST,
        ], 'adminUserLayout');
    }
}