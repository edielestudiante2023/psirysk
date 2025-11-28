<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - PsyRisk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .upload-zone {
            border: 3px dashed #667eea;
            border-radius: 15px;
            padding: 60px 20px;
            text-align: center;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            transition: all 0.3s;
            cursor: pointer;
        }
        .upload-zone:hover {
            border-color: #764ba2;
            background: linear-gradient(135deg, #e0e7ff 0%, #cfd9ff 100%);
        }
        .upload-zone.dragover {
            border-color: #28a745;
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        }
        .file-info {
            display: none;
            margin-top: 20px;
        }
        .csv-example {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('battery-services') ?>">Servicios</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('battery-services/' . $service['id']) ?>"><?= esc($service['service_name']) ?></a></li>
                        <li class="breadcrumb-item active">Cargar Trabajadores</li>
                    </ol>
                </nav>

                <!-- Card Principal -->
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-upload me-2"></i><?= $title ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Info del servicio -->
                        <div class="alert alert-info">
                            <strong><i class="fas fa-info-circle me-2"></i>Servicio:</strong> <?= esc($service['service_name']) ?><br>
                            <strong>Empresa:</strong> <?= esc($service['company_name']) ?><br>
                            <strong>Fecha:</strong> <?= date('d/m/Y', strtotime($service['service_date'])) ?>
                        </div>

                        <?php if (session()->getFlashdata('error')): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <?= session()->getFlashdata('error') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (session()->getFlashdata('warning')): ?>
                            <div class="alert alert-warning alert-dismissible fade show">
                                <?= session()->getFlashdata('warning') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Instrucciones -->
                        <div class="mb-4">
                            <h6 class="fw-bold"><i class="fas fa-file-csv text-success me-2"></i>Formato del archivo CSV</h6>
                            <p class="text-muted mb-3">
                                El archivo CSV debe contener las siguientes columnas en el orden especificado:
                            </p>

                            <div class="csv-example mb-3">
                                <strong>Headers (primera línea):</strong><br>
                                document_type;document;hire_date;name;position;area;email;phone;intralaboral_type;application_mode
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Columna</th>
                                            <th>Descripción</th>
                                            <th>Ejemplo</th>
                                            <th>Obligatorio</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><code>document_type</code></td>
                                            <td>Tipo de documento</td>
                                            <td>CC, PPT</td>
                                            <td>Sí</td>
                                        </tr>
                                        <tr>
                                            <td><code>document</code></td>
                                            <td>Número de documento</td>
                                            <td>92715071</td>
                                            <td>Sí</td>
                                        </tr>
                                        <tr>
                                            <td><code>hire_date</code></td>
                                            <td>Fecha de ingreso</td>
                                            <td>4/8/2025</td>
                                            <td>No</td>
                                        </tr>
                                        <tr>
                                            <td><code>name</code></td>
                                            <td>Nombre completo</td>
                                            <td>ROSA ANGELICA SACRISTAN</td>
                                            <td>Sí</td>
                                        </tr>
                                        <tr>
                                            <td><code>position</code></td>
                                            <td>Cargo</td>
                                            <td>AUXILIAR DE BODEGA</td>
                                            <td>Sí</td>
                                        </tr>
                                        <tr>
                                            <td><code>area</code></td>
                                            <td>Área o departamento</td>
                                            <td>OPERATIVA</td>
                                            <td>No</td>
                                        </tr>
                                        <tr>
                                            <td><code>email</code></td>
                                            <td>Correo electrónico</td>
                                            <td>rosa@empresa.com</td>
                                            <td>Sí</td>
                                        </tr>
                                        <tr>
                                            <td><code>phone</code></td>
                                            <td>Teléfono</td>
                                            <td>3001234567</td>
                                            <td>No</td>
                                        </tr>
                                        <tr class="table-warning">
                                            <td><code>intralaboral_type</code></td>
                                            <td><strong>Tipo de formulario intralaboral</strong></td>
                                            <td><strong>A</strong> o <strong>B</strong></td>
                                            <td><strong>Sí</strong></td>
                                        </tr>
                                        <tr class="table-warning">
                                            <td><code>application_mode</code></td>
                                            <td><strong>Modalidad de aplicación</strong></td>
                                            <td><strong>virtual</strong> o <strong>presencial</strong></td>
                                            <td><strong>Sí</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Importante:</strong>
                                <ul class="mb-0 mt-2">
                                    <li><strong>intralaboral_type</strong>: Use "A" para jefes/profesionales/técnicos y "B" para auxiliares/operarios</li>
                                    <li><strong>application_mode</strong>: Use "virtual" para envío de emails o "presencial" para aplicación manual</li>
                                    <li><strong>Separador de columnas</strong>: Use punto y coma (;) - compatible con Excel en Colombia</li>
                                    <li>Use UTF-8 como codificación del archivo</li>
                                </ul>
                            </div>

                            <div class="text-center mb-3">
                                <a href="<?= base_url('assets/templates/plantilla_trabajadores.csv') ?>"
                                   class="btn btn-outline-primary"
                                   download>
                                    <i class="fas fa-download me-2"></i>Descargar Plantilla CSV de Ejemplo
                                </a>
                            </div>
                        </div>

                        <hr>

                        <!-- Formulario de carga -->
                        <form action="<?= base_url('workers/process-csv/' . $service['id']) ?>"
                              method="POST"
                              enctype="multipart/form-data"
                              id="uploadForm">
                            <?= csrf_field() ?>

                            <div class="upload-zone" id="uploadZone">
                                <i class="fas fa-cloud-upload-alt fa-4x text-primary mb-3"></i>
                                <h5>Arrastra tu archivo CSV aquí</h5>
                                <p class="text-muted">o haz clic para seleccionar</p>
                                <input type="file"
                                       name="csv_file"
                                       id="csvFile"
                                       accept=".csv"
                                       class="d-none"
                                       required>
                            </div>

                            <div class="file-info" id="fileInfo">
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i>
                                    Archivo seleccionado: <strong id="fileName"></strong>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <a href="<?= base_url('battery-services/' . $service['id']) ?>" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-success" id="submitBtn" disabled>
                                    <i class="fas fa-upload me-2"></i>Cargar Trabajadores
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const uploadZone = document.getElementById('uploadZone');
        const fileInput = document.getElementById('csvFile');
        const fileInfo = document.getElementById('fileInfo');
        const fileName = document.getElementById('fileName');
        const submitBtn = document.getElementById('submitBtn');

        // Click en zona de carga
        uploadZone.addEventListener('click', () => {
            fileInput.click();
        });

        // Drag & Drop
        uploadZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadZone.classList.add('dragover');
        });

        uploadZone.addEventListener('dragleave', () => {
            uploadZone.classList.remove('dragover');
        });

        uploadZone.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadZone.classList.remove('dragover');

            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                showFileInfo(files[0]);
            }
        });

        // Cambio de archivo
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                showFileInfo(e.target.files[0]);
            }
        });

        function showFileInfo(file) {
            fileName.textContent = file.name;
            fileInfo.style.display = 'block';
            submitBtn.disabled = false;
        }
    </script>
</body>
</html>
