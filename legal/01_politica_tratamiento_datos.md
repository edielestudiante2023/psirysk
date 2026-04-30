# Política de Tratamiento de Datos Personales — psyrisk

> **BORRADOR — pendiente revisión legal final por el sponsor antes de publicar.**
> Versión 1.0 — Fecha: 2026-04-25 — Vigencia: a partir del 4 de mayo de 2026.

---

## 1. Responsable del Tratamiento

**CYCLOID TALENT SAS** (en adelante "psyrisk" o "la Plataforma"), sociedad colombiana identificada con NIT **901.653.912**, domiciliada en Colombia, contacto:

- **Email:** head.consultant.cycloidtalent@gmail.com
- **Sitio web:** https://cycloidtalent.com/

es el **responsable del tratamiento** de los datos personales recolectados a través de la plataforma psyrisk.

## 2. Marco legal aplicable

Esta política se rige por:
- **Ley 1581 de 2012** ("Régimen General de Protección de Datos Personales").
- **Decreto 1377 de 2013** (reglamentario).
- **Decreto 1074 de 2015** (Decreto Único Reglamentario).
- **Resolución 2764 de 2022** del Ministerio del Trabajo (Batería de Riesgo Psicosocial).
- **Circular Externa 002 de 2022** de la Superintendencia de Industria y Comercio (SIC) sobre Habeas Data.

## 3. Datos personales recolectados

### 3.1 Datos de identificación
Nombre, número y tipo de documento, email, teléfono, fecha de ingreso, cargo, área.

### 3.2 Datos sociodemográficos
Sexo, edad, estado civil, nivel educativo, estrato socioeconómico, lugar de residencia, tipo de contrato.

### 3.3 Datos sensibles — categorías especiales (Art. 5 Ley 1581)
Por la naturaleza del instrumento (Batería de Riesgo Psicosocial del Ministerio del Trabajo), psyrisk recolecta y procesa datos sensibles relativos a la **salud mental y emocional** del trabajador, incluyendo:

- Respuestas a cuestionarios de **factores intralaborales** (formas A y B).
- Respuestas a cuestionarios de **factores extralaborales**.
- Respuestas al **cuestionario de estrés**.
- Niveles calculados de riesgo psicosocial individual y colectivo.

> ⚠️ **El tratamiento de estos datos requiere autorización previa, expresa, informada y específica del titular**, conforme al Art. 6 de la Ley 1581.

## 4. Finalidad del tratamiento

Los datos se tratan exclusivamente para:

1. Aplicar la Batería de Riesgo Psicosocial conforme a la Resolución 2764 de 2022.
2. Generar reportes agregados (no individuales) para el empleador, en cumplimiento del SG-SST.
3. Generar planes de intervención y vigilancia epidemiológica.
4. Almacenar la evidencia documental requerida por la normativa colombiana.
5. Comunicaciones operativas con el trabajador (envío de enlace, recordatorios) vía SendGrid.
6. Análisis estadístico anonimizado para mejora del servicio.

**psyrisk NO usa los datos para:** publicidad, perfilamiento comercial, venta a terceros, decisiones automatizadas con efecto jurídico sobre el trabajador.

## 5. Derechos del titular (Habeas Data)

Conforme al Art. 8 de la Ley 1581, el trabajador titular puede:

- **Conocer, actualizar y rectificar** sus datos.
- **Solicitar prueba** de la autorización otorgada.
- **Ser informado** del uso dado a sus datos.
- **Presentar quejas** ante la SIC.
- **Revocar la autorización** y/o solicitar la **supresión** del dato cuando no se respeten los principios legales.
- **Acceder gratuitamente** a sus datos.

## 6. Procedimiento para ejercer derechos

El titular puede ejercer sus derechos enviando solicitud a **head.consultant.cycloidtalent@gmail.com** con:

1. Nombre completo y número de documento.
2. Petición concreta (consulta, rectificación, supresión, revocatoria).
3. Email de contacto para respuesta.

