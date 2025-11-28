<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Direct database connection
$mysqli = new mysqli("localhost", "root", "", "psyrisk");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "=== WORKER 1 DATA ===\n";

// Get worker
$result = $mysqli->query("SELECT id, intralaboral_type FROM workers WHERE id = 1");
$worker = $result->fetch_assoc();

echo "Worker ID: " . $worker['id'] . "\n";
echo "Intralaboral Type: " . ($worker['intralaboral_type'] ?? 'NULL') . "\n\n";

// Get responses
$result = $mysqli->query("SELECT question_number, answer_value FROM responses WHERE worker_id = 1 AND form_type = 'estres' ORDER BY question_number");

$answersArray = [];
while ($row = $result->fetch_assoc()) {
    $answersArray[$row['question_number']] = $row['answer_value'];
}

echo "=== RESPONSES ===\n";
echo "Total: " . count($answersArray) . "\n";

// Show distribution
$distribution = array_count_values($answersArray);
echo "Distribución:\n";
foreach ($distribution as $valor => $cantidad) {
    echo "  '{$valor}': {$cantidad}\n";
}

// Determine baremo
$intralaboralType = $worker['intralaboral_type'] ?? 'B';
$tipoBaremo = ($intralaboralType === 'A') ? 'jefes' : 'auxiliares';

echo "\n=== BAREMO ===\n";
echo "intralaboral_type: {$intralaboralType}\n";
echo "tipoBaremo: {$tipoBaremo}\n\n";

// Load EstresScoring
require_once 'app/Libraries/EstresScoring.php';
use App\Libraries\EstresScoring;

echo "=== CALLING EstresScoring::calificar() ===\n\n";

// Clear log before calling
$logFile = 'writable/logs/log-' . date('Y-m-d') . '.log';
file_put_contents($logFile, '');

$results = EstresScoring::calificar($answersArray, $tipoBaremo);

echo "RESULTS:\n";
echo "Puntaje Bruto: " . $results['puntaje_bruto_total'] . "\n";
echo "Puntaje Transformado: " . $results['puntaje_transformado_total'] . "\n";
echo "Nivel Estrés: " . $results['nivel_estres'] . "\n";
echo "Tipo Baremo: " . $results['tipo_baremo'] . "\n\n";

echo "Subtotales:\n";
foreach ($results['subtotales'] as $key => $value) {
    echo "  {$key}: {$value}\n";
}

echo "\n=== LOGS ===\n";
if (file_exists($logFile)) {
    $logContent = file_get_contents($logFile);
    if (!empty(trim($logContent))) {
        echo $logContent;
    } else {
        echo "(empty)\n";
    }
} else {
    echo "(log file not found)\n";
}

echo "\n=== FIN ===\n";

$mysqli->close();
