<?php
/**
 * Script de verificación de sincronización entre DEV y PROD
 * Verifica que los campos de consentimiento informado estén en ambos entornos
 */

// Configuración de DESARROLLO (local)
$dev_config = [
    'host' => 'localhost',
    'port' => 3306,
    'database' => 'psyrisk',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4'
];

// Configuración de PRODUCCIÓN
// IMPORTANTE: Define estas variables de entorno o crea un archivo .env.prod
$prod_config = [
    'host' => getenv('PROD_DB_HOST') ?: 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
    'port' => getenv('PROD_DB_PORT') ?: 25060,
    'database' => getenv('PROD_DB_NAME') ?: 'psyrisk',
    'username' => getenv('PROD_DB_USER') ?: 'cycloid_userdb',
    'password' => getenv('PROD_DB_PASS') ?: '',  // REQUERIDO: Define PROD_DB_PASS en variables de entorno
    'charset' => 'utf8mb4'
];

if (empty($prod_config['password'])) {
    die("❌ ERROR: La variable de entorno PROD_DB_PASS no está definida.\n" .
        "Por favor, define las credenciales de producción antes de ejecutar este script.\n");
}

echo "╔═══════════════════════════════════════════════════════════════════╗\n";
echo "║     VERIFICACIÓN DE SINCRONIZACIÓN: DESARROLLO vs PRODUCCIÓN      ║\n";
echo "╚═══════════════════════════════════════════════════════════════════╝\n\n";

function connectDatabase($config, $env_name) {
    echo "🔌 Conectando a {$env_name}...\n";

    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        if ($env_name === 'PRODUCCIÓN') {
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }

        $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
        echo "✅ Conexión a {$env_name} establecida\n\n";
        return $pdo;
    } catch (PDOException $e) {
        echo "❌ Error conectando a {$env_name}: " . $e->getMessage() . "\n";
        return null;
    }
}

function getTableStructure($pdo, $env_name) {
    echo "📋 Obteniendo estructura de worker_demographics en {$env_name}...\n";

    $stmt = $pdo->query("DESCRIBE worker_demographics");
    $columns = $stmt->fetchAll();

    $structure = [
        'columns' => [],
        'has_consent_accepted' => false,
        'has_consent_accepted_at' => false,
        'consent_accepted_details' => null,
        'consent_accepted_at_details' => null
    ];

    foreach ($columns as $column) {
        $structure['columns'][$column['Field']] = $column;

        if ($column['Field'] === 'consent_accepted') {
            $structure['has_consent_accepted'] = true;
            $structure['consent_accepted_details'] = $column;
        }

        if ($column['Field'] === 'consent_accepted_at') {
            $structure['has_consent_accepted_at'] = true;
            $structure['consent_accepted_at_details'] = $column;
        }
    }

    echo "✅ Estructura obtenida (" . count($structure['columns']) . " columnas)\n\n";
    return $structure;
}

