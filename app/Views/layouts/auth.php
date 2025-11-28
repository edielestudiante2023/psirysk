<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Login' ?> - RPS Cycloid</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #5a67d8 100%);
            min-height: 100vh;
            display: flex;
            position: relative;
        }

        /* Capa de degradado animado */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg,
                rgba(255,255,255,0) 0%,
                rgba(255,255,255,0.15) 30%,
                rgba(255,255,255,0.25) 50%,
                rgba(255,255,255,0.15) 70%,
                rgba(255,255,255,0) 100%);
            background-size: 300% 300%;
            animation: shimmer-bg 10s ease-in-out infinite;
            pointer-events: none;
            z-index: 0;
        }

        /* Particulas de escarcha */
        body::after {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image:
                radial-gradient(2px 2px at 10% 20%, rgba(255,255,255,0.8) 0%, transparent 100%),
                radial-gradient(2px 2px at 20% 50%, rgba(255,255,255,0.6) 0%, transparent 100%),
                radial-gradient(3px 3px at 30% 80%, rgba(255,255,255,0.7) 0%, transparent 100%),
                radial-gradient(2px 2px at 40% 30%, rgba(255,255,255,0.5) 0%, transparent 100%),
                radial-gradient(3px 3px at 50% 60%, rgba(255,255,255,0.8) 0%, transparent 100%),
                radial-gradient(2px 2px at 60% 10%, rgba(255,255,255,0.6) 0%, transparent 100%),
                radial-gradient(2px 2px at 70% 40%, rgba(255,255,255,0.7) 0%, transparent 100%),
                radial-gradient(3px 3px at 80% 70%, rgba(255,255,255,0.5) 0%, transparent 100%),
                radial-gradient(2px 2px at 90% 25%, rgba(255,255,255,0.8) 0%, transparent 100%),
                radial-gradient(2px 2px at 15% 75%, rgba(255,255,255,0.6) 0%, transparent 100%),
                radial-gradient(3px 3px at 35% 15%, rgba(255,255,255,0.7) 0%, transparent 100%),
                radial-gradient(2px 2px at 55% 45%, rgba(255,255,255,0.5) 0%, transparent 100%),
                radial-gradient(2px 2px at 75% 85%, rgba(255,255,255,0.8) 0%, transparent 100%),
                radial-gradient(3px 3px at 95% 55%, rgba(255,255,255,0.6) 0%, transparent 100%),
                radial-gradient(2px 2px at 25% 95%, rgba(255,255,255,0.7) 0%, transparent 100%),
                radial-gradient(2px 2px at 65% 5%, rgba(255,255,255,0.5) 0%, transparent 100%);
            animation: sparkle 8s ease-in-out infinite;
            pointer-events: none;
            z-index: 0;
        }

        /* Animacion del brillo de fondo */
        @keyframes shimmer-bg {
            0% {
                background-position: 0% 0%;
            }
            50% {
                background-position: 100% 100%;
            }
            100% {
                background-position: 0% 0%;
            }
        }

        /* Animacion de escarcha */
        @keyframes sparkle {
            0%, 100% {
                opacity: 0.5;
            }
            25% {
                opacity: 0.8;
            }
            50% {
                opacity: 0.6;
            }
            75% {
                opacity: 0.9;
            }
        }

        /* Sidebars laterales */
        .sidebar-logos {
            position: fixed;
            top: 0;
            width: 240px;
            height: 100vh;
            background: rgba(255,255,255,0.95);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 60px;
            padding: 40px 20px;
            box-shadow: 0 0 30px rgba(0,0,0,0.15);
            z-index: 100;
        }
        .sidebar-left {
            left: 0;
            border-radius: 0 20px 20px 0;
        }
        .sidebar-right {
            right: 0;
            border-radius: 20px 0 0 20px;
        }
        .sidebar-logos .logo-item {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .sidebar-logos .logo-item img {
            max-width: 180px;
            max-height: 180px;
            object-fit: contain;
            transition: transform 0.3s ease;
        }
        .sidebar-logos .logo-item img:hover {
            transform: scale(1.08);
        }
        .sidebar-logos .logo-item.logo-large img {
            max-width: 200px;
            max-height: 360px;
        }

        /* Animaciones para logos - movimiento duplicado */
        .sidebar-left .logo-item:nth-child(1) img {
            animation: subtle-breathe-1 7s ease-in-out infinite;
        }
        .sidebar-left .logo-item:nth-child(2) img {
            animation: subtle-breathe-2 8s ease-in-out infinite;
        }
        .sidebar-right .logo-item:nth-child(1) img {
            animation: subtle-breathe-3 9s ease-in-out infinite;
        }
        .sidebar-right .logo-item:nth-child(2) img {
            animation: subtle-breathe-4 10s ease-in-out infinite;
        }

        @keyframes subtle-breathe-1 {
            0%, 100% {
                transform: translateY(0) scale(1);
                opacity: 1;
            }
            50% {
                transform: translateY(-16px) scale(1.04);
                opacity: 0.92;
            }
        }
        @keyframes subtle-breathe-2 {
            0%, 100% {
                transform: translateY(0) scale(1);
                opacity: 1;
            }
            50% {
                transform: translateY(12px) scale(1.04);
                opacity: 0.92;
            }
        }
        @keyframes subtle-breathe-3 {
            0%, 100% {
                transform: translateY(0) scale(1);
                opacity: 1;
            }
            50% {
                transform: translateY(-12px) scale(1.04);
                opacity: 0.92;
            }
        }
        @keyframes subtle-breathe-4 {
            0%, 100% {
                transform: translateY(0) scale(1);
                opacity: 1;
            }
            50% {
                transform: translateY(16px) scale(1.03);
                opacity: 0.92;
            }
        }

        /* Contenedor principal */
        .auth-main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 260px;
            min-height: 100vh;
        }
        .auth-container {
            width: 100%;
            max-width: 450px;
        }
        .auth-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 50px rgba(0,0,0,0.25);
            padding: 40px;
        }
        .auth-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .auth-logo img {
            max-width: 200px;
            height: auto;
        }
        .auth-logo p {
            color: #6c757d;
            font-size: 0.9rem;
            margin-top: 10px;
            margin-bottom: 0;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .auth-footer {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }

        /* Responsive */
        @media (max-width: 1400px) {
            .sidebar-logos {
                width: 180px;
                gap: 40px;
            }
            .sidebar-logos .logo-item img {
                max-width: 140px;
                max-height: 140px;
            }
            .sidebar-logos .logo-item.logo-large img {
                max-width: 160px;
                max-height: 280px;
            }
            .auth-main {
                padding: 40px 200px;
            }
        }
        @media (max-width: 1200px) {
            .sidebar-logos {
                width: 140px;
                gap: 30px;
            }
            .sidebar-logos .logo-item img {
                max-width: 110px;
                max-height: 110px;
            }
            .sidebar-logos .logo-item.logo-large img {
                max-width: 120px;
                max-height: 200px;
            }
            .auth-main {
                padding: 40px 160px;
            }
        }
        @media (max-width: 992px) {
            .sidebar-logos {
                width: 100px;
                gap: 25px;
            }
            .sidebar-logos .logo-item img {
                max-width: 80px;
                max-height: 80px;
            }
            .sidebar-logos .logo-item.logo-large img {
                max-width: 90px;
                max-height: 150px;
            }
            .auth-main {
                padding: 40px 120px;
            }
        }
        @media (max-width: 768px) {
            .sidebar-logos {
                display: none;
            }
            .auth-main {
                padding: 40px 20px;
            }
        }

        <?= $this->renderSection('styles') ?>
    </style>
</head>
<body>
    <!-- Sidebar izquierdo -->
    <aside class="sidebar-logos sidebar-left">
        <div class="logo-item">
            <img src="<?= base_url('images/logos/logo_psirysk.png') ?>" alt="PsyRisk" title="PsyRisk">
        </div>
        <div class="logo-item">
            <img src="<?= base_url('images/logos/logoenterprisesstobscuro.png') ?>" alt="Enterprisesst" title="Desarrollado por Enterprisesst">
        </div>
    </aside>

    <!-- Sidebar derecho -->
    <aside class="sidebar-logos sidebar-right">
        <div class="logo-item" style="margin-top: 40px;">
            <img src="<?= base_url('images/logos/logo_rps.png') ?>" alt="RPS" title="Portafolio RPS">
        </div>
        <div class="logo-item logo-large">
            <img src="<?= base_url('images/logos/logo_psicloid_method.png') ?>" alt="Psicloid Method" title="Metodologia Psicloid">
        </div>
    </aside>

    <!-- Contenido principal -->
    <main class="auth-main">
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-logo">
                    <img src="<?= base_url('images/logos/cycloidgrissinfondo.png') ?>" alt="Cycloid Talent">
                    <p>Bateria de Riesgo Psicosocial</p>
                </div>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= session()->getFlashdata('error') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= session()->getFlashdata('success') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?= $this->renderSection('content') ?>

                <div class="auth-footer">
                    <small class="text-muted d-block">&copy; <?= date('Y') ?> Cycloid Talent SAS</small>
                    <small class="text-muted">Desarrollado por Enterprisesst</small>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
