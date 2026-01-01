<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateValidationResultsTable extends Migration
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
            'battery_service_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'ID del servicio de batería'
            ],
            'questionnaire_type' => [
                'type' => 'ENUM',
                'constraint' => ['intralaboral', 'extralaboral', 'estres'],
                'comment' => 'Tipo de cuestionario validado'
            ],
            'form_type' => [
                'type' => 'ENUM',
                'constraint' => ['A', 'B'],
                'null' => true,
                'comment' => 'Forma A o B (null para cuestionarios sin distinción)'
            ],
            'validation_level' => [
                'type' => 'ENUM',
                'constraint' => ['dimension', 'domain', 'total'],
                'comment' => 'Nivel de validación: dimensión, dominio o total'
            ],
            'element_key' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'comment' => 'Clave del elemento validado (ej: liderazgo, autonomia, total_intralaboral)'
            ],
            'element_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'comment' => 'Nombre del elemento validado'
            ],
            'total_workers' => [
                'type' => 'INT',
                'constraint' => 11,
                'comment' => 'Total de trabajadores incluidos en la validación'
            ],
            'sum_averages' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'comment' => 'Suma de promedios de ítems'
            ],
            'transformation_factor' => [
                'type' => 'DECIMAL',
                'constraint' => '10,4',
                'null' => true,
                'comment' => 'Factor de transformación aplicado (si aplica)'
            ],
            'calculated_score' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'comment' => 'Puntaje calculado por el núcleo validador'
            ],
            'db_score' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'comment' => 'Puntaje promedio desde calculated_results'
            ],
            'difference' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'comment' => 'Diferencia entre calculated_score y db_score'
            ],
            'validation_status' => [
                'type' => 'ENUM',
                'constraint' => ['ok', 'error'],
                'comment' => 'Estado de validación (ok si diff < 0.1, error si diff >= 0.1)'
            ],
            'processed_at' => [
                'type' => 'DATETIME',
                'comment' => 'Fecha y hora de procesamiento'
            ],
            'processed_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'ID del usuario que procesó la validación'
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('battery_service_id');
        $this->forge->addKey(['questionnaire_type', 'form_type']);
        $this->forge->addKey(['battery_service_id', 'validation_level']);
        $this->forge->addKey('validation_status');

        // Índice único para evitar duplicados
        $this->forge->addUniqueKey([
            'battery_service_id',
            'questionnaire_type',
            'form_type',
            'validation_level',
            'element_key'
        ], 'unique_validation');

        $this->forge->addForeignKey('battery_service_id', 'battery_services', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('processed_by', 'users', 'id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('validation_results');
    }

    public function down()
    {
        $this->forge->dropTable('validation_results');
    }
}
