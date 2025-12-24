<?php
/**
 * Dashboard Estrés - Vista Completa con Segmentadores
 * Incluye análisis detallado de las 31 preguntas del cuestionario de estrés
 */

// Helper function para badges de riesgo de estrés
function getEstresBadgeClass($nivel) {
    $styles = [
        'muy_bajo' => 'background-color: #28a745; color: white;',
        'bajo' => 'background-color: #7dce82; color: white;',
        'medio' => 'background-color: #ffc107; color: #333;',
        'alto' => 'background-color: #fd7e14; color: white;',
        'muy_alto' => 'background-color: #dc3545; color: white;'
    ];
    return $styles[$nivel] ?? 'background-color: #6c757d; color: white;';
}

function getEstresRiskLabel($nivel) {
    $labels = [
        'muy_bajo' => 'Muy Bajo',
        'bajo' => 'Bajo',
        'medio' => 'Medio',
        'alto' => 'Alto',
        'muy_alto' => 'Muy Alto'
    ];
    return $labels[$nivel] ?? 'N/A';
}

// Textos de las 31 preguntas del cuestionario de estrés
$estresQuestions = [
    1 => 'Dolores en el cuello y espalda o tensión muscular',
    2 => 'Problemas gastrointestinales, úlcera péptica, acidez, problemas digestivos o del colon',
    3 => 'Problemas respiratorios',
    4 => 'Dolor de cabeza',
    5 => 'Trastornos del sueño como somnolencia durante el día o desvelo en la noche',
    6 => 'Palpitaciones en el pecho o problemas cardíacos',
    7 => 'Cambios fuertes del apetito',
    8 => 'Problemas relacionados con la función de los órganos genitales (impotencia, frigidez)',
    9 => 'Dificultad en las relaciones familiares',
    10 => 'Dificultad para permanecer quieto o dificultad para iniciar actividades',
    11 => 'Dificultad en las relaciones con otras personas',
    12 => 'Sensación de aislamiento y desinterés',
    13 => 'Sentimiento de sobrecarga de trabajo',
    14 => 'Dificultad para concentrarse, olvidos frecuentes',
    15 => 'Aumento en el número de accidentes de trabajo',
    16 => 'Sentimiento de frustración, de no haber hecho lo que se quería en la vida',
    17 => 'Cansancio, tedio o desgano',
    18 => 'Disminución del rendimiento en el trabajo o poca creatividad',
    19 => 'Deseo de no asistir al trabajo',
    20 => 'Bajo compromiso o poco interés con lo que se hace',
    21 => 'Dificultad para tomar decisiones',
    22 => 'Deseo de cambiar de empleo',
    23 => 'Sentimiento de soledad y miedo',
    24 => 'Sentimiento de irritabilidad, actitudes y pensamientos negativos',
    25 => 'Sentimiento de angustia, preocupación o tristeza',
    26 => 'Consumo de drogas para aliviar la tensión o los nervios',
    27 => 'Sentimientos de que "no vale nada", o "no sirve para nada"',
    28 => 'Consumo de bebidas alcohólicas o café o cigarrillo',
    29 => 'Sentimiento de que está perdiendo la razón',
    30 => 'Comportamientos rígidos, obstinación o terquedad',
    31 => 'Sensación de no poder manejar los problemas de la vida'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?> - PsyRisk</title>

    <!-- Bootstrap 5.3.0 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <!-- Chart.js 4.4.0 -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0"></script>

    <style>
        :root {
            --color-muy-bajo: #28a745;
            --color-bajo: #7dce82;
            --color-medio: #ffc107;
            --color-alto: #fd7e14;
            --color-muy-alto: #dc3545;
        }

        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 0.875rem;
        }

        .navbar-custom {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .stat-card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: transform 0.2s, box-shadow 0.2s;
            height: 100%;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .risk-badge {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
            min-width: 80px;
            text-align: center;
        }

        .segmentador-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }

        .filter-group {
            margin-bottom: 15px;
        }

        .filter-group label {
            font-weight: 600;
            font-size: 0.85rem;
            color: #495057;
            margin-bottom: 5px;
        }

        .filter-group select {
            font-size: 0.85rem;
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
        }

        table.dataTable thead th {
            background: #f8f9fa;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #495057;
        }

        .symptoms-table {
            font-size: 0.8rem;
            width: 100%;
        }

        .symptoms-table th {
            font-weight: 600;
            padding: 12px 8px;
            text-align: center;
            vertical-align: middle;
        }

        .symptoms-table td {
            vertical-align: middle;
            padding: 8px;
        }

        .count-badge {
            display: inline-block;
            min-width: 35px;
            padding: 4px 8px;
            border-radius: 4px;
            text-align: center;
            font-weight: 600;
        }

        .count-siempre {
            background-color: #dc3545;
            color: white !important;
        }
        .count-casi-siempre {
            background-color: #fd7e14;
            color: white !important;
        }
        .count-aveces {
            background-color: #ffc107;
            color: #333 !important;
        }
        .count-nunca {
            background-color: #28a745;
            color: white !important;
        }
    </style>
</head>
<body>

