<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?> - PsyRisk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">

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

<!-- Header -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-1"><i class="fas fa-home me-2 text-primary"></i><?= esc($title) ?></h3>
                        <p class="text-muted mb-0">
                            <i class="fas fa-building me-2"></i><?= esc($service['service_name']) ?>
                            <span class="ms-3"><i class="fas fa-users me-2"></i><?= $totalWorkers ?> trabajadores evaluados</span>
                        </p>
                    </div>
                    <?php
                    $userRole = session()->get('role_name');
                    $backUrl = in_array($userRole, ['cliente_empresa', 'cliente_gestor'])
                        ? base_url('client/battery-services/' . $service['id'])
                        : base_url('battery-services/' . $service['id']);
                    ?>
                    <a href="<?= $backUrl ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Volver
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mapa de Calor Flexbox: Total + 7 Dimensiones (Sin Dominios) -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-info bg-opacity-10">
                <h5 class="mb-0"><i class="fas fa-fire me-2"></i>Mapa de Calor Extralaboral</h5>
                <small class="text-muted">Jerarquía: Total → 7 Dimensiones (sin dominios)</small>
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
    </div>
</div>

<!-- Cards de Cálculos Detallados -->
<div class="row mb-4">
    <div class="col-12">
        <h5 class="mb-3"><i class="fas fa-calculator me-2"></i>Cálculos Detallados por Dimensión</h5>
    </div>
</div>

