<?php

namespace App\Controllers\Pdf\Resultados;

use App\Controllers\Pdf\PdfBaseController;
use App\Models\DemographicsSectionsModel;

/**
 * Controller para la sección de Resultados Sociodemográficos y Ocupacionales
 */
class SociodemograficosController extends PdfBaseController
{
    protected $sectionsModel;

    public function __construct()
    {
        $this->sectionsModel = new DemographicsSectionsModel();
    }

    /**
     * Renderiza todas las páginas de resultados sociodemográficos
     */
    public function render($batteryServiceId)
    {
        $this->initializeData($batteryServiceId);

        // Obtener estadísticas de participación
        $stats = $this->getParticipationStats($batteryServiceId);

        // Obtener secciones guardadas de demographics_sections
        $savedSections = $this->sectionsModel->getByService($batteryServiceId);

        // Preparar datos para las vistas
        $sections = [];
        $socioData = [];
        $ocupData = [];
        $sintesisComment = null;

        if ($savedSections) {
            // Usar secciones guardadas
            $sections = $this->prepareSectionsForView($savedSections);
            $socioData = $this->extractSocioDataFromSections($savedSections);
            $ocupData = $this->extractOcupDataFromSections($savedSections);
            $sintesisComment = $savedSections['sintesis_comment'] ?? null;
        } else {
            // Fallback: obtener datos directamente de BD
            $socioData = $this->getSociodemographicData($batteryServiceId);
            $ocupData = $this->getOccupationalData($batteryServiceId);
        }

        // Obtener resultados generales para las conclusiones
        $resultadosGenerales = $this->getResultadosGenerales($batteryServiceId);

        $html = '';

        // Página 1: Resultados + Conclusiones
        $html .= $this->renderView('pdf/resultados/sociodemograficos/resultados_conclusiones', [
            'totalParticipantes' => $stats['total'] ?? 0,
            'formaA' => $stats['forma_a'] ?? 0,
            'formaB' => $stats['forma_b'] ?? 0,
            'resultados' => $resultadosGenerales,
            'sintesisIA' => $savedSections['sintesis_ia'] ?? null,
            'sintesisComment' => $sintesisComment,
        ]);

        // Página 2: Variables Sociodemográficas (con texto IA por sección)
        $html .= $this->renderView('pdf/resultados/sociodemograficos/variables_sociodemograficas', [
            'totalParticipantes' => $stats['total'] ?? 0,
            'socioData' => $socioData,
            'sections' => $sections,
        ]);

        // Página 3: Resultados Ocupacionales
        $html .= $this->renderView('pdf/resultados/sociodemograficos/resultados_ocupacionales', [
            'totalParticipantes' => $stats['total'] ?? 0,
            'ocupData' => $ocupData,
            'sections' => $sections,
        ]);

        return $html;
    }

    /**
     * Prepara las secciones para las vistas
     */
    protected function prepareSectionsForView($savedSections)
    {
        return [
            'sexo' => $savedSections['sexo_ia'] ?? null,
            'edad' => $savedSections['edad_ia'] ?? null,
            'estado_civil' => $savedSections['estado_civil_ia'] ?? null,
            'educacion' => $savedSections['educacion_ia'] ?? null,
            'estrato' => $savedSections['estrato_ia'] ?? null,
            'vivienda' => $savedSections['vivienda_ia'] ?? null,
            'dependientes' => $savedSections['dependientes_ia'] ?? null,
            'residencia' => $savedSections['residencia_ia'] ?? null,
            'antiguedad_empresa' => $savedSections['antiguedad_empresa_ia'] ?? null,
            'antiguedad_cargo' => $savedSections['antiguedad_cargo_ia'] ?? null,
            'contrato' => $savedSections['contrato_ia'] ?? null,
            'cargo' => $savedSections['cargo_ia'] ?? null,
            'area' => $savedSections['area_ia'] ?? null,
            'horas' => $savedSections['horas_ia'] ?? null,
            'salario' => $savedSections['salario_ia'] ?? null,
            'sintesis' => $savedSections['sintesis_ia'] ?? null,
        ];
    }

    /**
     * Extrae datos sociodemográficos de las secciones guardadas
     */
    protected function extractSocioDataFromSections($savedSections)
    {
        $socioData = [];

        $mappings = [
            'sexo_data' => 'sexo',
            'edad_data' => 'edad',
            'estado_civil_data' => 'estado_civil',
            'educacion_data' => 'escolaridad',
            'estrato_data' => 'estrato',
            'vivienda_data' => 'vivienda',
            'dependientes_data' => 'dependientes',
            'residencia_data' => 'residencia',
        ];

        foreach ($mappings as $source => $target) {
            if (!empty($savedSections[$source])) {
                $socioData[$target] = $this->normalizeDataFormat($savedSections[$source]);
            }
        }

        return $socioData;
    }

