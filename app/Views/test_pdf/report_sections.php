<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test PDF - Report Sections</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            .page-break { page-break-before: always; }
        }
        .section-card {
            margin-bottom: 15px;
            border-left: 4px solid #198754;
        }
        .section-card.executive { border-left-color: #dc3545; }
        .section-card.total { border-left-color: #fd7e14; }
        .section-card.questionnaire { border-left-color: #0d6efd; }
        .section-card.domain { border-left-color: #6f42c1; }
        .section-card.dimension { border-left-color: #198754; }
        .ai-text {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            white-space: pre-wrap;
            line-height: 1.7;
        }
        .raw-text {
            white-space: pre-wrap;
            font-family: monospace;
            font-size: 11px;
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 10px;
            border-radius: 5px;
            max-height: 200px;
            overflow: auto;
        }
        .risk-badge {
            font-size: 0.75rem;
            padding: 4px 8px;
        }
        .risk-sin { background-color: #28a745 !important; }
        .risk-bajo { background-color: #17a2b8 !important; }
        .risk-medio { background-color: #ffc107 !important; color: #000 !important; }
        .risk-alto { background-color: #fd7e14 !important; }
        .risk-muy-alto { background-color: #dc3545 !important; }
    </style>
</head>
<body class="bg-light">
    <div class="container py-4">
        <!-- Barra de navegación de prueba -->
        <div class="alert alert-info mb-4 no-print">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="alert-heading mb-1"><i class="fas fa-flask me-2"></i>Vista de Prueba - Report Sections</h5>
                    <small>Servicio #<?= $service['id'] ?> - <?= esc($service['company_name']) ?></small>
                </div>
                <div>
                    <a href="<?= base_url('test-pdf') ?>" class="btn btn-outline-success btn-sm me-2">
                        <i class="fas fa-arrow-left me-1"></i>Volver
                    </a>
                    <a href="<?= base_url('test-pdf/download-report-sections/' . $service['id']) ?>" class="btn btn-danger btn-sm me-2">
                        <i class="fas fa-file-pdf me-1"></i>Descargar PDF
                    </a>
                    <button onclick="window.print()" class="btn btn-success btn-sm">
                        <i class="fas fa-print me-1"></i>Imprimir
                    </button>
                </div>
            </div>
        </div>

        <!-- ENCABEZADO DEL DOCUMENTO -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Informe de Riesgo Psicosocial - Secciones</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Empresa:</strong> <?= esc($service['company_name']) ?></p>
                        <p><strong>ID Servicio:</strong> <?= $service['id'] ?></p>
                        <p><strong>ID Reporte:</strong> <?= $report['id'] ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Total Secciones:</strong> <span class="badge bg-success"><?= $totalSections ?></span></p>
                        <p><strong>Fecha Creación:</strong> <?= isset($report['created_at']) ? date('d/m/Y H:i', strtotime($report['created_at'])) : 'N/A' ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- RESUMEN POR NIVEL -->
        <div class="card mb-4 no-print">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="fas fa-layer-group me-2"></i>Resumen por Nivel de Sección</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col">
                        <div class="card border-danger">
                            <div class="card-body py-2">
                                <h3 class="mb-0 text-danger"><?= count($groupedSections['executive']) ?></h3>
                                <small>Executive</small>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card border-warning">
                            <div class="card-body py-2">
                                <h3 class="mb-0 text-warning"><?= count($groupedSections['total']) ?></h3>
                                <small>Total</small>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card border-primary">
                            <div class="card-body py-2">
                                <h3 class="mb-0 text-primary"><?= count($groupedSections['questionnaire']) ?></h3>
                                <small>Questionnaire</small>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card border-purple" style="border-color: #6f42c1 !important;">
                            <div class="card-body py-2">
                                <h3 class="mb-0" style="color: #6f42c1;"><?= count($groupedSections['domain']) ?></h3>
                                <small>Domain</small>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card border-success">
                            <div class="card-body py-2">
                                <h3 class="mb-0 text-success"><?= count($groupedSections['dimension']) ?></h3>
                                <small>Dimension</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECCIONES POR NIVEL -->
        <?php
        $levelConfig = [
            'executive' => ['title' => 'Resumen Ejecutivo', 'icon' => 'fa-star', 'color' => 'danger'],
            'total' => ['title' => 'Totales Generales', 'icon' => 'fa-calculator', 'color' => 'warning'],
            'questionnaire' => ['title' => 'Por Cuestionario', 'icon' => 'fa-clipboard-list', 'color' => 'primary'],
            'domain' => ['title' => 'Por Dominio', 'icon' => 'fa-sitemap', 'color' => 'purple'],
            'dimension' => ['title' => 'Por Dimensión', 'icon' => 'fa-cubes', 'color' => 'success'],
        ];

        foreach ($levelConfig as $level => $config):
            $levelSections = $groupedSections[$level];
            if (empty($levelSections)) continue;
        ?>
        <div class="card mb-4">
            <div class="card-header text-white" style="background-color: <?= $level === 'domain' ? '#6f42c1' : '' ?>; <?= $level !== 'domain' ? 'background-color: var(--bs-' . $config['color'] . ')' : '' ?>">
                <h5 class="mb-0">
                    <i class="fas <?= $config['icon'] ?> me-2"></i><?= $config['title'] ?>
                    <span class="badge bg-light text-dark ms-2"><?= count($levelSections) ?> secciones</span>
                </h5>
            </div>
            <div class="card-body">
                <?php foreach ($levelSections as $section): ?>
                <div class="card section-card <?= $level ?> mb-3">
                    <div class="card-header bg-light py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>#<?= $section['id'] ?></strong>
                                <?php if ($section['questionnaire_type']): ?>
                                <span class="badge bg-secondary ms-2"><?= $section['questionnaire_type'] ?></span>
                                <?php endif; ?>
                                <?php if ($section['form_type']): ?>
                                <span class="badge bg-info ms-1">Forma <?= $section['form_type'] ?></span>
                                <?php endif; ?>
                                <?php if ($section['domain_code']): ?>
                                <span class="badge bg-purple ms-1" style="background-color: #6f42c1 !important;"><?= $section['domain_code'] ?></span>
                                <?php endif; ?>
                                <?php if ($section['dimension_code']): ?>
                                <span class="badge bg-success ms-1"><?= $section['dimension_code'] ?></span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <?php if ($section['score_value'] !== null): ?>
                                <span class="badge bg-dark">Puntaje: <?= number_format($section['score_value'], 1) ?></span>
                                <?php endif; ?>
                                <?php if ($section['risk_level']): ?>
                                <?php
                                $riskClass = 'bg-secondary';
                                if (stripos($section['risk_level'], 'sin') !== false) $riskClass = 'risk-sin';
                                elseif (stripos($section['risk_level'], 'bajo') !== false) $riskClass = 'risk-bajo';
                                elseif (stripos($section['risk_level'], 'medio') !== false) $riskClass = 'risk-medio';
                                elseif (stripos($section['risk_level'], 'muy alto') !== false) $riskClass = 'risk-muy-alto';
                                elseif (stripos($section['risk_level'], 'alto') !== false) $riskClass = 'risk-alto';
                                ?>
                                <span class="badge risk-badge <?= $riskClass ?>"><?= $section['risk_level'] ?></span>
                                <?php endif; ?>
                                <?php if ($section['is_approved']): ?>
                                <span class="badge bg-success ms-1"><i class="fas fa-check"></i> Aprobado</span>
                                <?php else: ?>
                                <span class="badge bg-warning text-dark ms-1">Pendiente</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if ($section['ai_generated_text']): ?>
                        <div class="mb-3">
                            <h6 class="text-success mb-2"><i class="fas fa-robot me-1"></i>Texto Generado por IA:</h6>
                            <div class="ai-text"><?= nl2br(esc($section['ai_generated_text'])) ?></div>
                            <small class="text-muted mt-1 d-block">
                                <?= strlen($section['ai_generated_text']) ?> caracteres |
                                <?= str_word_count($section['ai_generated_text']) ?> palabras
                            </small>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-secondary mb-3">
                            <i class="fas fa-info-circle me-2"></i>Sin texto de IA generado
                        </div>
                        <?php endif; ?>

                        <?php if ($section['consultant_comment']): ?>
                        <div class="mb-3">
                            <h6 class="text-warning mb-2"><i class="fas fa-comment me-1"></i>Comentario del Consultor:</h6>
                            <div class="p-3 bg-warning bg-opacity-10 rounded"><?= nl2br(esc($section['consultant_comment'])) ?></div>
                        </div>
                        <?php endif; ?>

                        <!-- Datos técnicos (solo debug) -->
                        <div class="no-print">
                            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#debug-<?= $section['id'] ?>">
                                <i class="fas fa-bug me-1"></i>Ver datos técnicos
                            </button>
                            <div class="collapse mt-2" id="debug-<?= $section['id'] ?>">
                                <div class="card card-body bg-light">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <small>
                                                <strong>order_position:</strong> <?= $section['order_position'] ?? 'N/A' ?><br>
                                                <strong>approved_at:</strong> <?= $section['approved_at'] ?? 'N/A' ?><br>
                                                <strong>approved_by:</strong> <?= $section['approved_by'] ?? 'N/A' ?><br>
                                                <strong>created_at:</strong> <?= $section['created_at'] ?><br>
                                                <strong>updated_at:</strong> <?= $section['updated_at'] ?>
                                            </small>
                                        </div>
                                        <div class="col-md-6">
                                            <?php if ($section['distribution_data']): ?>
                                            <strong>distribution_data:</strong>
                                            <pre class="raw-text"><?= json_encode(json_decode($section['distribution_data']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?></pre>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- TABLA RESUMEN DE TODAS LAS SECCIONES -->
        <div class="card mb-4 no-print">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="fas fa-table me-2"></i>Tabla Resumen de Secciones</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Nivel</th>
                                <th>Cuestionario</th>
                                <th>Forma</th>
                                <th>Dominio</th>
                                <th>Dimensión</th>
                                <th>Puntaje</th>
                                <th>Riesgo</th>
                                <th>Texto IA</th>
                                <th>Aprobado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sections as $section): ?>
                            <tr>
                                <td><?= $section['id'] ?></td>
                                <td><span class="badge bg-secondary"><?= $section['section_level'] ?></span></td>
                                <td><?= $section['questionnaire_type'] ?: '-' ?></td>
                                <td><?= $section['form_type'] ?: '-' ?></td>
                                <td><?= $section['domain_code'] ?: '-' ?></td>
                                <td><?= $section['dimension_code'] ?: '-' ?></td>
                                <td><?= $section['score_value'] !== null ? number_format($section['score_value'], 1) : '-' ?></td>
                                <td><?= $section['risk_level'] ?: '-' ?></td>
                                <td>
                                    <?php if ($section['ai_generated_text']): ?>
                                    <span class="badge bg-success"><?= strlen($section['ai_generated_text']) ?> chars</span>
                                    <?php else: ?>
                                    <span class="badge bg-secondary">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($section['is_approved']): ?>
                                    <i class="fas fa-check text-success"></i>
                                    <?php else: ?>
                                    <i class="fas fa-clock text-warning"></i>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- PIE DE PÁGINA -->
        <div class="text-center text-muted py-3 no-print">
            <hr>
            <small>
                <i class="fas fa-flask me-1"></i>Vista de prueba para generación de PDF -
                Tabla: <code>report_sections</code> |
                Generado: <?= date('d/m/Y H:i:s') ?>
            </small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
