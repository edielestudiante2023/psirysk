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
                    <li class="breadcrumb-item active"><?= esc($title) ?></li>
                </ol>
            </nav>

            <!-- Alerta principal -->
            <div class="alert alert-warning mb-4">
                <h6 class="mb-2">
                    <i class="fas fa-info-circle me-2"></i>No hay participantes evaluados
                </h6>
                <p class="mb-0">
                    Este servicio no tiene trabajadores evaluados con el <strong><?= esc($formType) ?></strong>.
                    El mapa de calor no puede generarse sin datos de evaluación.
                </p>
            </div>

            <!-- Card informativa -->
            <div class="card shadow-sm">
                <div class="card-header bg-warning bg-opacity-10">
                    <h5 class="mb-0">
                        <i class="fas fa-heartbeat me-2"></i><?= esc($formType) ?>
                    </h5>
                </div>
                <div class="card-body">
                    <h6 class="text-primary mb-3">¿Qué evalúa este cuestionario?</h6>
                    <p class="text-muted">
                        El cuestionario de estrés evalúa los <strong>síntomas de estrés</strong> que pueden experimentar
                        los trabajadores como resultado de las condiciones laborales. Mide síntomas:
                    </p>
                    <ul class="text-muted">
                        <li><strong>Fisiológicos</strong>: dolores de cabeza, problemas gastrointestinales, tensión muscular</li>
                        <li><strong>Comportamentales</strong>: dificultad para concentrarse, irritabilidad, cambios en el sueño</li>
                        <li><strong>Cognitivos</strong>: dificultades de memoria, preocupación constante</li>
                    </ul>

                    <h6 class="text-primary mb-3 mt-4">Estructura del cuestionario</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card bg-light mb-3">
                                <div class="card-body">
                                    <h6 class="card-title text-primary">
                                        <i class="fas fa-clipboard-list me-2"></i>31 Preguntas
                                    </h6>
                                    <p class="card-text small text-muted mb-0">
                                        Mismo cuestionario para todas las personas, pero con <strong>baremos diferenciados</strong>
                                        según el tipo de cargo (Forma A para jefes/profesionales, Forma B para auxiliares/operarios).
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light mb-3">
                                <div class="card-body">
                                    <h6 class="card-title text-primary">
                                        <i class="fas fa-chart-bar me-2"></i>Baremos Diferenciados
                                    </h6>
                                    <p class="card-text small text-muted mb-0">
                                        Los rangos de interpretación (muy bajo, bajo, medio, alto, muy alto) son diferentes
                                        para la Forma A y Forma B.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h6 class="text-primary mb-3 mt-4">Posibles razones</h6>
                    <ul class="text-muted">
                        <li>No hay trabajadores asignados al servicio con el tipo de forma especificado</li>
                        <li>Los trabajadores aún no han completado el cuestionario de estrés</li>
                        <li>El servicio fue creado recientemente y está en proceso de evaluación</li>
                        <li>Los trabajadores fueron evaluados con la otra forma (A o B)</li>
                    </ul>

                    <div class="alert alert-info mt-4 mb-0">
                        <i class="fas fa-lightbulb me-2"></i>
                        <strong>Nota:</strong> Para generar el mapa de calor, es necesario que al menos un trabajador
                        haya completado todos los cuestionarios de la batería (Datos Generales, Intralaboral, Extralaboral y Estrés).
                    </div>
                </div>
            </div>

            <!-- Botones de navegación -->
            <div class="d-flex justify-content-between mt-4">
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
