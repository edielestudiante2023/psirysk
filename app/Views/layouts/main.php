<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'PsyRisk' ?> - RPS Cycloid</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
            --sidebar-width: 260px;
        }
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--primary-gradient);
            color: white;
            z-index: 1000;
            overflow-y: auto;
        }
        .sidebar-logo {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar-logo img {
            max-width: 150px;
            height: auto;
        }
        .sidebar-user {
            padding: 15px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            margin: 5px 15px;
            border-radius: 8px;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: rgba(255,255,255,0.2);
            color: white;
        }
        .sidebar .nav-link i {
            width: 24px;
        }
        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }
        .navbar-custom {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        /* Cards */
        .stat-card {
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border: none;
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-card .icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }
        /* Footer */
        .main-footer {
            background: white;
            padding: 20px;
            border-top: 1px solid #e9ecef;
            margin-top: auto;
        }
        .main-footer .footer-logos {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
        }
        .main-footer .footer-logos img {
            height: 70px;
            opacity: 0.7;
            transition: opacity 0.3s;
        }
        .main-footer .footer-logos img.logo-psicloid {
            height: 140px;
        }
        .main-footer .footer-logos img:hover {
            opacity: 1;
        }
        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s;
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
        }
        <?= $this->renderSection('styles') ?>
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-logo">
            <img src="<?= base_url('images/logos/logo_psirysk.png') ?>" alt="PsyRisk">
        </div>
        <div class="sidebar-user">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                    <div class="bg-white bg-opacity-25 rounded-circle p-2">
                        <i class="fas fa-user"></i>
                    </div>
                </div>
                <div class="flex-grow-1 ms-3">
                    <p class="mb-0 fw-semibold"><?= session()->get('name') ?? 'Usuario' ?></p>
                    <small class="text-white-50"><?= ucfirst(session()->get('role_name') ?? 'Rol') ?></small>
                </div>
            </div>
        </div>

        <nav class="nav flex-column mt-3">
            <?= $this->renderSection('sidebar_menu') ?>

            <hr class="text-white-50 mx-3">
            <a class="nav-link" href="<?= base_url('logout') ?>">
                <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesion
            </a>
        </nav>

    </aside>

    <!-- Main Content -->
    <main class="main-content d-flex flex-column">
        <!-- Top Navbar -->
        <nav class="navbar navbar-custom navbar-expand-lg px-4 py-3">
            <div class="container-fluid">
                <button class="btn btn-link d-lg-none me-3" type="button" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h4 class="mb-0"><?= $pageTitle ?? $title ?? 'Dashboard' ?></h4>
                <div class="ms-auto d-flex align-items-center gap-3">
                    <span class="badge bg-primary"><?= date('d/m/Y') ?></span>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <div class="flex-grow-1 p-4">
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= session()->getFlashdata('success') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= session()->getFlashdata('error') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?= $this->renderSection('content') ?>
        </div>

        <!-- Footer -->
        <footer class="main-footer">
            <div class="footer-logos mb-3">
                <img src="<?= base_url('images/logos/cycloidgrissinfondo.png') ?>" alt="Cycloid Talent" title="Cycloid Talent SAS">
                <img src="<?= base_url('images/logos/logo_rps.png') ?>" alt="RPS" title="Portafolio RPS">
                <img src="<?= base_url('images/logos/logo_psicloid_method.png') ?>" alt="Psicloid Method" title="Metodologia Psicloid" class="logo-psicloid">
                <img src="<?= base_url('images/logos/logoenterprisesstobscuro.png') ?>" alt="Enterprisesst" title="Desarrollado por Enterprisesst">
            </div>
            <div class="text-center text-muted small">
                <p class="mb-0">&copy; <?= date('Y') ?> Cycloid Talent SAS. Todos los derechos reservados.</p>
                <p class="mb-0">Desarrollado por Enterprisesst</p>
            </div>
        </footer>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sidebar toggle for mobile
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('show');
        });
    </script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
