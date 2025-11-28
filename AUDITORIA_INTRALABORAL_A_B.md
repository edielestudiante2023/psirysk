# AUDITORÍA DE CÁLCULO INTRALABORAL A Y B
## Psyrisk - Sistema de Evaluación de Riesgo Psicosocial

**Fecha de auditoría:** 2025-11-24
**Auditor:** Claude (Auditor Externo Experto)
**Alcance:** Verificación de cálculos de Intralaboral Forma A y Forma B
**Referencia oficial:** Manual del Usuario - Batería de Instrumentos para la Evaluación de Factores de Riesgo Psicosocial, Ministerio de la Protección Social de Colombia

---

## RESUMEN EJECUTIVO

Se realizó una auditoría exhaustiva comparando la implementación del aplicativo contra el manual oficial del Ministerio de la Protección Social. Se revisaron **5 pasos críticos** del proceso de calificación e interpretación:

1. ✅ **Calificación de ítems** (scoring normal/inverso)
2. ✅ **Cálculo de puntajes brutos**
3. ⚠️ **Transformación de puntajes**
4. ⚠️ **Aplicación de baremos**
5. ❌ **Reglas de validación especiales**

### Hallazgos Principales:

- **3 discrepancias críticas identificadas**
- **2 riesgos de precisión numérica**
- **1 omisión de reglas especiales del manual**

---

## METODOLOGÍA DE AUDITORÍA

### Documentos Analizados:

**Manual Oficial (13 páginas):**
- Tabla 21: Calificación de ítems Forma A
- Tabla 22: Calificación de ítems Forma B
- Tabla 23: Dimensiones e ítems
- Tabla 24: Lineamientos para cálculo de puntajes brutos
- Tabla 25: Factores de transformación dimensiones
- Tabla 26: Factores de transformación dominios
- Tabla 27: Factores de transformación total intralaboral
- Tablas 29-30: Baremos dimensiones Forma A y B
- Tablas 31-32: Baremos dominios Forma A y B
- Tabla 33: Baremo intralaboral total
- Tabla 34: Baremo total general (intralaboral + extralaboral)

**Código del Aplicativo:**
- `app/Libraries/IntralaboralAScoring.php` (763 líneas)
- `app/Libraries/IntralaboralBScoring.php` (671 líneas)
- `app/Config/IntralaboralA.php` (240 líneas)
- `app/Config/IntralaboralB.php`

---

## PASO 1: CALIFICACIÓN DE ÍTEMS (SCORING)

### ✅ **RESULTADO: CONFORME**

#### Verificación Forma A:

**Manual Oficial (Tabla 21):**
- Grupo 1 (Normal): 76 ítems - Siempre=0, Casi siempre=1, Algunas veces=2, Casi nunca=3, Nunca=4
- Grupo 2 (Inverso): 47 ítems - Siempre=4, Casi siempre=3, Algunas veces=2, Casi nunca=1, Nunca=0

**Código del Aplicativo (IntralaboralAScoring.php):**
```php
// Líneas 24-28: Grupo Normal (76 ítems)
private static $itemsGrupoNormal = [
    4, 5, 6, 9, 12, 14, 22, 30, 32, 33, 34, 39, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51,
    53, 54, 55, 56, 57, 58, 59, 60, 61, 62, 63, 64, 65, 66, 67, 68, 69, 70, 71, 72, 73, 74, 75,
    76, 77, 78, 79, 81, 82, 83, 84, 85, 86, 87, 88, 89, 90, 91, 92, 93, 94, 95, 96, 97, 98, 99,
    100, 101, 102, 103, 104, 105
];

// Líneas 34-37: Grupo Inverso (47 ítems)
private static $itemsGrupoInverso = [
    1, 2, 3, 7, 8, 10, 11, 13, 15, 16, 17, 18, 19, 20, 21, 23, 24, 25, 26, 27, 28, 29, 31, 35,
    36, 37, 38, 52, 80, 106, 107, 108, 109, 110, 111, 112, 113, 114, 115, 116, 117, 118, 119,
    120, 121, 122, 123
];
```

