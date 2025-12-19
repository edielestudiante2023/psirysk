<?php

namespace App\Controllers\PdfEjecutivo;

use App\Libraries\PdfGaugeGenerator;
use App\Libraries\IntralaboralAScoring;
use App\Libraries\IntralaboralBScoring;
use App\Libraries\EstresScoring;

/**
 * Controlador para la sección de Totales Intralaborales del PDF Ejecutivo
 * Genera 5 páginas: Intro, Forma A, Forma B, Resumen General, Tabla 34
 */
class TotalesIntralaboralesController extends PdfEjecutivoBaseController
{
    protected $calculatedResults = [];
    protected $gaugeGenerator;

    /**
     * Acciones por nivel de riesgo
     */
    protected $acciones = [
        'sin_riesgo'      => 'mantener',
        'riesgo_bajo'     => 'mantener',
        'riesgo_medio'    => 'observar y mantener',
        'riesgo_alto'     => 'intervenir en marco de vigilancia epidemiológica',
        'riesgo_muy_alto' => 'intervenir inmediatamente en marco de vigilancia epidemiológica',
    ];

    // =========================================================================
    // BAREMOS - Desde Single Source of Truth (Librerías de Scoring)
    // =========================================================================

    /**
     * Obtiene baremo Intralaboral Total según la forma
     */
    protected function getBaremoIntralaboral(string $forma): array
    {
        return ($forma === 'A')
            ? IntralaboralAScoring::getBaremoTotal()
            : IntralaboralBScoring::getBaremoTotal();
    }

    /**
     * Obtiene baremo Tabla 34 (Total General Psicosocial) según la forma
     */
    protected function getBaremoTabla34(string $forma): array
    {
        return EstresScoring::getBaremoGeneral($forma);
    }

    /**
     * Preview HTML
     */
    public function preview($batteryServiceId)
    {
        $this->initializeData($batteryServiceId);
        $this->loadCalculatedResults();
        $this->gaugeGenerator = new PdfGaugeGenerator();

        $html = $this->renderContent();
        return $this->generatePreview($html, 'Totales Intralaborales - Preview');
    }

    /**
     * Descargar PDF
     */
    public function download($batteryServiceId)
    {
        $this->initializeData($batteryServiceId);
        $this->loadCalculatedResults();
        $this->gaugeGenerator = new PdfGaugeGenerator();

        $html = $this->renderContent();
        $filename = 'totales_intralaborales_' . $batteryServiceId . '.pdf';
        return $this->generatePdf($html, $filename);
    }

