<?php

require_once 'vendor/autoload.php';

// Bootstrap CodeIgniter
$app = \Config\Services::codeigniter();
$app->initialize();

// Get CalculationService
$calculationService = \Config\Services::calculationService();

echo "=== RECALCULATING WORKER 1 ===\n";

try {
    $results = $calculationService->calculateAndSaveResults(1);
    echo "✅ Calculation successful!\n";
    var_dump($results);
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