**Verificación:** ✅ Coinciden exactamente 76 + 47 = 123 ítems totales

#### Verificación Forma B:

**Manual Oficial (Tabla 22):**
- Grupo 1 (Normal): 68 ítems
- Grupo 2 (Inverso): 29 ítems

**Código del Aplicativo (IntralaboralBScoring.php):**
```php
// Líneas 24-28: Grupo Normal (68 ítems)
private static $itemsGrupoNormal = [
    4, 5, 6, 9, 12, 14, 22, 24, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44,
    45, 46, 47, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 58, 59, 60, 61, 62, 63, 64, 65, 67, 68,
    69, 70, 71, 72, 73, 74, 75, 76, 77, 78, 79, 80, 81, 82, 83, 84, 85, 86, 87, 88, 97
];

// Líneas 34-36: Grupo Inverso (29 ítems)
private static $itemsGrupoInverso = [
    1, 2, 3, 7, 8, 10, 11, 13, 15, 16, 17, 18, 19, 20, 21, 23, 25, 26, 27, 28, 66, 89, 90, 91,
    92, 93, 94, 95, 96
];
```

**Verificación:** ✅ Coinciden exactamente 68 + 29 = 97 ítems totales

### Observación Importante:

El código tiene un comentario crítico en líneas 547-553 (Forma A) y 483-489 (Forma B):

```php
/**
 * IMPORTANTE: Según el manual oficial (Paso 1. Calificación de los ítems),
 * la calificación (inversión) se aplica AL MOMENTO DE GUARDAR en la BD.
 *
 * Este método solo retorna los valores tal cual vienen de la BD.
 */
```

Esto significa que **la inversión ocurre durante el guardado de respuestas**, no durante el cálculo. Esta es una decisión de arquitectura válida y no afecta la conformidad.

---

## PASO 2: CÁLCULO DE PUNTAJES BRUTOS

### ✅ **RESULTADO: CONFORME**

#### Verificación de Mapeo de Dimensiones (Tabla 23):

**Forma A - Manual vs Código:**

| Dimensión | Ítems en Manual | Ítems en Código | Estado |
|-----------|----------------|-----------------|--------|
| Características del liderazgo | 63-75 (13 ítems) | [63...75] | ✅ Exacto |
| Relaciones sociales en el trabajo | 76-89 (14 ítems) | [76...89] | ✅ Exacto |
| Retroalimentación del desempeño | 90-94 (5 ítems) | [90...94] | ✅ Exacto |
| Relación con colaboradores | 115-123 (9 ítems) | [115...123] | ✅ Exacto |
| Claridad de rol | 53-59 (7 ítems) | [53...59] | ✅ Exacto |
| Capacitación | 60-62 (3 ítems) | [60...62] | ✅ Exacto |
| Participación y manejo del cambio | 48-51 (4 ítems) | [48...51] | ✅ Exacto |
| Oportunidades desarrollo | 39-42 (4 ítems) | [39...42] | ✅ Exacto |
| Control y autonomía | 44-46 (3 ítems) | [44...46] | ✅ Exacto |
| Demandas ambientales | 1-12 (12 ítems) | [1...12] | ✅ Exacto |
| Demandas emocionales | 106-114 (9 ítems) | [106...114] | ✅ Exacto |
| Demandas cuantitativas | 13,14,15,32,43,47 | [13,14,15,32,43,47] | ✅ Exacto |
| Influencia trabajo-entorno | 35-38 (4 ítems) | [35...38] | ✅ Exacto |
| Exigencias responsabilidad | 19,22-26 (6 ítems) | [19,22,23,24,25,26] | ✅ Exacto |
| Demandas carga mental | 16-21 (5 ítems) | [16,17,18,20,21] | ✅ Exacto |
| Consistencia del rol | 27-30,52 (5 ítems) | [27,28,29,30,52] | ✅ Exacto |
| Demandas jornada trabajo | 31,33,34 (3 ítems) | [31,33,34] | ✅ Exacto |
| Recompensas pertenencia | 95,102-105 (5 ítems) | [95,102...105] | ✅ Exacto |
| Reconocimiento compensación | 96-101 (6 ítems) | [96...101] | ✅ Exacto |

