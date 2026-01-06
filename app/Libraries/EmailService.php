<?php

namespace App\Libraries;

use CodeIgniter\Email\Email;

/**
 * EmailService - Helper class for sending emails via SendGrid
 */
class EmailService
{
    protected $email;

    public function __construct()
    {
        $this->email = \Config\Services::email();
    }

    /**
     * Send assessment link to worker
     *
     * @param string $toEmail Worker's email
     * @param string $workerName Worker's name
     * @param string $assessmentLink Unique assessment link
     * @param string $companyName Company name
     * @param string $expirationDate Link expiration date
     * @return bool Success status
     */
    public function sendAssessmentLink($toEmail, $workerName, $assessmentLink, $companyName, $expirationDate)
    {
        $this->email->setTo($toEmail);
        $this->email->setSubject('Evaluación de Factores de Riesgo Psicosocial - ' . $companyName);

        $message = view('emails/assessment_link', [
            'workerName' => $workerName,
            'assessmentLink' => $assessmentLink,
            'companyName' => $companyName,
            'expirationDate' => $expirationDate
        ]);

        $this->email->setMessage($message);

        if ($this->email->send()) {
            log_message('info', "Assessment email sent to: {$toEmail}");
            return true;
        } else {
            log_message('error', "Failed to send assessment email to: {$toEmail}. Error: " . $this->email->printDebugger(['headers']));
            return false;
        }
    }

    /**
     * Send results notification to company manager
     *
     * @param string $toEmail Manager's email
     * @param string $managerName Manager's name
     * @param string $serviceName Service name
     * @param int $completedCount Completed assessments
     * @param int $totalCount Total workers
     * @return bool Success status
     */
    public function sendResultsNotification($toEmail, $managerName, $serviceName, $completedCount, $totalCount)
    {
        $this->email->setTo($toEmail);
        $this->email->setSubject('Resultados Disponibles - ' . $serviceName);

        $message = view('emails/results_notification', [
            'managerName' => $managerName,
            'serviceName' => $serviceName,
            'completedCount' => $completedCount,
            'totalCount' => $totalCount,
            'dashboardLink' => base_url('dashboard')
        ]);

        $this->email->setMessage($message);

        if ($this->email->send()) {
            log_message('info', "Results notification sent to: {$toEmail}");
            return true;
        } else {
            log_message('error', "Failed to send results notification to: {$toEmail}");
            return false;
        }
    }

    /**
     * Send test email to verify SendGrid configuration
     *
     * @param string $toEmail Test recipient email
     * @return bool Success status
     */
    public function sendTestEmail($toEmail)
    {
        $this->email->setTo($toEmail);
        $this->email->setSubject('PsyRisk - Test de Configuración de Email');

        $message = view('emails/test_email', [
            'testDate' => date('Y-m-d H:i:s')
        ]);

        $this->email->setMessage($message);

        if ($this->email->send()) {
            log_message('info', "Test email sent successfully to: {$toEmail}");
            return true;
        } else {
            log_message('error', "Test email failed. Error: " . $this->email->printDebugger(['headers']));
            return false;
        }
    }

    /**
     * Send service closure notification to client
     *
     * @param string $toEmail Client's email
     * @param string $clientName Client's name
     * @param string $serviceName Service name
     * @param string $companyName Company name
     * @param int $completedCount Completed workers
     * @param int $totalCount Total workers
     * @param float $participationPercent Participation percentage
     * @return bool Success status
     */
    public function sendServiceClosureToClient($toEmail, $clientName, $serviceName, $companyName, $completedCount, $totalCount, $participationPercent)
    {
        $this->email->setTo($toEmail);
        $this->email->setSubject('Servicio Finalizado - Informes Disponibles | ' . $serviceName);

        $message = view('emails/service_closure_client', [
            'clientName' => $clientName,
            'serviceName' => $serviceName,
            'companyName' => $companyName,
            'completedCount' => $completedCount,
            'totalCount' => $totalCount,
            'participationPercent' => $participationPercent,
            'reportsLink' => base_url('dashboard')
        ]);

        $this->email->setMessage($message);

        if ($this->email->send()) {
            log_message('info', "Service closure notification sent to client: {$toEmail}");
            return true;
        } else {
            log_message('error', "Failed to send closure notification to client: {$toEmail}");
            return false;
        }
    }

    /**
     * Send service closure notification to manager (for billing)
     *
     * @param string $toEmail Manager's email
     * @param string $managerName Manager's name
     * @param string $serviceName Service name
     * @param string $companyName Company name
     * @param int $completedCount Completed workers
     * @param int $totalCount Total workers
     * @param string $consultantName Consultant name
     * @param string $closureDate Closure date
     * @return bool Success status
     */
    public function sendServiceClosureToManager($toEmail, $managerName, $serviceName, $companyName, $completedCount, $totalCount, $consultantName, $closureDate)
    {
        $this->email->setTo($toEmail);
        $this->email->setSubject('Servicio Cerrado - Proceder con Facturación | ' . $serviceName);

        $message = view('emails/service_closure_manager', [
            'managerName' => $managerName,
            'serviceName' => $serviceName,
            'companyName' => $companyName,
            'completedCount' => $completedCount,
            'totalCount' => $totalCount,
            'consultantName' => $consultantName,
            'closureDate' => $closureDate
        ]);

        $this->email->setMessage($message);

        if ($this->email->send()) {
            log_message('info', "Service closure notification sent to manager: {$toEmail}");
            return true;
        } else {
            log_message('error', "Failed to send closure notification to manager: {$toEmail}");
            return false;
        }
    }

