<?php

namespace App\Models;

use CodeIgniter\Model;

class BatteryScheduleModel extends Model
{
    protected $table = 'battery_schedules';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'battery_service_id',
        'company_name',
        'contact_email',
        'contact_name',
        'evaluation_date',
        'intervention_start_date',
        'intralaboral_risk_level',
        'forma_a_risk_level',
        'forma_b_risk_level',
        'periodicity_years',
        'next_evaluation_date',
        'notification_30_days_sent',
        'notification_30_days_sent_at',
        'notification_7_days_sent',
        'notification_7_days_sent_at',
        'notification_overdue_sent',
        'notification_overdue_sent_at',
        'status',
        'notes',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'battery_service_id' => 'required|integer',
        'company_name' => 'required|string|max_length[255]',
        'contact_email' => 'required|valid_email|max_length[255]',
        'evaluation_date' => 'required|valid_date',
        'intralaboral_risk_level' => 'required|in_list[sin_riesgo,riesgo_bajo,riesgo_medio,riesgo_alto,riesgo_muy_alto]',
        'periodicity_years' => 'required|integer|in_list[1,2]',
        'next_evaluation_date' => 'required|valid_date',
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Calcula la periodicidad basándose en el nivel de riesgo intralaboral
     * Resolución 2764 de 2022
     */
    public function calculatePeriodicity(string $riskLevel): int
    {
        // Riesgo Alto o Muy Alto = Evaluación ANUAL (1 año)
        if (in_array($riskLevel, ['riesgo_alto', 'riesgo_muy_alto'])) {
            return 1;
        }

        // Riesgo Sin Riesgo, Bajo o Medio = Evaluación BIENAL (2 años)
        return 2;
    }

    /**
     * Calcula la fecha de próxima evaluación
     */
    public function calculateNextEvaluationDate(string $baseDate, int $periodicityYears): string
    {
        $date = new \DateTime($baseDate);
        $date->modify("+{$periodicityYears} years");
        return $date->format('Y-m-d');
    }

    /**
     * Obtiene el nivel de riesgo intralaboral general (máximo entre Forma A y B)
     */
    public function getOverallIntralaboralRiskLevel(?string $formaA, ?string $formaB): string
    {
        $riskOrder = [
            'sin_riesgo' => 0,
            'riesgo_bajo' => 1,
            'riesgo_medio' => 2,
            'riesgo_alto' => 3,
            'riesgo_muy_alto' => 4,
        ];

        $formaALevel = $riskOrder[$formaA] ?? 0;
        $formaBLevel = $riskOrder[$formaB] ?? 0;

        // Retornar el nivel más alto
        $maxLevel = max($formaALevel, $formaBLevel);

        return array_search($maxLevel, $riskOrder);
    }

    /**
     * Obtiene los recordatorios pendientes de enviar (30 días antes)
     */
    public function getPending30DaysNotifications()
    {
        $targetDate = date('Y-m-d', strtotime('+30 days'));

        return $this->where('status', 'active')
            ->where('next_evaluation_date', $targetDate)
            ->where('notification_30_days_sent', false)
            ->findAll();
    }

    /**
     * Obtiene los recordatorios pendientes de enviar (7 días antes)
     */
    public function getPending7DaysNotifications()
    {
        $targetDate = date('Y-m-d', strtotime('+7 days'));

        return $this->where('status', 'active')
            ->where('next_evaluation_date', $targetDate)
            ->where('notification_7_days_sent', false)
            ->findAll();
    }

    /**
     * Obtiene evaluaciones vencidas sin notificar
     */
    public function getPendingOverdueNotifications()
    {
        $today = date('Y-m-d');

        return $this->where('status', 'active')
            ->where('next_evaluation_date <', $today)
            ->where('notification_overdue_sent', false)
            ->findAll();
    }

    /**
     * Marca notificación como enviada
     */
    public function markNotificationSent(int $id, string $type)
    {
        $data = [
            "notification_{$type}_sent" => true,
            "notification_{$type}_sent_at" => date('Y-m-d H:i:s'),
        ];

        return $this->update($id, $data);
    }

    /**
     * Obtiene todos los recordatorios activos con información de la empresa
     */
    public function getAllActiveWithService()
    {
        return $this->select('battery_schedules.*, battery_services.service_name, battery_services.company_id')
            ->join('battery_services', 'battery_services.id = battery_schedules.battery_service_id')
            ->where('battery_schedules.status', 'active')
            ->orderBy('battery_schedules.next_evaluation_date', 'ASC')
            ->findAll();
    }

    /**
     * Obtiene recordatorios próximos a vencer (30 días)
     */
    public function getUpcomingEvaluations(int $days = 30)
    {
        $targetDate = date('Y-m-d', strtotime("+{$days} days"));

        return $this->select('battery_schedules.*, battery_services.service_name')
            ->join('battery_services', 'battery_services.id = battery_schedules.battery_service_id')
            ->where('battery_schedules.status', 'active')
            ->where('battery_schedules.next_evaluation_date <=', $targetDate)
            ->where('battery_schedules.next_evaluation_date >=', date('Y-m-d'))
            ->orderBy('battery_schedules.next_evaluation_date', 'ASC')
            ->findAll();
    }

    /**
     * Obtiene evaluaciones vencidas
     */
    public function getOverdueEvaluations()
    {
        $today = date('Y-m-d');

        return $this->select('battery_schedules.*, battery_services.service_name')
            ->join('battery_services', 'battery_services.id = battery_schedules.battery_service_id')
            ->where('battery_schedules.status', 'active')
            ->where('battery_schedules.next_evaluation_date <', $today)
            ->orderBy('battery_schedules.next_evaluation_date', 'ASC')
            ->findAll();
    }
}
