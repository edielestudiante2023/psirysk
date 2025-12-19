<?php

namespace App\Services;

use App\Models\ReportModel;
use App\Models\ReportSectionModel;
use App\Models\CalculatedResultModel;
use App\Models\BatteryServiceModel;
use App\Models\CompanyModel;

/**
 * Servicio para generar las secciones del informe con IA
 */
class ReportGeneratorService
{
    protected $reportModel;
    protected $sectionModel;
    protected $resultModel;
    protected $batteryModel;
    protected $companyModel;
    protected $openAIService;

    // Definiciones de dimensiones para los prompts
    protected $dimensionDefinitions = [
        // INTRALABORAL - Dominio Liderazgo y Relaciones Sociales
        'caracteristicas_liderazgo' => [
            'name' => 'Características del liderazgo',
            'definition' => 'Se refiere a los atributos de la gestión de los jefes inmediatos en relación con la planificación y asignación del trabajo, consecución de resultados, resolución de conflictos, participación, motivación, apoyo, interacción y comunicación con sus colaboradores.',
        ],
        'relaciones_sociales_trabajo' => [
            'name' => 'Relaciones sociales en el trabajo',
            'definition' => 'Son las interacciones que se establecen con otras personas en el trabajo, particularmente en lo referente a la posibilidad de establecer contacto con otros individuos, las características de las interacciones, el apoyo social y el trabajo en equipo.',
        ],
        'relaciones_sociales' => [
            'name' => 'Relaciones sociales en el trabajo',
            'definition' => 'Son las interacciones que se establecen con otras personas en el trabajo, particularmente en lo referente a la posibilidad de establecer contacto con otros individuos, las características de las interacciones, el apoyo social y el trabajo en equipo.',
        ],
        'retroalimentacion_desempeno' => [
            'name' => 'Retroalimentación del desempeño',
            'definition' => 'Describe la información que un trabajador recibe sobre la forma como realiza su trabajo, con el fin de indicar los aspectos a mejorar y los aspectos que fortalecen su labor.',
        ],
        'retroalimentacion' => [
            'name' => 'Retroalimentación del desempeño',
            'definition' => 'Describe la información que un trabajador recibe sobre la forma como realiza su trabajo, con el fin de indicar los aspectos a mejorar y los aspectos que fortalecen su labor.',
        ],
        'relacion_con_colaboradores' => [
            'name' => 'Relación con los colaboradores (subordinados)',
            'definition' => 'Trata de los atributos de la gestión de los subordinados en relación con la ejecución del trabajo, consecución de resultados, resolución de conflictos y participación. Solo aplica para trabajadores con personal a cargo.',
        ],
        'relacion_colaboradores' => [
            'name' => 'Relación con los colaboradores (subordinados)',
            'definition' => 'Trata de los atributos de la gestión de los subordinados en relación con la ejecución del trabajo, consecución de resultados, resolución de conflictos y participación. Solo aplica para trabajadores con personal a cargo.',
        ],
        'claridad_rol' => [
            'name' => 'Claridad de rol',
            'definition' => 'Es la definición y comunicación del papel que se espera que el trabajador desempeñe en la organización, específicamente en torno a los objetivos del trabajo, las funciones y resultados, el margen de autonomía y el impacto del ejercicio del cargo.',
        ],
        'capacitacion' => [
            'name' => 'Capacitación',
            'definition' => 'Se entiende por las actividades de inducción, entrenamiento y formación que la organización brinda al trabajador con el fin de desarrollar y fortalecer sus conocimientos y habilidades.',
        ],
        'participacion_manejo_cambio' => [
            'name' => 'Participación y manejo del cambio',
            'definition' => 'Se entiende como el conjunto de mecanismos organizacionales orientados a incrementar la capacidad de adaptación de los trabajadores a las diferentes transformaciones que se presentan en el contexto laboral.',
        ],
        'participacion_cambio' => [
            'name' => 'Participación y manejo del cambio',
            'definition' => 'Se entiende como el conjunto de mecanismos organizacionales orientados a incrementar la capacidad de adaptación de los trabajadores a las diferentes transformaciones que se presentan en el contexto laboral.',
        ],
        'oportunidades_desarrollo' => [
            'name' => 'Oportunidades para el uso y desarrollo de habilidades y conocimientos',
            'definition' => 'Se refiere a la posibilidad que el trabajo le brinda al individuo de aplicar, aprender y desarrollar sus habilidades y conocimientos.',
        ],
        'oportunidades' => [
            'name' => 'Oportunidades para el uso y desarrollo de habilidades y conocimientos',
            'definition' => 'Se refiere a la posibilidad que el trabajo le brinda al individuo de aplicar, aprender y desarrollar sus habilidades y conocimientos.',
        ],
        'control_autonomia_trabajo' => [
            'name' => 'Control y autonomía sobre el trabajo',
            'definition' => 'Se refiere al margen de decisión que tiene un individuo sobre aspectos como el orden de las actividades, la cantidad, el ritmo, la forma de trabajar, las pausas durante la jornada y los tiempos de descanso.',
        ],
        'control_autonomia' => [
            'name' => 'Control y autonomía sobre el trabajo',
            'definition' => 'Se refiere al margen de decisión que tiene un individuo sobre aspectos como el orden de las actividades, la cantidad, el ritmo, la forma de trabajar, las pausas durante la jornada y los tiempos de descanso.',
        ],
        'demandas_ambientales_esfuerzo_fisico' => [
            'name' => 'Demandas ambientales y de esfuerzo físico',
            'definition' => 'Hacen referencia a las condiciones del lugar de trabajo y a la carga física que involucran las actividades que se desarrollan, que bajo ciertas circunstancias exigen del individuo un esfuerzo de adaptación.',
        ],
        'demandas_ambientales' => [
            'name' => 'Demandas ambientales y de esfuerzo físico',
            'definition' => 'Hacen referencia a las condiciones del lugar de trabajo y a la carga física que involucran las actividades que se desarrollan, que bajo ciertas circunstancias exigen del individuo un esfuerzo de adaptación.',
        ],
        'demandas_emocionales' => [
            'name' => 'Demandas emocionales',
            'definition' => 'Situaciones afectivas y emocionales propias del contenido de la tarea que tienen el potencial de interferir con los sentimientos y emociones del trabajador.',
        ],
        'demandas_cuantitativas' => [
            'name' => 'Demandas cuantitativas',
            'definition' => 'Son las exigencias relativas a la cantidad de trabajo que se debe ejecutar, en relación con el tiempo disponible para hacerlo.',
        ],
        'influencia_trabajo_entorno_extralaboral' => [
            'name' => 'Influencia del trabajo sobre el entorno extralaboral',
            'definition' => 'Condición que se presenta cuando las exigencias de tiempo y esfuerzo que se hacen a un individuo en su trabajo impactan su vida extralaboral.',
        ],
        'influencia_extralaboral' => [
            'name' => 'Influencia del trabajo sobre el entorno extralaboral',
            'definition' => 'Condición que se presenta cuando las exigencias de tiempo y esfuerzo que se hacen a un individuo en su trabajo impactan su vida extralaboral.',
        ],
        'demandas_carga_mental' => [
            'name' => 'Demandas de carga mental',
            'definition' => 'Las exigencias de procesamiento cognitivo que implica la tarea y que involucran procesos mentales superiores de atención, memoria y análisis de información.',
        ],
        'carga_mental' => [
            'name' => 'Demandas de carga mental',
            'definition' => 'Las exigencias de procesamiento cognitivo que implica la tarea y que involucran procesos mentales superiores de atención, memoria y análisis de información.',
        ],
        'consistencia_rol' => [
            'name' => 'Consistencia del rol',
            'definition' => 'Se refiere a la compatibilidad o consistencia entre las diversas exigencias relacionadas con los principios de eficiencia, calidad técnica y ética, propios del servicio o producto.',
        ],
        'demandas_jornada_trabajo' => [
            'name' => 'Demandas de la jornada de trabajo',
            'definition' => 'Las exigencias del tiempo laboral que se hacen al individuo en términos de la duración y el horario de la jornada, así como de los periodos destinados a pausas y descansos periódicos.',
        ],
        'jornada_trabajo' => [
            'name' => 'Demandas de la jornada de trabajo',
            'definition' => 'Las exigencias del tiempo laboral que se hacen al individuo en términos de la duración y el horario de la jornada, así como de los periodos destinados a pausas y descansos periódicos.',
        ],
        'exigencias_responsabilidad_cargo' => [
            'name' => 'Exigencias de responsabilidad del cargo',
            'definition' => 'Las exigencias de responsabilidad directa en el trabajo hacen alusión al conjunto de obligaciones implícitas en el desempeño de un cargo, cuyos resultados no pueden ser transferidos a otras personas.',
        ],
        'exigencias_responsabilidad' => [
            'name' => 'Exigencias de responsabilidad del cargo',
            'definition' => 'Las exigencias de responsabilidad directa en el trabajo hacen alusión al conjunto de obligaciones implícitas en el desempeño de un cargo, cuyos resultados no pueden ser transferidos a otras personas.',
        ],
        'recompensas_pertenencia_estabilidad' => [
            'name' => 'Recompensas derivadas de la pertenencia a la organización y del trabajo que se realiza',
            'definition' => 'Se refieren al sentimiento de orgullo y a la percepción de estabilidad laboral que experimenta un individuo por estar vinculado a una organización, así como el sentimiento de autorrealización.',
        ],
        'recompensas_pertenencia' => [
            'name' => 'Recompensas derivadas de la pertenencia a la organización y del trabajo que se realiza',
            'definition' => 'Se refieren al sentimiento de orgullo y a la percepción de estabilidad laboral que experimenta un individuo por estar vinculado a una organización, así como el sentimiento de autorrealización.',
        ],
        'reconocimiento_compensacion' => [
            'name' => 'Reconocimiento y compensación',
            'definition' => 'Es el conjunto de retribuciones que la organización le otorga al trabajador en contraprestación al esfuerzo realizado en el trabajo.',
        ],
        'reconocimiento' => [
            'name' => 'Reconocimiento y compensación',
            'definition' => 'Es el conjunto de retribuciones que la organización le otorga al trabajador en contraprestación al esfuerzo realizado en el trabajo.',
        ],
        'tiempo_fuera_trabajo' => [
            'name' => 'Tiempo fuera del trabajo',
            'definition' => 'Se refiere al tiempo que el individuo dedica a actividades diferentes a las laborales.',
        ],
        'tiempo_fuera' => [
            'name' => 'Tiempo fuera del trabajo',
            'definition' => 'Se refiere al tiempo que el individuo dedica a actividades diferentes a las laborales.',
        ],
        'relaciones_familiares' => [
            'name' => 'Relaciones familiares',
            'definition' => 'Propiedades que caracterizan las interacciones del individuo con su núcleo familiar.',
        ],
        'comunicacion_relaciones' => [
            'name' => 'Comunicación y relaciones interpersonales',
            'definition' => 'Cualidades que caracterizan la comunicación e interacciones del individuo con sus allegados y amigos.',
        ],
        'comunicacion' => [
            'name' => 'Comunicación y relaciones interpersonales',
            'definition' => 'Cualidades que caracterizan la comunicación e interacciones del individuo con sus allegados y amigos.',
        ],
        'situacion_economica' => [
            'name' => 'Situación económica del grupo familiar',
            'definition' => 'Trata de la disponibilidad de medios económicos para que el trabajador y su grupo familiar atiendan sus gastos básicos.',
        ],
        'caracteristicas_vivienda' => [
            'name' => 'Características de la vivienda y de su entorno',
            'definition' => 'Se refiere a las condiciones de infraestructura, ubicación y entorno de las instalaciones físicas del lugar habitual de residencia.',
        ],
        'vivienda' => [
            'name' => 'Características de la vivienda y de su entorno',
            'definition' => 'Se refiere a las condiciones de infraestructura, ubicación y entorno de las instalaciones físicas del lugar habitual de residencia.',
        ],
        'influencia_entorno' => [
            'name' => 'Influencia del entorno extralaboral sobre el trabajo',
            'definition' => 'Corresponde al influjo de las exigencias de los roles familiares y personales en el bienestar y en la actividad laboral del trabajador.',
        ],
        'desplazamiento' => [
            'name' => 'Desplazamiento vivienda - trabajo - vivienda',
            'definition' => 'Son las condiciones en que se realiza el traslado del trabajador desde su sitio de vivienda hasta su lugar de trabajo y viceversa.',
        ],
        'dom_liderazgo' => [
            'name' => 'Dominio Liderazgo y relaciones sociales en el trabajo',
            'definition' => 'Alude a un tipo particular de relación social que se establece entre los superiores jerárquicos y sus colaboradores.',
        ],
        'dom_control' => [
            'name' => 'Dominio Control sobre el trabajo',
            'definition' => 'Posibilidad que el trabajo ofrece al individuo para influir y tomar decisiones sobre los diversos aspectos que intervienen en su realización.',
        ],
        'dom_demandas' => [
            'name' => 'Dominio Demandas del trabajo',
            'definition' => 'Se refieren a las exigencias que el trabajo impone al individuo.',
        ],
        'dom_recompensas' => [
            'name' => 'Dominio Recompensas',
            'definition' => 'Este término trata de la retribución que el trabajador obtiene a cambio de sus contribuciones o esfuerzos laborales.',
        ],
        'estres_total' => [
            'name' => 'Estrés',
            'definition' => 'Conjunto de reacciones fisiológicas y psicológicas que experimenta el organismo cuando se le somete a fuertes demandas.',
        ],
    ];

