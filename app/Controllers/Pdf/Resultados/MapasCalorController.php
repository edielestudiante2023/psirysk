<?php

namespace App\Controllers\Pdf\Resultados;

use App\Controllers\Pdf\PdfBaseController;
use App\Models\CalculatedResultModel;

/**
 * Controller para la sección de Mapas de Calor del informe PDF
 * Incluye: Introducción, Conclusiones, Mapa General, Intralaboral A/B, Extralaboral A/B, Estrés A/B
 */
class MapasCalorController extends PdfBaseController
{
    protected $calculatedResultModel;

    public function __construct()
    {
        $this->calculatedResultModel = new CalculatedResultModel();
    }

    /**
     * Renderiza todas las páginas de mapas de calor
     */
    public function render($batteryServiceId)
    {
        $this->initializeData($batteryServiceId);

        // Obtener estadísticas de participación
        $stats = $this->getParticipationStats($batteryServiceId);
        $totalParticipantes = $stats['total'] ?? 0;
        $formaA = $stats['forma_a'] ?? 0;
        $formaB = $stats['forma_b'] ?? 0;

        // Obtener todos los resultados calculados
        $results = $this->calculatedResultModel
            ->where('battery_service_id', $batteryServiceId)
            ->findAll();

        // Calcular datos de mapas de calor
        $heatmapGeneral = $this->calculateGeneralHeatmap($results);
        $heatmapIntraA = $this->calculateIntralaboralHeatmap($results, 'A');
        $heatmapIntraB = $this->calculateIntralaboralHeatmap($results, 'B');
        $heatmapExtraA = $this->calculateExtralaboralHeatmap($results, 'A');
        $heatmapExtraB = $this->calculateExtralaboralHeatmap($results, 'B');
        $heatmapEstresA = $this->calculateEstresHeatmap($results, 'A', $batteryServiceId);
        $heatmapEstresB = $this->calculateEstresHeatmap($results, 'B', $batteryServiceId);

        // Determinar nivel de riesgo general y periodicidad
        $nivelRiesgoGeneral = $this->determinarNivelRiesgoGeneral($heatmapGeneral);
        $periodicidad = in_array($nivelRiesgoGeneral['nivel'], ['riesgo_alto', 'riesgo_muy_alto']) ? 1 : 2;

        $html = '';

        // Página 1: Introducción a Resultados
        $html .= $this->renderView('pdf/resultados/mapas_calor/introduccion_resultados', [
            'totalParticipantes' => $totalParticipantes,
            'formaA' => $formaA,
            'formaB' => $formaB,
            'pctFormaA' => $totalParticipantes > 0 ? round(($formaA / $totalParticipantes) * 100) : 0,
            'pctFormaB' => $totalParticipantes > 0 ? round(($formaB / $totalParticipantes) * 100) : 0,
            'companyName' => $this->companyData['company_name'] ?? 'Empresa',
        ]);

        // Página 2: Conclusiones de la Batería
        $html .= $this->renderView('pdf/resultados/mapas_calor/conclusiones_bateria', [
            'companyName' => $this->companyData['company_name'] ?? 'Empresa',
            'nivelRiesgoGeneral' => $nivelRiesgoGeneral['texto'],
            'colorNivelGeneral' => $nivelRiesgoGeneral['color'],
            'puntajeFormaA' => $heatmapIntraA['intralaboral_total']['promedio'] ?? null,
            'nivelFormaA' => $this->getNivelTexto($heatmapIntraA['intralaboral_total']['nivel'] ?? 'sin_riesgo'),
            'puntajeFormaB' => $heatmapIntraB['intralaboral_total']['promedio'] ?? null,
            'nivelFormaB' => $this->getNivelTexto($heatmapIntraB['intralaboral_total']['nivel'] ?? 'sin_riesgo'),
            'periodicidad' => $periodicidad,
            'nivelEstresGeneral' => $this->getNivelTexto($heatmapGeneral['estres_total']['nivel'] ?? 'sin_riesgo'),
            'colorNivelEstres' => $this->getRiskColor($heatmapGeneral['estres_total']['nivel'] ?? 'sin_riesgo'),
            'puntajeEstresA' => $heatmapEstresA['estres_total']['promedio'] ?? null,
            'nivelEstresA' => $this->getNivelTexto($heatmapEstresA['estres_total']['nivel'] ?? 'sin_riesgo'),
            'puntajeEstresB' => $heatmapEstresB['estres_total']['promedio'] ?? null,
            'nivelEstresB' => $this->getNivelTexto($heatmapEstresB['estres_total']['nivel'] ?? 'sin_riesgo'),
        ]);

        // Página 3: Mapa de Calor General
        $html .= $this->renderView('pdf/resultados/mapas_calor/mapa_calor_general', [
            'heatmapData' => $heatmapGeneral,
            'totalWorkers' => $totalParticipantes,
        ]);

        // Página 4: Mapa Intralaboral Forma A (solo si hay datos)
        if ($formaA > 0) {
            $html .= $this->renderView('pdf/resultados/mapas_calor/mapa_intralaboral_a', [
                'calculations' => $heatmapIntraA,
                'totalTrabajadores' => $formaA,
            ]);
        }

        // Página 5: Mapa Intralaboral Forma B (solo si hay datos)
        if ($formaB > 0) {
            $html .= $this->renderView('pdf/resultados/mapas_calor/mapa_intralaboral_b', [
                'calculations' => $heatmapIntraB,
                'totalTrabajadores' => $formaB,
            ]);
        }

        // Página 6: Mapa Extralaboral Forma A (solo si hay datos)
        if ($formaA > 0) {
            $html .= $this->renderView('pdf/resultados/mapas_calor/mapa_extralaboral_a', [
                'calculations' => $heatmapExtraA,
                'totalTrabajadores' => $formaA,
            ]);
        }

        // Página 7: Mapa Extralaboral Forma B (solo si hay datos)
        if ($formaB > 0) {
            $html .= $this->renderView('pdf/resultados/mapas_calor/mapa_extralaboral_b', [
                'calculations' => $heatmapExtraB,
                'totalTrabajadores' => $formaB,
            ]);
        }

        // Página 8: Mapa Estrés Forma A (solo si hay datos)
        if ($formaA > 0) {
            $html .= $this->renderView('pdf/resultados/mapas_calor/mapa_estres_a', [
                'calculations' => $heatmapEstresA,
                'totalTrabajadores' => $formaA,
            ]);
        }

        // Página 9: Mapa Estrés Forma B (solo si hay datos)
        if ($formaB > 0) {
            $html .= $this->renderView('pdf/resultados/mapas_calor/mapa_estres_b', [
                'calculations' => $heatmapEstresB,
                'totalTrabajadores' => $formaB,
            ]);
        }

        return $html;
    }

