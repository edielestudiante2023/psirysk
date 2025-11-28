<?php
/**
 * Script para recalcular los resultados del trabajador 16
 */

require __DIR__ . '/vendor/autoload.php';

// Bootstrap CodeIgniter
define('FCPATH', __DIR__ . '/public/');
$_SERVER['REQUEST_METHOD'] = 'CLI';
chdir(__DIR__);

// Create application
$app = new \CodeIgniter\CodeIgniter(new \Config\App());
$app->initialize();

use App\Services\CalculationService;

$calculationService = new CalculationService();

echo "Recalculando resultados para Worker 16...\n";

// Eliminar resultados anteriores
$db = \Config\Database::connect();
$db->table('calculated_results')->where('worker_id', 16)->delete();

echo "Resultados anteriores eliminados.\n";

// Calcular nuevamente
$results = $calculationService->calculateAndSaveResults(16);

if ($results) {
    echo "✅ Resultados recalculados exitosamente!\n\n";
    echo "Dominio Demandas:\n";
    echo "  Puntaje: {$results['dom_demandas_puntaje']}%\n";
    echo "  Nivel: {$results['dom_demandas_nivel']}\n\n";

    echo "Dimensiones del Dominio 3:\n";
    echo "  Demandas ambientales: {$results['dim_demandas_ambientales_puntaje']}%\n";
    echo "  Demandas cuantitativas: {$results['dim_demandas_cuantitativas_puntaje']}%\n";
    echo "  Influencia trabajo-extralaboral: {$results['dim_influencia_trabajo_entorno_extralaboral_puntaje']}%\n";
    echo "  Responsabilidad del cargo: {$results['dim_demandas_responsabilidad_puntaje']}%\n";
    echo "  Carga mental: {$results['dim_demandas_carga_mental_puntaje']}%\n";
    echo "  Consistencia del rol: {$results['dim_consistencia_rol_puntaje']}%\n";
    echo "  Jornada de trabajo: {$results['dim_demandas_jornada_trabajo_puntaje']}%\n";
    echo "  Demandas emocionales: {$results['dim_demandas_emocionales_puntaje']}%\n";
} else {
    echo "❌ Error al recalcular resultados\n";
}
