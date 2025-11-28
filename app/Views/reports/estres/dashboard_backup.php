<?php
/**
 * Dashboard Estrés - Vista Simplificada
 * El cuestionario de estrés no tiene dominios/dimensiones como intralaboral
 * Solo muestra: nivel de riesgo general, distribución por niveles y tabla de trabajadores
 */
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
    <style>
        body { background-color: #f8f9fa; font-size: 0.9rem; }
        .stat-card { border-radius: 10px; padding: 20px; margin-bottom: 15px; box-shadow: 0 3px 10px rgba(0,0,0,0.08); border: none; }
        .stat-card .icon { font-size: 2rem; opacity: 0.8; }
        .chart-container { position: relative; height: 300px; margin-bottom: 15px; }
        .risk-badge { padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; display: inline-block; min-width: 90px; text-align: center; }
        .risk-muy_bajo { background-color: #28a745; color: white; }
        .risk-bajo { background-color: #7dce82; color: white; }
        .risk-medio { background-color: #ffc107; color: #333; }
        .risk-alto { background-color: #fd7e14; color: white; }
        .risk-muy_alto { background-color: #dc3545; color: white; }
    </style>
</head>
<body>
    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg bg-white shadow-sm p-3">
        <div class="container-fluid">
            <a href="<?= base_url('battery-services/' . $service['id']) ?>" class="btn btn-outline-secondary btn-sm me-3">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <h5 class="mb-0"><i class="fas fa-heartbeat me-2 text-warning"></i><?= $title ?></h5>
            <div class="ms-auto">
                <a href="<?= base_url('reports/export-pdf/' . $service['id'] . '/estres') ?>" class="btn btn-danger btn-sm me-2">
                    <i class="fas fa-file-pdf me-1"></i>PDF Completo
                </a>
                <a href="<?= base_url('reports/estres/executive/' . $service['id']) ?>" class="btn btn-warning btn-sm">
                    <i class="fas fa-bolt me-1"></i>Informe Ejecutivo
                </a>
            </div>
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
                        <span class="badge bg-warning">Total: <?= $totalWorkers ?> trabajadores</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas Generales -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stat-card bg-warning text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1" style="font-size: 0.75rem;">Promedio Estrés</h6>
                            <h4 class="fw-bold mb-0"><?= isset($stats['average']) ? number_format($stats['average'], 1) : '0.0' ?></h4>
                        </div>
                        <i class="fas fa-heartbeat icon"></i>
                    </div>
                </div>
            </div>
            <?php
            $riskLevels = ['muy_bajo' => 'Muy Bajo', 'bajo' => 'Bajo', 'medio' => 'Medio', 'alto' => 'Alto', 'muy_alto' => 'Muy Alto'];
            $riskColors = ['muy_bajo' => 'success', 'bajo' => 'info', 'medio' => 'warning', 'alto' => 'orange', 'muy_alto' => 'danger'];
            $index = 0;
            foreach ($riskLevels as $level => $label):
                if ($index >= 4) break; // Solo mostrar 4 cards más
            ?>
            <div class="col-md-3">
                <div class="card stat-card border">
                    <h6 class="text-uppercase mb-1" style="font-size: 0.75rem;"><?= $label ?></h6>
                    <h4 class="fw-bold mb-0 text-<?= $riskColors[$level] ?>">
                        <?= isset($stats['distribution'][$level]) ? $stats['distribution'][$level] : 0 ?>
                    </h4>
                    <small class="text-muted">trabajadores</small>
                </div>
            </div>
            <?php
                $index++;
            endforeach;
            ?>
        </div>

        <!-- Gráficas -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Distribución por Nivel de Riesgo</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="riskDistributionChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="fas fa-venus-mars me-2"></i>Distribución por Género</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="genderChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Resultados -->
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="fas fa-table me-2"></i>Resultados Detallados</h6>
            </div>
            <div class="card-body">
                <table id="resultsTable" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Documento</th>
                            <th>Género</th>
                            <th>Cargo</th>
                            <th>Puntaje</th>
                            <th>Nivel de Riesgo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $result): ?>
                        <tr>
                            <td><?= esc($result['name']) ?></td>
                            <td><?= esc($result['document']) ?></td>
                            <td><?= esc($result['gender'] ?? 'N/A') ?></td>
                            <td><?= esc($result['position'] ?? 'N/A') ?></td>
                            <td><?= number_format($result['estres_total_puntaje'] ?? 0, 1) ?></td>
                            <td>
                                <span class="risk-badge risk-<?= $result['estres_total_nivel'] ?? 'muy_bajo' ?>">
                                    <?= ucfirst(str_replace('_', ' ', $result['estres_total_nivel'] ?? 'N/A')) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
    // Gráfica de Distribución por Nivel de Riesgo
    const riskCtx = document.getElementById('riskDistributionChart').getContext('2d');
    new Chart(riskCtx, {
        type: 'doughnut',
        data: {
            labels: ['Muy Bajo', 'Bajo', 'Medio', 'Alto', 'Muy Alto'],
            datasets: [{
                data: [
                    <?= $stats['distribution']['muy_bajo'] ?? 0 ?>,
                    <?= $stats['distribution']['bajo'] ?? 0 ?>,
                    <?= $stats['distribution']['medio'] ?? 0 ?>,
                    <?= $stats['distribution']['alto'] ?? 0 ?>,
                    <?= $stats['distribution']['muy_alto'] ?? 0 ?>
                ],
                backgroundColor: ['#28a745', '#7dce82', '#ffc107', '#fd7e14', '#dc3545']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    // Gráfica por Género
    const genderCtx = document.getElementById('genderChart').getContext('2d');
    <?php
    $genderCounts = [];
    foreach ($results as $r) {
        $gender = $r['gender'] ?? 'No especificado';
        $genderCounts[$gender] = ($genderCounts[$gender] ?? 0) + 1;
    }
    ?>
    new Chart(genderCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_keys($genderCounts)) ?>,
            datasets: [{
                label: 'Trabajadores',
                data: <?= json_encode(array_values($genderCounts)) ?>,
                backgroundColor: '#667eea'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            }
        }
    });

    // DataTable
    $(document).ready(function() {
        $('#resultsTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
            },
            pageLength: 25
        });
    });
    </script>
</body>
</html>
