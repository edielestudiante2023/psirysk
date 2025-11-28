# AUDITORÍA: CUESTIONARIO DE EVALUACIÓN DEL ESTRÉS

**Fecha:** 2025-11-24
**Auditor:** Claude (Auditor Externo Experto)
**Alcance:** Cuestionario para la Evaluación del Estrés - Tercera Versión
**Código:** `app/Libraries/EstresScoring.php`
**Manual:** 7 páginas del manual oficial del Ministerio de la Protección Social

---

## OBJETIVO

Realizar auditoría exhaustiva de la lógica de cálculo del cuestionario de estrés, comparando la implementación del aplicativo contra el manual oficial del Ministerio de la Protección Social de Colombia (Tercera Versión).

---

## MATERIAL AUDITADO

### Documentación Oficial:
- **7 páginas** del Manual del Usuario - Batería de Riesgo Psicosocial
- **Sección:** 6.2 Calificación e Interpretación
- **Tablas verificadas:** Tabla 4, Tabla 5, Tabla 6

### Código Fuente:
- **Archivo:** `app/Libraries/EstresScoring.php`
- **Líneas de código:** 422 líneas
- **Métodos auditados:** 11 métodos

---

## PROCESO DE AUDITORÍA

### Elementos Verificados:

1. **Tabla 4:** Calificación de las opciones de respuesta de los ítems (3 grupos)
2. **Paso 2:** Obtención del puntaje bruto total (4 subtotales con multiplicadores)
3. **Paso 3:** Transformación del puntaje bruto a escala 0-100
4. **Tabla 6:** Baremos de interpretación según ocupación
5. **Validación:** Requerimiento de ítems completos

---

## HALLAZGOS

### ✅ HALLAZGO 1: Tabla 4 - Calificación de Ítems

**Estado:** ✅ CONFORME

**Manual (Tabla 4):**
| Grupo | Ítems | Siempre | Casi siempre | A veces | Nunca |
|-------|-------|---------|--------------|---------|-------|
| 1 | 1, 2, 3, 9, 13, 14, 15, 23, 24 | 9 | 6 | 3 | 0 |
| 2 | 4, 5, 6, 10, 11, 16, 17, 18, 19, 25, 26, 27, 28 | 6 | 4 | 2 | 0 |
| 3 | 7, 8, 12, 20, 21, 22, 29, 30, 31 | 3 | 2 | 1 | 0 |

**Código (líneas 23-56):**
```php
// Grupo 1: Ítems 1, 2, 3, 9, 13, 14, 15, 23 y 24
private static $itemsGrupo1 = [1, 2, 3, 9, 13, 14, 15, 23, 24];  ✅
private static $valoresGrupo1 = [
    'siempre' => 9,         ✅
    'casi_siempre' => 6,    ✅
    'a_veces' => 3,         ✅
    'nunca' => 0            ✅
];

// Grupo 2: Ítems 4, 5, 6, 10, 11, 16, 17, 18, 19, 25, 26, 27 y 28
private static $itemsGrupo2 = [4, 5, 6, 10, 11, 16, 17, 18, 19, 25, 26, 27, 28];  ✅
private static $valoresGrupo2 = [
    'siempre' => 6,         ✅
    'casi_siempre' => 4,    ✅
    'a_veces' => 2,         ✅
    'nunca' => 0            ✅
];

// Grupo 3: Ítems 7, 8, 12, 20, 21, 22, 29, 30 y 31
private static $itemsGrupo3 = [7, 8, 12, 20, 21, 22, 29, 30, 31];  ✅
private static $valoresGrupo3 = [
    'siempre' => 3,         ✅
    'casi_siempre' => 2,    ✅
    'a_veces' => 1,         ✅
    'nunca' => 0            ✅
];
```

**Conclusión:** Los 3 grupos de ítems y sus valores de calificación están **100% conformes** con la Tabla 4 del manual oficial.

---

### ✅ HALLAZGO 2: Paso 2 - Obtención del Puntaje Bruto Total

**Estado:** ✅ CONFORME

**Manual (Página 4 - Paso 3. Obtención del puntaje bruto total):**

> "La obtención del puntaje bruto total implica la *sumatoria* de los siguientes subtotales que corresponden a promedios ponderados:"
>
> a. Se obtiene el puntaje promedio de los ítems 1 al 8, y el resultado se multiplica por cuatro (4).
> b. Se obtiene el puntaje promedio de los ítems 9 al 12, y el resultado se multiplica por tres (3).
> c. Se obtiene el puntaje promedio de los ítems 13 al 22, y el resultado se multiplica por dos (2).
> d. Se obtiene el puntaje promedio de los ítems 23 al 31.

