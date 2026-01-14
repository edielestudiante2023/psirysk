<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\WorkerModel;
use App\Models\BatteryServiceModel;
use App\Models\CalculatedResultModel;
use App\Models\WorkerDemographicsModel;
use App\Models\ResponseModel;
use CodeIgniter\HTTP\ResponseInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class WorkerController extends BaseController
{
    protected $workerModel;
    protected $batteryServiceModel;

    public function __construct()
    {
        $this->workerModel = new WorkerModel();
        $this->batteryServiceModel = new BatteryServiceModel();
        helper(['form', 'url', 'filesystem']);
    }

    /**
     * Verificar permisos de acceso para operaciones de trabajadores
     * Consultores y admin pueden gestionar todos los trabajadores
     */
    private function checkWorkerPermissions($service)
    {
        $roleName = session()->get('role_name');

        // Directores comerciales NO pueden gestionar trabajadores
        if ($roleName === 'director_comercial') {
            return redirect()->to('/commercial')->with('error', 'No tienes permisos para gestionar trabajadores. Esta función es exclusiva del consultor asignado.');
        }

        // Consultores pueden ver todos los servicios (sin restricción por consultant_id)
        if (!in_array($roleName, ['superadmin', 'consultor', 'admin'])) {
            return redirect()->to('/dashboard')->with('error', 'No tienes permisos para este servicio');
        }

        return null; // Sin errores
    }

    /**
     * Lista global de todos los trabajadores (con filtro por empresa)
     */
    public function listAll()
    {
        // Verificar autenticación
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $roleName = session()->get('role_name');
        $userId = session()->get('id');

        // Directores comerciales NO pueden ver trabajadores
        if ($roleName === 'director_comercial') {
            return redirect()->to('/commercial')->with('error', 'No tienes permisos para ver trabajadores.');
        }

        $db = \Config\Database::connect();
        $companyModel = new \App\Models\CompanyModel();

        // Obtener todas las empresas (consultores ven todo)
        $companies = $companyModel->findAll();

        // Obtener todos los trabajadores con información del servicio y empresa
        $workers = $db->table('workers')
            ->select('workers.*, battery_services.service_name, battery_services.id as service_id, companies.name as company_name, companies.id as company_id')
            ->join('battery_services', 'battery_services.id = workers.battery_service_id')
            ->join('companies', 'companies.id = battery_services.company_id')
            ->orderBy('workers.created_at', 'DESC')
            ->get()
            ->getResultArray();

        $data = [
            'title' => 'Gestión de Trabajadores',
            'workers' => $workers,
            'companies' => $companies,
        ];

        return view('workers/list_all', $data);
    }

    public function upload($serviceId)
    {
        // Verificar autenticación
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // Verificar que el servicio existe
        $service = $this->batteryServiceModel
            ->select('battery_services.*, companies.name as company_name')
            ->join('companies', 'companies.id = battery_services.company_id')
            ->find($serviceId);

        if (!$service) {
            return redirect()->to('/battery-services')->with('error', 'Servicio no encontrado');
        }

        // Verificar permisos
        $permissionCheck = $this->checkWorkerPermissions($service);
        if ($permissionCheck) {
            return $permissionCheck;
        }

        $data = [
            'title' => 'Cargar Trabajadores desde CSV',
            'service' => $service,
        ];

        return view('workers/upload', $data);
    }

    public function processCSV($serviceId)
    {
        // Verificar autenticación
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $service = $this->batteryServiceModel->find($serviceId);
        if (!$service) {
            return redirect()->to('/battery-services')->with('error', 'Servicio no encontrado');
        }

        try {
            // Validar que se subió un archivo
            $file = $this->request->getFile('csv_file');
        if (!$file || !$file->isValid()) {
            return redirect()->back()->with('error', 'Debe seleccionar un archivo CSV válido');
        }

        // Validar extensión
        if ($file->getExtension() !== 'csv') {
            return redirect()->back()->with('error', 'El archivo debe ser un CSV');
        }

        // Leer el archivo CSV con delimitador punto y coma (;) para configuración regional Colombia
        $csvData = array_map(function($line) {
            return str_getcsv($line, ';');
        }, file($file->getTempName()));
        $header = array_shift($csvData); // Primera fila = headers

        // Limpiar BOM UTF-8 y espacios de los encabezados
        $header = array_map(function($h) {
            // Quitar BOM UTF-8 (EF BB BF), espacios en blanco, y convertir a minúsculas
            return trim(str_replace("\xEF\xBB\xBF", '', $h));
        }, $header);

        // Validar headers requeridos
        $requiredHeaders = [
            'document_type', 'document', 'hire_date', 'name', 'position',
            'area', 'email', 'phone', 'intralaboral_type', 'application_mode'
        ];

        $missingHeaders = array_diff($requiredHeaders, $header);
        if (!empty($missingHeaders)) {
            return redirect()->back()->with('error', 'Faltan columnas en el CSV: ' . implode(', ', $missingHeaders));
        }

        // Procesar cada fila
        $imported = 0;
        $errors = [];

        foreach ($csvData as $index => $row) {
            $rowNumber = $index + 2; // +2 porque array empieza en 0 y hay header

            // Validar que la fila tenga el mismo número de columnas que el header
            if (count($row) !== count($header)) {
                $errors[] = "Fila {$rowNumber}: Número incorrecto de columnas (esperado: " . count($header) . ", encontrado: " . count($row) . ")";
                continue;
            }

            // Crear array asociativo
            $data = array_combine($header, $row);

            // Validar datos obligatorios
            if (empty($data['document']) || empty($data['name']) || empty($data['email'])) {
                $errors[] = "Fila {$rowNumber}: Faltan datos obligatorios (documento, nombre o email)";
                continue;
            }

            // Validar tipo intralaboral
            if (!in_array(strtoupper($data['intralaboral_type']), ['A', 'B'])) {
                $errors[] = "Fila {$rowNumber}: Tipo intralaboral debe ser A o B";
                continue;
            }

            // Validar modalidad
            if (!in_array(strtolower($data['application_mode']), ['virtual', 'presencial'])) {
                $errors[] = "Fila {$rowNumber}: Modalidad debe ser 'virtual' o 'presencial'";
                continue;
            }

            // Generar token único
            $token = bin2hex(random_bytes(32));

            // Limpiar documento: extraer solo números
            $cleanDocument = preg_replace('/[^0-9]/', '', $data['document']);

            // Preparar datos para insertar
            $workerData = [
                'battery_service_id' => $serviceId,
                'document_type'      => $data['document_type'] ?? 'CC',
                'document'           => $cleanDocument,
                'hire_date'          => $this->parseDate($data['hire_date']),
                'name'               => $data['name'],
                'position'           => $data['position'],
                'area'               => $data['area'] ?? null,
                'email'              => $data['email'],
                'phone'              => $data['phone'] ?? null,
                'intralaboral_type'  => strtoupper($data['intralaboral_type']),
                'application_mode'   => strtolower($data['application_mode']),
                'token'              => $token,
                'email_sent'         => false,
                'status'             => 'pendiente',
            ];

            // Insertar trabajador
            try {
                $this->workerModel->insert($workerData);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Fila {$rowNumber}: Error al insertar - " . $e->getMessage();
            }
        }

        // Mensaje de resultado
        $message = "Se importaron {$imported} trabajadores correctamente.";
        if (!empty($errors)) {
            $message .= " Errores: " . implode('; ', $errors);
            return redirect()->to('/battery-services/' . $serviceId)->with('warning', $message);
        }

        return redirect()->to('/battery-services/' . $serviceId)->with('success', $message);

        } catch (\Exception $e) {
            log_message('error', 'Error procesando CSV: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al procesar el archivo CSV. Verifique que el formato sea correcto y que todas las filas tengan el mismo número de columnas que el encabezado.');
        }
    }

    private function parseDate($dateString)
    {
        // Intentar varios formatos de fecha
        $formats = ['d/m/Y', 'm/d/Y', 'Y-m-d'];

        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $dateString);
            if ($date) {
                return $date->format('Y-m-d');
            }
        }

        return null;
    }

    public function index($serviceId)
    {
        // Verificar autenticación
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $service = $this->batteryServiceModel
            ->select('battery_services.*, companies.name as company_name')
            ->join('companies', 'companies.id = battery_services.company_id')
            ->find($serviceId);

        if (!$service) {
            return redirect()->to('/battery-services')->with('error', 'Servicio no encontrado');
        }

        $workers = $this->workerModel->where('battery_service_id', $serviceId)->findAll();

        $data = [
            'title' => 'Trabajadores del Servicio',
            'service' => $service,
            'workers' => $workers,
        ];

        return view('workers/index', $data);
    }

    /**
     * Vista de trabajadores para clientes (cliente_empresa y cliente_gestor)
     * Solo permite ver resultados individuales, sin gestión
     */
    public function indexClient($serviceId)
    {
        // Verificar autenticación
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $roleName = session()->get('role_name');
        $userCompanyId = session()->get('company_id');

        // Solo para roles de cliente
        if (!in_array($roleName, ['cliente_empresa', 'cliente_gestor'])) {
            return redirect()->to('/dashboard')->with('error', 'Acceso no autorizado');
        }

        // Verificar que el usuario tenga empresa asignada
        if (!$userCompanyId) {
            return redirect()->to('/logout')->with('error', 'Tu usuario no tiene una empresa asignada.');
        }

        $service = $this->batteryServiceModel
            ->select('battery_services.*, companies.name as company_name, companies.parent_company_id')
            ->join('companies', 'companies.id = battery_services.company_id')
            ->find($serviceId);

        if (!$service) {
            return redirect()->to('/dashboard')->with('error', 'Servicio no encontrado');
        }

        // Verificar acceso según rol
        $hasAccess = false;

        if ($roleName === 'cliente_empresa') {
            // Solo puede ver servicios de su propia empresa
            $hasAccess = ($service['company_id'] == $userCompanyId);
        } elseif ($roleName === 'cliente_gestor') {
            // Puede ver servicios de su empresa o de empresas hijas
            if ($service['company_id'] == $userCompanyId) {
                $hasAccess = true;
            } else {
                // Verificar si la empresa del servicio es hija de la empresa gestora
                $hasAccess = ($service['parent_company_id'] == $userCompanyId);
            }
        }

        if (!$hasAccess) {
            return redirect()->to('/dashboard')->with('error', 'No tienes acceso a este servicio');
        }

        $workers = $this->workerModel->where('battery_service_id', $serviceId)->findAll();

        $data = [
            'title' => 'Trabajadores del Servicio',
            'service' => $service,
            'workers' => $workers,
        ];

        return view('workers/index_client', $data);
    }

    /**
     * Send assessment email to a single worker
     */
    public function sendEmail($workerId)
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['success' => false, 'message' => 'No autenticado']);
        }

        $worker = $this->workerModel->find($workerId);
        if (!$worker) {
            return $this->response->setJSON(['success' => false, 'message' => 'Trabajador no encontrado']);
        }

        // Verificar que sea modo virtual y tenga email
        if ($worker['application_mode'] !== 'virtual' || empty($worker['email'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Trabajador no tiene email configurado']);
        }

        // Get service information
        $service = $this->batteryServiceModel
            ->select('battery_services.*, companies.name as company_name')
            ->join('companies', 'companies.id = battery_services.company_id')
            ->find($worker['battery_service_id']);

        // Generate assessment link
        $assessmentLink = base_url('assessment/' . $worker['token']);

        // Send email using EmailService
        $emailService = new \App\Libraries\EmailService();
        $emailSent = $emailService->sendAssessmentLink(
            $worker['email'],
            $worker['name'],
            $assessmentLink,
            $service['company_name'],
            date('d/m/Y', strtotime($service['link_expiration_date']))
        );

        if ($emailSent) {
            // Update email_sent status (sin restricciones, permite reenvíos)
            $this->workerModel->update($workerId, ['email_sent' => true]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Email enviado exitosamente a ' . $worker['email'] . ' (' . $worker['name'] . ')'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al enviar el email. Verifique la configuración de SendGrid.'
            ]);
        }
    }

    /**
     * Update worker data
     */
    public function update($workerId)
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['success' => false, 'message' => 'No autenticado']);
        }

        $worker = $this->workerModel->find($workerId);
        if (!$worker) {
            return $this->response->setJSON(['success' => false, 'message' => 'Trabajador no encontrado']);
        }

        // Verificar permisos (consultores pueden editar todos los trabajadores)
        $roleName = session()->get('role_name');
        if (!in_array($roleName, ['superadmin', 'consultor', 'admin'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'No tienes permisos']);
        }

        // Validar y actualizar datos
        $data = [
            'document' => $this->request->getPost('document'),
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'position' => $this->request->getPost('position'),
            'area' => $this->request->getPost('area'),
            'intralaboral_type' => $this->request->getPost('intralaboral_type'),
            'application_mode' => $this->request->getPost('application_mode')
        ];

        // Agregar status si se envía
        $status = $this->request->getPost('status');
        if ($status && in_array($status, ['pendiente', 'en_progreso', 'completado', 'no_participo'])) {
            $data['status'] = $status;
        }

        // Validaciones básicas
        if (empty($data['document']) || empty($data['name']) || empty($data['email'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Datos incompletos']);
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Email inválido']);
        }

        try {
            $this->workerModel->update($workerId, $data);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Trabajador actualizado correctamente'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al actualizar: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Marcar TODOS los trabajadores pendientes/en_progreso como "No Participó"
     * Acción masiva para facilitar el cierre de servicios
     */
    public function markAllAsNoParticipo($serviceId)
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['success' => false, 'message' => 'No autenticado']);
        }

        // Verificar permisos (solo consultores y admin)
        $roleName = session()->get('role_name');
        if (!in_array($roleName, ['superadmin', 'consultor', 'admin'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'No tienes permisos']);
        }

        // Verificar que el servicio existe
        $service = $this->batteryServiceModel->find($serviceId);
        if (!$service) {
            return $this->response->setJSON(['success' => false, 'message' => 'Servicio no encontrado']);
        }

        // Obtener todos los trabajadores pendientes o en_progreso del servicio
        $workersToUpdate = $this->workerModel
            ->where('battery_service_id', $serviceId)
            ->whereIn('status', ['pendiente', 'en_progreso'])
            ->findAll();

        if (empty($workersToUpdate)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No hay trabajadores pendientes o en progreso para marcar'
            ]);
        }

        $updatedCount = 0;
        $errors = [];

        foreach ($workersToUpdate as $worker) {
            try {
                $this->workerModel->update($worker['id'], [
                    'status' => 'no_participo',
                    'non_participation_reason' => 'no_participo',
                    'non_participation_notes' => 'Marcado masivamente por consultor el ' . date('d/m/Y H:i')
                ]);
                $updatedCount++;
            } catch (\Exception $e) {
                $errors[] = $worker['name'];
            }
        }

        if ($updatedCount > 0) {
            return $this->response->setJSON([
                'success' => true,
                'message' => $updatedCount . ' trabajador(es) marcado(s) como "No Participó" correctamente.',
                'updated' => $updatedCount,
                'errors' => $errors
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al actualizar los trabajadores',
                'errors' => $errors
            ]);
        }
    }

    /**
     * Marcar trabajador como "No Participó"
     * Este estado excluye al trabajador de todas las estadísticas e informes
     */
    public function markAsNoParticipo($workerId)
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['success' => false, 'message' => 'No autenticado']);
        }

        $worker = $this->workerModel->find($workerId);
        if (!$worker) {
            return $this->response->setJSON(['success' => false, 'message' => 'Trabajador no encontrado']);
        }

        // Verificar permisos (solo consultores y admin)
        $roleName = session()->get('role_name');
        if (!in_array($roleName, ['superadmin', 'consultor', 'admin'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'No tienes permisos']);
        }

        // No se puede marcar como no_participo si ya completó
        if ($worker['status'] === 'completado') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No se puede marcar como "No Participó" a un trabajador que ya completó la batería'
            ]);
        }

        // Ya está marcado como no_participo
        if ($worker['status'] === 'no_participo') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Este trabajador ya está marcado como "No Participó"'
            ]);
        }

        try {
            $this->workerModel->update($workerId, [
                'status' => 'no_participo',
                'non_participation_reason' => 'no_participo',
                'non_participation_notes' => 'Marcado manualmente por consultor el ' . date('d/m/Y H:i')
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Trabajador marcado como "No Participó" correctamente. No será incluido en estadísticas ni informes.'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al actualizar: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Delete worker
     */
    public function delete($workerId)
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['success' => false, 'message' => 'No autenticado']);
        }

        $worker = $this->workerModel->find($workerId);
        if (!$worker) {
            return $this->response->setJSON(['success' => false, 'message' => 'Trabajador no encontrado']);
        }

        // Verificar permisos (consultores pueden eliminar cualquier trabajador)
        $roleName = session()->get('role_name');
        if (!in_array($roleName, ['superadmin', 'consultor', 'admin'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'No tienes permisos']);
        }

        try {
            // Eliminar registros relacionados en cascada
            $db = \Config\Database::connect();

            // Eliminar todas las respuestas de evaluaciones (tabla unificada)
            $db->table('responses')->where('worker_id', $workerId)->delete();

            // Eliminar datos demográficos
            $db->table('worker_demographics')->where('worker_id', $workerId)->delete();

            // Eliminar el trabajador
            $this->workerModel->delete($workerId);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Trabajador eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Send assessment emails to all workers in a service (bulk send)
     */
    public function sendBulkEmails($serviceId)
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['success' => false, 'message' => 'No autenticado']);
        }

        // Get service
        $service = $this->batteryServiceModel
            ->select('battery_services.*, companies.name as company_name')
            ->join('companies', 'companies.id = battery_services.company_id')
            ->find($serviceId);

        if (!$service) {
            return $this->response->setJSON(['success' => false, 'message' => 'Servicio no encontrado']);
        }

        // Get workers with virtual mode, email, and NOT completed/no_participo
        // Solo enviar a trabajadores pendientes o en_progreso
        $workers = $this->workerModel
            ->where('battery_service_id', $serviceId)
            ->where('application_mode', 'virtual')
            ->where('email IS NOT NULL')
            ->where('email !=', '')
            ->whereNotIn('status', ['completado', 'no_participo'])
            ->findAll();

        if (empty($workers)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No hay trabajadores con modo virtual y email en este servicio'
            ]);
        }

        $emailService = new \App\Libraries\EmailService();
        $sent = 0;
        $failed = 0;
        $errors = [];

        foreach ($workers as $worker) {
            $assessmentLink = base_url('assessment/' . $worker['token']);

            $emailSent = $emailService->sendAssessmentLink(
                $worker['email'],
                $worker['name'],
                $assessmentLink,
                $service['company_name'],
                date('d/m/Y', strtotime($service['link_expiration_date']))
            );

            if ($emailSent) {
                $this->workerModel->update($worker['id'], ['email_sent' => true]);
                $sent++;
            } else {
                $failed++;
                $errors[] = $worker['name'] . ' (' . $worker['email'] . ')';
            }
        }

        $message = "Envío masivo completado. Exitosos: {$sent}, Fallidos: {$failed}";
        if ($failed > 0) {
            $message .= ". Revise los emails fallidos.";
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => $message,
            'sent' => $sent,
            'failed' => $failed,
            'total' => count($workers),
            'errors' => $errors
        ]);
    }

    /**
     * Ver resultados individuales de un trabajador
     */
    public function results($workerId)
    {
        log_message('error', '🔍 ========== INICIO results() - Worker ID: ' . $workerId . ' ==========');

        // Verificar autenticación
        log_message('error', '🔍 Verificando autenticación...');
        log_message('error', '🔍 isLoggedIn: ' . (session()->get('isLoggedIn') ? 'SI' : 'NO'));
        log_message('error', '🔍 User ID: ' . session()->get('id'));
        log_message('error', '🔍 Role: ' . session()->get('role_name'));

        if (!session()->get('isLoggedIn')) {
            log_message('error', '❌ NO AUTENTICADO - Redirigiendo a /login');
            return redirect()->to('/login');
        }

        log_message('error', '✅ Usuario autenticado');

        // Obtener trabajador
        log_message('error', '🔍 Buscando trabajador ID: ' . $workerId);
        $worker = $this->workerModel->find($workerId);

        if (!$worker) {
            log_message('error', '❌ TRABAJADOR NO ENCONTRADO - ID: ' . $workerId);
            return redirect()->back()->with('error', 'Trabajador no encontrado');
        }

        log_message('error', '✅ Trabajador encontrado: ' . json_encode($worker));
        log_message('error', '🔍 Estado del trabajador: ' . $worker['status']);

        // Verificar que el trabajador haya completado la batería
        if ($worker['status'] !== 'completado') {
            log_message('error', '❌ TRABAJADOR NO COMPLETADO - Estado actual: ' . $worker['status']);
            return redirect()->back()->with('error', 'El trabajador no ha completado la batería');
        }

        log_message('error', '✅ Trabajador tiene estado completado');

        // Verificar permisos
        log_message('error', '🔍 Verificando permisos - Obteniendo servicio ID: ' . $worker['battery_service_id']);
        $service = $this->batteryServiceModel
            ->select('battery_services.*, companies.name as company_name, companies.parent_company_id')
            ->join('companies', 'companies.id = battery_services.company_id')
            ->find($worker['battery_service_id']);

        if (!$service) {
            log_message('error', '❌ SERVICIO NO ENCONTRADO - ID: ' . $worker['battery_service_id']);
            return redirect()->back()->with('error', 'Servicio no encontrado');
        }

        log_message('error', '✅ Servicio encontrado: ' . json_encode($service));

        // Verificar permisos según rol
        $roleName = session()->get('role_name');

        // Consultores y admins pueden ver todos los resultados
        if (in_array($roleName, ['superadmin', 'consultor', 'admin'])) {
            log_message('error', '✅ Permisos verificados - Rol admin/consultor');
        }
        // Clientes pueden ver resultados de su empresa (o empresas hijas para gestor)
        elseif (in_array($roleName, ['cliente_empresa', 'cliente_gestor'])) {
            $userCompanyId = session()->get('company_id');
            $hasAccess = false;

            if ($roleName === 'cliente_empresa') {
                $hasAccess = ($service['company_id'] == $userCompanyId);
            } elseif ($roleName === 'cliente_gestor') {
                $hasAccess = ($service['company_id'] == $userCompanyId) ||
                             ($service['parent_company_id'] == $userCompanyId);
            }

            if (!$hasAccess) {
                log_message('error', '❌ SIN PERMISOS - Cliente sin acceso a esta empresa');
                return redirect()->to('/dashboard')->with('error', 'No tienes permisos para ver estos resultados');
            }

            // Verificar que el servicio esté cerrado/finalizado para clientes
            if (!in_array($service['status'], ['cerrado', 'finalizado'])) {
                log_message('error', '❌ SERVICIO NO CERRADO - Cliente no puede ver resultados');
                return redirect()->to('/dashboard')->with('error', 'Los resultados estarán disponibles cuando el servicio esté finalizado');
            }

            log_message('error', '✅ Permisos verificados - Cliente con acceso');
        }
        else {
            log_message('error', '❌ SIN PERMISOS - Rol no autorizado: ' . $roleName);
            return redirect()->back()->with('error', 'No tienes permisos para ver estos resultados');
        }

        log_message('error', '✅ Permisos verificados correctamente');

        // Obtener resultados calculados
        log_message('error', '🔍 Buscando resultados calculados para worker_id: ' . $workerId);
        $resultModel = new CalculatedResultModel();
        $results = $resultModel->where('worker_id', $workerId)->first();

        // Si no existen resultados, calcularlos automáticamente
        if (!$results) {
            log_message('error', '⚠️ No existen resultados - Calculando automáticamente...');

            try {
                $calculationService = new \App\Services\CalculationService();
                $calculationService->calculateAndSaveResults($workerId);
                log_message('error', '✅ Cálculo ejecutado - Obteniendo resultados...');

                $results = $resultModel->where('worker_id', $workerId)->first();

                if (!$results) {
                    log_message('error', '❌ ERROR - No se pudieron calcular los resultados');
                    return redirect()->back()->with('error', 'Error al calcular los resultados');
                }

                log_message('error', '✅ Resultados calculados y obtenidos correctamente');
            } catch (\Exception $e) {
                log_message('error', '❌ EXCEPCIÓN al calcular resultados: ' . $e->getMessage());
                log_message('error', '❌ Stack trace: ' . $e->getTraceAsString());
                return redirect()->back()->with('error', 'Error al calcular los resultados: ' . $e->getMessage());
            }
        } else {
            log_message('error', '✅ Resultados existentes encontrados');
        }

        log_message('error', '🔍 Resultados: ' . json_encode($results));

        // Obtener datos demográficos
        log_message('error', '🔍 Obteniendo datos demográficos...');
        $demographicsModel = new WorkerDemographicsModel();
        $demographics = $demographicsModel->getByWorkerId($workerId);
        log_message('error', '🔍 Datos demográficos: ' . json_encode($demographics));

        $data = [
            'title' => 'Resultados Individuales - ' . $worker['name'],
            'worker' => $worker,
            'service' => $service,
            'results' => $results,
            'demographics' => $demographics
        ];

        log_message('error', '🔍 Data preparada para vista: ' . json_encode(array_keys($data)));

        // ENRUTAMIENTO SEGÚN TIPO DE FORMA - UNIVERSOS SEPARADOS
        $intralaboralType = strtoupper($worker['intralaboral_type']);
        $viewPath = '';

        if ($intralaboralType === 'A') {
            $viewPath = 'workers/results_forma_a';
            log_message('error', '🔍 Cargando vista FORMA A: ' . $viewPath . ' (Jefes/Profesionales/Técnicos)');
        } elseif ($intralaboralType === 'B') {
            $viewPath = 'workers/results_forma_b';
            log_message('error', '🔍 Cargando vista FORMA B: ' . $viewPath . ' (Auxiliares/Operarios)');
        } else {
            log_message('error', '❌ ERROR: Tipo intralaboral desconocido: ' . $intralaboralType);
            return redirect()->back()->with('error', 'Tipo de formulario intralaboral desconocido: ' . $intralaboralType);
        }

        try {
            $view = view($viewPath, $data);
            log_message('error', '✅ Vista cargada exitosamente: ' . $viewPath);
            log_message('error', '🔍 ========== FIN results() - ÉXITO ==========');
            return $view;
        } catch (\Exception $e) {
            log_message('error', '❌ ERROR al cargar vista: ' . $e->getMessage());
            log_message('error', '❌ Stack trace: ' . $e->getTraceAsString());
            log_message('error', '🔍 ========== FIN results() - ERROR EN VISTA ==========');
            return redirect()->back()->with('error', 'Error al cargar la vista de resultados: ' . $e->getMessage());
        }
    }

    /**
     * Exportar respuestas del trabajador a Excel
     */
    public function exportResponses($workerId)
    {
        // Verificar autenticación
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // Obtener trabajador y datos relacionados
        $worker = $this->workerModel->find($workerId);
        if (!$worker) {
            return redirect()->back()->with('error', 'Trabajador no encontrado');
        }

        $service = $this->batteryServiceModel
            ->select('battery_services.*, companies.name as company_name')
            ->join('companies', 'companies.id = battery_services.company_id')
            ->find($worker['battery_service_id']);

        // Verificar permisos (consultores pueden exportar todos los resultados)
        $roleName = session()->get('role_name');
        if (!in_array($roleName, ['superadmin', 'consultor', 'admin'])) {
            return redirect()->back()->with('error', 'No tienes permisos');
        }

        // Obtener datos demográficos y respuestas
        $demographicsModel = new WorkerDemographicsModel();
        $demographics = $demographicsModel->getByWorkerId($workerId);

        $responseModel = new ResponseModel();
        $generalData = $responseModel->getWorkerFormResponses($workerId, 'general_data');

        // Obtener respuestas intralaborales según el tipo (A o B)
        $intralaboralFormType = 'intralaboral_' . $worker['intralaboral_type'];
        $intralaboral = $responseModel->getWorkerFormResponses($workerId, $intralaboralFormType);

        $extralaboral = $responseModel->getWorkerFormResponses($workerId, 'extralaboral');
        $estres = $responseModel->getWorkerFormResponses($workerId, 'estres');

        // Crear array asociativo de respuestas para fácil acceso
        $intralaboralMap = [];
        foreach ($intralaboral as $resp) {
            $intralaboralMap[$resp['question_number']] = $resp['answer_value'];
        }
        $extralaboralMap = [];
        foreach ($extralaboral as $resp) {
            $extralaboralMap[$resp['question_number']] = $resp['answer_value'];
        }
        $estresMap = [];
        foreach ($estres as $resp) {
            $estresMap[$resp['question_number']] = $resp['answer_value'];
        }

        // Cargar configuraciones de cuestionarios
        $intralaboralConfig = $worker['intralaboral_type'] == 'A'
            ? \Config\IntralaboralA::class
            : \Config\IntralaboralB::class;
        $extralaboralConfig = \Config\Extralaboral::class;
        $estresConfig = \Config\Estres::class;

        // Crear Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Respuestas');

        // Arrays para encabezados y valores
        $headers = [];
        $values = [];

        // 1. Cliente
        $headers[] = 'Cliente';
        $values[] = $service['company_name'] ?? '';

        // 2. FORMULARIO
        $headers[] = 'FORMULARIO';
        $values[] = 'TIPO ' . $worker['intralaboral_type'];

        // 3-8. Datos básicos del trabajador
        $headers[] = 'Tipo de documento de identidad';
        $values[] = 'CC'; // Por defecto

        $headers[] = 'Número de documento de identidad';
        $values[] = $worker['document'];

        $headers[] = 'Nombres completos.';
        $values[] = $worker['name'];

        $headers[] = 'Apellidos completos.';
        $values[] = ''; // No tenemos separación de apellidos

        $headers[] = '¿He comprendido el objetivo y la metodología de la Evaluación de Factores de Riesgo Psicosocial?';
        $values[] = 'Si';

        $headers[] = 'Luego de comprender los objetivos y metodología, ¿deseo diligenciar los cuestionarios en mención?';
        $values[] = 'Si';

        // 9-25. Datos demográficos
        if ($demographics) {
            $headers[] = 'Género';
            $values[] = $demographics['gender'] ?? '';

            $headers[] = 'Año de nacimiento';
            $values[] = $demographics['birth_year'] ?? '';

            $headers[] = 'Estado civil.';
            $values[] = $demographics['marital_status'] ?? '';

            $headers[] = 'Área de trabajo';
            $values[] = $demographics['department'] ?? '';

            $headers[] = 'Cargo que desempeña.';
            $values[] = $demographics['position_name'] ?? $worker['position'];

            $headers[] = 'Último nivel de estudios que alcanzó';
            $values[] = $demographics['education_level'] ?? '';

            $headers[] = '¿Cuál es su ocupación o profesión?  Por ejemplo: Psicólogo, economista, contador, Administrador.';
            $values[] = $demographics['occupation'] ?? '';

            $headers[] = 'Cursa algún estudio actualmente?';
            $values[] = $demographics['current_studies'] ?? 'No';

            $headers[] = 'Ciudad de residencia actual';
            $values[] = $demographics['city_residence'] ?? '';

            $headers[] = 'Localidad donde vive';
            $values[] = $demographics['locality_residence'] ?? '';

            $headers[] = 'Ciudad donde trabaja actualmente';
            $values[] = $demographics['city_work'] ?? '';

            $headers[] = 'Seleccione y marque el estrato de los servicios públicos de su vivienda';
            $values[] = $demographics['stratum'] ?? '';

            $headers[] = 'Tipo de vivienda';
            $values[] = $demographics['housing_type'] ?? '';

            $headers[] = 'Número de personas que dependen económicamente de usted (aunque vivan en otro lugar)';
            $values[] = $demographics['dependents'] ?? '';

            $headers[] = 'Antigüedad en la empresa';
            $values[] = $demographics['time_in_company_years'] ?? '';

            $headers[] = 'Indique cuántas horas diarias de trabajo están establecidas habitualmente por la empresa para su cargo.';
            $values[] = $demographics['hours_per_day'] ?? '';

            $headers[] = 'Seleccione el tipo de contrato que tiene actualmente (marque una sola opción)';
            $values[] = $demographics['contract_type'] ?? '';

            $headers[] = 'Tipo de salario.';
            $values[] = $demographics['salary_type'] ?? '';
        }

        // PREGUNTAS INTRALABORALES
        $this->addIntralaboralQuestions($headers, $values, $intralaboralConfig, $intralaboralMap, $worker['intralaboral_type']);

        // PREGUNTAS EXTRALABORALES
        $this->addExtralaboralQuestions($headers, $values, $extralaboralConfig, $extralaboralMap);

        // PREGUNTAS DE ESTRÉS
        $this->addEstresQuestions($headers, $values, $estresConfig, $estresMap);

        // Escribir encabezados en fila 1
        $col = 1;
        foreach ($headers as $header) {
            $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $sheet->setCellValue($columnLetter . '1', $header);
            $col++;
        }

        // Escribir valores en fila 2
        $col = 1;
        foreach ($values as $value) {
            $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $sheet->setCellValue($columnLetter . '2', $value);
            $col++;
        }

        // Generar archivo
        $writer = new Xlsx($spreadsheet);
        $filename = 'respuestas_' . $worker['document'] . '_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    private function addIntralaboralQuestions(&$headers, &$values, $config, $responsesMap, $type)
    {
        $prefix = $type == 'A' ? 'ILA' : 'ILB';
        $questions = $config::$questions;
        $sectionHeaders = $config::$sectionHeaders;

        // Obtener preguntas condicionales
        $conditionalQuestion = isset($config::$conditionalQuestion) ? $config::$conditionalQuestion : null;
        $conditionalQuestion1 = isset($config::$conditionalQuestion1) ? $config::$conditionalQuestion1 : null;
        $conditionalQuestion2 = isset($config::$conditionalQuestion2) ? $config::$conditionalQuestion2 : null;

        // Ordenar por número de pregunta
        ksort($questions);

        foreach ($questions as $qNum => $qText) {
            // Buscar si hay header antes de esta pregunta
            $sectionHeader = isset($sectionHeaders[$qNum]) ? $sectionHeaders[$qNum] . ' ' : '';

            $headers[] = $sectionHeader . '[' . $prefix . '-' . $qNum . '-' . $qText . ']';
            $values[] = isset($responsesMap[$qNum]) ? $this->convertAnswerToText($responsesMap[$qNum]) : '';

            // Para IntralaboralB: insertar pregunta condicional después de pregunta 88
            if ($type == 'B' && $qNum == 88 && $conditionalQuestion) {
                $headers[] = 'Las siguientes preguntas están relacionadas con la atención a clientes y usuarios. ' . $conditionalQuestion['text'];
                $values[] = isset($responsesMap[$conditionalQuestion['number']]) ? $this->convertConditionalAnswer($responsesMap[$conditionalQuestion['number']]) : '';
            }

            // Para IntralaboralA: insertar preguntas condicionales en sus posiciones
            if ($type == 'A' && $qNum == 105 && $conditionalQuestion1) {
                $sectionHeader = isset($conditionalQuestion1['section_header']) ? $conditionalQuestion1['section_header'] . ' ' : '';
                $headers[] = $sectionHeader . $conditionalQuestion1['text'];
                $values[] = isset($responsesMap[$conditionalQuestion1['number']]) ? $this->convertConditionalAnswer($responsesMap[$conditionalQuestion1['number']]) : '';

                // Subpreguntas de condicional 1
                if (isset($conditionalQuestion1['subquestions'])) {
                    foreach ($conditionalQuestion1['subquestions'] as $subQ) {
                        $headers[] = $sectionHeader . '[' . $prefix . '-' . $subQ['number'] . '-' . $subQ['text'] . ']';
                        $values[] = isset($responsesMap[$subQ['number']]) ? $this->convertAnswerToText($responsesMap[$subQ['number']]) : '';
                    }
                }
            }

            if ($type == 'A' && $qNum == 114 && $conditionalQuestion2) {
                $sectionHeader2 = isset($conditionalQuestion2['section_header']) ? $conditionalQuestion2['section_header'] . ' ' : '';
                $headers[] = $sectionHeader2 . $conditionalQuestion2['text'];
                $values[] = isset($responsesMap[$conditionalQuestion2['number']]) ? $this->convertConditionalAnswer($responsesMap[$conditionalQuestion2['number']]) : '';

                // Subpreguntas de condicional 2
                if (isset($conditionalQuestion2['subquestions'])) {
                    foreach ($conditionalQuestion2['subquestions'] as $subQ) {
                        $headers[] = $sectionHeader2 . '[' . $prefix . '-' . $subQ['number'] . '-' . $subQ['text'] . ']';
                        $values[] = isset($responsesMap[$subQ['number']]) ? $this->convertAnswerToText($responsesMap[$subQ['number']]) : '';
                    }
                }
            }
        }
    }

    private function addExtralaboralQuestions(&$headers, &$values, $config, $responsesMap)
    {
        $questions = $config::$questions;
        $sectionHeaders = $config::$sectionHeaders;

        ksort($questions);

        foreach ($questions as $qNum => $qText) {
            $sectionHeader = isset($sectionHeaders[$qNum]) ? $sectionHeaders[$qNum] . ' ' : '';

            $headers[] = $sectionHeader . '[EL-' . $qNum . '-' . $qText . ']';
            $values[] = isset($responsesMap[$qNum]) ? $this->convertAnswerToText($responsesMap[$qNum]) : '';
        }
    }

    private function addEstresQuestions(&$headers, &$values, $config, $responsesMap)
    {
        $questions = $config::$questions;
        $sectionHeader = $config::$sectionHeader;

        ksort($questions);

        foreach ($questions as $qNum => $qText) {
            $headers[] = $sectionHeader . ' [ES-' . $qNum . '-' . $qText . ']';
            $values[] = isset($responsesMap[$qNum]) ? $this->convertEstresAnswer($responsesMap[$qNum]) : '';
        }
    }

    private function convertAnswerToText($value)
    {
        // Convertir a entero para asegurar comparación correcta
        $value = (int)$value;

        $map = [
            0 => 'Siempre',
            1 => 'Casi siempre',
            2 => 'Algunas veces',
            3 => 'Casi nunca',
            4 => 'Nunca'
        ];
        return $map[$value] ?? $value;
    }

    private function convertEstresAnswer($value)
    {
        // Convertir a entero para asegurar comparación correcta
        $value = (int)$value;

        // Mapeo oficial (0, 3, 6, 9)
        $mapOfficial = [
            0 => 'Siempre',
            3 => 'Casi siempre',
            6 => 'A veces',
            9 => 'Nunca'
        ];

        // Si el valor está en el mapeo oficial, usarlo
        if (isset($mapOfficial[$value])) {
            return $mapOfficial[$value];
        }

        // Fallback: mapeo alternativo por si está guardado como 0-4
        $mapAlternative = [
            0 => 'Siempre',
            1 => 'Casi siempre',
            2 => 'A veces',
            3 => 'Casi nunca',
            4 => 'Nunca'
        ];

        return $mapAlternative[$value] ?? $value;
    }

    private function convertConditionalAnswer($value)
    {
        return $value == 1 ? 'Si' : 'No';
    }

    /**
     * Mostrar formulario para crear un nuevo trabajador
     */
    public function create($serviceId)
    {
        // Verificar autenticación
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // Verificar que el servicio existe
        $service = $this->batteryServiceModel
            ->select('battery_services.*, companies.name as company_name')
            ->join('companies', 'companies.id = battery_services.company_id')
            ->find($serviceId);

        if (!$service) {
            return redirect()->to('/battery-services')->with('error', 'Servicio no encontrado');
        }

        // Verificar permisos (consultores pueden crear trabajadores en cualquier servicio)
        $roleName = session()->get('role_name');
        if (!in_array($roleName, ['superadmin', 'consultor', 'admin'])) {
            return redirect()->to('/battery-services')->with('error', 'No tienes permisos');
        }

        return view('workers/create', [
            'title' => 'Nuevo Trabajador',
            'service' => $service
        ]);
    }

    /**
     * Guardar un nuevo trabajador
     */
    public function store($serviceId)
    {
        // Verificar autenticación
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // Verificar permisos del servicio
        $service = $this->batteryServiceModel->find($serviceId);
        if (!$service) {
            return redirect()->to('/battery-services')->with('error', 'Servicio no encontrado');
        }

        // Verificar permisos (consultores pueden guardar trabajadores en cualquier servicio)
        $roleName = session()->get('role_name');
        if (!in_array($roleName, ['superadmin', 'consultor', 'admin'])) {
            return redirect()->to('/battery-services')->with('error', 'No tienes permisos');
        }

        // Validar datos básicos
        $rules = [
            'name' => 'required|min_length[3]|max_length[255]',
            'document' => 'required|min_length[5]|max_length[20]|numeric',
            'email' => 'required|valid_email',
            'position' => 'required|min_length[3]|max_length[255]',
            'intralaboral_type' => 'required|in_list[A,B]',
            'application_mode' => 'required|in_list[presencial,virtual]'
        ];

        if (!$this->validate($rules)) {
            $errors = $this->validator->getErrors();
            log_message('error', 'Worker validation failed: ' . json_encode($errors));
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        // Validar unicidad de documento dentro del mismo servicio
        $document = $this->request->getPost('document');

        $existingByDocument = $this->workerModel
            ->where('battery_service_id', $serviceId)
            ->where('document', $document)
            ->first();

        if ($existingByDocument) {
            return redirect()->back()->withInput()->with('errors', ['document' => 'Ya existe un trabajador con este documento en este servicio']);
        }

        // Crear trabajador
        $data = [
            'battery_service_id' => $serviceId,
            'name' => $this->request->getPost('name'),
            'document' => $this->request->getPost('document'),
            'email' => $this->request->getPost('email'),
            'position' => $this->request->getPost('position'),
            'area' => $this->request->getPost('area'),
            'intralaboral_type' => $this->request->getPost('intralaboral_type'),
            'application_mode' => $this->request->getPost('application_mode'),
            'status' => 'pendiente',
            'token' => bin2hex(random_bytes(32))
        ];

        log_message('debug', 'Attempting to insert worker with data: ' . json_encode($data));

        if ($this->workerModel->insert($data)) {
            $newId = $this->workerModel->getInsertID();
            log_message('debug', 'Worker inserted successfully with ID: ' . $newId);
            return redirect()->to('/workers/service/' . $serviceId)->with('success', 'Trabajador creado exitosamente (ID: ' . $newId . ')');
        } else {
            $errors = $this->workerModel->errors();
            log_message('error', 'Worker insert failed. Errors: ' . json_encode($errors));
            return redirect()->back()->withInput()->with('error', 'Error al crear el trabajador: ' . implode(', ', $errors));
        }
    }

    /**
     * Vista de Pre-Cierre: Gestión de cierre del servicio
     */
    public function preClose($serviceId)
    {
        // Verificar permisos (solo consultor)
        if (session()->get('role') !== 'consultor') {
            return redirect()->to('/dashboard')->with('error', 'No tienes permisos para cerrar servicios');
        }

        $service = $this->batteryServiceModel->find($serviceId);

        if (!$service) {
            return redirect()->to('/dashboard')->with('error', 'Servicio no encontrado');
        }

        // Verificar permisos del consultor
        $permissionCheck = $this->checkWorkerPermissions($service);
        if ($permissionCheck) {
            return $permissionCheck;
        }

        // Si ya está cerrado, redirigir
        if ($service['status'] === 'cerrado') {
            return redirect()->to('/workers/service/' . $serviceId)
                ->with('info', 'Este servicio ya está cerrado');
        }

        // Obtener estadísticas de trabajadores
        $workers = $this->workerModel->where('battery_service_id', $serviceId)->findAll();

        $stats = [
            'total' => count($workers),
            'completados' => count(array_filter($workers, fn($w) => $w['status'] === 'completado')),
            'en_proceso' => count(array_filter($workers, fn($w) => $w['status'] === 'en_proceso')),
            'invitados' => count(array_filter($workers, fn($w) => $w['status'] === 'invitado')),
            'pendientes' => count(array_filter($workers, fn($w) => $w['status'] === 'pendiente')),
            'no_participo' => count(array_filter($workers, fn($w) => $w['status'] === 'no_participo')),
            'abandonados' => count(array_filter($workers, fn($w) => $w['status'] === 'abandonado'))
        ];

        // Calcular porcentaje de completados
        $stats['percent_completado'] = ($stats['total'] > 0)
            ? round(($stats['completados'] / $stats['total']) * 100, 2)
            : 0;

        // Trabajadores que necesitan gestión (sin estado definitivo)
        $pendingManagement = array_filter($workers, function($w) {
            return in_array($w['status'], ['en_proceso', 'invitado', 'pendiente']);
        });

        $data = [
            'service' => $service,
            'stats' => $stats,
            'pendingManagement' => $pendingManagement,
            'minPercent' => $service['min_participation_percent'] ?? 50
        ];

        return view('workers/pre_close', $data);
    }

    /**
     * Actualizar estados de trabajadores masivamente
     */
    public function updateWorkerStatuses($serviceId)
    {
        // Verificar permisos
        if (session()->get('role') !== 'consultor') {
            return redirect()->to('/dashboard')->with('error', 'No tienes permisos');
        }

        $service = $this->batteryServiceModel->find($serviceId);
        $permissionCheck = $this->checkWorkerPermissions($service);
        if ($permissionCheck) {
            return $permissionCheck;
        }

        // Recibir datos del formulario
        $updates = $this->request->getPost('worker_updates');

        if (empty($updates)) {
            return redirect()->back()->with('error', 'No hay cambios para guardar');
        }

        $updatedCount = 0;

        foreach ($updates as $workerId => $data) {
            // Validar que se haya seleccionado un estado
            if (empty($data['status'])) {
                continue;
            }

            $updateData = [
                'status' => $data['status']
            ];

            // Si es "no_participo", guardar motivo
            if ($data['status'] === 'no_participo') {
                $updateData['non_participation_reason'] = $data['reason'] ?? null;
                $updateData['non_participation_notes'] = $data['notes'] ?? null;
            } elseif ($data['status'] === 'abandonado') {
                $updateData['non_participation_notes'] = $data['notes'] ?? null;
            }

            if ($this->workerModel->update($workerId, $updateData)) {
                $updatedCount++;
            }
        }

        return redirect()->to('/workers/service/' . $serviceId . '/pre-close')
            ->with('success', "Se actualizaron $updatedCount trabajadores correctamente");
    }

    /**
     * Cerrar servicio definitivamente
     */
    public function closeService($serviceId)
    {
        // Verificar permisos
        if (session()->get('role') !== 'consultor') {
            return redirect()->to('/dashboard')->with('error', 'No tienes permisos');
        }

        $service = $this->batteryServiceModel->find($serviceId);

        if (!$service) {
            return redirect()->to('/dashboard')->with('error', 'Servicio no encontrado');
        }

        $permissionCheck = $this->checkWorkerPermissions($service);
        if ($permissionCheck) {
            return $permissionCheck;
        }

        // Verificar que no esté ya cerrado
        if ($service['status'] === 'cerrado') {
            return redirect()->to('/workers/service/' . $serviceId)
                ->with('info', 'Este servicio ya está cerrado');
        }

        // Obtener todos los trabajadores
        $workers = $this->workerModel->where('battery_service_id', $serviceId)->findAll();

        // Verificar que todos los trabajadores tengan un estado definitivo
        $pendingStates = array_filter($workers, fn($w) =>
            in_array($w['status'], ['pendiente', 'invitado', 'en_proceso'])
        );

        if (!empty($pendingStates)) {
            return redirect()->to('/workers/service/' . $serviceId . '/pre-close')
                ->with('error', 'Debes gestionar todos los trabajadores pendientes antes de cerrar el servicio');
        }

        // Verificar porcentaje mínimo de participación
        $completados = count(array_filter($workers, fn($w) => $w['status'] === 'completado'));
        $total = count($workers);
        $percent = ($total > 0) ? ($completados / $total) * 100 : 0;
        $minPercent = $service['min_participation_percent'] ?? 50;

        if ($percent < $minPercent) {
            return redirect()->to('/workers/service/' . $serviceId . '/pre-close')
                ->with('error', "Porcentaje de participación (" . number_format($percent, 1) . "%) menor al mínimo requerido ($minPercent%)");
        }

        // Cerrar servicio
        $updateData = [
            'status' => 'cerrado',
            'closed_at' => date('Y-m-d H:i:s'),
            'closed_by' => session()->get('id'),
            'closure_notes' => $this->request->getPost('closure_notes')
        ];

        if ($this->batteryServiceModel->update($serviceId, $updateData)) {
            // Enviar notificaciones por email
            $this->sendClosureNotifications($service, $completados, $total, $percent);

            return redirect()->to('/workers/service/' . $serviceId)
                ->with('success', 'Servicio cerrado exitosamente. El cliente ya puede ver los informes. Se han enviado las notificaciones correspondientes.');
        } else {
            return redirect()->back()->with('error', 'Error al cerrar el servicio');
        }
    }

    /**
     * Enviar notificaciones de cierre de servicio
     */
    private function sendClosureNotifications($service, $completedCount, $totalCount, $participationPercent)
    {
        $emailService = new \App\Libraries\EmailService();
        $userModel = new \App\Models\UserModel();
        $companyModel = new \App\Models\CompanyModel();

        // Obtener información de la empresa
        $company = $companyModel->find($service['company_id']);

        // Obtener información del consultor que cerró
        $consultant = $userModel->find(session()->get('id'));
        $consultantName = $consultant['name'] ?? 'Consultor';

        $closureDate = date('d/m/Y H:i');

        // 1. NOTIFICAR AL CLIENTE (usuarios tipo cliente_empresa y cliente_gestor de la empresa)
        $clientUsers = $userModel
            ->where('company_id', $service['company_id'])
            ->whereIn('role', ['cliente_empresa', 'cliente_gestor'])
            ->findAll();

        foreach ($clientUsers as $clientUser) {
            try {
                $emailService->sendServiceClosureToClient(
                    $clientUser['email'],
                    $clientUser['name'],
                    $service['service_name'],
                    $company['company_name'],
                    $completedCount,
                    $totalCount,
                    $participationPercent
                );
            } catch (\Exception $e) {
                log_message('error', 'Error enviando email a cliente: ' . $e->getMessage());
            }
        }

        // 2. NOTIFICAR AL GESTOR/MANAGER (para facturación) - usuarios tipo admin o superadmin
        $managers = $userModel->whereIn('role', ['admin', 'superadmin'])->findAll();

        foreach ($managers as $manager) {
            try {
                $emailService->sendServiceClosureToManager(
                    $manager['email'],
                    $manager['name'],
                    $service['service_name'],
                    $company['company_name'],
                    $completedCount,
                    $totalCount,
                    $consultantName,
                    $closureDate
                );
            } catch (\Exception $e) {
                log_message('error', 'Error enviando email a manager: ' . $e->getMessage());
            }
        }

        // 3. NOTIFICAR AL VENDEDOR/COMERCIAL
        // Buscar al vendedor asociado al servicio (si existe un campo consultant_id o similar)
        // Por ahora notificamos a todos los usuarios comerciales
        $commercialUsers = $userModel->where('role', 'comercial')->findAll();

        foreach ($commercialUsers as $commercial) {
            try {
                $emailService->sendServiceClosureToCommercial(
                    $commercial['email'],
                    $commercial['name'],
                    $service['service_name'],
                    $company['company_name'],
                    $completedCount,
                    $closureDate
                );
            } catch (\Exception $e) {
                log_message('error', 'Error enviando email a comercial: ' . $e->getMessage());
            }
        }

        log_message('info', "Notificaciones de cierre enviadas para servicio ID: {$service['id']}");
    }

    /**
     * Calcular resultados para todos los trabajadores completados de un servicio
     */
    public function calculateAllResults($serviceId)
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['success' => false, 'message' => 'No autenticado']);
        }

        // Verificar permisos
        $roleName = session()->get('role_name');
        if (!in_array($roleName, ['superadmin', 'consultor', 'admin'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'No tienes permisos']);
        }

        // Verificar que el servicio existe
        $service = $this->batteryServiceModel->find($serviceId);
        if (!$service) {
            return $this->response->setJSON(['success' => false, 'message' => 'Servicio no encontrado']);
        }

        // Obtener todos los trabajadores completados
        $workers = $this->workerModel
            ->where('battery_service_id', $serviceId)
            ->where('status', 'completado')
            ->findAll();

        if (empty($workers)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No hay trabajadores completados en este servicio'
            ]);
        }

        $calculationService = new \App\Services\CalculationService();
        $resultModel = new CalculatedResultModel();

        $calculated = 0;
        $failed = 0;
        $errors = [];

        foreach ($workers as $worker) {
            try {
                // Calcular resultados - verificar el valor de retorno
                $result = $calculationService->calculateAndSaveResults($worker['id']);

                if ($result !== false) {
                    $calculated++;
                } else {
                    // El método retornó false (formularios incompletos u otro error)
                    $failed++;
                    $incompleteInfo = $calculationService->getIncompleteFormsInfo($worker['id'], $worker['intralaboral_type']);
                    if (!empty($incompleteInfo)) {
                        $errors[] = $worker['name'] . ': ' . $incompleteInfo[0]['message'];
                    } else {
                        $errors[] = $worker['name'] . ': Error desconocido al calcular';
                    }
                }

            } catch (\Exception $e) {
                $failed++;
                $errors[] = $worker['name'] . ': ' . $e->getMessage();
                log_message('error', 'Error calculando resultados para worker ' . $worker['id'] . ': ' . $e->getMessage());
            }
        }

        $message = "Cálculo completado. Exitosos: {$calculated}, Fallidos: {$failed}";

        return $this->response->setJSON([
            'success' => true,
            'message' => $message,
            'total' => count($workers),
            'calculated' => $calculated,
            'failed' => $failed,
            'errors' => $errors
        ]);
    }
}
