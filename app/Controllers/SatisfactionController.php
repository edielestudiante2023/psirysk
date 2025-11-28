<?php

namespace App\Controllers;

use App\Models\SatisfactionSurveyModel;
use App\Models\BatteryServiceModel;

class SatisfactionController extends BaseController
{
    protected $surveyModel;
    protected $serviceModel;

    public function __construct()
    {
        $this->surveyModel = new SatisfactionSurveyModel();
        $this->serviceModel = new BatteryServiceModel();
    }

    /**
     * Mostrar formulario de encuesta de satisfacción
     */
    public function index($serviceId)
    {
        // Verificar que el usuario sea cliente
        $userRole = session()->get('role');
        if (!in_array($userRole, ['cliente_empresa', 'cliente_gestor'])) {
            return redirect()->back()->with('error', 'No tiene permisos para acceder a esta sección.');
        }

        // Verificar que el servicio exista y esté cerrado
        $service = $this->serviceModel->find($serviceId);
        if (!$service) {
            return redirect()->back()->with('error', 'Servicio no encontrado.');
        }

        if ($service['status'] !== 'cerrado') {
            return redirect()->back()->with('error', 'El servicio aún no está cerrado.');
        }

        // Verificar que el usuario pertenezca a la empresa del servicio
        if ($service['company_id'] != session()->get('company_id')) {
            return redirect()->back()->with('error', 'No tiene permisos para este servicio.');
        }

        // Verificar si ya completó la encuesta
        if ($service['satisfaction_survey_completed']) {
            return redirect()->to('/reports/intralaboral/' . $serviceId)
                ->with('info', 'Ya completó la encuesta de satisfacción para este servicio.');
        }

        return view('satisfaction/survey', [
            'service' => $service,
        ]);
    }

