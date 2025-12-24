<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; padding: 30px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .header p { margin: 10px 0 0 0; font-size: 14px; opacity: 0.9; }
        .content { padding: 30px 20px; }
        .rejection-box { background-color: #f8d7da; border-left: 4px solid #dc3545; padding: 20px; margin: 20px 0; border-radius: 4px; }
        .rejection-box h3 { margin: 0; color: #721c24; }
        .info-section { background-color: #f8f9fa; border-radius: 4px; padding: 15px; margin: 20px 0; }
        .info-row { margin: 10px 0; }
        .info-row label { font-weight: bold; color: #666; display: inline-block; width: 130px; }
        .review-box { background-color: #fff3cd; border: 1px solid #ffc107; border-radius: 4px; padding: 15px; margin: 20px 0; }
        .review-box h4 { margin-top: 0; color: #856404; }
        .contact-box { background-color: #d1ecf1; border-left: 4px solid #0c5460; padding: 15px; margin: 20px 0; border-radius: 4px; text-align: center; }
        .footer { background-color: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; }
        .footer a { color: #007bff; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>❌ Solicitud No Aprobada</h1>
            <p>Acceso a Resultados Individuales</p>
        </div>

        <div class="content">
            <p>Estimado(a) <strong><?= esc($clientName) ?></strong>,</p>

            <div class="rejection-box">
                <h3>Su solicitud no fue aprobada</h3>
                <p style="margin: 10px 0 0 0; color: #721c24;">
                    Después de revisar su solicitud de acceso a resultados individuales,
                    el consultor especialista ha determinado que no puede ser aprobada en este momento.
                </p>
            </div>

            <div class="info-section">
                <h3 style="margin-top: 0; color: #333;">📋 Detalles de la Solicitud</h3>

                <div class="info-row">
                    <label>Trabajador:</label>
                    <value><?= esc($request['worker_first_name'] . ' ' . $request['worker_last_name']) ?></value>
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
                    <label>Revisado por:</label>
                    <value><?= esc($request['reviewer_first_name'] . ' ' . $request['reviewer_last_name']) ?></value>
                </div>
                <div class="info-row">
                    <label>Fecha de revisión:</label>
                    <value><?= date('d/m/Y H:i', strtotime($request['reviewed_at'])) ?></value>
                </div>
            </div>

            <?php if ($request['review_notes']): ?>
            <div class="review-box">
                <h4>📝 Observaciones del Consultor</h4>
                <p style="margin: 0; line-height: 1.6;">
                    <?= nl2br(esc($request['review_notes'])) ?>
                </p>
            </div>
            <?php endif; ?>

            <div class="contact-box">
                <h4 style="margin-top: 0; color: #0c5460;">¿Necesita más información?</h4>
                <p style="margin: 10px 0;">
                    Si tiene dudas sobre esta decisión o desea discutir su caso específico,
                    por favor contacte a su asesor de Cycloid Talent SAS.
                </p>
                <p style="margin: 15px 0 0 0;">
                    <strong>🌐 Visite:</strong><br>
                    <a href="https://cycloidtalent.com/riesgo-psicosocial" style="color: #0c5460; font-size: 16px;">
                        www.cycloidtalent.com/riesgo-psicosocial
                    </a>
                </p>
            </div>

            <p style="font-size: 14px; color: #666; border-top: 1px solid #ddd; padding-top: 20px;">
                <strong>Nota Legal:</strong> El acceso a resultados individuales de la Batería de Riesgo Psicosocial
                está sujeto a las normativas de protección de datos personales (Ley 1581 de 2012) y requiere
                justificación válida para su aprobación.
            </p>

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
