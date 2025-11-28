<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
// Helper para colores de riesgo
function getRiskBadgeClass($nivel) {
    $colors = [
        'sin_riesgo' => 'success', 'riesgo_bajo' => 'success',
        'riesgo_medio' => 'warning', 'riesgo_alto' => 'danger',
        'riesgo_muy_alto' => 'danger', 'muy_bajo' => 'success',
        'bajo' => 'success', 'medio' => 'warning',
        'alto' => 'danger', 'muy_alto' => 'danger'
    ];
    return $colors[$nivel] ?? 'secondary';
}

function formatRiskLevel($nivel) {
    return ucwords(str_replace('_', ' ', $nivel ?? 'N/A'));
}

// Mapeo de códigos de dimensión a nombres oficiales completos
$dimensionNames = [
    // Dominio 1: Liderazgo y relaciones sociales en el trabajo
    'caracteristicas_liderazgo' => '1.1 Características del liderazgo',
    'liderazgo' => '1.1 Características del liderazgo',
    'relaciones_sociales' => '1.2 Relaciones sociales en el trabajo',
    'relaciones_sociales_trabajo' => '1.2 Relaciones sociales en el trabajo',
    'retroalimentacion' => '1.3 Retroalimentación del desempeño',
    'retroalimentacion_desempeno' => '1.3 Retroalimentación del desempeño',
    'relacion_colaboradores' => '1.4 Relación con los colaboradores',
    'relacion_con_colaboradores' => '1.4 Relación con los colaboradores',

    // Dominio 2: Control sobre el trabajo
    'claridad_rol' => '2.1 Claridad de rol',
    'capacitacion' => '2.2 Capacitación',
    'participacion_cambio' => '2.3 Participación y manejo del cambio',
    'oportunidades' => '2.4 Oportunidades para el uso y desarrollo de habilidades y conocimientos',
    'oportunidades_desarrollo' => '2.4 Oportunidades para el uso y desarrollo de habilidades y conocimientos',
    'control_autonomia' => '2.5 Control y autonomía sobre el trabajo',

    // Dominio 3: Demandas del trabajo
    'demandas_ambientales' => '3.1 Demandas ambientales y de esfuerzo físico',
    'demandas_emocionales' => '3.2 Demandas emocionales',
    'demandas_cuantitativas' => '3.3 Demandas cuantitativas',
    'influencia_extralaboral' => '3.4 Influencia del trabajo sobre el entorno extralaboral',
    'exigencias_responsabilidad' => '3.5 Exigencias de responsabilidad del cargo',
    'carga_mental' => '3.6 Demandas de carga mental',
    'demandas_carga_mental' => '3.6 Demandas de carga mental',
    'consistencia_rol' => '3.7 Consistencia del rol',
    'jornada_trabajo' => '3.8 Demandas de la jornada de trabajo',
    'demandas_jornada' => '3.8 Demandas de la jornada de trabajo',

    // Dominio 4: Recompensas
    'recompensas_pertenencia' => '4.1 Recompensas derivadas de la pertenencia a la organización y del trabajo que se realiza',
    'reconocimiento' => '4.2 Reconocimiento y compensación',
    'reconocimiento_compensacion' => '4.2 Reconocimiento y compensación',

    // Dimensiones Extralaborales
    'tiempo_fuera' => 'Tiempo fuera del trabajo',
    'tiempo_fuera_trabajo' => 'Tiempo fuera del trabajo',
    'relaciones_familiares' => 'Relaciones familiares',
    'comunicacion' => 'Comunicación y relaciones interpersonales',
    'comunicacion_relaciones' => 'Comunicación y relaciones interpersonales',
    'situacion_economica' => 'Situación económica del grupo familiar',
    'vivienda' => 'Características de la vivienda y de su entorno',
    'caracteristicas_vivienda' => 'Características de la vivienda y de su entorno',
    'influencia_entorno' => 'Influencia del entorno extralaboral sobre el trabajo',
    'influencia_entorno_extralaboral' => 'Influencia del entorno extralaboral sobre el trabajo',
    'desplazamiento' => 'Desplazamiento vivienda - trabajo - vivienda',
    'desplazamiento_vivienda_trabajo' => 'Desplazamiento vivienda - trabajo - vivienda',
];

