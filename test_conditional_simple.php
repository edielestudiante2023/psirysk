<?php

echo "=== TEST VALIDACIÓN CONDICIONAL - SIMPLE ===\n\n";

// Conectar directamente a MySQL
$mysqli = new mysqli('localhost', 'root', '', 'psyrisk');

if ($mysqli->connect_error) {
    die("Error de conexión: " . $mysqli->connect_error);
}

$workerId = 1;

// ESCENARIO 1: NO atiende clientes (88 preguntas)
echo "--- ESCENARIO 1: NO atiende clientes (88 preguntas) ---\n";

// Limpiar datos previos
$mysqli->query("DELETE FROM responses WHERE worker_id = $workerId AND form_type = 'intralaboral_B'");
$mysqli->query("UPDATE workers SET atiende_clientes = 0 WHERE id = $workerId");
echo "✓ Worker configurado con atiende_clientes = 0\n";

// Insertar 88 respuestas
for ($i = 1; $i <= 88; $i++) {
    $mysqli->query("INSERT INTO responses (worker_id, form_type, question_number, answer_value, session_id, created_at)
                    VALUES ($workerId, 'intralaboral_B', $i, 0, 'test-session-1', NOW())");
}
echo "✓ Insertadas 88 respuestas\n";

// Verificar
$result = $mysqli->query("SELECT COUNT(*) as total FROM responses WHERE worker_id = $workerId AND form_type = 'intralaboral_B'");
$row = $result->fetch_assoc();
echo "✓ Total en BD: {$row['total']}\n\n";

echo "Ahora debes probar manualmente:\n";
echo "1. Ir a http://localhost/psyrisk/workers/results/$workerId\n";
echo "2. El sistema debe validar correctamente (88/88 preguntas)\n\n";

// ESCENARIO 2: SÍ atiende clientes (97 preguntas)
echo "--- ESCENARIO 2: SÍ atiende clientes (97 preguntas) ---\n";

// Limpiar datos previos
$mysqli->query("DELETE FROM responses WHERE worker_id = $workerId AND form_type = 'intralaboral_B'");
$mysqli->query("UPDATE workers SET atiende_clientes = 1 WHERE id = $workerId");
echo "✓ Worker configurado con atiende_clientes = 1\n";

// Insertar 97 respuestas
for ($i = 1; $i <= 97; $i++) {
    $mysqli->query("INSERT INTO responses (worker_id, form_type, question_number, answer_value, session_id, created_at)
                    VALUES ($workerId, 'intralaboral_B', $i, 0, 'test-session-2', NOW())");
}
echo "✓ Insertadas 97 respuestas\n";

// Verificar
$result = $mysqli->query("SELECT COUNT(*) as total FROM responses WHERE worker_id = $workerId AND form_type = 'intralaboral_B'");
$row = $result->fetch_assoc();
echo "✓ Total en BD: {$row['total']}\n\n";

echo "Ahora debes probar manualmente:\n";
echo "1. Ir a http://localhost/psyrisk/workers/results/$workerId\n";
echo "2. El sistema debe validar correctamente (97/97 preguntas)\n\n";

// ESCENARIO 3: SÍ atiende clientes pero solo 88 respuestas (debe fallar)
echo "--- ESCENARIO 3: SÍ atiende clientes pero solo 88 respuestas (debe fallar) ---\n";

// Limpiar datos previos
$mysqli->query("DELETE FROM responses WHERE worker_id = $workerId AND form_type = 'intralaboral_B'");
$mysqli->query("UPDATE workers SET atiende_clientes = 1 WHERE id = $workerId");
echo "✓ Worker configurado con atiende_clientes = 1\n";

// Insertar solo 88 respuestas
for ($i = 1; $i <= 88; $i++) {
    $mysqli->query("INSERT INTO responses (worker_id, form_type, question_number, answer_value, session_id, created_at)
                    VALUES ($workerId, 'intralaboral_B', $i, 0, 'test-session-3', NOW())");
}
echo "✓ Insertadas 88 respuestas (incompleto)\n";

// Verificar
$result = $mysqli->query("SELECT COUNT(*) as total FROM responses WHERE worker_id = $workerId AND form_type = 'intralaboral_B'");
$row = $result->fetch_assoc();
echo "✓ Total en BD: {$row['total']}\n\n";

echo "Ahora debes probar manualmente:\n";
echo "1. Ir a http://localhost/psyrisk/workers/results/$workerId\n";
echo "2. El sistema debe indicar que faltan respuestas (88/97 preguntas)\n\n";

$mysqli->close();

echo "=== FIN DEL TEST ===\n";
echo "\nPara limpiar los datos de prueba, ejecuta:\n";
echo "DELETE FROM responses WHERE worker_id = $workerId AND form_type = 'intralaboral_B';\n";
echo "UPDATE workers SET atiende_clientes = NULL WHERE id = $workerId;\n";
