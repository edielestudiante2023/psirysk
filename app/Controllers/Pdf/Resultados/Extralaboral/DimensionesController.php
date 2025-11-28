<?php

namespace App\Controllers\Pdf\Resultados\Extralaboral;

use App\Controllers\Pdf\PdfBaseController;
use App\Models\CalculatedResultModel;
use App\Models\ReportSectionModel;

/**
 * Controller para las Dimensiones Extralaborales del informe PDF
 * Genera las 7 dimensiones extralaborales (aplican a Forma A y B)
 */
class DimensionesController extends PdfBaseController
{
    protected $calculatedResultModel;
    protected $reportSectionModel;

    /**
     * Definición de las 7 dimensiones extralaborales
     * Todas aplican tanto a Forma A como a Forma B
     */
    protected $dimensiones = [
        'ext_tiempo_fuera_trabajo' => [
            'nombre' => 'Tiempo Fuera del Trabajo',
            'definicion' => 'Se refiere al tiempo que el individuo dedica a actividades diferentes a las laborales, como descansar, compartir con familia y amigos, atender responsabilidades personales o domésticas, realizar actividades de recreación y ocio.',
            'campo_puntaje' => 'ext_tiempo_fuera_trabajo_puntaje',
            'campo_nivel' => 'ext_tiempo_fuera_trabajo_nivel',
        ],
        'ext_relaciones_familiares' => [
            'nombre' => 'Relaciones Familiares',
            'definicion' => 'Propiedades que caracterizan las interacciones del individuo con su núcleo familiar.',
            'campo_puntaje' => 'ext_relaciones_familiares_puntaje',
            'campo_nivel' => 'ext_relaciones_familiares_nivel',
        ],
        'ext_comunicacion_relaciones' => [
            'nombre' => 'Comunicación y Relaciones Interpersonales',
            'definicion' => 'Cualidades que caracterizan la comunicación e interacciones del individuo con sus allegados y amigos.',
            'campo_puntaje' => 'ext_comunicacion_relaciones_puntaje',
            'campo_nivel' => 'ext_comunicacion_relaciones_nivel',
        ],
        'ext_situacion_economica' => [
            'nombre' => 'Situación Económica del Grupo Familiar',
            'definicion' => 'Trata de la disponibilidad de medios económicos para que el trabajador y su grupo familiar atiendan sus gastos básicos.',
            'campo_puntaje' => 'ext_situacion_economica_puntaje',
            'campo_nivel' => 'ext_situacion_economica_nivel',
        ],
        'ext_caracteristicas_vivienda' => [
            'nombre' => 'Características de la Vivienda y de su Entorno',
            'definicion' => 'Se refiere a las condiciones de infraestructura, ubicación y entorno de las instalaciones físicas del lugar habitual de residencia del trabajador y de su grupo familiar.',
            'campo_puntaje' => 'ext_caracteristicas_vivienda_puntaje',
            'campo_nivel' => 'ext_caracteristicas_vivienda_nivel',
        ],
        'ext_influencia_entorno' => [
            'nombre' => 'Influencia del Entorno Extralaboral sobre el Trabajo',
            'definicion' => 'Corresponde al influjo de las exigencias de los roles familiares y personales en el bienestar y en la actividad laboral del trabajador.',
            'campo_puntaje' => 'ext_influencia_entorno_puntaje',
            'campo_nivel' => 'ext_influencia_entorno_nivel',
        ],
        'ext_desplazamiento' => [
            'nombre' => 'Desplazamiento Vivienda – Trabajo – Vivienda',
            'definicion' => 'Son las condiciones en que se realiza el traslado del trabajador desde su sitio de vivienda hasta su lugar de trabajo y viceversa. Comprende la facilidad, la comodidad del transporte y la duración del recorrido.',
            'campo_puntaje' => 'ext_desplazamiento_puntaje',
            'campo_nivel' => 'ext_desplazamiento_nivel',
        ],
    ];

