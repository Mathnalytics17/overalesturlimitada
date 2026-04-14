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

        $this->service->reportPayment(
            $opportunityId,
            $_POST,
            $admin?->id ? (int)$admin->id : null
        );

        \redirect('/admin/sales/show?id=' . $opportunityId);
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

        $this->service->verifyPayment($paymentId, $admin?->id ? (int)$admin->id : null);

        \redirect('/admin/sales/show?id=' . $opportunityId);
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
            $this->service->rejectPayment($paymentId, $reason, $admin?->id ? (int)$admin->id : null);
        }

        \redirect('/admin/sales/show?id=' . $opportunityId);
    }
}