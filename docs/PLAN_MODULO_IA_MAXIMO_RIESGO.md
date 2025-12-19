# Plan: Módulo IA de Máximo Riesgo

## Objetivo

Crear una tabla que almacene los **peores resultados** (máximo riesgo) entre Forma A y B para cada dominio y dimensión, alimentando un módulo de IA especializado en análisis de intervención.

---

## Nueva Tabla: `max_risk_results`

### Estructura Propuesta

```sql
CREATE TABLE max_risk_results (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    battery_service_id INT UNSIGNED NOT NULL,

    -- Identificación del elemento evaluado
    element_type ENUM('total', 'domain', 'dimension') NOT NULL,
    questionnaire_type ENUM('intralaboral', 'extralaboral', 'estres') NOT NULL,
    element_code VARCHAR(100) NULL,  -- NULL para totales, código para dominios/dimensiones
    element_name VARCHAR(200) NOT NULL,

    -- Resultado del PEOR caso (máximo riesgo)
    worst_score DECIMAL(5,2) NOT NULL,
    worst_risk_level VARCHAR(20) NOT NULL,
    worst_form ENUM('A', 'B') NOT NULL,

    -- Datos de Forma A (si existe)
    form_a_score DECIMAL(5,2) NULL,
    form_a_risk_level VARCHAR(20) NULL,
    form_a_count INT UNSIGNED NULL,  -- n trabajadores

    -- Datos de Forma B (si existe)
    form_b_score DECIMAL(5,2) NULL,
    form_b_risk_level VARCHAR(20) NULL,
    form_b_count INT UNSIGNED NULL,  -- n trabajadores

    -- Metadata
    has_both_forms BOOLEAN DEFAULT FALSE,
    risk_priority INT UNSIGNED DEFAULT 0,  -- 1=muy_alto, 2=alto, 3=medio, etc.

    -- IA Analysis
    ai_analysis TEXT NULL,
    ai_recommendations TEXT NULL,
    ai_generated_at DATETIME NULL,
    ai_model_version VARCHAR(50) NULL,

    -- Timestamps
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Indexes
    INDEX idx_battery_service (battery_service_id),
    INDEX idx_element_type (element_type),
    INDEX idx_questionnaire (questionnaire_type),
    INDEX idx_risk_priority (risk_priority),
    INDEX idx_worst_risk (worst_risk_level),

    FOREIGN KEY (battery_service_id) REFERENCES battery_services(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## Datos a Almacenar

### Por cada battery_service_id:

| Elemento | element_type | questionnaire_type | element_code |
|----------|--------------|-------------------|--------------|
| Total Intralaboral | total | intralaboral | NULL |
| Dominio Liderazgo | domain | intralaboral | dom_liderazgo |
| Dominio Control | domain | intralaboral | dom_control |
| Dominio Demandas | domain | intralaboral | dom_demandas |
| Dominio Recompensas | domain | intralaboral | dom_recompensas |
| Dim Características Liderazgo | dimension | intralaboral | dim_caracteristicas_liderazgo |
| Dim Relaciones Sociales | dimension | intralaboral | dim_relaciones_sociales |
| ... (19 dimensiones intralaborales) |
| Total Extralaboral | total | extralaboral | NULL |
| Dim Tiempo Fuera | dimension | extralaboral | dim_tiempo_fuera |
| ... (7 dimensiones extralaborales) |
| Total Estrés | total | estres | NULL |

**Total aproximado: ~35 registros por servicio de batería**

---

## Servicio: `MaxRiskResultsService.php`

```php
<?php
namespace App\Services;

class MaxRiskResultsService
{
    /**
     * Calcula y almacena los máximos riesgos para un servicio
     */
    public function calculateAndStore(int $batteryServiceId): array;

    /**
     * Obtiene los máximos riesgos almacenados
     */
    public function getByBatteryService(int $batteryServiceId): array;

    /**
     * Obtiene solo elementos en riesgo alto/muy alto para IA
     */
    public function getHighRiskElements(int $batteryServiceId): array;

