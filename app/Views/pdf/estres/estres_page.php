<div class="pdf-page">
    <?= view('pdf/_partials/header', ['company' => $company, 'consultant' => $consultant]) ?>

    <div class="pdf-content">
        <!-- Título -->
        <h1 class="dimension-title" style="color: #9B59B6;">
            <?= esc($estres['nombre']) ?>
        </h1>

        <!-- Indicador de Forma -->
        <div style="text-align: center; margin-bottom: 10px;">
            <span style="
                background: <?= $forma === 'A' ? '#0077B6' : '#FF6B35' ?>;
                color: white;
                padding: 5px 15px;
                border-radius: 15px;
                font-size: 10pt;
                font-weight: bold;
            ">
                FORMA <?= $forma ?> - <?= $forma === 'A' ? 'Profesionales / Jefaturas' : 'Auxiliares / Operativos' ?>
            </span>
        </div>

        <!-- Caja de definición -->
        <?= view('pdf/_partials/components/definition_box', [
            'label' => 'Descripción:',
            'content' => $estres['definicion']
        ]) ?>

        <!-- Gauge -->
        <div class="gauge-dual-container">
            <div class="gauge-item">
                <?= view('pdf/_partials/components/gauge', [
                    'title' => 'Nivel de Estrés',
                    'value' => $estres['promedio'],
                    'ranges' => $baremos,
                    'forma' => $forma,
                    'size' => 'medium'
                ]) ?>

                <!-- Interpretación -->
                <div class="interpretation-text" style="font-size: 9pt; margin-top: 10px;">
                    Para el cuestionario Tipo <?= $forma ?> se evidencia que el nivel de estrés se encuentra con un valor de
                    <strong><?= number_format($estres['promedio'], 2, ',', '.') ?></strong> denominándose
                    <?php
                    $colors = [
                        'sin_riesgo' => '#4CAF50',
                        'riesgo_bajo' => '#8BC34A',
                        'riesgo_medio' => '#FFEB3B',
                        'riesgo_alto' => '#FF9800',
                        'riesgo_muy_alto' => '#F44336',
                    ];
                    $labels = [
                        'sin_riesgo' => 'MUY BAJO',
                        'riesgo_bajo' => 'BAJO',
                        'riesgo_medio' => 'MEDIO',
                        'riesgo_alto' => 'ALTO',
                        'riesgo_muy_alto' => 'MUY ALTO',
                    ];
                    $color = $colors[$estres['nivel']] ?? '#999';
                    $label = $labels[$estres['nivel']] ?? 'SIN DATOS';
                    ?>
                    <span style="color: <?= $color ?>; font-weight: bold;"><?= $label ?></span>,
                    por lo que se debe <strong><?= $estres['accion'] ?></strong>
                    <?php if ($forma === 'A'): ?>
                        las intervenciones que se realicen para los cargos profesionales o de jefatura.
                    <?php else: ?>
                        con acciones que se realicen para los cargos auxiliares u operativos.
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Porcentajes de distribución -->
        <h2 class="subsection-title" style="text-align: center; margin-top: 15px;">
            Porcentajes de distribución por Niveles de Estrés
        </h2>

        <div style="display: flex; justify-content: space-around; align-items: flex-start; margin-top: 10px;">
            <!-- Gráfico de barras -->
            <div style="flex: 1; max-width: 45%;">
                <?php
                $barData = [
                    'FORMA ' . $forma => [
                        'muy_alto' => $estres['porcentajes']['riesgo_muy_alto'] + $estres['porcentajes']['riesgo_alto'],
                        'medio' => $estres['porcentajes']['riesgo_medio'],
                        'sin_riesgo' => $estres['porcentajes']['sin_riesgo'] + $estres['porcentajes']['riesgo_bajo'],
                    ]
                ];
                ?>
                <?= view('pdf/_partials/components/bar_chart_stacked', [
                    'data' => $barData,
                    'showLegend' => true,
                    'height' => 150
                ]) ?>
            </div>

            <!-- Iconos y texto -->
            <div style="flex: 1; max-width: 50%; padding-left: 20px;">
                <?= view('pdf/_partials/components/risk_icons', [
                    'riesgoAlto' => $estres['distribucion']['riesgo_alto'] + $estres['distribucion']['riesgo_muy_alto'],
                    'riesgoMedio' => $estres['distribucion']['riesgo_medio'],
                ]) ?>

                <p style="font-size: 9pt; margin-top: 10px;">
                    Se evidencia que el <strong style="color: #F44336;"><?= $estres['porcentajes']['riesgo_alto'] + $estres['porcentajes']['riesgo_muy_alto'] ?>%</strong>
                    de los encuestados con el cuestionario Tipo <?= $forma ?> presentan un nivel de estrés <em>Alto y muy alto</em>,
                    el siguiente <strong style="color: #FFEB3B;"><?= $estres['porcentajes']['riesgo_medio'] ?>%</strong> presenta un nivel <em>Medio</em>
                    y el <strong style="color: #4CAF50;"><?= $estres['porcentajes']['sin_riesgo'] + $estres['porcentajes']['riesgo_bajo'] ?>%</strong>
                    restante presenta un nivel <em>Bajo o muy bajo</em>.
                </p>

                <!-- Foco objetivo -->
                <?php
                $focusTargets = [
                    'A' => 'Cargos Profesionales O De Jefatura',
                    'B' => 'Cargos Auxiliares u Operativos',
                ];
                $focusActions = [
                    'sin_riesgo' => 'Mantener programas de bienestar actuales',
                    'riesgo_bajo' => 'Mantener programas de bienestar actuales',
                    'riesgo_medio' => 'Reforzar programas de manejo del estrés',
                    'riesgo_alto' => 'Intervención prioritaria en manejo del estrés',
                    'riesgo_muy_alto' => 'Intervención inmediata y seguimiento individual',
                ];
                ?>
                <?= view('pdf/_partials/components/focus_box', [
                    'targetGroup' => $focusTargets[$forma] ?? 'Todos los cargos',
                    'action' => $focusActions[$estres['nivel']] ?? 'Evaluar',
                    'description' => 'Los síntomas de estrés identificados requieren atención según el nivel detectado.'
                ]) ?>
            </div>
        </div>

        <!-- Trabajadores en Riesgo Alto -->
        <div class="high-risk-section" style="margin-top: 15px;">
            <div class="high-risk-title" style="background: #9B59B6;">Trabajadores con Nivel de Estrés Alto y Muy Alto</div>

            <?php if (empty($estres['trabajadores_riesgo_alto'])): ?>
                <div class="no-high-risk">
                    No hay trabajadores con nivel de estrés alto o muy alto
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
                        $porArea = [];
                        foreach ($estres['trabajadores_riesgo_alto'] as $t) {
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

        <?php if (!empty($estres['texto_ia'])): ?>
        <!-- Texto generado por IA (exactamente como está en BD) -->
        <div class="interpretation-text" style="margin-top: 15px; font-size: 9pt; border-top: 1px solid #ccc; padding-top: 10px;">
            <?= nl2br(esc($estres['texto_ia'])) ?>
        </div>
        <?php endif; ?>

    </div>

    <?= view('pdf/_partials/footer') ?>
</div>
