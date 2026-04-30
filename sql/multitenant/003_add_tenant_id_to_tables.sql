-- =============================================================================
-- 003_add_tenant_id_to_tables.sql
-- Agrega columna `tenant_id` (NULLABLE de momento) a las tablas operativas
-- que requieren scoping por tenant.
-- =============================================================================
-- NO DESTRUCTIVO: solo agrega columnas, NULL permitido temporalmente.
-- 004 hace el backfill, 005 marca NOT NULL + crea FKs.
-- Tiempo estimado: ~5 segundos.
-- =============================================================================
-- Tablas afectadas (8):
--   companies, users, battery_services, consultants, reports,
--   action_plans, csv_imports, individual_results_requests
-- =============================================================================
-- Tablas que NO reciben tenant_id (heredan via FK):
--   workers (via battery_service_id), responses (via worker_id),
--   calculated_results, worker_demographics, max_risk_results,
--   validation_results, password_resets, battery_schedules, company_users
-- =============================================================================

ALTER TABLE `companies`
    ADD COLUMN `tenant_id` INT UNSIGNED NULL AFTER `id`;

ALTER TABLE `users`
    ADD COLUMN `tenant_id` INT UNSIGNED NULL AFTER `id`
    COMMENT 'NULL = platform admin (superadmin), no pertenece a ningún tenant';

ALTER TABLE `battery_services`
    ADD COLUMN `tenant_id` INT UNSIGNED NULL AFTER `id`;

ALTER TABLE `consultants`
    ADD COLUMN `tenant_id` INT UNSIGNED NULL AFTER `id`;

ALTER TABLE `reports`
    ADD COLUMN `tenant_id` INT UNSIGNED NULL AFTER `id`;

ALTER TABLE `action_plans`
    ADD COLUMN `tenant_id` INT UNSIGNED NULL AFTER `id`;

ALTER TABLE `csv_imports`
    ADD COLUMN `tenant_id` INT UNSIGNED NULL AFTER `id`;

ALTER TABLE `individual_results_requests`
    ADD COLUMN `tenant_id` INT UNSIGNED NULL AFTER `id`;

-- =============================================================================
-- DOWN (rollback manual):
-- ALTER TABLE `companies` DROP COLUMN `tenant_id`;
-- ALTER TABLE `users` DROP COLUMN `tenant_id`;
-- ALTER TABLE `battery_services` DROP COLUMN `tenant_id`;
-- ALTER TABLE `consultants` DROP COLUMN `tenant_id`;
-- ALTER TABLE `reports` DROP COLUMN `tenant_id`;
-- ALTER TABLE `action_plans` DROP COLUMN `tenant_id`;
-- ALTER TABLE `csv_imports` DROP COLUMN `tenant_id`;
-- ALTER TABLE `individual_results_requests` DROP COLUMN `tenant_id`;
-- =============================================================================
