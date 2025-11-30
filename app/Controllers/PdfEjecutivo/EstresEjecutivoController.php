<?php

namespace App\Controllers\PdfEjecutivo;

use App\Libraries\PdfGaugeGenerator;
use App\Libraries\EstresScoring;

/**
 * Controlador para la sección de Estrés del PDF Ejecutivo
 *
 * Genera 3 páginas:
 * - 1 página de introducción/separador
 * - 1 página Forma A (Jefes, Profesionales, Técnicos)
 * - 1 página Forma B (Auxiliares, Operarios)
 *
 * IMPORTANTE: El cuestionario de estrés usa nomenclatura diferente:
 * - muy_bajo, bajo, medio, alto, muy_alto (NO sin_riesgo, riesgo_bajo, etc.)
 * - Etiquetas: MB, B, M, A, MA (NO SR, RB, RM, RA, RMA)
 *
 * Tema de color: Púrpura #9B59B6
 */
class EstresEjecutivoController extends PdfEjecutivoBaseController
{
    /**
     * Color temático para Estrés (púrpura)
     */
    protected $themeColor = '#9B59B6';
    protected $themeColorLight = '#f3e5f5';
    protected $themeColorDark = '#7D3C98';

    /**
     * Etiquetas cortas para el gauge de estrés
     */
    protected $estresLabelsShort = [
        'muy_bajo'  => 'MB',
        'bajo'      => 'B',
        'medio'     => 'M',
        'alto'      => 'A',
        'muy_alto'  => 'MA',
    ];

    /**
     * Nombres completos de niveles de estrés
     */
    protected $estresLabelsLong = [
        'muy_bajo'  => 'Muy Bajo',
        'bajo'      => 'Bajo',
        'medio'     => 'Medio',
        'alto'      => 'Alto',
        'muy_alto'  => 'Muy Alto',
    ];

    /**
     * Colores por nivel de estrés
     */
    protected $estresColors = [
        'muy_bajo'  => '#4CAF50',
        'bajo'      => '#8BC34A',
        'medio'     => '#FFEB3B',
        'alto'      => '#FF9800',
        'muy_alto'  => '#F44336',
    ];

    /**
     * Acciones según nivel de estrés
     */
    protected $focusActions = [
        'muy_bajo'  => 'Mantener programas de bienestar actuales',
        'bajo'      => 'Mantener programas de bienestar actuales',
        'medio'     => 'Reforzar programas de manejo del estrés',
        'alto'      => 'Intervención prioritaria en manejo del estrés',
        'muy_alto'  => 'Intervención inmediata y seguimiento individual',
    ];

    protected $resultsFormaA = [];
    protected $resultsFormaB = [];

    /**
     * Preview HTML de la sección
     */
    public function preview($batteryServiceId)
    {
        $this->initializeData($batteryServiceId);
        $this->loadResults();

        $html = $this->renderIntro();
        $html .= '<div class="page-break"></div>';
        $html .= $this->renderEstresPage('A');
        $html .= '<div class="page-break"></div>';
        $html .= $this->renderEstresPage('B');
        $html .= $this->renderFirmaConsultor();

        return $this->generatePreview($html, 'Evaluación del Estrés - Preview');
    }

    /**
     * Descargar PDF de la sección
     */
    public function download($batteryServiceId)
    {
        $this->initializeData($batteryServiceId);
        $this->loadResults();

        $html = $this->renderIntro();
        $html .= '<div class="page-break"></div>';
        $html .= $this->renderEstresPage('A');
        $html .= '<div class="page-break"></div>';
        $html .= $this->renderEstresPage('B');
        $html .= $this->renderFirmaConsultor();

        $filename = 'estres_ejecutivo_' . $batteryServiceId . '.pdf';
        return $this->generatePdf($html, $filename);
    }

