<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - PsyRisk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #2d7a4f 0%, #4a9d6f 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }
        .result-container {
            max-width: 1400px;
            margin: 0 auto;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 2rem;
        }
        .card-header {
            border-radius: 15px 15px 0 0 !important;
            background: linear-gradient(135deg, #2d7a4f 0%, #4a9d6f 100%);
            color: white;
            padding: 1.5rem;
        }
        .forma-badge {
            background: #28a745;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.9rem;
            display: inline-block;
            margin-left: 1rem;
        }
        .nivel-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.9rem;
        }
        .sin-riesgo {
            background: #d4edda;
            color: #155724;
        }
        .riesgo-bajo {
            background: #d1ecf1;
            color: #0c5460;
        }
        .riesgo-medio {
            background: #fff3cd;
            color: #856404;
        }
        .riesgo-alto {
            background: #dc3545;
            color: #ffffff;
            font-weight: bold;
        }
        .riesgo-muy-alto {
            background: #dc3545;
            color: #ffffff;
            font-weight: bold;
        }
        .muy-bajo {
            background: #d4edda;
            color: #155724;
        }
        .bajo {
            background: #d1ecf1;
            color: #0c5460;
        }
        .medio {
            background: #fff3cd;
            color: #856404;
        }
        .alto {
            background: #dc3545;
            color: #ffffff;
            font-weight: bold;
        }
        .muy-alto {
            background: #dc3545;
            color: #ffffff;
            font-weight: bold;
        }
        .score-box {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            border-left: 4px solid #28a745;
        }
        .score-box h5 {
            margin-bottom: 0.5rem;
            color: #28a745;
            font-size: 0.9rem;
            text-transform: uppercase;
            font-weight: 600;
        }
        .score-value {
            font-size: 2rem;
            font-weight: bold;
            color: #333;
        }
        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            background: #28a745 !important;
            border-color: #28a745 !important;
        }
        .card-header .btn-link {
            color: white !important;
            text-decoration: none;
        }
        .card-header .btn-link:hover {
            color: #f0f0f0 !important;
        }
        @media print {
            body {
                background: white;
            }
            .print-btn, .btn-back {
                display: none;
            }
        }
    </style>
