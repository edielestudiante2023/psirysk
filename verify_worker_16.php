<?php
/**
 * Script de verificación de cálculos para Worker 16
 * Valida que todos los cálculos sean correctos cuando todas las respuestas son 4
 */

require __DIR__ . '/vendor/autoload.php';

// Cargar CodeIgniter
$app = \Config\Services::codeigniter();
$app->initialize();

use App\Models\ResponseModel;
use App\Models\WorkerModel;
use App\Models\CalculatedResultModel;

$responseModel = new ResponseModel();
$workerModel = new WorkerModel();
$resultModel = new CalculatedResultModel();

echo "\n========================================\n";
echo "VERIFICACIÓN DE CÁLCULOS - WORKER 16\n";
echo "========================================\n\n";

$workerId = 16;

// 1. Obtener información del worker
$worker = $workerModel->find($workerId);
echo "Worker ID: {$workerId}\n";
echo "Tipo Intralaboral: {$worker['intralaboral_type']}\n";
echo "Atiende Clientes: " . ($worker['atiende_clientes'] ? 'SÍ' : 'NO') . "\n";
echo "Es Jefe: " . ($worker['es_jefe'] ? 'SÍ' : 'NO') . "\n\n";

// 2. Obtener respuestas Intralaboral A
$responses = $responseModel->getWorkerFormResponses($workerId, 'intralaboral_a');
echo "Total respuestas Intralaboral A: " . count($responses) . "\n";

// Convertir a array
$answersArray = [];
foreach ($responses as $response) {
    $answersArray[$response['question_number']] = $response['answer_value'];
}

// 3. DIMENSIÓN: Demandas ambientales y de esfuerzo físico (preguntas 1-12)
echo "\n--- DIMENSIÓN: Demandas ambientales y de esfuerzo físico ---\n";
echo "Preguntas: 1-12\n";
$preguntas_dim1 = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
$suma_dim1 = 0;
foreach ($preguntas_dim1 as $q) {
    $valor = $answersArray[$q] ?? 0;
    $suma_dim1 += $valor;
    echo "  Q{$q}: {$valor}\n";
}
echo "Puntaje Bruto: {$suma_dim1}\n";
$factor_dim1 = 48;
$transformado_dim1 = round(($suma_dim1 / $factor_dim1) * 100, 1);
echo "Factor de transformación: {$factor_dim1}\n";
echo "Puntaje Transformado: {$transformado_dim1}%\n";
echo "Esperado: 100.0%\n";
echo "¿Correcto? " . ($transformado_dim1 == 100.0 ? '✅ SÍ' : '❌ NO') . "\n";

// 4. DIMENSIÓN: Demandas cuantitativas (preguntas 13-15)
echo "\n--- DIMENSIÓN: Demandas cuantitativas ---\n";
echo "Preguntas: 13-15\n";
$preguntas_dim2 = [13, 14, 15];
$suma_dim2 = 0;
foreach ($preguntas_dim2 as $q) {
    $valor = $answersArray[$q] ?? 0;
    $suma_dim2 += $valor;
    echo "  Q{$q}: {$valor}\n";
}
echo "Puntaje Bruto: {$suma_dim2}\n";
$factor_dim2 = 12;
$transformado_dim2 = round(($suma_dim2 / $factor_dim2) * 100, 1);
echo "Factor de transformación: {$factor_dim2}\n";
echo "Puntaje Transformado: {$transformado_dim2}%\n";
echo "Esperado: 100.0%\n";
echo "¿Correcto? " . ($transformado_dim2 == 100.0 ? '✅ SÍ' : '❌ NO') . "\n";

// 5. DIMENSIÓN: Influencia del trabajo sobre el entorno extralaboral (preguntas 35-38)
echo "\n--- DIMENSIÓN: Influencia del trabajo sobre el entorno extralaboral ---\n";
echo "Preguntas: 35-38\n";
$preguntas_dim3 = [35, 36, 37, 38];
$suma_dim3 = 0;
foreach ($preguntas_dim3 as $q) {
    $valor = $answersArray[$q] ?? 0;
    $suma_dim3 += $valor;
    echo "  Q{$q}: {$valor}\n";
}
echo "Puntaje Bruto: {$suma_dim3}\n";
$factor_dim3 = 16; // ERROR DETECTADO: debería ser 32 según línea 80 de IntralaboralAScoring.php
echo "Factor de transformación según línea 55 IntralaboralAScoring: 32\n";
$transformado_dim3_correcto = round(($suma_dim3 / 32) * 100, 1);
echo "Puntaje Transformado (con factor 32): {$transformado_dim3_correcto}%\n";

