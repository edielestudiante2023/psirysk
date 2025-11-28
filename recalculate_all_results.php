<?php

/**
 * Script para recalcular todos los resultados después de actualizar baremos
 *
 * Uso: php spark recalculate:results
 * O directamente: php recalculate_all_results.php
 */

// Definir constantes necesarias
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);
define('APPPATH', __DIR__ . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR);
define('ROOTPATH', __DIR__ . DIRECTORY_SEPARATOR);
define('SYSTEMPATH', __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'codeigniter4' . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR);
define('WRITEPATH', __DIR__ . DIRECTORY_SEPARATOR . 'writable' . DIRECTORY_SEPARATOR);

// Cargar autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Cargar CodeIgniter
$app = \Config\Services::codeigniter();
$app->initialize();

// Importar clases necesarias
use App\Models\WorkerModel;
use App\Services\CalculationService;

// Crear instancias
$workerModel = new WorkerModel();
$calculationService = new CalculationService();

// Obtener todos los trabajadores completados
$workers = $workerModel->where('status', 'completed')->findAll();

echo "=== RECALCULANDO RESULTADOS CON BAREMOS ACTUALIZADOS ===\n\n";
echo "Total de trabajadores completados: " . count($workers) . "\n\n";

$success = 0;
$errors = 0;

foreach ($workers as $worker) {
    echo "Procesando trabajador ID {$worker['id']} - {$worker['name']}... ";

    try {
        // Recalcular resultados
        $result = $calculationService->calculateAndSaveResults($worker['id']);

        if ($result) {
            echo "✅ OK\n";
            $success++;
        } else {
            echo "❌ ERROR: No se pudo recalcular\n";
            $errors++;
        }
    } catch (\Exception $e) {
        echo "❌ EXCEPCIÓN: " . $e->getMessage() . "\n";
        $errors++;
    }
}

echo "\n=== RESUMEN ===\n";
echo "Exitosos: $success\n";
echo "Errores: $errors\n";
echo "Total: " . count($workers) . "\n";

if ($success > 0) {
    echo "\n✅ Los resultados han sido recalculados con los nuevos baremos.\n";
    echo "Ahora puedes recargar la vista: http://localhost/psyrisk/reports/intralaboral-a/1\n";
}
