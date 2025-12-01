<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - PsyRisk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
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
                    <small class="text-white-50"><?= ucfirst(session()->get('role_name')) ?></small>
                </div>
                <nav class="nav flex-column mt-4">
                    <a class="nav-link" href="<?= base_url('dashboard') ?>">
                        <i class="fas fa-home me-2"></i> Dashboard
                    </a>
                    <a class="nav-link" href="<?= base_url('companies') ?>">
                        <i class="fas fa-building me-2"></i> Empresas
                    </a>
                    <a class="nav-link active" href="<?= base_url('battery-services') ?>">
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
                            <h4 class="mb-0"><?= $title ?></h4>
                            <small class="text-muted"><?= esc($service['service_name']) ?> - <?= esc($service['company_name']) ?></small>
                        </div>
                        <div>
                            <a href="<?= base_url('battery-services/' . $service['id']) ?>" class="btn btn-outline-secondary me-2">
                                <i class="fas fa-arrow-left me-2"></i>Ver Servicio
                            </a>
                            <a href="<?= base_url('workers/upload/' . $service['id']) ?>" class="btn btn-success">
                                <i class="fas fa-upload me-2"></i>Cargar CSV
                            </a>
                        </div>
                    </div>
                </nav>

                <div class="p-4">
                    <!-- Estadísticas -->
                    <div class="row mb-4">
                        <div class="col-md-2">
                            <div class="stats-box">
                                <h3 class="mb-0"><?= count($workers) ?></h3>
                                <small>Total Trabajadores</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="stats-box" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                                <h3 class="mb-0"><?= count(array_filter($workers, fn($w) => $w['status'] === 'completado')) ?></h3>
                                <small>Completados</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="stats-box" style="background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);">
                                <h3 class="mb-0"><?= count(array_filter($workers, fn($w) => $w['status'] === 'en_proceso')) ?></h3>
                                <small>En Proceso</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="stats-box" style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%);">
                                <h3 class="mb-0"><?= count(array_filter($workers, fn($w) => $w['status'] === 'pendiente')) ?></h3>
                                <small>Pendientes</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="stats-box" style="background: linear-gradient(135deg, #212529 0%, #343a40 100%);">
                                <h3 class="mb-0"><?= count(array_filter($workers, fn($w) => $w['status'] === 'no_participo')) ?></h3>
                                <small>No Participó</small>
                            </div>
                        </div>
                    </div>

                    <!-- Card de Gestión de Cierre -->
                    <?php if (session()->get('role') === 'consultor' && $service['status'] !== 'cerrado'): ?>
                        <?php
                        $completados = count(array_filter($workers, fn($w) => $w['status'] === 'completado'));
                        $total = count($workers);
                        $porcentaje = ($total > 0) ? round(($completados / $total) * 100, 1) : 0;
                        $sinGestionar = count(array_filter($workers, fn($w) => in_array($w['status'], ['en_proceso', 'invitado', 'pendiente'])));
                        ?>
                        <div class="card border-warning mb-4">
                            <div class="card-header bg-warning text-dark">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-hourglass-half me-2"></i>
                                        <strong>Servicio en Proceso</strong>
                                    </div>
                                    <span class="badge bg-light text-dark"><?= $porcentaje ?>% Completado</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h6 class="mb-3">Resumen de Participación:</h6>
                                        <div class="row">
                                            <div class="col-md-3 text-center mb-2">
                                                <div class="small text-muted">Completados</div>
                                                <h5 class="text-success mb-0"><?= $completados ?></h5>
                                            </div>
                                            <div class="col-md-3 text-center mb-2">
                                                <div class="small text-muted">En Proceso</div>
                                                <h5 class="text-warning mb-0"><?= count(array_filter($workers, fn($w) => $w['status'] === 'en_proceso')) ?></h5>
                                            </div>
                                            <div class="col-md-3 text-center mb-2">
                                                <div class="small text-muted">Invitados</div>
                                                <h5 class="text-info mb-0"><?= count(array_filter($workers, fn($w) => $w['status'] === 'invitado')) ?></h5>
                                            </div>
                                            <div class="col-md-3 text-center mb-2">
                                                <div class="small text-muted">Pendientes</div>
                                                <h5 class="text-secondary mb-0"><?= count(array_filter($workers, fn($w) => $w['status'] === 'pendiente')) ?></h5>
                                            </div>
                                        </div>

                                        <?php if ($sinGestionar > 0): ?>
                                            <div class="alert alert-warning mt-3 mb-0">
                                                <small>
                                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                                    Tienes <strong><?= $sinGestionar ?> trabajadores</strong> que requieren gestión antes de cerrar el servicio.
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-4 text-end d-flex align-items-center justify-content-end">
                                        <a href="<?= base_url('workers/service/' . $service['id'] . '/pre-close') ?>"
                                           class="btn btn-primary btn-lg">
                                            <i class="fas fa-check-circle me-2"></i>
                                            Gestionar Cierre de Servicio
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($service['status'] === 'cerrado'): ?>
                        <div class="card border-success mb-4">
                            <div class="card-header bg-success text-white">
                                <i class="fas fa-lock me-2"></i>
                                <strong>Servicio Cerrado</strong>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <p class="mb-2">
                                            <i class="fas fa-calendar-check me-2 text-success"></i>
                                            <strong>Cerrado el:</strong> <?= date('d/m/Y H:i', strtotime($service['closed_at'])) ?>
                                        </p>
                                        <?php if (!empty($service['closure_notes'])): ?>
                                            <p class="mb-0 small text-muted">
                                                <strong>Notas:</strong> <?= esc($service['closure_notes']) ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <span class="badge bg-success p-3" style="font-size: 1rem;">
                                            <i class="fas fa-check-circle me-1"></i>
                                            Informes Disponibles para el Cliente
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Tabla de trabajadores -->
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <?php if (empty($workers)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-users fa-4x text-muted mb-3"></i>
                                    <h5>No hay trabajadores cargados</h5>
                                    <p class="text-muted">Carga un archivo CSV para comenzar</p>
                                    <a href="<?= base_url('workers/upload/' . $service['id']) ?>" class="btn btn-success">
                                        <i class="fas fa-upload me-2"></i>Cargar Trabajadores
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table id="workersTable" class="table table-hover table-striped">
                                        <thead>
                                            <tr>
                                                <th>Documento</th>
                                                <th>Nombre</th>
                                                <th>Cargo</th>
                                                <th>Área</th>
                                                <th>Email</th>
                                                <th>Tipo</th>
                                                <th>Modalidad</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                            <tr class="filters">
                                                <th><input type="text" placeholder="Buscar documento"></th>
                                                <th><input type="text" placeholder="Buscar nombre"></th>
                                                <th><input type="text" placeholder="Buscar cargo"></th>
                                                <th><input type="text" placeholder="Buscar área"></th>
                                                <th><input type="text" placeholder="Buscar email"></th>
                                                <th><input type="text" placeholder="Tipo"></th>
                                                <th><input type="text" placeholder="Modalidad"></th>
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
                                                    <td><small><?= esc($worker['email']) ?></small></td>
                                                    <td>
                                                        <span class="badge bg-<?= $worker['intralaboral_type'] === 'A' ? 'primary' : 'info' ?>">
                                                            Forma <?= $worker['intralaboral_type'] ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-<?= $worker['application_mode'] === 'virtual' ? 'success' : 'warning' ?>">
                                                            <?= ucfirst($worker['application_mode']) ?>
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
                                                        <div class="btn-group btn-group-sm">
                                                            <?php if ($worker['status'] === 'completado'): ?>
                                                                <a href="<?= base_url('workers/results/' . $worker['id']) ?>"
                                                                   class="btn btn-outline-info"
                                                                   target="_blank"
                                                                   title="Ver resultados individuales">
                                                                    <i class="fas fa-chart-bar"></i>
                                                                </a>
                                                            <?php endif; ?>
                                                            <?php if (session()->get('role_name') !== 'director_comercial'): ?>
                                                                <?php if ($worker['application_mode'] === 'virtual'): ?>
                                                                    <button class="btn btn-outline-primary send-email-btn"
                                                                            data-worker-id="<?= $worker['id'] ?>"
                                                                            data-worker-name="<?= esc($worker['name']) ?>"
                                                                            data-worker-email="<?= esc($worker['email']) ?>"
                                                                            title="<?= $worker['email_sent'] ? 'Reenviar' : 'Enviar' ?> enlace a <?= esc($worker['email']) ?>">
                                                                        <i class="fas fa-envelope"></i>
                                                                    </button>
                                                                <?php endif; ?>
                                                                <button class="btn btn-outline-success view-link-btn"
                                                                        data-worker-token="<?= $worker['token'] ?>"
                                                                        data-worker-name="<?= esc($worker['name']) ?>"
                                                                        title="Copiar enlace de evaluación">
                                                                    <i class="fas fa-link"></i>
                                                                </button>
                                                                <button class="btn btn-outline-warning edit-worker-btn"
                                                                        data-worker-id="<?= $worker['id'] ?>"
                                                                        title="Editar trabajador">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <button class="btn btn-outline-danger delete-worker-btn"
                                                                        data-worker-id="<?= $worker['id'] ?>"
                                                                        data-worker-name="<?= esc($worker['name']) ?>"
                                                                        title="Eliminar trabajador">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                                <?php if (!in_array($worker['status'], ['completado', 'no_participo'])): ?>
                                                                    <button class="btn btn-outline-dark mark-no-participo-btn"
                                                                            data-worker-id="<?= $worker['id'] ?>"
                                                                            data-worker-name="<?= esc($worker['name']) ?>"
                                                                            title="Marcar como No Participó">
                                                                        <i class="fas fa-user-slash"></i>
                                                                    </button>
                                                                <?php endif; ?>
                                                            <?php else: ?>
                                                                <span class="badge bg-secondary">Solo lectura</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Botones de acción masiva -->
                                <div class="mt-3 d-flex justify-content-between">
                                    <?php if (session()->get('role_name') === 'director_comercial'): ?>
                                        <a href="<?= base_url('commercial') ?>" class="btn btn-secondary">
                                            <i class="fas fa-arrow-left me-2"></i>Volver al Módulo Comercial
                                        </a>
                                        <div class="alert alert-info mb-0">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Solo consulta. La gestión de trabajadores es responsabilidad del consultor asignado.
                                        </div>
                                    <?php else: ?>
                                        <?php
                                        // Contar trabajadores pendientes o en_progreso para el botón masivo
                                        $pendientesYEnProgreso = count(array_filter($workers, fn($w) => in_array($w['status'], ['pendiente', 'en_progreso'])));
                                        ?>
                                        <a href="<?= base_url('battery-services/' . $service['id']) ?>" class="btn btn-secondary">
                                            <i class="fas fa-arrow-left me-2"></i>Volver al Servicio
                                        </a>
                                        <div>
                                            <?php
                                            $completadosSinResultados = count(array_filter($workers, fn($w) => $w['status'] === 'completado'));
                                            ?>
                                            <?php if ($completadosSinResultados > 0): ?>
                                                <button class="btn btn-info me-2" id="calculateAllResultsBtn"
                                                        data-service-id="<?= $service['id'] ?>"
                                                        data-count="<?= $completadosSinResultados ?>">
                                                    <i class="fas fa-calculator me-2"></i>Calcular Resultados (<?= $completadosSinResultados ?>)
                                                </button>
                                            <?php endif; ?>
                                            <?php if ($pendientesYEnProgreso > 0): ?>
                                                <button class="btn btn-dark me-2" id="markAllNoParticipoBtn"
                                                        data-service-id="<?= $service['id'] ?>"
                                                        data-count="<?= $pendientesYEnProgreso ?>">
                                                    <i class="fas fa-users-slash me-2"></i>Marcar Todos No Participó (<?= $pendientesYEnProgreso ?>)
                                                </button>
                                            <?php endif; ?>
                                            <a href="<?= base_url('workers/create/' . $service['id']) ?>" class="btn btn-success me-2">
                                                <i class="fas fa-user-plus me-2"></i>Añadir Trabajador
                                            </a>
                                            <button class="btn btn-primary me-2" id="sendBulkEmailsBtn" data-service-id="<?= $service['id'] ?>">
                                                <i class="fas fa-paper-plane me-2"></i>Enviar Emails Masivo
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para editar trabajador -->
    <div class="modal fade" id="editWorkerModal" tabindex="-1" aria-labelledby="editWorkerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title" id="editWorkerModalLabel">
                        <i class="fas fa-edit me-2"></i>Editar Trabajador
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editWorkerForm">
                    <div class="modal-body">
                        <input type="hidden" id="edit_worker_id" name="worker_id">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Documento</label>
                                <input type="text" class="form-control" id="edit_document" name="document" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombre Completo</label>
                                <input type="text" class="form-control" id="edit_name" name="name" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" id="edit_email" name="email" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Cargo</label>
                                <input type="text" class="form-control" id="edit_position" name="position" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Área</label>
                                <input type="text" class="form-control" id="edit_area" name="area">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tipo de Formulario</label>
                                <select class="form-select" id="edit_intralaboral_type" name="intralaboral_type" required>
                                    <option value="A">Forma A (Profesionales/Jefes)</option>
                                    <option value="B">Forma B (Operarios/Auxiliares)</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Modalidad</label>
                                <select class="form-select" id="edit_application_mode" name="application_mode" required>
                                    <option value="virtual">Virtual</option>
                                    <option value="presencial">Presencial</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Estado</label>
                                <select class="form-select" id="edit_status" name="status" required>
                                    <option value="pendiente">Pendiente</option>
                                    <option value="en_progreso">En Progreso</option>
                                    <option value="completado">Completado</option>
                                    <option value="no_participo">No Participó</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save me-2"></i>Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <!-- DataTables Buttons para exportar Excel -->
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script>
        // Initialize DataTable with column filters and Excel export
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
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                     '<"row"<"col-sm-12"B>>' +
                     'rtip',
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel me-2"></i>Descargar Excel',
                        className: 'btn btn-success mb-3',
                        title: 'Trabajadores - <?= esc($service['service_name']) ?>',
                        filename: 'Trabajadores_<?= preg_replace('/[^a-zA-Z0-9]/', '_', $service['service_name']) ?>_<?= date('Ymd') ?>',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7] // Excluir columna Acciones
                        }
                    }
                ]
            });

            // Agregar filtros en cada columna
            $('#workersTable thead .filters th').each(function(i) {
                if (i < 8) { // Solo columnas 0-7 (no Acciones)
                    $('input', this).on('keyup change', function() {
                        if (table.column(i).search() !== this.value) {
                            table.column(i).search(this.value).draw();
                        }
                    });
                }
            });
        });

        // Send individual email
        document.querySelectorAll('.send-email-btn').forEach(button => {
            button.addEventListener('click', async function() {
                const workerId = this.dataset.workerId;
                const workerName = this.dataset.workerName;
                const workerEmail = this.dataset.workerEmail;
                const btn = this;

                if (!confirm(`¿Enviar enlace de evaluación?\n\nTrabajador: ${workerName}\nEmail: ${workerEmail}`)) {
                    return;
                }

                // Disable button and show loading
                btn.disabled = true;
                const originalHTML = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                try {
                    const response = await fetch(`<?= base_url('workers/send-email/') ?>${workerId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const result = await response.json();

                    if (result.success) {
                        // Mostrar check temporal pero mantener botón activo para reenvíos
                        btn.classList.remove('btn-outline-primary');
                        btn.classList.add('btn-success');
                        btn.innerHTML = '<i class="fas fa-check"></i>';
                        alert('✓ ' + result.message);

                        // Restaurar botón después de 2 segundos
                        setTimeout(() => {
                            btn.classList.remove('btn-success');
                            btn.classList.add('btn-outline-primary');
                            btn.innerHTML = originalHTML;
                            btn.disabled = false;
                        }, 2000);
                    } else {
                        btn.disabled = false;
                        btn.innerHTML = originalHTML;
                        alert('✗ Error: ' + result.message);
                    }
                } catch (error) {
                    btn.disabled = false;
                    btn.innerHTML = originalHTML;
                    alert('✗ Error de conexión: ' + error.message);
                }
            });
        });

        // View/Copy assessment link
        document.querySelectorAll('.view-link-btn').forEach(button => {
            button.addEventListener('click', async function() {
                const workerToken = this.dataset.workerToken;
                const workerName = this.dataset.workerName;
                const assessmentLink = `<?= base_url('assessment/') ?>${workerToken}`;

                // Copiar al portapapeles
                try {
                    await navigator.clipboard.writeText(assessmentLink);

                    // Mostrar link y confirmar que se copió
                    alert(`✓ Enlace copiado al portapapeles\n\nTrabajador: ${workerName}\n\nEnlace:\n${assessmentLink}`);

                    // Feedback visual
                    const originalHTML = this.innerHTML;
                    this.classList.remove('btn-outline-success');
                    this.classList.add('btn-success');
                    this.innerHTML = '<i class="fas fa-check"></i>';

                    setTimeout(() => {
                        this.classList.remove('btn-success');
                        this.classList.add('btn-outline-success');
                        this.innerHTML = originalHTML;
                    }, 1500);
                } catch (error) {
                    // Fallback: mostrar en alerta si no se puede copiar
                    alert(`Enlace de evaluación para ${workerName}:\n\n${assessmentLink}\n\n(No se pudo copiar automáticamente)`);
                }
            });
        });

        // Edit worker button
        const workersData = <?= json_encode($workers) ?>;
        const editModal = new bootstrap.Modal(document.getElementById('editWorkerModal'));

        document.querySelectorAll('.edit-worker-btn').forEach(button => {
            button.addEventListener('click', function() {
                const workerId = this.dataset.workerId;
                const worker = workersData.find(w => w.id == workerId);

                if (worker) {
                    // Llenar el formulario con los datos actuales
                    document.getElementById('edit_worker_id').value = worker.id;
                    document.getElementById('edit_document').value = worker.document || '';
                    document.getElementById('edit_name').value = worker.name || '';
                    document.getElementById('edit_email').value = worker.email || '';
                    document.getElementById('edit_position').value = worker.position || '';
                    document.getElementById('edit_area').value = worker.area || '';
                    document.getElementById('edit_intralaboral_type').value = worker.intralaboral_type || 'A';
                    document.getElementById('edit_application_mode').value = worker.application_mode || 'virtual';
                    document.getElementById('edit_status').value = worker.status || 'pendiente';

                    // Mostrar el modal
                    editModal.show();
                }
            });
        });

        // Save worker changes
        document.getElementById('editWorkerForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const workerId = formData.get('worker_id');
            const submitBtn = this.querySelector('button[type="submit"]');

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Guardando...';

            try {
                const response = await fetch(`<?= base_url('workers/update/') ?>${workerId}`, {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    alert('✓ Trabajador actualizado exitosamente');
                    editModal.hide();
                    location.reload(); // Recargar para mostrar cambios
                } else {
                    alert('✗ Error: ' + result.message);
                }
            } catch (error) {
                alert('✗ Error de conexión: ' + error.message);
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Guardar Cambios';
            }
        });

        // Delete worker button
        document.querySelectorAll('.delete-worker-btn').forEach(button => {
            button.addEventListener('click', async function() {
                const workerId = this.dataset.workerId;
                const workerName = this.dataset.workerName;

                if (!confirm(`⚠️ ¿ELIMINAR TRABAJADOR?\n\nTrabajador: ${workerName}\n\nEsta acción eliminará:\n- Datos del trabajador\n- Datos demográficos\n- Respuestas de evaluaciones\n\n¿Estás seguro?`)) {
                    return;
                }

                // Confirmación adicional
                if (!confirm(`CONFIRMACIÓN FINAL\n\n¿Realmente deseas eliminar a ${workerName}?\n\nEsta acción NO se puede deshacer.`)) {
                    return;
                }

                const btn = this;
                const originalHTML = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                try {
                    const response = await fetch(`<?= base_url('workers/delete/') ?>${workerId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const result = await response.json();

                    if (result.success) {
                        alert('✓ ' + result.message);
                        location.reload(); // Recargar para actualizar la tabla
                    } else {
                        alert('✗ Error: ' + result.message);
                        btn.disabled = false;
                        btn.innerHTML = originalHTML;
                    }
                } catch (error) {
                    alert('✗ Error de conexión: ' + error.message);
                    btn.disabled = false;
                    btn.innerHTML = originalHTML;
                }
            });
        });

        // Send bulk emails
        document.getElementById('sendBulkEmailsBtn')?.addEventListener('click', async function() {
            const serviceId = this.dataset.serviceId;
            const btn = this;

            if (!confirm('¿Enviar enlaces de evaluación a los trabajadores PENDIENTES?\n\nSolo se enviará a trabajadores con estado "Pendiente" o "En Progreso".\nLos trabajadores "Completados" y "No Participó" serán excluidos.')) {
                return;
            }

            // Disable button and show loading
            btn.disabled = true;
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Enviando...';

            try {
                const response = await fetch(`<?= base_url('workers/send-bulk-emails/') ?>${serviceId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const result = await response.json();

                if (result.success) {
                    let message = `✓ Envío masivo completado!\n\nTotal procesados: ${result.total}\nExitosos: ${result.sent}\nFallidos: ${result.failed}`;

                    if (result.errors && result.errors.length > 0) {
                        message += '\n\nEmails fallidos:\n' + result.errors.join('\n');
                    }

                    alert(message);
                } else {
                    alert('✗ Error: ' + result.message);
                }
            } catch (error) {
                alert('✗ Error de conexión: ' + error.message);
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalHTML;
            }
        });

        // Mark as "No Participó" with double confirmation (individual)
        document.querySelectorAll('.mark-no-participo-btn').forEach(button => {
            button.addEventListener('click', async function() {
                const workerId = this.dataset.workerId;
                const workerName = this.dataset.workerName;

                // Primera confirmación
                if (!confirm(`⚠️ MARCAR COMO "NO PARTICIPÓ"\n\nTrabajador: ${workerName}\n\nEsta acción:\n• Excluirá al trabajador de TODAS las estadísticas\n• No aparecerá en ningún informe\n• No se puede deshacer fácilmente\n\n¿Desea continuar?`)) {
                    return;
                }

                // Segunda confirmación (doble confirmación requerida)
                if (!confirm(`CONFIRMACIÓN FINAL\n\n¿Está SEGURO de que desea marcar a "${workerName}" como NO PARTICIPÓ?\n\nEl trabajador será excluido permanentemente de todos los cálculos e informes.`)) {
                    return;
                }

                const btn = this;
                const originalHTML = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                try {
                    const response = await fetch(`<?= base_url('workers/mark-no-participo/') ?>${workerId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const result = await response.json();

                    if (result.success) {
                        alert('✓ ' + result.message);
                        location.reload(); // Recargar para actualizar la tabla
                    } else {
                        alert('✗ Error: ' + result.message);
                        btn.disabled = false;
                        btn.innerHTML = originalHTML;
                    }
                } catch (error) {
                    alert('✗ Error de conexión: ' + error.message);
                    btn.disabled = false;
                    btn.innerHTML = originalHTML;
                }
            });
        });

        // Calculate ALL results for completed workers (MASIVO)
        document.getElementById('calculateAllResultsBtn')?.addEventListener('click', async function() {
            const serviceId = this.dataset.serviceId;
            const count = this.dataset.count;
            const btn = this;

            if (!confirm(`¿Calcular resultados para ${count} trabajador(es) completados?\n\nEsto procesará los resultados de todos los cuestionarios (Intralaboral, Extralaboral y Estrés) para cada trabajador.\n\nEl proceso puede tomar algunos segundos.`)) {
                return;
            }

            const originalHTML = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Calculando...';

            try {
                const response = await fetch(`<?= base_url('workers/calculate-all-results/') ?>${serviceId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const result = await response.json();

                if (result.success) {
                    let message = `✓ Cálculo masivo completado!\n\nTotal procesados: ${result.total}\nExitosos: ${result.calculated}\nFallidos: ${result.failed}`;

                    if (result.errors && result.errors.length > 0) {
                        message += '\n\nErrores:\n' + result.errors.slice(0, 5).join('\n');
                        if (result.errors.length > 5) {
                            message += `\n... y ${result.errors.length - 5} más`;
                        }
                    }

                    alert(message);
                    location.reload();
                } else {
                    alert('✗ Error: ' + result.message);
                }
            } catch (error) {
                alert('✗ Error de conexión: ' + error.message);
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalHTML;
            }
        });

        // Mark ALL pending/in_progress as "No Participó" (MASIVO) with double confirmation
        document.getElementById('markAllNoParticipoBtn')?.addEventListener('click', async function() {
            const serviceId = this.dataset.serviceId;
            const count = this.dataset.count;
            const btn = this;

            // Primera confirmación
            if (!confirm(`⚠️ ACCIÓN MASIVA: MARCAR TODOS COMO "NO PARTICIPÓ"\n\n${count} trabajador(es) serán marcados como "No Participó":\n• Todos los pendientes\n• Todos los que están en progreso\n\nEsta acción:\n• Excluirá a TODOS estos trabajadores de las estadísticas\n• No aparecerán en ningún informe\n• NO se puede deshacer fácilmente\n\n¿Desea continuar?`)) {
                return;
            }

            // Segunda confirmación (doble confirmación requerida)
            if (!confirm(`⚠️ CONFIRMACIÓN FINAL ⚠️\n\n¿Está ABSOLUTAMENTE SEGURO?\n\n${count} trabajador(es) serán marcados como "NO PARTICIPÓ" y excluidos permanentemente de todos los cálculos e informes.\n\nEscriba "SI" en el siguiente prompt para confirmar.`)) {
                return;
            }

            // Tercera confirmación: prompt para escribir "SI"
            const confirmText = prompt(`Para confirmar esta acción masiva, escriba "SI" (en mayúsculas):`);
            if (confirmText !== 'SI') {
                alert('Acción cancelada. No se escribió "SI" correctamente.');
                return;
            }

            const originalHTML = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Procesando...';

            try {
                const response = await fetch(`<?= base_url('workers/mark-all-no-participo/') ?>${serviceId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const result = await response.json();

                if (result.success) {
                    alert('✓ ' + result.message);
                    location.reload(); // Recargar para actualizar la tabla
                } else {
                    alert('✗ Error: ' + result.message);
                    btn.disabled = false;
                    btn.innerHTML = originalHTML;
                }
            } catch (error) {
                alert('✗ Error de conexión: ' + error.message);
                btn.disabled = false;
                btn.innerHTML = originalHTML;
            }
        });
    </script>
</body>
</html>
