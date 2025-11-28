<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\CalculationService;

class RecalculateWorker extends BaseCommand
{
    protected $group       = 'Workers';
    protected $name        = 'worker:recalculate';
    protected $description = 'Recalcula los resultados de un trabajador específico';
    protected $usage       = 'worker:recalculate <worker_id>';
    protected $arguments   = [
        'worker_id' => 'ID del trabajador a recalcular'
    ];

    public function run(array $params)
    {
        $workerId = $params[0] ?? null;

        if (!$workerId) {
            CLI::error('Debes proporcionar el ID del trabajador');
            CLI::write('Uso: php spark worker:recalculate <worker_id>');
            return;
        }

        CLI::write("Recalculando resultados para trabajador ID: $workerId", 'yellow');
        CLI::newLine();

        try {
            $calculationService = new CalculationService();
            $result = $calculationService->recalculateResults($workerId);

            if ($result !== false) {
                CLI::write('✓ Recálculo exitoso', 'green');
                CLI::newLine();

                // Mostrar dimensiones de recompensas
                $db = \Config\Database::connect();
                $calculated = $db->table('calculated_results')
                    ->where('worker_id', $workerId)
                    ->get()
                    ->getRowArray();

                CLI::write('=== DIMENSIONES DE RECOMPENSAS ===', 'cyan');
                CLI::write(sprintf('%-55s: %10s',
                    'Recompensas pertenencia',
                    $calculated['dim_recompensas_pertenencia_puntaje'] ?? 'NULL'
                ));
                CLI::write(sprintf('%-55s: %10s ⭐',
                    'Reconocimiento y compensación',
                    $calculated['dim_reconocimiento_compensacion_puntaje'] ?? 'NULL'
                ));
                CLI::write(sprintf('%-55s: %10s',
                    'Dominio Recompensas',
                    $calculated['dom_recompensas_puntaje'] ?? 'NULL'
                ));

            } else {
                CLI::error('Error en el recálculo');
            }

        } catch (\Exception $e) {
            CLI::error('Excepción: ' . $e->getMessage());
            CLI::write($e->getTraceAsString());
        }
    }
}
