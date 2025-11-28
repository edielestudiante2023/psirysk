<?php

namespace App\Controllers;

use App\Models\BatteryServiceModel;
use App\Models\CompanyModel;
use App\Models\DemographicsInterpretationModel;
use App\Models\DemographicsSectionsModel;
use App\Services\DemographicsReportService;

/**
 * Controlador independiente para el módulo de Ficha de Datos Generales con IA
 */
class DemographicsReportController extends BaseController
{
    protected $batteryModel;
    protected $companyModel;
    protected $demographicsService;
    protected $interpretationModel;
    protected $sectionsModel;

    public function __construct()
    {
        $this->batteryModel = new BatteryServiceModel();
        $this->companyModel = new CompanyModel();
        $this->demographicsService = new DemographicsReportService();
        $this->interpretationModel = new DemographicsInterpretationModel();
        $this->sectionsModel = new DemographicsSectionsModel();
    }

    /**
     * Vista principal del módulo de Ficha de Datos Generales con IA
     */
    public function index(int $serviceId)
    {
        // Verificar permisos (solo consultor)
        if (!$this->isConsultor()) {
            return redirect()->to('/dashboard')->with('error', 'Acceso denegado');
        }

        // Obtener datos del servicio (solo columnas que existen)
        $service = $this->batteryModel
            ->select('battery_services.id, battery_services.company_id, battery_services.status, battery_services.created_at, companies.name as company_name')
            ->join('companies', 'companies.id = battery_services.company_id')
            ->find($serviceId);

        if (!$service) {
            return redirect()->to('/dashboard')->with('error', 'Servicio no encontrado');
        }

        // Verificar si OpenAI está configurado
        $openAIConfigured = $this->demographicsService->isConfigured();

        // Obtener datos agregados
        $aggregatedData = $this->demographicsService->aggregateDemographics($serviceId);

        // DEBUG: Log de datos agregados
        log_message('debug', "=== CONTROLLER DEBUG ===");
        log_message('debug', "aggregatedData keys: " . implode(', ', array_keys($aggregatedData)));

        if (isset($aggregatedData['gender'])) {
            log_message('debug', "gender in controller: " . json_encode($aggregatedData['gender']));
            log_message('debug', "gender type: " . gettype($aggregatedData['gender']));
            log_message('debug', "gender count: " . (is_array($aggregatedData['gender']) ? count($aggregatedData['gender']) : 'N/A'));
        } else {
            log_message('error', "gender NOT SET in aggregatedData");
        }

        if (isset($aggregatedData['marital_status'])) {
            log_message('debug', "marital_status in controller: " . json_encode($aggregatedData['marital_status']));
        } else {
            log_message('error', "marital_status NOT SET in aggregatedData");
        }

        // Obtener secciones guardadas (nueva estructura)
        $savedSections = $this->sectionsModel->getByService($serviceId);
        $hasSavedSections = $savedSections !== null;
        $savedAt = $savedSections ? $savedSections['updated_at'] : null;
        $sintesisComment = $savedSections ? $savedSections['sintesis_comment'] : null;

        // Para compatibilidad con vista actual, construir interpretación completa si hay secciones
        $savedInterpretation = null;
        if ($hasSavedSections) {
            $savedInterpretation = $this->buildInterpretationFromSections($savedSections);
        }

        return view('demographics/index', [
            'service' => $service,
            'aggregatedData' => $aggregatedData,
            'openAIConfigured' => $openAIConfigured,
            'savedInterpretation' => $savedInterpretation,
            'savedSections' => $savedSections,
            'hasSavedSections' => $hasSavedSections,
            'savedAt' => $savedAt,
            'consultantComment' => $sintesisComment,
        ]);
    }

    /**
     * Construir texto de interpretación completo desde secciones guardadas
     */
    private function buildInterpretationFromSections(array $sections): string
    {
        $parts = [];

        $sectionTitles = [
            'sexo' => 'SEXO',
            'edad' => 'RANGO DE EDAD',
            'estado_civil' => 'ESTADO CIVIL',
            'educacion' => 'NIVEL EDUCATIVO',
            'estrato' => 'ESTRATO',
            'vivienda' => 'VIVIENDA',
            'dependientes' => 'PERSONAS A CARGO',
            'residencia' => 'LUGAR DE RESIDENCIA',
            'antiguedad_empresa' => 'ANTIGÜEDAD EN LA EMPRESA',
            'antiguedad_cargo' => 'ANTIGÜEDAD EN EL CARGO',
            'contrato' => 'TIPO DE CONTRATO',
            'cargo' => 'TIPO DE CARGO',
            'area' => 'ÁREA/DEPARTAMENTO',
            'horas' => 'HORAS DE TRABAJO',
            'salario' => 'RANGO SALARIAL',
            'sintesis' => 'SÍNTESIS GENERAL',
        ];

        foreach ($sectionTitles as $key => $title) {
            $iaColumn = $key . '_ia';
            if (!empty($sections[$iaColumn])) {
                $parts[] = "**{$title}:** " . $sections[$iaColumn];
            }
        }

        return implode("\n\n", $parts);
    }

