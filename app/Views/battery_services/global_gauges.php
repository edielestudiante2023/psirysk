<?php
// Importar librerías de scoring (Single Source of Truth para baremos)
use App\Libraries\IntralaboralAScoring;
use App\Libraries\IntralaboralBScoring;
use App\Libraries\ExtralaboralScoring;
use App\Libraries\EstresScoring;

// =========================================================================
// DIAGNÓSTICO ÁCIDO - Log exhaustivo para identificar causa raíz
// =========================================================================
$logFile = WRITEPATH . 'logs/global_gauges_debug_' . date('Y-m-d_H-i-s') . '.log';
$logData = [];
$logData['timestamp'] = date('Y-m-d H:i:s');
$logData['php_version'] = PHP_VERSION;

// 1. Verificar si las librerías existen y son accesibles
$logData['librerias_accesibles'] = [
    'IntralaboralAScoring' => class_exists('App\Libraries\IntralaboralAScoring'),
    'IntralaboralBScoring' => class_exists('App\Libraries\IntralaboralBScoring'),
    'ExtralaboralScoring' => class_exists('App\Libraries\ExtralaboralScoring'),
    'EstresScoring' => class_exists('App\Libraries\EstresScoring'),
];

// 2. Probar obtención de baremos totales
$logData['baremos_totales'] = [
    'IntralaboralAScoring::getBaremoTotal()' => IntralaboralAScoring::getBaremoTotal(),
    'IntralaboralBScoring::getBaremoTotal()' => IntralaboralBScoring::getBaremoTotal(),
    'ExtralaboralScoring::getBaremoTotal(A)' => ExtralaboralScoring::getBaremoTotal('A'),
    'ExtralaboralScoring::getBaremoTotal(B)' => ExtralaboralScoring::getBaremoTotal('B'),
    'EstresScoring::getBaremoA()' => EstresScoring::getBaremoA(),
    'EstresScoring::getBaremoB()' => EstresScoring::getBaremoB(),
    'EstresScoring::getBaremoGeneral(A)' => EstresScoring::getBaremoGeneral('A'),
    'EstresScoring::getBaremoGeneral(B)' => EstresScoring::getBaremoGeneral('B'),
];

// 3. Probar dominios
$logData['baremos_dominios_A'] = [
    'liderazgo' => IntralaboralAScoring::getBaremoDominio('liderazgo_relaciones_sociales'),
    'control' => IntralaboralAScoring::getBaremoDominio('control'),
    'demandas' => IntralaboralAScoring::getBaremoDominio('demandas'),
    'recompensas' => IntralaboralAScoring::getBaremoDominio('recompensas'),
];

$logData['baremos_dominios_B'] = [
    'liderazgo' => IntralaboralBScoring::getBaremoDominio('liderazgo_relaciones_sociales'),
    'control' => IntralaboralBScoring::getBaremoDominio('control'),
    'demandas' => IntralaboralBScoring::getBaremoDominio('demandas'),
    'recompensas' => IntralaboralBScoring::getBaremoDominio('recompensas'),
];

// 4. Probar TODAS las dimensiones A
$dimensionesA_test = [
    'caracteristicas_liderazgo', 'relaciones_sociales_trabajo', 'retroalimentacion_desempeno',
    'relacion_con_colaboradores', 'claridad_rol', 'capacitacion', 'participacion_manejo_cambio',
    'oportunidades_desarrollo', 'control_autonomia_trabajo', 'demandas_ambientales_esfuerzo_fisico',
    'demandas_emocionales', 'demandas_cuantitativas', 'influencia_trabajo_entorno_extralaboral',
    'exigencias_responsabilidad_cargo', 'demandas_carga_mental', 'consistencia_rol',
    'demandas_jornada_trabajo', 'recompensas_pertenencia_estabilidad', 'reconocimiento_compensacion'
];
$logData['baremos_dimensiones_A'] = [];
foreach ($dimensionesA_test as $dim) {
    $result = IntralaboralAScoring::getBaremoDimension($dim);
    $logData['baremos_dimensiones_A'][$dim] = $result !== null ? 'OK' : 'NULL';
}

// 5. Probar TODAS las dimensiones B
$dimensionesB_test = [
    'caracteristicas_liderazgo', 'relaciones_sociales_trabajo', 'retroalimentacion_desempeno',
    'claridad_rol', 'capacitacion', 'participacion_manejo_cambio', 'oportunidades_desarrollo',
    'control_autonomia_trabajo', 'demandas_ambientales_esfuerzo_fisico', 'demandas_emocionales',
    'demandas_cuantitativas', 'influencia_trabajo_entorno_extralaboral', 'demandas_carga_mental',
    'demandas_jornada_trabajo', 'recompensas_pertenencia_estabilidad', 'reconocimiento_compensacion'
];
$logData['baremos_dimensiones_B'] = [];
foreach ($dimensionesB_test as $dim) {
    $result = IntralaboralBScoring::getBaremoDimension($dim);
    $logData['baremos_dimensiones_B'][$dim] = $result !== null ? 'OK' : 'NULL';
}

// 6. Probar dimensiones extralaborales
$dimensionesExtra_test = ['tiempo_fuera_trabajo', 'relaciones_familiares', 'comunicacion_relaciones',
    'situacion_economica', 'caracteristicas_vivienda', 'influencia_entorno', 'desplazamiento'];
$logData['baremos_extralaboral_A'] = [];
$logData['baremos_extralaboral_B'] = [];
foreach ($dimensionesExtra_test as $dim) {
    $logData['baremos_extralaboral_A'][$dim] = ExtralaboralScoring::getBaremoDimension($dim, 'A') !== null ? 'OK' : 'NULL';
    $logData['baremos_extralaboral_B'][$dim] = ExtralaboralScoring::getBaremoDimension($dim, 'B') !== null ? 'OK' : 'NULL';
}

// 7. Verificar datos que llegan del controlador
$logData['datos_controlador'] = [
    'service_existe' => isset($service),
    'formaACount' => $formaACount ?? 'NO_DEFINIDO',
    'formaBCount' => $formaBCount ?? 'NO_DEFINIDO',
    'globalDataFormaA_keys' => isset($globalDataFormaA) ? array_keys($globalDataFormaA) : 'NO_DEFINIDO',
    'globalDataFormaB_keys' => isset($globalDataFormaB) ? array_keys($globalDataFormaB) : 'NO_DEFINIDO',
];

// 8. Verificar valores específicos de globalDataFormaA
if (isset($globalDataFormaA)) {
    $logData['globalDataFormaA_valores'] = [];
    foreach ($globalDataFormaA as $key => $val) {
        $logData['globalDataFormaA_valores'][$key] = is_numeric($val) ? $val : gettype($val);
    }
}

// Escribir log
file_put_contents($logFile, print_r($logData, true));

// También mostrar resumen en consola del navegador
echo "<!-- DIAGNÓSTICO GLOBAL_GAUGES: Log escrito en $logFile -->\n";
echo "<script>console.log('DIAGNÓSTICO GLOBAL_GAUGES', " . json_encode($logData) . ");</script>\n";

// =========================================================================
// FIN DIAGNÓSTICO
// =========================================================================

// Función helper para generar tarjetas de baremos
function renderBaremos($baremos, $type = 'riesgo') {
    if ($type === 'estres') {
        // EstresScoring usa nomenclatura diferente: muy_bajo, bajo, medio, alto, muy_alto
        $niveles = [
            'muy_bajo' => ['label' => 'Muy Bajo', 'class' => 'sin_riesgo'],
            'bajo' => ['label' => 'Bajo', 'class' => 'riesgo_bajo'],
            'medio' => ['label' => 'Medio', 'class' => 'riesgo_medio'],
            'alto' => ['label' => 'Alto', 'class' => 'riesgo_alto'],
            'muy_alto' => ['label' => 'Muy Alto', 'class' => 'riesgo_muy_alto']
        ];
    } else {
        $niveles = [
            'sin_riesgo' => ['label' => 'Sin Riesgo', 'class' => 'sin_riesgo'],
            'riesgo_bajo' => ['label' => 'Riesgo Bajo', 'class' => 'riesgo_bajo'],
            'riesgo_medio' => ['label' => 'Riesgo Medio', 'class' => 'riesgo_medio'],
            'riesgo_alto' => ['label' => 'Riesgo Alto', 'class' => 'riesgo_alto'],
            'riesgo_muy_alto' => ['label' => 'Riesgo Muy Alto', 'class' => 'riesgo_muy_alto']
        ];
    }

    echo '<div class="baremos-grid">';
    foreach ($niveles as $key => $config) {
        if (isset($baremos[$key])) {
            echo '<div class="baremo-card ' . $config['class'] . '">';
            echo '<div class="baremo-header">' . $config['label'] . '</div>';
            echo '<div class="baremo-range">' . $baremos[$key][0] . ' - ' . $baremos[$key][1] . '</div>';
            echo '</div>';
        }
    }
    echo '</div>';
}

// =========================================================================
// BAREMOS - Desde Single Source of Truth (Librerías de Scoring)
// =========================================================================

// Baremos Puntaje Total General (Tabla 34)
$baremosPuntajeTotalGeneralA = EstresScoring::getBaremoGeneral('A');
$baremosPuntajeTotalGeneralB = EstresScoring::getBaremoGeneral('B');

// Baremos Intralaboral Total (Tabla 33)
$baremosIntralaboralA = IntralaboralAScoring::getBaremoTotal();
$baremosIntralaboralB = IntralaboralBScoring::getBaremoTotal();

// Baremos Extralaboral Total (Tablas 17 y 18)
$baremosExtralaboralA = ExtralaboralScoring::getBaremoTotal('A');
$baremosExtralaboralB = ExtralaboralScoring::getBaremoTotal('B');

// Baremos de Estrés (Tabla 6)
$baremosEstresA = EstresScoring::getBaremoA();
$baremosEstresB = EstresScoring::getBaremoB();

// Baremos Dominios Intralaborales - Forma A
$baremosDomLiderazgoA = IntralaboralAScoring::getBaremoDominio('liderazgo_relaciones_sociales');
$baremosDomControlA = IntralaboralAScoring::getBaremoDominio('control');
$baremosDomDemandasA = IntralaboralAScoring::getBaremoDominio('demandas');
$baremosDomRecompensasA = IntralaboralAScoring::getBaremoDominio('recompensas');

