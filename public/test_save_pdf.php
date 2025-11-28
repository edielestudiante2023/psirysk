<?php
// Script para guardar el PDF directamente a archivo y diagnosticar
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
define('SYSTEMPATH', realpath(__DIR__ . '/../vendor/codeigniter4/framework/system') . DIRECTORY_SEPARATOR);
define('APPPATH', realpath(__DIR__ . '/../app') . DIRECTORY_SEPARATOR);
define('ROOTPATH', realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR);
define('WRITEPATH', realpath(__DIR__ . '/../writable') . DIRECTORY_SEPARATOR);

require __DIR__ . '/../vendor/autoload.php';

try {
    // Cargar configuración de base de datos
    $config = new \Config\Database();

    // Cargar el generador PDF
    require APPPATH . 'Libraries/PDFReportGenerator.php';

    $generator = new \App\Libraries\PDFReportGenerator();

    // Generar PDF para el servicio ID 2
    echo "Generando PDF...\n";
    $dompdf = $generator->generateCompleteReport(2);

    echo "Obteniendo output...\n";
    $output = $dompdf->output();

    echo "Tamaño del PDF: " . strlen($output) . " bytes\n";
    echo "Primeros 20 caracteres: " . substr($output, 0, 20) . "\n";
    echo "Últimos 20 caracteres: " . substr($output, -20) . "\n";

    // Guardar a archivo
    $filename = WRITEPATH . 'logs/test_report.pdf';
    file_put_contents($filename, $output);

    echo "\nPDF guardado en: $filename\n";
    echo "Ahora intenta abrir ese archivo directamente.\n";

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString();
}
