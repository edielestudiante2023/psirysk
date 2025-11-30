<?php

namespace App\Controllers\PdfNativo;

use App\Controllers\BaseController;

/**
 * Controlador base para PDF Nativo usando DomPDF
 * Diseñado específicamente para las limitaciones de DomPDF
 */
class PdfNativoBaseController extends BaseController
{
    protected $batteryServiceId;
    protected $companyData;
    protected $consultantData;

    /**
     * Colores por nivel de riesgo
     */
    protected $riskColors = [
        'sin_riesgo' => '#4CAF50',
        'riesgo_bajo' => '#8BC34A',
        'riesgo_medio' => '#FFEB3B',
        'riesgo_alto' => '#FF9800',
        'riesgo_muy_alto' => '#F44336',
    ];

    /**
     * Nombres legibles de niveles
     */
    protected $riskNames = [
        'sin_riesgo' => 'Sin Riesgo',
        'riesgo_bajo' => 'Riesgo Bajo',
        'riesgo_medio' => 'Riesgo Medio',
        'riesgo_alto' => 'Riesgo Alto',
        'riesgo_muy_alto' => 'Riesgo Muy Alto',
    ];

    /**
     * CSS nativo para DomPDF - Normas ICONTEC
     * Letter: 612pt x 792pt
     * Márgenes: Izq 4cm(113pt), Der 2cm(57pt), Sup/Inf 3cm(85pt)
     */
    protected function getCss()
    {
        return '
            @page {
                margin: 85pt 57pt 85pt 113pt;
            }

            body {
                font-family: DejaVu Sans, sans-serif;
                font-size: 10pt;
                line-height: 1.4;
                color: #333;
            }

            h1 {
                font-size: 16pt;
                color: #006699;
                text-align: center;
                margin: 0 0 20pt 0;
                padding-bottom: 8pt;
                border-bottom: 2pt solid #006699;
            }

            h2 {
                font-size: 14pt;
                color: #006699;
                text-align: center;
                margin: 0 0 15pt 0;
                padding-bottom: 5pt;
                border-bottom: 1pt solid #006699;
            }

            h3 {
                font-size: 12pt;
                color: #006699;
                margin: 15pt 0 10pt 0;
            }

            h4 {
                font-size: 11pt;
                color: #333;
                margin: 10pt 0 8pt 0;
            }

            p {
                text-align: justify;
                margin: 0 0 8pt 0;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin: 10pt 0;
            }

            th, td {
                border: 1pt solid #333;
                padding: 5pt;
                text-align: left;
                vertical-align: top;
                font-size: 9pt;
            }

            th {
                background-color: #006699;
                color: white;
                font-weight: bold;
                text-align: center;
            }

            ul, ol {
                margin: 5pt 0 10pt 15pt;
                padding: 0;
            }

            li {
                margin-bottom: 4pt;
            }

            .page-break {
                page-break-after: always;
            }

            .text-center { text-align: center; }
            .text-right { text-align: right; }
            .text-left { text-align: left; }
            .text-justify { text-align: justify; }

            .bold { font-weight: bold; }
            .italic { font-style: italic; }

            .bg-header { background-color: #006699; color: white; }
            .bg-light { background-color: #f5f5f5; }
            .bg-white { background-color: #ffffff; }

            .bg-sin-riesgo { background-color: #4CAF50; color: white; }
            .bg-riesgo-bajo { background-color: #8BC34A; color: white; }
            .bg-riesgo-medio { background-color: #FFEB3B; color: #333; }
            .bg-riesgo-alto { background-color: #FF9800; color: white; }
            .bg-riesgo-muy-alto { background-color: #F44336; color: white; }

            .small { font-size: 8pt; }
            .normal { font-size: 10pt; }
            .large { font-size: 12pt; }

            .mt-10 { margin-top: 10pt; }
            .mt-20 { margin-top: 20pt; }
            .mb-10 { margin-bottom: 10pt; }
            .mb-20 { margin-bottom: 20pt; }

            .portada-titulo {
                font-size: 18pt;
                color: #006699;
                text-align: center;
                margin: 40pt 0;
                font-weight: bold;
            }

            .portada-empresa {
                font-size: 14pt;
                text-align: center;
                margin: 20pt 0;
            }

            .firma-container {
                margin-top: 60pt;
                text-align: center;
            }

            .firma-linea {
                border-top: 1pt solid #333;
                width: 200pt;
                margin: 0 auto;
                padding-top: 5pt;
            }

            /* Estilos para páginas de dominio/dimensión */
            .title-domain {
                font-size: 14pt;
                font-weight: bold;
                color: #006699;
                text-align: center;
                margin: 0 0 4pt 0;
            }

            .title-sub {
                font-size: 10pt;
                color: #666;
                text-align: center;
                margin-bottom: 10pt;
            }

            .definition-box {
                background-color: #f9f9f9;
                border: 1pt solid #ddd;
                padding: 8pt;
                margin-bottom: 10pt;
                font-size: 8.5pt;
            }

            .definition-label {
                font-weight: bold;
                color: #006699;
                margin-bottom: 3pt;
            }
        ';
    }

    /**
     * Inicializa datos comunes
     */
    protected function initializeData($batteryServiceId)
    {
        $this->batteryServiceId = $batteryServiceId;
        $this->companyData = $this->loadCompanyData($batteryServiceId);
        $this->consultantData = $this->loadConsultantData($batteryServiceId);
    }

    /**
     * Carga datos de la empresa
     */
    protected function loadCompanyData($batteryServiceId)
    {
        $db = \Config\Database::connect();
        $query = $db->query("
            SELECT
                c.name as company_name,
                c.nit,
                c.address,
                c.city,
                c.phone,
                c.contact_email,
                c.logo_path,
                bs.service_date,
                bs.created_at
            FROM battery_services bs
            JOIN companies c ON bs.company_id = c.id
            WHERE bs.id = ?
        ", [$batteryServiceId]);

        return $query->getRowArray() ?? [];
    }

    /**
     * Carga datos del consultor
     */
    protected function loadConsultantData($batteryServiceId)
    {
        $db = \Config\Database::connect();
        $query = $db->query("
            SELECT
                con.nombre_completo,
                con.cargo,
                con.licencia_sst,
                con.email,
                con.telefono
            FROM battery_services bs
            LEFT JOIN consultants con ON bs.consultant_id = con.id
            WHERE bs.id = ?
        ", [$batteryServiceId]);

        return $query->getRowArray() ?? [];
    }

    /**
     * Renderiza una vista y retorna el HTML
     */
    protected function renderView($viewName, $data = [])
    {
        $data['company'] = $this->companyData;
        $data['consultant'] = $this->consultantData;
        $data['riskColors'] = $this->riskColors;
        $data['riskNames'] = $this->riskNames;

        return view($viewName, $data);
    }

    /**
     * Genera el PDF con DomPDF
     */
    protected function generatePdf($html, $filename)
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

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($fullHtml);
        $dompdf->setPaper('Letter', 'portrait');
        $dompdf->render();

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($dompdf->output());
    }

    /**
     * Formatea fecha
     */
    protected function formatDate($date)
    {
        if (empty($date)) return 'No especificada';
        $months = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
        $timestamp = strtotime($date);
        $day = date('d', $timestamp);
        $month = $months[date('n', $timestamp) - 1];
        $year = date('Y', $timestamp);
        return "$day de $month de $year";
    }

    /**
     * Obtiene clase CSS para nivel de riesgo
     */
    protected function getRiskClass($nivel)
    {
        $classes = [
            'sin_riesgo' => 'bg-sin-riesgo',
            'riesgo_bajo' => 'bg-riesgo-bajo',
            'riesgo_medio' => 'bg-riesgo-medio',
            'riesgo_alto' => 'bg-riesgo-alto',
            'riesgo_muy_alto' => 'bg-riesgo-muy-alto',
        ];
        return $classes[$nivel] ?? 'bg-light';
    }

    /**
     * Obtiene acción recomendada por nivel
     */
    protected function getRiskAction($nivel)
    {
        $actions = [
            'sin_riesgo' => 'Mantener condiciones actuales',
            'riesgo_bajo' => 'Acciones preventivas de mantenimiento',
            'riesgo_medio' => 'Observación y acciones preventivas',
            'riesgo_alto' => 'Intervención en marco de vigilancia epidemiológica',
            'riesgo_muy_alto' => 'Intervención inmediata en marco de vigilancia epidemiológica',
        ];
        return $actions[$nivel] ?? '';
    }
}