    /**
     * Actualiza análisis de IA para un elemento
     */
    public function updateAiAnalysis(int $id, string $analysis, string $recommendations): bool;
}
```

---

## Flujo de Datos

```
┌─────────────────────────────────────────────────────────────────┐
│                    calculated_results                            │
│  (datos individuales por trabajador)                            │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│              MaxRiskResultsService::calculateAndStore()          │
│  - Separa por Forma A/B                                         │
│  - Aplica baremos específicos                                   │
│  - Determina peor resultado                                     │
│  - Asigna prioridad de riesgo                                   │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                     max_risk_results                             │
│  (35 registros por servicio: totales + dominios + dimensiones)  │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                    Módulo IA Intervención                        │
│  - Consulta elementos con risk_priority = 1 o 2                 │
│  - Genera análisis contextualizado                              │
│  - Propone planes de intervención específicos                   │
│  - Almacena en ai_analysis y ai_recommendations                 │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                    Outputs                                       │
│  - PDF Conclusión (ya existente, ahora lee de tabla)            │
│  - Mapa de Calor (ya existente, ahora lee de tabla)             │
│  - Nuevo: Reporte IA de Intervención Priorizada                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## Módulo IA: Análisis de Intervención

### Prompt Base para IA

```
Eres un especialista en Seguridad y Salud en el Trabajo (SST) de Colombia.
Analiza los siguientes resultados de riesgo psicosocial y genera:

1. ANÁLISIS: Interpretación del nivel de riesgo encontrado
2. CAUSAS PROBABLES: Factores que podrían estar generando este riesgo
3. IMPACTO: Consecuencias potenciales para trabajadores y organización
4. INTERVENCIÓN: Acciones específicas priorizadas por urgencia

CONTEXTO:
- Empresa: {company_name}
- Sector: {company_sector}
- Elemento evaluado: {element_name}
- Tipo: {element_type} ({questionnaire_type})
- Puntaje máximo riesgo: {worst_score} (Forma {worst_form})
- Nivel de riesgo: {worst_risk_level}
- Trabajadores Forma A: {form_a_count} (puntaje: {form_a_score})
- Trabajadores Forma B: {form_b_count} (puntaje: {form_b_score})

BASE NORMATIVA: Resolución 2404/2019, Resolución 2764/2022
```

### Campos de Salida IA

| Campo | Descripción |
|-------|-------------|
| `ai_analysis` | Análisis interpretativo (500-800 palabras) |
| `ai_recommendations` | Plan de intervención estructurado (JSON o texto estructurado) |
| `ai_generated_at` | Timestamp de generación |
| `ai_model_version` | Versión del modelo usado (ej: "gpt-4-turbo-2024") |

---

## Implementación por Fases

### Fase 1: Infraestructura (Actual)
1. Crear tabla `max_risk_results`
2. Crear modelo `MaxRiskResultModel`
3. Crear servicio `MaxRiskResultsService`
4. Migrar cálculo del heatmap a usar este servicio

### Fase 2: Población de Datos
1. Trigger al finalizar servicio de batería
2. Recalcular para servicios existentes
3. Validar consistencia con heatmap actual

### Fase 3: Módulo IA
1. Crear `AiInterventionService`
2. Integrar con API de IA (OpenAI/Anthropic)
3. Generar análisis para elementos alto/muy alto
4. Vista de revisión para consultor

### Fase 4: Outputs
1. Nuevo PDF: "Informe de Intervención Priorizada"
2. Dashboard de elementos críticos
3. Exportación para planes de acción

---

## Ventajas del Diseño

1. **Single Source of Truth**: Los peores resultados se calculan una vez y se reutilizan
2. **Trazabilidad**: Historial completo de análisis por servicio
3. **Performance**: Consultas rápidas sin recalcular desde calculated_results
4. **Escalabilidad**: Fácil agregar más campos de IA o métricas
5. **Auditoría**: Registro de qué modelo generó cada análisis

---

## Archivos a Crear

| Archivo | Descripción |
|---------|-------------|
| `app/Models/MaxRiskResultModel.php` | Modelo CodeIgniter |
| `app/Services/MaxRiskResultsService.php` | Lógica de cálculo y almacenamiento |
| `app/Services/AiInterventionService.php` | Integración con IA |
| `app/Controllers/MaxRiskController.php` | API/vistas para gestionar |
| `app/Database/Migrations/xxx_create_max_risk_results.php` | Migración |

---

*Plan creado: 2025-12-18*
*Estado: Pendiente de aprobación*
