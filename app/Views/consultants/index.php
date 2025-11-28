<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Consultores' ?> - PsyRisk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0"><i class="fas fa-user-tie me-2"></i>Consultores</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="<?= base_url('/') ?>">Inicio</a></li>
                        <li class="breadcrumb-item active">Consultores</li>
                    </ol>
                </nav>
            </div>
            <a href="<?= base_url('consultants/create') ?>" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Nuevo Consultor
            </a>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <?php if (empty($consultants)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-user-tie fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No hay consultores registrados</p>
                    <a href="<?= base_url('consultants/create') ?>" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Crear primer consultor
                    </a>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Documento</th>
                                <th>Licencia SST</th>
                                <th>Email</th>
                                <th>Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($consultants as $consultant): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if (!empty($consultant['firma_path'])): ?>
                                        <img src="<?= base_url($consultant['firma_path']) ?>" alt="Firma" class="me-2" style="height: 30px; width: auto;">
                                        <?php else: ?>
                                        <i class="fas fa-user-circle fa-2x text-muted me-2"></i>
                                        <?php endif; ?>
                                        <div>
                                            <strong><?= esc($consultant['nombre_completo']) ?></strong>
                                            <?php if ($consultant['cargo']): ?>
                                            <br><small class="text-muted"><?= esc($consultant['cargo']) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td><?= esc($consultant['tipo_documento']) ?> <?= esc($consultant['numero_documento']) ?></td>
                                <td><?= esc($consultant['licencia_sst'] ?? '-') ?></td>
                                <td><?= esc($consultant['email'] ?? '-') ?></td>
                                <td>
                                    <?php if ($consultant['activo']): ?>
                                    <span class="badge bg-success">Activo</span>
                                    <?php else: ?>
                                    <span class="badge bg-secondary">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="<?= base_url('consultants/' . $consultant['id']) ?>" class="btn btn-sm btn-outline-info" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?= base_url('consultants/' . $consultant['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="<?= base_url('consultants/' . $consultant['id'] . '/delete') ?>" method="post" class="d-inline" onsubmit="return confirm('¿Está seguro de eliminar este consultor?')">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
