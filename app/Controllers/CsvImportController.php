<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CsvImportModel;
use App\Models\BatteryServiceModel;
use App\Models\WorkerModel;
use App\Models\ResponseModel;
use App\Models\WorkerDemographicsModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Controlador para importación de respuestas desde CSV
 * Módulo de contingencia para cuando el sistema principal falla
 */
class CsvImportController extends BaseController
{
    protected $csvImportModel;
    protected $batteryServiceModel;
    protected $workerModel;
    protected $responseModel;
    protected $workerDemographicsModel;

    /**
     * TABLA 21: Intralaboral Forma A - Preguntas INVERTIDAS
     */
    private $intralaboralA_invertidas = [
        1, 2, 3, 7, 8, 10, 11, 13, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26,
        27, 28, 29, 30, 31, 33, 35, 36, 37, 38, 52, 80, 106, 107, 108, 109, 110,
        111, 112, 113, 114, 115, 116, 117, 118, 119, 120, 121, 122, 123
    ];

    /**
     * TABLA 22: Intralaboral Forma B - Preguntas INVERTIDAS
     */
    private $intralaboralB_invertidas = [
        1, 2, 3, 7, 8, 10, 11, 13, 15, 16, 17, 18, 19, 20, 21, 23, 25, 26, 27, 28,
        66, 89, 90, 91, 92, 93, 94, 95, 96
    ];

    /**
     * TABLA 11: Extralaboral - Preguntas INVERTIDAS
     */
    private $extralaboral_invertidas = [
        2, 3, 6, 24, 26, 28, 30, 31
    ];

    /**
     * TABLA 4: Estrés - Grupos de preguntas con diferentes escalas
     */
    private $estres_grupo1 = [1, 2, 3, 9, 13, 14, 15, 23, 24]; // Siempre=9, Casi siempre=6, A veces=3, Nunca=0
    private $estres_grupo2 = [4, 5, 6, 10, 11, 16, 17, 18, 19, 25, 26, 27, 28]; // Siempre=6, Casi siempre=4, A veces=2, Nunca=0
    private $estres_grupo3 = [7, 8, 12, 20, 21, 22, 29, 30, 31]; // Siempre=3, Casi siempre=2, A veces=1, Nunca=0

    public function __construct()
    {
        $this->csvImportModel = new CsvImportModel();
        $this->batteryServiceModel = new BatteryServiceModel();
        $this->workerModel = new WorkerModel();
        $this->responseModel = new ResponseModel();
        $this->workerDemographicsModel = new WorkerDemographicsModel();
        helper(['form', 'url', 'filesystem']);
    }

    /**
     * Vista principal del módulo de importación CSV
     */
    public function index()
    {
        // Verificar autenticación
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $roleName = session()->get('role_name');

        // Solo consultores pueden importar CSV
        if ($roleName !== 'consultor') {
            return redirect()->to('/dashboard')->with('error', 'No tienes permisos para acceder a este módulo');
        }

        $userId = session()->get('id');

        // Obtener todos los servicios en curso (todos los consultores ven todos)
        $services = $this->batteryServiceModel
            ->where('status', 'en_curso')
            ->orderBy('created_at', 'DESC')
            ->findAll();

        // Obtener historial de importaciones
        $imports = $this->csvImportModel
            ->select('csv_imports.*, battery_services.service_name, users.name as imported_by_name')
            ->join('battery_services', 'battery_services.id = csv_imports.battery_service_id')
            ->join('users', 'users.id = csv_imports.imported_by')
            ->where('csv_imports.imported_by', $userId)
            ->orderBy('csv_imports.created_at', 'DESC')
            ->limit(20)
            ->findAll();

        return view('csv_import/index', [
            'title' => 'Importación CSV - Módulo de Contingencia',
            'services' => $services,
            'imports' => $imports
        ]);
    }

