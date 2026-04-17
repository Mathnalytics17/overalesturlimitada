<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../shared/helpers.php';
require_once __DIR__ . '/../core/Autoloader.php';

\app\Core\Autoloader::register(dirname(__DIR__));

use app\Core\Application;

$app = new Application(dirname(__DIR__));

require base_path('routes/web.php');
require base_path('routes/admin.php');
require base_path('routes/api.php');

$app->run();
