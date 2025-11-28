<?php

namespace App\Services;

use App\Models\WorkerDemographicsModel;
use App\Models\BatteryServiceModel;
use App\Models\CompanyModel;

/**
 * Servicio independiente para generar interpretaciones IA de la Ficha de Datos Generales
 * Enfoque interdisciplinario: Psicología, Sociología, Trabajo Social + Estadística
 */
class DemographicsReportService
{
    protected $demographicsModel;
    protected $batteryModel;
    protected $companyModel;
    protected $openAIService;

    // Mapeos para traducir códigos a texto legible
    protected $genderLabels = [
        'masculino' => 'Masculino',
        'Masculino' => 'Masculino',
        'femenino' => 'Femenino',
        'Femenino' => 'Femenino',
        'otro' => 'Otro',
        'Otro' => 'Otro',
        'M' => 'Masculino',
        'F' => 'Femenino',
    ];

    protected $maritalStatusLabels = [
        'soltero' => 'Soltero(a)',
        'Soltero(a)' => 'Soltero(a)',
        'casado' => 'Casado(a)',
        'Casado(a)' => 'Casado(a)',
        'union_libre' => 'Unión libre',
        'Unión libre' => 'Unión libre',
        'Union libre' => 'Unión libre',
        'separado' => 'Separado(a)',
        'Separado(a)' => 'Separado(a)',
        'divorciado' => 'Divorciado(a)',
        'Divorciado(a)' => 'Divorciado(a)',
        'viudo' => 'Viudo(a)',
        'Viudo(a)' => 'Viudo(a)',
    ];

    protected $educationLabels = [
        'ninguno' => 'Ninguno',
        'Ninguno' => 'Ninguno',
        'primaria_incompleta' => 'Primaria incompleta',
        'Primaria_incompleta' => 'Primaria incompleta',
        'Primaria incompleta' => 'Primaria incompleta',
        'primaria_completa' => 'Primaria completa',
        'Primaria_completa' => 'Primaria completa',
        'Primaria completa' => 'Primaria completa',
        'bachillerato_incompleto' => 'Bachillerato incompleto',
        'Bachillerato_incompleto' => 'Bachillerato incompleto',
        'Bachillerato incompleto' => 'Bachillerato incompleto',
        'bachillerato_completo' => 'Bachillerato completo',
        'Bachillerato_completo' => 'Bachillerato completo',
        'Bachillerato completo' => 'Bachillerato completo',
        'tecnico_incompleto' => 'Técnico/Tecnólogo incompleto',
        'Tecnico_incompleto' => 'Técnico/Tecnólogo incompleto',
        'Técnico/Tecnólogo incompleto' => 'Técnico/Tecnólogo incompleto',
        'tecnico_completo' => 'Técnico/Tecnólogo completo',
        'Tecnico_completo' => 'Técnico/Tecnólogo completo',
        'Técnico/Tecnólogo completo' => 'Técnico/Tecnólogo completo',
        'profesional_incompleto' => 'Profesional incompleto',
        'Profesional_incompleto' => 'Profesional incompleto',
        'Profesional incompleto' => 'Profesional incompleto',
        'profesional_completo' => 'Profesional completo',
        'Profesional_completo' => 'Profesional completo',
        'Profesional completo' => 'Profesional completo',
        'carrera_militar' => 'Carrera militar/Policía',
        'Carrera_militar' => 'Carrera militar/Policía',
        'Carrera militar/Policía' => 'Carrera militar/Policía',
        'posgrado_incompleto' => 'Postgrado incompleto',
        'Posgrado_incompleto' => 'Postgrado incompleto',
        'Postgrado incompleto' => 'Postgrado incompleto',
        'posgrado_completo' => 'Postgrado completo',
        'Posgrado_completo' => 'Postgrado completo',
        'Postgrado completo' => 'Postgrado completo',
    ];

    protected $housingLabels = [
        'propia' => 'Propia',
        'Propia' => 'Propia',
        'arrendada' => 'Arrendada',
        'Arrendada' => 'Arrendada',
        'familiar' => 'Familiar',
        'Familiar' => 'Familiar',
    ];

