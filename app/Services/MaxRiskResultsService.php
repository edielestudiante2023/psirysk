<?php

namespace App\Services;

use App\Models\MaxRiskResultModel;
use App\Models\CalculatedResultModel;
use App\Libraries\IntralaboralAScoring;
use App\Libraries\IntralaboralBScoring;
use App\Libraries\ExtralaboralScoring;
use App\Libraries\EstresScoring;

/**
 * Servicio para calcular y almacenar los resultados de máximo riesgo
 * entre Forma A y Forma B para cada dominio y dimensión.
 *
 * Estos resultados alimentan el Módulo IA de Intervención y el Mapa de Calor.
 */
class MaxRiskResultsService
{
    protected $model;
    protected $calculatedResultModel;

    // Orden de niveles de riesgo (mayor = peor)
    private const RISK_ORDER = [
        'sin_riesgo'      => 0,
        'riesgo_bajo'     => 1,
        'riesgo_medio'    => 2,
        'riesgo_alto'     => 3,
        'riesgo_muy_alto' => 4,
        // Alias para estrés
        'muy_bajo'        => 0,
        'bajo'            => 1,
        'medio'           => 2,
        'alto'            => 3,
        'muy_alto'        => 4,
    ];

    // Prioridad de intervención (1 = más urgente)
    private const RISK_PRIORITY = [
        'riesgo_muy_alto' => 1,
        'muy_alto'        => 1,
        'riesgo_alto'     => 2,
        'alto'            => 2,
        'riesgo_medio'    => 3,
        'medio'           => 3,
        'riesgo_bajo'     => 4,
        'bajo'            => 4,
        'sin_riesgo'      => 5,
        'muy_bajo'        => 5,
    ];

    // Nombres legibles de elementos
    private const ELEMENT_NAMES = [
        // Totales
        'intralaboral_total'     => 'Total Intralaboral',
        'extralaboral_total'     => 'Total Extralaboral',
        'estres_total'           => 'Total Estrés',

        // Dominios
        'dom_liderazgo'          => 'Liderazgo y Relaciones Sociales en el Trabajo',
        'dom_control'            => 'Control sobre el Trabajo',
        'dom_demandas'           => 'Demandas del Trabajo',
        'dom_recompensas'        => 'Recompensas',

        // Dimensiones Intralaborales
        'dim_caracteristicas_liderazgo'          => 'Características del Liderazgo',
        'dim_relaciones_sociales'                => 'Relaciones Sociales en el Trabajo',
        'dim_retroalimentacion'                  => 'Retroalimentación del Desempeño',
        'dim_relacion_colaboradores'             => 'Relación con los Colaboradores',
        'dim_claridad_rol'                       => 'Claridad de Rol',
        'dim_capacitacion'                       => 'Capacitación',
        'dim_participacion_manejo_cambio'        => 'Participación y Manejo del Cambio',
        'dim_oportunidades_desarrollo'           => 'Oportunidades para el Uso y Desarrollo de Habilidades',
        'dim_control_autonomia'                  => 'Control y Autonomía sobre el Trabajo',
        'dim_demandas_ambientales'               => 'Demandas Ambientales y de Esfuerzo Físico',
        'dim_demandas_emocionales'               => 'Demandas Emocionales',
        'dim_demandas_cuantitativas'             => 'Demandas Cuantitativas',
        'dim_influencia_trabajo_entorno'         => 'Influencia del Trabajo sobre el Entorno Extralaboral',
        'dim_demandas_responsabilidad'           => 'Exigencias de Responsabilidad del Cargo',
        'dim_carga_mental'                       => 'Demandas de Carga Mental',
        'dim_consistencia_rol'                   => 'Consistencia del Rol',
        'dim_demandas_jornada'                   => 'Demandas de la Jornada de Trabajo',
        'dim_recompensas_pertenencia'            => 'Recompensas Derivadas de la Pertenencia',
        'dim_reconocimiento_compensacion'        => 'Reconocimiento y Compensación',

        // Dimensiones Extralaborales
        'dim_tiempo_fuera'                       => 'Tiempo Fuera del Trabajo',
        'dim_relaciones_familiares_extra'        => 'Relaciones Familiares',
        'dim_comunicacion'                       => 'Comunicación y Relaciones Interpersonales',
        'dim_situacion_economica'                => 'Situación Económica del Grupo Familiar',
        'dim_caracteristicas_vivienda'           => 'Características de la Vivienda y de su Entorno',
        'dim_influencia_entorno_extra'           => 'Influencia del Entorno Extralaboral sobre el Trabajo',
        'dim_desplazamiento'                     => 'Desplazamiento Vivienda - Trabajo - Vivienda',
    ];

