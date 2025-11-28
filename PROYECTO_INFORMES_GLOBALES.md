# 📊 PROYECTO: INFORMES GLOBALES PSYRISK

## 🎯 OBJETIVO PRINCIPAL
Crear un sistema de informes interactivos con segmentadores demográficos y de riesgo para que los clientes y consultores visualicen resultados de baterías de riesgo psicosocial en Colombia, **eliminando la necesidad de Looker Studio** y haciendo todo dentro de la plataforma PsyRisk.

---

## 🏢 CONTEXTO DEL NEGOCIO: CYCLOID TALENT

### ¿Qué hace Cycloid Talent?
Realiza aplicaciones de batería de riesgo psicosocial en Colombia según normativa colombiana.

### Flujo ANTERIOR (con Lime Survey):
1. Consultor aplicaba formularios en Lime Survey
2. Descargaba Excel de Lime Survey
3. Pegaba datos en Excel con fórmulas (calculaba puntajes y generaba gráficas)
4. Copiaba/pegaba gráficas en Word/PowerPoint para crear PDF
5. Generaba 2 informes:
   - **Informe Principal PDF**: Detallado con todas las gráficas por dominios y dimensiones
   - **Informe Ejecutivo PDF**: Resumen con recomendaciones y cronograma de 6 meses
6. Subía Excel a **Looker Studio** para dashboard interactivo del cliente

### Flujo ACTUAL (con PsyRisk):
1. ✅ Formularios propios en PsyRisk
2. ✅ Envío de credenciales por email a trabajadores
3. ✅ Trabajadores completan baterías online
4. ✅ Cálculo automático de resultados (CalculatedResultModel)
5. ⚠️ **FALTA**: Visualización interactiva para cliente/consultor (reemplazar Looker Studio)

---

## 🎯 OBJETIVO ESPECÍFICO DEL PROYECTO

### LO QUE NECESITAMOS CREAR:

#### 1️⃣ **DASHBOARDS INTERACTIVOS CON SEGMENTADORES** (3 vistas)
   - Dashboard Intralaboral
   - Dashboard Extralaboral
   - Dashboard Estrés

**Cada dashboard debe tener:**

##### 🔽 SEGMENTADORES (Filtros dinámicos en HTML/JavaScript):
- **Por Nivel de Riesgo**: Sin Riesgo, Bajo, Medio, Alto, Muy Alto
- **Demográficos**:
  - Género (Masculino, Femenino, Otro)
  - Edad / Año de nacimiento
  - Estado civil
  - Nivel de estudios (education_level)
  - Ciudad de residencia
  - Estrato socioeconómico
  - Tipo de vivienda
- **Ocupacionales**:
  - Departamento/Área
  - Cargo (position)
  - Tipo de cargo (position_type: Operativo, Jefe, etc)
  - Tipo de contrato (contract_type)
  - Antigüedad en la empresa (time_in_company_months)
  - Antigüedad en el cargo (time_in_position_months)
  - Experiencia laboral (work_experience_years)
  - Horas de trabajo al día
- **Por Tipo de Formulario**: A o B
- **Botón**: 🔄 Limpiar todos los filtros

##### 📊 GRÁFICAS INTERACTIVAS (se actualizan con filtros):
- Gráfica de distribución de niveles de riesgo (Pie/Donut)
- Gráfica por dominios (Barras)
- Gráfica por género (Barras)
- Gráfica por tipo de formulario (Pie)
- **Las gráficas se actualizan automáticamente al aplicar filtros**

##### 📋 TABLA DETALLADA (filtrable):
Columnas:
- Nombre del trabajador
- Documento
- Género
- Tipo de formulario (A/B)
- Cargo
- Departamento
- **Nivel de Riesgo Intralaboral** (🟢🟡🟠🔴 con colores)
- **Nivel de Riesgo Extralaboral**
- **Nivel de Riesgo Estrés**
- **Nivel de Riesgo Total**

##### 🔘 BOTONES:
- 📥 **Descargar Excel** (con datos filtrados)
- 📄 **Descargar Informe Completo PDF** (con todas las gráficas)
- ⚡ **Ver Informe Ejecutivo** (redirige a otra vista)

---

#### 2️⃣ **INFORMES EJECUTIVOS** (3 vistas separadas)
   - Informe Ejecutivo Intralaboral
   - Informe Ejecutivo Extralaboral
   - Informe Ejecutivo Estrés

**Cada informe ejecutivo debe mostrar:**

##### 📊 Totales Globales:
- Total de participantes
- Promedio intralaboral
- Promedio extralaboral
- Promedio estrés

##### ⚠️ TABLA: "Requieren Atención"
Mostrar **SOLO** trabajadores con riesgo: **Medio, Alto o Muy Alto**

