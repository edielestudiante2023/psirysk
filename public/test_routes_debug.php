<?php
// Diagnóstico completo de rutas
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
define('SYSTEMPATH', realpath(__DIR__ . '/../vendor/codeigniter4/framework/system') . DIRECTORY_SEPARATOR);
define('APPPATH', realpath(__DIR__ . '/../app') . DIRECTORY_SEPARATOR);
define('ROOTPATH', realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR);
define('WRITEPATH', realpath(__DIR__ . '/../writable') . DIRECTORY_SEPARATOR);

require __DIR__ . '/../vendor/autoload.php';

use Config\Services;

echo "<h1>Diagnóstico de Rutas</h1>";

try {
    // Cargar el router
    $routes = Services::routes();

    echo "<h2>Configuración de Auto-Routing</h2>";
    $routing = new \Config\Routing();
    echo "autoRoute: " . ($routing->autoRoute ? 'TRUE' : 'FALSE') . "<br>";

    echo "<h2>Rutas Registradas</h2>";
    echo "<pre>";

    // Obtener todas las rutas HTTP
    $allRoutes = $routes->getRoutes();

    echo "Total de rutas: " . count($allRoutes) . "\n\n";

    // Filtrar solo rutas que contengan 'reports'
    echo "=== RUTAS QUE CONTIENEN 'reports' ===\n";
    foreach ($allRoutes as $route => $handler) {
        if (stripos($route, 'reports') !== false) {
            echo "$route => $handler\n";
        }
    }

    echo "\n=== PRIMERAS 20 RUTAS ===\n";
    $count = 0;
    foreach ($allRoutes as $route => $handler) {
        echo "$route => $handler\n";
        $count++;
        if ($count >= 20) break;
    }

    echo "</pre>";

    // Intentar hacer match de la ruta
    echo "<h2>Test de Matching de Ruta</h2>";

    // Crear un request simulado
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/psyrisk/reports/heatmap/2';

    $router = Services::router($routes);

    echo "URI a probar: /psyrisk/reports/heatmap/2<br>";
    echo "URI sin base: reports/heatmap/2<br>";

    // Intentar encontrar la ruta
    try {
        $router->handle('reports/heatmap/2');
        echo "<strong style='color:green'>✓ Ruta encontrada!</strong><br>";
        echo "Controller: " . $router->controllerName() . "<br>";
        echo "Method: " . $router->methodName() . "<br>";
        echo "Params: " . implode(', ', $router->params()) . "<br>";
    } catch (\Exception $e) {
        echo "<strong style='color:red'>✗ Error al hacer match:</strong><br>";
        echo $e->getMessage();
    }

} catch (\Exception $e) {
    echo "<h2 style='color:red'>Error</h2>";
    echo "<pre>";
    echo $e->getMessage() . "\n\n";
    echo $e->getTraceAsString();
    echo "</pre>";
}
