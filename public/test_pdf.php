<?php
require '../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Configurar opciones
$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Arial');

// Crear instancia
$dompdf = new Dompdf($options);

// HTML de prueba
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        h1 { color: #667eea; }
    </style>
</head>
<body>
    <h1>Prueba PDF</h1>
    <p>Este es un PDF de prueba generado con Dompdf.</p>
    <p>Fecha: ' . date('Y-m-d H:i:s') . '</p>
</body>
</html>
';

// Cargar HTML
$dompdf->loadHtml($html);

// Configurar papel
$dompdf->setPaper('Letter', 'portrait');

// Renderizar
$dompdf->render();

// Enviar al navegador
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="prueba.pdf"');
echo $dompdf->output();
