<?php

/**
 * Script simple para recalcular resultados después de correcciones
 */

// Get CodeIgniter base path
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);
chdir(FCPATH);

// Boot the CodeIgniter application
require FCPATH . '../app/Config/Paths.php';
$paths = new Config\Paths();
require $paths->systemDirectory . '/bootstrap.php';
$app = Config\Services::codeigniter();
$app->initialize();
$context = is_cli() ? 'php-cli' : 'web';
$app->setContext($context);

// Now we can use models and services
$db = \Config\Database::connect();
$calculationService = new \App\Services\CalculationService();

echo "\n╔══════════════════════════════════════════════════════════════════════════════╗\n";
echo "║  RECÁLCULO POST-CORRECCIONES CRÍTICAS                                       ║\n";
echo "╚══════════════════════════════════════════════════════════════════════════════╝\n\n";

echo "Correcciones aplicadas:\n";
echo "  ✓ Factor Control Forma B: 80 → 72\n";
echo "  ✓ Baremo total Forma A corregido\n";
echo "  ✓ Baremo total Forma B corregido\n";
echo "  ✓ Validación ítems completos\n\n";

// Get workers with results
$workers = $db->query("
    SELECT w.id, w.name, w.intralaboral_type,
           cr.intralaboral_total_puntaje, cr.intralaboral_total_nivel
    FROM workers w
    INNER JOIN calculated_results cr ON w.id = cr.worker_id
    WHERE w.status = 'completed'
    ORDER BY w.id
")->getResultArray();

$total = count($workers);
echo "Workers a recalcular: {$total}\n";
echo str_repeat("─", 80) . "\n\n";

$success = 0;
$cambios = [];

foreach ($workers as $worker) {
    $id = $worker['id'];
    $oldPuntaje = $worker['intralaboral_total_puntaje'];
    $oldNivel = $worker['intralaboral_total_nivel'];

    echo "[{$id}] {$worker['name']} (Forma {$worker['intralaboral_type']})\n";
    echo "  Antes: {$oldPuntaje} ({$oldNivel})\n";

    try {
        // Delete old
        $db->table('calculated_results')->where('worker_id', $id)->delete();

        // Recalculate
        $calculationService->calculateAllForWorker($id);

        // Get new
        $nuevo = $db->table('calculated_results')
            ->select('intralaboral_total_puntaje, intralaboral_total_nivel')
            ->where('worker_id', $id)
            ->get()
            ->getRowArray();

        if ($nuevo) {
            $newPuntaje = $nuevo['intralaboral_total_puntaje'];
            $newNivel = $nuevo['intralaboral_total_nivel'];

            echo "  Después: {$newPuntaje} ({$newNivel})\n";

            if ($oldNivel !== $newNivel) {
                echo "  ⚠️  NIVEL CAMBIÓ: {$oldNivel} → {$newNivel}\n";
                $cambios[] = "{$worker['name']} (#{$id}): {$oldNivel} → {$newNivel}";
            } else {
                echo "  ✓ Sin cambio de nivel\n";
            }

            $success++;
        } else {
            echo "  ✗ Error: no se generaron resultados\n";
        }
    } catch (\Exception $e) {
        echo "  ✗ Error: " . $e->getMessage() . "\n";
    }

    echo "\n";
}

echo str_repeat("═", 80) . "\n";
echo "RESUMEN\n";
echo str_repeat("═", 80) . "\n";
echo "Total: {$total}\n";
echo "Exitosos: {$success}\n";
echo "Cambios de nivel: " . count($cambios) . "\n\n";

if (count($cambios) > 0) {
    echo "TRABAJADORES CON CAMBIO DE NIVEL:\n";
    echo str_repeat("─", 80) . "\n";
    foreach ($cambios as $cambio) {
        echo "  • {$cambio}\n";
    }
    echo "\n";
}

echo "✓ RECÁLCULO COMPLETADO\n\n";
