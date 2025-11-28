# 📊 PLAN DE IMPLEMENTACIÓN: PDFs con Gráficos Chart.js

## 🎯 Objetivo

Implementar generación de PDFs profesionales que incluyan todos los gráficos de los dashboards de reportes (Intralaboral, Extralaboral, Estrés, Heatmap) con calidad idéntica a la visualización en navegador.

---

## 📋 Estado Actual del Sistema

### Tecnologías Ya Instaladas
✅ **Dompdf** - Generador de PDFs desde HTML
✅ **Chart.js** - Librería de gráficos JavaScript
✅ **Bootstrap 5** - Framework CSS

### Gráficos Existentes por Dashboard

#### 1. Intralaboral (`/reports/intralaboral/{serviceId}`)
- `riskChart` - Distribución de riesgo (Doughnut)
- `domainsChart` - Análisis por dominios (Bar)
- `genderChart` - Distribución por género (Pie)
- `dimensionsGroupedChart` - Dimensiones agrupadas (Bar)
- `formsComparisonChart` - Comparación Forma A vs B (Bar)
- `topDimensionsChart` - Top dimensiones críticas (Bar)
- `departmentChart` - Análisis por departamento (Bar)
- `educationChart` - Riesgo por nivel educativo (Bar)
- `ageChart` - Riesgo por edad (Bar)

#### 2. Extralaboral (`/reports/extralaboral/{serviceId}`)
- Similar estructura a Intralaboral con dimensiones específicas

#### 3. Estrés (`/reports/estres/{serviceId}`)
- Gráficos de síntomas fisiológicos, comportamentales e intelectuales

#### 4. Heatmap (`/reports/heatmap/{serviceId}`)
- Mapa de calor visual de riesgos

**TOTAL ESTIMADO:** ~25-30 gráficos diferentes en todo el sistema

---

## 🏗️ Arquitectura Propuesta

### Enfoque: Chart.js → Canvas → Base64 → PDF (Modular + FPDI)

```
┌─────────────────────────────────────────────────────────────┐
│ FASE 1: CAPTURA EN FRONTEND (JavaScript)                    │
├─────────────────────────────────────────────────────────────┤
│ Usuario abre dashboard → Chart.js renderiza gráficos       │
│ Usuario click "Generar PDF"                                 │
│ JavaScript captura todos los <canvas> como Base64          │
│ AJAX envía JSON con imágenes al backend                    │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ FASE 2: GENERACIÓN MODULAR EN BACKEND (PHP)                │
├─────────────────────────────────────────────────────────────┤
│ ReportsController recibe imágenes + serviceId              │
│ PDFReportGenerator genera cada sección como PDF separado:  │
│   - Portada.pdf                                             │
│   - Contenido.pdf                                           │
│   - Introduccion.pdf                                        │
│   - Demografico.pdf (con gráficos)                          │
│   - Intralaboral.pdf (con gráficos)                         │
│   - Extralaboral.pdf (con gráficos)                         │
│   - Estres.pdf (con gráficos)                               │
│   - Recomendaciones.pdf                                     │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ FASE 3: UNIFICACIÓN CON FPDI                               │
├─────────────────────────────────────────────────────────────┤
│ FPDI une todos los PDFs en uno solo                        │
│ Limpia archivos temporales                                 │
│ Descarga PDF completo al usuario                           │
└─────────────────────────────────────────────────────────────┘
```

---

## 📦 Requisitos Previos

### 1. Instalar FPDI

```bash
composer require setasign/fpdi
```

### 2. Verificar Permisos

```bash
# Asegurar que writable/temp/ existe y tiene permisos de escritura
chmod -R 775 writable/temp/
```

### 3. Configurar Dompdf (ya instalado)

```php
// Verificar en app/Libraries/PDFReportGenerator.php
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Arial');
$options->set('chroot', realpath(base_path()));
```

---

## 🔧 IMPLEMENTACIÓN PASO A PASO

## PASO 1: Crear Helper JavaScript para Captura de Gráficos

**Archivo:** `public/assets/js/chart-capture.js`

