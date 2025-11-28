<div class="pdf-page">
    <?= view('pdf/_partials/header', ['company' => $company, 'consultant' => $consultant]) ?>

    <div class="pdf-content">
        <!-- Título -->
        <h1 class="section-title" style="color: #006699; text-align: center; margin-bottom: 5px;">
            Resumen General - Factores de Riesgo Psicosocial Intralaboral
        </h1>
        <h2 style="text-align: center; color: #666; font-size: 12pt; margin-bottom: 20px;">
            Consolidado Total (Forma A + Forma B)
        </h2>

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
        $colorGeneral = $colors[$total['nivel']] ?? '#999';
        $labelGeneral = $labels[$total['nivel']] ?? 'SIN DATOS';
        ?>

        <!-- Resumen general con gauges comparativos -->
        <div style="display: table; width: 100%; margin-bottom: 20px;">
            <!-- Gauge Total General -->
            <div style="display: table-cell; width: 34%; text-align: center; vertical-align: top; padding: 10px;">
                <div style="background: linear-gradient(135deg, #0077B6 0%, #005A8C 100%); color: white; padding: 15px; border-radius: 10px;">
                    <div style="font-size: 10pt; margin-bottom: 5px;">TOTAL GENERAL</div>
                    <div style="font-size: 28pt; font-weight: bold;"><?= number_format($total['promedio'], 1) ?></div>
                    <div style="font-size: 9pt; padding: 5px 10px; background: <?= $colorGeneral ?>; border-radius: 4px; display: inline-block; margin-top: 5px;">
                        <?= $labelGeneral ?>
                    </div>
                    <div style="font-size: 8pt; margin-top: 10px; opacity: 0.9;">
                        <?= $total['total_evaluados'] ?> trabajadores evaluados
                    </div>
                </div>
            </div>

            <!-- Gauge Forma A -->
            <?php if ($totalA): ?>
            <div style="display: table-cell; width: 33%; text-align: center; vertical-align: top; padding: 10px;">
                <div style="background: #f5f5f5; padding: 15px; border-radius: 10px; border: 2px solid #0077B6;">
                    <div style="font-size: 9pt; color: #0077B6; margin-bottom: 5px;">FORMA A</div>
                    <div style="font-size: 9pt; color: #666;">Jefes, Profesionales y Técnicos</div>
                    <div style="font-size: 24pt; font-weight: bold; color: #333;"><?= number_format($totalA['promedio'], 1) ?></div>
                    <div style="font-size: 8pt; padding: 3px 8px; background: <?= $colors[$totalA['nivel']] ?? '#999' ?>; color: <?= $totalA['nivel'] === 'riesgo_medio' ? '#333' : 'white' ?>; border-radius: 4px; display: inline-block;">
                        <?= $labels[$totalA['nivel']] ?? 'N/A' ?>
                    </div>
                    <div style="font-size: 8pt; color: #666; margin-top: 5px;">
                        <?= $totalA['total_evaluados'] ?> evaluados
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Gauge Forma B -->
            <?php if ($totalB): ?>
            <div style="display: table-cell; width: 33%; text-align: center; vertical-align: top; padding: 10px;">
                <div style="background: #f5f5f5; padding: 15px; border-radius: 10px; border: 2px solid #FF9800;">
                    <div style="font-size: 9pt; color: #FF9800; margin-bottom: 5px;">FORMA B</div>
                    <div style="font-size: 9pt; color: #666;">Auxiliares y Operarios</div>
                    <div style="font-size: 24pt; font-weight: bold; color: #333;"><?= number_format($totalB['promedio'], 1) ?></div>
                    <div style="font-size: 8pt; padding: 3px 8px; background: <?= $colors[$totalB['nivel']] ?? '#999' ?>; color: <?= $totalB['nivel'] === 'riesgo_medio' ? '#333' : 'white' ?>; border-radius: 4px; display: inline-block;">
                        <?= $labels[$totalB['nivel']] ?? 'N/A' ?>
                    </div>
                    <div style="font-size: 8pt; color: #666; margin-top: 5px;">
                        <?= $totalB['total_evaluados'] ?> evaluados
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Interpretación general -->
        <div class="interpretation-box" style="background: #e8f5e9; padding: 15px; border-radius: 8px; border-left: 4px solid #4CAF50; margin-bottom: 20px;">
            <p style="font-size: 10pt; margin: 0;">
                El análisis consolidado de los factores de riesgo psicosocial intralaboral para
                <strong><?= $total['total_evaluados'] ?></strong> trabajadores
                (<?= $total['total_forma_a'] ?? 0 ?> de Forma A y <?= $total['total_forma_b'] ?? 0 ?> de Forma B)
                muestra un nivel de riesgo <span style="color: <?= $colorGeneral ?>; font-weight: bold;"><?= $labelGeneral ?></span>
                con un puntaje promedio de <strong><?= number_format($total['promedio'], 2, ',', '.') ?></strong>.
            </p>
        </div>

        <!-- Distribución consolidada -->
        <h3 style="color: #006699; margin-bottom: 10px; font-size: 11pt;">
            Distribución Consolidada por Niveles de Riesgo
        </h3>

        <div style="display: table; width: 100%; margin-bottom: 20px;">
            <div style="display: table-cell; width: 50%; vertical-align: top; padding-right: 10px;">
                <?php
                $barData = [];
                if ($totalA) {
                    $barData['Forma A'] = [
                        'muy_alto' => $totalA['porcentajes']['riesgo_muy_alto'] + $totalA['porcentajes']['riesgo_alto'],
                        'medio' => $totalA['porcentajes']['riesgo_medio'],
                        'sin_riesgo' => $totalA['porcentajes']['sin_riesgo'] + $totalA['porcentajes']['riesgo_bajo'],
                    ];
                }
                if ($totalB) {
                    $barData['Forma B'] = [
                        'muy_alto' => $totalB['porcentajes']['riesgo_muy_alto'] + $totalB['porcentajes']['riesgo_alto'],
                        'medio' => $totalB['porcentajes']['riesgo_medio'],
                        'sin_riesgo' => $totalB['porcentajes']['sin_riesgo'] + $totalB['porcentajes']['riesgo_bajo'],
                    ];
                }
                ?>
                <?= view('pdf/_partials/components/bar_chart_stacked', [
                    'data' => $barData,
                    'showLegend' => true,
                    'height' => 180
                ]) ?>
            </div>
            <div style="display: table-cell; width: 50%; vertical-align: top; padding-left: 10px;">
                <table class="data-table" style="width: 100%; font-size: 9pt;">
                    <thead>
                        <tr>
                            <th>Nivel</th>
                            <th style="text-align: center;">Total</th>
                            <th style="text-align: center;">%</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="background: #4CAF50; color: white;">Sin Riesgo</td>
                            <td style="text-align: center;"><?= $total['distribucion']['sin_riesgo'] ?></td>
                            <td style="text-align: center;"><?= $total['porcentajes']['sin_riesgo'] ?>%</td>
                        </tr>
                        <tr>
                            <td style="background: #8BC34A; color: white;">Riesgo Bajo</td>
                            <td style="text-align: center;"><?= $total['distribucion']['riesgo_bajo'] ?></td>
                            <td style="text-align: center;"><?= $total['porcentajes']['riesgo_bajo'] ?>%</td>
                        </tr>
                        <tr>
                            <td style="background: #FFEB3B; color: #333;">Riesgo Medio</td>
                            <td style="text-align: center;"><?= $total['distribucion']['riesgo_medio'] ?></td>
                            <td style="text-align: center;"><?= $total['porcentajes']['riesgo_medio'] ?>%</td>
                        </tr>
                        <tr>
                            <td style="background: #FF9800; color: white;">Riesgo Alto</td>
                            <td style="text-align: center;"><?= $total['distribucion']['riesgo_alto'] ?></td>
                            <td style="text-align: center;"><?= $total['porcentajes']['riesgo_alto'] ?>%</td>
                        </tr>
                        <tr>
                            <td style="background: #F44336; color: white;">Riesgo Muy Alto</td>
                            <td style="text-align: center;"><?= $total['distribucion']['riesgo_muy_alto'] ?></td>
                            <td style="text-align: center;"><?= $total['porcentajes']['riesgo_muy_alto'] ?>%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Texto generado por IA -->
        <?php if (!empty($total['texto_ia'])): ?>
        <div class="ai-text" style="margin-top: 15px; padding: 15px; background: #e3f2fd; border-left: 3px solid #2196F3; font-size: 9pt;">
            <strong>Análisis Consolidado:</strong><br>
            <?= nl2br(esc($total['texto_ia'])) ?>
        </div>
        <?php endif; ?>

    </div>

    <?= view('pdf/_partials/footer') ?>
</div>
