<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold">
                <i class="bi bi-graph-up-arrow text-primary me-2"></i>
                Dashboard de Satisfacción del Servicio
            </h2>
            <p class="text-muted">Análisis de encuestas de satisfacción completadas</p>
        </div>
    </div>

    <!-- Estadísticas Generales -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                                <i class="bi bi-clipboard-check text-primary fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Encuestas</h6>
                            <h3 class="mb-0 fw-bold"><?= $totalSurveys ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 rounded-circle p-3">
                                <i class="bi bi-star-fill text-success fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Satisfacción General</h6>
                            <h3 class="mb-0 fw-bold">
                                <?= $avgGeneral ?>
                                <small class="text-muted fs-6">/5.0</small>
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                                <i class="bi bi-emoji-smile text-warning fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Consultor</h6>
                            <h3 class="mb-0 fw-bold">
                                <?= $avgQ2 ?>
                                <small class="text-muted fs-6">/5.0</small>
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 rounded-circle p-3">
                                <i class="bi bi-hand-thumbs-up text-info fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Recomendarían</h6>
                            <h3 class="mb-0 fw-bold">
                                <?= $avgQ4 ?>
                                <small class="text-muted fs-6">/5.0</small>
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row mb-4">
        <!-- Promedio por Pregunta -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-bar-chart-line text-primary me-2"></i>
                        Promedio por Pregunta
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="questionAveragesChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Distribución de Satisfacción -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-pie-chart text-primary me-2"></i>
                        Distribución de Satisfacción
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="distributionChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Ranking por Empresa -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-trophy text-warning me-2"></i>
                        Ranking de Satisfacción por Empresa
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (count($companyStats) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="50">#</th>
                                        <th>Empresa</th>
                                        <th class="text-center" width="150">Encuestas</th>
                                        <th class="text-center" width="150">Promedio</th>
                                        <th width="300">Rating</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($companyStats as $index => $stat): ?>
                                        <tr>
                                            <td class="text-center">
                                                <?php if ($index === 0): ?>
                                                    <i class="bi bi-trophy-fill text-warning fs-5"></i>
                                                <?php elseif ($index === 1): ?>
                                                    <i class="bi bi-trophy-fill text-secondary fs-5"></i>
                                                <?php elseif ($index === 2): ?>
                                                    <i class="bi bi-trophy-fill text-danger fs-5"></i>
                                                <?php else: ?>
                                                    <?= $index + 1 ?>
                                                <?php endif; ?>
                                            </td>
                                            <td class="fw-bold"><?= esc($stat['company_name']) ?></td>
                                            <td class="text-center">
                                                <span class="badge bg-primary"><?= $stat['total_surveys'] ?></span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge <?= $stat['average_score'] >= 4.5 ? 'bg-success' : ($stat['average_score'] >= 4.0 ? 'bg-info' : ($stat['average_score'] >= 3.0 ? 'bg-warning' : 'bg-danger')) ?>">
                                                    <?= $stat['average_score'] ?> / 5.0
                                                </span>
                                            </td>
                                            <td>
                                                <div class="progress" style="height: 25px;">
                                                    <div class="progress-bar <?= $stat['average_score'] >= 4.5 ? 'bg-success' : ($stat['average_score'] >= 4.0 ? 'bg-info' : ($stat['average_score'] >= 3.0 ? 'bg-warning' : 'bg-danger')) ?>"
                                                        role="progressbar"
                                                        style="width: <?= ($stat['average_score'] / 5) * 100 ?>%"
                                                        aria-valuenow="<?= $stat['average_score'] ?>"
                                                        aria-valuemin="0"
                                                        aria-valuemax="5">
                                                        <?= $stat['average_score'] ?>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            No hay datos de satisfacción disponibles aún.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Listado de Encuestas Recientes -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-clock-history text-primary me-2"></i>
                        Encuestas Recientes
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (count($surveys) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover" id="surveysTable">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Empresa</th>
                                        <th>Servicio</th>
                                        <th>Respondió</th>
                                        <th class="text-center">Promedio</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($surveys as $survey): ?>
                                        <?php
                                        $avg = ($survey['question_1'] + $survey['question_2'] +
                                                $survey['question_3'] + $survey['question_4'] +
                                                $survey['question_5']) / 5;
                                        $avgRounded = round($avg, 2);
                                        ?>
                                        <tr>
                                            <td><?= date('d/m/Y H:i', strtotime($survey['completed_at'])) ?></td>
                                            <td><?= esc($survey['company_name']) ?></td>
                                            <td><?= esc($survey['service_name']) ?></td>
                                            <td><?= esc($survey['respondent_name']) ?></td>
                                            <td class="text-center">
                                                <span class="badge <?= $avgRounded >= 4.5 ? 'bg-success' : ($avgRounded >= 4.0 ? 'bg-info' : ($avgRounded >= 3.0 ? 'bg-warning' : 'bg-danger')) ?>">
                                                    <?= $avgRounded ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <a href="<?= base_url('satisfaction/view/' . $survey['service_id']) ?>"
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye me-1"></i>Ver Detalle
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            No hay encuestas completadas aún.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    // DataTable
    $(document).ready(function() {
        $('#surveysTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
            },
            order: [[0, 'desc']],
            pageLength: 10
        });
    });

    // Gráfico de Promedios por Pregunta
    const ctxQuestions = document.getElementById('questionAveragesChart').getContext('2d');
    new Chart(ctxQuestions, {
        type: 'bar',
        data: {
            labels: [
                'Satisfacción\nGeneral',
                'Profesionalismo\nConsultor',
                'Informes\nExpectativas',
                'Recomendaría\nServicio',
                'Facilidad\nNavegación'
            ],
            datasets: [{
                label: 'Promedio',
                data: [<?= $avgQ1 ?>, <?= $avgQ2 ?>, <?= $avgQ3 ?>, <?= $avgQ4 ?>, <?= $avgQ5 ?>],
                backgroundColor: [
                    'rgba(54, 162, 235, 0.6)',
                    'rgba(255, 206, 86, 0.6)',
                    'rgba(75, 192, 192, 0.6)',
                    'rgba(153, 102, 255, 0.6)',
                    'rgba(255, 159, 64, 0.6)'
                ],
                borderColor: [
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 5,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Promedio: ' + context.parsed.y + ' / 5.0';
                        }
                    }
                }
            }
        }
    });

    // Gráfico de Distribución
    const ctxDistribution = document.getElementById('distributionChart').getContext('2d');
    new Chart(ctxDistribution, {
        type: 'doughnut',
        data: {
            labels: [
                'Muy Alto (4.5-5.0)',
                'Alto (4.0-4.4)',
                'Medio (3.0-3.9)',
                'Bajo (2.0-2.9)',
                'Muy Bajo (1.0-1.9)'
            ],
            datasets: [{
                data: [
                    <?= $distribution['muy_alto'] ?>,
                    <?= $distribution['alto'] ?>,
                    <?= $distribution['medio'] ?>,
                    <?= $distribution['bajo'] ?>,
                    <?= $distribution['muy_bajo'] ?>
                ],
                backgroundColor: [
                    'rgba(40, 167, 69, 0.8)',
                    'rgba(23, 162, 184, 0.8)',
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(255, 108, 0, 0.8)',
                    'rgba(220, 53, 69, 0.8)'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = <?= $totalSurveys ?>;
                            const value = context.parsed;
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return context.label + ': ' + value + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
</script>

<?= $this->endSection() ?>
