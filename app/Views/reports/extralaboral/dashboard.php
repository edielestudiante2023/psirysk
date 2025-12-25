<?php
/**
 * Dashboard Extralaboral - Vista Completa con Segmentadores
 * Basado en arquitectura de dashboard intralaboral
 * Adaptado para 7 dimensiones extralaborales (sin dominios)
 *
 * Dimensiones evaluadas:
 * 1. Tiempo fuera del trabajo
 * 2. Relaciones familiares
 * 3. Comunicación y relaciones interpersonales
 * 4. Situación económica del grupo familiar
 * 5. Características de la vivienda y su entorno
 * 6. Influencia del entorno extralaboral sobre el trabajo
 * 7. Desplazamiento vivienda-trabajo-vivienda
 */

// Helper function para badges de riesgo con colores oficiales
function getBadgeClass($nivel) {
    $styles = [
        'sin_riesgo' => 'background-color: #28a745; color: white;',      // Verde oscuro
        'riesgo_bajo' => 'background-color: #28a745; color: white;',     // Verde oscuro
        'riesgo_medio' => 'background-color: #ffc107; color: #333;',     // Amarillo
        'riesgo_alto' => 'background-color: #dc3545; color: white;',     // Rojo intenso
        'riesgo_muy_alto' => 'background-color: #dc3545; color: white;'  // Rojo intenso
    ];
    return $styles[$nivel] ?? 'background-color: #6c757d; color: white;';
}

