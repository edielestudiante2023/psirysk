<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - PsyRisk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-6 text-center">
                <div class="card shadow-sm">
                    <div class="card-body p-5">
                        <i class="fas fa-check-circle fa-5x text-success mb-4"></i>
                        <h2 class="mb-3"><?= esc($title) ?></h2>
                        <p class="lead mb-4"><?= esc($message) ?></p>

                        <div class="alert alert-success text-start">
                            <p class="mb-0">
                                <i class="fas fa-envelope me-2"></i>
                                El solicitante recibirá una notificación por email con las instrucciones
                                para acceder a los resultados individuales.
                            </p>
                        </div>

                        <a href="<?= base_url('individual-results/management') ?>" class="btn btn-primary btn-lg mt-3">
                            <i class="fas fa-list me-2"></i>Ver Solicitudes Pendientes
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
