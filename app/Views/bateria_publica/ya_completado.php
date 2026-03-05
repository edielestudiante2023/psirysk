<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluación ya completada - PsyRisk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card-result {
            background: white;
            border-radius: 20px;
            padding: 50px 40px;
            text-align: center;
            max-width: 480px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .checkmark {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            animation: popIn 0.5s ease-out;
        }
        @keyframes popIn {
            0%   { transform: scale(0); }
            70%  { transform: scale(1.15); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>
    <div class="container px-3">
        <div class="card-result mx-auto">
            <div class="checkmark">
                <i class="bi bi-check-lg text-white" style="font-size: 3.5rem;"></i>
            </div>

            <h2 class="text-success mb-2">¡Ya completaste la evaluación!</h2>
            <p class="text-muted mb-4">
                Hola <strong><?= esc($worker['name']) ?></strong>, tu batería de riesgo psicosocial ya fue registrada exitosamente.
            </p>

            <div class="alert alert-success text-start">
                <i class="bi bi-shield-check me-2"></i>
                Tus respuestas han sido guardadas de forma confidencial.
            </div>

            <hr>
            <p class="text-muted small mb-0">
                <i class="bi bi-building me-1"></i>
                <?= esc($service['company_name']) ?> — <?= esc($service['service_name']) ?>
            </p>
        </div>
    </div>
</body>
</html>
