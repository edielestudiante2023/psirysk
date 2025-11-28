<div class="pdf-page firma-page">
    <?= view('pdf/_partials/header', ['company' => $company, 'consultant' => $consultant]) ?>

    <div class="pdf-content">
        <!-- Contenido previo si existe -->
        <?php if (!empty($contenidoPrevio)): ?>
        <div style="margin-bottom: 40px;">
            <?= $contenidoPrevio ?>
        </div>
        <?php endif; ?>

        <!-- Despedida -->
        <div style="margin-top: 60px;">
            <p style="font-size: 11pt; color: #333;">Cordialmente,</p>
        </div>

        <!-- Firma -->
        <div style="margin-top: 40px;">
            <?php if (!empty($consultant['signature_path'])): ?>
            <div style="margin-bottom: 10px;">
                <img src="<?= base_url($consultant['signature_path']) ?>" alt="Firma" style="max-height: 80px; max-width: 200px;">
            </div>
            <?php else: ?>
            <div style="margin-bottom: 10px; height: 60px; width: 200px;"></div>
            <?php endif; ?>

            <!-- Datos del consultor -->
            <div style="font-size: 11pt;">
                <p style="margin: 0; font-weight: bold; color: #0077B6;">
                    <?= esc($consultant['name'] ?? 'Consultor') ?>
                </p>
                <p style="margin: 3px 0; color: #555;">
                    <?= esc($consultant['position'] ?? 'Especialista en Seguridad y Salud en el Trabajo') ?>
                </p>
                <?php if (!empty($consultant['licencia_sst'])): ?>
                <p style="margin: 3px 0; color: #555; font-size: 10pt;">
                    Licencia SST: <?= esc($consultant['licencia_sst']) ?>
                </p>
                <?php endif; ?>
            </div>

            <!-- Información de contacto -->
            <div style="margin-top: 15px; font-size: 10pt;">
                <?php if (!empty($consultant['email'])): ?>
                <p style="margin: 3px 0;">
                    <a href="mailto:<?= esc($consultant['email']) ?>" style="color: #0077B6; text-decoration: none;">
                        <?= esc($consultant['email']) ?>
                    </a>
                </p>
                <?php endif; ?>
                <?php if (!empty($consultant['website'])): ?>
                <p style="margin: 3px 0;">
                    <a href="<?= esc($consultant['website']) ?>" style="color: #0077B6; text-decoration: none;">
                        <?= esc($consultant['website']) ?>
                    </a>
                </p>
                <?php endif; ?>
                <?php if (!empty($consultant['linkedin'])): ?>
                <p style="margin: 3px 0;">
                    <a href="<?= esc($consultant['linkedin']) ?>" style="color: #0077B6; text-decoration: none;">
                        <?= esc($consultant['linkedin']) ?>
                    </a>
                </p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?= view('pdf/_partials/footer') ?>
</div>