function getRiskLabel($nivel) {
    $labels = [
        'sin_riesgo' => 'Sin Riesgo',
        'riesgo_bajo' => 'Riesgo Bajo',
        'riesgo_medio' => 'Riesgo Medio',
        'riesgo_alto' => 'Riesgo Alto',
        'riesgo_muy_alto' => 'Riesgo Muy Alto'
    ];
    return $labels[$nivel] ?? 'N/A';
}
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

    <!-- Chart.js 4.4.0 with DataLabels -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>

    <style>
        :root {
            --color-sin-riesgo: #28a745;   /* Verde oscuro */
            --color-bajo: #28a745;         /* Verde oscuro */
            --color-medio: #ffc107;        /* Amarillo */
            --color-alto: #dc3545;         /* Rojo intenso */
            --color-muy-alto: #dc3545;     /* Rojo intenso */
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

        .stat-card .icon {
            font-size: 2rem;
            opacity: 0.8;
        }

        .dimension-card {
            border-radius: 10px;
            border: none;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
            margin-bottom: 15px;
            transition: all 0.2s;
        }

        .dimension-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
        }

        .dimension-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
        }

        .dimension-value {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .risk-badge {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
            min-width: 100px;
            text-align: center;
        }

        .chart-container {
            position: relative;
            height: 320px;
            padding: 15px;
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

        .card-header-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            padding: 15px 20px;
        }

        table.dataTable thead th {
            background: #f8f9fa;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #495057;
        }

        .loading-spinner {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 9999;
            display: none;
        }

        .loading-spinner.active {
            display: block;
        }
    </style>
</head>
<body>

<!-- Loading Spinner -->
<div class="loading-spinner" id="loadingSpinner">
    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
        <span class="visually-hidden">Cargando...</span>
    </div>
</div>

<!-- Top Navbar -->
<nav class="navbar navbar-custom navbar-expand-lg p-3">
    <div class="container-fluid">
        <a href="<?= base_url('dashboard') ?>" class="btn btn-outline-secondary btn-sm me-3">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
        <h5 class="mb-0"><i class="fas fa-home me-2 text-primary"></i><?= $title ?></h5>
        <div class="ms-auto">
            <button class="btn btn-success btn-sm me-2" onclick="window.print()">
                <i class="fas fa-print me-1"></i>Imprimir
            </button>
            <button class="btn btn-primary btn-sm me-2"
                data-download-type="excel"
                data-service-id="<?= $service['id'] ?>"
                data-url="<?= base_url('reports/export-excel/' . $service['id'] . '/extralaboral') ?>">
                <i class="fas fa-file-excel me-1"></i>Excel
            </button>
            <a href="<?= base_url('reports/export-pdf/' . $service['id'] . '/extralaboral') ?>" class="btn btn-danger btn-sm me-2">
                <i class="fas fa-file-pdf me-1"></i>PDF Completo
            </a>
            <a href="<?= base_url('reports/extralaboral/executive/' . $service['id']) ?>" class="btn btn-warning btn-sm">
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
                    <span class="badge bg-primary" style="font-size: 1rem; padding: 10px 20px;">
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

        <!-- Filtros de Riesgo Psicosocial -->
        <div class="alert alert-light border mb-3">
            <h6 class="fw-bold mb-2 small"><i class="fas fa-exclamation-triangle text-danger me-2"></i>Filtros de Riesgo Psicosocial</h6>
            <div class="row">
                <!-- Nivel de Riesgo -->
                <div class="col-md-3">
                    <div class="filter-group">
                        <label><i class="fas fa-chart-line me-1"></i>Nivel de Riesgo</label>
                        <select class="form-select form-select-sm" id="filter_risk_level">
                            <option value="">Todos</option>
                            <?php foreach ($segmentadores['niveles_riesgo'] as $nivel): ?>
                                <option value="<?= $nivel ?>"><?= getRiskLabel($nivel) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Dimensión -->
                <div class="col-md-3">
                    <div class="filter-group">
                        <label><i class="fas fa-layer-group me-1"></i>Dimensión</label>
                        <select class="form-select form-select-sm" id="filter_dimension">
                            <option value="">Todas las dimensiones</option>
                            <option value="dim_tiempo_fuera_trabajo">Tiempo fuera del trabajo</option>
                            <option value="dim_relaciones_familiares">Relaciones familiares</option>
                            <option value="dim_comunicacion_relaciones_interpersonales">Comunicación y relaciones</option>
                            <option value="dim_situacion_economica_grupo_familiar">Situación económica familiar</option>
                            <option value="dim_caracteristicas_vivienda_entorno">Vivienda y entorno</option>
                            <option value="dim_influencia_entorno_extralaboral">Influencia entorno extralaboral</option>
                            <option value="dim_desplazamiento_vivienda_trabajo">Desplazamiento vivienda-trabajo</option>
                        </select>
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

    <!-- Estadísticas Generales - Total Extralaboral -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1" style="font-size: 0.85rem; opacity: 0.9;">Total Extralaboral</h6>
                        <h2 class="fw-bold mb-0"
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            title="Puntaje: <?= number_format($stats['extralaboralTotal'], 1) ?>"
                            style="cursor: help;">
                            <?= $stats['extralaboralTotalLabel'] ?? 'Sin datos' ?>
                        </h2>
                        <p class="mb-0 small mt-1" style="opacity: 0.8;">
                            <i class="fas fa-users me-1"></i><?= $totalWorkers ?> trabajadores evaluados
                        </p>
                    </div>
                    <i class="fas fa-home" style="font-size: 3rem; opacity: 0.6;"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Distribución por Nivel de Riesgo -->
    <h6 class="section-title">Distribución por Nivel de Riesgo</h6>
    <div class="row mb-3">
        <?php
        $riskCards = [
            ['nivel' => 'sin_riesgo', 'label' => 'SIN RIESGO', 'color' => '#28a745'],
            ['nivel' => 'riesgo_bajo', 'label' => 'RIESGO BAJO', 'color' => '#28a745'],
            ['nivel' => 'riesgo_medio', 'label' => 'RIESGO MEDIO', 'color' => '#ffc107'],
            ['nivel' => 'riesgo_alto', 'label' => 'RIESGO ALTO', 'color' => '#dc3545'],
            ['nivel' => 'riesgo_muy_alto', 'label' => 'RIESGO MUY ALTO', 'color' => '#dc3545']
        ];

        foreach ($riskCards as $card):
            $count = $stats['riskDistribution'][$card['nivel']] ?? 0;
            $textColor = ($card['nivel'] === 'riesgo_medio') ? '#333' : '#fff';
        ?>
        <div class="col-md-6 col-lg-2 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center rounded" style="background-color: <?= $card['color'] ?>; color: <?= $textColor ?>;">
                    <h6 class="mb-2 text-uppercase" style="font-size: 0.7rem; font-weight: 600; letter-spacing: 0.5px;"><?= $card['label'] ?></h6>
                    <h1 class="fw-bold mb-1" style="font-size: 3rem;" data-stat-risk="<?= $card['nivel'] ?>"><?= $count ?></h1>
                    <small style="font-size: 0.75rem;">trabajadores</small>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- 7 Dimensiones Extralaborales - Acordeón por Forma -->
    <?php
    // Función para calcular promedio y nivel de una dimensión extralaboral
    function calcularDimensionExtra($results, $dbKey, $formType = null) {
        $suma = 0;
        $count = 0;
        $nivelCounts = ['sin_riesgo' => 0, 'riesgo_bajo' => 0, 'riesgo_medio' => 0, 'riesgo_alto' => 0, 'riesgo_muy_alto' => 0];

        foreach ($results as $r) {
            // Filtrar por tipo de forma si se especifica
            if ($formType !== null && ($r['intralaboral_form_type'] ?? '') !== $formType) {
                continue;
            }

            $puntajeKey = $dbKey . '_puntaje';
            $nivelKey = $dbKey . '_nivel';

            if (isset($r[$puntajeKey]) && $r[$puntajeKey] !== null && $r[$puntajeKey] !== '') {
                $suma += floatval($r[$puntajeKey]);
                $count++;
            }

            if (isset($r[$nivelKey]) && isset($nivelCounts[$r[$nivelKey]])) {
                $nivelCounts[$r[$nivelKey]]++;
            }
        }

        $promedio = $count > 0 ? $suma / $count : 0;

        // Determinar nivel predominante
        arsort($nivelCounts);
        $nivelPredominante = key($nivelCounts);
        if (array_sum($nivelCounts) === 0) {
            $nivelPredominante = 'sin_riesgo';
        }

        return ['promedio' => $promedio, 'nivel' => $nivelPredominante, 'count' => $count];
    }

    // Las 7 dimensiones extralaborales con nombres de columnas BD correctos
    $dimensionesExtra = [
        ['db_key' => 'extralaboral_tiempo_fuera', 'label' => 'Tiempo fuera del trabajo', 'icon' => 'clock'],
        ['db_key' => 'extralaboral_relaciones_familiares', 'label' => 'Relaciones familiares', 'icon' => 'users'],
        ['db_key' => 'extralaboral_comunicacion_relaciones', 'label' => 'Comunicación y relaciones interpersonales', 'icon' => 'comments'],
        ['db_key' => 'extralaboral_situacion_economica', 'label' => 'Situación económica del grupo familiar', 'icon' => 'dollar-sign'],
        ['db_key' => 'extralaboral_vivienda_entorno', 'label' => 'Características de la vivienda y su entorno', 'icon' => 'home'],
        ['db_key' => 'extralaboral_influencia_entorno', 'label' => 'Influencia del entorno extralaboral sobre el trabajo', 'icon' => 'arrows-alt'],
        ['db_key' => 'extralaboral_desplazamiento', 'label' => 'Desplazamiento vivienda-trabajo-vivienda', 'icon' => 'car']
    ];

    // Contar trabajadores por forma
    $countExtraFormaA = 0;
    $countExtraFormaB = 0;
    foreach ($results as $r) {
        if (($r['intralaboral_form_type'] ?? '') === 'A') $countExtraFormaA++;
        if (($r['intralaboral_form_type'] ?? '') === 'B') $countExtraFormaB++;
    }
    ?>

    <div class="accordion mb-4" id="accordionDimensionesExtra">
        <!-- Forma A -->
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingExtraFormaA">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExtraFormaA" aria-expanded="true" aria-controls="collapseExtraFormaA">
                    <i class="fas fa-th-large me-2 text-primary"></i>
                    <strong>Dimensiones Extralaborales - Forma A</strong>
                    <span class="badge bg-primary ms-2">7 dimensiones</span>
                    <span class="badge bg-secondary ms-2"><?= $countExtraFormaA ?> trabajadores</span>
                </button>
            </h2>
            <div id="collapseExtraFormaA" class="accordion-collapse collapse show" aria-labelledby="headingExtraFormaA" data-bs-parent="#accordionDimensionesExtra">
                <div class="accordion-body">
                    <?php if ($countExtraFormaA > 0): ?>
                    <div class="row">
                        <?php foreach ($dimensionesExtra as $dim):
                            $calc = calcularDimensionExtra($results, $dim['db_key'], 'A');
                        ?>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card dimension-card">
                                <div class="card-body">
                                    <div class="dimension-label">
                                        <i class="fas fa-<?= $dim['icon'] ?> me-2"></i><?= $dim['label'] ?>
                                    </div>
                                    <div class="dimension-value text-primary">
                                        <?= number_format($calc['promedio'], 1) ?>%
                                    </div>
                                    <span class="risk-badge" style="<?= getBadgeClass($calc['nivel']) ?>">
                                        <?= getRiskLabel($calc['nivel']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>No hay trabajadores evaluados con Forma A
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Forma B -->
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingExtraFormaB">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExtraFormaB" aria-expanded="false" aria-controls="collapseExtraFormaB">
                    <i class="fas fa-th-large me-2 text-warning"></i>
                    <strong>Dimensiones Extralaborales - Forma B</strong>
                    <span class="badge bg-warning text-dark ms-2">7 dimensiones</span>
                    <span class="badge bg-secondary ms-2"><?= $countExtraFormaB ?> trabajadores</span>
                </button>
            </h2>
            <div id="collapseExtraFormaB" class="accordion-collapse collapse" aria-labelledby="headingExtraFormaB" data-bs-parent="#accordionDimensionesExtra">
                <div class="accordion-body">
                    <?php if ($countExtraFormaB > 0): ?>
                    <div class="row">
                        <?php foreach ($dimensionesExtra as $dim):
                            $calc = calcularDimensionExtra($results, $dim['db_key'], 'B');
                        ?>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card dimension-card">
                                <div class="card-body">
                                    <div class="dimension-label">
                                        <i class="fas fa-<?= $dim['icon'] ?> me-2"></i><?= $dim['label'] ?>
                                    </div>
                                    <div class="dimension-value text-warning">
                                        <?= number_format($calc['promedio'], 1) ?>%
                                    </div>
                                    <span class="risk-badge" style="<?= getBadgeClass($calc['nivel']) ?>">
                                        <?= getRiskLabel($calc['nivel']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>No hay trabajadores evaluados con Forma B
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficas -->
    <h6 class="section-title">
        <i class="fas fa-chart-pie me-2"></i>Análisis Visual
    </h6>

    <div class="row mb-4">
        <!-- Distribución de Riesgo -->
        <div class="col-lg-6 mb-3">
            <div class="card">
                <div class="card-header-custom">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-pie me-2"></i>Distribución por Nivel de Riesgo
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="chartRiskDistribution"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dimensiones Bar Chart -->
        <div class="col-lg-6 mb-3">
            <div class="card">
                <div class="card-header-custom">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Nivel de Riesgo por Dimensión
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="chartDimensions"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Género Distribution -->
        <div class="col-lg-6 mb-3">
            <div class="card">
                <div class="card-header-custom">
                    <h6 class="mb-0">
                        <i class="fas fa-venus-mars me-2"></i>Distribución por Género
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="chartGender"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top 5 Dimensiones Críticas -->
        <div class="col-lg-6 mb-3">
            <div class="card">
                <div class="card-header-custom">
                    <h6 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>Top 5 Dimensiones Críticas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="chartTopDimensions"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top 10 Departamentos con Mayor Riesgo -->
    <?php if (!empty($segmentadores['departamentos']) && count($segmentadores['departamentos']) > 1): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header-custom">
                    <h6 class="mb-0">
                        <i class="fas fa-sitemap me-2"></i>Top 10 Departamentos con Mayor Riesgo
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height: 400px;">
                        <canvas id="chartTopDepartments"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Tabla de Resultados Detallados -->
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
                            <th>Nivel Riesgo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $result): ?>
                        <tr data-worker-id="<?= $result['worker_id'] ?>"
                            data-gender="<?= esc($result['gender'] ?? '') ?>"
                            data-department="<?= esc($result['department'] ?? '') ?>"
                            data-position="<?= esc($result['position'] ?? '') ?>"
                            data-position-type="<?= esc($result['position_type'] ?? '') ?>"
                            data-contract-type="<?= esc($result['contract_type'] ?? '') ?>"
                            data-education-level="<?= esc($result['education_level'] ?? '') ?>"
                            data-marital-status="<?= esc($result['marital_status'] ?? '') ?>"
                            data-stratum="<?= esc($result['stratum'] ?? '') ?>"
                            data-housing-type="<?= esc($result['housing_type'] ?? '') ?>"
                            data-time-in-company="<?= esc($result['time_in_company_type'] ?? '') ?>"
                            data-risk-level="<?= esc($result['extralaboral_total_nivel'] ?? 'sin_riesgo') ?>">
                            <td><?= esc($result['worker_name'] ?? 'N/A') ?></td>
                            <td><?= esc($result['worker_document'] ?? 'N/A') ?></td>
                            <td><?= esc($result['gender'] ?? 'N/A') ?></td>
                            <td><?= esc($result['department'] ?? 'N/A') ?></td>
                            <td><?= esc($result['position'] ?? 'N/A') ?></td>
                            <td><?= esc($result['position_type'] ?? 'N/A') ?></td>
                            <td><strong><?= number_format($result['extralaboral_total_puntaje'] ?? 0, 1) ?>%</strong></td>
                            <td>
                                <span class="risk-badge" style="<?= getBadgeClass($result['extralaboral_total_nivel'] ?? 'sin_riesgo') ?>">
                                    <?= getRiskLabel($result['extralaboral_total_nivel'] ?? 'sin_riesgo') ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $userRole = session()->get('role_name');
                                if (in_array($userRole, ['superadmin', 'admin', 'consultor'])): ?>
                                    <a href="<?= base_url("workers/results/{$result['worker_id']}") ?>"
                                       class="btn btn-sm btn-success" title="Ver resultados individuales"
                                       target="_blank">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
                                <?php else: ?>
                                    <a href="<?= base_url("individual-results/request/{$serviceId}/{$result['worker_id']}/extralaboral") ?>"
                                       class="btn btn-sm btn-primary" title="Solicitar acceso a resultados individuales">
                                        <i class="fas fa-lock"></i> Solicitar
                                    </a>
                                <?php endif; ?>
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
const userRole = '<?= session()->get('role_name') ?>';
const isConsultant = ['superadmin', 'admin', 'consultor'].includes(userRole);
let filteredResults = [...allResults];

// DEBUG: Verificar estructura de datos
console.log('=== DATOS CARGADOS ===');
console.log('Total results:', allResults.length);
if (allResults.length > 0) {
    console.log('Primer resultado (todas las propiedades):', allResults[0]);

    // Verificar campos demográficos
    console.log('Campos demográficos del primer resultado:');
    console.log('  - gender:', allResults[0].gender);
    console.log('  - department:', allResults[0].department);
    console.log('  - position:', allResults[0].position);
    console.log('  - position_type:', allResults[0].position_type);
    console.log('  - contract_type:', allResults[0].contract_type);
    console.log('  - education_level:', allResults[0].education_level);
    console.log('  - marital_status:', allResults[0].marital_status);
    console.log('  - city_residence:', allResults[0].city_residence);
    console.log('  - stratum:', allResults[0].stratum);
    console.log('  - housing_type:', allResults[0].housing_type);
    console.log('  - time_in_company_type:', allResults[0].time_in_company_type);

    // Verificar valores únicos
    console.log('Valores únicos:');
    console.log('  - Géneros:', [...new Set(allResults.map(r => r.gender))]);
    console.log('  - Departamentos:', [...new Set(allResults.map(r => r.department))]);
    console.log('  - Ciudades:', [...new Set(allResults.map(r => r.city_residence))]);
}
console.log('=== FIN DEBUG INICIAL ===');

// Colores de riesgo
const riskColors = {
    'sin_riesgo': '#28a745',    // Verde oscuro
    'riesgo_bajo': '#28a745',   // Verde oscuro
    'riesgo_medio': '#ffc107',  // Amarillo
    'riesgo_alto': '#dc3545',   // Rojo intenso
    'riesgo_muy_alto': '#dc3545' // Rojo intenso
};

// Mapeo entre nombres de dimensiones en filtros y nombres en la BD
const dimensionMapping = {
    'dim_tiempo_fuera_trabajo': 'extralaboral_tiempo_fuera',
    'dim_relaciones_familiares': 'extralaboral_relaciones_familiares',
    'dim_comunicacion_relaciones_interpersonales': 'extralaboral_comunicacion',
    'dim_situacion_economica_grupo_familiar': 'extralaboral_situacion_economica',
    'dim_caracteristicas_vivienda_entorno': 'extralaboral_caracteristicas_vivienda',
    'dim_influencia_entorno_extralaboral': 'extralaboral_influencia_entorno',
    'dim_desplazamiento_vivienda_trabajo': 'extralaboral_desplazamiento'
};

// Helper functions para badges y labels de riesgo (versión JavaScript)
function getBadgeClass(nivel) {
    const styles = {
        'sin_riesgo': 'background-color: #28a745; color: white;',
        'riesgo_bajo': 'background-color: #28a745; color: white;',
        'riesgo_medio': 'background-color: #ffc107; color: #333;',
        'riesgo_alto': 'background-color: #dc3545; color: white;',
        'riesgo_muy_alto': 'background-color: #dc3545; color: white;'
    };
    return styles[nivel] || 'background-color: #6c757d; color: white;';
}

function getRiskLabel(nivel) {
    const labels = {
        'sin_riesgo': 'Sin Riesgo',
        'riesgo_bajo': 'Riesgo Bajo',
        'riesgo_medio': 'Riesgo Medio',
        'riesgo_alto': 'Riesgo Alto',
        'riesgo_muy_alto': 'Riesgo Muy Alto'
    };
    return labels[nivel] || 'N/A';
}

// ============================================
// INICIALIZACIÓN DE CHARTS Y DATATABLE
// ============================================
let chartRiskDistribution, chartDimensions, chartGender, chartTopDimensions, chartTopDepartments;
let dataTable; // Variable global para DataTable

// Chart.js global config
Chart.defaults.font.family = "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif";
Chart.defaults.font.size = 11;

// Configuración común de DataLabels
const dataLabelsConfig = {
    anchor: 'end',
    align: 'end',
    formatter: (value) => value > 0 ? value : '',
    font: { weight: 'bold', size: 10 }
};

// ============================================
// FUNCIÓN: Crear Chart de Distribución de Riesgo
// ============================================
function createRiskDistributionChart(data) {
    const ctx = document.getElementById('chartRiskDistribution').getContext('2d');

    if (chartRiskDistribution) {
        chartRiskDistribution.destroy();
    }

    const riskCounts = {
        'sin_riesgo': 0,
        'riesgo_bajo': 0,
        'riesgo_medio': 0,
        'riesgo_alto': 0,
        'riesgo_muy_alto': 0
    };

    data.forEach(r => {
        const nivel = r.extralaboral_total_nivel || 'sin_riesgo';
        riskCounts[nivel]++;
    });

    chartRiskDistribution = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Sin Riesgo', 'Riesgo Bajo', 'Riesgo Medio', 'Riesgo Alto', 'Riesgo Muy Alto'],
            datasets: [{
                data: [
                    riskCounts.sin_riesgo,
                    riskCounts.riesgo_bajo,
                    riskCounts.riesgo_medio,
                    riskCounts.riesgo_alto,
                    riskCounts.riesgo_muy_alto
                ],
                backgroundColor: Object.values(riskColors),
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { padding: 15, font: { size: 11 } }
                },
                datalabels: {
                    color: '#fff',
                    font: { weight: 'bold', size: 13 },
                    formatter: (value) => value > 0 ? value : ''
                }
            }
        },
        plugins: [ChartDataLabels]
    });
}