// Baremos Dominios Intralaborales - Forma B
$baremosDomLiderazgoB = IntralaboralBScoring::getBaremoDominio('liderazgo_relaciones_sociales');
$baremosDomControlB = IntralaboralBScoring::getBaremoDominio('control');
$baremosDomDemandasB = IntralaboralBScoring::getBaremoDominio('demandas');
$baremosDomRecompensasB = IntralaboralBScoring::getBaremoDominio('recompensas');

// =========================================================================
// DIMENSIONES INTRALABORALES - Desde Single Source of Truth
// =========================================================================

// Dimensiones Forma A (Tabla 29)
$baremosDimCaracteristicasLiderazgoA = IntralaboralAScoring::getBaremoDimension('caracteristicas_liderazgo');
$baremosDimRelacionesSocialesA = IntralaboralAScoring::getBaremoDimension('relaciones_sociales_trabajo');
$baremosDimRetroalimentacionA = IntralaboralAScoring::getBaremoDimension('retroalimentacion_desempeno');
$baremosDimRelacionColaboradoresA = IntralaboralAScoring::getBaremoDimension('relacion_con_colaboradores');
$baremosDimClaridadRolA = IntralaboralAScoring::getBaremoDimension('claridad_rol');
$baremosDimCapacitacionA = IntralaboralAScoring::getBaremoDimension('capacitacion');

$baremosDimParticipacionCambioA = IntralaboralAScoring::getBaremoDimension('participacion_manejo_cambio');
$baremosDimOportunidadesA = IntralaboralAScoring::getBaremoDimension('oportunidades_desarrollo');
$baremosDimControlAutonomiaA = IntralaboralAScoring::getBaremoDimension('control_autonomia_trabajo');
$baremosDimDemandasAmbientalesA = IntralaboralAScoring::getBaremoDimension('demandas_ambientales_esfuerzo_fisico');
$baremosDimDemandasEmocionalesA = IntralaboralAScoring::getBaremoDimension('demandas_emocionales');
$baremosDimDemandasCuantitativasA = IntralaboralAScoring::getBaremoDimension('demandas_cuantitativas');
$baremosDimInfluenciaTrabajoA = IntralaboralAScoring::getBaremoDimension('influencia_trabajo_entorno_extralaboral');
$baremosDimExigenciasResponsabilidadA = IntralaboralAScoring::getBaremoDimension('exigencias_responsabilidad_cargo');
$baremosDimDemandasCargaMentalA = IntralaboralAScoring::getBaremoDimension('demandas_carga_mental');
$baremosDimConsistenciaRolA = IntralaboralAScoring::getBaremoDimension('consistencia_rol');
$baremosDimDemandasJornadaA = IntralaboralAScoring::getBaremoDimension('demandas_jornada_trabajo');
$baremosDimRecompensasPertenenciaA = IntralaboralAScoring::getBaremoDimension('recompensas_pertenencia_estabilidad');
$baremosDimReconocimientoCompensacionA = IntralaboralAScoring::getBaremoDimension('reconocimiento_compensacion');

// Dimensiones Forma B (Tabla 30)
$baremosDimCaracteristicasLiderazgoB = IntralaboralBScoring::getBaremoDimension('caracteristicas_liderazgo');
$baremosDimRelacionesSocialesB = IntralaboralBScoring::getBaremoDimension('relaciones_sociales_trabajo');
$baremosDimRetroalimentacionB = IntralaboralBScoring::getBaremoDimension('retroalimentacion_desempeno');
$baremosDimClaridadRolB = IntralaboralBScoring::getBaremoDimension('claridad_rol');
$baremosDimCapacitacionB = IntralaboralBScoring::getBaremoDimension('capacitacion');
$baremosDimParticipacionCambioB = IntralaboralBScoring::getBaremoDimension('participacion_manejo_cambio');
$baremosDimOportunidadesB = IntralaboralBScoring::getBaremoDimension('oportunidades_desarrollo');
$baremosDimControlAutonomiaB = IntralaboralBScoring::getBaremoDimension('control_autonomia_trabajo');
$baremosDimDemandasAmbientalesB = IntralaboralBScoring::getBaremoDimension('demandas_ambientales_esfuerzo_fisico');
$baremosDimDemandasEmocionalesB = IntralaboralBScoring::getBaremoDimension('demandas_emocionales');
$baremosDimDemandasCuantitativasB = IntralaboralBScoring::getBaremoDimension('demandas_cuantitativas');
$baremosDimInfluenciaTrabajoB = IntralaboralBScoring::getBaremoDimension('influencia_trabajo_entorno_extralaboral');
$baremosDimDemandasCargaMentalB = IntralaboralBScoring::getBaremoDimension('demandas_carga_mental');
$baremosDimDemandasJornadaB = IntralaboralBScoring::getBaremoDimension('demandas_jornada_trabajo');
$baremosDimRecompensasPertenenciaB = IntralaboralBScoring::getBaremoDimension('recompensas_pertenencia_estabilidad');
$baremosDimReconocimientoCompensacionB = IntralaboralBScoring::getBaremoDimension('reconocimiento_compensacion');

// =========================================================================
// DIMENSIONES EXTRALABORALES - Desde Single Source of Truth
// =========================================================================

// Dimensiones Extralaborales Forma A (Tabla 17)
$baremosDimTiempoFueraTrabajoA = ExtralaboralScoring::getBaremoDimension('tiempo_fuera_trabajo', 'A');
$baremosDimRelacionesFamiliaresA = ExtralaboralScoring::getBaremoDimension('relaciones_familiares', 'A');
$baremosDimComunicacionRelacionesA = ExtralaboralScoring::getBaremoDimension('comunicacion_relaciones', 'A');
$baremosDimSituacionEconomicaA = ExtralaboralScoring::getBaremoDimension('situacion_economica', 'A');
$baremosDimCaracteristicasViviendaA = ExtralaboralScoring::getBaremoDimension('caracteristicas_vivienda', 'A');
$baremosDimInfluenciaEntornoA = ExtralaboralScoring::getBaremoDimension('influencia_entorno', 'A');
$baremosDimDesplazamientoViviendaA = ExtralaboralScoring::getBaremoDimension('desplazamiento', 'A');

