<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Batería de Riesgo Psicosocial - <?= esc($service['company_name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 460px;
            width: 100%;
        }
        .card-header {
            border-radius: 20px 20px 0 0 !important;
            background: linear-gradient(135deg, #1a365d 0%, #2c5282 100%);
            padding: 2rem;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
        }
        .btn-ingresar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 30px;
            font-size: 1.1rem;
            transition: all 0.3s;
        }
        .btn-ingresar:hover {
            background: linear-gradient(135deg, #5a6fd6 0%, #6a4190 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        .icon-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #e8edfb 0%, #d6dff8 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="container px-3">
        <div class="card mx-auto">
            <div class="card-header text-white text-center">
                <h5 class="mb-1 fw-bold"><?= esc($service['company_name']) ?></h5>
                <p class="mb-0 opacity-75 small">Batería de Riesgo Psicosocial — <?= esc($service['service_name']) ?></p>
            </div>
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <div class="icon-circle mb-3">
                        <i class="bi bi-person-badge text-primary" style="font-size: 2.5rem;"></i>
                    </div>
                    <h5 class="mb-1">Acceso a la Evaluación</h5>
                    <p class="text-muted small">Ingresa tu número de documento de identidad para comenzar</p>
                </div>

                <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i><?= session()->getFlashdata('error') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <form action="<?= base_url('bateria/validar') ?>" method="post">
                    <input type="hidden" name="enlace" value="<?= esc($enlace) ?>">

                    <div class="mb-4">
                        <label for="documento" class="form-label fw-semibold">
                            <i class="bi bi-card-text me-1"></i>Número de documento
                        </label>
                        <input type="text"
                               class="form-control form-control-lg"
                               id="documento"
                               name="documento"
                               placeholder="Ej: 1020304050"
                               required
                               autofocus
                               inputmode="numeric"
                               pattern="[0-9]+"
                               title="Solo números, sin puntos ni espacios">
                        <div class="form-text">Solo números, sin puntos ni espacios.</div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-ingresar btn-lg text-white">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar evaluación
                        </button>
                    </div>
                </form>

                <?php if (!empty($service['link_expiration_date'])): ?>
                <div class="mt-4 pt-3 border-top">
                    <div class="d-flex justify-content-between text-muted small">
                        <span><i class="bi bi-calendar me-1"></i>Enlace disponible hasta:</span>
                        <strong><?= date('d/m/Y', strtotime($service['link_expiration_date'])) ?></strong>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <div class="card-footer bg-light text-center py-3">
                <small class="text-muted">
                    <i class="bi bi-shield-lock me-1"></i>
                    Tus respuestas son confidenciales
                </small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
