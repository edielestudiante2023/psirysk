<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Nuevo Consultor' ?> - PsyRisk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="mb-4">
            <h1 class="h3 mb-0"><i class="fas fa-user-plus me-2"></i><?= $title ?></h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('/') ?>">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('consultants') ?>">Consultores</a></li>
                    <li class="breadcrumb-item active">Nuevo</li>
                </ol>
            </nav>
        </div>

        <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <form action="<?= base_url('consultants/store') ?>" method="post" enctype="multipart/form-data">
                    <?= csrf_field() ?>

                    <?= view('consultants/_form', ['errors' => session()->getFlashdata('errors') ?? []]) ?>

                    <hr>
                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= base_url('consultants') ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Guardar Consultor
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
