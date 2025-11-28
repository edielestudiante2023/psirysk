<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateReportSectionsTable extends Migration
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
            'report_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],

            // NIVEL DE LA SECCIÓN
            'section_level' => [
                'type'       => 'ENUM',
                'constraint' => ['executive', 'total', 'questionnaire', 'domain', 'dimension'],
                'comment'    => 'executive=resumen gerencial, total=total general, questionnaire=intralaboral/extralaboral/stress, domain=dominios, dimension=dimensiones',
            ],

            // TIPO DE CUESTIONARIO
            'questionnaire_type' => [
                'type'       => 'ENUM',
                'constraint' => ['general', 'intralaboral', 'extralaboral', 'stress'],
                'null'       => true,
                'comment'    => 'general para totales y ejecutivo, específico para los demás',
            ],

            // CÓDIGO DEL DOMINIO (solo para nivel domain y dimension intralaboral)
            'domain_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment'    => 'liderazgo, control, demandas, recompensas',
            ],

            // CÓDIGO DE LA DIMENSIÓN
            'dimension_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Código único de la dimensión',
            ],

            // TIPO DE FORMA
            'form_type' => [
                'type'       => 'ENUM',
                'constraint' => ['A', 'B', 'conjunto'],
                'comment'    => 'A=jefes/profesionales, B=auxiliares/operarios, conjunto=ambos',
            ],

            // DATOS NUMÉRICOS
            'score_value' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'null'       => true,
                'comment'    => 'Puntaje calculado',
            ],

            'risk_level' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'comment'    => 'sin_riesgo, riesgo_bajo, riesgo_medio, riesgo_alto, riesgo_muy_alto',
            ],

            // PORCENTAJES DE DISTRIBUCIÓN (para gráficos)
            'distribution_data' => [
                'type'    => 'JSON',
                'null'    => true,
                'comment' => 'Porcentajes por nivel de riesgo para gráficos',
            ],

            // TEXTOS GENERADOS Y DEL CONSULTOR
            'ai_generated_text' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Texto generado por OpenAI - NO editable',
            ],

            'consultant_comment' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Comentario adicional del consultor - opcional',
            ],

            // CONTROL DE FLUJO
            'is_approved' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'comment'    => '0=pendiente, 1=aprobado',
            ],

            'approved_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

            'approved_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],

            // ORDEN EN EL INFORME
            'order_position' => [
                'type'       => 'INT',
                'constraint' => 4,
                'default'    => 0,
                'comment'    => 'Orden de aparición en el PDF',
            ],

            // METADATOS
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
        $this->forge->addForeignKey('report_id', 'reports', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('approved_by', 'users', 'id', 'SET NULL', 'CASCADE');

        // Índices para búsqueda rápida
        $this->forge->addKey('section_level');
        $this->forge->addKey('questionnaire_type');
        $this->forge->addKey('form_type');
        $this->forge->addKey('is_approved');

        // Índice único para evitar duplicados
        $this->forge->addUniqueKey(['report_id', 'section_level', 'questionnaire_type', 'domain_code', 'dimension_code', 'form_type'], 'unique_section');

        $this->forge->createTable('report_sections');
    }

    public function down()
    {
        $this->forge->dropTable('report_sections');
    }
}
