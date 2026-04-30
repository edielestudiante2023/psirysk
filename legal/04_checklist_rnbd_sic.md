# Checklist — Registro Nacional de Bases de Datos (RNBD) ante la SIC

> Cumplimiento del Decreto 1074 de 2015, Cap. 26.
> Aplicable a: **CYCLOID TALENT SAS** como tenant inicial; replicable para cada tenant que se afilie.

---

## 1. ¿Aplica?

**Sí** si la empresa cumple alguna de estas:
- Persona jurídica.
- Activos totales superiores a 100.000 UVT (~$5.027 millones COP en 2026).
- O cuando trate datos sensibles, **independiente del tamaño** (psyrisk SÍ trata datos sensibles → aplica).

> **CYCLOID TALENT SAS aplica obligatoriamente** porque trata datos sensibles de salud mental.

## 2. Datos que necesitas para inscribir la BD

Para cada **base de datos** (en nuestro caso: la BD `psyrisk`), la SIC pide:

| Campo | Para psyrisk |
|---|---|
| **Nombre de la BD** | `psyrisk` |
| **Finalidad** | Aplicación de la Batería de Riesgo Psicosocial (Resolución 2764/2022 Mintrabajo) y generación de reportes de vigilancia epidemiológica |
| **Forma de tratamiento** | Automatizado (en línea, vía web) |
| **Política de tratamiento de datos** | URL pública del documento (`legal/01_politica_tratamiento_datos.md` publicado en `/legal/politica`) |
| **Encargados del tratamiento** | DigitalOcean LLC (hosting BD), Twilio Inc. dba SendGrid (email), Wompi/Bancolombia (pagos) |
| **Países de almacenamiento** | EE.UU. (DigitalOcean), Colombia (Wompi) |
| **Cantidad de titulares** | Aprox. al cierre Q1: ~700 trabajadores + 18 usuarios del sistema (actualizable) |
| **Categorías de datos sensibles** | Salud mental, percepción psicosocial laboral |
| **Medidas de seguridad** | TLS, hash de passwords, aislamiento por tenant, backups cifrados, restricción de acceso por rol |

## 3. Documentos a tener listos antes de inscribir

- [ ] **Certificado de existencia y representación legal** de Cycloid Talent SAS (no mayor a 30 días).
- [ ] **Cédula del representante legal**.
- [ ] **Política de Tratamiento de Datos** publicada en URL pública.
- [ ] **Aviso de Privacidad** publicado en URL pública.
- [ ] **Procedimiento interno** documentado para atender consultas y reclamos (plazos: 10/15 días hábiles).
- [ ] **Modelo de autorización** firmable por el titular (Anexo del aviso).
- [ ] **Contratos con encargados** (DigitalOcean, SendGrid, Wompi) — pueden ser los Términos de Servicio firmados al contratar.

## 4. Procedimiento

1. Crear cuenta en https://www.sic.gov.co (gratuito, ciudadano + empresarial).
2. Sección **Habeas Data → Registro Nacional de Bases de Datos**.
3. Completar formulario por cada BD (en nuestro caso: 1 — `psyrisk`).
4. Adjuntar política de tratamiento de datos.
5. Recibir **número de inscripción** (lo da inmediatamente el sistema).
6. Publicar el número en la política de privacidad de psyrisk.

## 5. Mantenimiento posterior

- **Actualizar anualmente** entre el 1° de enero y el 31 de marzo, incluso si nada cambió.
- Si cambia la **finalidad**, los **encargados** o los **países**: actualizar dentro de los 10 días hábiles.
- Si hay **incidente de seguridad** que comprometa datos: **reportar a la SIC dentro de los 15 días hábiles** siguientes a su detección (Art. 17 Ley 1581).

## 6. Plan de acción (sponsor)

- [ ] **Antes del 4 de mayo:** publicar política y aviso en /legal/politica y /legal/aviso.
- [ ] **Primera semana de mayo:** completar inscripción en RNBD/SIC.
- [ ] **Mayo 31:** documentar procedimiento interno de habeas data y crear inbox `privacidad@cycloidtalent.com` (o el que se decida).
- [ ] **Trimestral:** revisar volumen de titulares e ir actualizando.

## 7. Para tenants nuevos (Fase 2)

Cada tenant que se afilie debe registrar **su propia** BD ante la SIC, porque cada uno es responsable de los datos de sus trabajadores. **psyrisk** aparece en la lista de **encargados** de cada tenant.

> Esto es un **diferenciador comercial** de psyrisk: si onboardeamos a un psicólogo, le ahorramos lo técnico, **pero el RNBD es de él**, no nuestro. Hay que comunicarlo claramente en el onboarding para evitar malos entendidos.

---

## Pendientes urgentes (para sponsor)

- [ ] Asignar persona/email responsable para gestión Habeas Data en Cycloid.
- [ ] Confirmar URL pública definitiva de política y aviso.
- [ ] Iniciar inscripción en RNBD durante la primera semana después del go-live.
