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

        // Preparar encabezado con logos de la plataforma
        $headerLogosHtml = $this->renderHeaderLogos();

        // Preparar logo de la empresa si existe
        $companyLogoHtml = '';
        $logoPath = $this->getLogoPath();
        if ($logoPath) {
            // Logo empresa: 300x150 px máximo
            $logoDataUri = $this->imageToDataUri($logoPath, 300, 150, 85);
            if ($logoDataUri) {
                $companyLogoHtml = '<div style="text-align: center; margin: 20pt 0;">
                    <img src="' . $logoDataUri . '" style="max-width: 140pt; max-height: 70pt;" alt="Logo Empresa">
                </div>';
            }
        }

        // Sin logo en el pie de página
        $footerLogoHtml = '';

        // Portada compacta que cabe en una página con márgenes ICONTEC
        $html = '
<!-- PORTADA -->
<div style="position: relative; min-height: 600pt;">
' . $headerLogosHtml . '

<div style="text-align: center; padding-top: 15pt;">

    ' . $companyLogoHtml . '

    <div style="font-size: 20pt; color: #006699; font-weight: bold; margin: 20pt 0 12pt 0; line-height: 1.2;">
        INFORME DE BATERÍA<br>DE RIESGO PSICOSOCIAL
    </div>

    <div style="font-size: 11pt; color: #666; margin-bottom: 25pt;">
        Resolución 2764 de 2022<br>
        Ministerio del Trabajo de Colombia
    </div>

    <div style="font-size: 14pt; margin: 20pt 0;">
        <strong>' . esc($company['company_name'] ?? 'EMPRESA') . '</strong><br>
        <span style="font-size: 10pt;">NIT: ' . esc($company['nit'] ?? 'No registrado') . '</span>
    </div>

    <div style="margin: 20pt 0; font-size: 10pt;">
        <p style="margin: 3pt 0;">' . esc($company['city'] ?? 'Colombia') . '</p>
        <p style="margin: 3pt 0;">' . $fecha . '</p>
    </div>

    <div style="margin-top: 30pt; font-size: 10pt;">
        <p style="margin-bottom: 3pt; color: #666;">Elaborado por:</p>
        <p style="margin: 3pt 0;"><strong>' . esc($consultant['nombre_completo'] ?? 'Consultor') . '</strong></p>
        <p style="margin: 3pt 0; font-size: 9pt; color: #666;">' . esc($consultant['cargo'] ?? 'Psicólogo Especialista en SST') . '</p>
        <p style="margin: 3pt 0; font-size: 9pt; color: #666;">Licencia SST: ' . esc($consultant['licencia_sst'] ?? 'No registrada') . '</p>
    </div>

</div>

' . $footerLogoHtml . '
</div>
';

        return $html;
    }

    /**
     * Renderiza el encabezado con los logos de la plataforma (3 logos centrados)
     */
    protected function renderHeaderLogos()
    {
        $logos = [
            'cycloidgrissinfondo.png',
            'logo_psirysk.png',
            'logo_rps.png',
        ];

        $logosHtml = '';
        foreach ($logos as $logo) {
            $logoPath = FCPATH . 'images/logos/' . $logo;
            // Logos pequeños del encabezado: 150x60 px máximo
            $logoDataUri = $this->imageToDataUri($logoPath, 150, 60, 80);
            if ($logoDataUri) {
                $logosHtml .= '<td style="width: 33%; text-align: center; border: none; padding: 5pt;">
                    <img src="' . $logoDataUri . '" style="max-height: 40pt; max-width: 100pt;" alt="Logo">
                </td>';
            }
        }

        return '
<!-- Encabezado con logos de plataforma -->
<table style="width: 100%; border-collapse: collapse; margin-bottom: 10pt; border-bottom: 1pt solid #ddd; padding-bottom: 8pt;">
    <tr>
        ' . $logosHtml . '
    </tr>
</table>';
    }

    /**
     * Renderiza el pie de página con logo Psicloid Method
     */
    protected function renderFooterLogo()
    {
        $logoPath = FCPATH . 'images/logos/logo_psicloid_method.png';
        // Logo footer: 600x200 px para que se vea bien al 3x
        $logoDataUri = $this->imageToDataUri($logoPath, 600, 200, 85);

        if (!$logoDataUri) {
            return '';
        }

        return '
<!-- Pie de página con logo Psicloid Method -->
<div style="position: absolute; bottom: 10pt; left: 0; right: 0; text-align: center; border-top: 1pt solid #ddd; padding-top: 10pt;">
    <img src="' . $logoDataUri . '" style="max-height: 150pt; max-width: 540pt;" alt="Psicloid Method">
</div>';
    }
}