    public function __construct()
    {
        $this->model = new MaxRiskResultModel();
        $this->calculatedResultModel = new CalculatedResultModel();
    }

    /**
     * Calcula y almacena los máximos riesgos para un servicio de batería
     *
     * @param int $batteryServiceId ID del servicio
     * @param bool $forceRecalculate Si true, elimina resultados existentes y recalcula
     * @return array Resumen del cálculo
     */
    public function calculateAndStore(int $batteryServiceId, bool $forceRecalculate = false): array
    {
        // Verificar si ya existen resultados
        if (!$forceRecalculate && $this->model->existsForBatteryService($batteryServiceId)) {
            return [
                'status'  => 'exists',
                'message' => 'Los resultados ya existen. Use forceRecalculate=true para recalcular.',
                'count'   => $this->model->where('battery_service_id', $batteryServiceId)->countAllResults(),
            ];
        }

        // Eliminar resultados existentes si es recálculo
        if ($forceRecalculate) {
            $this->model->deleteByBatteryService($batteryServiceId);
        }

        // Obtener resultados calculados
        $results = $this->calculatedResultModel
            ->where('battery_service_id', $batteryServiceId)
            ->findAll();

        if (empty($results)) {
            return [
                'status'  => 'error',
                'message' => 'No hay resultados calculados para este servicio.',
                'count'   => 0,
            ];
        }

        // Separar por forma
        $resultsA = array_filter($results, fn($r) => $r['intralaboral_form_type'] === 'A');
        $resultsB = array_filter($results, fn($r) => $r['intralaboral_form_type'] === 'B');

        $countA = count($resultsA);
        $countB = count($resultsB);
        $hasFormaA = $countA > 0;
        $hasFormaB = $countB > 0;
        $hasBothForms = $hasFormaA && $hasFormaB;

        // Cargar baremos
        $baremos = $this->loadBaremos();

        // Definir todos los elementos a calcular
        $elementsToCalculate = $this->getElementsDefinition($baremos);

        // Calcular y almacenar cada elemento
        $insertedCount = 0;
        foreach ($elementsToCalculate as $elementCode => $config) {
            $worst = $this->calculateWorstResult(
                $config['field'],
                $config['baremo_a'],
                $config['baremo_b'],
                $resultsA,
                $resultsB,
                $hasFormaA,
                $hasFormaB,
                $hasBothForms
            );

            if ($worst === null || $worst['promedio'] === 0 && $worst['cantidad'] === 0) {
                continue; // Saltar si no hay datos
            }

            $data = [
                'battery_service_id' => $batteryServiceId,
                'element_type'       => $config['type'],
                'questionnaire_type' => $config['questionnaire'],
                'element_code'       => $elementCode,
                'element_name'       => self::ELEMENT_NAMES[$elementCode] ?? $elementCode,
                'worst_score'        => $worst['promedio'],
                'worst_risk_level'   => $worst['nivel'],
                'worst_form'         => $worst['forma_origen'] ?? ($hasFormaA ? 'A' : 'B'),
                'form_a_score'       => $worst['data_a']['promedio'] ?? null,
                'form_a_risk_level'  => $worst['data_a']['nivel'] ?? null,
                'form_a_count'       => $countA,
                'form_b_score'       => $worst['data_b']['promedio'] ?? null,
                'form_b_risk_level'  => $worst['data_b']['nivel'] ?? null,
                'form_b_count'       => $countB,
                'has_both_forms'     => $hasBothForms,
                'risk_priority'      => self::RISK_PRIORITY[$worst['nivel']] ?? 5,
            ];

            $this->model->insert($data);
            $insertedCount++;
        }

        return [
            'status'        => 'success',
            'message'       => "Se calcularon y almacenaron $insertedCount elementos.",
            'count'         => $insertedCount,
            'count_a'       => $countA,
            'count_b'       => $countB,
            'has_both_forms' => $hasBothForms,
        ];
    }

