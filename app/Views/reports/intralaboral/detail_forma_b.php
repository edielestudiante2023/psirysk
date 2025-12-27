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

<div class="container-fluid mt-4">

<?php
// Helper function para obtener colores según nivel de riesgo (3 colores agrupados)
function getNivelColor($nivel) {
    $colores = [
        'sin_riesgo' => '#5FE330',      // Verde - Sin riesgo o riesgo bajo
        'riesgo_bajo' => '#5FE330',     // Verde - Sin riesgo o riesgo bajo
        'riesgo_medio' => '#F5F74A',    // Amarillo - Riesgo medio
        'riesgo_alto' => '#FF4444',     // Rojo - Riesgo alto y muy alto
        'riesgo_muy_alto' => '#FF4444'  // Rojo - Riesgo alto y muy alto
    ];
    return $colores[$nivel] ?? '#9E9E9E';
}

// Helper function para obtener texto legible del nivel
function getNivelTexto($nivel) {
    $textos = [
        'sin_riesgo' => 'Sin riesgo',
        'riesgo_bajo' => 'Riesgo bajo',
        'riesgo_medio' => 'Riesgo medio',
        'riesgo_alto' => 'Riesgo alto',
        'riesgo_muy_alto' => 'Riesgo muy alto'
    ];
    return $textos[$nivel] ?? 'No definido';
}
?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('battery-services') ?>">Servicios</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('battery-services/view/' . $service['id']) ?>"><?= esc($service['service_name']) ?></a></li>
                    <li class="breadcrumb-item active">Intralaboral Forma B</li>
                </ol>
            </nav>

            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1"><i class="fas fa-fire me-2"></i><?= esc($title) ?></h2>
                    <p class="text-muted mb-0">
                        <i class="fas fa-building me-1"></i><?= esc($service['service_name']) ?>
                        <span class="ms-3"><i class="fas fa-users me-1"></i><?= $totalWorkers ?> trabajadores evaluados con Forma B</span>
                    </p>
                </div>
                <a href="<?= base_url('battery-services/view/' . $service['id']) ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Volver
                </a>
            </div>
        </div>
    </div>

    <!-- Estructura del Cuestionario -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-info">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-sitemap me-2"></i>Estructura del Cuestionario Intralaboral - Forma B</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary"><i class="fas fa-layer-group me-2"></i>DOMINIO 1: Liderazgo y Relaciones Sociales en el Trabajo</h6>
                            <ul class="list-unstyled ms-3">
                                <li>1. Características del liderazgo</li>
                                <li>2. Relaciones sociales en el trabajo</li>
                                <li>3. Retroalimentación del desempeño</li>
                            </ul>

                            <h6 class="text-primary mt-3"><i class="fas fa-layer-group me-2"></i>DOMINIO 2: Control sobre el Trabajo</h6>
                            <ul class="list-unstyled ms-3">
                                <li>5. Claridad de rol</li>
                                <li>6. Capacitación</li>
                                <li>7. Participación y manejo del cambio</li>
                                <li>8. Oportunidades para el uso y desarrollo de habilidades y conocimientos</li>
                                <li>9. Control y autonomía sobre el trabajo</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary"><i class="fas fa-layer-group me-2"></i>DOMINIO 3: Demandas del Trabajo</h6>
                            <ul class="list-unstyled ms-3">
                                <li>10. Demandas ambientales y de esfuerzo físico</li>
                                <li>11. Demandas emocionales</li>
                                <li>12. Demandas cuantitativas</li>
                                <li>13. Influencia del trabajo sobre el entorno extralaboral</li>
                                <li>15. Demandas de carga mental</li>
                                <li>17. Demandas de la jornada de trabajo</li>
                            </ul>

                            <h6 class="text-primary mt-3"><i class="fas fa-layer-group me-2"></i>DOMINIO 4: Recompensas</h6>
                            <ul class="list-unstyled ms-3">
                                <li>18. Recompensas derivadas de la pertenencia y del trabajo que se realiza</li>
                                <li>19. Reconocimiento y compensación</li>
                            </ul>
                        </div>
                    </div>
                    <div class="alert alert-warning mt-3 mb-0">
                        <small><i class="fas fa-info-circle me-1"></i><strong>Nota:</strong> La Forma B tiene 16 dimensiones distribuidas en 4 dominios. Se excluyen las dimensiones específicas de Forma A: Relación con colaboradores (4), Exigencias de responsabilidad del cargo (14), Consistencia del rol (16), que solo aplican para jefes y cargos con alta responsabilidad.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mapa de Calor Visual (Flexbox) -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-th me-2"></i>Mapa de Calor Intralaboral - Forma B</h5>
                </div>
                <div class="card-body p-0">
                    <div class="d-flex" style="min-height: 600px;">

                        <!-- TOTAL INTRALABORAL (20% izquierda) -->
                        <div class="d-flex align-items-center justify-content-center p-3" style="width: 20%; background-color: <?= getNivelColor($calculations['intralaboral_total']['nivel']) ?>; color: white; border-right: 2px solid white;">
                            <div class="text-center">
                                <h3 class="mb-2" style="font-size: 1.8rem; font-weight: bold;">TOTAL<br>INTRALABORAL</h3>
                                <div style="font-size: 2.5rem; font-weight: bold;"><?= number_format($calculations['intralaboral_total']['promedio'], 1) ?></div>
                                <div style="font-size: 1.1rem; margin-top: 10px; font-weight: 600;"><?= strtoupper(getNivelTexto($calculations['intralaboral_total']['nivel'])) ?></div>
                            </div>
                        </div>

                        <!-- DOMINIOS + DIMENSIONES (80% derecha) -->
                        <div class="d-flex flex-column" style="width: 80%;">

                            <!-- DOMINIO 1: LIDERAZGO Y RELACIONES SOCIALES -->
                            <div class="d-flex" style="flex: 1; border-bottom: 2px solid white;">
                                <!-- Dominio Header -->
                                <div class="d-flex align-items-center justify-content-center p-2" style="width: 25%; background-color: <?= getNivelColor($calculations['dom_liderazgo']['nivel']) ?>; color: white; border-right: 2px solid white;">
                                    <div class="text-center">
                                        <div style="font-size: 0.85rem; font-weight: bold;">LIDERAZGO Y<br>RELACIONES SOCIALES</div>
                                        <div style="font-size: 1.5rem; font-weight: bold; margin-top: 5px;"><?= number_format($calculations['dom_liderazgo']['promedio'], 1) ?></div>
                                        <div style="font-size: 0.8rem; margin-top: 3px;"><?= getNivelTexto($calculations['dom_liderazgo']['nivel']) ?></div>
                                    </div>
                                </div>
                                <!-- Dimensiones del Dominio 1 -->
                                <div class="d-flex flex-column" style="width: 75%;">
                                    <div class="d-flex align-items-center justify-content-center p-2" style="flex: 1; background-color: <?= getNivelColor($calculations['dim_caracteristicas_liderazgo']['nivel']) ?>; color: white; border-bottom: 1px solid white;">
                                        <div class="text-center" style="font-size: 0.75rem;">
                                            <div style="font-weight: 600;">Características del liderazgo</div>
                                            <div style="font-size: 1.2rem; font-weight: bold; margin-top: 3px;"><?= number_format($calculations['dim_caracteristicas_liderazgo']['promedio'], 1) ?></div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-center p-2" style="flex: 1; background-color: <?= getNivelColor($calculations['dim_relaciones_sociales']['nivel']) ?>; color: white; border-bottom: 1px solid white;">
                                        <div class="text-center" style="font-size: 0.75rem;">
                                            <div style="font-weight: 600;">Relaciones sociales en el trabajo</div>
                                            <div style="font-size: 1.2rem; font-weight: bold; margin-top: 3px;"><?= number_format($calculations['dim_relaciones_sociales']['promedio'], 1) ?></div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-center p-2" style="flex: 1; background-color: <?= getNivelColor($calculations['dim_retroalimentacion']['nivel']) ?>; color: white;">
                                        <div class="text-center" style="font-size: 0.75rem;">
                                            <div style="font-weight: 600;">Retroalimentación del desempeño</div>
                                            <div style="font-size: 1.2rem; font-weight: bold; margin-top: 3px;"><?= number_format($calculations['dim_retroalimentacion']['promedio'], 1) ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- DOMINIO 2: CONTROL SOBRE EL TRABAJO -->
                            <div class="d-flex" style="flex: 1; border-bottom: 2px solid white;">
                                <div class="d-flex align-items-center justify-content-center p-2" style="width: 25%; background-color: <?= getNivelColor($calculations['dom_control']['nivel']) ?>; color: white; border-right: 2px solid white;">
                                    <div class="text-center">
                                        <div style="font-size: 0.85rem; font-weight: bold;">CONTROL SOBRE<br>EL TRABAJO</div>
                                        <div style="font-size: 1.5rem; font-weight: bold; margin-top: 5px;"><?= number_format($calculations['dom_control']['promedio'], 1) ?></div>
                                        <div style="font-size: 0.8rem; margin-top: 3px;"><?= getNivelTexto($calculations['dom_control']['nivel']) ?></div>
                                    </div>
                                </div>
                                <div class="d-flex flex-column" style="width: 75%;">
                                    <div class="d-flex align-items-center justify-content-center p-2" style="flex: 1; background-color: <?= getNivelColor($calculations['dim_claridad_rol']['nivel']) ?>; color: white; border-bottom: 1px solid white;">
                                        <div class="text-center" style="font-size: 0.75rem;">
                                            <div style="font-weight: 600;">Claridad de rol</div>
                                            <div style="font-size: 1.2rem; font-weight: bold; margin-top: 3px;"><?= number_format($calculations['dim_claridad_rol']['promedio'], 1) ?></div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-center p-2" style="flex: 1; background-color: <?= getNivelColor($calculations['dim_capacitacion']['nivel']) ?>; color: white; border-bottom: 1px solid white;">
                                        <div class="text-center" style="font-size: 0.75rem;">
                                            <div style="font-weight: 600;">Capacitación</div>
                                            <div style="font-size: 1.2rem; font-weight: bold; margin-top: 3px;"><?= number_format($calculations['dim_capacitacion']['promedio'], 1) ?></div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-center p-2" style="flex: 1; background-color: <?= getNivelColor($calculations['dim_participacion_cambio']['nivel']) ?>; color: white; border-bottom: 1px solid white;">
                                        <div class="text-center" style="font-size: 0.75rem;">
                                            <div style="font-weight: 600;">Participación y manejo del cambio</div>
                                            <div style="font-size: 1.2rem; font-weight: bold; margin-top: 3px;"><?= number_format($calculations['dim_participacion_cambio']['promedio'], 1) ?></div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-center p-2" style="flex: 1; background-color: <?= getNivelColor($calculations['dim_oportunidades_desarrollo']['nivel']) ?>; color: white; border-bottom: 1px solid white;">
                                        <div class="text-center" style="font-size: 0.75rem;">
                                            <div style="font-weight: 600;">Oportunidades para el uso y desarrollo de habilidades y conocimientos</div>
                                            <div style="font-size: 1.2rem; font-weight: bold; margin-top: 3px;"><?= number_format($calculations['dim_oportunidades_desarrollo']['promedio'], 1) ?></div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-center p-2" style="flex: 1; background-color: <?= getNivelColor($calculations['dim_control_autonomia']['nivel']) ?>; color: white;">
                                        <div class="text-center" style="font-size: 0.75rem;">
                                            <div style="font-weight: 600;">Control y autonomía sobre el trabajo</div>
                                            <div style="font-size: 1.2rem; font-weight: bold; margin-top: 3px;"><?= number_format($calculations['dim_control_autonomia']['promedio'], 1) ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- DOMINIO 3: DEMANDAS DEL TRABAJO (6 dimensiones para Forma B) -->
                            <div class="d-flex" style="flex: 1; border-bottom: 2px solid white;">
                                <div class="d-flex align-items-center justify-content-center p-2" style="width: 25%; background-color: <?= getNivelColor($calculations['dom_demandas']['nivel']) ?>; color: white; border-right: 2px solid white;">
                                    <div class="text-center">
                                        <div style="font-size: 0.85rem; font-weight: bold;">DEMANDAS<br>DEL TRABAJO</div>
                                        <div style="font-size: 1.5rem; font-weight: bold; margin-top: 5px;"><?= number_format($calculations['dom_demandas']['promedio'], 1) ?></div>
                                        <div style="font-size: 0.8rem; margin-top: 3px;"><?= getNivelTexto($calculations['dom_demandas']['nivel']) ?></div>
                                    </div>
                                </div>
                                <div class="d-flex flex-column" style="width: 75%;">
                                    <div class="d-flex align-items-center justify-content-center p-2" style="flex: 1; background-color: <?= getNivelColor($calculations['dim_demandas_ambientales']['nivel']) ?>; color: white; border-bottom: 1px solid white;">
                                        <div class="text-center" style="font-size: 0.75rem;">
                                            <div style="font-weight: 600;">Demandas ambientales y de esfuerzo físico</div>
                                            <div style="font-size: 1.2rem; font-weight: bold; margin-top: 3px;"><?= number_format($calculations['dim_demandas_ambientales']['promedio'], 1) ?></div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-center p-2" style="flex: 1; background-color: <?= getNivelColor($calculations['dim_demandas_emocionales']['nivel']) ?>; color: white; border-bottom: 1px solid white;">
                                        <div class="text-center" style="font-size: 0.75rem;">
                                            <div style="font-weight: 600;">Demandas emocionales</div>
                                            <div style="font-size: 1.2rem; font-weight: bold; margin-top: 3px;"><?= number_format($calculations['dim_demandas_emocionales']['promedio'], 1) ?></div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-center p-2" style="flex: 1; background-color: <?= getNivelColor($calculations['dim_demandas_cuantitativas']['nivel']) ?>; color: white; border-bottom: 1px solid white;">
                                        <div class="text-center" style="font-size: 0.75rem;">
                                            <div style="font-weight: 600;">Demandas cuantitativas</div>
                                            <div style="font-size: 1.2rem; font-weight: bold; margin-top: 3px;"><?= number_format($calculations['dim_demandas_cuantitativas']['promedio'], 1) ?></div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-center p-2" style="flex: 1; background-color: <?= getNivelColor($calculations['dim_influencia_entorno']['nivel']) ?>; color: white; border-bottom: 1px solid white;">
                                        <div class="text-center" style="font-size: 0.75rem;">
                                            <div style="font-weight: 600;">Influencia del trabajo sobre el entorno extralaboral</div>
                                            <div style="font-size: 1.2rem; font-weight: bold; margin-top: 3px;"><?= number_format($calculations['dim_influencia_entorno']['promedio'], 1) ?></div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-center p-2" style="flex: 1; background-color: <?= getNivelColor($calculations['dim_demandas_carga_mental']['nivel']) ?>; color: white; border-bottom: 1px solid white;">
                                        <div class="text-center" style="font-size: 0.75rem;">
                                            <div style="font-weight: 600;">Demandas de carga mental</div>
                                            <div style="font-size: 1.2rem; font-weight: bold; margin-top: 3px;"><?= number_format($calculations['dim_demandas_carga_mental']['promedio'], 1) ?></div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-center p-2" style="flex: 1; background-color: <?= getNivelColor($calculations['dim_demandas_jornada']['nivel']) ?>; color: white;">
                                        <div class="text-center" style="font-size: 0.75rem;">
                                            <div style="font-weight: 600;">Demandas de la jornada de trabajo</div>
                                            <div style="font-size: 1.2rem; font-weight: bold; margin-top: 3px;"><?= number_format($calculations['dim_demandas_jornada']['promedio'], 1) ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- DOMINIO 4: RECOMPENSAS -->
                            <div class="d-flex" style="flex: 1;">
                                <div class="d-flex align-items-center justify-content-center p-2" style="width: 25%; background-color: <?= getNivelColor($calculations['dom_recompensas']['nivel']) ?>; color: white; border-right: 2px solid white;">
                                    <div class="text-center">
                                        <div style="font-size: 0.85rem; font-weight: bold;">RECOMPENSAS</div>
                                        <div style="font-size: 1.5rem; font-weight: bold; margin-top: 5px;"><?= number_format($calculations['dom_recompensas']['promedio'], 1) ?></div>
                                        <div style="font-size: 0.8rem; margin-top: 3px;"><?= getNivelTexto($calculations['dom_recompensas']['nivel']) ?></div>
                                    </div>
                                </div>
                                <div class="d-flex flex-column" style="width: 75%;">
                                    <div class="d-flex align-items-center justify-content-center p-2" style="flex: 1; background-color: <?= getNivelColor($calculations['dim_recompensas_pertenencia']['nivel']) ?>; color: white; border-bottom: 1px solid white;">
                                        <div class="text-center" style="font-size: 0.75rem;">
                                            <div style="font-weight: 600;">Recompensas derivadas de la pertenencia y del trabajo que se realiza</div>
                                            <div style="font-size: 1.2rem; font-weight: bold; margin-top: 3px;"><?= number_format($calculations['dim_recompensas_pertenencia']['promedio'], 1) ?></div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-center p-2" style="flex: 1; background-color: <?= getNivelColor($calculations['dim_reconocimiento_compensacion']['nivel']) ?>; color: white;">
                                        <div class="text-center" style="font-size: 0.75rem;">
                                            <div style="font-weight: 600;">Reconocimiento y compensación</div>
                                            <div style="font-size: 1.2rem; font-weight: bold; margin-top: 3px;"><?= number_format($calculations['dim_reconocimiento_compensacion']['promedio'], 1) ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Título de sección de cálculos -->
    <div class="row mb-3">
        <div class="col-12">
            <h4><i class="fas fa-calculator me-2"></i>Cálculos Detallados</h4>
            <p class="text-muted">Promedios aritméticos y aplicación de baremos según Resolución 2404/2019</p>
        </div>
    </div>

    <!-- CARDS DE CÁLCULOS (21 total: 1 total + 4 dominios + 16 dimensiones) -->

    <!-- 1. TOTAL INTRALABORAL -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-start border-primary border-4">
                <div class="card-header" style="background-color: <?= getNivelColor($calculations['intralaboral_total']['nivel']) ?>; color: white;">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>TOTAL INTRALABORAL</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Cálculo del Promedio:</h6>
                            <p class="mb-2"><strong>Suma de puntajes:</strong> <?= number_format($calculations['intralaboral_total']['suma'], 2) ?></p>
                            <p class="mb-2"><strong>Cantidad de trabajadores:</strong> <?= $calculations['intralaboral_total']['cantidad'] ?></p>
                            <p class="mb-3"><strong>Promedio:</strong> <?= number_format($calculations['intralaboral_total']['suma'], 2) ?> ÷ <?= $calculations['intralaboral_total']['cantidad'] ?> = <span class="badge bg-info" style="font-size: 1.1rem;"><?= number_format($calculations['intralaboral_total']['promedio'], 2) ?></span></p>

                            <h6>Nivel de Riesgo:</h6>
                            <p>Con un puntaje de <strong><?= number_format($calculations['intralaboral_total']['promedio'], 2) ?></strong>, según la Tabla 33 (Forma B), el nivel de riesgo es:</p>
                            <h4><span class="badge" style="background-color: <?= getNivelColor($calculations['intralaboral_total']['nivel']) ?>;"><?= strtoupper(getNivelTexto($calculations['intralaboral_total']['nivel'])) ?></span></h4>
                        </div>
                        <div class="col-md-6">
                            <h6>Baremo Aplicado (Tabla 33 - Forma B):</h6>
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nivel de Riesgo</th>
                                        <th>Rango</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($calculations['intralaboral_total']['baremo'] as $nivelKey => $rango): ?>
                                    <tr <?= $nivelKey === $calculations['intralaboral_total']['nivel'] ? 'class="table-success fw-bold"' : '' ?>>
                                        <td><?= getNivelTexto($nivelKey) ?></td>
                                        <td><?= $rango[0] ?> - <?= $rango[1] ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <p class="text-muted small mb-0"><i class="fas fa-info-circle me-1"></i>El promedio <?= number_format($calculations['intralaboral_total']['promedio'], 2) ?> se encuentra en el rango <?= $calculations['intralaboral_total']['rango_aplicado'][0] ?> - <?= $calculations['intralaboral_total']['rango_aplicado'][1] ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- DOMINIOS (4 cards) -->
    <div class="row mb-3">
        <div class="col-12"><h5 class="text-primary"><i class="fas fa-layer-group me-2"></i>DOMINIOS</h5></div>
    </div>

    <!-- DOMINIO 1: LIDERAZGO -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header" style="background-color: <?= getNivelColor($calculations['dom_liderazgo']['nivel']) ?>; color: white;">
                    <h6 class="mb-0">Dominio: Liderazgo y relaciones sociales en el trabajo</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Promedio:</strong> <?= number_format($calculations['dom_liderazgo']['suma'], 2) ?> ÷ <?= $calculations['dom_liderazgo']['cantidad'] ?> = <span class="badge bg-info"><?= number_format($calculations['dom_liderazgo']['promedio'], 2) ?></span></p>
                            <h6><span class="badge" style="background-color: <?= getNivelColor($calculations['dom_liderazgo']['nivel']) ?>;"><?= strtoupper(getNivelTexto($calculations['dom_liderazgo']['nivel'])) ?></span></h6>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light"><tr><th>Nivel</th><th>Rango</th></tr></thead>
                                <tbody>
                                    <?php foreach ($calculations['dom_liderazgo']['baremo'] as $nivelKey => $rango): ?>
                                    <tr <?= $nivelKey === $calculations['dom_liderazgo']['nivel'] ? 'class="table-success fw-bold"' : '' ?>>
                                        <td><?= getNivelTexto($nivelKey) ?></td>
                                        <td><?= $rango[0] ?> - <?= $rango[1] ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- DOMINIO 2: CONTROL -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header" style="background-color: <?= getNivelColor($calculations['dom_control']['nivel']) ?>; color: white;">
                    <h6 class="mb-0">Dominio: Control sobre el trabajo</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Promedio:</strong> <?= number_format($calculations['dom_control']['suma'], 2) ?> ÷ <?= $calculations['dom_control']['cantidad'] ?> = <span class="badge bg-info"><?= number_format($calculations['dom_control']['promedio'], 2) ?></span></p>
                            <h6><span class="badge" style="background-color: <?= getNivelColor($calculations['dom_control']['nivel']) ?>;"><?= strtoupper(getNivelTexto($calculations['dom_control']['nivel'])) ?></span></h6>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light"><tr><th>Nivel</th><th>Rango</th></tr></thead>
                                <tbody>
                                    <?php foreach ($calculations['dom_control']['baremo'] as $nivelKey => $rango): ?>
                                    <tr <?= $nivelKey === $calculations['dom_control']['nivel'] ? 'class="table-success fw-bold"' : '' ?>>
                                        <td><?= getNivelTexto($nivelKey) ?></td>
                                        <td><?= $rango[0] ?> - <?= $rango[1] ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- DOMINIO 3: DEMANDAS -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header" style="background-color: <?= getNivelColor($calculations['dom_demandas']['nivel']) ?>; color: white;">
                    <h6 class="mb-0">Dominio: Demandas del trabajo</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Promedio:</strong> <?= number_format($calculations['dom_demandas']['suma'], 2) ?> ÷ <?= $calculations['dom_demandas']['cantidad'] ?> = <span class="badge bg-info"><?= number_format($calculations['dom_demandas']['promedio'], 2) ?></span></p>
                            <h6><span class="badge" style="background-color: <?= getNivelColor($calculations['dom_demandas']['nivel']) ?>;"><?= strtoupper(getNivelTexto($calculations['dom_demandas']['nivel'])) ?></span></h6>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light"><tr><th>Nivel</th><th>Rango</th></tr></thead>
                                <tbody>
                                    <?php foreach ($calculations['dom_demandas']['baremo'] as $nivelKey => $rango): ?>
                                    <tr <?= $nivelKey === $calculations['dom_demandas']['nivel'] ? 'class="table-success fw-bold"' : '' ?>>
                                        <td><?= getNivelTexto($nivelKey) ?></td>
                                        <td><?= $rango[0] ?> - <?= $rango[1] ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- DOMINIO 4: RECOMPENSAS -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header" style="background-color: <?= getNivelColor($calculations['dom_recompensas']['nivel']) ?>; color: white;">
                    <h6 class="mb-0">Dominio: Recompensas</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Promedio:</strong> <?= number_format($calculations['dom_recompensas']['suma'], 2) ?> ÷ <?= $calculations['dom_recompensas']['cantidad'] ?> = <span class="badge bg-info"><?= number_format($calculations['dom_recompensas']['promedio'], 2) ?></span></p>
                            <h6><span class="badge" style="background-color: <?= getNivelColor($calculations['dom_recompensas']['nivel']) ?>;"><?= strtoupper(getNivelTexto($calculations['dom_recompensas']['nivel'])) ?></span></h6>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light"><tr><th>Nivel</th><th>Rango</th></tr></thead>
                                <tbody>
                                    <?php foreach ($calculations['dom_recompensas']['baremo'] as $nivelKey => $rango): ?>
                                    <tr <?= $nivelKey === $calculations['dom_recompensas']['nivel'] ? 'class="table-success fw-bold"' : '' ?>>
                                        <td><?= getNivelTexto($nivelKey) ?></td>
                                        <td><?= $rango[0] ?> - <?= $rango[1] ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- DIMENSIONES (16 cards para Forma B) -->
    <div class="row mb-3">
        <div class="col-12"><h5 class="text-success"><i class="fas fa-puzzle-piece me-2"></i>DIMENSIONES (16 para Forma B)</h5></div>
    </div>

    <?php
    // Array de dimensiones para Forma B (16 dimensiones, NO incluye las 3 exclusivas de Forma A)
    $dimensiones = [
        ['key' => 'dim_caracteristicas_liderazgo', 'nombre' => 'Características del liderazgo', 'dominio' => 'Liderazgo'],
        ['key' => 'dim_relaciones_sociales', 'nombre' => 'Relaciones sociales en el trabajo', 'dominio' => 'Liderazgo'],
        ['key' => 'dim_retroalimentacion', 'nombre' => 'Retroalimentación del desempeño', 'dominio' => 'Liderazgo'],
        ['key' => 'dim_claridad_rol', 'nombre' => 'Claridad de rol', 'dominio' => 'Control'],
        ['key' => 'dim_capacitacion', 'nombre' => 'Capacitación', 'dominio' => 'Control'],
        ['key' => 'dim_participacion_cambio', 'nombre' => 'Participación y manejo del cambio', 'dominio' => 'Control'],
        ['key' => 'dim_oportunidades_desarrollo', 'nombre' => 'Oportunidades para el uso y desarrollo de habilidades y conocimientos', 'dominio' => 'Control'],
        ['key' => 'dim_control_autonomia', 'nombre' => 'Control y autonomía sobre el trabajo', 'dominio' => 'Control'],
        ['key' => 'dim_demandas_ambientales', 'nombre' => 'Demandas ambientales y de esfuerzo físico', 'dominio' => 'Demandas'],
        ['key' => 'dim_demandas_emocionales', 'nombre' => 'Demandas emocionales', 'dominio' => 'Demandas'],
        ['key' => 'dim_demandas_cuantitativas', 'nombre' => 'Demandas cuantitativas', 'dominio' => 'Demandas'],
        ['key' => 'dim_influencia_entorno', 'nombre' => 'Influencia del trabajo sobre el entorno extralaboral', 'dominio' => 'Demandas'],
        ['key' => 'dim_demandas_carga_mental', 'nombre' => 'Demandas de carga mental', 'dominio' => 'Demandas'],
        ['key' => 'dim_demandas_jornada', 'nombre' => 'Demandas de la jornada de trabajo', 'dominio' => 'Demandas'],
        ['key' => 'dim_recompensas_pertenencia', 'nombre' => 'Recompensas derivadas de la pertenencia a la organización y del trabajo que se realiza', 'dominio' => 'Recompensas'],
        ['key' => 'dim_reconocimiento_compensacion', 'nombre' => 'Reconocimiento y compensación', 'dominio' => 'Recompensas'],
    ];
    ?>

    <?php foreach ($dimensiones as $index => $dim): ?>
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header" style="background-color: <?= getNivelColor($calculations[$dim['key']]['nivel']) ?>; color: white;">
                    <h6 class="mb-0"><?= $index + 1 ?>. <?= esc($dim['nombre']) ?> <small class="ms-2">(<?= $dim['dominio'] ?>)</small></h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Promedio:</strong> <?= number_format($calculations[$dim['key']]['suma'], 2) ?> ÷ <?= $calculations[$dim['key']]['cantidad'] ?> = <span class="badge bg-info"><?= number_format($calculations[$dim['key']]['promedio'], 2) ?></span></p>
                            <h6><span class="badge" style="background-color: <?= getNivelColor($calculations[$dim['key']]['nivel']) ?>;"><?= strtoupper(getNivelTexto($calculations[$dim['key']]['nivel'])) ?></span></h6>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light"><tr><th>Nivel</th><th>Rango</th></tr></thead>
                                <tbody>
                                    <?php foreach ($calculations[$dim['key']]['baremo'] as $nivelKey => $rango): ?>
                                    <tr <?= $nivelKey === $calculations[$dim['key']]['nivel'] ? 'class="table-success fw-bold"' : '' ?>>
                                        <td><?= getNivelTexto($nivelKey) ?></td>
                                        <td><?= $rango[0] ?> - <?= $rango[1] ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Nota metodológica -->
    <div class="row mt-4 mb-4">
        <div class="col-12">
            <div class="alert alert-info">
                <h6><i class="fas fa-info-circle me-2"></i>Nota Metodológica</h6>
                <p class="mb-2">Los cálculos se realizan utilizando el <strong>promedio aritmético</strong> de los puntajes transformados de todos los trabajadores evaluados con Forma B. Los baremos aplicados corresponden a las tablas oficiales de la Resolución 2404 de 2019: Tabla 33 para Total Intralaboral Forma B, Tabla 32 para Dominios, y Tabla 30 para las 16 Dimensiones de Forma B.</p>
                <p class="mb-0"><strong>Nota:</strong> La Forma B tiene 16 dimensiones (3 menos que Forma A). Las dimensiones exclusivas de Forma A que NO aplican para Forma B son: <em>Relación con colaboradores</em> (Dominio Liderazgo), <em>Exigencias de responsabilidad del cargo</em> (Dominio Demandas), y <em>Consistencia del rol</em> (Dominio Demandas).</p>
            </div>
        </div>
    </div>

    <!-- Interpretación de Niveles de Riesgo -->
    <div class="row mb-4">
        <div class="col-12">
            <h5 class="mb-3"><i class="fas fa-book me-2"></i>Interpretación de Niveles de Riesgo Intralaboral</h5>
            <div class="card shadow-sm">
                <div class="card-body">
                    <p class="text-muted mb-3">Definiciones oficiales según Resolución 2404/2019 para la interpretación de los niveles de riesgo psicosocial intralaboral:</p>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 20%;">Nivel de Riesgo</th>
                                    <th>Interpretación y Acciones Requeridas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <span class="badge" style="background-color: <?= getNivelColor('sin_riesgo') ?>; font-size: 0.95rem;">Sin Riesgo o Riesgo Despreciable</span>
                                    </td>
                                    <td>
                                        <strong>Ausencia de riesgo o riesgo tan bajo que no amerita desarrollar actividades de intervención.</strong>
                                        Las dimensiones y dominios que se encuentren bajo esta categoría serán objeto de acciones o programas de <strong>promoción</strong>.
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="badge" style="background-color: <?= getNivelColor('riesgo_bajo') ?>; font-size: 0.95rem;">Riesgo Bajo</span>
                                    </td>
                                    <td>
                                        <strong>No se espera que los factores psicosociales que obtengan puntuaciones de este nivel estén relacionados con síntomas o respuestas de estrés significativas.</strong>
                                        Las dimensiones y dominios que se encuentren bajo esta categoría serán objeto de acciones o programas de intervención, a fin de <strong>mantenerlos en los niveles de riesgo más bajos posibles</strong>.
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="badge" style="background-color: <?= getNivelColor('riesgo_medio') ?>; font-size: 0.95rem;">Riesgo Medio</span>
                                    </td>
                                    <td>
                                        <strong>Nivel de riesgo en el que se esperaría una respuesta de estrés moderada.</strong>
                                        Las dimensiones y dominios que se encuentren bajo esta categoría ameritan <strong>observación y acciones sistemáticas de intervención para prevenir efectos perjudiciales en la salud</strong>.
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="badge" style="background-color: <?= getNivelColor('riesgo_alto') ?>; font-size: 0.95rem;">Riesgo Alto</span>
                                    </td>
                                    <td>
                                        <strong>Nivel de riesgo que tiene una importante posibilidad de asociación con respuestas de estrés alto.</strong>
                                        Por tanto, las dimensiones y dominios que se encuentren bajo esta categoría <strong>requieren intervención en el marco de un sistema de vigilancia epidemiológica</strong>.
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="badge" style="background-color: <?= getNivelColor('riesgo_muy_alto') ?>; font-size: 0.95rem;">Riesgo Muy Alto</span>
                                    </td>
                                    <td>
                                        <strong>Nivel de riesgo con amplia posibilidad de asociarse a respuestas muy altas de estrés.</strong>
                                        Por consiguiente, las dimensiones y dominios que se encuentren bajo esta categoría <strong>requieren intervención inmediata en el marco de un sistema de vigilancia epidemiológica</strong>.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-warning mt-3 mb-0">
                        <h6 class="mb-2"><i class="fas fa-exclamation-triangle me-2"></i>Importante</h6>
                        <p class="mb-0 small">Estas definiciones aplican para todos los factores de riesgo psicosocial intralaboral (dominios y dimensiones). Los niveles de riesgo <strong>Alto</strong> y <strong>Muy Alto</strong> requieren implementación de un sistema de vigilancia epidemiológica según la normatividad colombiana vigente.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
