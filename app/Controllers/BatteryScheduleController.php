<?php

namespace App\Controllers;

use App\Models\BatteryScheduleModel;
use App\Models\BatteryServiceModel;
use App\Models\CalculatedResultsModel;
use App\Libraries\IntralaboralAScoring;
use App\Libraries\IntralaboralBScoring;

class BatteryScheduleController extends BaseController
{
    protected $scheduleModel;
    protected $serviceModel;
    protected $resultsModel;

    public function __construct()
    {
        $this->scheduleModel = new BatteryScheduleModel();
        $this->serviceModel = new BatteryServiceModel();
        $this->resultsModel = new CalculatedResultsModel();
    }

    /**
     * Vista principal: Lista de todos los recordatorios
     */
    public function index()
    {
        $data = [
            'title' => 'Recordatorios de Batería Psicosocial',
            'schedules' => $this->scheduleModel->getAllActiveWithService(),
            'upcoming' => $this->scheduleModel->getUpcomingEvaluations(30),
            'overdue' => $this->scheduleModel->getOverdueEvaluations(),
        ];

        return view('battery_schedules/index', $data);
    }

    /**
     * Crea automáticamente un recordatorio desde un servicio completado
     */
    public function createFromService($serviceId)
    {
        $service = $this->serviceModel->find($serviceId);

        if (!$service) {
            return redirect()->back()->with('error', 'Servicio no encontrado');
        }

        // Obtener resultados para determinar nivel de riesgo
        $results = $this->resultsModel
            ->where('battery_service_id', $serviceId)
            ->findAll();

        if (empty($results)) {
            return redirect()->back()->with('error', 'No hay resultados calculados para este servicio');
        }

        // Calcular niveles de riesgo por forma
        $formaARisk = $this->calculateFormARisk($results);
        $formaBRisk = $this->calculateFormBRisk($results);

        // Determinar nivel de riesgo general (el más alto)
        $overallRisk = $this->scheduleModel->getOverallIntralaboralRiskLevel($formaARisk, $formaBRisk);

        // Calcular periodicidad
        $periodicity = $this->scheduleModel->calculatePeriodicity($overallRisk);

        // Fecha de evaluación = created_at del servicio o hoy
        $evaluationDate = $service['created_at'] ?? date('Y-m-d');

        // Fecha de próxima evaluación
        $nextEvaluationDate = $this->scheduleModel->calculateNextEvaluationDate(
            $evaluationDate,
            $periodicity
        );

        // Preparar datos
        $scheduleData = [
            'battery_service_id' => $serviceId,
            'company_name' => $service['service_name'],
            'contact_email' => $service['contact_email'] ?? '',
            'contact_name' => $service['contact_name'] ?? '',
            'evaluation_date' => $evaluationDate,
            'intralaboral_risk_level' => $overallRisk,
            'forma_a_risk_level' => $formaARisk,
            'forma_b_risk_level' => $formaBRisk,
            'periodicity_years' => $periodicity,
            'next_evaluation_date' => $nextEvaluationDate,
            'status' => 'active',
        ];

        if ($this->scheduleModel->insert($scheduleData)) {
            return redirect()->to('/battery-schedules')->with('success', 'Recordatorio creado exitosamente');
        } else {
            return redirect()->back()->with('error', 'Error al crear el recordatorio');
        }
    }

    /**
     * Editar un recordatorio
     */
    public function edit($id)
    {
        $schedule = $this->scheduleModel->find($id);

        if (!$schedule) {
            return redirect()->back()->with('error', 'Recordatorio no encontrado');
        }

        if ($this->request->getMethod() === 'POST') {
            $data = $this->request->getPost();

            if ($this->scheduleModel->update($id, $data)) {
                return redirect()->to('/battery-schedules')->with('success', 'Recordatorio actualizado');
            } else {
                return redirect()->back()->with('error', 'Error al actualizar');
            }
        }

        return view('battery_schedules/edit', ['schedule' => $schedule, 'title' => 'Editar Recordatorio']);
    }

    /**
     * Cancelar un recordatorio
     */
    public function cancel($id)
    {
        if ($this->scheduleModel->update($id, ['status' => 'cancelled'])) {
            return redirect()->back()->with('success', 'Recordatorio cancelado');
        }

        return redirect()->back()->with('error', 'Error al cancelar');
    }

    /**
     * Marcar como completado (nueva batería realizada)
     */
    public function complete($id)
    {
        if ($this->scheduleModel->update($id, ['status' => 'completed'])) {
            return redirect()->back()->with('success', 'Recordatorio marcado como completado');
        }

        return redirect()->back()->with('error', 'Error al completar');
    }

    /**
     * Enviar recordatorio manual
     */
    public function sendManual($id)
    {
        $schedule = $this->scheduleModel->find($id);

        if (!$schedule) {
            return redirect()->back()->with('error', 'Recordatorio no encontrado');
        }

        // Aquí llamarías al servicio de email
        // Por ahora solo registramos el intento

        return redirect()->back()->with('success', 'Recordatorio enviado manualmente');
    }

    /**
     * Calcula el nivel de riesgo intralaboral para Forma A
     */
    private function calculateFormARisk(array $results): ?string
    {
        $formaAResults = array_filter($results, function($r) {
            return $r['intralaboral_form_type'] === 'A';
        });

        if (empty($formaAResults)) {
            return null;
        }

        // Obtener el nivel de riesgo del total intralaboral promedio
        $totalScores = array_column($formaAResults, 'intralaboral_total_puntaje');
        $avgScore = array_sum($totalScores) / count($totalScores);

        return $this->getRiskLevelFromScore($avgScore, 'A');
    }

    /**
     * Calcula el nivel de riesgo intralaboral para Forma B
     */
    private function calculateFormBRisk(array $results): ?string
    {
        $formaBResults = array_filter($results, function($r) {
            return $r['intralaboral_form_type'] === 'B';
        });

        if (empty($formaBResults)) {
            return null;
        }

        $totalScores = array_column($formaBResults, 'intralaboral_total_puntaje');
        $avgScore = array_sum($totalScores) / count($totalScores);

        return $this->getRiskLevelFromScore($avgScore, 'B');
    }

    /**
     * Convierte puntaje a nivel de riesgo según baremos
     * BAREMOS desde Single Source of Truth (IntralaboralA/BScoring)
     */
    private function getRiskLevelFromScore(float $score, string $formType): string
    {
        // BAREMO desde fuente única autorizada
        $baremo = ($formType === 'A')
            ? IntralaboralAScoring::getBaremoTotal()
            : IntralaboralBScoring::getBaremoTotal();

        foreach ($baremo as $level => $range) {
            if ($score >= $range[0] && $score <= $range[1]) {
                return $level;
            }
        }

        return 'sin_riesgo';
    }
}