    /**
     * Baremos extralaborales (iguales para A y B)
     */
    protected $baremos = [
        'ext_tiempo_fuera_trabajo' => ['sin_riesgo' => [0.0, 6.3], 'riesgo_bajo' => [6.4, 25.0], 'riesgo_medio' => [25.1, 37.5], 'riesgo_alto' => [37.6, 50.0], 'riesgo_muy_alto' => [50.1, 100.0]],
        'ext_relaciones_familiares' => ['sin_riesgo' => [0.0, 8.3], 'riesgo_bajo' => [8.4, 25.0], 'riesgo_medio' => [25.1, 33.3], 'riesgo_alto' => [33.4, 50.0], 'riesgo_muy_alto' => [50.1, 100.0]],
        'ext_comunicacion_relaciones' => ['sin_riesgo' => [0.0, 10.0], 'riesgo_bajo' => [10.1, 20.0], 'riesgo_medio' => [20.1, 30.0], 'riesgo_alto' => [30.1, 45.0], 'riesgo_muy_alto' => [45.1, 100.0]],
        'ext_situacion_economica' => ['sin_riesgo' => [0.0, 16.7], 'riesgo_bajo' => [16.8, 25.0], 'riesgo_medio' => [25.1, 33.3], 'riesgo_alto' => [33.4, 50.0], 'riesgo_muy_alto' => [50.1, 100.0]],
        'ext_caracteristicas_vivienda' => ['sin_riesgo' => [0.0, 5.6], 'riesgo_bajo' => [5.7, 11.1], 'riesgo_medio' => [11.2, 19.4], 'riesgo_alto' => [19.5, 30.6], 'riesgo_muy_alto' => [30.7, 100.0]],
        'ext_influencia_entorno' => ['sin_riesgo' => [0.0, 8.3], 'riesgo_bajo' => [8.4, 16.7], 'riesgo_medio' => [16.8, 25.0], 'riesgo_alto' => [25.1, 41.7], 'riesgo_muy_alto' => [41.8, 100.0]],
        'ext_desplazamiento' => ['sin_riesgo' => [0.0, 0.0], 'riesgo_bajo' => [0.1, 16.7], 'riesgo_medio' => [16.8, 33.3], 'riesgo_alto' => [33.4, 50.0], 'riesgo_muy_alto' => [50.1, 100.0]],
    ];

    public function __construct()
    {
        $this->calculatedResultModel = new CalculatedResultModel();
        $this->reportSectionModel = new ReportSectionModel();
    }

    /**
     * Renderiza todas las páginas de dimensiones extralaborales
     * Intercalando Forma A y Forma B para cada dimensión
     *
     * @param int $batteryServiceId
     * @param string|null $forma 'A', 'B' o null para ambas (intercalado)
     * @return string HTML de todas las páginas
     */
    public function render($batteryServiceId, $forma = null)
    {
        $this->initializeData($batteryServiceId);

        $formasDisponibles = $forma ? [strtoupper($forma)] : ['A', 'B'];

        // Cargar resultados para cada forma
        $resultsByForma = [];
        $reportTextsByForma = [];

        foreach ($formasDisponibles as $f) {
            $results = $this->calculatedResultModel
                ->where('battery_service_id', $batteryServiceId)
                ->where('intralaboral_form_type', $f)
                ->findAll();

            if (!empty($results)) {
                $resultsByForma[$f] = $results;
                $reportTextsByForma[$f] = $this->getReportTexts($batteryServiceId, $f);
            }
        }

        if (empty($resultsByForma)) {
            return '';
        }

        $html = '';

        // Página introductoria
        $html .= $this->renderView('pdf/extralaboral/intro_dimensiones', []);
        $html .= $this->pageBreak();

        // Para cada dimensión extralaboral
        foreach ($this->dimensiones as $dimKey => $dimension) {
            // Generar página para cada forma (A primero, B después)
            foreach (['A', 'B'] as $f) {
                if (!isset($resultsByForma[$f])) {
                    continue;
                }

                $dimensionData = $this->calculateDimensionData(
                    $resultsByForma[$f],
                    $dimKey,
                    $dimension,
                    $f
                );
                $dimensionData['texto_ia'] = $reportTextsByForma[$f][$dimKey] ?? null;

                $html .= $this->renderView('pdf/extralaboral/dimension_page', [
                    'dimension' => $dimensionData,
                    'forma' => $f,
                    'baremos' => $this->baremos[$dimKey] ?? null,
                ]);

                $html .= $this->pageBreak();
            }
        }

        return $html;
    }

