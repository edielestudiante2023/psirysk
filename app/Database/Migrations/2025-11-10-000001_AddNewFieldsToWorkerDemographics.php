<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNewFieldsToWorkerDemographics extends Migration
{
    public function up()
    {
        $fields = [
            'department_residence' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'city_residence'
            ],
            'city_work' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'dependents'
            ],
            'department_work' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'city_work'
            ],
            'time_in_company_type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'after' => 'department_work'
            ],
            'time_in_company_years' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'after' => 'time_in_company_type'
            ],
            'position_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'time_in_company_years'
            ],
            'time_in_position_type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'after' => 'position_type'
            ],
            'time_in_position_years' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'after' => 'time_in_position_type'
            ],
            'salary_type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'after' => 'hours_per_day'
            ],
        ];

        $this->forge->addColumn('worker_demographics', $fields);

        // Remove old fields that are no longer used
        if ($this->db->fieldExists('work_experience_years', 'worker_demographics')) {
            $this->forge->dropColumn('worker_demographics', 'work_experience_years');
        }
        if ($this->db->fieldExists('time_in_position_months', 'worker_demographics')) {
            $this->forge->dropColumn('worker_demographics', 'time_in_position_months');
        }
        if ($this->db->fieldExists('time_in_company_months', 'worker_demographics')) {
            $this->forge->dropColumn('worker_demographics', 'time_in_company_months');
        }
        if ($this->db->fieldExists('work_schedule', 'worker_demographics')) {
            $this->forge->dropColumn('worker_demographics', 'work_schedule');
        }
    }

    public function down()
    {
        $fields = [
            'department_residence',
            'city_work',
            'department_work',
            'time_in_company_type',
            'time_in_company_years',
            'position_name',
            'time_in_position_type',
            'time_in_position_years',
            'salary_type'
        ];

        $this->forge->dropColumn('worker_demographics', $fields);

        // Restore old fields
        $oldFields = [
            'work_experience_years' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true
            ],
            'time_in_position_months' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true
            ],
            'time_in_company_months' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true
            ],
            'work_schedule' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true
            ]
        ];

        $this->forge->addColumn('worker_demographics', $oldFields);
    }
}