<!-- Top Navbar -->
<nav class="navbar navbar-custom navbar-expand-lg p-3">
    <div class="container-fluid">
        <a href="<?= base_url('dashboard') ?>" class="btn btn-outline-secondary btn-sm me-3">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
        <h5 class="mb-0"><i class="fas fa-heartbeat me-2 text-warning"></i><?= $title ?></h5>
        <div class="ms-auto">
            <button class="btn btn-success btn-sm me-2" onclick="window.print()">
                <i class="fas fa-print me-1"></i>Imprimir
            </button>
            <a href="<?= base_url('reports/export-pdf/' . $service['id'] . '/estres') ?>" class="btn btn-danger btn-sm me-2">
                <i class="fas fa-file-pdf me-1"></i>PDF Completo
            </a>
            <a href="<?= base_url('reports/estres/executive/' . $service['id']) ?>" class="btn btn-warning btn-sm">
                <i class="fas fa-bolt me-1"></i>Informe Ejecutivo
            </a>
        </div>
    </div>
</nav>

<!-- Main Content -->
<div class="container-fluid p-4">

    <!-- Service Info Card -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 class="fw-bold mb-2">
                        <i class="fas fa-briefcase me-2 text-primary"></i>
                        <?= esc($service['service_name']) ?>
                    </h5>
                    <p class="mb-0 text-muted">
                        <i class="fas fa-building me-2"></i><?= esc($service['company_name']) ?>
                        <span class="mx-2">|</span>
                        <i class="fas fa-calendar me-2"></i><?= date('d/m/Y', strtotime($service['service_date'])) ?>
                        <span class="mx-2">|</span>
                        <i class="fas fa-user me-2"></i><?= esc($service['consultant_name'] ?? 'N/A') ?>
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <span class="badge bg-warning" style="font-size: 1rem; padding: 10px 20px;">
                        <i class="fas fa-users me-2"></i><?= $totalWorkers ?> Trabajadores
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Segmentadores y Filtros -->
    <div class="segmentador-card">
        <h6 class="fw-bold mb-3">
            <i class="fas fa-filter text-primary me-2"></i>Segmentadores y Filtros
        </h6>

        <!-- Filtros de Riesgo -->
        <div class="alert alert-light border mb-3">
            <h6 class="fw-bold mb-2 small"><i class="fas fa-exclamation-triangle text-danger me-2"></i>Filtros de Riesgo</h6>
            <div class="row">
                <!-- Nivel de Riesgo -->
                <div class="col-md-3">
                    <div class="filter-group">
                        <label><i class="fas fa-chart-line me-1"></i>Nivel de Estrés</label>
                        <select class="form-select form-select-sm" id="filter_risk_level">
                            <option value="">Todos</option>
                            <?php foreach ($segmentadores['niveles_riesgo'] as $nivel): ?>
                                <option value="<?= $nivel ?>"><?= getEstresRiskLabel($nivel) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Filtro por Frecuencia de Síntoma -->
                <div class="col-md-9">
                    <div class="filter-group">
                        <label><i class="fas fa-notes-medical me-1"></i>Filtro por Frecuencia de Síntomas (aplica a tabla de 31 preguntas)</label>
                        <div class="btn-group btn-group-sm" role="group">
                            <input type="radio" class="btn-check" name="filter_symptom_frequency" id="filter_symptom_all" value="" checked>
                            <label class="btn btn-outline-secondary" for="filter_symptom_all">Todos</label>

                            <input type="radio" class="btn-check" name="filter_symptom_frequency" id="filter_symptom_siempre" value="siempre">
                            <label class="btn btn-outline-danger" for="filter_symptom_siempre">Siempre</label>

                            <input type="radio" class="btn-check" name="filter_symptom_frequency" id="filter_symptom_casi_siempre" value="casi_siempre">
                            <label class="btn btn-outline-warning" for="filter_symptom_casi_siempre">Casi Siempre</label>

                            <input type="radio" class="btn-check" name="filter_symptom_frequency" id="filter_symptom_aveces" value="a_veces">
                            <label class="btn btn-outline-info" for="filter_symptom_aveces">A Veces</label>

                            <input type="radio" class="btn-check" name="filter_symptom_frequency" id="filter_symptom_nunca" value="nunca">
                            <label class="btn btn-outline-success" for="filter_symptom_nunca">Nunca</label>

                            <input type="radio" class="btn-check" name="filter_symptom_frequency" id="filter_symptom_critico" value="critico">
                            <label class="btn btn-outline-dark" for="filter_symptom_critico">Crítico (Siempre+Casi Siempre)</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros Demográficos -->
        <div class="alert alert-light border mb-3">
            <h6 class="fw-bold mb-2 small"><i class="fas fa-users text-info me-2"></i>Filtros Demográficos</h6>
            <div class="row">
                <!-- Género -->
                <?php if (!empty($segmentadores['generos'])): ?>
                <div class="col-md-2">
                    <div class="filter-group">
                        <label><i class="fas fa-venus-mars me-1"></i>Género</label>
                        <select class="form-select form-select-sm" id="filter_gender">
                            <option value="">Todos</option>
                            <?php foreach ($segmentadores['generos'] as $genero): ?>
                                <option value="<?= esc($genero) ?>"><?= esc($genero) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Departamento -->
                <?php if (!empty($segmentadores['departamentos'])): ?>
                <div class="col-md-3">
                    <div class="filter-group">
                        <label><i class="fas fa-sitemap me-1"></i>Departamento</label>
                        <select class="form-select form-select-sm" id="filter_department">
                            <option value="">Todos</option>
                            <?php foreach ($segmentadores['departamentos'] as $dept): ?>
                                <option value="<?= esc($dept) ?>"><?= esc($dept) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Tipo de Cargo -->
                <?php if (!empty($segmentadores['tipos_cargo'])): ?>
                <div class="col-md-2">
                    <div class="filter-group">
                        <label><i class="fas fa-user-tag me-1"></i>Tipo de Cargo</label>
                        <select class="form-select form-select-sm" id="filter_position_type">
                            <option value="">Todos</option>
                            <?php foreach ($segmentadores['tipos_cargo'] as $tipo): ?>
                                <option value="<?= esc($tipo) ?>"><?= esc($tipo) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Cargo Específico -->
                <?php if (!empty($segmentadores['cargos'])): ?>
                <div class="col-md-2">
                    <div class="filter-group">
                        <label><i class="fas fa-id-badge me-1"></i>Cargo</label>
                        <select class="form-select form-select-sm" id="filter_position">
                            <option value="">Todos</option>
                            <?php foreach ($segmentadores['cargos'] as $cargo): ?>
                                <option value="<?= esc($cargo) ?>"><?= esc($cargo) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Nivel de Estudios -->
                <?php if (!empty($segmentadores['niveles_estudio'])): ?>
                <div class="col-md-2">
                    <div class="filter-group">
                        <label><i class="fas fa-graduation-cap me-1"></i>Nivel Estudios</label>
                        <select class="form-select form-select-sm" id="filter_education">
                            <option value="">Todos</option>
                            <?php foreach ($segmentadores['niveles_estudio'] as $nivel): ?>
                                <option value="<?= esc($nivel) ?>"><?= esc($nivel) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Estado Civil -->
                <?php if (!empty($segmentadores['estados_civiles'])): ?>
                <div class="col-md-2">
                    <div class="filter-group">
                        <label><i class="fas fa-heart me-1"></i>Estado Civil</label>
                        <select class="form-select form-select-sm" id="filter_marital_status">
                            <option value="">Todos</option>
                            <?php foreach ($segmentadores['estados_civiles'] as $estado): ?>
                                <option value="<?= esc($estado) ?>"><?= esc($estado) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Filtros Laborales y Ubicación -->
        <div class="alert alert-light border mb-0">
            <h6 class="fw-bold mb-2 small"><i class="fas fa-briefcase text-success me-2"></i>Filtros Laborales y Ubicación</h6>
            <div class="row">
                <!-- Tipo de Contrato -->
                <?php if (!empty($segmentadores['tipos_contrato'])): ?>
                <div class="col-md-3">
                    <div class="filter-group">
                        <label><i class="fas fa-file-contract me-1"></i>Tipo Contrato</label>
                        <select class="form-select form-select-sm" id="filter_contract_type">
                            <option value="">Todos</option>
                            <?php foreach ($segmentadores['tipos_contrato'] as $tipo): ?>
                                <option value="<?= esc($tipo) ?>"><?= esc($tipo) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Ciudad -->
                <?php if (!empty($segmentadores['ciudades'])): ?>
                <div class="col-md-3">
                    <div class="filter-group">
                        <label><i class="fas fa-map-marker-alt me-1"></i>Ciudad</label>
                        <select class="form-select form-select-sm" id="filter_city">
                            <option value="">Todas</option>
                            <?php foreach ($segmentadores['ciudades'] as $ciudad): ?>
                                <option value="<?= esc($ciudad) ?>"><?= esc($ciudad) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Estrato -->
                <?php if (!empty($segmentadores['estratos'])): ?>
                <div class="col-md-2">
                    <div class="filter-group">
                        <label><i class="fas fa-layer-group me-1"></i>Estrato</label>
                        <select class="form-select form-select-sm" id="filter_stratum">
                            <option value="">Todos</option>
                            <?php foreach ($segmentadores['estratos'] as $estrato): ?>
                                <option value="<?= esc($estrato) ?>">Estrato <?= esc($estrato) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Tipo de Vivienda -->
                <?php if (!empty($segmentadores['tipos_vivienda'])): ?>
                <div class="col-md-2">
                    <div class="filter-group">
                        <label><i class="fas fa-home me-1"></i>Tipo Vivienda</label>
                        <select class="form-select form-select-sm" id="filter_housing_type">
                            <option value="">Todos</option>
                            <?php foreach ($segmentadores['tipos_vivienda'] as $tipo): ?>
                                <option value="<?= esc($tipo) ?>"><?= esc($tipo) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Antigüedad -->
                <?php if (!empty($segmentadores['antiguedad'])): ?>
                <div class="col-md-2">
                    <div class="filter-group">
                        <label><i class="fas fa-calendar-alt me-1"></i>Antigüedad</label>
                        <select class="form-select form-select-sm" id="filter_time_in_company">
                            <option value="">Todos</option>
                            <?php foreach ($segmentadores['antiguedad'] as $ant): ?>
                                <option value="<?= esc($ant) ?>"><?= esc($ant) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="text-center mt-3">
            <button class="btn btn-secondary btn-sm" onclick="clearAllFilters()">
                <i class="fas fa-redo me-1"></i>Limpiar Todos los Filtros
            </button>
        </div>
    </div>

    <!-- Distribución por Nivel de Estrés -->
    <h6 class="section-title">Distribución por Nivel de Estrés</h6>
    <div class="row mb-4">
        <?php
        $riskCards = [
            ['nivel' => 'muy_bajo', 'label' => 'MUY BAJO', 'color' => '#28a745', 'textColor' => '#fff'],
            ['nivel' => 'bajo', 'label' => 'BAJO', 'color' => '#28a745', 'textColor' => '#fff'],
            ['nivel' => 'medio', 'label' => 'MEDIO', 'color' => '#ffc107', 'textColor' => '#333'],
            ['nivel' => 'alto', 'label' => 'ALTO', 'color' => '#dc3545', 'textColor' => '#fff'],
            ['nivel' => 'muy_alto', 'label' => 'MUY ALTO', 'color' => '#dc3545', 'textColor' => '#fff']
        ];

        foreach ($riskCards as $card):
            $count = $stats['distribution'][$card['nivel']] ?? 0;
            $textColor = $card['textColor'];
        ?>
        <div class="col-md-6 col-lg mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center rounded" style="background-color: <?= $card['color'] ?>; color: <?= $textColor ?>;">
                    <h6 class="mb-2 text-uppercase" style="font-size: 0.7rem; font-weight: 600; letter-spacing: 0.5px;"><?= $card['label'] ?></h6>
                    <h1 class="fw-bold mb-1" style="font-size: 2.5rem;" data-stat-risk="<?= $card['nivel'] ?>"><?= $count ?></h1>
                    <small style="font-size: 0.75rem;">trabajadores</small>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- GRÁFICO: Distribución Porcentual por Nivel de Estrés -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <h6 class="mb-0 text-dark">
                <i class="fas fa-chart-pie me-2 text-primary"></i>Distribución Porcentual por Nivel de Estrés
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8 offset-md-2">
                    <canvas id="chartNivelesEstres" style="max-height: 350px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- NUEVA SECCIÓN: Análisis Detallado por Síntoma (31 Preguntas) -->
    <h6 class="section-title">
        <i class="fas fa-notes-medical me-2"></i>Análisis Detallado por Síntoma (31 Preguntas)
    </h6>

    <!-- GRÁFICO: Top 10 Síntomas más Frecuentes -->
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-white">
            <h6 class="mb-0 text-dark">
                <i class="fas fa-chart-bar me-2 text-danger"></i>Top 10 Síntomas más Frecuentes (Críticos)
            </h6>
        </div>
        <div class="card-body">
            <div class="alert alert-light border mb-3">
                <h6 class="mb-2"><i class="fas fa-info-circle text-primary me-2"></i>¿Cómo interpretar este gráfico?</h6>
                <p class="mb-2 small">Este gráfico muestra los <strong>10 síntomas con mayor número de casos críticos</strong> (suma de "Siempre" + "Casi Siempre").</p>
                <ul class="mb-0 small">
                    <li><strong>Cada barra</strong> representa un síntoma específico del cuestionario</li>
                    <li><strong>El largo total de la barra</strong> muestra cuántos trabajadores reportaron ese síntoma en cualquier frecuencia</li>
                    <li><strong>Los colores dentro de cada barra</strong> indican la frecuencia:
                        <span class="ms-2">🔴 Rojo = Siempre</span> |
                        <span>🟠 Naranja = Casi Siempre</span> |
                        <span>🟡 Amarillo = A Veces</span> |
                        <span>🟢 Verde = Nunca</span>
                    </li>
                    <li><strong>Prioriza la intervención</strong> en los síntomas con mayor segmento rojo y naranja (parte izquierda de la barra)</li>
                </ul>
            </div>
            <div style="height: 500px;">
                <canvas id="chartSintomas"></canvas>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="alert alert-info mb-3">
                <i class="fas fa-info-circle me-2"></i>
                <strong>¿Cómo interpretar esta tabla?</strong>
                <p class="mt-2 mb-2">Cada columna muestra <strong>cuántas personas respondieron cada opción de frecuencia</strong> para cada síntoma del cuestionario de estrés:</p>
                <ul class="mb-2">
                    <li><strong class="text-danger">Siempre</strong>: El trabajador presenta este síntoma de forma permanente (mayor riesgo)</li>
                    <li><strong class="text-warning">Casi Siempre</strong>: El trabajador presenta este síntoma frecuentemente (alto riesgo)</li>
                    <li><strong class="text-info">A Veces</strong>: El trabajador presenta este síntoma ocasionalmente (riesgo moderado)</li>
                    <li><strong class="text-success">Nunca</strong>: El trabajador NO presenta este síntoma (sin riesgo)</li>
                </ul>
                <p class="mb-0"><i class="fas fa-exclamation-triangle text-danger me-1"></i>La columna <strong>Crítico</strong> muestra cuántas personas presentan cada síntoma de forma permanente o frecuente (Siempre + Casi Siempre). <strong>Un número alto de personas indica que ese síntoma requiere intervención urgente.</strong></p>
            </div>

            <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                <table id="tableSintomas" class="table table-bordered symptoms-table">
                    <thead>
                        <tr>
                            <th style="width: 5%; background-color: #f8f9fa;">#</th>
                            <th style="width: 35%; background-color: #f8f9fa; text-align: left;">Síntoma / Pregunta</th>
                            <th style="width: 12%; background-color: #dc3545; color: white;">Siempre</th>
                            <th style="width: 12%; background-color: #fd7e14; color: white;">Casi Siempre</th>
                            <th style="width: 12%; background-color: #ffc107; color: white;">A Veces</th>
                            <th style="width: 12%; background-color: #28a745; color: white;">Nunca</th>
                            <th style="width: 12%; background-color: #6c757d; color: white;">Crítico</th>
                        </tr>
                    </thead>
                    <tbody id="sintomasTableBody">
                        <?php for ($i = 1; $i <= 31; $i++): ?>
                        <tr data-question="<?= $i ?>">
                            <td class="text-center"><strong><?= $i ?></strong></td>
                            <td><small><?= $estresQuestions[$i] ?></small></td>
                            <td class="text-center"><strong><span class="count-badge count-siempre" data-q="<?= $i ?>" data-answer="siempre">0</span></strong></td>
                            <td class="text-center"><strong><span class="count-badge count-casi-siempre" data-q="<?= $i ?>" data-answer="casi_siempre">0</span></strong></td>
                            <td class="text-center"><strong><span class="count-badge count-aveces" data-q="<?= $i ?>" data-answer="a_veces">0</span></strong></td>
                            <td class="text-center"><strong><span class="count-badge count-nunca" data-q="<?= $i ?>" data-answer="nunca">0</span></strong></td>
                            <td class="text-center"><strong><span data-q="<?= $i ?>" data-answer="critico">0</span></strong></td>
                        </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tabla de Resultados Detallados por Trabajador -->
    <h6 class="section-title">
        <i class="fas fa-table me-2"></i>Resultados Detallados por Trabajador
    </h6>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tableResults" class="table table-striped table-hover" style="font-size: 0.8rem;">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Documento</th>
                            <th>Género</th>
                            <th>Departamento</th>
                            <th>Cargo</th>
                            <th>Tipo Cargo</th>
                            <th>Puntaje Total</th>
                            <th>Nivel Estrés</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $result): ?>
                        <tr>
                            <td><?= esc($result['worker_name'] ?? 'N/A') ?></td>
                            <td><?= esc($result['worker_document'] ?? 'N/A') ?></td>
                            <td><?= esc($result['gender'] ?? 'N/A') ?></td>
                            <td><?= esc($result['department'] ?? 'N/A') ?></td>
                            <td><?= esc($result['position'] ?? 'N/A') ?></td>
                            <td><?= esc($result['position_type'] ?? 'N/A') ?></td>
                            <td><strong><?= number_format($result['estres_total_puntaje'] ?? 0, 1) ?>%</strong></td>
                            <td>
                                <span class="risk-badge" style="<?= getEstresBadgeClass($result['estres_total_nivel'] ?? 'muy_bajo') ?>">
                                    <?= getEstresRiskLabel($result['estres_total_nivel'] ?? 'muy_bajo') ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= base_url("individual-results/request/{$serviceId}/{$result['worker_id']}/estres") ?>"
                                   class="btn btn-sm btn-primary" title="Solicitar acceso a resultados individuales">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
