# Dashboard de Satisfacción para Vendedores y Admin

## 🎯 Resumen

Se ha implementado un **dashboard completo de análisis de satisfacción** accesible para:
- **Admin / Superadmin**: Monitoreo de calidad del servicio
- **Comercial (Vendedores)**: Seguimiento comercial y facturación

---

## 📍 Acceso

**URL**: `/satisfaction/dashboard`

**Roles permitidos**:
- `admin`
- `superadmin`
- `comercial`

---

## 📊 Características del Dashboard

### 1. Tarjetas de Estadísticas Generales (KPIs)

#### 📋 Total Encuestas
- Contador de encuestas completadas
- Icono: Clipboard con check
- Color: Azul primario

#### ⭐ Satisfacción General
- Promedio global de todas las encuestas
- Escala: X.XX / 5.0
- Color: Verde éxito

#### 😊 Profesionalismo Consultor
- Promedio de pregunta 2 (calidad del consultor)
- Métrica clave para recursos humanos
- Color: Amarillo advertencia

#### 👍 Recomendarían el Servicio
- Promedio de pregunta 4 (NPS aproximado)
- Métrica comercial importante
- Color: Azul info

---

### 2. Gráficos Interactivos (Chart.js)

#### Gráfico de Barras: Promedio por Pregunta
- Muestra las 5 preguntas
- Escala vertical 0-5
- Colores diferenciados por pregunta
- Tooltip con detalle

#### Gráfico de Dona: Distribución de Satisfacción
5 segmentos con rangos:
- **Muy Alto** (4.5-5.0): Verde
- **Alto** (4.0-4.4): Azul
- **Medio** (3.0-3.9): Amarillo
- **Bajo** (2.0-2.9): Naranja
- **Muy Bajo** (1.0-1.9): Rojo

Tooltip muestra:
- Cantidad de encuestas
- Porcentaje del total

---

### 3. Ranking de Empresas por Satisfacción

#### Características:
- Ordenado de mayor a menor satisfacción
- **Medallas** para top 3:
  - 🥇 Oro (1er lugar)
  - 🥈 Plata (2do lugar)
  - 🥉 Bronce (3er lugar)

#### Columnas:
- **#**: Posición o medalla
- **Empresa**: Nombre de la empresa
- **Encuestas**: Badge con cantidad
- **Promedio**: Badge con color según nivel
- **Rating**: Barra de progreso visual

#### Colores de badges:
- Verde: ≥ 4.5
- Azul: ≥ 4.0
- Amarillo: ≥ 3.0
- Rojo: < 3.0

---

### 4. Tabla de Encuestas Recientes

#### Funcionalidades:
- **DataTables** con búsqueda y paginación
- Ordenado por fecha descendente
- 10 registros por página
- Idioma en español

#### Columnas:
- **Fecha**: dd/mm/yyyy HH:mm
- **Empresa**: Nombre
- **Servicio**: Nombre del servicio
- **Respondió**: Usuario que completó encuesta
- **Promedio**: Badge con color
- **Acciones**: Botón "Ver Detalle"

---

## 🔍 Vista de Detalle Individual

**URL**: `/satisfaction/view/{serviceId}`

**Roles permitidos**: Admin, Superadmin, Consultor, Comercial

### Secciones:

#### 1. Información del Servicio
Card con:
- Nombre del servicio
- Fecha del servicio
- Fecha de completado de encuesta
- Promedio general destacado con badge

#### 2. Gráfico de Radar (Chart.js)
- 5 ejes (una por pregunta)
- Escala 0-5
- Visualización clara del perfil de satisfacción
- Útil para identificar fortalezas/debilidades

#### 3. Respuestas Detalladas
5 tarjetas (una por pregunta) con:
- Pregunta completa
- Badge con puntuación
- Barra de progreso con color único
- Texto descriptivo del nivel seleccionado

Ejemplo:
```
Pregunta 1: ¿Qué tan satisfecho está...?
[4 / 5] ████████░░ "Satisfecho"
```

#### 4. Comentarios del Cliente
Si el cliente dejó comentarios:
- Card destacada
- Texto en formato preservando saltos de línea
- Borde izquierdo azul
- Fondo gris claro

---

## 💼 Utilidad para Vendedores

### 1. Seguimiento Comercial
- **Identificar clientes satisfechos** para renovaciones
- **Detectar clientes insatisfechos** para intervención
- **Ranking de empresas** para priorizar seguimiento

### 2. Argumentos de Venta
- **Datos duros** de satisfacción general
- **Testimonios** (comentarios positivos)
- **Mejora continua** (métricas en tiempo real)

### 3. Facturación
- Confirmar que servicio fue bien recibido antes de facturar
- Evidencia de calidad del servicio entregado
- Información para justificar costos

### 4. Detección de Problemas
- Promedio < 3.0: Atención urgente
- Comentarios negativos: Revisión con consultor
- Empresa sin encuesta: Seguimiento para completarla

