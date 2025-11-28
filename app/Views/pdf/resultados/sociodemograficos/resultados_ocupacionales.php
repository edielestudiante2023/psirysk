<!-- PÁGINA: RESULTADOS OCUPACIONALES -->
<?= $this->include('pdf/_partials/header') ?>

<h2 class="section-title" style="color: #006699; text-align: center; text-decoration: underline; margin-bottom: 20px;">
    Resultados Ocupacionales
</h2>

<?php
// Función helper para calcular porcentaje (si no está definida)
if (!function_exists('calcPorcentaje')) {
    function calcPorcentaje($cantidad, $total) {
        return $total > 0 ? round(($cantidad / $total) * 100, 2) : 0;
    }
}

// Verificar si hay secciones IA guardadas
$hasSections = !empty($sections) && is_array($sections);
?>

<table style="width: 100%; border-collapse: collapse; font-size: 9pt;">
    <!-- ANTIGÜEDAD EN LA EMPRESA -->
    <tr>
        <td style="width: 50%; padding: 10px; vertical-align: top; border: 1px solid #ddd;">
            <?php if ($hasSections && !empty($sections['antiguedad_empresa'])): ?>
            <p style="text-align: justify; margin: 0; line-height: 1.6;">
                <?= nl2br(esc($sections['antiguedad_empresa'])) ?>
            </p>
            <?php else: ?>
            <?php
            $antiguedadData = $ocupData['antiguedad'] ?? [];
            if (!empty($antiguedadData)):
                usort($antiguedadData, function($a, $b) { return $b['cantidad'] - $a['cantidad']; });
            ?>
            <p style="text-align: justify; margin: 0;">
                La mayoría de los colaboradores cuenta con una antigüedad
                <?php foreach ($antiguedadData as $i => $ant):
                    $pct = calcPorcentaje($ant['cantidad'], $totalParticipantes);
                ?>
                    <?= $i > 0 ? ($i == count($antiguedadData) - 1 ? ' y otro ' : ', ') : '' ?>
                    <?= $i == 0 ? 'entre ' : '' ?><?= strtolower($ant['valor']) ?> (<?= $pct ?>%)
                <?php endforeach; ?>.
            </p>
            <?php endif; ?>
            <?php endif; ?>
        </td>
        <td style="width: 50%; padding: 10px; vertical-align: top; border: 1px solid #ddd;">
            <table style="width: 100%; border-collapse: collapse; font-size: 8pt;">
                <tr style="background-color: #f0f0f0;">
                    <th style="border: 1px solid #ccc; padding: 5px;">ANTIGÜEDAD</th>
                    <th style="border: 1px solid #ccc; padding: 5px;">CANTIDAD</th>
                    <th style="border: 1px solid #ccc; padding: 5px;">PARTICIPACIÓN</th>
                </tr>
                <?php foreach ($ocupData['antiguedad'] ?? [] as $item): ?>
                <tr>
                    <td style="border: 1px solid #ccc; padding: 5px;"><?= esc($item['valor']) ?></td>
                    <td style="border: 1px solid #ccc; padding: 5px; text-align: center;"><?= $item['cantidad'] ?></td>
                    <td style="border: 1px solid #ccc; padding: 5px; text-align: center;"><?= calcPorcentaje($item['cantidad'], $totalParticipantes) ?>%</td>
                </tr>
                <?php endforeach; ?>
            </table>
        </td>
    </tr>

    <!-- TIPO DE CONTRATO -->
    <tr>
        <td style="padding: 10px; vertical-align: top; border: 1px solid #ddd;">
            <?php if ($hasSections && !empty($sections['contrato'])): ?>
            <p style="text-align: justify; margin: 0; line-height: 1.6;">
                <?= nl2br(esc($sections['contrato'])) ?>
            </p>
            <?php else: ?>
            <?php
            $contratoData = $ocupData['tipo_contrato'] ?? [];
            if (!empty($contratoData)):
                usort($contratoData, function($a, $b) { return $b['cantidad'] - $a['cantidad']; });
                $contratoMayor = $contratoData[0] ?? null;
            ?>
            <p style="text-align: justify; margin: 0;">
                <?php if (count($contratoData) == 1): ?>
                    Todos los colaboradores cuentan con contratos a <?= strtolower($contratoMayor['valor']) ?>.
                <?php else: ?>
                    La mayoría de los colaboradores (<?= calcPorcentaje($contratoMayor['cantidad'], $totalParticipantes) ?>%)
                    cuenta con contrato <?= strtolower($contratoMayor['valor']) ?>.
                <?php endif; ?>
            </p>
            <?php endif; ?>
            <?php endif; ?>
        </td>
        <td style="padding: 10px; vertical-align: top; border: 1px solid #ddd;">
            <table style="width: 100%; border-collapse: collapse; font-size: 8pt;">
                <tr style="background-color: #f0f0f0;">
                    <th style="border: 1px solid #ccc; padding: 5px;">TIPO DE CONTRATO</th>
                    <th style="border: 1px solid #ccc; padding: 5px;">CANTIDAD</th>
                    <th style="border: 1px solid #ccc; padding: 5px;">PARTICIPACIÓN</th>
                </tr>
                <?php foreach ($ocupData['tipo_contrato'] ?? [] as $item): ?>
                <tr>
                    <td style="border: 1px solid #ccc; padding: 5px;"><?= esc($item['valor']) ?></td>
                    <td style="border: 1px solid #ccc; padding: 5px; text-align: center;"><?= $item['cantidad'] ?></td>
                    <td style="border: 1px solid #ccc; padding: 5px; text-align: center;"><?= calcPorcentaje($item['cantidad'], $totalParticipantes) ?>%</td>
                </tr>
                <?php endforeach; ?>
            </table>
        </td>
    </tr>

    <!-- TIPO DE CARGO -->
    <tr>
        <td style="padding: 10px; vertical-align: top; border: 1px solid #ddd;">
            <?php if ($hasSections && !empty($sections['cargo'])): ?>
            <p style="text-align: justify; margin: 0; line-height: 1.6;">
                <?= nl2br(esc($sections['cargo'])) ?>
            </p>
            <?php else: ?>
            <?php
            $cargoData = $ocupData['tipo_cargo'] ?? [];
            if (!empty($cargoData)):
                usort($cargoData, function($a, $b) { return $b['cantidad'] - $a['cantidad']; });
            ?>
            <p style="text-align: justify; margin: 0;">
                En cuanto al tipo de cargo,
                <?php foreach ($cargoData as $i => $cargo):
                    $pct = calcPorcentaje($cargo['cantidad'], $totalParticipantes);
                ?>
                    <?= $i > 0 ? ($i == count($cargoData) - 1 ? ' y ' : ', ') : '' ?>
                    <?= $pct ?>% <?= $i == 0 ? 'corresponde a ' : '' ?><?= strtolower($cargo['valor']) ?>
                <?php endforeach; ?>.
            </p>
            <?php endif; ?>
            <?php endif; ?>
        </td>
        <td style="padding: 10px; vertical-align: top; border: 1px solid #ddd;">
            <table style="width: 100%; border-collapse: collapse; font-size: 8pt;">
                <tr style="background-color: #f0f0f0;">
                    <th style="border: 1px solid #ccc; padding: 5px;">TIPO DE CARGO</th>
                    <th style="border: 1px solid #ccc; padding: 5px;">CANTIDAD</th>
                    <th style="border: 1px solid #ccc; padding: 5px;">PARTICIPACIÓN</th>
                </tr>
                <?php foreach ($ocupData['tipo_cargo'] ?? [] as $item): ?>
                <tr>
                    <td style="border: 1px solid #ccc; padding: 5px;"><?= esc($item['valor']) ?></td>
                    <td style="border: 1px solid #ccc; padding: 5px; text-align: center;"><?= $item['cantidad'] ?></td>
                    <td style="border: 1px solid #ccc; padding: 5px; text-align: center;"><?= calcPorcentaje($item['cantidad'], $totalParticipantes) ?>%</td>
                </tr>
                <?php endforeach; ?>
            </table>
        </td>
    </tr>

    <?php if (!empty($ocupData['departamento']) || ($hasSections && !empty($sections['area']))): ?>
    <!-- DEPARTAMENTO/ÁREA -->
    <tr>
        <td style="padding: 10px; vertical-align: top; border: 1px solid #ddd;">
            <?php if ($hasSections && !empty($sections['area'])): ?>
            <p style="text-align: justify; margin: 0; line-height: 1.6;">
                <?= nl2br(esc($sections['area'])) ?>
            </p>
            <?php else: ?>
            <?php
            $deptData = $ocupData['departamento'];
            usort($deptData, function($a, $b) { return $b['cantidad'] - $a['cantidad']; });
            ?>
            <p style="text-align: justify; margin: 0;">
                Los colaboradores se distribuyen en las siguientes áreas:
                <?php foreach ($deptData as $i => $dept):
                    $pct = calcPorcentaje($dept['cantidad'], $totalParticipantes);
                ?>
                    <?= $i > 0 ? ', ' : '' ?><?= $dept['valor'] ?> (<?= $pct ?>%)
                <?php endforeach; ?>.
            </p>
            <?php endif; ?>
        </td>
        <td style="padding: 10px; vertical-align: top; border: 1px solid #ddd;">
            <table style="width: 100%; border-collapse: collapse; font-size: 8pt;">
                <tr style="background-color: #f0f0f0;">
                    <th style="border: 1px solid #ccc; padding: 5px;">DEPARTAMENTO/ÁREA</th>
                    <th style="border: 1px solid #ccc; padding: 5px;">CANTIDAD</th>
                    <th style="border: 1px solid #ccc; padding: 5px;">PARTICIPACIÓN</th>
                </tr>
                <?php foreach ($ocupData['departamento'] ?? [] as $item): ?>
                <tr>
                    <td style="border: 1px solid #ccc; padding: 5px;"><?= esc($item['valor']) ?></td>
                    <td style="border: 1px solid #ccc; padding: 5px; text-align: center;"><?= $item['cantidad'] ?></td>
                    <td style="border: 1px solid #ccc; padding: 5px; text-align: center;"><?= calcPorcentaje($item['cantidad'], $totalParticipantes) ?>%</td>
                </tr>
                <?php endforeach; ?>
            </table>
        </td>
    </tr>
    <?php endif; ?>

    <?php if (!empty($ocupData['horas']) || ($hasSections && !empty($sections['horas']))): ?>
    <!-- HORAS DE TRABAJO -->
    <tr>
        <td style="padding: 10px; vertical-align: top; border: 1px solid #ddd;">
            <?php if ($hasSections && !empty($sections['horas'])): ?>
            <p style="text-align: justify; margin: 0; line-height: 1.6;">
                <?= nl2br(esc($sections['horas'])) ?>
            </p>
            <?php else: ?>
            <?php
            $horasData = $ocupData['horas'] ?? [];
            if (!empty($horasData)):
                usort($horasData, function($a, $b) { return $b['cantidad'] - $a['cantidad']; });
            ?>
            <p style="text-align: justify; margin: 0;">
                Distribución de horas de trabajo diarias:
                <?php foreach ($horasData as $i => $hora):
                    $pct = calcPorcentaje($hora['cantidad'], $totalParticipantes);
                ?>
                    <?= $i > 0 ? ', ' : '' ?><?= $hora['valor'] ?> (<?= $pct ?>%)
                <?php endforeach; ?>.
            </p>
            <?php endif; ?>
            <?php endif; ?>
        </td>
        <td style="padding: 10px; vertical-align: top; border: 1px solid #ddd;">
            <table style="width: 100%; border-collapse: collapse; font-size: 8pt;">
                <tr style="background-color: #f0f0f0;">
                    <th style="border: 1px solid #ccc; padding: 5px;">HORAS DE TRABAJO</th>
                    <th style="border: 1px solid #ccc; padding: 5px;">CANTIDAD</th>
                    <th style="border: 1px solid #ccc; padding: 5px;">PARTICIPACIÓN</th>
                </tr>
                <?php foreach ($ocupData['horas'] ?? [] as $item): ?>
                <tr>
                    <td style="border: 1px solid #ccc; padding: 5px;"><?= esc($item['valor']) ?></td>
                    <td style="border: 1px solid #ccc; padding: 5px; text-align: center;"><?= $item['cantidad'] ?></td>
                    <td style="border: 1px solid #ccc; padding: 5px; text-align: center;"><?= calcPorcentaje($item['cantidad'], $totalParticipantes) ?>%</td>
                </tr>
                <?php endforeach; ?>
            </table>
        </td>
    </tr>
    <?php endif; ?>

    <?php if (!empty($ocupData['salario']) || ($hasSections && !empty($sections['salario']))): ?>
    <!-- RANGO SALARIAL -->
    <tr>
        <td style="padding: 10px; vertical-align: top; border: 1px solid #ddd;">
            <?php if ($hasSections && !empty($sections['salario'])): ?>
            <p style="text-align: justify; margin: 0; line-height: 1.6;">
                <?= nl2br(esc($sections['salario'])) ?>
            </p>
            <?php else: ?>
            <?php
            $salarioData = $ocupData['salario'] ?? [];
            if (!empty($salarioData)):
                usort($salarioData, function($a, $b) { return $b['cantidad'] - $a['cantidad']; });
            ?>
            <p style="text-align: justify; margin: 0;">
                Distribución por rango salarial:
                <?php foreach ($salarioData as $i => $sal):
                    $pct = calcPorcentaje($sal['cantidad'], $totalParticipantes);
                ?>
                    <?= $i > 0 ? ', ' : '' ?><?= $sal['valor'] ?> (<?= $pct ?>%)
                <?php endforeach; ?>.
            </p>
            <?php endif; ?>
            <?php endif; ?>
        </td>
        <td style="padding: 10px; vertical-align: top; border: 1px solid #ddd;">
            <table style="width: 100%; border-collapse: collapse; font-size: 8pt;">
                <tr style="background-color: #f0f0f0;">
                    <th style="border: 1px solid #ccc; padding: 5px;">RANGO SALARIAL</th>
                    <th style="border: 1px solid #ccc; padding: 5px;">CANTIDAD</th>
                    <th style="border: 1px solid #ccc; padding: 5px;">PARTICIPACIÓN</th>
                </tr>
                <?php foreach ($ocupData['salario'] ?? [] as $item): ?>
                <tr>
                    <td style="border: 1px solid #ccc; padding: 5px;"><?= esc($item['valor']) ?></td>
                    <td style="border: 1px solid #ccc; padding: 5px; text-align: center;"><?= $item['cantidad'] ?></td>
                    <td style="border: 1px solid #ccc; padding: 5px; text-align: center;"><?= calcPorcentaje($item['cantidad'], $totalParticipantes) ?>%</td>
                </tr>
                <?php endforeach; ?>
            </table>
        </td>
    </tr>
    <?php endif; ?>
</table>

<?php if (!$hasSections): ?>
<p style="text-align: center; color: #999; font-style: italic; margin-top: 20px;">
    No hay interpretación IA guardada. Genere y guarde la interpretación desde el módulo de Ficha de Datos Generales.
</p>
<?php endif; ?>

<?= $this->include('pdf/_partials/footer') ?>
<div class="page-break"></div>
