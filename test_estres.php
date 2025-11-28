<?php
/**
 * Script de prueba para validar cálculo de Estrés
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/Libraries/EstresScoring.php';

use App\Libraries\EstresScoring;

echo "=== PRUEBA DE CÁLCULO DE ESTRÉS ===\n\n";

// Test 1: Todas las respuestas = Siempre (0)
// Según Tabla 4 del Ministerio:
// Grupo 1 (ítems 1,2,3,9,13,14,15,23,24): Siempre=9
// Grupo 2 (ítems 4,5,6,10,11,16,17,18,19,25,26,27,28): Siempre=6
// Grupo 3 (ítems 7,8,12,20,21,22,29,30,31): Siempre=3

$respuestasTodosSiempre = [];
for ($i = 1; $i <= 31; $i++) {
    $respuestasTodosSiempre[$i] = 'siempre';
}

echo "TEST 1: Todas las respuestas = 'Siempre'\n";
echo "Esperado según Excel (texto.txt):\n";
echo "- Puntaje Bruto: Calculado según grupos\n";
echo "- Puntaje Transformado: 174.1\n";
echo "- Nivel: Muy Bajo\n\n";

$resultado1 = EstresScoring::calificar($respuestasTodosSiempre, 'auxiliares');

echo "Resultado del sistema:\n";
echo "- Puntaje Bruto: {$resultado1['puntaje_bruto_total']}\n";
echo "- Puntaje Transformado: {$resultado1['puntaje_transformado_total']}\n";
echo "- Nivel: {$resultado1['nivel_estres']}\n";
echo "- Subtotales:\n";
foreach ($resultado1['subtotales'] as $key => $value) {
    echo "  * {$key}: {$value}\n";
}
echo "\n";

// Test 2: Todas las respuestas = Nunca (3)
$respuestasTodosNunca = [];
for ($i = 1; $i <= 31; $i++) {
    $respuestasTodosNunca[$i] = 'nunca';
}

echo "TEST 2: Todas las respuestas = 'Nunca'\n";
echo "Esperado: Puntaje = 0, Nivel = Muy Bajo\n\n";

$resultado2 = EstresScoring::calificar($respuestasTodosNunca, 'auxiliares');

echo "Resultado del sistema:\n";
echo "- Puntaje Bruto: {$resultado2['puntaje_bruto_total']}\n";
echo "- Puntaje Transformado: {$resultado2['puntaje_transformado_total']}\n";
echo "- Nivel: {$resultado2['nivel_estres']}\n\n";

// Detalle del cálculo manual TEST 1
echo "=== CÁLCULO MANUAL DETALLADO (TEST 1) ===\n\n";

$grupo1 = [1, 2, 3, 9, 13, 14, 15, 23, 24]; // 9 ítems × 9 puntos = 81
$grupo2 = [4, 5, 6, 10, 11, 16, 17, 18, 19, 25, 26, 27, 28]; // 13 ítems × 6 puntos = 78
$grupo3 = [7, 8, 12, 20, 21, 22, 29, 30, 31]; // 9 ítems × 3 puntos = 27

echo "Grupo 1 (ítems 1,2,3,9,13,14,15,23,24): 9 ítems × 9 = 81 puntos\n";
echo "Grupo 2 (ítems 4,5,6,10,11,16,17,18,19,25,26,27,28): 13 ítems × 6 = 78 puntos\n";
echo "Grupo 3 (ítems 7,8,12,20,21,22,29,30,31): 9 ítems × 3 = 27 puntos\n";
echo "TOTAL ÍTEMS: 81 + 78 + 27 = 186 puntos\n\n";

echo "Según documento oficial:\n";
echo "a. Promedio ítems 1-8 × 4\n";
echo "   Grupo1 (1,2,3): 9+9+9 = 27\n";
echo "   Grupo2 (4,5,6): 6+6+6 = 18\n";
echo "   Grupo3 (7,8): 3+3 = 6\n";
echo "   Suma: 27+18+6 = 51\n";
echo "   Promedio: 51/8 = 6.375\n";
echo "   Subtotal1: 6.375 × 4 = 25.5\n\n";

echo "b. Promedio ítems 9-12 × 3\n";
echo "   Grupo1 (9): 9\n";
echo "   Grupo2 (10,11): 6+6 = 12\n";
echo "   Grupo3 (12): 3\n";
echo "   Suma: 9+12+3 = 24\n";
echo "   Promedio: 24/4 = 6\n";
echo "   Subtotal2: 6 × 3 = 18\n\n";

echo "c. Promedio ítems 13-22 × 2\n";
echo "   Grupo1 (13,14,15): 9+9+9 = 27\n";
echo "   Grupo2 (16,17,18,19): 6+6+6+6 = 24\n";
echo "   Grupo3 (20,21,22): 3+3+3 = 9\n";
echo "   Suma: 27+24+9 = 60\n";
echo "   Promedio: 60/10 = 6\n";
echo "   Subtotal3: 6 × 2 = 12\n\n";

echo "d. Suma ítems 23-31\n";
echo "   Grupo1 (23,24): 9+9 = 18\n";
echo "   Grupo2 (25,26,27,28): 6+6+6+6 = 24\n";
echo "   Grupo3 (29,30,31): 3+3+3 = 9\n";
echo "   Subtotal4: 18+24+9 = 51\n\n";

echo "PUNTAJE BRUTO TOTAL: 25.5 + 18 + 12 + 51 = 106.5\n";
echo "PUNTAJE TRANSFORMADO: (106.5 / 61.16) × 100 = 174.13\n\n";

echo "¿Coincide con el sistema?: " . (abs($resultado1['puntaje_transformado_total'] - 174.1) < 0.2 ? "✓ SÍ" : "✗ NO") . "\n";