</head>
<body>
    <button class="btn btn-danger print-btn" onclick="window.print()">
        <i class="fas fa-print me-2"></i>Imprimir Resultados
    </button>

    <div class="result-container">
        <!-- Información del Trabajador -->
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">
                    <i class="fas fa-user me-2"></i>Información del Trabajador
                    <span class="forma-badge">FORMA B - Auxiliares y Operarios</span>
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Nombre:</strong> <?= esc($worker['name']) ?></p>
                        <p><strong>Documento:</strong> <?= esc($worker['document']) ?></p>
                        <p><strong>Cargo:</strong> <?= esc($worker['position']) ?></p>
                        <p><strong>Área:</strong> <?= esc($worker['area']) ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Empresa:</strong> <?= esc($service['company_name']) ?></p>
                        <p><strong>Tipo de Cuestionario:</strong> Forma B (97 preguntas)</p>
                        <p><strong>Tipo de Posición:</strong> <?= esc($demographics['position_type'] ?? 'N/A') ?></p>
                        <p><strong>Fecha de Completado:</strong> <?= date('d/m/Y H:i', strtotime($worker['completed_at'])) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resultado Total General -->
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0"><i class="fas fa-chart-bar me-2"></i>RESULTADO TOTAL GENERAL</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="score-box">
                            <h5>Puntaje Transformado</h5>
                            <div class="score-value"><?= number_format($results['puntaje_total_general'], 1) ?></div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="score-box">
                            <h5>Nivel de Riesgo</h5>
                            <?php
                            $nivelRiesgo = $results['puntaje_total_general_nivel'] ?? 'N/A';
                            $nivelTexto = [
                                'sin_riesgo' => 'Sin Riesgo o Riesgo Despreciable',
                                'riesgo_bajo' => 'Riesgo Bajo',
                                'riesgo_medio' => 'Riesgo Medio',
                                'riesgo_alto' => 'Riesgo Alto',
                                'riesgo_muy_alto' => 'Riesgo Muy Alto'
                            ];
                            ?>
                            <span class="nivel-badge <?= str_replace('_', '-', $nivelRiesgo) ?>">
                                <?= $nivelTexto[$nivelRiesgo] ?? strtoupper(str_replace('_', ' ', $nivelRiesgo)) ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resultados Intralaborales FORMA B -->
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0"><i class="fas fa-building me-2"></i>FACTORES INTRALABORALES - FORMA B</h3>
            </div>
            <div class="card-body">
                <h5 class="mb-3">Dominios y Dimensiones – Factores de Riesgo Psicosocial Intralaboral (16 Dimensiones)</h5>

                <div class="accordion" id="accordionIntralaboral">
                    <!-- DOMINIO 1: Liderazgo y Relaciones Sociales en el Trabajo -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingDominio1">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDominio1" aria-expanded="true" aria-controls="collapseDominio1">
                                <div class="d-flex align-items-center w-100">
                                    <strong class="me-3">DOMINIO 1: Liderazgo y Relaciones Sociales en el Trabajo</strong>
                                    <span class="me-3">Puntaje: <strong><?= number_format($results['dom_liderazgo_puntaje'], 1) ?></strong></span>
                                    <span class="nivel-badge <?= str_replace('_', '-', $results['dom_liderazgo_nivel']) ?>">
                                        <?= strtoupper(str_replace('_', ' ', $results['dom_liderazgo_nivel'])) ?>
                                    </span>
                                </div>
                            </button>
                        </h2>
                        <div id="collapseDominio1" class="accordion-collapse collapse show" aria-labelledby="headingDominio1" data-bs-parent="#accordionIntralaboral">
                            <div class="accordion-body">
                                <h6 class="mb-3 text-muted">Dimensiones</h6>
                                <div class="row">
                                    <!-- Características del liderazgo -->
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="score-box">
                                            <h6>Características del liderazgo</h6>
                                            <div class="score-value"><?= number_format($results['dim_caracteristicas_liderazgo_puntaje'], 1) ?></div>
                                            <span class="nivel-badge <?= str_replace('_', '-', $results['dim_caracteristicas_liderazgo_nivel']) ?>">
                                                <?= strtoupper(str_replace('_', ' ', $results['dim_caracteristicas_liderazgo_nivel'])) ?>
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Relaciones sociales en el trabajo -->
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="score-box">
                                            <h6>Relaciones sociales en el trabajo</h6>
                                            <div class="score-value"><?= number_format($results['dim_relaciones_sociales_puntaje'], 1) ?></div>
                                            <span class="nivel-badge <?= str_replace('_', '-', $results['dim_relaciones_sociales_nivel']) ?>">
                                                <?= strtoupper(str_replace('_', ' ', $results['dim_relaciones_sociales_nivel'])) ?>
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Retroalimentación del desempeño -->
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="score-box">
                                            <h6>Retroalimentación del desempeño</h6>
                                            <div class="score-value"><?= number_format($results['dim_retroalimentacion_puntaje'], 1) ?></div>
                                            <span class="nivel-badge <?= str_replace('_', '-', $results['dim_retroalimentacion_nivel']) ?>">
                                                <?= strtoupper(str_replace('_', ' ', $results['dim_retroalimentacion_nivel'])) ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="alert alert-info mt-3">
                                    <i class="fas fa-info-circle me-2"></i><strong>Nota:</strong> La Forma B NO incluye la dimensión "Relación con los colaboradores" ya que está diseñada para auxiliares y operarios sin personal a cargo.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- DOMINIO 2: Control sobre el Trabajo -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingDominio2">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDominio2" aria-expanded="false" aria-controls="collapseDominio2">
                                <div class="d-flex align-items-center w-100">
                                    <strong class="me-3">DOMINIO 2: Control sobre el Trabajo</strong>
                                    <span class="me-3">Puntaje: <strong><?= number_format($results['dom_control_puntaje'], 1) ?></strong></span>
                                    <span class="nivel-badge <?= str_replace('_', '-', $results['dom_control_nivel']) ?>">
                                        <?= strtoupper(str_replace('_', ' ', $results['dom_control_nivel'])) ?>
                                    </span>
                                </div>
                            </button>
                        </h2>
                        <div id="collapseDominio2" class="accordion-collapse collapse" aria-labelledby="headingDominio2" data-bs-parent="#accordionIntralaboral">
                            <div class="accordion-body">
                                <h6 class="mb-3 text-muted">Dimensiones</h6>
                                <div class="row">
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="score-box">
                                            <h6>Claridad de rol</h6>
                                            <div class="score-value"><?= number_format($results['dim_claridad_rol_puntaje'], 1) ?></div>
                                            <span class="nivel-badge <?= str_replace('_', '-', $results['dim_claridad_rol_nivel']) ?>">
                                                <?= strtoupper(str_replace('_', ' ', $results['dim_claridad_rol_nivel'])) ?>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="score-box">
                                            <h6>Capacitación</h6>
                                            <div class="score-value"><?= number_format($results['dim_capacitacion_puntaje'], 1) ?></div>
                                            <span class="nivel-badge <?= str_replace('_', '-', $results['dim_capacitacion_nivel']) ?>">
                                                <?= strtoupper(str_replace('_', ' ', $results['dim_capacitacion_nivel'])) ?>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="score-box">
                                            <h6>Participación y manejo del cambio</h6>
                                            <div class="score-value"><?= number_format($results['dim_participacion_manejo_cambio_puntaje'], 1) ?></div>
                                            <span class="nivel-badge <?= str_replace('_', '-', $results['dim_participacion_manejo_cambio_nivel']) ?>">
                                                <?= strtoupper(str_replace('_', ' ', $results['dim_participacion_manejo_cambio_nivel'])) ?>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-lg-6 mb-3">
                                        <div class="score-box">
                                            <h6>Oportunidades para el uso y desarrollo de habilidades y conocimientos</h6>
                                            <div class="score-value"><?= number_format($results['dim_oportunidades_desarrollo_puntaje'], 1) ?></div>
                                            <span class="nivel-badge <?= str_replace('_', '-', $results['dim_oportunidades_desarrollo_nivel']) ?>">
                                                <?= strtoupper(str_replace('_', ' ', $results['dim_oportunidades_desarrollo_nivel'])) ?>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-lg-6 mb-3">
                                        <div class="score-box">
                                            <h6>Control y autonomía sobre el trabajo</h6>
                                            <div class="score-value"><?= number_format($results['dim_control_autonomia_puntaje'], 1) ?></div>
                                            <span class="nivel-badge <?= str_replace('_', '-', $results['dim_control_autonomia_nivel']) ?>">
                                                <?= strtoupper(str_replace('_', ' ', $results['dim_control_autonomia_nivel'])) ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- DOMINIO 3: Demandas del Trabajo -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingDominio3">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDominio3" aria-expanded="false" aria-controls="collapseDominio3">
                                <div class="d-flex align-items-center w-100">
                                    <strong class="me-3">DOMINIO 3: Demandas del Trabajo</strong>
                                    <span class="me-3">Puntaje: <strong><?= number_format($results['dom_demandas_puntaje'], 1) ?></strong></span>
                                    <span class="nivel-badge <?= str_replace('_', '-', $results['dom_demandas_nivel']) ?>">
                                        <?= strtoupper(str_replace('_', ' ', $results['dom_demandas_nivel'])) ?>
                                    </span>
                                </div>
                            </button>
                        </h2>
                        <div id="collapseDominio3" class="accordion-collapse collapse" aria-labelledby="headingDominio3" data-bs-parent="#accordionIntralaboral">
                            <div class="accordion-body">
                                <h6 class="mb-3 text-muted">Dimensiones</h6>
                                <div class="row">
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="score-box">
                                            <h6>Demandas ambientales y de esfuerzo físico</h6>
                                            <div class="score-value"><?= number_format($results['dim_demandas_ambientales_puntaje'], 1) ?></div>
                                            <span class="nivel-badge <?= str_replace('_', '-', $results['dim_demandas_ambientales_nivel']) ?>">
                                                <?= strtoupper(str_replace('_', ' ', $results['dim_demandas_ambientales_nivel'])) ?>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="score-box">
                                            <h6>Demandas emocionales</h6>
                                            <div class="score-value"><?= number_format($results['dim_demandas_emocionales_puntaje'], 1) ?></div>
                                            <span class="nivel-badge <?= str_replace('_', '-', $results['dim_demandas_emocionales_nivel']) ?>">
                                                <?= strtoupper(str_replace('_', ' ', $results['dim_demandas_emocionales_nivel'])) ?>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="score-box">
                                            <h6>Demandas cuantitativas</h6>
                                            <div class="score-value"><?= number_format($results['dim_demandas_cuantitativas_puntaje'], 1) ?></div>
                                            <span class="nivel-badge <?= str_replace('_', '-', $results['dim_demandas_cuantitativas_nivel']) ?>">
                                                <?= strtoupper(str_replace('_', ' ', $results['dim_demandas_cuantitativas_nivel'])) ?>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="score-box">
                                            <h6>Influencia del trabajo sobre el entorno extralaboral</h6>
                                            <div class="score-value"><?= number_format($results['dim_influencia_trabajo_entorno_extralaboral_puntaje'], 1) ?></div>
                                            <span class="nivel-badge <?= str_replace('_', '-', $results['dim_influencia_trabajo_entorno_extralaboral_nivel']) ?>">
                                                <?= strtoupper(str_replace('_', ' ', $results['dim_influencia_trabajo_entorno_extralaboral_nivel'])) ?>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="score-box">
                                            <h6>Demandas de carga mental</h6>
                                            <div class="score-value"><?= number_format($results['dim_demandas_carga_mental_puntaje'], 1) ?></div>
                                            <span class="nivel-badge <?= str_replace('_', '-', $results['dim_demandas_carga_mental_nivel']) ?>">
                                                <?= strtoupper(str_replace('_', ' ', $results['dim_demandas_carga_mental_nivel'])) ?>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="score-box">
                                            <h6>Demandas de la jornada de trabajo</h6>
                                            <div class="score-value"><?= number_format($results['dim_demandas_jornada_trabajo_puntaje'], 1) ?></div>
                                            <span class="nivel-badge <?= str_replace('_', '-', $results['dim_demandas_jornada_trabajo_nivel']) ?>">
                                                <?= strtoupper(str_replace('_', ' ', $results['dim_demandas_jornada_trabajo_nivel'])) ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="alert alert-warning mt-3">
                                    <i class="fas fa-exclamation-triangle me-2"></i><strong>Nota:</strong> La Forma B tiene menos dimensiones de demandas comparado con la Forma A (6 vs 8 dimensiones). No incluye "Exigencias de responsabilidad del cargo" ni "Consistencia del rol".
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- DOMINIO 4: Recompensas -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingDominio4">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDominio4" aria-expanded="false" aria-controls="collapseDominio4">
                                <div class="d-flex align-items-center w-100">
                                    <strong class="me-3">DOMINIO 4: Recompensas</strong>
                                    <span class="me-3">Puntaje: <strong><?= number_format($results['dom_recompensas_puntaje'], 1) ?></strong></span>
                                    <span class="nivel-badge <?= str_replace('_', '-', $results['dom_recompensas_nivel']) ?>">
                                        <?= strtoupper(str_replace('_', ' ', $results['dom_recompensas_nivel'])) ?>
                                    </span>
                                </div>
                            </button>
                        </h2>
                        <div id="collapseDominio4" class="accordion-collapse collapse" aria-labelledby="headingDominio4" data-bs-parent="#accordionIntralaboral">
                            <div class="accordion-body">
                                <h6 class="mb-3 text-muted">Dimensiones</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="score-box">
                                            <h6>Recompensas derivadas de la pertenencia a la organización y del trabajo que se realiza</h6>
                                            <div class="score-value"><?= number_format($results['dim_recompensas_pertenencia_puntaje'], 1) ?></div>
                                            <span class="nivel-badge <?= str_replace('_', '-', $results['dim_recompensas_pertenencia_nivel']) ?>">
                                                <?= strtoupper(str_replace('_', ' ', $results['dim_recompensas_pertenencia_nivel'])) ?>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <div class="score-box">
                                            <h6>Reconocimiento y compensación</h6>
                                            <div class="score-value"><?= number_format($results['dim_reconocimiento_compensacion_puntaje'], 1) ?></div>
                                            <span class="nivel-badge <?= str_replace('_', '-', $results['dim_reconocimiento_compensacion_nivel']) ?>">
                                                <?= strtoupper(str_replace('_', ' ', $results['dim_reconocimiento_compensacion_nivel'])) ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <h5 class="mt-4 mb-3">Total Intralaboral</h5>
                <div class="row">
                    <div class="col-md-4">
                        <div class="score-box">
                            <h5>Puntaje Transformado</h5>
                            <div class="score-value"><?= number_format($results['intralaboral_total_puntaje'], 1) ?></div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="score-box">
                            <h5>Nivel de Riesgo</h5>
                            <span class="nivel-badge <?= str_replace('_', '-', $results['intralaboral_total_nivel']) ?>">
                                <?= strtoupper(str_replace('_', ' ', $results['intralaboral_total_nivel'])) ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resultados Extralaborales -->
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0"><i class="fas fa-home me-2"></i>FACTORES EXTRALABORALES</h3>
            </div>
            <div class="card-body">
                <h5 class="mb-3">Dimensiones – Factores de Riesgo Psicosocial Extralaboral</h5>

                <div class="card mb-3">
                    <div class="card-header p-0">
                        <button class="btn btn-link w-100 text-start text-decoration-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExtralaboral" aria-expanded="false" aria-controls="collapseExtralaboral">
                            <div class="d-flex align-items-center w-100 p-3">
                                <strong class="me-3">Factores de Riesgo Psicosocial Extralaboral</strong>
                                <span class="me-3">Puntaje: <strong><?= number_format($results['extralaboral_total_puntaje'], 1) ?></strong></span>
                                <span class="nivel-badge <?= str_replace('_', '-', $results['extralaboral_total_nivel']) ?>">
                                    <?= strtoupper(str_replace('_', ' ', $results['extralaboral_total_nivel'])) ?>
                                </span>
                            </div>
                        </button>
                    </div>
                    <div id="collapseExtralaboral" class="collapse">
                        <div class="card-body">
                                <h6 class="mb-3 text-muted">Dimensiones</h6>
                                <div class="row">
                                    <div class="col-md-6 col-lg-3 mb-3">
                                        <div class="score-box">
                                            <h6>Tiempo fuera del trabajo</h6>
                                            <div class="score-value"><?= number_format($results['extralaboral_tiempo_fuera_puntaje'], 1) ?></div>
                                            <span class="nivel-badge <?= str_replace('_', '-', $results['extralaboral_tiempo_fuera_nivel']) ?>">
                                                <?= strtoupper(str_replace('_', ' ', $results['extralaboral_tiempo_fuera_nivel'])) ?>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-lg-3 mb-3">
                                        <div class="score-box">
                                            <h6>Relaciones familiares</h6>
                                            <div class="score-value"><?= number_format($results['extralaboral_relaciones_familiares_puntaje'], 1) ?></div>
                                            <span class="nivel-badge <?= str_replace('_', '-', $results['extralaboral_relaciones_familiares_nivel']) ?>">
                                                <?= strtoupper(str_replace('_', ' ', $results['extralaboral_relaciones_familiares_nivel'])) ?>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-lg-3 mb-3">
                                        <div class="score-box">
                                            <h6>Comunicación y relaciones interpersonales</h6>
                                            <div class="score-value"><?= number_format($results['extralaboral_comunicacion_puntaje'], 1) ?></div>
                                            <span class="nivel-badge <?= str_replace('_', '-', $results['extralaboral_comunicacion_nivel']) ?>">
                                                <?= strtoupper(str_replace('_', ' ', $results['extralaboral_comunicacion_nivel'])) ?>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-lg-3 mb-3">
                                        <div class="score-box">
                                            <h6>Situación económica del grupo familiar</h6>
                                            <div class="score-value"><?= number_format($results['extralaboral_situacion_economica_puntaje'], 1) ?></div>
                                            <span class="nivel-badge <?= str_replace('_', '-', $results['extralaboral_situacion_economica_nivel']) ?>">
                                                <?= strtoupper(str_replace('_', ' ', $results['extralaboral_situacion_economica_nivel'])) ?>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-lg-3 mb-3">
                                        <div class="score-box">
                                            <h6>Características de la vivienda y de su entorno</h6>
                                            <div class="score-value"><?= number_format($results['extralaboral_caracteristicas_vivienda_puntaje'], 1) ?></div>
                                            <span class="nivel-badge <?= str_replace('_', '-', $results['extralaboral_caracteristicas_vivienda_nivel']) ?>">
                                                <?= strtoupper(str_replace('_', ' ', $results['extralaboral_caracteristicas_vivienda_nivel'])) ?>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-lg-3 mb-3">
                                        <div class="score-box">
                                            <h6>Influencia del entorno extralaboral sobre el trabajo</h6>
                                            <div class="score-value"><?= number_format($results['extralaboral_influencia_entorno_puntaje'], 1) ?></div>
                                            <span class="nivel-badge <?= str_replace('_', '-', $results['extralaboral_influencia_entorno_nivel']) ?>">
                                                <?= strtoupper(str_replace('_', ' ', $results['extralaboral_influencia_entorno_nivel'])) ?>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-lg-3 mb-3">
                                        <div class="score-box">
                                            <h6>Desplazamiento vivienda – trabajo – vivienda</h6>
                                            <div class="score-value"><?= number_format($results['extralaboral_desplazamiento_puntaje'], 1) ?></div>
                                            <span class="nivel-badge <?= str_replace('_', '-', $results['extralaboral_desplazamiento_nivel']) ?>">
                                                <?= strtoupper(str_replace('_', ' ', $results['extralaboral_desplazamiento_nivel'])) ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resultados de Estrés -->
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0"><i class="fas fa-heartbeat me-2"></i>NIVEL DE ESTRÉS</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="score-box">
                            <h5>Puntaje Transformado</h5>
                            <div class="score-value"><?= number_format($results['estres_total_puntaje'], 1) ?></div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="score-box">
                            <h5>Nivel de Estrés</h5>
                            <span class="nivel-badge <?= str_replace('_', '-', $results['estres_total_nivel']) ?>">
                                <?= strtoupper(str_replace('_', ' ', $results['estres_total_nivel'])) ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detalle de Cálculos -->
        <div class="card">
            <div class="card-header" style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#calculosDetalle">
                <h3 class="mb-0">
                    <i class="fas fa-calculator me-2"></i>VER DETALLE DE CÁLCULOS Y FÓRMULAS
                    <i class="fas fa-chevron-down float-end"></i>
                </h3>
            </div>
            <div id="calculosDetalle" class="collapse">
                <div class="card-body">
                    <div class="alert alert-info">
                        <strong><i class="fas fa-info-circle me-2"></i>Información:</strong>
                        Los cálculos se realizan según la metodología oficial de la Batería de Riesgo Psicosocial del Ministerio de la Protección Social de Colombia - <strong>Forma B (Auxiliares y Operarios)</strong>.
                    </div>

                    <h5 class="mt-4">Especificaciones Forma B</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tr class="table-danger">
                                <td><strong>Total de Preguntas:</strong></td>
                                <td>97 preguntas</td>
                            </tr>
                            <tr>
                                <td><strong>Total de Dimensiones:</strong></td>
                                <td>16 dimensiones</td>
                            </tr>
                            <tr>
                                <td><strong>Dimensiones excluidas:</strong></td>
                                <td>
                                    <ul class="mb-0">
                                        <li>"Relación con los colaboradores" (solo Forma A)</li>
                                        <li>"Exigencias de responsabilidad del cargo" (solo Forma A)</li>
                                        <li>"Consistencia del rol" (solo Forma A)</li>
                                        <li>Algunas preguntas condicionales para jefes</li>
                                    </ul>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Baremos aplicados:</strong></td>
                                <td>Baremos específicos Forma B para auxiliares y operarios</td>
                            </tr>
                        </table>
                    </div>

                    <h5 class="mt-4">Cálculo del Puntaje Total General</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tr>
                                <td><strong>Puntaje Bruto Intralaboral:</strong></td>
                                <td><?= $results['intralaboral_total_puntaje'] ?? 'N/A' ?></td>
                            </tr>
                            <tr>
                                <td><strong>Puntaje Bruto Extralaboral:</strong></td>
                                <td><?= $results['extralaboral_total_puntaje'] ?? 'N/A' ?></td>
                            </tr>
                            <tr>
                                <td><strong>Suma de Puntajes Brutos:</strong></td>
                                <td><?= number_format(($results['intralaboral_total_puntaje'] ?? 0) + ($results['extralaboral_total_puntaje'] ?? 0), 2) ?></td>
                            </tr>
                            <tr class="table-primary">
                                <td><strong>Fórmula de Transformación:</strong></td>
                                <td>(Suma Puntajes Brutos / 388) × 100</td>
                            </tr>
                            <tr class="table-success">
                                <td><strong>Puntaje Total General Transformado:</strong></td>
                                <td><strong><?= number_format($results['puntaje_total_general'], 1) ?></strong></td>
                            </tr>
                        </table>
                    </div>

                    <h5 class="mt-4">Baremos Utilizados</h5>
                    <p><strong>Tipo de Cuestionario:</strong> Forma B (Auxiliares y Operarios - 97 preguntas)</p>
                    <p><strong>Tipo de Posición:</strong> <?= esc($demographics['position_type'] ?? 'N/A') ?></p>
                    <p class="text-muted mt-3"><small><i class="fas fa-info-circle me-1"></i>Los baremos (tablas de interpretación) son diferentes a los de la Forma A, específicamente diseñados para auxiliares y operarios.</small></p>
                </div>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="text-center mb-4 mt-4">
            <a href="<?= base_url('workers/export-responses/' . $worker['id']) ?>" class="btn btn-lg btn-success me-3" target="_blank">
                <i class="fas fa-file-excel me-2"></i>Descargar Excel con Respuestas
            </a>
            <a href="<?= base_url('workers/service/' . $service['id']) ?>" class="btn btn-lg btn-secondary btn-back">
                <i class="fas fa-arrow-left me-2"></i>Volver a la Lista de Trabajadores
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
