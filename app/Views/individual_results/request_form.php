<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - PsyRisk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .info-card {
            background: #f8f9fa;
            border-left: 4px solid #0d6efd;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .warning-card {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .char-counter {
            font-size: 0.875rem;
            color: #6c757d;
        }
        .char-counter.warning {
            color: #ffc107;
        }
        .char-counter.danger {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-lock me-2"></i>
                            Solicitud de Acceso a Resultados Individuales
                        </h4>
                    </div>
                    <div class="card-body">

                        <div class="warning-card">
                            <h5><i class="fas fa-info-circle me-2"></i>Información Legal</h5>
                            <p class="mb-2">
                                Los resultados individuales de la Batería de Riesgo Psicosocial contienen
                                información sensible protegida por la normativa de protección de datos personales.
                            </p>
                            <p class="mb-0">
                                Para acceder a estos resultados, debe proporcionar una justificación válida
                                que será revisada por el consultor especialista de <strong>Cycloid Talent SAS</strong>.
                            </p>
                        </div>

                        <div class="info-card">
                            <h6><i class="fas fa-user me-2"></i>Trabajador</h6>
                            <p class="mb-1">
                                <strong>Nombre:</strong> <?= esc($worker['name']) ?>
                            </p>
                            <p class="mb-1">
                                <strong>Documento:</strong> <?= esc($worker['document']) ?>
                            </p>
                            <p class="mb-0">
                                <strong>Tipo de Evaluación:</strong>
                                <?php
                                    $types = [
                                        'intralaboral_a' => 'Intralaboral Forma A',
                                        'intralaboral_b' => 'Intralaboral Forma B',
                                        'extralaboral' => 'Extralaboral',
                                        'estres' => 'Estrés'
                                    ];
                                    echo esc($types[$requestType] ?? $requestType);
                                ?>
                            </p>
                        </div>

                        <?php if (session()->getFlashdata('errors')): ?>
                            <div class="alert alert-danger">
                                <h6>Por favor corrija los siguientes errores:</h6>
                                <ul class="mb-0">
                                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                        <li><?= esc($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form action="<?= base_url('individual-results/submit') ?>" method="POST" id="requestForm">
                            <?= csrf_field() ?>
                            <input type="hidden" name="service_id" value="<?= esc($service['id']) ?>">
                            <input type="hidden" name="worker_id" value="<?= esc($worker['id']) ?>">
                            <input type="hidden" name="request_type" value="<?= esc($requestType) ?>">

                            <div class="mb-4">
                                <label for="motivation" class="form-label">
                                    <strong>Motivación de la Solicitud</strong>
                                    <span class="text-danger">*</span>
                                </label>
                                <textarea
                                    class="form-control"
                                    id="motivation"
                                    name="motivation"
                                    rows="6"
                                    maxlength="2000"
                                    required
                                    placeholder="Explique detalladamente la razón por la cual requiere acceso a los resultados individuales de este trabajador. Por ejemplo: situación médica, proceso legal, requerimiento de ARL, etc. (mínimo 20 caracteres)"
                                ><?= old('motivation') ?></textarea>
                                <div class="d-flex justify-content-between mt-1">
                                    <small class="text-muted">Mínimo 20 caracteres, máximo 2000</small>
                                    <span id="charCounter" class="char-counter">0 / 2000</span>
                                </div>
                            </div>

                            <div class="alert alert-info">
                                <h6><i class="fas fa-clock me-2"></i>Proceso de Revisión</h6>
                                <ol class="mb-0">
                                    <li>Su solicitud será enviada al consultor especialista</li>
                                    <li>El consultor revisará la justificación proporcionada</li>
                                    <li>Recibirá una notificación por email con la decisión</li>
                                    <li>Si es aprobada, tendrá acceso temporal de 48 horas</li>
                                </ol>
                            </div>

                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="acceptTerms" required>
                                <label class="form-check-label" for="acceptTerms">
                                    Acepto que he leído y entiendo que el acceso a resultados individuales
                                    está sujeto a aprobación y que la información será utilizada únicamente
                                    para los fines declarados en la motivación.
                                </label>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                                <a href="<?= base_url("reports/intralaboral/{$service['id']}") ?>" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                                    <i class="fas fa-paper-plane me-2"></i>Enviar Solicitud
                                </button>
                            </div>
                        </form>

                    </div>
                </div>

                <div class="mt-4 text-center">
                    <p class="text-muted">
                        ¿Tiene dudas sobre este proceso?<br>
                        Contacte a su asesor de <a href="https://cycloidtalent.com/riesgo-psicosocial" target="_blank">Cycloid Talent SAS</a>
                    </p>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const motivationTextarea = document.getElementById('motivation');
        const charCounter = document.getElementById('charCounter');
        const acceptTerms = document.getElementById('acceptTerms');
        const submitBtn = document.getElementById('submitBtn');

        // Character counter
        motivationTextarea.addEventListener('input', function() {
            const length = this.value.length;
            charCounter.textContent = length + ' / 2000';

            if (length < 20) {
                charCounter.classList.add('danger');
                charCounter.classList.remove('warning');
            } else if (length > 1800) {
                charCounter.classList.add('warning');
                charCounter.classList.remove('danger');
            } else {
                charCounter.classList.remove('danger', 'warning');
            }

            checkFormValidity();
        });

        // Enable submit button only when form is valid
        acceptTerms.addEventListener('change', checkFormValidity);

        function checkFormValidity() {
            const motivationValid = motivationTextarea.value.length >= 20;
            const termsAccepted = acceptTerms.checked;

            submitBtn.disabled = !(motivationValid && termsAccepted);
        }

        // Prevent accidental form abandonment
        let formModified = false;
        motivationTextarea.addEventListener('input', function() {
            formModified = true;
        });

        window.addEventListener('beforeunload', function(e) {
            if (formModified && motivationTextarea.value.length > 0) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        document.getElementById('requestForm').addEventListener('submit', function() {
            formModified = false;
        });
    </script>
</body>
</html>
