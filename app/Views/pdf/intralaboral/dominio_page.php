<div class="pdf-page">
    <?= view('pdf/_partials/header', ['company' => $company, 'consultant' => $consultant]) ?>

    <div class="pdf-content">
        <!-- Título del dominio -->
        <h1 class="section-title" style="color: #006699; text-align: center; margin-bottom: 5px;">
            Dominio: <?= esc($dominio['nombre']) ?>
        </h1>
        <h2 style="text-align: center; color: #666; font-size: 11pt; margin-bottom: 15px;">
            Forma <?= $forma ?> - <?= $tipoTrabajadores ?>
        </h2>

        <!-- Caja de definición -->
        <?= view('pdf/_partials/components/definition_box', [
            'label' => 'Definición:',
            'content' => $dominio['definicion']
        ]) ?>

        <!-- Gauge y resultados -->
        <div style="display: table; width: 100%; margin: 15px 0;">
            <div style="display: table-cell; width: 40%; vertical-align: top; text-align: center;">
                <?= view('pdf/_partials/components/gauge', [
                    'title' => $dominio['nombre'],
                    'value' => $dominio['promedio'],
                    'ranges' => $baremos,
                    'forma' => $forma,
                    'size' => 'medium'
                ]) ?>
            </div>
            <div style="display: table-cell; width: 60%; vertical-align: top; padding-left: 15px;">
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
                $color = $colors[$dominio['nivel']] ?? '#999';
                $label = $labels[$dominio['nivel']] ?? 'SIN DATOS';
                ?>

                <!-- Interpretación -->
                <div class="interpretation-text" style="font-size: 9pt; margin-bottom: 15px;">
                    <p>
                        El dominio <strong><?= esc($dominio['nombre']) ?></strong> para el cuestionario Tipo <?= $forma ?>
                        presenta un puntaje promedio de <strong><?= number_format($dominio['promedio'], 2, ',', '.') ?></strong>,
                        clasificándose como <span style="color: <?= $color ?>; font-weight: bold;"><?= $label ?></span>.
                    </p>
                    <p style="margin-top: 8px;">
                        Se evaluaron <strong><?= $dominio['total_evaluados'] ?></strong> trabajadores.
                        La acción recomendada es: <strong><?= $dominio['accion'] ?></strong>.
                    </p>
                </div>

                <!-- Dimensiones incluidas -->
                <div style="background: #f5f5f5; padding: 10px; border-radius: 6px; font-size: 9pt;">
                    <strong style="color: #006699;">Dimensiones que componen este dominio:</strong>
                    <ul style="margin: 8px 0 0 15px; padding: 0;">
                        <?php foreach ($dominio['dimensiones'] as $dim): ?>
                        <li style="margin-bottom: 3px;"><?= esc(str_replace(['(Solo Forma A)', '(Solo Forma B)'], '', $dim)) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Distribución por niveles de riesgo -->
        <h3 style="color: #006699; margin: 15px 0 10px 0; font-size: 10pt;">
            Distribución por Niveles de Riesgo
        </h3>

        <div style="display: table; width: 100%;">
            <div style="display: table-cell; width: 50%; vertical-align: top; padding-right: 10px;">
                <?php
                $barData = [
                    'FORMA ' . $forma => [
                        'muy_alto' => $dominio['porcentajes']['riesgo_muy_alto'] + $dominio['porcentajes']['riesgo_alto'],
                        'medio' => $dominio['porcentajes']['riesgo_medio'],
                        'sin_riesgo' => $dominio['porcentajes']['sin_riesgo'] + $dominio['porcentajes']['riesgo_bajo'],
                    ]
                ];
                ?>
                <?= view('pdf/_partials/components/bar_chart_stacked', [
                    'data' => $barData,
                    'showLegend' => true,
                    'height' => 120
                ]) ?>
            </div>
            <div style="display: table-cell; width: 50%; vertical-align: top; padding-left: 10px;">
                <!-- Iconos de riesgo -->
                <?= view('pdf/_partials/components/risk_icons', [
                    'riesgoAlto' => $dominio['distribucion']['riesgo_alto'] + $dominio['distribucion']['riesgo_muy_alto'],
                    'riesgoMedio' => $dominio['distribucion']['riesgo_medio'],
                ]) ?>

                <p style="font-size: 9pt; margin-top: 10px;">
                    <strong style="color: #F44336;"><?= $dominio['porcentajes']['riesgo_alto'] + $dominio['porcentajes']['riesgo_muy_alto'] ?>%</strong>
                    en riesgo alto/muy alto |
                    <strong style="color: #FFEB3B;"><?= $dominio['porcentajes']['riesgo_medio'] ?>%</strong>
                    en riesgo medio |
                    <strong style="color: #4CAF50;"><?= $dominio['porcentajes']['sin_riesgo'] + $dominio['porcentajes']['riesgo_bajo'] ?>%</strong>
                    bajo/sin riesgo
                </p>
            </div>
        </div>

        <!-- Foco objetivo -->
        <?php
        $focusTargets = [
            'A' => 'Cargos Profesionales O De Jefatura',
            'B' => 'Cargos Auxiliares u Operativos',
        ];
        $focusActions = [
            'sin_riesgo' => 'Mantener programas actuales de bienestar',
            'riesgo_bajo' => 'Continuar con programas de prevención',
            'riesgo_medio' => 'Reforzar programas de intervención',
            'riesgo_alto' => 'Intervención prioritaria requerida',
            'riesgo_muy_alto' => 'Intervención inmediata obligatoria',
        ];
        ?>
        <?= view('pdf/_partials/components/focus_box', [
            'targetGroup' => $focusTargets[$forma] ?? 'Todos los cargos',
            'action' => $focusActions[$dominio['nivel']] ?? 'Evaluar situación',
            'description' => 'El nivel de riesgo del dominio requiere las acciones indicadas según la Resolución 2404/2019.'
        ]) ?>

        <!-- Texto generado por IA -->
        <?php if (!empty($dominio['texto_ia'])): ?>
        <div class="ai-text" style="margin-top: 15px; padding: 12px; background: #e3f2fd; border-left: 3px solid #2196F3; font-size: 9pt;">
            <strong>Análisis del Dominio:</strong><br>
            <?= nl2br(esc($dominio['texto_ia'])) ?>
        </div>
        <?php endif; ?>

    </div>

    <?= view('pdf/_partials/footer') ?>
</div>
