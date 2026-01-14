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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

                        <form id="editServiceForm" action="<?= base_url('battery-services/update/' . $service['id']) ?>" method="POST">
                            <?= csrf_field() ?>
                            <input type="hidden" id="current_status" value="<?= $service['status'] ?>">

                            <div class="mb-3">
                                <label for="company_id" class="form-label">Empresa Cliente *</label>
                                <select class="form-select" id="company_id" name="company_id" required>
                                    <option value="">Seleccione una empresa...</option>
                                    <?php foreach ($companies as $company): ?>
                                        <option value="<?= $company['id'] ?>"
                                                <?= old('company_id', $service['company_id']) == $company['id'] ? 'selected' : '' ?>
                                                data-parent-id="<?= $company['parent_company_id'] ?? '' ?>"
                                                data-parent-name="<?= $company['parent_company_name'] ?? '' ?>"
                                                data-parent-email="<?= $company['parent_contact_email'] ?? '' ?>">
                                            <?= esc($company['name']) ?> (NIT: <?= esc($company['nit']) ?>)
                                            <?php if (!empty($company['parent_company_name'])): ?>
                                                - Gestora: <?= esc($company['parent_company_name']) ?>
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Notificar Empresa Gestora (solo visible si aplica) -->
                            <div class="mb-3" id="notify_parent_container" style="display: none;">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="notify_parent_company" name="notify_parent_company" value="1"
                                           <?= old('notify_parent_company', $service['notify_parent_company'] ?? 0) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="notify_parent_company">
                                        <i class="fas fa-building me-2"></i>
                                        <span id="parent_company_label">También notificar a la empresa gestora</span>
                                    </label>
                                    <small class="form-text text-muted d-block mt-1" id="parent_company_info"></small>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="consultant_id" class="form-label">Consultor Asignado *</label>
                                <select class="form-select" id="consultant_id" name="consultant_id" required>
                                    <option value="">Seleccione un consultor...</option>
                                    <?php foreach ($consultants as $consultant): ?>
                                        <option value="<?= $consultant['id'] ?>"
                                                <?= old('consultant_id', $service['consultant_id']) == $consultant['id'] ? 'selected' : '' ?>>
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
                            </div>

                            <div class="mb-3">
                                <label for="service_name" class="form-label">Nombre del Servicio *</label>
                                <input type="text"
                                       class="form-control"
                                       id="service_name"
                                       name="service_name"
                                       value="<?= old('service_name', $service['service_name']) ?>"
                                       required>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="service_date" class="form-label">Fecha del Servicio *</label>
                                    <input type="date"
                                           class="form-control"
                                           id="service_date"
                                           name="service_date"
                                           value="<?= old('service_date', $service['service_date']) ?>"
                                           required>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="expiration_date" class="form-label">Fecha de Vencimiento *</label>
                                    <input type="date"
                                           class="form-control"
                                           id="expiration_date"
                                           name="expiration_date"
                                           value="<?= old('expiration_date', $service['link_expiration_date']) ?>"
                                           required>
                                    <small class="text-muted">Fecha límite para completar las evaluaciones</small>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="status" class="form-label">Estado *</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="planificado" <?= old('status', $service['status']) === 'planificado' ? 'selected' : '' ?>>
                                            Planificado
                                        </option>
                                        <option value="en_curso" <?= old('status', $service['status']) === 'en_curso' ? 'selected' : '' ?>>
                                            En Curso
                                        </option>
                                        <option value="finalizado" <?= old('status', $service['status']) === 'finalizado' ? 'selected' : '' ?>>
                                            Finalizado
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <!-- Cantidades por Tipo de Formulario -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="cantidad_forma_a" class="form-label">
                                        <i class="fas fa-users me-2"></i>Cantidad Forma A (Jefes/Profesionales) *
                                    </label>
                                    <input type="number" class="form-control" id="cantidad_forma_a" name="cantidad_forma_a"
                                           value="<?= old('cantidad_forma_a', $service['cantidad_forma_a'] ?? 0) ?>"
                                           min="0"
                                           required>
                                    <small class="text-muted">Número de trabajadores que responderán Forma A</small>
                                </div>
                                <div class="col-md-6">
                                    <label for="cantidad_forma_b" class="form-label">
                                        <i class="fas fa-users me-2"></i>Cantidad Forma B (Auxiliares/Operarios) *
                                    </label>
                                    <input type="number" class="form-control" id="cantidad_forma_b" name="cantidad_forma_b"
                                           value="<?= old('cantidad_forma_b', $service['cantidad_forma_b'] ?? 0) ?>"
                                           min="0"
                                           required>
                                    <small class="text-muted">Número de trabajadores que responderán Forma B</small>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Total de Unidades:</strong> <span id="total_units">0</span>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <div class="d-flex justify-content-between">
                                <a href="<?= base_url('battery-services') ?>" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-warning text-dark">
                                    <i class="fas fa-save me-2"></i>Actualizar Servicio
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        // Datos de empresas con información de parent_company
        const companiesData = <?= json_encode($companies) ?>;

        $(document).ready(function() {
            // Inicializar Select2 para empresa
            $('#company_id').select2({
                theme: 'bootstrap-5',
                placeholder: 'Seleccione una empresa...',
                allowClear: true
            });

            // Inicializar Select2 para consultor
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

            // Ejecutar al cargar la página para mostrar el campo si ya hay una empresa seleccionada
            $('#company_id').trigger('change');
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

        // Validación al cambiar a "Finalizado"
        const serviceId = <?= $service['id'] ?>;
        const currentStatus = document.getElementById('current_status').value;

        document.getElementById('editServiceForm').addEventListener('submit', async function(e) {
            const newStatus = document.getElementById('status').value;

            // Solo validar si se está cambiando a "finalizado" y no era finalizado antes
            if (newStatus === 'finalizado' && currentStatus !== 'finalizado') {
                e.preventDefault(); // Prevenir envío del formulario

                // Mostrar loading
                Swal.fire({
                    title: 'Verificando...',
                    text: 'Comprobando estado de los trabajadores',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {
                    const response = await fetch(`<?= base_url('battery-services/check-can-finalize/') ?>${serviceId}`);
                    const result = await response.json();

                    if (!result.success) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: result.message
                        });
                        return;
                    }

                    if (!result.canFinalize) {
                        // Mostrar alerta con detalles
                        Swal.fire({
                            icon: 'error',
                            title: 'No es posible finalizar',
                            html: `
                                <div class="text-start">
                                    <p><strong>Hay trabajadores sin resolver:</strong></p>
                                    <ul>
                                        <li><strong>${result.pendientes}</strong> trabajador(es) en estado <span class="badge bg-secondary">Pendiente</span></li>
                                        <li><strong>${result.enProgreso}</strong> trabajador(es) en estado <span class="badge bg-warning text-dark">En Progreso</span></li>
                                    </ul>
                                    <hr>
                                    <p class="mb-0">Antes de finalizar debe:</p>
                                    <ol class="mb-0">
                                        <li>Esperar que completen la evaluación, o</li>
                                        <li>Marcarlos como <strong>"No Participó"</strong> desde la vista de trabajadores</li>
                                    </ol>
                                </div>
                            `,
                            confirmButtonText: 'Ir a Trabajadores',
                            showCancelButton: true,
                            cancelButtonText: 'Cerrar'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = '<?= base_url('workers/service/' . $service['id']) ?>';
                            }
                        });
                        return;
                    }

                    // Puede finalizar - confirmar acción
                    Swal.fire({
                        icon: 'success',
                        title: 'Listo para finalizar',
                        html: `
                            <div class="text-start">
                                <p><strong>Resumen de trabajadores:</strong></p>
                                <ul>
                                    <li><strong>${result.completados}</strong> completado(s)</li>
                                    <li><strong>${result.noParticipo}</strong> marcado(s) como "No Participó"</li>
                                </ul>
                                <p class="mb-0">¿Desea finalizar el servicio?</p>
                            </div>
                        `,
                        showCancelButton: true,
                        confirmButtonText: 'Sí, Finalizar',
                        cancelButtonText: 'Cancelar',
                        confirmButtonColor: '#28a745'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Enviar el formulario
                            document.getElementById('editServiceForm').submit();
                        }
                    });

                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de conexión',
                        text: 'No se pudo verificar el estado de los trabajadores: ' + error.message
                    });
                }
            }
            // Si no es cambio a finalizado, el formulario se envía normalmente
        });
    </script>
</body>
</html>
