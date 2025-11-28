<?php
/**
 * Componente Áreas con Riesgo Alto y Muy Alto
 *
 * Variables esperadas:
 * - $areas: array - Lista de áreas con riesgo alto [
 *     ['name' => 'Área', 'participants' => 5, 'avgAge' => 35.5, 'chartData' => [...]]
 *   ]
 * - $title: string - Título de la sección (opcional)
 */

$title = $title ?? 'Áreas con Riesgo Alto y Muy Alto';
?>

<div class="high-risk-section">
    <div class="high-risk-title"><?= esc($title) ?></div>

    <?php if (empty($areas)): ?>
        <div class="no-high-risk">
            No hay áreas en riesgo alto o muy alto
        </div>
    <?php else: ?>
        <?php foreach ($areas as $area): ?>
        <div style="margin-bottom: 20px; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
            <div style="font-weight: bold; font-size: 11pt; margin-bottom: 10px;">
                <?= esc($area['name']) ?>
            </div>

            <div style="display: flex; gap: 20px; align-items: center;">
                <!-- Gráfico de pie -->
                <?php if (!empty($area['chartData'])): ?>
                <div style="flex: 1;">
                    <?= view('pdf/_partials/components/pie_chart', [
                        'data' => $area['chartData'],
                        'size' => 120,
                        'showLegend' => false
                    ]) ?>
                </div>
                <?php endif; ?>

                <!-- Información -->
                <div style="flex: 1; text-align: center;">
                    <div style="font-size: 10pt; color: #666;">PARTICIPANTES</div>
                    <div style="font-size: 24pt; font-weight: bold; color: #0066cc;">
                        <?= $area['participants'] ?? 0 ?>
                    </div>

                    <div style="font-size: 10pt; color: #666; margin-top: 10px;">EDAD PROMEDIO</div>
                    <div style="font-size: 20pt; font-weight: bold; color: #0066cc;">
                        <?= number_format($area['avgAge'] ?? 0, 1, ',', '.') ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
