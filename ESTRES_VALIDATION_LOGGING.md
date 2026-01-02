# Sistema de Logging para Validación de Estrés

## 📍 Ubicación de Logs

Los logs se guardan automáticamente en:

```
c:\xampp\htdocs\psyrisk\writable\logs\log-YYYY-MM-DD.php
```

Donde `YYYY-MM-DD` es la fecha actual (ej: `log-2026-01-01.php`)

## 🔍 Cómo Ver los Logs

### Opción 1: Ver en tiempo real (Windows CMD)
```cmd
cd c:\xampp\htdocs\psyrisk\writable\logs
more log-2026-01-01.php
```

### Opción 2: Ver las últimas líneas
```cmd
powershell -Command "Get-Content 'c:\xampp\htdocs\psyrisk\writable\logs\log-2026-01-01.php' -Tail 100"
```

### Opción 3: Abrir con editor de texto
Simplemente abrir el archivo con Notepad++, VS Code, o cualquier editor.

## 🧪 Cómo Probar el Sistema de Logging

### Paso 1: Limpiar logs anteriores (opcional)
```cmd
del c:\xampp\htdocs\psyrisk\writable\logs\log-*.php
```

### Paso 2: Ejecutar validación desde navegador

1. Ir a: `http://localhost/psyrisk/validation/6` (donde 6 es tu serviceId)

2. Hacer clic en el botón **"Re-procesar Total"** para **Forma A**

3. Esperar a que redirija

4. Hacer clic en el botón **"Re-procesar Total"** para **Forma B**

5. Esperar a que redirija

### Paso 3: Revisar los logs

Abrir el archivo de log del día actual y buscar las secciones:

```
========================================
INICIO PROCESAMIENTO ESTRÉS - Servicio: 6, Forma: A
========================================
```

## 📊 Qué Información Contienen los Logs

### 1. **Información de Inicio**
- Servicio ID y nombre
- Forma (A o B)
- Validación del tipo de formulario

### 2. **Workers Encontrados**
- Total de workers completados
- Total de workers con respuestas de estrés
- Lista detallada de workers (con nivel DEBUG)

### 3. **Cálculo por Bloques** (el más importante)

Para cada bloque de ítems (1-8, 9-12, 13-22, 23-31):

```
--- BLOQUE 1: Ítems 1-8 (Factor ×4) ---
  Ítem 1: responses=23, subtotal=138, promedio=6.0000
    Raw values sample (primeros 5): [6, 6, 6, 6, 6]
    Scored values sample (primeros 5): [6, 6, 6, 6, 6]
  Ítem 2: responses=23, subtotal=138, promedio=6.0000
  Ítem 3: responses=23, subtotal=69, promedio=3.0000
  Suma de promedios: 48.0000
  Cantidad ítems: 8
  Promedio del bloque: 6.0000
  Promedio × Factor: 6.0000 × 4 = 24.0000
  Puntaje bruto acumulado: 24.0000
```

### 4. **Puntaje Bruto Final**
```
========================================
PUNTAJE BRUTO FINAL: 61.1600
========================================
```

### 5. **Transformación**
```
========================================
TRANSFORMACIÓN (Tabla 4 - Paso 4)
Factor de transformación: 61.1666666666666
Fórmula: (61.16 / 61.1666666666666) × 100
Puntaje transformado calculado: 99.99
========================================
```

### 6. **Puntajes de BD**
```
Obteniendo puntajes de BD desde calculated_results...
Registros encontrados en calculated_results: 23
Puntajes NO NULL: 23
Primeros 10 puntajes de BD: [10.90, 10.90, 10.90, ...]
Suma de puntajes BD: 250.70
Cantidad para promedio: 23
Promedio de puntajes BD: 10.90
```

### 7. **Comparación Final**
```
========================================
COMPARACIÓN FINAL
Puntaje calculado (Validador): 99.99
Puntaje promedio BD (Aplicativo): 10.90
Diferencia: 89.09
Estado: error
========================================
```

