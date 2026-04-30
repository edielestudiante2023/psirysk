-- =============================================================================
-- 007_create_billing_tables.sql
-- Sistema de cobro: transacciones Wompi + movimientos de créditos
-- =============================================================================

-- Transacciones de cobro (cada cargo Wompi exitoso o fallido queda registrado)
CREATE TABLE IF NOT EXISTS `billing_transactions` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id` INT UNSIGNED NOT NULL,
    `transaction_type` ENUM('subscription','credit_pack','adjustment') NOT NULL,
    `amount_cop` INT NOT NULL COMMENT 'Monto en pesos colombianos',
    `currency` VARCHAR(3) NOT NULL DEFAULT 'COP',
    `wompi_transaction_id` VARCHAR(64) NULL,
    `wompi_reference` VARCHAR(64) NULL COMMENT 'Referencia única que enviamos a Wompi',
    `wompi_status` ENUM('PENDING','APPROVED','DECLINED','VOIDED','ERROR') NULL,
    `payment_method` VARCHAR(32) NULL COMMENT 'CARD, NEQUI, PSE, BANCOLOMBIA_TRANSFER',
    `description` VARCHAR(255) NULL,
    `metadata` JSON NULL,
    `paid_at` DATETIME NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_billing_reference` (`wompi_reference`),
    KEY `idx_billing_tenant` (`tenant_id`),
    KEY `idx_billing_status` (`wompi_status`),
    KEY `idx_billing_paid` (`paid_at`),
    CONSTRAINT `fk_billing_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ledger de créditos: cada vez que un tenant gana o consume créditos queda registro
CREATE TABLE IF NOT EXISTS `credit_movements` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id` INT UNSIGNED NOT NULL,
    `movement_type` ENUM('grant','consume','refund','adjustment','expiration') NOT NULL,
    `amount` INT NOT NULL COMMENT 'Positivo = ingreso de créditos; Negativo = consumo',
    `balance_after` INT NOT NULL COMMENT 'Saldo del tenant tras el movimiento',
    `source` VARCHAR(64) NOT NULL COMMENT 'monthly_refill, evaluation_consumed, credit_pack, manual',
    `reference_type` VARCHAR(64) NULL COMMENT 'Ej: battery_service, billing_transaction',
    `reference_id` INT UNSIGNED NULL,
    `notes` VARCHAR(500) NULL,
    `created_by` INT UNSIGNED NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_credit_tenant` (`tenant_id`),
    KEY `idx_credit_type` (`movement_type`),
    KEY `idx_credit_created` (`created_at`),
    CONSTRAINT `fk_credit_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- DOWN:
-- DROP TABLE IF EXISTS `credit_movements`;
-- DROP TABLE IF EXISTS `billing_transactions`;
-- =============================================================================