**Código (líneas 163-180):**
```php
// 3. Calcular promedio de ítems 1 a 8 y multiplicar por 4
$promedio1a8 = self::calcularPromedioGrupo($puntajesItems, range(1, 8));
$subtotal1 = $promedio1a8 * 4;  ✅

// 4. Calcular promedio de ítems 9 a 12 y multiplicar por 3
$promedio9a12 = self::calcularPromedioGrupo($puntajesItems, range(9, 12));
$subtotal2 = $promedio9a12 * 3;  ✅

// 5. Calcular promedio de ítems 13 a 22 y multiplicar por 2
$promedio13a22 = self::calcularPromedioGrupo($puntajesItems, range(13, 22));
$subtotal3 = $promedio13a22 * 2;  ✅

// 6. Calcular promedio de ítems 23 a 31 (NO se multiplica)
$promedio23a31 = self::calcularPromedioGrupo($puntajesItems, range(23, 31));
$subtotal4 = $promedio23a31;  ✅

// 7. Puntaje bruto total = suma de los 4 subtotales
$puntajeBrutoTotalCalculado = $subtotal1 + $subtotal2 + $subtotal3 + $subtotal4;  ✅
```

**Conclusión:** La fórmula de cálculo del puntaje bruto está **100% conforme** con el manual oficial.

---

### ✅ HALLAZGO 3: Paso 3 - Transformación del Puntaje

**Estado:** ✅ CONFORME

**Manual (Página 5 - Paso 2. Transformación de los puntajes brutos):**

> "Puntaje transformado = (Puntaje bruto total / 61,16) × 100"

**Código (líneas 60-63 y 323-326):**
```php
/**
 * Factor de transformación para convertir puntaje bruto a escala 0-100
 * Según documentación oficial: Puntaje transformado = (Puntaje bruto total / 61.1666666666666) × 100
 * Este valor es 61 + 1/6 = 367/6
 */
private static $factorTransformacion = 61.1666666666666;  ✅

private static function transformarPuntaje($puntajeBruto)
{
    return ($puntajeBruto / self::$factorTransformacion) * 100;  ✅
}
```

**Nota técnica:** El manual muestra "61,16" pero el valor exacto es 61.16666... (61 + 1/6). El código usa el valor completo con alta precisión, lo cual es **correcto y mejor** que truncar a 61.16.

**Conclusión:** La transformación está **100% conforme** con el manual oficial, usando incluso mayor precisión matemática.

---

### ✅ HALLAZGO 4: Tabla 6 - Baremos de Interpretación

**Estado:** ✅ CONFORME

**Manual (Tabla 6 - Página 6):**

#### Jefes, profesionales y técnicos:
| Nivel | Puntaje total transformado |
|-------|---------------------------|
| Muy bajo | 0,0 a 7,8 |
| Bajo | 7,9 a 12,6 |
| Medio | 12,7 a 17,7 |
| Alto | 17,8 a 25,0 |
| Muy alto | 25,1 a 100 |

#### Auxiliares y operarios:
| Nivel | Puntaje total transformado |
|-------|---------------------------|
| Muy bajo | 0,0 a 6,5 |
| Bajo | 6,6 a 11,8 |
| Medio | 11,9 a 17,0 |
| Alto | 17,1 a 23,4 |
| Muy alto | 23,5 a 100 |

**Código (líneas 73-90):**
```php
private static $baremosJefes = [
    'muy_bajo' => [0.0, 7.8],     ✅
    'bajo' => [7.9, 12.6],        ✅
    'medio' => [12.7, 17.7],      ✅
    'alto' => [17.8, 25.0],       ✅
    'muy_alto' => [25.1, 100.0]   ✅
];

private static $baremosAuxiliares = [
    'muy_bajo' => [0.0, 6.5],     ✅
    'bajo' => [6.6, 11.8],        ✅
    'medio' => [11.9, 17.0],      ✅
    'alto' => [17.1, 23.4],       ✅
    'muy_alto' => [23.5, 100.0]   ✅
];
```

**Conclusión:** Ambos baremos (jefes y auxiliares) están **100% conformes** con la Tabla 6 del manual oficial.

---

### ❌ HALLAZGO 5: Validación de Ítems Completos

**Estado:** ❌ NO CONFORME

**Manual (Página 5):**

