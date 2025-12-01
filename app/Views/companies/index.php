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
                    <a class="nav-link" href="<?= base_url('dashboard') ?>" target="_blank">
                        <i class="fas fa-home me-2"></i> Dashboard
                    </a>
                    <?php if (in_array(session()->get('role_name'), ['superadmin'])): ?>
                    <a class="nav-link" href="<?= base_url('users') ?>" target="_blank">
                        <i class="fas fa-users me-2"></i> Usuarios
                    </a>
                    <?php endif; ?>
                    <a class="nav-link active" href="<?= base_url('companies') ?>">
                        <i class="fas fa-building me-2"></i> Empresas
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
                        <h4 class="mb-0"><?= $title ?></h4>
                        <a href="<?= base_url('companies/create') ?>" class="btn btn-success" target="_blank">
                            <i class="fas fa-plus me-2"></i>Nueva Empresa
                        </a>
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
                                            <th>NIT</th>
                                            <th>Nombre</th>
                                            <th>Tipo</th>
                                            <th>Contacto</th>
                                            <th>Teléfono</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($companies)): ?>
                                            <tr>
                                                <td colspan="7" class="text-center py-4">
                                                    <i class="fas fa-building fa-3x text-muted mb-3 d-block"></i>
                                                    <p class="text-muted">No hay empresas registradas</p>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($companies as $company): ?>
                                                <tr>
                                                    <td><?= esc($company['nit']) ?></td>
                                                    <td>
                                                        <strong><?= esc($company['name']) ?></strong>
                                                        <?php if ($company['parent_company_id']): ?>
                                                            <br><small class="text-muted"><i class="fas fa-link"></i> Gestionada</small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-<?= $company['type'] === 'gestor_multicompania' ? 'primary' : 'info' ?>">
                                                            <?= $company['type'] === 'gestor_multicompania' ? 'Gestor' : 'Individual' ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?= esc($company['contact_name'] ?? '-') ?><br>
                                                        <small class="text-muted"><?= esc($company['contact_email'] ?? '-') ?></small>
                                                    </td>
                                                    <td><?= esc($company['phone'] ?? '-') ?></td>
                                                    <td>
                                                        <span class="badge bg-<?= $company['status'] === 'active' ? 'success' : 'secondary' ?>">
                                                            <?= ucfirst($company['status']) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="<?= base_url('companies/edit/' . $company['id']) ?>"
                                                               class="btn btn-outline-primary"
                                                               title="Editar"
                                                               target="_blank">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <?php if (session()->get('role_name') === 'superadmin'): ?>
                                                            <button onclick="confirmDelete(<?= $company['id'] ?>)"
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

    <!-- Form para eliminar (oculto) -->
    <form id="deleteForm" method="POST" style="display:none;">
        <?= csrf_field() ?>
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete(id) {
            if (confirm('¿Está seguro de eliminar esta empresa? Esta acción no se puede deshacer.')) {
                const form = document.getElementById('deleteForm');
                form.action = '<?= base_url('companies/delete/') ?>' + id;
                form.submit();
            }
        }
    </script>
</body>
</html>
