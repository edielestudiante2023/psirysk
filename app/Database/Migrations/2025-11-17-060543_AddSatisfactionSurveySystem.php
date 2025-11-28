<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSatisfactionSurveySystem extends Migration
{
    public function up()
    {
        // 1. Crear tabla de encuestas de satisfacción
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'service_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'Usuario cliente que respondió la encuesta',
            ],
            'question_1' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'comment' => '¿Qué tan satisfecho está con el servicio recibido? (1-5)',
            ],
            'question_2' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'comment' => '¿El consultor fue claro y profesional durante el proceso? (1-5)',
            ],
            'question_3' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'comment' => '¿Los informes cumplen con sus expectativas? (1-5)',
            ],
            'question_4' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'comment' => '¿Recomendaría nuestros servicios a otras empresas? (1-5)',
            ],
            'question_5' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'comment' => '¿Qué tan fácil fue navegar y entender los resultados? (1-5)',
            ],
            'comments' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Comentarios o sugerencias (opcional)',
            ],
            'completed_at' => [
                'type' => 'DATETIME',
                'null' => false,
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
        $this->forge->addForeignKey('service_id', 'battery_services', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('service_satisfaction_surveys');

        // 2. Agregar campo a battery_services para marcar si la encuesta fue completada
        $fields = [
            'satisfaction_survey_completed' => [
                'type' => 'BOOLEAN',
                'default' => false,
                'after' => 'closure_notes',
            ],
        ];

        $this->forge->addColumn('battery_services', $fields);
    }

    public function down()
    {
        // Eliminar columna de battery_services
        $this->forge->dropColumn('battery_services', 'satisfaction_survey_completed');

        // Eliminar tabla de encuestas
        $this->forge->dropTable('service_satisfaction_surveys', true);
    }
}
