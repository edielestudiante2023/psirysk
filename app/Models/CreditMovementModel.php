<?php

namespace App\Models;

use CodeIgniter\Model;

class CreditMovementModel extends Model
{
    use \App\Traits\TenantScopedTrait;

    protected $table = 'credit_movements';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'tenant_id', 'movement_type', 'amount', 'balance_after',
        'source', 'reference_type', 'reference_id', 'notes', 'created_by',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = '';

    protected $beforeInsert = ['injectTenantId'];
    protected $beforeFind   = ['scopeToTenant'];
}