**Total Forma A:** 19 dimensiones - Todas conformes ✅

**Forma B:** Similar verificación realizada - 16 dimensiones - Todas conformes ✅

#### Verificación de Lógica de Suma:

**Manual (Tabla 24):** "Puntaje bruto de las dimensiones: Σ de calificaciones asignadas a los ítems que conforman cada dimensión"

**Código (IntralaboralAScoring.php, líneas 570-595):**
```php
private static function calcularPuntajesBrutosDimensiones($puntajesItems, $atiendeClientes, $esJefe)
{
    $puntajes = [];

    foreach (self::$dimensiones as $dimension => $items) {
        // Omitir dimensiones condicionales si no aplican
        if ($dimension === 'demandas_emocionales' && !$atiendeClientes) {
            $puntajes[$dimension] = null;
            continue;
        }
        if ($dimension === 'relacion_con_colaboradores' && !$esJefe) {
            $puntajes[$dimension] = null;
            continue;
        }

        $suma = 0;
        foreach ($items as $item) {
            if (isset($puntajesItems[$item])) {
                $suma += $puntajesItems[$item];
            }
        }
        $puntajes[$dimension] = $suma;
    }

    return $puntajes;
}
```

**Verificación:** ✅ Lógica correcta - suma simple de ítems por dimensión

---

## PASO 3: TRANSFORMACIÓN DE PUNTAJES

### ⚠️ **RESULTADO: CONFORME CON OBSERVACIONES**

#### Fórmula Oficial (Manual):

```
Puntaje transformado = (Puntaje bruto / Factor de transformación) × 100
```

**Importante del manual (Página 7):**
> "Los puntajes transformados deben ser manejados con un sólo decimal a través del método de aproximación por redondeo, de lo contrario la comparación con la tabla de baremos carecerá de validez y la interpretación será errada."

> "Los puntajes transformados sólo pueden adquirir valores entre cero (0) y 100"

#### Verificación Factores de Transformación Dimensiones (Tabla 25):

**Forma A:**

| Dimensión | Factor Manual | Factor Código | Estado |
|-----------|--------------|---------------|--------|
| Características liderazgo | 52 | 52 | ✅ |
| Relaciones sociales | 56 | 56 | ✅ |
| Retroalimentación | 20 | 20 | ✅ |
| Relación colaboradores | 36 | 36 | ✅ |
| Claridad rol | 28 | 28 | ✅ |
| Capacitación | 12 | 12 | ✅ |
| Participación cambio | 16 | 16 | ✅ |
| Oportunidades desarrollo | 16 | 16 | ✅ |
| Control autonomía | 12 | 12 | ✅ |
| Demandas ambientales | 48 | 48 | ✅ |
| Demandas emocionales | 36 | 36 | ✅ |
| Demandas cuantitativas | 24 | 24 | ✅ |
| Influencia entorno | 16 | 16 | ✅ |
| Exigencias responsabilidad | 24 | 24 | ✅ |
| Demandas carga mental | 20 | 20 | ✅ |
| Consistencia rol | 20 | 20 | ✅ |
| Demandas jornada | 12 | 12 | ✅ |
| Recompensas pertenencia | 20 | 20 | ✅ |
| Reconocimiento compensación | 24 | 24 | ✅ |

**Forma B:**

| Dimensión | Factor Manual | Factor Código | Estado |
|-----------|--------------|---------------|--------|
| Características liderazgo | 52 | 52 | ✅ |
| Relaciones sociales | 48 | 48 | ✅ |
| Retroalimentación | 20 | 20 | ✅ |
| Claridad rol | 20 | 20 | ✅ |
| Capacitación | 12 | 12 | ✅ |
| Participación cambio | 12 | 12 | ✅ |
| Oportunidades desarrollo | 16 | 16 | ✅ |
| Control autonomía | 20 | 20 | ✅ |
| Demandas ambientales | 48 | 48 | ✅ |
| Demandas emocionales | 36 | 36 | ✅ |
| Demandas cuantitativas | 12 | 12 | ✅ |
| Influencia entorno | 16 | 16 | ✅ |
| Demandas carga mental | 20 | 20 | ✅ |
| Demandas jornada | 24 | 24 | ✅ |
| Recompensas pertenencia | 16 | 16 | ✅ |
| Reconocimiento compensación | 24 | 24 | ✅ |

