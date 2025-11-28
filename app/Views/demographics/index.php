<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="fas fa-users text-primary me-2"></i>
                Variables Sociodemográficas - Interpretación IA
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('battery-services/' . $service['id']) ?>"><?= esc($service['company_name']) ?></a></li>
                    <li class="breadcrumb-item active">Ficha de Datos Generales IA</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('reports/ficha-datos-generales/' . $service['id']) ?>" class="btn btn-outline-info" target="_blank">
                <i class="fas fa-chart-pie me-1"></i> Ver Gráficas
            </a>
            <a href="<?= base_url('battery-services/' . $service['id']) ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Volver
            </a>
        </div>
    </div>

    <?php if (!$openAIConfigured): ?>
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Atención:</strong> OpenAI no está configurado. Configure la variable OPENAI_API_KEY en el archivo .env para habilitar la generación de interpretaciones con IA.
    </div>
    <?php endif; ?>

    <?php if (isset($aggregatedData['error'])): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle me-2"></i>
        <?= esc($aggregatedData['error']) ?>
    </div>
    <?php else: ?>

    <!-- Panel de Control de IA -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-building fa-2x text-primary me-3"></i>
                        <div>
                            <h5 class="mb-0"><?= esc($service['company_name']) ?></h5>
                            <p class="text-muted mb-0">
                                <i class="fas fa-users me-1"></i>
                                <strong><?= $aggregatedData['total_workers'] ?></strong> trabajadores evaluados
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                    <?php if ($openAIConfigured): ?>
                    <?php if ($savedInterpretation): ?>
                    <!-- Ya hay datos guardados -->
                    <span class="badge bg-success me-2" style="font-size: 0.9rem; vertical-align: middle;">
                        <i class="fas fa-check-circle me-1"></i> Guardado
                    </span>
                    <button type="button" class="btn btn-outline-warning" id="btnRegenerateIA">
                        <i class="fas fa-sync me-1"></i> Regenerar
                    </button>
                    <?php else: ?>
                    <!-- Sin datos guardados -->
                    <button type="button" class="btn btn-primary btn-lg" id="btnGenerateIA">
                        <i class="fas fa-brain me-2"></i> Generar Interpretaciones IA
                    </button>
                    <?php endif; ?>
                    <!-- Botón Guardar (oculto hasta que se genere/regenere) -->
                    <button type="button" class="btn btn-success btn-lg ms-2" id="btnSaveIA" style="display: none;">
                        <i class="fas fa-save me-2"></i> Guardar
                    </button>
                    <?php else: ?>
                    <button type="button" class="btn btn-secondary btn-lg" disabled>
                        <i class="fas fa-brain me-2"></i> IA No Disponible
                    </button>
                    <?php endif; ?>

                    <!-- Estado de guardado -->
                    <span id="saveStatus" class="badge ms-2" style="display: none; font-size: 0.9rem; vertical-align: middle;"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading State -->
    <div id="loadingState" class="card shadow-sm mb-4" style="display: none;">
        <div class="card-body text-center py-5">
            <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                <span class="visually-hidden">Generando...</span>
            </div>
            <h5>Generando Interpretaciones con IA...</h5>
            <p class="text-muted">Analizando el perfil sociodemográfico. Este proceso puede tomar 30-60 segundos.</p>
            <div class="progress mx-auto" style="max-width: 400px; height: 6px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%"></div>
            </div>
        </div>
    </div>

    <!-- Contenedor de Interpretaciones Generadas -->
    <div id="interpretationsContainer">
        <?php if ($savedInterpretation): ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-brain text-purple me-2"></i>Interpretaciones Generadas por IA</h6>
                <?php if (isset($savedAt) && $savedAt): ?>
                <small class="text-muted">
                    <i class="fas fa-database me-1"></i>Guardado: <?= date('d/m/Y H:i', strtotime($savedAt)) ?>
                </small>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <div class="interpretation-content" style="white-space: pre-wrap; line-height: 1.8;">
                    <?= nl2br(esc($savedInterpretation)) ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php /* DEBUG: Mostrar datos que llegan a la vista - COMENTADO (descomentar si se necesita debug)
    <?php if (ENVIRONMENT === 'development'): ?>
    <div class="alert alert-warning mb-4">
        <strong><i class="fas fa-bug me-2"></i>DEBUG - Datos en Vista Principal (index.php):</strong><br>
        <hr>
        <div class="row">
            <div class="col-md-6">
                <strong>Variables Simples:</strong><br>
                <small>
                <code>gender</code> isset: <span class="badge <?= isset($aggregatedData['gender']) ? 'bg-success' : 'bg-danger' ?>"><?= isset($aggregatedData['gender']) ? 'SI' : 'NO' ?></span> |
                type: <span class="badge bg-info"><?= isset($aggregatedData['gender']) ? gettype($aggregatedData['gender']) : 'N/A' ?></span> |
                count: <span class="badge bg-primary"><?= isset($aggregatedData['gender']) && is_array($aggregatedData['gender']) ? count($aggregatedData['gender']) : 'N/A' ?></span><br>

                <code>marital_status</code> isset: <span class="badge <?= isset($aggregatedData['marital_status']) ? 'bg-success' : 'bg-danger' ?>"><?= isset($aggregatedData['marital_status']) ? 'SI' : 'NO' ?></span> |
                count: <span class="badge bg-primary"><?= isset($aggregatedData['marital_status']) && is_array($aggregatedData['marital_status']) ? count($aggregatedData['marital_status']) : 'N/A' ?></span><br>

                <code>education_level</code> isset: <span class="badge <?= isset($aggregatedData['education_level']) ? 'bg-success' : 'bg-danger' ?>"><?= isset($aggregatedData['education_level']) ? 'SI' : 'NO' ?></span> |
                count: <span class="badge bg-primary"><?= isset($aggregatedData['education_level']) && is_array($aggregatedData['education_level']) ? count($aggregatedData['education_level']) : 'N/A' ?></span><br>

                <code>stratum</code> isset: <span class="badge <?= isset($aggregatedData['stratum']) ? 'bg-success' : 'bg-danger' ?>"><?= isset($aggregatedData['stratum']) ? 'SI' : 'NO' ?></span> |
                count: <span class="badge bg-primary"><?= isset($aggregatedData['stratum']) && is_array($aggregatedData['stratum']) ? count($aggregatedData['stratum']) : 'N/A' ?></span><br>

                <code>housing_type</code> isset: <span class="badge <?= isset($aggregatedData['housing_type']) ? 'bg-success' : 'bg-danger' ?>"><?= isset($aggregatedData['housing_type']) ? 'SI' : 'NO' ?></span> |
                count: <span class="badge bg-primary"><?= isset($aggregatedData['housing_type']) && is_array($aggregatedData['housing_type']) ? count($aggregatedData['housing_type']) : 'N/A' ?></span><br>
                </small>
            </div>
            <div class="col-md-6">
                <strong>Variables Compuestas:</strong><br>
                <small>
                <code>age_groups</code> isset: <span class="badge <?= isset($aggregatedData['age_groups']) ? 'bg-success' : 'bg-danger' ?>"><?= isset($aggregatedData['age_groups']) ? 'SI' : 'NO' ?></span><br>
                &nbsp;&nbsp;→ distribution count: <span class="badge bg-primary"><?= isset($aggregatedData['age_groups']['distribution']) && is_array($aggregatedData['age_groups']['distribution']) ? count($aggregatedData['age_groups']['distribution']) : 'N/A' ?></span><br>

                <code>dependents</code> isset: <span class="badge <?= isset($aggregatedData['dependents']) ? 'bg-success' : 'bg-danger' ?>"><?= isset($aggregatedData['dependents']) ? 'SI' : 'NO' ?></span><br>
                &nbsp;&nbsp;→ distribution count: <span class="badge bg-primary"><?= isset($aggregatedData['dependents']['distribution']) && is_array($aggregatedData['dependents']['distribution']) ? count($aggregatedData['dependents']['distribution']) : 'N/A' ?></span><br>

                <code>hours_per_day</code> isset: <span class="badge <?= isset($aggregatedData['hours_per_day']) ? 'bg-success' : 'bg-danger' ?>"><?= isset($aggregatedData['hours_per_day']) ? 'SI' : 'NO' ?></span><br>
                &nbsp;&nbsp;→ distribution count: <span class="badge bg-primary"><?= isset($aggregatedData['hours_per_day']['distribution']) && is_array($aggregatedData['hours_per_day']['distribution']) ? count($aggregatedData['hours_per_day']['distribution']) : 'N/A' ?></span><br>
                </small>
            </div>
        </div>
        <hr>
        <strong>Muestra de Datos Gender:</strong><br>
        <?php if (isset($aggregatedData['gender']) && is_array($aggregatedData['gender']) && count($aggregatedData['gender']) > 0): ?>
        <pre style="font-size: 11px; max-height: 100px; overflow: auto; background: #f5f5f5; padding: 5px;"><?= json_encode($aggregatedData['gender'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?></pre>
        <?php else: ?>
        <span class="text-danger">Sin datos</span>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    */ ?>

    <!-- Variables Sociodemográficas -->
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-table me-2"></i>Variables Sociodemográficas</h5>
        </div>
        <div class="card-body p-0">

            <!-- SEXO -->
            <div class="variable-section border-bottom">
                <div class="row g-0">
                    <div class="col-md-5 p-4 bg-light border-end">
                        <h6 class="text-primary mb-3"><i class="fas fa-venus-mars me-2"></i>SEXO</h6>
                        <div class="interpretation-box" id="interp-sexo">
                            <p class="text-muted fst-italic mb-0">Genera la interpretación con IA para ver el análisis.</p>
                        </div>
                    </div>
                    <div class="col-md-7 p-4">
                        <?= view('demographics/_distribution_table', ['data' => $aggregatedData['gender']]) ?>
                    </div>
                </div>
            </div>

            <!-- RANGO DE EDAD -->
            <div class="variable-section border-bottom">
                <div class="row g-0">
                    <div class="col-md-5 p-4 bg-light border-end">
                        <h6 class="text-primary mb-3"><i class="fas fa-birthday-cake me-2"></i>RANGO DE EDAD</h6>
                        <?php if (!empty($aggregatedData['age_groups']['statistics'])): ?>
                        <div class="mb-2">
                            <small class="text-muted">
                                Mín: <?= $aggregatedData['age_groups']['statistics']['min'] ?> |
                                Máx: <?= $aggregatedData['age_groups']['statistics']['max'] ?> |
                                Promedio: <?= $aggregatedData['age_groups']['statistics']['mean'] ?> años
                            </small>
                        </div>
                        <?php endif; ?>
                        <div class="interpretation-box" id="interp-edad">
                            <p class="text-muted fst-italic mb-0">Genera la interpretación con IA para ver el análisis.</p>
                        </div>
                    </div>
                    <div class="col-md-7 p-4">
                        <?= view('demographics/_distribution_table', ['data' => $aggregatedData['age_groups']['distribution']]) ?>
                    </div>
                </div>
            </div>

            <!-- ESTADO CIVIL -->
            <div class="variable-section border-bottom">
                <div class="row g-0">
                    <div class="col-md-5 p-4 bg-light border-end">
                        <h6 class="text-primary mb-3"><i class="fas fa-heart me-2"></i>ESTADO CIVIL</h6>
                        <div class="interpretation-box" id="interp-estado-civil">
                            <p class="text-muted fst-italic mb-0">Genera la interpretación con IA para ver el análisis.</p>
                        </div>
                    </div>
                    <div class="col-md-7 p-4">
                        <?= view('demographics/_distribution_table', ['data' => $aggregatedData['marital_status']]) ?>
                    </div>
                </div>
            </div>

            <!-- NIVEL EDUCATIVO -->
            <div class="variable-section border-bottom">
                <div class="row g-0">
                    <div class="col-md-5 p-4 bg-light border-end">
                        <h6 class="text-primary mb-3"><i class="fas fa-graduation-cap me-2"></i>NIVEL MÁXIMO DE ESCOLARIDAD</h6>
                        <div class="interpretation-box" id="interp-educacion">
                            <p class="text-muted fst-italic mb-0">Genera la interpretación con IA para ver el análisis.</p>
                        </div>
                    </div>
                    <div class="col-md-7 p-4">
                        <?= view('demographics/_distribution_table', ['data' => $aggregatedData['education_level']]) ?>
                    </div>
                </div>
            </div>

            <!-- ESTRATO -->
            <div class="variable-section border-bottom">
                <div class="row g-0">
                    <div class="col-md-5 p-4 bg-light border-end">
                        <h6 class="text-primary mb-3"><i class="fas fa-layer-group me-2"></i>ESTRATO SOCIOECONÓMICO</h6>
                        <div class="interpretation-box" id="interp-estrato">
                            <p class="text-muted fst-italic mb-0">Genera la interpretación con IA para ver el análisis.</p>
                        </div>
                    </div>
                    <div class="col-md-7 p-4">
                        <?= view('demographics/_distribution_table', ['data' => $aggregatedData['stratum']]) ?>
                    </div>
                </div>
            </div>

            <!-- TIPO DE VIVIENDA -->
            <div class="variable-section border-bottom">
                <div class="row g-0">
                    <div class="col-md-5 p-4 bg-light border-end">
                        <h6 class="text-primary mb-3"><i class="fas fa-home me-2"></i>TIPO DE VIVIENDA</h6>
                        <div class="interpretation-box" id="interp-vivienda">
                            <p class="text-muted fst-italic mb-0">Genera la interpretación con IA para ver el análisis.</p>
                        </div>
                    </div>
                    <div class="col-md-7 p-4">
                        <?= view('demographics/_distribution_table', ['data' => $aggregatedData['housing_type']]) ?>
                    </div>
                </div>
            </div>

            <!-- PERSONAS A CARGO -->
            <div class="variable-section border-bottom">
                <div class="row g-0">
                    <div class="col-md-5 p-4 bg-light border-end">
                        <h6 class="text-primary mb-3"><i class="fas fa-users me-2"></i>PERSONAS A CARGO</h6>
                        <?php if (!empty($aggregatedData['dependents']['statistics'])): ?>
                        <div class="mb-2">
                            <small class="text-muted">
                                Promedio: <?= $aggregatedData['dependents']['statistics']['mean'] ?> personas
                            </small>
                        </div>
                        <?php endif; ?>
                        <div class="interpretation-box" id="interp-dependientes">
                            <p class="text-muted fst-italic mb-0">Genera la interpretación con IA para ver el análisis.</p>
                        </div>
                    </div>
                    <div class="col-md-7 p-4">
                        <?= view('demographics/_distribution_table', ['data' => $aggregatedData['dependents']['distribution']]) ?>
                    </div>
                </div>
            </div>

            <!-- LUGAR DE RESIDENCIA -->
            <div class="variable-section border-bottom">
                <div class="row g-0">
                    <div class="col-md-5 p-4 bg-light border-end">
                        <h6 class="text-primary mb-3"><i class="fas fa-map-marker-alt me-2"></i>LUGAR DE RESIDENCIA</h6>
                        <div class="interpretation-box" id="interp-residencia">
                            <p class="text-muted fst-italic mb-0">Genera la interpretación con IA para ver el análisis.</p>
                        </div>
                    </div>
                    <div class="col-md-7 p-4">
                        <?= view('demographics/_distribution_table', ['data' => $aggregatedData['city_residence']]) ?>
                    </div>
                </div>
            </div>

            <!-- ANTIGÜEDAD EN LA EMPRESA -->
            <div class="variable-section border-bottom">
                <div class="row g-0">
                    <div class="col-md-5 p-4 bg-light border-end">
                        <h6 class="text-primary mb-3"><i class="fas fa-building me-2"></i>ANTIGÜEDAD EN LA EMPRESA</h6>
                        <div class="interpretation-box" id="interp-antiguedad-empresa">
                            <p class="text-muted fst-italic mb-0">Genera la interpretación con IA para ver el análisis.</p>
                        </div>
                    </div>
                    <div class="col-md-7 p-4">
                        <?= view('demographics/_distribution_table', ['data' => $aggregatedData['time_in_company']]) ?>
                    </div>
                </div>
            </div>

            <!-- ANTIGÜEDAD EN EL CARGO -->
            <div class="variable-section border-bottom">
                <div class="row g-0">
                    <div class="col-md-5 p-4 bg-light border-end">
                        <h6 class="text-primary mb-3"><i class="fas fa-user-clock me-2"></i>ANTIGÜEDAD EN EL CARGO</h6>
                        <div class="interpretation-box" id="interp-antiguedad-cargo">
                            <p class="text-muted fst-italic mb-0">Genera la interpretación con IA para ver el análisis.</p>
                        </div>
                    </div>
                    <div class="col-md-7 p-4">
                        <?= view('demographics/_distribution_table', ['data' => $aggregatedData['time_in_position']]) ?>
                    </div>
                </div>
            </div>

            <!-- TIPO DE CONTRATO -->
            <div class="variable-section border-bottom">
                <div class="row g-0">
                    <div class="col-md-5 p-4 bg-light border-end">
                        <h6 class="text-primary mb-3"><i class="fas fa-file-contract me-2"></i>TIPO DE CONTRATO</h6>
                        <div class="interpretation-box" id="interp-contrato">
                            <p class="text-muted fst-italic mb-0">Genera la interpretación con IA para ver el análisis.</p>
                        </div>
                    </div>
                    <div class="col-md-7 p-4">
                        <?= view('demographics/_distribution_table', ['data' => $aggregatedData['contract_type']]) ?>
                    </div>
                </div>
            </div>

            <!-- TIPO DE CARGO -->
            <div class="variable-section border-bottom">
                <div class="row g-0">
                    <div class="col-md-5 p-4 bg-light border-end">
                        <h6 class="text-primary mb-3"><i class="fas fa-user-tie me-2"></i>TIPO DE CARGO</h6>
                        <div class="interpretation-box" id="interp-cargo">
                            <p class="text-muted fst-italic mb-0">Genera la interpretación con IA para ver el análisis.</p>
                        </div>
                    </div>
                    <div class="col-md-7 p-4">
                        <?= view('demographics/_distribution_table', ['data' => $aggregatedData['position_type']]) ?>
                    </div>
                </div>
            </div>

            <!-- ÁREA/DEPARTAMENTO -->
            <div class="variable-section border-bottom">
                <div class="row g-0">
                    <div class="col-md-5 p-4 bg-light border-end">
                        <h6 class="text-primary mb-3"><i class="fas fa-sitemap me-2"></i>ÁREA / DEPARTAMENTO</h6>
                        <div class="interpretation-box" id="interp-area">
                            <p class="text-muted fst-italic mb-0">Genera la interpretación con IA para ver el análisis.</p>
                        </div>
                    </div>
                    <div class="col-md-7 p-4">
                        <?= view('demographics/_distribution_table', ['data' => $aggregatedData['department_area']]) ?>
                    </div>
                </div>
            </div>

            <!-- HORAS DE TRABAJO -->
            <div class="variable-section border-bottom">
                <div class="row g-0">
                    <div class="col-md-5 p-4 bg-light border-end">
                        <h6 class="text-primary mb-3"><i class="fas fa-clock me-2"></i>HORAS DE TRABAJO DIARIAS</h6>
                        <?php if (!empty($aggregatedData['hours_per_day']['statistics'])): ?>
                        <div class="mb-2">
                            <small class="text-muted">
                                Promedio: <?= $aggregatedData['hours_per_day']['statistics']['mean'] ?> horas
                            </small>
                        </div>
                        <?php endif; ?>
                        <div class="interpretation-box" id="interp-horas">
                            <p class="text-muted fst-italic mb-0">Genera la interpretación con IA para ver el análisis.</p>
                        </div>
                    </div>
                    <div class="col-md-7 p-4">
                        <?= view('demographics/_distribution_table', ['data' => $aggregatedData['hours_per_day']['distribution']]) ?>
                    </div>
                </div>
            </div>

            <!-- RANGO SALARIAL -->
            <div class="variable-section">
                <div class="row g-0">
                    <div class="col-md-5 p-4 bg-light border-end">
                        <h6 class="text-primary mb-3"><i class="fas fa-money-bill-wave me-2"></i>RANGO SALARIAL</h6>
                        <div class="interpretation-box" id="interp-salario">
                            <p class="text-muted fst-italic mb-0">Genera la interpretación con IA para ver el análisis.</p>
                        </div>
                    </div>
                    <div class="col-md-7 p-4">
                        <?= view('demographics/_distribution_table', ['data' => $aggregatedData['salary_type']]) ?>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Síntesis General (se muestra después de generar IA) -->
    <div id="sintesisContainer" class="card shadow-sm mt-4" style="display: none;">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>SÍNTESIS GENERAL</h5>
        </div>
        <div class="card-body">
            <div id="interp-sintesis" class="lead">
            </div>
        </div>
    </div>

    <!-- Comentarios del Consultor -->
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-comment-dots me-2"></i>Comentarios del Consultor</h5>
            <span id="commentSaveStatus" class="badge bg-light text-muted" style="display: none;">
                <i class="fas fa-check me-1"></i>Guardado
            </span>
        </div>
        <div class="card-body">
            <p class="text-muted small mb-3">
                <i class="fas fa-info-circle me-1"></i>
                Agregue sus observaciones, ajustes o comentarios adicionales sobre el análisis demográfico.
                Estos comentarios se guardarán automáticamente y serán incluidos en el informe final.
            </p>
            <textarea
                id="consultantComment"
                class="form-control"
                rows="5"
                placeholder="Escriba aquí sus comentarios sobre el análisis demográfico..."
                style="resize: vertical;"
            ><?= esc($consultantComment ?? '') ?></textarea>
            <div class="mt-3 d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    <i class="fas fa-keyboard me-1"></i>Los cambios se guardan automáticamente al escribir
                </small>
                <button type="button" id="btnSaveComment" class="btn btn-warning btn-sm">
                    <i class="fas fa-save me-1"></i>Guardar Comentario
                </button>
            </div>
        </div>
    </div>

    <?php endif; ?>
