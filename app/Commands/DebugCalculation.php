<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\CalculationService;
use App\Models\WorkerModel;
use App\Models\CalculatedResultModel;

class DebugCalculation extends BaseCommand
{
    protected $group = 'Debug';
    protected $name = 'debug:calculate';
    protected $description = 'Debug calculation for a specific worker or all failing workers';
    protected $usage = 'debug:calculate [workerId]';
    protected $arguments = [
        'workerId' => 'Worker ID to debug (optional, calculates all failing if not provided)',
    ];

    public function run(array $params)
    {
        $workerId = $params[0] ?? null;
        $workerModel = new WorkerModel();
        $resultModel = new CalculatedResultModel();
        $calculationService = new CalculationService();

        if ($workerId) {
            // Debug single worker
            $this->debugWorker((int)$workerId, $workerModel, $calculationService, $resultModel);
        } else {
            // Debug all failing workers in service 4
            $db = \Config\Database::connect();
            $results = $db->query("
                SELECT w.id, w.document, w.name, w.intralaboral_type, w.atiende_clientes, w.es_jefe
                FROM workers w
                LEFT JOIN calculated_results cr ON cr.worker_id = w.id
                WHERE w.battery_service_id = 4
                AND w.status = 'completado'
                AND cr.id IS NULL
            ")->getResultArray();

            CLI::write("=== " . count($results) . " WORKERS SIN RESULTADOS ===", 'yellow');

            foreach ($results as $row) {
                $this->debugWorker((int)$row['id'], $workerModel, $calculationService, $resultModel);
                CLI::write(str_repeat('-', 60));
            }
        }
    }

    private function debugWorker($workerId, $workerModel, $calculationService, $resultModel)
    {
        $worker = $workerModel->find($workerId);

        if (!$worker) {
            CLI::write("Worker $workerId no encontrado", 'red');
            return;
        }

        CLI::write("\n=== Worker ID: $workerId ===", 'cyan');
        CLI::write("Nombre: " . $worker['name']);
        CLI::write("Tipo: " . $worker['intralaboral_type']);
        CLI::write("atiende_clientes: " . var_export($worker['atiende_clientes'], true));
        CLI::write("es_jefe: " . var_export($worker['es_jefe'], true));

        // Verificar formularios completos
        $complete = $calculationService->allFormsComplete($workerId, $worker['intralaboral_type']);
        CLI::write("allFormsComplete: " . ($complete ? 'SI' : 'NO'), $complete ? 'green' : 'red');

        if (!$complete) {
            $info = $calculationService->getIncompleteFormsInfo($workerId, $worker['intralaboral_type']);
            foreach ($info as $item) {
                CLI::write("  - " . $item['message'], 'yellow');
            }
            return;
        }

        // Intentar calcular
        CLI::write("Intentando calcular...", 'white');
        try {
            $result = $calculationService->calculateAndSaveResults($workerId);
            if ($result) {
                CLI::write("EXITO - Calculado!", 'green');
                CLI::write("Intralaboral: " . ($result['intralaboral_total_puntaje'] ?? 'N/A') . " - " . ($result['intralaboral_total_nivel'] ?? 'N/A'));
            } else {
                CLI::write("FALLO - Retorno false", 'red');
            }
        } catch (\Throwable $e) {
            CLI::write("ERROR: " . $e->getMessage(), 'red');
            CLI::write("File: " . $e->getFile() . ":" . $e->getLine(), 'light_red');
        }

        // Verificar BD
        $exists = $resultModel->where('worker_id', $workerId)->countAllResults();
        CLI::write("En BD: " . ($exists ? 'SI' : 'NO'), $exists ? 'green' : 'red');
    }
}
