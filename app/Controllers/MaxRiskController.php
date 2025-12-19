<?php

namespace App\Controllers;

use App\Models\MaxRiskResultModel;
use App\Models\BatteryServiceModel;
use App\Models\CompanyModel;
use App\Services\MaxRiskResultsService;

/**
 * Controlador para el módulo "Conclusión Total De RPS (Máximo Riesgo)"
 *
 * Gestiona la visualización y edición de resultados de máximo riesgo,
 * prompts contextuales para IA, y comentarios del consultor.
 */
class MaxRiskController extends BaseController
{
    protected MaxRiskResultModel $maxRiskModel;
    protected BatteryServiceModel $batteryServiceModel;
    protected CompanyModel $companyModel;

    public function __construct()
    {
        $this->maxRiskModel = new MaxRiskResultModel();
        $this->batteryServiceModel = new BatteryServiceModel();
        $this->companyModel = new CompanyModel();
    }

    /**
     * Vista principal del módulo
     * Calcula automáticamente los resultados si no existen
     */
    public function index($batteryServiceId)
    {
        $batteryServiceId = (int) $batteryServiceId;

        // Obtener datos del servicio
        $batteryService = $this->batteryServiceModel->find($batteryServiceId);
        if (!$batteryService) {
            return redirect()->to('/battery-services')->with('error', 'Servicio no encontrado');
        }

        // Obtener datos de la empresa
        $company = $this->companyModel->find($batteryService['company_id']);

        // Calcular resultados si no existen
        if (!$this->maxRiskModel->existsForBatteryService($batteryServiceId)) {
            $service = new MaxRiskResultsService();
            $service->calculateAndStore($batteryServiceId);
        }

        // Obtener todos los resultados
        $results = $this->maxRiskModel->getByBatteryService($batteryServiceId);

        // Agrupar por tipo de cuestionario
        $grouped = [
            'intralaboral' => [
                'totals'     => [],
                'domains'    => [],
                'dimensions' => [],
            ],
            'extralaboral' => [
                'totals'     => [],
                'dimensions' => [],
            ],
            'estres' => [
                'totals' => [],
            ],
        ];

        foreach ($results as $result) {
            $questionnaire = $result['questionnaire_type'];
            $type = $result['element_type'];

            if ($type === 'total') {
                $grouped[$questionnaire]['totals'][] = $result;
            } elseif ($type === 'domain') {
                $grouped[$questionnaire]['domains'][] = $result;
            } elseif ($type === 'dimension') {
                $grouped[$questionnaire]['dimensions'][] = $result;
            }
        }

        // Obtener estadísticas
        $stats = $this->maxRiskModel->getRiskStats($batteryServiceId);

        return view('max_risk/index', [
            'title'          => 'Conclusión Total De RPS (Máximo Riesgo)',
            'batteryService' => $batteryService,
            'company'        => $company,
            'grouped'        => $grouped,
            'stats'          => $stats,
            'allResults'     => $results,
        ]);
    }

    /**
     * Recalcular todos los resultados
     */
    public function recalculate($batteryServiceId)
    {
        $batteryServiceId = (int) $batteryServiceId;

        $service = new MaxRiskResultsService();
        $result = $service->calculateAndStore($batteryServiceId, true);

        if ($result['status'] === 'success') {
            return redirect()->to("/max-risk/{$batteryServiceId}")
                ->with('success', "Recalculado: {$result['count']} elementos procesados");
        }

        return redirect()->to("/max-risk/{$batteryServiceId}")
            ->with('error', $result['message'] ?? 'Error al recalcular');
    }

