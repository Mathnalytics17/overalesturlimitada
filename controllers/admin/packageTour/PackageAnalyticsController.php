<?php

namespace app\Controllers\admin\packageTour;

use app\Core\Controller;
use app\Core\Request;
use app\Services\Package\PackageAnalyticsService;

class PackageAnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $report = (new PackageAnalyticsService())->report(
            (string) $request->input('from', ''),
            (string) $request->input('to', ''),
            (int) $request->input('page', 1),
            (int) $request->input('per_page', 25)
        );

        return $this->render('admin/packageTour/analytics', [
            'active' => 'package_analytics',
            'page_title' => 'Analítica de paquetes',
            'page_subtitle' => 'Vistas, favoritos y consultas por paquete.',
            'report' => $report,
        ], 'adminUserLayout');
    }
}
