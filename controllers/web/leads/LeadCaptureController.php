<?php

namespace app\Controllers\web\leads;

use app\Core\Controller;
use app\Core\RateLimiter;
use app\Core\Request;
use app\Services\Crm\LeadService;

class LeadCaptureController extends Controller
{
    protected LeadService $service;

    public function __construct()
    {
        $this->service = new LeadService();
    }

    public function submitContact(Request $request)
    {
        return $this->handle('contact', $request, 'contact', '/styles/contact.css');
    }

    public function submitPqrs(Request $request)
    {
        return $this->handle('pqrs', $request, 'pqrs', '/styles/pqrs.css');
    }

    public function submitTickets(Request $request)
    {
        return $this->handle('tickets', $request, 'tickets', '/styles/contact.css');
    }

    public function submitExtraServices(Request $request)
    {
        return $this->handle('extra_service', $request, 'extra-services/show', '/styles/contact.css');
    }

    public function submitPackage(Request $request)
{
    $rateLimitResponse = $this->checkRateLimit('lead_package_submit', $request, 5, 1800, 'packagesTourist/inquiryResult');
    if ($rateLimitResponse !== null) {
        return $rateLimitResponse;
    }

    $turnstile = validate_turnstile($request->input('cf-turnstile-response'), $request->getIpAddress());
    if (!$turnstile['success']) {
        return $this->render('packagesTourist/inquiryResult', [
            'success' => false,
            'errors' => [$turnstile['message'] ?? 'Debes completar la verificacion de seguridad.'],
            'lead' => null,
            'whatsappUrl' => null,
            'whatsappMessage' => null,
            'old' => $request->getBody(),
        ], 'mainUserLayout');
    }

    $result = $this->service->createFromWebForm('package', $request->getBody());

    $this->registerRateLimitAttempt('lead_package_submit', $request, 1800);

    if (!$result['success']) {
        return $this->render('packagesTourist/inquiryResult', [
            'success' => false,
            'errors' => $result['errors'] ?? ['No fue posible registrar tu solicitud.'],
            'lead' => null,
            'whatsappUrl' => null,
            'whatsappMessage' => null,
            'old' => $result['old'] ?? [],
        ], 'mainUserLayout');
    }

    return $this->render('packagesTourist/inquiryResult', [
        'success' => true,
        'errors' => [],
        'lead' => $result['lead'],
        'whatsappUrl' => $result['whatsapp_url'] ?? null,
        'whatsappMessage' => $result['whatsapp_message'] ?? null,
    ], 'mainUserLayout');
}
protected function flattenErrors(array $errors): string
{
    $messages = [];

    foreach ($errors as $fieldErrors) {
        if (is_array($fieldErrors)) {
            foreach ($fieldErrors as $error) {
                $messages[] = $error;
            }
        } elseif (is_string($fieldErrors)) {
            $messages[] = $fieldErrors;
        }
    }

    return implode(' ', $messages);
}
    protected function handle(string $sourceType, Request $request, string $view, string $pageCss)
    {
        $rateLimitResponse = $this->checkRateLimit('lead_form_' . $sourceType, $request, 5, 1800, $view, $pageCss);
        if ($rateLimitResponse !== null) {
            return $rateLimitResponse;
        }

        $turnstile = validate_turnstile($request->input('cf-turnstile-response'), $request->getIpAddress());
        if (!$turnstile['success']) {
            return $this->render($view, [
                'pageCss' => $pageCss,
                'message' => $turnstile['message'] ?? 'Debes completar la verificacion de seguridad.',
                'messageType' => 'error',
                'old' => $request->getBody(),
            ], 'mainUserLayout');
        }

        $result = $this->service->createFromWebForm($sourceType, $request->getBody());
        $this->registerRateLimitAttempt('lead_form_' . $sourceType, $request, 1800);

        if (!$result['success']) {
            return $this->render($view, [
                'pageCss' => $pageCss,
                'message' => $this->flattenErrors($result['errors'] ?? ['general' => ['No fue posible registrar tu solicitud.']]),
                'messageType' => 'error',
                'old' => $result['old'] ?? [],
            ], 'mainUserLayout');
        }

        return $this->render('leads/created', [
            'pageCss' => $pageCss,
            'lead' => $result['lead'],
            'whatsappUrl' => $result['whatsapp_url'] ?? null,
            'whatsappMessage' => $result['whatsapp_message'] ?? null,
        ], 'mainUserLayout');
    }

    protected function checkRateLimit(string $action, Request $request, int $maxAttempts, int $decaySeconds, string $view, ?string $pageCss = null): mixed
    {
        $rateLimitKey = rate_limit_key($action, $request->getIpAddress());

        if (!RateLimiter::tooManyAttempts($rateLimitKey, $maxAttempts, $decaySeconds)) {
            return null;
        }

        $payload = [
            'message' => 'Demasiadas solicitudes desde esta conexion. Espera unos minutos antes de volver a intentar.',
            'messageType' => 'error',
            'old' => $request->getBody(),
        ];

        if ($pageCss !== null) {
            $payload['pageCss'] = $pageCss;
        }

        if ($view === 'packagesTourist/inquiryResult') {
            $payload['success'] = false;
            $payload['errors'] = ['Demasiadas solicitudes desde esta conexion. Espera unos minutos antes de volver a intentar.'];
            $payload['lead'] = null;
            $payload['whatsappUrl'] = null;
            $payload['whatsappMessage'] = null;
        }

        return $this->render($view, $payload, 'mainUserLayout');
    }

    protected function registerRateLimitAttempt(string $action, Request $request, int $decaySeconds): void
    {
        $rateLimitKey = rate_limit_key($action, $request->getIpAddress());
        RateLimiter::hit($rateLimitKey, $decaySeconds);
    }
}
