<?php

namespace app\Controllers\admin\sales;

use app\Core\AdminAuth;
use app\Core\Controller;
use app\Core\Csrf;
use app\Services\Admin\Sales\SalesPaymentService;
use app\Core\Flash;
class SalesPaymentController extends Controller
{
    protected SalesPaymentService $service;

    public function __construct()
    {
        $this->service = new SalesPaymentService();
    }

    public function store()
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $opportunityId = (int)($_POST['sales_opportunity_id'] ?? 0);
        $admin = AdminAuth::user();

        $result = $this->service->reportPayment(
            $opportunityId,
            $_POST,
            $admin?->id ? (int)$admin->id : null
        );

        if (!empty($result['success'])) {
            Flash::success($result['message'] ?? 'Pago reportado correctamente.');
        } else {
            Flash::error($result['message'] ?? 'No fue posible registrar el pago.');
        }

        \redirect($this->safeReturnTo($_POST['return_to'] ?? '', '/admin/sales/show?id=' . $opportunityId));
    }

    public function verify()
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $paymentId = (int)($_POST['payment_id'] ?? 0);
        $opportunityId = (int)($_POST['sales_opportunity_id'] ?? 0);
        $admin = AdminAuth::user();

        $result = $this->service->verifyPayment($paymentId, $admin?->id ? (int)$admin->id : null);
        if (!empty($result['success'])) {
            Flash::success($result['message'] ?? 'Pago validado correctamente.');
        } else {
            Flash::error($result['message'] ?? 'No fue posible validar el pago.');
        }

        \redirect($this->safeReturnTo($_POST['return_to'] ?? '', '/admin/sales/show?id=' . $opportunityId));
    }

    public function reject()
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $paymentId = (int)($_POST['payment_id'] ?? 0);
        $opportunityId = (int)($_POST['sales_opportunity_id'] ?? 0);
        $reason = trim((string)($_POST['reason'] ?? ''));
        $admin = AdminAuth::user();

        if ($reason !== '') {
            $ok = $this->service->rejectPayment($paymentId, $reason, $admin?->id ? (int)$admin->id : null);
            $ok ? Flash::success('Pago rechazado correctamente.') : Flash::error('No fue posible rechazar el pago.');
        } else {
            Flash::error('Debes indicar el motivo del rechazo.');
        }

        \redirect($this->safeReturnTo($_POST['return_to'] ?? '', '/admin/sales/show?id=' . $opportunityId));
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