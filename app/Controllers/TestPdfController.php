<?php

namespace App\Controllers;

use App\Models\DemographicsInterpretationModel;
use App\Models\ReportSectionModel;
use App\Models\ReportModel;
use App\Models\BatteryServiceModel;
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Controlador PROVISIONAL para probar la generación de PDFs
 * ELIMINAR EN PRODUCCIÓN
 */
class TestPdfController extends BaseController
{
    /**
     * Vista de prueba para demographics_interpretations
     */
    public function testDemographics(int $serviceId)
    {
        $interpretationModel = new DemographicsInterpretationModel();
        $batteryModel = new BatteryServiceModel();

        // Obtener datos del servicio
        $service = $batteryModel
            ->select('battery_services.id, battery_services.company_id, companies.name as company_name')
            ->join('companies', 'companies.id = battery_services.company_id')
            ->find($serviceId);

        if (!$service) {
            return "Servicio no encontrado";
        }

        // Obtener interpretación guardada
        $record = $interpretationModel->getLatestByService($serviceId);

        if (!$record) {
            return "No hay interpretación guardada para el servicio $serviceId";
        }

        // Parsear el texto de interpretación por secciones
        $sections = $this->parseInterpretationSections($record['interpretation_text']);

        return view('test_pdf/demographics', [
            'service' => $service,
            'record' => $record,
            'sections' => $sections,
            'rawText' => $record['interpretation_text'],
            'consultantComment' => $record['consultant_comment'],
            'aggregatedData' => $record['aggregated_data'], // El cast json-array del modelo ya decodifica
        ]);
    }

    /**
     * Vista de prueba para report_sections
     */
    public function testReportSections(int $serviceId)
    {
        $reportModel = new ReportModel();
        $sectionModel = new ReportSectionModel();
        $batteryModel = new BatteryServiceModel();

        // Obtener datos del servicio
        $service = $batteryModel
            ->select('battery_services.id, battery_services.company_id, companies.name as company_name')
            ->join('companies', 'companies.id = battery_services.company_id')
            ->find($serviceId);

        if (!$service) {
            return "Servicio no encontrado";
        }

        // Obtener el reporte del servicio
        $report = $reportModel->where('battery_service_id', $serviceId)->first();

        if (!$report) {
            return "No hay reporte generado para el servicio $serviceId";
        }

        // Obtener todas las secciones del reporte
        $sections = $sectionModel->getSectionsByReport($report['id']);

        // Agrupar secciones por nivel
        $groupedSections = [
            'executive' => [],
            'total' => [],
            'questionnaire' => [],
            'domain' => [],
            'dimension' => [],
        ];

        foreach ($sections as $section) {
            $level = $section['section_level'];
            if (isset($groupedSections[$level])) {
                $groupedSections[$level][] = $section;
            }
        }

        return view('test_pdf/report_sections', [
            'service' => $service,
            'report' => $report,
            'sections' => $sections,
            'groupedSections' => $groupedSections,
            'totalSections' => count($sections),
        ]);
    }

    /**
     * Parsear el texto de interpretación demográfica en secciones
     */
    private function parseInterpretationSections(string $text): array
    {
        $sections = [];

        // Patrón para encontrar secciones: **NOMBRE:**
        $pattern = '/\*\*([A-ZÁÉÍÓÚÜÑ\s\/]+):\*\*/u';

        // Dividir el texto por el patrón
        $parts = preg_split($pattern, $text, -1, PREG_SPLIT_DELIM_CAPTURE);

        // El primer elemento es texto antes de la primera sección (ignorar si está vacío)
        for ($i = 1; $i < count($parts); $i += 2) {
            $sectionName = trim($parts[$i]);
            $sectionContent = isset($parts[$i + 1]) ? trim($parts[$i + 1]) : '';

            $sections[] = [
                'name' => $sectionName,
                'content' => $sectionContent,
            ];
        }

        return $sections;
    }

    /**
     * Vista índice de pruebas disponibles
     */
    public function index()
    {
        $batteryModel = new BatteryServiceModel();

        // Obtener servicios con datos
        $services = $batteryModel
            ->select('battery_services.id, battery_services.company_id, battery_services.status, companies.name as company_name')
            ->join('companies', 'companies.id = battery_services.company_id')
            ->orderBy('battery_services.id', 'DESC')
            ->limit(10)
            ->findAll();

        return view('test_pdf/index', [
            'services' => $services,
        ]);
    }

    /**
     * Descargar PDF de demographics_interpretations
     */
    public function downloadDemographicsPdf(int $serviceId)
    {
        $interpretationModel = new DemographicsInterpretationModel();
        $batteryModel = new BatteryServiceModel();

        // Obtener datos del servicio
        $service = $batteryModel
            ->select('battery_services.id, battery_services.company_id, companies.name as company_name')
            ->join('companies', 'companies.id = battery_services.company_id')
            ->find($serviceId);

        if (!$service) {
            return "Servicio no encontrado";
        }

        // Obtener interpretación guardada
        $record = $interpretationModel->getLatestByService($serviceId);

        if (!$record) {
            return "No hay interpretación guardada para el servicio $serviceId";
        }

        // Parsear el texto de interpretación por secciones
        $sections = $this->parseInterpretationSections($record['interpretation_text']);

        // Generar HTML para PDF
        $html = view('test_pdf/demographics_pdf', [
            'service' => $service,
            'record' => $record,
            'sections' => $sections,
            'consultantComment' => $record['consultant_comment'],
            'aggregatedData' => $record['aggregated_data'],
        ]);

        // Configurar DOMPDF
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        // Nombre del archivo
        $filename = 'Ficha_Datos_Generales_Servicio_' . $serviceId . '_' . date('Ymd_His') . '.pdf';

        // Descargar
        $dompdf->stream($filename, ['Attachment' => true]);
        exit;
    }

    /**
     * Descargar PDF de report_sections
     */
    public function downloadReportSectionsPdf(int $serviceId)
    {
        $reportModel = new ReportModel();
        $sectionModel = new ReportSectionModel();
        $batteryModel = new BatteryServiceModel();

        // Obtener datos del servicio
        $service = $batteryModel
            ->select('battery_services.id, battery_services.company_id, companies.name as company_name')
            ->join('companies', 'companies.id = battery_services.company_id')
            ->find($serviceId);

        if (!$service) {
            return "Servicio no encontrado";
        }

        // Obtener el reporte del servicio
        $report = $reportModel->where('battery_service_id', $serviceId)->first();

        if (!$report) {
            return "No hay reporte generado para el servicio $serviceId";
        }

        // Obtener todas las secciones del reporte
        $sections = $sectionModel->getSectionsByReport($report['id']);

        // Agrupar secciones por nivel
        $groupedSections = [
            'executive' => [],
            'total' => [],
            'questionnaire' => [],
            'domain' => [],
            'dimension' => [],
        ];

        foreach ($sections as $section) {
            $level = $section['section_level'];
            if (isset($groupedSections[$level])) {
                $groupedSections[$level][] = $section;
            }
        }

        // Generar HTML para PDF
        $html = view('test_pdf/report_sections_pdf', [
            'service' => $service,
            'report' => $report,
            'sections' => $sections,
            'groupedSections' => $groupedSections,
            'totalSections' => count($sections),
        ]);

        // Configurar DOMPDF
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        // Nombre del archivo
        $filename = 'Informe_Secciones_Servicio_' . $serviceId . '_' . date('Ymd_His') . '.pdf';

        // Descargar
        $dompdf->stream($filename, ['Attachment' => true]);
        exit;
    }
}