    /**
     * Procesar envío de encuesta de satisfacción
     */
    public function submit($serviceId)
    {
        // Validaciones de permisos (igual que index)
        $userRole = session()->get('role');
        if (!in_array($userRole, ['cliente_empresa', 'cliente_gestor'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No tiene permisos para esta acción.'
            ]);
        }

        $service = $this->serviceModel->find($serviceId);
        if (!$service || $service['status'] !== 'cerrado' ||
            $service['company_id'] != session()->get('company_id')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Servicio no válido o no tiene permisos.'
            ]);
        }

        if ($service['satisfaction_survey_completed']) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Ya completó la encuesta de satisfacción.'
            ]);
        }

        // Validar datos de la encuesta
        $rules = [
            'question_1' => 'required|integer|in_list[1,2,3,4,5]',
            'question_2' => 'required|integer|in_list[1,2,3,4,5]',
            'question_3' => 'required|integer|in_list[1,2,3,4,5]',
            'question_4' => 'required|integer|in_list[1,2,3,4,5]',
            'question_5' => 'required|integer|in_list[1,2,3,4,5]',
            'comments' => 'permit_empty|string|max_length[5000]',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Por favor complete todas las preguntas requeridas.',
                'errors' => $this->validator->getErrors()
            ]);
        }

        // Guardar encuesta
        $data = [
            'service_id' => $serviceId,
            'user_id' => session()->get('user_id'),
            'question_1' => $this->request->getPost('question_1'),
            'question_2' => $this->request->getPost('question_2'),
            'question_3' => $this->request->getPost('question_3'),
            'question_4' => $this->request->getPost('question_4'),
            'question_5' => $this->request->getPost('question_5'),
            'comments' => $this->request->getPost('comments'),
            'completed_at' => date('Y-m-d H:i:s'),
        ];

        try {
            $this->surveyModel->insert($data);

            // Marcar servicio como encuesta completada
            $this->serviceModel->update($serviceId, [
                'satisfaction_survey_completed' => true
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => '¡Gracias por completar la encuesta! Ahora puede descargar sus informes.',
                'redirect' => base_url('/reports/intralaboral/' . $serviceId)
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error al guardar encuesta de satisfacción: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al guardar la encuesta. Por favor intente nuevamente.'
            ]);
        }
    }

    /**
     * Ver resultados de encuesta (solo para admin/consultor/comercial)
     */
    public function view($serviceId)
    {
        $userRole = session()->get('role');
        if (!in_array($userRole, ['admin', 'superadmin', 'consultor', 'comercial'])) {
            return redirect()->back()->with('error', 'No tiene permisos para ver esta información.');
        }

        $service = $this->serviceModel->find($serviceId);
        if (!$service) {
            return redirect()->back()->with('error', 'Servicio no encontrado.');
        }

        $survey = $this->surveyModel->getByService($serviceId);
        $averageScore = $this->surveyModel->getAverageScore($serviceId);

        return view('satisfaction/view', [
            'service' => $service,
            'survey' => $survey,
            'averageScore' => $averageScore
        ]);
    }

    /**
     * Dashboard de análisis de satisfacción (admin/comercial)
     */
    public function dashboard()
    {
        $userRole = session()->get('role');
        if (!in_array($userRole, ['admin', 'superadmin', 'comercial'])) {
            return redirect()->back()->with('error', 'No tiene permisos para acceder a esta sección.');
        }

        // Obtener todas las encuestas con información del servicio y empresa
        $surveys = $this->surveyModel
            ->select('service_satisfaction_surveys.*,
                      battery_services.service_name,
                      battery_services.service_date,
                      companies.company_name,
                      companies.id as company_id,
                      users.name as respondent_name')
            ->join('battery_services', 'battery_services.id = service_satisfaction_surveys.service_id')
            ->join('companies', 'companies.id = battery_services.company_id')
            ->join('users', 'users.id = service_satisfaction_surveys.user_id')
            ->orderBy('service_satisfaction_surveys.completed_at', 'DESC')
            ->findAll();

        // Calcular estadísticas generales
        $totalSurveys = count($surveys);
        $avgGeneral = 0;
        $avgQ1 = 0;
        $avgQ2 = 0;
        $avgQ3 = 0;
        $avgQ4 = 0;
        $avgQ5 = 0;

        if ($totalSurveys > 0) {
            foreach ($surveys as $survey) {
                $avgQ1 += $survey['question_1'];
                $avgQ2 += $survey['question_2'];
                $avgQ3 += $survey['question_3'];
                $avgQ4 += $survey['question_4'];
                $avgQ5 += $survey['question_5'];
            }

            $avgQ1 = round($avgQ1 / $totalSurveys, 2);
            $avgQ2 = round($avgQ2 / $totalSurveys, 2);
            $avgQ3 = round($avgQ3 / $totalSurveys, 2);
            $avgQ4 = round($avgQ4 / $totalSurveys, 2);
            $avgQ5 = round($avgQ5 / $totalSurveys, 2);

            $avgGeneral = round(($avgQ1 + $avgQ2 + $avgQ3 + $avgQ4 + $avgQ5) / 5, 2);
        }

        // Estadísticas por empresa
        $companyModel = new \App\Models\CompanyModel();
        $companies = $companyModel->findAll();
        $companyStats = [];

        foreach ($companies as $company) {
            $stats = $this->surveyModel->getCompanyStats($company['id']);
            if ($stats && $stats['total_surveys'] > 0) {
                $companyStats[] = [
                    'company_name' => $company['company_name'],
                    'company_id' => $company['id'],
                    'total_surveys' => $stats['total_surveys'],
                    'average_score' => round($stats['average_score'], 2)
                ];
            }
        }

        // Ordenar por promedio descendente
        usort($companyStats, function($a, $b) {
            return $b['average_score'] <=> $a['average_score'];
        });

        // Calcular distribución de satisfacción
        $distribution = [
            'muy_bajo' => 0,    // 1.0 - 1.9
            'bajo' => 0,        // 2.0 - 2.9
            'medio' => 0,       // 3.0 - 3.9
            'alto' => 0,        // 4.0 - 4.4
            'muy_alto' => 0     // 4.5 - 5.0
        ];

        foreach ($surveys as $survey) {
            $avg = ($survey['question_1'] + $survey['question_2'] +
                    $survey['question_3'] + $survey['question_4'] +
                    $survey['question_5']) / 5;

            if ($avg < 2.0) $distribution['muy_bajo']++;
            elseif ($avg < 3.0) $distribution['bajo']++;
            elseif ($avg < 4.0) $distribution['medio']++;
            elseif ($avg < 4.5) $distribution['alto']++;
            else $distribution['muy_alto']++;
        }

        return view('satisfaction/dashboard', [
            'surveys' => $surveys,
            'totalSurveys' => $totalSurveys,
            'avgGeneral' => $avgGeneral,
            'avgQ1' => $avgQ1,
            'avgQ2' => $avgQ2,
            'avgQ3' => $avgQ3,
            'avgQ4' => $avgQ4,
            'avgQ5' => $avgQ5,
            'companyStats' => $companyStats,
            'distribution' => $distribution
        ]);
    }
}
