<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - PsyRisk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
            font-size: 0.85rem;
        }
        .navbar-custom {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .section-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            margin: 20px 0 10px 0;
            font-weight: 600;
        }
        .table-consolidacion {
            font-size: 0.8rem;
            margin-bottom: 0;
        }
        .table-consolidacion th {
            background-color: #f8f9fa;
            font-weight: 600;
            text-align: center;
            vertical-align: middle;
            border: 1px solid #dee2e6;
        }
        .table-consolidacion td {
            text-align: center;
            vertical-align: middle;
            border: 1px solid #dee2e6;
        }
        .table-consolidacion .escala-nombre {
            text-align: left;
            font-weight: 500;
            background-color: #fff9e6;
        }
        .header-forma-a {
            background-color: #e3f2fd !important;
        }
        .header-forma-b {
            background-color: #fce4ec !important;
        }
        /* ELIMINADO: header-conjunto - no existe baremo para mezclar formas */
        .cell-bajo { background-color: #c8e6c9; }
        .cell-medio { background-color: #fff9c4; }
        .cell-alto { background-color: #ffcdd2; }
        .chart-container {
            position: relative;
            height: 300px;
            margin: 15px 0;
        }
        .totales-card {
            background: white;
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }
        .total-badge {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 600;
            margin: 5px;
        }
        .badge-forma-a { background-color: #bbdefb; color: #1565c0; }
        .badge-forma-b { background-color: #f8bbd9; color: #c2185b; }
        .badge-total { background-color: #e0e0e0; color: #424242; }
        .badge-masculino { background-color: #90caf9; color: #0d47a1; }
        .badge-femenino { background-color: #f48fb1; color: #880e4f; }
        @media print {
            .no-print { display: none !important; }
            .section-header { break-before: avoid; }
            body { font-size: 0.7rem; }
        }
    </style>
</head>
<body>
    <!-- Top Navbar -->
    <nav class="navbar navbar-custom navbar-expand-lg p-3 no-print">
        <div class="container-fluid">
            <a href="<?= base_url('dashboard') ?>" class="btn btn-outline-secondary btn-sm me-3">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <h5 class="mb-0"><i class="fas fa-chart-bar me-2 text-primary"></i><?= $title ?></h5>
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
                        <span class="badge bg-primary">Total: <?= $totales['conjunto'] ?> trabajadores</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Totales por Forma y Género -->
        <div class="totales-card">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="fw-bold mb-2"><i class="fas fa-users me-2"></i>Distribución por Forma</h6>
                    <span class="total-badge badge-forma-a">
                        <i class="fas fa-user-tie me-1"></i>Forma A: <?= $totales['forma_a'] ?>
                    </span>
                    <span class="total-badge badge-forma-b">
                        <i class="fas fa-user me-1"></i>Forma B: <?= $totales['forma_b'] ?>
                    </span>
                    <span class="total-badge badge-conjunto">
                        <i class="fas fa-users me-1"></i>Total: <?= $totales['conjunto'] ?>
                    </span>
                </div>
                <div class="col-md-6">
                    <h6 class="fw-bold mb-2"><i class="fas fa-venus-mars me-2"></i>Distribución por Género</h6>
                    <span class="total-badge badge-masculino">
                        <i class="fas fa-mars me-1"></i>Masculino: <?= $genero['masculino'] ?>
                    </span>
                    <span class="total-badge badge-femenino">
                        <i class="fas fa-venus me-1"></i>Femenino: <?= $genero['femenino'] ?>
                    </span>
                </div>
            </div>
        </div>

        <?php
        // Nombres de secciones para mostrar
        $nombresSecciones = [
            'total_general' => 'Resultado Total General',
            'intralaboral' => 'Intralaboral - Total',
            'dominios_intralaboral' => 'Intralaboral - Dominios',
            'dimensiones_liderazgo' => 'Dimensiones - Liderazgo y Relaciones Sociales',
            'dimensiones_control' => 'Dimensiones - Control sobre el Trabajo',
            'dimensiones_demandas' => 'Dimensiones - Demandas del Trabajo',
            'dimensiones_recompensas' => 'Dimensiones - Recompensas',
            'extralaboral' => 'Extralaboral - Total',
            'dimensiones_extralaboral' => 'Extralaboral - Dimensiones',
            'estres' => 'Estrés'
        ];
        ?>

        <?php foreach ($consolidacion as $seccion => $items): ?>
            <div class="section-header">
                <i class="fas fa-chart-pie me-2"></i><?= $nombresSecciones[$seccion] ?? $seccion ?>
            </div>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-consolidacion table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th rowspan="2" style="min-width: 250px;">ESCALAS</th>
                                    <th colspan="3" class="header-forma-a">FORMA A</th>
                                    <th colspan="3" class="header-forma-b">FORMA B</th>
                                </tr>
                                <tr>
                                    <!-- Forma A -->
                                    <th class="header-forma-a cell-bajo" style="width: 80px;">Bajo y<br>sin riesgo</th>
                                    <th class="header-forma-a cell-medio" style="width: 80px;">Riesgo<br>medio</th>
                                    <th class="header-forma-a cell-alto" style="width: 80px;">Riesgo alto<br>y muy alto</th>
                                    <!-- Forma B -->
                                    <th class="header-forma-b cell-bajo" style="width: 80px;">Bajo y<br>sin riesgo</th>
                                    <th class="header-forma-b cell-medio" style="width: 80px;">Riesgo<br>medio</th>
                                    <th class="header-forma-b cell-alto" style="width: 80px;">Riesgo alto<br>y muy alto</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $key => $data): ?>
                                <tr>
                                    <td class="escala-nombre"><?= esc($data['label']) ?></td>
                                    <!-- Forma A -->
                                    <td class="cell-bajo"><?= $data['forma_a']['porcentaje']['bajo_sin_riesgo'] ?>%</td>
                                    <td class="cell-medio"><?= $data['forma_a']['porcentaje']['riesgo_medio'] ?>%</td>
                                    <td class="cell-alto"><?= $data['forma_a']['porcentaje']['alto_muy_alto'] ?>%</td>
                                    <!-- Forma B -->
                                    <td class="cell-bajo"><?= $data['forma_b']['porcentaje']['bajo_sin_riesgo'] ?>%</td>
                                    <td class="cell-medio"><?= $data['forma_b']['porcentaje']['riesgo_medio'] ?>%</td>
                                    <td class="cell-alto"><?= $data['forma_b']['porcentaje']['alto_muy_alto'] ?>%</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Gráficos por Forma (sin conjunto - no existe baremo para mezclar formas) -->
            <?php if (count($items) > 0): ?>
            <?php $firstItem = reset($items); ?>
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light py-2">
                            <small class="fw-bold">Forma A - Jefes, Profesionales y Técnicos (n=<?= $totales['forma_a'] ?>)</small>
                        </div>
                        <div class="card-body p-2">
                            <div class="chart-container" style="height: 200px;">
                                <canvas id="chart_<?= $seccion ?>_a"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light py-2">
                            <small class="fw-bold">Forma B - Auxiliares y Operarios (n=<?= $totales['forma_b'] ?>)</small>
                        </div>
                        <div class="card-body p-2">
                            <div class="chart-container" style="height: 200px;">
                                <canvas id="chart_<?= $seccion ?>_b"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        <?php endforeach; ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Datos de consolidación desde PHP
        const consolidacion = <?= json_encode($consolidacion) ?>;

        // Colores para las escalas agrupadas
        const colores = {
            bajo_sin_riesgo: '#4caf50',  // Verde
            riesgo_medio: '#ffeb3b',      // Amarillo
            alto_muy_alto: '#f44336'      // Rojo
        };

        // Crear gráficos para cada sección
        Chart.register(ChartDataLabels);

        Object.keys(consolidacion).forEach(seccion => {
            const items = consolidacion[seccion];
            const firstKey = Object.keys(items)[0];
            if (!firstKey) return;

            const data = items[firstKey];

            // Crear gráfico solo para Forma A y B (no existe baremo para conjunto)
            ['a', 'b'].forEach(forma => {
                const canvasId = `chart_${seccion}_${forma}`;
                const canvas = document.getElementById(canvasId);
                if (!canvas) return;

                const formaKey = `forma_${forma}`;
                const porcentajes = data[formaKey]?.porcentaje;
                if (!porcentajes) return;

                new Chart(canvas, {
                    type: 'bar',
                    data: {
                        labels: ['Bajo/Sin riesgo', 'Riesgo medio', 'Alto/Muy alto'],
                        datasets: [{
                            data: [
                                porcentajes.bajo_sin_riesgo,
                                porcentajes.riesgo_medio,
                                porcentajes.alto_muy_alto
                            ],
                            backgroundColor: [
                                colores.bajo_sin_riesgo,
                                colores.riesgo_medio,
                                colores.alto_muy_alto
                            ],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        indexAxis: 'y',
                        plugins: {
                            legend: { display: false },
                            datalabels: {
                                anchor: 'end',
                                align: 'end',
                                formatter: (value) => value + '%',
                                font: { weight: 'bold', size: 11 },
                                color: '#333'
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                max: 100,
                                ticks: { callback: value => value + '%' }
                            },
                            y: {
                                ticks: { font: { size: 10 } }
                            }
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>
