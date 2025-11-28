<?php

require 'vendor/autoload.php';

use App\Libraries\IntralaboralBScoring;
use App\Libraries\ExtralaboralScoring;
use App\Libraries\EstresScoring;

$db = \Config\Database::connect();

echo "=== RECALCULAR WORKER 4 DESDE CERO ===\n\n";

$workerId = 4;

// Obtener worker info
$worker = $db->table('workers')->where('id', $workerId)->get()->getRowArray();
echo "Worker: {$worker['name']} (ID: $workerId)\n";
echo "Tipo Intralaboral: {$worker['intralaboral_type']}\n";
echo "Atiende Clientes: " . ($worker['atiende_clientes'] ?? 'NULL') . "\n\n";

// PASO 1: INTRALABORAL
echo "=== INTRALABORAL FORMA B ===\n";
$responsesIntra = $db->table('responses')
    ->where('worker_id', $workerId)
    ->where('form_type', 'intralaboral_B')
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
$resultadosIntra = IntralaboralBScoring::calificar($respuestasIntra, $atiendeClientes);

echo "DOMINIOS:\n";
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

// PASO 2: EXTRALABORAL
echo "=== EXTRALABORAL ===\n";
$responsesExtra = $db->table('responses')
    ->where('worker_id', $workerId)
    ->where('form_type', 'extralaboral')
    ->orderBy('question_number')
    ->get()
    ->getResultArray();

echo "Total respuestas: " . count($responsesExtra) . "\n\n";

$respuestasExtra = [];
foreach ($responsesExtra as $r) {
    $respuestasExtra[$r['question_number']] = $r['answer_value'];
}

$resultadosExtra = ExtralaboralScoring::calificar($respuestasExtra);

echo "TOTAL EXTRALABORAL:\n";
echo "  Puntaje Bruto: " . $resultadosExtra['puntajes_brutos']['total'] . "\n";
echo "  Puntaje Transformado: " . $resultadosExtra['puntaje_transformado_total'] . "\n";
echo "  Nivel: " . $resultadosExtra['nivel_riesgo_total'] . "\n\n";

// PASO 3: ESTRÉS
echo "=== ESTRÉS ===\n";
$responsesEstres = $db->table('responses')
    ->where('worker_id', $workerId)
    ->where('form_type', 'estres')
    ->orderBy('question_number')
    ->get()
    ->getResultArray();

echo "Total respuestas: " . count($responsesEstres) . "\n\n";

$respuestasEstres = [];
foreach ($responsesEstres as $r) {
    $respuestasEstres[$r['question_number']] = $r['answer_value'];
}

$tipoBaremo = ($worker['intralaboral_type'] === 'A') ? 'jefes' : 'auxiliares';
$resultadosEstres = EstresScoring::calificar($respuestasEstres, $tipoBaremo);

echo "TOTAL ESTRÉS:\n";
echo "  Puntaje Bruto: " . $resultadosEstres['puntaje_bruto_total'] . "\n";
echo "  Puntaje Transformado: " . $resultadosEstres['puntaje_transformado_total'] . "\n";
echo "  Nivel: " . $resultadosEstres['nivel_estres'] . "\n\n";

// PASO 4: TOTAL GENERAL
echo "=== TOTAL GENERAL ===\n";

$puntajeBrutoIntralaboral = $resultadosIntra['puntaje_bruto_total'];
$puntajeBrutoExtralaboral = $resultadosExtra['puntajes_brutos']['total'];
$puntajeBrutoTotal = $puntajeBrutoIntralaboral + $puntajeBrutoExtralaboral;

// Factor de transformación según tipo de formulario (Tabla 28)
$factorTransformacion = ($worker['intralaboral_type'] === 'A') ? 616 : 512;

$puntajeTransformadoTotal = round(($puntajeBrutoTotal / $factorTransformacion) * 100, 1);

echo "Intralaboral Bruto: $puntajeBrutoIntralaboral\n";
echo "Extralaboral Bruto: $puntajeBrutoExtralaboral\n";
echo "Suma Total: $puntajeBrutoTotal\n";
echo "Factor Transformación (Forma {$worker['intralaboral_type']}): $factorTransformacion\n";
echo "Puntaje Transformado: $puntajeTransformadoTotal\n\n";

// Validar que no exceda 100
if ($puntajeTransformadoTotal > 100) {
    echo "⚠️ WARNING: Puntaje excede 100, limitando a 100.0\n";
    $puntajeTransformadoTotal = 100.0;
}

// Comparar con BD
$calculatedResults = $db->table('calculated_results')
    ->where('worker_id', $workerId)
    ->get()
    ->getRowArray();

echo "=== COMPARACIÓN CON BASE DE DATOS ===\n\n";

echo sprintf("%-40s | %10s | %10s | %s\n", "Campo", "Calculado", "En BD", "Estado");
echo str_repeat("-", 80) . "\n";

// Dominio Demandas
$calcDemandas = $resultadosIntra['puntajes_transformados_dominios']['demandas'];
$bdDemandas = $calculatedResults['dom_demandas_puntaje'];
echo sprintf("%-40s | %10.1f | %10.1f | %s\n",
    "Dominio Demandas",
    $calcDemandas,
    $bdDemandas,
    ($calcDemandas == $bdDemandas) ? "✅" : "❌"
);

// Intralaboral Total
$calcIntra = $resultadosIntra['puntaje_transformado_total'];
$bdIntra = $calculatedResults['intralaboral_total_puntaje'];
echo sprintf("%-40s | %10.1f | %10.1f | %s\n",
    "Intralaboral Total",
    $calcIntra,
    $bdIntra,
    ($calcIntra == $bdIntra) ? "✅" : "❌"
);

// Total General
$bdTotalGeneral = $calculatedResults['puntaje_total_general'];
echo sprintf("%-40s | %10.1f | %10.1f | %s\n",
    "Puntaje Total General",
    $puntajeTransformadoTotal,
    $bdTotalGeneral,
    ($puntajeTransformadoTotal == $bdTotalGeneral) ? "✅" : "❌"
);

echo "\n";