// ============================================
// FUNCIÓN: Crear Chart de Dimensiones
// ============================================
function createDimensionsChart(data) {
    const ctx = document.getElementById('chartDimensions').getContext('2d');

    if (chartDimensions) {
        chartDimensions.destroy();
    }

    // Baremos extralaborales oficiales (Resolución 2404/2019 - Tabla 18: Auxiliares)
    const extralaboralBaremos = {
        'dim_tiempo_fuera_trabajo': [
            { min: 0.0, max: 6.3, nivel: 'sin_riesgo', label: 'Sin Riesgo' },
            { min: 6.4, max: 25.0, nivel: 'riesgo_bajo', label: 'Riesgo Bajo' },
            { min: 25.1, max: 37.5, nivel: 'riesgo_medio', label: 'Riesgo Medio' },
            { min: 37.6, max: 50.0, nivel: 'riesgo_alto', label: 'Riesgo Alto' },
            { min: 50.1, max: 100, nivel: 'riesgo_muy_alto', label: 'Riesgo Muy Alto' }
        ],
        'dim_relaciones_familiares': [
            { min: 0.0, max: 8.3, nivel: 'sin_riesgo', label: 'Sin Riesgo' },
            { min: 8.4, max: 25.0, nivel: 'riesgo_bajo', label: 'Riesgo Bajo' },
            { min: 25.1, max: 33.3, nivel: 'riesgo_medio', label: 'Riesgo Medio' },
            { min: 33.4, max: 50.0, nivel: 'riesgo_alto', label: 'Riesgo Alto' },
            { min: 50.1, max: 100, nivel: 'riesgo_muy_alto', label: 'Riesgo Muy Alto' }
        ],
        'dim_comunicacion_relaciones_interpersonales': [
            { min: 0.0, max: 5.0, nivel: 'sin_riesgo', label: 'Sin Riesgo' },
            { min: 5.1, max: 15.0, nivel: 'riesgo_bajo', label: 'Riesgo Bajo' },
            { min: 15.1, max: 25.0, nivel: 'riesgo_medio', label: 'Riesgo Medio' },
            { min: 25.1, max: 35.0, nivel: 'riesgo_alto', label: 'Riesgo Alto' },
            { min: 35.1, max: 100, nivel: 'riesgo_muy_alto', label: 'Riesgo Muy Alto' }
        ],
        'dim_situacion_economica_grupo_familiar': [
            { min: 0.0, max: 16.7, nivel: 'sin_riesgo', label: 'Sin Riesgo' },
            { min: 16.8, max: 25.0, nivel: 'riesgo_bajo', label: 'Riesgo Bajo' },
            { min: 25.1, max: 41.7, nivel: 'riesgo_medio', label: 'Riesgo Medio' },
            { min: 41.8, max: 50.0, nivel: 'riesgo_alto', label: 'Riesgo Alto' },
            { min: 50.1, max: 100, nivel: 'riesgo_muy_alto', label: 'Riesgo Muy Alto' }
        ],
        'dim_caracteristicas_vivienda_entorno': [
            { min: 0.0, max: 5.6, nivel: 'sin_riesgo', label: 'Sin Riesgo' },
            { min: 5.7, max: 11.1, nivel: 'riesgo_bajo', label: 'Riesgo Bajo' },
            { min: 11.2, max: 16.7, nivel: 'riesgo_medio', label: 'Riesgo Medio' },
            { min: 16.8, max: 27.8, nivel: 'riesgo_alto', label: 'Riesgo Alto' },
            { min: 27.9, max: 100, nivel: 'riesgo_muy_alto', label: 'Riesgo Muy Alto' }
        ],
        'dim_influencia_entorno_extralaboral': [
            { min: 0.0, max: 0.9, nivel: 'sin_riesgo', label: 'Sin Riesgo' },
            { min: 1.0, max: 16.7, nivel: 'riesgo_bajo', label: 'Riesgo Bajo' },
            { min: 16.8, max: 25.0, nivel: 'riesgo_medio', label: 'Riesgo Medio' },
            { min: 25.1, max: 41.7, nivel: 'riesgo_alto', label: 'Riesgo Alto' },
            { min: 41.8, max: 100, nivel: 'riesgo_muy_alto', label: 'Riesgo Muy Alto' }
        ],
        'dim_desplazamiento_vivienda_trabajo': [
            { min: 0.0, max: 0.9, nivel: 'sin_riesgo', label: 'Sin Riesgo' },
            { min: 1.0, max: 12.5, nivel: 'riesgo_bajo', label: 'Riesgo Bajo' },
            { min: 12.6, max: 25.0, nivel: 'riesgo_medio', label: 'Riesgo Medio' },
            { min: 25.1, max: 43.8, nivel: 'riesgo_alto', label: 'Riesgo Alto' },
            { min: 43.9, max: 100, nivel: 'riesgo_muy_alto', label: 'Riesgo Muy Alto' }
        ]
    };

    // Función para obtener nivel de riesgo según baremo
    const getRiskLevelFromScore = (score, dimension) => {
        const baremo = extralaboralBaremos[dimension];
        if (!baremo) return { nivel: 'sin_riesgo', label: 'Sin Riesgo' };

        for (let range of baremo) {
            if (score >= range.min && score <= range.max) {
                return { nivel: range.nivel, label: range.label };
            }
        }
        return { nivel: 'sin_riesgo', label: 'Sin Riesgo' };
    };

    // Calcular promedios y niveles de riesgo de las 7 dimensiones
    const dimensionKeys = [
        'dim_tiempo_fuera_trabajo',
        'dim_relaciones_familiares',
        'dim_comunicacion_relaciones_interpersonales',
        'dim_situacion_economica_grupo_familiar',
        'dim_caracteristicas_vivienda_entorno',
        'dim_influencia_entorno_extralaboral',
        'dim_desplazamiento_vivienda_trabajo'
    ];

    const dimensionData = [];
    const dimensionColors = [];
    const dimensionLabels = [];

    if (data.length > 0) {
        dimensionKeys.forEach(dim => {
            const dbColumnName = dimensionMapping[dim];

            // Calcular promedio de puntaje para esta dimensión
            let sum = 0;
            let count = 0;

            data.forEach(r => {
                const puntaje = parseFloat(r[dbColumnName + '_puntaje'] || 0);
                sum += puntaje;
                count++;
            });

            // Calcular promedio
            const average = count > 0 ? sum / count : 0;

            // Determinar nivel de riesgo basado en el PUNTAJE PROMEDIO usando baremos oficiales
            const riskLevel = getRiskLevelFromScore(average, dim);

            // Usar el puntaje promedio como altura de la barra
            dimensionData.push(average);

            // Color basado en el nivel de riesgo del puntaje promedio
            dimensionColors.push(riskColors[riskLevel.nivel]);

            // Label del nivel de riesgo
            dimensionLabels.push(riskLevel.label);
        });
    }

    const labels = [
        'Tiempo fuera\ndel trabajo',
        'Relaciones\nfamiliares',
        'Comunicación y\nrelaciones',
        'Situación\neconómica',
        'Vivienda y\nentorno',
        'Influencia\nextralaboral',
        'Desplazamiento'
    ];

    chartDimensions = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Nivel de Riesgo',
                data: dimensionData,
                backgroundColor: dimensionColors,
                borderColor: dimensionColors,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: (value) => value + '%',
                        font: { size: 10 }
                    },
                    grid: {
                        display: true,
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                }
            },
            plugins: {
                legend: { display: false },
                datalabels: {
                    anchor: 'end',
                    align: 'top',
                    formatter: (value, context) => {
                        return dimensionLabels[context.dataIndex];
                    },
                    color: '#333',
                    font: {
                        weight: 'bold',
                        size: 10
                    },
                    backgroundColor: 'rgba(255, 255, 255, 0.8)',
                    borderRadius: 4,
                    padding: 4
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return dimensionLabels[context.dataIndex] + ': ' + context.parsed.y.toFixed(1) + '%';
                        }
                    }
                }
            }
        },
        plugins: [ChartDataLabels]
    });
}

