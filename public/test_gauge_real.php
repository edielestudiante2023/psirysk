<?php
/**
 * PRUEBA COMPLETA: PDF con DomPDF + Gauge + Datos Reales
 *
 * Este archivo es la PRUEBA DE CONCEPTO que demuestra:
 * 1. Gauge SVG renderizado correctamente en DomPDF
 * 2. Datos reales desde calculated_results
 * 3. Baremos oficiales aplicados
 * 4. Textos IA desde report_sections
 *
 * URLs:
 * - Preview: http://localhost/psyrisk/public/test_gauge_real.php?battery_id=1
 * - PDF: http://localhost/psyrisk/public/test_gauge_real.php?battery_id=1&download=1
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// ============================================================
// CONFIGURACIÓN DE BASE DE DATOS
// ============================================================
$dbHost = 'localhost';
$dbName = 'psyrisk';
$dbUser = 'root';
$dbPass = '';

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// ============================================================
// PARÁMETROS
// ============================================================
$batteryServiceId = isset($_GET['battery_id']) ? (int)$_GET['battery_id'] : 1;
$dominio = isset($_GET['dominio']) ? $_GET['dominio'] : 'liderazgo';
$forma = isset($_GET['forma']) ? strtoupper($_GET['forma']) : 'A';

// ============================================================
// BAREMOS OFICIALES
// ============================================================
$baremosDominios = [
    'A' => [
        'liderazgo' => ['sin_riesgo' => [0.0, 9.1], 'riesgo_bajo' => [9.2, 17.7], 'riesgo_medio' => [17.8, 25.6], 'riesgo_alto' => [25.7, 34.8], 'riesgo_muy_alto' => [34.9, 100.0]],
        'control' => ['sin_riesgo' => [0.0, 10.7], 'riesgo_bajo' => [10.8, 19.0], 'riesgo_medio' => [19.1, 29.8], 'riesgo_alto' => [29.9, 40.5], 'riesgo_muy_alto' => [40.6, 100.0]],
        'demandas' => ['sin_riesgo' => [0.0, 28.5], 'riesgo_bajo' => [28.6, 35.0], 'riesgo_medio' => [35.1, 41.5], 'riesgo_alto' => [41.6, 47.5], 'riesgo_muy_alto' => [47.6, 100.0]],
        'recompensas' => ['sin_riesgo' => [0.0, 4.5], 'riesgo_bajo' => [4.6, 11.4], 'riesgo_medio' => [11.5, 20.5], 'riesgo_alto' => [20.6, 29.5], 'riesgo_muy_alto' => [29.6, 100.0]],
    ],
    'B' => [
        'liderazgo' => ['sin_riesgo' => [0.0, 8.3], 'riesgo_bajo' => [8.4, 17.5], 'riesgo_medio' => [17.6, 26.7], 'riesgo_alto' => [26.8, 38.3], 'riesgo_muy_alto' => [38.4, 100.0]],
        'control' => ['sin_riesgo' => [0.0, 19.4], 'riesgo_bajo' => [19.5, 26.4], 'riesgo_medio' => [26.5, 34.7], 'riesgo_alto' => [34.8, 43.1], 'riesgo_muy_alto' => [43.2, 100.0]],
        'demandas' => ['sin_riesgo' => [0.0, 26.9], 'riesgo_bajo' => [27.0, 33.3], 'riesgo_medio' => [33.4, 37.8], 'riesgo_alto' => [37.9, 44.2], 'riesgo_muy_alto' => [44.3, 100.0]],
        'recompensas' => ['sin_riesgo' => [0.0, 2.5], 'riesgo_bajo' => [2.6, 10.0], 'riesgo_medio' => [10.1, 17.5], 'riesgo_alto' => [17.6, 27.5], 'riesgo_muy_alto' => [27.6, 100.0]],
    ],
];

$dominiosInfo = [
    'liderazgo' => [
        'nombre' => 'Liderazgo y Relaciones Sociales en el Trabajo',
        'definicion' => 'Se refiere al tipo de relación social que se establece entre los superiores jerárquicos y sus colaboradores y cuyas características influyen en la forma de trabajar y en el ambiente de relaciones de un área.',
        'campo_puntaje' => 'dom_liderazgo_puntaje',
        'campo_nivel' => 'dom_liderazgo_nivel',
        'dimensiones' => ['Características del Liderazgo', 'Relaciones Sociales en el Trabajo', 'Retroalimentación del Desempeño', 'Relación con los Colaboradores'],
    ],
    'control' => [
        'nombre' => 'Control sobre el Trabajo',
        'definicion' => 'Posibilidad que el trabajo ofrece al individuo para influir y tomar decisiones sobre los diversos aspectos que intervienen en su realización.',
        'campo_puntaje' => 'dom_control_puntaje',
        'campo_nivel' => 'dom_control_nivel',
        'dimensiones' => ['Claridad del Rol', 'Capacitación', 'Participación y Manejo del Cambio', 'Oportunidades de Desarrollo', 'Control y Autonomía sobre el Trabajo'],
    ],
    'demandas' => [
        'nombre' => 'Demandas del Trabajo',
        'definicion' => 'Se refieren a las exigencias que el trabajo impone al individuo. Pueden ser de diversa naturaleza, como cuantitativas, cognitivas o mentales, emocionales, de responsabilidad, del ambiente físico laboral y de la jornada de trabajo.',
        'campo_puntaje' => 'dom_demandas_puntaje',
        'campo_nivel' => 'dom_demandas_nivel',
        'dimensiones' => ['Demandas Cuantitativas', 'Demandas de Carga Mental', 'Demandas Emocionales', 'Exigencias de Responsabilidad', 'Demandas Ambientales', 'Demandas de la Jornada', 'Consistencia del Rol', 'Influencia del Ambiente Laboral'],
    ],
    'recompensas' => [
        'nombre' => 'Recompensas',
        'definicion' => 'Este término trata de la retribución que el trabajador obtiene a cambio de sus contribuciones o esfuerzos laborales.',
        'campo_puntaje' => 'dom_recompensas_puntaje',
        'campo_nivel' => 'dom_recompensas_nivel',
        'dimensiones' => ['Recompensas derivadas de la pertenencia', 'Reconocimiento y Compensación'],
    ],
];

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

$riskActions = [
    'sin_riesgo' => 'Mantener condiciones actuales',
    'riesgo_bajo' => 'Acciones preventivas de mantenimiento',
    'riesgo_medio' => 'Observación y acciones preventivas',
    'riesgo_alto' => 'Intervención en marco de vigilancia epidemiológica',
    'riesgo_muy_alto' => 'Intervención inmediata en marco de vigilancia epidemiológica',
];

// ============================================================
// FUNCIONES
// ============================================================

/**
 * Genera un gauge SVG con segmentos DINÁMICOS según el baremo real
 * CON ETIQUETAS DE DEBUG para verificar datos
 *
 * El semicírculo va de IZQUIERDA (0) a DERECHA (100)
 * - Verde (sin_riesgo) a la izquierda
 * - Rojo (muy_alto) a la derecha
 */
