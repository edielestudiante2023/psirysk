<?php

namespace App\Models;

use CodeIgniter\Model;

class ActionPlanModel extends Model
{
    protected $table            = 'action_plans';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'dimension_code',
        'dimension_name',
        'domain_code',
        'questionnaire_type',
        'introduction',
        'objectives',
        'activities_6months',
        'bibliography'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'objectives' => 'json-array',
        'activities_6months' => 'json-array',
        'bibliography' => 'json-array'
    ];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Get action plan by dimension code
     */
    public function getByDimension($dimensionCode)
    {
        return $this->where('dimension_code', $dimensionCode)->first();
    }

    /**
     * Get all action plans for a questionnaire type
     */
    public function getByQuestionnaireType($type)
    {
        return $this->where('questionnaire_type', $type)->findAll();
    }

    /**
     * Get action plans for dimensions in risk (medium, high, very_high)
     * Based on calculated results
     */
    public function getRecommendationsForWorker($workerId)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('calculated_results cr');
        $builder->select('cr.*');
        $builder->where('cr.worker_id', $workerId);

        $results = $builder->get()->getRowArray();

        if (!$results) {
            return [];
        }

        $recommendations = [];

        // Check all dimensions and domains for medium/high/very_high risk
        // This will be populated dynamically based on risk levels

        return $recommendations;
    }
}
