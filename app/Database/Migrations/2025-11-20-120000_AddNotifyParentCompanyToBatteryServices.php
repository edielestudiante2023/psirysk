<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNotifyParentCompanyToBatteryServices extends Migration
{
    public function up()
    {
        $fields = [
            'notify_parent_company' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'null' => false,
                'after' => 'consultant_id',
                'comment' => 'Si debe notificar a la empresa gestora (1=sí, 0=no)'
            ],
        ];

        $this->forge->addColumn('battery_services', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('battery_services', 'notify_parent_company');
    }
}
