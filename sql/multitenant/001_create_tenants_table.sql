-- =============================================================================
-- 001_create_tenants_table.sql
-- Crea la tabla `tenants` que representa a cada psicólogo/consultora afiliada
-- a la plataforma psyrisk en modo white-label.
-- =============================================================================
-- IDEMPOTENTE: usa CREATE TABLE IF NOT EXISTS.
-- Tiempo estimado: < 1 segundo.
-- =============================================================================

CREATE TABLE IF NOT EXISTS `tenants` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,

    -- Identificación
    `slug` VARCHAR(64) NOT NULL COMMENT 'URL-friendly: cycloid, psicologo-pepe',
    `legal_name` VARCHAR(255) NOT NULL COMMENT 'Razon social legal',
    `trade_name` VARCHAR(255) NOT NULL COMMENT 'Nombre comercial visible',
    `nit` VARCHAR(20) NOT NULL,

    -- Contacto del tenant (psicólogo titular)
    `contact_name` VARCHAR(255) NOT NULL,
    `contact_email` VARCHAR(255) NOT NULL,
    `contact_phone` VARCHAR(20) NULL,
    `address` TEXT NULL,
    `city` VARCHAR(100) NULL,
    `country` VARCHAR(64) NOT NULL DEFAULT 'Colombia',

    -- Branding white-label
    `logo_path` VARCHAR(500) NULL COMMENT 'Logo del tenant para portal y PDFs',
    `brand_primary_color` VARCHAR(7) NOT NULL DEFAULT '#0066CC',
    `brand_secondary_color` VARCHAR(7) NOT NULL DEFAULT '#003366',
    `email_from_name` VARCHAR(255) NULL COMMENT 'Sender name del tenant; fallback psyrisk',
    `email_from_address` VARCHAR(255) NULL COMMENT 'Sender email; fallback psyrisk',
    `pdf_footer_text` TEXT NULL,
    `website_url` VARCHAR(255) NULL,
    `linkedin_url` VARCHAR(255) NULL,

    -- Plan + Suscripción (Modelo C: híbrido)
    `plan` ENUM('inicial','profesional','empresarial','custom') NOT NULL DEFAULT 'inicial',
    `status` ENUM('trial','active','suspended','cancelled') NOT NULL DEFAULT 'trial',
    `trial_ends_at` DATE NULL,
    `current_period_start` DATE NULL,
    `current_period_end` DATE NULL,

    -- Créditos (1 crédito = 1 evaluación de 1 trabajador)
    `credits_balance` INT NOT NULL DEFAULT 0 COMMENT 'Disponibles ahora',
    `credits_included_monthly` INT NOT NULL DEFAULT 0 COMMENT 'Renovacion mensual del plan',
    `credits_used_lifetime` INT NOT NULL DEFAULT 0,

    -- Datos de cobro (Wompi)
    `wompi_customer_id` VARCHAR(64) NULL,
    `monthly_fee_cop` INT NOT NULL DEFAULT 0 COMMENT 'Tarifa mensual en pesos',
    `extra_credit_price_cop` INT NOT NULL DEFAULT 0 COMMENT 'Precio crédito adicional',

    -- Auditoría
    `created_by` INT UNSIGNED NULL COMMENT 'user_id del platform admin que creó el tenant',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` DATETIME NULL,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_tenants_slug` (`slug`),
    UNIQUE KEY `uniq_tenants_nit` (`nit`),
    KEY `idx_tenants_status` (`status`),
    KEY `idx_tenants_plan` (`plan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- DOWN (rollback manual):
-- DROP TABLE IF EXISTS `tenants`;
-- =============================================================================