    /**
     * Carga resultados de calculated_results para ambas formas
     */
    protected function loadResults()
    {
        $db = \Config\Database::connect();

        // Forma A (Jefes, Profesionales, Técnicos)
        $queryA = $db->query("
            SELECT cr.*, w.name as worker_name, w.area, w.position
            FROM calculated_results cr
            JOIN workers w ON cr.worker_id = w.id
            WHERE cr.battery_service_id = ?
            AND cr.intralaboral_form_type = 'A'
        ", [$this->batteryServiceId]);
        $this->resultsFormaA = $queryA->getResultArray();

        // Forma B (Auxiliares, Operarios)
        $queryB = $db->query("
            SELECT cr.*, w.name as worker_name, w.area, w.position
            FROM calculated_results cr
            JOIN workers w ON cr.worker_id = w.id
            WHERE cr.battery_service_id = ?
            AND cr.intralaboral_form_type = 'B'
        ", [$this->batteryServiceId]);
        $this->resultsFormaB = $queryB->getResultArray();
    }

    /**
     * Renderiza el HTML de la sección (para el Orquestador)
     * Este método es público para ser usado por PdfEjecutivoOrchestrator
     */
    public function render($batteryServiceId)
    {
        if (empty($this->resultsFormaA) && empty($this->resultsFormaB)) {
            $this->initializeData($batteryServiceId);
            $this->loadResults();
        }

        $html = $this->renderIntro();
        $html .= '<div class="page-break"></div>';
        $html .= $this->renderEstresPage('A');
        $html .= '<div class="page-break"></div>';
        $html .= $this->renderEstresPage('B');
        $html .= $this->renderFirmaConsultor();

        return $html;
    }

    /**
     * Renderiza la página introductoria/separador
     * NOTA: No usar display: flex - usar display: table/table-cell
     */
    protected function renderIntro()
    {
        $html = '
<div style="text-align: center; margin-bottom: 10pt;">
    <h1 style="font-size: 14pt; color: ' . $this->themeColor . '; margin: 0 0 6pt 0; border-bottom: 2pt solid ' . $this->themeColor . '; padding-bottom: 5pt;">
        CUESTIONARIO INDEPENDIENTE<br>CUESTIONARIO DE EVALUACIÓN DEL ESTRÉS
    </h1>
</div>

<p style="font-size: 9pt; text-align: justify; margin-bottom: 8pt;">
    El cuestionario para la evaluación del estrés es un instrumento diseñado para evaluar
    síntomas reveladores de la presencia de reacciones de estrés, distribuidos en cuatro
    categorías principales según el tipo de síntomas.
</p>

<div style="background-color: #f5f5f5; border: 1pt solid #ddd; padding: 6pt; margin-bottom: 8pt;">
    <p style="font-weight: bold; color: ' . $this->themeColor . '; margin: 0 0 5pt 0; font-size: 9pt;">Categorías de síntomas evaluados:</p>

    <table style="width: 100%; border: none; font-size: 8pt;">
        <tr>
            <td style="border: none; padding: 3pt; vertical-align: top; width: 50%;">
                <p style="margin: 0 0 2pt 0; font-weight: bold; color: ' . $this->themeColor . ';">▶ Síntomas fisiológicos</p>
                <p style="margin: 0; color: #666; font-size: 7pt;">Dolores musculares, problemas gastrointestinales, alteraciones del sueño, etc.</p>
            </td>
            <td style="border: none; padding: 3pt; vertical-align: top; width: 50%;">
                <p style="margin: 0 0 2pt 0; font-weight: bold; color: ' . $this->themeColor . ';">▶ Síntomas de comportamiento social</p>
                <p style="margin: 0; color: #666; font-size: 7pt;">Dificultad en relaciones, aislamiento, conflictos interpersonales, etc.</p>
            </td>
        </tr>
        <tr>
            <td style="border: none; padding: 3pt; vertical-align: top;">
                <p style="margin: 0 0 2pt 0; font-weight: bold; color: ' . $this->themeColor . ';">▶ Síntomas intelectuales y laborales</p>
                <p style="margin: 0; color: #666; font-size: 7pt;">Dificultad de concentración, olvidos, bajo rendimiento, etc.</p>
            </td>
            <td style="border: none; padding: 3pt; vertical-align: top;">
                <p style="margin: 0 0 2pt 0; font-weight: bold; color: ' . $this->themeColor . ';">▶ Síntomas psicoemocionales</p>
                <p style="margin: 0; color: #666; font-size: 7pt;">Ansiedad, depresión, irritabilidad, angustia, etc.</p>
            </td>
        </tr>
    </table>
</div>

<!-- Banner central con icono y título -->
<table style="width: 100%; border-collapse: collapse; margin: 10pt 0;">
    <tr>
        <td style="background-color: ' . $this->themeColor . '; color: white; text-align: center; padding: 12pt; border: none;">
            <span style="font-size: 24pt;">⚡</span>
            <span style="font-size: 16pt; font-weight: bold; margin-left: 8pt;">EVALUACIÓN DEL ESTRÉS</span>
        </td>
    </tr>
</table>

<!-- Resumen en 3 columnas -->
<table style="width: 100%; border-collapse: collapse; margin-bottom: 8pt;">
    <tr>
        <td style="width: 33%; text-align: center; background-color: ' . $this->themeColorDark . '; color: white; padding: 8pt; border: 1pt solid ' . $this->themeColor . ';">
            <span style="font-size: 16pt; font-weight: bold;">2</span><br>
            <span style="font-size: 7pt;">Páginas de Análisis</span>
        </td>
        <td style="width: 34%; text-align: center; background-color: ' . $this->themeColor . '; color: white; padding: 8pt; border: 1pt solid ' . $this->themeColorDark . ';">
            <span style="font-size: 16pt; font-weight: bold;">31</span><br>
            <span style="font-size: 7pt;">Ítems Evaluados</span>
        </td>
        <td style="width: 33%; text-align: center; background-color: #AB69C6; color: white; padding: 8pt; border: 1pt solid ' . $this->themeColor . ';">
            <span style="font-size: 16pt; font-weight: bold;">4</span><br>
            <span style="font-size: 7pt;">Categorías de Síntomas</span>
        </td>
    </tr>
</table>

<div style="background-color: #fff3cd; border: 1pt solid #ffc107; padding: 6pt; margin-top: 8pt;">
    <p style="font-size: 8pt; margin: 0; color: #856404;">
        <strong>Nota:</strong> El cuestionario de estrés utiliza una nomenclatura diferente a los cuestionarios
        intralaboral y extralaboral. Los niveles son: Muy Bajo, Bajo, Medio, Alto y Muy Alto,
        en lugar de Sin Riesgo, Riesgo Bajo, etc.
    </p>
</div>';

        return $html;
    }

    /**
     * Renderiza una página de estrés (Forma A o B)
     */
    protected function renderEstresPage($forma)
    {
        $baremo = EstresScoring::getBaremo($forma);
        $results = ($forma === 'A') ? $this->resultsFormaA : $this->resultsFormaB;

        if (empty($results)) {
            return $this->renderEstresSinDatos($forma);
        }

        // Calcular promedio
        $puntajes = array_column($results, 'estres_total_puntaje');
        $puntajes = array_filter($puntajes, function($v) { return $v !== null && $v !== ''; });

        if (empty($puntajes)) {
            return $this->renderEstresSinDatos($forma);
        }

        $promedio = round(array_sum($puntajes) / count($puntajes), 2);
        $nivel = $this->getNivelEstresFromPuntaje($promedio, $baremo);
        $nivelNombre = $this->estresLabelsLong[$nivel] ?? 'Desconocido';
        $nivelColor = $this->estresColors[$nivel] ?? '#999';

        // Generar gauge específico para estrés
        $gaugeUri = $this->generateEstresGauge($promedio, $baremo, $nivelColor);

        // Calcular distribución
        $distribucion = $this->calculateEstresDistribution($results);
        $total = count($results);

        // Agrupar para barra: Alto+MuyAlto, Medio, Bajo+MuyBajo
        $countAlto = $distribucion['alto'] + $distribucion['muy_alto'];
        $countMedio = $distribucion['medio'];
        $countBajo = $distribucion['bajo'] + $distribucion['muy_bajo'];

        $pctAlto = $total > 0 ? round(($countAlto / $total) * 100) : 0;
        $pctMedio = $total > 0 ? round(($countMedio / $total) * 100) : 0;
        $pctBajo = $total > 0 ? round(($countBajo / $total) * 100) : 0;

        // Ajustar para que sume 100%
        $suma = $pctAlto + $pctMedio + $pctBajo;
        if ($suma !== 100 && $suma > 0) {
            $diff = 100 - $suma;
            if ($pctBajo > 0) $pctBajo += $diff;
            elseif ($pctMedio > 0) $pctMedio += $diff;
            else $pctAlto += $diff;
        }

        // Título según forma
        $tipoTrabajador = ($forma === 'A') ? 'Profesionales o de Jefatura' : 'Auxiliares u Operativos';
        $badgeColor = ($forma === 'A') ? '#0077B6' : '#FF6B35';

        // Acción según nivel
        $accionNivel = $this->focusActions[$nivel] ?? 'Evaluación requerida';
        $verboAccion = $this->getVerboAccionEstres($nivel);

        $html = '
<!-- ELEMENTO 1: Título -->
<h1 style="font-size: 13pt; color: ' . $this->themeColor . '; margin: 0 0 3pt 0; padding-bottom: 4pt; border-bottom: 2pt solid ' . $this->themeColor . ';">
    Evaluación del Estrés
</h1>
<p style="font-size: 9pt; color: #666; text-align: center; margin: 0 0 8pt 0;">
    <span style="background: ' . $badgeColor . '; color: white; padding: 2pt 8pt; font-weight: bold;">
        FORMA ' . $forma . ' - ' . $tipoTrabajador . '
    </span>
</p>

<!-- ELEMENTO 2: Caja de descripción -->
<div style="background-color: #f9f9f9; border: 1pt solid #ddd; padding: 6pt; margin-bottom: 8pt;">
    <p style="font-size: 8pt; margin: 0; text-align: justify;">
        El cuestionario para la evaluación del estrés es un instrumento diseñado para evaluar
        síntomas reveladores de la presencia de reacciones de estrés, distribuidos en cuatro
        categorías principales según el tipo de síntomas de estrés.
    </p>
</div>

<!-- ELEMENTO 3: Gauge centrado -->
<div style="text-align: center; margin: 5pt 0;">
    <img src="' . $gaugeUri . '" style="width: 160pt; height: auto;" />

    <!-- ELEMENTO 4: Leyenda de convenciones (específica para estrés) -->
    <div style="font-size: 6pt; color: #666; margin: 2pt 0; line-height: 1.2;">
        MB=Muy Bajo | B=Bajo | M=Medio | A=Alto | MA=Muy Alto
    </div>
</div>

<!-- ELEMENTO 5: Tabla de baremos -->
<table style="width: 100%; font-size: 7pt; border-collapse: collapse; margin: 5pt 0;">
    <tr>
        <td style="background: #4CAF50; color: white; text-align: center; padding: 2pt; border: 1pt solid #ccc;">Muy Bajo</td>
        <td style="background: #8BC34A; color: white; text-align: center; padding: 2pt; border: 1pt solid #ccc;">Bajo</td>
        <td style="background: #FFEB3B; color: #333; text-align: center; padding: 2pt; border: 1pt solid #ccc;">Medio</td>
        <td style="background: #FF9800; color: white; text-align: center; padding: 2pt; border: 1pt solid #ccc;">Alto</td>
        <td style="background: #F44336; color: white; text-align: center; padding: 2pt; border: 1pt solid #ccc;">Muy Alto</td>
    </tr>
    <tr>
        <td style="background: #E8F5E9; text-align: center; padding: 2pt; border: 1pt solid #ccc; font-size: 6pt;">' . $baremo['muy_bajo'][0] . '-' . $baremo['muy_bajo'][1] . '</td>
        <td style="background: #F1F8E9; text-align: center; padding: 2pt; border: 1pt solid #ccc; font-size: 6pt;">' . $baremo['bajo'][0] . '-' . $baremo['bajo'][1] . '</td>
        <td style="background: #FFFDE7; text-align: center; padding: 2pt; border: 1pt solid #ccc; font-size: 6pt;">' . $baremo['medio'][0] . '-' . $baremo['medio'][1] . '</td>
        <td style="background: #FFF3E0; text-align: center; padding: 2pt; border: 1pt solid #ccc; font-size: 6pt;">' . $baremo['alto'][0] . '-' . $baremo['alto'][1] . '</td>
        <td style="background: #FFEBEE; text-align: center; padding: 2pt; border: 1pt solid #ccc; font-size: 6pt;">' . $baremo['muy_alto'][0] . '-' . $baremo['muy_alto'][1] . '</td>
    </tr>
</table>

<!-- ELEMENTO 6: Texto interpretativo -->
<p style="font-size: 8pt; text-align: justify; margin: 8pt 0;">
    Para el cuestionario Tipo <strong>' . $forma . '</strong> se evidencia que el nivel de estrés
    se encuentra con un valor de <strong>' . number_format($promedio, 2) . '</strong> denominándose
    <span style="background-color: ' . $nivelColor . '; color: ' . ($nivel === 'medio' ? '#333' : '#fff') . '; padding: 1pt 4pt; font-weight: bold;">' . strtoupper($nivelNombre) . '</span>,
    por lo que se debe <strong>' . $verboAccion . '</strong> las intervenciones que se realicen para los
    cargos <strong>' . $tipoTrabajador . '</strong>.
</p>

<!-- ELEMENTO 7: Título subsección distribución -->
<p style="font-size: 9pt; font-weight: bold; color: ' . $this->themeColor . '; margin: 8pt 0 4pt 0;">Porcentajes de distribución por Niveles de Estrés:</p>

<!-- ELEMENTO 8: Barra de distribución -->
<table style="width: 100%; height: 16pt; border-collapse: collapse; margin-bottom: 4pt;">
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

<!-- Leyenda de la barra -->
<table style="width: 100%; font-size: 7pt; border: none; margin-bottom: 6pt;">
    <tr>
        <td style="border: none; text-align: left; color: #F44336;">■ Alto + Muy Alto: ' . $pctAlto . '% (' . $countAlto . ')</td>
        <td style="border: none; text-align: center; color: #DAA520;">■ Medio: ' . $pctMedio . '% (' . $countMedio . ')</td>
        <td style="border: none; text-align: right; color: #4CAF50;">■ Bajo + Muy Bajo: ' . $pctBajo . '% (' . $countBajo . ')</td>
    </tr>
</table>

<!-- ELEMENTO 9: Texto de distribución -->
<p style="font-size: 8pt; text-align: justify; margin: 5pt 0;">
    Se evidencia que el <strong style="color: #F44336;">' . $pctAlto . '%</strong> de los encuestados con el cuestionario Tipo ' . $forma . '
    presentan un nivel de estrés Alto y Muy Alto, el siguiente <strong style="color: #DAA520;">' . $pctMedio . '%</strong>
    presenta un nivel Medio y el <strong style="color: #4CAF50;">' . $pctBajo . '%</strong> restante presenta un nivel Bajo o Muy Bajo.
</p>

<!-- ELEMENTO 10: Caja de foco objetivo -->
<div style="border: 1pt solid ' . $this->themeColor . '; background-color: ' . $this->themeColorLight . '; padding: 5pt 7pt; margin-top: 6pt;">
    <p style="font-size: 8pt; margin: 0;">
        <span style="font-weight: bold; color: ' . $this->themeColor . ';">Foco Objetivo:</span>
        Cargos ' . $tipoTrabajador . '<br>
        <span style="font-weight: bold; color: ' . $this->themeColor . ';">Acción:</span> ' . $accionNivel . '<br>
        <span style="font-size: 7pt; color: #666;">Los síntomas de estrés identificados requieren atención según el nivel detectado.</span>
    </p>
</div>';

        // ELEMENTO 11: Tabla de trabajadores con nivel alto y muy alto
        $trabajadoresRiesgo = $this->getTrabajadoresEnRiesgoAlto($results);
        if (!empty($trabajadoresRiesgo)) {
            $html .= '
<p style="font-size: 8pt; font-weight: bold; color: ' . $this->themeColor . '; margin: 8pt 0 3pt 0;">Trabajadores con Nivel de Estrés Alto y Muy Alto:</p>
<table style="width: 100%; font-size: 7pt; border-collapse: collapse;">
    <tr>
        <th style="background: ' . $this->themeColor . '; color: white; padding: 3pt; border: 1pt solid #333;">Área</th>
        <th style="background: ' . $this->themeColor . '; color: white; padding: 3pt; border: 1pt solid #333;">Cargo</th>
        <th style="background: ' . $this->themeColor . '; color: white; padding: 3pt; border: 1pt solid #333; width: 50pt;">Participantes</th>
        <th style="background: ' . $this->themeColor . '; color: white; padding: 3pt; border: 1pt solid #333; width: 60pt;">Nivel</th>
    </tr>';

            $maxRows = min(count($trabajadoresRiesgo), 5);
            for ($i = 0; $i < $maxRows; $i++) {
                $trab = $trabajadoresRiesgo[$i];
                $nivelTrabColor = $this->estresColors[$trab['nivel']] ?? '#999';
                $nivelTrabNombre = $this->estresLabelsLong[$trab['nivel']] ?? $trab['nivel'];
                $html .= '
    <tr>
        <td style="padding: 2pt; border: 1pt solid #ccc;">' . esc($trab['area']) . '</td>
        <td style="padding: 2pt; border: 1pt solid #ccc;">' . esc($trab['cargo']) . '</td>
        <td style="padding: 2pt; border: 1pt solid #ccc; text-align: center;">' . $trab['count'] . '</td>
        <td style="padding: 2pt; border: 1pt solid #ccc; text-align: center; background-color: ' . $nivelTrabColor . '; color: white;">' . $nivelTrabNombre . '</td>
    </tr>';
            }

            $html .= '</table>';
        }

        // ELEMENTO 12: Texto IA (si existe)
        $textoIA = $this->getTextoIA($forma);
        if (!empty($textoIA)) {
            $html .= '
<div style="border-left: 2pt solid ' . $this->themeColor . '; background-color: ' . $this->themeColorLight . '; padding: 5pt 7pt; margin-top: 6pt;">
    <p style="font-weight: bold; color: ' . $this->themeColorDark . '; margin: 0 0 3pt 0; font-size: 8pt;">Análisis del Especialista SST:</p>
    <p style="font-size: 7.5pt; margin: 0; text-align: justify;">' . esc($textoIA) . '</p>
</div>';
        }

        return $html;
    }

    /**
     * Renderiza página cuando no hay datos
     */
    protected function renderEstresSinDatos($forma)
    {
        $tipoTrabajador = ($forma === 'A') ? 'Profesionales o de Jefatura' : 'Auxiliares u Operativos';
        $badgeColor = ($forma === 'A') ? '#0077B6' : '#FF6B35';

        return '
<h1 style="font-size: 13pt; color: ' . $this->themeColor . '; margin: 0 0 3pt 0; padding-bottom: 4pt; border-bottom: 2pt solid ' . $this->themeColor . ';">
    Evaluación del Estrés
</h1>
<p style="font-size: 9pt; color: #666; text-align: center; margin: 0 0 15pt 0;">
    <span style="background: ' . $badgeColor . '; color: white; padding: 2pt 8pt; font-weight: bold;">
        FORMA ' . $forma . ' - ' . $tipoTrabajador . '
    </span>
</p>

<div style="background-color: #f5f5f5; border: 1pt solid #ddd; padding: 20pt; text-align: center; margin: 40pt 0;">
    <p style="font-size: 12pt; color: #666; margin: 0;">
        No hay datos disponibles para el cuestionario de estrés<br>
        en la Forma ' . $forma . '
    </p>
</div>';
    }

    /**
     * Genera un gauge SVG específico para estrés con etiquetas MB, B, M, A, MA
     */
    protected function generateEstresGauge($value, $baremo, $currentColor)
    {
        // Parámetros del gauge
        $cx = 100;
        $cy = 90;
        $radius = 70;
        $labelRadius = 50;
        $needleLength = 60;

        // Calcular ángulo de la aguja
        $percentage = min(100, max(0, $value));
        $needleAngleDeg = 180 - ($percentage * 1.8);
        $needleAngleRad = deg2rad($needleAngleDeg);

        $needleX = $cx + ($needleLength * cos($needleAngleRad));
        $needleY = $cy - ($needleLength * sin($needleAngleRad));

        // Función para calcular punto en el arco
        $getPoint = function($pct, $r = null) use ($cx, $cy, $radius) {
            $r = $r ?? $radius;
            $angleDeg = 180 - ($pct * 1.8);
            $angleRad = deg2rad($angleDeg);
            return [
                round($cx + $r * cos($angleRad), 2),
                round($cy - $r * sin($angleRad), 2)
            ];
        };

        // Generar los segmentos del arco y etiquetas
        $levels = ['muy_bajo', 'bajo', 'medio', 'alto', 'muy_alto'];
        $pathsSvg = '';
        $labelsSvg = '';

        foreach ($levels as $lvl) {
            if (!isset($baremo[$lvl])) continue;

            $startPct = $baremo[$lvl][0];
            $endPct = min(100, $baremo[$lvl][1]);

            $p1 = $getPoint($startPct);
            $p2 = $getPoint($endPct);

            $pathsSvg .= '<path d="M ' . $p1[0] . ' ' . $p1[1] .
                         ' A ' . $radius . ' ' . $radius .
                         ' 0 0 1 ' . $p2[0] . ' ' . $p2[1] .
                         '" fill="none" stroke="' . $this->estresColors[$lvl] .
                         '" stroke-width="12"/>' . "\n    ";

            // Etiqueta en UNA SOLA LINEA: "MB: 0-7.8"
            $midPct = ($startPct + $endPct) / 2;
            $labelPos = $getPoint($midPct, $labelRadius);
            $labelsSvg .= '<text x="' . $labelPos[0] . '" y="' . $labelPos[1] .
                         '" font-family="Arial" font-size="4" text-anchor="middle" fill="#333">' .
                         $this->estresLabelsShort[$lvl] . ': ' . $startPct . '-' . $endPct . '</text>' . "\n    ";
        }

        // Etiquetas de extremos (0 y 100)
        $labelsSvg .= '<text x="28" y="95" font-family="Arial" font-size="5" fill="#333">0</text>' . "\n    ";
        $labelsSvg .= '<text x="168" y="95" font-family="Arial" font-size="5" fill="#333">100</text>' . "\n    ";

        $svg = '<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="200" height="120" viewBox="0 0 200 120">
    <!-- Fondo gris del arco -->
    <path d="M 30 90 A 70 70 0 0 1 170 90" fill="none" stroke="#E8E8E8" stroke-width="12"/>

    <!-- Segmentos dinámicos según baremo -->
    ' . $pathsSvg . '

    <!-- Etiquetas de cada segmento -->
    ' . $labelsSvg . '

    <!-- Aguja apuntando a: ' . number_format($value, 1) . ' -->
    <line x1="' . $cx . '" y1="' . $cy . '" x2="' . round($needleX, 2) . '" y2="' . round($needleY, 2) . '"
          stroke="#333" stroke-width="3" stroke-linecap="round"/>

    <!-- Centro de la aguja -->
    <circle cx="' . $cx . '" cy="' . $cy . '" r="8" fill="#333"/>

    <!-- Valor actual -->
    <text x="' . $cx . '" y="115" font-family="Arial, sans-serif" font-size="14" font-weight="bold"
          text-anchor="middle" fill="' . $currentColor . '">' . number_format($value, 1) . '</text>
</svg>';

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    /**
     * Obtiene el nivel de estrés basado en el puntaje y baremo
     */
    protected function getNivelEstresFromPuntaje($puntaje, $baremo)
    {
        foreach ($baremo as $nivel => $rango) {
            if ($puntaje >= $rango[0] && $puntaje <= $rango[1]) {
                return $nivel;
            }
        }
        return 'muy_bajo';
    }

    /**
     * Calcula distribución por niveles de estrés
     */
    protected function calculateEstresDistribution($results)
    {
        $distribution = [
            'muy_bajo'  => 0,
            'bajo'      => 0,
            'medio'     => 0,
            'alto'      => 0,
            'muy_alto'  => 0,
        ];

        foreach ($results as $result) {
            $nivel = $result['estres_total_nivel'] ?? '';
            if (isset($distribution[$nivel])) {
                $distribution[$nivel]++;
            }
        }

        return $distribution;
    }

    /**
     * Obtiene trabajadores en riesgo alto y muy alto
     */
    protected function getTrabajadoresEnRiesgoAlto($results)
    {
        $trabajadores = [];

        foreach ($results as $result) {
            $nivel = $result['estres_total_nivel'] ?? '';
            if ($nivel === 'alto' || $nivel === 'muy_alto') {
                $key = ($result['area'] ?? 'Sin área') . '|' . ($result['position'] ?? 'Sin cargo') . '|' . $nivel;
                if (!isset($trabajadores[$key])) {
                    $trabajadores[$key] = [
                        'area' => $result['area'] ?? 'Sin área',
                        'cargo' => $result['position'] ?? 'Sin cargo',
                        'nivel' => $nivel,
                        'count' => 0
                    ];
                }
                $trabajadores[$key]['count']++;
            }
        }

        usort($trabajadores, function($a, $b) {
            return $b['count'] - $a['count'];
        });

        return array_values($trabajadores);
    }

    /**
     * Obtiene verbo de acción según nivel de estrés
     */
    protected function getVerboAccionEstres($nivel)
    {
        $verbos = [
            'muy_bajo'  => 'mantener',
            'bajo'      => 'continuar',
            'medio'     => 'reforzar',
            'alto'      => 'priorizar',
            'muy_alto'  => 'implementar inmediatamente',
        ];
        return $verbos[$nivel] ?? 'evaluar';
    }

    /**
     * Obtiene texto de análisis IA si existe en report_sections
     */
    protected function getTextoIA($forma)
    {
        $db = \Config\Database::connect();

        $reportQuery = $db->query("
            SELECT id FROM reports WHERE battery_service_id = ? LIMIT 1
        ", [$this->batteryServiceId]);
        $report = $reportQuery->getRowArray();

        if (!$report) {
            return null;
        }

        $query = $db->query("
            SELECT ai_generated_text, consultant_comment
            FROM report_sections
            WHERE report_id = ?
            AND questionnaire_type = 'estres'
            AND form_type = ?
            LIMIT 1
        ", [$report['id'], $forma]);

        $result = $query->getRowArray();

        if (!empty($result['consultant_comment'])) {
            return $result['consultant_comment'];
        }
        return $result['ai_generated_text'] ?? null;
    }

    /**
     * Renderiza la firma del consultor al final de la última página
     */
    protected function renderFirmaConsultor()
    {
        $consultant = $this->consultantData;

        $html = '
<!-- Firma del Consultor -->
<div style="margin-top: 40pt;">
    <p style="font-size: 11pt; color: #333; margin: 0 0 30pt 0;">Cordialmente,</p>

    <!-- Imagen de firma (si existe) -->
    <div style="margin-bottom: 10pt;">';

        if (!empty($consultant['firma_path'])) {
            $firmaPath = FCPATH . $consultant['firma_path'];
            $firmaDataUri = $this->imageToDataUri($firmaPath);
            if ($firmaDataUri) {
                $html .= '<img src="' . $firmaDataUri . '" alt="Firma" style="max-height: 80px; max-width: 200px;">';
            } else {
                $html .= '<div style="height: 50pt; width: 180pt; border-bottom: 1pt solid #333;"></div>';
            }
        } else {
            $html .= '<div style="height: 50pt; width: 180pt; border-bottom: 1pt solid #333;"></div>';
        }

        $html .= '
    </div>

    <!-- Datos del consultor -->
    <div style="font-size: 11pt;">
        <p style="margin: 0; font-weight: bold; color: #0077B6;">
            ' . htmlspecialchars($consultant['nombre_completo'] ?? 'Consultor') . '
        </p>
        <p style="margin: 3pt 0; color: #555;">
            ' . htmlspecialchars($consultant['cargo'] ?? 'Especialista en Seguridad y Salud en el Trabajo') . '
        </p>';

        if (!empty($consultant['licencia_sst'])) {
            $html .= '
        <p style="margin: 3pt 0; color: #555; font-size: 10pt;">
            Licencia SST: ' . htmlspecialchars($consultant['licencia_sst']) . '
        </p>';
        }

        $html .= '
    </div>

    <!-- Información de contacto -->
    <div style="margin-top: 12pt; font-size: 10pt;">';

        if (!empty($consultant['email'])) {
            $html .= '
        <p style="margin: 3pt 0;">
            <a href="mailto:' . htmlspecialchars($consultant['email']) . '" style="color: #0077B6; text-decoration: none;">
                ' . htmlspecialchars($consultant['email']) . '
            </a>
        </p>';
        }

        if (!empty($consultant['website'])) {
            $html .= '
        <p style="margin: 3pt 0;">
            <a href="' . htmlspecialchars($consultant['website']) . '" style="color: #0077B6; text-decoration: none;">
                ' . htmlspecialchars($consultant['website']) . '
            </a>
        </p>';
        }

        if (!empty($consultant['linkedin'])) {
            $html .= '
        <p style="margin: 3pt 0;">
            <a href="' . htmlspecialchars($consultant['linkedin']) . '" style="color: #0077B6; text-decoration: none;">
                ' . htmlspecialchars($consultant['linkedin']) . '
            </a>
        </p>';
        }

        $html .= '
    </div>
</div>';

        return $html;
    }
}