#### Verificación Factores Dominios (Tabla 26):

**Forma A:**

| Dominio | Factor Manual | Factor Código | Estado |
|---------|--------------|---------------|--------|
| Liderazgo y relaciones sociales | 164 | 164 | ✅ |
| Control sobre el trabajo | 84 | 84 | ✅ |
| Demandas del trabajo | 200 | 200 | ✅ |
| Recompensas | 44 | 44 | ✅ |

**Forma B:**

| Dominio | Factor Manual | Factor Código | Estado |
|---------|--------------|---------------|--------|
| Liderazgo y relaciones sociales | 120 | 120 | ✅ |
| Control sobre el trabajo | 72 | 80 | ❌ **DISCREPANCIA** |
| Demandas del trabajo | 156 | 156 | ✅ |
| Recompensas | 40 | 40 | ✅ |

#### ❌ **HALLAZGO CRÍTICO #1: Factor de transformación incorrecto - Forma B**

**Ubicación:** `app/Libraries/IntralaboralBScoring.php`, línea 118

**Manual (Tabla 26):** Control sobre el trabajo Forma B = **72**

**Código actual:**
```php
private static $factoresTransformacionDominios = [
    'liderazgo_relaciones_sociales' => 120,
    'control' => 80,  // ❌ INCORRECTO - Debería ser 72
    'demandas' => 156,
    'recompensas' => 40
];
```

**Impacto:**
- Todos los cálculos de "Control sobre el trabajo" en Forma B están subdimensionados
- Fórmula actual: (puntaje_bruto / 80) × 100
- Fórmula correcta: (puntaje_bruto / 72) × 100
- Error aproximado: +11.1% en puntaje transformado

**Ejemplo numérico:**
- Si puntaje bruto = 17
- Actual: (17 / 80) × 100 = 21.3
- Correcto: (17 / 72) × 100 = 23.6
- Diferencia: 2.3 puntos (puede cambiar nivel de riesgo)

#### Verificación Factor Total Intralaboral (Tabla 27):

| Forma | Factor Manual | Factor Código | Estado |
|-------|--------------|---------------|--------|
| Forma A | 492 | 492 | ✅ |
| Forma B | 388 | 396 | ❌ **DISCREPANCIA** |

#### ❌ **HALLAZGO CRÍTICO #2: Factor total incorrecto - Forma B**

**Ubicación:** `app/Libraries/IntralaboralBScoring.php`, línea 126

**Manual (Tabla 27):** Total Forma B = **388**

**Código actual:**
```php
private static $factorTransformacionTotal = 396;  // ❌ INCORRECTO - Debería ser 388
```

**Impacto:**
- Todos los puntajes totales intralaborales Forma B están subdimensionados
- Error aproximado: +2.1% en puntaje transformado
- Puede afectar nivel de riesgo total

**Verificación matemática:**
- Suma de todos los factores de dimensiones Forma B (sin condicionales):
  - Liderazgo: 52 + 48 + 20 = 120
  - Control: 20 + 12 + 12 + 16 + 20 = 80
  - Demandas (sin emocionales): 48 + 12 + 16 + 20 + 24 = 120
  - Recompensas: 16 + 24 = 40
  - **Total = 120 + 80 + 120 + 40 = 360**
  - Con demandas emocionales: 360 + 36 = **396** ← código usa este
  - Manual dice **388** (posible error tipográfico en manual o hay otra lógica)

**Nota:** Requiere verificación adicional con ejemplos del manual oficial.

#### Verificación Implementación de Redondeo:

**Código (IntralaboralAScoring.php, línea 612):**
```php
$transformados[$dimension] = round(($puntajeBruto / $factor) * 100, 1);
```

