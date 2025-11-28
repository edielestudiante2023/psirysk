<?php

namespace App\Controllers\Pdf\Resultados\Intralaboral;

use App\Controllers\Pdf\PdfBaseController;
use App\Models\CalculatedResultModel;
use App\Models\ReportSectionModel;

/**
 * Controller para las Dimensiones Intralaborales del informe PDF
 * Genera las 19 dimensiones (para Forma A) o 16 dimensiones (para Forma B)
 */
class DimensionesController extends PdfBaseController
{
    protected $calculatedResultModel;
    protected $reportSectionModel;

    /**
     * Definición de las dimensiones intralaborales
     */
    protected $dimensiones = [
        // Dominio: Liderazgo y Relaciones Sociales
        'dim_caracteristicas_liderazgo' => [
            'nombre' => 'Características del Liderazgo',
            'dominio' => 'Liderazgo y Relaciones Sociales en el Trabajo',
            'definicion' => 'Se refiere a los atributos de la gestión de los jefes inmediatos en relación con la planificación y asignación del trabajo, consecución de resultados, resolución de conflictos, participación, motivación, apoyo, interacción y comunicación con sus colaboradores.',
            'campo_puntaje' => 'dim_caracteristicas_liderazgo_puntaje',
            'campo_nivel' => 'dim_caracteristicas_liderazgo_nivel',
            'aplica_forma' => ['A', 'B'],
        ],
        'dim_relaciones_sociales' => [
            'nombre' => 'Relaciones Sociales en el Trabajo',
            'dominio' => 'Liderazgo y Relaciones Sociales en el Trabajo',
            'definicion' => 'Son las interacciones que se establecen con otras personas en el trabajo, particularmente en lo referente a: la posibilidad de establecer contacto con otros individuos en el ejercicio de la actividad laboral, las características y calidad de las interacciones entre compañeros, el apoyo social que se recibe de los compañeros, el trabajo en equipo y la cohesión.',
            'campo_puntaje' => 'dim_relaciones_sociales_puntaje',
            'campo_nivel' => 'dim_relaciones_sociales_nivel',
            'aplica_forma' => ['A', 'B'],
        ],
        'dim_retroalimentacion' => [
            'nombre' => 'Retroalimentación del Desempeño',
            'dominio' => 'Liderazgo y Relaciones Sociales en el Trabajo',
            'definicion' => 'Describe la información que un trabajador recibe sobre la forma como realiza su trabajo con el fin de indicar las fortalezas y debilidades y tomar acciones para mejorar o mantener el desempeño.',
            'campo_puntaje' => 'dim_retroalimentacion_puntaje',
            'campo_nivel' => 'dim_retroalimentacion_nivel',
            'aplica_forma' => ['A', 'B'],
        ],
        'dim_relacion_colaboradores' => [
            'nombre' => 'Relación con los Colaboradores (Subordinados)',
            'dominio' => 'Liderazgo y Relaciones Sociales en el Trabajo',
            'definicion' => 'Trata de los atributos de la gestión de los subordinados en relación con la ejecución del trabajo, consecución de resultados, resolución de conflictos y participación. Además, se consideran las características de interacción y formas de comunicación con la jefatura.',
            'campo_puntaje' => 'dim_relacion_colaboradores_puntaje',
            'campo_nivel' => 'dim_relacion_colaboradores_nivel',
            'aplica_forma' => ['A'], // Solo Forma A
        ],

        // Dominio: Control sobre el Trabajo
        'dim_claridad_rol' => [
            'nombre' => 'Claridad del Rol',
            'dominio' => 'Control sobre el Trabajo',
            'definicion' => 'Es la definición y comunicación del papel que se espera que el trabajador desempeñe en la organización, específicamente en torno a los objetivos del trabajo, las funciones y resultados, el margen de autonomía y el impacto del ejercicio del cargo en la empresa.',
            'campo_puntaje' => 'dim_claridad_rol_puntaje',
            'campo_nivel' => 'dim_claridad_rol_nivel',
            'aplica_forma' => ['A', 'B'],
        ],
        'dim_capacitacion' => [
            'nombre' => 'Capacitación',
            'dominio' => 'Control sobre el Trabajo',
            'definicion' => 'Se entiende por las actividades de inducción, entrenamiento y formación que la organización brinda al trabajador con el fin de desarrollar y fortalecer sus conocimientos y habilidades.',
            'campo_puntaje' => 'dim_capacitacion_puntaje',
            'campo_nivel' => 'dim_capacitacion_nivel',
            'aplica_forma' => ['A', 'B'],
        ],
        'dim_participacion_cambio' => [
            'nombre' => 'Participación y Manejo del Cambio',
            'dominio' => 'Control sobre el Trabajo',
            'definicion' => 'Se entiende como el conjunto de mecanismos organizacionales orientados a incrementar la capacidad de adaptación de los trabajadores a las diferentes transformaciones que se presentan en el contexto laboral.',
            'campo_puntaje' => 'dim_participacion_manejo_cambio_puntaje',
            'campo_nivel' => 'dim_participacion_manejo_cambio_nivel',
            'aplica_forma' => ['A', 'B'],
        ],
        'dim_oportunidades' => [
            'nombre' => 'Oportunidades para el Uso y Desarrollo de Habilidades y Conocimientos',
            'dominio' => 'Control sobre el Trabajo',
            'definicion' => 'Se refiere a la posibilidad que el trabajo le brinda al individuo de aplicar, aprender y desarrollar sus habilidades y conocimientos.',
            'campo_puntaje' => 'dim_oportunidades_desarrollo_puntaje',
            'campo_nivel' => 'dim_oportunidades_desarrollo_nivel',
            'aplica_forma' => ['A', 'B'],
        ],
        'dim_control_autonomia' => [
            'nombre' => 'Control y Autonomía sobre el Trabajo',
            'dominio' => 'Control sobre el Trabajo',
            'definicion' => 'Se refiere al margen de decisión que tiene un individuo sobre aspectos como el orden de las actividades, la cantidad, el ritmo, la forma de trabajar, las pausas durante la jornada y los tiempos de descanso.',
            'campo_puntaje' => 'dim_control_autonomia_puntaje',
            'campo_nivel' => 'dim_control_autonomia_nivel',
            'aplica_forma' => ['A', 'B'],
        ],

        // Dominio: Demandas del Trabajo
        'dim_demandas_ambientales' => [
            'nombre' => 'Demandas Ambientales y de Esfuerzo Físico',
            'dominio' => 'Demandas del Trabajo',
            'definicion' => 'Las demandas ambientales y de esfuerzo físico de la ocupación hacen referencia a las condiciones del lugar de trabajo y a la carga física que involucran las actividades que se desarrollan, que bajo ciertas circunstancias exigen del individuo un esfuerzo de adaptación.',
            'campo_puntaje' => 'dim_demandas_ambientales_puntaje',
            'campo_nivel' => 'dim_demandas_ambientales_nivel',
            'aplica_forma' => ['A', 'B'],
        ],
        'dim_demandas_emocionales' => [
            'nombre' => 'Demandas Emocionales',
            'dominio' => 'Demandas del Trabajo',
            'definicion' => 'Situaciones afectivas y emocionales propias del contenido de la tarea que tienen el potencial de interferir con los sentimientos y emociones del trabajador.',
            'campo_puntaje' => 'dim_demandas_emocionales_puntaje',
            'campo_nivel' => 'dim_demandas_emocionales_nivel',
            'aplica_forma' => ['A', 'B'],
        ],
        'dim_demandas_cuantitativas' => [
            'nombre' => 'Demandas Cuantitativas',
            'dominio' => 'Demandas del Trabajo',
            'definicion' => 'Son las exigencias relativas a la cantidad de trabajo que se debe ejecutar, en relación con el tiempo disponible para hacerlo.',
            'campo_puntaje' => 'dim_demandas_cuantitativas_puntaje',
            'campo_nivel' => 'dim_demandas_cuantitativas_nivel',
            'aplica_forma' => ['A', 'B'],
        ],
        'dim_influencia_trabajo' => [
            'nombre' => 'Influencia del Trabajo sobre el Entorno Extralaboral',
            'dominio' => 'Demandas del Trabajo',
            'definicion' => 'Condición que se presenta cuando las exigencias de tiempo y esfuerzo que se hacen a un individuo en su trabajo, impactan su vida extralaboral.',
            'campo_puntaje' => 'dim_influencia_trabajo_puntaje',
            'campo_nivel' => 'dim_influencia_trabajo_nivel',
            'aplica_forma' => ['A', 'B'],
        ],
        'dim_exigencias_responsabilidad' => [
            'nombre' => 'Exigencias de Responsabilidad del Cargo',
            'dominio' => 'Demandas del Trabajo',
            'definicion' => 'Las exigencias de responsabilidad directa en el trabajo hacen alusión al conjunto de obligaciones implícitas en el desempeño de un cargo, cuyos resultados no pueden ser transferidos a otras personas.',
            'campo_puntaje' => 'dim_exigencias_responsabilidad_puntaje',
            'campo_nivel' => 'dim_exigencias_responsabilidad_nivel',
            'aplica_forma' => ['A'], // Solo Forma A
        ],
        'dim_demandas_carga_mental' => [
            'nombre' => 'Demandas de Carga Mental',
            'dominio' => 'Demandas del Trabajo',
            'definicion' => 'Las exigencias de carga mental se refieren a las demandas de procesamiento cognitivo que implica la tarea y que involucran procesos mentales superiores de atención, memoria y análisis de información para generar una respuesta.',
            'campo_puntaje' => 'dim_demandas_carga_mental_puntaje',
            'campo_nivel' => 'dim_demandas_carga_mental_nivel',
            'aplica_forma' => ['A', 'B'],
        ],
        'dim_consistencia_rol' => [
            'nombre' => 'Consistencia del Rol',
            'dominio' => 'Demandas del Trabajo',
            'definicion' => 'Se refiere a la compatibilidad o consistencia entre las diversas exigencias relacionadas con los principios de eficiencia, calidad técnica y ética, propios del servicio o producto, que tiene un trabajador en el desempeño de su cargo.',
            'campo_puntaje' => 'dim_consistencia_rol_puntaje',
            'campo_nivel' => 'dim_consistencia_rol_nivel',
            'aplica_forma' => ['A'], // Solo Forma A
        ],
        'dim_demandas_jornada' => [
            'nombre' => 'Demandas de la Jornada de Trabajo',
            'dominio' => 'Demandas del Trabajo',
            'definicion' => 'Las demandas de la jornada de trabajo son las exigencias del tiempo laboral que se hacen al individuo en términos de la duración y el horario de la jornada, así como de los periodos destinados a pausas y descansos periódicos.',
            'campo_puntaje' => 'dim_demandas_jornada_puntaje',
            'campo_nivel' => 'dim_demandas_jornada_nivel',
            'aplica_forma' => ['A', 'B'],
        ],

        // Dominio: Recompensas
        'dim_recompensas_pertenencia' => [
            'nombre' => 'Recompensas Derivadas de la Pertenencia a la Organización y del Trabajo que se Realiza',
            'dominio' => 'Recompensas',
            'definicion' => 'Se refieren al sentimiento de orgullo y a la percepción de estabilidad laboral que experimenta el colaborador por estar vinculado a una organización, así como el sentimiento de autorrealización que experimenta por efectuar su trabajo.',
            'campo_puntaje' => 'dim_recompensas_pertenencia_puntaje',
            'campo_nivel' => 'dim_recompensas_pertenencia_nivel',
            'aplica_forma' => ['A', 'B'],
        ],
        'dim_reconocimiento_compensacion' => [
            'nombre' => 'Reconocimiento y Compensación',
            'dominio' => 'Recompensas',
            'definicion' => 'Es el conjunto de retribuciones que la organización le otorga al trabajador en contraprestación al esfuerzo realizado en el trabajo. Estas retribuciones corresponden a reconocimiento, remuneración económica y acceso a los servicios de bienestar y posibilidades de desarrollo.',
            'campo_puntaje' => 'dim_reconocimiento_compensacion_puntaje',
            'campo_nivel' => 'dim_reconocimiento_compensacion_nivel',
            'aplica_forma' => ['A', 'B'],
        ],
    ];

