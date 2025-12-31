<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\CalculationService;
use App\Models\WorkerModel;

class RecalculateAllWorkers extends BaseCommand
{
    protected $group       = 'Workers';
    protected $name        = 'workers:recalculate-all';
    protected $description = 'Recalcula los resultados de TODOS los trabajadores completados';
    protected $usage       = 'workers:recalculate-all [service_id]';
    protected $arguments   = [
        'service_id' => '[Opcional] ID del servicio. Si no se proporciona, recalcula todos los servicios'
    ];

    public function run(array $params)
    {
        $serviceId = $params[0] ?? null;

        CLI::write('╔════════════════════════════════════════════════════════════╗', 'yellow');
        CLI::write('║  RECÁLCULO MASIVO DE RESULTADOS                            ║', 'yellow');
        CLI::write('╚════════════════════════════════════════════════════════════╝', 'yellow');
        CLI::newLine();

        if ($serviceId) {
            CLI::write("Recalculando solo servicio ID: $serviceId", 'cyan');
        } else {
            CLI::write("Recalculando TODOS los servicios", 'cyan');
        }
        CLI::newLine();

        // Obtener workers
        $workerModel = new WorkerModel();
        $query = $workerModel->where('status', 'completado');

        if ($serviceId) {
            $query->where('battery_service_id', $serviceId);
        }

        $workers = $query->orderBy('id', 'ASC')->findAll();

        $total = count($workers);
        CLI::write("Workers a recalcular: $total", 'green');
        CLI::newLine();

        if ($total === 0) {
            CLI::error('No hay workers para recalcular');
            return;
        }

        // Confirmar
        $confirm = CLI::prompt('¿Deseas continuar? (yes/no)', ['yes', 'no']);
        if ($confirm !== 'yes') {
            CLI::write('Operación cancelada', 'yellow');
            return;
        }

        CLI::newLine();
        CLI::write('Iniciando recálculo...', 'green');
        CLI::newLine();

        $calculationService = new CalculationService();
        $success = 0;
        $errors = 0;

        foreach ($workers as $index => $worker) {
            $current = $index + 1;
            $workerId = $worker['id'];
            $workerName = $worker['name'];

            CLI::showProgress($current, $total);

            try {
                $result = $calculationService->recalculateResults($workerId);

                if ($result !== false) {
                    $success++;
                    CLI::write("  ✓ [{$current}/{$total}] Worker $workerId: $workerName", 'green');
                } else {
                    $errors++;
                    CLI::error("  ✗ [{$current}/{$total}] Worker $workerId: $workerName - Error en recálculo");
                }

            } catch (\Exception $e) {
                $errors++;
                CLI::error("  ✗ [{$current}/{$total}] Worker $workerId: $workerName - Exception: " . $e->getMessage());
            }
        }

        CLI::newLine(2);
        CLI::write('╔════════════════════════════════════════════════════════════╗', 'cyan');
        CLI::write('║  RESUMEN DEL RECÁLCULO                                     ║', 'cyan');
        CLI::write('╚════════════════════════════════════════════════════════════╝', 'cyan');
        CLI::write("Total procesados: $total", 'white');
        CLI::write("Exitosos: $success", 'green');
        CLI::write("Errores: $errors", $errors > 0 ? 'red' : 'white');
        CLI::newLine();
    }
}
