<?php
// Conexión directa a MySQL
$mysqli = new mysqli('localhost', 'root', '', 'psyrisk');

if ($mysqli->connect_error) {
    die('Error de conexión: ' . $mysqli->connect_error);
}

// Verificar si existen datos para servicio 9
$result = $mysqli->query('SELECT COUNT(*) as count FROM max_risk_results WHERE battery_service_id = 9');
$row = $result->fetch_assoc();

echo "Registros en max_risk_results para servicio 9: " . $row['count'] . PHP_EOL;

if ($row['count'] > 0) {
    echo "\n✓ Datos existentes\n";

    $result2 = $mysqli->query('SELECT element_type, COUNT(*) as count FROM max_risk_results WHERE battery_service_id = 9 GROUP BY element_type');
    echo "\nDesglose por tipo:\n";
    while ($row2 = $result2->fetch_assoc()) {
        echo "  " . $row2['element_type'] . ": " . $row2['count'] . "\n";
    }

    $result3 = $mysqli->query('SELECT element_code, worst_score, worst_risk_level, worst_form, form_a_score, form_b_score FROM max_risk_results WHERE battery_service_id = 9 AND element_type = "dimension" LIMIT 3');
    echo "\nPrimeras 3 dimensiones:\n";
    while ($row3 = $result3->fetch_assoc()) {
        echo sprintf("  %s: worst=%.2f (%s) | A=%.2f | B=%.2f\n",
            $row3['element_code'],
            $row3['worst_score'],
            $row3['worst_form'],
            $row3['form_a_score'] ?? 0,
            $row3['form_b_score'] ?? 0
        );
    }
} else {
    echo "\n✗ NO HAY DATOS - Necesitas calcular primero\n";
    echo "\nVisita en el navegador:\n";
    echo "http://localhost/psyrisk/reports/heatmap/9\n";
    echo "(Esto auto-genera los datos de max_risk_results si no existen)\n";
}

$mysqli->close();
