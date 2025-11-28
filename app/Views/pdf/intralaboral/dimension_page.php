<div class="pdf-page">
    <?= view('pdf/_partials/header', ['company' => $company, 'consultant' => $consultant]) ?>

    <div class="pdf-content">
        <!-- Título de la dimensión -->
        <h1 class="dimension-title">
            Dimensión <?= esc($dimension['nombre']) ?>
        </h1>

        <!-- Caja de definición -->
        <?= view('pdf/_partials/components/definition_box', [
            'label' => 'Definición:',
            'content' => $dimension['definicion']
        ]) ?>

        <!-- Gauges duales (Forma A y B si aplica, o solo la forma actual) -->
        <div class="gauge-dual-container">
            <div class="gauge-item">
                <?= view('pdf/_partials/components/gauge', [
                    'title' => $dimension['nombre'],
                    'value' => $dimension['promedio'],
                    'ranges' => $baremos,
                    'forma' => $forma,
                    'size' => 'medium'
                ]) ?>

                <!-- Interpretación -->
                <div class="interpretation-text" style="font-size: 9pt; margin-top: 10px;">
                    Para el cuestionario Tipo <?= $forma ?> se evidencia que el nivel de riesgo psicosocial se encuentra con un valor de
                    <strong><?= number_format($dimension['promedio'], 2, ',', '.') ?></strong> denominándose
                    <?php
                    $colors = [
                        'sin_riesgo' => '#4CAF50',
                        'riesgo_bajo' => '#8BC34A',
                        'riesgo_medio' => '#FFEB3B',
                        'riesgo_alto' => '#FF9800',
                        'riesgo_muy_alto' => '#F44336',
                    ];
                    $labels = [
                        'sin_riesgo' => 'SIN RIESGO',
                        'riesgo_bajo' => 'RIESGO BAJO',
                        'riesgo_medio' => 'RIESGO MEDIO',
                        'riesgo_alto' => 'RIESGO ALTO',
                        'riesgo_muy_alto' => 'RIESGO MUY ALTO',
                    ];
                    $color = $colors[$dimension['nivel']] ?? '#999';
                    $label = $labels[$dimension['nivel']] ?? 'SIN DATOS';
                    ?>
                    <span style="color: <?= $color ?>; font-weight: bold;"><?= $label ?></span>,
                    por lo que se debe <strong><?= $dimension['accion'] ?></strong>
                    <?php if ($forma === 'A'): ?>
                        las intervenciones que se realicen para los cargos profesionales o de jefatura.
                    <?php else: ?>
                        con acciones que se realicen para los cargos auxiliares u operativos.
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Porcentajes de distribución por Niveles de Riesgo -->
        <h2 class="subsection-title" style="text-align: center; margin-top: 15px;">
            Porcentajes de distribución por Niveles de Riesgo
        </h2>

        <div style="display: flex; justify-content: space-around; align-items: flex-start; margin-top: 10px;">
            <!-- Gráfico de barras -->
            <div style="flex: 1; max-width: 45%;">
                <?php
                $barData = [
                    'FORMA ' . $forma => [
                        'muy_alto' => $dimension['porcentajes']['riesgo_muy_alto'] + $dimension['porcentajes']['riesgo_alto'],
                        'medio' => $dimension['porcentajes']['riesgo_medio'],
                        'sin_riesgo' => $dimension['porcentajes']['sin_riesgo'] + $dimension['porcentajes']['riesgo_bajo'],
                    ]
                ];
                ?>
                <?= view('pdf/_partials/components/bar_chart_stacked', [
                    'data' => $barData,
                    'showLegend' => true,
                    'height' => 150
                ]) ?>
            </div>

            <!-- Iconos y texto de foco -->
            <div style="flex: 1; max-width: 50%; padding-left: 20px;">
                <!-- Iconos de riesgo -->
                <?= view('pdf/_partials/components/risk_icons', [
                    'riesgoAlto' => $dimension['distribucion']['riesgo_alto'] + $dimension['distribucion']['riesgo_muy_alto'],
                    'riesgoMedio' => $dimension['distribucion']['riesgo_medio'],
                ]) ?>

                <p style="font-size: 9pt; margin-top: 10px;">
                    Se evidencia que el <strong style="color: #F44336;"><?= $dimension['porcentajes']['riesgo_alto'] + $dimension['porcentajes']['riesgo_muy_alto'] ?>%</strong>
                    de los encuestados con el cuestionario Tipo <?= $forma ?> están en un nivel de riesgo <em>Alto y muy alto</em>,
                    el siguiente <strong style="color: #FFEB3B;"><?= $dimension['porcentajes']['riesgo_medio'] ?>%</strong> restante están en un nivel de riesgo <em>Medio</em>
                    y el <strong style="color: #4CAF50;"><?= $dimension['porcentajes']['sin_riesgo'] + $dimension['porcentajes']['riesgo_bajo'] ?>%</strong>
                    restante está en un riesgo <em>Bajo y sin riesgo</em>.
                </p>

                <!-- Foco objetivo -->
                <?php
                $focusTargets = [
                    'A' => 'Cargos Profesionales O De Jefatura',
                    'B' => 'Cargos Auxiliares u Operativos',
                ];
                $focusActions = [
                    'sin_riesgo' => 'Acción continuar con los programas actuales',
                    'riesgo_bajo' => 'Acción continuar con los programas actuales',
                    'riesgo_medio' => 'Acción reforzar los programas actuales',
                    'riesgo_alto' => 'Intervención prioritaria',
                    'riesgo_muy_alto' => 'Intervención inmediata',
                ];
                ?>
                <?= view('pdf/_partials/components/focus_box', [
                    'targetGroup' => $focusTargets[$forma] ?? 'Todos los cargos',
                    'action' => $focusActions[$dimension['nivel']] ?? 'Evaluar',
                    'description' => 'El factor de riesgo causa, o podría causar, alteraciones en la salud del trabajador que ameritan observación y acciones de intervención preventivas.'
                ]) ?>
            </div>
        </div>

        <!-- Áreas con Riesgo Alto y Muy Alto -->
        <div class="high-risk-section" style="margin-top: 15px;">
            <div class="high-risk-title">Áreas con Riesgo Alto y Muy Alto</div>

            <?php if (empty($dimension['trabajadores_riesgo_alto'])): ?>
                <div class="no-high-risk">
                    No hay áreas en riesgo alto o muy alto
                </div>
            <?php else: ?>
                <table class="data-table" style="font-size: 8pt;">
                    <thead>
                        <tr>
                            <th>Área</th>
                            <th>Cargo</th>
                            <th class="text-center">Participantes</th>
                            <th class="text-center">Nivel</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Agrupar por área
                        $porArea = [];
                        foreach ($dimension['trabajadores_riesgo_alto'] as $t) {
                            $area = $t['area'] ?: 'Sin área';
                            if (!isset($porArea[$area])) {
                                $porArea[$area] = ['count' => 0, 'cargos' => []];
                            }
                            $porArea[$area]['count']++;
                            $porArea[$area]['cargos'][] = $t['cargo'];
                        }
                        foreach ($porArea as $area => $info):
                        ?>
                        <tr>
                            <td><?= esc($area) ?></td>
                            <td><?= esc(implode(', ', array_unique($info['cargos']))) ?></td>
                            <td class="text-center"><?= $info['count'] ?></td>
                            <td class="text-center" style="background: #F44336; color: white;">Alto/Muy Alto</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <?php if (!empty($dimension['texto_ia'])): ?>
        <!-- Texto generado por IA (exactamente como está en BD) -->
        <div class="interpretation-text" style="margin-top: 15px; font-size: 9pt; border-top: 1px solid #ccc; padding-top: 10px;">
            <?= nl2br(esc($dimension['texto_ia'])) ?>
        </div>
        <?php endif; ?>

    </div>

    <?= view('pdf/_partials/footer') ?>
</div>
