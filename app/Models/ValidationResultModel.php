<?php

namespace App\Models;

use CodeIgniter\Model;

class ValidationResultModel extends Model
{
    protected $table = 'validation_results';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'battery_service_id',
        'questionnaire_type',
        'form_type',
        'validation_level',
        'element_key',
        'element_name',
        'total_workers',
        'sum_averages',
        'transformation_factor',
        'calculated_score',
        'db_score',
        'difference',
        'validation_status',
        'processed_at',
        'processed_by'
    ];
    protected $useTimestamps = false;
    protected $returnType = 'array';

    /**
     * Verifica si las dimensiones están procesadas
     * @param int $serviceId
     * @param string $questionnaireType 'intralaboral', 'extralaboral', 'estres'
     * @param string|null $formType 'A' o 'B' (null para intralaboral sin distinción)
     * @return bool
     */
    public function areDimensionsProcessed($serviceId, $questionnaireType, $formType = null)
    {
        $builder = $this->where('battery_service_id', $serviceId)
            ->where('questionnaire_type', $questionnaireType)
            ->where('validation_level', 'dimension');

        if ($formType) {
            $builder->where('form_type', $formType);
        }

        return $builder->countAllResults() > 0;
    }

    /**
     * Verifica si los dominios están procesados
     * @param int $serviceId
     * @param string $questionnaireType 'intralaboral'
     * @param string|null $formType 'A' o 'B'
     * @return bool
     */
    public function areDomainsProcessed($serviceId, $questionnaireType, $formType = null)
    {
        $builder = $this->where('battery_service_id', $serviceId)
            ->where('questionnaire_type', $questionnaireType)
            ->where('validation_level', 'domain');

        if ($formType) {
            $builder->where('form_type', $formType);
        }

        return $builder->countAllResults() > 0;
    }

    /**
     * Verifica si los totales están procesados
     * @param int $serviceId
     * @param string $questionnaireType 'intralaboral', 'extralaboral', 'estres'
     * @param string|null $formType 'A' o 'B'
     * @return bool
     */
    public function areTotalsProcessed($serviceId, $questionnaireType, $formType = null)
    {
        $builder = $this->where('battery_service_id', $serviceId)
            ->where('questionnaire_type', $questionnaireType)
            ->where('validation_level', 'total');

        if ($formType) {
            $builder->where('form_type', $formType);
        }

        return $builder->countAllResults() > 0;
    }

    /**
     * Cuenta errores de validación
     * @param int $serviceId
     * @param string $questionnaireType
     * @param string|null $formType
     * @return int
     */
    public function countErrors($serviceId, $questionnaireType, $formType = null)
    {
        $builder = $this->where('battery_service_id', $serviceId)
            ->where('questionnaire_type', $questionnaireType)
            ->where('validation_status', 'error');

        if ($formType) {
            $builder->where('form_type', $formType);
        }

        return $builder->countAllResults();
    }

    /**
     * Obtiene un resultado de validación específico
     * @param int $serviceId
     * @param string $questionnaireType
     * @param string $formType
     * @param string $validationLevel 'dimension', 'domain', 'total'
     * @param string $elementKey
     * @return array|null
     */
    public function getValidationResult($serviceId, $questionnaireType, $formType, $validationLevel, $elementKey)
    {
        return $this->where('battery_service_id', $serviceId)
            ->where('questionnaire_type', $questionnaireType)
            ->where('form_type', $formType)
            ->where('validation_level', $validationLevel)
            ->where('element_key', $elementKey)
            ->first();
    }

    /**
     * Elimina validaciones anteriores para re-procesar
     * @param int $serviceId
     * @param string $questionnaireType
     * @param string $formType
     * @param string $validationLevel
     * @return bool
     */
    public function deletePreviousValidations($serviceId, $questionnaireType, $formType, $validationLevel)
    {
        return $this->where('battery_service_id', $serviceId)
            ->where('questionnaire_type', $questionnaireType)
            ->where('form_type', $formType)
            ->where('validation_level', $validationLevel)
            ->delete();
    }

    /**
     * Obtiene todas las validaciones de un servicio
     * @param int $serviceId
     * @param string|null $questionnaireType
     * @param string|null $formType
     * @return array
     */
    public function getServiceValidations($serviceId, $questionnaireType = null, $formType = null)
    {
        $builder = $this->where('battery_service_id', $serviceId);

        if ($questionnaireType) {
            $builder->where('questionnaire_type', $questionnaireType);
        }

        if ($formType) {
            $builder->where('form_type', $formType);
        }

        return $builder->orderBy('validation_level', 'ASC')
            ->orderBy('element_key', 'ASC')
            ->findAll();
    }

    /**
     * Obtiene validaciones con errores
     * @param int $serviceId
     * @param string|null $questionnaireType
     * @param string|null $formType
     * @return array
     */
    public function getErrorValidations($serviceId, $questionnaireType = null, $formType = null)
    {
        $builder = $this->where('battery_service_id', $serviceId)
            ->where('validation_status', 'error');

        if ($questionnaireType) {
            $builder->where('questionnaire_type', $questionnaireType);
        }

        if ($formType) {
            $builder->where('form_type', $formType);
        }

        return $builder->orderBy('abs(difference)', 'DESC')
            ->findAll();
    }

    /**
     * Obtiene resultados de dimensiones
     * @param int $serviceId
     * @param string $questionnaireType
     * @param string $formType
     * @return array
     */
    public function getDimensionResults($serviceId, $questionnaireType, $formType)
    {
        return $this->where('battery_service_id', $serviceId)
            ->where('questionnaire_type', $questionnaireType)
            ->where('form_type', $formType)
            ->where('validation_level', 'dimension')
            ->orderBy('element_key', 'ASC')
            ->findAll();
    }

    /**
     * Obtiene resultados de dominios
     * @param int $serviceId
     * @param string $questionnaireType
     * @param string $formType
     * @return array
     */
    public function getDomainResults($serviceId, $questionnaireType, $formType)
    {
        return $this->where('battery_service_id', $serviceId)
            ->where('questionnaire_type', $questionnaireType)
            ->where('form_type', $formType)
            ->where('validation_level', 'domain')
            ->orderBy('element_key', 'ASC')
            ->findAll();
    }

    /**
     * Obtiene resultado total
     * @param int $serviceId
     * @param string $questionnaireType
     * @param string $formType
     * @return array|null
     */
    public function getTotalResult($serviceId, $questionnaireType, $formType)
    {
        return $this->where('battery_service_id', $serviceId)
            ->where('questionnaire_type', $questionnaireType)
            ->where('form_type', $formType)
            ->where('validation_level', 'total')
            ->first();
    }
}
