<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>
<form action="<?= base_url('login') ?>" method="POST">
    <?= csrf_field() ?>

    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email"
               class="form-control"
               id="email"
               name="email"
               placeholder="tu@email.com"
               value="<?= old('email') ?>"
               required>
    </div>

    <div class="mb-4">
        <label for="password" class="form-label">Contrasena</label>
        <div class="input-group">
            <input type="password"
                   class="form-control"
                   id="password"
                   name="password"
                   placeholder="********"
                   required>
            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                <i class="fas fa-eye" id="eyeIcon"></i>
            </button>
        </div>
    </div>

    <div class="d-grid">
        <button type="submit" class="btn btn-primary btn-lg">
            Iniciar Sesion
        </button>
    </div>

    <div class="text-center mt-3">
        <a href="<?= base_url('forgot-password') ?>" class="text-decoration-none">
            <i class="fas fa-question-circle me-1"></i>Olvidaste tu contrasena?
        </a>
    </div>
</form>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.getElementById('togglePassword').addEventListener('click', function() {
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.classList.remove('fa-eye');
            eyeIcon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            eyeIcon.classList.remove('fa-eye-slash');
            eyeIcon.classList.add('fa-eye');
        }
    });
</script>
<?= $this->endSection() ?>
