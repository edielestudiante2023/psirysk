<?php

namespace App\Controllers\PdfCycloid;

use App\Controllers\BaseController;

/**
 * Clase base para todos los controllers de generación de PDF Cycloid
 * Proporciona métodos comunes para header, footer, estilos y utilidades
 */
class PdfCycloidBaseController extends BaseController
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
