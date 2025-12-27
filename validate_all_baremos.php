<?php
require_once 'vendor/autoload.php';

use App\Libraries\IntralaboralAScoring;
use App\Libraries\IntralaboralBScoring;
use App\Libraries\ExtralaboralScoring;
use App\Libraries\EstresScoring;

echo "=== VALIDACIÓN DE BAREMOS ===\n\n";

// INTRALABORAL A - Dimensiones
$dimensionesA = [
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
    'reconocimiento_compensacion',
];

echo "INTRALABORAL FORMA A - Dimensiones:\n";
foreach ($dimensionesA as $dim) {
    $baremo = IntralaboralAScoring::getBaremoDimension($dim);
    echo "  $dim: " . ($baremo ? "✓" : "✗ NULL") . "\n";
}

// INTRALABORAL A - Dominios
$dominiosA = [
    'liderazgo_relaciones_sociales',
    'control',
    'demandas',
    'recompensas',
];

echo "\nINTRALABORAL FORMA A - Dominios:\n";
foreach ($dominiosA as $dom) {
    $baremo = IntralaboralAScoring::getBaremoDominio($dom);
    echo "  $dom: " . ($baremo ? "✓" : "✗ NULL") . "\n";
}

// INTRALABORAL A - Total
echo "\nINTRALABORAL FORMA A - Total:\n";
$baremo = IntralaboralAScoring::getBaremoTotal();
echo "  total: " . ($baremo ? "✓" : "✗ NULL") . "\n";

// INTRALABORAL B - Dimensiones (mismas que A, excepto relacion_con_colaboradores)
echo "\nINTRALABORAL FORMA B - Dimensiones:\n";
foreach ($dimensionesA as $dim) {
    if ($dim === 'relacion_con_colaboradores') continue; // No existe en Forma B
    $baremo = IntralaboralBScoring::getBaremoDimension($dim);
    echo "  $dim: " . ($baremo ? "✓" : "✗ NULL") . "\n";
}

// EXTRALABORAL - Dimensiones
$dimensionesExtra = [
    'tiempo_fuera_trabajo',
    'relaciones_familiares',
    'comunicacion_relaciones',
    'situacion_economica',
    'caracteristicas_vivienda',
    'influencia_entorno',
    'desplazamiento',
];

echo "\nEXTRALABORAL - Dimensiones:\n";
foreach ($dimensionesExtra as $dim) {
    $baremo = ExtralaboralScoring::getBaremoDimension($dim);
    echo "  $dim: " . ($baremo ? "✓" : "✗ NULL") . "\n";
}

// EXTRALABORAL - Total
echo "\nEXTRALABORAL - Total:\n";
$baremo = ExtralaboralScoring::getBaremoTotal();
echo "  total: " . ($baremo ? "✓" : "✗ NULL") . "\n";

// ESTRÉS - Total
echo "\nESTRÉS - Total:\n";
$baremoA = EstresScoring::getBaremoA();
echo "  Forma A: " . ($baremoA ? "✓" : "✗ NULL") . "\n";
$baremoB = EstresScoring::getBaremoB();
echo "  Forma B: " . ($baremoB ? "✓" : "✗ NULL") . "\n";

echo "\n=== VALIDACIÓN COMPLETA ===\n";
