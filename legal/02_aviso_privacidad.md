# Aviso de Privacidad — psyrisk

> Este aviso se muestra al trabajador **antes** de iniciar la evaluación de Batería de Riesgo Psicosocial.
> Versión 1.0 — 2026-04-25

---

## Versión corta (visible en pantalla, antes de aceptar)

> **Tus respuestas son confidenciales y están protegidas por la Ley 1581 de 2012.**
>
> - El **responsable** del tratamiento de tus datos es **{{tenant.legal_name}}** (NIT {{tenant.nit}}), prestando el servicio a través de la plataforma psyrisk.
> - Tus respuestas a los cuestionarios de **factores intralaborales, extralaborales y estrés** son **datos sensibles** (Art. 5 Ley 1581) y se tratan con tu autorización expresa.
> - Tu **empleador NO recibe tus respuestas individuales**, solo reportes **agregados** que protegen tu anonimato (mínimo 4 personas por categoría).
> - La **finalidad** es exclusivamente cumplir con la Resolución 2764 de 2022 del Ministerio del Trabajo (Batería de Riesgo Psicosocial) y diseñar planes de mejora del clima laboral.
> - Tienes derecho a **conocer, actualizar, rectificar, suprimir** tus datos y **revocar** la autorización en cualquier momento, escribiendo a **{{tenant.contact_email}}**.
> - Conservamos tus datos por **5 años** (vigilancia epidemiológica) y luego los anonimizamos.
>
> **[Ver política completa]({{tenant.privacy_policy_url}})**
>
> ☐ He leído y autorizo el tratamiento de mis datos personales conforme a este aviso.
> ☐ Sé que puedo revocar esta autorización en cualquier momento.

---

## Texto completo de autorización (debe quedar firmado y almacenado)

**Yo, [Nombre del trabajador]**, identificado con [Tipo Documento] número [Número Documento], en calidad de trabajador de la empresa **[Nombre Empresa]**, declaro:

1. Que he sido informado de manera previa y expresa de la **identidad del responsable** del tratamiento de mis datos: **{{tenant.legal_name}}** (NIT {{tenant.nit}}), con domicilio en {{tenant.address}}, contacto {{tenant.contact_email}}.

2. Que conozco la **finalidad específica** del tratamiento: aplicar la Batería de Riesgo Psicosocial conforme a la Resolución 2764 de 2022 del Ministerio del Trabajo de Colombia, y generar planes de intervención y vigilancia epidemiológica para mi empleador.

3. Que entiendo que entre los datos a recolectar hay **datos sensibles** relacionados con mi **salud mental, percepción de estrés laboral y factores psicosociales**, los cuales requieren mi autorización expresa conforme al Art. 6 de la Ley 1581 de 2012.

4. Que conozco mis **derechos** como titular de los datos:
   - Conocer, actualizar y rectificar mis datos.
   - Solicitar prueba de esta autorización.
   - Ser informado del uso dado a mis datos.
   - Presentar quejas ante la Superintendencia de Industria y Comercio (SIC).
   - Revocar esta autorización y/o solicitar la supresión cuando no se respeten los principios legales.

5. Que sé que **mi empleador NO recibirá mis respuestas individuales**, sino reportes agregados que preservan mi anonimato. Para que mis resultados individuales sean visibles a un consultor, **debo solicitarlo expresamente** mediante el procedimiento `/individual-results/request/...`.

6. Que autorizo expresamente que mis datos sean almacenados en la infraestructura de **DigitalOcean** (hosting de base de datos) y que las comunicaciones operativas sean enviadas a través de **SendGrid**.

7. **OTORGO mi autorización LIBRE, EXPRESA, INFORMADA y ESPECÍFICA** para el tratamiento de mis datos personales conforme a lo arriba descrito y a la [Política de Tratamiento de Datos]({{tenant.privacy_policy_url}}) de psyrisk.

**Fecha:** [auto-generada al aceptar] | **IP:** [auto-registrada] | **Token de sesión:** [auto-registrada]

---

## Implementación técnica

Las variables `{{tenant.*}}` se reemplazan en runtime con datos del tenant del psicólogo que opera la evaluación:
- `{{tenant.legal_name}}` → `tenants.legal_name`
- `{{tenant.nit}}` → `tenants.nit`
- `{{tenant.address}}` → `tenants.address`
- `{{tenant.contact_email}}` → `tenants.contact_email`
- `{{tenant.privacy_policy_url}}` → URL pública del documento `legal/01_politica_tratamiento_datos.md` o un fallback institucional.

La aceptación queda registrada en la tabla `worker_demographics` (campo `consent_given_at` ya existe — ver migración `2025-12-23-000001_AddConsentToWorkerDemographics`).

---

## Pendientes para el sponsor

- [ ] Validar el texto con asesor legal (cuando se pueda).
- [ ] Decidir URL pública de la política (`/legal/politica-de-tratamiento-de-datos` o subdominio).
- [ ] Confirmar que el campo `consent_given_at` también almacena el **hash del aviso visto** para evidencia auditoría (si no, lo agregamos como mejora Día 7).