// ============================================
// FUNCIÓN: Crear Chart de Género
// ============================================
function createGenderChart(data) {
    const ctx = document.getElementById('chartGender').getContext('2d');

    if (chartGender) {
        chartGender.destroy();
    }

    const genderCounts = {};
    data.forEach(r => {
        const gender = r.gender || 'No especificado';
        genderCounts[gender] = (genderCounts[gender] || 0) + 1;
    });

    chartGender = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: Object.keys(genderCounts),
            datasets: [{
                data: Object.values(genderCounts),
                backgroundColor: ['#667eea', '#764ba2', '#f093fb', '#4facfe'],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { padding: 15, font: { size: 11 } }
                },
                datalabels: {
                    color: '#fff',
                    font: { weight: 'bold', size: 13 },
                    formatter: (value, ctx) => {
                        const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((value / total) * 100).toFixed(1);
                        return value > 0 ? `${value}\n(${percentage}%)` : '';
                    }
                }
            }
        },
        plugins: [ChartDataLabels]
    });
}

// ============================================
// FUNCIÓN: Crear Chart Top 5 Dimensiones Críticas
// ============================================
function createTopDimensionsChart(data) {
    const ctx = document.getElementById('chartTopDimensions').getContext('2d');

    if (chartTopDimensions) {
        chartTopDimensions.destroy();
    }

    // Baremos extralaborales para determinar niveles de riesgo
    const extralaboralBaremos = {
        'dim_tiempo_fuera_trabajo': [
            { min: 0.0, max: 6.3, nivel: 'sin_riesgo', label: 'Sin Riesgo' },
            { min: 6.4, max: 25.0, nivel: 'riesgo_bajo', label: 'Riesgo Bajo' },
            { min: 25.1, max: 37.5, nivel: 'riesgo_medio', label: 'Riesgo Medio' },
            { min: 37.6, max: 50.0, nivel: 'riesgo_alto', label: 'Riesgo Alto' },
            { min: 50.1, max: 100, nivel: 'riesgo_muy_alto', label: 'Riesgo Muy Alto' }
        ],
        'dim_relaciones_familiares': [
            { min: 0.0, max: 8.3, nivel: 'sin_riesgo', label: 'Sin Riesgo' },
            { min: 8.4, max: 25.0, nivel: 'riesgo_bajo', label: 'Riesgo Bajo' },
            { min: 25.1, max: 33.3, nivel: 'riesgo_medio', label: 'Riesgo Medio' },
            { min: 33.4, max: 50.0, nivel: 'riesgo_alto', label: 'Riesgo Alto' },
            { min: 50.1, max: 100, nivel: 'riesgo_muy_alto', label: 'Riesgo Muy Alto' }
        ],
        'dim_comunicacion_relaciones_interpersonales': [
            { min: 0.0, max: 5.0, nivel: 'sin_riesgo', label: 'Sin Riesgo' },
            { min: 5.1, max: 15.0, nivel: 'riesgo_bajo', label: 'Riesgo Bajo' },
            { min: 15.1, max: 25.0, nivel: 'riesgo_medio', label: 'Riesgo Medio' },
            { min: 25.1, max: 35.0, nivel: 'riesgo_alto', label: 'Riesgo Alto' },
            { min: 35.1, max: 100, nivel: 'riesgo_muy_alto', label: 'Riesgo Muy Alto' }
        ],
        'dim_situacion_economica_grupo_familiar': [
            { min: 0.0, max: 16.7, nivel: 'sin_riesgo', label: 'Sin Riesgo' },
            { min: 16.8, max: 25.0, nivel: 'riesgo_bajo', label: 'Riesgo Bajo' },
            { min: 25.1, max: 41.7, nivel: 'riesgo_medio', label: 'Riesgo Medio' },
            { min: 41.8, max: 50.0, nivel: 'riesgo_alto', label: 'Riesgo Alto' },
            { min: 50.1, max: 100, nivel: 'riesgo_muy_alto', label: 'Riesgo Muy Alto' }
        ],
        'dim_caracteristicas_vivienda_entorno': [
            { min: 0.0, max: 5.6, nivel: 'sin_riesgo', label: 'Sin Riesgo' },
            { min: 5.7, max: 11.1, nivel: 'riesgo_bajo', label: 'Riesgo Bajo' },
            { min: 11.2, max: 16.7, nivel: 'riesgo_medio', label: 'Riesgo Medio' },
            { min: 16.8, max: 27.8, nivel: 'riesgo_alto', label: 'Riesgo Alto' },
            { min: 27.9, max: 100, nivel: 'riesgo_muy_alto', label: 'Riesgo Muy Alto' }
        ],
        'dim_influencia_entorno_extralaboral': [
            { min: 0.0, max: 0.9, nivel: 'sin_riesgo', label: 'Sin Riesgo' },
            { min: 1.0, max: 16.7, nivel: 'riesgo_bajo', label: 'Riesgo Bajo' },
            { min: 16.8, max: 25.0, nivel: 'riesgo_medio', label: 'Riesgo Medio' },
            { min: 25.1, max: 41.7, nivel: 'riesgo_alto', label: 'Riesgo Alto' },
            { min: 41.8, max: 100, nivel: 'riesgo_muy_alto', label: 'Riesgo Muy Alto' }
        ],
        'dim_desplazamiento_vivienda_trabajo': [
            { min: 0.0, max: 0.9, nivel: 'sin_riesgo', label: 'Sin Riesgo' },
            { min: 1.0, max: 12.5, nivel: 'riesgo_bajo', label: 'Riesgo Bajo' },
            { min: 12.6, max: 25.0, nivel: 'riesgo_medio', label: 'Riesgo Medio' },
            { min: 25.1, max: 43.8, nivel: 'riesgo_alto', label: 'Riesgo Alto' },
            { min: 43.9, max: 100, nivel: 'riesgo_muy_alto', label: 'Riesgo Muy Alto' }
        ]
    };

    const getRiskLevelFromScore = (score, dimensionKey) => {
        const baremo = extralaboralBaremos[dimensionKey];
        if (!baremo) return { nivel: 'sin_riesgo', label: 'Sin Riesgo' };

        for (let range of baremo) {
            if (score >= range.min && score <= range.max) {
                return { nivel: range.nivel, label: range.label };
            }
        }
        return { nivel: 'sin_riesgo', label: 'Sin Riesgo' };
    };

    // Calcular promedios y ordenar
    const dimensions = {
        'Tiempo fuera del trabajo': 0,
        'Relaciones familiares': 0,
        'Comunicación y relaciones': 0,
        'Situación económica': 0,
        'Vivienda y entorno': 0,
        'Influencia extralaboral': 0,
        'Desplazamiento': 0
    };

    const dimKeys = [
        'dim_tiempo_fuera_trabajo',
        'dim_relaciones_familiares',
        'dim_comunicacion_relaciones_interpersonales',
        'dim_situacion_economica_grupo_familiar',
        'dim_caracteristicas_vivienda_entorno',
        'dim_influencia_entorno_extralaboral',
        'dim_desplazamiento_vivienda_trabajo'
    ];

    const dimScores = {};
    if (data.length > 0) {
        dimKeys.forEach((key, index) => {
            let sum = 0;
            const dbColumnName = dimensionMapping[key];
            data.forEach(r => {
                sum += parseFloat(r[dbColumnName + '_puntaje'] || 0);
            });
            const label = Object.keys(dimensions)[index];
            const avg = sum / data.length;
            const riskLevel = getRiskLevelFromScore(avg, key);

            dimScores[label] = {
                score: avg,
                nivel: riskLevel.nivel,
                label: riskLevel.label
            };
        });
    }

    // Ordenar por score y tomar top 5
    const sorted = Object.entries(dimScores)
        .sort((a, b) => b[1].score - a[1].score)
        .slice(0, 5);

    const labels = sorted.map(s => s[0]);
    const values = sorted.map(s => s[1].score);
    const riskLabels = sorted.map(s => s[1].label);
    const colors = sorted.map(s => riskColors[s[1].nivel]);

    chartTopDimensions = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Nivel de Riesgo',
                data: values,
                backgroundColor: colors,
                borderColor: colors,
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: (value) => value + '%',
                        font: { size: 10 }
                    }
                }
            },
            plugins: {
                legend: { display: false },
                datalabels: {
                    anchor: 'end',
                    align: 'end',
                    formatter: (value, context) => {
                        return riskLabels[context.dataIndex];
                    },
                    color: '#fff',
                    font: { weight: 'bold', size: 10 },
                    backgroundColor: 'rgba(0, 0, 0, 0.3)',
                    borderRadius: 4,
                    padding: 4
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return riskLabels[context.dataIndex] + ': ' + context.parsed.x.toFixed(1) + '%';
                        }
                    }
                }
            }
        },
        plugins: [ChartDataLabels]
    });
}

