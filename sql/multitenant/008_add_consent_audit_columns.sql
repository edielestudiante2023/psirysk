-- =============================================================================
-- 008_add_consent_audit_columns.sql
-- Agrega columnas de auditoría del consentimiento al tratamiento de datos.
-- Ya existe `consent_given_at` en worker_demographics; añadimos el resto.
-- =============================================================================

ALTER TABLE `worker_demographics`
    ADD COLUMN `consent_text_hash` VARCHAR(64) NULL COMMENT 'SHA256 del aviso visto por el trabajador' AFTER `consent_accepted_at`,
    ADD COLUMN `consent_ip_address` VARCHAR(45) NULL AFTER `consent_text_hash`,
    ADD COLUMN `consent_user_agent` VARCHAR(500) NULL AFTER `consent_ip_address`,
    ADD COLUMN `consent_tenant_id` INT UNSIGNED NULL COMMENT 'Tenant del psicologo responsable' AFTER `consent_user_agent`;

-- Backfill: para los registros existentes que ya tienen consent_accepted_at,
-- inferimos el tenant desde la cadena worker -> battery_service -> tenant_id.
UPDATE `worker_demographics` wd
JOIN `workers` w ON w.id = wd.worker_id
JOIN `battery_services` bs ON bs.id = w.battery_service_id
SET wd.consent_tenant_id = bs.tenant_id
WHERE wd.consent_accepted_at IS NOT NULL AND wd.consent_tenant_id IS NULL;

-- =============================================================================
-- DOWN:
-- ALTER TABLE `worker_demographics`
--     DROP COLUMN `consent_text_hash`,
--     DROP COLUMN `consent_ip_address`,
--     DROP COLUMN `consent_user_agent`,
--     DROP COLUMN `consent_tenant_id`;
-- =============================================================================
