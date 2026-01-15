<?php

namespace App\Controllers;

use App\Models\ActionPlanModel;
use App\Models\CalculatedResultModel;
use App\Models\MaxRiskResultModel;
use App\Models\BatteryServiceModel;

class RecommendationsController extends BaseController
{
    protected $actionPlanModel;
    protected $resultModel;
    protected $maxRiskModel;
    protected $batteryServiceModel;

    public function __construct()
    {
        $this->actionPlanModel = new ActionPlanModel();
        $this->resultModel = new CalculatedResultModel();
        $this->maxRiskModel = new MaxRiskResultModel();
        $this->batteryServiceModel = new BatteryServiceModel();
    }

    /**
     * Show action plan for a specific dimension
     *
     * @param string $dimensionCode Código de la dimensión
     * @param int $workerId ID del trabajador (opcional, para personalización)
     */
    public function view($dimensionCode, $workerId = null)
    {
        // Get action plan for dimension
        $actionPlan = $this->actionPlanModel->getByDimension($dimensionCode);

        if (!$actionPlan) {
            return redirect()->back()->with('error', 'Plan de acción no encontrado');
        }

        // JSON fields are automatically decoded by the model's $casts
        // No need to manually json_decode()

        // If worker ID provided, get their specific results
        $workerResults = null;
        if ($workerId) {
            $workerResults = $this->resultModel->where('worker_id', $workerId)->first();
        }

        return view('recommendations/action_plan', [
            'actionPlan' => $actionPlan,
            'workerResults' => $workerResults
        ]);
    }

    /**
     * Get all recommendations for a service based on risk levels
     * Shows dimensions with medium, high, or very high risk
     *
     * @param int $serviceId ID del servicio
     */
    public function forService($serviceId)
    {
        // Get all results for this service
        $results = $this->resultModel->getResultsByService($serviceId);

        if (empty($results)) {
            return redirect()->back()->with('error', 'No hay resultados disponibles para este servicio');
        }

        // Analyze which dimensions are in risk (medio, alto, muy_alto)
        $riskyDimensions = $this->identifyRiskyDimensions($results);

        // Get action plans for risky dimensions
        $actionPlans = [];
        foreach ($riskyDimensions as $dimension) {
            $plan = $this->actionPlanModel->getByDimension($dimension['code']);
            if ($plan) {
                $plan['risk_level'] = $dimension['level'];
                $plan['average_score'] = $dimension['average_score'];
                $plan['affected_workers'] = $dimension['count'];
                $actionPlans[] = $plan;
            }
        }

        return view('recommendations/service_recommendations', [
            'serviceId' => $serviceId,
            'actionPlans' => $actionPlans,
            'totalWorkers' => count($results)
        ]);
    }

    /**
     * Identify dimensions with medium/high/very high risk
     * Returns array of dimensions that need intervention
     */
    private function identifyRiskyDimensions($results)
    {
        $riskyDimensions = [];

        // List of dimension fields to check
        $dimensionsToCheck = [
            // Intralaboral dimensions (add all from calculated_results)
            'intralaboral_liderazgo' => 'Liderazgo y Relaciones Sociales',
            'intralaboral_control' => 'Control sobre el Trabajo',
            'intralaboral_demandas' => 'Demandas del Trabajo',
            'intralaboral_recompensas' => 'Recompensas',
            // Add more as needed
        ];

        foreach ($dimensionsToCheck as $fieldPrefix => $dimensionName) {
            $puntajeField = $fieldPrefix . '_puntaje';
            $nivelField = $fieldPrefix . '_nivel';

            $scores = [];
            $riskCount = 0;
            $totalCount = 0;

            foreach ($results as $result) {
                if (isset($result[$nivelField])) {
                    $nivel = $result[$nivelField];
                    $totalCount++;

                    // Count if risk is medium, high, or very high
                    if (in_array($nivel, ['riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'])) {
                        $riskCount++;
                        if (isset($result[$puntajeField])) {
                            $scores[] = $result[$puntajeField];
                        }
                    }
                }
            }

            // If 50% or more workers have medium/high/very high risk
            if ($riskCount > 0 && ($riskCount / $totalCount) >= 0.5) {
                $avgScore = !empty($scores) ? array_sum($scores) / count($scores) : 0;

                $riskyDimensions[] = [
                    'code' => str_replace('intralaboral_', '', $fieldPrefix),
                    'name' => $dimensionName,
                    'level' => $this->getMostCommonRiskLevel($results, $nivelField),
                    'average_score' => round($avgScore, 1),
                    'count' => $riskCount
                ];
            }
        }

        return $riskyDimensions;
    }

