<?php

namespace App\Controllers;

use App\Models\MaxRiskResultModel;
use App\Models\BatteryServiceModel;
use App\Models\CompanyModel;
use App\Services\MaxRiskResultsService;

/**
 * Controlador para el módulo "Conclusión Total De RPS (Máximo Riesgo)"
 *
 * Genera UNA conclusión global integrando todos los resultados de máximo riesgo.
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
            'intralaboral' => ['totals' => [], 'domains' => [], 'dimensions' => []],
            'extralaboral' => ['totals' => [], 'dimensions' => []],
            'estres' => ['totals' => []],
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

        // Identificar elementos críticos (alto y muy alto)
        $criticalElements = array_filter($results, function($r) {
            return in_array($r['worst_risk_level'], ['alto', 'muy_alto', 'riesgo_alto', 'riesgo_muy_alto']);
        });

        return view('max_risk/index', [
            'title'            => 'Conclusión Total De RPS (Máximo Riesgo)',
            'batteryService'   => $batteryService,
            'company'          => $company,
            'grouped'          => $grouped,
            'stats'            => $stats,
            'allResults'       => $results,
            'criticalElements' => $criticalElements,
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
     * Guardar contexto complementario del consultor (AJAX)
     */
    public function savePrompt()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request']);
        }

        $batteryServiceId = (int) $this->request->getPost('battery_service_id');
        $prompt = $this->request->getPost('prompt');

        $result = $this->batteryServiceModel->update($batteryServiceId, [
            'global_conclusion_prompt' => $prompt,
        ]);

        return $this->response->setJSON([
            'success' => $result,
            'message' => $result ? 'Contexto guardado' : 'Error al guardar',
        ]);
    }

    /**
     * Generar Conclusión Global con IA (AJAX)
     */
    public function generateGlobalConclusion()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request']);
        }

        $batteryServiceId = (int) $this->request->getPost('battery_service_id');

        // Obtener datos
        $batteryService = $this->batteryServiceModel->find($batteryServiceId);
        if (!$batteryService) {
            return $this->response->setJSON(['success' => false, 'message' => 'Servicio no encontrado']);
        }

        $company = $this->companyModel->find($batteryService['company_id']);
        $results = $this->maxRiskModel->getByBatteryService($batteryServiceId);
        $stats = $this->maxRiskModel->getRiskStats($batteryServiceId);

        if (empty($results)) {
            return $this->response->setJSON(['success' => false, 'message' => 'No hay resultados calculados']);
        }

        // Construir prompt global
        $prompt = $this->buildGlobalPrompt($results, $stats, $company, $batteryService);

        // Llamar a OpenAI
        $aiResponse = $this->callOpenAI($prompt);

        if ($aiResponse['success']) {
            // Guardar conclusión en battery_services
            $this->batteryServiceModel->update($batteryServiceId, [
                'global_conclusion_text' => $aiResponse['analysis'],
                'global_conclusion_generated_at' => date('Y-m-d H:i:s'),
            ]);

            return $this->response->setJSON([
                'success' => true,
                'conclusion' => $aiResponse['analysis'],
                'message' => 'Conclusión generada correctamente',
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => $aiResponse['error'] ?? 'Error al generar conclusión',
        ]);
    }

    /**
     * Construir prompt para conclusión global
     */
    private function buildGlobalPrompt(array $results, array $stats, array $company, array $batteryService): string
    {
        $prompt = "Eres un especialista en Seguridad y Salud en el Trabajo (SST) de Colombia, experto en riesgo psicosocial según la Resolución 2764/2022 y Resolución 2404/2019.\n\n";

        $prompt .= "Genera la **CONCLUSIÓN TOTAL DE APLICACIÓN DE LA BATERÍA DE RIESGO PSICOSOCIAL** para esta empresa.\n\n";

        $prompt .= "Este texto es el resumen ejecutivo final que integra todos los resultados. Debe ser:\n";
        $prompt .= "- Profesional y técnico, pero comprensible para gerencia\n";
        $prompt .= "- Integrador: no lista elementos uno por uno, sino que da una visión global\n";
        $prompt .= "- Práctico: identifica las áreas más críticas y qué hacer al respecto\n";
        $prompt .= "- Entre 400 y 600 palabras\n\n";

        $prompt .= "=== DATOS DE LA EMPRESA ===\n";
        $prompt .= "Empresa: {$company['name']}\n";
        $prompt .= "NIT: " . ($company['nit'] ?? 'N/A') . "\n";
        $prompt .= "Sector/Actividad: " . ($company['economic_activity'] ?? 'No especificado') . "\n\n";

        $prompt .= "=== RESUMEN ESTADÍSTICO ===\n";
        $prompt .= "Total de elementos evaluados: {$stats['total_elements']}\n";
        $prompt .= "Elementos en riesgo MUY ALTO: {$stats['muy_alto']}\n";
        $prompt .= "Elementos en riesgo ALTO: {$stats['alto']}\n";
        $prompt .= "Elementos en riesgo MEDIO: {$stats['medio']}\n";
        $prompt .= "Elementos en riesgo BAJO o SIN RIESGO: " . ($stats['bajo'] + $stats['sin_riesgo']) . "\n";
        $prompt .= "Total elementos críticos (alto + muy alto): {$stats['critical_count']}\n\n";

        // Agrupar resultados para el prompt
        $prompt .= "=== RESULTADOS POR CUESTIONARIO (Máximo Riesgo) ===\n\n";

        // Totales
        $prompt .= "TOTALES:\n";
        foreach ($results as $r) {
            if ($r['element_type'] === 'total') {
                $prompt .= "- {$r['element_name']}: {$r['worst_score']} ({$r['worst_risk_level']}) - Forma {$r['worst_form']}\n";
            }
        }

        // Dominios con riesgo alto/muy alto
        $prompt .= "\nDOMINIOS CRÍTICOS (Alto/Muy Alto):\n";
        $hasCriticalDomains = false;
        foreach ($results as $r) {
            if ($r['element_type'] === 'domain' && in_array($r['worst_risk_level'], ['alto', 'muy_alto', 'riesgo_alto', 'riesgo_muy_alto'])) {
                $prompt .= "- {$r['element_name']}: {$r['worst_score']} ({$r['worst_risk_level']})\n";
                $hasCriticalDomains = true;
            }
        }
        if (!$hasCriticalDomains) {
            $prompt .= "- Ningún dominio en riesgo alto o muy alto\n";
        }

        // Dimensiones con riesgo alto/muy alto
        $prompt .= "\nDIMENSIONES CRÍTICAS (Alto/Muy Alto):\n";
        $hasCriticalDimensions = false;
        foreach ($results as $r) {
            if ($r['element_type'] === 'dimension' && in_array($r['worst_risk_level'], ['alto', 'muy_alto', 'riesgo_alto', 'riesgo_muy_alto'])) {
                $prompt .= "- {$r['element_name']} ({$r['questionnaire_type']}): {$r['worst_score']} ({$r['worst_risk_level']})\n";
                $hasCriticalDimensions = true;
            }
        }
        if (!$hasCriticalDimensions) {
            $prompt .= "- Ninguna dimensión en riesgo alto o muy alto\n";
        }

        // Agregar contexto del consultor si existe
        if (!empty($batteryService['global_conclusion_prompt'])) {
            $prompt .= "\n=== CONTEXTO ADICIONAL DEL CONSULTOR (muy importante, integra esto en tu análisis) ===\n";
            $prompt .= $batteryService['global_conclusion_prompt'] . "\n";
        }

        $prompt .= "\n=== ESTRUCTURA DE LA CONCLUSIÓN ===\n";
        $prompt .= "1. PANORAMA GENERAL: Estado global de riesgo psicosocial de la organización\n";
        $prompt .= "2. FACTORES CRÍTICOS: Los 2-3 aspectos más preocupantes que requieren intervención inmediata\n";
        $prompt .= "3. FACTORES PROTECTORES: Aspectos positivos que la organización debe mantener\n";
        $prompt .= "4. RECOMENDACIÓN PRINCIPAL: La acción más importante a tomar\n\n";

        $prompt .= "=== INSTRUCCIONES FINALES ===\n";
        $prompt .= "- NO uses listas con viñetas, escribe en prosa fluida\n";
        $prompt .= "- NO repitas los puntajes numéricos, interpreta su significado\n";
        $prompt .= "- Fundamenta en la normativa colombiana (Res. 2764/2022)\n";
        $prompt .= "- El tono debe ser de un profesional SST presentando conclusiones a la alta dirección\n";

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
                        ['role' => 'system', 'content' => 'Eres un experto en SST de Colombia especializado en riesgo psicosocial. Escribes conclusiones ejecutivas profesionales.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.7,
                    'max_tokens'  => 2000,
                ],
                'timeout' => 90,
            ]);

            $data = json_decode($response->getBody(), true);

            if (isset($data['choices'][0]['message']['content'])) {
                return [
                    'success'  => true,
                    'analysis' => $data['choices'][0]['message']['content'],
                    'model'    => $data['model'] ?? 'gpt-4',
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
     * Guardar conclusión editada manualmente (AJAX)
     */
    public function saveConclusion()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request']);
        }

        $batteryServiceId = (int) $this->request->getPost('battery_service_id');
        $conclusion = $this->request->getPost('conclusion');

        $result = $this->batteryServiceModel->update($batteryServiceId, [
            'global_conclusion_text' => $conclusion,
        ]);

        return $this->response->setJSON([
            'success' => $result,
            'message' => $result ? 'Conclusión guardada' : 'Error al guardar',
        ]);
    }
}
