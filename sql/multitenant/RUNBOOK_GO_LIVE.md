# RUNBOOK — Go-Live Multi-tenant psyrisk

> Procedimiento paso a paso para desplegar a producción el lunes 4 de mayo de 2026.
> **Sponsor:** ejecuta cada paso siguiendo el orden estricto.

---

## ⏰ Cronograma sugerido (sábado 2 — lunes 4)

| Día | Hora | Acción |
|---|---|---|
| Sábado 2 may | 10:00 | T-2: Backup de producción + revisión de archivos |
| Sábado 2 may | 11:00 | T-2: Subir archivos al servidor (no activar todavía) |
| Domingo 3 may | 10:00 | T-1: Iniciar trámite Wompi producción + ajustar `.env` |
| Domingo 3 may | 18:00 | T-1: Ejecutar migraciones SQL en producción |
| Domingo 3 may | 19:00 | T-1: Verificar con `verify_multitenant.php` |
| Domingo 3 may | 20:00 | T-1: Pruebas de humo con cuentas reales |
| Lunes 4 may | 08:00 | T-0: GO LIVE — anuncio público |

---

## PASO 0 — Pre-flight (sábado 2 may, 10:00)

```bash
# 0.1 Backup completo de la BD
mysqldump --host=db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com \
          --port=25060 \
          --user=cycloid_userdb \
          -p \
          --ssl-mode=REQUIRED \
          --single-transaction \
          --routines \
          --triggers \
          psyrisk > backup_pre_multitenant_2026-05-02.sql

# 0.2 Verificar tamaño del backup (debe ser >5MB)
ls -lh backup_pre_multitenant_2026-05-02.sql

# 0.3 Comprimir y guardar en lugar seguro
gzip backup_pre_multitenant_2026-05-02.sql
# Subir a Drive personal o S3 — fuera del servidor de producción
```

> ⚠️ **Sin backup verificado, NO continuar.**

---

## PASO 1 — Subir archivos al servidor (sábado 2 may)

Subir vía aapanel / FTP / git pull los siguientes archivos NUEVOS (los modificados también):

### Migraciones SQL
```
sql/multitenant/
├── README.md
├── RUNBOOK_GO_LIVE.md            ← este archivo
├── AUDIT_RIESGOS_CONOCIDOS.md
├── 001_create_tenants_table.sql
├── 002_seed_cycloid_tenant.sql
├── 003_add_tenant_id_to_tables.sql
├── 004_backfill_tenant_id.sql
├── 005_make_tenant_id_not_null.sql
├── 006_create_tenant_signups_table.sql
├── 007_create_billing_tables.sql
└── 008_add_consent_audit_columns.sql
```

### Código nuevo
```
app/Controllers/
├── LandingController.php
├── OnboardingController.php
├── SignupController.php
├── SubscriptionController.php
└── TenantController.php

app/Filters/
├── AuthFilter.php
├── TenantCreditFilter.php
└── TenantFilter.php

app/Helpers/
└── tenant_helper.php

app/Models/
├── BillingTransactionModel.php
├── CreditMovementModel.php
├── TenantModel.php
└── TenantSignupModel.php

app/Services/
├── CreditConsumptionService.php
└── WompiService.php

app/Traits/
└── TenantScopedTrait.php

app/Views/
├── emails/tenant_verification.php
├── landing/ (4 archivos)
├── onboarding/ (3 archivos)
├── signup/ (4 archivos)
├── subscription/ (2 archivos)
└── tenants/ (3 archivos)

scripts/verify_multitenant.php

legal/ (4 documentos)
```

### Modificados (sobrescribir)
```
app/Config/Filters.php
app/Config/Routes.php
app/Controllers/AuthController.php
app/Controllers/BaseController.php
app/Controllers/WorkerController.php
app/Models/UserModel.php
app/Models/CompanyModel.php
app/Models/BatteryServiceModel.php
app/Models/ConsultantModel.php
app/Models/ReportModel.php
app/Models/ActionPlanModel.php
app/Models/CsvImportModel.php
app/Models/IndividualResultRequestModel.php
app/Views/layouts/main.php
app/Views/layouts/report.php
```

