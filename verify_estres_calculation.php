<?php
/**
 * Verificación manual del cálculo de Estrés para Worker 16
 */

$mysqli = new mysqli("localhost", "root", "", "psyrisk");

if ($mysqli->connect_error) {
    die("Error de conexión: " . $mysqli->connect_error);
}

echo "\n========================================\n";
echo "VERIFICACIÓN MANUAL - ESTRÉS WORKER 16\n";
echo "========================================\n\n";

$workerId = 16;

// Obtener respuestas
$result = $mysqli->query("SELECT question_number, answer_value
                          FROM responses
                          WHERE worker_id = {$workerId} AND form_type = 'estres'
                          ORDER BY question_number");

$responses = [];
while ($row = $result->fetch_assoc()) {
    $responses[$row['question_number']] = (int)$row['answer_value'];
}

echo "Total respuestas: " . count($responses) . "\n\n";

// Calcular según metodología oficial
// Paso 2: Obtención del puntaje bruto total

// a. Promedio ítems 1-8, multiplicado por 4
$suma_1_8 = 0;
for ($i = 1; $i <= 8; $i++) {
    $suma_1_8 += $responses[$i];
}
$promedio_1_8 = $suma_1_8 / 8;
$subtotal_a = $promedio_1_8 * 4;

echo "a. Ítems 1-8:\n";
echo "   Suma: {$suma_1_8}\n";
echo "   Promedio: {$promedio_1_8}\n";
echo "   Subtotal (promedio × 4): {$subtotal_a}\n\n";

// b. Promedio ítems 9-12, multiplicado por 3
$suma_9_12 = 0;
for ($i = 9; $i <= 12; $i++) {
    $suma_9_12 += $responses[$i];
}
$promedio_9_12 = $suma_9_12 / 4;
$subtotal_b = $promedio_9_12 * 3;

echo "b. Ítems 9-12:\n";
echo "   Suma: {$suma_9_12}\n";
echo "   Promedio: {$promedio_9_12}\n";
echo "   Subtotal (promedio × 3): {$subtotal_b}\n\n";

// c. Promedio ítems 13-22, multiplicado por 2
$suma_13_22 = 0;
for ($i = 13; $i <= 22; $i++) {
    $suma_13_22 += $responses[$i];
}
$promedio_13_22 = $suma_13_22 / 10;
$subtotal_c = $promedio_13_22 * 2;

echo "c. Ítems 13-22:\n";
echo "   Suma: {$suma_13_22}\n";
echo "   Promedio: {$promedio_13_22}\n";
echo "   Subtotal (promedio × 2): {$subtotal_c}\n\n";

// d. Promedio ítems 23-31 (sin multiplicar)
$suma_23_31 = 0;
for ($i = 23; $i <= 31; $i++) {
    $suma_23_31 += $responses[$i];
}
$promedio_23_31 = $suma_23_31 / 9;
$subtotal_d = $promedio_23_31;

echo "d. Ítems 23-31:\n";
echo "   Suma: {$suma_23_31}\n";
echo "   Promedio: {$promedio_23_31}\n";
echo "   Subtotal (promedio): {$subtotal_d}\n\n";

// Puntaje bruto total
$puntaje_bruto_total = $subtotal_a + $subtotal_b + $subtotal_c + $subtotal_d;

echo "========================================\n";
echo "PUNTAJE BRUTO TOTAL: {$puntaje_bruto_total}\n";
echo "========================================\n\n";

// Paso 3: Transformación
$factor = 61.16;
$puntaje_transformado = round(($puntaje_bruto_total / $factor) * 100, 1);

echo "Factor de transformación: {$factor}\n";
echo "Puntaje transformado: {$puntaje_transformado}%\n\n";

// Determinar nivel (Baremo para Jefes/profesionales/técnicos)
$nivel = '';
if ($puntaje_transformado >= 0.0 && $puntaje_transformado <= 7.8) {
    $nivel = 'Muy bajo';
} elseif ($puntaje_transformado >= 7.9 && $puntaje_transformado <= 12.6) {
    $nivel = 'Bajo';
} elseif ($puntaje_transformado >= 12.7 && $puntaje_transformado <= 17.7) {
    $nivel = 'Medio';
} elseif ($puntaje_transformado >= 17.8 && $puntaje_transformado <= 25.0) {
    $nivel = 'Alto';
} else {
    $nivel = 'Muy alto';
}

echo "Nivel de estrés: {$nivel}\n";

echo "\n========================================\n";
echo "CONCLUSIÓN\n";
echo "========================================\n";
echo "✅ El puntaje transformado debería ser: {$puntaje_transformado}%\n";
echo "✅ El nivel de estrés debería ser: {$nivel}\n";

$mysqli->close();
echo "\n";
?>
