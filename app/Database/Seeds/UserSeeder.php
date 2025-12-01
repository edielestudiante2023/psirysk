<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'email'      => 'edison.cuervo@cycloidtalent.com',
                'password'   => password_hash('Admin123*', PASSWORD_DEFAULT),
                'role_id'    => 1, // superadmin
                'name'       => 'Edison Cuervo',
                'phone'      => null,
                'status'     => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'email'      => 'eleyson.segura@cycloidtalent.com',
                'password'   => password_hash('3227145322', PASSWORD_DEFAULT),
                'role_id'    => 2, // consultor
                'name'       => 'Eleyson Segura',
                'phone'      => null,
                'status'     => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'email'      => 'admin@gestorhseq.com',
                'password'   => password_hash('Gestor2024*', PASSWORD_DEFAULT),
                'role_id'    => 3, // cliente_gestor
                'company_id' => 1, // Gestor HSEQ SAS
                'name'       => 'María González',
                'phone'      => null,
                'status'     => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'email'      => 'rrhh@construccionesabc.com',
                'password'   => password_hash('Cliente2024*', PASSWORD_DEFAULT),
                'role_id'    => 4, // cliente_empresa
                'company_id' => 2, // Construcciones ABC
                'name'       => 'Carlos Ramírez',
                'phone'      => null,
                'status'     => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'email'      => 'talento@logisticaxyz.com',
                'password'   => password_hash('Cliente2024*', PASSWORD_DEFAULT),
                'role_id'    => 4, // cliente_empresa
                'company_id' => 3, // Logística XYZ
                'name'       => 'Ana López',
                'phone'      => null,
                'status'     => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'email'      => 'comercial@cycloidtalent.com',
                'password'   => password_hash('Gladiator2024*', PASSWORD_DEFAULT),
                'role_id'    => 5, // director_comercial
                'name'       => 'Director Comercial',
                'phone'      => null,
                'status'     => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        // Solo insertar si la tabla está vacía
        $count = $this->db->table('users')->countAllResults();
        if ($count == 0) {
            $this->db->table('users')->insertBatch($data);
            echo "UserSeeder: " . count($data) . " usuarios insertados.\n";
        } else {
            echo "UserSeeder: Tabla no vacía, omitiendo ($count registros existentes).\n";
        }
    }
}
