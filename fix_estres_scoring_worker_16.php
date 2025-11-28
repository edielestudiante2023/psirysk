<?php
/**
 * Script para corregir el scoring de respuestas de Estrés para Worker 16
 * Las respuestas fueron importadas como 0 (interfaz) pero deben ser scored según Tabla 4
 */

$mysqli = new mysqli("localhost", "root", "", "psyrisk");

if ($mysqli->connect_error) {
    die("Error de conexión: " . $mysqli->connect_error);
}

echo "\n========================================\n";
echo "CORRECCIÓN DE SCORING - ESTRÉS WORKER 16\n";
echo "========================================\n\n";

$workerId = 16;

// Definir grupos según Tabla 4
$grupo1 = [1, 2, 3, 9, 13, 14, 15, 23, 24];
$grupo2 = [4, 5, 6, 10, 11, 16, 17, 18, 19, 25, 26, 27, 28];
$grupo3 = [7, 8, 12, 20, 21, 22, 29, 30, 31];

// Mapping de valor interfaz -> valor scored
// Asumiendo que 0=Siempre, 1=Casi siempre, 2=A veces, 3=Nunca
$scoringGrupo1 = [0 => 9, 1 => 6, 2 => 3, 3 => 0];
$scoringGrupo2 = [0 => 6, 1 => 4, 2 => 2, 3 => 0];
$scoringGrupo3 = [0 => 3, 1 => 2, 2 => 1, 3 => 0];

// Obtener respuestas actuales
$result = $mysqli->query("SELECT id, question_number, answer_value
                          FROM responses
                          WHERE worker_id = {$workerId} AND form_type = 'estres'
                          ORDER BY question_number");

echo "Respuestas actuales (primeras 10):\n";
$count = 0;
$updates = [];

while ($row = $result->fetch_assoc()) {
    $id = $row['id'];
    $questionNumber = $row['question_number'];
    $currentValue = (int)$row['answer_value'];

    // Determinar el grupo y aplicar scoring
    if (in_array($questionNumber, $grupo1)) {
        $scoredValue = $scoringGrupo1[$currentValue] ?? 0;
        $grupo = 'Grupo 1';
    } elseif (in_array($questionNumber, $grupo2)) {
        $scoredValue = $scoringGrupo2[$currentValue] ?? 0;
        $grupo = 'Grupo 2';
    } elseif (in_array($questionNumber, $grupo3)) {
        $scoredValue = $scoringGrupo3[$currentValue] ?? 0;
        $grupo = 'Grupo 3';
    } else {
        $scoredValue = 0;
        $grupo = 'DESCONOCIDO';
    }

    $updates[] = [
        'id' => $id,
        'question_number' => $questionNumber,
        'old_value' => $currentValue,
        'new_value' => $scoredValue,
        'grupo' => $grupo
    ];

    if ($count < 10) {
        echo "  Q{$questionNumber} ({$grupo}): {$currentValue} -> {$scoredValue}\n";
        $count++;
    }
}

echo "\nTotal preguntas a actualizar: " . count($updates) . "\n";
echo "\n¿Continuar con la actualización? (Presiona ENTER para continuar)\n";
// readline(""); // Comentado para ejecución automática

echo "\nAplicando cambios...\n";

$updatedCount = 0;
foreach ($updates as $update) {
    $sql = "UPDATE responses
            SET answer_value = {$update['new_value']}
            WHERE id = {$update['id']}";

    if ($mysqli->query($sql)) {
        $updatedCount++;
    } else {
        echo "❌ Error actualizando Q{$update['question_number']}: " . $mysqli->error . "\n";
    }
}

echo "✅ Actualización completada: {$updatedCount} respuestas actualizadas\n\n";

// Verificar actualización
echo "========================================\n";
echo "VERIFICACIÓN POST-ACTUALIZACIÓN\n";
echo "========================================\n";

$result = $mysqli->query("SELECT question_number, answer_value
                          FROM responses
                          WHERE worker_id = {$workerId} AND form_type = 'estres'
                          ORDER BY question_number");

echo "\nPrimeras 10 respuestas actualizadas:\n";
$count = 0;
$suma = 0;
while ($row = $result->fetch_assoc()) {
    $q = $row['question_number'];
    $v = $row['answer_value'];
    $suma += $v;

    if ($count < 10) {
        $grupo = '';
        if (in_array($q, $grupo1)) $grupo = '(G1)';
        elseif (in_array($q, $grupo2)) $grupo = '(G2)';
        elseif (in_array($q, $grupo3)) $grupo = '(G3)';

        echo "  Q{$q} {$grupo}: {$v}\n";
        $count++;
    }
}

echo "\nPuntaje bruto total: {$suma}\n";
echo "Puntaje transformado: " . round(($suma / 61.1666666666666) * 100, 1) . "%\n";

// Eliminar resultados calculados para forzar recálculo
echo "\n========================================\n";
echo "ELIMINANDO RESULTADOS PREVIOS\n";
echo "========================================\n";

$mysqli->query("DELETE FROM calculated_results WHERE worker_id = {$workerId}");
echo "✅ Resultados eliminados. Listo para recalcular.\n";

$mysqli->close();
echo "\n";
?>
