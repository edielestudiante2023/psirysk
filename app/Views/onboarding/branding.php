<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h3>Configura tu marca</h3>
                    <p class="text-muted">Personaliza cómo se ve la plataforma para tus clientes (white-label).</p>

                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
                    <?php endif; ?>

                    <form method="post" action="<?= base_url('onboarding/branding') ?>" enctype="multipart/form-data">
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label class="form-label">Logo de tu consultora</label>
                            <input type="file" name="logo" class="form-control" accept="image/png,image/jpeg,image/webp">
                            <div class="form-text">PNG, JPG o WEBP. Máx 2MB. Recomendado: 300x100 px.</div>
                            <?php if (!empty($tenant['logo_path'])): ?>
                                <div class="mt-2"><img src="<?= base_url($tenant['logo_path']) ?>" style="max-height:60px;"> <small class="text-muted">(actual)</small></div>
                            <?php endif; ?>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Color primario</label>
                                <input type="color" name="brand_primary_color" class="form-control form-control-color" value="<?= $tenant['brand_primary_color'] ?? '#0066CC' ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Color secundario</label>
                                <input type="color" name="brand_secondary_color" class="form-control form-control-color" value="<?= $tenant['brand_secondary_color'] ?? '#003366' ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Sitio web</label>
                                <input type="url" name="website_url" class="form-control" value="<?= esc($tenant['website_url'] ?? '') ?>" placeholder="https://...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">LinkedIn</label>
                                <input type="url" name="linkedin_url" class="form-control" value="<?= esc($tenant['linkedin_url'] ?? '') ?>">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Dirección</label>
                                <input type="text" name="address" class="form-control" value="<?= esc($tenant['address'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Ciudad</label>
                                <input type="text" name="city" class="form-control" value="<?= esc($tenant['city'] ?? '') ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Texto al pie de tus PDFs</label>
                                <textarea name="pdf_footer_text" class="form-control" rows="2"><?= esc($tenant['pdf_footer_text'] ?? '') ?></textarea>
                                <div class="form-text">Aparece en todos los reportes generados (ej. "Generado por [Tu Consultora] · contacto@email.com").</div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="<?= base_url('dashboard') ?>" class="btn btn-link">Saltar</a>
                            <button type="submit" class="btn btn-primary">Guardar y continuar →</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
