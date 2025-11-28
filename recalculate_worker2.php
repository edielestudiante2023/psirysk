<?php

require 'vendor/autoload.php';

use App\Libraries\IntralaboralAScoring;
use App\Libraries\ExtralaboralScoring;
use App\Libraries\EstresScoring;

$db = \Config\Database::connect();

echo "=== RECALCULAR WORKER 2 DESDE CERO (FORMA A) ===\n\n";

$workerId = 2;

// Obtener worker info
$worker = $db->table('workers')->where('id', $workerId)->get()->getRowArray();
echo "Worker: {$worker['name']} (ID: $workerId)\n";
echo "Tipo Intralaboral: {$worker['intralaboral_type']}\n";
echo "Atiende Clientes: " . ($worker['atiende_clientes'] ?? 'NULL') . "\n";
echo "Es Jefe: " . ($worker['es_jefe'] ?? 'NULL') . "\n\n";

// PASO 1: INTRALABORAL
echo "=== INTRALABORAL FORMA A ===\n";
$responsesIntra = $db->table('responses')
    ->where('worker_id', $workerId)
    ->where('form_type', 'intralaboral_A')
    ->orderBy('question_number')
    ->get()
    ->getResultArray();

echo "Total respuestas: " . count($responsesIntra) . "\n\n";

// Convertir a array
$respuestasIntra = [];
foreach ($responsesIntra as $r) {
    $respuestasIntra[$r['question_number']] = $r['answer_value'];
}

// Calificar
$atiendeClientes = ($worker['atiende_clientes'] == 1);
$esJefe = ($worker['es_jefe'] == 1);
$resultadosIntra = IntralaboralAScoring::calificar($respuestasIntra, $atiendeClientes, $esJefe);

echo "DIMENSIONES:\n";
foreach ($resultadosIntra['puntajes_transformados_dimensiones'] as $dimension => $puntaje) {
    if ($puntaje === null) {
        echo sprintf("  %-50s: %10s (%s)\n", $dimension, "N/A", "NO APLICA");
    } else {
        echo sprintf("  %-50s: %10.1f (%s)\n",
            $dimension,
            $puntaje,
            $resultadosIntra['niveles_riesgo_dimensiones'][$dimension]
        );
    }
}

echo "\nDOMINIOS:\n";
foreach ($resultadosIntra['puntajes_transformados_dominios'] as $dominio => $puntaje) {
    echo sprintf("  %-30s: %5.1f (%s)\n",
        $dominio,
        $puntaje,
        $resultadosIntra['niveles_riesgo_dominios'][$dominio]
    );
}

echo "\nTOTAL INTRALABORAL:\n";
echo "  Puntaje Bruto: " . $resultadosIntra['puntaje_bruto_total'] . "\n";
echo "  Puntaje Transformado: " . $resultadosIntra['puntaje_transformado_total'] . "\n";
echo "  Nivel: " . $resultadosIntra['nivel_riesgo_total'] . "\n\n";

// Comparar dimensiones de RECOMPENSAS con BD
echo "=== COMPARACIÓN DIMENSIONES RECOMPENSAS ===\n\n";

$calculatedResults = $db->table('calculated_results')
    ->where('worker_id', $workerId)
    ->get()
    ->getRowArray();

echo sprintf("%-55s | %10s | %10s\n", "Dimensión", "Calculado", "En BD");
echo str_repeat("-", 80) . "\n";

// Recompensas pertenencia
$calcRecomp = $resultadosIntra['puntajes_transformados_dimensiones']['recompensas_pertenencia_estabilidad'];
$bdRecomp = $calculatedResults['dim_recompensas_pertenencia_puntaje'];
echo sprintf("%-55s | %10.1f | %10s\n",
    "Recompensas derivadas de la pertenencia",
    $calcRecomp,
    $bdRecomp ?? 'NULL'
);

// Reconocimiento y compensación (NUEVA DIMENSIÓN)
$calcRecon = $resultadosIntra['puntajes_transformados_dimensiones']['reconocimiento_compensacion'];
$bdRecon = $calculatedResults['dim_reconocimiento_compensacion_puntaje'];
echo sprintf("%-55s | %10.1f | %10s ⭐ NUEVA\n",
    "Reconocimiento y compensación",
    $calcRecon,
    $bdRecon ?? 'NULL'
);

// Dominio Recompensas
$calcDomRecomp = $resultadosIntra['puntajes_transformados_dominios']['recompensas'];
$bdDomRecomp = $calculatedResults['dom_recompensas_puntaje'];
echo sprintf("%-55s | %10.1f | %10s\n",
    "Dominio Recompensas",
    $calcDomRecomp,
    $bdDomRecomp ?? 'NULL'
);

echo "\n";
