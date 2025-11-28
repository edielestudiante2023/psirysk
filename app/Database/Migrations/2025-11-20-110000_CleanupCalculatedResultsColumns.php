<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CleanupCalculatedResultsColumns extends Migration
{
    public function up()
    {
        // Eliminar columnas obsoletas de extralaboral con prefijo 'extra_'
        $this->forge->dropColumn('calculated_results', [
            'extra_tiempo_fuera_puntaje',
            'extra_tiempo_fuera_nivel',
            'extra_relaciones_familiares_puntaje',
            'extra_relaciones_familiares_nivel',
            'extra_comunicacion_relaciones_puntaje',
            'extra_comunicacion_relaciones_nivel',
            'extra_situacion_economica_puntaje',
            'extra_situacion_economica_nivel',
            'extra_caracteristicas_vivienda_puntaje',
            'extra_caracteristicas_vivienda_nivel',
            'extra_influencia_entorno_puntaje',
            'extra_influencia_entorno_nivel',
            'extra_desplazamiento_puntaje',
            'extra_desplazamiento_nivel',
        ]);

        // Eliminar subdivisiones de estrés que no existen en el instrumento oficial
        $this->forge->dropColumn('calculated_results', [
            'estres_sintomas_fisiologicos_puntaje',
            'estres_sintomas_fisiologicos_nivel',
            'estres_sintomas_comportamiento_social_puntaje',
            'estres_sintomas_comportamiento_social_nivel',
            'estres_sintomas_intelectuales_laborales_puntaje',
            'estres_sintomas_intelectuales_laborales_nivel',
            'estres_sintomas_psicoemocionales_puntaje',
            'estres_sintomas_psicoemocionales_nivel',
        ]);
    }

    public function down()
    {
        // Recrear columnas de extralaboral con prefijo 'extra_' si se hace rollback
        $extralaboralFields = [
            'extra_tiempo_fuera_puntaje' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'null' => true,
            ],
            'extra_tiempo_fuera_nivel' => [
                'type' => 'ENUM',
                'constraint' => ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'],
                'null' => true,
            ],
            'extra_relaciones_familiares_puntaje' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'null' => true,
            ],
            'extra_relaciones_familiares_nivel' => [
                'type' => 'ENUM',
                'constraint' => ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'],
                'null' => true,
            ],
            'extra_comunicacion_relaciones_puntaje' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'null' => true,
            ],
            'extra_comunicacion_relaciones_nivel' => [
                'type' => 'ENUM',
                'constraint' => ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'],
                'null' => true,
            ],
            'extra_situacion_economica_puntaje' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'null' => true,
            ],
            'extra_situacion_economica_nivel' => [
                'type' => 'ENUM',
                'constraint' => ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'],
                'null' => true,
            ],
            'extra_caracteristicas_vivienda_puntaje' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'null' => true,
            ],
            'extra_caracteristicas_vivienda_nivel' => [
                'type' => 'ENUM',
                'constraint' => ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'],
                'null' => true,
            ],
            'extra_influencia_entorno_puntaje' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'null' => true,
            ],
            'extra_influencia_entorno_nivel' => [
                'type' => 'ENUM',
                'constraint' => ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'],
                'null' => true,
            ],
            'extra_desplazamiento_puntaje' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'null' => true,
            ],
            'extra_desplazamiento_nivel' => [
                'type' => 'ENUM',
                'constraint' => ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'],
                'null' => true,
            ],
        ];

        $this->forge->addColumn('calculated_results', $extralaboralFields);

        // Recrear subdivisiones de estrés si se hace rollback
        $estresFields = [
            'estres_sintomas_fisiologicos_puntaje' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'null' => true,
            ],
            'estres_sintomas_fisiologicos_nivel' => [
                'type' => 'ENUM',
                'constraint' => ['muy_bajo', 'bajo', 'medio', 'alto', 'muy_alto'],
                'null' => true,
            ],
            'estres_sintomas_comportamiento_social_puntaje' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'null' => true,
            ],
            'estres_sintomas_comportamiento_social_nivel' => [
                'type' => 'ENUM',
                'constraint' => ['muy_bajo', 'bajo', 'medio', 'alto', 'muy_alto'],
                'null' => true,
            ],
            'estres_sintomas_intelectuales_laborales_puntaje' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'null' => true,
            ],
            'estres_sintomas_intelectuales_laborales_nivel' => [
                'type' => 'ENUM',
                'constraint' => ['muy_bajo', 'bajo', 'medio', 'alto', 'muy_alto'],
                'null' => true,
            ],
            'estres_sintomas_psicoemocionales_puntaje' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'null' => true,
            ],
            'estres_sintomas_psicoemocionales_nivel' => [
                'type' => 'ENUM',
                'constraint' => ['muy_bajo', 'bajo', 'medio', 'alto', 'muy_alto'],
                'null' => true,
            ],
        ];

        $this->forge->addColumn('calculated_results', $estresFields);
    }
}
