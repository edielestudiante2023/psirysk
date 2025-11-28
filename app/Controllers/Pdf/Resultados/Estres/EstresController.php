<?php

namespace App\Controllers\Pdf\Resultados\Estres;

use App\Controllers\Pdf\PdfBaseController;
use App\Models\CalculatedResultModel;
use App\Models\ReportSectionModel;

/**
 * Controller para el Cuestionario de Estrés del informe PDF
 * Es un cuestionario independiente que se aplica a Forma A y B
 */
class EstresController extends PdfBaseController
{
    protected $calculatedResultModel;
    protected $reportSectionModel;

    /**
     * Baremos del cuestionario de estrés (iguales para A y B)
     */
    protected $baremos = [
        'sin_riesgo' => [0.0, 7.8],
        'riesgo_bajo' => [7.9, 12.6],
        'riesgo_medio' => [12.7, 17.7],
        'riesgo_alto' => [17.8, 25.0],
        'riesgo_muy_alto' => [25.1, 100.0],
    ];

    public function __construct()
    {
        $this->calculatedResultModel = new CalculatedResultModel();
        $this->reportSectionModel = new ReportSectionModel();
    }

    /**
     * Renderiza las páginas del cuestionario de estrés
     * Intercalando Forma A y Forma B
     *
     * @param int $batteryServiceId
     * @param string|null $forma 'A', 'B' o null para ambas (intercalado)
     * @return string HTML de las páginas
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

        // Agregar separador de dominio Estrés
        $html .= $this->renderDomainSeparator();
        $html .= $this->pageBreak();

        // Generar página para cada forma (A primero, B después)
        foreach (['A', 'B'] as $f) {
            if (!isset($resultsByForma[$f])) {
                continue;
            }

            $estresData = $this->calculateEstresData($resultsByForma[$f], $f);
            $estresData['texto_ia'] = $reportTextsByForma[$f]['estres'] ?? null;

            $html .= $this->renderView('pdf/estres/estres_page', [
                'estres' => $estresData,
                'forma' => $f,
                'baremos' => $this->baremos,
            ]);

            $html .= $this->pageBreak();
        }

        return $html;
    }

    /**
     * Renderiza el separador de dominio estrés
     */
    private function renderDomainSeparator()
    {
        return $this->renderView('pdf/estres/domain_separator', [
            'dominio' => 'Cuestionario de Evaluación del Estrés',
        ]);
    }

    /**
     * Calcula los datos del cuestionario de estrés
     */
    private function calculateEstresData($results, $forma)
    {
        // Calcular promedio del puntaje de estrés
        $puntajes = array_column($results, 'estres_puntaje');
        $puntajes = array_filter($puntajes, function($v) { return $v !== null && $v !== ''; });
        $promedio = !empty($puntajes) ? array_sum($puntajes) / count($puntajes) : 0;

        // Contar por nivel de riesgo
        $niveles = array_column($results, 'estres_nivel');
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
        $nivelPromedio = $this->getNivelFromPuntaje($promedio);

        // Identificar trabajadores en riesgo alto/muy alto
        $trabajadoresRiesgoAlto = [];
        foreach ($results as $result) {
            $nivel = $result['estres_nivel'] ?? '';
            if (in_array($nivel, ['riesgo_alto', 'riesgo_muy_alto'])) {
                $trabajadoresRiesgoAlto[] = [
                    'nombre' => $result['worker_name'] ?? '',
                    'area' => $result['department'] ?? '',
                    'cargo' => $result['position'] ?? '',
                    'puntaje' => $result['estres_puntaje'] ?? 0,
                    'nivel' => $nivel,
                ];
            }
        }

        // Categorías de síntomas de estrés
        $sintomasCategorias = [
            'fisiologicos' => 'Síntomas fisiológicos',
            'comportamiento_social' => 'Síntomas de comportamiento social',
            'intelectuales_laborales' => 'Síntomas intelectuales y laborales',
            'psicoemocionales' => 'Síntomas psicoemocionales',
        ];

        return [
            'nombre' => 'Evaluación del Estrés',
            'definicion' => 'El cuestionario para la evaluación del estrés es un instrumento diseñado para evaluar síntomas reveladores de la presencia de reacciones de estrés, distribuidos en cuatro categorías principales según el tipo de síntomas de estrés.',
            'promedio' => $promedio,
            'nivel' => $nivelPromedio,
            'total_evaluados' => $total,
            'distribucion' => $distribucion,
            'porcentajes' => $porcentajes,
            'trabajadores_riesgo_alto' => $trabajadoresRiesgoAlto,
            'accion' => $this->getRiskAction($nivelPromedio),
            'sintomas_categorias' => $sintomasCategorias,
        ];
    }

    /**
     * Obtiene el nivel de riesgo basado en el puntaje
     */
    private function getNivelFromPuntaje($puntaje)
    {
        foreach ($this->baremos as $nivel => $rango) {
            if ($puntaje >= $rango[0] && $puntaje <= $rango[1]) {
                return $nivel;
            }
        }
        return 'sin_riesgo';
    }

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

        // Para estrés, el texto está en section_level = 'questionnaire'
        $sections = $this->reportSectionModel
            ->where('report_id', $report['id'])
            ->where('questionnaire_type', 'stress')
            ->where('form_type', $forma)
            ->where('section_level', 'questionnaire')
            ->findAll();

        $texts = [];
        foreach ($sections as $section) {
            // El texto de estrés siempre va con key 'estres'
            if (!empty($section['ai_generated_text'])) {
                $texts['estres'] = $section['ai_generated_text'];
            }
        }

        return $texts;
    }

    /**
     * Preview del cuestionario de estrés en navegador
     */
    public function preview($batteryServiceId, $forma = null)
    {
        $html = $this->render($batteryServiceId, $forma);

        $title = 'Preview: Cuestionario de Estrés';
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
