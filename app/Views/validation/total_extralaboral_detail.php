<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validación Total Extralaboral</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8f9fa; }
        .validation-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 2rem; margin-bottom: 2rem; border-radius: 8px; }
        .stats-card { border-left: 4px solid #667eea; }
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
                    <h2 class="mb-2"><i class="fas fa-chart-area me-2"></i>Validación Total Extralaboral - Forma <?= esc($formType) ?></h2>
                    <p class="mb-0 opacity-75">
                        <i class="fas fa-building me-2"></i><?= esc($service['service_name']) ?> |
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
        $result = $results[0];
        $match = $result['validation_status'] === 'ok';
        ?>
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card shadow-sm h-100 stats-card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Total Participantes</h5>
                    </div>
                    <div class="card-body text-center">
                        <h1 class="display-4 text-primary mb-0"><?= $totalWorkers ?></h1>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card shadow-sm h-100 stats-card">
                    <div class="card-header bg-<?= $match ? 'success' : 'danger' ?> text-white">
                        <h5 class="mb-0"><i class="fas fa-<?= $match ? 'check-circle' : 'exclamation-triangle' ?> me-2"></i>Estado de Validación</h5>
                    </div>
                    <div class="card-body text-center">
                        <h1 class="display-4 text-<?= $match ? 'success' : 'danger' ?> mb-0">
                            <?= $match ? 'OK' : 'ERROR' ?>
                        </h1>
                        <p class="text-muted mt-2 mb-0">
                            Diferencia: <?= number_format(abs($result['difference']), 2) ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Baremos Oficiales -->
        <?php if ($baremos): ?>
        <?php
        // Determinar el rango donde cae el puntaje calculado
        $calculatedScore = $results[0]['calculated_score'];
        $nivelActual = null;
        foreach ($baremos as $nivel => $rango) {
            if ($calculatedScore >= $rango['min'] && $calculatedScore <= $rango['max']) {
                $nivelActual = $nivel;
                break;
            }
        }
        ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Baremos Oficiales - Total Extralaboral (Tabla 24)</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered baremo-table">
                    <thead>
                        <tr>
                            <?php foreach ($baremos as $nivel => $rango): ?>
                                <th class="text-center <?= $nivel === $nivelActual ? 'bg-' . $rango['color'] . ' text-white' : 'bg-light' ?>"><?= esc($rango['label']) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="text-center">
                            <?php foreach ($baremos as $nivel => $rango): ?>
                                <td class="<?= $nivel === $nivelActual ? 'table-' . $rango['color'] : '' ?>">
                                    <?= $rango['min'] ?> - <?= $rango['max'] ?>
                                    <?php if ($nivel === $nivelActual): ?>
                                        <br><strong class="text-<?= $rango['color'] ?>"><?= number_format($calculatedScore, 2) ?></strong>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Dimensiones que componen el Total Extralaboral -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="fas fa-layer-group me-2"></i>Dimensiones que Componen el Total Extralaboral</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Dimensión</th>
                                <th class="text-center">Puntaje Bruto<br><small class="text-muted">(sum_averages)</small></th>
                                <th class="text-center">Factor Transformación</th>
                                <th class="text-center">Puntaje Transformado<br><small class="text-muted">(Calculado)</small></th>
                                <th class="text-center">Puntaje BD</th>
                                <th class="text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($dimensions)): ?>
                                <?php foreach ($dimensions as $dim): ?>
                                    <?php
                                        $match = abs($dim['calculated_score'] - $dim['db_score']) < 0.1;
                                        $rowClass = $match ? 'match' : 'mismatch';
                                    ?>
                                    <tr class="<?= $rowClass ?>">
                                        <td><strong><?= esc($dim['element_name']) ?></strong></td>
                                        <td class="text-center"><?= number_format($dim['sum_averages'], 2) ?></td>
                                        <td class="text-center"><?= $dim['transformation_factor'] ?></td>
                                        <td class="text-center"><strong><?= number_format($dim['calculated_score'], 2) ?></strong></td>
                                        <td class="text-center"><?= number_format($dim['db_score'], 2) ?></td>
                                        <td class="text-center">
                                            <?php if ($match): ?>
                                                <i class="fas fa-check-circle text-success fa-lg"></i>
                                            <?php else: ?>
                                                <i class="fas fa-exclamation-triangle text-danger fa-lg"></i>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <tr class="table-primary">
                                <td><strong>SUMA TOTAL (Puntaje Bruto del Total Extralaboral)</strong></td>
                                <td class="text-center"><strong><?= number_format($result['sum_averages'], 2) ?></strong></td>
                                <td class="text-center"><strong><?= $result['transformation_factor'] ?></strong></td>
                                <td class="text-center">
                                    <strong><?= number_format($result['calculated_score'], 2) ?></strong>
                                    <?php
                                    // Determinar nivel de riesgo
                                    $nivelRiesgoData = null;
                                    if ($baremos) {
                                        foreach ($baremos as $nivel => $rango) {
                                            $score = $result['calculated_score'];
                                            if ($score >= $rango['min'] && $score <= $rango['max']) {
                                                $nivelRiesgoData = $rango;
                                                break;
                                            }
                                        }
                                    }
                                    ?>
                                    <?php if ($nivelRiesgoData): ?>
                                        <br><span class="badge bg-<?= $nivelRiesgoData['color'] ?>"><?= esc($nivelRiesgoData['label']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center"><strong><?= number_format($result['db_score'], 2) ?></strong></td>
                                <td class="text-center">
                                    <?php if ($match): ?>
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
                <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Fórmula de Cálculo del Total Extralaboral</h5>
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>Paso 1:</strong> Sumar los promedios brutos de todas las 7 dimensiones extralaboral</p>
                <pre class="bg-light p-3 rounded">Suma = Promedio Dim1 + Promedio Dim2 + ... + Promedio Dim7</pre>

                <p class="mb-2 mt-3"><strong>Paso 2:</strong> Transformar con el factor total (Tabla 20)</p>
                <pre class="bg-light p-3 rounded">Puntaje Total Transformado = (Suma / <?= $factorTotal ?>) × 100</pre>

                <p class="text-muted mt-3 mb-0"><small><strong>Nota:</strong> Los promedios brutos de cada dimensión se obtienen de la tabla validation_results (campo sum_averages), NO se recalculan desde items.</small></p>
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
