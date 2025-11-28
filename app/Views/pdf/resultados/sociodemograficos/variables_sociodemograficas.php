<!-- PÁGINA: VARIABLES SOCIODEMOGRÁFICAS -->
<?= $this->include('pdf/_partials/header') ?>

<h2 class="section-title" style="color: #006699; text-align: center; text-decoration: underline; margin-bottom: 20px;">
    Variables Sociodemográficas
</h2>

<?php
// Función helper para calcular porcentaje
if (!function_exists('calcPorcentaje')) {
    function calcPorcentaje($cantidad, $total) {
        return $total > 0 ? round(($cantidad / $total) * 100, 2) : 0;
    }
}

// Verificar si hay secciones guardadas
$hasSections = !empty($sections) && is_array($sections);
?>

<table style="width: 100%; border-collapse: collapse; font-size: 9pt;">
    <!-- SEXO -->
    <tr>
        <td style="width: 50%; padding: 10px; vertical-align: top; border: 1px solid #ddd;">
            <?php if ($hasSections && !empty($sections['sexo'])): ?>
            <p style="text-align: justify; margin: 0; line-height: 1.6;">
                <?= nl2br(esc($sections['sexo'])) ?>
            </p>
            <?php else: ?>
            <?php
            $sexoData = $socioData['sexo'] ?? [];
            if (!empty($sexoData)):
                usort($sexoData, function($a, $b) { return $b['cantidad'] - $a['cantidad']; });
                $mayorSexo = $sexoData[0] ?? null;
                $menorSexo = count($sexoData) > 1 ? $sexoData[1] : null;
            ?>
            <p style="text-align: justify; margin: 0;">
                El <?= $mayorSexo ? calcPorcentaje($mayorSexo['cantidad'], $totalParticipantes) : 0 ?>% de los empleados son
                <?= strtolower($mayorSexo['valor'] ?? 'N/A') == 'femenino' ? 'mujeres' : 'hombres' ?> y el
                <?= $menorSexo ? calcPorcentaje($menorSexo['cantidad'], $totalParticipantes) : 0 ?>%
                restante corresponde a <?= strtolower($menorSexo['valor'] ?? 'N/A') == 'femenino' ? 'mujeres' : 'hombres' ?>.
            </p>
            <?php endif; ?>
            <?php endif; ?>
        </td>
        <td style="width: 50%; padding: 10px; vertical-align: top; border: 1px solid #ddd;">
            <table style="width: 100%; border-collapse: collapse; font-size: 8pt;">
                <tr style="background-color: #f0f0f0;">
                    <th style="border: 1px solid #ccc; padding: 5px;">SEXO</th>
                    <th style="border: 1px solid #ccc; padding: 5px;">CANTIDAD</th>
                    <th style="border: 1px solid #ccc; padding: 5px;">PARTICIPACIÓN</th>
                </tr>
                <?php foreach ($socioData['sexo'] ?? [] as $item): ?>
                <tr>
                    <td style="border: 1px solid #ccc; padding: 5px;"><?= esc($item['valor']) ?></td>
                    <td style="border: 1px solid #ccc; padding: 5px; text-align: center;"><?= $item['cantidad'] ?></td>
                    <td style="border: 1px solid #ccc; padding: 5px; text-align: center;"><?= calcPorcentaje($item['cantidad'], $totalParticipantes) ?>%</td>
                </tr>
                <?php endforeach; ?>
            </table>
        </td>
    </tr>

    <!-- RANGO DE EDAD -->
    <tr>
        <td style="padding: 10px; vertical-align: top; border: 1px solid #ddd;">
            <?php if ($hasSections && !empty($sections['edad'])): ?>
            <p style="text-align: justify; margin: 0; line-height: 1.6;">
                <?= nl2br(esc($sections['edad'])) ?>
            </p>
            <?php else: ?>
            <?php
            $edadData = $socioData['edad'] ?? [];
            if (!empty($edadData)):
                usort($edadData, function($a, $b) { return $b['cantidad'] - $a['cantidad']; });
            ?>
            <p style="text-align: justify; margin: 0;">
                La composición por edades demuestra que la mayoría
                <?php foreach (array_slice($edadData, 0, 2) as $i => $edad): ?>
                    <?= $i > 0 ? ' y ' : '' ?>
                    <?= calcPorcentaje($edad['cantidad'], $totalParticipantes) ?>% en <?= strtolower($edad['valor']) ?>
                <?php endforeach; ?>.
            </p>
            <?php endif; ?>
            <?php endif; ?>
        </td>
        <td style="padding: 10px; vertical-align: top; border: 1px solid #ddd;">
            <table style="width: 100%; border-collapse: collapse; font-size: 8pt;">
                <tr style="background-color: #f0f0f0;">
                    <th style="border: 1px solid #ccc; padding: 5px;">RANGO DE EDAD</th>
                    <th style="border: 1px solid #ccc; padding: 5px;">CANTIDAD</th>
                    <th style="border: 1px solid #ccc; padding: 5px;">PARTICIPACIÓN</th>
                </tr>
                <?php foreach ($socioData['edad'] ?? [] as $item): ?>
                <tr>
                    <td style="border: 1px solid #ccc; padding: 5px;"><?= esc($item['valor']) ?></td>
                    <td style="border: 1px solid #ccc; padding: 5px; text-align: center;"><?= $item['cantidad'] ?></td>
                    <td style="border: 1px solid #ccc; padding: 5px; text-align: center;"><?= calcPorcentaje($item['cantidad'], $totalParticipantes) ?>%</td>
                </tr>
                <?php endforeach; ?>
            </table>
        </td>
    </tr>

    <!-- ESTADO CIVIL -->
    <tr>
        <td style="padding: 10px; vertical-align: top; border: 1px solid #ddd;">
            <?php if ($hasSections && !empty($sections['estado_civil'])): ?>
            <p style="text-align: justify; margin: 0; line-height: 1.6;">
                <?= nl2br(esc($sections['estado_civil'])) ?>
            </p>
            <?php else: ?>
            <?php
            $estadoCivilData = $socioData['estado_civil'] ?? [];
            if (!empty($estadoCivilData)):
                usort($estadoCivilData, function($a, $b) { return $b['cantidad'] - $a['cantidad']; });
            ?>
            <p style="text-align: justify; margin: 0;">
                El estado civil de los trabajadores:
                <?php foreach ($estadoCivilData as $i => $ec): ?>
                    <?= $i > 0 ? ', ' : '' ?><?= calcPorcentaje($ec['cantidad'], $totalParticipantes) ?>% <?= strtolower($ec['valor']) ?>
                <?php endforeach; ?>.
            </p>
            <?php endif; ?>
            <?php endif; ?>
        </td>
        <td style="padding: 10px; vertical-align: top; border: 1px solid #ddd;">
            <table style="width: 100%; border-collapse: collapse; font-size: 8pt;">
                <tr style="background-color: #f0f0f0;">
                    <th style="border: 1px solid #ccc; padding: 5px;">ESTADO CIVIL</th>
                    <th style="border: 1px solid #ccc; padding: 5px;">CANTIDAD</th>
                    <th style="border: 1px solid #ccc; padding: 5px;">PARTICIPACIÓN</th>
                </tr>
                <?php foreach ($socioData['estado_civil'] ?? [] as $item): ?>
                <tr>
                    <td style="border: 1px solid #ccc; padding: 5px;"><?= esc($item['valor']) ?></td>
                    <td style="border: 1px solid #ccc; padding: 5px; text-align: center;"><?= $item['cantidad'] ?></td>
                    <td style="border: 1px solid #ccc; padding: 5px; text-align: center;"><?= calcPorcentaje($item['cantidad'], $totalParticipantes) ?>%</td>
                </tr>
                <?php endforeach; ?>
            </table>
        </td>
    </tr>

    <!-- NIVEL DE ESCOLARIDAD -->
    <tr>
        <td style="padding: 10px; vertical-align: top; border: 1px solid #ddd;">
            <?php if ($hasSections && !empty($sections['educacion'])): ?>
            <p style="text-align: justify; margin: 0; line-height: 1.6;">
                <?= nl2br(esc($sections['educacion'])) ?>
            </p>
            <?php else: ?>
            <?php
            $escolaridadData = $socioData['escolaridad'] ?? [];
            if (!empty($escolaridadData)):
                usort($escolaridadData, function($a, $b) { return $b['cantidad'] - $a['cantidad']; });
            ?>
            <p style="text-align: justify; margin: 0;">
                Nivel de escolaridad:
                <?php foreach (array_slice($escolaridadData, 0, 2) as $i => $esc): ?>
                    <?= $i > 0 ? ', ' : '' ?><?= strtolower($esc['valor']) ?>
                    (<?= calcPorcentaje($esc['cantidad'], $totalParticipantes) ?>%)
                <?php endforeach; ?>.
            </p>
            <?php endif; ?>
            <?php endif; ?>
        </td>
        <td style="padding: 10px; vertical-align: top; border: 1px solid #ddd;">
            <table style="width: 100%; border-collapse: collapse; font-size: 8pt;">
                <tr style="background-color: #f0f0f0;">
                    <th style="border: 1px solid #ccc; padding: 5px;">ESCOLARIDAD</th>
                    <th style="border: 1px solid #ccc; padding: 5px;">CANTIDAD</th>
                    <th style="border: 1px solid #ccc; padding: 5px;">PARTICIPACIÓN</th>
                </tr>
                <?php foreach ($socioData['escolaridad'] ?? [] as $item): ?>
                <tr>
                    <td style="border: 1px solid #ccc; padding: 5px;"><?= esc($item['valor']) ?></td>
                    <td style="border: 1px solid #ccc; padding: 5px; text-align: center;"><?= $item['cantidad'] ?></td>
                    <td style="border: 1px solid #ccc; padding: 5px; text-align: center;"><?= calcPorcentaje($item['cantidad'], $totalParticipantes) ?>%</td>
                </tr>
                <?php endforeach; ?>
            </table>
        </td>
    </tr>

    <!-- ESTRATO -->
    <tr>
        <td style="padding: 10px; vertical-align: top; border: 1px solid #ddd;">
            <?php if ($hasSections && !empty($sections['estrato'])): ?>
            <p style="text-align: justify; margin: 0; line-height: 1.6;">
                <?= nl2br(esc($sections['estrato'])) ?>
            </p>
            <?php else: ?>
            <?php
            $estratoData = $socioData['estrato'] ?? [];
            if (!empty($estratoData)):
                usort($estratoData, function($a, $b) { return $b['cantidad'] - $a['cantidad']; });
            ?>
            <p style="text-align: justify; margin: 0;">
                Estrato socioeconómico:
                <?php foreach ($estratoData as $i => $est): ?>
                    <?= $i > 0 ? ', ' : '' ?>estrato <?= $est['valor'] ?>
                    (<?= calcPorcentaje($est['cantidad'], $totalParticipantes) ?>%)
                <?php endforeach; ?>.
            </p>
            <?php endif; ?>
            <?php endif; ?>
        </td>
        <td style="padding: 10px; vertical-align: top; border: 1px solid #ddd;">
            <table style="width: 100%; border-collapse: collapse; font-size: 8pt;">
                <tr style="background-color: #f0f0f0;">
                    <th style="border: 1px solid #ccc; padding: 5px;">ESTRATO</th>
                    <th style="border: 1px solid #ccc; padding: 5px;">CANTIDAD</th>
                    <th style="border: 1px solid #ccc; padding: 5px;">PARTICIPACIÓN</th>
                </tr>
                <?php foreach ($socioData['estrato'] ?? [] as $item): ?>
                <tr>
                    <td style="border: 1px solid #ccc; padding: 5px;"><?= esc($item['valor']) ?></td>
                    <td style="border: 1px solid #ccc; padding: 5px; text-align: center;"><?= $item['cantidad'] ?></td>
                    <td style="border: 1px solid #ccc; padding: 5px; text-align: center;"><?= calcPorcentaje($item['cantidad'], $totalParticipantes) ?>%</td>
                </tr>
                <?php endforeach; ?>
            </table>
        </td>
    </tr>

    <!-- TIPO DE VIVIENDA -->
    <tr>
        <td style="padding: 10px; vertical-align: top; border: 1px solid #ddd;">
            <?php if ($hasSections && !empty($sections['vivienda'])): ?>
            <p style="text-align: justify; margin: 0; line-height: 1.6;">
                <?= nl2br(esc($sections['vivienda'])) ?>
            </p>
            <?php else: ?>
            <?php
            $viviendaData = $socioData['vivienda'] ?? [];
            if (!empty($viviendaData)):
                usort($viviendaData, function($a, $b) { return $b['cantidad'] - $a['cantidad']; });
            ?>
            <p style="text-align: justify; margin: 0;">
                Tipo de vivienda:
                <?php foreach ($viviendaData as $i => $viv): ?>
                    <?= $i > 0 ? ', ' : '' ?><?= strtolower($viv['valor']) ?>
                    (<?= calcPorcentaje($viv['cantidad'], $totalParticipantes) ?>%)
                <?php endforeach; ?>.
            </p>
            <?php endif; ?>
            <?php endif; ?>
        </td>
        <td style="padding: 10px; vertical-align: top; border: 1px solid #ddd;">
            <table style="width: 100%; border-collapse: collapse; font-size: 8pt;">
                <tr style="background-color: #f0f0f0;">
                    <th style="border: 1px solid #ccc; padding: 5px;">TIPO DE VIVIENDA</th>
                    <th style="border: 1px solid #ccc; padding: 5px;">CANTIDAD</th>
                    <th style="border: 1px solid #ccc; padding: 5px;">PARTICIPACIÓN</th>
                </tr>
                <?php foreach ($socioData['vivienda'] ?? [] as $item): ?>
                <tr>
                    <td style="border: 1px solid #ccc; padding: 5px;"><?= esc($item['valor']) ?></td>
                    <td style="border: 1px solid #ccc; padding: 5px; text-align: center;"><?= $item['cantidad'] ?></td>
                    <td style="border: 1px solid #ccc; padding: 5px; text-align: center;"><?= calcPorcentaje($item['cantidad'], $totalParticipantes) ?>%</td>
                </tr>
                <?php endforeach; ?>
            </table>
        </td>
    </tr>
</table>

<?php if (!$hasSections): ?>
<p style="text-align: center; color: #999; font-style: italic; margin-top: 20px;">
    No hay interpretación IA guardada. Genere y guarde la interpretación desde el módulo de Ficha de Datos Generales.
</p>
<?php endif; ?>

<?= $this->include('pdf/_partials/footer') ?>
<div class="page-break"></div>
