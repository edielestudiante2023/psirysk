# Documentación: Implementación del Mapa de Calor con Cálculos Detallados

## Contexto del Proyecto
Sistema PsyRisk - Evaluación de Riesgo Psicosocial basado en la Resolución 2404/2019 del Ministerio de Trabajo de Colombia.

## Objetivo del Módulo
Crear una vista dedicada que muestre el Mapa de Calor Global de Riesgo Psicosocial con transparencia total de los cálculos matemáticos, baremos aplicados, y metodología utilizada para determinar cada color/nivel de riesgo.

---

## 1. FUNDAMENTOS METODOLÓGICOS

### 1.1 Normativa Aplicable
- **Resolución 2404 de 2019** - Ministerio de Trabajo de Colombia
- Batería de instrumentos para evaluación de factores de riesgo psicosocial

### 1.2 Componentes de Evaluación
1. **Intralaboral** (4 dominios, 19 dimensiones)
   - Forma A: 123 preguntas (jefes, profesionales, técnicos)
   - Forma B: 97 preguntas (auxiliares, operarios)
2. **Extralaboral** (7 dimensiones, 31 preguntas)
3. **Estrés** (31 preguntas)

### 1.3 Niveles de Riesgo (5 categorías)
- `sin_riesgo` → Verde claro (#90EE90)
- `riesgo_bajo` → Amarillo (#FFFF00)
- `riesgo_medio` → Naranja (#FFA500)
- `riesgo_alto` → Rojo (#FF4444)
- `riesgo_muy_alto` → Rojo oscuro (#CC0000)

### 1.4 Método de Cálculo: PROMEDIO + BAREMO
**IMPORTANTE**: NO usar moda (valor más frecuente). Usar promedio aritmético de puntajes brutos y luego aplicar baremos oficiales.

```
Nivel de Riesgo = aplicar_baremo(promedio(puntajes_brutos))
```

---

## 2. ARQUITECTURA DE LA SOLUCIÓN

### 2.1 Estructura de Archivos

```
app/
├── Config/
│   └── Routes.php                          # Definición de rutas
├── Controllers/
│   ├── BatteryServiceController.php        # Cálculo heatmap original
│   └── ReportsController.php               # Controlador de informes + heatmap detallado
├── Models/
│   ├── BatteryServiceModel.php
│   ├── CalculatedResultModel.php           # Resultados calculados por trabajador
│   └── WorkerModel.php
└── Views/
    ├── battery_services/
    │   └── view.php                        # Vista principal servicio (con botón)
    └── reports/
        └── heatmap_detail.php              # Vista dedicada mapa de calor

writable/
└── logs/
    └── log-YYYY-MM-DD.log                  # Logs de errores
```

### 2.2 Flujo de Datos

```
1. Usuario hace clic en "Mapa de Calor" → /reports/heatmap/{serviceId}
2. ReportsController::heatmap($serviceId)
   ├── Verificar acceso (checkAccess)
   ├── Obtener resultados de calculated_results
   ├── Calcular heatmap detallado (calculateHeatmapWithDetails)
   └── Renderizar vista con datos
3. Vista muestra:
   ├── Heatmap visual con colores
   ├── Cards de cálculo por componente
   └── Baremos aplicados con rangos
```

---

## 3. IMPLEMENTACIÓN PASO A PASO

### PASO 1: Agregar Ruta en Routes.php

**Archivo**: `app/Config/Routes.php`

```php
// Rutas de Informes con Segmentadores
$routes->group('reports', function($routes) {
    // Mapa de Calor Global
    $routes->get('heatmap/(:num)', 'ReportsController::heatmap/$1');

    // ... otras rutas
});
```

**Ubicación**: Dentro del grupo `reports`, como primera ruta del grupo.

---

### PASO 2: Agregar Botón en Vista Principal

**Archivo**: `app/Views/battery_services/view.php`

**Ubicación**: Dentro del card "Informes Globales", junto a otros botones.

```php
<!-- Card: Informes Globales -->
<div class="col-md-6">
    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="card-title">
                <i class="fas fa-chart-bar text-primary me-2"></i>
                Informes Globales
            </h5>

            <!-- AGREGAR ESTE BOTÓN -->
            <a href="<?= base_url('reports/heatmap/' . $service['id']) ?>"
               class="btn btn-info btn-sm text-white">
                <i class="fas fa-th me-2"></i>Mapa de Calor
            </a>

            <!-- Otros botones... -->
        </div>
    </div>
</div>
```

---

### PASO 3: Crear Método en ReportsController

**Archivo**: `app/Controllers/ReportsController.php`

#### 3.1 Método Principal: `heatmap()`

```php
/**
 * Vista dedicada al Mapa de Calor con información de cálculos detallados
 */
public function heatmap($serviceId)
{
    // Verificar acceso del usuario
    $service = $this->checkAccess($serviceId);

    // IMPORTANTE: checkAccess() puede devolver:
    // - RedirectResponse (si no tiene permiso)
    // - View (si servicio en progreso)
    // - Array $service (si todo OK)
    if ($service instanceof \CodeIgniter\HTTP\RedirectResponse ||
        $service instanceof \CodeIgniter\View\View ||
        is_string($service)) {
        return $service;
    }

    // Obtener todos los resultados calculados
    $results = $this->calculatedResultModel
        ->select('calculated_results.*, workers.name, workers.document')
        ->join('workers', 'workers.id = calculated_results.worker_id')
        ->where('calculated_results.battery_service_id', $serviceId)
        ->findAll();

    if (empty($results)) {
        return redirect()->back()->with('error', 'No hay resultados calculados para este servicio');
    }

    // Calcular heatmap con datos detallados
    $heatmapCalculations = $this->calculateHeatmapWithDetails($results);

    $data = [
        'title' => 'Mapa de Calor - Riesgo Psicosocial Global',
        'service' => $service,
        'results' => $results,
        'totalWorkers' => count($results),
        'heatmapCalculations' => $heatmapCalculations,
    ];

    return view('reports/heatmap_detail', $data);
}
```

**⚠️ ERROR COMÚN**: No verificar correctamente el tipo de retorno de `checkAccess()`. Si solo haces `if ($accessCheck)` y `checkAccess()` devuelve un array, el array será truthy y se devolverá, causando una página en blanco.

#### 3.2 Método de Cálculo: `calculateHeatmapWithDetails()`

```php
/**
 * Calcular heatmap con información detallada de baremos y promedios
 */
private function calculateHeatmapWithDetails($results)
{
    if (empty($results)) {
        return null;
    }

    // Determinar tipo de forma predominante (A o B)
    $formasCounts = array_count_values(array_column($results, 'intralaboral_form_type'));
    $formaType = ($formasCounts['A'] ?? 0) >= ($formasCounts['B'] ?? 0) ? 'A' : 'B';

    // BAREMOS OFICIALES - Resolución 2404/2019

    // Tabla 33: Intralaboral Total
    $baremoIntralaboralTotal = $formaType === 'A'
        ? [
            'sin_riesgo' => [0.0, 13.5],
            'riesgo_bajo' => [13.6, 17.7],
            'riesgo_medio' => [17.8, 22.9],
            'riesgo_alto' => [23.0, 29.2],
            'riesgo_muy_alto' => [29.3, 100.0]
        ]
        : [
            'sin_riesgo' => [0.0, 17.0],
            'riesgo_bajo' => [17.1, 22.6],
            'riesgo_medio' => [22.7, 28.7],
            'riesgo_alto' => [28.8, 34.8],
            'riesgo_muy_alto' => [34.9, 100.0]
        ];

    // Tabla 31: Dominios (iguales para A y B)
    $baremoDominios = [
        'liderazgo' => [
            'sin_riesgo' => [0.0, 3.8],
            'riesgo_bajo' => [3.9, 9.5],
            'riesgo_medio' => [9.6, 17.7],
            'riesgo_alto' => [17.8, 28.5],
            'riesgo_muy_alto' => [28.6, 100.0]
        ],
        'control' => [
            'sin_riesgo' => [0.0, 7.9],
            'riesgo_bajo' => [8.0, 12.5],
            'riesgo_medio' => [12.6, 17.0],
            'riesgo_alto' => [17.1, 22.9],
            'riesgo_muy_alto' => [23.0, 100.0]
        ],
        'demandas' => [
            'sin_riesgo' => [0.0, 28.6],
            'riesgo_bajo' => [28.7, 33.3],
            'riesgo_medio' => [33.4, 37.4],
            'riesgo_alto' => [37.5, 42.9],
            'riesgo_muy_alto' => [43.0, 100.0]
        ],
        'recompensas' => [
            'sin_riesgo' => [0.0, 5.0],
            'riesgo_bajo' => [5.1, 10.0],
            'riesgo_medio' => [10.1, 15.0],
            'riesgo_alto' => [15.1, 20.0],
            'riesgo_muy_alto' => [20.1, 100.0]
        ]
    ];

    // Tabla 18: Extralaboral
    $baremoExtralaboral = [
        'sin_riesgo' => [0.0, 11.3],
        'riesgo_bajo' => [11.4, 13.9],
        'riesgo_medio' => [14.0, 17.9],
        'riesgo_alto' => [18.0, 22.6],
        'riesgo_muy_alto' => [22.7, 100.0]
    ];

    // Tabla 6: Estrés
    $baremoEstres = [
        'sin_riesgo' => [0.0, 7.8],
        'riesgo_bajo' => [7.9, 12.6],
        'riesgo_medio' => [12.7, 17.7],
        'riesgo_alto' => [17.8, 25.0],
        'riesgo_muy_alto' => [25.1, 100.0]
    ];

    // Función helper para calcular detalle con promedio + baremo
    $calculateDetail = function($field, $baremo) use ($results) {
        $puntajes = array_filter(array_column($results, $field), function($v) {
            return $v !== null && $v !== '';
        });

        if (empty($puntajes)) {
            return [
                'promedio' => 0,
                'nivel' => 'sin_riesgo',
                'cantidad' => 0,
                'puntajes' => [],
                'suma' => 0,
                'baremo' => $baremo,
                'rango_aplicado' => [0, 0]
            ];
        }

        // PASO 1: Calcular promedio aritmético
        $suma = array_sum($puntajes);
        $cantidad = count($puntajes);
        $promedio = $suma / $cantidad;

        // PASO 2: Aplicar baremo para determinar nivel
        $nivel = 'sin_riesgo';
        foreach ($baremo as $nivelKey => $rango) {
            if ($promedio >= $rango[0] && $promedio <= $rango[1]) {
                $nivel = $nivelKey;
                break;
            }
        }

        return [
            'promedio' => round($promedio, 2),
            'nivel' => $nivel,
            'cantidad' => $cantidad,
            'puntajes' => $puntajes,
            'suma' => $suma,
            'baremo' => $baremo,
            'rango_aplicado' => $baremo[$nivel] ?? [0, 0]
        ];
    };

    // Calcular para todos los componentes
    return [
        'forma_type' => $formaType,

        // INTRALABORAL
        'intralaboral_total' => $calculateDetail('intralaboral_total_puntaje', $baremoIntralaboralTotal),
        'dom_liderazgo' => $calculateDetail('dom_liderazgo_puntaje', $baremoDominios['liderazgo']),
        'dom_control' => $calculateDetail('dom_control_puntaje', $baremoDominios['control']),
        'dom_demandas' => $calculateDetail('dom_demandas_puntaje', $baremoDominios['demandas']),
        'dom_recompensas' => $calculateDetail('dom_recompensas_puntaje', $baremoDominios['recompensas']),

        // EXTRALABORAL
        'extralaboral_total' => $calculateDetail('extralaboral_total_puntaje', $baremoExtralaboral),

        // ESTRÉS
        'estres_total' => $calculateDetail('estres_total_puntaje', $baremoEstres),
    ];
}
```

**Puntos Clave**:
1. Determinar forma A o B por mayoría
2. Usar baremos oficiales exactos de las tablas
3. Calcular promedio aritmético, NO moda
4. Devolver datos completos para transparencia

---

### PASO 4: Crear Vista Dedicada

**Archivo**: `app/Views/reports/heatmap_detail.php`

#### 4.1 Estructura HTML Base

```html
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Estilos del heatmap visual */
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <!-- Header -->
        <!-- Heatmap Visual -->
        <!-- Cards de Cálculos Detallados -->
        <!-- Nota Metodológica -->
    </div>
</body>
</html>
```

#### 4.2 Función Helper para Mapear Colores

```php
<?php
function getNivelColor($nivel) {
    $colors = [
        'sin_riesgo' => '#90EE90',
        'riesgo_bajo' => '#FFFF00',
        'riesgo_medio' => '#FFA500',
        'riesgo_alto' => '#FF4444',
        'riesgo_muy_alto' => '#CC0000'
    ];
    return $colors[$nivel] ?? '#D3D3D3';
}

function getNivelTexto($nivel) {
    $textos = [
        'sin_riesgo' => 'Sin Riesgo',
        'riesgo_bajo' => 'Riesgo Bajo',
        'riesgo_medio' => 'Riesgo Medio',
        'riesgo_alto' => 'Riesgo Alto',
        'riesgo_muy_alto' => 'Riesgo Muy Alto'
    ];
    return $textos[$nivel] ?? 'Sin datos';
}
?>
```

#### 4.3 Heatmap Visual con Flexbox

```html
<!-- Mapa de Calor Visual -->
<div class="heatmap-container">
    <!-- Leyenda -->
    <div class="heatmap-legend">
        <span class="legend-item">
            <span class="legend-dot" style="background: #90EE90;"></span>Sin Riesgo
        </span>
        <span class="legend-item">
            <span class="legend-dot" style="background: #FFFF00;"></span>Riesgo Bajo
        </span>
        <span class="legend-item">
            <span class="legend-dot" style="background: #FFA500;"></span>Riesgo Medio
        </span>
        <span class="legend-item">
            <span class="legend-dot" style="background: #FF4444;"></span>Riesgo Alto
        </span>
        <span class="legend-item">
            <span class="legend-dot" style="background: #CC0000;"></span>Riesgo Muy Alto
        </span>
    </div>

    <!-- SECCIÓN INTRALABORAL -->
    <div class="heatmap-intralaboral">
        <!-- Total Intralaboral (20% izquierda) -->
        <div class="heatmap-total-left"
             style="background-color: <?= getNivelColor($heatmapCalculations['intralaboral_total']['nivel']) ?>">
            TOTAL INTRALABORAL<br>
            (Forma <?= $heatmapCalculations['forma_type'] ?>)
        </div>

        <!-- Dominios y Dimensiones (80% derecha) -->
        <div class="heatmap-domains-dimensions">
            <!-- DOMINIO 1: LIDERAZGO -->
            <div class="domain-row">
                <div class="domain-cell"
                     style="background-color: <?= getNivelColor($heatmapCalculations['dom_liderazgo']['nivel']) ?>">
                    LIDERAZGO Y<br>RELACIONES SOCIALES
                </div>
                <div class="dimensions-cell">
                    <!-- Dimensiones del dominio liderazgo -->
                    <div class="heatmap-dimension"
                         style="background-color: <?= getNivelColor($heatmapCalculations['dim_caracteristicas_liderazgo']['nivel']) ?>">
                        Características del liderazgo
                    </div>
                    <!-- ... más dimensiones -->
                </div>
            </div>

            <!-- DOMINIO 2, 3, 4... -->
        </div>
    </div>

    <!-- SECCIÓN EXTRALABORAL -->
    <div class="heatmap-extralaboral">
        <!-- Similar estructura -->
    </div>

    <!-- SECCIÓN ESTRÉS -->
    <div class="heatmap-estres">
        <!-- Similar estructura -->
    </div>
</div>
```

**CSS Crítico para Flexbox**:

```css
.heatmap-intralaboral {
    display: flex;
    border-bottom: 2px solid #000;
}

.heatmap-total-left {
    flex: 0 0 20%;  /* 20% fijo */
    padding: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    border-right: 2px solid #000;
}

.heatmap-domains-dimensions {
    flex: 1;  /* 80% restante */
    display: flex;
    flex-direction: column;
}

.domain-row {
    display: flex;
    border-bottom: 1px solid #999;
}

.domain-cell {
    flex: 0 0 30%;  /* 30% del 80% */
    padding: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    border-right: 2px solid #000;
}

.dimensions-cell {
    flex: 1;  /* 70% del 80% */
    display: flex;
    flex-direction: column;
}

.heatmap-dimension {
    padding: 8px 12px;
    font-size: 11px;
    border-bottom: 1px solid #999;
    min-height: 35px;
    display: flex;
    align-items: center;
}
```

#### 4.4 Cards de Cálculos Detallados

```html
<!-- Cálculos Detallados -->
<div class="row mt-5">
    <div class="col-12">
        <h3 class="mb-4">
            <i class="fas fa-calculator me-2"></i>
            Cálculos Detallados y Aplicación de Baremos
        </h3>
    </div>
</div>

<div class="row">
    <?php
    // Función para renderizar card de cálculo
    function renderCalculationDetail($title, $data, $icon, $color) {
        ?>
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-<?= $color ?> text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-<?= $icon ?> me-2"></i>
                        <?= $title ?>
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Resultado -->
                    <div class="alert alert-info mb-3">
                        <strong>Nivel de Riesgo:</strong>
                        <span class="badge"
                              style="background-color: <?= getNivelColor($data['nivel']) ?>; color: #000;">
                            <?= strtoupper(str_replace('_', ' ', $data['nivel'])) ?>
                        </span>
                    </div>

                    <!-- Fórmula de Cálculo -->
                    <div class="formula-box mb-3">
                        <h6><i class="fas fa-calculator me-2"></i>Cálculo del Promedio:</h6>
                        <p class="mb-1">
                            <strong>1. Suma de puntajes:</strong>
                            <?= number_format($data['suma'], 2) ?>
                        </p>
                        <p class="mb-1">
                            <strong>2. Cantidad de trabajadores:</strong>
                            <?= $data['cantidad'] ?>
                        </p>
                        <p class="mb-0">
                            <strong>3. Promedio aritmético:</strong>
                            <?= $data['suma'] ?> ÷ <?= $data['cantidad'] ?> =
                            <strong class="text-primary"><?= $data['promedio'] ?></strong>
                        </p>
                    </div>

                    <!-- Tabla de Baremo -->
                    <h6><i class="fas fa-table me-2"></i>Baremo Aplicado:</h6>
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Nivel de Riesgo</th>
                                <th>Rango de Puntaje</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['baremo'] as $nivel => $rango): ?>
                            <tr class="<?= $nivel === $data['nivel'] ? 'table-success fw-bold' : '' ?>">
                                <td><?= ucfirst(str_replace('_', ' ', $nivel)) ?></td>
                                <td><?= $rango[0] ?> - <?= $rango[1] ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- Explicación -->
                    <div class="alert alert-warning mb-0">
                        <small>
                            <i class="fas fa-info-circle me-1"></i>
                            <strong>Interpretación:</strong>
                            El promedio <strong><?= $data['promedio'] ?></strong>
                            se encuentra en el rango
                            <strong>[<?= $data['rango_aplicado'][0] ?> - <?= $data['rango_aplicado'][1] ?>]</strong>,
                            correspondiente a <strong><?= strtoupper(str_replace('_', ' ', $data['nivel'])) ?></strong>.
                        </small>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    // Renderizar cards
    renderCalculationDetail(
        'Total Intralaboral (Forma ' . $heatmapCalculations['forma_type'] . ')',
        $heatmapCalculations['intralaboral_total'],
        'briefcase',
        'primary'
    );

    renderCalculationDetail(
        'Dominio: Liderazgo y Relaciones Sociales',
        $heatmapCalculations['dom_liderazgo'],
        'users-cog',
        'info'
    );

    // ... más cards para cada componente
    ?>
</div>
```

#### 4.5 Nota Metodológica

```html
<!-- Nota Metodológica -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-primary">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-book me-2"></i>
                    Nota Metodológica
                </h5>
            </div>
            <div class="card-body">
                <h6>Método de Cálculo Empleado:</h6>
                <ol>
                    <li>
                        <strong>Recolección de puntajes:</strong>
                        Se obtienen los puntajes brutos de cada trabajador
                        para cada componente evaluado.
                    </li>
                    <li>
                        <strong>Cálculo de promedio aritmético:</strong>
                        Se suman todos los puntajes y se dividen entre
                        el número total de trabajadores.
                        <br>
                        <code>Promedio = Σ puntajes / N trabajadores</code>
                    </li>
                    <li>
                        <strong>Aplicación de baremos oficiales:</strong>
                        El promedio obtenido se compara con los rangos
                        establecidos en las tablas oficiales de la
                        Resolución 2404 de 2019.
                    </li>
                    <li>
                        <strong>Asignación de nivel de riesgo:</strong>
                        Se identifica en qué rango cae el promedio y
                        se asigna el nivel correspondiente.
                    </li>
                    <li>
                        <strong>Representación visual:</strong>
                        Cada nivel se representa con un color específico
                        en el mapa de calor.
                    </li>
                </ol>

                <div class="alert alert-info mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Importante:</strong> Este método garantiza
                    transparencia total en los cálculos y permite
                    rastrear cómo se obtuvo cada nivel de riesgo,
                    cumpliendo con los lineamientos técnicos de la
                    normativa colombiana.
                </div>
            </div>
        </div>
    </div>
</div>
```

---

## 4. ERRORES COMUNES Y SOLUCIONES

### Error 1: Página en Blanco (404 sin contenido)

**Síntoma**: La página carga pero está completamente en blanco, solo con debugbar.

**Causa**: Error en la verificación del retorno de `checkAccess()`.

```php
// ❌ INCORRECTO
$accessCheck = $this->checkAccess($serviceId);
if ($accessCheck) {  // Si devuelve array, esto es TRUE
    return $accessCheck;  // Devuelve array en lugar de vista
}

// ✅ CORRECTO
$service = $this->checkAccess($serviceId);
if ($service instanceof \CodeIgniter\HTTP\RedirectResponse ||
    $service instanceof \CodeIgniter\View\View ||
    is_string($service)) {
    return $service;
}
// Si llega aquí, $service es el array con datos
```

### Error 2: Cálculo por Moda en lugar de Promedio

**Síntoma**: Los niveles no coinciden con lo esperado.

**Causa**: Usar `array_count_values()` y tomar el más frecuente.

```php
// ❌ INCORRECTO (moda)
$counts = array_count_values($levels);
arsort($counts);
return key($counts);

// ✅ CORRECTO (promedio + baremo)
$promedio = array_sum($puntajes) / count($puntajes);
foreach ($baremo as $nivel => $rango) {
    if ($promedio >= $rango[0] && $promedio <= $rango[1]) {
        return $nivel;
    }
}
```

### Error 3: Flexbox No Alinea Correctamente

**Síntoma**: Dominios y dimensiones no se alinean visualmente.

**Solución**: Usar estructura de 3 niveles con flex correcto:

```css
/* Nivel 1: Contenedor principal */
.heatmap-intralaboral {
    display: flex;
}

/* Nivel 2: Total (20%) + Dominios-Dims (80%) */
.heatmap-total-left {
    flex: 0 0 20%;
}
.heatmap-domains-dimensions {
    flex: 1;
    display: flex;
    flex-direction: column;
}

/* Nivel 3: Cada fila de dominio */
.domain-row {
    display: flex;
}
.domain-cell {
    flex: 0 0 30%;  /* 30% del 80% */
}
.dimensions-cell {
    flex: 1;  /* 70% del 80% */
}
```

### Error 4: Baremos Incorrectos

**Síntoma**: Colores no coinciden con evaluaciones oficiales.

**Solución**: Usar EXACTAMENTE los baremos de las tablas oficiales:
- Tabla 33: Intralaboral Total (diferente para A y B)
- Tabla 31: Dominios (igual para A y B)
- Tabla 29: Dimensiones Forma A
- Tabla 27: Dimensiones Forma B
- Tabla 18: Extralaboral
- Tabla 6: Estrés

### Error 5: Rutas No se Reconocen

**Síntoma**: 404 en todas las rutas del grupo.

**Verificar**:
1. Que `autoRoute = false` en `app/Config/Routing.php`
2. Que la ruta esté DENTRO del grupo correcto
3. Que el patrón sea correcto: `heatmap/(:num)` no `heatmap/(:segment)`
4. Limpiar cache: `del /Q "C:\xampp\htdocs\psyrisk\writable\cache\*"`

---

## 5. CHECKLIST DE IMPLEMENTACIÓN

Para implementar un módulo similar (ej: análisis de dominios, dimensiones, etc.):

### ☐ 1. Planificación
- [ ] Definir objetivo del módulo
- [ ] Identificar baremos aplicables
- [ ] Definir estructura de datos a mostrar
- [ ] Diseñar layout visual (wireframe)

### ☐ 2. Backend
- [ ] Agregar ruta en `Routes.php` dentro del grupo correcto
- [ ] Crear método en `ReportsController` o controlador apropiado
- [ ] Implementar método `checkAccess()` con verificación correcta de tipos
- [ ] Crear método de cálculo con baremos oficiales
- [ ] Usar promedio aritmético, NO moda
- [ ] Devolver datos completos (promedio, suma, cantidad, baremo, rango)

### ☐ 3. Vista
- [ ] Crear archivo en `app/Views/reports/`
- [ ] Implementar header con breadcrumbs y título
- [ ] Crear sección visual (gráfico/heatmap/dashboard)
- [ ] Implementar cards de cálculos detallados
- [ ] Mostrar baremos aplicados en tablas
- [ ] Agregar nota metodológica
- [ ] Botón "Volver" o navegación

### ☐ 4. Estilos
- [ ] Definir colores consistentes con niveles de riesgo
- [ ] Usar flexbox para layouts complejos
- [ ] Responsive design (col-md-6, col-lg-4, etc.)
- [ ] Tablas con bordes claros
- [ ] Badges con colores de nivel

### ☐ 5. Integración
- [ ] Agregar botón en vista principal (`battery_services/view.php`)
- [ ] Probar acceso como consultor
- [ ] Probar acceso como cliente (servicio cerrado)
- [ ] Verificar redirecciones y permisos

### ☐ 6. Pruebas
- [ ] Verificar cálculos manualmente con Excel
- [ ] Comparar con baremos oficiales
- [ ] Probar con servicios con 1, 5, 50, 100+ trabajadores
- [ ] Verificar con Forma A y Forma B
- [ ] Probar casos edge (sin datos, todos mismo nivel, etc.)

### ☐ 7. Documentación
- [ ] Comentar código complejo
- [ ] Documentar baremos utilizados
- [ ] Agregar ejemplo de uso en comments
- [ ] Actualizar este archivo .md si es necesario

---

## 6. PLANTILLA PARA NUEVOS MÓDULOS

### Estructura de Método en Controller

```php
/**
 * [Nombre del Módulo] - [Descripción breve]
 *
 * Baremos aplicados:
 * - Tabla X: [Descripción]
 * - Tabla Y: [Descripción]
 */
public function nombreModulo($serviceId)
{
    // 1. Verificar acceso
    $service = $this->checkAccess($serviceId);
    if ($service instanceof \CodeIgniter\HTTP\RedirectResponse ||
        $service instanceof \CodeIgniter\View\View ||
        is_string($service)) {
        return $service;
    }

    // 2. Obtener datos necesarios
    $results = $this->calculatedResultModel
        ->where('battery_service_id', $serviceId)
        ->findAll();

    if (empty($results)) {
        return redirect()->back()->with('error', 'No hay resultados');
    }

    // 3. Procesar datos con método privado
    $datosCalculados = $this->calcularDatosModulo($results);

    // 4. Preparar data para vista
    $data = [
        'title' => 'Título del Módulo',
        'service' => $service,
        'results' => $results,
        'datosCalculados' => $datosCalculados,
    ];

    // 5. Renderizar vista
    return view('reports/nombre_modulo', $data);
}

/**
 * Método privado de cálculo
 */
private function calcularDatosModulo($results)
{
    // Baremos oficiales
    $baremo = [
        'sin_riesgo' => [0.0, 10.0],
        'riesgo_bajo' => [10.1, 20.0],
        // ...
    ];

    // Función de cálculo
    $calcular = function($field, $baremo) use ($results) {
        $puntajes = array_filter(array_column($results, $field));
        if (empty($puntajes)) {
            return ['promedio' => 0, 'nivel' => 'sin_riesgo', /* ... */];
        }

        $promedio = array_sum($puntajes) / count($puntajes);

        $nivel = 'sin_riesgo';
        foreach ($baremo as $nivelKey => $rango) {
            if ($promedio >= $rango[0] && $promedio <= $rango[1]) {
                $nivel = $nivelKey;
                break;
            }
        }

        return [
            'promedio' => round($promedio, 2),
            'nivel' => $nivel,
            'cantidad' => count($puntajes),
            'suma' => array_sum($puntajes),
            'baremo' => $baremo,
            'rango_aplicado' => $baremo[$nivel]
        ];
    };

    // Retornar datos calculados
    return [
        'componente1' => $calcular('campo1', $baremo),
        'componente2' => $calcular('campo2', $baremo),
        // ...
    ];
}
```

---

## 7. RECURSOS Y REFERENCIAS

### Documentación Oficial
- Resolución 2404 de 2019 - Ministerio de Trabajo
- Manual de uso de la Batería de Riesgo Psicosocial
- Tablas de baremos oficiales (Tablas 1-35)

### Estructura de Base de Datos

**Tabla**: `calculated_results`

Campos relevantes:
- `worker_id` - ID del trabajador
- `battery_service_id` - ID del servicio
- `intralaboral_form_type` - 'A' o 'B'
- `intralaboral_total_puntaje` - Puntaje total intralaboral
- `dom_liderazgo_puntaje` - Dominio liderazgo
- `dom_control_puntaje` - Dominio control
- `dom_demandas_puntaje` - Dominio demandas
- `dom_recompensas_puntaje` - Dominio recompensas
- `dim_[nombre]_puntaje` - Puntajes de dimensiones (19 para A, 16 para B)
- `extralaboral_total_puntaje` - Puntaje extralaboral
- `estres_total_puntaje` - Puntaje estrés

### Convenciones de Código
- Nombres de métodos: camelCase
- Nombres de variables: snake_case para campos de BD, camelCase para variables PHP
- Comentarios: PHPDoc para métodos públicos
- Indentación: 4 espacios

---

## 8. SIGUIENTES PASOS (ROADMAP)

Módulos pendientes que seguirán esta misma estructura:

1. **Análisis de Dominios Intralaboral** (4 dominios)
   - Vista dedicada con gráficos de barras
   - Cálculos por dominio
   - Distribución por trabajador

2. **Análisis de Dimensiones Intralaboral** (19 dims A / 16 dims B)
   - Tabla detallada
   - Gráficos radar
   - Top 5 dimensiones críticas

3. **Análisis Extralaboral** (7 dimensiones)
   - Vista similar a intralaboral
   - Correlaciones con intralaboral

4. **Análisis de Estrés**
   - Gráfico de distribución
   - Síntomas más frecuentes
   - Correlación con factores de riesgo

5. **Recomendaciones y Plan de Intervención**
   - Generación automática basada en niveles
   - Priorización de acciones
   - Timeline sugerido

---

## 9. CONTACTO Y MANTENIMIENTO

**Desarrollador Original**: Claude (Anthropic)
**Fecha Creación**: 2025-11-21
**Última Actualización**: 2025-11-21
**Versión Sistema**: PsyRisk v1.0

Para dudas o mejoras, consultar este documento y seguir la estructura establecida.

---

**FIN DE LA DOCUMENTACIÓN**
