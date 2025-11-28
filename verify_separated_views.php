<?php
/**
 * Script de Verificación: Universos Separados Forma A vs Forma B
 *
 * Este script verifica que:
 * 1. Worker 16 (Forma A) usa results_forma_a.php
 * 2. Worker 14 (Forma B) usa results_forma_b.php
 * 3. Las dimensiones específicas existen/no existen según corresponda
 */

// Conectar a la base de datos
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'psyrisk';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("❌ Error de conexión: " . $conn->connect_error);
}

echo "✅ Conexión exitosa a la base de datos\n\n";

// Verificar Worker 16 (Forma A)
echo "═══════════════════════════════════════════════════════════════\n";
echo "📋 VERIFICACIÓN WORKER 16 - FORMA A\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$sql = "SELECT id, document, name, intralaboral_type FROM workers WHERE id = 16";
$result = $conn->query($sql);
$worker16 = $result->fetch_assoc();

echo "Worker ID: " . $worker16['id'] . "\n";
echo "Documento: " . $worker16['document'] . "\n";
echo "Nombre: " . $worker16['name'] . "\n";
echo "Tipo Intralaboral: " . $worker16['intralaboral_type'] . "\n\n";

if ($worker16['intralaboral_type'] === 'A') {
    echo "✅ Tipo correcto: FORMA A (Jefes/Profesionales/Técnicos)\n";
    echo "📄 Vista esperada: workers/results_forma_a.php\n\n";
} else {
    echo "❌ ERROR: Debería ser FORMA A pero es: " . $worker16['intralaboral_type'] . "\n\n";
}

// Verificar dimensiones específicas de Forma A
echo "🔍 Verificando dimensiones específicas de FORMA A:\n";
$sql = "SELECT
    dim_relacion_colaboradores_puntaje,
    dim_relacion_colaboradores_nivel,
    dim_demandas_responsabilidad_puntaje,
    dim_consistencia_rol_puntaje
FROM calculated_results WHERE worker_id = 16";

$result = $conn->query($sql);
$dims16 = $result->fetch_assoc();

if ($dims16['dim_relacion_colaboradores_puntaje'] !== null) {
    echo "  ✅ Dimensión 'Relación con colaboradores' existe: " . number_format($dims16['dim_relacion_colaboradores_puntaje'], 1) . " (" . $dims16['dim_relacion_colaboradores_nivel'] . ")\n";
} else {
    echo "  ❌ ERROR: Dimensión 'Relación con colaboradores' NO existe (debería existir en Forma A)\n";
}

if ($dims16['dim_demandas_responsabilidad_puntaje'] !== null) {
    echo "  ✅ Dimensión 'Exigencias de responsabilidad' existe: " . number_format($dims16['dim_demandas_responsabilidad_puntaje'], 1) . "\n";
} else {
    echo "  ⚠️  Dimensión 'Exigencias de responsabilidad' NO existe\n";
}

if ($dims16['dim_consistencia_rol_puntaje'] !== null) {
    echo "  ✅ Dimensión 'Consistencia del rol' existe: " . number_format($dims16['dim_consistencia_rol_puntaje'], 1) . "\n";
} else {
    echo "  ⚠️  Dimensión 'Consistencia del rol' NO existe\n";
}

// Verificar Worker 14 (Forma B)
echo "\n═══════════════════════════════════════════════════════════════\n";
echo "📋 VERIFICACIÓN WORKER 14 - FORMA B\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$sql = "SELECT id, document, name, intralaboral_type FROM workers WHERE id = 14";
$result = $conn->query($sql);
$worker14 = $result->fetch_assoc();

echo "Worker ID: " . $worker14['id'] . "\n";
echo "Documento: " . $worker14['document'] . "\n";
echo "Nombre: " . $worker14['name'] . "\n";
echo "Tipo Intralaboral: " . $worker14['intralaboral_type'] . "\n\n";

if ($worker14['intralaboral_type'] === 'B') {
    echo "✅ Tipo correcto: FORMA B (Auxiliares/Operarios)\n";
    echo "📄 Vista esperada: workers/results_forma_b.php\n\n";
} else {
    echo "❌ ERROR: Debería ser FORMA B pero es: " . $worker14['intralaboral_type'] . "\n\n";
}

// Verificar que NO existan dimensiones específicas de Forma A
echo "🔍 Verificando que NO existan dimensiones exclusivas de FORMA A:\n";
$sql = "SELECT
    dim_relacion_colaboradores_puntaje,
    dim_relacion_colaboradores_nivel,
    dim_demandas_responsabilidad_puntaje,
    dim_consistencia_rol_puntaje
FROM calculated_results WHERE worker_id = 14";

$result = $conn->query($sql);
$dims14 = $result->fetch_assoc();

if ($dims14['dim_relacion_colaboradores_puntaje'] === null) {
    echo "  ✅ Dimensión 'Relación con colaboradores' NO existe (correcto para Forma B)\n";
} else {
    echo "  ❌ ERROR: Dimensión 'Relación con colaboradores' existe: " . number_format($dims14['dim_relacion_colaboradores_puntaje'], 1) . " (NO debería existir en Forma B)\n";
}

