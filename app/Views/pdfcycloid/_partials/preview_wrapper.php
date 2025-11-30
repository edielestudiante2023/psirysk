<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($pageTitle ?? 'Preview PDF Cycloid') ?></title>
    <style>
        /* Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            line-height: 1.3;
            color: #333;
            background: #e0e0e0;
            padding: 20px;
        }

        /* Container centrado */
        .preview-container {
            max-width: 240mm;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Toolbar */
        .preview-toolbar {
            background: #17a2b8;
            color: white;
            padding: 10px 20px;
            margin-bottom: 20px;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 216mm;
        }

        .preview-toolbar h1 {
            font-size: 16px;
            margin: 0;
        }

        .preview-toolbar .actions a {
            color: white;
            background: #138496;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 3px;
            margin-left: 10px;
        }

        .preview-toolbar .actions a:hover {
            background: #117a8b;
        }

        /* Página PDF */
        .pdf-page {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            margin: 0 auto 20px auto;
            width: 216mm; /* Letter width */
            min-height: 279mm; /* Letter height */
            padding: 20mm;
        }

        /* Portada específica */
        .pdf-page.portada {
            text-align: center;
            padding-top: 40mm;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Header de portada */
        .pdf-header {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 60px;
            margin-bottom: 30px;
            border-bottom: none !important;
            width: 100%;
        }

        /* Estilos de portada */
        .portada-title {
            font-size: 20pt;
            font-weight: bold;
            color: #0066cc;
            margin-bottom: 60px;
        }

        .portada-company-label {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .portada-company-name {
            font-size: 13pt;
            font-style: italic;
            margin-bottom: 40px;
        }

        .portada-company-logo {
            margin: 15px 0 30px 0;
            text-align: center;
        }

        .portada-consultant-label {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .portada-consultant-name {
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .portada-consultant-title {
            font-size: 10pt;
            font-style: italic;
            margin-bottom: 5px;
        }

        .portada-consultant-license {
            font-size: 9pt;
            color: #555;
            margin-bottom: 40px;
        }

        .portada-date {
            font-size: 11pt;
            margin-top: 40px;
        }

        /* Print */
        @media print {
            body {
                background: white;
                padding: 0;
            }

            .preview-toolbar {
                display: none;
            }

            .preview-container {
                max-width: none;
            }

            .pdf-page {
                box-shadow: none;
                margin: 0;
                page-break-after: always;
            }
        }
    </style>
</head>
<body>
    <div class="preview-container">
        <div class="preview-toolbar">
            <h1><?= esc($pageTitle ?? 'Preview PDF Cycloid') ?></h1>
            <div class="actions">
                <a href="javascript:window.print()"><i class="fas fa-print"></i> Imprimir</a>
                <a href="<?= base_url('pdf-cycloid/download/portada/' . ($batteryServiceId ?? 1)) ?>"><i class="fas fa-download"></i> Descargar PDF</a>
            </div>
        </div>

        <?= $content ?>
    </div>
</body>
</html>
