<?php
/**
 * Componente Tabla de Niveles de Riesgo
 *
 * Variables esperadas:
 * - $showDescriptions: bool - Mostrar descripciones completas (default: true)
 */

$showDescriptions = $showDescriptions ?? true;

$riskLevels = [
    [
        'level' => 'Sin riesgo',
        'color' => '#4CAF50',
        'textColor' => 'white',
        'description' => 'Ausencia de riesgo o riesgo tan bajo que no amerita desarrollar actividades de Intervención'
    ],
    [
        'level' => 'Riesgo bajo',
        'color' => '#8BC34A',
        'textColor' => 'white',
        'description' => 'No se espera que los factores psicosociales que obtengan puntuaciones de este nivel estén relacionados con síntomas o respuestas significativas'
    ],
    [
        'level' => 'Riesgo medio',
        'color' => '#FFEB3B',
        'textColor' => '#333',
        'description' => 'Se esperaría una respuesta de estrés moderada. Las dimensiones y dominios que se encuentren bajo esta categoría ameritan observación y acciones de intervención preventivas'
    ],
    [
        'level' => 'Riesgo alto',
        'color' => '#FF9800',
        'textColor' => 'white',
        'description' => 'Tiene una importante posibilidad de asociación con respuestas de estrés alto y por tanto las dimensiones y dominios que se encuentren bajo esta categoría requieren intervención en el marco de un sistema de vigilancia epidemiológica'
    ],
    [
        'level' => 'Riesgo muy alto',
        'color' => '#F44336',
        'textColor' => 'white',
        'description' => 'Tiene una amplia posibilidad de asociarse a respuestas muy altas de estrés. Por consiguiente, las dimensiones y dominios que se encuentren bajo esta categoría requieren intervención inmediata en el marco de un sistema de vigilancia Epidemiológica'
    ],
];
?>

<table class="risk-levels-table">
    <thead>
        <tr>
            <th style="width: 120px;">Nivel de riesgo</th>
            <?php if ($showDescriptions): ?>
            <th>Descripción</th>
            <?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($riskLevels as $risk): ?>
        <tr>
            <td class="risk-level-cell" style="background-color: <?= $risk['color'] ?>; color: <?= $risk['textColor'] ?>;">
                <?= $risk['level'] ?>
            </td>
            <?php if ($showDescriptions): ?>
            <td><?= $risk['description'] ?></td>
            <?php endif; ?>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
