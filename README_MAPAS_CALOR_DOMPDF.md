# Mapas de Calor para PDF Ejecutivo - Guia Completa DomPDF

## Resumen

Este documento detalla TODO lo necesario para reproducir la seccion de Mapas de Calor del informe PDF ejecutivo usando DomPDF. El preview funcional esta en:

```
http://localhost/psyrisk/pdf/preview/mapas-calor/1
```

---

## 1. ARQUITECTURA DE ARCHIVOS

### Controladores

| Archivo | Ruta | Proposito |
|---------|------|-----------|
| MapasCalorController.php | app/Controllers/Pdf/Resultados/ | Controlador COMPLETO con toda la logica |
| MapasCalorController.php | app/Controllers/PdfEjecutivo/ | Version simplificada para ejecutivo |
| PdfEjecutivoBaseController.php | app/Controllers/PdfEjecutivo/ | Controlador base con CSS y helpers |

### Vistas

| Archivo | Proposito |
|---------|-----------|
| introduccion_resultados.php | Pagina de introduccion a resultados |
| conclusiones_bateria.php | Conclusiones generales de la bateria |
| mapa_calor_general.php | Mapa de calor consolidado (todas las formas) |
| mapa_intralaboral_a.php | Mapa intralaboral Forma A (19 dimensiones) |
| mapa_intralaboral_b.php | Mapa intralaboral Forma B (16 dimensiones) |
| mapa_extralaboral_a.php | Mapa extralaboral Forma A (7 dimensiones) |
| mapa_extralaboral_b.php | Mapa extralaboral Forma B (7 dimensiones) |
| mapa_estres_a.php | Mapa estres Forma A con tabla de 31 sintomas |
| mapa_estres_b.php | Mapa estres Forma B con tabla de 31 sintomas |

---

## 2. COLORES OFICIALES POR NIVEL DE RIESGO

### Intralaboral y Extralaboral
```php
$colores = [
    'sin_riesgo'      => '#4CAF50',  // Verde
    'riesgo_bajo'     => '#8BC34A',  // Verde claro
    'riesgo_medio'    => '#FFC107',  // Amarillo (texto negro #333)
    'riesgo_alto'     => '#FF9800',  // Naranja
    'riesgo_muy_alto' => '#F44336',  // Rojo
];
```

### Estres (nomenclatura diferente)
```php
$coloresEstres = [
    'muy_bajo'  => '#4CAF50',  // Verde
    'bajo'      => '#8BC34A',  // Verde claro
    'medio'     => '#FFC107',  // Amarillo (texto negro #333)
    'alto'      => '#FF9800',  // Naranja
    'muy_alto'  => '#F44336',  // Rojo
];
```

### Color de texto segun fondo
```php
function getTextColor($nivel) {
    // Solo riesgo_medio y medio usan texto negro
    return in_array($nivel, ['riesgo_medio', 'medio']) ? '#333' : '#fff';
}
```

---

## 3. CONSULTA SQL PARA OBTENER DATOS

### Query principal desde calculated_results
```sql
SELECT
    intralaboral_form_type,        -- 'A' o 'B'
    intralaboral_total_nivel,      -- 'sin_riesgo', 'riesgo_bajo', etc.
    intralaboral_total_puntaje,    -- decimal ej: 25.5
    extralaboral_total_nivel,
    extralaboral_total_puntaje,
    estres_total_nivel,            -- 'muy_bajo', 'bajo', 'medio', 'alto', 'muy_alto'
    estres_total_puntaje,
    -- Dominios intralaborales
    dom_liderazgo_puntaje,
    dom_liderazgo_nivel,
    dom_control_puntaje,
    dom_control_nivel,
    dom_demandas_puntaje,
    dom_demandas_nivel,
    dom_recompensas_puntaje,
    dom_recompensas_nivel,
    -- Dimensiones intralaborales (19 para Forma A, 16 para Forma B)
    dim_caracteristicas_liderazgo_puntaje,
    dim_relaciones_sociales_puntaje,
    dim_retroalimentacion_puntaje,
    dim_relacion_colaboradores_puntaje,  -- Solo Forma A
    dim_claridad_rol_puntaje,
    dim_capacitacion_puntaje,
    dim_participacion_manejo_cambio_puntaje,
    dim_oportunidades_desarrollo_puntaje,
    dim_control_autonomia_puntaje,
    dim_demandas_ambientales_puntaje,
    dim_demandas_emocionales_puntaje,
    dim_demandas_cuantitativas_puntaje,
    dim_influencia_trabajo_entorno_extralaboral_puntaje,
    dim_demandas_responsabilidad_puntaje,  -- Solo Forma A
    dim_demandas_carga_mental_puntaje,
    dim_consistencia_rol_puntaje,          -- Solo Forma A
    dim_demandas_jornada_trabajo_puntaje,
    dim_recompensas_pertenencia_puntaje,
    dim_reconocimiento_compensacion_puntaje,
    -- Dimensiones extralaborales (7 dimensiones)
    extralaboral_tiempo_fuera_puntaje,
    extralaboral_relaciones_familiares_puntaje,
    extralaboral_comunicacion_puntaje,
    extralaboral_situacion_economica_puntaje,
    extralaboral_caracteristicas_vivienda_puntaje,
    extralaboral_influencia_entorno_puntaje,
    extralaboral_desplazamiento_puntaje
FROM calculated_results
WHERE battery_service_id = ?
```

