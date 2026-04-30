-- =============================================================================
-- 005_make_tenant_id_not_null.sql
-- Endurece el aislamiento: hace tenant_id NOT NULL en las tablas que
-- requieren tenant obligatorio, agrega FKs e índices.
--
-- IMPORTANTE: ejecutar SOLO después de 004 (backfill verificado en cero huérfanos).
-- =============================================================================
-- Tiempo estimado: ~5 segundos.
-- =============================================================================

-- USERS: tenant_id sigue NULLABLE porque el SUPER ADMIN (id=1) es global.
-- Solo agregamos índice + FK con SET NULL.
ALTER TABLE `users`
    ADD CONSTRAINT `fk_users_tenant`
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`)
    ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `users`
    ADD INDEX `idx_users_tenant` (`tenant_id`);

-- COMPANIES: tenant_id obligatorio
ALTER TABLE `companies`
    MODIFY COLUMN `tenant_id` INT UNSIGNED NOT NULL,
    ADD CONSTRAINT `fk_companies_tenant`
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE,
    ADD INDEX `idx_companies_tenant` (`tenant_id`);

-- BATTERY_SERVICES: tenant_id obligatorio
ALTER TABLE `battery_services`
    MODIFY COLUMN `tenant_id` INT UNSIGNED NOT NULL,
    ADD CONSTRAINT `fk_battery_services_tenant`
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE,
    ADD INDEX `idx_battery_services_tenant` (`tenant_id`);

-- CONSULTANTS: tenant_id obligatorio
ALTER TABLE `consultants`
    MODIFY COLUMN `tenant_id` INT UNSIGNED NOT NULL,
    ADD CONSTRAINT `fk_consultants_tenant`
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE,
    ADD INDEX `idx_consultants_tenant` (`tenant_id`);

-- REPORTS: tenant_id obligatorio
ALTER TABLE `reports`
    MODIFY COLUMN `tenant_id` INT UNSIGNED NOT NULL,
    ADD CONSTRAINT `fk_reports_tenant`
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE,
    ADD INDEX `idx_reports_tenant` (`tenant_id`);

-- ACTION_PLANS: tenant_id obligatorio
ALTER TABLE `action_plans`
    MODIFY COLUMN `tenant_id` INT UNSIGNED NOT NULL,
    ADD CONSTRAINT `fk_action_plans_tenant`
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE,
    ADD INDEX `idx_action_plans_tenant` (`tenant_id`);

-- CSV_IMPORTS: tenant_id obligatorio
ALTER TABLE `csv_imports`
    MODIFY COLUMN `tenant_id` INT UNSIGNED NOT NULL,
    ADD CONSTRAINT `fk_csv_imports_tenant`
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE,
    ADD INDEX `idx_csv_imports_tenant` (`tenant_id`);

-- INDIVIDUAL_RESULTS_REQUESTS: tenant_id obligatorio
ALTER TABLE `individual_results_requests`
    MODIFY COLUMN `tenant_id` INT UNSIGNED NOT NULL,
    ADD CONSTRAINT `fk_individual_results_requests_tenant`
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE,
    ADD INDEX `idx_individual_results_requests_tenant` (`tenant_id`);

-- =============================================================================
-- DOWN (rollback manual):
-- ALTER TABLE `users` DROP FOREIGN KEY `fk_users_tenant`, DROP INDEX `idx_users_tenant`;
-- ALTER TABLE `companies` DROP FOREIGN KEY `fk_companies_tenant`, DROP INDEX `idx_companies_tenant`, MODIFY COLUMN `tenant_id` INT UNSIGNED NULL;
-- ALTER TABLE `battery_services` DROP FOREIGN KEY `fk_battery_services_tenant`, DROP INDEX `idx_battery_services_tenant`, MODIFY COLUMN `tenant_id` INT UNSIGNED NULL;
-- ALTER TABLE `consultants` DROP FOREIGN KEY `fk_consultants_tenant`, DROP INDEX `idx_consultants_tenant`, MODIFY COLUMN `tenant_id` INT UNSIGNED NULL;
-- ALTER TABLE `reports` DROP FOREIGN KEY `fk_reports_tenant`, DROP INDEX `idx_reports_tenant`, MODIFY COLUMN `tenant_id` INT UNSIGNED NULL;
-- ALTER TABLE `action_plans` DROP FOREIGN KEY `fk_action_plans_tenant`, DROP INDEX `idx_action_plans_tenant`, MODIFY COLUMN `tenant_id` INT UNSIGNED NULL;
-- ALTER TABLE `csv_imports` DROP FOREIGN KEY `fk_csv_imports_tenant`, DROP INDEX `idx_csv_imports_tenant`, MODIFY COLUMN `tenant_id` INT UNSIGNED NULL;
-- ALTER TABLE `individual_results_requests` DROP FOREIGN KEY `fk_individual_results_requests_tenant`, DROP INDEX `idx_individual_results_requests_tenant`, MODIFY COLUMN `tenant_id` INT UNSIGNED NULL;
-- =============================================================================
