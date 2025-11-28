<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateConsultantsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'nombre_completo' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
            ],
            'tipo_documento' => [
                'type' => 'ENUM',
                'constraint' => ['CC', 'CE', 'PAS', 'OTRO'],
                'default' => 'CC',
            ],
            'numero_documento' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
            ],
            'licencia_sst' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'cargo' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'default' => 'Psicólogo Especialista SST',
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
                'null' => true,
            ],
            'telefono' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
            ],
            'website' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'linkedin' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'firma_path' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
            ],
            'activo' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
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
        $this->forge->createTable('consultants');
    }

    public function down()
    {
        $this->forge->dropTable('consultants');
    }
}
