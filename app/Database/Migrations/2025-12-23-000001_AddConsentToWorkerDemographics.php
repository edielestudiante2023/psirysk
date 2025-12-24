<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddConsentToWorkerDemographics extends Migration
{
    public function up()
    {
        $fields = [
            'consent_accepted' => [
                'type' => 'BOOLEAN',
                'null' => true,
                'default' => null,
                'comment' => 'Indica si el trabajador aceptó el consentimiento informado',
                'after' => 'worker_id'
            ],
            'consent_accepted_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'default' => null,
                'comment' => 'Fecha y hora en que se aceptó el consentimiento',
                'after' => 'consent_accepted'
            ]
        ];

        $this->forge->addColumn('worker_demographics', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('worker_demographics', ['consent_accepted', 'consent_accepted_at']);
    }
}
