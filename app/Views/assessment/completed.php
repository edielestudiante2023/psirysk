<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Batería Completada - PsyRisk</title>
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
        .success-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 3rem;
            max-width: 600px;
            text-align: center;
        }
        .success-icon {
            font-size: 5rem;
            color: #28a745;
            margin-bottom: 1.5rem;
            animation: checkmark 0.8s ease;
        }
        @keyframes checkmark {
            0% { transform: scale(0); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>
    <div class="success-card">
        <i class="fas fa-check-circle success-icon"></i>
        <h2 class="mb-3">¡Batería Completada!</h2>
        <p class="text-muted mb-4">
            Hemos recibido todas tus respuestas exitosamente.
        </p>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Próximos pasos:</strong><br>
            Los resultados serán procesados y tu empresa recibirá el informe correspondiente.
            El equipo de Seguridad y Salud en el Trabajo te contactará con los resultados.
        </div>
        <p class="text-muted mt-4">
            <small>
                <strong>Completado el:</strong> <?= date('d/m/Y H:i') ?><br>
                Gracias por tu participación en la evaluación de riesgo psicosocial.
            </small>
        </p>
        <hr class="my-4">
        <p class="text-muted">
            <small>Puedes cerrar esta ventana de forma segura.</small>
        </p>
    </div>
</body>
</html>
