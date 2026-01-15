<?php

namespace App\Controllers\PdfEjecutivo;

/**
 * Orquestador del Informe Ejecutivo PDF
 *
 * Este informe es una versión resumida que incluye solo:
 * 1. Portada
 * 2. Mapas de Calor (resumen visual de resultados)
 * 3. Recomendaciones y Planes de Acción
 *
 * Es diferente al "Informe de Batería de Riesgo Psicosocial" completo
 * que incluye todas las secciones detalladas.
 */
class InformeEjecutivoOrchestrator extends PdfEjecutivoBaseController
{
    /**
     * Secciones del Informe Ejecutivo
     */
    protected $secciones = [
        ['PortadaController', 'Portada'],
        ['MapasCalorController', 'Mapas de Calor'],
        // ['RecomendacionesPlanesController', 'Recomendaciones y Planes de Acción'], // Removido del PDF ejecutivo
    ];

    /**
     * Preview HTML del informe ejecutivo en navegador
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

        return $this->generatePreview($html, 'Informe Ejecutivo - Preview');
    }

    /**
     * Descarga PDF del informe ejecutivo
     */
    public function download($batteryServiceId)
    {
        // Verificar acceso
        $accessCheck = $this->checkPdfAccess($batteryServiceId);
        if ($accessCheck !== null) {
            return $accessCheck;
        }

        $this->initializeData($batteryServiceId);
        $html = $this->renderAllSections($batteryServiceId);

        $companyName = preg_replace('/[^a-zA-Z0-9]/', '_', $this->companyData['company_name'] ?? 'Empresa');
        $fecha = date('Ymd');
        $filename = "Informe_Ejecutivo_{$companyName}_{$fecha}.pdf";

        return $this->generatePdf($html, $filename);
    }

    /**
     * Renderiza todas las secciones del informe ejecutivo
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
     */
    public function render($batteryServiceId)
    {
        return $this->renderAllSections($batteryServiceId);
    }
}
