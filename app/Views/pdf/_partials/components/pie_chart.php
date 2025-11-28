<?php
/**
 * Componente Gráfico Circular (Pie Chart) SVG
 *
 * Variables esperadas:
 * - $title: string - Título del gráfico
 * - $data: array - Datos ['label' => value, ...]
 * - $colors: array - Colores personalizados (opcional)
 * - $size: int - Tamaño del SVG (default: 150)
 * - $showLabels: bool - Mostrar etiquetas en el gráfico (default: true)
 * - $showLegend: bool - Mostrar leyenda (default: true)
 * - $centerText: string - Texto en el centro (para donut chart)
 */

$size = $size ?? 150;
$showLabels = $showLabels ?? true;
$showLegend = $showLegend ?? true;

$defaultColors = [
    '#4CAF50', '#8BC34A', '#FFEB3B', '#FF9800', '#F44336',
    '#9C27B0', '#3F51B5', '#00BCD4', '#795548', '#607D8B'
];

$colors = $colors ?? $defaultColors;

// Centro y radio
$cx = $size / 2;
$cy = $size / 2;
$radius = ($size / 2) - 10;

// Si es donut
$innerRadius = isset($centerText) ? $radius * 0.6 : 0;

// Calcular total y porcentajes
$total = array_sum($data);
$startAngle = -90; // Empezar desde arriba

/**
 * Función para crear path de sector
 */
function createPieSlice($cx, $cy, $radius, $innerRadius, $startAngle, $endAngle) {
    $startRad = deg2rad($startAngle);
    $endRad = deg2rad($endAngle);

    $x1 = $cx + $radius * cos($startRad);
    $y1 = $cy + $radius * sin($startRad);
    $x2 = $cx + $radius * cos($endRad);
    $y2 = $cy + $radius * sin($endRad);

    $largeArc = ($endAngle - $startAngle > 180) ? 1 : 0;

    if ($innerRadius > 0) {
        // Donut
        $x3 = $cx + $innerRadius * cos($endRad);
        $y3 = $cy + $innerRadius * sin($endRad);
        $x4 = $cx + $innerRadius * cos($startRad);
        $y4 = $cy + $innerRadius * sin($startRad);

        return "M $x1 $y1 A $radius $radius 0 $largeArc 1 $x2 $y2 L $x3 $y3 A $innerRadius $innerRadius 0 $largeArc 0 $x4 $y4 Z";
    }

    return "M $cx $cy L $x1 $y1 A $radius $radius 0 $largeArc 1 $x2 $y2 Z";
}
?>

<div class="pie-chart-container">
    <?php if (!empty($title)): ?>
    <div style="font-size: 10pt; font-weight: bold; text-align: center; margin-bottom: 10px;">
        <?= esc($title) ?>
    </div>
    <?php endif; ?>

    <svg class="pie-chart-svg" viewBox="0 0 <?= $size ?> <?= $size ?>" xmlns="http://www.w3.org/2000/svg" style="max-width: <?= $size ?>px;">
        <?php
        $i = 0;
        $currentAngle = $startAngle;

        foreach ($data as $label => $value):
            if ($value <= 0) {
                $i++;
                continue;
            }

            $percentage = ($total > 0) ? ($value / $total * 100) : 0;
            $sliceAngle = ($percentage / 100) * 360;
            $endAngle = $currentAngle + $sliceAngle;

            // Evitar que el ángulo final sea exactamente igual al inicial (círculo completo)
            if ($sliceAngle >= 359.9) {
                $endAngle = $currentAngle + 359.9;
            }

            $path = createPieSlice($cx, $cy, $radius, $innerRadius, $currentAngle, $endAngle);
            $color = $colors[$i % count($colors)];

            // Posición para la etiqueta (en el medio del arco)
            $midAngle = $currentAngle + ($sliceAngle / 2);
            $midRad = deg2rad($midAngle);
            $labelRadius = ($radius + $innerRadius) / 2;
            $labelX = $cx + $labelRadius * 0.7 * cos($midRad);
            $labelY = $cy + $labelRadius * 0.7 * sin($midRad);
        ?>
            <path d="<?= $path ?>" fill="<?= $color ?>" stroke="white" stroke-width="1"/>

            <?php if ($showLabels && $percentage >= 5): ?>
            <text x="<?= $labelX ?>" y="<?= $labelY ?>" text-anchor="middle" dominant-baseline="middle"
                  font-size="9" font-weight="bold" fill="white" style="text-shadow: 1px 1px 1px rgba(0,0,0,0.5);">
                <?= number_format($percentage, 0) ?>%
            </text>
            <?php endif; ?>

        <?php
            $currentAngle = $endAngle;
            $i++;
        endforeach;
        ?>

        <?php if (isset($centerText)): ?>
        <text x="<?= $cx ?>" y="<?= $cy ?>" text-anchor="middle" dominant-baseline="middle"
              font-size="12" font-weight="bold" fill="#333">
            <?= esc($centerText) ?>
        </text>
        <?php endif; ?>
    </svg>

    <?php if ($showLegend): ?>
    <div class="pie-chart-legend">
        <?php
        $i = 0;
        foreach ($data as $label => $value):
            if ($value <= 0) {
                $i++;
                continue;
            }
            $color = $colors[$i % count($colors)];
            $percentage = ($total > 0) ? ($value / $total * 100) : 0;
        ?>
        <div class="legend-item">
            <div class="legend-color" style="background-color: <?= $color ?>;"></div>
            <span><?= esc($label) ?> (<?= number_format($percentage, 1) ?>%)</span>
        </div>
        <?php
            $i++;
        endforeach;
        ?>
    </div>
    <?php endif; ?>
</div>
