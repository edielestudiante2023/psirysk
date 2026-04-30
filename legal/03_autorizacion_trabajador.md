# Autorización del Trabajador para Tratamiento de Datos Personales

> Documento que el trabajador firma electrónicamente al inicio de la evaluación.
> Versión 1.0 — 2026-04-25

---

## Versión técnica (lo que se almacena en BD)

Por cada trabajador que inicia una evaluación, en `worker_demographics` queda:

| Campo | Valor |
|---|---|
| `consent_given_at` | timestamp ISO 8601 (ej. `2026-05-04 09:14:33`) |
| `consent_text_hash` | SHA256 del texto exacto que el trabajador vio (evidencia auditable) |
| `consent_ip_address` | IP del cliente |
| `consent_user_agent` | navegador del cliente |
| `consent_tenant_id` | tenant del psicólogo que opera la evaluación (responsable) |

> Si los campos `consent_text_hash`, `consent_ip_address`, `consent_user_agent`, `consent_tenant_id` no existen, agregar en migración SQL `006_add_consent_audit_columns.sql` (Día 7).

---

## Texto que se muestra al trabajador (versión visible)

### Pantalla 1 — Identificación del responsable

**Hola, [Nombre del trabajador].**

Antes de comenzar la evaluación de Riesgo Psicosocial, queremos que sepas exactamente quién maneja tus datos:

| | |
|---|---|
| **Responsable del tratamiento** | {{tenant.legal_name}} |
| **NIT** | {{tenant.nit}} |
| **Domicilio** | {{tenant.address}} |
| **Email para ejercer tus derechos** | {{tenant.contact_email}} |

Esta evaluación se realiza en cumplimiento de la **Resolución 2764 de 2022** del Ministerio del Trabajo y se opera a través de la plataforma **psyrisk**.

[Continuar →]

### Pantalla 2 — Qué datos vamos a tratar

Vamos a recolectar y procesar:

✓ Tus **datos básicos** (nombre, documento, email, cargo, área).
✓ Tus **datos sociodemográficos** (edad, sexo, estado civil, nivel educativo, etc.).
✓ Tus **respuestas a los cuestionarios** de factores intralaborales, extralaborales y estrés.

> **⚠️ Importante: tus respuestas son DATOS SENSIBLES** relacionados con tu salud mental y emocional. Por eso la ley exige que autorices su tratamiento de manera **expresa, libre, informada y específica**.

[Continuar →]

### Pantalla 3 — Para qué los vamos a usar

✓ Generar reportes **agregados** para tu empleador (mínimo 4 personas por categoría — nadie te identificará individualmente).
✓ Diseñar planes de mejora del clima laboral.
✓ Cumplir la vigilancia epidemiológica obligatoria (Resolución 2764 / 2022).

✗ **NO se usan** para perfilamiento comercial, publicidad, decisiones automatizadas que te afecten jurídicamente, ni se venden a terceros.

[Continuar →]

### Pantalla 4 — Tus derechos

Como titular de los datos puedes en cualquier momento:

- **Conocer** qué datos tenemos sobre ti.
- **Actualizarlos** o **rectificarlos** si están desactualizados o incorrectos.
- **Pedir prueba** de esta autorización.
- **Saber** quién ha visto tus datos.
- **Quejarte** ante la Superintendencia de Industria y Comercio (SIC).
- **Revocar** esta autorización y/o pedir que **suprimamos** tus datos.

Para hacerlo, escribe a **{{tenant.contact_email}}** con el asunto "Habeas Data" y tu número de documento. Te responderemos en máximo 10 días hábiles (consultas) o 15 días hábiles (reclamos).

[Continuar →]

### Pantalla 5 — Confirmación

> **DECLARO que:**
>
> ☐ He leído y comprendido la información anterior.
> ☐ Acepto la [Política de Tratamiento de Datos]({{tenant.privacy_policy_url}}) de psyrisk.
> ☐ **Otorgo mi autorización LIBRE, EXPRESA, INFORMADA y ESPECÍFICA** para el tratamiento de mis datos personales —incluyendo los datos sensibles— conforme a la Ley 1581 de 2012 y la Resolución 2764 de 2022.
> ☐ Sé que esta autorización es **revocable** en cualquier momento.

**[ ❌ NO autorizo ]   [ ✓ Autorizo y comienzo la evaluación ]**

> Si NO autorizas, no podrás continuar con la evaluación digital. Tu empleador será notificado y podrá ofrecerte la versión presencial.

---

## Notas de implementación

1. **Hash del texto:** al cargarse la pantalla, calcular SHA256 del HTML renderizado (sin scripts ni datos dinámicos del trabajador, solo el texto base). Almacenar en `consent_text_hash` para evidencia auditable.

2. **Versionado:** si en el futuro se cambia el texto del aviso, debe quedar registro de **qué versión** vio cada trabajador. Solución: el archivo `legal/03_autorizacion_trabajador.md` se hashea y la versión vigente queda en `app/Config/Consent.php`.

3. **Casos de revocatoria:** si un trabajador revoca su autorización después de haber respondido:
   - Sus respuestas individuales se anonimizan (se reemplaza nombre/documento/email por hash).
   - Sus respuestas siguen contando en los agregados (porque ya están consolidadas y no se puede deshacer estadísticamente, pero quedan disociadas de su identidad).

4. **Menor de edad:** la batería 2010 no contempla menores de 18 años. Si llega un caso, bloquear la evaluación con un mensaje pidiendo autorización del representante legal.

---

## Pendientes para sponsor

- [ ] Validar redacción con asesor legal cuando sea posible.
- [ ] Confirmar protocolo cuando un trabajador NO autoriza (evaluación presencial alternativa).
- [ ] Crear migración `006_add_consent_audit_columns.sql` para campos de auditoría faltantes (Día 7).
