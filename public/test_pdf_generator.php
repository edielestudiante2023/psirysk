<?php
// Cargar CodeIgniter bootstrap
require __DIR__ . '/../vendor/autoload.php';

// Configurar constantes básicas
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
define('SYSTEMPATH', realpath(__DIR__ . '/../vendor/codeigniter4/framework/system') . DIRECTORY_SEPARATOR);
define('APPPATH', realpath(__DIR__ . '/../app') . DIRECTORY_SEPARATOR);
define('ROOTPATH', realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR);
define('WRITEPATH', realpath(__DIR__ . '/../writable') . DIRECTORY_SEPARATOR);

try {
    // Cargar el generador PDF
    require APPPATH . 'Libraries/PDFReportGenerator.php';

    $generator = new \App\Libraries\PDFReportGenerator();

    // Generar PDF para el servicio ID 2
    $dompdf = $generator->generateCompleteReport(2);

    // Enviar al navegador
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="test_report.pdf"');
    echo $dompdf->output();

} catch (\Exception $e) {
    header('Content-Type: text/html; charset=utf-8');
    echo "<h1>Error al generar PDF</h1>";
    echo "<pre>";
    echo "Mensaje: " . $e->getMessage() . "\n\n";
    echo "Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
    echo "Trace:\n" . $e->getTraceAsString();
    echo "</pre>";
}
