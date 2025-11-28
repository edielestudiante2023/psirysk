<?php

echo "=== TEST BAREMO MATCHING ===\n\n";

// Baremos from EstresScoring.php
$baremosAuxiliares = [
    'muy_bajo' => [0.0, 6.5],
    'bajo' => [6.6, 11.8],
    'medio' => [11.9, 17.0],
    'alto' => [17.1, 23.4],
    'muy_alto' => [23.5, 100.0]
];

$baremosJefes = [
    'muy_bajo' => [0.0, 7.8],
    'bajo' => [7.9, 12.6],
    'medio' => [12.7, 17.7],
    'alto' => [17.8, 25.0],
    'muy_alto' => [25.1, 100.0]
];

function determinarNivel($puntaje, $baremos) {
    echo "Puntaje a evaluar: {$puntaje}\n";
    echo "Evaluando con baremos:\n";

    foreach ($baremos as $nivel => $rango) {
        $min = $rango[0];
        $max = $rango[1];
        $cumple = ($puntaje >= $min && $puntaje <= $max);

        echo "  - {$nivel}: [{$min}, {$max}] -> " . ($cumple ? 'MATCH' : 'no match') . "\n";

        if ($cumple) {
            return $nivel;
        }
    }

    return 'muy_bajo'; // Default
}

// Test 1: Puntaje 100.0 con baremo auxiliares
echo "TEST 1: Puntaje 100.0 con baremo AUXILIARES\n";
echo "----------------------------------------\n";
$nivel1 = determinarNivel(100.0, $baremosAuxiliares);
echo "RESULTADO: {$nivel1}\n";
echo "ESPERADO: muy_alto\n\n";

// Test 2: Puntaje 100.0 con baremo jefes
echo "TEST 2: Puntaje 100.0 con baremo JEFES\n";
echo "----------------------------------------\n";
$nivel2 = determinarNivel(100.0, $baremosJefes);
echo "RESULTADO: {$nivel2}\n";
echo "ESPERADO: muy_alto\n\n";

// Test 3: Otros puntajes con baremo auxiliares
echo "TEST 3: Varios puntajes con baremo AUXILIARES\n";
echo "----------------------------------------\n";
$testPuntajes = [5.0, 10.0, 15.0, 20.0, 50.0, 100.0];
foreach ($testPuntajes as $p) {
    $nivel = determinarNivel($p, $baremosAuxiliares);
    echo "Puntaje {$p} -> {$nivel}\n\n";
}

echo "=== FIN TEST ===\n";
