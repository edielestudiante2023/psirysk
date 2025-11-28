<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - PsyRisk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }
        .main-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .gladiator-header {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            text-align: center;
        }
        .gladiator-header h1 {
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        .gladiator-header .subtitle {
            opacity: 0.9;
            font-size: 1.1rem;
        }
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 0.75rem;
        }
        .status-planificado { background: #3498db; color: white; }
        .status-en_curso { background: #f39c12; color: white; }
        .status-finalizado { background: #27ae60; color: white; }
        .btn-gladiator {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            border: none;
            color: white;
            font-weight: bold;
        }
        .btn-gladiator:hover {
            background: linear-gradient(135deg, #c0392b 0%, #a93226 100%);
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header Gladiator -->
        <div class="gladiator-header">
            <h1><i class="fas fa-shield-alt me-2"></i>EQUIPO GLADIATOR</h1>
            <p class="subtitle">Módulo Comercial - Gestión de Órdenes de Servicio</p>
            <p class="mb-0"><i class="fas fa-user-tie me-2"></i><?= esc(session()->get('name')) ?></p>
        </div>

        <!-- Mensajes -->
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i><?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Card Principal -->
        <div class="main-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3><i class="fas fa-file-contract me-2"></i>Historial de Órdenes de Servicio</h3>
                <div>
                    <a href="<?= base_url('commercial/create') ?>" class="btn btn-gladiator btn-lg">
                        <i class="fas fa-plus me-2"></i>Nueva Orden de Servicio
                    </a>
                    <a href="<?= base_url('logout') ?>" class="btn btn-secondary btn-lg ms-2">
                        <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                    </a>
                </div>
            </div>

            <!-- Tabla de servicios -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Servicio</th>
                            <th>Consultor Asignado</th>
                            <th>Fecha Servicio</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($services)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                    <p>No hay órdenes de servicio registradas</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($services as $service): ?>
                                <tr>
                                    <td><strong>#<?= $service['id'] ?></strong></td>
                                    <td>
                                        <strong><?= esc($service['company_name']) ?></strong><br>
                                        <small class="text-muted">NIT: <?= esc($service['company_nit']) ?></small>
                                    </td>
                                    <td><?= esc($service['service_name']) ?></td>
                                    <td>
                                        <i class="fas fa-user-tie me-1"></i><?= esc($service['consultant_name']) ?><br>
                                        <small class="text-muted"><?= esc($service['consultant_email']) ?></small>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($service['service_date'])) ?></td>
                                    <td>
                                        <span class="status-badge status-<?= $service['status'] ?>">
                                            <?= $service['status'] === 'planificado' ? 'Abierto' : ($service['status'] === 'finalizado' ? 'Cerrado' : 'En Curso') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?= base_url('commercial/download-pdf/' . $service['id']) ?>"
                                           class="btn btn-sm btn-danger"
                                           title="Descargar PDF"
                                           target="_blank">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                        <a href="<?= base_url('workers/service/' . $service['id']) ?>"
                                           class="btn btn-sm btn-primary"
                                           title="Ver trabajadores"
                                           target="_blank">
                                            <i class="fas fa-users"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Estadísticas rápidas -->
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h3><?= count(array_filter($services, fn($s) => $s['status'] === 'planificado')) ?></h3>
                            <p class="mb-0">Órdenes Abiertas</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <h3><?= count(array_filter($services, fn($s) => $s['status'] === 'en_curso')) ?></h3>
                            <p class="mb-0">En Curso</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h3><?= count(array_filter($services, fn($s) => $s['status'] === 'finalizado')) ?></h3>
                            <p class="mb-0">Cerradas</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