Columnas:
- Nombre
- Documento
- Nivel de Riesgo (🟡🟠🔴)
- **Botón**: 📅 Ver Recomendaciones (redirige a las vistas de cronograma ya creadas)

##### 🔘 BOTONES:
- ↩️ Volver al Dashboard
- 📥 Descargar Informe Ejecutivo PDF
- 📅 **Ver Recomendaciones Globales** (enlace a vistas de recomendaciones existentes)

---

## 👥 CONTROL DE ACCESO

### 🔐 ¿QUIÉN VE QUÉ?

#### **CLIENTE** (roles: `cliente_empresa`, `cliente_gestor`):
- ✅ Ve dashboards e informes ejecutivos **SOLO de SU empresa**
- ✅ Ve solo los servicios de batería asignados a su empresa
- ❌ NO ve otras empresas

#### **CONSULTOR** (rol: `consultor`):
- ✅ Ve dashboards e informes ejecutivos de **TODAS las empresas**
- ✅ Tiene selector adicional: "Empresa" y "Servicio"
- ✅ Sin restricciones de acceso
- ✅ Puede descargar todo

#### **ADMIN** (rol: `admin`):
- ❌ **NO** ve estos informes
- ❌ No le atañe, no lo entiende, no lo maneja

#### **SUPERADMIN** (rol: `superadmin`):
- ❌ **NO** ve estos informes (mismo que admin)

#### **VENDEDOR/COMERCIAL** (rol: `comercial`):
- ❌ **NO** ve estos informes
- ❌ No le interesa

---

## 📁 ESTRUCTURA DE ARCHIVOS A CREAR

### Controlador:
```
app/Controllers/ReportsController.php
```

Métodos necesarios:
- `intralaboral($serviceId)` - Dashboard intralaboral
- `intralaboralExecutive($serviceId)` - Informe ejecutivo intralaboral
- `extralaboral($serviceId)` - Dashboard extralaboral
- `extralaboralExecutive($serviceId)` - Informe ejecutivo extralaboral
- `estres($serviceId)` - Dashboard estrés
- `estresExecutive($serviceId)` - Informe ejecutivo estrés
- `exportExcel($serviceId, $type)` - Exportar a Excel
- `exportPDF($serviceId, $type)` - Exportar PDF completo
- `exportExecutivePDF($serviceId, $type)` - Exportar PDF ejecutivo

### Vistas:
```
app/Views/reports/
├── intralaboral/
│   ├── dashboard.php (con segmentadores y gráficas)
│   └── executive.php (informe ejecutivo)
├── extralaboral/
│   ├── dashboard.php
│   └── executive.php
├── estres/
│   ├── dashboard.php
│   └── executive.php
└── components/
    ├── segmentadores.php (componente reutilizable)
    └── tabla_resultados.php (componente reutilizable)
```

### JavaScript:
```
public/js/
└── reports/
    ├── filters.js (manejo de segmentadores)
    ├── charts.js (actualización de gráficas)
    └── export.js (exportación a Excel)
```

### Rutas:
```php
// app/Config/Routes.php
$routes->group('reports', function($routes) {
    // Dashboards
    $routes->get('intralaboral/(:num)', 'ReportsController::intralaboral/$1');
    $routes->get('extralaboral/(:num)', 'ReportsController::extralaboral/$1');
    $routes->get('estres/(:num)', 'ReportsController::estres/$1');

    // Informes Ejecutivos
    $routes->get('intralaboral/executive/(:num)', 'ReportsController::intralaboralExecutive/$1');
    $routes->get('extralaboral/executive/(:num)', 'ReportsController::extralaboralExecutive/$1');
    $routes->get('estres/executive/(:num)', 'ReportsController::estresExecutive/$1');

    // Exportaciones
    $routes->get('export-excel/(:num)/(:alpha)', 'ReportsController::exportExcel/$1/$2');
    $routes->get('export-pdf/(:num)/(:alpha)', 'ReportsController::exportPDF/$1/$2');
    $routes->get('export-executive-pdf/(:num)/(:alpha)', 'ReportsController::exportExecutivePDF/$1/$2');
});
```

---

## 🗄️ DATOS DISPONIBLES (CalculatedResultModel)

### Demográficos:
- `gender` - Género
- `birth_year` - Año de nacimiento
- `age` - Edad
- `marital_status` - Estado civil
- `education_level` - Nivel de estudios
- `city_residence` - Ciudad de residencia
- `stratum` - Estrato socioeconómico
- `housing_type` - Tipo de vivienda

### Ocupacionales:
- `department` - Departamento/Área
- `position` - Cargo
- `position_type` - Tipo de cargo
- `contract_type` - Tipo de contrato
- `work_experience_years` - Experiencia laboral
- `time_in_company_months` - Antigüedad en empresa
- `time_in_position_months` - Antigüedad en cargo
- `hours_per_day` - Horas de trabajo al día

