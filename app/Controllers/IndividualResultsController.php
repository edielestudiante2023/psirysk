<?php

namespace App\Controllers;

use App\Models\IndividualResultRequestModel;
use App\Models\WorkerModel;
use App\Models\BatteryServiceModel;
use App\Libraries\EmailService;

class IndividualResultsController extends BaseController
{
    protected $requestModel;
    protected $workerModel;
    protected $serviceModel;
    protected $emailService;

    public function __construct()
    {
        $this->requestModel = new IndividualResultRequestModel();
        $this->workerModel = new WorkerModel();
        $this->serviceModel = new BatteryServiceModel();
        $this->emailService = new EmailService();
    }

    /**
     * Show request form for accessing individual results
     */
    public function requestAccess($serviceId, $workerId, $requestType)
    {
        // Verify user is authenticated
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $userId = session()->get('id');

        // Check if there's already a pending request
        $existingRequest = $this->requestModel
            ->where('service_id', $serviceId)
            ->where('worker_id', $workerId)
            ->where('requester_user_id', $userId)
            ->where('request_type', $requestType)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existingRequest) {
            if ($existingRequest['status'] === 'pending') {
                return redirect()->to("/individual-results/status/{$existingRequest['id']}");
            } elseif ($existingRequest['status'] === 'approved') {
                // Check if access is still valid
                if (strtotime($existingRequest['access_granted_until']) > time()) {
                    return redirect()->to("/individual-results/view/{$existingRequest['access_token']}");
                }
            }
        }

        // Get worker and service info
        $worker = $this->workerModel->find($workerId);
        $service = $this->serviceModel->find($serviceId);

        if (!$worker || !$service) {
            return redirect()->back()->with('error', 'Trabajador o servicio no encontrado');
        }

        $data = [
            'title' => 'Solicitud de Acceso a Resultados Individuales',
            'worker' => $worker,
            'service' => $service,
            'requestType' => $requestType,
        ];