    /**
     * Guardar prompt contextual del consultor (AJAX)
     */
    public function savePrompt()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request']);
        }

        $id = (int) $this->request->getPost('id');
        $prompt = $this->request->getPost('prompt');

        $result = $this->maxRiskModel->saveConsultantPrompt($id, $prompt);

        return $this->response->setJSON([
            'success' => $result,
            'message' => $result ? 'Contexto guardado' : 'Error al guardar',
        ]);
    }

    /**
     * Guardar comentario del consultor (AJAX)
     */
    public function saveComment()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request']);
        }

        $id = (int) $this->request->getPost('id');
        $comment = $this->request->getPost('comment');

        $result = $this->maxRiskModel->saveConsultantComment($id, $comment);

        return $this->response->setJSON([
            'success' => $result,
            'message' => $result ? 'Comentario guardado' : 'Error al guardar',
        ]);
    }

    /**
     * Generar análisis con IA (AJAX)
     */
    public function generateAi()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request']);
        }

        $id = (int) $this->request->getPost('id');

        // Obtener el elemento
        $element = $this->maxRiskModel->find($id);
        if (!$element) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Elemento no encontrado',
            ]);
        }

        // Obtener datos del servicio y empresa para contexto
        $batteryService = $this->batteryServiceModel->find($element['battery_service_id']);
        $company = $this->companyModel->find($batteryService['company_id']);

        // Construir prompt para IA
        $prompt = $this->buildAiPrompt($element, $company, $batteryService);

        // Llamar a la API de IA (OpenAI)
        $aiResponse = $this->callOpenAI($prompt);

        if ($aiResponse['success']) {
            // Guardar análisis
            $this->maxRiskModel->updateAiAnalysis(
                $id,
                $aiResponse['analysis'],
                $aiResponse['recommendations'] ?? null,
                $aiResponse['model'] ?? 'gpt-4'
            );

            return $this->response->setJSON([
                'success'         => true,
                'analysis'        => $aiResponse['analysis'],
                'recommendations' => $aiResponse['recommendations'] ?? '',
                'message'         => 'Análisis generado correctamente',
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => $aiResponse['error'] ?? 'Error al generar análisis',
        ]);
    }

    /**
     * Construir prompt para IA
     */
    private function buildAiPrompt(array $element, array $company, array $batteryService): string
    {
        $nivelDescripcion = [
            'sin_riesgo'       => 'Sin riesgo o riesgo despreciable',
            'bajo'             => 'Riesgo bajo',
            'medio'            => 'Riesgo medio',
            'alto'             => 'Riesgo alto',
            'muy_alto'         => 'Riesgo muy alto',
            'riesgo_bajo'      => 'Riesgo bajo',
            'riesgo_medio'     => 'Riesgo medio',
            'riesgo_alto'      => 'Riesgo alto',
            'riesgo_muy_alto'  => 'Riesgo muy alto',
        ];

        $nivel = $nivelDescripcion[$element['worst_risk_level']] ?? $element['worst_risk_level'];

        $prompt = "Eres un especialista en Seguridad y Salud en el Trabajo (SST) de Colombia, experto en riesgo psicosocial según la Resolución 2764/2022 y Resolución 2404/2019.\n\n";

        $prompt .= "Analiza el siguiente resultado de evaluación de riesgo psicosocial y genera:\n";
        $prompt .= "1. ANÁLISIS: Interpretación profesional del nivel de riesgo encontrado (2-3 párrafos)\n";
        $prompt .= "2. CAUSAS PROBABLES: Factores organizacionales que podrían estar generando este riesgo\n";
        $prompt .= "3. IMPACTO: Consecuencias potenciales para los trabajadores y la organización\n";
        $prompt .= "4. INTERVENCIÓN PRIORITARIA: Acciones específicas ordenadas por urgencia\n\n";

        $prompt .= "=== DATOS DEL RESULTADO ===\n";
        $prompt .= "Empresa: {$company['name']}\n";
        $prompt .= "Sector/Actividad: " . ($company['economic_activity'] ?? 'No especificado') . "\n";
        $prompt .= "Elemento evaluado: {$element['element_name']}\n";
        $prompt .= "Tipo: {$element['element_type']} ({$element['questionnaire_type']})\n";
        $prompt .= "Puntaje de máximo riesgo: {$element['worst_score']} (Forma {$element['worst_form']})\n";
        $prompt .= "Nivel de riesgo: {$nivel}\n";

        if ($element['has_both_forms']) {
            $prompt .= "\nComparativo entre formas:\n";
            $prompt .= "- Forma A (Jefes/Profesionales): Puntaje {$element['form_a_score']}, n={$element['form_a_count']} trabajadores\n";
            $prompt .= "- Forma B (Auxiliares/Operarios): Puntaje {$element['form_b_score']}, n={$element['form_b_count']} trabajadores\n";
        }

        // Agregar contexto del consultor si existe
        if (!empty($element['consultant_prompt'])) {
            $prompt .= "\n=== CONTEXTO ADICIONAL DEL CONSULTOR (muy importante, considera esto en tu análisis) ===\n";
            $prompt .= $element['consultant_prompt'] . "\n";
        }

        $prompt .= "\n=== INSTRUCCIONES ===\n";
        $prompt .= "- Responde en español profesional\n";
        $prompt .= "- Sé específico y práctico en las recomendaciones\n";
        $prompt .= "- Fundamenta en la normativa colombiana vigente\n";
        $prompt .= "- El análisis debe ser útil para un profesional SST que elabora el informe de la batería\n";

        return $prompt;
    }

    /**
     * Llamar a API de OpenAI
     */
    private function callOpenAI(string $prompt): array
    {
        $apiKey = getenv('OPENAI_API_KEY') ?: ($_ENV['OPENAI_API_KEY'] ?? null);

        if (!$apiKey) {
            return [
                'success' => false,
                'error'   => 'API Key de OpenAI no configurada',
            ];
        }

        $client = \Config\Services::curlrequest();

        try {
            $response = $client->post('https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => "Bearer {$apiKey}",
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'model'       => 'gpt-4',
                    'messages'    => [
                        ['role' => 'system', 'content' => 'Eres un experto en SST de Colombia especializado en riesgo psicosocial.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.7,
                    'max_tokens'  => 2000,
                ],
                'timeout' => 60,
            ]);

            $data = json_decode($response->getBody(), true);

            if (isset($data['choices'][0]['message']['content'])) {
                $content = $data['choices'][0]['message']['content'];

                // Intentar separar análisis y recomendaciones
                $analysis = $content;
                $recommendations = null;

                if (preg_match('/INTERVENCIÓN[^:]*:(.*)/is', $content, $matches)) {
                    $recommendations = trim($matches[1]);
                }

                return [
                    'success'         => true,
                    'analysis'        => $analysis,
                    'recommendations' => $recommendations,
                    'model'           => $data['model'] ?? 'gpt-4',
                ];
            }

            return [
                'success' => false,
                'error'   => 'Respuesta inválida de OpenAI',
            ];
        } catch (\Exception $e) {
            log_message('error', 'OpenAI API Error: ' . $e->getMessage());

            return [
                'success' => false,
                'error'   => 'Error de conexión con OpenAI: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Obtener un elemento específico (AJAX)
     */
    public function getElement($id)
    {
        $element = $this->maxRiskModel->find((int) $id);

        if (!$element) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'No encontrado']);
        }

        return $this->response->setJSON($element);
    }
}
