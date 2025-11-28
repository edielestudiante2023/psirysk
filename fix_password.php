<?php
require __DIR__ . '/vendor/autoload.php';

$db = \Config\Database::connect();

// Generar nuevo hash
$password = 'password';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "Generando nuevo hash para contraseña: {$password}\n";
echo "Hash: {$hash}\n\n";

// Actualizar Diana
$db->table('users')
   ->where('email', 'diana.cuestas@cycloidtalent.com')
   ->update(['password' => $hash]);

echo "✓ Contraseña actualizada para diana.cuestas@cycloidtalent.com\n";

// Actualizar consultor
$db->table('users')
   ->where('email', 'consultor@cycloidtalent.com')
   ->update(['password' => $hash]);

echo "✓ Contraseña actualizada para consultor@cycloidtalent.com\n\n";

// Verificar
$user = $db->table('users')
           ->where('email', 'diana.cuestas@cycloidtalent.com')
           ->get()
           ->getRowArray();

echo "Verificación:\n";
echo "Email: {$user['email']}\n";
echo "Password hash guardado: " . substr($user['password'], 0, 40) . "...\n";
echo "Verificación password_verify: " . (password_verify('password', $user['password']) ? '✓ OK' : '✗ FAIL') . "\n";
