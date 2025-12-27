<?php
$file = 'app/Views/reports/intralaboral/dashboard.php';
$content = file_get_contents($file);

// Reemplazar formatMaxRisk por formatMaxRiskHTML y formatMaxRiskTooltip en los otros 3 dominios
$content = preg_replace(
    '/title="<\?= formatMaxRisk\(\$stats\[\'maxRisk\'\]\[(\'control\'|\'demandas\'|\'recompensas\')\] \?\? \[\], true\) \?>"/m',
    'title="<?= formatMaxRiskTooltip($stats[\'maxRisk\'][$1] ?? []) ?>"',
    $content
);

$content = preg_replace(
    '/<\?= formatMaxRisk\(\$stats\[\'maxRisk\'\]\[(\'control\'|\'demandas\'|\'recompensas\')\] \?\? \[\]\) \?>/m',
    '<?= formatMaxRiskHTML($stats[\'maxRisk\'][$1] ?? []) ?>',
    $content
);

file_put_contents($file, $content);
echo "✅ Cards de dominios actualizados\n";
