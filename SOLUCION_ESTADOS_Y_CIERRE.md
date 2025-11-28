# 🔒 SOLUCIÓN: Estados de Trabajadores y Cierre de Servicio

## 🎯 PROBLEMA IDENTIFICADO

### Situación Real:
1. Se cargan 30 trabajadores en el CSV
2. Solo 20 completan la batería
3. Los otros 10 no participan por:
   - 🏥 Incapacidad
   - 📅 Vacaciones
   - 🚫 Licencia/Calamidad
   - 😴 No quisieron/abandonaron
   - ⏳ Quedaron en proceso (no terminaron)

### Necesidad:
- El **consultor** debe poder **cerrar el servicio** aunque no todos participen
- El **cliente** solo debe ver informes cuando el servicio esté **cerrado**
- Debe haber claridad de quién participó y quién no
- Los que quedaron en proceso deben tener un manejo especial

---

## 💡 SOLUCIÓN PROPUESTA

### 1️⃣ ESTADOS DE TRABAJADORES (`workers.status`)

Actualmente existe el campo `status` en la tabla `workers`. Propongo estos estados:

```sql
-- Estados posibles en workers.status
'pendiente'           -- Cargado pero no ha iniciado (email no enviado o no abierto)
'invitado'            -- Email enviado, no ha iniciado
'en_proceso'          -- Inició pero no completó
'completado'          -- Completó toda la batería
'no_participo'        -- Marcado manualmente por consultor (incapacidad, vacaciones, etc.)
'abandonado'          -- En proceso pero cerrado sin completar
```

### 2️⃣ ESTADOS DE SERVICIO (`battery_services.status`)

Actualmente existe el campo `status`. Propongo estos estados:

```sql
-- Estados posibles en battery_services.status
'en_curso'            -- Servicio activo, recolectando datos
'revision'            -- Consultor está revisando antes de cerrar
'cerrado'             -- Servicio finalizado, cliente puede ver informes
'cancelado'           -- Servicio cancelado
```

### 3️⃣ NUEVOS CAMPOS EN `battery_services`

```sql
ALTER TABLE battery_services
ADD COLUMN closed_at DATETIME NULL COMMENT 'Fecha de cierre del servicio',
ADD COLUMN closed_by INT NULL COMMENT 'ID del usuario que cerró',
ADD COLUMN closure_notes TEXT NULL COMMENT 'Notas del cierre';
```

---

## 🔄 FLUJO DE TRABAJO PROPUESTO

### FASE 1: Carga y Aplicación (Estado: `en_curso`)
1. Consultor carga CSV de 30 trabajadores → Status: `pendiente`
2. Consultor envía emails → Status: `invitado`
3. Trabajadores empiezan → Status: `en_proceso`
4. Trabajadores completan → Status: `completado`

### FASE 2: Revisión (Estado: `revision`)
Cuando el consultor decide cerrar el servicio:

1. **Vista de Pre-Cierre** muestra:
   ```
   📊 Resumen del Servicio:
   ✅ Completados:        20 trabajadores (66%)
   ⏳ En Proceso:         5 trabajadores  (16%)
   📧 Invitados:          3 trabajadores  (10%)
   ❓ Pendientes:         2 trabajadores  (6%)

   ⚠️ Acciones Requeridas:
   - Debes gestionar 10 trabajadores sin completar
   ```

2. **Modal de Gestión de Participantes**:
   ```
   Por cada trabajador sin completar, el consultor debe seleccionar:

   [ ] Juan Pérez - En Proceso
       ⚪ Marcar como "No Participó"
          Motivo: [Incapacidad ▼] [Vacaciones] [Licencia] [Otro]
          Nota: [___________________]

       ⚪ Marcar como "Abandonado"
          (Inició pero no quiso terminar)

       ⚪ Mantener en proceso
          (Darle más tiempo para completar)

   [Guardar Cambios]
   ```

3. **Reglas de Cierre**:
   - Mínimo 50% de participación completada (configurable)
   - Todos los trabajadores deben tener un estado definitivo
   - Consultor puede agregar notas de cierre

### FASE 3: Cierre (Estado: `cerrado`)
Una vez cerrado:
1. ✅ **Cliente** puede ver informes
2. 🔒 **No se pueden agregar** más trabajadores
3. 🔒 **No se pueden eliminar** trabajadores completados
4. ✅ Los trabajadores en proceso pueden seguir completando (opcional)
5. ✅ Se genera un reporte de participación

