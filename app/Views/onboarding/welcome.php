<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body p-5 text-center">
                    <div style="font-size: 4rem;">🎉</div>
                    <h2 class="mt-3">¡Bienvenido a psyrisk, <?= esc($tenant['contact_name'] ?? '') ?>!</h2>
                    <p class="text-muted">Tu cuenta <strong><?= esc($tenant['legal_name']) ?></strong> está activa con un periodo de prueba de 14 días.</p>
                    <hr>
                    <h5>Próximos pasos:</h5>
                    <ol class="text-start mx-auto" style="max-width: 400px;">
                        <li>Sube tu logo y configura tu marca (white-label).</li>
                        <li>Crea tu primera empresa cliente.</li>
                        <li>Lanza tu primera evaluación.</li>
                    </ol>
                    <a href="<?= base_url('onboarding/branding') ?>" class="btn btn-primary btn-lg mt-3">Configurar mi marca →</a>
                    <a href="<?= base_url('dashboard') ?>" class="btn btn-link mt-3 d-block">Saltar e ir al dashboard</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
