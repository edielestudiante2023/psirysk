<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informes Disponibles - <?= esc($companyName) ?></title>
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
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
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
            color: #28a745;
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
            background: #e7f3ff;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .success-box {
            background: #d4edda;
            border-left: 4px solid #28a745;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .stats-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .stats-table td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .stats-table td:first-child {
            font-weight: bold;
            color: #666;
        }
        .stats-table td:last-child {
            text-align: right;
            color: #667eea;
            font-weight: bold;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ddd;
        }
        .badge {
            display: inline-block;
            background: #28a745;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>✅ Servicio Finalizado</h1>
            <p style="margin: 5px 0; font-size: 16px;">Informes de Riesgo Psicosocial Disponibles</p>
        </div>

        <div class="content">
            <p class="greeting">Hola, <?= esc($clientName) ?></p>

            <div class="success-box">
                <p style="margin: 0; font-size: 16px;">
                    <strong>🎉 ¡Buenas noticias!</strong><br>
                    El servicio de <strong><?= esc($serviceName) ?></strong> ha sido finalizado exitosamente.
                </p>
            </div>

            <p>Nos complace informarte que los informes de evaluación de riesgo psicosocial para <strong><?= esc($companyName) ?></strong> ya están disponibles para su consulta.</p>

            <div class="info-box">
                <strong>📊 Resumen del Servicio:</strong>
                <table class="stats-table">
                    <tr>
                        <td>Total de trabajadores invitados:</td>
                        <td><?= $totalCount ?></td>
                    </tr>
                    <tr>
                        <td>Trabajadores que completaron:</td>
                        <td><?= $completedCount ?></td>
                    </tr>
                    <tr>
                        <td>Porcentaje de participación:</td>
                        <td><span class="badge"><?= number_format($participationPercent, 1) ?>%</span></td>
                    </tr>
                </table>
            </div>

            <p><strong>Los informes incluyen:</strong></p>
            <ul>
                <li>📈 Dashboard interactivo con segmentadores demográficos</li>
                <li>📊 Análisis de resultados por dominios y dimensiones</li>
                <li>⚠️ Identificación de trabajadores en riesgo</li>
                <li>💡 Recomendaciones y planes de acción</li>
                <li>📋 Informes ejecutivos descargables</li>
            </ul>

            <center>
                <a href="<?= $reportsLink ?>" class="cta-button">
                    📄 Acceder a los Informes
                </a>
            </center>

            <p style="margin-top: 30px; font-size: 14px; color: #666;">
                Los informes están disponibles en la plataforma PsyRisk. Puedes acceder en cualquier momento desde tu panel de control.
            </p>

            <div class="info-box">
                <strong>💡 ¿Necesitas ayuda?</strong><br>
                Si tienes alguna pregunta sobre los resultados o requieres asesoría para interpretar los informes,
                nuestro equipo consultor está disponible para apoyarte.
            </div>

            <p style="margin-top: 20px; font-size: 14px;">
                Gracias por confiar en <strong>PsyRisk</strong> para la evaluación de riesgo psicosocial de tu organización.
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