    /**
     * Obtiene los resultados de máximo riesgo para un servicio
     */
    public function getByBatteryService(int $batteryServiceId): array
    {
        return $this->model->getByBatteryService($batteryServiceId);
    }

    /**
     * Obtiene solo los elementos con riesgo alto o muy alto (para IA)
     */
    public function getHighRiskElements(int $batteryServiceId): array
    {
        return $this->model->getHighRiskElements($batteryServiceId);
    }

    /**
     * Obtiene resultados formateados para el heatmap (compatible con la vista actual)
     */
    public function getHeatmapData(int $batteryServiceId): ?array
    {
        $results = $this->model->getByBatteryService($batteryServiceId);

        if (empty($results)) {
            return null;
        }

        // Convertir a formato compatible con heatmap_detail.php
        $heatmap = [
            'has_forma_a'     => false,
            'has_forma_b'     => false,
            'has_both_forms'  => false,
            'count_a'         => 0,
            'count_b'         => 0,
        ];

        foreach ($results as $result) {
            $code = $result['element_code'];

            // Metadata de formas (solo necesitamos tomarlo una vez)
            if ($heatmap['count_a'] === 0) {
                $heatmap['count_a'] = $result['form_a_count'] ?? 0;
                $heatmap['count_b'] = $result['form_b_count'] ?? 0;
                $heatmap['has_forma_a'] = $heatmap['count_a'] > 0;
                $heatmap['has_forma_b'] = $heatmap['count_b'] > 0;
                $heatmap['has_both_forms'] = $result['has_both_forms'];
            }

            // Construir estructura compatible
            $heatmap[$code] = [
                'promedio'       => (float)$result['worst_score'],
                'nivel'          => $result['worst_risk_level'],
                'forma_origen'   => $result['worst_form'],
                'solo_una_forma' => !$result['has_both_forms'],
                'data_a'         => $result['form_a_score'] ? [
                    'promedio' => (float)$result['form_a_score'],
                    'nivel'    => $result['form_a_risk_level'],
                ] : null,
                'data_b'         => $result['form_b_score'] ? [
                    'promedio' => (float)$result['form_b_score'],
                    'nivel'    => $result['form_b_risk_level'],
                ] : null,
            ];
        }

        return $heatmap;
    }

    /**
     * Actualiza el análisis de IA para un elemento
     */
    public function updateAiAnalysis(int $id, string $analysis, ?string $recommendations = null, ?string $modelVersion = null): bool
    {
        return $this->model->updateAiAnalysis($id, $analysis, $recommendations, $modelVersion);
    }

    /**
     * Obtiene estadísticas de riesgo
     */
    public function getRiskStats(int $batteryServiceId): array
    {
        return $this->model->getRiskStats($batteryServiceId);
    }

    /**
     * Verifica si existen resultados para un servicio
     */
    public function existsForBatteryService(int $batteryServiceId): bool
    {
        return $this->model->existsForBatteryService($batteryServiceId);
    }

    /**
     * Elimina todos los resultados de un servicio
     */
    public function deleteByBatteryService(int $batteryServiceId): bool
    {
        return $this->model->deleteByBatteryService($batteryServiceId);
    }

    // ============================================
    // MÉTODOS PRIVADOS
    // ============================================

