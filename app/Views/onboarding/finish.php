<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-sm text-center">
                <div class="card-body p-5">
                    <div style="font-size: 4rem;">✅</div>
                    <h3 class="mt-3">¡Todo listo!</h3>
                    <p class="text-muted">Tu marca está configurada. Ya puedes empezar a usar psyrisk.</p>
                    <a href="<?= base_url('dashboard') ?>" class="btn btn-primary btn-lg mt-3">Ir al dashboard →</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
