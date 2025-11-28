<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'name'        => 'superadmin',
                'description' => 'Superadministrador con acceso total al sistema',
                'permissions' => json_encode([
                    'users' => ['create', 'read', 'update', 'delete'],
                    'companies' => ['create', 'read', 'update', 'delete'],
                    'battery_services' => ['create', 'read', 'update', 'delete'],
                    'reports' => ['create', 'read', 'update', 'delete'],
                    'settings' => ['manage'],
                ]),
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'name'        => 'consultor',
                'description' => 'Consultor de riesgo psicosocial',
                'permissions' => json_encode([
                    'companies' => ['create', 'read', 'update'],
                    'battery_services' => ['create', 'read', 'update', 'delete'],
                    'workers' => ['create', 'read', 'update', 'delete'],
                    'reports' => ['create', 'read', 'update'],
                ]),
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'name'        => 'cliente_gestor',
                'description' => 'Cliente gestor multicompañía (Administradora SG-SST)',
                'permissions' => json_encode([
                    'reports' => ['read'],
                ]),
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'name'        => 'cliente_empresa',
                'description' => 'Cliente empresa individual',
                'permissions' => json_encode([
                    'reports' => ['read'],
                    'history' => ['read'],
                ]),
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'name'        => 'director_comercial',
                'description' => 'Director Comercial - Equipo Gladiator - Gestión de órdenes de servicio y ventas',
                'permissions' => json_encode([
                    'commercial' => ['create', 'read', 'update', 'delete'],
                    'companies' => ['create', 'read', 'update'],
                    'battery_services' => ['create', 'read'],
                    'reports' => ['read'],
                    'satisfaction' => ['read'],
                ]),
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('roles')->insertBatch($data);
    }
}
