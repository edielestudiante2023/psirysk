<?php

namespace App\Controllers\Pdf\Services;

use App\Controllers\Pdf\PdfBaseController;
use App\Controllers\Pdf\Estaticas\PortadaController;
use App\Controllers\Pdf\Estaticas\ContenidoController;
use App\Controllers\Pdf\Estaticas\IntroduccionController;
use App\Controllers\Pdf\Resultados\SociodemograficosController;
use App\Controllers\Pdf\Resultados\MapasCalorController;
use App\Controllers\Pdf\Resultados\Intralaboral\DimensionesController as IntralaboralDimensionesController;
use App\Controllers\Pdf\Resultados\Extralaboral\DimensionesController as ExtralaboralDimensionesController;
use App\Controllers\Pdf\Resultados\Estres\EstresController;
use App\Controllers\Pdf\Resultados\Recomendaciones\RecomendacionesController;

/**
 * Orquestador del PDF completo
 * Ensambla todas las secciones del informe en el orden correcto
 *
 * Tipos de informe:
 * - Completo: Informe de Batería de Riesgo Psicosocial (todas las secciones)
 * - Ejecutivo: Informe Ejecutivo de Batería de Riesgo Psicosocial (portada + mapas calor + recomendaciones)
 */
class PdfReportOrchestrator extends PdfBaseController
{
    /**
     * Orden de las secciones del informe COMPLETO
     */
    protected $sections = [
        'portada',
        'contenido',
        'introduccion',  // Introducción + Marco Conceptual + Marco Legal + Objetivos + Metodología
        'sociodemograficos',  // Síntesis General + Variables Sociodemográficas + Resultados Ocupacionales
        'mapas_calor',  // Resultados + Conclusiones + Mapas de Calor (General, Intralaboral A/B, Extralaboral A/B, Estrés A/B)
        'intralaboral_dimensiones',
        'extralaboral_dimensiones',
        'estres',
        'firma',  // Firma del psicólogo - Cierre del informe
    ];

    /**
     * Secciones del informe EJECUTIVO (resumen)
     */
    protected $executiveSections = [
        'portada',
        'mapas_calor',
        'recomendaciones',
    ];

    /**
     * Genera el HTML completo del informe
     *
     * @param int $batteryServiceId
     * @param array $options Opciones de generación (secciones a incluir, report_type, etc.)
     * @return string HTML completo del informe
     */
    public function generateFullReport($batteryServiceId, $options = [])
    {
        $this->initializeData($batteryServiceId);

        $sectionsToInclude = $options['sections'] ?? $this->sections;
        $reportType = $options['report_type'] ?? 'completo';
        $html = '';

        // Agregar estilos CSS
        $html .= $this->getStyles();

        // Generar cada sección
        foreach ($sectionsToInclude as $section) {
            $sectionHtml = $this->renderSection($section, $batteryServiceId, $reportType);
            if (!empty($sectionHtml)) {
                $html .= $sectionHtml;
            }
        }

        return $html;
    }

    /**
     * Renderiza una sección específica
     *
     * @param string $section Nombre de la sección
     * @param int $batteryServiceId ID del servicio de batería
     * @param string $reportType Tipo de informe: 'completo' o 'ejecutivo'
     */
    private function renderSection($section, $batteryServiceId, $reportType = 'completo')
    {
        switch ($section) {
            case 'portada':
                $controller = new PortadaController();
                return $controller->render($batteryServiceId, $reportType);

            case 'contenido':
                $controller = new ContenidoController();
                return $controller->render($batteryServiceId);

            case 'introduccion':
                $controller = new IntroduccionController();
                return $controller->render($batteryServiceId);

            case 'sociodemograficos':
                $controller = new SociodemograficosController();
                return $controller->render($batteryServiceId);

            case 'mapas_calor':
                $controller = new MapasCalorController();
                return $controller->render($batteryServiceId);

            case 'intralaboral_dimensiones':
                $controller = new IntralaboralDimensionesController();
                return $controller->render($batteryServiceId);

            case 'extralaboral_dimensiones':
                $controller = new ExtralaboralDimensionesController();
                return $controller->render($batteryServiceId);

            case 'estres':
                $controller = new EstresController();
                return $controller->render($batteryServiceId);

            case 'recomendaciones':
                $controller = new RecomendacionesController();
                return $controller->render($batteryServiceId);

            case 'firma':
                return $this->renderFirma();

            default:
                return '';
        }
    }

