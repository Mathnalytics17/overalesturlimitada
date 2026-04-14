<?php

namespace app\Controllers\web\leads;

use app\Core\Controller;
use app\Core\Request;
use app\Services\Crm\LeadService;
use app\Core\Flash;

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
    $result = $this->service->createFromWebForm('package', $request->getBody());

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
        $result = $this->service->createFromWebForm($sourceType, $request->getBody());

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
}
