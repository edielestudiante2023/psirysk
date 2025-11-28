<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Email - PsyRisk</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 0 0 10px 10px;
        }
        .success-icon {
            font-size: 48px;
            color: #28a745;
            text-align: center;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>✅ Configuración Exitosa</h1>
        <p>PsyRisk - Sistema de Evaluación Psicosocial</p>
    </div>

    <div class="content">
        <div class="success-icon">✓</div>

        <h2>¡SendGrid está configurado correctamente!</h2>

        <p>Este es un correo de prueba para verificar que la integración con SendGrid está funcionando correctamente en tu aplicación PsyRisk.</p>

        <p><strong>Detalles de la prueba:</strong></p>
        <ul>
            <li><strong>Fecha y hora:</strong> <?= $testDate ?></li>
            <li><strong>Servidor SMTP:</strong> smtp.sendgrid.net</li>
            <li><strong>Puerto:</strong> 587</li>
            <li><strong>Encriptación:</strong> TLS</li>
        </ul>

        <p>Si estás viendo este correo, significa que:</p>
        <ul>
            <li>✅ La API Key de SendGrid es válida</li>
            <li>✅ La configuración SMTP es correcta</li>
            <li>✅ El sistema puede enviar correos HTML</li>
            <li>✅ Tu aplicación está lista para enviar enlaces de evaluación</li>
        </ul>

        <p style="margin-top: 30px; padding: 15px; background: #e7f3ff; border-left: 4px solid #0066cc; border-radius: 4px;">
            <strong>Nota:</strong> Este es un correo automático generado por el sistema PsyRisk para verificar la configuración de SendGrid.
        </p>
    </div>

    <div class="footer">
        <p>PsyRisk © <?= date('Y') ?> - Sistema de Evaluación de Factores de Riesgo Psicosocial</p>
        <p>Este correo fue enviado desde el entorno de <?= ENVIRONMENT ?></p>
    </div>
</body>
</html>
