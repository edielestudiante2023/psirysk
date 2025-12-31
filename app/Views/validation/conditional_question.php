<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?> - PsyRisk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }
        .content-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            color: white;
            margin-bottom: 1rem;
        }
        .stat-value {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        .badge-respuesta {
            font-size: 0.95rem;
            padding: 0.5rem 1rem;
            font-weight: 600;
        }
        .alert-legal {
            border-left: 5px solid #dc3545;
            background-color: #f8d7da;
            color: #721c24;
        }
        .table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h2 class="mb-1"><i class="fas fa-list-check me-2"></i><?= esc($title) ?></h2>
                    <p class="text-muted mb-0"><?= esc($service['service_name']) ?> - <?= esc($service['company_name']) ?></p>
                </div>
                <a href="<?= base_url('/validation/' . $service['id']) ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Volver
                </a>
            </div>

            <!-- Pregunta Condicional -->
            <div class="alert alert-info mb-0">
                <h5 class="mb-1"><i class="fas fa-question-circle me-2"></i>Pregunta Condicional <?= $formType === 'A' && strpos($title, 'II') !== false ? 'II' : 'I' ?></h5>
                <p class="mb-2"><strong><?= esc($pregunta) ?></strong></p>
                <small><i class="fas fa-info-circle me-1"></i>Controla la habilitación de los ítems: <?= esc($items_controlados) ?></small>
            </div>
        </div>

        <!-- Alerta Legal -->
        <?php if ($formType === 'A' && strpos($title, 'II') !== false): ?>
        <div class="content-card">
            <div class="alert alert-legal mb-0">
                <h5 class="mb-2"><i class="fas fa-exclamation-triangle me-2"></i>Advertencia Legal</h5>
                <p class="mb-0">
                    <strong>Un jefe que responda "NO" a esta pregunta NO responderá los 9 ítems de la dimensión "Relación con Colaboradores".</strong>
                    Esto puede tener implicaciones legales si existe una inconsistencia entre el cargo declarado (cargo con personal a cargo)
                    y la respuesta proporcionada. Revise cuidadosamente este listado para identificar posibles incongruencias.
                </p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Estadísticas -->
        <div class="content-card">
            <h4 class="mb-3"><i class="fas fa-chart-pie me-2"></i>Estadísticas de Respuestas</h4>
            <div class="row">
                <div class="col-md-4">
                    <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <div class="stat-value"><?= $stats['total'] ?></div>
                        <div class="stat-label">Total Trabajadores</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card bg-success">
                        <div class="stat-value"><?= $stats['si'] ?></div>
                        <div class="stat-label">Respondieron SÍ</div>
                        <small>(<?= $stats['total'] > 0 ? round(($stats['si'] / $stats['total']) * 100, 1) : 0 ?>%)</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card bg-warning">
                        <div class="stat-value"><?= $stats['no'] ?></div>
                        <div class="stat-label">Respondieron NO</div>
                        <small>(<?= $stats['total'] > 0 ? round(($stats['no'] / $stats['total']) * 100, 1) : 0 ?>%)</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Trabajadores -->
        <div class="content-card">
            <h4 class="mb-3"><i class="fas fa-users me-2"></i>Detalle de Respuestas por Trabajador</h4>
            <div class="table-responsive">
                <table id="workersTable" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Cédula</th>
                            <th>Nombre</th>
                            <th>Cargo</th>
                            <th>Área</th>
                            <th>Respuesta</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($workers as $index => $worker):
                            $respuestaKey = $formType === 'A' && strpos($title, 'II') !== false ? 'respuesta_pregunta_ii' : 'respuesta_pregunta_i';
                            $respuesta = $worker[$respuestaKey] ?? null;
                            $respuestaLower = $respuesta ? strtolower($respuesta) : null;
                        ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><strong><?= esc($worker['document']) ?></strong></td>
                            <td><?= esc($worker['name']) ?></td>
                            <td><?= esc($worker['position']) ?></td>
                            <td><?= esc($worker['area'] ?? 'N/A') ?></td>
                            <td>
                                <?php if ($respuestaLower === 'si'): ?>
                                    <span class="badge badge-respuesta bg-success">
                                        <i class="fas fa-check me-1"></i>SÍ
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-respuesta bg-warning text-dark">
                                        <i class="fas fa-times me-1"></i>NO
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Leyenda -->
        <div class="content-card">
            <h5 class="mb-3"><i class="fas fa-info-circle me-2"></i>Interpretación</h5>
            <ul class="mb-0">
                <li><strong>SÍ:</strong> El trabajador respondió los <?= esc($items_controlados) ?> correspondientes.</li>
                <li><strong>NO:</strong> El trabajador NO respondió estos ítems (fueron omitidos del cuestionario).</li>
            </ul>

            <?php if ($formType === 'A' && strpos($title, 'II') !== false): ?>
            <hr>
            <h5 class="text-danger"><i class="fas fa-balance-scale me-2"></i>Verificación Legal Recomendada</h5>
            <p class="mb-0">
                Revise que los trabajadores con cargos de jefatura, supervisión, coordinación o dirección
                (especialmente aquellos con personal a cargo según organigrama) hayan respondido <strong>SÍ</strong>
                a esta pregunta. Una respuesta de <strong>NO</strong> en estos casos puede indicar:
            </p>
            <ul class="mb-0 mt-2">
                <li>Error del trabajador al responder</li>
                <li>Inconsistencia entre cargo declarado y funciones reales</li>
                <li>Posible riesgo legal por omisión de evaluación de dimensión crítica</li>
            </ul>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#workersTable').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },
                pageLength: 25,
                order: [[1, 'asc']], // Ordenar por cédula
                columnDefs: [
                    { orderable: false, targets: [0] } // Deshabilitar orden en columna #
                ]
            });
        });
    </script>
</body>
</html>
