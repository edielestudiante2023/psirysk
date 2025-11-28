# Sistema de Encuesta de Satisfacción - PsyRisk

## 📋 Resumen del Sistema

Sistema implementado para medir la satisfacción del servicio antes de permitir descargas de PDF/Excel. Los clientes deben completar una encuesta de 5 preguntas en escala Likert + 1 pregunta abierta opcional antes de descargar informes.

---

## 🎯 Objetivo

**Asegurar que el cliente experimenta todo el sistema interactivo** (dashboards, segmentadores, informes ejecutivos, recomendaciones) **antes de evaluar el servicio**, y solo bloquear las descargas de PDF/Excel hasta que complete la encuesta.

---

## 🔄 Flujo del Sistema

### 1. Cliente accede a informes (servicio cerrado)
- ✅ Puede ver dashboards interactivos
- ✅ Puede usar segmentadores
- ✅ Puede ver informes ejecutivos
- ✅ Puede navegar recomendaciones
- ❌ **NO puede descargar PDF/Excel** hasta completar encuesta

### 2. Cliente intenta descargar PDF/Excel
- Sistema verifica si `satisfaction_survey_completed = true`
- Si NO está completada:
  - Muestra modal informativo
  - Redirige a encuesta de satisfacción
- Si SÍ está completada:
  - Permite descarga inmediatamente

### 3. Cliente completa encuesta
- 5 preguntas obligatorias (escala 1-5)
- 1 pregunta abierta opcional (comentarios)
- Al enviar:
  - Guarda respuestas en BD
  - Marca servicio como `satisfaction_survey_completed = true`
  - Redirige a informes con descargas habilitadas

---

## 📊 Preguntas de la Encuesta

### Preguntas Obligatorias (Escala Likert 1-5)

1. **¿Qué tan satisfecho está con el servicio recibido?**
   - 1: Muy insatisfecho 😞
   - 5: Muy satisfecho 😄

2. **¿El consultor fue claro y profesional durante el proceso?**
   - 1: Totalmente en desacuerdo
   - 5: Totalmente de acuerdo

3. **¿Los informes cumplen con sus expectativas?**
   - 1: Totalmente en desacuerdo
   - 5: Totalmente de acuerdo

4. **¿Recomendaría nuestros servicios a otras empresas?**
   - 1: Definitivamente no
   - 5: Definitivamente sí

5. **¿Qué tan fácil fue navegar y entender los resultados?**
   - 1: Muy difícil
   - 5: Muy fácil

### Pregunta Opcional

6. **Comentarios o sugerencias** (texto libre, máx 5000 caracteres)

---

## 🗄️ Estructura de Base de Datos

### Nueva Tabla: `service_satisfaction_surveys`

