# PROCESO ESMERALDA - AUDITORÍA DE BAREMOS FORMA B
## Sistema de Auditoría Exhaustiva para Intralaboral Forma B

**Fecha creación:** 2025-11-24
**Versión:** 1.0
**Objetivo:** Garantizar 100% conformidad con Tabla 30 y Tabla 31 del manual oficial

---

## 🟢 ALCANCE DEL PROCESO ESMERALDA

### Forma B - Intralaboral (16 Dimensiones)

**Tabla 30: Baremos para las dimensiones intralaborales - Forma B**

#### Dominio 1: Liderazgo y relaciones sociales en el trabajo (4 dimensiones)
1. Características del liderazgo
2. Relaciones sociales en el trabajo
3. Retroalimentación del desempeño
4. Relación con los colaboradores (subordinados)

#### Dominio 2: Control sobre el trabajo (3 dimensiones)
5. Claridad de rol
6. Capacitación
7. Participación y manejo del cambio

#### Dominio 3: Demandas del trabajo (6 dimensiones)
8. Demandas ambientales y de esfuerzo físico
9. Demandas emocionales
10. Demandas cuantitativas
11. Influencia del trabajo sobre el entorno extralaboral
12. Demandas de la jornada de trabajo
13. (Sin Exigencias de responsabilidad)
14. (Sin Demandas de carga mental)
15. (Sin Consistencia del rol)

#### Dominio 4: Recompensas (2 dimensiones)
16. Recompensas derivadas de la pertenencia
17. Reconocimiento y compensación

**Total Forma B: 16 dimensiones** (vs 19 en Forma A)

**Diferencias vs Forma A:**
- ❌ NO incluye: Exigencias de responsabilidad del cargo
- ❌ NO incluye: Demandas de carga mental
- ❌ NO incluye: Consistencia del rol

---

## 🟢 FASE 1: PREPARACIÓN ESMERALDA

### 1.1 Material Oficial Forma B
- [ ] Manual oficial - Tabla 30 (Dimensiones Forma B)
- [ ] Manual oficial - Tabla 31 (Dominios - igual A y B)
- [ ] Manual oficial - Tabla 33 (Total Forma B)

### 1.2 Búsqueda Específica Forma B
```bash
# Buscar archivos específicos de Forma B
grep -r "forma.*B\|formab\|forma_b" app/ --include="*.php"
grep -r "IntralaboralB" app/ --include="*.php"
```

### 1.3 Archivos Críticos Forma B
- [ ] `app/Libraries/IntralaboralBScoring.php`
- [ ] `app/Controllers/ReportsController.php` (métodos Forma B)
- [ ] `app/Views/reports/intralaboral/detail_forma_b.php`
- [ ] `app/Views/workers/results_forma_b.php`

---

## 🟢 FASE 2: AUDITORÍA DIMENSIONES FORMA B (TABLA 30)

### Plantilla por Dimensión Forma B

```markdown
## Dimensión [#]: [NOMBRE]

**Tabla 30 - Forma B**
- Sin riesgo: [X.X - Y.Y]
- Riesgo bajo: [X.X - Y.Y]
- Riesgo medio: [X.X - Y.Y]
- Riesgo alto: [X.X - Y.Y]
- Riesgo muy alto: [X.X - Y.Y]

**Ubicaciones en código:**

### Búsqueda:
```bash
grep -n "nombre_dimension" app/**/*.php
```

### Resultados:
1. IntralaboralBScoring.php: Línea [X]
   - Estado: ✅/❌

2. ReportsController.php: Línea [X]
   - Método: [nombre]
   - Estado: ✅/❌

### Auditoría:
| Nivel | Código | Manual | Estado |
|-------|--------|--------|--------|
| Sin riesgo | [X-Y] | [X-Y] | ✅/❌ |
| Riesgo bajo | [X-Y] | [X-Y] | ✅/❌ |
| Riesgo medio | [X-Y] | [X-Y] | ✅/❌ |
| Riesgo alto | [X-Y] | [X-Y] | ✅/❌ |
| Riesgo muy alto | [X-Y] | [X-Y] | ✅/❌ |

**Errores:** [#]
**Acción:** [Corregir/Conforme]
```

---

## 🟢 FASE 3: CHECKLIST COMPLETO FORMA B

### Dominio 1: Liderazgo (4 dimensiones)