---

## 4. ESTRUCTURA DE DATOS PARA MAPA DE CALOR TABLA

### Estructura para tabla de distribucion por niveles
```php
$heatmapData = [
    'intralaboral' => [
        'A' => [
            'sin_riesgo' => 5,      // cantidad de trabajadores
            'riesgo_bajo' => 3,
            'riesgo_medio' => 2,
            'riesgo_alto' => 1,
            'riesgo_muy_alto' => 0,
        ],
        'B' => [
            'sin_riesgo' => 8,
            'riesgo_bajo' => 4,
            'riesgo_medio' => 2,
            'riesgo_alto' => 0,
            'riesgo_muy_alto' => 1,
        ],
    ],
    'extralaboral' => [
        'A' => [...],
        'B' => [...],
    ],
    'estres' => [
        'A' => [
            'muy_bajo' => 5,
            'bajo' => 3,
            'medio' => 2,
            'alto' => 1,
            'muy_alto' => 0,
        ],
        'B' => [...],
    ],
    'total_a' => 11,   // total trabajadores Forma A
    'total_b' => 15,   // total trabajadores Forma B
    'total' => 26,     // total general
];
```

---

## 5. ESTRUCTURA DE DOMINIOS Y DIMENSIONES

### INTRALABORAL FORMA A (19 dimensiones)
```php
$dominiosFormaA = [
    [
        'nombre' => 'LIDERAZGO Y RELACIONES SOCIALES EN EL TRABAJO',
        'key' => 'dom_liderazgo',
        'dimensiones' => [
            ['key' => 'dim_caracteristicas_liderazgo', 'nombre' => 'Caracteristicas del liderazgo'],
            ['key' => 'dim_relaciones_sociales', 'nombre' => 'Relaciones sociales en el trabajo'],
            ['key' => 'dim_retroalimentacion', 'nombre' => 'Retroalimentacion del desempeno'],
            ['key' => 'dim_relacion_colaboradores', 'nombre' => 'Relacion con los colaboradores'],  // SOLO FORMA A
        ]
    ],
    [
        'nombre' => 'CONTROL SOBRE EL TRABAJO',
        'key' => 'dom_control',
        'dimensiones' => [
            ['key' => 'dim_claridad_rol', 'nombre' => 'Claridad de rol'],
            ['key' => 'dim_capacitacion', 'nombre' => 'Capacitacion'],
            ['key' => 'dim_participacion_manejo_cambio', 'nombre' => 'Participacion y manejo del cambio'],
            ['key' => 'dim_oportunidades_desarrollo', 'nombre' => 'Oportunidades para el uso y desarrollo de habilidades'],
            ['key' => 'dim_control_autonomia', 'nombre' => 'Control y autonomia sobre el trabajo'],
        ]
    ],
    [
        'nombre' => 'DEMANDAS DEL TRABAJO',
        'key' => 'dom_demandas',
        'dimensiones' => [
            ['key' => 'dim_demandas_ambientales', 'nombre' => 'Demandas ambientales y de esfuerzo fisico'],
            ['key' => 'dim_demandas_emocionales', 'nombre' => 'Demandas emocionales'],
            ['key' => 'dim_demandas_cuantitativas', 'nombre' => 'Demandas cuantitativas'],
            ['key' => 'dim_influencia_trabajo_entorno_extralaboral', 'nombre' => 'Influencia del trabajo sobre el entorno extralaboral'],
            ['key' => 'dim_demandas_responsabilidad', 'nombre' => 'Exigencias de responsabilidad del cargo'],  // SOLO FORMA A
            ['key' => 'dim_carga_mental', 'nombre' => 'Demandas de carga mental'],
            ['key' => 'dim_consistencia_rol', 'nombre' => 'Consistencia del rol'],  // SOLO FORMA A
            ['key' => 'dim_demandas_jornada_trabajo', 'nombre' => 'Demandas de la jornada de trabajo'],
        ]
    ],
    [
        'nombre' => 'RECOMPENSAS',
        'key' => 'dom_recompensas',
        'dimensiones' => [
            ['key' => 'dim_recompensas_pertenencia', 'nombre' => 'Recompensas derivadas de la pertenencia a la organizacion'],
            ['key' => 'dim_reconocimiento_compensacion', 'nombre' => 'Reconocimiento y compensacion'],
        ]
    ],
];
```