---

## 📈 Estadísticas Disponibles

### Método del Controlador: `SatisfactionController::dashboard()`

Calcula y retorna:

```php
[
    'surveys' => [...],              // Todas las encuestas con joins
    'totalSurveys' => 45,            // Total de encuestas
    'avgGeneral' => 4.35,            // Promedio general
    'avgQ1' => 4.2,                  // Promedio pregunta 1
    'avgQ2' => 4.5,                  // Promedio pregunta 2
    'avgQ3' => 4.3,                  // Promedio pregunta 3
    'avgQ4' => 4.1,                  // Promedio pregunta 4
    'avgQ5' => 4.4,                  // Promedio pregunta 5
    'companyStats' => [              // Estadísticas por empresa
        [
            'company_name' => 'Empresa ABC',
            'company_id' => 5,
            'total_surveys' => 8,
            'average_score' => 4.6
        ],
        ...
    ],
    'distribution' => [              // Distribución de satisfacción
        'muy_bajo' => 2,
        'bajo' => 5,
        'medio' => 12,
        'alto' => 15,
        'muy_alto' => 11
    ]
]
```

---

## 🎨 Diseño Visual

### Paleta de Colores

#### Por Nivel de Satisfacción:
- 🟢 Verde (`bg-success`): ≥ 4.5
- 🔵 Azul (`bg-info`): ≥ 4.0 y < 4.5
- 🟡 Amarillo (`bg-warning`): ≥ 3.0 y < 4.0
- 🔴 Rojo (`bg-danger`): < 3.0

#### KPIs:
- Azul primario: Total encuestas
- Verde: Satisfacción general
- Amarillo: Profesionalismo
- Azul info: Recomendarían

### Iconografía (Bootstrap Icons):
- `bi-graph-up-arrow`: Título dashboard
- `bi-clipboard-check`: Total encuestas
- `bi-star-fill`: Satisfacción
- `bi-emoji-smile`: Consultor
- `bi-hand-thumbs-up`: Recomendarían
- `bi-trophy-fill`: Ranking
- `bi-clock-history`: Encuestas recientes
- `bi-eye`: Ver detalle
- `bi-radar`: Gráfico radar

---

## 🔄 Flujo de Navegación

```
/satisfaction/dashboard
    │
    ├── Ver estadísticas generales
    ├── Ver ranking de empresas
    ├── Ver gráficos
    │
    └── Clic en "Ver Detalle" de una encuesta
        │
        └── /satisfaction/view/{serviceId}
            ├── Ver gráfico radar
            ├── Ver respuestas detalladas
            ├── Ver comentarios
            └── Volver al dashboard
```

---

## 📱 Responsive Design

- ✅ Móviles: Columnas apiladas, gráficos ajustados
- ✅ Tablets: 2 columnas en KPIs
- ✅ Desktop: 4 columnas en KPIs
- ✅ Tablas: Scroll horizontal en móviles

---

## 🚀 Próximos Pasos (Opcionales)

### Para Vendedores:
1. **Exportar dashboard a PDF** para presentaciones
2. **Filtros por fecha** (último mes, trimestre, año)
3. **Filtro por vendedor** (ver solo mis clientes)
4. **Alertas automáticas** cuando satisfacción < 3.0

### Para Admin:
1. **Comparativa temporal** (satisfacción mes a mes)
2. **Análisis por consultor** (rendimiento individual)
3. **Correlación** satisfacción vs tiempo de cierre
4. **Dashboard ejecutivo** con métricas clave

---

## ✅ Archivos Implementados

### Nuevas vistas:
1. `app/Views/satisfaction/dashboard.php` - Dashboard completo
2. `app/Views/satisfaction/view.php` - Detalle individual con radar

### Controlador modificado:
1. `app/Controllers/SatisfactionController.php`:
   - Método `dashboard()` agregado
   - Método `view()` modificado para incluir `comercial`

### Rutas agregadas:
1. `/satisfaction/dashboard` - Dashboard principal
2. `/satisfaction/view/{serviceId}` - Detalle individual

---

## 📝 Notas Importantes

1. **Consultor también tiene acceso** a ver detalles individuales de sus servicios
2. **Clientes NO tienen acceso** al dashboard ni a ver resultados
3. **DataTables** requiere jQuery (ya incluido en layout)
4. **Chart.js** se carga desde CDN
5. **Idioma español** configurado en DataTables

---

✅ **Sistema completamente funcional y listo para usar después de ejecutar la migración.**

## 🎯 Valor de Negocio

### Para Vendedores:
- 📊 Datos para argumentar renovaciones
- 🚨 Alertas tempranas de insatisfacción
- 🏆 Identificar clientes estrella
- 💰 Justificar facturación

### Para Admin:
- 📈 Monitoreo de calidad
- 👥 Evaluación de consultores
- 🎯 Decisiones basadas en datos
- 🔍 Identificar áreas de mejora