    public function __construct()
    {
        $this->reportModel = new ReportModel();
        $this->sectionModel = new ReportSectionModel();
        $this->resultModel = new CalculatedResultModel();
        $this->batteryModel = new BatteryServiceModel();
        $this->companyModel = new CompanyModel();
        $this->openAIService = new OpenAIService();
    }

    /**
     * Generar todas las secciones del informe (SIN llamar a OpenAI)
     * La generación de texto con IA se hace después, sección por sección
     */
    public function generateReportSections(int $reportId): array
    {
        $report = $this->reportModel->find($reportId);
        if (!$report) {
            return ['success' => false, 'message' => 'Informe no encontrado'];
        }

        $batteryServiceId = $report['battery_service_id'];

        // Obtener datos agregados por forma (SOLO A y B - no existe baremo "conjunto")
        // Resolución 2404/2019 Tabla 34: Los baremos son específicos por forma
        $resultsFormaA = $this->getAggregatedResults($batteryServiceId, 'A');
        $resultsFormaB = $this->getAggregatedResults($batteryServiceId, 'B');

        // Obtener información de la empresa
        $batteryService = $this->batteryModel->find($batteryServiceId);
        $company = $this->companyModel->find($batteryService['company_id']);

        $sectionsCreated = 0;
        $order = 1;

        // NOTA METODOLÓGICA: No se genera sección "executive conjunto" porque
        // la Resolución 2404/2019 (Tabla 34) NO define baremos para mezclar
        // trabajadores de Forma A y Forma B. Cada forma tiene su propio baremo.

        // 1. RESUMEN EJECUTIVO POR FORMA (A y B separados)
        // El resumen ejecutivo ahora se genera por forma, con baremos válidos
        foreach (['A', 'B'] as $formType) {
            $results = $formType === 'A' ? $resultsFormaA : $resultsFormaB;
            if (!empty($results)) {
                $sectionsCreated += $this->createSection($reportId, [
                    'section_level' => 'executive',
                    'questionnaire_type' => 'general',
                    'form_type' => $formType,
                    'score_value' => $results['puntaje_total_general'] ?? null,
                    'risk_level' => $results['puntaje_total_general_nivel'] ?? null,
                    'order_position' => $order++,
                ]);
            }
        }

        // 2. TOTALES GENERALES (solo A y B - sin "conjunto")
        foreach (['A', 'B'] as $formType) {
            $results = $formType === 'A' ? $resultsFormaA : $resultsFormaB;
            if (!empty($results)) {
                $sectionsCreated += $this->createSection($reportId, [
                    'section_level' => 'total',
                    'questionnaire_type' => 'general',
                    'form_type' => $formType,
                    'score_value' => $results['puntaje_total_general'] ?? null,
                    'risk_level' => $results['puntaje_total_general_nivel'] ?? null,
                    'order_position' => $order++,
                ]);
            }
        }

        // 3. TOTALES POR CUESTIONARIO
        $questionnaireFields = [
            'intralaboral' => ['puntaje' => 'intralaboral_total_puntaje', 'nivel' => 'intralaboral_total_nivel'],
            'extralaboral' => ['puntaje' => 'extralaboral_total_puntaje', 'nivel' => 'extralaboral_total_nivel'],
            'stress' => ['puntaje' => 'estres_total_puntaje', 'nivel' => 'estres_total_nivel'],
        ];

        foreach (['A', 'B'] as $formType) {
            $results = $formType === 'A' ? $resultsFormaA : $resultsFormaB;
            if (!empty($results)) {
                foreach ($questionnaireFields as $qType => $fields) {
                    $sectionsCreated += $this->createSection($reportId, [
                        'section_level' => 'questionnaire',
                        'questionnaire_type' => $qType,
                        'form_type' => $formType,
                        'score_value' => $results[$fields['puntaje']] ?? null,
                        'risk_level' => $results[$fields['nivel']] ?? null,
                        'order_position' => $order++,
                    ]);
                }
            }
        }

        // 4. DOMINIOS INTRALABORAL
        $dominioFields = [
            'liderazgo' => ['puntaje' => 'dom_liderazgo_puntaje', 'nivel' => 'dom_liderazgo_nivel'],
            'control' => ['puntaje' => 'dom_control_puntaje', 'nivel' => 'dom_control_nivel'],
            'demandas' => ['puntaje' => 'dom_demandas_puntaje', 'nivel' => 'dom_demandas_nivel'],
            'recompensas' => ['puntaje' => 'dom_recompensas_puntaje', 'nivel' => 'dom_recompensas_nivel'],
        ];

        foreach (['A', 'B'] as $formType) {
            $results = $formType === 'A' ? $resultsFormaA : $resultsFormaB;
            if (!empty($results)) {
                foreach ($dominioFields as $domCode => $fields) {
                    $sectionsCreated += $this->createSection($reportId, [
                        'section_level' => 'domain',
                        'questionnaire_type' => 'intralaboral',
                        'domain_code' => $domCode,
                        'form_type' => $formType,
                        'score_value' => $results[$fields['puntaje']] ?? null,
                        'risk_level' => $results[$fields['nivel']] ?? null,
                        'order_position' => $order++,
                    ]);
                }
            }
        }

        // 5. DIMENSIONES INTRALABORAL
        $dimIntraFields = [
            'caracteristicas_liderazgo' => ['p' => 'dim_caracteristicas_liderazgo_puntaje', 'n' => 'dim_caracteristicas_liderazgo_nivel', 'd' => 'liderazgo'],
            'relaciones_sociales' => ['p' => 'dim_relaciones_sociales_puntaje', 'n' => 'dim_relaciones_sociales_nivel', 'd' => 'liderazgo'],
            'retroalimentacion' => ['p' => 'dim_retroalimentacion_puntaje', 'n' => 'dim_retroalimentacion_nivel', 'd' => 'liderazgo'],
            'relacion_colaboradores' => ['p' => 'dim_relacion_colaboradores_puntaje', 'n' => 'dim_relacion_colaboradores_nivel', 'd' => 'liderazgo', 'only_a' => true], // Solo Forma A
            'claridad_rol' => ['p' => 'dim_claridad_rol_puntaje', 'n' => 'dim_claridad_rol_nivel', 'd' => 'control'],
            'capacitacion' => ['p' => 'dim_capacitacion_puntaje', 'n' => 'dim_capacitacion_nivel', 'd' => 'control'],
            'participacion_cambio' => ['p' => 'dim_participacion_manejo_cambio_puntaje', 'n' => 'dim_participacion_manejo_cambio_nivel', 'd' => 'control'],
            'oportunidades' => ['p' => 'dim_oportunidades_desarrollo_puntaje', 'n' => 'dim_oportunidades_desarrollo_nivel', 'd' => 'control'],
            'control_autonomia' => ['p' => 'dim_control_autonomia_puntaje', 'n' => 'dim_control_autonomia_nivel', 'd' => 'control'],
            'demandas_ambientales' => ['p' => 'dim_demandas_ambientales_puntaje', 'n' => 'dim_demandas_ambientales_nivel', 'd' => 'demandas'],
            'demandas_emocionales' => ['p' => 'dim_demandas_emocionales_puntaje', 'n' => 'dim_demandas_emocionales_nivel', 'd' => 'demandas'],
            'demandas_cuantitativas' => ['p' => 'dim_demandas_cuantitativas_puntaje', 'n' => 'dim_demandas_cuantitativas_nivel', 'd' => 'demandas'],
            'influencia_extralaboral' => ['p' => 'dim_influencia_trabajo_entorno_extralaboral_puntaje', 'n' => 'dim_influencia_trabajo_entorno_extralaboral_nivel', 'd' => 'demandas'],
            'carga_mental' => ['p' => 'dim_demandas_carga_mental_puntaje', 'n' => 'dim_demandas_carga_mental_nivel', 'd' => 'demandas'],
            'exigencias_responsabilidad' => ['p' => 'dim_demandas_responsabilidad_puntaje', 'n' => 'dim_demandas_responsabilidad_nivel', 'd' => 'demandas', 'only_a' => true], // Solo Forma A
            'consistencia_rol' => ['p' => 'dim_consistencia_rol_puntaje', 'n' => 'dim_consistencia_rol_nivel', 'd' => 'demandas', 'only_a' => true], // Solo Forma A
            'jornada_trabajo' => ['p' => 'dim_demandas_jornada_trabajo_puntaje', 'n' => 'dim_demandas_jornada_trabajo_nivel', 'd' => 'demandas'],
            'recompensas_pertenencia' => ['p' => 'dim_recompensas_pertenencia_puntaje', 'n' => 'dim_recompensas_pertenencia_nivel', 'd' => 'recompensas'],
            'reconocimiento' => ['p' => 'dim_reconocimiento_compensacion_puntaje', 'n' => 'dim_reconocimiento_compensacion_nivel', 'd' => 'recompensas'],
        ];

        foreach (['A', 'B'] as $formType) {
            $results = $formType === 'A' ? $resultsFormaA : $resultsFormaB;
            if (!empty($results)) {
                foreach ($dimIntraFields as $dimCode => $fields) {
                    // Saltar dimensiones exclusivas de Forma A cuando se procesa Forma B
                    if ($formType === 'B' && !empty($fields['only_a'])) {
                        continue;
                    }
                    if (isset($results[$fields['p']]) && $results[$fields['p']] !== null) {
                        $sectionsCreated += $this->createSection($reportId, [
                            'section_level' => 'dimension',
                            'questionnaire_type' => 'intralaboral',
                            'domain_code' => $fields['d'],
                            'dimension_code' => $dimCode,
                            'form_type' => $formType,
                            'score_value' => $results[$fields['p']],
                            'risk_level' => $results[$fields['n']] ?? null,
                            'order_position' => $order++,
                        ]);
                    }
                }
            }
        }

        // 6. DIMENSIONES EXTRALABORAL
        $dimExtraFields = [
            'tiempo_fuera' => ['p' => 'extralaboral_tiempo_fuera_puntaje', 'n' => 'extralaboral_tiempo_fuera_nivel'],
            'relaciones_familiares' => ['p' => 'extralaboral_relaciones_familiares_puntaje', 'n' => 'extralaboral_relaciones_familiares_nivel'],
            'comunicacion' => ['p' => 'extralaboral_comunicacion_puntaje', 'n' => 'extralaboral_comunicacion_nivel'],
            'situacion_economica' => ['p' => 'extralaboral_situacion_economica_puntaje', 'n' => 'extralaboral_situacion_economica_nivel'],
            'vivienda' => ['p' => 'extralaboral_caracteristicas_vivienda_puntaje', 'n' => 'extralaboral_caracteristicas_vivienda_nivel'],
            'influencia_entorno' => ['p' => 'extralaboral_influencia_entorno_puntaje', 'n' => 'extralaboral_influencia_entorno_nivel'],
            'desplazamiento' => ['p' => 'extralaboral_desplazamiento_puntaje', 'n' => 'extralaboral_desplazamiento_nivel'],
        ];

        foreach (['A', 'B'] as $formType) {
            $results = $formType === 'A' ? $resultsFormaA : $resultsFormaB;
            if (!empty($results)) {
                foreach ($dimExtraFields as $dimCode => $fields) {
                    if (isset($results[$fields['p']]) && $results[$fields['p']] !== null) {
                        $sectionsCreated += $this->createSection($reportId, [
                            'section_level' => 'dimension',
                            'questionnaire_type' => 'extralaboral',
                            'dimension_code' => $dimCode,
                            'form_type' => $formType,
                            'score_value' => $results[$fields['p']],
                            'risk_level' => $results[$fields['n']] ?? null,
                            'order_position' => $order++,
                        ]);
                    }
                }
            }
        }

        return [
            'success' => true,
            'sections_created' => $sectionsCreated,
            'message' => "Se crearon {$sectionsCreated} secciones. Ahora puede generar el texto con IA para cada sección."
        ];
    }

