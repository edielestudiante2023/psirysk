<?php
/**
 * verify_multitenant.php
 * --------------------------------------------------------
 * Script de verificación POST-MIGRACIÓN para producción.
 * Solo SELECTs. Cero escritura.
 *
 * Ejecutar tras correr las 8 migraciones en orden:
 *   php scripts/verify_multitenant.php
 *
 * Sale con código 0 si todo OK; código 1 si hay anomalías.
 *
 * Configurar las credenciales vía variables de entorno o
 * editar este archivo solo durante la ejecución (luego borrar valores).
 */

$host = getenv('DB_HOST') ?: 'localhost';
$port = (int) (getenv('DB_PORT') ?: 3306);
$db   = getenv('DB_NAME') ?: 'psyrisk';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';

$dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
$opts = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $opts);
} catch (Throwable $e) {
    fwrite(STDERR, "✗ Conexión falló: " . $e->getMessage() . PHP_EOL);
    exit(1);
}

$failures = [];
$warnings = [];

function check(string $label, bool $ok, string $detail, array &$failures, array &$warnings, bool $warning = false): void
{
    $icon = $ok ? '✓' : ($warning ? '⚠' : '✗');
    $color = $ok ? "\033[32m" : ($warning ? "\033[33m" : "\033[31m");
    $reset = "\033[0m";
    echo "{$color}{$icon}{$reset} {$label}";
    if ($detail) echo " — {$detail}";
    echo PHP_EOL;
    if (!$ok) {
        if ($warning) $warnings[] = "{$label}: {$detail}";
        else $failures[] = "{$label}: {$detail}";
    }
}

echo "\n=== VERIFICACIÓN MULTI-TENANT psyrisk ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

// 1. Tablas creadas
echo "[1/8] Tablas creadas\n";
$expected = ['tenants', 'tenant_signups', 'billing_transactions', 'credit_movements'];
foreach ($expected as $t) {
    $exists = $pdo->query("SHOW TABLES LIKE " . $pdo->quote($t))->fetchColumn();
    check("Tabla {$t} existe", (bool) $exists, $exists ? '' : 'NO ENCONTRADA', $failures, $warnings);
}

// 2. Cycloid es tenant 1
echo "\n[2/8] Tenant raíz Cycloid\n";
$cycloid = $pdo->query("SELECT id, slug, legal_name, status FROM tenants WHERE id = 1")->fetch();
check("Tenant id=1 existe", (bool) $cycloid,
    $cycloid ? "{$cycloid['legal_name']} ({$cycloid['status']})" : 'NO EXISTE', $failures, $warnings);
if ($cycloid) {
    check("Slug = 'cycloid'", $cycloid['slug'] === 'cycloid',
        "slug actual: {$cycloid['slug']}", $failures, $warnings);
}

// 3. Columna tenant_id en las 8 tablas operativas
echo "\n[3/8] Columna tenant_id\n";
$scoped = ['companies','users','battery_services','consultants','reports','action_plans','csv_imports','individual_results_requests'];
foreach ($scoped as $t) {
    $col = $pdo->query("SHOW COLUMNS FROM `{$t}` LIKE 'tenant_id'")->fetch();
    check("`{$t}.tenant_id` existe", (bool) $col, '', $failures, $warnings);
}

// 4. Backfill: 0 huérfanos (excepto users.id=1 que es global)
echo "\n[4/8] Backfill — registros sin tenant\n";
foreach ($scoped as $t) {
    if ($t === 'users') {
        $orphans = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE tenant_id IS NULL AND id <> 1")->fetchColumn();
        check("`users` sin tenant (excepto SUPER ADMIN id=1)", $orphans === 0,
            $orphans > 0 ? "{$orphans} huérfanos" : 'OK', $failures, $warnings);
    } else {
        try {
            $orphans = (int) $pdo->query("SELECT COUNT(*) FROM `{$t}` WHERE tenant_id IS NULL")->fetchColumn();
            check("`{$t}` sin tenant", $orphans === 0,
                $orphans > 0 ? "{$orphans} huérfanos" : 'OK', $failures, $warnings);
        } catch (Throwable $e) {
            check("`{$t}` sin tenant", false, $e->getMessage(), $failures, $warnings);
        }
    }
}