function generateGaugeSvg($value, $baremo) {
    $riskColors = [
        'sin_riesgo' => '#4CAF50',
        'riesgo_bajo' => '#8BC34A',
        'riesgo_medio' => '#FFEB3B',
        'riesgo_alto' => '#FF9800',
        'riesgo_muy_alto' => '#F44336',
    ];

    $riskLabelsShort = [
        'sin_riesgo' => 'SR',
        'riesgo_bajo' => 'RB',
        'riesgo_medio' => 'RM',
        'riesgo_alto' => 'RA',
        'riesgo_muy_alto' => 'RMA',
    ];

    // Determinar nivel actual
    $nivel = 'sin_riesgo';
    foreach ($baremo as $niv => $rango) {
        if ($value >= $rango[0] && $value <= $rango[1]) {
            $nivel = $niv;
            break;
        }
    }

    $color = $riskColors[$nivel] ?? '#999999';

    // Parámetros del gauge
    $cx = 100;      // Centro X
    $cy = 90;       // Centro Y
    $radius = 70;   // Radio del arco
    $labelRadius = 50; // Radio para las etiquetas (más cerca del centro)

    // Calcular ángulo de la aguja
    $percentage = min(100, max(0, $value));
    $needleAngleDeg = 180 - ($percentage * 1.8);
    $needleAngleRad = deg2rad($needleAngleDeg);
    $needleLength = $radius - 10;
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
    $levels = ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'];
    $pathsSvg = '';
    $labelsSvg = '';

    foreach ($levels as $lvl) {
        if (!isset($baremo[$lvl])) continue;

        $startPct = $baremo[$lvl][0];
        $endPct = min(100, $baremo[$lvl][1]);

        $p1 = $getPoint($startPct);
        $p2 = $getPoint($endPct);

        // Arco del segmento
        $pathsSvg .= '<path d="M ' . $p1[0] . ' ' . $p1[1] . ' A ' . $radius . ' ' . $radius . ' 0 0 1 ' . $p2[0] . ' ' . $p2[1] . '" fill="none" stroke="' . $riskColors[$lvl] . '" stroke-width="12"/>' . "\n    ";

        // Etiqueta en el centro del segmento (fuente reducida a la mitad)
        $midPct = ($startPct + $endPct) / 2;
        $labelPos = $getPoint($midPct, $labelRadius);
        $labelsSvg .= '<text x="' . $labelPos[0] . '" y="' . $labelPos[1] . '" font-family="Arial" font-size="5" text-anchor="middle" fill="#333">' . $riskLabelsShort[$lvl] . '</text>' . "\n    ";
        // Rango del baremo debajo de la etiqueta
        $labelsSvg .= '<text x="' . $labelPos[0] . '" y="' . ($labelPos[1] + 5) . '" font-family="Arial" font-size="4" text-anchor="middle" fill="#666">' . $startPct . '-' . $endPct . '</text>' . "\n    ";
    }

    // Etiquetas de extremos (0 y 100) - fuente reducida
    $labelsSvg .= '<text x="28" y="95" font-family="Arial" font-size="5" fill="#333">0</text>' . "\n    ";
    $labelsSvg .= '<text x="168" y="95" font-family="Arial" font-size="5" fill="#333">100</text>' . "\n    ";

    $svg = '<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="200" height="120" viewBox="0 0 200 120">
    <!-- Fondo del arco -->
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
          text-anchor="middle" fill="' . $color . '">' . number_format($value, 1) . '</text>
</svg>';

    return 'data:image/svg+xml;base64,' . base64_encode($svg);
}

