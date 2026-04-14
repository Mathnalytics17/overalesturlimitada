<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php-error.log');
error_reporting(E_ALL);

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