<?php

/**
 * Script de Validacion Pre-Migracion de Baremos
 *
 * OBJETIVO: Comparar los baremos hardcodeados en ReportsController y
 * BatteryServiceController contra los valores de las librerias autorizadas
 * ANTES de realizar la migracion.
 *
 * EJECUCION:
 *   cd c:\xampp\htdocs\psyrisk
 *   php scripts/validate_baremos.php
 *
 * SALIDA:
 *   - Lista de baremos que coinciden (OK)
 *   - Lista de discrepancias (ERROR)
 *   - Recomendaciones para resolver discrepancias
 */

// Cargar el autoloader de CodeIgniter
require_once __DIR__ . '/../vendor/autoload.php';

// Inicializar CodeIgniter para poder usar las librerias
$paths = new \Config\Paths();
define('FCPATH', __DIR__ . '/../public/');
chdir(FCPATH);

// Cargar librerias
use App\Libraries\IntralaboralAScoring;
use App\Libraries\IntralaboralBScoring;
use App\Libraries\ExtralaboralScoring;
use App\Libraries\EstresScoring;

echo "\n";
echo "╔══════════════════════════════════════════════════════════════════════════════╗\n";
echo "║            VALIDACION PRE-MIGRACION DE BAREMOS - PSYRISK                     ║\n";
echo "║                                                                              ║\n";
echo "║  Comparando baremos hardcodeados vs librerias autorizadas                    ║\n";
echo "╚══════════════════════════════════════════════════════════════════════════════╝\n\n";

$discrepancias = [];
$ok = 0;
$errors = 0;

// =============================================================================
// FUNCION HELPER PARA COMPARAR BAREMOS
// =============================================================================

function compararBaremo($nombre, $hardcoded, $libreria, &$discrepancias, &$ok, &$errors)
{
    $match = true;

    foreach ($hardcoded as $nivel => $rangoHc) {
        if (!isset($libreria[$nivel])) {
            $match = false;
            break;
        }

        $rangoLib = $libreria[$nivel];

        // Comparar con tolerancia para floats
        if (abs($rangoHc[0] - $rangoLib[0]) > 0.01 || abs($rangoHc[1] - $rangoLib[1]) > 0.01) {
            $match = false;
            break;
        }
    }

    if ($match) {
        echo "  ✓ {$nombre}\n";
        $ok++;
    } else {
        echo "  ✗ {$nombre} - DISCREPANCIA\n";
        $discrepancias[] = [
            'nombre' => $nombre,
            'hardcoded' => $hardcoded,
            'libreria' => $libreria
        ];
        $errors++;
    }
}

// =============================================================================
// 1. INTRALABORAL TOTAL
// =============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "1. INTRALABORAL TOTAL (Tabla 33)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

// Forma A - Hardcoded en ReportsController linea ~1450
$hardcodedTotalA = [
    'sin_riesgo' => [0.0, 19.7],
    'riesgo_bajo' => [19.8, 25.8],
    'riesgo_medio' => [25.9, 31.5],
    'riesgo_alto' => [31.6, 38.0],
    'riesgo_muy_alto' => [38.1, 100.0]
];

compararBaremo(
    'Intralaboral Total Forma A',
    $hardcodedTotalA,
    IntralaboralAScoring::getBaremoTotal(),
    $discrepancias, $ok, $errors
);

// Forma B
$hardcodedTotalB = [
    'sin_riesgo' => [0.0, 20.6],
    'riesgo_bajo' => [20.7, 26.0],
    'riesgo_medio' => [26.1, 31.2],
    'riesgo_alto' => [31.3, 38.7],
    'riesgo_muy_alto' => [38.8, 100.0]
];

compararBaremo(
    'Intralaboral Total Forma B',
    $hardcodedTotalB,
    IntralaboralBScoring::getBaremoTotal(),
    $discrepancias, $ok, $errors
);

