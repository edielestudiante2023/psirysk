<?php

namespace App\Controllers\Pdf\Resultados\Intralaboral;

use App\Controllers\Pdf\PdfBaseController;
use App\Models\CalculatedResultModel;
use App\Models\ReportSectionModel;

/**
 * Controller para los Totales Intralaborales del informe PDF
 * Genera las páginas de Total General, Total Forma A y Total Forma B
 */
class TotalController extends PdfBaseController
{
    protected $calculatedResultModel;
    protected $reportSectionModel;

    /**
     * Baremos para el total intralaboral
     */
    protected $baremos = [
        'A' => [
            'sin_riesgo' => [0.0, 19.7],
            'riesgo_bajo' => [19.8, 25.8],
            'riesgo_medio' => [25.9, 31.5],
            'riesgo_alto' => [31.6, 38.7],
            'riesgo_muy_alto' => [38.8, 100.0],
        ],
        'B' => [
            'sin_riesgo' => [0.0, 20.6],
            'riesgo_bajo' => [20.7, 26.0],
            'riesgo_medio' => [26.1, 31.2],
            'riesgo_alto' => [31.3, 38.7],
            'riesgo_muy_alto' => [38.8, 100.0],
        ],
    ];

    /**
     * Baremos para el puntaje total general de factores de riesgo psicosocial
     * (Intralaboral + Extralaboral) - Tabla 34 Resolución 2404/2019
     */
    protected $baremosTotalGeneral = [
        'A' => [
            'sin_riesgo' => [0.0, 18.8],
            'riesgo_bajo' => [18.9, 24.4],
            'riesgo_medio' => [24.5, 29.5],
            'riesgo_alto' => [29.6, 35.4],
            'riesgo_muy_alto' => [35.5, 100.0],
        ],
        'B' => [
            'sin_riesgo' => [0.0, 19.9],
            'riesgo_bajo' => [20.0, 24.8],
            'riesgo_medio' => [24.9, 29.5],
            'riesgo_alto' => [29.6, 35.4],
            'riesgo_muy_alto' => [35.5, 100.0],
        ],
    ];

    public function __construct()
    {
        $this->calculatedResultModel = new CalculatedResultModel();
        $this->reportSectionModel = new ReportSectionModel();
    }

    /**
     * Renderiza las páginas de totales intralaborales
     *
     * @param int $batteryServiceId
     * @return string HTML de las páginas
     */
    public function render($batteryServiceId)
    {
        $this->initializeData($batteryServiceId);

        $html = '';

        // Página introductoria
        $html .= $this->renderView('pdf/intralaboral/intro_total', []);
        $html .= $this->pageBreak();

        // Obtener resultados por forma
        $resultsA = $this->calculatedResultModel
            ->where('battery_service_id', $batteryServiceId)
            ->where('intralaboral_form_type', 'A')
            ->findAll();

        $resultsB = $this->calculatedResultModel
            ->where('battery_service_id', $batteryServiceId)
            ->where('intralaboral_form_type', 'B')
            ->findAll();

        // Obtener textos de report_sections
        $reportTexts = $this->getReportTexts($batteryServiceId);

        // Generar página de Total Forma A si hay datos
        if (!empty($resultsA)) {
            $totalDataA = $this->calculateTotalData($resultsA, 'A');
            $totalDataA['texto_ia'] = $reportTexts['A'] ?? null;

            $html .= $this->renderView('pdf/intralaboral/total_page', [
                'total' => $totalDataA,
                'forma' => 'A',
                'baremos' => $this->baremos['A'],
                'tipoTrabajadores' => 'Jefes, Profesionales y Técnicos',
            ]);
            $html .= $this->pageBreak();
        }

        // Generar página de Total Forma B si hay datos
        if (!empty($resultsB)) {
            $totalDataB = $this->calculateTotalData($resultsB, 'B');
            $totalDataB['texto_ia'] = $reportTexts['B'] ?? null;

            $html .= $this->renderView('pdf/intralaboral/total_page', [
                'total' => $totalDataB,
                'forma' => 'B',
                'baremos' => $this->baremos['B'],
                'tipoTrabajadores' => 'Auxiliares y Operarios',
            ]);
            $html .= $this->pageBreak();
        }

        // Generar página de Total General Intralaboral (conjunto) si hay datos de ambas formas
        if (!empty($resultsA) || !empty($resultsB)) {
            $allResults = array_merge($resultsA ?? [], $resultsB ?? []);
            $totalDataGeneral = $this->calculateTotalDataGeneral($allResults, $resultsA, $resultsB);
            $totalDataGeneral['texto_ia'] = $reportTexts['conjunto'] ?? null;

            $html .= $this->renderView('pdf/intralaboral/total_general_page', [
                'total' => $totalDataGeneral,
                'totalA' => !empty($resultsA) ? $this->calculateTotalData($resultsA, 'A') : null,
                'totalB' => !empty($resultsB) ? $this->calculateTotalData($resultsB, 'B') : null,
            ]);
            $html .= $this->pageBreak();
        }

        // Generar página de Total General Psicosocial (Intralaboral + Extralaboral) - Tabla 34
        if (!empty($resultsA) || !empty($resultsB)) {
            $allResults = array_merge($resultsA ?? [], $resultsB ?? []);
            $totalPsicosocialData = $this->calculateTotalPsicosocial($allResults, $resultsA, $resultsB);

            $html .= $this->renderView('pdf/intralaboral/total_psicosocial_page', [
                'total' => $totalPsicosocialData,
                'totalA' => !empty($resultsA) ? $this->calculateTotalPsicosocialByForma($resultsA, 'A') : null,
                'totalB' => !empty($resultsB) ? $this->calculateTotalPsicosocialByForma($resultsB, 'B') : null,
                'baremosTotalGeneral' => $this->baremosTotalGeneral,
            ]);
            $html .= $this->pageBreak();
        }

        return $html;
    }

