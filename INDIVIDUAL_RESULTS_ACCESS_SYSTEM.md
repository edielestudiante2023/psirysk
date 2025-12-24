# Sistema de Solicitudes de Acceso a Resultados Individuales

## Descripción General

Este módulo implementa un sistema robusto y conforme a la legislación colombiana para gestionar el acceso a resultados individuales de la Batería de Riesgo Psicosocial. El sistema balancea la protección de datos personales sensibles (Ley 1581 de 2012) con la necesidad legítima de acceder a estos resultados en casos justificados.

## Características Principales

### ✅ Para Clientes
- **Solicitud Formal**: Formulario web para solicitar acceso con motivación detallada (mínimo 20 caracteres)
- **Seguimiento en Tiempo Real**: Vista de estado de la solicitud con actualización automática
- **Acceso Temporal**: Una vez aprobado, acceso limitado por tiempo (48 horas por defecto)
- **Notificaciones Email**: Avisos automáticos cuando la solicitud es procesada
- **Trazabilidad Completa**: Registro de IP, user agent y timestamps para auditoría

### ✅ Para Consultores
- **Dashboard de Gestión**: Vista centralizada de todas las solicitudes pendientes
- **Alertas de Urgencia**: Badge para solicitudes con más de 24 horas sin revisar
- **Revisión Detallada**: Vista completa de la solicitud con toda la información contextual
- **Aprobación/Rechazo**: Flujo simple con notas obligatorias en rechazos
- **Notificaciones Email**: Reciben email inmediato cuando hay nueva solicitud
- **Aprobación por Email**: Link mágico para aprobar directamente desde el email

### ✅ Integración con SendGrid
- **Emails Profesionales**: Templates HTML branded de Cycloid Talent
- **3 Tipos de Emails**:
  1. **Al Consultor**: Nueva solicitud con link directo de aprobación
  2. **Al Cliente (Aprobado)**: Link de acceso con fecha de expiración
  3. **Al Cliente (Rechazado)**: Razones del rechazo y contacto para más info
- **Trazabilidad**: Logs de todos los emails enviados

## Arquitectura del Sistema

### Base de Datos

**Tabla**: `individual_results_requests`

```sql
id INT - ID único de la solicitud
service_id INT - Servicio de batería
worker_id INT - Trabajador cuyos resultados se solicitan
requester_user_id INT - Usuario que solicita
request_type ENUM - Tipo: intralaboral_a/b, extralaboral, estres
motivation TEXT - Justificación (REQUERIDO LEGALMENTE)
status ENUM - pending/approved/rejected
reviewed_by INT - Consultor que revisó
review_notes TEXT - Notas del consultor
reviewed_at DATETIME - Fecha de revisión
access_granted_until DATETIME - Fecha de expiración del acceso
access_token VARCHAR(64) - Token único para acceso
ip_address VARCHAR(45) - IP del solicitante
user_agent VARCHAR(255) - Navegador del solicitante
created_at DATETIME
updated_at DATETIME
```

### Flujo de Trabajo

```
1. Cliente hace clic en "Ver" → Redirige a formulario de solicitud
2. Cliente llena motivación → Submit crea registro en BD
3. Sistema envía email al consultor con links de:
   - Revisar en dashboard
   - Aprobar directo (48h)
4. Consultor aprueba/rechaza:
   - Aprobado: Se genera acceso temporal + email al cliente
   - Rechazado: Email al cliente con razones
5. Cliente accede mediante token único
6. Sistema valida:
   - Token válido
   - Estado = approved
   - Fecha no expirada
7. Si válido → Redirige a resultados individuales
8. Al expirar → Acceso denegado automáticamente
```

### Archivos Principales

#### Migración
- `app/Database/Migrations/*_CreateIndividualResultsRequestsTable.php`

#### Modelo
- `app/Models/IndividualResultRequestModel.php`
  - Métodos: `getRequestWithDetails()`, `approveRequest()`, `rejectRequest()`, `hasActiveAccess()`, etc.

#### Controlador
- `app/Controllers/IndividualResultsController.php`
  - `requestAccess()` - Formulario de solicitud
  - `submitRequest()` - Procesar solicitud
  - `showStatus()` - Estado de la solicitud
  - `viewResults()` - Ver resultados (valida token)
  - `managementDashboard()` - Dashboard para consultores
  - `reviewRequest()` - Vista de revisión
  - `approveRequest()` - Aprobar solicitud
  - `rejectRequest()` - Rechazar solicitud

#### Vistas Cliente
- `app/Views/individual_results/request_form.php` - Formulario de solicitud
- `app/Views/individual_results/request_status.php` - Estado de la solicitud
- `app/Views/individual_results/access_denied.php` - Acceso denegado/expirado
- `app/Views/individual_results/approval_success.php` - Aprobación exitosa (consultor)

#### Vistas Consultor
- `app/Views/individual_results/management_dashboard.php` - Lista de solicitudes
- `app/Views/individual_results/review_request.php` - Revisar solicitud individual

#### Templates Email
- `app/Views/emails/request_notification_consultant.php` - Email al consultor
- `app/Views/emails/request_approved_client.php` - Email de aprobación
- `app/Views/emails/request_rejected_client.php` - Email de rechazo

