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
        $intralaboralTotalProcessedA = $this->validationResultModel->areTotalsProcessed($serviceId, 'intralaboral', 'A');
        $intralaboralTotalProcessedB = $this->validationResultModel->areTotalsProcessed($serviceId, 'intralaboral', 'B');
        $errorsCount = $this->validationResultModel->countErrors($serviceId, 'intralaboral');

        // Obtener registros con error de intralaboral para mostrar en detalle
        $intralaboralErrorsCountA = $this->validationResultModel->countErrors($serviceId, 'intralaboral', 'A');
        $intralaboralErrorsCountB = $this->validationResultModel->countErrors($serviceId, 'intralaboral', 'B');
        $intralaboralErrorsA = [];
        $intralaboralErrorsB = [];
        if ($intralaboralErrorsCountA > 0) {
            $intralaboralErrorsA = $this->validationResultModel
                ->where('battery_service_id', $serviceId)
                ->where('questionnaire_type', 'intralaboral')
                ->where('form_type', 'A')
                ->where('validation_status', 'error')
                ->findAll();
        }
        if ($intralaboralErrorsCountB > 0) {
            $intralaboralErrorsB = $this->validationResultModel
                ->where('battery_service_id', $serviceId)
                ->where('questionnaire_type', 'intralaboral')
                ->where('form_type', 'B')
                ->where('validation_status', 'error')
                ->findAll();
        }

        // Verificar estado de procesamiento extralaboral
        $extralaboralDimensionsProcessedA = $this->validationResultModel->areDimensionsProcessed($serviceId, 'extralaboral', 'A');
        $extralaboralDimensionsProcessedB = $this->validationResultModel->areDimensionsProcessed($serviceId, 'extralaboral', 'B');
        $extralaboralTotalProcessedA = $this->validationResultModel->areTotalsProcessed($serviceId, 'extralaboral', 'A');
        $extralaboralTotalProcessedB = $this->validationResultModel->areTotalsProcessed($serviceId, 'extralaboral', 'B');
        $extralaboralErrorsCountA = $this->validationResultModel->countErrors($serviceId, 'extralaboral', 'A');
        $extralaboralErrorsCountB = $this->validationResultModel->countErrors($serviceId, 'extralaboral', 'B');

        // Obtener dimensiones con error para mostrar en detalle
        $extralaboralErrorsA = [];
        $extralaboralErrorsB = [];
        if ($extralaboralErrorsCountA > 0) {
            $extralaboralErrorsA = $this->validationResultModel
                ->where('battery_service_id', $serviceId)
                ->where('questionnaire_type', 'extralaboral')
                ->where('form_type', 'A')
                ->where('validation_status', 'error')
                ->findAll();
        }
        if ($extralaboralErrorsCountB > 0) {
            $extralaboralErrorsB = $this->validationResultModel
                ->where('battery_service_id', $serviceId)
                ->where('questionnaire_type', 'extralaboral')
                ->where('form_type', 'B')
                ->where('validation_status', 'error')
                ->findAll();
        }

        // Verificar estado de procesamiento estrés
        $estresTotalProcessedA = $this->validationResultModel->areTotalsProcessed($serviceId, 'estres', 'A');
        $estresTotalProcessedB = $this->validationResultModel->areTotalsProcessed($serviceId, 'estres', 'B');
        $estresErrorsCountA = $this->validationResultModel->countErrors($serviceId, 'estres', 'A');
        $estresErrorsCountB = $this->validationResultModel->countErrors($serviceId, 'estres', 'B');

        // Obtener registros con error para mostrar en detalle
        $estresErrorsA = [];
        $estresErrorsB = [];
        if ($estresErrorsCountA > 0) {
            $estresErrorsA = $this->validationResultModel
                ->where('battery_service_id', $serviceId)
                ->where('questionnaire_type', 'estres')
                ->where('form_type', 'A')
                ->where('validation_status', 'error')
                ->findAll();
        }
        if ($estresErrorsCountB > 0) {
            $estresErrorsB = $this->validationResultModel
                ->where('battery_service_id', $serviceId)
                ->where('questionnaire_type', 'estres')
                ->where('form_type', 'B')
                ->where('validation_status', 'error')
                ->findAll();
        }

        $data = [
            'title' => 'Validación de Resultados - ' . $service['service_name'],
            'service' => $service,
            'workers' => $workers,
            'domainsFormaA' => $domainsFormaA,
            'domainsFormaB' => $domainsFormaB,
            'extralaboralDimensions' => $extralaboralDimensions,
            'dimensionsProcessed' => $dimensionsProcessed,
            'domainsProcessed' => $domainsProcessed,
            'intralaboralTotalProcessedA' => $intralaboralTotalProcessedA,
            'intralaboralTotalProcessedB' => $intralaboralTotalProcessedB,
            'intralaboralErrorsCountA' => $intralaboralErrorsCountA,
            'intralaboralErrorsCountB' => $intralaboralErrorsCountB,
            'intralaboralErrorsA' => $intralaboralErrorsA,
            'intralaboralErrorsB' => $intralaboralErrorsB,
            'errorsCount' => $errorsCount,
            'extralaboralDimensionsProcessedA' => $extralaboralDimensionsProcessedA,
            'extralaboralDimensionsProcessedB' => $extralaboralDimensionsProcessedB,
            'extralaboralTotalProcessedA' => $extralaboralTotalProcessedA,
            'extralaboralTotalProcessedB' => $extralaboralTotalProcessedB,
            'extralaboralErrorsCountA' => $extralaboralErrorsCountA,
            'extralaboralErrorsCountB' => $extralaboralErrorsCountB,
            'extralaboralErrorsA' => $extralaboralErrorsA,
            'extralaboralErrorsB' => $extralaboralErrorsB,
            'estresTotalProcessedA' => $estresTotalProcessedA,
            'estresTotalProcessedB' => $estresTotalProcessedB,
            'estresErrorsCountA' => $estresErrorsCountA,
            'estresErrorsCountB' => $estresErrorsCountB,
            'estresErrorsA' => $estresErrorsA,
            'estresErrorsB' => $estresErrorsB
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
        $this->validationResultModel->deletePreviousValidations($serviceId, 'intralaboral', $formType, 'dimension');

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
        $this->validationResultModel->deletePreviousValidations($serviceId, 'intralaboral', $formType, 'domain');

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
     * Process intralaboral total validation (cascade from domains)
     * POST /validation/process-total-intralaboral/{serviceId}/{formType}
     *
     * Patrón CASCADE: Suma los promedios de los dominios ya procesados
     */
    public function processIntralaboralTotal($serviceId, $formType = null)
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

        // Verificar que los dominios estén procesados
        $domainsProcessed = $this->validationResultModel->areDomainsProcessed($serviceId, 'intralaboral', $formType);
        if (!$domainsProcessed) {
            return redirect()->back()->with('error', 'Debe procesar primero los dominios intralaboral');
        }

        // Eliminar validaciones anteriores de total para esta forma
        $this->validationResultModel->deletePreviousValidations($serviceId, 'intralaboral', $formType, 'total');

        // Get domain results from validation_results (CASCADE) for this form type
        $domainResults = $this->validationResultModel->getDomainResults($serviceId, 'intralaboral', $formType);

        if (empty($domainResults)) {
            return redirect()->back()->with('error', 'Debe procesar primero los dominios intralaboral');
        }

        // Get total transformation factor from library (Single Source of Truth - Tabla 27)
        $scoringClass = ($formType === 'A') ? IntralaboralAScoring::class : IntralaboralBScoring::class;
        $factorTotal = $scoringClass::getFactorTransformacionIntralaboral();

        // Sumar los promedios de los 4 dominios
        $sumPromedios = array_sum(array_column($domainResults, 'sum_averages'));

        // Calcular puntaje transformado total
        $puntajeTransformado = $factorTotal > 0 ? round(($sumPromedios / $factorTotal) * 100, 2) : 0;

        // Obtener puntaje promedio de BD desde calculated_results
        // Usar los mismos workers que se usaron para dominios
        $totalWorkers = $domainResults[0]['total_workers'] ?? 0;

        // Obtener workers completados con esta forma
        $workers = $this->workerModel
            ->where('battery_service_id', $serviceId)
            ->where('status', 'completado')
            ->where('intralaboral_type', $formType)
            ->findAll();

        // Obtener puntajes de total intralaboral de BD
        $workerIds = array_column($workers, 'id');
        $calculatedResults = $this->calculatedResultModel
            ->whereIn('worker_id', $workerIds)
            ->findAll();

        $dbScores = array_column($calculatedResults, 'intralaboral_total_puntaje');
        $dbScore = count($dbScores) > 0 ? array_sum($dbScores) / count($dbScores) : 0;

        // Redondear valores PRIMERO, luego calcular diferencia
        $calculatedScoreRounded = round($puntajeTransformado, 2);
        $dbScoreRounded = round($dbScore, 2);
        $difference = round($calculatedScoreRounded - $dbScoreRounded, 2);

        // Preparar datos para guardar
        $data = [
            'battery_service_id' => $serviceId,
            'questionnaire_type' => 'intralaboral',
            'form_type' => $formType,
            'validation_level' => 'total',
            'element_key' => 'total_intralaboral',
            'element_name' => 'Total Intralaboral',
            'total_workers' => $totalWorkers,
            'sum_averages' => round($sumPromedios, 2),
            'transformation_factor' => $factorTotal,
            'calculated_score' => $calculatedScoreRounded,
            'db_score' => $dbScoreRounded,
            'difference' => $difference,
            'validation_status' => abs($difference) < 0.1 ? 'ok' : 'error',
            'processed_at' => date('Y-m-d H:i:s'),
            'processed_by' => session()->get('id')
        ];

        $this->validationResultModel->insert($data);

        $message = "Procesado total intralaboral Forma {$formType}.";
        return redirect()->to("/validation/{$serviceId}")
            ->with('success', $message);
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
        $this->validationResultModel->deletePreviousValidations($serviceId, 'extralaboral', $formType, 'dimension');

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

            // Obtener ítems inversos desde la librería (Tabla 11 - Single Source of Truth)
            $itemsInversos = \App\Libraries\ExtralaboralScoring::getItemsInversos();

            // Procesar cada ítem de la dimensión
            foreach ($itemKeys as $itemNumber) {
                // Obtener TODAS las respuestas de este ítem de TODOS los workers
                $responses = $this->responseModel
                    ->whereIn('worker_id', $workerIds)
                    ->where('form_type', 'extralaboral')
                    ->where('question_number', $itemNumber)
                    ->findAll();

                if (count($responses) > 0) {
                    // Determinar si el ítem es inverso (Grupo 2)
                    $isInverse = in_array($itemNumber, $itemsInversos);

                    // Subtotal = suma de todos los scores individuales CON inversión si aplica
                    $subtotal = 0;
                    foreach ($responses as $resp) {
                        $rawValue = $resp['answer_value'];
                        // Aplicar inversión si el ítem es Grupo 2 (Tabla 11)
                        $scoredValue = $isInverse ? (4 - $rawValue) : $rawValue;
                        $subtotal += $scoredValue;
                    }

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
        $this->validationResultModel->deletePreviousValidations($serviceId, 'extralaboral', $formType, 'total');

        // Get dimension results from validation_results (CASCADE) for this form type
        $dimensionResults = $this->validationResultModel->getDimensionResults($serviceId, 'extralaboral', $formType);

        if (empty($dimensionResults)) {
            return redirect()->back()->with('error', 'Debe procesar primero las dimensiones extralaboral');
        }

        // Get total transformation factor from library (Single Source of Truth - Tabla 14)
        $factorTotal = \App\Libraries\ExtralaboralScoring::getFactorTransformacionTotal();

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
     * Process stress (estrés) total validation
     * POST /validation/process-estres/{serviceId}/{formType}
     *
     * Patrón AGREGADO: UN registro total con sum_averages de TODOS los ítems (1-31)
     * Sin dimensiones intermedias (estrés no tiene dimensiones, solo total)
     */
    public function processEstres($serviceId, $formType = null)
    {
        $this->checkPermissions();

        log_message('info', '========================================');
        log_message('info', "INICIO PROCESAMIENTO ESTRÉS - Servicio: {$serviceId}, Forma: {$formType}");
        log_message('info', '========================================');

        $service = $this->batteryServiceModel->find($serviceId);
        if (!$service) {
            log_message('error', "Servicio no encontrado: {$serviceId}");
            return redirect()->back()->with('error', 'Servicio no encontrado');
        }

        log_message('info', "Servicio encontrado: ID={$serviceId}, Nombre={$service['service_name']}");

        // Validar que formType sea A o B
        if (!in_array($formType, ['A', 'B'])) {
            log_message('error', "Tipo de formulario inválido: {$formType}");
            return redirect()->back()->with('error', 'Tipo de formulario inválido');
        }

        log_message('info', "Tipo de formulario validado: Forma {$formType}");

        // Eliminar validaciones anteriores de estrés para esta forma
        log_message('info', "Eliminando validaciones anteriores para Forma {$formType}...");
        $this->validationResultModel->deletePreviousValidations($serviceId, 'estres', $formType, 'total');
        log_message('info', "Validaciones anteriores eliminadas");

        // Obtener workers completados con esta forma
        log_message('info', "Buscando workers con status=completado y intralaboral_type={$formType}...");
        $workers = $this->workerModel
            ->where('battery_service_id', $serviceId)
            ->where('status', 'completado')
            ->where('intralaboral_type', $formType)
            ->findAll();

        log_message('info', "Workers encontrados (completados): " . count($workers));

        if (empty($workers)) {
            log_message('error', "No hay trabajadores Forma {$formType}");
            return redirect()->back()->with('error', "No hay trabajadores Forma {$formType}");
        }

        // Filtrar workers que tienen respuestas de estrés
        log_message('info', "Filtrando workers con respuestas de estrés...");
        $workersWithEstres = [];
        foreach ($workers as $worker) {
            $hasResponses = $this->responseModel
                ->where('worker_id', $worker['id'])
                ->where('form_type', 'estres')
                ->countAllResults() > 0;

            if ($hasResponses) {
                $workersWithEstres[] = $worker;
                log_message('debug', "  Worker {$worker['id']} ({$worker['name']}) - TIENE respuestas estrés");
            } else {
                log_message('debug', "  Worker {$worker['id']} ({$worker['name']}) - NO tiene respuestas estrés");
            }
        }

        log_message('info', "Workers con respuestas de estrés: " . count($workersWithEstres));

        if (empty($workersWithEstres)) {
            log_message('error', "No hay trabajadores Forma {$formType} con cuestionario de estrés completado");
            return redirect()->back()->with('error', "No hay trabajadores Forma {$formType} con cuestionario de estrés completado");
        }

        // CALCULAR PUNTAJES INDIVIDUALES según Tabla 4 - Paso 3
        // METODOLOGÍA CORRECTA:
        // 1. Para CADA worker individual: aplicar a-b-c-d
        // 2. Promediar los puntajes transformados individuales
        // 3. Comparar con promedio de BD
        $workerIds = array_column($workersWithEstres, 'id');
        $workerCount = count($workersWithEstres);

        log_message('info', '========================================');
        log_message('info', "CÁLCULO DE PUNTAJES INDIVIDUALES");
        log_message('info', "Worker IDs: [" . implode(', ', $workerIds) . "]");
        log_message('info', "Total workers: {$workerCount}");
        log_message('info', '========================================');

        // Array para almacenar puntajes transformados individuales
        $puntajesTransformadosIndividuales = [];
        $workerNum = 1;

        // CALCULAR PUNTAJE INDIVIDUAL DE CADA WORKER
        foreach ($workersWithEstres as $worker) {
            $workerId = $worker['id'];

            log_message('info', "--- Worker {$workerNum}/{$workerCount}: ID={$workerId}, Nombre={$worker['name']} ---");

            // Obtener TODAS las respuestas de este worker
            $responses = $this->responseModel
                ->where('worker_id', $workerId)
                ->where('form_type', 'estres')
                ->findAll();

            if (count($responses) < 31) {
                log_message('warning', "  ⚠️ Worker {$workerId} tiene solo " . count($responses) . " respuestas (se requieren 31)");
                $workerNum++;
                continue;
            }

            // Organizar respuestas por número de pregunta
            $respuestasPorPregunta = [];
            foreach ($responses as $resp) {
                $respuestasPorPregunta[$resp['question_number']] = $resp['answer_value'];
            }

            // Aplicar metodología a-b-c-d POR ESTE WORKER
            // a. Promedio ítems 1-8 × 4
            $suma_1_8 = 0;
            for ($i = 1; $i <= 8; $i++) {
                $suma_1_8 += isset($respuestasPorPregunta[$i]) ? $respuestasPorPregunta[$i] : 0;
            }
            $promedio_1_8 = $suma_1_8 / 8;
            $subtotal_a = $promedio_1_8 * 4;

            // b. Promedio ítems 9-12 × 3
            $suma_9_12 = 0;
            for ($i = 9; $i <= 12; $i++) {
                $suma_9_12 += isset($respuestasPorPregunta[$i]) ? $respuestasPorPregunta[$i] : 0;
            }
            $promedio_9_12 = $suma_9_12 / 4;
            $subtotal_b = $promedio_9_12 * 3;

            // c. Promedio ítems 13-22 × 2
            $suma_13_22 = 0;
            for ($i = 13; $i <= 22; $i++) {
                $suma_13_22 += isset($respuestasPorPregunta[$i]) ? $respuestasPorPregunta[$i] : 0;
            }
            $promedio_13_22 = $suma_13_22 / 10;
            $subtotal_c = $promedio_13_22 * 2;

            // d. Promedio ítems 23-31
            $suma_23_31 = 0;
            for ($i = 23; $i <= 31; $i++) {
                $suma_23_31 += isset($respuestasPorPregunta[$i]) ? $respuestasPorPregunta[$i] : 0;
            }
            $promedio_23_31 = $suma_23_31 / 9;
            $subtotal_d = $promedio_23_31;

            // Puntaje bruto individual
            $puntajeBrutoIndividual = $subtotal_a + $subtotal_b + $subtotal_c + $subtotal_d;

            // Transformar (Tabla 4 - Paso 4)
            $factorTotal = \App\Libraries\EstresScoring::getFactorTransformacion();
            $puntajeTransformadoIndividual = ($puntajeBrutoIndividual / $factorTotal) * 100;

            $puntajesTransformadosIndividuales[] = $puntajeTransformadoIndividual;

            // Log solo para primeros 3 workers
            if ($workerNum <= 3) {
                log_message('debug', "  a. Ítems 1-8: suma={$suma_1_8}, promedio=" . number_format($promedio_1_8, 4) . ", ×4 = " . number_format($subtotal_a, 4));
                log_message('debug', "  b. Ítems 9-12: suma={$suma_9_12}, promedio=" . number_format($promedio_9_12, 4) . ", ×3 = " . number_format($subtotal_b, 4));
                log_message('debug', "  c. Ítems 13-22: suma={$suma_13_22}, promedio=" . number_format($promedio_13_22, 4) . ", ×2 = " . number_format($subtotal_c, 4));
                log_message('debug', "  d. Ítems 23-31: suma={$suma_23_31}, promedio=" . number_format($promedio_23_31, 4) . ", ×1 = " . number_format($subtotal_d, 4));
                log_message('debug', "  Puntaje bruto: " . number_format($puntajeBrutoIndividual, 4));
                log_message('debug', "  Puntaje transformado: " . number_format($puntajeTransformadoIndividual, 2) . "%");
            }

            $workerNum++;
        }

        // Calcular PROMEDIO de puntajes transformados individuales
        $puntajeTransformado = count($puntajesTransformadosIndividuales) > 0
            ? array_sum($puntajesTransformadosIndividuales) / count($puntajesTransformadosIndividuales)
            : 0;

        log_message('info', '========================================');
        log_message('info', "AGREGADO (Promedio de puntajes individuales)");
        log_message('info', "Puntajes individuales calculados: " . count($puntajesTransformadosIndividuales));
        log_message('info', "Primeros 5: [" . implode(', ', array_map(function($v) { return number_format($v, 2); }, array_slice($puntajesTransformadosIndividuales, 0, 5))) . "]");
        log_message('info', "Suma total: " . number_format(array_sum($puntajesTransformadosIndividuales), 2));
        log_message('info', "PROMEDIO DE TRANSFORMADOS: " . number_format($puntajeTransformado, 2) . "%");
        log_message('info', '========================================');

        // Obtener puntaje promedio de BD desde calculated_results
        log_message('info', "Obteniendo puntajes de BD desde calculated_results...");
        $calculatedResults = $this->calculatedResultModel
            ->whereIn('worker_id', $workerIds)
            ->findAll();

        log_message('info', "Registros encontrados en calculated_results: " . count($calculatedResults));

        $dbScores = array_column($calculatedResults, 'estres_total_puntaje');
        $dbScoresFiltered = array_filter($dbScores, function($score) {
            return $score !== null && $score !== '';
        });

        log_message('info', "Puntajes NO NULL: " . count($dbScoresFiltered));

        if (count($dbScoresFiltered) > 0) {
            $dbScore = array_sum($dbScoresFiltered) / count($dbScoresFiltered);
            log_message('debug', "Primeros 10 puntajes de BD: [" . implode(', ', array_slice($dbScoresFiltered, 0, 10)) . "]");
            log_message('info', "Suma de puntajes BD: " . array_sum($dbScoresFiltered));
            log_message('info', "Cantidad para promedio: " . count($dbScoresFiltered));
        } else {
            $dbScore = 0;
            log_message('warning', "NO HAY PUNTAJES EN BD");
        }

        log_message('info', "Promedio de puntajes BD: " . number_format($dbScore, 2));

        // Redondear los valores PRIMERO
        $calculatedScoreRounded = round($puntajeTransformado, 2);
        $dbScoreRounded = round($dbScore, 2);

        // Calcular la diferencia SOBRE LOS VALORES REDONDEADOS
        $difference = round($calculatedScoreRounded - $dbScoreRounded, 2);
        $validationStatus = abs($difference) < 0.1 ? 'ok' : 'error';

        log_message('info', '========================================');
        log_message('info', "COMPARACIÓN FINAL");
        log_message('info', "Puntaje calculado (Validador): {$puntajeTransformado} → redondeado: {$calculatedScoreRounded}");
        log_message('info', "Puntaje promedio BD (Aplicativo): {$dbScore} → redondeado: {$dbScoreRounded}");
        log_message('info', "Diferencia: {$difference}");
        log_message('info', "Estado: {$validationStatus}");
        log_message('info', '========================================');

        // Preparar datos para guardar
        $data = [
            'battery_service_id' => $serviceId,
            'questionnaire_type' => 'estres',
            'form_type' => $formType,
            'validation_level' => 'total',
            'element_key' => 'total_estres',
            'element_name' => 'Total Estrés',
            'total_workers' => $workerCount,
            'sum_averages' => $calculatedScoreRounded, // Promedio de puntajes transformados individuales
            'transformation_factor' => \App\Libraries\EstresScoring::getFactorTransformacion(),
            'calculated_score' => $calculatedScoreRounded,
            'db_score' => $dbScoreRounded,
            'difference' => $difference,
            'validation_status' => $validationStatus,
            'processed_at' => date('Y-m-d H:i:s'),
            'processed_by' => session()->get('id')
        ];

        log_message('info', "Guardando en validation_results...");
        log_message('debug', "Datos a guardar: " . json_encode($data, JSON_PRETTY_PRINT));

        $insertId = $this->validationResultModel->insert($data);

        if ($insertId) {
            log_message('info', "✓ Registro insertado con ID: {$insertId}");
        } else {
            log_message('error', "✗ Error al insertar en validation_results");
            log_message('error', "Errores del modelo: " . json_encode($this->validationResultModel->errors()));
        }

        log_message('info', '========================================');
        log_message('info', "FIN PROCESAMIENTO ESTRÉS - Forma {$formType}");
        log_message('info', '========================================');

        $message = "Procesado total de estrés Forma {$formType}.";
        return redirect()->to("/validation/{$serviceId}")
            ->with('success', $message);
    }

    /**
     * View total estres validation detail
     * GET /validation/total-estres/{serviceId}/{formType}
     *
     * NÚCLEO VALIDADOR - Recalcula detalles por ítem desde responses
     */
    public function validateTotalEstres($serviceId, $formType)
    {
        $permissionCheck = $this->checkPermissions();
        if ($permissionCheck) return $permissionCheck;

        // Get service
        $service = $this->batteryServiceModel
            ->select('battery_services.*, companies.name as company_name')
            ->join('companies', 'companies.id = battery_services.company_id')
            ->find($serviceId);

        if (!$service) {
            return redirect()->to('/battery-services')->with('error', 'Servicio no encontrado');
        }

        // Get validation result total
        $result = $this->validationResultModel->getValidationResult(
            $serviceId,
            'estres',
            $formType,
            'total',
            'total_estres'
        );

        if (!$result) {
            return redirect()->back()->with('error', 'Debe procesar primero el total de estrés');
        }

        // Get workers with stress responses for this form
        $workers = $this->workerModel
            ->where('battery_service_id', $serviceId)
            ->where('status', 'completado')
            ->where('intralaboral_type', $formType)
            ->findAll();

        // Filter workers that have stress responses
        $workersWithEstres = [];
        foreach ($workers as $worker) {
            $hasEstres = $this->responseModel
                ->where('worker_id', $worker['id'])
                ->where('form_type', 'estres')
                ->countAllResults() > 0;

            if ($hasEstres) {
                $workersWithEstres[] = $worker;
            }
        }

        $workerCount = count($workersWithEstres);

        // NOTA: Ya NO calculamos agregados por ítem aquí
        // La validación se hace calculando puntajes individuales (ver processEstres)
        // Esta vista solo muestra el resultado ya procesado en $result

        // Get baremos and factor
        $tipoTrabajador = \App\Libraries\EstresScoring::getTipoTrabajadorPorForma($formType);
        $baremos = \App\Libraries\EstresScoring::getBaremos($tipoTrabajador);
        $factorTotal = \App\Libraries\EstresScoring::getFactorTransformacion();
        $gruposMultiplicacion = \App\Libraries\EstresScoring::getGruposMultiplicacionInfo();

        $data = [
            'title' => 'Validación Total Estrés - Forma ' . $formType,
            'service' => $service,
            'formType' => $formType,
            'result' => $result,
            'totalWorkers' => $workerCount,
            'baremos' => $baremos,
            'factorTotal' => $factorTotal,
            'gruposMultiplicacion' => $gruposMultiplicacion
        ];

        return view('validation/total_estres_detail', $data);
    }

    /**
     * View complete validation history for a service
     * GET /validation/history/{serviceId}
     */
    public function validationHistory($serviceId)
    {
        log_message('info', '🔍 [validationHistory] INICIO - ServiceId: ' . $serviceId);

        $permissionCheck = $this->checkPermissions();
        if ($permissionCheck) {
            log_message('info', '🔍 [validationHistory] Permission check failed');
            return $permissionCheck;
        }

        log_message('info', '🔍 [validationHistory] Permissions OK, obteniendo servicio...');

        // Obtener información del servicio
        $service = $this->batteryServiceModel
            ->select('battery_services.*, companies.name as company_name')
            ->join('companies', 'companies.id = battery_services.company_id')
            ->find($serviceId);

        if (!$service) {
            log_message('error', '🔍 [validationHistory] Servicio NO encontrado');
            return redirect()->back()->with('error', 'Servicio no encontrado');
        }

        log_message('info', '🔍 [validationHistory] Servicio encontrado: ' . $service['service_name']);

        // Obtener TODOS los registros de validation_results para este servicio
        $validationModel = new \App\Models\ValidationResultModel();
        $builder = $validationModel->builder();

        log_message('info', '🔍 [validationHistory] Obteniendo registros de validation_results...');

        $results = $builder
            ->where('battery_service_id', $serviceId)
            ->orderBy('processed_at', 'DESC')
            ->get()
            ->getResultArray();

        log_message('info', '🔍 [validationHistory] Total registros: ' . count($results));

        $data = [
            'title' => 'Historial de Validaciones',
            'service' => $service,
            'results' => $results
        ];

        log_message('info', '🔍 [validationHistory] Renderizando vista validation_history...');

        return view('validation/validation_history', $data);
    }

    /**
     * Export validation history to Excel
     * GET /validation/history-export/{serviceId}
     */
    public function validationHistoryExport($serviceId)
    {
        $permissionCheck = $this->checkPermissions();
        if ($permissionCheck) return $permissionCheck;

        // Obtener información del servicio
        $service = $this->batteryServiceModel
            ->select('battery_services.*, companies.name as company_name')
            ->join('companies', 'companies.id = battery_services.company_id')
            ->find($serviceId);

        if (!$service) {
            return redirect()->back()->with('error', 'Servicio no encontrado');
        }

        // Obtener TODOS los registros
        $validationModel = new \App\Models\ValidationResultModel();
        $builder = $validationModel->builder();

        $results = $builder
            ->where('battery_service_id', $serviceId)
            ->orderBy('processed_at', 'DESC')
            ->get()
            ->getResultArray();

        // Crear CSV
        $filename = 'validation_history_service_' . $serviceId . '_' . date('Y-m-d_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // BOM para UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Encabezados
        fputcsv($output, [
            'ID',
            'Tipo de Cuestionario',
            'Forma',
            'Nivel',
            'Clave Elemento',
            'Nombre Elemento',
            'Total Trabajadores',
            'Suma Promedios',
            'Factor Transformación',
            'Puntaje Calculado',
            'Puntaje BD',
            'Diferencia',
            'Estado Validación',
            'Procesado Por',
            'Fecha Procesamiento'
        ], ';');

        // Datos
        foreach ($results as $row) {
            fputcsv($output, [
                $row['id'],
                $row['questionnaire_type'],
                $row['form_type'],
                $row['validation_level'],
                $row['element_key'],
                $row['element_name'],
                $row['total_workers'],
                $row['sum_averages'],
                $row['transformation_factor'],
                $row['calculated_score'],
                $row['db_score'],
                $row['difference'],
                $row['validation_status'],
                $row['processed_by'],
                $row['processed_at']
            ], ';');
        }

        fclose($output);
        exit;
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

        // Get dimension results from validation_results (para mostrar el desglose)
        $dimensionResults = $this->validationResultModel
            ->where('battery_service_id', $serviceId)
            ->where('questionnaire_type', 'extralaboral')
            ->where('validation_level', 'dimension')
            ->where('form_type', $formType)
            ->findAll();

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
            'dimensions' => $dimensionResults,
            'baremos' => $baremosConMetadata,
            'factorTotal' => $factorTotal,
            'totalWorkers' => $totalWorkers,
            'totalOk' => $totalOk,
            'totalErrors' => $totalErrors
        ];

        return view('validation/total_extralaboral_detail', $data);
    }
}