</div>

<style>
.text-purple { color: #6f42c1; }
.variable-section {
    transition: background-color 0.2s;
}
.variable-section:hover {
    background-color: #f8f9fa;
}
.interpretation-box {
    font-size: 0.95rem;
    line-height: 1.7;
    color: #333;
}
.interpretation-box.generated {
    background-color: #e8f4fd;
    padding: 1rem;
    border-radius: 0.5rem;
    border-left: 4px solid #0d6efd;
}
.interpretation-content strong {
    color: #0d6efd;
}
.progress {
    border-radius: 4px;
}
.progress-bar {
    font-size: 0.75rem;
    font-weight: 600;
}
@media print {
    .btn, .card-header .btn {
        display: none !important;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const serviceId = <?= $service['id'] ?>;
    const baseUrl = '<?= base_url() ?>';

    // Mapeo de secciones del texto IA a los contenedores HTML
    const sectionMapping = {
        'SEXO': 'interp-sexo',
        'RANGO DE EDAD': 'interp-edad',
        'ESTADO CIVIL': 'interp-estado-civil',
        'NIVEL EDUCATIVO': 'interp-educacion',
        'ESTRATO': 'interp-estrato',
        'VIVIENDA': 'interp-vivienda',
        'PERSONAS A CARGO': 'interp-dependientes',
        'LUGAR DE RESIDENCIA': 'interp-residencia',
        'ANTIGÜEDAD EN LA EMPRESA': 'interp-antiguedad-empresa',
        'ANTIGÜEDAD EN EL CARGO': 'interp-antiguedad-cargo',
        'TIPO DE CONTRATO': 'interp-contrato',
        'TIPO DE CARGO': 'interp-cargo',
        'ÁREA/DEPARTAMENTO': 'interp-area',
        'HORAS DE TRABAJO': 'interp-horas',
        'RANGO SALARIAL': 'interp-salario',
        'SÍNTESIS GENERAL': 'interp-sintesis'
    };

    // Función para parsear y distribuir las interpretaciones
    function distributeInterpretations(text) {
        const sections = text.split(/\*\*([A-ZÁÉÍÓÚÜÑ\s\/]+):\*\*/g);

        for (let i = 1; i < sections.length; i += 2) {
            const sectionName = sections[i].trim();
            const sectionContent = sections[i + 1] ? sections[i + 1].trim() : '';

            // Buscar el contenedor correspondiente
            for (const [key, containerId] of Object.entries(sectionMapping)) {
                if (sectionName.includes(key) || key.includes(sectionName)) {
                    const container = document.getElementById(containerId);
                    if (container) {
                        container.innerHTML = sectionContent;
                        container.classList.add('generated');

                        // Mostrar el contenedor de síntesis si es la síntesis
                        if (containerId === 'interp-sintesis') {
                            document.getElementById('sintesisContainer').style.display = 'block';
                        }
                    }
                    break;
                }
            }
        }
    }

    // Si hay interpretación guardada, distribuirla
    <?php if ($savedInterpretation): ?>
    const savedText = <?= json_encode($savedInterpretation) ?>;
    distributeInterpretations(savedText);
    // Ocultar el contenedor global ya que se distribuye
    document.getElementById('interpretationsContainer').innerHTML = '';
    <?php endif; ?>

    // Generar interpretación con IA
    const btnGenerate = document.getElementById('btnGenerateIA');
    const btnRegenerate = document.getElementById('btnRegenerateIA');
    const btnSaveIA = document.getElementById('btnSaveIA');
    const saveStatus = document.getElementById('saveStatus');

    // Variables para almacenar datos generados (antes de guardar)
    let pendingInterpretation = null;
    let pendingAggregatedData = null;

    async function generateInterpretation() {
        const loading = document.getElementById('loadingState');

        // Mostrar loading
        loading.style.display = 'block';

        // Ocultar botón guardar y estado
        if (btnSaveIA) btnSaveIA.style.display = 'none';
        if (saveStatus) saveStatus.style.display = 'none';

        // Limpiar interpretaciones anteriores
        Object.values(sectionMapping).forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.innerHTML = '<div class="spinner-border spinner-border-sm text-primary me-2"></div>Generando...';
                el.classList.remove('generated');
            }
        });
        document.getElementById('sintesisContainer').style.display = 'none';

        try {
            const response = await fetch(`${baseUrl}demographics-report/generate/${serviceId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();
            loading.style.display = 'none';

            if (data.success) {
                // Distribuir las interpretaciones en sus contenedores
                distributeInterpretations(data.interpretation);

                // Guardar datos pendientes para el botón Guardar
                pendingInterpretation = data.interpretation;
                pendingAggregatedData = data.aggregated_data;

                // Mostrar botón Guardar
                if (btnSaveIA) {
                    btnSaveIA.style.display = 'inline-block';
                }

                // Mostrar botón de regenerar si no existe
                if (!btnRegenerate) {
                    const newBtn = document.createElement('button');
                    newBtn.type = 'button';
                    newBtn.className = 'btn btn-outline-warning ms-2';
                    newBtn.id = 'btnRegenerateIA';
                    newBtn.innerHTML = '<i class="fas fa-sync me-1"></i> Regenerar';
                    newBtn.addEventListener('click', generateInterpretation);
                    btnGenerate.parentNode.insertBefore(newBtn, btnSaveIA);
                }
            } else {
                alert('Error: ' + data.message);
                // Restaurar mensaje por defecto
                Object.values(sectionMapping).forEach(id => {
                    const el = document.getElementById(id);
                    if (el && id !== 'interp-sintesis') {
                        el.innerHTML = '<p class="text-muted fst-italic mb-0">Error al generar. Intente nuevamente.</p>';
                    }
                });
            }

        } catch (error) {
            loading.style.display = 'none';
            alert('Error: ' + error.message);
        }
    }

    // ===== FUNCIÓN PARA GUARDAR SECCIONES =====
    async function saveInterpretations() {
        if (!pendingInterpretation) {
            alert('No hay interpretaciones para guardar. Genera primero.');
            return;
        }

        // Deshabilitar botón y mostrar estado
        if (btnSaveIA) {
            btnSaveIA.disabled = true;
            btnSaveIA.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Guardando...';
        }

        try {
            const response = await fetch(`${baseUrl}demographics-report/save-sections/${serviceId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    interpretation: pendingInterpretation,
                    aggregated_data: pendingAggregatedData
                })
            });

            const data = await response.json();

            if (data.success) {
                // Mostrar estado guardado
                if (saveStatus) {
                    saveStatus.style.display = 'inline-block';
                    saveStatus.className = 'badge bg-success ms-2';
                    saveStatus.innerHTML = '<i class="fas fa-check me-1"></i> Guardado ' + new Date().toLocaleTimeString();
                }

                // Ocultar botón guardar (ya está guardado)
                if (btnSaveIA) {
                    btnSaveIA.style.display = 'none';
                }

                // Limpiar datos pendientes
                pendingInterpretation = null;
                pendingAggregatedData = null;

            } else {
                alert('Error al guardar: ' + data.message);
                if (saveStatus) {
                    saveStatus.style.display = 'inline-block';
                    saveStatus.className = 'badge bg-danger ms-2';
                    saveStatus.innerHTML = '<i class="fas fa-times me-1"></i> Error';
                }
            }

        } catch (error) {
            alert('Error: ' + error.message);
            if (saveStatus) {
                saveStatus.style.display = 'inline-block';
                saveStatus.className = 'badge bg-danger ms-2';
                saveStatus.innerHTML = '<i class="fas fa-times me-1"></i> Error';
            }
        } finally {
            // Restaurar botón
            if (btnSaveIA) {
                btnSaveIA.disabled = false;
                btnSaveIA.innerHTML = '<i class="fas fa-save me-2"></i> Guardar';
            }
        }
    }

    if (btnGenerate) {
        btnGenerate.addEventListener('click', generateInterpretation);
    }

    if (btnRegenerate) {
        btnRegenerate.addEventListener('click', generateInterpretation);
    }

    if (btnSaveIA) {
        btnSaveIA.addEventListener('click', saveInterpretations);
    }

    // ===== FUNCIONALIDAD DE COMENTARIOS DEL CONSULTOR =====
    const commentTextarea = document.getElementById('consultantComment');
    const btnSaveComment = document.getElementById('btnSaveComment');
    const commentSaveStatus = document.getElementById('commentSaveStatus');
    let commentSaveTimeout = null;

    // Función para guardar comentario
    async function saveComment() {
        const comment = commentTextarea.value;

        try {
            btnSaveComment.disabled = true;
            btnSaveComment.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Guardando...';

            const response = await fetch(`${baseUrl}demographics-report/save-comment/${serviceId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ comment: comment })
            });

            const data = await response.json();

            if (data.success) {
                // Mostrar estado guardado
                commentSaveStatus.style.display = 'inline-block';
                commentSaveStatus.className = 'badge bg-success text-white';
                commentSaveStatus.innerHTML = '<i class="fas fa-check me-1"></i>Guardado';

                // Ocultar después de 3 segundos
                setTimeout(() => {
                    commentSaveStatus.style.display = 'none';
                }, 3000);
            } else {
                commentSaveStatus.style.display = 'inline-block';
                commentSaveStatus.className = 'badge bg-danger text-white';
                commentSaveStatus.innerHTML = '<i class="fas fa-times me-1"></i>Error';
            }

        } catch (error) {
            console.error('Error guardando comentario:', error);
            commentSaveStatus.style.display = 'inline-block';
            commentSaveStatus.className = 'badge bg-danger text-white';
            commentSaveStatus.innerHTML = '<i class="fas fa-times me-1"></i>Error';
        } finally {
            btnSaveComment.disabled = false;
            btnSaveComment.innerHTML = '<i class="fas fa-save me-1"></i>Guardar Comentario';
        }
    }

    // Guardar al hacer clic en el botón
    if (btnSaveComment) {
        btnSaveComment.addEventListener('click', saveComment);
    }

    // Auto-guardar al escribir (con debounce de 2 segundos)
    if (commentTextarea) {
        commentTextarea.addEventListener('input', function() {
            // Mostrar estado "guardando..."
            commentSaveStatus.style.display = 'inline-block';
            commentSaveStatus.className = 'badge bg-light text-muted';
            commentSaveStatus.innerHTML = '<i class="fas fa-edit me-1"></i>Escribiendo...';

            // Cancelar el timeout anterior si existe
            if (commentSaveTimeout) {
                clearTimeout(commentSaveTimeout);
            }

            // Establecer nuevo timeout para guardar
            commentSaveTimeout = setTimeout(saveComment, 2000);
        });
    }
});
</script>
<?= $this->endSection() ?>
