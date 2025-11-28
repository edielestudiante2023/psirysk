<?php
// Script para crear usuario administrador
$mysqli = new mysqli("localhost", "root", "", "psyrisk");

if ($mysqli->connect_error) {
    die("Error de conexión: " . $mysqli->connect_error);
}

$name = 'Edison Cuervo';
$email = 'edison.cuervo@cycloidtalent.com';
$password = 'Admin123*';
$roleId = 2; // admin

// Generar hash
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "Creando usuario administrador:\n";
echo "Nombre: {$name}\n";
echo "Email: {$email}\n";
echo "Password: {$password}\n";
echo "Hash: {$hash}\n\n";

// Insertar usuario
$stmt = $mysqli->prepare("INSERT INTO users (role_id, name, email, password, status, created_at) VALUES (?, ?, ?, ?, 'active', NOW())");
$stmt->bind_param("isss", $roleId, $name, $email, $hash);

if ($stmt->execute()) {
    $userId = $stmt->insert_id;
    echo "✓ Usuario administrador creado exitosamente con ID: {$userId}\n\n";

    // Verificar
    $result = $mysqli->query("SELECT u.*, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = $userId");
    $user = $result->fetch_assoc();

    echo "Verificación:\n";
    echo "ID: {$user['id']}\n";
    echo "Nombre: {$user['name']}\n";
    echo "Email: {$user['email']}\n";
    echo "Rol: {$user['role_name']}\n";
    echo "Status: {$user['status']}\n\n";

    // Probar password
    $verify = password_verify($password, $user['password']);
    echo "Verificación de contraseña: " . ($verify ? "✓ OK" : "✗ FAIL") . "\n";
} else {
    echo "✗ Error al crear usuario: " . $stmt->error . "\n";
}

$mysqli->close();
