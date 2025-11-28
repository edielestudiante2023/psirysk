<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddExtralaboralDimensionsToCalculatedResults extends Migration
{
    public function up()
    {
        $fields = [
            // Dimensión 1: Tiempo fuera del trabajo
            'extralaboral_tiempo_fuera_puntaje' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'null' => true,
            ],
            'extralaboral_tiempo_fuera_nivel' => [
                'type' => 'ENUM',
                'constraint' => ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'],
                'null' => true,
            ],

            // Dimensión 2: Relaciones familiares
            'extralaboral_relaciones_familiares_puntaje' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'null' => true,
            ],
            'extralaboral_relaciones_familiares_nivel' => [
                'type' => 'ENUM',
                'constraint' => ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'],
                'null' => true,
            ],

            // Dimensión 3: Comunicación y relaciones interpersonales
            'extralaboral_comunicacion_puntaje' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'null' => true,
            ],
            'extralaboral_comunicacion_nivel' => [
                'type' => 'ENUM',
                'constraint' => ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'],
                'null' => true,
            ],

            // Dimensión 4: Situación económica del grupo familiar
            'extralaboral_situacion_economica_puntaje' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'null' => true,
            ],
            'extralaboral_situacion_economica_nivel' => [
                'type' => 'ENUM',
                'constraint' => ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'],
                'null' => true,
            ],

            // Dimensión 5: Características de la vivienda y de su entorno
            'extralaboral_caracteristicas_vivienda_puntaje' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'null' => true,
            ],
            'extralaboral_caracteristicas_vivienda_nivel' => [
                'type' => 'ENUM',
                'constraint' => ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'],
                'null' => true,
            ],

            // Dimensión 6: Influencia del entorno extralaboral sobre el trabajo
            'extralaboral_influencia_entorno_puntaje' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'null' => true,
            ],
            'extralaboral_influencia_entorno_nivel' => [
                'type' => 'ENUM',
                'constraint' => ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'],
                'null' => true,
            ],

            // Dimensión 7: Desplazamiento vivienda-trabajo-vivienda
            'extralaboral_desplazamiento_puntaje' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'null' => true,
            ],
            'extralaboral_desplazamiento_nivel' => [
                'type' => 'ENUM',
                'constraint' => ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'],
                'null' => true,
            ],
        ];

        $this->forge->addColumn('calculated_results', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('calculated_results', [
            'extralaboral_tiempo_fuera_puntaje',
            'extralaboral_tiempo_fuera_nivel',
            'extralaboral_relaciones_familiares_puntaje',
            'extralaboral_relaciones_familiares_nivel',
            'extralaboral_comunicacion_puntaje',
            'extralaboral_comunicacion_nivel',
            'extralaboral_situacion_economica_puntaje',
            'extralaboral_situacion_economica_nivel',
            'extralaboral_caracteristicas_vivienda_puntaje',
            'extralaboral_caracteristicas_vivienda_nivel',
            'extralaboral_influencia_entorno_puntaje',
            'extralaboral_influencia_entorno_nivel',
            'extralaboral_desplazamiento_puntaje',
            'extralaboral_desplazamiento_nivel',
        ]);
    }
}