#### ✅ 1. Características del liderazgo
- [ ] Tabla 30 baremos extraídos
- [ ] IntralaboralBScoring.php auditado
- [ ] ReportsController.php auditado
- [ ] Views auditadas
- [ ] Estado: ⬜ Pendiente / ✅ Conforme / ❌ Error → ✅ Corregido

#### ✅ 2. Relaciones sociales en el trabajo
- [ ] Tabla 30 baremos extraídos
- [ ] IntralaboralBScoring.php auditado
- [ ] ReportsController.php auditado
- [ ] Views auditadas
- [ ] Estado: ⬜ / ✅ / ❌ → ✅

#### ✅ 3. Retroalimentación del desempeño
- [ ] Tabla 30 baremos extraídos
- [ ] IntralaboralBScoring.php auditado
- [ ] ReportsController.php auditado
- [ ] Views auditadas
- [ ] Estado: ⬜ / ✅ / ❌ → ✅

#### ✅ 4. Relación con los colaboradores
- [ ] Tabla 30 baremos extraídos
- [ ] IntralaboralBScoring.php auditado
- [ ] ReportsController.php auditado
- [ ] Views auditadas
- [ ] Estado: ⬜ / ✅ / ❌ → ✅

### Dominio 2: Control (3 dimensiones)

#### ✅ 5. Claridad de rol
- [ ] Estado: ⬜ / ✅ / ❌ → ✅

#### ✅ 6. Capacitación
- [ ] Estado: ⬜ / ✅ / ❌ → ✅

#### ✅ 7. Participación y manejo del cambio
- [ ] Estado: ⬜ / ✅ / ❌ → ✅

### Dominio 3: Demandas (6 dimensiones)

#### ✅ 8. Demandas ambientales
- [ ] Estado: ⬜ / ✅ / ❌ → ✅

#### ✅ 9. Demandas emocionales
- [ ] Estado: ⬜ / ✅ / ❌ → ✅

#### ✅ 10. Demandas cuantitativas
- [ ] Estado: ⬜ / ✅ / ❌ → ✅

#### ✅ 11. Influencia del trabajo sobre entorno extralaboral
- [ ] Estado: ⬜ / ✅ / ❌ → ✅

#### ✅ 12. Demandas de la jornada de trabajo
- [ ] Estado: ⬜ / ✅ / ❌ → ✅

#### ⚠️ 13. (NO APLICA en Forma B)
- Exigencias de responsabilidad: Solo Forma A

### Dominio 4: Recompensas (2 dimensiones)

#### ✅ 14. Recompensas derivadas de la pertenencia
- [ ] Estado: ⬜ / ✅ / ❌ → ✅

#### ✅ 15. Reconocimiento y compensación
- [ ] Estado: ⬜ / ✅ / ❌ → ✅

---

## 🟢 FASE 4: DOMINIOS FORMA B (TABLA 31)

### Tabla 31 - Dominios (Igual para A y B)

**Nota:** Los dominios usan los MISMOS baremos para Forma A y Forma B según Tabla 31.

#### Dominio: Liderazgo y relaciones sociales
- Sin riesgo: 0.0 - 9.1
- Riesgo bajo: 9.2 - 17.7
- Riesgo medio: 17.8 - 25.6
- Riesgo alto: 25.7 - 34.8
- Riesgo muy alto: 34.9 - 100

- [ ] IntralaboralBScoring.php
- [ ] ReportsController.php (método Forma B)
- [ ] Estado: ✅

#### Dominio: Control sobre el trabajo
- Sin riesgo: 0.0 - 10.7
- Riesgo bajo: 10.8 - 19.0
- Riesgo medio: 19.1 - 29.8
- Riesgo alto: 29.9 - 40.5
- Riesgo muy alto: 40.6 - 100

- [ ] IntralaboralBScoring.php
- [ ] ReportsController.php
- [ ] Estado: ✅

#### Dominio: Demandas del trabajo
- Sin riesgo: 0.0 - 28.5
- Riesgo bajo: 28.6 - 35.0
- Riesgo medio: 35.1 - 41.5
- Riesgo alto: 41.6 - 47.5
- Riesgo muy alto: 47.6 - 100

- [ ] IntralaboralBScoring.php
- [ ] ReportsController.php
- [ ] Estado: ✅

