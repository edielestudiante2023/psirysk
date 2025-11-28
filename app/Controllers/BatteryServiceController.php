<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\BatteryServiceModel;
use App\Models\CompanyModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

class BatteryServiceController extends BaseController
{
    protected $batteryServiceModel;
    protected $companyModel;
    protected $userModel;

    public function __construct()
    {
        $this->batteryServiceModel = new BatteryServiceModel();
        $this->companyModel = new CompanyModel();
        $this->userModel = new UserModel();
        helper(['form', 'url']);
    }

    public function index()
    {
        // Verificar autenticación
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $roleName = session()->get('role_name');
        $userId = session()->get('id');

        // Superadmin ve todos los servicios
        if ($roleName === 'superadmin') {
            $services = $this->batteryServiceModel
                ->select('battery_services.*, companies.name as company_name, consultants.nombre_completo as consultant_name')
                ->join('companies', 'companies.id = battery_services.company_id')
                ->join('consultants', 'consultants.id = battery_services.consultant_id', 'left')
                ->orderBy('battery_services.created_at', 'DESC')
                ->findAll();
        }
        // Consultor ve solo sus servicios (basado en empresa creada por el usuario)
        elseif ($roleName === 'consultor') {
            $services = $this->batteryServiceModel
                ->select('battery_services.*, companies.name as company_name, consultants.nombre_completo as consultant_name')
                ->join('companies', 'companies.id = battery_services.company_id')
                ->join('consultants', 'consultants.id = battery_services.consultant_id', 'left')
                ->where('companies.created_by', $userId)
                ->orderBy('battery_services.created_at', 'DESC')
                ->findAll();
        } else {
            return redirect()->to('/dashboard')->with('error', 'No tienes permisos');
        }

        $data = [
            'title' => 'Servicios de Batería Psicosocial',
            'services' => $services,
        ];

        return view('battery_services/index', $data);
    }

    public function create()
    {
        // Verificar autenticación y permisos
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $roleName = session()->get('role_name');
        if (!in_array($roleName, ['superadmin', 'consultor'])) {
            return redirect()->to('/dashboard')->with('error', 'No tienes permisos');
        }

        // Obtener empresas del consultor
        $userId = session()->get('id');
        if ($roleName === 'superadmin') {
            $companies = $this->companyModel->where('status', 'active')->findAll();
        } else {
            $companies = $this->companyModel
                ->where('created_by', $userId)
                ->where('status', 'active')
                ->findAll();
        }

        // Obtener consultores activos desde tabla consultants
        $db = \Config\Database::connect();
        $consultants = $db->table('consultants')
            ->where('activo', 1)
            ->orderBy('nombre_completo', 'ASC')
            ->get()
            ->getResultArray();

        $data = [
            'title' => 'Crear Servicio de Batería',
            'companies' => $companies,
            'consultants' => $consultants,
        ];

        return view('battery_services/create', $data);
    }

