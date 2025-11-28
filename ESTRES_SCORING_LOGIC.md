# Lógica de Calificación del Cuestionario de Estrés

## Contexto del Problema

Estamos implementando el **Cuestionario para la Evaluación del Estrés** de la Batería de Riesgo Psicosocial del Ministerio de la Protección Social de Colombia. El cálculo está produciendo resultados incorrectos (Puntaje Transformado = 0.0 cuando debería ser ~25 con todas las respuestas en "Siempre").

## Estructura del Cuestionario

- **Total de preguntas:** 31
- **Escala Likert:** 4 opciones (NO incluye "Casi nunca")
  - Siempre
  - Casi siempre
  - A veces
  - Nunca

## Sistema de Puntuación por Grupos (Tabla 4 - Ministerio)

Las 31 preguntas se dividen en 3 grupos con diferentes escalas de puntuación:

### Grupo 1 (9 ítems: 1, 2, 3, 9, 13, 14, 15, 23, 24)
- Siempre = 9 puntos
- Casi siempre = 6 puntos
- A veces = 3 puntos
- Nunca = 0 puntos

### Grupo 2 (13 ítems: 4, 5, 6, 10, 11, 16, 17, 18, 19, 25, 26, 27, 28)
- Siempre = 6 puntos
- Casi siempre = 4 puntos
- A veces = 2 puntos
- Nunca = 0 puntos

### Grupo 3 (9 ítems: 7, 8, 12, 20, 21, 22, 29, 30, 31)
- Siempre = 3 puntos
- Casi siempre = 2 puntos
- A veces = 1 punto
- Nunca = 0 puntos

## Fórmula de Cálculo del Puntaje Bruto

**NO es una suma simple**. Según la metodología oficial (confirmada por fórmula Excel):

```
Promedio Items 1-8 = (Suma puntos items 1-8) / 8
Subtotal 1 = Promedio Items 1-8 × 4

Promedio Items 9-12 = (Suma puntos items 9-12) / 4
Subtotal 2 = Promedio Items 9-12 × 3

Promedio Items 13-22 = (Suma puntos items 13-22) / 10
Subtotal 3 = Promedio Items 13-22 × 2

Promedio Items 23-31 = (Suma puntos items 23-31) / 9
Subtotal 4 = Promedio Items 23-31 × 1

Puntaje Bruto Total = Subtotal 1 + Subtotal 2 + Subtotal 3 + Subtotal 4
```

**NOTA IMPORTANTE:** El Subtotal 4 también usa PROMEDIO (no suma directa como se creía inicialmente).

## Fórmula de Transformación

```
Puntaje Transformado = (Puntaje Bruto Total / 61.16) × 100
```

**Factor oficial:** 61.16 (confirmado por documento del Ministerio)

## Baremos de Interpretación

Existen dos tipos de baremos según el cargo:

### Baremo para Jefes/Profesionales/Técnicos
- Muy bajo: 0.0 - 7.8
- Bajo: 7.9 - 12.6
- Medio: 12.7 - 17.7
- Alto: 17.8 - 25.0
- Muy alto: 25.1 - 100

### Baremo para Auxiliares/Operarios
- Muy bajo: 0.0 - 6.5
- Bajo: 6.6 - 11.8
- Medio: 11.9 - 17.0
- Alto: 17.1 - 23.4
- Muy alto: 23.5 - 100

## Implementación Actual (PHP)

### Flujo de Datos Original (CON PROBLEMA)

1. **Frontend (estres.php - JavaScript)**
   ```javascript
   // ANTIGUO: Convertía texto → números antes de enviar
   const valoresGrupo1 = {
       'siempre': 9,
       'casi_siempre': 6,
       'a_veces': 3,
       'nunca': 0
   };
   // Enviaba: {1: 9, 2: 6, 3: 3, ...}
   ```

2. **Backend (AssessmentController.php)**
   ```php
   // Recibía JSON con números
   $responses = json_decode($responsesRaw, true);
   // Guardaba en DB: answer_value = 9, 6, 3, 0
   ```

3. **Modelo (ResponseModel.php)**
   ```php
   // Validación requería enteros
   'answer_value' => 'required|integer'
   ```

4. **Cálculo (CalculationService.php)**
   ```php
   // Convertía números → texto según grupo
   if (in_array($questionNumber, $itemsGrupo1)) {
       $valueToText = [9 => 'siempre', 6 => 'casi_siempre', ...];
   }
   // Resultado: 'siempre', 'casi_siempre', etc.
   ```

5. **Scoring (EstresScoring.php)**
   ```php
   // Convertía texto → números OTRA VEZ
   private static $puntajes = [
       'siempre' => [...],
       'casi_siempre' => [...],
       // ESTO CAUSABA DOBLE CONVERSIÓN
   ];
   ```

**PROBLEMA:** La conversión texto→número→texto→número inflaba los puntajes.

### Flujo de Datos Nuevo (SOLUCIÓN IMPLEMENTADA)

1. **Frontend (estres.php - JavaScript)**
   ```javascript
   // NUEVO: Envía texto directamente
   const formData = new FormData(this);
   // Enviaba: {responses: {1: 'siempre', 2: 'casi_siempre', ...}}
   ```

2. **Backend (AssessmentController.php)**
   ```php
   // Recibe array directo (no JSON)
   $responses = $this->request->getPost('responses');
   // Guarda en DB: answer_value = 'siempre', 'casi_siempre', etc.
   ```