    /**
     * Calcula los datos del total para una forma específica
     */
    private function calculateTotalData($results, $forma)
    {
        $puntajes = array_column($results, 'intralaboral_total_puntaje');
        $puntajes = array_filter($puntajes, function($v) { return $v !== null && $v !== ''; });
        $promedio = !empty($puntajes) ? array_sum($puntajes) / count($puntajes) : 0;

        // Contar por nivel
        $niveles = array_column($results, 'intralaboral_total_nivel');
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

        // Determinar nivel del promedio
        $nivelPromedio = $this->getNivelFromPuntaje($promedio, $this->baremos[$forma]);

        return [
            'forma' => $forma,
            'promedio' => $promedio,
            'nivel' => $nivelPromedio,
            'total_evaluados' => $total,
            'distribucion' => $distribucion,
            'porcentajes' => $porcentajes,
            'accion' => $this->getRiskAction($nivelPromedio),
        ];
    }

    /**
     * Calcula datos del total general combinando ambas formas
     */
    private function calculateTotalDataGeneral($allResults, $resultsA, $resultsB)
    {
        $puntajes = array_column($allResults, 'intralaboral_total_puntaje');
        $puntajes = array_filter($puntajes, function($v) { return $v !== null && $v !== ''; });
        $promedio = !empty($puntajes) ? array_sum($puntajes) / count($puntajes) : 0;

        $total = count($allResults);
        $totalA = count($resultsA ?? []);
        $totalB = count($resultsB ?? []);

        // Distribución combinada
        $niveles = array_column($allResults, 'intralaboral_total_nivel');
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

        $porcentajes = [];
        foreach ($distribucion as $nivel => $count) {
            $porcentajes[$nivel] = $total > 0 ? round(($count / $total) * 100, 1) : 0;
        }

        // Nivel usando baremos de Forma A como referencia
        $nivelPromedio = $this->getNivelFromPuntaje($promedio, $this->baremos['A']);

        return [
            'forma' => 'conjunto',
            'promedio' => $promedio,
            'nivel' => $nivelPromedio,
            'total_evaluados' => $total,
            'total_forma_a' => $totalA,
            'total_forma_b' => $totalB,
            'distribucion' => $distribucion,
            'porcentajes' => $porcentajes,
            'accion' => $this->getRiskAction($nivelPromedio),
        ];
    }

    /**
     * Calcula el puntaje total general psicosocial (Intralaboral + Extralaboral)
     * Fórmula: (Puntaje Intralaboral + Puntaje Extralaboral) / 2
     */
    private function calculateTotalPsicosocial($allResults, $resultsA, $resultsB)
    {
        $puntajesGenerales = [];

        foreach ($allResults as $result) {
            $intra = $result['intralaboral_total_puntaje'] ?? null;
            $extra = $result['extralaboral_total_puntaje'] ?? null;

            if ($intra !== null && $intra !== '' && $extra !== null && $extra !== '') {
                $puntajesGenerales[] = ($intra + $extra) / 2;
            }
        }

        $promedioGeneral = !empty($puntajesGenerales) ? array_sum($puntajesGenerales) / count($puntajesGenerales) : 0;

        // Promedios separados
        $promedioIntra = 0;
        $promedioExtra = 0;
        $puntajesIntra = array_filter(array_column($allResults, 'intralaboral_total_puntaje'), fn($v) => $v !== null && $v !== '');
        $puntajesExtra = array_filter(array_column($allResults, 'extralaboral_total_puntaje'), fn($v) => $v !== null && $v !== '');

        if (!empty($puntajesIntra)) {
            $promedioIntra = array_sum($puntajesIntra) / count($puntajesIntra);
        }
        if (!empty($puntajesExtra)) {
            $promedioExtra = array_sum($puntajesExtra) / count($puntajesExtra);
        }

        $total = count($allResults);
        $totalA = count($resultsA ?? []);
        $totalB = count($resultsB ?? []);

        // Distribución por nivel usando baremos Forma A (referencia)
        $distribucion = [
            'sin_riesgo' => 0,
            'riesgo_bajo' => 0,
            'riesgo_medio' => 0,
            'riesgo_alto' => 0,
            'riesgo_muy_alto' => 0,
        ];

        foreach ($allResults as $result) {
            $forma = $result['intralaboral_form_type'] ?? 'A';
            $intra = $result['intralaboral_total_puntaje'] ?? null;
            $extra = $result['extralaboral_total_puntaje'] ?? null;

            if ($intra !== null && $intra !== '' && $extra !== null && $extra !== '') {
                $puntajeTotal = ($intra + $extra) / 2;
                $nivel = $this->getNivelFromPuntaje($puntajeTotal, $this->baremosTotalGeneral[$forma]);
                if (isset($distribucion[$nivel])) {
                    $distribucion[$nivel]++;
                }
            }
        }

        $porcentajes = [];
        foreach ($distribucion as $nivel => $count) {
            $porcentajes[$nivel] = $total > 0 ? round(($count / $total) * 100, 1) : 0;
        }

        // Nivel del promedio general usando baremos Forma A
        $nivelPromedio = $this->getNivelFromPuntaje($promedioGeneral, $this->baremosTotalGeneral['A']);

        return [
            'promedio_general' => $promedioGeneral,
            'promedio_intralaboral' => $promedioIntra,
            'promedio_extralaboral' => $promedioExtra,
            'nivel' => $nivelPromedio,
            'total_evaluados' => $total,
            'total_forma_a' => $totalA,
            'total_forma_b' => $totalB,
            'distribucion' => $distribucion,
            'porcentajes' => $porcentajes,
            'accion' => $this->getRiskAction($nivelPromedio),
        ];
    }