// =============================================================================
// 2. DOMINIOS FORMA A (Tabla 31)
// =============================================================================

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "2. DOMINIOS FORMA A (Tabla 31)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$hardcodedDominiosA = [
    'liderazgo_relaciones_sociales' => [
        'sin_riesgo' => [0.0, 9.1],
        'riesgo_bajo' => [9.2, 17.7],
        'riesgo_medio' => [17.8, 25.6],
        'riesgo_alto' => [25.7, 34.8],
        'riesgo_muy_alto' => [34.9, 100.0]
    ],
    'control' => [
        'sin_riesgo' => [0.0, 10.7],
        'riesgo_bajo' => [10.8, 19.0],
        'riesgo_medio' => [19.1, 29.8],
        'riesgo_alto' => [29.9, 40.5],
        'riesgo_muy_alto' => [40.6, 100.0]
    ],
    'demandas' => [
        'sin_riesgo' => [0.0, 28.5],
        'riesgo_bajo' => [28.6, 35.0],
        'riesgo_medio' => [35.1, 41.5],
        'riesgo_alto' => [41.6, 47.5],
        'riesgo_muy_alto' => [47.6, 100.0]
    ],
    'recompensas' => [
        'sin_riesgo' => [0.0, 4.5],
        'riesgo_bajo' => [4.6, 11.4],
        'riesgo_medio' => [11.5, 20.5],
        'riesgo_alto' => [20.6, 29.5],
        'riesgo_muy_alto' => [29.6, 100.0]
    ]
];

foreach ($hardcodedDominiosA as $dominio => $baremo) {
    compararBaremo(
        "Dominio {$dominio} (A)",
        $baremo,
        IntralaboralAScoring::getBaremoDominio($dominio),
        $discrepancias, $ok, $errors
    );
}

// =============================================================================
// 3. DOMINIOS FORMA B (Tabla 32)
// =============================================================================

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "3. DOMINIOS FORMA B (Tabla 32)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$hardcodedDominiosB = [
    'liderazgo_relaciones_sociales' => [
        'sin_riesgo' => [0.0, 10.0],
        'riesgo_bajo' => [10.1, 17.5],
        'riesgo_medio' => [17.6, 25.0],
        'riesgo_alto' => [25.1, 35.0],
        'riesgo_muy_alto' => [35.1, 100.0]
    ],
    'control' => [
        'sin_riesgo' => [0.0, 8.8],
        'riesgo_bajo' => [8.9, 16.3],
        'riesgo_medio' => [16.4, 23.8],
        'riesgo_alto' => [23.9, 31.3],
        'riesgo_muy_alto' => [31.4, 100.0]
    ],
    'demandas' => [
        'sin_riesgo' => [0.0, 26.9],
        'riesgo_bajo' => [27.0, 33.3],
        'riesgo_medio' => [33.4, 37.8],
        'riesgo_alto' => [37.9, 44.2],
        'riesgo_muy_alto' => [44.3, 100.0]
    ],
    'recompensas' => [
        'sin_riesgo' => [0.0, 2.5],
        'riesgo_bajo' => [2.6, 10.0],
        'riesgo_medio' => [10.1, 17.5],
        'riesgo_alto' => [17.6, 27.5],
        'riesgo_muy_alto' => [27.6, 100.0]
    ]
];

foreach ($hardcodedDominiosB as $dominio => $baremo) {
    compararBaremo(
        "Dominio {$dominio} (B)",
        $baremo,
        IntralaboralBScoring::getBaremoDominio($dominio),
        $discrepancias, $ok, $errors
    );
}

// =============================================================================
// 4. EXTRALABORAL TOTAL (Tablas 17 y 18)
// =============================================================================

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "4. EXTRALABORAL TOTAL (Tablas 17 y 18)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$hardcodedExtralaboralJefes = [
    'sin_riesgo' => [0.0, 11.3],
    'riesgo_bajo' => [11.4, 16.9],
    'riesgo_medio' => [17.0, 22.6],
    'riesgo_alto' => [22.7, 29.0],
    'riesgo_muy_alto' => [29.1, 100.0]
];

