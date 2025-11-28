<!-- PÁGINA: MAPA DE CALOR ESTRÉS FORMA A -->
<?= $this->include('pdf/_partials/header') ?>

<h2 class="section-title" style="color: #006699; text-align: center; text-decoration: underline; margin-bottom: 15px;">
    Mapa de Calor Síntomas de Estrés - Forma A
</h2>

<p style="text-align: center; font-size: 9pt; color: #666; margin-bottom: 15px;">
    Jefes, Profesionales y Técnicos | <?= $totalTrabajadores ?> trabajadores evaluados
</p>

<?php
if (!function_exists('getEstresColorA')) {
    function getEstresColorA($nivel) {
        $colores = [
            'muy_bajo' => '#4CAF50', 'bajo' => '#8BC34A', 'medio' => '#FFC107',
            'alto' => '#FF9800', 'muy_alto' => '#F44336',
            'sin_riesgo' => '#4CAF50', 'riesgo_bajo' => '#8BC34A', 'riesgo_medio' => '#FFC107',
            'riesgo_alto' => '#FF9800', 'riesgo_muy_alto' => '#F44336',
        ];
        return $colores[$nivel] ?? '#9E9E9E';
    }
}
if (!function_exists('getEstresTextColorA')) {
    function getEstresTextColorA($nivel) {
        return in_array($nivel, ['medio', 'riesgo_medio']) ? '#333' : '#fff';
    }
}
if (!function_exists('getNivelEstresTextoA')) {
    function getNivelEstresTextoA($nivel) {
        $textos = [
            'muy_bajo' => 'Muy Bajo', 'bajo' => 'Bajo', 'medio' => 'Medio',
            'alto' => 'Alto', 'muy_alto' => 'Muy Alto',
            'sin_riesgo' => 'Sin Riesgo', 'riesgo_bajo' => 'Bajo', 'riesgo_medio' => 'Medio',
            'riesgo_alto' => 'Alto', 'riesgo_muy_alto' => 'Muy Alto',
        ];
        return $textos[$nivel] ?? 'No definido';
    }
}
?>

<?php if (!empty($calculations)): ?>

<!-- Leyenda -->
<div style="text-align: center; margin-bottom: 10px; padding: 6px; background: #f5f5f5; border-radius: 4px; font-size: 8pt;">
    <span style="display: inline-block; margin: 0 8px;">
        <span style="display: inline-block; width: 10px; height: 10px; background: #4CAF50; border-radius: 50%; vertical-align: middle;"></span> Muy Bajo
    </span>
    <span style="display: inline-block; margin: 0 8px;">
        <span style="display: inline-block; width: 10px; height: 10px; background: #8BC34A; border-radius: 50%; vertical-align: middle;"></span> Bajo
    </span>
    <span style="display: inline-block; margin: 0 8px;">
        <span style="display: inline-block; width: 10px; height: 10px; background: #FFC107; border-radius: 50%; vertical-align: middle;"></span> Medio
    </span>
    <span style="display: inline-block; margin: 0 8px;">
        <span style="display: inline-block; width: 10px; height: 10px; background: #FF9800; border-radius: 50%; vertical-align: middle;"></span> Alto
    </span>
    <span style="display: inline-block; margin: 0 8px;">
        <span style="display: inline-block; width: 10px; height: 10px; background: #F44336; border-radius: 50%; vertical-align: middle;"></span> Muy Alto
    </span>
</div>

<!-- Mapa de Calor Estrés Total -->
<?php $totalData = $calculations['estres_total'] ?? ['nivel' => 'muy_bajo', 'promedio' => 0]; ?>
<div style="border: 2px solid #333; background: <?= getEstresColorA($totalData['nivel']) ?>; color: <?= getEstresTextColorA($totalData['nivel']) ?>; text-align: center; padding: 20px;">
    <h3 style="margin: 0 0 10px 0; font-size: 12pt;">SÍNTOMAS DE ESTRÉS</h3>
    <div style="font-size: 32pt; font-weight: bold; margin: 10px 0;"><?= number_format($totalData['promedio'], 1) ?></div>
    <div style="font-size: 14pt; font-weight: 600;"><?= getNivelEstresTextoA($totalData['nivel']) ?></div>
</div>

<!-- Tabla de Síntomas / Preguntas -->
<?php if (!empty($calculations['symptom_data'])): ?>
<h3 style="color: #006699; margin-top: 20px; margin-bottom: 10px; font-size: 11pt;">
    Análisis Detallado por Síntoma (31 Preguntas)
</h3>

