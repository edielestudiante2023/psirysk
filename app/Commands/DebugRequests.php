<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class DebugRequests extends BaseCommand
{
    protected $group       = 'Debug';
    protected $name        = 'debug:requests';
    protected $description = 'Debug individual results requests';

    public function run(array $params)
    {
        $db = \Config\Database::connect();

        CLI::write('=== CONSULTORES ===', 'yellow');
        $consultants = $db->query("SELECT id, name, role_id FROM users WHERE role_id = (SELECT id FROM roles WHERE name = 'consultor')")->getResultArray();
        foreach ($consultants as $c) {
            CLI::write("ID: {$c['id']}, Nombre: {$c['name']}, Role ID: {$c['role_id']}");
        }

        CLI::newLine();
        CLI::write('=== SERVICIOS DE BATERIA ===', 'yellow');
        $services = $db->query("SELECT id, service_name, consultant_id, company_id FROM battery_services")->getResultArray();
        foreach ($services as $s) {
            CLI::write("Service ID: {$s['id']}, Nombre: {$s['service_name']}, Consultant ID: {$s['consultant_id']}");
        }

        CLI::newLine();
        CLI::write('=== TODAS LAS SOLICITUDES ===', 'yellow');
        $allRequests = $db->query("SELECT * FROM individual_results_requests")->getResultArray();
        foreach ($allRequests as $r) {
            CLI::write("Request ID: {$r['id']}, Service ID: {$r['service_id']}, Worker ID: {$r['worker_id']}, Status: {$r['status']}, Type: {$r['request_type']}");
        }

        CLI::newLine();
        CLI::write('=== SOLICITUDES PENDIENTES ===', 'yellow');
        $requests = $db->query("SELECT irr.*, bs.service_name, bs.consultant_id
            FROM individual_results_requests irr
            JOIN battery_services bs ON bs.id = irr.service_id
            WHERE irr.status = 'pending'")->getResultArray();

        foreach ($requests as $r) {
            CLI::write("Request ID: {$r['id']}, Service: {$r['service_name']}, Consultant ID: {$r['consultant_id']}, Status: {$r['status']}");
        }

        CLI::newLine();
        CLI::write('=== QUERY DEL MODELO ===', 'yellow');
        foreach ($consultants as $c) {
            $count = $db->query("
                SELECT COUNT(*) as total
                FROM individual_results_requests
                JOIN battery_services ON battery_services.id = individual_results_requests.service_id
                WHERE battery_services.consultant_id = {$c['id']}
                AND individual_results_requests.status = 'pending'
            ")->getRow()->total;

            CLI::write("Consultor ID {$c['id']} ({$c['name']}): {$count} solicitudes pendientes", 'green');
        }
    }
}
