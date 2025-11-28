<div class="pdf-page">
    <?= view('pdf/_partials/header', ['company' => $company, 'consultant' => $consultant]) ?>

    <div class="pdf-content">
        <!-- Título -->
        <h1 class="section-title" style="color: #006699; text-align: center; margin-bottom: 5px;">
            Total Factores de Riesgo Psicosocial Intralaboral
        </h1>
        <h2 style="text-align: center; color: #666; font-size: 12pt; margin-bottom: 20px;">
            Forma <?= $forma ?> - <?= $tipoTrabajadores ?>
        </h2>

        <!-- Gauge principal -->
        <div style="text-align: center; margin-bottom: 20px;">
            <?= view('pdf/_partials/components/gauge', [
                'title' => 'Total Intralaboral Forma ' . $forma,
                'value' => $total['promedio'],
                'ranges' => $baremos,
                'forma' => $forma,
                'size' => 'large'
            ]) ?>
        </div>

        <!-- Interpretación del nivel -->
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
        $color = $colors[$total['nivel']] ?? '#999';
        $label = $labels[$total['nivel']] ?? 'SIN DATOS';
        ?>

        <div class="interpretation-box" style="background: #f5f5f5; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <p style="font-size: 10pt; margin: 0;">
                El análisis del Cuestionario de Factores de Riesgo Psicosocial Intralaboral Forma <?= $forma ?>,
                aplicado a <strong><?= $total['total_evaluados'] ?></strong> trabajadores
                (<?= $tipoTrabajadores ?>), arroja un puntaje promedio de
                <strong><?= number_format($total['promedio'], 2, ',', '.') ?></strong>,
                clasificándose como <span style="color: <?= $color ?>; font-weight: bold;"><?= $label ?></span>.
            </p>
            <p style="font-size: 10pt; margin: 10px 0 0 0;">
                Esto indica que se debe <strong><?= $total['accion'] ?></strong>
                <?php if ($forma === 'A'): ?>
                    las intervenciones para cargos profesionales o de jefatura.
                <?php else: ?>
                    las intervenciones para cargos auxiliares u operativos.
                <?php endif; ?>
            </p>
        </div>

        <!-- Distribución por niveles de riesgo -->
        <h3 style="color: #006699; margin-bottom: 10px; font-size: 11pt;">
            Distribución por Niveles de Riesgo
        </h3>

        <div style="display: table; width: 100%; margin-bottom: 20px;">
            <div style="display: table-cell; width: 50%; vertical-align: top; padding-right: 10px;">
                <!-- Gráfico de barras -->
                <?php
                $barData = [
                    'Forma ' . $forma => [
                        'muy_alto' => $total['porcentajes']['riesgo_muy_alto'] + $total['porcentajes']['riesgo_alto'],
                        'medio' => $total['porcentajes']['riesgo_medio'],
                        'sin_riesgo' => $total['porcentajes']['sin_riesgo'] + $total['porcentajes']['riesgo_bajo'],
                    ]
                ];
                ?>
                <?= view('pdf/_partials/components/bar_chart_stacked', [
                    'data' => $barData,
                    'showLegend' => true,
                    'height' => 150
                ]) ?>
            </div>
            <div style="display: table-cell; width: 50%; vertical-align: top; padding-left: 10px;">
                <!-- Tabla de distribución -->
                <table class="data-table" style="width: 100%; font-size: 9pt;">
                    <thead>
                        <tr>
                            <th>Nivel de Riesgo</th>
                            <th style="text-align: center;">N</th>
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

        <!-- Iconos de riesgo -->
        <?= view('pdf/_partials/components/risk_icons', [
            'riesgoAlto' => $total['distribucion']['riesgo_alto'] + $total['distribucion']['riesgo_muy_alto'],
            'riesgoMedio' => $total['distribucion']['riesgo_medio'],
        ]) ?>

        <!-- Texto generado por IA -->
        <?php if (!empty($total['texto_ia'])): ?>
        <div class="ai-text" style="margin-top: 20px; padding: 15px; background: #e3f2fd; border-left: 3px solid #2196F3; font-size: 9pt;">
            <strong>Análisis Detallado:</strong><br>
            <?= nl2br(esc($total['texto_ia'])) ?>
        </div>
        <?php endif; ?>

    </div>

    <?= view('pdf/_partials/footer') ?>
</div>
