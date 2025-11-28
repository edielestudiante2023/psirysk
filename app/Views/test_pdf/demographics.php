<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test PDF - Demographics Interpretations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            .page-break { page-break-before: always; }
        }
        .section-content {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            border-left: 4px solid #6f42c1;
        }
        .raw-text {
            white-space: pre-wrap;
            font-family: monospace;
            font-size: 12px;
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 15px;
            border-radius: 8px;
            max-height: 400px;
            overflow: auto;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-4">
        <!-- Barra de navegación de prueba -->
        <div class="alert alert-info mb-4 no-print">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="alert-heading mb-1"><i class="fas fa-flask me-2"></i>Vista de Prueba - Demographics Interpretations</h5>
                    <small>Servicio #<?= $service['id'] ?> - <?= esc($service['company_name']) ?></small>
                </div>
                <div>
                    <a href="<?= base_url('test-pdf') ?>" class="btn btn-outline-primary btn-sm me-2">
                        <i class="fas fa-arrow-left me-1"></i>Volver
                    </a>
                    <a href="<?= base_url('test-pdf/download-demographics/' . $service['id']) ?>" class="btn btn-danger btn-sm me-2">
                        <i class="fas fa-file-pdf me-1"></i>Descargar PDF
                    </a>
                    <button onclick="window.print()" class="btn btn-primary btn-sm">
                        <i class="fas fa-print me-1"></i>Imprimir
                    </button>
                </div>
            </div>
        </div>

        <!-- ENCABEZADO DEL DOCUMENTO -->
        <div class="card mb-4">
            <div class="card-header bg-purple text-white" style="background-color: #6f42c1 !important;">
                <h4 class="mb-0"><i class="fas fa-users me-2"></i>Ficha de Datos Generales - Análisis Sociodemográfico</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Empresa:</strong> <?= esc($service['company_name']) ?></p>
                        <p><strong>ID Servicio:</strong> <?= $service['id'] ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Fecha de Generación:</strong> <?= isset($record['created_at']) ? date('d/m/Y H:i', strtotime($record['created_at'])) : 'N/A' ?></p>
                        <p><strong>Última Actualización:</strong> <?= isset($record['updated_at']) ? date('d/m/Y H:i', strtotime($record['updated_at'])) : 'N/A' ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- INFORMACIÓN DE LA TABLA -->
        <div class="card mb-4 no-print">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="fas fa-database me-2"></i>Datos de la Tabla: demographics_interpretations</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-sm">
                    <tr>
                        <th style="width: 200px;">Campo</th>
                        <th>Valor</th>
                    </tr>
                    <tr>
                        <td><code>id</code></td>
                        <td><?= $record['id'] ?></td>
                    </tr>
                    <tr>
                        <td><code>battery_service_id</code></td>
                        <td><?= $record['battery_service_id'] ?></td>
                    </tr>
                    <tr>
                        <td><code>interpretation_text</code></td>
                        <td>
                            <span class="badge bg-success"><?= strlen($record['interpretation_text']) ?> caracteres</span>
                            <span class="badge bg-info"><?= str_word_count($record['interpretation_text']) ?> palabras</span>
                        </td>
                    </tr>
                    <tr>
                        <td><code>consultant_comment</code></td>
                        <td>
                            <?php if ($consultantComment): ?>
                            <span class="badge bg-success"><?= strlen($consultantComment) ?> caracteres</span>
                            <?php else: ?>
                            <span class="badge bg-secondary">Sin comentario</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><code>aggregated_data</code></td>
                        <td>
                            <?php if ($aggregatedData): ?>
                            <span class="badge bg-success"><?= count($aggregatedData) ?> variables</span>
                            <?php else: ?>
                            <span class="badge bg-secondary">Sin datos</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><code>generated_by</code></td>
                        <td><?= $record['generated_by'] ?? 'N/A' ?></td>
                    </tr>
                    <tr>
                        <td><code>created_at</code></td>
                        <td><?= $record['created_at'] ?></td>
                    </tr>
                    <tr>
                        <td><code>updated_at</code></td>
                        <td><?= $record['updated_at'] ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- SECCIONES PARSEADAS (para PDF) -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-list-alt me-2"></i>Interpretaciones Parseadas por Sección (<?= count($sections) ?> secciones)</h5>
            </div>
            <div class="card-body">
                <?php if (empty($sections)): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>No se encontraron secciones parseables.
                    Verifica que el texto use el formato <code>**NOMBRE:**</code>
                </div>
                <?php else: ?>
                <?php foreach ($sections as $index => $section): ?>
                <div class="section-content">
                    <h6 class="text-purple mb-2" style="color: #6f42c1;">
                        <i class="fas fa-chevron-right me-2"></i><?= esc($section['name']) ?>
                    </h6>
                    <p class="mb-0"><?= nl2br(esc($section['content'])) ?></p>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- COMENTARIOS DEL CONSULTOR -->
        <?php if ($consultantComment): ?>
        <div class="card mb-4">
            <div class="card-header bg-warning">
                <h5 class="mb-0"><i class="fas fa-comment-dots me-2"></i>Comentarios del Consultor</h5>
            </div>
            <div class="card-body">
                <p class="mb-0"><?= nl2br(esc($consultantComment)) ?></p>
            </div>
        </div>
        <?php endif; ?>

        <!-- DATOS AGREGADOS (DEBUG) -->
        <?php if ($aggregatedData): ?>
        <div class="card mb-4 no-print">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Datos Agregados (aggregated_data)</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($aggregatedData as $key => $value): ?>
                    <div class="col-md-4 mb-3">
                        <div class="card h-100">
                            <div class="card-header bg-light py-2">
                                <strong><?= esc($key) ?></strong>
                            </div>
                            <div class="card-body p-2" style="max-height: 150px; overflow: auto;">
                                <pre style="font-size: 10px; margin: 0;"><?= json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?></pre>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- TEXTO RAW (DEBUG) -->
        <div class="card mb-4 no-print">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="fas fa-code me-2"></i>Texto Raw (interpretation_text)</h5>
            </div>
            <div class="card-body">
                <div class="raw-text"><?= esc($rawText) ?></div>
            </div>
        </div>

        <!-- PIE DE PÁGINA -->
        <div class="text-center text-muted py-3 no-print">
            <hr>
            <small>
                <i class="fas fa-flask me-1"></i>Vista de prueba para generación de PDF -
                Tabla: <code>demographics_interpretations</code> |
                Generado: <?= date('d/m/Y H:i:s') ?>
            </small>
        </div>
    </div>
</body>
</html>