    /**
     * Extrae datos ocupacionales de las secciones guardadas
     */
    protected function extractOcupDataFromSections($savedSections)
    {
        $ocupData = [];

        $mappings = [
            'antiguedad_empresa_data' => 'antiguedad',
            'contrato_data' => 'tipo_contrato',
            'cargo_data' => 'tipo_cargo',
            'area_data' => 'departamento',
            'horas_data' => 'horas',
            'salario_data' => 'salario',
        ];

        foreach ($mappings as $source => $target) {
            if (!empty($savedSections[$source])) {
                $ocupData[$target] = $this->normalizeDataFormat($savedSections[$source]);
            }
        }

        return $ocupData;
    }

    /**
     * Normaliza el formato de datos para las vistas
     */
    protected function normalizeDataFormat($data)
    {
        if (!is_array($data)) return [];

        $normalized = [];
        foreach ($data as $item) {
            // Manejar diferentes formatos de datos
            if (isset($item['distribution'])) {
                // Formato con distribution (age_groups, dependents, etc.)
                foreach ($item['distribution'] as $dist) {
                    $normalized[] = [
                        'valor' => $dist['label'] ?? '',
                        'cantidad' => $dist['count'] ?? 0,
                    ];
                }
            } else {
                // Formato simple
                $normalized[] = [
                    'valor' => $item['label'] ?? $item['valor'] ?? $item['value'] ?? '',
                    'cantidad' => $item['count'] ?? $item['cantidad'] ?? 0,
                ];
            }
        }

        return $normalized;
    }

    /**
     * Preview de todas las páginas sociodemográficas
     */
    public function preview($batteryServiceId)
    {
        $html = $this->render($batteryServiceId);

        return view('pdf/_partials/preview_wrapper', [
            'content' => $html,
            'pageTitle' => 'Preview: Resultados Sociodemográficos'
        ]);
    }