#### Dominio: Recompensas
- Sin riesgo: 0.0 - 4.5
- Riesgo bajo: 4.6 - 11.4
- Riesgo medio: 11.5 - 20.5
- Riesgo alto: 20.6 - 29.5
- Riesgo muy alto: 29.6 - 100

- [ ] IntralaboralBScoring.php
- [ ] ReportsController.php
- [ ] Estado: ✅

---

## 🟢 FASE 5: TOTAL FORMA B (TABLA 33)

### Tabla 33 - Intralaboral Total Forma B

**Baremos oficiales:**
- Sin riesgo: 0.0 - 20.6
- Riesgo bajo: 20.7 - 26.0
- Riesgo medio: 26.1 - 31.2
- Riesgo alto: 31.3 - 38.7
- Riesgo muy alto: 38.8 - 100

**Ubicaciones a auditar:**
- [ ] IntralaboralBScoring.php: Factor transformación (388)
- [ ] IntralaboralBScoring.php: Baremo total
- [ ] ReportsController.php: Baremo total (métodos Forma B)
- [ ] Views: Validaciones y rangos mostrados

---

## 🟢 FASE 6: PRUEBAS FORMA B

### URLs a Verificar

1. [ ] `http://localhost/psyrisk/reports/intralaboral-b/1`
   - Screenshot: ✅
   - Rangos correctos: ✅
   - Dominios correctos: ✅

2. [ ] `http://localhost/psyrisk/workers/results/[ID_FORMA_B]`
   - Worker con Forma B identificado
   - Resultados verificados
   - Niveles de riesgo correctos

3. [ ] `http://localhost/psyrisk/reports/heatmap/1`
   - Diferenciación Forma A vs B
   - Rangos correctos para B
   - Colores apropiados

---

## 🟢 RESUMEN PROCESO ESMERALDA

### Checklist Final

**Dimensiones Forma B (16 total):**
- [ ] Dominio Liderazgo (4): ⬜⬜⬜⬜ → ✅✅✅✅
- [ ] Dominio Control (3): ⬜⬜⬜ → ✅✅✅
- [ ] Dominio Demandas (6): ⬜⬜⬜⬜⬜⬜ → ✅✅✅✅✅✅
- [ ] Dominio Recompensas (2): ⬜⬜ → ✅✅

**Dominios (4 total):**
- [ ] Liderazgo: ⬜ → ✅
- [ ] Control: ⬜ → ✅
- [ ] Demandas: ⬜ → ✅
- [ ] Recompensas: ⬜ → ✅

**Total Forma B:**
- [ ] Tabla 33: ⬜ → ✅
- [ ] Factor 388: ⬜ → ✅

**Archivos auditados:**
- [ ] IntralaboralBScoring.php: ✅
- [ ] ReportsController.php (métodos B): ✅
- [ ] Views Forma B: ✅

**Pruebas:**
- [ ] URLs verificadas: ✅
- [ ] Screenshots tomados: ✅
- [ ] Sin errores visuales: ✅

### Certificación Esmeralda

```
✅ CERTIFICACIÓN PROCESO ESMERALDA

Se certifica que la FORMA B del cuestionario Intralaboral
ha sido auditada exhaustivamente contra la Tabla 30 y Tabla 31
del manual oficial.

Dimensiones auditadas: 16/16 ✅
Dominios auditados: 4/4 ✅
Conformidad: 100% ✅

Fecha: [fecha]
Auditor: [nombre]
```

---

## 📊 DIFERENCIAS CLAVE FORMA A vs FORMA B

### Dimensiones Exclusivas de Forma A (NO en B):
1. ❌ Exigencias de responsabilidad del cargo
2. ❌ Demandas de carga mental
3. ❌ Consistencia del rol

### Dimensiones Comunes (A y B):
- ✅ Las 16 dimensiones de Forma B están en Forma A
- ⚠️ Pero con BAREMOS DIFERENTES en Tabla 29 vs Tabla 30

### Dominios:
- ✅ IGUALES para ambas formas (Tabla 31)
- ✅ Mismos 4 dominios
- ✅ Mismos rangos de baremos

### Total:
- ⚠️ DIFERENTES baremos (Tabla 33)
- Forma A: sin_riesgo hasta 19.7
- Forma B: sin_riesgo hasta 20.6

---

**Elaborado por:** Usuario + Claude
**Fecha:** 2025-11-24
**Versión:** 1.0
**Estado:** ✅ Listo para usar