function getNivelFromPuntaje($puntaje, $baremo) {
    foreach ($baremo as $nivel => $rango) {
        if ($puntaje >= $rango[0] && $puntaje <= $rango[1]) {
            return $nivel;
        }
    }
    return 'sin_riesgo';
}

function calculateDistribution($results, $campoNivel) {
    $distribucion = [
        'sin_riesgo' => 0,
        'riesgo_bajo' => 0,
        'riesgo_medio' => 0,
        'riesgo_alto' => 0,
        'riesgo_muy_alto' => 0,
    ];

    foreach ($results as $result) {
        $nivel = $result[$campoNivel] ?? '';
        if (isset($distribucion[$nivel])) {
            $distribucion[$nivel]++;
        }
    }

    return $distribucion;
}

// ============================================================
// OBTENER DATOS DE EMPRESA
// ============================================================
$stmtCompany = $pdo->prepare("
    SELECT c.name as company_name, c.nit, c.city
    FROM battery_services bs
    JOIN companies c ON bs.company_id = c.id
    WHERE bs.id = ?
");
$stmtCompany->execute([$batteryServiceId]);
$company = $stmtCompany->fetch(PDO::FETCH_ASSOC) ?: ['company_name' => 'Empresa', 'nit' => '', 'city' => ''];

// ============================================================
// OBTENER RESULTADOS CALCULADOS
// ============================================================
$dominioInfo = $dominiosInfo[$dominio];
$baremo = $baremosDominios[$forma][$dominio];

$stmtResults = $pdo->prepare("
    SELECT * FROM calculated_results
    WHERE battery_service_id = ? AND intralaboral_form_type = ?
");
$stmtResults->execute([$batteryServiceId, $forma]);
$results = $stmtResults->fetchAll(PDO::FETCH_ASSOC);

// Calcular promedio
$campoPuntaje = $dominioInfo['campo_puntaje'];
$campoNivel = $dominioInfo['campo_nivel'];

$puntajes = array_filter(array_column($results, $campoPuntaje), function($v) {
    return $v !== null && $v !== '';
});

$promedio = !empty($puntajes) ? array_sum($puntajes) / count($puntajes) : 0;
$nivel = getNivelFromPuntaje($promedio, $baremo);
$totalEvaluados = count($results);

// Distribución
$distribucion = calculateDistribution($results, $campoNivel);
$porcentajes = [];
foreach ($distribucion as $niv => $count) {
    $porcentajes[$niv] = $totalEvaluados > 0 ? round(($count / $totalEvaluados) * 100, 1) : 0;
}

$pctAlto = ($porcentajes['riesgo_alto'] ?? 0) + ($porcentajes['riesgo_muy_alto'] ?? 0);
$pctMedio = $porcentajes['riesgo_medio'] ?? 0;
$pctBajo = ($porcentajes['sin_riesgo'] ?? 0) + ($porcentajes['riesgo_bajo'] ?? 0);

// ============================================================
// OBTENER TEXTO IA
// ============================================================
$stmtAI = $pdo->prepare("
    SELECT ai_generated_text FROM report_sections
    WHERE report_id IN (SELECT id FROM reports WHERE battery_service_id = ?)
    AND questionnaire_type = 'intralaboral'
    AND section_level = 'domain'
    AND domain_code = ?
    AND form_type = ?
    LIMIT 1
");
$stmtAI->execute([$batteryServiceId, $dominio, $forma]);
$aiRow = $stmtAI->fetch(PDO::FETCH_ASSOC);
$textoIA = $aiRow['ai_generated_text'] ?? '';

// ============================================================
// GENERAR HTML
// ============================================================
$color = $riskColors[$nivel];
$label = $riskLabels[$nivel];
$action = $riskActions[$nivel];
$gaugeImage = generateGaugeSvg($promedio, $baremo);
$tipoTrabajadores = $forma === 'A' ? 'Jefes, Profesionales y Técnicos' : 'Auxiliares y Operarios';

$html = '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dominio ' . htmlspecialchars($dominioInfo['nombre']) . '</title>
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

        .header-table {
            width: 100%;
            border-bottom: 2px solid #006699;
            margin-bottom: 10px;
        }

        .header-table td {
            padding: 5px;
            font-size: 9pt;
        }

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

        .definition-box {
            border: 1px solid #ccc;
            background-color: #f9f9f9;
            padding: 8px;
            margin-bottom: 10px;
            font-size: 9pt;
        }

        .definition-label {
            font-weight: bold;
            color: #006699;
            margin-bottom: 3px;
        }

        .layout-table {
            width: 100%;
            border-collapse: collapse;
        }

        .layout-table td {
            vertical-align: top;
            padding: 5px;
        }

        .gauge-cell {
            text-align: center;
            width: 35%;
        }

        .gauge-img {
            max-width: 160px;
        }

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

        .interpretation {
            font-size: 9pt;
            text-align: justify;
        }

        .risk-label {
            font-weight: bold;
            padding: 2px 6px;
            color: white;
        }

        .dimensions-box {
            background-color: #f5f5f5;
            padding: 8px;
            margin-top: 10px;
            font-size: 8pt;
        }

        .dimensions-title {
            font-weight: bold;
            color: #006699;
            margin-bottom: 5px;
        }

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
            vertical-align: middle;
            padding-top: 0;
            padding-bottom: 0;
            box-sizing: border-box;
        }

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

        .ai-box {
            border-left: 3px solid #2196F3;
            background-color: #e3f2fd;
            padding: 10px;
            margin-top: 12px;
            font-size: 8pt;
            text-align: justify;
        }

        .ai-title {
            font-weight: bold;
            color: #1976D2;
            margin-bottom: 5px;
        }

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
        <td width="30%">' . htmlspecialchars($company['company_name']) . '</td>
        <td width="40%" style="text-align: center;"><strong>Batería de Riesgo Psicosocial</strong></td>
        <td width="30%" style="text-align: right;">Forma ' . $forma . '</td>
    </tr>
</table>

<!-- Título -->
<div class="title-main">Dominio: ' . htmlspecialchars($dominioInfo['nombre']) . '</div>
<div class="title-sub">' . $tipoTrabajadores . '</div>

<!-- Definición -->
<div class="definition-box">
    <div class="definition-label">Definición:</div>
    <div>' . htmlspecialchars($dominioInfo['definicion']) . '</div>
</div>

<!-- Layout principal -->
<table class="layout-table">
    <tr>
        <td class="gauge-cell">
            <img src="' . $gaugeImage . '" class="gauge-img" alt="Gauge">

            <!-- Leyenda de abreviaturas del gauge -->
            <div style="font-size: 6pt; color: #666; margin: 3px 0; line-height: 1.3;">
                SR=Sin Riesgo | RB=Riesgo Bajo | RM=Riesgo Medio<br>
                RA=Riesgo Alto | RMA=Riesgo Muy Alto
            </div>

            <table class="baremo-table">
                <tr>
                    <td class="bg-verde">Sin Riesgo</td>
                    <td class="bg-verde-claro">Bajo</td>
                    <td class="bg-amarillo">Medio</td>
                    <td class="bg-naranja">Alto</td>
                    <td class="bg-rojo">Muy Alto</td>
                </tr>
                <tr>
                    <td style="background:#E8F5E9;">' . $baremo['sin_riesgo'][0] . ' - ' . $baremo['sin_riesgo'][1] . '</td>
                    <td style="background:#F1F8E9;">' . $baremo['riesgo_bajo'][0] . ' - ' . $baremo['riesgo_bajo'][1] . '</td>
                    <td style="background:#FFFDE7;">' . $baremo['riesgo_medio'][0] . ' - ' . $baremo['riesgo_medio'][1] . '</td>
                    <td style="background:#FFF3E0;">' . $baremo['riesgo_alto'][0] . ' - ' . $baremo['riesgo_alto'][1] . '</td>
                    <td style="background:#FFEBEE;">' . $baremo['riesgo_muy_alto'][0] . ' - ' . $baremo['riesgo_muy_alto'][1] . '</td>
                </tr>
            </table>
        </td>

        <td style="width: 65%; padding-left: 15px;">
            <div class="interpretation">
                <p>
                    El dominio <strong>' . htmlspecialchars($dominioInfo['nombre']) . '</strong>
                    presenta un puntaje promedio de <strong>' . number_format($promedio, 2, ',', '.') . '</strong>,
                    clasificándose como
                    <span class="risk-label" style="background-color: ' . $color . '; color: ' . ($nivel == 'riesgo_medio' ? '#333' : 'white') . ';">
                        ' . $label . '
                    </span>.
                </p>
                <p>
                    Se evaluaron <strong>' . $totalEvaluados . '</strong> trabajadores en este dominio.
                </p>
            </div>

            <div class="dimensions-box">
                <div class="dimensions-title">Dimensiones que componen este dominio:</div>
                <ul style="margin: 0; padding-left: 15px;">
                    ' . implode('', array_map(function($dim) {
                        return '<li>' . htmlspecialchars($dim) . '</li>';
                    }, $dominioInfo['dimensiones'])) . '
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
            <div class="bar-container clearfix">';

