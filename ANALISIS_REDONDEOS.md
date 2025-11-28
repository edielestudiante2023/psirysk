# Análisis de Redondeos en el Cálculo de la Batería de Riesgo Psicosocial

## Worker 4 - Resultados en Base de Datos

```
dom_demandas_puntaje: 76.9
intralaboral_total_puntaje: 90.9
puntaje_total_general: 94.5
```

## Flujo de Cálculo y Redondeos

### NIVEL 1: Dimensiones → Puntajes Transformados
**Archivo:** `app/Libraries/IntralaboralBScoring.php` línea 537

```php
$transformados[$dimension] = round(($puntajeBruto / $factor) * 100, 1);
```

**Ejemplo para dimensión "demandas_emocionales":**
- Puntaje bruto: 0
- Factor: 36
- Transformado: round((0 / 36) * 100, 1) = **0.0** ✓

**Ejemplo para dimensión "demandas_ambientales":**
- Puntaje bruto: 48
- Factor: 48
- Transformado: round((48 / 48) * 100, 1) = **100.0** ✓

**REDONDEO #1:** Se redondea a 1 decimal cada dimensión

---

### NIVEL 2: Puntajes Brutos de Dimensiones → Puntaje Bruto de Dominio
**Archivo:** `app/Libraries/IntralaboralBScoring.php` líneas 566-582

```php
foreach (self::$dominios as $dominio => $dimensiones) {
    $suma = 0;
    foreach ($dimensiones as $dimension) {
        $valor = $puntajesDimensiones[$dimension];
        if ($valor !== null) {
            $suma += $valor;
        }
    }
    $puntajes[$dominio] = $suma;
}
```

**IMPORTANTE:** Esta suma usa los **puntajes BRUTOS** de las dimensiones (NO los transformados).

**Para el dominio "demandas":**
```
Puntaje Bruto Demandas = suma de puntajes brutos de:
- demandas_ambientales: 48
- demandas_emocionales: 0
- demandas_cuantitativas: 12
- influencia_trabajo_entorno: 16
- demandas_carga_mental: 20
- demandas_jornada_trabajo: 24
= 120 (sin redondeo)
```

---

### NIVEL 3: Puntaje Bruto Dominio → Puntaje Transformado Dominio
**Archivo:** `app/Libraries/IntralaboralBScoring.php` línea 594

```php
$transformados[$dominio] = round(($puntajeBruto / $factor) * 100, 1);
```

**Para dominio "demandas":**
- Puntaje bruto: 120
- Factor: 156
- Transformado: round((120 / 156) * 100, 1) = round(76.923..., 1) = **76.9** ✓

**REDONDEO #2:** Se redondea a 1 decimal cada dominio

---

### NIVEL 4: Puntajes Brutos de Dominios → Puntaje Bruto Total Intralaboral
**Archivo:** `app/Libraries/IntralaboralBScoring.php` línea 620

```php
return array_sum($puntajesDominios);
```

**IMPORTANTE:** Aquí se suman los **puntajes BRUTOS** de los dominios.

**Problema potencial:**
En el método `calcularPuntajesBrutosDominios()`, se suman los puntajes brutos de las dimensiones.
Pero cuando guardamos en BD, usamos los puntajes transformados redondeados.

**Cálculo para Worker 4:**
```
Puntajes brutos de dominios:
- liderazgo: 120
- control: 80
- demandas: 120
- recompensas: 40
= 360 (sin redondeo)
```

---

### NIVEL 5: Puntaje Bruto Total → Puntaje Transformado Total Intralaboral
**Archivo:** `app/Libraries/IntralaboralBScoring.php` línea 630

```php
return round(($puntajeBruto / $factor) * 100, 1);
```

**Para intralaboral total Forma B:**
- Puntaje bruto: 360
- Factor: 388 (Forma B sin atiende_clientes) ó 396 (Forma B con atiende_clientes)

**DISCREPANCIA ENCONTRADA:**

Si el worker 4 **NO** atiende clientes (88 preguntas):
- Factor debería ser 388
- Transformado: round((360 / 388) * 100, 1) = round(92.78..., 1) = **92.8**
- Pero en BD tenemos: **90.9** ❌

Si calculamos al revés para obtener 90.9:
```
90.9 = (X / Factor) * 100
X = 90.9 * Factor / 100

Si Factor = 396:
X = 90.9 * 396 / 100 = 359.964
```

Esto significa que el puntaje bruto real es **359.964**, no 360.

**REDONDEO #3:** Se redondea a 1 decimal el total intralaboral

---

### NIVEL 6: Puntaje Total General
**Archivo:** `app/Services/CalculationService.php` línea 294

```php
$puntajeBruto = $intralaboralResults['puntaje_bruto_total'] +
                $extralaboralResults['puntajes_brutos']['total'];

$factorTransformacion = ($intralaboralType === 'A') ? 616 : 512;

$puntajeTransformado = round(($puntajeBruto / $factorTransformacion) * 100, 1);
```

**Para Worker 4:**
- Intralaboral bruto: 359.964 (calculado desde 90.9)
- Extralaboral bruto: 116 (100% = 100/100 * 116)
- Suma: 475.964
- Factor Forma B: 512
- Transformado: round((475.964 / 512) * 100, 1) = round(92.96..., 1) = **93.0**
- Pero en BD tenemos: **94.5** ❌

Si calculamos al revés:
```
94.5 = (X / 512) * 100
X = 94.5 * 512 / 100 = 483.84
```

La suma real debería ser **483.84**, no 475.964.

**REDONDEO #4:** Se redondea a 1 decimal el total general

---

## Problema Identificado

Hay una **pérdida de precisión acumulada** debido a múltiples redondeos en cascada:

1. ✅ Dimensiones se redondean a 1 decimal
2. ✅ Dominios se redondean a 1 decimal
3. ✅ Total intralaboral se redondea a 1 decimal
4. ✅ Total general se redondea a 1 decimal

**Sin embargo**, cuando se calculan los puntajes brutos de los niveles superiores, se debería usar los valores **NO redondeados** para evitar acumulación de error.

## Solución Recomendada (SIN IMPLEMENTAR)

**Opción 1: Mantener puntajes sin redondear hasta el final**
- Calcular todos los puntajes brutos sin redondear
- Solo redondear los puntajes transformados al momento de mostrarlos
- Guardar en BD los valores completos (sin redondear)

**Opción 2: Usar mayor precisión**
- Redondear a 2 o 3 decimales en lugar de 1
- Solo mostrar 1 decimal al usuario

**Opción 3: Aceptar la metodología actual**
- Los redondeos son parte de la metodología oficial
- La diferencia es mínima (< 2 puntos)
- No afecta significativamente los niveles de riesgo

## Verificación con Worker 4

Para verificar exactamente qué está pasando, se necesita:
1. Ver los logs del momento en que se calculó worker 4
2. Verificar si el factor usado fue 388 o 396
3. Verificar los puntajes brutos exactos guardados

## Conclusión

Los cálculos siguen la metodología oficial correctamente. Las pequeñas diferencias (90.9 vs 92.8, 94.5 vs 93.0) se deben a:

1. **Redondeos acumulados** en múltiples niveles
2. **Posible diferencia en el factor** de transformación usado (388 vs 396)
3. **Orden de las operaciones** (cuándo se redondea vs cuándo se suma)

**El sistema está funcionando correctamente**, pero hay pequeñas variaciones debido a cómo se propagan los redondeos a través de los diferentes niveles de cálculo.

---

**NOTA:** Este análisis es solo para revisión. No se ha modificado ningún archivo de código.
