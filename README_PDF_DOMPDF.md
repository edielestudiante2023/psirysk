# PSYRISK - Guia PDF con DomPDF

## REGLA CRITICA: ETIQUETAS DEL GAUGE

**LAS ETIQUETAS DEBEN IR EN UNA SOLA LINEA:**

```
CORRECTO:   "SR: 0-19.7"    (una linea, un elemento <text>)
INCORRECTO: "SR" en linea 1, "0-19.7" en linea 2 (dos elementos <text>)
```

**Codigo correcto para etiquetas:**
```php
// UNA SOLA LINEA: etiqueta + rango juntos
$labelsSvg .= '<text x="' . $labelPos[0] . '" y="' . $labelPos[1] .
              '" font-family="Arial" font-size="4" text-anchor="middle" fill="#333">' .
              $riskLabelsShort[$lvl] . ': ' . $startPct . '-' . $endPct . '</text>';
```

**Codigo INCORRECTO (NO USAR):**
```php
// MAL: Dos elementos separados = dos lineas
$labelsSvg .= '<text ...>' . $riskLabelsShort[$lvl] . '</text>';  // Primera linea
$labelsSvg .= '<text ...>' . $startPct . '-' . $endPct . '</text>'; // Segunda linea
```

---

## Archivo de Referencia

**Archivo funcional:** `app/Libraries/PdfGaugeGenerator.php`

Este archivo contiene la implementacion correcta del gauge SVG.

---

## Estructura del Gauge SVG

### Elementos dentro del SVG (6 elementos)

| # | Elemento | Descripcion |
|---|----------|-------------|
| 1 | Arco de 5 colores | Segmentos proporcionales al baremo |
| 2 | Etiquetas con rango | "SR: 0-19.7", "RB: 19.8-25.8", etc. (UNA LINEA) |
| 3 | Indicadores 0 y 100 | En los extremos del arco |
| 4 | Aguja | Apuntando al valor calculado |
| 5 | Centro de aguja | Circulo negro en el centro |
| 6 | Valor numerico | El puntaje debajo del gauge |

### Elementos HTML debajo del SVG (2 elementos)

| # | Elemento | Descripcion |
|---|----------|-------------|
| 7 | Leyenda | SR=Sin Riesgo, RB=Riesgo Bajo, etc. |
| 8 | Tabla de baremos | 5 columnas con colores y rangos |

---

## Parametros Geometricos

```
cx = 100           Centro X del gauge
cy = 90            Centro Y del gauge
radius = 70        Radio del arco exterior
labelRadius = 50   Radio para etiquetas (interior)
needleLength = 60  Longitud de la aguja
SVG width = 200
SVG height = 120
```

### Formula del angulo

```php
// El semicirculo va de 180 grados (izquierda, valor=0) a 0 grados (derecha, valor=100)
$needleAngleDeg = 180 - ($percentage * 1.8);  // 180/100 = 1.8 grados por unidad
$needleAngleRad = deg2rad($needleAngleDeg);

// Posicion de la punta de la aguja
$needleX = $cx + ($needleLength * cos($needleAngleRad));
$needleY = $cy - ($needleLength * sin($needleAngleRad));  // NEGATIVO porque Y crece hacia abajo
```

---

## Colores y Etiquetas

### Colores por nivel de riesgo
```php
$riskColors = [
    'sin_riesgo'      => '#4CAF50',  // Verde oscuro
    'riesgo_bajo'     => '#8BC34A',  // Verde claro
    'riesgo_medio'    => '#FFEB3B',  // Amarillo
    'riesgo_alto'     => '#FF9800',  // Naranja
    'riesgo_muy_alto' => '#F44336',  // Rojo
];
```

### Abreviaturas
```php
$riskLabelsShort = [
    'sin_riesgo'      => 'SR',
    'riesgo_bajo'     => 'RB',
    'riesgo_medio'    => 'RM',
    'riesgo_alto'     => 'RA',
    'riesgo_muy_alto' => 'RMA',
];
```

---

## Baremos Oficiales (Resolucion 2404/2019)

