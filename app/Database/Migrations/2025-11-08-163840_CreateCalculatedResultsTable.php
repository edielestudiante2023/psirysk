<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCalculatedResultsTable extends Migration
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
            'battery_service_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],

            // DATOS DEL TRABAJADOR
            'worker_name' => ['type' => 'VARCHAR', 'constraint' => 255],
            'worker_document' => ['type' => 'VARCHAR', 'constraint' => 50],
            'worker_email' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],

            // DATOS DEMOGRÁFICOS
            'gender' => ['type' => 'ENUM', 'constraint' => ['Masculino', 'Femenino'], 'null' => true],
            'birth_year' => ['type' => 'INT', 'constraint' => 4, 'null' => true],
            'age' => ['type' => 'INT', 'constraint' => 3, 'null' => true],
            'marital_status' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'education_level' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'city_residence' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'stratum' => ['type' => 'INT', 'constraint' => 1, 'null' => true],
            'housing_type' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],

            // DATOS OCUPACIONALES
            'department' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'position' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'position_type' => ['type' => 'ENUM', 'constraint' => ['Jefatura', 'Profesional', 'Tecnico', 'Auxiliar', 'Operario'], 'null' => true],
            'contract_type' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'work_experience_years' => ['type' => 'INT', 'constraint' => 3, 'null' => true],
            'time_in_company_months' => ['type' => 'INT', 'constraint' => 4, 'null' => true],
            'time_in_position_months' => ['type' => 'INT', 'constraint' => 4, 'null' => true],
            'hours_per_day' => ['type' => 'DECIMAL', 'constraint' => '4,2', 'null' => true],

            // TIPO DE FORMULARIO
            'intralaboral_form_type' => ['type' => 'ENUM', 'constraint' => ['A', 'B']],

            // INTRALABORAL - DOMINIOS
            'dom_liderazgo_puntaje' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'dom_liderazgo_nivel' => ['type' => 'ENUM', 'constraint' => ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'], 'null' => true],
            'dom_control_puntaje' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'dom_control_nivel' => ['type' => 'ENUM', 'constraint' => ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'], 'null' => true],
            'dom_demandas_puntaje' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'dom_demandas_nivel' => ['type' => 'ENUM', 'constraint' => ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'], 'null' => true],
            'dom_recompensas_puntaje' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'dom_recompensas_nivel' => ['type' => 'ENUM', 'constraint' => ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'], 'null' => true],

            // INTRALABORAL - DIMENSIONES (19 dimensiones - algunas solo para A o B)
            'dim_caracteristicas_liderazgo_puntaje' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'dim_caracteristicas_liderazgo_nivel' => ['type' => 'ENUM', 'constraint' => ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'], 'null' => true],
            'dim_relaciones_sociales_puntaje' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'dim_relaciones_sociales_nivel' => ['type' => 'ENUM', 'constraint' => ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'], 'null' => true],
            'dim_retroalimentacion_puntaje' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'dim_retroalimentacion_nivel' => ['type' => 'ENUM', 'constraint' => ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'], 'null' => true],
            'dim_relacion_colaboradores_puntaje' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'dim_relacion_colaboradores_nivel' => ['type' => 'ENUM', 'constraint' => ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'], 'null' => true],
            'dim_claridad_rol_puntaje' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'dim_claridad_rol_nivel' => ['type' => 'ENUM', 'constraint' => ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'], 'null' => true],
            'dim_capacitacion_puntaje' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'dim_capacitacion_nivel' => ['type' => 'ENUM', 'constraint' => ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'], 'null' => true],
            'dim_participacion_manejo_cambio_puntaje' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'dim_participacion_manejo_cambio_nivel' => ['type' => 'ENUM', 'constraint' => ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'], 'null' => true],
            'dim_oportunidades_desarrollo_puntaje' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'dim_oportunidades_desarrollo_nivel' => ['type' => 'ENUM', 'constraint' => ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'], 'null' => true],
            'dim_control_autonomia_puntaje' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'dim_control_autonomia_nivel' => ['type' => 'ENUM', 'constraint' => ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'], 'null' => true],
            'dim_demandas_ambientales_puntaje' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'dim_demandas_ambientales_nivel' => ['type' => 'ENUM', 'constraint' => ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'], 'null' => true],
            'dim_demandas_emocionales_puntaje' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'dim_demandas_emocionales_nivel' => ['type' => 'ENUM', 'constraint' => ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'], 'null' => true],
            'dim_demandas_cuantitativas_puntaje' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'dim_demandas_cuantitativas_nivel' => ['type' => 'ENUM', 'constraint' => ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'], 'null' => true],
            'dim_influencia_trabajo_entorno_extralaboral_puntaje' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'dim_influencia_trabajo_entorno_extralaboral_nivel' => ['type' => 'ENUM', 'constraint' => ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'], 'null' => true],
            'dim_demandas_carga_mental_puntaje' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'dim_demandas_carga_mental_nivel' => ['type' => 'ENUM', 'constraint' => ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'], 'null' => true],
            'dim_demandas_jornada_trabajo_puntaje' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'dim_demandas_jornada_trabajo_nivel' => ['type' => 'ENUM', 'constraint' => ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'], 'null' => true],
            'dim_consistencia_rol_puntaje' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'dim_consistencia_rol_nivel' => ['type' => 'ENUM', 'constraint' => ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'], 'null' => true],
            'dim_demandas_responsabilidad_puntaje' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'dim_demandas_responsabilidad_nivel' => ['type' => 'ENUM', 'constraint' => ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'], 'null' => true],
            'dim_recompensas_pertenencia_puntaje' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'dim_recompensas_pertenencia_nivel' => ['type' => 'ENUM', 'constraint' => ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'], 'null' => true],
            'dim_reconocimiento_compensacion_puntaje' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'dim_reconocimiento_compensacion_nivel' => ['type' => 'ENUM', 'constraint' => ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'], 'null' => true],

            // TOTAL INTRALABORAL
            'intralaboral_total_puntaje' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'intralaboral_total_nivel' => ['type' => 'ENUM', 'constraint' => ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'], 'null' => true],

            // EXTRALABORAL - DIMENSIONES
            'extra_tiempo_fuera_puntaje' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'extra_tiempo_fuera_nivel' => ['type' => 'ENUM', 'constraint' => ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'], 'null' => true],
            'extra_relaciones_familiares_puntaje' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'extra_relaciones_familiares_nivel' => ['type' => 'ENUM', 'constraint' => ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'], 'null' => true],
            'extra_comunicacion_relaciones_puntaje' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'extra_comunicacion_relaciones_nivel' => ['type' => 'ENUM', 'constraint' => ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'], 'null' => true],
            'extra_situacion_economica_puntaje' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'extra_situacion_economica_nivel' => ['type' => 'ENUM', 'constraint' => ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'], 'null' => true],
            'extra_caracteristicas_vivienda_puntaje' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'extra_caracteristicas_vivienda_nivel' => ['type' => 'ENUM', 'constraint' => ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'], 'null' => true],
            'extra_influencia_entorno_puntaje' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'extra_influencia_entorno_nivel' => ['type' => 'ENUM', 'constraint' => ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'], 'null' => true],
            'extra_desplazamiento_puntaje' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'extra_desplazamiento_nivel' => ['type' => 'ENUM', 'constraint' => ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'], 'null' => true],
            'extralaboral_total_puntaje' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'extralaboral_total_nivel' => ['type' => 'ENUM', 'constraint' => ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'], 'null' => true],

            // ESTRÉS - DIMENSIONES
            'estres_sintomas_fisiologicos_puntaje' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'estres_sintomas_fisiologicos_nivel' => ['type' => 'ENUM', 'constraint' => ['muy_bajo', 'bajo', 'medio', 'alto', 'muy_alto'], 'null' => true],
            'estres_sintomas_comportamiento_social_puntaje' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'estres_sintomas_comportamiento_social_nivel' => ['type' => 'ENUM', 'constraint' => ['muy_bajo', 'bajo', 'medio', 'alto', 'muy_alto'], 'null' => true],
            'estres_sintomas_intelectuales_laborales_puntaje' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'estres_sintomas_intelectuales_laborales_nivel' => ['type' => 'ENUM', 'constraint' => ['muy_bajo', 'bajo', 'medio', 'alto', 'muy_alto'], 'null' => true],
            'estres_sintomas_psicoemocionales_puntaje' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'estres_sintomas_psicoemocionales_nivel' => ['type' => 'ENUM', 'constraint' => ['muy_bajo', 'bajo', 'medio', 'alto', 'muy_alto'], 'null' => true],
            'estres_total_puntaje' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'estres_total_nivel' => ['type' => 'ENUM', 'constraint' => ['muy_bajo', 'bajo', 'medio', 'alto', 'muy_alto'], 'null' => true],

            // TOTAL GENERAL
            'puntaje_total_general' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'puntaje_total_general_nivel' => ['type' => 'ENUM', 'constraint' => ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'], 'null' => true],

            // METADATOS
            'calculated_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('worker_id', 'workers', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('battery_service_id', 'battery_services', 'id', 'CASCADE', 'CASCADE');

        // Índices para segmentación rápida
        $this->forge->addKey('battery_service_id');
        $this->forge->addKey('intralaboral_form_type');
        $this->forge->addKey('gender');
        $this->forge->addKey('marital_status');
        $this->forge->addKey('education_level');
        $this->forge->addKey('city_residence');
        $this->forge->addKey('department');
        $this->forge->addKey('position_type');
        $this->forge->addKey('contract_type');

        $this->forge->createTable('calculated_results');
    }

    public function down()
    {
        $this->forge->dropTable('calculated_results');
    }
}