// Mapeo de códigos de dominio a nombres oficiales
$domainNames = [
    'liderazgo' => '1. Liderazgo y relaciones sociales en el trabajo',
    'liderazgo_relaciones' => '1. Liderazgo y relaciones sociales en el trabajo',
    'liderazgo_y_relaciones_sociales_en_el_trabajo' => '1. Liderazgo y relaciones sociales en el trabajo',
    'control' => '2. Control sobre el trabajo',
    'control_trabajo' => '2. Control sobre el trabajo',
    'control_sobre_el_trabajo' => '2. Control sobre el trabajo',
    'demandas' => '3. Demandas del trabajo',
    'demandas_trabajo' => '3. Demandas del trabajo',
    'demandas_del_trabajo' => '3. Demandas del trabajo',
    'recompensas' => '4. Recompensas',
];

// Función helper para obtener el nombre formateado
function getFormattedSectionName($section, $dimensionNames, $domainNames) {
    if ($section['section_level'] === 'executive') {
        return 'Resumen Ejecutivo';
    }
    if ($section['section_level'] === 'total') {
        return 'Puntaje Total General';
    }
    if (!empty($section['dimension_code'])) {
        $code = strtolower(str_replace(' ', '_', $section['dimension_code']));
        return $dimensionNames[$code] ?? ucwords(str_replace('_', ' ', $section['dimension_code']));
    }
    if (!empty($section['domain_code'])) {
        $code = strtolower(str_replace(' ', '_', $section['domain_code']));
        return $domainNames[$code] ?? ucwords(str_replace('_', ' ', $section['domain_code']));
    }
    if (!empty($section['questionnaire_type'])) {
        return ucwords($section['questionnaire_type']);
    }
    return 'Sección';
}
?>

