<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validación Total Intralaboral - Forma <?= esc($formType) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8f9fa; }
        .validation-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 2rem; margin-bottom: 2rem; border-radius: 8px; }
        .stats-card { border-left: 4px solid #667eea; }
        .badge-sin-riesgo { background-color: #28a745; }
        .badge-riesgo-bajo { background-color: #17a2b8; }
        .badge-riesgo-medio { background-color: #ffc107; color: #000; }
        .badge-riesgo-alto { background-color: #fd7e14; }
        .badge-riesgo-muy-alto { background-color: #dc3545; }
        .match { background-color: #d4edda !important; }
        .mismatch { background-color: #f8d7da !important; }
        .baremo-table th { background-color: #28a745; color: white; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="validation-header shadow-sm">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-2"><i class="fas fa-chart-line me-2"></i>Validación Total Intralaboral</h2>
                    <h4 class="mb-1">Cuestionario de Factores de Riesgo Psicosocial Intralaboral - Forma <?= esc($formType) ?></h4>
                    <p class="mb-0 opacity-75">
                        <i class="fas fa-building me-2"></i><?= esc($service['service_name']) ?> |
                        <?= $totalWorkers ?> participantes
                    </p>
                </div>
                <button onclick="window.close()" class="btn btn-light">
                    <i class="fas fa-times me-2"></i>Cerrar
                </button>
            </div>
        </div>

        <!-- Estadísticas y Baremos -->
        <div class="row mb-4">
            <!-- Estadísticas Calculadas -->
            <div class="col-md-6">
                <div class="card shadow-sm h-100 stats-card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Puntaje Total Calculado</h5>
                    </div>
                    <div class="card-body text-center">
                        <h1 class="display-4 text-primary mb-2"><?= number_format($promedioCalculado, 1) ?></h1>
                        <span class="badge badge-<?= $nivelCalculado ?> fs-5">
                            <?= strtoupper(str_replace('_', ' ', $nivelCalculado)) ?>
                        </span>
                        <p class="text-muted mt-3 mb-0">Promedio de <?= $totalWorkers ?> trabajadores</p>
                    </div>
                </div>
            </div>

            <!-- Estadísticas BD -->
            <div class="col-md-6">
                <div class="card shadow-sm h-100 stats-card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-database me-2"></i>Puntaje Total en BD</h5>
                    </div>
                    <div class="card-body text-center">
                        <h1 class="display-4 text-info mb-2"><?= number_format($promedioFromDB, 1) ?></h1>
                        <?php
                            $diff = abs($promedioCalculado - $promedioFromDB);
                            $isMatch = $diff < 0.1;
                        ?>
                        <?php if ($isMatch): ?>
                            <span class="badge bg-success fs-5"><i class="fas fa-check-circle me-1"></i>COINCIDE</span>
                        <?php else: ?>
                            <span class="badge bg-danger fs-5"><i class="fas fa-exclamation-triangle me-1"></i>DIFERENCIA: <?= number_format($diff, 2) ?></span>
                        <?php endif; ?>
                        <p class="text-muted mt-3 mb-0">Promedio almacenado en base de datos</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Baremos Oficiales Tabla 33 -->
        <?php if ($baremos): ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Baremos Oficiales - Tabla 33 (Puntaje Total Intralaboral)</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered baremo-table">
                    <thead>
                        <tr>
                            <th class="text-center">Sin riesgo o riesgo despreciable</th>
                            <th class="text-center">Riesgo bajo</th>
                            <th class="text-center">Riesgo medio</th>
                            <th class="text-center">Riesgo alto</th>
                            <th class="text-center">Riesgo muy alto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="text-center">
                            <td><?= $baremos['sin_riesgo']['min'] ?> - <?= $baremos['sin_riesgo']['max'] ?></td>
                            <td><?= $baremos['riesgo_bajo']['min'] ?> - <?= $baremos['riesgo_bajo']['max'] ?></td>
                            <td><?= $baremos['riesgo_medio']['min'] ?> - <?= $baremos['riesgo_medio']['max'] ?></td>
                            <td><?= $baremos['riesgo_alto']['min'] ?> - <?= $baremos['riesgo_alto']['max'] ?></td>
                            <td><?= $baremos['riesgo_muy_alto']['min'] ?> - <?= $baremos['riesgo_muy_alto']['max'] ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Tabla de Puntajes por Trabajador -->
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="fas fa-users me-2"></i>Puntajes Totales por Trabajador</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Trabajador</th>
                                <th class="text-center">Puntaje Bruto</th>
                                <th class="text-center">Puntaje Transformado (Calculado)</th>
                                <th class="text-center">Nivel Calculado</th>
                                <th class="text-center">Puntaje BD</th>
                                <th class="text-center">Nivel BD</th>
                                <th class="text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($totalScores as $score): ?>
                                <?php
                                    $puntajeMatch = abs($score['puntaje_transformado'] - $score['puntaje_bd']) < 0.1;
                                    $nivelMatch = $score['nivel_calculado'] === $score['nivel_bd'];
                                    $rowClass = ($puntajeMatch && $nivelMatch) ? 'match' : 'mismatch';
                                ?>
                                <tr class="<?= $rowClass ?>">
                                    <td><?= esc($score['worker_name']) ?></td>
                                    <td class="text-center"><?= number_format($score['puntaje_bruto'], 1) ?></td>
                                    <td class="text-center"><strong><?= number_format($score['puntaje_transformado'], 1) ?></strong></td>
                                    <td class="text-center">
                                        <span class="badge badge-<?= $score['nivel_calculado'] ?>">
                                            <?= strtoupper(str_replace('_', ' ', $score['nivel_calculado'])) ?>
                                        </span>
                                    </td>
                                    <td class="text-center"><?= number_format($score['puntaje_bd'], 1) ?></td>
                                    <td class="text-center">
                                        <span class="badge badge-<?= $score['nivel_bd'] ?>">
                                            <?= strtoupper(str_replace('_', ' ', $score['nivel_bd'])) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($puntajeMatch && $nivelMatch): ?>
                                            <i class="fas fa-check-circle text-success fa-lg"></i>
                                        <?php else: ?>
                                            <i class="fas fa-exclamation-triangle text-danger fa-lg"></i>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Botón Volver -->
        <div class="text-center mt-4">
            <button onclick="window.close()" class="btn btn-secondary btn-lg">
                <i class="fas fa-arrow-left me-2"></i>Volver
            </button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
