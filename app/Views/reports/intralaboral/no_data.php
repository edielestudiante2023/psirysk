<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?> - PsyRisk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('battery-services') ?>">Servicios</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('battery-services/' . $service['id']) ?>"><?= esc($service['service_name']) ?></a></li>
                    <li class="breadcrumb-item active"><?= esc($formType) ?></li>
                </ol>
            </nav>

            <!-- Card de Información -->
            <div class="card shadow-sm border-warning">
                <div class="card-header bg-warning bg-opacity-10 border-warning">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle fa-2x text-warning me-3"></i>
                        <div>
                            <h5 class="mb-0">Sin Datos Disponibles</h5>
                            <small class="text-muted"><?= esc($service['service_name']) ?></small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning mb-4">
                        <h6 class="mb-2">
                            <i class="fas fa-info-circle me-2"></i>No hay participantes evaluados
                        </h6>
                        <p class="mb-0">
                            Este servicio no tiene trabajadores evaluados con el <strong><?= esc($formType) ?></strong>.
                            El mapa de calor no puede generarse sin datos de evaluación.
                        </p>
                    </div>

                    <h6 class="mb-3"><i class="fas fa-clipboard-list me-2"></i>¿Qué es este formulario?</h6>

                    <?php if ($formType === 'Cuestionario Intralaboral Forma A'): ?>
                        <div class="card mb-3 border-info">
                            <div class="card-body">
                                <h6 class="text-info"><i class="fas fa-briefcase me-2"></i>Forma A - Perfil de Cargos</h6>
                                <p class="mb-2 small">El Cuestionario Intralaboral Forma A está diseñado para evaluar factores de riesgo psicosocial en trabajadores que ocupan cargos de:</p>
                                <ul class="small mb-0">
                                    <li>Jefes, supervisores o coordinadores con personal a cargo</li>
                                    <li>Profesionales, analistas o técnicos con responsabilidades profesionales</li>
                                    <li>Cargos con autonomía y toma de decisiones</li>
                                </ul>
                            </div>
                        </div>
                        <div class="card mb-3 border-secondary">
                            <div class="card-body">
                                <h6 class="text-secondary"><i class="fas fa-chart-bar me-2"></i>Estructura del Cuestionario</h6>
                                <p class="mb-2 small">Contiene <strong>123 preguntas</strong> que evalúan:</p>
                                <ul class="small mb-0">
                                    <li><strong>4 Dominios:</strong> Liderazgo y relaciones sociales, Control sobre el trabajo, Demandas del trabajo, Recompensas</li>
                                    <li><strong>19 Dimensiones</strong> psicosociales intralaborales</li>
                                </ul>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="card mb-3 border-info">
                            <div class="card-body">
                                <h6 class="text-info"><i class="fas fa-hard-hat me-2"></i>Forma B - Perfil de Cargos</h6>
                                <p class="mb-2 small">El Cuestionario Intralaboral Forma B está diseñado para evaluar factores de riesgo psicosocial en trabajadores que ocupan cargos de:</p>
                                <ul class="small mb-0">
                                    <li>Auxiliares, asistentes u operarios sin personal a cargo</li>
                                    <li>Cargos operativos y de apoyo</li>
                                    <li>Trabajadores con tareas definidas y menor autonomía</li>
                                </ul>
                            </div>
                        </div>
                        <div class="card mb-3 border-secondary">
                            <div class="card-body">
                                <h6 class="text-secondary"><i class="fas fa-chart-bar me-2"></i>Estructura del Cuestionario</h6>
                                <p class="mb-2 small">Contiene <strong>97 preguntas</strong> que evalúan:</p>
                                <ul class="small mb-0">
                                    <li><strong>4 Dominios:</strong> Liderazgo y relaciones sociales, Control sobre el trabajo, Demandas del trabajo, Recompensas</li>
                                    <li><strong>16 Dimensiones</strong> psicosociales intralaborales</li>
                                </ul>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="mb-2"><i class="fas fa-lightbulb me-2"></i>Posibles Razones</h6>
                            <ul class="small mb-0">
                                <li>Todos los trabajadores de este servicio fueron evaluados con la otra forma del cuestionario</li>
                                <li>La clasificación de cargos asignó a todos los trabajadores a una sola forma</li>
                                <li>El servicio está en proceso y aún no se han completado evaluaciones con esta forma</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="<?= base_url('battery-services/' . $service['id']) ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Volver al Servicio
                        </a>
                        <a href="<?= base_url('workers/service/' . $service['id']) ?>" class="btn btn-primary">
                            <i class="fas fa-users me-2"></i>Ver Trabajadores
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
