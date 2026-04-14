<?php

namespace app\Controllers\admin\sales;

use app\Core\AdminAuth;
use app\Core\Controller;
use app\Core\Csrf;
use app\Models\SalesOrder;
use app\Services\Admin\Sales\SalesOrderService;
use app\Models\SalesOpportunity;
use app\Models\SalesOpportunityEvent;
use app\Models\SalesPayment;
use app\Core\Flash;
class SalesOrderController extends Controller
{
    protected SalesOrderService $service;

    public function __construct()
    {
        $this->service = new SalesOrderService();
    }

    public function index()
    {
        $search = trim($_GET['q'] ?? '');
        $items = SalesOrder::adminList($search !== '' ? $search : null);

        return $this->render('admin/salesOrders/index', [
            'items' => $items,
            'search' => $search,
        ], 'adminUserLayout');
    }


    public function show()
{
    $id = (int)($_GET['id'] ?? 0);
    $item = SalesOrder::find($id);

    if (!$item) {
        \redirect('/admin/sales-orders');
    }

    $opportunity = SalesOpportunity::find((int)$item->sales_opportunity_id);
    $payments = SalesPayment::byOpportunity((int)$item->sales_opportunity_id);
    $events = SalesOpportunityEvent::byOpportunity((int)$item->sales_opportunity_id);

    return $this->render('admin/salesOrders/show', [
        'item' => $item,
        'opportunity' => $opportunity,
        'payments' => $payments,
        'events' => $events,
    ], 'adminUserLayout');
}

public function updateNotes()
{
    if (!Csrf::validate($_POST['_csrf'] ?? null)) {
        http_response_code(419);
        exit('CSRF inválido');
    }

    $id = (int)($_POST['id'] ?? 0);
    $notes = trim((string)($_POST['notes'] ?? ''));
    $admin = AdminAuth::user();

    $this->service->updateNotes($id, $notes, $admin?->id ? (int)$admin->id : null);

    \redirect('/admin/sales-orders/show?id=' . $id);
}
    public function createFromOpportunity()
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $opportunityId = (int)($_POST['sales_opportunity_id'] ?? 0);
        $admin = AdminAuth::user();

        $result = $this->service->createFromOpportunity(
            $opportunityId,
            $admin?->id ? (int)$admin->id : null
        );

        if (!empty($result['order'])) {
            \redirect('/admin/sales-orders');
        }

        \redirect('/admin/sales/show?id=' . $opportunityId);
    }

    public function updateOperationalStatus()
{
    if (!Csrf::validate($_POST['_csrf'] ?? null)) {
        http_response_code(419);
        exit('CSRF inválido');
    }

    $orderId = (int)($_POST['id'] ?? 0);
    $status = trim((string)($_POST['operational_status'] ?? ''));
    $returnTo = trim((string)($_POST['return_to'] ?? 'index'));
    $admin = AdminAuth::user();

    $this->service->updateOperationalStatus($orderId, $status, $admin?->id ? (int)$admin->id : null);

    if ($returnTo === 'show') {
        \redirect('/admin/sales-orders/show?id=' . $orderId);
    }

    \redirect('/admin/sales-orders');
}
}