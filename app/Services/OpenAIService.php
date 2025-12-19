<?php

namespace App\Services;

class OpenAIService
{
    private string $apiKey;
    private string $model;
    private string $apiUrl = 'https://api.openai.com/v1/chat/completions';

    public function __construct()
    {
        $this->apiKey = env('OPENAI_API_KEY', '');
        $this->model = env('OPENAI_MODEL', 'gpt-4o');
    }

    /**
     * Generar interpretación para una dimensión/dominio/total
     * @param array $data Datos de la sección
     * @param string|null $consultantPrompt Contexto adicional del consultor para complementar el prompt
     */
    public function generateInterpretation(array $data, ?string $consultantPrompt = null): ?string
    {
        $prompt = $this->buildPrompt($data, $consultantPrompt);

        $response = $this->callOpenAI($prompt);

        return $response;
    }

    /**
     * Generar resumen ejecutivo
     * @param string|null $consultantPrompt Contexto adicional del consultor
     */
    public function generateExecutiveSummary(array $companyData, array $overallResults, array $criticalAreas, ?string $consultantPrompt = null): ?string
    {
        $prompt = $this->buildExecutiveSummaryPrompt($companyData, $overallResults, $criticalAreas, $consultantPrompt);

        return $this->callOpenAI($prompt);
    }

    /**
     * Construir prompt para interpretación de dimensión/dominio
     * @param array $data Datos de la sección
     * @param string|null $consultantPrompt Contexto adicional del consultor
     */
    private function buildPrompt(array $data, ?string $consultantPrompt = null): string
    {
        $sectionLevel = $data['section_level'] ?? 'dimension';
        $name = $data['name'] ?? '';
        $definition = $data['definition'] ?? '';
        $scoreA = $data['score_a'] ?? null;
        $riskLevelA = $data['risk_level_a'] ?? null;
        $scoreB = $data['score_b'] ?? null;
        $riskLevelB = $data['risk_level_b'] ?? null;
        $distribution = $data['distribution'] ?? [];
        $formType = $data['form_type'] ?? null;

        $riskLabels = [
            'sin_riesgo' => 'SIN RIESGO',
            'riesgo_bajo' => 'RIESGO BAJO',
            'riesgo_medio' => 'RIESGO MEDIO',
            'riesgo_alto' => 'RIESGO ALTO',
            'riesgo_muy_alto' => 'RIESGO MUY ALTO',
            'muy_bajo' => 'MUY BAJO',
            'bajo' => 'BAJO',
            'medio' => 'MEDIO',
            'alto' => 'ALTO',
            'muy_alto' => 'MUY ALTO',
        ];

        $riskLabelA = $riskLabels[$riskLevelA] ?? $riskLevelA;
        $riskLabelB = $riskLabels[$riskLevelB] ?? $riskLevelB;

        $actionByRisk = [
            'sin_riesgo' => 'MANTENER',
            'riesgo_bajo' => 'MANTENER',
            'riesgo_medio' => 'OBSERVAR',
            'riesgo_alto' => 'INTERVENIR',
            'riesgo_muy_alto' => 'INTERVENIR INMEDIATAMENTE',
            'muy_bajo' => 'MANTENER',
            'bajo' => 'MANTENER',
            'medio' => 'OBSERVAR',
            'alto' => 'INTERVENIR',
            'muy_alto' => 'INTERVENIR INMEDIATAMENTE',
        ];

        // Determinar contexto de forma
        $formContext = '';
        $formInstruction = '';
        if ($formType === 'A') {
            $formContext = "\n\nIMPORTANTE: Este texto es ESPECÍFICAMENTE para el Cuestionario FORMA A, que aplica a cargos de JEFATURA y PROFESIONALES.";
            $formInstruction = "\n8. IMPORTANTE: Menciona SIEMPRE que este análisis corresponde al Cuestionario FORMA A dirigido a cargos de jefatura y profesionales. NO menciones la Forma B.";
        } elseif ($formType === 'B') {
            $formContext = "\n\nIMPORTANTE: Este texto es ESPECÍFICAMENTE para el Cuestionario FORMA B, que aplica a cargos AUXILIARES y OPERATIVOS.";
            $formInstruction = "\n8. IMPORTANTE: Menciona SIEMPRE que este análisis corresponde al Cuestionario FORMA B dirigido a cargos auxiliares y operativos. NO menciones la Forma A.";
        }

        $prompt = "Eres un psicólogo organizacional experto en riesgo psicosocial según la Resolución 2646 de 2008 y la Batería de Instrumentos del Ministerio de Trabajo de Colombia.

Genera un texto de interpretación profesional para incluir en un informe de diagnóstico de riesgo psicosocial.

CONTEXTO:
- Tipo de sección: {$sectionLevel}
- Nombre: {$name}
- Definición: {$definition}{$formContext}

RESULTADOS:";

        if ($scoreA !== null) {
            $actionA = $actionByRisk[$riskLevelA] ?? 'EVALUAR';
            $prompt .= "
- Cuestionario Forma A (cargos de jefatura y profesionales): Puntaje {$scoreA}, clasificado como {$riskLabelA}. Acción recomendada: {$actionA}.";
        }

        if ($scoreB !== null) {
            $actionB = $actionByRisk[$riskLevelB] ?? 'EVALUAR';
            $prompt .= "
- Cuestionario Forma B (cargos auxiliares y operativos): Puntaje {$scoreB}, clasificado como {$riskLabelB}. Acción recomendada: {$actionB}.";
        }

        if (!empty($distribution)) {
            $prompt .= "

DISTRIBUCIÓN PORCENTUAL:";
            foreach ($distribution as $level => $percentage) {
                $prompt .= "
- {$level}: {$percentage}%";
            }
        }

        // Agregar contexto del consultor si existe
        $consultantContext = '';
        if (!empty($consultantPrompt)) {
            $consultantContext = "

CONTEXTO ADICIONAL DEL CONSULTOR (muy importante, considera esto en tu análisis):
{$consultantPrompt}";
        }

        $prompt .= "{$consultantContext}

INSTRUCCIONES:
1. Redacta en tercera persona, tono técnico pero comprensible.
2. Menciona los puntajes exactos y la clasificación de riesgo.
3. Indica claramente la acción recomendada (MANTENER, OBSERVAR, INTERVENIR).
4. NO uses bullet points ni listas. Escribe en párrafos fluidos.
5. Longitud: 2-3 párrafos máximo.
6. NO menciones que eres una IA ni que este texto fue generado automáticamente.
7. El texto debe sonar como si lo escribiera un consultor humano experto.{$formInstruction}
8. Si hay contexto adicional del consultor, intégralo naturalmente en tu análisis.

Genera el texto de interpretación:";

        return $prompt;
    }

