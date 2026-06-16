<?php

namespace app\Controllers\admin\sales;

use app\Core\AdminAuth;
use app\Core\Controller;
use app\Core\Csrf;
use app\Core\Flash;
use app\Models\SalesOpportunity;
use app\Models\SalesOpportunityEvent;
use app\Models\SalesOrder;
use app\Models\SalesPayment;
use app\Services\Admin\Sales\SalesOrderService;

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
        $filters = [
            'q' => $search,
            'commercial_status' => trim((string) ($_GET['commercial_status'] ?? '')),
            'operational_status' => trim((string) ($_GET['operational_status'] ?? '')),
        ];

        $pagination = SalesOrder::paginateAdmin(
            $filters,
            (int) ($_GET['page'] ?? 1),
            (int) ($_GET['per_page'] ?? 25)
        );

        return $this->render('admin/salesOrder/index', [
            'page_title' => 'Ventas / Reservas',
            'page_subtitle' => 'Controla las ventas cerradas y su avance operativo.',
            'items' => $pagination['items'],
            'search' => $search,
            'filters' => $filters,
            'pagination' => $pagination,
        ], 'adminUserLayout');
    }

    public function show()
    {
        $id = (int)($_GET['id'] ?? 0);
        $item = SalesOrder::find($id);

        if (!$item) {
            Flash::error('La venta/reserva no fue encontrada.');
            \redirect('/admin/sales-orders');
        }

        $opportunity = SalesOpportunity::find((int)$item->sales_opportunity_id);
        $payments = SalesPayment::byOpportunity((int)$item->sales_opportunity_id);
        $events = SalesOpportunityEvent::byOpportunity((int)$item->sales_opportunity_id);

        return $this->render('admin/salesOrder/show', [
            'page_title' => 'Detalle de venta / reserva',
            'page_subtitle' => 'Consulta el estado comercial, pagos y operación.',
            'item' => $item,
            'opportunity' => $opportunity,
            'payments' => $payments,
            'events' => $events,
        ], 'adminUserLayout');
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
            if (!empty($result['success'])) {
                Flash::success($result['message'] ?? 'Venta/reserva creada correctamente.');
            } else {
                Flash::warning($result['message'] ?? 'La oportunidad ya tenía una venta/reserva creada.');
            }

            \redirect('/admin/sales-orders/show?id=' . (int)$result['order']->id);
        }

        Flash::error($result['message'] ?? 'No fue posible crear la venta/reserva.');
        \redirect($this->safeReturnTo($_POST['return_to'] ?? '', '/admin/sales/show?id=' . $opportunityId));
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

        $ok = $this->service->updateOperationalStatus($orderId, $status, $admin?->id ? (int)$admin->id : null);
        if ($ok) {
            Flash::success('Estado operativo actualizado correctamente.');
        } elseif ($status === 'completed') {
            Flash::error('No puedes marcar como completado si la venta todavía tiene saldo pendiente.');
        } else {
            Flash::error('No fue posible actualizar el estado operativo.');
        }

        if ($returnTo === 'show') {
            \redirect('/admin/sales-orders/show?id=' . $orderId);
        }

        \redirect('/admin/sales-orders');
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

        $ok = $this->service->updateNotes($id, $notes, $admin?->id ? (int)$admin->id : null);
        $ok ? Flash::success('Notas operativas actualizadas correctamente.') : Flash::error('No fue posible actualizar las notas operativas.');

        \redirect('/admin/sales-orders/show?id=' . $id);
    }

    protected function safeReturnTo(string $returnTo, string $fallback): string
    {
        $returnTo = trim($returnTo);
        if ($returnTo === '' || !str_starts_with($returnTo, '/admin')) {
            return $fallback;
        }
        return $returnTo;
    }
}
