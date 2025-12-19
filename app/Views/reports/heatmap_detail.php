<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .heatmap-container {
            border: 2px solid #000;
            background: white;
            margin-bottom: 30px;
        }
        .heatmap-legend {
            padding: 12px;
            background: white;
            border-bottom: 2px solid #000;
            text-align: center;
        }
        .legend-item {
            display: inline-block;
            margin: 0 15px;
            font-size: 13px;
        }
        .legend-dot {
            display: inline-block;
            width: 15px;
            height: 15px;
            border-radius: 50%;
            margin-right: 6px;
            vertical-align: middle;
        }

        /* INTRALABORAL */
        .heatmap-intralaboral {
            display: flex;
            border-bottom: 2px solid #000;
        }
        .heatmap-total-left {
            flex: 0 0 20%;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 12px;
            text-align: center;
            border-right: 2px solid #000;
        }
        .heatmap-domains-dimensions {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .domain-row {
            display: flex;
            border-bottom: 1px solid #666;
        }
        .domain-row:last-child {
            border-bottom: none;
        }
        .domain-cell {
            flex: 0 0 30%;
            padding: 10px;
            font-weight: bold;
            font-size: 11px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border-right: 2px solid #000;
        }
        .dimensions-cell {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .heatmap-dimension {
            flex: 1;
            padding: 8px 12px;
            border-bottom: 1px solid #999;
            font-size: 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        .heatmap-dimension .dimension-score {
            margin-top: 4px;
            font-size: 11px;
        }
        /* Texto blanco en fondo rojo - aplicar a todos los elementos hijos */
        .bg-danger,
        .bg-danger span,
        .bg-danger strong,
        .bg-danger .dimension-score,
        .bg-danger .dimension-score strong {
            color: white !important;
        }
        .heatmap-dimension:last-child {
            border-bottom: none;
        }

        /* EXTRALABORAL y ESTRÉS */
        .heatmap-row {
            display: flex;
            border-bottom: 2px solid #000;
        }
        .heatmap-row:last-child {
            border-bottom: none;
        }
        .heatmap-total {
            flex: 0 0 50%;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 12px;
            text-align: center;
            border-right: 2px solid #000;
        }
        .heatmap-dimensions-only {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        /* Colores */
        .bg-success-light { background-color: #90EE90 !important; }
        .bg-warning { background-color: #FFFF00 !important; color: #000 !important; }
        .bg-danger { background-color: #FF4444 !important; color: white !important; }
        .bg-secondary { background-color: #D3D3D3 !important; }

        /* Calculation details */
        .calc-card {
            border-left: 4px solid #667eea;
            background: #f8f9fa;
        }
        .calc-card.border-danger {
            border-left: 4px solid #dc3545 !important;
            box-shadow: 0 0 10px rgba(220, 53, 69, 0.3) !important;
        }
        .formula-box {
            background: #e7f3ff;
            border-left: 3px solid #0066cc;
            padding: 10px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
        }
        .baremo-table {
            font-size: 12px;
        }
        .baremo-table th {
            background: #667eea;
            color: white;
        }
        .baremo-active {
            background: #fffacd;
            font-weight: bold;
            border: 2px solid #ffd700;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= base_url('battery-services/' . $service['id']) ?>">
                <i class="fas fa-arrow-left me-2"></i>Volver al Servicio
            </a>
            <span class="navbar-text text-white">
                <?= esc($service['company_name']) ?> - <?= esc($service['service_name']) ?>
            </span>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <h2 class="mb-4">
                    <i class="fas fa-th me-2 text-info"></i><?= $title ?>
                    <small class="text-muted">(basado en <?= $totalWorkers ?> trabajadores evaluados)</small>
                </h2>
            </div>
        </div>

        <?php
        // Calcular nivel de riesgo intralaboral maximo
        // El heatmap usa intralaboral_total que ya combina todas las formas
        $maxRiskLevel = $heatmapCalculations['intralaboral_total']['nivel'] ?? 'sin_riesgo';

        $riskOrder = [
            'sin_riesgo' => 0,
            'riesgo_bajo' => 1,
            'riesgo_medio' => 2,
            'riesgo_alto' => 3,
            'riesgo_muy_alto' => 4
        ];

        // Para mostrar desglose por forma, necesitamos calcular desde $results
        $formaARisk = null;
        $formaBRisk = null;
        $formaAScores = [];
        $formaBScores = [];

        foreach ($results as $result) {
            if ($result['intralaboral_form_type'] == 'A' && $result['intralaboral_total_puntaje'] !== null) {
                $formaAScores[] = $result['intralaboral_total_puntaje'];
            }
            if ($result['intralaboral_form_type'] == 'B' && $result['intralaboral_total_puntaje'] !== null) {
                $formaBScores[] = $result['intralaboral_total_puntaje'];
            }
        }

        // Calcular promedios y niveles para cada forma
        if (!empty($formaAScores)) {
            $avgA = array_sum($formaAScores) / count($formaAScores);
            // Baremos Forma A (Tabla 33) - Corregidos según auditoría 2025-11-24
            if ($avgA <= 19.7) $formaARisk = 'sin_riesgo';
            elseif ($avgA <= 25.8) $formaARisk = 'riesgo_bajo';
            elseif ($avgA <= 31.5) $formaARisk = 'riesgo_medio';
            elseif ($avgA <= 38.0) $formaARisk = 'riesgo_alto';
            else $formaARisk = 'riesgo_muy_alto';
        }

        if (!empty($formaBScores)) {
            $avgB = array_sum($formaBScores) / count($formaBScores);
            // Baremos Forma B (Tabla 33) - Corregidos según auditoría 2025-11-24
            if ($avgB <= 20.6) $formaBRisk = 'sin_riesgo';
            elseif ($avgB <= 26.0) $formaBRisk = 'riesgo_bajo';
            elseif ($avgB <= 31.2) $formaBRisk = 'riesgo_medio';
            elseif ($avgB <= 38.7) $formaBRisk = 'riesgo_alto';
            else $formaBRisk = 'riesgo_muy_alto';
        }

        // El nivel maximo es el que determina la periodicidad
        $formaAOrder = isset($formaARisk) ? ($riskOrder[$formaARisk] ?? 0) : 0;
        $formaBOrder = isset($formaBRisk) ? ($riskOrder[$formaBRisk] ?? 0) : 0;
        $maxRiskOrder = max($formaAOrder, $formaBOrder);

        // Si hay al menos una forma evaluada, usar el maximo
        if ($maxRiskOrder > 0) {
            $maxRiskLevel = array_search($maxRiskOrder, $riskOrder);
        }

        // Determinar periodicidad segun normativa
        $periodicidad = in_array($maxRiskLevel, ['riesgo_alto', 'riesgo_muy_alto']) ? 1 : 2;
        $periodicidadTexto = $periodicidad == 1 ? 'ANUAL (1 año)' : 'CADA 2 AÑOS';

        $riskLevelTextos = [
            'sin_riesgo' => 'Sin Riesgo',
            'riesgo_bajo' => 'Riesgo Bajo',
            'riesgo_medio' => 'Riesgo Medio',
            'riesgo_alto' => 'Riesgo Alto',
            'riesgo_muy_alto' => 'Riesgo Muy Alto'
        ];
        $riskLevelTexto = $riskLevelTextos[$maxRiskLevel] ?? 'Sin Riesgo';

        // Colores de alerta
        $alertClass = in_array($maxRiskLevel, ['riesgo_alto', 'riesgo_muy_alto']) ? 'alert-danger' : 'alert-info';
        $iconClass = in_array($maxRiskLevel, ['riesgo_alto', 'riesgo_muy_alto']) ? 'fa-exclamation-triangle' : 'fa-info-circle';
        ?>

        <!-- ALERTA NORMATIVA - RESOLUCION 2764/2022 -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert <?= $alertClass ?> border-<?= str_replace('alert-', '', $alertClass) ?>" role="alert">
                    <h4 class="alert-heading">
                        <i class="fas <?= $iconClass ?> me-2"></i>Periodicidad de Evaluacion - Resolucion 2764 de 2022
                    </h4>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Nivel de Riesgo Intralaboral Detectado:</strong></p>
                            <h5 class="mb-3">
                                <span class="badge bg-<?= str_replace('alert-', '', $alertClass) ?>"><?= $riskLevelTexto ?></span>
                            </h5>
                            <?php if (!empty($formaARisk) && $formaARisk != 'sin_riesgo'): ?>
                            <p class="mb-1"><small>Forma A (Jefes/Profesionales): <?= $riskLevelTextos[$formaARisk] ?? 'N/A' ?></small></p>
                            <?php endif; ?>
                            <?php if (!empty($formaBRisk) && $formaBRisk != 'sin_riesgo'): ?>
                            <p class="mb-1"><small>Forma B (Auxiliares/Operarios): <?= $riskLevelTextos[$formaBRisk] ?? 'N/A' ?></small></p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Proxima Evaluacion Requerida:</strong></p>
                            <h5 class="mb-3">
                                <span class="badge bg-dark"><?= $periodicidadTexto ?></span>
                            </h5>
                            <p class="mb-0"><small><strong>Nota:</strong> La periodicidad se cuenta desde el <strong>inicio de las acciones de intervencion</strong>, no desde la aplicacion de la bateria.</small></p>
                        </div>
                    </div>
                    <hr>
                    <p class="mb-0">
                        <i class="fas fa-gavel me-2"></i>
                        <strong>Marco Legal:</strong> Segun la Resolucion 2764 de 2022 del Ministerio del Trabajo:
                    </p>
                    <ul class="mb-0 mt-2">
                        <li><strong>Evaluacion Anual:</strong> Cuando el riesgo intralaboral es <strong>Alto</strong> o <strong>Muy Alto</strong></li>
                        <li><strong>Evaluacion cada 2 años:</strong> Cuando el riesgo es <strong>Medio, Bajo o Sin Riesgo</strong></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- HEATMAP VISUAL -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-chart-area me-2"></i>Visualización Global</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php
                        function getColorClass($nivel) {
                            $colorMap = [
                                'sin_riesgo' => 'bg-success-light',
                                'riesgo_bajo' => 'bg-success-light',
                                'riesgo_medio' => 'bg-warning',
                                'riesgo_alto' => 'bg-danger',
                                'riesgo_muy_alto' => 'bg-danger',
                                'muy_bajo' => 'bg-success-light',
                                'bajo' => 'bg-success-light',
                                'medio' => 'bg-warning',
                                'alto' => 'bg-danger',
                                'muy_alto' => 'bg-danger'
                            ];
                            return $colorMap[$nivel] ?? 'bg-secondary';
                        }

                        /**
                         * Formatea el puntaje mostrando forma de origen y comparativo
                         * @param array $data Datos del cálculo con forma_origen, data_a, data_b, solo_una_forma
                         * @param bool $showComparative Si mostrar el valor comparativo de la otra forma
                         * @return string HTML formateado
                         */
                        function formatWorstScore($data, $showComparative = true) {
                            if (empty($data) || !isset($data['promedio'])) {
                                return 'N/D';
                            }

                            $promedio = $data['promedio'];
                            $formaOrigen = $data['forma_origen'] ?? null;
                            $soloUnaForma = $data['solo_una_forma'] ?? true;

                            // Si solo hay una forma, mostrar solo el número
                            if ($soloUnaForma || !$showComparative) {
                                return $promedio;
                            }

                            // Hay ambas formas: mostrar origen y comparativo
                            $html = $promedio . ' <span style="font-size: 0.8em;">(' . $formaOrigen . ')</span>';

                            // Agregar comparativo de la otra forma
                            $otraForma = $formaOrigen === 'A' ? 'B' : 'A';
                            $dataOtra = $formaOrigen === 'A' ? ($data['data_b'] ?? null) : ($data['data_a'] ?? null);

                            if ($dataOtra && isset($dataOtra['promedio'])) {
                                $html .= '<br><span style="font-size: 0.7em; color: #666;">' . $otraForma . ': ' . $dataOtra['promedio'] . '</span>';
                            }

                            return $html;
                        }

                        // Verificar si hay ambas formas para mostrar comparativos
                        $hasBothForms = $heatmapCalculations['has_both_forms'] ?? false;
                        ?>

                        <div class="heatmap-container">
                            <!-- Leyenda -->
                            <div class="heatmap-legend">
                                <span class="legend-item">
                                    <span class="legend-dot" style="background-color: #90EE90;"></span>
                                    Riesgo bajo y sin riesgo
                                </span>
                                <span class="legend-item">
                                    <span class="legend-dot" style="background-color: #FFFF00;"></span>
                                    Riesgo medio
                                </span>
                                <span class="legend-item">
                                    <span class="legend-dot" style="background-color: #FF4444;"></span>
                                    Riesgo alto y muy alto
                                </span>
                            </div>
                            <!-- Info de formas evaluadas -->
                            <div style="background: #f8f9fa; padding: 8px 12px; border-bottom: 1px solid #dee2e6; font-size: 11px;">
                                <strong>Mapa de Máximo Riesgo:</strong>
                                <?php if ($heatmapCalculations['has_both_forms'] ?? false): ?>
                                    Muestra el peor resultado entre Forma A (n=<?= $heatmapCalculations['count_a'] ?>) y Forma B (n=<?= $heatmapCalculations['count_b'] ?>).
                                    <span style="color: #666;">La forma de origen se indica entre paréntesis.</span>
                                <?php elseif ($heatmapCalculations['has_forma_a'] ?? false): ?>
                                    Solo Forma A evaluada (n=<?= $heatmapCalculations['count_a'] ?> trabajadores - Jefes/Profesionales/Técnicos)
                                <?php elseif ($heatmapCalculations['has_forma_b'] ?? false): ?>
                                    Solo Forma B evaluada (n=<?= $heatmapCalculations['count_b'] ?> trabajadores - Auxiliares/Operarios)
                                <?php endif; ?>
                            </div>

                            <!-- INTRALABORAL -->
                            <div class="heatmap-intralaboral">
                                <div class="heatmap-total-left <?= getColorClass($heatmapCalculations['intralaboral_total']['nivel']) ?>">
                                    TOTAL GENERAL FACTORES DE RIESGO PSICOSOCIAL INTRALABORAL
                                    <br><strong style="font-size: 14px;"><?= formatWorstScore($heatmapCalculations['intralaboral_total']) ?></strong>
                                </div>
                                <div class="heatmap-domains-dimensions">
                                    <div class="domain-row">
                                        <div class="domain-cell <?= getColorClass($heatmapCalculations['dom_liderazgo']['nivel']) ?>">
                                            LIDERAZGO Y RELACIONES SOCIALES EN EL TRABAJO
                                            <br><strong><?= formatWorstScore($heatmapCalculations['dom_liderazgo']) ?></strong>
                                        </div>
                                        <div class="dimensions-cell">
                                            <div class="heatmap-dimension <?= getColorClass($heatmapCalculations['dim_caracteristicas_liderazgo']['nivel']) ?>"><span>Características del liderazgo</span><span class="dimension-score"><strong>(<?= formatWorstScore($heatmapCalculations['dim_caracteristicas_liderazgo']) ?>)</strong></span></div>
                                            <div class="heatmap-dimension <?= getColorClass($heatmapCalculations['dim_relaciones_sociales']['nivel']) ?>"><span>Relaciones sociales en el trabajo</span><span class="dimension-score"><strong>(<?= formatWorstScore($heatmapCalculations['dim_relaciones_sociales']) ?>)</strong></span></div>
                                            <div class="heatmap-dimension <?= getColorClass($heatmapCalculations['dim_retroalimentacion']['nivel']) ?>"><span>Retroalimentación del desempeño</span><span class="dimension-score"><strong>(<?= formatWorstScore($heatmapCalculations['dim_retroalimentacion']) ?>)</strong></span></div>
                                            <?php if (isset($heatmapCalculations['dim_relacion_colaboradores'])): ?>
                                            <div class="heatmap-dimension <?= getColorClass($heatmapCalculations['dim_relacion_colaboradores']['nivel']) ?>"><span>Relación con los colaboradores</span><span class="dimension-score"><strong>(<?= formatWorstScore($heatmapCalculations['dim_relacion_colaboradores']) ?>)</strong></span></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="domain-row">
                                        <div class="domain-cell <?= getColorClass($heatmapCalculations['dom_control']['nivel']) ?>">
                                            CONTROL SOBRE EL TRABAJO
                                            <br><strong><?= formatWorstScore($heatmapCalculations['dom_control']) ?></strong>
                                        </div>
                                        <div class="dimensions-cell">
                                            <div class="heatmap-dimension <?= getColorClass($heatmapCalculations['dim_claridad_rol']['nivel']) ?>"><span>Claridad de rol</span><span class="dimension-score"><strong>(<?= formatWorstScore($heatmapCalculations['dim_claridad_rol']) ?>)</strong></span></div>
                                            <div class="heatmap-dimension <?= getColorClass($heatmapCalculations['dim_capacitacion']['nivel']) ?>"><span>Capacitación</span><span class="dimension-score"><strong>(<?= formatWorstScore($heatmapCalculations['dim_capacitacion']) ?>)</strong></span></div>
                                            <div class="heatmap-dimension <?= getColorClass($heatmapCalculations['dim_participacion_manejo_cambio']['nivel']) ?>"><span>Participación y manejo del cambio</span><span class="dimension-score"><strong>(<?= formatWorstScore($heatmapCalculations['dim_participacion_manejo_cambio']) ?>)</strong></span></div>
                                            <div class="heatmap-dimension <?= getColorClass($heatmapCalculations['dim_oportunidades_desarrollo']['nivel']) ?>"><span>Oportunidades desarrollo</span><span class="dimension-score"><strong>(<?= formatWorstScore($heatmapCalculations['dim_oportunidades_desarrollo']) ?>)</strong></span></div>
                                            <div class="heatmap-dimension <?= getColorClass($heatmapCalculations['dim_control_autonomia']['nivel']) ?>"><span>Control y autonomía</span><span class="dimension-score"><strong>(<?= formatWorstScore($heatmapCalculations['dim_control_autonomia']) ?>)</strong></span></div>
                                        </div>
                                    </div>
                                    <div class="domain-row">
                                        <div class="domain-cell <?= getColorClass($heatmapCalculations['dom_demandas']['nivel']) ?>">
                                            DEMANDAS DEL TRABAJO
                                            <br><strong><?= formatWorstScore($heatmapCalculations['dom_demandas']) ?></strong>
                                        </div>
                                        <div class="dimensions-cell">
                                            <div class="heatmap-dimension <?= getColorClass($heatmapCalculations['dim_demandas_ambientales']['nivel']) ?>"><span>Demandas ambientales</span><span class="dimension-score"><strong>(<?= formatWorstScore($heatmapCalculations['dim_demandas_ambientales']) ?>)</strong></span></div>
                                            <div class="heatmap-dimension <?= getColorClass($heatmapCalculations['dim_demandas_emocionales']['nivel']) ?>"><span>Demandas emocionales</span><span class="dimension-score"><strong>(<?= formatWorstScore($heatmapCalculations['dim_demandas_emocionales']) ?>)</strong></span></div>
                                            <div class="heatmap-dimension <?= getColorClass($heatmapCalculations['dim_demandas_cuantitativas']['nivel']) ?>"><span>Demandas cuantitativas</span><span class="dimension-score"><strong>(<?= formatWorstScore($heatmapCalculations['dim_demandas_cuantitativas']) ?>)</strong></span></div>
                                            <div class="heatmap-dimension <?= getColorClass($heatmapCalculations['dim_influencia_trabajo_entorno_extralaboral']['nivel']) ?>"><span>Influencia trabajo-extralaboral</span><span class="dimension-score"><strong>(<?= formatWorstScore($heatmapCalculations['dim_influencia_trabajo_entorno_extralaboral']) ?>)</strong></span></div>
                                            <?php if (isset($heatmapCalculations['dim_demandas_responsabilidad'])): ?>
                                            <div class="heatmap-dimension <?= getColorClass($heatmapCalculations['dim_demandas_responsabilidad']['nivel']) ?>"><span>Exigencias responsabilidad</span><span class="dimension-score"><strong>(<?= formatWorstScore($heatmapCalculations['dim_demandas_responsabilidad']) ?>)</strong></span></div>
                                            <?php endif; ?>
                                            <div class="heatmap-dimension <?= getColorClass($heatmapCalculations['dim_carga_mental']['nivel']) ?>"><span>Demandas carga mental</span><span class="dimension-score"><strong>(<?= formatWorstScore($heatmapCalculations['dim_carga_mental']) ?>)</strong></span></div>
                                            <?php if (isset($heatmapCalculations['dim_consistencia_rol'])): ?>
                                            <div class="heatmap-dimension <?= getColorClass($heatmapCalculations['dim_consistencia_rol']['nivel']) ?>"><span>Consistencia del rol</span><span class="dimension-score"><strong>(<?= formatWorstScore($heatmapCalculations['dim_consistencia_rol']) ?>)</strong></span></div>
                                            <?php endif; ?>
                                            <div class="heatmap-dimension <?= getColorClass($heatmapCalculations['dim_demandas_jornada_trabajo']['nivel']) ?>"><span>Demandas jornada trabajo</span><span class="dimension-score"><strong>(<?= formatWorstScore($heatmapCalculations['dim_demandas_jornada_trabajo']) ?>)</strong></span></div>
                                        </div>
                                    </div>
                                    <div class="domain-row">
                                        <div class="domain-cell <?= getColorClass($heatmapCalculations['dom_recompensas']['nivel']) ?>">
                                            RECOMPENSAS
                                            <br><strong><?= formatWorstScore($heatmapCalculations['dom_recompensas']) ?></strong>
                                        </div>
                                        <div class="dimensions-cell">
                                            <div class="heatmap-dimension <?= getColorClass($heatmapCalculations['dim_recompensas_pertenencia']['nivel']) ?>"><span>Recompensas pertenencia</span><span class="dimension-score"><strong>(<?= formatWorstScore($heatmapCalculations['dim_recompensas_pertenencia']) ?>)</strong></span></div>
                                            <div class="heatmap-dimension <?= getColorClass($heatmapCalculations['dim_reconocimiento_compensacion']['nivel']) ?>"><span>Reconocimiento y compensación</span><span class="dimension-score"><strong>(<?= formatWorstScore($heatmapCalculations['dim_reconocimiento_compensacion']) ?>)</strong></span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- EXTRALABORAL -->
                            <div class="heatmap-row">
                                <div class="heatmap-total <?= getColorClass($heatmapCalculations['extralaboral_total']['nivel']) ?>">
                                    FACTORES EXTRALABORALES
                                    <br><strong style="font-size: 14px;"><?= formatWorstScore($heatmapCalculations['extralaboral_total']) ?></strong>
                                </div>
                                <div class="heatmap-dimensions-only">
                                    <div class="heatmap-dimension <?= getColorClass($heatmapCalculations['dim_tiempo_fuera']['nivel']) ?>"><span>Tiempo fuera del trabajo</span><span class="dimension-score"><strong>(<?= formatWorstScore($heatmapCalculations['dim_tiempo_fuera']) ?>)</strong></span></div>
                                    <div class="heatmap-dimension <?= getColorClass($heatmapCalculations['dim_relaciones_familiares_extra']['nivel']) ?>"><span>Relaciones familiares</span><span class="dimension-score"><strong>(<?= formatWorstScore($heatmapCalculations['dim_relaciones_familiares_extra']) ?>)</strong></span></div>
                                    <div class="heatmap-dimension <?= getColorClass($heatmapCalculations['dim_comunicacion']['nivel']) ?>"><span>Comunicación interpersonal</span><span class="dimension-score"><strong>(<?= formatWorstScore($heatmapCalculations['dim_comunicacion']) ?>)</strong></span></div>
                                    <div class="heatmap-dimension <?= getColorClass($heatmapCalculations['dim_situacion_economica']['nivel']) ?>"><span>Situación económica familiar</span><span class="dimension-score"><strong>(<?= formatWorstScore($heatmapCalculations['dim_situacion_economica']) ?>)</strong></span></div>
                                    <div class="heatmap-dimension <?= getColorClass($heatmapCalculations['dim_caracteristicas_vivienda']['nivel']) ?>"><span>Características vivienda</span><span class="dimension-score"><strong>(<?= formatWorstScore($heatmapCalculations['dim_caracteristicas_vivienda']) ?>)</strong></span></div>
                                    <div class="heatmap-dimension <?= getColorClass($heatmapCalculations['dim_influencia_entorno_extra']['nivel']) ?>"><span>Influencia entorno extralaboral</span><span class="dimension-score"><strong>(<?= formatWorstScore($heatmapCalculations['dim_influencia_entorno_extra']) ?>)</strong></span></div>
                                    <div class="heatmap-dimension <?= getColorClass($heatmapCalculations['dim_desplazamiento']['nivel']) ?>"><span>Desplazamiento vivienda-trabajo</span><span class="dimension-score"><strong>(<?= formatWorstScore($heatmapCalculations['dim_desplazamiento']) ?>)</strong></span></div>
                                </div>
                            </div>

                            <!-- ESTRÉS -->
                            <div class="heatmap-row">
                                <div class="heatmap-total <?= getColorClass($heatmapCalculations['estres_total']['nivel']) ?>" style="flex: 1; border-right: none;">
                                    SÍNTOMAS DE ESTRÉS
                                    <br><strong style="font-size: 14px;"><?= formatWorstScore($heatmapCalculations['estres_total']) ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- DETALLES DE CÁLCULOS -->
        <div class="row">
            <div class="col-12">
                <h3 class="mb-3"><i class="fas fa-calculator me-2 text-primary"></i>Metodología de Cálculo y Baremos Aplicados</h3>
            </div>
        </div>

        <?php
        function renderCalculationDetail($title, $data, $icon, $color) {
            // Detectar si es riesgo alto o muy alto para resaltar con borde rojo
            $isHighRisk = in_array($data['nivel'] ?? '', ['riesgo_alto', 'riesgo_muy_alto', 'alto', 'muy_alto']);
            $borderClass = $isHighRisk ? 'border-danger border-3' : '';
            $headerBg = $isHighRisk ? 'danger' : $color;
            ?>
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm calc-card <?= $borderClass ?>">
                    <div class="card-header bg-<?= $headerBg ?> text-white">
                        <h5 class="mb-0">
                            <?php if ($isHighRisk): ?><i class="fas fa-exclamation-triangle me-2"></i><?php endif; ?>
                            <i class="fas fa-<?= $icon ?> me-2"></i><?= $title ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Resultado -->
                        <div class="alert alert-info mb-3">
                            <strong>Nivel de Riesgo:</strong>
                            <span class="badge bg-<?= getColorClass($data['nivel']) === 'bg-danger' ? 'danger' : (getColorClass($data['nivel']) === 'bg-warning' ? 'warning text-dark' : 'success') ?> ms-2">
                                <?= strtoupper(str_replace('_', ' ', $data['nivel'])) ?>
                            </span>
                        </div>

                        <!-- Fórmula -->
                        <h6><i class="fas fa-flask me-2"></i>Método de Cálculo:</h6>
                        <div class="formula-box mb-3">
                            <strong>1. Suma de puntajes:</strong> <?= number_format($data['suma'], 2) ?><br>
                            <strong>2. Cantidad de trabajadores:</strong> <?= $data['cantidad'] ?><br>
                            <strong>3. Promedio aritmético:</strong> <?= $data['suma'] ?> ÷ <?= $data['cantidad'] ?> = <strong><?= $data['promedio'] ?></strong>
                        </div>

                        <!-- Baremo aplicado -->
                        <h6><i class="fas fa-table me-2"></i>Baremo Aplicado (Resolución 2404/2019):</h6>
                        <table class="table table-sm table-bordered baremo-table">
                            <thead>
                                <tr>
                                    <th>Nivel de Riesgo</th>
                                    <th>Rango</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['baremo'] as $nivel => $rango): ?>
                                <tr class="<?= $nivel === $data['nivel'] ? 'baremo-active' : '' ?>">
                                    <td><?= ucfirst(str_replace('_', ' ', $nivel)) ?></td>
                                    <td><?= $rango[0] ?> - <?= $rango[1] ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <!-- Rango aplicado -->
                        <div class="alert alert-warning mb-0">
                            <strong><i class="fas fa-check-circle me-2"></i>El promedio <?= $data['promedio'] ?> se encuentra en el rango:</strong>
                            [<?= $data['rango_aplicado'][0] ?> - <?= $data['rango_aplicado'][1] ?>]
                            = <strong><?= strtoupper(str_replace('_', ' ', $data['nivel'])) ?></strong>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
        ?>

        <div class="row">
            <!-- INTRALABORAL TOTAL -->
            <?php renderCalculationDetail(
                'Total Intralaboral (Forma ' . $heatmapCalculations['forma_type'] . ')',
                $heatmapCalculations['intralaboral_total'],
                'briefcase',
                'primary'
            ); ?>

            <!-- DOMINIOS -->
            <?php renderCalculationDetail(
                'Dominio: Liderazgo y Relaciones Sociales',
                $heatmapCalculations['dom_liderazgo'],
                'users-cog',
                'info'
            ); ?>

            <?php renderCalculationDetail(
                'Dominio: Control sobre el Trabajo',
                $heatmapCalculations['dom_control'],
                'sliders-h',
                'success'
            ); ?>

            <?php renderCalculationDetail(
                'Dominio: Demandas del Trabajo',
                $heatmapCalculations['dom_demandas'],
                'tasks',
                'warning'
            ); ?>

            <?php renderCalculationDetail(
                'Dominio: Recompensas',
                $heatmapCalculations['dom_recompensas'],
                'award',
                'secondary'
            ); ?>

            <!-- EXTRALABORAL -->
            <?php renderCalculationDetail(
                'Total Extralaboral',
                $heatmapCalculations['extralaboral_total'],
                'home',
                'success'
            ); ?>

            <!-- ESTRÉS -->
            <?php renderCalculationDetail(
                'Síntomas de Estrés',
                $heatmapCalculations['estres_total'],
                'heartbeat',
                'danger'
            ); ?>
        </div>

        <!-- DIMENSIONES INTRALABORALES -->
        <div class="row">
            <div class="col-12">
                <h4 class="mb-3 mt-4"><i class="fas fa-layer-group me-2 text-info"></i>Dimensiones Intralaborales</h4>
            </div>
        </div>
        <div class="row">
            <!-- Dominio Liderazgo -->
            <?php if (isset($heatmapCalculations['dim_caracteristicas_liderazgo'])): ?>
            <?php renderCalculationDetail('Características del liderazgo', $heatmapCalculations['dim_caracteristicas_liderazgo'], 'user-tie', 'info'); ?>
            <?php endif; ?>
            <?php if (isset($heatmapCalculations['dim_relaciones_sociales'])): ?>
            <?php renderCalculationDetail('Relaciones sociales en el trabajo', $heatmapCalculations['dim_relaciones_sociales'], 'users', 'info'); ?>
            <?php endif; ?>
            <?php if (isset($heatmapCalculations['dim_retroalimentacion'])): ?>
            <?php renderCalculationDetail('Retroalimentación del desempeño', $heatmapCalculations['dim_retroalimentacion'], 'comments', 'info'); ?>
            <?php endif; ?>
            <?php if (isset($heatmapCalculations['dim_relacion_colaboradores'])): ?>
            <?php renderCalculationDetail('Relación con los colaboradores', $heatmapCalculations['dim_relacion_colaboradores'], 'handshake', 'info'); ?>
            <?php endif; ?>

            <!-- Dominio Control -->
            <?php if (isset($heatmapCalculations['dim_claridad_rol'])): ?>
            <?php renderCalculationDetail('Claridad de rol', $heatmapCalculations['dim_claridad_rol'], 'bullseye', 'success'); ?>
            <?php endif; ?>
            <?php if (isset($heatmapCalculations['dim_capacitacion'])): ?>
            <?php renderCalculationDetail('Capacitación', $heatmapCalculations['dim_capacitacion'], 'graduation-cap', 'success'); ?>
            <?php endif; ?>
            <?php if (isset($heatmapCalculations['dim_participacion_manejo_cambio'])): ?>
            <?php renderCalculationDetail('Participación y manejo del cambio', $heatmapCalculations['dim_participacion_manejo_cambio'], 'sync-alt', 'success'); ?>
            <?php endif; ?>
            <?php if (isset($heatmapCalculations['dim_oportunidades_desarrollo'])): ?>
            <?php renderCalculationDetail('Oportunidades de desarrollo', $heatmapCalculations['dim_oportunidades_desarrollo'], 'chart-line', 'success'); ?>
            <?php endif; ?>
            <?php if (isset($heatmapCalculations['dim_control_autonomia'])): ?>
            <?php renderCalculationDetail('Control y autonomía', $heatmapCalculations['dim_control_autonomia'], 'sliders-h', 'success'); ?>
            <?php endif; ?>

            <!-- Dominio Demandas -->
            <?php if (isset($heatmapCalculations['dim_demandas_ambientales'])): ?>
            <?php renderCalculationDetail('Demandas ambientales', $heatmapCalculations['dim_demandas_ambientales'], 'building', 'warning'); ?>
            <?php endif; ?>
            <?php if (isset($heatmapCalculations['dim_demandas_emocionales'])): ?>
            <?php renderCalculationDetail('Demandas emocionales', $heatmapCalculations['dim_demandas_emocionales'], 'heart', 'warning'); ?>
            <?php endif; ?>
            <?php if (isset($heatmapCalculations['dim_demandas_cuantitativas'])): ?>
            <?php renderCalculationDetail('Demandas cuantitativas', $heatmapCalculations['dim_demandas_cuantitativas'], 'list-ol', 'warning'); ?>
            <?php endif; ?>
            <?php if (isset($heatmapCalculations['dim_influencia_trabajo_entorno_extralaboral'])): ?>
            <?php renderCalculationDetail('Influencia trabajo-extralaboral', $heatmapCalculations['dim_influencia_trabajo_entorno_extralaboral'], 'exchange-alt', 'warning'); ?>
            <?php endif; ?>
            <?php if (isset($heatmapCalculations['dim_demandas_responsabilidad'])): ?>
            <?php renderCalculationDetail('Exigencias de responsabilidad', $heatmapCalculations['dim_demandas_responsabilidad'], 'shield-alt', 'warning'); ?>
            <?php endif; ?>
            <?php if (isset($heatmapCalculations['dim_carga_mental'])): ?>
            <?php renderCalculationDetail('Demandas de carga mental', $heatmapCalculations['dim_carga_mental'], 'brain', 'warning'); ?>
            <?php endif; ?>
            <?php if (isset($heatmapCalculations['dim_consistencia_rol'])): ?>
            <?php renderCalculationDetail('Consistencia del rol', $heatmapCalculations['dim_consistencia_rol'], 'balance-scale', 'warning'); ?>
            <?php endif; ?>
            <?php if (isset($heatmapCalculations['dim_demandas_jornada_trabajo'])): ?>
            <?php renderCalculationDetail('Demandas de la jornada de trabajo', $heatmapCalculations['dim_demandas_jornada_trabajo'], 'clock', 'warning'); ?>
            <?php endif; ?>

            <!-- Dominio Recompensas -->
            <?php if (isset($heatmapCalculations['dim_recompensas_pertenencia'])): ?>
            <?php renderCalculationDetail('Recompensas y pertenencia', $heatmapCalculations['dim_recompensas_pertenencia'], 'gift', 'secondary'); ?>
            <?php endif; ?>
            <?php if (isset($heatmapCalculations['dim_reconocimiento_compensacion'])): ?>
            <?php renderCalculationDetail('Reconocimiento y compensación', $heatmapCalculations['dim_reconocimiento_compensacion'], 'award', 'secondary'); ?>
            <?php endif; ?>
        </div>

        <!-- DIMENSIONES EXTRALABORALES -->
        <div class="row">
            <div class="col-12">
                <h4 class="mb-3 mt-4"><i class="fas fa-home me-2 text-success"></i>Dimensiones Extralaborales</h4>
            </div>
        </div>
        <div class="row">
            <?php if (isset($heatmapCalculations['dim_tiempo_fuera'])): ?>
            <?php renderCalculationDetail('Tiempo fuera del trabajo', $heatmapCalculations['dim_tiempo_fuera'], 'hourglass-half', 'success'); ?>
            <?php endif; ?>
            <?php if (isset($heatmapCalculations['dim_relaciones_familiares_extra'])): ?>
            <?php renderCalculationDetail('Relaciones familiares', $heatmapCalculations['dim_relaciones_familiares_extra'], 'users', 'success'); ?>
            <?php endif; ?>
            <?php if (isset($heatmapCalculations['dim_comunicacion'])): ?>
            <?php renderCalculationDetail('Comunicación interpersonal', $heatmapCalculations['dim_comunicacion'], 'comments', 'success'); ?>
            <?php endif; ?>
            <?php if (isset($heatmapCalculations['dim_situacion_economica'])): ?>
            <?php renderCalculationDetail('Situación económica familiar', $heatmapCalculations['dim_situacion_economica'], 'dollar-sign', 'success'); ?>
            <?php endif; ?>
            <?php if (isset($heatmapCalculations['dim_caracteristicas_vivienda'])): ?>
            <?php renderCalculationDetail('Características de la vivienda', $heatmapCalculations['dim_caracteristicas_vivienda'], 'home', 'success'); ?>
            <?php endif; ?>
            <?php if (isset($heatmapCalculations['dim_influencia_entorno_extra'])): ?>
            <?php renderCalculationDetail('Influencia del entorno extralaboral', $heatmapCalculations['dim_influencia_entorno_extra'], 'globe', 'success'); ?>
            <?php endif; ?>
            <?php if (isset($heatmapCalculations['dim_desplazamiento'])): ?>
            <?php renderCalculationDetail('Desplazamiento vivienda-trabajo', $heatmapCalculations['dim_desplazamiento'], 'car', 'success'); ?>
            <?php endif; ?>
        </div>

        <!-- Nota metodológica -->
        <div class="row mt-4 mb-5">
            <div class="col-12">
                <div class="card border-info">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Nota Metodológica</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-2">
                            <strong>Método de cálculo:</strong> El mapa de calor representa el nivel de riesgo global calculado mediante
                            el <strong>promedio aritmético</strong> de los puntajes transformados de todos los trabajadores evaluados.
                        </p>
                        <p class="mb-2">
                            <strong>Baremos aplicados:</strong> Se utilizan los baremos oficiales de la Resolución 2404 de 2019 del Ministerio
                            de Trabajo de Colombia para clasificar los promedios en niveles de riesgo.
                        </p>
                        <p class="mb-0">
                            <strong>Interpretación de colores:</strong>
                            <span class="badge bg-success ms-2">Verde</span> = Sin riesgo / Riesgo bajo |
                            <span class="badge bg-warning text-dark ms-2">Amarillo</span> = Riesgo medio |
                            <span class="badge bg-danger ms-2">Rojo</span> = Riesgo alto / Muy alto
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
