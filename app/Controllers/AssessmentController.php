<?php

namespace App\Controllers;

use App\Models\WorkerModel;
use App\Models\WorkerDemographicsModel;
use App\Models\ResponseModel;
use App\Models\BatteryServiceModel;
use App\Services\CalculationService;

class AssessmentController extends BaseController
{
    protected $workerModel;
    protected $demographicsModel;
    protected $responseModel;
    protected $serviceModel;
    protected $calculationService;

    /**
     * Tablas oficiales según Manual del Ministerio de la Protección Social
     * Paso 1. Calificación de los ítems
     */
    private $intralaboralA_invertidas = [
        1, 2, 3, 7, 8, 10, 11, 13, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26,
        27, 28, 29, 30, 31, 33, 35, 36, 37, 38, 52, 80, 106, 107, 108, 109, 110,
        111, 112, 113, 114, 115, 116, 117, 118, 119, 120, 121, 122, 123
    ];

    private $intralaboralB_invertidas = [
        1, 2, 3, 7, 8, 10, 11, 13, 15, 16, 17, 18, 19, 20, 21, 23, 25, 26, 27, 28,
        66, 89, 90, 91, 92, 93, 94, 95, 96
    ];

    private $extralaboral_invertidas = [
        2, 3, 6, 24, 26, 28, 30, 31
    ];

    private $estres_grupo1 = [1, 2, 3, 9, 13, 14, 15, 23, 24];
    private $estres_grupo2 = [4, 5, 6, 10, 11, 16, 17, 18, 19, 25, 26, 27, 28];
    private $estres_grupo3 = [7, 8, 12, 20, 21, 22, 29, 30, 31];

    public function __construct()
    {
        $this->workerModel = new WorkerModel();
        $this->demographicsModel = new WorkerDemographicsModel();
        $this->responseModel = new ResponseModel();
        $this->serviceModel = new BatteryServiceModel();
        $this->calculationService = new CalculationService();
    }

    /**
     * Califica una pregunta de Intralaboral según Tablas 21 y 22
     * Aplica el Paso 1. Calificación de los ítems según el manual oficial
     *
     * @param int $questionNumber Número de pregunta (1-123 para A, 1-97 para B)
     * @param int $likertValue Valor de Likert del formulario (0=Siempre, 1=Casi siempre, 2=Algunas veces, 3=Casi nunca, 4=Nunca)
     * @param string $formType 'A' o 'B'
     * @return int Puntaje calificado (0-4)
     */
    private function scoreIntralaboral($questionNumber, $likertValue, $formType)
    {
        $isInverted = false;

        if ($formType === 'A') {
            $isInverted = in_array($questionNumber, $this->intralaboralA_invertidas);
        } elseif ($formType === 'B') {
            $isInverted = in_array($questionNumber, $this->intralaboralB_invertidas);
        }

        if ($isInverted) {
            // Invertida: Siempre(0)=4, Casi siempre(1)=3, Algunas veces(2)=2, Casi nunca(3)=1, Nunca(4)=0
            return 4 - $likertValue;
        } else {
            // Normal: Siempre(0)=0, Casi siempre(1)=1, Algunas veces(2)=2, Casi nunca(3)=3, Nunca(4)=4
            return $likertValue;
        }
    }

    /**
     * Califica una pregunta de Extralaboral según Tabla 11
     *
     * @param int $questionNumber Número de pregunta (1-31)
     * @param int $likertValue Valor de Likert del formulario (0-4)
     * @return int Puntaje calificado (0-4)
     */
    private function scoreExtralaboral($questionNumber, $likertValue)
    {
        $isInverted = in_array($questionNumber, $this->extralaboral_invertidas);

        if ($isInverted) {
            return 4 - $likertValue;
        } else {
            return $likertValue;
        }
    }

    /**
     * Califica una pregunta de Estrés según Tabla 4 (grupos 1, 2, 3)
     *
     * @param int $questionNumber Número de pregunta (1-31)
     * @param int $likertValue Valor de Likert del formulario (0=Siempre, 1=Casi siempre, 2=A veces, 3=Nunca)
     * @return int Puntaje calificado según el grupo
     */
    private function scoreEstres($questionNumber, $likertValue)
    {
        // Convertir valores string a numéricos si es necesario
        $stringToNumeric = [
            'siempre' => 0,
            'casi_siempre' => 1,
            'a_veces' => 2,
            'nunca' => 3
        ];

        // Si el valor es string, convertirlo a numérico
        if (is_string($likertValue) && isset($stringToNumeric[$likertValue])) {
            $likertValue = $stringToNumeric[$likertValue];
        }

        if (in_array($questionNumber, $this->estres_grupo1)) {
            // Grupo 1: Siempre=9, Casi siempre=6, A veces=3, Nunca=0
            $map = [0 => 9, 1 => 6, 2 => 3, 3 => 0];
            return $map[$likertValue] ?? 0;
        } elseif (in_array($questionNumber, $this->estres_grupo2)) {
            // Grupo 2: Siempre=6, Casi siempre=4, A veces=2, Nunca=0
            $map = [0 => 6, 1 => 4, 2 => 2, 3 => 0];
            return $map[$likertValue] ?? 0;
        } elseif (in_array($questionNumber, $this->estres_grupo3)) {
            // Grupo 3: Siempre=3, Casi siempre=2, A veces=1, Nunca=0
            $map = [0 => 3, 1 => 2, 2 => 1, 3 => 0];
            return $map[$likertValue] ?? 0;
        }

        return 0;
    }

