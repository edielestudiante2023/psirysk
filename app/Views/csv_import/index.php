<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - PsyRisk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            margin: 5px 15px;
            border-radius: 8px;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background-color: rgba(255,255,255,0.2);
            color: white;
        }
        .navbar-custom {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .upload-zone {
            border: 3px dashed #667eea;
            border-radius: 15px;
            padding: 40px;
            text-align: center;
            background-color: #f8f9ff;
            transition: all 0.3s;
            cursor: pointer;
        }
        .upload-zone:hover {
            background-color: #e8ebff;
            border-color: #5568d3;
        }
        .alert-warning {
            border-left: 4px solid #ffc107;
        }
        .alert-info {
            border-left: 4px solid #17a2b8;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar p-0">
                <div class="p-4">
                    <h3 class="fw-bold">PsyRisk</h3>
                    <p class="small mb-0"><?= session()->get('name') ?></p>
                    <small class="text-white-50">Consultor RPS</small>
                </div>

                <nav class="nav flex-column mt-4">
                    <a class="nav-link" href="<?= base_url('dashboard') ?>">
                        <i class="fas fa-home me-2"></i> Dashboard
                    </a>
                    <a class="nav-link" href="<?= base_url('companies') ?>">
                        <i class="fas fa-building me-2"></i> Clientes
                    </a>
                    <a class="nav-link" href="<?= base_url('battery-services') ?>">
                        <i class="fas fa-clipboard-check me-2"></i> Servicios de Bateria
                    </a>
                    <a class="nav-link" href="<?= base_url('workers') ?>">
                        <i class="fas fa-users me-2"></i> Trabajadores
                    </a>
                    <a class="nav-link active" href="<?= base_url('csv-import') ?>">
                        <i class="fas fa-file-csv me-2"></i> Importar CSV
                    </a>
                    <hr class="text-white-50 mx-3">
                    <a class="nav-link" href="<?= base_url('logout') ?>">
                        <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesion
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 p-0">
                <!-- Top Navbar -->
                <nav class="navbar navbar-custom navbar-expand-lg p-3">
                    <div class="container-fluid">
                        <h4 class="mb-0"><i class="fas fa-life-ring text-warning me-2"></i>Modulo de Contingencia - Importacion CSV</h4>
                        <div class="ms-auto">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="fas fa-download me-2"></i>Descargar Plantilla
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="<?= base_url('csv-import/download-template-forma-a') ?>">
                                            <i class="fas fa-file-csv text-success me-2"></i>Forma A (Jefes/Profesionales)
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="<?= base_url('csv-import/download-template-forma-b') ?>">
                                            <i class="fas fa-file-csv text-info me-2"></i>Forma B (Auxiliares/Operarios)
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </nav>

                <!-- Content -->
                <div class="p-4">
                    <!-- Warning Alert -->
                    <div class="alert alert-warning shadow-sm">
                        <h5><i class="fas fa-exclamation-triangle me-2"></i>Modulo de Contingencia</h5>
                        <p class="mb-0">Este modulo permite importar respuestas desde archivos CSV cuando el sistema principal no esta disponible (ej: caida de Cloudflare, problemas con el servidor, etc.)</p>
                        <small class="text-muted">Usalo solo en casos de emergencia cuando necesites migrar datos desde LimeSurvey u otro sistema externo.</small>
                    </div>

                    <!-- Instrucciones Paso a Paso -->
                    <div class="alert alert-primary shadow-sm">
                        <h5><i class="fas fa-list-ol me-2"></i>Paso a Paso - IMPORTANTE</h5>
                        <ol class="mb-0">
                            <li class="mb-2">
                                <strong>CREAR SERVICIO:</strong> Ve a "Servicios de Bateria" y crea un nuevo servicio (o usa uno existente)
                            </li>
                            <li class="mb-2">
                                <strong>CARGAR TRABAJADORES:</strong> Desde el servicio, ve a "Trabajadores" y carga el CSV con los datos de los trabajadores
                                <br><small class="text-muted">Este paso es OBLIGATORIO. Sin trabajadores en el sistema, no se puede importar respuestas.</small>
                            </li>
                            <li class="mb-2">
                                <strong>DESCARGAR PLANTILLA:</strong> Descarga la plantilla de Forma A o Forma B segun el tipo de trabajadores
                            </li>
                            <li class="mb-2">
                                <strong>LLENAR CSV:</strong> Completa el CSV con las respuestas. Los documentos en el CSV <strong>DEBEN coincidir exactamente</strong> con los documentos de los trabajadores ya cargados en el servicio
                            </li>
                            <li class="mb-0">
                                <strong>IMPORTAR RESPUESTAS:</strong> Usa el formulario abajo para cargar el CSV con las respuestas
                            </li>
                        </ol>
                    </div>

                    <!-- Info Alert -->
                    <div class="alert alert-info shadow-sm">
                        <h6><i class="fas fa-info-circle me-2"></i>Formato del CSV</h6>
                        <p class="mb-1">Formato matriz: un trabajador por fila, todas las respuestas en columnas.</p>
                        <ul class="mb-0">
                            <li><strong>Estructura:</strong> <code>documento</code>, <code>nombre</code>, <strong>Ficha de Datos (22 campos)</strong>, <code>intralaboral_1</code>..., <code>extralaboral_1</code>..., <code>estres_1</code>...</li>
                            <li><strong>Ficha de Datos Generales (ANEXO 3):</strong> Datos sociodemograficos del trabajador (genero, edad, educacion, cargo, etc.)</li>
                            <li><strong>Respuestas en texto:</strong> Siempre, Casi siempre, Algunas veces, Casi nunca, Nunca</li>
                            <li><strong>Forma A:</strong> 22 datos + 123 intralaboral (con atiende_clientes y es_jefe intercalados) + 31 extralaboral + 31 estres</li>
                            <li><strong>Forma B:</strong> 22 datos + 97 intralaboral (con atiende_clientes intercalado) + 31 extralaboral + 31 estres</li>
                            <li><strong>Compatible:</strong> Excel y Google Sheets</li>
                        </ul>
                    </div>

                    <!-- Upload Form -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0"><i class="fas fa-upload text-primary me-2"></i>Cargar Archivo CSV</h5>
                        </div>
                        <div class="card-body">
                            <?= form_open_multipart('csv-import/upload', ['id' => 'uploadForm']) ?>
                                <div class="mb-3">
                                    <label for="battery_service_id" class="form-label">Seleccionar Servicio</label>
                                    <select class="form-select" id="battery_service_id" name="battery_service_id" required>
                                        <option value="">-- Seleccione un servicio --</option>
                                        <?php foreach ($services as $service): ?>
                                            <option value="<?= $service['id'] ?>">
                                                <?= esc($service['service_name']) ?> - <?= date('d/m/Y', strtotime($service['service_date'])) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="form-text text-muted">Solo se muestran servicios en curso</small>
                                </div>

                                <input type="hidden" name="csv_format" value="horizontal">

                                <div class="mb-3">
                                    <label for="form_type" class="form-label">Tipo de Formulario</label>
                                    <select class="form-select" id="form_type" name="form_type" required>
                                        <option value="">-- Seleccione el tipo de formulario --</option>
                                        <option value="A">Forma A (Jefes/Profesionales - 123 preguntas intralaboral)</option>
                                        <option value="B">Forma B (Auxiliares/Operarios - 97 preguntas intralaboral)</option>
                                    </select>
                                    <small class="form-text text-muted">Seleccione el tipo de formulario que corresponde al CSV que va a cargar</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Archivo CSV</label>
                                    <div class="upload-zone" onclick="document.getElementById('csv_file').click()">
                                        <i class="fas fa-cloud-upload-alt fa-4x text-primary mb-3"></i>
                                        <h5>Haz clic o arrastra el archivo aqui</h5>
                                        <p class="text-muted">Solo archivos CSV (maximo 10MB)</p>
                                        <input type="file" class="form-control d-none" id="csv_file" name="csv_file" accept=".csv,.txt" required>
                                        <p id="file_name" class="text-success fw-bold mt-2"></p>
                                    </div>
                                </div>

                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                        <i class="fas fa-upload me-2"></i>Importar CSV
                                    </button>
                                </div>
                            <?= form_close() ?>
                        </div>
                    </div>

                    <!-- Import History -->
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0"><i class="fas fa-history text-primary me-2"></i>Historial de Importaciones</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($imports)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-file-csv fa-4x text-muted mb-3"></i>
                                    <p class="text-muted">No hay importaciones registradas aun.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Servicio</th>
                                                <th>Tipo</th>
                                                <th>Archivo</th>
                                                <th>Total Filas</th>
                                                <th>Importadas</th>
                                                <th>Fallidas</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($imports as $import): ?>
                                                <tr>
                                                    <td><?= date('d/m/Y H:i', strtotime($import['created_at'])) ?></td>
                                                    <td><?= esc($import['service_name']) ?></td>
                                                    <td>
                                                        <span class="badge bg-<?= ($import['form_type'] ?? '') === 'A' ? 'primary' : 'info' ?>">
                                                            Forma <?= $import['form_type'] ?? '?' ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <i class="fas fa-file-csv text-success me-1"></i>
                                                        <?= esc($import['file_name']) ?>
                                                    </td>
                                                    <td><?= $import['total_rows'] ?></td>
                                                    <td>
                                                        <span class="badge bg-success"><?= $import['imported_rows'] ?></span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-<?= $import['failed_rows'] > 0 ? 'warning' : 'secondary' ?>">
                                                            <?= $import['failed_rows'] ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        $badgeColor = [
                                                            'procesando' => 'primary',
                                                            'completado' => 'success',
                                                            'completado_con_errores' => 'warning',
                                                            'error' => 'danger'
                                                        ];
                                                        ?>
                                                        <span class="badge bg-<?= $badgeColor[$import['status']] ?? 'secondary' ?>">
                                                            <?= ucfirst(str_replace('_', ' ', $import['status'])) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                                onclick="deleteImport(<?= $import['id'] ?>, '<?= esc($import['service_name']) ?>')"
                                                                title="Eliminar importacion">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
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
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mostrar SweetAlert si hay mensaje de exito
        <?php if (session()->getFlashdata('success')): ?>
        window.scrollTo(0, 0);
        Swal.fire({
            icon: 'success',
            title: 'Importacion Exitosa',
            html: '<?= session()->getFlashdata('success') ?>',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#28a745'
        });
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
        window.scrollTo(0, 0);
        Swal.fire({
            icon: 'error',
            title: 'Error en Importacion',
            html: '<?= session()->getFlashdata('error') ?>',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#dc3545'
        });
        <?php endif; ?>

        // Mostrar nombre del archivo seleccionado
        document.getElementById('csv_file').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name;
            const fileNameDisplay = document.getElementById('file_name');
            if (fileName) {
                fileNameDisplay.textContent = '📄 ' + fileName;
            }
        });

        // Drag and drop
        const uploadZone = document.querySelector('.upload-zone');

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadZone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            uploadZone.addEventListener(eventName, () => {
                uploadZone.style.borderColor = '#5568d3';
                uploadZone.style.backgroundColor = '#e0e3ff';
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadZone.addEventListener(eventName, () => {
                uploadZone.style.borderColor = '#667eea';
                uploadZone.style.backgroundColor = '#f8f9ff';
            }, false);
        });

        uploadZone.addEventListener('drop', function(e) {
            const dt = e.dataTransfer;
            const files = dt.files;

            if (files.length > 0) {
                document.getElementById('csv_file').files = files;
                document.getElementById('file_name').textContent = '📄 ' + files[0].name;
            }
        });

        // Mostrar loading al enviar formulario
        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Importando...';

            Swal.fire({
                title: 'Procesando...',
                html: 'Importando datos del CSV. Por favor espere.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        });

        // Funcion para eliminar importacion
        function deleteImport(importId, serviceName) {
            Swal.fire({
                title: 'Eliminar Importacion?',
                html: `<p>Esta accion eliminara:</p>
                       <ul class="text-start">
                         <li>Todas las respuestas importadas</li>
                         <li>Todos los resultados calculados</li>
                         <li>El registro de la importacion</li>
                       </ul>
                       <p class="text-danger fw-bold">Servicio: ${serviceName}</p>
                       <p class="text-danger">Esta accion NO se puede deshacer.</p>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Si, eliminar todo',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Eliminando...',
                        html: 'Por favor espere...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    fetch(`<?= base_url('csv-import/delete') ?>/${importId}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Eliminado',
                                text: data.message,
                                confirmButtonColor: '#28a745'
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message,
                                confirmButtonColor: '#dc3545'
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error de conexion: ' + error.message,
                            confirmButtonColor: '#dc3545'
                        });
                    });
                }
            });
        }
    </script>
</body>
</html>
