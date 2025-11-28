<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Informe' ?> - RPS Cycloid</title>
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
            border-bottom: 3px solid #667eea;
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
            <img src="<?= base_url('images/logos/cycloidgrissinfondo.png') ?>" alt="Cycloid Talent">
            <div>
                <strong style="color: #667eea;">Cycloid Talent SAS</strong><br>
                <small style="color: #666;">Gestion del Talento Humano</small>
            </div>
        </div>
        <div class="report-header-right">
            <img src="<?= base_url('images/logos/logo_rps.png') ?>" alt="RPS">
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
            <img src="<?= base_url('images/logos/cycloidgrissinfondo.png') ?>" alt="Cycloid" title="Cycloid Talent SAS">
            <img src="<?= base_url('images/logos/logo_rps.png') ?>" alt="RPS" title="Portafolio RPS">
            <img src="<?= base_url('images/logos/logo_psicloid_method.png') ?>" alt="Psicloid Method" title="Metodologia Psicloid">
        </div>
        <div class="report-footer-text">
            <p><strong>Cycloid Talent SAS</strong> - NIT: XXXXXXXXX-X</p>
            <p>Metodologia Psicloid - Portafolio RPS</p>
            <p>
                <img src="<?= base_url('images/logos/logoenterprisesstobscuro.jpg') ?>" alt="STOB" style="height: 15px; vertical-align: middle;">
                Desarrollado por Enterprisesst
            </p>
        </div>
        <div class="confidential-notice">
            <i class="fas fa-lock"></i>
            DOCUMENTO CONFIDENCIAL - Este informe contiene informacion sensible protegida por la Ley 1581 de 2012.
            Su uso, reproduccion o divulgacion no autorizada esta prohibida.
        </div>
    </footer>

    <?= $this->renderSection('scripts') ?>
</body>
</html>
