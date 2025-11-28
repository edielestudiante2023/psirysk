<?php
/**
 * Componente Caja de Definición
 *
 * Variables esperadas:
 * - $label: string - Etiqueta (ej: "Definición:")
 * - $content: string - Contenido de la definición
 * - $borderColor: string - Color del borde (opcional)
 */

$label = $label ?? 'Definición:';
$borderColor = $borderColor ?? '#ccc';
?>

<div class="definition-box" style="border-color: <?= $borderColor ?>;">
    <div class="label"><?= esc($label) ?></div>
    <div class="content"><?= $content ?></div>
</div>
