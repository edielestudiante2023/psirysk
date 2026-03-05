<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEnlaceAccesoToBatteryServices extends Migration
{
    public function up()
    {
        $this->forge->addColumn('battery_services', [
            'enlace_acceso' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'unique'     => true,
                'null'       => true,
                'default'    => null,
                'after'      => 'status',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('battery_services', 'enlace_acceso');
    }
}
