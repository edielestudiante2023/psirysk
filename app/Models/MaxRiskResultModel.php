<?php

namespace App\Models;

use CodeIgniter\Model;

class MaxRiskResultModel extends Model
{
    protected $table            = 'max_risk_results';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'battery_service_id',
        'element_type',
        'questionnaire_type',
        'element_code',
        'element_name',
        'worst_score',
        'worst_risk_level',
        'worst_form',
        'form_a_score',
        'form_a_risk_level',
        'form_a_count',
        'form_b_score',
        'form_b_risk_level',
        'form_b_count',
        'has_both_forms',
        'risk_priority',
        'ai_analysis',
        'ai_recommendations',
        'ai_generated_at',
        'ai_model_version',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'worst_score'    => 'float',
        'form_a_score'   => '?float',
        'form_b_score'   => '?float',
        'form_a_count'   => '?int',
        'form_b_count'   => '?int',
        'has_both_forms' => 'boolean',
        'risk_priority'  => 'int',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'battery_service_id' => 'required|integer',
        'element_type'       => 'required|in_list[total,domain,dimension]',
        'questionnaire_type' => 'required|in_list[intralaboral,extralaboral,estres]',
        'element_name'       => 'required|max_length[200]',
        'worst_score'        => 'required|decimal',
        'worst_risk_level'   => 'required|max_length[20]',
        'worst_form'         => 'required|in_list[A,B]',
    ];

    protected $validationMessages = [];
    protected $skipValidation     = false;

    /**
     * Obtener todos los resultados de máximo riesgo de un servicio
     */
    public function getByBatteryService(int $batteryServiceId): array
    {
        return $this->where('battery_service_id', $batteryServiceId)
                    ->orderBy('questionnaire_type', 'ASC')
                    ->orderBy('element_type', 'ASC')
                    ->orderBy('element_code', 'ASC')
                    ->findAll();
    }

    /**
     * Obtener elementos con riesgo alto o muy alto (para IA)
     */
    public function getHighRiskElements(int $batteryServiceId): array
    {
        return $this->where('battery_service_id', $batteryServiceId)
                    ->whereIn('worst_risk_level', ['alto', 'muy_alto', 'riesgo_alto', 'riesgo_muy_alto'])
                    ->orderBy('risk_priority', 'ASC')
                    ->findAll();
    }

    /**
     * Obtener elementos pendientes de análisis IA
     */
    public function getPendingAiAnalysis(int $batteryServiceId): array
    {
        return $this->where('battery_service_id', $batteryServiceId)
                    ->whereIn('worst_risk_level', ['alto', 'muy_alto', 'riesgo_alto', 'riesgo_muy_alto'])
                    ->where('ai_analysis IS NULL')
                    ->orderBy('risk_priority', 'ASC')
                    ->findAll();
    }

    /**
     * Obtener resultados por tipo de cuestionario
     */
    public function getByQuestionnaire(int $batteryServiceId, string $questionnaireType): array
    {
        return $this->where('battery_service_id', $batteryServiceId)
                    ->where('questionnaire_type', $questionnaireType)
                    ->orderBy('element_type', 'ASC')
                    ->orderBy('element_code', 'ASC')
                    ->findAll();
    }

    /**
     * Obtener solo totales
     */
    public function getTotals(int $batteryServiceId): array
    {
        return $this->where('battery_service_id', $batteryServiceId)
                    ->where('element_type', 'total')
                    ->findAll();
    }

    /**
     * Obtener solo dominios
     */
    public function getDomains(int $batteryServiceId): array
    {
        return $this->where('battery_service_id', $batteryServiceId)
                    ->where('element_type', 'domain')
                    ->findAll();
    }

    /**
     * Obtener solo dimensiones
     */
    public function getDimensions(int $batteryServiceId): array
    {
        return $this->where('battery_service_id', $batteryServiceId)
                    ->where('element_type', 'dimension')
                    ->findAll();
    }

    /**
     * Actualizar análisis de IA
     */
    public function updateAiAnalysis(int $id, string $analysis, ?string $recommendations = null, ?string $modelVersion = null): bool
    {
        return $this->update($id, [
            'ai_analysis'        => $analysis,
            'ai_recommendations' => $recommendations,
            'ai_generated_at'    => date('Y-m-d H:i:s'),
            'ai_model_version'   => $modelVersion,
        ]);
    }

    /**
     * Eliminar todos los resultados de un servicio (para recalcular)
     */
    public function deleteByBatteryService(int $batteryServiceId): bool
    {
        return $this->where('battery_service_id', $batteryServiceId)->delete();
    }

    /**
     * Verificar si ya existen resultados para un servicio
     */
    public function existsForBatteryService(int $batteryServiceId): bool
    {
        return $this->where('battery_service_id', $batteryServiceId)->countAllResults() > 0;
    }

    /**
     * Obtener estadísticas de riesgo del servicio
     */
    public function getRiskStats(int $batteryServiceId): array
    {
        $results = $this->where('battery_service_id', $batteryServiceId)->findAll();

        $stats = [
            'total_elements'  => count($results),
            'muy_alto'        => 0,
            'alto'            => 0,
            'medio'           => 0,
            'bajo'            => 0,
            'sin_riesgo'      => 0,
            'critical_count'  => 0,  // alto + muy_alto
        ];

        foreach ($results as $result) {
            $nivel = $result['worst_risk_level'];

            if (in_array($nivel, ['muy_alto', 'riesgo_muy_alto'])) {
                $stats['muy_alto']++;
                $stats['critical_count']++;
            } elseif (in_array($nivel, ['alto', 'riesgo_alto'])) {
                $stats['alto']++;
                $stats['critical_count']++;
            } elseif (in_array($nivel, ['medio', 'riesgo_medio'])) {
                $stats['medio']++;
            } elseif (in_array($nivel, ['bajo', 'riesgo_bajo'])) {
                $stats['bajo']++;
            } else {
                $stats['sin_riesgo']++;
            }
        }

        return $stats;
    }

    /**
     * Obtener elemento específico por código
     */
    public function getByElementCode(int $batteryServiceId, string $elementCode): ?array
    {
        return $this->where('battery_service_id', $batteryServiceId)
                    ->where('element_code', $elementCode)
                    ->first();
    }
}
