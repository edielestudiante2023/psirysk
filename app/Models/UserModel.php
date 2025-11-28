<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['email', 'password', 'role_id', 'company_id', 'name', 'phone', 'status', 'last_login'];

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
        'email'    => 'required|valid_email|is_unique[users.email,id,{id}]',
        'password' => 'required|min_length[6]',
        'role_id'  => 'required|integer',
        'name'     => 'required|min_length[3]',
    ];
    protected $validationMessages   = [
        'email' => [
            'required'    => 'El email es obligatorio',
            'valid_email' => 'Debe proporcionar un email válido',
            'is_unique'   => 'Este email ya está registrado',
        ],
        'password' => [
            'required'   => 'La contraseña es obligatoria',
            'min_length' => 'La contraseña debe tener al menos 6 caracteres',
        ],
        'name' => [
            'required'   => 'El nombre es obligatorio',
            'min_length' => 'El nombre debe tener al menos 3 caracteres',
        ],
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['hashPassword'];
    protected $afterInsert    = [];
    protected $beforeUpdate   = ['hashPassword'];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    protected function hashPassword(array $data)
    {
        if (isset($data['data']['password'])) {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        }
        return $data;
    }

    public function getUserWithRole($userId)
    {
        return $this->select('users.*, roles.name as role_name')
                    ->join('roles', 'roles.id = users.role_id')
                    ->where('users.id', $userId)
                    ->first();
    }

    public function getUserByEmail($email)
    {
        return $this->select('users.*, roles.name as role_name')
                    ->join('roles', 'roles.id = users.role_id')
                    ->where('users.email', $email)
                    ->first();
    }
}
