<?= $this->extend('layouts/auth') ?>
<?= $this->section('content') ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm text-center">
                <div class="card-body p-5">
                    <h3 class="text-danger">Error en la activación</h3>
                    <p><?= esc($message ?? 'Algo salió mal.') ?></p>
                    <a href="<?= base_url('login') ?>" class="btn btn-link">Volver a login</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
