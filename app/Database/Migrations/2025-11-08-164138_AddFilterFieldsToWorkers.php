<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFilterFieldsToWorkers extends Migration
{
    public function up()
    {
        $fields = [
            'atiende_clientes' => [
                'type'       => 'BOOLEAN',
                'null'       => true,
                'default'    => null,
                'comment'    => 'Filtro para preguntas 106-114 de Intralaboral A',
                'after'      => 'status'
            ],
            'es_jefe' => [
                'type'       => 'BOOLEAN',
                'null'       => true,
                'default'    => null,
                'comment'    => 'Filtro para preguntas 115-123 de Intralaboral A',
                'after'      => 'atiende_clientes'
            ],
        ];

        $this->forge->addColumn('workers', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('workers', ['atiende_clientes', 'es_jefe']);
    }
}
