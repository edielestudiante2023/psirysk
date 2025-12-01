<?php

namespace App\Controllers\PdfEjecutivo;

use App\Controllers\BaseController;
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Controlador base para PdfEjecutivo usando SOLO DomPDF
 * Diseñado específicamente para las limitaciones de DomPDF
 * Márgenes ICONTEC para documentos colombianos
 */
class PdfEjecutivoBaseController extends BaseController
{
    protected $batteryServiceId;
    protected $companyData;
    protected $consultantData;

    /**
     * Colores por nivel de riesgo (Resolución 2404/2019)
     */
    protected $riskColors = [
        'sin_riesgo'      => '#4CAF50',  // Verde
        'riesgo_bajo'     => '#8BC34A',  // Verde claro
        'riesgo_medio'    => '#FFEB3B',  // Amarillo
        'riesgo_alto'     => '#FF9800',  // Naranja
        'riesgo_muy_alto' => '#F44336',  // Rojo
    ];

    /**
     * Nombres legibles de niveles
     */
    protected $riskNames = [
        'sin_riesgo'      => 'Sin Riesgo',
        'riesgo_bajo'     => 'Riesgo Bajo',
        'riesgo_medio'    => 'Riesgo Medio',
        'riesgo_alto'     => 'Riesgo Alto',
        'riesgo_muy_alto' => 'Riesgo Muy Alto',
    ];

    /**
     * CSS nativo para DomPDF - Normas ICONTEC
     * Tamaño Letter: 612pt x 792pt
     * Márgenes: Superior 3cm(85pt), Derecho 2cm(57pt), Inferior 3cm(85pt), Izquierdo 4cm(113pt)
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

            /* Títulos */
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

            /* Tablas */
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

            /* Listas */
            ul, ol {
                margin: 5pt 0 10pt 15pt;
                padding: 0;
            }

            li {
                margin-bottom: 4pt;
            }

            /* Page break */
            .page-break {
                page-break-after: always;
            }

            /* Alineación */
            .text-center { text-align: center; }
            .text-right { text-align: right; }
            .text-left { text-align: left; }
            .text-justify { text-align: justify; }

            /* Estilos de texto */
            .bold { font-weight: bold; }
            .italic { font-style: italic; }

