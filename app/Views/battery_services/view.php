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
            <div class="col-md-10">
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('battery-services') ?>">Servicios</a></li>
                        <li class="breadcrumb-item active"><?= esc($service['service_name']) ?></li>
                    </ol>
                </nav>

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

                <!-- Card Principal -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-clipboard-check me-2"></i><?= esc($service['service_name']) ?>
                            </h5>
                            <span class="badge bg-light text-dark">
                                <?= ucfirst($service['status']) ?>
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted"><i class="fas fa-building me-2"></i>Información de la Empresa</h6>
                                <p class="mb-1"><strong>Empresa:</strong> <?= esc($service['company_name']) ?></p>
                                <p class="mb-1"><strong>NIT:</strong> <?= esc($service['nit']) ?></p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted"><i class="fas fa-calendar me-2"></i>Fechas</h6>
                                <p class="mb-1"><strong>Fecha de Servicio:</strong> <?= date('d/m/Y', strtotime($service['service_date'])) ?></p>
                                <p class="mb-1"><strong>Vencimiento de Enlaces:</strong> <?= date('d/m/Y', strtotime($service['link_expiration_date'])) ?></p>
                                <?php
                                $daysLeft = floor((strtotime($service['link_expiration_date']) - time()) / 86400);
                                if ($daysLeft < 0): ?>
                                    <span class="badge bg-danger">Expirado hace <?= abs($daysLeft) ?> días</span>
                                <?php elseif ($daysLeft <= 3): ?>
                                    <span class="badge bg-warning">Expira en <?= $daysLeft ?> días</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Activo (<?= $daysLeft ?> días restantes)</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <hr>

                        <h6 class="text-muted"><i class="fas fa-list-check me-2"></i>Formularios Incluidos</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="alert alert-info mb-2">
                                    <i class="fas fa-check-circle me-2"></i>Cuestionario Intralaboral (A/B)
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="alert alert-info mb-2">
                                    <i class="fas fa-check-circle me-2"></i>Factores Extralaborales
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="alert alert-info mb-2">
                                    <i class="fas fa-check-circle me-2"></i>Evaluación del Estrés
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Acciones Principales -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card shadow-sm h-100 border-primary">
                            <div class="card-body text-center">
                                <i class="fas fa-upload fa-3x text-primary mb-3"></i>
                                <h5>Cargar Trabajadores</h5>
                                <p class="text-muted">Sube un archivo CSV con el listado de trabajadores</p>
                                <a href="<?= base_url('workers/upload/' . $service['id']) ?>" class="btn btn-primary" target="_blank">
                                    <i class="fas fa-upload me-2"></i>Cargar CSV
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card shadow-sm h-100 border-success">
                            <div class="card-body text-center">
                                <i class="fas fa-users fa-3x text-success mb-3"></i>
                                <h5>Ver Trabajadores</h5>
                                <p class="text-muted">Consulta el listado completo de trabajadores</p>
                                <a href="<?= base_url('workers/service/' . $service['id']) ?>" class="btn btn-success" target="_blank">
                                    <i class="fas fa-list me-2"></i>Ver Listado
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card shadow-sm h-100 border-info">
                            <div class="card-body text-center">
                                <i class="fas fa-gauge-high fa-3x text-info mb-3"></i>
                                <h5>Gráficos Globales</h5>
                                <p class="text-muted">Promedios por Forma A y Forma B</p>
                                <a href="<?= base_url('battery-services/global-gauges/' . $service['id']) ?>" class="btn btn-info" target="_blank">
                                    <i class="fas fa-chart-pie me-2"></i>Ver Gráficos
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card shadow-sm h-100 border-danger">
                            <div class="card-body text-center">
                                <i class="fas fa-fire fa-3x text-danger mb-3"></i>
                                <h5>Mapa de Calor General</h5>
                                <p class="text-muted">Vista consolidada de todos los instrumentos</p>
                                <a href="<?= base_url('reports/heatmap/' . $service['id']) ?>" class="btn btn-danger" target="_blank">
                                    <i class="fas fa-th me-2"></i>Ver Mapa de Calor
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Consolidación Grupal y Ficha de Datos Generales -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card shadow-sm h-100 border-warning">
                            <div class="card-body text-center">
                                <i class="fas fa-chart-bar fa-3x text-warning mb-3"></i>
                                <h5>Consolidación Grupal</h5>
                                <p class="text-muted">Distribución por escalas de riesgo agrupadas (Forma A, B y Conjunto)</p>
                                <a href="<?= base_url('reports/consolidacion/' . $service['id']) ?>" class="btn btn-warning" target="_blank">
                                    <i class="fas fa-table me-2"></i>Ver Consolidación
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card shadow-sm h-100 border-info">
                            <div class="card-body text-center">
                                <i class="fas fa-id-card fa-3x text-info mb-3"></i>
                                <h5>Ficha de Datos Generales</h5>
                                <p class="text-muted">Consolidación demográfica con gráficos interactivos y filtros</p>
                                <a href="<?= base_url('reports/ficha-datos-generales/' . $service['id']) ?>" class="btn btn-info" target="_blank">
                                    <i class="fas fa-chart-pie me-2"></i>Ver Ficha
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php if (session()->get('role_name') === 'consultor' || session()->get('role_name') === 'superadmin'): ?>
                    <div class="col-md-4">
                        <div class="card shadow-sm h-100 border-purple" style="border-color: #6f42c1 !important;">
                            <div class="card-body text-center">
                                <i class="fas fa-robot fa-3x mb-3" style="color: #6f42c1;"></i>
                                <h5>Informe con IA</h5>
                                <p class="text-muted">Generación automática de interpretaciones con inteligencia artificial</p>
                                <a href="<?= base_url('report-sections/' . $service['id']) ?>" class="btn text-white" style="background-color: #6f42c1;">
                                    <i class="fas fa-magic me-2"></i>Generar Informe
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Módulo IA para Ficha de Datos Generales (Solo Consultores) -->
                <?php if (session()->get('role_name') === 'consultor' || session()->get('role_name') === 'superadmin'): ?>
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-brain fa-2x me-3" style="color: #6f42c1;"></i>
                            <div>
                                <h5 class="mb-1">Interpretación IA - Ficha de Datos Generales</h5>
                                <p class="text-muted small mb-0">Análisis interdisciplinario del perfil sociodemográfico (Psicología + Sociología + Trabajo Social + Estadística)</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card shadow-sm h-100" style="border-color: #6f42c1 !important; border-width: 2px;">
                            <div class="card-header border-bottom-0" style="background-color: rgba(111, 66, 193, 0.1);">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-users fa-lg me-2" style="color: #6f42c1;"></i>
                                    <div>
                                        <h6 class="mb-0">Ficha de Datos Generales con IA</h6>
                                        <small class="text-muted">Interpretación narrativa interdisciplinaria</small>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small mb-3">Genera interpretaciones automáticas del perfil demográfico de los trabajadores desde múltiples perspectivas: psicología organizacional, sociología laboral, trabajo social y análisis estadístico.</p>
                                <a href="<?= base_url('demographics-report/' . $service['id']) ?>" class="btn text-white" style="background-color: #6f42c1;" target="_blank">
                                    <i class="fas fa-brain me-2"></i>Generar Interpretación IA
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- MAPAS DE CALOR POR INSTRUMENTO -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-fire fa-2x text-danger me-3"></i>
                            <div>
                                <h5 class="mb-1">Mapas de Calor por Instrumento</h5>
                                <p class="text-muted small mb-0">Visualizaciones estáticas con jerarquía de dominios y dimensiones, colores según nivel de riesgo</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="card shadow-sm h-100 border-danger border-2">
                            <div class="card-header bg-danger bg-opacity-10 border-bottom-0">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-briefcase fa-lg text-danger me-2"></i>
                                    <div>
                                        <h6 class="mb-0">Intralaboral Forma A</h6>
                                        <small class="text-muted">Jefes, profesionales, técnicos</small>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small mb-3">Mapa de calor con 4 dominios y 19 dimensiones para cargos con personal a cargo o responsabilidades profesionales.</p>
                                <a href="<?= base_url('reports/intralaboral-a/' . $service['id']) ?>" class="btn btn-danger btn-sm" target="_blank">
                                    <i class="fas fa-fire me-2"></i>Ver Mapa de Calor
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="card shadow-sm h-100 border-danger border-2">
                            <div class="card-header bg-danger bg-opacity-10 border-bottom-0">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-hard-hat fa-lg text-danger me-2"></i>
                                    <div>
                                        <h6 class="mb-0">Intralaboral Forma B</h6>
                                        <small class="text-muted">Auxiliares, operarios</small>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small mb-3">Mapa de calor con 4 dominios y 16 dimensiones para cargos operativos y de apoyo sin personal a cargo.</p>
                                <a href="<?= base_url('reports/intralaboral-b/' . $service['id']) ?>" class="btn btn-danger btn-sm" target="_blank">
                                    <i class="fas fa-fire me-2"></i>Ver Mapa de Calor
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="card shadow-sm h-100 border-danger border-2">
                            <div class="card-header bg-danger bg-opacity-10 border-bottom-0">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-home fa-lg text-danger me-2"></i>
                                    <div>
                                        <h6 class="mb-0">Extralaboral</h6>
                                        <small class="text-muted">Factores fuera del trabajo</small>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small mb-3">Mapa de calor con 7 dimensiones extralaborales. Baremos diferenciados según forma intralaboral.</p>
                                <div class="d-grid gap-2">
                                    <a href="<?= base_url('reports/extralaboral-a/' . $service['id']) ?>" class="btn btn-danger btn-sm" target="_blank">
                                        <i class="fas fa-fire me-2"></i>Forma A - Jefes/Profesionales
                                    </a>
                                    <a href="<?= base_url('reports/extralaboral-b/' . $service['id']) ?>" class="btn btn-danger btn-sm" target="_blank">
                                        <i class="fas fa-fire me-2"></i>Forma B - Auxiliares/Operarios
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="card shadow-sm h-100 border-danger border-2">
                            <div class="card-header bg-danger bg-opacity-10 border-bottom-0">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-heartbeat fa-lg text-danger me-2"></i>
                                    <div>
                                        <h6 class="mb-0">Estrés</h6>
                                        <small class="text-muted">Síntomas de estrés</small>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small mb-3">Evaluación de síntomas fisiológicos, comportamentales y cognitivos del estrés laboral. Baremos diferenciados por forma.</p>
                                <div class="d-grid gap-2">
                                    <a href="<?= base_url('reports/estres-a/' . $service['id']) ?>" class="btn btn-danger btn-sm" target="_blank">
                                        <i class="fas fa-fire me-2"></i>Forma A - Jefes/Profesionales
                                    </a>
                                    <a href="<?= base_url('reports/estres-b/' . $service['id']) ?>" class="btn btn-danger btn-sm" target="_blank">
                                        <i class="fas fa-fire me-2"></i>Forma B - Auxiliares/Operarios
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- GRAFICACIÓN CYCLOID (Análisis Interactivo) -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-chart-line fa-2x text-primary me-3"></i>
                            <div>
                                <h5 class="mb-1">Graficación Cycloid</h5>
                                <p class="text-muted small mb-0">Análisis interactivos con gráficas radiales, filtros dinámicos y segmentadores</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="card shadow-sm h-100 border-primary border-2">
                            <div class="card-header bg-primary bg-opacity-10 border-bottom-0">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-briefcase fa-lg text-primary me-2"></i>
                                    <h6 class="mb-0">Cycloid Intralaboral</h6>
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small mb-3">Análisis interactivo con gráficas radiales por dominios y dimensiones. Filtros por forma A/B, género, cargo.</p>
                                <a href="<?= base_url('reports/intralaboral/' . $service['id']) ?>" class="btn btn-primary btn-sm" target="_blank">
                                    <i class="fas fa-chart-line me-2"></i>Ver Cycloid
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="card shadow-sm h-100 border-primary border-2">
                            <div class="card-header bg-primary bg-opacity-10 border-bottom-0">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-home fa-lg text-primary me-2"></i>
                                    <h6 class="mb-0">Cycloid Extralaboral</h6>
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small mb-3">Gráficas de 7 dimensiones extralaborales con segmentadores demográficos y análisis comparativo.</p>
                                <a href="<?= base_url('reports/extralaboral/' . $service['id']) ?>" class="btn btn-primary btn-sm" target="_blank">
                                    <i class="fas fa-chart-line me-2"></i>Ver Cycloid
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="card shadow-sm h-100 border-primary border-2">
                            <div class="card-header bg-primary bg-opacity-10 border-bottom-0">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-heartbeat fa-lg text-primary me-2"></i>
                                    <h6 class="mb-0">Cycloid Estrés</h6>
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small mb-3">Visualización de síntomas de estrés con filtros dinámicos y comparativas por grupos.</p>
                                <a href="<?= base_url('reports/estres/' . $service['id']) ?>" class="btn btn-primary btn-sm" target="_blank">
                                    <i class="fas fa-chart-line me-2"></i>Ver Cycloid
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recomendaciones y Planes de Acción -->
                <?php if (isset($recommendations) && !empty($recommendations)): ?>
                    <?= $recommendations ?>
                <?php endif; ?>

                <!-- GENERACIÓN DE INFORMES PDF -->
                <?php if (session()->get('role_name') === 'consultor' || session()->get('role_name') === 'superadmin'): ?>
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-file-pdf fa-2x text-danger me-3"></i>
                            <div>
                                <h5 class="mb-1">Generación de Informes PDF</h5>
                                <p class="text-muted small mb-0">Dos tipos de informe disponibles: Ejecutivo (resumen) y Completo (batería completa)</p>
                            </div>
                        </div>
                    </div>

                    <!-- Tarjetas de Descarga de Informes -->
                    <div class="col-md-6 mb-3">
                        <div class="card shadow-sm border-warning border-2 h-100">
                            <div class="card-header bg-warning text-dark">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-file-contract fa-lg me-2"></i>
                                    <div>
                                        <h6 class="mb-0">Informe Ejecutivo</h6>
                                        <small>Resumen para directivos</small>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small mb-3">
                                    <strong>Contenido:</strong> Portada + Mapas de Calor + Recomendaciones y Planes de Acción
                                </p>
                                <ul class="list-unstyled small text-muted mb-3">
                                    <li><i class="fas fa-check text-success me-2"></i>Visión general de resultados</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Identificación de riesgos críticos</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Plan de intervención</li>
                                </ul>
                                <div class="d-grid gap-2">
                                    <a href="<?= base_url('pdf/preview/ejecutivo/' . $service['id']) ?>" class="btn btn-outline-warning" target="_blank">
                                        <i class="fas fa-eye me-2"></i>Preview Ejecutivo
                                    </a>
                                    <a href="<?= base_url('pdf/download/ejecutivo/' . $service['id']) ?>" class="btn btn-warning text-dark">
                                        <i class="fas fa-download me-2"></i>Descargar PDF Ejecutivo
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="card shadow-sm border-danger border-2 h-100">
                            <div class="card-header bg-danger text-white">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-book fa-lg me-2"></i>
                                    <div>
                                        <h6 class="mb-0">Informe de Batería Completo</h6>
                                        <small>Documento técnico detallado</small>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small mb-3">
                                    <strong>Contenido:</strong> Todas las secciones del informe de riesgo psicosocial
                                </p>
                                <ul class="list-unstyled small text-muted mb-3">
                                    <li><i class="fas fa-check text-success me-2"></i>Marco conceptual y metodológico</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Análisis sociodemográfico</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Resultados detallados por cuestionario</li>
                                </ul>
                                <div class="d-grid gap-2">
                                    <a href="<?= base_url('pdf/preview/completo/' . $service['id']) ?>" class="btn btn-outline-danger" target="_blank">
                                        <i class="fas fa-eye me-2"></i>Preview Completo
                                    </a>
                                    <a href="<?= base_url('pdf/download/completo/' . $service['id']) ?>" class="btn btn-danger">
                                        <i class="fas fa-download me-2"></i>Descargar PDF Completo
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Preview por Secciones (Expandible) -->
                    <div class="col-md-12 mb-3">
                        <div class="card shadow-sm border-secondary">
                            <div class="card-header bg-secondary text-white" data-bs-toggle="collapse" data-bs-target="#previewSecciones" style="cursor: pointer;">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-layer-group fa-lg me-2"></i>
                                        <h6 class="mb-0">Preview por Secciones Individuales</h6>
                                    </div>
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                            </div>
                            <div class="card-body collapse" id="previewSecciones">
                                <div class="row">
                                    <!-- Columna 1: Estructura del Informe -->
                                    <div class="col-md-3">
                                        <h6 class="text-muted mb-3"><i class="fas fa-file-alt me-2"></i>Estructura</h6>
                                        <div class="d-grid gap-2">
                                            <a href="<?= base_url('pdf/preview/portada/' . $service['id']) ?>" class="btn btn-outline-secondary btn-sm" target="_blank">
                                                <i class="fas fa-image me-1"></i> Portada
                                            </a>
                                            <a href="<?= base_url('pdf/preview/contenido/' . $service['id']) ?>" class="btn btn-outline-secondary btn-sm" target="_blank">
                                                <i class="fas fa-list-ol me-1"></i> Tabla de Contenido
                                            </a>
                                            <a href="<?= base_url('pdf/preview/introduccion/' . $service['id']) ?>" class="btn btn-outline-info btn-sm" target="_blank">
                                                <i class="fas fa-book-open me-1"></i> Introducción
                                            </a>
                                        </div>
                                    </div>

                                    <!-- Columna 2: Sociodemográficos y Mapas de Calor -->
                                    <div class="col-md-3">
                                        <h6 class="text-muted mb-3"><i class="fas fa-users me-2"></i>Sociodemográficos</h6>
                                        <div class="d-grid gap-2">
                                            <a href="<?= base_url('pdf/preview/sociodemograficos/' . $service['id']) ?>" class="btn btn-outline-info btn-sm" target="_blank">
                                                <i class="fas fa-chart-pie me-1"></i> Variables Sociodemográficas
                                            </a>
                                            <a href="<?= base_url('pdf/preview/mapas-calor/' . $service['id']) ?>" class="btn btn-outline-danger btn-sm" target="_blank">
                                                <i class="fas fa-fire me-1"></i> Mapas de Calor
                                            </a>
                                        </div>
                                    </div>

                                    <!-- Columna 3: Resultados por Cuestionario -->
                                    <div class="col-md-3">
                                        <h6 class="text-muted mb-3"><i class="fas fa-chart-bar me-2"></i>Intralaboral</h6>
                                        <div class="d-grid gap-2">
                                            <a href="<?= base_url('pdf/preview/intralaboral-total/' . $service['id']) ?>" class="btn btn-outline-primary btn-sm" target="_blank">
                                                <i class="fas fa-chart-pie me-1"></i> Total (A, B, General)
                                            </a>
                                            <a href="<?= base_url('pdf/preview/intralaboral-dominios/' . $service['id']) ?>" class="btn btn-outline-primary btn-sm" target="_blank">
                                                <i class="fas fa-layer-group me-1"></i> Dominios (A y B)
                                            </a>
                                            <a href="<?= base_url('pdf/preview/intralaboral-dimensiones/' . $service['id']) ?>" class="btn btn-outline-primary btn-sm" target="_blank">
                                                <i class="fas fa-th-list me-1"></i> Dimensiones (A y B)
                                            </a>
                                        </div>
                                        <h6 class="text-muted mb-3 mt-3"><i class="fas fa-home me-2"></i>Extralaboral</h6>
                                        <div class="d-grid gap-2">
                                            <a href="<?= base_url('pdf/preview/extralaboral-dimensiones/' . $service['id']) ?>" class="btn btn-outline-success btn-sm" target="_blank">
                                                <i class="fas fa-th-list me-1"></i> Dimensiones (A y B)
                                            </a>
                                        </div>
                                        <h6 class="text-muted mb-3 mt-3"><i class="fas fa-heartbeat me-2"></i>Estrés</h6>
                                        <div class="d-grid gap-2">
                                            <a href="<?= base_url('pdf/preview/estres/' . $service['id']) ?>" class="btn btn-outline-purple btn-sm" target="_blank" style="color: #9C27B0; border-color: #9C27B0;">
                                                <i class="fas fa-brain me-1"></i> Análisis Estrés
                                            </a>
                                        </div>
                                    </div>

                                    <!-- Columna 4: Conclusiones -->
                                    <div class="col-md-3">
                                        <h6 class="text-muted mb-3"><i class="fas fa-clipboard-check me-2"></i>Conclusiones</h6>
                                        <div class="d-grid gap-2">
                                            <a href="<?= base_url('pdf/preview/recomendaciones-acciones/' . $service['id']) ?>" class="btn btn-outline-warning btn-sm" target="_blank">
                                                <i class="fas fa-lightbulb me-1"></i> Recomendaciones
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- PDF EJECUTIVO - Módulo DomPDF Nativo -->
                <?php if (session()->get('role_name') === 'consultor' || session()->get('role_name') === 'superadmin'): ?>
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-file-pdf fa-2x me-3" style="color: #28a745;"></i>
                            <div>
                                <h5 class="mb-1">PDF Ejecutivo - DomPDF Nativo</h5>
                                <p class="text-muted small mb-0">Nuevo módulo de generación PDF con gauges SVG renderizados correctamente</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="card shadow-sm border-success border-2 h-100">
                            <div class="card-header bg-success text-white">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-image fa-lg me-2"></i>
                                    <div>
                                        <h6 class="mb-0">Portada</h6>
                                        <small>Página inicial del informe</small>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small mb-3">
                                    Logo de empresa, título, datos de empresa y consultor con formato ICONTEC.
                                </p>
                                <div class="d-grid gap-2">
                                    <a href="<?= base_url('pdfejecutivo/preview/portada/' . $service['id']) ?>" class="btn btn-outline-success btn-sm" target="_blank">
                                        <i class="fas fa-eye me-2"></i>Ver HTML
                                    </a>
                                    <a href="<?= base_url('pdfejecutivo/download/portada/' . $service['id']) ?>" class="btn btn-success btn-sm">
                                        <i class="fas fa-download me-2"></i>Descargar PDF
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="card shadow-sm border-success border-2 h-100">
                            <div class="card-header bg-success text-white">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-list-ol fa-lg me-2"></i>
                                    <div>
                                        <h6 class="mb-0">Contenido</h6>
                                        <small>Índice del informe</small>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small mb-3">
                                    Tabla de contenido con estructura de secciones (sin numeración de páginas).
                                </p>
                                <div class="d-grid gap-2">
                                    <a href="<?= base_url('pdfejecutivo/preview/contenido/' . $service['id']) ?>" class="btn btn-outline-success btn-sm" target="_blank">
                                        <i class="fas fa-eye me-2"></i>Ver HTML
                                    </a>
                                    <a href="<?= base_url('pdfejecutivo/download/contenido/' . $service['id']) ?>" class="btn btn-success btn-sm">
                                        <i class="fas fa-download me-2"></i>Descargar PDF
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="card shadow-sm border-success border-2 h-100">
                            <div class="card-header bg-success text-white">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-book-open fa-lg me-2"></i>
                                    <div>
                                        <h6 class="mb-0">Introducción</h6>
                                        <small>Marco legal y metodología</small>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small mb-3">
                                    Marco legal, objetivos, metodología y tabla de niveles de riesgo.
                                </p>
                                <div class="d-grid gap-2">
                                    <a href="<?= base_url('pdfejecutivo/preview/introduccion/' . $service['id']) ?>" class="btn btn-outline-success btn-sm" target="_blank">
                                        <i class="fas fa-eye me-2"></i>Ver HTML
                                    </a>
                                    <a href="<?= base_url('pdfejecutivo/download/introduccion/' . $service['id']) ?>" class="btn btn-success btn-sm">
                                        <i class="fas fa-download me-2"></i>Descargar PDF
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="card shadow-sm border-success border-2 h-100">
                            <div class="card-header bg-success text-white">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-users fa-lg me-2"></i>
                                    <div>
                                        <h6 class="mb-0">Sociodemográficos</h6>
                                        <small>Características de la población</small>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small mb-3">
                                    Variables sociodemográficas y ocupacionales con análisis IA.
                                </p>
                                <div class="d-grid gap-2">
                                    <a href="<?= base_url('pdfejecutivo/preview/sociodemograficos/' . $service['id']) ?>" class="btn btn-outline-success btn-sm" target="_blank">
                                        <i class="fas fa-eye me-2"></i>Ver HTML
                                    </a>
                                    <a href="<?= base_url('pdfejecutivo/download/sociodemograficos/' . $service['id']) ?>" class="btn btn-success btn-sm">
                                        <i class="fas fa-download me-2"></i>Descargar PDF
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="card shadow-sm border-success border-2 h-100">
                            <div class="card-header bg-success text-white">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-th fa-lg me-2"></i>
                                    <div>
                                        <h6 class="mb-0">Mapas de Calor</h6>
                                        <small>Distribución de riesgo general</small>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small mb-3">
                                    Distribución por niveles de riesgo: Intralaboral, Extralaboral y Estrés.
                                </p>
                                <div class="d-grid gap-2">
                                    <a href="<?= base_url('pdfejecutivo/preview/mapas-calor/' . $service['id']) ?>" class="btn btn-outline-success btn-sm" target="_blank">
                                        <i class="fas fa-eye me-2"></i>Ver HTML
                                    </a>
                                    <a href="<?= base_url('pdfejecutivo/download/mapas-calor/' . $service['id']) ?>" class="btn btn-success btn-sm">
                                        <i class="fas fa-download me-2"></i>Descargar PDF
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="card shadow-sm border-success border-2 h-100">
                            <div class="card-header bg-success text-white">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-chart-pie fa-lg me-2"></i>
                                    <div>
                                        <h6 class="mb-0">Totales Intralaborales</h6>
                                        <small>Resultados consolidados por forma</small>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small mb-3">
                                    Total Forma A, Forma B, Resumen General y Tabla 34 (Psicosocial).
                                </p>
                                <div class="d-grid gap-2">
                                    <a href="<?= base_url('pdfejecutivo/preview/totales-intralaborales/' . $service['id']) ?>" class="btn btn-outline-success btn-sm" target="_blank">
                                        <i class="fas fa-eye me-2"></i>Ver HTML
                                    </a>
                                    <a href="<?= base_url('pdfejecutivo/download/totales-intralaborales/' . $service['id']) ?>" class="btn btn-success btn-sm">
                                        <i class="fas fa-download me-2"></i>Descargar PDF
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dominios Intralaborales -->
                    <div class="col-md-4 mb-3">
                        <div class="card shadow-sm border-primary border-2 h-100">
                            <div class="card-header bg-primary text-white">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-layer-group fa-lg me-2"></i>
                                    <div>
                                        <h6 class="mb-0">Dominios Intralaborales</h6>
                                        <small>Liderazgo, Control, Demandas, Recompensas</small>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small mb-3">
                                    4 dominios Forma A + 4 dominios Forma B con gauges y distribuciones.
                                </p>
                                <div class="d-grid gap-2">
                                    <a href="<?= base_url('pdfejecutivo/preview/dominios-intralaborales/' . $service['id']) ?>" class="btn btn-outline-primary btn-sm" target="_blank">
                                        <i class="fas fa-eye me-2"></i>Ver HTML
                                    </a>
                                    <a href="<?= base_url('pdfejecutivo/download/dominios-intralaborales/' . $service['id']) ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-download me-2"></i>Descargar PDF
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dimensiones Intralaborales -->
                    <div class="col-md-4 mb-3">
                        <div class="card shadow-sm border-info border-2 h-100">
                            <div class="card-header bg-info text-white">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-cubes fa-lg me-2"></i>
                                    <div>
                                        <h6 class="mb-0">Dimensiones Intralaborales</h6>
                                        <small>19 dimensiones detalladas</small>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small mb-3">
                                    19 dim. Forma A + 16 dim. Forma B = 35 páginas con gauges y baremos.
                                </p>
                                <div class="d-grid gap-2">
                                    <a href="<?= base_url('pdfejecutivo/preview/dimensiones-intralaborales/' . $service['id']) ?>" class="btn btn-outline-info btn-sm" target="_blank">
                                        <i class="fas fa-eye me-2"></i>Ver HTML
                                    </a>
                                    <a href="<?= base_url('pdfejecutivo/download/dimensiones-intralaborales/' . $service['id']) ?>" class="btn btn-info btn-sm">
                                        <i class="fas fa-download me-2"></i>Descargar PDF
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dimensiones Extralaborales -->
                    <div class="col-md-4 mb-3">
                        <div class="card shadow-sm h-100" style="border: 2px solid #00A86B;">
                            <div class="card-header text-white" style="background-color: #00A86B;">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-home fa-lg me-2"></i>
                                    <div>
                                        <h6 class="mb-0">Dimensiones Extralaborales</h6>
                                        <small>7 dimensiones por forma</small>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small mb-3">
                                    7 dim. Forma A + 7 dim. Forma B = 14 páginas con gauges y baremos.
                                </p>
                                <div class="d-grid gap-2">
                                    <a href="<?= base_url('pdfejecutivo/preview/dimensiones-extralaborales/' . $service['id']) ?>" class="btn btn-outline-success btn-sm" target="_blank" style="color: #00A86B; border-color: #00A86B;">
                                        <i class="fas fa-eye me-2"></i>Ver HTML
                                    </a>
                                    <a href="<?= base_url('pdfejecutivo/download/dimensiones-extralaborales/' . $service['id']) ?>" class="btn btn-sm text-white" style="background-color: #00A86B;">
                                        <i class="fas fa-download me-2"></i>Descargar PDF
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Evaluación del Estrés -->
                    <div class="col-md-4 mb-3">
                        <div class="card shadow-sm h-100" style="border: 2px solid #9B59B6;">
                            <div class="card-header text-white" style="background-color: #9B59B6;">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-bolt fa-lg me-2"></i>
                                    <div>
                                        <h6 class="mb-0">Evaluación del Estrés</h6>
                                        <small>Cuestionario independiente</small>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small mb-3">
                                    1 intro + 1 Forma A + 1 Forma B = 3 páginas con gauges y baremos.
                                </p>
                                <div class="d-grid gap-2">
                                    <a href="<?= base_url('pdfejecutivo/preview/estres-ejecutivo/' . $service['id']) ?>" class="btn btn-outline-secondary btn-sm" target="_blank" style="color: #9B59B6; border-color: #9B59B6;">
                                        <i class="fas fa-eye me-2"></i>Ver HTML
                                    </a>
                                    <a href="<?= base_url('pdfejecutivo/download/estres-ejecutivo/' . $service['id']) ?>" class="btn btn-sm text-white" style="background-color: #9B59B6;">
                                        <i class="fas fa-download me-2"></i>Descargar PDF
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recomendaciones y Planes de Acción (sección individual) -->
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <div class="card shadow-sm h-100" style="border: 2px solid #FF6B35;">
                            <div class="card-header text-white" style="background-color: #FF6B35;">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-clipboard-check fa-lg me-2"></i>
                                    <div>
                                        <h6 class="mb-0">Recomendaciones y Planes</h6>
                                        <small>Dimensiones en riesgo alto/muy alto</small>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small mb-3">
                                    Planes de acción a 6 meses para dimensiones con riesgo alto y muy alto.
                                </p>
                                <div class="d-grid gap-2">
                                    <a href="<?= base_url('pdfejecutivo/preview/recomendaciones-planes/' . $service['id']) ?>" class="btn btn-outline-secondary btn-sm" target="_blank" style="color: #FF6B35; border-color: #FF6B35;">
                                        <i class="fas fa-eye me-2"></i>Ver HTML
                                    </a>
                                    <a href="<?= base_url('pdfejecutivo/download/recomendaciones-planes/' . $service['id']) ?>" class="btn btn-sm text-white" style="background-color: #FF6B35;">
                                        <i class="fas fa-download me-2"></i>Descargar PDF
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- INFORMES COMPLETOS -->
                <div class="row mb-4">
                    <!-- INFORME DE BATERÍA DE RIESGO PSICOSOCIAL -->
                    <div class="col-md-6 mb-3">
                        <div class="card shadow-lg h-100" style="border: 3px solid #C41E3A;">
                            <div class="card-header text-white" style="background: linear-gradient(135deg, #C41E3A 0%, #8B0000 100%);">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-file-pdf fa-2x me-3"></i>
                                        <div>
                                            <h6 class="mb-0">Informe de Batería de Riesgo Psicosocial</h6>
                                            <small>Resolución 2764/2022</small>
                                        </div>
                                    </div>
                                    <span class="badge bg-light text-dark">
                                        <i class="fas fa-file-alt me-1"></i> ~80+ pág
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="small mb-2">
                                    <strong>Incluye:</strong> Portada, Contenido, Introducción, Sociodemográficos, Mapas de Calor,
                                    Totales, Dominios, Dimensiones Intralaborales, Extralaborales y Estrés.
                                </p>
                                <div class="d-grid gap-2">
                                    <a href="<?= base_url('pdfejecutivo/preview/completo/' . $service['id']) ?>" class="btn btn-outline-danger" target="_blank" style="border-color: #C41E3A; color: #C41E3A;">
                                        <i class="fas fa-eye me-2"></i>Ver HTML
                                    </a>
                                    <a href="<?= base_url('pdfejecutivo/download/' . $service['id']) ?>" class="btn text-white" style="background-color: #C41E3A;">
                                        <i class="fas fa-download me-2"></i>Descargar PDF
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- INFORME EJECUTIVO -->
                    <div class="col-md-6 mb-3">
                        <div class="card shadow-lg h-100" style="border: 3px solid #FF6B35;">
                            <div class="card-header text-white" style="background: linear-gradient(135deg, #FF6B35 0%, #E65100 100%);">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-briefcase fa-2x me-3"></i>
                                        <div>
                                            <h6 class="mb-0">Informe Ejecutivo</h6>
                                            <small>Resumen + Planes de Acción</small>
                                        </div>
                                    </div>
                                    <span class="badge bg-light text-dark">
                                        <i class="fas fa-file-alt me-1"></i> ~15-30 pág
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="small mb-2">
                                    <strong>Incluye:</strong> Portada, Mapas de Calor (resumen visual) y Recomendaciones con
                                    Planes de Acción para dimensiones en riesgo alto y muy alto.
                                </p>
                                <div class="d-grid gap-2">
                                    <a href="<?= base_url('pdfejecutivo/preview/ejecutivo/' . $service['id']) ?>" class="btn btn-outline-warning" target="_blank" style="border-color: #FF6B35; color: #FF6B35;">
                                        <i class="fas fa-eye me-2"></i>Ver HTML
                                    </a>
                                    <a href="<?= base_url('pdfejecutivo/download/ejecutivo/' . $service['id']) ?>" class="btn text-white" style="background-color: #FF6B35;">
                                        <i class="fas fa-download me-2"></i>Descargar PDF
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Botones de navegación -->
                <div class="d-flex justify-content-between">
                    <a href="<?= base_url('battery-services') ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Volver a Servicios
                    </a>
                    <div>
                        <a href="<?= base_url('battery-services/edit/' . $service['id']) ?>" class="btn btn-warning">
                            <i class="fas fa-edit me-2"></i>Editar Servicio
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
