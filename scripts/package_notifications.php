<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../shared/helpers.php';
require_once __DIR__ . '/../core/Autoloader.php';

\app\Core\Autoloader::register(dirname(__DIR__));
new \app\Core\Application(dirname(__DIR__));

use app\Services\Package\PackageNotificationService;

$args = array_slice($argv, 1);
$queueRecommendations = in_array('--queue-recommendations', $args, true);
$process = in_array('--process', $args, true);
$dryRun = in_array('--dry-run', $args, true);
$queuePackageSlug = null;
$limit = 50;

foreach ($args as $arg) {
    if (str_starts_with($arg, '--limit=')) {
        $limit = max(1, min(500, (int) substr($arg, strlen('--limit='))));
    }

    if (str_starts_with($arg, '--queue-package=')) {
        $queuePackageSlug = trim((string) substr($arg, strlen('--queue-package=')));
    }
}

if (!$queueRecommendations && !$process && $queuePackageSlug === null) {
    fwrite(STDERR, "Uso:\n");
    fwrite(STDERR, "  php scripts/package_notifications.php --queue-recommendations\n");
    fwrite(STDERR, "  php scripts/package_notifications.php --queue-package=slug-del-paquete\n");
    fwrite(STDERR, "  php scripts/package_notifications.php --process --dry-run [--limit=50]\n");
    fwrite(STDERR, "  php scripts/package_notifications.php --process [--limit=50]\n");
    exit(1);
}

$service = new PackageNotificationService();

if ($queuePackageSlug !== null) {
    $package = \app\Models\TourPackage::findBySlug($queuePackageSlug);
    if (!$package) {
        fwrite(STDERR, "Paquete no encontrado: {$queuePackageSlug}\n");
        exit(1);
    }

    $queued = $service->queueNewPublishedPackage($package);
    echo "Avisos de paquete nuevo encolados: {$queued}\n";
}

if ($queueRecommendations) {
    $queued = $service->queueRecommendationsForSubscribers();
    echo "Recomendaciones encoladas: {$queued}\n";
}

if ($process) {
    try {
        $result = $service->processPending($limit, $dryRun);
        echo ($dryRun ? "Simulación" : "Procesamiento") . " de cola:\n";
        foreach ($result as $key => $value) {
            echo "  {$key}: {$value}\n";
        }
    } catch (Throwable $exception) {
        fwrite(STDERR, $exception->getMessage() . "\n");
        exit(1);
    }
}
