# PROCESO DIAMANTE - AUDITORÍA DE BAREMOS
## Sistema de Auditoría Exhaustiva para Riesgo Psicosocial

**Fecha creación:** 2025-11-24
**Versión:** 1.0
**Objetivo:** Garantizar 100% conformidad con manuales oficiales del Ministerio de la Protección Social de Colombia

---

## 🔷 FASE 1: PREPARACIÓN (Base del Diamante)

### 1.1 Identificar Material Oficial
- [ ] Obtener manuales oficiales actualizados (PDF/físico)
- [ ] Identificar todas las tablas de baremos
- [ ] Listar tablas a auditar:
  - Tabla 29: Dimensiones Forma A
  - Tabla 30: Dimensiones Forma B
  - Tabla 31: Dominios (A y B)
  - Tabla 32: Dominios por tipo de cargo
  - Tabla 33: Total Intralaboral (A y B)
  - Otras tablas relevantes

### 1.2 Mapear Arquitectura del Sistema
- [ ] Identificar TODAS las capas del sistema:
  - **Libraries** (fuente de verdad)
  - **Controllers** (lógica de negocio)
  - **Views** (presentación)
  - **Models** (si tienen lógica)
  - **Helpers** (funciones auxiliares)

### 1.3 Búsqueda Exhaustiva de Baremos
```bash
# Buscar TODAS las referencias a baremos en el código
grep -r "sin_riesgo\|riesgo_bajo\|riesgo_medio\|riesgo_alto\|riesgo_muy_alto" app/
grep -r "liderazgo\|control\|demandas\|recompensas" app/
```

- [ ] Listar TODOS los archivos que contienen baremos
- [ ] Clasificar por tipo (Library/Controller/View)
- [ ] Priorizar por criticidad

---

## 🔷 FASE 2: AUDITORÍA POR DIMENSIÓN (Lado Izquierdo del Diamante)

### 2.1 Proceso por Dimensión/Dominio

**Para CADA dimensión/dominio del manual:**

#### Paso 1: Extraer Baremos Oficiales
```
Dimensión: [NOMBRE]
Forma: [A/B]
Tabla: [NÚMERO]

Baremos oficiales:
- Sin riesgo:     [X.X - Y.Y]
- Riesgo bajo:    [X.X - Y.Y]
- Riesgo medio:   [X.X - Y.Y]
- Riesgo alto:    [X.X - Y.Y]
- Riesgo muy alto: [X.X - Y.Y]
```

#### Paso 2: Buscar en TODOS los Archivos
```bash
# Ejemplo para "Características del liderazgo"
grep -n "caracteristicas_liderazgo\|liderazgo" app/**/*.php
```

- [ ] Listar TODAS las ubicaciones donde aparece
- [ ] Anotar archivo y número de línea

#### Paso 3: Auditar Cada Ubicación
Para cada ubicación encontrada:

**Archivo:** [ruta]
**Línea:** [número]
**Método/Función:** [nombre]

```php
// Código encontrado
'nombre_dimension' => [
    'sin_riesgo' => [X.X, Y.Y],
    // ...
]
```

**Comparación:**
| Nivel | Código Actual | Manual Oficial | Estado |
|-------|---------------|----------------|--------|
| Sin riesgo | [X-Y] | [X-Y] | ✅/❌ |
| Riesgo bajo | [X-Y] | [X-Y] | ✅/❌ |
| Riesgo medio | [X-Y] | [X-Y] | ✅/❌ |
| Riesgo alto | [X-Y] | [X-Y] | ✅/❌ |
| Riesgo muy alto | [X-Y] | [X-Y] | ✅/❌ |

