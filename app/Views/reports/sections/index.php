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

// Mapeo de tipos de cuestionario
$questionnaireNames = [
    'intralaboral' => 'Cuestionario Intralaboral',
    'extralaboral' => 'Cuestionario Extralaboral',
    'estres' => 'Cuestionario de Estrés',
    'stress' => 'Cuestionario de Estrés',
    'estrés' => 'Cuestionario de Estrés',
];

// Función helper para obtener el nombre formateado
function getFormattedName($section, $dimensionNames, $domainNames, $questionnaireNames) {
    if (!empty($section['dimension_code'])) {
        $code = strtolower(str_replace(' ', '_', $section['dimension_code']));
        return $dimensionNames[$code] ?? ucwords(str_replace('_', ' ', $section['dimension_code']));
    }
    if (!empty($section['domain_code'])) {
        $code = strtolower(str_replace(' ', '_', $section['domain_code']));
        return $domainNames[$code] ?? ucwords(str_replace('_', ' ', $section['domain_code']));
    }
    if (!empty($section['questionnaire_type'])) {
        $code = strtolower($section['questionnaire_type']);
        return $questionnaireNames[$code] ?? ucwords($section['questionnaire_type']);
    }
    return '-';
}
?>
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="fas fa-file-alt text-primary me-2"></i>
                Generación de Informe con IA
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" target="_blank">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('battery-services/' . $service['id']) ?>" target="_blank"><?= esc($service['company_name']) ?></a></li>
                    <li class="breadcrumb-item active">Secciones del Informe</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="<?= base_url('battery-services/' . $service['id']) ?>" class="btn btn-outline-secondary" target="_blank">
                <i class="fas fa-arrow-left me-1"></i> Volver
            </a>
        </div>
    </div>

    <!-- Alert si OpenAI no está configurado -->
    <?php if (!$openAIConfigured): ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>OpenAI no configurado.</strong> Para generar interpretaciones automáticas, configure su API Key en el archivo <code>.env</code>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Información del Servicio -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-1"><?= esc($service['company_name']) ?></h5>
                    <p class="text-muted mb-0">
                        <i class="fas fa-calendar me-1"></i> Servicio: <?= date('d/m/Y', strtotime($service['created_at'])) ?>
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <?php if (empty($sections)): ?>
                        <button type="button" class="btn btn-primary btn-lg" id="btnGenerate" <?= !$openAIConfigured ? 'disabled' : '' ?>>
                            <i class="fas fa-magic me-2"></i> Generar Secciones con IA
                        </button>
                    <?php else: ?>
                        <div class="d-flex justify-content-end align-items-center gap-3">
                            <div class="text-end">
                                <div class="h4 mb-0"><?= $stats['percentage'] ?>%</div>
                                <small class="text-muted"><?= $stats['approved'] ?> / <?= $stats['total'] ?> aprobadas</small>
                            </div>
                            <div class="progress" style="width: 150px; height: 10px;">
                                <div class="progress-bar bg-success" style="width: <?= $stats['percentage'] ?>%"></div>
                            </div>
                            <button type="button" class="btn btn-outline-warning btn-sm" id="btnRegenerate" title="Regenerar todas las secciones (se perderán los textos y aprobaciones actuales)">
                                <i class="fas fa-sync-alt me-1"></i> Regenerar
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($sections)): ?>
    <!-- Navegación por niveles -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-layer-group me-2"></i>Secciones del Informe</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <!-- Resumen Ejecutivo -->
                        <div class="col-md-4 col-lg">
                            <a href="<?= base_url('report-sections/review/' . $service['id'] . '/executive') ?>" class="card h-100 text-decoration-none hover-shadow" target="_blank">
                                <div class="card-body text-center">
                                    <div class="display-6 text-primary mb-2">
                                        <i class="fas fa-crown"></i>
                                    </div>
                                    <h6 class="card-title">Resumen Ejecutivo</h6>
                                    <p class="card-text text-muted small">Para gerentes</p>
                                </div>
                            </a>
                        </div>

                        <!-- Totales Generales -->
                        <div class="col-md-4 col-lg">
                            <a href="<?= base_url('report-sections/review/' . $service['id'] . '/total') ?>" class="card h-100 text-decoration-none hover-shadow" target="_blank">
                                <div class="card-body text-center">
                                    <div class="display-6 text-success mb-2">
                                        <i class="fas fa-chart-pie"></i>
                                    </div>
                                    <h6 class="card-title">Totales Generales</h6>
                                    <p class="card-text text-muted small">Puntajes globales</p>
                                </div>
                            </a>
                        </div>

                        <!-- Cuestionarios -->
                        <div class="col-md-4 col-lg">
                            <a href="<?= base_url('report-sections/review/' . $service['id'] . '/questionnaire') ?>" class="card h-100 text-decoration-none hover-shadow" target="_blank">
                                <div class="card-body text-center">
                                    <div class="display-6 text-info mb-2">
                                        <i class="fas fa-clipboard-list"></i>
                                    </div>
                                    <h6 class="card-title">Cuestionarios</h6>
                                    <p class="card-text text-muted small">Intra, Extra, Estrés</p>
                                </div>
                            </a>
                        </div>

                        <!-- Dominios -->
                        <div class="col-md-4 col-lg">
                            <a href="<?= base_url('report-sections/review/' . $service['id'] . '/domain') ?>" class="card h-100 text-decoration-none hover-shadow" target="_blank">
                                <div class="card-body text-center">
                                    <div class="display-6 text-warning mb-2">
                                        <i class="fas fa-sitemap"></i>
                                    </div>
                                    <h6 class="card-title">Dominios</h6>
                                    <p class="card-text text-muted small">4 dominios intralaboral</p>
                                </div>
                            </a>
                        </div>

                        <!-- Dimensiones -->
                        <div class="col-md-4 col-lg">
                            <a href="<?= base_url('report-sections/review/' . $service['id'] . '/dimension') ?>" class="card h-100 text-decoration-none hover-shadow" target="_blank">
                                <div class="card-body text-center">
                                    <div class="display-6 text-danger mb-2">
                                        <i class="fas fa-cubes"></i>
                                    </div>
                                    <h6 class="card-title">Dimensiones</h6>
                                    <p class="card-text text-muted small">Todas las dimensiones</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Acciones rápidas -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">Acciones Rápidas</h6>
                        <p class="text-muted mb-0 small">Gestione todas las secciones del informe</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-warning" id="btnRegenerate">
                            <i class="fas fa-sync me-1"></i> Regenerar Todo
                        </button>
                        <?php if ($stats['pending'] > 0): ?>
                        <button type="button" class="btn btn-success" id="btnApproveAll">
                            <i class="fas fa-check-double me-1"></i> Aprobar Todas (<?= $stats['pending'] ?> pendientes)
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de todas las secciones -->
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="fas fa-list me-2"></i>Todas las Secciones del Informe</h6>
            <span class="badge bg-primary"><?= count($sections) ?> secciones</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="tableSections">
                    <thead class="table-light">
                        <tr>
                            <th>Nivel</th>
                            <th>Tipo</th>
                            <th>Forma</th>
                            <th>Puntaje</th>
                            <th>Riesgo</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                        <tr class="filters">
                            <th><select class="form-select form-select-sm filter-select" data-column="0"><option value="">Todos</option></select></th>
                            <th><input type="text" class="form-control form-control-sm filter-input" data-column="1" placeholder="Buscar..."></th>
                            <th><select class="form-select form-select-sm filter-select" data-column="2"><option value="">Todos</option></select></th>
                            <th></th>
                            <th><select class="form-select form-select-sm filter-select" data-column="4"><option value="">Todos</option></select></th>
                            <th><select class="form-select form-select-sm filter-select" data-column="5"><option value="">Todos</option></select></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sections as $section): ?>
                        <tr data-approved="<?= $section['is_approved'] ? '1' : '0' ?>">
                            <td>
                                <span class="badge bg-secondary"><?= ucfirst($section['section_level']) ?></span>
                            </td>
                            <td><?= esc(getFormattedName($section, $dimensionNames, $domainNames, $questionnaireNames)) ?></td>
                            <td><span class="badge bg-info"><?= $section['form_type'] ?></span></td>
                            <td><?= $section['score_value'] ? number_format($section['score_value'], 2) : '-' ?></td>
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
                            <td>
                                <?php if ($section['is_approved']): ?>
                                    <span class="badge bg-success"><i class="fas fa-check me-1"></i>Aprobada</span>
                                <?php else: ?>
                                    <span class="badge bg-warning"><i class="fas fa-clock me-1"></i>Pendiente</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="<?= base_url('report-sections/edit/' . $section['id']) ?>" class="btn btn-outline-primary" target="_blank" title="Ver/Editar">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-info btn-prompt" data-id="<?= $section['id'] ?>" data-prompt="<?= esc($section['consultant_prompt'] ?? '') ?>" title="Contexto IA">
                                        <i class="fas fa-comment-dots"></i>
                                    </button>
                                    <?php if ($section['ai_generated_text']): ?>
                                    <button type="button" class="btn btn-outline-warning btn-reset" data-id="<?= $section['id'] ?>" title="Resetear y regenerar IA">
                                        <i class="fas fa-redo"></i>
                                    </button>
                                    <?php endif; ?>
                                    <?php if ($section['is_approved']): ?>
                                    <button type="button" class="btn btn-outline-secondary btn-unapprove" data-id="<?= $section['id'] ?>" title="Desaprobar">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                    <?php else: ?>
                                    <button type="button" class="btn btn-success btn-approve" data-id="<?= $section['id'] ?>" title="Aprobar">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php else: ?>
    <!-- Estado inicial - No hay secciones -->
    <div class="card shadow-sm">
        <div class="card-body text-center py-5">
            <div class="display-1 text-muted mb-4">
                <i class="fas fa-robot"></i>
            </div>
            <h4>No hay secciones generadas</h4>
            <p class="text-muted mb-4">
                Haga clic en "Generar Secciones con IA" para crear automáticamente todas las interpretaciones del informe.
            </p>
            <button type="button" class="btn btn-primary btn-lg" id="btnGenerateEmpty" <?= !$openAIConfigured ? 'disabled' : '' ?>>
                <i class="fas fa-magic me-2"></i> Generar Secciones con IA
            </button>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Modal de Progreso -->
