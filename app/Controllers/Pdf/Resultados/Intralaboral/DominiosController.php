<?php

namespace App\Controllers\Pdf\Resultados\Intralaboral;

use App\Controllers\Pdf\PdfBaseController;
use App\Models\CalculatedResultModel;
use App\Models\ReportSectionModel;

/**
 * Controller para los Dominios Intralaborales del informe PDF
 * Genera las páginas de los 4 dominios para Forma A y Forma B
 */
class DominiosController extends PdfBaseController
{
    protected $calculatedResultModel;
    protected $reportSectionModel;

    /**
     * Definición de los dominios intralaborales
     */
    protected $dominios = [
        'liderazgo' => [
            'nombre' => 'Liderazgo y Relaciones Sociales en el Trabajo',
            'codigo' => 'liderazgo',
            'definicion' => 'Se refiere al tipo de relación social que se establece entre los superiores jerárquicos y sus colaboradores y cuyas características influyen en la forma de trabajar y en el ambiente de relaciones de un área.',
            'campo_puntaje' => 'dom_liderazgo_puntaje',
            'campo_nivel' => 'dom_liderazgo_nivel',
            'dimensiones' => [
                'Características del Liderazgo',
                'Relaciones Sociales en el Trabajo',
                'Retroalimentación del Desempeño',
                'Relación con los Colaboradores (Solo Forma A)',
            ],
        ],
        'control' => [
            'nombre' => 'Control sobre el Trabajo',
            'codigo' => 'control',
            'definicion' => 'Posibilidad que el trabajo ofrece al individuo para influir y tomar decisiones sobre los diversos aspectos que intervienen en su realización.',
            'campo_puntaje' => 'dom_control_puntaje',
            'campo_nivel' => 'dom_control_nivel',
            'dimensiones' => [
                'Claridad del Rol',
                'Capacitación',
                'Participación y Manejo del Cambio',
                'Oportunidades para el Uso y Desarrollo de Habilidades',
                'Control y Autonomía sobre el Trabajo',
            ],
        ],
        'demandas' => [
            'nombre' => 'Demandas del Trabajo',
            'codigo' => 'demandas',
            'definicion' => 'Se refieren a las exigencias que el trabajo impone al individuo. Pueden ser de diversa naturaleza, como cuantitativas, cognitivas o mentales, emocionales, de responsabilidad, del ambiente físico laboral y de la jornada de trabajo.',
            'campo_puntaje' => 'dom_demandas_puntaje',
            'campo_nivel' => 'dom_demandas_nivel',
            'dimensiones' => [
                'Demandas Ambientales y de Esfuerzo Físico',
                'Demandas Emocionales',
                'Demandas Cuantitativas',
                'Influencia del Trabajo sobre el Entorno Extralaboral',
                'Exigencias de Responsabilidad del Cargo (Solo Forma A)',
                'Demandas de Carga Mental',
                'Consistencia del Rol (Solo Forma A)',
                'Demandas de la Jornada de Trabajo',
            ],
        ],
        'recompensas' => [
            'nombre' => 'Recompensas',
            'codigo' => 'recompensas',
            'definicion' => 'Este término se refiere a la retribución que el trabajador obtiene a cambio de sus contribuciones o esfuerzos laborales.',
            'campo_puntaje' => 'dom_recompensas_puntaje',
            'campo_nivel' => 'dom_recompensas_nivel',
            'dimensiones' => [
                'Recompensas Derivadas de la Pertenencia',
                'Reconocimiento y Compensación',
            ],
        ],
    ];

    /**
     * Baremos por forma y dominio
     */
    protected $baremos = [
        'A' => [
            'liderazgo' => ['sin_riesgo' => [0.0, 9.1], 'riesgo_bajo' => [9.2, 17.7], 'riesgo_medio' => [17.8, 26.0], 'riesgo_alto' => [26.1, 36.5], 'riesgo_muy_alto' => [36.6, 100.0]],
            'control' => ['sin_riesgo' => [0.0, 8.3], 'riesgo_bajo' => [8.4, 16.7], 'riesgo_medio' => [16.8, 25.0], 'riesgo_alto' => [25.1, 35.4], 'riesgo_muy_alto' => [35.5, 100.0]],
            'demandas' => ['sin_riesgo' => [0.0, 28.5], 'riesgo_bajo' => [28.6, 35.0], 'riesgo_medio' => [35.1, 41.3], 'riesgo_alto' => [41.4, 47.5], 'riesgo_muy_alto' => [47.6, 100.0]],
            'recompensas' => ['sin_riesgo' => [0.0, 2.3], 'riesgo_bajo' => [2.4, 9.1], 'riesgo_medio' => [9.2, 15.9], 'riesgo_alto' => [16.0, 25.0], 'riesgo_muy_alto' => [25.1, 100.0]],
        ],
        'B' => [
            'liderazgo' => ['sin_riesgo' => [0.0, 8.3], 'riesgo_bajo' => [8.4, 16.7], 'riesgo_medio' => [16.8, 25.0], 'riesgo_alto' => [25.1, 37.5], 'riesgo_muy_alto' => [37.6, 100.0]],
            'control' => ['sin_riesgo' => [0.0, 12.5], 'riesgo_bajo' => [12.6, 22.9], 'riesgo_medio' => [23.0, 33.3], 'riesgo_alto' => [33.4, 45.8], 'riesgo_muy_alto' => [45.9, 100.0]],
            'demandas' => ['sin_riesgo' => [0.0, 27.5], 'riesgo_bajo' => [27.6, 33.8], 'riesgo_medio' => [33.9, 40.0], 'riesgo_alto' => [40.1, 47.5], 'riesgo_muy_alto' => [47.6, 100.0]],
            'recompensas' => ['sin_riesgo' => [0.0, 2.3], 'riesgo_bajo' => [2.4, 9.1], 'riesgo_medio' => [9.2, 15.9], 'riesgo_alto' => [16.0, 25.0], 'riesgo_muy_alto' => [25.1, 100.0]],
        ],
    ];

