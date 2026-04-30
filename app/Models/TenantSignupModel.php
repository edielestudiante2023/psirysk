<?php

namespace App\Models;

use CodeIgniter\Model;

class TenantSignupModel extends Model
{
    protected $table         = 'tenant_signups';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'legal_name', 'trade_name', 'nit', 'contact_name', 'contact_email',
        'contact_phone', 'password_hash', 'plan',
        'verification_token', 'verification_expires_at',
        'status', 'verified_at', 'verified_ip', 'tenant_id',
        'signup_ip', 'signup_user_agent',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'legal_name'    => 'required|min_length[3]|max_length[255]',
        'nit'           => 'required|max_length[20]',
        'contact_name'  => 'required|min_length[3]|max_length[255]',
        'contact_email' => 'required|valid_email|max_length[255]',
        'plan'          => 'required|in_list[inicial,profesional,empresarial]',
    ];

    public function findByToken(string $token): ?array
    {
        $hashed = hash('sha256', $token);
        return $this->where('verification_token', $hashed)
                    ->where('status', 'pending')
                    ->where('verification_expires_at >=', date('Y-m-d H:i:s'))
                    ->first() ?: null;
    }

    public function emailAlreadyUsed(string $email): bool
    {
        $tenant = (new TenantModel())->withoutTenantScope()
            ->where('contact_email', $email)->first();
        if ($tenant) return true;

        $pending = $this->where('contact_email', $email)
            ->where('status', 'pending')
            ->where('verification_expires_at >=', date('Y-m-d H:i:s'))
            ->first();
        return (bool) $pending;
    }

    public function nitAlreadyUsed(string $nit): bool
    {
        $tenant = (new TenantModel())->withoutTenantScope()
            ->where('nit', $nit)->first();
        return (bool) $tenant;
    }
}
