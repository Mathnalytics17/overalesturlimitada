<?php

namespace app\Controllers\admin\sales;

use app\Core\AdminAuth;
use app\Core\Controller;
use app\Core\Csrf;
use app\Services\Admin\Sales\SalesQuoteService;
use app\Core\Flash;
class SalesQuoteController extends Controller
{
    protected SalesQuoteService $service;

    public function __construct()
    {
        $this->service = new SalesQuoteService();
    }

    public function store()
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $opportunityId = (int)($_POST['sales_opportunity_id'] ?? 0);
        $admin = AdminAuth::user();

        $this->service->create(
            $opportunityId,
            $_POST,
            $admin?->id ? (int)$admin->id : null
        );

        \redirect('/admin/sales/show?id=' . $opportunityId);
    }

    public function markSent()
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $quoteId = (int)($_POST['quote_id'] ?? 0);
        $opportunityId = (int)($_POST['sales_opportunity_id'] ?? 0);
        $admin = AdminAuth::user();

        $this->service->markSent($quoteId, $admin?->id ? (int)$admin->id : null);

        \redirect('/admin/sales/show?id=' . $opportunityId);
    }

    public function markAccepted()
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $quoteId = (int)($_POST['quote_id'] ?? 0);
        $opportunityId = (int)($_POST['sales_opportunity_id'] ?? 0);
        $admin = AdminAuth::user();

        $this->service->markAccepted($quoteId, $admin?->id ? (int)$admin->id : null);

        \redirect('/admin/sales/show?id=' . $opportunityId);
    }

    public function markRejected()
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $quoteId = (int)($_POST['quote_id'] ?? 0);
        $opportunityId = (int)($_POST['sales_opportunity_id'] ?? 0);
        $admin = AdminAuth::user();

        $this->service->markRejected($quoteId, $admin?->id ? (int)$admin->id : null);

        \redirect('/admin/sales/show?id=' . $opportunityId);
    }
}