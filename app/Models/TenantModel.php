<?php

namespace App\Models;

use CodeIgniter\Model;

class TenantModel extends Model
{
    protected $table            = 'tenants';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'slug',
        'legal_name',
        'trade_name',
        'nit',
        'contact_name',
        'contact_email',
        'contact_phone',
        'address',
        'city',
        'country',
        'logo_path',
        'brand_primary_color',
        'brand_secondary_color',
        'email_from_name',
        'email_from_address',
        'pdf_footer_text',
        'website_url',
        'linkedin_url',
        'plan',
        'status',
        'trial_ends_at',
        'current_period_start',
        'current_period_end',
        'credits_balance',
        'credits_included_monthly',
        'credits_used_lifetime',
        'wompi_customer_id',
        'monthly_fee_cop',
        'extra_credit_price_cop',
        'created_by',
    ];

    protected bool $allowEmptyInserts = false;

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules = [
        'slug'          => 'required|alpha_dash|min_length[2]|max_length[64]|is_unique[tenants.slug,id,{id}]',
        'legal_name'    => 'required|min_length[3]|max_length[255]',
        'trade_name'    => 'required|min_length[2]|max_length[255]',
        'nit'           => 'required|max_length[20]|is_unique[tenants.nit,id,{id}]',
        'contact_name'  => 'required|min_length[3]|max_length[255]',
        'contact_email' => 'required|valid_email|max_length[255]',
        'plan'          => 'required|in_list[inicial,profesional,empresarial,custom]',
        'status'        => 'required|in_list[trial,active,suspended,cancelled]',
    ];

    protected $validationMessages = [
        'slug' => [
            'is_unique'   => 'Este slug ya está en uso por otro tenant',
            'alpha_dash'  => 'El slug solo puede contener letras, números, guiones y guiones bajos',
        ],
        'nit' => [
            'is_unique' => 'Ya existe un tenant con este NIT',
        ],
    ];

    /**
     * Devuelve el tenant del usuario actualmente logueado, o null si es platform admin.
     */
    public function getCurrentTenant(): ?array
    {
        $tenantId = session()->get('tenant_id');
        if (!$tenantId) {
            return null;
        }
        return $this->where('status !=', 'cancelled')->find($tenantId);
    }

    /**
     * Verifica si el tenant tiene créditos disponibles para una nueva evaluación.
     */
    public function hasCredits(int $tenantId, int $needed = 1): bool
    {
        $tenant = $this->find($tenantId);
        if (!$tenant) {
            return false;
        }
        return ((int) $tenant['credits_balance']) >= $needed;
    }

    /**
     * Consume créditos de un tenant. Devuelve true si se consumió, false si saldo insuficiente.
     */
    public function consumeCredits(int $tenantId, int $amount): bool
    {
        if (!$this->hasCredits($tenantId, $amount)) {
            return false;
        }
        $db = \Config\Database::connect();
        $db->transStart();
        $db->query(
            "UPDATE tenants
             SET credits_balance = credits_balance - ?,
                 credits_used_lifetime = credits_used_lifetime + ?
             WHERE id = ? AND credits_balance >= ?",
            [$amount, $amount, $tenantId, $amount]
        );
        $db->transComplete();
        return $db->transStatus();
    }

    /**
     * Recarga créditos al inicio de un nuevo período de suscripción.
     */
    public function refillMonthlyCredits(int $tenantId): bool
    {
        $tenant = $this->find($tenantId);
        if (!$tenant) {
            return false;
        }
        return $this->update($tenantId, [
            'credits_balance'      => (int) $tenant['credits_included_monthly'],
            'current_period_start' => date('Y-m-d'),
            'current_period_end'   => date('Y-m-d', strtotime('+1 month')),
        ]);
    }

    /**
     * Tenant activo y dentro de período.
     */
    public function isActive(int $tenantId): bool
    {
        $tenant = $this->find($tenantId);
        if (!$tenant) {
            return false;
        }
        return in_array($tenant['status'], ['trial', 'active'], true);
    }
}
