<?php
/**
 * Partial para mostrar tablas de distribución demográfica
 * Espera recibir: $data (array de elementos con 'label', 'count', 'percentage')
 */

// DEBUG EXHAUSTIVO: Verificar todas las variables disponibles
if (ENVIRONMENT === 'development') {
    log_message('debug', "========================================");
    log_message('debug', "=== PARTIAL _distribution_table DEBUG ===");
    log_message('debug', "========================================");

    // Verificar si $data existe
    log_message('debug', "Variable \$data isset: " . (isset($data) ? 'YES' : 'NO'));

    if (isset($data)) {
        log_message('debug', "Variable \$data type: " . gettype($data));

        if (is_array($data)) {
            log_message('debug', "Variable \$data count: " . count($data));
            log_message('debug', "Variable \$data is_empty: " . (empty($data) ? 'YES' : 'NO'));

            if (count($data) > 0) {
                log_message('debug', "First element: " . json_encode($data[0] ?? 'N/A'));
                log_message('debug', "Full data: " . json_encode($data));
            }
        } else {
            log_message('debug', "Variable \$data value: " . print_r($data, true));
        }
    }

    // Mostrar todas las variables definidas en este scope
    $definedVars = get_defined_vars();
    log_message('debug', "Defined variables in partial: " . implode(', ', array_keys($definedVars)));
}

// Verificar que $data existe y es un array con elementos
$hasData = isset($data) && is_array($data) && count($data) > 0;
?>
<?php if ($hasData): ?>
<table class="table table-sm table-hover mb-0">
    <thead class="table-light">
        <tr>
            <th>Categoría</th>
            <th class="text-center" style="width: 80px;">Cantidad</th>
            <th class="text-center" style="width: 100px;">Participación</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $colors = ['bg-primary', 'bg-success', 'bg-info', 'bg-warning', 'bg-danger', 'bg-secondary'];
        $colorIndex = 0;
        foreach ($data as $item):
            $barColor = $colors[$colorIndex % count($colors)];
            $colorIndex++;
        ?>
        <tr>
            <td>
                <span class="fw-medium"><?= esc($item['label'] ?? 'Sin dato') ?></span>
            </td>
            <td class="text-center">
                <span class="fw-bold"><?= $item['count'] ?? 0 ?></span>
            </td>
            <td class="text-center">
                <span class="badge <?= $barColor ?> px-3"><?= number_format($item['percentage'] ?? 0, 1) ?>%</span>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php else: ?>
<div class="alert alert-secondary mb-0">
    <i class="fas fa-info-circle me-2"></i>No hay datos disponibles para esta distribución.
    <?php if (ENVIRONMENT === 'development'): ?>
    <br>
    <small class="text-muted">
        <strong>DEBUG Partial:</strong>
        $data isset: <?= isset($data) ? 'SI' : 'NO' ?> |
        type: <?= isset($data) ? gettype($data) : 'N/A' ?> |
        <?php if (isset($data)): ?>
        count: <?= is_array($data) ? count($data) : 'N/A' ?> |
        empty: <?= empty($data) ? 'SI' : 'NO' ?>
        <?php endif; ?>
    </small>
    <?php endif; ?>
</div>
<?php endif; ?>
