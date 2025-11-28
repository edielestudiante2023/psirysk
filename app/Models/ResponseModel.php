<?php

namespace App\Models;

use CodeIgniter\Model;

class ResponseModel extends Model
{
    protected $table            = 'responses';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'worker_id',
        'form_type',
        'question_number',
        'answer_value',
        'session_id'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'worker_id' => 'required|integer',
        'form_type' => 'required|in_list[ficha_datos_generales,intralaboral_A,intralaboral_B,extralaboral,estres]',
        'question_number' => 'required|integer',
        'answer_value' => 'required', // Can be integer or text (siempre, casi_siempre, a_veces, nunca)
    ];

    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['setCreatedAt'];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    protected function setCreatedAt(array $data)
    {
        $data['data']['created_at'] = date('Y-m-d H:i:s');
        return $data;
    }

    /**
     * Get all responses for a specific worker and form type
     */
    public function getWorkerFormResponses($workerId, $formType)
    {
        return $this->where('worker_id', $workerId)
                    ->where('form_type', $formType)
                    ->orderBy('question_number', 'ASC')
                    ->findAll();
    }

    /**
     * Check if worker has completed a specific form
     */
    public function isFormCompleted($workerId, $formType, $expectedQuestions)
    {
        $count = $this->where('worker_id', $workerId)
                      ->where('form_type', $formType)
                      ->countAllResults();

        return $count >= $expectedQuestions;
    }

    /**
     * Save or update response for a specific question
     */
    public function saveResponse($workerId, $formType, $questionNumber, $answerValue, $sessionId = null)
    {
        log_message('error', "🔍 [saveResponse] Worker: {$workerId}, Form: {$formType}, Q: {$questionNumber}, A: {$answerValue}");

        // Check if response already exists
        $existing = $this->where('worker_id', $workerId)
                         ->where('form_type', $formType)
                         ->where('question_number', $questionNumber)
                         ->first();

        log_message('error', '🔍 [saveResponse] Existing: ' . ($existing ? 'YES (id=' . $existing['id'] . ')' : 'NO'));

        $data = [
            'worker_id' => $workerId,
            'form_type' => $formType,
            'question_number' => $questionNumber,
            'answer_value' => $answerValue,
            'session_id' => $sessionId
        ];

        log_message('error', '🔍 [saveResponse] Data to save: ' . json_encode($data));

        if ($existing) {
            $result = $this->update($existing['id'], $data);
            log_message('error', '🔍 [saveResponse] UPDATE result: ' . ($result ? 'SUCCESS' : 'FAILED'));

            if (!$result) {
                log_message('error', '❌ [saveResponse] UPDATE errors: ' . json_encode($this->errors()));
            }

            return $result;
        } else {
            $result = $this->insert($data);
            log_message('error', '🔍 [saveResponse] INSERT result: ' . ($result ? 'SUCCESS (id=' . $result . ')' : 'FAILED'));

            if (!$result) {
                log_message('error', '❌ [saveResponse] INSERT errors: ' . json_encode($this->errors()));
            }

            return $result;
        }
    }

    /**
     * Get progress percentage for a worker's form
     */
    public function getFormProgress($workerId, $formType, $totalQuestions)
    {
        $answered = $this->where('worker_id', $workerId)
                         ->where('form_type', $formType)
                         ->countAllResults();

        return $totalQuestions > 0 ? round(($answered / $totalQuestions) * 100, 2) : 0;
    }

    /**
     * Delete all responses for a specific worker and form
     */
    public function deleteFormResponses($workerId, $formType)
    {
        return $this->where('worker_id', $workerId)
                    ->where('form_type', $formType)
                    ->delete();
    }

    /**
     * Get worker's overall progress across all forms
     */
    public function getOverallProgress($workerId)
    {
        $forms = [
            'ficha_datos_generales' => 0, // Will be stored in worker_demographics table
            'intralaboral_A' => 123,
            'intralaboral_B' => 97,
            'extralaboral' => 31,
            'estres' => 31
        ];

        $progress = [];
        foreach ($forms as $formType => $totalQuestions) {
            if ($totalQuestions > 0) {
                $progress[$formType] = $this->getFormProgress($workerId, $formType, $totalQuestions);
            }
        }

        return $progress;
    }
}
