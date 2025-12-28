<?php
session_start();
echo "<h1>Información de Sesión</h1>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>Role Name:</h2>";
echo "<p>" . ($_SESSION['role_name'] ?? 'NO DEFINIDO') . "</p>";

echo "<h2>User ID:</h2>";
echo "<p>" . ($_SESSION['id'] ?? 'NO DEFINIDO') . "</p>";

echo "<h2>¿Es consultor?</h2>";
$roleName = $_SESSION['role_name'] ?? '';
$isConsultant = in_array($roleName, ['superadmin', 'admin', 'consultor']);
echo "<p>" . ($isConsultant ? 'SÍ' : 'NO') . "</p>";
