<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($pageTitle ?? 'Preview PDF') ?></title>
    <style>
        <?= file_get_contents(APPPATH . 'Views/pdf/_partials/css/pdf-styles.css') ?>

        /* Estilos adicionales para preview */
        body {
            background: #e0e0e0;
            padding: 20px;
        }

        .preview-container {
            max-width: 220mm;
            margin: 0 auto;
        }

        .preview-toolbar {
            background: #333;
            color: white;
            padding: 10px 20px;
            margin-bottom: 20px;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .preview-toolbar h1 {
            font-size: 16px;
            margin: 0;
        }

        .preview-toolbar .actions a {
            color: white;
            background: #0066cc;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 3px;
            margin-left: 10px;
        }

        .preview-toolbar .actions a:hover {
            background: #0055aa;
        }

        .pdf-page {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            margin-bottom: 20px;
        }

        .page-break {
            height: 20px;
            background: #e0e0e0;
        }
    </style>
</head>
<body>
    <div class="preview-container">
        <div class="preview-toolbar">
            <h1><?= esc($pageTitle ?? 'Preview PDF') ?></h1>
            <div class="actions">
                <a href="javascript:window.print()">Imprimir / PDF</a>
                <a href="<?= base_url('pdf/preview/full/' . ($batteryServiceId ?? 1)) ?>">Ver Completo</a>
            </div>
        </div>

        <?= $content ?>
    </div>
</body>
</html>
