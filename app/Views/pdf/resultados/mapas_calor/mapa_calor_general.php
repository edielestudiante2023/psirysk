<!-- PÁGINA: MAPA DE CALOR GENERAL -->
<?= $this->include('pdf/_partials/header') ?>

<h2 class="section-title" style="color: #006699; text-align: center; text-decoration: underline; margin-bottom: 15px;">
    Mapa de Calor - Riesgo Psicosocial General
</h2>

<?php
// Función helper para obtener colores según nivel de riesgo
if (!function_exists('getGeneralHeatmapColor')) {
    function getGeneralHeatmapColor($nivel) {
        $colores = [
            'sin_riesgo' => '#4CAF50',
            'riesgo_bajo' => '#8BC34A',
            'riesgo_medio' => '#FFC107',
            'riesgo_alto' => '#FF9800',
            'riesgo_muy_alto' => '#F44336',
            'muy_bajo' => '#4CAF50',
            'bajo' => '#8BC34A',
            'medio' => '#FFC107',
            'alto' => '#FF9800',
            'muy_alto' => '#F44336',
        ];
        return $colores[$nivel] ?? '#9E9E9E';
    }
}

if (!function_exists('getGeneralHeatmapTextColor')) {
    function getGeneralHeatmapTextColor($nivel) {
        return in_array($nivel, ['riesgo_medio', 'medio']) ? '#333' : '#fff';
    }
}
?>

<?php if (!empty($heatmapData)): ?>

<!-- Leyenda -->
<div style="text-align: center; margin-bottom: 15px; padding: 8px; background: #f5f5f5; border-radius: 4px;">
    <span style="display: inline-block; margin: 0 10px; font-size: 9pt;">
        <span style="display: inline-block; width: 12px; height: 12px; background: #4CAF50; border-radius: 50%; vertical-align: middle;"></span>
        Sin Riesgo/Bajo
    </span>
    <span style="display: inline-block; margin: 0 10px; font-size: 9pt;">
        <span style="display: inline-block; width: 12px; height: 12px; background: #FFC107; border-radius: 50%; vertical-align: middle;"></span>
        Riesgo Medio
    </span>
    <span style="display: inline-block; margin: 0 10px; font-size: 9pt;">
        <span style="display: inline-block; width: 12px; height: 12px; background: #F44336; border-radius: 50%; vertical-align: middle;"></span>
        Riesgo Alto/Muy Alto
    </span>
</div>

