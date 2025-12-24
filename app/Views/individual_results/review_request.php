<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - PsyRisk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">

                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-file-alt me-2"></i>
                            Revisión de Solicitud #<?= $request['id'] ?>
                        </h4>
                    </div>
                    <div class="card-body">

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <h6 class="text-primary"><i class="fas fa-building me-2"></i>Información del Servicio</h6>
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <td class="text-muted" style="width: 40%;">Empresa:</td>
                                            <td><strong><?= esc($request['company_name']) ?></strong></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Servicio:</td>
                                            <td><?= esc($request['service_name']) ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <h6 class="text-primary"><i class="fas fa-user me-2"></i>Trabajador</h6>
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <td class="text-muted" style="width: 40%;">Nombre:</td>
                                            <td><strong><?= esc($request['worker_first_name'] . ' ' . $request['worker_last_name']) ?></strong></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Documento:</td>
                                            <td><?= esc($request['worker_document']) ?></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Tipo:</td>
                                            <td>
                                                <?php
                                                    $types = [
                                                        'intralaboral_a' => 'Intralaboral Forma A',
                                                        'intralaboral_b' => 'Intralaboral Forma B',
                                                        'extralaboral' => 'Extralaboral',
                                                        'estres' => 'Estrés'
                                                    ];
                                                    echo $types[$request['request_type']] ?? $request['request_type'];
                                                ?>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <h6 class="text-primary"><i class="fas fa-user-tie me-2"></i>Solicitante</h6>
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <td class="text-muted" style="width: 40%;">Nombre:</td>
                                            <td><strong><?= esc($request['requester_first_name'] . ' ' . $request['requester_last_name']) ?></strong></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Email:</td>
                                            <td><?= esc($request['requester_email']) ?></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Fecha:</td>
                                            <td><?= date('d/m/Y H:i', strtotime($request['created_at'])) ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <h6 class="text-primary"><i class="fas fa-info-circle me-2"></i>Información Técnica</h6>
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <td class="text-muted" style="width: 40%;">IP:</td>
                                            <td><?= esc($request['ip_address'] ?? 'N/A') ?></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Tiempo:</td>
                                            <td>
                                                <?php
                                                    $hoursSince = (time() - strtotime($request['created_at'])) / 3600;
                                                    echo $hoursSince < 1 ? round($hoursSince * 60) . ' minutos' : round($hoursSince) . ' horas';
                                                ?>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6 class="text-primary mb-2">
                                <i class="fas fa-comment-alt me-2"></i>Motivación de la Solicitud
                            </h6>
                            <div class="border rounded p-3 bg-light">
                                <?= nl2br(esc($request['motivation'])) ?>
                            </div>
                            <small class="text-muted">
                                <?= strlen($request['motivation']) ?> caracteres
                            </small>
                        </div>

                        <hr class="my-4">

                        <h5 class="mb-3"><i class="fas fa-gavel me-2"></i>Decisión del Consultor</h5>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="card border-success">
                                    <div class="card-header bg-success text-white">
                                        <i class="fas fa-check-circle me-2"></i>Aprobar Solicitud
                                    </div>
                                    <div class="card-body">
                                        <form action="<?= base_url("individual-results/approve/{$request['id']}") ?>" method="POST" id="approveForm">
                                            <?= csrf_field() ?>

                                            <div class="mb-3">
                                                <label for="access_hours" class="form-label">Duración del acceso</label>
                                                <select class="form-select" id="access_hours" name="access_hours">
                                                    <option value="24">24 horas</option>
                                                    <option value="48" selected>48 horas (recomendado)</option>
                                                    <option value="72">72 horas</option>
                                                    <option value="168">7 días</option>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label for="approve_notes" class="form-label">Notas (opcional)</label>
                                                <textarea class="form-control" id="approve_notes" name="review_notes" rows="3"
                                                    placeholder="Ej: Aprobado por situación médica justificada"></textarea>
                                            </div>

                                            <button type="submit" class="btn btn-success w-100">
                                                <i class="fas fa-check me-2"></i>Aprobar y Notificar
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="card border-danger">
                                    <div class="card-header bg-danger text-white">
                                        <i class="fas fa-times-circle me-2"></i>Rechazar Solicitud
                                    </div>
                                    <div class="card-body">
                                        <form action="<?= base_url("individual-results/reject/{$request['id']}") ?>" method="POST" id="rejectForm">
                                            <?= csrf_field() ?>

                                            <div class="mb-3">
                                                <label for="reject_notes" class="form-label">
                                                    Motivo del rechazo <span class="text-danger">*</span>
                                                </label>
                                                <textarea class="form-control" id="reject_notes" name="review_notes" rows="5" required
                                                    placeholder="Explique por qué se rechaza la solicitud. Esta información será enviada al solicitante."></textarea>
                                                <small class="text-muted">Mínimo 20 caracteres</small>
                                            </div>

                                            <button type="submit" class="btn btn-danger w-100" id="rejectBtn" disabled>
                                                <i class="fas fa-times me-2"></i>Rechazar y Notificar
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <a href="<?= base_url('individual-results/management') ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Volver a Solicitudes
                            </a>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validate reject form
        const rejectNotes = document.getElementById('reject_notes');
        const rejectBtn = document.getElementById('rejectBtn');

        rejectNotes.addEventListener('input', function() {
            rejectBtn.disabled = this.value.trim().length < 20;
        });

        // Confirmation dialogs
        document.getElementById('approveForm').addEventListener('submit', function(e) {
            if (!confirm('¿Está seguro de aprobar esta solicitud? El solicitante recibirá acceso inmediato.')) {
                e.preventDefault();
            }
        });

        document.getElementById('rejectForm').addEventListener('submit', function(e) {
            if (!confirm('¿Está seguro de rechazar esta solicitud? El solicitante será notificado.')) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