    protected $contractLabels = [
        'indefinido' => 'Término indefinido',
        'Indefinido' => 'Término indefinido',
        'Término indefinido' => 'Término indefinido',
        'fijo' => 'Término fijo',
        'Fijo' => 'Término fijo',
        'Término fijo' => 'Término fijo',
        'obra_labor' => 'Obra o labor',
        'Obra_labor' => 'Obra o labor',
        'Obra o labor' => 'Obra o labor',
        'prestacion_servicios' => 'Prestación de servicios',
        'Prestacion_servicios' => 'Prestación de servicios',
        'Prestación de servicios' => 'Prestación de servicios',
        'temporal' => 'Temporal',
        'Temporal' => 'Temporal',
        'aprendizaje' => 'Contrato de aprendizaje',
        'Aprendizaje' => 'Contrato de aprendizaje',
        'Contrato de aprendizaje' => 'Contrato de aprendizaje',
        'otro' => 'Otro tipo de contrato',
        'Otro' => 'Otro tipo de contrato',
        'Otro tipo de contrato' => 'Otro tipo de contrato',
    ];

    protected $positionTypeLabels = [
        'jefatura' => 'Jefatura - tiene personal a cargo',
        'Jefatura' => 'Jefatura - tiene personal a cargo',
        'Jefatura - tiene personal a cargo' => 'Jefatura - tiene personal a cargo',
        'profesional' => 'Profesional, analista, técnico, tecnólogo',
        'Profesional' => 'Profesional, analista, técnico, tecnólogo',
        'Profesional, analista, técnico, tecnólogo' => 'Profesional, analista, técnico, tecnólogo',
        'auxiliar' => 'Auxiliar, asistente administrativo, asistente técnico',
        'Auxiliar' => 'Auxiliar, asistente administrativo, asistente técnico',
        'Auxiliar, asistente administrativo, asistente técnico' => 'Auxiliar, asistente administrativo, asistente técnico',
        'operativo' => 'Operario, operador, ayudante, servicios generales',
        'Operativo' => 'Operario, operador, ayudante, servicios generales',
        'Operario, operador, ayudante, servicios generales' => 'Operario, operador, ayudante, servicios generales',
    ];

    protected $salaryLabels = [
        'menos_1_smlv' => 'Menos de 1 SMLV',
        'Menos_1_smlv' => 'Menos de 1 SMLV',
        'Menos de 1 SMLV' => 'Menos de 1 SMLV',
        '1_smlv' => '1 SMLV',
        '1 SMLV' => '1 SMLV',
        '1_2_smlv' => 'Entre 1 y 2 SMLV',
        'Entre 1 y 2 SMLV' => 'Entre 1 y 2 SMLV',
        '2_4_smlv' => 'Entre 2 y 4 SMLV',
        'Entre 2 y 4 SMLV' => 'Entre 2 y 4 SMLV',
        '4_mas_smlv' => '4 o más SMLV',
        '4 o más SMLV' => '4 o más SMLV',
    ];

    public function __construct()
    {
        $this->demographicsModel = new WorkerDemographicsModel();
        $this->batteryModel = new BatteryServiceModel();
        $this->companyModel = new CompanyModel();
        $this->openAIService = new OpenAIService();
    }

