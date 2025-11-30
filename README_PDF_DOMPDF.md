# PSYRISK - Módulo PDF con DomPDF

## Resumen Ejecutivo

Este documento describe el módulo de generación de PDF usando **DomPDF** para el sistema PSYRISK de evaluación de riesgo psicosocial. El módulo genera informes PDF con **gauges SVG dinámicos** que representan visualmente los niveles de riesgo según los baremos oficiales de la Resolución 2404/2019 de Colombia.

---

## Tabla de Contenidos

1. [Arquitectura General](#arquitectura-general)
2. [Archivo de Prueba Funcional](#archivo-de-prueba-funcional)
3. [Generación de Gauges SVG](#generación-de-gauges-svg)
4. [Consumo de Datos de Base de Datos](#consumo-de-datos-de-base-de-datos)
5. [Baremos Oficiales](#baremos-oficiales)
6. [Estructura de Tablas MySQL](#estructura-de-tablas-mysql)
7. [Consultas SQL Utilizadas](#consultas-sql-utilizadas)
8. [Colores y Niveles de Riesgo](#colores-y-niveles-de-riesgo)
9. [Limitaciones de DomPDF](#limitaciones-de-dompdf)
10. [URLs de Prueba](#urls-de-prueba)

---

## Arquitectura General

```
┌─────────────────────────────────────────────────────────────────┐
│                        PSYRISK PDF Module                        │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌──────────────┐    ┌──────────────┐    ┌──────────────┐       │
│  │   MySQL DB   │───▶│  PHP Script  │───▶│   DomPDF     │       │
│  │              │    │              │    │              │       │
│  │ - calculated │    │ - Consultas  │    │ - HTML→PDF   │       │
│  │   _results   │    │ - Baremos    │    │ - SVG embed  │       │
│  │ - reports    │    │ - Gauges SVG │    │              │       │
│  │ - report_    │    │              │    │              │       │
│  │   sections   │    │              │    │              │       │
│  └──────────────┘    └──────────────┘    └──────────────┘       │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

### Stack Tecnológico

- **PHP 8.x** - Lenguaje backend
- **CodeIgniter 4** - Framework MVC
- **DomPDF** - Librería de generación PDF (`composer require dompdf/dompdf`)
- **MySQL** - Base de datos `psyrisk`
- **XAMPP** - Servidor local (Windows)
- **SVG** - Gráficos vectoriales para gauges

---

## Archivo de Prueba Funcional

### Ubicación
```
c:\xampp\htdocs\psyrisk\public\test_gauge_real.php
```

### URLs de Acceso
```
Preview HTML: http://localhost/psyrisk/public/test_gauge_real.php?battery_id=1
Descarga PDF: http://localhost/psyrisk/public/test_gauge_real.php?battery_id=1&download=1
```

### Parámetros GET Disponibles
| Parámetro | Descripción | Valores | Default |
|-----------|-------------|---------|---------|
| `battery_id` | ID del servicio de batería | Entero positivo | 1 |
| `dominio` | Dominio intralaboral a mostrar | liderazgo, control, demandas, recompensas | liderazgo |
| `forma` | Tipo de formulario | A, B | A |
| `download` | Descargar como PDF | 1 | (preview HTML) |

### Ejemplo Completo
```
http://localhost/psyrisk/public/test_gauge_real.php?battery_id=1&dominio=control&forma=B&download=1
```

---

## Generación de Gauges SVG

### Función Principal: `generateGaugeSvg($value, $baremo)`

Esta función genera un gauge SVG **dinámico** donde cada segmento del arco tiene un tamaño proporcional al rango del baremo.

#### Parámetros de Entrada
```php
$value   // float - Puntaje promedio calculado (0-100)
$baremo  // array - Rangos del baremo para cada nivel de riesgo
```

#### Ejemplo de Baremo
```php
$baremo = [
    'sin_riesgo'      => [0.0, 9.1],
    'riesgo_bajo'     => [9.2, 17.7],
    'riesgo_medio'    => [17.8, 25.6],
    'riesgo_alto'     => [25.7, 34.8],
    'riesgo_muy_alto' => [34.9, 100.0]
];
```

### Geometría del Gauge

```
                    Semicírculo Superior

         0% ────────────────────────────── 100%
        (180°)                            (0°)
           ╲                              ╱
            ╲   VERDE → ROJO            ╱
             ╲                        ╱
              ╲     ┌─────┐         ╱
               ╲    │AGUJA│       ╱
                ╲   └──┬──┘     ╱
                 ╲     │      ╱
                  ╲    │    ╱
                   ╲   │  ╱
                    ╲  │╱
                     ╲│╱
                    CENTRO
                   (100, 90)
```

### Parámetros Geométricos
```php
$cx = 100;           // Centro X del gauge
$cy = 90;            // Centro Y del gauge
$radius = 70;        // Radio del arco exterior
$labelRadius = 50;   // Radio para las etiquetas (interior)
$needleLength = 60;  // Longitud de la aguja ($radius - 10)
```

### Cálculo del Ángulo de la Aguja
```php
// El semicírculo va de 180° (izquierda, valor=0) a 0° (derecha, valor=100)
$percentage = min(100, max(0, $value));
$needleAngleDeg = 180 - ($percentage * 1.8);  // 180° total / 100 = 1.8° por unidad
$needleAngleRad = deg2rad($needleAngleDeg);

// Posición de la punta de la aguja
$needleX = $cx + ($needleLength * cos($needleAngleRad));
$needleY = $cy - ($needleLength * sin($needleAngleRad));  // Negativo porque Y crece hacia abajo en SVG
```

### Cálculo de Puntos en el Arco
```php
$getPoint = function($pct, $r = null) use ($cx, $cy, $radius) {
    $r = $r ?? $radius;
    $angleDeg = 180 - ($pct * 1.8);
    $angleRad = deg2rad($angleDeg);
    return [
        round($cx + $r * cos($angleRad), 2),
        round($cy - $r * sin($angleRad), 2)
    ];
};
```

### Generación de Segmentos SVG
```php
foreach ($levels as $lvl) {
    $startPct = $baremo[$lvl][0];  // Valor inicial del rango
    $endPct = min(100, $baremo[$lvl][1]);  // Valor final del rango

    $p1 = $getPoint($startPct);  // Punto inicial del arco
    $p2 = $getPoint($endPct);    // Punto final del arco

    // Path SVG del arco
    // M = Move to (punto inicial)
    // A = Arc (rx, ry, x-axis-rotation, large-arc-flag, sweep-flag, x, y)
    $pathsSvg .= '<path d="M ' . $p1[0] . ' ' . $p1[1] .
                 ' A ' . $radius . ' ' . $radius .
                 ' 0 0 1 ' . $p2[0] . ' ' . $p2[1] .
                 '" fill="none" stroke="' . $riskColors[$lvl] .
                 '" stroke-width="12"/>';
}
```

### Estructura SVG Completa
```xml
<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="200" height="120" viewBox="0 0 200 120">
    <!-- Fondo gris del arco -->
    <path d="M 30 90 A 70 70 0 0 1 170 90" fill="none" stroke="#E8E8E8" stroke-width="12"/>

    <!-- Segmentos dinámicos según baremo -->
    <path d="M 30 90 A 70 70 0 0 1 46.42 52.33" fill="none" stroke="#4CAF50" stroke-width="12"/>
    <path d="M 46.42 52.33 A 70 70 0 0 1 68.12 31.89" fill="none" stroke="#8BC34A" stroke-width="12"/>
    <!-- ... más segmentos ... -->

    <!-- Etiquetas de cada segmento -->
    <text x="38" y="65" font-family="Arial" font-size="5" text-anchor="middle" fill="#333">SR</text>
    <text x="38" y="70" font-family="Arial" font-size="4" text-anchor="middle" fill="#666">0-9.1</text>
    <!-- ... más etiquetas ... -->

    <!-- Aguja -->
    <line x1="100" y1="90" x2="145.32" y2="42.18"
          stroke="#333" stroke-width="3" stroke-linecap="round"/>

    <!-- Centro de la aguja -->
    <circle cx="100" cy="90" r="8" fill="#333"/>

    <!-- Valor actual -->
    <text x="100" y="115" font-family="Arial, sans-serif" font-size="14" font-weight="bold"
          text-anchor="middle" fill="#F44336">45.5</text>
</svg>
```

### Retorno de la Función
```php
// El SVG se convierte a base64 para embeber en HTML como imagen
return 'data:image/svg+xml;base64,' . base64_encode($svg);
```

### Uso en HTML
```html
<img src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0i..." class="gauge-img" alt="Gauge">
```

---

## Consumo de Datos de Base de Datos

### Conexión PDO
```php
$dbHost = 'localhost';
$dbName = 'psyrisk';
$dbUser = 'root';
$dbPass = '';

$pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
```

### Flujo de Datos

```
┌─────────────────────────────────────────────────────────────────────────┐
│                         FLUJO DE DATOS                                   │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                          │
│  1. ENTRADA: battery_service_id (ej: 1)                                 │
│       │                                                                  │
│       ▼                                                                  │
│  2. CONSULTA: Datos de la empresa                                       │
│       │   SELECT c.name, c.nit, c.city                                  │
│       │   FROM battery_services bs                                       │
│       │   JOIN companies c ON bs.company_id = c.id                      │
│       │   WHERE bs.id = ?                                                │
│       │                                                                  │
│       ▼                                                                  │
│  3. CONSULTA: Resultados calculados                                     │
│       │   SELECT * FROM calculated_results                              │
│       │   WHERE battery_service_id = ?                                  │
│       │   AND intralaboral_form_type = ?                                │
│       │                                                                  │
│       ▼                                                                  │
│  4. PROCESAMIENTO:                                                       │
│       │   - Extraer puntajes del campo específico                       │
│       │   - Calcular promedio                                           │
│       │   - Determinar nivel según baremo                               │
│       │   - Calcular distribución por niveles                           │
│       │                                                                  │
│       ▼                                                                  │
│  5. CONSULTA: Texto IA (opcional)                                       │
│       │   SELECT ai_generated_text FROM report_sections                 │
│       │   WHERE report_id IN (SELECT id FROM reports                    │
│       │                       WHERE battery_service_id = ?)             │
│       │   AND questionnaire_type = 'intralaboral'                       │
│       │   AND section_level = 'domain'                                  │
│       │   AND domain_code = ?                                           │
│       │   AND form_type = ?                                             │
│       │                                                                  │
│       ▼                                                                  │
│  6. GENERACIÓN:                                                          │
│       │   - Crear gauge SVG con baremo y puntaje                        │
│       │   - Construir HTML completo                                     │
│       │   - Renderizar con DomPDF                                       │
│       │                                                                  │
│       ▼                                                                  │
│  7. SALIDA: PDF descargable o preview HTML                              │
│                                                                          │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## Baremos Oficiales

### Estructura de Baremos por Forma y Dominio

Los baremos están definidos según la **Resolución 2404/2019** del Ministerio del Trabajo de Colombia.

```php
$baremosDominios = [
    'A' => [  // Jefes, Profesionales y Técnicos
        'liderazgo' => [
            'sin_riesgo'      => [0.0, 9.1],
            'riesgo_bajo'     => [9.2, 17.7],
            'riesgo_medio'    => [17.8, 25.6],
            'riesgo_alto'     => [25.7, 34.8],
            'riesgo_muy_alto' => [34.9, 100.0]
        ],
        'control' => [
            'sin_riesgo'      => [0.0, 10.7],
            'riesgo_bajo'     => [10.8, 19.0],
            'riesgo_medio'    => [19.1, 29.8],
            'riesgo_alto'     => [29.9, 40.5],
            'riesgo_muy_alto' => [40.6, 100.0]
        ],
        'demandas' => [
            'sin_riesgo'      => [0.0, 28.5],
            'riesgo_bajo'     => [28.6, 35.0],
            'riesgo_medio'    => [35.1, 41.5],
            'riesgo_alto'     => [41.6, 47.5],
            'riesgo_muy_alto' => [47.6, 100.0]
        ],
        'recompensas' => [
            'sin_riesgo'      => [0.0, 4.5],
            'riesgo_bajo'     => [4.6, 11.4],
            'riesgo_medio'    => [11.5, 20.5],
            'riesgo_alto'     => [20.6, 29.5],
            'riesgo_muy_alto' => [29.6, 100.0]
        ],
    ],
    'B' => [  // Auxiliares y Operarios
        'liderazgo' => [
            'sin_riesgo'      => [0.0, 8.3],
            'riesgo_bajo'     => [8.4, 17.5],
            'riesgo_medio'    => [17.6, 26.7],
            'riesgo_alto'     => [26.8, 38.3],
            'riesgo_muy_alto' => [38.4, 100.0]
        ],
        'control' => [
            'sin_riesgo'      => [0.0, 19.4],
            'riesgo_bajo'     => [19.5, 26.4],
            'riesgo_medio'    => [26.5, 34.7],
            'riesgo_alto'     => [34.8, 43.1],
            'riesgo_muy_alto' => [43.2, 100.0]
        ],
        'demandas' => [
            'sin_riesgo'      => [0.0, 26.9],
            'riesgo_bajo'     => [27.0, 33.3],
            'riesgo_medio'    => [33.4, 37.8],
            'riesgo_alto'     => [37.9, 44.2],
            'riesgo_muy_alto' => [44.3, 100.0]
        ],
        'recompensas' => [
            'sin_riesgo'      => [0.0, 2.5],
            'riesgo_bajo'     => [2.6, 10.0],
            'riesgo_medio'    => [10.1, 17.5],
            'riesgo_alto'     => [17.6, 27.5],
            'riesgo_muy_alto' => [27.6, 100.0]
        ],
    ],
];
```

### Definición de Dominios

```php
$dominiosInfo = [
    'liderazgo' => [
        'nombre' => 'Liderazgo y Relaciones Sociales en el Trabajo',
        'definicion' => 'Se refiere al tipo de relación social que se establece entre los superiores jerárquicos y sus colaboradores...',
        'campo_puntaje' => 'dom_liderazgo_puntaje',
        'campo_nivel' => 'dom_liderazgo_nivel',
        'dimensiones' => [
            'Características del Liderazgo',
            'Relaciones Sociales en el Trabajo',
            'Retroalimentación del Desempeño',
            'Relación con los Colaboradores'
        ],
    ],
    'control' => [
        'nombre' => 'Control sobre el Trabajo',
        'definicion' => 'Posibilidad que el trabajo ofrece al individuo para influir y tomar decisiones...',
        'campo_puntaje' => 'dom_control_puntaje',
        'campo_nivel' => 'dom_control_nivel',
        'dimensiones' => [
            'Claridad del Rol',
            'Capacitación',
            'Participación y Manejo del Cambio',
            'Oportunidades de Desarrollo',
            'Control y Autonomía sobre el Trabajo'
        ],
    ],
    'demandas' => [
        'nombre' => 'Demandas del Trabajo',
        'definicion' => 'Se refieren a las exigencias que el trabajo impone al individuo...',
        'campo_puntaje' => 'dom_demandas_puntaje',
        'campo_nivel' => 'dom_demandas_nivel',
        'dimensiones' => [
            'Demandas Cuantitativas',
            'Demandas de Carga Mental',
            'Demandas Emocionales',
            'Exigencias de Responsabilidad',
            'Demandas Ambientales',
            'Demandas de la Jornada',
            'Consistencia del Rol',
            'Influencia del Ambiente Laboral'
        ],
    ],
    'recompensas' => [
        'nombre' => 'Recompensas',
        'definicion' => 'Este término trata de la retribución que el trabajador obtiene a cambio de sus contribuciones...',
        'campo_puntaje' => 'dom_recompensas_puntaje',
        'campo_nivel' => 'dom_recompensas_nivel',
        'dimensiones' => [
            'Recompensas derivadas de la pertenencia',
            'Reconocimiento y Compensación'
        ],
    ],
];
```

### Función para Determinar Nivel
```php
function getNivelFromPuntaje($puntaje, $baremo) {
    foreach ($baremo as $nivel => $rango) {
        if ($puntaje >= $rango[0] && $puntaje <= $rango[1]) {
            return $nivel;
        }
    }
    return 'sin_riesgo';
}
```

---

## Estructura de Tablas MySQL

### Tabla: `calculated_results`

Contiene los puntajes calculados para cada trabajador evaluado.

```sql
-- Campos relevantes para dominios intralaborales:
dom_liderazgo_puntaje       DECIMAL(5,2)
dom_liderazgo_nivel         VARCHAR(20)
dom_control_puntaje         DECIMAL(5,2)
dom_control_nivel           VARCHAR(20)
dom_demandas_puntaje        DECIMAL(5,2)
dom_demandas_nivel          VARCHAR(20)
dom_recompensas_puntaje     DECIMAL(5,2)
dom_recompensas_nivel       VARCHAR(20)

-- Campos para intralaboral total:
intralaboral_total_puntaje  DECIMAL(5,2)
intralaboral_total_nivel    VARCHAR(20)
intralaboral_form_type      ENUM('A', 'B')

-- Campos para extralaboral:
extralaboral_total_puntaje  DECIMAL(5,2)
extralaboral_total_nivel    VARCHAR(20)

-- Campos para estrés:
estres_total_puntaje        DECIMAL(5,2)
estres_total_nivel          VARCHAR(20)

-- Relación:
battery_service_id          INT UNSIGNED  -- FK a battery_services
```

### Tabla: `battery_services`

Representa un servicio de evaluación para una empresa.

```sql
id                  INT UNSIGNED PRIMARY KEY
company_id          INT UNSIGNED  -- FK a companies
consultant_id       INT UNSIGNED  -- FK a consultants
status              VARCHAR(20)
created_at          DATETIME
```

### Tabla: `companies`

Información de las empresas evaluadas.

```sql
id              INT UNSIGNED PRIMARY KEY
name            VARCHAR(150)
nit             VARCHAR(20)
city            VARCHAR(100)
contact_email   VARCHAR(150)
logo_path       VARCHAR(500)
```

### Tabla: `reports`

Informes generados para cada servicio de batería.

```sql
id                  INT UNSIGNED PRIMARY KEY
battery_service_id  INT UNSIGNED
status              VARCHAR(20)
created_at          DATETIME
```

### Tabla: `report_sections`

Secciones del informe con texto generado por IA.

```sql
id                  INT UNSIGNED PRIMARY KEY
report_id           INT UNSIGNED  -- FK a reports
questionnaire_type  ENUM('intralaboral', 'extralaboral', 'stress')
section_level       ENUM('questionnaire', 'domain', 'dimension')
form_type           ENUM('A', 'B')
domain_code         VARCHAR(50)   -- 'liderazgo', 'control', etc.
dimension_code      VARCHAR(50)
ai_generated_text   TEXT
```

---

## Consultas SQL Utilizadas

### 1. Obtener Datos de la Empresa
```sql
SELECT
    c.name AS company_name,
    c.nit,
    c.city
FROM battery_services bs
JOIN companies c ON bs.company_id = c.id
WHERE bs.id = :battery_service_id
```

### 2. Obtener Resultados Calculados
```sql
SELECT *
FROM calculated_results
WHERE battery_service_id = :battery_service_id
AND intralaboral_form_type = :forma
```

### 3. Obtener Texto IA para Dominio
```sql
SELECT ai_generated_text
FROM report_sections
WHERE report_id IN (
    SELECT id FROM reports
    WHERE battery_service_id = :battery_service_id
)
AND questionnaire_type = 'intralaboral'
AND section_level = 'domain'
AND domain_code = :dominio
AND form_type = :forma
LIMIT 1
```

### 4. Ejemplo de Cálculo de Promedio y Distribución
```php
// Obtener resultados
$stmtResults = $pdo->prepare("
    SELECT * FROM calculated_results
    WHERE battery_service_id = ? AND intralaboral_form_type = ?
");
$stmtResults->execute([$batteryServiceId, $forma]);
$results = $stmtResults->fetchAll(PDO::FETCH_ASSOC);

// Campo específico del dominio
$campoPuntaje = 'dom_liderazgo_puntaje';  // Según el dominio seleccionado
$campoNivel = 'dom_liderazgo_nivel';

// Extraer puntajes no nulos
$puntajes = array_filter(
    array_column($results, $campoPuntaje),
    function($v) {
        return $v !== null && $v !== '';
    }
);

// Calcular promedio
$promedio = !empty($puntajes) ? array_sum($puntajes) / count($puntajes) : 0;

// Determinar nivel según baremo
$nivel = getNivelFromPuntaje($promedio, $baremo);

// Contar total de evaluados
$totalEvaluados = count($results);

// Calcular distribución por niveles
$distribucion = [
    'sin_riesgo' => 0,
    'riesgo_bajo' => 0,
    'riesgo_medio' => 0,
    'riesgo_alto' => 0,
    'riesgo_muy_alto' => 0,
];

foreach ($results as $result) {
    $nivelIndividual = $result[$campoNivel] ?? '';
    if (isset($distribucion[$nivelIndividual])) {
        $distribucion[$nivelIndividual]++;
    }
}

// Calcular porcentajes agrupados
$pctAlto = (($distribucion['riesgo_alto'] + $distribucion['riesgo_muy_alto']) / $totalEvaluados) * 100;
$pctMedio = ($distribucion['riesgo_medio'] / $totalEvaluados) * 100;
$pctBajo = (($distribucion['sin_riesgo'] + $distribucion['riesgo_bajo']) / $totalEvaluados) * 100;
```

---

## Colores y Niveles de Riesgo

### Paleta de Colores
```php
$riskColors = [
    'sin_riesgo'      => '#4CAF50',  // Verde oscuro
    'riesgo_bajo'     => '#8BC34A',  // Verde claro
    'riesgo_medio'    => '#FFEB3B',  // Amarillo
    'riesgo_alto'     => '#FF9800',  // Naranja
    'riesgo_muy_alto' => '#F44336',  // Rojo
];
```

### Etiquetas de Niveles
```php
$riskLabels = [
    'sin_riesgo'      => 'SIN RIESGO',
    'riesgo_bajo'     => 'RIESGO BAJO',
    'riesgo_medio'    => 'RIESGO MEDIO',
    'riesgo_alto'     => 'RIESGO ALTO',
    'riesgo_muy_alto' => 'RIESGO MUY ALTO',
];
```

### Abreviaturas para el Gauge
```php
$riskLabelsShort = [
    'sin_riesgo'      => 'SR',
    'riesgo_bajo'     => 'RB',
    'riesgo_medio'    => 'RM',
    'riesgo_alto'     => 'RA',
    'riesgo_muy_alto' => 'RMA',
];
```

### Acciones Recomendadas
```php
$riskActions = [
    'sin_riesgo'      => 'Mantener condiciones actuales',
    'riesgo_bajo'     => 'Acciones preventivas de mantenimiento',
    'riesgo_medio'    => 'Observación y acciones preventivas',
    'riesgo_alto'     => 'Intervención en marco de vigilancia epidemiológica',
    'riesgo_muy_alto' => 'Intervención inmediata en marco de vigilancia epidemiológica',
];
```

---

## Limitaciones de DomPDF

### CSS NO Soportado
```css
/* EVITAR - NO FUNCIONA EN DOMPDF */
display: flex;
display: grid;
position: fixed;
transform: rotate();
box-shadow: ...;
```

### CSS Soportado
```css
/* USAR - FUNCIONA EN DOMPDF */
display: block;
display: inline-block;
display: table;
display: table-cell;
float: left;
float: right;
text-align: center;
vertical-align: middle;
line-height: 20px;  /* Para centrar verticalmente */
```

### Ejemplo de Centrado Vertical Compatible
```css
.bar-segment {
    height: 20px;
    float: left;
    text-align: center;
    font-size: 7pt;
    font-weight: bold;
    color: white;
    line-height: 20px;  /* Igual a height para centrar */
    vertical-align: middle;
    padding-top: 0;
    padding-bottom: 0;
    box-sizing: border-box;
}
```

### Configuración de DomPDF
```php
use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');
$options->set('isFontSubsettingEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('Letter', 'portrait');
$dompdf->render();

// Descargar
$dompdf->stream('informe.pdf', ['Attachment' => true]);

// O mostrar en navegador
$dompdf->stream('informe.pdf', ['Attachment' => false]);
```

---

## URLs de Prueba

### Archivo de Prueba Principal
```
# Preview HTML
http://localhost/psyrisk/public/test_gauge_real.php?battery_id=1

# Descargar PDF
http://localhost/psyrisk/public/test_gauge_real.php?battery_id=1&download=1

# Probar diferentes dominios
http://localhost/psyrisk/public/test_gauge_real.php?battery_id=1&dominio=liderazgo&forma=A
http://localhost/psyrisk/public/test_gauge_real.php?battery_id=1&dominio=control&forma=A
http://localhost/psyrisk/public/test_gauge_real.php?battery_id=1&dominio=demandas&forma=A
http://localhost/psyrisk/public/test_gauge_real.php?battery_id=1&dominio=recompensas&forma=A

# Probar Forma B
http://localhost/psyrisk/public/test_gauge_real.php?battery_id=1&dominio=liderazgo&forma=B
```

### Archivo de Prueba Simple (Gauge Básico)
```
http://localhost/psyrisk/public/test_gauge_dompdf.php
http://localhost/psyrisk/public/test_gauge_dompdf.php?download=1
```

---

## Archivos de Referencia

### Ubicación de Archivos Clave

| Archivo | Descripción |
|---------|-------------|
| `public/test_gauge_real.php` | Prueba completa con datos reales de BD |
| `public/test_gauge_dompdf.php` | Prueba simple de gauge con DomPDF |
| `app/Libraries/PdfGaugeGenerator.php` | Clase generadora de gauges (versión original) |
| `app/Controllers/PdfNativo/PdfNativoOrchestrator.php` | Orquestador del módulo PDF nativo |
| `app/Controllers/PdfNativo/PdfNativoBaseController.php` | Controlador base con estilos CSS |
| `PROBLEMA_PDF_GAUGES.md` | Documentación del problema con wkhtmltopdf |

### Carpeta de Versión Funcional
```
C:\xampp\htdocs\psyrisk\VERSIONPDFQUEFUNCIONO\
```
Contiene capturas de pantalla de PDFs generados correctamente con los gauges visibles.

---

## Resumen de Fórmulas Matemáticas

### Ángulo de la Aguja
```
ángulo_grados = 180 - (puntaje × 1.8)
```

### Posición de la Punta de la Aguja
```
x = centro_x + (longitud_aguja × cos(ángulo_radianes))
y = centro_y - (longitud_aguja × sin(ángulo_radianes))
```

### Punto en el Arco para un Porcentaje
```
ángulo = 180 - (porcentaje × 1.8)
x = centro_x + (radio × cos(ángulo_radianes))
y = centro_y - (radio × sin(ángulo_radianes))
```

### Promedio de Puntajes
```
promedio = Σ(puntajes) / cantidad_puntajes
```

### Porcentaje de Distribución
```
porcentaje_nivel = (cantidad_en_nivel / total_evaluados) × 100
```

---

## Contacto y Soporte

- **Proyecto**: PSYRISK - Sistema de Evaluación de Riesgo Psicosocial
- **Normativa**: Resolución 2404/2019 - Ministerio del Trabajo de Colombia
- **Framework**: CodeIgniter 4
- **Librería PDF**: DomPDF

---

*Documento generado: Noviembre 2024*