    /**
     * Landing page when worker accesses with token
     */
    public function index($token)
    {
        // Validate token
        $worker = $this->workerModel->where('token', $token)->first();

        if (!$worker) {
            return view('assessment/invalid_token');
        }

        // Check if link has expired
        $service = $this->serviceModel->find($worker['battery_service_id']);

        if (!$service || strtotime($service['link_expiration_date']) < time()) {
            return view('assessment/expired_link', [
                'worker' => $worker,
                'service' => $service
            ]);
        }

        // Store worker info in session
        $session = session();
        $session->set([
            'assessment_worker_id' => $worker['id'],
            'assessment_token' => $token,
            'assessment_intralaboral_type' => $worker['intralaboral_type']
        ]);

        // Update worker status to 'en_progreso' if still 'pendiente'
        if ($worker['status'] === 'pendiente') {
            $this->workerModel->update($worker['id'], [
                'status' => 'en_progreso',
                'started_at' => date('Y-m-d H:i:s')
            ]);
        }

        // Check progress and redirect to appropriate form
        return $this->redirectToCurrentForm($worker['id']);
    }

    /**
     * Determine which form the worker should see next
     */
    protected function redirectToCurrentForm($workerId)
    {
        // 1. Check if demographics completed
        $demographics = $this->demographicsModel->getByWorkerId($workerId);
        if (!$demographics || !$demographics['completed_at']) {
            return redirect()->to('/assessment/general-data');
        }

        $worker = $this->workerModel->find($workerId);
        $intralaboralType = $worker['intralaboral_type'];
        $formType = 'intralaboral_' . $intralaboralType;
        $intralaboralQuestions = $intralaboralType === 'A' ? 123 : 97;

        // 2. Check if intralaboral completed
        if (!$this->responseModel->isFormCompleted($workerId, $formType, $intralaboralQuestions)) {
            return redirect()->to('/assessment/intralaboral');
        }

        // 3. Check if extralaboral completed
        if (!$this->responseModel->isFormCompleted($workerId, 'extralaboral', 31)) {
            return redirect()->to('/assessment/extralaboral');
        }

        // 4. Check if estres completed
        if (!$this->responseModel->isFormCompleted($workerId, 'estres', 31)) {
            return redirect()->to('/assessment/estres');
        }

        // All forms completed
        return redirect()->to('/assessment/completed');
    }

    /**
     * Display general data form (Ficha de Datos Generales)
     */
    public function generalData()
    {
        $session = session();
        $workerId = $session->get('assessment_worker_id');

        if (!$workerId) {
            return redirect()->to('/assessment/invalid');
        }

        $worker = $this->workerModel->find($workerId);
        $demographics = $this->demographicsModel->getByWorkerId($workerId);

        // Get all unique areas from workers in the same service
        $serviceId = $worker['battery_service_id'];
        $areas = $this->workerModel
            ->select('area')
            ->where('battery_service_id', $serviceId)
            ->where('area IS NOT NULL')
            ->where('area !=', '')
            ->groupBy('area')
            ->orderBy('area', 'ASC')
            ->findAll();

        $areasList = array_column($areas, 'area');

        // Get all unique positions from workers in the same service
        $positions = $this->workerModel
            ->select('position')
            ->where('battery_service_id', $serviceId)
            ->where('position IS NOT NULL')
            ->where('position !=', '')
            ->groupBy('position')
            ->orderBy('position', 'ASC')
            ->findAll();

        $positionsList = array_column($positions, 'position');

        return view('assessment/general_data', [
            'worker' => $worker,
            'demographics' => $demographics,
            'areas' => $areasList,
            'positions' => $positionsList
        ]);
    }

