<div class="pdf-page">
    <?= view('pdf/_partials/header', ['company' => $company, 'consultant' => $consultant]) ?>

    <div class="pdf-content">
        <!-- Título -->
        <h1 class="dimension-title" style="color: #FF6B35;">
            Resumen de Recomendaciones
        </h1>

        <!-- Indicador de Forma -->
        <div style="text-align: center; margin-bottom: 15px;">
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

        <!-- Introducción -->
        <div style="font-size: 9pt; margin-bottom: 15px; text-align: justify; line-height: 1.5;">
            A continuación se presentan las dimensiones que requieren atención según los niveles de riesgo
            identificados en la evaluación. Las recomendaciones están organizadas por prioridad de intervención.
        </div>

        <?php
        $colors = [
            'riesgo_muy_alto' => '#F44336',
            'riesgo_alto' => '#FF9800',
            'riesgo_medio' => '#FFEB3B',
        ];
        $labels = [
            'riesgo_muy_alto' => 'MUY ALTO',
            'riesgo_alto' => 'ALTO',
            'riesgo_medio' => 'MEDIO',
        ];
        $typeLabels = [
            'intralaboral' => 'Intralaboral',
            'extralaboral' => 'Extralaboral',
            'estres' => 'Estrés',
        ];
        $typeColors = [
            'intralaboral' => '#0077B6',
            'extralaboral' => '#00A86B',
            'estres' => '#9C27B0',
        ];
        ?>

        <!-- Tabla de dimensiones en riesgo -->
        <?php if (!empty($riskDimensions)): ?>
        <table class="data-table" style="font-size: 8pt; width: 100%;">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 35%;">Dimensión</th>
                    <th style="width: 15%;">Tipo</th>
                    <th style="width: 12%; text-align: center;">Puntaje</th>
                    <th style="width: 13%; text-align: center;">% Riesgo</th>
                    <th style="width: 20%; text-align: center;">Nivel</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach ($riskDimensions as $dim): ?>
                <tr>
                    <td style="text-align: center;"><?= $i++ ?></td>
                    <td><?= esc($dim['nombre']) ?></td>
                    <td>
                        <span style="
                            background: <?= $typeColors[$dim['questionnaire_type']] ?? '#666' ?>;
                            color: white;
                            padding: 2px 6px;
                            border-radius: 3px;
                            font-size: 7pt;
                        ">
                            <?= $typeLabels[$dim['questionnaire_type']] ?? $dim['questionnaire_type'] ?>
                        </span>
                    </td>
                    <td style="text-align: center;"><?= number_format($dim['promedio'], 1) ?></td>
                    <td style="text-align: center;">
                        <?= $dim['porcentaje_riesgo'] ?>%
                        <br><span style="font-size: 7pt; color: #666;">(<?= $dim['trabajadores_en_riesgo'] ?>/<?= $dim['total_evaluados'] ?>)</span>
                    </td>
                    <td style="text-align: center;">
                        <span style="
                            background: <?= $colors[$dim['nivel']] ?? '#999' ?>;
                            color: <?= $dim['nivel'] === 'riesgo_medio' ? '#333' : 'white' ?>;
                            padding: 3px 8px;
                            border-radius: 3px;
                            font-weight: bold;
                            font-size: 7pt;
                        ">
                            <?= $labels[$dim['nivel']] ?? 'N/A' ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div style="text-align: center; padding: 30px; background: #E8F5E9; border-radius: 8px; margin: 20px 0;">
            <span style="font-size: 12pt; color: #4CAF50; font-weight: bold;">
                No se identificaron dimensiones con riesgo significativo
            </span>
            <p style="font-size: 9pt; color: #666; margin-top: 10px;">
                Todas las dimensiones evaluadas se encuentran en niveles de riesgo bajo o sin riesgo.
            </p>
        </div>
        <?php endif; ?>

        <!-- Resumen por tipo de cuestionario -->
        <?php if (!empty($riskDimensions)): ?>
        <h2 class="subsection-title" style="margin-top: 20px; color: #FF6B35;">
            Resumen por Área
        </h2>

        <div style="display: flex; justify-content: space-around; margin-top: 10px;">
            <?php foreach (['intralaboral', 'extralaboral', 'estres'] as $type): ?>
            <?php
            $count = count($byType[$type] ?? []);
            $highCount = count(array_filter($byType[$type] ?? [], function($d) {
                return in_array($d['nivel'], ['riesgo_alto', 'riesgo_muy_alto']);
            }));
            ?>
            <div style="
                background: <?= $typeColors[$type] ?>;
                color: white;
                padding: 15px 20px;
                border-radius: 8px;
                text-align: center;
                min-width: 120px;
            ">
                <div style="font-size: 20pt; font-weight: bold;"><?= $count ?></div>
                <div style="font-size: 8pt; opacity: 0.9;"><?= $typeLabels[$type] ?></div>
                <?php if ($highCount > 0): ?>
                <div style="font-size: 7pt; margin-top: 5px; background: rgba(255,255,255,0.2); padding: 2px 5px; border-radius: 3px;">
                    <?= $highCount ?> prioridad alta
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Nota de interpretación -->
        <div style="
            margin-top: 20px;
            padding: 10px 15px;
            background: #FFF3E0;
            border-left: 4px solid #FF6B35;
            font-size: 8pt;
            color: #555;
        ">
            <strong>Nota:</strong> Las dimensiones con nivel de riesgo ALTO y MUY ALTO requieren
            intervención prioritaria. En las siguientes páginas se detallan los planes de acción
            específicos para cada una de estas dimensiones.
        </div>

    </div>

    <?= view('pdf/_partials/footer') ?>
</div>