<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="fas fa-file-alt text-primary me-2"></i>
                <?= $levels[$currentLevel] ?? 'Secciones' ?>
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/battery-services/<?= $service['id'] ?>"><?= esc($service['company_name']) ?></a></li>
                    <li class="breadcrumb-item"><a href="/report-sections/<?= $service['id'] ?>">Secciones</a></li>
                    <li class="breadcrumb-item active"><?= $levels[$currentLevel] ?></li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="/report-sections/<?= $service['id'] ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Volver
            </a>
        </div>
    </div>

    <!-- Navegación entre niveles -->
    <div class="card shadow-sm mb-4">
        <div class="card-body py-2">
            <ul class="nav nav-pills nav-fill">
                <?php foreach ($levels as $key => $label): ?>
                <li class="nav-item">
                    <a class="nav-link <?= $currentLevel === $key ? 'active' : '' ?>"
                       href="/report-sections/review/<?= $service['id'] ?>/<?= $key ?>">
                        <?= $label ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <!-- Barra de progreso -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span>Progreso de aprobación</span>
                <span class="fw-bold"><?= $stats['approved'] ?> / <?= $stats['total'] ?> (<?= $stats['percentage'] ?>%)</span>
            </div>
            <div class="progress" style="height: 10px;">
                <div class="progress-bar bg-success" style="width: <?= $stats['percentage'] ?>%"></div>
            </div>
        </div>
    </div>

    <!-- Secciones -->
    <?php if (empty($sections)): ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        No hay secciones de este nivel para mostrar.
    </div>
    <?php else: ?>

    <div class="row">
        <?php foreach ($sections as $index => $section): ?>
        <div class="col-12 mb-4">
            <div class="card shadow-sm <?= $section['is_approved'] ? 'border-success' : '' ?>">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-3">
                        <?php if ($section['is_approved']): ?>
                        <span class="badge bg-success"><i class="fas fa-check me-1"></i>Aprobada</span>
                        <?php else: ?>
                        <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Pendiente</span>
                        <?php endif; ?>

                        <h6 class="mb-0">
                            <?= esc(getFormattedSectionName($section, $dimensionNames, $domainNames)) ?>
                        </h6>

                        <span class="badge bg-info"><?= $section['form_type'] ?></span>
                    </div>

                    <div class="d-flex align-items-center gap-3">
                        <?php if ($section['score_value']): ?>
                        <div class="text-end">
                            <small class="text-muted d-block">Puntaje</small>
                            <strong><?= number_format($section['score_value'], 2) ?></strong>
                        </div>
                        <?php endif; ?>

                        <?php if ($section['risk_level']): ?>
                        <span class="badge bg-<?= getRiskBadgeClass($section['risk_level']) ?> px-3 py-2">
                            <?= formatRiskLevel($section['risk_level']) ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Texto generado por IA -->
                    <div class="mb-4">
                        <label class="form-label text-muted small">
                            <i class="fas fa-robot me-1"></i> Análisis generado
                        </label>
                        <div class="bg-light p-3 rounded">
                            <?= nl2br(esc($section['ai_generated_text'] ?? 'No se ha generado texto para esta sección.')) ?>
                        </div>
                    </div>

                    <!-- Comentario del consultor -->
                    <div class="mb-3">
                        <label class="form-label text-muted small">
                            <i class="fas fa-user-edit me-1"></i> Comentario adicional del consultor (opcional)
                        </label>
                        <textarea class="form-control consultant-comment"
                                  data-section-id="<?= $section['id'] ?>"
                                  rows="3"
                                  placeholder="Agregue observaciones o comentarios adicionales basados en el contexto específico de la empresa..."
                        ><?= esc($section['consultant_comment'] ?? '') ?></textarea>
                        <div class="form-text">
                            Este comentario aparecerá después del análisis en el informe final.
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-white d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary btn-sm btn-save-comment" data-section-id="<?= $section['id'] ?>">
                        <i class="fas fa-save me-1"></i> Guardar Comentario
                    </button>

                    <?php if (!$section['is_approved']): ?>
                    <button type="button" class="btn btn-success btn-approve" data-section-id="<?= $section['id'] ?>">
                        <i class="fas fa-check me-1"></i> Aprobar Sección
                    </button>
                    <?php else: ?>
                    <span class="text-success">
                        <i class="fas fa-check-circle me-1"></i>
                        Aprobada el <?= date('d/m/Y H:i', strtotime($section['approved_at'])) ?>
                    </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Navegación entre secciones -->
    <div class="d-flex justify-content-between mb-4">
        <?php
        $levelKeys = array_keys($levels);
        $currentIndex = array_search($currentLevel, $levelKeys);
        $prevLevel = $currentIndex > 0 ? $levelKeys[$currentIndex - 1] : null;
        $nextLevel = $currentIndex < count($levelKeys) - 1 ? $levelKeys[$currentIndex + 1] : null;
        ?>

        <?php if ($prevLevel): ?>
        <a href="/report-sections/review/<?= $service['id'] ?>/<?= $prevLevel ?>" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-1"></i> <?= $levels[$prevLevel] ?>
        </a>
        <?php else: ?>
        <div></div>
        <?php endif; ?>

        <?php if ($nextLevel): ?>
        <a href="/report-sections/review/<?= $service['id'] ?>/<?= $nextLevel ?>" class="btn btn-primary">
            <?= $levels[$nextLevel] ?> <i class="fas fa-arrow-right ms-1"></i>
        </a>
        <?php endif; ?>
    </div>

    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Guardar comentario
    document.querySelectorAll('.btn-save-comment').forEach(btn => {
        btn.addEventListener('click', function() {
            const sectionId = this.dataset.sectionId;
            const textarea = document.querySelector(`.consultant-comment[data-section-id="${sectionId}"]`);
            const comment = textarea.value;

            fetch(`/report-sections/save-comment/${sectionId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `consultant_comment=${encodeURIComponent(comment)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Feedback visual
                    this.innerHTML = '<i class="fas fa-check me-1"></i> Guardado';
                    this.classList.remove('btn-outline-secondary');
                    this.classList.add('btn-success');
                    setTimeout(() => {
                        this.innerHTML = '<i class="fas fa-save me-1"></i> Guardar Comentario';
                        this.classList.remove('btn-success');
                        this.classList.add('btn-outline-secondary');
                    }, 2000);
                } else {
                    alert('Error al guardar: ' + data.message);
                }
            });
        });
    });

    // Aprobar sección
    document.querySelectorAll('.btn-approve').forEach(btn => {
        btn.addEventListener('click', function() {
            const sectionId = this.dataset.sectionId;

            fetch(`/report-sections/approve/${sectionId}`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Recargar para mostrar estado actualizado
                    window.location.reload();
                } else {
                    alert('Error al aprobar: ' + data.message);
                }
            });
        });
    });
});
</script>
<?= $this->endSection() ?>