#### Email Service
- `app/Libraries/EmailService.php`
  - `sendRequestNotificationToConsultant()`
  - `sendRequestApprovedToClient()`
  - `sendRequestRejectedToClient()`

### Rutas

```php
GET  /individual-results/request/{serviceId}/{workerId}/{type}  - Formulario
POST /individual-results/submit                                 - Enviar solicitud
GET  /individual-results/status/{requestId}                     - Ver estado
GET  /individual-results/view/{token}                           - Acceder (validado)

GET  /individual-results/management                             - Dashboard consultor
GET  /individual-results/review/{requestId}                     - Revisar solicitud
POST /individual-results/approve/{requestId}                    - Aprobar (dashboard)
GET  /individual-results/approve/{requestId}/{token}            - Aprobar (email)
POST /individual-results/reject/{requestId}                     - Rechazar
```

## Seguridad y Cumplimiento Legal

### Protección de Datos
- ✅ Motivación obligatoria (mínimo 20 caracteres)
- ✅ Registro de IP y User Agent
- ✅ Tokens únicos de 64 caracteres (bin2hex random_bytes)
- ✅ Acceso temporal con expiración automática
- ✅ Validación en cada acceso (no solo en creación)

### Trazabilidad
- ✅ Fecha/hora de solicitud
- ✅ Fecha/hora de revisión
- ✅ Quién solicitó (usuario + IP)
- ✅ Quién revisó (consultor)
- ✅ Decisión tomada (aprobado/rechazado)
- ✅ Notas del consultor
- ✅ Fecha de expiración del acceso

### Auditoría
- ✅ Logs de emails enviados
- ✅ Registro permanente de todas las solicitudes
- ✅ No se eliminan registros (para cumplimiento legal)

## Integración con Dashboards Existentes

Los botones "Ver" en los siguientes dashboards ahora redirigen al formulario de solicitud:

- **Dashboard Intralaboral** (`app/Views/reports/intralaboral/dashboard.php`)
  - Línea 1283: Detecta automáticamente Forma A o B

- **Dashboard Extralaboral** (`app/Views/reports/extralaboral/dashboard.php`)
  - Línea 832: Solicitud tipo "extralaboral"

- **Dashboard Estrés** (`app/Views/reports/estres/dashboard.php`)
  - Línea 662: Solicitud tipo "estres"

## Configuración

### 1. Ejecutar Migración

```bash
php spark migrate
```

### 2. Verificar SendGrid

Asegúrese de que su `.env` tenga configurado SendGrid:

```env
email.fromEmail = noreply@cycloidtalent.com
email.fromName = Cycloid Talent SAS
email.protocol = smtp
email.SMTPHost = smtp.sendgrid.net
email.SMTPUser = apikey
email.SMTPPass = YOUR_SENDGRID_API_KEY
email.SMTPPort = 587
email.SMTPCrypto = tls
email.mailType = html
```

### 3. Probar Email

```bash
php spark email:test tu@email.com
```

## Uso

### Para Clientes

1. Ir a Dashboard de Intralaboral/Extralaboral/Estrés
2. Hacer clic en botón "👁️ Ver" de un trabajador
3. Completar formulario con motivación (ej: "Trabajador en proceso de incapacidad médica por presunto estrés laboral, ARL requiere resultados individuales para evaluación")
4. Aceptar términos y enviar
5. Esperar aprobación del consultor (recibirá email)
6. Una vez aprobado, hacer clic en link del email o en el dashboard

### Para Consultores

1. Recibir email con nueva solicitud
2. **Opción A**: Hacer clic en "Aprobar Acceso (48 horas)" directamente desde el email
3. **Opción B**: Ir a "Gestión de Solicitudes" en el dashboard
4. Revisar motivación y contexto
5. Aprobar (con duración personalizable) o Rechazar (con motivo obligatorio)
6. El cliente recibirá notificación automática

## Mejoras Futuras Posibles

- [ ] Recordatorio automático si consultor no responde en 48 horas
- [ ] Dashboard semanal de solicitudes pendientes
- [ ] Estadísticas de solicitudes (aprobadas/rechazadas por mes)
- [ ] Extensión de acceso sin nueva solicitud
- [ ] Acceso múltiple (solicitar varios trabajadores a la vez)
- [ ] Integración con generación de PDF individual automático
- [ ] Firma digital del consultor en aprobación

## Notas Técnicas

- El token de acceso se genera automáticamente usando `bin2hex(random_bytes(32))`
- La duración del acceso por defecto es 48 horas pero es configurable
- El sistema valida en cada acceso, no confía en sesiones
- Los emails usan templates HTML responsive
- Compatible con todos los navegadores modernos
- Auto-refresh cada 30 segundos en vista de estado pendiente

## Soporte Legal

Este sistema fue diseñado considerando:

- **Ley 1581 de 2012**: Protección de datos personales en Colombia
- **Resolución 2404 de 2019**: Batería de Riesgo Psicosocial
- **Código Deontológico del Psicólogo**: Ley 1090 de 2006
- **Lineamientos del MinTrabajo**: Confidencialidad de resultados

Cualquier consulta legal debe dirigirse al asesor jurídico de Cycloid Talent SAS.

---

**Desarrollado para Cycloid Talent SAS**
Diciembre 2024