3. **Modelo (ResponseModel.php)**
   ```php
   // Validación flexible
   'answer_value' => 'required' // Acepta texto o entero
   ```

4. **Cálculo (CalculationService.php)**
   ```php
   // Pasa valores directamente (ya son texto)
   foreach ($responses as $response) {
       $answersArray[$response['question_number']] = $response['answer_value'];
   }
   // Resultado: 'siempre', 'casi_siempre', etc. (sin conversión)
   ```

5. **Scoring (EstresScoring.php)**
   ```php
   // Convierte texto → números UNA SOLA VEZ
   private static function calificarItems($respuestas) {
       // Aplica puntajes según grupo de cada ítem
   }
   ```

## Archivos Modificados

### 1. `app/Views/assessment/estres.php`
**Cambio:** Eliminada conversión JavaScript
- ❌ ANTES: `prepareFormDataWithNumericValues()` convertía texto→números
- ✅ AHORA: `new FormData(this)` envía texto directo

### 2. `app/Controllers/AssessmentController.php` (líneas 372-381)
**Cambio:** Recepción de array en lugar de JSON
```php
// ❌ ANTES:
$responsesRaw = $this->request->getPost('responses');
$responses = json_decode($responsesRaw, true);

// ✅ AHORA:
$responses = $this->request->getPost('responses');
```

### 3. `app/Models/ResponseModel.php` (línea 41)
**Cambio:** Validación flexible
```php
// ❌ ANTES:
'answer_value' => 'required|integer'

// ✅ AHORA:
'answer_value' => 'required' // Acepta texto o entero
```

### 4. `app/Services/CalculationService.php` (líneas 231-238)
**Cambio:** Eliminada conversión número→texto
```php
// ❌ ANTES: 50+ líneas con mapeo por grupos
if (is_numeric($value)) {
    if (in_array($questionNumber, $itemsGrupo1)) {
        $valueToText = [9 => 'siempre', ...];
    }
}

// ✅ AHORA: Simple asignación directa
foreach ($responses as $response) {
    $answersArray[$response['question_number']] = $response['answer_value'];
}
```

### 5. `app/Libraries/EstresScoring.php`
**NO MODIFICADO** - Contiene la lógica oficial del Ministerio

## Resultado Esperado con "Siempre" en Todas las Respuestas

Si todas las 31 respuestas son "Siempre":

### Cálculo Manual
```
Items 1-8:
- Item 1: 9 (Grupo 1)
- Item 2: 9 (Grupo 1)
- Item 3: 9 (Grupo 1)
- Item 4: 6 (Grupo 2)
- Item 5: 6 (Grupo 2)
- Item 6: 6 (Grupo 2)
- Item 7: 3 (Grupo 3)
- Item 8: 3 (Grupo 3)
Suma = 51
Promedio = 51/8 = 6.375
Subtotal 1 = 6.375 × 4 = 25.5

Items 9-12:
- Item 9: 9 (Grupo 1)
- Item 10: 6 (Grupo 2)
- Item 11: 6 (Grupo 2)
- Item 12: 3 (Grupo 3)
Suma = 24
Promedio = 24/4 = 6.0
Subtotal 2 = 6.0 × 3 = 18.0

Items 13-22:
- Items 13, 14, 15: 9 (Grupo 1) = 27
- Items 16, 17, 18, 19: 6 (Grupo 2) = 24
- Items 20, 21, 22: 3 (Grupo 3) = 9
Suma = 60
Promedio = 60/10 = 6.6
Subtotal 3 = 6.6 × 2 = 13.2

Items 23-31:
- Items 23, 24: 9 (Grupo 1) = 18
- Items 25, 26, 27, 28: 6 (Grupo 2) = 24
- Items 29, 30, 31: 3 (Grupo 3) = 9
Subtotal 4 = 18 + 24 + 9 = 51

Puntaje Bruto Total = 25.5 + 18.0 + 13.2 + 51 = 107.7
Puntaje Transformado = (107.7 / 61.16) × 100 = 176.1
```

## ✅ SOLUCIÓN ENCONTRADA

**El error estaba en el Subtotal 4 de EstresScoring.php (línea 175)**

### Error Original:
```php
// 6. Sumar ítems 23 a 31
$subtotal4 = self::sumarItems($puntajesItems, range(23, 31)); // ❌ SUMA
```

### Corrección Aplicada:
```php
// 6. Calcular promedio de ítems 23 a 31 (NO es suma, es promedio según fórmula oficial Excel)
$promedio23a31 = self::calcularPromedioGrupo($puntajesItems, range(23, 31));
$subtotal4 = $promedio23a31; // ✅ PROMEDIO
```

### Verificación con "Siempre" en todas las respuestas:
```
Subtotal 1 = 25.5
Subtotal 2 = 18.0
Subtotal 3 = 13.2
Subtotal 4 = 5.666... (era 51 con SUMA) ✅
Puntaje Bruto = 61.166666... (era 107.7 con SUMA)
Puntaje Transformado = 100.00 ✅ (era 176.1 con SUMA)
```

**Fórmula Excel confirmada:**
```excel
=(((PROMEDIO(MF8:MM8)*4)+(PROMEDIO(MN8:MQ8)*3)+(PROMEDIO(MR8:NA8)*2)+(PROMEDIO(NB8:NJ8)))/61.1666666666666)*100
```

Nota: Todos los 4 subtotales usan PROMEDIO.