    /**
     * Obtiene los estilos CSS del PDF
     */
    private function getStyles()
    {
        $cssPath = APPPATH . 'Views/pdf/_partials/css/pdf-styles.css';
        if (file_exists($cssPath)) {
            return '<style>' . file_get_contents($cssPath) . '</style>';
        }
        return '';
    }

    /**
     * Renderiza la página de firma del psicólogo
     */
    private function renderFirma()
    {
        return $this->renderView('pdf/_partials/firma_page', [
            'contenidoPrevio' => '',
        ]);
    }

    /**
     * Genera el HTML del informe EJECUTIVO (resumen)
     *
     * @param int $batteryServiceId
     * @return string HTML del informe ejecutivo
     */
    public function generateExecutiveReport($batteryServiceId)
    {
        return $this->generateFullReport($batteryServiceId, [
            'sections' => $this->executiveSections,
            'report_type' => 'ejecutivo'
        ]);
    }

    /**
     * Preview del informe completo en navegador
     */
    public function preview($batteryServiceId)
    {
        $this->initializeData($batteryServiceId);
        $companyName = $this->companyData['company_name'] ?? 'Empresa';
        $html = $this->generateFullReport($batteryServiceId);

        return view('pdf/_partials/preview_wrapper', [
            'content' => $html,
            'pageTitle' => "Informe de Batería de Riesgo Psicosocial - {$companyName}",
            'batteryServiceId' => $batteryServiceId
        ]);
    }

    /**
     * Preview del informe EJECUTIVO en navegador
     */
    public function previewExecutive($batteryServiceId)
    {
        $this->initializeData($batteryServiceId);
        $companyName = $this->companyData['company_name'] ?? 'Empresa';
        $html = $this->generateExecutiveReport($batteryServiceId);

        return view('pdf/_partials/preview_wrapper', [
            'content' => $html,
            'pageTitle' => "Informe Ejecutivo de Batería de Riesgo Psicosocial - {$companyName}",
            'batteryServiceId' => $batteryServiceId
        ]);
    }

    /**
     * Genera el archivo PDF COMPLETO usando wkhtmltopdf (uso interno)
     *
     * @param int $batteryServiceId
     * @return array ['success' => bool, 'path' => string, 'error' => string]
     */
    private function createCompletePdf($batteryServiceId)
    {
        $html = $this->generateFullReport($batteryServiceId);

        // Crear HTML completo con DOCTYPE
        $fullHtml = $this->wrapHtmlForPdf($html, 'completo');

        // Guardar HTML temporal
        $tempHtmlPath = WRITEPATH . 'pdf/temp_report_' . $batteryServiceId . '.html';
        $tempPdfPath = WRITEPATH . 'pdf/report_' . $batteryServiceId . '.pdf';

        // Asegurar que el directorio existe
        if (!is_dir(WRITEPATH . 'pdf')) {
            mkdir(WRITEPATH . 'pdf', 0755, true);
        }

        file_put_contents($tempHtmlPath, $fullHtml);

        // Comando wkhtmltopdf
        $wkhtmltopdfPath = $this->getWkhtmltopdfPath();

        $command = sprintf(
            '%s --page-size Letter --orientation Portrait ' .
            '--margin-top 10mm --margin-bottom 10mm --margin-left 10mm --margin-right 10mm ' .
            '--enable-local-file-access ' .
            '--encoding UTF-8 ' .
            '"%s" "%s" 2>&1',
            $wkhtmltopdfPath,
            $tempHtmlPath,
            $tempPdfPath
        );

        exec($command, $output, $returnCode);

        // Limpiar archivo temporal (comentado para debug)
        // @unlink($tempHtmlPath);

        if ($returnCode !== 0) {
            log_message('error', 'wkhtmltopdf error (completo): ' . implode("\n", $output));
            return [
                'success' => false,
                'path' => null,
                'error' => implode("\n", $output)
            ];
        }

        return [
            'success' => true,
            'path' => $tempPdfPath,
            'error' => null
        ];
    }

