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
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-user-plus me-2"></i>Nuevo Trabajador
                        </h4>
                        <p class="mb-0 mt-2"><small>Empresa: <?= esc($service['company_name']) ?></small></p>
                    </div>
                    <div class="card-body">
                        <?php if (session()->has('errors')): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach (session('errors') as $error): ?>
                                        <li><?= esc($error) ?></li>
                                    <?php endforeach ?>
                                </ul>
                            </div>
                        <?php endif ?>

                        <form action="<?= base_url('workers/store/' . $service['id']) ?>" method="POST">
                            <?= csrf_field() ?>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Nombre Completo *</label>
                                    <input type="text" class="form-control" id="name" name="name"
                                           value="<?= old('name') ?>" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="document" class="form-label">Número de Documento *</label>
                                    <input type="text" class="form-control" id="document" name="document"
                                           value="<?= old('document') ?>" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Correo Electrónico *</label>
                                    <input type="email" class="form-control" id="email" name="email"
                                           value="<?= old('email') ?>" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="position" class="form-label">Cargo *</label>
                                    <input type="text" class="form-control" id="position" name="position"
                                           value="<?= old('position') ?>" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="area" class="form-label">Área</label>
                                    <input type="text" class="form-control" id="area" name="area"
                                           value="<?= old('area') ?>"
                                           placeholder="Ej: Producción, Administración, Ventas...">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="application_mode" class="form-label">Modo de Aplicación *</label>
                                    <select class="form-select" id="application_mode" name="application_mode" required>
                                        <option value="">Seleccione...</option>
                                        <option value="presencial" <?= old('application_mode') == 'presencial' ? 'selected' : '' ?>>
                                            Presencial
                                        </option>
                                        <option value="virtual" <?= old('application_mode') == 'virtual' ? 'selected' : '' ?>>
                                            Virtual (se enviará enlace por email)
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="intralaboral_type" class="form-label">Tipo de Cuestionario Intralaboral *</label>
                                    <select class="form-select" id="intralaboral_type" name="intralaboral_type" required>
                                        <option value="">Seleccione...</option>
                                        <option value="A" <?= old('intralaboral_type') == 'A' ? 'selected' : '' ?>>
                                            Forma A - Jefes, Profesionales, Técnicos
                                        </option>
                                        <option value="B" <?= old('intralaboral_type') == 'B' ? 'selected' : '' ?>>
                                            Forma B - Auxiliares, Operarios
                                        </option>
                                    </select>
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Forma A: para cargos con personal a cargo o responsabilidades profesionales.<br>
                                        Forma B: para cargos auxiliares u operativos.
                                    </small>
                                </div>
                            </div>

                            <div class="alert alert-info">
                                <i class="fas fa-lightbulb me-2"></i>
                                <strong>Nota:</strong> Al crear el trabajador, se generará automáticamente un enlace único
                                para que pueda diligenciar los cuestionarios. Si selecciona modo virtual, se le enviará
                                el enlace por correo electrónico.
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <a href="<?= base_url('workers/service/' . $service['id']) ?>" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Guardar Trabajador
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
