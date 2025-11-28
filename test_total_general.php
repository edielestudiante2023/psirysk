<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Libraries\IntralaboralBScoring;
use App\Libraries\ExtralaboralScoring;

// Respuestas del worker_id 3
echo "=== TEST CÁLCULO TOTAL GENERAL ===\n\n";

// Intralaboral B - todas las respuestas en valor 4 (máximo)
// Según la DB: suma = 272
$respuestasIntra = [];
for ($i = 1; $i <= 97; $i++) {
    // Promedio: 272 / 97 ≈ 2.8
    $respuestasIntra[$i] = 4;  // Usar 4 para simular alto riesgo
}

// Extralaboral - suma = 92
$respuestasExtra = [];
for ($i = 1; $i <= 31; $i++) {
    // Promedio: 92 / 31 ≈ 3.0
    $respuestasExtra[$i] = 3;
}

// Calcular intralaboral
$resultadosIntra = IntralaboralBScoring::calificar($respuestasIntra, false);
echo "INTRALABORAL FORMA B:\n";
echo "  Puntaje bruto total: " . $resultadosIntra['puntaje_bruto_total'] . "\n";
echo "  Puntaje transformado total: " . $resultadosIntra['puntaje_transformado_total'] . "\n";
echo "  Nivel de riesgo: " . $resultadosIntra['nivel_riesgo_total'] . "\n\n";

// Calcular extralaboral
$resultadosExtra = ExtralaboralScoring::calificar($respuestasExtra, 'jefes');
echo "EXTRALABORAL:\n";
echo "  Puntaje bruto total: " . $resultadosExtra['puntajes_brutos']['total'] . "\n";
echo "  Puntaje transformado total: " . $resultadosExtra['puntajes_transformados']['total'] . "\n";
echo "  Nivel de riesgo: " . $resultadosExtra['niveles_riesgo']['total'] . "\n\n";

// Cálculo del total general (Forma B)
$puntajeBruto = $resultadosIntra['puntaje_bruto_total'] + $resultadosExtra['puntajes_brutos']['total'];
$factorTransformacion = 512; // Forma B
$puntajeTransformado = round(($puntajeBruto / $factorTransformacion) * 100, 1);

echo "TOTAL GENERAL:\n";
echo "  Suma puntajes brutos: $puntajeBruto\n";
echo "  Factor transformación (Forma B): $factorTransformacion\n";
echo "  Puntaje transformado: $puntajeTransformado\n";
echo "  Fórmula: ($puntajeBruto / $factorTransformacion) × 100 = $puntajeTransformado\n\n";

// Verificar si hay algún error
if ($puntajeTransformado > 100) {
    echo "⚠️ ERROR: El puntaje transformado no puede ser mayor a 100\n";
    echo "Esto indica que los puntajes brutos están siendo mal calculados\n";
} else {
    echo "✅ Puntaje transformado correcto (0-100)\n";
}
