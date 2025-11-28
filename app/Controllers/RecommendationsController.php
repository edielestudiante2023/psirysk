<?php

namespace App\Controllers;

use App\Models\ActionPlanModel;
use App\Models\CalculatedResultModel;

class RecommendationsController extends BaseController
{
    protected $actionPlanModel;
    protected $resultModel;

    public function __construct()
    {
        $this->actionPlanModel = new ActionPlanModel();
        $this->resultModel = new CalculatedResultModel();
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
     * @param int $serviceId ID del servicio
     * @return string HTML with recommendation buttons
     */
    public function getRecommendationButtons($serviceId)
    {
        // Get all results for this service
        $results = $this->resultModel->getResultsByService($serviceId);

        if (empty($results)) {
            return '';
        }

        // Get all risky dimensions
        $riskyDimensions = $this->identifyAllRiskyDimensions($results);

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
     * Identify ALL 27 dimensions with risk (not just domains)
     * Returns detailed array with dimension codes for action plans
     */
    private function identifyAllRiskyDimensions($results)
    {
        $riskyDimensions = [];

        // Map calculated_results fields to action plan dimension codes
        // IMPORTANT: Field prefixes MUST match exact database column names (without _puntaje/_nivel suffix)
        $dimensionMapping = [
            // INTRALABORAL - Liderazgo y Relaciones Sociales (4 dimensiones)
            'dim_caracteristicas_liderazgo' => ['code' => 'caracteristicas_liderazgo', 'name' => 'Características del Liderazgo'],
            'dim_relaciones_sociales' => ['code' => 'relaciones_sociales_trabajo', 'name' => 'Relaciones Sociales en el Trabajo'],
            'dim_retroalimentacion' => ['code' => 'retroalimentacion_desempeno', 'name' => 'Retroalimentación del Desempeño'],
            'dim_relacion_colaboradores' => ['code' => 'relacion_colaboradores', 'name' => 'Relación con los Colaboradores'],

            // INTRALABORAL - Control sobre el Trabajo (5 dimensiones)
            'dim_claridad_rol' => ['code' => 'claridad_rol', 'name' => 'Claridad del Rol'],
            'dim_capacitacion' => ['code' => 'capacitacion', 'name' => 'Capacitación'],
            'dim_participacion_manejo_cambio' => ['code' => 'participacion_manejo_cambio', 'name' => 'Participación y Manejo del Cambio'],
            'dim_oportunidades_desarrollo' => ['code' => 'oportunidades_desarrollo_habilidades', 'name' => 'Oportunidades Desarrollo Habilidades'],
            'dim_control_autonomia' => ['code' => 'control_autonomia_trabajo', 'name' => 'Control y Autonomía'],

            // INTRALABORAL - Demandas de Trabajo (8 dimensiones)
            'dim_demandas_ambientales' => ['code' => 'demandas_ambientales_esfuerzo_fisico', 'name' => 'Demandas Ambientales y Esfuerzo Físico'],
            'dim_demandas_emocionales' => ['code' => 'demandas_emocionales', 'name' => 'Demandas Emocionales'],
            'dim_demandas_cuantitativas' => ['code' => 'demandas_cuantitativas', 'name' => 'Demandas Cuantitativas'],
            'dim_influencia_trabajo_entorno_extralaboral' => ['code' => 'influencia_trabajo_entorno_extralaboral', 'name' => 'Influencia Trabajo sobre Entorno'],
            'dim_demandas_responsabilidad' => ['code' => 'exigencias_responsabilidad_cargo', 'name' => 'Exigencias de Responsabilidad'],
            'dim_demandas_carga_mental' => ['code' => 'demandas_carga_mental', 'name' => 'Demandas de Carga Mental'],
            'dim_consistencia_rol' => ['code' => 'consistencia_rol', 'name' => 'Consistencia del Rol'],
            'dim_demandas_jornada_trabajo' => ['code' => 'demandas_jornada_trabajo', 'name' => 'Demandas Jornada Trabajo'],

            // INTRALABORAL - Recompensas (2 dimensiones)
            'dim_recompensas_pertenencia' => ['code' => 'recompensas_pertenencia_organizacion', 'name' => 'Recompensas Pertenencia'],
            'dim_reconocimiento_compensacion' => ['code' => 'reconocimiento_compensacion', 'name' => 'Reconocimiento y Compensación'],

            // EXTRALABORAL (7 dimensiones)
            'extralaboral_tiempo_fuera' => ['code' => 'tiempo_fuera_trabajo', 'name' => 'Tiempo fuera del trabajo'],
            'extralaboral_relaciones_familiares' => ['code' => 'relaciones_familiares', 'name' => 'Relaciones familiares'],
            'extralaboral_comunicacion' => ['code' => 'comunicacion_relaciones_interpersonales', 'name' => 'Comunicación Interpersonal'],
            'extralaboral_situacion_economica' => ['code' => 'situacion_economica_familiar', 'name' => 'Situación Económica Familiar'],
            'extralaboral_caracteristicas_vivienda' => ['code' => 'caracteristicas_vivienda_entorno', 'name' => 'Características Vivienda'],
            'extralaboral_influencia_entorno' => ['code' => 'influencia_entorno_extralaboral', 'name' => 'Influencia Entorno Extralaboral'],
            'extralaboral_desplazamiento' => ['code' => 'desplazamiento_vivienda_trabajo', 'name' => 'Desplazamiento Vivienda-Trabajo'],

            // ESTRÉS (1 dimensión)
            'estres_total' => ['code' => 'estres', 'name' => 'Estrés']
        ];

        foreach ($dimensionMapping as $fieldPrefix => $dimensionInfo) {
            $nivelField = $fieldPrefix . '_nivel';
            $puntajeField = $fieldPrefix . '_puntaje';

            $riskCount = 0;
            $totalCount = 0;
            $scores = [];

            foreach ($results as $result) {
                if (isset($result[$nivelField])) {
                    $nivel = $result[$nivelField];
                    $totalCount++;

                    // Count if AMARILLO (medio) or ROJO (alto, muy_alto)
                    // For estres: medio, alto, muy_alto (without "riesgo_" prefix)
                    // For others: riesgo_medio, riesgo_alto, riesgo_muy_alto
                    $isRisky = in_array($nivel, ['riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto', 'medio', 'alto', 'muy_alto']);

                    if ($isRisky) {
                        $riskCount++;
                        if (isset($result[$puntajeField])) {
                            $scores[] = $result[$puntajeField];
                        }
                    }
                }
            }

            // If dimension has risk (at least 1 worker in risk)
            if ($riskCount > 0) {
                $avgScore = !empty($scores) ? array_sum($scores) / count($scores) : 0;
                $mostCommonLevel = $this->getMostCommonRiskLevel($results, $nivelField);

                $riskyDimensions[] = [
                    'code' => $dimensionInfo['code'],
                    'name' => $dimensionInfo['name'],
                    'level' => $mostCommonLevel,
                    'level_label' => $this->getRiskLevelLabel($mostCommonLevel),
                    'level_color' => $this->getRiskLevelColor($mostCommonLevel),
                    'average_score' => round($avgScore, 1),
                    'affected_workers' => $riskCount,
                    'total_workers' => $totalCount,
                    'percentage' => round(($riskCount / $totalCount) * 100, 1)
                ];
            }
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
