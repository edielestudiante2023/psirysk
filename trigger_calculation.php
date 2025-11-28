<?php

// Trigger calculation for worker 1
$mysqli = new mysqli("localhost", "root", "", "psyrisk");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "=== TRIGGERING CALCULATION FOR WORKER 1 ===\n\n";

// Make HTTP request to trigger calculation
$url = "http://localhost/psyrisk/workers/results/1";

echo "Making request to: {$url}\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: {$httpCode}\n";

// Wait a moment for calculation
sleep(2);

// Check database
echo "\n=== CHECKING DATABASE ===\n";
$result = $mysqli->query("SELECT estres_total_puntaje, estres_total_nivel FROM calculated_results WHERE worker_id = 1");

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo "✅ Resultados encontrados:\n";
    echo "   Estrés Puntaje: " . $row['estres_total_puntaje'] . "\n";
    echo "   Estrés Nivel: " . $row['estres_total_nivel'] . "\n\n";

    if ($row['estres_total_puntaje'] == 100.0 && $row['estres_total_nivel'] == 'muy_alto') {
        echo "✅✅✅ ÉXITO! El resultado es correcto!\n";
    } else {
        echo "❌ ERROR: El resultado no es el esperado\n";
        echo "   Esperado: Puntaje=100.0, Nivel=muy_alto\n";
    }
} else {
    echo "❌ No se encontraron resultados en la base de datos\n";
}

$mysqli->close();
echo "\n=== FIN ===\n";
