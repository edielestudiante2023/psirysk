<?php

namespace App\Controllers\Pdf\Resultados\Recomendaciones;

use App\Controllers\Pdf\PdfBaseController;
use App\Models\CalculatedResultModel;
use App\Models\ActionPlanModel;

/**
 * Controller para la sección de Recomendaciones del informe PDF
 * Genera páginas de recomendaciones basadas en los planes de acción
 * para dimensiones con riesgo medio, alto o muy alto
 */
class RecomendacionesController extends PdfBaseController
{
    protected $calculatedResultModel;
    protected $actionPlanModel;

    /**
     * Mapeo de campos de calculated_results a códigos de action_plans
     */
    protected $dimensionMapping = [
        // INTRALABORAL - Liderazgo y Relaciones Sociales (4 dimensiones)
        'dim_caracteristicas_liderazgo' => 'caracteristicas_liderazgo',
        'dim_relaciones_sociales' => 'relaciones_sociales_trabajo',
        'dim_retroalimentacion' => 'retroalimentacion_desempeno',
        'dim_relacion_colaboradores' => 'relacion_colaboradores',

        // INTRALABORAL - Control sobre el Trabajo (5 dimensiones)
        'dim_claridad_rol' => 'claridad_rol',
        'dim_capacitacion' => 'capacitacion',
        'dim_participacion_manejo_cambio' => 'participacion_manejo_cambio',
        'dim_oportunidades_desarrollo' => 'oportunidades_desarrollo_habilidades',
        'dim_control_autonomia' => 'control_autonomia_trabajo',

        // INTRALABORAL - Demandas de Trabajo (8 dimensiones)
        'dim_demandas_ambientales' => 'demandas_ambientales_esfuerzo_fisico',
        'dim_demandas_emocionales' => 'demandas_emocionales',
        'dim_demandas_cuantitativas' => 'demandas_cuantitativas',
        'dim_influencia_trabajo_entorno_extralaboral' => 'influencia_trabajo_entorno_extralaboral',
        'dim_exigencias_responsabilidad' => 'exigencias_responsabilidad_cargo',
        'dim_demandas_carga_mental' => 'demandas_carga_mental',
        'dim_consistencia_rol' => 'consistencia_rol',
        'dim_demandas_jornada_trabajo' => 'demandas_jornada_trabajo',

        // INTRALABORAL - Recompensas (2 dimensiones)
        'dim_recompensas_pertenencia' => 'recompensas_pertenencia_organizacion',
        'dim_reconocimiento_compensacion' => 'reconocimiento_compensacion',

        // EXTRALABORAL (7 dimensiones)
        'ext_tiempo_fuera_trabajo' => 'tiempo_fuera_trabajo',
        'ext_relaciones_familiares' => 'relaciones_familiares',
        'ext_comunicacion_relaciones' => 'comunicacion_relaciones_interpersonales',
        'ext_situacion_economica' => 'situacion_economica_familiar',
        'ext_caracteristicas_vivienda' => 'caracteristicas_vivienda_entorno',
        'ext_influencia_entorno' => 'influencia_entorno_extralaboral',
        'ext_desplazamiento' => 'desplazamiento_vivienda_trabajo',

        // ESTRÉS
        'estres' => 'estres',
    ];

    public function __construct()
    {
        $this->calculatedResultModel = new CalculatedResultModel();
        $this->actionPlanModel = new ActionPlanModel();
    }

    /**
     * Renderiza las páginas de recomendaciones
     *
     * @param int $batteryServiceId
     * @param string|null $forma 'A', 'B' o null para ambas
     * @return string HTML de las páginas
     */
    public function render($batteryServiceId, $forma = null)
    {
        $this->initializeData($batteryServiceId);

        $formasDisponibles = $forma ? [strtoupper($forma)] : ['A', 'B'];

        // Identificar dimensiones con riesgo para cada forma
        $riskDimensionsByForma = [];

        foreach ($formasDisponibles as $f) {
            $results = $this->calculatedResultModel
                ->where('battery_service_id', $batteryServiceId)
                ->where('intralaboral_form_type', $f)
                ->findAll();

            if (!empty($results)) {
                $riskDimensionsByForma[$f] = $this->identifyRiskyDimensions($results);
            }
        }

        if (empty($riskDimensionsByForma)) {
            return '';
        }

        $html = '';

        // Agregar separador de sección
        $html .= $this->renderSectionSeparator();
        $html .= $this->pageBreak();

        // Generar páginas de recomendaciones por forma
        foreach (['A', 'B'] as $f) {
            if (!isset($riskDimensionsByForma[$f]) || empty($riskDimensionsByForma[$f])) {
                continue;
            }

            // Agrupar por tipo de cuestionario
            $byType = $this->groupByQuestionnaireType($riskDimensionsByForma[$f]);

            // Página resumen de recomendaciones para esta forma
            $html .= $this->renderView('pdf/recomendaciones/resumen_page', [
                'forma' => $f,
                'riskDimensions' => $riskDimensionsByForma[$f],
                'byType' => $byType,
            ]);
            $html .= $this->pageBreak();

            // Páginas de detalle por dimensión en riesgo alto/muy alto
            foreach ($riskDimensionsByForma[$f] as $dimData) {
                if (!in_array($dimData['nivel'], ['riesgo_alto', 'riesgo_muy_alto'])) {
                    continue;
                }

                $actionPlan = $this->actionPlanModel->getByDimension($dimData['action_plan_code']);
                if (!$actionPlan) {
                    continue;
                }

                $html .= $this->renderView('pdf/recomendaciones/detalle_page', [
                    'forma' => $f,
                    'dimension' => $dimData,
                    'actionPlan' => $actionPlan,
                ]);
                $html .= $this->pageBreak();
            }
        }

        return $html;
    }

