<?php
/**
 * Componente Gauge (Velocímetro) SVG
 *
 * Variables esperadas:
 * - $title: string - Título del gauge
 * - $value: float - Valor actual (0-100)
 * - $ranges: array - Rangos personalizados (opcional)
 *   Formato 1: ['sin_riesgo' => [0, 3.8], 'riesgo_bajo' => [3.9, 15.4], ...]
 *   Formato 2: [['min' => 0, 'max' => 3.8, 'color' => '#4CAF50'], ...]
 * - $forma: string - 'A' o 'B' (opcional)
 * - $size: string - 'small', 'medium', 'large' (default: medium)
 */

$size = $size ?? 'medium';
$sizes = [
    'small' => ['width' => 150, 'height' => 100],
    'medium' => ['width' => 200, 'height' => 130],
    'large' => ['width' => 250, 'height' => 160],
];

$dimensions = $sizes[$size];
$width = $dimensions['width'];
$height = $dimensions['height'];

// Centro y radio del arco
$cx = $width / 2;
$cy = $height - 10;
$radius = min($width, $height) - 30;

// Determinar el nivel de riesgo y color
$riskColors = [
    'sin_riesgo' => '#4CAF50',
    'riesgo_bajo' => '#8BC34A',
    'riesgo_medio' => '#FFEB3B',
    'riesgo_alto' => '#FF9800',
    'riesgo_muy_alto' => '#F44336',
];

$riskLabels = [
    'sin_riesgo' => 'Sin Riesgo',
    'riesgo_bajo' => 'Riesgo Bajo',
    'riesgo_medio' => 'Riesgo Medio',
    'riesgo_alto' => 'Riesgo Alto',
    'riesgo_muy_alto' => 'Riesgo Muy Alto',
];

// Convertir rangos de formato baremo a formato estándar si es necesario
$normalizedRanges = [];
$baremosForTable = []; // Para la tabla de rangos
if (!empty($ranges)) {
    // Detectar formato - si tiene claves como 'sin_riesgo', es formato baremo
    if (isset($ranges['sin_riesgo']) || isset($ranges['riesgo_bajo'])) {
        // Formato baremo: ['sin_riesgo' => [0, 3.8], ...]
        $baremosForTable = $ranges; // Guardar para la tabla
        foreach ($ranges as $nivel => $rango) {
            $normalizedRanges[] = [
                'min' => $rango[0],
                'max' => $rango[1],
                'color' => $riskColors[$nivel] ?? '#999999',
                'label' => $riskLabels[$nivel] ?? ucfirst(str_replace('_', ' ', $nivel))
            ];
        }
    } else {
        // Ya está en formato estándar
        $normalizedRanges = $ranges;
    }
}

// Rangos por defecto si no se proporcionaron
if (empty($normalizedRanges)) {
    $normalizedRanges = [
        ['min' => 0, 'max' => 0, 'color' => '#4CAF50', 'label' => 'Sin Riesgo'],
        ['min' => 0.1, 'max' => 25, 'color' => '#8BC34A', 'label' => 'Riesgo Bajo'],
        ['min' => 25.1, 'max' => 50, 'color' => '#FFEB3B', 'label' => 'Riesgo Medio'],
        ['min' => 50.1, 'max' => 75, 'color' => '#FF9800', 'label' => 'Riesgo Alto'],
        ['min' => 75.1, 'max' => 100, 'color' => '#F44336', 'label' => 'Riesgo Muy Alto'],
    ];
}

// Determinar color actual basado en el valor
$currentColor = '#9E9E9E';
$currentLabel = 'Sin Datos';
foreach ($normalizedRanges as $range) {
    if ($value >= $range['min'] && $value <= $range['max']) {
        $currentColor = $range['color'];
        $currentLabel = $range['label'];
        break;
    }
}

// Calcular el valor máximo basado en los baremos
$gaugeMaxValue = 100;
if (!empty($ranges)) {
    if (isset($ranges['sin_riesgo']) || isset($ranges['riesgo_bajo'])) {
        $gaugeMaxValue = max(
            $ranges['riesgo_muy_alto'][1] ?? 100,
            $ranges['riesgo_alto'][1] ?? 100,
            $ranges['riesgo_medio'][1] ?? 100
        );
    }
}

// Calcular ángulo de la aguja (180° = arco completo de izquierda a derecha)
$angle = 180 - ($value / $gaugeMaxValue * 180);
$angleRad = deg2rad($angle);

// Punta de la aguja
$needleLength = $radius - 15;
$needleX = $cx + $needleLength * cos($angleRad);
$needleY = $cy - $needleLength * sin($angleRad);

// Función para crear arco SVG
if (!function_exists('describeArc')) {
    function describeArc($cx, $cy, $radius, $startAngle, $endAngle) {
        $start = [
            'x' => $cx + $radius * cos(deg2rad($startAngle)),
            'y' => $cy - $radius * sin(deg2rad($startAngle))
        ];
        $end = [
            'x' => $cx + $radius * cos(deg2rad($endAngle)),
            'y' => $cy - $radius * sin(deg2rad($endAngle))
        ];
        $largeArcFlag = ($endAngle - $startAngle <= 180) ? 0 : 1;

        return "M {$start['x']} {$start['y']} A $radius $radius 0 $largeArcFlag 0 {$end['x']} {$end['y']}";
    }
}
?>