```javascript
/**
 * Utilidad para capturar gráficos Chart.js como imágenes Base64
 * Compatible con todos los dashboards de PsyRisk
 */
class ChartCapture {
    /**
     * Captura un canvas individual como Base64
     */
    static captureChart(canvasId) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) {
            console.warn(`Canvas ${canvasId} no encontrado`);
            return null;
        }

        try {
            // Convertir a PNG de alta calidad
            return canvas.toDataURL('image/png', 1.0);
        } catch (error) {
            console.error(`Error capturando ${canvasId}:`, error);
            return null;
        }
    }

    /**
     * Captura múltiples gráficos
     */
    static captureMultiple(canvasIds) {
        const charts = {};

        canvasIds.forEach(id => {
            const base64 = this.captureChart(id);
            if (base64) {
                charts[id] = base64;
            }
        });

        return charts;
    }

    /**
     * Captura TODOS los canvas de la página
     */
    static captureAll() {
        const canvases = document.querySelectorAll('canvas');
        const charts = {};

        canvases.forEach(canvas => {
            if (canvas.id) {
                const base64 = canvas.toDataURL('image/png', 1.0);
                charts[canvas.id] = base64;
            }
        });

        return charts;
    }

    /**
     * Envía los gráficos al backend para generar PDF
     */
    static async generatePDF(serviceId, reportType, charts) {
        const response = await fetch(`/reports/generate-pdf/${reportType}/${serviceId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                charts: charts,
                timestamp: new Date().getTime()
            })
        });

        if (!response.ok) {
            throw new Error('Error generando PDF');
        }

        // Descargar PDF
        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `informe_${reportType}_${serviceId}_${Date.now()}.pdf`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    }
}

// Exponer globalmente
window.ChartCapture = ChartCapture;
```

---

## PASO 2: Modificar Vistas de Dashboard - Agregar Botón PDF

**Archivo:** `app/Views/reports/intralaboral/dashboard.php` (línea ~750)

```php
<!-- Agregar ANTES de los gráficos -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Informe Completo</h5>
                    <small class="text-muted">Descargar PDF con todos los gráficos y análisis</small>
                </div>
                <button
                    id="btnGenerarPDF"
                    class="btn btn-primary btn-lg"
                    onclick="generarPDFIntralaboral()"
                >
                    <i class="fas fa-file-pdf me-2"></i>Generar PDF Completo
                </button>
            </div>
        </div>
    </div>
</div>
```

**Agregar al final del archivo, DESPUÉS de los scripts de Chart.js (línea ~2200):**

```php
<!-- Script para generar PDF -->
<script src="<?= base_url('assets/js/chart-capture.js') ?>"></script>
<script>
async function generarPDFIntralaboral() {
    const btn = document.getElementById('btnGenerarPDF');
    const originalText = btn.innerHTML;

    try {
        // Deshabilitar botón y mostrar loading
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Generando PDF...';

        // Esperar 500ms para asegurar que todos los gráficos estén renderizados
        await new Promise(resolve => setTimeout(resolve, 500));

        // Capturar todos los gráficos
        const charts = ChartCapture.captureAll();

        console.log('Gráficos capturados:', Object.keys(charts));

        // Generar PDF
        await ChartCapture.generatePDF(
            <?= $service['id'] ?>,
            'intralaboral',
            charts
        );

        // Restaurar botón
        btn.innerHTML = '<i class="fas fa-check me-2"></i>PDF Descargado';
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }, 2000);

    } catch (error) {
        console.error('Error generando PDF:', error);
        alert('Error al generar el PDF. Por favor, intenta nuevamente.');
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
}
</script>
```

**IMPORTANTE:** Repetir esto para:
- `app/Views/reports/extralaboral/dashboard.php` → `generarPDFExtralaboral()`
- `app/Views/reports/estres/dashboard.php` → `generarPDFEstres()`
- `app/Views/reports/heatmap/index.php` → `generarPDFHeatmap()`

---

## PASO 3: Crear Ruta en el Router

**Archivo:** `app/Config/Routes.php` (dentro del grupo 'reports')

```php
$routes->group('reports', function($routes) {
    // ... rutas existentes ...

    // Nueva ruta para generar PDFs con gráficos
    $routes->post('generate-pdf/(:segment)/(:num)', 'ReportsController::generatePdfWithCharts/$1/$2');
});
```

---

## PASO 4: Método en ReportsController

**Archivo:** `app/Controllers/ReportsController.php`

```php
/**
 * Genera PDF con gráficos capturados desde el frontend
 *
 * @param string $reportType Tipo de reporte (intralaboral, extralaboral, estres, heatmap)
 * @param int $serviceId ID del servicio
 */
