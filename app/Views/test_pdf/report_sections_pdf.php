<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe de Secciones - <?= esc($service['company_name']) ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            background-color: #198754;
            color: white;
            padding: 15px;
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 16px;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 11px;
            opacity: 0.9;
        }
        .info-box {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 15px;
        }
        .info-box table {
            width: 100%;
        }
        .info-box td {
            padding: 3px 5px;
        }
        .info-box .label {
            font-weight: bold;
            width: 150px;
        }
        .summary-box {
            background-color: #e9ecef;
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 20px;
        }
        .summary-box h3 {
            font-size: 12px;
            margin-bottom: 8px;
        }
        .summary-stats {
            display: table;
            width: 100%;
        }
        .summary-stat {
            display: table-cell;
            text-align: center;
            padding: 5px;
        }
        .summary-stat .number {
            font-size: 18px;
            font-weight: bold;
        }
        .summary-stat .label {
            font-size: 9px;
            color: #666;
        }
        .level-section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        .level-title {
            padding: 8px 12px;
            font-size: 12px;
            font-weight: bold;
            color: white;
            margin-bottom: 0;
        }
        .level-executive { background-color: #dc3545; }
        .level-total { background-color: #fd7e14; }
        .level-questionnaire { background-color: #0d6efd; }
        .level-domain { background-color: #6f42c1; }
        .level-dimension { background-color: #198754; }
        .section-card {
            border: 1px solid #ddd;
            margin-bottom: 10px;
            page-break-inside: avoid;
        }
        .section-header {
            background-color: #f8f9fa;
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        .section-header .badges {
            margin-top: 5px;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 8px;
            border-radius: 3px;
            margin-right: 3px;
        }
        .badge-secondary { background-color: #6c757d; color: white; }
        .badge-info { background-color: #0dcaf0; color: black; }
        .badge-success { background-color: #198754; color: white; }
        .badge-warning { background-color: #ffc107; color: black; }
        .badge-danger { background-color: #dc3545; color: white; }
        .badge-purple { background-color: #6f42c1; color: white; }
        .section-body {
            padding: 10px;
        }
        .ai-text {
            background-color: #f8f9fa;
            padding: 10px;
            border-left: 3px solid #198754;
            margin-bottom: 10px;
            text-align: justify;
            line-height: 1.6;
        }
        .ai-text-label {
            font-size: 9px;
            color: #198754;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .consultant-comment {
            background-color: #fff3cd;
            padding: 10px;
            border-left: 3px solid #ffc107;
            margin-top: 10px;
        }
        .consultant-comment-label {
            font-size: 9px;
            color: #856404;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .risk-badge {
            padding: 3px 8px;
            font-size: 9px;
            font-weight: bold;
        }
        .risk-sin { background-color: #28a745; color: white; }
        .risk-bajo { background-color: #17a2b8; color: white; }
        .risk-medio { background-color: #ffc107; color: black; }
        .risk-alto { background-color: #fd7e14; color: white; }
        .risk-muy-alto { background-color: #dc3545; color: white; }
        .score-box {
            display: inline-block;
            background-color: #343a40;
            color: white;
            padding: 2px 8px;
            font-size: 9px;
            border-radius: 3px;
        }
        .no-ai-text {
            color: #999;
            font-style: italic;
            padding: 10px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 9px;
            color: #666;
        }
        .page-break {
            page-break-before: always;
        }
        .toc {
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
        }
        .toc h3 {
            font-size: 12px;
            margin-bottom: 10px;
        }
        .toc-item {
            padding: 3px 0;
            border-bottom: 1px dotted #ddd;
        }
    </style>
</head>
<body>
    <!-- Encabezado -->
    <div class="header">
        <h1>INFORME DE RIESGO PSICOSOCIAL</h1>
        <p>Secciones con Interpretación IA</p>
    </div>

    <!-- Información del servicio -->
    <div class="info-box">
        <table>
            <tr>
                <td class="label">Empresa:</td>
                <td><?= esc($service['company_name']) ?></td>
                <td class="label">ID Servicio:</td>
                <td><?= $service['id'] ?></td>
            </tr>
            <tr>
                <td class="label">ID Reporte:</td>
                <td><?= $report['id'] ?></td>
                <td class="label">Fecha Creación:</td>
                <td><?= isset($report['created_at']) ? date('d/m/Y H:i', strtotime($report['created_at'])) : 'N/A' ?></td>
            </tr>
        </table>
    </div>

    <!-- Resumen de secciones -->
    <div class="summary-box">
        <h3>Resumen del Informe</h3>
        <table style="width: 100%;">
            <tr>
                <td style="text-align: center; padding: 5px;">
                    <div style="font-size: 20px; font-weight: bold; color: #dc3545;"><?= count($groupedSections['executive']) ?></div>
                    <div style="font-size: 9px; color: #666;">Executive</div>
                </td>
                <td style="text-align: center; padding: 5px;">
                    <div style="font-size: 20px; font-weight: bold; color: #fd7e14;"><?= count($groupedSections['total']) ?></div>
                    <div style="font-size: 9px; color: #666;">Total</div>
                </td>
                <td style="text-align: center; padding: 5px;">
                    <div style="font-size: 20px; font-weight: bold; color: #0d6efd;"><?= count($groupedSections['questionnaire']) ?></div>
                    <div style="font-size: 9px; color: #666;">Questionnaire</div>
                </td>
                <td style="text-align: center; padding: 5px;">
                    <div style="font-size: 20px; font-weight: bold; color: #6f42c1;"><?= count($groupedSections['domain']) ?></div>
                    <div style="font-size: 9px; color: #666;">Domain</div>
                </td>
                <td style="text-align: center; padding: 5px;">
                    <div style="font-size: 20px; font-weight: bold; color: #198754;"><?= count($groupedSections['dimension']) ?></div>
                    <div style="font-size: 9px; color: #666;">Dimension</div>
                </td>
                <td style="text-align: center; padding: 5px; background-color: #e9ecef;">
                    <div style="font-size: 20px; font-weight: bold;"><?= $totalSections ?></div>
                    <div style="font-size: 9px; color: #666;">Total Secciones</div>
                </td>
            </tr>
        </table>
    </div>

    <?php
    $levelConfig = [
        'executive' => ['title' => 'RESUMEN EJECUTIVO', 'class' => 'level-executive'],
        'total' => ['title' => 'TOTALES GENERALES', 'class' => 'level-total'],
        'questionnaire' => ['title' => 'POR CUESTIONARIO', 'class' => 'level-questionnaire'],
        'domain' => ['title' => 'POR DOMINIO', 'class' => 'level-domain'],
        'dimension' => ['title' => 'POR DIMENSIÓN', 'class' => 'level-dimension'],
    ];

    foreach ($levelConfig as $level => $config):
        $levelSections = $groupedSections[$level];
        if (empty($levelSections)) continue;
    ?>
    <div class="level-section">
        <div class="level-title <?= $config['class'] ?>">
            <?= $config['title'] ?> (<?= count($levelSections) ?> secciones)
        </div>

        <?php foreach ($levelSections as $section): ?>
        <div class="section-card">
            <div class="section-header">
                <strong>#<?= $section['id'] ?></strong>

                <?php if ($section['questionnaire_type']): ?>
                <span class="badge badge-secondary"><?= $section['questionnaire_type'] ?></span>
                <?php endif; ?>

                <?php if ($section['form_type']): ?>
                <span class="badge badge-info">Forma <?= $section['form_type'] ?></span>
                <?php endif; ?>

                <?php if ($section['domain_code']): ?>
                <span class="badge badge-purple"><?= $section['domain_code'] ?></span>
                <?php endif; ?>

                <?php if ($section['dimension_code']): ?>
                <span class="badge badge-success"><?= $section['dimension_code'] ?></span>
                <?php endif; ?>

                <div class="badges" style="margin-top: 5px;">
                    <?php if ($section['score_value'] !== null): ?>
                    <span class="score-box">Puntaje: <?= number_format($section['score_value'], 1) ?></span>
                    <?php endif; ?>

                    <?php if ($section['risk_level']): ?>
                    <?php
                    $riskClass = 'badge-secondary';
                    if (stripos($section['risk_level'], 'sin') !== false) $riskClass = 'risk-sin';
                    elseif (stripos($section['risk_level'], 'bajo') !== false) $riskClass = 'risk-bajo';
                    elseif (stripos($section['risk_level'], 'medio') !== false) $riskClass = 'risk-medio';
                    elseif (stripos($section['risk_level'], 'muy alto') !== false) $riskClass = 'risk-muy-alto';
                    elseif (stripos($section['risk_level'], 'alto') !== false) $riskClass = 'risk-alto';
                    ?>
                    <span class="risk-badge <?= $riskClass ?>"><?= $section['risk_level'] ?></span>
                    <?php endif; ?>

                    <?php if ($section['is_approved']): ?>
                    <span class="badge badge-success">Aprobado</span>
                    <?php else: ?>
                    <span class="badge badge-warning">Pendiente</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="section-body">
                <?php if ($section['ai_generated_text']): ?>
                <div class="ai-text-label">TEXTO GENERADO POR IA:</div>
                <div class="ai-text"><?= nl2br(esc($section['ai_generated_text'])) ?></div>
                <?php else: ?>
                <div class="no-ai-text">Sin texto de IA generado para esta sección.</div>
                <?php endif; ?>

                <?php if ($section['consultant_comment']): ?>
                <div class="consultant-comment-label">COMENTARIO DEL CONSULTOR:</div>
                <div class="consultant-comment"><?= nl2br(esc($section['consultant_comment'])) ?></div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>

    <!-- Pie de página -->
    <div class="footer">
        Documento generado automáticamente | <?= date('d/m/Y H:i:s') ?> | PsyRisk - Sistema de Gestión de Riesgo Psicosocial
    </div>
</body>
</html>
