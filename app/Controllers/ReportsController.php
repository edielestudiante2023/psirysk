<?php

namespace App\Controllers;

use App\Models\BatteryServiceModel;
use App\Models\CalculatedResultModel;
use App\Models\CompanyModel;
use App\Models\WorkerModel;
use App\Libraries\IntralaboralAScoring;
use App\Libraries\IntralaboralBScoring;
use App\Libraries\ExtralaboralScoring;
use App\Libraries\EstresScoring;
use App\Services\MaxRiskResultsService;

class ReportsController extends BaseController
{
    protected $batteryServiceModel;
    protected $calculatedResultModel;
    protected $companyModel;
    protected $workerModel;

    public function __construct()
    {
        $this->batteryServiceModel = new BatteryServiceModel();
        $this->calculatedResultModel = new CalculatedResultModel();
        $this->companyModel = new CompanyModel();
        $this->workerModel = new WorkerModel();
    }

    /**
     * Verificar acceso del usuario al servicio
     */
    private function checkAccess($serviceId)
    {
        $userRole = session()->get('role_name');

        // Admin y vendedor NO tienen acceso
        if (in_array($userRole, ['admin', 'superadmin', 'comercial'])) {
            return redirect()->to('/dashboard')->with('error', 'No tienes permisos para acceder a esta sección');
        }

        // Obtener información del servicio incluyendo parent_company_id y nombre del consultor
        $service = $this->batteryServiceModel
            ->select('battery_services.*, companies.name as company_name, companies.nit, companies.parent_company_id, users.name as consultant_name')
            ->join('companies', 'companies.id = battery_services.company_id')
            ->join('users', 'users.id = battery_services.consultant_id', 'left')
            ->find($serviceId);

        if (!$service) {
            return redirect()->to('/dashboard')->with('error', 'Servicio no encontrado');
        }

        // Si es cliente, verificar acceso según rol
        if (in_array($userRole, ['cliente_empresa', 'cliente_gestor'])) {
            $userCompanyId = session()->get('company_id');
            $hasAccess = false;

            if ($userRole === 'cliente_empresa') {
                // Solo puede ver servicios de su propia empresa
                $hasAccess = ($service['company_id'] == $userCompanyId);
            } elseif ($userRole === 'cliente_gestor') {
                // Puede ver servicios de su empresa o de empresas hijas
                if ($service['company_id'] == $userCompanyId) {
                    $hasAccess = true;
                } else {
                    // Verificar si la empresa del servicio es hija de la empresa gestora
                    $hasAccess = ($service['parent_company_id'] == $userCompanyId);
                }
            }

            if (!$hasAccess) {
                return redirect()->to('/dashboard')->with('error', 'No tienes permisos para ver este servicio');
            }

            // IMPORTANTE: Cliente solo puede ver informes si el servicio está CERRADO
            // Acepta tanto 'cerrado' como 'finalizado' por compatibilidad
            if (!in_array($service['status'], ['cerrado', 'finalizado'])) {
                // Redirigir a vista de "Servicio en Proceso"
                return view('reports/service_in_progress', ['service' => $service]);
            }

            // NOTA: La encuesta de satisfacción se solicita SOLO al descargar PDFs
            // Los dashboards interactivos están disponibles sin encuesta
        }

        // Consultor puede ver todo (en cualquier estado)
        return $service;
    }

    /**
     * Obtener valores únicos para segmentadores
     */
    private function getUniqueValues($results, $field)
    {
        $values = array_unique(array_column($results, $field));
        return array_filter($values, function($v) {
            return $v !== null && $v !== '';
        });
    }

    /**
     * Helper para obtener etiquetas en español para time_in_company_type
     */
    private function getTimeInCompanyLabels($values)
    {
        $labels = [];
        $mapping = [
            'Menos_de_un_ano' => 'Menos de 1 año',
            'Mas_de_un_ano' => 'Más de 1 año',
            // Valores legacy (por compatibilidad con datos antiguos)
            'less_than_year' => 'Menos de 1 año',
            'more_than_year' => 'Más de 1 año',
            'Meses' => 'Menos de 1 año',
            '0' => 'Menos de 1 año'
        ];

        foreach ($values as $value) {
            $label = $mapping[$value] ?? $value;
            if (!in_array($label, $labels)) {
                $labels[$label] = $value; // key = label, value = valor real
            }
        }

        return $labels;
    }

