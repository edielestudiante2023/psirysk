<?= $this->extend('layouts/auth') ?>

<?= $this->section('styles') ?>
.password-requirements {
    font-size: 0.85rem;
    color: #666;
    margin-top: 5px;
}
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="text-center mb-4">
    <i class="fas fa-lock-open fa-3x text-primary mb-3"></i>
    <h5>Nueva Contrasena</h5>
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

<div class="alert alert-success">
    <i class="fas fa-check-circle me-2"></i>
    Ingresa tu nueva contrasena para restablecer el acceso a tu cuenta.
</div>

<form action="<?= base_url('password-reset/update') ?>" method="POST" id="resetForm">
    <?= csrf_field() ?>
    <input type="hidden" name="token" value="<?= esc($token) ?>">
    <input type="hidden" name="email" value="<?= esc($email) ?>">

    <div class="mb-3">
        <label for="email_display" class="form-label">Correo Electronico</label>
        <input type="text" class="form-control" value="<?= esc($email) ?>" disabled>
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">Nueva Contrasena</label>
        <div class="input-group">
            <span class="input-group-text">
                <i class="fas fa-lock"></i>
            </span>
            <input type="password" class="form-control" id="password" name="password"
                   placeholder="********" required minlength="8">
            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                <i class="fas fa-eye" id="eyeIcon"></i>
            </button>
        </div>
        <div class="password-requirements">
            <i class="fas fa-info-circle me-1"></i>
            Minimo 8 caracteres
        </div>
    </div>

    <div class="mb-3">
        <label for="password_confirm" class="form-label">Confirmar Nueva Contrasena</label>
        <div class="input-group">
            <span class="input-group-text">
                <i class="fas fa-lock"></i>
            </span>
            <input type="password" class="form-control" id="password_confirm" name="password_confirm"
                   placeholder="********" required minlength="8">
            <button class="btn btn-outline-secondary" type="button" id="togglePasswordConfirm">
                <i class="fas fa-eye" id="eyeIconConfirm"></i>
            </button>
        </div>
    </div>

    <div id="passwordMatch" class="alert alert-warning d-none">
        <i class="fas fa-exclamation-triangle me-2"></i>
        Las contrasenas no coinciden
    </div>

    <button type="submit" class="btn btn-primary w-100" id="submitBtn">
        <i class="fas fa-check me-2"></i>Restablecer Contrasena
    </button>
</form>

<div class="text-center mt-3">
    <a href="<?= base_url('login') ?>" class="text-decoration-none">
        <i class="fas fa-arrow-left me-2"></i>Volver al inicio de sesion
    </a>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.getElementById('togglePassword').addEventListener('click', function() {
        const password = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');
        if (password.type === 'password') {
            password.type = 'text';
            eyeIcon.classList.remove('fa-eye');
            eyeIcon.classList.add('fa-eye-slash');
        } else {
            password.type = 'password';
            eyeIcon.classList.remove('fa-eye-slash');
            eyeIcon.classList.add('fa-eye');
        }
    });

    document.getElementById('togglePasswordConfirm').addEventListener('click', function() {
        const passwordConfirm = document.getElementById('password_confirm');
        const eyeIconConfirm = document.getElementById('eyeIconConfirm');
        if (passwordConfirm.type === 'password') {
            passwordConfirm.type = 'text';
            eyeIconConfirm.classList.remove('fa-eye');
            eyeIconConfirm.classList.add('fa-eye-slash');
        } else {
            passwordConfirm.type = 'password';
            eyeIconConfirm.classList.remove('fa-eye-slash');
            eyeIconConfirm.classList.add('fa-eye');
        }
    });

    const password = document.getElementById('password');
    const passwordConfirm = document.getElementById('password_confirm');
    const passwordMatch = document.getElementById('passwordMatch');
    const submitBtn = document.getElementById('submitBtn');

    function checkPasswordMatch() {
        if (password.value && passwordConfirm.value) {
            if (password.value !== passwordConfirm.value) {
                passwordMatch.classList.remove('d-none');
                submitBtn.disabled = true;
            } else {
                passwordMatch.classList.add('d-none');
                submitBtn.disabled = false;
            }
        }
    }

    password.addEventListener('input', checkPasswordMatch);
    passwordConfirm.addEventListener('input', checkPasswordMatch);
</script>
<?= $this->endSection() ?>
