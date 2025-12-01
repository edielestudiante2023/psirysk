<?php

namespace Tests\Regression;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Services;

/**
 * Test de Regresion de Baremos
 *
 * Objetivo: Capturar el estado actual de las clasificaciones y comparar
 * despues de migrar para detectar regresiones.
 *
 * FLUJO DE USO:
 * 1. ANTES de migrar: php vendor/bin/phpunit --filter testCreateSnapshot tests/regression/BaremosRegressionTest.php
 * 2. Realizar la migracion
 * 3. DESPUES de migrar: php vendor/bin/phpunit --filter testCompareWithSnapshot tests/regression/BaremosRegressionTest.php
 *
 * Ejecutar: php vendor/bin/phpunit tests/regression/BaremosRegressionTest.php
 */
class BaremosRegressionTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $DBGroup = 'default';
    protected $serviceId = 1; // ID del servicio de prueba - cambiar segun ambiente

    /**
     * Directorio donde se guardan los snapshots
     */
    protected $snapshotDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->snapshotDir = WRITEPATH . 'tests/snapshots/';

        if (!is_dir($this->snapshotDir)) {
            mkdir($this->snapshotDir, 0755, true);
        }
    }

    // =========================================================================
    // CREACION DE SNAPSHOTS (Ejecutar ANTES de migrar)
    // =========================================================================

    /**
     * TEST: Crear snapshot de resultados actuales
     * Ejecutar ANTES de migrar para capturar estado actual
     *
     * @group snapshot-create
     */
    public function testCreateSnapshot()
    {
        $db = \Config\Database::connect();

        // Obtener todos los trabajadores del servicio de prueba
        $workers = $db->table('workers')
            ->where('service_id', $this->serviceId)
            ->get()
            ->getResultArray();

        if (empty($workers)) {
            $this->markTestSkipped("No hay trabajadores en el servicio {$this->serviceId}");
        }

        $snapshot = [
            'metadata' => [
                'created_at' => date('Y-m-d H:i:s'),
                'service_id' => $this->serviceId,
                'total_workers' => count($workers),
                'description' => 'Snapshot pre-migracion de baremos'
            ],
            'workers' => []
        ];

        foreach ($workers as $worker) {
            $snapshot['workers'][$worker['id']] = [
                // Intralaboral
                'intralaboral_total_puntaje' => $worker['intralaboral_total_puntaje'] ?? null,
                'intralaboral_total_nivel' => $worker['intralaboral_total_nivel'] ?? null,
                'dom_liderazgo_puntaje' => $worker['dom_liderazgo_puntaje'] ?? null,
                'dom_liderazgo_nivel' => $worker['dom_liderazgo_nivel'] ?? null,
                'dom_control_puntaje' => $worker['dom_control_puntaje'] ?? null,
                'dom_control_nivel' => $worker['dom_control_nivel'] ?? null,
                'dom_demandas_puntaje' => $worker['dom_demandas_puntaje'] ?? null,
                'dom_demandas_nivel' => $worker['dom_demandas_nivel'] ?? null,
                'dom_recompensas_puntaje' => $worker['dom_recompensas_puntaje'] ?? null,
                'dom_recompensas_nivel' => $worker['dom_recompensas_nivel'] ?? null,

                // Extralaboral
                'extralaboral_total_puntaje' => $worker['extralaboral_total_puntaje'] ?? null,
                'extralaboral_total_nivel' => $worker['extralaboral_total_nivel'] ?? null,

                // Estres
                'estres_puntaje' => $worker['estres_puntaje'] ?? null,
                'estres_nivel' => $worker['estres_nivel'] ?? null,

                // Metadatos
                'forma_type' => $worker['forma_type'] ?? null,
            ];
        }

        // Guardar snapshot con timestamp
        $filename = 'baremos_snapshot_' . date('Y-m-d_H-i-s') . '.json';
        $filepath = $this->snapshotDir . $filename;

        file_put_contents($filepath, json_encode($snapshot, JSON_PRETTY_PRINT));

        // Crear enlace simbolico al ultimo snapshot
        $latestPath = $this->snapshotDir . 'latest_snapshot.json';
        if (file_exists($latestPath)) {
            unlink($latestPath);
        }
        copy($filepath, $latestPath);

        $this->assertFileExists($filepath, "Snapshot creado en: {$filepath}");
        echo "\n✓ Snapshot creado: {$filename}\n";
        echo "  - Trabajadores: {$snapshot['metadata']['total_workers']}\n";
        echo "  - Servicio ID: {$this->serviceId}\n";
    }

    // =========================================================================
    // COMPARACION CON SNAPSHOTS (Ejecutar DESPUES de migrar)
    // =========================================================================

    /**
     * TEST: Comparar resultados actuales con snapshot
     * Ejecutar DESPUES de migrar para detectar regresiones
     *
     * @group snapshot-compare
     */
    public function testCompareWithSnapshot()
    {
        $latestPath = $this->snapshotDir . 'latest_snapshot.json';

        if (!file_exists($latestPath)) {
            $this->markTestSkipped('No hay snapshot para comparar. Ejecute testCreateSnapshot primero.');
        }

        $snapshot = json_decode(file_get_contents($latestPath), true);

        if (!$snapshot || empty($snapshot['workers'])) {
            $this->markTestSkipped('Snapshot vacio o invalido.');
        }

        $db = \Config\Database::connect();

        // Obtener datos actuales
        $currentWorkers = $db->table('workers')
            ->where('service_id', $snapshot['metadata']['service_id'])
            ->get()
            ->getResultArray();

        $discrepancias = [];

        foreach ($currentWorkers as $worker) {
            $workerId = $worker['id'];

            if (!isset($snapshot['workers'][$workerId])) {
                continue; // Trabajador nuevo, no estaba en el snapshot
            }

            $expected = $snapshot['workers'][$workerId];

            // Comparar niveles de riesgo (lo mas importante)
            $campos = [
                'intralaboral_total_nivel',
                'dom_liderazgo_nivel',
                'dom_control_nivel',
                'dom_demandas_nivel',
                'dom_recompensas_nivel',
                'extralaboral_total_nivel',
                'estres_nivel'
            ];

            foreach ($campos as $campo) {
                $valorEsperado = $expected[$campo] ?? null;
                $valorActual = $worker[$campo] ?? null;

                if ($valorEsperado !== $valorActual) {
                    $discrepancias[] = [
                        'worker_id' => $workerId,
                        'campo' => $campo,
                        'esperado' => $valorEsperado,
                        'actual' => $valorActual
                    ];
                }
            }
        }

        // Reportar discrepancias
        if (!empty($discrepancias)) {
            echo "\n\n❌ DISCREPANCIAS ENCONTRADAS:\n";
            echo str_repeat('=', 80) . "\n";

            foreach ($discrepancias as $d) {
                echo "Worker {$d['worker_id']}: {$d['campo']}\n";
                echo "  Esperado: {$d['esperado']}\n";
                echo "  Actual:   {$d['actual']}\n";
                echo str_repeat('-', 40) . "\n";
            }

            $this->fail("Se encontraron " . count($discrepancias) . " discrepancias. Ver detalles arriba.");
        }

        echo "\n✓ Todos los niveles de riesgo coinciden con el snapshot.\n";
        echo "  - Trabajadores comparados: " . count($currentWorkers) . "\n";
        $this->assertTrue(true);
    }

    /**
     * TEST: Comparar puntajes (mas estricto)
     *
     * @group snapshot-compare-strict
     */
    public function testCompareWithSnapshotStrict()
    {
        $latestPath = $this->snapshotDir . 'latest_snapshot.json';

        if (!file_exists($latestPath)) {
            $this->markTestSkipped('No hay snapshot para comparar.');
        }

        $snapshot = json_decode(file_get_contents($latestPath), true);
        $db = \Config\Database::connect();

        $currentWorkers = $db->table('workers')
            ->where('service_id', $snapshot['metadata']['service_id'])
            ->get()
            ->getResultArray();

        $discrepancias = [];
        $tolerancia = 0.01; // Tolerancia para comparacion de floats

        foreach ($currentWorkers as $worker) {
            $workerId = $worker['id'];

            if (!isset($snapshot['workers'][$workerId])) {
                continue;
            }

            $expected = $snapshot['workers'][$workerId];

            // Comparar puntajes (con tolerancia para floats)
            $camposPuntaje = [
                'intralaboral_total_puntaje',
                'dom_liderazgo_puntaje',
                'dom_control_puntaje',
                'dom_demandas_puntaje',
                'dom_recompensas_puntaje',
                'extralaboral_total_puntaje',
                'estres_puntaje'
            ];

            foreach ($camposPuntaje as $campo) {
                $valorEsperado = floatval($expected[$campo] ?? 0);
                $valorActual = floatval($worker[$campo] ?? 0);

                if (abs($valorEsperado - $valorActual) > $tolerancia) {
                    $discrepancias[] = [
                        'worker_id' => $workerId,
                        'campo' => $campo,
                        'esperado' => $valorEsperado,
                        'actual' => $valorActual,
                        'diferencia' => abs($valorEsperado - $valorActual)
                    ];
                }
            }
        }

        if (!empty($discrepancias)) {
            echo "\n\n❌ DISCREPANCIAS EN PUNTAJES:\n";
            foreach ($discrepancias as $d) {
                echo "Worker {$d['worker_id']}: {$d['campo']} - Diferencia: {$d['diferencia']}\n";
            }
            $this->fail("Se encontraron " . count($discrepancias) . " discrepancias en puntajes.");
        }

        $this->assertTrue(true);
    }

    // =========================================================================
    // UTILIDADES
    // =========================================================================

    /**
     * TEST: Listar snapshots disponibles
     *
     * @group snapshot-list
     */
    public function testListSnapshots()
    {
        $files = glob($this->snapshotDir . 'baremos_snapshot_*.json');

        echo "\n\nSnapshots disponibles:\n";
        echo str_repeat('=', 60) . "\n";

        if (empty($files)) {
            echo "No hay snapshots guardados.\n";
            $this->markTestSkipped('No hay snapshots.');
        }

        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            $filename = basename($file);
            $created = $data['metadata']['created_at'] ?? 'Unknown';
            $workers = $data['metadata']['total_workers'] ?? 0;

            echo "{$filename}\n";
            echo "  Creado: {$created}\n";
            echo "  Trabajadores: {$workers}\n";
            echo str_repeat('-', 40) . "\n";
        }

        $this->assertTrue(true);
    }

    /**
     * TEST: Limpiar snapshots antiguos (mantener ultimos 5)
     *
     * @group snapshot-cleanup
     */
    public function testCleanupOldSnapshots()
    {
        $files = glob($this->snapshotDir . 'baremos_snapshot_*.json');

        if (count($files) <= 5) {
            echo "\n✓ No hay snapshots antiguos para limpiar.\n";
            $this->assertTrue(true);
            return;
        }

        // Ordenar por fecha de modificacion
        usort($files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        // Eliminar todos menos los ultimos 5
        $toDelete = array_slice($files, 5);
        $deleted = 0;

        foreach ($toDelete as $file) {
            if (unlink($file)) {
                $deleted++;
            }
        }

        echo "\n✓ Eliminados {$deleted} snapshots antiguos.\n";
        $this->assertTrue(true);
    }

    /**
     * TEST: Generar reporte de comparacion detallado
     *
     * @group snapshot-report
     */
    public function testGenerateComparisonReport()
    {
        $latestPath = $this->snapshotDir . 'latest_snapshot.json';

        if (!file_exists($latestPath)) {
            $this->markTestSkipped('No hay snapshot para generar reporte.');
        }

        $snapshot = json_decode(file_get_contents($latestPath), true);
        $db = \Config\Database::connect();

        $currentWorkers = $db->table('workers')
            ->where('service_id', $snapshot['metadata']['service_id'])
            ->get()
            ->getResultArray();

        $report = [
            'generated_at' => date('Y-m-d H:i:s'),
            'snapshot_created_at' => $snapshot['metadata']['created_at'],
            'service_id' => $snapshot['metadata']['service_id'],
            'summary' => [
                'total_workers_snapshot' => count($snapshot['workers']),
                'total_workers_current' => count($currentWorkers),
                'niveles_coinciden' => 0,
                'niveles_difieren' => 0,
                'puntajes_coinciden' => 0,
                'puntajes_difieren' => 0
            ],
            'discrepancias' => []
        ];

        foreach ($currentWorkers as $worker) {
            $workerId = $worker['id'];

            if (!isset($snapshot['workers'][$workerId])) {
                continue;
            }

            $expected = $snapshot['workers'][$workerId];

            // Comparar todos los campos
            foreach ($expected as $campo => $valorEsperado) {
                $valorActual = $worker[$campo] ?? null;

                if (strpos($campo, '_nivel') !== false) {
                    // Es un nivel
                    if ($valorEsperado === $valorActual) {
                        $report['summary']['niveles_coinciden']++;
                    } else {
                        $report['summary']['niveles_difieren']++;
                        $report['discrepancias'][] = [
                            'worker_id' => $workerId,
                            'campo' => $campo,
                            'tipo' => 'nivel',
                            'esperado' => $valorEsperado,
                            'actual' => $valorActual
                        ];
                    }
                } elseif (strpos($campo, '_puntaje') !== false) {
                    // Es un puntaje
                    if (abs(floatval($valorEsperado) - floatval($valorActual)) < 0.01) {
                        $report['summary']['puntajes_coinciden']++;
                    } else {
                        $report['summary']['puntajes_difieren']++;
                    }
                }
            }
        }

        // Guardar reporte
        $reportPath = $this->snapshotDir . 'comparison_report_' . date('Y-m-d_H-i-s') . '.json';
        file_put_contents($reportPath, json_encode($report, JSON_PRETTY_PRINT));

        // Mostrar resumen
        echo "\n\n=== REPORTE DE COMPARACION ===\n";
        echo "Snapshot: {$report['snapshot_created_at']}\n";
        echo "Actual:   {$report['generated_at']}\n";
        echo str_repeat('-', 40) . "\n";
        echo "Niveles que coinciden: {$report['summary']['niveles_coinciden']}\n";
        echo "Niveles que difieren:  {$report['summary']['niveles_difieren']}\n";
        echo "Puntajes que coinciden: {$report['summary']['puntajes_coinciden']}\n";
        echo "Puntajes que difieren:  {$report['summary']['puntajes_difieren']}\n";
        echo str_repeat('=', 40) . "\n";
        echo "Reporte guardado en: {$reportPath}\n";

        $this->assertTrue(true);
    }
}