<?php
// Definición de dimensiones con sus baremos
$dimensionesDetalle = [
    [
        'key' => 'extralaboral_total',
        'nombre' => 'Total Extralaboral',
        'descripcion' => 'Puntaje general que refleja el impacto global de todos los factores extralaborales evaluados.',
        'baremo' => [
            ['nivel' => 'Sin riesgo o riesgo despreciable', 'rango' => '0,0 - 11,7', 'color' => 'sin_riesgo'],
            ['nivel' => 'Riesgo bajo', 'rango' => '11,8 - 15,0', 'color' => 'riesgo_bajo'],
            ['nivel' => 'Riesgo medio', 'rango' => '15,1 - 19,9', 'color' => 'riesgo_medio'],
            ['nivel' => 'Riesgo alto', 'rango' => '20,0 - 24,1', 'color' => 'riesgo_alto'],
            ['nivel' => 'Riesgo muy alto', 'rango' => '24,2 - 100,0', 'color' => 'riesgo_muy_alto']
        ]
    ],
    [
        'key' => 'extralaboral_tiempo_fuera',
        'nombre' => 'Tiempo fuera del trabajo',
        'descripcion' => 'Evalúa el tiempo destinado al descanso y actividades de recreación y desarrollo personal.',
        'baremo' => [
            ['nivel' => 'Sin riesgo o riesgo despreciable', 'rango' => '0,0 - 8,3', 'color' => 'sin_riesgo'],
            ['nivel' => 'Riesgo bajo', 'rango' => '8,4 - 25,0', 'color' => 'riesgo_bajo'],
            ['nivel' => 'Riesgo medio', 'rango' => '25,1 - 37,5', 'color' => 'riesgo_medio'],
            ['nivel' => 'Riesgo alto', 'rango' => '37,6 - 45,8', 'color' => 'riesgo_alto'],
            ['nivel' => 'Riesgo muy alto', 'rango' => '45,9 - 100,0', 'color' => 'riesgo_muy_alto']
        ]
    ],
    [
        'key' => 'extralaboral_relaciones_familiares',
        'nombre' => 'Relaciones familiares',
        'descripcion' => 'Propiedades del grupo familiar del trabajador que influyen en su bienestar y estabilidad.',
        'baremo' => [
            ['nivel' => 'Sin riesgo o riesgo despreciable', 'rango' => '0,0 - 6,3', 'color' => 'sin_riesgo'],
            ['nivel' => 'Riesgo bajo', 'rango' => '6,4 - 12,5', 'color' => 'riesgo_bajo'],
            ['nivel' => 'Riesgo medio', 'rango' => '12,6 - 25,0', 'color' => 'riesgo_medio'],
            ['nivel' => 'Riesgo alto', 'rango' => '25,1 - 37,5', 'color' => 'riesgo_alto'],
            ['nivel' => 'Riesgo muy alto', 'rango' => '37,6 - 100,0', 'color' => 'riesgo_muy_alto']
        ]
    ],
    [
        'key' => 'extralaboral_comunicacion',
        'nombre' => 'Comunicación y relaciones interpersonales',
        'descripcion' => 'Cualidades de la interacción del trabajador con su núcleo familiar y social cercano.',
        'baremo' => [
            ['nivel' => 'Sin riesgo o riesgo despreciable', 'rango' => '0,0', 'color' => 'sin_riesgo'],
            ['nivel' => 'Riesgo bajo', 'rango' => '0,1 - 5,6', 'color' => 'riesgo_bajo'],
            ['nivel' => 'Riesgo medio', 'rango' => '5,7 - 11,1', 'color' => 'riesgo_medio'],
            ['nivel' => 'Riesgo alto', 'rango' => '11,2 - 22,2', 'color' => 'riesgo_alto'],
            ['nivel' => 'Riesgo muy alto', 'rango' => '22,3 - 100,0', 'color' => 'riesgo_muy_alto']
        ]
    ],
    [
        'key' => 'extralaboral_situacion_economica',
        'nombre' => 'Situación económica del grupo familiar',
        'descripcion' => 'Disponibilidad de medios económicos para sostener un nivel de vida adecuado.',
        'baremo' => [
            ['nivel' => 'Sin riesgo o riesgo despreciable', 'rango' => '0,0 - 8,3', 'color' => 'sin_riesgo'],
            ['nivel' => 'Riesgo bajo', 'rango' => '8,4 - 25,0', 'color' => 'riesgo_bajo'],
            ['nivel' => 'Riesgo medio', 'rango' => '25,1 - 33,3', 'color' => 'riesgo_medio'],
            ['nivel' => 'Riesgo alto', 'rango' => '33,4 - 50,0', 'color' => 'riesgo_alto'],
            ['nivel' => 'Riesgo muy alto', 'rango' => '50,1 - 100,0', 'color' => 'riesgo_muy_alto']
        ]
    ],
    [
        'key' => 'extralaboral_caracteristicas_vivienda',
        'nombre' => 'Características de la vivienda y de su entorno',
        'descripcion' => 'Condiciones de infraestructura, ubicación y entorno de la vivienda del trabajador.',
        'baremo' => [
            ['nivel' => 'Sin riesgo o riesgo despreciable', 'rango' => '0,0', 'color' => 'sin_riesgo'],
            ['nivel' => 'Riesgo bajo', 'rango' => '0,1 - 5,6', 'color' => 'riesgo_bajo'],
            ['nivel' => 'Riesgo medio', 'rango' => '5,7 - 11,1', 'color' => 'riesgo_medio'],
            ['nivel' => 'Riesgo alto', 'rango' => '11,2 - 16,7', 'color' => 'riesgo_alto'],
            ['nivel' => 'Riesgo muy alto', 'rango' => '16,8 - 100,0', 'color' => 'riesgo_muy_alto']
        ]
    ],
    [
        'key' => 'extralaboral_influencia_entorno',
        'nombre' => 'Influencia del entorno extralaboral sobre el trabajo',
        'descripcion' => 'Grado en que situaciones de la vida familiar o personal afectan el desempeño laboral.',
        'baremo' => [
            ['nivel' => 'Sin riesgo o riesgo despreciable', 'rango' => '0,0 - 15,6', 'color' => 'sin_riesgo'],
            ['nivel' => 'Riesgo bajo', 'rango' => '15,7 - 28,1', 'color' => 'riesgo_bajo'],
            ['nivel' => 'Riesgo medio', 'rango' => '28,2 - 37,5', 'color' => 'riesgo_medio'],
            ['nivel' => 'Riesgo alto', 'rango' => '37,6 - 50,0', 'color' => 'riesgo_alto'],
            ['nivel' => 'Riesgo muy alto', 'rango' => '50,1 - 100,0', 'color' => 'riesgo_muy_alto']
        ]
    ],
    [
        'key' => 'extralaboral_desplazamiento',
        'nombre' => 'Desplazamiento vivienda-trabajo-vivienda',
        'descripcion' => 'Condiciones del traslado del trabajador desde su sitio de vivienda hasta su lugar de trabajo.',
        'baremo' => [
            ['nivel' => 'Sin riesgo o riesgo despreciable', 'rango' => '0,0', 'color' => 'sin_riesgo'],
            ['nivel' => 'Riesgo bajo', 'rango' => '0,1 - 6,3', 'color' => 'riesgo_bajo'],
            ['nivel' => 'Riesgo medio', 'rango' => '6,4 - 12,5', 'color' => 'riesgo_medio'],
            ['nivel' => 'Riesgo alto', 'rango' => '12,6 - 25,0', 'color' => 'riesgo_alto'],
            ['nivel' => 'Riesgo muy alto', 'rango' => '25,1 - 100,0', 'color' => 'riesgo_muy_alto']
        ]
    ]
];
?>

