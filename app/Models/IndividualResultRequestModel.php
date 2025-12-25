<?php

namespace App\Models;

use CodeIgniter\Model;

class IndividualResultRequestModel extends Model
{
    protected $table            = 'individual_results_requests';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'service_id',
        'worker_id',
        'requester_user_id',
        'request_type',
        'motivation',
        'status',
        'reviewed_by',
        'review_notes',
        'reviewed_at',
        'access_granted_until',
        'access_token',
        'ip_address',
        'user_agent',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'service_id'        => 'required|integer',
        'worker_id'         => 'required|integer',
        'requester_user_id' => 'required|integer',
        'request_type'      => 'required|in_list[intralaboral_a,intralaboral_b,extralaboral,estres]',
        'motivation'        => 'required|min_length[20]|max_length[2000]',
        'status'            => 'in_list[pending,approved,rejected]',
    ];

    protected $validationMessages = [
        'motivation' => [
            'required'   => 'Debe proporcionar una motivación para solicitar el acceso',
            'min_length' => 'La motivación debe tener al menos 20 caracteres',
            'max_length' => 'La motivación no puede exceder 2000 caracteres',
        ],
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['generateAccessToken'];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Generate unique access token before insert
     */
    protected function generateAccessToken(array $data)
    {
        if (!isset($data['data']['access_token'])) {
            $data['data']['access_token'] = bin2hex(random_bytes(32));
        }
        return $data;
    }

    /**
     * Get request with related data (worker, service, requester)
     */
    public function getRequestWithDetails($requestId)
    {
        return $this->select('
                individual_results_requests.*,
                workers.name as worker_name,
                workers.document as worker_document,
                requester.name as requester_name,
                requester.email as requester_email,
                reviewer.name as reviewer_name,
                services.service_name,
                companies.name as company_name
            ')
            ->join('workers', 'workers.id = individual_results_requests.worker_id')
            ->join('users as requester', 'requester.id = individual_results_requests.requester_user_id')
            ->join('users as reviewer', 'reviewer.id = individual_results_requests.reviewed_by', 'left')
            ->join('battery_services as services', 'services.id = individual_results_requests.service_id')
            ->join('companies', 'companies.id = services.company_id')
            ->where('individual_results_requests.id', $requestId)
            ->first();
    }

    /**
     * Get all pending requests (any consultant can approve any request)
     */
    public function getPendingRequestsForConsultant($consultantId = null)
    {
        return $this->select('
                individual_results_requests.*,
                workers.name as worker_name,
                workers.document as worker_document,
                requester.name as requester_name,
                requester.email as requester_email,
                services.service_name,
                companies.name as company_name
            ')
            ->join('workers', 'workers.id = individual_results_requests.worker_id')
            ->join('users as requester', 'requester.id = individual_results_requests.requester_user_id')
            ->join('battery_services as services', 'services.id = individual_results_requests.service_id')
            ->join('companies', 'companies.id = services.company_id')
            ->where('individual_results_requests.status', 'pending')
            ->orderBy('individual_results_requests.created_at', 'DESC')
            ->findAll();
    }

    /**
     * Get requests by service
     */
    public function getRequestsByService($serviceId)
    {
        return $this->select('
                individual_results_requests.*,
                workers.name as worker_name,
                requester.name as requester_name
            ')
            ->join('workers', 'workers.id = individual_results_requests.worker_id')
            ->join('users as requester', 'requester.id = individual_results_requests.requester_user_id')
            ->where('service_id', $serviceId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Check if user has active access to worker results
     */
    public function hasActiveAccess($userId, $workerId, $requestType)
    {
        $request = $this->where('requester_user_id', $userId)
            ->where('worker_id', $workerId)
            ->where('request_type', $requestType)
            ->where('status', 'approved')
            ->where('access_granted_until >=', date('Y-m-d H:i:s'))
            ->first();

        return $request !== null;
    }

    /**
     * Approve request and set access duration
     */
    public function approveRequest($requestId, $reviewerId, $reviewNotes = null, $accessHours = 48)
    {
        $data = [
            'status'               => 'approved',
            'reviewed_by'          => $reviewerId,
            'review_notes'         => $reviewNotes,
            'reviewed_at'          => date('Y-m-d H:i:s'),
            'access_granted_until' => date('Y-m-d H:i:s', strtotime("+{$accessHours} hours")),
        ];

        return $this->update($requestId, $data);
    }

    /**
     * Reject request
     */
    public function rejectRequest($requestId, $reviewerId, $reviewNotes)
    {
        $data = [
            'status'       => 'rejected',
            'reviewed_by'  => $reviewerId,
            'review_notes' => $reviewNotes,
            'reviewed_at'  => date('Y-m-d H:i:s'),
        ];

        return $this->update($requestId, $data);
    }

    /**
     * Get count of all pending requests (any consultant can see all)
     */
    public function getPendingCount($consultantId = null)
    {
        return $this->where('status', 'pending')
            ->countAllResults();
    }
}
