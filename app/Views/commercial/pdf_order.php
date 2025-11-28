<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Orden de Servicio #<?= $service['id'] ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
        }
        .header {
            margin-bottom: 30px;
            border-bottom: 3px solid #e74c3c;
            padding-bottom: 20px;
            text-align: center;
            position: relative;
        }
        .logo-left {
            position: absolute;
            left: 0;
            top: 0;
            height: 60px;
        }
        .logo-right {
            position: absolute;
            right: 0;
            top: 0;
            height: 60px;
        }
        .header h1 {
            color: #e74c3c;
            margin: 0;
            font-size: 28px;
            letter-spacing: 2px;
            padding-top: 15px;
        }
        .header .subtitle {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            background: #e74c3c;
            color: white;
            padding: 8px 15px;
            font-weight: bold;
            margin-bottom: 15px;
            font-size: 14px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        .info-table td:first-child {
            background: #f8f9fa;
            font-weight: bold;
            width: 35%;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 11px;
        }
        .status-planificado { background: #3498db; color: white; }
        .status-en_curso { background: #f39c12; color: white; }
        .status-finalizado { background: #27ae60; color: white; }
        .checkbox-list {
            list-style: none;
            padding: 0;
        }
        .checkbox-list li {
            margin-bottom: 8px;
        }
        .checkbox-list .checked {
            color: #27ae60;
            font-weight: bold;
        }
        .checkbox-list .unchecked {
            color: #ccc;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 2px solid #ddd;
            text-align: center;
            color: #666;
            font-size: 10px;
        }
        .signature-box {
            margin-top: 50px;
            border: 1px solid #ddd;
            padding: 15px;
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php
    // Logos para PDF - usar URLs completas
    $logoCycloid = base_url('assets/images/logo_gris.jpeg');
    $logoRPS = base_url('assets/images/rps.png');

    log_message('debug', "Logo Cycloid URL: {$logoCycloid}");
    log_message('debug', "Logo RPS URL: {$logoRPS}");
    ?>
    <div class="header">
        <img src="<?= $logoCycloid ?>" alt="Cycloid Talent" class="logo-left">
        <img src="<?= $logoRPS ?>" alt="Batería RPS" class="logo-right">

        <h1>CYCLOID TALENT</h1>
        <div class="subtitle">Equipo Gladiator - Área Comercial</div>
        <div class="subtitle">Batería de Riesgo Psicosocial</div>
        <h2 style="color: #e74c3c; margin: 15px 0 5px 0;">ORDEN DE SERVICIO</h2>
        <div style="font-size: 16px; color: #666;">N° <?= str_pad($service['id'], 6, '0', STR_PAD_LEFT) ?></div>
    </div>

    <!-- Información del Cliente -->
    <div class="section">
        <div class="section-title">INFORMACIÓN DEL CLIENTE</div>
        <table class="info-table">
            <tr>
                <td>Razón Social:</td>
                <td><?= esc($service['company_name']) ?></td>
            </tr>
            <tr>
                <td>NIT:</td>
                <td><?= esc($service['company_nit']) ?></td>
            </tr>
            <tr>
                <td>Dirección:</td>
                <td><?= esc($service['company_address'] ?? 'No registrada') ?></td>
            </tr>
        </table>
    </div>

    <!-- Información del Servicio -->
    <div class="section">
        <div class="section-title">INFORMACIÓN DEL SERVICIO</div>
        <table class="info-table">
            <tr>
                <td>Nombre del Servicio:</td>
                <td><?= esc($service['service_name']) ?></td>
            </tr>
            <tr>
                <td>Fecha de Servicio:</td>
                <td><?= date('d/m/Y', strtotime($service['service_date'])) ?></td>
            </tr>
            <tr>
                <td>Fecha de Expiración:</td>
                <td><?= date('d/m/Y', strtotime($service['link_expiration_date'])) ?></td>
            </tr>
            <tr>
                <td>Estado:</td>
                <td>
                    <span class="status-badge status-<?= $service['status'] ?>">
                        <?= $service['status'] === 'planificado' ? 'ABIERTO' : ($service['status'] === 'finalizado' ? 'CERRADO' : 'EN CURSO') ?>
                    </span>
                </td>
            </tr>
        </table>
    </div>

    <!-- Consultor Asignado -->
    <div class="section">
        <div class="section-title">CONSULTOR ASIGNADO</div>
        <table class="info-table">
            <tr>
                <td>Nombre:</td>
                <td><?= esc($service['consultant_name']) ?></td>
            </tr>
            <tr>
                <td>Email:</td>
                <td><?= esc($service['consultant_email']) ?></td>
            </tr>
        </table>
    </div>

    <!-- Cuestionarios Incluidos -->
    <div class="section">
        <div class="section-title">CUESTIONARIOS INCLUIDOS</div>
        <ul class="checkbox-list">
            <li class="<?= $service['includes_intralaboral'] ? 'checked' : 'unchecked' ?>">
                [<?= $service['includes_intralaboral'] ? 'X' : ' ' ?>] Cuestionario de Factores de Riesgo Psicosocial Intralaboral (Forma A y B)
            </li>
            <li class="<?= $service['includes_extralaboral'] ? 'checked' : 'unchecked' ?>">
                [<?= $service['includes_extralaboral'] ? 'X' : ' ' ?>] Cuestionario de Factores de Riesgo Psicosocial Extralaboral
            </li>
            <li class="<?= $service['includes_estres'] ? 'checked' : 'unchecked' ?>">
                [<?= $service['includes_estres'] ? 'X' : ' ' ?>] Cuestionario para la Evaluacion del Estres
            </li>
        </ul>
    </div>

    <!-- Cantidades por Tipo de Formulario -->
    <div class="section">
        <div class="section-title">DISTRIBUCIÓN DE UNIDADES</div>
        <table class="info-table">
            <tr>
                <td>Cantidad Forma A (Jefes/Profesionales):</td>
                <td style="text-align: right; font-weight: bold; font-size: 14px;"><?= $service['cantidad_forma_a'] ?? 0 ?> unidades</td>
            </tr>
            <tr>
                <td>Cantidad Forma B (Auxiliares/Operarios):</td>
                <td style="text-align: right; font-weight: bold; font-size: 14px;"><?= $service['cantidad_forma_b'] ?? 0 ?> unidades</td>
            </tr>
            <tr style="background: #f8f9fa;">
                <td style="font-weight: bold; font-size: 14px;">TOTAL DE UNIDADES:</td>
                <td style="text-align: right; font-weight: bold; font-size: 16px; color: #e74c3c;">
                    <?= ($service['cantidad_forma_a'] ?? 0) + ($service['cantidad_forma_b'] ?? 0) ?> unidades
                </td>
            </tr>
        </table>
    </div>

    <!-- Firma -->
    <div class="signature-box">
        <p style="margin: 0; font-weight: bold;">GENERADO POR:</p>
        <p style="margin: 5px 0;">Diana Patricia Cuestas Navia</p>
        <p style="margin: 5px 0; color: #666;">Directora Comercial - Equipo Gladiator</p>
        <p style="margin: 5px 0; font-size: 11px;">Fecha de generación: <?= date('d/m/Y H:i:s') ?></p>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Cycloid Talent SAS | www.cycloidtalent.com</p>
        <p>Batería de Riesgo Psicosocial - Ministerio de la Protección Social de Colombia</p>
    </div>
</body>
</html>
