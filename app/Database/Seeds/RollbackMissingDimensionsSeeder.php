<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RollbackMissingDimensionsSeeder extends Seeder
{
    public function run()
    {
        // Delete incorrectly formatted records
        $this->db->table('action_plans')
            ->whereIn('dimension_code', [
                'relaciones_sociales_trabajo',
                'retroalimentacion_desempeno',
                'relacion_colaboradores',
                'claridad_rol'
            ])
            ->delete();

        echo "Deleted 4 incorrectly formatted dimension records.\n";
    }
}