<!-- Mapa de Calor Visual -->
<div style="border: 2px solid #333; background: #fff; margin-bottom: 20px;">
    <!-- INTRALABORAL -->
    <div style="display: table; width: 100%; border-bottom: 2px solid #333;">
        <div style="display: table-cell; width: 20%; vertical-align: middle; text-align: center; padding: 15px; background: <?= getGeneralHeatmapColor($heatmapData['intralaboral_total']['nivel']) ?>; color: <?= getGeneralHeatmapTextColor($heatmapData['intralaboral_total']['nivel']) ?>; border-right: 2px solid #333; font-weight: bold; font-size: 9pt;">
            TOTAL GENERAL<br>FACTORES DE RIESGO<br>PSICOSOCIAL<br>INTRALABORAL<br>
            <span style="font-size: 14pt;"><?= number_format($heatmapData['intralaboral_total']['promedio'], 1) ?></span>
        </div>
        <div style="display: table-cell; width: 80%; vertical-align: top;">
            <!-- Dominios e Dimensiones -->
            <?php
            $dominios = [
                [
                    'nombre' => 'LIDERAZGO Y RELACIONES SOCIALES EN EL TRABAJO',
                    'key' => 'dom_liderazgo',
                    'dimensiones' => [
                        ['key' => 'dim_caracteristicas_liderazgo', 'nombre' => 'Características del liderazgo'],
                        ['key' => 'dim_relaciones_sociales', 'nombre' => 'Relaciones sociales en el trabajo'],
                        ['key' => 'dim_retroalimentacion', 'nombre' => 'Retroalimentación del desempeño'],
                        ['key' => 'dim_relacion_colaboradores', 'nombre' => 'Relación con los colaboradores'],
                    ]
                ],
                [
                    'nombre' => 'CONTROL SOBRE EL TRABAJO',
                    'key' => 'dom_control',
                    'dimensiones' => [
                        ['key' => 'dim_claridad_rol', 'nombre' => 'Claridad de rol'],
                        ['key' => 'dim_capacitacion', 'nombre' => 'Capacitación'],
                        ['key' => 'dim_participacion_manejo_cambio', 'nombre' => 'Participación y manejo del cambio'],
                        ['key' => 'dim_oportunidades_desarrollo', 'nombre' => 'Oportunidades para el uso y desarrollo de habilidades'],
                        ['key' => 'dim_control_autonomia', 'nombre' => 'Control y autonomía sobre el trabajo'],
                    ]
                ],
                [
                    'nombre' => 'DEMANDAS DEL TRABAJO',
                    'key' => 'dom_demandas',
                    'dimensiones' => [
                        ['key' => 'dim_demandas_ambientales', 'nombre' => 'Demandas ambientales y de esfuerzo físico'],
                        ['key' => 'dim_demandas_emocionales', 'nombre' => 'Demandas emocionales'],
                        ['key' => 'dim_demandas_cuantitativas', 'nombre' => 'Demandas cuantitativas'],
                        ['key' => 'dim_influencia_trabajo_entorno_extralaboral', 'nombre' => 'Influencia del trabajo sobre el entorno extralaboral'],
                        ['key' => 'dim_demandas_responsabilidad', 'nombre' => 'Exigencias de responsabilidad del cargo'],
                        ['key' => 'dim_carga_mental', 'nombre' => 'Demandas de carga mental'],
                        ['key' => 'dim_consistencia_rol', 'nombre' => 'Consistencia del rol'],
                        ['key' => 'dim_demandas_jornada_trabajo', 'nombre' => 'Demandas de la jornada de trabajo'],
                    ]
                ],
                [
                    'nombre' => 'RECOMPENSAS',
                    'key' => 'dom_recompensas',
                    'dimensiones' => [
                        ['key' => 'dim_recompensas_pertenencia', 'nombre' => 'Recompensas derivadas de la pertenencia'],
                        ['key' => 'dim_reconocimiento_compensacion', 'nombre' => 'Reconocimiento y compensación'],
                    ]
                ],
            ];

            foreach ($dominios as $domIndex => $dominio):
                $domData = $heatmapData[$dominio['key']] ?? ['nivel' => 'sin_riesgo', 'promedio' => 0];
            ?>
            <div style="display: table; width: 100%; border-bottom: <?= $domIndex < count($dominios) - 1 ? '1px solid #666' : 'none' ?>;">
                <div style="display: table-cell; width: 30%; vertical-align: middle; text-align: center; padding: 8px; background: <?= getGeneralHeatmapColor($domData['nivel']) ?>; color: <?= getGeneralHeatmapTextColor($domData['nivel']) ?>; border-right: 1px solid #666; font-weight: bold; font-size: 8pt;">
                    <?= $dominio['nombre'] ?><br>
                    <span style="font-size: 11pt;"><?= number_format($domData['promedio'], 1) ?></span>
                </div>
                <div style="display: table-cell; width: 70%; vertical-align: top;">
                    <?php foreach ($dominio['dimensiones'] as $dimIndex => $dimension):
                        $dimData = $heatmapData[$dimension['key']] ?? ['nivel' => 'sin_riesgo', 'promedio' => 0];
                    ?>
                    <div style="padding: 4px 8px; background: <?= getGeneralHeatmapColor($dimData['nivel']) ?>; color: <?= getGeneralHeatmapTextColor($dimData['nivel']) ?>; border-bottom: <?= $dimIndex < count($dominio['dimensiones']) - 1 ? '1px solid rgba(255,255,255,0.3)' : 'none' ?>; font-size: 8pt; overflow: hidden;">
                        <span style="float: left;"><?= $dimension['nombre'] ?></span>
                        <span style="float: right; font-weight: bold;"><?= number_format($dimData['promedio'], 1) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- EXTRALABORAL -->
    <div style="display: table; width: 100%; border-bottom: 2px solid #333;">
        <?php $extraData = $heatmapData['extralaboral_total'] ?? ['nivel' => 'sin_riesgo', 'promedio' => 0]; ?>
        <div style="display: table-cell; width: 50%; vertical-align: middle; text-align: center; padding: 12px; background: <?= getGeneralHeatmapColor($extraData['nivel']) ?>; color: <?= getGeneralHeatmapTextColor($extraData['nivel']) ?>; border-right: 2px solid #333; font-weight: bold; font-size: 10pt;">
            FACTORES EXTRALABORALES<br>
            <span style="font-size: 14pt;"><?= number_format($extraData['promedio'], 1) ?></span>
        </div>
        <div style="display: table-cell; width: 50%; vertical-align: top;">
            <?php
            $dimExtras = [
                ['key' => 'dim_tiempo_fuera', 'nombre' => 'Tiempo fuera del trabajo'],
                ['key' => 'dim_relaciones_familiares_extra', 'nombre' => 'Relaciones familiares'],
                ['key' => 'dim_comunicacion', 'nombre' => 'Comunicación y relaciones interpersonales'],
                ['key' => 'dim_situacion_economica', 'nombre' => 'Situación económica del grupo familiar'],
                ['key' => 'dim_caracteristicas_vivienda', 'nombre' => 'Características de la vivienda'],
                ['key' => 'dim_influencia_entorno_extra', 'nombre' => 'Influencia del entorno extralaboral'],
                ['key' => 'dim_desplazamiento', 'nombre' => 'Desplazamiento vivienda trabajo'],
            ];
            foreach ($dimExtras as $dimIndex => $dimension):
                $dimData = $heatmapData[$dimension['key']] ?? ['nivel' => 'sin_riesgo', 'promedio' => 0];
            ?>
            <div style="padding: 3px 8px; background: <?= getGeneralHeatmapColor($dimData['nivel']) ?>; color: <?= getGeneralHeatmapTextColor($dimData['nivel']) ?>; border-bottom: <?= $dimIndex < count($dimExtras) - 1 ? '1px solid rgba(255,255,255,0.3)' : 'none' ?>; font-size: 8pt; overflow: hidden;">
                <span style="float: left;"><?= $dimension['nombre'] ?></span>
                <span style="float: right; font-weight: bold;"><?= number_format($dimData['promedio'], 1) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ESTRÉS -->
    <?php $estresData = $heatmapData['estres_total'] ?? ['nivel' => 'sin_riesgo', 'promedio' => 0]; ?>
    <div style="text-align: center; padding: 15px; background: <?= getGeneralHeatmapColor($estresData['nivel']) ?>; color: <?= getGeneralHeatmapTextColor($estresData['nivel']) ?>; font-weight: bold; font-size: 10pt;">
        SÍNTOMAS DE ESTRÉS<br>
        <span style="font-size: 16pt;"><?= number_format($estresData['promedio'], 1) ?></span>
    </div>
</div>

<?php else: ?>
<p style="text-align: center; color: #999; font-style: italic; padding: 40px 0;">
    No hay datos disponibles para mostrar el mapa de calor.
</p>
<?php endif; ?>

<?= $this->include('pdf/_partials/footer') ?>
<div class="page-break"></div>
