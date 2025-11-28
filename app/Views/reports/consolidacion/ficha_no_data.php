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
        .empty-state {
            text-align: center;
            padding: 80px 20px;
        }
        .empty-state i {
            font-size: 5rem;
            color: #dee2e6;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('battery-services/' . $service['id']) ?>">Servicio</a></li>
                        <li class="breadcrumb-item active">Ficha de Datos Generales</li>
                    </ol>
                </nav>

                <div class="card shadow-sm">
                    <div class="card-body empty-state">
                        <i class="fas fa-id-card"></i>
                        <h4 class="text-muted">Sin Datos Demográficos</h4>
                        <p class="text-muted">
                            No hay fichas de datos generales completadas para este servicio.<br>
                            Los trabajadores deben completar el formulario de datos generales antes de poder generar este reporte.
                        </p>
                        <div class="mt-4">
                            <a href="<?= base_url('battery-services/' . $service['id']) ?>" class="btn btn-primary">
                                <i class="fas fa-arrow-left me-2"></i>Volver al Servicio
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