### INTRALABORAL FORMA B (16 dimensiones)
```php
$dominiosFormaB = [
    [
        'nombre' => 'LIDERAZGO Y RELACIONES SOCIALES EN EL TRABAJO',
        'key' => 'dom_liderazgo',
        'dimensiones' => [
            ['key' => 'dim_caracteristicas_liderazgo', 'nombre' => 'Caracteristicas del liderazgo'],
            ['key' => 'dim_relaciones_sociales', 'nombre' => 'Relaciones sociales en el trabajo'],
            ['key' => 'dim_retroalimentacion', 'nombre' => 'Retroalimentacion del desempeno'],
            // NO INCLUYE: dim_relacion_colaboradores
        ]
    ],
    [
        'nombre' => 'CONTROL SOBRE EL TRABAJO',
        'key' => 'dom_control',
        'dimensiones' => [
            ['key' => 'dim_claridad_rol', 'nombre' => 'Claridad de rol'],
            ['key' => 'dim_capacitacion', 'nombre' => 'Capacitacion'],
            ['key' => 'dim_participacion_manejo_cambio', 'nombre' => 'Participacion y manejo del cambio'],
            ['key' => 'dim_oportunidades_desarrollo', 'nombre' => 'Oportunidades desarrollo habilidades'],
            ['key' => 'dim_control_autonomia', 'nombre' => 'Control y autonomia sobre el trabajo'],
        ]
    ],
    [
        'nombre' => 'DEMANDAS DEL TRABAJO',
        'key' => 'dom_demandas',
        'dimensiones' => [
            ['key' => 'dim_demandas_ambientales', 'nombre' => 'Demandas ambientales y esfuerzo fisico'],
            ['key' => 'dim_demandas_emocionales', 'nombre' => 'Demandas emocionales'],
            ['key' => 'dim_demandas_cuantitativas', 'nombre' => 'Demandas cuantitativas'],
            ['key' => 'dim_influencia_trabajo_entorno_extralaboral', 'nombre' => 'Influencia trabajo sobre entorno extra'],
            // NO INCLUYE: dim_demandas_responsabilidad
            ['key' => 'dim_carga_mental', 'nombre' => 'Demandas de carga mental'],
            // NO INCLUYE: dim_consistencia_rol
            ['key' => 'dim_demandas_jornada_trabajo', 'nombre' => 'Demandas de la jornada de trabajo'],
        ]
    ],
    [
        'nombre' => 'RECOMPENSAS',
        'key' => 'dom_recompensas',
        'dimensiones' => [
            ['key' => 'dim_reconocimiento_compensacion', 'nombre' => 'Reconocimiento y compensacion'],
            ['key' => 'dim_recompensas_pertenencia', 'nombre' => 'Recompensas pertenencia organizacion'],
        ]
    ],
];
```

### EXTRALABORAL (7 dimensiones - igual para A y B)
```php
$dimensionesExtralaboral = [
    ['key' => 'dim_tiempo_fuera', 'nombre' => 'Tiempo fuera del trabajo'],
    ['key' => 'dim_relaciones_familiares', 'nombre' => 'Relaciones familiares'],
    ['key' => 'dim_comunicacion_relaciones', 'nombre' => 'Comunicacion y relaciones interpersonales'],
    ['key' => 'dim_situacion_economica', 'nombre' => 'Situacion economica del grupo familiar'],
    ['key' => 'dim_caracteristicas_vivienda', 'nombre' => 'Caracteristicas de la vivienda y de su entorno'],
    ['key' => 'dim_influencia_entorno', 'nombre' => 'Influencia del entorno extralaboral sobre el trabajo'],
    ['key' => 'dim_desplazamiento', 'nombre' => 'Desplazamiento vivienda - trabajo - vivienda'],
];
```

---

## 6. LAS 31 PREGUNTAS DE ESTRES