compararBaremo(
    'Extralaboral Total Jefes (Tabla 17)',
    $hardcodedExtralaboralJefes,
    ExtralaboralScoring::getBaremoTotal('A'),
    $discrepancias, $ok, $errors
);

$hardcodedExtralaboralAuxiliares = [
    'sin_riesgo' => [0.0, 12.9],
    'riesgo_bajo' => [13.0, 17.7],
    'riesgo_medio' => [17.8, 24.2],
    'riesgo_alto' => [24.3, 32.3],
    'riesgo_muy_alto' => [32.4, 100.0]
];

compararBaremo(
    'Extralaboral Total Auxiliares (Tabla 18)',
    $hardcodedExtralaboralAuxiliares,
    ExtralaboralScoring::getBaremoTotal('B'),
    $discrepancias, $ok, $errors
);

// =============================================================================
// 5. ESTRES (Tabla 6)
// =============================================================================

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "5. ESTRES (Tabla 6)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$hardcodedEstresJefes = [
    'muy_bajo' => [0.0, 7.8],
    'bajo' => [7.9, 12.6],
    'medio' => [12.7, 17.7],
    'alto' => [17.8, 25.0],
    'muy_alto' => [25.1, 100.0]
];

compararBaremo(
    'Estres Jefes (Tabla 6)',
    $hardcodedEstresJefes,
    EstresScoring::getBaremoA(),
    $discrepancias, $ok, $errors
);

$hardcodedEstresAuxiliares = [
    'muy_bajo' => [0.0, 6.5],
    'bajo' => [6.6, 11.8],
    'medio' => [11.9, 17.0],
    'alto' => [17.1, 23.4],
    'muy_alto' => [23.5, 100.0]
];

compararBaremo(
    'Estres Auxiliares (Tabla 6)',
    $hardcodedEstresAuxiliares,
    EstresScoring::getBaremoB(),
    $discrepancias, $ok, $errors
);

// =============================================================================
// RESUMEN
// =============================================================================

echo "\n";
echo "╔══════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                              RESUMEN                                         ║\n";
echo "╚══════════════════════════════════════════════════════════════════════════════╝\n\n";

echo "  Baremos validados:  " . ($ok + $errors) . "\n";
echo "  ✓ Coinciden:        {$ok}\n";
echo "  ✗ Discrepancias:    {$errors}\n\n";

if (empty($discrepancias)) {
    echo "╔══════════════════════════════════════════════════════════════════════════════╗\n";
    echo "║  ✓ TODOS LOS BAREMOS COINCIDEN - SEGURO PARA MIGRAR                         ║\n";
    echo "╚══════════════════════════════════════════════════════════════════════════════╝\n";
    exit(0);
} else {
    echo "╔══════════════════════════════════════════════════════════════════════════════╗\n";
    echo "║  ✗ SE ENCONTRARON DISCREPANCIAS - REVISAR ANTES DE MIGRAR                   ║\n";
    echo "╚══════════════════════════════════════════════════════════════════════════════╝\n\n";

    echo "DETALLE DE DISCREPANCIAS:\n";
    echo str_repeat('─', 80) . "\n";

    foreach ($discrepancias as $d) {
        echo "\n► {$d['nombre']}\n";
        echo "  Hardcoded (Controller):\n";
        foreach ($d['hardcoded'] as $nivel => $rango) {
            echo "    {$nivel}: [{$rango[0]}, {$rango[1]}]\n";
        }
        echo "  Libreria (Fuente Unica):\n";
        foreach ($d['libreria'] as $nivel => $rango) {
            echo "    {$nivel}: [{$rango[0]}, {$rango[1]}]\n";
        }
    }

    echo "\n";
    echo "RECOMENDACION:\n";
    echo str_repeat('─', 80) . "\n";
    echo "1. Verificar cuales valores son correctos segun la Resolucion 2764/2022\n";
    echo "2. Si la libreria es correcta: Migrar usando la libreria\n";
    echo "3. Si el hardcoded es correcto: Actualizar la libreria primero\n";
    echo "4. Documentar cualquier decision en README_BAREMOS.md\n\n";

    exit(1);
}
