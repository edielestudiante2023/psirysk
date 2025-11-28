<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test PDF - Índice</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="alert alert-warning mb-4">
            <h4 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Vista Provisional de Prueba</h4>
            <p class="mb-0">Esta vista es solo para probar la estructura de datos antes de generar PDFs reales. <strong>ELIMINAR EN PRODUCCIÓN.</strong></p>
        </div>

        <h1 class="mb-4"><i class="fas fa-file-pdf text-danger me-2"></i>Test de Generación PDF</h1>

        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card h-100 border-primary">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Demographics Interpretations</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Tabla: <code>demographics_interpretations</code></p>
                        <p>Contiene las interpretaciones de IA del perfil sociodemográfico.</p>
                        <hr>
                        <h6>Servicios disponibles:</h6>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($services as $service): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>
                                    <strong>#<?= $service['id'] ?></strong> - <?= esc($service['company_name']) ?>
                                    <br><small class="text-muted">Estado: <?= $service['status'] ?></small>
                                </span>
                                <a href="<?= base_url('test-pdf/demographics/' . $service['id']) ?>" class="btn btn-sm btn-primary" target="_blank">
                                    <i class="fas fa-eye me-1"></i>Ver
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card h-100 border-success">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Report Sections</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Tabla: <code>report_sections</code></p>
                        <p>Contiene las secciones del informe con textos de IA por dimensión/dominio.</p>
                        <hr>
                        <h6>Servicios disponibles:</h6>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($services as $service): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>
                                    <strong>#<?= $service['id'] ?></strong> - <?= esc($service['company_name']) ?>
                                    <br><small class="text-muted">Estado: <?= $service['status'] ?></small>
                                </span>
                                <a href="<?= base_url('test-pdf/report-sections/' . $service['id']) ?>" class="btn btn-sm btn-success" target="_blank">
                                    <i class="fas fa-eye me-1"></i>Ver
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="fas fa-database me-2"></i>Estructura de Datos</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary">demographics_interpretations</h6>
                        <pre class="bg-light p-3 rounded"><code>- id
- battery_service_id
- interpretation_text (LONGTEXT)
- consultant_comment (TEXT)
- aggregated_data (JSON)
- generated_by
- created_at
- updated_at</code></pre>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-success">report_sections</h6>
                        <pre class="bg-light p-3 rounded"><code>- id
- report_id
- section_level (executive/total/questionnaire/domain/dimension)
- questionnaire_type
- domain_code / dimension_code
- form_type (A/B/conjunto)
- score_value / risk_level
- ai_generated_text (LONGTEXT)
- consultant_comment (TEXT)
- is_approved
- distribution_data (JSON)</code></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