```php
$estresQuestions = [
    1 => 'Dolores en el cuello y espalda o tension muscular',
    2 => 'Problemas gastrointestinales, ulcera peptica, acidez, problemas digestivos o del colon',
    3 => 'Problemas respiratorios',
    4 => 'Dolor de cabeza',
    5 => 'Trastornos del sueno como somnolencia durante el dia o desvelo en la noche',
    6 => 'Palpitaciones en el pecho o problemas cardiacos',
    7 => 'Cambios fuertes del apetito',
    8 => 'Problemas relacionados con la funcion de los organos genitales (impotencia, frigidez)',
    9 => 'Dificultad en las relaciones familiares',
    10 => 'Dificultad para permanecer quieto o dificultad para iniciar actividades',
    11 => 'Dificultad en las relaciones con otras personas',
    12 => 'Sensacion de aislamiento y desinteres',
    13 => 'Sentimiento de sobrecarga de trabajo',
    14 => 'Dificultad para concentrarse, olvidos frecuentes',
    15 => 'Aumento en el numero de accidentes de trabajo',
    16 => 'Sentimiento de frustracion, de no haber hecho lo que se queria en la vida',
    17 => 'Cansancio, tedio o desgano',
    18 => 'Disminucion del rendimiento en el trabajo o poca creatividad',
    19 => 'Deseo de no asistir al trabajo',
    20 => 'Bajo compromiso o poco interes con lo que se hace',
    21 => 'Dificultad para tomar decisiones',
    22 => 'Deseo de cambiar de empleo',
    23 => 'Sentimiento de soledad y miedo',
    24 => 'Sentimiento de irritabilidad, actitudes y pensamientos negativos',
    25 => 'Sentimiento de angustia, preocupacion o tristeza',
    26 => 'Consumo de drogas para aliviar la tension o los nervios',
    27 => 'Sentimientos de que "no vale nada", o "no sirve para nada"',
    28 => 'Consumo de bebidas alcoholicas o cafe o cigarrillo',
    29 => 'Sentimiento de que esta perdiendo la razon',
    30 => 'Comportamientos rigidos, obstinacion o terquedad',
    31 => 'Sensacion de no poder manejar los problemas de la vida'
];
```

### Estructura de datos para tabla de sintomas
```php
$symptomData = [
    1 => [
        'question' => 'Dolores en el cuello y espalda o tension muscular',
        'siempre' => 3,        // cantidad que respondieron "Siempre"
        'casi_siempre' => 5,   // cantidad que respondieron "Casi Siempre"
        'a_veces' => 8,        // cantidad que respondieron "A Veces"
        'nunca' => 10,         // cantidad que respondieron "Nunca"
        'total' => 26,         // total de respuestas
        'critico' => 8,        // siempre + casi_siempre (requieren intervencion)
    ],
    2 => [...],
    // ... hasta 31
];
```

---

## 7. HTML COMPATIBLE CON DOMPDF

### IMPORTANTE: Limitaciones de DomPDF
- **NO usar `display: flex`** - NO SOPORTADO
- **NO usar `display: grid`** - NO SOPORTADO
- **USAR `display: table` y `display: table-cell`** para columnas
- **USAR `float: left/right`** para alinear elementos
- **USAR `line-height`** para centrado vertical
- **Usar unidades `pt` no `px`** para mayor precision

### Tabla de distribucion por niveles (estilo ejecutivo)
```html
<table style="width: 100%; border-collapse: collapse; font-size: 7.5pt; margin: 5pt 0;">
    <thead>
        <tr>
            <th style="background-color: #006699; color: white; padding: 4pt; border: 1pt solid #333; text-align: center; width: 15%;">Cuestionario</th>
            <th style="background-color: #006699; color: white; padding: 4pt; border: 1pt solid #333; text-align: center; width: 10%;">Forma</th>
            <th style="background-color: #4CAF50; color: white; padding: 4pt; border: 1pt solid #333; text-align: center; width: 13%;">Sin Riesgo</th>
            <th style="background-color: #8BC34A; color: white; padding: 4pt; border: 1pt solid #333; text-align: center; width: 13%;">Bajo</th>
            <th style="background-color: #FFEB3B; color: #333; padding: 4pt; border: 1pt solid #333; text-align: center; width: 13%;">Medio</th>
            <th style="background-color: #FF9800; color: white; padding: 4pt; border: 1pt solid #333; text-align: center; width: 13%;">Alto</th>
            <th style="background-color: #F44336; color: white; padding: 4pt; border: 1pt solid #333; text-align: center; width: 13%;">Muy Alto</th>
            <th style="background-color: #006699; color: white; padding: 4pt; border: 1pt solid #333; text-align: center; width: 10%;">Total</th>
        </tr>
    </thead>
    <tbody>
        <!-- Intralaboral Forma A -->
        <tr>
            <td rowspan="2" style="padding: 4pt; border: 1pt solid #333; text-align: center; font-weight: bold; background-color: #f0f0f0;">Intralaboral</td>
            <td style="padding: 4pt; border: 1pt solid #333; text-align: center;">Forma A</td>
            <td style="padding: 4pt; border: 1pt solid #333; text-align: center; background-color: #4CAF50; color: white;">5 (45%)</td>
            <td style="padding: 4pt; border: 1pt solid #333; text-align: center; background-color: #8BC34A; color: white;">3 (27%)</td>
            <td style="padding: 4pt; border: 1pt solid #333; text-align: center; background-color: #e0e0e0; color: #999;">0 (0%)</td>
            <td style="padding: 4pt; border: 1pt solid #333; text-align: center; background-color: #FF9800; color: white;">2 (18%)</td>
            <td style="padding: 4pt; border: 1pt solid #333; text-align: center; background-color: #F44336; color: white;">1 (9%)</td>
            <td style="padding: 4pt; border: 1pt solid #333; text-align: center; font-weight: bold;">11</td>
        </tr>
        <!-- Intralaboral Forma B -->
        <tr>
            <td style="padding: 4pt; border: 1pt solid #333; text-align: center;">Forma B</td>
            <!-- ... celdas ... -->
        </tr>
        <!-- Extralaboral y Estres siguen el mismo patron -->
    </tbody>
</table>
```

