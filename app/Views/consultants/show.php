<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Detalle Consultor' ?> - PsyRisk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0"><i class="fas fa-user-tie me-2"></i><?= $title ?></h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="<?= base_url('/') ?>">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('consultants') ?>">Consultores</a></li>
                        <li class="breadcrumb-item active">Detalle</li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="<?= base_url('consultants/' . $consultant['id'] . '/edit') ?>" class="btn btn-primary">
                    <i class="fas fa-edit me-1"></i> Editar
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información del Consultor</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="text-muted small">Nombre Completo</label>
                                <p class="mb-0 fw-bold"><?= esc($consultant['nombre_completo']) ?></p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">Documento</label>
                                <p class="mb-0"><?= esc($consultant['tipo_documento']) ?> <?= esc($consultant['numero_documento']) ?></p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="text-muted small">Cargo</label>
                                <p class="mb-0"><?= esc($consultant['cargo'] ?? '-') ?></p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">Licencia SST</label>
                                <p class="mb-0"><?= esc($consultant['licencia_sst'] ?? '-') ?></p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="text-muted small">Email</label>
                                <p class="mb-0">
                                    <?php if ($consultant['email']): ?>
                                    <a href="mailto:<?= esc($consultant['email']) ?>"><?= esc($consultant['email']) ?></a>
                                    <?php else: ?>
                                    -
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">Teléfono</label>
                                <p class="mb-0"><?= esc($consultant['telefono'] ?? '-') ?></p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="text-muted small">Sitio Web</label>
                                <p class="mb-0">
                                    <?php if ($consultant['website']): ?>
                                    <a href="<?= esc($consultant['website']) ?>" target="_blank"><?= esc($consultant['website']) ?></a>
                                    <?php else: ?>
                                    -
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">LinkedIn</label>
                                <p class="mb-0">
                                    <?php if ($consultant['linkedin']): ?>
                                    <a href="<?= esc($consultant['linkedin']) ?>" target="_blank"><?= esc($consultant['linkedin']) ?></a>
                                    <?php else: ?>
                                    -
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="text-muted small">Estado</label>
                                <p class="mb-0">
                                    <?php if ($consultant['activo']): ?>
                                    <span class="badge bg-success">Activo</span>
                                    <?php else: ?>
                                    <span class="badge bg-secondary">Inactivo</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="fas fa-signature me-2"></i>Firma</h5>
                    </div>
                    <div class="card-body text-center">
                        <?php if (!empty($consultant['firma_path'])): ?>
                        <img src="<?= base_url($consultant['firma_path']) ?>" alt="Firma" class="img-fluid" style="max-height: 150px;">
                        <?php else: ?>
                        <div class="text-muted py-4">
                            <i class="fas fa-signature fa-3x mb-2"></i>
                            <p class="mb-0">Sin firma registrada</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <a href="<?= base_url('consultants') ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Volver a la lista
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