    /**
     * Agregar datos demográficos para un servicio de batería
     * Solo incluye trabajadores con estado 'completado'
     */
    public function aggregateDemographics(int $serviceId): array
    {
        log_message('debug', "=== DEMOGRAPHICS DEBUG START ===");
        log_message('debug', "Service ID: {$serviceId}");

        $demographics = $this->demographicsModel
            ->select('worker_demographics.*')
            ->join('workers', 'workers.id = worker_demographics.worker_id')
            ->where('workers.battery_service_id', $serviceId)
            ->where('workers.status', 'completado')
            ->findAll();

        log_message('debug', "Demographics query result count: " . count($demographics));

        if (!empty($demographics)) {
            log_message('debug', "First record keys: " . implode(', ', array_keys($demographics[0])));
            log_message('debug', "First record gender: " . ($demographics[0]['gender'] ?? 'NULL'));
            log_message('debug', "First record marital_status: " . ($demographics[0]['marital_status'] ?? 'NULL'));
        }

        if (empty($demographics)) {
            log_message('error', "No demographics found for service {$serviceId}");
            return ['error' => 'No hay datos demográficos disponibles para este servicio'];
        }

        $total = count($demographics);
        log_message('debug', "Total workers with demographics: {$total}");

        // Agregaciones estadísticas
        $genderResult = $this->aggregateField($demographics, 'gender', $this->genderLabels);
        log_message('debug', "Gender aggregation result: " . json_encode($genderResult));

        $maritalResult = $this->aggregateField($demographics, 'marital_status', $this->maritalStatusLabels);
        log_message('debug', "Marital status aggregation result: " . json_encode($maritalResult));

        $educationResult = $this->aggregateField($demographics, 'education_level', $this->educationLabels);
        log_message('debug', "Education level aggregation result: " . json_encode($educationResult));

        $stratumResult = $this->aggregateStratum($demographics);
        log_message('debug', "Stratum aggregation result: " . json_encode($stratumResult));

        $housingResult = $this->aggregateField($demographics, 'housing_type', $this->housingLabels);
        log_message('debug', "Housing type aggregation result: " . json_encode($housingResult));

        $aggregated = [
            'total_workers' => $total,
            'gender' => $genderResult,
            'age_groups' => $this->aggregateAgeGroups($demographics),
            'marital_status' => $maritalResult,
            'education_level' => $educationResult,
            'stratum' => $stratumResult,
            'housing_type' => $housingResult,
            'dependents' => $this->aggregateDependents($demographics),
            'department_residence' => $this->aggregateTopValues($demographics, 'department_residence', 10),
            'city_residence' => $this->aggregateTopValues($demographics, 'city_residence', 10),
            'department_work' => $this->aggregateTopValues($demographics, 'department_work', 10),
            'city_work' => $this->aggregateTopValues($demographics, 'city_work', 10),
            'time_in_company' => $this->aggregateTimeInCompany($demographics),
            'time_in_position' => $this->aggregateTimeInPosition($demographics),
            'contract_type' => $this->aggregateField($demographics, 'contract_type', $this->contractLabels),
            'position_type' => $this->aggregateField($demographics, 'position_type', $this->positionTypeLabels),
            'department_area' => $this->aggregateTopValues($demographics, 'department', 15),
            'hours_per_day' => $this->aggregateHoursPerDay($demographics),
            'salary_type' => $this->aggregateField($demographics, 'salary_type', $this->salaryLabels),
        ];

        log_message('debug', "=== DEMOGRAPHICS DEBUG END ===");
        return $aggregated;
    }

    /**
     * Agregar un campo simple con etiquetas
     */
    private function aggregateField(array $data, string $field, array $labels): array
    {
        $counts = [];
        $total = count($data);

        foreach ($data as $row) {
            $value = $row[$field] ?? 'no_responde';
            if (empty($value)) $value = 'no_responde';

            $label = $labels[$value] ?? ucfirst(str_replace('_', ' ', $value));

            if (!isset($counts[$label])) {
                $counts[$label] = 0;
            }
            $counts[$label]++;
        }

        // Calcular porcentajes y ordenar
        $result = [];
        arsort($counts);
        foreach ($counts as $label => $count) {
            $result[] = [
                'label' => $label,
                'count' => $count,
                'percentage' => round(($count / $total) * 100, 1),
            ];
        }

        return $result;
    }

    /**
     * Agregar grupos de edad basados en birth_year
     */
    private function aggregateAgeGroups(array $data): array
    {
        $currentYear = (int)date('Y');
        $groups = [
            '18-25 años' => 0,
            '26-35 años' => 0,
            '36-45 años' => 0,
            '46-55 años' => 0,
            '56+ años' => 0,
            'No reporta' => 0,
        ];

        $ages = [];
        $total = count($data);

        foreach ($data as $row) {
            $birthYear = $row['birth_year'] ?? null;
            if (!$birthYear || $birthYear < 1940 || $birthYear > $currentYear - 18) {
                $groups['No reporta']++;
                continue;
            }

            $age = $currentYear - (int)$birthYear;
            $ages[] = $age;

            if ($age >= 18 && $age <= 25) {
                $groups['18-25 años']++;
            } elseif ($age >= 26 && $age <= 35) {
                $groups['26-35 años']++;
            } elseif ($age >= 36 && $age <= 45) {
                $groups['36-45 años']++;
            } elseif ($age >= 46 && $age <= 55) {
                $groups['46-55 años']++;
            } else {
                $groups['56+ años']++;
            }
        }

        // Estadísticas descriptivas de edad
        $ageStats = [];
        if (!empty($ages)) {
            sort($ages);
            $ageStats = [
                'min' => min($ages),
                'max' => max($ages),
                'mean' => round(array_sum($ages) / count($ages), 1),
                'median' => $this->calculateMedian($ages),
            ];
        }

        $result = [];
        foreach ($groups as $label => $count) {
            if ($count > 0) {
                $result[] = [
                    'label' => $label,
                    'count' => $count,
                    'percentage' => round(($count / $total) * 100, 1),
                ];
            }
        }

        return [
            'distribution' => $result,
            'statistics' => $ageStats,
        ];
    }

