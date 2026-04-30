<?php

namespace App\Models;

use CodeIgniter\Model;

class BillingTransactionModel extends Model
{
    use \App\Traits\TenantScopedTrait;

    protected $table = 'billing_transactions';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'tenant_id', 'transaction_type', 'amount_cop', 'currency',
        'wompi_transaction_id', 'wompi_reference', 'wompi_status',
        'payment_method', 'description', 'metadata', 'paid_at',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $beforeInsert = ['injectTenantId'];
    protected $beforeFind   = ['scopeToTenant'];

    public function findByReference(string $reference): ?array
    {
        return $this->withoutTenantScope()
            ->where('wompi_reference', $reference)
            ->first() ?: null;
    }
}
