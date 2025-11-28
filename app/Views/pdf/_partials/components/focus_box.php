<?php
/**
 * Componente Caja de Foco Objetivo
 *
 * Variables esperadas:
 * - $targetGroup: string - Grupo objetivo (ej: "Todos los cargos")
 * - $action: string - Acción recomendada (ej: "Acción continuar con los programas actuales")
 * - $description: string - Descripción detallada
 */
?>

<div class="focus-box">
    <div class="focus-title">
        <strong>Foco objetivo:</strong> <?= esc($targetGroup ?? 'Todos los cargos') ?> –
        <span style="color: #0066cc;"><?= esc($action ?? 'Acción recomendada') ?></span>
    </div>
    <?php if (!empty($description)): ?>
    <div class="focus-content">
        <?= $description ?>
    </div>
    <?php endif; ?>
</div>
