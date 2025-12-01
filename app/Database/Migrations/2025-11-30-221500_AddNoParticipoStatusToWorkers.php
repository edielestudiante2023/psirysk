<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNoParticipoStatusToWorkers extends Migration
{
    public function up()
    {
        // Modificar el ENUM de status para incluir 'no_participo'
        $this->db->query("ALTER TABLE workers MODIFY COLUMN status ENUM('pendiente', 'en_progreso', 'completado', 'no_participo') DEFAULT 'pendiente'");
    }

    public function down()
    {
        // Revertir al ENUM original
        $this->db->query("ALTER TABLE workers MODIFY COLUMN status ENUM('pendiente', 'en_progreso', 'completado') DEFAULT 'pendiente'");
    }
}
