<div class="pdf-page">
    <?= view('pdf/_partials/header', ['company' => $company, 'consultant' => $consultant]) ?>

    <div class="pdf-content">
        <!-- Título -->
        <h1 class="section-title" style="color: #006699; text-align: center; margin-bottom: 5px;">
            Puntaje Total General de Factores de Riesgo Psicosocial
        </h1>
        <h2 style="text-align: center; color: #666; font-size: 11pt; margin-bottom: 15px;">
            Intralaboral + Extralaboral (Tabla 34 - Resolución 2404/2019)
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

        <!-- Fórmula explicativa -->
        <div style="background: #fff3e0; padding: 10px; border-radius: 6px; margin-bottom: 15px; text-align: center;">
            <strong style="color: #e65100;">Fórmula:</strong>
            <span style="font-size: 10pt;">
                Puntaje Total = (Puntaje Intralaboral + Puntaje Extralaboral) / 2
            </span>
        </div>

        <!-- Panel principal con resultado general -->
        <div style="display: table; width: 100%; margin-bottom: 20px;">
            <!-- Resultado Total General -->
            <div style="display: table-cell; width: 40%; text-align: center; vertical-align: top; padding: 10px;">
                <div style="background: linear-gradient(135deg, #6a1b9a 0%, #4a148c 100%); color: white; padding: 20px; border-radius: 12px;">
                    <div style="font-size: 11pt; margin-bottom: 5px;">TOTAL GENERAL PSICOSOCIAL</div>
                    <div style="font-size: 32pt; font-weight: bold;"><?= number_format($total['promedio_general'], 1) ?></div>
                    <div style="font-size: 9pt; padding: 5px 10px; background: <?= $colorGeneral ?>; color: <?= $total['nivel'] === 'riesgo_medio' ? '#333' : 'white' ?>; border-radius: 4px; display: inline-block; margin-top: 8px;">
                        <?= $labelGeneral ?>
                    </div>
                    <div style="font-size: 8pt; margin-top: 10px; opacity: 0.9;">
                        <?= $total['total_evaluados'] ?> trabajadores evaluados
                    </div>
                </div>
            </div>

            <!-- Desglose Intralaboral y Extralaboral -->
            <div style="display: table-cell; width: 60%; vertical-align: top; padding: 10px;">
                <div style="display: table; width: 100%;">
                    <!-- Puntaje Intralaboral -->
                    <div style="display: table-cell; width: 50%; padding-right: 5px;">
                        <div style="background: #e3f2fd; padding: 15px; border-radius: 8px; text-align: center; border: 2px solid #1976D2;">
                            <div style="font-size: 9pt; color: #1976D2; margin-bottom: 5px;">INTRALABORAL</div>
                            <div style="font-size: 22pt; font-weight: bold; color: #1976D2;"><?= number_format($total['promedio_intralaboral'], 1) ?></div>
                            <div style="font-size: 8pt; color: #666; margin-top: 5px;">Promedio</div>
                        </div>
                    </div>
                    <!-- Puntaje Extralaboral -->
                    <div style="display: table-cell; width: 50%; padding-left: 5px;">
                        <div style="background: #f3e5f5; padding: 15px; border-radius: 8px; text-align: center; border: 2px solid #7B1FA2;">
                            <div style="font-size: 9pt; color: #7B1FA2; margin-bottom: 5px;">EXTRALABORAL</div>
                            <div style="font-size: 22pt; font-weight: bold; color: #7B1FA2;"><?= number_format($total['promedio_extralaboral'], 1) ?></div>
                            <div style="font-size: 8pt; color: #666; margin-top: 5px;">Promedio</div>
                        </div>
                    </div>
                </div>

                <!-- Comparativa por formas -->
                <div style="margin-top: 10px; display: table; width: 100%;">
                    <?php if ($totalA): ?>
                    <div style="display: table-cell; width: 50%; padding-right: 5px;">
                        <div style="background: #f5f5f5; padding: 10px; border-radius: 6px; text-align: center;">
                            <div style="font-size: 8pt; color: #0077B6;">FORMA A</div>
                            <div style="font-size: 16pt; font-weight: bold;"><?= number_format($totalA['promedio_general'], 1) ?></div>
                            <div style="font-size: 7pt; padding: 2px 6px; background: <?= $colors[$totalA['nivel']] ?? '#999' ?>; color: <?= $totalA['nivel'] === 'riesgo_medio' ? '#333' : 'white' ?>; border-radius: 3px; display: inline-block;">
                                <?= $labels[$totalA['nivel']] ?? 'N/A' ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($totalB): ?>
                    <div style="display: table-cell; width: 50%; padding-left: 5px;">
                        <div style="background: #f5f5f5; padding: 10px; border-radius: 6px; text-align: center;">
                            <div style="font-size: 8pt; color: #FF9800;">FORMA B</div>
                            <div style="font-size: 16pt; font-weight: bold;"><?= number_format($totalB['promedio_general'], 1) ?></div>
                            <div style="font-size: 7pt; padding: 2px 6px; background: <?= $colors[$totalB['nivel']] ?? '#999' ?>; color: <?= $totalB['nivel'] === 'riesgo_medio' ? '#333' : 'white' ?>; border-radius: 3px; display: inline-block;">
                                <?= $labels[$totalB['nivel']] ?? 'N/A' ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Interpretación -->
        <div class="interpretation-box" style="background: #f3e5f5; padding: 12px; border-radius: 8px; border-left: 4px solid #7B1FA2; margin-bottom: 15px;">
            <p style="font-size: 9pt; margin: 0;">
                El <strong>Puntaje Total General de Factores de Riesgo Psicosocial</strong> para
                <strong><?= $total['total_evaluados'] ?></strong> trabajadores
                (<?= $total['total_forma_a'] ?? 0 ?> Forma A y <?= $total['total_forma_b'] ?? 0 ?> Forma B)
                es de <strong><?= number_format($total['promedio_general'], 2, ',', '.') ?></strong>,
                clasificándose como <span style="color: <?= $colorGeneral ?>; font-weight: bold;"><?= $labelGeneral ?></span>.
            </p>
            <p style="font-size: 9pt; margin: 8px 0 0 0;">
                Este puntaje resulta de promediar el riesgo <strong>Intralaboral</strong> (<?= number_format($total['promedio_intralaboral'], 1) ?>)
                y <strong>Extralaboral</strong> (<?= number_format($total['promedio_extralaboral'], 1) ?>).
                La acción recomendada es: <strong><?= $total['accion'] ?></strong>.
            </p>
        </div>

        <!-- Distribución por niveles de riesgo -->
        <h3 style="color: #006699; margin-bottom: 10px; font-size: 10pt;">
            Distribución por Niveles de Riesgo - Total General Psicosocial
        </h3>

        <div style="display: table; width: 100%; margin-bottom: 15px;">
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
                    'height' => 140
                ]) ?>
            </div>
            <div style="display: table-cell; width: 50%; vertical-align: top; padding-left: 10px;">
                <table class="data-table" style="width: 100%; font-size: 8pt;">
                    <thead>
                        <tr>
                            <th>Nivel</th>
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

        <!-- Tabla de Baremos (Tabla 34) -->
        <h3 style="color: #006699; margin-bottom: 8px; font-size: 10pt;">
            Baremos - Tabla 34 (Resolución 2404/2019)
        </h3>
        <div style="display: table; width: 100%; font-size: 8pt;">
            <div style="display: table-cell; width: 50%; padding-right: 5px;">
                <table class="data-table" style="width: 100%;">
                    <thead>
                        <tr style="background: #0077B6; color: white;">
                            <th colspan="2" style="text-align: center;">Forma A</th>
                        </tr>
                        <tr>
                            <th>Nivel</th>
                            <th style="text-align: center;">Rango</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($baremosTotalGeneral['A'] as $nivel => $rango): ?>
                        <tr>
                            <td style="background: <?= $colors[$nivel] ?>; color: <?= $nivel === 'riesgo_medio' ? '#333' : 'white' ?>;">
                                <?= $labels[$nivel] ?>
                            </td>
                            <td style="text-align: center;"><?= $rango[0] ?> - <?= $rango[1] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div style="display: table-cell; width: 50%; padding-left: 5px;">
                <table class="data-table" style="width: 100%;">
                    <thead>
                        <tr style="background: #FF9800; color: white;">
                            <th colspan="2" style="text-align: center;">Forma B</th>
                        </tr>
                        <tr>
                            <th>Nivel</th>
                            <th style="text-align: center;">Rango</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($baremosTotalGeneral['B'] as $nivel => $rango): ?>
                        <tr>
                            <td style="background: <?= $colors[$nivel] ?>; color: <?= $nivel === 'riesgo_medio' ? '#333' : 'white' ?>;">
                                <?= $labels[$nivel] ?>
                            </td>
                            <td style="text-align: center;"><?= $rango[0] ?> - <?= $rango[1] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <?= view('pdf/_partials/footer') ?>
</div>
