<?php

namespace App\Models;

use CodeIgniter\Model;

class DemographicsInterpretationModel extends Model
{
    protected $table            = 'demographics_interpretations';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'battery_service_id',
        'interpretation_text',
        'consultant_comment',
        'aggregated_data',
        'generated_by',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'aggregated_data' => '?json-array',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'battery_service_id'  => 'required|integer',
        'interpretation_text' => 'required',
    ];

    protected $validationMessages = [];
    protected $skipValidation     = false;

    /**
     * Obtener la interpretación más reciente de un servicio
     */
    public function getLatestByService(int $serviceId): ?array
    {
        return $this->where('battery_service_id', $serviceId)
                    ->orderBy('created_at', 'DESC')
                    ->first();
    }

    /**
     * Obtener todas las interpretaciones de un servicio
     */
    public function getAllByService(int $serviceId): array
    {
        return $this->where('battery_service_id', $serviceId)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Guardar o actualizar interpretación
     * Si ya existe una para el servicio, la actualiza; si no, crea una nueva
     */
    public function saveInterpretation(int $serviceId, string $interpretation, ?array $aggregatedData = null, ?int $userId = null): bool
    {
        $existing = $this->getLatestByService($serviceId);

        $data = [
            'battery_service_id'  => $serviceId,
            'interpretation_text' => $interpretation,
            'aggregated_data'     => $aggregatedData, // El cast json-array hace el encode/decode automáticamente
            'generated_by'        => $userId,
        ];

        if ($existing) {
            // Actualizar existente
            return $this->update($existing['id'], $data);
        } else {
            // Crear nuevo
            return $this->insert($data) !== false;
        }
    }

    /**
     * Eliminar interpretación de un servicio
     */
    public function deleteByService(int $serviceId): bool
    {
        return $this->where('battery_service_id', $serviceId)->delete();
    }

    /**
     * Verificar si existe interpretación para un servicio
     */
    public function hasInterpretation(int $serviceId): bool
    {
        return $this->where('battery_service_id', $serviceId)->countAllResults() > 0;
    }

    /**
     * Obtener historial de interpretaciones (para auditoría)
     */
    public function getHistory(int $serviceId, int $limit = 10): array
    {
        return $this->select('demographics_interpretations.*, users.name as generated_by_name')
                    ->join('users', 'users.id = demographics_interpretations.generated_by', 'left')
                    ->where('battery_service_id', $serviceId)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Guardar comentario del consultor
     */
    public function saveConsultantComment(int $serviceId, string $comment): bool
    {
        $existing = $this->getLatestByService($serviceId);

        if ($existing) {
            return $this->update($existing['id'], ['consultant_comment' => $comment]);
        }

        // Si no existe interpretación, crear una vacía con el comentario
        return $this->insert([
            'battery_service_id'  => $serviceId,
            'interpretation_text' => '',
            'consultant_comment'  => $comment,
        ]) !== false;
    }

    /**
     * Obtener comentario del consultor
     */
    public function getConsultantComment(int $serviceId): ?string
    {
        $record = $this->getLatestByService($serviceId);
        return $record ? $record['consultant_comment'] : null;
    }
}