    /**
     * Obtiene datos sociodemográficos agregados
     */
    protected function getSociodemographicData($batteryServiceId)
    {
        $db = \Config\Database::connect();

        $data = [];

        // Sexo
        $query = $db->query("
            SELECT gender as valor, COUNT(*) as cantidad
            FROM calculated_results
            WHERE battery_service_id = ? AND gender IS NOT NULL
            GROUP BY gender
        ", [$batteryServiceId]);
        $data['sexo'] = $query->getResultArray();

        // Rango de edad
        $query = $db->query("
            SELECT
                CASE
                    WHEN age < 25 THEN 'Menor de 25'
                    WHEN age BETWEEN 25 AND 34 THEN 'Entre 25 y 34,9'
                    WHEN age BETWEEN 35 AND 44 THEN 'Entre 35 y 44,9'
                    WHEN age BETWEEN 45 AND 54 THEN 'Entre 45 y 54,9'
                    ELSE 'Mayor de 55'
                END as valor,
                COUNT(*) as cantidad
            FROM calculated_results
            WHERE battery_service_id = ? AND age IS NOT NULL
            GROUP BY valor
            ORDER BY MIN(age)
        ", [$batteryServiceId]);
        $data['edad'] = $query->getResultArray();

        // Lugar de residencia
        $query = $db->query("
            SELECT city_residence as valor, COUNT(*) as cantidad
            FROM calculated_results
            WHERE battery_service_id = ? AND city_residence IS NOT NULL
            GROUP BY city_residence
        ", [$batteryServiceId]);
        $data['residencia'] = $query->getResultArray();

        // Estado civil
        $query = $db->query("
            SELECT marital_status as valor, COUNT(*) as cantidad
            FROM calculated_results
            WHERE battery_service_id = ? AND marital_status IS NOT NULL
            GROUP BY marital_status
        ", [$batteryServiceId]);
        $data['estado_civil'] = $query->getResultArray();

        // Nivel de escolaridad
        $query = $db->query("
            SELECT education_level as valor, COUNT(*) as cantidad
            FROM calculated_results
            WHERE battery_service_id = ? AND education_level IS NOT NULL
            GROUP BY education_level
        ", [$batteryServiceId]);
        $data['escolaridad'] = $query->getResultArray();

        // Estrato
        $query = $db->query("
            SELECT stratum as valor, COUNT(*) as cantidad
            FROM calculated_results
            WHERE battery_service_id = ? AND stratum IS NOT NULL
            GROUP BY stratum
            ORDER BY stratum
        ", [$batteryServiceId]);
        $data['estrato'] = $query->getResultArray();

        // Tipo de vivienda
        $query = $db->query("
            SELECT housing_type as valor, COUNT(*) as cantidad
            FROM calculated_results
            WHERE battery_service_id = ? AND housing_type IS NOT NULL
            GROUP BY housing_type
        ", [$batteryServiceId]);
        $data['vivienda'] = $query->getResultArray();

        // Personas a cargo - verificar si existe la columna
        $query = $db->query("SHOW COLUMNS FROM calculated_results LIKE 'dependents'");
        if ($query->getNumRows() > 0) {
            $query = $db->query("
                SELECT
                    CASE
                        WHEN dependents = 0 THEN 'Ninguna'
                        WHEN dependents BETWEEN 1 AND 2 THEN '1 a 2'
                        WHEN dependents BETWEEN 3 AND 4 THEN '3 a 4'
                        ELSE '5 o más'
                    END as valor,
                    COUNT(*) as cantidad
                FROM calculated_results
                WHERE battery_service_id = ?
                GROUP BY valor
            ", [$batteryServiceId]);
            $data['dependientes'] = $query->getResultArray();
        } else {
            $data['dependientes'] = [];
        }

        return $data;
    }

    /**
     * Obtiene datos ocupacionales agregados
     */
    protected function getOccupationalData($batteryServiceId)
    {
        $db = \Config\Database::connect();

        $data = [];

        // Antigüedad
        $query = $db->query("
            SELECT
                CASE
                    WHEN work_experience_years < 1 THEN 'Menos de 1 año'
                    WHEN work_experience_years BETWEEN 1 AND 3 THEN '1 a 3 años'
                    WHEN work_experience_years BETWEEN 4 AND 5 THEN '4 a 5 años'
                    WHEN work_experience_years BETWEEN 6 AND 10 THEN '6 a 10 años'
                    ELSE 'Más de 10 años'
                END as valor,
                COUNT(*) as cantidad
            FROM calculated_results
            WHERE battery_service_id = ? AND work_experience_years IS NOT NULL
            GROUP BY valor
        ", [$batteryServiceId]);
        $data['antiguedad'] = $query->getResultArray();

        // Tipo de contrato
        $query = $db->query("
            SELECT contract_type as valor, COUNT(*) as cantidad
            FROM calculated_results
            WHERE battery_service_id = ? AND contract_type IS NOT NULL
            GROUP BY contract_type
        ", [$batteryServiceId]);
        $data['tipo_contrato'] = $query->getResultArray();

        // Tipo de cargo/posición
        $query = $db->query("
            SELECT position_type as valor, COUNT(*) as cantidad
            FROM calculated_results
            WHERE battery_service_id = ? AND position_type IS NOT NULL
            GROUP BY position_type
        ", [$batteryServiceId]);
        $data['tipo_cargo'] = $query->getResultArray();

        // Departamento/área
        $query = $db->query("
            SELECT department as valor, COUNT(*) as cantidad
            FROM calculated_results
            WHERE battery_service_id = ? AND department IS NOT NULL
            GROUP BY department
        ", [$batteryServiceId]);
        $data['departamento'] = $query->getResultArray();

        return $data;
    }

    /**
     * Obtiene resultados generales para conclusiones
     */
    protected function getResultadosGenerales($batteryServiceId)
    {
        $db = \Config\Database::connect();

        // Promedios por forma
        $query = $db->query("
            SELECT
                intralaboral_form_type,
                AVG(puntaje_total_general) as puntaje_general,
                AVG(intralaboral_total_puntaje) as puntaje_intralaboral,
                AVG(extralaboral_total_puntaje) as puntaje_extralaboral,
                AVG(estres_total_puntaje) as puntaje_estres
            FROM calculated_results
            WHERE battery_service_id = ?
            GROUP BY intralaboral_form_type
        ", [$batteryServiceId]);

        $resultados = [
            'forma_a' => null,
            'forma_b' => null,
        ];

        foreach ($query->getResultArray() as $row) {
            $key = $row['intralaboral_form_type'] === 'A' ? 'forma_a' : 'forma_b';
            $resultados[$key] = [
                'puntaje_general' => round($row['puntaje_general'], 2),
                'nivel_general' => $this->getRiskLevelFromScore($row['puntaje_general']),
                'puntaje_intralaboral' => round($row['puntaje_intralaboral'], 2),
                'puntaje_extralaboral' => round($row['puntaje_extralaboral'], 2),
                'puntaje_estres' => round($row['puntaje_estres'], 2),
                'nivel_estres' => $this->getRiskLevelFromScore($row['puntaje_estres']),
            ];
        }

        return $resultados;
    }

    /**
     * Formatea nivel de riesgo para mostrar
     */
    protected function formatRiskLevel($level)
    {
        $labels = [
            'sin_riesgo' => 'Sin Riesgo',
            'riesgo_bajo' => 'Riesgo Bajo',
            'riesgo_medio' => 'Riesgo Medio',
            'riesgo_alto' => 'Riesgo Alto',
            'riesgo_muy_alto' => 'Riesgo Muy Alto',
        ];
        return $labels[$level] ?? ucwords(str_replace('_', ' ', $level));
    }
}
