<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Cierre de Servicio - PsyRisk</title>
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
            text-align: center;
        }
        .stat-card .value {
            font-size: 2.5rem;
            font-weight: bold;
        }
        .stat-card .label {
            font-size: 0.85rem;
            text-transform: uppercase;
        }
        .worker-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            border-left: 4px solid #667eea;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
        }
        .worker-card.completado {
            border-left-color: #28a745;
            opacity: 0.7;
        }
        .worker-card.pending {
            border-left-color: #ffc107;
        }
        .status-badge {
            padding: 5px 12px;
            border-radius: 5px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .alert-warning-custom {
            background-color: #fff3cd;
            border-left: 5px solid #ffc107;
            padding: 20px;
            border-radius: 10px;
        }
        .progress-ring {
            width: 150px;
            height: 150px;
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
                    <small class="text-white-50">Consultor</small>
                </div>

                <nav class="nav flex-column mt-4">
                    <a class="nav-link" href="<?= base_url('dashboard') ?>">
                        <i class="fas fa-home me-2"></i> Dashboard
                    </a>
                    <a class="nav-link active" href="<?= base_url('workers/service/' . $service['id']) ?>">
                        <i class="fas fa-users me-2"></i> Trabajadores
                    </a>
                    <hr class="text-white-50 mx-3">
                    <a class="nav-link" href="<?= base_url('logout') ?>">
                        <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h3 class="mb-1">
                            <i class="fas fa-check-circle text-primary me-2"></i>
                            Gestionar Cierre de Servicio
                        </h3>
                        <p class="text-muted mb-0"><?= esc($service['service_name']) ?></p>
                    </div>
                    <a href="<?= base_url('workers/service/' . $service['id']) ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Volver
                    </a>
                </div>

                <!-- Estadísticas de Participación -->
                <div class="row mb-4">
                    <div class="col-md-2">
                        <div class="card stat-card bg-success text-white">
                            <div class="value"><?= $stats['completados'] ?></div>
                            <div class="label">✅ Completados</div>
                            <small><?= number_format($stats['percent_completado'], 1) ?>%</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card stat-card bg-warning text-dark">
                            <div class="value"><?= $stats['en_proceso'] ?></div>
                            <div class="label">⏳ En Proceso</div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card stat-card bg-info text-white">
                            <div class="value"><?= $stats['invitados'] ?></div>
                            <div class="label">📧 Invitados</div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card stat-card bg-secondary text-white">
                            <div class="value"><?= $stats['pendientes'] ?></div>
                            <div class="label">⚪ Pendientes</div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card stat-card bg-danger text-white">
                            <div class="value"><?= $stats['abandonados'] ?></div>
                            <div class="label">❌ Abandonados</div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card stat-card bg-dark text-white">
                            <div class="value"><?= $stats['no_participo'] ?></div>
                            <div class="label">🚫 No Participaron</div>
                        </div>
                    </div>
                </div>

                <!-- Validación de Cierre -->
                <?php
                $canClose = true;
                $pendingCount = $stats['en_proceso'] + $stats['invitados'] + $stats['pendientes'];
                ?>

                <?php if ($stats['percent_completado'] < $minPercent): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Participación insuficiente:</strong>
                        El porcentaje de completados (<?= number_format($stats['percent_completado'], 1) ?>%)
                        es menor al mínimo requerido (<?= $minPercent ?>%).
                    </div>
                    <?php $canClose = false; ?>
                <?php endif; ?>

                <?php if ($pendingCount > 0): ?>
                    <div class="alert-warning-custom mb-4">
                        <h5 class="mb-2">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            Acciones Requeridas
                        </h5>
                        <p class="mb-0">
                            Tienes <strong><?= $pendingCount ?> trabajadores</strong> sin gestionar.
                            Debes asignar un estado a cada uno antes de poder cerrar el servicio.
                        </p>
                    </div>
                    <?php $canClose = false; ?>
                <?php endif; ?>

                <!-- Formulario de Gestión -->
                <form action="<?= base_url('workers/update-statuses/' . $service['id']) ?>" method="POST" id="closeForm">

                    <!-- Trabajadores Pendientes de Gestión -->
                    <?php if (!empty($pendingManagement)): ?>
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0">
                                    <i class="fas fa-tasks text-primary me-2"></i>
                                    Trabajadores por Gestionar (<?= count($pendingManagement) ?>)
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php foreach ($pendingManagement as $worker): ?>
                                    <div class="worker-card pending">
                                        <div class="row align-items-center">
                                            <div class="col-md-4">
                                                <h6 class="fw-bold mb-1">
                                                    <i class="fas fa-user me-2 text-primary"></i>
                                                    <?= esc($worker['name']) ?>
                                                </h6>
                                                <p class="mb-0 small text-muted">
                                                    <strong>Doc:</strong> <?= esc($worker['document']) ?><br>
                                                    <strong>Cargo:</strong> <?= esc($worker['position']) ?>
                                                </p>
                                                <span class="status-badge bg-warning">
                                                    Estado Actual: <?= ucfirst($worker['status']) ?>
                                                </span>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-bold">Nuevo Estado:</label>
                                                        <select class="form-select form-select-sm"
                                                                name="worker_updates[<?= $worker['id'] ?>][status]"
                                                                required
                                                                onchange="toggleReasonField(<?= $worker['id'] ?>, this.value)">
                                                            <option value="">Seleccionar...</option>
                                                            <option value="en_proceso">Mantener en proceso</option>
                                                            <option value="no_participo">No Participó</option>
                                                            <option value="abandonado">Abandonó</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4" id="reason_<?= $worker['id'] ?>" style="display:none;">
                                                        <label class="form-label small fw-bold">Motivo:</label>
                                                        <select class="form-select form-select-sm"
                                                                name="worker_updates[<?= $worker['id'] ?>][reason]">
                                                            <option value="">Seleccionar...</option>
                                                            <option value="Incapacidad">Incapacidad</option>
                                                            <option value="Vacaciones">Vacaciones</option>
                                                            <option value="Licencia">Licencia</option>
                                                            <option value="Calamidad">Calamidad</option>
                                                            <option value="Desvinculado">Desvinculado</option>
                                                            <option value="Otro">Otro</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-bold">Notas (opcional):</label>
                                                        <input type="text"
                                                               class="form-control form-control-sm"
                                                               name="worker_updates[<?= $worker['id'] ?>][notes]"
                                                               placeholder="Observaciones...">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="text-end mb-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i>Guardar Cambios
                            </button>
                        </div>
                    <?php endif; ?>

                    <!-- Formulario de Cierre Final -->
                    <?php if ($canClose): ?>
                        <div class="card border-0 shadow-sm bg-light">
                            <div class="card-body p-4">
                                <h5 class="mb-3">
                                    <i class="fas fa-lock text-success me-2"></i>
                                    Cierre de Servicio
                                </h5>

                                <div class="alert alert-info">
                                    <h6><i class="fas fa-info-circle me-2"></i>Resumen Final:</h6>
                                    <ul class="mb-0">
                                        <li>✅ Trabajadores completados: <strong><?= $stats['completados'] ?> (<?= number_format($stats['percent_completado'], 1) ?>%)</strong></li>
                                        <li>🚫 No participaron: <strong><?= $stats['no_participo'] ?></strong></li>
                                        <li>❌ Abandonaron: <strong><?= $stats['abandonados'] ?></strong></li>
                                    </ul>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Notas de Cierre (opcional):</label>
                                    <textarea class="form-control"
                                              name="closure_notes"
                                              rows="3"
                                              placeholder="Observaciones generales sobre el servicio, incidencias, etc."></textarea>
                                </div>

                                <div class="alert alert-warning">
                                    <h6><i class="fas fa-exclamation-triangle me-2"></i>Al cerrar el servicio:</h6>
                                    <ul class="mb-0">
                                        <li>El cliente podrá ver los informes y resultados</li>
                                        <li>No podrás agregar más trabajadores</li>
                                        <li>Solo se calcularán los trabajadores completados</li>
                                        <li>Esta acción NO se puede deshacer</li>
                                    </ul>
                                </div>

                                <div class="text-center mt-4">
                                    <button type="button" class="btn btn-success btn-lg px-5" onclick="confirmClose()">
                                        <i class="fas fa-check-circle me-2"></i>
                                        Cerrar Servicio Definitivamente
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmación -->
    <div class="modal fade" id="confirmCloseModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Confirmar Cierre de Servicio
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">¿Estás seguro de que deseas cerrar este servicio?</p>
                    <p class="text-danger fw-bold mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Esta acción es irreversible. El cliente podrá ver los informes inmediatamente.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form action="<?= base_url('workers/close-service/' . $service['id']) ?>" method="POST" style="display:inline;">
                        <input type="hidden" name="closure_notes" id="closure_notes_hidden">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check me-2"></i>Sí, Cerrar Servicio
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleReasonField(workerId, status) {
            const reasonField = document.getElementById('reason_' + workerId);
            if (status === 'no_participo') {
                reasonField.style.display = 'block';
                reasonField.querySelector('select').required = true;
            } else {
                reasonField.style.display = 'none';
                reasonField.querySelector('select').required = false;
            }
        }

        function confirmClose() {
            // Copiar notas al campo hidden del modal
            const notes = document.querySelector('textarea[name="closure_notes"]').value;
            document.getElementById('closure_notes_hidden').value = notes;

            // Mostrar modal
            const modal = new bootstrap.Modal(document.getElementById('confirmCloseModal'));
            modal.show();
        }
    </script>
</body>
</html>
