<?php
// Script de diagnóstico para verificar GD y procesamiento de imágenes

echo "<h1>Diagnóstico de Imágenes para Dompdf</h1>";

// 1. Verificar extensión GD
echo "<h2>1. Extensión GD</h2>";
if (extension_loaded('gd')) {
    echo "✓ <span style='color: green;'>GD está HABILITADO</span><br>";
    $gdInfo = gd_info();
    echo "<pre>";
    print_r($gdInfo);
    echo "</pre>";
} else {
    echo "✗ <span style='color: red;'>GD NO está habilitado</span><br>";
    echo "<strong>SOLUCIÓN:</strong> Habilitar extension=gd en php.ini<br>";
}

// 2. Verificar rutas de imágenes
echo "<h2>2. Rutas de Imágenes</h2>";
$logoCycloid = __DIR__ . '/../public/assets/images/logo_gris.jpeg';
$logoRPS = __DIR__ . '/../public/assets/images/rps.png';

echo "Logo Cycloid: " . $logoCycloid . "<br>";
echo "Existe: " . (file_exists($logoCycloid) ? '✓ SÍ' : '✗ NO') . "<br>";
if (file_exists($logoCycloid)) {
    echo "Tamaño: " . filesize($logoCycloid) . " bytes<br>";
}

echo "<br>";

echo "Logo RPS: " . $logoRPS . "<br>";
echo "Existe: " . (file_exists($logoRPS) ? '✓ SÍ' : '✗ NO') . "<br>";
if (file_exists($logoRPS)) {
    echo "Tamaño: " . filesize($logoRPS) . " bytes<br>";
}

// 3. Verificar URL base
echo "<h2>3. URL Base</h2>";
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$baseUrl = $protocol . "://" . $host . "/psyrisk";
echo "Base URL: " . $baseUrl . "<br>";
echo "URL Logo Cycloid: " . $baseUrl . "/assets/images/logo_gris.jpeg<br>";
echo "URL Logo RPS: " . $baseUrl . "/assets/images/rps.png<br>";

// 4. Test de carga de imagen
echo "<h2>4. Test Visual de Imágenes</h2>";
echo "<img src='/psyrisk/assets/images/logo_gris.jpeg' style='height: 80px; border: 1px solid #ccc; margin: 10px;'><br>";
echo "<img src='/psyrisk/assets/images/rps.png' style='height: 80px; border: 1px solid #ccc; margin: 10px;'><br>";

// 5. Verificar permisos
echo "<h2>5. Información del Sistema</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Memory Limit: " . ini_get('memory_limit') . "<br>";
echo "Dompdf installed: " . (class_exists('Dompdf\Dompdf') ? '✓ SÍ' : '✗ NO') . "<br>";

// 6. Test de conversión Base64
echo "<h2>6. Test Base64</h2>";
if (file_exists($logoCycloid)) {
    $imageData = file_get_contents($logoCycloid);
    $base64 = base64_encode($imageData);
    echo "Base64 generado: " . strlen($base64) . " caracteres<br>";
    echo "Primeros 100 caracteres: " . substr($base64, 0, 100) . "...<br>";
}
