<!-- PÁGINA: MAPA DE CALOR INTRALABORAL FORMA B -->
<?= $this->include('pdf/_partials/header') ?>

<h2 class="section-title" style="color: #006699; text-align: center; text-decoration: underline; margin-bottom: 15px;">
    Mapa de Calor Intralaboral - Forma B
</h2>

<p style="text-align: center; font-size: 9pt; color: #666; margin-bottom: 15px;">
    Auxiliares y Operarios | <?= $totalTrabajadores ?> trabajadores evaluados
</p>

<?php
if (!function_exists('getHeatmapColorB')) {
    function getHeatmapColorB($nivel) {
        $colores = [
            'sin_riesgo' => '#4CAF50', 'riesgo_bajo' => '#8BC34A', 'riesgo_medio' => '#FFC107',
            'riesgo_alto' => '#FF9800', 'riesgo_muy_alto' => '#F44336',
        ];
        return $colores[$nivel] ?? '#9E9E9E';
    }
}
if (!function_exists('getHeatmapTextColorB')) {
    function getHeatmapTextColorB($nivel) {
        return $nivel === 'riesgo_medio' ? '#333' : '#fff';
    }
}
if (!function_exists('getNivelTextoB')) {
    function getNivelTextoB($nivel) {
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

<!-- Mapa de Calor -->
<div style="border: 2px solid #333; background: #fff;">
    <div style="display: table; width: 100%;">
        <!-- TOTAL INTRALABORAL -->
        <?php $totalData = $calculations['intralaboral_total'] ?? ['nivel' => 'sin_riesgo', 'promedio' => 0]; ?>
        <div style="display: table-cell; width: 18%; vertical-align: middle; text-align: center; padding: 10px; background: <?= getHeatmapColorB($totalData['nivel']) ?>; color: <?= getHeatmapTextColorB($totalData['nivel']) ?>; border-right: 2px solid #333; font-weight: bold; font-size: 8pt;">
            TOTAL<br>INTRALABORAL<br>
            <span style="font-size: 16pt;"><?= number_format($totalData['promedio'], 1) ?></span><br>
            <span style="font-size: 7pt;"><?= getNivelTextoB($totalData['nivel']) ?></span>
        </div>

        <!-- DOMINIOS Y DIMENSIONES - Forma B tiene 16 dimensiones -->
        <div style="display: table-cell; width: 82%; vertical-align: top;">
            <?php
            // Forma B: 4 dominios con 16 dimensiones totales
            // NO incluye: "Relación con los colaboradores", "Exigencias responsabilidad cargo", "Consistencia del rol"
            $dominios = [
                [
                    'nombre' => 'LIDERAZGO Y RELACIONES SOCIALES',
                    'key' => 'dom_liderazgo',
                    'dimensiones' => [
                        ['key' => 'dim_caracteristicas_liderazgo', 'nombre' => 'Características del liderazgo'],
                        ['key' => 'dim_relaciones_sociales', 'nombre' => 'Relaciones sociales en el trabajo'],
                        ['key' => 'dim_retroalimentacion', 'nombre' => 'Retroalimentación del desempeño'],
                        // Forma B NO tiene "Relación con los colaboradores"
                    ]
                ],
                [
                    'nombre' => 'CONTROL SOBRE EL TRABAJO',
                    'key' => 'dom_control',
                    'dimensiones' => [
                        ['key' => 'dim_claridad_rol', 'nombre' => 'Claridad de rol'],
                        ['key' => 'dim_capacitacion', 'nombre' => 'Capacitación'],
                        ['key' => 'dim_participacion_manejo_cambio', 'nombre' => 'Participación y manejo del cambio'],
                        ['key' => 'dim_oportunidades_desarrollo', 'nombre' => 'Oportunidades desarrollo habilidades'],
                        ['key' => 'dim_control_autonomia', 'nombre' => 'Control y autonomía sobre el trabajo'],
                    ]
                ],
                [
                    'nombre' => 'DEMANDAS DEL TRABAJO',
                    'key' => 'dom_demandas',
                    'dimensiones' => [
                        ['key' => 'dim_demandas_ambientales', 'nombre' => 'Demandas ambientales y esfuerzo físico'],
                        ['key' => 'dim_demandas_emocionales', 'nombre' => 'Demandas emocionales'],
                        ['key' => 'dim_demandas_cuantitativas', 'nombre' => 'Demandas cuantitativas'],
                        ['key' => 'dim_influencia_trabajo_entorno_extralaboral', 'nombre' => 'Influencia trabajo sobre entorno extra'],
                        // Forma B NO tiene "Exigencias responsabilidad cargo"
                        ['key' => 'dim_carga_mental', 'nombre' => 'Demandas de carga mental'],
                        // Forma B NO tiene "Consistencia del rol"
                        ['key' => 'dim_demandas_jornada_trabajo', 'nombre' => 'Demandas de la jornada de trabajo'],
                    ]
                ],
                [
                    'nombre' => 'RECOMPENSAS',
                    'key' => 'dom_recompensas',
                    'dimensiones' => [
                        ['key' => 'dim_reconocimiento_compensacion', 'nombre' => 'Reconocimiento y compensación'],
                        ['key' => 'dim_recompensas_pertenencia', 'nombre' => 'Recompensas pertenencia organización'],
                    ]
                ],
            ];

            foreach ($dominios as $domIndex => $dominio):
                $domData = $calculations[$dominio['key']] ?? ['nivel' => 'sin_riesgo', 'promedio' => 0];
            ?>
            <div style="display: table; width: 100%; border-bottom: <?= $domIndex < count($dominios) - 1 ? '2px solid #333' : 'none' ?>;">
                <div style="display: table-cell; width: 28%; vertical-align: middle; text-align: center; padding: 6px; background: <?= getHeatmapColorB($domData['nivel']) ?>; color: <?= getHeatmapTextColorB($domData['nivel']) ?>; border-right: 1px solid #666; font-weight: bold; font-size: 7pt;">
                    <?= $dominio['nombre'] ?><br>
                    <span style="font-size: 12pt;"><?= number_format($domData['promedio'], 1) ?></span>
                </div>
                <?php
                // Obtener el color de la última dimensión para el fondo del contenedor
                $lastDim = end($dominio['dimensiones']);
                $lastDimKey = $lastDim['key'];
                $lastDimDataBg = $calculations[$lastDimKey] ?? ['nivel' => 'sin_riesgo', 'promedio' => 0];
                ?>
                <div style="display: table-cell; width: 72%; vertical-align: top; padding: 0; margin: 0; background-color: <?= getHeatmapColorB($lastDimDataBg['nivel']) ?>;">
                    <?php foreach ($dominio['dimensiones'] as $dimIndex => $dimension):
                        $dimData = $calculations[$dimension['key']] ?? ['nivel' => 'sin_riesgo', 'promedio' => 0];
                    ?>
                    <div style="padding: 3px 6px; background-color: <?= getHeatmapColorB($dimData['nivel']) ?>; color: <?= getHeatmapTextColorB($dimData['nivel']) ?>; border-bottom: <?= $dimIndex < count($dominio['dimensiones']) - 1 ? '1px solid rgba(0,0,0,0.2)' : 'none' ?>; font-size: 7pt; overflow: hidden; margin: 0;">
                        <span style="float: left;"><?= $dimension['nombre'] ?></span>
                        <span style="float: right; font-weight: bold;"><?= number_format($dimData['promedio'], 1) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Nota Metodológica -->
<div style="margin-top: 15px; padding: 10px; background: #fff3e0; border-left: 3px solid #FF9800; font-size: 8pt;">
    <strong>Nota:</strong> La Forma B contiene 16 dimensiones distribuidas en 4 dominios. Se aplica a auxiliares y operarios
    sin responsabilidad de coordinación. Baremos según Resolución 2404/2019.
</div>

<?php else: ?>
<p style="text-align: center; color: #999; font-style: italic; padding: 40px 0;">
    No hay datos de Forma B disponibles.
</p>
<?php endif; ?>

<?= $this->include('pdf/_partials/footer') ?>
<div class="page-break"></div>
