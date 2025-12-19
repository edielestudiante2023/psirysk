# Plan de Implementacion: Calculo de Periodicidad en PsyRisk

## Objetivo

Implementar el calculo automatico de periodicidad de evaluacion segun Articulo 3 de la Resolucion 2404/2019, mostrando al cliente cuando debe realizar la proxima evaluacion (1 o 2 anos).

---

## Arquitectura Propuesta

### Nuevo Servicio: `PeriodicidadService.php`

```
app/Services/PeriodicidadService.php
```

**Responsabilidades:**
1. Calcular promedio de puntaje bruto total por forma (A y B)
2. Transformar a puntaje y determinar nivel usando Tabla 33
3. Aplicar regla de periodicidad (nivel mas alto prevalece)
4. Retornar recomendacion con justificacion normativa

---

## Datos Requeridos

### Entrada
- `battery_service_id`: ID del servicio de bateria

### Fuente de Datos
Tabla `calculated_results`:
- `intralaboral_total_bruto` - Puntaje bruto intralaboral
- `intralaboral_form_type` - 'A' o 'B'
- `intralaboral_total_puntaje` - Puntaje transformado (ya existe)
- `intralaboral_total_nivel` - Nivel de riesgo individual

### Salida
```php
[
    'forma_a' => [
        'n_trabajadores' => 2,
        'promedio_bruto' => 85.5,
        'puntaje_transformado' => 17.4,
        'nivel' => 'sin_riesgo'
    ],
    'forma_b' => [
        'n_trabajadores' => 2,
        'promedio_bruto' => 72.0,
        'puntaje_transformado' => 18.6,
        'nivel' => 'sin_riesgo'
    ],
    'periodicidad' => [
        'anos' => 2,
        'nivel_determinante' => 'sin_riesgo',
        'forma_determinante' => null, // null si ambas iguales
        'justificacion' => 'Ambas formas presentan nivel sin riesgo/bajo/medio'
    ]
]
```

---

## Implementacion Paso a Paso

### Paso 1: Crear PeriodicidadService

```php
<?php
namespace App\Services;

class PeriodicidadService
{
    // Baremos Tabla 33 - Intralaboral Total
    private const BAREMOS_INTRALABORAL_TOTAL = [
        'A' => [
            'sin_riesgo'      => [0.0, 19.7],
            'riesgo_bajo'     => [19.8, 25.8],
            'riesgo_medio'    => [25.9, 31.5],
            'riesgo_alto'     => [31.6, 38.8],
            'riesgo_muy_alto' => [38.9, 100.0]
        ],
        'B' => [
            'sin_riesgo'      => [0.0, 20.6],
            'riesgo_bajo'     => [20.7, 26.0],
            'riesgo_medio'    => [26.1, 33.2],
            'riesgo_alto'     => [33.3, 40.0],
            'riesgo_muy_alto' => [40.1, 100.0]
        ]
    ];

    // Factores de transformacion
    private const FACTOR_FORMA_A = 492;
    private const FACTOR_FORMA_B = 388;

    public function calcularPeriodicidad(int $batteryServiceId): array
    {
        // Implementacion...
    }
}
```

### Paso 2: Metodo de Calculo

```php
public function calcularPeriodicidad(int $batteryServiceId): array
{
    $model = new \App\Models\CalculatedResultModel();
    $results = $model->where('battery_service_id', $batteryServiceId)->findAll();

    // Separar por forma
    $formaA = array_filter($results, fn($r) => $r['intralaboral_form_type'] === 'A');
    $formaB = array_filter($results, fn($r) => $r['intralaboral_form_type'] === 'B');

    // Calcular estadisticas por forma
    $statsA = $this->calcularStatsPorForma($formaA, 'A');
    $statsB = $this->calcularStatsPorForma($formaB, 'B');

    // Determinar periodicidad
    $periodicidad = $this->determinarPeriodicidad($statsA, $statsB);

    return [
        'forma_a' => $statsA,
        'forma_b' => $statsB,
        'periodicidad' => $periodicidad
    ];
}

private function calcularStatsPorForma(array $trabajadores, string $forma): array
{
    $n = count($trabajadores);
    if ($n === 0) {
        return ['n_trabajadores' => 0, 'nivel' => null];
    }

    // Sumar puntajes brutos
    $sumaBrutos = array_sum(array_column($trabajadores, 'intralaboral_total_bruto'));
    $promedioBruto = $sumaBrutos / $n;

    // Transformar
    $factor = ($forma === 'A') ? self::FACTOR_FORMA_A : self::FACTOR_FORMA_B;
    $puntajeTransformado = round(($promedioBruto / $factor) * 100, 2);

    // Clasificar
    $nivel = $this->clasificarNivel($puntajeTransformado, $forma);

    return [
        'n_trabajadores' => $n,
        'promedio_bruto' => round($promedioBruto, 2),
        'puntaje_transformado' => $puntajeTransformado,
        'nivel' => $nivel
    ];
}

private function clasificarNivel(float $puntaje, string $forma): string
{
    $baremos = self::BAREMOS_INTRALABORAL_TOTAL[$forma];
    foreach ($baremos as $nivel => $rango) {
        if ($puntaje >= $rango[0] && $puntaje <= $rango[1]) {
            return $nivel;
        }
    }
    return 'riesgo_muy_alto'; // Default si excede
}

private function determinarPeriodicidad(array $statsA, array $statsB): array
{
    $nivelesAltos = ['riesgo_alto', 'riesgo_muy_alto'];

    $nivelA = $statsA['nivel'] ?? null;
    $nivelB = $statsB['nivel'] ?? null;

    $aAlto = in_array($nivelA, $nivelesAltos);
    $bAlto = in_array($nivelB, $nivelesAltos);

    if ($aAlto || $bAlto) {
        return [
            'anos' => 1,
            'nivel_determinante' => $aAlto ? $nivelA : $nivelB,
            'forma_determinante' => $aAlto ? 'A' : 'B',
            'justificacion' => 'Segun Art. 3 Res. 2404/2019: nivel alto o muy alto requiere evaluacion anual'
        ];
    }

    return [
        'anos' => 2,
        'nivel_determinante' => $this->getNivelMasAlto($nivelA, $nivelB),
        'forma_determinante' => null,
        'justificacion' => 'Ambas formas en nivel medio o inferior. Evaluacion cada 2 anos.'
    ];
}
```

