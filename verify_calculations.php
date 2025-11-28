<?php
/**
 * Script simple de verificación de cálculos para Worker 16
 * Consulta directa a la base de datos sin usar CodeIgniter
 */

// Conectar a MySQL
$mysqli = new mysqli("localhost", "root", "", "psyrisk");

if ($mysqli->connect_error) {
    die("Error de conexión: " . $mysqli->connect_error);
}

echo "\n========================================\n";
echo "VERIFICACIÓN DE CÁLCULOS - WORKER 16\n";
echo "========================================\n\n";

$workerId = 16;

// 1. Obtener información del worker
$result = $mysqli->query("SELECT id, intralaboral_type, atiende_clientes, es_jefe FROM workers WHERE id = {$workerId}");
$worker = $result->fetch_assoc();

echo "Worker ID: {$workerId}\n";
echo "Tipo Intralaboral: {$worker['intralaboral_type']}\n";
echo "Atiende Clientes: " . ($worker['atiende_clientes'] ? 'SÍ' : 'NO') . " (valor: {$worker['atiende_clientes']})\n";
echo "Es Jefe: " . ($worker['es_jefe'] ? 'SÍ' : 'NO') . " (valor: {$worker['es_jefe']})\n\n";

// 2. Obtener todas las respuestas
$result = $mysqli->query("SELECT question_number, answer_value FROM responses WHERE worker_id = {$workerId} AND form_type = 'intralaboral_a' ORDER BY question_number");

$answersArray = [];
while ($row = $result->fetch_assoc()) {
    $answersArray[$row['question_number']] = $row['answer_value'];
}

echo "Total respuestas: " . count($answersArray) . "\n\n";

// Verificar que todas sean 4
$min = min($answersArray);
$max = max($answersArray);
echo "Valor mínimo: {$min}\n";
echo "Valor máximo: {$max}\n";
echo "Todas las respuestas son 4: " . ($min == 4 && $max == 4 ? '✅ SÍ' : '❌ NO') . "\n\n";

// VERIFICACIÓN MANUAL DE CADA DIMENSIÓN DEL DOMINIO 3

// 1. Demandas ambientales y de esfuerzo físico (preguntas 1-12)
echo "========================================\n";
echo "DIMENSIÓN: Demandas ambientales\n";
echo "========================================\n";
$preguntas = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
$suma = 0;
foreach ($preguntas as $q) {
    $valor = $answersArray[$q];
    $suma += $valor;
}
$factor = 48;
$transformado = round(($suma / $factor) * 100, 1);
echo "Preguntas: 1-12 (12 preguntas)\n";
echo "Puntaje Bruto: {$suma} (esperado: 48 = 12 × 4)\n";
echo "Factor: {$factor}\n";
echo "Transformado: {$transformado}% (esperado: 100.0%)\n";
echo "Estado: " . ($transformado == 100.0 ? '✅ CORRECTO' : '❌ ERROR') . "\n\n";

// 2. Demandas cuantitativas (preguntas 13-15)
echo "========================================\n";
echo "DIMENSIÓN: Demandas cuantitativas\n";
echo "========================================\n";
$preguntas = [13, 14, 15];
$suma = 0;
foreach ($preguntas as $q) {
    $valor = $answersArray[$q];
    $suma += $valor;
}
$factor = 12;
$transformado = round(($suma / $factor) * 100, 1);
echo "Preguntas: 13-15 (3 preguntas)\n";
echo "Puntaje Bruto: {$suma} (esperado: 12 = 3 × 4)\n";
echo "Factor: {$factor}\n";
echo "Transformado: {$transformado}% (esperado: 100.0%)\n";
echo "Estado: " . ($transformado == 100.0 ? '✅ CORRECTO' : '❌ ERROR') . "\n\n";

// 3. Influencia del trabajo sobre entorno extralaboral
echo "========================================\n";
echo "DIMENSIÓN: Influencia trabajo-extralaboral\n";
echo "========================================\n";
echo "⚠️ VERIFICACIÓN CRÍTICA:\n";
echo "Según IntralaboralAScoring.php línea 55:\n";
echo "  [31, 32, 33, 34, 35, 36, 37, 38] = 8 preguntas\n\n";

