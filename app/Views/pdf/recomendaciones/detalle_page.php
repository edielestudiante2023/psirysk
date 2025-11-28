<div class="pdf-page">
    <?= view('pdf/_partials/header', ['company' => $company, 'consultant' => $consultant]) ?>

    <div class="pdf-content">
        <?php
        $colors = [
            'riesgo_muy_alto' => '#F44336',
            'riesgo_alto' => '#FF9800',
            'riesgo_medio' => '#FFEB3B',
        ];
        $labels = [
            'riesgo_muy_alto' => 'RIESGO MUY ALTO',
            'riesgo_alto' => 'RIESGO ALTO',
            'riesgo_medio' => 'RIESGO MEDIO',
        ];
        $typeLabels = [
            'intralaboral' => 'Intralaboral',
            'extralaboral' => 'Extralaboral',
            'estres' => 'Estrés',
        ];
        ?>

        <!-- Encabezado de la dimensión -->
        <div style="
            background: linear-gradient(135deg, <?= $colors[$dimension['nivel']] ?> 0%, <?= $colors[$dimension['nivel']] ?>99 100%);
            color: <?= $dimension['nivel'] === 'riesgo_medio' ? '#333' : 'white' ?>;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 15px;
        ">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-size: 8pt; opacity: 0.8; text-transform: uppercase; letter-spacing: 1px;">
                        Plan de Acción - <?= $typeLabels[$dimension['questionnaire_type']] ?? '' ?>
                    </div>
                    <div style="font-size: 14pt; font-weight: bold; margin-top: 3px;">
                        <?= esc($dimension['nombre']) ?>
                    </div>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 9pt; font-weight: bold;">
                        <?= $labels[$dimension['nivel']] ?? '' ?>
                    </div>
                    <div style="font-size: 8pt; opacity: 0.9;">
                        Puntaje: <?= number_format($dimension['promedio'], 1) ?> |
                        <?= $dimension['porcentaje_riesgo'] ?>% en riesgo
                    </div>
                </div>
            </div>
        </div>

        <!-- Indicador de Forma -->
        <div style="text-align: center; margin-bottom: 10px;">
            <span style="
                background: <?= $forma === 'A' ? '#0077B6' : '#FF6B35' ?>;
                color: white;
                padding: 3px 12px;
                border-radius: 10px;
                font-size: 8pt;
                font-weight: bold;
            ">
                FORMA <?= $forma ?> - <?= $forma === 'A' ? 'Profesionales / Jefaturas' : 'Auxiliares / Operativos' ?>
            </span>
        </div>

        <!-- Introducción -->
        <?php if (!empty($actionPlan['introduction'])): ?>
        <div style="
            background: #F5F5F5;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 12px;
            font-size: 8pt;
            line-height: 1.5;
            text-align: justify;
        ">
            <?= esc($actionPlan['introduction']) ?>
        </div>
        <?php endif; ?>

        <!-- Objetivos -->
        <?php if (!empty($actionPlan['objectives'])): ?>
        <div style="margin-bottom: 12px;">
            <h3 style="
                color: #FF6B35;
                font-size: 10pt;
                margin: 0 0 8px 0;
                padding-bottom: 3px;
                border-bottom: 2px solid #FF6B35;
            ">
                Objetivos
            </h3>
            <ul style="font-size: 8pt; margin: 0; padding-left: 20px; line-height: 1.6;">
                <?php
                $objectives = is_array($actionPlan['objectives']) ? $actionPlan['objectives'] : json_decode($actionPlan['objectives'], true);
                if (is_array($objectives)):
                    foreach ($objectives as $obj):
                        // Puede ser un string simple o un objeto con description
                        $objText = is_array($obj) ? ($obj['description'] ?? $obj['objetivo'] ?? json_encode($obj)) : $obj;
                ?>
                <li style="margin-bottom: 4px;"><?= esc($objText) ?></li>
                <?php
                    endforeach;
                endif;
                ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- Actividades a 6 meses -->
        <?php if (!empty($actionPlan['activities_6months'])): ?>
        <div style="margin-bottom: 12px;">
            <h3 style="
                color: #FF6B35;
                font-size: 10pt;
                margin: 0 0 8px 0;
                padding-bottom: 3px;
                border-bottom: 2px solid #FF6B35;
            ">
                Plan de Actividades (6 meses)
            </h3>
            <?php
            $activities = is_array($actionPlan['activities_6months']) ? $actionPlan['activities_6months'] : json_decode($actionPlan['activities_6months'], true);
            if (is_array($activities)):
                // Verificar si es formato por mes (mes_1, mes_2...) o lista plana
                $isByMonth = isset($activities['mes_1']) || isset($activities['month_1']);
            ?>
                <?php if ($isByMonth): ?>
                    <?php
                    $monthLabels = [
                        'mes_1' => 'Mes 1', 'mes_2' => 'Mes 2', 'mes_3' => 'Mes 3',
                        'mes_4' => 'Mes 4', 'mes_5' => 'Mes 5', 'mes_6' => 'Mes 6',
                        'month_1' => 'Mes 1', 'month_2' => 'Mes 2', 'month_3' => 'Mes 3',
                        'month_4' => 'Mes 4', 'month_5' => 'Mes 5', 'month_6' => 'Mes 6',
                    ];
                    ?>
                    <table class="data-table" style="font-size: 7pt; width: 100%;">
                        <thead>
                            <tr>
                                <th style="width: 12%;">Mes</th>
                                <th style="width: 88%;">Actividades</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activities as $monthKey => $monthActivities): ?>
                            <tr>
                                <td style="text-align: center; font-weight: bold; vertical-align: top;">
                                    <?= $monthLabels[$monthKey] ?? $monthKey ?>
                                </td>
                                <td>
                                    <ul style="margin: 0; padding-left: 15px;">
                                    <?php foreach ($monthActivities as $act): ?>
                                        <li style="margin-bottom: 3px;">
                                            <?= esc(is_array($act) ? ($act['description'] ?? $act['actividad'] ?? '') : $act) ?>
                                        </li>
                                    <?php endforeach; ?>
                                    </ul>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <table class="data-table" style="font-size: 7pt; width: 100%;">
                        <thead>
                            <tr>
                                <th style="width: 5%;">#</th>
                                <th style="width: 70%;">Actividad</th>
                                <th style="width: 25%;">Plazo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; foreach ($activities as $activity): ?>
                            <tr>
                                <td style="text-align: center;"><?= $i++ ?></td>
                                <td><?= esc(is_array($activity) ? ($activity['description'] ?? $activity['actividad'] ?? '') : $activity) ?></td>
                                <td><?= esc(is_array($activity) ? ($activity['plazo'] ?? $activity['deadline'] ?? 'Según cronograma') : 'Según cronograma') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Bibliografía -->
        <?php if (!empty($actionPlan['bibliography'])): ?>
        <div style="margin-top: 10px;">
            <h3 style="
                color: #666;
                font-size: 9pt;
                margin: 0 0 5px 0;
            ">
                Referencias
            </h3>
            <div style="font-size: 7pt; color: #666; font-style: italic;">
                <?php
                $bibliography = is_array($actionPlan['bibliography']) ? $actionPlan['bibliography'] : json_decode($actionPlan['bibliography'], true);
                if (is_array($bibliography)):
                    foreach ($bibliography as $ref):
                ?>
                <div style="margin-bottom: 3px;">• <?= esc($ref) ?></div>
                <?php
                    endforeach;
                endif;
                ?>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <?= view('pdf/_partials/footer') ?>
</div>
