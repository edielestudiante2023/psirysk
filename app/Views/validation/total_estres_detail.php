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
        .match { background-color: #d4edda !important; }
        .mismatch { background-color: #f8d7da !important; }
        .baremo-table th { background-color: #f093fb; color: white; font-size: 0.9rem; }
        .badge-grupo1 { background-color: #0d6efd; }
        .badge-grupo2 { background-color: #198754; }
        .badge-grupo3 { background-color: #ffc107; color: #000; }
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
        $calculatedScore = $result['calculated_score'];
        $nivelActual = null;
        foreach ($baremos as $nivel => $rango) {
            if ($calculatedScore >= $rango['min'] && $calculatedScore <= $rango['max']) {
                $nivelActual = $nivel;
                break;
            }
        }
        ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header text-white" style="background-color: #f093fb;">
                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Baremos Oficiales - Total Estrés (Tabla 6)</h5>
                <small>Tercera versión del cuestionario - <?= $formType === 'A' ? 'Jefes, Profesionales y Técnicos' : 'Auxiliares y Operarios' ?></small>
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

        <!-- Resumen de Validación -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Resumen de Validación del Total Estrés</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Métrica</th>
                                <th class="text-center">Valor</th>
                                <th>Descripción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Puntaje Bruto (Sum Averages)</strong></td>
                                <td class="text-center"><strong><?= number_format($result['sum_averages'], 2) ?></strong></td>
                                <td>Suma de promedios de los 31 ítems</td>
                            </tr>
                            <tr>
                                <td><strong>Factor de Transformación</strong></td>
                                <td class="text-center"><strong><?= $result['transformation_factor'] ?></strong></td>
                                <td>Factor oficial (Tabla 4)</td>
                            </tr>
                            <tr class="<?= $match ? 'match' : 'mismatch' ?>">
                                <td><strong>Puntaje Transformado (Calculado)</strong></td>
                                <td class="text-center">
                                    <strong><?= number_format($result['calculated_score'], 2) ?></strong>
                                    <?php if ($nivelActual): ?>
                                        <br><span class="badge bg-<?= $baremos[$nivelActual]['color'] ?>"><?= esc($baremos[$nivelActual]['label']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>Puntaje calculado por el Núcleo Validador desde responses</td>
                            </tr>
                            <tr class="<?= $match ? 'match' : 'mismatch' ?>">
                                <td><strong>Puntaje BD (Promedio Real)</strong></td>
                                <td class="text-center"><strong><?= number_format($result['db_score'], 2) ?></strong></td>
                                <td>Promedio de calculated_results.estres_total_puntaje</td>
                            </tr>
                            <tr>
                                <td><strong>Diferencia</strong></td>
                                <td class="text-center">
                                    <strong class="text-<?= $match ? 'success' : 'danger' ?>"><?= number_format($result['difference'], 2) ?></strong>
                                </td>
                                <td>Calculated - DB Score (Tolerancia: ±0.1)</td>
                            </tr>
                            <tr>
                                <td><strong>Estado de Validación</strong></td>
                                <td class="text-center">
                                    <?php if ($match): ?>
                                        <i class="fas fa-check-circle text-success fa-2x"></i>
                                    <?php else: ?>
                                        <i class="fas fa-exclamation-triangle text-danger fa-2x"></i>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($match): ?>
                                        <span class="text-success">✓ OK - Los puntajes coinciden</span>
                                    <?php else: ?>
                                        <span class="text-danger">✗ ERROR - Discrepancia detectada. Ejecutar: <code>php spark recalculate:estres</code></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Información de Grupos de Ítems -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-layer-group me-2"></i>Grupos de Calificación (Tabla 4)</h5>
            </div>
            <div class="card-body">
                <?php
                $gruposInfo = \App\Libraries\EstresScoring::getItemsPorGrupo();
                $coloresBadge = ['grupo1' => 'badge-grupo1', 'grupo2' => 'badge-grupo2', 'grupo3' => 'badge-grupo3'];
                $valoresPorGrupo = [
                    'grupo1' => 'Siempre=9, Casi siempre=6, A veces=3, Nunca=0',
                    'grupo2' => 'Siempre=6, Casi siempre=4, A veces=2, Nunca=0',
                    'grupo3' => 'Siempre=3, Casi siempre=2, A veces=1, Nunca=0'
                ];
                ?>

                <div class="row">
                    <?php foreach ($gruposInfo as $grupoKey => $items): ?>
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <span class="badge <?= $coloresBadge[$grupoKey] ?>">
                                            <?= strtoupper(str_replace('grupo', 'Grupo ', $grupoKey)) ?>
                                        </span>
                                    </h6>
                                    <p class="card-text small">
                                        <strong>Ítems:</strong> <?= implode(', ', $items) ?><br>
                                        <strong>Valores:</strong> <?= $valoresPorGrupo[$grupoKey] ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Fórmula de Cálculo -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Fórmula de Cálculo del Total Estrés</h5>
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>Paso 1:</strong> Para cada ítem (1-31), calcular promedio de TODOS los workers</p>
                <pre class="bg-light p-3 rounded">Para cada ítem:
  - Obtener respuestas de todos los workers
  - Calificar según grupo (Grupo 1: 9,6,3,0 | Grupo 2: 6,4,2,0 | Grupo 3: 3,2,1,0)
  - Calcular promedio del ítem
</pre>

                <p class="mb-2 mt-3"><strong>Paso 2:</strong> Sumar los promedios de los 31 ítems</p>
                <pre class="bg-light p-3 rounded">Suma = Promedio Ítem1 + Promedio Ítem2 + ... + Promedio Ítem31
Suma = <?= number_format($result['sum_averages'], 2) ?></pre>

                <p class="mb-2 mt-3"><strong>Paso 3:</strong> Transformar con el factor total (Tabla 4)</p>
                <pre class="bg-light p-3 rounded">Puntaje Total Transformado = (Suma / <?= $factorTotal ?>) × 100
Puntaje Total Transformado = (<?= number_format($result['sum_averages'], 2) ?> / <?= $factorTotal ?>) × 100 = <?= number_format($result['calculated_score'], 2) ?></pre>

                <p class="text-muted mt-3 mb-0"><small><strong>Nota:</strong> El Núcleo Validador re-calcula desde responses raw (tabla responses), NO confía en calculated_results. Esto permite detectar bugs en el Núcleo del Aplicativo.</small></p>
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