    /**
     * Baremos por forma y dimensión
     */
    protected $baremos = [
        'A' => [
            'dim_caracteristicas_liderazgo' => ['sin_riesgo' => [0.0, 3.8], 'riesgo_bajo' => [3.9, 15.4], 'riesgo_medio' => [15.5, 30.8], 'riesgo_alto' => [30.9, 46.2], 'riesgo_muy_alto' => [46.3, 100.0]],
            'dim_relaciones_sociales' => ['sin_riesgo' => [0.0, 5.4], 'riesgo_bajo' => [5.5, 16.1], 'riesgo_medio' => [16.2, 25.0], 'riesgo_alto' => [25.1, 37.5], 'riesgo_muy_alto' => [37.6, 100.0]],
            'dim_retroalimentacion' => ['sin_riesgo' => [0.0, 10.0], 'riesgo_bajo' => [10.1, 25.0], 'riesgo_medio' => [25.1, 40.0], 'riesgo_alto' => [40.1, 55.0], 'riesgo_muy_alto' => [55.1, 100.0]],
            'dim_relacion_colaboradores' => ['sin_riesgo' => [0.0, 13.9], 'riesgo_bajo' => [14.0, 25.0], 'riesgo_medio' => [25.1, 33.3], 'riesgo_alto' => [33.4, 47.2], 'riesgo_muy_alto' => [47.3, 100.0]],
            'dim_claridad_rol' => ['sin_riesgo' => [0.0, 0.9], 'riesgo_bajo' => [1.0, 10.7], 'riesgo_medio' => [10.8, 21.4], 'riesgo_alto' => [21.5, 39.3], 'riesgo_muy_alto' => [39.4, 100.0]],
            'dim_capacitacion' => ['sin_riesgo' => [0.0, 0.9], 'riesgo_bajo' => [1.0, 16.7], 'riesgo_medio' => [16.8, 33.3], 'riesgo_alto' => [33.4, 50.0], 'riesgo_muy_alto' => [50.1, 100.0]],
            'dim_participacion_cambio' => ['sin_riesgo' => [0.0, 12.5], 'riesgo_bajo' => [12.6, 25.0], 'riesgo_medio' => [25.1, 37.5], 'riesgo_alto' => [37.6, 50.0], 'riesgo_muy_alto' => [50.1, 100.0]],
            'dim_oportunidades' => ['sin_riesgo' => [0.0, 0.9], 'riesgo_bajo' => [1.0, 6.3], 'riesgo_medio' => [6.4, 18.8], 'riesgo_alto' => [18.9, 31.3], 'riesgo_muy_alto' => [31.4, 100.0]],
            'dim_control_autonomia' => ['sin_riesgo' => [0.0, 8.3], 'riesgo_bajo' => [8.4, 25.0], 'riesgo_medio' => [25.1, 41.7], 'riesgo_alto' => [41.8, 58.3], 'riesgo_muy_alto' => [58.4, 100.0]],
            'dim_demandas_ambientales' => ['sin_riesgo' => [0.0, 14.6], 'riesgo_bajo' => [14.7, 22.9], 'riesgo_medio' => [23.0, 31.3], 'riesgo_alto' => [31.4, 39.6], 'riesgo_muy_alto' => [39.7, 100.0]],
            'dim_demandas_emocionales' => ['sin_riesgo' => [0.0, 16.7], 'riesgo_bajo' => [16.8, 25.0], 'riesgo_medio' => [25.1, 33.3], 'riesgo_alto' => [33.4, 47.2], 'riesgo_muy_alto' => [47.3, 100.0]],
            'dim_demandas_cuantitativas' => ['sin_riesgo' => [0.0, 25.0], 'riesgo_bajo' => [25.1, 33.3], 'riesgo_medio' => [33.4, 45.8], 'riesgo_alto' => [45.9, 54.2], 'riesgo_muy_alto' => [54.3, 100.0]],
            'dim_influencia_trabajo' => ['sin_riesgo' => [0.0, 18.8], 'riesgo_bajo' => [18.9, 31.3], 'riesgo_medio' => [31.4, 43.8], 'riesgo_alto' => [43.9, 50.0], 'riesgo_muy_alto' => [50.1, 100.0]],
            'dim_exigencias_responsabilidad' => ['sin_riesgo' => [0.0, 37.5], 'riesgo_bajo' => [37.6, 54.2], 'riesgo_medio' => [54.3, 66.7], 'riesgo_alto' => [66.8, 79.2], 'riesgo_muy_alto' => [79.3, 100.0]],
            'dim_demandas_carga_mental' => ['sin_riesgo' => [0.0, 60.0], 'riesgo_bajo' => [60.1, 70.0], 'riesgo_medio' => [70.1, 80.0], 'riesgo_alto' => [80.1, 90.0], 'riesgo_muy_alto' => [90.1, 100.0]],
            'dim_consistencia_rol' => ['sin_riesgo' => [0.0, 15.0], 'riesgo_bajo' => [15.1, 25.0], 'riesgo_medio' => [25.1, 35.0], 'riesgo_alto' => [35.1, 45.0], 'riesgo_muy_alto' => [45.1, 100.0]],
            'dim_demandas_jornada' => ['sin_riesgo' => [0.0, 8.3], 'riesgo_bajo' => [8.4, 25.0], 'riesgo_medio' => [25.1, 33.3], 'riesgo_alto' => [33.4, 50.0], 'riesgo_muy_alto' => [50.1, 100.0]],
            'dim_recompensas_pertenencia' => ['sin_riesgo' => [0.0, 0.9], 'riesgo_bajo' => [1.0, 5.0], 'riesgo_medio' => [5.1, 10.0], 'riesgo_alto' => [10.1, 20.0], 'riesgo_muy_alto' => [20.1, 100.0]],
            'dim_reconocimiento_compensacion' => ['sin_riesgo' => [0.0, 4.2], 'riesgo_bajo' => [4.3, 16.7], 'riesgo_medio' => [16.8, 25.0], 'riesgo_alto' => [25.1, 37.5], 'riesgo_muy_alto' => [37.6, 100.0]],
        ],
        'B' => [
            'dim_caracteristicas_liderazgo' => ['sin_riesgo' => [0.0, 3.8], 'riesgo_bajo' => [3.9, 13.5], 'riesgo_medio' => [13.6, 25.0], 'riesgo_alto' => [25.1, 38.5], 'riesgo_muy_alto' => [38.6, 100.0]],
            'dim_relaciones_sociales' => ['sin_riesgo' => [0.0, 6.3], 'riesgo_bajo' => [6.4, 14.6], 'riesgo_medio' => [14.7, 27.1], 'riesgo_alto' => [27.2, 37.5], 'riesgo_muy_alto' => [37.6, 100.0]],
            'dim_retroalimentacion' => ['sin_riesgo' => [0.0, 5.0], 'riesgo_bajo' => [5.1, 20.0], 'riesgo_medio' => [20.1, 30.0], 'riesgo_alto' => [30.1, 50.0], 'riesgo_muy_alto' => [50.1, 100.0]],
            'dim_claridad_rol' => ['sin_riesgo' => [0.0, 0.9], 'riesgo_bajo' => [1.0, 5.0], 'riesgo_medio' => [5.1, 15.0], 'riesgo_alto' => [15.1, 30.0], 'riesgo_muy_alto' => [30.1, 100.0]],
            'dim_capacitacion' => ['sin_riesgo' => [0.0, 0.9], 'riesgo_bajo' => [1.0, 16.7], 'riesgo_medio' => [16.8, 25.0], 'riesgo_alto' => [25.1, 50.0], 'riesgo_muy_alto' => [50.1, 100.0]],
            'dim_participacion_cambio' => ['sin_riesgo' => [0.0, 16.7], 'riesgo_bajo' => [16.8, 33.3], 'riesgo_medio' => [33.4, 41.7], 'riesgo_alto' => [41.8, 58.3], 'riesgo_muy_alto' => [58.4, 100.0]],
            'dim_oportunidades' => ['sin_riesgo' => [0.0, 6.3], 'riesgo_bajo' => [6.4, 18.8], 'riesgo_medio' => [18.9, 31.3], 'riesgo_alto' => [31.4, 43.8], 'riesgo_muy_alto' => [43.9, 100.0]],
            'dim_control_autonomia' => ['sin_riesgo' => [0.0, 16.7], 'riesgo_bajo' => [16.8, 33.3], 'riesgo_medio' => [33.4, 50.0], 'riesgo_alto' => [50.1, 66.7], 'riesgo_muy_alto' => [66.8, 100.0]],
            'dim_demandas_ambientales' => ['sin_riesgo' => [0.0, 20.8], 'riesgo_bajo' => [20.9, 29.2], 'riesgo_medio' => [29.3, 37.5], 'riesgo_alto' => [37.6, 50.0], 'riesgo_muy_alto' => [50.1, 100.0]],
            'dim_demandas_emocionales' => ['sin_riesgo' => [0.0, 16.7], 'riesgo_bajo' => [16.8, 25.0], 'riesgo_medio' => [25.1, 33.3], 'riesgo_alto' => [33.4, 50.0], 'riesgo_muy_alto' => [50.1, 100.0]],
            'dim_demandas_cuantitativas' => ['sin_riesgo' => [0.0, 25.0], 'riesgo_bajo' => [25.1, 33.3], 'riesgo_medio' => [33.4, 41.7], 'riesgo_alto' => [41.8, 58.3], 'riesgo_muy_alto' => [58.4, 100.0]],
            'dim_influencia_trabajo' => ['sin_riesgo' => [0.0, 18.8], 'riesgo_bajo' => [18.9, 25.0], 'riesgo_medio' => [25.1, 37.5], 'riesgo_alto' => [37.6, 50.0], 'riesgo_muy_alto' => [50.1, 100.0]],
            'dim_demandas_carga_mental' => ['sin_riesgo' => [0.0, 55.0], 'riesgo_bajo' => [55.1, 65.0], 'riesgo_medio' => [65.1, 75.0], 'riesgo_alto' => [75.1, 85.0], 'riesgo_muy_alto' => [85.1, 100.0]],
            'dim_demandas_jornada' => ['sin_riesgo' => [0.0, 16.7], 'riesgo_bajo' => [16.8, 33.3], 'riesgo_medio' => [33.4, 41.7], 'riesgo_alto' => [41.8, 58.3], 'riesgo_muy_alto' => [58.4, 100.0]],
            'dim_recompensas_pertenencia' => ['sin_riesgo' => [0.0, 0.9], 'riesgo_bajo' => [1.0, 5.0], 'riesgo_medio' => [5.1, 12.5], 'riesgo_alto' => [12.6, 22.5], 'riesgo_muy_alto' => [22.6, 100.0]],
            'dim_reconocimiento_compensacion' => ['sin_riesgo' => [0.0, 4.2], 'riesgo_bajo' => [4.3, 12.5], 'riesgo_medio' => [12.6, 25.0], 'riesgo_alto' => [25.1, 37.5], 'riesgo_muy_alto' => [37.6, 100.0]],
        ],
    ];

