<?php

namespace App\Models;

use CodeIgniter\Model;

class WorkerDemographicsModel extends Model
{
    protected $table            = 'worker_demographics';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'worker_id',
        'gender',
        'birth_year',
        'marital_status',
        'education_level',
        'occupation',
        'city_residence',
        'department_residence',
        'stratum',
        'housing_type',
        'dependents',
        'city_work',
        'department_work',
        'time_in_company_type',
        'time_in_company_years',
        'position_name',
        'position_type',
        'time_in_position_type',
        'time_in_position_years',
        'department',
        'contract_type',
        'hours_per_day',
        'salary_type',
        'completed_at'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'worker_id' => 'required|integer|is_unique[worker_demographics.worker_id,id,{id}]',
        'gender' => 'permit_empty|in_list[Masculino,Femenino]',
        'birth_year' => 'permit_empty|integer',
        'marital_status' => 'permit_empty',
        'education_level' => 'permit_empty',
        'stratum' => 'permit_empty',
        'housing_type' => 'permit_empty',
        'dependents' => 'permit_empty|integer',
        'contract_type' => 'permit_empty',
        'position_type' => 'permit_empty',
        'hours_per_day' => 'permit_empty|numeric',
        'salary_type' => 'permit_empty'
    ];

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
     * Get demographics data for a specific worker
     */
    public function getByWorkerId($workerId)
    {
        return $this->where('worker_id', $workerId)->first();
    }

    /**
     * Check if worker has completed demographics form
     */
    public function isCompleted($workerId)
    {
        $demo = $this->where('worker_id', $workerId)->first();
        return $demo && $demo['completed_at'] !== null;
    }

    /**
     * Mark demographics form as completed
     */
    public function markAsCompleted($workerId)
    {
        $demo = $this->where('worker_id', $workerId)->first();
        if ($demo) {
            return $this->update($demo['id'], ['completed_at' => date('Y-m-d H:i:s')]);
        }
        return false;
    }
}
