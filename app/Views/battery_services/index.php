<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - PsyRisk</title>
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
                        <h4 class="mb-0"><?= $title ?></h4>
                        <button class="btn btn-success" onclick="location.href='<?= base_url('battery-services/create') ?>'">
                            <i class="fas fa-plus me-2"></i>Nuevo Servicio
                        </button>
                    </div>
                </nav>

                <div class="p-4">
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

                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nombre del Servicio</th>
                                            <th>Empresa</th>
                                            <th>Fecha</th>
                                            <th>Vencimiento</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($services)): ?>
                                            <tr>
                                                <td colspan="6" class="text-center py-4">
                                                    <i class="fas fa-clipboard-list fa-3x text-muted mb-3 d-block"></i>
                                                    <p class="text-muted">No hay servicios de batería registrados</p>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($services as $service): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?= esc($service['service_name']) ?></strong>
                                                    </td>
                                                    <td><?= esc($service['company_name']) ?></td>
                                                    <td><?= date('d/m/Y', strtotime($service['service_date'])) ?></td>
                                                    <td>
                                                        <?= date('d/m/Y', strtotime($service['link_expiration_date'])) ?>
                                                        <?php
                                                        $daysLeft = floor((strtotime($service['link_expiration_date']) - time()) / 86400);
                                                        if ($daysLeft < 0): ?>
                                                            <span class="badge bg-danger ms-2">Expirado</span>
                                                        <?php elseif ($daysLeft <= 3): ?>
                                                            <span class="badge bg-warning ms-2"><?= $daysLeft ?> días</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-<?= $service['status'] === 'finalizado' ? 'success' : ($service['status'] === 'en_curso' ? 'warning' : 'secondary') ?>">
                                                            <?= ucfirst($service['status']) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="<?= base_url('battery-services/' . $service['id']) ?>"
                                                               class="btn btn-outline-info"
                                                               title="Ver">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <a href="<?= base_url('battery-services/edit/' . $service['id']) ?>"
                                                               class="btn btn-outline-primary"
                                                               title="Editar">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <?php if (session()->get('role_name') === 'superadmin'): ?>
                                                            <button onclick="confirmDelete(<?= $service['id'] ?>, '<?= esc($service['service_name'], 'js') ?>', '<?= esc($service['company_name'], 'js') ?>')"
                                                                    class="btn btn-outline-danger"
                                                                    title="Eliminar">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form id="deleteForm" method="POST" style="display:none;">
        <?= csrf_field() ?>
    </form>

    <!-- Modal de Primera Confirmación -->
    <div class="modal fade" id="deleteConfirmModal1" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning mb-3">
                        <i class="fas fa-info-circle me-2"></i><strong>Atención:</strong> Está a punto de eliminar un servicio completo.
                    </div>
                    <p><strong>Servicio:</strong> <span id="serviceName"></span></p>
                    <p><strong>Empresa:</strong> <span id="companyName"></span></p>
                    <div id="serviceStats" class="mt-3">
                        <p class="mb-1"><i class="fas fa-users me-2"></i>Trabajadores: <span id="workerCount" class="badge bg-secondary">Cargando...</span></p>
                        <p class="mb-1"><i class="fas fa-file-alt me-2"></i>Respuestas: <span id="responseCount" class="badge bg-secondary">Cargando...</span></p>
                        <p class="mb-1"><i class="fas fa-chart-bar me-2"></i>Resultados: <span id="resultCount" class="badge bg-secondary">Cargando...</span></p>
                    </div>
                    <hr>
                    <p class="text-muted small mb-0"><i class="fas fa-exclamation-circle me-1"></i>Todos estos registros serán eliminados permanentemente.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i>Cancelar</button>
                    <button type="button" class="btn btn-warning" id="btnContinueDelete"><i class="fas fa-arrow-right me-1"></i>Continuar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Segunda Confirmación (Final) -->
    <div class="modal fade" id="deleteConfirmModal2" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-danger">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-skull-crossbones me-2"></i>CONFIRMACIÓN FINAL</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="mb-4"><i class="fas fa-trash-alt fa-4x text-danger"></i></div>
                    <h4 class="text-danger mb-3">¿ESTÁ COMPLETAMENTE SEGURO?</h4>
                    <p class="mb-3">Esta acción <strong>NO SE PUEDE DESHACER</strong>.<br>Se eliminarán permanentemente:</p>
                    <ul class="list-unstyled text-start bg-light p-3 rounded">
                        <li><i class="fas fa-check text-danger me-2"></i>El servicio y su configuración</li>
                        <li><i class="fas fa-check text-danger me-2"></i>Todos los trabajadores asociados</li>
                        <li><i class="fas fa-check text-danger me-2"></i>Todas las respuestas de evaluación</li>
                        <li><i class="fas fa-check text-danger me-2"></i>Todos los resultados calculados</li>
                        <li><i class="fas fa-check text-danger me-2"></i>Los datos demográficos</li>
                    </ul>
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" id="confirmCheckbox">
                        <label class="form-check-label text-danger fw-bold" for="confirmCheckbox">Entiendo que esta acción es irreversible</label>
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary btn-lg" data-bs-dismiss="modal"><i class="fas fa-arrow-left me-1"></i>Cancelar</button>
                    <button type="button" class="btn btn-danger btn-lg" id="btnFinalDelete" disabled><i class="fas fa-trash-alt me-1"></i>ELIMINAR DEFINITIVAMENTE</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentServiceId = null;
        const modal1 = new bootstrap.Modal(document.getElementById('deleteConfirmModal1'));
        const modal2 = new bootstrap.Modal(document.getElementById('deleteConfirmModal2'));

        function confirmDelete(id, serviceName, companyName) {
            currentServiceId = id;
            document.getElementById('serviceName').textContent = serviceName;
            document.getElementById('companyName').textContent = companyName;
            document.getElementById('workerCount').textContent = 'Cargando...';
            document.getElementById('responseCount').textContent = 'Cargando...';
            document.getElementById('resultCount').textContent = 'Cargando...';

            fetch('<?= base_url('battery-services/delete-info/') ?>' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('workerCount').textContent = data.workers;
                        document.getElementById('workerCount').className = 'badge ' + (data.workers > 0 ? 'bg-danger' : 'bg-success');
                        document.getElementById('responseCount').textContent = data.responses;
                        document.getElementById('responseCount').className = 'badge ' + (data.responses > 0 ? 'bg-danger' : 'bg-success');
                        document.getElementById('resultCount').textContent = data.results;
                        document.getElementById('resultCount').className = 'badge ' + (data.results > 0 ? 'bg-danger' : 'bg-success');
                    }
                }).catch(err => console.error('Error:', err));
            modal1.show();
        }

        document.getElementById('btnContinueDelete').addEventListener('click', function() {
            modal1.hide();
            document.getElementById('confirmCheckbox').checked = false;
            document.getElementById('btnFinalDelete').disabled = true;
            setTimeout(() => modal2.show(), 300);
        });

        document.getElementById('confirmCheckbox').addEventListener('change', function() {
            document.getElementById('btnFinalDelete').disabled = !this.checked;
        });

        document.getElementById('btnFinalDelete').addEventListener('click', function() {
            if (currentServiceId && document.getElementById('confirmCheckbox').checked) {
                const form = document.getElementById('deleteForm');
                form.action = '<?= base_url('battery-services/delete/') ?>' + currentServiceId;
                form.submit();
            }
        });
    </script>
</body>
</html>
