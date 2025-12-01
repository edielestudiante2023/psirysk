<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - PsyRisk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }
        .main-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .gladiator-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            text-align: center;
        }
        .gladiator-header h1 {
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        .gladiator-header .subtitle {
            opacity: 0.9;
            font-size: 1.1rem;
        }
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 0.75rem;
        }
        .status-planificado { background: #3498db; color: white; }
        .status-en_curso { background: #f39c12; color: white; }
        .status-finalizado { background: #27ae60; color: white; }
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
        /* Estilos para cards mensuales */
        .month-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            cursor: pointer;
            border: 2px solid transparent;
        }
        .month-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            border-color: #667eea;
        }
        .month-card.active {
            border-color: #e74c3c;
            background: linear-gradient(135deg, #fff5f5 0%, #fff 100%);
        }
        .month-card .month-name {
            font-weight: bold;
            color: #2c3e50;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .month-card .month-services {
            font-size: 1.8rem;
            font-weight: bold;
            color: #667eea;
        }
        .month-card .month-units {
            font-size: 1rem;
            color: #27ae60;
            font-weight: 600;
        }
        .month-card.no-data {
            opacity: 0.5;
        }
        .month-card.no-data .month-services,
        .month-card.no-data .month-units {
            color: #bdc3c7;
        }
        .filters-section {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .filter-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        .year-btn {
            border-radius: 20px;
            padding: 0.5rem 1.2rem;
            margin: 0 0.25rem;
            border: 2px solid #667eea;
            background: white;
            color: #667eea;
            font-weight: 600;
            transition: all 0.3s;
        }
        .year-btn:hover, .year-btn.active {
            background: #667eea;
            color: white;
        }
        .totals-summary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .totals-summary h4 {
            margin: 0;
            font-size: 2.5rem;
            font-weight: bold;
        }
        .totals-summary p {
            margin: 0;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="container-fluid px-4">
        <!-- Header Gladiator -->
        <div class="gladiator-header">
            <div class="d-flex justify-content-between align-items-start">
                <a href="<?= base_url('commercial') ?>" class="btn btn-light btn-sm">
                    <i class="fas fa-arrow-left me-1"></i>Dashboard
                </a>
                <div class="flex-grow-1 text-center">
                    <h1><i class="fas fa-shield-alt me-2"></i>EQUIPO GLADIATOR</h1>
                    <p class="subtitle">Módulo Comercial - Gestión de Órdenes de Servicio</p>
                    <p class="mb-0"><i class="fas fa-user-tie me-2"></i><?= esc(session()->get('name')) ?></p>
                </div>
                <div style="width: 100px;"></div>
            </div>
        </div>

        <!-- Mensajes -->
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i><?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Sección de Filtros -->
        <div class="filters-section">
            <div class="row align-items-end">
                <div class="col-md-4">
                    <div class="filter-label"><i class="fas fa-calendar-alt me-2"></i>Filtrar por Año</div>
                    <div class="year-buttons" id="yearButtons">
                        <?php foreach ($availableYears as $year): ?>
                            <button type="button" class="year-btn <?= $year == $currentYear ? 'active' : '' ?>" data-year="<?= $year ?>">
                                <?= $year ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="filter-label"><i class="fas fa-calendar-day me-2"></i>Desde</div>
                    <input type="date" class="form-control" id="startDate" placeholder="Fecha inicio">
                </div>
                <div class="col-md-3">
                    <div class="filter-label"><i class="fas fa-calendar-day me-2"></i>Hasta</div>
                    <input type="date" class="form-control" id="endDate" placeholder="Fecha fin">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-outline-secondary w-100" id="clearFilters">
                        <i class="fas fa-times me-1"></i>Limpiar
                    </button>
                </div>
            </div>
        </div>

        <!-- Resumen de Totales -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="totals-summary">
                    <div class="row align-items-center">
                        <div class="col-6 text-center border-end">
                            <h4 id="totalServicesYear">0</h4>
                            <p><i class="fas fa-clipboard-list me-1"></i>Servicios</p>
                        </div>
                        <div class="col-6 text-center">
                            <h4 id="totalUnitsYear">0</h4>
                            <p><i class="fas fa-users me-1"></i>Unidades</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 d-flex align-items-center">
                <div class="text-white">
                    <h5 class="mb-1"><i class="fas fa-chart-line me-2"></i>Estadísticas del Año <span id="selectedYearLabel"><?= $currentYear ?></span></h5>
                    <p class="mb-0 opacity-75">Haz clic en un mes para filtrar la tabla</p>
                </div>
            </div>
        </div>

        <!-- Cards Mensuales -->
        <div class="row mb-4" id="monthlyCards">
            <?php
            $totalServices = 0;
            $totalUnits = 0;
            foreach ($monthlyStats as $month):
                $hasData = $month['services'] > 0;
                $totalServices += $month['services'];
                $totalUnits += $month['units'];
            ?>
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="month-card p-3 text-center <?= !$hasData ? 'no-data' : '' ?>"
                         data-month="<?= $month['month'] ?>" data-month-name="<?= $month['name'] ?>">
                        <div class="month-name mb-2"><?= $month['name'] ?></div>
                        <div class="month-services"><?= $month['services'] ?></div>
                        <small class="text-muted">servicios</small>
                        <div class="month-units mt-1">
                            <i class="fas fa-users me-1"></i><?= number_format($month['units']) ?>
                        </div>
                        <small class="text-muted">unidades</small>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Card Principal -->
        <div class="main-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>
                    <i class="fas fa-file-contract me-2"></i>Historial de Órdenes de Servicio
                    <small class="text-muted fs-6" id="tableFilterLabel"></small>
                </h3>
                <div>
                    <a href="<?= base_url('commercial/create') ?>" class="btn btn-gladiator btn-lg">
                        <i class="fas fa-plus me-2"></i>Nueva Orden de Servicio
                    </a>
                    <a href="<?= base_url('commercial') ?>" class="btn btn-secondary btn-lg ms-2">
                        <i class="fas fa-arrow-left me-2"></i>Volver al Dashboard
                    </a>
                </div>
            </div>

            <!-- Tabla de servicios -->
            <div class="table-responsive">
                <table id="ordersTable" class="table table-hover table-sm">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 50px;">ID</th>
                            <th style="width: 180px;">Cliente</th>
                            <th style="width: 150px;">Servicio</th>
                            <th style="width: 150px;">Consultor</th>
                            <th style="width: 85px;">Fecha Servicio</th>
                            <th style="width: 85px;">Vencimiento</th>
                            <th style="width: 100px;">Unidades</th>
                            <th style="width: 80px;">Estado</th>
                            <th style="width: 120px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($services)): ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                    <p>No hay órdenes de servicio registradas</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($services as $service): ?>
                                <tr>
                                    <td><strong>#<?= $service['id'] ?></strong></td>
                                    <td>
                                        <strong><?= esc($service['company_name']) ?></strong><br>
                                        <small class="text-muted">NIT: <?= esc($service['company_nit']) ?></small>
                                        <?php if (!empty($service['parent_company_name'])): ?>
                                            <br><small class="text-primary"><i class="fas fa-building"></i> <?= esc($service['parent_company_name']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span title="<?= esc($service['service_name']) ?>">
                                            <?= strlen($service['service_name']) > 30 ? esc(substr($service['service_name'], 0, 30)) . '...' : esc($service['service_name']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <i class="fas fa-user-tie me-1"></i><?= esc($service['consultant_name']) ?><br>
                                        <small class="text-muted"><?= esc($service['consultant_email']) ?></small>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($service['service_date'])) ?></td>
                                    <td>
                                        <?php
                                        $expirationDate = strtotime($service['link_expiration_date']);
                                        $today = strtotime(date('Y-m-d'));
                                        $daysRemaining = floor(($expirationDate - $today) / (60 * 60 * 24));
                                        $isExpired = $daysRemaining < 0;
                                        $isNearExpiration = $daysRemaining >= 0 && $daysRemaining <= 3;
                                        ?>
                                        <span class="<?= $isExpired ? 'text-danger' : ($isNearExpiration ? 'text-warning' : 'text-success') ?>">
                                            <?= date('d/m/Y', strtotime($service['link_expiration_date'])) ?>
                                        </span><br>
                                        <small class="<?= $isExpired ? 'text-danger' : ($isNearExpiration ? 'text-warning' : 'text-muted') ?>">
                                            <?php if ($isExpired): ?>
                                                <i class="fas fa-exclamation-triangle"></i> Vencido
                                            <?php elseif ($isNearExpiration): ?>
                                                <i class="fas fa-clock"></i> <?= $daysRemaining ?> días
                                            <?php else: ?>
                                                <?= $daysRemaining ?> días
                                            <?php endif; ?>
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <?php
                                        $formaA = $service['cantidad_forma_a'] ?? 0;
                                        $formaB = $service['cantidad_forma_b'] ?? 0;
                                        $total = $formaA + $formaB;
                                        ?>
                                        <strong><?= $total ?></strong><br>
                                        <small class="text-muted">A:<?= $formaA ?> | B:<?= $formaB ?></small>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?= $service['status'] ?>">
                                            <?= $service['status'] === 'planificado' ? 'Abierto' : ($service['status'] === 'finalizado' ? 'Cerrado' : 'En Curso') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?= base_url('battery-services/edit/' . $service['id']) ?>"
                                           class="btn btn-sm btn-warning"
                                           title="Editar orden">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= base_url('commercial/download-pdf/' . $service['id']) ?>"
                                           class="btn btn-sm btn-danger"
                                           title="Descargar PDF"
                                           target="_blank">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                        <a href="<?= base_url('workers/service/' . $service['id']) ?>"
                                           class="btn btn-sm btn-primary"
                                           title="Ver trabajadores"
                                           target="_blank">
                                            <i class="fas fa-users"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Estadísticas rápidas -->
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h3><?= count(array_filter($services, fn($s) => $s['status'] === 'planificado')) ?></h3>
                            <p class="mb-0">Órdenes Abiertas</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <h3><?= count(array_filter($services, fn($s) => $s['status'] === 'en_curso')) ?></h3>
                            <p class="mb-0">En Curso</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h3><?= count(array_filter($services, fn($s) => $s['status'] === 'finalizado')) ?></h3>
                            <p class="mb-0">Cerradas</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicializar DataTable
            var table = $('#ordersTable').DataTable({
                responsive: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },
                order: [[0, 'desc']],
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
                columnDefs: [
                    { orderable: false, targets: 8 }
                ],
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                     '<"row"<"col-sm-12"tr>>' +
                     '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                drawCallback: function() {
                    $('.pagination').addClass('pagination-sm');
                }
            });

            // Variables globales
            var currentYear = <?= $currentYear ?>;
            var selectedMonth = null;

            // Calcular y mostrar totales iniciales
            updateTotals();

            // Función para actualizar totales
            function updateTotals() {
                var totalServices = 0;
                var totalUnits = 0;
                $('.month-card').each(function() {
                    totalServices += parseInt($(this).find('.month-services').text()) || 0;
                    var unitsText = $(this).find('.month-units').text().replace(/[^\d]/g, '');
                    totalUnits += parseInt(unitsText) || 0;
                });
                $('#totalServicesYear').text(totalServices.toLocaleString('es-CO'));
                $('#totalUnitsYear').text(totalUnits.toLocaleString('es-CO'));
            }

            // Click en botones de año
            $('#yearButtons').on('click', '.year-btn', function() {
                var year = $(this).data('year');
                currentYear = year;

                // Actualizar botón activo
                $('.year-btn').removeClass('active');
                $(this).addClass('active');

                // Actualizar label
                $('#selectedYearLabel').text(year);

                // Limpiar selección de mes
                selectedMonth = null;
                $('.month-card').removeClass('active');
                $('#tableFilterLabel').text('');

                // Cargar estadísticas del año
                loadMonthlyStats(year);

                // Filtrar tabla por año
                filterTableByYear(year);
            });

            // Click en cards mensuales
            $('#monthlyCards').on('click', '.month-card', function() {
                var month = $(this).data('month');
                var monthName = $(this).data('month-name');

                // Toggle selección
                if (selectedMonth === month) {
                    // Deseleccionar
                    selectedMonth = null;
                    $('.month-card').removeClass('active');
                    $('#tableFilterLabel').text('');
                    filterTableByYear(currentYear);
                } else {
                    // Seleccionar mes
                    selectedMonth = month;
                    $('.month-card').removeClass('active');
                    $(this).addClass('active');
                    $('#tableFilterLabel').text('- ' + monthName + ' ' + currentYear);
                    filterTableByMonth(currentYear, month);
                }
            });

            // Filtros de fecha
            $('#startDate, #endDate').on('change', function() {
                var startDate = $('#startDate').val();
                var endDate = $('#endDate').val();

                if (startDate && endDate) {
                    loadMonthlyStats(currentYear, startDate, endDate);
                    filterTableByDateRange(startDate, endDate);
                    $('#tableFilterLabel').text('- Filtrado por fechas');
                }
            });

            // Limpiar filtros
            $('#clearFilters').on('click', function() {
                $('#startDate').val('');
                $('#endDate').val('');
                selectedMonth = null;
                $('.month-card').removeClass('active');
                $('#tableFilterLabel').text('');
                loadMonthlyStats(currentYear);
                table.search('').columns().search('').draw();
            });

            // Función para cargar estadísticas mensuales via AJAX
            function loadMonthlyStats(year, startDate, endDate) {
                var url = '<?= base_url('commercial/monthly-stats') ?>?year=' + year;
                if (startDate) url += '&start_date=' + startDate;
                if (endDate) url += '&end_date=' + endDate;

                $.get(url, function(response) {
                    if (response.success) {
                        updateMonthlyCards(response.stats);
                        updateTotals();
                    }
                });
            }

            // Función para actualizar las cards con nuevos datos
            function updateMonthlyCards(stats) {
                for (var month in stats) {
                    var data = stats[month];
                    var card = $('.month-card[data-month="' + month + '"]');
                    card.find('.month-services').text(data.services);
                    card.find('.month-units').html('<i class="fas fa-users me-1"></i>' + data.units.toLocaleString('es-CO'));

                    if (data.services > 0) {
                        card.removeClass('no-data');
                    } else {
                        card.addClass('no-data');
                    }
                }
            }

            // Función para filtrar tabla por año
            function filterTableByYear(year) {
                table.column(4).search(year).draw(); // Columna de Fecha Servicio
            }

            // Función para filtrar tabla por mes específico
            function filterTableByMonth(year, month) {
                var monthStr = month.toString().padStart(2, '0');
                // Buscar formato dd/mm/yyyy donde mm coincide con el mes
                var regex = '\\/' + monthStr + '\\/' + year;
                table.column(4).search(regex, true, false).draw();
            }

            // Función para filtrar por rango de fechas
            function filterTableByDateRange(startDate, endDate) {
                // Para filtro de rango de fechas, usamos una búsqueda personalizada
                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    var dateStr = data[4]; // Columna de fecha
                    if (!dateStr) return false;

                    // Convertir dd/mm/yyyy a Date
                    var parts = dateStr.split('/');
                    if (parts.length !== 3) return true;

                    var rowDate = new Date(parts[2], parts[1] - 1, parts[0]);
                    var start = new Date(startDate);
                    var end = new Date(endDate);

                    return rowDate >= start && rowDate <= end;
                });

                table.draw();

                // Limpiar el filtro personalizado después de usarlo
                $.fn.dataTable.ext.search.pop();
            }
        });
    </script>
</body>
</html>
