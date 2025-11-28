<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - PsyRisk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm text-center p-5">
                    <i class="fas fa-chart-bar fa-4x text-muted mb-4"></i>
                    <h4 class="text-muted mb-3">Sin datos disponibles</h4>
                    <p class="text-muted">No hay resultados calculados para este servicio.</p>
                    <p class="text-muted small">Servicio: <?= esc($service['service_name']) ?></p>
                    <a href="<?= base_url('dashboard') ?>" class="btn btn-primary mt-3">
                        <i class="fas fa-arrow-left me-2"></i>Volver al Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
