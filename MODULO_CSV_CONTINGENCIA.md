# 🛡️ MÓDULO DE CONTINGENCIA - IMPORTACIÓN CSV

## 📋 RESUMEN

Este módulo permite al consultor importar respuestas de cuestionarios desde archivos CSV cuando el sistema principal no está disponible (caídas de Cloudflare, problemas de servidor, etc.).

**Caso de uso**: Si el sistema de evaluación en línea falla, el consultor puede:
1. Migrar temporalmente a LimeSurvey u otro sistema externo
2. Exportar los datos a Excel/CSV
3. Importarlos a PsyRisk usando este módulo
4. Continuar con el procesamiento normal

---

## 🎯 CARACTERÍSTICAS

### ✅ Funcionalidades Implementadas

1. **Carga de archivos CSV**
   - Validación de formato
   - Procesamiento línea por línea
   - Drag & drop support
   - Hasta 10MB

2. **Mapeo automático**
   - Documento → Trabajador
   - Cuestionario → Tipo de formulario
   - Pregunta → Número de pregunta
   - Respuesta → Valor numérico

3. **Validaciones**
   - Trabajador debe existir en el servicio
   - Tipo de cuestionario válido
   - Campos obligatorios presentes
   - Formato de datos correcto

4. **Registro de auditoría**
   - Tabla `csv_imports` con historial completo
   - Total de filas procesadas
   - Filas importadas exitosamente
   - Filas con errores
   - Log detallado de errores

5. **Actualización de estados**
   - Trabajadores pasan de `pendiente`/`invitado` a `en_proceso` automáticamente

---

## 📁 FORMATO DEL CSV

### Columnas Requeridas

| Columna | Descripción | Ejemplo |
|---------|-------------|---------|
| `documento` | Número de documento del trabajador | 1234567890 |
| `cuestionario` | Tipo de cuestionario | intralaboral_a |
| `pregunta` | Número de pregunta | 1 |
| `respuesta` | Valor numérico de respuesta | 4 |

### Tipos de Cuestionario Válidos

- `intralaboral` o `intralaboral_a` → Intralaboral Forma A
- `intralaboral_b` → Intralaboral Forma B
- `extralaboral` → Extralaboral
- `estres` → Estrés
- `ficha_datos` → Ficha de Datos Generales

### Ejemplo de CSV

```csv
documento,cuestionario,pregunta,respuesta
1234567890,intralaboral_a,1,4
1234567890,intralaboral_a,2,3
1234567890,intralaboral_a,3,2
1234567890,extralaboral,1,3
1234567890,extralaboral,2,4
1234567890,estres,1,2
9876543210,intralaboral_b,1,1
9876543210,intralaboral_b,2,2
```

---

## 🚀 FLUJO DE USO

### Paso 1: Preparar datos

1. Exportar respuestas desde LimeSurvey (o sistema externo) a Excel
2. Transformar el Excel al formato esperado (4 columnas)
3. Guardar como CSV

### Paso 2: Acceder al módulo

1. Iniciar sesión como consultor
2. Ir al menú lateral → **"Importar CSV"**

### Paso 3: Cargar CSV

1. Seleccionar el servicio de batería
2. Arrastrar el archivo CSV o hacer clic para seleccionarlo
3. Click en **"Importar CSV"**

### Paso 4: Verificar resultados

El sistema mostrará:
- ✅ Total de filas procesadas
- ✅ Filas importadas exitosamente
- ⚠️ Filas con errores (si las hay)

---

## 📊 TABLA DE BASE DE DATOS

### Tabla `csv_imports`

```sql
CREATE TABLE `csv_imports` (
    `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `battery_service_id` INT(11) UNSIGNED NOT NULL,
    `imported_by` INT(11) UNSIGNED NOT NULL,
    `file_name` VARCHAR(255) NOT NULL,
    `total_rows` INT(11) DEFAULT 0,
    `imported_rows` INT(11) DEFAULT 0,
    `failed_rows` INT(11) DEFAULT 0,
    `error_log` TEXT NULL,
    `status` ENUM('procesando', 'completado', 'error') DEFAULT 'procesando',
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    FOREIGN KEY (`battery_service_id`) REFERENCES `battery_services`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`imported_by`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

---

## 🛠️ ARCHIVOS CREADOS/MODIFICADOS

### ✅ Archivos Nuevos (5)

1. `app/Database/Migrations/2025-11-19-034048_CreateCsvImportsTable.php`
2. `app/Models/CsvImportModel.php`
3. `app/Controllers/CsvImportController.php`
4. `app/Views/csv_import/index.php`
5. `MODULO_CSV_CONTINGENCIA.md` (este archivo)

### ✅ Archivos Modificados (2)