// ============================================
// FUNCIÓN: Crear Chart Top 10 Departamentos
// ============================================
<?php if (!empty($segmentadores['departamentos']) && count($segmentadores['departamentos']) > 1): ?>
function createTopDepartmentsChart(data) {
    const ctx = document.getElementById('chartTopDepartments').getContext('2d');

    if (chartTopDepartments) {
        chartTopDepartments.destroy();
    }

    const deptScores = {};
    data.forEach(r => {
        const dept = r.department || 'No especificado';
        if (!deptScores[dept]) {
            deptScores[dept] = { sum: 0, count: 0 };
        }
        deptScores[dept].sum += parseFloat(r.extralaboral_total_puntaje || 0);
        deptScores[dept].count++;
    });

    // Baremo para total extralaboral (Tabla 18: Auxiliares)
    const getTotalRiskLevel = (puntaje) => {
        if (puntaje <= 12.9) return { nivel: 'sin_riesgo', label: 'Sin Riesgo' };
        if (puntaje <= 17.7) return { nivel: 'riesgo_bajo', label: 'Riesgo Bajo' };
        if (puntaje <= 24.2) return { nivel: 'riesgo_medio', label: 'Riesgo Medio' };
        if (puntaje <= 32.3) return { nivel: 'riesgo_alto', label: 'Riesgo Alto' };
        return { nivel: 'riesgo_muy_alto', label: 'Riesgo Muy Alto' };
    };

    // Calcular promedios y ordenar
    const deptAverages = Object.entries(deptScores).map(([dept, data]) => {
        const avg = data.sum / data.count;
        const riskLevel = getTotalRiskLevel(avg);
        return {
            dept: dept,
            avg: avg,
            nivel: riskLevel.nivel,
            label: riskLevel.label
        };
    }).sort((a, b) => b.avg - a.avg).slice(0, 10);

    const labels = deptAverages.map(d => d.dept);
    const values = deptAverages.map(d => d.avg);
    const riskLabels = deptAverages.map(d => d.label);
    const colors = deptAverages.map(d => riskColors[d.nivel]);

    chartTopDepartments = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Nivel de Riesgo',
                data: values,
                backgroundColor: colors,
                borderColor: colors,
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: (value) => value + '%',
                        font: { size: 10 }
                    }
                }
            },
            plugins: {
                legend: { display: false },
                datalabels: {
                    anchor: 'end',
                    align: 'end',
                    formatter: (value, context) => {
                        return riskLabels[context.dataIndex];
                    },
                    color: '#fff',
                    font: { weight: 'bold', size: 10 },
                    backgroundColor: 'rgba(0, 0, 0, 0.3)',
                    borderRadius: 4,
                    padding: 4
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return riskLabels[context.dataIndex] + ': ' + context.parsed.x.toFixed(1) + '%';
                        }
                    }
                }
            }
        },
        plugins: [ChartDataLabels]
    });
}
<?php endif; ?>