    /**
     * Carga todos los baremos necesarios
     */
    private function loadBaremos(): array
    {
        return [
            'A' => [
                'intralaboral_total' => IntralaboralAScoring::getBaremoTotal(),
                'dominios' => [
                    'liderazgo'   => IntralaboralAScoring::getBaremoDominio('liderazgo_relaciones_sociales'),
                    'control'     => IntralaboralAScoring::getBaremoDominio('control'),
                    'demandas'    => IntralaboralAScoring::getBaremoDominio('demandas'),
                    'recompensas' => IntralaboralAScoring::getBaremoDominio('recompensas'),
                ],
                'dimensiones_intra' => [
                    'caracteristicas_liderazgo'   => IntralaboralAScoring::getBaremoDimension('caracteristicas_liderazgo'),
                    'relaciones_sociales'         => IntralaboralAScoring::getBaremoDimension('relaciones_sociales_trabajo'),
                    'retroalimentacion'           => IntralaboralAScoring::getBaremoDimension('retroalimentacion_desempeno'),
                    'relacion_colaboradores'      => IntralaboralAScoring::getBaremoDimension('relacion_con_colaboradores'),
                    'claridad_rol'                => IntralaboralAScoring::getBaremoDimension('claridad_rol'),
                    'capacitacion'                => IntralaboralAScoring::getBaremoDimension('capacitacion'),
                    'participacion_cambio'        => IntralaboralAScoring::getBaremoDimension('participacion_manejo_cambio'),
                    'oportunidades_desarrollo'    => IntralaboralAScoring::getBaremoDimension('oportunidades_desarrollo'),
                    'control_autonomia'           => IntralaboralAScoring::getBaremoDimension('control_autonomia_trabajo'),
                    'demandas_ambientales'        => IntralaboralAScoring::getBaremoDimension('demandas_ambientales_esfuerzo_fisico'),
                    'demandas_emocionales'        => IntralaboralAScoring::getBaremoDimension('demandas_emocionales'),
                    'demandas_cuantitativas'      => IntralaboralAScoring::getBaremoDimension('demandas_cuantitativas'),
                    'influencia_entorno'          => IntralaboralAScoring::getBaremoDimension('influencia_trabajo_entorno_extralaboral'),
                    'exigencias_responsabilidad'  => IntralaboralAScoring::getBaremoDimension('exigencias_responsabilidad_cargo'),
                    'carga_mental'                => IntralaboralAScoring::getBaremoDimension('demandas_carga_mental'),
                    'consistencia_rol'            => IntralaboralAScoring::getBaremoDimension('consistencia_rol'),
                    'demandas_jornada'            => IntralaboralAScoring::getBaremoDimension('demandas_jornada_trabajo'),
                    'recompensas_pertenencia'     => IntralaboralAScoring::getBaremoDimension('recompensas_pertenencia_estabilidad'),
                    'reconocimiento_compensacion' => IntralaboralAScoring::getBaremoDimension('reconocimiento_compensacion'),
                ],
                'extralaboral_total' => ExtralaboralScoring::getBaremoTotal('A'),
                'estres_total'       => EstresScoring::getBaremoA(),
            ],
            'B' => [
                'intralaboral_total' => IntralaboralBScoring::getBaremoTotal(),
                'dominios' => [
                    'liderazgo'   => IntralaboralBScoring::getBaremoDominio('liderazgo_relaciones_sociales'),
                    'control'     => IntralaboralBScoring::getBaremoDominio('control'),
                    'demandas'    => IntralaboralBScoring::getBaremoDominio('demandas'),
                    'recompensas' => IntralaboralBScoring::getBaremoDominio('recompensas'),
                ],
                'dimensiones_intra' => [
                    'caracteristicas_liderazgo'   => IntralaboralBScoring::getBaremoDimension('caracteristicas_liderazgo'),
                    'relaciones_sociales'         => IntralaboralBScoring::getBaremoDimension('relaciones_sociales_trabajo'),
                    'retroalimentacion'           => IntralaboralBScoring::getBaremoDimension('retroalimentacion_desempeno'),
                    'relacion_colaboradores'      => IntralaboralBScoring::getBaremoDimension('relacion_colaboradores'),
                    'claridad_rol'                => IntralaboralBScoring::getBaremoDimension('claridad_rol'),
                    'capacitacion'                => IntralaboralBScoring::getBaremoDimension('capacitacion'),
                    'participacion_cambio'        => IntralaboralBScoring::getBaremoDimension('participacion_manejo_cambio'),
                    'oportunidades_desarrollo'    => IntralaboralBScoring::getBaremoDimension('oportunidades_desarrollo'),
                    'control_autonomia'           => IntralaboralBScoring::getBaremoDimension('control_autonomia_trabajo'),
                    'demandas_ambientales'        => IntralaboralBScoring::getBaremoDimension('demandas_ambientales_esfuerzo_fisico'),
                    'demandas_emocionales'        => IntralaboralBScoring::getBaremoDimension('demandas_emocionales'),
                    'demandas_cuantitativas'      => IntralaboralBScoring::getBaremoDimension('demandas_cuantitativas'),
                    'influencia_entorno'          => IntralaboralBScoring::getBaremoDimension('influencia_trabajo_entorno_extralaboral'),
                    'carga_mental'                => IntralaboralBScoring::getBaremoDimension('demandas_carga_mental'),
                    'demandas_jornada'            => IntralaboralBScoring::getBaremoDimension('demandas_jornada_trabajo'),
                    'recompensas_pertenencia'     => IntralaboralBScoring::getBaremoDimension('recompensas_pertenencia_estabilidad'),
                    'reconocimiento_compensacion' => IntralaboralBScoring::getBaremoDimension('reconocimiento_compensacion'),
                ],
                'extralaboral_total' => ExtralaboralScoring::getBaremoTotal('B'),
                'estres_total'       => EstresScoring::getBaremoB(),
            ],
            'dimensiones_extra' => [
                'tiempo_fuera'             => ExtralaboralScoring::getBaremoDimension('tiempo_fuera_trabajo'),
                'relaciones_familiares'    => ExtralaboralScoring::getBaremoDimension('relaciones_familiares'),
                'comunicacion'             => ExtralaboralScoring::getBaremoDimension('comunicacion_relaciones'),
                'situacion_economica'      => ExtralaboralScoring::getBaremoDimension('situacion_economica'),
                'caracteristicas_vivienda' => ExtralaboralScoring::getBaremoDimension('caracteristicas_vivienda'),
                'influencia_entorno_extra' => ExtralaboralScoring::getBaremoDimension('influencia_entorno'),
                'desplazamiento'           => ExtralaboralScoring::getBaremoDimension('desplazamiento'),
            ],
        ];
    }

