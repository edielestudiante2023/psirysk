<?php

// Simple script to test Estrés calculation with actual DB data
require 'vendor/autoload.php';

$config = new \Config\Database();
$db = \Config\Database::connect();

// Get worker 1 data
$worker = $db->table('workers')->where('id', 1)->get()->getRowArray();

echo "=== WORKER 1 DATA ===\n";
echo "ID: " . $worker['id'] . "\n";
echo "Intralaboral Type: " . ($worker['intralaboral_type'] ?? 'NULL') . "\n\n";

// Get responses
$responses = $db->table('responses')
    ->where('worker_id', 1)
    ->where('form_type', 'estres')
    ->orderBy('question_number')
    ->get()
    ->getResultArray();

echo "=== RESPONSES ===\n";
echo "Total: " . count($responses) . "\n";

// Build answers array
$answersArray = [];
foreach ($responses as $r) {
    $answersArray[$r['question_number']] = $r['answer_value'];
}

echo "Primeras 5 respuestas:\n";
for ($i = 1; $i <= 5; $i++) {
    echo "  Q{$i}: " . ($answersArray[$i] ?? 'N/A') . "\n";
}

// Count answer distribution
$distribution = array_count_values($answersArray);
echo "\nDistribución de respuestas:\n";
foreach ($distribution as $valor => $cantidad) {
    echo "  {$valor}: {$cantidad}\n";
}

// Determine baremo
$intralaboralType = $worker['intralaboral_type'] ?? 'B';
$tipoBaremo = ($intralaboralType === 'A') ? 'jefes' : 'auxiliares';

echo "\n=== BAREMO DETERMINATION ===\n";
echo "intralaboral_type: {$intralaboralType}\n";
echo "Tipo Baremo: {$tipoBaremo}\n\n";

// Now call EstresScoring
require_once 'app/Libraries/EstresScoring.php';

use App\Libraries\EstresScoring;

echo "=== CALLING EstresScoring::calificar() ===\n";
$results = EstresScoring::calificar($answersArray, $tipoBaremo);

echo "\nRESULTS:\n";
echo "Puntaje Bruto: " . $results['puntaje_bruto_total'] . "\n";
echo "Puntaje Transformado: " . $results['puntaje_transformado_total'] . "\n";
echo "Nivel Estrés: " . $results['nivel_estres'] . "\n";
echo "Tipo Baremo: " . $results['tipo_baremo'] . "\n\n";

echo "Subtotales:\n";
foreach ($results['subtotales'] as $key => $value) {
    echo "  {$key}: {$value}\n";
}

echo "\n=== CHECK LOG FILE ===\n";
$logFile = 'writable/logs/log-' . date('Y-m-d') . '.log';
if (file_exists($logFile)) {
    echo "Log file: {$logFile}\n";
    echo "Last 50 lines:\n";
    $lines = file($logFile);
    $lastLines = array_slice($lines, -50);
    foreach ($lastLines as $line) {
        if (strpos($line, '[determinarNivelEstres]') !== false || strpos($line, '[EstresScoring]') !== false) {
            echo $line;
        }
    }
} else {
    echo "Log file not found: {$logFile}\n";
}

echo "\n=== FIN ===\n";