    public function __construct()
    {
        $this->calculatedResultModel = new CalculatedResultModel();
        $this->reportSectionModel = new ReportSectionModel();
    }

    /**
     * Orden de dominios para el informe
     */
    protected $dominiosOrden = [
        'Liderazgo y Relaciones Sociales en el Trabajo',
        'Control sobre el Trabajo',
        'Demandas del Trabajo',
        'Recompensas',
    ];

    /**
     * Renderiza todas las páginas de dimensiones intralaborales
     * Organizado por dominio, intercalando Forma A y Forma B para cada dimensión
     *
     * @param int $batteryServiceId
     * @param string|null $forma 'A', 'B' o null para ambas (intercalado)
     * @return string HTML de todas las páginas de dimensiones
     */
    public function render($batteryServiceId, $forma = null)
    {
        $this->initializeData($batteryServiceId);

        // Si se especifica una forma, usar solo esa
        $formasDisponibles = $forma ? [strtoupper($forma)] : ['A', 'B'];

        // Cargar resultados para cada forma disponible
        $resultsByForma = [];
        $reportTextsByForma = [];

        foreach ($formasDisponibles as $f) {
            $results = $this->calculatedResultModel
                ->where('battery_service_id', $batteryServiceId)
                ->where('intralaboral_form_type', $f)
                ->findAll();

            if (!empty($results)) {
                $resultsByForma[$f] = $results;
                $reportTextsByForma[$f] = $this->getReportTexts($batteryServiceId, $f);
            }
        }

        // Si no hay resultados, retornar vacío
        if (empty($resultsByForma)) {
            return '';
        }

        $html = '';

        // Página introductoria
        $html .= $this->renderView('pdf/intralaboral/intro_dimensiones', []);
        $html .= $this->pageBreak();

        // Agrupar dimensiones por dominio
        $dimensionesPorDominio = [];
        foreach ($this->dimensiones as $dimKey => $dimension) {
            $dominio = $dimension['dominio'];
            if (!isset($dimensionesPorDominio[$dominio])) {
                $dimensionesPorDominio[$dominio] = [];
            }
            $dimensionesPorDominio[$dominio][$dimKey] = $dimension;
        }

        // Iterar por dominios en orden
        foreach ($this->dominiosOrden as $dominio) {
            if (!isset($dimensionesPorDominio[$dominio])) {
                continue;
            }

            // Para cada dimensión del dominio
            foreach ($dimensionesPorDominio[$dominio] as $dimKey => $dimension) {
                // Generar página para cada forma disponible (A primero, B después)
                foreach (['A', 'B'] as $f) {
                    // Verificar si esta forma está disponible y aplica a esta dimensión
                    if (!isset($resultsByForma[$f])) {
                        continue;
                    }
                    if (!in_array($f, $dimension['aplica_forma'])) {
                        continue;
                    }

                    $dimensionData = $this->calculateDimensionData(
                        $resultsByForma[$f],
                        $dimKey,
                        $dimension,
                        $f
                    );
                    $dimensionData['texto_ia'] = $reportTextsByForma[$f][$dimKey] ?? null;

                    $html .= $this->renderView('pdf/intralaboral/dimension_page', [
                        'dimension' => $dimensionData,
                        'forma' => $f,
                        'baremos' => $this->baremos[$f][$dimKey] ?? null,
                    ]);

                    $html .= $this->pageBreak();
                }
            }
        }

        return $html;
    }

