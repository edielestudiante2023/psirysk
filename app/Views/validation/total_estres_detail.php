<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validación Total Estrés</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8f9fa; }
        .validation-header { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 2rem; margin-bottom: 2rem; border-radius: 8px; }
        .stats-card { border-left: 4px solid #f093fb; }
        .badge-grupo1 { background-color: #0d6efd; }
        .badge-grupo2 { background-color: #198754; }
        .badge-grupo3 { background-color: #ffc107; color: #000; }
        .baremo-actual { background-color: #fff3cd; font-weight: bold; border-left: 4px solid #ffc107; }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="validation-header shadow-sm">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-2"><i class="fas fa-brain me-2"></i>Validación Total Estrés - Forma <?= esc($formType) ?></h2>
                    <p class="mb-0 opacity-75">
                        <i class="fas fa-building me-2"></i><?= esc($service['company_name']) ?> - <?= esc($service['service_name']) ?> |
                        <?= $totalWorkers ?> participantes (Forma <?= esc($formType) ?>)
                    </p>
                </div>
                <button onclick="window.close()" class="btn btn-light">
                    <i class="fas fa-times me-2"></i>Cerrar
                </button>
            </div>
        </div>

        <!-- Estadísticas Generales -->
        <?php
        $match = $result['validation_status'] === 'ok';
        $calculatedScore = $result['calculated_score'];

        // Determinar el rango donde cae el puntaje calculado
        $nivelActual = null;
        foreach ($baremos as $nivel => $rango) {
            if ($calculatedScore >= $rango['min'] && $calculatedScore <= $rango['max']) {
                $nivelActual = $nivel;
                break;
            }
        }
        ?>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card shadow-sm h-100 stats-card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <?php if ($match): ?>
                                <i class="fas fa-check-circle text-success me-2"></i>Validación Exitosa
                            <?php else: ?>
                                <i class="fas fa-exclamation-triangle text-danger me-2"></i>Discrepancia Detectada
                            <?php endif; ?>
                        </h5>
                        <div class="row mt-3">
                            <div class="col-6">
                                <p class="mb-1 text-muted small">Puntaje Calculado</p>
                                <h4 class="text-primary"><?= number_format($calculatedScore, 2) ?></h4>
                            </div>
                            <div class="col-6">
                                <p class="mb-1 text-muted small">Puntaje en BD</p>
                                <h4 class="text-info"><?= number_format($result['db_score'], 2) ?></h4>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-12">
                                <p class="mb-1 text-muted small">Diferencia</p>
                                <h4 class="<?= $match ? 'text-success' : 'text-danger' ?>">
                                    <?= number_format($result['difference'], 2) ?>
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm h-100 stats-card">
                    <div class="card-body text-center">
                        <p class="mb-1 text-muted small">Nivel de Riesgo</p>
                        <h3>
                            <span class="badge bg-<?= $baremos[$nivelActual]['color'] ?? 'secondary' ?> badge-status">
                                <?= esc($baremos[$nivelActual]['label'] ?? 'N/A') ?>
                            </span>
                        </h3>
                        <small class="text-muted">Según baremos oficiales (Tabla 6)</small>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm h-100 stats-card">
                    <div class="card-body text-center">
                        <p class="mb-1 text-muted small">Total Participantes</p>
                        <h1 class="display-4 text-primary mb-0"><?= $totalWorkers ?></h1>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Baremos (Tabla 6) -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Baremos de la Tercera Versión del "Cuestionario para la Evaluación del Estrés"</h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">
                    <i class="fas fa-info-circle me-1"></i>
                    <strong>Puntaje total transformado:</strong> Según Resolución 2404/2019 - Tabla 6
                </p>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Nivel de Síntomas de Estrés</th>
                                <th class="text-center">Rango de Puntaje</th>
                                <th class="text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($baremos as $nivel => $rango): ?>
                                <tr class="<?= $nivel === $nivelActual ? 'baremo-actual' : '' ?>">
                                    <td>
                                        <span class="badge bg-<?= $rango['color'] ?> me-2"><?= esc($rango['label']) ?></span>
                                    </td>
                                    <td class="text-center">
                                        <?= number_format($rango['min'], 1) ?> - <?= number_format($rango['max'], 1) ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($nivel === $nivelActual): ?>
                                            <i class="fas fa-arrow-left text-warning me-2"></i>
                                            <strong>Puntaje actual: <?= number_format($calculatedScore, 2) ?></strong>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Resumen de Validación -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>Resumen de Validación</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-3">
                    <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Metodología de Validación</h6>
                    <p class="mb-2">El validador calcula el puntaje de cada trabajador individual aplicando la metodología oficial (a-b-c-d), luego promedia todos los puntajes transformados individuales y compara con el promedio almacenado en la base de datos.</p>
                    <code class="d-block bg-white p-2">
                        Por cada worker: Puntaje = [(Promedio ítems 1-8 × 4) + (Promedio ítems 9-12 × 3) + (Promedio ítems 13-22 × 2) + (Promedio ítems 23-31 × 1)] / <?= $factorTotal ?> × 100
                    </code>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card bg-light h-100">
                            <div class="card-body">
                                <h6 class="card-title">Transformación Final</h6>
                                <p class="mb-2"><strong>Factor de Transformación:</strong> <?= $factorTotal ?></p>
                                <p class="mb-2"><strong>Promedio de Puntajes Individuales:</strong></p>
                                <p class="mb-0"><strong>Puntaje Transformado:</strong>
                                    <span class="badge bg-success fs-6"><?= number_format($calculatedScore, 2) ?></span>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light h-100">
                            <div class="card-body">
                                <h6 class="card-title">Comparación con BD</h6>
                                <p class="mb-2"><strong>Calculado (Validador):</strong> <?= number_format($calculatedScore, 2) ?></p>
                                <p class="mb-2"><strong>Promedio BD:</strong> <?= number_format($result['db_score'], 2) ?></p>
                                <p class="mb-0"><strong>Diferencia:</strong>
                                    <span class="badge <?= $match ? 'bg-success' : 'bg-danger' ?>">
                                        <?= number_format($result['difference'], 2) ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grupos de Calificación (Tabla 4) -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-layer-group me-2"></i>Grupos de Calificación (Tabla 4)</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php
                    $gruposData = [
                        [
                            'numero' => 1,
                            'items' => '1, 2, 3, 9, 13, 14, 15, 23, 24',
                            'valores' => 'Siempre=9, Casi siempre=6, A veces=3, Nunca=0',
                            'color' => 'primary'
                        ],
                        [
                            'numero' => 2,
                            'items' => '4, 5, 6, 10, 11, 16, 17, 18, 19, 25, 26, 27, 28',
                            'valores' => 'Siempre=6, Casi siempre=4, A veces=2, Nunca=0',
                            'color' => 'success'
                        ],
                        [
                            'numero' => 3,
                            'items' => '7, 8, 12, 20, 21, 22, 29, 30, 31',
                            'valores' => 'Siempre=3, Casi siempre=2, A veces=1, Nunca=0',
                            'color' => 'warning'
                        ]
                    ];
                    ?>
                    <?php foreach ($gruposData as $grupo): ?>
                        <div class="col-md-4 mb-3">
                            <div class="card h-100 border-<?= $grupo['color'] ?>">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <span class="badge bg-<?= $grupo['color'] ?> <?= $grupo['color'] === 'warning' ? 'text-dark' : '' ?>">
                                            GRUPO <?= $grupo['numero'] ?>
                                        </span>
                                    </h6>
                                    <p class="card-text small">
                                        <strong>Ítems:</strong> <?= $grupo['items'] ?><br>
                                        <strong>Valores:</strong> <?= $grupo['valores'] ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Factores de Multiplicación (Tabla 4 - Paso 3) -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Factores de Multiplicación (Tabla 4 - Paso 3)</h5>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    <i class="fas fa-info-circle me-1"></i>
                    El Puntaje Bruto se calcula promediando los ítems de cada bloque y luego aplicando el factor correspondiente.
                </p>
                <div class="row">
                    <?php foreach ($gruposMultiplicacion as $grupo): ?>
                        <div class="col-md-3 mb-3">
                            <div class="card h-100 border-<?= $grupo['color'] ?>">
                                <div class="card-body text-center">
                                    <h6 class="card-title">
                                        <span class="badge bg-<?= $grupo['color'] ?> <?= $grupo['color'] === 'warning' ? 'text-dark' : '' ?>">
                                            <?= $grupo['label'] ?>
                                        </span>
                                    </h6>
                                    <p class="card-text small">
                                        <strong>Ítems:</strong> <?= $grupo['items'][0] ?>-<?= end($grupo['items']) ?><br>
                                        <strong>Factor:</strong> <span class="fs-5 fw-bold text-<?= $grupo['color'] ?>">×<?= $grupo['factor'] ?></span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="alert alert-success mt-3 mb-0">
                    <strong>Fórmula del Puntaje Bruto (por trabajador individual):</strong><br>
                    <code class="bg-white p-2 d-block mt-2">
                        Puntaje Bruto = (Promedio ítems 1-8 × 4) + (Promedio ítems 9-12 × 3) + (Promedio ítems 13-22 × 2) + (Promedio ítems 23-31 × 1)
                    </code>
                    <small class="text-muted d-block mt-2">
                        <strong>Importante:</strong> El factor se aplica al <strong>promedio del bloque</strong>, NO a cada ítem individual.
                    </small>
                </div>
            </div>
        </div>

        <!-- Botón Volver -->
        <div class="text-center mt-4 mb-4">
            <button onclick="window.close()" class="btn btn-secondary btn-lg">
                <i class="fas fa-arrow-left me-2"></i>Volver
            </button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