<div class="gauge-container">
    <?php if (!empty($title)): ?>
    <div class="gauge-title"><?= esc($title) ?></div>
    <?php endif; ?>

    <svg class="gauge-svg" viewBox="0 0 <?= $width ?> <?= $height ?>" xmlns="http://www.w3.org/2000/svg">
        <!-- Arcos de colores de fondo basados en baremos reales -->
        <?php
        $arcWidth = 15;

        // Calcular segmentos basados en los rangos reales
        $maxValue = 100;
        if (!empty($baremosForTable)) {
            // Obtener el máximo valor del baremo
            $maxValue = max(
                $baremosForTable['riesgo_muy_alto'][1] ?? 100,
                $baremosForTable['riesgo_alto'][1] ?? 100,
                $baremosForTable['riesgo_medio'][1] ?? 100
            );
        }

        // Función para convertir valor a ángulo (0-100 -> 180-0 grados)
        $valueToAngle = function($val) use ($maxValue) {
            return 180 - ($val / $maxValue * 180);
        };

        // Construir segmentos desde los baremos
        $segments = [];
        $riskOrder = ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'];

        foreach ($riskOrder as $nivel) {
            if (isset($baremosForTable[$nivel])) {
                $min = $baremosForTable[$nivel][0];
                $max = $baremosForTable[$nivel][1];
                $segments[] = [
                    'start' => $valueToAngle($min),
                    'end' => $valueToAngle($max),
                    'color' => $riskColors[$nivel]
                ];
            }
        }

        // Si no hay baremos, usar segmentos por defecto
        if (empty($segments)) {
            $segments = [
                ['start' => 180, 'end' => 144, 'color' => '#4CAF50'],
                ['start' => 144, 'end' => 108, 'color' => '#8BC34A'],
                ['start' => 108, 'end' => 72, 'color' => '#FFEB3B'],
                ['start' => 72, 'end' => 36, 'color' => '#FF9800'],
                ['start' => 36, 'end' => 0, 'color' => '#F44336'],
            ];
        }

        foreach ($segments as $segment):
            $path = describeArc($cx, $cy, $radius, $segment['end'], $segment['start']);
        ?>
        <path d="<?= $path ?>" fill="none" stroke="<?= $segment['color'] ?>" stroke-width="<?= $arcWidth ?>" stroke-linecap="butt"/>
        <?php endforeach; ?>

        <!-- Aguja -->
        <line x1="<?= $cx ?>" y1="<?= $cy ?>" x2="<?= $needleX ?>" y2="<?= $needleY ?>"
              stroke="#333" stroke-width="3" stroke-linecap="round"/>

        <!-- Centro de la aguja -->
        <circle cx="<?= $cx ?>" cy="<?= $cy ?>" r="8" fill="#333"/>
        <circle cx="<?= $cx ?>" cy="<?= $cy ?>" r="4" fill="#666"/>

        <!-- Valor en el centro -->
        <text x="<?= $cx ?>" y="<?= $cy - 25 ?>" text-anchor="middle" font-size="16" font-weight="bold" fill="<?= $currentColor ?>">
            <?= number_format($value, 2, ',', '.') ?>
        </text>

        <!-- Etiqueta "Puntaje Obtenido" -->
        <text x="<?= $cx ?>" y="<?= $cy - 10 ?>" text-anchor="middle" font-size="8" fill="#666">
            Puntaje Obtenido
        </text>
    </svg>

    <!-- Tabla de rangos debajo del gauge -->
    <table class="risk-ranges-table">
        <tr>
            <td colspan="2" class="range-header">1-Sin Riesgo o Riesgo Despreciable</td>
            <td class="range-header">2-Riesgo Bajo</td>
            <td class="range-header">3-Riesgo Medio</td>
            <td class="range-header">4-Riesgo Alto</td>
            <td class="range-header">5-Riesgo Muy Alto</td>
        </tr>
        <tr>
            <td class="risk-sin-riesgo">límite ↓</td>
            <td class="risk-sin-riesgo"><?= isset($baremosForTable['sin_riesgo']) ? $baremosForTable['sin_riesgo'][0] : 0 ?></td>
            <td class="risk-bajo"><?= isset($baremosForTable['riesgo_bajo']) ? $baremosForTable['riesgo_bajo'][0] : 0.1 ?></td>
            <td class="risk-medio"><?= isset($baremosForTable['riesgo_medio']) ? $baremosForTable['riesgo_medio'][0] : 25.1 ?></td>
            <td class="risk-alto"><?= isset($baremosForTable['riesgo_alto']) ? $baremosForTable['riesgo_alto'][0] : 50.1 ?></td>
            <td class="risk-muy-alto"><?= isset($baremosForTable['riesgo_muy_alto']) ? $baremosForTable['riesgo_muy_alto'][0] : 75.1 ?></td>
        </tr>
        <tr>
            <td class="risk-sin-riesgo">límite ↑</td>
            <td class="risk-sin-riesgo"><?= isset($baremosForTable['sin_riesgo']) ? $baremosForTable['sin_riesgo'][1] : 0 ?></td>
            <td class="risk-bajo"><?= isset($baremosForTable['riesgo_bajo']) ? $baremosForTable['riesgo_bajo'][1] : 25 ?></td>
            <td class="risk-medio"><?= isset($baremosForTable['riesgo_medio']) ? $baremosForTable['riesgo_medio'][1] : 50 ?></td>
            <td class="risk-alto"><?= isset($baremosForTable['riesgo_alto']) ? $baremosForTable['riesgo_alto'][1] : 75 ?></td>
            <td class="risk-muy-alto"><?= isset($baremosForTable['riesgo_muy_alto']) ? $baremosForTable['riesgo_muy_alto'][1] : 100 ?></td>
        </tr>
    </table>
</div>