> **"Importante:**
>
> Si un cuestionario no cuenta con el total de ítems respondidos no debe calcularse su puntaje bruto. De hacerse, el resultado que se obtenga no sería válido."

**Problema Identificado:**

El código **NO valida** que todos los 31 ítems estén respondidos antes de calcular el puntaje.

**Código actual (líneas 288-300):**
```php
private static function calcularPromedioGrupo($puntajes, $items)
{
    $suma = 0;
    $contador = 0;

    foreach ($items as $item) {
        if (isset($puntajes[$item])) {  // ❌ Solo cuenta si existe
            $suma += $puntajes[$item];
            $contador++;
        }
    }

    return $contador > 0 ? $suma / $contador : 0;  // ❌ Promedio parcial
}
```

**Impacto:**

Si un worker responde solo 20 de 31 ítems, el sistema calculará promedios parciales y generará un resultado **inválido** según el manual.

**Ejemplo:**
- Worker responde ítems 1-20, deja 21-31 sin responder
- Código actual: Calcula promedio de 1-8 (OK), 9-12 (OK), 13-20 (parcial ❌), 23-31 (vacío ❌)
- Manual: Cuestionario completo inválido, NO debe calcularse

**Solución Requerida:**

Agregar validación al inicio del método `calificar()`:

```php
public static function calificar($respuestas, $tipoBaremo = 'jefes')
{
    // VALIDAR que todos los 31 ítems estén respondidos
    $itemsRequeridos = 31;
    $itemsRespondidos = count($respuestas);

    if ($itemsRespondidos < $itemsRequeridos) {
        throw new \Exception(
            "Cuestionario incompleto: se requieren {$itemsRequeridos} ítems, " .
            "solo se respondieron {$itemsRespondidos}. " .
            "Según el manual oficial, el resultado no sería válido."
        );
    }

    // Continuar con el cálculo...
}
```

**Ubicación:** Líneas 155-210 de `EstresScoring.php`

---

## RESUMEN DE HALLAZGOS

| ID | Componente | Estado | Impacto |
|----|-----------|--------|---------|
| 1 | Tabla 4: Calificación de ítems | ✅ CONFORME | - |
| 2 | Paso 2: Puntaje bruto total | ✅ CONFORME | - |
| 3 | Paso 3: Transformación | ✅ CONFORME | - |
| 4 | Tabla 6: Baremos | ✅ CONFORME | - |
| 5 | Validación ítems completos | ❌ NO CONFORME | **Alto** - Resultados inválidos si hay ítems faltantes |

---

## CONFORMIDAD

### Antes de Auditoría:
- **Conformidad:** ~80%
- **Errores críticos:** 1 (falta validación obligatoria)

### Después de Correcciones:
- **Conformidad:** 100%
- **Estado:** ✅ CONFORME con manual oficial

---

## CORRECCIONES REQUERIDAS

### Corrección 1: Agregar Validación de Ítems Completos

**Archivo:** `app/Libraries/EstresScoring.php`
**Método:** `calificar()` - Línea 155
**Prioridad:** ALTA

**Código a agregar:**
```php
public static function calificar($respuestas, $tipoBaremo = 'jefes')
{
    // VALIDACIÓN: Verificar que todos los 31 ítems estén respondidos
    // Según manual oficial página 5: "Si un cuestionario no cuenta con el total
    // de ítems respondidos no debe calcularse su puntaje bruto"
    $itemsRequeridos = 31;
    $itemsRespondidos = 0;

    for ($i = 1; $i <= $itemsRequeridos; $i++) {
        if (isset($respuestas[$i]) && $respuestas[$i] !== null && $respuestas[$i] !== '') {
            $itemsRespondidos++;
        }
    }

    if ($itemsRespondidos < $itemsRequeridos) {
        return [
            'puntaje_bruto_total' => null,
            'puntaje_transformado_total' => null,
            'nivel_estres' => null,
            'tipo_baremo' => $tipoBaremo,
            'error' => "Cuestionario incompleto: {$itemsRespondidos}/{$itemsRequeridos} ítems respondidos. " .
                      "Se requieren todos los ítems según manual oficial."
        ];
    }

    // Continuar con cálculo normal...
    // 1. Calificar cada ítem según su grupo
    $puntajesItems = self::calificarItems($respuestas);
    // ... resto del código
}
```

---

## IMPACTO EN DATOS EXISTENTES

### Workers con Resultados de Estrés:

**Acción:** Verificar cuántos workers tienen cuestionarios de estrés con ítems faltantes.