    /**
     * Calcula el mapa de calor general (combinando ambas formas)
     * Incluye todos los dominios, dimensiones y totales
     */
    protected function calculateGeneralHeatmap($results)
    {
        if (empty($results)) {
            return $this->getEmptyHeatmapStructure();
        }

        // Helper para calcular detalles
        $calculateDetail = function($field, $baremo) use ($results) {
            $puntajes = array_filter(array_column($results, $field), function($v) {
                return $v !== null && $v !== '';
            });

            if (empty($puntajes)) {
                return [
                    'promedio' => 0,
                    'nivel' => 'sin_riesgo',
                    'cantidad' => 0
                ];
            }

            $promedio = array_sum($puntajes) / count($puntajes);
            $nivel = $this->determinarNivelConBaremo($promedio, $baremo);

            return [
                'promedio' => round($promedio, 2),
                'nivel' => $nivel,
                'cantidad' => count($puntajes)
            ];
        };

        // Baremos para total general (usar Forma A como base)
        $baremoIntralaboralTotal = [
            'sin_riesgo' => [0.0, 19.7],
            'riesgo_bajo' => [19.8, 25.8],
            'riesgo_medio' => [25.9, 31.5],
            'riesgo_alto' => [31.6, 38.0],
            'riesgo_muy_alto' => [38.1, 100.0]
        ];

        $baremoDominios = [
            'liderazgo' => ['sin_riesgo' => [0.0, 9.1], 'riesgo_bajo' => [9.2, 17.7], 'riesgo_medio' => [17.8, 25.6], 'riesgo_alto' => [25.7, 34.8], 'riesgo_muy_alto' => [34.9, 100.0]],
            'control' => ['sin_riesgo' => [0.0, 10.7], 'riesgo_bajo' => [10.8, 19.0], 'riesgo_medio' => [19.1, 29.8], 'riesgo_alto' => [29.9, 40.5], 'riesgo_muy_alto' => [40.6, 100.0]],
            'demandas' => ['sin_riesgo' => [0.0, 28.5], 'riesgo_bajo' => [28.6, 35.0], 'riesgo_medio' => [35.1, 41.5], 'riesgo_alto' => [41.6, 47.5], 'riesgo_muy_alto' => [47.6, 100.0]],
            'recompensas' => ['sin_riesgo' => [0.0, 4.5], 'riesgo_bajo' => [4.6, 11.4], 'riesgo_medio' => [11.5, 20.5], 'riesgo_alto' => [20.6, 29.5], 'riesgo_muy_alto' => [29.6, 100.0]]
        ];

        $baremoDimensionesIntra = [
            'caracteristicas_liderazgo' => ['sin_riesgo' => [0.0, 3.8], 'riesgo_bajo' => [3.9, 15.4], 'riesgo_medio' => [15.5, 30.8], 'riesgo_alto' => [30.9, 46.2], 'riesgo_muy_alto' => [46.3, 100.0]],
            'relaciones_sociales' => ['sin_riesgo' => [0.0, 5.4], 'riesgo_bajo' => [5.5, 16.1], 'riesgo_medio' => [16.2, 25.0], 'riesgo_alto' => [25.1, 37.5], 'riesgo_muy_alto' => [37.6, 100.0]],
            'retroalimentacion' => ['sin_riesgo' => [0.0, 10.0], 'riesgo_bajo' => [10.1, 25.0], 'riesgo_medio' => [25.1, 40.0], 'riesgo_alto' => [40.1, 55.0], 'riesgo_muy_alto' => [55.1, 100.0]],
            'relacion_colaboradores' => ['sin_riesgo' => [0.0, 13.9], 'riesgo_bajo' => [14.0, 25.0], 'riesgo_medio' => [25.1, 33.3], 'riesgo_alto' => [33.4, 47.2], 'riesgo_muy_alto' => [47.3, 100.0]],
            'claridad_rol' => ['sin_riesgo' => [0.0, 0.9], 'riesgo_bajo' => [1.0, 10.7], 'riesgo_medio' => [10.8, 21.4], 'riesgo_alto' => [21.5, 39.3], 'riesgo_muy_alto' => [39.4, 100.0]],
            'capacitacion' => ['sin_riesgo' => [0.0, 0.9], 'riesgo_bajo' => [1.0, 16.7], 'riesgo_medio' => [16.8, 33.3], 'riesgo_alto' => [33.4, 50.0], 'riesgo_muy_alto' => [50.1, 100.0]],
            'participacion_cambio' => ['sin_riesgo' => [0.0, 12.5], 'riesgo_bajo' => [12.6, 25.0], 'riesgo_medio' => [25.1, 37.5], 'riesgo_alto' => [37.6, 50.0], 'riesgo_muy_alto' => [50.1, 100.0]],
            'oportunidades_desarrollo' => ['sin_riesgo' => [0.0, 0.9], 'riesgo_bajo' => [1.0, 6.3], 'riesgo_medio' => [6.4, 18.8], 'riesgo_alto' => [18.9, 31.3], 'riesgo_muy_alto' => [31.4, 100.0]],
            'control_autonomia' => ['sin_riesgo' => [0.0, 8.3], 'riesgo_bajo' => [8.4, 25.0], 'riesgo_medio' => [25.1, 41.7], 'riesgo_alto' => [41.8, 58.3], 'riesgo_muy_alto' => [58.4, 100.0]],
            'demandas_ambientales' => ['sin_riesgo' => [0.0, 14.6], 'riesgo_bajo' => [14.7, 22.9], 'riesgo_medio' => [23.0, 31.3], 'riesgo_alto' => [31.4, 39.6], 'riesgo_muy_alto' => [39.7, 100.0]],
            'demandas_emocionales' => ['sin_riesgo' => [0.0, 16.7], 'riesgo_bajo' => [16.8, 25.0], 'riesgo_medio' => [25.1, 33.3], 'riesgo_alto' => [33.4, 47.2], 'riesgo_muy_alto' => [47.3, 100.0]],
            'demandas_cuantitativas' => ['sin_riesgo' => [0.0, 25.0], 'riesgo_bajo' => [25.1, 33.3], 'riesgo_medio' => [33.4, 45.8], 'riesgo_alto' => [45.9, 54.2], 'riesgo_muy_alto' => [54.3, 100.0]],
            'influencia_entorno' => ['sin_riesgo' => [0.0, 18.8], 'riesgo_bajo' => [18.9, 31.3], 'riesgo_medio' => [31.4, 43.8], 'riesgo_alto' => [43.9, 50.0], 'riesgo_muy_alto' => [50.1, 100.0]],
            'exigencias_responsabilidad' => ['sin_riesgo' => [0.0, 37.5], 'riesgo_bajo' => [37.6, 54.2], 'riesgo_medio' => [54.3, 66.7], 'riesgo_alto' => [66.8, 79.2], 'riesgo_muy_alto' => [79.3, 100.0]],
            'carga_mental' => ['sin_riesgo' => [0.0, 60.0], 'riesgo_bajo' => [60.1, 70.0], 'riesgo_medio' => [70.1, 80.0], 'riesgo_alto' => [80.1, 90.0], 'riesgo_muy_alto' => [90.1, 100.0]],
            'consistencia_rol' => ['sin_riesgo' => [0.0, 15.0], 'riesgo_bajo' => [15.1, 25.0], 'riesgo_medio' => [25.1, 35.0], 'riesgo_alto' => [35.1, 45.0], 'riesgo_muy_alto' => [45.1, 100.0]],
            'demandas_jornada' => ['sin_riesgo' => [0.0, 8.3], 'riesgo_bajo' => [8.4, 25.0], 'riesgo_medio' => [25.1, 33.3], 'riesgo_alto' => [33.4, 50.0], 'riesgo_muy_alto' => [50.1, 100.0]],
            'recompensas_pertenencia' => ['sin_riesgo' => [0.0, 0.9], 'riesgo_bajo' => [1.0, 5.0], 'riesgo_medio' => [5.1, 10.0], 'riesgo_alto' => [10.1, 20.0], 'riesgo_muy_alto' => [20.1, 100.0]],
            'reconocimiento_compensacion' => ['sin_riesgo' => [0.0, 4.2], 'riesgo_bajo' => [4.3, 16.7], 'riesgo_medio' => [16.8, 25.0], 'riesgo_alto' => [25.1, 37.5], 'riesgo_muy_alto' => [37.6, 100.0]]
        ];

        $baremoExtralaboralTotal = [
            'sin_riesgo' => [0.0, 11.3],
            'riesgo_bajo' => [11.4, 16.9],
            'riesgo_medio' => [17.0, 22.6],
            'riesgo_alto' => [22.7, 29.0],
            'riesgo_muy_alto' => [29.1, 100.0]
        ];

        $baremoDimensionesExtra = [
            'tiempo_fuera' => ['sin_riesgo' => [0.0, 6.3], 'riesgo_bajo' => [6.4, 25.0], 'riesgo_medio' => [25.1, 37.5], 'riesgo_alto' => [37.6, 50.0], 'riesgo_muy_alto' => [50.1, 100.0]],
            'relaciones_familiares' => ['sin_riesgo' => [0.0, 8.3], 'riesgo_bajo' => [8.4, 25.0], 'riesgo_medio' => [25.1, 33.3], 'riesgo_alto' => [33.4, 50.0], 'riesgo_muy_alto' => [50.1, 100.0]],
            'comunicacion' => ['sin_riesgo' => [0.0, 0.9], 'riesgo_bajo' => [1.0, 10.0], 'riesgo_medio' => [10.1, 20.0], 'riesgo_alto' => [20.1, 30.0], 'riesgo_muy_alto' => [30.1, 100.0]],
            'situacion_economica' => ['sin_riesgo' => [0.0, 8.3], 'riesgo_bajo' => [8.4, 25.0], 'riesgo_medio' => [25.1, 33.3], 'riesgo_alto' => [33.4, 50.0], 'riesgo_muy_alto' => [50.1, 100.0]],
            'caracteristicas_vivienda' => ['sin_riesgo' => [0.0, 5.6], 'riesgo_bajo' => [5.7, 11.1], 'riesgo_medio' => [11.2, 13.9], 'riesgo_alto' => [14.0, 22.2], 'riesgo_muy_alto' => [22.3, 100.0]],
            'influencia_entorno_extra' => ['sin_riesgo' => [0.0, 8.3], 'riesgo_bajo' => [8.4, 16.7], 'riesgo_medio' => [16.8, 25.0], 'riesgo_alto' => [25.1, 41.7], 'riesgo_muy_alto' => [41.8, 100.0]],
            'desplazamiento' => ['sin_riesgo' => [0.0, 0.9], 'riesgo_bajo' => [1.0, 12.5], 'riesgo_medio' => [12.6, 25.0], 'riesgo_alto' => [25.1, 43.8], 'riesgo_muy_alto' => [43.9, 100.0]]
        ];

        $baremoEstres = [
            'muy_bajo' => [0.0, 7.8],
            'bajo' => [7.9, 12.6],
            'medio' => [12.7, 17.7],
            'alto' => [17.8, 25.0],
            'muy_alto' => [25.1, 100.0]
        ];

        return [
            // Totales
            'intralaboral_total' => $calculateDetail('intralaboral_total_puntaje', $baremoIntralaboralTotal),
            'extralaboral_total' => $calculateDetail('extralaboral_total_puntaje', $baremoExtralaboralTotal),
            'estres_total' => $calculateDetail('estres_total_puntaje', $baremoEstres),

            // Dominios intralaborales
            'dom_liderazgo' => $calculateDetail('dom_liderazgo_puntaje', $baremoDominios['liderazgo']),
            'dom_control' => $calculateDetail('dom_control_puntaje', $baremoDominios['control']),
            'dom_demandas' => $calculateDetail('dom_demandas_puntaje', $baremoDominios['demandas']),
            'dom_recompensas' => $calculateDetail('dom_recompensas_puntaje', $baremoDominios['recompensas']),

            // Dimensiones intralaborales
            'dim_caracteristicas_liderazgo' => $calculateDetail('dim_caracteristicas_liderazgo_puntaje', $baremoDimensionesIntra['caracteristicas_liderazgo']),
            'dim_relaciones_sociales' => $calculateDetail('dim_relaciones_sociales_puntaje', $baremoDimensionesIntra['relaciones_sociales']),
            'dim_retroalimentacion' => $calculateDetail('dim_retroalimentacion_puntaje', $baremoDimensionesIntra['retroalimentacion']),
            'dim_relacion_colaboradores' => $calculateDetail('dim_relacion_colaboradores_puntaje', $baremoDimensionesIntra['relacion_colaboradores']),
            'dim_claridad_rol' => $calculateDetail('dim_claridad_rol_puntaje', $baremoDimensionesIntra['claridad_rol']),
            'dim_capacitacion' => $calculateDetail('dim_capacitacion_puntaje', $baremoDimensionesIntra['capacitacion']),
            'dim_participacion_manejo_cambio' => $calculateDetail('dim_participacion_manejo_cambio_puntaje', $baremoDimensionesIntra['participacion_cambio']),
            'dim_oportunidades_desarrollo' => $calculateDetail('dim_oportunidades_desarrollo_puntaje', $baremoDimensionesIntra['oportunidades_desarrollo']),
            'dim_control_autonomia' => $calculateDetail('dim_control_autonomia_puntaje', $baremoDimensionesIntra['control_autonomia']),
            'dim_demandas_ambientales' => $calculateDetail('dim_demandas_ambientales_puntaje', $baremoDimensionesIntra['demandas_ambientales']),
            'dim_demandas_emocionales' => $calculateDetail('dim_demandas_emocionales_puntaje', $baremoDimensionesIntra['demandas_emocionales']),
            'dim_demandas_cuantitativas' => $calculateDetail('dim_demandas_cuantitativas_puntaje', $baremoDimensionesIntra['demandas_cuantitativas']),
            'dim_influencia_trabajo_entorno_extralaboral' => $calculateDetail('dim_influencia_trabajo_entorno_extralaboral_puntaje', $baremoDimensionesIntra['influencia_entorno']),
            'dim_demandas_responsabilidad' => $calculateDetail('dim_demandas_responsabilidad_puntaje', $baremoDimensionesIntra['exigencias_responsabilidad']),
            'dim_carga_mental' => $calculateDetail('dim_demandas_carga_mental_puntaje', $baremoDimensionesIntra['carga_mental']),
            'dim_consistencia_rol' => $calculateDetail('dim_consistencia_rol_puntaje', $baremoDimensionesIntra['consistencia_rol']),
            'dim_demandas_jornada_trabajo' => $calculateDetail('dim_demandas_jornada_trabajo_puntaje', $baremoDimensionesIntra['demandas_jornada']),
            'dim_recompensas_pertenencia' => $calculateDetail('dim_recompensas_pertenencia_puntaje', $baremoDimensionesIntra['recompensas_pertenencia']),
            'dim_reconocimiento_compensacion' => $calculateDetail('dim_reconocimiento_compensacion_puntaje', $baremoDimensionesIntra['reconocimiento_compensacion']),

            // Dimensiones extralaborales (con nombres que espera la vista)
            'dim_tiempo_fuera' => $calculateDetail('extralaboral_tiempo_fuera_puntaje', $baremoDimensionesExtra['tiempo_fuera']),
            'dim_relaciones_familiares_extra' => $calculateDetail('extralaboral_relaciones_familiares_puntaje', $baremoDimensionesExtra['relaciones_familiares']),
            'dim_comunicacion' => $calculateDetail('extralaboral_comunicacion_puntaje', $baremoDimensionesExtra['comunicacion']),
            'dim_situacion_economica' => $calculateDetail('extralaboral_situacion_economica_puntaje', $baremoDimensionesExtra['situacion_economica']),
            'dim_caracteristicas_vivienda' => $calculateDetail('extralaboral_caracteristicas_vivienda_puntaje', $baremoDimensionesExtra['caracteristicas_vivienda']),
            'dim_influencia_entorno_extra' => $calculateDetail('extralaboral_influencia_entorno_puntaje', $baremoDimensionesExtra['influencia_entorno_extra']),
            'dim_desplazamiento' => $calculateDetail('extralaboral_desplazamiento_puntaje', $baremoDimensionesExtra['desplazamiento']),
        ];
    }