            /* Fondos */
            .bg-header { background-color: #006699; color: white; }
            .bg-light { background-color: #f5f5f5; }
            .bg-white { background-color: #ffffff; }

            /* Fondos por nivel de riesgo */
            .bg-sin-riesgo { background-color: #4CAF50; color: white; }
            .bg-riesgo-bajo { background-color: #8BC34A; color: white; }
            .bg-riesgo-medio { background-color: #FFEB3B; color: #333; }
            .bg-riesgo-alto { background-color: #FF9800; color: white; }
            .bg-riesgo-muy-alto { background-color: #F44336; color: white; }

            /* Tamaños de texto */
            .small { font-size: 8pt; }
            .normal { font-size: 10pt; }
            .large { font-size: 12pt; }

            /* Márgenes */
            .mt-10 { margin-top: 10pt; }
            .mt-20 { margin-top: 20pt; }
            .mt-40 { margin-top: 40pt; }
            .mb-10 { margin-bottom: 10pt; }
            .mb-20 { margin-bottom: 20pt; }

            /* Portada */
            .portada-titulo {
                font-size: 24pt;
                color: #006699;
                text-align: center;
                margin: 40pt 0;
                font-weight: bold;
                line-height: 1.3;
            }

            .portada-subtitulo {
                font-size: 14pt;
                color: #666;
                text-align: center;
                margin: 20pt 0;
            }

            .portada-empresa {
                font-size: 16pt;
                text-align: center;
                margin: 30pt 0;
            }

            .portada-consultor {
                font-size: 11pt;
                text-align: center;
                margin-top: 60pt;
            }

            /* Firma */
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

            /* Header de página */
            .header-table {
                width: 100%;
                border-bottom: 2px solid #006699;
                margin-bottom: 8px;
            }

            .header-table td {
                border: none;
                padding: 4px;
            }

            /* Focus box (acciones recomendadas) */
            .focus-box {
                border: 1px solid #006699;
                background-color: #e8f4fc;
                padding: 6px 7px;
                margin-top: 8px;
                font-size: 7.5pt;
            }

            .focus-title {
                font-weight: bold;
                color: #006699;
            }

            /* Texto IA */
            .ai-text-box {
                border-left: 2px solid #2196F3;
                background-color: #e3f2fd;
                padding: 7px 9px;
                margin-top: 9px;
                font-size: 7.5pt;
                text-align: justify;
            }

            .ai-text-title {
                font-weight: bold;
                color: #1976D2;
                margin-bottom: 4px;
            }

            /* Logo centrado */
            .logo-container {
                text-align: center;
                margin: 20pt 0;
            }

            .logo-container img {
                max-width: 180pt;
                max-height: 100pt;
            }
        ';
    }

    /**
     * Inicializa datos comunes para cualquier sección del PDF
     */
    protected function initializeData($batteryServiceId)
    {
        $this->batteryServiceId = $batteryServiceId;
        $this->companyData = $this->loadCompanyData($batteryServiceId);
        $this->consultantData = $this->loadConsultantData($batteryServiceId);
    }

    /**
     * Verificar acceso del usuario al servicio para PDFs
     * Similar a ReportsController::checkAccess()
     * @return mixed null si tiene acceso, RedirectResponse si no
     */
    protected function checkPdfAccess($batteryServiceId)
    {
        // Verificar autenticación
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $userRole = session()->get('role_name');

        // Admin y vendedor NO tienen acceso a PDFs
        if (in_array($userRole, ['admin', 'superadmin', 'comercial'])) {
            return redirect()->to('/dashboard')->with('error', 'No tienes permisos para acceder a esta sección');
        }

        // Obtener información del servicio
        $db = \Config\Database::connect();
        $service = $db->query("
            SELECT bs.*, c.parent_company_id
            FROM battery_services bs
            JOIN companies c ON bs.company_id = c.id
            WHERE bs.id = ?
        ", [$batteryServiceId])->getRowArray();

        if (!$service) {
            return redirect()->to('/dashboard')->with('error', 'Servicio no encontrado');
        }

        // Si es cliente, verificar acceso según rol
        if (in_array($userRole, ['cliente_empresa', 'cliente_gestor'])) {
            $userCompanyId = session()->get('company_id');
            $hasAccess = false;

            if ($userRole === 'cliente_empresa') {
                // Solo puede ver servicios de su propia empresa
                $hasAccess = ($service['company_id'] == $userCompanyId);
            } elseif ($userRole === 'cliente_gestor') {
                // Puede ver servicios de su empresa o de empresas hijas
                if ($service['company_id'] == $userCompanyId) {
                    $hasAccess = true;
                } else {
                    // Verificar si la empresa del servicio es hija de la empresa gestora
                    $hasAccess = ($service['parent_company_id'] == $userCompanyId);
                }
            }

            if (!$hasAccess) {
                return redirect()->to('/dashboard')->with('error', 'No tienes permisos para ver este servicio');
            }

            // Cliente solo puede ver PDFs si el servicio está cerrado o finalizado
            if (!in_array($service['status'], ['cerrado', 'finalizado'])) {
                return redirect()->to('/dashboard')->with('error', 'Los informes PDF estarán disponibles cuando el servicio esté finalizado');
            }

            // IMPORTANTE: Cliente debe completar encuesta de satisfacción antes de descargar PDFs
            if (!$service['satisfaction_survey_completed']) {
                return redirect()->to('/satisfaction/survey/' . $batteryServiceId)
                    ->with('info', 'Para descargar los informes PDF, por favor complete primero la encuesta de satisfacción.');
            }
        }

        // Consultor puede ver todo
        return null;
    }

    /**
     * Carga datos de la empresa desde battery_services + companies
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

        $data = $query->getRowArray() ?? [];

        // Corregir doble codificación UTF-8
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = $this->fixUtf8Encoding($value);
            }
        }

        return $data;
    }

    /**
     * Carga datos del consultor desde battery_services + consultants
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
                con.telefono,
                con.website,
                con.linkedin,
                con.firma_path
            FROM battery_services bs
            LEFT JOIN consultants con ON bs.consultant_id = con.id
            WHERE bs.id = ?
        ", [$batteryServiceId]);

        $data = $query->getRowArray() ?? [];

        // Corregir doble codificación UTF-8
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = $this->fixUtf8Encoding($value);
            }
        }

        return $data;
    }

    /**
     * Genera el PDF con DomPDF
     *
     * @param string $html Contenido HTML del PDF
     * @param string $filename Nombre del archivo de salida
     * @param bool $download Si es true descarga, si es false retorna el output
     * @return mixed Response con PDF o string con contenido
     */
    protected function generatePdf($html, $filename, $download = true)
    {
        $fullHtml = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>' . $this->getCss() . '</style>
</head>
<body>' . $html . '</body>
</html>';

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isFontSubsettingEnabled', true);
        $options->set('chroot', FCPATH);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($fullHtml);
        $dompdf->setPaper('Letter', 'portrait');
        $dompdf->render();

        if ($download) {
            return $this->response
                ->setHeader('Content-Type', 'application/pdf')
                ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->setBody($dompdf->output());
        }

        return $dompdf->output();
    }

    /**
     * Genera preview HTML (sin PDF) para desarrollo
     */
    protected function generatePreview($html, $title = 'Preview PDF')
    {
        $fullHtml = '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>' . esc($title) . '</title>
    <style>
        body {
            background: #e0e0e0;
            margin: 0;
            padding: 20px;
        }
        .page-container {
            width: 612px; /* Letter width */
            margin: 0 auto;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.3);
            padding: 85px 57px 85px 113px; /* ICONTEC margins */
            box-sizing: border-box;
            min-height: 792px;
        }
        ' . $this->getCss() . '
    </style>
</head>
<body>
    <div class="page-container">
        ' . $html . '
    </div>
</body>
</html>';

        // Asegurar codificación UTF-8
        $fullHtml = mb_convert_encoding($fullHtml, 'UTF-8', 'auto');

        return $this->response
            ->setHeader('Content-Type', 'text/html; charset=UTF-8')
            ->setBody($fullHtml);
    }

    // ==========================================
    // HELPERS
    // ==========================================

    /**
     * Corrige problemas de codificación UTF-8
     * Reemplaza caracteres mal codificados comunes en español
     */
    protected function fixUtf8Encoding($string)
    {
        if (empty($string)) {
            return $string;
        }

        // Reemplazar el caracter ß (0xC3 interpretado como Latin1) por á
        // Este es el caso específico de "Bogotß" -> "Bogotá"
        $fixed = str_replace(chr(0xDF), chr(0xE1), $string);

        // También intentar con el patrón de doble codificación común
        $fixed = str_replace(
            ["\xC3\x83\xC2\xA1", "\xC3\x83\xC2\xA9", "\xC3\x83\xC2\xAD", "\xC3\x83\xC2\xB3", "\xC3\x83\xC2\xBA", "\xC3\x83\xC2\xB1"],
            ["\xC3\xA1", "\xC3\xA9", "\xC3\xAD", "\xC3\xB3", "\xC3\xBA", "\xC3\xB1"],
            $fixed
        );

        return $fixed;
    }

    /**
     * Formatea fecha al estilo colombiano: "29 de noviembre de 2025"
     */
    protected function formatDate($date)
    {
        if (empty($date)) {
            return 'No especificada';
        }

        $months = [
            'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
            'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'
        ];

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
            'sin_riesgo'      => 'bg-sin-riesgo',
            'riesgo_bajo'     => 'bg-riesgo-bajo',
            'riesgo_medio'    => 'bg-riesgo-medio',
            'riesgo_alto'     => 'bg-riesgo-alto',
            'riesgo_muy_alto' => 'bg-riesgo-muy-alto',
        ];
        return $classes[$nivel] ?? 'bg-light';
    }

    /**
     * Obtiene color hex para nivel de riesgo
     */
    protected function getRiskColor($nivel)
    {
        return $this->riskColors[$nivel] ?? '#999999';
    }

    /**
     * Obtiene nombre legible del nivel de riesgo
     */
    protected function getRiskName($nivel)
    {
        return $this->riskNames[$nivel] ?? $nivel;
    }

    /**
     * Obtiene acción recomendada según nivel de riesgo (Resolución 2404/2019)
     */
    protected function getRiskAction($nivel)
    {
        $actions = [
            'sin_riesgo'      => 'Mantener condiciones actuales',
            'riesgo_bajo'     => 'Acciones preventivas de mantenimiento',
            'riesgo_medio'    => 'Observación y acciones preventivas',
            'riesgo_alto'     => 'Intervención en marco de vigilancia epidemiológica',
            'riesgo_muy_alto' => 'Intervención inmediata en marco de vigilancia epidemiológica',
        ];
        return $actions[$nivel] ?? '';
    }

    /**
     * Determina nivel de riesgo según puntaje y baremo
     */
    protected function getNivelFromPuntaje($puntaje, $baremo)
    {
        foreach ($baremo as $nivel => $rango) {
            if ($puntaje >= $rango[0] && $puntaje <= $rango[1]) {
                return $nivel;
            }
        }
        return 'sin_riesgo';
    }

    /**
     * Calcula distribución por niveles de riesgo
     */
    protected function calculateDistribution($results, $campoNivel)
    {
        $distribucion = [
            'sin_riesgo'      => 0,
            'riesgo_bajo'     => 0,
            'riesgo_medio'    => 0,
            'riesgo_alto'     => 0,
            'riesgo_muy_alto' => 0,
        ];

        foreach ($results as $result) {
            $nivel = $result[$campoNivel] ?? '';
            if (isset($distribucion[$nivel])) {
                $distribucion[$nivel]++;
            }
        }

        return $distribucion;
    }

    /**
     * Obtiene ruta absoluta del logo de la empresa
     */
    protected function getLogoPath()
    {
        if (empty($this->companyData['logo_path'])) {
            return null;
        }

        $logoPath = FCPATH . $this->companyData['logo_path'];

        if (file_exists($logoPath)) {
            return $logoPath;
        }

        return null;
    }

    /**
     * Convierte imagen a data URI base64 para embeber en HTML
     * Optimizado: redimensiona imágenes grandes para reducir tamaño del PDF
     *
     * @param string $imagePath Ruta de la imagen
     * @param int $maxWidth Ancho máximo en píxeles (default 400)
     * @param int $maxHeight Alto máximo en píxeles (default 200)
     * @param int $quality Calidad JPEG 0-100 (default 85)
     */
    protected function imageToDataUri($imagePath, $maxWidth = 400, $maxHeight = 200, $quality = 85)
    {
        if (!file_exists($imagePath)) {
            return null;
        }

        // Obtener información de la imagen
        $imageInfo = @getimagesize($imagePath);
        if (!$imageInfo) {
            return null;
        }

        $origWidth = $imageInfo[0];
        $origHeight = $imageInfo[1];
        $mimeType = $imageInfo['mime'];

        // Si la imagen es pequeña, usar directamente sin procesar
        if ($origWidth <= $maxWidth && $origHeight <= $maxHeight) {
            $imageData = file_get_contents($imagePath);
            return 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
        }

        // Calcular nuevas dimensiones manteniendo proporción
        $ratio = min($maxWidth / $origWidth, $maxHeight / $origHeight);
        $newWidth = (int)($origWidth * $ratio);
        $newHeight = (int)($origHeight * $ratio);

        // Crear imagen según tipo
        switch ($mimeType) {
            case 'image/jpeg':
                $srcImage = @imagecreatefromjpeg($imagePath);
                break;
            case 'image/png':
                $srcImage = @imagecreatefrompng($imagePath);
                break;
            case 'image/gif':
                $srcImage = @imagecreatefromgif($imagePath);
                break;
            default:
                // Si no podemos procesar, devolver original
                $imageData = file_get_contents($imagePath);
                return 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
        }

        if (!$srcImage) {
            $imageData = file_get_contents($imagePath);
            return 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
        }

        // Crear imagen destino
        $dstImage = imagecreatetruecolor($newWidth, $newHeight);

        // Preservar transparencia para PNG
        if ($mimeType === 'image/png') {
            imagealphablending($dstImage, false);
            imagesavealpha($dstImage, true);
            $transparent = imagecolorallocatealpha($dstImage, 255, 255, 255, 127);
            imagefilledrectangle($dstImage, 0, 0, $newWidth, $newHeight, $transparent);
        }

        // Redimensionar
        imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

        // Capturar salida como JPEG (más pequeño) o PNG si tiene transparencia
        ob_start();
        if ($mimeType === 'image/png') {
            imagepng($dstImage, null, 6); // Compresión PNG nivel 6
            $outputMime = 'image/png';
        } else {
            imagejpeg($dstImage, null, $quality);
            $outputMime = 'image/jpeg';
        }
        $imageData = ob_get_clean();

        // Liberar memoria
        imagedestroy($srcImage);
        imagedestroy($dstImage);

        return 'data:' . $outputMime . ';base64,' . base64_encode($imageData);
    }
}