<div class="modal fade" id="progressModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-5">
                <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" id="progressSpinner"></div>
                <h5 id="progressTitle">Generando estructura...</h5>
                <p class="text-muted mb-0" id="progressMessage">Creando secciones del informe...</p>
                <div class="progress mt-3" style="height: 20px; display: none;" id="progressBar">
                    <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                </div>
                <p class="text-muted small mt-2" id="progressDetail">Por favor no cierre esta ventana.</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Contexto del Consultor -->
<div class="modal fade" id="promptModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-comment-dots me-2"></i>Contexto Adicional para IA</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info mb-3">
                    <i class="fas fa-lightbulb me-2"></i>
                    <strong>¿Qué es esto?</strong> Este campo complementa el prompt del sistema.
                    Use-lo para dar contexto específico que la IA debe considerar al generar el análisis.
                </div>
                <div class="mb-3">
                    <label for="consultantPromptText" class="form-label fw-bold">Contexto para la IA:</label>
                    <textarea class="form-control" id="consultantPromptText" rows="4"
                        placeholder="Ejemplos:
• Enfoca tu respuesta a una población en mayor medida madres cabeza de familia
• Es industria textil con ruido de máquinas planas que genera condiciones de salud
• El área administrativa trabaja en turnos nocturnos rotativos
• Considerar que hay alto índice de rotación en el último año"></textarea>
                    <div class="form-text">
                        Este contexto se enviará a la IA junto con los datos de la sección.
                        Después de guardar, debe <strong>regenerar</strong> el texto IA para ver los cambios.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-info" id="btnSavePrompt">
                    <i class="fas fa-save me-1"></i> Guardar Contexto
                </button>
            </div>
        </div>
    </div>
