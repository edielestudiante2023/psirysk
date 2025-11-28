<?php
/**
 * Componente Gráfico de Barras Apiladas
 *
 * Variables esperadas:
 * - $title: string - Título del gráfico
 * - $data: array - Datos por categoría [
 *     'FORMA A' => ['muy_alto' => 10, 'alto' => 20, 'medio' => 30, 'bajo' => 25, 'sin_riesgo' => 15],
 *     'FORMA B' => [...],
 *     'CONJUNTO' => [...]
 *   ]
 * - $showLegend: bool - Mostrar leyenda (default: true)
 * - $height: int - Altura de las barras en px (default: 200)
 */

$showLegend = $showLegend ?? true;
$height = $height ?? 200;

$colors = [
    'muy_alto' => '#F44336',
    'alto' => '#FF9800',
    'medio' => '#FFEB3B',
    'bajo' => '#8BC34A',
    'sin_riesgo' => '#4CAF50',
];

$labels = [
    'muy_alto' => 'Riesgo alto y muy alto',
    'alto' => 'Riesgo alto',
    'medio' => 'Riesgo medio',
    'bajo' => 'Bajo y sin riesgo',
    'sin_riesgo' => 'Sin riesgo',
];

// Calcular el máximo para escalar
$maxValue = 100;
?>

<div class="stacked-bar-container">
    <?php if (!empty($title)): ?>
    <div class="stacked-bar-title"><?= esc($title) ?></div>
    <?php endif; ?>

    <div style="display: flex; justify-content: space-around; align-items: flex-end; height: <?= $height ?>px; padding: 10px 0;">
        <?php foreach ($data as $category => $values): ?>
        <div style="text-align: center; width: <?= (100 / count($data)) - 5 ?>%;">
            <!-- Barra apilada vertical -->
            <div style="display: flex; flex-direction: column-reverse; height: <?= $height - 30 ?>px; border: 1px solid #ccc;">
                <?php
                $total = array_sum($values);
                foreach ($values as $level => $value):
                    if ($value <= 0) continue;
                    $percentage = ($total > 0) ? ($value / $total * 100) : 0;
                    $segmentHeight = ($value / $maxValue * 100);
                ?>
                <div style="
                    height: <?= $segmentHeight ?>%;
                    background-color: <?= $colors[$level] ?? '#999' ?>;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 8pt;
                    font-weight: bold;
                    color: <?= in_array($level, ['medio']) ? '#333' : 'white' ?>;
                    min-height: <?= $value > 0 ? '15px' : '0' ?>;
                ">
                    <?php if ($percentage >= 10): ?>
                        <?= number_format($percentage, 1) ?>%
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <!-- Etiqueta de categoría -->
            <div style="font-size: 9pt; font-weight: bold; margin-top: 5px;">
                <?= esc($category) ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if ($showLegend): ?>
    <div class="stacked-bar-legend">
        <div class="legend-item">
            <div class="legend-color" style="background-color: <?= $colors['muy_alto'] ?>;"></div>
            <span>Riesgo alto y muy alto</span>
        </div>
        <div class="legend-item">
            <div class="legend-color" style="background-color: <?= $colors['medio'] ?>;"></div>
            <span>Riesgo medio</span>
        </div>
        <div class="legend-item">
            <div class="legend-color" style="background-color: <?= $colors['sin_riesgo'] ?>;"></div>
            <span>Bajo y sin riesgo</span>
        </div>
    </div>
    <?php endif; ?>
</div>