// ============================================
// FUNCIÓN: Actualizar Estadísticas
// ============================================
function updateStats(data) {
    // Baremos extralaborales oficiales (Auxiliares)
    const extralaboralBaremos = {
        'total': [
            { min: 0.0, max: 12.9, nivel: 'sin_riesgo', label: 'Sin Riesgo' },
            { min: 13.0, max: 17.7, nivel: 'riesgo_bajo', label: 'Riesgo Bajo' },
            { min: 17.8, max: 24.2, nivel: 'riesgo_medio', label: 'Riesgo Medio' },
            { min: 24.3, max: 32.3, nivel: 'riesgo_alto', label: 'Riesgo Alto' },
            { min: 32.4, max: 100, nivel: 'riesgo_muy_alto', label: 'Riesgo Muy Alto' }
        ],
        'dim_tiempo_fuera_trabajo': [
            { min: 0.0, max: 6.3, nivel: 'sin_riesgo', label: 'Sin Riesgo' },
            { min: 6.4, max: 25.0, nivel: 'riesgo_bajo', label: 'Riesgo Bajo' },
            { min: 25.1, max: 37.5, nivel: 'riesgo_medio', label: 'Riesgo Medio' },
            { min: 37.6, max: 50.0, nivel: 'riesgo_alto', label: 'Riesgo Alto' },
            { min: 50.1, max: 100, nivel: 'riesgo_muy_alto', label: 'Riesgo Muy Alto' }
        ],
        'dim_relaciones_familiares': [
            { min: 0.0, max: 8.3, nivel: 'sin_riesgo', label: 'Sin Riesgo' },
            { min: 8.4, max: 25.0, nivel: 'riesgo_bajo', label: 'Riesgo Bajo' },
            { min: 25.1, max: 33.3, nivel: 'riesgo_medio', label: 'Riesgo Medio' },
            { min: 33.4, max: 50.0, nivel: 'riesgo_alto', label: 'Riesgo Alto' },
            { min: 50.1, max: 100, nivel: 'riesgo_muy_alto', label: 'Riesgo Muy Alto' }
        ],
        'dim_comunicacion_relaciones_interpersonales': [
            { min: 0.0, max: 5.0, nivel: 'sin_riesgo', label: 'Sin Riesgo' },
            { min: 5.1, max: 15.0, nivel: 'riesgo_bajo', label: 'Riesgo Bajo' },
            { min: 15.1, max: 25.0, nivel: 'riesgo_medio', label: 'Riesgo Medio' },
            { min: 25.1, max: 35.0, nivel: 'riesgo_alto', label: 'Riesgo Alto' },
            { min: 35.1, max: 100, nivel: 'riesgo_muy_alto', label: 'Riesgo Muy Alto' }
        ],
        'dim_situacion_economica_grupo_familiar': [
            { min: 0.0, max: 16.7, nivel: 'sin_riesgo', label: 'Sin Riesgo' },
            { min: 16.8, max: 25.0, nivel: 'riesgo_bajo', label: 'Riesgo Bajo' },
            { min: 25.1, max: 41.7, nivel: 'riesgo_medio', label: 'Riesgo Medio' },
            { min: 41.8, max: 50.0, nivel: 'riesgo_alto', label: 'Riesgo Alto' },
            { min: 50.1, max: 100, nivel: 'riesgo_muy_alto', label: 'Riesgo Muy Alto' }
        ],
        'dim_caracteristicas_vivienda_entorno': [
            { min: 0.0, max: 5.6, nivel: 'sin_riesgo', label: 'Sin Riesgo' },
            { min: 5.7, max: 11.1, nivel: 'riesgo_bajo', label: 'Riesgo Bajo' },
            { min: 11.2, max: 16.7, nivel: 'riesgo_medio', label: 'Riesgo Medio' },
            { min: 16.8, max: 27.8, nivel: 'riesgo_alto', label: 'Riesgo Alto' },
            { min: 27.9, max: 100, nivel: 'riesgo_muy_alto', label: 'Riesgo Muy Alto' }
        ],
        'dim_influencia_entorno_extralaboral': [
            { min: 0.0, max: 0.9, nivel: 'sin_riesgo', label: 'Sin Riesgo' },
            { min: 1.0, max: 16.7, nivel: 'riesgo_bajo', label: 'Riesgo Bajo' },
            { min: 16.8, max: 25.0, nivel: 'riesgo_medio', label: 'Riesgo Medio' },
            { min: 25.1, max: 41.7, nivel: 'riesgo_alto', label: 'Riesgo Alto' },
            { min: 41.8, max: 100, nivel: 'riesgo_muy_alto', label: 'Riesgo Muy Alto' }
        ],
        'dim_desplazamiento_vivienda_trabajo': [
            { min: 0.0, max: 0.9, nivel: 'sin_riesgo', label: 'Sin Riesgo' },
            { min: 1.0, max: 12.5, nivel: 'riesgo_bajo', label: 'Riesgo Bajo' },
            { min: 12.6, max: 25.0, nivel: 'riesgo_medio', label: 'Riesgo Medio' },
            { min: 25.1, max: 43.8, nivel: 'riesgo_alto', label: 'Riesgo Alto' },
            { min: 43.9, max: 100, nivel: 'riesgo_muy_alto', label: 'Riesgo Muy Alto' }
        ]
    };

    // Función para obtener nivel de riesgo según baremo
    const getRiskLevelFromScore = (score, dimension) => {
        const baremo = extralaboralBaremos[dimension];
        if (!baremo) return { nivel: 'sin_riesgo', label: 'Sin Riesgo' };

        for (let range of baremo) {
            if (score >= range.min && score <= range.max) {
                return { nivel: range.nivel, label: range.label };
            }
        }
        return { nivel: 'sin_riesgo', label: 'Sin Riesgo' };
    };

    // Puntaje Total Promedio
    let totalSum = 0;
    data.forEach(r => {
        totalSum += parseFloat(r.extralaboral_total_puntaje || 0);
    });
    const totalAvg = data.length > 0 ? totalSum / data.length : 0;

    // Actualizar el puntaje total y su nivel en el card grande
    const totalRiskLevel = getRiskLevelFromScore(totalAvg, 'total');
    const totalCard = document.querySelector('.stat-card h2');
    if (totalCard) {
        totalCard.textContent = totalRiskLevel.label;
        totalCard.setAttribute('title', 'Puntaje: ' + totalAvg.toFixed(1));
    }

    // Distribución de riesgos
    const riskCounts = {
        'sin_riesgo': 0,
        'riesgo_bajo': 0,
        'riesgo_medio': 0,
        'riesgo_alto': 0,
        'riesgo_muy_alto': 0
    };

    data.forEach(r => {
        const nivel = r.extralaboral_total_nivel || 'sin_riesgo';
        riskCounts[nivel]++;
    });

    Object.keys(riskCounts).forEach(nivel => {
        const elem = document.querySelector(`[data-stat-risk="${nivel}"]`);
        if (elem) {
            elem.textContent = riskCounts[nivel];
        }
    });

    // Actualizar dimensiones
    const dimensions = {
        'dim_tiempo_fuera_trabajo': 0,
        'dim_relaciones_familiares': 0,
        'dim_comunicacion_relaciones_interpersonales': 0,
        'dim_situacion_economica_grupo_familiar': 0,
        'dim_caracteristicas_vivienda_entorno': 0,
        'dim_influencia_entorno_extralaboral': 0,
        'dim_desplazamiento_vivienda_trabajo': 0
    };

    if (data.length > 0) {
        data.forEach(r => {
            Object.keys(dimensions).forEach(dim => {
                const dbColumnName = dimensionMapping[dim];
                dimensions[dim] += parseFloat(r[dbColumnName + '_puntaje'] || 0);
            });
        });

        Object.keys(dimensions).forEach(dim => {
            const avgScore = dimensions[dim] / data.length;
            const riskLevel = getRiskLevelFromScore(avgScore, dim);

            // Actualizar puntaje
            const elemPuntaje = document.querySelector(`[data-dimension="${dim}"]`);
            if (elemPuntaje) {
                elemPuntaje.textContent = avgScore.toFixed(1) + '%';
            }

            // Actualizar nivel de riesgo (badge)
            const elemNivel = document.querySelector(`[data-dimension-nivel="${dim}"]`);
            if (elemNivel) {
                elemNivel.textContent = riskLevel.label;
                elemNivel.style = getBadgeClass(riskLevel.nivel);
            }
        });
    }
}

