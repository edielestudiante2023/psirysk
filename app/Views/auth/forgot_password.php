<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>
<div class="text-center mb-4">
    <i class="fas fa-key fa-3x text-primary mb-3"></i>
    <h5>Recuperar Contrasena</h5>
</div>

<?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <li><?= esc($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="alert alert-info">
    <i class="fas fa-info-circle me-2"></i>
    Ingresa tu correo electronico y te enviaremos un enlace para restablecer tu contrasena.
</div>

<form action="<?= base_url('password-reset/send') ?>" method="POST">
    <?= csrf_field() ?>

    <div class="mb-3">
        <label for="email" class="form-label">Correo Electronico</label>
        <div class="input-group">
            <span class="input-group-text">
                <i class="fas fa-envelope"></i>
            </span>
            <input type="email" class="form-control" id="email" name="email"
                   placeholder="tu@email.com" value="<?= old('email') ?>" required autofocus>
        </div>
    </div>

    <button type="submit" class="btn btn-primary w-100">
        <i class="fas fa-paper-plane me-2"></i>Enviar Enlace de Recuperacion
    </button>
</form>

<div class="text-center mt-3">
    <a href="<?= base_url('login') ?>" class="text-decoration-none">
        <i class="fas fa-arrow-left me-2"></i>Volver al inicio de sesion
    </a>
</div>
<?= $this->endSection() ?>
