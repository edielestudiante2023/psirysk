<?php

namespace App\Controllers\PdfCycloid;

/**
 * Controller para la página de Portada del informe PDF Cycloid
 */
class PortadaController extends PdfCycloidBaseController
{
    /**
     * Títulos según tipo de informe
     */
    protected $reportTitles = [
        'completo' => 'INFORME DE BATERÍA DE RIESGO PSICOSOCIAL',
        'ejecutivo' => 'INFORME EJECUTIVO DE BATERÍA DE RIESGO PSICOSOCIAL',
        'default' => 'EVALUACIÓN DE FACTORES DE RIESGO PSICOSOCIAL',
    ];

    /**
     * Renderiza la página de portada
     *
     * @param int $batteryServiceId
     * @param string $reportType Tipo de informe: 'completo', 'ejecutivo', o 'default'
     * @return string HTML de la portada
     */
    public function render($batteryServiceId, $reportType = 'default')
    {
        $this->initializeData($batteryServiceId);

        $title = $this->reportTitles[$reportType] ?? $this->reportTitles['default'];

        return $this->renderView('pdfcycloid/portada', [
            'reportTitle' => $title,
            'reportType' => $reportType,
            'applicationDate' => $this->formatDate($this->companyData['application_date'] ?? date('Y-m-d'), 'short'),
            'city' => $this->companyData['city'] ?? 'Bogotá D.C.',
        ]);
    }

    /**
     * Preview de la portada en navegador
     */
    public function preview($batteryServiceId)
    {
        $html = $this->render($batteryServiceId);

        return view('pdfcycloid/_partials/preview_wrapper', [
            'content' => $html,
            'pageTitle' => 'Preview: Portada (Cycloid)',
            'batteryServiceId' => $batteryServiceId
        ]);
    }

    /**
     * Descarga la portada como PDF usando DomPDF
     */
    public function download($batteryServiceId)
    {
        $html = $this->render($batteryServiceId);

        // CSS específico para PDF con márgenes centrados
        $css = '
            @page {
                margin: 20mm 25mm 20mm 25mm;
            }

            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                font-family: Arial, Helvetica, sans-serif;
                font-size: 10pt;
                line-height: 1.3;
                color: #333;
            }

            .pdf-page {
                width: 100%;
            }

            .pdf-page.portada {
                text-align: center;
                padding-top: 30mm;
            }

            .pdf-header {
                text-align: center;
                margin-bottom: 30px;
            }

            .pdf-header img {
                display: inline-block;
                margin: 0 30px;
                vertical-align: middle;
            }

            .portada-title {
                font-size: 20pt;
                font-weight: bold;
                color: #0066cc;
                margin-bottom: 60px;
            }

            .portada-company-label {
                font-size: 11pt;
                font-weight: bold;
                margin-bottom: 10px;
            }

            .portada-company-name {
                font-size: 13pt;
                font-style: italic;
                margin-bottom: 40px;
            }

            .portada-company-logo {
                margin: 15px 0 30px 0;
                text-align: center;
            }

            .portada-consultant-label {
                font-size: 11pt;
                font-weight: bold;
                margin-bottom: 10px;
            }

            .portada-consultant-name {
                font-size: 14pt;
                font-weight: bold;
                margin-bottom: 5px;
            }

            .portada-consultant-title {
                font-size: 10pt;
                font-style: italic;
                margin-bottom: 5px;
            }

            .portada-consultant-license {
                font-size: 9pt;
                color: #555;
                margin-bottom: 40px;
            }

            .portada-date {
                font-size: 11pt;
                margin-top: 40px;
            }
        ';

        $fullHtml = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>' . $css . '</style>
        </head>
        <body>' . $html . '</body>
        </html>';

        // Configurar DomPDF
        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($fullHtml);
        $dompdf->setPaper('Letter', 'portrait');
        $dompdf->render();

        // Nombre del archivo
        $filename = 'portada_cycloid_' . $batteryServiceId . '.pdf';

        // Enviar al navegador
        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($dompdf->output());
    }
}
