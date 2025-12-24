# Auditoría de Discrepancia entre Mapas de Calor

## Fecha: 2025-12-15
## Servicio Analizado: ID 2 - RPS CYCLOID 2025

---

## 1. CONTEXTO DEL PROBLEMA

Se detectaron discrepancias entre dos vistas que muestran mapas de calor del mismo servicio:

| Vista | URL | Controlador |
|-------|-----|-------------|
| **Heatmap Web** | `/reports/heatmap/2` | `ReportsController::heatmap()` |
| **PDF Ejecutivo** | `/pdfejecutivo/preview/mapas-calor/2` | `MapasCalorController::preview()` |

---

## 2. DATOS CRUDOS DEL SERVICIO 2

### 2.1 Trabajadores Evaluados

| Worker ID | Nombre | Forma | Tipo Cargo |
|-----------|--------|-------|------------|
| 7 | DIANA PATRICIA CUESTAS NAVIA | A | Jefe/Profesional/Técnico |
| 8 | SOLANGEL CUERVO PERDOMO | B | Auxiliar/Operario |
| 9 | ELEYSON AUGUSTO SEGURA ANACONA | A | Jefe/Profesional/Técnico |
| 10 | EDISON ERNESTO CUERVO SALAZAR | A | Jefe/Profesional/Técnico |

**Distribución:** 3 Forma A + 1 Forma B = 4 trabajadores

### 2.2 Puntajes y Niveles Almacenados en Base de Datos

#### INTRALABORAL TOTAL

| Worker | Forma | Puntaje Transformado | Nivel (BD) |
|--------|-------|---------------------|------------|
| 7 | A | 8.10 | sin_riesgo |
| 9 | A | 12.60 | sin_riesgo |
| 10 | A | 25.00 | riesgo_bajo |
| 8 | B | 16.80 | sin_riesgo |

#### EXTRALABORAL TOTAL

| Worker | Forma | Puntaje Transformado | Nivel (BD) |
|--------|-------|---------------------|------------|
| 7 | A | 17.70 | riesgo_medio |
| 9 | A | 15.30 | riesgo_bajo |
| 10 | A | 25.80 | riesgo_alto |
| 8 | B | 21.80 | riesgo_medio |

#### ESTRÉS TOTAL

| Worker | Forma | Puntaje Transformado | Nivel (BD) |
|--------|-------|---------------------|------------|
| 7 | A | 1.60 | muy_bajo |
| 9 | A | 17.40 | medio |
| 10 | A | 22.90 | alto |
| 8 | B | 19.30 | alto |

---

## 3. BAREMOS OFICIALES (Resolución 2404/2019)

### 3.1 Intralaboral Total - Tabla 33

#### Forma A (Jefes, Profesionales, Técnicos)
| Nivel | Rango |
|-------|-------|
| Sin riesgo | 0.0 - 19.7 |
| Riesgo bajo | 19.8 - 25.8 |
| Riesgo medio | 25.9 - 31.5 |
| Riesgo alto | 31.6 - 38.0 |
| Riesgo muy alto | 38.1 - 100.0 |

#### Forma B (Auxiliares, Operarios)
| Nivel | Rango |
|-------|-------|
| Sin riesgo | 0.0 - 20.6 |
| Riesgo bajo | 20.7 - 26.0 |
| Riesgo medio | 26.1 - 31.2 |
| Riesgo alto | 31.3 - 38.7 |
| Riesgo muy alto | 38.8 - 100.0 |

**NOTA CRÍTICA:** Los baremos son DIFERENTES para cada forma. No se pueden mezclar.

---

## 4. ANÁLISIS DEL MÉTODO 1: ReportsController::calculateHeatmapWithDetails()

### 4.1 Ubicación del Código
- **Archivo:** `app/Controllers/ReportsController.php`
- **Línea:** ~1455

### 4.2 Descripción del Algoritmo

```php
// Paso 1: Obtener TODOS los resultados (Forma A + B mezclados)
$results = $this->calculatedResultModel
    ->where('battery_service_id', $serviceId)
    ->findAll();

// Paso 2: Determinar forma "predominante"
$formasCounts = array_count_values(array_column($results, 'intralaboral_form_type'));
$formaType = ($formasCounts['A'] ?? 0) >= ($formasCounts['B'] ?? 0) ? 'A' : 'B';

// Paso 3: Seleccionar baremo según forma predominante
$baremoIntralaboralTotal = $formaType === 'A'
    ? IntralaboralAScoring::getBaremoTotal()
    : IntralaboralBScoring::getBaremoTotal();

// Paso 4: Calcular PROMEDIO de TODOS los puntajes
$puntajes = array_column($results, 'intralaboral_total_puntaje');
$promedio = array_sum($puntajes) / count($puntajes);

// Paso 5: Aplicar el baremo al promedio
foreach ($baremo as $nivelKey => $rango) {
    if ($promedio >= $rango[0] && $promedio <= $rango[1]) {
        $nivel = $nivelKey;
        break;
    }
}
```

### 4.3 Cálculo Matemático Aplicado

**INTRALABORAL TOTAL:**