### Paso 3: Integrar en Vista

Agregar seccion en `battery_services/view.php`:

```php
<!-- Card de Periodicidad -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-info text-white">
        <i class="fas fa-calendar-alt me-2"></i>Periodicidad de Evaluacion
        <span class="badge bg-light text-dark float-end">Art. 3 Res. 2404/2019</span>
    </div>
    <div class="card-body">
        <?php
        $periodicidadService = new \App\Services\PeriodicidadService();
        $periodicidad = $periodicidadService->calcularPeriodicidad($service['id']);
        ?>

        <div class="row">
            <div class="col-md-4">
                <div class="text-center p-3 bg-light rounded">
                    <h6>Forma A</h6>
                    <p class="mb-0">n = <?= $periodicidad['forma_a']['n_trabajadores'] ?></p>
                    <span class="badge bg-<?= $periodicidad['forma_a']['nivel'] === 'riesgo_alto' ? 'danger' : 'success' ?>">
                        <?= ucwords(str_replace('_', ' ', $periodicidad['forma_a']['nivel'])) ?>
                    </span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center p-3 bg-light rounded">
                    <h6>Forma B</h6>
                    <p class="mb-0">n = <?= $periodicidad['forma_b']['n_trabajadores'] ?></p>
                    <span class="badge bg-<?= $periodicidad['forma_b']['nivel'] === 'riesgo_alto' ? 'danger' : 'success' ?>">
                        <?= ucwords(str_replace('_', ' ', $periodicidad['forma_b']['nivel'])) ?>
                    </span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center p-3 <?= $periodicidad['periodicidad']['anos'] === 1 ? 'bg-warning' : 'bg-success text-white' ?> rounded">
                    <h6>Proxima Evaluacion</h6>
                    <h2 class="mb-0"><?= $periodicidad['periodicidad']['anos'] ?> ano<?= $periodicidad['periodicidad']['anos'] > 1 ? 's' : '' ?></h2>
                    <small><?= $periodicidad['periodicidad']['justificacion'] ?></small>
                </div>
            </div>
        </div>
    </div>
</div>
```

### Paso 4: Agregar al PDF Ejecutivo

Crear `app/Controllers/PdfEjecutivo/PeriodicidadController.php`:

- Pagina dedicada mostrando:
  - Resumen de niveles por forma
  - Recomendacion de periodicidad
  - Cita textual del Articulo 3
  - Fecha recomendada de proxima evaluacion

---

## Validacion de Datos

### Verificar que existen puntajes brutos

```sql
SELECT
    intralaboral_form_type,
    COUNT(*) as n,
    AVG(intralaboral_total_bruto) as promedio_bruto,
    AVG(intralaboral_total_puntaje) as promedio_transformado
FROM calculated_results
WHERE battery_service_id = ?
GROUP BY intralaboral_form_type;
```

### Si no existen brutos

Si `intralaboral_total_bruto` esta vacio o es NULL, se puede reconstruir:

```php
$bruto = ($puntajeTransformado * $factor) / 100;
```

---

## Archivos a Crear/Modificar

| Archivo | Accion | Descripcion |
|---------|--------|-------------|
| `app/Services/PeriodicidadService.php` | CREAR | Logica de calculo |
| `app/Views/battery_services/view.php` | MODIFICAR | Agregar card de periodicidad |
| `app/Controllers/PdfEjecutivo/PeriodicidadController.php` | CREAR | PDF con recomendacion |
| `app/Config/Routes.php` | MODIFICAR | Ruta para PDF periodicidad |

---

## Estimacion de Complejidad

| Componente | Complejidad |
|------------|-------------|
| PeriodicidadService | Baja |
| Vista card | Baja |
| PDF Controller | Media |
| **Total** | **Media-Baja** |

---

## Criterios de Aceptacion

1. El sistema calcula correctamente el promedio de puntaje bruto por forma
2. La transformacion usa los factores correctos (492/388)
3. La clasificacion usa los baremos correctos de Tabla 33
4. La regla de periodicidad aplica: alto/muy alto = 1 ano, resto = 2 anos
5. Se muestra justificacion normativa (Art. 3 Res. 2404/2019)
6. Se genera PDF descargable con la recomendacion

---

## Notas Importantes

1. **NO mezclar formas**: El calculo de periodicidad se basa en evaluar cada forma por separado
2. **El nivel mas alto prevalece**: Si cualquier forma es alto/muy alto, periodicidad = 1 ano
3. **Base normativa**: Siempre citar Articulo 3 de Resolucion 2404/2019
4. **Confidencialidad**: Este calculo no expone datos individuales, solo promedios agregados

---

*Plan creado: 2025-12-18*
*Estado: Pendiente de implementacion*
