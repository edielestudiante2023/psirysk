<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - PsyRisk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .status-pending {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
        }
        .status-approved {
            background: #d1e7dd;
            border-left: 4px solid #198754;
        }
        .status-rejected {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
        }
        .timeline {
            position: relative;
            padding-left: 2rem;
        }
        .timeline::before {
            content: '';
            position: absolute;
            left: 0.5rem;
            top: 0.5rem;
            bottom: 0.5rem;
            width: 2px;
            background: #dee2e6;
        }
        .timeline-item {
            position: relative;
            padding-left: 2rem;
            margin-bottom: 1.5rem;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0.25rem;
            width: 1rem;
            height: 1rem;
            border-radius: 50%;
            background: #fff;
            border: 2px solid #0d6efd;
        }
        .timeline-item.completed::before {
            background: #0d6efd;
        }
    </style>
</head>
<body>
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">

                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle me-2"></i>
                        <?= session()->getFlashdata('success') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card shadow-sm">
                    <div class="card-header <?php
                        echo $request['status'] === 'pending' ? 'bg-warning text-dark' :
                            ($request['status'] === 'approved' ? 'bg-success text-white' : 'bg-danger text-white');
                    ?>">
                        <h4 class="mb-0">
                            <i class="fas fa-<?php
                                echo $request['status'] === 'pending' ? 'clock' :
                                    ($request['status'] === 'approved' ? 'check-circle' : 'times-circle');
                            ?> me-2"></i>
                            Estado: <?php
                                echo $request['status'] === 'pending' ? 'En Revisión' :
                                    ($request['status'] === 'approved' ? 'Aprobada' : 'Rechazada');
                            ?>
                        </h4>
                    </div>
                    <div class="card-body">

                        <div class="status-<?= $request['status'] ?> p-4 rounded mb-4">
                            <?php if ($request['status'] === 'pending'): ?>
                                <h5><i class="fas fa-hourglass-half me-2"></i>Solicitud en Proceso</h5>
                                <p class="mb-0">
                                    Su solicitud está siendo revisada por el consultor especialista de Cycloid Talent.
                                    Recibirá una notificación por email cuando sea procesada.
                                </p>
                            <?php elseif ($request['status'] === 'approved'): ?>
                                <h5><i class="fas fa-check-circle me-2"></i>Solicitud Aprobada</h5>
                                <p class="mb-2">
                                    Su solicitud ha sido aprobada. El acceso estará disponible hasta:
                                </p>
                                <p class="mb-3">
                                    <strong class="fs-5"><?= date('d/m/Y H:i', strtotime($request['access_granted_until'])) ?></strong>
                                </p>
                                <?php if (strtotime($request['access_granted_until']) > time()): ?>
                                    <a href="<?= base_url("individual-results/view/{$request['access_token']}") ?>"
                                       class="btn btn-success btn-lg">
                                        <i class="fas fa-eye me-2"></i>Ver Resultados Individuales
                                    </a>
                                <?php else: ?>
                                    <div class="alert alert-warning mb-0">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        El período de acceso ha expirado.
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <h5><i class="fas fa-times-circle me-2"></i>Solicitud Rechazada</h5>
                                <p class="mb-0">
                                    Su solicitud no fue aprobada. Por favor contacte a su asesor de Cycloid Talent
                                    para más información.
                                </p>
                            <?php endif; ?>
                        </div>

                        <h5 class="mb-3">Detalles de la Solicitud</h5>

                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th style="width: 30%;">Empresa</th>
                                    <td><?= esc($request['company_name']) ?></td>
                                </tr>
                                <tr>
                                    <th>Servicio</th>
                                    <td><?= esc($request['service_name']) ?></td>
                                </tr>
                                <tr>
                                    <th>Trabajador</th>
                                    <td>
                                        <?= esc($request['worker_name']) ?>
                                        <br>
                                        <small class="text-muted">Doc: <?= esc($request['worker_document']) ?></small>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Tipo de Evaluación</th>
                                    <td>
                                        <?php
                                            $types = [
                                                'intralaboral_a' => 'Intralaboral Forma A',
                                                'intralaboral_b' => 'Intralaboral Forma B',
                                                'extralaboral' => 'Extralaboral',
                                                'estres' => 'Estrés'
                                            ];
                                            echo esc($types[$request['request_type']] ?? $request['request_type']);
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Fecha de Solicitud</th>
                                    <td><?= date('d/m/Y H:i', strtotime($request['created_at'])) ?></td>
                                </tr>
                            </tbody>
                        </table>

                        <h6 class="mb-2">Motivación de la Solicitud:</h6>
                        <div class="border rounded p-3 bg-light mb-4">
                            <?= nl2br(esc($request['motivation'])) ?>
                        </div>

                        <?php if ($request['status'] !== 'pending'): ?>
                            <h6 class="mb-2">Revisión del Consultor:</h6>
                            <div class="border rounded p-3 bg-light mb-3">
                                <p class="mb-1">
                                    <strong>Revisado por:</strong>
                                    <?= esc($request['reviewer_first_name'] . ' ' . $request['reviewer_last_name']) ?>
                                </p>
                                <p class="mb-1">
                                    <strong>Fecha:</strong>
                                    <?= date('d/m/Y H:i', strtotime($request['reviewed_at'])) ?>
                                </p>
                                <?php if ($request['review_notes']): ?>
                                    <p class="mb-0">
                                        <strong>Notas:</strong><br>
                                        <?= nl2br(esc($request['review_notes'])) ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <div class="mt-4">
                            <a href="<?= base_url("reports/intralaboral/{$request['service_id']}") ?>"
                               class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Volver al Dashboard
                            </a>
                        </div>

                    </div>
                </div>

                <div class="mt-4 text-center">
                    <p class="text-muted">
                        ¿Necesita asistencia?<br>
                        Visite <a href="https://cycloidtalent.com/riesgo-psicosocial" target="_blank">cycloidtalent.com/riesgo-psicosocial</a>
                        o contacte a su asesor de Cycloid Talent SAS
                    </p>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <?php if ($request['status'] === 'pending'): ?>
    <script>
        // Auto-refresh page every 30 seconds if status is pending
        setTimeout(function() {
            location.reload();
        }, 30000);
    </script>
    <?php endif; ?>
</body>
</html>