    /**
     * Calcula el mapa de calor intralaboral por forma
     */
    protected function calculateIntralaboralHeatmap($results, $forma)
    {
        $filteredResults = array_filter($results, function($r) use ($forma) {
            return strtoupper($r['intralaboral_form_type'] ?? '') === $forma;
        });

        if (empty($filteredResults)) {
            return $this->getEmptyIntralaboralStructure();
        }

        // Baremos según forma (Tabla 33)
        $baremoIntralaboralTotal = $forma === 'A'
            ? [
                'sin_riesgo' => [0.0, 19.7],
                'riesgo_bajo' => [19.8, 25.8],
                'riesgo_medio' => [25.9, 31.5],
                'riesgo_alto' => [31.6, 38.0],
                'riesgo_muy_alto' => [38.1, 100.0]
            ]
            : [
                'sin_riesgo' => [0.0, 20.6],
                'riesgo_bajo' => [20.7, 26.0],
                'riesgo_medio' => [26.1, 31.2],
                'riesgo_alto' => [31.3, 38.7],
                'riesgo_muy_alto' => [38.8, 100.0]
            ];

        // Baremos dominios Forma A (Tabla 31)
        $baremoDominiosA = [
            'liderazgo' => [
                'sin_riesgo' => [0.0, 9.1],
                'riesgo_bajo' => [9.2, 17.7],
                'riesgo_medio' => [17.8, 25.6],
                'riesgo_alto' => [25.7, 34.8],
                'riesgo_muy_alto' => [34.9, 100.0]
            ],
            'control' => [
                'sin_riesgo' => [0.0, 10.7],
                'riesgo_bajo' => [10.8, 19.0],
                'riesgo_medio' => [19.1, 29.8],
                'riesgo_alto' => [29.9, 40.5],
                'riesgo_muy_alto' => [40.6, 100.0]
            ],
            'demandas' => [
                'sin_riesgo' => [0.0, 28.5],
                'riesgo_bajo' => [28.6, 35.0],
                'riesgo_medio' => [35.1, 41.5],
                'riesgo_alto' => [41.6, 47.5],
                'riesgo_muy_alto' => [47.6, 100.0]
            ],
            'recompensas' => [
                'sin_riesgo' => [0.0, 4.5],
                'riesgo_bajo' => [4.6, 11.4],
                'riesgo_medio' => [11.5, 20.5],
                'riesgo_alto' => [20.6, 29.5],
                'riesgo_muy_alto' => [29.6, 100.0]
            ]
        ];

        // Baremos dominios Forma B (Tabla 32)
        $baremoDominiosB = [
            'liderazgo' => [
                'sin_riesgo' => [0.0, 8.3],
                'riesgo_bajo' => [8.4, 17.5],
                'riesgo_medio' => [17.6, 26.7],
                'riesgo_alto' => [26.8, 38.3],
                'riesgo_muy_alto' => [38.4, 100.0]
            ],
            'control' => [
                'sin_riesgo' => [0.0, 19.4],
                'riesgo_bajo' => [19.5, 26.4],
                'riesgo_medio' => [26.5, 34.7],
                'riesgo_alto' => [34.8, 43.1],
                'riesgo_muy_alto' => [43.2, 100.0]
            ],
            'demandas' => [
                'sin_riesgo' => [0.0, 26.9],
                'riesgo_bajo' => [27.0, 33.3],
                'riesgo_medio' => [33.4, 37.8],
                'riesgo_alto' => [37.9, 44.2],
                'riesgo_muy_alto' => [44.3, 100.0]
            ],
            'recompensas' => [
                'sin_riesgo' => [0.0, 2.5],
                'riesgo_bajo' => [2.6, 10.0],
                'riesgo_medio' => [10.1, 17.5],
                'riesgo_alto' => [17.6, 27.5],
                'riesgo_muy_alto' => [27.6, 100.0]
            ]
        ];

        $baremoDominios = $forma === 'A' ? $baremoDominiosA : $baremoDominiosB;

        // Baremos de dimensiones intralaborales Forma A (Tabla 29)
        $baremoDimensionesA = [
            'caracteristicas_liderazgo' => [
                'sin_riesgo' => [0.0, 3.8], 'riesgo_bajo' => [3.9, 15.4], 'riesgo_medio' => [15.5, 30.8],
                'riesgo_alto' => [30.9, 46.2], 'riesgo_muy_alto' => [46.3, 100.0]
            ],
            'relaciones_sociales' => [
                'sin_riesgo' => [0.0, 5.4], 'riesgo_bajo' => [5.5, 16.1], 'riesgo_medio' => [16.2, 25.0],
                'riesgo_alto' => [25.1, 37.5], 'riesgo_muy_alto' => [37.6, 100.0]
            ],
            'retroalimentacion' => [
                'sin_riesgo' => [0.0, 10.0], 'riesgo_bajo' => [10.1, 25.0], 'riesgo_medio' => [25.1, 40.0],
                'riesgo_alto' => [40.1, 55.0], 'riesgo_muy_alto' => [55.1, 100.0]
            ],
            'relacion_colaboradores' => [
                'sin_riesgo' => [0.0, 13.9], 'riesgo_bajo' => [14.0, 25.0], 'riesgo_medio' => [25.1, 33.3],
                'riesgo_alto' => [33.4, 47.2], 'riesgo_muy_alto' => [47.3, 100.0]
            ],
            'claridad_rol' => [
                'sin_riesgo' => [0.0, 0.9], 'riesgo_bajo' => [1.0, 10.7], 'riesgo_medio' => [10.8, 21.4],
                'riesgo_alto' => [21.5, 39.3], 'riesgo_muy_alto' => [39.4, 100.0]
            ],
            'capacitacion' => [
                'sin_riesgo' => [0.0, 0.9], 'riesgo_bajo' => [1.0, 16.7], 'riesgo_medio' => [16.8, 33.3],
                'riesgo_alto' => [33.4, 50.0], 'riesgo_muy_alto' => [50.1, 100.0]
            ],
            'participacion_cambio' => [
                'sin_riesgo' => [0.0, 12.5], 'riesgo_bajo' => [12.6, 25.0], 'riesgo_medio' => [25.1, 37.5],
                'riesgo_alto' => [37.6, 50.0], 'riesgo_muy_alto' => [50.1, 100.0]
            ],
            'oportunidades_desarrollo' => [
                'sin_riesgo' => [0.0, 0.9], 'riesgo_bajo' => [1.0, 6.3], 'riesgo_medio' => [6.4, 18.8],
                'riesgo_alto' => [18.9, 31.3], 'riesgo_muy_alto' => [31.4, 100.0]
            ],
            'control_autonomia' => [
                'sin_riesgo' => [0.0, 8.3], 'riesgo_bajo' => [8.4, 25.0], 'riesgo_medio' => [25.1, 41.7],
                'riesgo_alto' => [41.8, 58.3], 'riesgo_muy_alto' => [58.4, 100.0]
            ],
            'demandas_ambientales' => [
                'sin_riesgo' => [0.0, 14.6], 'riesgo_bajo' => [14.7, 22.9], 'riesgo_medio' => [23.0, 31.3],
                'riesgo_alto' => [31.4, 39.6], 'riesgo_muy_alto' => [39.7, 100.0]
            ],
            'demandas_emocionales' => [
                'sin_riesgo' => [0.0, 16.7], 'riesgo_bajo' => [16.8, 25.0], 'riesgo_medio' => [25.1, 33.3],
                'riesgo_alto' => [33.4, 47.2], 'riesgo_muy_alto' => [47.3, 100.0]
            ],
            'demandas_cuantitativas' => [
                'sin_riesgo' => [0.0, 25.0], 'riesgo_bajo' => [25.1, 33.3], 'riesgo_medio' => [33.4, 45.8],
                'riesgo_alto' => [45.9, 54.2], 'riesgo_muy_alto' => [54.3, 100.0]
            ],
            'influencia_entorno' => [
                'sin_riesgo' => [0.0, 18.8], 'riesgo_bajo' => [18.9, 31.3], 'riesgo_medio' => [31.4, 43.8],
                'riesgo_alto' => [43.9, 50.0], 'riesgo_muy_alto' => [50.1, 100.0]
            ],
            'exigencias_responsabilidad' => [
                'sin_riesgo' => [0.0, 37.5], 'riesgo_bajo' => [37.6, 54.2], 'riesgo_medio' => [54.3, 66.7],
                'riesgo_alto' => [66.8, 79.2], 'riesgo_muy_alto' => [79.3, 100.0]
            ],
            'carga_mental' => [
                'sin_riesgo' => [0.0, 60.0], 'riesgo_bajo' => [60.1, 70.0], 'riesgo_medio' => [70.1, 80.0],
                'riesgo_alto' => [80.1, 90.0], 'riesgo_muy_alto' => [90.1, 100.0]
            ],
            'consistencia_rol' => [
                'sin_riesgo' => [0.0, 15.0], 'riesgo_bajo' => [15.1, 25.0], 'riesgo_medio' => [25.1, 35.0],
                'riesgo_alto' => [35.1, 45.0], 'riesgo_muy_alto' => [45.1, 100.0]
            ],
            'demandas_jornada' => [
                'sin_riesgo' => [0.0, 8.3], 'riesgo_bajo' => [8.4, 25.0], 'riesgo_medio' => [25.1, 33.3],
                'riesgo_alto' => [33.4, 50.0], 'riesgo_muy_alto' => [50.1, 100.0]
            ],
            'recompensas_pertenencia' => [
                'sin_riesgo' => [0.0, 0.9], 'riesgo_bajo' => [1.0, 5.0], 'riesgo_medio' => [5.1, 10.0],
                'riesgo_alto' => [10.1, 20.0], 'riesgo_muy_alto' => [20.1, 100.0]
            ],
            'reconocimiento_compensacion' => [
                'sin_riesgo' => [0.0, 4.2], 'riesgo_bajo' => [4.3, 16.7], 'riesgo_medio' => [16.8, 25.0],
                'riesgo_alto' => [25.1, 37.5], 'riesgo_muy_alto' => [37.6, 100.0]
            ]
        ];

        // Baremos de dimensiones intralaborales Forma B (Tabla 30)
        $baremoDimensionesB = [
            'caracteristicas_liderazgo' => [
                'sin_riesgo' => [0.0, 7.7], 'riesgo_bajo' => [7.8, 19.2], 'riesgo_medio' => [19.3, 32.7],
                'riesgo_alto' => [32.8, 48.1], 'riesgo_muy_alto' => [48.2, 100.0]
            ],
            'relaciones_sociales' => [
                'sin_riesgo' => [0.0, 6.3], 'riesgo_bajo' => [6.4, 14.6], 'riesgo_medio' => [14.7, 25.0],
                'riesgo_alto' => [25.1, 37.5], 'riesgo_muy_alto' => [37.6, 100.0]
            ],
            'retroalimentacion' => [
                'sin_riesgo' => [0.0, 5.0], 'riesgo_bajo' => [5.1, 20.0], 'riesgo_medio' => [20.1, 35.0],
                'riesgo_alto' => [35.1, 55.0], 'riesgo_muy_alto' => [55.1, 100.0]
            ],
            'claridad_rol' => [
                'sin_riesgo' => [0.0, 3.6], 'riesgo_bajo' => [3.7, 14.3], 'riesgo_medio' => [14.4, 28.6],
                'riesgo_alto' => [28.7, 42.9], 'riesgo_muy_alto' => [43.0, 100.0]
            ],
            'capacitacion' => [
                'sin_riesgo' => [0.0, 8.3], 'riesgo_bajo' => [8.4, 16.7], 'riesgo_medio' => [16.8, 33.3],
                'riesgo_alto' => [33.4, 50.0], 'riesgo_muy_alto' => [50.1, 100.0]
            ],
            'participacion_cambio' => [
                'sin_riesgo' => [0.0, 18.8], 'riesgo_bajo' => [18.9, 31.3], 'riesgo_medio' => [31.4, 43.8],
                'riesgo_alto' => [43.9, 56.3], 'riesgo_muy_alto' => [56.4, 100.0]
            ],
            'oportunidades_desarrollo' => [
                'sin_riesgo' => [0.0, 6.3], 'riesgo_bajo' => [6.4, 12.5], 'riesgo_medio' => [12.6, 25.0],
                'riesgo_alto' => [25.1, 37.5], 'riesgo_muy_alto' => [37.6, 100.0]
            ],
            'control_autonomia' => [
                'sin_riesgo' => [0.0, 33.3], 'riesgo_bajo' => [33.4, 50.0], 'riesgo_medio' => [50.1, 58.3],
                'riesgo_alto' => [58.4, 75.0], 'riesgo_muy_alto' => [75.1, 100.0]
            ],
            'demandas_ambientales' => [
                'sin_riesgo' => [0.0, 18.8], 'riesgo_bajo' => [18.9, 27.1], 'riesgo_medio' => [27.2, 35.4],
                'riesgo_alto' => [35.5, 45.8], 'riesgo_muy_alto' => [45.9, 100.0]
            ],
            'demandas_emocionales' => [
                'sin_riesgo' => [0.0, 16.7], 'riesgo_bajo' => [16.8, 25.0], 'riesgo_medio' => [25.1, 33.3],
                'riesgo_alto' => [33.4, 41.7], 'riesgo_muy_alto' => [41.8, 100.0]
            ],
            'demandas_cuantitativas' => [
                'sin_riesgo' => [0.0, 25.0], 'riesgo_bajo' => [25.1, 33.3], 'riesgo_medio' => [33.4, 41.7],
                'riesgo_alto' => [41.8, 50.0], 'riesgo_muy_alto' => [50.1, 100.0]
            ],
            'influencia_entorno' => [
                'sin_riesgo' => [0.0, 18.8], 'riesgo_bajo' => [18.9, 31.3], 'riesgo_medio' => [31.4, 43.8],
                'riesgo_alto' => [43.9, 56.3], 'riesgo_muy_alto' => [56.4, 100.0]
            ],
            'demandas_jornada' => [
                'sin_riesgo' => [0.0, 8.3], 'riesgo_bajo' => [8.4, 25.0], 'riesgo_medio' => [25.1, 33.3],
                'riesgo_alto' => [33.4, 50.0], 'riesgo_muy_alto' => [50.1, 100.0]
            ],
            'recompensas_pertenencia' => [
                'sin_riesgo' => [0.0, 0.9], 'riesgo_bajo' => [1.0, 5.0], 'riesgo_medio' => [5.1, 10.0],
                'riesgo_alto' => [10.1, 15.0], 'riesgo_muy_alto' => [15.1, 100.0]
            ],
            'reconocimiento_compensacion' => [
                'sin_riesgo' => [0.0, 4.2], 'riesgo_bajo' => [4.3, 16.7], 'riesgo_medio' => [16.8, 29.2],
                'riesgo_alto' => [29.3, 41.7], 'riesgo_muy_alto' => [41.8, 100.0]
            ]
        ];

        $baremoDimensiones = $forma === 'A' ? $baremoDimensionesA : $baremoDimensionesB;

        // Helper para calcular promedios
        $calculateDetail = function($field, $baremo) use ($filteredResults) {
            $puntajes = array_filter(array_column($filteredResults, $field), function($v) {
                return $v !== null && $v !== '';
            });

            if (empty($puntajes)) {
                return ['promedio' => 0, 'nivel' => 'sin_riesgo', 'cantidad' => 0];
            }

            $promedio = array_sum($puntajes) / count($puntajes);
            $nivel = $this->determinarNivelConBaremo($promedio, $baremo);

            return [
                'promedio' => round($promedio, 2),
                'nivel' => $nivel,
                'cantidad' => count($puntajes)
            ];
        };

        // Calcular todos los valores - Usando nombres de columna correctos de la BD
        return [
            'intralaboral_total' => $calculateDetail('intralaboral_total_puntaje', $baremoIntralaboralTotal),
            'dom_liderazgo' => $calculateDetail('dom_liderazgo_puntaje', $baremoDominios['liderazgo']),
            'dom_control' => $calculateDetail('dom_control_puntaje', $baremoDominios['control']),
            'dom_demandas' => $calculateDetail('dom_demandas_puntaje', $baremoDominios['demandas']),
            'dom_recompensas' => $calculateDetail('dom_recompensas_puntaje', $baremoDominios['recompensas']),
            // Dimensiones usando nombres correctos de columnas
            'dim_caracteristicas_liderazgo' => $calculateDetail('dim_caracteristicas_liderazgo_puntaje', $baremoDimensiones['caracteristicas_liderazgo']),
            'dim_relaciones_sociales' => $calculateDetail('dim_relaciones_sociales_puntaje', $baremoDimensiones['relaciones_sociales']),
            'dim_retroalimentacion' => $calculateDetail('dim_retroalimentacion_puntaje', $baremoDimensiones['retroalimentacion']),
            'dim_relacion_colaboradores' => $calculateDetail('dim_relacion_colaboradores_puntaje', $baremoDimensiones['relacion_colaboradores'] ?? $baremoDimensiones['caracteristicas_liderazgo']),
            'dim_claridad_rol' => $calculateDetail('dim_claridad_rol_puntaje', $baremoDimensiones['claridad_rol']),
            'dim_capacitacion' => $calculateDetail('dim_capacitacion_puntaje', $baremoDimensiones['capacitacion']),
            'dim_participacion_manejo_cambio' => $calculateDetail('dim_participacion_manejo_cambio_puntaje', $baremoDimensiones['participacion_cambio']),
            'dim_oportunidades_desarrollo' => $calculateDetail('dim_oportunidades_desarrollo_puntaje', $baremoDimensiones['oportunidades_desarrollo']),
            'dim_control_autonomia' => $calculateDetail('dim_control_autonomia_puntaje', $baremoDimensiones['control_autonomia']),
            'dim_demandas_ambientales' => $calculateDetail('dim_demandas_ambientales_puntaje', $baremoDimensiones['demandas_ambientales']),
            'dim_demandas_emocionales' => $calculateDetail('dim_demandas_emocionales_puntaje', $baremoDimensiones['demandas_emocionales']),
            'dim_demandas_cuantitativas' => $calculateDetail('dim_demandas_cuantitativas_puntaje', $baremoDimensiones['demandas_cuantitativas']),
            'dim_influencia_trabajo_entorno_extralaboral' => $calculateDetail('dim_influencia_trabajo_entorno_extralaboral_puntaje', $baremoDimensiones['influencia_entorno']),
            'dim_demandas_responsabilidad' => $calculateDetail('dim_demandas_responsabilidad_puntaje', $baremoDimensiones['exigencias_responsabilidad'] ?? $baremoDimensiones['demandas_ambientales']),
            'dim_carga_mental' => $calculateDetail('dim_demandas_carga_mental_puntaje', $baremoDimensiones['carga_mental'] ?? $baremoDimensiones['demandas_ambientales']),
            'dim_consistencia_rol' => $calculateDetail('dim_consistencia_rol_puntaje', $baremoDimensiones['consistencia_rol'] ?? $baremoDimensiones['demandas_ambientales']),
            'dim_demandas_jornada_trabajo' => $calculateDetail('dim_demandas_jornada_trabajo_puntaje', $baremoDimensiones['demandas_jornada']),
            'dim_recompensas_pertenencia' => $calculateDetail('dim_recompensas_pertenencia_puntaje', $baremoDimensiones['recompensas_pertenencia']),
            'dim_reconocimiento_compensacion' => $calculateDetail('dim_reconocimiento_compensacion_puntaje', $baremoDimensiones['reconocimiento_compensacion']),
        ];
    }