    public function __construct()
    {
        $this->calculatedResultModel = new CalculatedResultModel();
        $this->reportSectionModel = new ReportSectionModel();
    }

    /**
     * Renderiza las páginas de dominios intralaborales
     * Primero todos los dominios de Forma A, luego todos los de Forma B
     *
     * @param int $batteryServiceId
     * @param string|null $forma 'A', 'B' o null para ambas
     * @return string HTML de las páginas
     */
    public function render($batteryServiceId, $forma = null)
    {
        $this->initializeData($batteryServiceId);

        $formasDisponibles = $forma ? [strtoupper($forma)] : ['A', 'B'];
        $html = '';

        // Página introductoria
        $html .= $this->renderView('pdf/intralaboral/intro_dominios', []);
        $html .= $this->pageBreak();

        foreach ($formasDisponibles as $f) {
            $results = $this->calculatedResultModel
                ->where('battery_service_id', $batteryServiceId)
                ->where('intralaboral_form_type', $f)
                ->findAll();

            if (empty($results)) {
                continue;
            }

            $reportTexts = $this->getReportTexts($batteryServiceId, $f);

            // Generar página para cada dominio
            foreach ($this->dominios as $domKey => $dominio) {
                $dominioData = $this->calculateDominioData($results, $domKey, $dominio, $f);
                $dominioData['texto_ia'] = $reportTexts[$domKey] ?? null;

                $html .= $this->renderView('pdf/intralaboral/dominio_page', [
                    'dominio' => $dominioData,
                    'forma' => $f,
                    'baremos' => $this->baremos[$f][$domKey] ?? null,
                    'tipoTrabajadores' => $f === 'A' ? 'Jefes, Profesionales y Técnicos' : 'Auxiliares y Operarios',
                ]);
                $html .= $this->pageBreak();
            }
        }

        return $html;
    }

    /**
     * Calcula los datos de un dominio específico
     */
    private function calculateDominioData($results, $domKey, $dominio, $forma)
    {
        $campoPuntaje = $dominio['campo_puntaje'];
        $campoNivel = $dominio['campo_nivel'];

        // Calcular promedio
        $puntajes = array_column($results, $campoPuntaje);
        $puntajes = array_filter($puntajes, function($v) { return $v !== null && $v !== ''; });
        $promedio = !empty($puntajes) ? array_sum($puntajes) / count($puntajes) : 0;

        // Contar por nivel
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

        // Porcentajes
        $porcentajes = [];
        foreach ($distribucion as $nivel => $count) {
            $porcentajes[$nivel] = $total > 0 ? round(($count / $total) * 100, 1) : 0;
        }

        // Nivel del promedio
        $nivelPromedio = $this->getNivelFromPuntaje($promedio, $this->baremos[$forma][$domKey] ?? []);

        // Filtrar dimensiones según forma
        $dimensionesFiltered = array_filter($dominio['dimensiones'], function($dim) use ($forma) {
            if (strpos($dim, '(Solo Forma A)') !== false && $forma === 'B') {
                return false;
            }
            return true;
        });

        return [
            'key' => $domKey,
            'codigo' => $dominio['codigo'],
            'nombre' => $dominio['nombre'],
            'definicion' => $dominio['definicion'],
            'promedio' => $promedio,
            'nivel' => $nivelPromedio,
            'total_evaluados' => $total,
            'distribucion' => $distribucion,
            'porcentajes' => $porcentajes,
            'dimensiones' => array_values($dimensionesFiltered),
            'accion' => $this->getRiskAction($nivelPromedio),
        ];
    }

    /**
     * Obtiene nivel de riesgo según puntaje
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
     * Obtiene textos de report_sections
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
            ->where('questionnaire_type', 'intralaboral')
            ->where('form_type', $forma)
            ->where('section_level', 'domain')
            ->findAll();

        $texts = [];
        foreach ($sections as $section) {
            $domainCode = $section['domain_code'] ?? '';
            if (!empty($section['ai_generated_text'])) {
                $texts[$domainCode] = $section['ai_generated_text'];
            }
        }

        return $texts;
    }

    /**
     * Preview en navegador
     */
    public function preview($batteryServiceId, $forma = null)
    {
        $html = $this->render($batteryServiceId, $forma);

        $title = 'Preview: Dominios Intralaborales';
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