$preguntas = [31, 32, 33, 34, 35, 36, 37, 38];
$suma = 0;
foreach ($preguntas as $q) {
    $valor = $answersArray[$q];
    $suma += $valor;
}
$factor = 32;
$transformado = round(($suma / $factor) * 100, 1);
echo "Preguntas: 31-38 (8 preguntas)\n";
echo "Puntaje Bruto: {$suma} (esperado: 32 = 8 × 4)\n";
echo "Factor: {$factor}\n";
echo "Transformado: {$transformado}% (esperado: 100.0%)\n";
echo "Estado: " . ($transformado == 100.0 ? '✅ CORRECTO' : '❌ ERROR') . "\n\n";

// 4. Exigencias de responsabilidad del cargo (preguntas 22-26)
echo "========================================\n";
echo "DIMENSIÓN: Responsabilidad del cargo\n";
echo "========================================\n";
$preguntas = [22, 23, 24, 25, 26];
$suma = 0;
foreach ($preguntas as $q) {
    $valor = $answersArray[$q];
    $suma += $valor;
}
$factor = 20;
$transformado = round(($suma / $factor) * 100, 1);
echo "Preguntas: 22-26 (5 preguntas)\n";
echo "Puntaje Bruto: {$suma} (esperado: 20 = 5 × 4)\n";
echo "Factor: {$factor}\n";
echo "Transformado: {$transformado}% (esperado: 100.0%)\n";
echo "Estado: " . ($transformado == 100.0 ? '✅ CORRECTO' : '❌ ERROR') . "\n\n";

// 5. Demandas de carga mental (preguntas 16-21)
echo "========================================\n";
echo "DIMENSIÓN: Carga mental\n";
echo "========================================\n";
$preguntas = [16, 17, 18, 19, 20, 21];
$suma = 0;
foreach ($preguntas as $q) {
    $valor = $answersArray[$q];
    $suma += $valor;
}
$factor = 24;
$transformado = round(($suma / $factor) * 100, 1);
echo "Preguntas: 16-21 (6 preguntas)\n";
echo "Puntaje Bruto: {$suma} (esperado: 24 = 6 × 4)\n";
echo "Factor: {$factor}\n";
echo "Transformado: {$transformado}% (esperado: 100.0%)\n";
echo "Estado: " . ($transformado == 100.0 ? '✅ CORRECTO' : '❌ ERROR') . "\n\n";

// 6. Consistencia del rol (preguntas 27-30)
echo "========================================\n";
echo "DIMENSIÓN: Consistencia del rol\n";
echo "========================================\n";
$preguntas = [27, 28, 29, 30];
$suma = 0;
foreach ($preguntas as $q) {
    $valor = $answersArray[$q];
    $suma += $valor;
}
$factor = 16;
$transformado = round(($suma / $factor) * 100, 1);
echo "Preguntas: 27-30 (4 preguntas)\n";
echo "Puntaje Bruto: {$suma} (esperado: 16 = 4 × 4)\n";
echo "Factor: {$factor}\n";
echo "Transformado: {$transformado}% (esperado: 100.0%)\n";
echo "Estado: " . ($transformado == 100.0 ? '✅ CORRECTO' : '❌ ERROR') . "\n\n";

// 7. Demandas de la jornada de trabajo (preguntas 31, 33)
echo "========================================\n";
echo "DIMENSIÓN: Jornada de trabajo\n";
echo "========================================\n";
echo "⚠️ NOTA: Esta dimensión COMPARTE preguntas 31 y 33 con\n";
echo "   'Influencia trabajo-extralaboral'\n\n";
$preguntas = [31, 33];
$suma = 0;
foreach ($preguntas as $q) {
    $valor = $answersArray[$q];
    $suma += $valor;
}
$factor = 8;
$transformado = round(($suma / $factor) * 100, 1);
echo "Preguntas: 31, 33 (2 preguntas)\n";
echo "Puntaje Bruto: {$suma} (esperado: 8 = 2 × 4)\n";
echo "Factor: {$factor}\n";
echo "Transformado: {$transformado}% (esperado: 100.0%)\n";
echo "Estado: " . ($transformado == 100.0 ? '✅ CORRECTO' : '❌ ERROR') . "\n\n";

