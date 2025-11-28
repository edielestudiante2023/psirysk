<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFichaDatosToResponsesFormType extends Migration
{
    public function up()
    {
        // Modify form_type ENUM to include ficha_datos_generales
        $this->db->query("ALTER TABLE responses MODIFY COLUMN form_type ENUM('ficha_datos_generales', 'intralaboral_A', 'intralaboral_B', 'extralaboral', 'estres') NOT NULL");
    }

    public function down()
    {
        // Revert form_type ENUM to original values
        $this->db->query("ALTER TABLE responses MODIFY COLUMN form_type ENUM('intralaboral_A', 'intralaboral_B', 'extralaboral', 'estres') NOT NULL");
    }
}