1. `app/Config/Routes.php` - Rutas del módulo CSV
2. `app/Views/dashboard/consultor.php` - Enlace en menú

---

## 🔒 SEGURIDAD

### Control de Acceso

- ✅ Solo usuarios con rol `consultor` pueden acceder
- ✅ Solo pueden importar a sus propios servicios
- ✅ Validación de autenticación en todas las rutas
- ✅ Verificación de permisos en cada operación

### Validación de Datos

- ✅ Validación de extensión de archivo (.csv, .txt)
- ✅ Límite de tamaño (10MB)
- ✅ Validación de campos obligatorios
- ✅ Verificación de existencia de trabajadores
- ✅ Validación de tipos de cuestionario

---

## ⚙️ FUNCIONES DEL CONTROLADOR

### Métodos Principales

**`index()`**
- Vista principal del módulo
- Lista servicios en curso del consultor
- Muestra historial de importaciones

**`upload()`**
- Procesa el archivo CSV cargado
- Valida formato y contenido
- Registra importación en BD
- Llama a `processCSV()`

**`processCSV($file, $serviceId, $importId)`**
- Lee archivo línea por línea
- Normaliza headers
- Procesa cada fila
- Retorna estadísticas

**`importRow($data, $serviceId)`**
- Valida campos requeridos
- Busca trabajador por documento
- Mapea tipo de cuestionario
- Crea/actualiza respuesta
- Actualiza estado del trabajador

**`downloadTemplate()`**
- Descarga plantilla CSV de ejemplo
- Incluye datos de muestra

---

## 📝 EJEMPLO DE USO COMPLETO

### Escenario: Caída de Cloudflare

**1. Situación**
```
🔴 Cloudflare está caído
🔴 Los trabajadores no pueden acceder a las evaluaciones en línea
⏰ Deadline del servicio: Mañana
```

**2. Acción Rápida**
```
1. ✅ Activar cuenta de LimeSurvey
2. ✅ Crear cuestionarios en LimeSurvey
3. ✅ Enviar links de LimeSurvey a los trabajadores
4. ✅ Los trabajadores completan en LimeSurvey
```

**3. Recuperación de Datos**
```
1. ✅ Exportar respuestas de LimeSurvey a Excel
2. ✅ Transformar Excel al formato de 4 columnas
3. ✅ Guardar como CSV
```

**4. Importación a PsyRisk**
```
1. ✅ Acceder a http://localhost/psyrisk/csv-import
2. ✅ Seleccionar el servicio
3. ✅ Cargar el CSV
4. ✅ Verificar: "45 registros importados, 0 fallidos"
```

**5. Continuar Normal**
```
1. ✅ Los trabajadores ahora tienen estado "en_proceso"
2. ✅ Las respuestas están en la tabla `responses`
3. ✅ El sistema puede calcular resultados normalmente
4. ✅ Se pueden generar informes
```

---

## 🎯 MEJORAS FUTURAS (Opcionales)

### Versión 2.0

- [ ] Soporte para formato Excel (.xlsx) directo
- [ ] Validación avanzada de rangos de respuesta
- [ ] Importación de datos demográficos
- [ ] Preview de datos antes de importar
- [ ] Rollback de importaciones
- [ ] Importación por lotes asíncrona (para archivos grandes)
- [ ] Dashboard de métricas de importación

---

## ✅ VENTAJAS DEL MÓDULO

1. **Continuidad del Servicio** - No depende de un solo proveedor
2. **Flexibilidad** - Acepta datos de cualquier fuente
3. **Rapidez** - Importación en segundos
4. **Auditoría** - Registro completo de todas las operaciones
5. **Validación** - Detecta errores antes de guardar
6. **Transparencia** - Muestra exactamente qué falló y por qué

---

## 🔧 COMANDOS ÚTILES

### Ejecutar migración
```bash
php spark migrate
```

### Ver historial de importaciones
```sql
SELECT * FROM csv_imports
ORDER BY created_at DESC
LIMIT 10;
```

### Ver respuestas importadas
```sql
SELECT r.*, w.document_number, w.name
FROM responses r
JOIN workers w ON w.id = r.worker_id
WHERE r.created_at > '2025-11-19 00:00:00'
ORDER BY r.created_at DESC;
```

---

## 📞 SOPORTE

Si encuentras errores durante la importación:

1. Revisa el log de errores en el historial
2. Verifica que el formato del CSV sea correcto
3. Asegúrate de que los trabajadores existan en el servicio
4. Verifica que los números de documento coincidan exactamente

---

**Módulo creado por**: Claude Code
**Fecha**: 2025-11-19
**Versión**: 1.0
**Estado**: ✅ Producción
