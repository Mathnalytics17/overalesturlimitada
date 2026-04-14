<?php

namespace app\Controllers\admin\experience;

use app\Core\AdminAuth;
use app\Core\Controller;
use app\Core\Csrf;
use app\Models\TravelExperience;
use app\Services\Experience\TravelExperienceService;
use app\Models\TravelExperienceImage;
use app\Core\Flash;

class AdminExperienceController extends Controller
{
    protected TravelExperienceService $service;

    public function __construct()
    {
        $this->service = new TravelExperienceService();
    }

    public function index()
    {
        $filters = [
            'q' => trim($_GET['q'] ?? ''),
            'status' => trim($_GET['status'] ?? ''),
            'experience_type' => trim($_GET['experience_type'] ?? ''),
        ];

        return $this->render('admin/experiences/index', [
            'items' => TravelExperience::adminList($filters, 200),
            'filters' => $filters,
            'counts' => [
                'pending_review' => TravelExperience::countByStatus('pending_review'),
                'approved' => TravelExperience::countByStatus('approved'),
                'rejected' => TravelExperience::countByStatus('rejected'),
                'archived' => TravelExperience::countByStatus('archived'),
            ],
        ], 'adminUserLayout');
    }

    public function show()
    {
        $id = (int)($_GET['id'] ?? 0);
        $item = TravelExperience::find($id);

        if (!$item) {
            \redirect('/admin/experiences');
        }

        return $this->render('admin/experiences/show', [
            'item' => $item,
            'errors' => [],
            'message' => null,
            'old' => [],
            'images' => TravelExperienceImage::byExperience((int)$item->id),
        ], 'adminUserLayout');
    }

 public function approve()
{
    if (!Csrf::validate($_POST['_csrf'] ?? null)) {
        http_response_code(419);
        exit('CSRF inválido');
    }

    $id = (int)($_POST['id'] ?? 0);
    $admin = AdminAuth::user();

    $this->service->approve($id, $admin?->id ? (int)$admin->id : null);
    Flash::success('La experiencia fue aprobada correctamente.');
    \redirect('/admin/experiences');
return;
}

    public function reject()
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $id = (int)($_POST['id'] ?? 0);
        $adminNotes = trim((string)($_POST['admin_notes'] ?? ''));
        $admin = AdminAuth::user();

        $this->service->reject($id, $adminNotes, $admin?->id ? (int)$admin->id : null);

        \redirect('/admin/experiences/show?id=' . $id);
    }

    public function update()
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $id = (int)($_POST['id'] ?? 0);
        $result = $this->service->updateByAdmin($id, $_POST);

        if (!empty($result['success'])) {
            \redirect('/admin/experiences/show?id=' . $id);
        }

        $item = TravelExperience::find($id);

        return $this->render('admin/experiences/show', [
            'item' => $item,
            'errors' => $result['errors'] ?? [],
            'message' => $result['message'] ?? null,
            'old' => $result['old'] ?? [],
        ], 'adminUserLayout');
    }
}