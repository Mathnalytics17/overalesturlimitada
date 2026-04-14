<?php

namespace app\Controllers\web\pqrs;

use app\Core\Controller;
use app\Core\Request;
use app\Services\Pqrs\PqrsService;
use app\Core\Flash;

class PqrsController extends Controller
{
    protected PqrsService $service;

    public function __construct()
    {
        $this->service = new PqrsService();
    }

    public function index()
    {
        return $this->render('pqrs', [
            'pageCss' => '/public/styles/pqrs.css',
        ], 'mainUserLayout');
    }

    public function submit(Request $request)
    {
        $result = $this->service->createFromForm($request->getBody(), $request->files());

        if (!$result['success']) {
            return $this->render('pqrs', [
                'pageCss' => '/public/styles/pqrs.css',
                'message' => implode(' ', $result['errors'] ?? ['No fue posible registrar la PQRS.']),
                'messageType' => 'error',
                'old' => $result['old'] ?? [],
            ], 'mainUserLayout');
        }

        return $this->render('pqrs/submitted', [
            'pageCss' => '/public/styles/pqrs.css',
            'case' => $result['case'],
        ], 'mainUserLayout');
    }
}