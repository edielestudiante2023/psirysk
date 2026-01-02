<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8f9fa; }
        .history-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 2rem; margin-bottom: 2rem; border-radius: 8px; }
        .badge-ok { background-color: #198754; }
        .badge-error { background-color: #dc3545; }
        .table-responsive { overflow-x: auto; }
        table.dataTable thead th { background-color: #f8f9fa; font-weight: 600; }
        table.dataTable thead tr.filters th { background-color: #e9ecef; padding: 8px 4px; }
        table.dataTable thead tr.filters select { font-size: 0.85rem; }
        .dt-buttons { margin-bottom: 1rem; }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="history-header shadow-sm">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-2"><i class="fas fa-history me-2"></i>Historial de Validaciones</h2>
                    <p class="mb-0 opacity-75">
                        <i class="fas fa-building me-2"></i><?= esc($service['company_name']) ?> - <?= esc($service['service_name']) ?> |
                        <?= count($results) ?> validaciones registradas
                    </p>
                </div>
                <button onclick="window.close()" class="btn btn-light">
                    <i class="fas fa-times me-2"></i>Cerrar
                </button>
            </div>
        </div>

        <!-- Acciones Rápidas -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0">
                            <i class="fas fa-download me-2"></i>Descargar Respaldo
                        </h5>
                        <p class="text-muted small mb-0 mt-1">Descarga todos los registros de validación en formato Excel (CSV)</p>
                    </div>
                    <div>
                        <button id="clearFilters" class="btn btn-secondary btn-lg me-2">
                            <i class="fas fa-eraser me-2"></i>Limpiar Filtros
                        </button>
                        <a href="<?= base_url('validation/history-export/' . $service['id']) ?>" class="btn btn-success btn-lg">
                            <i class="fas fa-file-excel me-2"></i>Descargar Excel
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Validaciones -->
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-table me-2"></i>Historial Completo de Validaciones</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="historyTable" class="table table-striped table-hover table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cuestionario</th>
                                <th>Forma</th>
                                <th>Nivel</th>
                                <th>Elemento</th>
                                <th>Trabajadores</th>
                                <th>Puntaje Calc.</th>
                                <th>Puntaje BD</th>
                                <th>Diferencia</th>
                                <th>Estado</th>
                                <th>Procesado</th>
                            </tr>
                            <tr class="filters">
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results as $row): ?>
                                <tr>
                                    <td><?= esc($row['id']) ?></td>
                                    <td>
                                        <?php
                                        $typeLabels = [
                                            'intralaboral' => '<span class="badge bg-info">Intralaboral</span>',
                                            'extralaboral' => '<span class="badge bg-warning text-dark">Extralaboral</span>',
                                            'estres' => '<span class="badge bg-danger">Estrés</span>'
                                        ];
                                        echo $typeLabels[$row['questionnaire_type']] ?? esc($row['questionnaire_type']);
                                        ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary"><?= esc($row['form_type']) ?></span>
                                    </td>
                                    <td>
                                        <?php
                                        $levelLabels = [
                                            'dimension' => '<small class="text-muted">Dimensión</small>',
                                            'domain' => '<small class="text-primary fw-bold">Dominio</small>',
                                            'total' => '<small class="text-success fw-bold">TOTAL</small>'
                                        ];
                                        echo $levelLabels[$row['validation_level']] ?? esc($row['validation_level']);
                                        ?>
                                    </td>
                                    <td>
                                        <small class="text-muted d-block"><?= esc($row['element_key']) ?></small>
                                        <strong><?= esc($row['element_name']) ?></strong>
                                    </td>
                                    <td class="text-center"><?= esc($row['total_workers']) ?></td>
                                    <td class="text-end"><?= number_format($row['calculated_score'], 2) ?></td>
                                    <td class="text-end"><?= number_format($row['db_score'], 2) ?></td>
                                    <td class="text-end">
                                        <?php
                                        $diff = floatval($row['difference']);
                                        $class = abs($diff) < 0.1 ? 'text-success' : 'text-danger';
                                        ?>
                                        <span class="<?= $class ?> fw-bold"><?= number_format($diff, 2) ?></span>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($row['validation_status'] === 'ok'): ?>
                                            <span class="badge badge-ok"><i class="fas fa-check me-1"></i>OK</span>
                                        <?php else: ?>
                                            <span class="badge badge-error"><i class="fas fa-exclamation-triangle me-1"></i>Error</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small class="d-block"><?= date('d/m/Y', strtotime($row['processed_at'])) ?></small>
                                        <small class="text-muted"><?= date('H:i:s', strtotime($row['processed_at'])) ?></small>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Leyenda -->
        <div class="card shadow-sm mt-4">
            <div class="card-body">
                <h6 class="card-title"><i class="fas fa-info-circle me-2"></i>Leyenda</h6>
                <div class="row">
                    <div class="col-md-4">
                        <p class="mb-1"><strong>Niveles de Validación:</strong></p>
                        <ul class="small">
                            <li><strong>Dimensión:</strong> Validación de una dimensión individual</li>
                            <li><strong>Dominio:</strong> Validación de un dominio (agrupación de dimensiones)</li>
                            <li><strong>TOTAL:</strong> Validación del puntaje total del cuestionario</li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <p class="mb-1"><strong>Estados:</strong></p>
                        <ul class="small">
                            <li><span class="badge badge-ok">OK</span> - Diferencia menor a 0.1 (validación exitosa)</li>
                            <li><span class="badge badge-error">Error</span> - Diferencia mayor o igual a 0.1 (discrepancia detectada)</li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <p class="mb-1"><strong>Colores de Diferencia:</strong></p>
                        <ul class="small">
                            <li><span class="text-success fw-bold">Verde:</span> Diferencia &lt; 0.1 (aceptable)</li>
                            <li><span class="text-danger fw-bold">Rojo:</span> Diferencia ≥ 0.1 (requiere revisión)</li>
                        </ul>
                    </div>
                </div>
                <div class="alert alert-info mt-3 mb-0">
                    <i class="fas fa-lightbulb me-2"></i>
                    <strong>Nota:</strong> Los datos mostrados corresponden a todos los procesamientos de validación realizados.
                    Puedes usar los filtros de DataTables para buscar por tipo de cuestionario, forma, nivel, o cualquier otro campo.
                    El botón "Descargar Excel" te permite obtener un respaldo completo de estos datos para análisis externo.
                </div>
            </div>
        </div>

        <!-- Botón Volver -->
        <div class="text-center mt-4 mb-4">
            <button onclick="window.close()" class="btn btn-secondary btn-lg">
                <i class="fas fa-arrow-left me-2"></i>Volver
            </button>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

    <script>
        $(document).ready(function() {
            var table = $('#historyTable').DataTable({
                "orderCellsTop": true,
                "fixedHeader": true,
                "language": {
                    "decimal": ",",
                    "emptyTable": "No hay datos disponibles",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                    "infoEmpty": "Mostrando 0 a 0 de 0 registros",
                    "infoFiltered": "(filtrado de _MAX_ registros totales)",
                    "infoPostFix": "",
                    "thousands": ".",
                    "lengthMenu": "Mostrar _MENU_ registros",
                    "loadingRecords": "Cargando...",
                    "processing": "Procesando...",
                    "search": "Buscar:",
                    "zeroRecords": "No se encontraron registros coincidentes",
                    "paginate": {
                        "first": "Primero",
                        "last": "Último",
                        "next": "Siguiente",
                        "previous": "Anterior"
                    },
                    "aria": {
                        "sortAscending": ": activar para ordenar la columna ascendente",
                        "sortDescending": ": activar para ordenar la columna descendente"
                    }
                },
                "pageLength": 25,
                "order": [[0, "desc"]], // Ordenar por ID descendente (más recientes primero)
                "columnDefs": [
                    { "orderable": false, "targets": [4] } // Columna "Elemento" no ordenable (tiene HTML complejo)
                ],
                "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
                "responsive": true,
                "stateSave": true, // Guardar el estado de la tabla (filtros, ordenamiento, página)
                "initComplete": function() {
                    var api = this.api();

                    // Crear dropdowns para columnas específicas: Cuestionario, Forma, Nivel, Elemento, Estado
                    api.columns([1, 2, 3, 4, 9]).every(function() {
                        var column = this;
                        var colIndex = column.index();

                        var select = $('<select class="form-select form-select-sm"><option value="">Todos</option></select>')
                            .appendTo($('#historyTable thead tr.filters th').eq(colIndex))
                            .on('change', function() {
                                var val = $(this).val();

                                if (val) {
                                    // Para columna Elemento, buscar que contenga el texto (no exacto)
                                    if (colIndex === 4) {
                                        column.search(val, false, false).draw();
                                    } else {
                                        // Para otras columnas, búsqueda exacta
                                        var escapedVal = $.fn.dataTable.util.escapeRegex(val);
                                        column.search('^' + escapedVal + '$', true, false).draw();
                                    }
                                } else {
                                    column.search('', false, false).draw();
                                }
                            });

                        // Obtener valores únicos
                        var uniqueValues = [];
                        column.data().unique().sort().each(function(d, j) {
                            // Extraer texto plano de HTML (elimina tags y limpia)
                            var text = $('<div>').html(d).text().trim();

                            // Para la columna Elemento, usar solo la parte después del element_key
                            if (colIndex === 4 && text.indexOf('\n') !== -1) {
                                // Extraer solo el nombre del elemento (segunda línea)
                                var lines = text.split('\n').filter(function(line) { return line.trim(); });
                                text = lines.length > 1 ? lines[1].trim() : text;
                            }

                            if (text && uniqueValues.indexOf(text) === -1) {
                                uniqueValues.push(text);
                            }
                        });

                        // Agregar opciones al select
                        uniqueValues.forEach(function(val) {
                            select.append('<option value="' + val + '">' + val + '</option>');
                        });
                    });
                }
            });

            // Botón para limpiar todos los filtros
            $('#clearFilters').on('click', function() {
                // Resetear todos los selects a "Todos"
                $('#historyTable thead tr.filters select').val('').trigger('change');

                // Limpiar la búsqueda general de DataTables
                table.search('').draw();
            });
        });
    </script>
</body>
</html>
