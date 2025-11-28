# SendGrid Quick Start Guide - PsyRisk Battery Reminders

## Estado Actual: ✅ CONFIGURADO, PROBADO Y FUNCIONANDO

### ✅ Configuración Completada y Verificada

Su sistema SendGrid está completamente configurado y ha sido probado exitosamente:

- **Email From**: notificacion.cycloidtalent@cycloidtalent.com
- **Email Name**: Cycloid Talent SAS
- **SMTP Host**: smtp.sendgrid.net
- **API Key**: Configurada en .env ✓
- **Puerto**: 587 (TLS) ✓
- **Estado**: ✅ SISTEMA COMPLETAMENTE FUNCIONAL

### ✅ Pruebas Realizadas:

- ✓ Email de prueba enviado exitosamente
- ✓ Recordatorio de 30 días enviado y registrado
- ✓ Recordatorio de 7 días enviado y registrado
- ✓ Notificación de vencimiento enviada y registrada
- ✓ Migración de base de datos completada
- ✓ Tabla `battery_schedules` creada con 3 recordatorios de prueba
- ✓ Comando `php spark reminders:send` funcionando correctamente

---

## 🚀 Cómo Usar el Sistema

### 1. Ver Panel de Recordatorios

Accede a: `http://localhost/psyrisk/battery-schedules`

Este panel muestra:
- Todas las evaluaciones programadas
- Alertas de evaluaciones próximas (30 días)
- Alertas de evaluaciones vencidas
- Historial de notificaciones enviadas

### 2. Crear Recordatorio Automáticamente

Cuando completes una batería psicosocial, el sistema puede crear automáticamente el recordatorio:

```php
// En tu código después de cerrar un servicio
$scheduleController = new \App\Controllers\BatteryScheduleController();
$scheduleController->createFromService($serviceId);
```

Esto automáticamente:
- ✓ Calcula el nivel de riesgo intralaboral (Forma A y B)
- ✓ Determina periodicidad (1 año para Alto/Muy Alto, 2 años para otros)
- ✓ Programa la próxima evaluación
- ✓ Activa recordatorios automáticos

### 3. Ejecutar Envío de Recordatorios Manualmente

```bash
php spark reminders:send
```

Este comando busca y envía:
- 📧 Recordatorios de 30 días antes
- ⚠️ Recordatorios de 7 días antes
- 🚨 Notificaciones de evaluaciones vencidas

### 4. Verificar Configuración SendGrid

```bash
php spark test:email tu@email.com
```

---

## 📧 Tipos de Emails Enviados

### 1. Recordatorio 30 Días
- **Cuándo**: Exactamente 30 días antes de la fecha programada
- **Asunto**: "Recordatorio: Próxima evaluación de riesgo psicosocial en 30 días"
- **Contenido**: Información de la evaluación, recomendaciones de preparación

### 2. Recordatorio 7 Días
- **Cuándo**: Exactamente 7 días antes de la fecha programada
- **Asunto**: "Urgente: Evaluación de riesgo psicosocial en 7 días"
- **Contenido**: Alerta urgente, acciones inmediatas requeridas

### 3. Notificación de Vencimiento
- **Cuándo**: Después de que vence la fecha programada
- **Asunto**: "⚠️ Evaluación de riesgo psicosocial VENCIDA"
- **Contenido**: Alerta de incumplimiento normativo, consecuencias legales

---

## ⚙️ Configurar Tarea Programada (Cron)

Para que los recordatorios se envíen automáticamente cada día:

### En Windows (Task Scheduler):

1. Abrir "Programador de tareas"
2. Crear nueva tarea básica
3. Configurar:
   - **Nombre**: Recordatorios Batería Psicosocial
   - **Desencadenador**: Diariamente a las 8:00 AM
   - **Acción**: Iniciar un programa
   - **Programa**: `C:\xampp\php\php.exe`
   - **Argumentos**: `C:\xampp\htdocs\psyrisk\spark reminders:send`
   - **Iniciar en**: `C:\xampp\htdocs\psyrisk`

### En Linux (Crontab):

```bash
# Editar crontab
crontab -e

# Agregar línea (ejecutar diariamente a las 8:00 AM)
0 8 * * * /usr/bin/php /var/www/psyrisk/spark reminders:send >> /var/log/psyrisk-reminders.log 2>&1
```

---

## 🧪 Crear Recordatorio de Prueba

Para probar el sistema completo, puedes crear un recordatorio manualmente en la base de datos:

```sql
-- Insertar recordatorio de prueba que vence en 30 días
INSERT INTO battery_schedules (
    battery_service_id,
    company_name,
    contact_email,
    contact_name,
    evaluation_date,
    intralaboral_risk_level,
    forma_a_risk_level,
    periodicity_years,
    next_evaluation_date,
    status
) VALUES (
    1,  -- ID de servicio existente
    'Empresa de Prueba S.A.S.',
    'tu@email.com',  -- TU EMAIL AQUÍ
    'Tu Nombre',
    CURDATE(),
    'riesgo_alto',
    'riesgo_alto',
    1,
    DATE_ADD(CURDATE(), INTERVAL 30 DAY),  -- Vence en 30 días
    'active'
);
```

Luego ejecuta:
```bash
php spark reminders:send
```

Deberías recibir un email de recordatorio de 30 días.

---

## 🔍 Verificar Recordatorios Pendientes

```sql
-- Ver todos los recordatorios activos
SELECT
    company_name,
    contact_email,
    next_evaluation_date,
    DATEDIFF(next_evaluation_date, CURDATE()) as dias_restantes,
    notification_30_days_sent,
    notification_7_days_sent
FROM battery_schedules
WHERE status = 'active'
ORDER BY next_evaluation_date;

-- Ver recordatorios que necesitan notificación de 30 días
SELECT company_name, contact_email, next_evaluation_date
FROM battery_schedules
WHERE status = 'active'
AND next_evaluation_date = DATE_ADD(CURDATE(), INTERVAL 30 DAY)
AND notification_30_days_sent = 0;

-- Ver evaluaciones vencidas
SELECT company_name, contact_email, next_evaluation_date
FROM battery_schedules
WHERE status = 'active'
AND next_evaluation_date < CURDATE()
AND notification_overdue_sent = 0;
```

---

## 📋 Estructura de la Base de Datos

```
battery_schedules
├── id (auto_increment)
├── battery_service_id (FK)
├── company_name
├── contact_email (destinatario de emails)
├── contact_name
├── evaluation_date (fecha última evaluación)
├── intralaboral_risk_level (nivel general: máximo entre A y B)
├── forma_a_risk_level
├── forma_b_risk_level
├── periodicity_years (1=Anual, 2=Bienal)
├── next_evaluation_date (fecha calculada de próxima evaluación)
├── notification_30_days_sent (booleano)
├── notification_30_days_sent_at (timestamp)
├── notification_7_days_sent (booleano)
├── notification_7_days_sent_at (timestamp)
├── notification_overdue_sent (booleano)
├── notification_overdue_sent_at (timestamp)
├── status (active, completed, cancelled)
├── notes (texto libre)
├── created_at
└── updated_at
```

---

## 🔧 Troubleshooting

### Los emails no llegan

1. **Verificar configuración**:
   ```bash
   php spark test:email tu@email.com
   ```

2. **Verificar API Key de SendGrid**:
   - Ir a: https://app.sendgrid.com/settings/api_keys
   - Verificar que la API Key está activa
   - Si es necesario, crear una nueva y actualizar `.env`

3. **Verificar logs**:
   ```bash
   tail -f writable/logs/log-*.log
   ```

### El comando reminders:send no encuentra recordatorios

Verificar que hay recordatorios con fechas correctas:
```sql
SELECT * FROM battery_schedules WHERE status = 'active';
```

### La tarea programada no se ejecuta

**Windows**: Verificar en Task Scheduler que:
- La tarea está habilitada
- La ruta al PHP es correcta
- La ruta al proyecto es correcta

**Linux**: Verificar logs de cron:
```bash
grep CRON /var/log/syslog
```

---

## 📖 Normativa

**Resolución 2764 de 2022** (Ministerio del Trabajo de Colombia)

### Periodicidad de Evaluación:

1. **Evaluación ANUAL (1 año)**:
   - Cuando el riesgo psicosocial intralaboral es **Alto** o **Muy Alto**

2. **Evaluación BIENAL (2 años)**:
   - Cuando el riesgo es **Medio, Bajo o Sin Riesgo**

### Punto de Partida:
- El conteo de periodicidad inicia desde la **fecha de inicio de las acciones de intervención**
- NO desde la fecha de aplicación de la batería
- Solo se considera el **riesgo intralaboral** (NO extralaboral)
- Se toma el nivel **máximo** entre Forma A y Forma B

---

## 📞 Soporte

Para más información, consulta:
- [BATTERY_REMINDERS_SETUP.md](./BATTERY_REMINDERS_SETUP.md) - Documentación completa
- Logs del sistema: `writable/logs/`
- SendGrid Dashboard: https://app.sendgrid.com/

---

**Versión**: 1.0
**Fecha**: Enero 2025
**Estado**: ✓ Configurado y Funcionando
