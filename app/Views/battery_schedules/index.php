<?php
$this->extend('layouts/main');
$this->section('content');
?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="fas fa-calendar-alt me-2"></i><?= esc($title) ?></h2>
            <p class="text-muted">Gestión de recordatorios para evaluaciones periódicas según Resolución 2764/2022</p>
        </div>
    </div>

    <!-- Alertas de próximas evaluaciones y vencidas -->
    <div class="row mb-4">
        <!-- Evaluaciones próximas (30 días) -->
        <?php if (!empty($upcoming)): ?>
        <div class="col-md-6">
            <div class="alert alert-warning border-warning">
                <h5 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Evaluaciones Próximas</h5>
                <p class="mb-2">Las siguientes empresas deben realizar su evaluación en los próximos 30 días:</p>
                <ul class="mb-0">
                    <?php foreach (array_slice($upcoming, 0, 5) as $schedule): ?>
                    <li>
                        <strong><?= esc($schedule['company_name']) ?></strong> -
                        Fecha límite: <?= date('d/m/Y', strtotime($schedule['next_evaluation_date'])) ?>
                        (<?= $schedule['periodicity_years'] == 1 ? 'Evaluación Anual' : 'Evaluación Bienal' ?>)
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php if (count($upcoming) > 5): ?>
                <p class="mt-2 mb-0"><small>Y <?= count($upcoming) - 5 ?> más...</small></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Evaluaciones vencidas -->
        <?php if (!empty($overdue)): ?>
        <div class="col-md-6">
            <div class="alert alert-danger border-danger">
                <h5 class="alert-heading"><i class="fas fa-ban me-2"></i>Evaluaciones VENCIDAS</h5>
                <p class="mb-2">Las siguientes empresas tienen evaluaciones vencidas que requieren atención inmediata:</p>
                <ul class="mb-0">
                    <?php foreach (array_slice($overdue, 0, 5) as $schedule): ?>
                    <li>
                        <strong><?= esc($schedule['company_name']) ?></strong> -
                        Vencida desde: <?= date('d/m/Y', strtotime($schedule['next_evaluation_date'])) ?>
                        <span class="badge bg-danger"><?= $schedule['intralaboral_risk_level'] ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php if (count($overdue) > 5): ?>
                <p class="mt-2 mb-0"><small>Y <?= count($overdue) - 5 ?> más...</small></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Tabla principal de recordatorios -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Todas las Evaluaciones Programadas</h5>
                    <span class="badge bg-light text-dark"><?= count($schedules) ?> registros</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Empresa</th>
                                    <th>Contacto</th>
                                    <th>Email</th>
                                    <th>Última Evaluación</th>
                                    <th>Nivel Riesgo Intralaboral</th>
                                    <th>Periodicidad</th>
                                    <th>Próxima Evaluación</th>
                                    <th>Días Restantes</th>
                                    <th>Notificaciones</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($schedules)): ?>
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                        No hay recordatorios programados
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($schedules as $schedule):
                                    $daysRemaining = (strtotime($schedule['next_evaluation_date']) - time()) / (60 * 60 * 24);
                                    $daysRemaining = round($daysRemaining);

                                    $statusClass = 'success';
                                    $statusIcon = 'check-circle';

                                    if ($daysRemaining < 0) {
                                        $statusClass = 'danger';
                                        $statusIcon = 'ban';
                                    } elseif ($daysRemaining <= 7) {
                                        $statusClass = 'danger';
                                        $statusIcon = 'exclamation-triangle';
                                    } elseif ($daysRemaining <= 30) {
                                        $statusClass = 'warning';
                                        $statusIcon = 'exclamation-circle';
                                    }

                                    $riskBadgeClass = [
                                        'sin_riesgo' => 'success',
                                        'riesgo_bajo' => 'info',
                                        'riesgo_medio' => 'warning',
                                        'riesgo_alto' => 'orange',
                                        'riesgo_muy_alto' => 'danger',
                                    ][$schedule['intralaboral_risk_level']] ?? 'secondary';
                                ?>
                                <tr>
                                    <td><strong><?= esc($schedule['company_name']) ?></strong></td>
                                    <td><?= esc($schedule['contact_name']) ?: '-' ?></td>
                                    <td>
                                        <a href="mailto:<?= esc($schedule['contact_email']) ?>">
                                            <i class="fas fa-envelope me-1"></i><?= esc($schedule['contact_email']) ?>
                                        </a>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($schedule['evaluation_date'])) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $riskBadgeClass ?>">
                                            <?= str_replace('_', ' ', ucwords($schedule['intralaboral_risk_level'], '_')) ?>
                                        </span>
                                        <br>
                                        <small class="text-muted">
                                            <?php if ($schedule['forma_a_risk_level']): ?>
                                                A: <?= str_replace('_', ' ', $schedule['forma_a_risk_level']) ?>
                                            <?php endif; ?>
                                            <?php if ($schedule['forma_b_risk_level']): ?>
                                                B: <?= str_replace('_', ' ', $schedule['forma_b_risk_level']) ?>
                                            <?php endif; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $schedule['periodicity_years'] == 1 ? 'danger' : 'info' ?>">
                                            <?= $schedule['periodicity_years'] == 1 ? 'Anual' : 'Cada 2 años' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong><?= date('d/m/Y', strtotime($schedule['next_evaluation_date'])) ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $statusClass ?>">
                                            <i class="fas fa-<?= $statusIcon ?> me-1"></i>
                                            <?php if ($daysRemaining < 0): ?>
                                                Vencida (<?= abs($daysRemaining) ?> días)
                                            <?php else: ?>
                                                <?= $daysRemaining ?> días
                                            <?php endif; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <?php if ($schedule['notification_30_days_sent']): ?>
                                                <span class="badge bg-success" title="30 días enviado">30d ✓</span>
                                            <?php endif; ?>
                                            <?php if ($schedule['notification_7_days_sent']): ?>
                                                <span class="badge bg-warning" title="7 días enviado">7d ✓</span>
                                            <?php endif; ?>
                                            <?php if ($schedule['notification_overdue_sent']): ?>
                                                <span class="badge bg-danger" title="Vencido enviado">V ✓</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="<?= base_url("battery-services/view/{$schedule['battery_service_id']}") ?>"
                                               class="btn btn-outline-primary"
                                               title="Ver servicio">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= base_url("battery-schedules/edit/{$schedule['id']}") ?>"
                                               class="btn btn-outline-warning"
                                               title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?= base_url("battery-schedules/send-manual/{$schedule['id']}") ?>"
                                               class="btn btn-outline-info"
                                               title="Enviar recordatorio ahora"
                                               onclick="return confirm('¿Enviar recordatorio por email ahora?')">
                                                <i class="fas fa-paper-plane"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Información normativa -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="card-title"><i class="fas fa-info-circle me-2"></i>Marco Normativo - Resolución 2764 de 2022</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Periodicidad de Evaluación:</strong></p>
                            <ul class="small mb-0">
                                <li><strong>Evaluación Anual:</strong> Cuando el riesgo psicosocial intralaboral es <strong>Alto</strong> o <strong>Muy Alto</strong></li>
                                <li><strong>Evaluación cada 2 años:</strong> Cuando el riesgo es <strong>Medio, Bajo o Sin Riesgo</strong></li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Punto de partida para el conteo:</strong></p>
                            <ul class="small mb-0">
                                <li>La periodicidad se cuenta desde el <strong>inicio de las acciones de intervención</strong>, no desde la aplicación</li>
                                <li>Las intervenciones deben iniciarse <strong>inmediatamente</strong> tras identificar el riesgo</li>
                                <li>Se evalúa el nivel de riesgo <strong>intralaboral únicamente</strong> (Forma A y/o B)</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>