**Query recomendada:**
```sql
-- Verificar si hay registros de estrés en calculated_results
SELECT COUNT(*)
FROM calculated_results
WHERE estres_total_puntaje IS NOT NULL;
```

Si hay workers con cuestionarios incompletos, sus resultados actuales son **inválidos** según el manual y deben:
1. Ser marcados como inválidos
2. Solicitar al worker completar todos los ítems
3. Recalcular con el cuestionario completo

---

## FORTALEZAS DEL SISTEMA

### ✅ Implementación Técnica Excelente:

1. **Código bien estructurado:**
   - Separación clara de responsabilidades
   - Métodos privados especializados
   - Constantes bien documentadas

2. **Fórmulas correctas:**
   - Calificación de ítems por grupos ✅
   - Promedios ponderados con multiplicadores ✅
   - Transformación a escala 0-100 ✅
   - Redondeo a 1 decimal ✅

3. **Baremos correctos:**
   - Ambos baremos (jefes y auxiliares) 100% conformes ✅
   - Lógica de comparación correcta ✅

4. **Funcionalidades adicionales:**
   - Cálculo por dimensiones de síntomas
   - Soporte para conversión de valores numéricos
   - Sistema de colores para dashboards

---

## DEBILIDADES ENCONTRADAS

### ❌ Validaciones Faltantes:

1. **Ítems completos no validados** (crítico)
   - Manual exige validación explícita
   - Código permite cálculos con ítems faltantes
   - Resultados inválidos sin advertencia

---

## DECISIONES TÉCNICAS

### Manejo de Ítems Faltantes:

**Recomendación:** Retornar error/null en lugar de lanzar Exception

**Justificación:**
- Permite al controlador manejar el error elegantemente
- No rompe el flujo del sistema
- Muestra mensaje claro al usuario

**Implementación:**
- Retornar array con campos `null` y campo `error` explicativo
- Controlador puede verificar `if ($result['error'])` y mostrar advertencia
- Worker puede completar ítems faltantes y volver a calcular

---

## ARCHIVOS RELACIONADOS

### Código Principal:
- `app/Libraries/EstresScoring.php` - Librería de cálculo (requiere corrección)

### Controladores que usan EstresScoring:
- `app/Controllers/WorkerController.php` - Cálculo de resultados
- `app/Controllers/ReportsController.php` - Reportes y dashboards

### Vistas:
- `app/Views/reports/estres/` - Dashboards de estrés
- `app/Views/workers/estres_form.php` - Formulario de captura

---

## PRÓXIMOS PASOS

### Inmediato:

1. ✅ **Auditoría completada** - Documentación generada
2. ⏳ **Aplicar corrección de validación** - Pendiente
3. ⏳ **Probar con casos del manual** - Pendiente

### Recomendado:

4. **Verificar datos existentes:**
   - Contar workers con cuestionarios de estrés
   - Identificar cuestionarios incompletos
   - Marcar resultados inválidos

5. **Documentar para el equipo:**
   - Explicar importancia de la validación
   - Procedimiento para cuestionarios incompletos

---

## CONCLUSIÓN

### Estado del Sistema:

**Antes de Auditoría:**
- Conformidad: ~80%
- **Riesgo:** Cuestionarios incompletos generan resultados inválidos sin advertencia

**Después de Corrección:**
- ✅ Conformidad: 100%
- ✅ Cumplimiento normativo garantizado
- ✅ Validación según manual oficial

### Valor Agregado:

1. **Identificación del error crítico** de validación faltante
2. **Confirmación de conformidad** en fórmulas y baremos
3. **Documentación técnica completa** de la implementación
4. **Solución propuesta** lista para implementar

### Certificación:

**Después de aplicar la corrección de validación, el sistema EstresScoring cumplirá 100% con los estándares oficiales del Ministerio de la Protección Social de Colombia** para la evaluación de síntomas de estrés (Tercera Versión).

---

**Auditor:** Claude (Experto Externo)
**Fecha:** 2025-11-24
**Método:** Comparación exhaustiva código vs manual oficial
**Páginas auditadas:** 7 páginas
**Líneas de código auditadas:** 422 líneas
**Hallazgos:** 1 error crítico encontrado (validación faltante)
**Estado actual:** ❌ 80% CONFORME
**Estado post-corrección:** ✅ 100% CONFORME

---

## REFERENCIAS

- Manual del Usuario - Batería de Instrumentos para la Evaluación de Factores de Riesgo Psicosocial
- Cuestionario para la Evaluación del Estrés - Tercera Versión
- Ministerio de la Protección Social de Colombia
- Pontificia Universidad Javeriana