    /**
     * Calcula el mapa de calor extralaboral por forma
     */
    protected function calculateExtralaboralHeatmap($results, $forma)
    {
        $filteredResults = array_filter($results, function($r) use ($forma) {
            return strtoupper($r['intralaboral_form_type'] ?? '') === $forma;
        });

        if (empty($filteredResults)) {
            return $this->getEmptyExtralaboralStructure();
        }

        $cargoType = $forma === 'A' ? 'jefes' : 'auxiliares';

        // Baremos Extralaboral según tipo de cargo (Tabla 17 y 18)
        $baremoExtralaboralTotal = $cargoType === 'jefes'
            ? [
                'sin_riesgo' => [0.0, 11.3], 'riesgo_bajo' => [11.4, 16.9],
                'riesgo_medio' => [17.0, 22.6], 'riesgo_alto' => [22.7, 29.0],
                'riesgo_muy_alto' => [29.1, 100.0]
            ]
            : [
                'sin_riesgo' => [0.0, 12.9], 'riesgo_bajo' => [13.0, 17.7],
                'riesgo_medio' => [17.8, 24.2], 'riesgo_alto' => [24.3, 32.3],
                'riesgo_muy_alto' => [32.4, 100.0]
            ];

        // Baremos de dimensiones extralaborales
        $baremoDimensionesExtra = $cargoType === 'jefes'
            ? [
                'tiempo_fuera' => ['sin_riesgo' => [0.0, 6.3], 'riesgo_bajo' => [6.4, 25.0], 'riesgo_medio' => [25.1, 37.5], 'riesgo_alto' => [37.6, 50.0], 'riesgo_muy_alto' => [50.1, 100.0]],
                'relaciones_familiares' => ['sin_riesgo' => [0.0, 8.3], 'riesgo_bajo' => [8.4, 25.0], 'riesgo_medio' => [25.1, 33.3], 'riesgo_alto' => [33.4, 50.0], 'riesgo_muy_alto' => [50.1, 100.0]],
                'comunicacion' => ['sin_riesgo' => [0.0, 0.9], 'riesgo_bajo' => [1.0, 10.0], 'riesgo_medio' => [10.1, 20.0], 'riesgo_alto' => [20.1, 30.0], 'riesgo_muy_alto' => [30.1, 100.0]],
                'situacion_economica' => ['sin_riesgo' => [0.0, 8.3], 'riesgo_bajo' => [8.4, 25.0], 'riesgo_medio' => [25.1, 33.3], 'riesgo_alto' => [33.4, 50.0], 'riesgo_muy_alto' => [50.1, 100.0]],
                'caracteristicas_vivienda' => ['sin_riesgo' => [0.0, 5.6], 'riesgo_bajo' => [5.7, 11.1], 'riesgo_medio' => [11.2, 13.9], 'riesgo_alto' => [14.0, 22.2], 'riesgo_muy_alto' => [22.3, 100.0]],
                'influencia_entorno' => ['sin_riesgo' => [0.0, 8.3], 'riesgo_bajo' => [8.4, 16.7], 'riesgo_medio' => [16.8, 25.0], 'riesgo_alto' => [25.1, 41.7], 'riesgo_muy_alto' => [41.8, 100.0]],
                'desplazamiento' => ['sin_riesgo' => [0.0, 0.9], 'riesgo_bajo' => [1.0, 12.5], 'riesgo_medio' => [12.6, 25.0], 'riesgo_alto' => [25.1, 43.8], 'riesgo_muy_alto' => [43.9, 100.0]]
            ]
            : [
                'tiempo_fuera' => ['sin_riesgo' => [0.0, 6.3], 'riesgo_bajo' => [6.4, 25.0], 'riesgo_medio' => [25.1, 37.5], 'riesgo_alto' => [37.6, 50.0], 'riesgo_muy_alto' => [50.1, 100.0]],
                'relaciones_familiares' => ['sin_riesgo' => [0.0, 8.3], 'riesgo_bajo' => [8.4, 25.0], 'riesgo_medio' => [25.1, 33.3], 'riesgo_alto' => [33.4, 50.0], 'riesgo_muy_alto' => [50.1, 100.0]],
                'comunicacion' => ['sin_riesgo' => [0.0, 5.0], 'riesgo_bajo' => [5.1, 15.0], 'riesgo_medio' => [15.1, 25.0], 'riesgo_alto' => [25.1, 35.0], 'riesgo_muy_alto' => [35.1, 100.0]],
                'situacion_economica' => ['sin_riesgo' => [0.0, 16.7], 'riesgo_bajo' => [16.8, 25.0], 'riesgo_medio' => [25.1, 41.7], 'riesgo_alto' => [41.8, 50.0], 'riesgo_muy_alto' => [50.1, 100.0]],
                'caracteristicas_vivienda' => ['sin_riesgo' => [0.0, 5.6], 'riesgo_bajo' => [5.7, 11.1], 'riesgo_medio' => [11.2, 16.7], 'riesgo_alto' => [16.8, 27.8], 'riesgo_muy_alto' => [27.9, 100.0]],
                'influencia_entorno' => ['sin_riesgo' => [0.0, 0.9], 'riesgo_bajo' => [1.0, 16.7], 'riesgo_medio' => [16.8, 25.0], 'riesgo_alto' => [25.1, 41.7], 'riesgo_muy_alto' => [41.8, 100.0]],
                'desplazamiento' => ['sin_riesgo' => [0.0, 0.9], 'riesgo_bajo' => [1.0, 12.5], 'riesgo_medio' => [12.6, 25.0], 'riesgo_alto' => [25.1, 43.8], 'riesgo_muy_alto' => [43.9, 100.0]]
            ];

        $calculateDetail = function($field, $baremo) use ($filteredResults) {
            $puntajes = array_filter(array_column($filteredResults, $field), function($v) {
                return $v !== null && $v !== '';
            });

            if (empty($puntajes)) {
                return ['promedio' => 0, 'nivel' => 'sin_riesgo', 'cantidad' => 0];
            }

            $promedio = array_sum($puntajes) / count($puntajes);
            $nivel = $this->determinarNivelConBaremo($promedio, $baremo);

            return [
                'promedio' => round($promedio, 2),
                'nivel' => $nivel,
                'cantidad' => count($puntajes)
            ];
        };

        return [
            'extralaboral_total' => $calculateDetail('extralaboral_total_puntaje', $baremoExtralaboralTotal),
            'dim_tiempo_fuera' => $calculateDetail('extralaboral_tiempo_fuera_puntaje', $baremoDimensionesExtra['tiempo_fuera']),
            'dim_relaciones_familiares' => $calculateDetail('extralaboral_relaciones_familiares_puntaje', $baremoDimensionesExtra['relaciones_familiares']),
            'dim_comunicacion_relaciones' => $calculateDetail('extralaboral_comunicacion_puntaje', $baremoDimensionesExtra['comunicacion']),
            'dim_situacion_economica' => $calculateDetail('extralaboral_situacion_economica_puntaje', $baremoDimensionesExtra['situacion_economica']),
            'dim_caracteristicas_vivienda' => $calculateDetail('extralaboral_caracteristicas_vivienda_puntaje', $baremoDimensionesExtra['caracteristicas_vivienda']),
            'dim_influencia_entorno' => $calculateDetail('extralaboral_influencia_entorno_puntaje', $baremoDimensionesExtra['influencia_entorno']),
            'dim_desplazamiento' => $calculateDetail('extralaboral_desplazamiento_puntaje', $baremoDimensionesExtra['desplazamiento']),
        ];
    }

