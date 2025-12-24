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
    <?= view('templates/header') ?>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-6 text-center">
                <div class="card shadow-sm">
                    <div class="card-body p-5">
                        <i class="fas fa-lock fa-5x text-danger mb-4"></i>
                        <h2 class="mb-3"><?= esc($title) ?></h2>
                        <p class="lead mb-4"><?= esc($message) ?></p>

                        <div class="alert alert-info text-start">
                            <h6><i class="fas fa-info-circle me-2"></i>Para ver resultados individuales</h6>
                            <p class="mb-2">
                                Los resultados individuales de trabajadores están protegidos y requieren
                                autorización formal de Cycloid Talent SAS.
                            </p>
                            <p class="mb-0">
                                Por favor contacte a su asesor o visite:
                                <br>
                                <a href="https://cycloidtalent.com/riesgo-psicosocial" target="_blank">
                                    cycloidtalent.com/riesgo-psicosocial
                                </a>
                            </p>
                        </div>

                        <a href="<?= base_url('dashboard') ?>" class="btn btn-primary btn-lg mt-3">
                            <i class="fas fa-home me-2"></i>Volver al Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?= view('templates/footer') ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
