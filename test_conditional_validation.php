<?php

require 'vendor/autoload.php';

$config = new \Config\Database();
$db = \Config\Database::connect();

echo "=== TEST VALIDACIÓN CONDICIONAL INTRALABORAL B ===\n\n";

$workerId = 1;

// Verificar estado inicial
$worker = $db->table('workers')->where('id', $workerId)->get()->getRowArray();
echo "Worker ID: {$workerId}\n";
echo "Nombre: {$worker['name']}\n";
echo "Tipo Intralaboral: {$worker['intralaboral_type']}\n";
echo "Atiende Clientes: " . ($worker['atiende_clientes'] ?? 'NULL') . "\n\n";

// ESCENARIO 1: Responder 88 preguntas (NO atiende clientes)
echo "--- ESCENARIO 1: NO atiende clientes (88 preguntas) ---\n";

// Primero actualizar atiende_clientes a 0 (NO)
$db->table('workers')->update(['atiende_clientes' => 0], ['id' => $workerId]);
echo "✓ Establecido atiende_clientes = 0 (NO)\n";

// Limpiar respuestas previas
$db->table('responses')->where('worker_id', $workerId)->where('form_type', 'intralaboral_B')->delete();

// Crear 88 respuestas (preguntas 1-88, todas con valor 0 = "Siempre")
$responses = [];
for ($i = 1; $i <= 88; $i++) {
    $responses[] = [
        'worker_id' => $workerId,
        'form_type' => 'intralaboral_B',
        'question_number' => $i,
        'answer_value' => 0, // Siempre
        'session_id' => 'test-session-' . time(),
        'created_at' => date('Y-m-d H:i:s')
    ];
}

// Insertar respuestas
$db->table('responses')->insertBatch($responses);
echo "✓ Insertadas 88 respuestas (preguntas 1-88)\n";

// Verificar cuántas respuestas hay
$count = $db->table('responses')
    ->where('worker_id', $workerId)
    ->where('form_type', 'intralaboral_B')
    ->countAllResults();
echo "✓ Total respuestas en BD: $count\n\n";

// Ahora usar CalculationService para validar
$calculationService = new \App\Services\CalculationService();

echo "Verificando si tiene todos los formularios completos...\n";
try {
    $hasAll = $calculationService->hasAllFormsCompleted($workerId);
    if ($hasAll) {
        echo "✅ VALIDACIÓN EXITOSA: Sistema reconoce que tiene todas las respuestas necesarias (88/88)\n";
    } else {
        echo "❌ ERROR: Sistema indica que faltan respuestas\n";
    }
} catch (\Exception $e) {
    echo "❌ EXCEPCIÓN: " . $e->getMessage() . "\n";
}

echo "\n";

// Limpiar para escenario 2
echo "--- Limpiando para escenario 2... ---\n";
$db->table('responses')->where('worker_id', $workerId)->where('form_type', 'intralaboral_B')->delete();
echo "✓ Respuestas eliminadas\n\n";

// ESCENARIO 2: Responder 97 preguntas (SÍ atiende clientes)
echo "--- ESCENARIO 2: SÍ atiende clientes (97 preguntas) ---\n";

// Actualizar atiende_clientes a 1 (SÍ)
$db->table('workers')->update(['atiende_clientes' => 1], ['id' => $workerId]);
echo "✓ Establecido atiende_clientes = 1 (SÍ)\n";

// Crear 97 respuestas (preguntas 1-97, todas con valor 0 = "Siempre")
$responses = [];
for ($i = 1; $i <= 97; $i++) {
    $responses[] = [
        'worker_id' => $workerId,
        'form_type' => 'intralaboral_B',
        'question_number' => $i,
        'answer_value' => 0, // Siempre
        'session_id' => 'test-session-' . time(),
        'created_at' => date('Y-m-d H:i:s')
    ];
}

// Insertar respuestas
$db->table('responses')->insertBatch($responses);
echo "✓ Insertadas 97 respuestas (preguntas 1-97)\n";

// Verificar cuántas respuestas hay
$count = $db->table('responses')
    ->where('worker_id', $workerId)
    ->where('form_type', 'intralaboral_B')
    ->countAllResults();
echo "✓ Total respuestas en BD: $count\n\n";

// Intentar validar que tiene todos los formularios completos
echo "Verificando si tiene todos los formularios completos...\n";
try {
    $hasAll = $calculationService->hasAllFormsCompleted($workerId);
    if ($hasAll) {
        echo "✅ VALIDACIÓN EXITOSA: Sistema reconoce que tiene todas las respuestas necesarias (97/97)\n";
    } else {
        echo "❌ ERROR: Sistema indica que faltan respuestas\n";
    }
} catch (\Exception $e) {
    echo "❌ EXCEPCIÓN: " . $e->getMessage() . "\n";
}

echo "\n";

// ESCENARIO 3: Intentar con solo 88 preguntas pero diciendo que SÍ atiende clientes (debe fallar)
echo "--- ESCENARIO 3: SÍ atiende clientes pero solo 88 respuestas (debe fallar) ---\n";

// Limpiar respuestas
$db->table('responses')->where('worker_id', $workerId)->where('form_type', 'intralaboral_B')->delete();

// Mantener atiende_clientes = 1 (SÍ)
echo "✓ atiende_clientes = 1 (SÍ) mantenido\n";

// Crear solo 88 respuestas
$responses = [];
for ($i = 1; $i <= 88; $i++) {
    $responses[] = [
        'worker_id' => $workerId,
        'form_type' => 'intralaboral_B',
        'question_number' => $i,
        'answer_value' => 0,
        'session_id' => 'test-session-' . time(),
        'created_at' => date('Y-m-d H:i:s')
    ];
}

$db->table('responses')->insertBatch($responses);
echo "✓ Insertadas 88 respuestas (preguntas 1-88)\n";

// Intentar validar (debe indicar que faltan respuestas)
echo "Verificando si tiene todos los formularios completos...\n";
try {
    $hasAll = $calculationService->hasAllFormsCompleted($workerId);
    if ($hasAll) {
        echo "❌ ERROR: Sistema NO debería validar porque faltan preguntas 89-97\n";
    } else {
        echo "✅ CORRECTO: Sistema detecta que faltan respuestas (tiene 88, necesita 97)\n";
    }
} catch (\Exception $e) {
    echo "❌ EXCEPCIÓN: " . $e->getMessage() . "\n";
}

echo "\n=== FIN DEL TEST ===\n";

// Limpiar todo al final
echo "\n--- Limpiando datos de prueba... ---\n";
$db->table('responses')->where('worker_id', $workerId)->where('form_type', 'intralaboral_B')->delete();
$db->table('workers')->update(['atiende_clientes' => null], ['id' => $workerId]);
echo "✓ Limpieza completada\n";