// 5. NOT NULL constraint en columnas críticas
echo "\n[5/8] NOT NULL en tenant_id (post-005)\n";
$mustBeNotNull = ['companies','battery_services','consultants','reports','action_plans','csv_imports','individual_results_requests'];
foreach ($mustBeNotNull as $t) {
    $col = $pdo->query("SHOW COLUMNS FROM `{$t}` LIKE 'tenant_id'")->fetch();
    if ($col) {
        $isNullable = $col['Null'] === 'YES';
        check("`{$t}.tenant_id` es NOT NULL", !$isNullable,
            $isNullable ? 'todavía es NULL — falta correr 005' : '', $failures, $warnings);
    }
}

// 6. Foreign keys creadas
echo "\n[6/8] Foreign keys → tenants\n";
$fks = $pdo->query("
    SELECT TABLE_NAME, CONSTRAINT_NAME
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE REFERENCED_TABLE_SCHEMA = DATABASE()
      AND REFERENCED_TABLE_NAME = 'tenants'
      AND COLUMN_NAME = 'tenant_id'
")->fetchAll();
$fkTables = array_column($fks, 'TABLE_NAME');
foreach ($scoped as $t) {
    $hasFK = in_array($t, $fkTables, true);
    check("FK `{$t}.tenant_id` → tenants.id", $hasFK,
        $hasFK ? '' : 'falta FK', $failures, $warnings);
}

// 7. Test de aislamiento simulado: ningún data leak entre tenants
echo "\n[7/8] Simulación de aislamiento\n";
$tenantCount = (int) $pdo->query("SELECT COUNT(*) FROM tenants")->fetchColumn();
check("Tenants en BD", $tenantCount >= 1, "{$tenantCount} tenants", $failures, $warnings);

// Verificación cruzada: cada empresa debe pertenecer a un tenant válido
$invalidCompanies = (int) $pdo->query("
    SELECT COUNT(*) FROM companies c
    LEFT JOIN tenants t ON t.id = c.tenant_id
    WHERE t.id IS NULL
")->fetchColumn();
check("Empresas con tenant_id válido", $invalidCompanies === 0,
    $invalidCompanies > 0 ? "{$invalidCompanies} empresas con tenant inexistente" : '',
    $failures, $warnings);

// Verificación: workers indirectamente bajo un tenant
$workersUnderTenant = (int) $pdo->query("
    SELECT COUNT(DISTINCT w.id)
    FROM workers w
    JOIN battery_services bs ON bs.id = w.battery_service_id
    WHERE bs.tenant_id IS NOT NULL
")->fetchColumn();
$totalWorkers = (int) $pdo->query("SELECT COUNT(*) FROM workers")->fetchColumn();
check("Trabajadores conectados a un tenant vía battery_service",
    $workersUnderTenant === $totalWorkers,
    "{$workersUnderTenant} de {$totalWorkers}", $failures, $warnings,
    $workersUnderTenant !== $totalWorkers);

// 8. Sanidad de configuración Wompi
echo "\n[8/8] Configuración de cobros\n";
$tenants = $pdo->query("SELECT id, plan, monthly_fee_cop, credits_included_monthly FROM tenants")->fetchAll();
foreach ($tenants as $t) {
    if ($t['plan'] === 'custom') continue;
    $hasFee = (int) $t['monthly_fee_cop'] > 0;
    $hasCredits = (int) $t['credits_included_monthly'] > 0 || $t['id'] === 1; // tenant 1 puede tener 99999 (custom infinito)
    check("Tenant {$t['id']} ({$t['plan']}) tiene tarifa configurada",
        $hasFee || $t['id'] === 1, $hasFee ? '' : 'monthly_fee_cop=0', $failures, $warnings, true);
}

// =============================================================================
echo "\n=== RESUMEN ===\n";
if (empty($failures) && empty($warnings)) {
    echo "\033[32m✓ TODO EN ORDEN — listo para go-live\033[0m\n";
    exit(0);
}
if (!empty($failures)) {
    echo "\033[31m✗ " . count($failures) . " fallas críticas:\033[0m\n";
    foreach ($failures as $f) echo "  - {$f}\n";
}
if (!empty($warnings)) {
    echo "\033[33m⚠ " . count($warnings) . " advertencias:\033[0m\n";
    foreach ($warnings as $w) echo "  - {$w}\n";
}
exit(empty($failures) ? 0 : 1);
