<?php

namespace App\Controllers\Pdf\Estaticas;

use App\Controllers\Pdf\PdfBaseController;

/**
 * Controller para la página de Portada del informe PDF
 */
class PortadaController extends PdfBaseController
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

        return $this->renderView('pdf/estaticas/portada', [
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

        return view('pdf/_partials/preview_wrapper', [
            'content' => $html,
            'pageTitle' => 'Preview: Portada'
        ]);
    }
}