---

## 📊 VISTAS Y FUNCIONALIDADES A CREAR

### 1. Vista de Pre-Cierre para Consultor

**Ruta**: `/workers/service/{id}/pre-close`

```php
// WorkerController.php
public function preClose($serviceId)
{
    // Verificar que sea consultor
    // Obtener estadísticas de participación
    // Mostrar trabajadores sin completar
    // Permitir asignar estados

    return view('workers/pre_close', $data);
}
```

**Elementos de la Vista**:
- 📊 Dashboard de participación
- 📋 Lista de trabajadores sin completar con opciones
- 📝 Campo para notas de cierre
- ⚠️ Validaciones y advertencias
- 🔒 Botón "Cerrar Servicio"

### 2. Modal de Confirmación de Cierre

```
┌─────────────────────────────────────────────────┐
│  ⚠️ Confirmar Cierre de Servicio                │
├─────────────────────────────────────────────────┤
│                                                 │
│  Estás a punto de cerrar el servicio:          │
│  "Batería Psicosocial - Empresa XYZ"           │
│                                                 │
│  📊 Resumen Final:                              │
│  ✅ Completados: 20 (66%)                       │
│  🚫 No Participaron: 8 (27%)                    │
│  ❌ Abandonados: 2 (7%)                         │
│                                                 │
│  ⚠️ Al cerrar el servicio:                      │
│  • El cliente podrá ver los informes           │
│  • No podrás agregar más trabajadores          │
│  • Solo se calcularán los completados          │
│                                                 │
│  Notas de cierre (opcional):                   │
│  [________________________________]             │
│                                                 │
│  [Cancelar]  [✓ Confirmar Cierre]              │
└─────────────────────────────────────────────────┘
```

### 3. Modificación a Vista de Informes

**En ReportsController.php**:

```php
private function checkAccess($serviceId)
{
    // ... código existente ...

    // Si es cliente, verificar que servicio esté cerrado
    if (in_array($userRole, ['cliente_empresa', 'cliente_gestor'])) {
        if ($service['status'] !== 'cerrado') {
            return redirect()->to('/dashboard')
                ->with('warning', 'Los informes estarán disponibles cuando el consultor finalice el servicio');
        }
    }

    // ... resto del código ...
}
```

### 4. Vista de "Servicio en Proceso" para Cliente

Cuando el cliente intenta acceder pero el servicio no está cerrado:

```
┌─────────────────────────────────────────────────┐
│  ⏳ Servicio en Proceso                         │
├─────────────────────────────────────────────────┤
│                                                 │
│  El servicio "Batería Psicosocial"            │
│  se encuentra actualmente en proceso.          │
│                                                 │
│  📊 Progreso Actual:                            │
│  ▓▓▓▓▓▓▓▓▓▓▓░░░░  67% completado               │
│                                                 │
│  20 de 30 trabajadores han completado          │
│  la batería de riesgo psicosocial.             │
│                                                 │
│  Los informes estarán disponibles cuando       │
│  el consultor finalice la recolección.         │
│                                                 │
│  📅 Fecha estimada: 25/11/2025                  │
│                                                 │
│  [← Volver al Dashboard]                       │
└─────────────────────────────────────────────────┘
```

---

## 📋 TABLA DE DECISIONES POR ESTADO

| Estado Worker | ¿Se calcula? | ¿Se muestra en tabla? | ¿Aparece en informe? | Color Badge |
|---------------|--------------|----------------------|---------------------|-------------|
| `completado` | ✅ Sí | ✅ Sí | ✅ Sí | 🟢 Verde |
| `en_proceso` (servicio abierto) | ❌ No | ✅ Sí | ❌ No | 🟡 Amarillo |
| `abandonado` | ❌ No | ✅ Sí (separado) | ⚠️ En sección aparte | 🔴 Rojo |
| `no_participo` | ❌ No | ✅ Sí (separado) | 📝 En notas | ⚪ Gris |
| `invitado` | ❌ No | ✅ Sí | ❌ No | 🔵 Azul |
| `pendiente` | ❌ No | ✅ Sí | ❌ No | ⚫ Gris oscuro |

---

## 🎨 MODIFICACIONES AL DASHBOARD DE TRABAJADORES

### Vista Actual de Trabajadores (`workers/index.php`)

Agregar sección de control de cierre:

