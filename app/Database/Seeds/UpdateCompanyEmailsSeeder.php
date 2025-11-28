<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UpdateCompanyEmailsSeeder extends Seeder
{
    public function run()
    {
        // Update Gestor Multicompañía email
        $this->db->table('companies')
            ->where('type', 'gestor_multicompania')
            ->limit(1)
            ->update(['contact_email' => 'cycloidtalent@gmail.com']);

        echo "✓ Email de empresa gestor actualizado\n";

        // Update first individual company email (child of gestor)
        $gestor = $this->db->table('companies')
            ->where('type', 'gestor_multicompania')
            ->get()
            ->getFirstRow();

        if ($gestor) {
            $this->db->table('companies')
                ->where('type', 'empresa_individual')
                ->where('parent_company_id', $gestor->id)
                ->limit(1)
                ->update(['contact_email' => 'head.consultant.cycloidtalent@gmail.com']);

            echo "✓ Email de empresa individual actualizado\n";
        }
    }
}