**Plazos de respuesta:**
- **Consultas:** máximo **10 días hábiles** desde la recepción.
- **Reclamos:** máximo **15 días hábiles**, prorrogables 8 días hábiles más.

## 7. Encargados del tratamiento

psyrisk transfiere datos a los siguientes encargados, todos bajo contrato de tratamiento:

| Encargado | Servicio | País | Finalidad |
|---|---|---|---|
| DigitalOcean LLC | Hosting de base de datos MySQL | EE.UU. (con certificación adecuada) | Almacenamiento |
| SendGrid (Twilio) | Email transaccional | EE.UU. | Envío de enlaces y recordatorios |
| Wompi (Bancolombia) | Pasarela de pagos | Colombia | Cobro a tenants |

> Para transferencias internacionales se aplica el Art. 26 de la Ley 1581 (nivel adecuado de protección o autorización expresa).

## 8. Confidencialidad y aislamiento

Cada **tenant** (psicólogo o consultora afiliada) opera en un espacio lógicamente aislado:
- Solo puede acceder a los datos de **sus** empresas y trabajadores.
- Los reportes individuales solo son visibles bajo solicitud expresa del titular (mecanismo `individual_results_requests`).
- Los reportes agregados al empleador se entregan con un mínimo de N>=4 trabajadores por categoría para preservar el anonimato.

## 9. Medidas de seguridad

- Conexiones cifradas TLS/SSL en todo el tránsito.
- Base de datos con SSL obligatorio (`sslmode=REQUIRED`).
- Contraseñas hash con `password_hash` (bcrypt) — nunca en texto plano.
- Aislamiento de tenants enforced a nivel de query (filter middleware).
- Backups cifrados periódicos.
- Auditoría de accesos (last_login, created_at, updated_at).

## 10. Conservación de datos

Los datos se conservan por el tiempo legal requerido:
- **Datos de evaluación:** mínimo **5 años** según Resolución 2764 (vigilancia epidemiológica).
- **Datos contables / facturación:** **10 años** según Código de Comercio.
- Pasados estos términos y previa solicitud, se anonimizan o suprimen.

## 11. Modificaciones

psyrisk se reserva el derecho de modificar esta política, notificando con **15 días de anticipación** a los titulares activos vía email.

## 12. Vigencia

Vigente desde el **4 de mayo de 2026**. Versión actual: **1.0**.

## 13. Registro Nacional de Bases de Datos (RNBD)

CYCLOID TALENT SAS se compromete a registrar la base de datos psyrisk ante la Superintendencia de Industria y Comercio dentro de los plazos legales aplicables a su tamaño empresarial.

---

## Anexo A — Aviso de Privacidad (resumen visible en el portal del trabajador)

> Tus respuestas a esta evaluación de riesgo psicosocial son tratadas por **CYCLOID TALENT SAS** (NIT 901.653.912) bajo la Ley 1581 de 2012. Tu empleador solo recibe **reportes agregados**, nunca tus respuestas individuales. Tienes derecho a conocer, actualizar y suprimir tus datos escribiendo a head.consultant.cycloidtalent@gmail.com. Política completa: [/legal/politica-de-tratamiento-de-datos](/legal/politica-de-tratamiento-de-datos).

---

> **TAREAS PENDIENTES PARA SPONSOR:**
> 1. Validar dirección física exacta de Cycloid Talent SAS (no apareció en BD).
> 2. Decidir email oficial de contacto de privacidad (¿head.consultant@? ¿privacidad@? ¿dpo@cycloidtalent.com?).
> 3. Confirmar el nombre del **Oficial de Protección de Datos (DPO)** si Cycloid lo tiene formalmente designado, o asignar la función al sponsor ejecutivo.
> 4. Iniciar registro RNBD ante SIC (formulario en https://www.sic.gov.co — gratuito si la empresa < 100 empleados).