```html
<!-- Si el servicio está en_curso -->
<div class="card border-warning mb-3">
    <div class="card-header bg-warning text-dark">
        <i class="fas fa-hourglass-half me-2"></i>
        Servicio en Proceso
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <div class="stat-box">
                    <h3>20</h3>
                    <p>Completados</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box">
                    <h3>5</h3>
                    <p>En Proceso</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box">
                    <h3>3</h3>
                    <p>Invitados</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box">
                    <h3>2</h3>
                    <p>Pendientes</p>
                </div>
            </div>
        </div>

        <div class="text-end mt-3">
            <button class="btn btn-primary" onclick="location.href='<?= base_url('workers/service/'.$serviceId.'/pre-close') ?>'">
                <i class="fas fa-check-circle me-2"></i>
                Gestionar Cierre de Servicio
            </button>
        </div>
    </div>
</div>
```

---

## 🗄️ ESTRUCTURA DE BASE DE DATOS

### Modificaciones Necesarias:

```sql
-- 1. Agregar campos a battery_services
ALTER TABLE battery_services
ADD COLUMN closed_at DATETIME NULL COMMENT 'Fecha de cierre',
ADD COLUMN closed_by INT NULL COMMENT 'Usuario que cerró',
ADD COLUMN closure_notes TEXT NULL COMMENT 'Notas del cierre',
ADD COLUMN min_participation_percent INT DEFAULT 50 COMMENT 'Mínimo % de participación';

-- 2. Modificar allowedFields en BatteryServiceModel.php
'closed_at', 'closed_by', 'closure_notes', 'min_participation_percent'

-- 3. Agregar campo de motivo en workers
ALTER TABLE workers
ADD COLUMN non_participation_reason VARCHAR(100) NULL COMMENT 'Motivo de no participación',
ADD COLUMN non_participation_notes TEXT NULL COMMENT 'Notas adicionales';

-- 4. Modificar allowedFields en WorkerModel.php
'non_participation_reason', 'non_participation_notes'
```

---

## 🔧 CÓDIGO A IMPLEMENTAR

### 1. Método de Pre-Cierre en WorkerController

```php
public function preClose($serviceId)
{
    // Verificar permisos (solo consultor)
    if (session()->get('role') !== 'consultor') {
        return redirect()->to('/dashboard')->with('error', 'No autorizado');
    }

    $service = $this->batteryServiceModel->find($serviceId);

    if ($service['status'] === 'cerrado') {
        return redirect()->to('/workers/service/'.$serviceId)
            ->with('info', 'Este servicio ya está cerrado');
    }

    // Obtener estadísticas
    $workers = $this->workerModel->where('battery_service_id', $serviceId)->findAll();

    $stats = [
        'total' => count($workers),
        'completados' => count(array_filter($workers, fn($w) => $w['status'] === 'completado')),
        'en_proceso' => count(array_filter($workers, fn($w) => $w['status'] === 'en_proceso')),
        'invitados' => count(array_filter($workers, fn($w) => $w['status'] === 'invitado')),
        'pendientes' => count(array_filter($workers, fn($w) => $w['status'] === 'pendiente')),
        'no_participo' => count(array_filter($workers, fn($w) => $w['status'] === 'no_participo')),
        'abandonados' => count(array_filter($workers, fn($w) => $w['status'] === 'abandonado'))
    ];

    $stats['percent_completado'] = ($stats['total'] > 0)
        ? round(($stats['completados'] / $stats['total']) * 100, 2)
        : 0;

    // Trabajadores que necesitan gestión
    $pendingManagement = array_filter($workers, function($w) {
        return in_array($w['status'], ['en_proceso', 'invitado', 'pendiente']);
    });

    $data = [
        'service' => $service,
        'stats' => $stats,
        'pendingManagement' => $pendingManagement,
        'minPercent' => $service['min_participation_percent'] ?? 50
    ];

    return view('workers/pre_close', $data);
}
```

### 2. Método de Actualización Masiva de Estados

```php
public function updateWorkerStatuses($serviceId)
{
    // Recibir datos del formulario
    $updates = $this->request->getPost('worker_updates'); // Array de worker_id => [status, reason, notes]

    foreach ($updates as $workerId => $data) {
        $this->workerModel->update($workerId, [
            'status' => $data['status'],
            'non_participation_reason' => $data['reason'] ?? null,
            'non_participation_notes' => $data['notes'] ?? null
        ]);
    }

    return redirect()->to('/workers/service/'.$serviceId.'/pre-close')
        ->with('success', 'Estados actualizados correctamente');
}
```

