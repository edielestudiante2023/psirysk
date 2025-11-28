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
        .form-card {
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
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
                        <h4 class="mb-0"><i class="fas fa-user-edit me-2"></i><?= $title ?></h4>
                    </div>
                </nav>

                <!-- Content -->
                <div class="p-4">
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <div class="card form-card border-0">
                                <div class="card-body p-4">
                                    <?php if (session()->getFlashdata('errors')): ?>
                                        <div class="alert alert-danger">
                                            <ul class="mb-0">
                                                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                                    <li><?= esc($error) ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>

                                    <form action="<?= base_url('users/update/' . $user['id']) ?>" method="POST" novalidate>
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="debug" value="test">

                                        <div class="mb-3">
                                            <label for="name" class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="name" name="name"
                                                   value="<?= old('name', $user['name']) ?>" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control" id="email" name="email"
                                                   value="<?= old('email', $user['email']) ?>" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="password" class="form-label">Nueva Contraseña</label>
                                            <input type="password" class="form-control" id="password" name="password" minlength="8">
                                            <small class="text-muted">Dejar en blanco para mantener la contraseña actual. Mínimo 8 caracteres si se cambia.</small>
                                        </div>

                                        <div class="mb-3">
                                            <label for="role_id" class="form-label">Rol <span class="text-danger">*</span></label>
                                            <select class="form-select" id="role_id" name="role_id" required>
                                                <option value="">Seleccionar rol...</option>
                                                <?php foreach ($roles as $role): ?>
                                                    <option value="<?= $role['id'] ?>"
                                                            data-role-name="<?= $role['name'] ?>"
                                                            <?= old('role_id', $user['role_id']) == $role['id'] ? 'selected' : '' ?>>
                                                        <?= esc($role['name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="mb-3" id="company_field" style="display: none;">
                                            <label for="company_id" class="form-label">Empresa <span class="text-danger">*</span></label>
                                            <select class="form-select" id="company_id" name="company_id">
                                                <option value="">Seleccionar empresa...</option>
                                                <?php foreach ($companies as $company): ?>
                                                    <option value="<?= $company['id'] ?>"
                                                            data-type="<?= $company['type'] ?>"
                                                            <?= old('company_id', $user['company_id']) == $company['id'] ? 'selected' : '' ?>>
                                                        <?= esc($company['name']) ?> (<?= $company['type'] === 'gestor_multicompania' ? 'Gestor' : 'Individual' ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <small class="text-muted" id="company_hint"></small>
                                        </div>

                                        <div class="mb-3">
                                            <label for="status" class="form-label">Estado <span class="text-danger">*</span></label>
                                            <select class="form-select" id="status" name="status" required>
                                                <option value="active" <?= old('status', $user['status']) === 'active' ? 'selected' : '' ?>>Activo</option>
                                                <option value="inactive" <?= old('status', $user['status']) === 'inactive' ? 'selected' : '' ?>>Inactivo</option>
                                            </select>
                                        </div>

                                        <div class="d-flex gap-2 mt-4">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save me-2"></i>Actualizar Usuario
                                            </button>
                                            <a href="<?= base_url('users') ?>" class="btn btn-secondary">
                                                <i class="fas fa-times me-2"></i>Cancelar
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mostrar/ocultar campo de empresa según rol seleccionado
        const roleSelect = document.getElementById('role_id');
        const companyField = document.getElementById('company_field');
        const companySelect = document.getElementById('company_id');
        const companyHint = document.getElementById('company_hint');

        function updateCompanyField() {
            const selectedOption = roleSelect.options[roleSelect.selectedIndex];
            const roleName = selectedOption?.dataset?.roleName;

            if (!roleName) {
                companyField.style.display = 'none';
                companySelect.required = false;
                return;
            }

            if (roleName === 'cliente_gestor' || roleName === 'cliente_empresa') {
                companyField.style.display = 'block';
                companySelect.required = true;

                if (roleName === 'cliente_gestor') {
                    companyHint.textContent = 'Selecciona una empresa de tipo Gestor Multicliente';
                    Array.from(companySelect.options).forEach(option => {
                        if (option.value && option.dataset.type !== 'gestor_multicompania') {
                            option.style.display = 'none';
                            option.disabled = true;
                        } else {
                            option.style.display = 'block';
                            option.disabled = false;
                        }
                    });
                } else if (roleName === 'cliente_empresa') {
                    companyHint.textContent = 'Selecciona una empresa individual';
                    Array.from(companySelect.options).forEach(option => {
                        if (option.value && option.dataset.type !== 'empresa_individual') {
                            option.style.display = 'none';
                            option.disabled = true;
                        } else {
                            option.style.display = 'block';
                            option.disabled = false;
                        }
                    });
                }
            } else {
                companyField.style.display = 'none';
                companySelect.required = false;
            }
        }

        roleSelect.addEventListener('change', updateCompanyField);
        updateCompanyField();
    </script>
</body>
</html>
