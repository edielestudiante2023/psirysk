<?php
// Conexión directa a MySQL
$mysqli = new mysqli("localhost", "root", "", "psyrisk");

if ($mysqli->connect_error) {
    die("Error de conexión: " . $mysqli->connect_error);
}

// Generar hash
$password = 'password';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "Contraseña: {$password}\n";
echo "Hash generado: {$hash}\n\n";

// Actualizar Diana
$stmt = $mysqli->prepare("UPDATE users SET password = ? WHERE email = ?");
$email1 = 'diana.cuestas@cycloidtalent.com';
$stmt->bind_param("ss", $hash, $email1);
$stmt->execute();
echo "✓ Contraseña actualizada para diana.cuestas@cycloidtalent.com\n";

// Actualizar Consultor
$email2 = 'consultor@cycloidtalent.com';
$stmt->bind_param("ss", $hash, $email2);
$stmt->execute();
echo "✓ Contraseña actualizada para consultor@cycloidtalent.com\n\n";

// Verificar
$result = $mysqli->query("SELECT email, password FROM users WHERE email = 'diana.cuestas@cycloidtalent.com'");
$user = $result->fetch_assoc();

echo "Verificación:\n";
echo "Email: {$user['email']}\n";
echo "Hash guardado: " . substr($user['password'], 0, 40) . "...\n";
echo "Longitud hash: " . strlen($user['password']) . " caracteres\n";
echo "password_verify: " . (password_verify('password', $user['password']) ? '✓ OK' : '✗ FAIL') . "\n";

$mysqli->close();