if ($pctAlto > 0) {
    $html .= '<div class="bar-segment" style="width: ' . $pctAlto . '%; background-color: #F44336;">' . ($pctAlto > 10 ? round($pctAlto) . '%' : '') . '</div>';
}
if ($pctMedio > 0) {
    $html .= '<div class="bar-segment" style="width: ' . $pctMedio . '%; background-color: #FFEB3B; color: #333;">' . ($pctMedio > 10 ? round($pctMedio) . '%' : '') . '</div>';
}
if ($pctBajo > 0) {
    $html .= '<div class="bar-segment" style="width: ' . $pctBajo . '%; background-color: #4CAF50;">' . ($pctBajo > 10 ? round($pctBajo) . '%' : '') . '</div>';
}

$html .= '</div>

            <table style="width: 100%; font-size: 7pt; margin-top: 5px; border: none;">
                <tr>
                    <td style="border: none;"><span style="display:inline-block; width:10px; height:10px; background:#F44336;"></span> Alto/Muy Alto: ' . round($pctAlto, 1) . '%</td>
                    <td style="border: none;"><span style="display:inline-block; width:10px; height:10px; background:#FFEB3B;"></span> Medio: ' . round($pctMedio, 1) . '%</td>
                    <td style="border: none;"><span style="display:inline-block; width:10px; height:10px; background:#4CAF50;"></span> Sin/Bajo: ' . round($pctBajo, 1) . '%</td>
                </tr>
            </table>
        </td>
        <td width="40%" style="padding-left: 15px;">
            <div style="font-size: 9pt;">
                <strong style="color: #F44336;">' . ($distribucion['riesgo_alto'] + $distribucion['riesgo_muy_alto']) . '</strong> personas en riesgo alto/muy alto<br>
                <strong style="color: #F9A825;">' . $distribucion['riesgo_medio'] . '</strong> personas en riesgo medio<br>
                <strong style="color: #4CAF50;">' . ($distribucion['sin_riesgo'] + $distribucion['riesgo_bajo']) . '</strong> personas sin riesgo/riesgo bajo
            </div>
        </td>
    </tr>