    /**
     * Calcula el mapa de calor de estrés por forma - incluye tabla de síntomas/preguntas
     */
    protected function calculateEstresHeatmap($results, $forma, $batteryServiceId)
    {
        $filteredResults = array_filter($results, function($r) use ($forma) {
            return strtoupper($r['intralaboral_form_type'] ?? '') === $forma;
        });

        if (empty($filteredResults)) {
            return $this->getEmptyEstresStructure();
        }

        $cargoType = $forma === 'A' ? 'jefes' : 'auxiliares';

        // Baremos Estrés según tipo de cargo (Tabla 6)
        $baremoEstres = $cargoType === 'jefes'
            ? [
                'muy_bajo' => [0.0, 7.8], 'bajo' => [7.9, 12.6], 'medio' => [12.7, 17.7],
                'alto' => [17.8, 25.0], 'muy_alto' => [25.1, 100.0]
            ]
            : [
                'muy_bajo' => [0.0, 6.5], 'bajo' => [6.6, 11.8], 'medio' => [11.9, 17.0],
                'alto' => [17.1, 23.4], 'muy_alto' => [23.5, 100.0]
            ];

        // Calcular total estrés
        $puntajesEstres = array_filter(array_column($filteredResults, 'estres_total_puntaje'), function($v) {
            return $v !== null && $v !== '';
        });

        $promedioEstres = !empty($puntajesEstres) ? array_sum($puntajesEstres) / count($puntajesEstres) : 0;
        $nivelEstres = $this->determinarNivelConBaremo($promedioEstres, $baremoEstres);

        // Obtener respuestas de estrés para la tabla de síntomas
        $symptomData = $this->getEstresSymptomData($batteryServiceId, $forma);

        return [
            'estres_total' => [
                'promedio' => round($promedioEstres, 2),
                'nivel' => $nivelEstres,
                'cantidad' => count($puntajesEstres)
            ],
            'symptom_data' => $symptomData,
        ];
    }