### Mapa de calor visual con dominios y dimensiones
```html
<div style="border: 2px solid #333; background: #fff; margin-bottom: 20px;">
    <!-- INTRALABORAL -->
    <div style="display: table; width: 100%; border-bottom: 2px solid #333;">
        <!-- Celda Total Intralaboral -->
        <div style="display: table-cell; width: 20%; vertical-align: middle; text-align: center; padding: 15px; background: #4CAF50; color: #fff; border-right: 2px solid #333; font-weight: bold; font-size: 9pt;">
            TOTAL GENERAL<br>FACTORES DE RIESGO<br>PSICOSOCIAL<br>INTRALABORAL<br>
            <span style="font-size: 14pt;">25.5</span>
        </div>
        <!-- Contenedor de dominios -->
        <div style="display: table-cell; width: 80%; vertical-align: top;">
            <!-- Dominio Liderazgo -->
            <div style="display: table; width: 100%; border-bottom: 1px solid #666;">
                <div style="display: table-cell; width: 30%; vertical-align: middle; text-align: center; padding: 8px; background: #8BC34A; color: #fff; border-right: 1px solid #666; font-weight: bold; font-size: 8pt;">
                    LIDERAZGO Y RELACIONES SOCIALES EN EL TRABAJO<br>
                    <span style="font-size: 11pt;">18.5</span>
                </div>
                <div style="display: table-cell; width: 70%; vertical-align: top;">
                    <!-- Dimension -->
                    <div style="padding: 4px 8px; background: #4CAF50; color: #fff; border-bottom: 1px solid rgba(255,255,255,0.3); font-size: 8pt; overflow: hidden;">
                        <span style="float: left;">Caracteristicas del liderazgo</span>
                        <span style="float: right; font-weight: bold;">12.3</span>
                    </div>
                    <!-- Mas dimensiones... -->
                </div>
            </div>
            <!-- Mas dominios... -->
        </div>
    </div>

    <!-- EXTRALABORAL -->
    <div style="display: table; width: 100%; border-bottom: 2px solid #333;">
        <div style="display: table-cell; width: 50%; vertical-align: middle; text-align: center; padding: 12px; background: #8BC34A; color: #fff; border-right: 2px solid #333; font-weight: bold; font-size: 10pt;">
            FACTORES EXTRALABORALES<br>
            <span style="font-size: 14pt;">15.2</span>
        </div>
        <div style="display: table-cell; width: 50%; vertical-align: top;">
            <!-- Dimensiones extralaborales -->
            <div style="padding: 3px 8px; background: #4CAF50; color: #fff; border-bottom: 1px solid rgba(255,255,255,0.3); font-size: 8pt; overflow: hidden;">
                <span style="float: left;">Tiempo fuera del trabajo</span>
                <span style="float: right; font-weight: bold;">8.5</span>
            </div>
            <!-- Mas dimensiones... -->
        </div>
    </div>

    <!-- ESTRES -->
    <div style="text-align: center; padding: 15px; background: #FFC107; color: #333; font-weight: bold; font-size: 10pt;">
        SINTOMAS DE ESTRES<br>
        <span style="font-size: 16pt;">14.2</span>
    </div>
</div>
```