    /**
     * Generar interpretación con IA vía AJAX (NO guarda automáticamente)
     */
    public function generate(int $serviceId)
    {
        if (!$this->isConsultor()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Acceso denegado']);
        }

        // Verificar configuración de OpenAI
        if (!$this->demographicsService->isConfigured()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'OpenAI no está configurado. Por favor configure OPENAI_API_KEY en el archivo .env'
            ]);
        }

        try {
            $result = $this->demographicsService->generateInterpretation($serviceId);

            if (isset($result['error'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $result['error']
                ]);
            }

            // Parsear secciones del texto IA generado
            $parsedSections = $this->sectionsModel->parseIAText($result['interpretation']);

            return $this->response->setJSON([
                'success' => true,
                'interpretation' => $result['interpretation'],
                'sections' => $parsedSections,
                'aggregated_data' => $result['aggregated_data'],
                'generated_at' => $result['generated_at'],
                'saved_to_db' => false, // No se guarda automáticamente
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error generando interpretación demográfica: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Guardar secciones generadas vía AJAX (botón Guardar)
     */
    public function saveSections(int $serviceId)
    {
        if (!$this->isConsultor()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Acceso denegado']);
        }

        try {
            $json = $this->request->getJSON();
            $iaText = $json->interpretation ?? '';
            $aggregatedData = (array) ($json->aggregated_data ?? []);

            if (empty($iaText)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No hay interpretación para guardar'
                ]);
            }

            $userId = session()->get('user_id');
            $saved = $this->sectionsModel->saveSections($serviceId, $iaText, $aggregatedData, $userId);

            if ($saved) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Secciones guardadas correctamente',
                    'saved_at' => date('Y-m-d H:i:s'),
                ]);
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => 'No se pudieron guardar las secciones',
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error guardando secciones demográficas: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Obtener solo los datos agregados (sin IA) vía AJAX
     */
    public function getData(int $serviceId)
    {
        if (!$this->isConsultor()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Acceso denegado']);
        }

        try {
            $aggregatedData = $this->demographicsService->aggregateDemographics($serviceId);

            if (isset($aggregatedData['error'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $aggregatedData['error']
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $aggregatedData,
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error obteniendo datos demográficos: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Obtener interpretación guardada vía AJAX
     */
    public function getInterpretation(int $serviceId)
    {
        if (!$this->isConsultor()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Acceso denegado']);
        }

        $record = $this->interpretationModel->getLatestByService($serviceId);

        if (!$record) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No hay interpretación guardada'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'interpretation' => $record['interpretation_text'],
            'generated_at' => $record['updated_at'],
        ]);
    }

    /**
     * Obtener historial de interpretaciones vía AJAX
     */
    public function getHistory(int $serviceId)
    {
        if (!$this->isConsultor()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Acceso denegado']);
        }

        $history = $this->interpretationModel->getHistory($serviceId);

        return $this->response->setJSON([
            'success' => true,
            'history' => $history,
        ]);
    }

    /**
     * Guardar comentario del consultor en síntesis vía AJAX
     */
    public function saveComment(int $serviceId)
    {
        if (!$this->isConsultor()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Acceso denegado']);
        }

        $comment = $this->request->getPost('comment') ?? $this->request->getJSON()->comment ?? '';

        try {
            // Guardar en la nueva tabla de secciones
            $saved = $this->sectionsModel->saveSintesisComment($serviceId, $comment);

            if ($saved) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Comentario guardado correctamente',
                    'saved_at' => date('Y-m-d H:i:s'),
                ]);
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => 'No se pudo guardar el comentario',
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error guardando comentario demográfico: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Obtener comentario del consultor vía AJAX
     */
    public function getComment(int $serviceId)
    {
        if (!$this->isConsultor()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Acceso denegado']);
        }

        $comment = $this->interpretationModel->getConsultantComment($serviceId);

        return $this->response->setJSON([
            'success' => true,
            'comment' => $comment,
        ]);
    }

    /**
     * Limpiar interpretación guardada
     */
    public function clearInterpretation(int $serviceId)
    {
        if (!$this->isConsultor()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Acceso denegado']);
        }

        // Eliminar de BD
        $deleted = $this->interpretationModel->deleteByService($serviceId);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Interpretación eliminada',
            'deleted_from_db' => $deleted,
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
