<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - PsyRisk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .request-card {
            transition: all 0.3s;
            border-left: 4px solid #ffc107;
        }
        .request-card:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }
        .urgent-badge {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }
    </style>
</head>
<body>
    <div class="container my-5">

        <div class="row mb-4">
            <div class="col">
                <h2><i class="fas fa-tasks me-2"></i><?= $title ?></h2>
                <p class="text-muted">Revise y gestione las solicitudes de acceso a resultados individuales</p>
            </div>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>
                <?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-warning text-dark">
                    <div class="card-body text-center">
                        <h1 class="display-4"><?= count($pendingRequests) ?></h1>
                        <p class="mb-0"><i class="fas fa-clock me-2"></i>Solicitudes Pendientes</p>
                    </div>
                </div>
            </div>
        </div>

        <?php if (empty($pendingRequests)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                No hay solicitudes pendientes de revisión en este momento.
            </div>
        <?php else: ?>

            <div class="row">
                <?php foreach ($pendingRequests as $request): ?>
                    <?php
                        $createdTime = strtotime($request['created_at']);
                        $hoursSince = (time() - $createdTime) / 3600;
                        $isUrgent = $hoursSince > 24;
                    ?>

                    <div class="col-lg-6 mb-4">
                        <div class="card request-card h-100">
                            <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                                <span>
                                    <i class="fas fa-clock me-2"></i>
                                    Solicitud #<?= $request['id'] ?>
                                </span>
                                <?php if ($isUrgent): ?>
                                    <span class="badge bg-danger urgent-badge">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        +24 horas
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">

                                <h5 class="card-title"><?= esc($request['company_name']) ?></h5>
                                <h6 class="text-muted"><?= esc($request['service_name']) ?></h6>

                                <hr>

                                <div class="row mb-3">
                                    <div class="col-6">
                                        <small class="text-muted d-block">Trabajador</small>
                                        <strong><?= esc($request['worker_name']) ?></strong>
                                        <br>
                                        <small><?= esc($request['worker_document']) ?></small>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">Tipo</small>
                                        <strong>
                                            <?php
                                                $types = [
                                                    'intralaboral_a' => 'Intralaboral A',
                                                    'intralaboral_b' => 'Intralaboral B',
                                                    'extralaboral' => 'Extralaboral',
                                                    'estres' => 'Estrés'
                                                ];
                                                echo $types[$request['request_type']] ?? $request['request_type'];
                                            ?>
                                        </strong>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <small class="text-muted d-block">Solicitado por</small>
                                    <strong><?= esc($request['requester_first_name'] . ' ' . $request['requester_last_name']) ?></strong>
                                    <br>
                                    <small><?= esc($request['requester_email']) ?></small>
                                </div>

                                <div class="mb-3">
                                    <small class="text-muted d-block">Fecha de solicitud</small>
                                    <strong><?= date('d/m/Y H:i', strtotime($request['created_at'])) ?></strong>
                                    <small class="text-muted">
                                        (hace <?= $hoursSince < 1 ? round($hoursSince * 60) . ' minutos' : round($hoursSince) . ' horas' ?>)
                                    </small>
                                </div>

                                <div class="mb-3">
                                    <small class="text-muted d-block mb-1">Motivación</small>
                                    <div class="border rounded p-2 bg-light" style="max-height: 100px; overflow-y: auto;">
                                        <small><?= nl2br(esc($request['motivation'])) ?></small>
                                    </div>
                                </div>

                            </div>
                            <div class="card-footer bg-transparent">
                                <div class="d-grid gap-2">
                                    <a href="<?= base_url("individual-results/review/{$request['id']}") ?>"
                                       class="btn btn-primary">
                                        <i class="fas fa-search me-2"></i>Revisar y Decidir
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php endforeach; ?>
            </div>

        <?php endif; ?>

        <div class="mt-4">
            <a href="<?= base_url('dashboard') ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Volver al Dashboard
            </a>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
