<?php
// Helper function para colores de riesgo
function getRiskColor($nivel) {
    $colors = [
        'sin_riesgo' => '#28a745',
        'riesgo_bajo' => '#7dce82',
        'riesgo_medio' => '#ffc107',
        'riesgo_alto' => '#fd7e14',
        'riesgo_muy_alto' => '#dc3545',
        'muy_bajo' => '#28a745',
        'bajo' => '#7dce82',
        'medio' => '#ffc107',
        'alto' => '#fd7e14',
        'muy_alto' => '#dc3545'
    ];
    return $colors[$nivel] ?? '#6c757d';
}

function getRiskLabel($nivel) {
    $labels = [
        'sin_riesgo' => 'Sin Riesgo',
        'riesgo_bajo' => 'Riesgo Bajo',
        'riesgo_medio' => 'Riesgo Medio',
        'riesgo_alto' => 'Riesgo Alto',
        'riesgo_muy_alto' => 'Riesgo Muy Alto',
        'muy_bajo' => 'Muy Bajo',
        'bajo' => 'Bajo',
        'medio' => 'Medio',
        'alto' => 'Alto',
        'muy_alto' => 'Muy Alto'
    ];
    return $labels[$nivel] ?? 'N/A';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?></title>
    <style>
        @page {
            margin: 20mm 15mm;
            size: letter;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #333;
        }

        /* Portada */
        .cover-page {
            text-align: center;
            padding-top: 80px;
            page-break-after: always;
        }

        .cover-logo {
            width: 120px;
            height: 120px;
            margin: 0 auto 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 48pt;
            font-weight: bold;
        }

        .cover-title {
            font-size: 32pt;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .cover-subtitle {
            font-size: 18pt;
            color: #667eea;
            margin-bottom: 40px;
        }

        .cover-info {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 10px;
            margin: 40px auto;
            max-width: 500px;
            text-align: left;
        }

        .cover-info-item {
            padding: 10px 0;
            border-bottom: 1px solid #dee2e6;
            font-size: 11pt;
        }

        .cover-info-item:last-child {
            border-bottom: none;
        }

        .cover-info-label {
            font-weight: bold;
            color: #667eea;
            display: inline-block;
            width: 140px;
        }

        .cover-footer {
            position: absolute;
            bottom: 40px;
            left: 0;
            right: 0;
            text-align: center;
            color: #6c757d;
            font-size: 9pt;
        }

        /* Encabezado de páginas internas */
        .page-header {
            border-bottom: 3px solid #667eea;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .page-header h1 {
            font-size: 20pt;
            color: #2c3e50;
            font-weight: bold;
        }

        .page-header .meta {
            font-size: 9pt;
            color: #6c757d;
            margin-top: 5px;
        }

        /* Sección de resumen */
        .summary-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 25px;
        }

        .summary-title {
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .summary-stats {
            display: table;
            width: 100%;
            margin-top: 15px;
        }

        .summary-stat {
            display: table-cell;
            text-align: center;
            padding: 10px;
            width: 25%;
        }

        .summary-stat-value {
            font-size: 24pt;
            font-weight: bold;
            display: block;
        }

        .summary-stat-label {
            font-size: 9pt;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: block;
            margin-top: 5px;
        }

        /* Alerta de riesgo */
        .alert-risk {
            background: #fff3cd;
            border-left: 5px solid #ffc107;
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .alert-risk-title {
            font-size: 12pt;
            font-weight: bold;
            color: #856404;
            margin-bottom: 8px;
        }

        .alert-risk-text {
            font-size: 10pt;
            color: #856404;
            line-height: 1.5;
        }

        /* Tabla de trabajadores */
        .section-title {
            font-size: 14pt;
            font-weight: bold;
            color: #2c3e50;
            margin: 25px 0 15px 0;
            padding-left: 12px;
            border-left: 4px solid #667eea;
        }

        .workers-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 9pt;
        }

        .workers-table thead {
            background: #f8f9fa;
        }

        .workers-table th {
            padding: 10px 8px;
            text-align: left;
            font-weight: bold;
            color: #495057;
            border-bottom: 2px solid #dee2e6;
            font-size: 9pt;
        }

        .workers-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #f0f0f0;
        }

        .workers-table tbody tr:nth-child(even) {
            background: #fafbfc;
        }

        .workers-table tbody tr:hover {
            background: #f1f3f5;
        }

        .risk-badge {
            padding: 4px 10px;
            border-radius: 3px;
            font-size: 8pt;
            font-weight: bold;
            color: white;
            text-align: center;
            display: inline-block;
            min-width: 85px;
        }

        .score-value {
            font-weight: bold;
            font-size: 10pt;
        }

        /* Heatmap Container */
        .heatmap-container {
            border: 2px solid #000;
            margin: 20px 0;
            page-break-inside: avoid;
        }

        .heatmap-legend {
            text-align: center;
            padding: 8px;
            background: white;
            border-bottom: 1px solid #ccc;
            font-size: 9pt;
        }

        .legend-item {
            display: inline-block;
            margin: 0 15px;
        }

        .legend-dot {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 5px;
            vertical-align: middle;
        }

        .heatmap-section {
            display: table;
            width: 100%;
            border-top: 2px solid #000;
        }

        .heatmap-total {
            display: table-cell;
            vertical-align: middle;
            padding: 15px;
            font-size: 9pt;
            font-weight: bold;
            text-align: center;
            border-right: 2px solid #000;
            width: 20%;
        }

        .heatmap-domains {
            display: table-cell;
            vertical-align: top;
            width: 30%;
            border-right: 2px solid #000;
        }

        .heatmap-domain {
            padding: 10px;
            font-size: 8pt;
            text-align: center;
            font-weight: bold;
            border-bottom: 1px solid #999;
            min-height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .heatmap-domain:last-child {
            border-bottom: none;
        }

        .heatmap-dimensions {
            display: table-cell;
            vertical-align: top;
            width: 50%;
        }

        .heatmap-dimension {
            padding: 6px 10px;
            font-size: 7.5pt;
            border-bottom: 1px solid #999;
            min-height: 22px;
            display: flex;
            align-items: center;
        }

        .heatmap-dimension:last-child {
            border-bottom: none;
        }

        /* Colors */
        .color-green { background-color: #90EE90; }
        .color-yellow { background-color: #FFFF00; }
        .color-red { background-color: #FF4444; color: white; }
        .color-gray { background-color: #D3D3D3; }

        /* Footer */
        .page-footer {
            position: fixed;
            bottom: 10mm;
            left: 15mm;
            right: 15mm;
            text-align: center;
            font-size: 8pt;
            color: #6c757d;
            padding-top: 10px;
            border-top: 1px solid #dee2e6;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #6c757d;
            font-style: italic;
        }
    </style>
</head>
<body>
    <!-- PORTADA -->
    <div class="cover-page">
        <div class="cover-logo">P</div>
        <div class="cover-title">Informe Ejecutivo</div>
        <div class="cover-subtitle">Evaluación de Factores de Riesgo Psicosocial Intralaboral</div>

        <div class="cover-info">
            <div class="cover-info-item">
                <span class="cover-info-label">Empresa:</span>
                <span><?= esc($service['company_name']) ?></span>
            </div>
            <div class="cover-info-item">
                <span class="cover-info-label">Servicio:</span>
                <span><?= esc($service['service_name']) ?></span>
            </div>
            <div class="cover-info-item">
                <span class="cover-info-label">Fecha:</span>
                <span><?= date('d/m/Y', strtotime($service['service_date'])) ?></span>
            </div>
            <div class="cover-info-item">
                <span class="cover-info-label">Total Evaluados:</span>
                <span><?= $totales['participantes'] ?> trabajadores</span>
            </div>
            <div class="cover-info-item">
                <span class="cover-info-label">Fecha de Informe:</span>
                <span><?= date('d/m/Y') ?></span>
            </div>
        </div>

        <div class="cover-footer">
            <p><strong>PsyRisk</strong> - Sistema de Evaluación de Riesgo Psicosocial</p>
            <p>Generado conforme a la Resolución 2404 de 2019</p>
        </div>
    </div>

    <!-- PÁGINA 2: RESUMEN EJECUTIVO -->
    <div class="page-header">
        <h1>Resumen Ejecutivo</h1>
        <div class="meta"><?= esc($service['company_name']) ?> | <?= date('d/m/Y') ?></div>
    </div>

    <div class="summary-section">
        <div class="summary-title">Indicadores Generales</div>
        <div class="summary-stats">
            <div class="summary-stat">
                <span class="summary-stat-value"><?= $totales['participantes'] ?></span>
                <span class="summary-stat-label">Participantes</span>
            </div>
            <div class="summary-stat">
                <span class="summary-stat-value"><?= number_format($totales['promedio_intralaboral'], 1) ?></span>
                <span class="summary-stat-label">Promedio Intralaboral</span>
            </div>
            <div class="summary-stat">
                <span class="summary-stat-value"><?= number_format($totales['promedio_extralaboral'], 1) ?></span>
                <span class="summary-stat-label">Promedio Extralaboral</span>
            </div>
            <div class="summary-stat">
                <span class="summary-stat-value"><?= number_format($totales['promedio_estres'], 1) ?></span>
                <span class="summary-stat-label">Promedio Estrés</span>
            </div>
        </div>
    </div>

    <?php if ($totalRisk > 0): ?>
    <div class="alert-risk">
        <div class="alert-risk-title">⚠ Atención Requerida</div>
        <div class="alert-risk-text">
            Se han identificado <strong><?= $totalRisk ?> trabajadores (<?= round(($totalRisk / $totales['participantes']) * 100, 1) ?>%)</strong>
            con niveles de riesgo medio, alto o muy alto que requieren intervención prioritaria según la normativa vigente.
        </div>
    </div>
    <?php endif; ?>

    <!-- TRABAJADORES QUE REQUIEREN ATENCIÓN -->
    <?php if (!empty($riskResults)): ?>
    <h2 class="section-title">Trabajadores que Requieren Atención Inmediata</h2>

    <table class="workers-table">
        <thead>
            <tr>
                <th style="width: 15%;">Documento</th>
                <th style="width: 25%;">Nombre</th>
                <th style="width: 15%;">Intralaboral</th>
                <th style="width: 15%;">Extralaboral</th>
                <th style="width: 15%;">Estrés</th>
                <th style="width: 15%;">General</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($riskResults as $result): ?>
            <tr>
                <td><?= esc($result['document'] ?? 'N/A') ?></td>
                <td><?= esc($result['name'] ?? 'N/A') ?></td>
                <td>
                    <span class="score-value" style="color: <?= getRiskColor($result['intralaboral_total_nivel']) ?>">
                        <?= number_format($result['intralaboral_total_puntaje'], 1) ?>
                    </span>
                    <br>
                    <span class="risk-badge" style="background: <?= getRiskColor($result['intralaboral_total_nivel']) ?>">
                        <?= getRiskLabel($result['intralaboral_total_nivel']) ?>
                    </span>
                </td>
                <td>
                    <span class="score-value" style="color: <?= getRiskColor($result['extralaboral_total_nivel']) ?>">
                        <?= number_format($result['extralaboral_total_puntaje'], 1) ?>
                    </span>
                    <br>
                    <span class="risk-badge" style="background: <?= getRiskColor($result['extralaboral_total_nivel']) ?>">
                        <?= getRiskLabel($result['extralaboral_total_nivel']) ?>
                    </span>
                </td>
                <td>
                    <span class="score-value" style="color: <?= getRiskColor($result['estres_total_nivel']) ?>">
                        <?= number_format($result['estres_total_puntaje'], 1) ?>
                    </span>
                    <br>
                    <span class="risk-badge" style="background: <?= getRiskColor($result['estres_total_nivel']) ?>">
                        <?= getRiskLabel($result['estres_total_nivel']) ?>
                    </span>
                </td>
                <td>
                    <span class="score-value" style="color: <?= getRiskColor($result['puntaje_total_general_nivel'] ?? 'medio') ?>">
                        <?= number_format($result['puntaje_total_general'] ?? 0, 1) ?>
                    </span>
                    <br>
                    <span class="risk-badge" style="background: <?= getRiskColor($result['puntaje_total_general_nivel'] ?? 'medio') ?>">
                        <?= getRiskLabel($result['puntaje_total_general_nivel'] ?? 'medio') ?>
                    </span>
                </td>
            </tr>

            <!-- Detalle de dominios para cada trabajador -->
            <tr>
                <td colspan="6" style="background: #f8f9fa; padding: 5px 10px;">
                    <div class="domains-grid">
                        <div class="domain-item">
                            <span class="domain-label">Liderazgo</span>
                            <span class="domain-value" style="color: <?= getRiskColor($result['dom_liderazgo_nivel'] ?? 'bajo') ?>">
                                <?= number_format($result['dom_liderazgo_puntaje'] ?? 0, 1) ?>
                            </span>
                        </div>
                        <div class="domain-item">
                            <span class="domain-label">Control</span>
                            <span class="domain-value" style="color: <?= getRiskColor($result['dom_control_nivel'] ?? 'bajo') ?>">
                                <?= number_format($result['dom_control_puntaje'] ?? 0, 1) ?>
                            </span>
                        </div>
                        <div class="domain-item">
                            <span class="domain-label">Demandas</span>
                            <span class="domain-value" style="color: <?= getRiskColor($result['dom_demandas_nivel'] ?? 'bajo') ?>">
                                <?= number_format($result['dom_demandas_puntaje'] ?? 0, 1) ?>
                            </span>
                        </div>
                        <div class="domain-item">
                            <span class="domain-label">Recompensas</span>
                            <span class="domain-value" style="color: <?= getRiskColor($result['dom_recompensas_nivel'] ?? 'bajo') ?>">
                                <?= number_format($result['dom_recompensas_puntaje'] ?? 0, 1) ?>
                            </span>
                        </div>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div class="no-data">
        <p>✓ No se identificaron trabajadores en niveles de riesgo que requieran atención inmediata.</p>
        <p>Todos los participantes presentan niveles de riesgo bajo o sin riesgo.</p>
    </div>
    <?php endif; ?>

    <!-- Footer en todas las páginas -->
    <div class="page-footer">
        PsyRisk - <?= esc($service['service_name']) ?> | Página <span class="pageNumber"></span>
    </div>
</body>
</html>
