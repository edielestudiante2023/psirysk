<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWorkerDemographicsTable extends Migration
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
                'unique'     => true,
            ],
            // Datos demográficos
            'gender' => [
                'type'       => 'ENUM',
                'constraint' => ['Masculino', 'Femenino'],
                'null'       => true,
            ],
            'birth_year' => [
                'type'       => 'INT',
                'constraint' => 4,
                'null'       => true,
            ],
            'marital_status' => [
                'type'       => 'ENUM',
                'constraint' => ['Soltero(a)', 'Casado(a)', 'Union_libre', 'Separado(a)', 'Divorciado(a)', 'Viudo(a)', 'Religioso(a)'],
                'null'       => true,
            ],
            'education_level' => [
                'type'       => 'ENUM',
                'constraint' => ['Ninguno', 'Primaria_incompleta', 'Primaria_completa', 'Bachillerato_incompleto', 'Bachillerato_completo', 'Tecnico_incompleto', 'Tecnico_completo', 'Tecnologo_incompleto', 'Tecnologo_completo', 'Profesional_incompleto', 'Profesional_completo', 'Postgrado_incompleto', 'Postgrado_completo'],
                'null'       => true,
            ],
            'occupation' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'city_residence' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'stratum' => [
                'type'       => 'INT',
                'constraint' => 1,
                'null'       => true,
            ],
            'housing_type' => [
                'type'       => 'ENUM',
                'constraint' => ['Propia', 'Familiar', 'Arrendada'],
                'null'       => true,
            ],
            'dependents' => [
                'type'       => 'INT',
                'constraint' => 2,
                'null'       => true,
                'comment'    => 'Número de personas a cargo',
            ],
            // Datos ocupacionales
            'contract_type' => [
                'type'       => 'ENUM',
                'constraint' => ['Termino_fijo', 'Termino_indefinido', 'Cooperado', 'Temporal', 'Prestacion_servicios', 'No_tiene'],
                'null'       => true,
            ],
            'work_experience_years' => [
                'type'       => 'INT',
                'constraint' => 2,
                'null'       => true,
            ],
            'position_type' => [
                'type'       => 'ENUM',
                'constraint' => ['Jefatura', 'Profesional', 'Tecnico', 'Auxiliar', 'Operario'],
                'null'       => true,
            ],
            'time_in_position_months' => [
                'type'       => 'INT',
                'constraint' => 3,
                'null'       => true,
            ],
            'time_in_company_months' => [
                'type'       => 'INT',
                'constraint' => 3,
                'null'       => true,
            ],
            'department' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'work_schedule' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'hours_per_day' => [
                'type'       => 'INT',
                'constraint' => 2,
                'null'       => true,
            ],
            'completed_at' => [
                'type' => 'DATETIME',
                'null' => true,
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
        $this->forge->addForeignKey('worker_id', 'workers', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('worker_demographics');
    }

    public function down()
    {
        $this->forge->dropTable('worker_demographics');
    }
}
