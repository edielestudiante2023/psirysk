<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Nueva estructura para guardar interpretaciones demográficas por sección
 * Solo la síntesis general tiene comentario del consultor
 */
class CreateDemographicsSectionsTable extends Migration
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

            // ============ VARIABLES SOCIODEMOGRÁFICAS ============

            // 1. SEXO
            'sexo_ia' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'sexo_data' => [
                'type' => 'JSON',
                'null' => true,
            ],

            // 2. RANGO DE EDAD
            'edad_ia' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'edad_data' => [
                'type' => 'JSON',
                'null' => true,
            ],

            // 3. ESTADO CIVIL
            'estado_civil_ia' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'estado_civil_data' => [
                'type' => 'JSON',
                'null' => true,
            ],

            // 4. NIVEL EDUCATIVO
            'educacion_ia' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'educacion_data' => [
                'type' => 'JSON',
                'null' => true,
            ],

            // 5. ESTRATO
            'estrato_ia' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'estrato_data' => [
                'type' => 'JSON',
                'null' => true,
            ],

            // 6. VIVIENDA
            'vivienda_ia' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'vivienda_data' => [
                'type' => 'JSON',
                'null' => true,
            ],

            // 7. PERSONAS A CARGO
            'dependientes_ia' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'dependientes_data' => [
                'type' => 'JSON',
                'null' => true,
            ],

            // 8. LUGAR DE RESIDENCIA
            'residencia_ia' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'residencia_data' => [
                'type' => 'JSON',
                'null' => true,
            ],

            // ============ VARIABLES OCUPACIONALES ============

            // 9. ANTIGÜEDAD EN LA EMPRESA
            'antiguedad_empresa_ia' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'antiguedad_empresa_data' => [
                'type' => 'JSON',
                'null' => true,
            ],

            // 10. ANTIGÜEDAD EN EL CARGO
            'antiguedad_cargo_ia' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'antiguedad_cargo_data' => [
                'type' => 'JSON',
                'null' => true,
            ],

            // 11. TIPO DE CONTRATO
            'contrato_ia' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'contrato_data' => [
                'type' => 'JSON',
                'null' => true,
            ],

            // 12. TIPO DE CARGO
            'cargo_ia' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'cargo_data' => [
                'type' => 'JSON',
                'null' => true,
            ],

            // 13. ÁREA/DEPARTAMENTO
            'area_ia' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'area_data' => [
                'type' => 'JSON',
                'null' => true,
            ],

            // 14. HORAS DE TRABAJO
            'horas_ia' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'horas_data' => [
                'type' => 'JSON',
                'null' => true,
            ],

            // 15. RANGO SALARIAL
            'salario_ia' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'salario_data' => [
                'type' => 'JSON',
                'null' => true,
            ],

            // ============ SÍNTESIS GENERAL (con comentario del consultor) ============

            // 16. SÍNTESIS GENERAL
            'sintesis_ia' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'sintesis_comment' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            // ============ METADATA ============

            'total_workers' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'generated_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
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
        $this->forge->addForeignKey('generated_by', 'users', 'id', 'SET NULL', 'CASCADE');

        // Solo un registro por servicio
        $this->forge->addUniqueKey('battery_service_id');

        $this->forge->createTable('demographics_sections');
    }

    public function down()
    {
        $this->forge->dropTable('demographics_sections');
    }
}
