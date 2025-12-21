<?php

namespace App\Controllers\PdfEjecutivo;

use App\Libraries\IntralaboralAScoring;
use App\Libraries\IntralaboralBScoring;
use App\Libraries\ExtralaboralScoring;
use App\Libraries\EstresScoring;

/**
 * Controlador de Mapas de Calor para el Informe Ejecutivo PDF
 * Muestra distribución de riesgo por cuestionario y forma (A/B)
 * Incluye dominios, dimensiones y síntomas de estrés
 *
 * NOTA BAREMOS: Los totales (intralaboral, extralaboral, estrés) usan baremos
 * de las librerías autorizadas (Single Source of Truth). Los baremos de
 * dominios/dimensiones en el mapa GENERAL son aproximaciones para visualización
 * mixta (cuando se combinan formas A y B). Los mapas por forma específica
 * usan niveles pre-calculados de la BD.
 */
class MapasCalorController extends PdfEjecutivoBaseController
{
    protected $heatmapData = null;
    protected $detailedData = null;
    protected $symptomData = null;

    /**
     * Colores para estrés (nomenclatura diferente)
     */
    protected $stressColors = [
        'muy_bajo'  => '#4CAF50',
        'bajo'      => '#8BC34A',
        'medio'     => '#FFC107',
        'alto'      => '#FF9800',
        'muy_alto'  => '#F44336',
    ];

    /**
     * Nombres legibles para niveles de estrés
     */
    protected $stressNames = [
        'muy_bajo'  => 'Muy Bajo',
        'bajo'      => 'Bajo',
        'medio'     => 'Medio',
        'alto'      => 'Alto',
        'muy_alto'  => 'Muy Alto',
    ];

    /**
     * Dominios Forma A (19 dimensiones)
     */
    protected $dominiosFormaA = [
        [
            'nombre' => 'LIDERAZGO Y RELACIONES SOCIALES EN EL TRABAJO',
            'key' => 'dom_liderazgo',
            'dimensiones' => [
                ['key' => 'dim_caracteristicas_liderazgo', 'nombre' => 'Características del liderazgo'],
                ['key' => 'dim_relaciones_sociales', 'nombre' => 'Relaciones sociales en el trabajo'],
                ['key' => 'dim_retroalimentacion', 'nombre' => 'Retroalimentación del desempeño'],
                ['key' => 'dim_relacion_colaboradores', 'nombre' => 'Relación con los colaboradores'],
            ]
        ],
        [
            'nombre' => 'CONTROL SOBRE EL TRABAJO',
            'key' => 'dom_control',
            'dimensiones' => [
                ['key' => 'dim_claridad_rol', 'nombre' => 'Claridad de rol'],
                ['key' => 'dim_capacitacion', 'nombre' => 'Capacitación'],
                ['key' => 'dim_participacion_manejo_cambio', 'nombre' => 'Participación y manejo del cambio'],
                ['key' => 'dim_oportunidades_desarrollo', 'nombre' => 'Oportunidades desarrollo habilidades'],
                ['key' => 'dim_control_autonomia', 'nombre' => 'Control y autonomía sobre el trabajo'],
            ]
        ],
        [
            'nombre' => 'DEMANDAS DEL TRABAJO',
            'key' => 'dom_demandas',
            'dimensiones' => [
                ['key' => 'dim_demandas_ambientales', 'nombre' => 'Demandas ambientales y esfuerzo físico'],
                ['key' => 'dim_demandas_emocionales', 'nombre' => 'Demandas emocionales'],
                ['key' => 'dim_demandas_cuantitativas', 'nombre' => 'Demandas cuantitativas'],
                ['key' => 'dim_influencia_trabajo_entorno_extralaboral', 'nombre' => 'Influencia trabajo sobre entorno extra'],
                ['key' => 'dim_demandas_responsabilidad', 'nombre' => 'Exigencias de responsabilidad del cargo'],
                ['key' => 'dim_demandas_carga_mental', 'nombre' => 'Demandas de carga mental'],
                ['key' => 'dim_consistencia_rol', 'nombre' => 'Consistencia del rol'],
                ['key' => 'dim_demandas_jornada_trabajo', 'nombre' => 'Demandas de la jornada de trabajo'],
            ]
        ],
        [
            'nombre' => 'RECOMPENSAS',
            'key' => 'dom_recompensas',
            'dimensiones' => [
                ['key' => 'dim_recompensas_pertenencia', 'nombre' => 'Recompensas pertenencia organización'],
                ['key' => 'dim_reconocimiento_compensacion', 'nombre' => 'Reconocimiento y compensación'],
            ]
        ],
    ];

    /**
     * Dominios Forma B (16 dimensiones - sin las 3 exclusivas de A)
     */
    protected $dominiosFormaB = [
        [
            'nombre' => 'LIDERAZGO Y RELACIONES SOCIALES EN EL TRABAJO',
            'key' => 'dom_liderazgo',
            'dimensiones' => [
                ['key' => 'dim_caracteristicas_liderazgo', 'nombre' => 'Características del liderazgo'],
                ['key' => 'dim_relaciones_sociales', 'nombre' => 'Relaciones sociales en el trabajo'],
                ['key' => 'dim_retroalimentacion', 'nombre' => 'Retroalimentación del desempeño'],
            ]
        ],
        [
            'nombre' => 'CONTROL SOBRE EL TRABAJO',
            'key' => 'dom_control',
            'dimensiones' => [
                ['key' => 'dim_claridad_rol', 'nombre' => 'Claridad de rol'],
                ['key' => 'dim_capacitacion', 'nombre' => 'Capacitación'],
                ['key' => 'dim_participacion_manejo_cambio', 'nombre' => 'Participación y manejo del cambio'],
                ['key' => 'dim_oportunidades_desarrollo', 'nombre' => 'Oportunidades desarrollo habilidades'],
                ['key' => 'dim_control_autonomia', 'nombre' => 'Control y autonomía sobre el trabajo'],
            ]
        ],
        [
            'nombre' => 'DEMANDAS DEL TRABAJO',
            'key' => 'dom_demandas',
            'dimensiones' => [
                ['key' => 'dim_demandas_ambientales', 'nombre' => 'Demandas ambientales y esfuerzo físico'],
                ['key' => 'dim_demandas_emocionales', 'nombre' => 'Demandas emocionales'],
                ['key' => 'dim_demandas_cuantitativas', 'nombre' => 'Demandas cuantitativas'],
                ['key' => 'dim_influencia_trabajo_entorno_extralaboral', 'nombre' => 'Influencia trabajo sobre entorno extra'],
                ['key' => 'dim_demandas_carga_mental', 'nombre' => 'Demandas de carga mental'],
                ['key' => 'dim_demandas_jornada_trabajo', 'nombre' => 'Demandas de la jornada de trabajo'],
            ]
        ],
        [
            'nombre' => 'RECOMPENSAS',
            'key' => 'dom_recompensas',
            'dimensiones' => [
                ['key' => 'dim_recompensas_pertenencia', 'nombre' => 'Recompensas pertenencia organización'],
                ['key' => 'dim_reconocimiento_compensacion', 'nombre' => 'Reconocimiento y compensación'],
            ]
        ],
    ];

    /**
     * Dimensiones extralaborales (7 dimensiones)
     */
    protected $dimensionesExtralaboral = [
        ['key' => 'extralaboral_tiempo_fuera', 'nombre' => 'Tiempo fuera del trabajo'],
        ['key' => 'extralaboral_relaciones_familiares', 'nombre' => 'Relaciones familiares'],
        ['key' => 'extralaboral_comunicacion', 'nombre' => 'Comunicación y relaciones interpersonales'],
        ['key' => 'extralaboral_situacion_economica', 'nombre' => 'Situación económica del grupo familiar'],
        ['key' => 'extralaboral_caracteristicas_vivienda', 'nombre' => 'Características de la vivienda y entorno'],
        ['key' => 'extralaboral_influencia_entorno', 'nombre' => 'Influencia del entorno extralaboral'],
        ['key' => 'extralaboral_desplazamiento', 'nombre' => 'Desplazamiento vivienda - trabajo - vivienda'],
    ];

    /**
     * Las 31 preguntas de estrés
     */
    protected $estresQuestions = [
        1 => 'Dolores en el cuello y espalda o tensión muscular',
        2 => 'Problemas gastrointestinales, úlcera péptica, acidez, problemas digestivos o del colon',
        3 => 'Problemas respiratorios',
        4 => 'Dolor de cabeza',
        5 => 'Trastornos del sueño como somnolencia durante el día o desvelo en la noche',
        6 => 'Palpitaciones en el pecho o problemas cardíacos',
        7 => 'Cambios fuertes del apetito',
        8 => 'Problemas relacionados con la función de los órganos genitales',
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
        31 => 'Sensación de no poder manejar los problemas de la vida',
    ];

    /**
     * Preview de los mapas de calor en navegador
     */
    public function preview($batteryServiceId)
    {
        $this->initializeData($batteryServiceId);
        $this->loadAllData($batteryServiceId);
        $html = $this->render($batteryServiceId);

        return $this->generatePreview($html, 'Preview: Mapas de Calor');
    }

    /**
     * Descarga PDF de los mapas de calor
     */
    public function download($batteryServiceId)
    {
        $this->initializeData($batteryServiceId);
        $this->loadAllData($batteryServiceId);
        $html = $this->render($batteryServiceId);

        return $this->generatePdf($html, "MapasCalor.pdf");
    }

    /**
     * Carga todos los datos necesarios
     */
    protected function loadAllData($batteryServiceId)
    {
        $this->loadHeatmapData($batteryServiceId);
        $this->loadDetailedData($batteryServiceId);
        $this->loadSymptomData($batteryServiceId);
    }

