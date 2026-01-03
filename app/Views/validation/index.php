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
        .info-card {
            border-left: 4px solid #0d6efd;
        }
        .dimension-link {
            transition: all 0.3s ease;
            border-left: 3px solid transparent !important;
        }
        .dimension-link:hover {
            background-color: #f8f9fa;
            border-left-color: #0d6efd !important;
            padding-left: calc(3rem + 3px) !important;
        }
        .domain-section {
            transition: all 0.3s ease;
        }
        .domain-header {
            position: sticky;
            top: 0;
            z-index: 10;
            border-bottom: 2px solid #dee2e6;
        }
        .domain-header:hover {
            background-color: #e9ecef !important;
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
                            <h4 class="mb-0"><i class="fas fa-shield-check text-primary me-2"></i><?= esc($title) ?></h4>
                            <small class="text-muted"><?= esc($service['service_name']) ?> - <?= esc($service['company_name']) ?></small>
                        </div>
                        <a href="<?= base_url('battery-services/' . $service['id']) ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Volver al Servicio
                        </a>
                    </div>
                </nav>

                <div class="p-4">
                    <!-- Información General -->
                    <div class="card info-card shadow-sm mb-4">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-info-circle text-primary me-2"></i>Información del Servicio</h5>
                            <div class="row">
                                <div class="col-md-3">
                                    <p class="mb-1 text-muted small">Total Trabajadores</p>
                                    <h4 class="text-primary"><?= count($workers) ?></h4>
                                    <small class="text-muted">completados</small>
                                </div>
                                <div class="col-md-3">
                                    <p class="mb-1 text-muted small">Forma A</p>
                                    <h4 class="text-success"><?= count(array_filter($workers, fn($w) => $w['intralaboral_type'] === 'A')) ?></h4>
                                    <small class="text-muted">trabajadores</small>
                                </div>
                                <div class="col-md-3">
                                    <p class="mb-1 text-muted small">Forma B</p>
                                    <h4 class="text-info"><?= count(array_filter($workers, fn($w) => $w['intralaboral_type'] === 'B')) ?></h4>
                                    <small class="text-muted">trabajadores</small>
                                </div>
                                <div class="col-md-3">
                                    <p class="mb-1 text-muted small">Estado</p>
                                    <h5><span class="badge bg-success"><?= esc($service['status']) ?></span></h5>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de Procesamiento -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>Procesamiento de Validaciones</h5>
                        </div>
                        <div class="card-body">
                            <?php if (session()->getFlashdata('success')): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="fas fa-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>
                            <?php if (session()->getFlashdata('error')): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fas fa-exclamation-triangle me-2"></i><?= session()->getFlashdata('error') ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card <?= $dimensionsProcessed ? 'border-success' : 'border-warning' ?> h-100">
                                        <div class="card-body">
                                            <h6 class="card-title">
                                                <?php if ($dimensionsProcessed): ?>
                                                    <i class="fas fa-check-circle text-success me-2"></i>Dimensiones Procesadas
                                                <?php else: ?>
                                                    <i class="fas fa-exclamation-circle text-warning me-2"></i>Dimensiones Pendientes
                                                <?php endif; ?>
                                            </h6>
                                            <p class="card-text small text-muted">
                                                <?php if ($dimensionsProcessed): ?>
                                                    Las dimensiones ya han sido procesadas y validadas. Puede re-procesar para actualizar los datos.
                                                <?php else: ?>
                                                    Procese las dimensiones para generar las validaciones desde las respuestas originales.
                                                <?php endif; ?>
                                            </p>
                                            <form action="<?= base_url('validation/process-dimensions/' . $service['id']) ?>" method="post" class="d-inline">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn <?= $dimensionsProcessed ? 'btn-outline-success' : 'btn-success' ?> btn-sm">
                                                    <i class="fas fa-sync-alt me-1"></i><?= $dimensionsProcessed ? 'Re-procesar' : 'Procesar' ?> Dimensiones
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card <?= $domainsProcessed ? 'border-success' : 'border-secondary' ?> h-100">
                                        <div class="card-body">
                                            <h6 class="card-title">
                                                <?php if ($domainsProcessed): ?>
                                                    <i class="fas fa-check-circle text-success me-2"></i>Dominios Procesados
                                                <?php elseif ($dimensionsProcessed): ?>
                                                    <i class="fas fa-exclamation-circle text-warning me-2"></i>Dominios Pendientes
                                                <?php else: ?>
                                                    <i class="fas fa-lock text-secondary me-2"></i>Dominios Bloqueados
                                                <?php endif; ?>
                                            </h6>
                                            <p class="card-text small text-muted">
                                                <?php if ($domainsProcessed): ?>
                                                    Los dominios ya han sido procesados. Puede re-procesar para actualizar los datos.
                                                <?php elseif ($dimensionsProcessed): ?>
                                                    Procese los dominios para validar las sumatorias de dimensiones.
                                                <?php else: ?>
                                                    Debe procesar las dimensiones primero antes de procesar los dominios.
                                                <?php endif; ?>
                                            </p>
                                            <form action="<?= base_url('validation/process-domains/' . $service['id']) ?>" method="post" class="d-inline">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn <?= $domainsProcessed ? 'btn-outline-success' : 'btn-success' ?> btn-sm" <?= !$dimensionsProcessed ? 'disabled' : '' ?>>
                                                    <i class="fas fa-sync-alt me-1"></i><?= $domainsProcessed ? 'Re-procesar' : 'Procesar' ?> Dominios
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if ($errorsCount > 0): ?>
                                <div class="alert alert-warning mt-3 mb-0">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Se encontraron <strong><?= $errorsCount ?></strong> discrepancias entre los cálculos y la base de datos.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Validación Intralaboral Forma A -->
                    <div class="card shadow-sm mb-4" id="intralaboral-section-a">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>Cuestionario de Factores de Riesgo Psicosocial Intralaboral - Forma A</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card <?= $intralaboralTotalProcessedA ? 'border-success' : 'border-secondary' ?> h-100">
                                        <div class="card-body">
                                            <h6 class="card-title">
                                                <?php if ($intralaboralTotalProcessedA): ?>
                                                    <i class="fas fa-check-circle text-success me-2"></i>Total Procesado
                                                <?php elseif ($domainsProcessed): ?>
                                                    <i class="fas fa-exclamation-circle text-warning me-2"></i>Total Pendiente
                                                <?php else: ?>
                                                    <i class="fas fa-lock text-secondary me-2"></i>Total Bloqueado
                                                <?php endif; ?>
                                            </h6>
                                            <p class="card-text small text-muted">
                                                <?php if ($intralaboralTotalProcessedA): ?>
                                                    El total intralaboral Forma A ya ha sido procesado.
                                                <?php elseif ($domainsProcessed): ?>
                                                    Procese el total intralaboral Forma A.
                                                <?php else: ?>
                                                    Debe procesar los dominios primero.
                                                <?php endif; ?>
                                            </p>
                                            <form action="<?= base_url('validation/process-total-intralaboral/' . $service['id'] . '/A') ?>" method="post" class="d-inline" onsubmit="return false;">
                                                <?= csrf_field() ?>
                                                <button type="button" onclick="submitFormWithoutScroll(this.form, '#intralaboral-section-a')" class="btn <?= $intralaboralTotalProcessedA ? 'btn-outline-primary' : 'btn-primary' ?> btn-sm" <?= !$domainsProcessed ? 'disabled' : '' ?>>
                                                    <i class="fas fa-sync-alt me-1"></i><?= $intralaboralTotalProcessedA ? 'Re-procesar' : 'Procesar' ?> Total
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border-info h-100">
                                        <div class="card-body">
                                            <h6 class="card-title">
                                                <i class="fas fa-eye text-info me-2"></i>Ver Validación Total
                                            </h6>
                                            <p class="card-text small text-muted">
                                                <?php if ($intralaboralTotalProcessedA): ?>
                                                    Revise los resultados de la validación total.
                                                <?php else: ?>
                                                    Debe procesar el total primero para ver los resultados.
                                                <?php endif; ?>
                                            </p>
                                            <a href="<?= base_url('validation/total/' . $service['id'] . '/A') ?>" target="_blank" class="btn btn-outline-info btn-sm" <?= !$intralaboralTotalProcessedA ? 'disabled' : '' ?>>
                                                <i class="fas fa-check-circle me-1"></i>Validar Total
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if ($intralaboralErrorsCountA > 0): ?>
                                <div class="alert alert-warning mt-3 mb-0">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Se encontraron <strong><?= $intralaboralErrorsCountA ?></strong> discrepancias en intralaboral Forma A:
                                    <ul class="mb-0 mt-2">
                                        <?php foreach ($intralaboralErrorsA as $error): ?>
                                            <li><strong><?= esc($error['element_name']) ?></strong> (Diferencia: <?= number_format($error['difference'], 2) ?>)</li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-body p-0 border-top">
                            <?php foreach ($domainsFormaA as $domainIndex => $domain): ?>
                                <div class="domain-section <?= $domainIndex > 0 ? 'border-top' : '' ?>">
                                    <div class="domain-header bg-light p-3 d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0 text-primary fw-bold">
                                                <i class="fas fa-folder me-2"></i><?= esc($domain['name']) ?>
                                            </h6>
                                            <small class="text-muted"><?= count($domain['dimensions']) ?> dimensiones</small>
                                        </div>
                                        <a href="<?= base_url('validation/domain/' . $service['id'] . '/' . $domain['key'] . '/A') ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-check-circle me-1"></i>Validar Dominio
                                        </a>
                                    </div>
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($domain['dimensions'] as $dimension): ?>
                                            <a href="<?= base_url('validation/dimension/' . $service['id'] . '/' . $dimension['key'] . '/A') ?>" target="_blank" class="list-group-item list-group-item-action dimension-link border-0 ps-5">
                                                <div class="d-flex w-100 justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-1"><i class="fas fa-angle-right me-2 text-muted"></i><?= esc($dimension['name']) ?></h6>
                                                        <small class="text-muted">Ítems: <?= $dimension['item_range'] ?> (<?= $dimension['item_count'] ?> preguntas)</small>
                                                    </div>
                                                    <span class="badge bg-primary rounded-pill">Validar <i class="fas fa-external-link-alt ms-1"></i></span>
                                                </div>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Validación Forma B -->
                    <div class="card shadow-sm" id="intralaboral-section-b">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>Cuestionario de Factores de Riesgo Psicosocial Intralaboral - Forma B</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card <?= $intralaboralTotalProcessedB ? 'border-success' : 'border-secondary' ?> h-100">
                                        <div class="card-body">
                                            <h6 class="card-title">
                                                <?php if ($intralaboralTotalProcessedB): ?>
                                                    <i class="fas fa-check-circle text-success me-2"></i>Total Procesado
                                                <?php elseif ($domainsProcessed): ?>
                                                    <i class="fas fa-exclamation-circle text-warning me-2"></i>Total Pendiente
                                                <?php else: ?>
                                                    <i class="fas fa-lock text-secondary me-2"></i>Total Bloqueado
                                                <?php endif; ?>
                                            </h6>
                                            <p class="card-text small text-muted">
                                                <?php if ($intralaboralTotalProcessedB): ?>
                                                    El total intralaboral Forma B ya ha sido procesado.
                                                <?php elseif ($domainsProcessed): ?>
                                                    Procese el total intralaboral Forma B.
                                                <?php else: ?>
                                                    Debe procesar los dominios primero.
                                                <?php endif; ?>
                                            </p>
                                            <form action="<?= base_url('validation/process-total-intralaboral/' . $service['id'] . '/B') ?>" method="post" class="d-inline" onsubmit="return false;">
                                                <?= csrf_field() ?>
                                                <button type="button" onclick="submitFormWithoutScroll(this.form, '#intralaboral-section-b')" class="btn <?= $intralaboralTotalProcessedB ? 'btn-outline-success' : 'btn-success' ?> btn-sm" <?= !$domainsProcessed ? 'disabled' : '' ?>>
                                                    <i class="fas fa-sync-alt me-1"></i><?= $intralaboralTotalProcessedB ? 'Re-procesar' : 'Procesar' ?> Total
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border-info h-100">
                                        <div class="card-body">
                                            <h6 class="card-title">
                                                <i class="fas fa-eye text-info me-2"></i>Ver Validación Total
                                            </h6>
                                            <p class="card-text small text-muted">
                                                <?php if ($intralaboralTotalProcessedB): ?>
                                                    Revise los resultados de la validación total.
                                                <?php else: ?>
                                                    Debe procesar el total primero para ver los resultados.
                                                <?php endif; ?>
                                            </p>
                                            <a href="<?= base_url('validation/total/' . $service['id'] . '/B') ?>" target="_blank" class="btn btn-outline-info btn-sm" <?= !$intralaboralTotalProcessedB ? 'disabled' : '' ?>>
                                                <i class="fas fa-check-circle me-1"></i>Validar Total
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if ($intralaboralErrorsCountB > 0): ?>
                                <div class="alert alert-warning mt-3 mb-0">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Se encontraron <strong><?= $intralaboralErrorsCountB ?></strong> discrepancias en intralaboral Forma B:
                                    <ul class="mb-0 mt-2">
                                        <?php foreach ($intralaboralErrorsB as $error): ?>
                                            <li><strong><?= esc($error['element_name']) ?></strong> (Diferencia: <?= number_format($error['difference'], 2) ?>)</li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-body p-0 border-top">
                            <?php foreach ($domainsFormaB as $domainIndex => $domain): ?>
                                <div class="domain-section <?= $domainIndex > 0 ? 'border-top' : '' ?>">
                                    <div class="domain-header bg-light p-3 d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0 text-success fw-bold">
                                                <i class="fas fa-folder me-2"></i><?= esc($domain['name']) ?>
                                            </h6>
                                            <small class="text-muted"><?= count($domain['dimensions']) ?> dimensiones</small>
                                        </div>
                                        <a href="<?= base_url('validation/domain/' . $service['id'] . '/' . $domain['key'] . '/B') ?>" target="_blank" class="btn btn-outline-success btn-sm">
                                            <i class="fas fa-check-circle me-1"></i>Validar Dominio
                                        </a>
                                    </div>
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($domain['dimensions'] as $dimension): ?>
                                            <a href="<?= base_url('validation/dimension/' . $service['id'] . '/' . $dimension['key'] . '/B') ?>" target="_blank" class="list-group-item list-group-item-action dimension-link border-0 ps-5">
                                                <div class="d-flex w-100 justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-1"><i class="fas fa-angle-right me-2 text-muted"></i><?= esc($dimension['name']) ?></h6>
                                                        <small class="text-muted">Ítems: <?= $dimension['item_range'] ?> (<?= $dimension['item_count'] ?> preguntas)</small>
                                                    </div>
                                                    <span class="badge bg-success rounded-pill">Validar <i class="fas fa-external-link-alt ms-1"></i></span>
                                                </div>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Validación Extralaboral Forma A -->
                    <div class="card shadow-sm mb-4 mt-4" id="extralaboral-section-a">
                        <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-home me-2"></i>Cuestionario de Factores de Riesgo Psicosocial Extralaboral - Forma A</h5>
                            <div>
                                <a href="<?= base_url('validation/total-extralaboral/' . $service['id'] . '/A') ?>" target="_blank" class="btn btn-dark btn-sm">
                                    <i class="fas fa-check-circle me-1"></i>Validar Total
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Botones de procesamiento -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="card <?= $extralaboralDimensionsProcessedA ? 'border-success' : 'border-secondary' ?> h-100">
                                        <div class="card-body">
                                            <h6 class="card-title">
                                                <?php if ($extralaboralDimensionsProcessedA): ?>
                                                    <i class="fas fa-check-circle text-success me-2"></i>Dimensiones Procesadas
                                                <?php else: ?>
                                                    <i class="fas fa-exclamation-circle text-warning me-2"></i>Dimensiones Pendientes
                                                <?php endif; ?>
                                            </h6>
                                            <p class="card-text small text-muted">
                                                <?php if ($extralaboralDimensionsProcessedA): ?>
                                                    Las dimensiones extralaboral Forma A ya han sido procesadas.
                                                <?php else: ?>
                                                    Procese las dimensiones extralaboral para trabajadores Forma A.
                                                <?php endif; ?>
                                            </p>
                                            <form action="<?= base_url('validation/process-dimensions-extralaboral/' . $service['id'] . '/A') ?>" method="post" class="d-inline" onsubmit="return false;">
                                                <?= csrf_field() ?>
                                                <button type="button" onclick="submitFormWithoutScroll(this.form, '#extralaboral-section-a')" class="btn <?= $extralaboralDimensionsProcessedA ? 'btn-outline-success' : 'btn-success' ?> btn-sm">
                                                    <i class="fas fa-sync-alt me-1"></i><?= $extralaboralDimensionsProcessedA ? 'Re-procesar' : 'Procesar' ?> Dimensiones
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card <?= $extralaboralTotalProcessedA ? 'border-success' : 'border-secondary' ?> h-100">
                                        <div class="card-body">
                                            <h6 class="card-title">
                                                <?php if ($extralaboralTotalProcessedA): ?>
                                                    <i class="fas fa-check-circle text-success me-2"></i>Total Procesado
                                                <?php elseif ($extralaboralDimensionsProcessedA): ?>
                                                    <i class="fas fa-exclamation-circle text-warning me-2"></i>Total Pendiente
                                                <?php else: ?>
                                                    <i class="fas fa-lock text-secondary me-2"></i>Total Bloqueado
                                                <?php endif; ?>
                                            </h6>
                                            <p class="card-text small text-muted">
                                                <?php if ($extralaboralTotalProcessedA): ?>
                                                    El total extralaboral Forma A ya ha sido procesado.
                                                <?php elseif ($extralaboralDimensionsProcessedA): ?>
                                                    Procese el total extralaboral Forma A.
                                                <?php else: ?>
                                                    Debe procesar las dimensiones primero.
                                                <?php endif; ?>
                                            </p>
                                            <form action="<?= base_url('validation/process-total-extralaboral/' . $service['id'] . '/A') ?>" method="post" class="d-inline" onsubmit="return false;">
                                                <?= csrf_field() ?>
                                                <button type="button" onclick="submitFormWithoutScroll(this.form, '#extralaboral-section-a')" class="btn <?= $extralaboralTotalProcessedA ? 'btn-outline-success' : 'btn-success' ?> btn-sm" <?= !$extralaboralDimensionsProcessedA ? 'disabled' : '' ?>>
                                                    <i class="fas fa-sync-alt me-1"></i><?= $extralaboralTotalProcessedA ? 'Re-procesar' : 'Procesar' ?> Total
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if ($extralaboralErrorsCountA > 0): ?>
                                <div class="alert alert-warning mt-3 mb-0">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Se encontraron <strong><?= $extralaboralErrorsCountA ?></strong> discrepancias en extralaboral Forma A:
                                    <ul class="mb-0 mt-2">
                                        <?php foreach ($extralaboralErrorsA as $error): ?>
                                            <li><strong><?= esc($error['element_name']) ?></strong> (Diferencia: <?= number_format($error['difference'], 2) ?>)</li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <!-- Lista de dimensiones -->
                            <div class="list-group list-group-flush mt-3">
                                <?php foreach ($extralaboralDimensions as $dimension): ?>
                                    <a href="<?= base_url('validation/dimension-extralaboral/' . $service['id'] . '/' . $dimension['key'] . '/A') ?>" target="_blank" class="list-group-item list-group-item-action dimension-link border-0 ps-3">
                                        <div class="d-flex w-100 justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1"><i class="fas fa-angle-right me-2 text-muted"></i><?= esc($dimension['name']) ?></h6>
                                                <small class="text-muted">Ítems: <?= $dimension['item_range'] ?> (<?= $dimension['item_count'] ?> preguntas)</small>
                                            </div>
                                            <span class="badge bg-warning text-dark rounded-pill">Validar <i class="fas fa-external-link-alt ms-1"></i></span>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Validación Extralaboral Forma B -->
                    <div class="card shadow-sm mb-4" id="extralaboral-section-b">
                        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-home me-2"></i>Cuestionario de Factores de Riesgo Psicosocial Extralaboral - Forma B</h5>
                            <div>
                                <a href="<?= base_url('validation/total-extralaboral/' . $service['id'] . '/B') ?>" target="_blank" class="btn btn-light btn-sm">
                                    <i class="fas fa-check-circle me-1"></i>Validar Total
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Botones de procesamiento -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="card <?= $extralaboralDimensionsProcessedB ? 'border-success' : 'border-secondary' ?> h-100">
                                        <div class="card-body">
                                            <h6 class="card-title">
                                                <?php if ($extralaboralDimensionsProcessedB): ?>
                                                    <i class="fas fa-check-circle text-success me-2"></i>Dimensiones Procesadas
                                                <?php else: ?>
                                                    <i class="fas fa-exclamation-circle text-warning me-2"></i>Dimensiones Pendientes
                                                <?php endif; ?>
                                            </h6>
                                            <p class="card-text small text-muted">
                                                <?php if ($extralaboralDimensionsProcessedB): ?>
                                                    Las dimensiones extralaboral Forma B ya han sido procesadas.
                                                <?php else: ?>
                                                    Procese las dimensiones extralaboral para trabajadores Forma B.
                                                <?php endif; ?>
                                            </p>
                                            <form action="<?= base_url('validation/process-dimensions-extralaboral/' . $service['id'] . '/B') ?>" method="post" class="d-inline" onsubmit="return false;">
                                                <?= csrf_field() ?>
                                                <button type="button" onclick="submitFormWithoutScroll(this.form, '#extralaboral-section-b')" class="btn <?= $extralaboralDimensionsProcessedB ? 'btn-outline-success' : 'btn-success' ?> btn-sm">
                                                    <i class="fas fa-sync-alt me-1"></i><?= $extralaboralDimensionsProcessedB ? 'Re-procesar' : 'Procesar' ?> Dimensiones
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card <?= $extralaboralTotalProcessedB ? 'border-success' : 'border-secondary' ?> h-100">
                                        <div class="card-body">
                                            <h6 class="card-title">
                                                <?php if ($extralaboralTotalProcessedB): ?>
                                                    <i class="fas fa-check-circle text-success me-2"></i>Total Procesado
                                                <?php elseif ($extralaboralDimensionsProcessedB): ?>
                                                    <i class="fas fa-exclamation-circle text-warning me-2"></i>Total Pendiente
                                                <?php else: ?>
                                                    <i class="fas fa-lock text-secondary me-2"></i>Total Bloqueado
                                                <?php endif; ?>
                                            </h6>
                                            <p class="card-text small text-muted">
                                                <?php if ($extralaboralTotalProcessedB): ?>
                                                    El total extralaboral Forma B ya ha sido procesado.
                                                <?php elseif ($extralaboralDimensionsProcessedB): ?>
                                                    Procese el total extralaboral Forma B.
                                                <?php else: ?>
                                                    Debe procesar las dimensiones primero.
                                                <?php endif; ?>
                                            </p>
                                            <form action="<?= base_url('validation/process-total-extralaboral/' . $service['id'] . '/B') ?>" method="post" class="d-inline" onsubmit="return false;">
                                                <?= csrf_field() ?>
                                                <button type="button" onclick="submitFormWithoutScroll(this.form, '#extralaboral-section-b')" class="btn <?= $extralaboralTotalProcessedB ? 'btn-outline-success' : 'btn-success' ?> btn-sm" <?= !$extralaboralDimensionsProcessedB ? 'disabled' : '' ?>>
                                                    <i class="fas fa-sync-alt me-1"></i><?= $extralaboralTotalProcessedB ? 'Re-procesar' : 'Procesar' ?> Total
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if ($extralaboralErrorsCountB > 0): ?>
                                <div class="alert alert-warning mt-3 mb-0">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Se encontraron <strong><?= $extralaboralErrorsCountB ?></strong> discrepancias en extralaboral Forma B:
                                    <ul class="mb-0 mt-2">
                                        <?php foreach ($extralaboralErrorsB as $error): ?>
                                            <li><strong><?= esc($error['element_name']) ?></strong> (Diferencia: <?= number_format($error['difference'], 2) ?>)</li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <!-- Lista de dimensiones -->
                            <div class="list-group list-group-flush mt-3">
                                <?php foreach ($extralaboralDimensions as $dimension): ?>
                                    <a href="<?= base_url('validation/dimension-extralaboral/' . $service['id'] . '/' . $dimension['key'] . '/B') ?>" target="_blank" class="list-group-item list-group-item-action dimension-link border-0 ps-3">
                                        <div class="d-flex w-100 justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1"><i class="fas fa-angle-right me-2 text-muted"></i><?= esc($dimension['name']) ?></h6>
                                                <small class="text-muted">Ítems: <?= $dimension['item_range'] ?> (<?= $dimension['item_count'] ?> preguntas)</small>
                                            </div>
                                            <span class="badge bg-info rounded-pill">Validar <i class="fas fa-external-link-alt ms-1"></i></span>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección Estrés -->
    <div class="mb-4" id="estres-section">
        <div class="card shadow-sm">
            <div class="card-header bg-gradient text-white" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <h4 class="mb-0"><i class="fas fa-brain me-2"></i>Cuestionario para la Evaluación del Estrés</h4>
            </div>
            <div class="card-body">
                <p class="text-muted mb-4">
                    <i class="fas fa-info-circle me-2"></i>Validación del total de estrés. 31 ítems agrupados en 3 grupos de calificación.
                </p>

                <!-- Forma A -->
                <div class="mb-4" id="estres-section-a">
                    <div class="card border-primary">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-user-tie me-2"></i>Forma A - Jefes, Profesionales y Técnicos</h5>
                            <div>
                                <a href="<?= base_url('validation/total-estres/' . $service['id'] . '/A') ?>" target="_blank" class="btn btn-light btn-sm">
                                    <i class="fas fa-check-circle me-1"></i>Validar Total
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Botón de procesamiento -->
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="card <?= $estresTotalProcessedA ? 'border-success' : 'border-secondary' ?> h-100">
                                        <div class="card-body">
                                            <h6 class="card-title">
                                                <?php if ($estresTotalProcessedA): ?>
                                                    <i class="fas fa-check-circle text-success me-2"></i>Total Procesado
                                                <?php else: ?>
                                                    <i class="fas fa-exclamation-circle text-warning me-2"></i>Total Pendiente
                                                <?php endif; ?>
                                            </h6>
                                            <p class="card-text small text-muted">
                                                <?php if ($estresTotalProcessedA): ?>
                                                    El total de estrés Forma A ya ha sido procesado.
                                                <?php else: ?>
                                                    Procese el total de estrés para trabajadores Forma A.
                                                <?php endif; ?>
                                            </p>
                                            <form action="<?= base_url('validation/process-estres/' . $service['id'] . '/A') ?>" method="post" class="d-inline" onsubmit="return false;">
                                                <?= csrf_field() ?>
                                                <button type="button" onclick="submitFormWithoutScroll(this.form, '#estres-section-a')" class="btn <?= $estresTotalProcessedA ? 'btn-outline-success' : 'btn-success' ?> btn-sm">
                                                    <i class="fas fa-sync-alt me-1"></i><?= $estresTotalProcessedA ? 'Re-procesar' : 'Procesar' ?> Total
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if ($estresErrorsCountA > 0): ?>
                                <div class="alert alert-warning mt-3 mb-0">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Se encontraron <strong><?= $estresErrorsCountA ?></strong> discrepancias en estrés Forma A:
                                    <ul class="mb-0 mt-2">
                                        <?php foreach ($estresErrorsA as $error): ?>
                                            <li><strong><?= esc($error['element_name']) ?></strong> (Diferencia: <?= number_format($error['difference'], 2) ?>)</li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <p class="mb-0 mt-2">
                                        <strong>Solución:</strong> Ejecutar <code>php spark recalculate:estres</code> para recalcular todos los workers.
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Forma B -->
                <div class="mb-0" id="estres-section-b">
                    <div class="card border-info">
                        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-user me-2"></i>Forma B - Auxiliares y Operarios</h5>
                            <div>
                                <a href="<?= base_url('validation/total-estres/' . $service['id'] . '/B') ?>" target="_blank" class="btn btn-light btn-sm">
                                    <i class="fas fa-check-circle me-1"></i>Validar Total
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Botón de procesamiento -->
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="card <?= $estresTotalProcessedB ? 'border-success' : 'border-secondary' ?> h-100">
                                        <div class="card-body">
                                            <h6 class="card-title">
                                                <?php if ($estresTotalProcessedB): ?>
                                                    <i class="fas fa-check-circle text-success me-2"></i>Total Procesado
                                                <?php else: ?>
                                                    <i class="fas fa-exclamation-circle text-warning me-2"></i>Total Pendiente
                                                <?php endif; ?>
                                            </h6>
                                            <p class="card-text small text-muted">
                                                <?php if ($estresTotalProcessedB): ?>
                                                    El total de estrés Forma B ya ha sido procesado.
                                                <?php else: ?>
                                                    Procese el total de estrés para trabajadores Forma B.
                                                <?php endif; ?>
                                            </p>
                                            <form action="<?= base_url('validation/process-estres/' . $service['id'] . '/B') ?>" method="post" class="d-inline" onsubmit="return false;">
                                                <?= csrf_field() ?>
                                                <button type="button" onclick="submitFormWithoutScroll(this.form, '#estres-section-b')" class="btn <?= $estresTotalProcessedB ? 'btn-outline-success' : 'btn-success' ?> btn-sm">
                                                    <i class="fas fa-sync-alt me-1"></i><?= $estresTotalProcessedB ? 'Re-procesar' : 'Procesar' ?> Total
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if ($estresErrorsCountB > 0): ?>
                                <div class="alert alert-warning mt-3 mb-0">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Se encontraron <strong><?= $estresErrorsCountB ?></strong> discrepancias en estrés Forma B:
                                    <ul class="mb-0 mt-2">
                                        <?php foreach ($estresErrorsB as $error): ?>
                                            <li><strong><?= esc($error['element_name']) ?></strong> (Diferencia: <?= number_format($error['difference'], 2) ?>)</li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <p class="mb-0 mt-2">
                                        <strong>Solución:</strong> Ejecutar <code>php spark recalculate:estres</code> para recalcular todos los workers.
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección: Historial Completo de Validaciones (RESPALDO) -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-dark">
                <div class="card-header bg-dark text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-history me-2"></i>Historial Completo de Validaciones
                    </h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Respaldo de Seguridad:</strong> Accede al historial completo de todas las validaciones procesadas
                        en este servicio. Puedes filtrar, buscar y descargar los datos en formato Excel para análisis externo.
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <h5><i class="fas fa-database me-2"></i>Base de Datos de Validaciones</h5>
                            <p class="text-muted mb-2">
                                Esta vista muestra todos los registros almacenados en la tabla <code>validation_results</code>
                                para este servicio, incluyendo:
                            </p>
                            <ul class="small text-muted">
                                <li>Todas las validaciones de <strong>Intralaboral</strong> (dimensiones, dominios y total)</li>
                                <li>Todas las validaciones de <strong>Extralaboral</strong> (dimensiones y total)</li>
                                <li>Todas las validaciones de <strong>Estrés</strong> (total)</li>
                                <li>Información completa: puntajes calculados, puntajes en BD, diferencias, estado, fecha de procesamiento</li>
                            </ul>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="<?= base_url('validation/history/' . $service['id']) ?>"
                               class="btn btn-dark btn-lg"
                               target="_blank">
                                <i class="fas fa-eye me-2"></i>Ver Historial Completo
                            </a>
                            <p class="text-muted small mt-2 mb-0">
                                <i class="fas fa-external-link-alt me-1"></i>Se abre en nueva pestaña
                            </p>
                        </div>
                    </div>

                    <hr class="my-3">

                    <div class="row">
                        <div class="col-12">
                            <h6 class="mb-2"><i class="fas fa-download me-2"></i>Características del Historial:</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="border rounded p-3 h-100">
                                        <h6 class="text-primary"><i class="fas fa-filter me-2"></i>Filtros Avanzados</h6>
                                        <p class="small mb-0">DataTables con búsqueda en todos los campos, ordenamiento y paginación</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="border rounded p-3 h-100">
                                        <h6 class="text-success"><i class="fas fa-file-excel me-2"></i>Descarga Excel</h6>
                                        <p class="small mb-0">Exporta todos los datos a formato CSV para análisis externo</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="border rounded p-3 h-100">
                                        <h6 class="text-info"><i class="fas fa-save me-2"></i>Respaldo Completo</h6>
                                        <p class="small mb-0">Garantiza trazabilidad de todas las validaciones procesadas</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Toast container for notifications -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
        <div id="successToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-check-circle me-2"></i><span id="toastMessage"></span>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <script>
        // Función para mostrar toast de éxito
        function showSuccessToast(message) {
            const toastElement = document.getElementById('successToast');
            const toastMessageElement = document.getElementById('toastMessage');
            toastMessageElement.textContent = message;

            const toast = new bootstrap.Toast(toastElement, {
                animation: true,
                autohide: true,
                delay: 5000
            });
            toast.show();
        }

        // Función para enviar formulario sin scroll al top
        function submitFormWithoutScroll(form, sectionId) {
            // Guardar posición actual de scroll
            const currentScrollPosition = window.scrollY || window.pageYOffset;

            // Mostrar indicador de carga
            const submitButton = form.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Procesando...';

            // Enviar el formulario
            fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (response.redirected) {
                    // Mostrar mensaje de éxito
                    showSuccessToast('✓ Procesado correctamente. Recargando...');

                    // Recargar la página manteniendo la posición de scroll
                    setTimeout(() => {
                        window.location.href = response.url + '#' + sectionId.substring(1);

                        // Restaurar scroll después de un pequeño delay
                        setTimeout(() => {
                            window.scrollTo(0, currentScrollPosition);
                        }, 100);
                    }, 1000);
                } else {
                    // Si no hay redirección, simplemente recargar
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
                showSuccessToast('❌ Error al procesar. Intente nuevamente.');
            });
        }

        // Mostrar toast si hay mensaje flash al cargar la página
        window.addEventListener('DOMContentLoaded', function() {
            <?php if (session()->getFlashdata('success')): ?>
                showSuccessToast('<?= addslashes(session()->getFlashdata('success')) ?>');
            <?php endif; ?>
        });
    </script>
</body>
</html>
