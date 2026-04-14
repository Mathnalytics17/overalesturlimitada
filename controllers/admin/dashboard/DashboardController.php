<?php

namespace app\Controllers\admin\dashboard;

use app\Core\Controller;
use app\Services\Admin\Dashboard\DashboardService;

class DashboardController extends Controller
{
    protected DashboardService $service;

    public function __construct()
    {
        $this->service = new DashboardService();
    }

    public function index()
    {
        return $this->render('admin/dashboard/index', [
            'stats' => $this->service->getStats(),
            'pipeline' => $this->service->getPipelineSummary(),
            'alerts' => $this->service->getAlerts(),
            'products' => $this->service->getTopProducts(),
            'finance' => $this->service->getFinanceSummary(),
            'pqrs' => $this->service->getPqrsSummary(),
        ], 'adminUserLayout');
    }
}