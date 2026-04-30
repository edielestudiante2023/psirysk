<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Política de Tratamiento de Datos · psyrisk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>body{font-family:'Segoe UI',Tahoma,sans-serif;} .container{max-width:800px;}</style>
</head>
<body class="py-5">
<div class="container">
    <a href="<?= base_url('/') ?>" class="btn btn-link mb-3">← Volver</a>
    <h1>Política de Tratamiento de Datos Personales</h1>
    <p class="text-muted">Versión 1.0 · Vigente desde el 4 de mayo de 2026</p>
    <hr>
    <?php $md = file_get_contents(ROOTPATH . 'legal/01_politica_tratamiento_datos.md'); ?>
    <pre style="white-space:pre-wrap;font-family:'Segoe UI',sans-serif;font-size:14px;line-height:1.6;"><?= esc($md) ?></pre>
</div>
</body>
</html>
