<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddServiceClosureFields extends Migration
{
    public function up()
    {
        // Agregar campos de cierre a battery_services
        $fields = [
            'closed_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Fecha y hora de cierre del servicio'
            ],
            'closed_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'comment' => 'ID del usuario que cerró el servicio'
            ],
            'closure_notes' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Notas del consultor al cerrar el servicio'
            ],
            'min_participation_percent' => [
                'type' => 'INT',
                'constraint' => 3,
                'default' => 50,
                'comment' => 'Porcentaje mínimo de participación para cerrar'
            ]
        ];
        $this->forge->addColumn('battery_services', $fields);

        // Agregar campos de motivo de no participación a workers
        $workerFields = [
            'non_participation_reason' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => 'Motivo de no participación (Incapacidad, Vacaciones, etc.)'
            ],
            'non_participation_notes' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Notas adicionales sobre la no participación'
            ]
        ];
        $this->forge->addColumn('workers', $workerFields);
    }

    public function down()
    {
        // Eliminar campos de battery_services
        $this->forge->dropColumn('battery_services', [
            'closed_at',
            'closed_by',
            'closure_notes',
            'min_participation_percent'
        ]);

        // Eliminar campos de workers
        $this->forge->dropColumn('workers', [
            'non_participation_reason',
            'non_participation_notes'
        ]);
    }
}