> Los archivos están listos en local. Sincronizarlos vía git push + git pull en producción, o aapanel.

---

## PASO 2 — Configurar `.env` en producción (domingo 3 may)

Editar `/path/to/psyrisk/env` (en aapanel) y agregar al final:

```ini
# ============================================
# WOMPI (pasarela de pagos)
# ============================================
wompi.environment    = sandbox    # cambiar a 'production' tras aprobación Wompi
wompi.publicKey      = pub_test_xxxxxxxxxxxxxxxxxxxx
wompi.privateKey     = prv_test_xxxxxxxxxxxxxxxxxxxx
wompi.eventsKey      = test_events_xxxxxxxxxxxxxxxxxxxx
wompi.integritySecret = test_integrity_xxxxxxxxxxxxxxxxxxxx

# ============================================
# Tenant raíz (Cycloid) — usado solo en el seed inicial
# ============================================
tenant.rootSlug       = cycloid
```

> Las llaves sandbox se obtienen creando cuenta gratis en https://comercios.wompi.co/.
> Las de producción llegan después de enviar documentación legal (24-72h hábiles).

---

## PASO 3 — Ejecutar migraciones (domingo 3 may, 18:00)

**Conectarse al servidor de BD:**
```bash
mysql --host=db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com \
      --port=25060 \
      --user=cycloid_userdb \
      -p \
      --ssl-mode=REQUIRED \
      psyrisk
```

**Ejecutar EN ORDEN ESTRICTO:**
```sql
SOURCE sql/multitenant/001_create_tenants_table.sql;
SOURCE sql/multitenant/002_seed_cycloid_tenant.sql;
SOURCE sql/multitenant/003_add_tenant_id_to_tables.sql;
SOURCE sql/multitenant/004_backfill_tenant_id.sql;
SOURCE sql/multitenant/005_make_tenant_id_not_null.sql;
SOURCE sql/multitenant/006_create_tenant_signups_table.sql;
SOURCE sql/multitenant/007_create_billing_tables.sql;
SOURCE sql/multitenant/008_add_consent_audit_columns.sql;
```

> **Tiempo total:** ~30 segundos sobre la BD actual.

---

## PASO 4 — Verificación automática (domingo 3 may, 19:00)

```bash
DB_HOST=db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com \
DB_PORT=25060 \
DB_NAME=psyrisk \
DB_USER=cycloid_userdb \
DB_PASS=AVNS_xxxx \
php scripts/verify_multitenant.php
```

**Salida esperada:** todos los chequeos en verde, exit 0.

Si hay fallas:
- **Crítica:** rollback inmediato (ver PASO 9).
- **Warning:** evaluar y decidir.

---

## PASO 5 — Pruebas de humo manuales (domingo 3 may, 20:00)

Hacer estas pruebas con tu navegador en orden:

