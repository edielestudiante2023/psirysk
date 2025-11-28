<?php

// Bootstrap CodeIgniter
require __DIR__ . '/vendor/autoload.php';

// Get the CodeIgniter application
$app = require_once FCPATH . '../app/Config/Paths.php';

// Initialize services
$config = new \Config\Services();

// Include necessary files
require_once APPPATH . 'Libraries/EstresScoring.php';

use App\Libraries\EstresScoring;

echo "=== TEST ESTRÉS CALCULATION ===\n\n";

// Create array with all "siempre" responses
$respuestas = [];
for ($i = 1; $i <= 31; $i++) {
    $respuestas[$i] = 'siempre';
}

echo "Respuestas de prueba: Todas 'siempre'\n\n";

// Test with auxiliares baremo (Forma B)
echo "--- TEST CON BAREMO AUXILIARES (Forma B) ---\n";
$resultadosAuxiliares = EstresScoring::calificar($respuestas, 'auxiliares');
echo "Puntaje Bruto: " . $resultadosAuxiliares['puntaje_bruto_total'] . "\n";
echo "Puntaje Transformado: " . $resultadosAuxiliares['puntaje_transformado_total'] . "\n";
echo "Nivel: " . $resultadosAuxiliares['nivel_estres'] . "\n";
echo "Tipo Baremo: " . $resultadosAuxiliares['tipo_baremo'] . "\n\n";

// Test with jefes baremo (Forma A)
echo "--- TEST CON BAREMO JEFES (Forma A) ---\n";
$resultadosJefes = EstresScoring::calificar($respuestas, 'jefes');
echo "Puntaje Bruto: " . $resultadosJefes['puntaje_bruto_total'] . "\n";
echo "Puntaje Transformado: " . $resultadosJefes['puntaje_transformado_total'] . "\n";
echo "Nivel: " . $resultadosJefes['nivel_estres'] . "\n";
echo "Tipo Baremo: " . $resultadosJefes['tipo_baremo'] . "\n\n";

// Now check what's in the database for worker 1
echo "--- VERIFICANDO BASE DE DATOS ---\n";
$db = \Config\Database::connect();

// Check worker data
$worker = $db->table('workers')->where('id', 1)->get()->getRowArray();
echo "Worker 1 intralaboral_type: " . ($worker['intralaboral_type'] ?? 'NULL') . "\n";

// Check responses
$responses = $db->table('responses')
    ->where('worker_id', 1)
    ->where('form_type', 'estres')
    ->get()
    ->getResultArray();
echo "Total responses: " . count($responses) . "\n";

if (count($responses) > 0) {
    echo "Primera respuesta: Q" . $responses[0]['question_number'] . " = " . $responses[0]['answer_value'] . "\n";

    // Count unique answer values
    $valores = array_count_values(array_column($responses, 'answer_value'));
    echo "Distribución de respuestas:\n";
    foreach ($valores as $valor => $cantidad) {
        echo "  - {$valor}: {$cantidad}\n";
    }
}

echo "\n=== FIN TEST ===\n";