    /**
     * Carga resultados calculados desde la BD
     */
    protected function loadCalculatedResults()
    {
        $db = \Config\Database::connect();

        $query = $db->query("
            SELECT
                intralaboral_form_type,
                intralaboral_total_puntaje,
                intralaboral_total_nivel,
                extralaboral_total_puntaje,
                extralaboral_total_nivel
            FROM calculated_results
            WHERE battery_service_id = ?
        ", [$this->batteryServiceId]);

        $this->calculatedResults = $query->getResultArray();
    }

    /**
     * Obtiene texto IA para una sección
     */
    protected function getAiText($formType, $sectionLevel = 'total')
    {
        $db = \Config\Database::connect();

        $query = $db->query("
            SELECT ai_generated_text
            FROM report_sections rs
            JOIN reports r ON rs.report_id = r.id
            WHERE r.battery_service_id = ?
            AND rs.questionnaire_type = 'intralaboral'
            AND rs.section_level = ?
            AND rs.form_type = ?
        ", [$this->batteryServiceId, $sectionLevel, $formType]);

        $row = $query->getRowArray();
        return $row['ai_generated_text'] ?? '';
    }

    /**
     * Calcula estadísticas por forma
     */
    protected function getStatsByForma($forma)
    {
        $results = array_filter($this->calculatedResults, function ($r) use ($forma) {
            return $r['intralaboral_form_type'] === $forma;
        });

        $total = count($results);
        if ($total === 0) {
            return null;
        }

        $sumaPuntaje = 0;
        $distribucion = [
            'sin_riesgo'      => 0,
            'riesgo_bajo'     => 0,
            'riesgo_medio'    => 0,
            'riesgo_alto'     => 0,
            'riesgo_muy_alto' => 0,
        ];

        foreach ($results as $r) {
            $sumaPuntaje += floatval($r['intralaboral_total_puntaje']);
            $nivel = $r['intralaboral_total_nivel'] ?? 'sin_riesgo';
            if (isset($distribucion[$nivel])) {
                $distribucion[$nivel]++;
            }
        }

        $promedio = $sumaPuntaje / $total;
        $nivel = $this->getNivelFromPuntaje($promedio, $this->getBaremoIntralaboral($forma));

        return [
            'total'        => $total,
            'promedio'     => $promedio,
            'nivel'        => $nivel,
            'distribucion' => $distribucion,
        ];
    }

    /**
     * Calcula estadísticas generales (ambas formas)
     */
    protected function getStatsGeneral()
    {
        $total = count($this->calculatedResults);
        if ($total === 0) {
            return null;
        }

        $sumaPuntaje = 0;
        $distribucion = [
            'sin_riesgo'      => 0,
            'riesgo_bajo'     => 0,
            'riesgo_medio'    => 0,
            'riesgo_alto'     => 0,
            'riesgo_muy_alto' => 0,
        ];

        foreach ($this->calculatedResults as $r) {
            $sumaPuntaje += floatval($r['intralaboral_total_puntaje']);
            $nivel = $r['intralaboral_total_nivel'] ?? 'sin_riesgo';
            if (isset($distribucion[$nivel])) {
                $distribucion[$nivel]++;
            }
        }

        $promedio = $sumaPuntaje / $total;

        // Usar baremos forma A como referencia general
        $nivel = $this->getNivelFromPuntaje($promedio, $this->getBaremoIntralaboral('A'));

        return [
            'total'        => $total,
            'promedio'     => $promedio,
            'nivel'        => $nivel,
            'distribucion' => $distribucion,
        ];
    }

    /**
     * Factores de transformación según Tabla 28 - Resolución 2404/2019
     * Factor Total = Factor Intralaboral + Factor Extralaboral
     */
    protected const FACTORES_TABLA28 = [
        'A' => [
            'intralaboral' => 492,
            'extralaboral' => 124,
            'total'        => 616,  // 492 + 124
        ],
        'B' => [
            'intralaboral' => 388,
            'extralaboral' => 124,
            'total'        => 512,  // 388 + 124
        ],
    ];

    /**
     * Calcula estadísticas para Tabla 34 (Intralaboral + Extralaboral)
     * FÓRMULA OFICIAL TABLA 28: Puntaje = (Bruto_Intra + Bruto_Extra) / Factor × 100
     *
     * JUSTIFICACIÓN METODOLÓGICA:
     * Los puntajes brutos se obtienen aplicando la fórmula inversa de transformación:
     *   Bruto = (Transformado × Factor) / 100
     *
     * Esto es matemáticamente equivalente a sumar las calificaciones originales de cada ítem,
     * dado que la fórmula de transformación original (Tabla 24, Res. 2404/2019) es:
     *   Transformado = (Bruto / Factor) × 100
     *
     * Por tanto: Bruto = (Transformado × Factor) / 100 = Σ(calificaciones ítems)
     *
     * Esta metodología garantiza:
     * 1. Resultados idénticos a recalcular desde respuestas individuales
     * 2. Trazabilidad completa del cálculo
     * 3. Eficiencia computacional (evita re-leer todas las respuestas)
     *
     * @param string $forma 'A' o 'B'
     * @return array|null Estadísticas con operaciones aritméticas detalladas
     */
    protected function getStatsTabla34($forma)
    {
        $results = array_filter($this->calculatedResults, function ($r) use ($forma) {
            return $r['intralaboral_form_type'] === $forma;
        });

        $total = count($results);
        if ($total === 0) {
            return null;
        }

        // Factores de transformación según Tabla 28
        $factores = self::FACTORES_TABLA28[$forma];
        $factorIntra = $factores['intralaboral'];
        $factorExtra = $factores['extralaboral'];
        $factorTotal = $factores['total'];

        // Acumuladores para promedios de puntajes TRANSFORMADOS (para mostrar)
        $sumaIntraTransformado = 0;
        $sumaExtraTransformado = 0;

        // Acumuladores para puntajes BRUTOS (para cálculo correcto)
        $sumaBrutoIntra = 0;
        $sumaBrutoExtra = 0;

        foreach ($results as $r) {
            // Puntajes transformados (almacenados en BD)
            $intraTransformado = floatval($r['intralaboral_total_puntaje']);
            $extraTransformado = floatval($r['extralaboral_total_puntaje']);

            // Convertir a BRUTOS: Bruto = (Transformado × Factor) / 100
            $intraBruto = ($intraTransformado * $factorIntra) / 100;
            $extraBruto = ($extraTransformado * $factorExtra) / 100;

            // Acumular
            $sumaIntraTransformado += $intraTransformado;
            $sumaExtraTransformado += $extraTransformado;
            $sumaBrutoIntra += $intraBruto;
            $sumaBrutoExtra += $extraBruto;
        }

        // Promedios de puntajes transformados (para mostrar en PDF)
        $promedioIntraTransformado = $sumaIntraTransformado / $total;
        $promedioExtraTransformado = $sumaExtraTransformado / $total;

        // Promedios de puntajes brutos
        $promedioBrutoIntra = $sumaBrutoIntra / $total;
        $promedioBrutoExtra = $sumaBrutoExtra / $total;
        $promedioBrutoTotal = $promedioBrutoIntra + $promedioBrutoExtra;

        // CÁLCULO FINAL según Tabla 28: (Bruto_Intra + Bruto_Extra) / Factor × 100
        $promedioTotalTransformado = ($promedioBrutoTotal / $factorTotal) * 100;

        // Determinar nivel de riesgo
        $nivelTotal = $this->getNivelFromPuntaje($promedioTotalTransformado, $this->getBaremoTabla34($forma));

        return [
            'total'                     => $total,
            'forma'                     => $forma,

            // Puntajes transformados (para mostrar)
            'promedio_intra'            => $promedioIntraTransformado,
            'promedio_extra'            => $promedioExtraTransformado,
            'promedio_total'            => $promedioTotalTransformado,
            'nivel_total'               => $nivelTotal,

            // Puntajes brutos (para auditoría y transparencia)
            'promedio_bruto_intra'      => round($promedioBrutoIntra, 2),
            'promedio_bruto_extra'      => round($promedioBrutoExtra, 2),
            'promedio_bruto_total'      => round($promedioBrutoTotal, 2),

            // Factores usados (para mostrar en PDF)
            'factor_intra'              => $factorIntra,
            'factor_extra'              => $factorExtra,
            'factor_total'              => $factorTotal,
        ];
    }

    /**
     * Renderiza el HTML de la sección (para el Orquestador)
     * Este método es público para ser usado por PdfEjecutivoOrchestrator
     */
    public function render($batteryServiceId)
    {
        if (empty($this->calculatedResults)) {
            $this->initializeData($batteryServiceId);
            $this->loadCalculatedResults();
            $this->gaugeGenerator = new PdfGaugeGenerator();
        }

        return $this->renderContent();
    }

    /**
     * Renderiza todo el contenido
     */
    protected function renderContent()
    {
        $html = '';

        // Página 1: Intro
        $html .= $this->renderPaginaIntro();

        // Página 2: Total Forma A
        $statsA = $this->getStatsByForma('A');
        if ($statsA) {
            $html .= '<div class="page-break"></div>';
            $html .= $this->renderPaginaForma('A', $statsA);
        }

        // Página 3: Total Forma B
        $statsB = $this->getStatsByForma('B');
        if ($statsB) {
            $html .= '<div class="page-break"></div>';
            $html .= $this->renderPaginaForma('B', $statsB);
        }

        // Página 4: Resumen General
        $statsGeneral = $this->getStatsGeneral();
        if ($statsGeneral) {
            $html .= '<div class="page-break"></div>';
            $html .= $this->renderPaginaResumenGeneral($statsA, $statsB, $statsGeneral);
        }

        // Página 5: Tabla 34 - Total General Psicosocial
        $html .= '<div class="page-break"></div>';
        $html .= $this->renderPaginaTabla34($statsA, $statsB);

        return $html;
    }

    // =========================================================================
    // PÁGINA 1: INTRO
    // =========================================================================
    protected function renderPaginaIntro()
    {
        $html = '
<div style="text-align: center; padding-top: 80pt;">
    <div style="background: linear-gradient(135deg, #0077B6, #005A8C); background-color: #0077B6; color: white; padding: 30pt 20pt; margin: 0 auto; max-width: 350pt;">
        <div style="font-size: 12pt; font-weight: bold; margin-bottom: 8pt;">SECCIÓN</div>
        <div style="font-size: 18pt; font-weight: bold;">Resultados Totales Intralaborales</div>
    </div>
</div>

<div style="margin-top: 30pt; padding: 15pt; background-color: #f8f9fa; border-left: 4pt solid #0077B6;">
    <p style="font-size: 10pt; text-align: justify; margin: 0;">
        Esta sección presenta los puntajes totales del Cuestionario de Factores de Riesgo Psicosocial Intralaboral, consolidando los resultados de todas las dimensiones y dominios evaluados. Los resultados se presentan diferenciados por tipo de cuestionario aplicado.
    </p>
</div>

<div style="margin-top: 25pt;">
    <h3 style="font-size: 12pt; color: #0077B6; margin-bottom: 10pt;">En esta sección encontrará:</h3>
    <table style="width: 100%; border: none;">
        <tr>
            <td style="width: 30pt; vertical-align: top; border: none; padding: 5pt;">
                <div style="background-color: #0077B6; color: white; width: 22pt; height: 22pt; text-align: center; font-weight: bold; font-size: 11pt; line-height: 22pt;">1</div>
            </td>
            <td style="border: none; padding: 5pt; font-size: 10pt;">
                <strong>Total Forma A</strong> - Resultados para Jefes, Profesionales y Técnicos
            </td>
        </tr>
        <tr>
            <td style="width: 30pt; vertical-align: top; border: none; padding: 5pt;">
                <div style="background-color: #0077B6; color: white; width: 22pt; height: 22pt; text-align: center; font-weight: bold; font-size: 11pt; line-height: 22pt;">2</div>
            </td>
            <td style="border: none; padding: 5pt; font-size: 10pt;">
                <strong>Total Forma B</strong> - Resultados para Auxiliares y Operarios
            </td>
        </tr>
        <tr>
            <td style="width: 30pt; vertical-align: top; border: none; padding: 5pt;">
                <div style="background-color: #0077B6; color: white; width: 22pt; height: 22pt; text-align: center; font-weight: bold; font-size: 11pt; line-height: 22pt;">3</div>
            </td>
            <td style="border: none; padding: 5pt; font-size: 10pt;">
                <strong>Resumen General Intralaboral</strong> - Consolidado de ambas formas
            </td>
        </tr>
        <tr>
            <td style="width: 30pt; vertical-align: top; border: none; padding: 5pt;">
                <div style="background-color: #0077B6; color: white; width: 22pt; height: 22pt; text-align: center; font-weight: bold; font-size: 11pt; line-height: 22pt;">4</div>
            </td>
            <td style="border: none; padding: 5pt; font-size: 10pt;">
                <strong>Puntaje Total General Psicosocial</strong> - Intralaboral + Extralaboral (Tabla 34)
            </td>
        </tr>
    </table>
</div>

<div style="margin-top: 40pt; padding: 12pt; background-color: #fff3e0; border-left: 4pt solid #FF9800;">
    <p style="font-size: 9pt; margin: 0; color: #e65100;">
        <strong>Nota metodológica:</strong> Los niveles de riesgo se determinan según los baremos establecidos en la Resolución 2404 de 2019 del Ministerio del Trabajo de Colombia.
    </p>
</div>
';
        return $html;
    }

    // =========================================================================
    // PÁGINA 2-3: TOTAL FORMA A/B
    // =========================================================================
    protected function renderPaginaForma($forma, $stats)
    {
        $titulo = $forma === 'A' ? 'Jefes, Profesionales y Técnicos' : 'Auxiliares y Operarios';
        $colorBorde = $forma === 'A' ? '#0077B6' : '#FF9800';

        $promedio = number_format($stats['promedio'], 1);
        $nivel = $stats['nivel'];
        $nivelNombre = $this->getRiskName($nivel);
        $nivelColor = $this->getRiskColor($nivel);
        $accion = $this->acciones[$nivel] ?? 'mantener';
        $total = $stats['total'];

        // Obtener baremo desde Single Source of Truth
        $baremo = $this->getBaremoIntralaboral($forma);

        // Generar gauge SVG
        $gaugeUri = $this->gaugeGenerator->generate($stats['promedio'], $baremo);

        // Texto interpretación
        $cargoTipo = $forma === 'A' ? 'cargos profesionales o de jefatura' : 'cargos auxiliares u operativos';

        $html = '
<h1 style="font-size: 14pt; color: #006699; margin: 0 0 5pt 0; padding-bottom: 5pt; border-bottom: 2pt solid ' . $colorBorde . ';">
    Total Factores de Riesgo Psicosocial Intralaboral
</h1>
<p style="font-size: 11pt; color: #666; text-align: center; margin: 0 0 15pt 0;">
    Forma ' . $forma . ' - ' . $titulo . '
</p>

<!-- Gauge centrado con leyenda y tabla de baremos -->
<div style="text-align: center; margin: 10pt 0;">
    <img src="' . $gaugeUri . '" style="width: 180pt; height: auto;" />

    <!-- ELEMENTO 7: Leyenda de convenciones -->
    <div style="font-size: 6pt; color: #666; margin: 3pt 0; line-height: 1.3;">
        SR=Sin Riesgo | RB=Riesgo Bajo | RM=Riesgo Medio<br>
        RA=Riesgo Alto | RMA=Riesgo Muy Alto
    </div>

    <!-- ELEMENTO 8: Tabla de baremos -->
    <table style="width: 100%; font-size: 7pt; border-collapse: collapse; margin-top: 5pt;">
        <tr>
            <td style="background: #4CAF50; color: white; text-align: center; padding: 3pt; border: 1pt solid #ccc;">Sin Riesgo</td>
            <td style="background: #8BC34A; color: white; text-align: center; padding: 3pt; border: 1pt solid #ccc;">Bajo</td>
            <td style="background: #FFEB3B; color: #333; text-align: center; padding: 3pt; border: 1pt solid #ccc;">Medio</td>
            <td style="background: #FF9800; color: white; text-align: center; padding: 3pt; border: 1pt solid #ccc;">Alto</td>
            <td style="background: #F44336; color: white; text-align: center; padding: 3pt; border: 1pt solid #ccc;">Muy Alto</td>
        </tr>
        <tr>
            <td style="background: #E8F5E9; text-align: center; padding: 3pt; border: 1pt solid #ccc;">' . $baremo['sin_riesgo'][0] . ' - ' . $baremo['sin_riesgo'][1] . '</td>
            <td style="background: #F1F8E9; text-align: center; padding: 3pt; border: 1pt solid #ccc;">' . $baremo['riesgo_bajo'][0] . ' - ' . $baremo['riesgo_bajo'][1] . '</td>
            <td style="background: #FFFDE7; text-align: center; padding: 3pt; border: 1pt solid #ccc;">' . $baremo['riesgo_medio'][0] . ' - ' . $baremo['riesgo_medio'][1] . '</td>
            <td style="background: #FFF3E0; text-align: center; padding: 3pt; border: 1pt solid #ccc;">' . $baremo['riesgo_alto'][0] . ' - ' . $baremo['riesgo_alto'][1] . '</td>
            <td style="background: #FFEBEE; text-align: center; padding: 3pt; border: 1pt solid #ccc;">' . $baremo['riesgo_muy_alto'][0] . ' - ' . $baremo['riesgo_muy_alto'][1] . '</td>
        </tr>
    </table>
</div>

<!-- Badge de nivel -->
<div style="text-align: center; margin: 10pt 0;">
    <span style="display: inline-block; background-color: ' . $nivelColor . '; color: ' . ($nivel === 'riesgo_medio' ? '#333' : '#fff') . '; padding: 6pt 20pt; font-weight: bold; font-size: 11pt;">
        ' . strtoupper($nivelNombre) . '
    </span>
</div>

<!-- Caja de interpretación -->
<div style="background-color: #e8f5e9; border: 1pt solid #4CAF50; padding: 12pt; margin: 15pt 0; font-size: 9pt; text-align: justify;">
    El análisis del Cuestionario de Factores de Riesgo Psicosocial Intralaboral Forma ' . $forma . ', aplicado a <strong>' . $total . '</strong> trabajadores (' . $titulo . '), arroja un puntaje promedio de <strong>' . $promedio . '</strong>, clasificándose como <strong>' . $nivelNombre . '</strong>. Esto indica que se debe <strong>' . $accion . '</strong> las intervenciones para ' . $cargoTipo . '.
</div>

<!-- Tabla de distribución -->
<h3 style="font-size: 11pt; color: #006699; margin: 15pt 0 8pt 0;">Distribución por Nivel de Riesgo</h3>
<table style="width: 100%; border-collapse: collapse; font-size: 9pt;">
    <thead>
        <tr>
            <th style="background-color: #006699; color: white; padding: 6pt; border: 1pt solid #333; text-align: left; width: 50%;">Nivel de Riesgo</th>
            <th style="background-color: #006699; color: white; padding: 6pt; border: 1pt solid #333; text-align: center; width: 25%;">N</th>
            <th style="background-color: #006699; color: white; padding: 6pt; border: 1pt solid #333; text-align: center; width: 25%;">%</th>
        </tr>
    </thead>
    <tbody>
';

        $niveles = ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'];
        foreach ($niveles as $niv) {
            $count = $stats['distribucion'][$niv];
            $pct = $total > 0 ? round(($count / $total) * 100, 1) : 0;
            $color = $this->getRiskColor($niv);
            $textColor = $niv === 'riesgo_medio' ? '#333' : '#fff';

            $html .= '
        <tr>
            <td style="background-color: ' . $color . '; color: ' . $textColor . '; padding: 5pt; border: 1pt solid #333; font-weight: bold;">' . $this->getRiskName($niv) . '</td>
            <td style="padding: 5pt; border: 1pt solid #333; text-align: center;">' . $count . '</td>
            <td style="padding: 5pt; border: 1pt solid #333; text-align: center;">' . $pct . '%</td>
        </tr>
';
        }

        $html .= '
        <tr style="background-color: #f5f5f5; font-weight: bold;">
            <td style="padding: 5pt; border: 1pt solid #333;">TOTAL</td>
            <td style="padding: 5pt; border: 1pt solid #333; text-align: center;">' . $total . '</td>
            <td style="padding: 5pt; border: 1pt solid #333; text-align: center;">100%</td>
        </tr>
    </tbody>
</table>
';

        // Texto IA si existe
        $aiText = $this->getAiText($forma);
        if (!empty($aiText)) {
            $html .= '
<div class="ai-text-box" style="margin-top: 15pt;">
    <div class="ai-text-title">Análisis del Especialista SST:</div>
    ' . nl2br(esc($aiText)) . '
</div>
';
        }

        return $html;
    }

    // =========================================================================
    // PÁGINA 4: RESUMEN GENERAL INTRALABORAL
    // =========================================================================
    protected function renderPaginaResumenGeneral($statsA, $statsB, $statsGeneral)
    {
        $totalA = $statsA ? $statsA['total'] : 0;
        $totalB = $statsB ? $statsB['total'] : 0;
        $totalGeneral = $statsGeneral['total'];

        $promedioA = $statsA ? number_format($statsA['promedio'], 1) : 'N/A';
        $promedioB = $statsB ? number_format($statsB['promedio'], 1) : 'N/A';

        $nivelA = $statsA ? $statsA['nivel'] : 'sin_riesgo';
        $nivelB = $statsB ? $statsB['nivel'] : 'sin_riesgo';
        $nivelNombreA = $this->getRiskName($nivelA);
        $nivelNombreB = $this->getRiskName($nivelB);
        $colorA = $this->getRiskColor($nivelA);
        $colorB = $this->getRiskColor($nivelB);

        $html = '
<h1 style="font-size: 14pt; color: #006699; margin: 0 0 5pt 0; padding-bottom: 5pt; border-bottom: 2pt solid #006699;">
    Resumen General - Factores de Riesgo Psicosocial Intralaboral
</h1>
<p style="font-size: 11pt; color: #666; text-align: center; margin: 0 0 15pt 0;">
    Resultados por Forma (Tabla 33 - Resolución 2764/2022)
</p>

<!-- 2 cajas comparativas - Solo Forma A y Forma B -->
<table style="width: 100%; border-collapse: separate; border-spacing: 8pt;">
    <tr>
        <!-- FORMA A -->
        <td style="width: 50%; background-color: #1a1a1a; color: white; text-align: center; padding: 20pt 15pt; vertical-align: middle; border: none;">
            <div style="font-size: 10pt; font-weight: bold; margin-bottom: 8pt;">FORMA A</div>
            <div style="font-size: 8pt; margin-bottom: 10pt; color: #ccc;">Jefes, Profesionales y Técnicos</div>
            <div style="font-size: 28pt; font-weight: bold;">' . $promedioA . '</div>
            <div style="display: inline-block; background-color: ' . $colorA . '; color: ' . ($nivelA === 'riesgo_medio' ? '#333' : '#fff') . '; padding: 4pt 12pt; margin-top: 8pt; font-size: 9pt; font-weight: bold;">
                ' . strtoupper($nivelNombreA) . '
            </div>
            <div style="font-size: 9pt; margin-top: 10pt; color: #ccc;">n = ' . $totalA . ' trabajadores</div>
        </td>
        <!-- FORMA B -->
        <td style="width: 50%; background-color: #1a1a1a; color: white; text-align: center; padding: 20pt 15pt; vertical-align: middle; border: none;">
            <div style="font-size: 10pt; font-weight: bold; margin-bottom: 8pt;">FORMA B</div>
            <div style="font-size: 8pt; margin-bottom: 10pt; color: #ccc;">Auxiliares y Operarios</div>
            <div style="font-size: 28pt; font-weight: bold;">' . $promedioB . '</div>
            <div style="display: inline-block; background-color: ' . $colorB . '; color: ' . ($nivelB === 'riesgo_medio' ? '#333' : '#fff') . '; padding: 4pt 12pt; margin-top: 8pt; font-size: 9pt; font-weight: bold;">
                ' . strtoupper($nivelNombreB) . '
            </div>
            <div style="font-size: 9pt; margin-top: 10pt; color: #ccc;">n = ' . $totalB . ' trabajadores</div>
        </td>
    </tr>
</table>

<!-- Nota metodológica -->
<div style="background-color: #fff3cd; border: 1pt solid #ffc107; padding: 10pt; margin: 15pt 0; font-size: 8pt; text-align: justify;">
    <strong>Nota metodológica:</strong> Según la Resolución 2764/2022, los resultados de Forma A y Forma B no deben promediarse entre sí, ya que corresponden a poblaciones con baremos diferentes (Tabla 33). Cada forma debe interpretarse de manera independiente.
</div>

<!-- Caja de interpretación -->
<div style="background-color: #e8f5e9; border: 1pt solid #4CAF50; padding: 12pt; margin: 10pt 0; font-size: 9pt; text-align: justify;">
    Se evaluaron <strong>' . $totalGeneral . '</strong> trabajadores en total: <strong>' . $totalA . '</strong> mediante Forma A (Jefes/Profesionales) con nivel <strong>' . $nivelNombreA . '</strong>, y <strong>' . $totalB . '</strong> mediante Forma B (Auxiliares/Operarios) con nivel <strong>' . $nivelNombreB . '</strong>.
</div>

<!-- Tabla distribución consolidada -->
<h3 style="font-size: 11pt; color: #006699; margin: 15pt 0 8pt 0;">Distribución Consolidada por Nivel de Riesgo</h3>
<table style="width: 100%; border-collapse: collapse; font-size: 9pt;">
    <thead>
        <tr>
            <th style="background-color: #006699; color: white; padding: 6pt; border: 1pt solid #333; text-align: left;">Nivel de Riesgo</th>
            <th style="background-color: #0077B6; color: white; padding: 6pt; border: 1pt solid #333; text-align: center;">Forma A</th>
            <th style="background-color: #FF9800; color: white; padding: 6pt; border: 1pt solid #333; text-align: center;">Forma B</th>
            <th style="background-color: #333; color: white; padding: 6pt; border: 1pt solid #333; text-align: center;">Total</th>
            <th style="background-color: #333; color: white; padding: 6pt; border: 1pt solid #333; text-align: center;">%</th>
        </tr>
    </thead>
    <tbody>
';

        $niveles = ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'];
        foreach ($niveles as $niv) {
            $countA = $statsA ? $statsA['distribucion'][$niv] : 0;
            $countB = $statsB ? $statsB['distribucion'][$niv] : 0;
            $countTotal = $statsGeneral['distribucion'][$niv];
            $pct = $totalGeneral > 0 ? round(($countTotal / $totalGeneral) * 100, 1) : 0;
            $color = $this->getRiskColor($niv);
            $textColor = $niv === 'riesgo_medio' ? '#333' : '#fff';

            $html .= '
        <tr>
            <td style="background-color: ' . $color . '; color: ' . $textColor . '; padding: 5pt; border: 1pt solid #333; font-weight: bold;">' . $this->getRiskName($niv) . '</td>
            <td style="padding: 5pt; border: 1pt solid #333; text-align: center;">' . $countA . '</td>
            <td style="padding: 5pt; border: 1pt solid #333; text-align: center;">' . $countB . '</td>
            <td style="padding: 5pt; border: 1pt solid #333; text-align: center; font-weight: bold;">' . $countTotal . '</td>
            <td style="padding: 5pt; border: 1pt solid #333; text-align: center;">' . $pct . '%</td>
        </tr>
';
        }

        $html .= '
        <tr style="background-color: #f5f5f5; font-weight: bold;">
            <td style="padding: 5pt; border: 1pt solid #333;">TOTAL</td>
            <td style="padding: 5pt; border: 1pt solid #333; text-align: center;">' . $totalA . '</td>
            <td style="padding: 5pt; border: 1pt solid #333; text-align: center;">' . $totalB . '</td>
            <td style="padding: 5pt; border: 1pt solid #333; text-align: center;">' . $totalGeneral . '</td>
            <td style="padding: 5pt; border: 1pt solid #333; text-align: center;">100%</td>
        </tr>
    </tbody>
</table>
';

        // Textos AI por forma (no existe baremo para 'conjunto')
        $aiTextA = $this->getAiText('A');
        $aiTextB = $this->getAiText('B');

        if (!empty($aiTextA) || !empty($aiTextB)) {
            $html .= '<div style="margin-top: 15pt;">';

            if (!empty($aiTextA)) {
                $html .= '
<div class="ai-text-box" style="margin-bottom: 10pt;">
    <div class="ai-text-title">Análisis del Especialista SST - Forma A:</div>
    ' . nl2br(esc($aiTextA)) . '
</div>';
            }

            if (!empty($aiTextB)) {
                $html .= '
<div class="ai-text-box">
    <div class="ai-text-title">Análisis del Especialista SST - Forma B:</div>
    ' . nl2br(esc($aiTextB)) . '
</div>';
            }

            $html .= '</div>';
        }

        return $html;
    }

    // =========================================================================
    // PÁGINA 5: TABLA 34 - TOTAL GENERAL PSICOSOCIAL
    // =========================================================================
    protected function renderPaginaTabla34($statsA, $statsB)
    {
        $stats34A = $this->getStatsTabla34('A');
        $stats34B = $this->getStatsTabla34('B');

        // Obtener niveles y colores por forma
        $nivelA = $stats34A ? $stats34A['nivel_total'] : 'sin_riesgo';
        $nivelB = $stats34B ? $stats34B['nivel_total'] : 'sin_riesgo';
        $nivelNombreA = $this->getRiskName($nivelA);
        $nivelNombreB = $this->getRiskName($nivelB);
        $colorA = $this->getRiskColor($nivelA);
        $colorB = $this->getRiskColor($nivelB);

        $html = '
<h1 style="font-size: 13pt; color: #006699; margin: 0 0 5pt 0; padding-bottom: 5pt; border-bottom: 2pt solid #6a1b9a;">
    Puntaje Total General de Factores de Riesgo Psicosocial
</h1>
<p style="font-size: 10pt; color: #666; text-align: center; margin: 0 0 10pt 0;">
    Intralaboral + Extralaboral (Tabla 28 y 34 - Resolución 2404/2019)
</p>

<!-- Fórmula Oficial Tabla 28 -->
<div style="background-color: #f3e5f5; border: 1pt solid #6a1b9a; padding: 10pt; margin: 10pt 0; text-align: center;">
    <div style="font-size: 10pt; color: #6a1b9a; font-weight: bold; margin-bottom: 5pt;">
        Fórmula Oficial - Tabla 28 Resolución 2404/2019
    </div>
    <div style="font-size: 11pt; color: #4a148c; font-family: monospace;">
        Puntaje Total = (Puntaje Bruto Intra + Puntaje Bruto Extra) ÷ Factor × 100
    </div>
    <div style="font-size: 8pt; color: #666; margin-top: 5pt;">
        Factor Forma A = 616 (492 + 124) | Factor Forma B = 512 (388 + 124)
    </div>
</div>
';

        // Sección Forma A con operaciones aritméticas visibles
        if ($stats34A) {
            $html .= $this->renderSeccionFormaTabla34($stats34A, 'A', $colorA, $nivelNombreA);
        }

        // Sección Forma B con operaciones aritméticas visibles
        if ($stats34B) {
            $html .= $this->renderSeccionFormaTabla34($stats34B, 'B', $colorB, $nivelNombreB);
        }

        $html .= '
<!-- Nota metodológica -->
<div style="background-color: #e8f5e9; border: 1pt solid #4CAF50; padding: 8pt; margin: 10pt 0; font-size: 8pt; text-align: justify;">
    <strong>Nota metodológica - Obtención de puntajes brutos:</strong> Los puntajes brutos se calculan aplicando la fórmula inversa de transformación: <em>Bruto = (Transformado × Factor) ÷ 100</em>. Este procedimiento es matemáticamente equivalente a sumar las calificaciones originales de cada ítem del cuestionario, dado que la fórmula de transformación original es <em>Transformado = (Bruto ÷ Factor) × 100</em> (Tabla 24, Resolución 2404/2019). Esta metodología garantiza trazabilidad completa y resultados idénticos a los obtenidos desde las respuestas individuales.
</div>

<!-- Tabla de Baremos Tabla 34 -->
<h3 style="font-size: 10pt; color: #6a1b9a; margin: 10pt 0 5pt 0;">Baremos Tabla 34 - Resolución 2404/2019</h3>
<table style="width: 100%; border-collapse: collapse; font-size: 7pt;">
    <tr>
        <td style="width: 50%; vertical-align: top; padding-right: 5pt; border: none;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr><th colspan="2" style="background-color: #0077B6; color: white; padding: 4pt; border: 1pt solid #333;">FORMA A (Factor 616)</th></tr>
                </thead>
                <tbody>
                    <tr><td style="padding: 2pt; border: 1pt solid #333; background-color: #4CAF50; color: white;">Sin Riesgo</td><td style="padding: 2pt; border: 1pt solid #333; text-align: center;">0.0 - 18.8</td></tr>
                    <tr><td style="padding: 2pt; border: 1pt solid #333; background-color: #8BC34A; color: white;">Riesgo Bajo</td><td style="padding: 2pt; border: 1pt solid #333; text-align: center;">18.9 - 24.4</td></tr>
                    <tr><td style="padding: 2pt; border: 1pt solid #333; background-color: #FFEB3B; color: #333;">Riesgo Medio</td><td style="padding: 2pt; border: 1pt solid #333; text-align: center;">24.5 - 29.5</td></tr>
                    <tr><td style="padding: 2pt; border: 1pt solid #333; background-color: #FF9800; color: white;">Riesgo Alto</td><td style="padding: 2pt; border: 1pt solid #333; text-align: center;">29.6 - 35.4</td></tr>
                    <tr><td style="padding: 2pt; border: 1pt solid #333; background-color: #F44336; color: white;">Riesgo Muy Alto</td><td style="padding: 2pt; border: 1pt solid #333; text-align: center;">35.5 - 100.0</td></tr>
                </tbody>
            </table>
        </td>
        <td style="width: 50%; vertical-align: top; padding-left: 5pt; border: none;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr><th colspan="2" style="background-color: #FF9800; color: white; padding: 4pt; border: 1pt solid #333;">FORMA B (Factor 512)</th></tr>
                </thead>
                <tbody>
                    <tr><td style="padding: 2pt; border: 1pt solid #333; background-color: #4CAF50; color: white;">Sin Riesgo</td><td style="padding: 2pt; border: 1pt solid #333; text-align: center;">0.0 - 19.9</td></tr>
                    <tr><td style="padding: 2pt; border: 1pt solid #333; background-color: #8BC34A; color: white;">Riesgo Bajo</td><td style="padding: 2pt; border: 1pt solid #333; text-align: center;">20.0 - 24.8</td></tr>
                    <tr><td style="padding: 2pt; border: 1pt solid #333; background-color: #FFEB3B; color: #333;">Riesgo Medio</td><td style="padding: 2pt; border: 1pt solid #333; text-align: center;">24.9 - 29.5</td></tr>
                    <tr><td style="padding: 2pt; border: 1pt solid #333; background-color: #FF9800; color: white;">Riesgo Alto</td><td style="padding: 2pt; border: 1pt solid #333; text-align: center;">29.6 - 35.4</td></tr>
                    <tr><td style="padding: 2pt; border: 1pt solid #333; background-color: #F44336; color: white;">Riesgo Muy Alto</td><td style="padding: 2pt; border: 1pt solid #333; text-align: center;">35.5 - 100.0</td></tr>
                </tbody>
            </table>
        </td>
    </tr>
</table>
';

        return $html;
    }

    /**
     * Renderiza una sección de forma con operaciones aritméticas visibles
     */
    protected function renderSeccionFormaTabla34($stats, $forma, $colorNivel, $nivelNombre)
    {
        $colorFondo = $forma === 'A' ? '#e3f2fd' : '#fff3e0';
        $colorBorde = $forma === 'A' ? '#0077B6' : '#FF9800';
        $colorTexto = $forma === 'A' ? '#0077B6' : '#e65100';
        $titulo = $forma === 'A' ? 'Jefes, Profesionales y Técnicos' : 'Auxiliares y Operarios';
        $textColorNivel = $stats['nivel_total'] === 'riesgo_medio' ? '#333' : '#fff';

        return '
<!-- FORMA ' . $forma . ' - Operaciones Aritméticas Visibles -->
<div style="background-color: ' . $colorFondo . '; border: 2pt solid ' . $colorBorde . '; padding: 10pt; margin: 8pt 0;">
    <div style="text-align: center; margin-bottom: 8pt;">
        <span style="font-size: 11pt; font-weight: bold; color: ' . $colorTexto . ';">FORMA ' . $forma . '</span>
        <span style="font-size: 9pt; color: #666;"> - ' . $titulo . ' (n=' . $stats['total'] . ')</span>
    </div>

    <!-- Paso 1: Puntajes Transformados (de la BD) -->
    <div style="background-color: white; padding: 8pt; margin: 5pt 0; border: 1pt solid #ccc;">
        <div style="font-size: 8pt; color: #666; margin-bottom: 5pt; font-weight: bold;">Paso 1: Puntajes Transformados (almacenados)</div>
        <table style="width: 100%; border: none;">
            <tr>
                <td style="text-align: center; border: none; padding: 3pt; width: 45%;">
                    <div style="font-size: 7pt; color: #666;">Intralaboral Transformado</div>
                    <div style="font-size: 14pt; font-weight: bold; color: ' . $colorTexto . ';">' . number_format($stats['promedio_intra'], 2) . '</div>
                </td>
                <td style="text-align: center; border: none; padding: 3pt; width: 10%;"></td>
                <td style="text-align: center; border: none; padding: 3pt; width: 45%;">
                    <div style="font-size: 7pt; color: #666;">Extralaboral Transformado</div>
                    <div style="font-size: 14pt; font-weight: bold; color: ' . $colorTexto . ';">' . number_format($stats['promedio_extra'], 2) . '</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Paso 2: Conversión a Brutos -->
    <div style="background-color: white; padding: 8pt; margin: 5pt 0; border: 1pt solid #ccc;">
        <div style="font-size: 8pt; color: #666; margin-bottom: 5pt; font-weight: bold;">Paso 2: Conversión a Puntajes Brutos</div>
        <table style="width: 100%; border: none; font-size: 8pt;">
            <tr>
                <td style="text-align: center; border: none; padding: 3pt; width: 45%;">
                    <div style="font-family: monospace; color: #333;">(' . number_format($stats['promedio_intra'], 2) . ' × ' . $stats['factor_intra'] . ') ÷ 100</div>
                    <div style="font-size: 12pt; font-weight: bold; color: ' . $colorTexto . ';">= ' . number_format($stats['promedio_bruto_intra'], 2) . '</div>
                </td>
                <td style="text-align: center; border: none; padding: 3pt; width: 10%;"></td>
                <td style="text-align: center; border: none; padding: 3pt; width: 45%;">
                    <div style="font-family: monospace; color: #333;">(' . number_format($stats['promedio_extra'], 2) . ' × ' . $stats['factor_extra'] . ') ÷ 100</div>
                    <div style="font-size: 12pt; font-weight: bold; color: ' . $colorTexto . ';">= ' . number_format($stats['promedio_bruto_extra'], 2) . '</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Paso 3: Suma de Brutos -->
    <div style="background-color: white; padding: 8pt; margin: 5pt 0; border: 1pt solid #ccc;">
        <div style="font-size: 8pt; color: #666; margin-bottom: 5pt; font-weight: bold;">Paso 3: Suma de Puntajes Brutos</div>
        <div style="text-align: center; font-family: monospace; font-size: 10pt;">
            ' . number_format($stats['promedio_bruto_intra'], 2) . ' + ' . number_format($stats['promedio_bruto_extra'], 2) . ' = <strong>' . number_format($stats['promedio_bruto_total'], 2) . '</strong>
        </div>
    </div>

    <!-- Paso 4: Cálculo Final (Tabla 28) -->
    <div style="background-color: #1a1a1a; color: white; padding: 10pt; margin: 5pt 0;">
        <div style="font-size: 8pt; color: #ccc; margin-bottom: 5pt; font-weight: bold;">Paso 4: Aplicar Fórmula Tabla 28</div>
        <div style="text-align: center;">
            <div style="font-family: monospace; font-size: 10pt; margin-bottom: 5pt;">
                (' . number_format($stats['promedio_bruto_total'], 2) . ' ÷ ' . $stats['factor_total'] . ') × 100
            </div>
            <div style="font-size: 24pt; font-weight: bold;">' . number_format($stats['promedio_total'], 1) . '</div>
            <div style="display: inline-block; background-color: ' . $colorNivel . '; color: ' . $textColorNivel . '; padding: 4pt 15pt; margin-top: 5pt; font-size: 9pt; font-weight: bold;">
                ' . strtoupper($nivelNombre) . '
            </div>
        </div>
    </div>
</div>
';
    }
}
