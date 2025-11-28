<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Servicio en Proceso - PsyRisk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container-box {
            max-width: 600px;
        }
        .status-card {
            background: white;
            border-radius: 20px;
            padding: 50px 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            text-align: center;
        }
        .icon-container {
            font-size: 5rem;
            color: #667eea;
            margin-bottom: 30px;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        .progress-custom {
            height: 30px;
            border-radius: 15px;
            background-color: #e9ecef;
            margin: 30px 0;
        }
        .progress-bar-custom {
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        .info-box {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            text-align: left;
        }
        .info-box i {
            color: #667eea;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container container-box">
        <div class="status-card">
            <div class="icon-container">
                <i class="fas fa-hourglass-half"></i>
            </div>

            <h2 class="mb-3">Servicio en Proceso</h2>
            <h5 class="text-muted mb-4"><?= esc($service['service_name']) ?></h5>

            <p class="lead mb-4">
                El servicio de batería de riesgo psicosocial se encuentra actualmente en proceso de recolección de datos.
            </p>

            <?php
            // Calcular progreso (esto sería más preciso con datos reales)
            $workerModel = new \App\Models\WorkerModel();
            $workers = $workerModel->where('battery_service_id', $service['id'])->findAll();
            $total = count($workers);
            $completados = count(array_filter($workers, fn($w) => $w['status'] === 'completado'));
            $porcentaje = ($total > 0) ? round(($completados / $total) * 100) : 0;
            ?>

            <div class="info-box">
                <h6 class="fw-bold mb-3">
                    <i class="fas fa-chart-line"></i>
                    Progreso Actual
                </h6>
                <div class="progress-custom">
                    <div class="progress-bar-custom" style="width: <?= $porcentaje ?>%">
                        <?= $porcentaje ?>%
                    </div>
                </div>
                <p class="mb-0 text-center">
                    <strong><?= $completados ?></strong> de <strong><?= $total ?></strong> trabajadores han completado la batería
                </p>
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>Nota:</strong> Los informes y resultados estarán disponibles una vez que el consultor finalice la recolección de datos y cierre el servicio.
            </div>

            <?php if (!empty($service['link_expiration_date'])): ?>
                <div class="info-box">
                    <p class="mb-1">
                        <i class="fas fa-calendar-alt"></i>
                        <strong>Fecha de expiración de enlaces:</strong>
                    </p>
                    <p class="mb-0">
                        <?= date('d/m/Y', strtotime($service['link_expiration_date'])) ?>
                    </p>
                </div>
            <?php endif; ?>

            <div class="mt-4">
                <a href="<?= base_url('dashboard') ?>" class="btn btn-primary btn-lg">
                    <i class="fas fa-arrow-left me-2"></i>
                    Volver al Dashboard
                </a>
            </div>

            <div class="mt-3">
                <small class="text-muted">
                    <i class="fas fa-headset me-1"></i>
                    Si tienes alguna duda, contacta a tu consultor asignado
                </small>
            </div>
        </div>

        <div class="text-center mt-4">
            <p class="text-white small mb-0">
                <i class="fas fa-shield-alt me-1"></i>
                PsyRisk - Sistema de Gestión de Riesgo Psicosocial
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