    /**
     * Calcula los datos de una dimensión específica
     */
    private function calculateDimensionData($results, $dimKey, $dimension, $forma)
    {
        $campoPuntaje = $dimension['campo_puntaje'];
        $campoNivel = $dimension['campo_nivel'];

        // Calcular promedio
        $puntajes = array_column($results, $campoPuntaje);
        $puntajes = array_filter($puntajes, function($v) { return $v !== null && $v !== ''; });
        $promedio = !empty($puntajes) ? array_sum($puntajes) / count($puntajes) : 0;

        // Contar por nivel de riesgo
        $niveles = array_column($results, $campoNivel);
        $distribucion = [
            'sin_riesgo' => 0,
            'riesgo_bajo' => 0,
            'riesgo_medio' => 0,
            'riesgo_alto' => 0,
            'riesgo_muy_alto' => 0,
        ];

        foreach ($niveles as $nivel) {
            if (isset($distribucion[$nivel])) {
                $distribucion[$nivel]++;
            }
        }

        $total = count($results);

        // Calcular porcentajes
        $porcentajes = [];
        foreach ($distribucion as $nivel => $count) {
            $porcentajes[$nivel] = $total > 0 ? round(($count / $total) * 100, 1) : 0;
        }

        // Determinar nivel del promedio
        $nivelPromedio = $this->getNivelFromPuntaje($promedio, $this->baremos[$forma][$dimKey] ?? []);

        // Identificar trabajadores en riesgo alto/muy alto
        $trabajadoresRiesgoAlto = [];
        foreach ($results as $result) {
            $nivel = $result[$campoNivel] ?? '';
            if (in_array($nivel, ['riesgo_alto', 'riesgo_muy_alto'])) {
                $trabajadoresRiesgoAlto[] = [
                    'nombre' => $result['worker_name'] ?? '',
                    'area' => $result['department'] ?? '',
                    'cargo' => $result['position'] ?? '',
                    'puntaje' => $result[$campoPuntaje] ?? 0,
                    'nivel' => $nivel,
                ];
            }
        }

        return [
            'key' => $dimKey,
            'nombre' => $dimension['nombre'],
            'dominio' => $dimension['dominio'],
            'definicion' => $dimension['definicion'],
            'promedio' => $promedio,
            'nivel' => $nivelPromedio,
            'total_evaluados' => $total,
            'distribucion' => $distribucion,
            'porcentajes' => $porcentajes,
            'trabajadores_riesgo_alto' => $trabajadoresRiesgoAlto,
            'accion' => $this->getRiskAction($nivelPromedio),
        ];
    }

