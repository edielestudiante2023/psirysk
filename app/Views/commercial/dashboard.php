<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Comercial - PsyRisk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
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
        .stat-card {
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border: none;
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-card .icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }
        .navbar-custom {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .order-card {
            border-left: 4px solid #667eea;
            transition: all 0.3s;
        }
        .order-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
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
                    <small class="text-white-50">Equipo Gladiator</small>
                </div>

                <nav class="nav flex-column mt-4">
                    <a class="nav-link active" href="<?= base_url('commercial') ?>">
                        <i class="fas fa-home me-2"></i> Dashboard
                    </a>
                    <a class="nav-link" href="<?= base_url('commercial/orders') ?>">
                        <i class="fas fa-file-contract me-2"></i> Órdenes de Servicio
                    </a>
                    <a class="nav-link" href="<?= base_url('companies') ?>">
                        <i class="fas fa-building me-2"></i> Clientes
                    </a>
                    <a class="nav-link" href="<?= base_url('satisfaction/dashboard') ?>">
                        <i class="fas fa-star me-2"></i> Encuestas Satisfaccion
                    </a>
                    <hr class="text-white-50 mx-3">
                    <a class="nav-link" href="<?= base_url('logout') ?>">
                        <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 p-0">
                <!-- Top Navbar -->
                <nav class="navbar navbar-custom navbar-expand-lg p-3">
                    <div class="container-fluid">
                        <h4 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Dashboard Comercial - Equipo Gladiator</h4>
                        <div class="ms-auto">
                            <button class="btn btn-primary me-2" onclick="location.href='<?= base_url('commercial/create') ?>'">
                                <i class="fas fa-plus me-2"></i>Nueva Orden
                            </button>
                            <button class="btn btn-success" onclick="location.href='<?= base_url('companies/create') ?>'">
                                <i class="fas fa-building me-2"></i>Nuevo Cliente
                            </button>
                        </div>
                    </div>
                </nav>

                <!-- Content -->
                <div class="p-4">
                    <!-- Statistics Cards -->
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card stat-card bg-primary text-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-uppercase mb-1">Clientes</h6>
                                        <h2 class="fw-bold mb-0"><?= $totalClients ?></h2>
                                    </div>
                                    <i class="fas fa-building icon"></i>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card stat-card bg-success text-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-uppercase mb-1">Órdenes Totales</h6>
                                        <h2 class="fw-bold mb-0"><?= $totalOrders ?></h2>
                                    </div>
                                    <i class="fas fa-file-contract icon"></i>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card stat-card bg-warning text-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-uppercase mb-1">Órdenes Abiertas</h6>
                                        <h2 class="fw-bold mb-0"><?= $openOrders ?></h2>
                                    </div>
                                    <i class="fas fa-clock icon"></i>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card stat-card bg-info text-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-uppercase mb-1">Órdenes Cerradas</h6>
                                        <h2 class="fw-bold mb-0"><?= $closedOrders ?></h2>
                                    </div>
                                    <i class="fas fa-check-circle icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Orders -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card shadow-sm border-0">
                                <div class="card-header bg-white py-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0"><i class="fas fa-history text-primary me-2"></i>Últimas Órdenes</h5>
                                        <a href="<?= base_url('commercial/orders') ?>" class="btn btn-sm btn-outline-primary">Ver Todas</a>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($recentOrders)): ?>
                                        <div class="text-center py-5">
                                            <i class="fas fa-file-contract fa-4x text-muted mb-3"></i>
                                            <p class="text-muted">No hay órdenes de servicio registradas aún.</p>
                                            <button class="btn btn-primary" onclick="location.href='<?= base_url('commercial/create') ?>'">
                                                <i class="fas fa-plus me-2"></i>Crear Primera Orden
                                            </button>
                                        </div>
                                    <?php else: ?>
                                        <div class="list-group list-group-flush">
                                            <?php foreach ($recentOrders as $order): ?>
                                                <div class="list-group-item order-card mb-2">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div>
                                                            <h6 class="mb-1 fw-bold"><?= esc($order['service_name']) ?></h6>
                                                            <p class="mb-1">
                                                                <i class="fas fa-building text-muted me-2"></i>
                                                                <strong><?= esc($order['company_name']) ?></strong>
                                                            </p>
                                                            <small class="text-muted">
                                                                <i class="fas fa-calendar me-1"></i>
                                                                <?= date('d/m/Y', strtotime($order['created_at'])) ?>
                                                            </small>
                                                        </div>
                                                        <div class="text-end">
                                                            <span class="badge bg-<?= $order['status'] === 'finalizado' ? 'success' : ($order['status'] === 'en_curso' ? 'warning' : 'secondary') ?> mb-2">
                                                                <?= ucfirst(str_replace('_', ' ', $order['status'])) ?>
                                                            </span>
                                                            <br>
                                                            <a href="<?= base_url('workers/service/' . $order['id']) ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                                                <i class="fas fa-eye me-1"></i>Ver Detalles
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
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