### 3. Método de Cierre Final

```php
public function closeService($serviceId)
{
    $service = $this->batteryServiceModel->find($serviceId);
    $workers = $this->workerModel->where('battery_service_id', $serviceId)->findAll();

    // Verificar que todos los trabajadores tengan un estado definitivo
    $pendingStates = array_filter($workers, fn($w) =>
        in_array($w['status'], ['pendiente', 'invitado', 'en_proceso'])
    );

    if (!empty($pendingStates)) {
        return redirect()->back()->with('error',
            'Debes gestionar todos los trabajadores pendientes antes de cerrar'
        );
    }

    // Verificar porcentaje mínimo
    $completados = count(array_filter($workers, fn($w) => $w['status'] === 'completado'));
    $percent = ($completados / count($workers)) * 100;
    $minPercent = $service['min_participation_percent'] ?? 50;

    if ($percent < $minPercent) {
        return redirect()->back()->with('error',
            "Porcentaje de participación ($percent%) menor al mínimo requerido ($minPercent%)"
        );
    }

    // Cerrar servicio
    $this->batteryServiceModel->update($serviceId, [
        'status' => 'cerrado',
        'closed_at' => date('Y-m-d H:i:s'),
        'closed_by' => session()->get('user_id'),
        'closure_notes' => $this->request->getPost('closure_notes')
    ]);

    return redirect()->to('/workers/service/'.$serviceId)
        ->with('success', 'Servicio cerrado exitosamente. El cliente ya puede ver los informes.');
}
```

---

## 📊 IMPACTO EN LOS INFORMES

### Modificación en Cálculo de Estadísticas

Solo se deben calcular trabajadores con `status = 'completado'`:

```php
// En ReportsController.php
$results = $this->calculatedResultModel
    ->select('calculated_results.*, workers.status')
    ->join('workers', 'workers.id = calculated_results.worker_id')
    ->where('battery_service_id', $serviceId)
    ->where('workers.status', 'completado') // ← FILTRO CRUCIAL
    ->findAll();
```

### Sección Adicional en Informes

Agregar al final del informe ejecutivo:

```html
<div class="card mt-4">
    <div class="card-header bg-light">
        <h6>📋 Nota sobre Participación</h6>
    </div>
    <div class="card-body">
        <p>
            <strong>Total de trabajadores invitados:</strong> 30<br>
            <strong>Trabajadores que completaron:</strong> 20 (66%)<br>
            <strong>No participaron:</strong> 8 (27%) - Motivos: Incapacidad, Vacaciones<br>
            <strong>Abandonaron:</strong> 2 (7%)
        </p>
        <p class="text-muted small mb-0">
            Los resultados de este informe se basan únicamente en los 20 trabajadores
            que completaron la batería de riesgo psicosocial.
        </p>
    </div>
</div>
```

---

## ✅ RESUMEN DE LA SOLUCIÓN

### Para el CONSULTOR:
1. ✅ Puede ver progreso en tiempo real
2. ✅ Gestiona trabajadores que no completan
3. ✅ Asigna motivos de no participación
4. ✅ Cierra el servicio cuando está listo
5. ✅ Mantiene control total del proceso

### Para el CLIENTE:
1. ✅ Ve progreso mientras está en curso
2. ✅ **SOLO** ve informes cuando está **cerrado**
3. ✅ Entiende claramente quiénes participaron
4. ✅ Tiene claridad de los datos del informe

### Para el SISTEMA:
1. ✅ Estados claros y trazabilidad
2. ✅ Validaciones de calidad de datos
3. ✅ Informes basados solo en completados
4. ✅ Histórico de motivos de no participación

---

## 🚀 PRÓXIMOS PASOS DE IMPLEMENTACIÓN

1. ✅ Crear migración para nuevos campos
2. ✅ Actualizar modelos (WorkerModel, BatteryServiceModel)
3. ✅ Crear vista `workers/pre_close.php`
4. ✅ Crear métodos en WorkerController
5. ✅ Modificar checkAccess en ReportsController
6. ✅ Crear vista de "Servicio en Proceso" para cliente
7. ✅ Agregar sección de participación en informes
8. ✅ Testing completo del flujo

---

**¿Quieres que implemente esta solución completa?**
