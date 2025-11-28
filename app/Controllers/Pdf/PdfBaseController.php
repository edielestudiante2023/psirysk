<?php

namespace App\Controllers\Pdf;

use App\Controllers\BaseController;

/**
 * Clase base para todos los controllers de generación de PDF
 * Proporciona métodos comunes para header, footer, estilos y utilidades
 */
class PdfBaseController extends BaseController
{
    protected $batteryServiceId;
    protected $companyData;
    protected $consultantData;

    /**
     * Configuración común del PDF
     */
    protected $pdfConfig = [
        'page_size' => 'Letter',
        'orientation' => 'portrait',
        'margin_top' => 25,
        'margin_bottom' => 20,
        'margin_left' => 20,
        'margin_right' => 20,
    ];

    /**
     * Colores estándar por nivel de riesgo
     */
    protected $riskColors = [
        'sin_riesgo' => '#4CAF50',      // Verde
        'riesgo_bajo' => '#8BC34A',      // Verde claro
        'riesgo_medio' => '#FFEB3B',     // Amarillo
        'riesgo_alto' => '#FF9800',      // Naranja
        'riesgo_muy_alto' => '#F44336',  // Rojo
    ];

    /**
     * Rangos de puntuación por nivel de riesgo
     */
    protected $riskRanges = [
        'sin_riesgo' => [0, 0],
        'riesgo_bajo' => [0.1, 25],
        'riesgo_medio' => [25.1, 50],
        'riesgo_alto' => [50.1, 75],
        'riesgo_muy_alto' => [75.1, 100],
    ];

    /**
     * Inicializa los datos comunes necesarios para el PDF
     */
    protected function initializeData($batteryServiceId)
    {
        $this->batteryServiceId = $batteryServiceId;

        // Cargar datos de la empresa
        $this->companyData = $this->loadCompanyData($batteryServiceId);

        // Cargar datos del consultor
        $this->consultantData = $this->loadConsultantData($batteryServiceId);
    }

    /**
     * Carga los datos de la empresa desde battery_services
     */
    protected function loadCompanyData($batteryServiceId)
    {
        $db = \Config\Database::connect();

        $query = $db->query("
            SELECT
                bs.id,
                bs.company_id,
                c.name as company_name,
                c.nit,
                c.address,
                c.phone,
                c.logo_path,
                bs.service_date,
                bs.status
            FROM battery_services bs
            JOIN companies c ON bs.company_id = c.id
            WHERE bs.id = ?
        ", [$batteryServiceId]);

        $data = $query->getRowArray() ?: [];
        // Agregar campos adicionales con valores por defecto
        $data['city'] = $data['city'] ?? 'Bogotá D.C.';
        $data['logo_path'] = $data['logo_path'] ?? null;
        $data['application_date'] = $data['service_date'] ?? date('Y-m-d');

        return $data;
    }

    /**
     * Carga los datos del consultor desde la tabla consultants
     */
    protected function loadConsultantData($batteryServiceId)
    {
        $db = \Config\Database::connect();

        $query = $db->query("
            SELECT
                c.id,
                c.nombre_completo as name,
                c.tipo_documento,
                c.numero_documento,
                c.licencia_sst,
                c.cargo as position,
                c.email,
                c.telefono as phone,
                c.website,
                c.linkedin,
                c.firma_path as signature_path
            FROM battery_services bs
            JOIN consultants c ON bs.consultant_id = c.id
            WHERE bs.id = ?
        ", [$batteryServiceId]);

        $data = $query->getRowArray() ?: [];

        // Valores por defecto si no hay consultor asignado
        if (empty($data)) {
            $data = [
                'name' => 'Consultor',
                'position' => 'Especialista SST',
                'email' => '',
                'phone' => '',
                'licencia_sst' => '',
                'signature_path' => null,
            ];
        }

        return $data;
    }

    /**
     * Renderiza el header común para todas las páginas del PDF
     */
    protected function renderHeader()
    {
        return view('pdf/_partials/header', [
            'company' => $this->companyData,
            'consultant' => $this->consultantData,
        ]);
    }

    /**
     * Renderiza el footer común para todas las páginas del PDF
     */
    protected function renderFooter($pageNumber = null, $totalPages = null)
    {
        return view('pdf/_partials/footer', [
            'pageNumber' => $pageNumber,
            'totalPages' => $totalPages,
        ]);
    }

    /**
     * Obtiene el color correspondiente a un nivel de riesgo
     */
    protected function getRiskColor($riskLevel)
    {
        $level = strtolower(str_replace(' ', '_', $riskLevel));
        return $this->riskColors[$level] ?? '#9E9E9E';
    }

    /**
     * Determina el nivel de riesgo basado en un puntaje
     */
    protected function getRiskLevelFromScore($score)
    {
        if ($score <= 0) return 'sin_riesgo';
        if ($score <= 25) return 'riesgo_bajo';
        if ($score <= 50) return 'riesgo_medio';
        if ($score <= 75) return 'riesgo_alto';
        return 'riesgo_muy_alto';
    }

    /**
     * Obtiene el texto de acción recomendada según el nivel de riesgo
     */
    protected function getRiskAction($riskLevel)
    {
        $actions = [
            'sin_riesgo' => 'MANTENER',
            'riesgo_bajo' => 'MANTENER',
            'riesgo_medio' => 'REFORZAR',
            'riesgo_alto' => 'INTERVENIR',
            'riesgo_muy_alto' => 'INTERVENIR INMEDIATAMENTE',
        ];

        $level = strtolower(str_replace(' ', '_', $riskLevel));
        return $actions[$level] ?? 'EVALUAR';
    }

    /**
     * Genera el HTML base de una página con header y footer
     */
    protected function wrapInPage($content, $pageNumber = null, $totalPages = null)
    {
        return '
            <div class="pdf-page">
                ' . $this->renderHeader() . '
                <div class="pdf-content">
                    ' . $content . '
                </div>
                ' . $this->renderFooter($pageNumber, $totalPages) . '
            </div>
            <div class="page-break"></div>
        ';
    }

    /**
     * Genera un page break para el PDF
     */
    protected function pageBreak()
    {
        return '<div class="page-break"></div>';
    }

    /**
     * Formatea un número con decimales según configuración colombiana
     */
    protected function formatNumber($number, $decimals = 2)
    {
        return number_format($number, $decimals, ',', '.');
    }

    /**
     * Formatea una fecha en español
     */
    protected function formatDate($date, $format = 'long')
    {
        $months = [
            1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
            5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
            9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
        ];

        $timestamp = strtotime($date);
        $day = date('d', $timestamp);
        $month = $months[(int)date('m', $timestamp)];
        $year = date('Y', $timestamp);

        if ($format === 'long') {
            return "$day de $month de $year";
        }

        return "$month de $year";
    }

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
     * Carga los estilos CSS para el PDF
     */
    protected function loadStyles()
    {
        return '<style>' . file_get_contents(APPPATH . 'Views/pdf/_partials/css/pdf-styles.css') . '</style>';
    }

    /**
     * Método helper para renderizar una vista con datos comunes
     */
    protected function renderView($viewPath, $data = [])
    {
        $commonData = [
            'company' => $this->companyData,
            'consultant' => $this->consultantData,
            'batteryServiceId' => $this->batteryServiceId,
            'riskColors' => $this->riskColors,
        ];

        return view($viewPath, array_merge($commonData, $data));
    }
}