// ============================================
// DATOS GLOBALES
// ============================================
const allResults = <?= json_encode($results) ?>;
const responsesData = <?= json_encode($responsesData) ?>;
let filteredResults = [...allResults];

// Variables para los gráficos
let chartNivelesEstres = null;
let chartSintomas = null;

// DEBUG
console.log('=== DATOS ESTRÉS CARGADOS ===');
console.log('Total workers:', allResults.length);
console.log('Responses data sample:', responsesData);

// Helper functions
function getEstresBadgeClass(nivel) {
    const styles = {
        'muy_bajo': 'background-color: #28a745; color: white;',
        'bajo': 'background-color: #7dce82; color: white;',
        'medio': 'background-color: #ffc107; color: #333;',
        'alto': 'background-color: #fd7e14; color: white;',
        'muy_alto': 'background-color: #dc3545; color: white;'
    };
    return styles[nivel] || 'background-color: #6c757d; color: white;';
}

function getEstresRiskLabel(nivel) {
    const labels = {
        'muy_bajo': 'Muy Bajo',
        'bajo': 'Bajo',
        'medio': 'Medio',
        'alto': 'Alto',
        'muy_alto': 'Muy Alto'
    };
    return labels[nivel] || 'N/A';
}

// ============================================
// FUNCIÓN: Convertir puntaje numérico a frecuencia textual
// ============================================
function convertirPuntajeAFrecuencia(questionNum, puntaje) {
    const puntajeInt = parseInt(puntaje);

    // Tabla 4: Calificación de las opciones de respuesta
    // Preguntas 1, 2, 3, 9, 13, 14, 15, 23, 24
    const grupo1 = [1, 2, 3, 9, 13, 14, 15, 23, 24];
    // Preguntas 4, 5, 6, 10, 11, 16, 17, 18, 19, 25, 26, 27, 28
    const grupo2 = [4, 5, 6, 10, 11, 16, 17, 18, 19, 25, 26, 27, 28];
    // Preguntas 7, 8, 12, 20, 21, 22, 29, 30, 31
    const grupo3 = [7, 8, 12, 20, 21, 22, 29, 30, 31];

    if (grupo1.includes(questionNum)) {
        if (puntajeInt === 9) return 'siempre';
        if (puntajeInt === 6) return 'casi_siempre';
        if (puntajeInt === 3) return 'a_veces';
        if (puntajeInt === 0) return 'nunca';
    } else if (grupo2.includes(questionNum)) {
        if (puntajeInt === 6) return 'siempre';
        if (puntajeInt === 4) return 'casi_siempre';
        if (puntajeInt === 2) return 'a_veces';
        if (puntajeInt === 0) return 'nunca';
    } else if (grupo3.includes(questionNum)) {
        if (puntajeInt === 3) return 'siempre';
        if (puntajeInt === 2) return 'casi_siempre';
        if (puntajeInt === 1) return 'a_veces';
        if (puntajeInt === 0) return 'nunca';
    }

    return null; // Valor no reconocido
}