    /**
     * Agregar estratos socioeconómicos
     */
    private function aggregateStratum(array $data): array
    {
        $stratumLabels = [
            '1' => 'Estrato 1',
            'Estrato 1' => 'Estrato 1',
            '2' => 'Estrato 2',
            'Estrato 2' => 'Estrato 2',
            '3' => 'Estrato 3',
            'Estrato 3' => 'Estrato 3',
            '4' => 'Estrato 4',
            'Estrato 4' => 'Estrato 4',
            '5' => 'Estrato 5',
            'Estrato 5' => 'Estrato 5',
            '6' => 'Estrato 6',
            'Estrato 6' => 'Estrato 6',
            '7' => 'Sin estrato definido',
            'finca' => 'Finca',
            'Finca' => 'Finca',
            'no_sabe' => 'No sabe/No responde',
            'No sabe' => 'No sabe/No responde',
            'No_sabe' => 'No sabe/No responde',
        ];

        return $this->aggregateField($data, 'stratum', $stratumLabels);
    }

    /**
     * Agregar personas a cargo
     */
    private function aggregateDependents(array $data): array
    {
        $groups = [
            'Ninguna' => 0,
            '1-2 personas' => 0,
            '3-4 personas' => 0,
            '5+ personas' => 0,
            'No reporta' => 0,
        ];
        $total = count($data);
        $dependentValues = [];

        foreach ($data as $row) {
            $dependents = $row['dependents'] ?? null;

            if ($dependents === null || $dependents === '') {
                $groups['No reporta']++;
                continue;
            }

            $dep = (int)$dependents;
            $dependentValues[] = $dep;

            if ($dep === 0) {
                $groups['Ninguna']++;
            } elseif ($dep <= 2) {
                $groups['1-2 personas']++;
            } elseif ($dep <= 4) {
                $groups['3-4 personas']++;
            } else {
                $groups['5+ personas']++;
            }
        }

        $result = [];
        foreach ($groups as $label => $count) {
            if ($count > 0) {
                $result[] = [
                    'label' => $label,
                    'count' => $count,
                    'percentage' => round(($count / $total) * 100, 1),
                ];
            }
        }

        // Estadísticas
        $stats = [];
        if (!empty($dependentValues)) {
            $stats = [
                'mean' => round(array_sum($dependentValues) / count($dependentValues), 1),
                'max' => max($dependentValues),
            ];
        }

        return [
            'distribution' => $result,
            'statistics' => $stats,
        ];
    }

    /**
     * Agregar tiempo en la empresa
     */
    private function aggregateTimeInCompany(array $data): array
    {
        $groups = [
            'Menos de 1 año' => 0,
            '1-5 años' => 0,
            '6-10 años' => 0,
            '11-15 años' => 0,
            '16-20 años' => 0,
            'Más de 20 años' => 0,
            'No reporta' => 0,
        ];
        $total = count($data);

        foreach ($data as $row) {
            $years = $row['time_in_company_years'] ?? null;

            if ($years === null || $years === '') {
                $groups['No reporta']++;
                continue;
            }

            $y = (float)$years;

            if ($y < 1) {
                $groups['Menos de 1 año']++;
            } elseif ($y <= 5) {
                $groups['1-5 años']++;
            } elseif ($y <= 10) {
                $groups['6-10 años']++;
            } elseif ($y <= 15) {
                $groups['11-15 años']++;
            } elseif ($y <= 20) {
                $groups['16-20 años']++;
            } else {
                $groups['Más de 20 años']++;
            }
        }

        $result = [];
        foreach ($groups as $label => $count) {
            if ($count > 0) {
                $result[] = [
                    'label' => $label,
                    'count' => $count,
                    'percentage' => round(($count / $total) * 100, 1),
                ];
            }
        }

        return $result;
    }

