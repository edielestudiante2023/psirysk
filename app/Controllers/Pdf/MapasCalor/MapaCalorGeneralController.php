<?php

namespace App\Controllers\Pdf\MapasCalor;

use App\Controllers\Pdf\PdfBaseController;
use App\Models\CalculatedResultModel;

/**
 * Controller para el Mapa de Calor General del informe PDF
 */
class MapaCalorGeneralController extends PdfBaseController
{
    protected $calculatedResultModel;

    public function __construct()
    {
        $this->calculatedResultModel = new CalculatedResultModel();
    }

    /**
     * Renderiza la página del mapa de calor general
     *
     * @param int $batteryServiceId
     * @return string HTML del mapa de calor
     */
    public function render($batteryServiceId)
    {
        $this->initializeData($batteryServiceId);

        // Obtener resultados calculados
        $results = $this->calculatedResultModel
            ->select('calculated_results.*, workers.name, workers.document, workers.position, workers.area')
            ->join('workers', 'workers.id = calculated_results.worker_id')
            ->where('calculated_results.battery_service_id', $batteryServiceId)
            ->findAll();

        if (empty($results)) {
            return $this->renderView('pdf/mapas_calor/general', [
                'heatmapData' => null,
                'message' => 'No hay resultados calculados'
            ]);
        }

        // Calcular datos del mapa de calor
        $heatmapData = $this->calculateHeatmapData($results);

        return $this->renderView('pdf/mapas_calor/general', [
            'heatmapData' => $heatmapData,
            'totalWorkers' => count($results),
        ]);
    }

    /**
     * Calcula los datos para el mapa de calor general
     */
    private function calculateHeatmapData($results)
    {
        $data = [
            'intralaboral' => [
                'forma_a' => ['total' => 0, 'sin_riesgo' => 0, 'bajo' => 0, 'medio' => 0, 'alto' => 0, 'muy_alto' => 0],
                'forma_b' => ['total' => 0, 'sin_riesgo' => 0, 'bajo' => 0, 'medio' => 0, 'alto' => 0, 'muy_alto' => 0],
            ],
            'extralaboral' => [
                'forma_a' => ['total' => 0, 'sin_riesgo' => 0, 'bajo' => 0, 'medio' => 0, 'alto' => 0, 'muy_alto' => 0],
                'forma_b' => ['total' => 0, 'sin_riesgo' => 0, 'bajo' => 0, 'medio' => 0, 'alto' => 0, 'muy_alto' => 0],
            ],
            'estres' => [
                'forma_a' => ['total' => 0, 'muy_bajo' => 0, 'bajo' => 0, 'medio' => 0, 'alto' => 0, 'muy_alto' => 0],
                'forma_b' => ['total' => 0, 'muy_bajo' => 0, 'bajo' => 0, 'medio' => 0, 'alto' => 0, 'muy_alto' => 0],
            ],
            'workers' => [],
        ];

        foreach ($results as $result) {
            $forma = strtolower($result['intralaboral_form_type']) === 'a' ? 'forma_a' : 'forma_b';

            // Intralaboral
            $data['intralaboral'][$forma]['total']++;
            $intraLevel = $this->getRiskLevelFromScore($result['intralaboral_total'] ?? 0);
            $data['intralaboral'][$forma][$this->normalizeLevel($intraLevel)]++;

            // Extralaboral
            $data['extralaboral'][$forma]['total']++;
            $extraLevel = $this->getRiskLevelFromScore($result['extralaboral_total'] ?? 0);
            $data['extralaboral'][$forma][$this->normalizeLevel($extraLevel)]++;

            // Estrés
            $data['estres'][$forma]['total']++;
            $estresLevel = $this->getEstresLevel($result['estres_total'] ?? 0);
            $data['estres'][$forma][$this->normalizeEstresLevel($estresLevel)]++;

            // Datos individuales para la tabla detallada
            $data['workers'][] = [
                'name' => $result['name'],
                'document' => $result['document'],
                'position' => $result['position'] ?? '',
                'area' => $result['area'] ?? '',
                'forma' => $result['intralaboral_form_type'],
                'intralaboral' => $result['intralaboral_total'],
                'intralaboral_level' => $intraLevel,
                'extralaboral' => $result['extralaboral_total'],
                'extralaboral_level' => $extraLevel,
                'estres' => $result['estres_total'],
                'estres_level' => $estresLevel,
            ];
        }

        return $data;
    }

    /**
     * Normaliza el nivel de riesgo para las claves del array
     */
    private function normalizeLevel($level)
    {
        $map = [
            'sin_riesgo' => 'sin_riesgo',
            'riesgo_bajo' => 'bajo',
            'riesgo_medio' => 'medio',
            'riesgo_alto' => 'alto',
            'riesgo_muy_alto' => 'muy_alto',
        ];
        return $map[$level] ?? 'medio';
    }

    /**
     * Obtiene el nivel de estrés basado en el puntaje
     */
    private function getEstresLevel($score)
    {
        if ($score <= 7.8) return 'muy_bajo';
        if ($score <= 12.6) return 'bajo';
        if ($score <= 17.7) return 'medio';
        if ($score <= 25.0) return 'alto';
        return 'muy_alto';
    }

    /**
     * Normaliza el nivel de estrés
     */
    private function normalizeEstresLevel($level)
    {
        return $level; // Ya está normalizado
    }

    /**
     * Preview del mapa de calor en navegador
     */
    public function preview($batteryServiceId)
    {
        $html = $this->render($batteryServiceId);

        return view('pdf/_partials/preview_wrapper', [
            'content' => $html,
            'pageTitle' => 'Preview: Mapa de Calor General',
            'batteryServiceId' => $batteryServiceId
        ]);
    }
}