### Leyenda de colores
```html
<div style="text-align: center; margin-bottom: 15px; padding: 8px; background: #f5f5f5;">
    <span style="display: inline-block; margin: 0 10px; font-size: 9pt;">
        <span style="display: inline-block; width: 12px; height: 12px; background: #4CAF50; vertical-align: middle;"></span>
        Sin Riesgo/Muy Bajo
    </span>
    <span style="display: inline-block; margin: 0 10px; font-size: 9pt;">
        <span style="display: inline-block; width: 12px; height: 12px; background: #8BC34A; vertical-align: middle;"></span>
        Riesgo Bajo
    </span>
    <span style="display: inline-block; margin: 0 10px; font-size: 9pt;">
        <span style="display: inline-block; width: 12px; height: 12px; background: #FFC107; vertical-align: middle;"></span>
        Riesgo Medio
    </span>
    <span style="display: inline-block; margin: 0 10px; font-size: 9pt;">
        <span style="display: inline-block; width: 12px; height: 12px; background: #FF9800; vertical-align: middle;"></span>
        Riesgo Alto
    </span>
    <span style="display: inline-block; margin: 0 10px; font-size: 9pt;">
        <span style="display: inline-block; width: 12px; height: 12px; background: #F44336; vertical-align: middle;"></span>
        Riesgo Muy Alto
    </span>
</div>
```

### Tabla de sintomas de estres (31 preguntas)
```html
<table style="width: 100%; border-collapse: collapse; font-size: 7pt; margin-bottom: 15px;">
    <thead>
        <tr>
            <th style="width: 4%; background-color: #f8f9fa; padding: 6px 3px; border: 1px solid #ddd; text-align: center; font-weight: bold;">#</th>
            <th style="width: 40%; background-color: #f8f9fa; padding: 6px 3px; border: 1px solid #ddd; text-align: left; font-weight: bold;">Sintoma / Pregunta</th>
            <th style="width: 11%; background-color: #dc3545; color: white; padding: 6px 3px; border: 1px solid #ddd; text-align: center; font-weight: bold;">Siempre</th>
            <th style="width: 11%; background-color: #fd7e14; color: white; padding: 6px 3px; border: 1px solid #ddd; text-align: center; font-weight: bold;">Casi Siempre</th>
            <th style="width: 11%; background-color: #ffc107; color: #333; padding: 6px 3px; border: 1px solid #ddd; text-align: center; font-weight: bold;">A Veces</th>
            <th style="width: 11%; background-color: #28a745; color: white; padding: 6px 3px; border: 1px solid #ddd; text-align: center; font-weight: bold;">Nunca</th>
            <th style="width: 12%; background-color: #6c757d; color: white; padding: 6px 3px; border: 1px solid #ddd; text-align: center; font-weight: bold;">Critico</th>
        </tr>
    </thead>
    <tbody>
        <tr style="background: #fff;">
            <td style="padding: 4px 3px; border: 1px solid #ddd; text-align: center; font-weight: bold;">1</td>
            <td style="padding: 4px 3px; border: 1px solid #ddd; text-align: left; font-size: 6.5pt;">Dolores en el cuello y espalda o tension muscular</td>
            <td style="padding: 4px 3px; border: 1px solid #ddd; text-align: center; font-weight: bold; background: #ffebee; color: #c62828;">3</td>
            <td style="padding: 4px 3px; border: 1px solid #ddd; text-align: center; font-weight: bold; background: #fff3e0; color: #e65100;">5</td>
            <td style="padding: 4px 3px; border: 1px solid #ddd; text-align: center;">8</td>
            <td style="padding: 4px 3px; border: 1px solid #ddd; text-align: center;">10</td>
            <td style="padding: 4px 3px; border: 1px solid #ddd; text-align: center; font-weight: bold; background: #ffcdd2; color: #b71c1c;">8</td>
        </tr>
        <!-- Filas 2-31 -->
    </tbody>
</table>

<!-- Leyenda de la tabla -->
<div style="padding: 8px; background: #e3f2fd; border-left: 3px solid #2196F3; font-size: 7pt;">
    <strong>Interpretacion de la tabla:</strong>
    <ul style="margin: 5px 0 0 15px; padding: 0;">
        <li><strong>Siempre:</strong> El trabajador presenta este sintoma de forma permanente (mayor riesgo)</li>
        <li><strong>Casi Siempre:</strong> El trabajador presenta este sintoma frecuentemente (alto riesgo)</li>
        <li><strong>A Veces:</strong> El trabajador presenta este sintoma ocasionalmente (riesgo moderado)</li>
        <li><strong>Nunca:</strong> El trabajador NO presenta este sintoma (sin riesgo)</li>
        <li><strong>Critico:</strong> Suma de "Siempre" + "Casi Siempre" - indica cuantas personas requieren intervencion urgente</li>
    </ul>
</div>
```

---

## 8. TEXTOS PARA LAS PAGINAS

