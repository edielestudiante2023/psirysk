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
            <?php
            $userRole = session()->get('role_name');
            $isClient = in_array($userRole, ['cliente_empresa', 'cliente_gestor']);
            $serviceUrl = $isClient ? base_url('client/battery-services/' . $service['id']) : base_url('battery-services/' . $service['id']);
            ?>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                    <?php if (!$isClient): ?>
                    <li class="breadcrumb-item"><a href="<?= base_url('battery-services') ?>">Servicios</a></li>
                    <?php endif; ?>
                    <li class="breadcrumb-item"><a href="<?= $serviceUrl ?>"><?= esc($service['service_name']) ?></a></li>
                    <li class="breadcrumb-item active">Extralaboral</li>
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

                    <h6 class="mb-3"><i class="fas fa-clipboard-list me-2"></i>¿Qué es este cuestionario?</h6>

                    <div class="card mb-3 border-info">
                        <div class="card-body">
                            <h6 class="text-info"><i class="fas fa-home me-2"></i>Cuestionario Extralaboral</h6>
                            <p class="mb-2 small">El Cuestionario de Factores de Riesgo Psicosocial Extralaboral evalúa condiciones del entorno fuera del trabajo que pueden afectar la salud y el desempeño laboral del trabajador.</p>
                            <ul class="small mb-0">
                                <li>Es el mismo cuestionario para todos los trabajadores (no tiene formas A o B)</li>
                                <li>Evalúa aspectos familiares, económicos, de vivienda y desplazamiento</li>
                                <li>Complementa la evaluación intralaboral para un diagnóstico integral</li>
                            </ul>
                        </div>
                    </div>

                    <div class="card mb-3 border-secondary">
                        <div class="card-body">
                            <h6 class="text-secondary"><i class="fas fa-chart-bar me-2"></i>Estructura del Cuestionario</h6>
                            <p class="mb-2 small">Contiene <strong>31 preguntas</strong> que evalúan <strong>7 dimensiones:</strong></p>
                            <ol class="small mb-0">
                                <li><strong>Tiempo fuera del trabajo:</strong> Tiempo para descanso y recuperación</li>
                                <li><strong>Relaciones familiares:</strong> Propiedades del grupo familiar y relaciones</li>
                                <li><strong>Comunicación y relaciones interpersonales:</strong> Cualidades de la comunicación familiar</li>
                                <li><strong>Situación económica del grupo familiar:</strong> Disponibilidad de recursos económicos</li>
                                <li><strong>Características de la vivienda:</strong> Condiciones de infraestructura y entorno</li>
                                <li><strong>Influencia del entorno extralaboral:</strong> Impacto de situaciones familiares en el trabajo</li>
                                <li><strong>Desplazamiento vivienda-trabajo:</strong> Tiempo y condiciones de traslado</li>
                            </ol>
                        </div>
                    </div>

                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="mb-2"><i class="fas fa-lightbulb me-2"></i>Posibles Razones</h6>
                            <ul class="small mb-0">
                                <li>El servicio está en proceso y los trabajadores aún no han completado este cuestionario</li>
                                <li>Los trabajadores completaron solo algunos cuestionarios de la batería</li>
                                <li>Puede haber un problema técnico que impidió guardar las respuestas</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white">
                    <?php
                    $userRole = session()->get('role_name');
                    $isClient = in_array($userRole, ['cliente_empresa', 'cliente_gestor']);
                    $backUrl = $isClient ? base_url('client/battery-services/' . $service['id']) : base_url('battery-services/' . $service['id']);
                    $workersUrl = $isClient ? base_url('client/workers/service/' . $service['id']) : base_url('workers/service/' . $service['id']);
                    ?>
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="<?= $backUrl ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Volver al Servicio
                        </a>
                        <a href="<?= $workersUrl ?>" class="btn btn-primary">
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
