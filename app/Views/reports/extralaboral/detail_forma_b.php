<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?> - PsyRisk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
        @media print {
            .no-print { display: none !important; }
            body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body class="bg-light">

<!-- Botón de impresión -->
<button onclick="window.print()" class="btn btn-primary print-button no-print">
    <i class="fas fa-print me-2"></i>Imprimir
</button>

<div class="container-fluid mt-4">

<?php
// Helper function para obtener colores según nivel de riesgo
function getNivelColor($nivel) {
    $colores = [
        'sin_riesgo' => '#4CAF50',      // Verde
        'riesgo_bajo' => '#8BC34A',     // Verde claro
        'riesgo_medio' => '#FFC107',    // Amarillo
        'riesgo_alto' => '#FF9800',     // Naranja
        'riesgo_muy_alto' => '#F44336'  // Rojo
    ];
    return $colores[$nivel] ?? '#9E9E9E';
}

// Helper function para obtener texto legible del nivel
function getNivelTexto($nivel) {
    $textos = [
        'sin_riesgo' => 'Sin Riesgo',
        'riesgo_bajo' => 'Riesgo Bajo',
        'riesgo_medio' => 'Riesgo Medio',
        'riesgo_alto' => 'Riesgo Alto',
        'riesgo_muy_alto' => 'Riesgo Muy Alto'
    ];
    return $textos[$nivel] ?? 'Desconocido';
}
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="no-print">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('battery-services') ?>">Servicios</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('battery-services/' . $service['id']) ?>"><?= esc($service['service_name']) ?></a></li>
        <li class="breadcrumb-item active">Mapa de Calor Extralaboral - Forma B</li>
    </ol>
</nav>

<!-- Encabezado -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-info text-white">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1">
                    <i class="fas fa-home me-2"></i>Mapa de Calor - Extralaboral Forma B
                </h4>
                <p class="mb-0 small">Factores de Riesgo Psicosocial Fuera del Trabajo - Auxiliares, Operarios</p>
            </div>
            <div class="text-end">
                <h5 class="mb-0"><?= esc($service['service_name']) ?></h5>
                <small><?= esc($service['company_name']) ?></small><br>
                <small>Fecha: <?= date('d/m/Y', strtotime($service['service_date'])) ?></small>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <p class="mb-1"><i class="fas fa-users text-primary me-2"></i><strong>Total Evaluados (Forma B):</strong> <?= $totalWorkers ?></p>
            </div>
            <div class="col-md-4">
                <p class="mb-1"><i class="fas fa-clipboard-list text-info me-2"></i><strong>Dimensiones:</strong> 7 (sin dominios)</p>
            </div>
            <div class="col-md-4">
                <p class="mb-1"><i class="fas fa-chart-bar text-success me-2"></i><strong>Método:</strong> Promedio aritmético</p>
            </div>
        </div>
    </div>
</div>

<!-- Estructura del Cuestionario -->
<div class="card shadow-sm mb-4 border-info">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0"><i class="fas fa-sitemap me-2"></i>Estructura del Cuestionario Extralaboral - Forma B</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-12">
                <h6 class="text-primary"><i class="fas fa-list-ul me-2"></i>7 DIMENSIONES EXTRALABORALES (Sin Dominios)</h6>
                <ul class="list-unstyled ms-3">
                    <li>1. Tiempo fuera del trabajo</li>
                    <li>2. Relaciones familiares</li>
                    <li>3. Comunicación y relaciones interpersonales</li>
                    <li>4. Situación económica del grupo familiar</li>
                    <li>5. Características de la vivienda y de su entorno</li>
                    <li>6. Influencia del entorno extralaboral sobre el trabajo</li>
                    <li>7. Desplazamiento vivienda-trabajo-vivienda</li>
                </ul>
            </div>
        </div>
        <div class="alert alert-warning mt-3 mb-0">
            <small><i class="fas fa-info-circle me-1"></i><strong>Nota:</strong> El cuestionario extralaboral tiene 7 dimensiones y NO tiene dominios (a diferencia del intralaboral). Se aplica el mismo cuestionario de 31 preguntas tanto para Forma A como para Forma B, pero los baremos son diferenciados: Tabla 17 para Forma A (jefes, profesionales, técnicos) y Tabla 18 para Forma B (auxiliares, operarios).</small>
        </div>
    </div>
</div>

<!-- MAPA DE CALOR PRINCIPAL -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white border-bottom">
        <h5 class="mb-0 text-info">
            <i class="fas fa-fire me-2"></i>Mapa de Calor Extralaboral - Forma B
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="d-flex" style="min-height: 500px;">
            <!-- Total Extralaboral (20% izquierda) -->
            <div class="d-flex align-items-center justify-content-center p-3" style="width: 20%; background-color: <?= getNivelColor($calculations['extralaboral_total']['nivel']) ?>; color: white; border-right: 2px solid white;">
                <div class="text-center">
                    <div style="font-size: 0.9rem; font-weight: 600; margin-bottom: 10px;">TOTAL<br>EXTRALABORAL</div>
                    <div style="font-size: 3rem; font-weight: bold; margin: 10px 0;"><?= number_format($calculations['extralaboral_total']['promedio'], 1) ?></div>
                    <div style="font-size: 0.85rem; font-weight: 600; margin-top: 10px;">
                        <?= getNivelTexto($calculations['extralaboral_total']['nivel']) ?>
                    </div>
                    <div style="font-size: 0.75rem; margin-top: 10px; opacity: 0.9;">
                        <i class="fas fa-users me-2"></i><?= $calculations['extralaboral_total']['cantidad'] ?> trabajadores
                    </div>
                </div>
            </div>

            <!-- 7 Dimensiones (80% derecha) -->
            <div class="d-flex flex-column" style="width: 80%;">
                <?php
                $dimensiones = [
                    ['key' => 'extralaboral_tiempo_fuera', 'nombre' => 'Tiempo fuera del trabajo'],
                    ['key' => 'extralaboral_relaciones_familiares', 'nombre' => 'Relaciones familiares'],
                    ['key' => 'extralaboral_comunicacion', 'nombre' => 'Comunicación y relaciones interpersonales'],
                    ['key' => 'extralaboral_situacion_economica', 'nombre' => 'Situación económica del grupo familiar'],
                    ['key' => 'extralaboral_caracteristicas_vivienda', 'nombre' => 'Características de la vivienda y de su entorno'],
                    ['key' => 'extralaboral_influencia_entorno', 'nombre' => 'Influencia del entorno extralaboral sobre el trabajo'],
                    ['key' => 'extralaboral_desplazamiento', 'nombre' => 'Desplazamiento vivienda-trabajo-vivienda']
                ];

                foreach ($dimensiones as $index => $dim):
                    $isLast = ($index === count($dimensiones) - 1);
                    $borderStyle = $isLast ? '' : 'border-bottom: 2px solid white;';
                ?>
                    <div class="d-flex align-items-center justify-content-center p-3" style="flex: 1; background-color: <?= getNivelColor($calculations[$dim['key']]['nivel']) ?>; color: white; <?= $borderStyle ?>">
                        <div class="text-center" style="font-size: 0.85rem;">
                            <div style="font-weight: 600;"><?= $dim['nombre'] ?></div>
                            <div style="font-size: 1.5rem; font-weight: bold; margin-top: 5px;"><?= number_format($calculations[$dim['key']]['promedio'], 1) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Título de sección de cálculos -->
<div class="row mb-3">
    <div class="col-12">
        <h4><i class="fas fa-calculator me-2"></i>Cálculos Detallados</h4>
        <p class="text-muted">Promedios aritméticos y aplicación de baremos según Resolución 2404/2019</p>
    </div>
</div>

<!-- 1. TOTAL EXTRALABORAL -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-start border-primary border-4">
            <div class="card-header" style="background-color: <?= getNivelColor($calculations['extralaboral_total']['nivel']) ?>; color: white;">
                <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>TOTAL EXTRALABORAL</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Cálculo del Promedio:</h6>
                        <p class="mb-2"><strong>Suma de puntajes:</strong> <?= number_format($calculations['extralaboral_total']['suma'], 2) ?></p>
                        <p class="mb-2"><strong>Cantidad de trabajadores:</strong> <?= $calculations['extralaboral_total']['cantidad'] ?></p>
                        <p class="mb-3"><strong>Promedio:</strong> <?= number_format($calculations['extralaboral_total']['suma'], 2) ?> ÷ <?= $calculations['extralaboral_total']['cantidad'] ?> = <span class="badge bg-info" style="font-size: 1.1rem;"><?= number_format($calculations['extralaboral_total']['promedio'], 2) ?></span></p>

                        <h6>Nivel de Riesgo:</h6>
                        <p>Con un puntaje de <strong><?= number_format($calculations['extralaboral_total']['promedio'], 2) ?></strong>, según la Tabla 34, el nivel de riesgo es:</p>
                        <h4><span class="badge" style="background-color: <?= getNivelColor($calculations['extralaboral_total']['nivel']) ?>;"><?= strtoupper(getNivelTexto($calculations['extralaboral_total']['nivel'])) ?></span></h4>
                    </div>
                    <div class="col-md-6">
                        <h6>Baremo Aplicado (Tabla 34 - Total Extralaboral):</h6>
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Nivel de Riesgo</th>
                                    <th>Rango</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($calculations['extralaboral_total']['baremo'] as $nivelKey => $rango): ?>
                                <tr <?= $nivelKey === $calculations['extralaboral_total']['nivel'] ? 'class="table-success fw-bold"' : '' ?>>
                                    <td><?= getNivelTexto($nivelKey) ?></td>
                                    <td><?= $rango[0] ?> - <?= $rango[1] ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <p class="text-muted small mb-0"><i class="fas fa-info-circle me-1"></i>El promedio <?= number_format($calculations['extralaboral_total']['promedio'], 2) ?> se encuentra en el rango <?= $calculations['extralaboral_total']['rango_aplicado'][0] ?> - <?= $calculations['extralaboral_total']['rango_aplicado'][1] ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- DIMENSIONES (7 cards) -->
<div class="row mb-3">
    <div class="col-12"><h5 class="text-success"><i class="fas fa-puzzle-piece me-2"></i>DIMENSIONES (7 para Extralaboral)</h5></div>
</div>

<?php
// Array de dimensiones con sus nombres completos
$dimensionesDetalle = [
    ['key' => 'extralaboral_tiempo_fuera', 'nombre' => 'Tiempo fuera del trabajo'],
    ['key' => 'extralaboral_relaciones_familiares', 'nombre' => 'Relaciones familiares'],
    ['key' => 'extralaboral_comunicacion', 'nombre' => 'Comunicación y relaciones interpersonales'],
    ['key' => 'extralaboral_situacion_economica', 'nombre' => 'Situación económica del grupo familiar'],
    ['key' => 'extralaboral_caracteristicas_vivienda', 'nombre' => 'Características de la vivienda y de su entorno'],
    ['key' => 'extralaboral_influencia_entorno', 'nombre' => 'Influencia del entorno extralaboral sobre el trabajo'],
    ['key' => 'extralaboral_desplazamiento', 'nombre' => 'Desplazamiento vivienda-trabajo-vivienda'],
];
?>

<?php foreach ($dimensionesDetalle as $index => $dim): ?>
<div class="row mb-3">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header" style="background-color: <?= getNivelColor($calculations[$dim['key']]['nivel']) ?>; color: white;">
                <h6 class="mb-0"><?= $index + 1 ?>. <?= esc($dim['nombre']) ?></h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-2"><strong>Promedio:</strong> <?= number_format($calculations[$dim['key']]['suma'], 2) ?> ÷ <?= $calculations[$dim['key']]['cantidad'] ?> = <span class="badge bg-info"><?= number_format($calculations[$dim['key']]['promedio'], 2) ?></span></p>
                        <h6><span class="badge" style="background-color: <?= getNivelColor($calculations[$dim['key']]['nivel']) ?>;"><?= strtoupper(getNivelTexto($calculations[$dim['key']]['nivel'])) ?></span></h6>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light"><tr><th>Nivel</th><th>Rango</th></tr></thead>
                            <tbody>
                                <?php foreach ($calculations[$dim['key']]['baremo'] as $nivelKey => $rango): ?>
                                <tr <?= $nivelKey === $calculations[$dim['key']]['nivel'] ? 'class="table-success fw-bold"' : '' ?>>
                                    <td><?= getNivelTexto($nivelKey) ?></td>
                                    <td><?= $rango[0] ?> - <?= $rango[1] ?></td>
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
<?php endforeach; ?>

<!-- Interpretación de Niveles de Riesgo -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0">
            <i class="fas fa-info-circle me-2"></i>Interpretación de Niveles de Riesgo
        </h5>
    </div>
    <div class="card-body">
        <p class="text-muted mb-3">Los factores extralaborales se refieren a aspectos del entorno familiar, social y económico del trabajador que pueden afectar su salud y desempeño laboral. La interpretación de los niveles de riesgo permite orientar acciones de intervención apropiadas.</p>

        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th style="width: 20%;">Nivel de Riesgo</th>
                        <th>Interpretación y Acciones Requeridas</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <span class="badge" style="background-color: <?= getNivelColor('sin_riesgo') ?>; font-size: 0.95rem;">Sin Riesgo</span>
                        </td>
                        <td>
                            <strong>Ausencia de riesgo o riesgo tan bajo que no amerita desarrollar actividades de intervención.</strong>
                            Las dimensiones que se encuentren bajo esta categoría serán objeto de acciones o programas de <strong>promoción</strong>.
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span class="badge" style="background-color: <?= getNivelColor('riesgo_bajo') ?>; font-size: 0.95rem;">Riesgo Bajo</span>
                        </td>
                        <td>
                            <strong>No se espera que los factores psicosociales que obtengan puntuaciones de este nivel estén relacionados con síntomas o respuestas de estrés significativas.</strong>
                            Las dimensiones que se encuentren bajo esta categoría serán objeto de acciones o programas de intervención, a fin de <strong>mantenerlos en los niveles de riesgo más bajos posibles</strong>.
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span class="badge" style="background-color: <?= getNivelColor('riesgo_medio') ?>; font-size: 0.95rem;">Riesgo Medio</span>
                        </td>
                        <td>
                            <strong>Nivel de riesgo en el que se esperaría una respuesta de estrés moderada.</strong>
                            Las dimensiones que se encuentren bajo esta categoría ameritan <strong>observación y acciones sistemáticas de intervención para prevenir efectos perjudiciales en la salud</strong>.
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span class="badge" style="background-color: <?= getNivelColor('riesgo_alto') ?>; font-size: 0.95rem;">Riesgo Alto</span>
                        </td>
                        <td>
                            <strong>Nivel de riesgo que tiene una importante posibilidad de asociación con respuestas de estrés alto.</strong>
                            Por tanto, las dimensiones que se encuentren bajo esta categoría <strong>requieren intervención en el marco de un sistema de vigilancia epidemiológica</strong>.
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span class="badge" style="background-color: <?= getNivelColor('riesgo_muy_alto') ?>; font-size: 0.95rem;">Riesgo Muy Alto</span>
                        </td>
                        <td>
                            <strong>Nivel de riesgo con amplia posibilidad de asociarse a respuestas muy altas de estrés.</strong>
                            Por consiguiente, las dimensiones que se encuentren bajo esta categoría <strong>requieren intervención inmediata en el marco de un sistema de vigilancia epidemiológica</strong>.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Nota metodológica -->
<div class="row mt-4 mb-4">
    <div class="col-12">
        <div class="alert alert-info">
            <h6><i class="fas fa-info-circle me-2"></i>Nota Metodológica</h6>
            <p class="mb-0">Los cálculos se realizan utilizando el <strong>promedio aritmético</strong> de los puntajes transformados de todos los trabajadores evaluados con Forma B. Los baremos aplicados corresponden a las tablas oficiales de la Resolución 2404 de 2019: Tabla 34 Forma B para Total Extralaboral (auxiliares, operarios), y Tabla 18 para las 7 Dimensiones.</p>
        </div>
    </div>
</div>

<!-- Botones de navegación -->
<div class="d-flex justify-content-between mb-4 no-print">
    <a href="<?= base_url('battery-services/' . $service['id']) ?>" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Volver al Servicio
    </a>
    <div>
        <a href="<?= base_url('reports/extralaboral-a/' . $service['id']) ?>" class="btn btn-outline-primary me-2">
            <i class="fas fa-exchange-alt me-2"></i>Ver Forma A
        </a>
        <a href="<?= base_url('workers/service/' . $service['id']) ?>" class="btn btn-primary">
            <i class="fas fa-users me-2"></i>Ver Trabajadores
        </a>
    </div>
</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
