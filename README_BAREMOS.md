# PSYRISK - Guia de Baremos

## Regla Principal

**NUNCA definas baremos manualmente en controladores, vistas o cualquier otro archivo.**

Los baremos deben obtenerse UNICAMENTE desde las librerias Scoring:

```php
use App\Libraries\IntralaboralAScoring;
use App\Libraries\IntralaboralBScoring;
use App\Libraries\ExtralaboralScoring;
use App\Libraries\EstresScoring;
```

---

## Fuentes Oficiales de Baremos

| Cuestionario | Libreria | Tablas Resolucion 2404/2019 |
|--------------|----------|----------------------------|
| Intralaboral Forma A | `IntralaboralAScoring` | Tablas 29, 31, 33 |
| Intralaboral Forma B | `IntralaboralBScoring` | Tablas 30, 32, 33 |
| Extralaboral | `ExtralaboralScoring` | Tablas correspondientes |
| Estres | `EstresScoring` | Tablas correspondientes |

---

## Metodos Disponibles

### IntralaboralAScoring / IntralaboralBScoring

```php
// Baremos de DOMINIOS (Tablas 31 y 32)
IntralaboralAScoring::getBaremosDominios();      // Todos los dominios
IntralaboralAScoring::getBaremoDominio($codigo); // Un dominio especifico

// Baremos de DIMENSIONES (Tablas 29 y 30)
IntralaboralAScoring::getBaremosDimensiones();      // Todas las dimensiones
IntralaboralAScoring::getBaremoDimension($codigo);  // Una dimension especifica

// Baremo TOTAL INTRALABORAL (Tabla 33)
IntralaboralAScoring::getBaremoTotal();
```

### Codigos de Dominios

```php
$dominios = [
    'liderazgo_relaciones_sociales',  // Liderazgo y Relaciones Sociales
    'control',                         // Control sobre el Trabajo
    'demandas',                        // Demandas del Trabajo
    'recompensas',                     // Recompensas
];
```

### Codigos de Dimensiones

```php
// Forma A y B
$dimensiones = [
    'caracteristicas_liderazgo',
    'relaciones_sociales_trabajo',
    'retroalimentacion_desempeno',
    'relacion_con_colaboradores',      // Solo Forma A
    'claridad_rol',
    'capacitacion',
    'participacion_manejo_cambio',
    'oportunidades_desarrollo',
    'control_autonomia_trabajo',
    'demandas_ambientales_esfuerzo_fisico',
    'demandas_emocionales',
    'demandas_cuantitativas',
    'influencia_trabajo_entorno_extralaboral',
    'exigencias_responsabilidad_cargo', // Solo Forma A
    'demandas_carga_mental',
    'consistencia_rol',                 // Solo Forma A
    'demandas_jornada_trabajo',
    'recompensas_pertenencia_estabilidad',
    'reconocimiento_compensacion',
];
```

---

## Ejemplo Correcto

```php
<?php
namespace App\Controllers\PdfEjecutivo;

use App\Libraries\IntralaboralAScoring;
use App\Libraries\IntralaboralBScoring;

class MiControlador extends PdfEjecutivoBaseController
{
    // Obtener baremo de un dominio segun la forma
    protected function getBaremoDominio($dominio, $forma)
    {
        if ($forma === 'A') {
            return IntralaboralAScoring::getBaremoDominio($dominio);
        } else {
            return IntralaboralBScoring::getBaremoDominio($dominio);
        }
    }

    // Uso en el render
    public function renderPagina()
    {
        $baremo = $this->getBaremoDominio('liderazgo_relaciones_sociales', 'A');

        // $baremo contiene:
        // [
        //     'sin_riesgo'      => [0.0, 9.1],
        //     'riesgo_bajo'     => [9.2, 17.7],
        //     'riesgo_medio'    => [17.8, 25.6],
        //     'riesgo_alto'     => [25.7, 34.8],
        //     'riesgo_muy_alto' => [34.9, 100.0],
        // ]
    }
}
```

---

## Ejemplo Incorrecto (NO HACER)

```php
// MAL: Baremos hardcodeados en el controlador
protected $baremosDominios = [
    'A' => [
        'liderazgo' => [
            'sin_riesgo' => [0.0, 9.1],
            'riesgo_bajo' => [9.2, 17.7],
            // ... esto esta MAL
        ],
    ],
];
```

Problemas de este enfoque:
1. Duplicacion de datos
2. Riesgo de inconsistencias
3. Dificil de mantener
4. Posibles errores de transcripcion

---

## Mapeo de Codigos

Si usas codigos cortos en tu controlador, crea un mapeo:

```php
// Mapeo de codigos cortos a codigos de las librerias
protected $mapeoCodigosDominios = [
    'liderazgo'   => 'liderazgo_relaciones_sociales',
    'control'     => 'control',
    'demandas'    => 'demandas',
    'recompensas' => 'recompensas',
];

protected function getBaremoDominio($codigoCorto, $forma)
{
    $codigoLibreria = $this->mapeoCodigosDominios[$codigoCorto] ?? $codigoCorto;

    if ($forma === 'A') {
        return IntralaboralAScoring::getBaremoDominio($codigoLibreria);
    } else {
        return IntralaboralBScoring::getBaremoDominio($codigoLibreria);
    }
}
```

---

## Estructura de un Baremo

Todos los baremos siguen esta estructura:

```php
[
    'sin_riesgo'      => [min, max],  // Ej: [0.0, 9.1]
    'riesgo_bajo'     => [min, max],  // Ej: [9.2, 17.7]
    'riesgo_medio'    => [min, max],  // Ej: [17.8, 25.6]
    'riesgo_alto'     => [min, max],  // Ej: [25.7, 34.8]
    'riesgo_muy_alto' => [min, max],  // Ej: [34.9, 100.0]
]
```

Excepcion: Estres usa nomenclatura diferente:
```php
[
    'muy_bajo' => [min, max],
    'bajo'     => [min, max],
    'medio'    => [min, max],
    'alto'     => [min, max],
    'muy_alto' => [min, max],
]
```

---

## Archivos de Referencia

| Archivo | Contenido |
|---------|-----------|
| `app/Libraries/IntralaboralAScoring.php` | Baremos Forma A (Tablas 29, 31, 33) |
| `app/Libraries/IntralaboralBScoring.php` | Baremos Forma B (Tablas 30, 32, 33) |
| `app/Libraries/ExtralaboralScoring.php` | Baremos Extralaboral |
| `app/Libraries/EstresScoring.php` | Baremos Estres |

---

## Beneficios de Este Enfoque

1. **Fuente unica de verdad**: Los baremos estan en UN solo lugar
2. **Consistencia**: El PDF muestra los mismos baremos usados en los calculos
3. **Mantenibilidad**: Si cambia la resolucion, se edita UN archivo
4. **Sin duplicacion**: No hay riesgo de copiar mal los valores

---

*Resolucion 2404/2019 - Ministerio del Trabajo de Colombia*