### 8. **Guardado en BD**
```
Guardando en validation_results...
Datos a guardar: {
    "battery_service_id": 6,
    "questionnaire_type": "estres",
    "form_type": "A",
    ...
}
✓ Registro insertado con ID: 42
```

## 🔎 Qué Buscar en los Logs

### ✅ Para diagnosticar discrepancias:

1. **¿Cuántos workers se están procesando?**
   - Buscar: `Workers con respuestas de estrés:`
   - Debe coincidir con el total esperado

2. **¿Los valores RAW son correctos?**
   - Buscar: `Raw values sample`
   - Valores esperados: 0, 1, 2, 3, 4, 6, 9

3. **¿Los valores SCORED son correctos?**
   - Buscar: `Scored values sample`
   - Grupo 1: 9, 6, 3, 0
   - Grupo 2: 6, 4, 2, 0
   - Grupo 3: 3, 2, 1, 0

4. **¿El promedio del bloque es correcto?**
   - Buscar: `Promedio del bloque:`
   - Debe ser: (suma de promedios) / (cantidad de ítems)

5. **¿El factor de transformación es 61.16?**
   - Buscar: `Factor de transformación:`
   - Debe ser: 61.1666666666666

6. **¿Por qué hay diferencia entre Validador y BD?**
   - Buscar: `COMPARACIÓN FINAL`
   - Si `Puntaje calculado` >> `Puntaje promedio BD`:
     - El Núcleo del Aplicativo tiene un bug
     - Los puntajes individuales están mal calculados
   - Si `Puntaje calculado` ≈ `Puntaje promedio BD`:
     - Todo está bien ✓

## 🐛 Diagnóstico de Problemas Comunes

### Problema: "No hay trabajadores Forma X"
**Causa**: No hay workers con `status='completado'` y `intralaboral_type='X'`

**Solución**: Verificar en BD:
```sql
SELECT id, name, status, intralaboral_type
FROM workers
WHERE battery_service_id = 6
  AND intralaboral_type = 'A';
```

### Problema: "No hay trabajadores con cuestionario de estrés completado"
**Causa**: Hay workers completados pero sin respuestas en `responses` con `form_type='estres'`

**Solución**: Verificar en BD:
```sql
SELECT worker_id, COUNT(*) as total_respuestas
FROM responses
WHERE worker_id IN (175, 176, 177, ...)
  AND form_type = 'estres'
GROUP BY worker_id;
```

### Problema: Diferencia muy grande entre Validador y BD
**Causa**: El Núcleo del Aplicativo (`EstresScoring::calificar()`) tiene un bug

**Solución**:
1. Revisar los logs para ver qué está calculando el Validador
2. Comparar con el código de `EstresScoring::calificar()`
3. Ejecutar: `php spark recalculate:estres` después de corregir el bug

## 📌 Niveles de Log

Los logs usan diferentes niveles según la importancia:

- **INFO**: Eventos importantes del flujo normal
- **DEBUG**: Detalles técnicos (solo los primeros 3 ítems de cada bloque para no saturar)
- **WARNING**: Situaciones anómalas pero no críticas
- **ERROR**: Errores que impiden continuar

## 🎯 Objetivo del Sistema de Logging

Este sistema de logging extremadamente detallado ("batería de logs muy, muy ácida") permite:

1. **Rastrear el flujo completo** desde que se hace clic en "Re-procesar Total" hasta que se guarda en BD
2. **Identificar exactamente dónde se genera la discrepancia** entre Validador y Aplicativo
3. **Verificar que los cálculos por bloque son correctos** según la Tabla 4 del Ministerio
4. **Auditar todos los valores intermedios** (raw, scored, promedios, factores)
5. **Diagnosticar bugs** en el Núcleo del Aplicativo comparando con el Validador

## 📅 Limpieza de Logs

Los logs se acumulan diariamente. Para limpiar logs antiguos:

```cmd
cd c:\xampp\htdocs\psyrisk\writable\logs
del log-2025-*.php
```

⚠️ **IMPORTANTE**: No eliminar el log del día actual mientras estés probando.
