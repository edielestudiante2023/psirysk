<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCsvImportsTable extends Migration
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
            'imported_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'file_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'total_rows' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'imported_rows' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'failed_rows' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'error_log' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['procesando', 'completado', 'error'],
                'default'    => 'procesando',
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
        $this->forge->addForeignKey('imported_by', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('csv_imports');
    }

    public function down()
    {
        $this->forge->dropTable('csv_imports');
    }
}
