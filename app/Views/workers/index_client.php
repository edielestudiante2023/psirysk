<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - PsyRisk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
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
        thead input {
            width: 100%;
            padding: 5px;
            box-sizing: border-box;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.875rem;
        }
        thead th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        table.dataTable tbody tr:hover {
            background-color: #f0f3ff !important;
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
                    <small class="text-white-50"><?= ucfirst(str_replace('_', ' ', session()->get('role_name'))) ?></small>
                </div>
                <nav class="nav flex-column mt-4">
                    <a class="nav-link" href="<?= base_url('dashboard') ?>">
                        <i class="fas fa-home me-2"></i> Dashboard
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
                            <small class="text-muted"><?= esc($service['service_name']) ?> - <?= esc($service['company_name']) ?></small>
                        </div>
                    </div>
                </nav>

                <div class="p-4">
                    <!-- Estadisticas -->
                    <div class="row mb-4">
                        <div class="col">
                            <div class="stats-box">
                                <h3 class="mb-0"><?= count($workers) ?></h3>
                                <small>Total Trabajadores</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="stats-box" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                                <h3 class="mb-0"><?= count(array_filter($workers, fn($w) => $w['status'] === 'completado')) ?></h3>
                                <small>Completados</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="stats-box" style="background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);">
                                <h3 class="mb-0"><?= count(array_filter($workers, fn($w) => $w['status'] === 'en_progreso')) ?></h3>
                                <small>En Proceso</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="stats-box" style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%);">
                                <h3 class="mb-0"><?= count(array_filter($workers, fn($w) => $w['status'] === 'pendiente')) ?></h3>
                                <small>Pendientes</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="stats-box" style="background: linear-gradient(135deg, #212529 0%, #343a40 100%);">
                                <h3 class="mb-0"><?= count(array_filter($workers, fn($w) => $w['status'] === 'no_participo')) ?></h3>
                                <small>No Participó</small>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de trabajadores -->
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <?php if (empty($workers)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-users fa-4x text-muted mb-3"></i>
                                    <h5>No hay trabajadores en este servicio</h5>
                                    <p class="text-muted">Los trabajadores seran cargados por el consultor asignado.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table id="workersTable" class="table table-hover table-striped">
                                        <thead>
                                            <tr>
                                                <th>Documento</th>
                                                <th>Nombre</th>
                                                <th>Cargo</th>
                                                <th>Area</th>
                                                <th>Tipo</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                            <tr class="filters">
                                                <th><input type="text" placeholder="Buscar documento"></th>
                                                <th><input type="text" placeholder="Buscar nombre"></th>
                                                <th><input type="text" placeholder="Buscar cargo"></th>
                                                <th><input type="text" placeholder="Buscar area"></th>
                                                <th><input type="text" placeholder="Tipo"></th>
                                                <th><input type="text" placeholder="Estado"></th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($workers as $worker): ?>
                                                <tr>
                                                    <td>
                                                        <?= esc($worker['document_type']) ?>
                                                        <?= esc($worker['document']) ?>
                                                    </td>
                                                    <td><strong><?= esc($worker['name']) ?></strong></td>
                                                    <td><?= esc($worker['position']) ?></td>
                                                    <td><?= esc($worker['area'] ?? '-') ?></td>
                                                    <td>
                                                        <span class="badge bg-<?= $worker['intralaboral_type'] === 'A' ? 'primary' : 'info' ?>">
                                                            Forma <?= $worker['intralaboral_type'] ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        $statusColors = [
                                                            'pendiente' => 'secondary',
                                                            'en_progreso' => 'warning',
                                                            'completado' => 'success',
                                                            'no_participo' => 'dark'
                                                        ];
                                                        $statusLabels = [
                                                            'pendiente' => 'Pendiente',
                                                            'en_progreso' => 'En Progreso',
                                                            'completado' => 'Completado',
                                                            'no_participo' => 'No Participó'
                                                        ];
                                                        ?>
                                                        <span class="badge bg-<?= $statusColors[$worker['status']] ?? 'secondary' ?>">
                                                            <?= $statusLabels[$worker['status']] ?? ucfirst($worker['status']) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php if ($worker['status'] === 'completado'): ?>
                                                            <a href="<?= base_url('workers/results/' . $worker['id']) ?>"
                                                               class="btn btn-sm btn-outline-info"
                                                               target="_blank"
                                                               title="Ver resultados individuales">
                                                                <i class="fas fa-chart-bar me-1"></i> Ver Resultados
                                                            </a>
                                                        <?php elseif ($worker['status'] === 'no_participo'): ?>
                                                            <span class="text-muted small">
                                                                <i class="fas fa-user-slash me-1"></i> No Participó
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="text-muted small">
                                                                <i class="fas fa-clock me-1"></i> Pendiente
                                                            </span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Boton de regreso -->
                                <div class="mt-3">
                                    <a href="<?= base_url('client/battery-services/' . $service['id']) ?>" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left me-2"></i>Volver al Servicio
                                    </a>
                                </div>
                            <?php endif; ?>
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
    <script>
        // Initialize DataTable with column filters
        $(document).ready(function() {
            var table = $('#workersTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },
                pageLength: 25,
                order: [[1, 'asc']], // Ordenar por nombre por defecto
                columnDefs: [
                    { orderable: false, targets: -1 } // Deshabilitar orden en columna Acciones
                ],
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip'
            });

            // Agregar filtros en cada columna
            $('#workersTable thead .filters th').each(function(i) {
                if (i < 6) { // Solo columnas 0-5 (no Acciones)
                    $('input', this).on('keyup change', function() {
                        if (table.column(i).search() !== this.value) {
                            table.column(i).search(this.value).draw();
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>
