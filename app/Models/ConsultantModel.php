<?php

namespace App\Models;

use CodeIgniter\Model;

class ConsultantModel extends Model
{
    protected $table = 'consultants';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'nombre_completo',
        'tipo_documento',
        'numero_documento',
        'licencia_sst',
        'cargo',
        'email',
        'telefono',
        'website',
        'linkedin',
        'firma_path',
        'activo',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'nombre_completo' => 'required|min_length[3]|max_length[150]',
        'tipo_documento' => 'required|in_list[CC,CE,PAS,OTRO]',
        'numero_documento' => 'required|max_length[30]',
        'email' => 'permit_empty|valid_email|max_length[150]',
    ];

    protected $validationMessages = [
        'nombre_completo' => [
            'required' => 'El nombre completo es obligatorio',
            'min_length' => 'El nombre debe tener al menos 3 caracteres',
        ],
        'numero_documento' => [
            'required' => 'El número de documento es obligatorio',
        ],
        'email' => [
            'valid_email' => 'El email no es válido',
        ],
    ];

    /**
     * Obtiene consultores activos para dropdown
     */
    public function getActiveForDropdown()
    {
        return $this->where('activo', 1)
            ->orderBy('nombre_completo', 'ASC')
            ->findAll();
    }
}
