<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .header p { margin: 10px 0 0 0; font-size: 14px; opacity: 0.9; }
        .content { padding: 30px 20px; }
        .alert-box { background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px; }
        .alert-box strong { color: #856404; }
        .info-section { background-color: #f8f9fa; border-radius: 4px; padding: 15px; margin: 20px 0; }
        .info-row { margin: 10px 0; }
        .info-row label { font-weight: bold; color: #666; display: inline-block; width: 150px; }
        .info-row value { color: #333; }
        .motivation-box { background-color: #e7f3ff; border: 1px solid #b3d9ff; border-radius: 4px; padding: 15px; margin: 20px 0; }
        .motivation-box h4 { margin-top: 0; color: #0066cc; }
        .button-container { text-align: center; margin: 30px 0; }
        .button { display: inline-block; padding: 14px 30px; margin: 10px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px; }
        .button-approve { background-color: #28a745; color: white; }
        .button-review { background-color: #007bff; color: white; }
        .button:hover { opacity: 0.9; }
        .footer { background-color: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; }
        .footer a { color: #007bff; text-decoration: none; }
        .urgent-badge { background-color: #dc3545; color: white; padding: 5px 10px; border-radius: 3px; font-size: 12px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔔 Nueva Solicitud de Acceso</h1>
            <p>Resultados Individuales - Batería de Riesgo Psicosocial</p>
        </div>

        <div class="content">
            <p>Estimado(a) <strong><?= esc($consultantName) ?></strong>,</p>

            <div class="alert-box">
                <strong>Tiene una nueva solicitud pendiente de revisión</strong><br>
                Una solicitud de acceso a resultados individuales requiere su aprobación.
            </div>

            <div class="info-section">
                <h3 style="margin-top: 0; color: #333;">📋 Información de la Solicitud</h3>

                <div class="info-row">
                    <label>Empresa:</label>
                    <value><strong><?= esc($request['company_name']) ?></strong></value>
                </div>
                <div class="info-row">
                    <label>Servicio:</label>
                    <value><?= esc($request['service_name']) ?></value>
                </div>
                <div class="info-row">
                    <label>Trabajador:</label>
                    <value><?= esc($request['worker_name']) ?></value>
                </div>
                <div class="info-row">
                    <label>Documento:</label>
                    <value><?= esc($request['worker_document']) ?></value>
                </div>
                <div class="info-row">
                    <label>Tipo de Evaluación:</label>
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
                    <label>Solicitado por:</label>
                    <value><?= esc($request['requester_name']) ?></value>
                </div>
                <div class="info-row">
                    <label>Email solicitante:</label>
                    <value><?= esc($request['requester_email']) ?></value>
                </div>
                <div class="info-row">
                    <label>Fecha de solicitud:</label>
                    <value><?= date('d/m/Y H:i', strtotime($request['created_at'])) ?></value>
                </div>
            </div>

            <div class="motivation-box">
                <h4>📝 Motivación de la Solicitud</h4>
                <p style="margin: 0; line-height: 1.6;">
                    <?= nl2br(esc($request['motivation'])) ?>
                </p>
            </div>

            <div class="button-container">
                <a href="<?= $reviewUrl ?>" class="button button-review">
                    📋 Revisar Solicitud
                </a>
                <br>
                <small style="color: #666;">O apruebe directamente desde este email:</small>
                <br>
                <a href="<?= $approveUrl ?>" class="button button-approve">
                    ✅ Aprobar Acceso (48 horas)
                </a>
            </div>

            <p style="font-size: 14px; color: #666; border-top: 1px solid #ddd; padding-top: 20px;">
                <strong>Nota:</strong> Esta solicitud quedará registrada en el sistema con fines de auditoría y cumplimiento legal.
                El acceso aprobado será temporal y automáticamente revocado al vencimiento.
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
