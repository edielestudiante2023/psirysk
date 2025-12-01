<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\BatteryServiceModel;
use App\Models\CompanyModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;
use App\Libraries\IntralaboralAScoring;
use App\Libraries\IntralaboralBScoring;
use App\Libraries\ExtralaboralScoring;
use App\Libraries\EstresScoring;

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

        // BAREMOS INTRALABORAL - Desde fuente única autorizada (README_BAREMOS.md)
        $baremoIntralaboralTotal = $formaType === 'A'
            ? IntralaboralAScoring::getBaremoTotal()
            : IntralaboralBScoring::getBaremoTotal();

        // Baremos de dominios - Desde fuente única autorizada
        // Mapeo de códigos cortos a códigos de librería
        $mapeoCodigosDominios = [
            'liderazgo' => 'liderazgo_relaciones_sociales',
            'control' => 'control',
            'demandas' => 'demandas',
            'recompensas' => 'recompensas',
        ];

        $baremoDominios = [];
        foreach ($mapeoCodigosDominios as $codigoCorto => $codigoLibreria) {
            $baremoDominios[$codigoCorto] = $formaType === 'A'
                ? IntralaboralAScoring::getBaremoDominio($codigoLibreria)
                : IntralaboralBScoring::getBaremoDominio($codigoLibreria);
        }

        // Baremos de dimensiones - Desde fuente única autorizada
        // Mapeo de códigos cortos a códigos de librería
        $mapeoCodigosDimensiones = [
            'caracteristicas_liderazgo' => 'caracteristicas_liderazgo',
            'relaciones_sociales' => 'relaciones_sociales_trabajo',
            'retroalimentacion' => 'retroalimentacion_desempeno',
            'relacion_colaboradores' => 'relacion_colaboradores',  // Solo Forma A
            'claridad_rol' => 'claridad_rol',
            'capacitacion' => 'capacitacion',
            'participacion_cambio' => 'participacion_manejo_cambio',
            'oportunidades_desarrollo' => 'oportunidades_desarrollo',
            'control_autonomia' => 'control_autonomia_trabajo',
            'demandas_ambientales' => 'demandas_ambientales_esfuerzo_fisico',
            'demandas_emocionales' => 'demandas_emocionales',
            'demandas_cuantitativas' => 'demandas_cuantitativas',
            'influencia_entorno' => 'influencia_trabajo_entorno_extralaboral',
            'exigencias_responsabilidad' => 'exigencias_responsabilidad_cargo',  // Solo Forma A
            'demandas_carga_mental' => 'demandas_carga_mental',
            'consistencia_rol' => 'consistencia_rol',  // Solo Forma A
            'demandas_jornada' => 'demandas_jornada_trabajo',
            'recompensas_pertenencia' => 'recompensas_pertenencia_estabilidad',
            'reconocimiento_compensacion' => 'reconocimiento_compensacion',
            'reconocimiento' => 'reconocimiento_compensacion',  // Alias
        ];

        $baremosDimensiones = [];
        foreach ($mapeoCodigosDimensiones as $codigoCorto => $codigoLibreria) {
            $baremo = $formaType === 'A'
                ? IntralaboralAScoring::getBaremoDimension($codigoLibreria)
                : IntralaboralBScoring::getBaremoDimension($codigoLibreria);
            // Solo agregar si el baremo existe (algunas dimensiones son exclusivas de Forma A)
            if ($baremo !== null) {
                $baremosDimensiones[$codigoCorto] = $baremo;
            }
        }

        // BAREMOS EXTRALABORAL - Desde fuente única autorizada
        // Usamos Forma B (auxiliares) como general para promedios grupales
        $mapeoCodigosExtralaboral = [
            'tiempo_fuera' => 'tiempo_fuera_trabajo',
            'relaciones_familiares' => 'relaciones_familiares',
            'comunicacion' => 'comunicacion_relaciones',
            'situacion_economica' => 'situacion_economica',
            'caracteristicas_vivienda' => 'caracteristicas_vivienda',
            'influencia_entorno' => 'influencia_entorno_extralaboral',
            'desplazamiento' => 'desplazamiento_vivienda_trabajo',
        ];

        $baremosExtralaboral = [];
        foreach ($mapeoCodigosExtralaboral as $codigoCorto => $codigoLibreria) {
            $baremo = ExtralaboralScoring::getBaremoDimension($codigoLibreria);
            if ($baremo !== null) {
                $baremosExtralaboral[$codigoCorto] = $baremo;
            }
        }
        // Baremo total extralaboral (usamos Forma B por defecto)
        $baremosExtralaboral['total'] = ExtralaboralScoring::getBaremoTotal('B');

        // BAREMO ESTRÉS - Desde fuente única autorizada
        // Usamos Forma B (auxiliares) como general para promedios grupales
        $baremoEstres = EstresScoring::getBaremoB();

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

        // Baremos desde fuente única autorizada (README_BAREMOS.md)
        // Helper para construir baremos por forma
        $buildBaremos = function($forma) {
            $intralaboral = $forma === 'A' ? IntralaboralAScoring::class : IntralaboralBScoring::class;

            $baremos = [
                'intralaboral' => $intralaboral::getBaremoTotal(),
                'extralaboral' => ExtralaboralScoring::getBaremoTotal($forma),
                'estres' => $forma === 'A' ? EstresScoring::getBaremoA() : EstresScoring::getBaremoB(),
                'puntaje_total_general' => EstresScoring::getBaremoGeneral($forma),
            ];

            // Dominios intralaborales
            $mapDominios = [
                'dom_liderazgo' => 'liderazgo_relaciones_sociales',
                'dom_control' => 'control',
                'dom_demandas' => 'demandas',
                'dom_recompensas' => 'recompensas',
            ];
            foreach ($mapDominios as $key => $codigo) {
                $baremos[$key] = $intralaboral::getBaremoDominio($codigo);
            }

            // Dimensiones intralaborales
            $mapDimensiones = [
                'dim_caracteristicas_liderazgo' => 'caracteristicas_liderazgo',
                'dim_relaciones_sociales' => 'relaciones_sociales_trabajo',
                'dim_retroalimentacion' => 'retroalimentacion_desempeno',
                'dim_relacion_colaboradores' => 'relacion_con_colaboradores',
                'dim_claridad_rol' => 'claridad_rol',
                'dim_capacitacion' => 'capacitacion',
                'dim_participacion_cambio' => 'participacion_manejo_cambio',
                'dim_oportunidades' => 'oportunidades_desarrollo',
                'dim_control_autonomia' => 'control_autonomia_trabajo',
                'dim_demandas_ambientales' => 'demandas_ambientales_esfuerzo_fisico',
                'dim_demandas_emocionales' => 'demandas_emocionales',
                'dim_demandas_cuantitativas' => 'demandas_cuantitativas',
                'dim_influencia_trabajo' => 'influencia_trabajo_entorno_extralaboral',
                'dim_exigencias_responsabilidad' => 'exigencias_responsabilidad_cargo',
                'dim_demandas_carga_mental' => 'demandas_carga_mental',
                'dim_consistencia_rol' => 'consistencia_rol',
                'dim_demandas_jornada' => 'demandas_jornada_trabajo',
                'dim_recompensas_pertenencia' => 'recompensas_pertenencia_estabilidad',
                'dim_reconocimiento_compensacion' => 'reconocimiento_compensacion',
            ];
            foreach ($mapDimensiones as $key => $codigo) {
                $baremo = $intralaboral::getBaremoDimension($codigo);
                if ($baremo !== null) {
                    $baremos[$key] = $baremo;
                }
            }

            // Dimensiones extralaborales
            $mapExtralaboral = [
                'dim_tiempo_fuera_trabajo' => 'tiempo_fuera_trabajo',
                'dim_relaciones_familiares' => 'relaciones_familiares',
                'dim_comunicacion_relaciones' => 'comunicacion_relaciones',
                'dim_situacion_economica' => 'situacion_economica',
                'dim_caracteristicas_vivienda' => 'caracteristicas_vivienda',
                'dim_influencia_entorno' => 'influencia_entorno',
                'dim_desplazamiento_vivienda' => 'desplazamiento',
            ];
            foreach ($mapExtralaboral as $key => $codigo) {
                $baremo = ExtralaboralScoring::getBaremoDimension($codigo, $forma);
                if ($baremo !== null) {
                    $baremos[$key] = $baremo;
                }
            }

            return $baremos;
        };

        $baremosA = $buildBaremos('A');
        $baremosB = $buildBaremos('B');

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
            $dimParticipacionCambioA = $calcularPromedio(array_column($resultsFormaA, 'dim_participacion_manejo_cambio_puntaje'));
            $dimOportunidadesA = $calcularPromedio(array_column($resultsFormaA, 'dim_oportunidades_desarrollo_puntaje'));
            $dimControlAutonomiaA = $calcularPromedio(array_column($resultsFormaA, 'dim_control_autonomia_puntaje'));
            $dimDemandasAmbientalesA = $calcularPromedio(array_column($resultsFormaA, 'dim_demandas_ambientales_puntaje'));

            // Dimensiones Forma A (11-15)
            $dimDemandasEmocionalesA = $calcularPromedio(array_column($resultsFormaA, 'dim_demandas_emocionales_puntaje'));
            $dimDemandasCuantitativasA = $calcularPromedio(array_column($resultsFormaA, 'dim_demandas_cuantitativas_puntaje'));
            $dimInfluenciaTrabajoA = $calcularPromedio(array_column($resultsFormaA, 'dim_influencia_trabajo_entorno_extralaboral_puntaje'));
            $dimExigenciasResponsabilidadA = $calcularPromedio(array_column($resultsFormaA, 'dim_demandas_responsabilidad_puntaje'));
            $dimDemandasCargaMentalA = $calcularPromedio(array_column($resultsFormaA, 'dim_demandas_carga_mental_puntaje'));

            // Dimensiones Forma A (16-19)
            $dimConsistenciaRolA = $calcularPromedio(array_column($resultsFormaA, 'dim_consistencia_rol_puntaje'));
            $dimDemandasJornadaA = $calcularPromedio(array_column($resultsFormaA, 'dim_demandas_jornada_trabajo_puntaje'));
            $dimRecompensasPertenenciaA = $calcularPromedio(array_column($resultsFormaA, 'dim_recompensas_pertenencia_puntaje'));
            $dimReconocimientoCompensacionA = $calcularPromedio(array_column($resultsFormaA, 'dim_reconocimiento_compensacion_puntaje'));

            // Dimensiones Extralaborales Forma A
            $dimTiempoFueraTrabajoA = $calcularPromedio(array_column($resultsFormaA, 'extralaboral_tiempo_fuera_puntaje'));
            $dimRelacionesFamiliaresA = $calcularPromedio(array_column($resultsFormaA, 'extralaboral_relaciones_familiares_puntaje'));
            $dimComunicacionRelacionesA = $calcularPromedio(array_column($resultsFormaA, 'extralaboral_comunicacion_puntaje'));
            $dimSituacionEconomicaA = $calcularPromedio(array_column($resultsFormaA, 'extralaboral_situacion_economica_puntaje'));
            $dimCaracteristicasViviendaA = $calcularPromedio(array_column($resultsFormaA, 'extralaboral_caracteristicas_vivienda_puntaje'));
            $dimInfluenciaEntornoA = $calcularPromedio(array_column($resultsFormaA, 'extralaboral_influencia_entorno_puntaje'));
            $dimDesplazamientoViviendaA = $calcularPromedio(array_column($resultsFormaA, 'extralaboral_desplazamiento_puntaje'));

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
            $dimParticipacionCambioB = $calcularPromedio(array_column($resultsFormaB, 'dim_participacion_manejo_cambio_puntaje'));
            $dimOportunidadesB = $calcularPromedio(array_column($resultsFormaB, 'dim_oportunidades_desarrollo_puntaje'));
            $dimControlAutonomiaB = $calcularPromedio(array_column($resultsFormaB, 'dim_control_autonomia_puntaje'));
            $dimDemandasAmbientalesB = $calcularPromedio(array_column($resultsFormaB, 'dim_demandas_ambientales_puntaje'));
            $dimDemandasEmocionalesB = $calcularPromedio(array_column($resultsFormaB, 'dim_demandas_emocionales_puntaje'));

            // Dimensiones Forma B (11-16)
            $dimDemandasCuantitativasB = $calcularPromedio(array_column($resultsFormaB, 'dim_demandas_cuantitativas_puntaje'));
            $dimInfluenciaTrabajoB = $calcularPromedio(array_column($resultsFormaB, 'dim_influencia_trabajo_entorno_extralaboral_puntaje'));
            $dimDemandasCargaMentalB = $calcularPromedio(array_column($resultsFormaB, 'dim_demandas_carga_mental_puntaje'));
            $dimDemandasJornadaB = $calcularPromedio(array_column($resultsFormaB, 'dim_demandas_jornada_trabajo_puntaje'));
            $dimRecompensasPertenenciaB = $calcularPromedio(array_column($resultsFormaB, 'dim_recompensas_pertenencia_puntaje'));
            $dimReconocimientoCompensacionB = $calcularPromedio(array_column($resultsFormaB, 'dim_reconocimiento_compensacion_puntaje'));

            // Dimensiones Extralaborales Forma B
            $dimTiempoFueraTrabajoB = $calcularPromedio(array_column($resultsFormaB, 'extralaboral_tiempo_fuera_puntaje'));
            $dimRelacionesFamiliaresB = $calcularPromedio(array_column($resultsFormaB, 'extralaboral_relaciones_familiares_puntaje'));
            $dimComunicacionRelacionesB = $calcularPromedio(array_column($resultsFormaB, 'extralaboral_comunicacion_puntaje'));
            $dimSituacionEconomicaB = $calcularPromedio(array_column($resultsFormaB, 'extralaboral_situacion_economica_puntaje'));
            $dimCaracteristicasViviendaB = $calcularPromedio(array_column($resultsFormaB, 'extralaboral_caracteristicas_vivienda_puntaje'));
            $dimInfluenciaEntornoB = $calcularPromedio(array_column($resultsFormaB, 'extralaboral_influencia_entorno_puntaje'));
            $dimDesplazamientoViviendaB = $calcularPromedio(array_column($resultsFormaB, 'extralaboral_desplazamiento_puntaje'));

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
