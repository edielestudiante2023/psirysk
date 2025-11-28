<div class="pdf-page">
    <?= view('pdf/_partials/header', ['company' => $company, 'consultant' => $consultant]) ?>

    <div class="pdf-content">
        <h1 class="section-title">Mapa de Calor - Riesgo Psicosocial General</h1>

        <?php if (empty($heatmapData)): ?>
            <p class="text-center" style="margin-top: 50px; color: #666;">
                <?= $message ?? 'No hay datos disponibles para mostrar el mapa de calor.' ?>
            </p>
        <?php else: ?>

            <!-- Resumen General -->
            <div class="definition-box">
                <div class="label">Resumen de Participación:</div>
                <div class="content">
                    <strong>Total de trabajadores evaluados:</strong> <?= $totalWorkers ?><br>
                    <strong>Forma A (Jefes/Profesionales):</strong> <?= $heatmapData['intralaboral']['forma_a']['total'] ?><br>
                    <strong>Forma B (Auxiliares/Operativos):</strong> <?= $heatmapData['intralaboral']['forma_b']['total'] ?>
                </div>
            </div>

            <!-- Mapa de Calor Visual -->
            <h2 class="subsection-title">Distribución por Niveles de Riesgo</h2>

            <table class="heatmap-table">
                <thead>
                    <tr>
                        <th>Cuestionario</th>
                        <th>Forma</th>
                        <th style="background: #4CAF50;">Sin Riesgo</th>
                        <th style="background: #8BC34A;">Bajo</th>
                        <th style="background: #FFEB3B; color: #333;">Medio</th>
                        <th style="background: #FF9800;">Alto</th>
                        <th style="background: #F44336;">Muy Alto</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Intralaboral Forma A -->
                    <tr>
                        <td rowspan="2"><strong>Intralaboral</strong></td>
                        <td>Forma A</td>
                        <?php
                        $intraA = $heatmapData['intralaboral']['forma_a'];
                        $total = max($intraA['total'], 1);
                        ?>
                        <td class="heatmap-cell" style="background: <?= $intraA['sin_riesgo'] > 0 ? '#4CAF50' : '#e0e0e0' ?>; color: <?= $intraA['sin_riesgo'] > 0 ? 'white' : '#999' ?>;">
                            <?= $intraA['sin_riesgo'] ?> (<?= round($intraA['sin_riesgo']/$total*100) ?>%)
                        </td>
                        <td class="heatmap-cell" style="background: <?= $intraA['bajo'] > 0 ? '#8BC34A' : '#e0e0e0' ?>; color: <?= $intraA['bajo'] > 0 ? 'white' : '#999' ?>;">
                            <?= $intraA['bajo'] ?> (<?= round($intraA['bajo']/$total*100) ?>%)
                        </td>
                        <td class="heatmap-cell" style="background: <?= $intraA['medio'] > 0 ? '#FFEB3B' : '#e0e0e0' ?>; color: <?= $intraA['medio'] > 0 ? '#333' : '#999' ?>;">
                            <?= $intraA['medio'] ?> (<?= round($intraA['medio']/$total*100) ?>%)
                        </td>
                        <td class="heatmap-cell" style="background: <?= $intraA['alto'] > 0 ? '#FF9800' : '#e0e0e0' ?>; color: <?= $intraA['alto'] > 0 ? 'white' : '#999' ?>;">
                            <?= $intraA['alto'] ?> (<?= round($intraA['alto']/$total*100) ?>%)
                        </td>
                        <td class="heatmap-cell" style="background: <?= $intraA['muy_alto'] > 0 ? '#F44336' : '#e0e0e0' ?>; color: <?= $intraA['muy_alto'] > 0 ? 'white' : '#999' ?>;">
                            <?= $intraA['muy_alto'] ?> (<?= round($intraA['muy_alto']/$total*100) ?>%)
                        </td>
                        <td><strong><?= $intraA['total'] ?></strong></td>
                    </tr>
                    <!-- Intralaboral Forma B -->
                    <tr>
                        <td>Forma B</td>
                        <?php
                        $intraB = $heatmapData['intralaboral']['forma_b'];
                        $total = max($intraB['total'], 1);
                        ?>
                        <td class="heatmap-cell" style="background: <?= $intraB['sin_riesgo'] > 0 ? '#4CAF50' : '#e0e0e0' ?>; color: <?= $intraB['sin_riesgo'] > 0 ? 'white' : '#999' ?>;">
                            <?= $intraB['sin_riesgo'] ?> (<?= round($intraB['sin_riesgo']/$total*100) ?>%)
                        </td>
                        <td class="heatmap-cell" style="background: <?= $intraB['bajo'] > 0 ? '#8BC34A' : '#e0e0e0' ?>; color: <?= $intraB['bajo'] > 0 ? 'white' : '#999' ?>;">
                            <?= $intraB['bajo'] ?> (<?= round($intraB['bajo']/$total*100) ?>%)
                        </td>
                        <td class="heatmap-cell" style="background: <?= $intraB['medio'] > 0 ? '#FFEB3B' : '#e0e0e0' ?>; color: <?= $intraB['medio'] > 0 ? '#333' : '#999' ?>;">
                            <?= $intraB['medio'] ?> (<?= round($intraB['medio']/$total*100) ?>%)
                        </td>
                        <td class="heatmap-cell" style="background: <?= $intraB['alto'] > 0 ? '#FF9800' : '#e0e0e0' ?>; color: <?= $intraB['alto'] > 0 ? 'white' : '#999' ?>;">
                            <?= $intraB['alto'] ?> (<?= round($intraB['alto']/$total*100) ?>%)
                        </td>
                        <td class="heatmap-cell" style="background: <?= $intraB['muy_alto'] > 0 ? '#F44336' : '#e0e0e0' ?>; color: <?= $intraB['muy_alto'] > 0 ? 'white' : '#999' ?>;">
                            <?= $intraB['muy_alto'] ?> (<?= round($intraB['muy_alto']/$total*100) ?>%)
                        </td>
                        <td><strong><?= $intraB['total'] ?></strong></td>
                    </tr>

                    <!-- Extralaboral Forma A -->
                    <tr>
                        <td rowspan="2"><strong>Extralaboral</strong></td>
                        <td>Forma A</td>
                        <?php
                        $extraA = $heatmapData['extralaboral']['forma_a'];
                        $total = max($extraA['total'], 1);
                        ?>
                        <td class="heatmap-cell" style="background: <?= $extraA['sin_riesgo'] > 0 ? '#4CAF50' : '#e0e0e0' ?>; color: <?= $extraA['sin_riesgo'] > 0 ? 'white' : '#999' ?>;">
                            <?= $extraA['sin_riesgo'] ?> (<?= round($extraA['sin_riesgo']/$total*100) ?>%)
                        </td>
                        <td class="heatmap-cell" style="background: <?= $extraA['bajo'] > 0 ? '#8BC34A' : '#e0e0e0' ?>; color: <?= $extraA['bajo'] > 0 ? 'white' : '#999' ?>;">
                            <?= $extraA['bajo'] ?> (<?= round($extraA['bajo']/$total*100) ?>%)
                        </td>
                        <td class="heatmap-cell" style="background: <?= $extraA['medio'] > 0 ? '#FFEB3B' : '#e0e0e0' ?>; color: <?= $extraA['medio'] > 0 ? '#333' : '#999' ?>;">
                            <?= $extraA['medio'] ?> (<?= round($extraA['medio']/$total*100) ?>%)
                        </td>
                        <td class="heatmap-cell" style="background: <?= $extraA['alto'] > 0 ? '#FF9800' : '#e0e0e0' ?>; color: <?= $extraA['alto'] > 0 ? 'white' : '#999' ?>;">
                            <?= $extraA['alto'] ?> (<?= round($extraA['alto']/$total*100) ?>%)
                        </td>
                        <td class="heatmap-cell" style="background: <?= $extraA['muy_alto'] > 0 ? '#F44336' : '#e0e0e0' ?>; color: <?= $extraA['muy_alto'] > 0 ? 'white' : '#999' ?>;">
                            <?= $extraA['muy_alto'] ?> (<?= round($extraA['muy_alto']/$total*100) ?>%)
                        </td>
                        <td><strong><?= $extraA['total'] ?></strong></td>
                    </tr>
                    <!-- Extralaboral Forma B -->
                    <tr>
                        <td>Forma B</td>
                        <?php
                        $extraB = $heatmapData['extralaboral']['forma_b'];
                        $total = max($extraB['total'], 1);
                        ?>
                        <td class="heatmap-cell" style="background: <?= $extraB['sin_riesgo'] > 0 ? '#4CAF50' : '#e0e0e0' ?>; color: <?= $extraB['sin_riesgo'] > 0 ? 'white' : '#999' ?>;">
                            <?= $extraB['sin_riesgo'] ?> (<?= round($extraB['sin_riesgo']/$total*100) ?>%)
                        </td>
                        <td class="heatmap-cell" style="background: <?= $extraB['bajo'] > 0 ? '#8BC34A' : '#e0e0e0' ?>; color: <?= $extraB['bajo'] > 0 ? 'white' : '#999' ?>;">
                            <?= $extraB['bajo'] ?> (<?= round($extraB['bajo']/$total*100) ?>%)
                        </td>
                        <td class="heatmap-cell" style="background: <?= $extraB['medio'] > 0 ? '#FFEB3B' : '#e0e0e0' ?>; color: <?= $extraB['medio'] > 0 ? '#333' : '#999' ?>;">
                            <?= $extraB['medio'] ?> (<?= round($extraB['medio']/$total*100) ?>%)
                        </td>
                        <td class="heatmap-cell" style="background: <?= $extraB['alto'] > 0 ? '#FF9800' : '#e0e0e0' ?>; color: <?= $extraB['alto'] > 0 ? 'white' : '#999' ?>;">
                            <?= $extraB['alto'] ?> (<?= round($extraB['alto']/$total*100) ?>%)
                        </td>
                        <td class="heatmap-cell" style="background: <?= $extraB['muy_alto'] > 0 ? '#F44336' : '#e0e0e0' ?>; color: <?= $extraB['muy_alto'] > 0 ? 'white' : '#999' ?>;">
                            <?= $extraB['muy_alto'] ?> (<?= round($extraB['muy_alto']/$total*100) ?>%)
                        </td>
                        <td><strong><?= $extraB['total'] ?></strong></td>
                    </tr>

                    <!-- Estrés Forma A -->
                    <tr>
                        <td rowspan="2"><strong>Estrés</strong></td>
                        <td>Forma A</td>
                        <?php
                        $estresA = $heatmapData['estres']['forma_a'];
                        $total = max($estresA['total'], 1);
                        ?>
                        <td class="heatmap-cell" style="background: <?= $estresA['muy_bajo'] > 0 ? '#4CAF50' : '#e0e0e0' ?>; color: <?= $estresA['muy_bajo'] > 0 ? 'white' : '#999' ?>;">
                            <?= $estresA['muy_bajo'] ?> (<?= round($estresA['muy_bajo']/$total*100) ?>%)
                        </td>
                        <td class="heatmap-cell" style="background: <?= $estresA['bajo'] > 0 ? '#8BC34A' : '#e0e0e0' ?>; color: <?= $estresA['bajo'] > 0 ? 'white' : '#999' ?>;">
                            <?= $estresA['bajo'] ?> (<?= round($estresA['bajo']/$total*100) ?>%)
                        </td>
                        <td class="heatmap-cell" style="background: <?= $estresA['medio'] > 0 ? '#FFEB3B' : '#e0e0e0' ?>; color: <?= $estresA['medio'] > 0 ? '#333' : '#999' ?>;">
                            <?= $estresA['medio'] ?> (<?= round($estresA['medio']/$total*100) ?>%)
                        </td>
                        <td class="heatmap-cell" style="background: <?= $estresA['alto'] > 0 ? '#FF9800' : '#e0e0e0' ?>; color: <?= $estresA['alto'] > 0 ? 'white' : '#999' ?>;">
                            <?= $estresA['alto'] ?> (<?= round($estresA['alto']/$total*100) ?>%)
                        </td>
                        <td class="heatmap-cell" style="background: <?= $estresA['muy_alto'] > 0 ? '#F44336' : '#e0e0e0' ?>; color: <?= $estresA['muy_alto'] > 0 ? 'white' : '#999' ?>;">
                            <?= $estresA['muy_alto'] ?> (<?= round($estresA['muy_alto']/$total*100) ?>%)
                        </td>
                        <td><strong><?= $estresA['total'] ?></strong></td>
                    </tr>
                    <!-- Estrés Forma B -->
                    <tr>
                        <td>Forma B</td>
                        <?php
                        $estresB = $heatmapData['estres']['forma_b'];
                        $total = max($estresB['total'], 1);
                        ?>
                        <td class="heatmap-cell" style="background: <?= $estresB['muy_bajo'] > 0 ? '#4CAF50' : '#e0e0e0' ?>; color: <?= $estresB['muy_bajo'] > 0 ? 'white' : '#999' ?>;">
                            <?= $estresB['muy_bajo'] ?> (<?= round($estresB['muy_bajo']/$total*100) ?>%)
                        </td>
                        <td class="heatmap-cell" style="background: <?= $estresB['bajo'] > 0 ? '#8BC34A' : '#e0e0e0' ?>; color: <?= $estresB['bajo'] > 0 ? 'white' : '#999' ?>;">
                            <?= $estresB['bajo'] ?> (<?= round($estresB['bajo']/$total*100) ?>%)
                        </td>
                        <td class="heatmap-cell" style="background: <?= $estresB['medio'] > 0 ? '#FFEB3B' : '#e0e0e0' ?>; color: <?= $estresB['medio'] > 0 ? '#333' : '#999' ?>;">
                            <?= $estresB['medio'] ?> (<?= round($estresB['medio']/$total*100) ?>%)
                        </td>
                        <td class="heatmap-cell" style="background: <?= $estresB['alto'] > 0 ? '#FF9800' : '#e0e0e0' ?>; color: <?= $estresB['alto'] > 0 ? 'white' : '#999' ?>;">
                            <?= $estresB['alto'] ?> (<?= round($estresB['alto']/$total*100) ?>%)
                        </td>
                        <td class="heatmap-cell" style="background: <?= $estresB['muy_alto'] > 0 ? '#F44336' : '#e0e0e0' ?>; color: <?= $estresB['muy_alto'] > 0 ? 'white' : '#999' ?>;">
                            <?= $estresB['muy_alto'] ?> (<?= round($estresB['muy_alto']/$total*100) ?>%)
                        </td>
                        <td><strong><?= $estresB['total'] ?></strong></td>
                    </tr>
                </tbody>
            </table>

            <!-- Leyenda -->
            <div class="stacked-bar-legend" style="margin-top: 20px;">
                <div class="legend-item">
                    <div class="legend-color" style="background-color: #4CAF50;"></div>
                    <span>Sin Riesgo / Muy Bajo</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background-color: #8BC34A;"></div>
                    <span>Riesgo Bajo</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background-color: #FFEB3B;"></div>
                    <span>Riesgo Medio</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background-color: #FF9800;"></div>
                    <span>Riesgo Alto</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background-color: #F44336;"></div>
                    <span>Riesgo Muy Alto</span>
                </div>
            </div>

        <?php endif; ?>
    </div>

    <?= view('pdf/_partials/footer') ?>
</div>
