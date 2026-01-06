<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDetailedReportToCsvImports extends Migration
{
    public function up()
    {
        $this->forge->addColumn('csv_imports', [
            'detailed_report' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'error_log',
                'comment' => 'Informe detallado JSON con errores categorizados'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('csv_imports', 'detailed_report');
    }
}
