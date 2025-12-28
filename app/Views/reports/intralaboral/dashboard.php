<?php
/**
 * Función helper para obtener la clase CSS del badge según el nivel de riesgo
 */
function getBadgeClass($nivel) {
    switch($nivel) {
        case 'sin_riesgo':
        case 'riesgo_bajo':
            return 'bg-success text-white';
        case 'riesgo_medio':
            return 'bg-warning text-dark';
        case 'riesgo_alto':
        case 'riesgo_muy_alto':
            return 'bg-danger text-white';
        default:
            return 'bg-secondary text-white';
    }
}

/**
 * Función helper para obtener el color del nivel de estrés
 */
function getNivelEstresColor($nivel) {
    $colores = [
        'muy_bajo' => '#28a745',    // Verde
        'bajo' => '#93C572',        // Verde claro
        'medio' => '#ffc107',       // Amarillo
        'alto' => '#fd7e14',        // Naranja
        'muy_alto' => '#dc3545'     // Rojo
    ];
    return $colores[$nivel] ?? '#6c757d';
}

/**
 * Función helper para obtener el texto del nivel de estrés
 */
function getNivelEstresTexto($nivel) {
    $textos = [
        'muy_bajo' => 'MUY BAJO',
        'bajo' => 'BAJO',
        'medio' => 'MEDIO',
        'alto' => 'ALTO',
        'muy_alto' => 'MUY ALTO'
    ];
    return $textos[$nivel] ?? 'N/A';
}

/**
 * Función helper para obtener el label del nivel de riesgo
 */
function getRiskLabel($nivel) {
    $labels = [
        'sin_riesgo' => 'Sin Riesgo',
        'riesgo_bajo' => 'Riesgo Bajo',
        'riesgo_medio' => 'Riesgo Medio',
        'riesgo_alto' => 'Riesgo Alto',
        'riesgo_muy_alto' => 'Riesgo Muy Alto'
    ];
    return $labels[$nivel] ?? 'Sin datos';
}

/**
 * Función helper para obtener la clase de color del card según nivel de riesgo
 */
function getCardColorClass($nivel) {
    switch($nivel) {
        case 'sin_riesgo':
        case 'riesgo_bajo':
            return 'bg-success'; // Verde
        case 'riesgo_medio':
            return 'bg-warning'; // Amarillo
        case 'riesgo_alto':
        case 'riesgo_muy_alto':
            return 'bg-danger'; // Rojo
        default:
            return 'bg-secondary'; // Gris
    }
}

/**
 * Función helper para obtener el color de texto apropiado según el fondo
 */
function getCardTextClass($nivel) {
    switch($nivel) {
        case 'riesgo_medio':
            return 'text-dark'; // Texto oscuro para fondo amarillo
        default:
            return 'text-white'; // Texto blanco para otros fondos
    }
}

/**
 * Función helper para obtener el gradiente del card Total según nivel de riesgo
 */
function getTotalCardGradient($nivel) {
    switch($nivel) {
        case 'sin_riesgo':
        case 'riesgo_bajo':
            return 'background: linear-gradient(135deg, #28a745 0%, #20c997 100%);'; // Verde
        case 'riesgo_medio':
            return 'background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);'; // Amarillo/Naranja
        case 'riesgo_alto':
            return 'background: linear-gradient(135deg, #fd7e14 0%, #dc3545 100%);'; // Naranja/Rojo
        case 'riesgo_muy_alto':
            return 'background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);'; // Rojo oscuro
        default:
            return 'background: linear-gradient(135deg, #6c757d 0%, #495057 100%);'; // Gris
    }
}

/**
 * Función helper para formatear datos MAX RISK con HTML
 */
