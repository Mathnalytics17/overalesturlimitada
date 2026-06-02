<?php
use app\Controllers\web\packageTour\TourPackageController;
/* api */
$app->router->get('/api/packagesTourist', [TourPackageController::class, 'apiList']);

use app\Controllers\api\ChatbotLeadController;

$app->router->post('/api/chatbot/leads/whatsapp', [ChatbotLeadController::class, 'whatsappAccepted']);
