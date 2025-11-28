<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Libraries\IntralaboralBScoring;

// Simular respuestas - todas "siempre" (valor 0)
// Forma B tiene 97 preguntas
$respuestas = [];
for ($i = 1; $i <= 97; $i++) {
    $respuestas[$i] = 0; // Siempre = 0
}

echo "=== TEST INTRALABORAL FORMA B ===\n\n";

// Probar con atiende_clientes = false
echo "--- Con atiende_clientes = false ---\n";
$resultados_sin_clientes = IntralaboralBScoring::calificar($respuestas, false);

echo "\nDimensiones (Puntajes Transformados):\n";
foreach ($resultados_sin_clientes['puntajes_transformados_dimensiones'] as $dimension => $puntaje) {
    $nivel = $resultados_sin_clientes['niveles_riesgo_dimensiones'][$dimension];
    echo "  - $dimension: $puntaje ($nivel)\n";
}

echo "\nDominios (Puntajes Transformados):\n";
foreach ($resultados_sin_clientes['puntajes_transformados_dominios'] as $dominio => $puntaje) {
    $nivel = $resultados_sin_clientes['niveles_riesgo_dominios'][$dominio];
    echo "  - $dominio: $puntaje ($nivel)\n";
}

echo "\nTotal Intralaboral:\n";
echo "  - Puntaje: " . $resultados_sin_clientes['puntaje_transformado_total'] . "\n";
echo "  - Nivel: " . $resultados_sin_clientes['nivel_riesgo_total'] . "\n";

// Verificar que reconocimiento_compensacion esté presente
echo "\n=== VERIFICACIÓN ===\n";
if (isset($resultados_sin_clientes['puntajes_transformados_dimensiones']['reconocimiento_compensacion'])) {
    echo "✅ Dimensión 'reconocimiento_compensacion' encontrada\n";
    echo "   Puntaje: " . $resultados_sin_clientes['puntajes_transformados_dimensiones']['reconocimiento_compensacion'] . "\n";
    echo "   Nivel: " . $resultados_sin_clientes['niveles_riesgo_dimensiones']['reconocimiento_compensacion'] . "\n";
} else {
    echo "❌ Dimensión 'reconocimiento_compensacion' NO encontrada\n";
}

if (isset($resultados_sin_clientes['puntajes_transformados_dimensiones']['demandas_emocionales'])) {
    echo "✅ Dimensión 'demandas_emocionales' encontrada\n";
    echo "   Puntaje: " . $resultados_sin_clientes['puntajes_transformados_dimensiones']['demandas_emocionales'] . "\n";
    echo "   Nivel: " . $resultados_sin_clientes['niveles_riesgo_dimensiones']['demandas_emocionales'] . "\n";
} else {
    echo "❌ Dimensión 'demandas_emocionales' NO encontrada\n";
}

echo "\n";
