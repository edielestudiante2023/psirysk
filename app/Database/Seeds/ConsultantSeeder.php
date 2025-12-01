<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seeder para consultores del sistema
 *
 * Carga el consultor principal de Cycloid Talent
 * Este seeder es CRÍTICO para producción ya que los PDFs
 * requieren datos del consultor para la firma
 */
class ConsultantSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'nombre_completo' => 'EDISON ERNESTO CUERVO SALAZAR',
                'tipo_documento' => 'CC',
                'numero_documento' => '80039147',
                'licencia_sst' => 'RESOLUCIÓN No. 4241 de 19/08/2022',
                'cargo' => 'Psicologo, Especialista en Gerencia de la Seguridad y Salud en el Trabajo',
                'email' => 'edison.cuervo@cycloidtalent.com',
                'telefono' => '3132123799',
                'website' => 'https://cycloidtalent.com/',
                'linkedin' => 'https://www.linkedin.com/in/edison-cuervo-224a68b6/',
                'firma_path' => null, // Se sube manualmente en producción
                'activo' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        // Solo insertar si la tabla está vacía
        $count = $this->db->table('consultants')->countAllResults();
        if ($count == 0) {
            $this->db->table('consultants')->insertBatch($data);
            echo "ConsultantSeeder: " . count($data) . " consultor(es) insertado(s).\n";
        } else {
            echo "ConsultantSeeder: Tabla no vacía, omitiendo ($count registros existentes).\n";
        }
    }
}