    /**
     * Define todos los elementos a calcular con sus configuraciones
     */
    private function getElementsDefinition(array $baremos): array
    {
        $elements = [];

        // === TOTALES ===
        $elements['intralaboral_total'] = [
            'type'          => 'total',
            'questionnaire' => 'intralaboral',
            'field'         => 'intralaboral_total_puntaje',
            'baremo_a'      => $baremos['A']['intralaboral_total'],
            'baremo_b'      => $baremos['B']['intralaboral_total'],
        ];
        $elements['extralaboral_total'] = [
            'type'          => 'total',
            'questionnaire' => 'extralaboral',
            'field'         => 'extralaboral_total_puntaje',
            'baremo_a'      => $baremos['A']['extralaboral_total'],
            'baremo_b'      => $baremos['B']['extralaboral_total'],
        ];
        $elements['estres_total'] = [
            'type'          => 'total',
            'questionnaire' => 'estres',
            'field'         => 'estres_total_puntaje',
            'baremo_a'      => $baremos['A']['estres_total'],
            'baremo_b'      => $baremos['B']['estres_total'],
        ];

        // === DOMINIOS ===
        $dominios = [
            'dom_liderazgo'   => ['field' => 'dom_liderazgo_puntaje', 'key' => 'liderazgo'],
            'dom_control'     => ['field' => 'dom_control_puntaje', 'key' => 'control'],
            'dom_demandas'    => ['field' => 'dom_demandas_puntaje', 'key' => 'demandas'],
            'dom_recompensas' => ['field' => 'dom_recompensas_puntaje', 'key' => 'recompensas'],
        ];
        foreach ($dominios as $code => $config) {
            $elements[$code] = [
                'type'          => 'domain',
                'questionnaire' => 'intralaboral',
                'field'         => $config['field'],
                'baremo_a'      => $baremos['A']['dominios'][$config['key']] ?? [],
                'baremo_b'      => $baremos['B']['dominios'][$config['key']] ?? [],
            ];
        }

        // === DIMENSIONES INTRALABORALES ===
        // Mapeo explícito: [código_interno => [campo_bd, clave_baremo]]
        $dimensionesIntra = [
            'dim_caracteristicas_liderazgo'   => ['dim_caracteristicas_liderazgo_puntaje', 'caracteristicas_liderazgo'],
            'dim_relaciones_sociales'         => ['dim_relaciones_sociales_puntaje', 'relaciones_sociales'],
            'dim_retroalimentacion'           => ['dim_retroalimentacion_puntaje', 'retroalimentacion'],
            'dim_relacion_colaboradores'      => ['dim_relacion_colaboradores_puntaje', 'relacion_colaboradores'],
            'dim_claridad_rol'                => ['dim_claridad_rol_puntaje', 'claridad_rol'],
            'dim_capacitacion'                => ['dim_capacitacion_puntaje', 'capacitacion'],
            'dim_participacion_manejo_cambio' => ['dim_participacion_manejo_cambio_puntaje', 'participacion_cambio'],
            'dim_oportunidades_desarrollo'    => ['dim_oportunidades_desarrollo_puntaje', 'oportunidades_desarrollo'],
            'dim_control_autonomia'           => ['dim_control_autonomia_puntaje', 'control_autonomia'],
            'dim_demandas_ambientales'        => ['dim_demandas_ambientales_puntaje', 'demandas_ambientales'],
            'dim_demandas_emocionales'        => ['dim_demandas_emocionales_puntaje', 'demandas_emocionales'],
            'dim_demandas_cuantitativas'      => ['dim_demandas_cuantitativas_puntaje', 'demandas_cuantitativas'],
            'dim_influencia_trabajo_entorno'  => ['dim_influencia_trabajo_entorno_extralaboral_puntaje', 'influencia_entorno'],
            'dim_demandas_responsabilidad'    => ['dim_demandas_responsabilidad_puntaje', 'exigencias_responsabilidad'],
            'dim_carga_mental'                => ['dim_demandas_carga_mental_puntaje', 'carga_mental'],
            'dim_consistencia_rol'            => ['dim_consistencia_rol_puntaje', 'consistencia_rol'],
            'dim_demandas_jornada'            => ['dim_demandas_jornada_trabajo_puntaje', 'demandas_jornada'],
            'dim_recompensas_pertenencia'     => ['dim_recompensas_pertenencia_puntaje', 'recompensas_pertenencia'],
            'dim_reconocimiento_compensacion' => ['dim_reconocimiento_compensacion_puntaje', 'reconocimiento_compensacion'],
        ];

        foreach ($dimensionesIntra as $code => $config) {
            $fieldName = $config[0];
            $baremKey = $config[1];

            $baremoA = $baremos['A']['dimensiones_intra'][$baremKey] ?? null;
            $baremoB = $baremos['B']['dimensiones_intra'][$baremKey] ?? null;

            // Solo agregar si existe al menos un baremo
            if ($baremoA || $baremoB) {
                $elements[$code] = [
                    'type'          => 'dimension',
                    'questionnaire' => 'intralaboral',
                    'field'         => $fieldName,
                    'baremo_a'      => $baremoA ?? [],
                    'baremo_b'      => $baremoB ?? [],
                ];
            }
        }

        // === DIMENSIONES EXTRALABORALES ===
        // Nota: Los campos en BD usan formato extralaboral_{dimension}_puntaje (sin "dim_")
        $dimensionesExtra = [
            'dim_tiempo_fuera'              => ['field' => 'extralaboral_tiempo_fuera_puntaje', 'key' => 'tiempo_fuera'],
            'dim_relaciones_familiares_extra' => ['field' => 'extralaboral_relaciones_familiares_puntaje', 'key' => 'relaciones_familiares'],
            'dim_comunicacion'              => ['field' => 'extralaboral_comunicacion_puntaje', 'key' => 'comunicacion'],
            'dim_situacion_economica'       => ['field' => 'extralaboral_situacion_economica_puntaje', 'key' => 'situacion_economica'],
            'dim_caracteristicas_vivienda'  => ['field' => 'extralaboral_caracteristicas_vivienda_puntaje', 'key' => 'caracteristicas_vivienda'],
            'dim_influencia_entorno_extra'  => ['field' => 'extralaboral_influencia_entorno_puntaje', 'key' => 'influencia_entorno_extra'],
            'dim_desplazamiento'            => ['field' => 'extralaboral_desplazamiento_puntaje', 'key' => 'desplazamiento'],
        ];

        foreach ($dimensionesExtra as $code => $config) {
            $baremo = $baremos['dimensiones_extra'][$config['key']] ?? null;
            if ($baremo) {
                $elements[$code] = [
                    'type'          => 'dimension',
                    'questionnaire' => 'extralaboral',
                    'field'         => $config['field'],
                    'baremo_a'      => $baremo, // Igual para ambas formas
                    'baremo_b'      => $baremo,
                ];
            }
        }

        return $elements;
    }

