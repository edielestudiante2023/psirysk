<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - PsyRisk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }
        .form-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 2.5rem;
            max-width: 900px;
            margin: 0 auto;
        }
        .gladiator-header {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            text-align: center;
        }
        .btn-gladiator {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            border: none;
            color: white;
            font-weight: bold;
        }
        .btn-gladiator:hover {
            background: linear-gradient(135deg, #c0392b 0%, #a93226 100%);
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-card">
            <div class="gladiator-header">
                <h2><i class="fas fa-file-contract me-2"></i>Nueva Orden de Servicio</h2>
                <p class="mb-0">Equipo Gladiator - Cycloid Talent</p>
            </div>

            <!-- Errores de validación -->
            <?php if (session()->getFlashdata('errors')): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach (session()->getFlashdata('errors') as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('commercial/store') ?>" method="POST">
                <?= csrf_field() ?>

                <!-- Cliente -->
                <div class="mb-4">
                    <label for="company_id" class="form-label">
                        <i class="fas fa-building me-2"></i>Cliente *
                    </label>
                    <select class="form-select" id="company_id" name="company_id" required>
                        <option value="">Seleccione un cliente...</option>
                        <?php foreach ($companies as $company): ?>
                            <option value="<?= $company['id'] ?>" <?= old('company_id') == $company['id'] ? 'selected' : '' ?>>
                                <?= esc($company['name']) ?> - NIT: <?= esc($company['nit']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Notificar Empresa Gestora (solo visible si aplica) -->
                <div class="mb-4" id="notify_parent_container" style="display: none;">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="notify_parent_company" name="notify_parent_company" value="1">
                        <label class="form-check-label" for="notify_parent_company">
                            <i class="fas fa-building me-2"></i>
                            <span id="parent_company_label">También notificar a la empresa gestora</span>
                        </label>
                        <small class="form-text text-muted d-block mt-1" id="parent_company_info"></small>
                    </div>
                </div>

                <!-- Consultor Asignado -->
                <div class="mb-4">
                    <label for="consultant_id" class="form-label">
                        <i class="fas fa-user-tie me-2"></i>Consultor Asignado *
                    </label>
                    <select class="form-select" id="consultant_id" name="consultant_id" required>
                        <option value="">Seleccione un consultor...</option>
                        <?php foreach ($consultants as $consultant): ?>
                            <option value="<?= $consultant['id'] ?>" <?= old('consultant_id') == $consultant['id'] ? 'selected' : '' ?>>
                                <?= esc($consultant['name']) ?> - <?= esc($consultant['email']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Nombre del Servicio -->
                <div class="mb-4">
                    <label for="service_name" class="form-label">
                        <i class="fas fa-tag me-2"></i>Nombre del Servicio *
                    </label>
                    <input type="text" class="form-control" id="service_name" name="service_name"
                           value="<?= old('service_name') ?>"
                           placeholder="Ej: Batería de Riesgo Psicosocial 2025"
                           required>
                </div>

                <!-- Fecha de Servicio -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="service_date" class="form-label">
                            <i class="fas fa-calendar me-2"></i>Fecha de Servicio *
                        </label>
                        <input type="date" class="form-control" id="service_date" name="service_date"
                               value="<?= old('service_date', date('Y-m-d')) ?>"
                               required>
                    </div>
                    <div class="col-md-6">
                        <label for="link_expiration_days" class="form-label">
                            <i class="fas fa-clock me-2"></i>Días de Vigencia del Enlace *
                        </label>
                        <input type="number" class="form-control" id="link_expiration_days" name="link_expiration_days"
                               value="<?= old('link_expiration_days', 15) ?>"
                               min="1" max="365"
                               required>
                        <small class="text-muted">El enlace expirará en X días desde la fecha de servicio</small>
                    </div>
                </div>

                <!-- Cuestionarios Incluidos -->
                <div class="mb-4">
                    <label class="form-label">
                        <i class="fas fa-clipboard-list me-2"></i>Cuestionarios Incluidos
                    </label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="includes_intralaboral"
                               name="includes_intralaboral" value="1" checked>
                        <label class="form-check-label" for="includes_intralaboral">
                            Cuestionario Intralaboral (Forma A y B)
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="includes_extralaboral"
                               name="includes_extralaboral" value="1" checked>
                        <label class="form-check-label" for="includes_extralaboral">
                            Cuestionario Extralaboral
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="includes_estres"
                               name="includes_estres" value="1" checked>
                        <label class="form-check-label" for="includes_estres">
                            Cuestionario de Estrés
                        </label>
                    </div>
                </div>

                <!-- Cantidades por Tipo de Formulario -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="cantidad_forma_a" class="form-label">
                            <i class="fas fa-users me-2"></i>Cantidad Forma A (Jefes/Profesionales) *
                        </label>
                        <input type="number" class="form-control" id="cantidad_forma_a" name="cantidad_forma_a"
                               value="<?= old('cantidad_forma_a', 0) ?>"
                               min="0"
                               required>
                        <small class="text-muted">Número de trabajadores que responderán Forma A</small>
                    </div>
                    <div class="col-md-6">
                        <label for="cantidad_forma_b" class="form-label">
                            <i class="fas fa-users me-2"></i>Cantidad Forma B (Auxiliares/Operarios) *
                        </label>
                        <input type="number" class="form-control" id="cantidad_forma_b" name="cantidad_forma_b"
                               value="<?= old('cantidad_forma_b', 0) ?>"
                               min="0"
                               required>
                        <small class="text-muted">Número de trabajadores que responderán Forma B</small>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Total de Unidades:</strong> <span id="total_units">0</span>
                        </div>
                    </div>
                </div>

                <!-- Botones -->
                <div class="d-flex justify-content-between mt-4">
                    <a href="<?= base_url('commercial') ?>" class="btn btn-secondary btn-lg">
                        <i class="fas fa-arrow-left me-2"></i>Cancelar
                    </a>
                    <button type="submit" class="btn btn-gladiator btn-lg">
                        <i class="fas fa-paper-plane me-2"></i>Crear Orden y Enviar Email
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        // Datos de empresas con información de parent_company
        const companiesData = <?= json_encode($companies) ?>;

        $(document).ready(function() {
            // Inicializar Select2
            $('#company_id').select2({
                theme: 'bootstrap-5',
                placeholder: 'Seleccione un cliente...',
                allowClear: true
            });

            $('#consultant_id').select2({
                theme: 'bootstrap-5',
                placeholder: 'Seleccione un consultor...',
                allowClear: true
            });

            // Manejar cambio de empresa seleccionada
            $('#company_id').on('change', function() {
                const companyId = $(this).val();
                const notifyContainer = $('#notify_parent_container');
                const notifyCheckbox = $('#notify_parent_company');
                const parentLabel = $('#parent_company_label');
                const parentInfo = $('#parent_company_info');

                if (!companyId) {
                    notifyContainer.hide();
                    notifyCheckbox.prop('checked', false);
                    return;
                }

                // Buscar la empresa en el array
                const company = companiesData.find(c => c.id == companyId);

                if (company && company.parent_company_id && company.parent_company_name) {
                    // La empresa tiene una gestora
                    parentLabel.text(`También notificar a la empresa gestora: ${company.parent_company_name}`);

                    if (company.parent_contact_email) {
                        parentInfo.html(`<i class="fas fa-envelope me-1"></i> Email: ${company.parent_contact_email}`);
                    } else {
                        parentInfo.html(`<i class="fas fa-exclamation-triangle me-1"></i> La empresa gestora no tiene email de contacto configurado`);
                    }

                    notifyContainer.fadeIn();
                } else {
                    // La empresa NO tiene gestora
                    notifyContainer.hide();
                    notifyCheckbox.prop('checked', false);
                }
            });
        });

        // Calcular total de unidades
        function updateTotalUnits() {
            const formaA = parseInt(document.getElementById('cantidad_forma_a').value) || 0;
            const formaB = parseInt(document.getElementById('cantidad_forma_b').value) || 0;
            const total = formaA + formaB;
            document.getElementById('total_units').textContent = total;
        }

        // Agregar event listeners
        document.getElementById('cantidad_forma_a').addEventListener('input', updateTotalUnits);
        document.getElementById('cantidad_forma_b').addEventListener('input', updateTotalUnits);

        // Calcular inicial
        updateTotalUnits();
    </script>
</body>
</html>