**Errores encontrados:** [#]
**Acción:** [Corregir/Ya conforme]

#### Paso 4: Corrección Inmediata
Si hay errores:
- [ ] Aplicar corrección
- [ ] Verificar sintaxis PHP
- [ ] Documentar cambio
- [ ] Marcar como ✅ CORREGIDO

#### Paso 5: Validación
- [ ] Re-leer el código corregido
- [ ] Confirmar 100% conformidad
- [ ] Pasar a siguiente dimensión

---

## 🔷 FASE 3: AUDITORÍA POR ARCHIVO (Lado Derecho del Diamante)

### 3.1 Proceso por Archivo

**Para CADA archivo identificado:**

#### Checklist por Archivo

**Archivo:** `app/Controllers/ReportsController.php`

- [ ] **Ubicar TODOS los arrays de baremos**
  - Buscar: `$baremo`, `'sin_riesgo'`, `'riesgo_bajo'`
  - Listar líneas: [lista]

- [ ] **Identificar qué método los usa**
  - Método 1: [nombre] - Línea [X]
  - Método 2: [nombre] - Línea [Y]

- [ ] **Determinar para qué Forma aplica**
  - ¿Es Forma A, Forma B, o ambas?
  - Buscar comentarios o lógica condicional

- [ ] **Auditar CADA array encontrado**
  - Array 1 (línea X): [status]
  - Array 2 (línea Y): [status]

- [ ] **Verificar consistencia**
  - ¿Hay duplicación de baremos?
  - ¿Están sincronizados?

- [ ] **Estado final del archivo**
  - Errores encontrados: [#]
  - Errores corregidos: [#]
  - Estado: ✅ 100% CONFORME

---

## 🔷 FASE 4: VALIDACIÓN CRUZADA (Punta Superior del Diamante)

### 4.1 Verificación de Consistencia

#### Entre Libraries y Controllers
```
Dimensión: [nombre]
- Library (IntralaboralAScoring.php): [baremos]
- Controller (ReportsController.php): [baremos]
- ¿Coinciden? [SÍ/NO]
```

- [ ] Comparar baremos entre capas
- [ ] Identificar inconsistencias
- [ ] Resolver conflictos (¿cuál es correcto?)

#### Entre Múltiples Controllers
- [ ] ¿Hay varios controllers con baremos duplicados?
- [ ] ¿Están todos sincronizados?
- [ ] Centralizar si es posible

### 4.2 Prueba con URLs Reales

**Lista de URLs a probar:**

1. [ ] `http://localhost/psyrisk/reports/intralaboral-a/1`
2. [ ] `http://localhost/psyrisk/reports/intralaboral-b/1`
3. [ ] `http://localhost/psyrisk/reports/heatmap/1`
4. [ ] `http://localhost/psyrisk/workers/results/14`
5. [ ] `http://localhost/psyrisk/workers/results/16`

**Para cada URL:**
- Tomar screenshot
- Verificar rangos mostrados vs manual oficial
- Anotar discrepancias
- Corregir si es necesario

---

## 🔷 FASE 5: DOCUMENTACIÓN (Base Inferior del Diamante)

### 5.1 Registro de Errores

**Plantilla por Error:**

```markdown
### Error #[N]: [Nombre Dimensión/Dominio]

**Archivo:** [ruta]
**Línea:** [número]
**Método:** [nombre]
**Forma:** [A/B]

**Baremo Incorrecto:**
```php
'dimension' => [
    'sin_riesgo' => [0.0, X.X],  // ❌ Incorrecto
    // ...
]
```

**Baremo Correcto (Manual Oficial - Tabla [N]):**
```php
'dimension' => [
    'sin_riesgo' => [0.0, Y.Y],  // ✅ Correcto
    // ...
]
```

**Impacto:**
- Afecta a: [listado de funcionalidades]
- Severidad: [Crítica/Alta/Media/Baja]

**Corrección Aplicada:** ✅ [Fecha]
```

### 5.2 Informe Final de Auditoría

```markdown
# INFORME DE AUDITORÍA - [NOMBRE CUESTIONARIO]

**Fecha:** [fecha]
**Auditor:** [nombre]
**Alcance:** [descripción]

## Resumen Ejecutivo

- **Total archivos auditados:** [#]
- **Total dimensiones/dominios:** [#]
- **Errores encontrados:** [#]
- **Errores corregidos:** [#]
- **Conformidad final:** [%]

## Detalle por Archivo

### [Archivo 1]
- Errores: [#]
- Correcciones: [lista]
- Estado: ✅/❌

[Repetir para cada archivo]

## Detalle por Dimensión

### [Dimensión 1]
- Tabla oficial: [#]
- Ubicaciones encontradas: [#]
- Errores: [#]
- Estado: ✅/❌

[Repetir para cada dimensión]

## Conclusión

[Texto narrativo del estado final]
```

---

## 🔷 FASE 6: VERIFICACIÓN FINAL (Cierre del Diamante)

### 6.1 Checklist Final

- [ ] **Todas las dimensiones auditadas**
  - Forma A: [#/19] ✅
  - Forma B: [#/16] ✅

- [ ] **Todos los dominios auditados**
  - Total: [#/4] ✅

- [ ] **Todos los archivos auditados**
  - Libraries: [#] ✅
  - Controllers: [#] ✅
  - Views: [#] ✅

- [ ] **Todas las URLs probadas**
  - URLs funcionando: [#] ✅

- [ ] **Documentación completa**
  - Informe generado: ✅
  - Errores documentados: ✅
  - Correcciones registradas: ✅

### 6.2 Certificación

```
CERTIFICO QUE:

El sistema [NOMBRE] ha sido auditado exhaustivamente contra los
manuales oficiales del Ministerio de la Protección Social de
Colombia.

Conformidad alcanzada: [X]%

Fecha: [fecha]
Auditor: [nombre]
```

---

## 📋 PLANTILLA DE TRABAJO - USO PRÁCTICO

### Template para Auditar Forma A - Dimensiones

```markdown
## AUDITORÍA: FORMA A - DIMENSIONES (TABLA 29)

### Dimensión 1: Características del liderazgo

**Baremos oficiales (Tabla 29):**
- Sin riesgo: 0.0 - 3.8
- Riesgo bajo: 3.9 - 15.4
- Riesgo medio: 15.5 - 30.8
- Riesgo alto: 30.9 - 46.2
- Riesgo muy alto: 46.3 - 100

**Ubicaciones en código:**

1. `IntralaboralAScoring.php:150`
   - Estado: ✅ CONFORME

2. `ReportsController.php:1509`
   - Estado: ✅ CONFORME

3. `ReportsController.php:1927`
   - Estado: ❌ INCORRECTO → ✅ CORREGIDO

**Total errores:** 1
**Total ubicaciones:** 3
**Conformidad:** 100%

---

### Dimensión 2: Relaciones sociales en el trabajo

[Repetir estructura]

---

[Continuar para las 19 dimensiones]
```

---

## 🎯 MÉTRICAS DE ÉXITO

### Indicadores de Calidad

- **Cobertura:** 100% de dimensiones auditadas
- **Precisión:** 0 errores remanentes
- **Trazabilidad:** Cada corrección documentada
- **Reproducibilidad:** Proceso replicable por otro auditor

### Criterios de Aceptación

✅ **APROBADO** si:
- Todas las dimensiones: 100% conforme
- Todas las URLs: Muestran rangos correctos
- Todas las ubicaciones: Sincronizadas
- Documentación: Completa

❌ **RECHAZADO** si:
- Queda 1 o más errores sin corregir
- Hay inconsistencias entre archivos
- Documentación incompleta

---

## 📌 NOTAS IMPORTANTES

### Errores Comunes a Evitar

1. ❌ **No auditar TODAS las ubicaciones** de una dimensión
   - Buscar en Libraries, Controllers Y Views

2. ❌ **Corregir un archivo y olvidar otros** con mismos baremos
   - Siempre buscar duplicados

3. ❌ **No verificar qué método usa cada array**
   - Un método puede estar obsoleto y no afectar

4. ❌ **Confundir Forma A con Forma B**
   - SIEMPRE verificar comentarios y contexto

5. ❌ **No probar las URLs después de corregir**
   - La prueba final es visual en navegador

### Buenas Prácticas

✅ **Auditar dimensión por dimensión** (no archivo por archivo)
✅ **Documentar mientras auditas** (no al final)
✅ **Corregir inmediatamente** al encontrar error
✅ **Verificar visualmente** después de cada corrección
✅ **Mantener lista de chequeo** actualizada

---

## 🔄 PROCESO ITERATIVO

Si se encuentra un error después de "terminar":

1. **NO pánico** - Es normal en auditorías complejas
2. **Agregar a lista de errores** con toda la info
3. **Re-ejecutar FASE 2** para esa dimensión específica
4. **Re-ejecutar FASE 4** para validar consistencia
5. **Actualizar documentación**
6. **Re-certificar**

---

**Elaborado por:** Usuario + Claude
**Fecha:** 2025-11-24
**Versión:** 1.0
**Estado:** ✅ Listo para usar
