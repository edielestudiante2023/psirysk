<?php

namespace App\Services;

use App\Models\WorkerModel;
use App\Models\WorkerDemographicsModel;
use App\Models\ResponseModel;
use App\Models\CalculatedResultModel;
use App\Libraries\IntralaboralAScoring;
use App\Libraries\IntralaboralBScoring;
use App\Libraries\ExtralaboralScoring;
use App\Libraries\EstresScoring;

/**
 * Servicio de cálculo de resultados de la batería de riesgo psicosocial
 *
 * Este servicio se encarga de:
 * 1. Recopilar las respuestas de un trabajador de todos los formularios
 * 2. Calificar cada cuestionario usando las librerías de scoring
 * 3. Calcular puntajes transformados y niveles de riesgo
 * 4. Guardar los resultados en la tabla calculated_results para reporting rápido
 */
class CalculationService
{
    protected $workerModel;
    protected $demographicsModel;
    protected $responseModel;
    protected $resultModel;

    public function __construct()
    {
        $this->workerModel = new WorkerModel();
        $this->demographicsModel = new WorkerDemographicsModel();
        $this->responseModel = new ResponseModel();
        $this->resultModel = new CalculatedResultModel();
    }

    /**
     * Calcula y guarda los resultados para un trabajador
     * Se llama cuando el trabajador completa todos los cuestionarios
     *
     * @param int $workerId ID del trabajador
     * @return array Resultados calculados o false si hay error
     */
    public function calculateAndSaveResults($workerId)
    {
        try {
            // 1. Obtener información del trabajador
            $worker = $this->workerModel->find($workerId);
            if (!$worker) {
                log_message('error', "Worker not found: {$workerId}");
                return false;
            }

            // 2. Obtener datos demográficos
            $demographics = $this->demographicsModel->getByWorkerId($workerId);
            if (!$demographics) {
                log_message('error', "Demographics not found for worker: {$workerId}");
                return false;
            }

            // 3. Verificar que todos los formularios estén completos
            if (!$this->allFormsComplete($workerId, $worker['intralaboral_type'])) {
                log_message('error', "Not all forms complete for worker: {$workerId}");
                return false;
            }

            // 4. Calcular resultados de cada cuestionario
            $intralaboralResults = $this->calculateIntralaboral($workerId, $worker);
            $extralaboralResults = $this->calculateExtralaboral($workerId, $demographics);
            $estresResults = $this->calculateEstres($workerId, $worker);

            // 5. Calcular puntaje total general
            $totalGeneral = $this->calculateTotalGeneral(
                $intralaboralResults,
                $extralaboralResults,
                $estresResults,
                $worker['intralaboral_type']
            );

            // 6. Preparar datos para guardar en calculated_results
            $resultData = $this->prepareResultData(
                $worker,
                $demographics,
                $intralaboralResults,
                $extralaboralResults,
                $estresResults,
                $totalGeneral
            );

            // 7. Guardar resultados
            $saved = $this->resultModel->saveResults($workerId, $resultData);

            if ($saved) {
                log_message('info', "Results calculated and saved for worker: {$workerId}");
                return $resultData;
            } else {
                log_message('error', "Failed to save results for worker: {$workerId}");
                return false;
            }

        } catch (\Exception $e) {
            log_message('error', "Error calculating results for worker {$workerId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica que todos los formularios requeridos estén completos
     * PÚBLICO para permitir validación desde AssessmentController
     */
    public function allFormsComplete($workerId, $intralaboralType)
    {
        log_message('error', '🔍 ===== VERIFICANDO FORMULARIOS COMPLETOS - Worker ID: ' . $workerId . ' =====');

        // Ficha de datos generales
        log_message('error', '🔍 Verificando Ficha de Datos Generales (demographics)...');
        $demographicsComplete = $this->demographicsModel->isCompleted($workerId);
        log_message('error', $demographicsComplete ? '✅ Ficha de Datos: COMPLETA' : '❌ Ficha de Datos: INCOMPLETA');

        if (!$demographicsComplete) {
            log_message('error', '❌ FALTA: Ficha de Datos Generales');
            return false;
        }

        // Intralaboral (A o B según tipo)
        $formType = 'intralaboral_' . $intralaboralType;

        // Obtener información del worker para verificar si atiende clientes
        $worker = $this->workerModel->find($workerId);
        $atiendeClientes = $worker['atiende_clientes'] ?? null;

        // Ajustar número esperado de preguntas según tipo y si atiende clientes
        if ($intralaboralType === 'A') {
            // Forma A: 123 preguntas siempre
            $expectedQuestions = 123;
        } else {
            // Forma B: 97 preguntas si atiende clientes, 88 si no atiende
            if ($atiendeClientes === 1 || $atiendeClientes === true) {
                $expectedQuestions = 97; // Incluye preguntas 89-97
            } else {
                $expectedQuestions = 88; // No incluye preguntas 89-97
            }
        }

        log_message('error', '🔍 Verificando Intralaboral Tipo ' . $intralaboralType . ' (' . $expectedQuestions . ' preguntas, atiende_clientes: ' . ($atiendeClientes ? 'SI' : 'NO') . ')...');

        $intralaboralComplete = $this->responseModel->isFormCompleted($workerId, $formType, $expectedQuestions);
        log_message('error', $intralaboralComplete ? '✅ Intralaboral: COMPLETO' : '❌ Intralaboral: INCOMPLETO');

        if (!$intralaboralComplete) {
            // Contar cuántas respuestas hay
            $actualCount = $this->responseModel->where('worker_id', $workerId)
                ->where('form_type', $formType)
                ->countAllResults();
            log_message('error', '❌ FALTA: Intralaboral Tipo ' . $intralaboralType . ' - Esperadas: ' . $expectedQuestions . ', Encontradas: ' . $actualCount);
            return false;
        }

        // Extralaboral (31 preguntas)
        log_message('error', '🔍 Verificando Extralaboral (31 preguntas)...');
        $extralaboralComplete = $this->responseModel->isFormCompleted($workerId, 'extralaboral', 31);
        log_message('error', $extralaboralComplete ? '✅ Extralaboral: COMPLETO' : '❌ Extralaboral: INCOMPLETO');

        if (!$extralaboralComplete) {
            $actualCount = $this->responseModel->where('worker_id', $workerId)
                ->where('form_type', 'extralaboral')
                ->countAllResults();
            log_message('error', '❌ FALTA: Extralaboral - Esperadas: 31, Encontradas: ' . $actualCount);
            return false;
        }

        // Estrés (31 preguntas)
        log_message('error', '🔍 Verificando Estrés (31 preguntas)...');
        $estresComplete = $this->responseModel->isFormCompleted($workerId, 'estres', 31);
        log_message('error', $estresComplete ? '✅ Estrés: COMPLETO' : '❌ Estrés: INCOMPLETO');

        if (!$estresComplete) {
            $actualCount = $this->responseModel->where('worker_id', $workerId)
                ->where('form_type', 'estres')
                ->countAllResults();
            log_message('error', '❌ FALTA: Estrés - Esperadas: 31, Encontradas: ' . $actualCount);
            return false;
        }

        log_message('error', '✅ TODOS LOS FORMULARIOS ESTÁN COMPLETOS');
        return true;
    }

    /**
     * Calcula resultados del cuestionario Intralaboral (A o B)
     */
    protected function calculateIntralaboral($workerId, $worker)
    {
        $formType = 'intralaboral_' . $worker['intralaboral_type'];
        $responses = $this->responseModel->getWorkerFormResponses($workerId, $formType);

        // Convertir respuestas a formato array [numero_pregunta => valor]
        $answersArray = [];
        foreach ($responses as $response) {
            $answersArray[$response['question_number']] = $response['answer_value'];
        }

        // Calificar según el tipo de formulario
        if ($worker['intralaboral_type'] === 'A') {
            $results = IntralaboralAScoring::calificar(
                $answersArray,
                $worker['atiende_clientes'] ?? false,
                $worker['es_jefe'] ?? false
            );
        } else {
            $results = IntralaboralBScoring::calificar(
                $answersArray,
                $worker['atiende_clientes'] ?? false
            );
        }

        return $results;
    }

    /**
     * Calcula resultados del cuestionario Extralaboral
     */
    protected function calculateExtralaboral($workerId, $demographics)
    {
        $responses = $this->responseModel->getWorkerFormResponses($workerId, 'extralaboral');

        // Convertir respuestas a formato array
        $answersArray = [];
        foreach ($responses as $response) {
            $answersArray[$response['question_number']] = $response['answer_value'];
        }

        // Determinar tipo de baremo según position_type
        $tipoBaremo = $this->getTipoBaremoExtralaboral($demographics['position_type']);

        $results = ExtralaboralScoring::calificar($answersArray, $tipoBaremo);

        log_message('error', '🔍 [CalculationService::calculateExtralaboral] Extralaboral results: ' . json_encode($results));

        return $results;
    }

    /**
     * Calcula resultados del cuestionario de Estrés
     */
    protected function calculateEstres($workerId, $worker)
    {
        log_message('error', "🔍 [CalculationService::calculateEstres] Worker ID: {$workerId}");
        log_message('error', "🔍 [CalculationService::calculateEstres] Worker data: " . json_encode($worker));

        $responses = $this->responseModel->getWorkerFormResponses($workerId, 'estres');
        log_message('error', "🔍 [CalculationService::calculateEstres] Total responses: " . count($responses));

        // Convertir respuestas a formato array
        // Los valores ya vienen como texto ('siempre', 'casi_siempre', 'a_veces', 'nunca')
        // directamente desde el formulario, sin necesidad de conversión
        $answersArray = [];

        foreach ($responses as $response) {
            $answersArray[$response['question_number']] = $response['answer_value'];
        }

        log_message('error', "🔍 [CalculationService::calculateEstres] Primeras 3 respuestas: " . json_encode(array_slice($answersArray, 0, 3, true)));

        // Determinar tipo de baremo según intralaboral_type (Forma A o B)
        $intralaboralType = $worker['intralaboral_type'] ?? 'DESCONOCIDO';
        log_message('error', "🔍 [CalculationService::calculateEstres] intralaboral_type del worker: {$intralaboralType}");

        $tipoBaremo = $this->getTipoBaremoEstres($intralaboralType);
        log_message('error', "🔍 [CalculationService::calculateEstres] Tipo baremo calculado: {$tipoBaremo}");

        $results = EstresScoring::calificar($answersArray, $tipoBaremo);
        log_message('error', "🔍 [CalculationService::calculateEstres] Resultados: " . json_encode($results));

        return $results;
    }

    /**
     * Calcula el puntaje total general según metodología del Ministerio
     * Tabla 28: Factor de transformación puntaje general
     * - Forma A: 616
     * - Forma B: 512
     */
    protected function calculateTotalGeneral($intralaboralResults, $extralaboralResults, $estresResults, $intralaboralType)
    {
        // Sumar puntajes brutos de intralaboral + extralaboral
        $puntajeBruto = $intralaboralResults['puntaje_bruto_total'] +
                        $extralaboralResults['puntajes_brutos']['total'];

        // Determinar factor de transformación según tipo de formulario (Tabla 28)
        $factorTransformacion = ($intralaboralType === 'A') ? 616 : 512;

        $puntajeTransformado = round(($puntajeBruto / $factorTransformacion) * 100, 1);

        // Validación: El puntaje transformado NO puede exceder 100
        // Si excede, es porque hay un error en los puntajes brutos
        if ($puntajeTransformado > 100) {
            log_message('error', "⚠️ [CalculationService::calculateTotalGeneral] ERROR: Puntaje transformado excede 100! " .
                "Intralaboral bruto: {$intralaboralResults['puntaje_bruto_total']}, " .
                "Extralaboral bruto: {$extralaboralResults['puntajes_brutos']['total']}, " .
                "Suma: $puntajeBruto, Factor: $factorTransformacion, " .
                "Transformado calculado: $puntajeTransformado (LIMITADO A 100)");
            $puntajeTransformado = 100.0;
        }

        // DEBUG: Log de cálculo
        log_message('debug', "🔍 [CalculationService::calculateTotalGeneral] " .
            "Intralaboral bruto: {$intralaboralResults['puntaje_bruto_total']}, " .
            "Extralaboral bruto: {$extralaboralResults['puntajes_brutos']['total']}, " .
            "Suma: $puntajeBruto, Factor: $factorTransformacion, " .
            "Transformado: $puntajeTransformado, Tipo: $intralaboralType");

        // Determinar nivel de riesgo según Tabla 34 (Baremo general según tipo)
        $nivelRiesgo = $this->determinarNivelRiesgoGeneral($puntajeTransformado, $intralaboralType);

        return [
            'puntaje_bruto' => $puntajeBruto,
            'puntaje_transformado' => $puntajeTransformado,
            'nivel_riesgo' => $nivelRiesgo
        ];
    }

    /**
     * Determina el nivel de riesgo general según Tabla 34
     * Baremos diferentes para Forma A y Forma B
     */
    protected function determinarNivelRiesgoGeneral($puntaje, $intralaboralType)
    {
        // Tabla 34 - Baremos según tipo de formulario
        if ($intralaboralType === 'A') {
            // Forma A - Jefes, profesionales, técnicos
            $baremos = [
                'sin_riesgo' => [0.0, 18.8],
                'riesgo_bajo' => [18.9, 24.4],
                'riesgo_medio' => [24.5, 29.5],
                'riesgo_alto' => [29.6, 35.4],
                'riesgo_muy_alto' => [35.5, 100.0]
            ];
        } else {
            // Forma B - Auxiliares, operarios
            // Tabla 34: Baremos para el puntaje total general - Verificado 2025-11-25
            $baremos = [
                'sin_riesgo' => [0.0, 19.9],      // Correcto - mantiene rangos continuos
                'riesgo_bajo' => [20.0, 24.8],
                'riesgo_medio' => [24.9, 29.5],
                'riesgo_alto' => [29.6, 35.4],
                'riesgo_muy_alto' => [35.5, 100.0]
            ];
        }

        // Usar epsilon para floating point comparison
        foreach ($baremos as $nivel => $rango) {
            if ($puntaje >= $rango[0] && $puntaje <= ($rango[1] + 0.1)) {
                return $nivel;
            }
        }

        return 'sin_riesgo';
    }

    /**
     * Determina el tipo de baremo para Extralaboral según position_type
     */
    protected function getTipoBaremoExtralaboral($positionType)
    {
        // Tabla 17: jefes, profesionales, técnicos
        // Tabla 18: auxiliares, operarios
        $tiposJefes = ['Jefatura', 'Profesional', 'Tecnico'];

        return in_array($positionType, $tiposJefes) ? 'jefes' : 'auxiliares';
    }

    /**
     * Determina el tipo de baremo para Estrés según intralaboral_type
     * Forma A (Jefes/Profesionales/Técnicos) → baremo 'jefes'
     * Forma B (Auxiliares/Operarios) → baremo 'auxiliares'
     */
    protected function getTipoBaremoEstres($intralaboralType)
    {
        // Tabla 6: Forma A vs Forma B
        return ($intralaboralType === 'A') ? 'jefes' : 'auxiliares';
    }

    /**
     * Prepara los datos para insertar en calculated_results
     * Incluye todos los campos necesarios para segmentación en dashboards
     */
    protected function prepareResultData($worker, $demographics, $intralaboralResults, $extralaboralResults, $estresResults, $totalGeneral)
    {
        $data = [
            // Identificación
            'battery_service_id' => $worker['battery_service_id'],

            // Datos demográficos para segmentación
            'intralaboral_form_type' => $worker['intralaboral_type'],
            'gender' => $demographics['gender'],
            'marital_status' => $demographics['marital_status'],
            'education_level' => $demographics['education_level'],
            'city_residence' => $demographics['city_residence'],
            'department' => $demographics['department'],
            'position' => $worker['position'],
            'position_type' => $demographics['position_type'],
            'contract_type' => $demographics['contract_type'],
            'work_experience_years' => $demographics['time_in_company_years'] ?? 0,
            'time_in_company_months' => ($demographics['time_in_company_years'] ?? 0) * 12,
            'hours_per_day' => $demographics['hours_per_day'] ?? 0,

            // === RESULTADOS INTRALABORAL ===

            // Dominios Intralaboral (dom_*)
            'dom_liderazgo_puntaje' => $intralaboralResults['puntajes_transformados_dominios']['liderazgo_relaciones_sociales'] ?? null,
            'dom_liderazgo_nivel' => $intralaboralResults['niveles_riesgo_dominios']['liderazgo_relaciones_sociales'] ?? null,
            'dom_control_puntaje' => $intralaboralResults['puntajes_transformados_dominios']['control'] ?? null,
            'dom_control_nivel' => $intralaboralResults['niveles_riesgo_dominios']['control'] ?? null,
            'dom_demandas_puntaje' => $intralaboralResults['puntajes_transformados_dominios']['demandas'] ?? null,
            'dom_demandas_nivel' => $intralaboralResults['niveles_riesgo_dominios']['demandas'] ?? null,
            'dom_recompensas_puntaje' => $intralaboralResults['puntajes_transformados_dominios']['recompensas'] ?? null,
            'dom_recompensas_nivel' => $intralaboralResults['niveles_riesgo_dominios']['recompensas'] ?? null,

            // Dimensiones Intralaboral (dim_*) - mapeo según nombres de columnas en DB
            'dim_caracteristicas_liderazgo_puntaje' => $intralaboralResults['puntajes_transformados_dimensiones']['caracteristicas_liderazgo'] ?? null,
            'dim_caracteristicas_liderazgo_nivel' => $intralaboralResults['niveles_riesgo_dimensiones']['caracteristicas_liderazgo'] ?? null,
            'dim_relaciones_sociales_puntaje' => $intralaboralResults['puntajes_transformados_dimensiones']['relaciones_sociales_trabajo'] ?? null,
            'dim_relaciones_sociales_nivel' => $intralaboralResults['niveles_riesgo_dimensiones']['relaciones_sociales_trabajo'] ?? null,
            'dim_retroalimentacion_puntaje' => $intralaboralResults['puntajes_transformados_dimensiones']['retroalimentacion_desempeno'] ?? null,
            'dim_retroalimentacion_nivel' => $intralaboralResults['niveles_riesgo_dimensiones']['retroalimentacion_desempeno'] ?? null,
            'dim_relacion_colaboradores_puntaje' => $intralaboralResults['puntajes_transformados_dimensiones']['relacion_con_colaboradores'] ?? null,
            'dim_relacion_colaboradores_nivel' => $intralaboralResults['niveles_riesgo_dimensiones']['relacion_con_colaboradores'] ?? null,
            'dim_claridad_rol_puntaje' => $intralaboralResults['puntajes_transformados_dimensiones']['claridad_rol'] ?? null,
            'dim_claridad_rol_nivel' => $intralaboralResults['niveles_riesgo_dimensiones']['claridad_rol'] ?? null,
            'dim_capacitacion_puntaje' => $intralaboralResults['puntajes_transformados_dimensiones']['capacitacion'] ?? null,
            'dim_capacitacion_nivel' => $intralaboralResults['niveles_riesgo_dimensiones']['capacitacion'] ?? null,
            'dim_participacion_manejo_cambio_puntaje' => $intralaboralResults['puntajes_transformados_dimensiones']['participacion_manejo_cambio'] ?? null,
            'dim_participacion_manejo_cambio_nivel' => $intralaboralResults['niveles_riesgo_dimensiones']['participacion_manejo_cambio'] ?? null,
            'dim_oportunidades_desarrollo_puntaje' => $intralaboralResults['puntajes_transformados_dimensiones']['oportunidades_desarrollo'] ?? null,
            'dim_oportunidades_desarrollo_nivel' => $intralaboralResults['niveles_riesgo_dimensiones']['oportunidades_desarrollo'] ?? null,
            'dim_control_autonomia_puntaje' => $intralaboralResults['puntajes_transformados_dimensiones']['control_autonomia_trabajo'] ?? null,
            'dim_control_autonomia_nivel' => $intralaboralResults['niveles_riesgo_dimensiones']['control_autonomia_trabajo'] ?? null,
            'dim_demandas_ambientales_puntaje' => $intralaboralResults['puntajes_transformados_dimensiones']['demandas_ambientales_esfuerzo_fisico'] ?? null,
            'dim_demandas_ambientales_nivel' => $intralaboralResults['niveles_riesgo_dimensiones']['demandas_ambientales_esfuerzo_fisico'] ?? null,
            'dim_demandas_emocionales_puntaje' => $intralaboralResults['puntajes_transformados_dimensiones']['demandas_emocionales'] ?? null,
            'dim_demandas_emocionales_nivel' => $intralaboralResults['niveles_riesgo_dimensiones']['demandas_emocionales'] ?? null,
            'dim_demandas_cuantitativas_puntaje' => $intralaboralResults['puntajes_transformados_dimensiones']['demandas_cuantitativas'] ?? null,
            'dim_demandas_cuantitativas_nivel' => $intralaboralResults['niveles_riesgo_dimensiones']['demandas_cuantitativas'] ?? null,
            'dim_influencia_trabajo_entorno_extralaboral_puntaje' => $intralaboralResults['puntajes_transformados_dimensiones']['influencia_trabajo_entorno_extralaboral'] ?? null,
            'dim_influencia_trabajo_entorno_extralaboral_nivel' => $intralaboralResults['niveles_riesgo_dimensiones']['influencia_trabajo_entorno_extralaboral'] ?? null,
            'dim_demandas_carga_mental_puntaje' => $intralaboralResults['puntajes_transformados_dimensiones']['demandas_carga_mental'] ?? null,
            'dim_demandas_carga_mental_nivel' => $intralaboralResults['niveles_riesgo_dimensiones']['demandas_carga_mental'] ?? null,
            'dim_demandas_jornada_trabajo_puntaje' => $intralaboralResults['puntajes_transformados_dimensiones']['demandas_jornada_trabajo'] ?? null,
            'dim_demandas_jornada_trabajo_nivel' => $intralaboralResults['niveles_riesgo_dimensiones']['demandas_jornada_trabajo'] ?? null,
            'dim_consistencia_rol_puntaje' => $intralaboralResults['puntajes_transformados_dimensiones']['consistencia_rol'] ?? null,
            'dim_consistencia_rol_nivel' => $intralaboralResults['niveles_riesgo_dimensiones']['consistencia_rol'] ?? null,
            'dim_demandas_responsabilidad_puntaje' => $intralaboralResults['puntajes_transformados_dimensiones']['exigencias_responsabilidad_cargo'] ?? null,
            'dim_demandas_responsabilidad_nivel' => $intralaboralResults['niveles_riesgo_dimensiones']['exigencias_responsabilidad_cargo'] ?? null,
            'dim_recompensas_pertenencia_puntaje' => $intralaboralResults['puntajes_transformados_dimensiones']['recompensas_pertenencia_estabilidad'] ?? null,
            'dim_recompensas_pertenencia_nivel' => $intralaboralResults['niveles_riesgo_dimensiones']['recompensas_pertenencia_estabilidad'] ?? null,
            'dim_reconocimiento_compensacion_puntaje' => $intralaboralResults['puntajes_transformados_dimensiones']['reconocimiento_compensacion'] ?? null,
            'dim_reconocimiento_compensacion_nivel' => $intralaboralResults['niveles_riesgo_dimensiones']['reconocimiento_compensacion'] ?? null,

            // Total Intralaboral
            'intralaboral_total_puntaje' => $intralaboralResults['puntaje_transformado_total'],
            'intralaboral_total_nivel' => $intralaboralResults['nivel_riesgo_total'],

            // === RESULTADOS EXTRALABORAL ===

            // Dimensiones Extralaboral
            'extralaboral_tiempo_fuera_puntaje' => $extralaboralResults['puntajes_transformados']['tiempo_fuera_trabajo'] ?? null,
            'extralaboral_tiempo_fuera_nivel' => $extralaboralResults['niveles_riesgo']['tiempo_fuera_trabajo'] ?? null,
            'extralaboral_relaciones_familiares_puntaje' => $extralaboralResults['puntajes_transformados']['relaciones_familiares'] ?? null,
            'extralaboral_relaciones_familiares_nivel' => $extralaboralResults['niveles_riesgo']['relaciones_familiares'] ?? null,
            'extralaboral_comunicacion_puntaje' => $extralaboralResults['puntajes_transformados']['comunicacion_relaciones'] ?? null,
            'extralaboral_comunicacion_nivel' => $extralaboralResults['niveles_riesgo']['comunicacion_relaciones'] ?? null,
            'extralaboral_situacion_economica_puntaje' => $extralaboralResults['puntajes_transformados']['situacion_economica'] ?? null,
            'extralaboral_situacion_economica_nivel' => $extralaboralResults['niveles_riesgo']['situacion_economica'] ?? null,
            'extralaboral_caracteristicas_vivienda_puntaje' => $extralaboralResults['puntajes_transformados']['caracteristicas_vivienda'] ?? null,
            'extralaboral_caracteristicas_vivienda_nivel' => $extralaboralResults['niveles_riesgo']['caracteristicas_vivienda'] ?? null,
            'extralaboral_influencia_entorno_puntaje' => $extralaboralResults['puntajes_transformados']['influencia_entorno'] ?? null,
            'extralaboral_influencia_entorno_nivel' => $extralaboralResults['niveles_riesgo']['influencia_entorno'] ?? null,
            'extralaboral_desplazamiento_puntaje' => $extralaboralResults['puntajes_transformados']['desplazamiento'] ?? null,
            'extralaboral_desplazamiento_nivel' => $extralaboralResults['niveles_riesgo']['desplazamiento'] ?? null,

            // Total Extralaboral
            'extralaboral_total_puntaje' => $extralaboralResults['puntajes_transformados']['total'],
            'extralaboral_total_nivel' => $extralaboralResults['niveles_riesgo']['total'],

            // === RESULTADOS ESTRÉS ===

            // Dimensiones Estrés (síntomas)
            'estres_fisiologico_puntaje' => $estresResults['puntajes_por_dimension']['sintomas_fisiologicos'] ?? 0,
            'estres_comportamiento_puntaje' => $estresResults['puntajes_por_dimension']['sintomas_comportamiento_social'] ?? 0,
            'estres_laboral_puntaje' => $estresResults['puntajes_por_dimension']['sintomas_laborales'] ?? 0,
            'estres_psicoemocional_puntaje' => $estresResults['puntajes_por_dimension']['sintomas_psicoemocionales'] ?? 0,

            // Total Estrés
            'estres_total_puntaje' => $estresResults['puntaje_transformado_total'],
            'estres_total_nivel' => $estresResults['nivel_estres'],

            // === PUNTAJE TOTAL GENERAL ===
            'puntaje_total_general' => $totalGeneral['puntaje_transformado'],
            'puntaje_total_general_nivel' => $totalGeneral['nivel_riesgo'],

            // Metadatos
            'calculated_at' => date('Y-m-d H:i:s')
        ];

        return $data;
    }

    /**
     * Obtiene los resultados calculados de un trabajador
     * Si no existen, intenta calcularlos
     */
    public function getWorkerResults($workerId)
    {
        // Intentar obtener resultados existentes
        $results = $this->resultModel->getByWorkerId($workerId);

        // Si no existen, calcularlos
        if (!$results) {
            $this->calculateAndSaveResults($workerId);
            $results = $this->resultModel->getByWorkerId($workerId);
        }

        return $results;
    }

    /**
     * Recalcula los resultados de un trabajador
     * Útil si se actualizan las respuestas
     */
    public function recalculateResults($workerId)
    {
        // Eliminar resultados anteriores
        $this->resultModel->deleteByWorkerId($workerId);

        // Calcular nuevamente
        return $this->calculateAndSaveResults($workerId);
    }
}
