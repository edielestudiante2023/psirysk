<?php

namespace App\Controllers\PdfEjecutivo;

use App\Libraries\PdfGaugeGenerator;
use App\Libraries\IntralaboralAScoring;
use App\Libraries\IntralaboralBScoring;

/**
 * Controlador para la sección de Dominios Intralaborales del PDF Ejecutivo
 *
 * Genera 9 páginas:
 * - 1 página de introducción
 * - 4 páginas de dominios Forma A
 * - 4 páginas de dominios Forma B
 *
 * Los baremos se obtienen desde IntralaboralAScoring e IntralaboralBScoring
 * para garantizar consistencia con el núcleo del sistema.
 */
class DominiosIntralaboralesController extends PdfEjecutivoBaseController
{
    /**
     * Mapeo de códigos cortos a códigos de las librerías Scoring
     * En el controlador usamos 'liderazgo', pero en las librerías es 'liderazgo_relaciones_sociales'
     */
    protected $mapeoCodigosDominios = [
        'liderazgo'   => 'liderazgo_relaciones_sociales',
        'control'     => 'control',
        'demandas'    => 'demandas',
        'recompensas' => 'recompensas',
    ];

    /**
     * Definición de los 4 dominios intralaborales
     */
    protected $dominios = [
        'liderazgo' => [
            'nombre' => 'Liderazgo y Relaciones Sociales en el Trabajo',
            'codigo' => 'liderazgo',
            'definicion' => 'Se refiere al tipo de relación social que se establece entre los superiores jerárquicos y sus colaboradores y cuyas características influyen en la forma de trabajar y en el ambiente de relaciones de un área.',
            'campo_puntaje' => 'dom_liderazgo_puntaje',
            'campo_nivel' => 'dom_liderazgo_nivel',
            'dimensiones_A' => [
                'Características del Liderazgo',
                'Relaciones Sociales en el Trabajo',
                'Retroalimentación del Desempeño',
                'Relación con los Colaboradores',
            ],
            'dimensiones_B' => [
                'Características del Liderazgo',
                'Relaciones Sociales en el Trabajo',
                'Retroalimentación del Desempeño',
            ],
        ],
        'control' => [
            'nombre' => 'Control sobre el Trabajo',
            'codigo' => 'control',
            'definicion' => 'Posibilidad que el trabajo ofrece al individuo para influir y tomar decisiones sobre los diversos aspectos que intervienen en su realización.',
            'campo_puntaje' => 'dom_control_puntaje',
            'campo_nivel' => 'dom_control_nivel',
            'dimensiones_A' => [
                'Claridad del Rol',
                'Capacitación',
                'Participación y Manejo del Cambio',
                'Oportunidades para el Uso y Desarrollo de Habilidades',
                'Control y Autonomía sobre el Trabajo',
            ],
            'dimensiones_B' => [
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
            'dimensiones_A' => [
                'Demandas Ambientales y de Esfuerzo Físico',
                'Demandas Emocionales',
                'Demandas Cuantitativas',
                'Influencia del Trabajo sobre el Entorno Extralaboral',
                'Exigencias de Responsabilidad del Cargo',
                'Demandas de Carga Mental',
                'Consistencia del Rol',
                'Demandas de la Jornada de Trabajo',
            ],
            'dimensiones_B' => [
                'Demandas Ambientales y de Esfuerzo Físico',
                'Demandas Emocionales',
                'Demandas Cuantitativas',
                'Influencia del Trabajo sobre el Entorno Extralaboral',
                'Demandas de Carga Mental',
                'Demandas de la Jornada de Trabajo',
            ],
        ],
        'recompensas' => [
            'nombre' => 'Recompensas',
            'codigo' => 'recompensas',
            'definicion' => 'Este término se refiere a la retribución que el trabajador obtiene a cambio de sus contribuciones o esfuerzos laborales.',
            'campo_puntaje' => 'dom_recompensas_puntaje',
            'campo_nivel' => 'dom_recompensas_nivel',
            'dimensiones_A' => [
                'Recompensas Derivadas de la Pertenencia a la Organización',
                'Reconocimiento y Compensación',
            ],
            'dimensiones_B' => [
                'Recompensas Derivadas de la Pertenencia a la Organización',
                'Reconocimiento y Compensación',
            ],
        ],
    ];

    /**
     * Acciones según nivel de riesgo
     */
    protected $focusActions = [
        'sin_riesgo'      => 'Mantener programas actuales de bienestar',
        'riesgo_bajo'     => 'Continuar con programas de prevención',
        'riesgo_medio'    => 'Reforzar programas de intervención',
        'riesgo_alto'     => 'Intervención prioritaria requerida',
        'riesgo_muy_alto' => 'Intervención inmediata obligatoria',
    ];

    protected $resultsFormaA = [];
    protected $resultsFormaB = [];
    protected $gaugeGenerator;

    /**
     * Obtiene el baremo de un dominio desde las librerías Scoring
     * Esta es la fuente única de verdad para los baremos (Tablas 31 y 32)
     *
     * @param string $dominioCodigo Código corto del dominio (liderazgo, control, demandas, recompensas)
     * @param string $forma 'A' o 'B'
     * @return array Baremo del dominio
     */
    protected function getBaremoDominio($dominioCodigo, $forma)
    {
        // Mapear código corto al código de las librerías
        $codigoLibreria = $this->mapeoCodigosDominios[$dominioCodigo] ?? $dominioCodigo;

        if ($forma === 'A') {
            return IntralaboralAScoring::getBaremoDominio($codigoLibreria);
        } else {
            return IntralaboralBScoring::getBaremoDominio($codigoLibreria);
        }
    }

    /**
     * Preview HTML de la sección
     */
    public function preview($batteryServiceId)
    {
        $this->initializeData($batteryServiceId);
        $this->loadResults();
        $this->gaugeGenerator = new PdfGaugeGenerator();

        $html = $this->renderAllPages();

        return $this->generatePreview($html, 'Dominios Intralaborales - Preview');
    }

    /**
     * Descargar PDF
     */
    public function download($batteryServiceId)
    {
        $this->initializeData($batteryServiceId);
        $this->loadResults();
        $this->gaugeGenerator = new PdfGaugeGenerator();

        $html = $this->renderAllPages();

        $filename = 'dominios_intralaborales_' . $batteryServiceId . '.pdf';
        return $this->generatePdf($html, $filename);
    }

    /**
     * Carga los resultados calculados
     */
    protected function loadResults()
    {
        $db = \Config\Database::connect();

        // Forma A
        $queryA = $db->query("
            SELECT
                dom_liderazgo_puntaje, dom_liderazgo_nivel,
                dom_control_puntaje, dom_control_nivel,
                dom_demandas_puntaje, dom_demandas_nivel,
                dom_recompensas_puntaje, dom_recompensas_nivel
            FROM calculated_results
            WHERE battery_service_id = ?
            AND intralaboral_form_type = 'A'
        ", [$this->batteryServiceId]);
        $this->resultsFormaA = $queryA->getResultArray();

        // Forma B
        $queryB = $db->query("
            SELECT
                dom_liderazgo_puntaje, dom_liderazgo_nivel,
                dom_control_puntaje, dom_control_nivel,
                dom_demandas_puntaje, dom_demandas_nivel,
                dom_recompensas_puntaje, dom_recompensas_nivel
            FROM calculated_results
            WHERE battery_service_id = ?
            AND intralaboral_form_type = 'B'
        ", [$this->batteryServiceId]);
        $this->resultsFormaB = $queryB->getResultArray();
    }

    /**
     * Obtiene texto IA para un dominio
     */
    protected function getAIText($dominioCodigo, $forma)
    {
        $db = \Config\Database::connect();

        $query = $db->query("
            SELECT ai_generated_text
            FROM report_sections
            WHERE report_id IN (
                SELECT id FROM reports WHERE battery_service_id = ?
            )
            AND questionnaire_type = 'intralaboral'
            AND section_level = 'domain'
            AND domain_code = ?
            AND form_type = ?
            LIMIT 1
        ", [$this->batteryServiceId, $dominioCodigo, $forma]);

        $row = $query->getRowArray();
        return $row['ai_generated_text'] ?? '';
    }

    /**
     * Renderiza todas las páginas
     */
    protected function renderAllPages()
    {
        $html = '';

        // Página 1: Introducción
        $html .= $this->renderIntroduccion();

        // Páginas 2-5: Dominios Forma A
        foreach (['liderazgo', 'control', 'demandas', 'recompensas'] as $dominio) {
            $html .= '<div class="page-break"></div>';
            $html .= $this->renderDominio($dominio, 'A');
        }

        // Páginas 6-9: Dominios Forma B
        foreach (['liderazgo', 'control', 'demandas', 'recompensas'] as $dominio) {
            $html .= '<div class="page-break"></div>';
            $html .= $this->renderDominio($dominio, 'B');
        }

        return $html;
    }

    /**
     * Renderiza página de introducción
     */
    protected function renderIntroduccion()
    {
        $totalA = count($this->resultsFormaA);
        $totalB = count($this->resultsFormaB);

        $html = '
<h1 style="font-size: 16pt; color: #006699; text-align: center; margin: 0 0 15pt 0; padding-bottom: 8pt; border-bottom: 2pt solid #006699;">
    SECCIÓN - Dominios Intralaborales
</h1>

<p style="font-size: 10pt; text-align: justify; margin: 0 0 15pt 0;">
    Esta sección presenta el análisis por dominios del Cuestionario de Factores de Riesgo Psicosocial Intralaboral.
    Los dominios agrupan las dimensiones relacionadas conceptualmente, permitiendo identificar áreas críticas de
    intervención en el ambiente de trabajo.
</p>

<h3 style="font-size: 12pt; color: #006699; margin: 15pt 0 10pt 0;">Los 4 dominios evaluados son:</h3>

<div style="background: #f5f5f5; border: 1pt solid #ddd; padding: 10pt; margin-bottom: 10pt;">
    <p style="margin: 0 0 5pt 0;"><strong style="color: #006699;">1. Liderazgo y Relaciones Sociales en el Trabajo</strong></p>
    <p style="margin: 0; font-size: 9pt; color: #666;">Características del liderazgo, relaciones sociales, retroalimentación y relación con colaboradores</p>
</div>

<div style="background: #f5f5f5; border: 1pt solid #ddd; padding: 10pt; margin-bottom: 10pt;">
    <p style="margin: 0 0 5pt 0;"><strong style="color: #006699;">2. Control sobre el Trabajo</strong></p>
    <p style="margin: 0; font-size: 9pt; color: #666;">Claridad del rol, capacitación, participación, oportunidades de desarrollo y autonomía</p>
</div>

<div style="background: #f5f5f5; border: 1pt solid #ddd; padding: 10pt; margin-bottom: 10pt;">
    <p style="margin: 0 0 5pt 0;"><strong style="color: #006699;">3. Demandas del Trabajo</strong></p>
    <p style="margin: 0; font-size: 9pt; color: #666;">Demandas ambientales, emocionales, cuantitativas, de carga mental, jornada y responsabilidad</p>
</div>

<div style="background: #f5f5f5; border: 1pt solid #ddd; padding: 10pt; margin-bottom: 15pt;">
    <p style="margin: 0 0 5pt 0;"><strong style="color: #006699;">4. Recompensas</strong></p>
    <p style="margin: 0; font-size: 9pt; color: #666;">Recompensas por pertenencia a la organización, reconocimiento y compensación</p>
</div>

<h3 style="font-size: 12pt; color: #006699; margin: 15pt 0 10pt 0;">Resumen de la Sección</h3>

<table style="width: 100%; border-collapse: collapse;">
    <tr>
        <td style="width: 33%; padding: 10pt; text-align: center; background: #e3f2fd; border: 1pt solid #90caf9;">
            <div style="font-size: 24pt; font-weight: bold; color: #1976D2;">4</div>
            <div style="font-size: 9pt; color: #1976D2;">Dominios Forma A</div>
            <div style="font-size: 8pt; color: #666;">' . $totalA . ' trabajadores</div>
        </td>
        <td style="width: 33%; padding: 10pt; text-align: center; background: #fff3e0; border: 1pt solid #ffcc80;">
            <div style="font-size: 24pt; font-weight: bold; color: #F57C00;">4</div>
            <div style="font-size: 9pt; color: #F57C00;">Dominios Forma B</div>
            <div style="font-size: 8pt; color: #666;">' . $totalB . ' trabajadores</div>
        </td>
        <td style="width: 33%; padding: 10pt; text-align: center; background: #f3e5f5; border: 1pt solid #ce93d8;">
            <div style="font-size: 24pt; font-weight: bold; color: #7B1FA2;">8</div>
            <div style="font-size: 9pt; color: #7B1FA2;">Páginas Total</div>
            <div style="font-size: 8pt; color: #666;">de análisis</div>
        </td>
    </tr>
</table>

<div style="margin-top: 20pt; padding: 10pt; background: #e8f5e9; border: 1pt solid #a5d6a7;">
    <p style="margin: 0; font-size: 9pt;"><strong style="color: #2E7D32;">Forma A:</strong> Jefes, Profesionales y Técnicos</p>
    <p style="margin: 5pt 0 0 0; font-size: 9pt;"><strong style="color: #F57C00;">Forma B:</strong> Auxiliares y Operarios</p>
</div>
';

        return $html;
    }

    /**
     * Renderiza una página de dominio
     */
    protected function renderDominio($dominioCodigo, $forma)
    {
        $dominio = $this->dominios[$dominioCodigo];
        $baremo = $this->getBaremoDominio($dominioCodigo, $forma);
        $results = ($forma === 'A') ? $this->resultsFormaA : $this->resultsFormaB;

        if (empty($results)) {
            return $this->renderDominioSinDatos($dominio, $forma);
        }

        // Calcular promedio
        $puntajes = array_column($results, $dominio['campo_puntaje']);
        $puntajes = array_filter($puntajes, function($v) { return $v !== null && $v !== ''; });

        if (empty($puntajes)) {
            return $this->renderDominioSinDatos($dominio, $forma);
        }

        $promedio = array_sum($puntajes) / count($puntajes);
        $total = count($puntajes);

        // Determinar nivel del promedio
        $nivel = $this->getNivelFromPuntaje($promedio, $baremo);
        $nivelColor = $this->getRiskColor($nivel);
        $nivelNombre = $this->getRiskName($nivel);

        // Calcular distribución
        $distribucion = $this->calculateDistribution($results, $dominio['campo_nivel']);
        $pctAlto = round((($distribucion['riesgo_alto'] + $distribucion['riesgo_muy_alto']) / $total) * 100, 1);
        $pctMedio = round(($distribucion['riesgo_medio'] / $total) * 100, 1);
        $pctBajo = round((($distribucion['sin_riesgo'] + $distribucion['riesgo_bajo']) / $total) * 100, 1);

        // Generar gauge
        $gaugeUri = $this->gaugeGenerator->generate($promedio, $baremo);

        // Obtener texto IA
        $aiText = $this->getAIText($dominioCodigo, $forma);

        // Título según forma
        $tituloForma = ($forma === 'A') ? 'Jefes, Profesionales y Técnicos' : 'Auxiliares y Operarios';
        $colorBorde = ($forma === 'A') ? '#1976D2' : '#F57C00';
        $dimensiones = ($forma === 'A') ? $dominio['dimensiones_A'] : $dominio['dimensiones_B'];
        $grupoObjetivo = ($forma === 'A') ? 'Cargos Profesionales o de Jefatura' : 'Cargos Auxiliares u Operativos';

        $html = '
<h1 style="font-size: 14pt; color: #006699; margin: 0 0 3pt 0; padding-bottom: 3pt; border-bottom: 2pt solid ' . $colorBorde . ';">
    Dominio: ' . esc($dominio['nombre']) . '
</h1>
<p style="font-size: 10pt; color: #666; text-align: center; margin: 0 0 8pt 0;">
    Forma ' . $forma . ' - ' . $tituloForma . '
</p>

<!-- Caja de definición -->
<div style="background: #f9f9f9; border: 1pt solid #ddd; padding: 6pt; margin-bottom: 8pt; font-size: 8pt;">
    <span style="font-weight: bold; color: #006699;">Definición:</span>
    ' . esc($dominio['definicion']) . '
</div>

<!-- Layout principal: 2 columnas -->
<table style="width: 100%; border: none; margin-bottom: 8pt;">
    <tr>
        <!-- Columna izquierda: Gauge -->
        <td style="width: 40%; vertical-align: top; border: none; padding: 0 8pt 0 0;">
            <div style="text-align: center;">
                <img src="' . $gaugeUri . '" style="width: 150pt; height: auto;" />

                <!-- Leyenda -->
                <div style="font-size: 5pt; color: #666; margin: 2pt 0; line-height: 1.2;">
                    SR=Sin Riesgo | RB=Riesgo Bajo | RM=Riesgo Medio<br>
                    RA=Riesgo Alto | RMA=Riesgo Muy Alto
                </div>

                <!-- Tabla de baremos -->
                <table style="width: 100%; font-size: 5.5pt; border-collapse: collapse; margin-top: 3pt;">
                    <tr>
                        <td style="background: #4CAF50; color: white; text-align: center; padding: 2pt; border: 1pt solid #ccc;">SR</td>
                        <td style="background: #8BC34A; color: white; text-align: center; padding: 2pt; border: 1pt solid #ccc;">RB</td>
                        <td style="background: #FFEB3B; color: #333; text-align: center; padding: 2pt; border: 1pt solid #ccc;">RM</td>
                        <td style="background: #FF9800; color: white; text-align: center; padding: 2pt; border: 1pt solid #ccc;">RA</td>
                        <td style="background: #F44336; color: white; text-align: center; padding: 2pt; border: 1pt solid #ccc;">RMA</td>
                    </tr>
                    <tr>
                        <td style="background: #E8F5E9; text-align: center; padding: 2pt; border: 1pt solid #ccc; font-size: 5pt;">' . $baremo['sin_riesgo'][0] . '-' . $baremo['sin_riesgo'][1] . '</td>
                        <td style="background: #F1F8E9; text-align: center; padding: 2pt; border: 1pt solid #ccc; font-size: 5pt;">' . $baremo['riesgo_bajo'][0] . '-' . $baremo['riesgo_bajo'][1] . '</td>
                        <td style="background: #FFFDE7; text-align: center; padding: 2pt; border: 1pt solid #ccc; font-size: 5pt;">' . $baremo['riesgo_medio'][0] . '-' . $baremo['riesgo_medio'][1] . '</td>
                        <td style="background: #FFF3E0; text-align: center; padding: 2pt; border: 1pt solid #ccc; font-size: 5pt;">' . $baremo['riesgo_alto'][0] . '-' . $baremo['riesgo_alto'][1] . '</td>
                        <td style="background: #FFEBEE; text-align: center; padding: 2pt; border: 1pt solid #ccc; font-size: 5pt;">' . $baremo['riesgo_muy_alto'][0] . '-100</td>
                    </tr>
                </table>
            </div>
        </td>

        <!-- Columna derecha: Interpretación -->
        <td style="width: 60%; vertical-align: top; border: none; padding: 0;">
            <p style="font-size: 8pt; text-align: justify; margin: 0 0 8pt 0;">
                El dominio <strong>' . esc($dominio['nombre']) . '</strong> para el cuestionario Tipo ' . $forma . '
                presenta un puntaje promedio de <strong>' . number_format($promedio, 1) . '</strong>,
                clasificándose como <strong style="color: ' . $nivelColor . ';">' . $nivelNombre . '</strong>.
                Se evaluaron <strong>' . $total . '</strong> trabajadores.
            </p>

            <p style="font-size: 8pt; margin: 0 0 8pt 0;">
                <strong>Acción recomendada:</strong> ' . $this->getRiskAction($nivel) . '
            </p>

            <!-- Dimensiones -->
            <div style="background: #f5f5f5; border: 1pt solid #ddd; padding: 5pt; font-size: 7.5pt;">
                <strong style="color: #006699;">Dimensiones que componen este dominio:</strong>
                <ul style="margin: 3pt 0 0 12pt; padding: 0;">';

        foreach ($dimensiones as $dim) {
            $html .= '<li style="margin-bottom: 2pt;">' . esc($dim) . '</li>';
        }

        $html .= '
                </ul>
            </div>
        </td>
    </tr>
</table>

<!-- Distribución por niveles -->
<div style="margin-bottom: 8pt;">
    <p style="font-size: 8pt; font-weight: bold; color: #006699; margin: 0 0 4pt 0;">Distribución por Niveles de Riesgo:</p>

    <!-- Barra de distribución -->
    <table style="width: 100%; height: 18pt; border-collapse: collapse; margin-bottom: 4pt;">
        <tr>';

        if ($pctAlto > 0) {
            $html .= '<td style="width: ' . $pctAlto . '%; background-color: #F44336; text-align: center; color: white; font-size: 7pt; font-weight: bold; border: none;">' . $pctAlto . '%</td>';
        }
        if ($pctMedio > 0) {
            $html .= '<td style="width: ' . $pctMedio . '%; background-color: #FFEB3B; text-align: center; color: #333; font-size: 7pt; font-weight: bold; border: none;">' . $pctMedio . '%</td>';
        }
        if ($pctBajo > 0) {
            $html .= '<td style="width: ' . $pctBajo . '%; background-color: #4CAF50; text-align: center; color: white; font-size: 7pt; font-weight: bold; border: none;">' . $pctBajo . '%</td>';
        }

        $html .= '
        </tr>
    </table>

    <table style="width: 100%; font-size: 7pt; border: none;">
        <tr>
            <td style="width: 33%; text-align: left; border: none; color: #d32f2f;">
                <strong>' . $pctAlto . '%</strong> en riesgo alto/muy alto
            </td>
            <td style="width: 33%; text-align: center; border: none; color: #f9a825;">
                <strong>' . $pctMedio . '%</strong> en riesgo medio
            </td>
            <td style="width: 33%; text-align: right; border: none; color: #388e3c;">
                <strong>' . $pctBajo . '%</strong> bajo/sin riesgo
            </td>
        </tr>
    </table>
</div>

<!-- Caja de Foco -->
<div style="border: 1pt solid #006699; background: #e8f4fc; padding: 6pt; margin-bottom: 8pt; font-size: 7.5pt;">
    <p style="margin: 0 0 3pt 0;"><strong style="color: #006699;">Grupo Objetivo:</strong> ' . $grupoObjetivo . '</p>
    <p style="margin: 0 0 3pt 0;"><strong style="color: #006699;">Acción:</strong> ' . ($this->focusActions[$nivel] ?? 'Evaluar según nivel') . '</p>
    <p style="margin: 0; font-style: italic; color: #666;">El nivel de riesgo del dominio requiere las acciones indicadas según la Resolución 2404/2019.</p>
</div>';

        // Texto IA si existe
        if (!empty($aiText)) {
            $html .= '
<div style="border-left: 2pt solid #2196F3; background: #e3f2fd; padding: 6pt; font-size: 7.5pt;">
    <p style="margin: 0 0 3pt 0; font-weight: bold; color: #1976D2;">Análisis del Dominio:</p>
    <p style="margin: 0; text-align: justify;">' . nl2br(esc($aiText)) . '</p>
</div>';
        }

        return $html;
    }

    /**
     * Renderiza página cuando no hay datos para el dominio
     */
    protected function renderDominioSinDatos($dominio, $forma)
    {
        $tituloForma = ($forma === 'A') ? 'Jefes, Profesionales y Técnicos' : 'Auxiliares y Operarios';
        $colorBorde = ($forma === 'A') ? '#1976D2' : '#F57C00';

        return '
<h1 style="font-size: 14pt; color: #006699; margin: 0 0 5pt 0; padding-bottom: 5pt; border-bottom: 2pt solid ' . $colorBorde . ';">
    Dominio: ' . esc($dominio['nombre']) . '
</h1>
<p style="font-size: 10pt; color: #666; text-align: center; margin: 0 0 15pt 0;">
    Forma ' . $forma . ' - ' . $tituloForma . '
</p>

<div style="background: #fff3e0; border: 1pt solid #ffcc80; padding: 20pt; text-align: center; margin-top: 50pt;">
    <p style="font-size: 12pt; color: #f57c00; margin: 0;">
        <strong>No hay datos disponibles</strong>
    </p>
    <p style="font-size: 10pt; color: #666; margin: 10pt 0 0 0;">
        No se encontraron resultados calculados para este dominio en Forma ' . $forma . '.
    </p>
</div>';
    }
}
