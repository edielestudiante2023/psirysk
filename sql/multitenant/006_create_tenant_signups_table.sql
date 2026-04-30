-- =============================================================================
-- 006_create_tenant_signups_table.sql
-- Tabla intermedia para gestionar el flujo de auto-registro de psicólogos:
-- 1. Psicólogo llena formulario público de signup → fila en tenant_signups
-- 2. Recibe email con token de verificación
-- 3. Hace click → tenant real se crea, fila marcada como completada
-- =============================================================================
-- IDEMPOTENTE.
-- Tiempo estimado: < 1 segundo.
-- =============================================================================

CREATE TABLE IF NOT EXISTS `tenant_signups` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,

    -- Datos capturados en el formulario
    `legal_name` VARCHAR(255) NOT NULL,
    `trade_name` VARCHAR(255) NULL,
    `nit` VARCHAR(20) NOT NULL,
    `contact_name` VARCHAR(255) NOT NULL,
    `contact_email` VARCHAR(255) NOT NULL,
    `contact_phone` VARCHAR(20) NULL,
    `password_hash` VARCHAR(255) NOT NULL COMMENT 'pre-hasheado al momento de signup',
    `plan` ENUM('inicial','profesional','empresarial') NOT NULL DEFAULT 'inicial',

    -- Token de verificación
    `verification_token` VARCHAR(64) NOT NULL,
    `verification_expires_at` DATETIME NOT NULL,

    -- Estado
    `status` ENUM('pending','verified','expired','cancelled') NOT NULL DEFAULT 'pending',
    `verified_at` DATETIME NULL,
    `verified_ip` VARCHAR(45) NULL,
    `tenant_id` INT UNSIGNED NULL COMMENT 'ID del tenant creado al verificar',

    -- Anti-abuso
    `signup_ip` VARCHAR(45) NOT NULL,
    `signup_user_agent` VARCHAR(500) NULL,

    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_signups_token` (`verification_token`),
    KEY `idx_signups_email` (`contact_email`),
    KEY `idx_signups_nit` (`nit`),
    KEY `idx_signups_status` (`status`),
    KEY `idx_signups_expires` (`verification_expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- DOWN:
-- DROP TABLE IF EXISTS `tenant_signups`;
-- =============================================================================
