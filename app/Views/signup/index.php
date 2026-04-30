<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear cuenta · psyrisk</title>
    <link rel="icon" type="image/x-icon" href="<?= base_url('favicon.ico') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .signup-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 15px 50px rgba(0,0,0,0.25);
        }
        .plan-card {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            cursor: pointer;
            transition: all .2s;
            background: #fff;
        }
        .plan-card:has(input:checked) {
            border-color: #667eea;
            background: #f0f7ff;
            box-shadow: 0 4px 12px rgba(102,126,234,0.15);
        }
        .plan-card .form-check-input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }
        .nav-top {
            background: rgba(255,255,255,0.95);
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>

<nav class="navbar nav-top sticky-top">
    <div class="container">
        <a class="navbar-brand" href="<?= base_url('/') ?>">
            <img src="<?= base_url('images/logos/logo_psyrisk.png') ?>" alt="psyrisk" style="height: 36px;">
        </a>
        <div>
            <a href="<?= base_url('/') ?>" class="btn btn-link btn-sm">← Volver</a>
            <a href="<?= base_url('login') ?>" class="btn btn-outline-primary btn-sm">Ya tengo cuenta</a>
        </div>
    </div>
</nav>

<div class="container py-4 py-md-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="signup-card p-4 p-md-5">
                <div class="text-center mb-4">
                    <h2 class="mb-2">Crea tu cuenta de psyrisk</h2>
                    <p class="text-muted mb-0">Plataforma white-label de Bateria de Riesgo Psicosocial. <strong>14 dias de prueba gratis.</strong></p>
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

                    <h5 class="mt-2">Datos de la consultora / psicologo</h5>
                    <hr class="mt-1 mb-3">

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Razon social *</label>
                            <input type="text" name="legal_name" class="form-control" required value="<?= old('legal_name') ?>" placeholder="Ej. Pablo Garcia Psicologo SAS">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">NIT *</label>
                            <input type="text" name="nit" class="form-control" required value="<?= old('nit') ?>" placeholder="900123456">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Nombre comercial (opcional)</label>
                            <input type="text" name="trade_name" class="form-control" value="<?= old('trade_name') ?>" placeholder="Si es distinto a la razon social">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nombre de contacto *</label>
                            <input type="text" name="contact_name" class="form-control" required value="<?= old('contact_name') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Telefono</label>
                            <input type="text" name="contact_phone" class="form-control" value="<?= old('contact_phone') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Email *</label>
                            <input type="email" name="contact_email" class="form-control" required value="<?= old('contact_email') ?>" placeholder="contacto@miconsultora.com">
                            <div class="form-text">Sera tu usuario de login y recibira el enlace de verificacion.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contrasena *</label>
                            <input type="password" name="password" class="form-control" required minlength="8" placeholder="Minimo 8 caracteres">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirmar contrasena *</label>
                            <input type="password" name="password_confirm" class="form-control" required minlength="8">
                        </div>
                    </div>

                    <h5 class="mt-4">Selecciona tu plan</h5>
                    <hr class="mt-1 mb-3">

                    <div class="row g-3">
                        <?php foreach ($plans as $key => $plan): ?>
                            <div class="col-md-4">
                                <label class="card h-100 plan-card p-3 mb-0">
                                    <input type="radio" name="plan" value="<?= $key ?>" <?= old('plan') === $key || (!old('plan') && $key === 'inicial') ? 'checked' : '' ?>>
                                    <div class="text-center">
                                        <h6 class="card-title mb-2"><?= $plan['label'] ?></h6>
                                        <h4 class="mb-1">$<?= number_format($plan['monthly_fee'], 0, ',', '.') ?></h4>
                                        <p class="text-muted small mb-2">/mes</p>
                                        <p class="mb-1"><strong><?= $plan['description'] ?></strong></p>
                                        <p class="text-muted small mb-0">Credito extra: $<?= number_format($plan['extra_credit'], 0, ',', '.') ?></p>
                                    </div>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="form-check mt-4">
                        <input type="checkbox" name="accept_terms" id="accept_terms" class="form-check-input" required>
                        <label for="accept_terms" class="form-check-label">
                            Acepto la <a href="<?= base_url('legal/politica') ?>" target="_blank">Politica de Tratamiento de Datos</a> y los <a href="<?= base_url('legal/terminos') ?>" target="_blank">Terminos de Servicio</a> de psyrisk.
                        </label>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">Crear mi cuenta gratis</button>
                    </div>

                    <p class="text-center mt-3 mb-0 text-muted">
                        <small>Sin tarjeta de credito · Cancelas cuando quieras</small>
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
