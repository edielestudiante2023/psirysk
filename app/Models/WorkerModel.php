<?php

namespace App\Models;

use CodeIgniter\Model;

class WorkerModel extends Model
{
    protected $table            = 'workers';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'battery_service_id',
        'email',
        'name',
        'document',
        'document_type',
        'hire_date',
        'position',
        'area',
        'phone',
        'intralaboral_type',
        'application_mode',
        'token',
        'email_sent',
        'email_sent_at',
        'started_at',
        'completed_at',
        'status',
        'non_participation_reason',
        'non_participation_notes',
        'atiende_clientes',
        'es_jefe'
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