try {
    // Conectar a ambos entornos
    $dev_pdo = connectDatabase($dev_config, 'DESARROLLO');
    $prod_pdo = connectDatabase($prod_config, 'PRODUCCIÓN');

    if (!$dev_pdo || !$prod_pdo) {
        throw new Exception("No se pudo conectar a uno o ambos entornos");
    }

    // Obtener estructuras
    $dev_structure = getTableStructure($dev_pdo, 'DESARROLLO');
    $prod_structure = getTableStructure($prod_pdo, 'PRODUCCIÓN');

    // Comparar estructuras
    echo "╔═══════════════════════════════════════════════════════════════════╗\n";
    echo "║                    COMPARACIÓN DE ESTRUCTURAS                     ║\n";
    echo "╚═══════════════════════════════════════════════════════════════════╝\n\n";

    // Verificar campos de consentimiento
    echo "📊 CAMPOS DE CONSENTIMIENTO INFORMADO:\n\n";

    echo "┌─────────────────────────────┬──────────────┬──────────────┐\n";
    echo "│ Campo                       │ Desarrollo   │ Producción   │\n";
    echo "├─────────────────────────────┼──────────────┼──────────────┤\n";

    // consent_accepted
    $dev_check = $dev_structure['has_consent_accepted'] ? '✅ SÍ' : '❌ NO';
    $prod_check = $prod_structure['has_consent_accepted'] ? '✅ SÍ' : '❌ NO';
    printf("│ %-27s │ %-12s │ %-12s │\n", 'consent_accepted', $dev_check, $prod_check);

    // consent_accepted_at
    $dev_check = $dev_structure['has_consent_accepted_at'] ? '✅ SÍ' : '❌ NO';
    $prod_check = $prod_structure['has_consent_accepted_at'] ? '✅ SÍ' : '❌ NO';
    printf("│ %-27s │ %-12s │ %-12s │\n", 'consent_accepted_at', $dev_check, $prod_check);

    echo "└─────────────────────────────┴──────────────┴──────────────┘\n\n";

    // Detalles de los campos
    if ($dev_structure['has_consent_accepted'] && $prod_structure['has_consent_accepted']) {
        echo "📝 DETALLES DEL CAMPO 'consent_accepted':\n";
        $dev_details = $dev_structure['consent_accepted_details'];
        $prod_details = $prod_structure['consent_accepted_details'];

        echo "  DESARROLLO:\n";
        echo "    - Tipo: {$dev_details['Type']}\n";
        echo "    - Null: {$dev_details['Null']}\n";
        echo "    - Default: " . ($dev_details['Default'] ?? 'NULL') . "\n";

        echo "  PRODUCCIÓN:\n";
        echo "    - Tipo: {$prod_details['Type']}\n";
        echo "    - Null: {$prod_details['Null']}\n";
        echo "    - Default: " . ($prod_details['Default'] ?? 'NULL') . "\n\n";
    }

    if ($dev_structure['has_consent_accepted_at'] && $prod_structure['has_consent_accepted_at']) {
        echo "📝 DETALLES DEL CAMPO 'consent_accepted_at':\n";
        $dev_details = $dev_structure['consent_accepted_at_details'];
        $prod_details = $prod_structure['consent_accepted_at_details'];

        echo "  DESARROLLO:\n";
        echo "    - Tipo: {$dev_details['Type']}\n";
        echo "    - Null: {$dev_details['Null']}\n";
        echo "    - Default: " . ($dev_details['Default'] ?? 'NULL') . "\n";

        echo "  PRODUCCIÓN:\n";
        echo "    - Tipo: {$prod_details['Type']}\n";
        echo "    - Null: {$prod_details['Null']}\n";
        echo "    - Default: " . ($prod_details['Default'] ?? 'NULL') . "\n\n";
    }

    // Comparar totales de columnas
    $dev_count = count($dev_structure['columns']);
    $prod_count = count($prod_structure['columns']);

    echo "📊 TOTAL DE COLUMNAS:\n";
    echo "  - Desarrollo: {$dev_count} columnas\n";
    echo "  - Producción: {$prod_count} columnas\n\n";

    // Resultado final
    echo "╔═══════════════════════════════════════════════════════════════════╗\n";
    echo "║                         RESULTADO FINAL                           ║\n";
    echo "╚═══════════════════════════════════════════════════════════════════╝\n\n";

    $is_synced = $dev_structure['has_consent_accepted'] === $prod_structure['has_consent_accepted'] &&
                 $dev_structure['has_consent_accepted_at'] === $prod_structure['has_consent_accepted_at'];

    if ($is_synced && $dev_structure['has_consent_accepted'] && $dev_structure['has_consent_accepted_at']) {
        echo "✅ ¡SINCRONIZACIÓN EXITOSA!\n\n";
        echo "Ambos entornos tienen los campos de consentimiento informado:\n";
        echo "  ✅ consent_accepted\n";
        echo "  ✅ consent_accepted_at\n\n";
        echo "🎉 El proyecto está balanceado y simétrico entre DEV y PROD\n";
    } elseif ($is_synced) {
        echo "⚠️  AMBOS ENTORNOS ESTÁN SINCRONIZADOS\n\n";
        echo "Pero faltan los campos de consentimiento informado en ambos\n";
        echo "Ejecute: php apply_consent_migration_prod.php\n";
    } else {
        echo "❌ LOS ENTORNOS NO ESTÁN SINCRONIZADOS\n\n";
        echo "Diferencias encontradas:\n";

        if ($dev_structure['has_consent_accepted'] !== $prod_structure['has_consent_accepted']) {
            echo "  - Campo 'consent_accepted' difiere entre entornos\n";
        }

        if ($dev_structure['has_consent_accepted_at'] !== $prod_structure['has_consent_accepted_at']) {
            echo "  - Campo 'consent_accepted_at' difiere entre entornos\n";
        }

        echo "\nAcción requerida:\n";
        if (!$prod_structure['has_consent_accepted'] || !$prod_structure['has_consent_accepted_at']) {
            echo "  → Ejecutar: php apply_consent_migration_prod.php\n";
        }
    }

} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n╔═══════════════════════════════════════════════════════════════════╗\n";
echo "║                    VERIFICACIÓN COMPLETADA                        ║\n";
echo "╚═══════════════════════════════════════════════════════════════════╝\n";
