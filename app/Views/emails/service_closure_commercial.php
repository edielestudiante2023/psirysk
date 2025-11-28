<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Servicio Finalizado - Facturación Pendiente</title>
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
        .success-box {
            background: #d4edda;
            border-left: 4px solid #28a745;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .stats-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
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
            color: #28a745;
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
        .icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="icon">✅</div>
            <h1>Servicio Finalizado</h1>
            <p style="margin: 5px 0; font-size: 16px;">Tu cliente ya puede acceder a los informes</p>
        </div>

        <div class="content">
            <p class="greeting">Hola, <?= esc($sellerName) ?></p>

            <div class="success-box">
                <p style="margin: 0; font-size: 16px;">
                    <strong>🎉 ¡Servicio completado exitosamente!</strong><br>
                    El servicio de riesgo psicosocial para tu cliente ha sido finalizado.
                </p>
            </div>

            <p>Te informamos que el servicio vendido a <strong><?= esc($companyName) ?></strong> ha sido cerrado por el consultor y los informes ya están disponibles para el cliente.</p>

            <div class="info-box">
                <strong>📋 Información del Servicio:</strong>
                <table class="stats-table">
                    <tr>
                        <td>Empresa:</td>
                        <td><?= esc($companyName) ?></td>
                    </tr>
                    <tr>
                        <td>Servicio:</td>
                        <td><?= esc($serviceName) ?></td>
                    </tr>
                    <tr>
                        <td>Trabajadores completados:</td>
                        <td><span class="badge"><?= $completedCount ?></span></td>
                    </tr>
                    <tr>
                        <td>Fecha de cierre:</td>
                        <td><?= $closureDate ?></td>
                    </tr>
                </table>
            </div>

            <div class="info-box">
                <strong>💰 Facturación:</strong>
                <p style="margin: 10px 0 0 0;">
                    El equipo administrativo ha sido notificado para proceder con la facturación del saldo restante del servicio.
                    Puedes hacer seguimiento con el área de facturación si es necesario.
                </p>
            </div>

            <p><strong>📞 Recomendaciones:</strong></p>
            <ul>
                <li>Contacta a tu cliente para confirmar que recibió acceso a los informes</li>
                <li>Ofrece acompañamiento en la interpretación de resultados si lo requiere</li>
                <li>Identifica oportunidades de servicios adicionales o seguimiento</li>
                <li>Solicita retroalimentación sobre el servicio prestado</li>
            </ul>

            <p style="margin-top: 30px; font-size: 14px; color: #666;">
                Gracias por tu gestión comercial. El cliente ahora tiene acceso completo a todos los informes y recomendaciones en la plataforma PsyRisk.
            </p>
        </div>

        <div class="footer">
            <p><strong>PsyRisk</strong> - Sistema de Gestión de Riesgo Psicosocial</p>
            <p>Equipo Comercial © <?= date('Y') ?></p>
            <p style="margin-top: 10px; font-size: 11px;">
                Este correo fue enviado de manera automática. Por favor no respondas a este mensaje.
            </p>
        </div>
    </div>
</body>
</html>