    /**
     * Dashboard Intralaboral
     */
    public function intralaboral($serviceId)
    {
        $service = $this->checkAccess($serviceId);
        if ($service instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $service;
        }

        // Obtener resultados con JOIN a worker_demographics y workers para obtener datos demográficos completos
        $db = \Config\Database::connect();
        $builder = $db->table('calculated_results cr');
        $builder->select('cr.*,
                          wd.birth_year,
                          (YEAR(CURDATE()) - wd.birth_year) as age,
                          wd.stratum,
                          wd.housing_type,
                          wd.time_in_company_type,
                          wd.time_in_company_years,
                          w.name as worker_name,
                          w.document as worker_document');
        $builder->join('worker_demographics wd', 'wd.worker_id = cr.worker_id', 'left');
        $builder->join('workers w', 'w.id = cr.worker_id', 'left');
        $builder->where('cr.battery_service_id', $serviceId);
        $results = $builder->get()->getResultArray();

        // Preparar datos para segmentadores
        $segmentadores = [
            'niveles_riesgo' => ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'],
            'generos' => $this->getUniqueValues($results, 'gender'),
            'departamentos' => $this->getUniqueValues($results, 'department'),
            'cargos' => $this->getUniqueValues($results, 'position'),
            'tipos_cargo' => $this->getUniqueValues($results, 'position_type'),
            'tipos_contrato' => $this->getUniqueValues($results, 'contract_type'),
            'niveles_estudio' => $this->getUniqueValues($results, 'education_level'),
            'ciudades' => $this->getUniqueValues($results, 'city_residence'),
            'estados_civiles' => $this->getUniqueValues($results, 'marital_status'),
            'estratos' => $this->getUniqueValues($results, 'stratum'),
            'tipos_vivienda' => $this->getUniqueValues($results, 'housing_type'),
            'antiguedad' => $this->getTimeInCompanyLabels($this->getUniqueValues($results, 'time_in_company_type')),
            'tipos_formulario' => ['A', 'B']
        ];

        // Calcular estadísticas generales
        $stats = $this->calculateIntralaboralStats($results);

        // Obtener solicitudes de acceso existentes para este servicio
        $workerIds = array_column($results, 'worker_id');
        $accessRequests = [];
        if (!empty($workerIds)) {
            $requestsBuilder = $db->table('individual_results_requests');
            $requestsBuilder->select('worker_id, request_type, status, access_granted_until, access_token');
            $requestsBuilder->where('service_id', $serviceId);
            $requestsBuilder->whereIn('worker_id', $workerIds);
            $requestsBuilder->whereIn('request_type', ['intralaboral_a', 'intralaboral_b']);
            $requests = $requestsBuilder->get()->getResultArray();

            foreach ($requests as $req) {
                $key = $req['worker_id'] . '_' . $req['request_type'];
                $accessRequests[$key] = $req;
            }
        }

        $data = [
            'title' => 'Dashboard Intralaboral - ' . $service['service_name'],
            'service' => $service,
            'serviceId' => $serviceId,
            'results' => $results,
            'segmentadores' => $segmentadores,
            'stats' => $stats,
            'totalWorkers' => count($results),
            'accessRequests' => $accessRequests
        ];

        return view('reports/intralaboral/dashboard', $data);
    }

    /**
     * Informe Ejecutivo Intralaboral
     */
    public function intralaboralExecutive($serviceId)
    {
        $service = $this->checkAccess($serviceId);
        if ($service instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $service;
        }

        // Obtener resultados
        $results = $this->calculatedResultModel
            ->where('battery_service_id', $serviceId)
            ->findAll();

        // Filtrar solo riesgo medio, alto y muy alto
        $riskResults = array_filter($results, function($r) {
            return in_array($r['intralaboral_total_nivel'], ['riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto']);
        });

        // Calcular totales
        $totalIntralaboral = 0;
        $totalExtralaboral = 0;
        $totalEstres = 0;

        foreach ($results as $result) {
            $totalIntralaboral += $result['intralaboral_total_puntaje'] ?? 0;
            $totalExtralaboral += $result['extralaboral_total_puntaje'] ?? 0;
            $totalEstres += $result['estres_total_puntaje'] ?? 0;
        }

        $count = count($results);
        $totales = [
            'participantes' => $count,
            'promedio_intralaboral' => $count > 0 ? round($totalIntralaboral / $count, 2) : 0,
            'promedio_extralaboral' => $count > 0 ? round($totalExtralaboral / $count, 2) : 0,
            'promedio_estres' => $count > 0 ? round($totalEstres / $count, 2) : 0
        ];

        $data = [
            'title' => 'Informe Ejecutivo Intralaboral - ' . $service['service_name'],
            'service' => $service,
            'totales' => $totales,
            'riskResults' => $riskResults,
            'totalRisk' => count($riskResults)
        ];

        return view('reports/intralaboral/executive', $data);
    }

    /**
     * Dashboard Extralaboral
     */
    public function extralaboral($serviceId)
    {
        $service = $this->checkAccess($serviceId);
        if ($service instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $service;
        }

        // Consulta con JOINs igual que intralaboral
        $db = \Config\Database::connect();
        $builder = $db->table('calculated_results cr');
        $builder->select('cr.*,
                          wd.birth_year,
                          (YEAR(CURDATE()) - wd.birth_year) as age,
                          wd.stratum,
                          wd.housing_type,
                          wd.time_in_company_type,
                          wd.time_in_company_years,
                          w.name as worker_name,
                          w.document as worker_document');
        $builder->join('worker_demographics wd', 'wd.worker_id = cr.worker_id', 'left');
        $builder->join('workers w', 'w.id = cr.worker_id', 'left');
        $builder->where('cr.battery_service_id', $serviceId);
        $results = $builder->get()->getResultArray();

        // Segmentadores (SIN tipos_formulario porque extralaboral no tiene A/B)
        $segmentadores = [
            'niveles_riesgo' => ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'],
            'generos' => $this->getUniqueValues($results, 'gender'),
            'departamentos' => $this->getUniqueValues($results, 'department'),
            'cargos' => $this->getUniqueValues($results, 'position'),
            'tipos_cargo' => $this->getUniqueValues($results, 'position_type'),
            'tipos_contrato' => $this->getUniqueValues($results, 'contract_type'),
            'niveles_estudio' => $this->getUniqueValues($results, 'education_level'),
            'ciudades' => $this->getUniqueValues($results, 'city_residence'),
            'estados_civiles' => $this->getUniqueValues($results, 'marital_status'),
            'estratos' => $this->getUniqueValues($results, 'stratum'),
            'tipos_vivienda' => $this->getUniqueValues($results, 'housing_type'),
            'antiguedad' => $this->getTimeInCompanyLabels($this->getUniqueValues($results, 'time_in_company_type'))
        ];

        $stats = $this->calculateExtralaboralStats($results);

        // Obtener solicitudes de acceso existentes para este servicio
        $workerIds = array_column($results, 'worker_id');
        $accessRequests = [];
        if (!empty($workerIds)) {
            $requestsBuilder = $db->table('individual_results_requests');
            $requestsBuilder->select('worker_id, request_type, status, access_granted_until, access_token');
            $requestsBuilder->where('service_id', $serviceId);
            $requestsBuilder->whereIn('worker_id', $workerIds);
            $requestsBuilder->where('request_type', 'extralaboral');
            $requests = $requestsBuilder->get()->getResultArray();

            foreach ($requests as $req) {
                $key = $req['worker_id'] . '_extralaboral';
                $accessRequests[$key] = $req;
            }
        }

        $data = [
            'title' => 'Dashboard Extralaboral - ' . $service['service_name'],
            'service' => $service,
            'serviceId' => $serviceId,
            'results' => $results,
            'segmentadores' => $segmentadores,
            'stats' => $stats,
            'totalWorkers' => count($results),
            'accessRequests' => $accessRequests
        ];

        return view('reports/extralaboral/dashboard', $data);
    }

    /**
     * Informe Ejecutivo Extralaboral
     */
    public function extralaboralExecutive($serviceId)
    {
        $service = $this->checkAccess($serviceId);
        if ($service instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $service;
        }

        $results = $this->calculatedResultModel
            ->where('battery_service_id', $serviceId)
            ->findAll();

        $riskResults = array_filter($results, function($r) {
            return in_array($r['extralaboral_total_nivel'], ['riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto']);
        });

        $totalIntralaboral = 0;
        $totalExtralaboral = 0;
        $totalEstres = 0;

        foreach ($results as $result) {
            $totalIntralaboral += $result['intralaboral_total_puntaje'] ?? 0;
            $totalExtralaboral += $result['extralaboral_total_puntaje'] ?? 0;
            $totalEstres += $result['estres_total_puntaje'] ?? 0;
        }

        $count = count($results);
        $totales = [
            'participantes' => $count,
            'promedio_intralaboral' => $count > 0 ? round($totalIntralaboral / $count, 2) : 0,
            'promedio_extralaboral' => $count > 0 ? round($totalExtralaboral / $count, 2) : 0,
            'promedio_estres' => $count > 0 ? round($totalEstres / $count, 2) : 0
        ];

        $data = [
            'title' => 'Informe Ejecutivo Extralaboral - ' . $service['service_name'],
            'service' => $service,
            'totales' => $totales,
            'riskResults' => $riskResults,
            'totalRisk' => count($riskResults)
        ];

        return view('reports/extralaboral/executive', $data);
    }

    /**
     * Dashboard Estrés
     */
    public function estres($serviceId)
    {
        $service = $this->checkAccess($serviceId);
        if ($service instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $service;
        }

        // Consulta con JOINs para obtener todos los datos demográficos
        $db = \Config\Database::connect();
        $builder = $db->table('calculated_results cr');
        $builder->select('cr.*,
                          wd.birth_year,
                          (YEAR(CURDATE()) - wd.birth_year) as age,
                          wd.stratum,
                          wd.housing_type,
                          wd.time_in_company_type,
                          wd.time_in_company_years,
                          w.name as worker_name,
                          w.document as worker_document');
        $builder->join('worker_demographics wd', 'wd.worker_id = cr.worker_id', 'left');
        $builder->join('workers w', 'w.id = cr.worker_id', 'left');
        $builder->where('cr.battery_service_id', $serviceId);
        $results = $builder->get()->getResultArray();

        // Obtener respuestas individuales de las 31 preguntas
        $workerIds = array_column($results, 'worker_id');
        $responsesData = [];

        if (!empty($workerIds)) {
            $responsesBuilder = $db->table('responses');
            $responsesBuilder->select('worker_id, question_number, answer_value');
            $responsesBuilder->where('form_type', 'estres');
            $responsesBuilder->whereIn('worker_id', $workerIds);
            $responsesBuilder->orderBy('worker_id, question_number');
            $responses = $responsesBuilder->get()->getResultArray();

            // Organizar respuestas por worker_id
            foreach ($responses as $response) {
                $wid = $response['worker_id'];
                $qnum = $response['question_number'];
                if (!isset($responsesData[$wid])) {
                    $responsesData[$wid] = [];
                }
                $responsesData[$wid][$qnum] = strtolower($response['answer_value']);
            }
        }

        $segmentadores = [
            'niveles_riesgo' => ['muy_bajo', 'bajo', 'medio', 'alto', 'muy_alto'],
            'generos' => $this->getUniqueValues($results, 'gender'),
            'departamentos' => $this->getUniqueValues($results, 'department'),
            'cargos' => $this->getUniqueValues($results, 'position'),
            'tipos_cargo' => $this->getUniqueValues($results, 'position_type'),
            'tipos_contrato' => $this->getUniqueValues($results, 'contract_type'),
            'niveles_estudio' => $this->getUniqueValues($results, 'education_level'),
            'ciudades' => $this->getUniqueValues($results, 'city_residence'),
            'estados_civiles' => $this->getUniqueValues($results, 'marital_status'),
            'estratos' => $this->getUniqueValues($results, 'stratum'),
            'tipos_vivienda' => $this->getUniqueValues($results, 'housing_type'),
            'antiguedad' => $this->getTimeInCompanyLabels($this->getUniqueValues($results, 'time_in_company_type')),
            'tipos_formulario' => ['A', 'B']
        ];

        $stats = $this->calculateEstresStats($results);

        // Obtener solicitudes de acceso existentes para este servicio
        $accessRequests = [];
        if (!empty($workerIds)) {
            $requestsBuilder = $db->table('individual_results_requests');
            $requestsBuilder->select('worker_id, request_type, status, access_granted_until, access_token');
            $requestsBuilder->where('service_id', $serviceId);
            $requestsBuilder->whereIn('worker_id', $workerIds);
            $requestsBuilder->where('request_type', 'estres');
            $requests = $requestsBuilder->get()->getResultArray();

            foreach ($requests as $req) {
                $key = $req['worker_id'] . '_estres';
                $accessRequests[$key] = $req;
            }
        }

        $data = [
            'title' => 'Dashboard Estrés - ' . $service['service_name'],
            'service' => $service,
            'serviceId' => $serviceId,
            'results' => $results,
            'responsesData' => $responsesData,
            'segmentadores' => $segmentadores,
            'stats' => $stats,
            'totalWorkers' => count($results),
            'accessRequests' => $accessRequests
        ];

        return view('reports/estres/dashboard', $data);
    }

    /**
     * Informe Ejecutivo Estrés
     */
    public function estresExecutive($serviceId)
    {
        $service = $this->checkAccess($serviceId);
        if ($service instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $service;
        }

        $results = $this->calculatedResultModel
            ->where('battery_service_id', $serviceId)
            ->findAll();

        $riskResults = array_filter($results, function($r) {
            return in_array($r['estres_total_nivel'], ['medio', 'alto', 'muy_alto']);
        });

        $totalIntralaboral = 0;
        $totalExtralaboral = 0;
        $totalEstres = 0;

        foreach ($results as $result) {
            $totalIntralaboral += $result['intralaboral_total_puntaje'] ?? 0;
            $totalExtralaboral += $result['extralaboral_total_puntaje'] ?? 0;
            $totalEstres += $result['estres_total_puntaje'] ?? 0;
        }

        $count = count($results);
        $totales = [
            'participantes' => $count,
            'promedio_intralaboral' => $count > 0 ? round($totalIntralaboral / $count, 2) : 0,
            'promedio_extralaboral' => $count > 0 ? round($totalExtralaboral / $count, 2) : 0,
            'promedio_estres' => $count > 0 ? round($totalEstres / $count, 2) : 0
        ];

        $data = [
            'title' => 'Informe Ejecutivo Estrés - ' . $service['service_name'],
            'service' => $service,
            'totales' => $totales,
            'riskResults' => $riskResults,
            'totalRisk' => count($riskResults)
        ];

        return view('reports/estres/executive', $data);
    }

    /**
     * Convertir puntaje a nivel de riesgo intralaboral según baremos oficiales
     * @param float $puntaje El puntaje a convertir
     * @param string $domain El dominio específico (liderazgo, control, demandas, recompensas) o 'total'
     * @param string $formType Tipo de formulario 'A' o 'B'
     */
    private function getIntralaboralRiskLevel($puntaje, $domain, $formType = 'A')
    {
        if ($puntaje === null || $puntaje === '') return ['nivel' => '', 'label' => ''];

        // Baremos oficiales según Resolución 2404/2019
        $baremos = [
            'total' => [
                // Tabla 33 - Baremos puntaje total intralaboral (Forma A)
                'A' => [
                    ['min' => 0.0, 'max' => 19.7, 'nivel' => 'sin_riesgo', 'label' => 'Sin Riesgo'],
                    ['min' => 19.8, 'max' => 25.8, 'nivel' => 'riesgo_bajo', 'label' => 'Riesgo Bajo'],
                    ['min' => 25.9, 'max' => 31.5, 'nivel' => 'riesgo_medio', 'label' => 'Riesgo Medio'],
                    ['min' => 31.6, 'max' => 38.0, 'nivel' => 'riesgo_alto', 'label' => 'Riesgo Alto'],
                    ['min' => 38.1, 'max' => 100.0, 'nivel' => 'riesgo_muy_alto', 'label' => 'Riesgo Muy Alto']
                ],
                // Tabla 33 - Baremos puntaje total intralaboral (Forma B)
                'B' => [
                    ['min' => 0.0, 'max' => 20.6, 'nivel' => 'sin_riesgo', 'label' => 'Sin Riesgo'],
                    ['min' => 20.7, 'max' => 26.0, 'nivel' => 'riesgo_bajo', 'label' => 'Riesgo Bajo'],
                    ['min' => 26.1, 'max' => 31.2, 'nivel' => 'riesgo_medio', 'label' => 'Riesgo Medio'],
                    ['min' => 31.3, 'max' => 38.7, 'nivel' => 'riesgo_alto', 'label' => 'Riesgo Alto'],
                    ['min' => 38.8, 'max' => 100.0, 'nivel' => 'riesgo_muy_alto', 'label' => 'Riesgo Muy Alto']
                ]
            ],
            'liderazgo' => [
                // Tabla 31 - Dominios Forma A: Liderazgo y relaciones sociales
                'A' => [
                    ['min' => 0.0, 'max' => 9.1, 'nivel' => 'sin_riesgo', 'label' => 'Sin Riesgo'],
                    ['min' => 9.2, 'max' => 17.7, 'nivel' => 'riesgo_bajo', 'label' => 'Riesgo Bajo'],
                    ['min' => 17.8, 'max' => 25.6, 'nivel' => 'riesgo_medio', 'label' => 'Riesgo Medio'],
                    ['min' => 25.7, 'max' => 34.8, 'nivel' => 'riesgo_alto', 'label' => 'Riesgo Alto'],
                    ['min' => 34.9, 'max' => 100.0, 'nivel' => 'riesgo_muy_alto', 'label' => 'Riesgo Muy Alto']
                ],
                // Tabla 32 - Dominios Forma B: Liderazgo y relaciones sociales
                'B' => [
                    ['min' => 0.0, 'max' => 8.3, 'nivel' => 'sin_riesgo', 'label' => 'Sin Riesgo'],
                    ['min' => 8.4, 'max' => 17.5, 'nivel' => 'riesgo_bajo', 'label' => 'Riesgo Bajo'],
                    ['min' => 17.6, 'max' => 26.7, 'nivel' => 'riesgo_medio', 'label' => 'Riesgo Medio'],
                    ['min' => 26.8, 'max' => 38.3, 'nivel' => 'riesgo_alto', 'label' => 'Riesgo Alto'],
                    ['min' => 38.4, 'max' => 100.0, 'nivel' => 'riesgo_muy_alto', 'label' => 'Riesgo Muy Alto']
                ]
            ],
            'control' => [
                // Tabla 31 - Dominios Forma A: Control sobre el trabajo
                'A' => [
                    ['min' => 0.0, 'max' => 10.7, 'nivel' => 'sin_riesgo', 'label' => 'Sin Riesgo'],
                    ['min' => 10.8, 'max' => 19.0, 'nivel' => 'riesgo_bajo', 'label' => 'Riesgo Bajo'],
                    ['min' => 19.1, 'max' => 29.8, 'nivel' => 'riesgo_medio', 'label' => 'Riesgo Medio'],
                    ['min' => 29.9, 'max' => 40.5, 'nivel' => 'riesgo_alto', 'label' => 'Riesgo Alto'],
                    ['min' => 40.6, 'max' => 100.0, 'nivel' => 'riesgo_muy_alto', 'label' => 'Riesgo Muy Alto']
                ],
                // Tabla 32 - Dominios Forma B: Control sobre el trabajo
                'B' => [
                    ['min' => 0.0, 'max' => 19.4, 'nivel' => 'sin_riesgo', 'label' => 'Sin Riesgo'],
                    ['min' => 19.5, 'max' => 26.4, 'nivel' => 'riesgo_bajo', 'label' => 'Riesgo Bajo'],
                    ['min' => 26.5, 'max' => 34.7, 'nivel' => 'riesgo_medio', 'label' => 'Riesgo Medio'],
                    ['min' => 34.8, 'max' => 43.1, 'nivel' => 'riesgo_alto', 'label' => 'Riesgo Alto'],
                    ['min' => 43.2, 'max' => 100.0, 'nivel' => 'riesgo_muy_alto', 'label' => 'Riesgo Muy Alto']
                ]
            ],
            'demandas' => [
                // Tabla 31 - Dominios Forma A: Demandas del trabajo
                'A' => [
                    ['min' => 0.0, 'max' => 28.5, 'nivel' => 'sin_riesgo', 'label' => 'Sin Riesgo'],
                    ['min' => 28.6, 'max' => 35.0, 'nivel' => 'riesgo_bajo', 'label' => 'Riesgo Bajo'],
                    ['min' => 35.1, 'max' => 41.5, 'nivel' => 'riesgo_medio', 'label' => 'Riesgo Medio'],
                    ['min' => 41.6, 'max' => 47.5, 'nivel' => 'riesgo_alto', 'label' => 'Riesgo Alto'],
                    ['min' => 47.6, 'max' => 100.0, 'nivel' => 'riesgo_muy_alto', 'label' => 'Riesgo Muy Alto']
                ],
                // Tabla 32 - Dominios Forma B: Demandas del trabajo
                'B' => [
                    ['min' => 0.0, 'max' => 26.9, 'nivel' => 'sin_riesgo', 'label' => 'Sin Riesgo'],
                    ['min' => 27.0, 'max' => 33.3, 'nivel' => 'riesgo_bajo', 'label' => 'Riesgo Bajo'],
                    ['min' => 33.4, 'max' => 37.8, 'nivel' => 'riesgo_medio', 'label' => 'Riesgo Medio'],
                    ['min' => 37.9, 'max' => 44.2, 'nivel' => 'riesgo_alto', 'label' => 'Riesgo Alto'],
                    ['min' => 44.3, 'max' => 100.0, 'nivel' => 'riesgo_muy_alto', 'label' => 'Riesgo Muy Alto']
                ]
            ],
            'recompensas' => [
                // Tabla 31 - Dominios Forma A: Recompensas
                'A' => [
                    ['min' => 0.0, 'max' => 4.5, 'nivel' => 'sin_riesgo', 'label' => 'Sin Riesgo'],
                    ['min' => 4.6, 'max' => 11.4, 'nivel' => 'riesgo_bajo', 'label' => 'Riesgo Bajo'],
                    ['min' => 11.5, 'max' => 20.5, 'nivel' => 'riesgo_medio', 'label' => 'Riesgo Medio'],
                    ['min' => 20.6, 'max' => 29.5, 'nivel' => 'riesgo_alto', 'label' => 'Riesgo Alto'],
                    ['min' => 29.6, 'max' => 100.0, 'nivel' => 'riesgo_muy_alto', 'label' => 'Riesgo Muy Alto']
                ],
                // Tabla 32 - Dominios Forma B: Recompensas
                'B' => [
                    ['min' => 0.0, 'max' => 2.5, 'nivel' => 'sin_riesgo', 'label' => 'Sin Riesgo'],
                    ['min' => 2.6, 'max' => 10.0, 'nivel' => 'riesgo_bajo', 'label' => 'Riesgo Bajo'],
                    ['min' => 10.1, 'max' => 17.5, 'nivel' => 'riesgo_medio', 'label' => 'Riesgo Medio'],
                    ['min' => 17.6, 'max' => 27.5, 'nivel' => 'riesgo_alto', 'label' => 'Riesgo Alto'],
                    ['min' => 27.6, 'max' => 100.0, 'nivel' => 'riesgo_muy_alto', 'label' => 'Riesgo Muy Alto']
                ]
            ]
        ];

        // Obtener los baremos correctos
        if (!isset($baremos[$domain])) {
            return ['nivel' => '', 'label' => ''];
        }

        $baremosToUse = $baremos[$domain][$formType] ?? [];

        // Buscar el rango correspondiente
        foreach ($baremosToUse as $range) {
            if ($puntaje >= $range['min'] && $puntaje <= $range['max']) {
                return ['nivel' => $range['nivel'], 'label' => $range['label']];
            }
        }

        return ['nivel' => '', 'label' => ''];
    }

    /**
     * Obtener nivel de riesgo para una dimensión usando el baremo del dominio padre
     * @param float $puntaje El puntaje de la dimensión
     * @param string $dimension El nombre de la dimensión
     * @param string $formType Tipo de formulario 'A' o 'B'
     */
    private function getDimensionRiskLevel($puntaje, $dimension, $formType = 'A')
    {
        if ($puntaje === null || $puntaje === '') return ['nivel' => '', 'label' => ''];

        // Mapeo de dimensiones a su dominio padre
        $dimensionToDomain = [
            'dim_caracteristicas_liderazgo' => 'liderazgo',
            'dim_relaciones_sociales' => 'liderazgo',
            'dim_retroalimentacion' => 'liderazgo',
            'dim_relacion_colaboradores' => 'liderazgo',
            'dim_claridad_rol' => 'control',
            'dim_capacitacion' => 'control',
            'dim_participacion_manejo_cambio' => 'control',
            'dim_oportunidades_desarrollo' => 'control',
            'dim_control_autonomia' => 'control',
            'dim_demandas_ambientales' => 'demandas',
            'dim_demandas_emocionales' => 'demandas',
            'dim_demandas_cuantitativas' => 'demandas',
            'dim_influencia_trabajo_entorno_extralaboral' => 'demandas',
            'dim_demandas_responsabilidad' => 'demandas',
            'dim_demandas_carga_mental' => 'demandas',
            'dim_consistencia_rol' => 'demandas',
            'dim_demandas_jornada_trabajo' => 'demandas',
            'dim_reconocimiento_compensacion' => 'recompensas',
            'dim_recompensas_pertenencia' => 'recompensas'
        ];

        $parentDomain = $dimensionToDomain[$dimension] ?? 'liderazgo';

        // Usar el baremo del dominio padre para clasificar la dimensión
        return $this->getIntralaboralRiskLevel($puntaje, $parentDomain, $formType);
    }

    /**
     * Obtener nivel de riesgo extralaboral según baremos oficiales
     * Tabla 17 (Jefes/Profesionales/Técnicos) y Tabla 18 (Auxiliares/Operarios)
     *
     * @param float $puntaje Puntaje transformado (0-100)
     * @param string $dimension Nombre de la dimensión o 'total'
     * @param string $baremoType 'jefes' o 'auxiliares'
     * @return array ['nivel' => string, 'label' => string]
     */
    private function getExtralaboralRiskLevel($puntaje, $dimension = 'total', $baremoType = 'jefes')
    {
        if ($puntaje === null || $puntaje === '') return ['nivel' => '', 'label' => ''];

        // Baremos oficiales según Resolución 2404/2019
        // Tabla 17: Trabajadores con cargos de jefatura y profesionales o técnicos
        // Tabla 18: Trabajadores con cargos auxiliares y operarios
        $baremos = [
            'jefes' => [
                'total' => [
                    ['min' => 0.0, 'max' => 11.3, 'nivel' => 'sin_riesgo', 'label' => 'Sin Riesgo'],
                    ['min' => 11.4, 'max' => 16.9, 'nivel' => 'riesgo_bajo', 'label' => 'Riesgo Bajo'],
                    ['min' => 17.0, 'max' => 22.6, 'nivel' => 'riesgo_medio', 'label' => 'Riesgo Medio'],
                    ['min' => 22.7, 'max' => 29.0, 'nivel' => 'riesgo_alto', 'label' => 'Riesgo Alto'],
                    ['min' => 29.1, 'max' => 100, 'nivel' => 'riesgo_muy_alto', 'label' => 'Riesgo Muy Alto']
                ],
                'dim_tiempo_fuera_trabajo' => [
                    ['min' => 0.0, 'max' => 6.3, 'nivel' => 'sin_riesgo', 'label' => 'Sin Riesgo'],
                    ['min' => 6.4, 'max' => 25.0, 'nivel' => 'riesgo_bajo', 'label' => 'Riesgo Bajo'],
                    ['min' => 25.1, 'max' => 37.5, 'nivel' => 'riesgo_medio', 'label' => 'Riesgo Medio'],
                    ['min' => 37.6, 'max' => 50.0, 'nivel' => 'riesgo_alto', 'label' => 'Riesgo Alto'],
                    ['min' => 50.1, 'max' => 100, 'nivel' => 'riesgo_muy_alto', 'label' => 'Riesgo Muy Alto']
                ],
                'dim_relaciones_familiares' => [
                    ['min' => 0.0, 'max' => 8.3, 'nivel' => 'sin_riesgo', 'label' => 'Sin Riesgo'],
                    ['min' => 8.4, 'max' => 25.0, 'nivel' => 'riesgo_bajo', 'label' => 'Riesgo Bajo'],
                    ['min' => 25.1, 'max' => 33.3, 'nivel' => 'riesgo_medio', 'label' => 'Riesgo Medio'],
                    ['min' => 33.4, 'max' => 50.0, 'nivel' => 'riesgo_alto', 'label' => 'Riesgo Alto'],
                    ['min' => 50.1, 'max' => 100, 'nivel' => 'riesgo_muy_alto', 'label' => 'Riesgo Muy Alto']
                ],
                'dim_comunicacion_relaciones_interpersonales' => [
                    ['min' => 0.0, 'max' => 0.9, 'nivel' => 'sin_riesgo', 'label' => 'Sin Riesgo'],
                    ['min' => 1.0, 'max' => 10.0, 'nivel' => 'riesgo_bajo', 'label' => 'Riesgo Bajo'],
                    ['min' => 10.1, 'max' => 20.0, 'nivel' => 'riesgo_medio', 'label' => 'Riesgo Medio'],
                    ['min' => 20.1, 'max' => 30.0, 'nivel' => 'riesgo_alto', 'label' => 'Riesgo Alto'],
                    ['min' => 30.1, 'max' => 100, 'nivel' => 'riesgo_muy_alto', 'label' => 'Riesgo Muy Alto']
                ],
                'dim_situacion_economica_grupo_familiar' => [
                    ['min' => 0.0, 'max' => 8.3, 'nivel' => 'sin_riesgo', 'label' => 'Sin Riesgo'],
                    ['min' => 8.4, 'max' => 25.0, 'nivel' => 'riesgo_bajo', 'label' => 'Riesgo Bajo'],
                    ['min' => 25.1, 'max' => 33.3, 'nivel' => 'riesgo_medio', 'label' => 'Riesgo Medio'],
                    ['min' => 33.4, 'max' => 50.0, 'nivel' => 'riesgo_alto', 'label' => 'Riesgo Alto'],
                    ['min' => 50.1, 'max' => 100, 'nivel' => 'riesgo_muy_alto', 'label' => 'Riesgo Muy Alto']
                ],
                'dim_caracteristicas_vivienda_entorno' => [
                    ['min' => 0.0, 'max' => 5.6, 'nivel' => 'sin_riesgo', 'label' => 'Sin Riesgo'],
                    ['min' => 5.7, 'max' => 11.1, 'nivel' => 'riesgo_bajo', 'label' => 'Riesgo Bajo'],
                    ['min' => 11.2, 'max' => 13.9, 'nivel' => 'riesgo_medio', 'label' => 'Riesgo Medio'],
                    ['min' => 14.0, 'max' => 22.2, 'nivel' => 'riesgo_alto', 'label' => 'Riesgo Alto'],
                    ['min' => 22.3, 'max' => 100, 'nivel' => 'riesgo_muy_alto', 'label' => 'Riesgo Muy Alto']
                ],
                'dim_influencia_entorno_extralaboral' => [
                    ['min' => 0.0, 'max' => 8.3, 'nivel' => 'sin_riesgo', 'label' => 'Sin Riesgo'],
                    ['min' => 8.4, 'max' => 16.7, 'nivel' => 'riesgo_bajo', 'label' => 'Riesgo Bajo'],
                    ['min' => 16.8, 'max' => 25.0, 'nivel' => 'riesgo_medio', 'label' => 'Riesgo Medio'],
                    ['min' => 25.1, 'max' => 41.7, 'nivel' => 'riesgo_alto', 'label' => 'Riesgo Alto'],
                    ['min' => 41.8, 'max' => 100, 'nivel' => 'riesgo_muy_alto', 'label' => 'Riesgo Muy Alto']
                ],
                'dim_desplazamiento_vivienda_trabajo' => [
                    ['min' => 0.0, 'max' => 0.9, 'nivel' => 'sin_riesgo', 'label' => 'Sin Riesgo'],
                    ['min' => 1.0, 'max' => 12.5, 'nivel' => 'riesgo_bajo', 'label' => 'Riesgo Bajo'],
                    ['min' => 12.6, 'max' => 25.0, 'nivel' => 'riesgo_medio', 'label' => 'Riesgo Medio'],
                    ['min' => 25.1, 'max' => 43.8, 'nivel' => 'riesgo_alto', 'label' => 'Riesgo Alto'],
                    ['min' => 43.9, 'max' => 100, 'nivel' => 'riesgo_muy_alto', 'label' => 'Riesgo Muy Alto']
                ]
            ],
            'auxiliares' => [
                'total' => [
                    ['min' => 0.0, 'max' => 12.9, 'nivel' => 'sin_riesgo', 'label' => 'Sin Riesgo'],
                    ['min' => 13.0, 'max' => 17.7, 'nivel' => 'riesgo_bajo', 'label' => 'Riesgo Bajo'],
                    ['min' => 17.8, 'max' => 24.2, 'nivel' => 'riesgo_medio', 'label' => 'Riesgo Medio'],
                    ['min' => 24.3, 'max' => 32.3, 'nivel' => 'riesgo_alto', 'label' => 'Riesgo Alto'],
                    ['min' => 32.4, 'max' => 100, 'nivel' => 'riesgo_muy_alto', 'label' => 'Riesgo Muy Alto']
                ],
                'dim_tiempo_fuera_trabajo' => [
                    ['min' => 0.0, 'max' => 6.3, 'nivel' => 'sin_riesgo', 'label' => 'Sin Riesgo'],
                    ['min' => 6.4, 'max' => 25.0, 'nivel' => 'riesgo_bajo', 'label' => 'Riesgo Bajo'],
                    ['min' => 25.1, 'max' => 37.5, 'nivel' => 'riesgo_medio', 'label' => 'Riesgo Medio'],
                    ['min' => 37.6, 'max' => 50.0, 'nivel' => 'riesgo_alto', 'label' => 'Riesgo Alto'],
                    ['min' => 50.1, 'max' => 100, 'nivel' => 'riesgo_muy_alto', 'label' => 'Riesgo Muy Alto']
                ],
                'dim_relaciones_familiares' => [
                    ['min' => 0.0, 'max' => 8.3, 'nivel' => 'sin_riesgo', 'label' => 'Sin Riesgo'],
                    ['min' => 8.4, 'max' => 25.0, 'nivel' => 'riesgo_bajo', 'label' => 'Riesgo Bajo'],
                    ['min' => 25.1, 'max' => 33.3, 'nivel' => 'riesgo_medio', 'label' => 'Riesgo Medio'],
                    ['min' => 33.4, 'max' => 50.0, 'nivel' => 'riesgo_alto', 'label' => 'Riesgo Alto'],
                    ['min' => 50.1, 'max' => 100, 'nivel' => 'riesgo_muy_alto', 'label' => 'Riesgo Muy Alto']
                ],
                'dim_comunicacion_relaciones_interpersonales' => [
                    ['min' => 0.0, 'max' => 5.0, 'nivel' => 'sin_riesgo', 'label' => 'Sin Riesgo'],
                    ['min' => 5.1, 'max' => 15.0, 'nivel' => 'riesgo_bajo', 'label' => 'Riesgo Bajo'],
                    ['min' => 15.1, 'max' => 25.0, 'nivel' => 'riesgo_medio', 'label' => 'Riesgo Medio'],
                    ['min' => 25.1, 'max' => 35.0, 'nivel' => 'riesgo_alto', 'label' => 'Riesgo Alto'],
                    ['min' => 35.1, 'max' => 100, 'nivel' => 'riesgo_muy_alto', 'label' => 'Riesgo Muy Alto']
                ],
                'dim_situacion_economica_grupo_familiar' => [
                    ['min' => 0.0, 'max' => 16.7, 'nivel' => 'sin_riesgo', 'label' => 'Sin Riesgo'],
                    ['min' => 16.8, 'max' => 25.0, 'nivel' => 'riesgo_bajo', 'label' => 'Riesgo Bajo'],
                    ['min' => 25.1, 'max' => 41.7, 'nivel' => 'riesgo_medio', 'label' => 'Riesgo Medio'],
                    ['min' => 41.8, 'max' => 50.0, 'nivel' => 'riesgo_alto', 'label' => 'Riesgo Alto'],
                    ['min' => 50.1, 'max' => 100, 'nivel' => 'riesgo_muy_alto', 'label' => 'Riesgo Muy Alto']
                ],
                'dim_caracteristicas_vivienda_entorno' => [
                    ['min' => 0.0, 'max' => 5.6, 'nivel' => 'sin_riesgo', 'label' => 'Sin Riesgo'],
                    ['min' => 5.7, 'max' => 11.1, 'nivel' => 'riesgo_bajo', 'label' => 'Riesgo Bajo'],
                    ['min' => 11.2, 'max' => 16.7, 'nivel' => 'riesgo_medio', 'label' => 'Riesgo Medio'],
                    ['min' => 16.8, 'max' => 27.8, 'nivel' => 'riesgo_alto', 'label' => 'Riesgo Alto'],
                    ['min' => 27.9, 'max' => 100, 'nivel' => 'riesgo_muy_alto', 'label' => 'Riesgo Muy Alto']
                ],
                'dim_influencia_entorno_extralaboral' => [
                    ['min' => 0.0, 'max' => 0.9, 'nivel' => 'sin_riesgo', 'label' => 'Sin Riesgo'],
                    ['min' => 1.0, 'max' => 16.7, 'nivel' => 'riesgo_bajo', 'label' => 'Riesgo Bajo'],
                    ['min' => 16.8, 'max' => 25.0, 'nivel' => 'riesgo_medio', 'label' => 'Riesgo Medio'],
                    ['min' => 25.1, 'max' => 41.7, 'nivel' => 'riesgo_alto', 'label' => 'Riesgo Alto'],
                    ['min' => 41.8, 'max' => 100, 'nivel' => 'riesgo_muy_alto', 'label' => 'Riesgo Muy Alto']
                ],
                'dim_desplazamiento_vivienda_trabajo' => [
                    ['min' => 0.0, 'max' => 0.9, 'nivel' => 'sin_riesgo', 'label' => 'Sin Riesgo'],
                    ['min' => 1.0, 'max' => 12.5, 'nivel' => 'riesgo_bajo', 'label' => 'Riesgo Bajo'],
                    ['min' => 12.6, 'max' => 25.0, 'nivel' => 'riesgo_medio', 'label' => 'Riesgo Medio'],
                    ['min' => 25.1, 'max' => 43.8, 'nivel' => 'riesgo_alto', 'label' => 'Riesgo Alto'],
                    ['min' => 43.9, 'max' => 100, 'nivel' => 'riesgo_muy_alto', 'label' => 'Riesgo Muy Alto']
                ]
            ]
        ];

        $baremo = $baremos[$baremoType][$dimension] ?? $baremos[$baremoType]['total'];

        foreach ($baremo as $range) {
            if ($puntaje >= $range['min'] && $puntaje <= $range['max']) {
                return ['nivel' => $range['nivel'], 'label' => $range['label']];
            }
        }

        return ['nivel' => '', 'label' => ''];
    }

    /**
     * Calcular estadísticas intralaboral
     */
    /**
     * Calcular estadísticas intralaborales usando lógica MAX RISK
     * Muestra el PEOR resultado entre Forma A y B (jurídicamente correcto)
     */
    private function calculateIntralaboralStats($results)
    {
        if (empty($results)) {
            return [
                'riskDistribution' => [],
                'maxRisk' => [],
                'genderDistribution' => [],
                'formTypeDistribution' => []
            ];
        }

        // Separar resultados por forma
        $resultsA = array_filter($results, fn($r) => $r['intralaboral_form_type'] === 'A');
        $resultsB = array_filter($results, fn($r) => $r['intralaboral_form_type'] === 'B');

        $countA = count($resultsA);
        $countB = count($resultsB);
        $hasFormaA = $countA > 0;
        $hasFormaB = $countB > 0;
        $hasBothForms = $hasFormaA && $hasFormaB;

        // Baremos oficiales para cada forma
        $baremosA = [
            'intralaboral_total' => IntralaboralAScoring::getBaremoTotal(),
            'liderazgo' => IntralaboralAScoring::getBaremoDominio('liderazgo_relaciones_sociales'),
            'control' => IntralaboralAScoring::getBaremoDominio('control'),
            'demandas' => IntralaboralAScoring::getBaremoDominio('demandas'),
            'recompensas' => IntralaboralAScoring::getBaremoDominio('recompensas'),
        ];

        $baremosB = [
            'intralaboral_total' => IntralaboralBScoring::getBaremoTotal(),
            'liderazgo' => IntralaboralBScoring::getBaremoDominio('liderazgo_relaciones_sociales'),
            'control' => IntralaboralBScoring::getBaremoDominio('control'),
            'demandas' => IntralaboralBScoring::getBaremoDominio('demandas'),
            'recompensas' => IntralaboralBScoring::getBaremoDominio('recompensas'),
        ];

        // Orden de niveles de riesgo
        $riskOrder = [
            'sin_riesgo' => 0,
            'riesgo_bajo' => 1,
            'riesgo_medio' => 2,
            'riesgo_alto' => 3,
            'riesgo_muy_alto' => 4,
        ];

        // Función para calcular datos de UNA forma
        $calculateForma = function($field, $baremo, $resultados) use ($riskOrder) {
            $puntajes = array_filter(array_column($resultados, $field), function($v) {
                return $v !== null && $v !== '';
            });

            if (empty($puntajes)) {
                return null;
            }

            $promedio = array_sum($puntajes) / count($puntajes);
            $nivel = 'sin_riesgo';

            foreach ($baremo as $nivelKey => $rango) {
                if ($promedio >= $rango[0] && $promedio <= $rango[1]) {
                    $nivel = $nivelKey;
                    break;
                }
            }

            return [
                'promedio' => round($promedio, 1),
                'nivel' => $nivel,
                'nivel_order' => $riskOrder[$nivel] ?? 0,
                'cantidad' => count($puntajes),
            ];
        };

        // Función para obtener el PEOR resultado entre Forma A y B
        $getWorstResult = function($field, $baremoA, $baremoB) use ($calculateForma, $resultsA, $resultsB, $hasFormaA, $hasFormaB, $hasBothForms) {
            $dataA = $hasFormaA ? $calculateForma($field, $baremoA, $resultsA) : null;
            $dataB = $hasFormaB ? $calculateForma($field, $baremoB, $resultsB) : null;

            // Si solo hay una forma, devolver esa
            if (!$hasBothForms) {
                $data = $dataA ?? $dataB;
                if ($data === null) {
                    return [
                        'promedio' => 0,
                        'nivel' => 'sin_riesgo',
                        'forma_origen' => null,
                        'data_a' => null,
                        'data_b' => null,
                    ];
                }
                $forma = $dataA ? 'A' : 'B';
                return array_merge($data, [
                    'forma_origen' => $forma,
                    'data_a' => $dataA,
                    'data_b' => $dataB,
                ]);
            }

            // Hay ambas formas: determinar cuál es peor
            $orderA = $dataA['nivel_order'] ?? 0;
            $orderB = $dataB['nivel_order'] ?? 0;

            if ($orderA === $orderB) {
                $worst = ($dataA['promedio'] ?? 0) >= ($dataB['promedio'] ?? 0) ? $dataA : $dataB;
                $formaOrigen = ($dataA['promedio'] ?? 0) >= ($dataB['promedio'] ?? 0) ? 'A' : 'B';
            } else {
                $worst = $orderA > $orderB ? $dataA : $dataB;
                $formaOrigen = $orderA > $orderB ? 'A' : 'B';
            }

            return array_merge($worst, [
                'forma_origen' => $formaOrigen,
                'data_a' => $dataA,
                'data_b' => $dataB,
            ]);
        };

        // Mapeo de dimensiones intralaborales
        $mapDimensionesIntra = [
            'caracteristicas_liderazgo' => 'caracteristicas_liderazgo',
            'relaciones_sociales' => 'relaciones_sociales_trabajo',
            'retroalimentacion' => 'retroalimentacion_desempeno',
            'relacion_colaboradores' => 'relacion_con_colaboradores',
            'claridad_rol' => 'claridad_rol',
            'capacitacion' => 'capacitacion',
            'participacion_cambio' => 'participacion_manejo_cambio',
            'oportunidades_desarrollo' => 'oportunidades_desarrollo',
            'control_autonomia' => 'control_autonomia_trabajo',
            'demandas_ambientales' => 'demandas_ambientales_esfuerzo_fisico',
            'demandas_emocionales' => 'demandas_emocionales',
            'demandas_cuantitativas' => 'demandas_cuantitativas',
            'influencia_entorno' => 'influencia_trabajo_entorno_extralaboral',
            'exigencias_responsabilidad' => 'exigencias_responsabilidad_cargo',
            'carga_mental' => 'demandas_carga_mental',
            'consistencia_rol' => 'consistencia_rol',
            'demandas_jornada' => 'demandas_jornada_trabajo',
            'recompensas_pertenencia' => 'recompensas_pertenencia_estabilidad',
            'reconocimiento_compensacion' => 'reconocimiento_compensacion',
        ];

        // Cargar baremos de dimensiones para cada forma
        $baremosA['dimensiones'] = [];
        $baremosB['dimensiones'] = [];
        foreach ($mapDimensionesIntra as $codigoCorto => $codigoLibreria) {
            $baremoA = IntralaboralAScoring::getBaremoDimension($codigoLibreria);
            $baremoB = IntralaboralBScoring::getBaremoDimension($codigoLibreria);
            if ($baremoA !== null) $baremosA['dimensiones'][$codigoCorto] = $baremoA;
            if ($baremoB !== null) $baremosB['dimensiones'][$codigoCorto] = $baremoB;
        }

        // Calcular MAX RISK para Total y Dominios
        $maxRisk = [
            'intralaboral_total' => $getWorstResult('intralaboral_total_puntaje', $baremosA['intralaboral_total'], $baremosB['intralaboral_total']),
            'liderazgo' => $getWorstResult('dom_liderazgo_puntaje', $baremosA['liderazgo'], $baremosB['liderazgo']),
            'control' => $getWorstResult('dom_control_puntaje', $baremosA['control'], $baremosB['control']),
            'demandas' => $getWorstResult('dom_demandas_puntaje', $baremosA['demandas'], $baremosB['demandas']),
            'recompensas' => $getWorstResult('dom_recompensas_puntaje', $baremosA['recompensas'], $baremosB['recompensas']),
        ];

        // Calcular MAX RISK para todas las dimensiones
        $maxRisk['dim_caracteristicas_liderazgo'] = $getWorstResult('dim_caracteristicas_liderazgo_puntaje',
            $baremosA['dimensiones']['caracteristicas_liderazgo'] ?? [],
            $baremosB['dimensiones']['caracteristicas_liderazgo'] ?? []);
        $maxRisk['dim_relaciones_sociales'] = $getWorstResult('dim_relaciones_sociales_puntaje',
            $baremosA['dimensiones']['relaciones_sociales'] ?? [],
            $baremosB['dimensiones']['relaciones_sociales'] ?? []);
        $maxRisk['dim_retroalimentacion'] = $getWorstResult('dim_retroalimentacion_puntaje',
            $baremosA['dimensiones']['retroalimentacion'] ?? [],
            $baremosB['dimensiones']['retroalimentacion'] ?? []);
        $maxRisk['dim_relacion_colaboradores'] = $getWorstResult('dim_relacion_colaboradores_puntaje',
            $baremosA['dimensiones']['relacion_colaboradores'] ?? [],
            $baremosB['dimensiones']['relacion_colaboradores'] ?? []);
        $maxRisk['dim_claridad_rol'] = $getWorstResult('dim_claridad_rol_puntaje',
            $baremosA['dimensiones']['claridad_rol'] ?? [],
            $baremosB['dimensiones']['claridad_rol'] ?? []);
        $maxRisk['dim_capacitacion'] = $getWorstResult('dim_capacitacion_puntaje',
            $baremosA['dimensiones']['capacitacion'] ?? [],
            $baremosB['dimensiones']['capacitacion'] ?? []);
        $maxRisk['dim_participacion_manejo_cambio'] = $getWorstResult('dim_participacion_manejo_cambio_puntaje',
            $baremosA['dimensiones']['participacion_cambio'] ?? [],
            $baremosB['dimensiones']['participacion_cambio'] ?? []);
        $maxRisk['dim_oportunidades_desarrollo'] = $getWorstResult('dim_oportunidades_desarrollo_puntaje',
            $baremosA['dimensiones']['oportunidades_desarrollo'] ?? [],
            $baremosB['dimensiones']['oportunidades_desarrollo'] ?? []);
        $maxRisk['dim_control_autonomia'] = $getWorstResult('dim_control_autonomia_puntaje',
            $baremosA['dimensiones']['control_autonomia'] ?? [],
            $baremosB['dimensiones']['control_autonomia'] ?? []);
        $maxRisk['dim_demandas_ambientales'] = $getWorstResult('dim_demandas_ambientales_puntaje',
            $baremosA['dimensiones']['demandas_ambientales'] ?? [],
            $baremosB['dimensiones']['demandas_ambientales'] ?? []);
        $maxRisk['dim_demandas_emocionales'] = $getWorstResult('dim_demandas_emocionales_puntaje',
            $baremosA['dimensiones']['demandas_emocionales'] ?? [],
            $baremosB['dimensiones']['demandas_emocionales'] ?? []);
        $maxRisk['dim_demandas_cuantitativas'] = $getWorstResult('dim_demandas_cuantitativas_puntaje',
            $baremosA['dimensiones']['demandas_cuantitativas'] ?? [],
            $baremosB['dimensiones']['demandas_cuantitativas'] ?? []);
        $maxRisk['dim_influencia_trabajo_entorno_extralaboral'] = $getWorstResult('dim_influencia_trabajo_entorno_extralaboral_puntaje',
            $baremosA['dimensiones']['influencia_entorno'] ?? [],
            $baremosB['dimensiones']['influencia_entorno'] ?? []);
        $maxRisk['dim_demandas_responsabilidad'] = $getWorstResult('dim_demandas_responsabilidad_puntaje',
            $baremosA['dimensiones']['exigencias_responsabilidad'] ?? [],
            $baremosB['dimensiones']['exigencias_responsabilidad'] ?? []);
        $maxRisk['dim_demandas_carga_mental'] = $getWorstResult('dim_demandas_carga_mental_puntaje',
            $baremosA['dimensiones']['carga_mental'] ?? [],
            $baremosB['dimensiones']['carga_mental'] ?? []);
        $maxRisk['dim_consistencia_rol'] = $getWorstResult('dim_consistencia_rol_puntaje',
            $baremosA['dimensiones']['consistencia_rol'] ?? [],
            $baremosB['dimensiones']['consistencia_rol'] ?? []);
        $maxRisk['dim_demandas_jornada_trabajo'] = $getWorstResult('dim_demandas_jornada_trabajo_puntaje',
            $baremosA['dimensiones']['demandas_jornada'] ?? [],
            $baremosB['dimensiones']['demandas_jornada'] ?? []);
        $maxRisk['dim_recompensas_pertenencia'] = $getWorstResult('dim_recompensas_pertenencia_puntaje',
            $baremosA['dimensiones']['recompensas_pertenencia'] ?? [],
            $baremosB['dimensiones']['recompensas_pertenencia'] ?? []);
        $maxRisk['dim_reconocimiento_compensacion'] = $getWorstResult('dim_reconocimiento_compensacion_puntaje',
            $baremosA['dimensiones']['reconocimiento_compensacion'] ?? [],
            $baremosB['dimensiones']['reconocimiento_compensacion'] ?? []);

        // Distribución de riesgo (basada en niveles individuales de trabajadores)
        $riskDistribution = [
            'sin_riesgo' => 0,
            'riesgo_bajo' => 0,
            'riesgo_medio' => 0,
            'riesgo_alto' => 0,
            'riesgo_muy_alto' => 0
        ];

        foreach ($results as $result) {
            if (!empty($result['intralaboral_total_nivel'])) {
                $nivel = $result['intralaboral_total_nivel'];
                if (isset($riskDistribution[$nivel])) {
                    $riskDistribution[$nivel]++;
                }
            }
        }

        // Distribución por género
        $genderDistribution = [];
        foreach ($results as $result) {
            $gender = $result['gender'] ?? 'No especificado';
            if (!isset($genderDistribution[$gender])) {
                $genderDistribution[$gender] = 0;
            }
            $genderDistribution[$gender]++;
        }

        return [
            'riskDistribution' => $riskDistribution,
            'maxRisk' => $maxRisk,
            'genderDistribution' => $genderDistribution,
            'formTypeDistribution' => ['A' => $countA, 'B' => $countB],
            'has_forma_a' => $hasFormaA,
            'has_forma_b' => $hasFormaB,
            'has_both_forms' => $hasBothForms,
        ];
    }

    /**
     * Aplicar baremo para determinar nivel de riesgo
     *
     * @param float $puntaje Puntaje transformado (0-100)
     * @param array $baremo Array con rangos por nivel ['sin_riesgo' => [min, max], ...]
     * @return string Nivel de riesgo ('sin_riesgo', 'riesgo_bajo', etc.)
     */
    private function aplicarBaremo($puntaje, $baremo)
    {
        if (empty($baremo) || !is_array($baremo)) {
            return 'sin_riesgo';
        }

        foreach ($baremo as $nivel => $rango) {
            if (!is_array($rango) || count($rango) < 2) {
                continue;
            }

            $min = $rango[0];
            $max = $rango[1];

            if ($puntaje >= $min && $puntaje <= $max) {
                return $nivel;
            }
        }

        return 'sin_riesgo';
    }

    /**
     * Calcular estadísticas extralaboral
     */
    private function calculateExtralaboralStats($results)
    {
        if (empty($results)) {
            return [
                'riskDistribution' => [],
                'maxRisk' => [],
                'genderDistribution' => [],
                'has_forma_a' => false,
                'has_forma_b' => false
            ];
        }

        // Mapeo entre nombres de dimensiones en la vista y nombres en la BD
        $dimensionMapping = [
            'dim_tiempo_fuera_trabajo' => 'extralaboral_tiempo_fuera',
            'dim_relaciones_familiares' => 'extralaboral_relaciones_familiares',
            'dim_comunicacion_relaciones_interpersonales' => 'extralaboral_comunicacion',
            'dim_situacion_economica_grupo_familiar' => 'extralaboral_situacion_economica',
            'dim_caracteristicas_vivienda_entorno' => 'extralaboral_caracteristicas_vivienda',
            'dim_influencia_entorno_extralaboral' => 'extralaboral_influencia_entorno',
            'dim_desplazamiento_vivienda_trabajo' => 'extralaboral_desplazamiento'
        ];

        // Separar resultados por forma (Forma A = intralaboral_form_type 'A', Forma B = 'B')
        $resultsA = array_filter($results, fn($r) => ($r['intralaboral_form_type'] ?? '') === 'A');
        $resultsB = array_filter($results, fn($r) => ($r['intralaboral_form_type'] ?? '') === 'B');

        $hasFormaA = !empty($resultsA);
        $hasFormaB = !empty($resultsB);

        // Baremos oficiales para cada forma
        $baremosA = ExtralaboralScoring::getBaremosDimensionesA();
        $baremosB = ExtralaboralScoring::getBaremosDimensionesB();

        // Función helper para calcular promedio y nivel de una forma
        $calculateForma = function($field, $baremo, $formResults) {
            if (empty($formResults)) {
                return null;
            }
            $sum = 0;
            $count = 0;
            foreach ($formResults as $r) {
                if (isset($r[$field]) && $r[$field] !== null && $r[$field] !== '') {
                    $sum += floatval($r[$field]);
                    $count++;
                }
            }
            if ($count === 0) {
                return null;
            }
            $promedio = round($sum / $count, 1);
            $nivel = $this->aplicarBaremo($promedio, $baremo);
            return [
                'promedio' => $promedio,
                'nivel' => $nivel,
                'count' => $count
            ];
        };

        // Función para obtener el PEOR resultado entre Forma A y B
        $getWorstResult = function($field, $baremoA, $baremoB) use ($calculateForma, $resultsA, $resultsB, $hasFormaA, $hasFormaB) {
            $dataA = $hasFormaA ? $calculateForma($field, $baremoA, $resultsA) : null;
            $dataB = $hasFormaB ? $calculateForma($field, $baremoB, $resultsB) : null;

            // Si solo hay una forma, retornar esa
            if ($dataA && !$dataB) {
                return array_merge($dataA, ['forma_origen' => 'A', 'data_a' => $dataA, 'data_b' => null]);
            }
            if ($dataB && !$dataA) {
                return array_merge($dataB, ['forma_origen' => 'B', 'data_a' => null, 'data_b' => $dataB]);
            }
            if (!$dataA && !$dataB) {
                return ['promedio' => 0, 'nivel' => 'sin_riesgo', 'forma_origen' => null, 'data_a' => null, 'data_b' => null];
            }

            // Comparar niveles (mayor = peor)
            $riskOrder = ['sin_riesgo' => 1, 'riesgo_bajo' => 2, 'riesgo_medio' => 3, 'riesgo_alto' => 4, 'riesgo_muy_alto' => 5];
            $orderA = $riskOrder[$dataA['nivel']] ?? 0;
            $orderB = $riskOrder[$dataB['nivel']] ?? 0;

            // Determinar cuál es peor
            if ($orderA === $orderB) {
                // Mismo nivel, usar el promedio más alto
                $worst = ($dataA['promedio'] >= $dataB['promedio']) ? $dataA : $dataB;
                $formaOrigen = ($dataA['promedio'] >= $dataB['promedio']) ? 'A' : 'B';
            } else {
                $worst = ($orderA > $orderB) ? $dataA : $dataB;
                $formaOrigen = ($orderA > $orderB) ? 'A' : 'B';
            }

            return array_merge($worst, [
                'forma_origen' => $formaOrigen,
                'data_a' => $dataA,
                'data_b' => $dataB
            ]);
        };

        // Calcular MAX RISK para total extralaboral
        $maxRisk = [
            'extralaboral_total' => $getWorstResult('extralaboral_total_puntaje', $baremosA['total'], $baremosB['total'])
        ];

        // Calcular MAX RISK para las 7 dimensiones
        $dimensionKeys = [
            'tiempo_fuera_trabajo' => 'extralaboral_tiempo_fuera_puntaje',
            'relaciones_familiares' => 'extralaboral_relaciones_familiares_puntaje',
            'comunicacion_relaciones' => 'extralaboral_comunicacion_puntaje',
            'situacion_economica' => 'extralaboral_situacion_economica_puntaje',
            'caracteristicas_vivienda' => 'extralaboral_caracteristicas_vivienda_puntaje',
            'influencia_entorno' => 'extralaboral_influencia_entorno_puntaje',
            'desplazamiento' => 'extralaboral_desplazamiento_puntaje'
        ];

        foreach ($dimensionKeys as $dimKey => $puntajeField) {
            $maxRisk[$dimKey] = $getWorstResult($puntajeField, $baremosA[$dimKey], $baremosB[$dimKey]);
        }

        // Agregar también con el formato de vista (dim_*)
        foreach ($dimensionMapping as $viewKey => $dbKey) {
            $cleanKey = str_replace('extralaboral_', '', $dbKey);
            if (isset($maxRisk[$cleanKey])) {
                $maxRisk[$viewKey] = $maxRisk[$cleanKey];
            }
        }

        // Distribución de riesgo (basado en datos individuales)
        $riskDistribution = [
            'sin_riesgo' => 0,
            'riesgo_bajo' => 0,
            'riesgo_medio' => 0,
            'riesgo_alto' => 0,
            'riesgo_muy_alto' => 0
        ];

        foreach ($results as $result) {
            if (!empty($result['extralaboral_total_nivel'])) {
                $nivel = $result['extralaboral_total_nivel'];
                if (isset($riskDistribution[$nivel])) {
                    $riskDistribution[$nivel]++;
                }
            }
        }

        // Distribución por género
        $genderDistribution = [];
        foreach ($results as $result) {
            $gender = $result['gender'] ?? 'No especificado';
            if (!isset($genderDistribution[$gender])) {
                $genderDistribution[$gender] = 0;
            }
            $genderDistribution[$gender]++;
        }

        return [
            'maxRisk' => $maxRisk,
            'riskDistribution' => $riskDistribution,
            'genderDistribution' => $genderDistribution,
            'has_forma_a' => $hasFormaA,
            'has_forma_b' => $hasFormaB
        ];
    }

    /**
     * Calcular estadísticas estrés
     */
    private function calculateEstresStats($results)
    {
        if (empty($results)) {
            return [
                'riskDistribution' => [],
                'symptomAverages' => [],
                'genderDistribution' => [],
                'formTypeDistribution' => [],
                'maxRisk' => []
            ];
        }

        // Obtener baremos oficiales para estrés
        $baremosA = \App\Libraries\EstresScoring::getBaremoA();
        $baremosB = \App\Libraries\EstresScoring::getBaremoB();

        $stats = [
            'riskDistribution' => [
                'muy_bajo' => 0,
                'bajo' => 0,
                'medio' => 0,
                'alto' => 0,
                'muy_alto' => 0
            ],
            'symptomAverages' => [
                'fisiologicos' => 0,
                'comportamiento_social' => 0,
                'intelectuales_laborales' => 0,
                'psicoemocionales' => 0
            ],
            'genderDistribution' => [],
            'formTypeDistribution' => ['A' => 0, 'B' => 0],
            'maxRisk' => [
                'estres_total' => ['promedio' => 0, 'nivel' => 'muy_bajo', 'forma_origen' => null, 'data_a' => null, 'data_b' => null],
                'sintomas_fisiologicos' => ['promedio' => 0, 'nivel' => 'muy_bajo', 'forma_origen' => null, 'data_a' => null, 'data_b' => null],
                'sintomas_comportamiento' => ['promedio' => 0, 'nivel' => 'muy_bajo', 'forma_origen' => null, 'data_a' => null, 'data_b' => null],
                'sintomas_intelectuales' => ['promedio' => 0, 'nivel' => 'muy_bajo', 'forma_origen' => null, 'data_a' => null, 'data_b' => null],
                'sintomas_psicoemocionales' => ['promedio' => 0, 'nivel' => 'muy_bajo', 'forma_origen' => null, 'data_a' => null, 'data_b' => null]
            ]
        ];

        $count = count($results);
        $totals = array_fill_keys(array_keys($stats['symptomAverages']), 0);

        // Agrupar resultados por worker_id para aplicar MAX RISK
        $workerData = [];
        foreach ($results as $result) {
            $workerId = $result['worker_id'];
            $formType = $result['intralaboral_form_type'] ?? '';

            if (!isset($workerData[$workerId])) {
                $workerData[$workerId] = [
                    'A' => null,
                    'B' => null,
                    'gender' => $result['gender'] ?? 'No especificado'
                ];
            }

            $workerData[$workerId][$formType] = $result;
        }

        // Función helper para aplicar baremo
        $aplicarBaremo = function($puntaje, $forma) use ($baremosA, $baremosB) {
            $baremo = ($forma === 'A') ? $baremosA : $baremosB;
            foreach ($baremo as $nivel => $rango) {
                if ($puntaje >= $rango[0] && $puntaje <= $rango[1]) {
                    return $nivel;
                }
            }
            return 'muy_bajo';
        };

        // Calcular MAX RISK por trabajador
        $maxRiskTotals = [
            'estres_total' => 0,
            'sintomas_fisiologicos' => 0,
            'sintomas_comportamiento' => 0,
            'sintomas_intelectuales' => 0,
            'sintomas_psicoemocionales' => 0
        ];

        foreach ($workerData as $workerId => $forms) {
            $dataA = $forms['A'];
            $dataB = $forms['B'];

            // Determinar cual forma tiene el peor riesgo (MAX RISK)
            $maxRiesgoData = null;
            $formaOrigen = null;

            if ($dataA && $dataB) {
                $puntajeA = $dataA['estres_total_puntaje'] ?? 0;
                $puntajeB = $dataB['estres_total_puntaje'] ?? 0;
                $nivelA = $aplicarBaremo($puntajeA, 'A');
                $nivelB = $aplicarBaremo($puntajeB, 'B');

                // Orden de severidad de niveles
                $ordenSeveridad = ['muy_bajo' => 0, 'bajo' => 1, 'medio' => 2, 'alto' => 3, 'muy_alto' => 4];

                if ($ordenSeveridad[$nivelB] > $ordenSeveridad[$nivelA]) {
                    $maxRiesgoData = $dataB;
                    $formaOrigen = 'B';
                } else {
                    $maxRiesgoData = $dataA;
                    $formaOrigen = 'A';
                }
            } elseif ($dataA) {
                $maxRiesgoData = $dataA;
                $formaOrigen = 'A';
            } elseif ($dataB) {
                $maxRiesgoData = $dataB;
                $formaOrigen = 'B';
            }

            if ($maxRiesgoData) {
                // Acumular para distribución de riesgo
                $nivel = $aplicarBaremo($maxRiesgoData['estres_total_puntaje'] ?? 0, $formaOrigen);
                if (isset($stats['riskDistribution'][$nivel])) {
                    $stats['riskDistribution'][$nivel]++;
                }

                // Acumular totales para MAX RISK
                $maxRiskTotals['estres_total'] += $maxRiesgoData['estres_total_puntaje'] ?? 0;
                $maxRiskTotals['sintomas_fisiologicos'] += $maxRiesgoData['estres_sintomas_fisiologicos_puntaje'] ?? 0;
                $maxRiskTotals['sintomas_comportamiento'] += $maxRiesgoData['estres_sintomas_comportamiento_social_puntaje'] ?? 0;
                $maxRiskTotals['sintomas_intelectuales'] += $maxRiesgoData['estres_sintomas_intelectuales_laborales_puntaje'] ?? 0;
                $maxRiskTotals['sintomas_psicoemocionales'] += $maxRiesgoData['estres_sintomas_psicoemocionales_puntaje'] ?? 0;

                // Acumular para promedios simples (compatibilidad)
                $totals['fisiologicos'] += $maxRiesgoData['estres_sintomas_fisiologicos_puntaje'] ?? 0;
                $totals['comportamiento_social'] += $maxRiesgoData['estres_sintomas_comportamiento_social_puntaje'] ?? 0;
                $totals['intelectuales_laborales'] += $maxRiesgoData['estres_sintomas_intelectuales_laborales_puntaje'] ?? 0;
                $totals['psicoemocionales'] += $maxRiesgoData['estres_sintomas_psicoemocionales_puntaje'] ?? 0;

                // Distribución por género
                $gender = $forms['gender'];
                if (!isset($stats['genderDistribution'][$gender])) {
                    $stats['genderDistribution'][$gender] = 0;
                }
                $stats['genderDistribution'][$gender]++;

                // Distribución por tipo de formulario
                if (isset($stats['formTypeDistribution'][$formaOrigen])) {
                    $stats['formTypeDistribution'][$formaOrigen]++;
                }
            }
        }

        // Calcular promedios MAX RISK
        $numWorkers = count($workerData);
        if ($numWorkers > 0) {
            // Total estrés
            $promedioTotal = $maxRiskTotals['estres_total'] / $numWorkers;
            $stats['maxRisk']['estres_total']['promedio'] = round($promedioTotal, 1);
            $stats['maxRisk']['estres_total']['nivel'] = $aplicarBaremo($promedioTotal, 'B'); // Usar baremo más conservador
            $stats['maxRisk']['estres_total']['forma_origen'] = null; // Promedio de ambas formas

            // Síntomas
            foreach (['sintomas_fisiologicos', 'sintomas_comportamiento', 'sintomas_intelectuales', 'sintomas_psicoemocionales'] as $sintoma) {
                $promedio = $maxRiskTotals[$sintoma] / $numWorkers;
                $stats['maxRisk'][$sintoma]['promedio'] = round($promedio, 1);
                // Los síntomas no tienen baremos específicos, solo se muestran como porcentajes
            }

            // Calcular promedios simples (compatibilidad)
            foreach ($totals as $key => $total) {
                $stats['symptomAverages'][$key] = round($total / $numWorkers, 2);
            }
        }

        return $stats;
    }

    /**
     * Verificar si cliente completó encuesta de satisfacción (usado vía AJAX)
     */
    public function checkSurveyCompletion($serviceId)
    {
        log_message('info', '[SURVEY-CHECK] Iniciando verificación para service_id: ' . $serviceId);

        $userRole = session()->get('role');
        log_message('info', '[SURVEY-CHECK] User role: ' . $userRole);

        // Solo aplicar a clientes
        if (!in_array($userRole, ['cliente_empresa', 'cliente_gestor'])) {
            log_message('info', '[SURVEY-CHECK] Usuario es consultor, permitiendo descarga sin encuesta');
            return $this->response->setJSON([
                'completed' => true, // Consultor no necesita encuesta
                'can_download' => true
            ]);
        }

        $service = $this->batteryServiceModel->find($serviceId);
        log_message('info', '[SURVEY-CHECK] Servicio encontrado: ' . ($service ? 'SI' : 'NO'));

        if (!$service) {
            log_message('error', '[SURVEY-CHECK] Servicio no encontrado para ID: ' . $serviceId);
            return $this->response->setJSON([
                'completed' => false,
                'can_download' => false,
                'message' => 'Servicio no encontrado'
            ]);
        }

        log_message('info', '[SURVEY-CHECK] Servicio status: ' . $service['status']);
        log_message('info', '[SURVEY-CHECK] Encuesta completada: ' . ($service['satisfaction_survey_completed'] ?? 'NULL'));

        // Verificar que el servicio esté cerrado
        // Acepta tanto 'cerrado' como 'finalizado' por compatibilidad
        if (!in_array($service['status'], ['cerrado', 'finalizado'])) {
            log_message('warning', '[SURVEY-CHECK] Servicio no cerrado, bloqueando descarga');
            return $this->response->setJSON([
                'completed' => false,
                'can_download' => false,
                'message' => 'El servicio aún no está cerrado'
            ]);
        }

        // Verificar si completó la encuesta
        if (!$service['satisfaction_survey_completed']) {
            log_message('warning', '[SURVEY-CHECK] Encuesta no completada, mostrando modal');
            return $this->response->setJSON([
                'completed' => false,
                'can_download' => false,
                'message' => 'Debe completar la encuesta de satisfacción antes de descargar informes',
                'survey_url' => base_url('/satisfaction/survey/' . $serviceId)
            ]);
        }

        log_message('info', '[SURVEY-CHECK] Todo OK, permitiendo descarga');
        return $this->response->setJSON([
            'completed' => true,
            'can_download' => true
        ]);
    }

    /**
     * Exportar a Excel
     */
    public function exportExcel($serviceId, $type)
    {
        // Verificar acceso y encuesta de satisfacción
        $service = $this->checkAccess($serviceId);
        if ($service instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $service;
        }

        // TODO: Implementar exportación a Excel
        return redirect()->back()->with('info', 'Funcionalidad de exportación a Excel en desarrollo');
    }

    /**
     * Exportar PDF completo
     */
    public function exportPDF($serviceId, $type)
    {
        try {
            // Verificar acceso
            $service = $this->checkAccess($serviceId);
            if ($service instanceof \CodeIgniter\HTTP\RedirectResponse) {
                return $service;
            }

            log_message('info', 'Iniciando generación de PDF para servicio: ' . $serviceId);

            // Generar PDF usando PDFReportGenerator
            $pdfGenerator = new \App\Libraries\PDFReportGenerator();
            $dompdf = $pdfGenerator->generateCompleteReport($serviceId);

            log_message('info', 'PDF generado, obteniendo output');

            // DEBUGGING: Guardar HTML generado
            $htmlContent = $pdfGenerator->getGeneratedHTML();
            file_put_contents(WRITEPATH . 'logs/pdf_html_debug.html', $htmlContent);
            log_message('info', 'HTML guardado en logs/pdf_html_debug.html, tamaño: ' . strlen($htmlContent) . ' bytes');

            // Obtener el output del PDF
            $output = $dompdf->output();

            log_message('info', 'Output obtenido, tamaño: ' . strlen($output) . ' bytes');
            log_message('info', 'Primeros 100 caracteres: ' . substr($output, 0, 100));

            // Limpiar cualquier output buffer previo
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            // Configurar nombre del archivo - IMPORTANTE: mantener .pdf al final
            $baseFileName = 'Informe_Completo_' . $service['service_name'] . '_' . date('Y-m-d');
            $baseFileName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $baseFileName);
            $fileName = $baseFileName . '.pdf';

            log_message('info', 'Enviando respuesta PDF al navegador, nombre: ' . $fileName);

            // Enviar PDF directamente sin usar Response de CodeIgniter
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $fileName . '"');
            header('Content-Transfer-Encoding: binary');
            header('Content-Length: ' . strlen($output));
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');
            echo $output;
            exit();

        } catch (\Exception $e) {
            // Log del error
            log_message('error', 'Error generando PDF: ' . $e->getMessage());
            log_message('error', 'Trace: ' . $e->getTraceAsString());

            // Retornar error en formato HTML para debugging
            return $this->response
                ->setHeader('Content-Type', 'text/html; charset=utf-8')
                ->setBody(
                    '<h1>Error al generar PDF</h1>' .
                    '<p><strong>Mensaje:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>' .
                    '<p><strong>Archivo:</strong> ' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . '</p>' .
                    '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>'
                );
        }
    }

    /**
     * Exportar PDF ejecutivo
     */
    public function exportExecutivePDF($serviceId, $type)
    {
        log_message('info', '[EXPORT-EXEC-PDF] Iniciando exportación ejecutiva para service_id: ' . $serviceId . ', type: ' . $type);

        try {
            // Verificar acceso
            log_message('info', '[EXPORT-EXEC-PDF] Verificando acceso');
            $service = $this->checkAccess($serviceId);
            if ($service instanceof \CodeIgniter\HTTP\RedirectResponse) {
                log_message('warning', '[EXPORT-EXEC-PDF] Acceso denegado, redirigiendo');
                return $service;
            }

            log_message('info', '[EXPORT-EXEC-PDF] Acceso permitido, servicio: ' . $service['service_name']);

            // Obtener resultados según el tipo (con JOIN para name y document)
            log_message('info', '[EXPORT-EXEC-PDF] Obteniendo resultados');
            $results = $this->calculatedResultModel
                ->select('calculated_results.*, workers.name, workers.document')
                ->join('workers', 'workers.id = calculated_results.worker_id')
                ->where('calculated_results.battery_service_id', $serviceId)
                ->findAll();

            log_message('info', '[EXPORT-EXEC-PDF] Resultados obtenidos: ' . count($results));

            if (empty($results)) {
                log_message('error', '[EXPORT-EXEC-PDF] No hay resultados disponibles');
                return redirect()->back()->with('error', 'No hay resultados disponibles para generar el informe');
            }

            // Calcular estadísticas según el tipo de reporte
            log_message('info', '[EXPORT-EXEC-PDF] Calculando estadísticas para tipo: ' . $type);

            // Filtrar resultados de riesgo según el tipo
            if ($type === 'intralaboral') {
                $riskResults = array_filter($results, function($r) {
                    return in_array($r['intralaboral_total_nivel'], ['riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto']);
                });
            } elseif ($type === 'extralaboral') {
                $riskResults = array_filter($results, function($r) {
                    return in_array($r['extralaboral_total_nivel'], ['riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto']);
                });
            } else { // estres
                $riskResults = array_filter($results, function($r) {
                    return in_array($r['estres_total_nivel'], ['muy_alto', 'alto', 'medio']);
                });
            }

            // Calcular totales
            $totalIntralaboral = 0;
            $totalExtralaboral = 0;
            $totalEstres = 0;

            foreach ($results as $result) {
                $totalIntralaboral += $result['intralaboral_total_puntaje'] ?? 0;
                $totalExtralaboral += $result['extralaboral_total_puntaje'] ?? 0;
                $totalEstres += $result['estres_total_puntaje'] ?? 0;
            }

            $count = count($results);
            $totales = [
                'participantes' => $count,
                'promedio_intralaboral' => $count > 0 ? round($totalIntralaboral / $count, 2) : 0,
                'promedio_extralaboral' => $count > 0 ? round($totalExtralaboral / $count, 2) : 0,
                'promedio_estres' => $count > 0 ? round($totalEstres / $count, 2) : 0
            ];

            // Preparar datos para la vista
            $data = [
                'title' => ucfirst($type) . ' - Informe Ejecutivo - ' . $service['service_name'],
                'service' => $service,
                'totales' => $totales,
                'riskResults' => $riskResults,
                'totalRisk' => count($riskResults)
            ];

            log_message('info', '[EXPORT-EXEC-PDF] Renderizando vista HTML para PDF');
            // Renderizar la vista HTML específica para PDF (mejor diseño)
            $html = view('reports/' . $type . '/executive_pdf', $data);
            log_message('info', '[EXPORT-EXEC-PDF] HTML generado, tamaño: ' . strlen($html) . ' bytes');

            // Generar PDF con Dompdf
            log_message('info', '[EXPORT-EXEC-PDF] Generando PDF con Dompdf');
            $options = new \Dompdf\Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $options->set('defaultFont', 'Arial');

            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('Letter', 'portrait');
            $dompdf->render();

            // Obtener output
            $output = $dompdf->output();
            log_message('info', '[EXPORT-EXEC-PDF] PDF generado, tamaño: ' . strlen($output) . ' bytes');

            // Limpiar output buffer
            log_message('info', '[EXPORT-EXEC-PDF] Limpiando output buffers');
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            // Configurar nombre del archivo
            $baseFileName = 'Informe_Ejecutivo_' . ucfirst($type) . '_' . $service['service_name'] . '_' . date('Y-m-d');
            $baseFileName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $baseFileName);
            $fileName = $baseFileName . '.pdf';

            log_message('info', '[EXPORT-EXEC-PDF] Enviando PDF: ' . $fileName);

            // Enviar PDF
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $fileName . '"');
            header('Content-Transfer-Encoding: binary');
            header('Content-Length: ' . strlen($output));
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');
            echo $output;

            log_message('info', '[EXPORT-EXEC-PDF] PDF enviado exitosamente');
            exit();

        } catch (\Exception $e) {
            log_message('error', '[EXPORT-EXEC-PDF] ERROR: ' . $e->getMessage());
            log_message('error', '[EXPORT-EXEC-PDF] ERROR FILE: ' . $e->getFile() . ':' . $e->getLine());
            log_message('error', '[EXPORT-EXEC-PDF] ERROR TRACE: ' . $e->getTraceAsString());
            return redirect()->back()->with('error', 'Error al generar PDF: ' . $e->getMessage());
        }
    }

    /**
     * Vista dedicada al Mapa de Calor con información de cálculos detallados
     */
    public function heatmap($serviceId)
    {
        // Verificar acceso
        $service = $this->checkAccess($serviceId);
        if ($service instanceof \CodeIgniter\HTTP\RedirectResponse || $service instanceof \CodeIgniter\View\View || is_string($service)) {
            return $service;
        }

        // Obtener todos los resultados calculados
        $results = $this->calculatedResultModel
            ->select('calculated_results.*, workers.name, workers.document')
            ->join('workers', 'workers.id = calculated_results.worker_id')
            ->where('calculated_results.battery_service_id', $serviceId)
            ->findAll();

        if (empty($results)) {
            return redirect()->back()->with('error', 'No hay resultados calculados para este servicio');
        }

        // Calcular heatmap con datos detallados
        $batteryServiceController = new \App\Controllers\BatteryServiceController();
        $heatmapCalculations = $this->calculateHeatmapWithDetails($results);

        $data = [
            'title' => 'Mapa de Calor - Riesgo Psicosocial Global',
            'service' => $service,
            'results' => $results,
            'totalWorkers' => count($results),
            'heatmapCalculations' => $heatmapCalculations,
        ];

        return view('reports/heatmap_detail', $data);
    }

    /**
     * Método público para calcular heatmap - usado por PdfEjecutivo
     * Garantiza que PDF y vista web usen exactamente los mismos cálculos
     */
    public function calculateHeatmapForPdf($results)
    {
        return $this->calculateHeatmapWithDetails($results);
    }

    /**
     * Calcular heatmap con información detallada de baremos y promedios
     * MAPA DE CALOR DE MÁXIMO RIESGO: Muestra el peor resultado entre Forma A y B
     */
    private function calculateHeatmapWithDetails($results)
    {
        if (empty($results)) {
            return null;
        }

        // Separar resultados por forma
        $resultsA = array_filter($results, fn($r) => $r['intralaboral_form_type'] === 'A');
        $resultsB = array_filter($results, fn($r) => $r['intralaboral_form_type'] === 'B');

        $countA = count($resultsA);
        $countB = count($resultsB);

        // Determinar qué formas están presentes
        $hasFormaA = $countA > 0;
        $hasFormaB = $countB > 0;
        $hasBothForms = $hasFormaA && $hasFormaB;

        // Para compatibilidad, mantener forma_type como la predominante
        $formaType = ($countA >= $countB) ? 'A' : 'B';

        // BAREMOS para cada forma - Desde fuente única autorizada
        $baremosA = [
            'intralaboral_total' => IntralaboralAScoring::getBaremoTotal(),
            'dominios' => [
                'liderazgo' => IntralaboralAScoring::getBaremoDominio('liderazgo_relaciones_sociales'),
                'control' => IntralaboralAScoring::getBaremoDominio('control'),
                'demandas' => IntralaboralAScoring::getBaremoDominio('demandas'),
                'recompensas' => IntralaboralAScoring::getBaremoDominio('recompensas'),
            ],
            'extralaboral_total' => ExtralaboralScoring::getBaremoTotal('A'),
            'estres_total' => EstresScoring::getBaremoA(),
        ];

        $baremosB = [
            'intralaboral_total' => IntralaboralBScoring::getBaremoTotal(),
            'dominios' => [
                'liderazgo' => IntralaboralBScoring::getBaremoDominio('liderazgo_relaciones_sociales'),
                'control' => IntralaboralBScoring::getBaremoDominio('control'),
                'demandas' => IntralaboralBScoring::getBaremoDominio('demandas'),
                'recompensas' => IntralaboralBScoring::getBaremoDominio('recompensas'),
            ],
            'extralaboral_total' => ExtralaboralScoring::getBaremoTotal('B'),
            'estres_total' => EstresScoring::getBaremoB(),
        ];

        // Mapeo de dimensiones intralaborales
        $mapDimensionesIntra = [
            'caracteristicas_liderazgo' => 'caracteristicas_liderazgo',
            'relaciones_sociales' => 'relaciones_sociales_trabajo',
            'retroalimentacion' => 'retroalimentacion_desempeno',
            'relacion_colaboradores' => 'relacion_con_colaboradores',
            'claridad_rol' => 'claridad_rol',
            'capacitacion' => 'capacitacion',
            'participacion_cambio' => 'participacion_manejo_cambio',
            'oportunidades_desarrollo' => 'oportunidades_desarrollo',
            'control_autonomia' => 'control_autonomia_trabajo',
            'demandas_ambientales' => 'demandas_ambientales_esfuerzo_fisico',
            'demandas_emocionales' => 'demandas_emocionales',
            'demandas_cuantitativas' => 'demandas_cuantitativas',
            'influencia_entorno' => 'influencia_trabajo_entorno_extralaboral',
            'exigencias_responsabilidad' => 'exigencias_responsabilidad_cargo',
            'carga_mental' => 'demandas_carga_mental',
            'consistencia_rol' => 'consistencia_rol',
            'demandas_jornada' => 'demandas_jornada_trabajo',
            'recompensas_pertenencia' => 'recompensas_pertenencia_estabilidad',
            'reconocimiento_compensacion' => 'reconocimiento_compensacion',
        ];

        // Cargar baremos de dimensiones para cada forma
        $baremosA['dimensiones_intra'] = [];
        $baremosB['dimensiones_intra'] = [];
        foreach ($mapDimensionesIntra as $codigoCorto => $codigoLibreria) {
            $baremoA = IntralaboralAScoring::getBaremoDimension($codigoLibreria);
            $baremoB = IntralaboralBScoring::getBaremoDimension($codigoLibreria);
            if ($baremoA !== null) $baremosA['dimensiones_intra'][$codigoCorto] = $baremoA;
            if ($baremoB !== null) $baremosB['dimensiones_intra'][$codigoCorto] = $baremoB;
        }

        // Baremos de dimensiones extralaborales (igual para ambas formas)
        $mapDimensionesExtra = [
            'tiempo_fuera' => 'tiempo_fuera_trabajo',
            'relaciones_familiares' => 'relaciones_familiares',
            'comunicacion' => 'comunicacion_relaciones',
            'situacion_economica' => 'situacion_economica',
            'caracteristicas_vivienda' => 'caracteristicas_vivienda',
            'influencia_entorno_extra' => 'influencia_entorno',
            'desplazamiento' => 'desplazamiento',
        ];
        $baremoDimensionesExtra = [];
        foreach ($mapDimensionesExtra as $codigoCorto => $codigoLibreria) {
            $baremo = ExtralaboralScoring::getBaremoDimension($codigoLibreria);
            if ($baremo !== null) {
                $baremoDimensionesExtra[$codigoCorto] = $baremo;
            }
        }

        // Orden de niveles de riesgo (mayor = peor)
        $riskOrder = [
            'sin_riesgo' => 0,
            'riesgo_bajo' => 1,
            'riesgo_medio' => 2,
            'riesgo_alto' => 3,
            'riesgo_muy_alto' => 4,
            // Alias para estrés
            'muy_bajo' => 0,
            'bajo' => 1,
            'medio' => 2,
            'alto' => 3,
            'muy_alto' => 4,
        ];

        // Función helper para calcular detalle de UNA forma
        $calculateForma = function($field, $baremo, $resultados) use ($riskOrder) {
            $puntajes = array_filter(array_column($resultados, $field), function($v) {
                return $v !== null && $v !== '';
            });

            if (empty($puntajes)) {
                return null; // No hay datos para esta forma
            }

            $promedio = array_sum($puntajes) / count($puntajes);
            $nivel = 'sin_riesgo';

            foreach ($baremo as $nivelKey => $rango) {
                if ($promedio >= $rango[0] && $promedio <= $rango[1]) {
                    $nivel = $nivelKey;
                    break;
                }
            }

            return [
                'promedio' => round($promedio, 2),
                'nivel' => $nivel,
                'nivel_order' => $riskOrder[$nivel] ?? 0,
                'cantidad' => count($puntajes),
                'suma' => array_sum($puntajes),
                'baremo' => $baremo,
                'rango_aplicado' => $baremo[$nivel] ?? [0, 0]
            ];
        };

        // Función para obtener el PEOR resultado entre Forma A y B
        $getWorstResult = function($field, $baremoA, $baremoB) use ($calculateForma, $resultsA, $resultsB, $hasFormaA, $hasFormaB, $hasBothForms) {
            $dataA = $hasFormaA ? $calculateForma($field, $baremoA, $resultsA) : null;
            $dataB = $hasFormaB ? $calculateForma($field, $baremoB, $resultsB) : null;

            // Si solo hay una forma, devolver esa
            if (!$hasBothForms) {
                $data = $dataA ?? $dataB;
                if ($data === null) {
                    return [
                        'promedio' => 0,
                        'nivel' => 'sin_riesgo',
                        'cantidad' => 0,
                        'forma_origen' => null,
                        'solo_una_forma' => true,
                        'data_a' => null,
                        'data_b' => null,
                    ];
                }
                $forma = $dataA ? 'A' : 'B';
                return array_merge($data, [
                    'forma_origen' => $forma,
                    'solo_una_forma' => true,
                    'data_a' => $dataA,
                    'data_b' => $dataB,
                ]);
            }

            // Hay ambas formas: determinar cuál es peor
            $orderA = $dataA['nivel_order'] ?? 0;
            $orderB = $dataB['nivel_order'] ?? 0;

            // Si empatan en nivel, el peor es el de mayor puntaje
            if ($orderA === $orderB) {
                $worst = ($dataA['promedio'] ?? 0) >= ($dataB['promedio'] ?? 0) ? $dataA : $dataB;
                $formaOrigen = ($dataA['promedio'] ?? 0) >= ($dataB['promedio'] ?? 0) ? 'A' : 'B';
            } else {
                $worst = $orderA > $orderB ? $dataA : $dataB;
                $formaOrigen = $orderA > $orderB ? 'A' : 'B';
            }

            return array_merge($worst, [
                'forma_origen' => $formaOrigen,
                'solo_una_forma' => false,
                'data_a' => $dataA,
                'data_b' => $dataB,
            ]);
        };

        // Calcular todos los detalles usando PEOR RESULTADO entre formas
        $calculations = [
            'forma_type' => $formaType,
            'has_forma_a' => $hasFormaA,
            'has_forma_b' => $hasFormaB,
            'has_both_forms' => $hasBothForms,
            'count_a' => $countA,
            'count_b' => $countB,

            // Totales - PEOR entre A y B
            'intralaboral_total' => $getWorstResult('intralaboral_total_puntaje', $baremosA['intralaboral_total'], $baremosB['intralaboral_total']),

            // Dominios - PEOR entre A y B
            'dom_liderazgo' => $getWorstResult('dom_liderazgo_puntaje', $baremosA['dominios']['liderazgo'], $baremosB['dominios']['liderazgo']),
            'dom_control' => $getWorstResult('dom_control_puntaje', $baremosA['dominios']['control'], $baremosB['dominios']['control']),
            'dom_demandas' => $getWorstResult('dom_demandas_puntaje', $baremosA['dominios']['demandas'], $baremosB['dominios']['demandas']),
            'dom_recompensas' => $getWorstResult('dom_recompensas_puntaje', $baremosA['dominios']['recompensas'], $baremosB['dominios']['recompensas']),

            // Extralaboral y Estrés - PEOR entre A y B
            'extralaboral_total' => $getWorstResult('extralaboral_total_puntaje', $baremosA['extralaboral_total'], $baremosB['extralaboral_total']),
            'estres_total' => $getWorstResult('estres_total_puntaje', $baremosA['estres_total'], $baremosB['estres_total']),

            // Dimensiones intralaborales - PEOR entre A y B
            'dim_caracteristicas_liderazgo' => $getWorstResult('dim_caracteristicas_liderazgo_puntaje',
                $baremosA['dimensiones_intra']['caracteristicas_liderazgo'] ?? [],
                $baremosB['dimensiones_intra']['caracteristicas_liderazgo'] ?? []),
            'dim_relaciones_sociales' => $getWorstResult('dim_relaciones_sociales_puntaje',
                $baremosA['dimensiones_intra']['relaciones_sociales'] ?? [],
                $baremosB['dimensiones_intra']['relaciones_sociales'] ?? []),
            'dim_retroalimentacion' => $getWorstResult('dim_retroalimentacion_puntaje',
                $baremosA['dimensiones_intra']['retroalimentacion'] ?? [],
                $baremosB['dimensiones_intra']['retroalimentacion'] ?? []),
            'dim_relacion_colaboradores' => isset($baremosA['dimensiones_intra']['relacion_colaboradores'])
                ? $getWorstResult('dim_relacion_colaboradores_puntaje',
                    $baremosA['dimensiones_intra']['relacion_colaboradores'] ?? [],
                    $baremosB['dimensiones_intra']['relacion_colaboradores'] ?? [])
                : null,
            'dim_claridad_rol' => $getWorstResult('dim_claridad_rol_puntaje',
                $baremosA['dimensiones_intra']['claridad_rol'] ?? [],
                $baremosB['dimensiones_intra']['claridad_rol'] ?? []),
            'dim_capacitacion' => $getWorstResult('dim_capacitacion_puntaje',
                $baremosA['dimensiones_intra']['capacitacion'] ?? [],
                $baremosB['dimensiones_intra']['capacitacion'] ?? []),
            'dim_participacion_manejo_cambio' => $getWorstResult('dim_participacion_manejo_cambio_puntaje',
                $baremosA['dimensiones_intra']['participacion_cambio'] ?? [],
                $baremosB['dimensiones_intra']['participacion_cambio'] ?? []),
            'dim_oportunidades_desarrollo' => $getWorstResult('dim_oportunidades_desarrollo_puntaje',
                $baremosA['dimensiones_intra']['oportunidades_desarrollo'] ?? [],
                $baremosB['dimensiones_intra']['oportunidades_desarrollo'] ?? []),
            'dim_control_autonomia' => $getWorstResult('dim_control_autonomia_puntaje',
                $baremosA['dimensiones_intra']['control_autonomia'] ?? [],
                $baremosB['dimensiones_intra']['control_autonomia'] ?? []),
            'dim_demandas_ambientales' => $getWorstResult('dim_demandas_ambientales_puntaje',
                $baremosA['dimensiones_intra']['demandas_ambientales'] ?? [],
                $baremosB['dimensiones_intra']['demandas_ambientales'] ?? []),
            'dim_demandas_emocionales' => $getWorstResult('dim_demandas_emocionales_puntaje',
                $baremosA['dimensiones_intra']['demandas_emocionales'] ?? [],
                $baremosB['dimensiones_intra']['demandas_emocionales'] ?? []),
            'dim_demandas_cuantitativas' => $getWorstResult('dim_demandas_cuantitativas_puntaje',
                $baremosA['dimensiones_intra']['demandas_cuantitativas'] ?? [],
                $baremosB['dimensiones_intra']['demandas_cuantitativas'] ?? []),
            'dim_influencia_trabajo_entorno_extralaboral' => $getWorstResult('dim_influencia_trabajo_entorno_extralaboral_puntaje',
                $baremosA['dimensiones_intra']['influencia_entorno'] ?? [],
                $baremosB['dimensiones_intra']['influencia_entorno'] ?? []),
            'dim_demandas_responsabilidad' => isset($baremosA['dimensiones_intra']['exigencias_responsabilidad'])
                ? $getWorstResult('dim_demandas_responsabilidad_puntaje',
                    $baremosA['dimensiones_intra']['exigencias_responsabilidad'] ?? [],
                    $baremosB['dimensiones_intra']['exigencias_responsabilidad'] ?? [])
                : null,
            'dim_carga_mental' => $getWorstResult('dim_demandas_carga_mental_puntaje',
                $baremosA['dimensiones_intra']['carga_mental'] ?? [],
                $baremosB['dimensiones_intra']['carga_mental'] ?? []),
            'dim_consistencia_rol' => isset($baremosA['dimensiones_intra']['consistencia_rol'])
                ? $getWorstResult('dim_consistencia_rol_puntaje',
                    $baremosA['dimensiones_intra']['consistencia_rol'] ?? [],
                    $baremosB['dimensiones_intra']['consistencia_rol'] ?? [])
                : null,
            'dim_demandas_jornada_trabajo' => $getWorstResult('dim_demandas_jornada_trabajo_puntaje',
                $baremosA['dimensiones_intra']['demandas_jornada'] ?? [],
                $baremosB['dimensiones_intra']['demandas_jornada'] ?? []),
            'dim_recompensas_pertenencia' => $getWorstResult('dim_recompensas_pertenencia_puntaje',
                $baremosA['dimensiones_intra']['recompensas_pertenencia'] ?? [],
                $baremosB['dimensiones_intra']['recompensas_pertenencia'] ?? []),
            'dim_reconocimiento_compensacion' => $getWorstResult('dim_reconocimiento_compensacion_puntaje',
                $baremosA['dimensiones_intra']['reconocimiento_compensacion'] ?? [],
                $baremosB['dimensiones_intra']['reconocimiento_compensacion'] ?? []),

            // Dimensiones extralaborales - mismo baremo para ambas formas
            'dim_tiempo_fuera' => $getWorstResult('extralaboral_tiempo_fuera_puntaje',
                $baremoDimensionesExtra['tiempo_fuera'], $baremoDimensionesExtra['tiempo_fuera']),
            'dim_relaciones_familiares_extra' => $getWorstResult('extralaboral_relaciones_familiares_puntaje',
                $baremoDimensionesExtra['relaciones_familiares'], $baremoDimensionesExtra['relaciones_familiares']),
            'dim_comunicacion' => $getWorstResult('extralaboral_comunicacion_puntaje',
                $baremoDimensionesExtra['comunicacion'], $baremoDimensionesExtra['comunicacion']),
            'dim_situacion_economica' => $getWorstResult('extralaboral_situacion_economica_puntaje',
                $baremoDimensionesExtra['situacion_economica'], $baremoDimensionesExtra['situacion_economica']),
            'dim_caracteristicas_vivienda' => $getWorstResult('extralaboral_caracteristicas_vivienda_puntaje',
                $baremoDimensionesExtra['caracteristicas_vivienda'], $baremoDimensionesExtra['caracteristicas_vivienda']),
            'dim_influencia_entorno_extra' => $getWorstResult('extralaboral_influencia_entorno_puntaje',
                $baremoDimensionesExtra['influencia_entorno_extra'], $baremoDimensionesExtra['influencia_entorno_extra']),
            'dim_desplazamiento' => $getWorstResult('extralaboral_desplazamiento_puntaje',
                $baremoDimensionesExtra['desplazamiento'], $baremoDimensionesExtra['desplazamiento']),
        ];

        return $calculations;
    }

    /**
     * Mapa de Calor Detallado - Intralaboral Forma A
     * Muestra Total + 4 Dominios + 19 Dimensiones
     */
    public function intralaboralFormaA($serviceId)
    {
        // Verificar acceso
        $service = $this->checkAccess($serviceId);
        if ($service instanceof \CodeIgniter\HTTP\RedirectResponse ||
            $service instanceof \CodeIgniter\View\View ||
            is_string($service)) {
            return $service;
        }

        // Obtener SOLO resultados de Forma A
        $results = $this->calculatedResultModel
            ->select('calculated_results.*, workers.name, workers.document')
            ->join('workers', 'workers.id = calculated_results.worker_id')
            ->where('calculated_results.battery_service_id', $serviceId)
            ->where('calculated_results.intralaboral_form_type', 'A')
            ->findAll();

        if (empty($results)) {
            // No hay datos, mostrar vista informativa
            $data = [
                'title' => 'Sin Datos - Intralaboral Forma A',
                'service' => $service,
                'formType' => 'Cuestionario Intralaboral Forma A',
            ];
            return view('reports/intralaboral/no_data', $data);
        }

        // Calcular datos detallados para Forma A
        $calculations = $this->calculateIntralaboralFormaADetails($results);

        $data = [
            'title' => 'Mapa de Calor Intralaboral - Forma A',
            'service' => $service,
            'results' => $results,
            'totalWorkers' => count($results),
            'calculations' => $calculations,
        ];

        return view('reports/intralaboral/detail_forma_a', $data);
    }

    /**
     * Mapa de Calor Detallado - Intralaboral Forma B
     * Muestra Total + 4 Dominios + 16 Dimensiones
     */
    public function intralaboralFormaB($serviceId)
    {
        // Verificar acceso
        $service = $this->checkAccess($serviceId);
        if ($service instanceof \CodeIgniter\HTTP\RedirectResponse ||
            $service instanceof \CodeIgniter\View\View ||
            is_string($service)) {
            return $service;
        }

        // Obtener SOLO resultados de Forma B
        $results = $this->calculatedResultModel
            ->select('calculated_results.*, workers.name, workers.document')
            ->join('workers', 'workers.id = calculated_results.worker_id')
            ->where('calculated_results.battery_service_id', $serviceId)
            ->where('calculated_results.intralaboral_form_type', 'B')
            ->findAll();

        if (empty($results)) {
            // No hay datos, mostrar vista informativa
            $data = [
                'title' => 'Sin Datos - Intralaboral Forma B',
                'service' => $service,
                'formType' => 'Cuestionario Intralaboral Forma B',
            ];
            return view('reports/intralaboral/no_data', $data);
        }

        // Calcular datos detallados para Forma B
        $calculations = $this->calculateIntralaboralFormaBDetails($results);

        $data = [
            'title' => 'Mapa de Calor Intralaboral - Forma B',
            'service' => $service,
            'results' => $results,
            'totalWorkers' => count($results),
            'calculations' => $calculations,
        ];

        return view('reports/intralaboral/detail_forma_b', $data);
    }

    /**
     * Calcular detalles completos de Intralaboral Forma A
     * Incluye: 1 Total + 4 Dominios + 19 Dimensiones
     * BAREMOS: Desde fuente única autorizada (README_BAREMOS.md)
     */
    private function calculateIntralaboralFormaADetails($results)
    {
        if (empty($results)) {
            return null;
        }

        // BAREMOS - Desde fuente única autorizada (IntralaboralAScoring)
        $baremoIntralaboralTotal = IntralaboralAScoring::getBaremoTotal();

        // Dominios Forma A (Tabla 31)
        $baremoDominios = [
            'liderazgo' => IntralaboralAScoring::getBaremoDominio('liderazgo_relaciones_sociales'),
            'control' => IntralaboralAScoring::getBaremoDominio('control'),
            'demandas' => IntralaboralAScoring::getBaremoDominio('demandas'),
            'recompensas' => IntralaboralAScoring::getBaremoDominio('recompensas'),
        ];

        // Dimensiones Forma A (Tabla 29) - Nombres exactos de IntralaboralAScoring
        $baremoDimensiones = [
            'dim_caracteristicas_liderazgo' => IntralaboralAScoring::getBaremoDimension('caracteristicas_liderazgo'),
            'dim_relaciones_sociales' => IntralaboralAScoring::getBaremoDimension('relaciones_sociales_trabajo'),
            'dim_retroalimentacion' => IntralaboralAScoring::getBaremoDimension('retroalimentacion_desempeno'),
            'dim_relacion_colaboradores' => IntralaboralAScoring::getBaremoDimension('relacion_con_colaboradores'),
            'dim_claridad_rol' => IntralaboralAScoring::getBaremoDimension('claridad_rol'),
            'dim_capacitacion' => IntralaboralAScoring::getBaremoDimension('capacitacion'),
            'dim_participacion_cambio' => IntralaboralAScoring::getBaremoDimension('participacion_manejo_cambio'),
            'dim_oportunidades_desarrollo' => IntralaboralAScoring::getBaremoDimension('oportunidades_desarrollo'),
            'dim_control_autonomia' => IntralaboralAScoring::getBaremoDimension('control_autonomia_trabajo'),
            // Dominio 3: Demandas (8 dimensiones)
            'dim_demandas_ambientales' => IntralaboralAScoring::getBaremoDimension('demandas_ambientales_esfuerzo_fisico'),
            'dim_demandas_emocionales' => IntralaboralAScoring::getBaremoDimension('demandas_emocionales'),
            'dim_demandas_cuantitativas' => IntralaboralAScoring::getBaremoDimension('demandas_cuantitativas'),
            'dim_influencia_entorno' => IntralaboralAScoring::getBaremoDimension('influencia_trabajo_entorno_extralaboral'),
            'dim_exigencias_responsabilidad' => IntralaboralAScoring::getBaremoDimension('exigencias_responsabilidad_cargo'),
            'dim_demandas_carga_mental' => IntralaboralAScoring::getBaremoDimension('demandas_carga_mental'),
            'dim_consistencia_rol' => IntralaboralAScoring::getBaremoDimension('consistencia_rol'),
            'dim_demandas_jornada' => IntralaboralAScoring::getBaremoDimension('demandas_jornada_trabajo'),
            // Dominio 4: Recompensas (2 dimensiones)
            'dim_recompensas_pertenencia' => IntralaboralAScoring::getBaremoDimension('recompensas_pertenencia_estabilidad'),
            'dim_reconocimiento_compensacion' => IntralaboralAScoring::getBaremoDimension('reconocimiento_compensacion'),
        ];

        // Función helper para calcular detalle
        $calculateDetail = function($field, $baremo) use ($results) {
            $puntajes = array_filter(array_column($results, $field), function($v) {
                return $v !== null && $v !== '';
            });

            if (empty($puntajes)) {
                return [
                    'promedio' => 0,
                    'nivel' => 'sin_riesgo',
                    'cantidad' => 0,
                    'puntajes' => [],
                    'suma' => 0,
                    'baremo' => $baremo,
                    'rango_aplicado' => [0, 0]
                ];
            }

            $suma = array_sum($puntajes);
            $cantidad = count($puntajes);
            $promedio = $suma / $cantidad;

            $nivel = 'sin_riesgo';
            foreach ($baremo as $nivelKey => $rango) {
                if ($promedio >= $rango[0] && $promedio <= $rango[1]) {
                    $nivel = $nivelKey;
                    break;
                }
            }

            return [
                'promedio' => round($promedio, 2),
                'nivel' => $nivel,
                'cantidad' => $cantidad,
                'puntajes' => $puntajes,
                'suma' => round($suma, 2),
                'baremo' => $baremo,
                'rango_aplicado' => $baremo[$nivel] ?? [0, 0]
            ];
        };

        // Calcular todos los componentes
        return [
            // Total
            'intralaboral_total' => $calculateDetail('intralaboral_total_puntaje', $baremoIntralaboralTotal),

            // 4 Dominios
            'dom_liderazgo' => $calculateDetail('dom_liderazgo_puntaje', $baremoDominios['liderazgo']),
            'dom_control' => $calculateDetail('dom_control_puntaje', $baremoDominios['control']),
            'dom_demandas' => $calculateDetail('dom_demandas_puntaje', $baremoDominios['demandas']),
            'dom_recompensas' => $calculateDetail('dom_recompensas_puntaje', $baremoDominios['recompensas']),

            // 19 Dimensiones
            'dim_caracteristicas_liderazgo' => $calculateDetail('dim_caracteristicas_liderazgo_puntaje', $baremoDimensiones['dim_caracteristicas_liderazgo']),
            'dim_relaciones_sociales' => $calculateDetail('dim_relaciones_sociales_puntaje', $baremoDimensiones['dim_relaciones_sociales']),
            'dim_retroalimentacion' => $calculateDetail('dim_retroalimentacion_puntaje', $baremoDimensiones['dim_retroalimentacion']),
            'dim_relacion_colaboradores' => $calculateDetail('dim_relacion_colaboradores_puntaje', $baremoDimensiones['dim_relacion_colaboradores']),
            'dim_claridad_rol' => $calculateDetail('dim_claridad_rol_puntaje', $baremoDimensiones['dim_claridad_rol']),
            'dim_capacitacion' => $calculateDetail('dim_capacitacion_puntaje', $baremoDimensiones['dim_capacitacion']),
            'dim_participacion_manejo_cambio' => $calculateDetail('dim_participacion_manejo_cambio_puntaje', $baremoDimensiones['dim_participacion_cambio']),
            'dim_oportunidades_desarrollo' => $calculateDetail('dim_oportunidades_desarrollo_puntaje', $baremoDimensiones['dim_oportunidades_desarrollo']),
            'dim_control_autonomia' => $calculateDetail('dim_control_autonomia_puntaje', $baremoDimensiones['dim_control_autonomia']),
            'dim_demandas_ambientales' => $calculateDetail('dim_demandas_ambientales_puntaje', $baremoDimensiones['dim_demandas_ambientales']),
            'dim_demandas_emocionales' => $calculateDetail('dim_demandas_emocionales_puntaje', $baremoDimensiones['dim_demandas_emocionales']),
            'dim_demandas_cuantitativas' => $calculateDetail('dim_demandas_cuantitativas_puntaje', $baremoDimensiones['dim_demandas_cuantitativas']),
            'dim_influencia_trabajo_entorno_extralaboral' => $calculateDetail('dim_influencia_trabajo_entorno_extralaboral_puntaje', $baremoDimensiones['dim_influencia_entorno']),
            'dim_demandas_responsabilidad' => $calculateDetail('dim_demandas_responsabilidad_puntaje', $baremoDimensiones['dim_exigencias_responsabilidad']),
            'dim_demandas_carga_mental' => $calculateDetail('dim_demandas_carga_mental_puntaje', $baremoDimensiones['dim_demandas_carga_mental']),
            'dim_consistencia_rol' => $calculateDetail('dim_consistencia_rol_puntaje', $baremoDimensiones['dim_consistencia_rol']),
            'dim_demandas_jornada_trabajo' => $calculateDetail('dim_demandas_jornada_trabajo_puntaje', $baremoDimensiones['dim_demandas_jornada']),
            'dim_recompensas_pertenencia' => $calculateDetail('dim_recompensas_pertenencia_puntaje', $baremoDimensiones['dim_recompensas_pertenencia']),
            'dim_reconocimiento_compensacion' => $calculateDetail('dim_reconocimiento_compensacion_puntaje', $baremoDimensiones['dim_reconocimiento_compensacion'])
        ];
    }

    /**
     * Calcular detalles completos de Intralaboral Forma B
     * Incluye: 1 Total + 4 Dominios + 16 Dimensiones (sin 3.5, 3.6, 3.7)
     */
    private function calculateIntralaboralFormaBDetails($results)
    {
        if (empty($results)) {
            return null;
        }

        // BAREMOS - Desde fuente única autorizada (IntralaboralBScoring)
        $baremoIntralaboralTotal = IntralaboralBScoring::getBaremoTotal();

        // Dominios Forma B (Tabla 32)
        $baremoDominios = [
            'liderazgo' => IntralaboralBScoring::getBaremoDominio('liderazgo_relaciones_sociales'),
            'control' => IntralaboralBScoring::getBaremoDominio('control'),
            'demandas' => IntralaboralBScoring::getBaremoDominio('demandas'),
            'recompensas' => IntralaboralBScoring::getBaremoDominio('recompensas'),
        ];

        // Dimensiones Forma B (Tabla 30 - 16 dimensiones) - Nombres exactos de IntralaboralBScoring
        $baremoDimensiones = [
            // Dominio 1: Liderazgo (3 dimensiones - SIN relacion_colaboradores)
            'dim_caracteristicas_liderazgo' => IntralaboralBScoring::getBaremoDimension('caracteristicas_liderazgo'),
            'dim_relaciones_sociales' => IntralaboralBScoring::getBaremoDimension('relaciones_sociales_trabajo'),
            'dim_retroalimentacion' => IntralaboralBScoring::getBaremoDimension('retroalimentacion_desempeno'),
            // Dominio 2: Control (5 dimensiones)
            'dim_claridad_rol' => IntralaboralBScoring::getBaremoDimension('claridad_rol'),
            'dim_capacitacion' => IntralaboralBScoring::getBaremoDimension('capacitacion'),
            'dim_participacion_cambio' => IntralaboralBScoring::getBaremoDimension('participacion_manejo_cambio'),
            'dim_oportunidades_desarrollo' => IntralaboralBScoring::getBaremoDimension('oportunidades_desarrollo'),
            'dim_control_autonomia' => IntralaboralBScoring::getBaremoDimension('control_autonomia_trabajo'),
            // Dominio 3: Demandas (5 dimensiones - SIN exigencias_responsabilidad, consistencia_rol)
            'dim_demandas_ambientales' => IntralaboralBScoring::getBaremoDimension('demandas_ambientales_esfuerzo_fisico'),
            'dim_demandas_emocionales' => IntralaboralBScoring::getBaremoDimension('demandas_emocionales'),
            'dim_demandas_cuantitativas' => IntralaboralBScoring::getBaremoDimension('demandas_cuantitativas'),
            'dim_influencia_entorno' => IntralaboralBScoring::getBaremoDimension('influencia_trabajo_entorno_extralaboral'),
            'dim_demandas_carga_mental' => IntralaboralBScoring::getBaremoDimension('demandas_carga_mental'),
            'dim_demandas_jornada' => IntralaboralBScoring::getBaremoDimension('demandas_jornada_trabajo'),
            // Dominio 4: Recompensas (2 dimensiones)
            'dim_recompensas_pertenencia' => IntralaboralBScoring::getBaremoDimension('recompensas_pertenencia_estabilidad'),
            'dim_reconocimiento_compensacion' => IntralaboralBScoring::getBaremoDimension('reconocimiento_compensacion'),
        ];

        // Función helper para calcular detalle
        $calculateDetail = function($field, $baremo) use ($results) {
            $puntajes = array_filter(array_column($results, $field), function($v) {
                return $v !== null && $v !== '';
            });

            if (empty($puntajes)) {
                return [
                    'promedio' => 0,
                    'nivel' => 'sin_riesgo',
                    'cantidad' => 0,
                    'puntajes' => [],
                    'suma' => 0,
                    'baremo' => $baremo,
                    'rango_aplicado' => [0, 0]
                ];
            }

            $suma = array_sum($puntajes);
            $cantidad = count($puntajes);
            $promedio = $suma / $cantidad;

            $nivel = 'sin_riesgo';
            foreach ($baremo as $nivelKey => $rango) {
                if ($promedio >= $rango[0] && $promedio <= $rango[1]) {
                    $nivel = $nivelKey;
                    break;
                }
            }

            return [
                'promedio' => round($promedio, 2),
                'nivel' => $nivel,
                'cantidad' => $cantidad,
                'puntajes' => $puntajes,
                'suma' => round($suma, 2),
                'baremo' => $baremo,
                'rango_aplicado' => $baremo[$nivel] ?? [0, 0]
            ];
        };

        // Calcular todos los componentes (16 dimensiones, SIN las 3 de Forma A)
        return [
            // Total
            'intralaboral_total' => $calculateDetail('intralaboral_total_puntaje', $baremoIntralaboralTotal),

            // 4 Dominios
            'dom_liderazgo' => $calculateDetail('dom_liderazgo_puntaje', $baremoDominios['liderazgo']),
            'dom_control' => $calculateDetail('dom_control_puntaje', $baremoDominios['control']),
            'dom_demandas' => $calculateDetail('dom_demandas_puntaje', $baremoDominios['demandas']),
            'dom_recompensas' => $calculateDetail('dom_recompensas_puntaje', $baremoDominios['recompensas']),

            // 16 Dimensiones (Forma B NO tiene: relacion_colaboradores, exigencias_responsabilidad, consistencia_rol)
            'dim_caracteristicas_liderazgo' => $calculateDetail('dim_caracteristicas_liderazgo_puntaje', $baremoDimensiones['dim_caracteristicas_liderazgo']),
            'dim_relaciones_sociales' => $calculateDetail('dim_relaciones_sociales_puntaje', $baremoDimensiones['dim_relaciones_sociales']),
            'dim_retroalimentacion' => $calculateDetail('dim_retroalimentacion_puntaje', $baremoDimensiones['dim_retroalimentacion']),
            // dim_relacion_colaboradores - Solo Forma A
            'dim_claridad_rol' => $calculateDetail('dim_claridad_rol_puntaje', $baremoDimensiones['dim_claridad_rol']),
            'dim_capacitacion' => $calculateDetail('dim_capacitacion_puntaje', $baremoDimensiones['dim_capacitacion']),
            'dim_participacion_cambio' => $calculateDetail('dim_participacion_manejo_cambio_puntaje', $baremoDimensiones['dim_participacion_cambio']),
            'dim_oportunidades_desarrollo' => $calculateDetail('dim_oportunidades_desarrollo_puntaje', $baremoDimensiones['dim_oportunidades_desarrollo']),
            'dim_control_autonomia' => $calculateDetail('dim_control_autonomia_puntaje', $baremoDimensiones['dim_control_autonomia']),
            'dim_demandas_ambientales' => $calculateDetail('dim_demandas_ambientales_puntaje', $baremoDimensiones['dim_demandas_ambientales']),
            'dim_demandas_emocionales' => $calculateDetail('dim_demandas_emocionales_puntaje', $baremoDimensiones['dim_demandas_emocionales']),
            'dim_demandas_cuantitativas' => $calculateDetail('dim_demandas_cuantitativas_puntaje', $baremoDimensiones['dim_demandas_cuantitativas']),
            'dim_influencia_entorno' => $calculateDetail('dim_influencia_trabajo_entorno_extralaboral_puntaje', $baremoDimensiones['dim_influencia_entorno']),
            // dim_exigencias_responsabilidad - Solo Forma A
            'dim_demandas_carga_mental' => $calculateDetail('dim_demandas_carga_mental_puntaje', $baremoDimensiones['dim_demandas_carga_mental']),
            // dim_consistencia_rol - Solo Forma A
            'dim_demandas_jornada' => $calculateDetail('dim_demandas_jornada_trabajo_puntaje', $baremoDimensiones['dim_demandas_jornada']),
            'dim_recompensas_pertenencia' => $calculateDetail('dim_recompensas_pertenencia_puntaje', $baremoDimensiones['dim_recompensas_pertenencia']),
            'dim_reconocimiento_compensacion' => $calculateDetail('dim_reconocimiento_compensacion_puntaje', $baremoDimensiones['dim_reconocimiento_compensacion'])
        ];
    }

    /**
     * Mapa de Calor Detallado - Extralaboral
     * Muestra Total + 7 Dimensiones (sin dominios)
     */
    public function extralaboralHeatmap($serviceId)
    {
        // Verificar acceso
        $service = $this->checkAccess($serviceId);
        if ($service instanceof \CodeIgniter\HTTP\RedirectResponse ||
            $service instanceof \CodeIgniter\View\View ||
            is_string($service)) {
            return $service;
        }

        // Obtener todos los resultados extralaborales
        $results = $this->calculatedResultModel
            ->select('calculated_results.*, workers.name, workers.document')
            ->join('workers', 'workers.id = calculated_results.worker_id')
            ->where('calculated_results.battery_service_id', $serviceId)
            ->where('calculated_results.extralaboral_total_puntaje IS NOT NULL')
            ->findAll();

        if (empty($results)) {
            // No hay datos, mostrar vista informativa
            $data = [
                'title' => 'Sin Datos - Extralaboral',
                'service' => $service,
                'formType' => 'Cuestionario de Factores de Riesgo Psicosocial Extralaboral',
            ];
            return view('reports/extralaboral/no_data', $data);
        }

        // Calcular datos detallados para Extralaboral
        $calculations = $this->calculateExtralaboralDetails($results);

        $data = [
            'title' => 'Mapa de Calor Extralaboral',
            'service' => $service,
            'results' => $results,
            'totalWorkers' => count($results),
            'calculations' => $calculations,
        ];

        return view('reports/extralaboral/detail', $data);
    }

    /**
     * Calcula detalles del cuestionario extralaboral
     * Retorna Total + 7 Dimensiones
     */
    private function calculateExtralaboralDetails($results)
    {
        // Baremo oficial para Total Extralaboral (Tabla 34 de la Resolución 2404/2019)
        $baremoExtralaboralTotal = [
            ['min' => 0.0, 'max' => 11.3, 'nivel' => 'sin_riesgo'],
            ['min' => 11.4, 'max' => 16.9, 'nivel' => 'riesgo_bajo'],
            ['min' => 17.0, 'max' => 22.6, 'nivel' => 'riesgo_medio'],
            ['min' => 22.7, 'max' => 29.0, 'nivel' => 'riesgo_alto'],
            ['min' => 29.1, 'max' => 100.0, 'nivel' => 'riesgo_muy_alto']
        ];

        // Baremos oficiales para las 7 Dimensiones (Tabla 32 de la Resolución 2404/2019)
        $baremoDimensiones = [
            'extralaboral_tiempo_fuera' => [
                ['min' => 0.0, 'max' => 8.3, 'nivel' => 'sin_riesgo'],
                ['min' => 8.4, 'max' => 25.0, 'nivel' => 'riesgo_bajo'],
                ['min' => 25.1, 'max' => 37.5, 'nivel' => 'riesgo_medio'],
                ['min' => 37.6, 'max' => 45.8, 'nivel' => 'riesgo_alto'],
                ['min' => 45.9, 'max' => 100.0, 'nivel' => 'riesgo_muy_alto']
            ],
            'extralaboral_relaciones_familiares' => [
                ['min' => 0.0, 'max' => 6.3, 'nivel' => 'sin_riesgo'],
                ['min' => 6.4, 'max' => 12.5, 'nivel' => 'riesgo_bajo'],
                ['min' => 12.6, 'max' => 25.0, 'nivel' => 'riesgo_medio'],
                ['min' => 25.1, 'max' => 37.5, 'nivel' => 'riesgo_alto'],
                ['min' => 37.6, 'max' => 100.0, 'nivel' => 'riesgo_muy_alto']
            ],
            'extralaboral_comunicacion' => [
                ['min' => 0.0, 'max' => 0.0, 'nivel' => 'sin_riesgo'],
                ['min' => 0.1, 'max' => 5.6, 'nivel' => 'riesgo_bajo'],
                ['min' => 5.7, 'max' => 11.1, 'nivel' => 'riesgo_medio'],
                ['min' => 11.2, 'max' => 22.2, 'nivel' => 'riesgo_alto'],
                ['min' => 22.3, 'max' => 100.0, 'nivel' => 'riesgo_muy_alto']
            ],
            'extralaboral_situacion_economica' => [
                ['min' => 0.0, 'max' => 8.3, 'nivel' => 'sin_riesgo'],
                ['min' => 8.4, 'max' => 25.0, 'nivel' => 'riesgo_bajo'],
                ['min' => 25.1, 'max' => 33.3, 'nivel' => 'riesgo_medio'],
                ['min' => 33.4, 'max' => 50.0, 'nivel' => 'riesgo_alto'],
                ['min' => 50.1, 'max' => 100.0, 'nivel' => 'riesgo_muy_alto']
            ],
            'extralaboral_caracteristicas_vivienda' => [
                ['min' => 0.0, 'max' => 0.0, 'nivel' => 'sin_riesgo'],
                ['min' => 0.1, 'max' => 5.6, 'nivel' => 'riesgo_bajo'],
                ['min' => 5.7, 'max' => 11.1, 'nivel' => 'riesgo_medio'],
                ['min' => 11.2, 'max' => 16.7, 'nivel' => 'riesgo_alto'],
                ['min' => 16.8, 'max' => 100.0, 'nivel' => 'riesgo_muy_alto']
            ],
            'extralaboral_influencia_entorno' => [
                ['min' => 0.0, 'max' => 15.6, 'nivel' => 'sin_riesgo'],
                ['min' => 15.7, 'max' => 28.1, 'nivel' => 'riesgo_bajo'],
                ['min' => 28.2, 'max' => 37.5, 'nivel' => 'riesgo_medio'],
                ['min' => 37.6, 'max' => 50.0, 'nivel' => 'riesgo_alto'],
                ['min' => 50.1, 'max' => 100.0, 'nivel' => 'riesgo_muy_alto']
            ],
            'extralaboral_desplazamiento' => [
                ['min' => 0.0, 'max' => 0.0, 'nivel' => 'sin_riesgo'],
                ['min' => 0.1, 'max' => 6.3, 'nivel' => 'riesgo_bajo'],
                ['min' => 6.4, 'max' => 12.5, 'nivel' => 'riesgo_medio'],
                ['min' => 12.6, 'max' => 25.0, 'nivel' => 'riesgo_alto'],
                ['min' => 25.1, 'max' => 100.0, 'nivel' => 'riesgo_muy_alto']
            ]
        ];

        // Función helper para calcular promedio y nivel
        $calculateDetail = function($field, $baremo) use ($results) {
            $values = array_filter(array_column($results, $field), function($v) { return $v !== null; });
            if (empty($values)) {
                return ['promedio' => 0, 'nivel' => 'sin_riesgo', 'total_trabajadores' => 0];
            }

            $promedio = array_sum($values) / count($values);
            $nivel = 'sin_riesgo';
            foreach ($baremo as $rango) {
                if ($promedio >= $rango['min'] && $promedio <= $rango['max']) {
                    $nivel = $rango['nivel'];
                    break;
                }
            }

            return [
                'promedio' => $promedio,
                'nivel' => $nivel,
                'total_trabajadores' => count($values)
            ];
        };

        // Calcular Total + 7 Dimensiones
        return [
            'extralaboral_total' => $calculateDetail('extralaboral_total_puntaje', $baremoExtralaboralTotal),
            'extralaboral_tiempo_fuera' => $calculateDetail('extralaboral_tiempo_fuera_puntaje', $baremoDimensiones['extralaboral_tiempo_fuera']),
            'extralaboral_relaciones_familiares' => $calculateDetail('extralaboral_relaciones_familiares_puntaje', $baremoDimensiones['extralaboral_relaciones_familiares']),
            'extralaboral_comunicacion' => $calculateDetail('extralaboral_comunicacion_puntaje', $baremoDimensiones['extralaboral_comunicacion']),
            'extralaboral_situacion_economica' => $calculateDetail('extralaboral_situacion_economica_puntaje', $baremoDimensiones['extralaboral_situacion_economica']),
            'extralaboral_caracteristicas_vivienda' => $calculateDetail('extralaboral_caracteristicas_vivienda_puntaje', $baremoDimensiones['extralaboral_caracteristicas_vivienda']),
            'extralaboral_influencia_entorno' => $calculateDetail('extralaboral_influencia_entorno_puntaje', $baremoDimensiones['extralaboral_influencia_entorno']),
            'extralaboral_desplazamiento' => $calculateDetail('extralaboral_desplazamiento_puntaje', $baremoDimensiones['extralaboral_desplazamiento'])
        ];
    }

    /**
     * Calcular detalles completos de Extralaboral Forma A
     * Incluye: 1 Total + 7 Dimensiones
     * Aplica baremos oficiales de Tabla 17 (Jefes/Profesionales/Técnicos) y Tabla 34 (Total)
     *
     * @param array $results Resultados de trabajadores con intralaboral_type='A'
     * @return array Cálculos detallados para Total + 7 Dimensiones
     */
    private function calculateExtralaboralFormaADetails($results)
    {
        if (empty($results)) {
            return null;
        }

        // BAREMOS - Desde fuente única autorizada (ExtralaboralScoring)
        $baremoExtralaboralTotal = ExtralaboralScoring::getBaremoTotal('A');

        // Dimensiones Extralaboral Forma A - Nombres exactos de ExtralaboralScoring
        $baremoDimensiones = [
            'tiempo_fuera' => ExtralaboralScoring::getBaremoDimension('tiempo_fuera_trabajo', 'A'),
            'relaciones_familiares' => ExtralaboralScoring::getBaremoDimension('relaciones_familiares', 'A'),
            'comunicacion' => ExtralaboralScoring::getBaremoDimension('comunicacion_relaciones', 'A'),
            'situacion_economica' => ExtralaboralScoring::getBaremoDimension('situacion_economica', 'A'),
            'caracteristicas_vivienda' => ExtralaboralScoring::getBaremoDimension('caracteristicas_vivienda', 'A'),
            'influencia_entorno' => ExtralaboralScoring::getBaremoDimension('influencia_entorno', 'A'),
            'desplazamiento' => ExtralaboralScoring::getBaremoDimension('desplazamiento', 'A'),
        ];

        // Función helper (formato compatible con vistas de intralaboral)
        $calculateDetail = function($field, $baremo) use ($results) {
            $puntajes = array_filter(array_column($results, $field), function($v) {
                return $v !== null && $v !== '';
            });

            if (empty($puntajes)) {
                return [
                    'promedio' => 0,
                    'nivel' => 'sin_riesgo',
                    'cantidad' => 0,
                    'suma' => 0,
                    'baremo' => $baremo,
                    'rango_aplicado' => [0, 0]
                ];
            }

            $suma = array_sum($puntajes);
            $cantidad = count($puntajes);
            $promedio = $suma / $cantidad;

            $nivel = 'sin_riesgo';
            foreach ($baremo as $nivelKey => $rango) {
                if ($promedio >= $rango[0] && $promedio <= $rango[1]) {
                    $nivel = $nivelKey;
                    break;
                }
            }

            return [
                'promedio' => round($promedio, 2),
                'nivel' => $nivel,
                'cantidad' => $cantidad,
                'suma' => round($suma, 2),
                'baremo' => $baremo,
                'rango_aplicado' => $baremo[$nivel] ?? [0, 0]
            ];
        };

        // Retornar Total + 7 Dimensiones
        return [
            'extralaboral_total' => $calculateDetail('extralaboral_total_puntaje', $baremoExtralaboralTotal),
            'extralaboral_tiempo_fuera' => $calculateDetail('extralaboral_tiempo_fuera_puntaje', $baremoDimensiones['tiempo_fuera']),
            'extralaboral_relaciones_familiares' => $calculateDetail('extralaboral_relaciones_familiares_puntaje', $baremoDimensiones['relaciones_familiares']),
            'extralaboral_comunicacion' => $calculateDetail('extralaboral_comunicacion_puntaje', $baremoDimensiones['comunicacion']),
            'extralaboral_situacion_economica' => $calculateDetail('extralaboral_situacion_economica_puntaje', $baremoDimensiones['situacion_economica']),
            'extralaboral_caracteristicas_vivienda' => $calculateDetail('extralaboral_caracteristicas_vivienda_puntaje', $baremoDimensiones['caracteristicas_vivienda']),
            'extralaboral_influencia_entorno' => $calculateDetail('extralaboral_influencia_entorno_puntaje', $baremoDimensiones['influencia_entorno']),
            'extralaboral_desplazamiento' => $calculateDetail('extralaboral_desplazamiento_puntaje', $baremoDimensiones['desplazamiento'])
        ];
    }

    /**
     * Calcular detalles completos de Extralaboral Forma B
     * Incluye: 1 Total + 7 Dimensiones
     * Aplica baremos oficiales de Tabla 18 (Auxiliares/Operarios) y Tabla 34 (Total)
     *
     * @param array $results Resultados de trabajadores con intralaboral_type='B'
     * @return array Cálculos detallados para Total + 7 Dimensiones
     */
    private function calculateExtralaboralFormaBDetails($results)
    {
        if (empty($results)) {
            return null;
        }

        // BAREMOS - Desde fuente única autorizada (ExtralaboralScoring)
        $baremoExtralaboralTotal = ExtralaboralScoring::getBaremoTotal('B');

        // Dimensiones Extralaboral Forma B - Nombres exactos de ExtralaboralScoring
        $baremoDimensiones = [
            'tiempo_fuera' => ExtralaboralScoring::getBaremoDimension('tiempo_fuera_trabajo', 'B'),
            'relaciones_familiares' => ExtralaboralScoring::getBaremoDimension('relaciones_familiares', 'B'),
            'comunicacion' => ExtralaboralScoring::getBaremoDimension('comunicacion_relaciones', 'B'),
            'situacion_economica' => ExtralaboralScoring::getBaremoDimension('situacion_economica', 'B'),
            'caracteristicas_vivienda' => ExtralaboralScoring::getBaremoDimension('caracteristicas_vivienda', 'B'),
            'influencia_entorno' => ExtralaboralScoring::getBaremoDimension('influencia_entorno', 'B'),
            'desplazamiento' => ExtralaboralScoring::getBaremoDimension('desplazamiento', 'B'),
        ];

        // Función helper (formato compatible con vistas de intralaboral)
        $calculateDetail = function($field, $baremo) use ($results) {
            $puntajes = array_filter(array_column($results, $field), function($v) {
                return $v !== null && $v !== '';
            });

            if (empty($puntajes)) {
                return [
                    'promedio' => 0,
                    'nivel' => 'sin_riesgo',
                    'cantidad' => 0,
                    'suma' => 0,
                    'baremo' => $baremo,
                    'rango_aplicado' => [0, 0]
                ];
            }

            $suma = array_sum($puntajes);
            $cantidad = count($puntajes);
            $promedio = $suma / $cantidad;

            $nivel = 'sin_riesgo';
            foreach ($baremo as $nivelKey => $rango) {
                if ($promedio >= $rango[0] && $promedio <= $rango[1]) {
                    $nivel = $nivelKey;
                    break;
                }
            }

            return [
                'promedio' => round($promedio, 2),
                'nivel' => $nivel,
                'cantidad' => $cantidad,
                'suma' => round($suma, 2),
                'baremo' => $baremo,
                'rango_aplicado' => $baremo[$nivel] ?? [0, 0]
            ];
        };

        // Retornar Total + 7 Dimensiones
        return [
            'extralaboral_total' => $calculateDetail('extralaboral_total_puntaje', $baremoExtralaboralTotal),
            'extralaboral_tiempo_fuera' => $calculateDetail('extralaboral_tiempo_fuera_puntaje', $baremoDimensiones['tiempo_fuera']),
            'extralaboral_relaciones_familiares' => $calculateDetail('extralaboral_relaciones_familiares_puntaje', $baremoDimensiones['relaciones_familiares']),
            'extralaboral_comunicacion' => $calculateDetail('extralaboral_comunicacion_puntaje', $baremoDimensiones['comunicacion']),
            'extralaboral_situacion_economica' => $calculateDetail('extralaboral_situacion_economica_puntaje', $baremoDimensiones['situacion_economica']),
            'extralaboral_caracteristicas_vivienda' => $calculateDetail('extralaboral_caracteristicas_vivienda_puntaje', $baremoDimensiones['caracteristicas_vivienda']),
            'extralaboral_influencia_entorno' => $calculateDetail('extralaboral_influencia_entorno_puntaje', $baremoDimensiones['influencia_entorno']),
            'extralaboral_desplazamiento' => $calculateDetail('extralaboral_desplazamiento_puntaje', $baremoDimensiones['desplazamiento'])
        ];
    }

    /**
     * Mapa de Calor - Estrés Forma A
     */
    public function estresFormaA($serviceId)
    {
        // Verificar acceso
        $service = $this->checkAccess($serviceId);
        if ($service instanceof \CodeIgniter\HTTP\RedirectResponse ||
            $service instanceof \CodeIgniter\View\View ||
            is_string($service)) {
            return $service;
        }

        // Obtener trabajadores con forma A (intralaboral_type = 'A')
        $results = $this->calculatedResultModel
            ->select('calculated_results.*, workers.name, workers.document, workers.intralaboral_type')
            ->join('workers', 'workers.id = calculated_results.worker_id')
            ->where('calculated_results.battery_service_id', $serviceId)
            ->where('workers.intralaboral_type', 'A')
            ->where('calculated_results.estres_total_puntaje IS NOT NULL')
            ->findAll();

        if (empty($results)) {
            // No hay datos, mostrar vista informativa
            $data = [
                'title' => 'Sin Datos - Estrés Forma A',
                'service' => $service,
                'formType' => 'Cuestionario para la Evaluación del Estrés - Forma A',
            ];
            return view('reports/estres/no_data', $data);
        }

        // Calcular datos detallados para Estrés Forma A
        $calculations = $this->calculateEstresFormaADetails($results);

        $data = [
            'title' => 'Mapa de Calor Estrés - Forma A',
            'service' => $service,
            'results' => $results,
            'totalWorkers' => count($results),
            'calculations' => $calculations,
            'forma' => 'A'
        ];

        return view('reports/estres/detail_forma_a', $data);
    }

    /**
     * Mapa de Calor - Estrés Forma B
     */
    public function estresFormaB($serviceId)
    {
        // Verificar acceso
        $service = $this->checkAccess($serviceId);
        if ($service instanceof \CodeIgniter\HTTP\RedirectResponse ||
            $service instanceof \CodeIgniter\View\View ||
            is_string($service)) {
            return $service;
        }

        // Obtener trabajadores con forma B (intralaboral_type = 'B')
        $results = $this->calculatedResultModel
            ->select('calculated_results.*, workers.name, workers.document, workers.intralaboral_type')
            ->join('workers', 'workers.id = calculated_results.worker_id')
            ->where('calculated_results.battery_service_id', $serviceId)
            ->where('workers.intralaboral_type', 'B')
            ->where('calculated_results.estres_total_puntaje IS NOT NULL')
            ->findAll();

        if (empty($results)) {
            // No hay datos, mostrar vista informativa
            $data = [
                'title' => 'Sin Datos - Estrés Forma B',
                'service' => $service,
                'formType' => 'Cuestionario para la Evaluación del Estrés - Forma B',
            ];
            return view('reports/estres/no_data', $data);
        }

        // Calcular datos detallados para Estrés Forma B
        $calculations = $this->calculateEstresFormaBDetails($results);

        $data = [
            'title' => 'Mapa de Calor Estrés - Forma B',
            'service' => $service,
            'results' => $results,
            'totalWorkers' => count($results),
            'calculations' => $calculations,
            'forma' => 'B'
        ];

        return view('reports/estres/detail_forma_b', $data);
    }

    /**
     * Calcular detalles del Estrés - Forma A
     * Baremos según Resolución 2404/2019 - Tabla 23 (Forma A)
     */
    private function calculateEstresFormaADetails($results)
    {
        // BAREMOS - Desde fuente única autorizada (EstresScoring)
        $baremoEstresTotal = EstresScoring::getBaremoA();

        // Calcular puntaje bruto promedio según metodología oficial
        // Paso 2. Obtención del puntaje bruto total (Manual, página 381):
        // a. Promedio ítems 1-8, resultado × 4
        // b. Promedio ítems 9-12, resultado × 3
        // c. Promedio ítems 13-22, resultado × 2
        // d. Promedio ítems 23-31 (sin multiplicar)
        // Puntaje bruto = a + b + c + d

        $responseModel = new \App\Models\ResponseModel();
        $workerIds = array_column($results, 'worker_id');

        $puntajeBrutoTotal = 0;
        $countWorkers = 0;

        foreach ($workerIds as $workerId) {
            $responses = $responseModel
                ->where('worker_id', $workerId)
                ->where('form_type', 'estres')
                ->findAll();

            if (!empty($responses)) {
                // Organizar respuestas por número de pregunta
                $respuestasPorPregunta = [];
                foreach ($responses as $resp) {
                    $respuestasPorPregunta[$resp['question_number']] = $resp['answer_value'];
                }

                // Calcular cada subtotal según la metodología
                $suma_1_8 = 0;
                $count_1_8 = 0;
                for ($i = 1; $i <= 8; $i++) {
                    if (isset($respuestasPorPregunta[$i])) {
                        $suma_1_8 += $respuestasPorPregunta[$i];
                        $count_1_8++;
                    }
                }
                $promedio_1_8 = $count_1_8 > 0 ? $suma_1_8 / $count_1_8 : 0;
                $subtotal_a = $promedio_1_8 * 4;

                $suma_9_12 = 0;
                $count_9_12 = 0;
                for ($i = 9; $i <= 12; $i++) {
                    if (isset($respuestasPorPregunta[$i])) {
                        $suma_9_12 += $respuestasPorPregunta[$i];
                        $count_9_12++;
                    }
                }
                $promedio_9_12 = $count_9_12 > 0 ? $suma_9_12 / $count_9_12 : 0;
                $subtotal_b = $promedio_9_12 * 3;

                $suma_13_22 = 0;
                $count_13_22 = 0;
                for ($i = 13; $i <= 22; $i++) {
                    if (isset($respuestasPorPregunta[$i])) {
                        $suma_13_22 += $respuestasPorPregunta[$i];
                        $count_13_22++;
                    }
                }
                $promedio_13_22 = $count_13_22 > 0 ? $suma_13_22 / $count_13_22 : 0;
                $subtotal_c = $promedio_13_22 * 2;

                $suma_23_31 = 0;
                $count_23_31 = 0;
                for ($i = 23; $i <= 31; $i++) {
                    if (isset($respuestasPorPregunta[$i])) {
                        $suma_23_31 += $respuestasPorPregunta[$i];
                        $count_23_31++;
                    }
                }
                $promedio_23_31 = $count_23_31 > 0 ? $suma_23_31 / $count_23_31 : 0;
                $subtotal_d = $promedio_23_31;

                $puntajeBruto = $subtotal_a + $subtotal_b + $subtotal_c + $subtotal_d;
                $puntajeBrutoTotal += $puntajeBruto;
                $countWorkers++;
            }
        }

        $puntajeBrutoPromedio = $countWorkers > 0 ? $puntajeBrutoTotal / $countWorkers : 0;

        // Función auxiliar para calcular cada métrica (formato librería: 'nivel' => [min, max])
        $calculateDetail = function($field, $baremo) use ($results) {
            $valores = array_column($results, $field);
            $promedio = count($valores) > 0 ? array_sum($valores) / count($valores) : 0;

            // Clasificar según baremo (formato: 'nivel' => [min, max])
            $nivel = 'muy_bajo'; // Nivel por defecto para estrés
            foreach ($baremo as $nivelKey => $rango) {
                if ($promedio >= $rango[0] && $promedio <= $rango[1]) {
                    $nivel = $nivelKey;
                    break;
                }
            }

            return [
                'promedio' => round($promedio, 1),
                'nivel' => $nivel,
                'total_trabajadores' => count($valores)
            ];
        };

        // Calcular Total
        $estresTotal = $calculateDetail('estres_total_puntaje', $baremoEstresTotal);
        $estresTotal['puntaje_bruto_promedio'] = round($puntajeBrutoPromedio, 1);

        return [
            'estres_total' => $estresTotal
        ];
    }

    /**
     * Calcular detalles del Estrés - Forma B
     * Baremos según Resolución 2404/2019 - Tabla 24 (Forma B)
     */
    private function calculateEstresFormaBDetails($results)
    {
        // BAREMOS - Desde fuente única autorizada (EstresScoring)
        $baremoEstresTotal = EstresScoring::getBaremoB();

        // Calcular puntaje bruto promedio según metodología oficial
        // Paso 2. Obtención del puntaje bruto total (Manual, página 381):
        // a. Promedio ítems 1-8, resultado × 4
        // b. Promedio ítems 9-12, resultado × 3
        // c. Promedio ítems 13-22, resultado × 2
        // d. Promedio ítems 23-31 (sin multiplicar)
        // Puntaje bruto = a + b + c + d

        $responseModel = new \App\Models\ResponseModel();
        $workerIds = array_column($results, 'worker_id');

        $puntajeBrutoTotal = 0;
        $countWorkers = 0;

        foreach ($workerIds as $workerId) {
            $responses = $responseModel
                ->where('worker_id', $workerId)
                ->where('form_type', 'estres')
                ->findAll();

            if (!empty($responses)) {
                // Organizar respuestas por número de pregunta
                $respuestasPorPregunta = [];
                foreach ($responses as $resp) {
                    $respuestasPorPregunta[$resp['question_number']] = $resp['answer_value'];
                }

                // Calcular cada subtotal según la metodología
                $suma_1_8 = 0;
                $count_1_8 = 0;
                for ($i = 1; $i <= 8; $i++) {
                    if (isset($respuestasPorPregunta[$i])) {
                        $suma_1_8 += $respuestasPorPregunta[$i];
                        $count_1_8++;
                    }
                }
                $promedio_1_8 = $count_1_8 > 0 ? $suma_1_8 / $count_1_8 : 0;
                $subtotal_a = $promedio_1_8 * 4;

                $suma_9_12 = 0;
                $count_9_12 = 0;
                for ($i = 9; $i <= 12; $i++) {
                    if (isset($respuestasPorPregunta[$i])) {
                        $suma_9_12 += $respuestasPorPregunta[$i];
                        $count_9_12++;
                    }
                }
                $promedio_9_12 = $count_9_12 > 0 ? $suma_9_12 / $count_9_12 : 0;
                $subtotal_b = $promedio_9_12 * 3;

                $suma_13_22 = 0;
                $count_13_22 = 0;
                for ($i = 13; $i <= 22; $i++) {
                    if (isset($respuestasPorPregunta[$i])) {
                        $suma_13_22 += $respuestasPorPregunta[$i];
                        $count_13_22++;
                    }
                }
                $promedio_13_22 = $count_13_22 > 0 ? $suma_13_22 / $count_13_22 : 0;
                $subtotal_c = $promedio_13_22 * 2;

                $suma_23_31 = 0;
                $count_23_31 = 0;
                for ($i = 23; $i <= 31; $i++) {
                    if (isset($respuestasPorPregunta[$i])) {
                        $suma_23_31 += $respuestasPorPregunta[$i];
                        $count_23_31++;
                    }
                }
                $promedio_23_31 = $count_23_31 > 0 ? $suma_23_31 / $count_23_31 : 0;
                $subtotal_d = $promedio_23_31;

                $puntajeBruto = $subtotal_a + $subtotal_b + $subtotal_c + $subtotal_d;
                $puntajeBrutoTotal += $puntajeBruto;
                $countWorkers++;
            }
        }

        $puntajeBrutoPromedio = $countWorkers > 0 ? $puntajeBrutoTotal / $countWorkers : 0;

        // Función auxiliar para calcular cada métrica (formato librería: 'nivel' => [min, max])
        $calculateDetail = function($field, $baremo) use ($results) {
            $valores = array_column($results, $field);
            $promedio = count($valores) > 0 ? array_sum($valores) / count($valores) : 0;

            // Clasificar según baremo (formato: 'nivel' => [min, max])
            $nivel = 'muy_bajo'; // Nivel por defecto para estrés
            foreach ($baremo as $nivelKey => $rango) {
                if ($promedio >= $rango[0] && $promedio <= $rango[1]) {
                    $nivel = $nivelKey;
                    break;
                }
            }

            return [
                'promedio' => round($promedio, 1),
                'nivel' => $nivel,
                'total_trabajadores' => count($valores)
            ];
        };

        // Calcular Total
        $estresTotal = $calculateDetail('estres_total_puntaje', $baremoEstresTotal);
        $estresTotal['puntaje_bruto_promedio'] = round($puntajeBrutoPromedio, 1);

        return [
            'estres_total' => $estresTotal
        ];
    }

    /**
     * Mapa de calor Extralaboral - Forma A (Jefes, profesionales, técnicos)
     */
    public function extralaboralFormaA($serviceId)
    {
        $service = $this->checkAccess($serviceId);
        if ($service instanceof \CodeIgniter\HTTP\RedirectResponse ||
            $service instanceof \CodeIgniter\View\View ||
            is_string($service)) {
            return $service;
        }

        // Obtener trabajadores con forma A que tengan datos extralaborales
        $results = $this->calculatedResultModel
            ->select('calculated_results.*, workers.name, workers.document, workers.intralaboral_type')
            ->join('workers', 'workers.id = calculated_results.worker_id')
            ->where('calculated_results.battery_service_id', $serviceId)
            ->where('workers.intralaboral_type', 'A')
            ->where('calculated_results.extralaboral_total_puntaje IS NOT NULL')
            ->findAll();

        if (empty($results)) {
            $data = [
                'title' => 'Sin Datos - Extralaboral Forma A',
                'service' => $service,
                'formType' => 'Cuestionario de Factores de Riesgo Psicosocial Extralaboral - Forma A'
            ];
            return view('reports/extralaboral/no_data', $data);
        }

        $calculations = $this->calculateExtralaboralFormaADetails($results);

        $data = [
            'title' => 'Mapa de Calor Extralaboral - Forma A',
            'service' => $service,
            'results' => $results,
            'totalWorkers' => count($results),
            'calculations' => $calculations,
            'forma' => 'A'
        ];

        return view('reports/extralaboral/detail_forma_a', $data);
    }

    /**
     * Mapa de calor Extralaboral - Forma B (Auxiliares, operarios)
     */
    public function extralaboralFormaB($serviceId)
    {
        $service = $this->checkAccess($serviceId);
        if ($service instanceof \CodeIgniter\HTTP\RedirectResponse ||
            $service instanceof \CodeIgniter\View\View ||
            is_string($service)) {
            return $service;
        }

        // Obtener trabajadores con forma B que tengan datos extralaborales
        $results = $this->calculatedResultModel
            ->select('calculated_results.*, workers.name, workers.document, workers.intralaboral_type')
            ->join('workers', 'workers.id = calculated_results.worker_id')
            ->where('calculated_results.battery_service_id', $serviceId)
            ->where('workers.intralaboral_type', 'B')
            ->where('calculated_results.extralaboral_total_puntaje IS NOT NULL')
            ->findAll();

        if (empty($results)) {
            $data = [
                'title' => 'Sin Datos - Extralaboral Forma B',
                'service' => $service,
                'formType' => 'Cuestionario de Factores de Riesgo Psicosocial Extralaboral - Forma B'
            ];
            return view('reports/extralaboral/no_data', $data);
        }

        $calculations = $this->calculateExtralaboralFormaBDetails($results);

        $data = [
            'title' => 'Mapa de Calor Extralaboral - Forma B',
            'service' => $service,
            'results' => $results,
            'totalWorkers' => count($results),
            'calculations' => $calculations,
            'forma' => 'B'
        ];

        return view('reports/extralaboral/detail_forma_b', $data);
    }

    /**
     * Consolidación Grupal - Reporte estilo Excel
     * Muestra distribución por escalas agrupadas: Bajo/Sin riesgo, Medio, Alto/Muy alto
     * Separado por Forma A y Forma B (sin "conjunto" - no existe baremo para mezclar formas)
     *
     * NOTA METODOLÓGICA (Resolución 2404/2019):
     * Los baremos de la Tabla 34 son específicos por forma. No se deben mezclar
     * trabajadores de Forma A con Forma B para calcular porcentajes combinados.
     */
    public function consolidacion($serviceId)
    {
        $service = $this->checkAccess($serviceId);
        if ($service instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $service;
        }

        // Obtener todos los resultados del servicio
        $results = $this->calculatedResultModel
            ->where('battery_service_id', $serviceId)
            ->findAll();

        if (empty($results)) {
            return view('reports/consolidacion/no_data', [
                'title' => 'Sin Datos - Consolidación Grupal',
                'service' => $service
            ]);
        }

        // Separar por forma
        $formaA = array_filter($results, fn($r) => $r['intralaboral_form_type'] === 'A');
        $formaB = array_filter($results, fn($r) => $r['intralaboral_form_type'] === 'B');

        $totalA = count($formaA);
        $totalB = count($formaB);
        $totalGeneral = $totalA + $totalB; // Solo para información, no para cálculos

        // Obtener escalas y calcular consolidación
        $escalas = $this->getEscalasConsolidacion();
        $consolidacion = [];

        foreach ($escalas as $seccion => $items) {
            $consolidacion[$seccion] = [];

            foreach ($items as $key => $config) {
                $nivelField = $config['nivel_field'];
                $label = $config['label'];
                $isEstres = $config['is_estres'] ?? false;

                // Contar por nivel para cada forma (sin mezclar)
                $conteoA = $this->contarPorNivelAgrupado($formaA, $nivelField, $isEstres);
                $conteoB = $this->contarPorNivelAgrupado($formaB, $nivelField, $isEstres);

                $consolidacion[$seccion][$key] = [
                    'label' => $label,
                    'forma_a' => [
                        'conteo' => $conteoA,
                        'porcentaje' => $this->calcularPorcentajesConsolidacion($conteoA, $totalA),
                        'total' => $totalA
                    ],
                    'forma_b' => [
                        'conteo' => $conteoB,
                        'porcentaje' => $this->calcularPorcentajesConsolidacion($conteoB, $totalB),
                        'total' => $totalB
                    ]
                    // ELIMINADO: 'conjunto' - no existe baremo para mezclar formas A y B
                ];
            }
        }

        $data = [
            'title' => 'Consolidación Grupal - ' . $service['service_name'],
            'service' => $service,
            'consolidacion' => $consolidacion,
            'totales' => [
                'forma_a' => $totalA,
                'forma_b' => $totalB,
                'conjunto' => $totalGeneral // Total de trabajadores (Forma A + Forma B)
            ],
            'genero' => [
                'masculino' => count(array_filter($results, fn($r) => $r['gender'] === 'Masculino')),
                'femenino' => count(array_filter($results, fn($r) => $r['gender'] === 'Femenino'))
            ]
        ];

        return view('reports/consolidacion/dashboard', $data);
    }

    /**
     * Definir estructura de escalas para consolidación
     */
    private function getEscalasConsolidacion()
    {
        return [
            'total_general' => [
                'total' => [
                    'nivel_field' => 'puntaje_total_general_nivel',
                    'label' => 'Resultado Total General'
                ]
            ],
            'intralaboral' => [
                'total' => [
                    'nivel_field' => 'intralaboral_total_nivel',
                    'label' => 'Total Intralaboral'
                ]
            ],
            'dominios_intralaboral' => [
                'liderazgo' => [
                    'nivel_field' => 'dom_liderazgo_nivel',
                    'label' => 'Liderazgo y relaciones sociales en el trabajo'
                ],
                'control' => [
                    'nivel_field' => 'dom_control_nivel',
                    'label' => 'Control sobre el trabajo'
                ],
                'demandas' => [
                    'nivel_field' => 'dom_demandas_nivel',
                    'label' => 'Demandas del trabajo'
                ],
                'recompensas' => [
                    'nivel_field' => 'dom_recompensas_nivel',
                    'label' => 'Recompensas'
                ]
            ],
            'dimensiones_liderazgo' => [
                'caracteristicas_liderazgo' => [
                    'nivel_field' => 'dim_caracteristicas_liderazgo_nivel',
                    'label' => 'Características del liderazgo'
                ],
                'relaciones_sociales' => [
                    'nivel_field' => 'dim_relaciones_sociales_nivel',
                    'label' => 'Relaciones sociales en el trabajo'
                ],
                'retroalimentacion' => [
                    'nivel_field' => 'dim_retroalimentacion_nivel',
                    'label' => 'Retroalimentación del desempeño'
                ],
                'relacion_colaboradores' => [
                    'nivel_field' => 'dim_relacion_colaboradores_nivel',
                    'label' => 'Relación con los colaboradores (subordinados)'
                ]
            ],
            'dimensiones_control' => [
                'claridad_rol' => [
                    'nivel_field' => 'dim_claridad_rol_nivel',
                    'label' => 'Claridad de rol'
                ],
                'capacitacion' => [
                    'nivel_field' => 'dim_capacitacion_nivel',
                    'label' => 'Capacitación'
                ],
                'participacion_cambio' => [
                    'nivel_field' => 'dim_participacion_manejo_cambio_nivel',
                    'label' => 'Participación y manejo del cambio'
                ],
                'oportunidades_desarrollo' => [
                    'nivel_field' => 'dim_oportunidades_desarrollo_nivel',
                    'label' => 'Oportunidades para el uso y desarrollo de habilidades'
                ],
                'control_autonomia' => [
                    'nivel_field' => 'dim_control_autonomia_nivel',
                    'label' => 'Control y autonomía sobre el trabajo'
                ]
            ],
            'dimensiones_demandas' => [
                'demandas_ambientales' => [
                    'nivel_field' => 'dim_demandas_ambientales_nivel',
                    'label' => 'Demandas ambientales y de esfuerzo físico'
                ],
                'demandas_emocionales' => [
                    'nivel_field' => 'dim_demandas_emocionales_nivel',
                    'label' => 'Demandas emocionales'
                ],
                'demandas_cuantitativas' => [
                    'nivel_field' => 'dim_demandas_cuantitativas_nivel',
                    'label' => 'Demandas cuantitativas'
                ],
                'influencia_extralaboral' => [
                    'nivel_field' => 'dim_influencia_trabajo_entorno_extralaboral_nivel',
                    'label' => 'Influencia del trabajo sobre el entorno extralaboral'
                ],
                'demandas_responsabilidad' => [
                    'nivel_field' => 'dim_demandas_responsabilidad_nivel',
                    'label' => 'Exigencias de responsabilidad del cargo'
                ],
                'carga_mental' => [
                    'nivel_field' => 'dim_demandas_carga_mental_nivel',
                    'label' => 'Demandas de carga mental'
                ],
                'consistencia_rol' => [
                    'nivel_field' => 'dim_consistencia_rol_nivel',
                    'label' => 'Consistencia del rol'
                ],
                'jornada_trabajo' => [
                    'nivel_field' => 'dim_demandas_jornada_trabajo_nivel',
                    'label' => 'Demandas de la jornada de trabajo'
                ]
            ],
            'dimensiones_recompensas' => [
                'recompensas_pertenencia' => [
                    'nivel_field' => 'dim_recompensas_pertenencia_nivel',
                    'label' => 'Recompensas derivadas de la pertenencia a la organización'
                ],
                'reconocimiento_compensacion' => [
                    'nivel_field' => 'dim_reconocimiento_compensacion_nivel',
                    'label' => 'Reconocimiento y compensación'
                ]
            ],
            'extralaboral' => [
                'total' => [
                    'nivel_field' => 'extralaboral_total_nivel',
                    'label' => 'Total Extralaboral'
                ]
            ],
            'dimensiones_extralaboral' => [
                'tiempo_fuera' => [
                    'nivel_field' => 'extralaboral_tiempo_fuera_nivel',
                    'label' => 'Tiempo fuera del trabajo'
                ],
                'relaciones_familiares' => [
                    'nivel_field' => 'extralaboral_relaciones_familiares_nivel',
                    'label' => 'Relaciones familiares'
                ],
                'comunicacion' => [
                    'nivel_field' => 'extralaboral_comunicacion_nivel',
                    'label' => 'Comunicación y relaciones interpersonales'
                ],
                'situacion_economica' => [
                    'nivel_field' => 'extralaboral_situacion_economica_nivel',
                    'label' => 'Situación económica del grupo familiar'
                ],
                'caracteristicas_vivienda' => [
                    'nivel_field' => 'extralaboral_caracteristicas_vivienda_nivel',
                    'label' => 'Características de la vivienda y de su entorno'
                ],
                'influencia_entorno' => [
                    'nivel_field' => 'extralaboral_influencia_entorno_nivel',
                    'label' => 'Influencia del entorno extralaboral sobre el trabajo'
                ],
                'desplazamiento' => [
                    'nivel_field' => 'extralaboral_desplazamiento_nivel',
                    'label' => 'Desplazamiento vivienda - trabajo - vivienda'
                ]
            ],
            'estres' => [
                'total' => [
                    'nivel_field' => 'estres_total_nivel',
                    'label' => 'Total Estrés',
                    'is_estres' => true
                ]
            ]
        ];
    }

    /**
     * Contar resultados por nivel agrupado para consolidación
     */
    private function contarPorNivelAgrupado($results, $nivelField, $isEstres = false)
    {
        $conteo = [
            'bajo_sin_riesgo' => 0,
            'riesgo_medio' => 0,
            'alto_muy_alto' => 0
        ];

        foreach ($results as $result) {
            $nivel = $result[$nivelField] ?? null;

            if ($isEstres) {
                // Para estrés: muy_bajo, bajo, medio, alto, muy_alto
                if (in_array($nivel, ['muy_bajo', 'bajo'])) {
                    $conteo['bajo_sin_riesgo']++;
                } elseif ($nivel === 'medio') {
                    $conteo['riesgo_medio']++;
                } elseif (in_array($nivel, ['alto', 'muy_alto'])) {
                    $conteo['alto_muy_alto']++;
                }
            } else {
                // Para intralaboral/extralaboral
                if (in_array($nivel, ['sin_riesgo', 'riesgo_bajo'])) {
                    $conteo['bajo_sin_riesgo']++;
                } elseif ($nivel === 'riesgo_medio') {
                    $conteo['riesgo_medio']++;
                } elseif (in_array($nivel, ['riesgo_alto', 'riesgo_muy_alto'])) {
                    $conteo['alto_muy_alto']++;
                }
            }
        }

        return $conteo;
    }

    /**
     * Calcular porcentajes para consolidación
     */
    private function calcularPorcentajesConsolidacion($conteo, $total)
    {
        if ($total === 0) {
            return [
                'bajo_sin_riesgo' => 0,
                'riesgo_medio' => 0,
                'alto_muy_alto' => 0
            ];
        }

        return [
            'bajo_sin_riesgo' => round(($conteo['bajo_sin_riesgo'] / $total) * 100, 1),
            'riesgo_medio' => round(($conteo['riesgo_medio'] / $total) * 100, 1),
            'alto_muy_alto' => round(($conteo['alto_muy_alto'] / $total) * 100, 1)
        ];
    }

    /**
     * Consolidación de Ficha de Datos Generales
     * Muestra distribución demográfica con gráficos de torta y barras
     * Incluye filtros y segmentadores similares al dashboard intralaboral
     */
    public function fichaDatosGenerales($serviceId)
    {
        $service = $this->checkAccess($serviceId);
        if ($service instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $service;
        }

        // Obtener datos demográficos con JOIN a workers y calculated_results
        $db = \Config\Database::connect();
        $builder = $db->table('worker_demographics wd');
        $builder->select('wd.*,
                          w.name as worker_name,
                          w.document as worker_document,
                          w.email as worker_email,
                          cr.intralaboral_form_type,
                          cr.intralaboral_total_nivel,
                          cr.extralaboral_total_nivel,
                          cr.estres_total_nivel,
                          cr.puntaje_total_general_nivel,
                          (YEAR(CURDATE()) - wd.birth_year) as age');
        $builder->join('workers w', 'w.id = wd.worker_id', 'inner');
        $builder->join('calculated_results cr', 'cr.worker_id = wd.worker_id', 'left');
        $builder->where('w.battery_service_id', $serviceId);
        $builder->where('wd.completed_at IS NOT NULL');
        $results = $builder->get()->getResultArray();

        if (empty($results)) {
            return view('reports/consolidacion/ficha_no_data', [
                'title' => 'Sin Datos - Ficha de Datos Generales',
                'service' => $service
            ]);
        }

        // Preparar segmentadores (valores únicos de cada campo)
        $segmentadores = [
            'generos' => $this->getUniqueValues($results, 'gender'),
            'departamentos' => $this->getUniqueValues($results, 'department'),
            'cargos' => $this->getUniqueValues($results, 'position_name'),
            'tipos_cargo' => $this->getUniqueValues($results, 'position_type'),
            'tipos_contrato' => $this->getUniqueValues($results, 'contract_type'),
            'niveles_estudio' => $this->getUniqueValues($results, 'education_level'),
            'ciudades' => $this->getUniqueValues($results, 'city_residence'),
            'estados_civiles' => $this->getUniqueValues($results, 'marital_status'),
            'estratos' => $this->getUniqueValues($results, 'stratum'),
            'tipos_vivienda' => $this->getUniqueValues($results, 'housing_type'),
            'antiguedad' => $this->getTimeInCompanyLabels($this->getUniqueValues($results, 'time_in_company_type')),
            'tipos_formulario' => ['A', 'B']
        ];

        // Calcular estadísticas demográficas
        $stats = $this->calculateDemographicStats($results);

        $data = [
            'title' => 'Ficha de Datos Generales - ' . $service['service_name'],
            'service' => $service,
            'results' => $results,
            'segmentadores' => $segmentadores,
            'stats' => $stats,
            'totalWorkers' => count($results)
        ];

        return view('reports/consolidacion/ficha_dashboard', $data);
    }

    /**
     * Calcular estadísticas demográficas para gráficos
     */
    private function calculateDemographicStats($results)
    {
        $stats = [
            'gender' => [],
            'marital_status' => [],
            'education_level' => [],
            'position_type' => [],
            'contract_type' => [],
            'stratum' => [],
            'housing_type' => [],
            'department' => [],
            'city' => [],
            'age_ranges' => [],
            'time_in_company' => [],
            'form_type' => [],
            'dependents' => [],
            'hours_per_day' => []
        ];

        // Rangos de edad
        $ageRanges = [
            '18-25' => 0,
            '26-35' => 0,
            '36-45' => 0,
            '46-55' => 0,
            '56-65' => 0,
            '65+' => 0
        ];

        foreach ($results as $r) {
            // Género
            $gender = $r['gender'] ?? 'No especificado';
            $stats['gender'][$gender] = ($stats['gender'][$gender] ?? 0) + 1;

            // Estado civil
            $marital = $r['marital_status'] ?? 'No especificado';
            $stats['marital_status'][$marital] = ($stats['marital_status'][$marital] ?? 0) + 1;

            // Nivel de estudios
            $education = $r['education_level'] ?? 'No especificado';
            $stats['education_level'][$education] = ($stats['education_level'][$education] ?? 0) + 1;

            // Tipo de cargo
            $positionType = $r['position_type'] ?? 'No especificado';
            $stats['position_type'][$positionType] = ($stats['position_type'][$positionType] ?? 0) + 1;

            // Tipo de contrato
            $contract = $r['contract_type'] ?? 'No especificado';
            $stats['contract_type'][$contract] = ($stats['contract_type'][$contract] ?? 0) + 1;

            // Estrato
            $stratum = $r['stratum'] ?? 'No especificado';
            $stats['stratum'][$stratum] = ($stats['stratum'][$stratum] ?? 0) + 1;

            // Tipo de vivienda
            $housing = $r['housing_type'] ?? 'No especificado';
            $stats['housing_type'][$housing] = ($stats['housing_type'][$housing] ?? 0) + 1;

            // Departamento/Área
            $dept = $r['department'] ?? 'No especificado';
            $stats['department'][$dept] = ($stats['department'][$dept] ?? 0) + 1;

            // Ciudad
            $city = $r['city_residence'] ?? 'No especificado';
            $stats['city'][$city] = ($stats['city'][$city] ?? 0) + 1;

            // Tipo de formulario
            $formType = $r['intralaboral_form_type'] ?? 'No especificado';
            $stats['form_type'][$formType] = ($stats['form_type'][$formType] ?? 0) + 1;

            // Antigüedad
            $timeCompany = $r['time_in_company_type'] ?? 'No especificado';
            $stats['time_in_company'][$timeCompany] = ($stats['time_in_company'][$timeCompany] ?? 0) + 1;

            // Personas a cargo
            $dependents = $r['dependents'] ?? 0;
            $depKey = $dependents == 0 ? 'Sin dependientes' : ($dependents <= 2 ? '1-2 personas' : ($dependents <= 4 ? '3-4 personas' : '5+ personas'));
            $stats['dependents'][$depKey] = ($stats['dependents'][$depKey] ?? 0) + 1;

            // Horas por día
            $hours = $r['hours_per_day'] ?? 0;
            $hoursKey = $hours <= 6 ? '6 horas o menos' : ($hours <= 8 ? '7-8 horas' : ($hours <= 10 ? '9-10 horas' : 'Más de 10 horas'));
            $stats['hours_per_day'][$hoursKey] = ($stats['hours_per_day'][$hoursKey] ?? 0) + 1;

            // Rangos de edad
            $age = intval($r['age'] ?? 0);
            if ($age >= 18 && $age <= 25) $ageRanges['18-25']++;
            elseif ($age >= 26 && $age <= 35) $ageRanges['26-35']++;
            elseif ($age >= 36 && $age <= 45) $ageRanges['36-45']++;
            elseif ($age >= 46 && $age <= 55) $ageRanges['46-55']++;
            elseif ($age >= 56 && $age <= 65) $ageRanges['56-65']++;
            elseif ($age > 65) $ageRanges['65+']++;
        }

        $stats['age_ranges'] = $ageRanges;

        return $stats;
    }

    /**
     * Calcular y almacenar máximos riesgos para un servicio
     * Ruta temporal: /dev/calculate-max-risk/{id}
     */
    public function calculateMaxRisk($batteryServiceId)
    {
        // Solo permitir en desarrollo o para consultores
        $userRole = session()->get('role_name');
        if (!in_array($userRole, ['consultor', 'superadmin']) && ENVIRONMENT !== 'development') {
            return $this->response->setJSON(['error' => 'No autorizado']);
        }

        $service = new MaxRiskResultsService();

        // Calcular y almacenar (forceRecalculate = true)
        $result = $service->calculateAndStore((int)$batteryServiceId, true);

        // Obtener datos almacenados
        $data = $service->getByBatteryService((int)$batteryServiceId);

        // Formatear para mostrar
        $output = "<pre>";
        $output .= "=== CÁLCULO DE MÁXIMOS RIESGOS ===\n\n";
        $output .= "Battery Service ID: $batteryServiceId\n";
        $output .= "Status: {$result['status']}\n";
        $output .= "Mensaje: {$result['message']}\n";
        $output .= "Registros: {$result['count']}\n\n";

        if (!empty($data)) {
            $output .= sprintf("%-40s | %-10s | %-5s | %-7s | %-15s | %-7s | %-7s\n",
                "Elemento", "Tipo", "Peor", "Score", "Nivel", "A", "B");
            $output .= str_repeat('-', 105) . "\n";

            foreach ($data as $row) {
                $output .= sprintf("%-40s | %-10s | %-5s | %-7s | %-15s | %-7s | %-7s\n",
                    substr($row['element_name'], 0, 40),
                    $row['element_type'],
                    $row['worst_form'],
                    $row['worst_score'],
                    $row['worst_risk_level'],
                    $row['form_a_score'] ?? 'N/A',
                    $row['form_b_score'] ?? 'N/A'
                );
            }

            // Mostrar elementos de alto riesgo
            $highRisk = $service->getHighRiskElements((int)$batteryServiceId);
            if (!empty($highRisk)) {
                $output .= "\n\n=== ELEMENTOS DE ALTO RIESGO (para IA) ===\n";
                foreach ($highRisk as $row) {
                    $output .= "- {$row['element_name']}: {$row['worst_score']} ({$row['worst_risk_level']}) - Forma {$row['worst_form']}\n";
                }
            }

            // Estadísticas
            $stats = $service->getRiskStats((int)$batteryServiceId);
            $output .= "\n\n=== ESTADÍSTICAS ===\n";
            $output .= "Total elementos: {$stats['total_elements']}\n";
            $output .= "Muy alto: {$stats['muy_alto']}\n";
            $output .= "Alto: {$stats['alto']}\n";
            $output .= "Medio: {$stats['medio']}\n";
            $output .= "Bajo: {$stats['bajo']}\n";
            $output .= "Sin riesgo: {$stats['sin_riesgo']}\n";
            $output .= "Críticos (alto+muy_alto): {$stats['critical_count']}\n";
        }

        $output .= "</pre>";

        return $output;
    }
}

