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

                    <!-- Validación Intralaboral Forma A -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>Cuestionario de Factores de Riesgo Psicosocial Intralaboral - Forma A</h5>
                            <button class="btn btn-light btn-sm">
                                <i class="fas fa-check-circle me-1"></i>Validar Total
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <?php foreach ($domainsFormaA as $domainIndex => $domain): ?>
                                <div class="domain-section <?= $domainIndex > 0 ? 'border-top' : '' ?>">
                                    <div class="domain-header bg-light p-3 d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0 text-primary fw-bold">
                                                <i class="fas fa-folder me-2"></i><?= esc($domain['name']) ?>
                                            </h6>
                                            <small class="text-muted"><?= count($domain['dimensions']) ?> dimensiones</small>
                                        </div>
                                        <button class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-check-circle me-1"></i>Validar Dominio
                                        </button>
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
                    <div class="card shadow-sm">
                        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>Cuestionario de Factores de Riesgo Psicosocial Intralaboral - Forma B</h5>
                            <button class="btn btn-light btn-sm">
                                <i class="fas fa-check-circle me-1"></i>Validar Total
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <?php foreach ($domainsFormaB as $domainIndex => $domain): ?>
                                <div class="domain-section <?= $domainIndex > 0 ? 'border-top' : '' ?>">
                                    <div class="domain-header bg-light p-3 d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0 text-success fw-bold">
                                                <i class="fas fa-folder me-2"></i><?= esc($domain['name']) ?>
                                            </h6>
                                            <small class="text-muted"><?= count($domain['dimensions']) ?> dimensiones</small>
                                        </div>
                                        <button class="btn btn-outline-success btn-sm">
                                            <i class="fas fa-check-circle me-1"></i>Validar Dominio
                                        </button>
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
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
