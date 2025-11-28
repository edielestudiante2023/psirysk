<!-- PÁGINA: MAPA DE CALOR EXTRALABORAL FORMA B -->
<?= $this->include('pdf/_partials/header') ?>

<h2 class="section-title" style="color: #006699; text-align: center; text-decoration: underline; margin-bottom: 15px;">
    Mapa de Calor Extralaboral - Forma B
</h2>

<p style="text-align: center; font-size: 9pt; color: #666; margin-bottom: 15px;">
    Auxiliares y Operarios | <?= $totalTrabajadores ?> trabajadores evaluados
</p>

<?php
if (!function_exists('getExtraColorB')) {
    function getExtraColorB($nivel) {
        $colores = [
            'sin_riesgo' => '#4CAF50', 'riesgo_bajo' => '#8BC34A', 'riesgo_medio' => '#FFC107',
            'riesgo_alto' => '#FF9800', 'riesgo_muy_alto' => '#F44336',
        ];
        return $colores[$nivel] ?? '#9E9E9E';
    }
}
if (!function_exists('getExtraTextColorB')) {
    function getExtraTextColorB($nivel) {
        return $nivel === 'riesgo_medio' ? '#333' : '#fff';
    }
}
if (!function_exists('getNivelExtraTextoB')) {
    function getNivelExtraTextoB($nivel) {
        $textos = [
            'sin_riesgo' => 'Sin Riesgo', 'riesgo_bajo' => 'Riesgo Bajo', 'riesgo_medio' => 'Riesgo Medio',
            'riesgo_alto' => 'Riesgo Alto', 'riesgo_muy_alto' => 'Riesgo Muy Alto',
        ];
        return $textos[$nivel] ?? 'No definido';
    }
}
?>

<?php if (!empty($calculations)): ?>

<!-- Leyenda -->
<div style="text-align: center; margin-bottom: 10px; padding: 6px; background: #f5f5f5; border-radius: 4px; font-size: 8pt;">
    <span style="display: inline-block; margin: 0 8px;">
        <span style="display: inline-block; width: 10px; height: 10px; background: #4CAF50; border-radius: 50%; vertical-align: middle;"></span> Sin Riesgo
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

<!-- Mapa de Calor Extralaboral -->
<div style="border: 2px solid #333; background: #fff;">
    <div style="display: table; width: 100%;">
        <!-- TOTAL EXTRALABORAL -->
        <?php $totalData = $calculations['extralaboral_total'] ?? ['nivel' => 'sin_riesgo', 'promedio' => 0]; ?>
        <div style="display: table-cell; width: 40%; vertical-align: middle; text-align: center; padding: 20px; background: <?= getExtraColorB($totalData['nivel']) ?>; color: <?= getExtraTextColorB($totalData['nivel']) ?>; border-right: 2px solid #333; font-weight: bold; font-size: 10pt;">
            TOTAL FACTORES<br>EXTRALABORALES<br>
            <span style="font-size: 24pt;"><?= number_format($totalData['promedio'], 1) ?></span><br>
            <span style="font-size: 9pt;"><?= getNivelExtraTextoB($totalData['nivel']) ?></span>
        </div>

        <!-- DIMENSIONES EXTRALABORALES -->
        <div style="display: table-cell; width: 60%; vertical-align: top;">
            <?php
            $dimensiones = [
                ['key' => 'dim_tiempo_fuera', 'nombre' => 'Tiempo fuera del trabajo'],
                ['key' => 'dim_relaciones_familiares', 'nombre' => 'Relaciones familiares'],
                ['key' => 'dim_comunicacion_relaciones', 'nombre' => 'Comunicación y relaciones interpersonales'],
                ['key' => 'dim_situacion_economica', 'nombre' => 'Situación económica del grupo familiar'],
                ['key' => 'dim_caracteristicas_vivienda', 'nombre' => 'Características de la vivienda y de su entorno'],
                ['key' => 'dim_influencia_entorno', 'nombre' => 'Influencia del entorno extralaboral sobre el trabajo'],
                ['key' => 'dim_desplazamiento', 'nombre' => 'Desplazamiento vivienda - trabajo - vivienda'],
            ];

            foreach ($dimensiones as $dimIndex => $dimension):
                $dimData = $calculations[$dimension['key']] ?? ['nivel' => 'sin_riesgo', 'promedio' => 0];
            ?>
            <div style="padding: 8px 12px; background: <?= getExtraColorB($dimData['nivel']) ?>; color: <?= getExtraTextColorB($dimData['nivel']) ?>; border-bottom: <?= $dimIndex < count($dimensiones) - 1 ? '1px solid rgba(0,0,0,0.2)' : 'none' ?>; font-size: 9pt; overflow: hidden;">
                <span style="float: left;"><?= $dimension['nombre'] ?></span>
                <span style="float: right; font-weight: bold; font-size: 11pt;"><?= number_format($dimData['promedio'], 1) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Interpretación -->
<div style="margin-top: 20px; padding: 12px; background: #fff3e0; border-left: 3px solid #FF9800; font-size: 9pt;">
    <strong>Interpretación Factores Extralaborales:</strong><br>
    Los factores extralaborales comprenden los aspectos del entorno familiar, social y económico del trabajador.
    Incluyen las condiciones del lugar de vivienda, que pueden influir en la salud y bienestar del individuo.
    El nivel de riesgo detectado es: <strong><?= getNivelExtraTextoB($totalData['nivel']) ?></strong>.
</div>

<!-- Nota Metodológica -->
<div style="margin-top: 15px; padding: 10px; background: #e3f2fd; border-left: 3px solid #2196F3; font-size: 8pt;">
    <strong>Nota:</strong> El cuestionario extralaboral contiene 7 dimensiones que evalúan las condiciones externas al
    trabajo que pueden afectar la salud del trabajador. Baremos aplicados según Resolución 2404/2019.
</div>

<?php else: ?>
<p style="text-align: center; color: #999; font-style: italic; padding: 40px 0;">
    No hay datos extralaborales de Forma B disponibles.
</p>
<?php endif; ?>

<?= $this->include('pdf/_partials/footer') ?>
<div class="page-break"></div>
