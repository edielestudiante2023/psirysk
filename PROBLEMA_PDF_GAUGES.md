# PROBLEMA: Gauges SVG no se renderizan en PDF con wkhtmltopdf

## Descripción del Problema
Los gauges (velocímetros SVG) se ven correctamente en el **preview del navegador** pero **desaparecen en el PDF generado** con wkhtmltopdf.

## Archivos Clave Involucrados

### Componentes de Gauges
- `app/Views/pdf/_partials/components/gauge.php` - Gauge individual SVG
- `app/Views/pdf/_partials/components/gauge_dual.php` - Dos gauges lado a lado

### Páginas que usan Gauges
- `app/Views/pdf/intralaboral/dimension_page.php`
- `app/Views/pdf/extralaboral/dimension_page.php`
- `app/Views/pdf/estres/estres_page.php`

### Estilos CSS
- `app/Views/pdf/_partials/css/pdf-styles.css`

### Orquestador PDF
- `app/Controllers/Pdf/Services/PdfReportOrchestrator.php`

## Causa Raíz Probable
wkhtmltopdf tiene **soporte limitado para**:
1. **display: flex** - No funciona correctamente
2. **SVG complejos** - Puede tener problemas con algunos elementos SVG
3. **JavaScript** - El motor WebKit es antiguo

## Intentos Anteriores (Fallidos)
1. Cambiar `display: flex` por `display: table` → **Rompió los gauges en el navegador**
2. Agregar opciones a wkhtmltopdf (`--disable-smart-shrinking`, `--javascript-delay`) → **No resolvió**

## Alternativas a Explorar

### 1. Dompdf (PHP)
```bash
composer require dompdf/dompdf
```
- Convierte HTML + CSS a PDF
- No requiere binarios externos
- Buen soporte para estilos básicos

### 2. mPDF (PHP)
```bash
composer require mpdf/mpdf
```
- Buen soporte para tablas y UTF-8
- Maneja bien encabezados/pies de página

### 3. TCPDF (PHP)
```bash
composer require tecnickcom/tcpdf
```
- Más control manual
- Muy robusto pero más código

### 4. Convertir SVG a imágenes PNG/Base64
- Renderizar SVG a imagen antes de generar PDF
- Usar librería como `Intervention/Image` o canvas server-side

### 5. Usar Chart.js con imágenes estáticas
- Generar gráficos como imágenes en servidor
- Incrustar las imágenes en el HTML del PDF

## URLs de Prueba
- Preview navegador: http://localhost/psyrisk/pdf/preview/completo/1
- Download PDF: http://localhost/psyrisk/pdf/download/completo/1
- Preview gauge individual: http://localhost/psyrisk/pdf/preview/intralaboral-dimensiones/1

## Estado Actual
- Los gauges funcionan en preview web ✅
- Los gauges NO aparecen en PDF descargado ❌
- El resto del informe PDF funciona correctamente ✅
