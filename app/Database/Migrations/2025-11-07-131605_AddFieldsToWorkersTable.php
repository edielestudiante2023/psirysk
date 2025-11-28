<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFieldsToWorkersTable extends Migration
{
    public function up()
    {
        $fields = [
            'document_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
                'after'      => 'document',
            ],
            'hire_date' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'document_type',
            ],
            'area' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'position',
            ],
            'phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'after'      => 'email',
            ],
        ];

        $this->forge->addColumn('workers', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('workers', ['document_type', 'hire_date', 'area', 'phone']);
    }
}
