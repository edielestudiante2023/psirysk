<?php
// Script para verificar login del consultor
$mysqli = new mysqli("localhost", "root", "", "psyrisk");

if ($mysqli->connect_error) {
    die("Error de conexión: " . $mysqli->connect_error);
}

$email = 'eleyson.segura@cycloidtalent.com';
$password = '3227145322';

// Obtener usuario
$result = $mysqli->query("SELECT u.*, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.email = '$email'");

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    echo "Usuario encontrado:\n";
    echo "ID: {$user['id']}\n";
    echo "Nombre: {$user['name']}\n";
    echo "Email: {$user['email']}\n";
    echo "Rol: {$user['role_name']}\n";
    echo "Hash guardado: " . substr($user['password'], 0, 30) . "...\n";
    echo "Longitud hash: " . strlen($user['password']) . " caracteres\n\n";

    // Verificar contraseña
    echo "Probando contraseña: {$password}\n";
    $verify = password_verify($password, $user['password']);
    echo "Resultado password_verify: " . ($verify ? "✓ CORRECTO" : "✗ INCORRECTO") . "\n\n";

    if (!$verify) {
        echo "Generando nuevo hash...\n";
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        echo "Nuevo hash: {$newHash}\n\n";

        // Actualizar
        $stmt = $mysqli->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $newHash, $email);
        $stmt->execute();

        echo "✓ Hash actualizado en la base de datos\n";

        // Verificar nuevamente
        $result2 = $mysqli->query("SELECT password FROM users WHERE email = '$email'");
        $user2 = $result2->fetch_assoc();
        $verify2 = password_verify($password, $user2['password']);
        echo "Verificación final: " . ($verify2 ? "✓ OK - Ahora funciona" : "✗ Aún hay problema") . "\n";
    }
} else {
    echo "✗ Usuario no encontrado con email: {$email}\n";
}

$mysqli->close();
