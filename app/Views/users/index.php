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
        .navbar-custom {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .table-card {
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        .badge-role {
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
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
                    <a class="nav-link active" href="<?= base_url('users') ?>">
                        <i class="fas fa-users me-2"></i> Usuarios
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
                <!-- Top Navbar -->
                <nav class="navbar navbar-custom navbar-expand-lg p-3">
                    <div class="container-fluid">
                        <h4 class="mb-0"><i class="fas fa-users me-2"></i><?= $title ?></h4>
                        <div class="ms-auto">
                            <button class="btn btn-primary" onclick="location.href='<?= base_url('users/create') ?>'">
                                <i class="fas fa-plus me-2"></i>Nuevo Usuario
                            </button>
                        </div>
                    </div>
                </nav>

                <!-- Content -->
                <div class="p-4">
                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i><?= session()->getFlashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="card table-card border-0">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Nombre</th>
                                            <th>Email</th>
                                            <th>Rol</th>
                                            <th>Empresa</th>
                                            <th>Estado</th>
                                            <th>Fecha Creación</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($users)): ?>
                                            <tr>
                                                <td colspan="8" class="text-center py-5">
                                                    <i class="fas fa-users fa-3x text-muted mb-3 d-block"></i>
                                                    <p class="text-muted">No hay usuarios registrados</p>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($users as $user): ?>
                                                <tr>
                                                    <td><?= $user['id'] ?></td>
                                                    <td>
                                                        <strong><?= esc($user['name']) ?></strong>
                                                    </td>
                                                    <td><?= esc($user['email']) ?></td>
                                                    <td>
                                                        <span class="badge badge-role bg-<?=
                                                            $user['role_name'] === 'superadmin' ? 'danger' :
                                                            ($user['role_name'] === 'admin' ? 'warning' :
                                                            ($user['role_name'] === 'consultor' ? 'info' :
                                                            ($user['role_name'] === 'director_comercial' ? 'success' : 'secondary')))
                                                        ?>">
                                                            <?= esc($user['role_name']) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php if (!empty($user['company_name'])): ?>
                                                            <small><i class="fas fa-building text-muted me-1"></i><?= esc($user['company_name']) ?></small>
                                                        <?php else: ?>
                                                            <small class="text-muted">-</small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-<?= $user['status'] === 'active' ? 'success' : 'secondary' ?>">
                                                            <?= $user['status'] === 'active' ? 'Activo' : 'Inactivo' ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted">
                                                            <i class="fas fa-calendar me-1"></i>
                                                            <?= date('d/m/Y', strtotime($user['created_at'])) ?>
                                                        </small>
                                                    </td>
                                                    <td class="text-center">
                                                        <a href="<?= base_url('users/edit/' . $user['id']) ?>"
                                                           class="btn btn-sm btn-outline-primary me-1"
                                                           title="Editar">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <?php if (session()->get('role_name') === 'superadmin' && $user['id'] != session()->get('id')): ?>
                                                            <button type="button"
                                                                    class="btn btn-sm btn-outline-danger"
                                                                    onclick="confirmDelete(<?= $user['id'] ?>, '<?= esc($user['name']) ?>')"
                                                                    title="Eliminar">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        <?php endif; ?>
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

    <!-- Form oculto para eliminar -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete(userId, userName) {
            if (confirm('¿Estás seguro de eliminar al usuario "' + userName + '"?\n\nEsta acción no se puede deshacer.')) {
                const form = document.getElementById('deleteForm');
                form.action = '<?= base_url('users/delete') ?>/' + userId;
                form.submit();
            }
        }
    </script>
</body>
</html>