    /**
     * Obtiene el nivel de riesgo basado en el puntaje y baremo
     */
    private function getNivelFromPuntaje($puntaje, $baremo)
    {
        foreach ($baremo as $nivel => $rango) {
            if ($puntaje >= $rango[0] && $puntaje <= $rango[1]) {
                return $nivel;
            }
        }
        return 'sin_riesgo';
    }

    /**
     * Mapeo de dimension_code (BD) a dimension key (controller)
     */
    protected $dimensionCodeMapping = [
        // BD dimension_code => controller key
        'caracteristicas_liderazgo' => 'dim_caracteristicas_liderazgo',
        'relaciones_sociales' => 'dim_relaciones_sociales',
        'retroalimentacion' => 'dim_retroalimentacion',
        'relacion_colaboradores' => 'dim_relacion_colaboradores',
        'claridad_rol' => 'dim_claridad_rol',
        'capacitacion' => 'dim_capacitacion',
        'participacion_cambio' => 'dim_participacion_cambio',
        'oportunidades' => 'dim_oportunidades',
        'control_autonomia' => 'dim_control_autonomia',
        'demandas_ambientales' => 'dim_demandas_ambientales',
        'demandas_emocionales' => 'dim_demandas_emocionales',
        'demandas_cuantitativas' => 'dim_demandas_cuantitativas',
        'influencia_extralaboral' => 'dim_influencia_trabajo',  // BD usa influencia_extralaboral
        'exigencias_responsabilidad' => 'dim_exigencias_responsabilidad',
        'carga_mental' => 'dim_demandas_carga_mental',
        'consistencia_rol' => 'dim_consistencia_rol',
        'jornada_trabajo' => 'dim_demandas_jornada',  // BD usa jornada_trabajo
        'recompensas_pertenencia' => 'dim_recompensas_pertenencia',
        'reconocimiento' => 'dim_reconocimiento_compensacion',  // BD usa reconocimiento
    ];

