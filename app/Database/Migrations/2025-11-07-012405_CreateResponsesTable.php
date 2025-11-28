<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateResponsesTable extends Migration
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
            'worker_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'form_type' => [
                'type'       => 'ENUM',
                'constraint' => ['intralaboral_A', 'intralaboral_B', 'extralaboral', 'estres'],
            ],
            'question_number' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'answer_value' => [
                'type'       => 'INT',
                'constraint' => 11,
                'comment'    => 'Valor en escala Likert 0-4',
            ],
            'session_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('worker_id', 'workers', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addKey(['worker_id', 'form_type', 'question_number']);
        $this->forge->createTable('responses');
    }

    public function down()
    {
        $this->forge->dropTable('responses');
    }
}