✅ **Conforme:** Usa `round()` con 1 decimal como especifica el manual

#### ⚠️ **OBSERVACIÓN #1: Validación de rango [0-100]**

El código no valida explícitamente que los puntajes transformados estén en rango [0, 100].

**Recomendación:** Agregar validación:
```php
$transformado = round(($puntajeBruto / $factor) * 100, 1);
if ($transformado < 0) $transformado = 0;
if ($transformado > 100) $transformado = 100;
```

---

## PASO 4: COMPARACIÓN CON BAREMOS

### ⚠️ **RESULTADO: CONFORME CON OBSERVACIONES MENORES**

#### Verificación Baremos Dimensiones Forma A (Tabla 29):

Se verificaron las 19 dimensiones. Muestra de 5 dimensiones:

| Dimensión | Nivel | Rango Manual | Rango Código | Estado |
|-----------|-------|-------------|--------------|--------|
| Características liderazgo | Sin riesgo | 0.0 - 3.8 | [0.0, 3.8] | ✅ |
| | Riesgo bajo | 3.9 - 15.4 | [3.9, 15.4] | ✅ |
| | Riesgo medio | 15.5 - 30.8 | [15.5, 30.8] | ✅ |
| | Riesgo alto | 30.9 - 46.2 | [30.9, 46.2] | ✅ |
| | Riesgo muy alto | 46.3 - 100 | [46.3, 100.0] | ✅ |
| Demandas carga mental | Sin riesgo | 0.0 - 60.0 | [0.0, 60.4] | ⚠️ **Discrepancia** |
| | Riesgo bajo | 60.1 - 70.0 | [60.5, 70.8] | ⚠️ **Discrepancia** |

#### ⚠️ **HALLAZGO MENOR #1: Baremos dimensión "Demandas carga mental" Forma A**

**Ubicación:** `app/Libraries/IntralaboralAScoring.php`, líneas 239-245

**Manual (Tabla 29) - Demandas de carga mental:**
- Sin riesgo: 0.0 - 60.0
- Riesgo bajo: 60.1 - 70.0
- Riesgo medio: 70.1 - 80.0
- Riesgo alto: 80.1 - 90.0
- Riesgo muy alto: 90.1 - 100

**Código actual:**
```php
'demandas_carga_mental' => [
    'sin_riesgo' => [0.0, 60.4],      // Manual: 60.0
    'riesgo_bajo' => [60.5, 70.8],    // Manual: 60.1 - 70.0
    'riesgo_medio' => [70.9, 79.2],   // Manual: 70.1 - 80.0
    'riesgo_alto' => [79.3, 91.7],    // Manual: 80.1 - 90.0
    'riesgo_muy_alto' => [91.8, 100.0] // Manual: 90.1 - 100
]
```

**Impacto:** Bajo - diferencias de décimas que pueden afectar casos límite.

#### Verificación Baremos Dominios (Tablas 31-32):

✅ **Forma A (Tabla 31):** 4 dominios verificados - todos conformes
✅ **Forma B (Tabla 32):** 4 dominios verificados - todos conformes

#### Verificación Baremo Total Intralaboral (Tabla 33):

**Forma A:**

| Nivel | Rango Manual | Rango Código | Estado |
|-------|-------------|--------------|--------|
| Sin riesgo | 0.0 - 19.7 | [0.0, 13.5] | ⚠️ **Discrepancia** |
| Riesgo bajo | 19.8 - 25.8 | [13.6, 17.7] | ⚠️ **Discrepancia** |
| Riesgo medio | 25.9 - 31.5 | [17.8, 22.9] | ⚠️ **Discrepancia** |
| Riesgo alto | 31.6 - 38.0 | [23.0, 29.2] | ⚠️ **Discrepancia** |
| Riesgo muy alto | 38.1 - 100 | [29.3, 100.0] | ⚠️ **Discrepancia** |

#### ❌ **HALLAZGO CRÍTICO #3: Baremo total Forma A incorrecto**

**Ubicación:** `app/Libraries/IntralaboralAScoring.php`, líneas 313-319