</div>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

<style>
.hover-shadow {
    transition: all 0.3s ease;
}
.hover-shadow:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15) !important;
}
#tableSections thead tr.filters th {
    padding: 5px;
}
#tableSections thead tr.filters .form-select,
#tableSections thead tr.filters .form-control {
    font-size: 0.85rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const serviceId = <?= $service['id'] ?>;
    const reportId = <?= $report['id'] ?? 'null' ?>;
    const baseUrl = '<?= base_url() ?>';
    const progressModal = new bootstrap.Modal(document.getElementById('progressModal'));

    // Elementos del modal
    const progressTitle = document.getElementById('progressTitle');
    const progressMessage = document.getElementById('progressMessage');
    const progressBar = document.getElementById('progressBar');
    const progressBarInner = progressBar.querySelector('.progress-bar');
    const progressDetail = document.getElementById('progressDetail');

    // Función para actualizar el progreso visual
    function updateProgress(current, total, message) {
        const percent = Math.round((current / total) * 100);
        progressBarInner.style.width = percent + '%';
        progressMessage.textContent = message;
        progressDetail.textContent = `${current} de ${total} secciones procesadas`;
    }

    // Función para generar IA de una sección
    async function generateAIForSection(sectionId) {
        const response = await fetch(`${baseUrl}report-sections/generate-ai/${sectionId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        return await response.json();
    }

    // Función para generar todas las secciones con IA progresivamente
    async function generateAllAIProgressively(sectionIds) {
        progressTitle.textContent = 'Generando interpretaciones con IA...';
        progressBar.style.display = 'block';

        let completed = 0;
        const total = sectionIds.length;
        let errors = [];

        for (const sectionId of sectionIds) {
            completed++;
            updateProgress(completed, total, `Procesando sección ${completed}...`);

            try {
                const result = await generateAIForSection(sectionId);
                if (!result.success) {
                    errors.push(`Sección ${sectionId}: ${result.message}`);
                }
            } catch (error) {
                errors.push(`Sección ${sectionId}: ${error.message}`);
            }

            // Pequeña pausa para no sobrecargar el servidor
            await new Promise(resolve => setTimeout(resolve, 500));
        }

        return { completed, errors };
    }

    // Generar secciones
    document.querySelectorAll('#btnGenerate, #btnGenerateEmpty, #btnRegenerate').forEach(btn => {
        btn?.addEventListener('click', async function() {
            if (this.id === 'btnRegenerate') {
                if (!confirm('¿Está seguro? Esto eliminará todas las secciones actuales y las regenerará.')) {
                    return;
                }
            }

            // Paso 1: Crear estructura de secciones
            progressTitle.textContent = 'Paso 1: Creando estructura...';
            progressMessage.textContent = 'Analizando resultados y creando secciones...';
            progressBar.style.display = 'none';
            progressModal.show();

            try {
                const structureResponse = await fetch(`${baseUrl}report-sections/generate/${serviceId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const structureData = await structureResponse.json();

                if (!structureData.success) {
                    throw new Error(structureData.message);
                }

                // Paso 2: Obtener secciones pendientes de texto IA
                progressTitle.textContent = 'Paso 2: Preparando generación IA...';
                progressMessage.textContent = 'Obteniendo secciones para procesar...';

                // Recargar la página para obtener el nuevo reportId y las secciones
                // Luego usar el endpoint para obtener las secciones pendientes
                const pendingResponse = await fetch(`${baseUrl}report-sections/generate-all-ai/${reportId || structureData.report_id}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const pendingData = await pendingResponse.json();

                if (!pendingData.success || pendingData.pending === 0) {
                    progressModal.hide();
                    alert(`Se crearon ${structureData.sections_created} secciones. ` +
                          (pendingData.pending === 0 ? 'Todas ya tienen texto generado.' : ''));
                    window.location.reload();
                    return;
                }

                // Paso 3: Generar IA para cada sección
                const result = await generateAllAIProgressively(pendingData.section_ids);

                progressModal.hide();

                if (result.errors.length > 0) {
                    alert(`Proceso completado con ${result.errors.length} errores.\n\n` +
                          result.errors.slice(0, 5).join('\n'));
                } else {
                    alert(`Se generaron ${result.completed} interpretaciones con IA correctamente.`);
                }

                window.location.reload();

            } catch (error) {
                progressModal.hide();
                alert('Error: ' + error.message);
            }
        });
    });

    // Aprobar todas
    document.getElementById('btnApproveAll')?.addEventListener('click', function() {
        if (!confirm('¿Aprobar todas las secciones pendientes?')) return;

        fetch(`${baseUrl}report-sections/approve-all/${reportId}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            }
        });
    });
});
</script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    // Inicializar DataTables
    var table = $('#tableSections').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
        },
        pageLength: 25,
        order: [[0, 'asc'], [1, 'asc']],
        orderCellsTop: true,
        fixedHeader: true,
        columnDefs: [
            { orderable: false, targets: [6] } // Columna de acciones no ordenable
        ]
    });

    // Llenar los selectores de filtro con valores únicos
    function populateFilterSelects() {
        var columns = [0, 2, 4, 5]; // Nivel, Forma, Riesgo, Estado
        columns.forEach(function(colIdx) {
            var column = table.column(colIdx);
            var select = $('thead tr.filters th').eq(colIdx).find('select');

            // Obtener valores únicos
            var uniqueValues = [];
            column.data().each(function(d) {
                var text = $(d).text().trim();
                if (text && uniqueValues.indexOf(text) === -1) {
                    uniqueValues.push(text);
                }
            });

            // Ordenar y agregar opciones
            uniqueValues.sort().forEach(function(val) {
                select.append('<option value="' + val + '">' + val + '</option>');
            });
        });
    }
    populateFilterSelects();

    // Evento de filtro para selects
    $('thead tr.filters').on('change', '.filter-select', function() {
        var colIdx = $(this).data('column');
        var val = $(this).val();
        table.column(colIdx).search(val ? '^' + $.fn.dataTable.util.escapeRegex(val) + '$' : '', true, false).draw();
    });

    // Evento de filtro para inputs de texto
    $('thead tr.filters').on('keyup', '.filter-input', function() {
        var colIdx = $(this).data('column');
        table.column(colIdx).search(this.value).draw();
    });

    // Variables para el modal de prompt
    var promptModal = new bootstrap.Modal(document.getElementById('promptModal'));
    var currentSectionId = null;
    var baseUrl = '<?= base_url() ?>';

    // Re-enlazar eventos después de cada redibujado de la tabla
    function bindTableEvents() {
        // Evento Aprobar
        $('.btn-approve').off('click').on('click', function() {
            var btn = $(this);
            var sectionId = btn.data('id');

            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

            fetch(baseUrl + 'report-sections/approve/' + sectionId, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    btn.prop('disabled', false).html('<i class="fas fa-check"></i>');
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                btn.prop('disabled', false).html('<i class="fas fa-check"></i>');
                alert('Error: ' + error.message);
            });
        });

        // Evento Desaprobar
        $('.btn-unapprove').off('click').on('click', function() {
            var btn = $(this);
            var sectionId = btn.data('id');

            if (!confirm('¿Desaprobar esta sección?')) return;

            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

            fetch(baseUrl + 'report-sections/unapprove/' + sectionId, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    btn.prop('disabled', false).html('<i class="fas fa-undo"></i>');
                    alert('Error: ' + data.message);
                }
            });
        });

        // Evento Reset (para regenerar IA)
        $('.btn-reset').off('click').on('click', function() {
            var btn = $(this);
            var sectionId = btn.data('id');

            if (!confirm('¿Resetear esta sección? Se eliminará el texto generado y podrá regenerarlo con IA.')) return;

            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

            fetch(baseUrl + 'report-sections/reset/' + sectionId, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Preguntar si quiere regenerar ahora
                    if (confirm('Sección reseteada. ¿Desea regenerar el texto con IA ahora?')) {
                        btn.html('<i class="fas fa-spinner fa-spin"></i>');
                        fetch(baseUrl + 'report-sections/generate-ai/' + sectionId, {
                            method: 'POST',
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        })
                        .then(response => response.json())
                        .then(result => {
                            if (result.success) {
                                alert('Texto regenerado exitosamente.');
                            } else {
                                alert('Error al regenerar: ' + result.message);
                            }
                            window.location.reload();
                        });
                    } else {
                        window.location.reload();
                    }
                } else {
                    btn.prop('disabled', false).html('<i class="fas fa-redo"></i>');
                    alert('Error: ' + data.message);
                }
            });
        });

        // Evento Contexto IA (abrir modal)
        $('.btn-prompt').off('click').on('click', function() {
            currentSectionId = $(this).data('id');
            var currentPrompt = $(this).data('prompt') || '';
            $('#consultantPromptText').val(currentPrompt);
            promptModal.show();
        });
    }

    // Guardar contexto del consultor
    $('#btnSavePrompt').on('click', function() {
        if (!currentSectionId) return;

        var btn = $(this);
        var prompt = $('#consultantPromptText').val();

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Guardando...');

        fetch(baseUrl + 'report-sections/save-prompt/' + currentSectionId, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'consultant_prompt=' + encodeURIComponent(prompt)
        })
        .then(response => response.json())
        .then(data => {
            btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Guardar Contexto');
            if (data.success) {
                // Actualizar el data-prompt del botón
                $('[data-id="' + currentSectionId + '"].btn-prompt').data('prompt', prompt);
                promptModal.hide();

                // Preguntar si quiere regenerar
                if (prompt && confirm('Contexto guardado. ¿Desea regenerar el texto con IA ahora para aplicar este contexto?')) {
                    var resetBtn = $('[data-id="' + currentSectionId + '"].btn-reset');
                    if (resetBtn.length) {
                        // Primero resetear, luego regenerar
                        fetch(baseUrl + 'report-sections/reset/' + currentSectionId, {
                            method: 'POST',
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        })
                        .then(() => fetch(baseUrl + 'report-sections/generate-ai/' + currentSectionId, {
                            method: 'POST',
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        }))
                        .then(response => response.json())
                        .then(result => {
                            alert(result.success ? 'Texto regenerado con el nuevo contexto.' : 'Error: ' + result.message);
                            window.location.reload();
                        });
                    } else {
                        alert('Contexto guardado. La próxima vez que genere el texto IA, se usará este contexto.');
                    }
                } else if (!prompt) {
                    alert('Contexto eliminado correctamente.');
                } else {
                    alert('Contexto guardado. La próxima vez que regenere el texto IA, se usará este contexto.');
                }
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Guardar Contexto');
            alert('Error: ' + error.message);
        });
    });

    // Enlazar eventos inicialmente y re-enlazar después de cada redibujado
    bindTableEvents();
    table.on('draw', function() {
        bindTableEvents();
    });
});
</script>
<?= $this->endSection() ?>
