<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dominios Intralaborales - PDF</title>
    <style>
        /* === ESTILOS OPTIMIZADOS PARA DOMPDF === */
        @page {
            size: Letter;
            margin: 15mm 10mm 10mm 10mm;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 8.5pt;
            line-height: 1.3;
            color: #333;
            margin: 0;
            padding: 0;
        }

        /* Tablas de layout */
        table {
            border-collapse: collapse;
        }

        .layout-table {
            width: 100%;
            border: none;
        }

        .layout-table td {
            vertical-align: top;
            padding: 4px;
        }

        /* Page break */
        .page-break {
            page-break-after: always;
        }

        /* Header */
        .header-table {
            width: 100%;
            border-bottom: 2px solid #006699;
            margin-bottom: 8px;
        }

        .header-table td {
            padding: 4px;
        }

        /* Títulos */
        .title-main {
            font-size: 13pt;
            font-weight: bold;
            color: #006699;
            text-align: center;
            margin: 7px 0;
        }

        .title-sub {
            font-size: 10pt;
            color: #666;
            text-align: center;
            margin-bottom: 10px;
        }

        /* Caja de definición */
        .definition-box {
            border: 1px solid #ccc;
            background-color: #f9f9f9;
            padding: 6px 7px;
            margin-bottom: 8px;
        }

        .definition-label {
            font-weight: bold;
            margin-bottom: 2px;
        }

        /* Gauge container */
        .gauge-cell {
            text-align: center;
            width: 33%;
        }

        .gauge-img {
            max-width: 150px;
        }

        /* Tabla de baremos */
        .baremo-table {
            width: 100%;
            font-size: 6.5pt;
            border-collapse: collapse;
            margin-top: 4px;
        }

        .baremo-table td {
            border: 1px solid #ccc;
            padding: 2px 3px;
            text-align: center;
        }

        .bg-verde { background-color: #4CAF50; color: white; }
        .bg-verde-claro { background-color: #8BC34A; color: white; }
        .bg-amarillo { background-color: #FFEB3B; color: #333; }
        .bg-naranja { background-color: #FF9800; color: white; }
        .bg-rojo { background-color: #F44336; color: white; }

        /* Interpretación */
        .interpretation {
            font-size: 8.5pt;
            text-align: justify;
        }

        .interpretation p {
            margin: 4px 0;
        }

        .risk-label {
            font-weight: bold;
            padding: 2px 5px;
        }

        /* Dimensiones */
        .dimensions-box {
            background-color: #f5f5f5;
            padding: 6px 7px;
            margin-top: 8px;
        }

        .dimensions-title {
            font-weight: bold;
            color: #006699;
            margin-bottom: 3px;
            font-size: 7.5pt;
        }

        .dimensions-list {
            margin: 0;
            padding-left: 14px;
            font-size: 7.5pt;
        }

        .dimensions-list li {
            margin-bottom: 1px;
        }

        /* Distribución */
        .distribution-title {
            font-size: 9.5pt;
            font-weight: bold;
            color: #006699;
            margin: 10px 0 6px 0;
        }

        /* Barra horizontal simple */
        .bar-container {
            width: 100%;
            height: 18px;
            border: 1px solid #ccc;
            margin: 4px 0;
        }

        .bar-segment {
            height: 18px;
            float: left;
            text-align: center;
            font-size: 6.5pt;
            font-weight: bold;
            color: white;
            line-height: 18px;
        }

        /* Focus box */
        .focus-box {
            border: 1px solid #006699;
            background-color: #e8f4fc;
            padding: 6px 7px;
            margin-top: 8px;
            font-size: 7.5pt;
        }

        .focus-title {
            font-weight: bold;
            color: #006699;
        }

        /* Texto IA */
        .ai-text-box {
            border-left: 2px solid #2196F3;
            background-color: #e3f2fd;
            padding: 7px 9px;
            margin-top: 9px;
            font-size: 7.5pt;
            text-align: justify;
        }

        .ai-text-title {
            font-weight: bold;
            color: #1976D2;
            margin-bottom: 4px;
        }

        /* Footer */
        .footer {
            border-top: 1px solid #ccc;
            padding-top: 4px;
            margin-top: 10px;
            font-size: 7.5pt;
            color: #666;
            text-align: center;
        }

        /* Clearfix */
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
    </style>
</head>
<body>

<?php
use App\Libraries\PdfGaugeGenerator;
$gaugeGenerator = new PdfGaugeGenerator();

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

$focusActions = [
    'sin_riesgo' => 'Mantener programas de bienestar',
    'riesgo_bajo' => 'Continuar prevención',
    'riesgo_medio' => 'Reforzar intervención',
    'riesgo_alto' => 'Intervención prioritaria',
    'riesgo_muy_alto' => 'Intervención inmediata',
];
?>

<?php foreach ($dominiosData as $index => $dominio): ?>
    <?php
    $color = $riskColors[$dominio['nivel']] ?? '#999';
    $label = $riskLabels[$dominio['nivel']] ?? 'SIN DATOS';
    $gaugeImage = $gaugeGenerator->generate($dominio['promedio'], $dominio['baremos']);

    // Calcular porcentajes agrupados
    $pctAlto = ($dominio['porcentajes']['riesgo_alto'] ?? 0) + ($dominio['porcentajes']['riesgo_muy_alto'] ?? 0);
    $pctMedio = $dominio['porcentajes']['riesgo_medio'] ?? 0;
    $pctBajo = ($dominio['porcentajes']['sin_riesgo'] ?? 0) + ($dominio['porcentajes']['riesgo_bajo'] ?? 0);
    ?>

    <!-- Header -->
    <table class="header-table">
        <tr>
            <td width="30%"><?= esc($company['company_name'] ?? 'Empresa') ?></td>
            <td width="40%" align="center"><strong>Batería de Riesgo Psicosocial</strong></td>
            <td width="30%" align="right">Forma <?= $dominio['forma'] ?></td>
        </tr>
    </table>

    <!-- Título -->
    <div class="title-main">Dominio: <?= esc($dominio['nombre']) ?></div>
    <div class="title-sub"><?= $dominio['tipo_trabajadores'] ?></div>

    <!-- Definición -->
    <div class="definition-box">
        <div class="definition-label">Definición:</div>
        <div><?= esc($dominio['definicion']) ?></div>
    </div>

    <!-- Layout principal: Gauge + Interpretación -->
    <table class="layout-table">
        <tr>
            <!-- Columna izquierda: Gauge -->
            <td class="gauge-cell">
                <img src="<?= $gaugeImage ?>" class="gauge-img" alt="Gauge">

                <!-- Tabla de baremos -->
                <table class="baremo-table">
                    <tr>
                        <td colspan="2" class="bg-verde">Sin Riesgo</td>
                        <td class="bg-verde-claro">Bajo</td>
                        <td class="bg-amarillo">Medio</td>
                        <td class="bg-naranja">Alto</td>
                        <td class="bg-rojo">Muy Alto</td>
                    </tr>
                    <tr>
                        <td style="background:#E8F5E9;">↓</td>
                        <td style="background:#E8F5E9;"><?= $dominio['baremos']['sin_riesgo'][0] ?? 0 ?></td>
                        <td style="background:#F1F8E9;"><?= $dominio['baremos']['riesgo_bajo'][0] ?? 0 ?></td>
                        <td style="background:#FFFDE7;"><?= $dominio['baremos']['riesgo_medio'][0] ?? 0 ?></td>
                        <td style="background:#FFF3E0;"><?= $dominio['baremos']['riesgo_alto'][0] ?? 0 ?></td>
                        <td style="background:#FFEBEE;"><?= $dominio['baremos']['riesgo_muy_alto'][0] ?? 0 ?></td>
                    </tr>
                    <tr>
                        <td style="background:#E8F5E9;">↑</td>
                        <td style="background:#E8F5E9;"><?= $dominio['baremos']['sin_riesgo'][1] ?? 0 ?></td>
                        <td style="background:#F1F8E9;"><?= $dominio['baremos']['riesgo_bajo'][1] ?? 0 ?></td>
                        <td style="background:#FFFDE7;"><?= $dominio['baremos']['riesgo_medio'][1] ?? 0 ?></td>
                        <td style="background:#FFF3E0;"><?= $dominio['baremos']['riesgo_alto'][1] ?? 0 ?></td>
                        <td style="background:#FFEBEE;"><?= $dominio['baremos']['riesgo_muy_alto'][1] ?? 0 ?></td>
                    </tr>
                </table>
            </td>

            <!-- Columna derecha: Interpretación -->
            <td style="width: 67%; padding-left: 12px;">
                <div class="interpretation">
                    <p>
                        El dominio <strong><?= esc($dominio['nombre']) ?></strong>
                        presenta un puntaje promedio de <strong><?= number_format($dominio['promedio'], 2, ',', '.') ?></strong>,
                        clasificándose como
                        <span class="risk-label" style="background-color: <?= $color ?>; color: <?= $dominio['nivel'] == 'riesgo_medio' ? '#333' : 'white' ?>;">
                            <?= $label ?>
                        </span>.
                    </p>
                    <p>
                        Se evaluaron <strong><?= $dominio['total_evaluados'] ?></strong> trabajadores.
                    </p>
                </div>

                <!-- Dimensiones -->
                <div class="dimensions-box">
                    <div class="dimensions-title">Dimensiones que componen este dominio:</div>
                    <ul class="dimensions-list">
                        <?php foreach ($dominio['dimensiones'] as $dim): ?>
                        <li><?= esc($dim) ?></li>
                        <?php endforeach; ?>
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
                    <?php if ($pctAlto > 0): ?>
                    <div class="bar-segment" style="width: <?= $pctAlto ?>%; background-color: #F44336;">
                        <?= $pctAlto > 10 ? round($pctAlto) . '%' : '' ?>
                    </div>
                    <?php endif; ?>
                    <?php if ($pctMedio > 0): ?>
                    <div class="bar-segment" style="width: <?= $pctMedio ?>%; background-color: #FFEB3B; color: #333;">
                        <?= $pctMedio > 10 ? round($pctMedio) . '%' : '' ?>
                    </div>
                    <?php endif; ?>
                    <?php if ($pctBajo > 0): ?>
                    <div class="bar-segment" style="width: <?= $pctBajo ?>%; background-color: #4CAF50;">
                        <?= $pctBajo > 10 ? round($pctBajo) . '%' : '' ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Leyenda -->
                <table style="width: 100%; font-size: 7.5pt; margin-top: 4px;">
                    <tr>
                        <td><span style="display:inline-block; width:9px; height:9px; background:#F44336;"></span> Alto/Muy Alto: <?= round($pctAlto, 1) ?>%</td>
                        <td><span style="display:inline-block; width:9px; height:9px; background:#FFEB3B;"></span> Medio: <?= round($pctMedio, 1) ?>%</td>
                        <td><span style="display:inline-block; width:9px; height:9px; background:#4CAF50;"></span> Sin/Bajo: <?= round($pctBajo, 1) ?>%</td>
                    </tr>
                </table>
            </td>
            <td width="40%" style="padding-left: 12px;">
                <div style="font-size: 8.5pt;">
                    <strong style="color: #F44336;"><?= $dominio['distribucion']['riesgo_alto'] + $dominio['distribucion']['riesgo_muy_alto'] ?></strong> personas en riesgo alto/muy alto<br>
                    <strong style="color: #F9A825;"><?= $dominio['distribucion']['riesgo_medio'] ?></strong> personas en riesgo medio
                </div>
            </td>
        </tr>
    </table>

    <!-- Focus box -->
    <div class="focus-box">
        <div class="focus-title">Acción Recomendada:</div>
        <div><?= $focusActions[$dominio['nivel']] ?? 'Evaluar situación' ?> según Resolución 2404/2019.</div>
    </div>

    <!-- Texto generado por IA -->
    <?php if (!empty($dominio['texto_ia'])): ?>
    <div class="ai-text-box">
        <div class="ai-text-title">Análisis del Dominio:</div>
        <div><?= nl2br(esc($dominio['texto_ia'])) ?></div>
    </div>
    <?php endif; ?>

    <!-- Footer -->
    <div class="footer">
        Informe generado por PSYRISK - Batería de Riesgo Psicosocial
    </div>

    <?php if ($index < count($dominiosData) - 1): ?>
    <div class="page-break"></div>
    <?php endif; ?>

<?php endforeach; ?>

</body>
</html>