    /**
     * Get most common risk level for a dimension
     */
    private function getMostCommonRiskLevel($results, $nivelField)
    {
        $levels = [];
        foreach ($results as $result) {
            if (isset($result[$nivelField])) {
                $level = $result[$nivelField];
                // Accept both estrés format (medio, alto, muy_alto) and standard format (riesgo_medio, etc.)
                if (in_array($level, ['riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto', 'medio', 'alto', 'muy_alto'])) {
                    $levels[] = $level;
                }
            }
        }

        if (empty($levels)) {
            return 'riesgo_medio';
        }

        $counts = array_count_values($levels);
        arsort($counts);
        return key($counts);
    }

    /**
     * Get recommendation buttons for risky dimensions in a service
     * Returns HTML component that can be included in views
     *
     * @param int $serviceId ID del servicio (battery_service_id)
     * @return string HTML with recommendation buttons
     */
    public function getRecommendationButtons($serviceId)
    {
        // Get all risky dimensions from max_risk_results
        $riskyDimensions = $this->identifyAllRiskyDimensions($serviceId);

        if (empty($riskyDimensions)) {
            return '';
        }

        // Return view component
        return view('components/recommendation_buttons', [
            'riskyDimensions' => $riskyDimensions,
            'serviceId' => $serviceId
        ]);
    }

