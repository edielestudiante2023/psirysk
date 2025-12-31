<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\BatteryServiceModel;
use App\Models\WorkerModel;
use App\Models\ResponseModel;
use App\Models\CalculatedResultModel;
use App\Libraries\IntralaboralAScoring;
use App\Libraries\IntralaboralBScoring;

class ValidationController extends BaseController
{
    protected $batteryServiceModel;
    protected $workerModel;
    protected $responseModel;
    protected $calculatedResultModel;

    public function __construct()
    {
        $this->batteryServiceModel = new BatteryServiceModel();
        $this->workerModel = new WorkerModel();
        $this->responseModel = new ResponseModel();
        $this->calculatedResultModel = new CalculatedResultModel();
    }

    /**
     * Verificar permisos de acceso - Solo consultores y admin
     */
    private function checkPermissions()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $roleName = session()->get('role_name');
        if (!in_array($roleName, ['superadmin', 'consultor', 'admin'])) {
            return redirect()->to('/dashboard')->with('error', 'No tienes permisos para acceder a la validación de resultados');
        }

        return null;
    }

    /**
     * Vista principal de validación para un servicio
     */
    public function index($serviceId)
    {
        log_message('error', '🔍 [ValidationController::index] ServiceId: ' . $serviceId);
        log_message('error', '🔍 [ValidationController::index] REQUEST_URI: ' . $_SERVER['REQUEST_URI']);
        log_message('error', '🔍 [ValidationController::index] Query String: ' . ($_SERVER['QUERY_STRING'] ?? 'none'));

        $permissionCheck = $this->checkPermissions();
        if ($permissionCheck) return $permissionCheck;

        // Obtener información del servicio
        $service = $this->batteryServiceModel
            ->select('battery_services.*, companies.name as company_name')
            ->join('companies', 'companies.id = battery_services.company_id')
            ->find($serviceId);

        if (!$service) {
            return redirect()->to('/battery-services')->with('error', 'Servicio no encontrado');
        }

        // Obtener trabajadores del servicio que completaron las evaluaciones
        $workers = $this->workerModel
            ->where('battery_service_id', $serviceId)
            ->where('status', 'completado')
            ->findAll();

        // Validar si hay trabajadores para validar
        if (empty($workers)) {
            return redirect()->to('/battery-services/' . $serviceId)->with('error', 'No hay trabajadores con evaluaciones completadas para validar');
        }

        // Obtener configuración de dimensiones agrupadas por dominios desde librerías oficiales
        $domainsFormaA = $this->getDimensionsGroupedByDomain('A');
        $domainsFormaB = $this->getDimensionsGroupedByDomain('B');

        $data = [
            'title' => 'Validación de Resultados - ' . $service['service_name'],
            'service' => $service,
            'workers' => $workers,
            'domainsFormaA' => $domainsFormaA,
            'domainsFormaB' => $domainsFormaB
        ];

        return view('validation/index', $data);
    }

    /**
     * Validar dimensión específica con forma explícita (A o B)
     */
    public function validateDimension($serviceId, $dimension, $formType)
    {
        $permissionCheck = $this->checkPermissions();
        if ($permissionCheck) return $permissionCheck;

        // Validar que formType sea A o B
        $formType = strtoupper($formType);
        if (!in_array($formType, ['A', 'B'])) {
            return redirect()->to('/validation/' . $serviceId)->with('error', 'Forma inválida');
        }

        // Obtener información del servicio
        $service = $this->batteryServiceModel
            ->select('battery_services.*, companies.name as company_name')
            ->join('companies', 'companies.id = battery_services.company_id')
            ->find($serviceId);

        if (!$service) {
            return redirect()->to('/battery-services')->with('error', 'Servicio no encontrado');
        }

        // Obtener trabajadores de la forma especificada
        $workers = $this->workerModel
            ->where('battery_service_id', $serviceId)
            ->where('intralaboral_type', $formType)
            ->where('status', 'completado')
            ->findAll();

        if (empty($workers)) {
            return redirect()->to('/validation/' . $serviceId)->with('error', 'No hay trabajadores Forma ' . $formType . ' con evaluaciones completadas');
        }

        // Obtener configuración de la dimensión usando la forma especificada
        $dimensionConfig = $this->getDimensionConfig($dimension, $formType);

        if (!$dimensionConfig) {
            return redirect()->to('/validation/' . $serviceId)->with('error', 'Dimensión no encontrada en Forma ' . $formType);
        }

        // Determinar form_type para la BD
        $formTypeDb = 'intralaboral_' . $formType;

        // Calcular validación por cada ítem de la dimensión
        $validationData = $this->calculateDimensionValidation($workers, $dimensionConfig, $formTypeDb);

        $data = [
            'title' => 'Validación: ' . $dimensionConfig['name'] . ' (Forma ' . $formType . ')',
            'service' => $service,
            'dimension' => $dimension,
            'dimensionConfig' => $dimensionConfig,
            'workers' => $workers,
            'validationData' => $validationData,
            'formType' => $formType
        ];

        return view('validation/dimension_detail', $data);
    }

    /**
     * Calcular validación completa de una dimensión
     */
    private function calculateDimensionValidation($workers, $dimensionConfig, $formType)
    {
        $items = $dimensionConfig['items'];
        $itemsData = [];
        $totalParticipants = count($workers);

        // Por cada ítem de la dimensión
        foreach ($items as $itemNumber) {
            $isInverse = in_array($itemNumber, $dimensionConfig['inverse_items']);

            // Valores de scoring según tipo de ítem (normal o inverso)
            $scoreValues = $isInverse
                ? ['siempre' => 4, 'casi_siempre' => 3, 'algunas_veces' => 2, 'casi_nunca' => 1, 'nunca' => 0]
                : ['siempre' => 0, 'casi_siempre' => 1, 'algunas_veces' => 2, 'casi_nunca' => 3, 'nunca' => 4];

            $itemData = [
                'item_number' => $itemNumber,
                'participants' => $totalParticipants,
                'responses' => [
                    'siempre' => 0,      // Cuenta de personas que eligieron esta opción
                    'casi_siempre' => 0,
                    'algunas_veces' => 0,
                    'casi_nunca' => 0,
                    'nunca' => 0
                ],
                'score_values' => $scoreValues, // Valores de puntaje para cada opción
                'scores' => [
                    'siempre' => 0,      // Puntaje total = cantidad × valor
                    'casi_siempre' => 0,
                    'algunas_veces' => 0,
                    'casi_nunca' => 0,
                    'nunca' => 0
                ],
                'subtotal' => 0,
                'is_inverse' => $isInverse
            ];

            // Obtener todas las respuestas de este ítem
            $responses = $this->responseModel
                ->whereIn('worker_id', array_column($workers, 'id'))
                ->where('form_type', $formType)
                ->where('question_number', $itemNumber)
                ->findAll();

            // DEBUG: Log para el primer ítem
            if ($itemNumber === $items[0]) {
                log_message('error', '🔍 [calculateDimensionValidation] First item: ' . $itemNumber);
                log_message('error', '🔍 [calculateDimensionValidation] Form type searching: "' . $formType . '"');
                log_message('error', '🔍 [calculateDimensionValidation] Worker IDs: ' . json_encode(array_column($workers, 'id')));
                log_message('error', '🔍 [calculateDimensionValidation] Responses found: ' . count($responses));
                if (count($responses) > 0) {
                    log_message('error', '🔍 [calculateDimensionValidation] Sample response: ' . json_encode($responses[0]));
                }
            }

            // Mapeo de valores numéricos a opciones de respuesta
            // Los valores en BD (0-4) representan la RESPUESTA LITERAL del usuario:
            // 0=Siempre, 1=Casi siempre, 2=Algunas veces, 3=Casi nunca, 4=Nunca
            // Esto aplica tanto para ítems normales como inversos
            $numToOption = ['0' => 'siempre', '1' => 'casi_siempre', '2' => 'algunas_veces', '3' => 'casi_nunca', '4' => 'nunca'];

            // Contar respuestas y calcular puntajes
            foreach ($responses as $response) {
                $numericValue = trim($response['answer_value']);

                // Convertir valor numérico a opción de respuesta
                if (isset($numToOption[$numericValue])) {
                    $option = $numToOption[$numericValue];
                    $itemData['responses'][$option]++;

                    // Calcular el puntaje según si el ítem es normal o inverso
                    // Normal: usar el valor directo (0,1,2,3,4)
                    // Inverso: invertir el valor (4,3,2,1,0)
                    $scoreValue = $isInverse ? (4 - (int)$numericValue) : (int)$numericValue;
                    $itemData['scores'][$option] += $scoreValue;
                }
            }

            // Calcular subtotal del ítem
            $itemData['subtotal'] = array_sum($itemData['scores']);
            $itemData['average'] = $totalParticipants > 0 ? round($itemData['subtotal'] / $totalParticipants, 2) : 0;

            // Validar que la suma de respuestas sea igual al total de participantes
            $totalResponses = array_sum($itemData['responses']);
            $itemData['response_count_valid'] = ($totalResponses === $totalParticipants);
            $itemData['response_count_difference'] = $totalResponses - $totalParticipants;

            $itemsData[] = $itemData;
        }

        // Calcular suma de promedios de la dimensión
        $sumPromedios = array_sum(array_column($itemsData, 'average'));

        // Aplicar transformación
        $factor = $dimensionConfig['transformation_factor'];
        $puntajeTransformado = $factor > 0 ? round(($sumPromedios / $factor) * 100, 2) : 0;

        // Obtener nivel de riesgo según baremos
        $nivelRiesgo = $this->getNivelRiesgo($puntajeTransformado, $dimensionConfig['baremos']);

        // Comparar con BD
        $dbComparison = $this->compareWithDatabase($workers, $dimensionConfig['db_field'], $puntajeTransformado);

        return [
            'items' => $itemsData,
            'sum_promedios' => round($sumPromedios, 2),
            'transformation_factor' => $factor,
            'puntaje_transformado' => $puntajeTransformado,
            'nivel_riesgo' => $nivelRiesgo,
            'db_comparison' => $dbComparison
        ];
    }

    /**
     * Obtener nivel de riesgo según baremos
     */
    private function getNivelRiesgo($puntaje, $baremos)
    {
        foreach ($baremos as $nivel => $rango) {
            if ($puntaje >= $rango['min'] && $puntaje <= $rango['max']) {
                return [
                    'nivel' => $nivel,
                    'label' => $rango['label'],
                    'color' => $rango['color']
                ];
            }
        }

        return ['nivel' => 'desconocido', 'label' => 'Desconocido', 'color' => 'secondary'];
    }

    /**
     * Comparar puntaje calculado con el almacenado en BD
     */
    private function compareWithDatabase($workers, $dbField, $calculatedScore)
    {
        $workerIds = array_column($workers, 'id');
        $dbResults = $this->calculatedResultModel
            ->select("AVG({$dbField}) as average_score")
            ->whereIn('worker_id', $workerIds)
            ->first();

        $dbScore = $dbResults['average_score'] ? round($dbResults['average_score'], 2) : 0;
        $difference = round($calculatedScore - $dbScore, 2);

        return [
            'db_score' => $dbScore,
            'calculated_score' => $calculatedScore,
            'difference' => $difference,
            'matches' => abs($difference) < 0.1, // Tolerancia de 0.1 por redondeos
            'status' => abs($difference) < 0.1 ? 'ok' : 'error'
        ];
    }

    /**
     * Configuración de dimensiones obtenida desde las librerías oficiales
     * Soporta Forma A y Forma B
     */
    private function getDimensionConfig($dimension, $formType = 'A')
    {
        log_message('error', '🔧 [getDimensionConfig] Called with dimension: "' . $dimension . '" Form: ' . $formType);

        // Usar reflexión para obtener datos privados de las librerías oficiales
        $scoringClass = $formType === 'A' ? IntralaboralAScoring::class : IntralaboralBScoring::class;

        // Obtener baremos desde la librería oficial
        $baremosDimensiones = $formType === 'A'
            ? IntralaboralAScoring::getBaremosDimensiones()
            : IntralaboralBScoring::getBaremosDimensiones();

        if (!isset($baremosDimensiones[$dimension])) {
            log_message('error', '🔧 [getDimensionConfig] Dimensión no encontrada en baremos oficiales');
            return null;
        }

        // Usar reflexión para acceder a propiedades privadas
        $reflection = new \ReflectionClass($scoringClass);

        // Obtener dimensiones (ítems)
        $dimensionesProperty = $reflection->getProperty('dimensiones');
        $dimensionesProperty->setAccessible(true);
        $dimensiones = $dimensionesProperty->getValue();

        if (!isset($dimensiones[$dimension])) {
            log_message('error', '🔧 [getDimensionConfig] Dimensión no encontrada en configuración de ítems');
            return null;
        }

        // Obtener ítems inversos
        $inverseProperty = $reflection->getProperty('itemsGrupoInverso');
        $inverseProperty->setAccessible(true);
        $itemsInversos = $inverseProperty->getValue();

        // Obtener factores de transformación
        $factoresProperty = $reflection->getProperty('factoresTransformacionDimensiones');
        $factoresProperty->setAccessible(true);
        $factores = $factoresProperty->getValue();

        // Convertir baremos oficiales al formato esperado por la vista
        $baremosFormateados = [];
        $baremos = $baremosDimensiones[$dimension];

        foreach ($baremos as $nivel => $rango) {
            $color = match($nivel) {
                'sin_riesgo' => 'success',
                'riesgo_bajo' => 'info',
                'riesgo_medio' => 'warning',
                'riesgo_alto' => 'danger',
                'riesgo_muy_alto' => 'danger',
                default => 'secondary'
            };

            $label = match($nivel) {
                'sin_riesgo' => 'Sin riesgo o riesgo despreciable',
                'riesgo_bajo' => 'Riesgo bajo',
                'riesgo_medio' => 'Riesgo medio',
                'riesgo_alto' => 'Riesgo alto',
                'riesgo_muy_alto' => 'Riesgo muy alto',
                default => 'Desconocido'
            };

            $baremosFormateados[$nivel] = [
                'min' => $rango[0],
                'max' => $rango[1],
                'label' => $label,
                'color' => $color
            ];
        }

        // Formatear nombre de la dimensión con tildes correctas
        $nombre = $this->getDimensionDisplayName($dimension);

        // Mapeo de dimensiones a campos de BD (algunos tienen nombres abreviados)
        $dimensionToDbFieldMap = [
            // Forma A - Dominio Liderazgo y Relaciones Sociales
            'caracteristicas_liderazgo' => 'dim_caracteristicas_liderazgo_puntaje',
            'relaciones_sociales_trabajo' => 'dim_relaciones_sociales_puntaje',  // Abreviado en BD
            'retroalimentacion_desempeno' => 'dim_retroalimentacion_puntaje',    // Abreviado en BD
            'relacion_con_colaboradores' => 'dim_relacion_colaboradores_puntaje', // Abreviado en BD

            // Forma A - Dominio Control
            'claridad_rol' => 'dim_claridad_rol_puntaje',
            'capacitacion' => 'dim_capacitacion_puntaje',
            'participacion_manejo_cambio' => 'dim_participacion_manejo_cambio_puntaje',
            'oportunidades_desarrollo' => 'dim_oportunidades_desarrollo_puntaje',
            'control_autonomia_trabajo' => 'dim_control_autonomia_puntaje',

            // Forma A - Dominio Demandas
            'demandas_ambientales_esfuerzo_fisico' => 'dim_demandas_ambientales_puntaje',
            'demandas_emocionales' => 'dim_demandas_emocionales_puntaje',
            'demandas_cuantitativas' => 'dim_demandas_cuantitativas_puntaje',
            'influencia_trabajo_entorno_extralaboral' => 'dim_influencia_trabajo_entorno_extralaboral_puntaje',
            'exigencias_responsabilidad_cargo' => 'dim_demandas_responsabilidad_puntaje',
            'demandas_carga_mental' => 'dim_demandas_carga_mental_puntaje',
            'consistencia_rol' => 'dim_consistencia_rol_puntaje',
            'demandas_jornada_trabajo' => 'dim_demandas_jornada_trabajo_puntaje',

            // Forma A - Dominio Recompensas
            'recompensas_pertenencia_estabilidad' => 'dim_recompensas_pertenencia_puntaje',
            'reconocimiento_compensacion' => 'dim_reconocimiento_compensacion_puntaje',
        ];

        // Determinar campo de BD usando el mapeo o default
        $dbField = $dimensionToDbFieldMap[$dimension] ?? ('dim_' . $dimension . '_puntaje');

        // Filtrar ítems inversos de esta dimensión
        $itemsInversosDimension = array_intersect($itemsInversos, $dimensiones[$dimension]);

        $config = [
            'name' => $nombre,
            'items' => $dimensiones[$dimension],
            'inverse_items' => array_values($itemsInversosDimension),
            'transformation_factor' => $factores[$dimension],
            'db_field' => $dbField,
            'baremos' => $baremosFormateados
        ];

        log_message('error', '🔧 [getDimensionConfig] Config generado desde librería oficial: ' . json_encode([
            'items_count' => count($config['items']),
            'inverse_count' => count($config['inverse_items']),
            'factor' => $config['transformation_factor']
        ]));

        return $config;
    }

    /**
     * Obtener lista completa de dimensiones disponibles para una forma
     * Obtiene información desde las librerías oficiales
     */
    private function getDimensionsListForForm($formType = 'A')
    {
        $scoringClass = $formType === 'A' ? IntralaboralAScoring::class : IntralaboralBScoring::class;

        // Obtener baremos (contiene todas las dimensiones disponibles)
        $baremosDimensiones = $formType === 'A'
            ? IntralaboralAScoring::getBaremosDimensiones()
            : IntralaboralBScoring::getBaremosDimensiones();

        // Usar reflexión para obtener la configuración de ítems
        $reflection = new \ReflectionClass($scoringClass);
        $dimensionesProperty = $reflection->getProperty('dimensiones');
        $dimensionesProperty->setAccessible(true);
        $dimensiones = $dimensionesProperty->getValue();

        $dimensionsList = [];

        foreach (array_keys($baremosDimensiones) as $dimensionKey) {
            if (!isset($dimensiones[$dimensionKey])) {
                continue; // Skip si no tiene ítems configurados
            }

            $items = $dimensiones[$dimensionKey];
            $itemCount = count($items);
            $minItem = min($items);
            $maxItem = max($items);

            // Formatear nombre con tildes correctas
            $name = $this->getDimensionDisplayName($dimensionKey);

            $dimensionsList[] = [
                'key' => $dimensionKey,
                'name' => $name,
                'item_count' => $itemCount,
                'item_range' => "$minItem-$maxItem",
                'items' => $items
            ];
        }

        return $dimensionsList;
    }

    /**
     * Obtener dimensiones organizadas por dominios
     * Estructura jerárquica desde las librerías oficiales
     */
    private function getDimensionsGroupedByDomain($formType = 'A')
    {
        $scoringClass = $formType === 'A' ? IntralaboralAScoring::class : IntralaboralBScoring::class;

        // Usar reflexión para obtener dominios
        $reflection = new \ReflectionClass($scoringClass);
        $dominiosProperty = $reflection->getProperty('dominios');
        $dominiosProperty->setAccessible(true);
        $dominios = $dominiosProperty->getValue();

        // Obtener dimensiones
        $dimensionesProperty = $reflection->getProperty('dimensiones');
        $dimensionesProperty->setAccessible(true);
        $dimensiones = $dimensionesProperty->getValue();

        // Nombres legibles para dominios con tildes correctas
        $dominioNames = [
            'liderazgo_relaciones_sociales' => 'Liderazgo y Relaciones Sociales en el Trabajo',
            'control' => 'Control sobre el Trabajo',
            'demandas' => 'Demandas del Trabajo',
            'recompensas' => 'Recompensas'
        ];

        $result = [];

        foreach ($dominios as $dominioKey => $dimensionKeys) {
            $dimensionsList = [];

            foreach ($dimensionKeys as $dimensionKey) {
                if (!isset($dimensiones[$dimensionKey])) {
                    continue;
                }

                $items = $dimensiones[$dimensionKey];
                $itemCount = count($items);
                $minItem = min($items);
                $maxItem = max($items);

                // Formatear nombre de dimensión con tildes correctas
                $name = $this->getDimensionDisplayName($dimensionKey);

                $dimensionsList[] = [
                    'key' => $dimensionKey,
                    'name' => $name,
                    'item_count' => $itemCount,
                    'item_range' => "$minItem-$maxItem",
                    'items' => $items
                ];
            }

            if (!empty($dimensionsList)) {
                $result[] = [
                    'key' => $dominioKey,
                    'name' => $dominioNames[$dominioKey] ?? ucwords(str_replace('_', ' ', $dominioKey)),
                    'dimensions' => $dimensionsList
                ];
            }
        }

        return $result;
    }

    /**
     * Obtener nombre legible de dimensión con tildes correctas
     */
    private function getDimensionDisplayName($dimensionKey)
    {
        $displayNames = [
            // Dominio Liderazgo y Relaciones Sociales
            'caracteristicas_liderazgo' => 'Características del Liderazgo',
            'relaciones_sociales_trabajo' => 'Relaciones Sociales en el Trabajo',
            'retroalimentacion_desempeno' => 'Retroalimentación del Desempeño',
            'relacion_con_colaboradores' => 'Relación con los Colaboradores',

            // Dominio Control
            'claridad_rol' => 'Claridad de Rol',
            'capacitacion' => 'Capacitación',
            'participacion_manejo_cambio' => 'Participación y Manejo del Cambio',
            'oportunidades_desarrollo' => 'Oportunidades para el Uso y Desarrollo de Habilidades y Conocimientos',
            'control_autonomia_trabajo' => 'Control y Autonomía sobre el Trabajo',

            // Dominio Demandas
            'demandas_ambientales_esfuerzo_fisico' => 'Demandas Ambientales y de Esfuerzo Físico',
            'demandas_emocionales' => 'Demandas Emocionales',
            'demandas_cuantitativas' => 'Demandas Cuantitativas',
            'influencia_trabajo_entorno_extralaboral' => 'Influencia del Trabajo sobre el Entorno Extralaboral',
            'exigencias_responsabilidad_cargo' => 'Exigencias de Responsabilidad del Cargo',
            'demandas_carga_mental' => 'Demandas de Carga Mental',
            'consistencia_rol' => 'Consistencia del Rol',
            'demandas_jornada_trabajo' => 'Demandas de la Jornada de Trabajo',

            // Dominio Recompensas
            'recompensas_pertenencia_estabilidad' => 'Recompensas Derivadas de la Pertenencia a la Organización y del Trabajo que se Realiza',
            'reconocimiento_compensacion' => 'Reconocimiento y Compensación'
        ];

        return $displayNames[$dimensionKey] ?? ucwords(str_replace('_', ' ', $dimensionKey));
    }

    /**
     * Lista trabajadores Forma A que respondieron Pregunta I (Atención a clientes)
     */
    public function conditionalFormaA_I($serviceId)
    {
        $permissionCheck = $this->checkPermissions();
        if ($permissionCheck) return $permissionCheck;

        // Obtener información del servicio
        $service = $this->batteryServiceModel
            ->select('battery_services.*, companies.name as company_name')
            ->join('companies', 'companies.id = battery_services.company_id')
            ->find($serviceId);

        if (!$service) {
            return redirect()->to('/battery-services')->with('error', 'Servicio no encontrado');
        }

        // Obtener trabajadores Forma A completados
        $workers = $this->workerModel
            ->where('battery_service_id', $serviceId)
            ->where('intralaboral_type', 'A')
            ->where('status', 'completado')
            ->findAll();

        if (empty($workers)) {
            return redirect()->to('/validation/' . $serviceId)->with('error', 'No hay trabajadores Forma A con evaluaciones completadas');
        }

        // Las respuestas condicionales se guardan en workers.atiende_clientes (1=Sí, 0 o NULL=No)
        // No es necesario buscar en 'responses' porque se guardan directamente en workers

        // Estadísticas
        $respuestaSi = 0;
        $respuestaNo = 0;

        foreach ($workers as &$worker) {
            // atiende_clientes: 1=Sí, 0 o NULL=No (NULL se trata como No)
            if ($worker['atiende_clientes'] === 1 || $worker['atiende_clientes'] === '1') {
                $worker['respuesta_pregunta_i'] = 'si';
                $respuestaSi++;
            } else {
                // Cualquier otro valor (0, NULL, etc.) se trata como No
                $worker['respuesta_pregunta_i'] = 'no';
                $respuestaNo++;
            }
        }
        unset($worker);

        $data = [
            'title' => 'Validación: Pregunta Condicional I - Forma A',
            'service' => $service,
            'workers' => $workers,
            'pregunta' => 'En mi trabajo debo brindar servicio a clientes o usuarios',
            'formType' => 'A',
            'stats' => [
                'total' => count($workers),
                'si' => $respuestaSi,
                'no' => $respuestaNo
            ],
            'items_controlados' => '106-114 (9 ítems sobre atención a clientes)'
        ];

        return view('validation/conditional_question', $data);
    }

    /**
     * Lista trabajadores Forma A que respondieron Pregunta II (Jefe de otras personas)
     */
    public function conditionalFormaA_II($serviceId)
    {
        $permissionCheck = $this->checkPermissions();
        if ($permissionCheck) return $permissionCheck;

        // Obtener información del servicio
        $service = $this->batteryServiceModel
            ->select('battery_services.*, companies.name as company_name')
            ->join('companies', 'companies.id = battery_services.company_id')
            ->find($serviceId);

        if (!$service) {
            return redirect()->to('/battery-services')->with('error', 'Servicio no encontrado');
        }

        // Obtener trabajadores Forma A completados
        $workers = $this->workerModel
            ->where('battery_service_id', $serviceId)
            ->where('intralaboral_type', 'A')
            ->where('status', 'completado')
            ->findAll();

        if (empty($workers)) {
            return redirect()->to('/validation/' . $serviceId)->with('error', 'No hay trabajadores Forma A con evaluaciones completadas');
        }

        // Las respuestas condicionales se guardan en workers.es_jefe (1=Sí, 0=No, NULL=Sin respuesta)
        // No es necesario buscar en 'responses' porque se guardan directamente en workers

        // Estadísticas
        $respuestaSi = 0;
        $respuestaNo = 0;

        foreach ($workers as &$worker) {
            // es_jefe: 1=Sí, 0 o NULL=No (NULL se trata como No)
            if ($worker['es_jefe'] === 1 || $worker['es_jefe'] === '1') {
                $worker['respuesta_pregunta_ii'] = 'si';
                $respuestaSi++;
            } else {
                // Cualquier otro valor (0, NULL, etc.) se trata como No
                $worker['respuesta_pregunta_ii'] = 'no';
                $respuestaNo++;
            }
        }
        unset($worker);

        $data = [
            'title' => 'Validación: Pregunta Condicional II - Forma A',
            'service' => $service,
            'workers' => $workers,
            'pregunta' => 'Soy jefe de otras personas en mi trabajo',
            'formType' => 'A',
            'stats' => [
                'total' => count($workers),
                'si' => $respuestaSi,
                'no' => $respuestaNo
            ],
            'items_controlados' => '115-123 (9 ítems sobre relación con colaboradores)'
        ];

        return view('validation/conditional_question', $data);
    }

    /**
     * Lista trabajadores Forma B que respondieron Pregunta I (Atención a clientes)
     */
    public function conditionalFormaB_I($serviceId)
    {
        $permissionCheck = $this->checkPermissions();
        if ($permissionCheck) return $permissionCheck;

        // Obtener información del servicio
        $service = $this->batteryServiceModel
            ->select('battery_services.*, companies.name as company_name')
            ->join('companies', 'companies.id = battery_services.company_id')
            ->find($serviceId);

        if (!$service) {
            return redirect()->to('/battery-services')->with('error', 'Servicio no encontrado');
        }

        // Obtener trabajadores Forma B completados
        $workers = $this->workerModel
            ->where('battery_service_id', $serviceId)
            ->where('intralaboral_type', 'B')
            ->where('status', 'completado')
            ->findAll();

        if (empty($workers)) {
            return redirect()->to('/validation/' . $serviceId)->with('error', 'No hay trabajadores Forma B con evaluaciones completadas');
        }

        // Las respuestas condicionales se guardan en workers.atiende_clientes (1=Sí, 0=No, NULL=Sin respuesta)
        // No es necesario buscar en 'responses' porque se guardan directamente en workers

        // Estadísticas
        $respuestaSi = 0;
        $respuestaNo = 0;

        foreach ($workers as &$worker) {
            // atiende_clientes: 1=Sí, 0 o NULL=No (NULL se trata como No)
            if ($worker['atiende_clientes'] === 1 || $worker['atiende_clientes'] === '1') {
                $worker['respuesta_pregunta_i'] = 'si';
                $respuestaSi++;
            } else {
                // Cualquier otro valor (0, NULL, etc.) se trata como No
                $worker['respuesta_pregunta_i'] = 'no';
                $respuestaNo++;
            }
        }
        unset($worker);

        $data = [
            'title' => 'Validación: Pregunta Condicional I - Forma B',
            'service' => $service,
            'workers' => $workers,
            'pregunta' => 'En mi trabajo debo brindar servicio a clientes o usuarios',
            'formType' => 'B',
            'stats' => [
                'total' => count($workers),
                'si' => $respuestaSi,
                'no' => $respuestaNo
            ],
            'items_controlados' => '89-97 (9 ítems sobre atención a clientes)'
        ];

        return view('validation/conditional_question', $data);
    }

    /**
     * Validación de Dominio (agrupa varias dimensiones)
     * Muestra puntajes y niveles de riesgo del dominio según Tablas 31-32
     */
    public function validateDomain($serviceId, $domainKey, $formType)
    {
        $this->checkPermissions();

        // Obtener configuración del dominio desde las librerías oficiales
        $scoringClass = ($formType === 'A') ? IntralaboralAScoring::class : IntralaboralBScoring::class;
        $allDomains = $scoringClass::getDominios();

        if (!isset($allDomains[$domainKey])) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("Dominio no encontrado: $domainKey");
        }

        $dimensionsInDomain = $allDomains[$domainKey];
        $baremosDominio = $scoringClass::getBaremosDominios();
        $baremos = $baremosDominio[$domainKey] ?? null;

        // Obtener workers completados con la forma correspondiente
        $workers = $this->workerModel
            ->where('battery_service_id', $serviceId)
            ->where('status', 'completado')
            ->where('intralaboral_type', $formType)
            ->findAll();

        if (empty($workers)) {
            return redirect()->back()->with('error', 'No hay trabajadores completados para validar');
        }

        $workerIds = array_column($workers, 'id');

        // Obtener resultados de BD
        $calculatedResults = $this->calculatedResultModel
            ->whereIn('worker_id', $workerIds)
            ->findAll();

        $resultsById = [];
        foreach ($calculatedResults as $result) {
            $resultsById[$result['worker_id']] = $result;
        }

        // Calcular puntajes del dominio usando la librería oficial
        $domainScores = [];
        foreach ($workers as $worker) {
            $responses = $this->responseModel
                ->where('worker_id', $worker['id'])
                ->whereIn('question_number', range(1, 123))
                ->findAll();

            $answersArray = [];
            foreach ($responses as $resp) {
                $answersArray[$resp['question_number']] = $resp['answer_value'];
            }

            if (empty($answersArray)) {
                continue;
            }

            // Calcular usando librería oficial
            $result = $scoringClass::calcular($answersArray, $worker['atiende_clientes'] === 'si', $worker['es_jefe'] === 'si');

            // Extraer puntaje del dominio
            $puntajeBruto = $result['puntajes_brutos_dominios'][$domainKey] ?? null;
            $puntajeTransformado = $result['puntajes_transformados_dominios'][$domainKey] ?? null;
            $nivel = $result['niveles_riesgo_dominios'][$domainKey] ?? null;

            $domainScores[] = [
                'worker_id' => $worker['id'],
                'worker_name' => $worker['name'],
                'puntaje_bruto' => $puntajeBruto,
                'puntaje_transformado' => $puntajeTransformado,
                'nivel_calculado' => $nivel,
                'nivel_bd' => $resultsById[$worker['id']]['dom_' . $domainKey . '_nivel'] ?? null,
                'puntaje_bd' => $resultsById[$worker['id']]['dom_' . $domainKey . '_puntaje'] ?? null
            ];
        }

        // Calcular estadísticas
        $puntajes = array_column($domainScores, 'puntaje_transformado');
        $promedioCalculado = count($puntajes) > 0 ? round(array_sum($puntajes) / count($puntajes), 2) : 0;
        $promedioFromDB = count($domainScores) > 0
            ? round(array_sum(array_column($domainScores, 'puntaje_bd')) / count($domainScores), 2)
            : 0;

        // Determinar nivel según baremos
        $nivelCalculado = $this->getNivelRiesgo($promedioCalculado, $baremos);

        $data = [
            'service' => $this->batteryServiceModel->find($serviceId),
            'domainKey' => $domainKey,
            'domainName' => $this->getDomainDisplayName($domainKey),
            'formType' => $formType,
            'dimensionsInDomain' => $dimensionsInDomain,
            'baremos' => $baremos,
            'domainScores' => $domainScores,
            'promedioCalculado' => $promedioCalculado,
            'promedioFromDB' => $promedioFromDB,
            'nivelCalculado' => $nivelCalculado,
            'totalWorkers' => count($workers)
        ];

        return view('validation/domain_detail', $data);
    }

    /**
     * Obtiene el nombre amigable del dominio
     */
    private function getDomainDisplayName($domainKey)
    {
        $names = [
            'liderazgo_relaciones_sociales' => 'Liderazgo y Relaciones Sociales en el Trabajo',
            'control' => 'Control sobre el Trabajo',
            'demandas' => 'Demandas del Trabajo',
            'recompensas' => 'Recompensas'
        ];
        return $names[$domainKey] ?? $domainKey;
    }

    /**
     * Validación del Puntaje Total Intralaboral (Tabla 33)
     * Suma de todas las dimensiones transformadas y calificadas según baremos oficiales
     */
    public function validateTotal($serviceId, $formType)
    {
        $this->checkPermissions();

        // Obtener clase de scoring según forma
        $scoringClass = ($formType === 'A') ? IntralaboralAScoring::class : IntralaboralBScoring::class;
        $baremoTotal = $scoringClass::getBaremoTotal();

        // Obtener workers completados con la forma correspondiente
        $workers = $this->workerModel
            ->where('battery_service_id', $serviceId)
            ->where('status', 'completado')
            ->where('intralaboral_type', $formType)
            ->findAll();

        if (empty($workers)) {
            return redirect()->back()->with('error', 'No hay trabajadores completados para validar');
        }

        $workerIds = array_column($workers, 'id');

        // Obtener resultados de BD
        $calculatedResults = $this->calculatedResultModel
            ->whereIn('worker_id', $workerIds)
            ->findAll();

        $resultsById = [];
        foreach ($calculatedResults as $result) {
            $resultsById[$result['worker_id']] = $result;
        }

        // Calcular puntajes totales usando la librería oficial
        $totalScores = [];
        foreach ($workers as $worker) {
            $responses = $this->responseModel
                ->where('worker_id', $worker['id'])
                ->whereIn('question_number', range(1, $formType === 'A' ? 123 : 97))
                ->findAll();

            $answersArray = [];
            foreach ($responses as $resp) {
                $answersArray[$resp['question_number']] = $resp['answer_value'];
            }

            if (empty($answersArray)) {
                continue;
            }

            // Calcular usando librería oficial
            $result = $scoringClass::calcular($answersArray, $worker['atiende_clientes'] === 'si', $worker['es_jefe'] === 'si');

            // Extraer puntajes totales
            $puntajeBruto = $result['puntaje_bruto_total'] ?? null;
            $puntajeTransformado = $result['puntaje_total_intralaboral'] ?? null;
            $nivel = $result['nivel_riesgo_total'] ?? null;

            $totalScores[] = [
                'worker_id' => $worker['id'],
                'worker_name' => $worker['name'],
                'puntaje_bruto' => $puntajeBruto,
                'puntaje_transformado' => $puntajeTransformado,
                'nivel_calculado' => $nivel,
                'nivel_bd' => $resultsById[$worker['id']]['nivel_riesgo_intralaboral'] ?? null,
                'puntaje_bd' => $resultsById[$worker['id']]['puntaje_total_intralaboral'] ?? null
            ];
        }

        // Calcular estadísticas
        $puntajes = array_column($totalScores, 'puntaje_transformado');
        $promedioCalculado = count($puntajes) > 0 ? round(array_sum($puntajes) / count($puntajes), 2) : 0;
        $promedioFromDB = count($totalScores) > 0
            ? round(array_sum(array_column($totalScores, 'puntaje_bd')) / count($totalScores), 2)
            : 0;

        // Determinar nivel según baremos
        $nivelCalculado = $this->getNivelRiesgo($promedioCalculado, $baremoTotal);

        $data = [
            'service' => $this->batteryServiceModel->find($serviceId),
            'formType' => $formType,
            'baremos' => $baremoTotal,
            'totalScores' => $totalScores,
            'promedioCalculado' => $promedioCalculado,
            'promedioFromDB' => $promedioFromDB,
            'nivelCalculado' => $nivelCalculado,
            'totalWorkers' => count($workers)
        ];

        return view('validation/total_detail', $data);
    }
}
