<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - PsyRisk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-clipboard-check me-2"></i><?= $title ?></h5>
                    </div>
                    <div class="card-body">
                        <?php if (session()->getFlashdata('errors')): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                        <li><?= esc($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form action="<?= base_url('battery-services/store') ?>" method="POST">
                            <?= csrf_field() ?>

                            <div class="mb-3">
                                <label for="company_id" class="form-label">Empresa Cliente *</label>
                                <select class="form-select" id="company_id" name="company_id" required>
                                    <option value="">Seleccione una empresa...</option>
                                    <?php foreach ($companies as $company): ?>
                                        <option value="<?= $company['id'] ?>" <?= old('company_id') == $company['id'] ? 'selected' : '' ?>>
                                            <?= esc($company['name']) ?> (NIT: <?= esc($company['nit']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">
                                    Empresa donde se aplicará la batería de riesgo psicosocial
                                </small>
                            </div>

                            <div class="mb-3">
                                <label for="consultant_id" class="form-label">Consultor Asignado *</label>
                                <select class="form-select" id="consultant_id" name="consultant_id" required>
                                    <option value="">Seleccione un consultor...</option>
                                    <?php foreach ($consultants as $consultant): ?>
                                        <option value="<?= $consultant['id'] ?>" <?= old('consultant_id') == $consultant['id'] ? 'selected' : '' ?>>
                                            <?= esc($consultant['nombre_completo']) ?>
                                            <?php if (!empty($consultant['licencia_sst'])): ?>
                                                - Lic. <?= esc($consultant['licencia_sst']) ?>
                                            <?php endif; ?>
                                            <?php if (!empty($consultant['email'])): ?>
                                                (<?= esc($consultant['email']) ?>)
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">
                                    Consultor que firmará los informes
                                </small>
                            </div>

                            <div class="mb-3">
                                <label for="service_name" class="form-label">Nombre del Servicio *</label>
                                <input type="text"
                                       class="form-control"
                                       id="service_name"
                                       name="service_name"
                                       value="<?= old('service_name') ?>"
                                       placeholder="Ej: Batería Psicosocial 2025 - Q1"
                                       required>
                                <small class="text-muted">
                                    Nombre descriptivo para identificar este servicio
                                </small>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="service_date" class="form-label">Fecha del Servicio *</label>
                                    <input type="date"
                                           class="form-control"
                                           id="service_date"
                                           name="service_date"
                                           value="<?= old('service_date') ?>"
                                           required>
                                    <small class="text-muted">
                                        Fecha de aplicación de la batería
                                    </small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Fecha de Vencimiento</label>
                                    <input type="text"
                                           class="form-control"
                                           value="7 días después de la fecha del servicio"
                                           readonly
                                           disabled>
                                    <small class="text-muted">
                                        Se calculará automáticamente
                                    </small>
                                </div>
                            </div>

                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Formularios incluidos:</strong>
                                <ul class="mb-0 mt-2">
                                    <li>Cuestionario Intralaboral (Tipo A o B según cargo)</li>
                                    <li>Cuestionario de Factores Extralaborales</li>
                                    <li>Cuestionario de Evaluación del Estrés</li>
                                </ul>
                            </div>

                            <hr>

                            <div class="d-flex justify-content-between">
                                <a href="<?= base_url('battery-services') ?>" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save me-2"></i>Crear Servicio
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