// Pero el código usa preguntas 31-38 (8 preguntas)
echo "\n⚠️ VERIFICACIÓN DE PREGUNTAS:\n";
echo "Según línea 55 de IntralaboralAScoring.php:\n";
echo "  'influencia_trabajo_entorno_extralaboral' => [31, 32, 33, 34, 35, 36, 37, 38]\n";
echo "Son 8 preguntas (31-38), no 4 (35-38)\n";
$preguntas_dim3_real = [31, 32, 33, 34, 35, 36, 37, 38];
$suma_dim3_real = 0;
foreach ($preguntas_dim3_real as $q) {
    $valor = $answersArray[$q] ?? 0;
    $suma_dim3_real += $valor;
    echo "  Q{$q}: {$valor}\n";
}
echo "Puntaje Bruto CORRECTO: {$suma_dim3_real}\n";
$factor_dim3_real = 32;
$transformado_dim3_real = round(($suma_dim3_real / $factor_dim3_real) * 100, 1);
echo "Puntaje Transformado CORRECTO: {$transformado_dim3_real}%\n";
echo "Esperado: 100.0%\n";
echo "¿Correcto? " . ($transformado_dim3_real == 100.0 ? '✅ SÍ' : '❌ NO') . "\n";

// 6. DIMENSIÓN: Exigencias de responsabilidad del cargo (preguntas 22-26)
echo "\n--- DIMENSIÓN: Exigencias de responsabilidad del cargo ---\n";
echo "Preguntas: 22-26\n";
$preguntas_dim4 = [22, 23, 24, 25, 26];
$suma_dim4 = 0;
foreach ($preguntas_dim4 as $q) {
    $valor = $answersArray[$q] ?? 0;
    $suma_dim4 += $valor;
    echo "  Q{$q}: {$valor}\n";
}
echo "Puntaje Bruto: {$suma_dim4}\n";
$factor_dim4 = 20;
$transformado_dim4 = round(($suma_dim4 / $factor_dim4) * 100, 1);
echo "Factor de transformación: {$factor_dim4}\n";
echo "Puntaje Transformado: {$transformado_dim4}%\n";
echo "Esperado: 100.0%\n";
echo "¿Correcto? " . ($transformado_dim4 == 100.0 ? '✅ SÍ' : '❌ NO') . "\n";

// 7. DIMENSIÓN: Demandas de carga mental (preguntas 16-21)
echo "\n--- DIMENSIÓN: Demandas de carga mental ---\n";
echo "Preguntas: 16-21\n";
$preguntas_dim5 = [16, 17, 18, 19, 20, 21];
$suma_dim5 = 0;
foreach ($preguntas_dim5 as $q) {
    $valor = $answersArray[$q] ?? 0;
    $suma_dim5 += $valor;
    echo "  Q{$q}: {$valor}\n";
}
echo "Puntaje Bruto: {$suma_dim5}\n";
$factor_dim5 = 24;
$transformado_dim5 = round(($suma_dim5 / $factor_dim5) * 100, 1);
echo "Factor de transformación: {$factor_dim5}\n";
echo "Puntaje Transformado: {$transformado_dim5}%\n";
echo "Esperado: 100.0%\n";
echo "¿Correcto? " . ($transformado_dim5 == 100.0 ? '✅ SÍ' : '❌ NO') . "\n";

// 8. DIMENSIÓN: Consistencia del rol (preguntas 27-30)
echo "\n--- DIMENSIÓN: Consistencia del rol ---\n";
echo "Preguntas: 27-30\n";
$preguntas_dim6 = [27, 28, 29, 30];
$suma_dim6 = 0;
foreach ($preguntas_dim6 as $q) {
    $valor = $answersArray[$q] ?? 0;
    $suma_dim6 += $valor;
    echo "  Q{$q}: {$valor}\n";
}
echo "Puntaje Bruto: {$suma_dim6}\n";
$factor_dim6 = 16;
$transformado_dim6 = round(($suma_dim6 / $factor_dim6) * 100, 1);
echo "Factor de transformación: {$factor_dim6}\n";
echo "Puntaje Transformado: {$transformado_dim6}%\n";
echo "Esperado: 100.0%\n";
echo "¿Correcto? " . ($transformado_dim6 == 100.0 ? '✅ SÍ' : '❌ NO') . "\n";

// 9. DIMENSIÓN: Demandas de la jornada de trabajo (preguntas 31, 33)
echo "\n--- DIMENSIÓN: Demandas de la jornada de trabajo ---\n";
echo "Preguntas: 31, 33\n";
$preguntas_dim7 = [31, 33];
$suma_dim7 = 0;
foreach ($preguntas_dim7 as $q) {
    $valor = $answersArray[$q] ?? 0;
    $suma_dim7 += $valor;
    echo "  Q{$q}: {$valor}\n";
}
echo "Puntaje Bruto: {$suma_dim7}\n";
$factor_dim7 = 8;
$transformado_dim7 = round(($suma_dim7 / $factor_dim7) * 100, 1);
echo "Factor de transformación: {$factor_dim7}\n";
echo "Puntaje Transformado: {$transformado_dim7}%\n";
echo "Esperado: 100.0%\n";
echo "¿Correcto? " . ($transformado_dim7 == 100.0 ? '✅ SÍ' : '❌ NO') . "\n";