    /**
     * Calcula el peor resultado entre Forma A y B para un campo específico
     */
    private function calculateWorstResult(
        string $field,
        ?array $baremoA,
        ?array $baremoB,
        array $resultsA,
        array $resultsB,
        bool $hasFormaA,
        bool $hasFormaB,
        bool $hasBothForms
    ): ?array {
        $dataA = ($hasFormaA && $baremoA) ? $this->calculateFormaDetail($field, $baremoA, $resultsA) : null;
        $dataB = ($hasFormaB && $baremoB) ? $this->calculateFormaDetail($field, $baremoB, $resultsB) : null;

        // Si no hay datos de ninguna forma
        if ($dataA === null && $dataB === null) {
            return null;
        }

        // Si solo hay data de una forma para ESTA dimensión específica
        // (puede ser porque el baremo no existe en una forma, o porque no hay trabajadores)
        if ($dataA === null || $dataB === null) {
            $data = $dataA ?? $dataB;
            $forma = $dataA ? 'A' : 'B';
            return array_merge($data, [
                'forma_origen'   => $forma,
                'solo_una_forma' => true,
                'data_a'         => $dataA,
                'data_b'         => $dataB,
            ]);
        }

        // Hay ambas formas con datos: determinar cuál es peor
        $orderA = $dataA['nivel_order'] ?? 0;
        $orderB = $dataB['nivel_order'] ?? 0;

        // Si empatan en nivel, el peor es el de mayor puntaje
        if ($orderA === $orderB) {
            $worst = ($dataA['promedio'] ?? 0) >= ($dataB['promedio'] ?? 0) ? $dataA : $dataB;
            $formaOrigen = ($dataA['promedio'] ?? 0) >= ($dataB['promedio'] ?? 0) ? 'A' : 'B';
        } else {
            $worst = $orderA > $orderB ? $dataA : $dataB;
            $formaOrigen = $orderA > $orderB ? 'A' : 'B';
        }

        return array_merge($worst, [
            'forma_origen'   => $formaOrigen,
            'solo_una_forma' => false,
            'data_a'         => $dataA,
            'data_b'         => $dataB,
        ]);
    }

    /**
     * Calcula los detalles de una forma específica
     */
    private function calculateFormaDetail(string $field, array $baremo, array $resultados): ?array
    {
        $puntajes = array_filter(array_column($resultados, $field), function($v) {
            return $v !== null && $v !== '';
        });

        if (empty($puntajes)) {
            return null;
        }

        $promedio = array_sum($puntajes) / count($puntajes);
        $nivel = 'sin_riesgo';

        foreach ($baremo as $nivelKey => $rango) {
            if ($promedio >= $rango[0] && $promedio <= $rango[1]) {
                $nivel = $nivelKey;
                break;
            }
        }

        return [
            'promedio'    => round($promedio, 2),
            'nivel'       => $nivel,
            'nivel_order' => self::RISK_ORDER[$nivel] ?? 0,
            'cantidad'    => count($puntajes),
        ];
    }
}