public function generatePdfWithCharts($reportType, $serviceId)
{
    // Validar tipo de reporte
    $validTypes = ['intralaboral', 'extralaboral', 'estres', 'heatmap'];
    if (!in_array($reportType, $validTypes)) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Tipo de reporte inválido'
        ])->setStatusCode(400);
    }

    // Verificar acceso al servicio
    $service = $this->checkAccess($serviceId);
    if ($service instanceof \CodeIgniter\HTTP\RedirectResponse) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'No tienes permisos para acceder a este servicio'
        ])->setStatusCode(403);
    }

    // Obtener gráficos del request
    $json = $this->request->getJSON(true);
    $chartImages = $json['charts'] ?? [];

    if (empty($chartImages)) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'No se recibieron gráficos para incluir en el PDF'
        ])->setStatusCode(400);
    }

    log_message('info', "Generando PDF {$reportType} para servicio {$serviceId} con " . count($chartImages) . " gráficos");

    try {
        // Generar PDF usando PDFReportGenerator
        $generator = new \App\Libraries\PDFReportGenerator();
        $generator->setChartImages($chartImages);
        $generator->setService($service);

        // Generar según tipo
        $pdfPath = match($reportType) {
            'intralaboral' => $generator->generateIntralaboralReport($serviceId),
            'extralaboral' => $generator->generateExtralaboralReport($serviceId),
            'estres' => $generator->generateEstresReport($serviceId),
            'heatmap' => $generator->generateHeatmapReport($serviceId),
            default => throw new \Exception('Tipo de reporte no soportado')
        };

        // Descargar y limpiar
        $response = $this->response
            ->download($pdfPath, null)
            ->setContentType('application/pdf');

        // Limpiar archivo temporal después de descarga
        register_shutdown_function(function() use ($pdfPath) {
            if (file_exists($pdfPath)) {
                @unlink($pdfPath);
            }
        });

        return $response;

    } catch (\Exception $e) {
        log_message('error', "Error generando PDF {$reportType}: " . $e->getMessage());

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Error al generar el PDF: ' . $e->getMessage()
        ])->setStatusCode(500);
    }
}
```

---

## PASO 5: Modificar PDFReportGenerator (Enfoque Modular)

**Archivo:** `app/Libraries/PDFReportGenerator.php`

```php
<?php

namespace App\Libraries;

use Dompdf\Dompdf;
use Dompdf\Options;
use setasign\Fpdi\Fpdi;

class PDFReportGenerator
{
    private $chartImages = [];
    private $service = null;

    /**
     * Configura las imágenes de gráficos capturadas desde el frontend
     */
    public function setChartImages($images)
    {
        $this->chartImages = $images;
        return $this;
    }

    /**
     * Configura los datos del servicio
     */
    public function setService($service)
    {
        $this->service = $service;
        return $this;
    }

    /**
     * Genera una sección individual como PDF
     *
     * @param string $sectionName Nombre de la sección (para archivo temporal)
     * @param string $htmlContent Contenido HTML de la sección
     * @return string Path del PDF temporal generado
     */
    private function generateSectionPDF($sectionName, $htmlContent)
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Arial');
        $options->set('chroot', realpath(base_path()));

        $dompdf = new Dompdf($options);

        // Construir HTML completo
        $html = $this->getHTMLHeader();
        $html .= $htmlContent;
        $html .= $this->getHTMLFooter();

        $dompdf->loadHtml($html);
        $dompdf->setPaper('Letter', 'portrait');
        $dompdf->render();

        // Guardar temporalmente
        $tempPath = WRITEPATH . "temp/{$sectionName}_" . time() . ".pdf";
        file_put_contents($tempPath, $dompdf->output());

        log_message('debug', "Sección generada: {$sectionName} -> {$tempPath}");

