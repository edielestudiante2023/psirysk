<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateActionPlansTable extends Migration
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
            'dimension_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'comment'    => 'Código de la dimensión (ej: caracteristicas_liderazgo)',
            ],
            'dimension_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'comment'    => 'Nombre completo de la dimensión',
            ],
            'domain_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Código del dominio al que pertenece',
            ],
            'questionnaire_type' => [
                'type'       => 'ENUM',
                'constraint' => ['intralaboral', 'extralaboral', 'estres'],
                'comment'    => 'Tipo de cuestionario',
            ],
            'introduction' => [
                'type'    => 'TEXT',
                'comment' => 'Texto introductorio contextual',
            ],
            'objectives' => [
                'type'    => 'JSON',
                'comment' => 'Array de objetivos estratégicos',
            ],
            'activities_6months' => [
                'type'    => 'JSON',
                'comment' => 'Cronograma de actividades por mes',
            ],
            'bibliography' => [
                'type'    => 'JSON',
                'null'    => true,
                'comment' => 'Referencias bibliográficas',
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
        $this->forge->addKey('dimension_code');
        $this->forge->addKey('questionnaire_type');
        $this->forge->createTable('action_plans');
    }

    public function down()
    {
        $this->forge->dropTable('action_plans');
    }
}
