<?php if (!empty($riskyDimensions)): ?>
<?php
// Agrupar dimensiones por categoría
$grouped = [
    'intralaboral' => [],
    'extralaboral' => [],
    'estres' => []
];

foreach ($riskyDimensions as $dimension) {
    $code = $dimension['code'];

    // Clasificar por código de dimensión
    if ($code === 'estres') {
        $grouped['estres'][] = $dimension;
    } elseif (strpos($code, 'tiempo_fuera') !== false ||
              strpos($code, 'relaciones_familiares') !== false ||
              strpos($code, 'comunicacion_relaciones') !== false ||
              strpos($code, 'situacion_economica') !== false ||
              strpos($code, 'caracteristicas_vivienda') !== false ||
              strpos($code, 'influencia_entorno_extralaboral') !== false ||
              strpos($code, 'desplazamiento_vivienda') !== false) {
        $grouped['extralaboral'][] = $dimension;
    } else {
        $grouped['intralaboral'][] = $dimension;
    }
}

$totalIntralaboral = count($grouped['intralaboral']);
$totalExtralaboral = count($grouped['extralaboral']);
$totalEstres = count($grouped['estres']);
?>

<!-- Sección de Recomendaciones y Planes de Acción -->
<div class="card shadow-sm mb-4 border-warning">
    <div class="card-header bg-gradient text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Dimensiones en Riesgo - Planes de Acción Disponibles
                </h5>
                <p class="mb-0 small">Se han detectado <?= count($riskyDimensions) ?> dimensión(es) que requieren intervención</p>
            </div>
            <div class="text-end">
                <span class="badge bg-light text-dark fs-6 px-3 py-2">
                    <i class="fas fa-briefcase me-1"></i> Intralaboral: <?= $totalIntralaboral ?>
                </span>
                <span class="badge bg-light text-dark fs-6 px-3 py-2 ms-2">
                    <i class="fas fa-home me-1"></i> Extralaboral: <?= $totalExtralaboral ?>
                </span>
                <span class="badge bg-light text-dark fs-6 px-3 py-2 ms-2">
                    <i class="fas fa-heartbeat me-1"></i> Estrés: <?= $totalEstres ?>
                </span>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="alert alert-info mb-4">
            <i class="fas fa-info-circle me-2"></i>
            <strong>¿Qué hacer ahora?</strong> Las siguientes dimensiones presentan niveles de riesgo MEDIO (amarillo) o ALTO/MUY ALTO (rojo).
            Haga clic en "Ver Plan de Intervención" para acceder a las recomendaciones del equipo de expertos Cycloid Talent SAS.
        </div>

        <!-- INTRALABORAL -->
        <?php if ($totalIntralaboral > 0): ?>
        <div class="dimension-category mb-4">
            <div class="category-header mb-3 pb-2 border-bottom border-primary">
                <h5 class="text-primary mb-1">
                    <i class="fas fa-briefcase me-2"></i>
                    Factores de Riesgo Intralaboral
                </h5>
                <p class="text-muted small mb-0">
                    <?= $totalIntralaboral ?> dimensión(es) intralaboral(es) en riesgo |
                    <strong>Total posible:</strong> 19 dimensiones (Forma A) / 16 dimensiones (Forma B)
                </p>
            </div>
            <div class="row">
                <?php foreach ($grouped['intralaboral'] as $dimension): ?>
                    <div class="col-12 col-md-6 col-lg-4 col-xl-3 mb-3">
                        <div class="card h-100 border-<?= $dimension['level_color'] ?> shadow-sm" style="border-width: 2px;">
                            <div class="card-body p-3">
                                <!-- Risk Badge -->
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge bg-<?= $dimension['level_color'] ?> mb-2">
                                        <?= $dimension['level_label'] ?>
                                    </span>
                                    <span class="badge bg-secondary">
                                        <?= $dimension['percentage'] ?>%
                                    </span>
                                </div>

                                <!-- Dimension Name -->
                                <h6 class="card-title fw-bold mb-2" style="min-height: 40px; font-size: 0.9rem;">
                                    <?= esc($dimension['name']) ?>
                                </h6>

                                <!-- Score Display (like heatmap) -->
                                <div class="text-center mb-3">
                                    <div class="fs-5 fw-bold text-dark">
                                        (<?= $dimension['worst_score'] ?>
                                        <span class="text-primary">(<?= $dimension['worst_form'] ?>)</span>)
                                    </div>
                                    <?php if ($dimension['has_both_forms']): ?>
                                        <?php
                                        // Mostrar el puntaje de la otra forma
                                        $otherForm = $dimension['worst_form'] === 'A' ? 'B' : 'A';
                                        $otherScore = $dimension['worst_form'] === 'A' ? $dimension['form_b_score'] : $dimension['form_a_score'];
                                        ?>
                                        <?php if ($otherScore !== null): ?>
                                            <div class="small text-success">
                                                <?= $otherForm ?>: <?= $otherScore ?>)
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>

                                <!-- Statistics -->
                                <div class="small text-muted mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span><i class="fas fa-users me-1"></i>Evaluados:</span>
                                        <strong>
                                            <?php if ($dimension['has_both_forms']): ?>
                                                A: <?= $dimension['form_a_count'] ?? 0 ?> | B: <?= $dimension['form_b_count'] ?? 0 ?>
                                            <?php else: ?>
                                                <?= $dimension['total_workers'] ?>
                                            <?php endif; ?>
                                        </strong>
                                    </div>
                                </div>

                                <!-- Action Button -->
                                <a href="<?= base_url('recommendations/dimension/' . $dimension['code']) ?>"
                                   class="btn btn-<?= $dimension['level_color'] ?> btn-sm w-100"
                                   target="_blank">
                                    <i class="fas fa-file-alt me-1"></i>
                                    Ver Plan
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- EXTRALABORAL -->
        <?php if ($totalExtralaboral > 0): ?>
        <div class="dimension-category mb-4">
            <div class="category-header mb-3 pb-2 border-bottom border-success">
                <h5 class="text-success mb-1">
                    <i class="fas fa-home me-2"></i>
                    Factores de Riesgo Extralaboral
                </h5>
                <p class="text-muted small mb-0">
                    <?= $totalExtralaboral ?> dimensión(es) extralaboral(es) en riesgo |
                    <strong>Total posible:</strong> 7 dimensiones
                </p>
            </div>
            <div class="row">
                <?php foreach ($grouped['extralaboral'] as $dimension): ?>
                    <div class="col-12 col-md-6 col-lg-4 col-xl-3 mb-3">
                        <div class="card h-100 border-<?= $dimension['level_color'] ?> shadow-sm" style="border-width: 2px;">
                            <div class="card-body p-3">
                                <!-- Risk Badge -->
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge bg-<?= $dimension['level_color'] ?> mb-2">
                                        <?= $dimension['level_label'] ?>
                                    </span>
                                    <span class="badge bg-secondary">
                                        <?= $dimension['percentage'] ?>%
                                    </span>
                                </div>

                                <!-- Dimension Name -->
                                <h6 class="card-title fw-bold mb-2" style="min-height: 40px; font-size: 0.9rem;">
                                    <?= esc($dimension['name']) ?>
                                </h6>

                                <!-- Score Display (like heatmap) -->
                                <div class="text-center mb-3">
                                    <div class="fs-5 fw-bold text-dark">
                                        (<?= $dimension['worst_score'] ?>
                                        <span class="text-primary">(<?= $dimension['worst_form'] ?>)</span>)
                                    </div>
                                    <?php if ($dimension['has_both_forms']): ?>
                                        <?php
                                        // Mostrar el puntaje de la otra forma
                                        $otherForm = $dimension['worst_form'] === 'A' ? 'B' : 'A';
                                        $otherScore = $dimension['worst_form'] === 'A' ? $dimension['form_b_score'] : $dimension['form_a_score'];
                                        ?>
                                        <?php if ($otherScore !== null): ?>
                                            <div class="small text-success">
                                                <?= $otherForm ?>: <?= $otherScore ?>)
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>

                                <!-- Statistics -->
                                <div class="small text-muted mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span><i class="fas fa-users me-1"></i>Evaluados:</span>
                                        <strong>
                                            <?php if ($dimension['has_both_forms']): ?>
                                                A: <?= $dimension['form_a_count'] ?? 0 ?> | B: <?= $dimension['form_b_count'] ?? 0 ?>
                                            <?php else: ?>
                                                <?= $dimension['total_workers'] ?>
                                            <?php endif; ?>
                                        </strong>
                                    </div>
                                </div>

                                <!-- Action Button -->
                                <a href="<?= base_url('recommendations/dimension/' . $dimension['code']) ?>"
                                   class="btn btn-<?= $dimension['level_color'] ?> btn-sm w-100"
                                   target="_blank">
                                    <i class="fas fa-file-alt me-1"></i>
                                    Ver Plan
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- ESTRÉS -->
        <?php if ($totalEstres > 0): ?>
        <div class="dimension-category mb-4">
            <div class="category-header mb-3 pb-2 border-bottom border-danger">
                <h5 class="text-danger mb-1">
                    <i class="fas fa-heartbeat me-2"></i>
                    Evaluación del Estrés
                </h5>
                <p class="text-muted small mb-0">
                    <?= $totalEstres ?> evaluación(es) de estrés en riesgo |
                    <strong>Total posible:</strong> 1 dimensión
                </p>
            </div>
            <div class="row">
                <?php foreach ($grouped['estres'] as $dimension): ?>
                    <div class="col-12 col-md-6 col-lg-4 col-xl-3 mb-3">
                        <div class="card h-100 border-<?= $dimension['level_color'] ?> shadow-sm" style="border-width: 2px;">
                            <div class="card-body p-3">
                                <!-- Risk Badge -->
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge bg-<?= $dimension['level_color'] ?> mb-2">
                                        <?= $dimension['level_label'] ?>
                                    </span>
                                    <span class="badge bg-secondary">
                                        <?= $dimension['percentage'] ?>%
                                    </span>
                                </div>

                                <!-- Dimension Name -->
                                <h6 class="card-title fw-bold mb-2" style="min-height: 40px; font-size: 0.9rem;">
                                    <?= esc($dimension['name']) ?>
                                </h6>

                                <!-- Score Display (like heatmap) -->
                                <div class="text-center mb-3">
                                    <div class="fs-5 fw-bold text-dark">
                                        (<?= $dimension['worst_score'] ?>
                                        <span class="text-primary">(<?= $dimension['worst_form'] ?>)</span>)
                                    </div>
                                    <?php if ($dimension['has_both_forms']): ?>
                                        <?php
                                        // Mostrar el puntaje de la otra forma
                                        $otherForm = $dimension['worst_form'] === 'A' ? 'B' : 'A';
                                        $otherScore = $dimension['worst_form'] === 'A' ? $dimension['form_b_score'] : $dimension['form_a_score'];
                                        ?>
                                        <?php if ($otherScore !== null): ?>
                                            <div class="small text-success">
                                                <?= $otherForm ?>: <?= $otherScore ?>)
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>

                                <!-- Statistics -->
                                <div class="small text-muted mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span><i class="fas fa-users me-1"></i>Evaluados:</span>
                                        <strong>
                                            <?php if ($dimension['has_both_forms']): ?>
                                                A: <?= $dimension['form_a_count'] ?? 0 ?> | B: <?= $dimension['form_b_count'] ?? 0 ?>
                                            <?php else: ?>
                                                <?= $dimension['total_workers'] ?>
                                            <?php endif; ?>
                                        </strong>
                                    </div>
                                </div>

                                <!-- Action Button -->
                                <a href="<?= base_url('recommendations/dimension/' . $dimension['code']) ?>"
                                   class="btn btn-<?= $dimension['level_color'] ?> btn-sm w-100"
                                   target="_blank">
                                    <i class="fas fa-file-alt me-1"></i>
                                    Ver Plan
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Summary Statistics -->
        <div class="alert alert-light border mb-3">
            <div class="row text-center">
                <div class="col-md-3">
                    <h6 class="text-primary mb-1"><i class="fas fa-chart-pie me-1"></i>Total Dimensiones</h6>
                    <p class="mb-0 fs-4 fw-bold"><?= count($riskyDimensions) ?> / 27</p>
                    <small class="text-muted">dimensiones en riesgo</small>
                </div>
                <div class="col-md-3">
                    <h6 class="text-primary mb-1"><i class="fas fa-briefcase me-1"></i>Intralaboral</h6>
                    <p class="mb-0 fs-4 fw-bold"><?= $totalIntralaboral ?> / 19</p>
                    <small class="text-muted">dimensiones (Forma A)</small>
                </div>
                <div class="col-md-3">
                    <h6 class="text-success mb-1"><i class="fas fa-home me-1"></i>Extralaboral</h6>
                    <p class="mb-0 fs-4 fw-bold"><?= $totalExtralaboral ?> / 7</p>
                    <small class="text-muted">dimensiones</small>
                </div>
                <div class="col-md-3">
                    <h6 class="text-danger mb-1"><i class="fas fa-heartbeat me-1"></i>Estrés</h6>
                    <p class="mb-0 fs-4 fw-bold"><?= $totalEstres ?> / 1</p>
                    <small class="text-muted">evaluación</small>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
.card.border-warning:hover {
    box-shadow: 0 0.5rem 1rem rgba(255, 193, 7, 0.15) !important;
    transform: translateY(-2px);
    transition: all 0.3s;
}

.card.border-danger:hover {
    box-shadow: 0 0.5rem 1rem rgba(220, 53, 69, 0.15) !important;
    transform: translateY(-2px);
    transition: all 0.3s;
}

.bg-gradient {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.dimension-category {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1.5rem;
}

.category-header {
    background: white;
    padding: 1rem;
    border-radius: 6px;
}
</style>

<?php endif; ?>