// Dimensiones Extralaborales Forma B (Tabla 18)
$baremosDimTiempoFueraTrabajoB = ExtralaboralScoring::getBaremoDimension('tiempo_fuera_trabajo', 'B');
$baremosDimRelacionesFamiliaresB = ExtralaboralScoring::getBaremoDimension('relaciones_familiares', 'B');
$baremosDimComunicacionRelacionesB = ExtralaboralScoring::getBaremoDimension('comunicacion_relaciones', 'B');
$baremosDimSituacionEconomicaB = ExtralaboralScoring::getBaremoDimension('situacion_economica', 'B');
$baremosDimCaracteristicasViviendaB = ExtralaboralScoring::getBaremoDimension('caracteristicas_vivienda', 'B');
$baremosDimInfluenciaEntornoB = ExtralaboralScoring::getBaremoDimension('influencia_entorno', 'B');
$baremosDimDesplazamientoViviendaB = ExtralaboralScoring::getBaremoDimension('desplazamiento', 'B');

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gráficos Globales - <?= esc($service['service_name']) ?> - PsyRisk</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Chart.js + plugin de datalabels -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>

    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }

        .container-main {
            max-width: 1600px;
            margin: 0 auto;
        }

        .header-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .gauge-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 1.5rem;
            color: #667eea;
            border-bottom: 3px solid #667eea;
            padding-bottom: 0.5rem;
        }

        .forma-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: bold;
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }

        .forma-badge.forma-a {
            background: #007bff;
            color: white;
        }

        .forma-badge.forma-b {
            background: #28a745;
            color: white;
        }

        /* Gauge container */
        .gauge-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .gauge-title {
            text-align: center;
            font-weight: bold;
            font-size: 1rem;
            margin-bottom: 1rem;
            color: #333;
            position: relative;
        }

        .gauge-number {
            position: absolute;
            top: -8px;
            left: -8px;
            background: #007bff;
            color: white;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: bold;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            z-index: 5;
        }

        .gauge-wrapper {
            position: relative;
            height: 200px;
            margin-bottom: 1rem;
        }

        /* Aguja HTML */
        .needle {
            position: absolute;
            left: 50%;
            top: 50%;
            width: 2px;
            height: 45%;
            background: #000;
            transform-origin: bottom center;
            transform: rotate(0deg);
            z-index: 10;
        }

        .needle::after {
            content: "";
            position: absolute;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #000;
            bottom: -6px;
            left: 50%;
            transform: translateX(-50%);
        }

        .gauge-score {
            text-align: center;
            margin-top: -50px;
            margin-bottom: 0.5rem;
        }

        .gauge-score-value {
            font-size: 1.5rem;
            font-weight: bold;
            background: white;
            padding: 0.4rem 1.2rem;
            border-radius: 8px;
            display: inline-block;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .gauge-label {
            background: #333;
            color: white;
            padding: 0.25rem 0.8rem;
            border-radius: 5px;
            font-weight: bold;
            font-size: 0.75rem;
            margin-top: 0.5rem;
            display: inline-block;
        }

        /* Tarjetas de baremos */
        .baremos-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .baremo-card {
            border: 2px solid;
            border-radius: 8px;
            padding: 0.6rem;
            text-align: center;
        }

        .baremo-card.sin-riesgo {
            border-color: #28a745;
            background-color: #d4edda;
        }

        .baremo-card.riesgo-bajo {
            border-color: #90ee90;
            background-color: #e8f8e8;
        }

        .baremo-card.riesgo-medio {
            border-color: #ffc107;
            background-color: #fff9e6;
        }

        .baremo-card.riesgo-alto {
            border-color: #dc3545;
            background-color: #f8d7da;
        }

        .baremo-card.riesgo-muy-alto {
            border-color: #8b0000;
            background-color: #ffe0e0;
        }

        .baremo-header {
            font-size: 0.75rem;
            font-weight: bold;
            margin-bottom: 0.3rem;
            min-height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .baremo-range {
            font-size: 0.9rem;
            font-weight: bold;
        }

        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }

        @media print {
            .print-btn, .no-print { display: none !important; }
            body { background: white; }
        }
    </style>
</head>
<body>
    <div class="container-main">
        <!-- Botón de imprimir -->
        <button onclick="window.print()" class="btn btn-primary print-btn">
            <i class="fas fa-print me-2"></i>Imprimir
        </button>

        <!-- Header -->
        <div class="header-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-2">
                        <i class="fas fa-chart-pie text-primary me-2"></i>
                        Gráficos Globales de Riesgo Psicosocial
                    </h1>
                    <h4 class="text-muted mb-0"><?= esc($service['service_name']) ?></h4>
                    <p class="text-muted mb-0">
                        <i class="fas fa-building me-2"></i><?= esc($service['company_name']) ?>
                    </p>
                </div>
                <div class="text-end">
                    <div class="mb-2">
                        <span class="badge bg-primary fs-6">Forma A: <?= $formaACount ?> trabajadores</span>
                    </div>
                    <div>
                        <span class="badge bg-success fs-6">Forma B: <?= $formaBCount ?> trabajadores</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECCIÓN 0: PUNTAJE TOTAL GENERAL DE FACTORES DE RIESGO PSICOSOCIAL (Tabla 34) -->
        <div class="gauge-section">
            <div class="section-title">
                <i class="fas fa-chart-pie me-2"></i>PUNTAJE TOTAL GENERAL DE FACTORES DE RIESGO PSICOSOCIAL
            </div>
            <p class="text-muted mb-4">Cuestionario de factores de riesgo intralaboral y cuestionario de factores de riesgo extralaboral (Tabla 34)</p>

            <!-- Descripción de cuestionarios por forma -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="p-3 text-center" style="background-color: #e3f2fd; border: 1px solid #1976D2; border-radius: 5px;">
                        <span style="font-size: 0.9rem; color: #1976D2; line-height: 1.5;">
                            Cuestionario de factores de<br>
                            riesgo intralaboral <strong>forma A</strong><br>
                            y cuestionario de factores<br>
                            de riesgo extralaboral
                        </span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 text-center" style="background-color: #fff3e0; border: 1px solid #FF9800; border-radius: 5px;">
                        <span style="font-size: 0.9rem; color: #e65100; line-height: 1.5;">
                            Cuestionario de factores de<br>
                            riesgo intralaboral <strong>forma B</strong><br>
                            y cuestionario de factores<br>
                            de riesgo extralaboral
                        </span>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Puntaje Total General Forma A -->
                <?php if ($formaACount > 0): ?>
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="forma-badge forma-a mb-2">
                            <i class="fas fa-users me-1"></i>FORMA A
                        </div>
                        <div class="gauge-title"><span class="gauge-number">0A</span>Puntaje Total General</div>
                        <div class="gauge-wrapper">
                            <canvas id="gaugePuntajeTotalGeneralA"></canvas>
                            <div class="needle" id="needlePuntajeTotalGeneralA"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value"><?= number_format($globalDataFormaA['puntaje_total_general_promedio'], 1) ?></div>
                            <div class="gauge-label"><?= strtoupper(str_replace('_', ' ', $globalDataFormaA['puntaje_total_general_nivel'])) ?></div>
                        </div>
                        <?php renderBaremos($baremosPuntajeTotalGeneralA); ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Puntaje Total General Forma B -->
                <?php if ($formaBCount > 0): ?>
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="forma-badge forma-b mb-2">
                            <i class="fas fa-hard-hat me-1"></i>FORMA B
                        </div>
                        <div class="gauge-title"><span class="gauge-number">0B</span>Puntaje Total General</div>
                        <div class="gauge-wrapper">
                            <canvas id="gaugePuntajeTotalGeneralB"></canvas>
                            <div class="needle" id="needlePuntajeTotalGeneralB"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value"><?= number_format($globalDataFormaB['puntaje_total_general_promedio'], 1) ?></div>
                            <div class="gauge-label"><?= strtoupper(str_replace('_', ' ', $globalDataFormaB['puntaje_total_general_nivel'])) ?></div>
                        </div>
                        <?php renderBaremos($baremosPuntajeTotalGeneralB); ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- SECCIÓN 1: TOTALES GLOBALES FORMA A -->
        <?php if ($formaACount > 0): ?>
        <div class="gauge-section">
            <div class="forma-badge forma-a">
                <i class="fas fa-users me-2"></i>FORMA A - Promedios Globales
            </div>

            <div class="row">
                <!-- Total Intralaboral Forma A -->
                <div class="col-md-4">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">1</span>Total Intralaboral</div>
                        <div class="gauge-wrapper">
                            <canvas id="gaugeIntralaFormaA"></canvas>
                            <div class="needle" id="needleIntralaFormaA"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value"><?= number_format($globalDataFormaA['intralaboral_promedio'], 1) ?></div>
                            <div class="gauge-label"><?= strtoupper($globalDataFormaA['intralaboral_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosIntralaboralA); ?>
                    </div>
                </div>

                <!-- Total Extralaboral Forma A -->
                <div class="col-md-4">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">2</span>Total Extralaboral</div>
                        <div class="gauge-wrapper">
                            <canvas id="gaugeExtralFormaA"></canvas>
                            <div class="needle" id="needleExtralFormaA"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value"><?= number_format($globalDataFormaA['extralaboral_promedio'], 1) ?></div>
                            <div class="gauge-label"><?= strtoupper($globalDataFormaA['extralaboral_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosExtralaboralA); ?>
                    </div>
                </div>

                <!-- Total Estrés Forma A -->
                <div class="col-md-4">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">3</span>Total Estrés</div>
                        <div class="gauge-wrapper">
                            <canvas id="gaugeEstresFormaA"></canvas>
                            <div class="needle" id="needleEstresFormaA"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value"><?= number_format($globalDataFormaA['estres_promedio'], 1) ?></div>
                            <div class="gauge-label"><?= strtoupper($globalDataFormaA['estres_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosEstresA, 'estres'); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- SECCIÓN 2: TOTALES GLOBALES FORMA B -->
        <?php if ($formaBCount > 0): ?>
        <div class="gauge-section">
            <div class="forma-badge forma-b">
                <i class="fas fa-users me-2"></i>FORMA B - Promedios Globales
            </div>

            <div class="row">
                <!-- Total Intralaboral Forma B -->
                <div class="col-md-4">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">8</span>Total Intralaboral</div>
                        <div class="gauge-wrapper">
                            <canvas id="gaugeIntralaFormaB"></canvas>
                            <div class="needle" id="needleIntralaFormaB"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value"><?= number_format($globalDataFormaB['intralaboral_promedio'], 1) ?></div>
                            <div class="gauge-label"><?= strtoupper($globalDataFormaB['intralaboral_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosIntralaboralB); ?>
                    </div>
                </div>

                <!-- Total Extralaboral Forma B -->
                <div class="col-md-4">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">9</span>Total Extralaboral</div>
                        <div class="gauge-wrapper">
                            <canvas id="gaugeExtralFormaB"></canvas>
                            <div class="needle" id="needleExtralFormaB"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value"><?= number_format($globalDataFormaB['extralaboral_promedio'], 1) ?></div>
                            <div class="gauge-label"><?= strtoupper($globalDataFormaB['extralaboral_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosExtralaboralB); ?>
                    </div>
                </div>

                <!-- Total Estrés Forma B -->
                <div class="col-md-4">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">10</span>Total Estrés</div>
                        <div class="gauge-wrapper">
                            <canvas id="gaugeEstresFormaB"></canvas>
                            <div class="needle" id="needleEstresFormaB"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value"><?= number_format($globalDataFormaB['estres_promedio'], 1) ?></div>
                            <div class="gauge-label"><?= strtoupper($globalDataFormaB['estres_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosEstresB, 'estres'); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- SECCIÓN 3: DOMINIOS INTRALABORALES FORMA A -->
        <?php if ($formaACount > 0): ?>
        <div class="gauge-section">
            <div class="forma-badge forma-a">
                <i class="fas fa-cubes me-2"></i>FORMA A - Dominios Intralaborales
            </div>

            <div class="row mb-3">
                <!-- Liderazgo y Relaciones -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">4</span>Liderazgo y Relaciones</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDomLiderazgoA"></canvas>
                            <div class="needle" id="needleDomLiderazgoA"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaA['dom_liderazgo_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaA['dom_liderazgo_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDomLiderazgoA); ?>
                    </div>
                </div>

                <!-- Control sobre el Trabajo -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">5</span>Control sobre el Trabajo</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDomControlA"></canvas>
                            <div class="needle" id="needleDomControlA"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaA['dom_control_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaA['dom_control_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDomControlA); ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Demandas del Trabajo -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">6</span>Demandas del Trabajo</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDomDemandasA"></canvas>
                            <div class="needle" id="needleDomDemandasA"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaA['dom_demandas_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaA['dom_demandas_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDomDemandasA); ?>
                    </div>
                </div>

                <!-- Recompensas -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">7</span>Recompensas</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDomRecompensasA"></canvas>
                            <div class="needle" id="needleDomRecompensasA"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaA['dom_recompensas_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaA['dom_recompensas_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDomRecompensasA); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- SECCIÓN 4: DOMINIOS INTRALABORALES FORMA B -->
        <?php if ($formaBCount > 0): ?>
        <div class="gauge-section">
            <div class="forma-badge forma-b">
                <i class="fas fa-cubes me-2"></i>FORMA B - Dominios Intralaborales
            </div>

            <div class="row mb-3">
                <!-- Liderazgo y Relaciones -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">11</span>Liderazgo y Relaciones</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDomLiderazgoB"></canvas>
                            <div class="needle" id="needleDomLiderazgoB"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaB['dom_liderazgo_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaB['dom_liderazgo_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDomLiderazgoB); ?>
                    </div>
                </div>

                <!-- Control sobre el Trabajo -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">12</span>Control sobre el Trabajo</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDomControlB"></canvas>
                            <div class="needle" id="needleDomControlB"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaB['dom_control_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaB['dom_control_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDomControlB); ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Demandas del Trabajo -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">13</span>Demandas del Trabajo</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDomDemandasB"></canvas>
                            <div class="needle" id="needleDomDemandasB"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaB['dom_demandas_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaB['dom_demandas_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDomDemandasB); ?>
                    </div>
                </div>

                <!-- Recompensas -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">14</span>Recompensas</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDomRecompensasB"></canvas>
                            <div class="needle" id="needleDomRecompensasB"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaB['dom_recompensas_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaB['dom_recompensas_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDomRecompensasB); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- DIMENSIONES INTRALABORALES FORMA A -->
        <?php if (!empty($globalDataFormaA)): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h3 class="card-title mb-0">DIMENSIONES INTRALABORALES - FORMA A</h3>
            </div>

            <div class="row">
                <!-- Características del Liderazgo -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">15</span>Características del Liderazgo</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDimCaracteristicasLiderazgoA"></canvas>
                            <div class="needle" id="needleDimCaracteristicasLiderazgoA"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaA['dim_caracteristicas_liderazgo_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaA['dim_caracteristicas_liderazgo_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDimCaracteristicasLiderazgoA); ?>
                    </div>
                </div>

                <!-- Relaciones Sociales en el Trabajo -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">16</span>Relaciones Sociales en el Trabajo</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDimRelacionesSocialesA"></canvas>
                            <div class="needle" id="needleDimRelacionesSocialesA"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaA['dim_relaciones_sociales_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaA['dim_relaciones_sociales_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDimRelacionesSocialesA); ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Retroalimentación del Desempeño -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">17</span>Retroalimentación del Desempeño</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDimRetroalimentacionA"></canvas>
                            <div class="needle" id="needleDimRetroalimentacionA"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaA['dim_retroalimentacion_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaA['dim_retroalimentacion_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDimRetroalimentacionA); ?>
                    </div>
                </div>

                <!-- Relación con los Colaboradores -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">18</span>Relación con los Colaboradores</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDimRelacionColaboradoresA"></canvas>
                            <div class="needle" id="needleDimRelacionColaboradoresA"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaA['dim_relacion_colaboradores_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaA['dim_relacion_colaboradores_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDimRelacionColaboradoresA); ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Claridad de Rol -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">19</span>Claridad de Rol</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDimClaridadRolA"></canvas>
                            <div class="needle" id="needleDimClaridadRolA"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaA['dim_claridad_rol_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaA['dim_claridad_rol_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDimClaridadRolA); ?>
                    </div>
                </div>

                <!-- Capacitación -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">20</span>Capacitación</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDimCapacitacionA"></canvas>
                            <div class="needle" id="needleDimCapacitacionA"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaA['dim_capacitacion_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaA['dim_capacitacion_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDimCapacitacionA); ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Participación y Manejo del Cambio -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">21</span>Participación y Manejo del Cambio</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDimParticipacionCambioA"></canvas>
                            <div class="needle" id="needleDimParticipacionCambioA"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaA['dim_participacion_cambio_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaA['dim_participacion_cambio_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDimParticipacionCambioA); ?>
                    </div>
                </div>

                <!-- Oportunidades para el uso y desarrollo de habilidades y conocimientos -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">22</span>Oportunidades para el Uso y Desarrollo de Habilidades</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDimOportunidadesA"></canvas>
                            <div class="needle" id="needleDimOportunidadesA"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaA['dim_oportunidades_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaA['dim_oportunidades_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDimOportunidadesA); ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Control y Autonomía sobre el Trabajo -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">23</span>Control y Autonomía sobre el Trabajo</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDimControlAutonomiaA"></canvas>
                            <div class="needle" id="needleDimControlAutonomiaA"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaA['dim_control_autonomia_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaA['dim_control_autonomia_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDimControlAutonomiaA); ?>
                    </div>
                </div>

                <!-- Demandas Ambientales y de Esfuerzo Físico -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">24</span>Demandas Ambientales y de Esfuerzo Físico</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDimDemandasAmbientalesA"></canvas>
                            <div class="needle" id="needleDimDemandasAmbientalesA"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaA['dim_demandas_ambientales_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaA['dim_demandas_ambientales_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDimDemandasAmbientalesA); ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Demandas Emocionales -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">25</span>Demandas Emocionales</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDimDemandasEmocionalesA"></canvas>
                            <div class="needle" id="needleDimDemandasEmocionalesA"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaA['dim_demandas_emocionales_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaA['dim_demandas_emocionales_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDimDemandasEmocionalesA); ?>
                    </div>
                </div>

                <!-- Demandas Cuantitativas -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">26</span>Demandas Cuantitativas</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDimDemandasCuantitativasA"></canvas>
                            <div class="needle" id="needleDimDemandasCuantitativasA"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaA['dim_demandas_cuantitativas_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaA['dim_demandas_cuantitativas_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDimDemandasCuantitativasA); ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Influencia del Trabajo sobre el Entorno Extralaboral -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">27</span>Influencia del Trabajo sobre el Entorno Extralaboral</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDimInfluenciaTrabajoA"></canvas>
                            <div class="needle" id="needleDimInfluenciaTrabajoA"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaA['dim_influencia_trabajo_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaA['dim_influencia_trabajo_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDimInfluenciaTrabajoA); ?>
                    </div>
                </div>

                <!-- Exigencias de Responsabilidad del Cargo -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">28</span>Exigencias de Responsabilidad del Cargo</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDimExigenciasResponsabilidadA"></canvas>
                            <div class="needle" id="needleDimExigenciasResponsabilidadA"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaA['dim_exigencias_responsabilidad_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaA['dim_exigencias_responsabilidad_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDimExigenciasResponsabilidadA); ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Demandas de Carga Mental -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">29</span>Demandas de Carga Mental</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDimDemandasCargaMentalA"></canvas>
                            <div class="needle" id="needleDimDemandasCargaMentalA"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaA['dim_demandas_carga_mental_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaA['dim_demandas_carga_mental_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDimDemandasCargaMentalA); ?>
                    </div>
                </div>

                <!-- Consistencia del Rol -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">30</span>Consistencia del Rol</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDimConsistenciaRolA"></canvas>
                            <div class="needle" id="needleDimConsistenciaRolA"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaA['dim_consistencia_rol_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaA['dim_consistencia_rol_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDimConsistenciaRolA); ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Demandas de la Jornada de Trabajo -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">31</span>Demandas de la Jornada de Trabajo</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDimDemandasJornadaA"></canvas>
                            <div class="needle" id="needleDimDemandasJornadaA"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaA['dim_demandas_jornada_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaA['dim_demandas_jornada_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDimDemandasJornadaA); ?>
                    </div>
                </div>

                <!-- Recompensas Derivadas de la Pertenencia -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">32</span>Recompensas Derivadas de la Pertenencia</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDimRecompensasPertenenciaA"></canvas>
                            <div class="needle" id="needleDimRecompensasPertenenciaA"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaA['dim_recompensas_pertenencia_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaA['dim_recompensas_pertenencia_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDimRecompensasPertenenciaA); ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Reconocimiento y Compensación -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">33</span>Reconocimiento y Compensación</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDimReconocimientoCompensacionA"></canvas>
                            <div class="needle" id="needleDimReconocimientoCompensacionA"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaA['dim_reconocimiento_compensacion_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaA['dim_reconocimiento_compensacion_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDimReconocimientoCompensacionA); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- DIMENSIONES INTRALABORALES FORMA B -->
        <?php if (!empty($globalDataFormaB)): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h3 class="card-title mb-0">DIMENSIONES INTRALABORALES - FORMA B</h3>
            </div>

            <div class="row">
                <!-- Características del Liderazgo -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">34</span>Características del Liderazgo</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDimCaracteristicasLiderazgoB"></canvas>
                            <div class="needle" id="needleDimCaracteristicasLiderazgoB"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaB['dim_caracteristicas_liderazgo_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaB['dim_caracteristicas_liderazgo_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDimCaracteristicasLiderazgoB); ?>
                    </div>
                </div>

                <!-- Relaciones Sociales en el Trabajo -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">35</span>Relaciones Sociales en el Trabajo</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDimRelacionesSocialesB"></canvas>
                            <div class="needle" id="needleDimRelacionesSocialesB"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaB['dim_relaciones_sociales_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaB['dim_relaciones_sociales_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDimRelacionesSocialesB); ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Retroalimentación del Desempeño -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">36</span>Retroalimentación del Desempeño</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDimRetroalimentacionB"></canvas>
                            <div class="needle" id="needleDimRetroalimentacionB"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaB['dim_retroalimentacion_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaB['dim_retroalimentacion_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDimRetroalimentacionB); ?>
                    </div>
                </div>

                <!-- Claridad de Rol -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">37</span>Claridad de Rol</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDimClaridadRolB"></canvas>
                            <div class="needle" id="needleDimClaridadRolB"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaB['dim_claridad_rol_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaB['dim_claridad_rol_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDimClaridadRolB); ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Capacitación -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">38</span>Capacitación</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDimCapacitacionB"></canvas>
                            <div class="needle" id="needleDimCapacitacionB"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaB['dim_capacitacion_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaB['dim_capacitacion_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDimCapacitacionB); ?>
                    </div>
                </div>

                <!-- Participación y Manejo del Cambio -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">39</span>Participación y Manejo del Cambio</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDimParticipacionCambioB"></canvas>
                            <div class="needle" id="needleDimParticipacionCambioB"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaB['dim_participacion_cambio_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaB['dim_participacion_cambio_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDimParticipacionCambioB); ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Oportunidades para el uso y desarrollo de habilidades -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">40</span>Oportunidades para el Uso y Desarrollo de Habilidades</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDimOportunidadesB"></canvas>
                            <div class="needle" id="needleDimOportunidadesB"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaB['dim_oportunidades_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaB['dim_oportunidades_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDimOportunidadesB); ?>
                    </div>
                </div>

                <!-- Control y Autonomía sobre el Trabajo -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">41</span>Control y Autonomía sobre el Trabajo</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDimControlAutonomiaB"></canvas>
                            <div class="needle" id="needleDimControlAutonomiaB"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaB['dim_control_autonomia_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaB['dim_control_autonomia_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDimControlAutonomiaB); ?>
                    </div>
                </div>

                <!-- Demandas Ambientales y de Esfuerzo Físico -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">42</span>Demandas Ambientales y de Esfuerzo Físico</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDimDemandasAmbientalesB"></canvas>
                            <div class="needle" id="needleDimDemandasAmbientalesB"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaB['dim_demandas_ambientales_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaB['dim_demandas_ambientales_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDimDemandasAmbientalesB); ?>
                    </div>
                </div>

                <!-- Demandas Emocionales -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">43</span>Demandas Emocionales</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDimDemandasEmocionalesB"></canvas>
                            <div class="needle" id="needleDimDemandasEmocionalesB"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaB['dim_demandas_emocionales_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaB['dim_demandas_emocionales_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDimDemandasEmocionalesB); ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Demandas Cuantitativas -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">44</span>Demandas Cuantitativas</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDimDemandasCuantitativasB"></canvas>
                            <div class="needle" id="needleDimDemandasCuantitativasB"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaB['dim_demandas_cuantitativas_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaB['dim_demandas_cuantitativas_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDimDemandasCuantitativasB); ?>
                    </div>
                </div>

                <!-- Influencia del Trabajo sobre el Entorno Extralaboral -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">45</span>Influencia del Trabajo sobre el Entorno Extralaboral</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDimInfluenciaTrabajoB"></canvas>
                            <div class="needle" id="needleDimInfluenciaTrabajoB"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaB['dim_influencia_trabajo_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaB['dim_influencia_trabajo_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDimInfluenciaTrabajoB); ?>
                    </div>
                </div>

                <!-- Demandas de Carga Mental -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">46</span>Demandas de Carga Mental</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDimDemandasCargaMentalB"></canvas>
                            <div class="needle" id="needleDimDemandasCargaMentalB"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaB['dim_demandas_carga_mental_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaB['dim_demandas_carga_mental_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDimDemandasCargaMentalB); ?>
                    </div>
                </div>

                <!-- Demandas de la Jornada de Trabajo -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">47</span>Demandas de la Jornada de Trabajo</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDimDemandasJornadaB"></canvas>
                            <div class="needle" id="needleDimDemandasJornadaB"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaB['dim_demandas_jornada_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaB['dim_demandas_jornada_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDimDemandasJornadaB); ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Recompensas Derivadas de la Pertenencia -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">48</span>Recompensas Derivadas de la Pertenencia</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDimRecompensasPertenenciaB"></canvas>
                            <div class="needle" id="needleDimRecompensasPertenenciaB"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaB['dim_recompensas_pertenencia_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaB['dim_recompensas_pertenencia_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDimRecompensasPertenenciaB); ?>
                    </div>
                </div>

                <!-- Reconocimiento y Compensación -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">49</span>Reconocimiento y Compensación</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDimReconocimientoCompensacionB"></canvas>
                            <div class="needle" id="needleDimReconocimientoCompensacionB"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaB['dim_reconocimiento_compensacion_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaB['dim_reconocimiento_compensacion_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDimReconocimientoCompensacionB); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- DIMENSIONES EXTRALABORALES FORMA A -->
        <?php if (!empty($globalDataFormaA)): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h3 class="card-title mb-0">DIMENSIONES EXTRALABORALES - FORMA A</h3>
            </div>

            <div class="row">
                <!-- Tiempo Fuera del Trabajo -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">50</span>Tiempo Fuera del Trabajo</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDimTiempoFueraTrabajoA"></canvas>
                            <div class="needle" id="needleDimTiempoFueraTrabajoA"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaA['dim_tiempo_fuera_trabajo_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaA['dim_tiempo_fuera_trabajo_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDimTiempoFueraTrabajoA); ?>
                    </div>
                </div>

                <!-- Relaciones Familiares -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">51</span>Relaciones Familiares</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDimRelacionesFamiliaresA"></canvas>
                            <div class="needle" id="needleDimRelacionesFamiliaresA"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaA['dim_relaciones_familiares_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaA['dim_relaciones_familiares_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDimRelacionesFamiliaresA); ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Comunicación y Relaciones Interpersonales -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">52</span>Comunicación y Relaciones Interpersonales</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDimComunicacionRelacionesA"></canvas>
                            <div class="needle" id="needleDimComunicacionRelacionesA"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaA['dim_comunicacion_relaciones_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaA['dim_comunicacion_relaciones_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDimComunicacionRelacionesA); ?>
                    </div>
                </div>

                <!-- Situación Económica del Grupo Familiar -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">53</span>Situación Económica del Grupo Familiar</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDimSituacionEconomicaA"></canvas>
                            <div class="needle" id="needleDimSituacionEconomicaA"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaA['dim_situacion_economica_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaA['dim_situacion_economica_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDimSituacionEconomicaA); ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Características de la Vivienda y de su Entorno -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">54</span>Características de la Vivienda y de su Entorno</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDimCaracteristicasViviendaA"></canvas>
                            <div class="needle" id="needleDimCaracteristicasViviendaA"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaA['dim_caracteristicas_vivienda_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaA['dim_caracteristicas_vivienda_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDimCaracteristicasViviendaA); ?>
                    </div>
                </div>

                <!-- Influencia del Entorno Extralaboral sobre el Trabajo -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">55</span>Influencia del Entorno Extralaboral sobre el Trabajo</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDimInfluenciaEntornoA"></canvas>
                            <div class="needle" id="needleDimInfluenciaEntornoA"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaA['dim_influencia_entorno_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaA['dim_influencia_entorno_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDimInfluenciaEntornoA); ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Desplazamiento Vivienda - Trabajo - Vivienda -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">56</span>Desplazamiento Vivienda - Trabajo - Vivienda</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDimDesplazamientoViviendaA"></canvas>
                            <div class="needle" id="needleDimDesplazamientoViviendaA"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaA['dim_desplazamiento_vivienda_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaA['dim_desplazamiento_vivienda_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDimDesplazamientoViviendaA); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- DIMENSIONES EXTRALABORALES FORMA B -->
        <?php if (!empty($globalDataFormaB)): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h3 class="card-title mb-0">DIMENSIONES EXTRALABORALES - FORMA B</h3>
            </div>

            <div class="row">
                <!-- Tiempo Fuera del Trabajo -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">57</span>Tiempo Fuera del Trabajo</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDimTiempoFueraTrabajoB"></canvas>
                            <div class="needle" id="needleDimTiempoFueraTrabajoB"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaB['dim_tiempo_fuera_trabajo_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaB['dim_tiempo_fuera_trabajo_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDimTiempoFueraTrabajoB); ?>
                    </div>
                </div>

                <!-- Relaciones Familiares -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">58</span>Relaciones Familiares</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDimRelacionesFamiliaresB"></canvas>
                            <div class="needle" id="needleDimRelacionesFamiliaresB"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaB['dim_relaciones_familiares_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaB['dim_relaciones_familiares_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDimRelacionesFamiliaresB); ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Comunicación y Relaciones Interpersonales -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">59</span>Comunicación y Relaciones Interpersonales</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDimComunicacionRelacionesB"></canvas>
                            <div class="needle" id="needleDimComunicacionRelacionesB"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaB['dim_comunicacion_relaciones_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaB['dim_comunicacion_relaciones_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDimComunicacionRelacionesB); ?>
                    </div>
                </div>

                <!-- Situación Económica del Grupo Familiar -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">60</span>Situación Económica del Grupo Familiar</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDimSituacionEconomicaB"></canvas>
                            <div class="needle" id="needleDimSituacionEconomicaB"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaB['dim_situacion_economica_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaB['dim_situacion_economica_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDimSituacionEconomicaB); ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Características de la Vivienda y de su Entorno -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">61</span>Características de la Vivienda y de su Entorno</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDimCaracteristicasViviendaB"></canvas>
                            <div class="needle" id="needleDimCaracteristicasViviendaB"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaB['dim_caracteristicas_vivienda_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaB['dim_caracteristicas_vivienda_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDimCaracteristicasViviendaB); ?>
                    </div>
                </div>

                <!-- Influencia del Entorno Extralaboral sobre el Trabajo -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">62</span>Influencia del Entorno Extralaboral sobre el Trabajo</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDimInfluenciaEntornoB"></canvas>
                            <div class="needle" id="needleDimInfluenciaEntornoB"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaB['dim_influencia_entorno_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaB['dim_influencia_entorno_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDimInfluenciaEntornoB); ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Desplazamiento Vivienda - Trabajo - Vivienda -->
                <div class="col-md-6">
                    <div class="gauge-card">
                        <div class="gauge-title"><span class="gauge-number">63</span>Desplazamiento Vivienda - Trabajo - Vivienda</div>
                        <div class="gauge-wrapper" style="height: 200px;">
                            <canvas id="gaugeDimDesplazamientoViviendaB"></canvas>
                            <div class="needle" id="needleDimDesplazamientoViviendaB"></div>
                        </div>
                        <div class="gauge-score">
                            <div class="gauge-score-value" style="font-size: 1.3rem;"><?= number_format($globalDataFormaB['dim_desplazamiento_vivienda_promedio'], 1) ?></div>
                            <div class="gauge-label" style="font-size: 0.75rem;"><?= strtoupper($globalDataFormaB['dim_desplazamiento_vivienda_nivel']) ?></div>
                        </div>
                        <?php renderBaremos($baremosDimDesplazamientoViviendaB); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Botón de volver -->
        <div class="text-center mb-4 no-print">
            <a href="<?= base_url('battery-services/' . $service['id']) ?>" class="btn btn-lg btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Volver al Servicio
            </a>
        </div>
    </div>

    <script>
        // Registrar plugin de datalabels
        Chart.register(ChartDataLabels);

        // Función helper para crear gauge
        function createGauge(canvasId, needleId, score, baremos) {
            const canvas = document.getElementById(canvasId);
            const needle = document.getElementById(needleId);

            if (!canvas || !needle || !baremos) {
                console.warn('createGauge: Elemento no encontrado o baremos null', canvasId, baremos);
                return;
            }

            // Detectar tipo de nomenclatura (Estrés usa muy_bajo/bajo/medio/alto/muy_alto)
            const esEstres = baremos.hasOwnProperty('muy_bajo');

            let segments, labels;

            if (esEstres) {
                // Nomenclatura de Estrés: muy_bajo, bajo, medio, alto, muy_alto
                segments = [
                    baremos.muy_bajo[1] - baremos.muy_bajo[0],
                    baremos.bajo[1] - baremos.bajo[0],
                    baremos.medio[1] - baremos.medio[0],
                    baremos.alto[1] - baremos.alto[0],
                    baremos.muy_alto[1] - baremos.muy_alto[0]
                ];
                labels = [
                    baremos.muy_bajo[0] + ' - ' + baremos.muy_bajo[1],
                    baremos.bajo[0] + ' - ' + baremos.bajo[1],
                    baremos.medio[0] + ' - ' + baremos.medio[1],
                    baremos.alto[0] + ' - ' + baremos.alto[1],
                    baremos.muy_alto[0] + ' - ' + baremos.muy_alto[1]
                ];
            } else {
                // Nomenclatura estándar: sin_riesgo, riesgo_bajo, riesgo_medio, riesgo_alto, riesgo_muy_alto
                segments = [
                    baremos.sin_riesgo[1] - baremos.sin_riesgo[0],
                    baremos.riesgo_bajo[1] - baremos.riesgo_bajo[0],
                    baremos.riesgo_medio[1] - baremos.riesgo_medio[0],
                    baremos.riesgo_alto[1] - baremos.riesgo_alto[0],
                    baremos.riesgo_muy_alto[1] - baremos.riesgo_muy_alto[0]
                ];
                labels = [
                    baremos.sin_riesgo[0] + ' - ' + baremos.sin_riesgo[1],
                    baremos.riesgo_bajo[0] + ' - ' + baremos.riesgo_bajo[1],
                    baremos.riesgo_medio[0] + ' - ' + baremos.riesgo_medio[1],
                    baremos.riesgo_alto[0] + ' - ' + baremos.riesgo_alto[1],
                    baremos.riesgo_muy_alto[0] + ' - ' + baremos.riesgo_muy_alto[1]
                ];
            }

            // Calcular ángulo de la aguja (-90 a 90)
            const angleDeg = -90 + (score / 100) * 180;
            needle.style.transform = `rotate(${angleDeg}deg)`;

            new Chart(canvas, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: segments,
                        backgroundColor: ['#28a745', '#90ee90', '#FFFF00', '#dc3545', '#8b0000'],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    circumference: 180,
                    rotation: -90,
                    plugins: {
                        legend: { display: false },
                        tooltip: { enabled: false },
                        datalabels: {
                            color: '#000',
                            font: { weight: 'bold', size: 10 },
                            formatter: (value, ctx) => ctx.chart.data.labels[ctx.dataIndex],
                            anchor: 'center',
                            align: 'end',
                            offset: -5
                        }
                    }
                }
            });
        }

        // =======================================================================
        // BAREMOS - Generados desde PHP (Single Source of Truth)
        // =======================================================================

        // Baremos Puntaje Total General (Tabla 34)
        const baremosPuntajeTotalGeneralA = <?= json_encode($baremosPuntajeTotalGeneralA) ?>;
        const baremosPuntajeTotalGeneralB = <?= json_encode($baremosPuntajeTotalGeneralB) ?>;

        // Baremos de Tabla 33 - Intralaboral Total
        const baremosIntralaboralA = <?= json_encode($baremosIntralaboralA) ?>;
        const baremosIntralaboralB = <?= json_encode($baremosIntralaboralB) ?>;

        // Baremos de Tablas 17/18 - Extralaboral Total
        const baremosExtralaboralA = <?= json_encode($baremosExtralaboralA) ?>;
        const baremosExtralaboralB = <?= json_encode($baremosExtralaboralB) ?>;

        // Baremos de Tabla 6 - Estrés
        const baremosEstresA = <?= json_encode($baremosEstresA) ?>;
        const baremosEstresB = <?= json_encode($baremosEstresB) ?>;

        // Baremos de Tabla 31 - Dominios Forma A
        const baremosDomLiderazgoA = <?= json_encode($baremosDomLiderazgoA) ?>;
        const baremosDomControlA = <?= json_encode($baremosDomControlA) ?>;
        const baremosDomDemandasA = <?= json_encode($baremosDomDemandasA) ?>;
        const baremosDomRecompensasA = <?= json_encode($baremosDomRecompensasA) ?>;

        // Baremos de Tabla 32 - Dominios Forma B
        const baremosDomLiderazgoB = <?= json_encode($baremosDomLiderazgoB) ?>;
        const baremosDomControlB = <?= json_encode($baremosDomControlB) ?>;
        const baremosDomDemandasB = <?= json_encode($baremosDomDemandasB) ?>;
        const baremosDomRecompensasB = <?= json_encode($baremosDomRecompensasB) ?>;

        // =======================================================================
        // BAREMOS DE DIMENSIONES - Desde Single Source of Truth (PHP)
        // =======================================================================

        // Baremos de Tabla 29 - Dimensiones Forma A
        const baremosDimCaracteristicasLiderazgoA = <?= json_encode($baremosDimCaracteristicasLiderazgoA) ?>;
        const baremosDimRelacionesSocialesA = <?= json_encode($baremosDimRelacionesSocialesA) ?>;
        const baremosDimRetroalimentacionA = <?= json_encode($baremosDimRetroalimentacionA) ?>;
        const baremosDimRelacionColaboradoresA = <?= json_encode($baremosDimRelacionColaboradoresA) ?>;
        const baremosDimClaridadRolA = <?= json_encode($baremosDimClaridadRolA) ?>;
        const baremosDimCapacitacionA = <?= json_encode($baremosDimCapacitacionA) ?>;
        const baremosDimParticipacionCambioA = <?= json_encode($baremosDimParticipacionCambioA) ?>;
        const baremosDimOportunidadesA = <?= json_encode($baremosDimOportunidadesA) ?>;
        const baremosDimControlAutonomiaA = <?= json_encode($baremosDimControlAutonomiaA) ?>;
        const baremosDimDemandasAmbientalesA = <?= json_encode($baremosDimDemandasAmbientalesA) ?>;
        const baremosDimDemandasEmocionalesA = <?= json_encode($baremosDimDemandasEmocionalesA) ?>;
        const baremosDimDemandasCuantitativasA = <?= json_encode($baremosDimDemandasCuantitativasA) ?>;
        const baremosDimInfluenciaTrabajoA = <?= json_encode($baremosDimInfluenciaTrabajoA) ?>;
        const baremosDimExigenciasResponsabilidadA = <?= json_encode($baremosDimExigenciasResponsabilidadA) ?>;
        const baremosDimDemandasCargaMentalA = <?= json_encode($baremosDimDemandasCargaMentalA) ?>;
        const baremosDimConsistenciaRolA = <?= json_encode($baremosDimConsistenciaRolA) ?>;
        const baremosDimDemandasJornadaA = <?= json_encode($baremosDimDemandasJornadaA) ?>;
        const baremosDimRecompensasPertenenciaA = <?= json_encode($baremosDimRecompensasPertenenciaA) ?>;
        const baremosDimReconocimientoCompensacionA = <?= json_encode($baremosDimReconocimientoCompensacionA) ?>;

        // Baremos de Tabla 30 - Dimensiones Forma B
        const baremosDimCaracteristicasLiderazgoB = <?= json_encode($baremosDimCaracteristicasLiderazgoB) ?>;
        const baremosDimRelacionesSocialesB = <?= json_encode($baremosDimRelacionesSocialesB) ?>;
        const baremosDimRetroalimentacionB = <?= json_encode($baremosDimRetroalimentacionB) ?>;
        const baremosDimClaridadRolB = <?= json_encode($baremosDimClaridadRolB) ?>;
        const baremosDimCapacitacionB = <?= json_encode($baremosDimCapacitacionB) ?>;
        const baremosDimParticipacionCambioB = <?= json_encode($baremosDimParticipacionCambioB) ?>;
        const baremosDimOportunidadesB = <?= json_encode($baremosDimOportunidadesB) ?>;
        const baremosDimControlAutonomiaB = <?= json_encode($baremosDimControlAutonomiaB) ?>;
        const baremosDimDemandasAmbientalesB = <?= json_encode($baremosDimDemandasAmbientalesB) ?>;
        const baremosDimDemandasEmocionalesB = <?= json_encode($baremosDimDemandasEmocionalesB) ?>;
        const baremosDimDemandasCuantitativasB = <?= json_encode($baremosDimDemandasCuantitativasB) ?>;
        const baremosDimInfluenciaTrabajoB = <?= json_encode($baremosDimInfluenciaTrabajoB) ?>;
        const baremosDimDemandasCargaMentalB = <?= json_encode($baremosDimDemandasCargaMentalB) ?>;
        const baremosDimDemandasJornadaB = <?= json_encode($baremosDimDemandasJornadaB) ?>;
        const baremosDimRecompensasPertenenciaB = <?= json_encode($baremosDimRecompensasPertenenciaB) ?>;
        const baremosDimReconocimientoCompensacionB = <?= json_encode($baremosDimReconocimientoCompensacionB) ?>;

        // Baremos Dimensiones Extralaborales Forma A (Tabla 17)
        const baremosDimTiempoFueraTrabajoA = <?= json_encode($baremosDimTiempoFueraTrabajoA) ?>;
        const baremosDimRelacionesFamiliaresA = <?= json_encode($baremosDimRelacionesFamiliaresA) ?>;
        const baremosDimComunicacionRelacionesA = <?= json_encode($baremosDimComunicacionRelacionesA) ?>;
        const baremosDimSituacionEconomicaA = <?= json_encode($baremosDimSituacionEconomicaA) ?>;
        const baremosDimCaracteristicasViviendaA = <?= json_encode($baremosDimCaracteristicasViviendaA) ?>;
        const baremosDimInfluenciaEntornoA = <?= json_encode($baremosDimInfluenciaEntornoA) ?>;
        const baremosDimDesplazamientoViviendaA = <?= json_encode($baremosDimDesplazamientoViviendaA) ?>;

        // Baremos Dimensiones Extralaborales Forma B (Tabla 18)
        const baremosDimTiempoFueraTrabajoB = <?= json_encode($baremosDimTiempoFueraTrabajoB) ?>;
        const baremosDimRelacionesFamiliaresB = <?= json_encode($baremosDimRelacionesFamiliaresB) ?>;
        const baremosDimComunicacionRelacionesB = <?= json_encode($baremosDimComunicacionRelacionesB) ?>;
        const baremosDimSituacionEconomicaB = <?= json_encode($baremosDimSituacionEconomicaB) ?>;
        const baremosDimCaracteristicasViviendaB = <?= json_encode($baremosDimCaracteristicasViviendaB) ?>;
        const baremosDimInfluenciaEntornoB = <?= json_encode($baremosDimInfluenciaEntornoB) ?>;
        const baremosDimDesplazamientoViviendaB = <?= json_encode($baremosDimDesplazamientoViviendaB) ?>;

        // =========================================================================
        // DIAGNÓSTICO JS ÁCIDO - Verificar qué baremos llegaron correctamente
        // =========================================================================
        const diagnosticoJS = {
            // Totales
            baremosPuntajeTotalGeneralA: baremosPuntajeTotalGeneralA,
            baremosPuntajeTotalGeneralB: baremosPuntajeTotalGeneralB,
            baremosIntralaboralA: baremosIntralaboralA,
            baremosIntralaboralB: baremosIntralaboralB,
            baremosExtralaboralA: baremosExtralaboralA,
            baremosExtralaboralB: baremosExtralaboralB,
            baremosEstresA: baremosEstresA,
            baremosEstresB: baremosEstresB,
            // Dominios
            baremosDomLiderazgoA: baremosDomLiderazgoA,
            baremosDomControlA: baremosDomControlA,
            baremosDomDemandasA: baremosDomDemandasA,
            baremosDomRecompensasA: baremosDomRecompensasA,
            baremosDomLiderazgoB: baremosDomLiderazgoB,
            baremosDomControlB: baremosDomControlB,
            baremosDomDemandasB: baremosDomDemandasB,
            baremosDomRecompensasB: baremosDomRecompensasB,
            // Dimensiones A
            baremosDimCaracteristicasLiderazgoA: baremosDimCaracteristicasLiderazgoA,
            baremosDimRelacionesSocialesA: baremosDimRelacionesSocialesA,
            baremosDimRetroalimentacionA: baremosDimRetroalimentacionA,
            baremosDimRelacionColaboradoresA: baremosDimRelacionColaboradoresA,
            baremosDimClaridadRolA: baremosDimClaridadRolA,
            baremosDimCapacitacionA: baremosDimCapacitacionA,
            baremosDimParticipacionCambioA: baremosDimParticipacionCambioA,
            baremosDimOportunidadesA: baremosDimOportunidadesA,
            baremosDimControlAutonomiaA: baremosDimControlAutonomiaA,
            baremosDimDemandasAmbientalesA: baremosDimDemandasAmbientalesA,
            baremosDimDemandasEmocionalesA: baremosDimDemandasEmocionalesA,
            baremosDimDemandasCuantitativasA: baremosDimDemandasCuantitativasA,
            baremosDimInfluenciaTrabajoA: baremosDimInfluenciaTrabajoA,
            baremosDimExigenciasResponsabilidadA: baremosDimExigenciasResponsabilidadA,
            baremosDimDemandasCargaMentalA: baremosDimDemandasCargaMentalA,
            baremosDimConsistenciaRolA: baremosDimConsistenciaRolA,
            baremosDimDemandasJornadaA: baremosDimDemandasJornadaA,
            baremosDimRecompensasPertenenciaA: baremosDimRecompensasPertenenciaA,
            baremosDimReconocimientoCompensacionA: baremosDimReconocimientoCompensacionA,
            // Dimensiones B
            baremosDimCaracteristicasLiderazgoB: baremosDimCaracteristicasLiderazgoB,
            baremosDimRelacionesSocialesB: baremosDimRelacionesSocialesB,
            baremosDimRetroalimentacionB: baremosDimRetroalimentacionB,
            baremosDimClaridadRolB: baremosDimClaridadRolB,
            baremosDimCapacitacionB: baremosDimCapacitacionB,
            baremosDimParticipacionCambioB: baremosDimParticipacionCambioB,
            baremosDimOportunidadesB: baremosDimOportunidadesB,
            baremosDimControlAutonomiaB: baremosDimControlAutonomiaB,
            baremosDimDemandasAmbientalesB: baremosDimDemandasAmbientalesB,
            baremosDimDemandasEmocionalesB: baremosDimDemandasEmocionalesB,
            baremosDimDemandasCuantitativasB: baremosDimDemandasCuantitativasB,
            baremosDimInfluenciaTrabajoB: baremosDimInfluenciaTrabajoB,
            baremosDimDemandasCargaMentalB: baremosDimDemandasCargaMentalB,
            baremosDimDemandasJornadaB: baremosDimDemandasJornadaB,
            baremosDimRecompensasPertenenciaB: baremosDimRecompensasPertenenciaB,
            baremosDimReconocimientoCompensacionB: baremosDimReconocimientoCompensacionB,
            // Extralaborales
            baremosDimTiempoFueraTrabajoA: baremosDimTiempoFueraTrabajoA,
            baremosDimRelacionesFamiliaresA: baremosDimRelacionesFamiliaresA,
            baremosDimComunicacionRelacionesA: baremosDimComunicacionRelacionesA,
            baremosDimSituacionEconomicaA: baremosDimSituacionEconomicaA,
            baremosDimCaracteristicasViviendaA: baremosDimCaracteristicasViviendaA,
            baremosDimInfluenciaEntornoA: baremosDimInfluenciaEntornoA,
            baremosDimDesplazamientoViviendaA: baremosDimDesplazamientoViviendaA,
            baremosDimTiempoFueraTrabajoB: baremosDimTiempoFueraTrabajoB,
            baremosDimRelacionesFamiliaresB: baremosDimRelacionesFamiliaresB,
            baremosDimComunicacionRelacionesB: baremosDimComunicacionRelacionesB,
            baremosDimSituacionEconomicaB: baremosDimSituacionEconomicaB,
            baremosDimCaracteristicasViviendaB: baremosDimCaracteristicasViviendaB,
            baremosDimInfluenciaEntornoB: baremosDimInfluenciaEntornoB,
            baremosDimDesplazamientoViviendaB: baremosDimDesplazamientoViviendaB,
        };

        // Contar nulls y mostrar resumen
        const nullsJS = [];
        const oksJS = [];
        for (const [key, val] of Object.entries(diagnosticoJS)) {
            if (val === null || val === undefined) {
                nullsJS.push(key);
            } else {
                oksJS.push(key);
            }
        }

        console.log('=== DIAGNÓSTICO JS BAREMOS ===');
        console.log('NULLS (' + nullsJS.length + '):', nullsJS);
        console.log('OKs (' + oksJS.length + '):', oksJS);
        console.log('Detalle completo:', diagnosticoJS);

        // Mostrar en la página si hay nulls
        if (nullsJS.length > 0) {
            const alertDiv = document.createElement('div');
            alertDiv.style.cssText = 'background:#f44336;color:white;padding:20px;margin:10px;border-radius:8px;';
            alertDiv.innerHTML = '<h3>⚠️ DIAGNÓSTICO: ' + nullsJS.length + ' baremos NULL en JS</h3><pre>' + JSON.stringify(nullsJS, null, 2) + '</pre>';
            document.body.insertBefore(alertDiv, document.body.firstChild);
        }
        // =========================================================================

        // Puntaje Total General (Tabla 34)
        <?php if ($formaACount > 0): ?>
        createGauge('gaugePuntajeTotalGeneralA', 'needlePuntajeTotalGeneralA', <?= $globalDataFormaA['puntaje_total_general_promedio'] ?>, baremosPuntajeTotalGeneralA);
        <?php endif; ?>
        <?php if ($formaBCount > 0): ?>
        createGauge('gaugePuntajeTotalGeneralB', 'needlePuntajeTotalGeneralB', <?= $globalDataFormaB['puntaje_total_general_promedio'] ?>, baremosPuntajeTotalGeneralB);
        <?php endif; ?>

        // Crear gauges para Forma A
        <?php if ($formaACount > 0): ?>
        createGauge('gaugeIntralaFormaA', 'needleIntralaFormaA', <?= $globalDataFormaA['intralaboral_promedio'] ?>, baremosIntralaboralA);
        createGauge('gaugeExtralFormaA', 'needleExtralFormaA', <?= $globalDataFormaA['extralaboral_promedio'] ?>, baremosExtralaboralA);
        createGauge('gaugeEstresFormaA', 'needleEstresFormaA', <?= $globalDataFormaA['estres_promedio'] ?>, baremosEstresA);

        // Dominios Forma A
        createGauge('gaugeDomLiderazgoA', 'needleDomLiderazgoA', <?= $globalDataFormaA['dom_liderazgo_promedio'] ?>, baremosDomLiderazgoA);
        createGauge('gaugeDomControlA', 'needleDomControlA', <?= $globalDataFormaA['dom_control_promedio'] ?>, baremosDomControlA);
        createGauge('gaugeDomDemandasA', 'needleDomDemandasA', <?= $globalDataFormaA['dom_demandas_promedio'] ?>, baremosDomDemandasA);
        createGauge('gaugeDomRecompensasA', 'needleDomRecompensasA', <?= $globalDataFormaA['dom_recompensas_promedio'] ?>, baremosDomRecompensasA);

        // Dimensiones Forma A (primeras 5)
        createGauge('gaugeDimCaracteristicasLiderazgoA', 'needleDimCaracteristicasLiderazgoA', <?= $globalDataFormaA['dim_caracteristicas_liderazgo_promedio'] ?>, baremosDimCaracteristicasLiderazgoA);
        createGauge('gaugeDimRelacionesSocialesA', 'needleDimRelacionesSocialesA', <?= $globalDataFormaA['dim_relaciones_sociales_promedio'] ?>, baremosDimRelacionesSocialesA);
        createGauge('gaugeDimRetroalimentacionA', 'needleDimRetroalimentacionA', <?= $globalDataFormaA['dim_retroalimentacion_promedio'] ?>, baremosDimRetroalimentacionA);
        createGauge('gaugeDimRelacionColaboradoresA', 'needleDimRelacionColaboradoresA', <?= $globalDataFormaA['dim_relacion_colaboradores_promedio'] ?>, baremosDimRelacionColaboradoresA);
        createGauge('gaugeDimClaridadRolA', 'needleDimClaridadRolA', <?= $globalDataFormaA['dim_claridad_rol_promedio'] ?>, baremosDimClaridadRolA);

        // Dimensiones Forma A (6-10)
        createGauge('gaugeDimCapacitacionA', 'needleDimCapacitacionA', <?= $globalDataFormaA['dim_capacitacion_promedio'] ?>, baremosDimCapacitacionA);
        createGauge('gaugeDimParticipacionCambioA', 'needleDimParticipacionCambioA', <?= $globalDataFormaA['dim_participacion_cambio_promedio'] ?>, baremosDimParticipacionCambioA);
        createGauge('gaugeDimOportunidadesA', 'needleDimOportunidadesA', <?= $globalDataFormaA['dim_oportunidades_promedio'] ?>, baremosDimOportunidadesA);
        createGauge('gaugeDimControlAutonomiaA', 'needleDimControlAutonomiaA', <?= $globalDataFormaA['dim_control_autonomia_promedio'] ?>, baremosDimControlAutonomiaA);
        createGauge('gaugeDimDemandasAmbientalesA', 'needleDimDemandasAmbientalesA', <?= $globalDataFormaA['dim_demandas_ambientales_promedio'] ?>, baremosDimDemandasAmbientalesA);

        // Dimensiones Forma A (11-15)
        createGauge('gaugeDimDemandasEmocionalesA', 'needleDimDemandasEmocionalesA', <?= $globalDataFormaA['dim_demandas_emocionales_promedio'] ?>, baremosDimDemandasEmocionalesA);
        createGauge('gaugeDimDemandasCuantitativasA', 'needleDimDemandasCuantitativasA', <?= $globalDataFormaA['dim_demandas_cuantitativas_promedio'] ?>, baremosDimDemandasCuantitativasA);
        createGauge('gaugeDimInfluenciaTrabajoA', 'needleDimInfluenciaTrabajoA', <?= $globalDataFormaA['dim_influencia_trabajo_promedio'] ?>, baremosDimInfluenciaTrabajoA);
        createGauge('gaugeDimExigenciasResponsabilidadA', 'needleDimExigenciasResponsabilidadA', <?= $globalDataFormaA['dim_exigencias_responsabilidad_promedio'] ?>, baremosDimExigenciasResponsabilidadA);
        createGauge('gaugeDimDemandasCargaMentalA', 'needleDimDemandasCargaMentalA', <?= $globalDataFormaA['dim_demandas_carga_mental_promedio'] ?>, baremosDimDemandasCargaMentalA);

        // Dimensiones Forma A (16-19)
        createGauge('gaugeDimConsistenciaRolA', 'needleDimConsistenciaRolA', <?= $globalDataFormaA['dim_consistencia_rol_promedio'] ?>, baremosDimConsistenciaRolA);
        createGauge('gaugeDimDemandasJornadaA', 'needleDimDemandasJornadaA', <?= $globalDataFormaA['dim_demandas_jornada_promedio'] ?>, baremosDimDemandasJornadaA);
        createGauge('gaugeDimRecompensasPertenenciaA', 'needleDimRecompensasPertenenciaA', <?= $globalDataFormaA['dim_recompensas_pertenencia_promedio'] ?>, baremosDimRecompensasPertenenciaA);
        createGauge('gaugeDimReconocimientoCompensacionA', 'needleDimReconocimientoCompensacionA', <?= $globalDataFormaA['dim_reconocimiento_compensacion_promedio'] ?>, baremosDimReconocimientoCompensacionA);
        <?php endif; ?>

        // Crear gauges para Forma B
        <?php if ($formaBCount > 0): ?>
        createGauge('gaugeIntralaFormaB', 'needleIntralaFormaB', <?= $globalDataFormaB['intralaboral_promedio'] ?>, baremosIntralaboralB);
        createGauge('gaugeExtralFormaB', 'needleExtralFormaB', <?= $globalDataFormaB['extralaboral_promedio'] ?>, baremosExtralaboralB);
        createGauge('gaugeEstresFormaB', 'needleEstresFormaB', <?= $globalDataFormaB['estres_promedio'] ?>, baremosEstresB);

        // Dominios Forma B
        createGauge('gaugeDomLiderazgoB', 'needleDomLiderazgoB', <?= $globalDataFormaB['dom_liderazgo_promedio'] ?>, baremosDomLiderazgoB);
        createGauge('gaugeDomControlB', 'needleDomControlB', <?= $globalDataFormaB['dom_control_promedio'] ?>, baremosDomControlB);
        createGauge('gaugeDomDemandasB', 'needleDomDemandasB', <?= $globalDataFormaB['dom_demandas_promedio'] ?>, baremosDomDemandasB);
        createGauge('gaugeDomRecompensasB', 'needleDomRecompensasB', <?= $globalDataFormaB['dom_recompensas_promedio'] ?>, baremosDomRecompensasB);

        // Dimensiones Forma B (primeras 5)
        createGauge('gaugeDimCaracteristicasLiderazgoB', 'needleDimCaracteristicasLiderazgoB', <?= $globalDataFormaB['dim_caracteristicas_liderazgo_promedio'] ?>, baremosDimCaracteristicasLiderazgoB);
        createGauge('gaugeDimRelacionesSocialesB', 'needleDimRelacionesSocialesB', <?= $globalDataFormaB['dim_relaciones_sociales_promedio'] ?>, baremosDimRelacionesSocialesB);
        createGauge('gaugeDimRetroalimentacionB', 'needleDimRetroalimentacionB', <?= $globalDataFormaB['dim_retroalimentacion_promedio'] ?>, baremosDimRetroalimentacionB);
        createGauge('gaugeDimClaridadRolB', 'needleDimClaridadRolB', <?= $globalDataFormaB['dim_claridad_rol_promedio'] ?>, baremosDimClaridadRolB);
        createGauge('gaugeDimCapacitacionB', 'needleDimCapacitacionB', <?= $globalDataFormaB['dim_capacitacion_promedio'] ?>, baremosDimCapacitacionB);

        // Dimensiones Forma B (6-10)
        createGauge('gaugeDimParticipacionCambioB', 'needleDimParticipacionCambioB', <?= $globalDataFormaB['dim_participacion_cambio_promedio'] ?>, baremosDimParticipacionCambioB);
        createGauge('gaugeDimOportunidadesB', 'needleDimOportunidadesB', <?= $globalDataFormaB['dim_oportunidades_promedio'] ?>, baremosDimOportunidadesB);
        createGauge('gaugeDimControlAutonomiaB', 'needleDimControlAutonomiaB', <?= $globalDataFormaB['dim_control_autonomia_promedio'] ?>, baremosDimControlAutonomiaB);
        createGauge('gaugeDimDemandasAmbientalesB', 'needleDimDemandasAmbientalesB', <?= $globalDataFormaB['dim_demandas_ambientales_promedio'] ?>, baremosDimDemandasAmbientalesB);
        createGauge('gaugeDimDemandasEmocionalesB', 'needleDimDemandasEmocionalesB', <?= $globalDataFormaB['dim_demandas_emocionales_promedio'] ?>, baremosDimDemandasEmocionalesB);

        // Dimensiones Forma B (11-16)
        createGauge('gaugeDimDemandasCuantitativasB', 'needleDimDemandasCuantitativasB', <?= $globalDataFormaB['dim_demandas_cuantitativas_promedio'] ?>, baremosDimDemandasCuantitativasB);
        createGauge('gaugeDimInfluenciaTrabajoB', 'needleDimInfluenciaTrabajoB', <?= $globalDataFormaB['dim_influencia_trabajo_promedio'] ?>, baremosDimInfluenciaTrabajoB);
        createGauge('gaugeDimDemandasCargaMentalB', 'needleDimDemandasCargaMentalB', <?= $globalDataFormaB['dim_demandas_carga_mental_promedio'] ?>, baremosDimDemandasCargaMentalB);
        createGauge('gaugeDimDemandasJornadaB', 'needleDimDemandasJornadaB', <?= $globalDataFormaB['dim_demandas_jornada_promedio'] ?>, baremosDimDemandasJornadaB);
        createGauge('gaugeDimRecompensasPertenenciaB', 'needleDimRecompensasPertenenciaB', <?= $globalDataFormaB['dim_recompensas_pertenencia_promedio'] ?>, baremosDimRecompensasPertenenciaB);
        createGauge('gaugeDimReconocimientoCompensacionB', 'needleDimReconocimientoCompensacionB', <?= $globalDataFormaB['dim_reconocimiento_compensacion_promedio'] ?>, baremosDimReconocimientoCompensacionB);
        <?php endif; ?>

        // Dimensiones Extralaborales Forma A
        <?php if ($formaACount > 0): ?>
        createGauge('gaugeDimTiempoFueraTrabajoA', 'needleDimTiempoFueraTrabajoA', <?= $globalDataFormaA['dim_tiempo_fuera_trabajo_promedio'] ?>, baremosDimTiempoFueraTrabajoA);
        createGauge('gaugeDimRelacionesFamiliaresA', 'needleDimRelacionesFamiliaresA', <?= $globalDataFormaA['dim_relaciones_familiares_promedio'] ?>, baremosDimRelacionesFamiliaresA);
        createGauge('gaugeDimComunicacionRelacionesA', 'needleDimComunicacionRelacionesA', <?= $globalDataFormaA['dim_comunicacion_relaciones_promedio'] ?>, baremosDimComunicacionRelacionesA);
        createGauge('gaugeDimSituacionEconomicaA', 'needleDimSituacionEconomicaA', <?= $globalDataFormaA['dim_situacion_economica_promedio'] ?>, baremosDimSituacionEconomicaA);
        createGauge('gaugeDimCaracteristicasViviendaA', 'needleDimCaracteristicasViviendaA', <?= $globalDataFormaA['dim_caracteristicas_vivienda_promedio'] ?>, baremosDimCaracteristicasViviendaA);
        createGauge('gaugeDimInfluenciaEntornoA', 'needleDimInfluenciaEntornoA', <?= $globalDataFormaA['dim_influencia_entorno_promedio'] ?>, baremosDimInfluenciaEntornoA);
        createGauge('gaugeDimDesplazamientoViviendaA', 'needleDimDesplazamientoViviendaA', <?= $globalDataFormaA['dim_desplazamiento_vivienda_promedio'] ?>, baremosDimDesplazamientoViviendaA);
        <?php endif; ?>

        // Dimensiones Extralaborales Forma B
        <?php if ($formaBCount > 0): ?>
        createGauge('gaugeDimTiempoFueraTrabajoB', 'needleDimTiempoFueraTrabajoB', <?= $globalDataFormaB['dim_tiempo_fuera_trabajo_promedio'] ?>, baremosDimTiempoFueraTrabajoB);
        createGauge('gaugeDimRelacionesFamiliaresB', 'needleDimRelacionesFamiliaresB', <?= $globalDataFormaB['dim_relaciones_familiares_promedio'] ?>, baremosDimRelacionesFamiliaresB);
        createGauge('gaugeDimComunicacionRelacionesB', 'needleDimComunicacionRelacionesB', <?= $globalDataFormaB['dim_comunicacion_relaciones_promedio'] ?>, baremosDimComunicacionRelacionesB);
        createGauge('gaugeDimSituacionEconomicaB', 'needleDimSituacionEconomicaB', <?= $globalDataFormaB['dim_situacion_economica_promedio'] ?>, baremosDimSituacionEconomicaB);
        createGauge('gaugeDimCaracteristicasViviendaB', 'needleDimCaracteristicasViviendaB', <?= $globalDataFormaB['dim_caracteristicas_vivienda_promedio'] ?>, baremosDimCaracteristicasViviendaB);
        createGauge('gaugeDimInfluenciaEntornoB', 'needleDimInfluenciaEntornoB', <?= $globalDataFormaB['dim_influencia_entorno_promedio'] ?>, baremosDimInfluenciaEntornoB);
        createGauge('gaugeDimDesplazamientoViviendaB', 'needleDimDesplazamientoViviendaB', <?= $globalDataFormaB['dim_desplazamiento_vivienda_promedio'] ?>, baremosDimDesplazamientoViviendaB);
        <?php endif; ?>
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
