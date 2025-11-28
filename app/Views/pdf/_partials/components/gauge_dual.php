<?php
/**
 * Componente Dual Gauge (2 velocímetros lado a lado: Forma A y Forma B)
 *
 * Variables esperadas:
 * - $title: string - Título de la dimensión
 * - $valueA: float - Valor Forma A (0-100)
 * - $valueB: float - Valor Forma B (0-100)
 * - $rangesA: array - Rangos para Forma A
 * - $rangesB: array - Rangos para Forma B
 * - $interpretationA: string - Texto interpretativo Forma A
 * - $interpretationB: string - Texto interpretativo Forma B
 */

// Función para determinar nivel de riesgo
function getRiskLevel($value, $ranges = null) {
    if ($value <= 0) return ['level' => 'SIN RIESGO', 'color' => '#4CAF50', 'action' => 'MANTENER'];
    if ($value <= 25) return ['level' => 'RIESGO BAJO', 'color' => '#8BC34A', 'action' => 'MANTENER'];
    if ($value <= 50) return ['level' => 'RIESGO MEDIO', 'color' => '#FFEB3B', 'action' => 'REFORZAR'];
    if ($value <= 75) return ['level' => 'RIESGO ALTO', 'color' => '#FF9800', 'action' => 'INTERVENIR'];
    return ['level' => 'RIESGO MUY ALTO', 'color' => '#F44336', 'action' => 'INTERVENIR'];
}

$riskA = getRiskLevel($valueA);
$riskB = getRiskLevel($valueB);
?>

<div class="gauge-dual-container">
    <!-- Gauge Forma A -->
    <div class="gauge-item">
        <?= view('pdf/_partials/components/gauge', [
            'title' => $title,
            'value' => $valueA,
            'ranges' => $rangesA ?? null,
            'forma' => 'A',
            'size' => 'medium'
        ]) ?>

        <div class="interpretation-text" style="font-size: 9pt; margin-top: 10px;">
            Para el cuestionario Tipo A se evidencia que el nivel de riesgo psicosocial se encuentra con un valor de
            <strong><?= number_format($valueA, 2, ',', '.') ?></strong> denominándose
            <span style="color: <?= $riskA['color'] ?>; font-weight: bold;"><?= $riskA['level'] ?></span>,
            por lo que se debe <strong><?= $riskA['action'] ?></strong>
            <?php if (!empty($interpretationA)): ?>
                <?= $interpretationA ?>
            <?php else: ?>
                las intervenciones que se realicen para los cargos profesionales o de jefatura.
            <?php endif; ?>
        </div>
    </div>

    <!-- Gauge Forma B -->
    <div class="gauge-item">
        <?= view('pdf/_partials/components/gauge', [
            'title' => $title,
            'value' => $valueB,
            'ranges' => $rangesB ?? null,
            'forma' => 'B',
            'size' => 'medium'
        ]) ?>

        <div class="interpretation-text" style="font-size: 9pt; margin-top: 10px;">
            Para el cuestionario Tipo B se evidencia que el nivel de riesgo psicosocial se encuentra con un valor de
            <strong><?= number_format($valueB, 2, ',', '.') ?></strong> denominándose
            <span style="color: <?= $riskB['color'] ?>; font-weight: bold;"><?= $riskB['level'] ?></span>,
            por lo que se debe <strong><?= $riskB['action'] ?></strong>
            <?php if (!empty($interpretationB)): ?>
                <?= $interpretationB ?>
            <?php else: ?>
                con acciones que se realicen para los cargos auxiliares u operativos.
            <?php endif; ?>
        </div>
    </div>
</div>
