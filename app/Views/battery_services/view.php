<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - PsyRisk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #f5f7fa 0%, #e4e8ec 100%); min-height: 100vh; }
        .header-banner {
            background: linear-gradient(135deg, #1a365d 0%, #2c5282 50%, #3182ce 100%);
            color: white;
            padding: 1.5rem 0;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .info-badge {
            background: rgba(255,255,255,0.2);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            display: inline-block;
            margin: 0.25rem;
        }
        .section-title {
            display: flex;
            align-items: center;
            padding: 1rem 1.25rem;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 10px;
            margin-bottom: 1rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .section-title.danger { border-left: 4px solid #dc3545; }
        .section-title.primary { border-left: 4px solid #0d6efd; }
        .section-title.success { border-left: 4px solid #198754; }
        .section-title h5 { margin: 0; font-weight: 600; }

        .action-card {
            border: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            height: 100%;
            overflow: hidden;
        }
        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        .action-card .card-header {
            border: none;
            padding: 1rem;
        }
        .action-card .card-body {
            padding: 1rem;
        }
        .action-card .btn {
            border-radius: 8px;
            font-weight: 500;
        }
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }
        .quick-action-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            padding: 1rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            border: 2px solid transparent;
        }
        .quick-action-btn:hover {
            transform: scale(1.02);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .quick-action-btn i { font-size: 1.25rem; }

        .pdf-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1rem;
        }
        .pdf-card {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        .pdf-card:hover {
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        .pdf-card .card-header {
            padding: 0.75rem 1rem;
            font-size: 0.9rem;
        }
        .pdf-card .card-body {
            padding: 0.75rem 1rem;
        }
        .pdf-card .btn-sm {
            padding: 0.4rem 0.75rem;
            font-size: 0.8rem;
        }

        .informe-destacado {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 25px rgba(0,0,0,0.15);
        }
        .informe-destacado .card-header {
            padding: 1.25rem 1.5rem;
        }

        .recommendations-accordion .accordion-button {
            font-weight: 600;
            font-size: 1.1rem;
            padding: 1.25rem 1.5rem;
            background: linear-gradient(135deg, #fff3cd 0%, #ffeeba 100%);
            border: 2px solid #ffc107;
        }
        .recommendations-accordion .accordion-button:not(.collapsed) {
            background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
            color: #212529;
            box-shadow: none;
        }
        .recommendations-accordion .accordion-button::after {
            filter: brightness(0);
        }

        .nav-footer {
            background: white;
            border-radius: 10px;
            padding: 1rem;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
        }

        .content-section {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
        }

        /* Loading overlay para descargas */
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 9999;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }
        .loading-overlay.active {
            display: flex;
        }
        .loading-spinner {
            width: 80px;
            height: 80px;
            border: 6px solid rgba(255, 255, 255, 0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .loading-text {
            color: white;
            margin-top: 20px;
            font-size: 1.2rem;
            text-align: center;
        }
        .loading-subtext {
            color: rgba(255, 255, 255, 0.7);
            margin-top: 10px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <!-- Loading Overlay para descargas PDF -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
        <div class="loading-text">Generando PDF...</div>
        <div class="loading-subtext">Este proceso puede tomar hasta 30 segundos</div>
    </div>

    <!-- Header Banner -->
    <div class="header-banner">
        <div class="container-fluid px-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2" style="background: transparent;">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-white-50" target="_blank">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('battery-services') ?>" class="text-white-50" target="_blank">Servicios</a></li>
                            <li class="breadcrumb-item text-white"><?= esc($service['service_name']) ?></li>
                        </ol>
                    </nav>
                    <h3 class="mb-1"><i class="fas fa-clipboard-check me-2"></i><?= esc($service['service_name']) ?></h3>
                    <p class="mb-0 opacity-75"><?= esc($service['company_name']) ?> - NIT: <?= esc($service['nit']) ?></p>
                </div>
                <div class="text-end">
                    <div class="info-badge mb-2">
                        <i class="fas fa-calendar me-1"></i> <?= date('d/m/Y', strtotime($service['service_date'])) ?>
                    </div>
                    <?php
                    $daysLeft = floor((strtotime($service['link_expiration_date']) - time()) / 86400);
                    if ($daysLeft < 0): ?>
                        <div class="info-badge bg-danger"><i class="fas fa-exclamation-triangle me-1"></i> Expirado hace <?= abs($daysLeft) ?> días</div>
                    <?php elseif ($daysLeft <= 3): ?>
                        <div class="info-badge bg-warning text-dark"><i class="fas fa-clock me-1"></i> Expira en <?= $daysLeft ?> días</div>
                    <?php else: ?>
                        <div class="info-badge bg-success"><i class="fas fa-check-circle me-1"></i> <?= $daysLeft ?> días activo</div>
                    <?php endif; ?>
                    <span class="badge bg-light text-dark ms-2 py-2 px-3"><?= ucfirst($service['status']) ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid px-4">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('warning')): ?>
            <div class="alert alert-warning alert-dismissible fade show">
                <?= session()->getFlashdata('warning') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Acciones Rápidas -->
        <div class="content-section">
            <div class="section-title primary mb-3">
                <i class="fas fa-bolt fa-lg text-primary me-3"></i>
                <h5>Acciones Rápidas</h5>
            </div>
            <div class="quick-actions">
                <a href="<?= base_url('workers/upload/' . $service['id']) ?>" class="quick-action-btn btn btn-primary text-white" target="_blank">
                    <i class="fas fa-upload"></i> Cargar Trabajadores
                </a>
                <a href="<?= base_url('workers/service/' . $service['id']) ?>" class="quick-action-btn btn btn-success text-white" target="_blank">
                    <i class="fas fa-users"></i> Ver Trabajadores
                </a>
                <a href="<?= base_url('battery-services/global-gauges/' . $service['id']) ?>" class="quick-action-btn btn btn-info text-white" target="_blank">
                    <i class="fas fa-gauge-high"></i> Gráficos Globales
                </a>
                <a href="<?= base_url('reports/heatmap/' . $service['id']) ?>" class="quick-action-btn btn btn-danger text-white" target="_blank">
                    <i class="fas fa-fire"></i> Mapa de Calor General
                </a>
                <a href="<?= base_url('reports/consolidacion/' . $service['id']) ?>" class="quick-action-btn btn btn-warning" target="_blank">
                    <i class="fas fa-chart-bar"></i> Consolidación Grupal
                </a>
                <a href="<?= base_url('reports/ficha-datos-generales/' . $service['id']) ?>" class="quick-action-btn btn btn-secondary text-white" target="_blank">
                    <i class="fas fa-id-card"></i> Ficha Demográfica
                </a>
                <?php if (session()->get('role_name') === 'consultor' || session()->get('role_name') === 'superadmin'): ?>
                <a href="<?= base_url('report-sections/' . $service['id']) ?>" class="quick-action-btn text-white" style="background: #6f42c1;" target="_blank">
                    <i class="fas fa-robot"></i> Informe con IA
                </a>
                <a href="<?= base_url('demographics-report/' . $service['id']) ?>" class="quick-action-btn text-white" style="background: #9B59B6;" target="_blank">
                    <i class="fas fa-brain"></i> Interpretación IA
                </a>
                <a href="<?= base_url('max-risk/' . $service['id']) ?>" class="quick-action-btn text-white" style="background: linear-gradient(135deg, #e53e3e, #c53030);" target="_blank">
                    <i class="fas fa-chart-line"></i> Conclusión Total RPS
                </a>
                <a href="<?= base_url('validation/' . $service['id']) ?>" class="quick-action-btn btn btn-dark text-white" target="_blank">
                    <i class="fas fa-shield-check"></i> Validar Resultados
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- VALIDACIÓN DE PREGUNTAS CONDICIONALES -->
        <?php if (session()->get('role_name') === 'consultor' || session()->get('role_name') === 'superadmin'): ?>
        <div class="content-section">
            <div class="section-title" style="border-left: 4px solid #fd7e14; background: linear-gradient(135deg, #fff8e1 0%, #ffe5cc 100%);">
                <i class="fas fa-exclamation-triangle fa-lg me-3" style="color: #fd7e14;"></i>
                <h5>Validación de Preguntas Condicionales (Cumplimiento Legal)</h5>
                <span class="badge bg-warning text-dark ms-auto">3 validaciones</span>
            </div>

            <div class="alert alert-warning mb-3">
                <h6 class="mb-2"><i class="fas fa-info-circle me-2"></i>Importante</h6>
                <p class="mb-1">
                    Los cuestionarios intralaboral A y B contienen <strong>preguntas condicionales</strong> (Si/No) que habilitan o deshabilitan
                    segmentos de ítems. Es crítico validar las respuestas para evitar inconsistencias legales.
                </p>
                <small><strong>Ejemplo:</strong> Un jefe con personal a cargo que responda "NO" a la pregunta "Soy jefe de otras personas"
                NO responderá los 9 ítems de "Relación con Colaboradores", lo cual puede tener implicaciones legales.</small>
            </div>

            <div class="row g-3">
                <!-- Forma A - Pregunta I -->
                <div class="col-md-4">
                    <div class="action-card card border-info">
                        <div class="card-header bg-info bg-opacity-10">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-users text-info me-2"></i>
                                <strong>Forma A - Pregunta I</strong>
                            </div>
                        </div>
                        <div class="card-body">
                            <h6 class="mb-2">Atención a Clientes</h6>
                            <p class="small text-muted mb-3">
                                "¿En mi trabajo debo brindar servicio a clientes o usuarios?"
                            </p>
                            <p class="small mb-3">
                                <i class="fas fa-info-circle text-info me-1"></i>
                                Controla ítems 106-114 (9 preguntas)
                            </p>
                            <a href="<?= base_url('validation/conditional/forma-a-i/' . $service['id']) ?>"
                               class="btn btn-info btn-sm w-100" target="_blank">
                                <i class="fas fa-list-check me-1"></i> Ver Respuestas
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Forma A - Pregunta II -->
                <div class="col-md-4">
                    <div class="action-card card border-danger">
                        <div class="card-header bg-danger bg-opacity-10">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-user-tie text-danger me-2"></i>
                                <strong>Forma A - Pregunta II</strong>
                                <span class="badge bg-danger ms-auto">CRÍTICO</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <h6 class="mb-2">Jefatura de Personal</h6>
                            <p class="small text-muted mb-3">
                                "¿Soy jefe de otras personas en mi trabajo?"
                            </p>
                            <p class="small mb-3">
                                <i class="fas fa-exclamation-triangle text-danger me-1"></i>
                                Controla ítems 115-123 (9 preguntas sobre relación con colaboradores)
                            </p>
                            <a href="<?= base_url('validation/conditional/forma-a-ii/' . $service['id']) ?>"
                               class="btn btn-danger btn-sm w-100" target="_blank">
                                <i class="fas fa-shield-exclamation me-1"></i> Validar (Legal)
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Forma B - Pregunta I -->
                <div class="col-md-4">
                    <div class="action-card card border-success">
                        <div class="card-header bg-success bg-opacity-10">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-headset text-success me-2"></i>
                                <strong>Forma B - Pregunta I</strong>
                            </div>
                        </div>
                        <div class="card-body">
                            <h6 class="mb-2">Atención a Clientes</h6>
                            <p class="small text-muted mb-3">
                                "¿En mi trabajo debo brindar servicio a clientes o usuarios?"
                            </p>
                            <p class="small mb-3">
                                <i class="fas fa-info-circle text-success me-1"></i>
                                Controla ítems 89-97 (9 preguntas)
                            </p>
                            <a href="<?= base_url('validation/conditional/forma-b-i/' . $service['id']) ?>"
                               class="btn btn-success btn-sm w-100" target="_blank">
                                <i class="fas fa-list-check me-1"></i> Ver Respuestas
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- MAPAS DE CALOR POR INSTRUMENTO -->
        <div class="content-section">
            <div class="section-title danger">
                <i class="fas fa-fire fa-lg text-danger me-3"></i>
                <h5>Mapas de Calor por Instrumento</h5>
                <span class="badge bg-danger ms-auto">4 instrumentos</span>
            </div>
            <div class="row g-3">
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="action-card card border-danger">
                        <div class="card-header bg-danger bg-opacity-10">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-briefcase text-danger me-2"></i>
                                <div>
                                    <h6 class="mb-0">Intralaboral Forma A</h6>
                                    <small class="text-muted">Jefes, profesionales</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <a href="<?= base_url('reports/intralaboral-a/' . $service['id']) ?>" class="btn btn-danger w-100" target="_blank">
                                <i class="fas fa-fire me-2"></i>Ver Mapa
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="action-card card border-danger">
                        <div class="card-header bg-danger bg-opacity-10">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-hard-hat text-danger me-2"></i>
                                <div>
                                    <h6 class="mb-0">Intralaboral Forma B</h6>
                                    <small class="text-muted">Auxiliares, operarios</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <a href="<?= base_url('reports/intralaboral-b/' . $service['id']) ?>" class="btn btn-danger w-100" target="_blank">
                                <i class="fas fa-fire me-2"></i>Ver Mapa
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="action-card card border-danger">
                        <div class="card-header bg-danger bg-opacity-10">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-home text-danger me-2"></i>
                                <div>
                                    <h6 class="mb-0">Extralaboral</h6>
                                    <small class="text-muted">Factores externos</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="<?= base_url('reports/extralaboral-a/' . $service['id']) ?>" class="btn btn-outline-danger btn-sm" target="_blank">
                                    <i class="fas fa-fire me-1"></i>Forma A
                                </a>
                                <a href="<?= base_url('reports/extralaboral-b/' . $service['id']) ?>" class="btn btn-outline-danger btn-sm" target="_blank">
                                    <i class="fas fa-fire me-1"></i>Forma B
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="action-card card border-danger">
                        <div class="card-header bg-danger bg-opacity-10">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-heartbeat text-danger me-2"></i>
                                <div>
                                    <h6 class="mb-0">Estrés</h6>
                                    <small class="text-muted">Síntomas</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="<?= base_url('reports/estres-a/' . $service['id']) ?>" class="btn btn-outline-danger btn-sm" target="_blank">
                                    <i class="fas fa-fire me-1"></i>Forma A
                                </a>
                                <a href="<?= base_url('reports/estres-b/' . $service['id']) ?>" class="btn btn-outline-danger btn-sm" target="_blank">
                                    <i class="fas fa-fire me-1"></i>Forma B
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- GRAFICACIÓN CYCLOID -->
        <div class="content-section">
            <div class="section-title primary">
                <i class="fas fa-chart-line fa-lg text-primary me-3"></i>
                <h5>Graficación Cycloid - Análisis Interactivo</h5>
                <span class="badge bg-primary ms-auto">3 módulos</span>
            </div>
            <div class="row g-3">
                <div class="col-lg-4 col-md-6">
                    <div class="action-card card border-primary">
                        <div class="card-header bg-primary bg-opacity-10">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-briefcase text-primary me-2"></i>
                                <h6 class="mb-0">Cycloid Intralaboral</h6>
                            </div>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-2">Gráficas radiales por dominios y dimensiones con filtros.</p>
                            <a href="<?= base_url('reports/intralaboral/' . $service['id']) ?>" class="btn btn-primary w-100" target="_blank">
                                <i class="fas fa-chart-line me-2"></i>Ver Cycloid
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="action-card card border-primary">
                        <div class="card-header bg-primary bg-opacity-10">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-home text-primary me-2"></i>
                                <h6 class="mb-0">Cycloid Extralaboral</h6>
                            </div>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-2">7 dimensiones con segmentadores demográficos.</p>
                            <a href="<?= base_url('reports/extralaboral/' . $service['id']) ?>" class="btn btn-primary w-100" target="_blank">
                                <i class="fas fa-chart-line me-2"></i>Ver Cycloid
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="action-card card border-primary">
                        <div class="card-header bg-primary bg-opacity-10">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-heartbeat text-primary me-2"></i>
                                <h6 class="mb-0">Cycloid Estrés</h6>
                            </div>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-2">Síntomas de estrés con comparativas por grupos.</p>
                            <a href="<?= base_url('reports/estres/' . $service['id']) ?>" class="btn btn-primary w-100" target="_blank">
                                <i class="fas fa-chart-line me-2"></i>Ver Cycloid
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (session()->get('role_name') === 'consultor' || session()->get('role_name') === 'superadmin'): ?>
        <!-- PDF EJECUTIVO -->
        <div class="content-section">
            <div class="section-title success">
                <i class="fas fa-file-pdf fa-lg text-success me-3"></i>
                <h5>Secciones Individuales del Informe de Batería de Riesgo Psicosocial</h5>
                <span class="badge bg-success ms-auto">11 secciones</span>
            </div>
            <div class="pdf-grid mb-4">
                <!-- Portada -->
                <div class="pdf-card card">
                    <div class="card-header bg-success text-white">
                        <i class="fas fa-image me-2"></i>Portada
                    </div>
                    <div class="card-body">
                        <div class="d-flex gap-2">
                            <a href="<?= base_url('pdfejecutivo/preview/portada/' . $service['id']) ?>" class="btn btn-outline-success btn-sm flex-fill" target="_blank">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="<?= base_url('pdfejecutivo/download/portada/' . $service['id']) ?>" class="btn btn-success btn-sm flex-fill" target="_blank">
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <!-- Contenido -->
                <div class="pdf-card card">
                    <div class="card-header bg-success text-white">
                        <i class="fas fa-list-ol me-2"></i>Contenido
                    </div>
                    <div class="card-body">
                        <div class="d-flex gap-2">
                            <a href="<?= base_url('pdfejecutivo/preview/contenido/' . $service['id']) ?>" class="btn btn-outline-success btn-sm flex-fill" target="_blank">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="<?= base_url('pdfejecutivo/download/contenido/' . $service['id']) ?>" class="btn btn-success btn-sm flex-fill" target="_blank">
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <!-- Introducción -->
                <div class="pdf-card card">
                    <div class="card-header bg-success text-white">
                        <i class="fas fa-book-open me-2"></i>Introducción
                    </div>
                    <div class="card-body">
                        <div class="d-flex gap-2">
                            <a href="<?= base_url('pdfejecutivo/preview/introduccion/' . $service['id']) ?>" class="btn btn-outline-success btn-sm flex-fill" target="_blank">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="<?= base_url('pdfejecutivo/download/introduccion/' . $service['id']) ?>" class="btn btn-success btn-sm flex-fill" target="_blank">
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <!-- Sociodemográficos -->
                <div class="pdf-card card">
                    <div class="card-header bg-success text-white">
                        <i class="fas fa-users me-2"></i>Sociodemográficos
                    </div>
                    <div class="card-body">
                        <div class="d-flex gap-2">
                            <a href="<?= base_url('pdfejecutivo/preview/sociodemograficos/' . $service['id']) ?>" class="btn btn-outline-success btn-sm flex-fill" target="_blank">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="<?= base_url('pdfejecutivo/download/sociodemograficos/' . $service['id']) ?>" class="btn btn-success btn-sm flex-fill" target="_blank">
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <!-- Mapas de Calor -->
                <div class="pdf-card card">
                    <div class="card-header bg-success text-white">
                        <i class="fas fa-th me-2"></i>Mapas de Calor
                    </div>
                    <div class="card-body">
                        <div class="d-flex gap-2">
                            <a href="<?= base_url('pdfejecutivo/preview/mapas-calor/' . $service['id']) ?>" class="btn btn-outline-success btn-sm flex-fill" target="_blank">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="<?= base_url('pdfejecutivo/download/mapas-calor/' . $service['id']) ?>" class="btn btn-success btn-sm flex-fill" target="_blank">
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <!-- Totales Intralaborales -->
                <div class="pdf-card card">
                    <div class="card-header bg-success text-white">
                        <i class="fas fa-chart-pie me-2"></i>Totales Intralaborales
                    </div>
                    <div class="card-body">
                        <div class="d-flex gap-2">
                            <a href="<?= base_url('pdfejecutivo/preview/totales-intralaborales/' . $service['id']) ?>" class="btn btn-outline-success btn-sm flex-fill" target="_blank">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="<?= base_url('pdfejecutivo/download/totales-intralaborales/' . $service['id']) ?>" class="btn btn-success btn-sm flex-fill" target="_blank">
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <!-- Dominios Intralaborales -->
                <div class="pdf-card card">
                    <div class="card-header bg-primary text-white">
                        <i class="fas fa-layer-group me-2"></i>Dominios Intralaborales
                    </div>
                    <div class="card-body">
                        <div class="d-flex gap-2">
                            <a href="<?= base_url('pdfejecutivo/preview/dominios-intralaborales/' . $service['id']) ?>" class="btn btn-outline-primary btn-sm flex-fill" target="_blank">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="<?= base_url('pdfejecutivo/download/dominios-intralaborales/' . $service['id']) ?>" class="btn btn-primary btn-sm flex-fill" target="_blank">
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <!-- Dimensiones Intralaborales -->
                <div class="pdf-card card">
                    <div class="card-header bg-info text-white">
                        <i class="fas fa-cubes me-2"></i>Dim. Intralaborales
                    </div>
                    <div class="card-body">
                        <div class="d-flex gap-2">
                            <a href="<?= base_url('pdfejecutivo/preview/dimensiones-intralaborales/' . $service['id']) ?>" class="btn btn-outline-info btn-sm flex-fill" target="_blank">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="<?= base_url('pdfejecutivo/download/dimensiones-intralaborales/' . $service['id']) ?>" class="btn btn-info btn-sm flex-fill" target="_blank">
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <!-- Dimensiones Extralaborales -->
                <div class="pdf-card card">
                    <div class="card-header text-white" style="background-color: #00A86B;">
                        <i class="fas fa-home me-2"></i>Dim. Extralaborales
                    </div>
                    <div class="card-body">
                        <div class="d-flex gap-2">
                            <a href="<?= base_url('pdfejecutivo/preview/dimensiones-extralaborales/' . $service['id']) ?>" class="btn btn-sm flex-fill" style="border-color: #00A86B; color: #00A86B;" target="_blank">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="<?= base_url('pdfejecutivo/download/dimensiones-extralaborales/' . $service['id']) ?>" class="btn btn-sm flex-fill text-white" style="background-color: #00A86B;" target="_blank">
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <!-- Evaluación del Estrés -->
                <div class="pdf-card card">
                    <div class="card-header text-white" style="background-color: #9B59B6;">
                        <i class="fas fa-bolt me-2"></i>Estrés
                    </div>
                    <div class="card-body">
                        <div class="d-flex gap-2">
                            <a href="<?= base_url('pdfejecutivo/preview/estres-ejecutivo/' . $service['id']) ?>" class="btn btn-sm flex-fill" style="border-color: #9B59B6; color: #9B59B6;" target="_blank">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="<?= base_url('pdfejecutivo/download/estres-ejecutivo/' . $service['id']) ?>" class="btn btn-sm flex-fill text-white" style="background-color: #9B59B6;" target="_blank">
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <!-- Recomendaciones y Planes -->
                <div class="pdf-card card">
                    <div class="card-header text-white" style="background-color: #FF6B35;">
                        <i class="fas fa-clipboard-check me-2"></i>Recomendaciones
                    </div>
                    <div class="card-body">
                        <div class="d-flex gap-2">
                            <a href="<?= base_url('pdfejecutivo/preview/recomendaciones-planes/' . $service['id']) ?>" class="btn btn-sm flex-fill" style="border-color: #FF6B35; color: #FF6B35;" target="_blank">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="<?= base_url('pdfejecutivo/download/recomendaciones-planes/' . $service['id']) ?>" class="btn btn-sm flex-fill text-white" style="background-color: #FF6B35;" target="_blank">
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informes Completos -->
            <h6 class="text-muted mb-3"><i class="fas fa-file-pdf me-2"></i>Informes Completos</h6>
            <div class="row g-3">
                <div class="col-lg-6">
                    <div class="informe-destacado card" style="border: 3px solid #C41E3A;">
                        <div class="card-header text-white" style="background: linear-gradient(135deg, #C41E3A 0%, #8B0000 100%);">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-file-pdf fa-2x me-3"></i>
                                    <div>
                                        <h6 class="mb-0">Informe de Batería Completo</h6>
                                        <small>Resolución 2764/2022</small>
                                    </div>
                                </div>
                                <span class="badge bg-light text-dark">~80+ pág</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex gap-2">
                                <a href="<?= base_url('pdfejecutivo/preview/completo/' . $service['id']) ?>" class="btn btn-outline-danger flex-fill" style="border-color: #C41E3A; color: #C41E3A;" target="_blank">
                                    <i class="fas fa-eye me-2"></i>Ver HTML
                                </a>
                                <a href="<?= base_url('pdfejecutivo/download/' . $service['id']) ?>" class="btn text-white flex-fill" style="background-color: #C41E3A;">
                                    <i class="fas fa-download me-2"></i>Descargar PDF
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="informe-destacado card" style="border: 3px solid #FF6B35;">
                        <div class="card-header text-white" style="background: linear-gradient(135deg, #FF6B35 0%, #E65100 100%);">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-briefcase fa-2x me-3"></i>
                                    <div>
                                        <h6 class="mb-0">Informe Ejecutivo</h6>
                                        <small>Resumen + Planes de Acción</small>
                                    </div>
                                </div>
                                <span class="badge bg-light text-dark">~15-30 pág</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex gap-2">
                                <a href="<?= base_url('pdfejecutivo/preview/ejecutivo/' . $service['id']) ?>" class="btn btn-outline-warning flex-fill" style="border-color: #FF6B35; color: #FF6B35;" target="_blank">
                                    <i class="fas fa-eye me-2"></i>Ver HTML
                                </a>
                                <a href="<?= base_url('pdfejecutivo/download/ejecutivo/' . $service['id']) ?>" class="btn text-white flex-fill" style="background-color: #FF6B35;" target="_blank">
                                    <i class="fas fa-download me-2"></i>Descargar PDF
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Casos Blanco de Intervención (CONFIDENCIAL - Solo Consultor) -->
            <div class="mt-4">
                <div class="informe-destacado card" style="border: 3px solid #dc3545;">
                    <div class="card-header text-white" style="background: linear-gradient(135deg, #dc3545 0%, #8B0000 100%);">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-user-shield fa-2x me-3"></i>
                                <div>
                                    <h6 class="mb-0">Casos Blanco de Intervención</h6>
                                    <small>Uso exclusivo del profesional SST</small>
                                </div>
                            </div>
                            <span class="badge bg-warning text-dark">CONFIDENCIAL</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning mb-3 py-2">
                            <small><i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Reserva profesional:</strong> Este reporte contiene datos individualizables con reserva similar a historia clínica.
                            <strong>NO incluir en informes a la empresa.</strong> Uso permitido: ARL, médico SST bajo requerimiento.</small>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="<?= base_url('pdfejecutivo/preview/casos-intervencion/' . $service['id']) ?>" class="btn btn-outline-danger flex-fill" target="_blank">
                                <i class="fas fa-eye me-2"></i>Ver Casos
                            </a>
                            <a href="<?= base_url('pdfejecutivo/download/casos-intervencion/' . $service['id']) ?>" class="btn btn-danger flex-fill">
                                <i class="fas fa-download me-2"></i>Descargar PDF
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- SECCIÓN DE RECOMENDACIONES (DESPLEGABLE - AL FINAL) -->
        <?php if (isset($recommendations) && !empty($recommendations)): ?>
        <div class="accordion recommendations-accordion mb-4" id="recommendationsAccordion">
            <div class="accordion-item border-0 shadow-sm rounded overflow-hidden">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseRecommendations">
                        <i class="fas fa-exclamation-triangle text-warning me-3 fa-lg"></i>
                        <div>
                            <span class="fw-bold">¿Qué hacer ahora?</span>
                            <br>
                            <small class="text-muted fw-normal">Las siguientes dimensiones presentan niveles de riesgo MEDIO (amarillo) o ALTO/MUY ALTO (rojo). Haga clic en "Ver Plan de Intervención" para acceder a las recomendaciones del equipo de expertos Cycloid Talent SAS.</small>
                        </div>
                    </button>
                </h2>
                <div id="collapseRecommendations" class="accordion-collapse collapse">
                    <div class="accordion-body bg-warning bg-opacity-10">
                        <?= $recommendations ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Navegación -->
        <div class="nav-footer d-flex justify-content-between align-items-center mb-4">
            <a href="<?= base_url('battery-services') ?>" class="btn btn-secondary" target="_blank">
                <i class="fas fa-arrow-left me-2"></i>Volver a Servicios
            </a>
            <div>
                <a href="<?= base_url('battery-services/edit/' . $service['id']) ?>" class="btn btn-warning" target="_blank">
                    <i class="fas fa-edit me-2"></i>Editar Servicio
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mostrar spinner al descargar PDFs grandes
        document.querySelectorAll('a[href*="pdfejecutivo/download"]').forEach(function(link) {
            link.addEventListener('click', function(e) {
                // Solo para descargas del informe completo (no secciones individuales)
                if (this.href.includes('/download/') && !this.href.includes('/download/portada')
                    && !this.href.includes('/download/contenido') && !this.href.includes('/download/introduccion')
                    && !this.href.includes('/download/sociodemograficos') && !this.href.includes('/download/mapas-calor')
                    && !this.href.includes('/download/totales-intralaborales') && !this.href.includes('/download/dominios-intralaborales')
                    && !this.href.includes('/download/dimensiones-intralaborales') && !this.href.includes('/download/dimensiones-extralaborales')
                    && !this.href.includes('/download/estres-ejecutivo') && !this.href.includes('/download/recomendaciones-planes')) {
                    document.getElementById('loadingOverlay').classList.add('active');

                    // Ocultar después de un tiempo (la descarga inicia automáticamente)
                    setTimeout(function() {
                        document.getElementById('loadingOverlay').classList.remove('active');
                    }, 5000);
                }
            });
        });
    </script>
</body>
</html>
