<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'PsyRisk' ?></title>
    <!--[if mso]>
    <style type="text/css">
        body, table, td {font-family: Arial, Helvetica, sans-serif !important;}
    </style>
    <![endif]-->
</head>
<body style="margin: 0; padding: 0; background-color: #f4f4f4; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
    <!-- Wrapper Table -->
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f4f4f4;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                <!-- Email Container -->
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="background-color: #ffffff; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
                            <img src="<?= base_url('images/logos/cycloidgrissinfondo.png') ?>" alt="Cycloid Talent" style="max-width: 150px; height: auto;">
                            <p style="color: rgba(255,255,255,0.9); margin: 15px 0 0 0; font-size: 14px;">Bateria de Riesgo Psicosocial</p>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <?= $this->renderSection('content') ?>
                        </td>
                    </tr>

                    <!-- Divider -->
                    <tr>
                        <td style="padding: 0 30px;">
                            <hr style="border: none; border-top: 1px solid #e9ecef; margin: 0;">
                        </td>
                    </tr>

                    <!-- Logos Footer -->
                    <tr>
                        <td style="padding: 25px 30px; text-align: center;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center">
                                <tr>
                                    <td style="padding: 0 15px;">
                                        <img src="<?= base_url('images/logos/cycloidgrissinfondo.png') ?>" alt="Cycloid" style="height: 35px;">
                                    </td>
                                    <td style="padding: 0 15px;">
                                        <img src="<?= base_url('images/logos/logo_rps.png') ?>" alt="RPS" style="height: 35px;">
                                    </td>
                                    <td style="padding: 0 15px;">
                                        <img src="<?= base_url('images/logos/logo_psicloid_method.png') ?>" alt="Psicloid" style="height: 35px;">
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 25px 30px; text-align: center; border-radius: 0 0 10px 10px;">
                            <p style="margin: 0 0 10px 0; color: #666; font-size: 13px;">
                                <strong>Cycloid Talent SAS</strong><br>
                                Gestion del Talento Humano
                            </p>
                            <p style="margin: 0 0 15px 0; color: #999; font-size: 11px;">
                                <img src="<?= base_url('images/logos/logoenterprisesstobscuro.jpg') ?>" alt="STOB" style="height: 15px; vertical-align: middle; margin-right: 5px;">
                                Desarrollado por Enterprisesst
                            </p>
                            <p style="margin: 0; color: #999; font-size: 10px;">
                                Este correo fue enviado automaticamente por el sistema PsyRisk.<br>
                                Por favor no responda a este mensaje.
                            </p>
                        </td>
                    </tr>
                </table>

                <!-- Legal Footer -->
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0">
                    <tr>
                        <td style="padding: 20px; text-align: center;">
                            <p style="margin: 0; color: #999; font-size: 10px; line-height: 1.5;">
                                &copy; <?= date('Y') ?> Cycloid Talent SAS. Todos los derechos reservados.<br>
                                La informacion contenida en este correo es confidencial y esta protegida por la Ley 1581 de 2012.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