```
Puntajes: [8.10, 12.60, 25.00, 16.80]  ← Mezcla Forma A + Forma B

Suma: 8.10 + 12.60 + 25.00 + 16.80 = 62.50

Promedio: 62.50 / 4 = 15.625

Forma predominante: A (3 vs 1)

Baremo aplicado (Forma A):
  Sin riesgo: 0.0 - 19.7  ← 15.625 CAE AQUÍ

RESULTADO: sin_riesgo
```

**EXTRALABORAL TOTAL:**

```
Puntajes: [17.70, 15.30, 25.80, 21.80]  ← Mezcla Forma A + Forma B

Suma: 17.70 + 15.30 + 25.80 + 21.80 = 80.60

Promedio: 80.60 / 4 = 20.15

Baremo Extralaboral Forma A:
  Sin riesgo: 0.0 - 17.9
  Riesgo bajo: 18.0 - 22.6  ← 20.15 CAE AQUÍ

RESULTADO: riesgo_bajo
```

### 4.4 Resultados del Método 1

| Cuestionario | Promedio Calculado | Nivel Mostrado |
|--------------|-------------------|----------------|
| Intralaboral Total | 15.63 | **sin_riesgo** |
| Extralaboral Total | 20.15 | **riesgo_bajo** |

---

## 5. ANÁLISIS DEL MÉTODO 2: MapasCalorController (PDF)

### 5.1 Ubicación del Código
- **Archivo:** `app/Controllers/PdfEjecutivo/MapasCalorController.php`
- **Líneas:** ~230-293

### 5.2 Descripción del Algoritmo

```php
// Paso 1: Consultar niveles PRE-CALCULADOS de la BD
$query = $db->query("
    SELECT
        intralaboral_form_type,
        intralaboral_total_nivel,  -- Ya viene calculado
        extralaboral_total_nivel,
        estres_total_nivel
    FROM calculated_results
    WHERE battery_service_id = ?
", [$batteryServiceId]);

// Paso 2: Contar cuántas personas hay en cada nivel POR FORMA
foreach ($results as $row) {
    $forma = $row['intralaboral_form_type'];
    $nivel = $row['intralaboral_total_nivel'];

    $this->heatmapData['intralaboral'][$forma][$nivel]++;
}
```

### 5.3 Cálculo Matemático Aplicado

**INTRALABORAL TOTAL:**

```
Worker 7 (Forma A): Puntaje 8.10
  Baremo Forma A: 8.10 está en [0.0, 19.7] → sin_riesgo ✓

Worker 9 (Forma A): Puntaje 12.60
  Baremo Forma A: 12.60 está en [0.0, 19.7] → sin_riesgo ✓

Worker 10 (Forma A): Puntaje 25.00
  Baremo Forma A: 25.00 está en [19.8, 25.8] → riesgo_bajo ✓

Worker 8 (Forma B): Puntaje 16.80
  Baremo Forma B: 16.80 está en [0.0, 20.6] → sin_riesgo ✓

CONTEO FINAL:
  Forma A: 2 sin_riesgo, 1 riesgo_bajo, 0 riesgo_medio, 0 riesgo_alto
  Forma B: 1 sin_riesgo, 0 riesgo_bajo, 0 riesgo_medio, 0 riesgo_alto
```

**EXTRALABORAL TOTAL:**

```
Worker 7 (Forma A): Puntaje 17.70
  Baremo Extralaboral A: → riesgo_medio (según BD)

Worker 9 (Forma A): Puntaje 15.30
  Baremo Extralaboral A: → riesgo_bajo (según BD)

Worker 10 (Forma A): Puntaje 25.80
  Baremo Extralaboral A: → riesgo_alto (según BD)

Worker 8 (Forma B): Puntaje 21.80
  Baremo Extralaboral B: → riesgo_medio (según BD)

CONTEO FINAL:
  Forma A: 0 sin_riesgo, 1 riesgo_bajo, 1 riesgo_medio, 1 riesgo_alto
  Forma B: 0 sin_riesgo, 0 riesgo_bajo, 1 riesgo_medio, 0 riesgo_alto
```

### 5.4 Resultados del Método 2

| Cuestionario | Distribución de Niveles |
|--------------|------------------------|
| Intralaboral Total | 3 sin_riesgo + 1 riesgo_bajo |
| Extralaboral Total | 1 riesgo_bajo + 2 riesgo_medio + 1 riesgo_alto |

---

## 6. COMPARACIÓN DE RESULTADOS

### 6.1 Intralaboral Total

| Aspecto | Método 1 (Web Heatmap) | Método 2 (PDF) |
|---------|------------------------|----------------|
| Resultado | **sin_riesgo** | 3 sin_riesgo + 1 riesgo_bajo |
| Lógica | Promedio global | Conteo individual |
| ¿Muestra riesgo_bajo? | NO | SÍ (1 persona) |

### 6.2 Extralaboral Total

