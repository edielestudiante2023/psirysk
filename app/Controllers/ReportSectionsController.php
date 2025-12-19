<?php

namespace App\Controllers;

use App\Models\ReportModel;
use App\Models\ReportSectionModel;
use App\Models\BatteryServiceModel;
use App\Models\CompanyModel;
use App\Services\ReportGeneratorService;
use App\Services\OpenAIService;

class ReportSectionsController extends BaseController
{
    protected $reportModel;
    protected $sectionModel;
    protected $batteryModel;
    protected $companyModel;
    protected $generatorService;
    protected $openAIService;

    public function __construct()
    {
        $this->reportModel = new ReportModel();
        $this->sectionModel = new ReportSectionModel();
        $this->batteryModel = new BatteryServiceModel();
        $this->companyModel = new CompanyModel();
        $this->generatorService = new ReportGeneratorService();
        $this->openAIService = new OpenAIService();
    }

    /**
     * Vista principal de gestión de secciones del informe
     */
    public function index(int $serviceId)
    {
        // Verificar permisos (solo consultor)
        if (!$this->isConsultor()) {
            return redirect()->to('/dashboard')->with('error', 'Acceso denegado');
        }

        $service = $this->batteryModel
            ->select('battery_services.*, companies.name as company_name')
            ->join('companies', 'companies.id = battery_services.company_id')
            ->find($serviceId);

        if (!$service) {
            return redirect()->to('/dashboard')->with('error', 'Servicio no encontrado');
        }

        // Buscar o crear el reporte
        $report = $this->reportModel
            ->where('battery_service_id', $serviceId)
            ->where('report_type', 'general')
            ->first();

        if (!$report) {
            // Crear el reporte
            $reportId = $this->reportModel->insert([
                'battery_service_id' => $serviceId,
                'company_id' => $service['company_id'],
                'report_type' => 'general',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $report = $this->reportModel->find($reportId);
        }

        // Obtener secciones existentes
        $sections = $this->sectionModel->getSectionsByReport($report['id']);
        $stats = $this->sectionModel->getApprovalStats($report['id']);

        // Verificar si OpenAI está configurado
        $openAIConfigured = $this->openAIService->isConfigured();

        return view('reports/sections/index', [
            'service' => $service,
            'report' => $report,
            'sections' => $sections,
            'stats' => $stats,
            'openAIConfigured' => $openAIConfigured,
        ]);
    }

    /**
     * Generar todas las secciones con IA
     */
    public function generate(int $serviceId)
    {
        if (!$this->isConsultor()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Acceso denegado']);
        }

        // Verificar configuración de OpenAI
        if (!$this->openAIService->isConfigured()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'OpenAI no está configurado. Por favor configure OPENAI_API_KEY en el archivo .env'
            ]);
        }

        // Obtener o crear reporte
        $report = $this->reportModel
            ->where('battery_service_id', $serviceId)
            ->where('report_type', 'general')
            ->first();

        if (!$report) {
            $service = $this->batteryModel->find($serviceId);
            $reportId = $this->reportModel->insert([
                'battery_service_id' => $serviceId,
                'company_id' => $service['company_id'],
                'report_type' => 'general',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } else {
            $reportId = $report['id'];

            // Eliminar secciones anteriores si existen
            $this->sectionModel->where('report_id', $reportId)->delete();
        }

        // Generar secciones
        try {
            $result = $this->generatorService->generateReportSections($reportId);
            $result['report_id'] = $reportId; // Incluir el ID del reporte
            return $this->response->setJSON($result);
        } catch (\Exception $e) {
            log_message('error', 'Error generando secciones: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Ver/editar una sección específica
     */
    public function edit(int $sectionId)
    {
        if (!$this->isConsultor()) {
            return redirect()->to('/dashboard')->with('error', 'Acceso denegado');
        }

        $section = $this->sectionModel->find($sectionId);
        if (!$section) {
            return redirect()->back()->with('error', 'Sección no encontrada');
        }

        $report = $this->reportModel->find($section['report_id']);
        $service = $this->batteryModel
            ->select('battery_services.*, companies.name as company_name')
            ->join('companies', 'companies.id = battery_services.company_id')
            ->find($report['battery_service_id']);

        return view('reports/sections/edit', [
            'section' => $section,
            'report' => $report,
            'service' => $service,
        ]);
    }

    /**
     * Guardar comentario del consultor
     */
    public function saveComment(int $sectionId)
    {
        if (!$this->isConsultor()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Acceso denegado']);
        }

        $comment = $this->request->getPost('consultant_comment');

        $result = $this->sectionModel->addConsultantComment($sectionId, $comment);

        return $this->response->setJSON([
            'success' => $result,
            'message' => $result ? 'Comentario guardado' : 'Error al guardar'
        ]);
    }

    /**
     * Aprobar una sección
     */
    public function approve(int $sectionId)
    {
        if (!$this->isConsultor()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Acceso denegado']);
        }

        $userId = session()->get('user_id');
        $result = $this->sectionModel->approveSection($sectionId, $userId);

        // Obtener estadísticas actualizadas
        $section = $this->sectionModel->find($sectionId);
        $stats = $this->sectionModel->getApprovalStats($section['report_id']);

        return $this->response->setJSON([
            'success' => $result,
            'stats' => $stats,
            'message' => $result ? 'Sección aprobada' : 'Error al aprobar'
        ]);
    }

    /**
     * Aprobar todas las secciones
     */
    public function approveAll(int $reportId)
    {
        if (!$this->isConsultor()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Acceso denegado']);
        }

        $userId = session()->get('user_id');
        $result = $this->sectionModel->approveAllSections($reportId, $userId);

        return $this->response->setJSON([
            'success' => $result,
            'message' => $result ? 'Todas las secciones aprobadas' : 'Error al aprobar'
        ]);
    }

    /**
     * Vista de revisión por niveles (ejecutivo, totales, dominios, dimensiones)
     */
    public function review(int $serviceId, string $level = 'executive')
    {
        if (!$this->isConsultor()) {
            return redirect()->to('/dashboard')->with('error', 'Acceso denegado');
        }

        $service = $this->batteryModel
            ->select('battery_services.*, companies.name as company_name')
            ->join('companies', 'companies.id = battery_services.company_id')
            ->find($serviceId);

        $report = $this->reportModel
            ->where('battery_service_id', $serviceId)
            ->where('report_type', 'general')
            ->first();

        if (!$report) {
            return redirect()->to("/report-sections/{$serviceId}")->with('error', 'Primero debe generar las secciones del informe');
        }

        $sections = $this->sectionModel->getSectionsByLevel($report['id'], $level);
        $stats = $this->sectionModel->getApprovalStats($report['id']);

        // Niveles disponibles para navegación
        $levels = [
            'executive' => 'Resumen Ejecutivo',
            'total' => 'Totales Generales',
            'questionnaire' => 'Cuestionarios',
            'domain' => 'Dominios',
            'dimension' => 'Dimensiones',
        ];

        return view('reports/sections/review', [
            'service' => $service,
            'report' => $report,
            'sections' => $sections,
            'stats' => $stats,
            'currentLevel' => $level,
            'levels' => $levels,
        ]);
    }

    /**
     * Generar texto de IA para una sección específica
     */
    public function generateAI(int $sectionId)
    {
        if (!$this->isConsultor()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Acceso denegado']);
        }

        // Verificar configuración de OpenAI
        if (!$this->openAIService->isConfigured()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'OpenAI no está configurado. Por favor configure OPENAI_API_KEY en el archivo .env'
            ]);
        }

        $section = $this->sectionModel->find($sectionId);
        if (!$section) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sección no encontrada']);
        }

        try {
            $result = $this->generatorService->generateAITextForSection($sectionId);
            return $this->response->setJSON($result);
        } catch (\Exception $e) {
            log_message('error', 'Error generando texto IA para sección ' . $sectionId . ': ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Resetear una sección para regenerarla con IA
     * Limpia el texto generado y la aprobación
     */
    public function resetSection(int $sectionId)
    {
        if (!$this->isConsultor()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Acceso denegado']);
        }

        $section = $this->sectionModel->find($sectionId);
        if (!$section) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sección no encontrada']);
        }

        $result = $this->sectionModel->resetSection($sectionId);

        // Obtener estadísticas actualizadas
        $stats = $this->sectionModel->getApprovalStats($section['report_id']);

        return $this->response->setJSON([
            'success' => $result,
            'stats' => $stats,
            'message' => $result ? 'Sección reseteada. Puede regenerar el texto con IA.' : 'Error al resetear'
        ]);
    }

    /**
     * Desaprobar una sección (sin borrar el texto)
     */
    public function unapprove(int $sectionId)
    {
        if (!$this->isConsultor()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Acceso denegado']);
        }

        $section = $this->sectionModel->find($sectionId);
        if (!$section) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sección no encontrada']);
        }

        $result = $this->sectionModel->unapproveSection($sectionId);

        // Obtener estadísticas actualizadas
        $stats = $this->sectionModel->getApprovalStats($section['report_id']);

        return $this->response->setJSON([
            'success' => $result,
            'stats' => $stats,
            'message' => $result ? 'Sección desaprobada' : 'Error al desaprobar'
        ]);
    }

    /**
     * Guardar prompt complementario del consultor
     */
    public function saveConsultantPrompt(int $sectionId)
    {
        if (!$this->isConsultor()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Acceso denegado']);
        }

        $section = $this->sectionModel->find($sectionId);
        if (!$section) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sección no encontrada']);
        }

        $prompt = $this->request->getPost('consultant_prompt');

        // Permitir vacío para limpiar el prompt
        $result = $this->sectionModel->saveConsultantPrompt($sectionId, $prompt ?: null);

        return $this->response->setJSON([
            'success' => $result,
            'message' => $result ? 'Contexto guardado correctamente' : 'Error al guardar'
        ]);
    }

    /**
     * Generar texto de IA para todas las secciones pendientes (una por una)
     */
    public function generateAllAI(int $reportId)
    {
        if (!$this->isConsultor()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Acceso denegado']);
        }

        if (!$this->openAIService->isConfigured()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'OpenAI no está configurado'
            ]);
        }

        // Obtener secciones sin texto de IA
        $sections = $this->sectionModel
            ->where('report_id', $reportId)
            ->where('ai_generated_text IS NULL')
            ->findAll();

        if (empty($sections)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Todas las secciones ya tienen texto generado',
                'pending' => 0
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'pending' => count($sections),
            'section_ids' => array_column($sections, 'id'),
            'message' => 'Hay ' . count($sections) . ' secciones pendientes de generar'
        ]);
    }

    /**
     * API: Obtener secciones por nivel y forma
     */
    public function getSections(int $reportId)
    {
        if (!$this->isConsultor()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Acceso denegado']);
        }

        $level = $this->request->getGet('level');
        $formType = $this->request->getGet('form_type');
        $questionnaire = $this->request->getGet('questionnaire');

        $builder = $this->sectionModel->where('report_id', $reportId);

        if ($level) {
            $builder->where('section_level', $level);
        }
        if ($formType) {
            $builder->where('form_type', $formType);
        }
        if ($questionnaire) {
            $builder->where('questionnaire_type', $questionnaire);
        }

        $sections = $builder->orderBy('order_position', 'ASC')->findAll();

        return $this->response->setJSON([
            'success' => true,
            'sections' => $sections
        ]);
    }

    /**
     * Verificar si el usuario es consultor
     */
    private function isConsultor(): bool
    {
        $role = session()->get('role_name');
        return in_array($role, ['consultor', 'superadmin']);
    }
}
