<?php
/**
 * PRUEBA: Generar PDF con DomPDF y Gauge SVG
 *
 * Este archivo demuestra que DomPDF SÍ puede renderizar gauges SVG
 * cuando se diseñan correctamente.
 *
 * URL: http://localhost/psyrisk/public/test_gauge_dompdf.php
 * URL: http://localhost/psyrisk/public/test_gauge_dompdf.php?download=1
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// ============================================================
// FUNCIÓN: Generar Gauge SVG (copiada de PdfGaugeGenerator.php)
// ============================================================
function generateGaugeSvg($value, $baremo) {
    // Determinar nivel
    $nivel = 'sin_riesgo';
    foreach ($baremo as $niv => $rango) {
        if ($value >= $rango[0] && $value <= $rango[1]) {
            $nivel = $niv;
            break;
        }
    }

    $riskColors = [
        'sin_riesgo' => '#4CAF50',
        'riesgo_bajo' => '#8BC34A',
        'riesgo_medio' => '#FFEB3B',
        'riesgo_alto' => '#FF9800',
        'riesgo_muy_alto' => '#F44336',
    ];

    $color = $riskColors[$nivel] ?? '#999999';

    // Calcular porcentaje (0-100)
    $percentage = min(100, max(0, $value));

    // Calcular ángulo de la aguja (-90 a 90 grados)
    $angle = -90 + ($percentage * 1.8);

    // Centro del gauge
    $cx = 100;
    $cy = 90;
    $radius = 70;

    // Calcular posición de la punta de la aguja
    $radians = deg2rad($angle);
    $needleX = $cx + (($radius - 10) * cos($radians));
    $needleY = $cy + (($radius - 10) * sin($radians));

    $svg = '<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="200" height="120" viewBox="0 0 200 120">
    <!-- Fondo del arco segmentado -->
    <path d="M 30 90 A 70 70 0 0 1 170 90" fill="none" stroke="#E8E8E8" stroke-width="12"/>

    <!-- Segmento Sin Riesgo (verde oscuro) -->
    <path d="M 30 90 A 70 70 0 0 1 50 45" fill="none" stroke="#4CAF50" stroke-width="12"/>

    <!-- Segmento Riesgo Bajo (verde claro) -->
    <path d="M 50 45 A 70 70 0 0 1 85 25" fill="none" stroke="#8BC34A" stroke-width="12"/>

    <!-- Segmento Riesgo Medio (amarillo) -->
    <path d="M 85 25 A 70 70 0 0 1 115 25" fill="none" stroke="#FFEB3B" stroke-width="12"/>

    <!-- Segmento Riesgo Alto (naranja) -->
    <path d="M 115 25 A 70 70 0 0 1 150 45" fill="none" stroke="#FF9800" stroke-width="12"/>

    <!-- Segmento Riesgo Muy Alto (rojo) -->
    <path d="M 150 45 A 70 70 0 0 1 170 90" fill="none" stroke="#F44336" stroke-width="12"/>

    <!-- Aguja -->
    <line x1="' . $cx . '" y1="' . $cy . '" x2="' . round($needleX, 2) . '" y2="' . round($needleY, 2) . '"
          stroke="#333" stroke-width="3" stroke-linecap="round"/>

    <!-- Centro de la aguja -->
    <circle cx="' . $cx . '" cy="' . $cy . '" r="8" fill="#333"/>

    <!-- Valor -->
    <text x="' . $cx . '" y="115" font-family="Arial, sans-serif" font-size="14" font-weight="bold"
          text-anchor="middle" fill="' . $color . '">' . number_format($value, 1) . '</text>
</svg>';

    return 'data:image/svg+xml;base64,' . base64_encode($svg);
}

// ============================================================
// DATOS DE PRUEBA
// ============================================================
$puntaje = 45.5; // Puntaje de ejemplo
$baremo = [
    'sin_riesgo' => [0.0, 19.7],
    'riesgo_bajo' => [19.8, 25.8],
    'riesgo_medio' => [25.9, 31.5],
    'riesgo_alto' => [31.6, 38.7],
    'riesgo_muy_alto' => [38.8, 100.0],
];

// Determinar nivel
$nivel = 'sin_riesgo';
foreach ($baremo as $niv => $rango) {
    if ($puntaje >= $rango[0] && $puntaje <= $rango[1]) {
        $nivel = $niv;
        break;
    }
}

$riskColors = [
    'sin_riesgo' => '#4CAF50',
    'riesgo_bajo' => '#8BC34A',
    'riesgo_medio' => '#FFEB3B',
    'riesgo_alto' => '#FF9800',
    'riesgo_muy_alto' => '#F44336',
];

$riskLabels = [
    'sin_riesgo' => 'SIN RIESGO',
    'riesgo_bajo' => 'RIESGO BAJO',
    'riesgo_medio' => 'RIESGO MEDIO',
    'riesgo_alto' => 'RIESGO ALTO',
    'riesgo_muy_alto' => 'RIESGO MUY ALTO',
];

$color = $riskColors[$nivel];
$label = $riskLabels[$nivel];
$gaugeImage = generateGaugeSvg($puntaje, $baremo);

// ============================================================
// HTML DEL PDF
// ============================================================
$html = '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Prueba Gauge DomPDF</title>
    <style>
        @page {
            size: Letter;
            margin: 15mm 10mm 10mm 10mm;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 0;
        }

        /* Header */
        .header-table {
            width: 100%;
            border-bottom: 2px solid #006699;
            margin-bottom: 10px;
        }

        .header-table td {
            padding: 5px;
            font-size: 9pt;
        }

        /* Títulos */
        .title-main {
            font-size: 14pt;
            font-weight: bold;
            color: #006699;
            text-align: center;
            margin: 10px 0;
        }

        .title-sub {
            font-size: 10pt;
            color: #666;
            text-align: center;
            margin-bottom: 15px;
        }

        /* Caja de definición */
        .definition-box {
            border: 1px solid #ccc;
            background-color: #f9f9f9;
            padding: 8px;
            margin-bottom: 10px;
        }

        .definition-label {
            font-weight: bold;
            color: #006699;
            margin-bottom: 3px;
        }

        /* Layout table */
        .layout-table {
            width: 100%;
            border-collapse: collapse;
        }

        .layout-table td {
            vertical-align: top;
            padding: 5px;
        }

        /* Gauge */
        .gauge-cell {
            text-align: center;
            width: 35%;
        }

        .gauge-img {
            max-width: 160px;
        }

        /* Tabla de baremos */
        .baremo-table {
            width: 100%;
            font-size: 7pt;
            border-collapse: collapse;
            margin-top: 5px;
        }

        .baremo-table td {
            border: 1px solid #ccc;
            padding: 3px;
            text-align: center;
        }

        .bg-verde { background-color: #4CAF50; color: white; }
        .bg-verde-claro { background-color: #8BC34A; color: white; }
        .bg-amarillo { background-color: #FFEB3B; color: #333; }
        .bg-naranja { background-color: #FF9800; color: white; }
        .bg-rojo { background-color: #F44336; color: white; }

        /* Interpretación */
        .interpretation {
            font-size: 9pt;
            text-align: justify;
        }

        .risk-label {
            font-weight: bold;
            padding: 2px 6px;
            color: white;
        }

        /* Distribución */
        .distribution-title {
            font-size: 11pt;
            font-weight: bold;
            color: #006699;
            margin: 15px 0 8px 0;
        }

        .bar-container {
            width: 100%;
            height: 20px;
            border: 1px solid #ccc;
        }

        .bar-segment {
            height: 20px;
            float: left;
            text-align: center;
            font-size: 7pt;
            font-weight: bold;
            color: white;
            line-height: 20px;
        }

        /* Focus box */
        .focus-box {
            border: 1px solid #006699;
            background-color: #e8f4fc;
            padding: 8px;
            margin-top: 10px;
            font-size: 8pt;
        }

        .focus-title {
            font-weight: bold;
            color: #006699;
        }

        /* Footer */
        .footer {
            border-top: 1px solid #ccc;
            padding-top: 5px;
            margin-top: 15px;
            font-size: 8pt;
            color: #666;
            text-align: center;
        }

        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
    </style>
</head>
<body>

<!-- Header -->
<table class="header-table">
    <tr>
        <td width="30%">Empresa de Prueba</td>
        <td width="40%" style="text-align: center;"><strong>Batería de Riesgo Psicosocial</strong></td>
        <td width="30%" style="text-align: right;">Forma A</td>
    </tr>
</table>

<!-- Título -->
<div class="title-main">Dominio: Liderazgo y Relaciones Sociales en el Trabajo</div>
<div class="title-sub">Jefes, Profesionales y Técnicos</div>

<!-- Definición -->
<div class="definition-box">
    <div class="definition-label">Definición:</div>
    <div>Se refiere al tipo de relación social que se establece entre los superiores jerárquicos y sus colaboradores y cuyas características influyen en la forma de trabajar y en el ambiente de relaciones de un área.</div>
</div>

<!-- Layout principal: Gauge + Interpretación -->
<table class="layout-table">
    <tr>
        <!-- Columna izquierda: Gauge -->
        <td class="gauge-cell">
            <img src="' . $gaugeImage . '" class="gauge-img" alt="Gauge">

            <!-- Tabla de baremos -->
            <table class="baremo-table">
                <tr>
                    <td class="bg-verde">Sin Riesgo</td>
                    <td class="bg-verde-claro">Bajo</td>
                    <td class="bg-amarillo">Medio</td>
                    <td class="bg-naranja">Alto</td>
                    <td class="bg-rojo">Muy Alto</td>
                </tr>
                <tr>
                    <td style="background:#E8F5E9;">0 - 19.7</td>
                    <td style="background:#F1F8E9;">19.8 - 25.8</td>
                    <td style="background:#FFFDE7;">25.9 - 31.5</td>
                    <td style="background:#FFF3E0;">31.6 - 38.7</td>
                    <td style="background:#FFEBEE;">38.8 - 100</td>
                </tr>
            </table>
        </td>

        <!-- Columna derecha: Interpretación -->
        <td style="width: 65%; padding-left: 15px;">
            <div class="interpretation">
                <p>
                    El dominio <strong>Liderazgo y Relaciones Sociales en el Trabajo</strong>
                    presenta un puntaje promedio de <strong>' . number_format($puntaje, 2, ',', '.') . '</strong>,
                    clasificándose como
                    <span class="risk-label" style="background-color: ' . $color . '; color: ' . ($nivel == 'riesgo_medio' ? '#333' : 'white') . ';">
                        ' . $label . '
                    </span>.
                </p>
                <p>
                    Se evaluaron <strong>25</strong> trabajadores en este dominio.
                </p>
            </div>

            <!-- Dimensiones -->
            <div style="background-color: #f5f5f5; padding: 8px; margin-top: 10px;">
                <div style="font-weight: bold; color: #006699; margin-bottom: 5px; font-size: 8pt;">
                    Dimensiones que componen este dominio:
                </div>
                <ul style="margin: 0; padding-left: 15px; font-size: 8pt;">
                    <li>Características del Liderazgo</li>
                    <li>Relaciones Sociales en el Trabajo</li>
                    <li>Retroalimentación del Desempeño</li>
                    <li>Relación con los Colaboradores</li>
                </ul>
            </div>
        </td>
    </tr>
</table>

<!-- Distribución -->
<div class="distribution-title">Distribución por Niveles de Riesgo</div>

<table class="layout-table">
    <tr>
        <td width="60%">
            <!-- Barra horizontal -->
            <div class="bar-container clearfix">
                <div class="bar-segment" style="width: 60%; background-color: #F44336;">60%</div>
                <div class="bar-segment" style="width: 20%; background-color: #FFEB3B; color: #333;">20%</div>
                <div class="bar-segment" style="width: 20%; background-color: #4CAF50;">20%</div>
            </div>

            <!-- Leyenda -->
            <table style="width: 100%; font-size: 7pt; margin-top: 5px; border: none;">
                <tr>
                    <td style="border: none;"><span style="display:inline-block; width:10px; height:10px; background:#F44336;"></span> Alto/Muy Alto: 60%</td>
                    <td style="border: none;"><span style="display:inline-block; width:10px; height:10px; background:#FFEB3B;"></span> Medio: 20%</td>
                    <td style="border: none;"><span style="display:inline-block; width:10px; height:10px; background:#4CAF50;"></span> Sin/Bajo: 20%</td>
                </tr>
            </table>
        </td>
        <td width="40%" style="padding-left: 15px;">
            <div style="font-size: 9pt;">
                <strong style="color: #F44336;">15</strong> personas en riesgo alto/muy alto<br>
                <strong style="color: #F9A825;">5</strong> personas en riesgo medio
            </div>
        </td>
    </tr>
</table>

<!-- Focus box -->
<div class="focus-box">
    <div class="focus-title">Acción Recomendada:</div>
    <div>Intervención inmediata en marco de vigilancia epidemiológica según Resolución 2404/2019.</div>
</div>

<!-- Footer -->
<div class="footer">
    Informe generado por PSYRISK - Batería de Riesgo Psicosocial | Prueba de Gauge DomPDF
</div>

</body>
</html>';

// ============================================================
// GENERAR PDF O MOSTRAR PREVIEW
// ============================================================
if (isset($_GET['download']) && $_GET['download'] == '1') {
    // Generar PDF con DomPDF
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);
    $options->set('defaultFont', 'DejaVu Sans');
    $options->set('isFontSubsettingEnabled', true);

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('Letter', 'portrait');
    $dompdf->render();

    // Descargar
    $dompdf->stream('test_gauge_dompdf.pdf', ['Attachment' => true]);
} else {
    // Mostrar preview en navegador
    echo $html;
    echo '<div style="position: fixed; bottom: 20px; right: 20px; background: #006699; color: white; padding: 15px 25px; border-radius: 5px; font-family: Arial;">';
    echo '<a href="?download=1" style="color: white; text-decoration: none; font-weight: bold;">📥 DESCARGAR PDF</a>';
    echo '</div>';
}