        return view('individual_results/request_form', $data);
    }

    /**
     * Process the access request submission
     */
    public function submitRequest()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $userId = session()->get('id');

        $data = [
            'service_id'        => $this->request->getPost('service_id'),
            'worker_id'         => $this->request->getPost('worker_id'),
            'requester_user_id' => $userId,
            'request_type'      => $this->request->getPost('request_type'),
            'motivation'        => $this->request->getPost('motivation'),
            'status'            => 'pending',
            'ip_address'        => $this->request->getIPAddress(),
            'user_agent'        => $this->request->getUserAgent()->getAgentString(),
        ];

        if ($this->requestModel->insert($data)) {
            $requestId = $this->requestModel->getInsertID();

            // Send email notification to consultant
            $this->notifyConsultant($requestId);

            return redirect()->to("/individual-results/status/{$requestId}")
                ->with('success', 'Su solicitud ha sido enviada. Recibirá una notificación cuando sea revisada.');
        } else {
            $errors = $this->requestModel->errors();
            return redirect()->back()
                ->withInput()
                ->with('errors', $errors);
        }
    }

    /**
     * Show request status to the requester
     */
    public function showStatus($requestId)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $userId = session()->get('id');
        $request = $this->requestModel->getRequestWithDetails($requestId);

        if (!$request || $request['requester_user_id'] != $userId) {
            return redirect()->to('/dashboard')->with('error', 'Solicitud no encontrada');
        }

        $data = [
            'title' => 'Estado de Solicitud',
            'request' => $request,
        ];

        return view('individual_results/request_status', $data);
    }

    /**
     * View individual results (only if approved and within access window)
     */
    public function viewResults($accessToken)
    {
        $request = $this->requestModel->where('access_token', $accessToken)->first();

        if (!$request) {
            return view('individual_results/access_denied', [
                'title' => 'Acceso Denegado',
                'message' => 'Token de acceso inválido',
            ]);
        }

        // Check if approved
        if ($request['status'] !== 'approved') {
            return view('individual_results/access_denied', [
                'title' => 'Acceso Denegado',
                'message' => 'Esta solicitud no ha sido aprobada',
            ]);
        }

        // Check if access is still valid
        if (strtotime($request['access_granted_until']) < time()) {
            return view('individual_results/access_denied', [
                'title' => 'Acceso Expirado',
                'message' => 'El período de acceso ha expirado',
            ]);
        }

        // Redirect to the appropriate results page based on request type
        $workerId = $request['worker_id'];
        $serviceId = $request['service_id'];

        switch ($request['request_type']) {
            case 'intralaboral_a':
                return redirect()->to("/reports/intralaboral/detail-forma-a/{$serviceId}/{$workerId}");
            case 'intralaboral_b':
                return redirect()->to("/reports/intralaboral/detail-forma-b/{$serviceId}/{$workerId}");
            case 'extralaboral':
                return redirect()->to("/reports/extralaboral/detail/{$serviceId}/{$workerId}");
            case 'estres':
                return redirect()->to("/reports/estres/detail/{$serviceId}/{$workerId}");
            default:
                return view('individual_results/access_denied', [
                    'title' => 'Error',
                    'message' => 'Tipo de resultado no válido',
                ]);
        }
    }

    /**
     * Management dashboard for consultants
     */
    public function managementDashboard()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $userId = session()->get('id');
        $userRole = session()->get('role');

        // Only consultants can access this
        if ($userRole !== 'consultor') {
            return redirect()->to('/dashboard')->with('error', 'No tiene permisos para acceder a esta sección');
        }

        $pendingRequests = $this->requestModel->getPendingRequestsForConsultant($userId);

        $data = [
            'title' => 'Gestión de Solicitudes de Acceso',
            'pendingRequests' => $pendingRequests,
        ];

        return view('individual_results/management_dashboard', $data);
    }

    /**
     * Show request details for review
     */
    public function reviewRequest($requestId)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $userId = session()->get('id');
        $userRole = session()->get('role');

        if ($userRole !== 'consultor') {
            return redirect()->to('/dashboard')->with('error', 'No tiene permisos');
        }

        $request = $this->requestModel->getRequestWithDetails($requestId);

        if (!$request) {
            return redirect()->to('/individual-results/management')->with('error', 'Solicitud no encontrada');
        }

        $data = [
            'title' => 'Revisar Solicitud',
            'request' => $request,
        ];

        return view('individual_results/review_request', $data);
    }

    /**
     * Approve request (can be triggered from email link or dashboard)
     */
    public function approveRequest($requestId, $token = null)
    {
        // If token provided, validate it (for email approval)
        if ($token) {
            $request = $this->requestModel->find($requestId);
            if (!$request || $request['access_token'] !== $token) {
                return view('individual_results/access_denied', [
                    'title' => 'Error',
                    'message' => 'Token inválido',
                ]);
            }
            $reviewerId = $request['reviewed_by'] ?? session()->get('user_id');
        } else {
            // Dashboard approval - requires login
            if (!session()->get('isLoggedIn') || session()->get('role') !== 'consultor') {
                return redirect()->to('/login');
            }
            $reviewerId = session()->get('user_id');
        }

        $reviewNotes = $this->request->getPost('review_notes') ?? 'Aprobado por el consultor';
        $accessHours = $this->request->getPost('access_hours') ?? 48;

        if ($this->requestModel->approveRequest($requestId, $reviewerId, $reviewNotes, $accessHours)) {
            // Send email to requester
            $this->notifyRequester($requestId, 'approved');

            if ($token) {
                return view('individual_results/approval_success', [
                    'title' => 'Solicitud Aprobada',
                    'message' => 'La solicitud ha sido aprobada exitosamente',
                ]);
            } else {
                return redirect()->to('/individual-results/management')
                    ->with('success', 'Solicitud aprobada exitosamente');
            }
        } else {
            return redirect()->back()->with('error', 'Error al aprobar la solicitud');
        }
    }

    /**
     * Reject request
     */
    public function rejectRequest($requestId)
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'consultor') {
            return redirect()->to('/login');
        }

        $reviewerId = session()->get('user_id');
        $reviewNotes = $this->request->getPost('review_notes');

        if (!$reviewNotes) {
            return redirect()->back()->with('error', 'Debe proporcionar un motivo de rechazo');
        }

        if ($this->requestModel->rejectRequest($requestId, $reviewerId, $reviewNotes)) {
            // Send email to requester
            $this->notifyRequester($requestId, 'rejected');

            return redirect()->to('/individual-results/management')
                ->with('success', 'Solicitud rechazada');
        } else {
            return redirect()->back()->with('error', 'Error al rechazar la solicitud');
        }
    }

    /**
     * Send email notification to consultant when new request is submitted
     */
    private function notifyConsultant($requestId)
    {
        $request = $this->requestModel->getRequestWithDetails($requestId);

        if (!$request) {
            return false;
        }

        // Get consultant info
        $service = $this->serviceModel->select('users.email, users.name')
            ->join('users', 'users.id = battery_services.consultant_id')
            ->where('battery_services.id', $request['service_id'])
            ->first();

        if (!$service) {
            return false;
        }

        return $this->emailService->sendRequestNotificationToConsultant(
            $service['email'],
            $service['name'],
            $request
        );
    }

    /**
     * Send email notification to requester when request is reviewed
     */
    private function notifyRequester($requestId, $status)
    {
        $request = $this->requestModel->getRequestWithDetails($requestId);

        if (!$request) {
            return false;
        }

        if ($status === 'approved') {
            return $this->emailService->sendRequestApprovedToClient(
                $request['requester_email'],
                $request['requester_name'],
                $request
            );
        } else {
            return $this->emailService->sendRequestRejectedToClient(
                $request['requester_email'],
                $request['requester_name'],
                $request
            );
        }
    }
}
