<?php
require_once 'vendor/autoload.php';

use App\Libraries\IntralaboralAScoring;

// Probar el baremo de "Exigencias de responsabilidad del cargo" (exigencias_responsabilidad_cargo)
$puntaje = 55.6;
$baremo = IntralaboralAScoring::getBaremoDimension('exigencias_responsabilidad_cargo');

echo "Baremo para 'exigencias_responsabilidad':\n";
print_r($baremo);

echo "\nPuntaje a evaluar: $puntaje\n";

foreach ($baremo as $nivel => $rango) {
    $min = $rango[0];
    $max = $rango[1];
    echo "Nivel: $nivel - Rango: [$min, $max] - ";
    if ($puntaje >= $min && $puntaje <= $max) {
        echo "*** MATCH ***\n";
    } else {
        echo "no match\n";
    }
}
