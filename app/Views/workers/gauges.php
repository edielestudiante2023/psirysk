<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gráficos de Riesgo - <?= esc($worker['name']) ?> - PsyRisk</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Chart.js + plugin de datalabels -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>

    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }

        .container-main {
            max-width: 1400px;
            margin: 0 auto;
        }

        .header-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .gauge-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 1.5rem;
            color: #667eea;
            border-bottom: 3px solid #667eea;
            padding-bottom: 0.5rem;
        }

        /* Gauge container */
        .gauge-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .gauge-title {
            text-align: center;
            font-weight: bold;
            font-size: 1.1rem;
            margin-bottom: 1rem;
            color: #333;
        }

        .gauge-wrapper {
            position: relative;
            height: 250px;
            margin-bottom: 1rem;
        }

        /* Aguja HTML */
        .needle {
            position: absolute;
            left: 50%;
            top: 50%;
            width: 2px;
            height: 45%;
            background: #000;
            transform-origin: bottom center;
            transform: rotate(0deg);
            z-index: 10;
        }

        .needle::after {
            content: "";
            position: absolute;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #000;
            bottom: -6px;
            left: 50%;
            transform: translateX(-50%);
        }

        .gauge-score {
            text-align: center;
            margin-top: -50px;
            margin-bottom: 1rem;
        }

        .gauge-score-value {
            font-size: 1.8rem;
            font-weight: bold;
            background: white;
            padding: 0.5rem 1.5rem;
            border-radius: 8px;
            display: inline-block;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .gauge-label {
            background: #333;
            color: white;
            padding: 0.3rem 1rem;
            border-radius: 5px;
            font-weight: bold;
            font-size: 0.85rem;
            margin-top: 0.5rem;
            display: inline-block;
        }

        /* Baremos grid */
        .baremos-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .baremo-card {
            border: 2px solid;
            border-radius: 8px;
            padding: 0.5rem;
            text-align: center;
            font-size: 0.75rem;
        }

        .baremo-card.sin-riesgo { border-color: #28a745; background-color: #d4edda; }
        .baremo-card.riesgo-bajo { border-color: #90ee90; background-color: #e8f8e8; }
        .baremo-card.riesgo-medio { border-color: #ffc107; background-color: #fff9e6; }
        .baremo-card.riesgo-alto { border-color: #dc3545; background-color: #f8d7da; }
        .baremo-card.riesgo-muy-alto { border-color: #8b0000; background-color: #ffe0e0; }

        .baremo-header { font-weight: bold; margin-bottom: 0.25rem; font-size: 0.7rem; }
        .baremo-range { font-weight: bold; font-size: 0.8rem; }

        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }

        @media print {
            .print-btn, .no-print { display: none !important; }
            body { background: white; }
        }
    </style>
</head>
<body>
    <div class="container-main">
        <!-- Botón de imprimir -->
        <button onclick="window.print()" class="btn btn-primary print-btn">
            <i class="fas fa-print me-2"></i>Imprimir
        </button>

        <!-- Header -->
        <div class="header-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-2">
                        <i class="fas fa-chart-pie text-primary me-2"></i>
                        Gráficos de Riesgo Psicosocial
                    </h1>
                    <h4 class="text-muted mb-0"><?= esc($worker['name']) ?></h4>
                    <p class="text-muted mb-0">
                        <i class="fas fa-id-card me-2"></i><?= esc($worker['document']) ?>
                        <span class="mx-2">|</span>
                        <i class="fas fa-briefcase me-2"></i><?= esc($worker['position']) ?>
                    </p>
                </div>
                <div class="text-end">
                    <span class="badge bg-<?= $worker['intralaboral_type'] === 'A' ? 'primary' : 'success' ?> fs-5">
                        Forma <?= $worker['intralaboral_type'] ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- SECCIÓN 1: TOTALES PRINCIPALES -->
        <div class="gauge-section">
            <h2 class="section-title">
                <i class="fas fa-chart-line me-2"></i>Puntajes Totales
            </h2>

            <div class="row">
                <!-- Total Intralaboral -->
                <div class="col-md-4">
                    <div class="gauge-card">
                        <div class="gauge-title">Total Intralaboral</div>
                        <div class="gauge-wrapper">
                            <canvas id="gaugeIntralaboral"></canvas>
                            <div class="needle" id="needleIntralaboral"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value"><?= number_format($results['intralaboral_total_puntaje'], 1) ?></div>
                            <div class="gauge-label"><?= strtoupper($results['intralaboral_total_nivel']) ?></div>
                        </div>
                        <div class="baremos-grid" id="baremosIntralaboral"></div>
                    </div>
                </div>

                <!-- Total Extralaboral -->
                <div class="col-md-4">
                    <div class="gauge-card">
                        <div class="gauge-title">Total Extralaboral</div>
                        <div class="gauge-wrapper">
                            <canvas id="gaugeExtralaboral"></canvas>
                            <div class="needle" id="needleExtralaboral"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value"><?= number_format($results['extralaboral_total_puntaje'], 1) ?></div>
                            <div class="gauge-label"><?= strtoupper($results['extralaboral_total_nivel']) ?></div>
                        </div>
                        <div class="baremos-grid" id="baremosExtralaboral"></div>
                    </div>
                </div>

                <!-- Total Estrés -->
                <div class="col-md-4">
                    <div class="gauge-card">
                        <div class="gauge-title">Total Estrés</div>
                        <div class="gauge-wrapper">
                            <canvas id="gaugeEstres"></canvas>
                            <div class="needle" id="needleEstres"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value"><?= number_format($results['estres_total_puntaje'], 1) ?></div>
                            <div class="gauge-label"><?= strtoupper($results['estres_total_nivel']) ?></div>
                        </div>
                        <div class="baremos-grid" id="baremosEstres"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECCIÓN 2: DOMINIOS INTRALABORALES -->
        <div class="gauge-section">
            <h2 class="section-title">
                <i class="fas fa-cubes me-2"></i>Dominios Intralaborales
            </h2>

            <div class="row">
                <!-- Dom. Liderazgo -->
                <div class="col-md-3">
                    <div class="gauge-card">
                        <div class="gauge-title">Liderazgo y Relaciones</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDomLiderazgo"></canvas>
                            <div class="needle" id="needleDomLiderazgo"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($results['dom_liderazgo_puntaje'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($results['dom_liderazgo_nivel']) ?></div>
                        </div>
                    </div>
                </div>

                <!-- Dom. Control -->
                <div class="col-md-3">
                    <div class="gauge-card">
                        <div class="gauge-title">Control sobre el Trabajo</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDomControl"></canvas>
                            <div class="needle" id="needleDomControl"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($results['dom_control_puntaje'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($results['dom_control_nivel']) ?></div>
                        </div>
                    </div>
                </div>

                <!-- Dom. Demandas -->
                <div class="col-md-3">
                    <div class="gauge-card">
                        <div class="gauge-title">Demandas del Trabajo</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDomDemandas"></canvas>
                            <div class="needle" id="needleDomDemandas"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($results['dom_demandas_puntaje'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($results['dom_demandas_nivel']) ?></div>
                        </div>
                    </div>
                </div>

                <!-- Dom. Recompensas -->
                <div class="col-md-3">
                    <div class="gauge-card">
                        <div class="gauge-title">Recompensas</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDomRecompensas"></canvas>
                            <div class="needle" id="needleDomRecompensas"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($results['dom_recompensas_puntaje'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($results['dom_recompensas_nivel']) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECCIÓN 3: DIMENSIONES INTRALABORALES - LIDERAZGO -->
        <div class="gauge-section">
            <h2 class="section-title">
                <i class="fas fa-puzzle-piece me-2"></i>Dimensiones - Dominio Liderazgo
            </h2>

            <div class="row">
                <!-- Características del liderazgo -->
                <div class="col-md-<?= $worker['intralaboral_type'] === 'A' ? '3' : '4' ?>">
                    <div class="gauge-card">
                        <div class="gauge-title">Características del Liderazgo</div>
                        <div class="gauge-wrapper" style="height: 180px;">
                            <canvas id="gaugeDimCaractLiderazgo"></canvas>
                            <div class="needle" id="needleDimCaractLiderazgo"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.2rem;"><?= number_format($results['dim_caracteristicas_liderazgo_puntaje'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.7rem;"><?= strtoupper($results['dim_caracteristicas_liderazgo_nivel']) ?></div>
                        </div>
                    </div>
                </div>

                <!-- Relaciones sociales en el trabajo -->
                <div class="col-md-<?= $worker['intralaboral_type'] === 'A' ? '3' : '4' ?>">
                    <div class="gauge-card">
                        <div class="gauge-title">Relaciones Sociales</div>
                        <div class="gauge-wrapper" style="height: 180px;">
                            <canvas id="gaugeDimRelacionesSociales"></canvas>
                            <div class="needle" id="needleDimRelacionesSociales"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.2rem;"><?= number_format($results['dim_relaciones_sociales_puntaje'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.7rem;"><?= strtoupper($results['dim_relaciones_sociales_nivel']) ?></div>
                        </div>
                    </div>
                </div>

                <!-- Retroalimentación del desempeño -->
                <div class="col-md-<?= $worker['intralaboral_type'] === 'A' ? '3' : '4' ?>">
                    <div class="gauge-card">
                        <div class="gauge-title">Retroalimentación</div>
                        <div class="gauge-wrapper" style="height: 180px;">
                            <canvas id="gaugeDimRetroalimentacion"></canvas>
                            <div class="needle" id="needleDimRetroalimentacion"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.2rem;"><?= number_format($results['dim_retroalimentacion_puntaje'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.7rem;"><?= strtoupper($results['dim_retroalimentacion_nivel']) ?></div>
                        </div>
                    </div>
                </div>

                <!-- Relación con colaboradores (solo Forma A) -->
                <?php if ($worker['intralaboral_type'] === 'A'): ?>
                <div class="col-md-3">
                    <div class="gauge-card">
                        <div class="gauge-title">Relación con Colaboradores</div>
                        <div class="gauge-wrapper" style="height: 180px;">
                            <canvas id="gaugeDimRelacionColaboradores"></canvas>
                            <div class="needle" id="needleDimRelacionColaboradores"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.2rem;"><?= number_format($results['dim_relacion_colaboradores_puntaje'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.7rem;"><?= strtoupper($results['dim_relacion_colaboradores_nivel']) ?></div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- SECCIÓN 4: DIMENSIONES INTRALABORALES - CONTROL -->
        <div class="gauge-section">
            <h2 class="section-title">
                <i class="fas fa-puzzle-piece me-2"></i>Dimensiones - Dominio Control
            </h2>

            <div class="row">
                <!-- Claridad de rol -->
                <div class="col-md-<?= $worker['intralaboral_type'] === 'A' ? '2' : '3' ?>">
                    <div class="gauge-card">
                        <div class="gauge-title">Claridad de Rol</div>
                        <div class="gauge-wrapper" style="height: 180px;">
                            <canvas id="gaugeDimClaridadRol"></canvas>
                            <div class="needle" id="needleDimClaridadRol"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.2rem;"><?= number_format($results['dim_claridad_rol_puntaje'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.7rem;"><?= strtoupper($results['dim_claridad_rol_nivel']) ?></div>
                        </div>
                    </div>
                </div>

                <!-- Capacitación -->
                <div class="col-md-<?= $worker['intralaboral_type'] === 'A' ? '2' : '3' ?>">
                    <div class="gauge-card">
                        <div class="gauge-title">Capacitación</div>
                        <div class="gauge-wrapper" style="height: 180px;">
                            <canvas id="gaugeDimCapacitacion"></canvas>
                            <div class="needle" id="needleDimCapacitacion"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.2rem;"><?= number_format($results['dim_capacitacion_puntaje'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.7rem;"><?= strtoupper($results['dim_capacitacion_nivel']) ?></div>
                        </div>
                    </div>
                </div>

                <!-- Participación y manejo del cambio -->
                <div class="col-md-<?= $worker['intralaboral_type'] === 'A' ? '2' : '3' ?>">
                    <div class="gauge-card">
                        <div class="gauge-title">Participación y Cambio</div>
                        <div class="gauge-wrapper" style="height: 180px;">
                            <canvas id="gaugeDimParticipacion"></canvas>
                            <div class="needle" id="needleDimParticipacion"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.2rem;"><?= number_format($results['dim_participacion_puntaje'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.7rem;"><?= strtoupper($results['dim_participacion_nivel']) ?></div>
                        </div>
                    </div>
                </div>

                <!-- Oportunidades de desarrollo -->
                <div class="col-md-<?= $worker['intralaboral_type'] === 'A' ? '2' : '3' ?>">
                    <div class="gauge-card">
                        <div class="gauge-title">Oportunidades de Desarrollo</div>
                        <div class="gauge-wrapper" style="height: 180px;">
                            <canvas id="gaugeDimOportunidades"></canvas>
                            <div class="needle" id="needleDimOportunidades"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.2rem;"><?= number_format($results['dim_oportunidades_desarrollo_puntaje'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.7rem;"><?= strtoupper($results['dim_oportunidades_desarrollo_nivel']) ?></div>
                        </div>
                    </div>
                </div>

                <!-- Control y autonomía sobre el trabajo -->
                <div class="col-md-<?= $worker['intralaboral_type'] === 'A' ? '2' : '4' ?>">
                    <div class="gauge-card">
                        <div class="gauge-title">Control y Autonomía</div>
                        <div class="gauge-wrapper" style="height: 180px;">
                            <canvas id="gaugeDimControlAutonomia"></canvas>
                            <div class="needle" id="needleDimControlAutonomia"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.2rem;"><?= number_format($results['dim_control_autonomia_puntaje'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.7rem;"><?= strtoupper($results['dim_control_autonomia_nivel']) ?></div>
                        </div>
                    </div>
                </div>

                <!-- Consistencia del rol (solo Forma A) -->
                <?php if ($worker['intralaboral_type'] === 'A'): ?>
                <div class="col-md-2">
                    <div class="gauge-card">
                        <div class="gauge-title">Consistencia del Rol</div>
                        <div class="gauge-wrapper" style="height: 180px;">
                            <canvas id="gaugeDimConsistenciaRol"></canvas>
                            <div class="needle" id="needleDimConsistenciaRol"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.2rem;"><?= number_format($results['dim_consistencia_rol_puntaje'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.7rem;"><?= strtoupper($results['dim_consistencia_rol_nivel']) ?></div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- SECCIÓN 5: DIMENSIONES INTRALABORALES - DEMANDAS -->
        <div class="gauge-section">
            <h2 class="section-title">
                <i class="fas fa-puzzle-piece me-2"></i>Dimensiones - Dominio Demandas
            </h2>

            <div class="row">
                <!-- Demandas ambientales y de esfuerzo físico -->
                <div class="col-md-<?= $worker['intralaboral_type'] === 'A' ? '2' : '3' ?>">
                    <div class="gauge-card">
                        <div class="gauge-title">Demandas Ambientales</div>
                        <div class="gauge-wrapper" style="height: 180px;">
                            <canvas id="gaugeDimDemandasAmbientales"></canvas>
                            <div class="needle" id="needleDimDemandasAmbientales"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.2rem;"><?= number_format($results['dim_demandas_ambientales_puntaje'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.7rem;"><?= strtoupper($results['dim_demandas_ambientales_nivel']) ?></div>
                        </div>
                    </div>
                </div>

                <!-- Demandas emocionales -->
                <div class="col-md-<?= $worker['intralaboral_type'] === 'A' ? '2' : '3' ?>">
                    <div class="gauge-card">
                        <div class="gauge-title">Demandas Emocionales</div>
                        <div class="gauge-wrapper" style="height: 180px;">
                            <canvas id="gaugeDimDemandasEmocionales"></canvas>
                            <div class="needle" id="needleDimDemandasEmocionales"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.2rem;"><?= number_format($results['dim_demandas_emocionales_puntaje'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.7rem;"><?= strtoupper($results['dim_demandas_emocionales_nivel']) ?></div>
                        </div>
                    </div>
                </div>

                <!-- Demandas cuantitativas -->
                <div class="col-md-<?= $worker['intralaboral_type'] === 'A' ? '2' : '3' ?>">
                    <div class="gauge-card">
                        <div class="gauge-title">Demandas Cuantitativas</div>
                        <div class="gauge-wrapper" style="height: 180px;">
                            <canvas id="gaugeDimDemandasCuantitativas"></canvas>
                            <div class="needle" id="needleDimDemandasCuantitativas"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.2rem;"><?= number_format($results['dim_demandas_cuantitativas_puntaje'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.7rem;"><?= strtoupper($results['dim_demandas_cuantitativas_nivel']) ?></div>
                        </div>
                    </div>
                </div>

                <!-- Influencia del ambiente laboral sobre el extralaboral -->
                <div class="col-md-<?= $worker['intralaboral_type'] === 'A' ? '2' : '3' ?>">
                    <div class="gauge-card">
                        <div class="gauge-title">Influencia del Entorno</div>
                        <div class="gauge-wrapper" style="height: 180px;">
                            <canvas id="gaugeDimInfluenciaEntorno"></canvas>
                            <div class="needle" id="needleDimInfluenciaEntorno"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.2rem;"><?= number_format($results['dim_influencia_entorno_puntaje'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.7rem;"><?= strtoupper($results['dim_influencia_entorno_nivel']) ?></div>
                        </div>
                    </div>
                </div>

                <!-- Exigencias de responsabilidad del cargo (solo Forma A) -->
                <?php if ($worker['intralaboral_type'] === 'A'): ?>
                <div class="col-md-2">
                    <div class="gauge-card">
                        <div class="gauge-title">Responsabilidad del Cargo</div>
                        <div class="gauge-wrapper" style="height: 180px;">
                            <canvas id="gaugeDimResponsabilidad"></canvas>
                            <div class="needle" id="needleDimResponsabilidad"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.2rem;"><?= number_format($results['dim_responsabilidad_cargo_puntaje'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.7rem;"><?= strtoupper($results['dim_responsabilidad_cargo_nivel']) ?></div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Demandas de carga mental -->
                <div class="col-md-<?= $worker['intralaboral_type'] === 'A' ? '2' : '4' ?>">
                    <div class="gauge-card">
                        <div class="gauge-title">Carga Mental</div>
                        <div class="gauge-wrapper" style="height: 180px;">
                            <canvas id="gaugeDimCargaMental"></canvas>
                            <div class="needle" id="needleDimCargaMental"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.2rem;"><?= number_format($results['dim_carga_mental_puntaje'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.7rem;"><?= strtoupper($results['dim_carga_mental_nivel']) ?></div>
                        </div>
                    </div>
                </div>

                <!-- Demandas de la jornada de trabajo -->
                <div class="col-md-<?= $worker['intralaboral_type'] === 'A' ? '2' : '4' ?>">
                    <div class="gauge-card">
                        <div class="gauge-title">Jornada de Trabajo</div>
                        <div class="gauge-wrapper" style="height: 180px;">
                            <canvas id="gaugeDimJornada"></canvas>
                            <div class="needle" id="needleDimJornada"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.2rem;"><?= number_format($results['dim_jornada_trabajo_puntaje'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.7rem;"><?= strtoupper($results['dim_jornada_trabajo_nivel']) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECCIÓN 6: DIMENSIONES INTRALABORALES - RECOMPENSAS -->
        <div class="gauge-section">
            <h2 class="section-title">
                <i class="fas fa-puzzle-piece me-2"></i>Dimensiones - Dominio Recompensas
            </h2>

            <div class="row">
                <!-- Reconocimiento y compensación -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title">Reconocimiento y Compensación</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDimReconocimiento"></canvas>
                            <div class="needle" id="needleDimReconocimiento"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($results['dim_reconocimiento_puntaje'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($results['dim_reconocimiento_nivel']) ?></div>
                        </div>
                    </div>
                </div>

                <!-- Recompensas derivadas de la pertenencia a la organización -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title">Pertenencia a la Organización</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDimPertenencia"></canvas>
                            <div class="needle" id="needleDimPertenencia"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($results['dim_pertenencia_puntaje'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($results['dim_pertenencia_nivel']) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECCIÓN 7: DIMENSIONES EXTRALABORALES -->
        <div class="gauge-section">
            <h2 class="section-title">
                <i class="fas fa-home me-2"></i>Dimensiones Extralaborales
            </h2>

            <div class="row">
                <!-- Tiempo fuera del trabajo -->
                <div class="col-md-4">
                    <div class="gauge-card">
                        <div class="gauge-title">Tiempo Fuera del Trabajo</div>
                        <div class="gauge-wrapper" style="height: 180px;">
                            <canvas id="gaugeDimTiempoFuera"></canvas>
                            <div class="needle" id="needleDimTiempoFuera"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.2rem;"><?= number_format($results['extralaboral_tiempo_fuera_puntaje'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.7rem;"><?= strtoupper($results['extralaboral_tiempo_fuera_nivel']) ?></div>
                        </div>
                    </div>
                </div>

                <!-- Relaciones familiares -->
                <div class="col-md-4">
                    <div class="gauge-card">
                        <div class="gauge-title">Relaciones Familiares</div>
                        <div class="gauge-wrapper" style="height: 180px;">
                            <canvas id="gaugeDimRelacionesFamiliares"></canvas>
                            <div class="needle" id="needleDimRelacionesFamiliares"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.2rem;"><?= number_format($results['extralaboral_relaciones_familiares_puntaje'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.7rem;"><?= strtoupper($results['extralaboral_relaciones_familiares_nivel']) ?></div>
                        </div>
                    </div>
                </div>

                <!-- Comunicación y relaciones interpersonales -->
                <div class="col-md-4">
                    <div class="gauge-card">
                        <div class="gauge-title">Comunicación Interpersonal</div>
                        <div class="gauge-wrapper" style="height: 180px;">
                            <canvas id="gaugeDimComunicacion"></canvas>
                            <div class="needle" id="needleDimComunicacion"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.2rem;"><?= number_format($results['extralaboral_comunicacion_puntaje'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.7rem;"><?= strtoupper($results['extralaboral_comunicacion_nivel']) ?></div>
                        </div>
                    </div>
                </div>

                <!-- Situación económica del grupo familiar -->
                <div class="col-md-3">
                    <div class="gauge-card">
                        <div class="gauge-title">Situación Económica</div>
                        <div class="gauge-wrapper" style="height: 180px;">
                            <canvas id="gaugeDimSituacionEconomica"></canvas>
                            <div class="needle" id="needleDimSituacionEconomica"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.2rem;"><?= number_format($results['extralaboral_situacion_economica_puntaje'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.7rem;"><?= strtoupper($results['extralaboral_situacion_economica_nivel']) ?></div>
                        </div>
                    </div>
                </div>

                <!-- Características de la vivienda y de su entorno -->
                <div class="col-md-3">
                    <div class="gauge-card">
                        <div class="gauge-title">Vivienda</div>
                        <div class="gauge-wrapper" style="height: 180px;">
                            <canvas id="gaugeDimVivienda"></canvas>
                            <div class="needle" id="needleDimVivienda"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.2rem;"><?= number_format($results['extralaboral_vivienda_puntaje'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.7rem;"><?= strtoupper($results['extralaboral_vivienda_nivel']) ?></div>
                        </div>
                    </div>
                </div>

                <!-- Influencia del entorno extralaboral sobre el trabajo -->
                <div class="col-md-3">
                    <div class="gauge-card">
                        <div class="gauge-title">Influencia Extralaboral</div>
                        <div class="gauge-wrapper" style="height: 180px;">
                            <canvas id="gaugeDimInfluenciaExtralaboral"></canvas>
                            <div class="needle" id="needleDimInfluenciaExtralaboral"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.2rem;"><?= number_format($results['extralaboral_influencia_entorno_puntaje'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.7rem;"><?= strtoupper($results['extralaboral_influencia_entorno_nivel']) ?></div>
                        </div>
                    </div>
                </div>

                <!-- Desplazamiento vivienda-trabajo-vivienda -->
                <div class="col-md-3">
                    <div class="gauge-card">
                        <div class="gauge-title">Desplazamiento</div>
                        <div class="gauge-wrapper" style="height: 180px;">
                            <canvas id="gaugeDimDesplazamiento"></canvas>
                            <div class="needle" id="needleDimDesplazamiento"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.2rem;"><?= number_format($results['extralaboral_desplazamiento_puntaje'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.7rem;"><?= strtoupper($results['extralaboral_desplazamiento_nivel']) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botón de volver -->
        <div class="text-center mb-4 no-print">
            <a href="<?= base_url('workers/results/' . $worker['id']) ?>" class="btn btn-lg btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Volver a Resultados
            </a>
        </div>
    </div>

    <script>
        // Registrar plugin de datalabels
        Chart.register(ChartDataLabels);

        // Función helper para crear gauge
        function createGauge(canvasId, needleId, score, baremos, showLabels = true) {
            const canvas = document.getElementById(canvasId);
            const needle = document.getElementById(needleId);

            // Calcular segmentos proporcionales
            const segments = [
                baremos.sin_riesgo[1] - baremos.sin_riesgo[0],
                baremos.riesgo_bajo[1] - baremos.riesgo_bajo[0],
                baremos.riesgo_medio[1] - baremos.riesgo_medio[0],
                baremos.riesgo_alto[1] - baremos.riesgo_alto[0],
                baremos.riesgo_muy_alto[1] - baremos.riesgo_muy_alto[0]
            ];

            // Calcular ángulo de la aguja (-90 a 90)
            const angleDeg = -90 + (score / 100) * 180;
            needle.style.transform = `rotate(${angleDeg}deg)`;

            // Labels para cada segmento (rangos)
            const labels = showLabels ? [
                `${baremos.sin_riesgo[0]}-${baremos.sin_riesgo[1]}`,
                `${baremos.riesgo_bajo[0]}-${baremos.riesgo_bajo[1]}`,
                `${baremos.riesgo_medio[0]}-${baremos.riesgo_medio[1]}`,
                `${baremos.riesgo_alto[0]}-${baremos.riesgo_alto[1]}`,
                `${baremos.riesgo_muy_alto[0]}-${baremos.riesgo_muy_alto[1]}`
            ] : ['', '', '', '', ''];

            new Chart(canvas, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: segments,
                        backgroundColor: ['#28a745', '#90ee90', '#FFFF00', '#dc3545', '#8b0000'],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    circumference: 180,
                    rotation: -90,
                    plugins: {
                        legend: { display: false },
                        tooltip: { enabled: false },
                        datalabels: {
                            color: '#000',
                            font: { weight: 'bold', size: showLabels ? 10 : 0 },
                            formatter: (value, ctx) => ctx.chart.data.labels[ctx.dataIndex],
                            anchor: 'center',
                            align: 'end',
                            offset: -5
                        }
                    }
                }
            });
        }

        // Función para renderizar baremos
        function renderBaremos(containerId, baremos) {
            const container = document.getElementById(containerId);
            const niveles = [
                { key: 'sin_riesgo', label: 'Sin Riesgo', class: 'sin-riesgo' },
                { key: 'riesgo_bajo', label: 'Bajo', class: 'riesgo-bajo' },
                { key: 'riesgo_medio', label: 'Medio', class: 'riesgo-medio' },
                { key: 'riesgo_alto', label: 'Alto', class: 'riesgo-alto' },
                { key: 'riesgo_muy_alto', label: 'Muy Alto', class: 'riesgo-muy-alto' }
            ];

            niveles.forEach(nivel => {
                const rango = baremos[nivel.key];
                const card = document.createElement('div');
                card.className = `baremo-card ${nivel.class}`;
                card.innerHTML = `
                    <div class="baremo-header">${nivel.label}</div>
                    <div class="baremo-range">${rango[0]}-${rango[1]}</div>
                `;
                container.appendChild(card);
            });
        }

        // Baremos de Tabla 33 (Total Intralaboral Forma <?= $worker['intralaboral_type'] ?>)
        <?php if ($worker['intralaboral_type'] === 'A'): ?>
        const baremosIntralaboral = {
            sin_riesgo: [0.0, 20.6],
            riesgo_bajo: [20.8, 26.0],
            riesgo_medio: [26.1, 31.5],
            riesgo_alto: [31.6, 38.9],
            riesgo_muy_alto: [39.0, 100.0]
        };
        <?php else: ?>
        const baremosIntralaboral = {
            sin_riesgo: [0.0, 22.6],
            riesgo_bajo: [22.8, 28.8],
            riesgo_medio: [28.9, 35.4],
            riesgo_alto: [35.5, 42.9],
            riesgo_muy_alto: [43.0, 100.0]
        };
        <?php endif; ?>

        // Baremos de Tabla 34 (Total Extralaboral Forma <?= $worker['intralaboral_type'] ?>)
        <?php if ($worker['intralaboral_type'] === 'A'): ?>
        const baremosExtralaboral = {
            sin_riesgo: [0.0, 12.5],
            riesgo_bajo: [12.6, 17.7],
            riesgo_medio: [17.8, 24.3],
            riesgo_alto: [24.4, 31.5],
            riesgo_muy_alto: [31.6, 100.0]
        };
        <?php else: ?>
        const baremosExtralaboral = {
            sin_riesgo: [0.0, 13.9],
            riesgo_bajo: [14.0, 20.0],
            riesgo_medio: [20.1, 27.0],
            riesgo_alto: [27.1, 34.8],
            riesgo_muy_alto: [34.9, 100.0]
        };
        <?php endif; ?>

        // Baremos de Tabla 35 (Total Estrés)
        const baremosEstres = {
            sin_riesgo: [0.0, 7.8],
            riesgo_bajo: [7.9, 12.5],
            riesgo_medio: [12.6, 17.7],
            riesgo_alto: [17.8, 25.0],
            riesgo_muy_alto: [25.1, 100.0]
        };

        // Crear gauges principales
        createGauge('gaugeIntralaboral', 'needleIntralaboral', <?= $results['intralaboral_total_puntaje'] ?>, baremosIntralaboral);
        createGauge('gaugeExtralaboral', 'needleExtralaboral', <?= $results['extralaboral_total_puntaje'] ?>, baremosExtralaboral);
        createGauge('gaugeEstres', 'needleEstres', <?= $results['estres_total_puntaje'] ?>, baremosEstres);

        // Renderizar baremos
        renderBaremos('baremosIntralaboral', baremosIntralaboral);
        renderBaremos('baremosExtralaboral', baremosExtralaboral);
        renderBaremos('baremosEstres', baremosEstres);

        // Crear gauges de dominios (con baremos de Tabla 32)
        // TODO: Agregar baremos específicos para cada dominio según Tabla 32
        createGauge('gaugeDomLiderazgo', 'needleDomLiderazgo', <?= $results['dom_liderazgo_puntaje'] ?>, baremosIntralaboral, false);
        createGauge('gaugeDomControl', 'needleDomControl', <?= $results['dom_control_puntaje'] ?>, baremosIntralaboral, false);
        createGauge('gaugeDomDemandas', 'needleDomDemandas', <?= $results['dom_demandas_puntaje'] ?>, baremosIntralaboral, false);
        createGauge('gaugeDomRecompensas', 'needleDomRecompensas', <?= $results['dom_recompensas_puntaje'] ?>, baremosIntralaboral, false);
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
