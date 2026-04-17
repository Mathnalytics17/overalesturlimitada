<?php

namespace app\Controllers\web\services;

use app\Core\Controller;
use app\Core\Request;
use app\Models\ExtraService;
use app\Services\Crm\LeadService;
use app\Core\Flash;

class ExtraServicesController extends Controller
{
    protected LeadService $service;

    public function __construct()
    {
        $this->service = new LeadService();
    }


    public function index() { $services = ExtraService::getActivos(); return $this->render('extraServices/index', [ 'title' => 'Servicios Extras', 'services' => $services, ]); }
    public function show(Request $request)
    {
        $body = $request->getBody();
        $slug = $body['slug'] ?? ($_GET['slug'] ?? '');

        $service = ExtraService::findBySlug((string) $slug);

        if (!$service) {
            return $this->render('extraServices/show', [
                
                'service' => null,
                'errors' => [],
                'old' => [],
                'message' => 'Servicio no encontrado.',
                'messageType' => 'error',
                'openModal' => false,
                'whatsappUrl' => null,
                'pageCss' => '/styles/contact.css',
            ], 'mainUserLayout');
        }

        return $this->render('extraServices/show', [
          
            'service' => $service,
            'errors' => [],
            'old' => [],
            'message' => null,
            'messageType' => null,
            'openModal' => false,
            'whatsappUrl' => $this->service->buildExtraServiceWhatsAppUrl($service),
            'pageCss' => '/styles/contact.css',
        ], 'mainUserLayout');
    }

    public function submitExtraServices(Request $request)
    {
        $body = $request->getBody();
        $extraServiceId = (int) ($body['extra_service_id'] ?? 0);

        $service = ExtraService::findActiveById($extraServiceId);

        if (!$service) {
            return $this->render('extraServices/show', [
                'service' => null,
                'errors' => [
                    'general' => ['Servicio no encontrado.']
                ],
                'old' => $body,
                'message' => 'Servicio no encontrado.',
                'messageType' => 'error',
                'openModal' => true,
                'whatsappUrl' => null,
                'pageCss' => '/styles/contact.css',
            ], 'mainUserLayout');
        }

        $result = $this->service->createFromWebForm('extra_service', $body);

        if (!$result['success']) {
            return $this->render('extraServices/show', [
                'service' => $service,
                'errors' => $result['errors'] ?? [],
                'old' => $result['old'] ?? [],
                'message' => 'Revisa los campos del formulario.',
                'messageType' => 'error',
                'openModal' => true,
                'whatsappUrl' => $this->service->buildExtraServiceWhatsAppUrl($service, $body),
                'pageCss' => '/styles/contact.css',
            ], 'mainUserLayout');
        }

        return $this->render('extraServices/show', [
            'service' => $service,
            'errors' => [],
            'old' => [],
            'message' => 'Tu solicitud fue enviada correctamente.',
            'messageType' => 'success',
            'openModal' => false,
            'whatsappUrl' => $result['whatsapp_url'] ?? $this->service->buildExtraServiceWhatsAppUrl($service, $body),
            'pageCss' => '/styles/contact.css',
        ], 'mainUserLayout');
    }
}