<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWorkersTable extends Migration
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
            'battery_service_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'document' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'position' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'intralaboral_type' => [
                'type'       => 'ENUM',
                'constraint' => ['A', 'B'],
            ],
            'application_mode' => [
                'type'       => 'ENUM',
                'constraint' => ['virtual', 'presencial'],
            ],
            'token' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'unique'     => true,
            ],
            'email_sent' => [
                'type'    => 'BOOLEAN',
                'default' => false,
            ],
            'email_sent_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'started_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'completed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pendiente', 'en_progreso', 'completado'],
                'default'    => 'pendiente',
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
        $this->forge->addForeignKey('battery_service_id', 'battery_services', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('workers');
    }

    public function down()
    {
        $this->forge->dropTable('workers');
    }
}
