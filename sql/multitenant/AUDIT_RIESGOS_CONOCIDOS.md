# Auditoría de Riesgos Conocidos — Multi-tenant psyrisk

> Hallazgos de la auditoría de aislamiento del Día 7.
> Versión 1.0 — 2026-04-25

---

## Resumen ejecutivo

Tras los Días 1-6, el aislamiento de tenants está **garantizado** para el flujo principal:
- Login setea `tenant_id` en sesión.
- Filter `auth` + `tenant` bloquea rutas protegidas.
- 8 modelos clave aplican `WHERE tenant_id` automáticamente vía `TenantScopedTrait`.
- Vistas usan helpers white-label con fallback seguro.

Sin embargo, hay **6 riesgos residuales** identificados que requieren atención post-go-live (Fase 1.5).

---

## Riesgo 1 — Queries directas con `$db->table()` saltando el modelo

**Archivos afectados:**
- `app/Controllers/BatteryServiceController.php`
- `app/Controllers/WorkerController.php`
- `app/Controllers/CsvImportController.php`
- `app/Controllers/ReportsController.php`
- `app/Controllers/PasswordResetController.php` (irrelevante — tabla password_resets es por email)

**Severidad:** 🟡 Media — el `tenant_id` no se inyecta automáticamente; depende de filtros manuales por `battery_service_id` que el código actual ya hace.

**Mitigación actual:** las queries usan `where('battery_service_id', $serviceId)` y antes consultan el servicio vía el modelo (que SÍ está scoped). Si el servicio no es del tenant, `$service` es null → redirect.

**Mitigación recomendada (Fase 1.5):** auditar las ~20 ocurrencias de `$db->table()` en estos controladores y reemplazar por modelos siempre que sea posible. Donde sea inevitable, agregar `where('tenant_id', session()->get('tenant_id'))` explícito.

---

## Riesgo 2 — Sesión usa `role` en algunos sitios y `role_name` en otros

**Archivo:** `app/Controllers/WorkerController.php:1449`

```php
if (session()->get('role') !== 'consultor')  // ← bug: la clave es 'role_name'
```

**Severidad:** 🟢 Baja — este chequeo siempre falla → siempre redirige a dashboard. Funcionalidad de cerrar servicio no bloqueada en práctica porque el flujo llega vía botón solo accesible a consultores.

**Mitigación:** corregir a `role_name`. Bug pre-existente al multi-tenant — fuera del alcance del refactor actual.

---

## Riesgo 3 — Tabla `workers` no tiene `tenant_id` directo

**Diseño intencional.** Workers heredan tenant vía `battery_service_id → battery_services.tenant_id`.

**Severidad:** 🟢 Baja — el modelo `BatteryServiceModel` ya está scoped, por lo que cargar un servicio de otro tenant devuelve null y los queries a workers fallan en la cadena.

**Riesgo potencial:** si en un futuro alguien hace `WorkerModel::findAll()` directamente sin filtrar por servicio, devolvería **TODOS** los trabajadores del sistema. Hoy no se hace en producción.

**Mitigación recomendada:** agregar `tenant_id` a `workers` también (Fase 1.5) y aplicar el trait. Costo: 1 migración + actualizar el modelo. Beneficio: defensa en profundidad.

---

## Riesgo 4 — `Tabla company_users` está vacía y no se usa

**Hallazgo:** En la BD de producción, `company_users` tiene 0 filas. La relación user-company se hace via `users.company_id` directo.

**Severidad:** 🟢 Baja — código no la consulta.

**Mitigación:** considerar dropearla en Fase 2 si sigue sin uso. Por ahora, los modelos no la tocan, no hay riesgo.

---

## Riesgo 5 — Webhook de Wompi confía en firma SHA-256 sin lista de IPs

**Archivo:** `app/Controllers/SubscriptionController.php::webhook()`

**Severidad:** 🟢 Baja — la firma HMAC con `wompi.eventsKey` ya garantiza autenticidad. Solo Wompi tiene la clave secreta.

**Mitigación recomendada (defense in depth):** si Wompi publica IPs estáticas de origen, agregarlas a una whitelist. Hoy Wompi no garantiza IPs estáticas.

---

## Riesgo 6 — Reset de créditos al renovar suscripción puede sobreescribir saldo comprado

**Comportamiento actual:** al confirmar el pago de una suscripción mensual, `grantCreditsForTransaction()` SUMA los créditos incluidos al `credits_balance` actual (no resetea).

**Antes de cambiar:** verificar que sea el comportamiento de negocio deseado. Algunos SaaS resetean al inicio del mes (los créditos no usados se pierden). Otros suman.

**Decisión actual:** **suman** (más amigable al cliente). Documentado como decisión, no bug.

---

## Checklist de cierre para sponsor

- [ ] Aceptar los 6 riesgos arriba como Fase 1.5 (post go-live).
- [ ] Confirmar que riesgo 6 (suma vs reset) es la política deseada.
- [ ] Programar revisión técnica a 30 días del go-live.
- [ ] Asignar persona para auditar logs de webhooks Wompi en primera semana.
