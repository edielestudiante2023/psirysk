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
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-edit me-2"></i><?= $title ?></h5>
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

                        <form action="<?= base_url('companies/update/' . $company['id']) ?>" method="POST" enctype="multipart/form-data">
                            <?= csrf_field() ?>

                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label for="name" class="form-label">Nombre de la Empresa *</label>
                                    <input type="text"
                                           class="form-control"
                                           id="name"
                                           name="name"
                                           value="<?= old('name', $company['name']) ?>"
                                           required>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="nit" class="form-label">NIT *</label>
                                    <input type="text"
                                           class="form-control"
                                           id="nit"
                                           name="nit"
                                           value="<?= old('nit', $company['nit']) ?>"
                                           required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="type" class="form-label">Tipo de Empresa *</label>
                                    <select class="form-select" id="type" name="type" required>
                                        <option value="gestor_multicompania" <?= old('type', $company['type']) === 'gestor_multicompania' ? 'selected' : '' ?>>
                                            Gestor Multicompañía
                                        </option>
                                        <option value="empresa_individual" <?= old('type', $company['type']) === 'empresa_individual' ? 'selected' : '' ?>>
                                            Empresa Individual
                                        </option>
                                    </select>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="parent_company_id" class="form-label">Gestionada por</label>
                                    <select class="form-select" id="parent_company_id" name="parent_company_id">
                                        <option value="">Sin gestor</option>
                                        <?php foreach ($gestores as $gestor): ?>
                                            <option value="<?= $gestor['id'] ?>"
                                                    <?= old('parent_company_id', $company['parent_company_id']) == $gestor['id'] ? 'selected' : '' ?>>
                                                <?= esc($gestor['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="status" class="form-label">Estado *</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="active" <?= old('status', $company['status']) === 'active' ? 'selected' : '' ?>>
                                            Activo
                                        </option>
                                        <option value="inactive" <?= old('status', $company['status']) === 'inactive' ? 'selected' : '' ?>>
                                            Inactivo
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="address" class="form-label">Dirección</label>
                                <textarea class="form-control"
                                          id="address"
                                          name="address"
                                          rows="2"><?= old('address', $company['address']) ?></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="phone" class="form-label">Teléfono</label>
                                    <input type="text"
                                           class="form-control"
                                           id="phone"
                                           name="phone"
                                           value="<?= old('phone', $company['phone']) ?>">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="contact_name" class="form-label">Nombre de Contacto</label>
                                    <input type="text"
                                           class="form-control"
                                           id="contact_name"
                                           name="contact_name"
                                           value="<?= old('contact_name', $company['contact_name']) ?>">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="contact_email" class="form-label">Email de Contacto</label>
                                    <input type="email"
                                           class="form-control"
                                           id="contact_email"
                                           name="contact_email"
                                           value="<?= old('contact_email', $company['contact_email']) ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="logo" class="form-label">Logo de la Empresa</label>
                                <?php if (!empty($company['logo_path'])): ?>
                                <div class="mb-2">
                                    <img src="<?= base_url($company['logo_path']) ?>" alt="Logo actual" class="img-thumbnail" style="max-height: 100px;">
                                    <small class="d-block text-muted">Logo actual</small>
                                </div>
                                <?php endif; ?>
                                <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                                <small class="text-muted">Formatos: JPG, PNG, GIF. Tamaño recomendado: 200x200px</small>
                            </div>

                            <hr>

                            <div class="d-flex justify-content-between">
                                <a href="<?= base_url('companies') ?>" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-warning text-dark">
                                    <i class="fas fa-save me-2"></i>Actualizar Empresa
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
