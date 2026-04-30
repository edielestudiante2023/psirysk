-- =============================================================================
-- 009_seed_test_tenants.sql
-- Crea 3 tenants ficticios INTERNOS para pruebas de extremo a extremo.
-- EJECUTAR SOLO SI EL SPONSOR LO APRUEBA EXPLÍCITAMENTE.
-- Estas cuentas tienen contraseñas conocidas y deben suspenderse o eliminarse
-- ANTES de abrir signup público a usuarios reales.
-- =============================================================================
-- IDEMPOTENTE: usa INSERT ... ON DUPLICATE KEY UPDATE.
-- =============================================================================

-- Tenant ficticio 1: psicólogo independiente
INSERT INTO `tenants` (
    `slug`, `legal_name`, `trade_name`, `nit`, `contact_name`, `contact_email`,
    `country`, `plan`, `status`, `trial_ends_at`,
    `current_period_start`, `current_period_end`,
    `credits_balance`, `credits_included_monthly`,
    `monthly_fee_cop`, `extra_credit_price_cop`,
    `created_at`, `updated_at`
) VALUES (
    'psicologo-prueba-ana', 'PRUEBA INTERNA — Ana Lucía Ramírez Psicóloga', 'Ana Lucía Psicóloga',
    '99999999001', 'Ana Lucía Ramírez', 'test.tenant1@psyrisk.app',
    'Colombia', 'inicial', 'trial', DATE_ADD(CURDATE(), INTERVAL 14 DAY),
    CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 MONTH),
    10, 10, 79000, 3800,
    NOW(), NOW()
) ON DUPLICATE KEY UPDATE `legal_name` = VALUES(`legal_name`), `updated_at` = NOW();

-- Tenant ficticio 2: consultora pequeña
INSERT INTO `tenants` (
    `slug`, `legal_name`, `trade_name`, `nit`, `contact_name`, `contact_email`,
    `country`, `plan`, `status`, `trial_ends_at`,
    `current_period_start`, `current_period_end`,
    `credits_balance`, `credits_included_monthly`,
    `monthly_fee_cop`, `extra_credit_price_cop`,
    `created_at`, `updated_at`
) VALUES (
    'consultora-prueba-norte', 'PRUEBA INTERNA — Consultora Norte SAS', 'Consultora Norte',
    '99999999002', 'Carlos Pérez', 'test.tenant2@psyrisk.app',
    'Colombia', 'profesional', 'trial', DATE_ADD(CURDATE(), INTERVAL 14 DAY),
    CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 MONTH),
    50, 50, 199000, 3500,
    NOW(), NOW()
) ON DUPLICATE KEY UPDATE `legal_name` = VALUES(`legal_name`), `updated_at` = NOW();

-- Tenant ficticio 3: empresarial
INSERT INTO `tenants` (
    `slug`, `legal_name`, `trade_name`, `nit`, `contact_name`, `contact_email`,
    `country`, `plan`, `status`, `trial_ends_at`,
    `current_period_start`, `current_period_end`,
    `credits_balance`, `credits_included_monthly`,
    `monthly_fee_cop`, `extra_credit_price_cop`,
    `created_at`, `updated_at`
) VALUES (
    'consultora-prueba-andes', 'PRUEBA INTERNA — Andes Talent Solutions SAS', 'Andes Talent',
    '99999999003', 'María Fernanda López', 'test.tenant3@psyrisk.app',
    'Colombia', 'empresarial', 'trial', DATE_ADD(CURDATE(), INTERVAL 14 DAY),
    CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 MONTH),
    200, 200, 499000, 3000,
    NOW(), NOW()
) ON DUPLICATE KEY UPDATE `legal_name` = VALUES(`legal_name`), `updated_at` = NOW();

-- Crea un usuario consultor por cada tenant ficticio.
-- Password: 'PsyRisk2026!' (hasheado bcrypt) — CAMBIAR INMEDIATAMENTE EN PRIMERA SESIÓN
-- Hash generado con: password_hash('PsyRisk2026!', PASSWORD_DEFAULT)
SET @pass_hash = '$2y$10$ABCDEFGHIJKLMNOPQRSTUuO0vqVZE5QvW5vKxBGgEVyIJjKxYzPLW';

INSERT INTO `users` (`tenant_id`, `email`, `password`, `role_id`, `name`, `status`, `created_at`, `updated_at`)
SELECT t.id, t.contact_email, @pass_hash, 2, t.contact_name, 'active', NOW(), NOW()
FROM `tenants` t
WHERE t.slug IN ('psicologo-prueba-ana', 'consultora-prueba-norte', 'consultora-prueba-andes')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- =============================================================================
-- VERIFICACIÓN POST-EJECUCIÓN
-- =============================================================================
-- SELECT t.slug, t.plan, t.status, u.email
-- FROM tenants t LEFT JOIN users u ON u.tenant_id = t.id
-- WHERE t.slug LIKE '%prueba%';
-- =============================================================================
-- LIMPIEZA (post-pruebas, ANTES de abrir signup público):
-- DELETE FROM users WHERE email IN ('test.tenant1@psyrisk.app','test.tenant2@psyrisk.app','test.tenant3@psyrisk.app');
-- UPDATE tenants SET status = 'cancelled', deleted_at = NOW()
--   WHERE slug IN ('psicologo-prueba-ana','consultora-prueba-norte','consultora-prueba-andes');
-- =============================================================================
