<?php

/**
 * Script para recalcular TODOS los resultados después de correcciones críticas
 *
 * Correcciones aplicadas:
 * 1. Factor transformación Control Forma B: 80 → 72
 * 2. Baremo total Forma A: rangos corregidos según manual
 * 3. Baremo total Forma B: rangos corregidos según manual
 * 4. Validación de ítems completos por dimensión
 */

require __DIR__ . '/vendor/autoload.php';

use CodeIgniter\Config\Services;

// Bootstrap CodeIgniter
$pathsConfig = new \Config\Paths();
$app = new \CodeIgniter\CodeIgniter(new $pathsConfig());
$app->initialize();
$context = Services::request()->getContext();

// Get services
$db = \Config\Database::connect();
$calculationService = new \App\Services\CalculationService();

echo "\n";
echo "================================================================================\n";
echo "  RECÁLCULO MASIVO POST-CORRECCIONES CRÍTICAS\n";
echo "================================================================================\n";
echo "\n";
echo "Correcciones aplicadas:\n";
echo "  ✓ Factor Control Forma B: 80 → 72 (Tabla 26)\n";
echo "  ✓ Baremo total Forma A corregido (Tabla 33)\n";
echo "  ✓ Baremo total Forma B corregido (Tabla 33)\n";
echo "  ✓ Validación ítems completos agregada\n";
echo "\n";
echo "--------------------------------------------------------------------------------\n";

// Get all workers with calculated results
$query = $db->query("
    SELECT
        w.id,
        w.name,
        w.document,
        w.intralaboral_type,
        w.atiende_clientes,
        w.es_jefe,
        cr.intralaboral_total_puntaje as old_intra_puntaje,
        cr.intralaboral_total_nivel as old_intra_nivel,
        cr.dom_control_puntaje as old_control_puntaje,
        cr.dom_control_nivel as old_control_nivel
    FROM workers w
    INNER JOIN calculated_results cr ON w.id = cr.worker_id
    WHERE w.status = 'completed'
    ORDER BY w.id
");

$workers = $query->getResultArray();
$totalWorkers = count($workers);

echo "\nTotal de workers a recalcular: {$totalWorkers}\n";
echo "--------------------------------------------------------------------------------\n\n";

if ($totalWorkers === 0) {
    echo "✓ No hay workers con resultados calculados para recalcular.\n\n";
    exit(0);
}

$recalculados = 0;
$errores = 0;
$cambiosDetectados = [];

foreach ($workers as $worker) {
    $workerId = $worker['id'];
    $workerName = $worker['name'];
    $tipo = $worker['intralaboral_type'];

    echo "[{$workerId}] {$workerName} (Forma {$tipo})\n";
    echo "  Antiguo puntaje total: {$worker['old_intra_puntaje']} ({$worker['old_intra_nivel']})\n";
    echo "  Antiguo control: {$worker['old_control_puntaje']} ({$worker['old_control_nivel']})\n";

    try {
        // Delete old results
        $db->table('calculated_results')->where('worker_id', $workerId)->delete();

        // Recalculate
        $newResults = $calculationService->calculateAllForWorker($workerId);

        if ($newResults) {
            $recalculados++;

            // Get new values from database
            $newData = $db->table('calculated_results')
                ->where('worker_id', $workerId)
                ->get()
                ->getRowArray();

            echo "  ✓ Nuevo puntaje total: {$newData['intralaboral_total_puntaje']} ({$newData['intralaboral_total_nivel']})\n";
            echo "  ✓ Nuevo control: {$newData['dom_control_puntaje']} ({$newData['dom_control_nivel']})\n";

            // Detect changes
            $cambio = false;
            $detalles = [];

            // Check intralaboral total nivel change
            if ($worker['old_intra_nivel'] !== $newData['intralaboral_total_nivel']) {
                $cambio = true;
                $detalles[] = "NIVEL TOTAL: {$worker['old_intra_nivel']} → {$newData['intralaboral_total_nivel']}";
            }

            // Check control puntaje change (especially for Forma B)
            $oldControlPuntaje = floatval($worker['old_control_puntaje']);
            $newControlPuntaje = floatval($newData['dom_control_puntaje']);
            if (abs($oldControlPuntaje - $newControlPuntaje) > 0.5 && $tipo === 'B') {
                $cambio = true;
                $diff = $newControlPuntaje - $oldControlPuntaje;
                $detalles[] = "CONTROL (Forma B): {$oldControlPuntaje} → {$newControlPuntaje} (Δ " . sprintf("%+.1f", $diff) . ")";
            }

            if ($cambio) {
                $cambiosDetectados[] = [
                    'id' => $workerId,
                    'name' => $workerName,
                    'tipo' => $tipo,
                    'detalles' => $detalles
                ];
                echo "  ⚠️  CAMBIOS DETECTADOS:\n";
                foreach ($detalles as $detalle) {
                    echo "      - {$detalle}\n";
                }
            } else {
                echo "  ℹ️  Sin cambios significativos\n";
            }

        } else {
            echo "  ✗ Error en recálculo\n";
            $errores++;
        }

    } catch (\Exception $e) {
        echo "  ✗ ERROR: " . $e->getMessage() . "\n";
        $errores++;
    }

    echo "\n";
}

echo "================================================================================\n";
echo "  RESUMEN DE RECÁLCULO\n";
echo "================================================================================\n";
echo "\n";
echo "Total procesados: {$totalWorkers}\n";
echo "Recalculados exitosamente: {$recalculados}\n";
echo "Errores: {$errores}\n";
echo "Workers con cambios significativos: " . count($cambiosDetectados) . "\n";
echo "\n";

if (count($cambiosDetectados) > 0) {
    echo "--------------------------------------------------------------------------------\n";
    echo "  DETALLE DE CAMBIOS SIGNIFICATIVOS\n";
    echo "--------------------------------------------------------------------------------\n\n";

    foreach ($cambiosDetectados as $cambio) {
        echo "[{$cambio['id']}] {$cambio['name']} (Forma {$cambio['tipo']})\n";
        foreach ($cambio['detalles'] as $detalle) {
            echo "  • {$detalle}\n";
        }
        echo "\n";
    }
}

echo "================================================================================\n";
echo "  ✓ RECÁLCULO COMPLETADO\n";
echo "================================================================================\n";
echo "\n";
echo "NOTA IMPORTANTE:\n";
echo "Los cambios se deben principalmente a:\n";
echo "  1. Corrección del factor Control Forma B (afecta dominio Control)\n";
echo "  2. Corrección de baremos totales (puede cambiar niveles de riesgo)\n";
echo "  3. Validación estricta de ítems completos (puede invalidar dimensiones)\n";
echo "\n";
echo "Recomendación: Revisar los workers con cambios significativos.\n";
echo "\n";
