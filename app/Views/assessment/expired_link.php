<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enlace Expirado - PsyRisk</title>
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
        .error-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 3rem;
            max-width: 500px;
            text-align: center;
        }
        .error-icon {
            font-size: 5rem;
            color: #ffc107;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <i class="fas fa-clock error-icon"></i>
        <h2 class="mb-3">Enlace Expirado</h2>
        <p class="text-muted mb-4">
            El enlace para completar la batería de riesgo psicosocial ha expirado.
        </p>
        <?php if(isset($service)): ?>
        <p class="text-muted">
            <strong>Fecha de expiración:</strong> <?= date('d/m/Y', strtotime($service['link_expiration_date'])) ?>
        </p>
        <?php endif; ?>
        <p class="text-muted mt-4">
            Por favor, contacta con el responsable del servicio para solicitar un nuevo enlace.
        </p>
    </div>
</body>
</html>
