<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateIndividualResultsRequestsTable extends Migration
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
            'service_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => 'ID del servicio de batería',
            ],
            'worker_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => 'ID del trabajador cuyos resultados se solicitan',
            ],
            'requester_user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => 'ID del usuario cliente que solicita',
            ],
            'request_type' => [
                'type'       => 'ENUM',
                'constraint' => ['intralaboral_a', 'intralaboral_b', 'extralaboral', 'estres'],
                'comment'    => 'Tipo de resultado solicitado',
            ],
            'motivation' => [
                'type'    => 'TEXT',
                'comment' => 'Motivación/justificación de la solicitud (requerido legalmente)',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'approved', 'rejected'],
                'default'    => 'pending',
                'comment'    => 'Estado de la solicitud',
            ],
            'reviewed_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'ID del consultor que revisó la solicitud',
            ],
            'review_notes' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'Notas del consultor al aprobar/rechazar',
            ],
            'reviewed_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'comment' => 'Fecha y hora de revisión',
            ],
            'access_granted_until' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'comment' => 'Fecha hasta la cual el acceso está habilitado (si aprobado)',
            ],
            'access_token' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
                'comment'    => 'Token único para acceso temporal directo',
            ],
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => true,
                'comment'    => 'IP desde donde se hizo la solicitud',
            ],
            'user_agent' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'User agent del navegador',
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
        $this->forge->addKey('service_id');
        $this->forge->addKey('worker_id');
        $this->forge->addKey('requester_user_id');
        $this->forge->addKey('status');
        $this->forge->addKey('created_at');

        // Foreign keys
        $this->forge->addForeignKey('service_id', 'battery_services', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('worker_id', 'workers', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('requester_user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('reviewed_by', 'users', 'id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('individual_results_requests', true);
    }

    public function down()
    {
        $this->forge->dropTable('individual_results_requests', true);
    }
}