    /**
     * Mapear respuestas de texto a valores numéricos
     *
     * Intralaboral y Extralaboral: 5 opciones de respuesta según manual oficial
     *
     * PREGUNTAS NORMALES:
     *   - Siempre = 0, Casi siempre = 1, Algunas veces = 2, Casi nunca = 3, Nunca = 4
     *
     * PREGUNTAS INVERTIDAS (según Tablas 21, 22, 11):
     *   - Siempre = 4, Casi siempre = 3, Algunas veces = 2, Casi nunca = 1, Nunca = 0
     *
     * La inversión se aplica AQUÍ directamente según el tipo de formulario y número de pregunta.
     * Los valores se guardan ya calificados en la BD.
     *
     * Estrés: Se guarda como texto normalizado para que EstresScoring.php
     *         aplique la conversión correcta según grupos (Tabla 4)
     */
    protected function mapTextToNumber($text, $formType, $questionNumber = null)
    {
        $text = strtolower(trim($text));

        // Mapeo NORMAL para Intralaboral y Extralaboral
        $normalMap = [
            'siempre' => 0,
            'casi siempre' => 1,
            'algunas veces' => 2,
            'casi nunca' => 3,
            'nunca' => 4
        ];

        // Mapeo INVERTIDO para preguntas invertidas (Tablas 21, 22, 11)
        $invertedMap = [
            'siempre' => 4,
            'casi siempre' => 3,
            'algunas veces' => 2,
            'casi nunca' => 1,
            'nunca' => 0
        ];

        // Para ESTRÉS: Aplicar conversión directa según Tabla 4 (3 grupos con escalas diferentes)
        if ($formType === 'estres') {
            // Si ya es un número, devolverlo tal cual
            if (is_numeric($text)) {
                return (int)$text;
            }

            // Determinar el grupo de la pregunta según Tabla 4
            $grupo = null;
            if ($questionNumber !== null) {
                if (in_array($questionNumber, $this->estres_grupo1)) {
                    $grupo = 1; // Siempre=9, Casi siempre=6, A veces=3, Nunca=0
                } elseif (in_array($questionNumber, $this->estres_grupo2)) {
                    $grupo = 2; // Siempre=6, Casi siempre=4, A veces=2, Nunca=0
                } elseif (in_array($questionNumber, $this->estres_grupo3)) {
                    $grupo = 3; // Siempre=3, Casi siempre=2, A veces=1, Nunca=0
                }
            }

            // Mapeo según el grupo
            $estresMapGrupo1 = ['siempre' => 9, 'casi siempre' => 6, 'algunas veces' => 3, 'a veces' => 3, 'nunca' => 0];
            $estresMapGrupo2 = ['siempre' => 6, 'casi siempre' => 4, 'algunas veces' => 2, 'a veces' => 2, 'nunca' => 0];
            $estresMapGrupo3 = ['siempre' => 3, 'casi siempre' => 2, 'algunas veces' => 1, 'a veces' => 1, 'nunca' => 0];

            $mapToUse = null;
            if ($grupo === 1) {
                $mapToUse = $estresMapGrupo1;
            } elseif ($grupo === 2) {
                $mapToUse = $estresMapGrupo2;
            } elseif ($grupo === 3) {
                $mapToUse = $estresMapGrupo3;
            }

            if ($mapToUse === null) {
                log_message('error', "[CSV Import] No se pudo determinar el grupo de estrés para pregunta {$questionNumber}");
                return null;
            }

            $mapped = $mapToUse[$text] ?? null;

            if ($mapped === null) {
                log_message('warning', "[CSV Import] Valor de estrés no reconocido: '{$text}' (Grupo {$grupo}, Pregunta {$questionNumber})");
            }

            return $mapped;
        }

        // Intralaboral y Extralaboral: Determinar si es pregunta invertida
        $isInverted = false;

        if ($questionNumber !== null) {
            if ($formType === 'intralaboral_A') {
                $isInverted = in_array($questionNumber, $this->intralaboralA_invertidas);
            } elseif ($formType === 'intralaboral_B') {
                $isInverted = in_array($questionNumber, $this->intralaboralB_invertidas);
            } elseif ($formType === 'extralaboral') {
                $isInverted = in_array($questionNumber, $this->extralaboral_invertidas);
            }
        }

        // Seleccionar el mapeo correcto según si es invertida o normal
        $mapToUse = $isInverted ? $invertedMap : $normalMap;

        // Si ya es un número válido (0-4), devolverlo
        if (is_numeric($text)) {
            $numValue = (int)$text;
            // Validar que esté en el rango válido (0, 1, 2, 3, 4)
            if ($numValue >= 0 && $numValue <= 4) {
                return $numValue;
            }
            log_message('warning', "[CSV Import] Valor numérico inválido para intralaboral/extralaboral: {$numValue}. Debe estar entre 0 y 4.");
            return null;
        }

        // Mapear texto a número usando el mapa correcto
        $mapped = $mapToUse[$text] ?? null;

        if ($mapped === null) {
            log_message('warning', "[CSV Import] Valor de texto no reconocido para intralaboral/extralaboral: '{$text}'. Pregunta: {$questionNumber}, Invertida: " . ($isInverted ? 'Sí' : 'No'));
        }

        return $mapped;
    }

    /**
     * Procesar archivo CSV cargado
     */
    public function upload()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $roleName = session()->get('role_name');
        if ($roleName !== 'consultor') {
            return redirect()->to('/csv-import')->with('error', 'No tienes permisos');
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'battery_service_id' => 'required|integer',
            'csv_file' => 'uploaded[csv_file]|max_size[csv_file,10240]|ext_in[csv_file,csv,txt]',
            'csv_format' => 'required|in_list[vertical,horizontal]',
            'form_type' => 'required|in_list[A,B]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->with('error', 'Error en la validación: ' . implode(', ', $validation->getErrors()));
        }

        $serviceId = $this->request->getPost('battery_service_id');
        $file = $this->request->getFile('csv_file');
        $format = $this->request->getPost('csv_format');
        $formType = $this->request->getPost('form_type');

        // Verificar que el servicio existe
        $service = $this->batteryServiceModel->find($serviceId);
        if (!$service) {
            return redirect()->back()->with('error', 'Servicio no encontrado');
        }

        // Verificar que el usuario es consultor, superadmin o director comercial
        $roleName = session()->get('role_name');
        if (!in_array($roleName, ['consultor', 'superadmin', 'director_comercial'])) {
            return redirect()->back()->with('error', 'No tienes permisos para importar CSV');
        }

        if (!$file->isValid()) {
            return redirect()->back()->with('error', 'Archivo inválido: ' . $file->getErrorString());
        }

        // Registrar importación
        $importId = $this->csvImportModel->insert([
            'battery_service_id' => $serviceId,
            'imported_by' => session()->get('id'),
            'file_name' => $file->getName(),
            'form_type' => $formType,
            'status' => 'procesando'
        ]);

