# Arquitectura del Dashboard Intralaboral - PsyRisk

**Documento Técnico de Referencia para Replicación en Extralaboral y Estrés**

---

## 📋 Índice

1. [Resumen Ejecutivo](#resumen-ejecutivo)
2. [Flujo de Datos Completo](#flujo-de-datos-completo)
3. [Arquitectura del Controlador](#arquitectura-del-controlador)
4. [Arquitectura de la Vista](#arquitectura-de-la-vista)
5. [Sistema de Filtros (Segmentadores)](#sistema-de-filtros-segmentadores)
6. [Sistema de Gráficos (Charts)](#sistema-de-gráficos-charts)
7. [Tabla de Resultados (DataTable)](#tabla-de-resultados-datatable)
8. [Cálculos Estadísticos](#cálculos-estadísticos)
9. [Baremos y Clasificación de Riesgo](#baremos-y-clasificación-de-riesgo)
10. [Plantilla de Replicación](#plantilla-de-replicación)

---

## 1. Resumen Ejecutivo

### Propósito del Dashboard

El dashboard intralaboral es una interfaz analítica completa que permite:

- **Visualizar** resultados de evaluaciones psicosociales intralaborales
- **Filtrar** datos por múltiples dimensiones demográficas y de riesgo
- **Analizar** distribuciones de riesgo por dominios y dimensiones
- **Comparar** resultados entre Forma A (jefes/profesionales) y Forma B (auxiliares/operarios)
- **Identificar** áreas críticas de intervención
- **Exportar** reportes en Excel y PDF

### Componentes Principales

1. **Controlador**: `ReportsController::intralaboral()`
2. **Vista**: `app/Views/reports/intralaboral/dashboard.php`
3. **Ruta**: `/reports/intralaboral/{serviceId}`
4. **Dependencias**: Bootstrap 5, jQuery, DataTables, Chart.js, ChartDataLabels

---

## 2. Flujo de Datos Completo

```
┌─────────────────────────────────────────────────────────────────┐
│ 1. USUARIO ACCEDE A URL: /reports/intralaboral/1               │
└──────────────────────┬──────────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────────┐
│ 2. CONTROLADOR: ReportsController::intralaboral($serviceId)    │
│    ├─ Verifica acceso del usuario al servicio                  │
│    ├─ Consulta DB con JOINs (calculated_results + workers      │
│    │  + worker_demographics)                                   │
│    ├─ Extrae valores únicos para segmentadores                 │
│    ├─ Calcula estadísticas con calculateIntralaboralStats()    │
│    └─ Pasa datos a la vista                                    │
└──────────────────────┬──────────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────────┐
│ 3. VISTA: dashboard.php                                         │
│    ├─ Renderiza navbar con botones de exportación              │
│    ├─ Muestra tarjetas de estadísticas (dominios)              │
│    ├─ Genera panel de filtros (segmentadores)                  │
│    ├─ Crea canvas para 9 gráficos Chart.js                     │
│    ├─ Renderiza tabla HTML con todos los resultados            │
│    └─ Embebe JavaScript con datos PHP como JSON                │
└──────────────────────┬──────────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────────┐
│ 4. JAVASCRIPT: Inicialización                                  │
│    ├─ Convierte statsData = <?= json_encode($stats) ?>         │
│    ├─ Convierte allResults = <?= json_encode($results) ?>      │
│    ├─ Inicializa DataTable                                     │
│    ├─ Crea 9 gráficos Chart.js                                 │
│    ├─ Asigna event listeners a filtros                         │
│    └─ Sistema listo para interacción                           │
└──────────────────────┬──────────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────────┐
│ 5. USUARIO INTERACTÚA CON FILTROS                              │
│    └─ Evento 'change' → applyFilters()                         │
└──────────────────────┬──────────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────────┐
│ 6. FUNCIÓN applyFilters()                                       │
│    ├─ Filtra allResults según todos los criterios              │
│    ├─ Recalcula estadísticas con calculateStats()              │
│    ├─ Actualiza 9 gráficos con updateCharts()                  │
│    └─ Actualiza DataTable con resultados filtrados             │
└─────────────────────────────────────────────────────────────────┘
```

---

## 3. Arquitectura del Controlador

### Ubicación
`app/Controllers/ReportsController.php`

### Método Principal: `intralaboral($serviceId)`

**Líneas**: 107-161

```php
public function intralaboral($serviceId)
{
    // 1. Verificación de acceso
    $service = $this->checkAccess($serviceId);

    // 2. Consulta a base de datos con JOINs
    $db = \Config\Database::connect();
    $builder = $db->table('calculated_results cr');
    $builder->select('cr.*,
                      wd.birth_year,
                      (YEAR(CURDATE()) - wd.birth_year) as age,
                      wd.stratum,
                      wd.housing_type,
                      wd.time_in_company_type,
                      wd.time_in_company_years,
                      w.name as worker_name,
                      w.document as worker_document');
    $builder->join('worker_demographics wd', 'wd.worker_id = cr.worker_id', 'left');
    $builder->join('workers w', 'w.id = cr.worker_id', 'left');
    $builder->where('cr.battery_service_id', $serviceId);
    $results = $builder->get()->getResultArray();

    // 3. Preparar segmentadores (valores únicos)
    $segmentadores = [
        'niveles_riesgo' => ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'],
        'generos' => $this->getUniqueValues($results, 'gender'),
        'departamentos' => $this->getUniqueValues($results, 'department'),
        'cargos' => $this->getUniqueValues($results, 'position'),
        'tipos_cargo' => $this->getUniqueValues($results, 'position_type'),
        'tipos_contrato' => $this->getUniqueValues($results, 'contract_type'),
        'niveles_estudio' => $this->getUniqueValues($results, 'education_level'),
        'ciudades' => $this->getUniqueValues($results, 'city_residence'),
        'estados_civiles' => $this->getUniqueValues($results, 'marital_status'),
        'estratos' => $this->getUniqueValues($results, 'stratum'),
        'tipos_vivienda' => $this->getUniqueValues($results, 'housing_type'),
        'antiguedad' => $this->getTimeInCompanyLabels($this->getUniqueValues($results, 'time_in_company_type')),
        'tipos_formulario' => ['A', 'B']
    ];

    // 4. Calcular estadísticas
    $stats = $this->calculateIntralaboralStats($results);

    // 5. Preparar data array para la vista
    $data = [
        'title' => 'Dashboard Intralaboral - ' . $service['service_name'],
        'service' => $service,
        'results' => $results,               // Array completo de resultados
        'segmentadores' => $segmentadores,   // Valores únicos para filtros
        'stats' => $stats,                   // Estadísticas calculadas
        'totalWorkers' => count($results)
    ];

    // 6. Renderizar vista
    return view('reports/intralaboral/dashboard', $data);
}
```

### Métodos Auxiliares Clave

#### `getUniqueValues($results, $field)`
Extrae valores únicos de un campo para poblamiento de filtros.

#### `calculateIntralaboralStats($results)` - **CRÍTICO**

**Líneas**: 550-700

**Estructura de retorno**:
```php
return [
    'riskDistribution' => [
        'sin_riesgo' => 12,
        'riesgo_bajo' => 8,
        'riesgo_medio' => 15,
        'riesgo_alto' => 20,
        'riesgo_muy_alto' => 5
    ],
    'domainAverages' => [
        'liderazgo' => 25.3,
        'control' => 32.1,
        'demandas' => 38.5,
        'recompensas' => 22.7
    ],
    'dimensionAverages' => [
        'dim_caracteristicas_liderazgo' => 18.2,
        'dim_relaciones_sociales' => 22.5,
        // ... 19 dimensiones total
    ],
    'domainLevels' => [
        'liderazgo' => ['nivel' => 'riesgo_medio', 'label' => 'Riesgo Medio'],
        'control' => ['nivel' => 'riesgo_alto', 'label' => 'Riesgo Alto'],
        'demandas' => ['nivel' => 'riesgo_alto', 'label' => 'Riesgo Alto'],
        'recompensas' => ['nivel' => 'riesgo_bajo', 'label' => 'Riesgo Bajo']
    ],
    'dimensionLevels' => [
        'dim_caracteristicas_liderazgo' => ['nivel' => 'riesgo_bajo', 'label' => 'Riesgo Bajo'],
        // ... 19 dimensiones
    ],
    'intralaboralTotal' => 29.6,
    'intralaboralTotalNivel' => 'riesgo_medio',
    'intralaboralTotalLabel' => 'Riesgo Medio',
    'genderDistribution' => [
        'Masculino' => 35,
        'Femenino' => 25
    ],
    'formTypeDistribution' => [
        'A' => 15,
        'B' => 45
    ]
];
```

**Proceso de cálculo**:

1. **Inicializa estructuras** con valores en 0
2. **Itera sobre cada resultado** acumulando:
   - Conteo de niveles de riesgo
   - Suma de puntajes de dominios
   - Suma de puntajes de dimensiones
   - Distribución por género
   - Distribución por tipo de formulario
3. **Calcula promedios** dividiendo totales entre cantidad de resultados
4. **Determina tipo de formulario predominante** (A o B)
5. **Clasifica niveles de riesgo** usando baremos oficiales:
   - Total intralaboral → `getIntralaboralRiskLevel()`
   - Cada dominio → `getIntralaboralRiskLevel()`
   - Cada dimensión → `getDimensionRiskLevel()`

#### `getIntralaboralRiskLevel($puntaje, $domain, $formType)`

**Líneas**: 403-513

Aplica baremos oficiales de la Resolución 2404/2019 para clasificar puntajes.

**Ejemplo de baremo (Total Intralaboral - Forma A)**:
```php
'total' => [
    'A' => [
        ['min' => 0,    'max' => 20.6, 'nivel' => 'sin_riesgo',      'label' => 'Sin Riesgo'],
        ['min' => 20.8, 'max' => 26.0, 'nivel' => 'riesgo_bajo',     'label' => 'Riesgo Bajo'],
        ['min' => 26.2, 'max' => 31.5, 'nivel' => 'riesgo_medio',    'label' => 'Riesgo Medio'],
        ['min' => 31.7, 'max' => 38.7, 'nivel' => 'riesgo_alto',     'label' => 'Riesgo Alto'],
        ['min' => 38.9, 'max' => 100,  'nivel' => 'riesgo_muy_alto', 'label' => 'Riesgo Muy Alto']
    ],
    'B' => [ /* baremos para Forma B */ ]
],
```

**Baremos disponibles para**:
- `total`: Intralaboral total
- `liderazgo`: Dominio Liderazgo
- `control`: Dominio Control
- `demandas`: Dominio Demandas
- `recompensas`: Dominio Recompensas

#### `getDimensionRiskLevel($puntaje, $dimension, $formType)`

**Líneas**: 514-545

Mapea cada dimensión a su dominio padre y usa el baremo del dominio para clasificarla.

---

## 4. Arquitectura de la Vista

### Ubicación
`app/Views/reports/intralaboral/dashboard.php`

### Estructura General (2600+ líneas)

```
┌──────────────────────────────────────────────────────────────┐
│ SECCIÓN 1: Funciones Helper PHP (líneas 1-56)               │
│  ├─ getBadgeClass($nivel)                                   │
│  ├─ getNivelEstresColor($nivel)                             │
│  └─ getNivelEstresTexto($nivel)                             │
└──────────────────────────────────────────────────────────────┘
┌──────────────────────────────────────────────────────────────┐
│ SECCIÓN 2: CSS Estilos (líneas 59-130)                      │
│  ├─ .stat-card                                              │
│  ├─ .chart-container                                        │
│  ├─ .segmentador-card                                       │
│  ├─ .filter-group                                           │
│  └─ .risk-badge                                             │
└──────────────────────────────────────────────────────────────┘
┌──────────────────────────────────────────────────────────────┐
│ SECCIÓN 3: Navbar Superior (líneas 134-158)                 │
│  ├─ Botón Volver                                            │
│  ├─ Título del Dashboard                                    │
│  └─ Botones: Imprimir, Excel, PDF, Ejecutivo               │
└──────────────────────────────────────────────────────────────┘
┌──────────────────────────────────────────────────────────────┐
│ SECCIÓN 4: Tarjeta de Información del Servicio (163-179)    │
│  ├─ Nombre del servicio                                     │
│  ├─ Empresa                                                 │
│  ├─ Fecha de creación                                       │
│  └─ Total de trabajadores                                   │
└──────────────────────────────────────────────────────────────┘
┌──────────────────────────────────────────────────────────────┐
│ SECCIÓN 5: Tarjetas de Estadísticas (182-203)               │
│  └─ Total Intralaboral (gradiente, nivel de riesgo)        │
└──────────────────────────────────────────────────────────────┘
┌──────────────────────────────────────────────────────────────┐
│ SECCIÓN 6: Tarjetas de Dominios (206-548)                   │
│  ├─ Liderazgo (accordion con 4 dimensiones)                │
│  ├─ Control (accordion con 5 dimensiones)                  │
│  ├─ Demandas (accordion con 8 dimensiones)                 │
│  └─ Recompensas (accordion con 2 dimensiones)              │
└──────────────────────────────────────────────────────────────┘
┌──────────────────────────────────────────────────────────────┐
│ SECCIÓN 7: Panel de Filtros/Segmentadores (550-776)         │
│  ├─ FILTROS DE RIESGO:                                      │
│  │   ├─ Dominio (liderazgo, control, demandas, recompensas)│
│  │   ├─ Dimensión (dinámico según dominio)                 │
│  │   ├─ Nivel de Riesgo                                     │
│  │   └─ Tipo de Formulario (A/B)                           │
│  ├─ FILTROS DEMOGRÁFICOS:                                   │
│  │   ├─ Género                                              │
│  │   ├─ Departamento                                        │
│  │   ├─ Tipo de Cargo                                       │
│  │   ├─ Cargo Específico                                    │
│  │   ├─ Nivel de Estudios                                   │
│  │   ├─ Estado Civil                                        │
│  │   ├─ Tipo de Contrato                                    │
│  │   ├─ Ciudad                                              │
│  │   ├─ Estrato                                             │
│  │   ├─ Tipo de Vivienda                                    │
│  │   └─ Antigüedad en la Empresa                           │
│  └─ Botón: Limpiar Todos los Filtros                       │
└──────────────────────────────────────────────────────────────┘
┌──────────────────────────────────────────────────────────────┐
│ SECCIÓN 8: Gráficos (777-945)                               │
│  ├─ Fila 1 (3 gráficos):                                    │
│  │   ├─ Distribución de Riesgo (doughnut)                  │
│  │   ├─ Dominios Intralaborales (bar)                      │
│  │   └─ Distribución por Género (pie)                      │
│  ├─ Dimensiones por Dominio (bar agrupado, 400px)          │
│  ├─ Comparación Forma A vs B (bar comparativo, 350px)      │
│  ├─ Top 5 Dimensiones Críticas (horizontal bar, 320px)     │
│  ├─ Top 10 Departamentos (horizontal bar, 380px)           │
│  ├─ Riesgo por Nivel Educativo (bar, 340px)                │
│  └─ Riesgo por Rango de Edad (bar, 320px)                  │
└──────────────────────────────────────────────────────────────┘
┌──────────────────────────────────────────────────────────────┐
│ SECCIÓN 9: Tabla de Resultados (947-1020)                   │
│  └─ DataTable con columnas:                                 │
│      ├─ Nombre                                              │
│      ├─ Documento                                           │
│      ├─ Género                                              │
│      ├─ Tipo Form (A/B)                                     │
│      ├─ Cargo                                               │
│      ├─ Departamento                                        │
│      ├─ Nivel Intralaboral                                  │
│      ├─ Nivel Extralaboral                                  │
│      ├─ Nivel Estrés                                        │
│      ├─ Nivel Total                                         │
│      └─ Acciones (Ver resultados individuales)             │
└──────────────────────────────────────────────────────────────┘
┌──────────────────────────────────────────────────────────────┐
│ SECCIÓN 10: Toast de Notificaciones (1022-1033)             │
│  └─ Para mostrar sugerencias de uso de filtros             │
└──────────────────────────────────────────────────────────────┘
┌──────────────────────────────────────────────────────────────┐
│ SECCIÓN 11: Scripts y JavaScript (1035-2650+)               │
│  ├─ Importación de librerías                               │
│  ├─ Conversión de datos PHP → JS                           │
│  ├─ Configuración de Chart.js                              │
│  ├─ Inicialización de DataTable                            │
│  ├─ Creación de 9 gráficos                                 │
│  ├─ Event listeners de filtros                             │
│  ├─ Funciones de filtrado                                  │
│  └─ Funciones helper                                        │
└──────────────────────────────────────────────────────────────┘
```

---

## 5. Sistema de Filtros (Segmentadores)

### Arquitectura del Panel de Filtros

**Ubicación en vista**: Líneas 550-776

**Estructura HTML**:
```html
<div class="card segmentador-card">
    <div class="card-header">
        <h6>Segmentadores / Filtros</h6>
    </div>
    <div class="card-body">
        <!-- Fila 1: Filtros de Riesgo -->
        <div class="row mb-3">
            <div class="col-md-3">
                <div class="filter-group">
                    <label><i class="fas fa-layer-group"></i>Dominio</label>
                    <select class="form-select form-select-sm" id="filter_dominio">
                        <option value="">Todos los dominios</option>
                        <option value="liderazgo">Liderazgo y Relaciones Sociales</option>
                        <option value="control">Control sobre el Trabajo</option>
                        <option value="demandas">Demandas del Trabajo</option>
                        <option value="recompensas">Recompensas</option>
                    </select>
                </div>
            </div>
            <!-- Dimension, Nivel de Riesgo, Tipo de Formulario -->
        </div>

        <!-- Filas 2-4: Filtros Demográficos (11 filtros) -->
    </div>
</div>
```

### Filtro de Dimensión Jerárquico (IMPORTANTE)

**Comportamiento especial**: El filtro de dimensiones se actualiza dinámicamente según el dominio seleccionado.

**Mapeo en JavaScript** (líneas 1080-1108):
```javascript
const dimensionesPorDominio = {
    liderazgo: [
        { value: 'dim_caracteristicas_liderazgo', text: 'Características del liderazgo' },
        { value: 'dim_relaciones_sociales', text: 'Relaciones sociales en el trabajo' },
        { value: 'dim_retroalimentacion', text: 'Retroalimentación del desempeño' },
        { value: 'dim_relacion_colaboradores', text: 'Relación con los colaboradores (Forma A)' }
    ],
    control: [
        { value: 'dim_claridad_rol', text: 'Claridad de rol' },
        { value: 'dim_capacitacion', text: 'Capacitación' },
        { value: 'dim_participacion_manejo_cambio', text: 'Participación y manejo del cambio' },
        { value: 'dim_oportunidades_desarrollo', text: 'Oportunidades para el uso y desarrollo de habilidades' },
        { value: 'dim_control_autonomia', text: 'Control y autonomía sobre el trabajo' }
    ],
    demandas: [
        { value: 'dim_demandas_ambientales', text: 'Demandas ambientales y de esfuerzo físico' },
        { value: 'dim_demandas_emocionales', text: 'Demandas emocionales' },
        { value: 'dim_demandas_cuantitativas', text: 'Demandas cuantitativas' },
        { value: 'dim_influencia_trabajo_entorno_extralaboral', text: 'Influencia del trabajo sobre el entorno extralaboral' },
        { value: 'dim_demandas_responsabilidad', text: 'Exigencias de responsabilidad del cargo (Forma A)' },
        { value: 'dim_demandas_carga_mental', text: 'Demandas de carga mental (Forma A)' },
        { value: 'dim_consistencia_rol', text: 'Consistencia del rol (Forma A)' },
        { value: 'dim_demandas_jornada_trabajo', text: 'Demandas de la jornada de trabajo' }
    ],
    recompensas: [
        { value: 'dim_reconocimiento_compensacion', text: 'Reconocimiento y compensación' },
        { value: 'dim_recompensas_pertenencia', text: 'Recompensas derivadas de la pertenencia' }
    ]
};
```

**Event Listener para cambio de dominio** (líneas 1111-1141):
```javascript
document.getElementById('filter_dominio').addEventListener('change', function() {
    const dominioSeleccionado = this.value;
    const selectDimension = document.getElementById('filter_dimension');

    // Limpiar opciones existentes
    selectDimension.innerHTML = '<option value="">Todas las dimensiones</option>';
    selectDimension.value = '';

    // Si hay dominio seleccionado, cargar solo sus dimensiones
    if (dominioSeleccionado && dimensionesPorDominio[dominioSeleccionado]) {
        dimensionesPorDominio[dominioSeleccionado].forEach(dim => {
            const option = document.createElement('option');
            option.value = dim.value;
            option.textContent = dim.text;
            selectDimension.appendChild(option);
        });
    } else {
        // Si no hay dominio, mostrar TODAS las dimensiones
        Object.values(dimensionesPorDominio).flat().forEach(dim => {
            const option = document.createElement('option');
            option.value = dim.value;
            option.textContent = dim.text;
            selectDimension.appendChild(option);
        });
    }

    // Aplicar filtros
    applyFilters();
});
```

### Función Principal: `applyFilters()`

**Ubicación**: Líneas 2526-2650+

**Proceso completo**:

```javascript
function applyFilters() {
    // PASO 1: Recopilar valores de todos los filtros
    const filters = {
        dominio: document.getElementById('filter_dominio').value,
        dimension: document.getElementById('filter_dimension').value,
        nivel_riesgo: document.getElementById('filter_nivel_riesgo').value,
        gender: document.getElementById('filter_gender').value,
        form_type: document.getElementById('filter_form_type').value,
        department: document.getElementById('filter_department').value,
        position: document.getElementById('filter_position').value,
        position_type: document.getElementById('filter_position_type').value,
        education: document.getElementById('filter_education').value,
        marital_status: document.getElementById('filter_marital_status').value,
        contract_type: document.getElementById('filter_contract_type').value,
        city: document.getElementById('filter_city').value,
        stratum: document.getElementById('filter_stratum').value,
        housing_type: document.getElementById('filter_housing_type').value,
        time_in_company: document.getElementById('filter_time_in_company').value
    };

    // PASO 2: Filtrar array completo de resultados
    const filteredResults = allResults.filter(r => {
        // Aplicar todos los filtros demográficos
        const basicFilter =
            (!filters.nivel_riesgo || r.intralaboral_total_nivel === filters.nivel_riesgo) &&
            (!filters.gender || r.gender === filters.gender) &&
            (!filters.form_type || r.intralaboral_form_type === filters.form_type) &&
            (!filters.department || r.department === filters.department) &&
            (!filters.position || r.position === filters.position) &&
            (!filters.position_type || r.position_type === filters.position_type) &&
            (!filters.education || r.education_level === filters.education) &&
            (!filters.marital_status || r.marital_status === filters.marital_status) &&
            (!filters.contract_type || r.contract_type === filters.contract_type) &&
            (!filters.city || r.city_residence === filters.city) &&
            (!filters.stratum || r.stratum === filters.stratum) &&
            (!filters.housing_type || r.housing_type === filters.housing_type) &&
            (!filters.time_in_company || r.time_in_company_type === filters.time_in_company);

        if (!basicFilter) return false;

        // Filtro de dominio
        if (filters.dominio) {
            const dominioPuntajeKey = 'dom_' + filters.dominio + '_puntaje';
            if (!r[dominioPuntajeKey] || r[dominioPuntajeKey] === 0) return false;
        }

        // Filtro de dimensión
        if (filters.dimension) {
            const dimensionPuntajeKey = filters.dimension + '_puntaje';
            if (!r[dimensionPuntajeKey] || r[dimensionPuntajeKey] === 0) return false;
        }

        return true;
    });

    // PASO 3: Actualizar DataTable con resultados filtrados
    dataTable.clear();
    filteredResults.forEach(r => {
        // Generar HTML de cada fila
        dataTable.row.add([
            r.worker_name,
            r.worker_document,
            r.gender,
            '<span class="badge bg-secondary">' + r.intralaboral_form_type + '</span>',
            r.position,
            r.department,
            generateRiskBadge(r.intralaboral_total_nivel),
            generateRiskBadge(r.extralaboral_total_nivel),
            generateEstresBadge(r.estres_total_nivel),
            generateRiskBadge(r.puntaje_total_general_nivel),
            '<a href="/psyrisk/workers/results/' + r.worker_id + '" class="btn btn-sm btn-primary" target="_blank"><i class="fas fa-eye"></i></a>'
        ]);
    });
    dataTable.draw();

    // PASO 4: Actualizar gráficos con datos filtrados
    updateCharts(filteredResults, filters.dominio, filters.dimension);
}
```

### Event Listeners de Filtros

**Ubicación**: A lo largo del JavaScript

**Patrón estándar**:
```javascript
document.getElementById('filter_X').addEventListener('change', function() {
    applyFilters();
});
```

**Filtros con listeners**:
- `filter_dominio` (con lógica especial)
- `filter_dimension`
- `filter_nivel_riesgo`
- `filter_gender`
- `filter_form_type`
- `filter_department`
- `filter_position`
- `filter_position_type`
- `filter_education`
- `filter_marital_status`
- `filter_contract_type`
- `filter_city`
- `filter_stratum`
- `filter_housing_type`
- `filter_time_in_company`

---

## 6. Sistema de Gráficos (Charts)

### Librerías Utilizadas

```html
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
```

### Configuración Global

```javascript
// Deshabilitar datalabels por defecto (se activa selectivamente)
Chart.defaults.plugins.datalabels = {
    display: false
};
```

### Datos Disponibles en JavaScript

```javascript
const statsData = <?= json_encode($stats) ?>;
const allResults = <?= json_encode($results) ?>;
```

### Los 9 Gráficos del Dashboard

#### 1. **Distribución de Riesgo** (Doughnut Chart)
**Canvas**: `#riskChart`
**Ubicación**: Líneas 1165-1198
**Tipo**: `doughnut`
**Datos**: `statsData.riskDistribution`

```javascript
let riskChart = new Chart(riskCtx, {
    type: 'doughnut',
    data: {
        labels: ['Sin Riesgo', 'Riesgo Bajo', 'Riesgo Medio', 'Riesgo Alto', 'Riesgo Muy Alto'],
        datasets: [{
            data: [
                statsData.riskDistribution.sin_riesgo,
                statsData.riskDistribution.riesgo_bajo,
                statsData.riskDistribution.riesgo_medio,
                statsData.riskDistribution.riesgo_alto,
                statsData.riskDistribution.riesgo_muy_alto
            ],
            backgroundColor: ['#28a745', '#7dce82', '#ffc107', '#fd7e14', '#dc3545']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 } } },
            datalabels: {
                color: '#fff',
                font: { weight: 'bold', size: 12 },
                formatter: function(value) {
                    return value === 0 ? '' : value;
                }
            }
        }
    },
    plugins: [ChartDataLabels]
});
```

**Comportamiento con filtros**: Se actualiza dinámicamente mostrando distribución según filtros aplicados (total, dominio o dimensión).

---

#### 2. **Dominios Intralaborales** (Bar Chart)
**Canvas**: `#domainsChart`
**Ubicación**: Líneas 1200-1301
**Tipo**: `bar`
**Datos**: `statsData.domainAverages` + `statsData.domainLevels`

**Características especiales**:
- Eje Y muestra niveles categóricos (1-5)
- Colores según nivel de riesgo
- Tooltip muestra puntaje exacto

```javascript
const domainData = [
    {
        label: 'Liderazgo',
        nivel: statsData.domainLevels?.liderazgo?.nivel || 'sin_riesgo',
        puntaje: statsData.domainAverages.liderazgo
    },
    {
        label: 'Control',
        nivel: statsData.domainLevels?.control?.nivel || 'sin_riesgo',
        puntaje: statsData.domainAverages.control
    },
    {
        label: 'Demandas',
        nivel: statsData.domainLevels?.demandas?.nivel || 'sin_riesgo',
        puntaje: statsData.domainAverages.demandas
    },
    {
        label: 'Recompensas',
        nivel: statsData.domainLevels?.recompensas?.nivel || 'sin_riesgo',
        puntaje: statsData.domainAverages.recompensas
    }
];
```

---

#### 3. **Distribución por Género** (Pie Chart)
**Canvas**: `#genderChart`
**Ubicación**: Líneas 1303-1333
**Tipo**: `pie`
**Datos**: `statsData.genderDistribution`

```javascript
const genderLabels = Object.keys(statsData.genderDistribution);
const genderData = Object.values(statsData.genderDistribution);

let genderChart = new Chart(genderCtx, {
    type: 'pie',
    data: {
        labels: genderLabels,
        datasets: [{
            data: genderData,
            backgroundColor: ['#667eea', '#764ba2', '#f093fb']
        }]
    },
    // ... opciones
});
```

---

#### 4. **Dimensiones por Dominio** (Grouped Bar Chart) - COMPLEJO
**Canvas**: `#dimensionsGroupedChart`
**Ubicación**: Líneas 1366-1495
**Tipo**: `bar` (agrupado)
**Altura**: 400px
**Datos**: Todas las 19 dimensiones agrupadas por dominio

**Estructura de datos**:
```javascript
const dimensionsData = {
    'Liderazgo y Relaciones Sociales': [
        { name: 'Características\nLiderazgo', key: 'dim_caracteristicas_liderazgo', domain: 'liderazgo' },
        { name: 'Relaciones\nSociales', key: 'dim_relaciones_sociales', domain: 'liderazgo' },
        { name: 'Retroalimentación\nDesempeño', key: 'dim_retroalimentacion', domain: 'liderazgo' },
        { name: 'Relación con\nColaboradores', key: 'dim_relacion_colaboradores', domain: 'liderazgo' }
    ],
    'Control sobre el Trabajo': [ /* 5 dimensiones */ ],
    'Demandas del Trabajo': [ /* 8 dimensiones */ ],
    'Recompensas': [ /* 2 dimensiones */ ]
};
```

**Creación de datasets**: Un dataset por cada dimensión (19 total)
```javascript
Object.entries(dimensionsData).forEach(([domainName, dimensions]) => {
    dimensions.forEach((dim, idx) => {
        const dimLevel = statsData.dimensionLevels?.[dim.key]?.nivel || 'sin_riesgo';
        const dimScore = statsData.dimensionAverages?.[dim.key] || 0;

        // Crear array con valores solo para este dominio
        const data = labels.map((label, labelIdx) => {
            if (label === domainName) {
                return riskLevelValues[dimLevel];
            }
            return null;
        });

        groupedDatasets.push({
            label: dim.name.replace(/\n/g, ' '),
            data: data,
            backgroundColor: riskColors[dimLevel],
            dimScore: dimScore,
            dimLevel: dimLevel
        });
    });
});
```

---

#### 5. **Comparación Forma A vs Forma B** (Comparative Bar)
**Canvas**: `#formsComparisonChart`
**Ubicación**: Líneas 1497-1636
**Tipo**: `bar` (comparativo)
**Altura**: 350px

**Cálculo de promedios por forma**:
```javascript
function calculateFormAverages() {
    const formaA = { liderazgo: 0, control: 0, demandas: 0, recompensas: 0, count: 0 };
    const formaB = { liderazgo: 0, control: 0, demandas: 0, recompensas: 0, count: 0 };

    allResults.forEach(r => {
        if (r.intralaboral_form_type === 'A') {
            formaA.liderazgo += parseFloat(r.dom_liderazgo_puntaje || 0);
            formaA.control += parseFloat(r.dom_control_puntaje || 0);
            formaA.demandas += parseFloat(r.dom_demandas_puntaje || 0);
            formaA.recompensas += parseFloat(r.dom_recompensas_puntaje || 0);
            formaA.count++;
        } else if (r.intralaboral_form_type === 'B') {
            // ... mismo proceso para B
        }
    });

    return {
        formaA: {
            liderazgo: formaA.count > 0 ? formaA.liderazgo / formaA.count : 0,
            // ... otros dominios
            count: formaA.count
        },
        formaB: { /* ... */ }
    };
}
```

**Datasets**:
```javascript
datasets: [
    {
        label: `Forma A (${formAverages.formaA.count} trabajadores)`,
        data: [
            formAverages.formaA.liderazgo,
            formAverages.formaA.control,
            formAverages.formaA.demandas,
            formAverages.formaA.recompensas
        ],
        backgroundColor: 'rgba(158, 158, 158, 0.7)'  // Gris claro
    },
    {
        label: `Forma B (${formAverages.formaB.count} trabajadores)`,
        data: [ /* ... */ ],
        backgroundColor: 'rgba(66, 66, 66, 0.8)'  // Gris oscuro
    }
]
```

---

#### 6. **Top 5 Dimensiones Críticas** (Horizontal Bar)
**Canvas**: `#topDimensionsChart`
**Ubicación**: Líneas 1638-1743
**Tipo**: `bar` (horizontal: `indexAxis: 'y'`)
**Altura**: 320px

**Cálculo de top 5**:
```javascript
function calculateTopDimensions(results) {
    const dimensionScores = [];

    // Recopilar todas las dimensiones con sus puntajes
    Object.values(dimensionsData).flat().forEach(dim => {
        let totalScore = 0;
        let count = 0;

        results.forEach(r => {
            const score = parseFloat(r[dim.key + '_puntaje'] || 0);
            if (score > 0) {
                totalScore += score;
                count++;
            }
        });

        if (count > 0) {
            const avgScore = totalScore / count;
            const nivel = getDomainRiskLevel(avgScore, dim.domain);
            dimensionScores.push({
                name: dim.name.replace(/\n/g, ' '),
                key: dim.key,
                score: avgScore,
                nivel: nivel,
                color: riskColors[nivel]
            });
        }
    });

    // Ordenar por puntaje descendente y tomar top 5
    return dimensionScores.sort((a, b) => b.score - a.score).slice(0, 5);
}
```

---

#### 7. **Top 10 Departamentos con Mayor Riesgo** (Horizontal Bar)
**Canvas**: `#departmentChart`
**Ubicación**: Líneas 1745-1850+
**Tipo**: `bar` (horizontal)
**Altura**: 380px

**Cálculo por departamento**:
```javascript
function calculateTopDepartments(results) {
    const departmentScores = {};

    results.forEach(r => {
        const dept = r.department || 'Sin Departamento';
        const puntaje = parseFloat(r.intralaboral_total_puntaje || 0);

        if (!departmentScores[dept]) {
            departmentScores[dept] = {
                name: dept,
                totalScore: 0,
                count: 0
            };
        }

        departmentScores[dept].totalScore += puntaje;
        departmentScores[dept].count++;
    });

    // Calcular promedio y ordenar
    const departmentList = Object.values(departmentScores)
        .map(d => ({
            name: d.name,
            avgScore: d.totalScore / d.count,
            count: d.count
        }))
        .sort((a, b) => b.avgScore - a.avgScore)
        .slice(0, 10);

    return departmentList;
}
```

---

#### 8. **Riesgo por Nivel Educativo** (Bar Chart)
**Canvas**: `#educationChart`
**Tipo**: `bar`
**Altura**: 340px

---

#### 9. **Riesgo por Rango de Edad** (Bar Chart)
**Canvas**: `#ageChart`
**Tipo**: `bar`
**Altura**: 320px

**Agrupación por rangos de edad**:
```javascript
function getAgeRange(age) {
    if (age < 25) return '18-24 años';
    if (age < 35) return '25-34 años';
    if (age < 45) return '35-44 años';
    if (age < 55) return '45-54 años';
    return '55+ años';
}
```

---

### Función `updateCharts(filteredResults, selectedDominio, selectedDimension)`

**Ubicación**: Líneas 2228-2525

**Responsabilidades**:
1. Recalcular estadísticas con resultados filtrados
2. Actualizar distribución de riesgo según contexto (total/dominio/dimensión)
3. Actualizar TODOS los gráficos con nuevos datos
4. Llamar a `.update()` de cada chart

**Ejemplo de actualización**:
```javascript
// Actualizar gráfico de riesgo
riskChart.data.datasets[0].data = [
    riskDistribution.sin_riesgo,
    riskDistribution.riesgo_bajo,
    riskDistribution.riesgo_medio,
    riskDistribution.riesgo_alto,
    riskDistribution.riesgo_muy_alto
];
riskChart.update();

// Actualizar gráfico de dominios
domainsChart.data.datasets[0].data = domainData.map(d => riskLevelValues[d.nivel]);
domainsChart.data.datasets[0].puntajes = domainData.map(d => d.puntaje);
domainsChart.update();

// ... y así para todos los gráficos
```

---

## 7. Tabla de Resultados (DataTable)

### Inicialización

**Ubicación**: Líneas 1156-1163

```javascript
let dataTable = $('#resultsTable').DataTable({
    language: {
        url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
    },
    pageLength: 25,
    order: [[0, 'asc']]  // Ordenar por nombre (columna 0)
});
```

### Estructura HTML de la Tabla

**Ubicación**: Líneas 947-1020

```html
<table class="table table-sm table-hover" id="resultsTable">
    <thead class="table-light">
        <tr>
            <th>Nombre</th>
            <th>Documento</th>
            <th>Género</th>
            <th>Tipo Form</th>
            <th>Cargo</th>
            <th>Departamento</th>
            <th>Nivel Intralaboral</th>
            <th>Nivel Extralaboral</th>
            <th>Nivel Estrés</th>
            <th>Nivel Total</th>
            <th class="text-center">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($results as $result): ?>
            <tr data-result='<?= htmlspecialchars(json_encode($result), ENT_QUOTES, 'UTF-8') ?>'>
                <td><?= esc($result['worker_name']) ?></td>
                <td><?= esc($result['worker_document']) ?></td>
                <td><?= esc($result['gender']) ?></td>
                <td><span class="badge bg-secondary"><?= esc($result['intralaboral_form_type']) ?></span></td>
                <td><?= esc($result['position']) ?></td>
                <td><?= esc($result['department']) ?></td>
                <td>
                    <span class="risk-badge risk-<?= esc($result['intralaboral_total_nivel']) ?>">
                        <?= ucfirst(str_replace('_', ' ', $result['intralaboral_total_nivel'])) ?>
                    </span>
                </td>
                <td>
                    <span class="risk-badge risk-<?= esc($result['extralaboral_total_nivel']) ?>">
                        <?= ucfirst(str_replace('_', ' ', $result['extralaboral_total_nivel'])) ?>
                    </span>
                </td>
                <td>
                    <?php if (!empty($result['estres_total_nivel'])): ?>
                        <span class="badge" style="background-color: <?= getNivelEstresColor($result['estres_total_nivel']) ?>; color: white;">
                            <?= getNivelEstresTexto($result['estres_total_nivel']) ?>
                        </span>
                    <?php else: ?>
                        <span class="badge bg-secondary">N/A</span>
                    <?php endif; ?>
                </td>
                <td>
                    <span class="risk-badge risk-<?= esc($result['puntaje_total_general_nivel']) ?>">
                        <?= ucfirst(str_replace('_', ' ', $result['puntaje_total_general_nivel'])) ?>
                    </span>
                </td>
                <td class="text-center">
                    <a href="<?= base_url('workers/results/' . $result['worker_id']) ?>"
                       class="btn btn-sm btn-primary"
                       title="Ver resultados individuales"
                       target="_blank">
                        <i class="fas fa-eye"></i>
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
```

### Actualización Dinámica con Filtros

**Ubicación dentro de `applyFilters()`**: Líneas 2580-2640

```javascript
// Limpiar tabla
dataTable.clear();

// Agregar filas filtradas
filteredResults.forEach(r => {
    // Generar badge de estrés
    let estresBadge = '<span class="badge bg-secondary">N/A</span>';
    if (r.estres_total_nivel) {
        const colorEstres = getEstresColor(r.estres_total_nivel);
        const textoEstres = getEstresTexto(r.estres_total_nivel);
        estresBadge = `<span class="badge" style="background-color: ${colorEstres}; color: white;">${textoEstres}</span>`;
    }

    dataTable.row.add([
        r.worker_name || '',
        r.worker_document || '',
        r.gender || '',
        `<span class="badge bg-secondary">${r.intralaboral_form_type}</span>`,
        r.position || '',
        r.department || '',
        `<span class="risk-badge risk-${r.intralaboral_total_nivel}">${formatRiskLabel(r.intralaboral_total_nivel)}</span>`,
        `<span class="risk-badge risk-${r.extralaboral_total_nivel}">${formatRiskLabel(r.extralaboral_total_nivel)}</span>`,
        estresBadge,
        `<span class="risk-badge risk-${r.puntaje_total_general_nivel}">${formatRiskLabel(r.puntaje_total_general_nivel)}</span>`,
        `<a href="${baseURL}workers/results/${r.worker_id}" class="btn btn-sm btn-primary" title="Ver resultados individuales" target="_blank"><i class="fas fa-eye"></i></a>`
    ]);
});

// Redibujar tabla
dataTable.draw();
```

---

## 8. Cálculos Estadísticos

### Función JavaScript: `calculateStats(results)`

**Ubicación**: Líneas 2380-2520 (aprox)

**Responsabilidades**:
- Recalcular todas las estadísticas con resultados filtrados
- Retornar estructura idéntica a `statsData` del servidor

```javascript
function calculateStats(results) {
    const stats = {
        riskDistribution: {
            sin_riesgo: 0,
            riesgo_bajo: 0,
            riesgo_medio: 0,
            riesgo_alto: 0,
            riesgo_muy_alto: 0
        },
        domainAverages: {
            liderazgo: 0,
            control: 0,
            demandas: 0,
            recompensas: 0
        },
        dimensionAverages: {},
        genderDistribution: {},
        formTypeDistribution: { A: 0, B: 0 }
    };

    const count = results.length;
    if (count === 0) return stats;

    // Acumular totales
    let totalLiderazgo = 0;
    let totalControl = 0;
    let totalDemandas = 0;
    let totalRecompensas = 0;

    results.forEach(r => {
        // Distribución de riesgo
        if (r.intralaboral_total_nivel) {
            stats.riskDistribution[r.intralaboral_total_nivel]++;
        }

        // Sumar dominios
        totalLiderazgo += parseFloat(r.dom_liderazgo_puntaje || 0);
        totalControl += parseFloat(r.dom_control_puntaje || 0);
        totalDemandas += parseFloat(r.dom_demandas_puntaje || 0);
        totalRecompensas += parseFloat(r.dom_recompensas_puntaje || 0);

        // Distribución por género
        const gender = r.gender || 'No especificado';
        stats.genderDistribution[gender] = (stats.genderDistribution[gender] || 0) + 1;

        // Distribución por tipo de formulario
        if (r.intralaboral_form_type === 'A' || r.intralaboral_form_type === 'B') {
            stats.formTypeDistribution[r.intralaboral_form_type]++;
        }
    });

    // Calcular promedios
    stats.domainAverages.liderazgo = totalLiderazgo / count;
    stats.domainAverages.control = totalControl / count;
    stats.domainAverages.demandas = totalDemandas / count;
    stats.domainAverages.recompensas = totalRecompensas / count;

    return stats;
}
```

---

## 9. Baremos y Clasificación de Riesgo

### Función JavaScript: `getDomainRiskLevel(puntaje, domain)`

**Ubicación**: Líneas 2185-2225

**Propósito**: Clasificar un puntaje según baremos simplificados (promedio entre Forma A y B)

```javascript
function getDomainRiskLevel(puntaje, domain) {
    const baremos = {
        liderazgo: [
            { max: 6.0, nivel: 'sin_riesgo' },
            { max: 18.1, nivel: 'riesgo_bajo' },
            { max: 32.0, nivel: 'riesgo_medio' },
            { max: 46.0, nivel: 'riesgo_alto' },
            { max: 100, nivel: 'riesgo_muy_alto' }
        ],
        control: [
            { max: 17.8, nivel: 'sin_riesgo' },
            { max: 29.1, nivel: 'riesgo_bajo' },
            { max: 35.7, nivel: 'riesgo_medio' },
            { max: 44.6, nivel: 'riesgo_alto' },
            { max: 100, nivel: 'riesgo_muy_alto' }
        ],
        demandas: [
            { max: 29.6, nivel: 'sin_riesgo' },
            { max: 36.1, nivel: 'riesgo_bajo' },
            { max: 41.5, nivel: 'riesgo_medio' },
            { max: 47.4, nivel: 'riesgo_alto' },
            { max: 100, nivel: 'riesgo_muy_alto' }
        ],
        recompensas: [
            { max: 7.5, nivel: 'sin_riesgo' },
            { max: 15.0, nivel: 'riesgo_bajo' },
            { max: 25.0, nivel: 'riesgo_medio' },
            { max: 35.0, nivel: 'riesgo_alto' },
            { max: 100, nivel: 'riesgo_muy_alto' }
        ]
    };

    const domainBaremo = baremos[domain] || baremos.liderazgo;
    for (let range of domainBaremo) {
        if (puntaje <= range.max) {
            return range.nivel;
        }
    }
    return 'riesgo_muy_alto';
}
```

### Colores de Riesgo

**Mapeo estándar usado en todos los gráficos**:

```javascript
const riskColors = {
    'sin_riesgo': '#28a745',        // Verde
    'riesgo_bajo': '#7dce82',       // Verde claro
    'riesgo_medio': '#ffc107',      // Amarillo
    'riesgo_alto': '#fd7e14',       // Naranja
    'riesgo_muy_alto': '#dc3545'   // Rojo
};
```

**Niveles como valores numéricos** (para gráficos de barra):
```javascript
const riskLevelValues = {
    'sin_riesgo': 1,
    'riesgo_bajo': 2,
    'riesgo_medio': 3,
    'riesgo_alto': 4,
    'riesgo_muy_alto': 5
};
```

---

## 10. Plantilla de Replicación

### Para Crear Dashboard Extralaboral

#### Paso 1: Controlador

**Crear método en** `app/Controllers/ReportsController.php`:

```php
public function extralaboral($serviceId)
{
    $service = $this->checkAccess($serviceId);

    // Misma consulta DB
    $db = \Config\Database::connect();
    $builder = $db->table('calculated_results cr');
    $builder->select('cr.*, wd.*, w.name as worker_name, w.document as worker_document');
    $builder->join('worker_demographics wd', 'wd.worker_id = cr.worker_id', 'left');
    $builder->join('workers w', 'w.id = cr.worker_id', 'left');
    $builder->where('cr.battery_service_id', $serviceId);
    $results = $builder->get()->getResultArray();

    // Mismos segmentadores (sin tipos_formulario ya que extralaboral no tiene A/B)
    $segmentadores = [
        'niveles_riesgo' => ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'],
        'generos' => $this->getUniqueValues($results, 'gender'),
        // ... todos los demográficos
    ];

    // Calcular estadísticas EXTRALABORALES
    $stats = $this->calculateExtralaboralStats($results);

    $data = [
        'title' => 'Dashboard Extralaboral - ' . $service['service_name'],
        'service' => $service,
        'results' => $results,
        'segmentadores' => $segmentadores,
        'stats' => $stats,
        'totalWorkers' => count($results)
    ];

    return view('reports/extralaboral/dashboard', $data);
}

private function calculateExtralaboralStats($results)
{
    // Estructura IDÉNTICA a calculateIntralaboralStats pero usando campos extralaborales
    $stats = [
        'riskDistribution' => [ /* ... */ ],
        'dimensionAverages' => [
            // 7 DIMENSIONES EXTRALABORALES (no dominios)
            'dim_tiempo_fuera_trabajo' => 0,
            'dim_relaciones_familiares' => 0,
            'dim_comunicacion_relaciones_interpersonales' => 0,
            'dim_situacion_economica' => 0,
            'dim_caracteristicas_vivienda' => 0,
            'dim_influencia_entorno_extralaboral' => 0,
            'dim_desplazamiento_vivienda_trabajo' => 0
        ],
        // NO hay domainAverages en extralaboral
        'extralaboralTotal' => 0,
        'genderDistribution' => [],
    ];

    // Mismo proceso de acumulación y cálculo
    foreach ($results as $result) {
        // ... acumular puntajes extralaborales
    }

    return $stats;
}
```

#### Paso 2: Vista

**Crear** `app/Views/reports/extralaboral/dashboard.php`:

**Copiar estructura completa de intralaboral/dashboard.php y adaptar**:

1. **Helper functions**: Mantener igual
2. **CSS**: Mantener igual
3. **Navbar**: Cambiar título a "Dashboard Extralaboral"
4. **Tarjetas de estadísticas**: Cambiar a "Total Extralaboral"
5. **Tarjetas de dominios**: ELIMINAR (extralaboral no tiene dominios)
6. **Tarjetas de dimensiones**: Crear 7 tarjetas individuales:
   - Tiempo fuera del trabajo
   - Relaciones familiares
   - Comunicación y relaciones interpersonales
   - Situación económica del grupo familiar
   - Características de la vivienda y de su entorno
   - Influencia del entorno extralaboral sobre el trabajo
   - Desplazamiento vivienda-trabajo-vivienda

7. **Panel de filtros**:
   - ELIMINAR: filtro de Dominio
   - MODIFICAR: filtro de Dimensión (lista fija de 7 dimensiones)
   - ELIMINAR: filtro de Tipo de Formulario (extralaboral no tiene A/B)
   - MANTENER: Todos los filtros demográficos

8. **Gráficos**:
   - Distribución de Riesgo (igual)
   - **ELIMINAR**: Gráfico de Dominios
   - **CREAR**: Gráfico de Dimensiones Extralaborales (bar chart con 7 dimensiones)
   - Distribución por Género (igual)
   - **ELIMINAR**: Comparación Forma A vs B
   - Top 5 Dimensiones Críticas (adaptar a 5 de 7)
   - Top 10 Departamentos (igual)
   - Riesgo por Nivel Educativo (igual)
   - Riesgo por Rango de Edad (igual)

9. **JavaScript**:
   - Adaptar `dimensionsData` a las 7 dimensiones
   - Eliminar lógica de Forma A/B
   - Adaptar `applyFilters()` para NO filtrar por dominio ni form_type
   - Adaptar `updateCharts()` para omitir gráficos inexistentes
   - Usar baremos extralaborales en `getDimensionRiskLevel()`

#### Paso 3: Baremos Extralaborales

**Agregar en** `ReportsController.php`:

```php
private function getExtralaboralRiskLevel($puntaje, $dimension = 'total')
{
    // Baremos oficiales Resolución 2404/2019 - Extralaboral
    $baremos = [
        'total' => [
            ['min' => 0,    'max' => 13.9, 'nivel' => 'sin_riesgo',      'label' => 'Sin Riesgo'],
            ['min' => 14.0, 'max' => 17.7, 'nivel' => 'riesgo_bajo',     'label' => 'Riesgo Bajo'],
            ['min' => 17.8, 'max' => 22.6, 'nivel' => 'riesgo_medio',    'label' => 'Riesgo Medio'],
            ['min' => 22.7, 'max' => 27.3, 'nivel' => 'riesgo_alto',     'label' => 'Riesgo Alto'],
            ['min' => 27.4, 'max' => 100,  'nivel' => 'riesgo_muy_alto', 'label' => 'Riesgo Muy Alto']
        ],
        'dim_tiempo_fuera_trabajo' => [
            ['min' => 0,    'max' => 6.3,  'nivel' => 'sin_riesgo',      'label' => 'Sin Riesgo'],
            ['min' => 6.4,  'max' => 25.0, 'nivel' => 'riesgo_bajo',     'label' => 'Riesgo Bajo'],
            ['min' => 25.1, 'max' => 37.5, 'nivel' => 'riesgo_medio',    'label' => 'Riesgo Medio'],
            ['min' => 37.6, 'max' => 50.0, 'nivel' => 'riesgo_alto',     'label' => 'Riesgo Alto'],
            ['min' => 50.1, 'max' => 100,  'nivel' => 'riesgo_muy_alto', 'label' => 'Riesgo Muy Alto']
        ],
        // ... 6 dimensiones más
    ];

    $baremo = $baremos[$dimension] ?? $baremos['total'];
    foreach ($baremo as $range) {
        if ($puntaje >= $range['min'] && $puntaje <= $range['max']) {
            return ['nivel' => $range['nivel'], 'label' => $range['label']];
        }
    }
    return ['nivel' => '', 'label' => ''];
}
```

#### Paso 4: Ruta

**Agregar en** `app/Config/Routes.php`:

```php
$routes->get('extralaboral/(:num)', 'ReportsController::extralaboral/$1');
```

---

### Para Crear Dashboard Estrés

#### Diferencias Clave con Intralaboral/Extralaboral:

1. **NO tiene dimensiones ni dominios** - Solo nivel total
2. **Tiene baremos diferentes** (niveles: muy_bajo, bajo, medio, alto, muy_alto)
3. **Colores diferentes**:
   - Muy Bajo: Verde oscuro (#2d6a4f)
   - Bajo: Verde (#52b788)
   - Medio: Amarillo (#ffc107)
   - Alto: Naranja (#fd7e14)
   - Muy Alto: Rojo (#dc3545)

#### Estructura Simplificada:

```php
public function estres($serviceId)
{
    $service = $this->checkAccess($serviceId);

    // Misma consulta DB
    $results = /* ... */;

    // Segmentadores SIN dominios ni dimensiones
    $segmentadores = [
        'niveles_riesgo' => ['muy_bajo', 'bajo', 'medio', 'alto', 'muy_alto'],
        // ... solo demográficos
    ];

    $stats = $this->calculateEstresStats($results);

    return view('reports/estres/dashboard', $data);
}

private function calculateEstresStats($results)
{
    return [
        'riskDistribution' => [
            'muy_bajo' => 0,
            'bajo' => 0,
            'medio' => 0,
            'alto' => 0,
            'muy_alto' => 0
        ],
        'estresTotal' => 0,
        'estresTotalNivel' => '',
        'genderDistribution' => [],
        // NO hay dimensiones
    ];
}
```

**Gráficos**:
- Distribución de Riesgo de Estrés (doughnut con 5 niveles)
- Distribución por Género (pie)
- Estrés por Departamento (horizontal bar)
- Estrés por Nivel Educativo (bar)
- Estrés por Rango de Edad (bar)
- Estrés por Antigüedad (bar)

---

## Resumen de Archivos Clave

### Backend (PHP)
- `app/Controllers/ReportsController.php` - Lógica de negocio y cálculos
- `app/Models/CalculatedResultModel.php` - Modelo de datos
- `app/Config/Routes.php` - Definición de rutas

### Frontend (Views)
- `app/Views/reports/intralaboral/dashboard.php` - Vista completa (2600+ líneas)
- `app/Views/layout/header.php` - Header común
- `app/Views/layout/footer.php` - Footer común

### Assets
- `public/js/reports/filters.js` - Módulo de filtros (placeholder)
- `public/js/satisfaction-check.js` - Verificación de encuesta de satisfacción

### Dependencias CDN
- Bootstrap 5.3.0
- jQuery 3.7.0
- DataTables 1.13.6
- Chart.js 4.4.0
- ChartDataLabels 2.2.0
- Font Awesome 6.x

---

## Checklist de Replicación

Para replicar el dashboard en Extralaboral o Estrés:

- [ ] Crear método en `ReportsController`
- [ ] Implementar `calculate[Type]Stats()` con estructura adaptada
- [ ] Crear/adaptar método `get[Type]RiskLevel()` con baremos oficiales
- [ ] Copiar estructura de vista desde `intralaboral/dashboard.php`
- [ ] Adaptar sección de tarjetas de estadísticas (dimensiones/dominios)
- [ ] Modificar panel de filtros (eliminar/adaptar filtros específicos)
- [ ] Adaptar mapeo de dimensiones en JavaScript
- [ ] Crear/modificar gráficos según estructura del cuestionario
- [ ] Adaptar función `applyFilters()` para filtros específicos
- [ ] Adaptar función `updateCharts()` para gráficos específicos
- [ ] Implementar función `getDimensionRiskLevel()` con baremos correctos
- [ ] Definir colores de riesgo (pueden variar según tipo)
- [ ] Actualizar tabla de resultados (columnas específicas)
- [ ] Agregar ruta en `Routes.php`
- [ ] Probar con datos reales
- [ ] Verificar responsiveness en móvil
- [ ] Validar exportación a Excel/PDF

---

**Fin del Documento**
**Versión**: 1.0
**Fecha**: Enero 2025
**Autor**: Sistema PsyRisk - Cycloid Talent SAS