    /**
     * Carga datos de calculated_results para el mapa de calor general
     */
    protected function loadHeatmapData($batteryServiceId)
    {
        $db = \Config\Database::connect();

        $query = $db->query("
            SELECT
                intralaboral_form_type,
                intralaboral_total_nivel,
                extralaboral_total_nivel,
                estres_total_nivel
            FROM calculated_results
            WHERE battery_service_id = ?
        ", [$batteryServiceId]);

        $results = $query->getResultArray();

        // Inicializar estructura
        $this->heatmapData = [
            'intralaboral' => [
                'A' => $this->initRiskCounts(),
                'B' => $this->initRiskCounts(),
            ],
            'extralaboral' => [
                'A' => $this->initRiskCounts(),
                'B' => $this->initRiskCounts(),
            ],
            'estres' => [
                'A' => $this->initEstresRiskCounts(),
                'B' => $this->initEstresRiskCounts(),
            ],
            'total_a' => 0,
            'total_b' => 0,
            'total' => count($results),
        ];

        // Procesar resultados
        foreach ($results as $row) {
            $forma = $row['intralaboral_form_type'];

            if ($forma === 'A') {
                $this->heatmapData['total_a']++;
            } else {
                $this->heatmapData['total_b']++;
            }

            // Intralaboral
            $nivel = $row['intralaboral_total_nivel'] ?? 'sin_riesgo';
            if (isset($this->heatmapData['intralaboral'][$forma][$nivel])) {
                $this->heatmapData['intralaboral'][$forma][$nivel]++;
            }

            // Extralaboral
            $nivel = $row['extralaboral_total_nivel'] ?? 'sin_riesgo';
            if (isset($this->heatmapData['extralaboral'][$forma][$nivel])) {
                $this->heatmapData['extralaboral'][$forma][$nivel]++;
            }

            // Estrés
            $nivel = $row['estres_total_nivel'] ?? 'muy_bajo';
            if (isset($this->heatmapData['estres'][$forma][$nivel])) {
                $this->heatmapData['estres'][$forma][$nivel]++;
            }
        }
    }

    /**
     * Carga datos detallados de dominios y dimensiones
     */
    protected function loadDetailedData($batteryServiceId)
    {
        $db = \Config\Database::connect();

        $query = $db->query("
            SELECT
                intralaboral_form_type,
                intralaboral_total_puntaje,
                intralaboral_total_nivel,
                dom_liderazgo_puntaje,
                dom_liderazgo_nivel,
                dom_control_puntaje,
                dom_control_nivel,
                dom_demandas_puntaje,
                dom_demandas_nivel,
                dom_recompensas_puntaje,
                dom_recompensas_nivel,
                dim_caracteristicas_liderazgo_puntaje,
                dim_caracteristicas_liderazgo_nivel,
                dim_relaciones_sociales_puntaje,
                dim_relaciones_sociales_nivel,
                dim_retroalimentacion_puntaje,
                dim_retroalimentacion_nivel,
                dim_relacion_colaboradores_puntaje,
                dim_relacion_colaboradores_nivel,
                dim_claridad_rol_puntaje,
                dim_claridad_rol_nivel,
                dim_capacitacion_puntaje,
                dim_capacitacion_nivel,
                dim_participacion_manejo_cambio_puntaje,
                dim_participacion_manejo_cambio_nivel,
                dim_oportunidades_desarrollo_puntaje,
                dim_oportunidades_desarrollo_nivel,
                dim_control_autonomia_puntaje,
                dim_control_autonomia_nivel,
                dim_demandas_ambientales_puntaje,
                dim_demandas_ambientales_nivel,
                dim_demandas_emocionales_puntaje,
                dim_demandas_emocionales_nivel,
                dim_demandas_cuantitativas_puntaje,
                dim_demandas_cuantitativas_nivel,
                dim_influencia_trabajo_entorno_extralaboral_puntaje,
                dim_influencia_trabajo_entorno_extralaboral_nivel,
                dim_demandas_responsabilidad_puntaje,
                dim_demandas_responsabilidad_nivel,
                dim_demandas_carga_mental_puntaje,
                dim_demandas_carga_mental_nivel,
                dim_consistencia_rol_puntaje,
                dim_consistencia_rol_nivel,
                dim_demandas_jornada_trabajo_puntaje,
                dim_demandas_jornada_trabajo_nivel,
                dim_recompensas_pertenencia_puntaje,
                dim_recompensas_pertenencia_nivel,
                dim_reconocimiento_compensacion_puntaje,
                dim_reconocimiento_compensacion_nivel,
                extralaboral_total_puntaje,
                extralaboral_total_nivel,
                extralaboral_tiempo_fuera_puntaje,
                extralaboral_tiempo_fuera_nivel,
                extralaboral_relaciones_familiares_puntaje,
                extralaboral_relaciones_familiares_nivel,
                extralaboral_comunicacion_puntaje,
                extralaboral_comunicacion_nivel,
                extralaboral_situacion_economica_puntaje,
                extralaboral_situacion_economica_nivel,
                extralaboral_caracteristicas_vivienda_puntaje,
                extralaboral_caracteristicas_vivienda_nivel,
                extralaboral_influencia_entorno_puntaje,
                extralaboral_influencia_entorno_nivel,
                extralaboral_desplazamiento_puntaje,
                extralaboral_desplazamiento_nivel,
                estres_total_puntaje,
                estres_total_nivel
            FROM calculated_results
            WHERE battery_service_id = ?
        ", [$batteryServiceId]);

        $results = $query->getResultArray();

        $this->detailedData = [
            'A' => [],
            'B' => [],
        ];

        foreach ($results as $row) {
            $forma = $row['intralaboral_form_type'];
            $this->detailedData[$forma][] = $row;
        }
    }

    /**
     * Carga datos de síntomas de estrés (respuestas individuales)
     * Usa la tabla responses con form_type = 'estres'
     * Valores: 9=Siempre, 6=Casi siempre, 3=A veces, 0=Nunca
     */
    protected function loadSymptomData($batteryServiceId)
    {
        // Inicializar estructura vacía
        $this->symptomData = [
            'A' => [],
            'B' => [],
        ];

        // Inicializar conteos para cada forma
        foreach (['A', 'B'] as $forma) {
            for ($i = 1; $i <= 31; $i++) {
                $this->symptomData[$forma][$i] = [
                    'siempre' => 0,
                    'casi_siempre' => 0,
                    'a_veces' => 0,
                    'nunca' => 0,
                    'total' => 0,
                ];
            }
        }

        $db = \Config\Database::connect();

        try {
            // Obtener respuestas de estrés de la tabla responses
            $query = $db->query("
                SELECT
                    r.question_number,
                    r.answer_value,
                    cr.intralaboral_form_type
                FROM responses r
                JOIN calculated_results cr ON r.worker_id = cr.worker_id
                WHERE r.form_type = 'estres'
                AND cr.battery_service_id = ?
                ORDER BY cr.intralaboral_form_type, r.question_number
            ", [$batteryServiceId]);

            $results = $query->getResultArray();

            // Procesar respuestas
            foreach ($results as $row) {
                $forma = $row['intralaboral_form_type'];
                $pregunta = (int)$row['question_number'];
                $respuesta = (int)$row['answer_value'];

                if ($pregunta >= 1 && $pregunta <= 31 && isset($this->symptomData[$forma][$pregunta])) {
                    $this->symptomData[$forma][$pregunta]['total']++;

                    // Determinar frecuencia según Tabla 4 - Resolución 2764/2022
                    $frecuencia = $this->getFrecuenciaEstres($pregunta, $respuesta);

                    switch ($frecuencia) {
                        case 'siempre':
                            $this->symptomData[$forma][$pregunta]['siempre']++;
                            break;
                        case 'casi_siempre':
                            $this->symptomData[$forma][$pregunta]['casi_siempre']++;
                            break;
                        case 'a_veces':
                            $this->symptomData[$forma][$pregunta]['a_veces']++;
                            break;
                        case 'nunca':
                            $this->symptomData[$forma][$pregunta]['nunca']++;
                            break;
                    }
                }
            }
        } catch (\Exception $e) {
            // Si hay algún error, simplemente retornar con datos vacíos
            return;
        }
    }

    /**
     * Determina la frecuencia de respuesta según el ítem y el valor
     * Basado en la Tabla 4 de la Resolución 2764/2022
     *
     * Grupo 1 (ítems 1,2,3,9,13,14,15,23,24): Siempre=9, Casi siempre=6, A veces=3, Nunca=0
     * Grupo 2 (ítems 4,5,6,10,11,16,17,18,19,25,26,27,28): Siempre=6, Casi siempre=4, A veces=2, Nunca=0
     * Grupo 3 (ítems 7,8,12,20,21,22,29,30,31): Siempre=3, Casi siempre=2, A veces=1, Nunca=0
     */
    protected function getFrecuenciaEstres($item, $valor)
    {
        // Grupo 1: ítems con valores 9, 6, 3, 0
        $grupo1 = [1, 2, 3, 9, 13, 14, 15, 23, 24];
        // Grupo 2: ítems con valores 6, 4, 2, 0
        $grupo2 = [4, 5, 6, 10, 11, 16, 17, 18, 19, 25, 26, 27, 28];
        // Grupo 3: ítems con valores 3, 2, 1, 0
        $grupo3 = [7, 8, 12, 20, 21, 22, 29, 30, 31];

        if (in_array($item, $grupo1)) {
            // Grupo 1: Siempre=9, Casi siempre=6, A veces=3, Nunca=0
            switch ($valor) {
                case 9: return 'siempre';
                case 6: return 'casi_siempre';
                case 3: return 'a_veces';
                case 0: return 'nunca';
            }
        } elseif (in_array($item, $grupo2)) {
            // Grupo 2: Siempre=6, Casi siempre=4, A veces=2, Nunca=0
            switch ($valor) {
                case 6: return 'siempre';
                case 4: return 'casi_siempre';
                case 2: return 'a_veces';
                case 0: return 'nunca';
            }
        } elseif (in_array($item, $grupo3)) {
            // Grupo 3: Siempre=3, Casi siempre=2, A veces=1, Nunca=0
            switch ($valor) {
                case 3: return 'siempre';
                case 2: return 'casi_siempre';
                case 1: return 'a_veces';
                case 0: return 'nunca';
            }
        }

        return 'nunca'; // Por defecto
    }

    /**
     * Inicializa conteos de niveles de riesgo intralaboral/extralaboral
     */
    protected function initRiskCounts()
    {
        return [
            'sin_riesgo' => 0,
            'riesgo_bajo' => 0,
            'riesgo_medio' => 0,
            'riesgo_alto' => 0,
            'riesgo_muy_alto' => 0,
        ];
    }

    /**
     * Inicializa conteos de niveles de estrés
     */
    protected function initEstresRiskCounts()
    {
        return [
            'muy_bajo' => 0,
            'bajo' => 0,
            'medio' => 0,
            'alto' => 0,
            'muy_alto' => 0,
        ];
    }

    /**
     * Renderiza el HTML completo de mapas de calor
     */
    public function render($batteryServiceId)
    {
        if (empty($this->companyData)) {
            $this->initializeData($batteryServiceId);
            $this->loadAllData($batteryServiceId);
        }

        $html = '';

        // Página 1: Introducción a Resultados
        $html .= $this->renderIntroduccionResultados();

        // Página 2: Conclusiones de la Batería
        $html .= '<div class="page-break"></div>';
        $html .= $this->renderConclusiones();

        // Página 3: Encabezado y tabla de distribución general
        $html .= '<div class="page-break"></div>';
        $html .= $this->renderEncabezado();
        $html .= $this->renderResumen();
        $html .= $this->renderTablaMapaCalor();
        $html .= $this->renderLeyenda();

        // Página 4: Mapa de Calor Visual General (todos los dominios y dimensiones)
        $html .= '<div class="page-break"></div>';
        $html .= $this->renderMapaCalorVisualGeneral();

        // Página 5: Mapa visual intralaboral Forma A
        if ($this->heatmapData['total_a'] > 0) {
            $html .= '<div class="page-break"></div>';
            $html .= $this->renderMapaIntralaboral('A');
        }

        // Página 5: Mapa visual intralaboral Forma B
        if ($this->heatmapData['total_b'] > 0) {
            $html .= '<div class="page-break"></div>';
            $html .= $this->renderMapaIntralaboral('B');
        }

        // Página 6: Mapa extralaboral
        $html .= '<div class="page-break"></div>';
        $html .= $this->renderMapaExtralaboral();

        // Página 7: Tabla de síntomas de estrés Forma A
        if ($this->heatmapData['total_a'] > 0) {
            $html .= '<div class="page-break"></div>';
            $html .= $this->renderTablaSintomasEstres('A');
        }

        // Página 8: Tabla de síntomas de estrés Forma B
        if ($this->heatmapData['total_b'] > 0) {
            $html .= '<div class="page-break"></div>';
            $html .= $this->renderTablaSintomasEstres('B');
        }

        return $html;
    }

    /**
     * Página 1: Introducción a Resultados
     */
    protected function renderIntroduccionResultados()
    {
        $total = $this->heatmapData['total'];
        $totalA = $this->heatmapData['total_a'];
        $totalB = $this->heatmapData['total_b'];
        $pctA = $total > 0 ? round(($totalA / $total) * 100) : 0;
        $pctB = $total > 0 ? round(($totalB / $total) * 100) : 0;
        $nombreEmpresa = esc($this->companyData['company_name'] ?? 'la empresa');

        $html = '
<h1 style="font-size: 16pt; margin: 0 0 15pt 0; padding-bottom: 8pt; border-bottom: 2pt solid #006699;">Resultados</h1>

<p style="font-size: 10pt; text-align: justify; margin: 0 0 12pt 0; line-height: 1.5;">
Se contó con la participación de <strong>' . $total . '</strong> personas vinculadas a <strong>' . $nombreEmpresa . '</strong>, de los cuales <strong>' . $totalA . '</strong> personas son equivalentes al <strong>' . $pctA . '%</strong> con el cuestionario intralaboral Tipo A y <strong>' . $totalB . '</strong> personas fueron evaluados con el formato intralaboral Tipo B, equivalente al <strong>' . $pctB . '%</strong>. Al mismo tiempo, el 100% de los participantes diligenciaron el Cuestionario de evaluación riesgo psicosocial Intralaboral, extralaboral, el cuestionario de estrés y la ficha de datos generales; así como el consentimiento informado.
</p>

<p style="font-size: 10pt; text-align: justify; margin: 0 0 12pt 0; line-height: 1.5;">
Los resultados obtenidos se presentan en el siguiente orden:
</p>

<ol style="font-size: 10pt; margin: 0 0 20pt 20pt; padding: 0; line-height: 1.6;">
    <li style="margin-bottom: 5pt;">Resultados de las condiciones individuales – Información sociodemográfica y ocupacional.</li>
    <li style="margin-bottom: 5pt;">Resultados de la evaluación de factores de riesgo psicosocial (intralaboral, extralaboral).</li>
    <li style="margin-bottom: 5pt;">Resultados de la evaluación de estrés ocupacional.</li>
</ol>
';

        return $html;
    }

    /**
     * Página 2: Conclusiones de la Batería
     * Si existe una conclusión global generada por IA, la usa; sino, genera texto automático
     */
    protected function renderConclusiones()
    {
        $nombreEmpresa = esc($this->companyData['company_name'] ?? 'la empresa');

        // Verificar si existe conclusión global generada por IA
        $globalConclusion = $this->getGlobalConclusion();

        // Calcular promedios por forma
        $promediosA = $this->calcularPromediosForma('A');
        $promediosB = $this->calcularPromediosForma('B');

        // Puntajes y niveles intralaborales
        $puntajeIntraA = number_format($promediosA['intralaboral_total_puntaje'] ?? 0, 1);
        $nivelIntraA = $promediosA['intralaboral_total_nivel'] ?? 'sin_riesgo';
        $puntajeIntraB = number_format($promediosB['intralaboral_total_puntaje'] ?? 0, 1);
        $nivelIntraB = $promediosB['intralaboral_total_nivel'] ?? 'sin_riesgo';

        // Puntajes y niveles de estrés
        $puntajeEstresA = number_format($promediosA['estres_total_puntaje'] ?? 0, 1);
        $nivelEstresA = $promediosA['estres_total_nivel'] ?? 'muy_bajo';
        $puntajeEstresB = number_format($promediosB['estres_total_puntaje'] ?? 0, 1);
        $nivelEstresB = $promediosB['estres_total_nivel'] ?? 'muy_bajo';

        // Determinar nivel de riesgo general (el más alto entre A y B)
        $nivelRiesgoGeneral = $this->determinarNivelMasAlto($nivelIntraA, $nivelIntraB);
        $nivelEstresGeneral = $this->determinarNivelEstresMasAlto($nivelEstresA, $nivelEstresB);

        // Determinar periodicidad
        $periodicidadIntra = $this->getPeriodicidad($nivelRiesgoGeneral);
        $periodicidadEstres = $this->getPeriodicidadEstres($nivelEstresGeneral);

        // Nombres legibles
        $nombreNivelGeneral = $this->getRiskName($nivelRiesgoGeneral);
        $nombreNivelIntraA = $this->getRiskName($nivelIntraA);
        $nombreNivelIntraB = $this->getRiskName($nivelIntraB);
        $nombreNivelEstresGeneral = $this->stressNames[$nivelEstresGeneral] ?? $nivelEstresGeneral;
        $nombreNivelEstresA = $this->stressNames[$nivelEstresA] ?? $nivelEstresA;
        $nombreNivelEstresB = $this->stressNames[$nivelEstresB] ?? $nivelEstresB;

        // Color del nivel general
        $colorNivelGeneral = $this->getRiskColor($nivelRiesgoGeneral);
        $textColorGeneral = $nivelRiesgoGeneral === 'riesgo_medio' ? '#333' : '#fff';

        $html = '
<h1 style="font-size: 16pt; margin: 0 0 15pt 0; padding-bottom: 8pt; border-bottom: 2pt solid #006699;">Conclusión Total De Aplicación Batería De Riesgo Psicosocial</h1>
';

        // Si existe conclusión global generada por IA, mostrarla
        if (!empty($globalConclusion)) {
            // Convertir saltos de línea a párrafos HTML
            $paragraphs = explode("\n\n", $globalConclusion);
            foreach ($paragraphs as $paragraph) {
                $paragraph = trim($paragraph);
                if (!empty($paragraph)) {
                    // Convertir saltos de línea simples a <br>
                    $paragraph = nl2br(esc($paragraph));
                    $html .= '<p style="font-size: 10pt; text-align: justify; margin: 0 0 12pt 0; line-height: 1.6;">' . $paragraph . '</p>';
                }
            }
        } else {
            // Texto automático si no hay conclusión de IA
            $html .= '
<p style="font-size: 10pt; text-align: justify; margin: 0 0 12pt 0; line-height: 1.5;">
Los dominios principales de la batería de riesgo psicosocial como sus respectivas dimensiones son calificadas a partir de la interpretación del mayor puntaje obtenido, siendo este el factor determinante para establecer el panorama de riesgo psicosocial.
</p>

<p style="font-size: 10pt; text-align: justify; margin: 0 0 12pt 0; line-height: 1.5;">
Los resultados obtenidos del nivel de riesgo psicosocial a nivel general en <strong>' . $nombreEmpresa . '</strong> se clasifican en <span style="background-color: ' . $colorNivelGeneral . '; color: ' . $textColorGeneral . '; padding: 2pt 6pt; font-weight: bold;">' . $nombreNivelGeneral . '</span> (Cuestionario Tipo A = Calificación de <strong>' . $puntajeIntraA . '</strong> catalogado como <strong>' . $nombreNivelIntraA . '</strong>) / Cuestionario Tipo B con una calificación de <strong>' . $puntajeIntraB . '</strong> catalogado como <strong>' . $nombreNivelIntraB . '</strong>).
</p>

<p style="font-size: 10pt; text-align: justify; margin: 0 0 12pt 0; line-height: 1.5;">
Las dimensiones y dominios que se encuentren bajo esta categoría serán objeto de acciones o programas de intervención, a fin de mantenerlos en los niveles de riesgo más bajos posibles.
</p>
';
        }

        // Caja de periodicidad (siempre mostrar)
        $html .= '
<div style="background-color: #e8f4fc; border: 1pt solid #006699; padding: 10pt; margin: 15pt 0;">
    <p style="font-size: 10pt; margin: 0; line-height: 1.5;">
        <strong>Periodicidad de próxima medición:</strong> De acuerdo con el artículo 3 de la resolución 2764 del 2022, el periodo de la próxima medición se establece de acuerdo con el puntaje de la dimensión principal intralaboral observado anteriormente. Asimismo, se debe realizar una nueva medición en un plazo máximo de <strong>' . $periodicidadIntra . '</strong>.
    </p>
</div>
';

        // Solo mostrar sección "Conclusión Del Profesional" si NO hay conclusión de IA
        // (La conclusión de IA ya incluye el análisis profesional integrado)
        if (empty($globalConclusion)) {
            $html .= '
<h2 style="font-size: 13pt; color: #006699; margin: 20pt 0 10pt 0; padding-bottom: 5pt; border-bottom: 1pt solid #006699;">Conclusión Del Profesional</h2>

<p style="font-size: 10pt; text-align: justify; margin: 0 0 12pt 0; line-height: 1.5;">
El entorno de análisis de la batería de riesgo psicosocial consta de tres dimensiones principales las cuales constantemente interactúan entre sí; Al observar la perspectiva global se denota el nivel de <strong>' . $nombreNivelEstresGeneral . '</strong> en la dimensión principal de estrés (Cuestionario Tipo A = Calificación de <strong>' . $puntajeEstresA . '</strong> catalogado como <strong>' . $nombreNivelEstresA . '</strong> / Cuestionario Tipo B = Calificación de <strong>' . $puntajeEstresB . '</strong> catalogado como <strong>' . $nombreNivelEstresB . '</strong>).
</p>
';

            // Alerta si el nivel de estrés es alto o muy alto
            if (in_array($nivelEstresGeneral, ['alto', 'muy_alto'])) {
                $colorEstres = $this->stressColors[$nivelEstresGeneral];
                $html .= '
<div style="background-color: #ffebee; border-left: 4pt solid ' . $colorEstres . '; padding: 10pt; margin: 15pt 0;">
    <p style="font-size: 10pt; margin: 0; line-height: 1.5; color: #c62828;">
        <strong>Acción Inmediata Requerida:</strong> Se sugiere proceder mediante la Acción Inmediata del Programa de vigilancia epidemiológica: Concentración elevada de niveles de estrés sobre este grupo poblacional, Diseño de Programas De Vigilancia Epidemiológica En Riesgo Psicosocial. El factor de riesgo causa, o podría causar, alteraciones serias en la salud del trabajador, aumentando en consecuencia el número de incapacidades laborales.
    </p>
</div>
';
            }

            $html .= '
<p style="font-size: 10pt; text-align: justify; margin: 15pt 0 0 0; line-height: 1.5;">
Se sugiere realizar una nueva medición dentro de <strong>' . $periodicidadEstres . '</strong> en función de llevar a cabo un correcto seguimiento de efectividad en la dimensión estrés.
</p>
';
        }

        return $html;
    }

    /**
     * Obtiene la conclusión global generada por IA (si existe)
     */
    protected function getGlobalConclusion()
    {
        $db = \Config\Database::connect();
        $query = $db->query("
            SELECT global_conclusion_text
            FROM battery_services
            WHERE id = ?
        ", [$this->batteryServiceId]);

        $row = $query->getRowArray();
        return $row['global_conclusion_text'] ?? null;
    }

    /**
     * Determina el nivel de riesgo más alto entre dos niveles
     */
    protected function determinarNivelMasAlto($nivelA, $nivelB)
    {
        $orden = [
            'sin_riesgo' => 1,
            'riesgo_bajo' => 2,
            'riesgo_medio' => 3,
            'riesgo_alto' => 4,
            'riesgo_muy_alto' => 5,
        ];

        $ordenA = $orden[$nivelA] ?? 1;
        $ordenB = $orden[$nivelB] ?? 1;

        if ($ordenA >= $ordenB) {
            return $nivelA;
        }
        return $nivelB;
    }

    /**
     * Determina el nivel de estrés más alto entre dos niveles
     */
    protected function determinarNivelEstresMasAlto($nivelA, $nivelB)
    {
        $orden = [
            'muy_bajo' => 1,
            'bajo' => 2,
            'medio' => 3,
            'alto' => 4,
            'muy_alto' => 5,
        ];

        $ordenA = $orden[$nivelA] ?? 1;
        $ordenB = $orden[$nivelB] ?? 1;

        if ($ordenA >= $ordenB) {
            return $nivelA;
        }
        return $nivelB;
    }

    /**
     * Obtiene la periodicidad según el nivel de riesgo
     */
    protected function getPeriodicidad($nivel)
    {
        if (in_array($nivel, ['riesgo_alto', 'riesgo_muy_alto'])) {
            return 'un año';
        }
        return 'dos años';
    }

    /**
     * Obtiene la periodicidad según el nivel de estrés
     */
    protected function getPeriodicidadEstres($nivel)
    {
        if (in_array($nivel, ['alto', 'muy_alto'])) {
            return 'un año';
        }
        return 'dos años';
    }

    /**
     * Baremos genéricos para dominios y dimensiones en mapa GENERAL
     * NOTA: Son aproximaciones para visualización mixta (formas A+B combinadas).
     * Los mapas por forma específica usan niveles pre-calculados de la BD.
     */
    protected $baremosGenericos = [
        'dominio' => [
            'sin_riesgo' => [0, 19.9],
            'riesgo_bajo' => [20.0, 29.9],
            'riesgo_medio' => [30.0, 39.9],
            'riesgo_alto' => [40.0, 49.9],
            'riesgo_muy_alto' => [50.0, 100],
        ],
        'dimension' => [
            'sin_riesgo' => [0, 19.9],
            'riesgo_bajo' => [20.0, 29.9],
            'riesgo_medio' => [30.0, 39.9],
            'riesgo_alto' => [40.0, 49.9],
            'riesgo_muy_alto' => [50.0, 100],
        ],
    ];

    /**
     * Obtiene el nivel de riesgo según el puntaje y el tipo
     * Usa librerías autorizadas para totales, baremos genéricos para dominios/dimensiones
     */
    protected function getNivelPorPuntaje($puntaje, $tipoBaremo = 'dimension')
    {
        // BAREMOS - Desde fuente única autorizada (Single Source of Truth)
        $baremo = match ($tipoBaremo) {
            'intralaboral_total' => IntralaboralAScoring::getBaremoTotal(),
            'extralaboral_total' => ExtralaboralScoring::getBaremoTotal('A'),
            'estres_total' => EstresScoring::getBaremoA(),
            default => $this->baremosGenericos[$tipoBaremo] ?? $this->baremosGenericos['dimension'],
        };

        foreach ($baremo as $nivel => $rango) {
            if ($puntaje >= $rango[0] && $puntaje <= $rango[1]) {
                return $nivel;
            }
        }
        return 'sin_riesgo';
    }

    /**
     * Obtiene el color según el nivel de riesgo
     */
    protected function getColorPorNivel($nivel)
    {
        $colores = [
            'sin_riesgo' => '#4CAF50',
            'riesgo_bajo' => '#8BC34A',
            'riesgo_medio' => '#FFC107',
            'riesgo_alto' => '#FF9800',
            'riesgo_muy_alto' => '#F44336',
            'muy_bajo' => '#4CAF50',
            'bajo' => '#8BC34A',
            'medio' => '#FFC107',
            'alto' => '#FF9800',
            'muy_alto' => '#F44336',
        ];
        return $colores[$nivel] ?? '#4CAF50';
    }

    /**
     * Obtiene el color del texto según el nivel
     */
    protected function getTextColorPorNivel($nivel)
    {
        if (in_array($nivel, ['riesgo_medio', 'medio'])) {
            return '#333';
        }
        return '#fff';
    }

    /**
     * Carga promedios generales REUTILIZANDO ReportsController::calculateHeatmapWithDetails
     * Esto garantiza que los valores sean IDÉNTICOS a la vista heatmap
     */
    protected function loadPromediosGenerales($batteryServiceId)
    {
        $db = \Config\Database::connect();

        // Cargar todos los registros (igual que ReportsController::heatmap)
        $query = $db->query("
            SELECT *
            FROM calculated_results
            WHERE battery_service_id = ?
        ", [$batteryServiceId]);

        $results = $query->getResultArray();

        if (empty($results)) {
            return [];
        }

        // Usar ReportsController para calcular (Single Source of Truth)
        $reportsController = new \App\Controllers\ReportsController();
        $heatmapData = $reportsController->calculateHeatmapForPdf($results);

        // DEBUG: Log para verificar qué está retornando
        log_message('debug', 'PDF Heatmap - heatmapData: ' . json_encode([
            'is_null' => $heatmapData === null,
            'intralaboral_total' => $heatmapData['intralaboral_total'] ?? 'NOT_SET',
            'count_results' => count($results),
        ]));

        // Si heatmapData es null, retornar vacío
        if ($heatmapData === null) {
            return [];
        }

        // Convertir formato heatmap (usa 'promedio') a formato PDF (usa 'puntaje')
        $convertData = function($data) {
            if (!is_array($data) || !isset($data['promedio'])) {
                return ['puntaje' => 0, 'nivel' => 'sin_riesgo'];
            }
            return ['puntaje' => $data['promedio'], 'nivel' => $data['nivel']];
        };

        // Mapear claves del heatmap a claves del PDF
        return [
            // Totales
            'intralaboral_total' => $convertData($heatmapData['intralaboral_total'] ?? []),
            'extralaboral_total' => $convertData($heatmapData['extralaboral_total'] ?? []),
            'estres_total' => $convertData($heatmapData['estres_total'] ?? []),

            // Dominios
            'dom_liderazgo' => $convertData($heatmapData['dom_liderazgo'] ?? []),
            'dom_control' => $convertData($heatmapData['dom_control'] ?? []),
            'dom_demandas' => $convertData($heatmapData['dom_demandas'] ?? []),
            'dom_recompensas' => $convertData($heatmapData['dom_recompensas'] ?? []),

            // Dimensiones intralaborales
            'dim_caracteristicas_liderazgo' => $convertData($heatmapData['dim_caracteristicas_liderazgo'] ?? []),
            'dim_relaciones_sociales' => $convertData($heatmapData['dim_relaciones_sociales'] ?? []),
            'dim_retroalimentacion' => $convertData($heatmapData['dim_retroalimentacion'] ?? []),
            'dim_relacion_colaboradores' => $convertData($heatmapData['dim_relacion_colaboradores'] ?? []),
            'dim_claridad_rol' => $convertData($heatmapData['dim_claridad_rol'] ?? []),
            'dim_capacitacion' => $convertData($heatmapData['dim_capacitacion'] ?? []),
            'dim_participacion_cambio' => $convertData($heatmapData['dim_participacion_manejo_cambio'] ?? []),
            'dim_oportunidades_desarrollo' => $convertData($heatmapData['dim_oportunidades_desarrollo'] ?? []),
            'dim_control_autonomia' => $convertData($heatmapData['dim_control_autonomia'] ?? []),
            'dim_demandas_ambientales' => $convertData($heatmapData['dim_demandas_ambientales'] ?? []),
            'dim_demandas_emocionales' => $convertData($heatmapData['dim_demandas_emocionales'] ?? []),
            'dim_demandas_cuantitativas' => $convertData($heatmapData['dim_demandas_cuantitativas'] ?? []),
            'dim_influencia_entorno' => $convertData($heatmapData['dim_influencia_trabajo_entorno_extralaboral'] ?? []),
            'dim_demandas_responsabilidad' => $convertData($heatmapData['dim_demandas_responsabilidad'] ?? []),
            'dim_carga_mental' => $convertData($heatmapData['dim_carga_mental'] ?? []),
            'dim_consistencia_rol' => $convertData($heatmapData['dim_consistencia_rol'] ?? []),
            'dim_demandas_jornada' => $convertData($heatmapData['dim_demandas_jornada_trabajo'] ?? []),
            'dim_recompensas_pertenencia' => $convertData($heatmapData['dim_recompensas_pertenencia'] ?? []),
            'dim_reconocimiento' => $convertData($heatmapData['dim_reconocimiento_compensacion'] ?? []),

            // Dimensiones extralaborales
            'dim_tiempo_fuera' => $convertData($heatmapData['dim_tiempo_fuera'] ?? []),
            'dim_relaciones_familiares' => $convertData($heatmapData['dim_relaciones_familiares_extra'] ?? []),
            'dim_comunicacion' => $convertData($heatmapData['dim_comunicacion'] ?? []),
            'dim_situacion_economica' => $convertData($heatmapData['dim_situacion_economica'] ?? []),
            'dim_caracteristicas_vivienda' => $convertData($heatmapData['dim_caracteristicas_vivienda'] ?? []),
            'dim_influencia_entorno_extra' => $convertData($heatmapData['dim_influencia_entorno_extra'] ?? []),
            'dim_desplazamiento' => $convertData($heatmapData['dim_desplazamiento'] ?? []),
        ];
    }

    /**
     * Mapa de Calor Visual General - Todos los dominios y dimensiones en una página
     * Usa tablas HTML nativas para compatibilidad con DomPDF
     */
    protected function renderMapaCalorVisualGeneral()
    {
        $batteryServiceId = $this->companyData['battery_service_id'] ?? 1;
        $promedios = $this->loadPromediosGenerales($batteryServiceId);

        // Helper para extraer puntaje y nivel de la nueva estructura
        $getPuntaje = function($data) {
            return is_array($data) ? floatval($data['puntaje'] ?? 0) : floatval($data);
        };
        $getNivel = function($data) {
            return is_array($data) ? ($data['nivel'] ?? 'sin_riesgo') : 'sin_riesgo';
        };

        // Obtener niveles y colores para totales (ahora con nivel pre-calculado)
        $puntajeIntra = $getPuntaje($promedios['intralaboral_total'] ?? 0);
        $nivelIntra = $getNivel($promedios['intralaboral_total'] ?? []);
        $colorIntra = $this->getColorPorNivel($nivelIntra);
        $textColorIntra = $this->getTextColorPorNivel($nivelIntra);

        $puntajeExtra = $getPuntaje($promedios['extralaboral_total'] ?? 0);
        $nivelExtra = $getNivel($promedios['extralaboral_total'] ?? []);
        $colorExtra = $this->getColorPorNivel($nivelExtra);
        $textColorExtra = $this->getTextColorPorNivel($nivelExtra);

        $puntajeEstres = $getPuntaje($promedios['estres_total'] ?? 0);
        $nivelEstres = $getNivel($promedios['estres_total'] ?? []);
        $colorEstres = $this->getColorPorNivel($nivelEstres);
        $textColorEstres = $this->getTextColorPorNivel($nivelEstres);

        // Estructura de dominios con dimensiones
        $dominiosConfig = [
            [
                'nombre' => 'LIDERAZGO Y RELACIONES SOCIALES',
                'key' => 'dom_liderazgo',
                'dimensiones' => [
                    ['key' => 'dim_caracteristicas_liderazgo', 'nombre' => 'Características del liderazgo'],
                    ['key' => 'dim_relaciones_sociales', 'nombre' => 'Relaciones sociales en el trabajo'],
                    ['key' => 'dim_retroalimentacion', 'nombre' => 'Retroalimentación del desempeño'],
                    ['key' => 'dim_relacion_colaboradores', 'nombre' => 'Relación con los colaboradores'],
                ]
            ],
            [
                'nombre' => 'CONTROL SOBRE EL TRABAJO',
                'key' => 'dom_control',
                'dimensiones' => [
                    ['key' => 'dim_claridad_rol', 'nombre' => 'Claridad de rol'],
                    ['key' => 'dim_capacitacion', 'nombre' => 'Capacitación'],
                    ['key' => 'dim_participacion_cambio', 'nombre' => 'Participación y manejo del cambio'],
                    ['key' => 'dim_oportunidades_desarrollo', 'nombre' => 'Oportunidades desarrollo habilidades'],
                    ['key' => 'dim_control_autonomia', 'nombre' => 'Control y autonomía sobre el trabajo'],
                ]
            ],
            [
                'nombre' => 'DEMANDAS DEL TRABAJO',
                'key' => 'dom_demandas',
                'dimensiones' => [
                    ['key' => 'dim_demandas_ambientales', 'nombre' => 'Demandas ambientales y esfuerzo físico'],
                    ['key' => 'dim_demandas_emocionales', 'nombre' => 'Demandas emocionales'],
                    ['key' => 'dim_demandas_cuantitativas', 'nombre' => 'Demandas cuantitativas'],
                    ['key' => 'dim_influencia_entorno', 'nombre' => 'Influencia trabajo sobre entorno extra'],
                    ['key' => 'dim_demandas_responsabilidad', 'nombre' => 'Exigencias de responsabilidad del cargo'],
                    ['key' => 'dim_carga_mental', 'nombre' => 'Demandas de carga mental'],
                    ['key' => 'dim_consistencia_rol', 'nombre' => 'Consistencia del rol'],
                    ['key' => 'dim_demandas_jornada', 'nombre' => 'Demandas de la jornada de trabajo'],
                ]
            ],
            [
                'nombre' => 'RECOMPENSAS',
                'key' => 'dom_recompensas',
                'dimensiones' => [
                    ['key' => 'dim_recompensas_pertenencia', 'nombre' => 'Recompensas pertenencia organización'],
                    ['key' => 'dim_reconocimiento', 'nombre' => 'Reconocimiento y compensación'],
                ]
            ],
        ];

        $dimensionesExtra = [
            ['key' => 'dim_tiempo_fuera', 'nombre' => 'Tiempo fuera del trabajo'],
            ['key' => 'dim_relaciones_familiares', 'nombre' => 'Relaciones familiares'],
            ['key' => 'dim_comunicacion', 'nombre' => 'Comunicación y relaciones interpersonales'],
            ['key' => 'dim_situacion_economica', 'nombre' => 'Situación económica del grupo familiar'],
            ['key' => 'dim_caracteristicas_vivienda', 'nombre' => 'Características de la vivienda y entorno'],
            ['key' => 'dim_influencia_entorno_extra', 'nombre' => 'Influencia del entorno extralaboral'],
            ['key' => 'dim_desplazamiento', 'nombre' => 'Desplazamiento vivienda - trabajo'],
        ];

        // Contar total de dimensiones intralaborales
        $totalDimensionesIntra = 0;
        foreach ($dominiosConfig as $dom) {
            $totalDimensionesIntra += count($dom['dimensiones']);
        }

        $html = '
<!-- PDF_HEATMAP_VERSION: 2025-12-21_SharedCalc_v5 -->
<h2 style="color: #006699; text-align: center; margin: 0 0 8pt 0; font-size: 12pt; text-decoration: underline;">
    Mapa de Calor - Riesgo Psicosocial General
</h2>
<p style="font-size: 6pt; color: #999; text-align: center; margin: 0 0 4pt 0;">[v6-Debug ' . date('Y-m-d H:i:s') . ' | Intra=' . $puntajeIntra . ']</p>

<!-- Leyenda -->
<table style="width: 100%; margin-bottom: 6pt; background: #f5f5f5;">
    <tr>
        <td style="text-align: center; padding: 4pt; font-size: 7pt;">
            <span style="display: inline-block; width: 10pt; height: 10pt; background: #4CAF50;"></span> Sin Riesgo
            &nbsp;&nbsp;
            <span style="display: inline-block; width: 10pt; height: 10pt; background: #8BC34A;"></span> Bajo
            &nbsp;&nbsp;
            <span style="display: inline-block; width: 10pt; height: 10pt; background: #FFC107;"></span> Medio
            &nbsp;&nbsp;
            <span style="display: inline-block; width: 10pt; height: 10pt; background: #FF9800;"></span> Alto
            &nbsp;&nbsp;
            <span style="display: inline-block; width: 10pt; height: 10pt; background: #F44336;"></span> Muy Alto
        </td>
    </tr>
</table>

<!-- MAPA DE CALOR INTRALABORAL -->
<table style="width: 100%; border-collapse: collapse; border: 2pt solid #333;">
    <tr>
        <!-- Celda Total Intralaboral -->
        <td rowspan="' . $totalDimensionesIntra . '" style="width: 15%; vertical-align: middle; text-align: center; padding: 6pt; background: ' . $colorIntra . '; color: ' . $textColorIntra . '; border-right: 2pt solid #333; font-weight: bold; font-size: 6pt;">
            TOTAL GENERAL<br>FACTORES DE<br>RIESGO<br>PSICOSOCIAL<br>INTRALABORAL<br>
            <span style="font-size: 11pt;">' . number_format($puntajeIntra, 1) . '</span>
        </td>';

        $firstRow = true;
        foreach ($dominiosConfig as $dominio) {
            $dataDom = $promedios[$dominio['key']] ?? [];
            $puntajeDom = $getPuntaje($dataDom);
            $nivelDom = $getNivel($dataDom);
            $colorDom = $this->getColorPorNivel($nivelDom);
            $textColorDom = $this->getTextColorPorNivel($nivelDom);
            $numDimensiones = count($dominio['dimensiones']);

            foreach ($dominio['dimensiones'] as $dimIndex => $dim) {
                $dataDim = $promedios[$dim['key']] ?? [];
                $puntajeDim = $getPuntaje($dataDim);
                $nivelDim = $getNivel($dataDim);
                $colorDim = $this->getColorPorNivel($nivelDim);
                $textColorDim = $this->getTextColorPorNivel($nivelDim);

                if (!$firstRow) {
                    $html .= '<tr>';
                }

                // Primera dimensión del dominio: incluir celda del dominio con rowspan
                if ($dimIndex === 0) {
                    $html .= '
        <td rowspan="' . $numDimensiones . '" style="width: 25%; vertical-align: middle; text-align: center; padding: 4pt; background: ' . $colorDom . '; color: ' . $textColorDom . '; border: 1pt solid #666; font-weight: bold; font-size: 5.5pt;">
            ' . $dominio['nombre'] . '<br>
            <span style="font-size: 9pt;">' . number_format($puntajeDom, 1) . '</span>
        </td>';
                }

                // Celda de la dimensión
                $html .= '
        <td style="width: 45%; padding: 2pt 4pt; background: ' . $colorDim . '; color: ' . $textColorDim . '; border: 1pt solid rgba(0,0,0,0.2); font-size: 6pt;">
            ' . $dim['nombre'] . '
        </td>
        <td style="width: 15%; text-align: center; padding: 2pt; background: ' . $colorDim . '; color: ' . $textColorDim . '; border: 1pt solid rgba(0,0,0,0.2); font-size: 7pt; font-weight: bold;">
            ' . number_format($puntajeDim, 1) . '
        </td>';

                if (!$firstRow) {
                    $html .= '</tr>';
                } else {
                    $html .= '</tr>';
                    $firstRow = false;
                }
            }
        }

        $html .= '
</table>

<!-- MAPA DE CALOR EXTRALABORAL -->
<table style="width: 100%; border-collapse: collapse; border: 2pt solid #333; border-top: none;">
    <tr>
        <td rowspan="' . count($dimensionesExtra) . '" style="width: 40%; vertical-align: middle; text-align: center; padding: 8pt; background: ' . $colorExtra . '; color: ' . $textColorExtra . '; border-right: 2pt solid #333; font-weight: bold; font-size: 8pt;">
            FACTORES EXTRALABORALES<br>
            <span style="font-size: 12pt;">' . number_format($puntajeExtra, 1) . '</span>
        </td>';

        $firstExtraRow = true;
        foreach ($dimensionesExtra as $dim) {
            $dataDim = $promedios[$dim['key']] ?? [];
            $puntajeDim = $getPuntaje($dataDim);
            $nivelDim = $getNivel($dataDim);
            $colorDim = $this->getColorPorNivel($nivelDim);
            $textColorDim = $this->getTextColorPorNivel($nivelDim);

            if (!$firstExtraRow) {
                $html .= '<tr>';
            }

            $html .= '
        <td style="width: 45%; padding: 3pt 6pt; background: ' . $colorDim . '; color: ' . $textColorDim . '; border: 1pt solid rgba(0,0,0,0.2); font-size: 7pt;">
            ' . $dim['nombre'] . '
        </td>
        <td style="width: 15%; text-align: center; padding: 3pt; background: ' . $colorDim . '; color: ' . $textColorDim . '; border: 1pt solid rgba(0,0,0,0.2); font-size: 8pt; font-weight: bold;">
            ' . number_format($puntajeDim, 1) . '
        </td>
    </tr>';
            $firstExtraRow = false;
        }

        $html .= '
</table>

<!-- ESTRÉS -->
<table style="width: 100%; border-collapse: collapse; border: 2pt solid #333; border-top: none;">
    <tr>
        <td style="text-align: center; padding: 10pt; background: ' . $colorEstres . '; color: ' . $textColorEstres . '; font-weight: bold; font-size: 9pt;">
            SÍNTOMAS DE ESTRÉS<br>
            <span style="font-size: 14pt;">' . number_format($puntajeEstres, 1) . '</span>
        </td>
    </tr>
</table>

<p style="font-size: 7pt; color: #666; text-align: center; margin-top: 6pt;">
    Los valores representan el promedio de puntajes transformados de todos los trabajadores evaluados.
</p>
';

        return $html;
    }

    /**
     * Encabezado de la sección
     */
    protected function renderEncabezado()
    {
        return '
<h1 style="font-size: 14pt; margin: 0 0 12pt 0; padding-bottom: 5pt; border-bottom: 2pt solid #006699;">Mapa de Calor - Riesgo Psicosocial General</h1>

<p style="font-size: 9pt; text-align: justify; margin: 0 0 10pt 0;">
El siguiente mapa de calor presenta la distribución de los niveles de riesgo psicosocial identificados en los colaboradores evaluados, clasificados por tipo de cuestionario (Intralaboral, Extralaboral, Estrés) y forma de aplicación (A para jefes/profesionales, B para auxiliares/operativos).
</p>
';
    }

    /**
     * Resumen de participación
     */
    protected function renderResumen()
    {
        $totalA = $this->heatmapData['total_a'];
        $totalB = $this->heatmapData['total_b'];
        $total = $this->heatmapData['total'];
        $pctA = $total > 0 ? round(($totalA / $total) * 100) : 0;
        $pctB = $total > 0 ? round(($totalB / $total) * 100) : 0;

        return '
<div style="background-color: #e8f4fc; border: 1pt solid #006699; padding: 8pt; margin: 0 0 15pt 0;">
    <p style="font-size: 9pt; margin: 0;"><strong>Resumen de Participación:</strong></p>
    <p style="font-size: 9pt; margin: 3pt 0 0 10pt;">
        Total de trabajadores evaluados: <strong>' . $total . '</strong><br>
        Forma A (Jefes/Profesionales): <strong>' . $totalA . '</strong> (' . $pctA . '%)<br>
        Forma B (Auxiliares/Operativos): <strong>' . $totalB . '</strong> (' . $pctB . '%)
    </p>
</div>

<h2 style="font-size: 12pt; color: #006699; margin: 10pt 0 8pt 0; border-bottom: none; text-align: left;">Distribución por Niveles de Riesgo</h2>
';
    }

    /**
     * Tabla principal del mapa de calor
     */
    protected function renderTablaMapaCalor()
    {
        $html = '
<table style="width: 100%; border-collapse: collapse; font-size: 7.5pt; margin: 5pt 0;">
    <thead>
        <tr>
            <th style="background-color: #006699; color: white; padding: 4pt; border: 1pt solid #333; text-align: center; width: 15%;">Cuestionario</th>
            <th style="background-color: #006699; color: white; padding: 4pt; border: 1pt solid #333; text-align: center; width: 10%;">Forma</th>
            <th style="background-color: #4CAF50; color: white; padding: 4pt; border: 1pt solid #333; text-align: center; width: 13%;">Sin Riesgo</th>
            <th style="background-color: #8BC34A; color: white; padding: 4pt; border: 1pt solid #333; text-align: center; width: 13%;">Bajo</th>
            <th style="background-color: #FFC107; color: #333; padding: 4pt; border: 1pt solid #333; text-align: center; width: 13%;">Medio</th>
            <th style="background-color: #FF9800; color: white; padding: 4pt; border: 1pt solid #333; text-align: center; width: 13%;">Alto</th>
            <th style="background-color: #F44336; color: white; padding: 4pt; border: 1pt solid #333; text-align: center; width: 13%;">Muy Alto</th>
            <th style="background-color: #006699; color: white; padding: 4pt; border: 1pt solid #333; text-align: center; width: 10%;">Total</th>
        </tr>
    </thead>
    <tbody>
';

        // Intralaboral
        $html .= $this->renderFilaCuestionario('Intralaboral', 'intralaboral', 'A');
        $html .= $this->renderFilaCuestionario('', 'intralaboral', 'B', false);

        // Extralaboral
        $html .= $this->renderFilaCuestionario('Extralaboral', 'extralaboral', 'A');
        $html .= $this->renderFilaCuestionario('', 'extralaboral', 'B', false);

        // Estrés
        $html .= $this->renderFilaEstres('Estrés', 'A');
        $html .= $this->renderFilaEstres('', 'B', false);

        $html .= '
    </tbody>
</table>
';

        return $html;
    }

    /**
     * Renderiza una fila del mapa de calor para intralaboral/extralaboral
     */
    protected function renderFilaCuestionario($nombreCuestionario, $tipo, $forma, $conRowspan = true)
    {
        $data = $this->heatmapData[$tipo][$forma];
        $total = $forma === 'A' ? $this->heatmapData['total_a'] : $this->heatmapData['total_b'];
        $total = max($total, 1);

        $niveles = ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'];
        $colores = [
            'sin_riesgo' => ['bg' => '#4CAF50', 'fg' => 'white'],
            'riesgo_bajo' => ['bg' => '#8BC34A', 'fg' => 'white'],
            'riesgo_medio' => ['bg' => '#FFC107', 'fg' => '#333'],
            'riesgo_alto' => ['bg' => '#FF9800', 'fg' => 'white'],
            'riesgo_muy_alto' => ['bg' => '#F44336', 'fg' => 'white'],
        ];

        $html = '<tr>';

        if ($conRowspan) {
            $html .= '<td rowspan="2" style="padding: 4pt; border: 1pt solid #333; text-align: center; font-weight: bold; background-color: #f0f0f0;">' . $nombreCuestionario . '</td>';
        }

        $html .= '<td style="padding: 4pt; border: 1pt solid #333; text-align: center;">Forma ' . $forma . '</td>';

        foreach ($niveles as $nivel) {
            $count = $data[$nivel];
            $pct = round(($count / $total) * 100);
            $bgColor = $count > 0 ? $colores[$nivel]['bg'] : '#e0e0e0';
            $fgColor = $count > 0 ? $colores[$nivel]['fg'] : '#999';

            $html .= '<td style="padding: 4pt; border: 1pt solid #333; text-align: center; background-color: ' . $bgColor . '; color: ' . $fgColor . ';">' . $count . ' (' . $pct . '%)</td>';
        }

        $html .= '<td style="padding: 4pt; border: 1pt solid #333; text-align: center; font-weight: bold;">' . $total . '</td>';
        $html .= '</tr>';

        return $html;
    }

    /**
     * Renderiza una fila del mapa de calor para estrés (niveles diferentes)
     */
    protected function renderFilaEstres($nombreCuestionario, $forma, $conRowspan = true)
    {
        $data = $this->heatmapData['estres'][$forma];
        $total = $forma === 'A' ? $this->heatmapData['total_a'] : $this->heatmapData['total_b'];
        $total = max($total, 1);

        $niveles = ['muy_bajo', 'bajo', 'medio', 'alto', 'muy_alto'];
        $colores = [
            'muy_bajo' => ['bg' => '#4CAF50', 'fg' => 'white'],
            'bajo' => ['bg' => '#8BC34A', 'fg' => 'white'],
            'medio' => ['bg' => '#FFC107', 'fg' => '#333'],
            'alto' => ['bg' => '#FF9800', 'fg' => 'white'],
            'muy_alto' => ['bg' => '#F44336', 'fg' => 'white'],
        ];

        $html = '<tr>';

        if ($conRowspan) {
            $html .= '<td rowspan="2" style="padding: 4pt; border: 1pt solid #333; text-align: center; font-weight: bold; background-color: #f0f0f0;">' . $nombreCuestionario . '</td>';
        }

        $html .= '<td style="padding: 4pt; border: 1pt solid #333; text-align: center;">Forma ' . $forma . '</td>';

        foreach ($niveles as $nivel) {
            $count = $data[$nivel];
            $pct = round(($count / $total) * 100);
            $bgColor = $count > 0 ? $colores[$nivel]['bg'] : '#e0e0e0';
            $fgColor = $count > 0 ? $colores[$nivel]['fg'] : '#999';

            $html .= '<td style="padding: 4pt; border: 1pt solid #333; text-align: center; background-color: ' . $bgColor . '; color: ' . $fgColor . ';">' . $count . ' (' . $pct . '%)</td>';
        }

        $html .= '<td style="padding: 4pt; border: 1pt solid #333; text-align: center; font-weight: bold;">' . $total . '</td>';
        $html .= '</tr>';

        return $html;
    }

    /**
     * Leyenda de colores
     */
    protected function renderLeyenda()
    {
        return '
<div style="margin-top: 15pt;">
    <p style="font-size: 9pt; font-weight: bold; margin: 0 0 5pt 0;">Leyenda:</p>
    <table style="font-size: 8pt; border: none; width: 100%;">
        <tr>
            <td style="border: none; padding: 2pt 5pt;">
                <span style="display: inline-block; width: 12pt; height: 12pt; background-color: #4CAF50; border: 1pt solid #333;"></span>
                Sin Riesgo / Muy Bajo
            </td>
            <td style="border: none; padding: 2pt 5pt;">
                <span style="display: inline-block; width: 12pt; height: 12pt; background-color: #8BC34A; border: 1pt solid #333;"></span>
                Riesgo Bajo
            </td>
            <td style="border: none; padding: 2pt 5pt;">
                <span style="display: inline-block; width: 12pt; height: 12pt; background-color: #FFC107; border: 1pt solid #333;"></span>
                Riesgo Medio
            </td>
            <td style="border: none; padding: 2pt 5pt;">
                <span style="display: inline-block; width: 12pt; height: 12pt; background-color: #FF9800; border: 1pt solid #333;"></span>
                Riesgo Alto
            </td>
            <td style="border: none; padding: 2pt 5pt;">
                <span style="display: inline-block; width: 12pt; height: 12pt; background-color: #F44336; border: 1pt solid #333;"></span>
                Riesgo Muy Alto
            </td>
        </tr>
    </table>
</div>

<p style="font-size: 8pt; color: #666; margin-top: 10pt; text-align: justify;">
<strong>Nota:</strong> Los colores más intensos indican mayor concentración de trabajadores en ese nivel de riesgo. Las celdas en gris claro indican que no hay trabajadores en ese nivel.
</p>
';
    }

    /**
     * Mapa visual intralaboral por forma (A o B)
     */
    protected function renderMapaIntralaboral($forma)
    {
        $dominios = $forma === 'A' ? $this->dominiosFormaA : $this->dominiosFormaB;
        $titulo = $forma === 'A' ? 'Jefes, Profesionales y Técnicos' : 'Auxiliares y Operarios';

        // Calcular total de dimensiones real
        $totalDimensiones = 0;
        foreach ($dominios as $dom) {
            $totalDimensiones += count($dom['dimensiones']);
        }

        // Calcular promedios
        $promedios = $this->calcularPromediosForma($forma);

        // Total intralaboral
        $totalNivel = $promedios['intralaboral_total_nivel'] ?? 'sin_riesgo';
        $totalPuntaje = number_format($promedios['intralaboral_total_puntaje'] ?? 0, 1);
        $totalColor = $this->getRiskColor($totalNivel);
        $totalTextColor = $totalNivel === 'riesgo_medio' ? '#333' : '#fff';

        $html = '
<h1 style="font-size: 14pt; margin: 0 0 8pt 0; padding-bottom: 5pt; border-bottom: 2pt solid #006699;">Mapa de Calor Intralaboral - Forma ' . $forma . '</h1>
<p style="font-size: 9pt; color: #666; margin: 0 0 10pt 0; text-align: center;">' . $titulo . ' (' . $totalDimensiones . ' dimensiones) - n=' . ($forma === 'A' ? $this->heatmapData['total_a'] : $this->heatmapData['total_b']) . '</p>

<!-- Mapa de Calor Intralaboral usando tabla HTML nativa -->
<table style="width: 100%; border-collapse: collapse; border: 2pt solid #333;">
    <tr>
        <!-- Celda Total Intralaboral -->
        <td rowspan="' . $totalDimensiones . '" style="width: 18%; vertical-align: middle; text-align: center; padding: 8pt; background: ' . $totalColor . '; color: ' . $totalTextColor . '; border-right: 2pt solid #333; font-weight: bold; font-size: 7pt;">
            TOTAL GENERAL<br>FACTORES DE<br>RIESGO<br>PSICOSOCIAL<br>INTRALABORAL<br>
            <span style="font-size: 12pt;">' . $totalPuntaje . '</span>
        </td>';

        $firstRow = true;
        foreach ($dominios as $dominio) {
            $dominioKey = $dominio['key'];
            $dominioNivel = $promedios[$dominioKey . '_nivel'] ?? 'sin_riesgo';
            $dominioPuntaje = number_format($promedios[$dominioKey . '_puntaje'] ?? 0, 1);
            $dominioColor = $this->getRiskColor($dominioNivel);
            $dominioTextColor = $dominioNivel === 'riesgo_medio' ? '#333' : '#fff';
            $numDimensiones = count($dominio['dimensiones']);

            foreach ($dominio['dimensiones'] as $dimIndex => $dimension) {
                $dimKey = $dimension['key'];
                $dimNivel = $promedios[$dimKey . '_nivel'] ?? 'sin_riesgo';
                $dimPuntaje = number_format($promedios[$dimKey . '_puntaje'] ?? 0, 1);
                $dimColor = $this->getRiskColor($dimNivel);
                $dimTextColor = $dimNivel === 'riesgo_medio' ? '#333' : '#fff';

                if (!$firstRow) {
                    $html .= '<tr>';
                }

                // Primera dimensión del dominio: incluir celda del dominio con rowspan
                if ($dimIndex === 0) {
                    $html .= '
        <td rowspan="' . $numDimensiones . '" style="width: 22%; vertical-align: middle; text-align: center; padding: 4pt; background: ' . $dominioColor . '; color: ' . $dominioTextColor . '; border: 1pt solid #666; font-weight: bold; font-size: 6pt;">
            ' . $dominio['nombre'] . '<br>
            <span style="font-size: 10pt;">' . $dominioPuntaje . '</span>
        </td>';
                }

                // Celda de la dimensión
                $html .= '
        <td style="width: 45%; padding: 3pt 6pt; background: ' . $dimColor . '; color: ' . $dimTextColor . '; border: 1pt solid rgba(0,0,0,0.2); font-size: 7pt;">
            ' . $dimension['nombre'] . '
        </td>
        <td style="width: 15%; text-align: center; padding: 3pt; background: ' . $dimColor . '; color: ' . $dimTextColor . '; border: 1pt solid rgba(0,0,0,0.2); font-size: 8pt; font-weight: bold;">
            ' . $dimPuntaje . '
        </td>
    </tr>';
                $firstRow = false;
            }
        }

        $html .= '
</table>

<div style="padding: 8pt; background: #f5f5f5; border-left: 3pt solid #006699; font-size: 8pt; margin-top: 10pt;">
    <strong>Nota metodológica:</strong> La Forma ' . $forma . ' contiene ' . $totalDimensiones . ' dimensiones distribuidas en 4 dominios. ';

        if ($forma === 'A') {
            $html .= 'Se aplica a jefes, profesionales y técnicos con responsabilidad de coordinación o personal a cargo. Incluye dimensiones exclusivas: Relación con los colaboradores, Exigencias de responsabilidad del cargo y Consistencia del rol.';
        } else {
            $html .= 'Se aplica a auxiliares y operarios sin responsabilidad de coordinación. Baremos según Resolución 2404/2019.';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Renderiza un dominio con sus dimensiones (método legacy - no usado)
     */
    protected function renderDominio($dominio, $promedios, $isLast = false)
    {
        // Este método ya no se usa, la lógica está integrada en renderMapaIntralaboral
        return '';
    }

    /**
     * Calcula promedios de puntajes por forma
     */
    protected function calcularPromediosForma($forma)
    {
        $data = $this->detailedData[$forma] ?? [];
        if (empty($data)) {
            return [];
        }

        $promedios = [];
        $campos = [
            'intralaboral_total_puntaje', 'intralaboral_total_nivel',
            'dom_liderazgo_puntaje', 'dom_liderazgo_nivel',
            'dom_control_puntaje', 'dom_control_nivel',
            'dom_demandas_puntaje', 'dom_demandas_nivel',
            'dom_recompensas_puntaje', 'dom_recompensas_nivel',
            'dim_caracteristicas_liderazgo_puntaje', 'dim_caracteristicas_liderazgo_nivel',
            'dim_relaciones_sociales_puntaje', 'dim_relaciones_sociales_nivel',
            'dim_retroalimentacion_puntaje', 'dim_retroalimentacion_nivel',
            'dim_relacion_colaboradores_puntaje', 'dim_relacion_colaboradores_nivel',
            'dim_claridad_rol_puntaje', 'dim_claridad_rol_nivel',
            'dim_capacitacion_puntaje', 'dim_capacitacion_nivel',
            'dim_participacion_manejo_cambio_puntaje', 'dim_participacion_manejo_cambio_nivel',
            'dim_oportunidades_desarrollo_puntaje', 'dim_oportunidades_desarrollo_nivel',
            'dim_control_autonomia_puntaje', 'dim_control_autonomia_nivel',
            'dim_demandas_ambientales_puntaje', 'dim_demandas_ambientales_nivel',
            'dim_demandas_emocionales_puntaje', 'dim_demandas_emocionales_nivel',
            'dim_demandas_cuantitativas_puntaje', 'dim_demandas_cuantitativas_nivel',
            'dim_influencia_trabajo_entorno_extralaboral_puntaje', 'dim_influencia_trabajo_entorno_extralaboral_nivel',
            'dim_demandas_responsabilidad_puntaje', 'dim_demandas_responsabilidad_nivel',
            'dim_demandas_carga_mental_puntaje', 'dim_demandas_carga_mental_nivel',
            'dim_consistencia_rol_puntaje', 'dim_consistencia_rol_nivel',
            'dim_demandas_jornada_trabajo_puntaje', 'dim_demandas_jornada_trabajo_nivel',
            'dim_recompensas_pertenencia_puntaje', 'dim_recompensas_pertenencia_nivel',
            'dim_reconocimiento_compensacion_puntaje', 'dim_reconocimiento_compensacion_nivel',
            'extralaboral_total_puntaje', 'extralaboral_total_nivel',
            'extralaboral_tiempo_fuera_puntaje', 'extralaboral_tiempo_fuera_nivel',
            'extralaboral_relaciones_familiares_puntaje', 'extralaboral_relaciones_familiares_nivel',
            'extralaboral_comunicacion_puntaje', 'extralaboral_comunicacion_nivel',
            'extralaboral_situacion_economica_puntaje', 'extralaboral_situacion_economica_nivel',
            'extralaboral_caracteristicas_vivienda_puntaje', 'extralaboral_caracteristicas_vivienda_nivel',
            'extralaboral_influencia_entorno_puntaje', 'extralaboral_influencia_entorno_nivel',
            'extralaboral_desplazamiento_puntaje', 'extralaboral_desplazamiento_nivel',
            'estres_total_puntaje', 'estres_total_nivel',
        ];

        foreach ($campos as $campo) {
            if (strpos($campo, '_puntaje') !== false) {
                // Calcular promedio de puntajes
                $suma = 0;
                $count = 0;
                foreach ($data as $row) {
                    if (isset($row[$campo]) && $row[$campo] !== null) {
                        $suma += (float)$row[$campo];
                        $count++;
                    }
                }
                $promedios[$campo] = $count > 0 ? $suma / $count : 0;
            } else {
                // Para niveles, obtener el más frecuente
                $niveles = [];
                foreach ($data as $row) {
                    if (isset($row[$campo]) && !empty($row[$campo])) {
                        $niveles[] = $row[$campo];
                    }
                }
                if (!empty($niveles)) {
                    $frecuencias = array_count_values($niveles);
                    arsort($frecuencias);
                    $promedios[$campo] = array_key_first($frecuencias);
                } else {
                    $promedios[$campo] = 'sin_riesgo';
                }
            }
        }

        return $promedios;
    }

    /**
     * Mapa visual extralaboral
     */
    protected function renderMapaExtralaboral()
    {
        $html = '
<h1 style="font-size: 14pt; margin: 0 0 8pt 0; padding-bottom: 5pt; border-bottom: 2pt solid #006699;">Mapa de Calor Extralaboral</h1>
<p style="font-size: 9pt; color: #666; margin: 0 0 10pt 0; text-align: center;">Factores externos al trabajo (7 dimensiones)</p>
';

        // Forma A
        if ($this->heatmapData['total_a'] > 0) {
            $promediosA = $this->calcularPromediosForma('A');
            $html .= $this->renderSeccionExtralaboral('A', $promediosA);
        }

        // Forma B
        if ($this->heatmapData['total_b'] > 0) {
            $promediosB = $this->calcularPromediosForma('B');
            $html .= $this->renderSeccionExtralaboral('B', $promediosB);
        }

        // Nota
        $html .= '
<div style="padding: 8pt; background: #f5f5f5; border-left: 3pt solid #006699; font-size: 8pt; margin-top: 15pt;">
    <strong>Nota metodológica:</strong> El cuestionario extralaboral contiene 7 dimensiones que evalúan las condiciones externas al trabajo que pueden afectar la salud del trabajador. Los factores extralaborales comprenden los aspectos del entorno familiar, social y económico del trabajador. Baremos aplicados según Resolución 2404/2019.
</div>
';

        return $html;
    }

    /**
     * Sección extralaboral por forma - Usando tabla HTML nativa para DomPDF
     */
    protected function renderSeccionExtralaboral($forma, $promedios)
    {
        $totalNivel = $promedios['extralaboral_total_nivel'] ?? 'sin_riesgo';
        $totalPuntaje = number_format($promedios['extralaboral_total_puntaje'] ?? 0, 1);
        $totalColor = $this->getRiskColor($totalNivel);
        $totalTextColor = $totalNivel === 'riesgo_medio' ? '#333' : '#fff';
        $n = $forma === 'A' ? $this->heatmapData['total_a'] : $this->heatmapData['total_b'];
        $titulo = $forma === 'A' ? 'Jefes/Profesionales' : 'Auxiliares/Operarios';

        $totalDimensiones = count($this->dimensionesExtralaboral); // 7 dimensiones

        $html = '
<h3 style="font-size: 11pt; color: #006699; margin: 15pt 0 8pt 0;">Forma ' . $forma . ' - ' . $titulo . ' (n=' . $n . ')</h3>

<table style="width: 100%; border-collapse: collapse; border: 2px solid #333;">
';
        // Primera fila: celda TOTAL con rowspan + primera dimensión
        $firstDim = $this->dimensionesExtralaboral[0];
        $firstDimKey = $firstDim['key'];
        $firstDimNivel = $promedios[$firstDimKey . '_nivel'] ?? 'sin_riesgo';
        $firstDimPuntaje = number_format($promedios[$firstDimKey . '_puntaje'] ?? 0, 1);
        $firstDimColor = $this->getRiskColor($firstDimNivel);
        $firstDimTextColor = $firstDimNivel === 'riesgo_medio' ? '#333' : '#fff';

        $html .= '
    <tr>
        <td rowspan="' . $totalDimensiones . '" style="width: 40%; background: ' . $totalColor . '; color: ' . $totalTextColor . '; text-align: center; vertical-align: middle; padding: 10pt; border-right: 2px solid #333; font-weight: bold; font-size: 9pt;">
            FACTORES<br>EXTRALABORALES<br>
            <span style="font-size: 14pt;">' . $totalPuntaje . '</span>
        </td>
        <td style="width: 45%; background: ' . $firstDimColor . '; color: ' . $firstDimTextColor . '; padding: 4pt 6pt; font-size: 7.5pt; border-bottom: 1px solid #ddd;">' . $firstDim['nombre'] . '</td>
        <td style="width: 15%; background: ' . $firstDimColor . '; color: ' . $firstDimTextColor . '; padding: 4pt 6pt; font-size: 8pt; font-weight: bold; text-align: right; border-bottom: 1px solid #ddd;">' . $firstDimPuntaje . '</td>
    </tr>
';

        // Resto de dimensiones (índice 1 a 6)
        for ($i = 1; $i < $totalDimensiones; $i++) {
            $dim = $this->dimensionesExtralaboral[$i];
            $dimKey = $dim['key'];
            $dimNivel = $promedios[$dimKey . '_nivel'] ?? 'sin_riesgo';
            $dimPuntaje = number_format($promedios[$dimKey . '_puntaje'] ?? 0, 1);
            $dimColor = $this->getRiskColor($dimNivel);
            $dimTextColor = $dimNivel === 'riesgo_medio' ? '#333' : '#fff';
            $borderBottom = ($i < $totalDimensiones - 1) ? 'border-bottom: 1px solid #ddd;' : '';

            $html .= '
    <tr>
        <td style="background: ' . $dimColor . '; color: ' . $dimTextColor . '; padding: 4pt 6pt; font-size: 7.5pt; ' . $borderBottom . '">' . $dim['nombre'] . '</td>
        <td style="background: ' . $dimColor . '; color: ' . $dimTextColor . '; padding: 4pt 6pt; font-size: 8pt; font-weight: bold; text-align: right; ' . $borderBottom . '">' . $dimPuntaje . '</td>
    </tr>
';
        }

        $html .= '
</table>
';

        return $html;
    }

    /**
     * Tabla de síntomas de estrés (31 preguntas)
     */
    protected function renderTablaSintomasEstres($forma)
    {
        $n = $forma === 'A' ? $this->heatmapData['total_a'] : $this->heatmapData['total_b'];
        $titulo = $forma === 'A' ? 'Jefes, Profesionales y Técnicos' : 'Auxiliares y Operarios';

        // Calcular nivel promedio de estrés
        $promedios = $this->calcularPromediosForma($forma);
        $estresNivel = $promedios['estres_total_nivel'] ?? 'muy_bajo';
        $estresPuntaje = number_format($promedios['estres_total_puntaje'] ?? 0, 1);
        $estresColor = $this->stressColors[$estresNivel] ?? '#4CAF50';
        $estresTextColor = $estresNivel === 'medio' ? '#333' : '#fff';

        $html = '
<h1 style="font-size: 14pt; margin: 0 0 8pt 0; padding-bottom: 5pt; border-bottom: 2pt solid #006699;">Síntomas de Estrés - Forma ' . $forma . '</h1>
<p style="font-size: 9pt; color: #666; margin: 0 0 10pt 0; text-align: center;">' . $titulo . ' (n=' . $n . ')</p>

<div style="text-align: center; margin-bottom: 10pt; padding: 8pt; background: ' . $estresColor . '; color: ' . $estresTextColor . ';">
    <span style="font-weight: bold; font-size: 10pt;">NIVEL TOTAL DE ESTRÉS: ' . strtoupper($this->stressNames[$estresNivel] ?? $estresNivel) . ' (' . $estresPuntaje . ')</span>
</div>

<table style="width: 100%; border-collapse: collapse; font-size: 6.5pt; margin-bottom: 10pt;">
    <thead>
        <tr>
            <th style="width: 4%; background-color: #f8f9fa; padding: 4pt 2pt; border: 1px solid #ddd; text-align: center; font-weight: bold;">#</th>
            <th style="width: 40%; background-color: #f8f9fa; padding: 4pt 2pt; border: 1px solid #ddd; text-align: left; font-weight: bold;">Síntoma / Pregunta</th>
            <th style="width: 11%; background-color: #dc3545; color: white; padding: 4pt 2pt; border: 1px solid #ddd; text-align: center; font-weight: bold;">Siempre</th>
            <th style="width: 11%; background-color: #fd7e14; color: white; padding: 4pt 2pt; border: 1px solid #ddd; text-align: center; font-weight: bold;">Casi Siempre</th>
            <th style="width: 11%; background-color: #ffc107; color: #333; padding: 4pt 2pt; border: 1px solid #ddd; text-align: center; font-weight: bold;">A Veces</th>
            <th style="width: 11%; background-color: #28a745; color: white; padding: 4pt 2pt; border: 1px solid #ddd; text-align: center; font-weight: bold;">Nunca</th>
            <th style="width: 12%; background-color: #6c757d; color: white; padding: 4pt 2pt; border: 1px solid #ddd; text-align: center; font-weight: bold;">Crítico</th>
        </tr>
    </thead>
    <tbody>
';

        // Filas de síntomas
        for ($i = 1; $i <= 31; $i++) {
            $data = $this->symptomData[$forma][$i] ?? [
                'siempre' => 0,
                'casi_siempre' => 0,
                'a_veces' => 0,
                'nunca' => 0,
                'total' => 0,
            ];

            $critico = $data['siempre'] + $data['casi_siempre'];
            $bgRow = ($i % 2 === 0) ? '#f8f9fa' : '#fff';

            // Estilos para celdas críticas
            $siempreStyle = $data['siempre'] > 0 ? 'background: #ffebee; color: #c62828; font-weight: bold;' : '';
            $casiSiempreStyle = $data['casi_siempre'] > 0 ? 'background: #fff3e0; color: #e65100; font-weight: bold;' : '';
            $criticoStyle = $critico > 0 ? 'background: #ffcdd2; color: #b71c1c; font-weight: bold;' : '';

            $html .= '
        <tr style="background: ' . $bgRow . ';">
            <td style="padding: 3pt 2pt; border: 1px solid #ddd; text-align: center; font-weight: bold;">' . $i . '</td>
            <td style="padding: 3pt 2pt; border: 1px solid #ddd; text-align: left;">' . esc($this->estresQuestions[$i]) . '</td>
            <td style="padding: 3pt 2pt; border: 1px solid #ddd; text-align: center; ' . $siempreStyle . '">' . $data['siempre'] . '</td>
            <td style="padding: 3pt 2pt; border: 1px solid #ddd; text-align: center; ' . $casiSiempreStyle . '">' . $data['casi_siempre'] . '</td>
            <td style="padding: 3pt 2pt; border: 1px solid #ddd; text-align: center;">' . $data['a_veces'] . '</td>
            <td style="padding: 3pt 2pt; border: 1px solid #ddd; text-align: center;">' . $data['nunca'] . '</td>
            <td style="padding: 3pt 2pt; border: 1px solid #ddd; text-align: center; ' . $criticoStyle . '">' . $critico . '</td>
        </tr>
';
        }

        $html .= '
    </tbody>
</table>

<div style="padding: 6pt; background: #e3f2fd; border-left: 3pt solid #2196F3; font-size: 7pt;">
    <strong>Interpretación de la tabla:</strong>
    <ul style="margin: 4pt 0 0 12pt; padding: 0;">
        <li><strong>Siempre:</strong> El trabajador presenta este síntoma de forma permanente (mayor riesgo)</li>
        <li><strong>Casi Siempre:</strong> El trabajador presenta este síntoma frecuentemente (alto riesgo)</li>
        <li><strong>A Veces:</strong> El trabajador presenta este síntoma ocasionalmente (riesgo moderado)</li>
        <li><strong>Nunca:</strong> El trabajador NO presenta este síntoma (sin riesgo)</li>
        <li><strong>Crítico:</strong> Suma de "Siempre" + "Casi Siempre" - indica cuántas personas requieren intervención urgente</li>
    </ul>
</div>
';

        // Interpretación según nivel
        if (in_array($estresNivel, ['alto', 'muy_alto'])) {
            $html .= '
<div style="margin-top: 10pt; padding: 8pt; background: #ffebee; border-left: 3pt solid #F44336; font-size: 8pt;">
    <strong style="color: #c62828;">Alerta:</strong> El nivel de síntomas de estrés detectado es <strong>' . strtoupper($this->stressNames[$estresNivel]) . '</strong>. Se requiere intervención inmediata en el marco de un programa de vigilancia epidemiológica. Los trabajadores presentan alta probabilidad de asociación con efectos negativos en la salud física y mental.
</div>
';
        }

        return $html;
    }
}
