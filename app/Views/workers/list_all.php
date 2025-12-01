<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - PsyRisk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
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
        .stats-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .stats-box-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }
        .stats-box-warning {
            background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
        }
        .stats-box-info {
            background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%);
        }
        /* DataTables custom styling */
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #ced4da;
            border-radius: 5px;
            padding: 5px 10px;
        }
        .dataTables_wrapper .dataTables_length select {
            border: 1px solid #ced4da;
            border-radius: 5px;
            padding: 5px;
        }
        thead th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        table.dataTable tbody tr:hover {
            background-color: #f0f3ff !important;
        }
        .filter-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 20px;
            margin-bottom: 20px;
        }
        .badge-status {
            font-size: 0.75rem;
            padding: 5px 10px;
        }
        .select2-container--bootstrap-5 .select2-selection {
            min-height: 38px;
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
                    <a class="nav-link" href="<?= base_url('clients') ?>">
                        <i class="fas fa-building me-2"></i> Clientes
                    </a>
                    <a class="nav-link" href="<?= base_url('battery-services') ?>">
                        <i class="fas fa-clipboard-check me-2"></i> Servicios de Bateria
                    </a>
                    <a class="nav-link" href="<?= base_url('consultants') ?>">
                        <i class="fas fa-user-tie me-2"></i> Consultores
                    </a>
                    <a class="nav-link" href="<?= base_url('client-users') ?>">
                        <i class="fas fa-users-cog me-2"></i> Usuarios de Cliente
                    </a>
                    <a class="nav-link active" href="<?= base_url('workers') ?>">
                        <i class="fas fa-users me-2"></i> Trabajadores
                    </a>
                    <a class="nav-link" href="<?= base_url('csv-import') ?>">
                        <i class="fas fa-file-csv me-2"></i> Importar CSV
                    </a>
                    <a class="nav-link" href="<?= base_url('reports') ?>">
                        <i class="fas fa-chart-bar me-2"></i> Informes
                    </a>
                    <hr class="text-white-50 mx-3">
                    <a class="nav-link" href="<?= base_url('logout') ?>">
                        <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesion
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 p-0">
                <nav class="navbar navbar-custom navbar-expand-lg p-3">
                    <div class="container-fluid">
                        <div>
                            <h4 class="mb-0"><?= $title ?></h4>
                            <small class="text-muted">Vista global de todos los trabajadores</small>
                        </div>
                    </div>
                </nav>

                <div class="p-4">
                    <!-- Alertas -->
                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <?= session()->getFlashdata('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?= session()->getFlashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Estadisticas -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="stats-box">
                                <h3 class="mb-0"><?= count($workers) ?></h3>
                                <small>Total Trabajadores</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-box stats-box-success">
                                <h3 class="mb-0"><?= count(array_filter($workers, fn($w) => $w['status'] === 'completado')) ?></h3>
                                <small>Completados</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-box stats-box-warning">
                                <h3 class="mb-0"><?= count(array_filter($workers, fn($w) => $w['status'] === 'pendiente')) ?></h3>
                                <small>Pendientes</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-box stats-box-info">
                                <h3 class="mb-0"><?= count($companies) ?></h3>
                                <small>Empresas</small>
                            </div>
                        </div>
                    </div>

                    <!-- Filtro por Empresa -->
                    <div class="filter-card">
                        <div class="row align-items-end">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-filter me-2"></i>Filtrar por Empresa
                                </label>
                                <select id="companyFilter" class="form-select" style="width: 100%">
                                    <option value="">Todas las empresas</option>
                                    <?php foreach ($companies as $company): ?>
                                        <option value="<?= esc($company['name']) ?>"><?= esc($company['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-clipboard-check me-2"></i>Filtrar por Estado
                                </label>
                                <select id="statusFilter" class="form-select">
                                    <option value="">Todos los estados</option>
                                    <option value="pendiente">Pendiente</option>
                                    <option value="en_progreso">En Progreso</option>
                                    <option value="completado">Completado</option>
                                    <option value="no_participa">No Participa</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button id="clearFilters" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-times me-2"></i>Limpiar Filtros
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de Trabajadores -->
                    <div class="card">
                        <div class="card-body">
                            <table id="workersTable" class="table table-striped table-hover" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Documento</th>
                                        <th>Email</th>
                                        <th>Empresa</th>
                                        <th>Servicio</th>
                                        <th>Forma</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($workers as $worker): ?>
                                        <tr>
                                            <td>
                                                <strong><?= esc($worker['name']) ?></strong>
                                                <br><small class="text-muted"><?= esc($worker['position'] ?? '') ?></small>
                                            </td>
                                            <td>
                                                <small class="text-muted"><?= esc($worker['document_type'] ?? 'CC') ?></small>
                                                <?= esc($worker['document']) ?>
                                            </td>
                                            <td><?= esc($worker['email']) ?></td>
                                            <td><?= esc($worker['company_name']) ?></td>
                                            <td>
                                                <a href="<?= base_url('workers/service/' . $worker['service_id']) ?>" class="text-decoration-none">
                                                    <?= esc($worker['service_name']) ?>
                                                </a>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $worker['intralaboral_type'] === 'A' ? 'primary' : 'info' ?>">
                                                    Forma <?= esc($worker['intralaboral_type']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php
                                                $statusClass = match($worker['status']) {
                                                    'completado' => 'success',
                                                    'en_progreso' => 'warning',
                                                    'no_participa' => 'secondary',
                                                    default => 'danger'
                                                };
                                                $statusText = match($worker['status']) {
                                                    'completado' => 'Completado',
                                                    'en_progreso' => 'En Progreso',
                                                    'no_participa' => 'No Participa',
                                                    default => 'Pendiente'
                                                };
                                                ?>
                                                <span class="badge bg-<?= $statusClass ?> badge-status">
                                                    <?= $statusText ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($worker['status'] === 'completado'): ?>
                                                    <a href="<?= base_url('workers/results/' . $worker['id']) ?>"
                                                       class="btn btn-sm btn-outline-primary" title="Ver Resultados">
                                                        <i class="fas fa-chart-bar"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <a href="<?= base_url('workers/service/' . $worker['service_id']) ?>"
                                                   class="btn btn-sm btn-outline-secondary" title="Ir al Servicio">
                                                    <i class="fas fa-external-link-alt"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicializar DataTable
            var table = $('#workersTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },
                pageLength: 25,
                order: [[0, 'asc']],
                columnDefs: [
                    { orderable: false, targets: -1 } // Columna de acciones no ordenable
                ]
            });

            // Inicializar Select2
            $('#companyFilter').select2({
                theme: 'bootstrap-5',
                placeholder: 'Seleccione una empresa',
                allowClear: true
            });

            // Filtro por empresa
            $('#companyFilter').on('change', function() {
                var company = $(this).val();
                table.column(3).search(company).draw();
            });

            // Filtro por estado
            $('#statusFilter').on('change', function() {
                var status = $(this).val();
                var searchTerm = '';
                if (status === 'pendiente') searchTerm = 'Pendiente';
                else if (status === 'en_progreso') searchTerm = 'En Progreso';
                else if (status === 'completado') searchTerm = 'Completado';
                else if (status === 'no_participa') searchTerm = 'No Participa';

                table.column(6).search(searchTerm).draw();
            });

            // Limpiar filtros
            $('#clearFilters').on('click', function() {
                $('#companyFilter').val('').trigger('change');
                $('#statusFilter').val('');
                table.search('').columns().search('').draw();
            });
        });
    </script>
</body>
</html>