### Intralaboral Total
```php
$baremosIntralaboralTotal = [
    'A' => [  // Jefes, Profesionales y Tecnicos
        'sin_riesgo'      => [0.0, 19.7],
        'riesgo_bajo'     => [19.8, 25.8],
        'riesgo_medio'    => [25.9, 31.5],
        'riesgo_alto'     => [31.6, 38.7],
        'riesgo_muy_alto' => [38.8, 100.0],
    ],
    'B' => [  // Auxiliares y Operarios
        'sin_riesgo'      => [0.0, 20.6],
        'riesgo_bajo'     => [20.7, 26.0],
        'riesgo_medio'    => [26.1, 31.2],
        'riesgo_alto'     => [31.3, 38.7],
        'riesgo_muy_alto' => [38.8, 100.0],
    ],
];
```

### Total General Psicosocial (Tabla 34)
```php
$baremosTotalGeneral = [
    'A' => [
        'sin_riesgo'      => [0.0, 18.8],
        'riesgo_bajo'     => [18.9, 24.4],
        'riesgo_medio'    => [24.5, 29.5],
        'riesgo_alto'     => [29.6, 35.4],
        'riesgo_muy_alto' => [35.5, 100.0],
    ],
    'B' => [
        'sin_riesgo'      => [0.0, 19.9],
        'riesgo_bajo'     => [20.0, 24.8],
        'riesgo_medio'    => [24.9, 29.5],
        'riesgo_alto'     => [29.6, 35.4],
        'riesgo_muy_alto' => [35.5, 100.0],
    ],
];
```

### Extralaboral
```php
$baremosExtralaboral = [
    'A' => [
        'sin_riesgo'      => [0.0, 11.3],
        'riesgo_bajo'     => [11.4, 16.9],
        'riesgo_medio'    => [17.0, 22.6],
        'riesgo_alto'     => [22.7, 29.0],
        'riesgo_muy_alto' => [29.1, 100.0],
    ],
    'B' => [
        'sin_riesgo'      => [0.0, 12.5],
        'riesgo_bajo'     => [12.6, 17.9],
        'riesgo_medio'    => [18.0, 24.0],
        'riesgo_alto'     => [24.1, 30.0],
        'riesgo_muy_alto' => [30.1, 100.0],
    ],
];
```

### Estres (nomenclatura diferente)
```php
$baremosEstres = [
    'muy_bajo'  => [0.0, 7.8],
    'bajo'      => [7.9, 12.6],
    'medio'     => [12.7, 17.7],
    'alto'      => [17.8, 25.0],
    'muy_alto'  => [25.1, 100.0],
];
```

### Dominios Intralaboral Forma A
```php
'liderazgo'   => ['sin_riesgo' => [0.0, 9.1], 'riesgo_bajo' => [9.2, 17.7], 'riesgo_medio' => [17.8, 25.6], 'riesgo_alto' => [25.7, 34.8], 'riesgo_muy_alto' => [34.9, 100.0]],
'control'     => ['sin_riesgo' => [0.0, 10.7], 'riesgo_bajo' => [10.8, 19.0], 'riesgo_medio' => [19.1, 29.8], 'riesgo_alto' => [29.9, 40.5], 'riesgo_muy_alto' => [40.6, 100.0]],
'demandas'    => ['sin_riesgo' => [0.0, 28.5], 'riesgo_bajo' => [28.6, 35.0], 'riesgo_medio' => [35.1, 41.5], 'riesgo_alto' => [41.6, 47.5], 'riesgo_muy_alto' => [47.6, 100.0]],
'recompensas' => ['sin_riesgo' => [0.0, 4.5], 'riesgo_bajo' => [4.6, 11.4], 'riesgo_medio' => [11.5, 20.5], 'riesgo_alto' => [20.6, 29.5], 'riesgo_muy_alto' => [29.6, 100.0]],
```

