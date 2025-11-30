<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($pageTitle ?? 'Preview PDF Nativo') ?></title>
    <style>
        body {
            margin: 0;
            padding: 20px;
            background: #f0f0f0;
            font-family: Arial, sans-serif;
        }

        .toolbar {
            background: #333;
            color: white;
            padding: 10px 20px;
            margin: -20px -20px 20px -20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .toolbar h1 {
            margin: 0;
            font-size: 16px;
        }

        .toolbar a {
            background: #006699;
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 4px;
        }

        .toolbar a:hover {
            background: #004466;
        }

        .preview-container {
            max-width: 816px;
            margin: 0 auto;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            padding: 85pt 57pt 85pt 113pt;
        }

        <?= $css ?? '' ?>
    </style>
</head>
<body>
    <div class="toolbar">
        <h1><?= esc($pageTitle ?? 'Preview PDF Nativo') ?></h1>
        <a href="<?= base_url('pdf-nativo/download/' . ($batteryServiceId ?? 1)) ?>">
            Descargar PDF
        </a>
    </div>

    <div class="preview-container">
        <?= $content ?? '' ?>
    </div>
</body>
</html>
