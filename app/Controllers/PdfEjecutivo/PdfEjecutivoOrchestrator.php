<?php

namespace App\Controllers\PdfEjecutivo;

use ZipArchive;

/**
 * Orquestador del Informe Ejecutivo PDF Completo
 *
 * Une todas las secciones del informe de batería de riesgo psicosocial:
 * 1. Portada
 * 2. Tabla de Contenido
 * 3. Introducción
 * 4. Sociodemográficos
 * 5. Mapas de Calor
 * 6. Totales Intralaborales
 * 7. Dominios Intralaborales
 * 8. Dimensiones Intralaborales
 * 9. Dimensiones Extralaborales
 * 10. Estrés (con firma del consultor)
 *
 * Proporciona:
 * - Preview HTML completo en navegador
 * - Descarga ZIP con PDFs individuales (evita límite de memoria)
 */
class PdfEjecutivoOrchestrator extends PdfEjecutivoBaseController
{
    /**
     * Orden de las secciones del informe
     * Cada entrada: [nombre_clase, titulo_seccion]
     */
    protected $secciones = [
        ['PortadaController', 'Portada'],
        ['ContenidoController', 'Tabla de Contenido'],
        ['IntroduccionController', 'Introducción'],
        ['SociodemograficosController', 'Datos Sociodemográficos'],
        ['MapasCalorController', 'Mapas de Calor'],
        ['TotalesIntralaboralesController', 'Totales Intralaborales'],
        ['DominiosIntralaboralesController', 'Dominios Intralaborales'],
        ['DimensionesIntralaboralesController', 'Dimensiones Intralaborales'],
        ['DimensionesExtralaboralesController', 'Dimensiones Extralaborales'],
        ['EstresEjecutivoController', 'Evaluación del Estrés'],
    ];

    /**
     * Preview HTML del informe completo en navegador (desarrollo)
     */
    public function preview($batteryServiceId)
    {
        // Verificar acceso
        $accessCheck = $this->checkPdfAccess($batteryServiceId);
        if ($accessCheck !== null) {
            return $accessCheck;
        }

        $this->initializeData($batteryServiceId);
        $html = $this->renderAllSections($batteryServiceId);

        return $this->generatePreview($html, 'Informe de Batería Completo - Preview');
    }

    /**
     * Descarga ZIP con todas las secciones del informe como PDFs individuales
     * Esto evita el límite de memoria al generar un PDF muy grande
     */
    public function download($batteryServiceId)
    {
        // Verificar acceso
        $accessCheck = $this->checkPdfAccess($batteryServiceId);
        if ($accessCheck !== null) {
            return $accessCheck;
        }

        $this->initializeData($batteryServiceId);

        // Preparar nombres de archivo
        $companyName = preg_replace('/[^a-zA-Z0-9]/', '_', $this->companyData['company_name'] ?? 'Empresa');
        $fecha = date('Ymd');

        // Crear archivo ZIP temporal
        $zipPath = WRITEPATH . "temp/Informe_Bateria_{$companyName}_{$fecha}.zip";

        // Asegurar que existe el directorio temp
        if (!is_dir(WRITEPATH . 'temp')) {
            mkdir(WRITEPATH . 'temp', 0755, true);
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return $this->response->setStatusCode(500)->setBody('No se pudo crear el archivo ZIP');
        }

        // Generar cada sección como PDF individual y agregar al ZIP
        $index = 1;
        foreach ($this->secciones as $seccion) {
            $className = $seccion[0];
            $titulo = $seccion[1];

            $controllerClass = "App\\Controllers\\PdfEjecutivo\\{$className}";

            if (class_exists($controllerClass)) {
                try {
                    $controller = new $controllerClass();
                    $sectionHtml = $controller->render($batteryServiceId);

                    // Generar PDF de esta sección
                    $pdfContent = $this->generateSectionPdf($sectionHtml);

                    // Nombre del archivo: 01_Portada.pdf, 02_Contenido.pdf, etc.
                    $safeTitle = preg_replace('/[^a-zA-Z0-9áéíóúñÁÉÍÓÚÑ\s]/', '', $titulo);
                    $safeTitle = str_replace(' ', '_', $safeTitle);
                    $pdfFilename = sprintf('%02d_%s.pdf', $index, $safeTitle);

                    $zip->addFromString($pdfFilename, $pdfContent);
                } catch (\Exception $e) {
                    // Si falla una sección, continuar con las demás
                    log_message('error', "Error generando PDF sección {$titulo}: " . $e->getMessage());
                }
            }

            $index++;
        }

        $zip->close();

        // Leer el archivo ZIP y enviarlo
        $zipContent = file_get_contents($zipPath);

        // Eliminar archivo temporal
        @unlink($zipPath);

        $zipFilename = "Informe_Bateria_Completo_{$companyName}_{$fecha}.zip";

        return $this->response
            ->setHeader('Content-Type', 'application/zip')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $zipFilename . '"')
            ->setHeader('Content-Length', strlen($zipContent))
            ->setBody($zipContent);
    }

    /**
     * Genera PDF de una sección individual (retorna contenido binario)
     */
    protected function generateSectionPdf($html)
    {
        $fullHtml = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>' . $this->getCss() . '</style>
</head>
<body>' . $html . '</body>
</html>';

        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isFontSubsettingEnabled', true);
        $options->set('chroot', FCPATH);

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($fullHtml);
        $dompdf->setPaper('Letter', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    /**
     * Renderiza todas las secciones del informe
     */
    protected function renderAllSections($batteryServiceId)
    {
        $html = '';
        $isFirst = true;

        foreach ($this->secciones as $seccion) {
            $className = $seccion[0];
            $titulo = $seccion[1];

            // Agregar salto de página entre secciones (excepto la primera)
            if (!$isFirst) {
                $html .= '<div class="page-break"></div>';
            }

            // Instanciar el controlador y renderizar
            $controllerClass = "App\\Controllers\\PdfEjecutivo\\{$className}";

            if (class_exists($controllerClass)) {
                $controller = new $controllerClass();
                $sectionHtml = $controller->render($batteryServiceId);
                $html .= $sectionHtml;
            } else {
                // Si no existe el controlador, mostrar placeholder
                $html .= $this->renderSeccionNoDisponible($titulo);
            }

            $isFirst = false;
        }

        return $html;
    }

    /**
     * Renderiza un placeholder para secciones no disponibles
     */
    protected function renderSeccionNoDisponible($titulo)
    {
        return '
<div style="background: #fff3cd; border: 1pt solid #ffc107; padding: 20pt; text-align: center; margin: 50pt 0;">
    <p style="font-size: 14pt; color: #856404; margin: 0;">
        <strong>Sección: ' . esc($titulo) . '</strong><br>
        <span style="font-size: 10pt;">No disponible</span>
    </p>
</div>';
    }

    /**
     * Método render() para compatibilidad con el patrón
     * (Este controlador no necesita ser llamado desde otro orquestador,
     * pero lo implementamos por consistencia)
     */
    public function render($batteryServiceId)
    {
        return $this->renderAllSections($batteryServiceId);
    }
}
