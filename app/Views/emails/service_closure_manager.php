<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Servicio Cerrado - Facturación Pendiente</title>
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
        .warning-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
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
            margin: 20px 0;
            background: #f8f9fa;
            border-radius: 5px;
            overflow: hidden;
        }
        .stats-table td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
        }
        .stats-table tr:last-child td {
            border-bottom: none;
        }
        .stats-table td:first-child {
            font-weight: bold;
            color: #495057;
            width: 60%;
        }
        .stats-table td:last-child {
            text-align: right;
            color: #667eea;
            font-weight: bold;
            font-size: 16px;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ddd;
        }
        .badge-success {
            display: inline-block;
            background: #28a745;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
        }
        .action-required {
            background: #ff9800;
            color: white;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            text-align: center;
            font-weight: bold;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>🔔 Servicio Cerrado</h1>
            <p style="margin: 5px 0; font-size: 16px;">Proceder con Facturación</p>
        </div>

        <div class="content">
            <p class="greeting">Hola, <?= esc($managerName) ?></p>

            <div class="action-required">
                ⚡ ACCIÓN REQUERIDA: Facturación Pendiente
            </div>

            <p>Te informamos que el servicio de evaluación de riesgo psicosocial ha sido cerrado por el consultor y está listo para facturación.</p>

            <div class="info-box">
                <strong>📋 Detalles del Servicio:</strong>
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
                        <td>Consultor asignado:</td>
                        <td><?= esc($consultantName) ?></td>
                    </tr>
                    <tr>
                        <td>Fecha de cierre:</td>
                        <td><?= $closureDate ?></td>
                    </tr>
                </table>
            </div>

            <div class="info-box">
                <strong>👥 Participación:</strong>
                <table class="stats-table">
                    <tr>
                        <td>Total de trabajadores invitados:</td>
                        <td><?= $totalCount ?></td>
                    </tr>
                    <tr>
                        <td>Trabajadores que completaron:</td>
                        <td><span class="badge-success"><?= $completedCount ?></span></td>
                    </tr>
                    <tr>
                        <td>Porcentaje de participación:</td>
                        <td><?= number_format(($completedCount / $totalCount) * 100, 1) ?>%</td>
                    </tr>
                </table>
            </div>

            <div class="warning-box">
                <strong>💰 Próximos Pasos:</strong>
                <ol style="margin: 10px 0; padding-left: 20px;">
                    <li>Generar factura por el saldo restante del servicio</li>
                    <li>Enviar factura al cliente</li>
                    <li>Actualizar estado en el sistema de facturación</li>
                    <li>Coordinar con el equipo comercial si es necesario</li>
                </ol>
            </div>

            <p style="margin-top: 30px; font-size: 14px; color: #666;">
                <strong>Nota:</strong> Los informes ya están disponibles para el cliente en la plataforma PsyRisk.
            </p>

            <p style="margin-top: 20px; font-size: 14px;">
                Si necesitas información adicional o tienes alguna pregunta, contacta al consultor asignado.
            </p>
        </div>

        <div class="footer">
            <p><strong>PsyRisk</strong> - Sistema de Gestión de Riesgo Psicosocial</p>
            <p>Notificación Automática de Cierre de Servicio © <?= date('Y') ?></p>
            <p style="margin-top: 10px; font-size: 11px;">
                Este correo fue enviado de manera automática. Por favor no respondas a este mensaje.
            </p>
        </div>
    </div>
</body>
</html>
