<?= $this->extend('layouts/auth') ?>
<?= $this->section('content') ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm text-center">
                <div class="card-body p-5">
                    <div style="font-size: 4rem; color: #dc3545;">⚠️</div>
                    <h3 class="mt-3">Enlace inválido o expirado</h3>
                    <p>El enlace de verificación ya fue usado o expiró. Por favor regístrate de nuevo.</p>
                    <a href="<?= base_url('signup') ?>" class="btn btn-primary">Volver a registrarme</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