### Pagina: Introduccion a Resultados
```
Titulo: Resultados

Se conto con la participacion de [TOTAL_PARTICIPANTES] personas vinculadas a [NOMBRE_EMPRESA],
de los cuales [FORMA_A] personas son equivalentes al [PCT_FORMA_A]% con el cuestionario
intralaboral Tipo A y [FORMA_B] personas fueron evaluados con el formato intralaboral Tipo B,
equivalente al [PCT_FORMA_B]%. Al mismo tiempo, el 100% de los participantes diligenciaron
el Cuestionario de evaluacion riesgo psicosocial Intralaboral, extralaboral, el cuestionario
de estres y la ficha de datos generales; asi como el consentimiento informado.

Los resultados obtenidos se presentan en el siguiente orden:

1. Resultados de las condiciones individuales - Informacion sociodemografica y ocupacional.
2. Resultados de la evaluacion de factores de riesgo psicosocial (intralaboral, extralaboral).
3. Resultados de la evaluacion de estres ocupacional.

Subtitulo: Resultados de las condiciones individuales - Informacion sociodemografica y ocupacional

Estas hacen referencia a algunas caracteristicas propias del colaborador como sexo, edad,
estado civil, nivel educativo, escala socioeconomica, tipo de personas, vease la Tabla de
variables sociodemograficas, y algunos aspectos ocupacionales como antiguedad de la empresa,
el cargo, tipo de contratacion y modalidad de pago, vease Tabla de resultados ocupacionales.
```

### Pagina: Conclusiones de la Bateria
```
Titulo: Conclusion Total De Aplicacion Bateria De Riesgo Psicosocial

Los dominios principales de la bateria de riesgo psicosocial como sus respectivas dimensiones
son calificadas a partir de la interpretacion del mayor puntaje obtenido, siendo este el factor
determinante para establecer el panorama de riesgo psicosocial.

Los resultados obtenidos del nivel de riesgo psicosocial a nivel general en [NOMBRE_EMPRESA]
se clasifican en [NIVEL_RIESGO_GENERAL] (Cuestionario Tipo A = Calificacion de [PUNTAJE_A]
catalogado como [NIVEL_A]) / Cuestionario Tipo B con una calificacion de [PUNTAJE_B]
catalogado como [NIVEL_B]).

Las dimensiones y dominios que se encuentren bajo esta categoria seran objeto de acciones o
programas de intervencion, a fin de mantenerlos en los niveles de riesgo mas bajos posibles.

De acuerdo con el articulo 3 de la resolucion 2764 del 2022, el periodo de la proxima medicion
se establece de acuerdo con el puntaje de la dimension principal intralaboral observado
anteriormente. Asimismo, se debe realizar una nueva medicion en un plazo maximo de
[UN ANO / DOS ANOS].

Titulo: Conclusion Del Profesional

El entorno de analisis de la bateria de riesgo psicosocial consta de tres dimensiones principales
las cuales constantemente interactuan entre si; Al observar la perspectiva global se denota el
nivel de [NIVEL_ESTRES] en la dimension principal de estres (Cuestionario Tipo A = Calificacion
de [PUNTAJE_ESTRES_A] catalogado como [NIVEL_ESTRES_A] Cuestionario Tipo B = Calificacion de
[PUNTAJE_ESTRES_B] catalogado como [NIVEL_ESTRES_B])

[SI NIVEL ES ALTO O MUY ALTO]:
Se sugiere proceder mediante la Accion Inmediata del Programa de vigilancia epidemiologico:
Concentracion elevada de niveles de estres sobre este grupo poblacional, Diseno de Programas
De Vigilancia Epidemiologica En Riesgo Psicosocial. El factor de riesgo causa, o podria causar,
alteraciones serias en la salud del trabajador, aumentando en consecuencia el numero de
incapacidades laborales.

Se sugiere realizar una nueva medicion dentro de [UN ANO / DOS ANOS] en funcion de llevar a
cabo un correcto seguimiento de efectividad en la dimension estres.
```

### Interpretacion Factores Extralaborales
```
Los factores extralaborales comprenden los aspectos del entorno familiar, social y economico
del trabajador. Incluyen las condiciones del lugar de vivienda, que pueden influir en la salud
y bienestar del individuo. El nivel de riesgo detectado es: [NIVEL_EXTRALABORAL].
```

### Nota Metodologica Forma A
```
La Forma A contiene 19 dimensiones distribuidas en 4 dominios. Se aplica a jefes, profesionales
y tecnicos con responsabilidad de coordinacion o personal a cargo. Baremos segun Resolucion 2404/2019.
```

### Nota Metodologica Forma B
```
La Forma B contiene 16 dimensiones distribuidas en 4 dominios. Se aplica a auxiliares y operarios
sin responsabilidad de coordinacion. Baremos segun Resolucion 2404/2019.
```