// ============================================
// FUNCIÓN: Actualizar Tabla de Síntomas
// ============================================
function updateSymptomsTable(data) {
    console.log('Updating symptoms table with', data.length, 'workers');

    // Inicializar contadores para cada pregunta
    const symptomCounts = {};
    for (let q = 1; q <= 31; q++) {
        symptomCounts[q] = {
            'siempre': 0,
            'casi_siempre': 0,
            'casi siempre': 0, // Variante
            'a_veces': 0,
            'a veces': 0, // Variante
            'nunca': 0,
            'total': 0
        };
    }

    // Contar respuestas de los trabajadores filtrados
    data.forEach(worker => {
        const workerId = worker.worker_id;
        if (responsesData[workerId]) {
            const workerResponses = responsesData[workerId];
            for (let q = 1; q <= 31; q++) {
                if (workerResponses[q] !== undefined && workerResponses[q] !== null) {
                    let answer = String(workerResponses[q]).toLowerCase().replace(/ /g, '_');

                    // Si es un valor numérico, convertirlo a frecuencia textual
                    if (!isNaN(answer)) {
                        const frecuencia = convertirPuntajeAFrecuencia(q, answer);
                        if (frecuencia) {
                            answer = frecuencia;
                        } else {
                            console.warn(`Question ${q} for worker ${workerId} has unrecognized numeric value: ${answer}`);
                            continue;
                        }
                    }

                    if (symptomCounts[q][answer] !== undefined) {
                        symptomCounts[q][answer]++;
                        symptomCounts[q]['total']++;
                    }
                }
            }
        }
    });

    console.log('Symptom counts sample (Q1):', symptomCounts[1]);

    // Actualizar la tabla HTML y preparar datos para gráfico
    const symptomDataForChart = [];

    for (let q = 1; q <= 31; q++) {
        // Consolidar "casi siempre" y "casi_siempre"
        const casiSiempreTotal = (symptomCounts[q]['casi_siempre'] || 0) + (symptomCounts[q]['casi siempre'] || 0);
        const avecesTotal = (symptomCounts[q]['a_veces'] || 0) + (symptomCounts[q]['a veces'] || 0);
        const siempreTotal = symptomCounts[q]['siempre'] || 0;
        const nuncaTotal = symptomCounts[q]['nunca'] || 0;
        const total = symptomCounts[q]['total'];

        // Calcular crítico (Siempre + Casi Siempre) - NÚMERO DE PERSONAS
        const critico = siempreTotal + casiSiempreTotal;

        $(`span[data-q="${q}"][data-answer="siempre"]`).text(siempreTotal);
        $(`span[data-q="${q}"][data-answer="casi_siempre"]`).text(casiSiempreTotal);
        $(`span[data-q="${q}"][data-answer="a_veces"]`).text(avecesTotal);
        $(`span[data-q="${q}"][data-answer="nunca"]`).text(nuncaTotal);
        $(`span[data-q="${q}"][data-answer="critico"]`).text(critico).css('color', '#333');

        // Guardar datos para el gráfico
        symptomDataForChart.push({
            questionNum: q,
            siempre: siempreTotal,
            casiSiempre: casiSiempreTotal,
            aVeces: avecesTotal,
            nunca: nuncaTotal,
            critico: critico
        });
    }

    // Actualizar gráfico de síntomas
    updateChartSintomas(symptomDataForChart);
}

