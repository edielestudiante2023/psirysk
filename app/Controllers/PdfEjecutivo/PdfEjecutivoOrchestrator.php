<?php

namespace App\Controllers\PdfEjecutivo;

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
 * - Descarga PDF del informe completo
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
        $this->initializeData($batteryServiceId);
        $html = $this->renderAllSections($batteryServiceId);

        return $this->generatePreview($html, 'Informe Ejecutivo Completo - Preview');
    }

    /**
     * Descarga PDF del informe completo
     */
    public function download($batteryServiceId)
    {
        $this->initializeData($batteryServiceId);
        $html = $this->renderAllSections($batteryServiceId);

        $companyName = preg_replace('/[^a-zA-Z0-9]/', '_', $this->companyData['company_name'] ?? 'Empresa');
        $fecha = date('Ymd');
        $filename = "Informe_Ejecutivo_{$companyName}_{$fecha}.pdf";

        return $this->generatePdf($html, $filename);
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
