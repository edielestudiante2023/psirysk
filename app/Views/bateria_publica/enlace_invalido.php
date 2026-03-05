<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enlace no disponible - PsyRisk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card-error {
            background: white;
            border-radius: 20px;
            padding: 50px 40px;
            text-align: center;
            max-width: 460px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .icon-circle {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
        }
    </style>
</head>
<body>
    <div class="container px-3">
        <div class="card-error mx-auto">
            <div class="icon-circle">
                <i class="bi bi-link-45deg text-danger" style="font-size: 3rem;"></i>
            </div>

            <h3 class="text-danger mb-3">Enlace no disponible</h3>
            <p class="text-muted mb-4"><?= esc($mensaje) ?></p>

            <div class="alert alert-warning text-start">
                <i class="bi bi-info-circle me-2"></i>
                Si crees que esto es un error, comunícate con el responsable de la evaluación en tu empresa.
            </div>

            <hr>
            <small class="text-muted">
                <i class="bi bi-shield-lock me-1"></i>PsyRisk — Batería de Riesgo Psicosocial
            </small>
        </div>
    </div>
</body>
</html>
