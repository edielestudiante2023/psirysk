<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run()
    {
        // Insertar empresas
        $companies = [
            [
                'id'               => 1,
                'name'             => 'Gestor HSEQ SAS',
                'type'             => 'gestor_multicompania',
                'nit'              => '900123456-1',
                'address'          => 'Calle 100 # 20-30, Bogotá',
                'phone'            => '3001234567',
                'contact_name'     => 'María González',
                'contact_email'    => 'admin@gestorhseq.com',
                'parent_company_id' => null,
                'created_by'       => 2, // Eleyson (consultor)
                'status'           => 'active',
                'created_at'       => date('Y-m-d H:i:s'),
                'updated_at'       => date('Y-m-d H:i:s'),
            ],
            [
                'id'               => 2,
                'name'             => 'Construcciones ABC SAS',
                'type'             => 'empresa_individual',
                'nit'              => '800234567-2',
                'address'          => 'Carrera 15 # 85-40, Bogotá',
                'phone'            => '3109876543',
                'contact_name'     => 'Carlos Ramírez',
                'contact_email'    => 'rrhh@construccionesabc.com',
                'parent_company_id' => 1, // Gestionada por Gestor HSEQ
                'created_by'       => 2,
                'status'           => 'active',
                'created_at'       => date('Y-m-d H:i:s'),
                'updated_at'       => date('Y-m-d H:i:s'),
            ],
            [
                'id'               => 3,
                'name'             => 'Logística XYZ Ltda',
                'type'             => 'empresa_individual',
                'nit'              => '700345678-3',
                'address'          => 'Avenida 68 # 50-20, Medellín',
                'phone'            => '3158765432',
                'contact_name'     => 'Ana López',
                'contact_email'    => 'talento@logisticaxyz.com',
                'parent_company_id' => 1, // Gestionada por Gestor HSEQ
                'created_by'       => 2,
                'status'           => 'active',
                'created_at'       => date('Y-m-d H:i:s'),
                'updated_at'       => date('Y-m-d H:i:s'),
            ],
            [
                'id'               => 4,
                'name'             => 'Manufactura Industrial SAS',
                'type'             => 'empresa_individual',
                'nit'              => '600456789-4',
                'address'          => 'Zona Industrial Calle 13 # 45-10, Cali',
                'phone'            => '3201234567',
                'contact_name'     => 'Pedro Martínez',
                'contact_email'    => 'admin@manufactura.com',
                'parent_company_id' => null, // Cliente directo
                'created_by'       => 2,
                'status'           => 'active',
                'created_at'       => date('Y-m-d H:i:s'),
                'updated_at'       => date('Y-m-d H:i:s'),
            ],
        ];

        // Solo insertar si la tabla está vacía
        $countCompanies = $this->db->table('companies')->countAllResults();
        if ($countCompanies == 0) {
            $this->db->table('companies')->insertBatch($companies);
            echo "CompanySeeder: " . count($companies) . " empresas insertadas.\n";
        } else {
            echo "CompanySeeder: Tabla companies no vacía, omitiendo ($countCompanies registros existentes).\n";
            return; // No insertar relaciones si ya hay empresas
        }

        // Insertar relaciones company_users
        $companyUsers = [
            [
                'user_id'    => 3, // María González (cliente_gestor)
                'company_id' => 1, // Gestor HSEQ
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'user_id'    => 4, // Carlos Ramírez (cliente_empresa)
                'company_id' => 2, // Construcciones ABC
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'user_id'    => 5, // Ana López (cliente_empresa)
                'company_id' => 3, // Logística XYZ
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('company_users')->insertBatch($companyUsers);
        echo "CompanySeeder: " . count($companyUsers) . " relaciones company_users insertadas.\n";
    }
}