    /**
     * Save general data form
     */
    public function saveGeneralData()
    {
        $session = session();
        $workerId = $session->get('assessment_worker_id');

        if (!$workerId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sesión inválida']);
        }

        // Preparar datos del formulario
        $formData = [
            'gender' => $this->request->getPost('gender'),
            'birth_year' => $this->request->getPost('birth_year'),
            'marital_status' => $this->request->getPost('marital_status'),
            'education_level' => $this->request->getPost('education_level'),
            'occupation' => $this->request->getPost('occupation'),
            'city_residence' => $this->request->getPost('city_residence'),
            'department_residence' => $this->request->getPost('department_residence'),
            'stratum' => $this->request->getPost('stratum'),
            'housing_type' => $this->request->getPost('housing_type'),
            'dependents' => $this->request->getPost('dependents'),
            'city_work' => $this->request->getPost('city_work'),
            'department_work' => $this->request->getPost('department_work'),
            'time_in_company_type' => $this->request->getPost('time_in_company_type'),
            'time_in_company_years' => $this->request->getPost('time_in_company_years'),
            'position_name' => $this->request->getPost('position_name'),
            'position_type' => $this->request->getPost('position_type'),
            'time_in_position_type' => $this->request->getPost('time_in_position_type'),
            'time_in_position_years' => $this->request->getPost('time_in_position_years'),
            'department' => $this->request->getPost('department'),
            'contract_type' => $this->request->getPost('contract_type'),
            'hours_per_day' => $this->request->getPost('hours_per_day'),
            'salary_type' => $this->request->getPost('salary_type'),
            'completed_at' => date('Y-m-d H:i:s')
        ];

        // Check if already exists
        $existing = $this->demographicsModel->getByWorkerId($workerId);

        if ($existing) {
            // UPDATE: No incluir worker_id para evitar error de validación is_unique
            $result = $this->demographicsModel->update($existing['id'], $formData);
        } else {
            // INSERT: Incluir worker_id
            $formData['worker_id'] = $workerId;
            $result = $this->demographicsModel->insert($formData);
        }

        if ($result) {
            $responseData = [
                'success' => true,
                'message' => 'Datos guardados exitosamente',
                'redirect' => base_url('assessment/intralaboral')
            ];

            // VERIFICACIÓN DE INTEGRIDAD: Leer de nuevo desde la BD
            if (env('DEBUG_SAVE_VERIFICATION') === 'true') {
                $savedData = $this->demographicsModel->getByWorkerId($workerId);

                if ($savedData) {
                    $debugData = [];

                    // Comparar cada campo enviado con lo que quedó en BD
                    foreach ($formData as $field => $sentValue) {
                        if ($field !== 'worker_id' && $field !== 'completed_at') {
                            $dbValue = $savedData[$field] ?? null;
                            $debugData[] = [
                                'campo' => $field,
                                'valor_enviado' => $sentValue,
                                'valor_en_bd' => $dbValue,
                                'coincide' => ($sentValue == $dbValue)
                            ];
                        }
                    }

                    $responseData['debug_verification'] = $debugData;
                    $responseData['debug_enabled'] = true;
                }
            }

            return $this->response->setJSON($responseData);
        } else {
            log_message('error', 'Failed to save demographics. Errors: ' . json_encode($this->demographicsModel->errors()));
            log_message('error', 'Data attempted: ' . json_encode($formData));
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al guardar los datos: ' . implode(', ', $this->demographicsModel->errors()),
                'errors' => $this->demographicsModel->errors(),
                'debug_data' => $formData
            ]);
        }
    }

    /**
     * INLINE EDITING: Guarda un campo individual de general data
     * Permite auto-guardado campo por campo con verificación inmediata
     */
    public function saveFieldGeneralData()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Solicitud no válida'
            ]);
        }

        $workerId = session()->get('assessment_worker_id');
        if (!$workerId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Sesión no válida'
            ]);
        }

        // Obtener el campo y valor enviados
        $fieldName = $this->request->getPost('field_name');
        $fieldValue = $this->request->getPost('field_value');

        // Validar que el campo sea permitido
        $allowedFields = [
            'gender', 'birth_year', 'marital_status', 'education_level', 'occupation',
            'city_residence', 'department_residence', 'stratum', 'housing_type',
            'dependents', 'city_work', 'department_work', 'time_in_company_type',
            'time_in_company_years', 'position_name', 'position_type',
            'time_in_position_type', 'time_in_position_years', 'department',
            'contract_type', 'hours_per_day', 'salary_type'
        ];

        if (!in_array($fieldName, $allowedFields)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Campo no válido: ' . $fieldName
            ]);
        }

        // Buscar registro existente
        $existing = $this->demographicsModel->getByWorkerId($workerId);
        log_message('error', '🔍 Worker ID: ' . $workerId);
        log_message('error', '🔍 Existing record: ' . ($existing ? 'YES (ID: ' . $existing['id'] . ')' : 'NO'));

        $result = false;

        if ($existing) {
            // Actualizar solo el campo específico (NO incluir worker_id para evitar validación is_unique)
            $data = [
                $fieldName => $fieldValue
            ];
            log_message('error', '🔍 Data to UPDATE: ' . json_encode($data));
            log_message('error', '🔍 Attempting UPDATE with ID: ' . $existing['id']);

            $result = $this->demographicsModel->update($existing['id'], $data);
            log_message('error', '🔍 UPDATE result: ' . var_export($result, true));
            if (!$result) {
                log_message('error', '❌ UPDATE errors: ' . json_encode($this->demographicsModel->errors()));
            }
        } else {
            // Insertar nuevo registro con el campo (incluir worker_id)
            $data = [
                'worker_id' => $workerId,
                $fieldName => $fieldValue
            ];
            log_message('error', '🔍 Data to INSERT: ' . json_encode($data));
            log_message('error', '🔍 Attempting INSERT');

            $result = $this->demographicsModel->insert($data);
            log_message('error', '🔍 INSERT result: ' . var_export($result, true));
            if (!$result) {
                log_message('error', '❌ INSERT errors: ' . json_encode($this->demographicsModel->errors()));
            }
        }

        if ($result) {
            $responseData = [
                'success' => true,
                'message' => 'Campo guardado exitosamente',
                'field_name' => $fieldName,
                'field_value' => $fieldValue
            ];

            // VERIFICACIÓN DE INTEGRIDAD: Leer de nuevo desde la BD
            $debugMode = env('DEBUG_SAVE_VERIFICATION');
            log_message('error', '🔍 DEBUG_SAVE_VERIFICATION value: ' . var_export($debugMode, true));

            if ($debugMode === 'true' || $debugMode === true) {
                $savedData = $this->demographicsModel->getByWorkerId($workerId);
                log_message('error', '🔍 Saved data retrieved: ' . ($savedData ? 'YES' : 'NO'));

                if ($savedData) {
                    $dbValue = $savedData[$fieldName] ?? null;
                    log_message('error', "🔍 Field '{$fieldName}' - Sent: '{$fieldValue}' - DB: '{$dbValue}'");

                    $debugData = [[
                        'campo' => $fieldName,
                        'valor_enviado' => $fieldValue,
                        'valor_en_bd' => $dbValue,
                        'coincide' => ($fieldValue == $dbValue)
                    ]];

                    $responseData['debug_verification'] = $debugData;
                    $responseData['debug_enabled'] = true;
                    log_message('error', '✅ Debug verification added to response');
                }
            } else {
                log_message('error', '⚠️ DEBUG mode is OFF or invalid value');
            }

            return $this->response->setJSON($responseData);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al guardar el campo',
                'errors' => $this->demographicsModel->errors()
            ]);
        }
    }

    /**
     * Display intralaboral form (A or B depending on worker type)
     */
    public function intralaboral()
    {
        $session = session();
        $workerId = $session->get('assessment_worker_id');
        $intralaboralType = $session->get('assessment_intralaboral_type');

        if (!$workerId || !$intralaboralType) {
            return redirect()->to('/assessment/invalid');
        }

        $worker = $this->workerModel->find($workerId);
        $formType = 'intralaboral_' . $intralaboralType;
        $responses = $this->responseModel->getWorkerFormResponses($workerId, $formType);

        // Convert responses to array indexed by question number
        $savedResponses = [];
        foreach ($responses as $response) {
            $savedResponses[$response['question_number']] = $response['answer_value'];
        }

        return view('assessment/intralaboral_' . strtolower($intralaboralType), [
            'worker' => $worker,
            'responses' => $savedResponses
        ]);
    }

    /**
     * Save intralaboral responses (AJAX)
     */
    public function saveIntralaboral()
    {
        $session = session();
        $workerId = $session->get('assessment_worker_id');
        $intralaboralType = $session->get('assessment_intralaboral_type');

        if (!$workerId || !$intralaboralType) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sesión inválida']);
        }

        $responses = $this->request->getPost('responses');
        $formType = 'intralaboral_' . $intralaboralType;
        $sessionId = $this->request->getPost('session_id') ?? bin2hex(random_bytes(16));

        // Preparar array de actualización para campos condicionales
        $updateData = [];

        // Verificar si se respondió la pregunta condicional de atención a clientes
        $atiendeClientes = $this->request->getPost('atiende_clientes');
        if ($atiendeClientes !== null && $atiendeClientes !== '') {
            $updateData['atiende_clientes'] = ($atiendeClientes === 'si' || $atiendeClientes === '1' || $atiendeClientes === 1) ? 1 : 0;
            log_message('debug', "atiende_clientes = {$atiendeClientes} para worker {$workerId}");
        }

        // Verificar si se respondió la pregunta condicional de supervisión (es_jefe)
        // SOLO PARA FORMA A - Forma B no tiene esta pregunta
        if ($intralaboralType === 'A') {
            $esJefe = $this->request->getPost('es_jefe');
            if ($esJefe !== null && $esJefe !== '') {
                $updateData['es_jefe'] = ($esJefe === 'si' || $esJefe === '1' || $esJefe === 1) ? 1 : 0;
                log_message('debug', "es_jefe = {$esJefe} para worker {$workerId}");
            }
        }

        // Solo actualizar si hay datos
        if (!empty($updateData)) {
            $this->workerModel->update($workerId, $updateData);
            log_message('debug', "Campos condicionales actualizados: " . json_encode($updateData));
        }

        $savedCount = 0;
        $debugData = []; // Para verificación de integridad

        foreach ($responses as $questionNumber => $answerValue) {
            // PASO 1. CALIFICACIÓN DE LOS ÍTEMS según manual oficial
            // Aplicar scoring según Tabla 21 (Forma A) o Tabla 22 (Forma B)
            $scoredValue = $this->scoreIntralaboral($questionNumber, $answerValue, $intralaboralType);

            log_message('debug', "Intralaboral {$intralaboralType} - Q{$questionNumber}: Likert={$answerValue} -> Scored={$scoredValue}");

            if ($this->responseModel->saveResponse($workerId, $formType, $questionNumber, $scoredValue, $sessionId)) {
                $savedCount++;

                // VERIFICACIÓN DE INTEGRIDAD: Leer de nuevo desde la BD
                if (env('DEBUG_SAVE_VERIFICATION') === 'true') {
                    $verifyData = $this->responseModel
                        ->where('worker_id', $workerId)
                        ->where('form_type', $formType)
                        ->where('question_number', $questionNumber)
                        ->first();

                    if ($verifyData) {
                        $debugData[] = [
                            'pregunta' => $questionNumber,
                            'valor_enviado' => $answerValue,
                            'valor_transformado' => $scoredValue,
                            'valor_en_bd' => $verifyData['answer_value']
                        ];
                    }
                }
            }
        }

        $responseData = [
            'success' => true,
            'message' => "Se guardaron {$savedCount} respuestas",
            'saved_count' => $savedCount
        ];

        // Incluir datos de verificación si DEBUG_SAVE_VERIFICATION está activo
        if (env('DEBUG_SAVE_VERIFICATION') === 'true' && !empty($debugData)) {
            $responseData['debug_verification'] = $debugData;
            $responseData['debug_enabled'] = true;
        }

        return $this->response->setJSON($responseData);
    }

    /**
     * INLINE EDITING: Guarda UNA respuesta individual de intralaboral
     * Permite auto-guardado pregunta por pregunta con verificación inmediata
     */
    public function saveQuestionIntralaboral()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Solicitud no válida'
            ]);
        }

        $workerId = session()->get('assessment_worker_id');
        $intralaboralType = session()->get('assessment_intralaboral_type');

        if (!$workerId || !$intralaboralType) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Sesión no válida'
            ]);
        }

        $questionNumber = $this->request->getPost('question_number');
        $answerValue = $this->request->getPost('answer_value');
        $sessionId = $this->request->getPost('session_id') ?? bin2hex(random_bytes(16));

        $formType = 'intralaboral_' . $intralaboralType;

        // Aplicar scoring según tipo de forma
        $scoredValue = $this->scoreIntralaboral($questionNumber, $answerValue, $intralaboralType);

        // Guardar la respuesta
        $result = $this->responseModel->saveResponse($workerId, $formType, $questionNumber, $scoredValue, $sessionId);

        if ($result) {
            $responseData = [
                'success' => true,
                'message' => 'Respuesta guardada exitosamente',
                'question_number' => $questionNumber,
                'answer_value' => $answerValue,
                'scored_value' => $scoredValue
            ];

            // VERIFICACIÓN DE INTEGRIDAD: Leer de nuevo desde la BD
            $debugMode = env('DEBUG_SAVE_VERIFICATION');

            if ($debugMode === 'true' || $debugMode === true) {
                $verifyData = $this->responseModel
                    ->where('worker_id', $workerId)
                    ->where('form_type', $formType)
                    ->where('question_number', $questionNumber)
                    ->first();

                if ($verifyData) {
                    $debugData = [[
                        'pregunta' => $questionNumber,
                        'valor_enviado' => $answerValue,
                        'valor_transformado' => $scoredValue,
                        'valor_en_bd' => $verifyData['answer_value'],
                        'coincide' => ($scoredValue == $verifyData['answer_value'])
                    ]];

                    $responseData['debug_verification'] = $debugData;
                    $responseData['debug_enabled'] = true;
                }
            }

            return $this->response->setJSON($responseData);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al guardar la respuesta'
            ]);
        }
    }

    /**
     * Display extralaboral form
     */
    public function extralaboral()
    {
        $session = session();
        $workerId = $session->get('assessment_worker_id');

        if (!$workerId) {
            return redirect()->to('/assessment/invalid');
        }

        $worker = $this->workerModel->find($workerId);
        $responses = $this->responseModel->getWorkerFormResponses($workerId, 'extralaboral');

        $savedResponses = [];
        foreach ($responses as $response) {
            $savedResponses[$response['question_number']] = $response['answer_value'];
        }

        return view('assessment/extralaboral', [
            'worker' => $worker,
            'responses' => $savedResponses
        ]);
    }

    /**
     * Save extralaboral responses (AJAX)
     */
    public function saveExtralaboral()
    {
        $session = session();
        $workerId = $session->get('assessment_worker_id');

        if (!$workerId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sesión inválida']);
        }

        $responses = $this->request->getPost('responses');
        $sessionId = $this->request->getPost('session_id') ?? bin2hex(random_bytes(16));

        $savedCount = 0;
        $debugData = []; // Para verificación de integridad

        foreach ($responses as $questionNumber => $answerValue) {
            // PASO 1. CALIFICACIÓN DE LOS ÍTEMS según manual oficial
            // Aplicar scoring según Tabla 11 (Extralaboral)
            $scoredValue = $this->scoreExtralaboral($questionNumber, $answerValue);

            log_message('debug', "Extralaboral - Q{$questionNumber}: Likert={$answerValue} -> Scored={$scoredValue}");

            if ($this->responseModel->saveResponse($workerId, 'extralaboral', $questionNumber, $scoredValue, $sessionId)) {
                $savedCount++;

                // VERIFICACIÓN DE INTEGRIDAD: Leer de nuevo desde la BD
                if (env('DEBUG_SAVE_VERIFICATION') === 'true') {
                    $verifyData = $this->responseModel
                        ->where('worker_id', $workerId)
                        ->where('form_type', 'extralaboral')
                        ->where('question_number', $questionNumber)
                        ->first();

                    if ($verifyData) {
                        $debugData[] = [
                            'pregunta' => $questionNumber,
                            'valor_enviado' => $answerValue,
                            'valor_transformado' => $scoredValue,
                            'valor_en_bd' => $verifyData['answer_value']
                        ];
                    }
                }
            }
        }

        $responseData = [
            'success' => true,
            'message' => "Se guardaron {$savedCount} respuestas",
            'saved_count' => $savedCount
        ];

        // Incluir datos de verificación si DEBUG_SAVE_VERIFICATION está activo
        if (env('DEBUG_SAVE_VERIFICATION') === 'true' && !empty($debugData)) {
            $responseData['debug_verification'] = $debugData;
            $responseData['debug_enabled'] = true;
        }

        return $this->response->setJSON($responseData);
    }

    /**
     * INLINE EDITING: Guarda UNA respuesta individual de extralaboral
     */
    public function saveQuestionExtralaboral()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Solicitud no válida']);
        }

        $workerId = session()->get('assessment_worker_id');
        if (!$workerId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sesión no válida']);
        }

        $questionNumber = $this->request->getPost('question_number');
        $answerValue = $this->request->getPost('answer_value');
        $sessionId = $this->request->getPost('session_id') ?? bin2hex(random_bytes(16));

        // Aplicar scoring
        $scoredValue = $this->scoreExtralaboral($questionNumber, $answerValue);

        $result = $this->responseModel->saveResponse($workerId, 'extralaboral', $questionNumber, $scoredValue, $sessionId);

        if ($result) {
            $responseData = [
                'success' => true,
                'message' => 'Respuesta guardada exitosamente',
                'question_number' => $questionNumber,
                'answer_value' => $answerValue,
                'scored_value' => $scoredValue
            ];

            $debugMode = env('DEBUG_SAVE_VERIFICATION');
            if ($debugMode === 'true' || $debugMode === true) {
                $verifyData = $this->responseModel
                    ->where('worker_id', $workerId)
                    ->where('form_type', 'extralaboral')
                    ->where('question_number', $questionNumber)
                    ->first();

                if ($verifyData) {
                    $responseData['debug_verification'] = [[
                        'pregunta' => $questionNumber,
                        'valor_enviado' => $answerValue,
                        'valor_transformado' => $scoredValue,
                        'valor_en_bd' => $verifyData['answer_value'],
                        'coincide' => ($scoredValue == $verifyData['answer_value'])
                    ]];
                    $responseData['debug_enabled'] = true;
                }
            }

            return $this->response->setJSON($responseData);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Error al guardar la respuesta']);
        }
    }

    /**
     * Display estres form
     */
    public function estres()
    {
        $session = session();
        $workerId = $session->get('assessment_worker_id');

        if (!$workerId) {
            return redirect()->to('/assessment/invalid');
        }

        $worker = $this->workerModel->find($workerId);
        $responses = $this->responseModel->getWorkerFormResponses($workerId, 'estres');

        $savedResponses = [];
        foreach ($responses as $response) {
            $savedResponses[$response['question_number']] = $response['answer_value'];
        }

        return view('assessment/estres', [
            'worker' => $worker,
            'responses' => $savedResponses
        ]);
    }

    /**
     * Save estres responses (AJAX)
     */
    public function saveEstres()
    {
        $session = session();
        $workerId = $session->get('assessment_worker_id');

        if (!$workerId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sesión inválida']);
        }

        log_message('error', '🔍 ===== GUARDANDO CUESTIONARIO DE ESTRÉS - Worker ID: ' . $workerId . ' =====');
        log_message('error', '🔍 RAW POST DATA: ' . json_encode($this->request->getPost()));

        // Las respuestas vienen como array directo desde FormData
        $responses = $this->request->getPost('responses');
        $sessionId = $this->request->getPost('session_id') ?? bin2hex(random_bytes(16));

        log_message('error', '🔍 Responses type: ' . gettype($responses));
        log_message('error', '🔍 Respuestas recibidas count: ' . (is_array($responses) ? count($responses) : 0));
        log_message('error', '🔍 Respuestas content (first 5): ' . json_encode(is_array($responses) ? array_slice($responses, 0, 5, true) : []));

        $savedCount = 0;
        $debugData = []; // Para verificación de integridad

        foreach ($responses as $questionNumber => $answerValue) {
            // PASO 1. CALIFICACIÓN DE LOS ÍTEMS según manual oficial
            // Aplicar scoring según Tabla 4 (Estrés - 3 grupos)
            $scoredValue = $this->scoreEstres($questionNumber, $answerValue);

            log_message('debug', "Estrés - Q{$questionNumber}: Likert={$answerValue} -> Scored={$scoredValue}");

            if ($this->responseModel->saveResponse($workerId, 'estres', $questionNumber, $scoredValue, $sessionId)) {
                $savedCount++;

                // VERIFICACIÓN DE INTEGRIDAD: Leer de nuevo desde la BD
                if (env('DEBUG_SAVE_VERIFICATION') === 'true') {
                    $verifyData = $this->responseModel
                        ->where('worker_id', $workerId)
                        ->where('form_type', 'estres')
                        ->where('question_number', $questionNumber)
                        ->first();

                    if ($verifyData) {
                        $debugData[] = [
                            'pregunta' => $questionNumber,
                            'valor_enviado' => $answerValue,
                            'valor_transformado' => $scoredValue,
                            'valor_en_bd' => $verifyData['answer_value']
                        ];
                    }
                }
            }
        }

        log_message('error', '🔍 Respuestas guardadas: ' . $savedCount . ' de 31 esperadas');

        // VALIDACIÓN: Solo marcar como completado si tiene las 31 preguntas de estrés
        $ESTRES_REQUIRED_QUESTIONS = 31;

        if ($savedCount < $ESTRES_REQUIRED_QUESTIONS) {
            log_message('error', '⚠️ NO SE MARCA COMO COMPLETADO - Faltan ' . ($ESTRES_REQUIRED_QUESTIONS - $savedCount) . ' preguntas de estrés');

            $responseData = [
                'success' => false,
                'message' => "Debes responder todas las preguntas para completar el cuestionario ({$savedCount}/{$ESTRES_REQUIRED_QUESTIONS})",
                'saved_count' => $savedCount,
                'required_count' => $ESTRES_REQUIRED_QUESTIONS
            ];

            // Incluir datos de verificación si DEBUG_SAVE_VERIFICATION está activo
            if (env('DEBUG_SAVE_VERIFICATION') === 'true' && !empty($debugData)) {
                $responseData['debug_verification'] = $debugData;
                $responseData['debug_enabled'] = true;
            }

            return $this->response->setJSON($responseData);
        }

        log_message('error', '✅ Todas las preguntas de estrés completadas');

        // Verificar que TODOS los formularios estén completos antes de marcar como completado
        $worker = $this->workerModel->find($workerId);
        $intralaboralType = $worker['intralaboral_type'];

        log_message('error', '🔍 Verificando completitud de TODOS los formularios...');

        // Usar el servicio de cálculo para verificar que todos los formularios estén completos
        $allComplete = $this->calculationService->allFormsComplete($workerId, $intralaboralType);

        if (!$allComplete) {
            log_message('error', '⚠️ NO SE MARCA COMO COMPLETADO - Faltan otros formularios');

            $responseData = [
                'success' => false,
                'message' => "Debes completar todos los cuestionarios de la batería antes de finalizar",
                'saved_count' => $savedCount
            ];

            // Incluir datos de verificación si DEBUG_SAVE_VERIFICATION está activo
            if (env('DEBUG_SAVE_VERIFICATION') === 'true' && !empty($debugData)) {
                $responseData['debug_verification'] = $debugData;
                $responseData['debug_enabled'] = true;
            }

            return $this->response->setJSON($responseData);
        }

        log_message('error', '✅ TODOS los formularios están completos - Marcando como completado');

        // Update worker status to completed
        $this->workerModel->update($workerId, [
            'status' => 'completado',
            'completed_at' => date('Y-m-d H:i:s')
        ]);

        log_message('error', '✅ Worker marcado como COMPLETADO');

        // *** CALCULATE AND SAVE RESULTS ***
        try {
            log_message('error', '🔍 Calculando resultados...');
            $calculationResult = $this->calculationService->calculateAndSaveResults($workerId);

            if ($calculationResult === false) {
                log_message('error', "❌ Failed to calculate results for worker {$workerId} after completing assessment");
                // Continue anyway - don't block the completion
            } else {
                log_message('info', "✅ Successfully calculated and saved results for worker {$workerId}");
            }
        } catch (\Exception $e) {
            log_message('error', "❌ Exception calculating results for worker {$workerId}: " . $e->getMessage());
            // Continue anyway - don't block the completion
        }

        $responseData = [
            'success' => true,
            'message' => "Batería completada exitosamente",
            'saved_count' => $savedCount,
            'redirect' => base_url('assessment/completed')
        ];

        // Incluir datos de verificación si DEBUG_SAVE_VERIFICATION está activo
        if (env('DEBUG_SAVE_VERIFICATION') === 'true' && !empty($debugData)) {
            $responseData['debug_verification'] = $debugData;
            $responseData['debug_enabled'] = true;
        }

        return $this->response->setJSON($responseData);
    }

    /**
     * INLINE EDITING: Guarda UNA respuesta individual de estrés
     */
    public function saveQuestionEstres()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Solicitud no válida']);
        }

        $workerId = session()->get('assessment_worker_id');
        if (!$workerId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sesión no válida']);
        }

        $questionNumber = $this->request->getPost('question_number');
        $answerValue = $this->request->getPost('answer_value');
        $sessionId = $this->request->getPost('session_id') ?? bin2hex(random_bytes(16));

        // Aplicar scoring
        $scoredValue = $this->scoreEstres($questionNumber, $answerValue);

        $result = $this->responseModel->saveResponse($workerId, 'estres', $questionNumber, $scoredValue, $sessionId);

        if ($result) {
            $responseData = [
                'success' => true,
                'message' => 'Respuesta guardada exitosamente',
                'question_number' => $questionNumber,
                'answer_value' => $answerValue,
                'scored_value' => $scoredValue
            ];

            $debugMode = env('DEBUG_SAVE_VERIFICATION');
            if ($debugMode === 'true' || $debugMode === true) {
                $verifyData = $this->responseModel
                    ->where('worker_id', $workerId)
                    ->where('form_type', 'estres')
                    ->where('question_number', $questionNumber)
                    ->first();

                if ($verifyData) {
                    $responseData['debug_verification'] = [[
                        'pregunta' => $questionNumber,
                        'valor_enviado' => $answerValue,
                        'valor_transformado' => $scoredValue,
                        'valor_en_bd' => $verifyData['answer_value'],
                        'coincide' => ($scoredValue == $verifyData['answer_value'])
                    ]];
                    $responseData['debug_enabled'] = true;
                }
            }

            return $this->response->setJSON($responseData);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Error al guardar la respuesta']);
        }
    }

    /**
     * Completion page
     */
    public function completed()
    {
        $session = session();
        $workerId = $session->get('assessment_worker_id');

        if (!$workerId) {
            return redirect()->to('/assessment/invalid');
        }

        $worker = $this->workerModel->find($workerId);

        return view('assessment/completed', [
            'worker' => $worker
        ]);
    }

    /**
     * Get worker progress (AJAX)
     */
    public function getProgress()
    {
        $session = session();
        $workerId = $session->get('assessment_worker_id');

        if (!$workerId) {
            return $this->response->setJSON(['success' => false]);
        }

        $progress = $this->responseModel->getOverallProgress($workerId);
        $demographics = $this->demographicsModel->isCompleted($workerId);

        return $this->response->setJSON([
            'success' => true,
            'demographics' => $demographics,
            'progress' => $progress
        ]);
    }

    /**
     * Invalid token page
     */
    public function invalid()
    {
        return view('assessment/invalid_token');
    }
}