// 8. Demandas emocionales (condicional)
if ($worker['atiende_clientes']) {
    echo "========================================\n";
    echo "DIMENSIÓN: Demandas emocionales\n";
    echo "========================================\n";
    $preguntas = [106, 107, 108, 109, 110, 111, 112, 113, 114];
    $suma = 0;
    foreach ($preguntas as $q) {
        $valor = $answersArray[$q] ?? 0;
        $suma += $valor;
    }
    $factor = 36;
    $transformado = round(($suma / $factor) * 100, 1);
    echo "Preguntas: 106-114 (9 preguntas)\n";
    echo "Puntaje Bruto: {$suma} (esperado: 36 = 9 × 4)\n";
    echo "Factor: {$factor}\n";
    echo "Transformado: {$transformado}% (esperado: 100.0%)\n";
    echo "Estado: " . ($transformado == 100.0 ? '✅ CORRECTO' : '❌ ERROR') . "\n\n";
} else {
    echo "========================================\n";
    echo "DIMENSIÓN: Demandas emocionales\n";
    echo "========================================\n";
    echo "NO APLICA (worker no atiende clientes)\n";
    echo "atiende_clientes = NULL o 0\n\n";
}

// COMPARAR CON BASE DE DATOS
echo "\n========================================\n";
echo "COMPARACIÓN CON CALCULATED_RESULTS\n";
echo "========================================\n";
$result = $mysqli->query("SELECT
    dom_demandas_puntaje, dom_demandas_nivel,
    dim_demandas_ambientales_puntaje,
    dim_demandas_cuantitativas_puntaje,
    dim_influencia_trabajo_entorno_extralaboral_puntaje,
    dim_demandas_responsabilidad_puntaje,
    dim_demandas_carga_mental_puntaje,
    dim_consistencia_rol_puntaje,
    dim_demandas_jornada_trabajo_puntaje,
    dim_demandas_emocionales_puntaje
FROM calculated_results WHERE worker_id = {$workerId}");
$calc = $result->fetch_assoc();

echo "DOMINIO 3: Demandas del Trabajo\n";
echo "  Puntaje: {$calc['dom_demandas_puntaje']}%\n";
echo "  Nivel: {$calc['dom_demandas_nivel']}\n\n";

echo "DIMENSIONES:\n";
echo "  Demandas ambientales: {$calc['dim_demandas_ambientales_puntaje']}%\n";
echo "  Demandas cuantitativas: {$calc['dim_demandas_cuantitativas_puntaje']}%\n";
echo "  Influencia trabajo-extralaboral: {$calc['dim_influencia_trabajo_entorno_extralaboral_puntaje']}%\n";
echo "  Responsabilidad del cargo: {$calc['dim_demandas_responsabilidad_puntaje']}%\n";
echo "  Carga mental: {$calc['dim_demandas_carga_mental_puntaje']}%\n";
echo "  Consistencia del rol: {$calc['dim_consistencia_rol_puntaje']}%\n";
echo "  Jornada de trabajo: {$calc['dim_demandas_jornada_trabajo_puntaje']}%\n";
echo "  Demandas emocionales: {$calc['dim_demandas_emocionales_puntaje']}%\n";

echo "\n¿Todas las dimensiones son 100.0%? ";
$todas100 = (
    $calc['dim_demandas_ambientales_puntaje'] == 100.0 &&
    $calc['dim_demandas_cuantitativas_puntaje'] == 100.0 &&
    $calc['dim_influencia_trabajo_entorno_extralaboral_puntaje'] == 100.0 &&
    $calc['dim_demandas_responsabilidad_puntaje'] == 100.0 &&
    $calc['dim_demandas_carga_mental_puntaje'] == 100.0 &&
    $calc['dim_consistencia_rol_puntaje'] == 100.0 &&
    $calc['dim_demandas_jornada_trabajo_puntaje'] == 100.0 &&
    ($calc['dim_demandas_emocionales_puntaje'] == 100.0 || $calc['dim_demandas_emocionales_puntaje'] === null)
);
echo ($todas100 ? '✅ SÍ' : '❌ NO') . "\n";

echo "\n========================================\n";
echo "CONCLUSIÓN\n";
echo "========================================\n";
if ($todas100 && $calc['dom_demandas_puntaje'] == 100.0) {
    echo "✅ LOS CÁLCULOS SON CORRECTOS\n";
    echo "Todas las respuestas son 4, por lo tanto:\n";
    echo "- Todas las dimensiones deben ser 100.0%\n";
    echo "- El Dominio 3 debe ser 100.0%\n";
    echo "El sistema está calculando correctamente.\n";
} else {
    echo "❌ HAY ERRORES EN LOS CÁLCULOS\n";
    echo "Se esperaba que todas las dimensiones y el dominio\n";
    echo "fueran 100.0% ya que todas las respuestas son 4.\n";
}

$mysqli->close();
echo "\n";
?>