// ============================================
// FUNCIÓN: Aplicar Filtros
// ============================================
function applyFilters() {
    const filters = {
        risk_level: $('#filter_risk_level').val(),
        dimension: $('#filter_dimension').val(),
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

    console.log('=== APPLY FILTERS DEBUG ===');
    console.log('Active filters:', filters);

    filteredResults = allResults.filter(result => {
        // Filtro de nivel de riesgo
        if (filters.risk_level && result.extralaboral_total_nivel !== filters.risk_level) {
            return false;
        }

        // Filtro de dimensión - Este filtro es especial porque filtra por nivel de riesgo en una dimensión específica
        if (filters.dimension) {
            const dbColumnName = dimensionMapping[filters.dimension];
            const dimNivel = result[dbColumnName + '_nivel'] || '';
            if (!dimNivel) return false;
        }

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
        // Convertir stratum a string para comparación consistente
        if (filters.stratum && String(result.stratum) !== String(filters.stratum)) return false;
        if (filters.housing_type && result.housing_type !== filters.housing_type) return false;
        if (filters.time_in_company && result.time_in_company_type !== filters.time_in_company) return false;

        return true;
    });

    console.log('Results after filtering:', filteredResults.length, 'of', allResults.length);
    console.log('=== END APPLY FILTERS DEBUG ===');

    // Actualizar gráficas
    updateCharts();

    // Actualizar estadísticas
    updateStats(filteredResults);

    // Filtrar tabla
    filterTable();
}

// ============================================
// FUNCIÓN: Limpiar Todos los Filtros
// ============================================
function clearAllFilters() {
    $('select[id^="filter_"]').val('');
    filteredResults = [...allResults];
    updateCharts();
    updateStats(allResults);
    filterTable();
}

// ============================================
// FUNCIÓN: Actualizar Charts
// ============================================
function updateCharts() {
    createRiskDistributionChart(filteredResults);
    createDimensionsChart(filteredResults);
    createGenderChart(filteredResults);
    createTopDimensionsChart(filteredResults);
    <?php if (!empty($segmentadores['departamentos']) && count($segmentadores['departamentos']) > 1): ?>
    createTopDepartmentsChart(filteredResults);
    <?php endif; ?>
}

// ============================================
// FUNCIÓN: Filtrar Tabla
// ============================================
function filterTable() {
    console.log('=== FILTER TABLE DEBUG ===');
    console.log('Filtering', filteredResults.length, 'of', allResults.length, 'results');

    // Limpiar tabla y reconstruir con resultados filtrados
    dataTable.clear();

    filteredResults.forEach(r => {
        // Crear nodo TR
        const $row = $('<tr>')
            .append($('<td>').text(r.worker_name || 'N/A'))
            .append($('<td>').text(r.worker_document || 'N/A'))
            .append($('<td>').text(r.gender || 'N/A'))
            .append($('<td>').text(r.department || 'N/A'))
            .append($('<td>').text(r.position || 'N/A'))
            .append($('<td>').text(r.position_type || 'N/A'))
            .append($('<td>').html('<strong>' + parseFloat(r.extralaboral_total_puntaje || 0).toFixed(1) + '%</strong>'))
            .append($('<td>').html(
                '<span class="risk-badge" style="' + getBadgeClass(r.extralaboral_total_nivel || 'sin_riesgo') + '">' +
                    getRiskLabel(r.extralaboral_total_nivel || 'sin_riesgo') +
                '</span>'
            ))
            .append($('<td>').html(
                isConsultant ?
                    '<a href="<?= base_url("workers/results/") ?>' + r.worker_id + '" class="btn btn-sm btn-success" title="Ver resultados individuales" target="_blank">' +
                        '<i class="fas fa-eye"></i> Ver' +
                    '</a>' :
                    '<a href="<?= base_url("individual-results/request/") ?><?= $serviceId ?>/' + r.worker_id + '/extralaboral" class="btn btn-sm btn-primary" title="Solicitar acceso a resultados individuales">' +
                        '<i class="fas fa-lock"></i> Solicitar' +
                    '</a>'
            ));

        dataTable.row.add($row);
    });

    // Dibujar UNA SOLA VEZ después de agregar todas las filas
    dataTable.draw(false);

    console.log('Table updated with', filteredResults.length, 'rows');
    console.log('=== END DEBUG ===');
}


// ============================================
// INICIALIZACIÓN
// ============================================
$(document).ready(function() {
    // Inicializar DataTable y guardar en variable global
    dataTable = $('#tableResults').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
        },
        pageLength: 25,
        order: [[6, 'desc']], // Ordenar por puntaje total descendente
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip'
    });

    // Crear charts iniciales
    createRiskDistributionChart(allResults);
    createDimensionsChart(allResults);
    createGenderChart(allResults);
    createTopDimensionsChart(allResults);
    <?php if (!empty($segmentadores['departamentos']) && count($segmentadores['departamentos']) > 1): ?>
    createTopDepartmentsChart(allResults);
    <?php endif; ?>

    // Event listeners para filtros de selects
    $('select[id^="filter_"]').on('change', function() {
        applyFilters();
    });
});
</script>

</body>
</html>
