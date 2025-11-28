<?php
// Script para generar hash de contraseña
$password = 'password';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "Contraseña: {$password}\n";
echo "Hash: {$hash}\n";
echo "\nVerificación: " . (password_verify($password, $hash) ? 'OK' : 'FAIL') . "\n";