| Aspecto | Método 1 (Web Heatmap) | Método 2 (PDF) |
|---------|------------------------|----------------|
| Resultado | **riesgo_bajo** | 1 bajo + 2 medio + 1 alto |
| Lógica | Promedio global | Conteo individual |
| ¿Muestra riesgo_alto? | NO | SÍ (1 persona) |

---

## 7. ANÁLISIS SEGÚN LA CARTILLA OFICIAL

### 7.1 ¿Qué dice la Resolución 2404/2019?

La Batería de Instrumentos para la Evaluación de Factores de Riesgo Psicosocial establece:

1. **Evaluación INDIVIDUAL**: Cada trabajador debe ser evaluado individualmente
2. **Baremos ESPECÍFICOS por forma**: Forma A y Forma B tienen baremos diferentes porque corresponden a poblaciones diferentes
3. **No se promedian poblaciones diferentes**: Los jefes/profesionales y auxiliares/operarios tienen perfiles de riesgo distintos

### 7.2 ¿Cuál método es CORRECTO según la cartilla?

**El Método 2 (PDF) es el CORRECTO** porque:

1. **Evalúa a cada trabajador individualmente** con el baremo correspondiente a su forma
2. **Mantiene separadas las formas A y B** respetando que tienen baremos diferentes
3. **Muestra la distribución real de riesgo** en la población

### 7.3 ¿Por qué el Método 1 (Web) es INCORRECTO?

1. **Mezcla formas A y B**: Promedia puntajes de poblaciones con baremos diferentes
2. **Aplica un solo baremo a todos**: Usa el baremo de la "forma predominante" ignorando la otra
3. **Oculta casos de riesgo**: Al promediar, los casos individuales de riesgo alto se diluyen

---

## 8. EJEMPLO ILUSTRATIVO DEL PROBLEMA

### 8.1 Escenario: Extralaboral con 4 trabajadores

**Datos reales del Servicio 2:**
- Worker 7: 17.70 → riesgo_medio
- Worker 9: 15.30 → riesgo_bajo
- Worker 10: 25.80 → **riesgo_alto** ← ALERTA
- Worker 8: 21.80 → riesgo_medio

**Método 1 (INCORRECTO):**
```
Promedio = (17.70 + 15.30 + 25.80 + 21.80) / 4 = 20.15
Nivel = riesgo_bajo

PROBLEMA: El Worker 10 tiene riesgo_alto y REQUIERE INTERVENCIÓN INMEDIATA,
          pero el promedio lo oculta mostrando solo "riesgo_bajo".
```

**Método 2 (CORRECTO):**
```
Conteo:
- 1 persona en riesgo_bajo
- 2 personas en riesgo_medio
- 1 persona en riesgo_alto ← VISIBLE

VENTAJA: Se identifica claramente que hay 1 persona que requiere
         intervención inmediata por riesgo alto.
```

---

## 9. IMPLICACIONES LEGALES Y DE SALUD OCUPACIONAL

### 9.1 Riesgo de usar el Método 1

Según la Resolución 2764/2022 (que actualiza la 2404/2019):

> "Cuando el nivel de riesgo es **Alto** o **Muy Alto** se requiere intervención
> inmediata y seguimiento epidemiológico."

Si el sistema muestra "riesgo_bajo" (promedio) cuando hay 1 persona en "riesgo_alto":

1. **No se activa el protocolo de intervención inmediata**
2. **El trabajador en riesgo alto no recibe atención**
3. **Posible responsabilidad legal** si ocurre un evento de salud

### 9.2 Por qué el PDF muestra correctamente

El mapa de calor del PDF muestra:
- Cuántas personas hay en cada nivel de riesgo
- Separado por Forma A y Forma B
- Permite identificar los casos que requieren intervención

---

## 10. RECOMENDACIÓN

### 10.1 Acción Correctiva

El método `ReportsController::calculateHeatmapWithDetails()` debe modificarse para:

1. **NO promediar puntajes**
2. **Usar los niveles pre-calculados** de la BD (que ya aplicaron el baremo correcto)
3. **Mostrar conteo de personas** por nivel de riesgo
4. **Separar Forma A y Forma B**

### 10.2 Código Correcto (como lo hace el PDF)

```php
// CORRECTO: Contar cuántas personas hay en cada nivel
$niveles = [
    'sin_riesgo' => 0,
    'riesgo_bajo' => 0,
    'riesgo_medio' => 0,
    'riesgo_alto' => 0,
    'riesgo_muy_alto' => 0,
];

foreach ($results as $row) {
    $nivel = $row['intralaboral_total_nivel']; // Ya viene calculado en BD
    $niveles[$nivel]++;
}

// Resultado: distribución real de riesgo
```

---

## 11. CONCLUSIÓN

| Pregunta | Respuesta |
|----------|-----------|
| ¿Hay discrepancia? | SÍ |
| ¿Cuál es la correcta? | **Método 2 (PDF)** - conteo individual |
| ¿Por qué? | Respeta la evaluación individual y los baremos por forma |
| ¿Riesgo del Método 1? | Oculta casos de riesgo alto que requieren intervención |

---

*Documento generado para auditoría del sistema PSYRISK*
*Resolución de referencia: 2404/2019, actualizada por 2764/2022*
