<?php
/**
 * Script para aplicar migración de Consentimiento Informado en PRODUCCIÓN
 *
 * Este script agrega los campos consent_accepted y consent_accepted_at
 * a la tabla worker_demographics en la base de datos de producción
 */

// Configuración de producción
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

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║  APLICAR MIGRACIÓN DE CONSENTIMIENTO INFORMADO - PRODUCCIÓN    ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

echo "⚠️  ADVERTENCIA: Este script modificará la base de datos de PRODUCCIÓN\n";
echo "📊 Base de datos: {$prod_config['database']}\n";
echo "🌐 Host: {$prod_config['host']}\n\n";

// Solicitar confirmación
echo "¿Desea continuar? Escriba 'SI' para confirmar: ";
$handle = fopen("php://stdin", "r");
$confirmation = trim(fgets($handle));
fclose($handle);

if (strtoupper($confirmation) !== 'SI') {
    echo "\n❌ Operación cancelada por el usuario.\n";
    exit(0);
}

echo "\n";

try {
    // Conectar a la base de datos de producción con SSL
    echo "🔌 Conectando a la base de datos de producción...\n";

    $dsn = "mysql:host={$prod_config['host']};port={$prod_config['port']};dbname={$prod_config['database']};charset={$prod_config['charset']}";

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false, // Para DigitalOcean
    ];

    $pdo = new PDO($dsn, $prod_config['username'], $prod_config['password'], $options);
    echo "✅ Conexión establecida exitosamente\n\n";

    // 1. Verificar si la tabla existe
    echo "📋 PASO 1: Verificando existencia de tabla worker_demographics...\n";
    $stmt = $pdo->query("SHOW TABLES LIKE 'worker_demographics'");
    $tableExists = $stmt->rowCount() > 0;

    if (!$tableExists) {
        throw new Exception("❌ La tabla worker_demographics NO existe en producción");
    }
    echo "✅ Tabla worker_demographics encontrada\n\n";

    // 2. Verificar estructura actual
    echo "📋 PASO 2: Verificando estructura actual de la tabla...\n";
    $stmt = $pdo->query("DESCRIBE worker_demographics");
    $columns = $stmt->fetchAll();

    $hasConsentAccepted = false;
    $hasConsentAcceptedAt = false;

    echo "Columnas actuales:\n";
    foreach ($columns as $column) {
        echo "  - {$column['Field']} ({$column['Type']})\n";
        if ($column['Field'] === 'consent_accepted') {
            $hasConsentAccepted = true;
        }
        if ($column['Field'] === 'consent_accepted_at') {
            $hasConsentAcceptedAt = true;
        }
    }
    echo "\n";

    // 3. Aplicar migración si es necesario
    echo "📋 PASO 3: Aplicando migración...\n";

    $changesApplied = false;

    // Agregar campo consent_accepted si no existe
    if (!$hasConsentAccepted) {
        echo "  ➜ Agregando campo 'consent_accepted'...\n";
        $sql = "ALTER TABLE worker_demographics
                ADD COLUMN consent_accepted TINYINT(1) NULL DEFAULT NULL
                COMMENT 'Indica si el trabajador aceptó el consentimiento informado'
                AFTER worker_id";
        $pdo->exec($sql);
        echo "  ✅ Campo 'consent_accepted' agregado exitosamente\n";
        $changesApplied = true;
    } else {
        echo "  ℹ️  Campo 'consent_accepted' ya existe, omitiendo...\n";
    }

    // Agregar campo consent_accepted_at si no existe
    if (!$hasConsentAcceptedAt) {
        echo "  ➜ Agregando campo 'consent_accepted_at'...\n";
        $sql = "ALTER TABLE worker_demographics
                ADD COLUMN consent_accepted_at DATETIME NULL DEFAULT NULL
                COMMENT 'Fecha y hora en que se aceptó el consentimiento'
                AFTER consent_accepted";
        $pdo->exec($sql);
        echo "  ✅ Campo 'consent_accepted_at' agregado exitosamente\n";
        $changesApplied = true;
    } else {
        echo "  ℹ️  Campo 'consent_accepted_at' ya existe, omitiendo...\n";
    }

    echo "\n";

    // 4. Verificar cambios aplicados
    echo "📋 PASO 4: Verificando cambios aplicados...\n";
    $stmt = $pdo->query("DESCRIBE worker_demographics");
    $newColumns = $stmt->fetchAll();

    $verifiedConsentAccepted = false;
    $verifiedConsentAcceptedAt = false;

    echo "Estructura final de la tabla:\n";
    foreach ($newColumns as $column) {
        if ($column['Field'] === 'consent_accepted') {
            echo "  ✅ {$column['Field']} - {$column['Type']} - {$column['Null']} - {$column['Default']}\n";
            $verifiedConsentAccepted = true;
        } elseif ($column['Field'] === 'consent_accepted_at') {
            echo "  ✅ {$column['Field']} - {$column['Type']} - {$column['Null']} - {$column['Default']}\n";
            $verifiedConsentAcceptedAt = true;
        }
    }
    echo "\n";

    // 5. Resumen final
    echo "╔════════════════════════════════════════════════════════════════╗\n";
    echo "║                      RESUMEN DE MIGRACIÓN                      ║\n";
    echo "╚════════════════════════════════════════════════════════════════╝\n\n";

    if ($changesApplied) {
        echo "✅ MIGRACIÓN COMPLETADA EXITOSAMENTE\n\n";
        echo "Cambios aplicados:\n";
        if (!$hasConsentAccepted) {
            echo "  ✅ Campo 'consent_accepted' agregado\n";
        }
        if (!$hasConsentAcceptedAt) {
            echo "  ✅ Campo 'consent_accepted_at' agregado\n";
        }
    } else {
        echo "ℹ️  NO SE APLICARON CAMBIOS\n\n";
        echo "La tabla ya contiene los campos necesarios:\n";
        echo "  ✅ consent_accepted\n";
        echo "  ✅ consent_accepted_at\n";
    }

    echo "\n";

    if ($verifiedConsentAccepted && $verifiedConsentAcceptedAt) {
        echo "✅ VERIFICACIÓN EXITOSA: Ambos campos existen en la tabla\n";
    } else {
        echo "⚠️  ADVERTENCIA: Verificación incompleta\n";
        if (!$verifiedConsentAccepted) {
            echo "  ❌ Campo 'consent_accepted' no verificado\n";
        }
        if (!$verifiedConsentAcceptedAt) {
            echo "  ❌ Campo 'consent_accepted_at' no verificado\n";
        }
    }

    echo "\n";
    echo "🎉 Base de datos de PRODUCCIÓN actualizada correctamente\n";
    echo "🔄 El proyecto ahora está balanceado entre desarrollo y producción\n\n";

    // 6. Información adicional
    echo "📝 INFORMACIÓN ADICIONAL:\n";
    echo "  - Los campos permiten valores NULL por defecto\n";
    echo "  - Los trabajadores existentes tendrán consent_accepted = NULL\n";
    echo "  - Solo los nuevos trabajadores verán el consentimiento informado\n";
    echo "  - Para resetear un trabajador: UPDATE worker_demographics SET consent_accepted = NULL WHERE worker_id = X\n";

} catch (PDOException $e) {
    echo "\n❌ ERROR DE BASE DE DATOS:\n";
    echo "Código: " . $e->getCode() . "\n";
    echo "Mensaje: " . $e->getMessage() . "\n";
    echo "\nDetalles técnicos:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "\n❌ ERROR:\n";
    echo $e->getMessage() . "\n";
    exit(1);
}

echo "\n╔════════════════════════════════════════════════════════════════╗\n";
echo "║                    PROCESO COMPLETADO                          ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";