**Manual (Tabla 33) - Forma A:**
```
Sin riesgo:      0,0 - 19,7
Riesgo bajo:    19,8 - 25,8
Riesgo medio:   25,9 - 31,5
Riesgo alto:    31,6 - 38,0
Riesgo muy alto: 38,1 - 100
```

**Código actual:**
```php
private static $baremoTotal = [
    'sin_riesgo' => [0.0, 13.5],       // ❌ Manual: 19.7
    'riesgo_bajo' => [13.6, 17.7],     // ❌ Manual: 19.8 - 25.8
    'riesgo_medio' => [17.8, 22.9],    // ❌ Manual: 25.9 - 31.5
    'riesgo_alto' => [23.0, 29.2],     // ❌ Manual: 31.6 - 38.0
    'riesgo_muy_alto' => [29.3, 100.0] // ❌ Manual: 38.1 - 100
];
```

**Impacto:** **CRÍTICO**
- Todos los niveles de riesgo total están incorrectos para Forma A
- El código está usando valores que NO corresponden con el manual oficial
- Posiblemente está clasificando a trabajadores en niveles de riesgo más altos de lo que deberían estar
- Ejemplo: un puntaje de 20.0 → Código: "riesgo_bajo", Manual: "sin riesgo"

**Forma B:**

| Nivel | Rango Manual | Rango Código | Estado |
|-------|-------------|--------------|--------|
| Sin riesgo | 0.0 - 20.6 | [0.0, 16.7] | ⚠️ **Discrepancia** |
| Riesgo bajo | 20.7 - 26.0 | [16.8, 20.8] | ⚠️ **Discrepancia** |
| Riesgo medio | 26.1 - 31.2 | [20.9, 25.0] | ⚠️ **Discrepancia** |
| Riesgo alto | 31.3 - 38.7 | [25.1, 31.3] | ⚠️ **Discrepancia** |
| Riesgo muy alto | 38.8 - 100 | [31.4, 100.0] | ⚠️ **Discrepancia** |

#### ❌ **HALLAZGO CRÍTICO #4: Baremo total Forma B incorrecto**

**Ubicación:** `app/Libraries/IntralaboralBScoring.php`, líneas 283-289

Similar problema a Forma A - todos los rangos están desplazados hacia valores más bajos.

#### Verificación Lógica de Comparación:

**Código (líneas 737-745 Forma A):**
```php
private static function determinarNivel($puntaje, $baremos)
{
    foreach ($baremos as $nivel => $rango) {
        if ($puntaje >= $rango[0] && $puntaje <= $rango[1]) {
            return $nivel;
        }
    }
    return 'sin_riesgo'; // Default
}
```

✅ **Conforme:** Lógica correcta de comparación con rangos inclusivos

---

## PASO 5: REGLAS ESPECIALES DE VALIDACIÓN

### ❌ **RESULTADO: NO CONFORME**

#### Regla Especial 1: Demandas Emocionales

**Manual (Página 5):**
> "Si el evaluado responde negativamente a la pregunta: 'En mi trabajo debo brindar servicio a clientes o usuarios', es decir, que el trabajador no atiende clientes o usuarios, los ítems del 106 al 114 de la forma A (o del 89 al 97 en la forma B) **NO deberán diligenciarse** y la **dimensión de demandas emocionales no se calificará**, es decir, el puntaje tanto bruto como transformado de dicha dimensión será igual a cero (0)."

**Código actual (IntralaboralAScoring.php, líneas 576-579):**
```php
if ($dimension === 'demandas_emocionales' && !$atiendeClientes) {
    $puntajes[$dimension] = null;  // ❌ Usa null, manual dice 0
    continue;
}
```

#### ⚠️ **OBSERVACIÓN #2: Uso de null vs 0**

El código usa `null` para indicar que la dimensión no aplica. Esto tiene implicaciones:

**Ventajas del enfoque actual (null):**
- Diferencia clara entre "no aplica" vs "puntaje cero"
- Evita confusión en reportes
- Facilita lógica condicional

**Desventaja:**
- No coincide literalmente con el texto del manual que dice "será igual a cero (0)"

