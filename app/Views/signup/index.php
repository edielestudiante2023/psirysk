<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <img src="<?= base_url('images/logos/logo_psirysk.png') ?>" alt="psyrisk" style="max-height: 80px;">
                        <h2 class="mt-3 mb-1">Crea tu cuenta de psyrisk</h2>
                        <p class="text-muted">Plataforma white-label de Batería de Riesgo Psicosocial. 14 días de prueba gratis.</p>
                    </div>

                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
                    <?php endif; ?>
                    <?php if ($errors = session()->getFlashdata('errors')): ?>
                        <div class="alert alert-danger"><ul class="mb-0">
                            <?php foreach ($errors as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?>
                        </ul></div>
                    <?php endif; ?>

                    <form method="post" action="<?= base_url('signup') ?>" autocomplete="off">
                        <?= csrf_field() ?>
                        <h5 class="mt-2">Datos de la consultora / psicólogo</h5>
                        <hr class="mt-1 mb-3">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Razón social *</label>
                                <input type="text" name="legal_name" class="form-control" required value="<?= old('legal_name') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">NIT *</label>
                                <input type="text" name="nit" class="form-control" required value="<?= old('nit') ?>">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Nombre comercial (opcional)</label>
                                <input type="text" name="trade_name" class="form-control" value="<?= old('trade_name') ?>" placeholder="Si es distinto a la razón social">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nombre de contacto *</label>
                                <input type="text" name="contact_name" class="form-control" required value="<?= old('contact_name') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Teléfono</label>
                                <input type="text" name="contact_phone" class="form-control" value="<?= old('contact_phone') ?>">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Email *</label>
                                <input type="email" name="contact_email" class="form-control" required value="<?= old('contact_email') ?>">
                                <div class="form-text">Será tu usuario de login y recibirá el enlace de verificación.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contraseña *</label>
                                <input type="password" name="password" class="form-control" required minlength="8">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirmar contraseña *</label>
                                <input type="password" name="password_confirm" class="form-control" required minlength="8">
                            </div>
                        </div>

                        <h5 class="mt-4">Plan</h5>
                        <hr class="mt-1 mb-3">
                        <div class="row g-3">
                            <?php foreach ($plans as $key => $plan): ?>
                                <div class="col-md-4">
                                    <label class="card h-100 plan-card" style="cursor:pointer;">
                                        <input type="radio" name="plan" value="<?= $key ?>" <?= old('plan') === $key || (!old('plan') && $key === 'inicial') ? 'checked' : '' ?> class="form-check-input visually-hidden">
                                        <div class="card-body text-center">
                                            <h6 class="card-title"><?= $plan['label'] ?></h6>
                                            <h4>$<?= number_format($plan['monthly_fee'], 0, ',', '.') ?> <small class="text-muted">/mes</small></h4>
                                            <p class="text-muted small mb-0"><?= $plan['description'] ?></p>
                                            <p class="text-muted small">Crédito extra: $<?= number_format($plan['extra_credit'], 0, ',', '.') ?></p>
                                        </div>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="form-check mt-4">
                            <input type="checkbox" name="accept_terms" id="accept_terms" class="form-check-input" required>
                            <label for="accept_terms" class="form-check-label">
                                Acepto la <a href="<?= base_url('legal/politica') ?>" target="_blank">Política de Tratamiento de Datos</a> y los <a href="<?= base_url('legal/terminos') ?>" target="_blank">Términos de Servicio</a> de psyrisk.
                            </label>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">Crear cuenta</button>
                        </div>
                        <p class="text-center mt-3 mb-0">¿Ya tienes cuenta? <a href="<?= base_url('login') ?>">Inicia sesión</a></p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    .plan-card { border: 2px solid transparent; transition: all .2s; }
    .plan-card:has(input:checked) { border-color: #0066CC; background: #f0f7ff; }
</style>
<?= $this->endSection() ?>
