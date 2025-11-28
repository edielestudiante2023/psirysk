<?php
// Test if routing is working at all

echo "PHP is working<br>";
echo "Document root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Request URI: " . $_SERVER['REQUEST_URI'] . "<br>";
echo "Script name: " . $_SERVER['SCRIPT_NAME'] . "<br>";

// Try to load CodeIgniter
require __DIR__ . '/../vendor/autoload.php';

echo "Autoload successful<br>";

// Check if Routes.php exists
$routesFile = __DIR__ . '/../app/Config/Routes.php';
echo "Routes.php exists: " . (file_exists($routesFile) ? 'YES' : 'NO') . "<br>";

// Check if ReportsController exists
$controllerFile = __DIR__ . '/../app/Controllers/ReportsController.php';
echo "ReportsController.php exists: " . (file_exists($controllerFile) ? 'YES' : 'NO') . "<br>";

echo "<br>Testing route manually:<br>";
echo '<a href="/psyrisk/reports/heatmap/2">/psyrisk/reports/heatmap/2</a><br>';
echo '<a href="/psyrisk/dashboard">/psyrisk/dashboard</a><br>';
echo '<a href="/psyrisk/login">/psyrisk/login</a>';
