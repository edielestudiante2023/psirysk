<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFormaABQuantitiesToBatteryServices extends Migration
{
    public function up()
    {
        $fields = [
            'cantidad_forma_a' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'null' => false,
            ],
            'cantidad_forma_b' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'null' => false,
            ],
        ];

        $this->forge->addColumn('battery_services', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('battery_services', ['cantidad_forma_a', 'cantidad_forma_b']);
    }
}
