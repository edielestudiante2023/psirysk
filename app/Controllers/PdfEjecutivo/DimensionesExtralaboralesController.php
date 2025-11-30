<?php

namespace App\Controllers\PdfEjecutivo;

use App\Libraries\PdfGaugeGenerator;
use App\Libraries\ExtralaboralScoring;

/**
 * Controlador para la sección de Dimensiones Extralaborales del PDF Ejecutivo
 *
 * Genera 15 páginas:
 * - 1 página de introducción
 * - 7 páginas de dimensiones Forma A (Jefes, Profesionales, Técnicos)
 * - 7 páginas de dimensiones Forma B (Auxiliares, Operarios)
 *
 * Los baremos se obtienen desde ExtralaboralScoring
 * para garantizar consistencia con el núcleo del sistema (Single Source of Truth).
 *
 * Tema de color: Verde #00A86B
 */
class DimensionesExtralaboralesController extends PdfEjecutivoBaseController
{
    /**
     * Definición de las 7 dimensiones extralaborales
     * Según Resolución 2404/2019
     */
    protected $dimensiones = [
        'tiempo_fuera_trabajo' => [
            'nombre' => 'Tiempo Fuera del Trabajo',
            'codigo' => 'tiempo_fuera_trabajo',
            'definicion' => 'Se refiere al tiempo que el individuo dedica a actividades diferentes a las laborales, como descansar, compartir con familia y amigos, atender responsabilidades personales o domésticas, realizar actividades de recreación y ocio.',
            'campo_puntaje' => 'extralaboral_tiempo_fuera_puntaje',
            'campo_nivel' => 'extralaboral_tiempo_fuera_nivel',
        ],
        'relaciones_familiares' => [
            'nombre' => 'Relaciones Familiares',
            'codigo' => 'relaciones_familiares',
            'definicion' => 'Propiedades que caracterizan las interacciones del individuo con su núcleo familiar. Esta dimensión es una condición protectora cuando las relaciones familiares son armónicas.',
            'campo_puntaje' => 'extralaboral_relaciones_familiares_puntaje',
            'campo_nivel' => 'extralaboral_relaciones_familiares_nivel',
        ],
        'comunicacion_relaciones' => [
            'nombre' => 'Comunicación y Relaciones Interpersonales',
            'codigo' => 'comunicacion_relaciones',
            'definicion' => 'Cualidades que caracterizan la comunicación e interacciones del individuo con sus allegados y amigos. Esta dimensión constituye una condición protectora cuando la persona tiene una red de apoyo social funcional.',
            'campo_puntaje' => 'extralaboral_comunicacion_puntaje',
            'campo_nivel' => 'extralaboral_comunicacion_nivel',
        ],
        'situacion_economica' => [
            'nombre' => 'Situación Económica del Grupo Familiar',
            'codigo' => 'situacion_economica',
            'definicion' => 'Trata de la disponibilidad de medios económicos para que el trabajador y su grupo familiar atiendan sus gastos básicos. Es fuente de riesgo cuando los ingresos son insuficientes para cubrir necesidades.',
            'campo_puntaje' => 'extralaboral_situacion_economica_puntaje',
            'campo_nivel' => 'extralaboral_situacion_economica_nivel',
        ],
        'caracteristicas_vivienda' => [
            'nombre' => 'Características de la Vivienda y de su Entorno',
            'codigo' => 'caracteristicas_vivienda',
            'definicion' => 'Se refiere a las condiciones de infraestructura, ubicación y entorno de las instalaciones físicas del lugar habitual de residencia del trabajador y de su grupo familiar.',
            'campo_puntaje' => 'extralaboral_caracteristicas_vivienda_puntaje',
            'campo_nivel' => 'extralaboral_caracteristicas_vivienda_nivel',
        ],
        'influencia_entorno' => [
            'nombre' => 'Influencia del Entorno Extralaboral sobre el Trabajo',
            'codigo' => 'influencia_entorno',
            'definicion' => 'Corresponde al influjo de las exigencias de los roles familiares y personales en el bienestar y en la actividad laboral del trabajador.',
            'campo_puntaje' => 'extralaboral_influencia_entorno_puntaje',
            'campo_nivel' => 'extralaboral_influencia_entorno_nivel',
        ],
        'desplazamiento' => [
            'nombre' => 'Desplazamiento Vivienda – Trabajo – Vivienda',
            'codigo' => 'desplazamiento',
            'definicion' => 'Son las condiciones en que se realiza el traslado del trabajador desde su sitio de vivienda hasta su lugar de trabajo y viceversa. Comprende la facilidad, la comodidad del transporte y la duración del recorrido.',
            'campo_puntaje' => 'extralaboral_desplazamiento_puntaje',
            'campo_nivel' => 'extralaboral_desplazamiento_nivel',
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
     * Color temático para Extralaboral (verde)
     */
    protected $themeColor = '#00A86B';
    protected $themeColorLight = '#e8f5e9';
    protected $themeColorDark = '#006B45';

    /**
     * Obtiene el baremo de una dimensión desde ExtralaboralScoring
     * Esta es la fuente única de verdad para los baremos (Tablas 17 y 18)
     *
     * @param string $dimensionCodigo Código de la dimensión
     * @param string $forma 'A' o 'B'
     * @return array Baremo de la dimensión
     */
    protected function getBaremoDimension($dimensionCodigo, $forma)
    {
        return ExtralaboralScoring::getBaremoDimension($dimensionCodigo, $forma);
    }

    /**
     * Preview HTML de la sección
     */
    public function preview($batteryServiceId)
    {
        $this->initializeData($batteryServiceId);
        $this->loadResults();
        $this->gaugeGenerator = new PdfGaugeGenerator();

        $html = $this->renderIntro();
        $html .= $this->renderAllDimensiones();

        return $this->generatePreview($html, 'Dimensiones Extralaborales - Preview');
    }

    /**
     * Descargar PDF de la sección
     */
    public function download($batteryServiceId)
    {
        $this->initializeData($batteryServiceId);
        $this->loadResults();
        $this->gaugeGenerator = new PdfGaugeGenerator();

        $html = $this->renderIntro();
        $html .= $this->renderAllDimensiones();

        $filename = 'dimensiones_extralaborales_' . $batteryServiceId . '.pdf';
        return $this->generatePdf($html, $filename);
    }

    /**
     * Carga resultados de calculated_results para ambas formas
     * NOTA: El cuestionario extralaboral es el mismo para ambas formas,
     * pero los baremos son diferentes según el tipo de cargo
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
     * Renderiza la página introductoria
     * NOTA: No usar display: flex - usar display: table/table-cell
     */
    protected function renderIntro()
    {
        $numDimensiones = count($this->dimensiones);
        $totalPaginas = ($numDimensiones * 2); // 7 dimensiones × 2 formas = 14

        $html = '
<div style="text-align: center; margin-bottom: 20pt;">
    <h1 style="font-size: 16pt; color: ' . $this->themeColor . '; margin: 0 0 10pt 0; border-bottom: 2pt solid ' . $this->themeColor . '; padding-bottom: 8pt;">
        SECCIÓN<br>Dimensiones Extralaborales
    </h1>
</div>

<p style="font-size: 10pt; text-align: justify; margin-bottom: 15pt;">
    Esta sección presenta el análisis detallado de cada dimensión del Cuestionario
    de Factores de Riesgo Psicosocial Extralaboral. Cada dimensión representa un aspecto específico
    del ambiente extralaboral que puede afectar la salud y el bienestar de los trabajadores,
    incluyendo factores familiares, sociales, económicos y de entorno.
</p>

<div style="background-color: #f5f5f5; border: 1pt solid #ddd; padding: 10pt; margin-bottom: 15pt;">
    <p style="font-weight: bold; color: ' . $this->themeColor . '; margin: 0 0 8pt 0;">Las 7 Dimensiones Extralaborales:</p>
    <table style="width: 100%; border: none; font-size: 9pt;">
        <tr>
            <td style="border: none; padding: 3pt; width: 50%;">• Tiempo Fuera del Trabajo</td>
            <td style="border: none; padding: 3pt; width: 50%;">• Relaciones Familiares</td>
        </tr>
        <tr>
            <td style="border: none; padding: 3pt;">• Comunicación y Relaciones Interpersonales</td>
            <td style="border: none; padding: 3pt;">• Situación Económica del Grupo Familiar</td>
        </tr>
        <tr>
            <td style="border: none; padding: 3pt;">• Características de la Vivienda y su Entorno</td>
            <td style="border: none; padding: 3pt;">• Influencia del Entorno Extralaboral</td>
        </tr>
        <tr>
            <td style="border: none; padding: 3pt;">• Desplazamiento Vivienda – Trabajo – Vivienda</td>
            <td style="border: none; padding: 3pt;"></td>
        </tr>
    </table>
</div>

<!-- Resumen usando table-cell en lugar de flex -->
<table style="width: 100%; border-collapse: collapse; margin-bottom: 15pt;">
    <tr>
        <td style="width: 33%; text-align: center; background-color: ' . $this->themeColor . '; color: white; padding: 10pt; border: 1pt solid ' . $this->themeColorDark . ';">
            <span style="font-size: 20pt; font-weight: bold;">' . $numDimensiones . '</span><br>
            <span style="font-size: 9pt;">Dimensiones Forma A</span>
        </td>
        <td style="width: 33%; text-align: center; background-color: #2ECC71; color: white; padding: 10pt; border: 1pt solid ' . $this->themeColor . ';">
            <span style="font-size: 20pt; font-weight: bold;">' . $numDimensiones . '</span><br>
            <span style="font-size: 9pt;">Dimensiones Forma B</span>
        </td>
        <td style="width: 33%; text-align: center; background-color: #58D68D; color: white; padding: 10pt; border: 1pt solid #2ECC71;">
            <span style="font-size: 20pt; font-weight: bold;">' . $totalPaginas . '</span><br>
            <span style="font-size: 9pt;">Páginas Total</span>
        </td>
    </tr>
</table>

<div style="background-color: #fff3cd; border: 1pt solid #ffc107; padding: 8pt; margin-top: 15pt;">
    <p style="font-size: 9pt; margin: 0; color: #856404;">
        <strong>Nota:</strong> El cuestionario extralaboral es el mismo para todos los trabajadores,
        pero los baremos de interpretación difieren según el tipo de cargo:
        Forma A para jefes, profesionales y técnicos; Forma B para auxiliares y operarios.
    </p>
</div>

<div class="page-break"></div>';

        return $html;
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
            $this->gaugeGenerator = new PdfGaugeGenerator();
        }

        $html = $this->renderIntro();
        $html .= $this->renderAllDimensiones();

        return $html;
    }

    /**
     * Renderiza todas las páginas de dimensiones
     * Por cada dimensión: primero Forma A, luego Forma B
     */
    protected function renderAllDimensiones()
    {
        $html = '';
        $isFirst = true;

        foreach ($this->dimensiones as $codigo => $dimension) {
            // Forma A
            if (!$isFirst) {
                $html .= '<div class="page-break"></div>';
            }
            $html .= $this->renderDimension($codigo, 'A');
            $isFirst = false;

            // Forma B
            $html .= '<div class="page-break"></div>';
            $html .= $this->renderDimension($codigo, 'B');
        }

        return $html;
    }

    /**
     * Renderiza una página de dimensión
     */
    protected function renderDimension($dimensionCodigo, $forma)
    {
        $dimension = $this->dimensiones[$dimensionCodigo];
        $baremo = $this->getBaremoDimension($dimensionCodigo, $forma);

        if ($baremo === null) {
            return $this->renderDimensionSinBaremo($dimension, $forma);
        }

        $results = ($forma === 'A') ? $this->resultsFormaA : $this->resultsFormaB;

        if (empty($results)) {
            return $this->renderDimensionSinDatos($dimension, $forma);
        }

        // Calcular promedio
        $puntajes = array_column($results, $dimension['campo_puntaje']);
        $puntajes = array_filter($puntajes, function($v) { return $v !== null && $v !== ''; });

        if (empty($puntajes)) {
            return $this->renderDimensionSinDatos($dimension, $forma);
        }

        $promedio = round(array_sum($puntajes) / count($puntajes), 2);
        $nivel = $this->getNivelFromPuntaje($promedio, $baremo);
        $nivelNombre = $this->getRiskName($nivel);
        $nivelColor = $this->getRiskColor($nivel);

        // Generar gauge
        $gaugeUri = $this->gaugeGenerator->generate($promedio, $baremo);

        // Calcular distribución
        $distribucion = $this->calculateDistribution($results, $dimension['campo_nivel']);
        $total = count($results);

        // Agrupar para barra: Alto+MuyAlto, Medio, Bajo+SinRiesgo
        $countAlto = $distribucion['riesgo_alto'] + $distribucion['riesgo_muy_alto'];
        $countMedio = $distribucion['riesgo_medio'];
        $countBajo = $distribucion['riesgo_bajo'] + $distribucion['sin_riesgo'];

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
        $tipoTrabajador = ($forma === 'A') ? 'Jefes, Profesionales y Técnicos' : 'Auxiliares y Operarios';
        $colorBorde = ($forma === 'A') ? $this->themeColor : '#FF6600';

        // Acción según nivel
        $accionNivel = $this->focusActions[$nivel] ?? 'Evaluación requerida';
        $verboAccion = $this->getVerboAccion($nivel);

        $html = '
<!-- ELEMENTO 1: Título -->
<h1 style="font-size: 13pt; color: ' . $this->themeColor . '; margin: 0 0 3pt 0; padding-bottom: 4pt; border-bottom: 2pt solid ' . $colorBorde . ';">
    Dimensión: ' . esc($dimension['nombre']) . '
</h1>
<p style="font-size: 9pt; color: #666; text-align: center; margin: 0 0 8pt 0;">
    Factores Extralaborales - Forma ' . $forma . ' (' . $tipoTrabajador . ')
</p>

<!-- ELEMENTO 2: Caja de definición -->
<div style="background-color: #f9f9f9; border: 1pt solid #ddd; padding: 6pt; margin-bottom: 8pt;">
    <p style="font-weight: bold; color: ' . $this->themeColor . '; margin: 0 0 3pt 0; font-size: 8pt;">Definición:</p>
    <p style="font-size: 8pt; margin: 0; text-align: justify;">' . esc($dimension['definicion']) . '</p>
</div>

<!-- ELEMENTO 3: Gauge centrado -->
<div style="text-align: center; margin: 5pt 0;">
    <img src="' . $gaugeUri . '" style="width: 160pt; height: auto;" />

    <!-- ELEMENTO 4: Leyenda de convenciones -->
    <div style="font-size: 6pt; color: #666; margin: 2pt 0; line-height: 1.2;">
        SR=Sin Riesgo | RB=Riesgo Bajo | RM=Riesgo Medio | RA=Riesgo Alto | RMA=Riesgo Muy Alto
    </div>
</div>

<!-- ELEMENTO 5: Tabla de baremos -->
<table style="width: 100%; font-size: 7pt; border-collapse: collapse; margin: 5pt 0;">
    <tr>
        <td style="background: #4CAF50; color: white; text-align: center; padding: 2pt; border: 1pt solid #ccc;">Sin Riesgo</td>
        <td style="background: #8BC34A; color: white; text-align: center; padding: 2pt; border: 1pt solid #ccc;">Bajo</td>
        <td style="background: #FFEB3B; color: #333; text-align: center; padding: 2pt; border: 1pt solid #ccc;">Medio</td>
        <td style="background: #FF9800; color: white; text-align: center; padding: 2pt; border: 1pt solid #ccc;">Alto</td>
        <td style="background: #F44336; color: white; text-align: center; padding: 2pt; border: 1pt solid #ccc;">Muy Alto</td>
    </tr>
    <tr>
        <td style="background: #E8F5E9; text-align: center; padding: 2pt; border: 1pt solid #ccc; font-size: 6pt;">' . $baremo['sin_riesgo'][0] . '-' . $baremo['sin_riesgo'][1] . '</td>
        <td style="background: #F1F8E9; text-align: center; padding: 2pt; border: 1pt solid #ccc; font-size: 6pt;">' . $baremo['riesgo_bajo'][0] . '-' . $baremo['riesgo_bajo'][1] . '</td>
        <td style="background: #FFFDE7; text-align: center; padding: 2pt; border: 1pt solid #ccc; font-size: 6pt;">' . $baremo['riesgo_medio'][0] . '-' . $baremo['riesgo_medio'][1] . '</td>
        <td style="background: #FFF3E0; text-align: center; padding: 2pt; border: 1pt solid #ccc; font-size: 6pt;">' . $baremo['riesgo_alto'][0] . '-' . $baremo['riesgo_alto'][1] . '</td>
        <td style="background: #FFEBEE; text-align: center; padding: 2pt; border: 1pt solid #ccc; font-size: 6pt;">' . $baremo['riesgo_muy_alto'][0] . '-' . $baremo['riesgo_muy_alto'][1] . '</td>
    </tr>
</table>

<!-- ELEMENTO 6: Texto interpretativo -->
<p style="font-size: 8pt; text-align: justify; margin: 8pt 0;">
    Para el cuestionario Tipo <strong>' . $forma . '</strong> se evidencia que el nivel de riesgo psicosocial
    extralaboral se encuentra con un valor de <strong>' . number_format($promedio, 2) . '</strong> denominándose
    <span style="background-color: ' . $nivelColor . '; color: ' . ($nivel === 'riesgo_medio' ? '#333' : '#fff') . '; padding: 1pt 4pt; font-weight: bold;">' . strtoupper($nivelNombre) . '</span>,
    por lo que se debe <strong>' . $verboAccion . '</strong> las intervenciones que se realicen para los
    cargos <strong>' . $tipoTrabajador . '</strong>.
</p>

<!-- ELEMENTO 7: Título subsección distribución -->
<p style="font-size: 9pt; font-weight: bold; color: ' . $this->themeColor . '; margin: 8pt 0 4pt 0;">Porcentajes de distribución por Niveles de Riesgo:</p>

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
        <td style="border: none; text-align: right; color: #4CAF50;">■ Bajo + Sin Riesgo: ' . $pctBajo . '% (' . $countBajo . ')</td>
    </tr>
</table>

<!-- ELEMENTO 9: Texto de distribución con íconos -->
<p style="font-size: 8pt; text-align: justify; margin: 5pt 0;">
    Se evidencia que el <strong style="color: #F44336;">' . $pctAlto . '%</strong> de los encuestados con el cuestionario Tipo ' . $forma . '
    están en un nivel de riesgo Alto y Muy Alto, el siguiente <strong style="color: #DAA520;">' . $pctMedio . '%</strong> restante
    están en un nivel de riesgo Medio y el <strong style="color: #4CAF50;">' . $pctBajo . '%</strong> restante está en un riesgo Bajo y Sin Riesgo.
</p>

<!-- ELEMENTO 10: Caja de foco objetivo -->
<div style="border: 1pt solid ' . $this->themeColor . '; background-color: ' . $this->themeColorLight . '; padding: 5pt 7pt; margin-top: 6pt;">
    <p style="font-size: 8pt; margin: 0;">
        <span style="font-weight: bold; color: ' . $this->themeColor . ';">Foco Objetivo:</span>
        Cargos ' . $tipoTrabajador . '<br>
        <span style="font-weight: bold; color: ' . $this->themeColor . ';">Acción:</span> ' . $accionNivel . '
    </p>
</div>';

        // ELEMENTO 11: Tabla de áreas con riesgo alto y muy alto
        $areasRiesgo = $this->getAreasEnRiesgoAlto($results, $dimension['campo_nivel']);
        if (!empty($areasRiesgo)) {
            $html .= '
<p style="font-size: 8pt; font-weight: bold; color: ' . $this->themeColor . '; margin: 8pt 0 3pt 0;">Áreas con Riesgo Alto y Muy Alto:</p>
<table style="width: 100%; font-size: 7pt; border-collapse: collapse;">
    <tr>
        <th style="background: ' . $this->themeColor . '; color: white; padding: 3pt; border: 1pt solid #333;">Área</th>
        <th style="background: ' . $this->themeColor . '; color: white; padding: 3pt; border: 1pt solid #333;">Cargo</th>
        <th style="background: ' . $this->themeColor . '; color: white; padding: 3pt; border: 1pt solid #333; width: 50pt;">Participantes</th>
        <th style="background: ' . $this->themeColor . '; color: white; padding: 3pt; border: 1pt solid #333; width: 60pt;">Nivel</th>
    </tr>';

            $maxRows = min(count($areasRiesgo), 5); // Máximo 5 filas para caber en la página
            for ($i = 0; $i < $maxRows; $i++) {
                $area = $areasRiesgo[$i];
                $nivelAreaColor = $this->getRiskColor($area['nivel']);
                $html .= '
    <tr>
        <td style="padding: 2pt; border: 1pt solid #ccc;">' . esc($area['area']) . '</td>
        <td style="padding: 2pt; border: 1pt solid #ccc;">' . esc($area['cargo']) . '</td>
        <td style="padding: 2pt; border: 1pt solid #ccc; text-align: center;">' . $area['count'] . '</td>
        <td style="padding: 2pt; border: 1pt solid #ccc; text-align: center; background-color: ' . $nivelAreaColor . '; color: white;">' . $this->getRiskName($area['nivel']) . '</td>
    </tr>';
            }

            $html .= '</table>';
        }

        // ELEMENTO 12: Texto IA (si existe)
        $textoIA = $this->getTextoIA($dimensionCodigo, $forma);
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
    protected function renderDimensionSinDatos($dimension, $forma)
    {
        $tipoTrabajador = ($forma === 'A') ? 'Jefes, Profesionales y Técnicos' : 'Auxiliares y Operarios';
        $colorBorde = ($forma === 'A') ? $this->themeColor : '#FF6600';

        return '
<h1 style="font-size: 13pt; color: ' . $this->themeColor . '; margin: 0 0 3pt 0; padding-bottom: 4pt; border-bottom: 2pt solid ' . $colorBorde . ';">
    Dimensión: ' . esc($dimension['nombre']) . '
</h1>
<p style="font-size: 9pt; color: #666; text-align: center; margin: 0 0 15pt 0;">
    Factores Extralaborales - Forma ' . $forma . ' (' . $tipoTrabajador . ')
</p>

<div style="background-color: #f5f5f5; border: 1pt solid #ddd; padding: 20pt; text-align: center; margin: 40pt 0;">
    <p style="font-size: 12pt; color: #666; margin: 0;">
        No hay datos disponibles para esta dimensión<br>
        en el cuestionario Forma ' . $forma . '
    </p>
</div>';
    }

    /**
     * Renderiza página cuando no hay baremo disponible
     */
    protected function renderDimensionSinBaremo($dimension, $forma)
    {
        $tipoTrabajador = ($forma === 'A') ? 'Jefes, Profesionales y Técnicos' : 'Auxiliares y Operarios';

        return '
<h1 style="font-size: 13pt; color: ' . $this->themeColor . '; margin: 0 0 3pt 0; padding-bottom: 4pt; border-bottom: 2pt solid #cc0000;">
    Dimensión: ' . esc($dimension['nombre']) . '
</h1>
<p style="font-size: 9pt; color: #666; text-align: center; margin: 0 0 15pt 0;">
    Factores Extralaborales - Forma ' . $forma . ' (' . $tipoTrabajador . ')
</p>

<div style="background-color: #ffebee; border: 1pt solid #f44336; padding: 20pt; text-align: center; margin: 40pt 0;">
    <p style="font-size: 12pt; color: #c62828; margin: 0;">
        No se encontró baremo para esta dimensión en Forma ' . $forma . '
    </p>
</div>';
    }

    /**
     * Obtiene áreas/cargos en riesgo alto y muy alto
     */
    protected function getAreasEnRiesgoAlto($results, $campoNivel)
    {
        $areasRiesgo = [];

        foreach ($results as $result) {
            $nivel = $result[$campoNivel] ?? '';
            if ($nivel === 'riesgo_alto' || $nivel === 'riesgo_muy_alto') {
                $key = ($result['area'] ?? 'Sin área') . '|' . ($result['position'] ?? 'Sin cargo') . '|' . $nivel;
                if (!isset($areasRiesgo[$key])) {
                    $areasRiesgo[$key] = [
                        'area' => $result['area'] ?? 'Sin área',
                        'cargo' => $result['position'] ?? 'Sin cargo',
                        'nivel' => $nivel,
                        'count' => 0
                    ];
                }
                $areasRiesgo[$key]['count']++;
            }
        }

        // Ordenar por count descendente
        usort($areasRiesgo, function($a, $b) {
            return $b['count'] - $a['count'];
        });

        return array_values($areasRiesgo);
    }

    /**
     * Obtiene verbo de acción según nivel de riesgo
     */
    protected function getVerboAccion($nivel)
    {
        $verbos = [
            'sin_riesgo'      => 'mantener',
            'riesgo_bajo'     => 'continuar',
            'riesgo_medio'    => 'reforzar',
            'riesgo_alto'     => 'priorizar',
            'riesgo_muy_alto' => 'implementar inmediatamente',
        ];
        return $verbos[$nivel] ?? 'evaluar';
    }

    /**
     * Obtiene texto de análisis IA si existe en report_sections
     */
    protected function getTextoIA($dimensionCodigo, $forma)
    {
        $db = \Config\Database::connect();

        // Primero obtener el report_id para este battery_service
        $reportQuery = $db->query("
            SELECT id FROM reports WHERE battery_service_id = ? LIMIT 1
        ", [$this->batteryServiceId]);
        $report = $reportQuery->getRowArray();

        if (!$report) {
            return null;
        }

        // Buscar el texto IA para esta dimensión extralaboral
        $query = $db->query("
            SELECT ai_generated_text, consultant_comment
            FROM report_sections
            WHERE report_id = ?
            AND section_level = 'dimension'
            AND dimension_code = ?
            AND form_type = ?
            AND questionnaire_type = 'extralaboral'
            LIMIT 1
        ", [$report['id'], $dimensionCodigo, $forma]);

        $result = $query->getRowArray();

        // Preferir comentario del consultor, si no, texto IA
        if (!empty($result['consultant_comment'])) {
            return $result['consultant_comment'];
        }
        return $result['ai_generated_text'] ?? null;
    }
}