// 10. DIMENSIÓN: Demandas emocionales (preguntas 106-114) - condicional
if ($worker['atiende_clientes']) {
    echo "\n--- DIMENSIÓN: Demandas emocionales (CONDICIONAL - Atiende Clientes) ---\n";
    echo "Preguntas: 106-114\n";
    $preguntas_dim8 = [106, 107, 108, 109, 110, 111, 112, 113, 114];
    $suma_dim8 = 0;
    foreach ($preguntas_dim8 as $q) {
        $valor = $answersArray[$q] ?? 0;
        $suma_dim8 += $valor;
        echo "  Q{$q}: {$valor}\n";
    }
    echo "Puntaje Bruto: {$suma_dim8}\n";
    $factor_dim8 = 36;
    $transformado_dim8 = round(($suma_dim8 / $factor_dim8) * 100, 1);
    echo "Factor de transformación: {$factor_dim8}\n";
    echo "Puntaje Transformado: {$transformado_dim8}%\n";
    echo "Esperado: 100.0%\n";
    echo "¿Correcto? " . ($transformado_dim8 == 100.0 ? '✅ SÍ' : '❌ NO') . "\n";
} else {
    echo "\n--- DIMENSIÓN: Demandas emocionales ---\n";
    echo "NO APLICA (Worker no atiende clientes)\n";
}

// 11. CÁLCULO DEL DOMINIO 3: Demandas
echo "\n========================================\n";
echo "DOMINIO 3: DEMANDAS DEL TRABAJO\n";
echo "========================================\n";
echo "Suma de puntajes brutos de todas las dimensiones:\n";
$suma_dominio3 = $suma_dim1 + $suma_dim2 + $suma_dim3_real + $suma_dim4 + $suma_dim5 + $suma_dim6 + $suma_dim7;
echo "  Demandas ambientales: {$suma_dim1}\n";
echo "  Demandas cuantitativas: {$suma_dim2}\n";
echo "  Influencia trabajo-extralaboral: {$suma_dim3_real}\n";
echo "  Responsabilidad del cargo: {$suma_dim4}\n";
echo "  Carga mental: {$suma_dim5}\n";
echo "  Consistencia del rol: {$suma_dim6}\n";
echo "  Jornada de trabajo: {$suma_dim7}\n";

if ($worker['atiende_clientes']) {
    $suma_dominio3 += $suma_dim8;
    echo "  Demandas emocionales: {$suma_dim8}\n";
}

echo "\nPuntaje Bruto Total Dominio 3: {$suma_dominio3}\n";

// Factor de transformación según si atiende clientes o no
if ($worker['atiende_clientes']) {
    $factor_dominio3 = 216; // Incluye demandas emocionales
} else {
    $factor_dominio3 = 216; // El factor es siempre 216, las dimensiones condicionales se omiten del cálculo
}

echo "Factor de transformación Dominio 3: {$factor_dominio3}\n";
$transformado_dominio3 = round(($suma_dominio3 / $factor_dominio3) * 100, 1);
echo "Puntaje Transformado Dominio 3: {$transformado_dominio3}%\n";
echo "Esperado: 100.0% (o menor si no atiende clientes)\n";

// 12. Obtener resultado de la BD
$result = $resultModel->getByWorkerId($workerId);
echo "\n========================================\n";
echo "COMPARACIÓN CON BASE DE DATOS\n";
echo "========================================\n";
echo "Dominio 3 en BD: {$result['dom_demandas_puntaje']}%\n";
echo "Dominio 3 calculado: {$transformado_dominio3}%\n";
echo "¿Coinciden? " . ($result['dom_demandas_puntaje'] == $transformado_dominio3 ? '✅ SÍ' : '❌ NO') . "\n";

echo "\n========================================\n";
echo "RESUMEN DE DIMENSIONES EN BD\n";
echo "========================================\n";
echo "dim_demandas_ambientales_puntaje: {$result['dim_demandas_ambientales_puntaje']}\n";
echo "dim_demandas_cuantitativas_puntaje: {$result['dim_demandas_cuantitativas_puntaje']}\n";
echo "dim_influencia_trabajo_entorno_extralaboral_puntaje: {$result['dim_influencia_trabajo_entorno_extralaboral_puntaje']}\n";
echo "dim_demandas_responsabilidad_puntaje: {$result['dim_demandas_responsabilidad_puntaje']}\n";
echo "dim_demandas_carga_mental_puntaje: {$result['dim_demandas_carga_mental_puntaje']}\n";
echo "dim_consistencia_rol_puntaje: {$result['dim_consistencia_rol_puntaje']}\n";
echo "dim_demandas_jornada_trabajo_puntaje: {$result['dim_demandas_jornada_trabajo_puntaje']}\n";
if ($worker['atiende_clientes']) {
    echo "dim_demandas_emocionales_puntaje: {$result['dim_demandas_emocionales_puntaje']}\n";
}

echo "\n========================================\n";
echo "FIN DE VERIFICACIÓN\n";
echo "========================================\n\n";
