<?php
/**
 * Script para actualizar referencias de dimensiones en dashboard intralaboral
 * Cambia de dimensionAverages/dimensionLevels a maxRisk
 */

$file = 'app/Views/reports/intralaboral/dashboard.php';
$content = file_get_contents($file);

// Patrón 1: Cambiar getBadgeClass($stats['dimensionLevels']['dim_xxx']['nivel']
// a getBadgeClass($stats['maxRisk']['dim_xxx']['nivel']
$content = preg_replace(
    '/\$stats\[\'dimensionLevels\'\]\[(\'dim_[a-z_]+\')\]\[\'nivel\'\]/',
    '$stats[\'maxRisk\'][$1][\'nivel\']',
    $content
);

// Patrón 2: Cambiar number_format($stats['dimensionAverages']['dim_xxx'], 1)
// a number_format($stats['maxRisk']['dim_xxx']['promedio'] ?? 0, 1)
$content = preg_replace(
    '/number_format\(\$stats\[\'dimensionAverages\'\]\[(\'dim_[a-z_]+\')\],\s*1\)/',
    'number_format($stats[\'maxRisk\'][$1][\'promedio\'] ?? 0, 1)',
    $content
);

// Patrón 3: Cambiar $stats['dimensionLevels']['dim_xxx']['label']
// a getRiskLabel($stats['maxRisk']['dim_xxx']['nivel'] ?? 'sin_riesgo')
$content = preg_replace(
    '/\$stats\[\'dimensionLevels\'\]\[(\'dim_[a-z_]+\')\]\[\'label\'\]/',
    'getRiskLabel($stats[\'maxRisk\'][$1][\'nivel\'] ?? \'sin_riesgo\')',
    $content
);

file_put_contents($file, $content);

echo "✅ Archivo actualizado: $file\n";
echo "Cambios realizados:\n";
echo "- dimensionLevels['dim_xxx']['nivel'] → maxRisk['dim_xxx']['nivel']\n";
echo "- dimensionAverages['dim_xxx'] → maxRisk['dim_xxx']['promedio']\n";
echo "- dimensionLevels['dim_xxx']['label'] → getRiskLabel(maxRisk['dim_xxx']['nivel'])\n";
