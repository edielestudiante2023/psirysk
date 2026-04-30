# Migraciones Multi-Tenant — psyrisk

Migraciones para convertir psyrisk en plataforma SaaS white-label multi-tenant.

## Cómo ejecutar (producción)

Ejecutar **EN ORDEN ESTRICTO** desde el panel de aapanel o por CLI:

```bash
mysql -u USER -p psyrisk < 001_create_tenants_table.sql
mysql -u USER -p psyrisk < 002_seed_cycloid_tenant.sql
mysql -u USER -p psyrisk < 003_add_tenant_id_to_tables.sql
mysql -u USER -p psyrisk < 004_backfill_tenant_id.sql
mysql -u USER -p psyrisk < 005_make_tenant_id_not_null.sql
mysql -u USER -p psyrisk < 006_create_tenant_signups_table.sql
mysql -u USER -p psyrisk < 007_create_billing_tables.sql
mysql -u USER -p psyrisk < 008_add_consent_audit_columns.sql
# OPCIONAL solo para pruebas internas, ANTES de abrir signup público:
# mysql -u USER -p psyrisk < 009_seed_test_tenants.sql
```

> ⚠️ **ANTES de ejecutar:** hacer backup completo (`mysqldump psyrisk > backup_pre_multitenant_YYYYMMDD.sql`).

## Inventario y orden

| # | Archivo | Propósito | Tiempo estimado | Reversible |
|---|---|---|---|---|
| 001 | `001_create_tenants_table.sql` | Crea tabla `tenants` (branding + plan + créditos) | < 1s | DROP TABLE |
| 002 | `002_seed_cycloid_tenant.sql` | Inserta Cycloid Talent SAS como tenant #1 | < 1s | DELETE WHERE id=1 |
| 003 | `003_add_tenant_id_to_tables.sql` | `tenant_id NULL` en 8 tablas operativas | ~5s | DROP COLUMN |
| 004 | `004_backfill_tenant_id.sql` | Asigna tenant_id=1 a todos los registros existentes | ~10s | UPDATE ... SET NULL |
| 005 | `005_make_tenant_id_not_null.sql` | NOT NULL + FKs + índices | ~5s | Ver -- DOWN dentro del archivo |
| 006 | `006_create_tenant_signups_table.sql` | Tabla `tenant_signups` para auto-registro con verificación email | < 1s | DROP TABLE |
| 007 | `007_create_billing_tables.sql` | Tablas `billing_transactions` (cargos Wompi) y `credit_movements` (ledger) | < 1s | DROP TABLE |
| 008 | `008_add_consent_audit_columns.sql` | Columnas auditables de consentimiento del trabajador (hash, IP, UA, tenant) | ~3s | ALTER DROP COLUMN |
| 009 | `009_seed_test_tenants.sql` | **OPCIONAL** — 3 tenants ficticios para QA. Suspender antes del go-live público | < 1s | UPDATE status='cancelled' |

## Estado de la BD al terminar

- 1 tenant: Cycloid Talent SAS (id=1)
- Todos los registros existentes (15 empresas, 18 usuarios, 11 servicios, 708 trabajadores, 77.674 respuestas) quedan asignados al tenant 1.
- Sistema preparado para que el código aplique aislamiento al filtrar por `tenant_id` en todas las queries.

## Rollback completo (si algo falla en producción)

Cada archivo `.sql` incluye una sección comentada `-- DOWN` con el script de reversa.