    public function store()
    {
        // Verificar autenticación
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $rules = [
            'company_id' => 'required|integer',
            'consultant_id' => 'required|integer',
            'service_name' => 'required|min_length[3]',
            'service_date' => 'required|valid_date',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Calcular fecha de expiración (7 días después de service_date)
        $serviceDate = $this->request->getPost('service_date');
        $expirationDate = date('Y-m-d', strtotime($serviceDate . ' +15 days'));

        $data = [
            'company_id' => $this->request->getPost('company_id'),
            'consultant_id' => $this->request->getPost('consultant_id'),
            'service_name' => $this->request->getPost('service_name'),
            'service_date' => $serviceDate,
            'link_expiration_date' => $expirationDate,
            'includes_intralaboral' => true,
            'includes_extralaboral' => true,
            'includes_estres' => true,
            'status' => 'planificado',
        ];

        if ($this->batteryServiceModel->save($data)) {
            return redirect()->to('/battery-services')->with('success', 'Servicio creado exitosamente');
        } else {
            return redirect()->back()->withInput()->with('error', 'Error al crear el servicio');
        }
    }

    public function edit($id)
    {
        // Verificar autenticación
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $service = $this->batteryServiceModel->find($id);
        if (!$service) {
            return redirect()->to('/battery-services')->with('error', 'Servicio no encontrado');
        }

        // Verificar permisos
        $roleName = session()->get('role_name');
        $userId = session()->get('id');
        if ($roleName === 'consultor' && $service['consultant_id'] != $userId) {
            return redirect()->to('/battery-services')->with('error', 'No tienes permisos');
        }

        // Obtener todas las empresas activas (sin filtrar por created_by)
        $companies = $this->companyModel
            ->select('companies.*, parent.name as parent_company_name, parent.contact_email as parent_contact_email')
            ->join('companies as parent', 'parent.id = companies.parent_company_id', 'left')
            ->where('companies.status', 'active')
            ->orderBy('companies.name', 'ASC')
            ->findAll();

        // Obtener consultores activos desde tabla consultants
        $db = \Config\Database::connect();
        $consultants = $db->table('consultants')
            ->where('activo', 1)
            ->orderBy('nombre_completo', 'ASC')
            ->get()
            ->getResultArray();

        $data = [
            'title' => 'Editar Servicio',
            'service' => $service,
            'companies' => $companies,
            'consultants' => $consultants,
        ];

        return view('battery_services/edit', $data);
    }

    public function update($id)
    {
        // Verificar autenticación
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $service = $this->batteryServiceModel->find($id);
        if (!$service) {
            return redirect()->to('/battery-services')->with('error', 'Servicio no encontrado');
        }

        $rules = [
            'company_id' => 'required|integer',
            'service_name' => 'required|min_length[3]',
            'service_date' => 'required|valid_date',
            'status' => 'required|in_list[planificado,en_curso,finalizado]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $serviceDate = $this->request->getPost('service_date');
        $expirationDate = date('Y-m-d', strtotime($serviceDate . ' +15 days'));

        $data = [
            'company_id' => $this->request->getPost('company_id'),
            'consultant_id' => $this->request->getPost('consultant_id'),
            'notify_parent_company' => $this->request->getPost('notify_parent_company') ? 1 : 0,
            'service_name' => $this->request->getPost('service_name'),
            'service_date' => $serviceDate,
            'link_expiration_date' => $expirationDate,
            'cantidad_forma_a' => $this->request->getPost('cantidad_forma_a') ?? 0,
            'cantidad_forma_b' => $this->request->getPost('cantidad_forma_b') ?? 0,
            'status' => $this->request->getPost('status'),
        ];

        if ($this->batteryServiceModel->update($id, $data)) {
            return redirect()->to('/battery-services')->with('success', 'Servicio actualizado exitosamente');
        } else {
            return redirect()->back()->withInput()->with('error', 'Error al actualizar el servicio');
        }
    }

    public function delete($id)
    {
        // Verificar autenticación
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $service = $this->batteryServiceModel->find($id);
        if (!$service) {
            return redirect()->to('/battery-services')->with('error', 'Servicio no encontrado');
        }

        // Verificar permisos
        $roleName = session()->get('role_name');
        if ($roleName !== 'superadmin') {
            return redirect()->to('/battery-services')->with('error', 'Solo el superadmin puede eliminar servicios');
        }

        if ($this->batteryServiceModel->delete($id)) {
            return redirect()->to('/battery-services')->with('success', 'Servicio eliminado exitosamente');
        } else {
            return redirect()->back()->with('error', 'Error al eliminar el servicio');
        }
    }

    public function view($id)
    {
        // Verificar autenticación
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $service = $this->batteryServiceModel
            ->select('battery_services.*, companies.name as company_name, companies.nit')
            ->join('companies', 'companies.id = battery_services.company_id')
            ->find($id);

        if (!$service) {
            return redirect()->to('/battery-services')->with('error', 'Servicio no encontrado');
        }

        // Get recommendations for risky dimensions
        $recommendationsController = new \App\Controllers\RecommendationsController();
        $recommendationsHtml = $recommendationsController->getRecommendationButtons($id);

        $data = [
            'title' => 'Detalle del Servicio',
            'service' => $service,
            'recommendations' => $recommendationsHtml,
        ];

        return view('battery_services/view', $data);
    }

    /**
     * Calcular datos para heatmap basado en PROMEDIOS de puntajes brutos + aplicación de baremos
     * NO usa moda/mayorías, sino promedio aritmético de puntajes transformados
     */
    private function calculateHeatmapData($results)
    {
        if (empty($results)) {
            return null;
        }

        // Función helper para calcular promedio y aplicar baremo
        $getAverageLevel = function($puntajes, $baremo) {
            // Filtrar valores nulos
            $puntajes = array_filter($puntajes, function($v) { return $v !== null && $v !== ''; });

            if (empty($puntajes)) {
                return 'sin_riesgo';
            }

            // Calcular promedio
            $promedio = array_sum($puntajes) / count($puntajes);

            // Aplicar baremo para determinar nivel
            foreach ($baremo as $nivel => $rango) {
                if ($promedio >= $rango[0] && $promedio <= $rango[1]) {
                    return $nivel;
                }
            }

            return 'sin_riesgo';
        };

        // Determinar si la mayoría es Forma A o B (para seleccionar baremos correctos)
        $formasCounts = array_count_values(array_column($results, 'intralaboral_form_type'));
        $formaType = ($formasCounts['A'] ?? 0) >= ($formasCounts['B'] ?? 0) ? 'A' : 'B';

        // BAREMOS INTRALABORAL - Dependen de Forma A o B - Corregidos según auditoría 2025-11-24 (Tabla 33)
        $baremoIntralaboralTotal = $formaType === 'A'
            ? [
                'sin_riesgo' => [0.0, 19.7],
                'riesgo_bajo' => [19.8, 25.8],
                'riesgo_medio' => [25.9, 31.5],
                'riesgo_alto' => [31.6, 38.0],
                'riesgo_muy_alto' => [38.1, 100.0]
            ]
            : [
                'sin_riesgo' => [0.0, 20.6],
                'riesgo_bajo' => [20.7, 26.0],
                'riesgo_medio' => [26.1, 31.2],
                'riesgo_alto' => [31.3, 38.7],
                'riesgo_muy_alto' => [38.8, 100.0]
            ];

        // Baremos de dominios - Dependen de Forma A o B
        $baremoDominios = $formaType === 'A'
            ? [ // Tabla 31 - Forma A
                'liderazgo' => [
                    'sin_riesgo' => [0.0, 9.1],
                    'riesgo_bajo' => [9.2, 17.7],
                    'riesgo_medio' => [17.8, 25.6],
                    'riesgo_alto' => [25.7, 34.8],
                    'riesgo_muy_alto' => [34.9, 100.0]
                ],
                'control' => [
                    'sin_riesgo' => [0.0, 10.7],
                    'riesgo_bajo' => [10.8, 19.0],
                    'riesgo_medio' => [19.1, 29.8],
                    'riesgo_alto' => [29.9, 40.5],
                    'riesgo_muy_alto' => [40.6, 100.0]
                ],
                'demandas' => [
                    'sin_riesgo' => [0.0, 28.5],
                    'riesgo_bajo' => [28.6, 35.0],
                    'riesgo_medio' => [35.1, 41.5],
                    'riesgo_alto' => [41.6, 47.5],
                    'riesgo_muy_alto' => [47.6, 100.0]
                ],
                'recompensas' => [
                    'sin_riesgo' => [0.0, 4.5],
                    'riesgo_bajo' => [4.6, 11.4],
                    'riesgo_medio' => [11.5, 20.5],
                    'riesgo_alto' => [20.6, 29.5],
                    'riesgo_muy_alto' => [29.6, 100.0]
                ]
            ]
            : [ // Tabla 32 - Forma B
                'liderazgo' => [
                    'sin_riesgo' => [0.0, 10.0],
                    'riesgo_bajo' => [10.1, 17.5],
                    'riesgo_medio' => [17.6, 25.0],
                    'riesgo_alto' => [25.1, 35.0],
                    'riesgo_muy_alto' => [35.1, 100.0]
                ],
                'control' => [
                    'sin_riesgo' => [0.0, 8.8],
                    'riesgo_bajo' => [8.9, 16.3],
                    'riesgo_medio' => [16.4, 23.8],
                    'riesgo_alto' => [23.9, 31.3],
                    'riesgo_muy_alto' => [31.4, 100.0]
                ],
                'demandas' => [
                    'sin_riesgo' => [0.0, 26.9],
                    'riesgo_bajo' => [27.0, 33.3],
                    'riesgo_medio' => [33.4, 37.8],
                    'riesgo_alto' => [37.9, 44.2],
                    'riesgo_muy_alto' => [44.3, 100.0]
                ],
                'recompensas' => [
                    'sin_riesgo' => [0.0, 2.5],
                    'riesgo_bajo' => [2.6, 10.0],
                    'riesgo_medio' => [10.1, 17.5],
                    'riesgo_alto' => [17.6, 27.5],
                    'riesgo_muy_alto' => [27.6, 100.0]
                ]
            ];

        // Baremos de dimensiones Forma A (Tabla 29)
        $baremosDimensionesA = [
            'caracteristicas_liderazgo' => [
                'sin_riesgo' => [0.0, 3.8],
                'riesgo_bajo' => [3.9, 15.4],
                'riesgo_medio' => [15.5, 30.8],
                'riesgo_alto' => [30.9, 46.2],
                'riesgo_muy_alto' => [46.3, 100.0]
            ],
            'relaciones_sociales' => [
                'sin_riesgo' => [0.0, 5.4],
                'riesgo_bajo' => [5.5, 16.1],
                'riesgo_medio' => [16.2, 25.0],
                'riesgo_alto' => [25.1, 37.5],
                'riesgo_muy_alto' => [37.6, 100.0]
            ],
            'retroalimentacion' => [
                'sin_riesgo' => [0.0, 10.0],
                'riesgo_bajo' => [10.1, 25.0],
                'riesgo_medio' => [25.1, 40.0],
                'riesgo_alto' => [40.1, 55.0],
                'riesgo_muy_alto' => [55.1, 100.0]
            ],
            'relacion_colaboradores' => [
                'sin_riesgo' => [0.0, 13.9],
                'riesgo_bajo' => [14.0, 25.0],
                'riesgo_medio' => [25.1, 33.3],
                'riesgo_alto' => [33.4, 47.2],
                'riesgo_muy_alto' => [47.3, 100.0]
            ],
            'claridad_rol' => [
                'sin_riesgo' => [0.0, 0.9],
                'riesgo_bajo' => [1.0, 10.7],
                'riesgo_medio' => [10.8, 21.4],
                'riesgo_alto' => [21.5, 39.3],
                'riesgo_muy_alto' => [39.4, 100.0]
            ],
            'capacitacion' => [
                'sin_riesgo' => [0.0, 0.9],
                'riesgo_bajo' => [1.0, 16.7],
                'riesgo_medio' => [16.8, 33.3],
                'riesgo_alto' => [33.4, 50.0],
                'riesgo_muy_alto' => [50.1, 100.0]
            ],
            'participacion_cambio' => [
                'sin_riesgo' => [0.0, 12.5],
                'riesgo_bajo' => [12.6, 25.0],
                'riesgo_medio' => [25.1, 37.5],
                'riesgo_alto' => [37.6, 50.0],
                'riesgo_muy_alto' => [50.1, 100.0]
            ],
            'oportunidades_desarrollo' => [
                'sin_riesgo' => [0.0, 0.9],
                'riesgo_bajo' => [1.0, 6.3],
                'riesgo_medio' => [6.4, 18.8],
                'riesgo_alto' => [18.9, 31.3],
                'riesgo_muy_alto' => [31.4, 100.0]
            ],
            'control_autonomia' => [
                'sin_riesgo' => [0.0, 8.3],
                'riesgo_bajo' => [8.4, 25.0],
                'riesgo_medio' => [25.1, 41.7],
                'riesgo_alto' => [41.8, 58.3],
                'riesgo_muy_alto' => [58.4, 100.0]
            ],
            'demandas_ambientales' => [
                'sin_riesgo' => [0.0, 14.6],
                'riesgo_bajo' => [14.7, 22.9],
                'riesgo_medio' => [23.0, 31.3],
                'riesgo_alto' => [31.4, 39.6],
                'riesgo_muy_alto' => [39.7, 100.0]
            ],
            'demandas_emocionales' => [
                'sin_riesgo' => [0.0, 16.7],
                'riesgo_bajo' => [16.8, 25.0],
                'riesgo_medio' => [25.1, 33.3],
                'riesgo_alto' => [33.4, 47.2],
                'riesgo_muy_alto' => [47.3, 100.0]
            ],
            'demandas_cuantitativas' => [
                'sin_riesgo' => [0.0, 25.0],
                'riesgo_bajo' => [25.1, 33.3],
                'riesgo_medio' => [33.4, 45.8],
                'riesgo_alto' => [45.9, 54.2],
                'riesgo_muy_alto' => [54.3, 100.0]
            ],
            'influencia_entorno' => [
                'sin_riesgo' => [0.0, 18.8],
                'riesgo_bajo' => [18.9, 31.3],
                'riesgo_medio' => [31.4, 43.8],
                'riesgo_alto' => [43.9, 50.0],
                'riesgo_muy_alto' => [50.1, 100.0]
            ],
            'exigencias_responsabilidad' => [
                'sin_riesgo' => [0.0, 37.5],
                'riesgo_bajo' => [37.6, 54.2],
                'riesgo_medio' => [54.3, 66.7],
                'riesgo_alto' => [66.8, 79.2],
                'riesgo_muy_alto' => [79.3, 100.0]
            ],
            'demandas_carga_mental' => [
                'sin_riesgo' => [0.0, 60.0],
                'riesgo_bajo' => [60.1, 70.0],
                'riesgo_medio' => [70.1, 80.0],
                'riesgo_alto' => [80.1, 90.0],
                'riesgo_muy_alto' => [90.1, 100.0]
            ],
            'consistencia_rol' => [
                'sin_riesgo' => [0.0, 15.0],
                'riesgo_bajo' => [15.1, 25.0],
                'riesgo_medio' => [25.1, 35.0],
                'riesgo_alto' => [35.1, 45.0],
                'riesgo_muy_alto' => [45.1, 100.0]
            ],
            'demandas_jornada' => [
                'sin_riesgo' => [0.0, 8.3],
                'riesgo_bajo' => [8.4, 25.0],
                'riesgo_medio' => [25.1, 33.3],
                'riesgo_alto' => [33.4, 50.0],
                'riesgo_muy_alto' => [50.1, 100.0]
            ],
            'recompensas_pertenencia' => [
                'sin_riesgo' => [0.0, 0.9],
                'riesgo_bajo' => [1.0, 5.0],
                'riesgo_medio' => [5.1, 10.0],
                'riesgo_alto' => [10.1, 20.0],
                'riesgo_muy_alto' => [20.1, 100.0]
            ],
            'reconocimiento_compensacion' => [
                'sin_riesgo' => [0.0, 4.2],
                'riesgo_bajo' => [4.3, 16.7],
                'riesgo_medio' => [16.8, 25.0],
                'riesgo_alto' => [25.1, 37.5],
                'riesgo_muy_alto' => [37.6, 100.0]
            ],
            'reconocimiento' => [
                'sin_riesgo' => [0.0, 0.9],
                'riesgo_bajo' => [1.0, 6.8],
                'riesgo_medio' => [6.9, 13.6],
                'riesgo_alto' => [13.7, 22.7],
                'riesgo_muy_alto' => [22.8, 100.0]
            ]
        ];

        // Baremos de dimensiones Forma B (Tabla 30) - Solo 16 dimensiones
        $baremosDimensionesB = [
            'caracteristicas_liderazgo' => [
                'sin_riesgo' => [0.0, 3.8],
                'riesgo_bajo' => [3.9, 13.5],
                'riesgo_medio' => [13.6, 25.0],
                'riesgo_alto' => [25.1, 38.5],
                'riesgo_muy_alto' => [38.6, 100.0]
            ],
            'relaciones_sociales' => [
                'sin_riesgo' => [0.0, 6.3],
                'riesgo_bajo' => [6.4, 14.6],
                'riesgo_medio' => [14.7, 27.1],
                'riesgo_alto' => [27.2, 37.5],
                'riesgo_muy_alto' => [37.6, 100.0]
            ],
            'retroalimentacion' => [
                'sin_riesgo' => [0.0, 5.0],
                'riesgo_bajo' => [5.1, 20.0],
                'riesgo_medio' => [20.1, 30.0],
                'riesgo_alto' => [30.1, 50.0],
                'riesgo_muy_alto' => [50.1, 100.0]
            ],
            'relacion_colaboradores' => [
                'sin_riesgo' => [0.0, 0.9],
                'riesgo_bajo' => [1.0, 5.0],
                'riesgo_medio' => [5.1, 15.0],
                'riesgo_alto' => [15.1, 30.0],
                'riesgo_muy_alto' => [30.1, 100.0]
            ],
            'claridad_rol' => [
                'sin_riesgo' => [0.0, 0.9],
                'riesgo_bajo' => [1.0, 5.0],
                'riesgo_medio' => [5.1, 15.0],
                'riesgo_alto' => [15.1, 30.0],
                'riesgo_muy_alto' => [30.1, 100.0]
            ],
            'capacitacion' => [
                'sin_riesgo' => [0.0, 0.9],
                'riesgo_bajo' => [1.0, 16.7],
                'riesgo_medio' => [16.8, 25.0],
                'riesgo_alto' => [25.1, 50.0],
                'riesgo_muy_alto' => [50.1, 100.0]
            ],
            'participacion_cambio' => [
                'sin_riesgo' => [0.0, 16.7],
                'riesgo_bajo' => [16.8, 25.0],
                'riesgo_medio' => [25.1, 33.3],
                'riesgo_alto' => [33.4, 50.0],
                'riesgo_muy_alto' => [50.1, 100.0]
            ],
            'oportunidades_desarrollo' => [
                'sin_riesgo' => [0.0, 6.3],
                'riesgo_bajo' => [6.4, 12.5],
                'riesgo_medio' => [12.6, 18.8],
                'riesgo_alto' => [18.9, 31.3],
                'riesgo_muy_alto' => [31.4, 100.0]
            ],
            'control_autonomia' => [
                'sin_riesgo' => [0.0, 15.0],
                'riesgo_bajo' => [15.1, 25.0],
                'riesgo_medio' => [25.1, 35.0],
                'riesgo_alto' => [35.1, 45.0],
                'riesgo_muy_alto' => [45.1, 100.0]
            ],
            'demandas_ambientales' => [
                'sin_riesgo' => [0.0, 18.8],
                'riesgo_bajo' => [18.9, 25.0],
                'riesgo_medio' => [25.1, 31.3],
                'riesgo_alto' => [31.4, 39.6],
                'riesgo_muy_alto' => [39.7, 100.0]
            ],
            'demandas_emocionales' => [
                'sin_riesgo' => [0.0, 19.4],
                'riesgo_bajo' => [19.5, 27.8],
                'riesgo_medio' => [27.9, 38.9],
                'riesgo_alto' => [39.0, 50.0],
                'riesgo_muy_alto' => [50.1, 100.0]
            ],
            'demandas_cuantitativas' => [
                'sin_riesgo' => [0.0, 16.7],
                'riesgo_bajo' => [16.8, 33.3],
                'riesgo_medio' => [33.4, 41.7],
                'riesgo_alto' => [41.8, 50.0],
                'riesgo_muy_alto' => [50.1, 100.0]
            ],
            'influencia_entorno' => [
                'sin_riesgo' => [0.0, 25.0],
                'riesgo_bajo' => [25.1, 37.5],
                'riesgo_medio' => [37.6, 43.8],
                'riesgo_alto' => [43.9, 50.0],
                'riesgo_muy_alto' => [50.1, 100.0]
            ],
            'demandas_carga_mental' => [
                'sin_riesgo' => [0.0, 50.0],
                'riesgo_bajo' => [50.1, 65.0],
                'riesgo_medio' => [65.1, 75.0],
                'riesgo_alto' => [75.1, 85.0],
                'riesgo_muy_alto' => [85.1, 100.0]
            ],
            'demandas_jornada' => [
                'sin_riesgo' => [0.0, 25.0],
                'riesgo_bajo' => [25.1, 37.5],
                'riesgo_medio' => [37.6, 45.8],
                'riesgo_alto' => [45.9, 50.0],
                'riesgo_muy_alto' => [50.1, 100.0]
            ],
            'reconocimiento' => [
                'sin_riesgo' => [0.0, 0.9],
                'riesgo_bajo' => [1.0, 16.7],
                'riesgo_medio' => [16.8, 25.0],
                'riesgo_alto' => [25.1, 50.0],
                'riesgo_muy_alto' => [50.1, 100.0]
            ]
        ];

        // Seleccionar baremos de dimensiones según forma mayoritaria
        $baremosDimensiones = $formaType === 'A' ? $baremosDimensionesA : $baremosDimensionesB;

        // BAREMOS EXTRALABORAL (Tabla 18 - usamos baremo de auxiliares como general)
        $baremosExtralaboral = [
            'tiempo_fuera' => [
                'sin_riesgo' => [0.0, 6.3],
                'riesgo_bajo' => [6.4, 25.0],
                'riesgo_medio' => [25.1, 37.5],
                'riesgo_alto' => [37.6, 50.0],
                'riesgo_muy_alto' => [50.1, 100.0]
            ],
            'relaciones_familiares' => [
                'sin_riesgo' => [0.0, 8.3],
                'riesgo_bajo' => [8.4, 25.0],
                'riesgo_medio' => [25.1, 33.3],
                'riesgo_alto' => [33.4, 50.0],
                'riesgo_muy_alto' => [50.1, 100.0]
            ],
            'comunicacion' => [
                'sin_riesgo' => [0.0, 5.0],
                'riesgo_bajo' => [5.1, 15.0],
                'riesgo_medio' => [15.1, 25.0],
                'riesgo_alto' => [25.1, 35.0],
                'riesgo_muy_alto' => [35.1, 100.0]
            ],
            'situacion_economica' => [
                'sin_riesgo' => [0.0, 16.7],
                'riesgo_bajo' => [16.8, 25.0],
                'riesgo_medio' => [25.1, 41.7],
                'riesgo_alto' => [41.8, 50.0],
                'riesgo_muy_alto' => [50.1, 100.0]
            ],
            'caracteristicas_vivienda' => [
                'sin_riesgo' => [0.0, 5.6],
                'riesgo_bajo' => [5.7, 11.1],
                'riesgo_medio' => [11.2, 16.7],
                'riesgo_alto' => [16.8, 27.8],
                'riesgo_muy_alto' => [27.9, 100.0]
            ],
            'influencia_entorno' => [
                'sin_riesgo' => [0.0, 0.9],
                'riesgo_bajo' => [1.0, 16.7],
                'riesgo_medio' => [16.8, 25.0],
                'riesgo_alto' => [25.1, 41.7],
                'riesgo_muy_alto' => [41.8, 100.0]
            ],
            'desplazamiento' => [
                'sin_riesgo' => [0.0, 0.9],
                'riesgo_bajo' => [1.0, 12.5],
                'riesgo_medio' => [12.6, 25.0],
                'riesgo_alto' => [25.1, 43.8],
                'riesgo_muy_alto' => [43.9, 100.0]
            ],
            'total' => [
                'sin_riesgo' => [0.0, 12.9],
                'riesgo_bajo' => [13.0, 17.7],
                'riesgo_medio' => [17.8, 24.2],
                'riesgo_alto' => [24.3, 32.3],
                'riesgo_muy_alto' => [32.4, 100.0]
            ]
        ];

        // BAREMO ESTRÉS (Tabla 6 - usamos baremo de auxiliares como general)
        $baremoEstres = [
            'muy_bajo' => [0.0, 6.5],
            'bajo' => [6.6, 11.8],
            'medio' => [11.9, 17.0],
            'alto' => [17.1, 23.4],
            'muy_alto' => [23.5, 100.0]
        ];

        // Calcular niveles basados en PROMEDIOS + BAREMOS
        $data = [
            // INTRALABORAL
            'intralaboral_total' => $getAverageLevel(
                array_column($results, 'intralaboral_total_puntaje'),
                $baremoIntralaboralTotal
            ),
            'dom_liderazgo' => $getAverageLevel(
                array_column($results, 'dom_liderazgo_puntaje'),
                $baremoDominios['liderazgo']
            ),
            'dom_control' => $getAverageLevel(
                array_column($results, 'dom_control_puntaje'),
                $baremoDominios['control']
            ),
            'dom_demandas' => $getAverageLevel(
                array_column($results, 'dom_demandas_puntaje'),
                $baremoDominios['demandas']
            ),
            'dom_recompensas' => $getAverageLevel(
                array_column($results, 'dom_recompensas_puntaje'),
                $baremoDominios['recompensas']
            ),

            // DIMENSIONES INTRALABORAL
            'dim_caracteristicas_liderazgo' => $getAverageLevel(
                array_column($results, 'dim_caracteristicas_liderazgo_puntaje'),
                $baremosDimensiones['caracteristicas_liderazgo']
            ),
            'dim_relaciones_sociales' => $getAverageLevel(
                array_column($results, 'dim_relaciones_sociales_puntaje'),
                $baremosDimensiones['relaciones_sociales']
            ),
            'dim_retroalimentacion' => $getAverageLevel(
                array_column($results, 'dim_retroalimentacion_puntaje'),
                $baremosDimensiones['retroalimentacion']
            ),
            'dim_relacion_colaboradores' => $getAverageLevel(
                array_column($results, 'dim_relacion_colaboradores_puntaje'),
                $baremosDimensiones['relacion_colaboradores']
            ),
            'dim_claridad_rol' => $getAverageLevel(
                array_column($results, 'dim_claridad_rol_puntaje'),
                $baremosDimensiones['claridad_rol']
            ),
            'dim_capacitacion' => $getAverageLevel(
                array_column($results, 'dim_capacitacion_puntaje'),
                $baremosDimensiones['capacitacion']
            ),
            'dim_participacion_cambio' => $getAverageLevel(
                array_column($results, 'dim_participacion_cambio_puntaje'),
                $baremosDimensiones['participacion_cambio']
            ),
            'dim_oportunidades_desarrollo' => $getAverageLevel(
                array_column($results, 'dim_oportunidades_desarrollo_puntaje'),
                $baremosDimensiones['oportunidades_desarrollo']
            ),
            'dim_control_autonomia' => $getAverageLevel(
                array_column($results, 'dim_control_autonomia_puntaje'),
                $baremosDimensiones['control_autonomia']
            ),
            'dim_demandas_ambientales' => $getAverageLevel(
                array_column($results, 'dim_demandas_ambientales_puntaje'),
                $baremosDimensiones['demandas_ambientales']
            ),
            'dim_demandas_emocionales' => $getAverageLevel(
                array_column($results, 'dim_demandas_emocionales_puntaje'),
                $baremosDimensiones['demandas_emocionales']
            ),
            'dim_demandas_cuantitativas' => $getAverageLevel(
                array_column($results, 'dim_demandas_cuantitativas_puntaje'),
                $baremosDimensiones['demandas_cuantitativas']
            ),
            'dim_influencia_entorno' => $getAverageLevel(
                array_column($results, 'dim_influencia_entorno_puntaje'),
                $baremosDimensiones['influencia_entorno']
            ),
            'dim_exigencias_responsabilidad' => $getAverageLevel(
                array_column($results, 'dim_exigencias_responsabilidad_puntaje'),
                $baremosDimensiones['exigencias_responsabilidad']
            ),
            'dim_demandas_carga_mental' => $getAverageLevel(
                array_column($results, 'dim_demandas_carga_mental_puntaje'),
                $baremosDimensiones['demandas_carga_mental']
            ),
            'dim_consistencia_rol' => $getAverageLevel(
                array_column($results, 'dim_consistencia_rol_puntaje'),
                $baremosDimensiones['consistencia_rol']
            ),
            'dim_demandas_jornada' => $getAverageLevel(
                array_column($results, 'dim_demandas_jornada_puntaje'),
                $baremosDimensiones['demandas_jornada']
            ),
            'dim_reconocimiento_compensacion' => $getAverageLevel(
                array_column($results, 'dim_reconocimiento_compensacion_puntaje'),
                $baremosDimensiones['reconocimiento_compensacion']
            ),
            'dim_reconocimiento' => $getAverageLevel(
                array_column($results, 'dim_reconocimiento_puntaje'),
                $baremosDimensiones['reconocimiento']
            ),

            // EXTRALABORAL
            'extralaboral_total' => $getAverageLevel(
                array_column($results, 'extralaboral_total_puntaje'),
                $baremosExtralaboral['total']
            ),
            'extralaboral_tiempo_fuera' => $getAverageLevel(
                array_column($results, 'extralaboral_tiempo_fuera_puntaje'),
                $baremosExtralaboral['tiempo_fuera']
            ),
            'extralaboral_relaciones_familiares' => $getAverageLevel(
                array_column($results, 'extralaboral_relaciones_familiares_puntaje'),
                $baremosExtralaboral['relaciones_familiares']
            ),
            'extralaboral_comunicacion' => $getAverageLevel(
                array_column($results, 'extralaboral_comunicacion_puntaje'),
                $baremosExtralaboral['comunicacion']
            ),
            'extralaboral_situacion_economica' => $getAverageLevel(
                array_column($results, 'extralaboral_situacion_economica_puntaje'),
                $baremosExtralaboral['situacion_economica']
            ),
            'extralaboral_caracteristicas_vivienda' => $getAverageLevel(
                array_column($results, 'extralaboral_caracteristicas_vivienda_puntaje'),
                $baremosExtralaboral['caracteristicas_vivienda']
            ),
            'extralaboral_influencia_entorno' => $getAverageLevel(
                array_column($results, 'extralaboral_influencia_entorno_puntaje'),
                $baremosExtralaboral['influencia_entorno']
            ),
            'extralaboral_desplazamiento' => $getAverageLevel(
                array_column($results, 'extralaboral_desplazamiento_puntaje'),
                $baremosExtralaboral['desplazamiento']
            ),

            // ESTRÉS - Mapear muy_bajo/bajo a sin_riesgo, medio a riesgo_medio, alto/muy_alto a riesgo_alto
            'estres_total' => (function() use ($results, $getAverageLevel, $baremoEstres) {
                $nivelEstres = $getAverageLevel(
                    array_column($results, 'estres_total_puntaje'),
                    $baremoEstres
                );

                // Mapear niveles de estrés a niveles de riesgo estándar
                $mapeoEstres = [
                    'muy_bajo' => 'sin_riesgo',
                    'bajo' => 'riesgo_bajo',
                    'medio' => 'riesgo_medio',
                    'alto' => 'riesgo_alto',
                    'muy_alto' => 'riesgo_muy_alto'
                ];

                return $mapeoEstres[$nivelEstres] ?? 'sin_riesgo';
            })()
        ];

        return $data;
    }

    /**
     * Mostrar gráficos de gauges globales para la batería de servicios
     */
    public function globalGauges($id)
    {
        // Verificar autenticación
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $service = $this->batteryServiceModel
            ->select('battery_services.*, companies.name as company_name')
            ->join('companies', 'companies.id = battery_services.company_id')
            ->find($id);

        if (!$service) {
            return redirect()->to('/battery-services')->with('error', 'Servicio no encontrado');
        }

        // Obtener todos los resultados calculados para este servicio
        $calculatedResultsModel = new \App\Models\CalculatedResultModel();
        $allResults = $calculatedResultsModel
            ->where('battery_service_id', $id)
            ->findAll();

        if (empty($allResults)) {
            return redirect()->to('/battery-services/' . $id)
                ->with('error', 'No hay resultados calculados para este servicio');
        }

        // Separar resultados por forma
        $resultsFormaA = array_filter($allResults, function($r) {
            return $r['intralaboral_form_type'] === 'A';
        });

        $resultsFormaB = array_filter($allResults, function($r) {
            return $r['intralaboral_form_type'] === 'B';
        });

        // Función helper para calcular promedio
        $calcularPromedio = function($puntajes) {
            $puntajes = array_filter($puntajes, function($v) { return $v !== null && $v !== ''; });
            if (empty($puntajes)) return 0;
            return array_sum($puntajes) / count($puntajes);
        };

        // Función helper para aplicar baremo
        $aplicarBaremo = function($promedio, $baremo) {
            foreach ($baremo as $nivel => $rango) {
                if ($promedio >= $rango[0] && $promedio <= $rango[1]) {
                    return $nivel;
                }
            }
            return 'sin_riesgo';
        };

        // Baremos Forma A
        $baremosA = [
            'intralaboral' => [
                'sin_riesgo' => [0.0, 19.7],
                'riesgo_bajo' => [19.8, 25.8],
                'riesgo_medio' => [25.9, 31.5],
                'riesgo_alto' => [31.6, 38.0],
                'riesgo_muy_alto' => [38.1, 100.0]
            ],
            'extralaboral' => [
                'sin_riesgo' => [0.0, 11.3],
                'riesgo_bajo' => [11.4, 16.9],
                'riesgo_medio' => [17.0, 22.6],
                'riesgo_alto' => [22.7, 29.0],
                'riesgo_muy_alto' => [29.1, 100.0]
            ],
            'estres' => [
                'sin_riesgo' => [0.0, 7.8],
                'riesgo_bajo' => [7.9, 12.6],
                'riesgo_medio' => [12.7, 17.7],
                'riesgo_alto' => [17.8, 25.0],
                'riesgo_muy_alto' => [25.1, 100.0]
            ],
            // Puntaje Total General Forma A (Tabla 34)
            'puntaje_total_general' => [
                'sin_riesgo' => [0.0, 18.8],
                'riesgo_bajo' => [18.9, 24.4],
                'riesgo_medio' => [24.5, 29.5],
                'riesgo_alto' => [29.6, 35.4],
                'riesgo_muy_alto' => [35.5, 100.0]
            ],
            // Dominios Intralaborales Forma A (Tabla 31)
            'dom_liderazgo' => [
                'sin_riesgo' => [0.0, 9.1],
                'riesgo_bajo' => [9.2, 17.7],
                'riesgo_medio' => [17.8, 25.6],
                'riesgo_alto' => [25.7, 34.8],
                'riesgo_muy_alto' => [34.9, 100.0]
            ],
            'dom_control' => [
                'sin_riesgo' => [0.0, 10.7],
                'riesgo_bajo' => [10.8, 19.0],
                'riesgo_medio' => [19.1, 29.8],
                'riesgo_alto' => [29.9, 40.5],
                'riesgo_muy_alto' => [40.6, 100.0]
            ],
            'dom_demandas' => [
                'sin_riesgo' => [0.0, 28.5],
                'riesgo_bajo' => [28.6, 35.0],
                'riesgo_medio' => [35.1, 41.5],
                'riesgo_alto' => [41.6, 47.5],
                'riesgo_muy_alto' => [47.6, 100.0]
            ],
            'dom_recompensas' => [
                'sin_riesgo' => [0.0, 4.5],
                'riesgo_bajo' => [4.6, 11.4],
                'riesgo_medio' => [11.5, 20.5],
                'riesgo_alto' => [20.6, 29.5],
                'riesgo_muy_alto' => [29.6, 100.0]
            ],
            // Dimensiones Intralaborales Forma A (Tabla 29 - primeras 5)
            'dim_caracteristicas_liderazgo' => [
                'sin_riesgo' => [0.0, 3.8],
                'riesgo_bajo' => [3.9, 15.4],
                'riesgo_medio' => [15.5, 30.8],
                'riesgo_alto' => [30.9, 46.2],
                'riesgo_muy_alto' => [46.3, 100.0]
            ],
            'dim_relaciones_sociales' => [
                'sin_riesgo' => [0.0, 5.4],
                'riesgo_bajo' => [5.5, 16.1],
                'riesgo_medio' => [16.2, 25.0],
                'riesgo_alto' => [25.1, 37.5],
                'riesgo_muy_alto' => [37.6, 100.0]
            ],
            'dim_retroalimentacion' => [
                'sin_riesgo' => [0.0, 10.0],
                'riesgo_bajo' => [10.1, 25.0],
                'riesgo_medio' => [25.1, 40.0],
                'riesgo_alto' => [40.1, 55.0],
                'riesgo_muy_alto' => [55.1, 100.0]
            ],
            'dim_relacion_colaboradores' => [
                'sin_riesgo' => [0.0, 13.9],
                'riesgo_bajo' => [14.0, 25.0],
                'riesgo_medio' => [25.1, 33.3],
                'riesgo_alto' => [33.4, 47.2],
                'riesgo_muy_alto' => [47.3, 100.0]
            ],
            'dim_claridad_rol' => [
                'sin_riesgo' => [0.0, 0.9],
                'riesgo_bajo' => [1.0, 10.7],
                'riesgo_medio' => [10.8, 21.4],
                'riesgo_alto' => [21.5, 39.3],
                'riesgo_muy_alto' => [39.4, 100.0]
            ],
            'dim_capacitacion' => [
                'sin_riesgo' => [0.0, 0.9],
                'riesgo_bajo' => [1.0, 16.7],
                'riesgo_medio' => [16.8, 33.3],
                'riesgo_alto' => [33.4, 50.0],
                'riesgo_muy_alto' => [50.1, 100.0]
            ],
            'dim_participacion_cambio' => [
                'sin_riesgo' => [0.0, 12.5],
                'riesgo_bajo' => [12.6, 25.0],
                'riesgo_medio' => [25.1, 37.5],
                'riesgo_alto' => [37.6, 50.0],
                'riesgo_muy_alto' => [50.1, 100.0]
            ],
            'dim_oportunidades' => [
                'sin_riesgo' => [0.0, 0.9],
                'riesgo_bajo' => [1.0, 6.3],
                'riesgo_medio' => [6.4, 18.8],
                'riesgo_alto' => [18.9, 31.3],
                'riesgo_muy_alto' => [31.4, 100.0]
            ],
            'dim_control_autonomia' => [
                'sin_riesgo' => [0.0, 8.3],
                'riesgo_bajo' => [8.4, 25.0],
                'riesgo_medio' => [25.1, 41.7],
                'riesgo_alto' => [41.8, 58.3],
                'riesgo_muy_alto' => [58.4, 100.0]
            ],
            'dim_demandas_ambientales' => [
                'sin_riesgo' => [0.0, 14.6],
                'riesgo_bajo' => [14.7, 22.9],
                'riesgo_medio' => [23.0, 31.3],
                'riesgo_alto' => [31.4, 39.6],
                'riesgo_muy_alto' => [39.7, 100.0]
            ],
            'dim_demandas_emocionales' => [
                'sin_riesgo' => [0.0, 16.7],
                'riesgo_bajo' => [16.8, 25.0],
                'riesgo_medio' => [25.1, 33.3],
                'riesgo_alto' => [33.4, 47.2],
                'riesgo_muy_alto' => [47.3, 100.0]
            ],
            'dim_demandas_cuantitativas' => [
                'sin_riesgo' => [0.0, 25.0],
                'riesgo_bajo' => [25.1, 33.3],
                'riesgo_medio' => [33.4, 45.8],
                'riesgo_alto' => [45.9, 54.2],
                'riesgo_muy_alto' => [54.3, 100.0]
            ],
            'dim_influencia_trabajo' => [
                'sin_riesgo' => [0.0, 18.8],
                'riesgo_bajo' => [18.9, 31.3],
                'riesgo_medio' => [31.4, 43.8],
                'riesgo_alto' => [43.9, 50.0],
                'riesgo_muy_alto' => [50.1, 100.0]
            ],
            'dim_exigencias_responsabilidad' => [
                'sin_riesgo' => [0.0, 37.5],
                'riesgo_bajo' => [37.6, 54.2],
                'riesgo_medio' => [54.3, 66.7],
                'riesgo_alto' => [66.8, 79.2],
                'riesgo_muy_alto' => [79.3, 100.0]
            ],
            'dim_demandas_carga_mental' => [
                'sin_riesgo' => [0.0, 60.0],
                'riesgo_bajo' => [60.1, 70.0],
                'riesgo_medio' => [70.1, 80.0],
                'riesgo_alto' => [80.1, 90.0],
                'riesgo_muy_alto' => [90.1, 100.0]
            ],
            'dim_consistencia_rol' => [
                'sin_riesgo' => [0.0, 15.0],
                'riesgo_bajo' => [15.1, 25.0],
                'riesgo_medio' => [25.1, 35.0],
                'riesgo_alto' => [35.1, 45.0],
                'riesgo_muy_alto' => [45.1, 100.0]
            ],
            'dim_demandas_jornada' => [
                'sin_riesgo' => [0.0, 8.3],
                'riesgo_bajo' => [8.4, 25.0],
                'riesgo_medio' => [25.1, 33.3],
                'riesgo_alto' => [33.4, 50.0],
                'riesgo_muy_alto' => [50.1, 100.0]
            ],
            'dim_recompensas_pertenencia' => [
                'sin_riesgo' => [0.0, 0.9],
                'riesgo_bajo' => [1.0, 5.0],
                'riesgo_medio' => [5.1, 10.0],
                'riesgo_alto' => [10.1, 20.0],
                'riesgo_muy_alto' => [20.1, 100.0]
            ],
            'dim_reconocimiento_compensacion' => [
                'sin_riesgo' => [0.0, 4.2],
                'riesgo_bajo' => [4.3, 16.7],
                'riesgo_medio' => [16.8, 25.0],
                'riesgo_alto' => [25.1, 37.5],
                'riesgo_muy_alto' => [37.6, 100.0]
            ],
            // Dimensiones Extralaborales Forma A (Tabla 17)
            'dim_tiempo_fuera_trabajo' => [
                'sin_riesgo' => [0.0, 6.3],
                'riesgo_bajo' => [6.4, 25.0],
                'riesgo_medio' => [25.1, 37.5],
                'riesgo_alto' => [37.6, 50.0],
                'riesgo_muy_alto' => [50.1, 100.0]
            ],
            'dim_relaciones_familiares' => [
                'sin_riesgo' => [0.0, 8.3],
                'riesgo_bajo' => [8.4, 25.0],
                'riesgo_medio' => [25.1, 33.3],
                'riesgo_alto' => [33.4, 50.0],
                'riesgo_muy_alto' => [50.1, 100.0]
            ],
            'dim_comunicacion_relaciones' => [
                'sin_riesgo' => [0.0, 0.9],
                'riesgo_bajo' => [1.0, 10.0],
                'riesgo_medio' => [10.1, 20.0],
                'riesgo_alto' => [20.1, 30.0],
                'riesgo_muy_alto' => [30.1, 100.0]
            ],
            'dim_situacion_economica' => [
                'sin_riesgo' => [0.0, 8.3],
                'riesgo_bajo' => [8.4, 25.0],
                'riesgo_medio' => [25.1, 33.3],
                'riesgo_alto' => [33.4, 50.0],
                'riesgo_muy_alto' => [50.1, 100.0]
            ],
            'dim_caracteristicas_vivienda' => [
                'sin_riesgo' => [0.0, 5.6],
                'riesgo_bajo' => [5.7, 11.1],
                'riesgo_medio' => [11.2, 13.9],
                'riesgo_alto' => [14.0, 22.2],
                'riesgo_muy_alto' => [22.3, 100.0]
            ],
            'dim_influencia_entorno' => [
                'sin_riesgo' => [0.0, 8.3],
                'riesgo_bajo' => [8.4, 16.7],
                'riesgo_medio' => [16.8, 25.0],
                'riesgo_alto' => [25.1, 41.7],
                'riesgo_muy_alto' => [41.8, 100.0]
            ],
            'dim_desplazamiento_vivienda' => [
                'sin_riesgo' => [0.0, 0.9],
                'riesgo_bajo' => [1.0, 12.5],
                'riesgo_medio' => [12.6, 25.0],
                'riesgo_alto' => [25.1, 43.8],
                'riesgo_muy_alto' => [43.9, 100.0]
            ]
        ];

        // Baremos Forma B
        $baremosB = [
            'intralaboral' => [
                'sin_riesgo' => [0.0, 20.6],
                'riesgo_bajo' => [20.7, 26.0],
                'riesgo_medio' => [26.1, 31.2],
                'riesgo_alto' => [31.3, 38.7],
                'riesgo_muy_alto' => [38.8, 100.0]
            ],
            'extralaboral' => [
                'sin_riesgo' => [0.0, 12.9],
                'riesgo_bajo' => [13.0, 17.7],
                'riesgo_medio' => [17.8, 24.2],
                'riesgo_alto' => [24.3, 32.3],
                'riesgo_muy_alto' => [32.4, 100.0]
            ],
            'estres' => [
                'sin_riesgo' => [0.0, 6.5],
                'riesgo_bajo' => [6.6, 11.8],
                'riesgo_medio' => [11.9, 17.0],
                'riesgo_alto' => [17.1, 23.4],
                'riesgo_muy_alto' => [23.5, 100.0]
            ],
            // Puntaje Total General Forma B (Tabla 34)
            'puntaje_total_general' => [
                'sin_riesgo' => [0.0, 19.9],
                'riesgo_bajo' => [20.0, 24.8],
                'riesgo_medio' => [24.9, 29.5],
                'riesgo_alto' => [29.6, 35.4],
                'riesgo_muy_alto' => [35.5, 100.0]
            ],
            // Dominios Intralaborales Forma B (Tabla 32)
            'dom_liderazgo' => [
                'sin_riesgo' => [0.0, 8.3],
                'riesgo_bajo' => [8.4, 17.5],
                'riesgo_medio' => [17.6, 26.7],
                'riesgo_alto' => [26.8, 38.3],
                'riesgo_muy_alto' => [38.4, 100.0]
            ],
            'dom_control' => [
                'sin_riesgo' => [0.0, 19.4],
                'riesgo_bajo' => [19.5, 26.4],
                'riesgo_medio' => [26.5, 34.7],
                'riesgo_alto' => [34.8, 43.1],
                'riesgo_muy_alto' => [43.2, 100.0]
            ],
            'dom_demandas' => [
                'sin_riesgo' => [0.0, 26.9],
                'riesgo_bajo' => [27.0, 33.3],
                'riesgo_medio' => [33.4, 37.8],
                'riesgo_alto' => [37.9, 44.2],
                'riesgo_muy_alto' => [44.3, 100.0]
            ],
            'dom_recompensas' => [
                'sin_riesgo' => [0.0, 2.5],
                'riesgo_bajo' => [2.6, 10.0],
                'riesgo_medio' => [10.1, 17.5],
                'riesgo_alto' => [17.6, 27.5],
                'riesgo_muy_alto' => [27.6, 100.0]
            ],
            // Dimensiones Intralaborales Forma B (Tabla 30) - Primeras 5
            'dim_caracteristicas_liderazgo' => [
                'sin_riesgo' => [0.0, 3.8],
                'riesgo_bajo' => [3.9, 13.5],
                'riesgo_medio' => [13.6, 25.0],
                'riesgo_alto' => [25.1, 38.5],
                'riesgo_muy_alto' => [38.6, 100.0]
            ],
            'dim_relaciones_sociales' => [
                'sin_riesgo' => [0.0, 6.3],
                'riesgo_bajo' => [6.4, 14.6],
                'riesgo_medio' => [14.7, 27.1],
                'riesgo_alto' => [27.2, 37.5],
                'riesgo_muy_alto' => [37.6, 100.0]
            ],
            'dim_retroalimentacion' => [
                'sin_riesgo' => [0.0, 5.0],
                'riesgo_bajo' => [5.1, 20.0],
                'riesgo_medio' => [20.1, 30.0],
                'riesgo_alto' => [30.1, 50.0],
                'riesgo_muy_alto' => [50.1, 100.0]
            ],
            'dim_claridad_rol' => [
                'sin_riesgo' => [0.0, 0.9],
                'riesgo_bajo' => [1.0, 5.0],
                'riesgo_medio' => [5.1, 15.0],
                'riesgo_alto' => [15.1, 30.0],
                'riesgo_muy_alto' => [30.1, 100.0]
            ],
            'dim_capacitacion' => [
                'sin_riesgo' => [0.0, 0.9],
                'riesgo_bajo' => [1.0, 16.7],
                'riesgo_medio' => [16.8, 25.0],
                'riesgo_alto' => [25.1, 50.0],
                'riesgo_muy_alto' => [50.1, 100.0]
            ],
            // Dimensiones Intralaborales Forma B (Tabla 30) - Dimensiones 6-10
            'dim_participacion_cambio' => [
                'sin_riesgo' => [0.0, 16.7],
                'riesgo_bajo' => [16.8, 33.3],
                'riesgo_medio' => [33.4, 41.7],
                'riesgo_alto' => [41.8, 58.3],
                'riesgo_muy_alto' => [58.4, 100.0]
            ],
            'dim_oportunidades' => [
                'sin_riesgo' => [0.0, 12.5],
                'riesgo_bajo' => [12.6, 25.0],
                'riesgo_medio' => [25.1, 37.5],
                'riesgo_alto' => [37.6, 56.3],
                'riesgo_muy_alto' => [56.4, 100.0]
            ],
            'dim_control_autonomia' => [
                'sin_riesgo' => [0.0, 33.3],
                'riesgo_bajo' => [33.4, 50.0],
                'riesgo_medio' => [50.1, 66.7],
                'riesgo_alto' => [66.8, 75.0],
                'riesgo_muy_alto' => [75.1, 100.0]
            ],
            'dim_demandas_ambientales' => [
                'sin_riesgo' => [0.0, 22.9],
                'riesgo_bajo' => [23.0, 31.3],
                'riesgo_medio' => [31.4, 39.6],
                'riesgo_alto' => [39.7, 47.9],
                'riesgo_muy_alto' => [48.0, 100.0]
            ],
            'dim_demandas_emocionales' => [
                'sin_riesgo' => [0.0, 19.4],
                'riesgo_bajo' => [19.5, 27.8],
                'riesgo_medio' => [27.9, 38.9],
                'riesgo_alto' => [39.0, 47.2],
                'riesgo_muy_alto' => [47.3, 100.0]
            ],
            // Dimensiones Intralaborales Forma B (Tabla 30) - Dimensiones 11-16
            'dim_demandas_cuantitativas' => [
                'sin_riesgo' => [0.0, 16.7],
                'riesgo_bajo' => [16.8, 33.3],
                'riesgo_medio' => [33.4, 41.7],
                'riesgo_alto' => [41.8, 50.0],
                'riesgo_muy_alto' => [50.1, 100.0]
            ],
            'dim_influencia_trabajo' => [
                'sin_riesgo' => [0.0, 12.5],
                'riesgo_bajo' => [12.6, 25.0],
                'riesgo_medio' => [25.1, 31.3],
                'riesgo_alto' => [31.4, 50.0],
                'riesgo_muy_alto' => [50.1, 100.0]
            ],
            'dim_demandas_carga_mental' => [
                'sin_riesgo' => [0.0, 50.0],
                'riesgo_bajo' => [50.1, 65.0],
                'riesgo_medio' => [65.1, 75.0],
                'riesgo_alto' => [75.1, 85.0],
                'riesgo_muy_alto' => [85.1, 100.0]
            ],
            'dim_demandas_jornada' => [
                'sin_riesgo' => [0.0, 25.0],
                'riesgo_bajo' => [25.1, 37.5],
                'riesgo_medio' => [37.6, 45.8],
                'riesgo_alto' => [45.9, 58.3],
                'riesgo_muy_alto' => [58.4, 100.0]
            ],
            'dim_recompensas_pertenencia' => [
                'sin_riesgo' => [0.0, 0.9],
                'riesgo_bajo' => [1.0, 6.3],
                'riesgo_medio' => [6.4, 12.5],
                'riesgo_alto' => [12.6, 18.8],
                'riesgo_muy_alto' => [18.9, 100.0]
            ],
            'dim_reconocimiento_compensacion' => [
                'sin_riesgo' => [0.0, 0.9],
                'riesgo_bajo' => [1.0, 12.5],
                'riesgo_medio' => [12.6, 25.0],
                'riesgo_alto' => [25.1, 37.5],
                'riesgo_muy_alto' => [37.6, 100.0]
            ],
            // Dimensiones Extralaborales Forma B (Tabla 18)
            'dim_tiempo_fuera_trabajo' => [
                'sin_riesgo' => [0.0, 6.3],
                'riesgo_bajo' => [6.4, 25.0],
                'riesgo_medio' => [25.1, 37.5],
                'riesgo_alto' => [37.6, 50.0],
                'riesgo_muy_alto' => [50.1, 100.0]
            ],
            'dim_relaciones_familiares' => [
                'sin_riesgo' => [0.0, 8.3],
                'riesgo_bajo' => [8.4, 25.0],
                'riesgo_medio' => [25.1, 33.3],
                'riesgo_alto' => [33.4, 50.0],
                'riesgo_muy_alto' => [50.1, 100.0]
            ],
            'dim_comunicacion_relaciones' => [
                'sin_riesgo' => [0.0, 5.0],
                'riesgo_bajo' => [5.1, 15.0],
                'riesgo_medio' => [15.1, 25.0],
                'riesgo_alto' => [25.1, 35.0],
                'riesgo_muy_alto' => [35.1, 100.0]
            ],
            'dim_situacion_economica' => [
                'sin_riesgo' => [0.0, 16.7],
                'riesgo_bajo' => [16.8, 25.0],
                'riesgo_medio' => [25.1, 41.7],
                'riesgo_alto' => [41.8, 50.0],
                'riesgo_muy_alto' => [50.1, 100.0]
            ],
            'dim_caracteristicas_vivienda' => [
                'sin_riesgo' => [0.0, 5.6],
                'riesgo_bajo' => [5.7, 11.1],
                'riesgo_medio' => [11.2, 16.7],
                'riesgo_alto' => [16.8, 27.8],
                'riesgo_muy_alto' => [27.9, 100.0]
            ],
            'dim_influencia_entorno' => [
                'sin_riesgo' => [0.0, 0.9],
                'riesgo_bajo' => [1.0, 16.7],
                'riesgo_medio' => [16.8, 25.0],
                'riesgo_alto' => [25.1, 41.7],
                'riesgo_muy_alto' => [41.8, 100.0]
            ],
            'dim_desplazamiento_vivienda' => [
                'sin_riesgo' => [0.0, 0.9],
                'riesgo_bajo' => [1.0, 12.5],
                'riesgo_medio' => [12.6, 25.0],
                'riesgo_alto' => [25.1, 43.8],
                'riesgo_muy_alto' => [43.9, 100.0]
            ]
        ];

        // Calcular datos globales Forma A
        $globalDataFormaA = [
            'intralaboral_promedio' => 0,
            'intralaboral_nivel' => 'sin_riesgo',
            'extralaboral_promedio' => 0,
            'extralaboral_nivel' => 'sin_riesgo',
            'estres_promedio' => 0,
            'estres_nivel' => 'sin_riesgo',
            'dom_liderazgo_promedio' => 0,
            'dom_liderazgo_nivel' => 'sin_riesgo',
            'dom_control_promedio' => 0,
            'dom_control_nivel' => 'sin_riesgo',
            'dom_demandas_promedio' => 0,
            'dom_demandas_nivel' => 'sin_riesgo',
            'dom_recompensas_promedio' => 0,
            'dom_recompensas_nivel' => 'sin_riesgo'
        ];

        if (!empty($resultsFormaA)) {
            $intralaPromedioA = $calcularPromedio(array_column($resultsFormaA, 'intralaboral_total_puntaje'));
            $extralPromedioA = $calcularPromedio(array_column($resultsFormaA, 'extralaboral_total_puntaje'));
            $estresPromedioA = $calcularPromedio(array_column($resultsFormaA, 'estres_total_puntaje'));

            // Puntaje Total General (promedio de intralaboral y extralaboral)
            $puntajeTotalGeneralA = ($intralaPromedioA + $extralPromedioA) / 2;

            // Dominios
            $domLiderazgoPromedioA = $calcularPromedio(array_column($resultsFormaA, 'dom_liderazgo_puntaje'));
            $domControlPromedioA = $calcularPromedio(array_column($resultsFormaA, 'dom_control_puntaje'));
            $domDemandasPromedioA = $calcularPromedio(array_column($resultsFormaA, 'dom_demandas_puntaje'));
            $domRecompensasPromedioA = $calcularPromedio(array_column($resultsFormaA, 'dom_recompensas_puntaje'));

            // Dimensiones Forma A (primeras 5)
            $dimCaracteristicasLiderazgoA = $calcularPromedio(array_column($resultsFormaA, 'dim_caracteristicas_liderazgo_puntaje'));
            $dimRelacionesSocialesA = $calcularPromedio(array_column($resultsFormaA, 'dim_relaciones_sociales_puntaje'));
            $dimRetroalimentacionA = $calcularPromedio(array_column($resultsFormaA, 'dim_retroalimentacion_puntaje'));
            $dimRelacionColaboradoresA = $calcularPromedio(array_column($resultsFormaA, 'dim_relacion_colaboradores_puntaje'));
            $dimClaridadRolA = $calcularPromedio(array_column($resultsFormaA, 'dim_claridad_rol_puntaje'));

            // Dimensiones Forma A (6-10)
            $dimCapacitacionA = $calcularPromedio(array_column($resultsFormaA, 'dim_capacitacion_puntaje'));
            $dimParticipacionCambioA = $calcularPromedio(array_column($resultsFormaA, 'dim_participacion_cambio_puntaje'));
            $dimOportunidadesA = $calcularPromedio(array_column($resultsFormaA, 'dim_oportunidades_puntaje'));
            $dimControlAutonomiaA = $calcularPromedio(array_column($resultsFormaA, 'dim_control_autonomia_puntaje'));
            $dimDemandasAmbientalesA = $calcularPromedio(array_column($resultsFormaA, 'dim_demandas_ambientales_puntaje'));

            // Dimensiones Forma A (11-15)
            $dimDemandasEmocionalesA = $calcularPromedio(array_column($resultsFormaA, 'dim_demandas_emocionales_puntaje'));
            $dimDemandasCuantitativasA = $calcularPromedio(array_column($resultsFormaA, 'dim_demandas_cuantitativas_puntaje'));
            $dimInfluenciaTrabajoA = $calcularPromedio(array_column($resultsFormaA, 'dim_influencia_trabajo_puntaje'));
            $dimExigenciasResponsabilidadA = $calcularPromedio(array_column($resultsFormaA, 'dim_exigencias_responsabilidad_puntaje'));
            $dimDemandasCargaMentalA = $calcularPromedio(array_column($resultsFormaA, 'dim_demandas_carga_mental_puntaje'));

            // Dimensiones Forma A (16-19)
            $dimConsistenciaRolA = $calcularPromedio(array_column($resultsFormaA, 'dim_consistencia_rol_puntaje'));
            $dimDemandasJornadaA = $calcularPromedio(array_column($resultsFormaA, 'dim_demandas_jornada_puntaje'));
            $dimRecompensasPertenenciaA = $calcularPromedio(array_column($resultsFormaA, 'dim_recompensas_pertenencia_puntaje'));
            $dimReconocimientoCompensacionA = $calcularPromedio(array_column($resultsFormaA, 'dim_reconocimiento_compensacion_puntaje'));

            // Dimensiones Extralaborales Forma A
            $dimTiempoFueraTrabajoA = $calcularPromedio(array_column($resultsFormaA, 'dim_tiempo_fuera_trabajo_puntaje'));
            $dimRelacionesFamiliaresA = $calcularPromedio(array_column($resultsFormaA, 'dim_relaciones_familiares_puntaje'));
            $dimComunicacionRelacionesA = $calcularPromedio(array_column($resultsFormaA, 'dim_comunicacion_relaciones_puntaje'));
            $dimSituacionEconomicaA = $calcularPromedio(array_column($resultsFormaA, 'dim_situacion_economica_puntaje'));
            $dimCaracteristicasViviendaA = $calcularPromedio(array_column($resultsFormaA, 'dim_caracteristicas_vivienda_puntaje'));
            $dimInfluenciaEntornoA = $calcularPromedio(array_column($resultsFormaA, 'dim_influencia_entorno_puntaje'));
            $dimDesplazamientoViviendaA = $calcularPromedio(array_column($resultsFormaA, 'dim_desplazamiento_vivienda_puntaje'));

            $globalDataFormaA = [
                // Puntaje Total General (Tabla 34)
                'puntaje_total_general_promedio' => $puntajeTotalGeneralA,
                'puntaje_total_general_nivel' => $aplicarBaremo($puntajeTotalGeneralA, $baremosA['puntaje_total_general']),
                'intralaboral_promedio' => $intralaPromedioA,
                'intralaboral_nivel' => $aplicarBaremo($intralaPromedioA, $baremosA['intralaboral']),
                'extralaboral_promedio' => $extralPromedioA,
                'extralaboral_nivel' => $aplicarBaremo($extralPromedioA, $baremosA['extralaboral']),
                'estres_promedio' => $estresPromedioA,
                'estres_nivel' => $aplicarBaremo($estresPromedioA, $baremosA['estres']),
                'dom_liderazgo_promedio' => $domLiderazgoPromedioA,
                'dom_liderazgo_nivel' => $aplicarBaremo($domLiderazgoPromedioA, $baremosA['dom_liderazgo']),
                'dom_control_promedio' => $domControlPromedioA,
                'dom_control_nivel' => $aplicarBaremo($domControlPromedioA, $baremosA['dom_control']),
                'dom_demandas_promedio' => $domDemandasPromedioA,
                'dom_demandas_nivel' => $aplicarBaremo($domDemandasPromedioA, $baremosA['dom_demandas']),
                'dom_recompensas_promedio' => $domRecompensasPromedioA,
                'dom_recompensas_nivel' => $aplicarBaremo($domRecompensasPromedioA, $baremosA['dom_recompensas']),
                // Dimensiones (primeras 5)
                'dim_caracteristicas_liderazgo_promedio' => $dimCaracteristicasLiderazgoA,
                'dim_caracteristicas_liderazgo_nivel' => $aplicarBaremo($dimCaracteristicasLiderazgoA, $baremosA['dim_caracteristicas_liderazgo']),
                'dim_relaciones_sociales_promedio' => $dimRelacionesSocialesA,
                'dim_relaciones_sociales_nivel' => $aplicarBaremo($dimRelacionesSocialesA, $baremosA['dim_relaciones_sociales']),
                'dim_retroalimentacion_promedio' => $dimRetroalimentacionA,
                'dim_retroalimentacion_nivel' => $aplicarBaremo($dimRetroalimentacionA, $baremosA['dim_retroalimentacion']),
                'dim_relacion_colaboradores_promedio' => $dimRelacionColaboradoresA,
                'dim_relacion_colaboradores_nivel' => $aplicarBaremo($dimRelacionColaboradoresA, $baremosA['dim_relacion_colaboradores']),
                'dim_claridad_rol_promedio' => $dimClaridadRolA,
                'dim_claridad_rol_nivel' => $aplicarBaremo($dimClaridadRolA, $baremosA['dim_claridad_rol']),
                // Dimensiones (6-10)
                'dim_capacitacion_promedio' => $dimCapacitacionA,
                'dim_capacitacion_nivel' => $aplicarBaremo($dimCapacitacionA, $baremosA['dim_capacitacion']),
                'dim_participacion_cambio_promedio' => $dimParticipacionCambioA,
                'dim_participacion_cambio_nivel' => $aplicarBaremo($dimParticipacionCambioA, $baremosA['dim_participacion_cambio']),
                'dim_oportunidades_promedio' => $dimOportunidadesA,
                'dim_oportunidades_nivel' => $aplicarBaremo($dimOportunidadesA, $baremosA['dim_oportunidades']),
                'dim_control_autonomia_promedio' => $dimControlAutonomiaA,
                'dim_control_autonomia_nivel' => $aplicarBaremo($dimControlAutonomiaA, $baremosA['dim_control_autonomia']),
                'dim_demandas_ambientales_promedio' => $dimDemandasAmbientalesA,
                'dim_demandas_ambientales_nivel' => $aplicarBaremo($dimDemandasAmbientalesA, $baremosA['dim_demandas_ambientales']),
                // Dimensiones (11-15)
                'dim_demandas_emocionales_promedio' => $dimDemandasEmocionalesA,
                'dim_demandas_emocionales_nivel' => $aplicarBaremo($dimDemandasEmocionalesA, $baremosA['dim_demandas_emocionales']),
                'dim_demandas_cuantitativas_promedio' => $dimDemandasCuantitativasA,
                'dim_demandas_cuantitativas_nivel' => $aplicarBaremo($dimDemandasCuantitativasA, $baremosA['dim_demandas_cuantitativas']),
                'dim_influencia_trabajo_promedio' => $dimInfluenciaTrabajoA,
                'dim_influencia_trabajo_nivel' => $aplicarBaremo($dimInfluenciaTrabajoA, $baremosA['dim_influencia_trabajo']),
                'dim_exigencias_responsabilidad_promedio' => $dimExigenciasResponsabilidadA,
                'dim_exigencias_responsabilidad_nivel' => $aplicarBaremo($dimExigenciasResponsabilidadA, $baremosA['dim_exigencias_responsabilidad']),
                'dim_demandas_carga_mental_promedio' => $dimDemandasCargaMentalA,
                'dim_demandas_carga_mental_nivel' => $aplicarBaremo($dimDemandasCargaMentalA, $baremosA['dim_demandas_carga_mental']),
                // Dimensiones (16-19)
                'dim_consistencia_rol_promedio' => $dimConsistenciaRolA,
                'dim_consistencia_rol_nivel' => $aplicarBaremo($dimConsistenciaRolA, $baremosA['dim_consistencia_rol']),
                'dim_demandas_jornada_promedio' => $dimDemandasJornadaA,
                'dim_demandas_jornada_nivel' => $aplicarBaremo($dimDemandasJornadaA, $baremosA['dim_demandas_jornada']),
                'dim_recompensas_pertenencia_promedio' => $dimRecompensasPertenenciaA,
                'dim_recompensas_pertenencia_nivel' => $aplicarBaremo($dimRecompensasPertenenciaA, $baremosA['dim_recompensas_pertenencia']),
                'dim_reconocimiento_compensacion_promedio' => $dimReconocimientoCompensacionA,
                'dim_reconocimiento_compensacion_nivel' => $aplicarBaremo($dimReconocimientoCompensacionA, $baremosA['dim_reconocimiento_compensacion']),
                // Dimensiones Extralaborales
                'dim_tiempo_fuera_trabajo_promedio' => $dimTiempoFueraTrabajoA,
                'dim_tiempo_fuera_trabajo_nivel' => $aplicarBaremo($dimTiempoFueraTrabajoA, $baremosA['dim_tiempo_fuera_trabajo']),
                'dim_relaciones_familiares_promedio' => $dimRelacionesFamiliaresA,
                'dim_relaciones_familiares_nivel' => $aplicarBaremo($dimRelacionesFamiliaresA, $baremosA['dim_relaciones_familiares']),
                'dim_comunicacion_relaciones_promedio' => $dimComunicacionRelacionesA,
                'dim_comunicacion_relaciones_nivel' => $aplicarBaremo($dimComunicacionRelacionesA, $baremosA['dim_comunicacion_relaciones']),
                'dim_situacion_economica_promedio' => $dimSituacionEconomicaA,
                'dim_situacion_economica_nivel' => $aplicarBaremo($dimSituacionEconomicaA, $baremosA['dim_situacion_economica']),
                'dim_caracteristicas_vivienda_promedio' => $dimCaracteristicasViviendaA,
                'dim_caracteristicas_vivienda_nivel' => $aplicarBaremo($dimCaracteristicasViviendaA, $baremosA['dim_caracteristicas_vivienda']),
                'dim_influencia_entorno_promedio' => $dimInfluenciaEntornoA,
                'dim_influencia_entorno_nivel' => $aplicarBaremo($dimInfluenciaEntornoA, $baremosA['dim_influencia_entorno']),
                'dim_desplazamiento_vivienda_promedio' => $dimDesplazamientoViviendaA,
                'dim_desplazamiento_vivienda_nivel' => $aplicarBaremo($dimDesplazamientoViviendaA, $baremosA['dim_desplazamiento_vivienda'])
            ];
        }

        // Calcular datos globales Forma B
        $globalDataFormaB = [
            'intralaboral_promedio' => 0,
            'intralaboral_nivel' => 'sin_riesgo',
            'extralaboral_promedio' => 0,
            'extralaboral_nivel' => 'sin_riesgo',
            'estres_promedio' => 0,
            'estres_nivel' => 'sin_riesgo',
            'dom_liderazgo_promedio' => 0,
            'dom_liderazgo_nivel' => 'sin_riesgo',
            'dom_control_promedio' => 0,
            'dom_control_nivel' => 'sin_riesgo',
            'dom_demandas_promedio' => 0,
            'dom_demandas_nivel' => 'sin_riesgo',
            'dom_recompensas_promedio' => 0,
            'dom_recompensas_nivel' => 'sin_riesgo'
        ];

        if (!empty($resultsFormaB)) {
            $intralaPromedioB = $calcularPromedio(array_column($resultsFormaB, 'intralaboral_total_puntaje'));
            $extralPromedioB = $calcularPromedio(array_column($resultsFormaB, 'extralaboral_total_puntaje'));
            $estresPromedioB = $calcularPromedio(array_column($resultsFormaB, 'estres_total_puntaje'));

            // Puntaje Total General (promedio de intralaboral y extralaboral)
            $puntajeTotalGeneralB = ($intralaPromedioB + $extralPromedioB) / 2;

            // Dominios
            $domLiderazgoPromedioB = $calcularPromedio(array_column($resultsFormaB, 'dom_liderazgo_puntaje'));
            $domControlPromedioB = $calcularPromedio(array_column($resultsFormaB, 'dom_control_puntaje'));
            $domDemandasPromedioB = $calcularPromedio(array_column($resultsFormaB, 'dom_demandas_puntaje'));
            $domRecompensasPromedioB = $calcularPromedio(array_column($resultsFormaB, 'dom_recompensas_puntaje'));

            // Dimensiones Forma B (primeras 5)
            $dimCaracteristicasLiderazgoB = $calcularPromedio(array_column($resultsFormaB, 'dim_caracteristicas_liderazgo_puntaje'));
            $dimRelacionesSocialesB = $calcularPromedio(array_column($resultsFormaB, 'dim_relaciones_sociales_puntaje'));
            $dimRetroalimentacionB = $calcularPromedio(array_column($resultsFormaB, 'dim_retroalimentacion_puntaje'));
            $dimClaridadRolB = $calcularPromedio(array_column($resultsFormaB, 'dim_claridad_rol_puntaje'));
            $dimCapacitacionB = $calcularPromedio(array_column($resultsFormaB, 'dim_capacitacion_puntaje'));

            // Dimensiones Forma B (6-10)
            $dimParticipacionCambioB = $calcularPromedio(array_column($resultsFormaB, 'dim_participacion_cambio_puntaje'));
            $dimOportunidadesB = $calcularPromedio(array_column($resultsFormaB, 'dim_oportunidades_puntaje'));
            $dimControlAutonomiaB = $calcularPromedio(array_column($resultsFormaB, 'dim_control_autonomia_puntaje'));
            $dimDemandasAmbientalesB = $calcularPromedio(array_column($resultsFormaB, 'dim_demandas_ambientales_puntaje'));
            $dimDemandasEmocionalesB = $calcularPromedio(array_column($resultsFormaB, 'dim_demandas_emocionales_puntaje'));

            // Dimensiones Forma B (11-16)
            $dimDemandasCuantitativasB = $calcularPromedio(array_column($resultsFormaB, 'dim_demandas_cuantitativas_puntaje'));
            $dimInfluenciaTrabajoB = $calcularPromedio(array_column($resultsFormaB, 'dim_influencia_trabajo_puntaje'));
            $dimDemandasCargaMentalB = $calcularPromedio(array_column($resultsFormaB, 'dim_demandas_carga_mental_puntaje'));
            $dimDemandasJornadaB = $calcularPromedio(array_column($resultsFormaB, 'dim_demandas_jornada_puntaje'));
            $dimRecompensasPertenenciaB = $calcularPromedio(array_column($resultsFormaB, 'dim_recompensas_pertenencia_puntaje'));
            $dimReconocimientoCompensacionB = $calcularPromedio(array_column($resultsFormaB, 'dim_reconocimiento_compensacion_puntaje'));

            // Dimensiones Extralaborales Forma B
            $dimTiempoFueraTrabajoB = $calcularPromedio(array_column($resultsFormaB, 'dim_tiempo_fuera_trabajo_puntaje'));
            $dimRelacionesFamiliaresB = $calcularPromedio(array_column($resultsFormaB, 'dim_relaciones_familiares_puntaje'));
            $dimComunicacionRelacionesB = $calcularPromedio(array_column($resultsFormaB, 'dim_comunicacion_relaciones_puntaje'));
            $dimSituacionEconomicaB = $calcularPromedio(array_column($resultsFormaB, 'dim_situacion_economica_puntaje'));
            $dimCaracteristicasViviendaB = $calcularPromedio(array_column($resultsFormaB, 'dim_caracteristicas_vivienda_puntaje'));
            $dimInfluenciaEntornoB = $calcularPromedio(array_column($resultsFormaB, 'dim_influencia_entorno_puntaje'));
            $dimDesplazamientoViviendaB = $calcularPromedio(array_column($resultsFormaB, 'dim_desplazamiento_vivienda_puntaje'));

            $globalDataFormaB = [
                // Puntaje Total General (Tabla 34)
                'puntaje_total_general_promedio' => $puntajeTotalGeneralB,
                'puntaje_total_general_nivel' => $aplicarBaremo($puntajeTotalGeneralB, $baremosB['puntaje_total_general']),
                'intralaboral_promedio' => $intralaPromedioB,
                'intralaboral_nivel' => $aplicarBaremo($intralaPromedioB, $baremosB['intralaboral']),
                'extralaboral_promedio' => $extralPromedioB,
                'extralaboral_nivel' => $aplicarBaremo($extralPromedioB, $baremosB['extralaboral']),
                'estres_promedio' => $estresPromedioB,
                'estres_nivel' => $aplicarBaremo($estresPromedioB, $baremosB['estres']),
                'dom_liderazgo_promedio' => $domLiderazgoPromedioB,
                'dom_liderazgo_nivel' => $aplicarBaremo($domLiderazgoPromedioB, $baremosB['dom_liderazgo']),
                'dom_control_promedio' => $domControlPromedioB,
                'dom_control_nivel' => $aplicarBaremo($domControlPromedioB, $baremosB['dom_control']),
                'dom_demandas_promedio' => $domDemandasPromedioB,
                'dom_demandas_nivel' => $aplicarBaremo($domDemandasPromedioB, $baremosB['dom_demandas']),
                'dom_recompensas_promedio' => $domRecompensasPromedioB,
                'dom_recompensas_nivel' => $aplicarBaremo($domRecompensasPromedioB, $baremosB['dom_recompensas']),
                // Dimensiones Forma B (primeras 5)
                'dim_caracteristicas_liderazgo_promedio' => $dimCaracteristicasLiderazgoB,
                'dim_caracteristicas_liderazgo_nivel' => $aplicarBaremo($dimCaracteristicasLiderazgoB, $baremosB['dim_caracteristicas_liderazgo']),
                'dim_relaciones_sociales_promedio' => $dimRelacionesSocialesB,
                'dim_relaciones_sociales_nivel' => $aplicarBaremo($dimRelacionesSocialesB, $baremosB['dim_relaciones_sociales']),
                'dim_retroalimentacion_promedio' => $dimRetroalimentacionB,
                'dim_retroalimentacion_nivel' => $aplicarBaremo($dimRetroalimentacionB, $baremosB['dim_retroalimentacion']),
                'dim_claridad_rol_promedio' => $dimClaridadRolB,
                'dim_claridad_rol_nivel' => $aplicarBaremo($dimClaridadRolB, $baremosB['dim_claridad_rol']),
                'dim_capacitacion_promedio' => $dimCapacitacionB,
                'dim_capacitacion_nivel' => $aplicarBaremo($dimCapacitacionB, $baremosB['dim_capacitacion']),
                // Dimensiones Forma B (6-10)
                'dim_participacion_cambio_promedio' => $dimParticipacionCambioB,
                'dim_participacion_cambio_nivel' => $aplicarBaremo($dimParticipacionCambioB, $baremosB['dim_participacion_cambio']),
                'dim_oportunidades_promedio' => $dimOportunidadesB,
                'dim_oportunidades_nivel' => $aplicarBaremo($dimOportunidadesB, $baremosB['dim_oportunidades']),
                'dim_control_autonomia_promedio' => $dimControlAutonomiaB,
                'dim_control_autonomia_nivel' => $aplicarBaremo($dimControlAutonomiaB, $baremosB['dim_control_autonomia']),
                'dim_demandas_ambientales_promedio' => $dimDemandasAmbientalesB,
                'dim_demandas_ambientales_nivel' => $aplicarBaremo($dimDemandasAmbientalesB, $baremosB['dim_demandas_ambientales']),
                'dim_demandas_emocionales_promedio' => $dimDemandasEmocionalesB,
                'dim_demandas_emocionales_nivel' => $aplicarBaremo($dimDemandasEmocionalesB, $baremosB['dim_demandas_emocionales']),
                // Dimensiones Forma B (11-16)
                'dim_demandas_cuantitativas_promedio' => $dimDemandasCuantitativasB,
                'dim_demandas_cuantitativas_nivel' => $aplicarBaremo($dimDemandasCuantitativasB, $baremosB['dim_demandas_cuantitativas']),
                'dim_influencia_trabajo_promedio' => $dimInfluenciaTrabajoB,
                'dim_influencia_trabajo_nivel' => $aplicarBaremo($dimInfluenciaTrabajoB, $baremosB['dim_influencia_trabajo']),
                'dim_demandas_carga_mental_promedio' => $dimDemandasCargaMentalB,
                'dim_demandas_carga_mental_nivel' => $aplicarBaremo($dimDemandasCargaMentalB, $baremosB['dim_demandas_carga_mental']),
                'dim_demandas_jornada_promedio' => $dimDemandasJornadaB,
                'dim_demandas_jornada_nivel' => $aplicarBaremo($dimDemandasJornadaB, $baremosB['dim_demandas_jornada']),
                'dim_recompensas_pertenencia_promedio' => $dimRecompensasPertenenciaB,
                'dim_recompensas_pertenencia_nivel' => $aplicarBaremo($dimRecompensasPertenenciaB, $baremosB['dim_recompensas_pertenencia']),
                'dim_reconocimiento_compensacion_promedio' => $dimReconocimientoCompensacionB,
                'dim_reconocimiento_compensacion_nivel' => $aplicarBaremo($dimReconocimientoCompensacionB, $baremosB['dim_reconocimiento_compensacion']),
                // Dimensiones Extralaborales
                'dim_tiempo_fuera_trabajo_promedio' => $dimTiempoFueraTrabajoB,
                'dim_tiempo_fuera_trabajo_nivel' => $aplicarBaremo($dimTiempoFueraTrabajoB, $baremosB['dim_tiempo_fuera_trabajo']),
                'dim_relaciones_familiares_promedio' => $dimRelacionesFamiliaresB,
                'dim_relaciones_familiares_nivel' => $aplicarBaremo($dimRelacionesFamiliaresB, $baremosB['dim_relaciones_familiares']),
                'dim_comunicacion_relaciones_promedio' => $dimComunicacionRelacionesB,
                'dim_comunicacion_relaciones_nivel' => $aplicarBaremo($dimComunicacionRelacionesB, $baremosB['dim_comunicacion_relaciones']),
                'dim_situacion_economica_promedio' => $dimSituacionEconomicaB,
                'dim_situacion_economica_nivel' => $aplicarBaremo($dimSituacionEconomicaB, $baremosB['dim_situacion_economica']),
                'dim_caracteristicas_vivienda_promedio' => $dimCaracteristicasViviendaB,
                'dim_caracteristicas_vivienda_nivel' => $aplicarBaremo($dimCaracteristicasViviendaB, $baremosB['dim_caracteristicas_vivienda']),
                'dim_influencia_entorno_promedio' => $dimInfluenciaEntornoB,
                'dim_influencia_entorno_nivel' => $aplicarBaremo($dimInfluenciaEntornoB, $baremosB['dim_influencia_entorno']),
                'dim_desplazamiento_vivienda_promedio' => $dimDesplazamientoViviendaB,
                'dim_desplazamiento_vivienda_nivel' => $aplicarBaremo($dimDesplazamientoViviendaB, $baremosB['dim_desplazamiento_vivienda'])
            ];
        }

        $data = [
            'title' => 'Gráficos Globales',
            'service' => $service,
            'formaACount' => count($resultsFormaA),
            'formaBCount' => count($resultsFormaB),
            'globalDataFormaA' => $globalDataFormaA,
            'globalDataFormaB' => $globalDataFormaB
        ];

        return view('battery_services/global_gauges', $data);
    }
}
