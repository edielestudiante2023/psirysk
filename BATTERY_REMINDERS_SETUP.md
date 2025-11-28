# Sistema de Recordatorios Automáticos de Batería Psicosocial

## 📋 Descripción

Sistema automatizado para enviar recordatorios por email sobre las evaluaciones periódicas de riesgo psicosocial según la **Resolución 2764 de 2022** del Ministerio del Trabajo de Colombia.

## 🎯 Funcionalidades

### 1. **Cálculo Automático de Periodicidad**
- **Evaluación Anual (1 año)**: Cuando el riesgo intralaboral es Alto o Muy Alto
- **Evaluación Bienal (2 años)**: Cuando el riesgo intralaboral es Medio, Bajo o Sin Riesgo

### 2. **Notificaciones Automáticas**
- ✉️ **30 días antes**: Recordatorio anticipado
- ⚠️ **7 días antes**: Recordatorio urgente
- 🚨 **Después del vencimiento**: Alerta de evaluación vencida

### 3. **Panel Administrativo**
- Vista de todas las evaluaciones programadas
- Alertas visuales de evaluaciones próximas y vencidas
- Gestión manual de recordatorios
- Historial de notificaciones enviadas

---

## 🚀 Instalación

### Paso 1: Ejecutar Migraciones

```bash
cd C:\xampp\htdocs\psyrisk
php spark migrate
```

Esto creará la tabla `battery_schedules` con todos los campos necesarios.

### Paso 2: Configurar SendGrid

#### 2.1. Crear cuenta en SendGrid
1. Ir a https://sendgrid.com/
2. Crear una cuenta gratuita (permite 100 emails/día)
3. Verificar tu email y dominio

#### 2.2. Obtener API Key
1. En SendGrid Dashboard: Settings > API Keys
2. Crear nueva API Key con permisos "Full Access"
3. Copiar la API Key (solo se muestra una vez)

#### 2.3. Configurar en CodeIgniter

Editar el archivo `app/Config/Email.php`:

```php
<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Email extends BaseConfig
{
    public string $fromEmail = 'noreply@tudominio.com';
    public string $fromName = 'PsyRisk - Sistema de Riesgo Psicosocial';

    // Configuración SendGrid
    public array $sendgrid = [
        'protocol' => 'smtp',
        'SMTPHost' => 'smtp.sendgrid.net',
        'SMTPUser' => 'apikey',  // Literal "apikey"
        'SMTPPass' => 'TU_SENDGRID_API_KEY_AQUI',  // Tu API Key de SendGrid
        'SMTPPort' => 587,
        'SMTPCrypto' => 'tls',
        'mailType' => 'html',
        'charset' => 'utf-8',
        'wordWrap' => true,
        'newline' => "\r\n",
    ];

    // Otras configuraciones...
}
```

### Paso 3: Configurar Tareas Programadas (Cron)

#### En Windows (Task Scheduler):

1. Abrir "Programador de tareas" (Task Scheduler)
2. Crear nueva tarea básica
3. Configurar:
   - **Nombre**: Recordatorios Batería Psicosocial
   - **Desencadenador**: Diariamente a las 8:00 AM
   - **Acción**: Iniciar un programa
   - **Programa**: `C:\xampp\php\php.exe`
   - **Argumentos**: `C:\xampp\htdocs\psyrisk\spark reminders:send`

#### En Linux (Crontab):

```bash
# Editar crontab
crontab -e

# Agregar línea (ejecutar diariamente a las 8:00 AM)
0 8 * * * /usr/bin/php /var/www/psyrisk/spark reminders:send >> /var/log/psyrisk-reminders.log 2>&1
```

---

## 📊 Uso del Sistema

### 1. Crear Recordatorio Automáticamente

Cuando se completa una evaluación de batería:

```php
// En tu controlador después de finalizar la evaluación
$scheduleController = new \App\Controllers\BatteryScheduleController();
$scheduleController->createFromService($serviceId);
```

Esto automáticamente:
- Calcula el nivel de riesgo intralaboral (Forma A y B)
- Determina la periodicidad (1 o 2 años)
- Programa la próxima evaluación
- Activa los recordatorios automáticos

### 2. Acceder al Panel Administrativo

URL: `http://localhost/psyrisk/battery-schedules`

**Funcionalidades:**
- Ver todas las evaluaciones programadas
- Alertas de evaluaciones próximas (30 días)
- Alertas de evaluaciones vencidas
- Gestionar recordatorios manualmente
- Ver historial de notificaciones enviadas

### 3. Ejecutar Manualmente el Envío de Recordatorios

```bash
php spark reminders:send
```

Este comando:
- Busca evaluaciones que necesiten recordatorio de 30 días
- Busca evaluaciones que necesiten recordatorio de 7 días
- Busca evaluaciones vencidas sin notificar
- Envía los emails correspondientes
- Registra el envío en la base de datos

---

## 📧 Plantillas de Email

El sistema envía 3 tipos de emails:

### 1. **Recordatorio 30 días**
- Asunto: "Recordatorio: Próxima evaluación de riesgo psicosocial en 30 días"
- Contenido: Información de la evaluación, recomendaciones de preparación
- Enviado: Exactamente 30 días antes de la fecha programada

### 2. **Recordatorio 7 días**
- Asunto: "Urgente: Evaluación de riesgo psicosocial en 7 días"
- Contenido: Alerta urgente, acciones inmediatas requeridas
- Enviado: Exactamente 7 días antes de la fecha programada

### 3. **Notificación de Vencimiento**
- Asunto: "⚠️ Evaluación de riesgo psicosocial VENCIDA"
- Contenido: Alerta de incumplimiento, consecuencias legales, urgencia
- Enviado: El día posterior al vencimiento

---

## 🗄️ Estructura de la Base de Datos

### Tabla: `battery_schedules`

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | INT | ID único |
| `battery_service_id` | INT | Referencia al servicio de batería |
| `company_name` | VARCHAR(255) | Nombre de la empresa |
| `contact_email` | VARCHAR(255) | Email de contacto para notificaciones |
| `contact_name` | VARCHAR(255) | Nombre del contacto |
| `evaluation_date` | DATE | Fecha de la última evaluación |
| `intervention_start_date` | DATE | Fecha de inicio de intervenciones |
| `intralaboral_risk_level` | ENUM | Nivel de riesgo general (máximo entre A y B) |
| `forma_a_risk_level` | ENUM | Nivel de riesgo Forma A |
| `forma_b_risk_level` | ENUM | Nivel de riesgo Forma B |
| `periodicity_years` | TINYINT | 1=Anual, 2=Bienal |
| `next_evaluation_date` | DATE | Fecha calculada de próxima evaluación |
| `notification_30_days_sent` | BOOLEAN | Indica si se envió recordatorio 30 días |
| `notification_7_days_sent` | BOOLEAN | Indica si se envió recordatorio 7 días |
| `notification_overdue_sent` | BOOLEAN | Indica si se envió notificación de vencimiento |
| `status` | ENUM | active, completed, cancelled |
| `notes` | TEXT | Notas adicionales |

---

## 🔍 Troubleshooting

### Los emails no se envían

1. **Verificar configuración SendGrid**:
   ```bash
   php spark email:test
   ```

2. **Verificar logs**:
   ```bash
   tail -f writable/logs/log-*.log
   ```

3. **Probar envío manual**:
   ```php
   $email = \Config\Services::email();
   $email->setFrom('noreply@tudominio.com');
   $email->setTo('test@example.com');
   $email->setSubject('Test');
   $email->setMessage('Testing SendGrid');
   $email->send();
   ```

### El cron no se ejecuta

1. **Windows**: Verificar en Task Scheduler que la tarea esté habilitada
2. **Linux**: Verificar logs de cron: `grep CRON /var/log/syslog`
3. **Ejecutar manualmente** para verificar que funciona: `php spark reminders:send`

### No se calculan las fechas correctamente

Verificar que:
- Los resultados de la batería estén guardados en `calculated_results`
- El campo `intralaboral_total_puntaje` tenga valores
- La Forma A y/o B tengan nivel de riesgo calculado

---

## 📈 Monitoreo y Reportes

### Ver estadísticas de recordatorios

```sql
-- Total de recordatorios activos
SELECT COUNT(*) FROM battery_schedules WHERE status = 'active';

-- Próximas evaluaciones (30 días)
SELECT company_name, next_evaluation_date
FROM battery_schedules
WHERE status = 'active'
AND next_evaluation_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY);

-- Evaluaciones vencidas
SELECT company_name, next_evaluation_date, intralaboral_risk_level
FROM battery_schedules
WHERE status = 'active'
AND next_evaluation_date < CURDATE();

-- Historial de notificaciones enviadas
SELECT company_name,
       notification_30_days_sent_at,
       notification_7_days_sent_at,
       notification_overdue_sent_at
FROM battery_schedules
WHERE notification_30_days_sent = 1 OR notification_7_days_sent = 1;
```

---

## 🎨 Personalización

### Modificar plantillas de email

Editar el archivo: `app/Commands/SendBatteryReminders.php`

Buscar el método `getEmailBody()` para personalizar el HTML de los emails.

### Cambiar frecuencia de notificaciones

Editar `app/Models/BatteryScheduleModel.php`:

```php
// Cambiar de 30 a 45 días
public function getPending30DaysNotifications()
{
    $targetDate = date('Y-m-d', strtotime('+45 days')); // Modificar aquí
    // ...
}
```

---

## 📞 Soporte

Para problemas o sugerencias:
- Email: soporte@psyrisk.com
- Documentación: https://psyrisk.com/docs
- GitHub Issues: https://github.com/psyrisk/psyrisk/issues

---

## 📝 Licencia

Este módulo es parte del sistema PsyRisk y está sujeto a la misma licencia del proyecto principal.

**Versión**: 1.0
**Fecha**: Enero 2025
**Autor**: Equipo PsyRisk
