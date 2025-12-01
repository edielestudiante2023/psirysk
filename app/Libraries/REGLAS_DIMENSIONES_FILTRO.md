# Reglas para Dimensiones con Preguntas Filtro

## Fuente Normativa
**Batería de Instrumentos para la Evaluación de Factores de Riesgo Psicosocial**
Ministerio de la Protección Social de Colombia

---

## Dimensiones Afectadas

### 1. Demandas Emocionales (Forma A: ítems 106-114, Forma B: ítems 89-97)

**Pregunta filtro:** "En mi trabajo debo brindar servicio a clientes o usuarios"

| Respuesta | Comportamiento |
|-----------|----------------|
| **SÍ** | Se responden los ítems y se calcula normalmente |
| **NO** | Puntaje bruto = **0**, Puntaje transformado = **0**, Nivel = **sin_riesgo** |

### 2. Relación con los Colaboradores (Forma A: ítems 115-123)

**Pregunta filtro:** "Soy jefe de otras personas en mi trabajo"

| Respuesta | Comportamiento |
|-----------|----------------|
| **SÍ** | Se responden los ítems y se calcula normalmente |
| **NO** | Puntaje bruto = **0**, Puntaje transformado = **0**, Nivel = **sin_riesgo** |

> **Nota:** Esta dimensión solo aplica para Forma A (jefes, profesionales, técnicos).

---

## Regla Clave: Las Dimensiones Filtro ENTRAN al Cálculo con Valor 0

La cartilla del Ministerio es clara:

> "la dimensión [...] obtendrá automáticamente un puntaje bruto de cero (0)"

Esto significa que:

1. **La dimensión NO se excluye** del cálculo
2. **El puntaje bruto es 0**, no NULL
3. **El factor de transformación NO se modifica**
4. **El 0 reduce el puntaje** del dominio y del total

---

## Ejemplo Aritmético (Forma A, NO atiende clientes, NO es jefe)

### Dominio Liderazgo y Relaciones Sociales

| Dimensión | Factor | PB (máximo) | PB (con filtro NO) |
|-----------|--------|-------------|-------------------|
| Características del liderazgo | 52 | 52 | 52 |
| Relaciones sociales en el trabajo | 56 | 56 | 56 |
| Retroalimentación del desempeño | 20 | 20 | 20 |
| **Relación con los colaboradores** | 36 | 36 | **0** |
| **TOTAL DOMINIO** | **164** | **164** | **128** |

**Puntaje Transformado:**
- Sin filtro: (164 / 164) × 100 = **100%**
- Con filtro NO: (128 / 164) × 100 = **78%**

### Dominio Demandas del Trabajo

| Dimensión | Factor | PB (máximo) | PB (con filtro NO) |
|-----------|--------|-------------|-------------------|
| Demandas ambientales y esfuerzo físico | 48 | 48 | 48 |
| **Demandas emocionales** | 36 | 36 | **0** |
| Demandas cuantitativas | 24 | 24 | 24 |
| Influencia trabajo/entorno extralaboral | 16 | 16 | 16 |
| Exigencias de responsabilidad del cargo | 24 | 24 | 24 |
| Demandas de carga mental | 20 | 20 | 20 |
| Consistencia del rol | 20 | 20 | 20 |
| Demandas de la jornada de trabajo | 12 | 12 | 12 |
| **TOTAL DOMINIO** | **200** | **200** | **164** |

**Puntaje Transformado:**
- Sin filtro: (200 / 200) × 100 = **100%**
- Con filtro NO: (164 / 200) × 100 = **82%**

### Total Intralaboral (Forma A)

| Dominio | PB (máximo) | PB (con filtros NO) |
|---------|-------------|---------------------|
| Liderazgo y relaciones sociales | 164 | 128 |
| Control sobre el trabajo | 84 | 84 |
| Demandas del trabajo | 200 | 164 |
| Recompensas | 44 | 44 |
| **TOTAL** | **492** | **420** |

**Puntaje Transformado:**
- Sin filtros: (492 / 492) × 100 = **100%**
- Con filtros NO: (420 / 492) × 100 = **85.4%**

---

## Ejemplo Aritmético (Forma B, NO atiende clientes)

### Dominio Demandas del Trabajo

| Dimensión | Factor | PB (máximo) | PB (con filtro NO) |
|-----------|--------|-------------|-------------------|
| Demandas ambientales y esfuerzo físico | 48 | 48 | 48 |
| **Demandas emocionales** | 36 | 36 | **0** |
| Demandas cuantitativas | 12 | 12 | 12 |
| Influencia trabajo/entorno extralaboral | 16 | 16 | 16 |
| Demandas de carga mental | 20 | 20 | 20 |
| Demandas de la jornada de trabajo | 24 | 24 | 24 |
| **TOTAL DOMINIO** | **156** | **156** | **120** |

**Puntaje Transformado:**
- Sin filtro: (156 / 156) × 100 = **100%**
- Con filtro NO: (120 / 156) × 100 = **76.9%**

### Total Intralaboral (Forma B)

| Dominio | PB (máximo) | PB (con filtro NO) |
|---------|-------------|---------------------|
| Liderazgo y relaciones sociales | 120 | 120 |
| Control sobre el trabajo | 72 | 72 |
| Demandas del trabajo | 156 | 120 |
| Recompensas | 40 | 40 |
| **TOTAL** | **388** | **352** |

**Puntaje Transformado:**
- Sin filtro: (388 / 388) × 100 = **100%**
- Con filtro NO: (352 / 388) × 100 = **90.7%**

---

## Implementación en el Código

### IntralaboralAScoring.php

```php
// En calcularPuntajesBrutosDimensiones():
if ($dimension === 'demandas_emocionales' && !$atiendeClientes) {
    $puntajes[$dimension] = 0;  // NO es NULL, es 0
    continue;
}
if ($dimension === 'relacion_con_colaboradores' && !$esJefe) {
    $puntajes[$dimension] = 0;  // NO es NULL, es 0
    continue;
}

// En transformarPuntajesDominios() y transformarPuntajeTotal():
// El factor de transformación NO se ajusta
// Las dimensiones filtro entran como 0, reduciendo el puntaje
```

### IntralaboralBScoring.php

```php
// En calcularPuntajesBrutosDimensiones():
if ($dimension === 'demandas_emocionales' && !$atiendeClientes) {
    $puntajes[$dimension] = 0;  // NO es NULL, es 0
    continue;
}

// El factor de transformación NO se ajusta
```

---

## Resumen

| Aspecto | Comportamiento Correcto |
|---------|------------------------|
| Puntaje bruto de dimensión filtro | **0** (no NULL) |
| Puntaje transformado de dimensión filtro | **0** |
| Nivel de riesgo de dimensión filtro | **sin_riesgo** |
| Factor de transformación del dominio | **NO se modifica** |
| Factor de transformación del total | **NO se modifica** |
| Efecto en dominio/total | **Reduce el puntaje** |

---

## Referencias

- Batería de Instrumentos para la Evaluación de Factores de Riesgo Psicosocial
- Ministerio de la Protección Social, República de Colombia
- Tablas 21-33 del manual oficial

---

*Documento actualizado: Diciembre 2025*
*Implementación validada contra ejemplos aritméticos de la cartilla oficial*
