<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class RecalculateEstres extends BaseCommand
{
    protected $group       = 'Recalculate';
    protected $name        = 'recalculate:estres';
    protected $description = 'Recalcula puntajes de estrés para todos los workers completados desde responses raw';

    public function run(array $params)
    {
        CLI::write('╔═══════════════════════════════════════════════════════════════════════╗', 'yellow');
        CLI::write('║  RECÁLCULO MASIVO DE ESTRÉS - NÚCLEO DEL APLICATIVO                  ║', 'yellow');
        CLI::write('╚═══════════════════════════════════════════════════════════════════════╝', 'yellow');
        CLI::newLine();

        $workerModel = new \App\Models\WorkerModel();
        $responseModel = new \App\Models\ResponseModel();
        $calculatedResultModel = new \App\Models\CalculatedResultModel();

        // Get all completed workers with estres form_type
        $workers = $workerModel
            ->where('status', 'completed')
            ->whereIn('form_type', ['A', 'B'])
            ->findAll();

        if (empty($workers)) {
            CLI::error('No se encontraron workers completados.');
            return;
        }

        CLI::write('Total de workers a procesar: ' . count($workers), 'green');
        CLI::newLine();

        $processed = 0;
        $updated = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($workers as $worker) {
            CLI::write("Procesando worker #{$worker['id']} - {$worker['name']} (Forma {$worker['form_type']})...", 'cyan');

            try {
                // Verificar si tiene respuestas de estrés
                $estresResponses = $responseModel
                    ->where('worker_id', $worker['id'])
                    ->where('form_type', 'estres')
                    ->findAll();

                if (empty($estresResponses)) {
                    CLI::write("  ⊗ Skipped - No tiene respuestas de estrés", 'yellow');
                    $skipped++;
                    continue;
                }

                // Crear array de respuestas en formato esperado por EstresScoring
                $respuestas = [];
                foreach ($estresResponses as $resp) {
                    $respuestas[$resp['question_number']] = $resp['answer_value'];
                }

                // Verificar que tenga las 31 respuestas
                if (count($respuestas) !== 31) {
                    CLI::write("  ⊗ Skipped - Solo tiene " . count($respuestas) . "/31 respuestas", 'yellow');
                    $skipped++;
                    continue;
                }

                // Calcular con EstresScoring
                $resultado = \App\Libraries\EstresScoring::calificar($respuestas, $worker['form_type']);

                // Obtener calculated_result existente
                $existingResult = $calculatedResultModel
                    ->where('worker_id', $worker['id'])
                    ->first();

                if (!$existingResult) {
                    CLI::write("  ⊗ Skipped - No existe registro en calculated_results", 'yellow');
                    $skipped++;
                    continue;
                }

                // Comparar valores
                $oldPuntaje = $existingResult['estres_total_puntaje'] ?? 0;
                $newPuntaje = $resultado['puntajeTransformado'];
                $diff = abs($newPuntaje - $oldPuntaje);

                // Actualizar si hay diferencia
                if ($diff > 0.01) {
                    $calculatedResultModel->update($existingResult['id'], [
                        'estres_total_puntaje' => $newPuntaje,
                        'estres_total_nivel' => $resultado['nivelRiesgo'],
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);

                    CLI::write("  ✓ Updated - Puntaje: {$oldPuntaje} → {$newPuntaje} (diff: " . round($diff, 2) . ") - Nivel: {$resultado['nivelRiesgo']}", 'green');
                    $updated++;
                } else {
                    CLI::write("  ○ No change - Puntaje: {$newPuntaje} - Nivel: {$resultado['nivelRiesgo']}", 'white');
                }

                $processed++;

            } catch (\Exception $e) {
                CLI::error("  ✗ Error: " . $e->getMessage());
                $errors++;
            }

            CLI::newLine();
        }

        // Resumen final
        CLI::newLine();
        CLI::write('╔═══════════════════════════════════════════════════════════════════════╗', 'yellow');
        CLI::write('║  RESUMEN                                                              ║', 'yellow');
        CLI::write('╚═══════════════════════════════════════════════════════════════════════╝', 'yellow');
        CLI::newLine();

        CLI::write("Total workers procesados: {$processed}", 'green');
        CLI::write("Actualizados (con cambios): {$updated}", $updated > 0 ? 'green' : 'white');
        CLI::write("Sin cambios: " . ($processed - $updated), 'white');
        CLI::write("Skipped (sin respuestas o incompletos): {$skipped}", 'yellow');
        CLI::write("Errores: {$errors}", $errors > 0 ? 'red' : 'white');

        CLI::newLine();
        if ($updated > 0) {
            CLI::write('✓ Recálculo completado. Se actualizaron ' . $updated . ' workers.', 'green');
            CLI::write('  Ejecutar el módulo de validación para verificar coherencia.', 'cyan');
        } else {
            CLI::write('✓ Recálculo completado. No hubo cambios necesarios.', 'green');
        }

        CLI::newLine();
    }
}