// ============================================
// FUNCIÓN: Aplicar Filtros
// ============================================
function applyFilters() {
    const filters = {
        risk_level: $('#filter_risk_level').val(),
        gender: $('#filter_gender').val(),
        department: $('#filter_department').val(),
        position_type: $('#filter_position_type').val(),
        position: $('#filter_position').val(),
        education: $('#filter_education').val(),
        marital_status: $('#filter_marital_status').val(),
        contract_type: $('#filter_contract_type').val(),
        city: $('#filter_city').val(),
        stratum: $('#filter_stratum').val(),
        housing_type: $('#filter_housing_type').val(),
        time_in_company: $('#filter_time_in_company').val()
    };

    console.log('=== APPLY FILTERS ===');
    console.log('Active filters:', filters);

    filteredResults = allResults.filter(result => {
        // Filtro de nivel de riesgo
        if (filters.risk_level && result.estres_total_nivel !== filters.risk_level) return false;

        // Filtros demográficos
        if (filters.gender && result.gender !== filters.gender) return false;
        if (filters.department && result.department !== filters.department) return false;
        if (filters.position_type && result.position_type !== filters.position_type) return false;
        if (filters.position && result.position !== filters.position) return false;
        if (filters.education && result.education_level !== filters.education) return false;
        if (filters.marital_status && result.marital_status !== filters.marital_status) return false;

        // Filtros laborales
        if (filters.contract_type && result.contract_type !== filters.contract_type) return false;
        if (filters.city && result.city_residence !== filters.city) return false;
        if (filters.stratum && String(result.stratum) !== String(filters.stratum)) return false;
        if (filters.housing_type && result.housing_type !== filters.housing_type) return false;
        if (filters.time_in_company && result.time_in_company_type !== filters.time_in_company) return false;

        return true;
    });

    console.log('Filtered results:', filteredResults.length);

    // Actualizar estadísticas
    updateStats(filteredResults);

    // Actualizar tabla de síntomas
    updateSymptomsTable(filteredResults);

    // Actualizar tabla de trabajadores
    filterTable();
}

