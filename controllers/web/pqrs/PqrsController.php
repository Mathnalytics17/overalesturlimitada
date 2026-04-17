<?php

namespace app\Controllers\web\pqrs;

use app\Core\Controller;
use app\Core\RateLimiter;
use app\Core\Request;
use app\Services\Pqrs\PqrsService;

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
            'pageCss' => '/styles/pqrs.css',
            'title' => 'PQRS'
        ], 'mainUserLayout');
    }

    public function submit(Request $request)
    {
        $ipAddress = $request->getIpAddress();
        $rateLimitKey = rate_limit_key('web_pqrs_submit', $ipAddress);

        if (RateLimiter::tooManyAttempts($rateLimitKey, 3, 1800)) {
            return $this->render('pqrs', [
                'pageCss' => '/styles/pqrs.css',
                'message' => 'Demasiadas solicitudes PQRS desde esta conexion. Espera unos minutos antes de intentarlo de nuevo.',
                'messageType' => 'error',
                'old' => $request->getBody(),
            ], 'mainUserLayout');
        }

        $turnstile = validate_turnstile($request->input('cf-turnstile-response'), $ipAddress);
        if (!$turnstile['success']) {
            return $this->render('pqrs', [
                'pageCss' => '/styles/pqrs.css',
                'message' => $turnstile['message'] ?? 'Debes completar la verificacion de seguridad.',
                'messageType' => 'error',
                'old' => $request->getBody(),
            ], 'mainUserLayout');
        }

        $result = $this->service->createFromForm($request->getBody(), $request->files());

        if (!$result['success']) {
            RateLimiter::hit($rateLimitKey, 1800);
            return $this->render('pqrs', [
                'pageCss' => '/styles/pqrs.css',
                'message' => implode(' ', $result['errors'] ?? ['No fue posible registrar la PQRS.']),
                'messageType' => 'error',
                'old' => $result['old'] ?? [],
            ], 'mainUserLayout');
        }

        RateLimiter::hit($rateLimitKey, 1800);

        return $this->render('pqrs/submitted', [
            'pageCss' => '/styles/pqrs.css',
            'case' => $result['case'],
        ], 'mainUserLayout');
    }
}
