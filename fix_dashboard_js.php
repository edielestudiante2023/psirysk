<?php
/**
 * Script para actualizar referencias de dimensiones en JavaScript del dashboard intralaboral
 */

$file = 'app/Views/reports/intralaboral/dashboard.php';
$content = file_get_contents($file);

// Cambiar statsData.dimensionLevels?.[dim.key]?.nivel a statsData.maxRisk?.[dim.key]?.nivel
$content = str_replace(
    'statsData.dimensionLevels?.[dim.key]?.nivel',
    'statsData.maxRisk?.[dim.key]?.nivel',
    $content
);

// Cambiar statsData.dimensionAverages?.[dim.key] a statsData.maxRisk?.[dim.key]?.promedio
$content = str_replace(
    'statsData.dimensionAverages?.[dim.key]',
    'statsData.maxRisk?.[dim.key]?.promedio',
    $content
);

// Cambiar newStats.dimensionAverages[dim.key] a newStats.maxRisk[dim.key]?.promedio
$content = str_replace(
    'newStats.dimensionAverages[dim.key]',
    'newStats.maxRisk[dim.key]?.promedio',
    $content
);

file_put_contents($file, $content);

echo "✅ Archivo actualizado: $file\n";
echo "Cambios en JavaScript realizados\n";