    /**
     * Obtiene los datos de síntomas de estrés (31 preguntas) para una forma específica
     */
    protected function getEstresSymptomData($batteryServiceId, $forma)
    {
        $db = \Config\Database::connect();

        // Obtener worker_ids para la forma específica
        $workerIds = $db->query("
            SELECT worker_id FROM calculated_results
            WHERE battery_service_id = ? AND intralaboral_form_type = ?
        ", [$batteryServiceId, $forma])->getResultArray();

        if (empty($workerIds)) {
            return [];
        }

        $workerIdList = array_column($workerIds, 'worker_id');

        // Obtener respuestas de estrés desde la tabla responses
        $responses = $db->query("
            SELECT r.worker_id, r.question_number, r.answer_value
            FROM responses r
            WHERE r.form_type = 'estres'
            AND r.worker_id IN (" . implode(',', $workerIdList) . ")
            ORDER BY r.question_number
        ")->getResultArray();

        // Textos de las 31 preguntas del cuestionario de estrés
        $estresQuestions = [
            1 => 'Dolores en el cuello y espalda o tensión muscular',
            2 => 'Problemas gastrointestinales, úlcera péptica, acidez, problemas digestivos o del colon',
            3 => 'Problemas respiratorios',
            4 => 'Dolor de cabeza',
            5 => 'Trastornos del sueño como somnolencia durante el día o desvelo en la noche',
            6 => 'Palpitaciones en el pecho o problemas cardíacos',
            7 => 'Cambios fuertes del apetito',
            8 => 'Problemas relacionados con la función de los órganos genitales (impotencia, frigidez)',
            9 => 'Dificultad en las relaciones familiares',
            10 => 'Dificultad para permanecer quieto o dificultad para iniciar actividades',
            11 => 'Dificultad en las relaciones con otras personas',
            12 => 'Sensación de aislamiento y desinterés',
            13 => 'Sentimiento de sobrecarga de trabajo',
            14 => 'Dificultad para concentrarse, olvidos frecuentes',
            15 => 'Aumento en el número de accidentes de trabajo',
            16 => 'Sentimiento de frustración, de no haber hecho lo que se quería en la vida',
            17 => 'Cansancio, tedio o desgano',
            18 => 'Disminución del rendimiento en el trabajo o poca creatividad',
            19 => 'Deseo de no asistir al trabajo',
            20 => 'Bajo compromiso o poco interés con lo que se hace',
            21 => 'Dificultad para tomar decisiones',
            22 => 'Deseo de cambiar de empleo',
            23 => 'Sentimiento de soledad y miedo',
            24 => 'Sentimiento de irritabilidad, actitudes y pensamientos negativos',
            25 => 'Sentimiento de angustia, preocupación o tristeza',
            26 => 'Consumo de drogas para aliviar la tensión o los nervios',
            27 => 'Sentimientos de que "no vale nada", o "no sirve para nada"',
            28 => 'Consumo de bebidas alcohólicas o café o cigarrillo',
            29 => 'Sentimiento de que está perdiendo la razón',
            30 => 'Comportamientos rígidos, obstinación o terquedad',
            31 => 'Sensación de no poder manejar los problemas de la vida'
        ];

        // Inicializar contadores para cada pregunta
        $symptomCounts = [];
        for ($q = 1; $q <= 31; $q++) {
            $symptomCounts[$q] = [
                'question' => $estresQuestions[$q],
                'siempre' => 0,
                'casi_siempre' => 0,
                'a_veces' => 0,
                'nunca' => 0,
                'total' => 0
            ];
        }

        // Grupos de preguntas según la Tabla 4 de calificación
        $grupo1 = [1, 2, 3, 9, 13, 14, 15, 23, 24];
        $grupo2 = [4, 5, 6, 10, 11, 16, 17, 18, 19, 25, 26, 27, 28];
        $grupo3 = [7, 8, 12, 20, 21, 22, 29, 30, 31];

        // Contar respuestas
        foreach ($responses as $response) {
            $q = (int)$response['question_number'];
            $value = (int)$response['answer_value'];

            if (!isset($symptomCounts[$q])) continue;

            // Convertir puntaje numérico a frecuencia
            $frecuencia = null;

            if (in_array($q, $grupo1)) {
                if ($value === 9) $frecuencia = 'siempre';
                elseif ($value === 6) $frecuencia = 'casi_siempre';
                elseif ($value === 3) $frecuencia = 'a_veces';
                elseif ($value === 0) $frecuencia = 'nunca';
            } elseif (in_array($q, $grupo2)) {
                if ($value === 6) $frecuencia = 'siempre';
                elseif ($value === 4) $frecuencia = 'casi_siempre';
                elseif ($value === 2) $frecuencia = 'a_veces';
                elseif ($value === 0) $frecuencia = 'nunca';
            } elseif (in_array($q, $grupo3)) {
                if ($value === 3) $frecuencia = 'siempre';
                elseif ($value === 2) $frecuencia = 'casi_siempre';
                elseif ($value === 1) $frecuencia = 'a_veces';
                elseif ($value === 0) $frecuencia = 'nunca';
            }

            if ($frecuencia && isset($symptomCounts[$q][$frecuencia])) {
                $symptomCounts[$q][$frecuencia]++;
                $symptomCounts[$q]['total']++;
            }
        }

        // Calcular crítico (Siempre + Casi Siempre) para cada pregunta
        foreach ($symptomCounts as $q => &$data) {
            $data['critico'] = $data['siempre'] + $data['casi_siempre'];
        }

        return $symptomCounts;
    }

    /**
     * Determina el nivel de riesgo usando un baremo específico
     */
    protected function determinarNivelConBaremo($puntaje, $baremo)
    {
        foreach ($baremo as $nivel => $rango) {
            if ($puntaje >= $rango[0] && $puntaje <= $rango[1]) {
                return $nivel;
            }
        }
        return 'sin_riesgo';
    }

    /**
     * Estructuras vacías para cuando no hay datos
     */
    protected function getEmptyHeatmapStructure()
    {
        return [
            'intralaboral_total' => ['promedio' => 0, 'nivel' => 'sin_riesgo', 'cantidad' => 0],
            'extralaboral_total' => ['promedio' => 0, 'nivel' => 'sin_riesgo', 'cantidad' => 0],
            'estres_total' => ['promedio' => 0, 'nivel' => 'muy_bajo', 'cantidad' => 0],
        ];
    }

    protected function getEmptyIntralaboralStructure()
    {
        $empty = ['promedio' => 0, 'nivel' => 'sin_riesgo', 'cantidad' => 0];
        return [
            'intralaboral_total' => $empty,
            'dom_liderazgo' => $empty,
            'dom_control' => $empty,
            'dom_demandas' => $empty,
            'dom_recompensas' => $empty,
            'dim_caracteristicas_liderazgo' => $empty,
            'dim_relaciones_sociales' => $empty,
            'dim_retroalimentacion' => $empty,
            'dim_relacion_colaboradores' => $empty,
            'dim_claridad_rol' => $empty,
            'dim_capacitacion' => $empty,
            'dim_participacion_manejo_cambio' => $empty,
            'dim_oportunidades_desarrollo' => $empty,
            'dim_control_autonomia' => $empty,
            'dim_demandas_ambientales' => $empty,
            'dim_demandas_emocionales' => $empty,
            'dim_demandas_cuantitativas' => $empty,
            'dim_influencia_trabajo_entorno_extralaboral' => $empty,
            'dim_demandas_responsabilidad' => $empty,
            'dim_carga_mental' => $empty,
            'dim_consistencia_rol' => $empty,
            'dim_demandas_jornada_trabajo' => $empty,
            'dim_recompensas_pertenencia' => $empty,
            'dim_reconocimiento_compensacion' => $empty,
        ];
    }

    protected function getEmptyExtralaboralStructure()
    {
        $empty = ['promedio' => 0, 'nivel' => 'sin_riesgo', 'cantidad' => 0];
        return [
            'extralaboral_total' => $empty,
            'dim_tiempo_fuera' => $empty,
            'dim_relaciones_familiares' => $empty,
            'dim_comunicacion_relaciones' => $empty,
            'dim_situacion_economica' => $empty,
            'dim_caracteristicas_vivienda' => $empty,
            'dim_influencia_entorno' => $empty,
            'dim_desplazamiento' => $empty,
        ];
    }

    protected function getEmptyEstresStructure()
    {
        return [
            'estres_total' => ['promedio' => 0, 'nivel' => 'muy_bajo', 'cantidad' => 0],
            'symptom_data' => [],
        ];
    }

    /**
     * Determina el nivel de riesgo general
     */
    protected function determinarNivelRiesgoGeneral($heatmapData)
    {
        $nivel = $heatmapData['intralaboral_total']['nivel'] ?? 'sin_riesgo';

        return [
            'nivel' => $nivel,
            'texto' => $this->getNivelTexto($nivel),
            'color' => $this->getRiskColor($nivel),
        ];
    }

    /**
     * Obtiene el texto del nivel de riesgo
     */
    protected function getNivelTexto($nivel)
    {
        $textos = [
            'sin_riesgo' => 'Sin Riesgo',
            'riesgo_bajo' => 'Riesgo Bajo',
            'riesgo_medio' => 'Riesgo Medio',
            'riesgo_alto' => 'Riesgo Alto',
            'riesgo_muy_alto' => 'Riesgo Muy Alto',
            'muy_bajo' => 'Muy Bajo',
            'bajo' => 'Bajo',
            'medio' => 'Medio',
            'alto' => 'Alto',
            'muy_alto' => 'Muy Alto',
        ];

        return $textos[$nivel] ?? 'No Definido';
    }

    /**
     * Preview de todos los mapas de calor
     */
    public function preview($batteryServiceId)
    {
        $html = $this->render($batteryServiceId);

        return view('pdf/_partials/preview_wrapper', [
            'content' => $html,
            'pageTitle' => 'Preview: Mapas de Calor',
            'batteryServiceId' => $batteryServiceId
        ]);
    }
}
