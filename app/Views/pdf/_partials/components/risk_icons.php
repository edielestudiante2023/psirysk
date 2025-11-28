<?php
/**
 * Componente Iconos de Niveles de Riesgo (5-2)
 *
 * Variables esperadas:
 * - $riesgoAlto: int - Cantidad en riesgo alto/muy alto
 * - $riesgoMedio: int - Cantidad en riesgo medio
 * - $showLabels: bool - Mostrar etiquetas (default: true)
 */

$showLabels = $showLabels ?? true;
?>

<div class="risk-icons-container">
    <!-- Icono Riesgo Alto/Muy Alto -->
    <div style="display: flex; align-items: center;">
        <div class="risk-icon" style="background-color: #F44336;">
            <?= $riesgoAlto ?? 0 ?>
        </div>
        <?php if ($showLabels): ?>
        <span class="risk-icon-label">Riesgo alto y muy alto</span>
        <?php endif; ?>
    </div>

    <!-- Icono Riesgo Medio -->
    <div style="display: flex; align-items: center;">
        <div class="risk-icon" style="background-color: #FFEB3B; color: #333;">
            <?= $riesgoMedio ?? 0 ?>
        </div>
        <?php if ($showLabels): ?>
        <span class="risk-icon-label">Riesgo medio</span>
        <?php endif; ?>
    </div>
</div>
