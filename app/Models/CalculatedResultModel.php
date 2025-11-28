<?php

namespace App\Models;

use CodeIgniter\Model;

class CalculatedResultModel extends Model
{
    protected $table            = 'calculated_results';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'worker_id',
        'battery_service_id',
        'worker_name',
        'worker_document',
        'worker_email',

        // Demográficos
        'gender',
        'birth_year',
        'age',
        'marital_status',
        'education_level',
        'city_residence',
        'stratum',
        'housing_type',

        // Ocupacionales
        'department',
        'position',
        'position_type',
        'contract_type',
        'work_experience_years',
        'time_in_company_months',
        'time_in_position_months',
        'hours_per_day',

        // Tipo de formulario
        'intralaboral_form_type',

        // Dominios intralaboral
        'dom_liderazgo_puntaje', 'dom_liderazgo_nivel',
        'dom_control_puntaje', 'dom_control_nivel',
        'dom_demandas_puntaje', 'dom_demandas_nivel',
        'dom_recompensas_puntaje', 'dom_recompensas_nivel',

        // Dimensiones intralaboral
        'dim_caracteristicas_liderazgo_puntaje', 'dim_caracteristicas_liderazgo_nivel',
        'dim_relaciones_sociales_puntaje', 'dim_relaciones_sociales_nivel',
        'dim_retroalimentacion_puntaje', 'dim_retroalimentacion_nivel',
        'dim_relacion_colaboradores_puntaje', 'dim_relacion_colaboradores_nivel',
        'dim_claridad_rol_puntaje', 'dim_claridad_rol_nivel',
        'dim_capacitacion_puntaje', 'dim_capacitacion_nivel',
        'dim_participacion_manejo_cambio_puntaje', 'dim_participacion_manejo_cambio_nivel',
        'dim_oportunidades_desarrollo_puntaje', 'dim_oportunidades_desarrollo_nivel',
        'dim_control_autonomia_puntaje', 'dim_control_autonomia_nivel',
        'dim_demandas_ambientales_puntaje', 'dim_demandas_ambientales_nivel',
        'dim_demandas_emocionales_puntaje', 'dim_demandas_emocionales_nivel',
        'dim_demandas_cuantitativas_puntaje', 'dim_demandas_cuantitativas_nivel',
        'dim_influencia_trabajo_entorno_extralaboral_puntaje', 'dim_influencia_trabajo_entorno_extralaboral_nivel',
        'dim_demandas_carga_mental_puntaje', 'dim_demandas_carga_mental_nivel',
        'dim_demandas_jornada_trabajo_puntaje', 'dim_demandas_jornada_trabajo_nivel',
        'dim_consistencia_rol_puntaje', 'dim_consistencia_rol_nivel',
        'dim_demandas_responsabilidad_puntaje', 'dim_demandas_responsabilidad_nivel',
        'dim_recompensas_pertenencia_puntaje', 'dim_recompensas_pertenencia_nivel',
        'dim_reconocimiento_compensacion_puntaje', 'dim_reconocimiento_compensacion_nivel',

        // Total intralaboral
        'intralaboral_total_puntaje', 'intralaboral_total_nivel',

        // Extralaboral - Dimensiones
        'extralaboral_tiempo_fuera_puntaje', 'extralaboral_tiempo_fuera_nivel',
        'extralaboral_relaciones_familiares_puntaje', 'extralaboral_relaciones_familiares_nivel',
        'extralaboral_comunicacion_puntaje', 'extralaboral_comunicacion_nivel',
        'extralaboral_situacion_economica_puntaje', 'extralaboral_situacion_economica_nivel',
        'extralaboral_caracteristicas_vivienda_puntaje', 'extralaboral_caracteristicas_vivienda_nivel',
        'extralaboral_influencia_entorno_puntaje', 'extralaboral_influencia_entorno_nivel',
        'extralaboral_desplazamiento_puntaje', 'extralaboral_desplazamiento_nivel',
        'extralaboral_total_puntaje', 'extralaboral_total_nivel',

