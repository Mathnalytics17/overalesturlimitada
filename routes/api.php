<?php

use app\Controllers\web\packageTour\TourPackageController;

/* api */
$app->router->get('/api/packagesTourist', [TourPackageController::class, 'apiList']);
