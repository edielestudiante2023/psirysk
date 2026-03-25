<?php

namespace App\Libraries;

/**
 * EmailService - Envía emails via SendGrid API con click tracking desactivado
 * Evita que SendGrid reescriba URLs a url60.cycloidtalent.com (SSL inválido)
 */
class EmailService
{
    protected $fromEmail;
    protected $fromName;
    protected $apiKey;

    public function __construct()
    {
        $this->fromEmail = env('email.fromEmail') ?: 'notificacion.cycloidtalent@cycloidtalent.com';
        $this->fromName = env('email.fromName') ?: 'PsyRisk - Cycloid Talent';
        $this->apiKey = env('email.SMTPPass');
    }

    /**
     * Método central para enviar emails via SendGrid API
     */
    protected function sendViaSendGrid(string $toEmail, string $subject, string $htmlContent, array $options = []): bool
    {
        try {
            $email = new \SendGrid\Mail\Mail();
            $email->setFrom(
                $options['fromEmail'] ?? $this->fromEmail,
                $options['fromName'] ?? $this->fromName
            );
            $email->setSubject($subject);
            $email->addTo($toEmail);
            $email->addContent("text/html", $htmlContent);

            // CC si se proporcionan
            if (!empty($options['cc'])) {
                foreach ($options['cc'] as $cc) {
                    $email->addCc($cc);
                }
            }

            // Desactivar click tracking para evitar reescritura de URLs
            $trackingSettings = new \SendGrid\Mail\TrackingSettings();
            $clickTracking = new \SendGrid\Mail\ClickTracking();
            $clickTracking->setEnable(false);
            $clickTracking->setEnableText(false);
            $trackingSettings->setClickTracking($clickTracking);
            $email->setTrackingSettings($trackingSettings);

            $sendgrid = new \SendGrid($this->apiKey);
            $response = $sendgrid->send($email);

            if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
                log_message('info', "Email enviado a: {$toEmail} - Asunto: {$subject}");
                return true;
            } else {
                log_message('error', "Error enviando email a {$toEmail} (HTTP {$response->statusCode()}): " . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', "Excepción enviando email a {$toEmail}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send assessment link to worker
     */
    public function sendAssessmentLink($toEmail, $workerName, $assessmentLink, $companyName, $expirationDate)
    {
        $message = view('emails/assessment_link', [
            'workerName' => $workerName,
            'assessmentLink' => $assessmentLink,
            'companyName' => $companyName,
            'expirationDate' => $expirationDate
        ]);

        return $this->sendViaSendGrid($toEmail, 'Evaluación de Factores de Riesgo Psicosocial - ' . $companyName, $message);
    }

    /**
     * Send results notification to company manager
     */
    public function sendResultsNotification($toEmail, $managerName, $serviceName, $completedCount, $totalCount)
    {
        $message = view('emails/results_notification', [
            'managerName' => $managerName,
            'serviceName' => $serviceName,
            'completedCount' => $completedCount,
            'totalCount' => $totalCount,
            'dashboardLink' => base_url('dashboard')
        ]);

        return $this->sendViaSendGrid($toEmail, 'Resultados Disponibles - ' . $serviceName, $message);
    }

    /**
     * Send test email to verify configuration
     */
    public function sendTestEmail($toEmail)
    {
        $message = view('emails/test_email', [
            'testDate' => date('Y-m-d H:i:s')
        ]);

        return $this->sendViaSendGrid($toEmail, 'PsyRisk - Test de Configuración de Email', $message);
    }

    /**
     * Send service closure notification to client
     */
    public function sendServiceClosureToClient($toEmail, $clientName, $serviceName, $companyName, $completedCount, $totalCount, $participationPercent)
    {
        $message = view('emails/service_closure_client', [
            'clientName' => $clientName,
            'serviceName' => $serviceName,
            'companyName' => $companyName,
            'completedCount' => $completedCount,
            'totalCount' => $totalCount,
            'participationPercent' => $participationPercent,
            'reportsLink' => base_url('dashboard')
        ]);

        return $this->sendViaSendGrid($toEmail, 'Servicio Finalizado - Informes Disponibles | ' . $serviceName, $message);
    }

    /**
     * Send service closure notification to manager (for billing)
     */
    public function sendServiceClosureToManager($toEmail, $managerName, $serviceName, $companyName, $completedCount, $totalCount, $consultantName, $closureDate)
    {
        $message = view('emails/service_closure_manager', [
            'managerName' => $managerName,
            'serviceName' => $serviceName,
            'companyName' => $companyName,
            'completedCount' => $completedCount,
            'totalCount' => $totalCount,
            'consultantName' => $consultantName,
            'closureDate' => $closureDate
        ]);

        return $this->sendViaSendGrid($toEmail, 'Servicio Cerrado - Proceder con Facturación | ' . $serviceName, $message);
    }

    /**
     * Send service closure notification to commercial (seller)
     */
    public function sendServiceClosureToCommercial($toEmail, $sellerName, $serviceName, $companyName, $completedCount, $closureDate)
    {
        $message = view('emails/service_closure_commercial', [
            'sellerName' => $sellerName,
            'serviceName' => $serviceName,
            'companyName' => $companyName,
            'completedCount' => $completedCount,
            'closureDate' => $closureDate
        ]);

        return $this->sendViaSendGrid($toEmail, 'Servicio Finalizado - Facturación Pendiente | ' . $serviceName, $message);
    }

    /**
     * Send notification to consultant when new access request is submitted
     */
    public function sendRequestNotificationToConsultant($toEmail, $consultantName, $request)
    {
        $message = view('emails/request_notification_consultant', [
            'consultantName' => $consultantName,
            'request' => $request,
            'reviewUrl' => base_url("individual-results/review/{$request['id']}"),
            'approveUrl' => base_url("individual-results/approve/{$request['id']}/{$request['access_token']}")
        ]);

        return $this->sendViaSendGrid($toEmail, 'Nueva Solicitud de Acceso a Resultados Individuales - ' . $request['company_name'], $message);
    }

    /**
     * Send notification to client when request is approved
     */
    public function sendRequestApprovedToClient($toEmail, $clientName, $request)
    {
        $message = view('emails/request_approved_client', [
            'clientName' => $clientName,
            'request' => $request,
            'accessUrl' => base_url("individual-results/view/{$request['access_token']}"),
            'statusUrl' => base_url("individual-results/status/{$request['id']}")
        ]);

        return $this->sendViaSendGrid($toEmail, 'Solicitud Aprobada - Acceso a Resultados Individuales', $message);
    }

    /**
     * Send notification to client when request is rejected
     */
    public function sendRequestRejectedToClient($toEmail, $clientName, $request)
    {
        $message = view('emails/request_rejected_client', [
            'clientName' => $clientName,
            'request' => $request,
            'statusUrl' => base_url("individual-results/status/{$request['id']}")
        ]);

        return $this->sendViaSendGrid($toEmail, 'Solicitud No Aprobada - Acceso a Resultados Individuales', $message);
    }

    /**
     * Send CSV import report to consultant
     */
    public function sendCsvImportReport($toEmail, $consultantName, $importData)
    {
        $message = view('emails/csv_import_report', [
            'consultantName' => $consultantName,
            'importData' => $importData
        ]);

        return $this->sendViaSendGrid($toEmail, 'Informe de Importación CSV - ' . $importData['service_name'], $message);
    }

    /**
     * Get last email error (legacy compatibility)
     */
    public function getLastError()
    {
        return 'Ver logs para detalles del error';
    }
}