        return $tempPath;
    }

    /**
     * Une múltiples PDFs en uno solo usando FPDI
     *
     * @param array $pdfFiles Array de paths de PDFs a unir
     * @return string Path del PDF unificado
     */
    private function mergePDFs($pdfFiles)
    {
        $pdf = new Fpdi();

        foreach ($pdfFiles as $file) {
            if (!file_exists($file)) {
                log_message('warning', "PDF no encontrado para merge: {$file}");
                continue;
            }

            try {
                $pageCount = $pdf->setSourceFile($file);

                for ($page = 1; $page <= $pageCount; $page++) {
                    $pdf->AddPage();
                    $template = $pdf->importPage($page);
                    $pdf->useTemplate($template);
                }

                log_message('debug', "Merged {$pageCount} páginas de {$file}");

            } catch (\Exception $e) {
                log_message('error', "Error mergeando {$file}: " . $e->getMessage());
            }
        }

        // Guardar PDF unificado
        $outputPath = WRITEPATH . 'temp/informe_completo_' . time() . '.pdf';
        $pdf->Output($outputPath, 'F');

        log_message('info', "PDF unificado generado: {$outputPath}");

        return $outputPath;
    }

    /**
     * Genera informe completo de Intralaboral
     */
    public function generateIntralaboralReport($serviceId)
    {
        $tempPdfs = [];

        try {
            // Cargar datos del servicio y resultados
            $this->loadServiceData($serviceId);

            // Generar cada sección como PDF independiente
            $tempPdfs[] = $this->generateSectionPDF(
                'portada_intralaboral',
                $this->generateCoverPage('Intralaboral')
            );

            $tempPdfs[] = $this->generateSectionPDF(
                'contenido_intralaboral',
                $this->generateTableOfContents()
            );

            $tempPdfs[] = $this->generateSectionPDF(
                'introduccion',
                $this->generateIntroduction()
            );

            $tempPdfs[] = $this->generateSectionPDF(
                'demografico',
                $this->generateDemographicSectionWithCharts()
            );

            $tempPdfs[] = $this->generateSectionPDF(
                'intralaboral_analisis',
                $this->generateIntralaboralSectionWithCharts()
            );

            $tempPdfs[] = $this->generateSectionPDF(
                'recomendaciones',
                $this->generateRecommendationsSection()
            );

            // Unir todos los PDFs
            $finalPdf = $this->mergePDFs($tempPdfs);

            return $finalPdf;

        } finally {
            // Limpiar PDFs temporales de secciones
            foreach ($tempPdfs as $tempPdf) {
                if (file_exists($tempPdf)) {
                    @unlink($tempPdf);
                }
            }
        }
    }

    /**
     * Genera sección de análisis intralaboral CON gráficos
     */
    private function generateIntralaboralSectionWithCharts()
    {
        $html = '<div class="section">';
        $html .= '<h1>Análisis de Riesgo Intralaboral</h1>';

        // Gráfico de distribución de riesgo
        if (isset($this->chartImages['riskChart'])) {
            $html .= '<div class="chart-container">';
            $html .= '<h2>Distribución General de Riesgo</h2>';
            $html .= '<img src="' . $this->chartImages['riskChart'] . '" style="width: 100%; max-width: 600px; margin: 20px auto; display: block;"/>';
            $html .= '</div>';
            $html .= '<div class="page-break"></div>';
        }

        // Gráfico de dominios
        if (isset($this->chartImages['domainsChart'])) {
            $html .= '<div class="chart-container">';
            $html .= '<h2>Análisis por Dominios Psicosociales</h2>';
            $html .= '<p>Los dominios representan conjuntos de factores psicosociales relacionados:</p>';
            $html .= '<img src="' . $this->chartImages['domainsChart'] . '" style="width: 100%; max-width: 700px; margin: 20px auto; display: block;"/>';
            $html .= '</div>';
            $html .= '<div class="page-break"></div>';
        }

        // Gráfico de distribución por género
        if (isset($this->chartImages['genderChart'])) {
            $html .= '<div class="chart-container">';
            $html .= '<h2>Distribución por Género</h2>';
            $html .= '<img src="' . $this->chartImages['genderChart'] . '" style="width: 100%; max-width: 500px; margin: 20px auto; display: block;"/>';
            $html .= '</div>';
            $html .= '<div class="page-break"></div>';
        }

        // Dimensiones agrupadas
        if (isset($this->chartImages['dimensionsGroupedChart'])) {
            $html .= '<div class="chart-container">';
            $html .= '<h2>Análisis Detallado por Dimensiones</h2>';
            $html .= '<img src="' . $this->chartImages['dimensionsGroupedChart'] . '" style="width: 100%; max-width: 800px; margin: 20px auto; display: block;"/>';
            $html .= '</div>';
            $html .= '<div class="page-break"></div>';
        }

        // Top dimensiones críticas
        if (isset($this->chartImages['topDimensionsChart'])) {
            $html .= '<div class="chart-container">';
            $html .= '<h2>Dimensiones Más Críticas</h2>';
            $html .= '<p>Estas son las dimensiones que requieren atención prioritaria:</p>';
            $html .= '<img src="' . $this->chartImages['topDimensionsChart'] . '" style="width: 100%; max-width: 700px; margin: 20px auto; display: block;"/>';
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Genera sección demográfica CON gráficos
     */
    private function generateDemographicSectionWithCharts()
    {
        $html = '<div class="section">';
        $html .= '<h1>Variables Sociodemográficas</h1>';

        // Análisis por departamento
        if (isset($this->chartImages['departmentChart'])) {
            $html .= '<div class="chart-container">';
            $html .= '<h2>Distribución por Departamento</h2>';
            $html .= '<img src="' . $this->chartImages['departmentChart'] . '" style="width: 100%; max-width: 700px; margin: 20px auto; display: block;"/>';
            $html .= '</div>';
            $html .= '<div class="page-break"></div>';
        }

        // Análisis por educación
        if (isset($this->chartImages['educationChart'])) {
            $html .= '<div class="chart-container">';
            $html .= '<h2>Riesgo por Nivel Educativo</h2>';
            $html .= '<img src="' . $this->chartImages['educationChart'] . '" style="width: 100%; max-width: 700px; margin: 20px auto; display: block;"/>';
            $html .= '</div>';
            $html .= '<div class="page-break"></div>';
        }

        // Análisis por edad
        if (isset($this->chartImages['ageChart'])) {
            $html .= '<div class="chart-container">';
            $html .= '<h2>Riesgo por Grupo Etario</h2>';
            $html .= '<img src="' . $this->chartImages['ageChart'] . '" style="width: 100%; max-width: 700px; margin: 20px auto; display: block;"/>';
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Genera portada personalizada
     */
    private function generateCoverPage($reportType)
    {
        $html = '<div class="cover-page" style="text-align: center; padding: 100px 50px;">';
        $html .= '<h1 style="font-size: 36px; color: #667eea; margin-bottom: 30px;">INFORME DE EVALUACIÓN</h1>';
        $html .= '<h2 style="font-size: 28px; margin-bottom: 50px;">Riesgo Psicosocial ' . $reportType . '</h2>';

        if ($this->service) {
            $html .= '<div style="margin: 50px 0;">';
            $html .= '<h3 style="font-size: 20px; color: #333;">Empresa:</h3>';
            $html .= '<p style="font-size: 24px; font-weight: bold;">' . esc($this->service['company_name']) . '</p>';
            $html .= '</div>';

            $html .= '<div style="margin: 30px 0;">';
            $html .= '<p><strong>Servicio:</strong> ' . esc($this->service['service_name']) . '</p>';
            $html .= '<p><strong>Fecha:</strong> ' . date('d/m/Y', strtotime($this->service['service_date'])) . '</p>';
            $html .= '</div>';
        }

        $html .= '<div style="position: absolute; bottom: 50px; width: 100%; text-align: center;">';
        $html .= '<p style="color: #666;">Generado con PsyRisk</p>';
        $html .= '<p style="color: #999; font-size: 12px;">Resolución 2404/2019 - Ministerio de Trabajo de Colombia</p>';
        $html .= '</div>';

        $html .= '</div>';

        return $html;
    }

    /**
     * Header HTML base para PDFs
     */
    private function getHTMLHeader()
    {
        return '
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <style>
                @page {
                    margin: 2cm;
                }
                body {
                    font-family: Arial, sans-serif;
                    font-size: 11pt;
                    line-height: 1.6;
                    color: #333;
                }
                h1 {
                    color: #667eea;
                    font-size: 24pt;
                    margin-top: 20px;
                    margin-bottom: 15px;
                }
                h2 {
                    color: #764ba2;
                    font-size: 18pt;
                    margin-top: 15px;
                    margin-bottom: 10px;
                }
                .chart-container {
                    margin: 30px 0;
                    text-align: center;
                }
                .page-break {
                    page-break-after: always;
                }
                img {
                    max-width: 100%;
                    height: auto;
                }
                .section {
                    margin-bottom: 30px;
                }
            </style>
        </head>
        <body>
        ';
    }

    /**
     * Footer HTML base para PDFs
     */
    private function getHTMLFooter()
    {
        return '
        </body>
        </html>
        ';
    }

    /**
     * Método stub para cargar datos del servicio
     * IMPORTANTE: Implementar con lógica real de tu sistema
     */
    private function loadServiceData($serviceId)
    {
        // TODO: Implementar carga de datos
        log_message('debug', "Cargando datos del servicio {$serviceId}");
    }

    /**
     * Placeholder para tabla de contenidos
     */
    private function generateTableOfContents()
    {
        return '<h1>Tabla de Contenidos</h1><p>Contenido del informe...</p>';
    }

    /**
     * Placeholder para introducción
     */
    private function generateIntroduction()
    {
        return '<h1>Introducción</h1><p>Metodología y objetivos...</p>';
    }

    /**
     * Placeholder para recomendaciones
     */
    private function generateRecommendationsSection()
    {
        return '<h1>Recomendaciones</h1><p>Plan de intervención...</p>';
    }

    /**
     * Generar reporte de Extralaboral (similar a Intralaboral)
     */
    public function generateExtralaboralReport($serviceId)
    {
        // TODO: Implementar similar a generateIntralaboralReport
        return $this->generateIntralaboralReport($serviceId);
    }

    /**
     * Generar reporte de Estrés
     */
    public function generateEstresReport($serviceId)
    {
        // TODO: Implementar
        return $this->generateIntralaboralReport($serviceId);
    }

    /**
     * Generar reporte de Heatmap
     */
    public function generateHeatmapReport($serviceId)
    {
        // TODO: Implementar
        return $this->generateIntralaboralReport($serviceId);
    }
}
```

---

## 🧪 PLAN DE PRUEBAS

### Test 1: Captura de Gráficos
1. Abrir `/reports/intralaboral/1`
2. Abrir DevTools Console
3. Ejecutar: `console.log(ChartCapture.captureAll())`
4. Verificar que se capturen todos los canvas como Base64

### Test 2: Generación de PDF Simple
1. Click en "Generar PDF Completo"
2. Verificar que se descargue un PDF
3. Abrir PDF y verificar que tenga al menos 1 gráfico visible

### Test 3: PDF Completo
1. Generar PDF de Intralaboral completo
2. Verificar que incluya TODAS las secciones
3. Verificar que TODOS los gráficos se vean correctamente
4. Verificar calidad de imágenes (deben ser nítidas)

### Test 4: Limpieza de Temporales
1. Generar PDF
2. Verificar `writable/temp/`
3. Confirmar que no quedan archivos huérfanos

---

## 📊 CHECKLIST DE IMPLEMENTACIÓN

### Fase 1: Setup
- [ ] Instalar FPDI: `composer require setasign/fpdi`
- [ ] Crear `public/assets/js/chart-capture.js`
- [ ] Verificar permisos en `writable/temp/`

### Fase 2: Frontend
- [ ] Modificar `app/Views/reports/intralaboral/dashboard.php`
- [ ] Modificar `app/Views/reports/extralaboral/dashboard.php`
- [ ] Modificar `app/Views/reports/estres/dashboard.php`
- [ ] Modificar `app/Views/reports/heatmap/index.php`

### Fase 3: Backend
- [ ] Agregar ruta en `app/Config/Routes.php`
- [ ] Crear método `generatePdfWithCharts()` en ReportsController
- [ ] Modificar `app/Libraries/PDFReportGenerator.php`

### Fase 4: Testing
- [ ] Test captura de gráficos
- [ ] Test generación PDF Intralaboral
- [ ] Test generación PDF Extralaboral
- [ ] Test generación PDF Estrés
- [ ] Test generación PDF Heatmap

### Fase 5: Optimización
- [ ] Implementar caché de PDFs (opcional)
- [ ] Agregar barra de progreso en frontend
- [ ] Optimizar tamaño de imágenes Base64
- [ ] Implementar generación asíncrona (opcional)

---

## 🚀 MEJORAS FUTURAS (Post-MVP)

### Nivel 1: Funcional
- ✅ PDFs con gráficos básicos

### Nivel 2: Profesional
- [ ] Numeración de páginas
- [ ] Headers y footers personalizados
- [ ] Marca de agua (opcional)
- [ ] Firma digital del consultor

### Nivel 3: Avanzado
- [ ] Generación asíncrona con cola de trabajos
- [ ] Envío automático por email
- [ ] Versionado de PDFs
- [ ] Comparación entre periodos

### Nivel 4: Empresarial
- [ ] Caché inteligente de PDFs
- [ ] CDN para descarga
- [ ] Estadísticas de descargas
- [ ] Exportación a Word/Excel

---

## ⚠️ NOTAS IMPORTANTES

### Limitaciones de Dompdf
- **NO soporta Flexbox** (usar tablas HTML)
- **NO soporta CSS Grid** (usar tablas HTML)
- **JavaScript NO se ejecuta** (por eso capturamos desde frontend)
- **Tamaño máximo recomendado:** 10MB por PDF

### Optimizaciones de Rendimiento
- Limitar resolución de gráficos a 1200px width máximo
- Comprimir Base64 antes de enviar (opcional)
- Usar PNG solo cuando sea necesario (JPG para fotos)
- Implementar timeout de 2 minutos en generación

### Seguridad
- Validar siempre el `serviceId` y permisos del usuario
- Sanitizar nombres de archivos temporales
- Limpiar archivos temporales después de 1 hora máximo
- No almacenar Base64 en logs

---

## 📞 SOPORTE Y TROUBLESHOOTING

### Error: "Failed to load image"
**Causa:** Base64 inválido o muy largo
**Solución:** Verificar que `toDataURL()` se ejecute después de que Chart.js termine de renderizar

### Error: "Memory exhausted"
**Causa:** Demasiados gráficos o muy grandes
**Solución:** Reducir calidad PNG de 1.0 a 0.8 en `toDataURL('image/png', 0.8)`

### Error: "Cannot write to writable/temp/"
**Causa:** Permisos de escritura
**Solución:** `chmod -R 775 writable/temp/`

### PDFs vacíos o sin gráficos
**Causa:** No se recibieron imágenes Base64 del frontend
**Solución:** Verificar console.log y network tab en DevTools

---

## 🎓 RECURSOS Y REFERENCIAS

### Documentación Oficial
- [Dompdf](https://github.com/dompdf/dompdf)
- [FPDI](https://www.setasign.com/products/fpdi/about/)
- [Chart.js](https://www.chartjs.org/docs/latest/)
- [Canvas API](https://developer.mozilla.org/en-US/docs/Web/API/Canvas_API)

### Tutoriales Relacionados
- [Chart.js to PNG](https://www.chartjs.org/docs/latest/developers/api.html#tobase64image)
- [FPDI PDF Merging](https://www.setasign.com/products/fpdi/demos/concatenate-fake-tree/)

---

## 📝 AUTOR Y VERSIÓN

**Documento:** PLAN_IMPLEMENTACION_PDF_GRAFICOS.md
**Versión:** 1.0
**Fecha:** 2025-01-23
**Sistema:** PsyRisk - Batería de Riesgo Psicosocial
**Framework:** CodeIgniter 4

---

## 🔥 COMANDO RÁPIDO PARA EMPEZAR

```bash
# Cuando estés listo para implementar, ejecuta:
composer require setasign/fpdi
mkdir -p public/assets/js
# Luego sigue el PASO 1 del plan
```

---

**FIN DEL PLAN DE IMPLEMENTACIÓN**

Este documento es tu guía completa. Cuando estés listo, simplemente pásame este archivo y te diré:

> "Implementa el PASO X del PLAN_IMPLEMENTACION_PDF_GRAFICOS.md"

Y yo sabré exactamente qué hacer. 🚀