    /**
     * Send service closure notification to commercial (seller)
     *
     * @param string $toEmail Seller's email
     * @param string $sellerName Seller's name
     * @param string $serviceName Service name
     * @param string $companyName Company name
     * @param int $completedCount Completed workers
     * @param string $closureDate Closure date
     * @return bool Success status
     */
    public function sendServiceClosureToCommercial($toEmail, $sellerName, $serviceName, $companyName, $completedCount, $closureDate)
    {
        $this->email->setTo($toEmail);
        $this->email->setSubject('Servicio Finalizado - Facturación Pendiente | ' . $serviceName);

        $message = view('emails/service_closure_commercial', [
            'sellerName' => $sellerName,
            'serviceName' => $serviceName,
            'companyName' => $companyName,
            'completedCount' => $completedCount,
            'closureDate' => $closureDate
        ]);

        $this->email->setMessage($message);

        if ($this->email->send()) {
            log_message('info', "Service closure notification sent to commercial: {$toEmail}");
            return true;
        } else {
            log_message('error', "Failed to send closure notification to commercial: {$toEmail}");
            return false;
        }
    }

    /**
     * Send notification to consultant when new access request is submitted
     *
     * @param string $toEmail Consultant's email
     * @param string $consultantName Consultant's name
     * @param array $request Request details
     * @return bool Success status
     */
    public function sendRequestNotificationToConsultant($toEmail, $consultantName, $request)
    {
        $this->email->setTo($toEmail);
        $this->email->setSubject('Nueva Solicitud de Acceso a Resultados Individuales - ' . $request['company_name']);

        $message = view('emails/request_notification_consultant', [
            'consultantName' => $consultantName,
            'request' => $request,
            'reviewUrl' => base_url("individual-results/review/{$request['id']}"),
            'approveUrl' => base_url("individual-results/approve/{$request['id']}/{$request['access_token']}")
        ]);

        $this->email->setMessage($message);

        if ($this->email->send()) {
            log_message('info', "Request notification sent to consultant: {$toEmail}");
            return true;
        } else {
            log_message('error', "Failed to send request notification to consultant: {$toEmail}");
            return false;
        }
    }

    /**
     * Send notification to client when request is approved
     *
     * @param string $toEmail Client's email
     * @param string $clientName Client's name
     * @param array $request Request details
     * @return bool Success status
     */
    public function sendRequestApprovedToClient($toEmail, $clientName, $request)
    {
        $this->email->setTo($toEmail);
        $this->email->setSubject('Solicitud Aprobada - Acceso a Resultados Individuales');

        $message = view('emails/request_approved_client', [
            'clientName' => $clientName,
            'request' => $request,
            'accessUrl' => base_url("individual-results/view/{$request['access_token']}"),
            'statusUrl' => base_url("individual-results/status/{$request['id']}")
        ]);

        $this->email->setMessage($message);

        if ($this->email->send()) {
            log_message('info', "Request approved notification sent to client: {$toEmail}");
            return true;
        } else {
            log_message('error', "Failed to send request approved notification to client: {$toEmail}");
            return false;
        }
    }

    /**
     * Send notification to client when request is rejected
     *
     * @param string $toEmail Client's email
     * @param string $clientName Client's name
     * @param array $request Request details
     * @return bool Success status
     */
    public function sendRequestRejectedToClient($toEmail, $clientName, $request)
    {
        $this->email->setTo($toEmail);
        $this->email->setSubject('Solicitud No Aprobada - Acceso a Resultados Individuales');

        $message = view('emails/request_rejected_client', [
            'clientName' => $clientName,
            'request' => $request,
            'statusUrl' => base_url("individual-results/status/{$request['id']}")
        ]);

        $this->email->setMessage($message);

        if ($this->email->send()) {
            log_message('info', "Request rejected notification sent to client: {$toEmail}");
            return true;
        } else {
            log_message('error', "Failed to send request rejected notification to client: {$toEmail}");
            return false;
        }
    }

    /**
     * Send CSV import report to consultant
     *
     * @param string $toEmail Consultant's email
     * @param string $consultantName Consultant's name
     * @param array $importData Import details
     * @return bool Success status
     */
    public function sendCsvImportReport($toEmail, $consultantName, $importData)
    {
        $this->email->setTo($toEmail);
        $this->email->setSubject('Informe de Importación CSV - ' . $importData['service_name']);

        $message = view('emails/csv_import_report', [
            'consultantName' => $consultantName,
            'importData' => $importData
        ]);

        $this->email->setMessage($message);

        if ($this->email->send()) {
            log_message('info', "CSV import report sent to: {$toEmail}");
            return true;
        } else {
            log_message('error', "Failed to send CSV import report to: {$toEmail}");
            return false;
        }
    }

    /**
     * Get last email error
     *
     * @return string Error details
     */
    public function getLastError()
    {
        return $this->email->printDebugger(['headers']);
    }
}