    /**
     * Calcula los datos de una dimensión específica
     */
    private function calculateDimensionData($results, $dimKey, $dimension, $forma)
    {
        $campoPuntaje = $dimension['campo_puntaje'];
        $campoNivel = $dimension['campo_nivel'];

        // Calcular promedio
        $puntajes = array_column($results, $campoPuntaje);
        $puntajes = array_filter($puntajes, function($v) { return $v !== null && $v !== ''; });
        $promedio = !empty($puntajes) ? array_sum($puntajes) / count($puntajes) : 0;

        // Contar por nivel de riesgo
        $niveles = array_column($results, $campoNivel);
        $distribucion = [
            'sin_riesgo' => 0,
            'riesgo_bajo' => 0,
            'riesgo_medio' => 0,
            'riesgo_alto' => 0,
            'riesgo_muy_alto' => 0,
        ];

        foreach ($niveles as $nivel) {
            if (isset($distribucion[$nivel])) {
                $distribucion[$nivel]++;
            }
        }

        $total = count($results);

        // Calcular porcentajes
        $porcentajes = [];
        foreach ($distribucion as $nivel => $count) {
            $porcentajes[$nivel] = $total > 0 ? round(($count / $total) * 100, 1) : 0;
        }

        // Determinar nivel del promedio
        $nivelPromedio = $this->getNivelFromPuntaje($promedio, $this->baremos[$dimKey] ?? []);

        // Identificar trabajadores en riesgo alto/muy alto
        $trabajadoresRiesgoAlto = [];
        foreach ($results as $result) {
            $nivel = $result[$campoNivel] ?? '';
            if (in_array($nivel, ['riesgo_alto', 'riesgo_muy_alto'])) {
                $trabajadoresRiesgoAlto[] = [
                    'nombre' => $result['worker_name'] ?? '',
                    'area' => $result['department'] ?? '',
                    'cargo' => $result['position'] ?? '',
                    'puntaje' => $result[$campoPuntaje] ?? 0,
                    'nivel' => $nivel,
                ];
            }
        }

        return [
            'key' => $dimKey,
            'nombre' => $dimension['nombre'],
            'dominio' => 'Factores de Riesgo Psicosocial Extralaboral',
            'definicion' => $dimension['definicion'],
            'promedio' => $promedio,
            'nivel' => $nivelPromedio,
            'total_evaluados' => $total,
            'distribucion' => $distribucion,
            'porcentajes' => $porcentajes,
            'trabajadores_riesgo_alto' => $trabajadoresRiesgoAlto,
            'accion' => $this->getRiskAction($nivelPromedio),
        ];
    }

    /**
     * Obtiene el nivel de riesgo basado en el puntaje y baremo
     */
    private function getNivelFromPuntaje($puntaje, $baremo)
    {
        foreach ($baremo as $nivel => $rango) {
            if ($puntaje >= $rango[0] && $puntaje <= $rango[1]) {
                return $nivel;
            }
        }
        return 'sin_riesgo';
    }

    /**
     * Mapeo de dimension_code (BD) a dimension key (controller)
     */
    protected $dimensionCodeMapping = [
        // BD dimension_code => controller key
        'tiempo_fuera' => 'ext_tiempo_fuera_trabajo',
        'relaciones_familiares' => 'ext_relaciones_familiares',
        'comunicacion' => 'ext_comunicacion_relaciones',
        'situacion_economica' => 'ext_situacion_economica',
        'vivienda' => 'ext_caracteristicas_vivienda',
        'influencia_entorno' => 'ext_influencia_entorno',
        'desplazamiento' => 'ext_desplazamiento',
    ];

    /**
     * Obtiene los textos generados por IA de report_sections
     */
    private function getReportTexts($batteryServiceId, $forma)
    {
        $db = \Config\Database::connect();

        $report = $db->query("
            SELECT id FROM reports WHERE battery_service_id = ? LIMIT 1
        ", [$batteryServiceId])->getRowArray();

        if (!$report) {
            return [];
        }

        $sections = $this->reportSectionModel
            ->where('report_id', $report['id'])
            ->where('questionnaire_type', 'extralaboral')
            ->where('form_type', $forma)
            ->where('section_level', 'dimension')
            ->findAll();

        $texts = [];
        foreach ($sections as $section) {
            $dbCode = $section['dimension_code'] ?? '';
            // Mapear dimension_code de BD a key del controller
            $key = $this->dimensionCodeMapping[$dbCode] ?? $dbCode;
            if (!empty($section['ai_generated_text'])) {
                $texts[$key] = $section['ai_generated_text'];
            }
        }

        return $texts;
    }

    /**
     * Preview de las dimensiones extralaborales en navegador
     */
    public function preview($batteryServiceId, $forma = null)
    {
        $html = $this->render($batteryServiceId, $forma);

        $title = 'Preview: Dimensiones Extralaborales';
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
