# Periodicidad de Evaluacion - Resolucion 2404/2019

## Fundamento Legal

**Articulo 3 - Resolucion 2404 de 2019** (Ministerio del Trabajo de Colombia)

---

## Regla Principal

| Nivel de Riesgo (cualquier forma) | Periodicidad |
|-----------------------------------|--------------|
| Alto o Muy Alto                   | **ANUAL**    |
| Medio o Bajo                      | Cada 2 anos  |

---

## Texto Normativo Clave

> "Para calcular el nivel de riesgo psicosocial intralaboral de las empresas se debe establecer el promedio del puntaje bruto total... **por separado**... tanto para la **forma A**, como para la **forma B**, y en el caso que el nivel de riesgo para **alguna de estas dos formas** sea alto o muy alto la evaluacion debe realizarse de **forma anual**."

**Fuente:** Articulo 3, Resolucion 2404/2019 - Ministerio del Trabajo

---

## Interpretacion Practica

### NO existe baremo de "conjunto"

La Resolucion 2404/2019 **NO define** un baremo para mezclar Forma A y Forma B.
Cada forma tiene sus propios baremos en la Tabla 33.

### Criterio de Periodicidad: El nivel mas alto prevalece

Aunque la norma no usa literalmente las palabras "prevalece" o "vision de conjunto":

1. Cada empresa calcula **por separado** el nivel de riesgo para Forma A y Forma B
2. Si **cualquiera** de las dos formas presenta nivel alto o muy alto -> evaluacion **anual**
3. Solo si **ambas** formas estan en nivel medio o bajo -> evaluacion cada **2 anos**

### Ejemplo Practico

| Escenario | Forma A | Forma B | Periodicidad |
|-----------|---------|---------|--------------|
| 1         | Bajo    | Bajo    | 2 anos       |
| 2         | Medio   | Bajo    | 2 anos       |
| 3         | Bajo    | **Alto**| **ANUAL**    |
| 4         | **Alto**| Medio   | **ANUAL**    |
| 5         | **Muy Alto** | **Alto** | **ANUAL** |

---

## Calculo del Nivel de Riesgo Intralaboral (por forma)

Segun Articulo 3, para determinar el nivel de riesgo de la empresa:

1. **Obtener puntajes brutos totales** de cada trabajador evaluado
2. **Calcular promedio** del puntaje bruto total (separado por forma)
3. **Transformar** usando la formula: `(Promedio Bruto / Factor) x 100`
   - Factor Forma A: 492
   - Factor Forma B: 388
4. **Clasificar** usando Tabla 33 (baremos intralaboral total)

### Baremos Tabla 33 - Intralaboral Total

| Nivel | Forma A | Forma B |
|-------|---------|---------|
| Sin riesgo | 0.0 - 19.7 | 0.0 - 20.6 |
| Riesgo bajo | 19.8 - 25.8 | 20.7 - 26.0 |
| Riesgo medio | 25.9 - 31.5 | 26.1 - 33.2 |
| Riesgo alto | 31.6 - 38.8 | 33.3 - 40.0 |
| Riesgo muy alto | 38.9 - 100 | 40.1 - 100 |

---

## Implicaciones para el Sistema PsyRisk

### 1. Calculo de Periodicidad
- Evaluar nivel de Forma A (promedio de trabajadores Forma A)
- Evaluar nivel de Forma B (promedio de trabajadores Forma B)
- Si alguno es alto/muy alto -> periodicidad = 1 ano
- Si ambos son medio/bajo -> periodicidad = 2 anos

### 2. NO mezclar formas
- Nunca promediar puntajes de Forma A con Forma B
- Nunca aplicar un baremo "generico" a valores mezclados
- Los baremos son especificos por forma (Tabla 33)

### 3. Reporte de Periodicidad
El sistema debe mostrar:
- Nivel de riesgo Forma A (con n trabajadores)
- Nivel de riesgo Forma B (con n trabajadores)
- **Periodicidad recomendada** basada en el nivel mas alto

---

## Referencias Normativas

1. **Resolucion 2404 de 2019** - Ministerio del Trabajo
   - Articulo 3: Periodicidad de evaluacion
   - Anexo Tecnico: Tablas de baremos

2. **Resolucion 2764 de 2022** - Ministerio del Trabajo
   - Actualizacion de disposiciones
   - Confidencialidad de resultados individuales

3. **Bateria de Instrumentos** - Universidad Javeriana / Ministerio de la Proteccion Social
   - Manual tecnico con formulas y tablas

---

## Historial del Documento

| Fecha | Version | Cambios |
|-------|---------|---------|
| 2025-12-18 | 1.0 | Creacion inicial basada en analisis Art. 3 Res. 2404/2019 |

---

*Documento de referencia tecnica para el sistema PsyRisk*
