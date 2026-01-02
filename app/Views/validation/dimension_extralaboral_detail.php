<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($dimensionName) ?> - Validación Extralaboral</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    <style>
        .validation-table { font-size: 0.85rem; }
        .validation-table th { background-color: #28a745; color: white; font-weight: 600; text-align: center; vertical-align: middle; padding: 0.5rem; }
        .validation-table td { vertical-align: middle; padding: 0.5rem; text-align: center; }
        .item-number { background-color: #f8f9fa; font-weight: bold; }
        .comparison-card.ok { border-left: 4px solid #28a745; }
        .comparison-card.error { border-left: 4px solid #dc3545; }
        .score-0 { background-color: #d4edda !important; }
        .score-4 { background-color: #f8d7da !important; }
        .badge-status { font-size: 1.1rem; padding: 0.5rem 1rem; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 bg-primary sidebar min-vh-100 p-0">
                <div class="text-white p-4">
                    <h4 class="mb-0"><i class="fas fa-shield-check me-2"></i>PsyRisk</h4>
                    <small>Sistema de Validación</small>
                </div>
                <hr class="text-white-50 mx-3">
                <nav class="nav flex-column mt-4">
                    <a class="nav-link" href="<?= base_url('dashboard') ?>">
                        <i class="fas fa-home me-2"></i> Dashboard
                    </a>
                    <a class="nav-link" href="<?= base_url('companies') ?>">
                        <i class="fas fa-building me-2"></i> Empresas
                    </a>
                    <a class="nav-link" href="<?= base_url('battery-services') ?>">
                        <i class="fas fa-clipboard-check me-2"></i> Servicios
                    </a>
                    <hr class="text-white-50 mx-3">
                    <a class="nav-link" href="<?= base_url('logout') ?>">
                        <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 p-0">
                <nav class="navbar navbar-custom navbar-expand-lg p-3">
                    <div class="container-fluid">
                        <div>
                            <h4 class="mb-0"><i class="fas fa-shield-check text-primary me-2"></i><?= esc($dimensionConfig['name']) ?></h4>
                            <small class="text-muted"><?= esc($service['service_name']) ?> | <?= count($workers) ?> participantes | Forma <?= $formType ?></small>
                        </div>
                        <a href="<?= base_url('validation/' . $service['id']) ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Volver
                        </a>
                    </div>
                </nav>

                <div class="p-4">
                    <!-- Breadcrumb -->
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('battery-services') ?>">Servicios</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('battery-services/' . $service['id']) ?>"><?= esc($service['service_name']) ?></a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('validation/' . $service['id']) ?>">Validación</a></li>
                            <li class="breadcrumb-item active"><?= esc($dimensionConfig['name']) ?></li>
                        </ol>
                    </nav>

                    <!-- Resultado de Validación -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <div class="card comparison-card <?= $validationData['db_comparison']['status'] ?> shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <?php if ($validationData['db_comparison']['status'] === 'ok'): ?>
                                            <i class="fas fa-check-circle text-success me-2"></i>Validación Exitosa
                                        <?php else: ?>
                                            <i class="fas fa-exclamation-triangle text-danger me-2"></i>Discrepancia Detectada
                                        <?php endif; ?>
                                    </h5>
                                    <div class="row mt-3">
                                        <div class="col-md-4">
                                            <p class="mb-1 text-muted small">Puntaje Calculado</p>
                                            <h4 class="text-primary"><?= number_format($validationData['puntaje_transformado'], 2) ?></h4>
                                        </div>
                                        <div class="col-md-4">
                                            <p class="mb-1 text-muted small">Puntaje en BD</p>
                                            <h4 class="text-info"><?= number_format($validationData['db_comparison']['db_score'], 2) ?></h4>
                                        </div>
                                        <div class="col-md-4">
                                            <p class="mb-1 text-muted small">Diferencia</p>
                                            <h4 class="<?= $validationData['db_comparison']['status'] === 'ok' ? 'text-success' : 'text-danger' ?>">
                                                <?= number_format($validationData['db_comparison']['difference'], 2) ?>
                                            </h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card shadow-sm">
                                <div class="card-body text-center">
                                    <p class="mb-1 text-muted small">Nivel de Riesgo</p>
                                    <h3>
                                        <span class="badge bg-<?= $validationData['nivel_riesgo']['color'] ?> badge-status">
                                            <?= esc($validationData['nivel_riesgo']['label']) ?>
                                        </span>
                                    </h3>
                                    <small class="text-muted">Según baremos oficiales</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de Validación Detallada -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-table me-2"></i>Validación Detallada por Ítem</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover validation-table mb-0">
                                    <thead>
                                        <tr>
                                            <th rowspan="2">Elemento a evaluar</th>
                                            <th rowspan="2">Pregunta</th>
                                            <th rowspan="2">Cantidad<br>personas</th>
                                            <th colspan="5">Personas que eligieron una opción de respuesta</th>
                                            <th colspan="5">Puntajes por opción</th>
                                            <th rowspan="2">Subtotal</th>
                                            <th rowspan="2">Promedio</th>
                                            <th rowspan="2">Estado</th>
                                        </tr>
                                        <tr>
                                            <th>Siempre</th>
                                            <th>Casi<br>Siempre</th>
                                            <th>Algunas<br>Veces</th>
                                            <th>Casi<br>Nunca</th>
                                            <th>Nunca</th>
                                            <th>Siempre</th>
                                            <th>Casi<br>Siempre</th>
                                            <th>Algunas<br>Veces</th>
                                            <th>Casi<br>Nunca</th>
                                            <th>Nunca</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($validationData['items'] as $index => $item): ?>
                                            <tr>
                                                <?php if ($index === 0): ?>
                                                    <td rowspan="<?= count($validationData['items']) ?>" class="item-number">
                                                        <?= esc($dimensionConfig['name']) ?>
                                                    </td>
                                                <?php endif; ?>
                                                <td class="item-number"><?= $item['item_number'] ?></td>
                                                <td class="<?= !$item['response_count_valid'] ? 'bg-warning text-dark' : '' ?>" <?= !$item['response_count_valid'] ? 'title="⚠️ Suma de respuestas no coincide: ' . ($item['participants'] + $item['response_count_difference']) . ' (diferencia: ' . ($item['response_count_difference'] > 0 ? '+' : '') . $item['response_count_difference'] . ')"' : '' ?>>
                                                    <?= $item['participants'] ?>
                                                    <?php if (!$item['response_count_valid']): ?>
                                                        <i class="fas fa-exclamation-triangle ms-1" style="color: #856404;"></i>
                                                    <?php endif; ?>
                                                </td>
                                                <!-- Respuestas -->
                                                <td><?= $item['responses']['siempre'] ?></td>
                                                <td><?= $item['responses']['casi_siempre'] ?></td>
                                                <td><?= $item['responses']['algunas_veces'] ?></td>
                                                <td><?= $item['responses']['casi_nunca'] ?></td>
                                                <td><?= $item['responses']['nunca'] ?></td>
                                                <!-- Puntajes con fórmulas -->
                                                <td class="bg-light <?= $item['score_values']['siempre'] == 0 ? 'score-0' : ($item['score_values']['siempre'] == 4 ? 'score-4' : '') ?>" title="<?= $item['responses']['siempre'] ?> × <?= $item['score_values']['siempre'] ?>"><?= $item['responses']['siempre'] ?> × <?= $item['score_values']['siempre'] ?> = <?= $item['scores']['siempre'] ?></td>
                                                <td class="bg-light <?= $item['score_values']['casi_siempre'] == 0 ? 'score-0' : ($item['score_values']['casi_siempre'] == 4 ? 'score-4' : '') ?>" title="<?= $item['responses']['casi_siempre'] ?> × <?= $item['score_values']['casi_siempre'] ?>"><?= $item['responses']['casi_siempre'] ?> × <?= $item['score_values']['casi_siempre'] ?> = <?= $item['scores']['casi_siempre'] ?></td>
                                                <td class="bg-light <?= $item['score_values']['algunas_veces'] == 0 ? 'score-0' : ($item['score_values']['algunas_veces'] == 4 ? 'score-4' : '') ?>" title="<?= $item['responses']['algunas_veces'] ?> × <?= $item['score_values']['algunas_veces'] ?>"><?= $item['responses']['algunas_veces'] ?> × <?= $item['score_values']['algunas_veces'] ?> = <?= $item['scores']['algunas_veces'] ?></td>
                                                <td class="bg-light <?= $item['score_values']['casi_nunca'] == 0 ? 'score-0' : ($item['score_values']['casi_nunca'] == 4 ? 'score-4' : '') ?>" title="<?= $item['responses']['casi_nunca'] ?> × <?= $item['score_values']['casi_nunca'] ?>"><?= $item['responses']['casi_nunca'] ?> × <?= $item['score_values']['casi_nunca'] ?> = <?= $item['scores']['casi_nunca'] ?></td>
                                                <td class="bg-light <?= $item['score_values']['nunca'] == 0 ? 'score-0' : ($item['score_values']['nunca'] == 4 ? 'score-4' : '') ?>" title="<?= $item['responses']['nunca'] ?> × <?= $item['score_values']['nunca'] ?>"><?= $item['responses']['nunca'] ?> × <?= $item['score_values']['nunca'] ?> = <?= $item['scores']['nunca'] ?></td>
                                                <td class="fw-bold"><?= $item['subtotal'] ?></td>
                                                <td class="fw-bold text-primary"><?= number_format($item['average'], 2) ?></td>
                                                <td>
                                                    <?php if ($item['is_inverse']): ?>
                                                        <span class="badge bg-warning text-dark" title="Calificación inversa (Grupo 2)">INV</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success" title="Calificación normal (Grupo 1)">NOR</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>

                                        <!-- Fila de resumen -->
                                        <tr class="table-info fw-bold">
                                            <td colspan="2" class="text-end">SUMA PROMEDIOS</td>
                                            <td colspan="12"></td>
                                            <td class="text-primary"><?= number_format($validationData['suma_promedios'], 2) ?></td>
                                            <td></td>
                                        </tr>
                                        <tr class="table-info fw-bold">
                                            <td colspan="2" class="text-end">FACTOR DE TRANSFORMACIÓN</td>
                                            <td colspan="12"></td>
                                            <td><?= $validationData['factor'] ?></td>
                                            <td></td>
                                        </tr>
                                        <tr class="table-info fw-bold">
                                            <td colspan="2" class="text-end">DIVISIÓN (Suma / Factor)</td>
                                            <td colspan="12"></td>
                                            <td><?= number_format($validationData['suma_promedios'] / $validationData['factor'], 4) ?></td>
                                            <td></td>
                                        </tr>
                                        <tr class="table-success fw-bold">
                                            <td colspan="2" class="text-end">PUNTAJE TRANSFORMADO</td>
                                            <td colspan="12"></td>
                                            <td class="text-success"><?= number_format($validationData['puntaje_transformado'], 2) ?></td>
                                            <td></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de Baremos -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Tabla de Baremos</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered text-center mb-0">
                                <thead>
                                    <tr>
                                        <?php foreach ($baremos as $nivel => $rango): ?>
                                            <th class="bg-<?= $rango['color'] ?> text-white"><?= esc($rango['label']) ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <?php foreach ($baremos as $nivel => $rango): ?>
                                            <td class="table-<?= $rango['color'] ?>">
                                                <?= $rango['min'] ?> - <?= $rango['max'] ?>
                                                <?php if ($validationData['puntaje_transformado'] >= $rango['min'] && $validationData['puntaje_transformado'] <= $rango['max']): ?>
                                                    <br><strong class="text-<?= $rango['color'] ?>">ACTUAL: <?= number_format($validationData['puntaje_transformado'], 2) ?></strong>
                                                <?php endif; ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
