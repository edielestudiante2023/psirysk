<?php

namespace App\Models;

use CodeIgniter\Model;

class SatisfactionSurveyModel extends Model
{
    protected $table = 'service_satisfaction_surveys';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'service_id',
        'user_id',
        'question_1',
        'question_2',
        'question_3',
        'question_4',
        'question_5',
        'comments',
        'completed_at',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [
        'service_id' => 'required|integer',
        'user_id' => 'required|integer',
        'question_1' => 'required|integer|in_list[1,2,3,4,5]',
        'question_2' => 'required|integer|in_list[1,2,3,4,5]',
        'question_3' => 'required|integer|in_list[1,2,3,4,5]',
        'question_4' => 'required|integer|in_list[1,2,3,4,5]',
        'question_5' => 'required|integer|in_list[1,2,3,4,5]',
        'comments' => 'permit_empty|string|max_length[5000]',
    ];
    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    /**
     * Verificar si un servicio ya tiene encuesta de satisfacción completada
     */
    public function isCompletedForService(int $serviceId): bool
    {
        return $this->where('service_id', $serviceId)->countAllResults() > 0;
    }

    /**
     * Obtener encuesta de un servicio
     */
    public function getByService(int $serviceId)
    {
        return $this->where('service_id', $serviceId)->first();
    }

    /**
     * Calcular promedio de satisfacción de un servicio
     */
    public function getAverageScore(int $serviceId): ?float
    {
        $survey = $this->getByService($serviceId);

        if (!$survey) {
            return null;
        }

        $total = $survey['question_1'] + $survey['question_2'] +
                 $survey['question_3'] + $survey['question_4'] +
                 $survey['question_5'];

        return round($total / 5, 2);
    }

    /**
     * Obtener estadísticas de satisfacción por empresa
     */
    public function getCompanyStats(int $companyId): array
    {
        $builder = $this->db->table($this->table . ' s')
            ->select('
                COUNT(s.id) as total_surveys,
                AVG((s.question_1 + s.question_2 + s.question_3 + s.question_4 + s.question_5) / 5) as average_score,
                AVG(s.question_1) as avg_q1,
                AVG(s.question_2) as avg_q2,
                AVG(s.question_3) as avg_q3,
                AVG(s.question_4) as avg_q4,
                AVG(s.question_5) as avg_q5
            ')
            ->join('battery_services bs', 'bs.id = s.service_id')
            ->where('bs.company_id', $companyId);

        return $builder->get()->getRowArray() ?? [];
    }
}
