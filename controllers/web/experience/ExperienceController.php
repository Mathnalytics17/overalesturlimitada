<?php

namespace app\Controllers\web\experience;

use app\Core\Controller;
use app\Core\Csrf;
use app\Core\Request;
use app\Models\TravelExperience;
use app\Services\Experience\TravelExperienceService;
use app\Models\TravelExperienceImage;
use app\Core\Flash;

class ExperienceController extends Controller
{
    protected TravelExperienceService $service;

    public function __construct()
    {
        $this->service = new TravelExperienceService();
    }

   public function index()
{
    $items = TravelExperience::approvedList(100);

    foreach ($items as $item) {
        $item->cover_image = TravelExperienceImage::coverByExperience((int)$item->id);
    }

    $flashModal = null;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (!empty($_SESSION['experience_flash_modal'])) {
        $flashModal = $_SESSION['experience_flash_modal'];
        unset($_SESSION['experience_flash_modal']);
    }

    return $this->render('experiences/index', [
        'items' => $items,
        'flashModal' => $flashModal,
    ], 'mainUserLayout');
}

    public function share()
    {


    
        return $this->render('experiences/share', [
            'errors' => [],
            'message' => null,
            'old' => [],
        ], 'mainUserLayout');
    }

    public function store(Request $request)
{
    if (!Csrf::validate($_POST['_csrf'] ?? null)) {
        http_response_code(419);
        exit('CSRF inválido');
    }

    $result = $this->service->createFromPublicForm($request->getBody(), $_FILES);

    if (!empty($result['success'])) {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $_SESSION['experience_flash_modal'] = [
            'type' => 'success',
            'title' => 'Experiencia enviada',
            'message' => 'Tu experiencia fue enviada correctamente y quedó pendiente de revisión.',
        ];

        \redirect('/experiences');
        return;
    }

    return $this->render('experiences/share', [
        'errors' => $result['errors'] ?? [],
        'message' => $result['message'] ?? null,
        'old' => $result['old'] ?? [],
        'success' => false,
    ], 'mainUserLayout');
}
    
}