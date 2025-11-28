<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\BatteryScheduleModel;

class SendBatteryReminders extends BaseCommand
{
    protected $group       = 'Automation';
    protected $name        = 'reminders:send';
    protected $description = 'Envía recordatorios automáticos de evaluaciones de batería psicosocial';
    protected $usage       = 'reminders:send';

    public function run(array $params)
    {
        CLI::write('=== Iniciando envío de recordatorios de batería ===', 'yellow');
        CLI::newLine();

        $scheduleModel = new BatteryScheduleModel();
        $sent = 0;

        // 1. Enviar recordatorios de 30 días
        CLI::write('Verificando recordatorios de 30 días...', 'cyan');
        $reminders30 = $scheduleModel->getPending30DaysNotifications();

        foreach ($reminders30 as $reminder) {
            if ($this->sendReminder($reminder, '30_days')) {
                $scheduleModel->markNotificationSent($reminder['id'], '30_days');
                $sent++;
                CLI::write("  ✓ Recordatorio 30 días enviado a: {$reminder['contact_email']}", 'green');
            } else {
                CLI::write("  ✗ Error enviando a: {$reminder['contact_email']}", 'red');
            }
        }

        CLI::write("Total recordatorios 30 días: " . count($reminders30), 'white');
        CLI::newLine();

        // 2. Enviar recordatorios de 7 días
        CLI::write('Verificando recordatorios de 7 días...', 'cyan');
        $reminders7 = $scheduleModel->getPending7DaysNotifications();

        foreach ($reminders7 as $reminder) {
            if ($this->sendReminder($reminder, '7_days')) {
                $scheduleModel->markNotificationSent($reminder['id'], '7_days');
                $sent++;
                CLI::write("  ✓ Recordatorio 7 días enviado a: {$reminder['contact_email']}", 'green');
            } else {
                CLI::write("  ✗ Error enviando a: {$reminder['contact_email']}", 'red');
            }
        }

        CLI::write("Total recordatorios 7 días: " . count($reminders7), 'white');
        CLI::newLine();

        // 3. Enviar notificaciones de vencimiento
        CLI::write('Verificando evaluaciones vencidas...', 'cyan');
        $overdueReminders = $scheduleModel->getPendingOverdueNotifications();

        foreach ($overdueReminders as $reminder) {
            if ($this->sendReminder($reminder, 'overdue')) {
                $scheduleModel->markNotificationSent($reminder['id'], 'overdue');
                $sent++;
                CLI::write("  ✓ Notificación de vencimiento enviada a: {$reminder['contact_email']}", 'green');
            } else {
                CLI::write("  ✗ Error enviando a: {$reminder['contact_email']}", 'red');
            }
        }

        CLI::write("Total notificaciones vencidas: " . count($overdueReminders), 'white');
        CLI::newLine();

        // Resumen
        CLI::write("=== Resumen ===", 'yellow');
        CLI::write("Total de emails enviados: {$sent}", 'green');
        CLI::write("Proceso completado exitosamente", 'green');
    }

