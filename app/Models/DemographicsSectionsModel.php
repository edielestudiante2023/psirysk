<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo para manejar secciones demográficas individuales
 * Cada variable tiene su texto IA y datos guardados por separado
 */
class DemographicsSectionsModel extends Model
{
    protected $table            = 'demographics_sections';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'battery_service_id',
        // Variables sociodemográficas
        'sexo_ia', 'sexo_data',
        'edad_ia', 'edad_data',
        'estado_civil_ia', 'estado_civil_data',
        'educacion_ia', 'educacion_data',
        'estrato_ia', 'estrato_data',
        'vivienda_ia', 'vivienda_data',
        'dependientes_ia', 'dependientes_data',
        'residencia_ia', 'residencia_data',
        // Variables ocupacionales
        'antiguedad_empresa_ia', 'antiguedad_empresa_data',
        'antiguedad_cargo_ia', 'antiguedad_cargo_data',
        'contrato_ia', 'contrato_data',
        'cargo_ia', 'cargo_data',
        'area_ia', 'area_data',
        'horas_ia', 'horas_data',
        'salario_ia', 'salario_data',
        // Síntesis
        'sintesis_ia', 'sintesis_comment',
        // Metadata
        'total_workers', 'generated_by',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Casts para JSON
    protected array $casts = [
        'sexo_data' => '?json-array',
        'edad_data' => '?json-array',
        'estado_civil_data' => '?json-array',
        'educacion_data' => '?json-array',
        'estrato_data' => '?json-array',
        'vivienda_data' => '?json-array',
        'dependientes_data' => '?json-array',
        'residencia_data' => '?json-array',
        'antiguedad_empresa_data' => '?json-array',
        'antiguedad_cargo_data' => '?json-array',
        'contrato_data' => '?json-array',
        'cargo_data' => '?json-array',
        'area_data' => '?json-array',
        'horas_data' => '?json-array',
        'salario_data' => '?json-array',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Mapeo de secciones del texto IA a columnas de la BD
    protected $sectionMapping = [
        'SEXO' => 'sexo',
        'RANGO DE EDAD' => 'edad',
        'ESTADO CIVIL' => 'estado_civil',
        'NIVEL EDUCATIVO' => 'educacion',
        'NIVEL MÁXIMO DE ESCOLARIDAD' => 'educacion',
        'ESTRATO' => 'estrato',
        'VIVIENDA' => 'vivienda',
        'TIPO DE VIVIENDA' => 'vivienda',
        'PERSONAS A CARGO' => 'dependientes',
        'LUGAR DE RESIDENCIA' => 'residencia',
        'ANTIGÜEDAD EN LA EMPRESA' => 'antiguedad_empresa',
        'ANTIGÜEDAD EN EL CARGO' => 'antiguedad_cargo',
        'TIPO DE CONTRATO' => 'contrato',
        'TIPO DE CARGO' => 'cargo',
        'ÁREA/DEPARTAMENTO' => 'area',
        'ÁREA' => 'area',
        'DEPARTAMENTO' => 'area',
        'HORAS DE TRABAJO' => 'horas',
        'RANGO SALARIAL' => 'salario',
        'SÍNTESIS GENERAL' => 'sintesis',
        'SÍNTESIS' => 'sintesis',
    ];

    // Mapeo de datos agregados a columnas
    protected $dataMapping = [
        'gender' => 'sexo_data',
        'age_groups' => 'edad_data',
        'marital_status' => 'estado_civil_data',
        'education_level' => 'educacion_data',
        'stratum' => 'estrato_data',
        'housing_type' => 'vivienda_data',
        'dependents' => 'dependientes_data',
        'city_residence' => 'residencia_data',
        'time_in_company' => 'antiguedad_empresa_data',
        'time_in_position' => 'antiguedad_cargo_data',
        'contract_type' => 'contrato_data',
        'position_type' => 'cargo_data',
        'department_area' => 'area_data',
        'hours_per_day' => 'horas_data',
        'salary_type' => 'salario_data',
    ];

    /**
     * Obtener secciones de un servicio
     */
    public function getByService(int $serviceId): ?array
    {
        return $this->where('battery_service_id', $serviceId)->first();
    }

    /**
     * Verificar si existen secciones para un servicio
     */
    public function hasData(int $serviceId): bool
    {
        return $this->where('battery_service_id', $serviceId)->countAllResults() > 0;
    }

    /**
     * Parsear texto IA completo y separar en secciones
     */
    public function parseIAText(string $iaText): array
    {
        $sections = [];

        // Patrón: **TITULO:** contenido
        $pattern = '/\*\*([A-ZÁÉÍÓÚÜÑ\s\/]+):\*\*\s*(.*?)(?=\*\*[A-ZÁÉÍÓÚÜÑ\s\/]+:\*\*|$)/s';

        if (preg_match_all($pattern, $iaText, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $title = trim($match[1]);
                $content = trim($match[2]);

                // Buscar columna correspondiente
                foreach ($this->sectionMapping as $key => $column) {
                    if (stripos($title, $key) !== false || stripos($key, $title) !== false) {
                        $sections[$column . '_ia'] = $content;
                        break;
                    }
                }
            }
        }

        return $sections;
    }

    /**
     * Convertir datos agregados al formato de columnas
     */
    public function mapAggregatedData(array $aggregatedData): array
    {
        $data = [];

        foreach ($this->dataMapping as $source => $column) {
            if (isset($aggregatedData[$source])) {
                $data[$column] = $aggregatedData[$source];
            }
        }

        if (isset($aggregatedData['total_workers'])) {
            $data['total_workers'] = $aggregatedData['total_workers'];
        }

        return $data;
    }

    /**
     * Guardar o actualizar secciones completas
     */
    public function saveSections(int $serviceId, string $iaText, array $aggregatedData, ?int $userId = null): bool
    {
        // Parsear texto IA en secciones
        $iaSections = $this->parseIAText($iaText);

        // Mapear datos agregados
        $dataSections = $this->mapAggregatedData($aggregatedData);

        // Combinar todo
        $data = array_merge($iaSections, $dataSections);
        $data['battery_service_id'] = $serviceId;
        $data['generated_by'] = $userId;

        // Verificar si ya existe
        $existing = $this->getByService($serviceId);

        if ($existing) {
            return $this->update($existing['id'], $data);
        } else {
            return $this->insert($data) !== false;
        }
    }

    /**
     * Guardar solo el comentario del consultor en síntesis
     */
    public function saveSintesisComment(int $serviceId, string $comment): bool
    {
        $existing = $this->getByService($serviceId);

        if ($existing) {
            return $this->update($existing['id'], ['sintesis_comment' => $comment]);
        }

        // Si no existe, crear registro solo con el comentario
        return $this->insert([
            'battery_service_id' => $serviceId,
            'sintesis_comment' => $comment,
        ]) !== false;
    }

    /**
     * Obtener texto IA de una sección específica
     */
    public function getSectionIA(int $serviceId, string $section): ?string
    {
        $record = $this->getByService($serviceId);
        if (!$record) return null;

        $column = $section . '_ia';
        return $record[$column] ?? null;
    }

    /**
     * Obtener datos de una sección específica
     */
    public function getSectionData(int $serviceId, string $section): ?array
    {
        $record = $this->getByService($serviceId);
        if (!$record) return null;

        $column = $section . '_data';
        return $record[$column] ?? null;
    }

    /**
     * Obtener todas las secciones formateadas para la vista
     */
    public function getAllSectionsForView(int $serviceId): array
    {
        $record = $this->getByService($serviceId);
        if (!$record) return [];

        $sections = [
            // Sociodemográficas
            'sexo' => [
                'ia' => $record['sexo_ia'] ?? null,
                'data' => $record['sexo_data'] ?? null,
            ],
            'edad' => [
                'ia' => $record['edad_ia'] ?? null,
                'data' => $record['edad_data'] ?? null,
            ],
            'estado_civil' => [
                'ia' => $record['estado_civil_ia'] ?? null,
                'data' => $record['estado_civil_data'] ?? null,
            ],
            'educacion' => [
                'ia' => $record['educacion_ia'] ?? null,
                'data' => $record['educacion_data'] ?? null,
            ],
            'estrato' => [
                'ia' => $record['estrato_ia'] ?? null,
                'data' => $record['estrato_data'] ?? null,
            ],
            'vivienda' => [
                'ia' => $record['vivienda_ia'] ?? null,
                'data' => $record['vivienda_data'] ?? null,
            ],
            'dependientes' => [
                'ia' => $record['dependientes_ia'] ?? null,
                'data' => $record['dependientes_data'] ?? null,
            ],
            'residencia' => [
                'ia' => $record['residencia_ia'] ?? null,
                'data' => $record['residencia_data'] ?? null,
            ],
            // Ocupacionales
            'antiguedad_empresa' => [
                'ia' => $record['antiguedad_empresa_ia'] ?? null,
                'data' => $record['antiguedad_empresa_data'] ?? null,
            ],
            'antiguedad_cargo' => [
                'ia' => $record['antiguedad_cargo_ia'] ?? null,
                'data' => $record['antiguedad_cargo_data'] ?? null,
            ],
            'contrato' => [
                'ia' => $record['contrato_ia'] ?? null,
                'data' => $record['contrato_data'] ?? null,
            ],
            'cargo' => [
                'ia' => $record['cargo_ia'] ?? null,
                'data' => $record['cargo_data'] ?? null,
            ],
            'area' => [
                'ia' => $record['area_ia'] ?? null,
                'data' => $record['area_data'] ?? null,
            ],
            'horas' => [
                'ia' => $record['horas_ia'] ?? null,
                'data' => $record['horas_data'] ?? null,
            ],
            'salario' => [
                'ia' => $record['salario_ia'] ?? null,
                'data' => $record['salario_data'] ?? null,
            ],
            // Síntesis
            'sintesis' => [
                'ia' => $record['sintesis_ia'] ?? null,
                'comment' => $record['sintesis_comment'] ?? null,
            ],
        ];

        return $sections;
    }

    /**
     * Eliminar secciones de un servicio
     */
    public function deleteByService(int $serviceId): bool
    {
        return $this->where('battery_service_id', $serviceId)->delete();
    }
}
