<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Verifica tu cuenta de psyrisk</title>
</head>
<body style="font-family: 'Segoe UI', Tahoma, sans-serif; background: #f4f4f7; margin:0; padding:30px;">
    <table cellpadding="0" cellspacing="0" align="center" style="max-width:600px; background:#fff; border-radius:8px; overflow:hidden;">
        <tr>
            <td style="background:linear-gradient(135deg,#667eea,#764ba2); color:white; padding:30px; text-align:center;">
                <h1 style="margin:0;">psyrisk</h1>
                <p style="margin:5px 0 0;">Plataforma de evaluación psicosocial</p>
            </td>
        </tr>
        <tr>
            <td style="padding:30px;">
                <h2 style="margin-top:0;">Hola <?= esc($name) ?>,</h2>
                <p>Gracias por crear tu cuenta en <strong>psyrisk</strong>.</p>
                <p>Para activarla, haz clic en el siguiente botón. El enlace vence en <?= esc($expiresIn) ?>.</p>
                <p style="text-align:center; margin:30px 0;">
                    <a href="<?= esc($verifyUrl) ?>" style="display:inline-block; background:#0066CC; color:white; padding:14px 28px; border-radius:6px; text-decoration:none; font-weight:600;">Activar mi cuenta</a>
                </p>
                <p style="font-size:12px; color:#666;">Si el botón no funciona, copia y pega este enlace en tu navegador:</p>
                <p style="font-size:12px; color:#666; word-break:break-all;"><?= esc($verifyUrl) ?></p>
                <hr style="border:none; border-top:1px solid #eee; margin:30px 0;">
                <p style="font-size:11px; color:#999;">Si no creaste esta cuenta, puedes ignorar este mensaje. La cuenta no se activará si no haces clic.</p>
            </td>
        </tr>
        <tr>
            <td style="background:#f8f9fa; padding:20px; text-align:center; font-size:11px; color:#666;">
                © <?= date('Y') ?> psyrisk · Bogotá, Colombia<br>
                Powered by Cycloid Talent SAS
            </td>
        </tr>
    </table>
</body>
</html>