### Dominios Intralaboral Forma B
```php
'liderazgo'   => ['sin_riesgo' => [0.0, 8.3], 'riesgo_bajo' => [8.4, 17.5], 'riesgo_medio' => [17.6, 26.7], 'riesgo_alto' => [26.8, 38.3], 'riesgo_muy_alto' => [38.4, 100.0]],
'control'     => ['sin_riesgo' => [0.0, 19.4], 'riesgo_bajo' => [19.5, 26.4], 'riesgo_medio' => [26.5, 34.7], 'riesgo_alto' => [34.8, 43.1], 'riesgo_muy_alto' => [43.2, 100.0]],
'demandas'    => ['sin_riesgo' => [0.0, 26.9], 'riesgo_bajo' => [27.0, 33.3], 'riesgo_medio' => [33.4, 37.8], 'riesgo_alto' => [37.9, 44.2], 'riesgo_muy_alto' => [44.3, 100.0]],
'recompensas' => ['sin_riesgo' => [0.0, 2.5], 'riesgo_bajo' => [2.6, 10.0], 'riesgo_medio' => [10.1, 17.5], 'riesgo_alto' => [17.6, 27.5], 'riesgo_muy_alto' => [27.6, 100.0]],
```

---

## HTML del Gauge Completo

```html
<div style="text-align: center; margin: 10pt 0;">
    <!-- El gauge SVG -->
    <img src="<?= $gaugeUri ?>" style="width: 180pt; height: auto;" />

    <!-- ELEMENTO 7: Leyenda de convenciones -->
    <div style="font-size: 6pt; color: #666; margin: 3pt 0; line-height: 1.3;">
        SR=Sin Riesgo | RB=Riesgo Bajo | RM=Riesgo Medio<br>
        RA=Riesgo Alto | RMA=Riesgo Muy Alto
    </div>

    <!-- ELEMENTO 8: Tabla de baremos -->
    <table style="width: 100%; font-size: 7pt; border-collapse: collapse; margin-top: 5pt;">
        <tr>
            <td style="background: #4CAF50; color: white; text-align: center; padding: 3pt; border: 1pt solid #ccc;">Sin Riesgo</td>
            <td style="background: #8BC34A; color: white; text-align: center; padding: 3pt; border: 1pt solid #ccc;">Bajo</td>
            <td style="background: #FFEB3B; color: #333; text-align: center; padding: 3pt; border: 1pt solid #ccc;">Medio</td>
            <td style="background: #FF9800; color: white; text-align: center; padding: 3pt; border: 1pt solid #ccc;">Alto</td>
            <td style="background: #F44336; color: white; text-align: center; padding: 3pt; border: 1pt solid #ccc;">Muy Alto</td>
        </tr>
        <tr>
            <td style="background: #E8F5E9; text-align: center; padding: 3pt; border: 1pt solid #ccc;"><?= $baremo['sin_riesgo'][0] ?> - <?= $baremo['sin_riesgo'][1] ?></td>
            <td style="background: #F1F8E9; text-align: center; padding: 3pt; border: 1pt solid #ccc;"><?= $baremo['riesgo_bajo'][0] ?> - <?= $baremo['riesgo_bajo'][1] ?></td>
            <td style="background: #FFFDE7; text-align: center; padding: 3pt; border: 1pt solid #ccc;"><?= $baremo['riesgo_medio'][0] ?> - <?= $baremo['riesgo_medio'][1] ?></td>
            <td style="background: #FFF3E0; text-align: center; padding: 3pt; border: 1pt solid #ccc;"><?= $baremo['riesgo_alto'][0] ?> - <?= $baremo['riesgo_alto'][1] ?></td>
            <td style="background: #FFEBEE; text-align: center; padding: 3pt; border: 1pt solid #ccc;"><?= $baremo['riesgo_muy_alto'][0] ?> - <?= $baremo['riesgo_muy_alto'][1] ?></td>
        </tr>
    </table>
</div>
```

---

## Limitaciones de DomPDF

### CSS que NO funciona (NUNCA USAR)
```css
display: flex;
display: grid;
position: fixed;
transform: rotate();
box-shadow;
```

### CSS que SI funciona
```css
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

### Layout de columnas
```css
/* Usar esto en lugar de flex */
.row { display: table; width: 100%; }
.col { display: table-cell; vertical-align: top; }
```

---

## Configuracion DomPDF

```php
use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('Letter', 'portrait');
$dompdf->render();
$dompdf->stream('informe.pdf', ['Attachment' => false]);
```

---

## Margenes ICONTEC

```css
@page {
    margin: 85pt 57pt 85pt 113pt;  /* top right bottom left */
}
```

---

*Resolucion 2404/2019 - Ministerio del Trabajo de Colombia*
