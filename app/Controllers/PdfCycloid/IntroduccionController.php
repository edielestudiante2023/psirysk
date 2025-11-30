<?php

namespace App\Controllers\PdfCycloid;

/**
 * Controller para las páginas introductorias del informe PDF Cycloid
 * Incluye: Introducción, Marco Conceptual, Marco Legal, Objetivos, Metodología
 */
class IntroduccionController extends PdfCycloidBaseController
{
    /**
     * Obtiene las estadísticas de participación
     */
    protected function getParticipationStats($batteryServiceId)
    {
        $db = \Config\Database::connect();

        $query = $db->query("
            SELECT
                COUNT(*) as total,
                SUM(CASE WHEN intralaboral_form_type = 'A' THEN 1 ELSE 0 END) as forma_a,
                SUM(CASE WHEN intralaboral_form_type = 'B' THEN 1 ELSE 0 END) as forma_b
            FROM calculated_results
            WHERE battery_service_id = ?
        ", [$batteryServiceId]);

        return $query->getRowArray();
    }

    /**
     * Renderiza todas las páginas introductorias (para el PDF completo)
     */
    public function render($batteryServiceId)
    {
        $this->initializeData($batteryServiceId);

        // Obtener estadísticas de participación
        $stats = $this->getParticipationStats($batteryServiceId);

        $html = '';

        // Página 1: Introducción + Marco Conceptual
        $html .= $this->renderView('pdfcycloid/introduccion/introduccion', [
            'totalParticipantes' => $stats['total'] ?? 0,
            'formaA' => $stats['forma_a'] ?? 0,
            'formaB' => $stats['forma_b'] ?? 0,
        ]);

        // Página 2: Condiciones Intralaborales
        $html .= $this->renderView('pdfcycloid/introduccion/condiciones_intralaborales');

        // Página 3: Condiciones Extralaborales + Individuales
        $html .= $this->renderView('pdfcycloid/introduccion/condiciones_extralaborales');

        // Página 4-5: Marco Legal
        $html .= $this->renderView('pdfcycloid/introduccion/marco_legal');

        // Página 6: Objetivos
        $html .= $this->renderView('pdfcycloid/introduccion/objetivos');

        // Página 7-8: Metodología
        $html .= $this->renderView('pdfcycloid/introduccion/metodologia', [
            'totalParticipantes' => $stats['total'] ?? 0,
            'formaA' => $stats['forma_a'] ?? 0,
            'formaB' => $stats['forma_b'] ?? 0,
        ]);

        return $html;
    }

    /**
     * Preview de todas las páginas introductorias
     */
    public function preview($batteryServiceId)
    {
        $html = $this->render($batteryServiceId);

        return view('pdfcycloid/_partials/preview_wrapper', [
            'content' => $html,
            'pageTitle' => 'Preview: Introducción y Marco Teórico (Cycloid)',
            'batteryServiceId' => $batteryServiceId
        ]);
    }

    /**
     * Descarga la introducción completa como PDF usando DomPDF
     */
    public function download($batteryServiceId)
    {
        $html = $this->render($batteryServiceId);

        // CSS nativo para DomPDF - Normas ICONTEC
        // Letter: 612pt x 792pt (8.5" x 11")
        // Márgenes: Izq 4cm(113pt), Der 2cm(57pt), Sup/Inf 3cm(85pt)
        // Área útil: 612 - 113 - 57 = 442pt
        $css = '
            @page {
                margin: 85pt 57pt 85pt 113pt;
            }

            body {
                font-family: DejaVu Sans, Arial, sans-serif;
                font-size: 10pt;
                line-height: 1.5;
                color: #333;
            }

            h2 {
                font-size: 14pt;
                color: #006699;
                text-align: center;
                margin: 0 0 15pt 0;
                padding-bottom: 5pt;
                border-bottom: 2pt solid #006699;
            }

            h3 {
                font-size: 11pt;
                color: #006699;
                margin: 15pt 0 10pt 0;
            }

            p {
                text-align: justify;
                margin: 0 0 10pt 0;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin: 10pt 0;
            }

            th, td {
                border: 1pt solid #333;
                padding: 6pt;
                text-align: left;
                vertical-align: top;
            }

            th {
                background-color: #006699;
                color: white;
                font-weight: bold;
            }

            ul, ol {
                margin: 5pt 0 10pt 20pt;
                padding: 0;
            }

            li {
                margin-bottom: 5pt;
            }

            blockquote {
                margin: 10pt 15pt;
                padding: 8pt 10pt;
                border-left: 3pt solid #006699;
                background-color: #f0f0f0;
                font-style: italic;
            }

            .page-break {
                page-break-after: always;
            }

            .text-center {
                text-align: center;
            }

            .bg-light {
                background-color: #f5f5f5;
            }

            .bg-green { background-color: #4CAF50; color: white; }
            .bg-lime { background-color: #8BC34A; color: white; }
            .bg-yellow { background-color: #FFEB3B; color: #333; }
            .bg-orange { background-color: #FF9800; color: white; }
            .bg-red { background-color: #F44336; color: white; }
        ';

        $fullHtml = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>' . $css . '</style>
        </head>
        <body>
            <div class="content-wrapper">' . $html . '</div>
        </body>
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
        $filename = 'introduccion_cycloid_' . $batteryServiceId . '.pdf';

        // Enviar al navegador
        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($dompdf->output());
    }
}
