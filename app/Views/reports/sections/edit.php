<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
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

// Función para obtener nombre formateado
function getSectionTitle($section, $dimensionNames, $domainNames) {
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
        return 'Cuestionario ' . ucwords($section['questionnaire_type']);
    }
    return 'Sección';
}

$sectionTitle = getSectionTitle($section, $dimensionNames, $domainNames);
?>
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="fas fa-edit text-primary me-2"></i>
                <?= esc($sectionTitle) ?>
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('report-sections/' . $report['battery_service_id']) ?>">Secciones del Informe</a></li>
                    <li class="breadcrumb-item active"><?= esc($sectionTitle) ?></li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="<?= base_url('report-sections/' . $report['battery_service_id']) ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Volver
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Información de la sección -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <td class="text-muted">Empresa:</td>
                            <td><strong><?= esc($service['company_name']) ?></strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Nivel:</td>
                            <td>
                                <span class="badge bg-primary"><?= ucfirst($section['section_level']) ?></span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tipo:</td>
                            <td><?= ucfirst($section['questionnaire_type'] ?? '-') ?></td>
                        </tr>
                        <?php if ($section['domain_code']): ?>
                        <tr>
                            <td class="text-muted">Dominio:</td>
                            <td><?php
                                $domainCode = strtolower(str_replace(' ', '_', $section['domain_code']));
                                echo esc($domainNames[$domainCode] ?? ucwords(str_replace('_', ' ', $section['domain_code'])));
                            ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($section['dimension_code']): ?>
                        <tr>
                            <td class="text-muted">Dimensión:</td>
                            <td><?php
                                $dimCode = strtolower(str_replace(' ', '_', $section['dimension_code']));
                                echo esc($dimensionNames[$dimCode] ?? ucwords(str_replace('_', ' ', $section['dimension_code'])));
                            ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <td class="text-muted">Forma:</td>
                            <td><span class="badge bg-info"><?= $section['form_type'] ?></span></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Puntaje:</td>
                            <td><strong><?= $section['score_value'] ? number_format($section['score_value'], 2) : '-' ?></strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Nivel de Riesgo:</td>
                            <td>
                                <?php
                                $riskColors = [
                                    // Niveles de riesgo intralaboral/extralaboral
                                    'sin_riesgo' => 'success', 'riesgo_bajo' => 'success',
                                    'riesgo_medio' => 'warning', 'riesgo_alto' => 'danger',
                                    'riesgo_muy_alto' => 'danger',
                                    // Niveles de estrés
                                    'muy_bajo' => 'success', 'bajo' => 'success',
                                    'medio' => 'warning', 'alto' => 'danger',
                                    'muy_alto' => 'danger'
                                ];
                                $color = $riskColors[$section['risk_level']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?= $color ?>"><?= str_replace('_', ' ', ucfirst($section['risk_level'] ?? '-')) ?></span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Estado:</td>
                            <td>
                                <?php if ($section['is_approved']): ?>
                                    <span class="badge bg-success"><i class="fas fa-check me-1"></i>Aprobada</span>
                                <?php else: ?>
                                    <span class="badge bg-warning"><i class="fas fa-clock me-1"></i>Pendiente</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>

                    <?php if (!$section['is_approved']): ?>
                    <div class="d-grid gap-2 mt-3">
                        <button type="button" class="btn btn-success" id="btnApprove">
                            <i class="fas fa-check me-1"></i> Aprobar Sección
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Texto generado por IA -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-robot me-2 text-purple"></i>Interpretación Generada por IA</h6>
                    <?php if (!$section['ai_generated_text']): ?>
                    <button type="button" class="btn btn-sm btn-primary" id="btnGenerateAI">
                        <i class="fas fa-magic me-1"></i> Generar con IA
                    </button>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if ($section['ai_generated_text']): ?>
                        <div class="ai-text p-3 bg-light rounded" style="white-space: pre-wrap; font-size: 0.95rem;">
                            <?= nl2br(esc($section['ai_generated_text'])) ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-robot fa-3x mb-3"></i>
                            <p>No se ha generado texto con IA para esta sección.</p>
                            <p class="small">Haz clic en "Generar con IA" para crear la interpretación automática.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Comentario del consultor -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-comment-dots me-2 text-info"></i>Comentario del Consultor (Opcional)</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Agregue observaciones adicionales o notas específicas para esta sección.
                        Este comentario se incluirá en el informe final junto con la interpretación de la IA.
                    </p>
                    <textarea class="form-control" id="consultantComment" rows="4"
                              placeholder="Escriba su comentario aquí..."><?= esc($section['consultant_comment'] ?? '') ?></textarea>
                    <div class="d-flex justify-content-end mt-3">
                        <button type="button" class="btn btn-primary" id="btnSaveComment">
                            <i class="fas fa-save me-1"></i> Guardar Comentario
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.text-purple { color: #6f42c1; }
.ai-text {
    line-height: 1.7;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sectionId = <?= $section['id'] ?>;
    const baseUrl = '<?= base_url() ?>';

    // Generar texto con IA
    document.getElementById('btnGenerateAI')?.addEventListener('click', async function() {
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Generando...';

        try {
            const response = await fetch(`${baseUrl}report-sections/generate-ai/${sectionId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await response.json();

            if (data.success) {
                alert('Texto generado correctamente');
                window.location.reload();
            } else {
                alert('Error: ' + data.message);
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-magic me-1"></i> Generar con IA';
            }
        } catch (error) {
            alert('Error: ' + error.message);
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-magic me-1"></i> Generar con IA';
        }
    });

    // Guardar comentario
    document.getElementById('btnSaveComment')?.addEventListener('click', async function() {
        const comment = document.getElementById('consultantComment').value;

        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Guardando...';

        try {
            const formData = new FormData();
            formData.append('consultant_comment', comment);

            const response = await fetch(`${baseUrl}report-sections/save-comment/${sectionId}`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await response.json();

            if (data.success) {
                alert('Comentario guardado correctamente');
            } else {
                alert('Error: ' + data.message);
            }
        } catch (error) {
            alert('Error: ' + error.message);
        }

        this.disabled = false;
        this.innerHTML = '<i class="fas fa-save me-1"></i> Guardar Comentario';
    });

    // Aprobar sección
    document.getElementById('btnApprove')?.addEventListener('click', async function() {
        if (!confirm('¿Aprobar esta sección?')) return;

        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Aprobando...';

        try {
            const response = await fetch(`${baseUrl}report-sections/approve/${sectionId}`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await response.json();

            if (data.success) {
                alert('Sección aprobada correctamente');
                window.location.reload();
            } else {
                alert('Error: ' + data.message);
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-check me-1"></i> Aprobar Sección';
            }
        } catch (error) {
            alert('Error: ' + error.message);
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-check me-1"></i> Aprobar Sección';
        }
    });
});
</script>
<?= $this->endSection() ?>
