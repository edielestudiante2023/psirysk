<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ficha de Datos Generales - <?= esc($service['company_name']) ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            line-height: 1.5;
            color: #333;
        }
        .header {
            background-color: #6f42c1;
            color: white;
            padding: 15px;
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 18px;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 12px;
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
        .section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        .section-title {
            background-color: #6f42c1;
            color: white;
            padding: 8px 12px;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 0;
        }
        .section-content {
            border: 1px solid #6f42c1;
            border-top: none;
            padding: 12px;
            text-align: justify;
        }
        .variable-row {
            border-bottom: 1px solid #eee;
            padding: 10px 0;
            page-break-inside: avoid;
        }
        .variable-row:last-child {
            border-bottom: none;
        }
        .variable-name {
            color: #6f42c1;
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 5px;
        }
        .variable-interpretation {
            text-align: justify;
            line-height: 1.6;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 10px;
        }
        .data-table th {
            background-color: #f0f0f0;
            border: 1px solid #ddd;
            padding: 5px;
            text-align: left;
        }
        .data-table td {
            border: 1px solid #ddd;
            padding: 5px;
        }
        .consultant-comment {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            padding: 12px;
            margin-top: 20px;
        }
        .consultant-comment h3 {
            color: #856404;
            font-size: 12px;
            margin-bottom: 8px;
        }
        .synthesis {
            background-color: #e8f5e9;
            border: 2px solid #4caf50;
            padding: 15px;
            margin-top: 20px;
        }
        .synthesis h3 {
            color: #2e7d32;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .synthesis-content {
            text-align: justify;
            line-height: 1.7;
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
    </style>
</head>
<body>
    <!-- Encabezado -->
    <div class="header">
        <h1>FICHA DE DATOS GENERALES</h1>
        <p>Análisis Sociodemográfico con Interpretación IA</p>
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
                <td class="label">Fecha de Generación:</td>
                <td><?= isset($record['created_at']) ? date('d/m/Y H:i', strtotime($record['created_at'])) : 'N/A' ?></td>
                <td class="label">Última Actualización:</td>
                <td><?= isset($record['updated_at']) ? date('d/m/Y H:i', strtotime($record['updated_at'])) : 'N/A' ?></td>
            </tr>
        </table>
    </div>

    <!-- Interpretaciones por sección -->
    <?php
    $synthesis = null;
    foreach ($sections as $section):
        // Guardar síntesis para mostrar al final
        if (stripos($section['name'], 'SÍNTESIS') !== false || stripos($section['name'], 'SINTESIS') !== false):
            $synthesis = $section;
            continue;
        endif;
    ?>
    <div class="variable-row">
        <div class="variable-name"><?= esc($section['name']) ?></div>
        <div class="variable-interpretation"><?= nl2br(esc($section['content'])) ?></div>
    </div>
    <?php endforeach; ?>

    <!-- Síntesis General (destacada al final) -->
    <?php if ($synthesis): ?>
    <div class="synthesis">
        <h3>SÍNTESIS GENERAL</h3>
        <div class="synthesis-content">
            <?= nl2br(esc($synthesis['content'])) ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Comentarios del Consultor -->
    <?php if (!empty($consultantComment)): ?>
    <div class="consultant-comment">
        <h3>COMENTARIOS DEL CONSULTOR</h3>
        <div><?= nl2br(esc($consultantComment)) ?></div>
    </div>
    <?php endif; ?>

    <!-- Datos Demográficos (tabla resumen opcional) -->
    <?php if (!empty($aggregatedData) && isset($aggregatedData['total_workers'])): ?>
    <div class="page-break"></div>
    <div class="section">
        <div class="section-title">ANEXO: DATOS DEMOGRÁFICOS AGREGADOS</div>
        <div class="section-content">
            <p><strong>Total de trabajadores evaluados:</strong> <?= $aggregatedData['total_workers'] ?></p>

            <?php if (isset($aggregatedData['gender']) && is_array($aggregatedData['gender'])): ?>
            <table class="data-table">
                <tr>
                    <th colspan="3">Distribución por Sexo</th>
                </tr>
                <tr>
                    <th>Categoría</th>
                    <th>Cantidad</th>
                    <th>Porcentaje</th>
                </tr>
                <?php foreach ($aggregatedData['gender'] as $item): ?>
                <tr>
                    <td><?= esc($item['label']) ?></td>
                    <td><?= $item['count'] ?></td>
                    <td><?= number_format($item['percentage'], 1) ?>%</td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>

            <?php if (isset($aggregatedData['education_level']) && is_array($aggregatedData['education_level'])): ?>
            <table class="data-table" style="margin-top: 15px;">
                <tr>
                    <th colspan="3">Distribución por Nivel Educativo</th>
                </tr>
                <tr>
                    <th>Categoría</th>
                    <th>Cantidad</th>
                    <th>Porcentaje</th>
                </tr>
                <?php foreach ($aggregatedData['education_level'] as $item): ?>
                <tr>
                    <td><?= esc($item['label']) ?></td>
                    <td><?= $item['count'] ?></td>
                    <td><?= number_format($item['percentage'], 1) ?>%</td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>

            <?php if (isset($aggregatedData['contract_type']) && is_array($aggregatedData['contract_type'])): ?>
            <table class="data-table" style="margin-top: 15px;">
                <tr>
                    <th colspan="3">Distribución por Tipo de Contrato</th>
                </tr>
                <tr>
                    <th>Categoría</th>
                    <th>Cantidad</th>
                    <th>Porcentaje</th>
                </tr>
                <?php foreach ($aggregatedData['contract_type'] as $item): ?>
                <tr>
                    <td><?= esc($item['label']) ?></td>
                    <td><?= $item['count'] ?></td>
                    <td><?= number_format($item['percentage'], 1) ?>%</td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Pie de página -->
    <div class="footer">
        Documento generado automáticamente | <?= date('d/m/Y H:i:s') ?> | PsyRisk - Sistema de Gestión de Riesgo Psicosocial
    </div>
</body>
</html>