    /**
     * Agregar tiempo en el cargo
     */
    private function aggregateTimeInPosition(array $data): array
    {
        $groups = [
            'Menos de 1 año' => 0,
            '1-3 años' => 0,
            '4-6 años' => 0,
            '7-10 años' => 0,
            'Más de 10 años' => 0,
            'No reporta' => 0,
        ];
        $total = count($data);

        foreach ($data as $row) {
            $years = $row['time_in_position_years'] ?? null;

            if ($years === null || $years === '') {
                $groups['No reporta']++;
                continue;
            }

            $y = (float)$years;

            if ($y < 1) {
                $groups['Menos de 1 año']++;
            } elseif ($y <= 3) {
                $groups['1-3 años']++;
            } elseif ($y <= 6) {
                $groups['4-6 años']++;
            } elseif ($y <= 10) {
                $groups['7-10 años']++;
            } else {
                $groups['Más de 10 años']++;
            }
        }

        $result = [];
        foreach ($groups as $label => $count) {
            if ($count > 0) {
                $result[] = [
                    'label' => $label,
                    'count' => $count,
                    'percentage' => round(($count / $total) * 100, 1),
                ];
            }
        }

        return $result;
    }

    /**
     * Agregar horas de trabajo diarias
     */
    private function aggregateHoursPerDay(array $data): array
    {
        $groups = [
            '8 horas o menos' => 0,
            '9-10 horas' => 0,
            '11-12 horas' => 0,
            'Más de 12 horas' => 0,
            'No reporta' => 0,
        ];
        $total = count($data);
        $hoursValues = [];

        foreach ($data as $row) {
            $hours = $row['hours_per_day'] ?? null;

            if ($hours === null || $hours === '') {
                $groups['No reporta']++;
                continue;
            }

            $h = (int)$hours;
            $hoursValues[] = $h;

            if ($h <= 8) {
                $groups['8 horas o menos']++;
            } elseif ($h <= 10) {
                $groups['9-10 horas']++;
            } elseif ($h <= 12) {
                $groups['11-12 horas']++;
            } else {
                $groups['Más de 12 horas']++;
            }
        }

        $result = [];
        foreach ($groups as $label => $count) {
            if ($count > 0) {
                $result[] = [
                    'label' => $label,
                    'count' => $count,
                    'percentage' => round(($count / $total) * 100, 1),
                ];
            }
        }

        // Estadísticas
        $stats = [];
        if (!empty($hoursValues)) {
            $stats = [
                'mean' => round(array_sum($hoursValues) / count($hoursValues), 1),
            ];
        }

        return [
            'distribution' => $result,
            'statistics' => $stats,
        ];
    }

    /**
     * Agregar los valores más frecuentes de un campo
     */
    private function aggregateTopValues(array $data, string $field, int $limit = 10): array
    {
        $counts = [];
        $total = count($data);

        foreach ($data as $row) {
            $value = $row[$field] ?? 'No reporta';
            if (empty($value)) $value = 'No reporta';

            if (!isset($counts[$value])) {
                $counts[$value] = 0;
            }
            $counts[$value]++;
        }

        arsort($counts);
        $topValues = array_slice($counts, 0, $limit, true);

        $result = [];
        foreach ($topValues as $label => $count) {
            $result[] = [
                'label' => $label,
                'count' => $count,
                'percentage' => round(($count / $total) * 100, 1),
            ];
        }

        return $result;
    }

    /**
     * Calcular la mediana de un array
     */
    private function calculateMedian(array $values): float
    {
        $count = count($values);
        if ($count === 0) return 0;

        sort($values);
        $middle = floor($count / 2);

        if ($count % 2 === 0) {
            return ($values[$middle - 1] + $values[$middle]) / 2;
        }

        return $values[$middle];
    }