```sql
CREATE TABLE service_satisfaction_surveys (
    id INT PRIMARY KEY AUTO_INCREMENT,
    service_id INT NOT NULL,
    user_id INT NOT NULL COMMENT 'Usuario cliente que respondió',
    question_1 TINYINT(1) NOT NULL,
    question_2 TINYINT(1) NOT NULL,
    question_3 TINYINT(1) NOT NULL,
    question_4 TINYINT(1) NOT NULL,
    question_5 TINYINT(1) NOT NULL,
    comments TEXT NULL,
    completed_at DATETIME NOT NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    FOREIGN KEY (service_id) REFERENCES battery_services(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### Campo Agregado a `battery_services`

```sql
ALTER TABLE battery_services
ADD COLUMN satisfaction_survey_completed BOOLEAN DEFAULT FALSE;
```

---

## 📁 Archivos Creados/Modificados

### ✨ Archivos Nuevos

1. **Migration**: `app/Database/Migrations/2025-11-17-060543_AddSatisfactionSurveySystem.php`
   - Crea tabla `service_satisfaction_surveys`
   - Agrega campo `satisfaction_survey_completed` a `battery_services`

2. **Model**: `app/Models/SatisfactionSurveyModel.php`
   - CRUD de encuestas
   - Métodos helper: `isCompletedForService()`, `getAverageScore()`, `getCompanyStats()`

3. **Controller**: `app/Controllers/SatisfactionController.php`
   - `index($serviceId)`: Muestra formulario encuesta
   - `submit($serviceId)`: Procesa y guarda encuesta
   - `view($serviceId)`: Ver resultados (solo admin/consultor)

4. **View**: `app/Views/satisfaction/survey.php`
   - Formulario interactivo con escala Likert visual
   - Diseño responsive con gradientes
   - Emojis interactivos para cada nivel
   - Validación client-side y server-side

5. **JavaScript**: `public/js/satisfaction-check.js`
   - Intercepta clics en botones de descarga
   - Verifica vía AJAX si encuesta está completada
   - Muestra modal si falta completar
   - Permite descarga si está completada

### 🔧 Archivos Modificados

1. **app/Models/BatteryServiceModel.php**
   - Agregado `satisfaction_survey_completed` a `allowedFields`

2. **app/Controllers/ReportsController.php**
   - Modificado `checkAccess()`: Redirige a encuesta si no está completada (solo clientes)
   - Agregado `checkSurveyCompletion($serviceId)`: Endpoint AJAX para verificar
   - Modificados métodos de exportación para verificar encuesta

3. **app/Views/reports/intralaboral/dashboard.php**
   - Botón Excel modificado con atributos `data-download-type`, `data-service-id`, `data-url`
   - Agregado script `satisfaction-check.js`

4. **app/Views/reports/intralaboral/executive.php**
   - Botón PDF modificado con atributos para verificación
   - Agregado script `satisfaction-check.js`

5. **app/Config/Routes.php**
   - Agregado grupo `satisfaction` con 3 rutas
   - Agregada ruta `reports/check-survey/(:num)`

---

## 🛠️ Tecnologías Utilizadas

- **Backend**: CodeIgniter 4
- **Frontend**: Bootstrap 5
- **JavaScript**: Vanilla JS (fetch API)
- **Diseño**: CSS gradientes, animaciones, responsive
- **Validación**: Server-side (CI4) + Client-side (HTML5)

---

## 🔒 Permisos por Rol

### Cliente (cliente_empresa, cliente_gestor)
- ✅ Ver dashboards interactivos (siempre que servicio esté cerrado)
- ✅ Completar encuesta de satisfacción
- ❌ Descargar PDF/Excel sin completar encuesta
- ✅ Descargar PDF/Excel después de completar encuesta
- ❌ Ver resultados de encuestas

### Consultor
- ✅ Ver informes en cualquier estado (en_curso o cerrado)
- ✅ Descargar PDF/Excel sin restricciones (no requiere encuesta)
- ✅ Ver detalle de encuestas de satisfacción individuales

### Admin/Superadmin
- ✅ **Dashboard de análisis de satisfacción** (`/satisfaction/dashboard`)
- ✅ Ver estadísticas generales y por empresa
- ✅ Ver ranking de empresas por satisfacción
- ✅ Ver detalle de encuestas individuales
- ✅ Gráficos de distribución y promedios
- ❌ No tienen acceso a informes (según diseño actual)

### Comercial (Vendedor)
- ✅ **Dashboard de análisis de satisfacción** (`/satisfaction/dashboard`)
- ✅ Ver estadísticas generales y por empresa
- ✅ Ver ranking de empresas por satisfacción
- ✅ Ver detalle de encuestas individuales
- ✅ Útil para seguimiento comercial y facturación

---

## 🚀 Instalación

### Cuando tengas internet estable, ejecutar:

```bash
php spark migrate
```

Esto creará:
- Tabla `service_satisfaction_surveys`
- Campo `satisfaction_survey_completed` en `battery_services`

---

## 📊 Dashboard de Análisis de Satisfacción

### Acceso al Dashboard
**URL**: `/satisfaction/dashboard`
**Roles permitidos**: Admin, Superadmin, Comercial

### Funcionalidades del Dashboard

#### 📈 Estadísticas Generales
- **Total de encuestas completadas**
- **Satisfacción general promedio** (todas las empresas)
- **Promedio por pregunta individual**
- **Distribución de satisfacción** (muy bajo, bajo, medio, alto, muy alto)

#### 🏆 Ranking de Empresas
- Ordenado por promedio de satisfacción descendente
- Top 3 con medallas (oro, plata, bronce)
- Cantidad de encuestas por empresa
- Barra de progreso visual

#### 📊 Gráficos Interactivos
1. **Gráfico de Barras**: Promedio por cada pregunta
2. **Gráfico de Dona**: Distribución de niveles de satisfacción
3. **Tabla de encuestas recientes** con búsqueda y paginación

#### 🔍 Ver Detalle Individual
- Clic en "Ver Detalle" para ver encuesta completa
- Gráfico de radar con las 5 dimensiones
- Respuestas con barras de progreso
- Comentarios del cliente (si los hay)

### Métodos del Modelo

#### `SatisfactionSurveyModel::getAverageScore($serviceId)`
Calcula promedio de las 5 preguntas para un servicio.

#### `SatisfactionSurveyModel::getCompanyStats($companyId)`
Retorna:
- Total de encuestas completadas
- Promedio general de satisfacción
- Promedio por cada pregunta

Ejemplo de uso:
```php
$surveyModel = new SatisfactionSurveyModel();
$stats = $surveyModel->getCompanyStats(15);