| # | Acción | Resultado esperado |
|---|---|---|
| 5.1 | Visitar `https://psyrisk.com.co/` | Landing comercial nueva, NO redirección a login |
| 5.2 | Visitar `/login`, login como `eleyson.segura@cycloidtalent.com` | Entra al dashboard. Sidebar muestra logo Cycloid existente. Footer dice "© Cycloid Talent SAS" |
| 5.3 | Ir a `/companies` | Lista solo empresas del tenant Cycloid (las 15 actuales). No hay datos extra. |
| 5.4 | Ir a `/battery-services` | 11 servicios actuales visibles |
| 5.5 | Login como SUPER ADMIN (`head.consultant.cycloidtalent@gmail.com`) | Entra al dashboard. Tiene acceso a `/tenants` (gestor de tenants) |
| 5.6 | Ir a `/tenants` | Ve fila única: Cycloid Talent SAS con plan empresarial, status active |
| 5.7 | `/signup` | Form completo de registro con 3 planes |
| 5.8 | Llenar form con email de prueba `test@example.com` | Recibe email de SendGrid (revisar bandeja) |
| 5.9 | Click en enlace del email | Aterriza en `/onboarding/welcome`, login automático |
| 5.10 | Ir a `/onboarding/branding` | Form de logo, colores, etc. |
| 5.11 | Subir un logo placeholder + colores → guardar | Redirige a `/onboarding/finish` |
| 5.12 | Ir a `/dashboard` | Sidebar AHORA muestra el logo recién subido (white-label efectivo) |
| 5.13 | Ir a `/companies` siendo este nuevo tenant | **Lista VACÍA** (NO muestra datos de Cycloid). ✅ Aislamiento OK. |
| 5.14 | Ir a `/subscription` | Muestra plan inicial, 10 créditos, botón "Activar suscripción" |
| 5.15 | Click "Activar suscripción" | Redirige a Wompi sandbox checkout. ✅ |

---

## PASO 6 — Limpiar cuenta de prueba (lunes 4 may, 07:30)

Si el tenant de prueba quedó en producción, cancelarlo o suspenderlo:
```sql
UPDATE tenants SET status = 'cancelled', deleted_at = NOW() WHERE id > 1 AND contact_email LIKE '%test%';
```

---

## PASO 7 — Anuncio público (lunes 4 may, 08:00)

- Publicar en redes sociales / LinkedIn / Instagram.
- Compartir el link `https://psyrisk.com.co/` con prospectos identificados.
- Activar Google Ads / Facebook Ads (si aplica).

---

## PASO 8 — Monitoreo primera semana

| Métrica | Dónde mirarla | Acción si dispara |
|---|---|---|
| Errores 500 | logs de aapanel + writable/logs/ | Review inmediato |
| Pagos fallidos Wompi | Dashboard Wompi + tabla `billing_transactions` | Notificar al cliente |
| Signups no verificados | `SELECT * FROM tenant_signups WHERE status='pending'` | Email recordatorio a 12h |
| Tenants en trial vencido | `SELECT * FROM tenants WHERE status='trial' AND trial_ends_at < CURDATE()` | Email cobro |

---

## PASO 9 — Rollback (solo si hay falla crítica)

> **Antes de rollback, evaluar si fix-forward es más rápido.**

```bash
# 1. Restaurar BD desde backup
gunzip backup_pre_multitenant_2026-05-02.sql.gz
mysql ... psyrisk < backup_pre_multitenant_2026-05-02.sql

# 2. Restaurar código (git revert al commit anterior)
git checkout <commit-pre-multitenant>
```

Si solo falla algo puntual (ej. un controller), considerar:
- Comentar la línea problemática
- Re-deploy del archivo arreglado
- No hacer rollback completo a menos que la BD esté corrupta

---

## Contactos de emergencia

- **Sponsor (decisor):** `head.consultant.cycloidtalent@gmail.com`
- **Soporte aapanel:** según tu plan de hosting
- **Soporte Wompi:** `01 8000 51 00 10` o `comercios@wompi.co`
- **Soporte SendGrid:** dashboard.sendgrid.com → Support
- **Soporte DigitalOcean:** support.digitalocean.com

---

## Checklist final (firma del sponsor)

- [ ] Backup de BD verificado y guardado fuera del servidor
- [ ] Archivos sincronizados a producción
- [ ] `.env` actualizado con llaves Wompi sandbox
- [ ] Migraciones 001-008 ejecutadas en orden
- [ ] `verify_multitenant.php` salió con código 0
- [ ] 15 pruebas de humo pasaron
- [ ] Cuenta de prueba limpiada
- [ ] Sponsor firma este checklist y autoriza go-live

**Firma sponsor: __________________________ Fecha: ____________**