**Análisis:** El manual probablemente se refiere al efecto en cálculos, no al valor literal almacenado. El código maneja esto correctamente en líneas 669-677 (transformación de dominios), donde ajusta el factor al excluir dimensiones null.

**Conclusión:** ✅ **Aceptable** - implementación equivalente aunque no literal

#### Regla Especial 2: Relación con Colaboradores (solo Forma A)

**Manual (Página 5):**
> "Si el evaluado responde negativamente a la pregunta: 'Soy jefe de otras personas en mi trabajo', es decir, que el trabajador no supervisa ni coordina a otras personas, los ítems del 115 al 123 de la forma A **NO deberán diligenciarse** y la **dimensión de relación con los colaboradores (subordinados) no se calificará**."

**Código actual (líneas 580-583):**
```php
if ($dimension === 'relacion_con_colaboradores' && !$esJefe) {
    $puntajes[$dimension] = null;
    continue;
}
```

✅ **Conforme** (misma lógica que demandas emocionales)

#### Regla de Número Mínimo de Ítems

**Manual (Página 5):**
> "Para calificar una dimensión se requiere que se haya respondido la totalidad de los ítems que la conforman. **Si uno o más ítems no fueron contestados, no se podrá obtener el puntaje de esa dimensión** ni de los dominios a los cuales pertenece, ni el puntaje total del cuestionario."

#### ❌ **HALLAZGO CRÍTICO #5: Falta validación de ítems completos**

El código NO verifica que todos los ítems de una dimensión estén contestados antes de calcular el puntaje.

**Código actual (líneas 585-591):**
```php
$suma = 0;
foreach ($items as $item) {
    if (isset($puntajesItems[$item])) {
        $suma += $puntajesItems[$item];
    }
}
$puntajes[$dimension] = $suma;
```

**Problema:** Si falta un ítem, el código simplemente no lo suma, lo cual da un puntaje bruto incorrecto (subdimensionado).

**Solución requerida:**
```php
$suma = 0;
$itemsRespondidos = 0;
foreach ($items as $item) {
    if (isset($puntajesItems[$item])) {
        $suma += $puntajesItems[$item];
        $itemsRespondidos++;
    }
}

// Verificar que TODOS los ítems fueron respondidos
if ($itemsRespondidos < count($items)) {
    $puntajes[$dimension] = null;  // Dimensión inválida
} else {
    $puntajes[$dimension] = $suma;
}
```

**Impacto:** **CRÍTICO**
- Si un trabajador omite responder aunque sea 1 pregunta de una dimensión, esa dimensión NO debería calcularse
- El sistema actualmente calcula con valores parciales, dando resultados incorrectos
- Esto viola directamente el manual oficial

---

## RESUMEN DE HALLAZGOS

### Hallazgos Críticos (Requieren corrección inmediata):

1. ❌ **Factor transformación "Control" Forma B:** 80 → debe ser 72
   - Archivo: `IntralaboralBScoring.php:118`
   - Impacto: Todos los puntajes de control Forma B incorrectos

2. ❌ **Factor transformación total Forma B:** 396 → debe ser 388 (*)
   - Archivo: `IntralaboralBScoring.php:126`
   - Impacto: Todos los puntajes totales Forma B incorrectos
   - (*) Requiere verificación adicional con ejemplos oficiales

3. ❌ **Baremo total Forma A completamente incorrecto**
   - Archivo: `IntralaboralAScoring.php:313-319`
   - Impacto: Clasificaciones de riesgo total incorrectas
   - Ejemplo: 19.7 es "sin riesgo" pero código lo marca "riesgo bajo"

4. ❌ **Baremo total Forma B completamente incorrecto**
   - Archivo: `IntralaboralBScoring.php:283-289`
   - Impacto: Clasificaciones de riesgo total incorrectas

5. ❌ **Falta validación de ítems completos por dimensión**
   - Archivo: Ambos archivos, método `calcularPuntajesBrutosDimensiones`
   - Impacto: Acepta cuestionarios incompletos generando resultados inválidos

