<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\BatteryServiceModel;
use App\Models\WorkerModel;
use App\Models\ResponseModel;
use App\Models\CalculatedResultModel;
use App\Models\ValidationResultModel;
use App\Libraries\IntralaboralAScoring;
use App\Libraries\IntralaboralBScoring;

class ValidationController extends BaseController
{
    protected $batteryServiceModel;
    protected $workerModel;
    protected $responseModel;
    protected $calculatedResultModel;
    protected $validationResultModel;

    public function __construct()
    {
        $this->batteryServiceModel = new BatteryServiceModel();
        $this->workerModel = new WorkerModel();
        $this->responseModel = new ResponseModel();
        $this->calculatedResultModel = new CalculatedResultModel();
        $this->validationResultModel = new ValidationResultModel();
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

        // Obtener dimensiones extralaboral
        $extralaboralDimensions = $this->getExtralaboralDimensions();

        // Verificar estado de procesamiento intralaboral
        $dimensionsProcessed = $this->validationResultModel->areDimensionsProcessed($serviceId, 'intralaboral', null);
        $domainsProcessed = $this->validationResultModel->areDomainsProcessed($serviceId, 'intralaboral', null);
        $errorsCount = $this->validationResultModel->countErrors($serviceId, 'intralaboral');

        // Verificar estado de procesamiento extralaboral
        $extralaboralDimensionsProcessedA = $this->validationResultModel->areDimensionsProcessed($serviceId, 'extralaboral', 'A');
        $extralaboralDimensionsProcessedB = $this->validationResultModel->areDimensionsProcessed($serviceId, 'extralaboral', 'B');
        $extralaboralTotalProcessedA = $this->validationResultModel->areTotalsProcessed($serviceId, 'extralaboral', 'A');
        $extralaboralTotalProcessedB = $this->validationResultModel->areTotalsProcessed($serviceId, 'extralaboral', 'B');
        $extralaboralErrorsCountA = $this->validationResultModel->countErrors($serviceId, 'extralaboral', 'A');
        $extralaboralErrorsCountB = $this->validationResultModel->countErrors($serviceId, 'extralaboral', 'B');

        $data = [
            'title' => 'Validación de Resultados - ' . $service['service_name'],
            'service' => $service,
            'workers' => $workers,
            'domainsFormaA' => $domainsFormaA,
            'domainsFormaB' => $domainsFormaB,
            'extralaboralDimensions' => $extralaboralDimensions,
            'dimensionsProcessed' => $dimensionsProcessed,
            'domainsProcessed' => $domainsProcessed,
            'errorsCount' => $errorsCount,
            'extralaboralDimensionsProcessedA' => $extralaboralDimensionsProcessedA,
            'extralaboralDimensionsProcessedB' => $extralaboralDimensionsProcessedB,
            'extralaboralTotalProcessedA' => $extralaboralTotalProcessedA,
            'extralaboralTotalProcessedB' => $extralaboralTotalProcessedB,
            'extralaboralErrorsCountA' => $extralaboralErrorsCountA,
            'extralaboralErrorsCountB' => $extralaboralErrorsCountB
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
     * Obtener nivel de riesgo según baremos (formato con min/max)
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
     * Obtener nivel de riesgo según baremos de librerías (formato [min, max])
     */
    private function getNivelRiesgoFromLibrary($puntaje, $baremos)
    {
        foreach ($baremos as $nivel => $rango) {
            // Formato de librerías: [min, max]
            if ($puntaje >= $rango[0] && $puntaje <= $rango[1]) {
                return $nivel;
            }
        }

        return 'desconocido';
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
     * Obtener dimensiones extralaboral desde la librería oficial
     */
    private function getExtralaboralDimensions()
    {
        $scoringLib = new \App\Libraries\ExtralaboralScoring();
        $reflection = new \ReflectionClass($scoringLib);

        // Obtener dimensiones
        $dimensionesProperty = $reflection->getProperty('dimensiones');
        $dimensionesProperty->setAccessible(true);
        $dimensiones = $dimensionesProperty->getValue($scoringLib);

        // Obtener nombres de dimensiones desde la librería (Single Source of Truth)
        $dimensionNames = \App\Libraries\ExtralaboralScoring::getNombresDimensiones();

        $result = [];

        foreach ($dimensiones as $dimensionKey => $items) {
            $itemCount = count($items);
            $minItem = min($items);
            $maxItem = max($items);

            $result[] = [
                'key' => $dimensionKey,
                'name' => $dimensionNames[$dimensionKey] ?? ucwords(str_replace('_', ' ', $dimensionKey)),
                'item_count' => $itemCount,
                'item_range' => "$minItem-$maxItem",
                'items' => $items
            ];
        }

        return $result;
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
     * Validación de Dominio (consolida dimensiones ya validadas)
     * Muestra las dimensiones que componen el dominio y sus puntajes
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

        // Obtener service
        $service = $this->batteryServiceModel->find($serviceId);

        // Obtener workers completados con la forma correspondiente
        $workers = $this->workerModel
            ->where('battery_service_id', $serviceId)
            ->where('status', 'completado')
            ->where('intralaboral_type', $formType)
            ->findAll();

        if (empty($workers)) {
            return redirect()->back()->with('error', 'No hay trabajadores completados para validar');
        }

        // Obtener resultados de dimensiones desde validation_results
        $dimensionResults = $this->validationResultModel->getDimensionResults($serviceId, 'intralaboral', $formType);

        // Filtrar solo las dimensiones de este dominio
        $dimensionsData = [];
        $sumPromedios = 0;

        foreach ($dimensionsInDomain as $dimensionKey) {
            // Buscar el resultado de esta dimensión
            $dimResult = array_filter($dimensionResults, function($r) use ($dimensionKey) {
                return $r['element_key'] === $dimensionKey;
            });

            if (!empty($dimResult)) {
                $dimResult = array_values($dimResult)[0];
                $dimensionsData[] = [
                    'key' => $dimensionKey,
                    'name' => $dimResult['element_name'],
                    'sum_averages' => $dimResult['sum_averages'],
                    'calculated_score' => $dimResult['calculated_score'],
                    'db_score' => $dimResult['db_score'],
                    'transformation_factor' => $dimResult['transformation_factor']
                ];
                $sumPromedios += $dimResult['sum_averages'];
            }
        }

        // Obtener factor de transformación del dominio
        $reflectionClass = new \ReflectionClass($scoringClass);
        $factoresProperty = $reflectionClass->getProperty('factoresTransformacionDominios');
        $factoresProperty->setAccessible(true);
        $factoresTransformacion = $factoresProperty->getValue();
        $factor = $factoresTransformacion[$domainKey];

        // Calcular puntaje transformado del dominio
        $puntajeTransformado = $factor > 0 ? round(($sumPromedios / $factor) * 100, 2) : 0;

        // Obtener puntaje de BD
        $dbField = $this->getDomainDbField($domainKey);
        $workerIds = array_column($workers, 'id');
        $calculatedResults = $this->calculatedResultModel->whereIn('worker_id', $workerIds)->findAll();
        $dbScores = array_column($calculatedResults, $dbField);
        $dbScore = count($dbScores) > 0 ? round(array_sum($dbScores) / count($dbScores), 2) : 0;

        // Comparación
        $difference = round($puntajeTransformado - $dbScore, 2);
        $status = abs($difference) < 0.1 ? 'ok' : 'error';

        $validation = [
            'dimensions' => $dimensionsData,
            'sum_promedios' => $sumPromedios,
            'transformation_factor' => $factor,
            'puntaje_transformado' => $puntajeTransformado,
            'db_comparison' => [
                'db_score' => $dbScore,
                'difference' => $difference,
                'status' => $status
            ]
        ];

        $data = [
            'service' => $service,
            'domainKey' => $domainKey,
            'domainName' => $this->getDomainDisplayName($domainKey),
            'formType' => $formType,
            'baremos' => $baremos,
            'validation' => $validation,
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
     * Obtiene el nombre del campo de BD para un dominio
     */
    private function getDomainDbField($domainKey)
    {
        $fields = [
            'liderazgo_relaciones_sociales' => 'dom_liderazgo_puntaje',
            'control' => 'dom_control_puntaje',
            'demandas' => 'dom_demandas_puntaje',
            'recompensas' => 'dom_recompensas_puntaje'
        ];
        return $fields[$domainKey] ?? null;
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

        // Obtener service
        $service = $this->batteryServiceModel->find($serviceId);

        // Obtener workers completados con la forma correspondiente
        $workers = $this->workerModel
            ->where('battery_service_id', $serviceId)
            ->where('status', 'completado')
            ->where('intralaboral_type', $formType)
            ->findAll();

        if (empty($workers)) {
            return redirect()->back()->with('error', 'No hay trabajadores completados para validar');
        }

        // Obtener resultados de dominios desde validation_results
        $domainResults = $this->validationResultModel->getDomainResults($serviceId, 'intralaboral', $formType);

        // Preparar datos de dominios
        $domainsData = [];
        $sumPromedios = 0;

        $dominiosKeys = ['liderazgo_relaciones_sociales', 'control', 'demandas', 'recompensas'];

        foreach ($dominiosKeys as $domainKey) {
            // Buscar el resultado de este dominio
            $domResult = array_filter($domainResults, function($r) use ($domainKey) {
                return $r['element_key'] === $domainKey;
            });

            if (!empty($domResult)) {
                $domResult = array_values($domResult)[0];
                $domainsData[] = [
                    'key' => $domainKey,
                    'name' => $domResult['element_name'],
                    'sum_averages' => $domResult['sum_averages'],
                    'calculated_score' => $domResult['calculated_score'],
                    'db_score' => $domResult['db_score'],
                    'transformation_factor' => $domResult['transformation_factor']
                ];
                $sumPromedios += $domResult['sum_averages'];
            }
        }

        // Obtener factor de transformación del total
        $reflectionClass = new \ReflectionClass($scoringClass);
        $factorProperty = $reflectionClass->getProperty('factorTransformacionTotal');
        $factorProperty->setAccessible(true);
        $factorTotal = $factorProperty->getValue();

        // Calcular puntaje transformado total
        $puntajeTransformado = $factorTotal > 0 ? round(($sumPromedios / $factorTotal) * 100, 2) : 0;

        // Obtener puntaje de BD
        $workerIds = array_column($workers, 'id');
        $calculatedResults = $this->calculatedResultModel->whereIn('worker_id', $workerIds)->findAll();
        $dbScores = array_column($calculatedResults, 'intralaboral_total_puntaje');
        $dbScore = count($dbScores) > 0 ? round(array_sum($dbScores) / count($dbScores), 2) : 0;

        // Comparación
        $difference = round($puntajeTransformado - $dbScore, 2);
        $status = abs($difference) < 0.1 ? 'ok' : 'error';

        // Preparar puntajes por trabajador
        $totalScores = [];
        $resultsById = [];
        foreach ($calculatedResults as $result) {
            $resultsById[$result['worker_id']] = $result;
        }

        foreach ($workers as $worker) {
            $workerId = $worker['id'];
            $workerResult = $resultsById[$workerId] ?? null;

            if ($workerResult) {
                // Calcular puntaje bruto sumando los 4 dominios desde BD
                $puntajeBruto = ($workerResult['dom_liderazgo_puntaje'] ?? 0) +
                               ($workerResult['dom_control_puntaje'] ?? 0) +
                               ($workerResult['dom_demandas_puntaje'] ?? 0) +
                               ($workerResult['dom_recompensas_puntaje'] ?? 0);

                $totalScores[] = [
                    'worker_name' => $worker['name'],
                    'puntaje_bruto' => $puntajeBruto,
                    'puntaje_transformado' => $workerResult['intralaboral_total_puntaje'] ?? 0,
                    'nivel_bd' => $workerResult['intralaboral_total_nivel'] ?? 'sin_datos',
                    'nivel_calculado' => $workerResult['intralaboral_total_nivel'] ?? 'sin_datos',
                    'puntaje_bd' => $workerResult['intralaboral_total_puntaje'] ?? 0
                ];
            }
        }

        $validation = [
            'domains' => $domainsData,
            'sum_promedios' => $sumPromedios,
            'transformation_factor' => $factorTotal,
            'puntaje_transformado' => $puntajeTransformado,
            'db_comparison' => [
                'db_score' => $dbScore,
                'difference' => $difference,
                'status' => $status
            ]
        ];

        $data = [
            'service' => $service,
            'formType' => $formType,
            'baremos' => $baremoTotal,
            'validation' => $validation,
            'totalScores' => $totalScores,
            'totalWorkers' => count($workers)
        ];

        return view('validation/total_detail', $data);
    }

    /**
     * Procesar todas las dimensiones de intralaboral para un servicio
     * Guarda los resultados en validation_results
     */
    public function processDimensions($serviceId)
    {
        $this->checkPermissions();

        $service = $this->batteryServiceModel->find($serviceId);
        if (!$service) {
            return redirect()->back()->with('error', 'Servicio no encontrado');
        }

        // Procesar Forma A
        $processedA = $this->processDimensionsByForm($serviceId, 'A');

        // Procesar Forma B
        $processedB = $this->processDimensionsByForm($serviceId, 'B');

        $totalProcessed = $processedA + $processedB;

        return redirect()->to('validation/' . $serviceId)
            ->with('success', "Dimensiones procesadas correctamente: {$totalProcessed} dimensiones guardadas");
    }

    /**
     * Procesar dimensiones de una forma específica (A o B)
     */
    private function processDimensionsByForm($serviceId, $formType)
    {
        // Obtener workers completados con esta forma
        $workers = $this->workerModel
            ->where('battery_service_id', $serviceId)
            ->where('status', 'completado')
            ->where('intralaboral_type', $formType)
            ->findAll();

        if (empty($workers)) {
            return 0;
        }

        $scoringClass = ($formType === 'A') ? IntralaboralAScoring::class : IntralaboralBScoring::class;

        // Obtener todas las dimensiones desde la librería
        $baremosDimensiones = $scoringClass::getBaremosDimensiones();

        $processed = 0;

        // Eliminar validaciones anteriores de dimensiones para esta forma
        $this->validationResultModel->deleteValidationLevel($serviceId, 'intralaboral', $formType, 'dimension');

        // Convertir formType para consultas de responses (A -> intralaboral_A)
        $responseFormType = 'intralaboral_' . $formType;

        // Procesar cada dimensión
        foreach (array_keys($baremosDimensiones) as $dimensionKey) {
            // Obtener configuración de esta dimensión específica
            $config = $this->getDimensionConfig($dimensionKey, $formType);

            if (!$config) {
                continue; // Skip si no se pudo obtener la configuración
            }
            // Calcular validación de la dimensión (usar responseFormType para queries)
            $validation = $this->calculateDimensionValidation($workers, $config, $responseFormType);

            // Obtener puntaje promedio de BD desde calculated_results
            $dbField = $config['db_field'];
            $workerIds = array_column($workers, 'id');

            // Obtener resultados calculados para estos workers
            $calculatedResults = $this->calculatedResultModel
                ->whereIn('worker_id', $workerIds)
                ->findAll();

            // Extraer los puntajes del campo específico de dimensión
            $dbScores = array_column($calculatedResults, $dbField);
            $dbScore = count($dbScores) > 0 ? array_sum($dbScores) / count($dbScores) : 0;

            // Preparar datos para guardar
            $data = [
                'battery_service_id' => $serviceId,
                'questionnaire_type' => 'intralaboral',
                'form_type' => $formType,
                'validation_level' => 'dimension',
                'element_key' => $dimensionKey,
                'element_name' => $config['name'],
                'total_workers' => count($workers),
                'sum_averages' => $validation['sum_promedios'],
                'transformation_factor' => $validation['transformation_factor'],
                'calculated_score' => $validation['puntaje_transformado'],
                'db_score' => round($dbScore, 2),
                'difference' => round($validation['puntaje_transformado'] - $dbScore, 2),
                'validation_status' => abs($validation['puntaje_transformado'] - $dbScore) < 0.1 ? 'ok' : 'error',
                'processed_at' => date('Y-m-d H:i:s'),
                'processed_by' => session()->get('id')
            ];

            $this->validationResultModel->insert($data);
            $processed++;
        }

        return $processed;
    }

    /**
     * Procesar todos los dominios de intralaboral para un servicio
     * Requiere que las dimensiones ya estén procesadas
     */
    public function processDomains($serviceId)
    {
        $this->checkPermissions();

        $service = $this->batteryServiceModel->find($serviceId);
        if (!$service) {
            return redirect()->back()->with('error', 'Servicio no encontrado');
        }

        // Verificar que las dimensiones estén procesadas
        $dimensionsAProcessed = $this->validationResultModel->areDimensionsProcessed($serviceId, 'intralaboral', 'A');
        $dimensionsBProcessed = $this->validationResultModel->areDimensionsProcessed($serviceId, 'intralaboral', 'B');

        if (!$dimensionsAProcessed && !$dimensionsBProcessed) {
            return redirect()->back()->with('error', 'Debe procesar las dimensiones primero');
        }

        $totalProcessed = 0;

        // Procesar Forma A si tiene dimensiones procesadas
        if ($dimensionsAProcessed) {
            $totalProcessed += $this->processDomainsByForm($serviceId, 'A');
        }

        // Procesar Forma B si tiene dimensiones procesadas
        if ($dimensionsBProcessed) {
            $totalProcessed += $this->processDomainsByForm($serviceId, 'B');
        }

        return redirect()->to('validation/' . $serviceId)
            ->with('success', "Dominios procesados correctamente: {$totalProcessed} dominios guardados");
    }

    /**
     * Procesar dominios de una forma específica (A o B)
     */
    private function processDomainsByForm($serviceId, $formType)
    {
        $scoringClass = ($formType === 'A') ? IntralaboralAScoring::class : IntralaboralBScoring::class;

        // Obtener dominios desde la librería
        $dominios = $scoringClass::getDominios();

        // Obtener resultados de dimensiones ya procesados
        $dimensionResults = $this->validationResultModel->getDimensionResults($serviceId, 'intralaboral', $formType);

        if (empty($dimensionResults)) {
            return 0;
        }

        // Obtener workers
        $workers = $this->workerModel
            ->where('battery_service_id', $serviceId)
            ->where('status', 'completado')
            ->where('intralaboral_type', $formType)
            ->findAll();

        $totalWorkers = count($workers);
        $processed = 0;

        // Eliminar validaciones anteriores de dominios para esta forma
        $this->validationResultModel->deleteValidationLevel($serviceId, 'intralaboral', $formType, 'domain');

        // Procesar cada dominio
        foreach ($dominios as $domainKey => $dimensionsInDomain) {
            // Sumar los puntajes de las dimensiones que componen este dominio
            $sumPromedios = 0;

            foreach ($dimensionsInDomain as $dimensionKey) {
                // Buscar el resultado de esta dimensión
                $dimResult = array_filter($dimensionResults, function($r) use ($dimensionKey) {
                    return $r['element_key'] === $dimensionKey;
                });

                if (!empty($dimResult)) {
                    $dimResult = array_values($dimResult)[0];
                    $sumPromedios += $dimResult['sum_averages'];
                }
            }

            // Obtener factor de transformación del dominio
            $reflectionClass = new \ReflectionClass($scoringClass);
            $factoresProperty = $reflectionClass->getProperty('factoresTransformacionDominios');
            $factoresProperty->setAccessible(true);
            $factoresTransformacion = $factoresProperty->getValue();
            $factor = $factoresTransformacion[$domainKey];

            // Calcular puntaje transformado
            $puntajeTransformado = $factor > 0 ? round(($sumPromedios / $factor) * 100, 2) : 0;

            // Obtener puntaje promedio de BD desde calculated_results
            $dbField = $this->getDomainDbField($domainKey);
            $workerIds = array_column($workers, 'id');

            // Obtener resultados calculados para estos workers
            $calculatedResults = $this->calculatedResultModel
                ->whereIn('worker_id', $workerIds)
                ->findAll();

            // Extraer los puntajes del campo específico de dominio
            $dbScores = array_column($calculatedResults, $dbField);
            $dbScore = count($dbScores) > 0 ? array_sum($dbScores) / count($dbScores) : 0;

            // Preparar datos para guardar
            $data = [
                'battery_service_id' => $serviceId,
                'questionnaire_type' => 'intralaboral',
                'form_type' => $formType,
                'validation_level' => 'domain',
                'element_key' => $domainKey,
                'element_name' => $this->getDomainDisplayName($domainKey),
                'total_workers' => $totalWorkers,
                'sum_averages' => round($sumPromedios, 2),
                'transformation_factor' => $factor,
                'calculated_score' => $puntajeTransformado,
                'db_score' => round($dbScore, 2),
                'difference' => round($puntajeTransformado - $dbScore, 2),
                'validation_status' => abs($puntajeTransformado - $dbScore) < 0.1 ? 'ok' : 'error',
                'processed_at' => date('Y-m-d H:i:s'),
                'processed_by' => session()->get('id')
            ];

            $this->validationResultModel->insert($data);
            $processed++;
        }

        return $processed;
    }

    /**
     * Process extralaboral dimensions validation and save to validation_results
     * POST /validation/process-dimensions-extralaboral/{serviceId}/{formType}
     *
     * Patrón AGREGADO como intralaboral: UN registro por dimensión con promedios de TODOS los workers
     */
    public function processDimensionsExtralaboral($serviceId, $formType = null)
    {
        $this->checkPermissions();

        $service = $this->batteryServiceModel->find($serviceId);
        if (!$service) {
            return redirect()->back()->with('error', 'Servicio no encontrado');
        }

        // Validar que formType sea A o B
        if (!in_array($formType, ['A', 'B'])) {
            return redirect()->back()->with('error', 'Tipo de formulario inválido');
        }

        // Obtener workers completados con esta forma
        $workers = $this->workerModel
            ->where('battery_service_id', $serviceId)
            ->where('status', 'completado')
            ->where('intralaboral_type', $formType)
            ->findAll();

        if (empty($workers)) {
            return redirect()->back()->with('error', "No hay trabajadores Forma {$formType}");
        }

        // Filtrar workers que tienen respuestas extralaboral
        $workersWithExtralaboral = [];
        foreach ($workers as $worker) {
            $hasResponses = $this->responseModel
                ->where('worker_id', $worker['id'])
                ->where('form_type', 'extralaboral')
                ->countAllResults() > 0;

            if ($hasResponses) {
                $workersWithExtralaboral[] = $worker;
            }
        }

        if (empty($workersWithExtralaboral)) {
            return redirect()->back()->with('error', "No hay trabajadores Forma {$formType} con cuestionario extralaboral completado");
        }

        $processed = 0;

        // Eliminar validaciones anteriores de dimensiones para esta forma
        $this->validationResultModel->deleteValidationLevel($serviceId, 'extralaboral', $formType, 'dimension');

        // Get configuration from ExtralaboralScoring library
        $scoringLib = new \App\Libraries\ExtralaboralScoring();
        $reflectionClass = new \ReflectionClass($scoringLib);

        $dimensionsProperty = $reflectionClass->getProperty('dimensiones');
        $dimensionsProperty->setAccessible(true);
        $dimensiones = $dimensionsProperty->getValue($scoringLib);

        $factorsProperty = $reflectionClass->getProperty('factoresTransformacion');
        $factorsProperty->setAccessible(true);
        $factores = $factorsProperty->getValue($scoringLib);

        // Obtener nombres de dimensiones desde la librería (Single Source of Truth)
        $dimensionNames = \App\Libraries\ExtralaboralScoring::getNombresDimensiones();

        // Mapeo de claves a columnas de BD
        $dbFieldMap = [
            'tiempo_fuera_trabajo' => 'extralaboral_tiempo_fuera_puntaje',
            'relaciones_familiares' => 'extralaboral_relaciones_familiares_puntaje',
            'comunicacion_relaciones' => 'extralaboral_comunicacion_puntaje',
            'situacion_economica' => 'extralaboral_situacion_economica_puntaje',
            'caracteristicas_vivienda' => 'extralaboral_caracteristicas_vivienda_puntaje',
            'influencia_entorno' => 'extralaboral_influencia_entorno_puntaje',
            'desplazamiento' => 'extralaboral_desplazamiento_puntaje'
        ];

        // Procesar cada dimensión
        foreach ($dimensiones as $dimensionKey => $itemKeys) {
            $factor = $factores[$dimensionKey] ?? 0;
            $dimensionName = $dimensionNames[$dimensionKey] ?? ucwords(str_replace('_', ' ', $dimensionKey));

            // Calcular SUMA DE PROMEDIOS de todos los ítems (igual que intralaboral)
            // Para cada ítem: subtotal = suma de scores de todos los workers
            //                 promedio = subtotal / cantidad de workers
            // sum_averages = SUMA de todos los promedios de ítems

            $workerIds = array_column($workersWithExtralaboral, 'id');
            $workerCount = count($workersWithExtralaboral);
            $sumPromedios = 0;

            // Procesar cada ítem de la dimensión
            foreach ($itemKeys as $itemNumber) {
                // Obtener TODAS las respuestas de este ítem de TODOS los workers
                $responses = $this->responseModel
                    ->whereIn('worker_id', $workerIds)
                    ->where('form_type', 'extralaboral')
                    ->where('question_number', $itemNumber)
                    ->findAll();

                if (count($responses) > 0) {
                    // Subtotal = suma de todos los scores individuales
                    $subtotal = array_sum(array_column($responses, 'answer_value'));

                    // Promedio del ítem = subtotal / cantidad de workers
                    $average = $subtotal / $workerCount;

                    // Acumular en la suma de promedios
                    $sumPromedios += $average;
                }
            }

            if ($workerCount === 0) {
                continue; // Skip if no workers have this dimension
            }

            // Calcular puntaje transformado
            $puntajeTransformado = $factor > 0 ? round(($sumPromedios / $factor) * 100, 2) : 0;

            // Obtener puntaje promedio de BD desde calculated_results
            $dbField = $dbFieldMap[$dimensionKey] ?? null;
            if ($dbField) {
                $workerIds = array_column($workersWithExtralaboral, 'id');
                $calculatedResults = $this->calculatedResultModel
                    ->whereIn('worker_id', $workerIds)
                    ->findAll();

                $dbScores = array_column($calculatedResults, $dbField);
                $dbScore = count($dbScores) > 0 ? array_sum($dbScores) / count($dbScores) : 0;
            } else {
                $dbScore = 0;
            }

            // Preparar datos para guardar
            $data = [
                'battery_service_id' => $serviceId,
                'questionnaire_type' => 'extralaboral',
                'form_type' => $formType,
                'validation_level' => 'dimension',
                'element_key' => $dimensionKey,
                'element_name' => $dimensionName,
                'total_workers' => $workerCount,
                'sum_averages' => round($sumPromedios, 2),
                'transformation_factor' => $factor,
                'calculated_score' => $puntajeTransformado,
                'db_score' => round($dbScore, 2),
                'difference' => round($puntajeTransformado - $dbScore, 2),
                'validation_status' => abs($puntajeTransformado - $dbScore) < 0.1 ? 'ok' : 'error',
                'processed_at' => date('Y-m-d H:i:s'),
                'processed_by' => session()->get('id')
            ];

            $this->validationResultModel->insert($data);
            $processed++;
        }

        $message = "Procesadas {$processed} dimensiones extralaboral Forma {$formType}.";
        return redirect()->to("/validation/{$serviceId}")
            ->with('success', $message);
    }

    /**
     * Process extralaboral total validation (cascade from dimensions)
     * POST /validation/process-total-extralaboral/{serviceId}/{formType}
     *
     * Patrón AGREGADO como intralaboral: UN registro total con promedios
     */
    public function processTotalExtralaboral($serviceId, $formType = null)
    {
        $this->checkPermissions();

        $service = $this->batteryServiceModel->find($serviceId);
        if (!$service) {
            return redirect()->back()->with('error', 'Servicio no encontrado');
        }

        // Validar que formType sea A o B
        if (!in_array($formType, ['A', 'B'])) {
            return redirect()->back()->with('error', 'Tipo de formulario inválido');
        }

        // Eliminar validaciones anteriores de total para esta forma
        $this->validationResultModel->deleteValidationLevel($serviceId, 'extralaboral', $formType, 'total');

        // Get dimension results from validation_results (CASCADE) for this form type
        $dimensionResults = $this->validationResultModel->getDimensionResults($serviceId, 'extralaboral', $formType);

        if (empty($dimensionResults)) {
            return redirect()->back()->with('error', 'Debe procesar primero las dimensiones extralaboral');
        }

        // Get configuration from ExtralaboralScoring library
        $scoringLib = new \App\Libraries\ExtralaboralScoring();
        $reflectionClass = new \ReflectionClass($scoringLib);

        // Get total transformation factor
        $factorProperty = $reflectionClass->getProperty('factorTransformacionTotal');
        $factorProperty->setAccessible(true);
        $factorTotal = $factorProperty->getValue($scoringLib);

        // Sumar los promedios de las 7 dimensiones
        $sumPromedios = array_sum(array_column($dimensionResults, 'sum_averages'));

        // Calcular puntaje transformado total
        $puntajeTransformado = $factorTotal > 0 ? round(($sumPromedios / $factorTotal) * 100, 2) : 0;

        // Obtener puntaje promedio de BD desde calculated_results
        // Usar los mismos workers que se usaron para dimensiones
        $totalWorkers = $dimensionResults[0]['total_workers'] ?? 0;

        // Obtener workers completados con esta forma
        $workers = $this->workerModel
            ->where('battery_service_id', $serviceId)
            ->where('status', 'completado')
            ->where('intralaboral_type', $formType)
            ->findAll();

        // Filtrar workers que tienen respuestas extralaboral
        $workersWithExtralaboral = [];
        foreach ($workers as $worker) {
            $hasResponses = $this->responseModel
                ->where('worker_id', $worker['id'])
                ->where('form_type', 'extralaboral')
                ->countAllResults() > 0;

            if ($hasResponses) {
                $workersWithExtralaboral[] = $worker;
            }
        }

        // Obtener puntajes de total extralaboral de BD
        $workerIds = array_column($workersWithExtralaboral, 'id');
        $calculatedResults = $this->calculatedResultModel
            ->whereIn('worker_id', $workerIds)
            ->findAll();

        $dbScores = array_column($calculatedResults, 'extralaboral_total_puntaje');
        $dbScore = count($dbScores) > 0 ? array_sum($dbScores) / count($dbScores) : 0;

        // Preparar datos para guardar
        $data = [
            'battery_service_id' => $serviceId,
            'questionnaire_type' => 'extralaboral',
            'form_type' => $formType,
            'validation_level' => 'total',
            'element_key' => 'total_extralaboral',
            'element_name' => 'Total Extralaboral',
            'total_workers' => $totalWorkers,
            'sum_averages' => round($sumPromedios, 2),
            'transformation_factor' => $factorTotal,
            'calculated_score' => $puntajeTransformado,
            'db_score' => round($dbScore, 2),
            'difference' => round($puntajeTransformado - $dbScore, 2),
            'validation_status' => abs($puntajeTransformado - $dbScore) < 0.1 ? 'ok' : 'error',
            'processed_at' => date('Y-m-d H:i:s'),
            'processed_by' => session()->get('id')
        ];

        $this->validationResultModel->insert($data);

        $message = "Procesado total extralaboral Forma {$formType}.";
        return redirect()->to("/validation/{$serviceId}")
            ->with('success', $message);
    }

    /**
     * View validation detail for a single extralaboral dimension
     * GET /validation/dimension-extralaboral/{serviceId}/{dimensionKey}/{formType}
     */
    public function validateDimensionExtralaboral($serviceId, $dimensionKey, $formType)
    {
        $permissionCheck = $this->checkPermissions();
        if ($permissionCheck) return $permissionCheck;

        $service = $this->batteryServiceModel->find($serviceId);
        if (!$service) {
            return redirect()->back()->with('error', 'Servicio no encontrado');
        }

        // Validar que formType sea A o B
        if (!in_array($formType, ['A', 'B'])) {
            return redirect()->back()->with('error', 'Tipo de formulario inválido');
        }

        // Obtener workers para esta forma
        $workers = $this->workerModel
            ->where('battery_service_id', $serviceId)
            ->where('intralaboral_type', $formType)
            ->where('status', 'completado')
            ->findAll();

        if (empty($workers)) {
            return redirect()->back()->with('error', 'No hay trabajadores completados para Forma ' . $formType);
        }

        // Obtener configuración de la dimensión desde ExtralaboralScoring
        $reflection = new \ReflectionClass('App\Libraries\ExtralaboralScoring');
        $dimensionesProperty = $reflection->getProperty('dimensiones');
        $dimensionesProperty->setAccessible(true);
        $dimensiones = $dimensionesProperty->getValue();

        if (!isset($dimensiones[$dimensionKey])) {
            return redirect()->back()->with('error', 'Dimensión no encontrada');
        }

        $itemNumbers = $dimensiones[$dimensionKey];

        // Get dimension name from library
        $dimensionName = \App\Libraries\ExtralaboralScoring::getNombreDimension($dimensionKey);

        // Obtener ítems inversos desde la librería (Tabla 11 - Single Source of Truth)
        $itemsInversos = \App\Libraries\ExtralaboralScoring::getItemsInversos();

        // Determinar cuáles ítems de esta dimensión son inversos
        $inverseItemsInDimension = array_intersect($itemNumbers, $itemsInversos);

        // Construir configuración de dimensión similar a intralaboral
        $dimensionConfig = [
            'name' => $dimensionName,
            'items' => $itemNumbers,
            'inverse_items' => array_values($inverseItemsInDimension)
        ];

        // Calcular validación detallada por ítem (igual que intralaboral)
        $validationData = $this->calculateExtralaboralDimensionValidation($workers, $dimensionConfig, $dimensionKey);

        // Get baremos for this dimension using static method
        $baremos = \App\Libraries\ExtralaboralScoring::getBaremoDimension($dimensionKey, $formType);
        $baremosConMetadata = \App\Libraries\ExtralaboralScoring::getBaremoConMetadata($baremos);

        $data = [
            'service' => $service,
            'dimensionKey' => $dimensionKey,
            'dimensionName' => $dimensionName,
            'dimensionConfig' => $dimensionConfig,
            'formType' => $formType,
            'workers' => $workers,
            'validationData' => $validationData,
            'baremos' => $baremosConMetadata
        ];

        return view('validation/dimension_extralaboral_detail', $data);
    }

    /**
     * Calculate validation for extralaboral dimension (similar to intralaboral)
     */
    private function calculateExtralaboralDimensionValidation($workers, $dimensionConfig, $dimensionKey)
    {
        $items = $dimensionConfig['items'];
        $itemsData = [];
        $totalParticipants = count($workers);

        // Por cada ítem de la dimensión
        foreach ($items as $itemNumber) {
            // Determinar si el ítem es inverso usando la librería (Tabla 11 - Single Source of Truth)
            $isInverse = in_array($itemNumber, $dimensionConfig['inverse_items']);

            // Valores de scoring según tipo de ítem (normal o inverso) - Tabla 11
            $scoreValues = $isInverse
                ? ['siempre' => 4, 'casi_siempre' => 3, 'algunas_veces' => 2, 'casi_nunca' => 1, 'nunca' => 0]  // Grupo 2
                : ['siempre' => 0, 'casi_siempre' => 1, 'algunas_veces' => 2, 'casi_nunca' => 3, 'nunca' => 4]; // Grupo 1

            $itemData = [
                'item_number' => $itemNumber,
                'participants' => $totalParticipants,
                'responses' => [
                    'siempre' => 0,
                    'casi_siempre' => 0,
                    'algunas_veces' => 0,
                    'casi_nunca' => 0,
                    'nunca' => 0
                ],
                'score_values' => $scoreValues,
                'scores' => [
                    'siempre' => 0,
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
                ->where('form_type', 'extralaboral')
                ->where('question_number', $itemNumber)
                ->findAll();

            // Contar respuestas y calcular puntajes
            foreach ($responses as $response) {
                $answerValue = (int)$response['answer_value'];

                switch ($answerValue) {
                    case 0: $key = 'siempre'; break;
                    case 1: $key = 'casi_siempre'; break;
                    case 2: $key = 'algunas_veces'; break;
                    case 3: $key = 'casi_nunca'; break;
                    case 4: $key = 'nunca'; break;
                    default: continue 2;
                }

                $itemData['responses'][$key]++;
                $itemData['scores'][$key] = $itemData['responses'][$key] * $scoreValues[$key];
            }

            // Calcular subtotal y promedio
            $itemData['subtotal'] = array_sum($itemData['scores']);
            $itemData['average'] = $totalParticipants > 0 ? $itemData['subtotal'] / $totalParticipants : 0;

            // Validar suma de respuestas
            $totalResponses = array_sum($itemData['responses']);
            $itemData['response_count_valid'] = ($totalResponses === $totalParticipants);
            $itemData['response_count_difference'] = $totalResponses - $totalParticipants;

            // Validación de estado (comparar con average esperado)
            $itemData['status'] = 'ok';

            $itemsData[] = $itemData;
        }

        // Calcular puntaje total de la dimensión
        $sumaPromedios = array_sum(array_column($itemsData, 'average'));

        // Obtener factor de transformación para esta dimensión
        $factor = \App\Libraries\ExtralaboralScoring::getFactorDimension($dimensionKey);
        $puntajeTransformado = ($sumaPromedios / $factor) * 100;

        // Obtener puntaje de BD desde validation_results (promedio agregado, NO de un worker individual)
        $validationResult = $this->validationResultModel
            ->where('battery_service_id', $workers[0]['battery_service_id'])
            ->where('questionnaire_type', 'extralaboral')
            ->where('validation_level', 'dimension')
            ->where('element_key', $dimensionKey)
            ->where('form_type', strtoupper($workers[0]['intralaboral_type']))
            ->first();

        $dbScore = $validationResult ? (float)$validationResult['db_score'] : 0;

        $difference = abs($puntajeTransformado - $dbScore);
        $status = ($difference < 0.1) ? 'ok' : 'error';

        // Determinar nivel de riesgo
        $baremos = \App\Libraries\ExtralaboralScoring::getBaremoDimension($dimensionKey, 'A');
        $nivelRiesgoData = \App\Libraries\ExtralaboralScoring::getNivelRiesgo($puntajeTransformado, $baremos);

        return [
            'items' => $itemsData,
            'suma_promedios' => $sumaPromedios,
            'factor' => $factor,
            'puntaje_transformado' => $puntajeTransformado,
            'db_comparison' => [
                'db_score' => $dbScore,
                'difference' => $difference,
                'status' => $status
            ],
            'nivel_riesgo' => $nivelRiesgoData ?: ['label' => 'N/A', 'color' => 'secondary']
        ];
    }

    /**
     * View validation detail for extralaboral total
     * GET /validation/total-extralaboral/{serviceId}/{formType}
     */
    public function validateTotalExtralaboral($serviceId, $formType)
    {
        $permissionCheck = $this->checkPermissions();
        if ($permissionCheck) return $permissionCheck;

        $service = $this->batteryServiceModel->find($serviceId);
        if (!$service) {
            return redirect()->back()->with('error', 'Servicio no encontrado');
        }

        // Validar que formType sea A o B
        if (!in_array($formType, ['A', 'B'])) {
            return redirect()->back()->with('error', 'Tipo de formulario inválido');
        }

        // Get total results from validation_results for this form type
        $totalResults = $this->validationResultModel
            ->where('battery_service_id', $serviceId)
            ->where('questionnaire_type', 'extralaboral')
            ->where('validation_level', 'total')
            ->where('form_type', $formType)
            ->findAll();

        if (empty($totalResults)) {
            return redirect()->back()->with('error', 'No hay resultados procesados para el total extralaboral');
        }

        // Get total baremos using static method
        $baremos = \App\Libraries\ExtralaboralScoring::getBaremoTotal($formType);

        // Factor de transformación total desde la librería (Single Source of Truth - Tabla 14)
        $factorTotal = \App\Libraries\ExtralaboralScoring::getFactorTransformacionTotal();

        // Calculate statistics
        // Solo hay 1 registro agregado, extraer total_workers de ahí
        $totalWorkers = $totalResults[0]['total_workers'] ?? 0;
        $totalOk = 0;
        $totalErrors = 0;

        foreach ($totalResults as $result) {
            if ($result['validation_status'] === 'ok') {
                $totalOk++;
            } else {
                $totalErrors++;
            }
        }

        // Agregar metadata a baremos (labels y colores)
        $baremosConMetadata = \App\Libraries\ExtralaboralScoring::getBaremoConMetadata($baremos);

        $data = [
            'service' => $service,
            'formType' => $formType,
            'results' => $totalResults,
            'baremos' => $baremosConMetadata,
            'factorTotal' => $factorTotal,
            'totalWorkers' => $totalWorkers,
            'totalOk' => $totalOk,
            'totalErrors' => $totalErrors
        ];

        return view('validation/total_extralaboral_detail', $data);
    }
}