// ============================================
// FUNCIÓN: Actualizar Estadísticas
// ============================================
function updateStats(data) {
    // Actualizar distribución de riesgos
    const riskCounts = {
        'muy_bajo': 0,
        'bajo': 0,
        'medio': 0,
        'alto': 0,
        'muy_alto': 0
    };

    data.forEach(r => {
        const nivel = r.estres_total_nivel || 'muy_bajo';
        riskCounts[nivel]++;
    });

    Object.keys(riskCounts).forEach(nivel => {
        const elem = document.querySelector(`[data-stat-risk="${nivel}"]`);
        if (elem) {
            elem.textContent = riskCounts[nivel];
        }
    });

    // Actualizar gráfico de niveles de estrés
    updateChartNivelesEstres(riskCounts);
}

// ============================================
// FUNCIÓN: Crear/Actualizar Gráfico de Dona - Niveles de Estrés
// ============================================
function updateChartNivelesEstres(riskCounts) {
    const ctx = document.getElementById('chartNivelesEstres');
    if (!ctx) return;

    const data = {
        labels: ['Muy Bajo', 'Bajo', 'Medio', 'Alto', 'Muy Alto'],
        datasets: [{
            data: [
                riskCounts.muy_bajo,
                riskCounts.bajo,
                riskCounts.medio,
                riskCounts.alto,
                riskCounts.muy_alto
            ],
            backgroundColor: [
                '#28a745', // Verde - Muy Bajo
                '#28a745', // Verde - Bajo
                '#ffc107', // Amarillo - Medio
                '#dc3545', // Rojo - Alto
                '#dc3545'  // Rojo - Muy Alto
            ],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    };

    const config = {
        type: 'doughnut',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        font: {
                            size: 12
                        },
                        generateLabels: function(chart) {
                            const data = chart.data;
                            const total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                            return data.labels.map((label, i) => {
                                const value = data.datasets[0].data[i];
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return {
                                    text: `${label}: ${value} (${percentage}%)`,
                                    fillStyle: data.datasets[0].backgroundColor[i],
                                    hidden: false,
                                    index: i
                                };
                            });
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return `${label}: ${value} trabajadores (${percentage}%)`;
                        }
                    }
                }
            }
        }
    };

    // Destruir gráfico anterior si existe
    if (chartNivelesEstres) {
        chartNivelesEstres.destroy();
    }

    // Crear nuevo gráfico
    chartNivelesEstres = new Chart(ctx, config);
}

