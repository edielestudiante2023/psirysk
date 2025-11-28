<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBatterySchedulesTable extends Migration
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
            ],
            'company_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'contact_email' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'contact_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'evaluation_date' => [
                'type' => 'DATE',
                'comment' => 'Fecha de aplicación de la batería',
            ],
            'intervention_start_date' => [
                'type' => 'DATE',
                'null' => true,
                'comment' => 'Fecha de inicio de intervenciones',
            ],
            'intralaboral_risk_level' => [
                'type' => 'ENUM',
                'constraint' => ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'],
                'comment' => 'Nivel de riesgo intralaboral general (máximo entre Forma A y B)',
            ],
            'forma_a_risk_level' => [
                'type' => 'ENUM',
                'constraint' => ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'],
                'null' => true,
            ],
            'forma_b_risk_level' => [
                'type' => 'ENUM',
                'constraint' => ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'],
                'null' => true,
            ],
            'periodicity_years' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'comment' => '1 = Anual (riesgo alto/muy alto), 2 = Bienal (riesgo bajo/medio)',
            ],
            'next_evaluation_date' => [
                'type' => 'DATE',
                'comment' => 'Fecha calculada para próxima evaluación',
            ],
            'notification_30_days_sent' => [
                'type' => 'BOOLEAN',
                'default' => false,
                'comment' => 'Indica si se envió notificación 30 días antes',
            ],
            'notification_30_days_sent_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'notification_7_days_sent' => [
                'type' => 'BOOLEAN',
                'default' => false,
                'comment' => 'Indica si se envió notificación 7 días antes',
            ],
            'notification_7_days_sent_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'notification_overdue_sent' => [
                'type' => 'BOOLEAN',
                'default' => false,
                'comment' => 'Indica si se envió notificación de vencimiento',
            ],
            'notification_overdue_sent_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['active', 'completed', 'cancelled'],
                'default' => 'active',
            ],
            'notes' => [
                'type' => 'TEXT',
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
        $this->forge->addKey('battery_service_id');
        $this->forge->addKey('next_evaluation_date');
        $this->forge->addKey('status');

        $this->forge->addForeignKey('battery_service_id', 'battery_services', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('battery_schedules');
    }

    public function down()
    {
        $this->forge->dropTable('battery_schedules');
    }
}