    /**
     * Crear una sección (sin texto de IA)
     */
    protected function createSection(int $reportId, array $data): int
    {
        $data['report_id'] = $reportId;
        $data['ai_generated_text'] = null; // Se generará después

        try {
            return $this->sectionModel->insert($data) ? 1 : 0;
        } catch (\Exception $e) {
            log_message('error', 'Error creando sección: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Generar texto con IA para una sección específica
     */
    public function generateAITextForSection(int $sectionId): array
    {
        $section = $this->sectionModel->find($sectionId);
        if (!$section) {
            return ['success' => false, 'message' => 'Sección no encontrada'];
        }

        // Obtener información del reporte y empresa
        $report = $this->reportModel->find($section['report_id']);
        $batteryService = $this->batteryModel->find($report['battery_service_id']);
        $company = $this->companyModel->find($batteryService['company_id']);

        // Preparar datos para el prompt
        // Determinar si es Forma A o B y asignar correctamente
        $formType = $section['form_type'] ?? 'A';
        $data = [
            'section_level' => $section['section_level'],
            'form_type' => $formType,
        ];

        // Asignar score y risk_level según la forma
        if ($formType === 'B') {
            $data['score_b'] = $section['score_value'];
            $data['risk_level_b'] = $section['risk_level'];
        } else {
            $data['score_a'] = $section['score_value'];
            $data['risk_level_a'] = $section['risk_level'];
        }

        // Obtener nombre y definición según el tipo de sección
        if ($section['dimension_code']) {
            $def = $this->dimensionDefinitions[$section['dimension_code']] ?? null;
            $data['name'] = $def['name'] ?? ucwords(str_replace('_', ' ', $section['dimension_code']));
            $data['definition'] = $def['definition'] ?? '';
        } elseif ($section['domain_code']) {
            $key = 'dom_' . $section['domain_code'];
            $def = $this->dimensionDefinitions[$key] ?? null;
            $data['name'] = $def['name'] ?? 'Dominio ' . ucwords($section['domain_code']);
            $data['definition'] = $def['definition'] ?? '';
        } elseif ($section['section_level'] === 'questionnaire') {
            $names = [
                'intralaboral' => 'Factores de Riesgo Intralaboral',
                'extralaboral' => 'Factores de Riesgo Extralaboral',
                'stress' => 'Evaluación del Estrés',
            ];
            $data['name'] = $names[$section['questionnaire_type']] ?? 'Cuestionario';
            $data['definition'] = '';
        } else {
            $data['name'] = 'Puntaje Total General';
            $data['definition'] = 'Resultado integrado de los factores de riesgo psicosocial.';
        }

        // Obtener el prompt complementario del consultor (si existe)
        $consultantPrompt = $section['consultant_prompt'] ?? null;

        // Generar texto con IA
        if ($section['section_level'] === 'executive') {
            // Usar resultados de la forma específica (A o B), no mezclar
            $formType = $section['form_type'] ?? 'A';
            $resultsForma = $this->getAggregatedResults($report['battery_service_id'], $formType);
            $criticalAreas = $this->getCriticalAreas($resultsForma);

            // Contar trabajadores de esta forma
            $totalWorkersForma = $this->resultModel
                ->where('battery_service_id', $report['battery_service_id'])
                ->where('intralaboral_form_type', $formType)
                ->countAllResults();

            $formaNombre = $formType === 'A' ? 'Jefes, Profesionales y Técnicos' : 'Auxiliares y Operarios';

            $companyData = [
                'name' => $company['name'] ?? 'Empresa',
                'total_workers' => $totalWorkersForma,
                'evaluation_date' => date('Y-m-d'),
                'form_type' => $formType,
                'form_name' => $formaNombre,
            ];

            $overallResults = [
                'general_score' => $section['score_value'] ?? 0,
                'general_risk' => $section['risk_level'] ?? 'sin_riesgo',
                'intralaboral_score' => $resultsForma['intralaboral_total_puntaje'] ?? 0,
                'extralaboral_score' => $resultsForma['extralaboral_total_puntaje'] ?? 0,
                'stress_score' => $resultsForma['estres_total_puntaje'] ?? 0,
            ];

            $aiText = $this->openAIService->generateExecutiveSummary($companyData, $overallResults, $criticalAreas, $consultantPrompt);
        } else {
            $aiText = $this->openAIService->generateInterpretation($data, $consultantPrompt);
        }

        if ($aiText) {
            $this->sectionModel->update($sectionId, ['ai_generated_text' => $aiText]);
            return ['success' => true, 'text' => $aiText];
        }

        return ['success' => false, 'message' => 'Error al generar texto con IA'];
    }

    /**
     * Obtener resultados agregados por forma
     */
    protected function getAggregatedResults(int $batteryServiceId, string $formType): array
    {
        $results = $this->resultModel
            ->where('battery_service_id', $batteryServiceId)
            ->where('intralaboral_form_type', $formType)
            ->findAll();

        if (empty($results)) {
            return [];
        }

        return $this->calculateAverages($results);
    }

    /**
     * DEPRECATED: Este método mezclaba Forma A y B sin baremo válido.
     * La Resolución 2404/2019 (Tabla 34) NO define baremos para "conjunto".
     * Usar getAggregatedResults($batteryServiceId, 'A') o 'B' en su lugar.
     *
     * @deprecated Usar getAggregatedResults() con forma específica
     */
    protected function getAggregatedResultsConjunto(int $batteryServiceId): array
    {
        // Mantener por compatibilidad pero devolver vacío
        // TODO: Eliminar este método cuando se confirme que no hay dependencias
        log_message('warning', 'DEPRECATED: getAggregatedResultsConjunto() fue llamado. Use getAggregatedResults() con forma A o B.');

        $results = $this->resultModel
            ->where('battery_service_id', $batteryServiceId)
            ->findAll();

        if (empty($results)) {
            return [];
        }

        $aggregated = $this->calculateAverages($results);
        $aggregated['total_workers'] = count($results);

        return $aggregated;
    }

    /**
     * Calcular promedios de los resultados
     */
    protected function calculateAverages(array $results): array
    {
        if (empty($results)) {
            return [];
        }

        // Si solo hay un resultado, devolver directamente sus valores
        if (count($results) === 1) {
            return $results[0];
        }

        $numericFields = [
            'puntaje_total_general', 'intralaboral_total_puntaje', 'extralaboral_total_puntaje',
            'estres_total_puntaje', 'dom_liderazgo_puntaje', 'dom_control_puntaje',
            'dom_demandas_puntaje', 'dom_recompensas_puntaje',
            'dim_caracteristicas_liderazgo_puntaje', 'dim_relaciones_sociales_puntaje',
            'dim_retroalimentacion_puntaje', 'dim_relacion_colaboradores_puntaje', 'dim_claridad_rol_puntaje',
            'dim_capacitacion_puntaje', 'dim_participacion_manejo_cambio_puntaje',
            'dim_oportunidades_desarrollo_puntaje', 'dim_control_autonomia_puntaje',
            'dim_demandas_ambientales_puntaje', 'dim_demandas_emocionales_puntaje',
            'dim_demandas_cuantitativas_puntaje', 'dim_influencia_trabajo_entorno_extralaboral_puntaje',
            'dim_demandas_carga_mental_puntaje', 'dim_demandas_responsabilidad_puntaje',
            'dim_consistencia_rol_puntaje', 'dim_demandas_jornada_trabajo_puntaje',
            'dim_recompensas_pertenencia_puntaje', 'dim_reconocimiento_compensacion_puntaje',
            'extralaboral_tiempo_fuera_puntaje', 'extralaboral_relaciones_familiares_puntaje',
            'extralaboral_comunicacion_puntaje', 'extralaboral_situacion_economica_puntaje',
            'extralaboral_caracteristicas_vivienda_puntaje', 'extralaboral_influencia_entorno_puntaje',
            'extralaboral_desplazamiento_puntaje',
        ];

        $averages = [];

        foreach ($numericFields as $field) {
            $sum = 0;
            $validCount = 0;
            foreach ($results as $result) {
                if (isset($result[$field]) && $result[$field] !== null) {
                    $sum += floatval($result[$field]);
                    $validCount++;
                }
            }
            $averages[$field] = $validCount > 0 ? round($sum / $validCount, 2) : null;
        }

        // Determinar niveles de riesgo basados en promedios
        $averages['puntaje_total_general_nivel'] = $this->determineRiskLevel($averages['puntaje_total_general'] ?? 0);
        $averages['intralaboral_total_nivel'] = $this->determineRiskLevel($averages['intralaboral_total_puntaje'] ?? 0);
        $averages['extralaboral_total_nivel'] = $this->determineRiskLevel($averages['extralaboral_total_puntaje'] ?? 0);
        $averages['estres_total_nivel'] = $this->determineRiskLevel($averages['estres_total_puntaje'] ?? 0);
        $averages['dom_liderazgo_nivel'] = $this->determineRiskLevel($averages['dom_liderazgo_puntaje'] ?? 0);
        $averages['dom_control_nivel'] = $this->determineRiskLevel($averages['dom_control_puntaje'] ?? 0);
        $averages['dom_demandas_nivel'] = $this->determineRiskLevel($averages['dom_demandas_puntaje'] ?? 0);
        $averages['dom_recompensas_nivel'] = $this->determineRiskLevel($averages['dom_recompensas_puntaje'] ?? 0);

        // Determinar niveles de riesgo para dimensiones intralaborales
        $averages['dim_caracteristicas_liderazgo_nivel'] = $this->determineRiskLevel($averages['dim_caracteristicas_liderazgo_puntaje'] ?? 0);
        $averages['dim_relaciones_sociales_nivel'] = $this->determineRiskLevel($averages['dim_relaciones_sociales_puntaje'] ?? 0);
        $averages['dim_retroalimentacion_nivel'] = $this->determineRiskLevel($averages['dim_retroalimentacion_puntaje'] ?? 0);
        $averages['dim_relacion_colaboradores_nivel'] = $this->determineRiskLevel($averages['dim_relacion_colaboradores_puntaje'] ?? 0);
        $averages['dim_claridad_rol_nivel'] = $this->determineRiskLevel($averages['dim_claridad_rol_puntaje'] ?? 0);
        $averages['dim_capacitacion_nivel'] = $this->determineRiskLevel($averages['dim_capacitacion_puntaje'] ?? 0);
        $averages['dim_participacion_manejo_cambio_nivel'] = $this->determineRiskLevel($averages['dim_participacion_manejo_cambio_puntaje'] ?? 0);
        $averages['dim_oportunidades_desarrollo_nivel'] = $this->determineRiskLevel($averages['dim_oportunidades_desarrollo_puntaje'] ?? 0);
        $averages['dim_control_autonomia_nivel'] = $this->determineRiskLevel($averages['dim_control_autonomia_puntaje'] ?? 0);
        $averages['dim_demandas_ambientales_nivel'] = $this->determineRiskLevel($averages['dim_demandas_ambientales_puntaje'] ?? 0);
        $averages['dim_demandas_emocionales_nivel'] = $this->determineRiskLevel($averages['dim_demandas_emocionales_puntaje'] ?? 0);
        $averages['dim_demandas_cuantitativas_nivel'] = $this->determineRiskLevel($averages['dim_demandas_cuantitativas_puntaje'] ?? 0);
        $averages['dim_influencia_trabajo_entorno_extralaboral_nivel'] = $this->determineRiskLevel($averages['dim_influencia_trabajo_entorno_extralaboral_puntaje'] ?? 0);
        $averages['dim_demandas_carga_mental_nivel'] = $this->determineRiskLevel($averages['dim_demandas_carga_mental_puntaje'] ?? 0);
        $averages['dim_demandas_responsabilidad_nivel'] = $this->determineRiskLevel($averages['dim_demandas_responsabilidad_puntaje'] ?? 0);
        $averages['dim_consistencia_rol_nivel'] = $this->determineRiskLevel($averages['dim_consistencia_rol_puntaje'] ?? 0);
        $averages['dim_demandas_jornada_trabajo_nivel'] = $this->determineRiskLevel($averages['dim_demandas_jornada_trabajo_puntaje'] ?? 0);
        $averages['dim_recompensas_pertenencia_nivel'] = $this->determineRiskLevel($averages['dim_recompensas_pertenencia_puntaje'] ?? 0);
        $averages['dim_reconocimiento_compensacion_nivel'] = $this->determineRiskLevel($averages['dim_reconocimiento_compensacion_puntaje'] ?? 0);

        // Determinar niveles de riesgo para dimensiones extralaborales
        $averages['extralaboral_tiempo_fuera_nivel'] = $this->determineRiskLevel($averages['extralaboral_tiempo_fuera_puntaje'] ?? 0);
        $averages['extralaboral_relaciones_familiares_nivel'] = $this->determineRiskLevel($averages['extralaboral_relaciones_familiares_puntaje'] ?? 0);
        $averages['extralaboral_comunicacion_nivel'] = $this->determineRiskLevel($averages['extralaboral_comunicacion_puntaje'] ?? 0);
        $averages['extralaboral_situacion_economica_nivel'] = $this->determineRiskLevel($averages['extralaboral_situacion_economica_puntaje'] ?? 0);
        $averages['extralaboral_caracteristicas_vivienda_nivel'] = $this->determineRiskLevel($averages['extralaboral_caracteristicas_vivienda_puntaje'] ?? 0);
        $averages['extralaboral_influencia_entorno_nivel'] = $this->determineRiskLevel($averages['extralaboral_influencia_entorno_puntaje'] ?? 0);
        $averages['extralaboral_desplazamiento_nivel'] = $this->determineRiskLevel($averages['extralaboral_desplazamiento_puntaje'] ?? 0);

        return $averages;
    }

    /**
     * Determinar nivel de riesgo basado en puntaje
     */
    protected function determineRiskLevel(float $score): string
    {
        if ($score <= 19.9) return 'sin_riesgo';
        if ($score <= 24.8) return 'riesgo_bajo';
        if ($score <= 29.5) return 'riesgo_medio';
        if ($score <= 35.4) return 'riesgo_alto';
        return 'riesgo_muy_alto';
    }

    /**
     * Obtener áreas críticas (riesgo alto y muy alto)
     */
    protected function getCriticalAreas(array $results): array
    {
        $critical = [];
        $riskFields = [
            'dom_liderazgo_nivel' => ['name' => 'Liderazgo y relaciones sociales', 'puntaje' => 'dom_liderazgo_puntaje'],
            'dom_control_nivel' => ['name' => 'Control sobre el trabajo', 'puntaje' => 'dom_control_puntaje'],
            'dom_demandas_nivel' => ['name' => 'Demandas del trabajo', 'puntaje' => 'dom_demandas_puntaje'],
            'dom_recompensas_nivel' => ['name' => 'Recompensas', 'puntaje' => 'dom_recompensas_puntaje'],
        ];

        foreach ($riskFields as $field => $info) {
            $level = $results[$field] ?? 'sin_riesgo';
            if (in_array($level, ['riesgo_alto', 'riesgo_muy_alto'])) {
                $critical[] = [
                    'name' => $info['name'],
                    'score' => $results[$info['puntaje']] ?? 0,
                    'risk_level' => str_replace('_', ' ', $level),
                ];
            }
        }

        return $critical;
    }
}
