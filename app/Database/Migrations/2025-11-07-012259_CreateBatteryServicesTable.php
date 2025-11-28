<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBatteryServicesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'company_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'consultant_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'service_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'service_date' => [
                'type' => 'DATE',
            ],
            'link_expiration_date' => [
                'type' => 'DATE',
            ],
            'includes_intralaboral' => [
                'type'    => 'BOOLEAN',
                'default' => true,
            ],
            'includes_extralaboral' => [
                'type'    => 'BOOLEAN',
                'default' => true,
            ],
            'includes_estres' => [
                'type'    => 'BOOLEAN',
                'default' => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['planificado', 'en_curso', 'finalizado'],
                'default'    => 'planificado',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('consultant_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('battery_services');
    }

    public function down()
    {
        $this->forge->dropTable('battery_services');
    }
}