    /**
     * Identify ALL dimensions with risk from max_risk_results table
     * Returns detailed array with dimension codes for action plans
     *
     * @param int $batteryServiceId ID del servicio de batería
     * @return array Array de dimensiones en riesgo con datos de Forma A y B
     */
    private function identifyAllRiskyDimensions($batteryServiceId)
    {
        // Obtener todas las dimensiones y estrés total desde max_risk_results
        $maxRiskResults = $this->maxRiskModel
            ->where('battery_service_id', $batteryServiceId)
            ->groupStart()
                ->where('element_type', 'dimension')
                ->orWhere('element_code', 'estres_total')
            ->groupEnd()
            ->whereIn('worst_risk_level', ['riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto', 'medio', 'alto', 'muy_alto'])
            ->findAll();

        if (empty($maxRiskResults)) {
            return [];
        }

        $riskyDimensions = [];

        // Mapeo de element_code a action plan codes
        $codeMapping = [
            // INTRALABORAL
            'dim_caracteristicas_liderazgo' => 'caracteristicas_liderazgo',
            'dim_relaciones_sociales' => 'relaciones_sociales_trabajo',
            'dim_retroalimentacion' => 'retroalimentacion_desempeno',
            'dim_relacion_colaboradores' => 'relacion_colaboradores',
            'dim_claridad_rol' => 'claridad_rol',
            'dim_capacitacion' => 'capacitacion',
            'dim_participacion_manejo_cambio' => 'participacion_manejo_cambio',
            'dim_oportunidades_desarrollo' => 'oportunidades_desarrollo_habilidades',
            'dim_control_autonomia' => 'control_autonomia_trabajo',
            'dim_demandas_ambientales' => 'demandas_ambientales_esfuerzo_fisico',
            'dim_demandas_emocionales' => 'demandas_emocionales',
            'dim_demandas_cuantitativas' => 'demandas_cuantitativas',
            'dim_influencia_trabajo_entorno' => 'influencia_trabajo_entorno_extralaboral',
            'dim_demandas_responsabilidad' => 'exigencias_responsabilidad_cargo',
            'dim_carga_mental' => 'demandas_carga_mental',
            'dim_consistencia_rol' => 'consistencia_rol',
            'dim_demandas_jornada' => 'demandas_jornada_trabajo',
            'dim_recompensas_pertenencia' => 'recompensas_pertenencia_organizacion',
            'dim_reconocimiento_compensacion' => 'reconocimiento_compensacion',

            // EXTRALABORAL
            'dim_tiempo_fuera' => 'tiempo_fuera_trabajo',
            'dim_relaciones_familiares_extra' => 'relaciones_familiares',
            'dim_comunicacion' => 'comunicacion_relaciones_interpersonales',
            'dim_situacion_economica' => 'situacion_economica_familiar',
            'dim_caracteristicas_vivienda' => 'caracteristicas_vivienda_entorno',
            'dim_influencia_entorno_extra' => 'influencia_entorno_extralaboral',
            'dim_desplazamiento' => 'desplazamiento_vivienda_trabajo',

            // ESTRÉS
            'estres_total' => 'estres',
        ];

        foreach ($maxRiskResults as $result) {
            $elementCode = $result['element_code'];

            // Mapear a código de action plan
            $actionPlanCode = $codeMapping[$elementCode] ?? $elementCode;

            $riskyDimensions[] = [
                'code' => $actionPlanCode,
                'name' => $result['element_name'],
                'level' => $result['worst_risk_level'],
                'level_label' => $this->getRiskLevelLabel($result['worst_risk_level']),
                'level_color' => $this->getRiskLevelColor($result['worst_risk_level']),

                // Datos agregados (peor entre A y B)
                'worst_score' => round($result['worst_score'], 2),
                'worst_form' => $result['worst_form'],

                // Datos específicos por forma
                'form_a_score' => $result['form_a_score'] ? round($result['form_a_score'], 2) : null,
                'form_a_risk_level' => $result['form_a_risk_level'],
                'form_a_count' => $result['form_a_count'],

                'form_b_score' => $result['form_b_score'] ? round($result['form_b_score'], 2) : null,
                'form_b_risk_level' => $result['form_b_risk_level'],
                'form_b_count' => $result['form_b_count'],

                'has_both_forms' => $result['has_both_forms'],

                // Para compatibilidad con vista anterior
                'average_score' => round($result['worst_score'], 1),
                'affected_workers' => ($result['form_a_count'] ?? 0) + ($result['form_b_count'] ?? 0),
                'total_workers' => ($result['form_a_count'] ?? 0) + ($result['form_b_count'] ?? 0),
                'percentage' => 100 // Ya están en riesgo (filtrados por WHERE)
            ];
        }

        return $riskyDimensions;
    }

    /**
     * Get human-readable label for risk level
     */
    private function getRiskLevelLabel($level)
    {
        $labels = [
            'riesgo_medio' => 'RIESGO MEDIO',
            'riesgo_alto' => 'RIESGO ALTO',
            'riesgo_muy_alto' => 'RIESGO MUY ALTO',
            // Estrés levels
            'medio' => 'NIVEL MEDIO',
            'alto' => 'NIVEL ALTO',
            'muy_alto' => 'NIVEL MUY ALTO'
        ];
        return $labels[$level] ?? 'RIESGO';
    }

    /**
     * Get Bootstrap color class for risk level
     */
    private function getRiskLevelColor($level)
    {
        $colors = [
            'riesgo_medio' => 'warning',  // AMARILLO
            'riesgo_alto' => 'danger',    // ROJO
            'riesgo_muy_alto' => 'danger', // ROJO
            // Estrés levels
            'medio' => 'warning',  // AMARILLO
            'alto' => 'danger',    // ROJO
            'muy_alto' => 'danger' // ROJO
        ];
        return $colors[$level] ?? 'secondary';
    }
}