    /**
     * Obtiene los textos generados por IA de report_sections
     */
    private function getReportTexts($batteryServiceId, $forma)
    {
        $db = \Config\Database::connect();

        // Primero encontrar el report_id para este battery_service_id
        $report = $db->query("
            SELECT id FROM reports WHERE battery_service_id = ? LIMIT 1
        ", [$batteryServiceId])->getRowArray();

        if (!$report) {
            return [];
        }

        $sections = $this->reportSectionModel
            ->where('report_id', $report['id'])
            ->where('questionnaire_type', 'intralaboral')
            ->where('form_type', $forma)
            ->where('section_level', 'dimension')
            ->findAll();

        $texts = [];
        foreach ($sections as $section) {
            $dbCode = $section['dimension_code'] ?? '';
            // Mapear dimension_code de BD a key del controller
            $key = $this->dimensionCodeMapping[$dbCode] ?? $dbCode;
            if (!empty($section['ai_generated_text'])) {
                $texts[$key] = $section['ai_generated_text'];
            }
        }

        return $texts;
    }

    /**
     * Preview de las dimensiones en navegador
     */
    public function preview($batteryServiceId, $forma = null)
    {
        $html = $this->render($batteryServiceId, $forma);

        $title = 'Preview: Dimensiones Intralaborales';
        if ($forma) {
            $title .= ' - Forma ' . strtoupper($forma);
        }

        return view('pdf/_partials/preview_wrapper', [
            'content' => $html,
            'pageTitle' => $title,
            'batteryServiceId' => $batteryServiceId
        ]);
    }
}