    /**
     * Construir prompt para resumen ejecutivo
     * @param string|null $consultantPrompt Contexto adicional del consultor
     */
    private function buildExecutiveSummaryPrompt(array $companyData, array $overallResults, array $criticalAreas, ?string $consultantPrompt = null): string
    {
        $companyName = $companyData['name'] ?? 'la empresa';
        $totalWorkers = $companyData['total_workers'] ?? 0;
        $evaluationDate = $companyData['evaluation_date'] ?? date('Y-m-d');

        $generalScore = $overallResults['general_score'] ?? 0;
        $generalRisk = $overallResults['general_risk'] ?? 'sin_riesgo';
        $intralaboralScore = $overallResults['intralaboral_score'] ?? 0;
        $extralaboralScore = $overallResults['extralaboral_score'] ?? 0;
        $stressScore = $overallResults['stress_score'] ?? 0;

        $riskLabels = [
            'sin_riesgo' => 'SIN RIESGO',
            'riesgo_bajo' => 'RIESGO BAJO',
            'riesgo_medio' => 'RIESGO MEDIO',
            'riesgo_alto' => 'RIESGO ALTO',
            'riesgo_muy_alto' => 'RIESGO MUY ALTO',
        ];

        $generalRiskLabel = $riskLabels[$generalRisk] ?? $generalRisk;

        $criticalAreasText = '';
        if (!empty($criticalAreas)) {
            foreach ($criticalAreas as $area) {
                $criticalAreasText .= "- {$area['name']}: {$area['score']} puntos ({$area['risk_level']})\n";
            }
        }

        // Contexto adicional del consultor
        $consultantContext = '';
        if (!empty($consultantPrompt)) {
            $consultantContext = "
CONTEXTO ADICIONAL DEL CONSULTOR (muy importante, considera esto en tu análisis):
{$consultantPrompt}
";
        }

        $prompt = "Eres un psicólogo organizacional experto en riesgo psicosocial según la Resolución 2646 de 2008 de Colombia.

Genera un RESUMEN EJECUTIVO para el informe de diagnóstico de riesgo psicosocial.

DATOS DE LA EMPRESA:
- Nombre: {$companyName}
- Total de trabajadores evaluados: {$totalWorkers}
- Fecha de evaluación: {$evaluationDate}

RESULTADOS GENERALES:
- Puntaje total general: {$generalScore} puntos
- Clasificación general: {$generalRiskLabel}
- Puntaje intralaboral: {$intralaboralScore}
- Puntaje extralaboral: {$extralaboralScore}
- Puntaje estrés: {$stressScore}

ÁREAS CRÍTICAS (Riesgo Alto y Muy Alto):
{$criticalAreasText}
{$consultantContext}
INSTRUCCIONES:
1. Este resumen es para GERENTES que solo quieren la conclusión final.
2. Máximo 3 párrafos: situación actual, áreas críticas, recomendación general.
3. Sé directo y conciso. Los gerentes no tienen tiempo para detalles.
4. Menciona el nivel de riesgo general y qué significa para la empresa.
5. Lista las áreas que requieren intervención prioritaria.
6. Indica si se requiere programa de vigilancia epidemiológica.
7. NO uses jerga técnica excesiva. Debe ser entendible para no psicólogos.
8. NO menciones que eres una IA.
9. Si hay contexto adicional del consultor, intégralo naturalmente en tu análisis.

Genera el resumen ejecutivo:";

        return $prompt;
    }

    /**
     * Llamar a la API de OpenAI
     */
    private function callOpenAI(string $prompt): ?string
    {
        if (empty($this->apiKey) || $this->apiKey === 'sk-tu-api-key-aqui') {
            log_message('error', 'OpenAI API Key no configurada');
            return null;
        }

        $data = [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
            'temperature' => 0.7,
            'max_tokens' => 1000,
        ];

        $ch = curl_init($this->apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey,
            ],
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_TIMEOUT => 60,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            log_message('error', 'OpenAI cURL Error: ' . $error);
            return null;
        }

        if ($httpCode !== 200) {
            log_message('error', 'OpenAI API Error: HTTP ' . $httpCode . ' - ' . $response);
            return null;
        }

        $result = json_decode($response, true);

        if (isset($result['choices'][0]['message']['content'])) {
            return trim($result['choices'][0]['message']['content']);
        }

        log_message('error', 'OpenAI unexpected response format: ' . $response);
        return null;
    }

    /**
     * Verificar si el servicio está configurado correctamente
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey) && $this->apiKey !== 'sk-tu-api-key-aqui';
    }
}
