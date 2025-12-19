<?php

namespace App\Models;

use CodeIgniter\Model;

class BatteryServiceModel extends Model
{
    protected $table            = 'battery_services';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'company_id',
        'consultant_id',
        'notify_parent_company',
        'service_name',
        'service_date',
        'link_expiration_date',
        'includes_intralaboral',
        'includes_extralaboral',
        'includes_estres',
        'cantidad_forma_a',
        'cantidad_forma_b',
        'status',
        'closed_at',
        'closed_by',
        'closure_notes',
        'min_participation_percent',
        'satisfaction_survey_completed',
        // Campos para Conclusión Total RPS (Máximo Riesgo)
        'global_conclusion_prompt',
        'global_conclusion_text',
        'global_conclusion_generated_at',
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
}
