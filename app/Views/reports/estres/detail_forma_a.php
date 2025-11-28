<?php
// Funciones helper para colores y textos
function getNivelEstresColor($nivel) {
    $colores = [
        'muy_bajo' => '#28a745',    // Verde
        'bajo' => '#93C572',        // Verde claro
        'medio' => '#ffc107',       // Amarillo
        'alto' => '#fd7e14',        // Naranja
        'muy_alto' => '#dc3545'     // Rojo
    ];
    return $colores[$nivel] ?? '#6c757d';
}

function getNivelEstresTexto($nivel) {
    $textos = [
        'muy_bajo' => 'MUY BAJO',
        'bajo' => 'BAJO',
        'medio' => 'MEDIO',
        'alto' => 'ALTO',
        'muy_alto' => 'MUY ALTO'
    ];
    return $textos[$nivel] ?? 'N/A';
}
?>
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
        .nivel-box {
            border-radius: 8px;
            padding: 20px;
            color: white;
            text-align: center;
            font-weight: bold;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
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
    <div class="row justify-content-center">
        <div class="col-12">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="no-print">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('battery-services') ?>">Servicios</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('battery-services/' . $service['id']) ?>"><?= esc($service['service_name']) ?></a></li>
                    <li class="breadcrumb-item active">Mapa de Calor Estrés - Forma A</li>
                </ol>
            </nav>

            <!-- Encabezado -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-danger text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1">
                                <i class="fas fa-heartbeat me-2"></i>Mapa de Calor - Estrés Forma A
                            </h4>
                            <p class="mb-0 small">Cuestionario para la Evaluación del Estrés - Jefes, Profesionales y Técnicos</p>
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
                            <p class="mb-1"><i class="fas fa-users text-primary me-2"></i><strong>Total Evaluados (Forma A):</strong> <?= $totalWorkers ?></p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1"><i class="fas fa-clipboard-list text-info me-2"></i><strong>Instrumento:</strong> Estrés (31 preguntas)</p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1"><i class="fas fa-chart-bar text-success me-2"></i><strong>Método:</strong> Promedio aritmético</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MAPA DE CALOR PRINCIPAL -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 text-danger">
                        <i class="fas fa-fire me-2"></i>Nivel de Estrés - Forma A
                    </h5>
                </div>
                <div class="card-body p-0">
                    <!-- Visualización del Total de Estrés -->
                    <div class="d-flex justify-content-center align-items-center p-5" style="min-height: 300px; background-color: <?= getNivelEstresColor($calculations['estres_total']['nivel']) ?>;">
                        <div class="text-center text-white">
                            <div style="font-size: 1.5rem; font-weight: 300; letter-spacing: 2px;">NIVEL DE ESTRÉS TOTAL</div>
                            <div style="font-size: 6rem; font-weight: bold; line-height: 1;"><?= number_format($calculations['estres_total']['promedio'], 1) ?></div>
                            <div style="font-size: 2rem; font-weight: 600; letter-spacing: 1px; margin-top: 10px;">
                                <?= getNivelEstresTexto($calculations['estres_total']['nivel']) ?>
                            </div>
                            <div style="font-size: 1rem; margin-top: 15px; opacity: 0.9;">
                                <i class="fas fa-users me-2"></i><?= $calculations['estres_total']['total_trabajadores'] ?> trabajadores evaluados
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CÁLCULOS DETALLADOS -->
            <div class="row mb-4">
                <div class="col-12">
                    <h5 class="mb-3"><i class="fas fa-calculator me-2"></i>Cálculos Detallados</h5>

                    <!-- Resumen de Resultados -->
                    <div class="card shadow-sm mb-3">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0">Nivel de Estrés Total - Forma A</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 text-center">
                                    <p class="text-muted mb-1"><strong>Puntaje Promedio</strong></p>
                                    <h3 class="text-primary"><?= number_format($calculations['estres_total']['promedio'], 1) ?></h3>
                                </div>
                                <div class="col-md-3 text-center">
                                    <p class="text-muted mb-1"><strong>Nivel de Riesgo</strong></p>
                                    <span class="badge" style="background-color: <?= getNivelEstresColor($calculations['estres_total']['nivel']) ?>; font-size: 1.1rem; padding: 8px 16px;">
                                        <?= getNivelEstresTexto($calculations['estres_total']['nivel']) ?>
                                    </span>
                                </div>
                                <div class="col-md-3 text-center">
                                    <p class="text-muted mb-1"><strong>Trabajadores</strong></p>
                                    <h3 class="text-primary"><?= $calculations['estres_total']['total_trabajadores'] ?></h3>
                                </div>
                                <div class="col-md-3 text-center">
                                    <p class="text-muted mb-1"><strong>Baremo Aplicado</strong></p>
                                    <p class="mb-0 small">Tabla 23<br>Resolución 2404/2019</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Proceso de Transformación -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-secondary text-white">
                            <h6 class="mb-0"><i class="fas fa-function me-2"></i>Paso 2. Transformación de los puntajes brutos</h6>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-3">
                                Con el fin de lograr mejores comparaciones de los puntajes obtenidos en el cuestionario, el siguiente paso consiste en
                                realizar una transformación lineal del puntaje bruto total a una escala de puntajes que van de <strong>0 a 100</strong>.
                                Para realizar esta transformación se utiliza la siguiente fórmula:
                            </p>

                            <div class="alert alert-light border text-center py-4">
                                <div style="font-size: 1.3rem; font-family: 'Courier New', monospace;">
                                    <strong>Puntaje transformado</strong> = ( <strong>Puntaje bruto total</strong> / <strong>61,16</strong> ) × <strong>100</strong>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-md-4">
                                    <div class="alert alert-primary">
                                        <h6 class="mb-2"><i class="fas fa-info-circle me-2"></i>Valor máximo del cuestionario</h6>
                                        <p class="mb-0 small">
                                            El cuestionario de estrés tiene <strong>31 preguntas</strong> con opciones de respuesta del 0 al 6 (Siempre a Nunca).
                                            El puntaje máximo posible es: <strong>31 × 6 = 186</strong>.
                                            Sin embargo, para la transformación se utiliza el factor <strong>61,16</strong> según la normatividad.
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="alert alert-info">
                                        <h6 class="mb-2"><i class="fas fa-tally me-2"></i>Puntaje bruto promedio</h6>
                                        <p class="mb-1 small">El puntaje bruto total promedio (suma de respuestas) es:</p>
                                        <p class="mb-2 text-center" style="font-family: 'Courier New', monospace; font-size: 1.5rem;">
                                            <strong><?= number_format($calculations['estres_total']['puntaje_bruto_promedio'], 1) ?></strong>
                                        </p>
                                        <p class="mb-0 small">Este es el promedio de la suma directa de las respuestas de los <strong><?= $calculations['estres_total']['total_trabajadores'] ?> trabajadores</strong> antes de aplicar la transformación.</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="alert alert-success">
                                        <h6 class="mb-2"><i class="fas fa-calculator me-2"></i>Puntaje transformado promedio</h6>
                                        <p class="mb-1 small">El puntaje transformado promedio de estrés para este servicio es:</p>
                                        <p class="mb-2 text-center" style="font-family: 'Courier New', monospace; font-size: 1.5rem;">
                                            <strong><?= number_format($calculations['estres_total']['promedio'], 1) ?></strong>
                                        </p>
                                        <p class="mb-0 small">Este puntaje se compara con el baremo (Tabla 23) para determinar el nivel de estrés.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-warning mt-3 mb-0">
                                <h6 class="mb-2"><i class="fas fa-chart-line me-2"></i>Método de promedio aritmético</h6>
                                <p class="mb-0 small">
                                    El puntaje mostrado en este mapa de calor (<strong><?= number_format($calculations['estres_total']['promedio'], 1) ?></strong>)
                                    corresponde al <strong>promedio aritmético</strong> de los puntajes transformados de los
                                    <strong><?= $calculations['estres_total']['total_trabajadores'] ?> trabajadores</strong> evaluados con la Forma A.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tarjeta de Cálculo y Baremo -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-table me-2"></i>Baremo Oficial - Forma A
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center">Nivel de Estrés</th>
                                    <th class="text-center">Rango de Puntaje</th>
                                    <th class="text-center" style="width: 100px;">Color</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-center"><strong>Muy Bajo</strong></td>
                                    <td class="text-center">0.0 - 7.8</td>
                                    <td class="text-center"><span class="badge" style="background-color: <?= getNivelEstresColor('muy_bajo') ?>; width: 80px; padding: 8px;">Muy Bajo</span></td>
                                </tr>
                                <tr>
                                    <td class="text-center"><strong>Bajo</strong></td>
                                    <td class="text-center">7.9 - 12.6</td>
                                    <td class="text-center"><span class="badge" style="background-color: <?= getNivelEstresColor('bajo') ?>; width: 80px; padding: 8px;">Bajo</span></td>
                                </tr>
                                <tr>
                                    <td class="text-center"><strong>Medio</strong></td>
                                    <td class="text-center">12.7 - 17.7</td>
                                    <td class="text-center"><span class="badge" style="background-color: <?= getNivelEstresColor('medio') ?>; width: 80px; padding: 8px;">Medio</span></td>
                                </tr>
                                <tr>
                                    <td class="text-center"><strong>Alto</strong></td>
                                    <td class="text-center">17.8 - 25.0</td>
                                    <td class="text-center"><span class="badge" style="background-color: <?= getNivelEstresColor('alto') ?>; width: 80px; padding: 8px;">Alto</span></td>
                                </tr>
                                <tr>
                                    <td class="text-center"><strong>Muy Alto</strong></td>
                                    <td class="text-center">25.1 - 100.0</td>
                                    <td class="text-center"><span class="badge" style="background-color: <?= getNivelEstresColor('muy_alto') ?>; width: 80px; padding: 8px;">Muy Alto</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Definiciones de Niveles -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Interpretación de Niveles de Estrés
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card border-success">
                                <div class="card-header" style="background-color: <?= getNivelEstresColor('muy_bajo') ?>; color: white;">
                                    <strong>Muy Bajo</strong>
                                </div>
                                <div class="card-body">
                                    <p class="card-text small mb-0">
                                        Ausencia de síntomas de estrés o presencia muy baja de los mismos. No se requiere intervención específica.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card border-success">
                                <div class="card-header" style="background-color: <?= getNivelEstresColor('bajo') ?>; color: white;">
                                    <strong>Bajo</strong>
                                </div>
                                <div class="card-body">
                                    <p class="card-text small mb-0">
                                        Presencia baja de síntomas de estrés. Se recomienda mantener acciones de promoción de la salud mental.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card border-warning">
                                <div class="card-header" style="background-color: <?= getNivelEstresColor('medio') ?>; color: white;">
                                    <strong>Medio</strong>
                                </div>
                                <div class="card-body">
                                    <p class="card-text small mb-0">
                                        Presencia moderada de síntomas de estrés. Se requieren acciones de intervención para prevenir el aumento de síntomas.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card border-danger">
                                <div class="card-header" style="background-color: <?= getNivelEstresColor('alto') ?>; color: white;">
                                    <strong>Alto</strong>
                                </div>
                                <div class="card-body">
                                    <p class="card-text small mb-0">
                                        Alta presencia de síntomas de estrés. Se requieren acciones de intervención inmediatas en el marco de un Sistema de Vigilancia Epidemiológica.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <div class="card border-danger">
                                <div class="card-header" style="background-color: <?= getNivelEstresColor('muy_alto') ?>; color: white;">
                                    <strong>Muy Alto</strong>
                                </div>
                                <div class="card-body">
                                    <p class="card-text small mb-0">
                                        Presencia muy alta de síntomas de estrés. Se requiere intervención inmediata en el marco de un Sistema de Vigilancia Epidemiológica y valoración clínica de los individuos afectados.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Nota Metodológica -->
            <div class="alert alert-info">
                <h6 class="alert-heading">
                    <i class="fas fa-lightbulb me-2"></i>Nota Metodológica
                </h6>
                <ul class="mb-0 small">
                    <li><strong>Instrumento:</strong> Cuestionario para la evaluación del estrés - Forma A (31 preguntas)</li>
                    <li><strong>Aplicación:</strong> Jefes, profesionales, técnicos (mismo cuestionario que Forma B, pero con baremos diferentes)</li>
                    <li><strong>Baremo:</strong> Tabla 23 de la Resolución 2404 de 2019</li>
                    <li><strong>Cálculo:</strong> Promedio aritmético de los puntajes transformados de todos los trabajadores evaluados con Forma A</li>
                    <li><strong>Interpretación:</strong> Los niveles se interpretan de acuerdo a los rangos establecidos en la normatividad colombiana</li>
                </ul>
            </div>

            <!-- Botones de navegación -->
            <div class="d-flex justify-content-between mb-4 no-print">
                <a href="<?= base_url('battery-services/' . $service['id']) ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Volver al Servicio
                </a>
                <div>
                    <a href="<?= base_url('reports/estres-b/' . $service['id']) ?>" class="btn btn-outline-primary me-2">
                        <i class="fas fa-exchange-alt me-2"></i>Ver Forma B
                    </a>
                    <a href="<?= base_url('workers/service/' . $service['id']) ?>" class="btn btn-primary">
                        <i class="fas fa-users me-2"></i>Ver Trabajadores
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