    /**
     * Generar interpretación narrativa con IA
     */
    public function generateInterpretation(int $serviceId): ?array
    {
        // Obtener datos del servicio y empresa
        $service = $this->batteryModel
            ->select('battery_services.*, companies.name as company_name')
            ->join('companies', 'companies.id = battery_services.company_id')
            ->find($serviceId);

        if (!$service) {
            return ['error' => 'Servicio no encontrado'];
        }

        // Agregar datos demográficos
        $aggregated = $this->aggregateDemographics($serviceId);

        if (isset($aggregated['error'])) {
            return $aggregated;
        }

        // Verificar configuración de OpenAI
        if (!$this->openAIService->isConfigured()) {
            return ['error' => 'OpenAI no está configurado. Configure OPENAI_API_KEY en el archivo .env'];
        }

        // Construir prompt interdisciplinario
        $prompt = $this->buildInterdisciplinaryPrompt($service, $aggregated);

        // Llamar a OpenAI
        $interpretation = $this->callOpenAI($prompt);

        if (!$interpretation) {
            return ['error' => 'Error al generar la interpretación con IA'];
        }

        return [
            'success' => true,
            'company_name' => $service['company_name'],
            'total_workers' => $aggregated['total_workers'],
            'aggregated_data' => $aggregated,
            'interpretation' => $interpretation,
            'generated_at' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Construir el prompt para interpretaciones por variable
     * Genera interpretaciones cortas estilo informe para cada variable demográfica
     */
    private function buildInterdisciplinaryPrompt(array $service, array $data): string
    {
        $companyName = $service['company_name'];
        $total = $data['total_workers'];

        // Formatear los datos para el prompt
        $genderText = $this->formatDistribution($data['gender']);
        $ageText = $this->formatAgeData($data['age_groups']);
        $maritalText = $this->formatDistribution($data['marital_status']);
        $educationText = $this->formatDistribution($data['education_level']);
        $stratumText = $this->formatDistribution($data['stratum']);
        $housingText = $this->formatDistribution($data['housing_type']);
        $dependentsText = $this->formatDependentsData($data['dependents']);
        $residenceText = $this->formatDistribution($data['city_residence']);
        $timeCompanyText = $this->formatDistribution($data['time_in_company']);
        $timePositionText = $this->formatDistribution($data['time_in_position']);
        $contractText = $this->formatDistribution($data['contract_type']);
        $positionText = $this->formatDistribution($data['position_type']);
        $departmentText = $this->formatDistribution($data['department_area']);
        $hoursText = $this->formatHoursData($data['hours_per_day']);
        $salaryText = $this->formatDistribution($data['salary_type']);

        $prompt = "Eres un experto en riesgo psicosocial laboral con conocimientos en psicología organizacional, sociología del trabajo, trabajo social y estadística aplicada.

Tu tarea es generar INTERPRETACIONES NARRATIVAS DETALLADAS para cada variable sociodemográfica de la Ficha de Datos Generales, en el marco de la Batería de Riesgo Psicosocial del Ministerio de Trabajo de Colombia (Resolución 2646 de 2008).

DATOS DE LA EMPRESA:
- Empresa: {$companyName}
- Total de trabajadores evaluados: {$total}

DATOS SOCIODEMOGRÁFICOS AGREGADOS:

1. SEXO:
{$genderText}

2. RANGO DE EDAD:
{$ageText}

3. ESTADO CIVIL:
{$maritalText}

4. NIVEL MÁXIMO DE ESCOLARIDAD:
{$educationText}

5. ESTRATO SOCIOECONÓMICO:
{$stratumText}

6. TIPO DE VIVIENDA:
{$housingText}

7. PERSONAS A CARGO:
{$dependentsText}

8. LUGAR DE RESIDENCIA:
{$residenceText}

9. ANTIGÜEDAD EN LA EMPRESA:
{$timeCompanyText}

10. ANTIGÜEDAD EN EL CARGO:
{$timePositionText}

11. TIPO DE CONTRATO:
{$contractText}

12. TIPO DE CARGO:
{$positionText}

13. ÁREA/DEPARTAMENTO:
{$departmentText}

14. HORAS DE TRABAJO DIARIAS:
{$hoursText}

15. RANGO SALARIAL:
{$salaryText}

INSTRUCCIONES DE FORMATO:

Genera una interpretación narrativa DETALLADA para CADA variable. Cada interpretación debe:
- Tener entre 2 y 4 oraciones
- Mencionar TODOS los porcentajes de las categorías presentes
- Incluir una reflexión sobre las implicaciones para el riesgo psicosocial
- Ser coherente con el tipo de actividad económica de la empresa

FORMATO DE RESPUESTA (usar exactamente estos títulos):

**SEXO:**
[Ejemplo: \"El X% de los empleados son mujeres y el X% restante corresponde a hombres, siendo acorde a la actividad económica de la organización. Esta distribución sugiere que...\"]

**RANGO DE EDAD:**
[Ejemplo: \"La composición por edades en los colaboradores demuestra que la mayoría de los trabajadores (X%) se encuentra entre los X y X años. Por otro lado, un X% tiene más de X años, mientras que otro X% pertenece al rango de X a X años. Esta distribución etaria implica...\"]

**ESTADO CIVIL:**
[Ejemplo: \"El estado civil de los trabajadores se observa que la mayoría de los colaboradores, un X% son solteros y otro X% está casado, mientras que un X% se encuentra en unión libre. Esta configuración familiar sugiere...\"]

**NIVEL EDUCATIVO:**
[Ejemplo: \"La mayoría de los colaboradores cuenta con formación profesional completa (X%) o técnico/tecnológica completa (X%), mientras que un X% posee bachillerato completo. Este perfil educativo indica...\"]

**ESTRATO:**
[Ejemplo: \"En cuanto al estrato socioeconómico, se observa que un X% de los trabajadores pertenece al estrato X, seguido de un X% en estrato X. Esta distribución socioeconómica refleja...\"]

**VIVIENDA:**
[Ejemplo: \"Respecto al tipo de vivienda, el X% de los colaboradores reside en vivienda propia, mientras que un X% vive en arriendo y un X% en vivienda familiar. Esta situación habitacional sugiere...\"]

**PERSONAS A CARGO:**
[Ejemplo: \"En relación con las personas a cargo, el X% de los trabajadores tiene entre X y X personas dependientes económicamente, mientras que un X% no tiene personas a cargo. El promedio de dependientes es de X personas, lo cual implica...\"]

**LUGAR DE RESIDENCIA:**
[Ejemplo: \"Todos los colaboradores residen en X, lo que facilita la logística de desplazamiento al lugar de trabajo y...\"]

**ANTIGÜEDAD EN LA EMPRESA:**
[Ejemplo: \"En cuanto a la antigüedad en la empresa, el X% de los trabajadores tiene menos de X año, mientras que un X% lleva entre X y X años. Esta distribución indica...\"]

**ANTIGÜEDAD EN EL CARGO:**
[Ejemplo: \"Respecto a la antigüedad en el cargo actual, el X% de los colaboradores tiene menos de X año en su posición, mientras que un X% lleva entre X y X años. Esto sugiere...\"]

**TIPO DE CONTRATO:**
[Ejemplo: \"La modalidad contractual predominante es el contrato a término indefinido con un X% de los trabajadores, seguido del contrato a término fijo con un X%. Esta estabilidad contractual implica...\"]

**TIPO DE CARGO:**
[Ejemplo: \"En la distribución por tipo de cargo, el X% corresponde a cargos profesionales/analistas, un X% a cargos operativos y un X% a cargos de jefatura con personal a cargo. Esta estructura organizacional refleja...\"]

**ÁREA/DEPARTAMENTO:**
[Ejemplo: \"La distribución por áreas muestra que el X% de los trabajadores pertenece al área de X, seguido del área de X con un X%. Esta concentración por áreas sugiere...\"]

**HORAS DE TRABAJO:**
[Ejemplo: \"El X% de los colaboradores trabaja X horas diarias o menos, mientras que un X% reporta jornadas de X a X horas. El promedio de horas diarias es de X horas, lo cual...\"]

**RANGO SALARIAL:**
[Ejemplo: \"En cuanto a la remuneración, el X% de los trabajadores percibe entre X y X SMLV, mientras que un X% gana entre X y X SMLV. Esta distribución salarial indica...\"]

**SÍNTESIS GENERAL:**
[IMPORTANTE: Esta sección debe ser EXTENSA, con un mínimo de 10-12 oraciones organizadas en 3-4 párrafos. Debe integrar TODAS las variables sociodemográficas analizadas en un análisis comprehensivo.

ESTRUCTURA REQUERIDA:

PÁRRAFO 1 - PERFIL DEMOGRÁFICO BÁSICO: Integrar sexo, edad, estado civil y nivel educativo. Describir el perfil típico del trabajador de la empresa mencionando los porcentajes predominantes de cada variable y sus implicaciones desde la psicología organizacional.

PÁRRAFO 2 - CONDICIONES SOCIOECONÓMICAS: Analizar estrato socioeconómico, tipo de vivienda, personas a cargo y lugar de residencia. Reflexionar sobre las condiciones de vida de los trabajadores y cómo estas pueden influir en su bienestar laboral y vulnerabilidad al riesgo psicosocial desde una perspectiva de trabajo social.

PÁRRAFO 3 - PERFIL LABORAL Y ORGANIZACIONAL: Sintetizar antigüedad en empresa/cargo, tipo de contrato, tipo de cargo, área/departamento, horas de trabajo y rango salarial. Analizar la estabilidad laboral, las condiciones de trabajo y las implicaciones para la satisfacción y el compromiso organizacional desde la sociología del trabajo.

PÁRRAFO 4 - CONCLUSIONES Y RECOMENDACIONES: Identificar los factores de riesgo psicosocial más relevantes derivados del perfil sociodemográfico. Proponer líneas generales de intervención considerando las características específicas de la población evaluada. Mencionar fortalezas y áreas de oportunidad detectadas.]

REGLAS IMPORTANTES:
- Cada interpretación individual debe tener entre 2 y 4 oraciones
- La SÍNTESIS GENERAL debe tener MÍNIMO 10-12 oraciones en 3-4 párrafos sustanciales
- Menciona TODOS los porcentajes exactos de los datos proporcionados
- Relaciona los datos con posibles implicaciones para el riesgo psicosocial
- Usa frases como: \"El X% de los colaboradores...\", \"La mayoría de los trabajadores (X%)...\", \"Se observa que...\", \"Esta distribución sugiere...\"
- NO uses bullet points ni listas, solo texto narrativo fluido en párrafos
- Escribe en tercera persona con tono técnico profesional
- NO menciones que eres IA ni que el texto fue generado automáticamente

Genera las interpretaciones completas:";

        return $prompt;
    }

    /**
     * Formatear distribución para el prompt
     */
    private function formatDistribution(array $distribution): string
    {
        $lines = [];
        foreach ($distribution as $item) {
            $lines[] = "- {$item['label']}: {$item['count']} ({$item['percentage']}%)";
        }
        return implode("\n", $lines);
    }

    /**
     * Formatear datos de edad para el prompt
     */
    private function formatAgeData(array $ageData): string
    {
        $text = $this->formatDistribution($ageData['distribution']);

        if (!empty($ageData['statistics'])) {
            $stats = $ageData['statistics'];
            $text .= "\nEstadísticas: Edad mínima {$stats['min']} años, máxima {$stats['max']} años, promedio {$stats['mean']} años, mediana {$stats['median']} años.";
        }

        return $text;
    }

    /**
     * Formatear datos de dependientes para el prompt
     */
    private function formatDependentsData(array $dependentsData): string
    {
        $text = $this->formatDistribution($dependentsData['distribution']);

        if (!empty($dependentsData['statistics'])) {
            $stats = $dependentsData['statistics'];
            $text .= "\nPromedio de personas a cargo: {$stats['mean']}, máximo reportado: {$stats['max']}.";
        }

        return $text;
    }

    /**
     * Formatear datos de horas para el prompt
     */
    private function formatHoursData(array $hoursData): string
    {
        $text = $this->formatDistribution($hoursData['distribution']);

        if (!empty($hoursData['statistics'])) {
            $stats = $hoursData['statistics'];
            $text .= "\nPromedio de horas diarias: {$stats['mean']}.";
        }

        return $text;
    }

    /**
     * Llamar a la API de OpenAI
     */
    private function callOpenAI(string $prompt): ?string
    {
        $apiKey = env('OPENAI_API_KEY', '');
        $model = env('OPENAI_MODEL', 'gpt-4o');

        if (empty($apiKey) || $apiKey === 'sk-tu-api-key-aqui') {
            log_message('error', 'OpenAI API Key no configurada para DemographicsReportService');
            return null;
        }

        $data = [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
            'temperature' => 0.7,
            'max_tokens' => 6000, // Tokens suficientes para interpretaciones detalladas + síntesis extensa
        ];

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ],
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_TIMEOUT => 120, // Timeout más largo para respuestas largas
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            log_message('error', 'DemographicsReportService OpenAI cURL Error: ' . $error);
            return null;
        }

        if ($httpCode !== 200) {
            log_message('error', 'DemographicsReportService OpenAI API Error: HTTP ' . $httpCode . ' - ' . $response);
            return null;
        }

        $result = json_decode($response, true);

        if (isset($result['choices'][0]['message']['content'])) {
            return trim($result['choices'][0]['message']['content']);
        }

        log_message('error', 'DemographicsReportService OpenAI unexpected response: ' . $response);
        return null;
    }

    /**
     * Verificar si el servicio está configurado
     */
    public function isConfigured(): bool
    {
        return $this->openAIService->isConfigured();
    }
}