    /**
     * Envía el recordatorio por email
     */
    private function sendReminder(array $reminder, string $type): bool
    {
        try {
            $emailService = \Config\Services::email();

            // Configurar SendGrid si está disponible
            $config = config('Email');
            if (isset($config->sendgrid)) {
                $emailService->initialize($config->sendgrid);
            }

            // Preparar contenido según tipo
            $subject = $this->getEmailSubject($type, $reminder);
            $message = $this->getEmailBody($type, $reminder);

            $emailService->setFrom($config->fromEmail ?? 'noreply@cycloidtalent.com', $config->fromName ?? 'Cycloid Talent SAS');
            $emailService->setTo($reminder['contact_email']);
            $emailService->setSubject($subject);
            $emailService->setMessage($message);

            return $emailService->send();

        } catch (\Exception $e) {
            log_message('error', 'Error sending battery reminder: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene el asunto del email según el tipo
     */
    private function getEmailSubject(string $type, array $reminder): string
    {
        switch ($type) {
            case '30_days':
                return "Recordatorio: Próxima evaluación de riesgo psicosocial en 30 días - {$reminder['company_name']}";
            case '7_days':
                return "Urgente: Evaluación de riesgo psicosocial en 7 días - {$reminder['company_name']}";
            case 'overdue':
                return "⚠️ Evaluación de riesgo psicosocial VENCIDA - {$reminder['company_name']}";
            default:
                return "Recordatorio de evaluación de riesgo psicosocial";
        }
    }

    /**
     * Obtiene el cuerpo del email según el tipo
     */
    private function getEmailBody(string $type, array $reminder): string
    {
        $periodicityText = $reminder['periodicity_years'] == 1 ? 'anual' : 'cada 2 años';
        $riskLevelText = $this->getRiskLevelText($reminder['intralaboral_risk_level']);

        $nextDate = date('d/m/Y', strtotime($reminder['next_evaluation_date']));
        $evaluationDate = date('d/m/Y', strtotime($reminder['evaluation_date']));

        $baseMessage = "
<html>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #2c3e50; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background-color: #f9f9f9; }
        .highlight { background-color: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 15px 0; }
        .warning { background-color: #f8d7da; padding: 15px; border-left: 4px solid #dc3545; margin: 15px 0; }
        .info-table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        .info-table td { padding: 10px; border-bottom: 1px solid #ddd; }
        .info-table td:first-child { font-weight: bold; width: 40%; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>🔔 Recordatorio Bateria de Riesgo Psicosocial</h1>
            <p style='margin: 5px 0 0 0; font-size: 14px;'><strong>Cycloid Talent SAS</strong></p>
            <p style='margin: 5px 0 0 0; font-size: 12px;'><a href='https://cycloidtalent.com/' style='color: #fff; text-decoration: none;'>www.cycloidtalent.com</a></p>
        </div>
        <div class='content'>
            <p>Estimado(a) {$reminder['contact_name']},</p>
";

        // Tabla de información común para todos los emails
        $infoTable = "
            <table class='info-table'>
                <tr>
                    <td>Empresa:</td>
                    <td>{$reminder['company_name']}</td>
                </tr>
                <tr>
                    <td>Ultima evaluacion:</td>
                    <td>{$evaluationDate}</td>
                </tr>
                <tr>
                    <td>Proxima evaluacion:</td>
                    <td><strong>{$nextDate}</strong></td>
                </tr>
                <tr>
                    <td>Nivel de riesgo intralaboral:</td>
                    <td>{$riskLevelText}</td>
                </tr>
                <tr>
                    <td>Periodicidad:</td>
                    <td>Evaluacion {$periodicityText}</td>
                </tr>
            </table>
";

        switch ($type) {
            case '30_days':
                $baseMessage .= "
            <div class='highlight'>
                <p><strong>Le recordamos que en 30 dias se cumple el plazo para realizar la proxima evaluacion de factores de riesgo psicosocial.</strong></p>
            </div>

            {$infoTable}

            <p><strong>Recomendaciones:</strong></p>
            <ul>
                <li>Coordine con su equipo de Seguridad y Salud en el Trabajo la programacion de la evaluacion</li>
                <li>Prepare la informacion de los trabajadores que seran evaluados</li>
                <li>Asegure la disponibilidad de los recursos necesarios</li>
            </ul>
";
                break;

            case '7_days':
                $baseMessage .= "
            <div class='warning'>
                <p><strong>⚠️ URGENTE: Quedan solo 7 dias para realizar la evaluacion de riesgo psicosocial</strong></p>
            </div>

            {$infoTable}

            <p><strong>Acciones inmediatas requeridas:</strong></p>
            <ul>
                <li>Confirme la fecha de aplicacion de la bateria</li>
                <li>Asegure la participacion de todos los trabajadores</li>
                <li>Coordine con el proveedor de la evaluacion</li>
            </ul>
";
                break;

            case 'overdue':
                $baseMessage .= "
            <div class='warning'>
                <p><strong>⚠️ ALERTA: La evaluacion de riesgo psicosocial esta VENCIDA</strong></p>
            </div>

            {$infoTable}

            <p><strong>⚠️ Importancia del cumplimiento:</strong></p>
            <ul>
                <li>La Resolucion 2764 de 2022 establece la periodicidad obligatoria de evaluacion</li>
                <li>El incumplimiento puede generar sanciones por parte del Ministerio del Trabajo</li>
                <li>Es fundamental para la salud y bienestar de sus trabajadores</li>
            </ul>

            <p><strong>Le instamos a programar la evaluacion con caracter urgente.</strong></p>
";
                break;
        }

        $baseMessage .= "
            <p>Para mas informacion o agendar su evaluacion, por favor contactenos.</p>

            <p>Cordialmente,<br>
            <strong>Cycloid Talent SAS</strong><br>
            <a href='https://cycloidtalent.com/' style='color: #007bff; text-decoration: none;'>www.cycloidtalent.com</a></p>
        </div>
        <div class='footer'>
            <p>Este es un mensaje automatico. Por favor no responder a este correo.</p>
            <p>&copy; " . date('Y') . " Cycloid Talent SAS - Sistema de Gestion de Riesgo Psicosocial<br>
            <a href='https://cycloidtalent.com/' style='color: #666; text-decoration: none;'>www.cycloidtalent.com</a></p>
        </div>
    </div>
</body>
</html>
";

        return $baseMessage;
    }

    /**
     * Convierte el nivel de riesgo a texto legible
     */
    private function getRiskLevelText(string $level): string
    {
        $levels = [
            'sin_riesgo' => 'Sin riesgo o riesgo despreciable',
            'riesgo_bajo' => 'Riesgo Bajo',
            'riesgo_medio' => 'Riesgo Medio',
            'riesgo_alto' => 'Riesgo Alto',
            'riesgo_muy_alto' => 'Riesgo Muy Alto',
        ];

        return $levels[$level] ?? $level;
    }
}
