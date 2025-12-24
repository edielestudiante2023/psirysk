# Prueba de Consentimiento Informado

## Implementación Completada

Se ha implementado exitosamente el **Consentimiento Informado** antes del cuestionario sociodemográfico.

### Cambios Realizados

#### 1. Base de Datos
- ✅ Migración creada: `2025-12-23-000001_AddConsentToWorkerDemographics.php`
- ✅ Campos agregados a tabla `worker_demographics`:
  - `consent_accepted` (BOOLEAN)
  - `consent_accepted_at` (DATETIME)

#### 2. Modelo
- ✅ Actualizado `WorkerDemographicsModel.php` con los nuevos campos en `$allowedFields`

#### 3. Vista
- ✅ Creada `app/Views/assessment/informed_consent.php`
  - Diseño responsive y moderno
  - Texto completo del consentimiento informado
  - Botón único: "SÍ, Acepto"
  - Guardado AJAX con SweetAlert2

#### 4. Controlador
- ✅ Métodos agregados a `AssessmentController.php`:
  - `informedConsent()` - Muestra la vista del consentimiento
  - `acceptConsent()` - Procesa la aceptación vía POST/JSON
- ✅ Modificado `redirectToCurrentForm()` para verificar consentimiento primero

#### 5. Rutas
- ✅ Agregadas en `Config/Routes.php`:
  - `GET  /assessment/informed-consent`
  - `POST /assessment/accept-consent`

### Flujo Implementado

```
1. Trabajador recibe email con enlace: /assessment/{token}

2. AssessmentController::index($token)
   └─> Valida token y fecha de expiración
   └─> Crea sesión
   └─> Llama a redirectToCurrentForm()

3. redirectToCurrentForm()
   └─> Verifica si consent_accepted = true
       ├─> NO  → Redirige a /assessment/informed-consent
       └─> SÍ  → Verifica si datos generales completados
                 ├─> NO  → /assessment/general-data
                 └─> SÍ  → Verifica intralaboral, extralaboral, estrés

4. Vista informed_consent.php
   └─> Muestra texto del consentimiento
   └─> Botón "SÍ, Acepto"

5. Usuario hace click en "SÍ, Acepto"
   └─> POST /assessment/accept-consent (AJAX)
   └─> Guarda consent_accepted = true y consent_accepted_at = NOW()
   └─> Retorna JSON con redirect a /assessment/general-data

6. Continúa flujo normal:
   └─> Datos Generales → Intralaboral → Extralaboral → Estrés → Completado
```

### Cómo Probar

#### Trabajador de Prueba Reseteado
- **ID:** 13
- **Nombre:** ROSA ANGELICA SACRISTAN BARRETO
- **Email:** rosa.sacristan@empresa.com
- **Estado:** pendiente
- **Consentimiento:** NULL (reseteado para prueba)

#### URL de Prueba
```
http://localhost/psyrisk/public/assessment/20eabe4ead4b4dad5adcc9d75cb6775f149826b2bca7496579fa6d833a9c94c3
```

#### Pasos de Prueba

1. **Acceder a la URL del trabajador:**
   - Abrir en navegador la URL de arriba
   - Debe redirigir automáticamente a `/assessment/informed-consent`

2. **Verificar pantalla de consentimiento:**
   - ✅ Logo de escudo de seguridad
   - ✅ Título "Consentimiento Informado"
   - ✅ Texto completo con normativa (Resoluciones 2764/2022, 2646/2008, 2404/2019)
   - ✅ Información sobre los 4 cuestionarios
   - ✅ Protección de datos (Ley 1090/2006, Ley 1581/2012)
   - ✅ Botón "SÍ, Acepto" visible

3. **Aceptar consentimiento:**
   - Click en "SÍ, Acepto"
   - Debe mostrar mensaje: "¡Gracias! Su consentimiento ha sido registrado"
   - Redirige automáticamente a `/assessment/general-data`

4. **Verificar en base de datos:**
   ```sql
   SELECT worker_id, consent_accepted, consent_accepted_at
   FROM worker_demographics
   WHERE worker_id = 13;
   ```
   Resultado esperado:
   - `consent_accepted` = 1
   - `consent_accepted_at` = fecha y hora actual

5. **Verificar protección:**
   - Intentar acceder nuevamente con el token
   - NO debe volver a mostrar el consentimiento
   - Debe redirigir directamente al formulario correspondiente

### Diseño de la Vista

- **Colores:** Gradiente púrpura (#667eea → #764ba2)
- **Responsive:** Optimizado para móviles y desktop
- **Animaciones:** Entrada suave (fadeInUp)
- **UX:**
  - Botón deshabilitado durante el envío
  - Spinner de carga
  - Mensajes de éxito/error con SweetAlert2
  - No permite continuar sin aceptar

### Texto del Consentimiento Informado

El texto incluido cubre:
1. Objetivo de la evaluación
2. Marco legal (Resoluciones del Ministerio de Trabajo)
3. Definición de factores de riesgo psicosocial
4. Descripción de los 4 cuestionarios
5. Proceso de tabulación y análisis
6. Protección de datos personales
7. Reserva de información
8. Uso exclusivo para SST

### Archivos Modificados/Creados

```
✅ app/Database/Migrations/2025-12-23-000001_AddConsentToWorkerDemographics.php (NUEVO)
✅ app/Views/assessment/informed_consent.php (NUEVO)
✅ app/Controllers/AssessmentController.php (MODIFICADO)
✅ app/Models/WorkerDemographicsModel.php (MODIFICADO)
✅ app/Config/Routes.php (MODIFICADO)
```

### Validaciones Implementadas

1. **Sesión válida:** Verifica que exista `assessment_worker_id` en sesión
2. **Consentimiento único:** Si ya fue aceptado, redirige al siguiente formulario
3. **Guardado transaccional:** Try-catch para manejo de errores
4. **Timestamp:** Registra fecha y hora exacta de aceptación
5. **Protección de flujo:** No permite acceder a formularios sin aceptar consentimiento

---

## Estado: ✅ IMPLEMENTACIÓN COMPLETADA

La funcionalidad está lista para probar. El consentimiento informado se muestra ANTES del cuestionario de datos sociodemográficos y es OBLIGATORIO para continuar con la evaluación.