function formatMaxRiskHTML($data, $showOtherForm = false) {
    if (empty($data) || !isset($data['promedio'])) {
        return 'N/D';
    }

    $html = number_format($data['promedio'], 1);

    // Si hay forma de origen, mostrarla
    if (isset($data['forma_origen']) && $data['forma_origen'] !== null) {
        $html .= ' <span style="font-size: 0.85em; opacity: 0.9;">(Forma ' . $data['forma_origen'] . ')</span>';
    }

    // Opcionalmente mostrar el valor de la otra forma
    if ($showOtherForm && isset($data['data_a']) && isset($data['data_b'])) {
        $otraForma = $data['forma_origen'] === 'A' ? 'B' : 'A';
        $dataOtra = $data['forma_origen'] === 'A' ? $data['data_b'] : $data['data_a'];
        if ($dataOtra && isset($dataOtra['promedio'])) {
            $html .= '<br><small style="opacity: 0.7;">Forma ' . $otraForma . ': ' . number_format($dataOtra['promedio'], 1) . '</small>';
        }
    }

    return $html;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - PsyRisk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
            font-size: 0.9rem;
        }
        .navbar-custom {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .stat-card {
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            border: none;
        }
        .stat-card .icon {
            font-size: 2rem;
            opacity: 0.8;
        }
        .section-title {
            border-left: 4px solid #667eea;
            padding-left: 15px;
            margin-bottom: 15px;
            font-size: 1.1rem;
        }
        .chart-container {
            position: relative;
            height: 280px;
            margin-bottom: 15px;
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
        .risk-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
            min-width: 90px;
            text-align: center;
        }
        .risk-sin_riesgo { background-color: #28a745; color: white; }
        .risk-riesgo_bajo { background-color: #7dce82; color: white; }
        .risk-riesgo_medio { background-color: #ffc107; color: #333; }
        .risk-riesgo_alto { background-color: #fd7e14; color: white; }
        .risk-riesgo_muy_alto { background-color: #dc3545; color: white; }

        /* Estilos para tarjetas de dimensiones */
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
        .risk-badge-dim {
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
        table.dataTable {
            font-size: 0.85rem;
        }
        .btn-sm {
            font-size: 0.8rem;
            padding: 0.4rem 0.8rem;
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
            <h5 class="mb-0"><i class="fas fa-briefcase me-2 text-primary"></i><?= $title ?></h5>
        </div>
    </nav>

    <!-- Content -->
    <div class="container-fluid p-4">
        <!-- Información del Servicio -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body p-3">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-1"><?= esc($service['service_name']) ?></h6>
                        <p class="mb-0 small text-muted">
                            <i class="fas fa-building me-1"></i><?= esc($service['company_name']) ?>
                            <span class="mx-2">|</span>
                            <i class="fas fa-calendar me-1"></i><?= date('d/m/Y', strtotime($service['service_date'])) ?>
                        </p>
                    </div>
                    <div class="col-md-6 text-end">
                        <span class="badge bg-primary">Total: <?= $totalWorkers ?> trabajadores</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas Generales - Total Intralaboral -->
        <div class="row mb-3">
            <div class="col-12">
                <?php
                $nivelTotalIntralaboral = $stats['maxRisk']['intralaboral_total']['nivel'] ?? 'sin_riesgo';
                $textClass = $nivelTotalIntralaboral === 'riesgo_medio' ? 'text-dark' : 'text-white';
                ?>
                <div class="card stat-card" style="<?= getTotalCardGradient($nivelTotalIntralaboral) ?> color: white;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="<?= $textClass ?>">
                            <h6 class="text-uppercase mb-1" style="font-size: 0.85rem; opacity: 0.9;">Total Intralaboral (MAX RISK)</h6>
                            <h2 class="fw-bold mb-0">
                                <?= getRiskLabel($nivelTotalIntralaboral) ?>
                            </h2>
                            <p class="mb-0 small mt-1" style="opacity: 0.8;">
                                <i class="fas fa-chart-line me-1"></i><?= formatMaxRiskHTML($stats['maxRisk']['intralaboral_total'] ?? [], true) ?>
                            </p>
                            <p class="mb-0 small mt-1" style="opacity: 0.8;">
                                <i class="fas fa-users me-1"></i><?= $totalWorkers ?> trabajadores evaluados
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas por Dominios y Dimensiones -->
        <div class="row mb-3">
            <!-- DOMINIO 1: LIDERAZGO -->
            <div class="col-md-6 col-lg-3 mb-3">
                <?php $nivelLiderazgo = $stats['maxRisk']['liderazgo']['nivel'] ?? 'sin_riesgo'; ?>
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header <?= getCardColorClass($nivelLiderazgo) ?> <?= getCardTextClass($nivelLiderazgo) ?>">
                        <div class="d-flex justify-content-between align-items-center">
                            <div style="flex: 1;">
                                <h6 class="mb-0" style="font-size: 0.75rem;">Liderazgo y relaciones sociales en el trabajo</h6>
                                <h4 class="fw-bold mb-0 mt-1" style="font-size: 1.1rem;">
                                    <?= getRiskLabel($nivelLiderazgo) ?>
                                </h4>
                                <p class="mb-0 small mt-1" style="opacity: 0.9; font-size: 0.75rem;">
                                    <?= formatMaxRiskHTML($stats['maxRisk']['liderazgo'] ?? []) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="accordion accordion-flush" id="accordionLiderazgo">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed py-2 px-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseLiderazgo">
                                        <small><i class="fas fa-list me-2"></i>Ver Dimensiones (4)</small>
                                    </button>
                                </h2>
                                <div id="collapseLiderazgo" class="accordion-collapse collapse" data-bs-parent="#accordionLiderazgo">
                                    <div class="accordion-body p-2">
                                        <ul class="list-group list-group-flush small">
                                            <li class="list-group-item d-flex justify-content-between align-items-center py-1 px-2">
                                                <span style="font-size: 0.75rem;">Características del liderazgo</span>
                                                <span class="badge <?= getBadgeClass($stats['maxRisk']['dim_caracteristicas_liderazgo']['nivel'] ?? '') ?>" style=" font-size: 0.65rem;">
                                                    <?= getRiskLabel($stats['maxRisk']['dim_caracteristicas_liderazgo']['nivel'] ?? 'sin_riesgo') ?? 'Sin datos' ?>
                                                </span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center py-1 px-2">
                                                <span style="font-size: 0.75rem;">Relaciones sociales</span>
                                                <span class="badge <?= getBadgeClass($stats['maxRisk']['dim_relaciones_sociales']['nivel'] ?? '') ?>" style=" font-size: 0.65rem;">
                                                    <?= getRiskLabel($stats['maxRisk']['dim_relaciones_sociales']['nivel'] ?? 'sin_riesgo') ?? 'Sin datos' ?>
                                                </span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center py-1 px-2">
                                                <span style="font-size: 0.75rem;">Retroalimentación</span>
                                                <span class="badge <?= getBadgeClass($stats['maxRisk']['dim_retroalimentacion']['nivel'] ?? '') ?>" style=" font-size: 0.65rem;">
                                                    <?= getRiskLabel($stats['maxRisk']['dim_retroalimentacion']['nivel'] ?? 'sin_riesgo') ?? 'Sin datos' ?>
                                                </span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center py-1 px-2">
                                                <span style="font-size: 0.75rem;">Relación con colaboradores</span>
                                                <span class="badge <?= getBadgeClass($stats['maxRisk']['dim_relacion_colaboradores']['nivel'] ?? '') ?>" style=" font-size: 0.65rem;">
                                                    <?= getRiskLabel($stats['maxRisk']['dim_relacion_colaboradores']['nivel'] ?? 'sin_riesgo') ?? 'Sin datos' ?>
                                                </span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- DOMINIO 2: CONTROL -->
            <div class="col-md-6 col-lg-3 mb-3">
                <?php $nivelControl = $stats['maxRisk']['control']['nivel'] ?? 'sin_riesgo'; ?>
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header <?= getCardColorClass($nivelControl) ?> <?= getCardTextClass($nivelControl) ?>">
                        <div class="d-flex justify-content-between align-items-center">
                            <div style="flex: 1;">
                                <h6 class="mb-0" style="font-size: 0.75rem;">Control sobre el trabajo</h6>
                                <h4 class="fw-bold mb-0 mt-1" style="font-size: 1.1rem;">
                                    <?= getRiskLabel($nivelControl) ?>
                                </h4>
                                <p class="mb-0 small mt-1" style="opacity: 0.9; font-size: 0.75rem;">
                                    <?= formatMaxRiskHTML($stats['maxRisk']['control'] ?? []) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="accordion accordion-flush" id="accordionControl">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed py-2 px-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseControl">
                                        <small><i class="fas fa-list me-2"></i>Ver Dimensiones (5)</small>
                                    </button>
                                </h2>
                                <div id="collapseControl" class="accordion-collapse collapse" data-bs-parent="#accordionControl">
                                    <div class="accordion-body p-2">
                                        <ul class="list-group list-group-flush small">
                                            <li class="list-group-item d-flex justify-content-between align-items-center py-1 px-2">
                                                <span style="font-size: 0.75rem;">Claridad de rol</span>
                                                <span class="badge <?= getBadgeClass($stats['maxRisk']['dim_claridad_rol']['nivel'] ?? '') ?>" style=" font-size: 0.65rem;">
                                                    <?= getRiskLabel($stats['maxRisk']['dim_claridad_rol']['nivel'] ?? 'sin_riesgo') ?? 'Sin datos' ?>
                                                </span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center py-1 px-2">
                                                <span style="font-size: 0.75rem;">Capacitación</span>
                                                <span class="badge <?= getBadgeClass($stats['maxRisk']['dim_capacitacion']['nivel'] ?? '') ?>" style=" font-size: 0.65rem;">
                                                    <?= getRiskLabel($stats['maxRisk']['dim_capacitacion']['nivel'] ?? 'sin_riesgo') ?? 'Sin datos' ?>
                                                </span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center py-1 px-2">
                                                <span style="font-size: 0.75rem;">Participación y cambio</span>
                                                <span class="badge <?= getBadgeClass($stats['maxRisk']['dim_participacion_manejo_cambio']['nivel'] ?? '') ?>" style=" font-size: 0.65rem;">
                                                    <?= getRiskLabel($stats['maxRisk']['dim_participacion_manejo_cambio']['nivel'] ?? 'sin_riesgo') ?? 'Sin datos' ?>
                                                </span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center py-1 px-2">
                                                <span style="font-size: 0.75rem;">Oportunidades desarrollo</span>
                                                <span class="badge <?= getBadgeClass($stats['maxRisk']['dim_oportunidades_desarrollo']['nivel'] ?? '') ?>" style=" font-size: 0.65rem;">
                                                    <?= getRiskLabel($stats['maxRisk']['dim_oportunidades_desarrollo']['nivel'] ?? 'sin_riesgo') ?? 'Sin datos' ?>
                                                </span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center py-1 px-2">
                                                <span style="font-size: 0.75rem;">Control y autonomía</span>
                                                <span class="badge <?= getBadgeClass($stats['maxRisk']['dim_control_autonomia']['nivel'] ?? '') ?>" style=" font-size: 0.65rem;">
                                                    <?= getRiskLabel($stats['maxRisk']['dim_control_autonomia']['nivel'] ?? 'sin_riesgo') ?? 'Sin datos' ?>
                                                </span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- DOMINIO 3: DEMANDAS -->
            <div class="col-md-6 col-lg-3 mb-3">
                <?php $nivelDemandas = $stats['maxRisk']['demandas']['nivel'] ?? 'sin_riesgo'; ?>
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header <?= getCardColorClass($nivelDemandas) ?> <?= getCardTextClass($nivelDemandas) ?>">
                        <div class="d-flex justify-content-between align-items-center">
                            <div style="flex: 1;">
                                <h6 class="mb-0" style="font-size: 0.75rem;">Demandas del trabajo</h6>
                                <h4 class="fw-bold mb-0 mt-1" style="font-size: 1.1rem;">
                                    <?= getRiskLabel($nivelDemandas) ?>
                                </h4>
                                <p class="mb-0 small mt-1" style="font-size: 0.75rem;">
                                    <?= formatMaxRiskHTML($stats['maxRisk']['demandas'] ?? []) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="accordion accordion-flush" id="accordionDemandas">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed py-2 px-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDemandas">
                                        <small><i class="fas fa-list me-2"></i>Ver Dimensiones (8)</small>
                                    </button>
                                </h2>
                                <div id="collapseDemandas" class="accordion-collapse collapse" data-bs-parent="#accordionDemandas">
                                    <div class="accordion-body p-2">
                                        <ul class="list-group list-group-flush small">
                                            <li class="list-group-item d-flex justify-content-between align-items-center py-1 px-2">
                                                <span style="font-size: 0.75rem;">Demandas ambientales</span>
                                                <span class="badge <?= getBadgeClass($stats['maxRisk']['dim_demandas_ambientales']['nivel'] ?? '') ?>" style=" font-size: 0.65rem;">
                                                    <?= getRiskLabel($stats['maxRisk']['dim_demandas_ambientales']['nivel'] ?? 'sin_riesgo') ?? 'Sin datos' ?>
                                                </span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center py-1 px-2">
                                                <span style="font-size: 0.75rem;">Demandas emocionales</span>
                                                <span class="badge <?= getBadgeClass($stats['maxRisk']['dim_demandas_emocionales']['nivel'] ?? '') ?>" style=" font-size: 0.65rem;">
                                                    <?= getRiskLabel($stats['maxRisk']['dim_demandas_emocionales']['nivel'] ?? 'sin_riesgo') ?? 'Sin datos' ?>
                                                </span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center py-1 px-2">
                                                <span style="font-size: 0.75rem;">Demandas cuantitativas</span>
                                                <span class="badge <?= getBadgeClass($stats['maxRisk']['dim_demandas_cuantitativas']['nivel'] ?? '') ?>" style=" font-size: 0.65rem;">
                                                    <?= getRiskLabel($stats['maxRisk']['dim_demandas_cuantitativas']['nivel'] ?? 'sin_riesgo') ?? 'Sin datos' ?>
                                                </span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center py-1 px-2">
                                                <span style="font-size: 0.75rem;">Influencia del entorno</span>
                                                <span class="badge <?= getBadgeClass($stats['maxRisk']['dim_influencia_trabajo_entorno_extralaboral']['nivel'] ?? '') ?>" style=" font-size: 0.65rem;">
                                                    <?= getRiskLabel($stats['maxRisk']['dim_influencia_trabajo_entorno_extralaboral']['nivel'] ?? 'sin_riesgo') ?? 'Sin datos' ?>
                                                </span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center py-1 px-2">
                                                <span style="font-size: 0.75rem;">Exigencias responsabilidad</span>
                                                <span class="badge <?= getBadgeClass($stats['maxRisk']['dim_demandas_responsabilidad']['nivel'] ?? '') ?>" style=" font-size: 0.65rem;">
                                                    <?= getRiskLabel($stats['maxRisk']['dim_demandas_responsabilidad']['nivel'] ?? 'sin_riesgo') ?? 'Sin datos' ?>
                                                </span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center py-1 px-2">
                                                <span style="font-size: 0.75rem;">Demandas carga mental</span>
                                                <span class="badge <?= getBadgeClass($stats['maxRisk']['dim_demandas_carga_mental']['nivel'] ?? '') ?>" style=" font-size: 0.65rem;">
                                                    <?= getRiskLabel($stats['maxRisk']['dim_demandas_carga_mental']['nivel'] ?? 'sin_riesgo') ?? 'Sin datos' ?>
                                                </span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center py-1 px-2">
                                                <span style="font-size: 0.75rem;">Consistencia del rol</span>
                                                <span class="badge <?= getBadgeClass($stats['maxRisk']['dim_consistencia_rol']['nivel'] ?? '') ?>" style=" font-size: 0.65rem;">
                                                    <?= getRiskLabel($stats['maxRisk']['dim_consistencia_rol']['nivel'] ?? 'sin_riesgo') ?? 'Sin datos' ?>
                                                </span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center py-1 px-2">
                                                <span style="font-size: 0.75rem;">Demandas de la jornada</span>
                                                <span class="badge <?= getBadgeClass($stats['maxRisk']['dim_demandas_jornada_trabajo']['nivel'] ?? '') ?>" style=" font-size: 0.65rem;">
                                                    <?= getRiskLabel($stats['maxRisk']['dim_demandas_jornada_trabajo']['nivel'] ?? 'sin_riesgo') ?? 'Sin datos' ?>
                                                </span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- DOMINIO 4: RECOMPENSAS -->
            <div class="col-md-6 col-lg-3 mb-3">
                <?php $nivelRecompensas = $stats['maxRisk']['recompensas']['nivel'] ?? 'sin_riesgo'; ?>
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header <?= getCardColorClass($nivelRecompensas) ?> <?= getCardTextClass($nivelRecompensas) ?>">
                        <div class="d-flex justify-content-between align-items-center">
                            <div style="flex: 1;">
                                <h6 class="mb-0" style="font-size: 0.75rem;">Recompensas</h6>
                                <h4 class="fw-bold mb-0 mt-1" style="font-size: 1.1rem;">
                                    <?= getRiskLabel($nivelRecompensas) ?>
                                </h4>
                                <p class="mb-0 small mt-1" style="opacity: 0.9; font-size: 0.75rem;">
                                    <?= formatMaxRiskHTML($stats['maxRisk']['recompensas'] ?? []) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="accordion accordion-flush" id="accordionRecompensas">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed py-2 px-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseRecompensas">
                                        <small><i class="fas fa-list me-2"></i>Ver Dimensiones (2)</small>
                                    </button>
                                </h2>
                                <div id="collapseRecompensas" class="accordion-collapse collapse" data-bs-parent="#accordionRecompensas">
                                    <div class="accordion-body p-2">
                                        <ul class="list-group list-group-flush small">
                                            <li class="list-group-item d-flex justify-content-between align-items-center py-1 px-2">
                                                <span style="font-size: 0.75rem;">Reconocimiento y compensación</span>
                                                <span class="badge <?= getBadgeClass($stats['maxRisk']['dim_reconocimiento_compensacion']['nivel'] ?? '') ?>" style=" font-size: 0.65rem;">
                                                    <?= getRiskLabel($stats['maxRisk']['dim_reconocimiento_compensacion']['nivel'] ?? 'sin_riesgo') ?? 'Sin datos' ?>
                                                </span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center py-1 px-2">
                                                <span style="font-size: 0.75rem;">Recompensas pertenencia</span>
                                                <span class="badge <?= getBadgeClass($stats['maxRisk']['dim_recompensas_pertenencia']['nivel'] ?? '') ?>" style=" font-size: 0.65rem;">
                                                    <?= getRiskLabel($stats['maxRisk']['dim_recompensas_pertenencia']['nivel'] ?? 'sin_riesgo') ?? 'Sin datos' ?>
                                                </span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Segmentadores -->
        <div class="segmentador-card">
            <h6 class="fw-bold mb-3">
                <i class="fas fa-filter text-primary me-2"></i>Segmentadores y Filtros
            </h6>

            <!-- Filtros de Riesgo Psicosocial -->
            <div class="alert alert-light border mb-3">
                <h6 class="fw-bold mb-2 small"><i class="fas fa-chart-line text-danger me-2"></i>Filtros de Riesgo Psicosocial</h6>
                <div class="row">
                    <!-- Dominio -->
                    <div class="col-md-3">
                        <div class="filter-group">
                            <label><i class="fas fa-layer-group me-1"></i>Dominio</label>
                            <select class="form-select form-select-sm" id="filter_dominio">
                                <option value="">Todos los dominios</option>
                                <option value="liderazgo">Liderazgo y Relaciones Sociales</option>
                                <option value="control">Control sobre el Trabajo</option>
                                <option value="demandas">Demandas del Trabajo</option>
                                <option value="recompensas">Recompensas</option>
                            </select>
                        </div>
                    </div>

                    <!-- Dimensión (jerárquico) -->
                    <div class="col-md-4">
                        <div class="filter-group">
                            <label><i class="fas fa-stream me-1"></i>Dimensión</label>
                            <select class="form-select form-select-sm" id="filter_dimension">
                                <option value="">Todas las dimensiones</option>
                                <!-- Opciones dinámicas según dominio seleccionado -->
                            </select>
                        </div>
                    </div>

                    <!-- Nivel de Riesgo -->
                    <div class="col-md-3">
                        <div class="filter-group">
                            <label><i class="fas fa-exclamation-circle me-1"></i>Nivel de Riesgo</label>
                            <select class="form-select form-select-sm" id="filter_nivel_riesgo">
                                <option value="">Todos</option>
                                <option value="sin_riesgo">Sin Riesgo</option>
                                <option value="riesgo_bajo">Riesgo Bajo</option>
                                <option value="riesgo_medio">Riesgo Medio</option>
                                <option value="riesgo_alto">Riesgo Alto</option>
                                <option value="riesgo_muy_alto">Riesgo Muy Alto</option>
                            </select>
                        </div>
                    </div>

                    <!-- Tipo Formulario -->
                    <div class="col-md-2">
                        <div class="filter-group">
                            <label><i class="fas fa-file-alt me-1"></i>Tipo Formulario</label>
                            <select class="form-select form-select-sm" id="filter_form_type">
                                <option value="">Todos</option>
                                <option value="A">Forma A</option>
                                <option value="B">Forma B</option>
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

                    <!-- Departamento -->
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

                    <!-- Tipo de Cargo -->
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

                    <!-- Cargo Específico -->
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

                    <!-- Nivel de Estudios -->
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

                    <!-- Estado Civil -->
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
                </div>
            </div>

            <!-- Filtros Laborales -->
            <div class="alert alert-light border mb-0">
                <h6 class="fw-bold mb-2 small"><i class="fas fa-briefcase text-success me-2"></i>Filtros Laborales y Ubicación</h6>
                <div class="row">
                    <!-- Tipo de Contrato -->
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

                    <!-- Ciudad -->
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

                    <!-- Estrato -->
                    <div class="col-md-2">
                        <div class="filter-group">
                            <label><i class="fas fa-layer-group me-1"></i>Estrato</label>
                            <select class="form-select form-select-sm" id="filter_stratum">
                                <option value="">Todos</option>
                                <?php foreach ($segmentadores['estratos'] as $estrato): ?>
                                    <option value="<?= esc($estrato) ?>"><?= esc($estrato) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Tipo de Vivienda -->
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

                    <!-- Antigüedad -->
                    <div class="col-md-2">
                        <div class="filter-group">
                            <label><i class="fas fa-calendar-alt me-1"></i>Antigüedad</label>
                            <select class="form-select form-select-sm" id="filter_time_in_company">
                                <option value="">Todos</option>
                                <?php foreach ($segmentadores['antiguedad'] as $label => $valor): ?>
                                    <option value="<?= esc($valor) ?>"><?= esc($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-3">
                <button class="btn btn-secondary btn-sm" onclick="clearAllFilters()">
                    <i class="fas fa-redo me-1"></i>Limpiar Todos los Filtros
                </button>
            </div>
        </div>

        <!-- Distribución por Nivel de Riesgo -->
        <h6 class="section-title">Distribución por Nivel de Riesgo</h6>
        <div class="row mb-3">
            <?php
            $riskCards = [
                ['nivel' => 'sin_riesgo', 'label' => 'SIN RIESGO', 'color' => '#28a745'],
                ['nivel' => 'riesgo_bajo', 'label' => 'RIESGO BAJO', 'color' => '#7dce82'],
                ['nivel' => 'riesgo_medio', 'label' => 'RIESGO MEDIO', 'color' => '#ffc107'],
                ['nivel' => 'riesgo_alto', 'label' => 'RIESGO ALTO', 'color' => '#fd7e14'],
                ['nivel' => 'riesgo_muy_alto', 'label' => 'RIESGO MUY ALTO', 'color' => '#dc3545']
            ];

            // Calcular distribución de riesgo
            $riskDistribution = ['sin_riesgo' => 0, 'riesgo_bajo' => 0, 'riesgo_medio' => 0, 'riesgo_alto' => 0, 'riesgo_muy_alto' => 0];
            foreach ($results as $r) {
                $nivel = $r['intralaboral_total_nivel'] ?? 'sin_riesgo';
                if (isset($riskDistribution[$nivel])) {
                    $riskDistribution[$nivel]++;
                }
            }

            foreach ($riskCards as $card):
                $count = $riskDistribution[$card['nivel']] ?? 0;
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

        <!-- Dimensiones Intralaborales - Acordeón -->
        <?php
        // Helper functions para dimensiones
        function getDimRiskLabel($nivel) {
            $labels = [
                'sin_riesgo' => 'Sin Riesgo',
                'riesgo_bajo' => 'Riesgo Bajo',
                'riesgo_medio' => 'Riesgo Medio',
                'riesgo_alto' => 'Riesgo Alto',
                'riesgo_muy_alto' => 'Riesgo Muy Alto'
            ];
            return $labels[$nivel] ?? 'N/A';
        }

        function getDimBadgeStyle($nivel) {
            $styles = [
                'sin_riesgo' => 'background-color: #28a745; color: white;',
                'riesgo_bajo' => 'background-color: #7dce82; color: white;',
                'riesgo_medio' => 'background-color: #ffc107; color: #333;',
                'riesgo_alto' => 'background-color: #fd7e14; color: white;',
                'riesgo_muy_alto' => 'background-color: #dc3545; color: white;'
            ];
            return $styles[$nivel] ?? 'background-color: #6c757d; color: white;';
        }

        // Función para calcular promedio y nivel de una dimensión
        function calcularDimension($results, $dbKey, $formType = null) {
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

        // Dimensiones Forma A (19 dimensiones) con nombres de columnas BD correctos
        $dimensionesFormaA = [
            ['db_key' => 'dim_caracteristicas_liderazgo', 'label' => 'Características del liderazgo', 'icon' => 'user-tie'],
            ['db_key' => 'dim_relaciones_sociales', 'label' => 'Relaciones sociales en el trabajo', 'icon' => 'users'],
            ['db_key' => 'dim_retroalimentacion', 'label' => 'Retroalimentación del desempeño', 'icon' => 'comments'],
            ['db_key' => 'dim_relacion_colaboradores', 'label' => 'Relación con los colaboradores', 'icon' => 'handshake'],
            ['db_key' => 'dim_claridad_rol', 'label' => 'Claridad de rol', 'icon' => 'bullseye'],
            ['db_key' => 'dim_capacitacion', 'label' => 'Capacitación', 'icon' => 'graduation-cap'],
            ['db_key' => 'dim_participacion_manejo_cambio', 'label' => 'Participación y manejo del cambio', 'icon' => 'sync-alt'],
            ['db_key' => 'dim_oportunidades_desarrollo', 'label' => 'Oportunidades de desarrollo', 'icon' => 'chart-line'],
            ['db_key' => 'dim_control_autonomia', 'label' => 'Control y autonomía sobre el trabajo', 'icon' => 'sliders-h'],
            ['db_key' => 'dim_demandas_ambientales', 'label' => 'Demandas ambientales y de esfuerzo físico', 'icon' => 'hard-hat'],
            ['db_key' => 'dim_demandas_emocionales', 'label' => 'Demandas emocionales', 'icon' => 'heart'],
            ['db_key' => 'dim_demandas_cuantitativas', 'label' => 'Demandas cuantitativas', 'icon' => 'tasks'],
            ['db_key' => 'dim_influencia_trabajo_entorno_extralaboral', 'label' => 'Influencia del trabajo sobre el entorno extralaboral', 'icon' => 'home'],
            ['db_key' => 'dim_demandas_responsabilidad', 'label' => 'Exigencias de responsabilidad del cargo', 'icon' => 'shield-alt'],
            ['db_key' => 'dim_demandas_carga_mental', 'label' => 'Demandas de carga mental', 'icon' => 'brain'],
            ['db_key' => 'dim_consistencia_rol', 'label' => 'Consistencia del rol', 'icon' => 'balance-scale'],
            ['db_key' => 'dim_demandas_jornada_trabajo', 'label' => 'Demandas de la jornada de trabajo', 'icon' => 'clock'],
            ['db_key' => 'dim_recompensas_pertenencia', 'label' => 'Recompensas derivadas de la pertenencia', 'icon' => 'medal'],
            ['db_key' => 'dim_reconocimiento_compensacion', 'label' => 'Reconocimiento y compensación', 'icon' => 'award']
        ];

        // Dimensiones Forma B (16 dimensiones - sin las 3 exclusivas de Forma A)
        $dimensionesFormaB = [
            ['db_key' => 'dim_caracteristicas_liderazgo', 'label' => 'Características del liderazgo', 'icon' => 'user-tie'],
            ['db_key' => 'dim_relaciones_sociales', 'label' => 'Relaciones sociales en el trabajo', 'icon' => 'users'],
            ['db_key' => 'dim_retroalimentacion', 'label' => 'Retroalimentación del desempeño', 'icon' => 'comments'],
            // Sin: Relación con los colaboradores
            ['db_key' => 'dim_claridad_rol', 'label' => 'Claridad de rol', 'icon' => 'bullseye'],
            ['db_key' => 'dim_capacitacion', 'label' => 'Capacitación', 'icon' => 'graduation-cap'],
            ['db_key' => 'dim_participacion_manejo_cambio', 'label' => 'Participación y manejo del cambio', 'icon' => 'sync-alt'],
            ['db_key' => 'dim_oportunidades_desarrollo', 'label' => 'Oportunidades de desarrollo', 'icon' => 'chart-line'],
            ['db_key' => 'dim_control_autonomia', 'label' => 'Control y autonomía sobre el trabajo', 'icon' => 'sliders-h'],
            ['db_key' => 'dim_demandas_ambientales', 'label' => 'Demandas ambientales y de esfuerzo físico', 'icon' => 'hard-hat'],
            ['db_key' => 'dim_demandas_emocionales', 'label' => 'Demandas emocionales', 'icon' => 'heart'],
            ['db_key' => 'dim_demandas_cuantitativas', 'label' => 'Demandas cuantitativas', 'icon' => 'tasks'],
            ['db_key' => 'dim_influencia_trabajo_entorno_extralaboral', 'label' => 'Influencia del trabajo sobre el entorno extralaboral', 'icon' => 'home'],
            // Sin: Exigencias de responsabilidad del cargo
            ['db_key' => 'dim_demandas_carga_mental', 'label' => 'Demandas de carga mental', 'icon' => 'brain'],
            // Sin: Consistencia del rol
            ['db_key' => 'dim_demandas_jornada_trabajo', 'label' => 'Demandas de la jornada de trabajo', 'icon' => 'clock'],
            ['db_key' => 'dim_recompensas_pertenencia', 'label' => 'Recompensas derivadas de la pertenencia', 'icon' => 'medal'],
            ['db_key' => 'dim_reconocimiento_compensacion', 'label' => 'Reconocimiento y compensación', 'icon' => 'award']
        ];

        // Contar trabajadores por forma
        $countFormaA = 0;
        $countFormaB = 0;
        foreach ($results as $r) {
            if (($r['intralaboral_form_type'] ?? '') === 'A') $countFormaA++;
            if (($r['intralaboral_form_type'] ?? '') === 'B') $countFormaB++;
        }
        ?>

        <div class="accordion mb-4" id="accordionDimensiones">
            <!-- MAX RISK - Todas las Dimensiones -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingMaxRisk">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMaxRisk" aria-expanded="true" aria-controls="collapseMaxRisk">
                        <i class="fas fa-exclamation-triangle me-2 text-danger"></i>
                        <strong>Dimensiones Intralaborales (MAX RISK)</strong>
                        <span class="badge bg-danger ms-2">Peor escenario entre Formas A y B</span>
                        <span class="badge bg-secondary ms-2"><?= $totalWorkers ?> trabajadores evaluados</span>
                    </button>
                </h2>
                <div id="collapseMaxRisk" class="accordion-collapse collapse show" aria-labelledby="headingMaxRisk" data-bs-parent="#accordionDimensiones">
                    <div class="accordion-body">
                        <?php if ($totalWorkers > 0): ?>
                        <div class="alert alert-warning mb-3">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>MAX RISK:</strong> Se muestra el peor resultado entre Forma A y Forma B para cada dimensión, aplicando los baremos oficiales correspondientes.
                        </div>
                        <div class="row">
                            <?php foreach ($dimensionesFormaA as $dim):
                                $dimKey = $dim['db_key'];
                                $maxRiskData = $stats['maxRisk'][$dimKey] ?? null;
                                if (!$maxRiskData || !isset($maxRiskData['promedio'])) continue;
                            ?>
                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="card dimension-card">
                                    <div class="card-body">
                                        <div class="dimension-label">
                                            <i class="fas fa-<?= $dim['icon'] ?> me-2"></i><?= $dim['label'] ?>
                                        </div>
                                        <div class="dimension-value text-danger">
                                            <?= number_format($maxRiskData['promedio'], 1) ?>
                                            <?php if (isset($maxRiskData['forma_origen'])): ?>
                                                <small class="text-muted">(<?= $maxRiskData['forma_origen'] ?>)</small>
                                            <?php endif; ?>
                                        </div>
                                        <span class="risk-badge-dim" style="<?= getDimBadgeStyle($maxRiskData['nivel'] ?? 'sin_riesgo') ?>">
                                            <?= getDimRiskLabel($maxRiskData['nivel'] ?? 'sin_riesgo') ?>
                                        </span>
                                        <?php if (isset($maxRiskData['data_a']) && isset($maxRiskData['data_b'])): ?>
                                            <div class="mt-2 small text-muted">
                                                <div>Forma A: <?= number_format($maxRiskData['data_a']['promedio'], 1) ?></div>
                                                <div>Forma B: <?= number_format($maxRiskData['data_b']['promedio'], 1) ?></div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>No hay trabajadores evaluados
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficas -->
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-2">
                        <h6 class="mb-0 small fw-bold">
                            <i class="fas fa-chart-pie text-primary me-1"></i>Distribución de Riesgo
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="riskChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-2">
                        <h6 class="mb-0 small fw-bold">
                            <i class="fas fa-chart-bar text-primary me-1"></i>Dominios Intralaborales
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="domainsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-2">
                        <h6 class="mb-0 small fw-bold">
                            <i class="fas fa-users text-primary me-1"></i>Distribución por Género
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="genderChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfica de Dimensiones por Dominio -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-2">
                        <h6 class="mb-0 small fw-bold">
                            <i class="fas fa-chart-bar text-primary me-1"></i>Dimensiones por Dominio - Niveles de Riesgo
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="height: 400px;">
                            <canvas id="dimensionsGroupedChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfica de Comparación Forma A vs Forma B -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-2">
                        <h6 class="mb-0 small fw-bold">
                            <i class="fas fa-balance-scale text-primary me-1"></i>Comparación de Dominios: Forma A vs Forma B
                        </h6>
                        <p class="mb-0 small text-muted" style="font-size: 0.75rem;">
                            Forma A: Jefes, profesionales, técnicos | Forma B: Auxiliares, operarios
                        </p>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="height: 350px;">
                            <canvas id="formsComparisonChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfica de Top 5 Dimensiones Críticas -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-2">
                        <h6 class="mb-0 small fw-bold">
                            <i class="fas fa-exclamation-triangle text-danger me-1"></i>Top 5 Dimensiones Críticas (Mayor Riesgo)
                        </h6>
                        <p class="mb-0 small text-muted" style="font-size: 0.75rem;">
                            Prioridades de intervención según nivel de riesgo
                        </p>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="height: 320px;">
                            <canvas id="topDimensionsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfica de Distribución por Departamento/Área -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-2">
                        <h6 class="mb-0 small fw-bold">
                            <i class="fas fa-building text-info me-1"></i>Top 10 Departamentos con Mayor Riesgo Intralaboral
                        </h6>
                        <p class="mb-0 small text-muted" style="font-size: 0.75rem;">
                            Puntaje total intralaboral promedio por departamento
                        </p>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="height: 380px;">
                            <canvas id="departmentChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfica de Correlación Nivel Educativo vs Riesgo -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-2">
                        <h6 class="mb-0 small fw-bold">
                            <i class="fas fa-graduation-cap text-success me-1"></i>Riesgo Intralaboral por Nivel Educativo
                        </h6>
                        <p class="mb-0 small text-muted" style="font-size: 0.75rem;">
                            Correlación entre formación académica y nivel de riesgo psicosocial
                        </p>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="height: 340px;">
                            <canvas id="educationChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfica de Distribución por Rango de Edad -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-2">
                        <h6 class="mb-0 small fw-bold">
                            <i class="fas fa-users text-warning me-1"></i>Riesgo Intralaboral por Rango de Edad
                        </h6>
                        <p class="mb-0 small text-muted" style="font-size: 0.75rem;">
                            Distribución del riesgo psicosocial según grupos etarios
                        </p>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="height: 320px;">
                            <canvas id="ageChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Resultados -->
        <div class="row">
            <div class="col-12">
                <h6 class="section-title">
        <i class="fas fa-table me-2"></i>Resultados Detallados por Trabajador
    </h6>
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-3">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover" id="resultsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Documento</th>
                                        <th>Género</th>
                                        <th>Tipo Form</th>
                                        <th>Cargo</th>
                                        <th>Departamento</th>
                                        <th>Nivel Intralaboral</th>
                                        <th>Nivel Extralaboral</th>
                                        <th>Nivel Estrés</th>
                                        <th>Nivel Total</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                    <tr>
                                        <th><input type="text" class="form-control form-control-sm" placeholder="Buscar nombre"></th>
                                        <th><input type="text" class="form-control form-control-sm" placeholder="Buscar doc"></th>
                                        <th><input type="text" class="form-control form-control-sm" placeholder="Buscar género"></th>
                                        <th><input type="text" class="form-control form-control-sm" placeholder="Buscar form"></th>
                                        <th><input type="text" class="form-control form-control-sm" placeholder="Buscar cargo"></th>
                                        <th><input type="text" class="form-control form-control-sm" placeholder="Buscar depto"></th>
                                        <th><input type="text" class="form-control form-control-sm" placeholder="Buscar nivel"></th>
                                        <th><input type="text" class="form-control form-control-sm" placeholder="Buscar nivel"></th>
                                        <th><input type="text" class="form-control form-control-sm" placeholder="Buscar nivel"></th>
                                        <th><input type="text" class="form-control form-control-sm" placeholder="Buscar nivel"></th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($results as $result): ?>
                                        <tr data-result='<?= htmlspecialchars(json_encode($result), ENT_QUOTES, 'UTF-8') ?>'>
                                            <td><?= esc($result['worker_name']) ?></td>
                                            <td><?= esc($result['worker_document']) ?></td>
                                            <td><?= esc($result['gender']) ?></td>
                                            <td><span class="badge bg-secondary"><?= esc($result['intralaboral_form_type']) ?></span></td>
                                            <td><?= esc($result['position']) ?></td>
                                            <td><?= esc($result['department']) ?></td>
                                            <td>
                                                <span class="risk-badge risk-<?= esc($result['intralaboral_total_nivel']) ?>">
                                                    <?= ucfirst(str_replace('_', ' ', $result['intralaboral_total_nivel'])) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="risk-badge risk-<?= esc($result['extralaboral_total_nivel']) ?>">
                                                    <?= ucfirst(str_replace('_', ' ', $result['extralaboral_total_nivel'])) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if (!empty($result['estres_total_nivel'])): ?>
                                                    <span class="badge" style="background-color: <?= getNivelEstresColor($result['estres_total_nivel']) ?>; color: white;">
                                                        <?= getNivelEstresTexto($result['estres_total_nivel']) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="risk-badge risk-<?= esc($result['puntaje_total_general_nivel']) ?>">
                                                    <?= ucfirst(str_replace('_', ' ', $result['puntaje_total_general_nivel'])) ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <?php
                                                    $requestType = strtolower($result['intralaboral_form_type']) === 'a' ? 'intralaboral_a' : 'intralaboral_b';
                                                    $userRole = session()->get('role_name');
                                                    if (in_array($userRole, ['superadmin', 'admin', 'consultor'])): ?>
                                                        <a href="<?= base_url("workers/results/{$result['worker_id']}") ?>"
                                                           class="btn btn-sm btn-success" title="Ver resultados individuales"
                                                           target="_blank">
                                                            <i class="fas fa-eye"></i> Ver
                                                        </a>
                                                    <?php else:
                                                        $requestKey = $result['worker_id'] . '_' . $requestType;
                                                        $request = $accessRequests[$requestKey] ?? null;

                                                        if (!$request): ?>
                                                            <a href="<?= base_url("individual-results/request/{$serviceId}/{$result['worker_id']}/{$requestType}") ?>"
                                                               class="btn btn-sm btn-primary"
                                                               title="Solicitar acceso a resultados individuales">
                                                                <i class="fas fa-lock"></i> Solicitar
                                                            </a>
                                                        <?php elseif ($request['status'] === 'pending'): ?>
                                                            <button class="btn btn-sm btn-warning" disabled title="Solicitud pendiente de aprobación">
                                                                <i class="fas fa-clock"></i> Pendiente
                                                            </button>
                                                        <?php elseif ($request['status'] === 'approved' && strtotime($request['access_granted_until']) > time()): ?>
                                                            <a href="<?= base_url("individual-results/view/{$request['access_token']}") ?>"
                                                               class="btn btn-sm btn-success"
                                                               title="Ver resultados (acceso hasta <?= date('d/m/Y H:i', strtotime($request['access_granted_until'])) ?>)"
                                                               target="_blank">
                                                                <i class="fas fa-lock-open"></i> Ver
                                                            </a>
                                                        <?php elseif ($request['status'] === 'rejected'): ?>
                                                            <button class="btn btn-sm btn-danger" disabled title="Solicitud rechazada">
                                                                <i class="fas fa-times-circle"></i> Rechazado
                                                            </button>
                                                        <?php else: ?>
                                                            <a href="<?= base_url("individual-results/request/{$serviceId}/{$result['worker_id']}/{$requestType}") ?>"
                                                               class="btn btn-sm btn-secondary"
                                                               title="Acceso expirado - Solicitar nuevamente">
                                                                <i class="fas fa-redo"></i> Renovar
                                                            </a>
                                                        <?php endif;
                                                    endif; ?>
                                            </td>
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

    <!-- Toast Container para notificaciones -->
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
        <div id="filterToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i class="fas fa-info-circle me-2 text-warning"></i>
                <strong class="me-auto">Sugerencia de Filtros</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" id="toastMessage">
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="<?= base_url('js/satisfaction-check.js') ?>"></script>
    <script src="<?= base_url('js/reports/filters.js') ?>"></script>

    <script>
        // Deshabilitar plugin datalabels globalmente (se habilitará solo en gráficos específicos)
        Chart.defaults.plugins.datalabels = {
            display: false
        };

        // Datos para gráficos
        const statsData = <?= json_encode($stats) ?>;
        const allResults = <?= json_encode($results) ?>;
        const accessRequests = <?= json_encode($accessRequests) ?>;
        const userRole = '<?= session()->get('role_name') ?>';
        const isConsultant = ['superadmin', 'admin', 'consultor'].includes(userRole);

        // Función para mostrar notificaciones toast
        function showToast(message, type = 'info') {
            const toastElement = document.getElementById('filterToast');
            const toastMessage = document.getElementById('toastMessage');
            const toastHeader = toastElement.querySelector('.toast-header i');

            // Actualizar mensaje
            toastMessage.textContent = message;

            // Actualizar ícono según tipo
            if (type === 'warning') {
                toastHeader.className = 'fas fa-exclamation-triangle me-2 text-warning';
            } else if (type === 'info') {
                toastHeader.className = 'fas fa-info-circle me-2 text-primary';
            } else if (type === 'success') {
                toastHeader.className = 'fas fa-check-circle me-2 text-success';
            }

            // Mostrar toast
            const toast = new bootstrap.Toast(toastElement, {
                autohide: true,
                delay: 10000
            });
            toast.show();
        }

        // Función para generar botón de acción según estado de solicitud
        function getActionButton(workerId, intralaboralFormType) {
            const serviceId = '<?= $serviceId ?>';
            const baseUrl = '<?= base_url() ?>';
            const requestType = intralaboralFormType.toLowerCase() === 'a' ? 'intralaboral_a' : 'intralaboral_b';

            if (isConsultant) {
                return '<a href="' + baseUrl + 'workers/results/' + workerId + '" class="btn btn-sm btn-success" title="Ver resultados individuales" target="_blank">' +
                    '<i class="fas fa-eye"></i> Ver' +
                    '</a>';
            }

            // Cliente: verificar estado de solicitud
            const requestKey = workerId + '_' + requestType;
            const request = accessRequests[requestKey];

            if (!request) {
                // Sin solicitud
                return '<a href="' + baseUrl + 'individual-results/request/' + serviceId + '/' + workerId + '/' + requestType + '" class="btn btn-sm btn-primary" title="Solicitar acceso a resultados individuales">' +
                    '<i class="fas fa-lock"></i> Solicitar' +
                    '</a>';
            }

            if (request.status === 'pending') {
                // Pendiente
                return '<button class="btn btn-sm btn-warning" disabled title="Solicitud pendiente de aprobación">' +
                    '<i class="fas fa-clock"></i> Pendiente' +
                    '</button>';
            }

            if (request.status === 'approved') {
                const expiresAt = new Date(request.access_granted_until).getTime();
                const now = Date.now();

                if (expiresAt > now) {
                    // Aprobado y vigente
                    const expiresFormatted = new Date(request.access_granted_until).toLocaleString('es-CO', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    return '<a href="' + baseUrl + 'individual-results/view/' + request.access_token + '" class="btn btn-sm btn-success" title="Ver resultados (acceso hasta ' + expiresFormatted + ')" target="_blank">' +
                        '<i class="fas fa-lock-open"></i> Ver' +
                        '</a>';
                } else {
                    // Expirado
                    return '<a href="' + baseUrl + 'individual-results/request/' + serviceId + '/' + workerId + '/' + requestType + '" class="btn btn-sm btn-secondary" title="Acceso expirado - Solicitar nuevamente">' +
                        '<i class="fas fa-redo"></i> Renovar' +
                        '</a>';
                }
            }

            if (request.status === 'rejected') {
                // Rechazado
                return '<button class="btn btn-sm btn-danger" disabled title="Solicitud rechazada">' +
                    '<i class="fas fa-times-circle"></i> Rechazado' +
                    '</button>';
            }

            // Default: sin solicitud
            return '<a href="' + baseUrl + 'individual-results/request/' + serviceId + '/' + workerId + '/' + requestType + '" class="btn btn-sm btn-primary" title="Solicitar acceso a resultados individuales">' +
                '<i class="fas fa-lock"></i> Solicitar' +
                '</a>';
        }

        // Mapeo de dimensiones por dominio (jerárquico)
        const dimensionesPorDominio = {
            liderazgo: [
                { value: 'dim_caracteristicas_liderazgo', text: 'Características del liderazgo' },
                { value: 'dim_relaciones_sociales', text: 'Relaciones sociales en el trabajo' },
                { value: 'dim_retroalimentacion', text: 'Retroalimentación del desempeño' },
                { value: 'dim_relacion_colaboradores', text: 'Relación con los colaboradores (Forma A)' }
            ],
            control: [
                { value: 'dim_claridad_rol', text: 'Claridad de rol' },
                { value: 'dim_capacitacion', text: 'Capacitación' },
                { value: 'dim_participacion_manejo_cambio', text: 'Participación y manejo del cambio' },
                { value: 'dim_oportunidades_desarrollo', text: 'Oportunidades para el uso y desarrollo de habilidades' },
                { value: 'dim_control_autonomia', text: 'Control y autonomía sobre el trabajo' }
            ],
            demandas: [
                { value: 'dim_demandas_ambientales', text: 'Demandas ambientales y de esfuerzo físico' },
                { value: 'dim_demandas_emocionales', text: 'Demandas emocionales' },
                { value: 'dim_demandas_cuantitativas', text: 'Demandas cuantitativas' },
                { value: 'dim_influencia_trabajo_entorno_extralaboral', text: 'Influencia del trabajo sobre el entorno extralaboral' },
                { value: 'dim_demandas_responsabilidad', text: 'Exigencias de responsabilidad del cargo (Forma A)' },
                { value: 'dim_demandas_carga_mental', text: 'Demandas de carga mental (Forma A)' },
                { value: 'dim_consistencia_rol', text: 'Consistencia del rol (Forma A)' },
                { value: 'dim_demandas_jornada_trabajo', text: 'Demandas de la jornada de trabajo' }
            ],
            recompensas: [
                { value: 'dim_reconocimiento_compensacion', text: 'Reconocimiento y compensación' },
                { value: 'dim_recompensas_pertenencia', text: 'Recompensas derivadas de la pertenencia' }
            ]
        };

        // Manejar cambio de dominio para actualizar dimensiones
        document.getElementById('filter_dominio').addEventListener('change', function() {
            const dominioSeleccionado = this.value;
            const selectDimension = document.getElementById('filter_dimension');

            // Limpiar opciones existentes
            selectDimension.innerHTML = '<option value="">Todas las dimensiones</option>';
            selectDimension.value = ''; // Reset dimension selection

            // Si hay un dominio seleccionado, cargar sus dimensiones
            if (dominioSeleccionado && dimensionesPorDominio[dominioSeleccionado]) {
                dimensionesPorDominio[dominioSeleccionado].forEach(dim => {
                    const option = document.createElement('option');
                    option.value = dim.value;
                    option.textContent = dim.text;
                    selectDimension.appendChild(option);
                });
                selectDimension.disabled = false;
            } else {
                // Si no hay dominio, mostrar todas las dimensiones
                selectDimension.disabled = false;
                Object.values(dimensionesPorDominio).flat().forEach(dim => {
                    const option = document.createElement('option');
                    option.value = dim.value;
                    option.textContent = dim.text;
                    selectDimension.appendChild(option);
                });
            }

            // Trigger filter update
            applyFilters();
        });

        // Manejar cambio de dimensión
        document.getElementById('filter_dimension').addEventListener('change', function() {
            const dimensionSeleccionada = this.value;
            const dominioSeleccionado = document.getElementById('filter_dominio').value;

            // Mostrar alerta si hay dimensión seleccionada pero también hay dominio seleccionado
            if (dimensionSeleccionada && dominioSeleccionado) {
                showToast('Sugerencia: Si deseas filtrar solo por una dimensión específica, considera deseleccionar el filtro de Dominio para mejores resultados.', 'warning');
            }

            applyFilters();
        });

        // Inicializar DataTable
        let dataTable = $('#resultsTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
            },
            pageLength: 25,
            order: [[0, 'asc']],
            orderCellsTop: true,
            fixedHeader: true
        });

        // Activar filtros por columna en thead
        $('#resultsTable thead tr:eq(1) th').each(function(i) {
            $('input', this).on('keyup change', function() {
                if (dataTable.column(i).search() !== this.value) {
                    dataTable.column(i).search(this.value).draw();
                }
            });
        });

        // Gráfico de distribución de riesgo
        const riskCtx = document.getElementById('riskChart').getContext('2d');
        let riskChart = new Chart(riskCtx, {
            type: 'doughnut',
            data: {
                labels: ['Sin Riesgo', 'Riesgo Bajo', 'Riesgo Medio', 'Riesgo Alto', 'Riesgo Muy Alto'],
                datasets: [{
                    data: [
                        statsData.riskDistribution.sin_riesgo,
                        statsData.riskDistribution.riesgo_bajo,
                        statsData.riskDistribution.riesgo_medio,
                        statsData.riskDistribution.riesgo_alto,
                        statsData.riskDistribution.riesgo_muy_alto
                    ],
                    backgroundColor: ['#28a745', '#7dce82', '#ffc107', '#fd7e14', '#dc3545']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 } } },
                    datalabels: {
                        color: '#fff',
                        font: { weight: 'bold', size: 12 },
                        formatter: function(value, context) {
                            if (value === 0) return '';
                            return value;
                        }
                    }
                }
            },
            plugins: [ChartDataLabels]
        });

        // Gráfico de dominios - Niveles de riesgo
        const domainsCtx = document.getElementById('domainsChart').getContext('2d');

        // Mapeo de niveles de riesgo a valores numéricos para la gráfica
        const riskLevelValues = {
            'sin_riesgo': 1,
            'riesgo_bajo': 2,
            'riesgo_medio': 3,
            'riesgo_alto': 4,
            'riesgo_muy_alto': 5
        };

        // Mapeo de niveles a colores
        const riskColors = {
            'sin_riesgo': '#28a745',
            'riesgo_bajo': '#7dce82',
            'riesgo_medio': '#ffc107',
            'riesgo_alto': '#fd7e14',
            'riesgo_muy_alto': '#dc3545'
        };

        /**
         * Función helper para determinar nivel de riesgo intralaboral total
         * Aplica baremos más conservadores (Forma A) cuando hay mezcla de formas
         * Corregido según auditoría 2025-11-24
         */
        function getNivelRiesgoIntralaboral(avgScore) {
            // Baremos Forma A (Tabla 33) - más conservadores
            if (avgScore <= 19.7) return 'sin_riesgo';
            else if (avgScore <= 25.8) return 'riesgo_bajo';
            else if (avgScore <= 31.5) return 'riesgo_medio';
            else if (avgScore <= 38.0) return 'riesgo_alto';
            else return 'riesgo_muy_alto';
        }

        const domainData = [
            {
                label: 'Liderazgo',
                nivel: statsData.maxRisk?.liderazgo?.nivel || 'sin_riesgo',
                puntaje: statsData.maxRisk?.liderazgo?.promedio || 0
            },
            {
                label: 'Control',
                nivel: statsData.maxRisk?.control?.nivel || 'sin_riesgo',
                puntaje: statsData.maxRisk?.control?.promedio || 0
            },
            {
                label: 'Demandas',
                nivel: statsData.maxRisk?.demandas?.nivel || 'sin_riesgo',
                puntaje: statsData.maxRisk?.demandas?.promedio || 0
            },
            {
                label: 'Recompensas',
                nivel: statsData.maxRisk?.recompensas?.nivel || 'sin_riesgo',
                puntaje: statsData.maxRisk?.recompensas?.promedio || 0
            }
        ];

        let domainsChart = new Chart(domainsCtx, {
            type: 'bar',
            data: {
                labels: domainData.map(d => d.label),
                datasets: [{
                    label: 'Nivel de Riesgo',
                    data: domainData.map(d => riskLevelValues[d.nivel]),
                    backgroundColor: domainData.map(d => riskColors[d.nivel]),
                    puntajes: domainData.map(d => d.puntaje)
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 5,
                        ticks: {
                            stepSize: 1,
                            callback: function(value) {
                                const labels = ['', 'Sin Riesgo', 'Bajo', 'Medio', 'Alto', 'Muy Alto'];
                                return labels[value] || '';
                            }
                        }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const nivelLabels = {
                                    1: 'Sin Riesgo',
                                    2: 'Riesgo Bajo',
                                    3: 'Riesgo Medio',
                                    4: 'Riesgo Alto',
                                    5: 'Riesgo Muy Alto'
                                };
                                const puntaje = context.dataset.puntajes[context.dataIndex];
                                return nivelLabels[context.parsed.y] + ' (Puntaje: ' + puntaje.toFixed(1) + ')';
                            }
                        }
                    },
                    datalabels: {
                        anchor: 'end',
                        align: 'top',
                        color: '#333',
                        font: { weight: 'bold', size: 11 },
                        formatter: function(value, context) {
                            const puntaje = context.dataset.puntajes[context.dataIndex];
                            return puntaje.toFixed(1);
                        }
                    }
                }
            },
            plugins: [ChartDataLabels]
        });

        // Gráfico de género
        const genderCtx = document.getElementById('genderChart').getContext('2d');
        const genderLabels = Object.keys(statsData.genderDistribution);
        const genderData = Object.values(statsData.genderDistribution);

        let genderChart = new Chart(genderCtx, {
            type: 'pie',
            data: {
                labels: genderLabels,
                datasets: [{
                    data: genderData,
                    backgroundColor: ['#667eea', '#764ba2', '#f093fb']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 } } },
                    datalabels: {
                        color: '#fff',
                        font: { weight: 'bold', size: 12 },
                        formatter: function(value, context) {
                            if (value === 0) return '';
                            return value;
                        }
                    }
                }
            },
            plugins: [ChartDataLabels]
        });

        // Preparar datos de dimensiones agrupadas por dominio (GLOBAL para uso en filtros)
        const dimensionsData = {
            'Liderazgo y Relaciones Sociales': [
                { name: 'Características\nLiderazgo', key: 'dim_caracteristicas_liderazgo', domain: 'liderazgo' },
                { name: 'Relaciones\nSociales', key: 'dim_relaciones_sociales', domain: 'liderazgo' },
                { name: 'Retroalimentación\nDesempeño', key: 'dim_retroalimentacion', domain: 'liderazgo' },
                { name: 'Relación con\nColaboradores', key: 'dim_relacion_colaboradores', domain: 'liderazgo' }
            ],
            'Control sobre el Trabajo': [
                { name: 'Claridad\nde Rol', key: 'dim_claridad_rol', domain: 'control' },
                { name: 'Capacitación', key: 'dim_capacitacion', domain: 'control' },
                { name: 'Participación\ny Cambio', key: 'dim_participacion_manejo_cambio', domain: 'control' },
                { name: 'Oportunidades\nDesarrollo', key: 'dim_oportunidades_desarrollo', domain: 'control' },
                { name: 'Control y\nAutonomía', key: 'dim_control_autonomia', domain: 'control' }
            ],
            'Demandas del Trabajo': [
                { name: 'Demandas\nAmbientales', key: 'dim_demandas_ambientales', domain: 'demandas' },
                { name: 'Demandas\nEmocionales', key: 'dim_demandas_emocionales', domain: 'demandas' },
                { name: 'Demandas\nCuantitativas', key: 'dim_demandas_cuantitativas', domain: 'demandas' },
                { name: 'Influencia\nEntorno', key: 'dim_influencia_trabajo_entorno_extralaboral', domain: 'demandas' },
                { name: 'Exigencias\nResponsabilidad', key: 'dim_demandas_responsabilidad', domain: 'demandas' },
                { name: 'Carga\nMental', key: 'dim_demandas_carga_mental', domain: 'demandas' },
                { name: 'Consistencia\nde Rol', key: 'dim_consistencia_rol', domain: 'demandas' },
                { name: 'Jornada\nTrabajo', key: 'dim_demandas_jornada_trabajo', domain: 'demandas' }
            ],
            'Recompensas': [
                { name: 'Reconocimiento\ny Compensación', key: 'dim_reconocimiento_compensacion', domain: 'recompensas' },
                { name: 'Recompensas\nPertenencia', key: 'dim_recompensas_pertenencia', domain: 'recompensas' }
            ]
        };

        // Gráfico de Dimensiones Agrupadas por Dominio
        const dimensionsGroupedCtx = document.getElementById('dimensionsGroupedChart').getContext('2d');

        // Crear datasets - uno por dimensión
        const groupedDatasets = [];
        const allDimensions = [];

        // Recopilar todas las dimensiones con sus datos
        Object.entries(dimensionsData).forEach(([domainName, dimensions]) => {
            dimensions.forEach(dim => {
                const dimLevel = statsData.maxRisk?.[dim.key]?.nivel || 'sin_riesgo';
                const dimScore = statsData.maxRisk?.[dim.key]?.promedio || 0;

                allDimensions.push({
                    name: dim.name,
                    domain: domainName,
                    level: dimLevel,
                    score: dimScore,
                    color: riskColors[dimLevel]
                });
            });
        });

        // Crear un dataset por dimensión para el agrupamiento
        const labels = Object.keys(dimensionsData);
        let dimIndex = 0;

        Object.entries(dimensionsData).forEach(([domainName, dimensions]) => {
            dimensions.forEach((dim, idx) => {
                const dimLevel = statsData.maxRisk?.[dim.key]?.nivel || 'sin_riesgo';
                const dimScore = statsData.maxRisk?.[dim.key]?.promedio || 0;

                // Crear array de datos con valores solo para este dominio
                const data = labels.map((label, labelIdx) => {
                    if (label === domainName) {
                        return riskLevelValues[dimLevel];
                    }
                    return null;
                });

                groupedDatasets.push({
                    label: dim.name.replace(/\n/g, ' '),
                    data: data,
                    backgroundColor: riskColors[dimLevel],
                    borderColor: riskColors[dimLevel],
                    borderWidth: 1,
                    dimScore: dimScore,
                    dimLevel: dimLevel,
                    barThickness: 'flex',
                    maxBarThickness: 30
                });
            });
        });

        let dimensionsGroupedChart = new Chart(dimensionsGroupedCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: groupedDatasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'nearest',
                    intersect: true
                },
                scales: {
                    x: {
                        stacked: false,
                        ticks: {
                            font: { size: 11, weight: 'bold' },
                            color: '#495057'
                        },
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        max: 5.5,
                        ticks: {
                            stepSize: 1,
                            callback: function(value) {
                                const labels = ['', 'Sin Riesgo', 'Bajo', 'Medio', 'Alto', 'Muy Alto'];
                                return labels[value] || '';
                            },
                            font: { size: 10 }
                        },
                        grid: {
                            color: '#e9ecef'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            font: { size: 9 },
                            padding: 8,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        callbacks: {
                            title: function(context) {
                                return context[0].label + ' - ' + context[0].dataset.label;
                            },
                            label: function(context) {
                                const nivelLabels = {
                                    1: 'Sin Riesgo',
                                    2: 'Riesgo Bajo',
                                    3: 'Riesgo Medio',
                                    4: 'Riesgo Alto',
                                    5: 'Riesgo Muy Alto'
                                };
                                const nivel = nivelLabels[context.parsed.y];
                                const puntaje = context.dataset.dimScore;
                                return nivel + ' (Puntaje: ' + puntaje.toFixed(1) + ')';
                            }
                        }
                    },
                    datalabels: {
                        display: false  // Desactivar en gráfico agrupado por saturación visual
                    }
                }
            }
        });

        // Gráfico de Comparación Forma A vs Forma B
        const formsComparisonCtx = document.getElementById('formsComparisonChart').getContext('2d');

        // Calcular promedios por dominio para cada forma
        function calculateFormAverages() {
            const formaA = { liderazgo: 0, control: 0, demandas: 0, recompensas: 0, count: 0 };
            const formaB = { liderazgo: 0, control: 0, demandas: 0, recompensas: 0, count: 0 };

            allResults.forEach(r => {
                if (r.intralaboral_form_type === 'A') {
                    formaA.liderazgo += parseFloat(r.dom_liderazgo_puntaje || 0);
                    formaA.control += parseFloat(r.dom_control_puntaje || 0);
                    formaA.demandas += parseFloat(r.dom_demandas_puntaje || 0);
                    formaA.recompensas += parseFloat(r.dom_recompensas_puntaje || 0);
                    formaA.count++;
                } else if (r.intralaboral_form_type === 'B') {
                    formaB.liderazgo += parseFloat(r.dom_liderazgo_puntaje || 0);
                    formaB.control += parseFloat(r.dom_control_puntaje || 0);
                    formaB.demandas += parseFloat(r.dom_demandas_puntaje || 0);
                    formaB.recompensas += parseFloat(r.dom_recompensas_puntaje || 0);
                    formaB.count++;
                }
            });

            return {
                formaA: {
                    liderazgo: formaA.count > 0 ? formaA.liderazgo / formaA.count : 0,
                    control: formaA.count > 0 ? formaA.control / formaA.count : 0,
                    demandas: formaA.count > 0 ? formaA.demandas / formaA.count : 0,
                    recompensas: formaA.count > 0 ? formaA.recompensas / formaA.count : 0,
                    count: formaA.count
                },
                formaB: {
                    liderazgo: formaB.count > 0 ? formaB.liderazgo / formaB.count : 0,
                    control: formaB.count > 0 ? formaB.control / formaB.count : 0,
                    demandas: formaB.count > 0 ? formaB.demandas / formaB.count : 0,
                    recompensas: formaB.count > 0 ? formaB.recompensas / formaB.count : 0,
                    count: formaB.count
                }
            };
        }

        const formAverages = calculateFormAverages();

        let formsComparisonChart = new Chart(formsComparisonCtx, {
            type: 'bar',
            data: {
                labels: ['Liderazgo', 'Control', 'Demandas', 'Recompensas'],
                datasets: [
                    {
                        label: `Forma A (${formAverages.formaA.count} trabajadores)`,
                        data: [
                            formAverages.formaA.liderazgo,
                            formAverages.formaA.control,
                            formAverages.formaA.demandas,
                            formAverages.formaA.recompensas
                        ],
                        backgroundColor: 'rgba(158, 158, 158, 0.7)',  // Gris claro
                        borderColor: 'rgba(158, 158, 158, 1)',
                        borderWidth: 2
                    },
                    {
                        label: `Forma B (${formAverages.formaB.count} trabajadores)`,
                        data: [
                            formAverages.formaB.liderazgo,
                            formAverages.formaB.control,
                            formAverages.formaB.demandas,
                            formAverages.formaB.recompensas
                        ],
                        backgroundColor: 'rgba(66, 66, 66, 0.8)',  // Gris oscuro
                        borderColor: 'rgba(66, 66, 66, 1)',
                        borderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            stepSize: 10,
                            callback: function(value) {
                                return value.toFixed(0);
                            }
                        },
                        title: {
                            display: true,
                            text: 'Puntaje Promedio',
                            font: { size: 11, weight: 'bold' }
                        }
                    },
                    x: {
                        ticks: {
                            font: { size: 11, weight: 'bold' }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            boxWidth: 15,
                            font: { size: 11 },
                            padding: 15
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const puntaje = context.parsed.y;
                                const domain = context.label.toLowerCase();
                                const nivel = getDomainRiskLevel(puntaje, domain);
                                const nivelTexto = {
                                    'sin_riesgo': 'Sin Riesgo',
                                    'riesgo_bajo': 'Riesgo Bajo',
                                    'riesgo_medio': 'Riesgo Medio',
                                    'riesgo_alto': 'Riesgo Alto',
                                    'riesgo_muy_alto': 'Riesgo Muy Alto'
                                };
                                return context.dataset.label + ': ' + puntaje.toFixed(1) + ' (' + nivelTexto[nivel] + ')';
                            }
                        }
                    },
                    datalabels: {
                        anchor: 'end',
                        align: 'top',
                        color: '#333',
                        font: { weight: 'bold', size: 11 },
                        formatter: function(value, context) {
                            return value.toFixed(1);
                        }
                    }
                }
            },
            plugins: [ChartDataLabels]
        });

        // Gráfico de Top 5 Dimensiones Críticas
        const topDimensionsCtx = document.getElementById('topDimensionsChart').getContext('2d');

        // Función para calcular top 5 dimensiones con mayor riesgo
        function calculateTopDimensions(results) {
            const dimensionScores = [];

            // Recopilar todas las dimensiones con sus puntajes
            Object.values(dimensionsData).flat().forEach(dim => {
                let totalScore = 0;
                let count = 0;

                results.forEach(r => {
                    const score = parseFloat(r[dim.key + '_puntaje'] || 0);
                    if (score > 0) {
                        totalScore += score;
                        count++;
                    }
                });

                if (count > 0) {
                    const avgScore = totalScore / count;
                    const nivel = getDomainRiskLevel(avgScore, dim.domain);
                    dimensionScores.push({
                        name: dim.name.replace(/\n/g, ' '),
                        key: dim.key,
                        score: avgScore,
                        nivel: nivel,
                        color: riskColors[nivel]
                    });
                }
            });

            // Ordenar por puntaje descendente y tomar top 5
            return dimensionScores.sort((a, b) => b.score - a.score).slice(0, 5);
        }

        const topDimensions = calculateTopDimensions(allResults);

        let topDimensionsChart = new Chart(topDimensionsCtx, {
            type: 'bar',
            data: {
                labels: topDimensions.map(d => d.name),
                datasets: [{
                    label: 'Puntaje Promedio',
                    data: topDimensions.map(d => d.score),
                    backgroundColor: topDimensions.map(d => d.color),
                    borderColor: topDimensions.map(d => d.color),
                    borderWidth: 2
                }]
            },
            options: {
                indexAxis: 'y',  // Horizontal bar chart
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            stepSize: 10,
                            font: { size: 10 }
                        },
                        title: {
                            display: true,
                            text: 'Puntaje Promedio',
                            font: { size: 11, weight: 'bold' }
                        }
                    },
                    y: {
                        ticks: {
                            font: { size: 10, weight: 'bold' },
                            autoSkip: false
                        }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const dimension = topDimensions[context.dataIndex];
                                const nivelTexto = {
                                    'sin_riesgo': 'Sin Riesgo',
                                    'riesgo_bajo': 'Riesgo Bajo',
                                    'riesgo_medio': 'Riesgo Medio',
                                    'riesgo_alto': 'Riesgo Alto',
                                    'riesgo_muy_alto': 'Riesgo Muy Alto'
                                };
                                return 'Puntaje: ' + dimension.score.toFixed(1) + ' (' + nivelTexto[dimension.nivel] + ')';
                            }
                        }
                    },
                    datalabels: {
                        anchor: 'end',
                        align: 'end',
                        color: '#333',
                        font: { weight: 'bold', size: 11 },
                        formatter: function(value, context) {
                            return value.toFixed(1);
                        }
                    }
                }
            },
            plugins: [ChartDataLabels]
        });

        // Gráfico de Distribución por Departamento
        const departmentCtx = document.getElementById('departmentChart').getContext('2d');

        // Función para calcular top 10 departamentos con mayor riesgo
        function calculateTopDepartments(results) {
            const departmentScores = {};

            // Agrupar por departamento y calcular promedio de puntaje total intralaboral
            results.forEach(r => {
                const dept = r.department || 'Sin Departamento';
                const puntaje = parseFloat(r.intralaboral_total_puntaje || 0);

                if (!departmentScores[dept]) {
                    departmentScores[dept] = {
                        name: dept,
                        totalScore: 0,
                        count: 0
                    };
                }

                departmentScores[dept].totalScore += puntaje;
                departmentScores[dept].count++;
            });

            // Calcular promedios y determinar nivel de riesgo
            const departments = Object.values(departmentScores).map(dept => {
                const avgScore = dept.count > 0 ? dept.totalScore / dept.count : 0;

                // Determinar nivel de riesgo usando baremos corregidos (auditoría 2025-11-24)
                const nivel = getNivelRiesgoIntralaboral(avgScore);

                return {
                    name: dept.name,
                    score: avgScore,
                    count: dept.count,
                    nivel: nivel,
                    color: riskColors[nivel]
                };
            });

            // Ordenar por puntaje descendente y tomar top 10
            return departments.sort((a, b) => b.score - a.score).slice(0, 10);
        }

        const topDepartments = calculateTopDepartments(allResults);

        let departmentChart = new Chart(departmentCtx, {
            type: 'bar',
            data: {
                labels: topDepartments.map(d => d.name),
                datasets: [{
                    label: 'Puntaje Total Intralaboral',
                    data: topDepartments.map(d => d.score),
                    backgroundColor: topDepartments.map(d => d.color),
                    borderColor: topDepartments.map(d => d.color),
                    borderWidth: 2,
                    counts: topDepartments.map(d => d.count)
                }]
            },
            options: {
                indexAxis: 'y',  // Horizontal bar chart
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            stepSize: 10,
                            font: { size: 10 }
                        },
                        title: {
                            display: true,
                            text: 'Puntaje Total Intralaboral',
                            font: { size: 11, weight: 'bold' }
                        }
                    },
                    y: {
                        ticks: {
                            font: { size: 10, weight: 'bold' },
                            autoSkip: false
                        }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const dept = topDepartments[context.dataIndex];
                                const nivelTexto = {
                                    'sin_riesgo': 'Sin Riesgo',
                                    'riesgo_bajo': 'Riesgo Bajo',
                                    'riesgo_medio': 'Riesgo Medio',
                                    'riesgo_alto': 'Riesgo Alto',
                                    'riesgo_muy_alto': 'Riesgo Muy Alto'
                                };
                                return [
                                    'Puntaje: ' + dept.score.toFixed(1),
                                    'Nivel: ' + nivelTexto[dept.nivel],
                                    'Trabajadores: ' + dept.count
                                ];
                            }
                        }
                    },
                    datalabels: {
                        anchor: 'end',
                        align: 'end',
                        color: '#333',
                        font: { weight: 'bold', size: 11 },
                        formatter: function(value, context) {
                            return value.toFixed(1);
                        }
                    }
                }
            },
            plugins: [ChartDataLabels]
        });

        // Gráfico de Correlación Nivel Educativo vs Riesgo
        const educationCtx = document.getElementById('educationChart').getContext('2d');

        // Función para calcular riesgo por nivel educativo
        function calculateEducationRisk(results) {
            const educationScores = {};

            // Orden jerárquico de niveles educativos
            const educationOrder = [
                'Ninguno',
                'Primaria incompleta',
                'Primaria completa',
                'Bachillerato incompleto',
                'Bachillerato completo',
                'Técnico/Tecnológico incompleto',
                'Técnico/Tecnológico completo',
                'Profesional incompleto',
                'Profesional completo',
                'Postgrado incompleto',
                'Postgrado completo'
            ];

            // Agrupar por nivel educativo y calcular promedio de puntaje total intralaboral
            results.forEach(r => {
                const edu = r.education_level || 'Sin información';
                const puntaje = parseFloat(r.intralaboral_total_puntaje || 0);

                if (!educationScores[edu]) {
                    educationScores[edu] = {
                        name: edu,
                        totalScore: 0,
                        count: 0
                    };
                }

                educationScores[edu].totalScore += puntaje;
                educationScores[edu].count++;
            });

            // Calcular promedios y determinar nivel de riesgo
            const education = Object.values(educationScores).map(edu => {
                const avgScore = edu.count > 0 ? edu.totalScore / edu.count : 0;

                // Determinar nivel de riesgo usando baremos corregidos (auditoría 2025-11-24)
                const nivel = getNivelRiesgoIntralaboral(avgScore);

                return {
                    name: edu.name,
                    score: avgScore,
                    count: edu.count,
                    nivel: nivel,
                    color: riskColors[nivel]
                };
            });

            // Ordenar según jerarquía educativa
            return education.sort((a, b) => {
                const indexA = educationOrder.indexOf(a.name);
                const indexB = educationOrder.indexOf(b.name);

                // Si ambos están en el orden, usar ese orden
                if (indexA !== -1 && indexB !== -1) return indexA - indexB;
                // Si solo uno está en el orden, ese va primero
                if (indexA !== -1) return -1;
                if (indexB !== -1) return 1;
                // Si ninguno está en el orden, orden alfabético
                return a.name.localeCompare(b.name);
            });
        }

        const educationData = calculateEducationRisk(allResults);

        let educationChart = new Chart(educationCtx, {
            type: 'bar',
            data: {
                labels: educationData.map(e => e.name),
                datasets: [{
                    label: 'Puntaje Total Intralaboral',
                    data: educationData.map(e => e.score),
                    backgroundColor: educationData.map(e => e.color),
                    borderColor: educationData.map(e => e.color),
                    borderWidth: 2,
                    counts: educationData.map(e => e.count)
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
                            stepSize: 10,
                            font: { size: 10 }
                        },
                        title: {
                            display: true,
                            text: 'Puntaje Total Intralaboral',
                            font: { size: 11, weight: 'bold' }
                        }
                    },
                    x: {
                        ticks: {
                            font: { size: 9 },
                            maxRotation: 45,
                            minRotation: 45
                        }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const edu = educationData[context.dataIndex];
                                const nivelTexto = {
                                    'sin_riesgo': 'Sin Riesgo',
                                    'riesgo_bajo': 'Riesgo Bajo',
                                    'riesgo_medio': 'Riesgo Medio',
                                    'riesgo_alto': 'Riesgo Alto',
                                    'riesgo_muy_alto': 'Riesgo Muy Alto'
                                };
                                return [
                                    'Puntaje: ' + edu.score.toFixed(1),
                                    'Nivel: ' + nivelTexto[edu.nivel],
                                    'Trabajadores: ' + edu.count
                                ];
                            }
                        }
                    },
                    datalabels: {
                        anchor: 'end',
                        align: 'top',
                        color: '#333',
                        font: { weight: 'bold', size: 10 },
                        formatter: function(value, context) {
                            return value.toFixed(1);
                        }
                    }
                }
            },
            plugins: [ChartDataLabels]
        });

        // Gráfico de Distribución por Rango de Edad
        const ageCtx = document.getElementById('ageChart').getContext('2d');

        // Función para calcular riesgo por rango de edad
        function calculateAgeRisk(results) {
            const ageGroups = {
                '18-25': { min: 18, max: 25, totalScore: 0, count: 0 },
                '26-35': { min: 26, max: 35, totalScore: 0, count: 0 },
                '36-45': { min: 36, max: 45, totalScore: 0, count: 0 },
                '46-55': { min: 46, max: 55, totalScore: 0, count: 0 },
                '56-65': { min: 56, max: 65, totalScore: 0, count: 0 },
                '66+': { min: 66, max: 999, totalScore: 0, count: 0 }
            };

            // Agrupar por rango de edad y calcular promedio de puntaje total intralaboral
            const currentYear = new Date().getFullYear();

            results.forEach(r => {
                let age = null;

                // Intentar obtener la edad directamente
                if (r.age) {
                    age = parseInt(r.age);
                }
                // Si no existe, calcular desde birth_year
                else if (r.birth_year) {
                    const birthYear = parseInt(r.birth_year);
                    if (!isNaN(birthYear)) {
                        age = currentYear - birthYear;
                    }
                }

                const puntaje = parseFloat(r.intralaboral_total_puntaje || 0);

                if (age !== null && !isNaN(age) && !isNaN(puntaje) && age >= 18) {
                    // Determinar el grupo de edad
                    for (const [groupName, group] of Object.entries(ageGroups)) {
                        if (age >= group.min && age <= group.max) {
                            group.totalScore += puntaje;
                            group.count++;
                            break;
                        }
                    }
                }
            });

            // Calcular promedios y determinar nivel de riesgo
            const ageData = Object.keys(ageGroups).map(groupName => {
                const group = ageGroups[groupName];
                const avgScore = group.count > 0 ? group.totalScore / group.count : 0;

                // Determinar nivel de riesgo usando baremos corregidos (auditoría 2025-11-24)
                const nivel = getNivelRiesgoIntralaboral(avgScore);

                return {
                    name: groupName + ' años',
                    score: avgScore,
                    count: group.count,
                    nivel: nivel,
                    color: riskColors[nivel]
                };
            });

            // Filtrar solo grupos con datos
            return ageData.filter(g => g.count > 0);
        }

        const ageData = calculateAgeRisk(allResults);

        let ageChart = new Chart(ageCtx, {
            type: 'bar',
            data: {
                labels: ageData.map(a => a.name),
                datasets: [{
                    label: 'Puntaje Total Intralaboral',
                    data: ageData.map(a => a.score),
                    backgroundColor: ageData.map(a => a.color),
                    borderColor: ageData.map(a => a.color),
                    borderWidth: 2,
                    counts: ageData.map(a => a.count)
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
                            stepSize: 10,
                            font: { size: 10 }
                        },
                        title: {
                            display: true,
                            text: 'Puntaje Total Intralaboral',
                            font: { size: 11, weight: 'bold' }
                        }
                    },
                    x: {
                        ticks: {
                            font: { size: 10, weight: 'bold' }
                        }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const age = ageData[context.dataIndex];
                                const nivelTexto = {
                                    'sin_riesgo': 'Sin Riesgo',
                                    'riesgo_bajo': 'Riesgo Bajo',
                                    'riesgo_medio': 'Riesgo Medio',
                                    'riesgo_alto': 'Riesgo Alto',
                                    'riesgo_muy_alto': 'Riesgo Muy Alto'
                                };
                                return [
                                    'Puntaje: ' + age.score.toFixed(1),
                                    'Nivel: ' + nivelTexto[age.nivel],
                                    'Trabajadores: ' + age.count
                                ];
                            }
                        }
                    },
                    datalabels: {
                        anchor: 'end',
                        align: 'top',
                        color: '#333',
                        font: { weight: 'bold', size: 11 },
                        formatter: function(value, context) {
                            return value.toFixed(1);
                        }
                    }
                }
            },
            plugins: [ChartDataLabels]
        });

        // Función para limpiar filtros
        function clearAllFilters() {
            document.querySelectorAll('select[id^="filter_"]').forEach(select => {
                select.value = '';
            });

            // Resetear dimensiones cuando se limpian filtros
            const selectDimension = document.getElementById('filter_dimension');
            selectDimension.innerHTML = '<option value="">Todas las dimensiones</option>';
            Object.values(dimensionesPorDominio).flat().forEach(dim => {
                const option = document.createElement('option');
                option.value = dim.value;
                option.textContent = dim.text;
                selectDimension.appendChild(option);
            });

            dataTable.search('').columns().search('').draw();
            applyFilters();
        }

        // Función helper para obtener nivel de riesgo de un puntaje
        function getDomainRiskLevel(puntaje, domain) {
            // Baremos simplificados para JavaScript (promedio entre Forma A y B)
            const baremos = {
                liderazgo: [
                    { max: 6.0, nivel: 'sin_riesgo' },
                    { max: 18.1, nivel: 'riesgo_bajo' },
                    { max: 32.0, nivel: 'riesgo_medio' },
                    { max: 46.0, nivel: 'riesgo_alto' },
                    { max: 100, nivel: 'riesgo_muy_alto' }
                ],
                control: [
                    { max: 17.8, nivel: 'sin_riesgo' },
                    { max: 29.1, nivel: 'riesgo_bajo' },
                    { max: 35.7, nivel: 'riesgo_medio' },
                    { max: 44.6, nivel: 'riesgo_alto' },
                    { max: 100, nivel: 'riesgo_muy_alto' }
                ],
                demandas: [
                    { max: 29.6, nivel: 'sin_riesgo' },
                    { max: 36.1, nivel: 'riesgo_bajo' },
                    { max: 41.5, nivel: 'riesgo_medio' },
                    { max: 47.4, nivel: 'riesgo_alto' },
                    { max: 100, nivel: 'riesgo_muy_alto' }
                ],
                recompensas: [
                    { max: 7.5, nivel: 'sin_riesgo' },
                    { max: 15.0, nivel: 'riesgo_bajo' },
                    { max: 25.0, nivel: 'riesgo_medio' },
                    { max: 35.0, nivel: 'riesgo_alto' },
                    { max: 100, nivel: 'riesgo_muy_alto' }
                ]
            };

            const domainBaremo = baremos[domain] || baremos.liderazgo;
            for (let range of domainBaremo) {
                if (puntaje <= range.max) {
                    return range.nivel;
                }
            }
            return 'riesgo_muy_alto';
        }

        // Función para actualizar gráficos
        function updateCharts(filteredResults, selectedDominio, selectedDimension) {
            // Recalcular estadísticas con resultados filtrados
            const newStats = calculateStats(filteredResults);

            // Calcular distribución de riesgo según el filtro seleccionado
            let riskDistribution = { sin_riesgo: 0, riesgo_bajo: 0, riesgo_medio: 0, riesgo_alto: 0, riesgo_muy_alto: 0 };

            if (selectedDimension) {
                // Distribución por dimensión específica
                filteredResults.forEach(r => {
                    const puntaje = parseFloat(r[selectedDimension + '_puntaje'] || 0);
                    const dimensionInfo = Object.values(dimensionsData).flat().find(d => d.key === selectedDimension);
                    if (dimensionInfo) {
                        const nivel = getDomainRiskLevel(puntaje, dimensionInfo.domain);
                        if (riskDistribution[nivel] !== undefined) {
                            riskDistribution[nivel]++;
                        }
                    }
                });
            } else if (selectedDominio) {
                // Distribución por dominio específico
                filteredResults.forEach(r => {
                    const puntaje = parseFloat(r['dom_' + selectedDominio + '_puntaje'] || 0);
                    const nivel = getDomainRiskLevel(puntaje, selectedDominio);
                    if (riskDistribution[nivel] !== undefined) {
                        riskDistribution[nivel]++;
                    }
                });
            } else {
                // Distribución del total intralaboral (sin filtro)
                riskDistribution = newStats.riskDistribution;
            }

            // Actualizar gráfico de riesgo
            riskChart.data.datasets[0].data = [
                riskDistribution.sin_riesgo,
                riskDistribution.riesgo_bajo,
                riskDistribution.riesgo_medio,
                riskDistribution.riesgo_alto,
                riskDistribution.riesgo_muy_alto
            ];
            riskChart.update();

            // Actualizar cards de distribución de riesgo
            document.querySelector('[data-stat-risk="sin_riesgo"]').textContent = riskDistribution.sin_riesgo;
            document.querySelector('[data-stat-risk="riesgo_bajo"]').textContent = riskDistribution.riesgo_bajo;
            document.querySelector('[data-stat-risk="riesgo_medio"]').textContent = riskDistribution.riesgo_medio;
            document.querySelector('[data-stat-risk="riesgo_alto"]').textContent = riskDistribution.riesgo_alto;
            document.querySelector('[data-stat-risk="riesgo_muy_alto"]').textContent = riskDistribution.riesgo_muy_alto;

            // Actualizar gráfico de dominios con niveles - filtrar por dominio seleccionado
            const allDomains = [
                { key: 'liderazgo', label: 'Liderazgo', nivel: newStats.maxRisk?.liderazgo?.nivel || 'sin_riesgo', puntaje: newStats.maxRisk?.liderazgo?.promedio || 0 },
                { key: 'control', label: 'Control', nivel: newStats.maxRisk?.control?.nivel || 'sin_riesgo', puntaje: newStats.maxRisk?.control?.promedio || 0 },
                { key: 'demandas', label: 'Demandas', nivel: newStats.maxRisk?.demandas?.nivel || 'sin_riesgo', puntaje: newStats.maxRisk?.demandas?.promedio || 0 },
                { key: 'recompensas', label: 'Recompensas', nivel: newStats.maxRisk?.recompensas?.nivel || 'sin_riesgo', puntaje: newStats.maxRisk?.recompensas?.promedio || 0 }
            ];

            // Filtrar dominios si hay un dominio seleccionado
            const visibleDomains = selectedDominio ? allDomains.filter(d => d.key === selectedDominio) : allDomains;

            domainsChart.data.labels = visibleDomains.map(d => d.label);
            domainsChart.data.datasets[0].data = visibleDomains.map(d => riskLevelValues[d.nivel]);
            domainsChart.data.datasets[0].backgroundColor = visibleDomains.map(d => riskColors[d.nivel]);
            domainsChart.data.datasets[0].puntajes = visibleDomains.map(d => d.puntaje);
            domainsChart.update();

            // Actualizar gráfico de género
            const newGenderLabels = Object.keys(newStats.genderDistribution);
            const newGenderData = Object.values(newStats.genderDistribution);
            genderChart.data.labels = newGenderLabels;
            genderChart.data.datasets[0].data = newGenderData;
            genderChart.update();

            // Actualizar gráfico de dimensiones agrupadas - filtrar por dominio y dimensión
            const filteredDimensionsData = {};

            // Si hay dimensión seleccionada, solo mostrar esa dimensión
            if (selectedDimension) {
                // Encontrar el dominio de la dimensión seleccionada
                let dimensionDomain = null;
                Object.entries(dimensionsData).forEach(([domainName, dimensions]) => {
                    dimensions.forEach(dim => {
                        if (dim.key === selectedDimension) {
                            dimensionDomain = domainName;
                        }
                    });
                });

                if (dimensionDomain) {
                    filteredDimensionsData[dimensionDomain] = Object.entries(dimensionsData)
                        .find(([domainName]) => domainName === dimensionDomain)[1]
                        .filter(dim => dim.key === selectedDimension);
                }
            }
            // Si solo hay dominio seleccionado, mostrar todas sus dimensiones
            else if (selectedDominio) {
                const dominioNameMap = {
                    'liderazgo': 'Liderazgo y Relaciones Sociales',
                    'control': 'Control sobre el Trabajo',
                    'demandas': 'Demandas del Trabajo',
                    'recompensas': 'Recompensas'
                };
                const domainName = dominioNameMap[selectedDominio];
                if (domainName && dimensionsData[domainName]) {
                    filteredDimensionsData[domainName] = dimensionsData[domainName];
                }
            }
            // Si no hay filtros, mostrar todas
            else {
                Object.assign(filteredDimensionsData, dimensionsData);
            }

            // Reconstruir datasets filtrados
            const newGroupedDatasets = [];
            const newLabels = Object.keys(filteredDimensionsData);

            Object.entries(filteredDimensionsData).forEach(([domainName, dimensions]) => {
                dimensions.forEach((dim) => {
                    const dimScore = newStats.maxRisk[dim.key]?.promedio || 0;
                    const dimLevel = getDomainRiskLevel(dimScore, dim.domain);

                    const data = newLabels.map((label) => {
                        if (label === domainName) {
                            return riskLevelValues[dimLevel];
                        }
                        return null;
                    });

                    newGroupedDatasets.push({
                        label: dim.name.replace(/\n/g, ' '),
                        data: data,
                        backgroundColor: riskColors[dimLevel],
                        borderColor: riskColors[dimLevel],
                        borderWidth: 1,
                        dimScore: dimScore,
                        dimLevel: dimLevel,
                        barThickness: 'flex',
                        maxBarThickness: 30
                    });
                });
            });

            dimensionsGroupedChart.data.labels = newLabels;
            dimensionsGroupedChart.data.datasets = newGroupedDatasets;
            dimensionsGroupedChart.update();

            // Actualizar gráfico de comparación Forma A vs B con resultados filtrados
            const newFormAverages = calculateFormAveragesFromResults(filteredResults);

            formsComparisonChart.data.datasets[0].label = `Forma A (${newFormAverages.formaA.count} trabajadores)`;
            formsComparisonChart.data.datasets[0].data = [
                newFormAverages.formaA.liderazgo,
                newFormAverages.formaA.control,
                newFormAverages.formaA.demandas,
                newFormAverages.formaA.recompensas
            ];

            formsComparisonChart.data.datasets[1].label = `Forma B (${newFormAverages.formaB.count} trabajadores)`;
            formsComparisonChart.data.datasets[1].data = [
                newFormAverages.formaB.liderazgo,
                newFormAverages.formaB.control,
                newFormAverages.formaB.demandas,
                newFormAverages.formaB.recompensas
            ];

            formsComparisonChart.update();

            // Actualizar gráfico de Top 5 Dimensiones Críticas con resultados filtrados
            const newTopDimensions = calculateTopDimensions(filteredResults);

            topDimensionsChart.data.labels = newTopDimensions.map(d => d.name);
            topDimensionsChart.data.datasets[0].data = newTopDimensions.map(d => d.score);
            topDimensionsChart.data.datasets[0].backgroundColor = newTopDimensions.map(d => d.color);
            topDimensionsChart.data.datasets[0].borderColor = newTopDimensions.map(d => d.color);
            topDimensionsChart.update();

            // Actualizar gráfico de Top 10 Departamentos con resultados filtrados
            const newTopDepartments = calculateTopDepartments(filteredResults);

            departmentChart.data.labels = newTopDepartments.map(d => d.name);
            departmentChart.data.datasets[0].data = newTopDepartments.map(d => d.score);
            departmentChart.data.datasets[0].backgroundColor = newTopDepartments.map(d => d.color);
            departmentChart.data.datasets[0].borderColor = newTopDepartments.map(d => d.color);
            departmentChart.data.datasets[0].counts = newTopDepartments.map(d => d.count);
            departmentChart.update();

            // Actualizar gráfico de Nivel Educativo con resultados filtrados
            const newEducationData = calculateEducationRisk(filteredResults);

            educationChart.data.labels = newEducationData.map(e => e.name);
            educationChart.data.datasets[0].data = newEducationData.map(e => e.score);
            educationChart.data.datasets[0].backgroundColor = newEducationData.map(e => e.color);
            educationChart.data.datasets[0].borderColor = newEducationData.map(e => e.color);
            educationChart.data.datasets[0].counts = newEducationData.map(e => e.count);
            educationChart.update();

            // Actualizar gráfico de Rango de Edad con resultados filtrados
            const newAgeData = calculateAgeRisk(filteredResults);

            ageChart.data.labels = newAgeData.map(a => a.name);
            ageChart.data.datasets[0].data = newAgeData.map(a => a.score);
            ageChart.data.datasets[0].backgroundColor = newAgeData.map(a => a.color);
            ageChart.data.datasets[0].borderColor = newAgeData.map(a => a.color);
            ageChart.data.datasets[0].counts = newAgeData.map(a => a.count);
            ageChart.update();
        }

        // Función auxiliar para calcular promedios de formas a partir de resultados filtrados
        function calculateFormAveragesFromResults(results) {
            const formaA = { liderazgo: 0, control: 0, demandas: 0, recompensas: 0, count: 0 };
            const formaB = { liderazgo: 0, control: 0, demandas: 0, recompensas: 0, count: 0 };

            results.forEach(r => {
                if (r.intralaboral_form_type === 'A') {
                    formaA.liderazgo += parseFloat(r.dom_liderazgo_puntaje || 0);
                    formaA.control += parseFloat(r.dom_control_puntaje || 0);
                    formaA.demandas += parseFloat(r.dom_demandas_puntaje || 0);
                    formaA.recompensas += parseFloat(r.dom_recompensas_puntaje || 0);
                    formaA.count++;
                } else if (r.intralaboral_form_type === 'B') {
                    formaB.liderazgo += parseFloat(r.dom_liderazgo_puntaje || 0);
                    formaB.control += parseFloat(r.dom_control_puntaje || 0);
                    formaB.demandas += parseFloat(r.dom_demandas_puntaje || 0);
                    formaB.recompensas += parseFloat(r.dom_recompensas_puntaje || 0);
                    formaB.count++;
                }
            });

            return {
                formaA: {
                    liderazgo: formaA.count > 0 ? formaA.liderazgo / formaA.count : 0,
                    control: formaA.count > 0 ? formaA.control / formaA.count : 0,
                    demandas: formaA.count > 0 ? formaA.demandas / formaA.count : 0,
                    recompensas: formaA.count > 0 ? formaA.recompensas / formaA.count : 0,
                    count: formaA.count
                },
                formaB: {
                    liderazgo: formaB.count > 0 ? formaB.liderazgo / formaB.count : 0,
                    control: formaB.count > 0 ? formaB.control / formaB.count : 0,
                    demandas: formaB.count > 0 ? formaB.demandas / formaB.count : 0,
                    recompensas: formaB.count > 0 ? formaB.recompensas / formaB.count : 0,
                    count: formaB.count
                }
            };
        }

        // Función para calcular estadísticas con MAX RISK
        function calculateStats(results) {
            const stats = {
                riskDistribution: { sin_riesgo: 0, riesgo_bajo: 0, riesgo_medio: 0, riesgo_alto: 0, riesgo_muy_alto: 0 },
                maxRisk: {},
                genderDistribution: {}
            };

            // Separar por forma
            const resultsA = results.filter(r => r.intralaboral_form_type === 'A');
            const resultsB = results.filter(r => r.intralaboral_form_type === 'B');

            // Función helper para calcular promedio de un campo
            const calcAvg = (arr, field) => {
                if (arr.length === 0) return 0;
                const sum = arr.reduce((acc, r) => acc + parseFloat(r[field] || 0), 0);
                return parseFloat((sum / arr.length).toFixed(2));
            };

            // Función helper para obtener el peor resultado entre A y B
            const getMaxRisk = (fieldA, fieldB, domainKey) => {
                const avgA = calcAvg(resultsA, fieldA);
                const avgB = calcAvg(resultsB, fieldB);
                const nivelA = getDomainRiskLevel(avgA, domainKey);
                const nivelB = getDomainRiskLevel(avgB, domainKey);

                // Comparar niveles (mayor = peor)
                const riskOrder = { sin_riesgo: 1, riesgo_bajo: 2, riesgo_medio: 3, riesgo_alto: 4, riesgo_muy_alto: 5 };
                const orderA = riskOrder[nivelA] || 0;
                const orderB = riskOrder[nivelB] || 0;

                if (orderA >= orderB) {
                    return { promedio: avgA, nivel: nivelA, forma_origen: 'A' };
                } else {
                    return { promedio: avgB, nivel: nivelB, forma_origen: 'B' };
                }
            };

            // Calcular MAX RISK para dominios
            stats.maxRisk.liderazgo = getMaxRisk('dom_liderazgo_puntaje', 'dom_liderazgo_puntaje', 'liderazgo');
            stats.maxRisk.control = getMaxRisk('dom_control_puntaje', 'dom_control_puntaje', 'control');
            stats.maxRisk.demandas = getMaxRisk('dom_demandas_puntaje', 'dom_demandas_puntaje', 'demandas');
            stats.maxRisk.recompensas = getMaxRisk('dom_recompensas_puntaje', 'dom_recompensas_puntaje', 'recompensas');

            // Calcular MAX RISK para dimensiones
            Object.values(dimensionsData).flat().forEach(dim => {
                stats.maxRisk[dim.key] = getMaxRisk(dim.key + '_puntaje', dim.key + '_puntaje', dim.domain);
            });

            // Distribución de riesgo y género
            results.forEach(r => {
                if (r.intralaboral_total_nivel && stats.riskDistribution[r.intralaboral_total_nivel] !== undefined) {
                    stats.riskDistribution[r.intralaboral_total_nivel]++;
                }
                const gender = r.gender || 'No especificado';
                stats.genderDistribution[gender] = (stats.genderDistribution[gender] || 0) + 1;
            });

            return stats;
        }

        // Aplicar filtros
        document.querySelectorAll('select[id^="filter_"]').forEach(select => {
            select.addEventListener('change', function() {
                applyFilters();
            });
        });

        function applyFilters() {
            const filters = {
                dominio: document.getElementById('filter_dominio').value,
                dimension: document.getElementById('filter_dimension').value,
                nivel_riesgo: document.getElementById('filter_nivel_riesgo').value,
                gender: document.getElementById('filter_gender').value,
                form_type: document.getElementById('filter_form_type').value,
                department: document.getElementById('filter_department').value,
                position: document.getElementById('filter_position').value,
                position_type: document.getElementById('filter_position_type').value,
                education: document.getElementById('filter_education').value,
                marital_status: document.getElementById('filter_marital_status').value,
                contract_type: document.getElementById('filter_contract_type').value,
                city: document.getElementById('filter_city').value,
                stratum: document.getElementById('filter_stratum').value,
                housing_type: document.getElementById('filter_housing_type').value,
                time_in_company: document.getElementById('filter_time_in_company').value
            };

            // Filtrar resultados por TODOS los filtros (demográficos, dominio, dimensión, nivel de riesgo)
            const filteredResults = allResults.filter(r => {
                // Filtros demográficos y de nivel de riesgo
                const basicFilter = (!filters.nivel_riesgo || r.intralaboral_total_nivel === filters.nivel_riesgo) &&
                       (!filters.gender || r.gender === filters.gender) &&
                       (!filters.form_type || r.intralaboral_form_type === filters.form_type) &&
                       (!filters.department || r.department === filters.department) &&
                       (!filters.position || r.position === filters.position) &&
                       (!filters.position_type || r.position_type === filters.position_type) &&
                       (!filters.education || r.education_level === filters.education) &&
                       (!filters.marital_status || r.marital_status === filters.marital_status) &&
                       (!filters.contract_type || r.contract_type === filters.contract_type) &&
                       (!filters.city || r.city_residence === filters.city) &&
                       (!filters.stratum || r.stratum === filters.stratum) &&
                       (!filters.housing_type || r.housing_type === filters.housing_type) &&
                       (!filters.time_in_company || r.time_in_company_type === filters.time_in_company);

                if (!basicFilter) return false;

                // Filtro de dominio
                if (filters.dominio) {
                    const dominioPuntajeKey = 'dom_' + filters.dominio + '_puntaje';
                    if (!r[dominioPuntajeKey] || r[dominioPuntajeKey] === 0) return false;
                }

                // Filtro de dimensión
                if (filters.dimension) {
                    const dimensionPuntajeKey = filters.dimension + '_puntaje';
                    if (!r[dimensionPuntajeKey] || r[dimensionPuntajeKey] === 0) return false;
                }

                return true;
            });

            // Actualizar tabla con resultados filtrados
            dataTable.clear();
            filteredResults.forEach(r => {
                // Generar badge de estrés con colores correctos
                let estresBadge = '';
                if (r.estres_total_nivel) {
                    const estresColors = {
                        'muy_bajo': '#28a745',
                        'bajo': '#93C572',
                        'medio': '#ffc107',
                        'alto': '#fd7e14',
                        'muy_alto': '#dc3545'
                    };
                    const estresTextos = {
                        'muy_bajo': 'MUY BAJO',
                        'bajo': 'BAJO',
                        'medio': 'MEDIO',
                        'alto': 'ALTO',
                        'muy_alto': 'MUY ALTO'
                    };
                    const color = estresColors[r.estres_total_nivel] || '#6c757d';
                    const texto = estresTextos[r.estres_total_nivel] || 'N/A';
                    estresBadge = `<span class="badge" style="background-color: ${color}; color: white;">${texto}</span>`;
                } else {
                    estresBadge = '<span class="badge bg-secondary">N/A</span>';
                }

                const row = `<tr>
                    <td>${r.worker_name || ''}</td>
                    <td>${r.worker_document || ''}</td>
                    <td>${r.gender || ''}</td>
                    <td><span class="badge bg-secondary">${r.intralaboral_form_type || ''}</span></td>
                    <td>${r.position || ''}</td>
                    <td>${r.department || ''}</td>
                    <td><span class="risk-badge risk-${r.intralaboral_total_nivel}">${r.intralaboral_total_nivel.replace(/_/g, ' ')}</span></td>
                    <td><span class="risk-badge risk-${r.extralaboral_total_nivel}">${r.extralaboral_total_nivel.replace(/_/g, ' ')}</span></td>
                    <td>${estresBadge}</td>
                    <td><span class="risk-badge risk-${r.puntaje_total_general_nivel}">${r.puntaje_total_general_nivel.replace(/_/g, ' ')}</span></td>
                    <td class="text-center">
                        ${getActionButton(r.worker_id, r.intralaboral_form_type)}
                    </td>
                </tr>`;
                dataTable.row.add($(row)).draw(false);
            });

            // Actualizar gráficos con los mismos resultados filtrados
            updateCharts(filteredResults, filters.dominio, filters.dimension);
        }

        function exportToExcel() {
            alert('Funcionalidad de exportación a Excel en desarrollo');
        }
    </script>
</body>
</html>
