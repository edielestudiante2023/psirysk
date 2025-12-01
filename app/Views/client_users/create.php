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
                    <small class="text-white-50">Consultor RPS</small>
                </div>

                <nav class="nav flex-column mt-4">
                    <a class="nav-link" href="<?= base_url('dashboard') ?>">
                        <i class="fas fa-home me-2"></i> Dashboard
                    </a>
                    <a class="nav-link" href="<?= base_url('companies') ?>">
                        <i class="fas fa-building me-2"></i> Clientes
                    </a>
                    <a class="nav-link" href="<?= base_url('battery-services') ?>">
                        <i class="fas fa-clipboard-check me-2"></i> Servicios de Batería
                    </a>
                    <a class="nav-link active" href="<?= base_url('client-users') ?>">
                        <i class="fas fa-user-tie me-2"></i> Usuarios de Cliente
                    </a>
                    <a class="nav-link" href="<?= base_url('workers') ?>">
                        <i class="fas fa-users me-2"></i> Trabajadores
                    </a>
                    <a class="nav-link" href="<?= base_url('reports') ?>">
                        <i class="fas fa-chart-bar me-2"></i> Informes
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
                        <h4 class="mb-0"><i class="fas fa-user-plus me-2"></i><?= $title ?></h4>
                    </div>
                </nav>

                <!-- Content -->
                <div class="p-4">
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <div class="card form-card border-0">
                                <div class="card-body p-4">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Importante:</strong> Este usuario podrá acceder al sistema para visualizar los informes de su empresa asignada.
                                    </div>

                                    <?php if (session()->getFlashdata('errors')): ?>
                                        <div class="alert alert-danger">
                                            <ul class="mb-0">
                                                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                                    <li><?= esc($error) ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>

                                    <form action="<?= base_url('client-users/store') ?>" method="POST">
                                        <?= csrf_field() ?>

                                        <div class="mb-3">
                                            <label for="name" class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="name" name="name"
                                                   value="<?= old('name') ?>" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control" id="email" name="email"
                                                   value="<?= old('email') ?>" required>
                                            <small class="text-muted">Este será el usuario de acceso</small>
                                        </div>

                                        <div class="mb-3">
                                            <label for="password" class="form-label">Contrasena <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="password" class="form-control" id="password" name="password"
                                                       required minlength="8">
                                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                                    <i class="fas fa-eye" id="eyeIcon"></i>
                                                </button>
                                                <button class="btn btn-outline-primary" type="button" id="generatePassword" title="Generar contrasena aleatoria">
                                                    <i class="fas fa-random"></i>
                                                </button>
                                            </div>
                                            <small class="text-muted">Minimo 8 caracteres</small>
                                        </div>

                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="send_credentials_email" name="send_credentials_email" value="1" checked>
                                                <label class="form-check-label" for="send_credentials_email">
                                                    <i class="fas fa-envelope me-1"></i>Enviar credenciales por email al cliente
                                                </label>
                                            </div>
                                            <small class="text-muted">Se enviara un correo con las credenciales de acceso al usuario.</small>
                                        </div>

                                        <div class="mb-3">
                                            <label for="role_id" class="form-label">Tipo de Usuario <span class="text-danger">*</span></label>
                                            <select class="form-select" id="role_id" name="role_id" required>
                                                <option value="">Seleccionar tipo...</option>
                                                <?php foreach ($roles as $role): ?>
                                                    <option value="<?= $role['id'] ?>"
                                                            data-role-name="<?= $role['name'] ?>"
                                                            <?= old('role_id') == $role['id'] ? 'selected' : '' ?>>
                                                        <?= $role['name'] === 'cliente_gestor' ? 'Gestor Multiempresa' : 'Cliente Individual' ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <small class="text-muted" id="role_hint"></small>
                                        </div>

                                        <div class="mb-3">
                                            <label for="company_id" class="form-label">Empresa <span class="text-danger">*</span></label>
                                            <select class="form-select" id="company_id" name="company_id" required>
                                                <option value="">Seleccionar empresa...</option>
                                                <?php foreach ($companies as $company): ?>
                                                    <option value="<?= $company['id'] ?>"
                                                            data-type="<?= $company['type'] ?>"
                                                            <?= old('company_id') == $company['id'] ? 'selected' : '' ?>>
                                                        <?= esc($company['name']) ?> (<?= $company['type'] === 'gestor_multicompania' ? 'Gestor' : 'Individual' ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <small class="text-muted" id="company_hint"></small>
                                        </div>

                                        <div class="d-flex gap-2 mt-4">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save me-2"></i>Crear Usuario
                                            </button>
                                            <a href="<?= base_url('client-users') ?>" class="btn btn-secondary">
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
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const password = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');

            if (password.type === 'password') {
                password.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                password.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        });

        // Generate random password
        document.getElementById('generatePassword').addEventListener('click', function() {
            const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789@#$%';
            let password = '';
            for (let i = 0; i < 12; i++) {
                password += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            document.getElementById('password').value = password;
            document.getElementById('password').type = 'text';
            document.getElementById('eyeIcon').classList.remove('fa-eye');
            document.getElementById('eyeIcon').classList.add('fa-eye-slash');
        });

        // Filtrar empresas según tipo de usuario seleccionado
        const roleSelect = document.getElementById('role_id');
        const companySelect = document.getElementById('company_id');
        const roleHint = document.getElementById('role_hint');
        const companyHint = document.getElementById('company_hint');

        function updateCompanyFilter() {
            const selectedOption = roleSelect.options[roleSelect.selectedIndex];
            const roleName = selectedOption.dataset.roleName;

            if (roleName === 'cliente_gestor') {
                roleHint.textContent = 'Este usuario podrá ver informes de todas las empresas del grupo';
                companyHint.textContent = 'Selecciona una empresa de tipo Gestor Multicliente';
                // Filtrar solo empresas gestoras
                Array.from(companySelect.options).forEach(option => {
                    if (option.value && option.dataset.type !== 'gestor_multicompania') {
                        option.style.display = 'none';
                    } else {
                        option.style.display = 'block';
                    }
                });
            } else if (roleName === 'cliente_empresa') {
                roleHint.textContent = 'Este usuario solo podrá ver informes de su empresa';
                companyHint.textContent = 'Selecciona una empresa individual';
                // Filtrar solo empresas individuales
                Array.from(companySelect.options).forEach(option => {
                    if (option.value && option.dataset.type !== 'empresa_individual') {
                        option.style.display = 'none';
                    } else {
                        option.style.display = 'block';
                    }
                });
            } else {
                roleHint.textContent = '';
                companyHint.textContent = '';
                // Mostrar todas las opciones
                Array.from(companySelect.options).forEach(option => {
                    option.style.display = 'block';
                });
            }

            // Reset company selection if current value is now hidden
            const currentOption = companySelect.options[companySelect.selectedIndex];
            if (currentOption && currentOption.style.display === 'none') {
                companySelect.value = '';
            }
        }

        roleSelect.addEventListener('change', updateCompanyFilter);
        // Ejecutar al cargar si hay un valor previo
        if (roleSelect.value) {
            updateCompanyFilter();
        }
    </script>
</body>
</html>
