<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Informe' ?> - <?= tenant_brand_name() ?></title>
    <style>
        @page {
            margin: 20mm 15mm 25mm 15mm;
            size: A4;
        }
        @media print {
            body {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .page-break {
                page-break-before: always;
            }
            .no-print {
                display: none !important;
            }
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 11pt;
            line-height: 1.5;
            color: #333;
            background: #fff;
        }
        /* Header del reporte */
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 3px solid <?= tenant_primary_color() ?>;
            margin-bottom: 30px;
        }
        .report-header-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .report-header-left img {
            height: 50px;
        }
        .report-header-right {
            text-align: right;
        }
        .report-header-right img {
            height: 40px;
        }
        .report-title {
            text-align: center;
            margin-bottom: 30px;
        }
        .report-title h1 {
            color: #667eea;
            font-size: 24pt;
            margin-bottom: 5px;
        }
        .report-title h2 {
            color: #764ba2;
            font-size: 16pt;
            font-weight: normal;
        }
        .report-title p {
            color: #666;
            font-size: 10pt;
        }
        /* Contenido */
        .report-content {
            min-height: calc(100vh - 250px);
        }
        .report-section {
            margin-bottom: 25px;
        }
        .report-section h3 {
            color: #667eea;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 8px;
            margin-bottom: 15px;
            font-size: 14pt;
        }
        .report-section h4 {
            color: #764ba2;
            margin-bottom: 10px;
            font-size: 12pt;
        }
        /* Tablas */
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10pt;
        }
        .report-table th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: 600;
        }
        .report-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #e9ecef;
        }
        .report-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        /* Niveles de riesgo */
        .risk-sin { background-color: #28a745 !important; color: white; }
        .risk-bajo { background-color: #20c997 !important; color: white; }
        .risk-medio { background-color: #ffc107 !important; color: #333; }
        .risk-alto { background-color: #fd7e14 !important; color: white; }
        .risk-muy-alto { background-color: #dc3545 !important; color: white; }
        /* Info boxes */
        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin-bottom: 15px;
        }
        .info-box-warning {
            border-left-color: #ffc107;
            background: #fff9e6;
        }
        .info-box-danger {
            border-left-color: #dc3545;
            background: #fff5f5;
        }
        /* Footer del reporte */
        .report-footer {
            border-top: 2px solid #e9ecef;
            padding-top: 15px;
            margin-top: 30px;
        }
        .report-footer-logos {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 30px;
            margin-bottom: 15px;
        }
        .report-footer-logos img {
            height: 35px;
            opacity: 0.8;
        }
        .report-footer-text {
            text-align: center;
            font-size: 9pt;
            color: #666;
        }
        .report-footer-text p {
            margin-bottom: 3px;
        }
        /* Confidencialidad */
        .confidential-notice {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 15px;
            text-align: center;
            font-size: 9pt;
            margin-top: 15px;
            border-radius: 5px;
        }
        /* Utilidades */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .mb-3 { margin-bottom: 15px; }
        .mt-3 { margin-top: 15px; }
        .fw-bold { font-weight: bold; }
        <?= $this->renderSection('styles') ?>
    </style>
</head>
<body>
    <!-- Header -->
    <header class="report-header">
        <div class="report-header-left">
            <img src="<?= tenant_logo_url() ?>" alt="<?= esc(tenant_brand_name()) ?>">
            <div>
                <strong style="color: <?= tenant_primary_color() ?>;"><?= esc(tenant_legal_name()) ?></strong><br>
                <small style="color: #666;">NIT <?= esc(tenant_nit()) ?></small>
            </div>
        </div>
        <div class="report-header-right">
            <img src="<?= platform_logo_url() ?>" alt="psyrisk" style="opacity: 0.6;">
        </div>
    </header>

    <!-- Titulo del reporte -->
    <div class="report-title">
        <h1><?= $reportTitle ?? 'Informe de Evaluacion' ?></h1>
        <?php if (isset($reportSubtitle)): ?>
            <h2><?= $reportSubtitle ?></h2>
        <?php endif; ?>
        <p>Generado el <?= date('d/m/Y H:i') ?></p>
    </div>

    <!-- Contenido -->
    <div class="report-content">
        <?= $this->renderSection('content') ?>
    </div>

    <!-- Footer -->
    <footer class="report-footer">
        <div class="report-footer-logos">
            <img src="<?= tenant_logo_url() ?>" alt="<?= esc(tenant_brand_name()) ?>" title="<?= esc(tenant_legal_name()) ?>">
            <img src="<?= platform_logo_url() ?>" alt="psyrisk" title="Powered by psyrisk" style="opacity: 0.6;">
        </div>
        <div class="report-footer-text">
            <p><strong><?= esc(tenant_legal_name()) ?></strong> - NIT: <?= esc(tenant_nit()) ?></p>
            <p><?= esc(tenant_pdf_footer_text()) ?></p>
            <p style="font-size: 8pt; opacity: 0.7;">Powered by psyrisk</p>
        </div>
        <div class="confidential-notice">
            DOCUMENTO CONFIDENCIAL - Este informe contiene informacion sensible protegida por la Ley 1581 de 2012.
            Su uso, reproduccion o divulgacion no autorizada esta prohibida.
        </div>
    </footer>

    <?= $this->renderSection('scripts') ?>
</body>
</html>