    /**
     * Renderiza el separador de sección de recomendaciones
     */
    private function renderSectionSeparator()
    {
        return $this->renderView('pdf/recomendaciones/section_separator', [
            'titulo' => 'Recomendaciones y Planes de Acción',
        ]);
    }

    /**
     * Identifica dimensiones con riesgo medio, alto o muy alto
     */
    private function identifyRiskyDimensions($results)
    {
        $riskyDimensions = [];

        foreach ($this->dimensionMapping as $fieldPrefix => $actionPlanCode) {
            $nivelField = $fieldPrefix . '_nivel';
            $puntajeField = $fieldPrefix . '_puntaje';

            $riskCount = 0;
            $totalCount = 0;
            $scores = [];
            $levels = [];

            foreach ($results as $result) {
                $nivel = $result[$nivelField] ?? null;
                if ($nivel === null) {
                    continue;
                }

                $totalCount++;

                // Verificar si está en riesgo (medio, alto, muy alto)
                if (in_array($nivel, ['riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'])) {
                    $riskCount++;
                    if (isset($result[$puntajeField])) {
                        $scores[] = $result[$puntajeField];
                    }
                    $levels[] = $nivel;
                }
            }

            // Si hay trabajadores en riesgo, agregar a la lista
            if ($riskCount > 0 && $totalCount > 0) {
                $avgScore = !empty($scores) ? array_sum($scores) / count($scores) : 0;
                $mostCommonLevel = $this->getMostCommonLevel($levels);
                $percentage = round(($riskCount / $totalCount) * 100, 1);

                // Obtener nombre de la dimensión desde action_plans
                $actionPlan = $this->actionPlanModel->getByDimension($actionPlanCode);
                $dimensionName = $actionPlan['dimension_name'] ?? $actionPlanCode;
                $questionnaireType = $actionPlan['questionnaire_type'] ?? 'intralaboral';

                $riskyDimensions[] = [
                    'field_prefix' => $fieldPrefix,
                    'action_plan_code' => $actionPlanCode,
                    'nombre' => $dimensionName,
                    'questionnaire_type' => $questionnaireType,
                    'promedio' => round($avgScore, 2),
                    'nivel' => $mostCommonLevel,
                    'trabajadores_en_riesgo' => $riskCount,
                    'total_evaluados' => $totalCount,
                    'porcentaje_riesgo' => $percentage,
                ];
            }
        }

        // Ordenar por nivel de riesgo (muy alto primero) y luego por porcentaje
        usort($riskyDimensions, function ($a, $b) {
            $order = ['riesgo_muy_alto' => 0, 'riesgo_alto' => 1, 'riesgo_medio' => 2];
            $orderA = $order[$a['nivel']] ?? 3;
            $orderB = $order[$b['nivel']] ?? 3;

            if ($orderA !== $orderB) {
                return $orderA - $orderB;
            }

            return $b['porcentaje_riesgo'] - $a['porcentaje_riesgo'];
        });

        return $riskyDimensions;
    }

    /**
     * Obtiene el nivel de riesgo más común
     */
    private function getMostCommonLevel($levels)
    {
        if (empty($levels)) {
            return 'riesgo_medio';
        }

        $counts = array_count_values($levels);
        arsort($counts);
        return key($counts);
    }

    /**
     * Agrupa dimensiones en riesgo por tipo de cuestionario
     */
    private function groupByQuestionnaireType($dimensions)
    {
        $grouped = [
            'intralaboral' => [],
            'extralaboral' => [],
            'estres' => [],
        ];

        foreach ($dimensions as $dim) {
            $type = $dim['questionnaire_type'] ?? 'intralaboral';
            if (isset($grouped[$type])) {
                $grouped[$type][] = $dim;
            }
        }

        return $grouped;
    }

    /**
     * Preview de recomendaciones en navegador
     */
    public function preview($batteryServiceId, $forma = null)
    {
        $html = $this->render($batteryServiceId, $forma);

        $title = 'Preview: Recomendaciones';
        if ($forma) {
            $title .= ' - Forma ' . strtoupper($forma);
        }

        return view('pdf/_partials/preview_wrapper', [
            'content' => $html,
            'pageTitle' => $title,
            'batteryServiceId' => $batteryServiceId
        ]);
    }
}