        // Estrés
        'estres_total_puntaje', 'estres_total_nivel',

        // Total general
        'puntaje_total_general', 'puntaje_total_general_nivel',

        // Metadatos
        'calculated_at'
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
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Get results by battery service with filters
     */
    public function getResultsByService($serviceId, $filters = [])
    {
        $builder = $this->where('battery_service_id', $serviceId);

        // Aplicar filtros de segmentación
        if (!empty($filters['intralaboral_form_type'])) {
            $builder->where('intralaboral_form_type', $filters['intralaboral_form_type']);
        }

        if (!empty($filters['gender'])) {
            $builder->where('gender', $filters['gender']);
        }

        if (!empty($filters['department'])) {
            $builder->where('department', $filters['department']);
        }

        if (!empty($filters['position_type'])) {
            $builder->where('position_type', $filters['position_type']);
        }

        if (!empty($filters['education_level'])) {
            $builder->where('education_level', $filters['education_level']);
        }

        if (!empty($filters['city_residence'])) {
            $builder->where('city_residence', $filters['city_residence']);
        }

        if (!empty($filters['marital_status'])) {
            $builder->where('marital_status', $filters['marital_status']);
        }

        if (!empty($filters['contract_type'])) {
            $builder->where('contract_type', $filters['contract_type']);
        }

        return $builder->findAll();
    }

    /**
     * Get aggregated statistics for dashboards
     */
    public function getStatsByService($serviceId, $filters = [])
    {
        $results = $this->getResultsByService($serviceId, $filters);

        if (empty($results)) {
            return null;
        }

        $stats = [
            'total_workers' => count($results),
            'form_type_distribution' => [
                'A' => 0,
                'B' => 0
            ],
            'risk_distribution' => [
                'sin_riesgo' => 0,
                'riesgo_bajo' => 0,
                'riesgo_medio' => 0,
                'riesgo_alto' => 0,
                'riesgo_muy_alto' => 0
            ],
            'average_scores' => [],
            'by_dimension' => []
        ];

        foreach ($results as $result) {
            // Distribución por tipo de formulario
            $stats['form_type_distribution'][$result['intralaboral_form_type']]++;

            // Distribución por nivel de riesgo total
            if (!empty($result['puntaje_total_general_nivel'])) {
                $stats['risk_distribution'][$result['puntaje_total_general_nivel']]++;
            }
        }

        return $stats;
    }

    /**
     * Get color code for risk level (for dashboards)
     */
    public static function getRiskColor($nivel)
    {
        $colors = [
            'sin_riesgo' => '#28a745',      // Verde
            'riesgo_bajo' => '#7dce82',     // Verde claro
            'riesgo_medio' => '#ffc107',    // Amarillo
            'riesgo_alto' => '#fd7e14',     // Naranja
            'riesgo_muy_alto' => '#dc3545', // Rojo
            'muy_bajo' => '#28a745',
            'bajo' => '#7dce82',
            'medio' => '#ffc107',
            'alto' => '#fd7e14',
            'muy_alto' => '#dc3545'
        ];

        return $colors[$nivel] ?? '#6c757d';
    }

    /**
     * Save results for a worker
     * Inserts or updates calculated results
     */
    public function saveResults($workerId, $data)
    {
        // Check if results already exist
        $existing = $this->where('worker_id', $workerId)->first();

        $data['worker_id'] = $workerId;

        if ($existing) {
            // Update existing record
            return $this->update($existing['id'], $data);
        } else {
            // Insert new record
            return $this->insert($data);
        }
    }

    /**
     * Get results by worker ID
     */
    public function getByWorkerId($workerId)
    {
        return $this->where('worker_id', $workerId)->first();
    }

    /**
     * Delete results by worker ID
     */
    public function deleteByWorkerId($workerId)
    {
        return $this->where('worker_id', $workerId)->delete();
    }
}