// ============================================
// FUNCIÓN: Crear/Actualizar Gráfico de Barras - Top 10 Síntomas
// ============================================
function updateChartSintomas(symptomData) {
    const ctx = document.getElementById('chartSintomas');
    if (!ctx) return;

    // Nombres de las preguntas
    const estresQuestions = <?= json_encode($estresQuestions) ?>;

    // Ordenar por crítico (descendente) y tomar top 10
    const top10 = symptomData
        .sort((a, b) => b.critico - a.critico)
        .slice(0, 10);

    // Preparar datos para el gráfico
    const labels = top10.map(item => {
        const question = estresQuestions[item.questionNum];
        // Truncar texto si es muy largo
        return question.length > 50 ? question.substring(0, 47) + '...' : question;
    });

    const data = {
        labels: labels,
        datasets: [
            {
                label: 'Siempre',
                data: top10.map(item => item.siempre),
                backgroundColor: '#dc3545',
                borderWidth: 0
            },
            {
                label: 'Casi Siempre',
                data: top10.map(item => item.casiSiempre),
                backgroundColor: '#fd7e14',
                borderWidth: 0
            },
            {
                label: 'A Veces',
                data: top10.map(item => item.aVeces),
                backgroundColor: '#ffc107',
                borderWidth: 0
            },
            {
                label: 'Nunca',
                data: top10.map(item => item.nunca),
                backgroundColor: '#28a745',
                borderWidth: 0
            }
        ]
    };

    const config = {
        type: 'bar',
        data: data,
        options: {
            indexAxis: 'y', // Barras horizontales
            responsive: true,
            maintainAspectRatio: false,
            barThickness: 35, // Hacer las barras más anchas
            categoryPercentage: 0.9, // Espacio entre categorías
            barPercentage: 0.95, // Ancho de las barras
            scales: {
                x: {
                    stacked: true,
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Número de Trabajadores',
                        font: {
                            size: 13,
                            weight: 'bold'
                        }
                    },
                    ticks: {
                        font: {
                            size: 11
                        }
                    },
                    grid: {
                        display: true,
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                y: {
                    stacked: true,
                    ticks: {
                        font: {
                            size: 12
                        },
                        autoSkip: false
                    },
                    grid: {
                        display: false
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        padding: 20,
                        font: {
                            size: 13,
                            weight: 'bold'
                        },
                        usePointStyle: true,
                        pointStyle: 'rect',
                        boxWidth: 15,
                        boxHeight: 15
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: {
                        size: 13,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 12
                    },
                    callbacks: {
                        title: function(context) {
                            const index = context[0].dataIndex;
                            const questionNum = top10[index].questionNum;
                            return `Pregunta ${questionNum}`;
                        },
                        beforeBody: function(context) {
                            const index = context[0].dataIndex;
                            const questionNum = top10[index].questionNum;
                            return estresQuestions[questionNum];
                        },
                        label: function(context) {
                            const value = context.parsed.x;
                            return `${context.dataset.label}: ${value} ${value === 1 ? 'trabajador' : 'trabajadores'}`;
                        },
                        footer: function(context) {
                            const index = context[0].dataIndex;
                            const item = top10[index];
                            const total = item.siempre + item.casiSiempre + item.aVeces + item.nunca;
                            const critico = item.critico;
                            return `\nTotal: ${total} | Crítico: ${critico}`;
                        }
                    }
                }
            }
        }
    };

    // Destruir gráfico anterior si existe
    if (chartSintomas) {
        chartSintomas.destroy();
    }

    // Crear nuevo gráfico
    chartSintomas = new Chart(ctx, config);
}

// ============================================
// FUNCIÓN: Filtrar Tabla de Trabajadores
// ============================================
let dataTable;
let sintomasTable;

function filterTable() {
    console.log('Filtering table with', filteredResults.length, 'results');

    dataTable.clear();

    filteredResults.forEach(r => {
        const $row = $('<tr>')
            .append($('<td>').text(r.worker_name || 'N/A'))
            .append($('<td>').text(r.worker_document || 'N/A'))
            .append($('<td>').text(r.gender || 'N/A'))
            .append($('<td>').text(r.department || 'N/A'))
            .append($('<td>').text(r.position || 'N/A'))
            .append($('<td>').text(r.position_type || 'N/A'))
            .append($('<td>').html('<strong>' + parseFloat(r.estres_total_puntaje || 0).toFixed(1) + '%</strong>'))
            .append($('<td>').html(
                '<span class="risk-badge" style="' + getEstresBadgeClass(r.estres_total_nivel || 'muy_bajo') + '">' +
                    getEstresRiskLabel(r.estres_total_nivel || 'muy_bajo') +
                '</span>'
            ))
            .append($('<td>').html(
                '<a href="<?= base_url("individual-results/request/") ?><?= $serviceId ?>/' + r.worker_id + '/estres" class="btn btn-sm btn-primary" title="Solicitar acceso a resultados individuales">' +
                    '<i class="fas fa-eye"></i>' +
                '</a>'
            ));

        dataTable.row.add($row);
    });

    dataTable.draw(false);
}

// ============================================
// FUNCIÓN: Filtrar Síntomas por Frecuencia
// ============================================
function filterSymptomsByFrequency(frequency) {
    if (!frequency) {
        // Mostrar todas las filas
        $('#tableSintomas tbody tr').show();
        return;
    }

    $('#tableSintomas tbody tr').each(function() {
        const $row = $(this);
        const questionNum = $row.attr('data-question');

        let shouldShow = false;

        if (frequency === 'critico') {
            // Mostrar si Crítico > 0
            const criticoValue = parseInt($(`span[data-q="${questionNum}"][data-answer="critico"]`).text());
            shouldShow = criticoValue > 0;
        } else {
            // Mostrar si la frecuencia específica > 0
            const freqValue = parseInt($(`span[data-q="${questionNum}"][data-answer="${frequency}"]`).text());
            shouldShow = freqValue > 0;
        }

        if (shouldShow) {
            $row.show();
        } else {
            $row.hide();
        }
    });
}

// ============================================
// FUNCIÓN: Limpiar Filtros
// ============================================
function clearAllFilters() {
    $('select[id^="filter_"]').val('');
    $('input[name="filter_symptom_frequency"][value=""]').prop('checked', true);
    filteredResults = [...allResults];
    updateStats(allResults);
    updateSymptomsTable(allResults);
    filterTable();
    filterSymptomsByFrequency('');
}

// ============================================
// INICIALIZACIÓN
// ============================================
$(document).ready(function() {
    // Inicializar DataTable de trabajadores
    dataTable = $('#tableResults').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
        },
        pageLength: 25,
        order: [[6, 'desc']]
    });

    // NO inicializar DataTable para síntomas - dejar como tabla simple
    sintomasTable = null;

    // Inicializar estadísticas con todos los resultados
    updateStats(allResults);

    // Inicializar tabla de síntomas
    updateSymptomsTable(allResults);

    // Inicializar tabla de trabajadores con todos los resultados
    filterTable();

    // Event listeners para filtros demográficos
    $('select[id^="filter_"]').on('change', function() {
        applyFilters();
    });

    // Event listener para filtro de frecuencia de síntomas
    $('input[name="filter_symptom_frequency"]').on('change', function() {
        filterSymptomsByFrequency($(this).val());
    });
});
</script>

</body>
</html>
