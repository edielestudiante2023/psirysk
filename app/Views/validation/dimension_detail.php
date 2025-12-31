<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?> - PsyRisk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            margin: 5px 15px;
            border-radius: 8px;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background-color: rgba(255,255,255,0.2);
            color: white;
        }
        .navbar-custom {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .table-validation {
            font-size: 0.85rem;
        }
        .table-validation th {
            background-color: #2e7d32;
            color: white;
            text-align: center;
            vertical-align: middle;
            font-size: 0.75rem;
            padding: 8px 4px;
        }
        .table-validation td {
            text-align: center;
            vertical-align: middle;
            padding: 6px 4px;
            font-size: 0.8rem;
        }
        .table-validation .bg-light {
            font-size: 0.7rem;
            white-space: nowrap;
        }
        .score-0 {
            background-color: #e3f2fd !important;
            border-top: 2px solid #2196f3 !important;
            border-right: 2px solid #2196f3 !important;
            border-bottom: 2px solid #2196f3 !important;
            border-left: 2px solid #2196f3 !important;
            font-weight: bold;
        }
        .score-4 {
            background-color: #f3e5f5 !important;
            border-top: 2px solid #9c27b0 !important;
            border-right: 2px solid #9c27b0 !important;
            border-bottom: 2px solid #9c27b0 !important;
            border-left: 2px solid #9c27b0 !important;
            font-weight: bold;
        }
        .table-validation .item-number {
            font-weight: bold;
            background-color: #f8f9fa;
        }
        .table-summary {
            background-color: #e3f2fd;
            font-weight: bold;
        }
        .badge-status {
            font-size: 0.85rem;
        }
        .comparison-card {
            border-left: 4px solid;
        }
        .comparison-card.ok {
            border-left-color: #28a745;
        }
        .comparison-card.error {
            border-left-color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar p-0">
                <div class="p-4">
                    <h3 class="fw-bold">PsyRisk</h3>
                    <p class="small mb-0"><?= session()->get('name') ?></p>
                    <small class="text-white-50"><?= ucfirst(session()->get('role_name')) ?></small>
                </div>
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
                            <small class="text-muted"><?= esc($service['service_name']) ?> | <?= count($workers) ?> participantes</small>
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
                                <table class="table table-bordered table-hover table-validation mb-0">
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
                                                <!-- Puntajes con fórmulas y resaltado de valores extremos -->
                                                <td class="bg-light <?= $item['score_values']['siempre'] == 0 ? 'score-0' : ($item['score_values']['siempre'] == 4 ? 'score-4' : '') ?>" title="<?= $item['responses']['siempre'] ?> × <?= $item['score_values']['siempre'] ?>"><?= $item['responses']['siempre'] ?> × <?= $item['score_values']['siempre'] ?> = <?= $item['scores']['siempre'] ?></td>
                                                <td class="bg-light <?= $item['score_values']['casi_siempre'] == 0 ? 'score-0' : ($item['score_values']['casi_siempre'] == 4 ? 'score-4' : '') ?>" title="<?= $item['responses']['casi_siempre'] ?> × <?= $item['score_values']['casi_siempre'] ?>"><?= $item['responses']['casi_siempre'] ?> × <?= $item['score_values']['casi_siempre'] ?> = <?= $item['scores']['casi_siempre'] ?></td>
                                                <td class="bg-light <?= $item['score_values']['algunas_veces'] == 0 ? 'score-0' : ($item['score_values']['algunas_veces'] == 4 ? 'score-4' : '') ?>" title="<?= $item['responses']['algunas_veces'] ?> × <?= $item['score_values']['algunas_veces'] ?>"><?= $item['responses']['algunas_veces'] ?> × <?= $item['score_values']['algunas_veces'] ?> = <?= $item['scores']['algunas_veces'] ?></td>
                                                <td class="bg-light <?= $item['score_values']['casi_nunca'] == 0 ? 'score-0' : ($item['score_values']['casi_nunca'] == 4 ? 'score-4' : '') ?>" title="<?= $item['responses']['casi_nunca'] ?> × <?= $item['score_values']['casi_nunca'] ?>"><?= $item['responses']['casi_nunca'] ?> × <?= $item['score_values']['casi_nunca'] ?> = <?= $item['scores']['casi_nunca'] ?></td>
                                                <td class="bg-light <?= $item['score_values']['nunca'] == 0 ? 'score-0' : ($item['score_values']['nunca'] == 4 ? 'score-4' : '') ?>" title="<?= $item['responses']['nunca'] ?> × <?= $item['score_values']['nunca'] ?>"><?= $item['responses']['nunca'] ?> × <?= $item['score_values']['nunca'] ?> = <?= $item['scores']['nunca'] ?></td>
                                                <td class="fw-bold"><?= $item['subtotal'] ?></td>
                                                <td class="fw-bold text-primary"><?= number_format($item['average'], 2) ?></td>
                                                <td>
                                                    <?php if ($item['is_inverse']): ?>
                                                        <span class="badge bg-warning text-dark" title="Calificación inversa">INV</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success" title="Calificación normal">NOR</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>

                                        <!-- Fila de resumen -->
                                        <tr class="table-summary">
                                            <td colspan="2">SUMA PROMEDIOS</td>
                                            <td colspan="11"></td>
                                            <td class="text-end fs-5"><?= number_format($validationData['sum_promedios'], 2) ?></td>
                                            <td></td>
                                        </tr>
                                        <tr class="table-summary">
                                            <td colspan="2">FACTOR DE TRANSFORMACIÓN</td>
                                            <td colspan="11"></td>
                                            <td class="text-end fs-5"><?= $validationData['transformation_factor'] ?></td>
                                            <td></td>
                                        </tr>
                                        <tr class="table-summary">
                                            <td colspan="2">DIVISIÓN (Suma / Factor)</td>
                                            <td colspan="11"></td>
                                            <td class="text-end fs-5"><?= number_format($validationData['sum_promedios'] / $validationData['transformation_factor'], 4) ?></td>
                                            <td></td>
                                        </tr>
                                        <tr class="table-summary bg-primary text-white">
                                            <td colspan="2">PUNTAJE TRANSFORMADO</td>
                                            <td colspan="11"></td>
                                            <td class="text-end fs-4"><?= number_format($validationData['puntaje_transformado'], 2) ?></td>
                                            <td></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Baremos -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Tabla de Baremos</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr class="table-secondary">
                                            <th>Sin riesgo o riesgo despreciable</th>
                                            <th>Riesgo bajo</th>
                                            <th>Riesgo medio</th>
                                            <th>Riesgo alto</th>
                                            <th>Riesgo muy alto</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <?php foreach ($dimensionConfig['baremos'] as $nivel => $rango): ?>
                                                <td class="text-center <?= $validationData['nivel_riesgo']['nivel'] === $nivel ? 'table-' . $rango['color'] : '' ?>">
                                                    <?= number_format($rango['min'], 1) ?> - <?= number_format($rango['max'], 1) ?>
                                                    <?php if ($validationData['nivel_riesgo']['nivel'] === $nivel): ?>
                                                        <br><span class="badge bg-<?= $rango['color'] ?>">ACTUAL: <?= number_format($validationData['puntaje_transformado'], 2) ?></span>
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
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
