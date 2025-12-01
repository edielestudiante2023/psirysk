<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Libraries\IntralaboralAScoring;
use App\Libraries\IntralaboralBScoring;
use App\Libraries\ExtralaboralScoring;
use App\Libraries\EstresScoring;

echo "=== DIAGNÓSTICO DE BAREMOS ===\n\n";

// TOTALES
echo "--- TOTALES ---\n";
echo "IntralaboralA Total: " . (IntralaboralAScoring::getBaremoTotal() ? 'OK' : 'NULL') . "\n";
echo "IntralaboralB Total: " . (IntralaboralBScoring::getBaremoTotal() ? 'OK' : 'NULL') . "\n";
echo "ExtralaboralA Total: " . (ExtralaboralScoring::getBaremoTotal('A') ? 'OK' : 'NULL') . "\n";
echo "ExtralaboralB Total: " . (ExtralaboralScoring::getBaremoTotal('B') ? 'OK' : 'NULL') . "\n";
echo "EstresA: " . (EstresScoring::getBaremoA() ? 'OK' : 'NULL') . "\n";
echo "EstresB: " . (EstresScoring::getBaremoB() ? 'OK' : 'NULL') . "\n";
echo "General A (Tabla34): " . (EstresScoring::getBaremoGeneral('A') ? 'OK' : 'NULL') . "\n";
echo "General B (Tabla34): " . (EstresScoring::getBaremoGeneral('B') ? 'OK' : 'NULL') . "\n";

// DOMINIOS
echo "\n--- DOMINIOS FORMA A ---\n";
$dominios = ['liderazgo_relaciones_sociales', 'control', 'demandas', 'recompensas'];
foreach ($dominios as $d) {
    $r = IntralaboralAScoring::getBaremoDominio($d);
    echo "  $d: " . ($r ? 'OK' : 'NULL') . "\n";
}

echo "\n--- DOMINIOS FORMA B ---\n";
foreach ($dominios as $d) {
    $r = IntralaboralBScoring::getBaremoDominio($d);
    echo "  $d: " . ($r ? 'OK' : 'NULL') . "\n";
}

// DIMENSIONES FORMA A
echo "\n--- DIMENSIONES FORMA A ---\n";
$dimsA = [
    'caracteristicas_liderazgo',
    'relaciones_sociales_trabajo',
    'retroalimentacion_desempeno',
    'relacion_con_colaboradores',
    'claridad_rol',
    'capacitacion',
    'participacion_manejo_cambio',
    'oportunidades_desarrollo',
    'control_autonomia_trabajo',
    'demandas_ambientales_esfuerzo_fisico',
    'demandas_emocionales',
    'demandas_cuantitativas',
    'influencia_trabajo_entorno_extralaboral',
    'exigencias_responsabilidad_cargo',
    'demandas_carga_mental',
    'consistencia_rol',
    'demandas_jornada_trabajo',
    'recompensas_pertenencia_estabilidad',
    'reconocimiento_compensacion'
];
foreach ($dimsA as $d) {
    $r = IntralaboralAScoring::getBaremoDimension($d);
    echo "  $d: " . ($r ? 'OK' : 'NULL') . "\n";
}

// DIMENSIONES FORMA B
echo "\n--- DIMENSIONES FORMA B ---\n";
$dimsB = [
    'caracteristicas_liderazgo',
    'relaciones_sociales_trabajo',
    'retroalimentacion_desempeno',
    'claridad_rol',
    'capacitacion',
    'participacion_manejo_cambio',
    'oportunidades_desarrollo',
    'control_autonomia_trabajo',
    'demandas_ambientales_esfuerzo_fisico',
    'demandas_emocionales',
    'demandas_cuantitativas',
    'influencia_trabajo_entorno_extralaboral',
    'demandas_carga_mental',
    'demandas_jornada_trabajo',
    'recompensas_pertenencia_estabilidad',
    'reconocimiento_compensacion'
];
foreach ($dimsB as $d) {
    $r = IntralaboralBScoring::getBaremoDimension($d);
    echo "  $d: " . ($r ? 'OK' : 'NULL') . "\n";
}

// EXTRALABORAL
echo "\n--- DIMENSIONES EXTRALABORAL ---\n";
$dimsExtra = [
    'tiempo_fuera_trabajo',
    'relaciones_familiares',
    'comunicacion_relaciones',
    'situacion_economica',
    'caracteristicas_vivienda',
    'influencia_entorno',
    'desplazamiento'
];
foreach ($dimsExtra as $d) {
    $rA = ExtralaboralScoring::getBaremoDimension($d, 'A');
    $rB = ExtralaboralScoring::getBaremoDimension($d, 'B');
    echo "  $d A: " . ($rA ? 'OK' : 'NULL') . " | B: " . ($rB ? 'OK' : 'NULL') . "\n";
}

echo "\n=== FIN DIAGNÓSTICO ===\n";