<?php foreach ($dimensionesDetalle as $index => $dim): ?>
<div class="row mb-3">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header" style="background-color: <?= getNivelColor($calculations[$dim['key']]['nivel']) ?>; color: white;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i><?= $dim['nombre'] ?></h6>
                        <small><?= $dim['descripcion'] ?></small>
                    </div>
                    <div class="text-end">
                        <div style="font-size: 1.8rem; font-weight: bold;"><?= number_format($calculations[$dim['key']]['promedio'], 1) ?></div>
                        <div style="font-size: 0.85rem;"><?= getNivelTexto($calculations[$dim['key']]['nivel']) ?></div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nivel de Riesgo</th>
                                <th>Rango de Puntaje</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dim['baremo'] as $rango): ?>
                                <tr style="background-color: <?= $calculations[$dim['key']]['nivel'] === $rango['color'] ? getNivelColor($rango['color']) . '30' : 'transparent' ?>;">
                                    <td>
                                        <span class="badge" style="background-color: <?= getNivelColor($rango['color']) ?>;">
                                            <?= $rango['nivel'] ?>
                                        </span>
                                    </td>
                                    <td><?= $rango['rango'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<!-- Nota metodológica -->
<div class="row mt-4 mb-4">
    <div class="col-12">
        <div class="alert alert-info">
            <h6><i class="fas fa-info-circle me-2"></i>Nota Metodológica</h6>
            <p class="mb-0">Los cálculos se realizan utilizando el <strong>promedio aritmético</strong> de los puntajes transformados de todos los trabajadores evaluados. Los baremos aplicados corresponden a las tablas oficiales de la Resolución 2404 de 2019: Tabla 34 para Total Extralaboral y Tabla 32 para las 7 Dimensiones. El cuestionario extralaboral es igual para todos los trabajadores, independientemente de su cargo.</p>
        </div>
    </div>
</div>

<!-- Interpretación de Niveles de Riesgo -->
<div class="row mb-4">
    <div class="col-12">
        <h5 class="mb-3"><i class="fas fa-book me-2"></i>Interpretación de Niveles de Riesgo Extralaboral</h5>
        <div class="card shadow-sm">
            <div class="card-body">
                <p class="text-muted mb-3">Definiciones oficiales según Resolución 2404/2019 para la interpretación de los niveles de riesgo psicosocial extralaboral:</p>

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
                                    <span class="badge" style="background-color: <?= getNivelColor('sin_riesgo') ?>; font-size: 0.95rem;">Sin Riesgo o Riesgo Despreciable</span>
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

                <div class="alert alert-warning mt-3 mb-0">
                    <h6 class="mb-2"><i class="fas fa-exclamation-triangle me-2"></i>Importante</h6>
                    <p class="mb-0 small">Estas definiciones aplican para todos los factores de riesgo psicosocial extralaboral (dimensiones). Los niveles de riesgo <strong>Alto</strong> y <strong>Muy Alto</strong> requieren implementación de un sistema de vigilancia epidemiológica según la normatividad colombiana vigente.</p>
                </div>
            </div>
        </div>
    </div>
</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
