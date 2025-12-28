<?php
// Iniciar sesión de PHP normal
session_start();

// CodeIgniter 4 usa un prefijo "ci_session" para sus cookies
// Vamos a leer la cookie de sesión de CodeIgniter
$ci_session = $_COOKIE['ci_session'] ?? null;

echo "<!DOCTYPE html>";
echo "<html><head>";
echo "<title>Session Check</title>";
echo "<style>body { font-family: Arial; padding: 20px; } .info { background: #e3f2fd; padding: 15px; margin: 10px 0; border-radius: 5px; } .value { font-size: 24px; font-weight: bold; color: #1976d2; }</style>";
echo "</head><body>";
echo "<h1>🔍 Verificación de Sesión</h1>";

echo "<div class='info'>";
echo "<h2>1. Sesión PHP Estándar:</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
echo "</div>";

echo "<div class='info'>";
echo "<h2>2. Cookies Disponibles:</h2>";
echo "<pre>";
print_r($_COOKIE);
echo "</pre>";
echo "</div>";

// Intentar decodificar la sesión de CodeIgniter si existe
if ($ci_session) {
    echo "<div class='info'>";
    echo "<h2>3. Cookie de Sesión de CodeIgniter:</h2>";
    echo "<p>Existe: SÍ ✅</p>";
    echo "<p>Valor (primeros 100 caracteres): " . substr($ci_session, 0, 100) . "...</p>";
    echo "</div>";
} else {
    echo "<div class='info'>";
    echo "<h2>3. Cookie de Sesión de CodeIgniter:</h2>";
    echo "<p>No existe ❌</p>";
    echo "<p><strong>Esto significa que NO estás logueado o la sesión expiró.</strong></p>";
    echo "</div>";
}

echo "<div class='info'>";
echo "<h2>📝 Instrucciones:</h2>";
echo "<ol>";
echo "<li>Ve a <a href='".dirname($_SERVER['PHP_SELF'])."/login'>Login</a> y loguéate</li>";
echo "<li>Luego regresa a esta página</li>";
echo "<li>Deberías ver la cookie 'ci_session' con datos</li>";
echo "</ol>";
echo "</div>";

echo "</body></html>";
