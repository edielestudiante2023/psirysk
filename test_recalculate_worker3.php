<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap CodeIgniter
$pathsConfig = APPPATH . 'Config/Paths.php';
require realpath($pathsConfig) ?: $pathsConfig;

$paths = new Config\Paths();
$bootstrap = rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';
$app = require realpath($bootstrap) ?: $bootstrap;

// Get CalculationService
$calculationService = new \App\Services\CalculationService();

echo "Recalculando resultados para worker_id = 3...\n\n";

try {
    $result = $calculationService->recalculateResults(3);

    if ($result) {
        echo "✅ Resultados recalculados exitosamente\n";
    } else {
        echo "❌ Error al recalcular resultados\n";
    }
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\nRevisa el archivo de log para ver los detalles del cálculo.\n";