echo "Promedio de satisfacción: " . $stats['average_score'];
echo "Total encuestas: " . $stats['total_surveys'];
```

### Vista Individual de Encuesta

**URL**: `/satisfaction/view/{serviceId}`
**Roles permitidos**: Admin, Superadmin, Consultor, Comercial

#### Contenido:
- Información del servicio
- Gráfico de radar (5 dimensiones)
- Respuestas detalladas con barras de progreso
- Promedio general destacado
- Comentarios del cliente

---

## 🎨 Características de UX

### Diseño Visual
- ✅ Gradiente atractivo (púrpura/azul)
- ✅ Tarjetas con hover effects
- ✅ Emojis interactivos por nivel
- ✅ Responsive para móviles
- ✅ Animaciones suaves

### Interacción
- ✅ Radio buttons estilizados como botones
- ✅ Selección visual clara
- ✅ Modal informativo antes de redirigir
- ✅ Validación en tiempo real
- ✅ Mensajes de confirmación

### Accesibilidad
- ✅ Labels descriptivos
- ✅ Indicadores visuales claros
- ✅ Textos legibles
- ✅ Contraste adecuado

---

## 🧪 Casos de Prueba

### Caso 1: Cliente nuevo accede a informes
1. Servicio cerrado → Cliente puede ver dashboards
2. Cliente intenta descargar PDF → Aparece modal
3. Cliente completa encuesta → Puede descargar

### Caso 2: Cliente ya completó encuesta
1. Cliente accede a informes → Acceso directo
2. Cliente descarga PDF → Descarga inmediata

### Caso 3: Consultor accede a informes
1. Puede ver en cualquier estado
2. Puede descargar sin encuesta
3. Puede ver resultados de encuestas

### Caso 4: Validaciones
1. Intentar enviar sin responder todas las preguntas → Error
2. Responder con valores fuera de rango (1-5) → Error
3. Comentarios > 5000 caracteres → Error

---

## 📝 Notas Importantes

1. **Una encuesta por servicio**: El sistema permite solo UNA encuesta por servicio.

2. **No afecta a consultores**: Los consultores pueden descargar sin restricciones.

3. **Experiencia completa primero**: El cliente experimenta TODO el sistema antes de evaluar (dashboards, segmentadores, informes ejecutivos, recomendaciones).

4. **Migración pendiente**: Recuerda ejecutar `php spark migrate` cuando tengas internet estable.

5. **Estadísticas disponibles**: Los métodos de estadísticas ya están implementados para futuros dashboards de análisis de satisfacción.

---

## 🔮 Mejoras Futuras (Opcionales)

- Dashboard de análisis de satisfacción para admin
- Gráficos de tendencias por empresa
- Comparativas entre servicios
- Alertas si satisfacción < 3.0
- Exportar estadísticas a Excel
- Net Promoter Score (NPS) calculado automáticamente

---

✅ **Sistema completamente implementado y listo para usar después de ejecutar la migración.**