</table>

<!-- Acción recomendada -->
<div class="focus-box">
    <div class="focus-title">Acción Recomendada:</div>
    <div>' . $action . ' según Resolución 2404/2019.</div>
</div>';

// Texto IA si existe
if (!empty($textoIA)) {
    $html .= '
<div class="ai-box">
    <div class="ai-title">Análisis del Dominio:</div>
    <div>' . nl2br(htmlspecialchars($textoIA)) . '</div>
</div>';
}

$html .= '

<!-- Footer -->
<div class="footer">
    Informe generado por PSYRISK - Batería de Riesgo Psicosocial
</div>

</body>
</html>';

// ============================================================
// GENERAR PDF O MOSTRAR PREVIEW
// ============================================================
if (isset($_GET['download']) && $_GET['download'] == '1') {
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);
    $options->set('defaultFont', 'DejaVu Sans');
    $options->set('isFontSubsettingEnabled', true);

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('Letter', 'portrait');
    $dompdf->render();

    $filename = 'dominio_' . $dominio . '_forma_' . $forma . '.pdf';
    $dompdf->stream($filename, ['Attachment' => true]);
} else {
    echo $html;
    echo '<div style="position: fixed; bottom: 20px; right: 20px; background: #006699; color: white; padding: 15px 25px; border-radius: 5px; font-family: Arial; box-shadow: 0 2px 10px rgba(0,0,0,0.3);">';
    echo '<a href="?battery_id=' . $batteryServiceId . '&dominio=' . $dominio . '&forma=' . $forma . '&download=1" style="color: white; text-decoration: none; font-weight: bold;">📥 DESCARGAR PDF</a>';
    echo '</div>';

    // Panel de navegación
    echo '<div style="position: fixed; top: 20px; right: 20px; background: white; padding: 15px; border-radius: 5px; font-family: Arial; box-shadow: 0 2px 10px rgba(0,0,0,0.3); font-size: 12px;">';
    echo '<strong>Probar otros dominios:</strong><br><br>';
    foreach ($dominiosInfo as $key => $info) {
        echo '<a href="?battery_id=' . $batteryServiceId . '&dominio=' . $key . '&forma=A">' . $key . ' (A)</a> | ';
        echo '<a href="?battery_id=' . $batteryServiceId . '&dominio=' . $key . '&forma=B">' . $key . ' (B)</a><br>';
    }
    echo '</div>';
}
