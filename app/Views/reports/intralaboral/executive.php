<?php
// Helper function para colores de riesgo
function getRiskColor($nivel) {
    $colors = [
        'sin_riesgo' => 'success',
        'riesgo_bajo' => 'success',
        'riesgo_medio' => 'warning',
        'riesgo_alto' => 'danger',
        'riesgo_muy_alto' => 'danger',
        'muy_bajo' => 'success',
        'bajo' => 'success',
        'medio' => 'warning',
        'alto' => 'danger',
        'muy_alto' => 'danger'
    ];
    return $colors[$nivel] ?? 'secondary';
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
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar-custom {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .executive-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .stat-card {
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border: none;
            text-align: center;
        }
        .stat-card .value {
            font-size: 2.5rem;
            font-weight: bold;
        }
        .stat-card .label {
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .section-title {
            border-left: 4px solid #667eea;
            padding-left: 15px;
            margin-bottom: 20px;
        }
        .risk-badge {
            padding: 8px 15px;
            border-radius: 5px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
            min-width: 110px;
            text-align: center;
        }
        .risk-sin_riesgo { background-color: #28a745; color: white; }
        .risk-riesgo_bajo { background-color: #7dce82; color: white; }
        .risk-riesgo_medio { background-color: #ffc107; color: #333; }
        .risk-riesgo_alto { background-color: #fd7e14; color: white; }
        .risk-riesgo_muy_alto { background-color: #dc3545; color: white; }
        .alert-box {
            border-left: 5px solid #fd7e14;
            background-color: #fff3cd;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .worker-card {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
            transition: all 0.3s;
        }
        .worker-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <!-- Top Navbar -->
    <nav class="navbar navbar-custom navbar-expand-lg p-3">
        <div class="container-fluid">
            <a href="<?= base_url('reports/intralaboral/' . $service['id']) ?>" class="btn btn-outline-secondary btn-sm me-3">
                <i class="fas fa-arrow-left"></i> Volver al Dashboard
            </a>
            <h5 class="mb-0"><i class="fas fa-bolt me-2 text-warning"></i><?= $title ?></h5>
            <div class="ms-auto">
                <button class="btn btn-success btn-sm me-2" onclick="window.print()">
                    <i class="fas fa-print me-1"></i>Imprimir
                </button>
                <button class="btn btn-danger btn-sm"
                    data-download-type="pdf"
                    data-service-id="<?= $service['id'] ?>"
                    data-url="<?= base_url('reports/export-executive-pdf/' . $service['id'] . '/intralaboral') ?>">
                    <i class="fas fa-file-pdf me-1"></i>Descargar PDF
                </button>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <div class="container-fluid p-4">
        <!-- Executive Header -->
        <div class="executive-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="fw-bold mb-2">
                        <i class="fas fa-chart-line me-3"></i>Informe Ejecutivo
                    </h2>
                    <h4 class="mb-2"><?= esc($service['service_name']) ?></h4>
                    <p class="mb-0">
                        <i class="fas fa-building me-2"></i><?= esc($service['company_name']) ?>
                        <span class="mx-3">|</span>
                        <i class="fas fa-calendar me-2"></i><?= date('d/m/Y', strtotime($service['service_date'])) ?>
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="badge bg-light text-dark p-3" style="font-size: 1.2rem;">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                        <?= $totalRisk ?> Trabajadores en Riesgo
                    </div>
                </div>
            </div>
        </div>

        <!-- Totales Globales -->
        <div class="row mb-4">
            <div class="col-12">
                <h5 class="section-title">Resumen General de Resultados</h5>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-primary text-white">
                    <div class="label">Total Participantes</div>
                    <div class="value"><?= $totales['participantes'] ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-info text-white">
                    <div class="label">Promedio Intralaboral</div>
                    <div class="value"><?= number_format($totales['promedio_intralaboral'], 1) ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-success text-white">
                    <div class="label">Promedio Extralaboral</div>
                    <div class="value"><?= number_format($totales['promedio_extralaboral'], 1) ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-warning text-dark">
                    <div class="label">Promedio Estrés</div>
                    <div class="value"><?= number_format($totales['promedio_estres'], 1) ?></div>
                </div>
            </div>
        </div>

        <!-- Alerta de Riesgo -->
        <?php if ($totalRisk > 0): ?>
        <div class="alert-box">
            <h5 class="mb-2">
                <i class="fas fa-exclamation-circle text-warning me-2"></i>
                Atención Requerida
            </h5>
            <p class="mb-0">
                Se han identificado <strong><?= $totalRisk ?> trabajadores</strong> con niveles de riesgo
                <span class="badge bg-warning text-dark">Medio</span>,
                <span class="badge bg-danger">Alto</span> o
                <span class="badge bg-dark">Muy Alto</span>
                que requieren intervención prioritaria.
            </p>
        </div>
        <?php endif; ?>

        <!-- Trabajadores que Requieren Atención -->
        <div class="row">
            <div class="col-12">
                <h5 class="section-title">
                    <i class="fas fa-users-medical me-2"></i>
                    Trabajadores que Requieren Atención Inmediata
                </h5>

                <?php if (empty($riskResults)): ?>
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                            <h5 class="text-success">¡Excelente!</h5>
                            <p class="text-muted mb-0">No hay trabajadores con niveles de riesgo medio, alto o muy alto en el factor intralaboral.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php
                        // Ordenar por nivel de riesgo (muy alto primero)
                        $riesgoOrden = ['riesgo_muy_alto' => 5, 'riesgo_alto' => 4, 'riesgo_medio' => 3];
                        usort($riskResults, function($a, $b) use ($riesgoOrden) {
                            $ordenA = $riesgoOrden[$a['intralaboral_total_nivel']] ?? 0;
                            $ordenB = $riesgoOrden[$b['intralaboral_total_nivel']] ?? 0;
                            return $ordenB - $ordenA;
                        });
                        ?>

                        <?php foreach ($riskResults as $result): ?>
                            <div class="col-md-6">
                                <div class="worker-card">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            <h6 class="fw-bold mb-1">
                                                <i class="fas fa-user me-2 text-primary"></i>
                                                <?= esc($result['worker_name']) ?>
                                            </h6>
                                            <p class="mb-1 small text-muted">
                                                <strong>Doc:</strong> <?= esc($result['worker_document']) ?>
                                            </p>
                                            <p class="mb-1 small text-muted">
                                                <strong>Cargo:</strong> <?= esc($result['position']) ?>
                                            </p>
                                            <p class="mb-0 small text-muted">
                                                <strong>Depto:</strong> <?= esc($result['department']) ?>
                                            </p>
                                        </div>
                                        <div class="col-md-6 text-end">
                                            <div class="mb-2">
                                                <span class="risk-badge risk-<?= esc($result['intralaboral_total_nivel']) ?>">
                                                    <?= ucfirst(str_replace('_', ' ', $result['intralaboral_total_nivel'])) ?>
                                                </span>
                                            </div>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= base_url('recommendations/dimension/liderazgo?worker=' . $result['worker_id']) ?>"
                                                   class="btn btn-outline-primary"
                                                   title="Ver Recomendaciones">
                                                    <i class="fas fa-calendar-check"></i> Plan de Acción
                                                </a>
                                                <a href="<?= base_url('workers/results/' . $result['worker_id']) ?>"
                                                   class="btn btn-outline-secondary"
                                                   target="_blank"
                                                   title="Ver Resultados Detallados">
                                                    <i class="fas fa-chart-bar"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Detalle de Dominios -->
                                    <div class="row mt-3 pt-3 border-top">
                                        <div class="col-6 col-md-3 text-center">
                                            <small class="text-muted d-block">Liderazgo</small>
                                            <strong class="text-<?= getRiskColor($result['dom_liderazgo_nivel']) ?>">
                                                <?= number_format($result['dom_liderazgo_puntaje'], 1) ?>
                                            </strong>
                                        </div>
                                        <div class="col-6 col-md-3 text-center">
                                            <small class="text-muted d-block">Control</small>
                                            <strong class="text-<?= getRiskColor($result['dom_control_nivel']) ?>">
                                                <?= number_format($result['dom_control_puntaje'], 1) ?>
                                            </strong>
                                        </div>
                                        <div class="col-6 col-md-3 text-center">
                                            <small class="text-muted d-block">Demandas</small>
                                            <strong class="text-<?= getRiskColor($result['dom_demandas_nivel']) ?>">
                                                <?= number_format($result['dom_demandas_puntaje'], 1) ?>
                                            </strong>
                                        </div>
                                        <div class="col-6 col-md-3 text-center">
                                            <small class="text-muted d-block">Recompensas</small>
                                            <strong class="text-<?= getRiskColor($result['dom_recompensas_nivel']) ?>">
                                                <?= number_format($result['dom_recompensas_puntaje'], 1) ?>
                                            </strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Nota sobre Participación -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="fas fa-info-circle text-primary me-2"></i>
                            Nota sobre Participación
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php
                        // Obtener estadísticas de participación
                        $workerModel = new \App\Models\WorkerModel();
                        $allWorkers = $workerModel->where('battery_service_id', $service['id'])->findAll();
                        $totalInvitados = count($allWorkers);
                        $totalCompletados = count(array_filter($allWorkers, fn($w) => $w['status'] === 'completado'));
                        $totalNoParticipo = count(array_filter($allWorkers, fn($w) => $w['status'] === 'no_participo'));
                        $totalAbandonados = count(array_filter($allWorkers, fn($w) => $w['status'] === 'abandonado'));
                        $porcentajeParticipacion = ($totalInvitados > 0) ? round(($totalCompletados / $totalInvitados) * 100, 1) : 0;

                        // Agrupar motivos de no participación
                        $motivos = [];
                        foreach ($allWorkers as $w) {
                            if ($w['status'] === 'no_participo' && !empty($w['non_participation_reason'])) {
                                if (!isset($motivos[$w['non_participation_reason']])) {
                                    $motivos[$w['non_participation_reason']] = 0;
                                }
                                $motivos[$w['non_participation_reason']]++;
                            }
                        }
                        ?>

                        <div class="row">
                            <div class="col-md-8">
                                <p class="mb-2">
                                    <strong>Total de trabajadores invitados:</strong> <?= $totalInvitados ?>
                                </p>
                                <p class="mb-2">
                                    <strong>Trabajadores que completaron la batería:</strong>
                                    <?= $totalCompletados ?> (<?= $porcentajeParticipacion ?>%)
                                </p>

                                <?php if ($totalNoParticipo > 0): ?>
                                    <p class="mb-2">
                                        <strong>No participaron:</strong> <?= $totalNoParticipo ?>
                                        <?php if (!empty($motivos)): ?>
                                            - Motivos:
                                            <?php
                                            $motivosTexto = [];
                                            foreach ($motivos as $motivo => $cantidad) {
                                                $motivosTexto[] = "$motivo ($cantidad)";
                                            }
                                            echo implode(', ', $motivosTexto);
                                            ?>
                                        <?php endif; ?>
                                    </p>
                                <?php endif; ?>

                                <?php if ($totalAbandonados > 0): ?>
                                    <p class="mb-2">
                                        <strong>Abandonaron:</strong> <?= $totalAbandonados ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="badge bg-primary p-3" style="font-size: 1.5rem;">
                                    <?= $porcentajeParticipacion ?>%
                                    <div style="font-size: 0.7rem;">Participación</div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <p class="text-muted small mb-0">
                            <i class="fas fa-check-circle text-success me-1"></i>
                            Los resultados presentados en este informe se basan únicamente en los
                            <strong><?= $totalCompletados ?> trabajadores que completaron</strong>
                            satisfactoriamente la batería de riesgo psicosocial.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="row mt-4">
            <div class="col-12 text-center">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="mb-3">
                            <i class="fas fa-lightbulb text-warning me-2"></i>
                            Acciones Recomendadas
                        </h5>
                        <p class="text-muted mb-4">
                            Consulta las recomendaciones globales y planes de acción sugeridos para cada dimensión de riesgo identificada.
                        </p>
                        <div class="btn-group btn-group-lg">
                            <a href="<?= base_url('recommendations/service/' . $service['id']) ?>"
                               class="btn btn-primary">
                                <i class="fas fa-clipboard-list me-2"></i>Ver Recomendaciones Globales
                            </a>
                            <a href="<?= base_url('reports/intralaboral/' . $service['id']) ?>"
                               class="btn btn-outline-primary">
                                <i class="fas fa-chart-line me-2"></i>Ver Dashboard Completo
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url('js/satisfaction-check.js') ?>"></script>
    <script>
        function exportPDF() {
            alert('Funcionalidad de exportación a PDF en desarrollo');
        }
    </script>
</body>
</html>
