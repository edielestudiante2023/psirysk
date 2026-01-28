<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - PsyRisk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
            font-size: 0.9rem;
        }
        .navbar-custom {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .stat-card {
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            border: none;
        }
        .stat-card .icon {
            font-size: 2rem;
            opacity: 0.8;
        }
        .section-title {
            border-left: 4px solid #667eea;
            padding-left: 15px;
            margin-bottom: 15px;
            font-size: 1.1rem;
        }
        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 15px;
        }
        .chart-container-sm {
            position: relative;
            height: 250px;
            margin-bottom: 15px;
        }
        .segmentador-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }
        .filter-group {
            margin-bottom: 15px;
        }
        .filter-group label {
            font-weight: 600;
            font-size: 0.85rem;
            color: #495057;
            margin-bottom: 5px;
        }
        .filter-group select {
            font-size: 0.85rem;
        }
        .badge-forma-a { background-color: #bbdefb; color: #1565c0; }
        .badge-forma-b { background-color: #f8bbd9; color: #c2185b; }
        .total-badge {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 600;
            margin: 5px;
        }
        .chart-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }
        .chart-card .card-header {
            background: white;
            border-bottom: 1px solid #eee;
            padding: 12px 20px;
        }
        .chart-card .card-body {
            padding: 15px;
        }
        table.dataTable {
            font-size: 0.85rem;
        }
        @media print {
            .no-print { display: none !important; }
            body { font-size: 0.7rem; }
        }
    </style>
</head>
<body>
    <!-- Top Navbar -->
    <nav class="navbar navbar-custom navbar-expand-lg p-3 no-print">
        <div class="container-fluid">
            <?php
            $userRole = session()->get('role_name');
            $backUrl = in_array($userRole, ['cliente_empresa', 'cliente_gestor'])
                ? base_url('client/battery-services/' . $service['id'])
                : base_url('battery-services/' . $service['id']);
            ?>
            <a href="<?= $backUrl ?>" class="btn btn-outline-secondary btn-sm me-3">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <h5 class="mb-0"><i class="fas fa-id-card me-2 text-primary"></i><?= $title ?></h5>
            <div class="ms-auto">
                <button class="btn btn-success btn-sm me-2" onclick="window.print()">
                    <i class="fas fa-print me-1"></i>Imprimir
                </button>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <div class="container-fluid p-4">
        <!-- Información del Servicio -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body p-3">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-1"><?= esc($service['service_name']) ?></h6>
                        <p class="mb-0 small text-muted">
                            <i class="fas fa-building me-1"></i><?= esc($service['company_name']) ?>
                            <span class="mx-2">|</span>
                            <i class="fas fa-calendar me-1"></i><?= date('d/m/Y', strtotime($service['service_date'])) ?>
                        </p>
                    </div>
                    <div class="col-md-6 text-end">
                        <span class="badge bg-primary">Total: <?= $totalWorkers ?> trabajadores</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas Generales -->
        <div class="row mb-3">
            <div class="col-md-3">
                <div class="card stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1" style="font-size: 0.85rem; opacity: 0.9;">Total Trabajadores</h6>
                            <h2 class="fw-bold mb-0"><?= $totalWorkers ?></h2>
                        </div>
                        <i class="fas fa-users" style="font-size: 2.5rem; opacity: 0.6;"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1" style="font-size: 0.85rem; opacity: 0.9;">Forma A</h6>
                            <h2 class="fw-bold mb-0"><?= $stats['form_type']['A'] ?? 0 ?></h2>
                            <small style="opacity: 0.8;">Jefes, profesionales, técnicos</small>
                        </div>
                        <i class="fas fa-user-tie" style="font-size: 2.5rem; opacity: 0.6;"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1" style="font-size: 0.85rem; opacity: 0.9;">Forma B</h6>
                            <h2 class="fw-bold mb-0"><?= $stats['form_type']['B'] ?? 0 ?></h2>
                            <small style="opacity: 0.8;">Auxiliares, operarios</small>
                        </div>
                        <i class="fas fa-hard-hat" style="font-size: 2.5rem; opacity: 0.6;"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1" style="font-size: 0.85rem; opacity: 0.9;">Departamentos</h6>
                            <h2 class="fw-bold mb-0"><?= count($stats['department']) ?></h2>
                            <small style="opacity: 0.8;">Áreas diferentes</small>
                        </div>
                        <i class="fas fa-sitemap" style="font-size: 2.5rem; opacity: 0.6;"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Segmentadores y Filtros -->
        <div class="segmentador-card no-print">
            <h6 class="fw-bold mb-3">
                <i class="fas fa-filter text-primary me-2"></i>Segmentadores y Filtros
            </h6>

            <!-- Filtros Demográficos -->
            <div class="alert alert-light border mb-3">
                <h6 class="fw-bold mb-2 small"><i class="fas fa-users text-info me-2"></i>Filtros Demográficos</h6>
                <div class="row">
                    <!-- Género -->
                    <div class="col-md-2">
                        <div class="filter-group">
                            <label><i class="fas fa-venus-mars me-1"></i>Género</label>
                            <select class="form-select form-select-sm" id="filter_gender">
                                <option value="">Todos</option>
                                <?php foreach ($segmentadores['generos'] as $genero): ?>
                                    <option value="<?= esc($genero) ?>"><?= esc($genero) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Departamento -->
                    <div class="col-md-2">
                        <div class="filter-group">
                            <label><i class="fas fa-sitemap me-1"></i>Departamento</label>
                            <select class="form-select form-select-sm" id="filter_department">
                                <option value="">Todos</option>
                                <?php foreach ($segmentadores['departamentos'] as $dept): ?>
                                    <option value="<?= esc($dept) ?>"><?= esc($dept) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Tipo de Cargo -->
                    <div class="col-md-2">
                        <div class="filter-group">
                            <label><i class="fas fa-user-tag me-1"></i>Tipo de Cargo</label>
                            <select class="form-select form-select-sm" id="filter_position_type">
                                <option value="">Todos</option>
                                <?php foreach ($segmentadores['tipos_cargo'] as $tipo): ?>
                                    <option value="<?= esc($tipo) ?>"><?= esc($tipo) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Nivel de Estudios -->
                    <div class="col-md-2">
                        <div class="filter-group">
                            <label><i class="fas fa-graduation-cap me-1"></i>Nivel Estudios</label>
                            <select class="form-select form-select-sm" id="filter_education">
                                <option value="">Todos</option>
                                <?php foreach ($segmentadores['niveles_estudio'] as $nivel): ?>
                                    <option value="<?= esc($nivel) ?>"><?= esc(str_replace('_', ' ', $nivel)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Estado Civil -->
                    <div class="col-md-2">
                        <div class="filter-group">
                            <label><i class="fas fa-heart me-1"></i>Estado Civil</label>
                            <select class="form-select form-select-sm" id="filter_marital_status">
                                <option value="">Todos</option>
                                <?php foreach ($segmentadores['estados_civiles'] as $estado): ?>
                                    <option value="<?= esc($estado) ?>"><?= esc(str_replace('_', ' ', $estado)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Tipo Formulario -->
                    <div class="col-md-2">
                        <div class="filter-group">
                            <label><i class="fas fa-file-alt me-1"></i>Tipo Formulario</label>
                            <select class="form-select form-select-sm" id="filter_form_type">
                                <option value="">Todos</option>
                                <option value="A">Forma A</option>
                                <option value="B">Forma B</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros Laborales y Ubicación -->
            <div class="alert alert-light border mb-0">
                <h6 class="fw-bold mb-2 small"><i class="fas fa-briefcase text-success me-2"></i>Filtros Laborales y Ubicación</h6>
                <div class="row">
                    <!-- Tipo de Contrato -->
                    <div class="col-md-2">
                        <div class="filter-group">
                            <label><i class="fas fa-file-contract me-1"></i>Tipo Contrato</label>
                            <select class="form-select form-select-sm" id="filter_contract_type">
                                <option value="">Todos</option>
                                <?php foreach ($segmentadores['tipos_contrato'] as $tipo): ?>
                                    <option value="<?= esc($tipo) ?>"><?= esc(str_replace('_', ' ', $tipo)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Ciudad -->
                    <div class="col-md-2">
                        <div class="filter-group">
                            <label><i class="fas fa-map-marker-alt me-1"></i>Ciudad</label>
                            <select class="form-select form-select-sm" id="filter_city">
                                <option value="">Todas</option>
                                <?php foreach ($segmentadores['ciudades'] as $ciudad): ?>
                                    <option value="<?= esc($ciudad) ?>"><?= esc($ciudad) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Estrato -->
                    <div class="col-md-2">
                        <div class="filter-group">
                            <label><i class="fas fa-layer-group me-1"></i>Estrato</label>
                            <select class="form-select form-select-sm" id="filter_stratum">
                                <option value="">Todos</option>
                                <?php foreach ($segmentadores['estratos'] as $estrato): ?>
                                    <option value="<?= esc($estrato) ?>"><?= esc($estrato) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Tipo de Vivienda -->
                    <div class="col-md-2">
                        <div class="filter-group">
                            <label><i class="fas fa-home me-1"></i>Tipo Vivienda</label>
                            <select class="form-select form-select-sm" id="filter_housing_type">
                                <option value="">Todos</option>
                                <?php foreach ($segmentadores['tipos_vivienda'] as $tipo): ?>
                                    <option value="<?= esc($tipo) ?>"><?= esc($tipo) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Antigüedad -->
                    <div class="col-md-2">
                        <div class="filter-group">
                            <label><i class="fas fa-calendar-alt me-1"></i>Antigüedad</label>
                            <select class="form-select form-select-sm" id="filter_time_in_company">
                                <option value="">Todos</option>
                                <?php foreach ($segmentadores['antiguedad'] as $label => $valor): ?>
                                    <option value="<?= esc($valor) ?>"><?= esc($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Botón Limpiar -->
                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-secondary btn-sm w-100" onclick="clearAllFilters()">
                            <i class="fas fa-redo me-1"></i>Limpiar Filtros
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contador de Resultados Filtrados -->
        <div class="alert alert-info mb-3 no-print">
            <i class="fas fa-filter me-2"></i>
            Mostrando <strong id="filteredCount"><?= $totalWorkers ?></strong> de <?= $totalWorkers ?> trabajadores
        </div>

        <!-- GRÁFICOS - Fila 1: Género, Estado Civil, Tipo Formulario -->
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="chart-card">
                    <div class="card-header">
                        <h6 class="mb-0 small fw-bold">
                            <i class="fas fa-venus-mars text-primary me-1"></i>Distribución por Género
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="genderChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="chart-card">
                    <div class="card-header">
                        <h6 class="mb-0 small fw-bold">
                            <i class="fas fa-heart text-danger me-1"></i>Distribución por Estado Civil
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="maritalChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="chart-card">
                    <div class="card-header">
                        <h6 class="mb-0 small fw-bold">
                            <i class="fas fa-file-alt text-info me-1"></i>Distribución por Tipo de Formulario
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="formTypeChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- GRÁFICOS - Fila 2: Rangos de Edad, Nivel Educativo -->
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="chart-card">
                    <div class="card-header">
                        <h6 class="mb-0 small fw-bold">
                            <i class="fas fa-birthday-cake text-warning me-1"></i>Distribución por Rango de Edad
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="ageChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-card">
                    <div class="card-header">
                        <h6 class="mb-0 small fw-bold">
                            <i class="fas fa-graduation-cap text-success me-1"></i>Distribución por Nivel Educativo
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="educationChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- GRÁFICOS - Fila 3: Tipo de Cargo, Tipo de Contrato -->
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="chart-card">
                    <div class="card-header">
                        <h6 class="mb-0 small fw-bold">
                            <i class="fas fa-user-tag text-primary me-1"></i>Distribución por Tipo de Cargo
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="positionTypeChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-card">
                    <div class="card-header">
                        <h6 class="mb-0 small fw-bold">
                            <i class="fas fa-file-contract text-info me-1"></i>Distribución por Tipo de Contrato
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="contractChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- GRÁFICOS - Fila 4: Estrato, Tipo de Vivienda, Antigüedad -->
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="chart-card">
                    <div class="card-header">
                        <h6 class="mb-0 small fw-bold">
                            <i class="fas fa-layer-group text-secondary me-1"></i>Distribución por Estrato
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="stratumChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="chart-card">
                    <div class="card-header">
                        <h6 class="mb-0 small fw-bold">
                            <i class="fas fa-home text-warning me-1"></i>Distribución por Tipo de Vivienda
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="housingChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="chart-card">
                    <div class="card-header">
                        <h6 class="mb-0 small fw-bold">
                            <i class="fas fa-calendar-alt text-success me-1"></i>Distribución por Antigüedad
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="timeCompanyChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- GRÁFICOS - Fila 5: Personas a Cargo, Horas por Día -->
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="chart-card">
                    <div class="card-header">
                        <h6 class="mb-0 small fw-bold">
                            <i class="fas fa-users text-info me-1"></i>Distribución por Personas a Cargo
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="dependentsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-card">
                    <div class="card-header">
                        <h6 class="mb-0 small fw-bold">
                            <i class="fas fa-clock text-danger me-1"></i>Distribución por Horas de Trabajo Diarias
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="hoursChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- GRÁFICOS - Fila 6: Departamentos (Top 10) -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="chart-card">
                    <div class="card-header">
                        <h6 class="mb-0 small fw-bold">
                            <i class="fas fa-sitemap text-primary me-1"></i>Distribución por Departamento/Área (Top 10)
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="height: 350px;">
                            <canvas id="departmentChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Resultados -->
        <div class="row">
            <div class="col-12">
                <h6 class="section-title">Detalle de Trabajadores</h6>
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-3">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover" id="resultsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Documento</th>
                                        <th>Género</th>
                                        <th>Edad</th>
                                        <th>Estado Civil</th>
                                        <th>Nivel Estudios</th>
                                        <th>Cargo</th>
                                        <th>Departamento</th>
                                        <th>Tipo Contrato</th>
                                        <th>Forma</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($results as $result): ?>
                                        <tr data-result='<?= htmlspecialchars(json_encode($result), ENT_QUOTES, 'UTF-8') ?>'>
                                            <td><?= esc($result['worker_name']) ?></td>
                                            <td><?= esc($result['worker_document']) ?></td>
                                            <td><?= esc($result['gender']) ?></td>
                                            <td><?= esc($result['age']) ?> años</td>
                                            <td><?= esc(str_replace('_', ' ', $result['marital_status'])) ?></td>
                                            <td><?= esc(str_replace('_', ' ', $result['education_level'])) ?></td>
                                            <td><?= esc($result['position_name']) ?></td>
                                            <td><?= esc($result['department']) ?></td>
                                            <td><?= esc(str_replace('_', ' ', $result['contract_type'])) ?></td>
                                            <td>
                                                <span class="badge <?= $result['intralaboral_form_type'] === 'A' ? 'badge-forma-a' : 'badge-forma-b' ?>">
                                                    Forma <?= esc($result['intralaboral_form_type']) ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <a href="<?= base_url('workers/results/' . $result['worker_id']) ?>"
                                                   class="btn btn-sm btn-primary"
                                                   title="Ver resultados individuales"
                                                   target="_blank">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        // Deshabilitar plugin datalabels globalmente
        Chart.defaults.plugins.datalabels = {
            display: false
        };

        // Datos desde PHP
        const allResults = <?= json_encode($results) ?>;
        const initialStats = <?= json_encode($stats) ?>;

        // Paleta de colores variados (sin predominancia púrpura)
        const colorPalette = [
            '#3498db', '#e74c3c', '#2ecc71', '#f39c12', '#1abc9c', '#9b59b6',
            '#34495e', '#e67e22', '#16a085', '#27ae60', '#2980b9', '#8e44ad',
            '#f1c40f', '#d35400', '#c0392b', '#7f8c8d', '#2c3e50', '#95a5a6'
        ];

        const pieColors = ['#3498db', '#2ecc71', '#e74c3c', '#f39c12', '#1abc9c', '#9b59b6'];

        // Inicializar DataTable
        let dataTable = $('#resultsTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
            },
            pageLength: 25,
            order: [[0, 'asc']]
        });

        // Función para formatear etiquetas
        function formatLabel(label) {
            if (!label) return 'No especificado';
            return label.replace(/_/g, ' ').replace(/^\w/, c => c.toUpperCase());
        }

        // Función para crear gráfico de torta
        function createPieChart(ctx, data, title) {
            const labels = Object.keys(data).map(formatLabel);
            const values = Object.values(data);

            return new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: pieColors.slice(0, labels.length)
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'right', labels: { boxWidth: 12, font: { size: 10 } } },
                        datalabels: {
                            color: '#fff',
                            font: { weight: 'bold', size: 11 },
                            formatter: function(value, context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return value > 0 ? `${value}\n(${percentage}%)` : '';
                            }
                        }
                    }
                },
                plugins: [ChartDataLabels]
            });
        }

        // Función para crear gráfico de barras horizontal
        function createBarChart(ctx, data, title) {
            const labels = Object.keys(data).map(formatLabel);
            const values = Object.values(data);

            return new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: colorPalette.slice(0, labels.length),
                        borderWidth: 0
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        datalabels: {
                            anchor: 'end',
                            align: 'end',
                            color: '#333',
                            font: { weight: 'bold', size: 11 },
                            formatter: function(value) {
                                return value;
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: { stepSize: 1 }
                        }
                    }
                },
                plugins: [ChartDataLabels]
            });
        }

        // Función para crear gráfico de barras vertical
        function createVerticalBarChart(ctx, data, title) {
            const labels = Object.keys(data).map(formatLabel);
            const values = Object.values(data);

            return new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: colorPalette.slice(0, labels.length),
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        datalabels: {
                            anchor: 'end',
                            align: 'top',
                            color: '#333',
                            font: { weight: 'bold', size: 11 },
                            formatter: function(value) {
                                return value;
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1 }
                        }
                    }
                },
                plugins: [ChartDataLabels]
            });
        }

        // Crear todos los gráficos
        let genderChart = createPieChart(
            document.getElementById('genderChart').getContext('2d'),
            initialStats.gender,
            'Género'
        );

        let maritalChart = createPieChart(
            document.getElementById('maritalChart').getContext('2d'),
            initialStats.marital_status,
            'Estado Civil'
        );

        let formTypeChart = createPieChart(
            document.getElementById('formTypeChart').getContext('2d'),
            initialStats.form_type,
            'Tipo Formulario'
        );

        let ageChart = createVerticalBarChart(
            document.getElementById('ageChart').getContext('2d'),
            initialStats.age_ranges,
            'Rango de Edad'
        );

        let educationChart = createBarChart(
            document.getElementById('educationChart').getContext('2d'),
            initialStats.education_level,
            'Nivel Educativo'
        );

        let positionTypeChart = createPieChart(
            document.getElementById('positionTypeChart').getContext('2d'),
            initialStats.position_type,
            'Tipo de Cargo'
        );

        let contractChart = createPieChart(
            document.getElementById('contractChart').getContext('2d'),
            initialStats.contract_type,
            'Tipo de Contrato'
        );

        let stratumChart = createPieChart(
            document.getElementById('stratumChart').getContext('2d'),
            initialStats.stratum,
            'Estrato'
        );

        let housingChart = createPieChart(
            document.getElementById('housingChart').getContext('2d'),
            initialStats.housing_type,
            'Tipo de Vivienda'
        );

        let timeCompanyChart = createPieChart(
            document.getElementById('timeCompanyChart').getContext('2d'),
            initialStats.time_in_company,
            'Antigüedad'
        );

        let dependentsChart = createPieChart(
            document.getElementById('dependentsChart').getContext('2d'),
            initialStats.dependents,
            'Personas a Cargo'
        );

        let hoursChart = createPieChart(
            document.getElementById('hoursChart').getContext('2d'),
            initialStats.hours_per_day,
            'Horas de Trabajo'
        );

        // Top 10 departamentos
        let departmentData = initialStats.department;
        let sortedDepts = Object.entries(departmentData)
            .sort((a, b) => b[1] - a[1])
            .slice(0, 10);
        let top10Depts = Object.fromEntries(sortedDepts);

        let departmentChart = createBarChart(
            document.getElementById('departmentChart').getContext('2d'),
            top10Depts,
            'Departamentos'
        );

        // Función para calcular estadísticas de resultados filtrados
        function calculateStats(results) {
            const stats = {
                gender: {},
                marital_status: {},
                education_level: {},
                position_type: {},
                contract_type: {},
                stratum: {},
                housing_type: {},
                department: {},
                age_ranges: { '18-25': 0, '26-35': 0, '36-45': 0, '46-55': 0, '56-65': 0, '65+': 0 },
                time_in_company: {},
                form_type: {},
                dependents: {},
                hours_per_day: {}
            };

            results.forEach(r => {
                // Género
                const gender = r.gender || 'No especificado';
                stats.gender[gender] = (stats.gender[gender] || 0) + 1;

                // Estado civil
                const marital = r.marital_status || 'No especificado';
                stats.marital_status[marital] = (stats.marital_status[marital] || 0) + 1;

                // Nivel de estudios
                const education = r.education_level || 'No especificado';
                stats.education_level[education] = (stats.education_level[education] || 0) + 1;

                // Tipo de cargo
                const positionType = r.position_type || 'No especificado';
                stats.position_type[positionType] = (stats.position_type[positionType] || 0) + 1;

                // Tipo de contrato
                const contract = r.contract_type || 'No especificado';
                stats.contract_type[contract] = (stats.contract_type[contract] || 0) + 1;

                // Estrato
                const stratum = r.stratum || 'No especificado';
                stats.stratum[stratum] = (stats.stratum[stratum] || 0) + 1;

                // Tipo de vivienda
                const housing = r.housing_type || 'No especificado';
                stats.housing_type[housing] = (stats.housing_type[housing] || 0) + 1;

                // Departamento
                const dept = r.department || 'No especificado';
                stats.department[dept] = (stats.department[dept] || 0) + 1;

                // Tipo de formulario
                const formType = r.intralaboral_form_type || 'No especificado';
                stats.form_type[formType] = (stats.form_type[formType] || 0) + 1;

                // Antigüedad
                const timeCompany = r.time_in_company_type || 'No especificado';
                stats.time_in_company[timeCompany] = (stats.time_in_company[timeCompany] || 0) + 1;

                // Personas a cargo
                const dependents = r.dependents || 0;
                const depKey = dependents == 0 ? 'Sin dependientes' : (dependents <= 2 ? '1-2 personas' : (dependents <= 4 ? '3-4 personas' : '5+ personas'));
                stats.dependents[depKey] = (stats.dependents[depKey] || 0) + 1;

                // Horas por día
                const hours = r.hours_per_day || 0;
                const hoursKey = hours <= 6 ? '6 horas o menos' : (hours <= 8 ? '7-8 horas' : (hours <= 10 ? '9-10 horas' : 'Más de 10 horas'));
                stats.hours_per_day[hoursKey] = (stats.hours_per_day[hoursKey] || 0) + 1;

                // Rangos de edad
                const age = parseInt(r.age) || 0;
                if (age >= 18 && age <= 25) stats.age_ranges['18-25']++;
                else if (age >= 26 && age <= 35) stats.age_ranges['26-35']++;
                else if (age >= 36 && age <= 45) stats.age_ranges['36-45']++;
                else if (age >= 46 && age <= 55) stats.age_ranges['46-55']++;
                else if (age >= 56 && age <= 65) stats.age_ranges['56-65']++;
                else if (age > 65) stats.age_ranges['65+']++;
            });

            return stats;
        }

        // Función para actualizar gráfico de torta
        function updatePieChart(chart, data) {
            const labels = Object.keys(data).map(formatLabel);
            const values = Object.values(data);
            chart.data.labels = labels;
            chart.data.datasets[0].data = values;
            chart.data.datasets[0].backgroundColor = pieColors.slice(0, labels.length);
            chart.update();
        }

        // Función para actualizar gráfico de barras
        function updateBarChart(chart, data, isVertical = false) {
            const labels = Object.keys(data).map(formatLabel);
            const values = Object.values(data);
            chart.data.labels = labels;
            chart.data.datasets[0].data = values;
            chart.data.datasets[0].backgroundColor = colorPalette.slice(0, labels.length);
            chart.update();
        }

        // Función para actualizar todos los gráficos
        function updateCharts(filteredResults) {
            const newStats = calculateStats(filteredResults);

            updatePieChart(genderChart, newStats.gender);
            updatePieChart(maritalChart, newStats.marital_status);
            updatePieChart(formTypeChart, newStats.form_type);
            updateBarChart(ageChart, newStats.age_ranges, true);
            updateBarChart(educationChart, newStats.education_level);
            updatePieChart(positionTypeChart, newStats.position_type);
            updatePieChart(contractChart, newStats.contract_type);
            updatePieChart(stratumChart, newStats.stratum);
            updatePieChart(housingChart, newStats.housing_type);
            updatePieChart(timeCompanyChart, newStats.time_in_company);
            updatePieChart(dependentsChart, newStats.dependents);
            updatePieChart(hoursChart, newStats.hours_per_day);

            // Top 10 departamentos
            const sortedDepts = Object.entries(newStats.department)
                .sort((a, b) => b[1] - a[1])
                .slice(0, 10);
            updateBarChart(departmentChart, Object.fromEntries(sortedDepts));
        }

        // Función para aplicar filtros
        function applyFilters() {
            const filters = {
                gender: document.getElementById('filter_gender').value,
                department: document.getElementById('filter_department').value,
                position_type: document.getElementById('filter_position_type').value,
                education: document.getElementById('filter_education').value,
                marital_status: document.getElementById('filter_marital_status').value,
                form_type: document.getElementById('filter_form_type').value,
                contract_type: document.getElementById('filter_contract_type').value,
                city: document.getElementById('filter_city').value,
                stratum: document.getElementById('filter_stratum').value,
                housing_type: document.getElementById('filter_housing_type').value,
                time_in_company: document.getElementById('filter_time_in_company').value
            };

            // Filtrar resultados
            const filteredResults = allResults.filter(r => {
                return (!filters.gender || r.gender === filters.gender) &&
                       (!filters.department || r.department === filters.department) &&
                       (!filters.position_type || r.position_type === filters.position_type) &&
                       (!filters.education || r.education_level === filters.education) &&
                       (!filters.marital_status || r.marital_status === filters.marital_status) &&
                       (!filters.form_type || r.intralaboral_form_type === filters.form_type) &&
                       (!filters.contract_type || r.contract_type === filters.contract_type) &&
                       (!filters.city || r.city_residence === filters.city) &&
                       (!filters.stratum || r.stratum == filters.stratum) &&
                       (!filters.housing_type || r.housing_type === filters.housing_type) &&
                       (!filters.time_in_company || r.time_in_company_type === filters.time_in_company);
            });

            // Actualizar contador
            document.getElementById('filteredCount').textContent = filteredResults.length;

            // Actualizar tabla
            dataTable.clear();
            filteredResults.forEach(r => {
                const formBadgeClass = r.intralaboral_form_type === 'A' ? 'badge-forma-a' : 'badge-forma-b';
                const row = `<tr>
                    <td>${r.worker_name || ''}</td>
                    <td>${r.worker_document || ''}</td>
                    <td>${r.gender || ''}</td>
                    <td>${r.age || ''} años</td>
                    <td>${formatLabel(r.marital_status)}</td>
                    <td>${formatLabel(r.education_level)}</td>
                    <td>${r.position_name || ''}</td>
                    <td>${r.department || ''}</td>
                    <td>${formatLabel(r.contract_type)}</td>
                    <td><span class="badge ${formBadgeClass}">Forma ${r.intralaboral_form_type || ''}</span></td>
                    <td class="text-center">
                        <a href="<?= base_url() ?>workers/results/${r.worker_id}"
                           class="btn btn-sm btn-primary"
                           title="Ver resultados individuales"
                           target="_blank">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>`;
                dataTable.row.add($(row)).draw(false);
            });

            // Actualizar gráficos
            updateCharts(filteredResults);
        }

        // Función para limpiar filtros
        function clearAllFilters() {
            document.querySelectorAll('select[id^="filter_"]').forEach(select => {
                select.value = '';
            });
            applyFilters();
        }

        // Agregar event listeners a los filtros
        document.querySelectorAll('select[id^="filter_"]').forEach(select => {
            select.addEventListener('change', applyFilters);
        });
    </script>
</body>
</html>
