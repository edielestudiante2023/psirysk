-- =============================================================================
-- 002_seed_cycloid_tenant.sql
-- Inserta CYCLOID TALENT SAS como tenant #1 (tenant raíz de la plataforma).
-- Datos extraídos de la BD de producción (companies.id=2, user.id=2 Eleyson).
-- =============================================================================
-- IDEMPOTENTE: usa INSERT ... ON DUPLICATE KEY UPDATE para no duplicar.
-- Tiempo estimado: < 1 segundo.
-- =============================================================================

INSERT INTO `tenants` (
    `id`,
    `slug`,
    `legal_name`,
    `trade_name`,
    `nit`,
    `contact_name`,
    `contact_email`,
    `contact_phone`,
    `address`,
    `country`,
    `logo_path`,
    `brand_primary_color`,
    `brand_secondary_color`,
    `email_from_name`,
    `email_from_address`,
    `website_url`,
    `linkedin_url`,
    `plan`,
    `status`,
    `current_period_start`,
    `current_period_end`,
    `credits_balance`,
    `credits_included_monthly`,
    `monthly_fee_cop`,
    `extra_credit_price_cop`,
    `created_by`,
    `created_at`,
    `updated_at`
) VALUES (
    1,
    'cycloid',
    'CYCLOID TALENT SAS',
    'Cycloid Talent',
    '901653912',
    'DIANA PATRICIA CUESTAS',
    'dianapatricuestas@hotmail.com',
    NULL,
    NULL,
    'Colombia',
    'uploads/logos/companies/company_1765570586_1765570586_f21160ea75f34d520a56.png',
    '#0066CC',
    '#003366',
    'Cycloid Talent',
    'head.consultant.cycloidtalent@gmail.com',
    'https://cycloidtalent.com/',
    NULL,
    'empresarial',
    'active',
    CURDATE(),
    DATE_ADD(CURDATE(), INTERVAL 1 MONTH),
    99999,
    99999,
    0,
    0,
    1,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE
    `legal_name` = VALUES(`legal_name`),
    `trade_name` = VALUES(`trade_name`),
    `contact_email` = VALUES(`contact_email`),
    `logo_path` = VALUES(`logo_path`),
    `updated_at` = NOW();

-- Cycloid es el "tenant fundador" — créditos infinitos (99999) y plan empresarial.
-- El usuario admin de plataforma (User id=1 SUPER ADMIN) sigue por fuera de tenants.

-- =============================================================================
-- DOWN (rollback manual):
-- DELETE FROM `tenants` WHERE id = 1;
-- =============================================================================