    /**
     * Genera el PDF COMPLETO usando wkhtmltopdf (endpoint API)
     */
    public function generate($batteryServiceId)
    {
        $result = $this->createCompletePdf($batteryServiceId);

        if (!$result['success']) {
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'Error generando PDF Completo',
                'details' => $result['error']
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'pdf_path' => $result['path'],
            'download_url' => site_url('pdf/download/completo/' . $batteryServiceId)
        ]);
    }

    /**
     * Descarga el PDF COMPLETO generado
     */
    public function download($batteryServiceId)
    {
        $pdfPath = WRITEPATH . 'pdf/report_' . $batteryServiceId . '.pdf';

        // Generar si no existe
        if (!file_exists($pdfPath)) {
            $result = $this->createCompletePdf($batteryServiceId);
            if (!$result['success']) {
                return $this->response->setStatusCode(500)->setJSON([
                    'error' => 'Error generando PDF Completo',
                    'details' => $result['error']
                ]);
            }
        }

        if (!file_exists($pdfPath)) {
            return $this->response->setStatusCode(404)->setJSON([
                'error' => 'PDF no encontrado'
            ]);
        }

        // Obtener nombre de la empresa para el nombre del archivo
        $this->initializeData($batteryServiceId);
        $companyName = $this->companyData['company_name'] ?? 'Empresa';
        $companyName = preg_replace('/[^a-zA-Z0-9\s]/', '', $companyName);
        $companyName = str_replace(' ', '_', trim($companyName));
        $fileName = "Informe_Bateria_Riesgo_Psicosocial_{$companyName}.pdf";

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $fileName . '"')
            ->setBody(file_get_contents($pdfPath));
    }

    /**
     * Genera el archivo PDF EJECUTIVO usando wkhtmltopdf (uso interno)
     *
     * @param int $batteryServiceId
     * @return array ['success' => bool, 'path' => string, 'error' => string]
     */
    private function createExecutivePdf($batteryServiceId)
    {
        $html = $this->generateExecutiveReport($batteryServiceId);

        // Crear HTML completo con DOCTYPE
        $fullHtml = $this->wrapHtmlForPdf($html, 'ejecutivo');

        // Guardar HTML temporal
        $tempHtmlPath = WRITEPATH . 'pdf/temp_executive_' . $batteryServiceId . '.html';
        $tempPdfPath = WRITEPATH . 'pdf/executive_' . $batteryServiceId . '.pdf';

        // Asegurar que el directorio existe
        if (!is_dir(WRITEPATH . 'pdf')) {
            mkdir(WRITEPATH . 'pdf', 0755, true);
        }

        file_put_contents($tempHtmlPath, $fullHtml);

        // Comando wkhtmltopdf
        $wkhtmltopdfPath = $this->getWkhtmltopdfPath();

        $command = sprintf(
            '%s --page-size Letter --orientation Portrait ' .
            '--margin-top 10mm --margin-bottom 10mm --margin-left 10mm --margin-right 10mm ' .
            '--enable-local-file-access ' .
            '--encoding UTF-8 ' .
            '"%s" "%s" 2>&1',
            $wkhtmltopdfPath,
            $tempHtmlPath,
            $tempPdfPath
        );

        exec($command, $output, $returnCode);

        // Limpiar archivo temporal (comentado para debug)
        // @unlink($tempHtmlPath);

        if ($returnCode !== 0) {
            log_message('error', 'wkhtmltopdf error (ejecutivo): ' . implode("\n", $output));
            return [
                'success' => false,
                'path' => null,
                'error' => implode("\n", $output)
            ];
        }

        return [
            'success' => true,
            'path' => $tempPdfPath,
            'error' => null
        ];
    }

    /**
     * Genera el PDF EJECUTIVO usando wkhtmltopdf (endpoint API)
     */
    public function generateExecutive($batteryServiceId)
    {
        $result = $this->createExecutivePdf($batteryServiceId);

        if (!$result['success']) {
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'Error generando PDF Ejecutivo',
                'details' => $result['error']
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'pdf_path' => $result['path'],
            'download_url' => site_url('pdf/download/ejecutivo/' . $batteryServiceId)
        ]);
    }

    /**
     * Descarga el PDF EJECUTIVO generado
     */
    public function downloadExecutive($batteryServiceId)
    {
        $pdfPath = WRITEPATH . 'pdf/executive_' . $batteryServiceId . '.pdf';

        // Generar si no existe
        if (!file_exists($pdfPath)) {
            $result = $this->createExecutivePdf($batteryServiceId);
            if (!$result['success']) {
                return $this->response->setStatusCode(500)->setJSON([
                    'error' => 'Error generando PDF Ejecutivo',
                    'details' => $result['error']
                ]);
            }
        }

        if (!file_exists($pdfPath)) {
            return $this->response->setStatusCode(404)->setJSON([
                'error' => 'PDF Ejecutivo no encontrado'
            ]);
        }

        // Obtener nombre de la empresa para el nombre del archivo
        $this->initializeData($batteryServiceId);
        $companyName = $this->companyData['company_name'] ?? 'Empresa';
        $companyName = preg_replace('/[^a-zA-Z0-9\s]/', '', $companyName);
        $companyName = str_replace(' ', '_', trim($companyName));
        $fileName = "Informe_Ejecutivo_Bateria_Riesgo_Psicosocial_{$companyName}.pdf";

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $fileName . '"')
            ->setBody(file_get_contents($pdfPath));
    }

    /**
     * Envuelve el HTML en un documento completo para wkhtmltopdf
     *
     * @param string $content El contenido HTML
     * @param string $reportType Tipo de informe: 'completo' o 'ejecutivo'
     */
    private function wrapHtmlForPdf($content, $reportType = 'completo')
    {
        $companyName = $this->companyData['company_name'] ?? 'Empresa';

        if ($reportType === 'ejecutivo') {
            $title = "Informe Ejecutivo de Batería de Riesgo Psicosocial - {$companyName}";
        } else {
            $title = "Informe de Batería de Riesgo Psicosocial - {$companyName}";
        }

        return '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>' . htmlspecialchars($title) . '</title>
</head>
<body>
' . $content . '
</body>
</html>';
    }

    /**
     * Obtiene la ruta de wkhtmltopdf según el sistema operativo
     * Retorna el path ya con comillas para Windows si es necesario
     */
    private function getWkhtmltopdfPath()
    {
        if (PHP_OS_FAMILY === 'Windows') {
            // Rutas comunes en Windows
            $paths = [
                'C:\\Program Files\\wkhtmltopdf\\bin\\wkhtmltopdf.exe',
                'C:\\Program Files (x86)\\wkhtmltopdf\\bin\\wkhtmltopdf.exe',
            ];

            foreach ($paths as $path) {
                if (file_exists($path)) {
                    // Retornar con comillas para manejar espacios en el path
                    return '"' . $path . '"';
                }
            }

            // Si no se encuentra, intentar con el comando directo (si está en PATH)
            return 'wkhtmltopdf';
        }

        // Linux/Mac
        return '/usr/local/bin/wkhtmltopdf';
    }
}