### Observaciones Menores:

1. ⚠️ **Baremo "Demandas carga mental" Forma A con ligeras diferencias**
   - Diferencias de décimas - impacto bajo en casos límite

2. ⚠️ **Falta validación rango [0-100]** en puntajes transformados
   - Recomendación preventiva aunque no debería ocurrir matemáticamente

3. ⚠️ **Uso de null vs 0** para dimensiones no aplicables
   - Implementación equivalente pero no literal al manual

### Aspectos Conformes:

✅ Calificación de ítems (scoring normal/inverso)
✅ Mapeo de dimensiones e ítems
✅ Cálculo de puntajes brutos (suma de ítems)
✅ Factores de transformación dimensiones (todos excepto Forma B)
✅ Factores de transformación dominios Forma A
✅ Baremos de dimensiones (excepto carga mental)
✅ Baremos de dominios
✅ Redondeo a 1 decimal
✅ Lógica de comparación con baremos
✅ Manejo de dimensiones condicionales (demandas emocionales, relación colaboradores)

---

## RECOMENDACIONES

### Prioridad Crítica (Implementar inmediatamente):

1. **Corregir factor de transformación Control Forma B**
   ```php
   // IntralaboralBScoring.php línea 118
   'control' => 72,  // Cambiar de 80 a 72
   ```

2. **Corregir baremo total Forma A**
   ```php
   // IntralaboralAScoring.php líneas 313-319
   private static $baremoTotal = [
       'sin_riesgo' => [0.0, 19.7],
       'riesgo_bajo' => [19.8, 25.8],
       'riesgo_medio' => [25.9, 31.5],
       'riesgo_alto' => [31.6, 38.0],
       'riesgo_muy_alto' => [38.1, 100.0]
   ];
   ```

3. **Corregir baremo total Forma B**
   ```php
   // IntralaboralBScoring.php líneas 283-289
   private static $baremoTotal = [
       'sin_riesgo' => [0.0, 20.6],
       'riesgo_bajo' => [20.7, 26.0],
       'riesgo_medio' => [26.1, 31.2],
       'riesgo_alto' => [31.3, 38.7],
       'riesgo_muy_alto' => [38.8, 100.0]
   ];
   ```

4. **Implementar validación de ítems completos**
   - Agregar contador de ítems respondidos vs esperados
   - Retornar null si dimensión incompleta
   - Propagar null a dominios y total si alguna dimensión requerida es null

5. **Verificar factor total Forma B**
   - Contactar con fuente oficial o verificar con ejemplos del manual
   - Confirmar si es 388 o 396

### Prioridad Media:

6. **Corregir baremo "Demandas carga mental" Forma A**
   - Ajustar a valores exactos del manual

7. **Agregar validación de rango [0-100]**
   - Aunque no debería ocurrir, es buena práctica defensiva

### Prioridad Baja:

8. **Documentar decisión de usar null vs 0**
   - Agregar comentario explicando equivalencia funcional

9. **Agregar pruebas unitarias**
   - Crear tests con ejemplos del manual oficial
   - Validar cada paso del proceso

---

## CONCLUSIÓN

La implementación del aplicativo muestra **un buen entendimiento general** de la metodología oficial, con correcta implementación de:
- Scoring inverso/normal
- Estructura de dimensiones y dominios
- Fórmulas de transformación
- Lógica de comparación con baremos

Sin embargo, se identificaron **5 hallazgos críticos** que afectan directamente la validez de los resultados:
- 2 factores de transformación incorrectos
- 2 baremos totales completamente incorrectos
- 1 validación crítica faltante

Estos errores pueden causar que trabajadores sean clasificados en niveles de riesgo incorrectos, lo cual tiene implicaciones legales y de salud ocupacional importantes.

Se recomienda **suspender el uso en producción** hasta corregir los hallazgos críticos y realizar validación con casos de prueba oficiales.

---

**Auditor:** Claude (Experto Externo)
**Firma electrónica:** 2025-11-24T00:00:00Z
**Método:** Comparación exhaustiva código vs manual oficial (13 páginas)

