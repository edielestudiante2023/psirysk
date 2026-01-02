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
            <div class="col-md-4">
                <div class="card shadow-sm h-100 stats-card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Puntaje Total Calculado</h5>
                    </div>
                    <div class="card-body text-center">
                        <h1 class="display-4 text-primary mb-2"><?= number_format($validation['puntaje_transformado'], 2) ?></h1>
                        <p class="text-muted mt-3 mb-0">Suma de dominios: <?= number_format($validation['sum_promedios'], 2) ?></p>
                        <p class="text-muted mb-0">Factor: <?= $validation['transformation_factor'] ?></p>
                    </div>
                </div>
            </div>

            <!-- Estadísticas BD -->
            <div class="col-md-4">
                <div class="card shadow-sm h-100 stats-card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-database me-2"></i>Puntaje Total en BD</h5>
                    </div>
                    <div class="card-body text-center">
                        <h1 class="display-4 text-info mb-2"><?= number_format($validation['db_comparison']['db_score'], 2) ?></h1>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm h-100 stats-card">
                    <div class="card-header <?= $validation['db_comparison']['status'] === 'ok' ? 'bg-success' : 'bg-danger' ?> text-white">
                        <h5 class="mb-0"><i class="fas fa-balance-scale me-2"></i>Diferencia</h5>
                    </div>
                    <div class="card-body text-center">
                        <h1 class="display-4 <?= $validation['db_comparison']['status'] === 'ok' ? 'text-success' : 'text-danger' ?> mb-2">
                            <?= number_format($validation['db_comparison']['difference'], 2) ?>
                        </h1>
                        <?php if ($validation['db_comparison']['status'] === 'ok'): ?>
                            <span class="badge bg-success fs-5"><i class="fas fa-check-circle me-1"></i>VALIDACIÓN OK</span>
                        <?php else: ?>
                            <span class="badge bg-danger fs-5"><i class="fas fa-exclamation-triangle me-1"></i>DISCREPANCIA</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Baremos Oficiales Tabla 33 -->
        <?php if ($baremos): ?>
            <?php
                // Determinar en qué nivel se encuentra el puntaje
                $puntaje = $validation['puntaje_transformado'];
                $nivelActual = null;

                if ($puntaje >= $baremos['sin_riesgo'][0] && $puntaje <= $baremos['sin_riesgo'][1]) {
                    $nivelActual = 'sin_riesgo';
                } elseif ($puntaje >= $baremos['riesgo_bajo'][0] && $puntaje <= $baremos['riesgo_bajo'][1]) {
                    $nivelActual = 'riesgo_bajo';
                } elseif ($puntaje >= $baremos['riesgo_medio'][0] && $puntaje <= $baremos['riesgo_medio'][1]) {
                    $nivelActual = 'riesgo_medio';
                } elseif ($puntaje >= $baremos['riesgo_alto'][0] && $puntaje <= $baremos['riesgo_alto'][1]) {
                    $nivelActual = 'riesgo_alto';
                } elseif ($puntaje >= $baremos['riesgo_muy_alto'][0] && $puntaje <= $baremos['riesgo_muy_alto'][1]) {
                    $nivelActual = 'riesgo_muy_alto';
                }
            ?>
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
                            <td <?= $nivelActual === 'sin_riesgo' ? 'style="background-color: #28a745; color: white; font-weight: bold;"' : '' ?>>
                                <?= $baremos['sin_riesgo'][0] ?> - <?= $baremos['sin_riesgo'][1] ?>
                                <?php if ($nivelActual === 'sin_riesgo'): ?>
                                    <br><span class="badge bg-light text-dark mt-1">← Puntaje: <?= number_format($puntaje, 2) ?></span>
                                <?php endif; ?>
                            </td>
                            <td <?= $nivelActual === 'riesgo_bajo' ? 'style="background-color: #28a745; color: white; font-weight: bold;"' : '' ?>>
                                <?= $baremos['riesgo_bajo'][0] ?> - <?= $baremos['riesgo_bajo'][1] ?>
                                <?php if ($nivelActual === 'riesgo_bajo'): ?>
                                    <br><span class="badge bg-light text-dark mt-1">← Puntaje: <?= number_format($puntaje, 2) ?></span>
                                <?php endif; ?>
                            </td>
                            <td <?= $nivelActual === 'riesgo_medio' ? 'style="background-color: #ffc107; color: black; font-weight: bold;"' : '' ?>>
                                <?= $baremos['riesgo_medio'][0] ?> - <?= $baremos['riesgo_medio'][1] ?>
                                <?php if ($nivelActual === 'riesgo_medio'): ?>
                                    <br><span class="badge bg-dark text-white mt-1">← Puntaje: <?= number_format($puntaje, 2) ?></span>
                                <?php endif; ?>
                            </td>
                            <td <?= $nivelActual === 'riesgo_alto' ? 'style="background-color: #dc3545; color: white; font-weight: bold;"' : '' ?>>
                                <?= $baremos['riesgo_alto'][0] ?> - <?= $baremos['riesgo_alto'][1] ?>
                                <?php if ($nivelActual === 'riesgo_alto'): ?>
                                    <br><span class="badge bg-light text-dark mt-1">← Puntaje: <?= number_format($puntaje, 2) ?></span>
                                <?php endif; ?>
                            </td>
                            <td <?= $nivelActual === 'riesgo_muy_alto' ? 'style="background-color: #dc3545; color: white; font-weight: bold;"' : '' ?>>
                                <?= $baremos['riesgo_muy_alto'][0] ?> - <?= $baremos['riesgo_muy_alto'][1] ?>
                                <?php if ($nivelActual === 'riesgo_muy_alto'): ?>
                                    <br><span class="badge bg-light text-dark mt-1">← Puntaje: <?= number_format($puntaje, 2) ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Dominios que componen el Total -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="fas fa-layer-group me-2"></i>Dominios que Componen el Total Intralaboral</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Dominio</th>
                                <th class="text-center">Puntaje Bruto<br><small class="text-muted">(sum_averages)</small></th>
                                <th class="text-center">Factor Transformación</th>
                                <th class="text-center">Puntaje Transformado<br><small class="text-muted">(Calculado)</small></th>
                                <th class="text-center">Puntaje BD</th>
                                <th class="text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($validation['domains'] as $dom): ?>
                                <?php
                                    $match = abs($dom['calculated_score'] - $dom['db_score']) < 0.1;
                                    $rowClass = $match ? 'match' : 'mismatch';
                                ?>
                                <tr class="<?= $rowClass ?>">
                                    <td><strong><?= esc($dom['name']) ?></strong></td>
                                    <td class="text-center"><?= number_format($dom['sum_averages'], 2) ?></td>
                                    <td class="text-center"><?= $dom['transformation_factor'] ?></td>
                                    <td class="text-center"><strong><?= number_format($dom['calculated_score'], 2) ?></strong></td>
                                    <td class="text-center"><?= number_format($dom['db_score'], 2) ?></td>
                                    <td class="text-center">
                                        <?php if ($match): ?>
                                            <i class="fas fa-check-circle text-success fa-lg"></i>
                                        <?php else: ?>
                                            <i class="fas fa-exclamation-triangle text-danger fa-lg"></i>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="table-primary">
                                <td><strong>SUMA TOTAL (Puntaje Bruto del Total Intralaboral)</strong></td>
                                <td class="text-center"><strong><?= number_format($validation['sum_promedios'], 2) ?></strong></td>
                                <td class="text-center"><strong><?= $validation['transformation_factor'] ?></strong></td>
                                <td class="text-center"><strong><?= number_format($validation['puntaje_transformado'], 2) ?></strong></td>
                                <td class="text-center"><strong><?= number_format($validation['db_comparison']['db_score'], 2) ?></strong></td>
                                <td class="text-center">
                                    <?php if ($validation['db_comparison']['status'] === 'ok'): ?>
                                        <i class="fas fa-check-circle text-success fa-lg"></i>
                                    <?php else: ?>
                                        <i class="fas fa-exclamation-triangle text-danger fa-lg"></i>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Fórmula de Cálculo -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Fórmula de Cálculo del Total Intralaboral</h5>
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>Paso 1:</strong> Sumar los puntajes brutos de los 4 dominios</p>
                <pre class="bg-light p-3 rounded">Suma = <?php
                    $parts = [];
                    foreach ($validation['domains'] as $dom) {
                        $parts[] = number_format($dom['sum_averages'], 2);
                    }
                    echo implode(' + ', $parts);
                ?> = <?= number_format($validation['sum_promedios'], 2) ?></pre>

                <p class="mb-2 mt-3"><strong>Paso 2:</strong> Transformar con el factor del total intralaboral (Tabla 27)</p>
                <pre class="bg-light p-3 rounded">Puntaje Transformado = (<?= number_format($validation['sum_promedios'], 2) ?> / <?= $validation['transformation_factor'] ?>) × 100 = <?= number_format($validation['puntaje_transformado'], 2) ?></pre>
            </div>
        </div>

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