    /**
     * Calcula el puntaje total psicosocial por forma específica
     */
    private function calculateTotalPsicosocialByForma($results, $forma)
    {
        $puntajesGenerales = [];

        foreach ($results as $result) {
            $intra = $result['intralaboral_total_puntaje'] ?? null;
            $extra = $result['extralaboral_total_puntaje'] ?? null;

            if ($intra !== null && $intra !== '' && $extra !== null && $extra !== '') {
                $puntajesGenerales[] = ($intra + $extra) / 2;
            }
        }

        $promedioGeneral = !empty($puntajesGenerales) ? array_sum($puntajesGenerales) / count($puntajesGenerales) : 0;

        // Promedios separados
        $puntajesIntra = array_filter(array_column($results, 'intralaboral_total_puntaje'), fn($v) => $v !== null && $v !== '');
        $puntajesExtra = array_filter(array_column($results, 'extralaboral_total_puntaje'), fn($v) => $v !== null && $v !== '');

        $promedioIntra = !empty($puntajesIntra) ? array_sum($puntajesIntra) / count($puntajesIntra) : 0;
        $promedioExtra = !empty($puntajesExtra) ? array_sum($puntajesExtra) / count($puntajesExtra) : 0;

        $total = count($results);

        // Distribución por nivel
        $distribucion = [
            'sin_riesgo' => 0,
            'riesgo_bajo' => 0,
            'riesgo_medio' => 0,
            'riesgo_alto' => 0,
            'riesgo_muy_alto' => 0,
        ];

        foreach ($results as $result) {
            $intra = $result['intralaboral_total_puntaje'] ?? null;
            $extra = $result['extralaboral_total_puntaje'] ?? null;

            if ($intra !== null && $intra !== '' && $extra !== null && $extra !== '') {
                $puntajeTotal = ($intra + $extra) / 2;
                $nivel = $this->getNivelFromPuntaje($puntajeTotal, $this->baremosTotalGeneral[$forma]);
                if (isset($distribucion[$nivel])) {
                    $distribucion[$nivel]++;
                }
            }
        }

        $porcentajes = [];
        foreach ($distribucion as $nivel => $count) {
            $porcentajes[$nivel] = $total > 0 ? round(($count / $total) * 100, 1) : 0;
        }

        $nivelPromedio = $this->getNivelFromPuntaje($promedioGeneral, $this->baremosTotalGeneral[$forma]);

        return [
            'forma' => $forma,
            'promedio_general' => $promedioGeneral,
            'promedio_intralaboral' => $promedioIntra,
            'promedio_extralaboral' => $promedioExtra,
            'nivel' => $nivelPromedio,
            'total_evaluados' => $total,
            'distribucion' => $distribucion,
            'porcentajes' => $porcentajes,
            'accion' => $this->getRiskAction($nivelPromedio),
        ];
    }

    /**
     * Obtiene el nivel de riesgo basado en puntaje
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
     * Obtiene los textos de report_sections
     */
    private function getReportTexts($batteryServiceId)
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
            ->where('section_level', 'total')
            ->findAll();

        $texts = [];
        foreach ($sections as $section) {
            $formType = $section['form_type'] ?? 'conjunto';
            if (!empty($section['ai_generated_text'])) {
                $texts[$formType] = $section['ai_generated_text'];
            }
        }

        return $texts;
    }

    /**
     * Preview en navegador
     */
    public function preview($batteryServiceId)
    {
        $html = $this->render($batteryServiceId);

        return view('pdf/_partials/preview_wrapper', [
            'content' => $html,
            'pageTitle' => 'Preview: Totales Intralaborales',
            'batteryServiceId' => $batteryServiceId
        ]);
    }
}