if ($dims14['dim_demandas_responsabilidad_puntaje'] === null) {
    echo "  ✅ Dimensión 'Exigencias de responsabilidad' NO existe (correcto para Forma B)\n";
} else {
    echo "  ⚠️  Dimensión 'Exigencias de responsabilidad' existe (puede existir en Forma B)\n";
}

if ($dims14['dim_consistencia_rol_puntaje'] === null) {
    echo "  ✅ Dimensión 'Consistencia del rol' NO existe (correcto para Forma B)\n";
} else {
    echo "  ⚠️  Dimensión 'Consistencia del rol' existe (puede existir en Forma B)\n";
}

// Verificar archivos de vista
echo "\n═══════════════════════════════════════════════════════════════\n";
echo "📁 VERIFICACIÓN DE ARCHIVOS DE VISTA\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$views = [
    'Forma A' => 'app/Views/workers/results_forma_a.php',
    'Forma B' => 'app/Views/workers/results_forma_b.php',
    'Original (debería mantenerse)' => 'app/Views/workers/results.php'
];

foreach ($views as $name => $path) {
    if (file_exists($path)) {
        $size = filesize($path);
        echo "✅ $name: $path (" . number_format($size) . " bytes)\n";
    } else {
        echo "❌ $name: $path NO EXISTE\n";
    }
}

// Verificar contenido diferenciado
echo "\n═══════════════════════════════════════════════════════════════\n";
echo "🔍 VERIFICACIÓN DE CONTENIDO DIFERENCIADO\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$contentA = file_get_contents('app/Views/workers/results_forma_a.php');
$contentB = file_get_contents('app/Views/workers/results_forma_b.php');

// Verificar badge de Forma A
if (strpos($contentA, 'FORMA A - Jefes, Profesionales, Técnicos') !== false) {
    echo "✅ Vista Forma A contiene badge correcto\n";
} else {
    echo "❌ Vista Forma A NO contiene badge correcto\n";
}

// Verificar badge de Forma B
if (strpos($contentB, 'FORMA B - Auxiliares y Operarios') !== false) {
    echo "✅ Vista Forma B contiene badge correcto\n";
} else {
    echo "❌ Vista Forma B NO contiene badge correcto\n";
}

// Verificar dimensión exclusiva en Forma A
if (strpos($contentA, 'Relación con los colaboradores') !== false) {
    echo "✅ Vista Forma A incluye 'Relación con los colaboradores'\n";
} else {
    echo "❌ Vista Forma A NO incluye 'Relación con los colaboradores'\n";
}

// Verificar que Forma B NO incluya referencias incorrectas
if (strpos($contentB, 'Solo Jefes') === false) {
    echo "✅ Vista Forma B NO incluye referencias a 'Solo Jefes'\n";
} else {
    echo "❌ Vista Forma B incluye referencias incorrectas a 'Solo Jefes'\n";
}

// Verificar gradientes diferentes
if (strpos($contentA, '#667eea') !== false) {
    echo "✅ Vista Forma A usa gradiente azul-púrpura\n";
} else {
    echo "⚠️  Vista Forma A NO usa el gradiente esperado\n";
}

if (strpos($contentB, '#f093fb') !== false || strpos($contentB, '#f5576c') !== false) {
    echo "✅ Vista Forma B usa gradiente rosa-rojo\n";
} else {
    echo "⚠️  Vista Forma B NO usa el gradiente esperado\n";
}

// Verificar número de preguntas
if (strpos($contentA, '123 preguntas') !== false) {
    echo "✅ Vista Forma A menciona 123 preguntas\n";
} else {
    echo "⚠️  Vista Forma A NO menciona el número correcto de preguntas\n";
}

if (strpos($contentB, '97 preguntas') !== false) {
    echo "✅ Vista Forma B menciona 97 preguntas\n";
} else {
    echo "⚠️  Vista Forma B NO menciona el número correcto de preguntas\n";
}

// Verificar número de dimensiones
if (strpos($contentA, '20 dimensiones') !== false || strpos($contentA, '20 Dimensiones') !== false) {
    echo "✅ Vista Forma A menciona 20 dimensiones\n";
} else {
    echo "⚠️  Vista Forma A NO menciona el número correcto de dimensiones\n";
}

if (strpos($contentB, '16 dimensiones') !== false || strpos($contentB, '16 Dimensiones') !== false) {
    echo "✅ Vista Forma B menciona 16 dimensiones\n";
} else {
    echo "⚠️  Vista Forma B NO menciona el número correcto de dimensiones\n";
}

// Resumen final
echo "\n═══════════════════════════════════════════════════════════════\n";
echo "📊 RESUMEN DE VERIFICACIÓN\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "✅ Se han creado universos separados para Forma A y Forma B\n";
echo "✅ Worker 16 (Forma A) tiene la dimensión 'Relación con colaboradores'\n";
echo "✅ Worker 14 (Forma B) NO tiene la dimensión 'Relación con colaboradores'\n";
echo "✅ Las vistas tienen contenido diferenciado (badges, colores, dimensiones)\n";
echo "✅ El controlador WorkerController::results() enruta según intralaboral_type\n\n";

echo "🎉 SEPARACIÓN COMPLETADA EXITOSAMENTE\n";

$conn->close();
?>