<table style="width: 100%; border-collapse: collapse; font-size: 7pt; margin-bottom: 15px;">
    <thead>
        <tr>
            <th style="width: 4%; background-color: #f8f9fa; padding: 6px 3px; border: 1px solid #ddd; text-align: center; font-weight: bold;">#</th>
            <th style="width: 40%; background-color: #f8f9fa; padding: 6px 3px; border: 1px solid #ddd; text-align: left; font-weight: bold;">Síntoma / Pregunta</th>
            <th style="width: 11%; background-color: #dc3545; color: white; padding: 6px 3px; border: 1px solid #ddd; text-align: center; font-weight: bold;">Siempre</th>
            <th style="width: 11%; background-color: #fd7e14; color: white; padding: 6px 3px; border: 1px solid #ddd; text-align: center; font-weight: bold;">Casi Siempre</th>
            <th style="width: 11%; background-color: #ffc107; color: #333; padding: 6px 3px; border: 1px solid #ddd; text-align: center; font-weight: bold;">A Veces</th>
            <th style="width: 11%; background-color: #28a745; color: white; padding: 6px 3px; border: 1px solid #ddd; text-align: center; font-weight: bold;">Nunca</th>
            <th style="width: 12%; background-color: #6c757d; color: white; padding: 6px 3px; border: 1px solid #ddd; text-align: center; font-weight: bold;">Crítico</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($calculations['symptom_data'] as $qNum => $data): ?>
        <tr style="background: <?= $qNum % 2 == 0 ? '#f9f9f9' : '#fff' ?>;">
            <td style="padding: 4px 3px; border: 1px solid #ddd; text-align: center; font-weight: bold;"><?= $qNum ?></td>
            <td style="padding: 4px 3px; border: 1px solid #ddd; text-align: left; font-size: 6.5pt;"><?= esc($data['question']) ?></td>
            <td style="padding: 4px 3px; border: 1px solid #ddd; text-align: center; font-weight: bold; <?= $data['siempre'] > 0 ? 'background: #ffebee; color: #c62828;' : '' ?>"><?= $data['siempre'] ?></td>
            <td style="padding: 4px 3px; border: 1px solid #ddd; text-align: center; font-weight: bold; <?= $data['casi_siempre'] > 0 ? 'background: #fff3e0; color: #e65100;' : '' ?>"><?= $data['casi_siempre'] ?></td>
            <td style="padding: 4px 3px; border: 1px solid #ddd; text-align: center;"><?= $data['a_veces'] ?></td>
            <td style="padding: 4px 3px; border: 1px solid #ddd; text-align: center;"><?= $data['nunca'] ?></td>
            <td style="padding: 4px 3px; border: 1px solid #ddd; text-align: center; font-weight: bold; <?= $data['critico'] > 0 ? 'background: #ffcdd2; color: #b71c1c;' : '' ?>"><?= $data['critico'] ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Leyenda de la Tabla -->
<div style="padding: 8px; background: #e3f2fd; border-left: 3px solid #2196F3; font-size: 7pt; margin-bottom: 10px;">
    <strong>Interpretación de la tabla:</strong>
    <ul style="margin: 5px 0 0 15px; padding: 0;">
        <li><strong>Siempre:</strong> El trabajador presenta este síntoma de forma permanente (mayor riesgo)</li>
        <li><strong>Casi Siempre:</strong> El trabajador presenta este síntoma frecuentemente (alto riesgo)</li>
        <li><strong>A Veces:</strong> El trabajador presenta este síntoma ocasionalmente (riesgo moderado)</li>
        <li><strong>Nunca:</strong> El trabajador NO presenta este síntoma (sin riesgo)</li>
        <li><strong>Crítico:</strong> Suma de "Siempre" + "Casi Siempre" - indica cuántas personas requieren intervención urgente</li>
    </ul>
</div>
<?php endif; ?>

<!-- Interpretación -->
<div style="margin-top: 15px; padding: 10px; background: <?= in_array($totalData['nivel'], ['alto', 'muy_alto', 'riesgo_alto', 'riesgo_muy_alto']) ? '#ffebee' : '#e8f5e9' ?>; border-left: 3px solid <?= in_array($totalData['nivel'], ['alto', 'muy_alto', 'riesgo_alto', 'riesgo_muy_alto']) ? '#F44336' : '#4CAF50' ?>; font-size: 8pt;">
    <strong>Interpretación:</strong><br>
    <?php if (in_array($totalData['nivel'], ['alto', 'muy_alto', 'riesgo_alto', 'riesgo_muy_alto'])): ?>
    El nivel de síntomas de estrés detectado es <strong><?= getNivelEstresTextoA($totalData['nivel']) ?></strong>.
    Se requiere intervención inmediata en el marco de un programa de vigilancia epidemiológica.
    Los trabajadores presentan alta probabilidad de asociación con efectos negativos en la salud física y mental.
    <?php else: ?>
    El nivel de síntomas de estrés detectado es <strong><?= getNivelEstresTextoA($totalData['nivel']) ?></strong>.
    Se recomienda mantener acciones preventivas y de promoción de la salud para conservar estos niveles.
    <?php endif; ?>
</div>

<!-- Nota Metodológica -->
<div style="margin-top: 10px; padding: 8px; background: #e3f2fd; border-left: 3px solid #2196F3; font-size: 7pt;">
    <strong>Nota:</strong> El cuestionario de estrés evalúa síntomas reveladores de la presencia de reacciones de estrés,
    distribuidos en 31 preguntas. Baremos aplicados según Resolución 2404/2019.
</div>

<?php else: ?>
<p style="text-align: center; color: #999; font-style: italic; padding: 40px 0;">
    No hay datos de estrés para Forma A disponibles.
</p>
<?php endif; ?>

<?= $this->include('pdf/_partials/footer') ?>
<div class="page-break"></div>
