<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCompanyIdToUsers extends Migration
{
    public function up()
    {
        $fields = [
            'company_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'role_id',
            ],
        ];

        $this->forge->addColumn('users', $fields);

        // Agregar foreign key
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'SET NULL', 'CASCADE', 'users_company_id_foreign');
    }

    public function down()
    {
        // Eliminar foreign key primero
        $this->forge->dropForeignKey('users', 'users_company_id_foreign');

        // Eliminar columna
        $this->forge->dropColumn('users', 'company_id');
    }
}
