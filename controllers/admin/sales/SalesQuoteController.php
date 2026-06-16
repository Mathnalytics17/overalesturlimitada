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

        $result = $this->service->create(
            $opportunityId,
            $_POST,
            $admin?->id ? (int)$admin->id : null
        );

        if (!empty($result['success'])) {
            Flash::success($result['message'] ?? 'Cotización creada correctamente.');
        } else {
            Flash::error($result['message'] ?? 'No fue posible crear la cotización.');
        }

        \redirect($this->safeReturnTo($_POST['return_to'] ?? '', '/admin/sales/show?id=' . $opportunityId));
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

        $ok = $this->service->markSent($quoteId, $admin?->id ? (int)$admin->id : null);
        $ok ? Flash::success('Cotización marcada como enviada.') : Flash::error('No fue posible marcar la cotización como enviada.');

        \redirect($this->safeReturnTo($_POST['return_to'] ?? '', '/admin/sales/show?id=' . $opportunityId));
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

        $ok = $this->service->markAccepted($quoteId, $admin?->id ? (int)$admin->id : null);
        $ok ? Flash::success('Cotización aceptada. Ya puedes crear la venta/reserva.') : Flash::error('No fue posible aceptar la cotización.');

        \redirect($this->safeReturnTo($_POST['return_to'] ?? '', '/admin/sales/show?id=' . $opportunityId));
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

        $ok = $this->service->markRejected($quoteId, $admin?->id ? (int)$admin->id : null);
        $ok ? Flash::success('Cotización rechazada.') : Flash::error('No fue posible rechazar la cotización.');

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