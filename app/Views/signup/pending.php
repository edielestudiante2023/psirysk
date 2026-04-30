<?= $this->extend('layouts/auth') ?>
<?= $this->section('content') ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm text-center">
                <div class="card-body p-5">
                    <div style="font-size: 4rem; color: #0066CC;">📧</div>
                    <h3 class="mt-3">Revisa tu correo</h3>
                    <p>Te enviamos un enlace de verificación<?= $email ? ' a <strong>'.esc($email).'</strong>' : '' ?>.</p>
                    <p class="text-muted small">El enlace vence en 24 horas. Si no lo ves, revisa tu carpeta de spam.</p>
                    <a href="<?= base_url('login') ?>" class="btn btn-link">Volver a login</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