### Resultados:
- `intralaboral_form_type` - Tipo de formulario (A/B)
- `intralaboral_total_puntaje` - Puntaje total intralaboral
- `intralaboral_total_nivel` - Nivel de riesgo intralaboral
- `extralaboral_total_puntaje` - Puntaje total extralaboral
- `extralaboral_total_nivel` - Nivel de riesgo extralaboral
- `estres_total_puntaje` - Puntaje total estrés
- `estres_total_nivel` - Nivel de riesgo estrés
- `puntaje_total_general` - Puntaje total general
- `puntaje_total_general_nivel` - Nivel de riesgo general

### Dominios y Dimensiones (todos con `_puntaje` y `_nivel`):
- Dominios: liderazgo, control, demandas, recompensas
- Dimensiones: 19 dimensiones específicas

---

## 🎨 TECNOLOGÍAS A USAR

### Frontend:
- **Bootstrap 5** (ya en uso)
- **Chart.js** (para gráficas interactivas)
- **JavaScript Vanilla** (para filtros dinámicos)
- **DataTables** o similar (para tabla filtrable)
- **SheetJS (xlsx)** (para exportar Excel desde el navegador)

### Backend:
- **CodeIgniter 4** (ya en uso)
- **TCPDF o Dompdf** (para generar PDFs)
- **PhpSpreadsheet** (alternativa para Excel desde servidor)

---

## ✅ FASES DEL PROYECTO

### FASE 1: Setup Inicial ✅
- [x] Crear documento de contexto
- [ x] Eliminar vistas antiguas no necesarias
- [ ] Crear estructura de directorios

### FASE 2: Dashboard Intralaboral 🚧
- [ ] Crear controlador con método intralaboral()
- [ ] Crear vista dashboard intralaboral
- [ ] Implementar segmentadores HTML
- [ ] Implementar JavaScript para filtros
- [ ] Integrar Chart.js para gráficas
- [ ] Implementar tabla filtrable
- [ ] Probar funcionalidad completa

### FASE 3: Informe Ejecutivo Intralaboral
- [ ] Crear método intralaboralExecutive()
- [ ] Crear vista ejecutiva
- [ ] Mostrar solo riesgo medio/alto/muy alto
- [ ] Botones a recomendaciones
- [ ] Probar funcionalidad

### FASE 4: Dashboard y Ejecutivo Extralaboral
- [ ] Replicar dashboard para extralaboral
- [ ] Replicar ejecutivo para extralaboral

### FASE 5: Dashboard y Ejecutivo Estrés
- [ ] Replicar dashboard para estrés
- [ ] Replicar ejecutivo para estrés

### FASE 6: Exportaciones
- [ ] Implementar exportación a Excel
- [ ] Implementar exportación PDF completo
- [ ] Implementar exportación PDF ejecutivo

### FASE 7: Control de Acceso
- [ ] Implementar permisos para clientes (solo su empresa)
- [ ] Implementar permisos para consultores (todas las empresas)
- [ ] Verificar restricciones admin/vendedor

### FASE 8: Testing Final
- [ ] Pruebas con datos reales
- [ ] Ajustes de diseño
- [ ] Optimización de rendimiento

---

## 🔗 INTEGRACIONES CON VISTAS EXISTENTES

### Recomendaciones (ya creadas):
- `app/Views/recommendations/` - Vistas de cronogramas
- URL: `/recommendations/dimension/{dimension}`
- Los informes ejecutivos deben enlazar a estas vistas

### Trabajadores:
- `app/Views/workers/results.php` - Resultados individuales
- Posible enlace desde nombre del trabajador en tabla

---

## 📝 NOTAS IMPORTANTES

1. **NO usar Looker Studio** - Todo debe ser interno en PsyRisk
2. **Filtros en tiempo real** - Sin recargar página (JavaScript)
3. **Responsive** - Debe verse bien en móvil/tablet
4. **Colores de riesgo estandarizados**:
   - 🟢 Sin Riesgo: #28a745
   - 🟡 Riesgo Bajo: #7dce82
   - 🟠 Riesgo Medio: #ffc107
   - 🔴 Riesgo Alto: #fd7e14
   - ⚫ Riesgo Muy Alto: #dc3545
5. **Descargas deben respetar filtros aplicados**
6. **Admin/Vendedor NO tienen acceso** a estos informes

---

## 🚀 PRÓXIMOS PASOS

1. ✅ Crear este documento
2. ⏭️ Eliminar vistas antiguas (index, by_service, by_company, comparative)
3. ⏭️ Crear estructura de directorios nueva
4. ⏭️ Empezar con dashboard intralaboral

---

**Última actualización**: 2025-11-17
**Desarrollado por**: Claude Code para Cycloid Talent / PsyRisk