### Nota Metodologica Extralaboral
```
El cuestionario extralaboral contiene 7 dimensiones que evaluan las condiciones externas al
trabajo que pueden afectar la salud del trabajador. Baremos aplicados segun Resolucion 2404/2019.
```

### Nota Metodologica Estres
```
El cuestionario de estres evalua sintomas reveladores de la presencia de reacciones de estres,
distribuidos en 31 preguntas. Baremos aplicados segun Resolucion 2404/2019.
```

### Interpretacion Estres Alto/Muy Alto
```
El nivel de sintomas de estres detectado es [NIVEL]. Se requiere intervencion inmediata en el
marco de un programa de vigilancia epidemiologica. Los trabajadores presentan alta probabilidad
de asociacion con efectos negativos en la salud fisica y mental.
```

### Interpretacion Estres Bajo/Medio
```
El nivel de sintomas de estres detectado es [NIVEL]. Se recomienda mantener acciones preventivas
y de promocion de la salud para conservar estos niveles.
```

---

## 9. CSS BASE PARA DOMPDF

```css
@page {
    margin: 85pt 57pt 85pt 113pt;  /* ICONTEC: Superior 3cm, Derecho 2cm, Inferior 3cm, Izquierdo 4cm */
}

body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 10pt;
    line-height: 1.4;
    color: #333;
}

h1 {
    font-size: 16pt;
    color: #006699;
    text-align: center;
    margin: 0 0 20pt 0;
    padding-bottom: 8pt;
    border-bottom: 2pt solid #006699;
}

h2 {
    font-size: 14pt;
    color: #006699;
    text-align: center;
    margin: 0 0 15pt 0;
    padding-bottom: 5pt;
    border-bottom: 1pt solid #006699;
}

h3 {
    font-size: 12pt;
    color: #006699;
    margin: 15pt 0 10pt 0;
}

p {
    text-align: justify;
    margin: 0 0 8pt 0;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin: 10pt 0;
}

th, td {
    border: 1pt solid #333;
    padding: 5pt;
    text-align: left;
    vertical-align: top;
    font-size: 9pt;
}

th {
    background-color: #006699;
    color: white;
    font-weight: bold;
    text-align: center;
}

.page-break {
    page-break-after: always;
}
```

---

## 10. PERIODICIDAD DE NUEVA MEDICION

Segun Resolucion 2764/2022:

| Nivel de Riesgo | Periodicidad |
|-----------------|--------------|
| Sin Riesgo | 2 anos |
| Riesgo Bajo | 2 anos |
| Riesgo Medio | 2 anos |
| Riesgo Alto | 1 ano |
| Riesgo Muy Alto | 1 ano |

```php
$periodicidad = in_array($nivelRiesgoGeneral, ['riesgo_alto', 'riesgo_muy_alto']) ? 1 : 2;
```

---

## 11. RESUMEN DE DIFERENCIAS FORMA A vs FORMA B

| Aspecto | Forma A | Forma B |
|---------|---------|---------|
| Poblacion objetivo | Jefes, Profesionales, Tecnicos con coordinacion | Auxiliares, Operarios |
| Dimensiones intralaborales | 19 | 16 |
| Dimension exclusiva A | Relacion con los colaboradores | - |
| Dimension exclusiva A | Exigencias de responsabilidad del cargo | - |
| Dimension exclusiva A | Consistencia del rol | - |
| Dimensiones extralaborales | 7 | 7 |
| Preguntas estres | 31 | 31 |

---

## 12. ACCIONES RECOMENDADAS POR NIVEL

```php
$acciones = [
    'sin_riesgo'      => 'Mantener condiciones actuales',
    'riesgo_bajo'     => 'Acciones preventivas de mantenimiento',
    'riesgo_medio'    => 'Observacion y acciones preventivas',
    'riesgo_alto'     => 'Intervencion en marco de vigilancia epidemiologica',
    'riesgo_muy_alto' => 'Intervencion inmediata en marco de vigilancia epidemiologica',
];
```

---

## REFERENCIA RAPIDA

### Query minimo para mapa de calor ejecutivo
```sql
SELECT
    intralaboral_form_type,
    intralaboral_total_nivel,
    extralaboral_total_nivel,
    estres_total_nivel
FROM calculated_results
WHERE battery_service_id = ?
```

### Conteo por niveles
```php
foreach ($results as $row) {
    $forma = $row['intralaboral_form_type'];  // 'A' o 'B'
    $nivelIntra = $row['intralaboral_total_nivel'];

    $heatmapData['intralaboral'][$forma][$nivelIntra]++;
}
```

### Calculo de porcentaje
```php
$pct = round(($count / $total) * 100);
echo $count . ' (' . $pct . '%)';
```