        // Procesar CSV según formato
        try {
            if ($format === 'horizontal') {
                $result = $this->processHorizontalCSV($file, $serviceId, $importId, $formType);
            } else {
                $result = $this->processCSV($file, $serviceId, $importId);
            }

            // Actualizar registro de importación
            $this->csvImportModel->update($importId, [
                'total_rows' => $result['total'],
                'imported_rows' => $result['success'],
                'failed_rows' => $result['failed'],
                'error_log' => json_encode($result['errors']),
                'status' => $result['failed'] > 0 ? 'completado_con_errores' : 'completado'
            ]);

            if ($result['success'] > 0) {
                return redirect()->to('/csv-import')->with('success',
                    "Importación completada: {$result['success']} trabajadores importados, {$result['failed']} fallidos");
            } else {
                return redirect()->to('/csv-import')->with('error',
                    "No se pudo importar ningún registro. Revisa el formato del CSV");
            }

        } catch (\Exception $e) {
            $this->csvImportModel->update($importId, [
                'status' => 'error',
                'error_log' => json_encode(['error' => $e->getMessage()])
            ]);

            return redirect()->to('/csv-import')->with('error', 'Error al procesar CSV: ' . $e->getMessage());
        }
    }

    /**
     * Iniciar importación por lotes - Guardar CSV y retornar importId
     */
    public function startBatchImport()
    {
        try {
            if (!session()->get('isLoggedIn')) {
                return $this->response->setJSON(['success' => false, 'message' => 'No autorizado']);
            }

            log_message('error', '[BATCH] Iniciando importación por lotes');
            log_message('error', '[BATCH] Usuario: ' . session()->get('id'));

            $validation = \Config\Services::validation();
            $validation->setRules([
                'battery_service_id' => 'required|integer',
                'csv_file' => 'uploaded[csv_file]|max_size[csv_file,10240]|ext_in[csv_file,csv,txt]',
                'csv_format' => 'required|in_list[vertical,horizontal]',
                'form_type' => 'required|in_list[A,B]'
            ]);

            if (!$validation->withRequest($this->request)->run()) {
                $errors = implode(', ', $validation->getErrors());
                log_message('error', '[BATCH] Error de validación: ' . $errors);
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error en validación: ' . $errors
                ]);
            }

            $serviceId = $this->request->getPost('battery_service_id');
            $file = $this->request->getFile('csv_file');
            $format = $this->request->getPost('csv_format');
            $formType = $this->request->getPost('form_type');

            log_message('error', '[BATCH] Servicio: ' . $serviceId);
            log_message('error', '[BATCH] Formato: ' . $format);
            log_message('error', '[BATCH] Tipo formulario: ' . $formType);

            if (!$file->isValid()) {
                $error = $file->getErrorString();
                log_message('error', '[BATCH] Archivo inválido: ' . $error);
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Archivo inválido: ' . $error
                ]);
            }

            // Guardar archivo temporalmente
            $uploadPath = WRITEPATH . 'uploads/csv_imports/';
            if (!is_dir($uploadPath)) {
                if (!mkdir($uploadPath, 0755, true)) {
                    log_message('error', '[BATCH] No se pudo crear directorio: ' . $uploadPath);
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'No se pudo crear directorio temporal'
                    ]);
                }
            }

            $fileName = uniqid('import_') . '_' . $file->getName();
            if (!$file->move($uploadPath, $fileName)) {
                log_message('error', '[BATCH] No se pudo mover archivo a: ' . $uploadPath . $fileName);
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No se pudo guardar el archivo'
                ]);
            }

            log_message('error', '[BATCH] Archivo guardado en: ' . $uploadPath . $fileName);

            // Crear registro de importación
            $importId = $this->csvImportModel->insert([
                'battery_service_id' => $serviceId,
                'imported_by' => session()->get('id'),
                'file_name' => $file->getName(),
                'form_type' => $formType,
                'status' => 'procesando',
                'total_rows' => 0,
                'imported_rows' => 0,
                'failed_rows' => 0
            ]);

            log_message('error', '[BATCH] Registro de importación creado: ' . $importId);

            // Guardar información en sesión para procesar por lotes
            session()->set('csv_import_' . $importId, [
                'file_path' => $uploadPath . $fileName,
                'service_id' => $serviceId,
                'format' => $format,
                'form_type' => $formType,
                'offset' => 0 // Línea actual del CSV
            ]);

            log_message('error', '[BATCH] Importación iniciada exitosamente');

            return $this->response->setJSON([
                'success' => true,
                'importId' => $importId,
                'message' => 'Importación iniciada'
            ]);

        } catch (\Exception $e) {
            log_message('error', '[BATCH] Excepción: ' . $e->getMessage());
            log_message('error', '[BATCH] Stack trace: ' . $e->getTraceAsString());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Procesar siguiente lote de 5 filas
     */
    public function processBatch($importId)
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['success' => false, 'message' => 'No autorizado']);
        }

        $importData = session()->get('csv_import_' . $importId);
        if (!$importData) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sesión de importación no encontrada']);
        }

        $batchSize = 2; // Procesamos 2 trabajadores a la vez (~44 seg < 60 seg timeout)
        $filePath = $importData['file_path'];
        $serviceId = $importData['service_id'];
        $format = $importData['format'];
        $formType = $importData['form_type'];
        $offset = $importData['offset'];

        if (!file_exists($filePath)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Archivo CSV no encontrado']);
        }

        try {
            $handle = fopen($filePath, 'r');

            // Leer headers
            $headers = fgetcsv($handle, 0, ';');
            $headers = array_map(function($h) {
                return strtolower(trim(str_replace("\xEF\xBB\xBF", '', $h)));
            }, $headers);

            // Saltar líneas ya procesadas
            $currentLine = 0;
            while ($currentLine < $offset && !feof($handle)) {
                fgetcsv($handle, 0, ';');
                $currentLine++;
            }

            // Procesar siguiente lote
            $batchSuccess = 0;
            $batchFailed = 0;
            $batchErrors = [];
            $linesProcessed = 0;
            $endOfFile = false;

            while ($linesProcessed < $batchSize && ($row = fgetcsv($handle, 0, ';')) !== false) {
                $currentLine++;
                $linesProcessed++;
                $data = array_combine($headers, $row);

                try {
                    if ($format === 'horizontal') {
                        $this->importHorizontalRow($data, $serviceId, $headers, $formType);
                    } else {
                        $this->importRow($data, $serviceId);
                    }
                    $batchSuccess++;
                } catch (\Exception $e) {
                    $batchFailed++;
                    $batchErrors[] = "Fila " . ($offset + $linesProcessed) . ": " . $e->getMessage();
                }
            }

            $endOfFile = feof($handle);
            fclose($handle);

            // Actualizar registro de importación
            $import = $this->csvImportModel->find($importId);
            $totalSuccess = $import['imported_rows'] + $batchSuccess;
            $totalFailed = $import['failed_rows'] + $batchFailed;
            $totalRows = $totalSuccess + $totalFailed;

            $updateData = [
                'total_rows' => $totalRows,
                'imported_rows' => $totalSuccess,
                'failed_rows' => $totalFailed
            ];

            if ($endOfFile) {
                $updateData['status'] = $totalFailed > 0 ? 'completado_con_errores' : 'completado';

                // Limpiar sesión y archivo temporal
                session()->remove('csv_import_' . $importId);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            } else {
                // Actualizar offset para el siguiente lote
                $importData['offset'] = $offset + $linesProcessed;
                session()->set('csv_import_' . $importId, $importData);
            }

            $this->csvImportModel->update($importId, $updateData);

            return $this->response->setJSON([
                'success' => true,
                'completed' => $endOfFile,
                'batch' => [
                    'success' => $batchSuccess,
                    'failed' => $batchFailed,
                    'errors' => $batchErrors
                ],
                'total' => [
                    'rows' => $totalRows,
                    'success' => $totalSuccess,
                    'failed' => $totalFailed
                ]
            ]);

        } catch (\Exception $e) {
            $this->csvImportModel->update($importId, [
                'status' => 'error',
                'error_log' => json_encode(['error' => $e->getMessage()])
            ]);

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Obtener el ID del último import del usuario actual
     */
    public function getLatestImportId()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['success' => false, 'message' => 'No autorizado']);
        }

        $userId = session()->get('id');
        $import = $this->csvImportModel
            ->where('imported_by', $userId)
            ->where('status', 'procesando')
            ->orderBy('id', 'DESC')
            ->first();

        if ($import) {
            return $this->response->setJSON([
                'success' => true,
                'importId' => $import['id']
            ]);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'No se encontró importación en proceso']);
    }

    /**
     * Obtener progreso de importación en tiempo real
     */
    public function getImportProgress($importId)
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['success' => false, 'message' => 'No autorizado']);
        }

        $import = $this->csvImportModel->find($importId);
        if (!$import) {
            return $this->response->setJSON(['success' => false, 'message' => 'Importación no encontrada']);
        }

        return $this->response->setJSON([
            'success' => true,
            'status' => $import['status'],
            'total' => $import['total_rows'] ?? 0,
            'imported' => $import['imported_rows'] ?? 0,
            'failed' => $import['failed_rows'] ?? 0
        ]);
    }

    /**
     * Eliminar una importación completa (reversión)
     */
    public function deleteImport($importId)
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['success' => false, 'message' => 'No autorizado']);
        }

        $roleName = session()->get('role_name');
        if ($roleName !== 'consultor' && $roleName !== 'superadmin') {
            return $this->response->setJSON(['success' => false, 'message' => 'No tienes permisos']);
        }

        // Obtener la importación
        $import = $this->csvImportModel->find($importId);
        if (!$import) {
            return $this->response->setJSON(['success' => false, 'message' => 'Importación no encontrada']);
        }

        $serviceId = $import['battery_service_id'];

        try {
            $db = \Config\Database::connect();

            // Obtener los trabajadores del servicio que tienen respuestas
            $workers = $this->workerModel->where('battery_service_id', $serviceId)->findAll();
            $workerIds = array_column($workers, 'id');

            if (!empty($workerIds)) {
                // Eliminar respuestas de estos trabajadores
                $db->table('responses')->whereIn('worker_id', $workerIds)->delete();

                // Eliminar resultados calculados
                $db->table('calculated_results')->whereIn('worker_id', $workerIds)->delete();

                // Resetear estado de trabajadores a pendiente
                $this->workerModel->whereIn('id', $workerIds)->set([
                    'status' => 'pendiente',
                    'started_at' => null,
                    'completed_at' => null
                ])->update();
            }

            // Eliminar el registro de importación
            $this->csvImportModel->delete($importId);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Importación eliminada correctamente. Se eliminaron las respuestas y resultados de ' . count($workerIds) . ' trabajadores.'
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Procesar archivo CSV línea por línea
     */
    protected function processCSV($file, $serviceId, $importId)
    {
        $handle = fopen($file->getTempName(), 'r');
        $headers = fgetcsv($handle, 0, ';');

        // Normalizar headers (quitar BOM, espacios, minúsculas)
        $headers = array_map(function($h) {
            return strtolower(trim(str_replace("\xEF\xBB\xBF", '', $h)));
        }, $headers);

        $total = 0;
        $success = 0;
        $failed = 0;
        $errors = [];

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            $total++;
            $data = array_combine($headers, $row);

            try {
                $this->importRow($data, $serviceId);
                $success++;
            } catch (\Exception $e) {
                $failed++;
                $errors[] = "Fila {$total}: " . $e->getMessage();
            }

            // Actualizar progreso en tiempo real
            if ($total % 5 === 0 || $total === 1) {
                $this->csvImportModel->update($importId, [
                    'total_rows' => $total,
                    'imported_rows' => $success,
                    'failed_rows' => $failed
                ]);
            }
        }

        fclose($handle);

        return [
            'total' => $total,
            'success' => $success,
            'failed' => $failed,
            'errors' => $errors
        ];
    }

    /**
     * Procesar archivo CSV horizontal (formato matriz: un trabajador por fila)
     */
    protected function processHorizontalCSV($file, $serviceId, $importId, $formType)
    {
        log_message('error', '========================================');
        log_message('error', '🚀 [CSV HORIZONTAL] Iniciando procesamiento');
        log_message('error', "📁 Archivo temporal: {$file->getTempName()}");
        log_message('error', "📋 Service ID: {$serviceId}");
        log_message('error', "🆔 Import ID: {$importId}");
        log_message('error', "📋 Form Type: {$formType}");

        $handle = fopen($file->getTempName(), 'r');

        if (!$handle) {
            log_message('error', '❌ [CSV HORIZONTAL] No se pudo abrir el archivo');
            throw new \Exception('No se pudo abrir el archivo CSV');
        }

        log_message('error', '✅ [CSV HORIZONTAL] Archivo abierto correctamente');

        $headers = fgetcsv($handle, 0, ';');

        log_message('error', '📊 [CSV HORIZONTAL] Headers RAW: ' . json_encode($headers));
        log_message('error', '📏 [CSV HORIZONTAL] Total headers: ' . count($headers));

        // Normalizar headers (quitar BOM, espacios, minúsculas)
        $headers = array_map(function($h) {
            return strtolower(trim(str_replace("\xEF\xBB\xBF", '', $h)));
        }, $headers);

        log_message('error', '📊 [CSV HORIZONTAL] Headers NORMALIZADOS: ' . json_encode($headers));
        log_message('error', '🔍 [CSV HORIZONTAL] Primeros 5 headers: ' . json_encode(array_slice($headers, 0, 5)));

        $total = 0;
        $success = 0;
        $failed = 0;
        $errors = [];
        $rowNumber = 1; // Empezamos en 1 (la fila de headers)

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            $rowNumber++;
            $total++;

            log_message('error', "");
            log_message('error', "--- Procesando fila #{$rowNumber} (Trabajador #{$total}) ---");
            log_message('error', "📝 RAW data: " . json_encode($row));
            log_message('error', "📏 Columnas en fila: " . count($row));

            // Verificar que haya la misma cantidad de columnas
            if (count($row) !== count($headers)) {
                log_message('error', "⚠️ ADVERTENCIA: Columnas en fila ({count($row)}) != Headers ({count($headers)})");
            }

            $data = array_combine($headers, $row);

            log_message('error', "📋 Documento: " . ($data['documento'] ?? 'NO ENCONTRADO'));
            log_message('error', "👤 Nombre: " . ($data['nombre'] ?? 'NO ENCONTRADO'));

            try {
                log_message('error', "🔄 Llamando a importHorizontalRow...");
                $this->importHorizontalRow($data, $serviceId, $headers, $formType);
                $success++;
                log_message('error', "✅ Trabajador #{$total} importado EXITOSAMENTE");
            } catch (\Exception $e) {
                $failed++;
                $errorMsg = "Trabajador {$total} (Doc: " . ($data['documento'] ?? 'N/A') . "): " . $e->getMessage();
                $errors[] = $errorMsg;
                log_message('error', "❌ ERROR en trabajador #{$total}: " . $e->getMessage());
                log_message('error', "🔍 Stack trace: " . $e->getTraceAsString());
            }

            // Actualizar progreso en tiempo real
            if ($total % 5 === 0 || $total === 1) {
                $this->csvImportModel->update($importId, [
                    'total_rows' => $total,
                    'imported_rows' => $success,
                    'failed_rows' => $failed
                ]);
            }
        }

        fclose($handle);

        log_message('error', '');
        log_message('error', '📊 RESUMEN FINAL:');
        log_message('error', "   Total filas procesadas: {$total}");
        log_message('error', "   ✅ Exitosas: {$success}");
        log_message('error', "   ❌ Fallidas: {$failed}");
        log_message('error', '========================================');

        return [
            'total' => $total,
            'success' => $success,
            'failed' => $failed,
            'errors' => $errors
        ];
    }

    /**
     * Importar una fila horizontal del CSV
     * Formato: documento, nombre, 23 datos demográficos, intralaboral_1, intralaboral_2, ..., extralaboral_1, ..., estres_1, ...
     */
    protected function importHorizontalRow($data, $serviceId, $headers, $formType)
    {
        // Validar campos básicos
        if (!isset($data['documento']) || trim($data['documento']) === '') {
            throw new \Exception("Campo 'documento' faltante");
        }

        $documento = trim($data['documento']);
        $nombre = $data['nombre'] ?? '';

        log_message('error', "🔍 [importHorizontalRow] Buscando trabajador con doc: {$documento}");

        // Buscar trabajador por documento
        $worker = $this->workerModel
            ->where('battery_service_id', $serviceId)
            ->where('document', $documento)
            ->first();

        if (!$worker) {
            throw new \Exception("Trabajador no encontrado con documento: {$documento}");
        }

        log_message('error', "✅ [importHorizontalRow] Trabajador encontrado ID: {$worker['id']}, Tipo actual: {$worker['intralaboral_type']}");

        // Usar el tipo de formulario seleccionado por el usuario en el upload
        $intralaboralFormType = ($formType === 'A') ? 'intralaboral_A' : 'intralaboral_B';

        // Acumular cambios del worker para hacer un solo update al final
        $workerUpdates = [];

        // Verificar si necesita actualizar el tipo de intralaboral
        if ($worker['intralaboral_type'] !== $formType) {
            log_message('error', "🔄 [importHorizontalRow] Actualizando intralaboral_type de {$worker['intralaboral_type']} a {$formType}");
            $workerUpdates['intralaboral_type'] = $formType;
        }

        $responsesImported = 0;

        // Mapeo de campos demográficos (van a worker_demographics)
        $demographicFieldsMap = [
            'genero' => 'gender',
            'año_nacimiento' => 'birth_year',
            'estado_civil' => 'marital_status',
            'nivel_educacion' => 'education_level',
            'ocupacion' => 'occupation',
            'ciudad_residencia' => 'city_residence',
            'departamento_residencia' => 'department_residence',
            'estrato' => 'stratum',
            'tipo_vivienda' => 'housing_type',
            'dependientes' => 'dependents',
            'ciudad_trabajo' => 'city_work',
            'departamento_trabajo' => 'department_work',
            'tipo_tiempo_empresa' => 'time_in_company_type',
            'años_empresa' => 'time_in_company_years',
            'nombre_cargo' => 'position_name',
            'tipo_cargo' => 'position_type',
            'tipo_tiempo_cargo' => 'time_in_position_type',
            'años_cargo' => 'time_in_position_years',
            'departamento_area' => 'department',
            'tipo_contrato' => 'contract_type',
            'horas_dia' => 'hours_per_day',
            'tipo_salario' => 'salary_type'
            // NOTA: 'atiende_clientes' se procesa por separado (va a workers, no a worker_demographics)
        ];

        // Procesar datos demográficos
        $demographicsData = ['worker_id' => $worker['id']];
        foreach ($demographicFieldsMap as $csvField => $dbField) {
            if (isset($data[$csvField]) && trim($data[$csvField]) !== '') {
                $demographicsData[$dbField] = trim($data[$csvField]);
            }
        }

        // Guardar o actualizar datos demográficos
        if (count($demographicsData) > 1) {
            $existing = $this->workerDemographicsModel->where('worker_id', $worker['id'])->first();
            if ($existing) {
                // Solo actualizar si hay cambios en los datos
                $hasChanges = false;
                foreach ($demographicsData as $key => $value) {
                    if ($key !== 'worker_id' && (!isset($existing[$key]) || $existing[$key] != $value)) {
                        $hasChanges = true;
                        break;
                    }
                }

                if ($hasChanges) {
                    $this->workerDemographicsModel->update($existing['id'], $demographicsData);
                }
            } else {
                $demographicsData['completed_at'] = date('Y-m-d H:i:s');
                $this->workerDemographicsModel->insert($demographicsData);
            }
        }

        // Procesar atiende_clientes (va a workers, no a worker_demographics)
        // Si la columna existe en el CSV, procesarla
        if (isset($data['atiende_clientes'])) {
            $atiendeClientes = mb_strtolower(trim($data['atiende_clientes']), 'UTF-8');
            // Solo es 1 (Sí) si explícitamente dice "Sí" o "Si" (en cualquier combinación de mayúsculas/minúsculas)
            // Cualquier otro valor (vacío, "No", "no", etc.) es 0
            // Usar mb_strtolower para convertir correctamente "SÍ" → "sí"
            $newValue = ($atiendeClientes === 'sí' || $atiendeClientes === 'si') ? 1 : 0;

            // Normalizar valor actual (NULL se trata como 0)
            $currentValue = $worker['atiende_clientes'] === null ? 0 : (int)$worker['atiende_clientes'];

            // Solo acumular si el valor es diferente
            if ($currentValue !== $newValue) {
                $workerUpdates['atiende_clientes'] = $newValue;
            }
        }
        // Si la columna NO existe en el CSV, dejar NULL para validación manual

        // Procesar es_jefe (va a workers, no a worker_demographics)
        // Si la columna existe en el CSV, procesarla
        if (isset($data['es_jefe'])) {
            $esJefe = mb_strtolower(trim($data['es_jefe']), 'UTF-8');
            // Solo es 1 (Sí) si explícitamente dice "Sí" o "Si" (en cualquier combinación de mayúsculas/minúsculas)
            // Cualquier otro valor (vacío, "No", "no", etc.) es 0
            // Usar mb_strtolower para convertir correctamente "SÍ" → "sí"
            $newValue = ($esJefe === 'sí' || $esJefe === 'si') ? 1 : 0;

            // Normalizar valor actual (NULL se trata como 0)
            $currentValue = $worker['es_jefe'] === null ? 0 : (int)$worker['es_jefe'];

            // Solo acumular si el valor es diferente
            if ($currentValue !== $newValue) {
                $workerUpdates['es_jefe'] = $newValue;
            }
        }
        // Si la columna NO existe en el CSV, dejar NULL para validación manual

        // Procesar cada columna del CSV
        foreach ($headers as $index => $header) {
            // Ignorar campos básicos, demográficos y preguntas filtro (ya procesados arriba)
            if (in_array($header, ['documento', 'nombre', 'atiende_clientes', 'es_jefe']) || isset($demographicFieldsMap[$header])) {
                continue;
            }

            // Determinar tipo de cuestionario y número de pregunta
            if (preg_match('/^intralaboral_(\d+)$/', $header, $matches)) {
                $questionNumber = (int)$matches[1];
                $formTypeToUse = $intralaboralFormType;
            } elseif (preg_match('/^extralaboral_(\d+)$/', $header, $matches)) {
                $questionNumber = (int)$matches[1];
                $formTypeToUse = 'extralaboral';
            } elseif (preg_match('/^estres_(\d+)$/', $header, $matches)) {
                $questionNumber = (int)$matches[1];
                $formTypeToUse = 'estres';
            } else {
                continue; // Columna no reconocida, saltar
            }

            // Obtener el valor de la respuesta
            $answerText = $data[$header] ?? '';
            if (trim($answerText) === '') {
                continue; // Si no hay respuesta, saltar
            }

            // Mapear texto a número (pasando número de pregunta para aplicar inversión si corresponde)
            $answerValue = $this->mapTextToNumber($answerText, $formTypeToUse, $questionNumber);

            if ($answerValue === null) {
                $validOptions = ($formTypeToUse === 'estres')
                    ? 'Siempre, Casi siempre, Algunas veces, Nunca'
                    : 'Siempre, Casi siempre, Algunas veces, Casi nunca, Nunca';

                throw new \Exception(
                    "Valor de respuesta INVÁLIDO: '{$answerText}' para pregunta {$header}. " .
                    "Opciones válidas: {$validOptions}"
                );
            }

            // Guardar respuesta
            $existing = $this->responseModel
                ->where('worker_id', $worker['id'])
                ->where('form_type', $formTypeToUse)
                ->where('question_number', $questionNumber)
                ->first();

            $responseData = [
                'worker_id' => $worker['id'],
                'form_type' => $formTypeToUse,
                'question_number' => $questionNumber,
                'answer_value' => $answerValue
            ];

            if ($existing) {
                // Solo actualizar si el valor es diferente
                if ((int)$existing['answer_value'] !== $answerValue) {
                    $this->responseModel->update($existing['id'], $responseData);
                }
            } else {
                $this->responseModel->insert($responseData);
            }

            $responsesImported++;
        }

        // Validar que se importaron las respuestas esperadas
        $expectedIntralaboral = ($formType === 'A') ? 123 : 97;
        $intralaboralCount = $this->responseModel
            ->where('worker_id', $worker['id'])
            ->where('form_type', $intralaboralFormType)
            ->countAllResults();

        log_message('error', "📊 [importHorizontalRow] Respuestas intralaboral esperadas: {$expectedIntralaboral}, encontradas: {$intralaboralCount}");

        if ($intralaboralCount < $expectedIntralaboral) {
            log_message('error', "⚠️ [importHorizontalRow] ADVERTENCIA: Faltan respuestas intralaboral");
        }

        // Aplicar updates acumulados del worker (si hay cambios)
        if (!empty($workerUpdates)) {
            log_message('error', "🔄 [importHorizontalRow] Actualizando worker con cambios: " . json_encode($workerUpdates));
            try {
                $this->workerModel->update($worker['id'], $workerUpdates);
            } catch (\CodeIgniter\Database\Exceptions\DataException $e) {
                // Ignorar error "There is no data to update" - puede ocurrir con valores NULL vs 0
                if (strpos($e->getMessage(), 'There is no data to update') === false) {
                    throw $e; // Re-lanzar si es otro error
                }
                log_message('error', "⚠️ [importHorizontalRow] Update no necesario (valores ya actualizados)");
            }
        }

        // Actualizar estado del trabajador si tiene respuestas
        if ($responsesImported > 0) {
            // Verificar si el trabajador tiene TODO completo para marcarlo como 'completado'

            // 1. Verificar Ficha de Datos Generales
            $demographics = $this->workerDemographicsModel
                ->where('worker_id', $worker['id'])
                ->first();
            $hasDemographics = $demographics && $demographics['completed_at'] !== null;

            // 2. Contar respuestas por tipo de formulario
            $extralaboralCount = $this->responseModel
                ->where('worker_id', $worker['id'])
                ->where('form_type', 'extralaboral')
                ->countAllResults();

            $estresCount = $this->responseModel
                ->where('worker_id', $worker['id'])
                ->where('form_type', 'estres')
                ->countAllResults();

            // Determinar preguntas esperadas según respuestas filtro (atiende_clientes, es_jefe)
            // Refrescar datos del trabajador para obtener atiende_clientes y es_jefe actualizados
            $workerRefresh = $this->workerModel->find($worker['id']);
            $atiendeClientes = (int)($workerRefresh['atiende_clientes'] ?? 0);
            $esJefe = (int)($workerRefresh['es_jefe'] ?? 0);

            if ($formType === 'A') {
                // Forma A: 105 base + (9 si atiende_clientes) + (9 si es_jefe)
                $expectedIntralaboralTotal = 105;
                if ($atiendeClientes === 1) {
                    $expectedIntralaboralTotal += 9; // Preguntas 106-114
                }
                if ($esJefe === 1) {
                    $expectedIntralaboralTotal += 9; // Preguntas 115-123
                }
            } else {
                // Forma B: 88 base + (9 si atiende_clientes)
                $expectedIntralaboralTotal = 88;
                if ($atiendeClientes === 1) {
                    $expectedIntralaboralTotal += 9; // Preguntas 89-97
                }
            }

            $isComplete = (
                $hasDemographics &&
                $intralaboralCount >= $expectedIntralaboralTotal &&
                $extralaboralCount >= 31 &&
                $estresCount >= 31
            );

            log_message('error', "📊 [importHorizontalRow] Verificando completitud:");
            log_message('error', "   - Ficha Datos: " . ($hasDemographics ? 'Sí' : 'No'));
            log_message('error', "   - atiende_clientes: {$atiendeClientes}, es_jefe: {$esJefe}");
            log_message('error', "   - Intralaboral: {$intralaboralCount}/{$expectedIntralaboralTotal}");
            log_message('error', "   - Extralaboral: {$extralaboralCount}/31");
            log_message('error', "   - Estrés: {$estresCount}/31");
            log_message('error', "   - ¿Completo?: " . ($isComplete ? 'Sí' : 'No'));

            // Actualizar status según completitud
            if ($isComplete) {
                log_message('error', "✅ [importHorizontalRow] Trabajador COMPLETO - Actualizando a 'completado'");
                $this->workerModel->update($worker['id'], [
                    'status' => 'completado',
                    'completed_at' => date('Y-m-d H:i:s')
                ]);
            } else {
                log_message('error', "🔄 [importHorizontalRow] Trabajador PARCIAL - Actualizando a 'en_progreso'");
                $updateData = ['status' => 'en_progreso'];

                // Establecer started_at si aún no existe
                if ($worker['started_at'] === null) {
                    $updateData['started_at'] = date('Y-m-d H:i:s');
                }

                $this->workerModel->update($worker['id'], $updateData);
            }
        }

        log_message('error', "✅ [importHorizontalRow] Total respuestas importadas: {$responsesImported}");

        return true;
    }

    /**
     * Importar una fila del CSV
     * Formato esperado: documento, cuestionario, pregunta, respuesta
     */
    protected function importRow($data, $serviceId)
    {
        // Validar campos requeridos
        $requiredFields = ['documento', 'cuestionario', 'pregunta', 'respuesta'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                throw new \Exception("Campo requerido faltante: {$field}");
            }
        }

        $documento = trim($data['documento']);
        $cuestionario = strtolower(trim($data['cuestionario']));
        $pregunta = trim($data['pregunta']);
        $respuesta = trim($data['respuesta']);

        // Buscar trabajador por documento
        $worker = $this->workerModel
            ->where('battery_service_id', $serviceId)
            ->where('document_number', $documento)
            ->first();

        if (!$worker) {
            throw new \Exception("Trabajador no encontrado con documento: {$documento}");
        }

        // Mapear tipo de cuestionario (debe coincidir con enum en BD)
        $formTypeMap = [
            'intralaboral' => 'intralaboral_A',
            'intralaboral_a' => 'intralaboral_A',
            'intralaboral_b' => 'intralaboral_B',
            'extralaboral' => 'extralaboral',
            'estres' => 'estres',
            'ficha_datos' => 'ficha_datos_generales',
            'ficha_datos_generales' => 'ficha_datos_generales'
        ];

        $formType = $formTypeMap[$cuestionario] ?? null;
        if (!$formType) {
            throw new \Exception("Tipo de cuestionario desconocido: {$cuestionario}");
        }

        // Crear o actualizar respuesta
        $existingResponse = $this->responseModel
            ->where('worker_id', $worker['id'])
            ->where('form_type', $formType)
            ->where('question_number', $pregunta)
            ->first();

        $responseData = [
            'worker_id' => $worker['id'],
            'form_type' => $formType,
            'question_number' => $pregunta,
            'answer_value' => $respuesta
        ];

        if ($existingResponse) {
            $this->responseModel->update($existingResponse['id'], $responseData);
        } else {
            $this->responseModel->insert($responseData);
        }

        // Actualizar estado del trabajador si tiene respuestas
        if ($worker['status'] === 'pendiente' || $worker['status'] === 'invitado') {
            $this->workerModel->update($worker['id'], ['status' => 'en_proceso']);
        }

        return true;
    }

    /**
     * Descargar plantilla CSV para Forma A (Horizontal)
     */
    public function downloadTemplateFormaA()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $roleName = session()->get('role_name');
        if ($roleName !== 'consultor') {
            return redirect()->to('/dashboard')->with('error', 'No tienes permisos');
        }

        // Generar encabezados para Forma A
        // Estructura: documento, nombre, ficha_datos(22), intralaboral_1-105, atiende_clientes,
        //             intralaboral_106-114, es_jefe, intralaboral_115-123, extralaboral(31), estres(31)
        $headers = ['documento', 'nombre'];

        // Ficha de Datos Generales (22 campos demográficos)
        $fichaFields = [
            'genero', 'año_nacimiento', 'estado_civil', 'nivel_educacion', 'ocupacion',
            'ciudad_residencia', 'departamento_residencia', 'estrato', 'tipo_vivienda', 'dependientes',
            'ciudad_trabajo', 'departamento_trabajo', 'tipo_tiempo_empresa', 'años_empresa',
            'nombre_cargo', 'tipo_cargo', 'tipo_tiempo_cargo', 'años_cargo',
            'departamento_area', 'tipo_contrato', 'horas_dia', 'tipo_salario'
        ];
        foreach ($fichaFields as $field) {
            $headers[] = $field;
        }

        // Intralaboral A: preguntas 1-105, luego atiende_clientes, luego 106-114, luego es_jefe, luego 115-123
        for ($i = 1; $i <= 105; $i++) {
            $headers[] = "intralaboral_$i";
        }
        $headers[] = 'atiende_clientes';  // Entre pregunta 105 y 106
        for ($i = 106; $i <= 114; $i++) {
            $headers[] = "intralaboral_$i";
        }
        $headers[] = 'es_jefe';  // Entre pregunta 114 y 115
        for ($i = 115; $i <= 123; $i++) {
            $headers[] = "intralaboral_$i";
        }

        // Extralaboral (31 preguntas)
        for ($i = 1; $i <= 31; $i++) {
            $headers[] = "extralaboral_$i";
        }

        // Estrés (31 preguntas)
        for ($i = 1; $i <= 31; $i++) {
            $headers[] = "estres_$i";
        }

        // Crear CSV con encabezado y fila de ejemplo
        $csv = implode(';', $headers) . "\n";

        // Fila de ejemplo
        $exampleRow = ['1234567890', 'Juan Pérez'];

        // Ejemplo de datos demográficos
        $exampleRow = array_merge($exampleRow, [
            'Masculino', '1985', 'Casado(a)', 'Profesional', 'Ingeniero',
            'Bogotá', 'Cundinamarca', '3', 'Propia', '2',
            'Bogotá', 'Cundinamarca', 'Mas_de_un_ano', '5',
            'Jefe de Proyecto', 'Jefatura', 'Mas_de_un_ano', '3',
            'Operaciones', 'Indefinido', '8', 'Fijo'
        ]);

        // Intralaboral A: 1-105
        for ($i = 0; $i < 105; $i++) {
            $exampleRow[] = 'Siempre';
        }
        $exampleRow[] = 'Sí';  // atiende_clientes
        // Intralaboral A: 106-114
        for ($i = 0; $i < 9; $i++) {
            $exampleRow[] = 'Siempre';
        }
        $exampleRow[] = 'Sí';  // es_jefe
        // Intralaboral A: 115-123
        for ($i = 0; $i < 9; $i++) {
            $exampleRow[] = 'Siempre';
        }

        // Extralaboral
        for ($i = 0; $i < 31; $i++) {
            $exampleRow[] = 'Casi siempre';
        }
        // Estrés
        for ($i = 0; $i < 31; $i++) {
            $exampleRow[] = 'A veces';
        }

        $csv .= implode(';', $exampleRow) . "\n";

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="plantilla_importacion_forma_a.csv"')
            ->setBody("\xEF\xBB\xBF" . $csv); // BOM UTF-8
    }

    /**
     * Descargar plantilla CSV para Forma B (Horizontal)
     */
    public function downloadTemplateFormaB()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $roleName = session()->get('role_name');
        if ($roleName !== 'consultor') {
            return redirect()->to('/dashboard')->with('error', 'No tienes permisos');
        }

        // Generar encabezados para Forma B
        // Estructura: documento, nombre, ficha_datos(22), intralaboral_1-88, atiende_clientes,
        //             intralaboral_89-97, extralaboral(31), estres(31)
        // NOTA: Forma B NO tiene pregunta "es_jefe" (solo Forma A la tiene)
        $headers = ['documento', 'nombre'];

        // Ficha de Datos Generales (22 campos demográficos)
        $fichaFields = [
            'genero', 'año_nacimiento', 'estado_civil', 'nivel_educacion', 'ocupacion',
            'ciudad_residencia', 'departamento_residencia', 'estrato', 'tipo_vivienda', 'dependientes',
            'ciudad_trabajo', 'departamento_trabajo', 'tipo_tiempo_empresa', 'años_empresa',
            'nombre_cargo', 'tipo_cargo', 'tipo_tiempo_cargo', 'años_cargo',
            'departamento_area', 'tipo_contrato', 'horas_dia', 'tipo_salario'
        ];
        foreach ($fichaFields as $field) {
            $headers[] = $field;
        }

        // Intralaboral B: preguntas 1-88, luego atiende_clientes, luego 89-97
        for ($i = 1; $i <= 88; $i++) {
            $headers[] = "intralaboral_$i";
        }
        $headers[] = 'atiende_clientes';  // Entre pregunta 88 y 89
        for ($i = 89; $i <= 97; $i++) {
            $headers[] = "intralaboral_$i";
        }

        // Extralaboral (31 preguntas)
        for ($i = 1; $i <= 31; $i++) {
            $headers[] = "extralaboral_$i";
        }

        // Estrés (31 preguntas)
        for ($i = 1; $i <= 31; $i++) {
            $headers[] = "estres_$i";
        }

        // Crear CSV con encabezado y fila de ejemplo
        $csv = implode(';', $headers) . "\n";

        // Fila de ejemplo
        $exampleRow = ['9876543210', 'María García'];

        // Ejemplo de datos demográficos
        $exampleRow = array_merge($exampleRow, [
            'Femenino', '1990', 'Soltero(a)', 'Tecnico_Tecnologo', 'Auxiliar Administrativa',
            'Medellín', 'Antioquia', '2', 'Arriendo', '1',
            'Medellín', 'Antioquia', 'Menos_de_un_ano', '',
            'Auxiliar Contable', 'Auxiliar', 'Menos_de_un_ano', '',
            'Contabilidad', 'Temporal', '8', 'Fijo'
        ]);

        // Intralaboral B: 1-88
        for ($i = 0; $i < 88; $i++) {
            $exampleRow[] = 'Siempre';
        }
        $exampleRow[] = 'No';  // atiende_clientes (entre pregunta 88 y 89)
        // Intralaboral B: 89-97
        for ($i = 0; $i < 9; $i++) {
            $exampleRow[] = 'Siempre';
        }

        // Extralaboral
        for ($i = 0; $i < 31; $i++) {
            $exampleRow[] = 'Casi siempre';
        }
        // Estrés
        for ($i = 0; $i < 31; $i++) {
            $exampleRow[] = 'A veces';
        }

        $csv .= implode(';', $exampleRow) . "\n";

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="plantilla_importacion_forma_b.csv"')
            ->setBody("\xEF\xBB\xBF" . $csv); // BOM UTF-8
    }

    /**
     * Descargar plantilla CSV de ejemplo (LEGACY - mantener por compatibilidad)
     */
    public function downloadTemplate()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $roleName = session()->get('role_name');
        if ($roleName !== 'consultor') {
            return redirect()->to('/dashboard')->with('error', 'No tienes permisos');
        }

        $csv = "documento,cuestionario,pregunta,respuesta\n";
        $csv .= "1234567890,intralaboral_a,1,4\n";
        $csv .= "1234567890,intralaboral_a,2,3\n";
        $csv .= "1234567890,extralaboral,1,2\n";
        $csv .= "9876543210,intralaboral_b,1,1\n";

        return $this->response
            ->setHeader('Content-Type', 'text/csv')
            ->setHeader('Content-Disposition', 'attachment; filename="plantilla_importacion_psyrisk.csv"')
            ->setBody($csv);
    }
}
