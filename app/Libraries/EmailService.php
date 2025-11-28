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
     * Get last email error
     *
     * @return string Error details
     */
    public function getLastError()
    {
        return $this->email->printDebugger(['headers']);
    }
}
