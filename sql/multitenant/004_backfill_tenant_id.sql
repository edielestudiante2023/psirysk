-- =============================================================================
-- 004_backfill_tenant_id.sql
-- Asigna tenant_id = 1 (Cycloid) a TODOS los registros existentes en producción.
-- Esto es seguro porque hoy todo lo que opera psyrisk pertenece a Cycloid Talent.
-- =============================================================================
-- IDEMPOTENTE: solo afecta filas con tenant_id IS NULL.
-- Tiempo estimado: ~10 segundos sobre la BD actual (15 cias, 18 users, etc).
-- =============================================================================

-- Empresas (15 filas): todas pertenecen a Cycloid
UPDATE `companies` SET `tenant_id` = 1 WHERE `tenant_id` IS NULL;

-- Usuarios (18 filas): TODOS van al tenant Cycloid EXCEPTO el SUPER ADMIN (id=1)
-- que es admin de plataforma global y queda fuera de cualquier tenant.
UPDATE `users` SET `tenant_id` = 1 WHERE `tenant_id` IS NULL AND `id` <> 1;
-- Verificación: el SUPER ADMIN debe seguir con tenant_id=NULL (es correcto).

-- Servicios de batería (11 filas)
UPDATE `battery_services` SET `tenant_id` = 1 WHERE `tenant_id` IS NULL;

-- Consultores (1 fila: Edison Cuervo)
UPDATE `consultants` SET `tenant_id` = 1 WHERE `tenant_id` IS NULL;

-- Reportes (7 filas)
UPDATE `reports` SET `tenant_id` = 1 WHERE `tenant_id` IS NULL;

-- Planes de acción (27 filas)
UPDATE `action_plans` SET `tenant_id` = 1 WHERE `tenant_id` IS NULL;

-- Importaciones CSV (4 filas)
UPDATE `csv_imports` SET `tenant_id` = 1 WHERE `tenant_id` IS NULL;

-- Solicitudes de resultados individuales (5 filas)
UPDATE `individual_results_requests` SET `tenant_id` = 1 WHERE `tenant_id` IS NULL;

-- =============================================================================
-- VERIFICACIÓN POST-EJECUCIÓN
-- =============================================================================
-- Después de correr este script, verificar manualmente:
--   SELECT 'companies' AS t, COUNT(*) AS huerfanos FROM companies WHERE tenant_id IS NULL
--   UNION ALL SELECT 'battery_services', COUNT(*) FROM battery_services WHERE tenant_id IS NULL
--   UNION ALL SELECT 'consultants', COUNT(*) FROM consultants WHERE tenant_id IS NULL
--   UNION ALL SELECT 'reports', COUNT(*) FROM reports WHERE tenant_id IS NULL
--   UNION ALL SELECT 'action_plans', COUNT(*) FROM action_plans WHERE tenant_id IS NULL
--   UNION ALL SELECT 'csv_imports', COUNT(*) FROM csv_imports WHERE tenant_id IS NULL
--   UNION ALL SELECT 'individual_results_requests', COUNT(*) FROM individual_results_requests WHERE tenant_id IS NULL;
-- Debe devolver 0 en todas. (Para users debe haber 1 huérfano: el superadmin id=1.)
-- =============================================================================
-- DOWN (rollback manual):
-- UPDATE `companies` SET `tenant_id` = NULL WHERE `tenant_id` = 1;
-- UPDATE `users` SET `tenant_id` = NULL WHERE `tenant_id` = 1;
-- UPDATE `battery_services` SET `tenant_id` = NULL WHERE `tenant_id` = 1;
-- UPDATE `consultants` SET `tenant_id` = NULL WHERE `tenant_id` = 1;
-- UPDATE `reports` SET `tenant_id` = NULL WHERE `tenant_id` = 1;
-- UPDATE `action_plans` SET `tenant_id` = NULL WHERE `tenant_id` = 1;
-- UPDATE `csv_imports` SET `tenant_id` = NULL WHERE `tenant_id` = 1;
-- UPDATE `individual_results_requests` SET `tenant_id` = NULL WHERE `tenant_id` = 1;
-- =============================================================================
