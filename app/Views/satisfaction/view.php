<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <a href="<?= base_url('satisfaction/dashboard') ?>" class="btn btn-outline-secondary btn-sm mb-3">
                <i class="bi bi-arrow-left me-1"></i>Volver al Dashboard
            </a>
            <h2 class="fw-bold">
                <i class="bi bi-star-fill text-warning me-2"></i>
                Detalle de Encuesta de Satisfacción
            </h2>
        </div>
    </div>

    <?php if ($survey): ?>
        <!-- Información del Servicio -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            Información del Servicio
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-2">
                                    <strong>Servicio:</strong>
                                    <?= esc($service['service_name']) ?>
                                </p>
                                <p class="mb-2">
                                    <strong>Fecha del Servicio:</strong>
                                    <?= date('d/m/Y', strtotime($service['service_date'])) ?>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2">
                                    <strong>Encuesta Completada:</strong>
                                    <?= date('d/m/Y H:i', strtotime($survey['completed_at'])) ?>
                                </p>
                                <p class="mb-2">
                                    <strong>Promedio General:</strong>
                                    <span class="badge <?= $averageScore >= 4.5 ? 'bg-success' : ($averageScore >= 4.0 ? 'bg-info' : ($averageScore >= 3.0 ? 'bg-warning' : 'bg-danger')) ?> fs-6">
                                        <?= $averageScore ?> / 5.0
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico de Radar -->
        <div class="row mb-4">
            <div class="col-lg-6 mx-auto">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 text-center fw-bold">
                            <i class="bi bi-radar text-primary me-2"></i>
                            Evaluación Visual
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="radarChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Respuestas Detalladas -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 fw-bold">
                            <i class="bi bi-list-check text-primary me-2"></i>
                            Respuestas Detalladas
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Pregunta 1 -->
                        <div class="mb-4 p-3 bg-light rounded">
                            <h6 class="fw-bold mb-3">1. ¿Qué tan satisfecho está con el servicio recibido?</h6>
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <span class="badge bg-primary fs-5"><?= $survey['question_1'] ?> / 5</span>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="progress" style="height: 30px;">
                                        <div class="progress-bar bg-primary" role="progressbar"
                                            style="width: <?= ($survey['question_1'] / 5) * 100 ?>%">
                                            <?= $survey['question_1'] == 1 ? 'Muy insatisfecho' :
                                                ($survey['question_1'] == 2 ? 'Insatisfecho' :
                                                ($survey['question_1'] == 3 ? 'Neutral' :
                                                ($survey['question_1'] == 4 ? 'Satisfecho' : 'Muy satisfecho'))) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pregunta 2 -->
                        <div class="mb-4 p-3 bg-light rounded">
                            <h6 class="fw-bold mb-3">2. ¿El consultor fue claro y profesional durante el proceso?</h6>
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <span class="badge bg-warning fs-5"><?= $survey['question_2'] ?> / 5</span>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="progress" style="height: 30px;">
                                        <div class="progress-bar bg-warning" role="progressbar"
                                            style="width: <?= ($survey['question_2'] / 5) * 100 ?>%">
                                            <?= $survey['question_2'] == 1 ? 'Totalmente en desacuerdo' :
                                                ($survey['question_2'] == 2 ? 'En desacuerdo' :
                                                ($survey['question_2'] == 3 ? 'Neutral' :
                                                ($survey['question_2'] == 4 ? 'De acuerdo' : 'Totalmente de acuerdo'))) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pregunta 3 -->
                        <div class="mb-4 p-3 bg-light rounded">
                            <h6 class="fw-bold mb-3">3. ¿Los informes cumplen con sus expectativas?</h6>
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <span class="badge bg-info fs-5"><?= $survey['question_3'] ?> / 5</span>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="progress" style="height: 30px;">
                                        <div class="progress-bar bg-info" role="progressbar"
                                            style="width: <?= ($survey['question_3'] / 5) * 100 ?>%">
                                            <?= $survey['question_3'] == 1 ? 'Totalmente en desacuerdo' :
                                                ($survey['question_3'] == 2 ? 'En desacuerdo' :
                                                ($survey['question_3'] == 3 ? 'Neutral' :
                                                ($survey['question_3'] == 4 ? 'De acuerdo' : 'Totalmente de acuerdo'))) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pregunta 4 -->
                        <div class="mb-4 p-3 bg-light rounded">
                            <h6 class="fw-bold mb-3">4. ¿Recomendaría nuestros servicios a otras empresas?</h6>
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <span class="badge bg-success fs-5"><?= $survey['question_4'] ?> / 5</span>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="progress" style="height: 30px;">
                                        <div class="progress-bar bg-success" role="progressbar"
                                            style="width: <?= ($survey['question_4'] / 5) * 100 ?>%">
                                            <?= $survey['question_4'] == 1 ? 'Definitivamente no' :
                                                ($survey['question_4'] == 2 ? 'Probablemente no' :
                                                ($survey['question_4'] == 3 ? 'No estoy seguro' :
                                                ($survey['question_4'] == 4 ? 'Probablemente sí' : 'Definitivamente sí'))) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pregunta 5 -->
                        <div class="mb-4 p-3 bg-light rounded">
                            <h6 class="fw-bold mb-3">5. ¿Qué tan fácil fue navegar y entender los resultados?</h6>
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <span class="badge bg-danger fs-5"><?= $survey['question_5'] ?> / 5</span>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="progress" style="height: 30px;">
                                        <div class="progress-bar bg-danger" role="progressbar"
                                            style="width: <?= ($survey['question_5'] / 5) * 100 ?>%">
                                            <?= $survey['question_5'] == 1 ? 'Muy difícil' :
                                                ($survey['question_5'] == 2 ? 'Difícil' :
                                                ($survey['question_5'] == 3 ? 'Neutral' :
                                                ($survey['question_5'] == 4 ? 'Fácil' : 'Muy fácil'))) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Comentarios -->
        <?php if (!empty($survey['comments'])): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="mb-0 fw-bold">
                                <i class="bi bi-chat-left-quote text-primary me-2"></i>
                                Comentarios del Cliente
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-light border-start border-primary border-4">
                                <p class="mb-0" style="white-space: pre-wrap;"><?= esc($survey['comments']) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-2"></i>
            No se encontró la encuesta de satisfacción para este servicio.
        </div>
    <?php endif; ?>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Gráfico de Radar
    const ctxRadar = document.getElementById('radarChart').getContext('2d');
    new Chart(ctxRadar, {
        type: 'radar',
        data: {
            labels: [
                'Satisfacción General',
                'Profesionalismo Consultor',
                'Informes',
                'Recomendaría',
                'Facilidad Navegación'
            ],
            datasets: [{
                label: 'Evaluación',
                data: [
                    <?= $survey['question_1'] ?>,
                    <?= $survey['question_2'] ?>,
                    <?= $survey['question_3'] ?>,
                    <?= $survey['question_4'] ?>,
                    <?= $survey['question_5'] ?>
                ],
                backgroundColor: 'rgba(102, 126, 234, 0.2)',
                borderColor: 'rgba(102, 126, 234, 1)',
                pointBackgroundColor: 'rgba(102, 126, 234, 1)',
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: 'rgba(102, 126, 234, 1)',
                borderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                r: {
                    beginAtZero: true,
                    max: 5,
                    min: 0,
                    ticks: {
                        stepSize: 1
                    },
                    pointLabels: {
                        font: {
                            size: 12,
                            weight: 'bold'
                        }
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
                            return 'Puntuación: ' + context.parsed.r + ' / 5';
                        }
                    }
                }
            }
        }
    });
</script>

<?= $this->endSection() ?>
