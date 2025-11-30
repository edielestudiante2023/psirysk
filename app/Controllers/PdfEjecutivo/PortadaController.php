<?php

namespace App\Controllers\PdfEjecutivo;

/**
 * Controlador de Portada para el Informe Ejecutivo PDF
 * Sección 1: Página de portada profesional
 */
class PortadaController extends PdfEjecutivoBaseController
{
    /**
     * Preview de la portada en navegador (desarrollo)
     */
    public function preview($batteryServiceId)
    {
        $this->initializeData($batteryServiceId);
        $html = $this->render($batteryServiceId);

        return $this->generatePreview($html, 'Preview: Portada');
    }

    /**
     * Descarga PDF solo de la portada
     */
    public function download($batteryServiceId)
    {
        $this->initializeData($batteryServiceId);
        $html = $this->render($batteryServiceId);
        $companyName = preg_replace('/[^a-zA-Z0-9]/', '_', $this->companyData['company_name'] ?? 'Empresa');

        return $this->generatePdf($html, "Portada_{$companyName}.pdf");
    }

    /**
     * Renderiza el HTML de la portada
     * Este método es público para ser usado por el Orquestador
     */
    public function render($batteryServiceId)
    {
        if (empty($this->companyData)) {
            $this->initializeData($batteryServiceId);
        }

        $company = $this->companyData;
        $consultant = $this->consultantData;
        $fecha = $this->formatDate($company['service_date'] ?? date('Y-m-d'));

        // Preparar logo si existe (tamaño reducido para portada)
        $logoHtml = '';
        $logoPath = $this->getLogoPath();
        if ($logoPath) {
            $logoDataUri = $this->imageToDataUri($logoPath);
            if ($logoDataUri) {
                $logoHtml = '<div style="text-align: center; margin-bottom: 15pt;">
                    <img src="' . $logoDataUri . '" style="max-width: 120pt; max-height: 60pt;" alt="Logo">
                </div>';
            }
        }

        // Portada compacta que cabe en una página con márgenes ICONTEC
        // Área útil aprox: 442pt ancho x 622pt alto (Letter - márgenes)
        $html = '
<!-- PORTADA -->
<div style="text-align: center; padding-top: 30pt;">

    ' . $logoHtml . '

    <div style="font-size: 20pt; color: #006699; font-weight: bold; margin: 25pt 0 15pt 0; line-height: 1.2;">
        INFORME DE BATERÍA<br>DE RIESGO PSICOSOCIAL
    </div>

    <div style="font-size: 11pt; color: #666; margin-bottom: 30pt;">
        Resolución 2764 de 2022<br>
        Ministerio del Trabajo de Colombia
    </div>

    <div style="font-size: 14pt; margin: 25pt 0;">
        <strong>' . esc($company['company_name'] ?? 'EMPRESA') . '</strong><br>
        <span style="font-size: 10pt;">NIT: ' . esc($company['nit'] ?? 'No registrado') . '</span>
    </div>

    <div style="margin: 25pt 0; font-size: 10pt;">
        <p style="margin: 3pt 0;">' . esc($company['city'] ?? 'Colombia') . '</p>
        <p style="margin: 3pt 0;">' . $fecha . '</p>
    </div>

    <div style="margin-top: 40pt; font-size: 10pt;">
        <p style="margin-bottom: 3pt; color: #666;">Elaborado por:</p>
        <p style="margin: 3pt 0;"><strong>' . esc($consultant['nombre_completo'] ?? 'Consultor') . '</strong></p>
        <p style="margin: 3pt 0; font-size: 9pt; color: #666;">' . esc($consultant['cargo'] ?? 'Psicólogo Especialista en SST') . '</p>
        <p style="margin: 3pt 0; font-size: 9pt; color: #666;">Licencia SST: ' . esc($consultant['licencia_sst'] ?? 'No registrada') . '</p>
    </div>

</div>
';

        return $html;
    }
}
