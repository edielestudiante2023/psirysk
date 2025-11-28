<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluación de Riesgo Psicosocial - <?= esc($companyName) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 30px;
        }
        .greeting {
            font-size: 18px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 15px;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            padding: 15px 40px;
            border-radius: 5px;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
        }
        .cta-button:hover {
            opacity: 0.9;
        }
        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .warning-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ddd;
        }
        .steps {
            background: #e7f3ff;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .steps ol {
            margin: 10px 0;
            padding-left: 20px;
        }
        .steps li {
            margin: 8px 0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>🧠 Evaluación de Riesgo Psicosocial</h1>
            <p style="margin: 5px 0;"><?= esc($companyName) ?></p>
        </div>

        <div class="content">
            <p class="greeting">Hola, <?= esc($workerName) ?></p>

            <p>Te invitamos a completar la <strong>Batería de Evaluación de Factores de Riesgo Psicosocial</strong> establecida por el Ministerio de Trabajo de Colombia.</p>

            <p>Esta evaluación es importante para identificar los factores de riesgo que pueden afectar tu bienestar en el trabajo y nos ayudará a implementar mejoras en el ambiente laboral.</p>

            <div class="info-box">
                <strong>📋 Acerca de esta evaluación:</strong>
                <ul style="margin: 10px 0;">
                    <li>✅ Es confidencial y anónima</li>
                    <li>⏱️ Toma aproximadamente 30-45 minutos</li>
                    <li>📱 Puedes completarla desde cualquier dispositivo</li>
                    <li>💾 Puedes pausar y continuar más tarde</li>
                </ul>
            </div>

            <div class="steps">
                <strong>Pasos para completar la evaluación:</strong>
                <ol>
                    <li>Haz clic en el botón "Iniciar Evaluación"</li>
                    <li>Completa tus datos generales</li>
                    <li>Responde los cuestionarios de manera honesta</li>
                    <li>Envía tu evaluación al finalizar</li>
                </ol>
            </div>

            <center>
                <a href="<?= $assessmentLink ?>" class="cta-button">
                    📝 Iniciar Evaluación
                </a>
            </center>

            <div class="warning-box">
                <strong>⚠️ Importante:</strong> Este enlace es personal e intransferible. Vence el <strong><?= $expirationDate ?></strong>.
            </div>

            <p style="margin-top: 30px; font-size: 14px; color: #666;">
                Si tienes alguna pregunta o dificultad técnica, contacta al equipo de recursos humanos de tu empresa.
            </p>

            <p style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; font-size: 12px; color: #999;">
                <strong>Enlace de acceso directo:</strong><br>
                <a href="<?= $assessmentLink ?>" style="color: #667eea; word-break: break-all;"><?= $assessmentLink ?></a>
            </p>
        </div>

        <div class="footer">
            <p><strong>PsyRisk</strong> - Sistema de Evaluación de Factores de Riesgo Psicosocial</p>
            <p><?= esc($companyName) ?> © <?= date('Y') ?></p>
            <p style="margin-top: 10px; font-size: 11px;">
                Este correo fue enviado de manera automática. Por favor no respondas a este mensaje.
            </p>
        </div>
    </div>
</body>
</html>
