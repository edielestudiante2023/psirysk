<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 30px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .header p { margin: 10px 0 0 0; font-size: 14px; opacity: 0.9; }
        .content { padding: 30px 20px; }
        .success-box { background-color: #d4edda; border-left: 4px solid #28a745; padding: 20px; margin: 20px 0; border-radius: 4px; text-align: center; }
        .success-box h3 { margin: 0; color: #155724; }
        .success-box p { margin: 10px 0 0 0; color: #155724; font-size: 18px; }
        .info-section { background-color: #f8f9fa; border-radius: 4px; padding: 15px; margin: 20px 0; }
        .info-row { margin: 10px 0; }
        .info-row label { font-weight: bold; color: #666; display: inline-block; width: 130px; }
        .button-container { text-align: center; margin: 30px 0; }
        .button { display: inline-block; padding: 16px 40px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 18px; background-color: #28a745; color: white; }
        .button:hover { opacity: 0.9; }
        .warning-box { background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px; }
        .footer { background-color: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; }
        .footer a { color: #007bff; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✅ Solicitud Aprobada</h1>
            <p>Acceso a Resultados Individuales Autorizado</p>
        </div>

        <div class="content">
            <p>Estimado(a) <strong><?= esc($clientName) ?></strong>,</p>

            <div class="success-box">
                <h3>Su solicitud ha sido aprobada</h3>
                <p>El acceso estará disponible hasta:<br>
                <strong style="font-size: 20px;"><?= date('d/m/Y H:i', strtotime($request['access_granted_until'])) ?></strong></p>
            </div>

            <div class="info-section">
                <h3 style="margin-top: 0; color: #333;">📋 Detalles del Acceso</h3>

                <div class="info-row">
                    <label>Trabajador:</label>
                    <value><?= esc($request['worker_name']) ?></value>
                </div>
                <div class="info-row">
                    <label>Tipo:</label>
                    <value>
                        <?php
                            $types = [
                                'intralaboral_a' => 'Intralaboral Forma A',
                                'intralaboral_b' => 'Intralaboral Forma B',
                                'extralaboral' => 'Extralaboral',
                                'estres' => 'Estrés'
                            ];
                            echo $types[$request['request_type']] ?? $request['request_type'];
                        ?>
                    </value>
                </div>
                <div class="info-row">
                    <label>Aprobado por:</label>
                    <value><?= esc($request['reviewer_name']) ?></value>
                </div>
                <?php if ($request['review_notes']): ?>
                <div class="info-row">
                    <label>Notas:</label>
                    <value><?= nl2br(esc($request['review_notes'])) ?></value>
                </div>
                <?php endif; ?>
            </div>

            <div class="button-container">
                <a href="<?= $accessUrl ?>" class="button">
                    👁️ Ver Resultados Individuales
                </a>
            </div>

            <div class="warning-box">
                <strong>⚠️ Importante:</strong>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>El acceso es temporal y expirará automáticamente en la fecha indicada</li>
                    <li>La información es confidencial y debe ser tratada según la normativa de protección de datos</li>
                    <li>Todo acceso queda registrado con fines de auditoría</li>
                    <li>Si necesita una extensión, debe realizar una nueva solicitud</li>
                </ul>
            </div>

            <p style="text-align: center; margin-top: 20px;">
                <a href="<?= $statusUrl ?>" style="color: #007bff; text-decoration: none;">
                    Ver estado completo de la solicitud →
                </a>
            </p>
        </div>

        <div class="footer">
            <p><strong>Cycloid Talent SAS</strong><br>
            Sistema de Gestión de Riesgo Psicosocial</p>
            <p>
                <a href="https://cycloidtalent.com/riesgo-psicosocial">www.cycloidtalent.com/riesgo-psicosocial</a>
            </p>
            <p style="margin-top: 15px; font-size: 11px; color: #999;">
                Este es un mensaje automático. Por favor no responder a este correo.<br>
                &copy; <?= date('Y') ?> Cycloid Talent SAS. Todos los derechos reservados.
            </p>
        </div>
    </div>
</body>
</html>
