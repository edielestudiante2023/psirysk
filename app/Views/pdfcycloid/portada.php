<div class="pdf-page portada">
    <?php
    // Función helper para obtener imagen como base64
    if (!function_exists('getLogoSrc')) {
        function getLogoSrc($filename) {
            $logoPath = FCPATH . 'images/logos/' . $filename;
            if (file_exists($logoPath)) {
                $imageData = base64_encode(file_get_contents($logoPath));
                $mimeType = mime_content_type($logoPath);
                return 'data:' . $mimeType . ';base64,' . $imageData;
            }
            return false;
        }
    }

    // Logos superiores
    $logoCycloid = getLogoSrc('cycloidgrissinfondo.png');
    $logoRps = getLogoSrc('logo_rps.png');
    ?>

    <!-- Header con logos -->
    <div class="pdf-header" style="border-bottom: none; margin-bottom: 30px; text-align: center;">
        <?php if ($logoCycloid): ?>
            <img src="<?= $logoCycloid ?>" alt="Cycloid" style="height: 70px; width: auto; margin-right: 40px; vertical-align: middle;">
        <?php endif; ?>
        <?php if ($logoRps): ?>
            <img src="<?= $logoRps ?>" alt="RPS" style="height: 90px; width: auto; vertical-align: middle;">
        <?php endif; ?>
    </div>

    <!-- Título principal -->
    <div class="portada-title">
        <?= esc($reportTitle) ?>
    </div>

    <!-- Nombre de la empresa -->
    <div class="portada-company-label">
        NOMBRE DE LA EMPRESA
    </div>
    <div class="portada-company-name">
        <?= esc($company['company_name'] ?? 'EMPRESA') ?>
    </div>

    <!-- Logo de la empresa -->
    <?php if (!empty($company['logo_path'])):
        $companyLogoPath = FCPATH . $company['logo_path'];
        if (file_exists($companyLogoPath)):
            $companyLogoData = base64_encode(file_get_contents($companyLogoPath));
            $companyLogoMime = mime_content_type($companyLogoPath);
    ?>
    <div class="portada-company-logo" style="margin: 15px 0 25px 0; text-align: center;">
        <img src="data:<?= $companyLogoMime ?>;base64,<?= $companyLogoData ?>" alt="Logo Empresa" style="max-height: 80px; max-width: 200px;">
    </div>
    <?php endif; endif; ?>

    <!-- Asesorado por -->
    <div class="portada-consultant-label">
        ASESORADO POR
    </div>
    <div class="portada-consultant-name">
        <?= esc($consultant['name'] ?? 'CONSULTOR') ?>
    </div>
    <div class="portada-consultant-title">
        <?= esc($consultant['position'] ?? 'Psicólogo Especialista SST') ?>
    </div>
    <?php if (!empty($consultant['licencia_sst'])): ?>
    <div class="portada-consultant-license">
        <?= esc($consultant['licencia_sst']) ?>
    </div>
    <?php endif; ?>

    <!-- Informe interactivo (opcional) -->
    <?php if (!empty($interactiveUrl)): ?>
    <div style="margin: 20px 0;">
        <strong>INFORME INTERACTIVO</strong><br>
        <a href="<?= $interactiveUrl ?>" style="color: #0066cc;"><?= $interactiveUrl ?></a>
    </div>
    <?php endif; ?>

    <!-- Fecha y ciudad -->
    <div class="portada-date">
        <?= esc($city) ?>, <?= esc($applicationDate) ?>
    </div>
</div>
