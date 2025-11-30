<?php

namespace App\Controllers\PdfEjecutivo;

use App\Libraries\PdfGaugeGenerator;
use App\Libraries\IntralaboralAScoring;
use App\Libraries\IntralaboralBScoring;

/**
 * Controlador para la sección de Dimensiones Intralaborales del PDF Ejecutivo
 *
 * Genera 36 páginas:
 * - 1 página de introducción
 * - 19 páginas de dimensiones Forma A
 * - 16 páginas de dimensiones Forma B
 *
 * Los baremos se obtienen desde IntralaboralAScoring e IntralaboralBScoring
 * para garantizar consistencia con el núcleo del sistema (Single Source of Truth).
 */
class DimensionesIntralaboralesController extends PdfEjecutivoBaseController
{
    /**
     * Mapeo de códigos del controlador a códigos de las librerías Scoring
     */
    protected $mapeoCodigosDimensiones = [
        'caracteristicas_liderazgo'             => 'caracteristicas_liderazgo',
        'relaciones_sociales'                   => 'relaciones_sociales_trabajo',
        'retroalimentacion'                     => 'retroalimentacion_desempeno',
        'relacion_colaboradores'                => 'relacion_con_colaboradores',
        'claridad_rol'                          => 'claridad_rol',
        'capacitacion'                          => 'capacitacion',
        'participacion_manejo_cambio'           => 'participacion_manejo_cambio',
        'oportunidades_desarrollo'              => 'oportunidades_desarrollo',
        'control_autonomia'                     => 'control_autonomia_trabajo',
        'demandas_ambientales'                  => 'demandas_ambientales_esfuerzo_fisico',
        'demandas_emocionales'                  => 'demandas_emocionales',
        'demandas_cuantitativas'                => 'demandas_cuantitativas',
        'influencia_trabajo_entorno_extralaboral' => 'influencia_trabajo_entorno_extralaboral',
        'demandas_responsabilidad'              => 'exigencias_responsabilidad_cargo',
        'demandas_carga_mental'                 => 'demandas_carga_mental',
        'consistencia_rol'                      => 'consistencia_rol',
        'demandas_jornada_trabajo'              => 'demandas_jornada_trabajo',
        'recompensas_pertenencia'               => 'recompensas_pertenencia_estabilidad',
        'reconocimiento_compensacion'           => 'reconocimiento_compensacion',
    ];

    /**
     * Definición de las 19 dimensiones intralaborales
     * Organizadas por dominio
     */
    protected $dimensiones = [
        // DOMINIO 1: Liderazgo y Relaciones Sociales en el Trabajo
        'caracteristicas_liderazgo' => [
            'nombre' => 'Características del Liderazgo',
            'dominio' => 'Liderazgo y Relaciones Sociales en el Trabajo',
            'codigo' => 'caracteristicas_liderazgo',
            'definicion' => 'Se refiere a los atributos de la gestión de los jefes inmediatos en relación con la planificación y asignación del trabajo, consecución de resultados, resolución de conflictos, participación, motivación, apoyo, interacción y comunicación con sus colaboradores.',
            'campo_puntaje' => 'dim_caracteristicas_liderazgo_puntaje',
            'campo_nivel' => 'dim_caracteristicas_liderazgo_nivel',
            'forma_A' => true,
            'forma_B' => true,
        ],
        'relaciones_sociales' => [
            'nombre' => 'Relaciones Sociales en el Trabajo',
            'dominio' => 'Liderazgo y Relaciones Sociales en el Trabajo',
            'codigo' => 'relaciones_sociales',
            'definicion' => 'Son las interacciones que se establecen con otras personas en el trabajo, particularmente en lo referente a: la posibilidad de establecer contacto con otros individuos en el ejercicio de la actividad laboral, las características y calidad de las interacciones entre compañeros, el apoyo social que se recibe de los compañeros, el trabajo en equipo y la cohesión.',
            'campo_puntaje' => 'dim_relaciones_sociales_puntaje',
            'campo_nivel' => 'dim_relaciones_sociales_nivel',
            'forma_A' => true,
            'forma_B' => true,
        ],
        'retroalimentacion' => [
            'nombre' => 'Retroalimentación del Desempeño',
            'dominio' => 'Liderazgo y Relaciones Sociales en el Trabajo',
            'codigo' => 'retroalimentacion',
            'definicion' => 'Describe la información que un trabajador recibe sobre la forma como realiza su trabajo con el fin de indicar las fortalezas y debilidades y tomar acciones para mejorar o mantener el desempeño.',
            'campo_puntaje' => 'dim_retroalimentacion_puntaje',
            'campo_nivel' => 'dim_retroalimentacion_nivel',
            'forma_A' => true,
            'forma_B' => true,
        ],
        'relacion_colaboradores' => [
            'nombre' => 'Relación con los Colaboradores (Subordinados)',
            'dominio' => 'Liderazgo y Relaciones Sociales en el Trabajo',
            'codigo' => 'relacion_colaboradores',
            'definicion' => 'Trata de los atributos de la gestión de los subordinados en relación con la ejecución del trabajo, consecución de resultados, resolución de conflictos y participación. Aplica únicamente a cargos con personal a cargo o funciones de jefatura.',
            'campo_puntaje' => 'dim_relacion_colaboradores_puntaje',
            'campo_nivel' => 'dim_relacion_colaboradores_nivel',
            'forma_A' => true,
            'forma_B' => false, // SOLO FORMA A
        ],

        // DOMINIO 2: Control sobre el Trabajo
        'claridad_rol' => [
            'nombre' => 'Claridad del Rol',
            'dominio' => 'Control sobre el Trabajo',
            'codigo' => 'claridad_rol',
            'definicion' => 'Es la definición y comunicación del papel que se espera que el trabajador desempeñe en la organización, específicamente en torno a los objetivos del trabajo, las funciones y resultados.',
            'campo_puntaje' => 'dim_claridad_rol_puntaje',
            'campo_nivel' => 'dim_claridad_rol_nivel',
            'forma_A' => true,
            'forma_B' => true,
        ],
        'capacitacion' => [
            'nombre' => 'Capacitación',
            'dominio' => 'Control sobre el Trabajo',
            'codigo' => 'capacitacion',
            'definicion' => 'Se entiende por las actividades de inducción, entrenamiento y formación que la organización brinda al trabajador con el fin de desarrollar y fortalecer sus conocimientos y habilidades.',
            'campo_puntaje' => 'dim_capacitacion_puntaje',
            'campo_nivel' => 'dim_capacitacion_nivel',
            'forma_A' => true,
            'forma_B' => true,
        ],
        'participacion_manejo_cambio' => [
            'nombre' => 'Participación y Manejo del Cambio',
            'dominio' => 'Control sobre el Trabajo',
            'codigo' => 'participacion_manejo_cambio',
            'definicion' => 'Se entiende como el conjunto de mecanismos organizacionales orientados a incrementar la capacidad de adaptación de los trabajadores a las diferentes transformaciones que se presentan en el contexto laboral.',
            'campo_puntaje' => 'dim_participacion_manejo_cambio_puntaje',
            'campo_nivel' => 'dim_participacion_manejo_cambio_nivel',
            'forma_A' => true,
            'forma_B' => true,
        ],
        'oportunidades_desarrollo' => [
            'nombre' => 'Oportunidades para el Uso y Desarrollo de Habilidades',
            'dominio' => 'Control sobre el Trabajo',
            'codigo' => 'oportunidades_desarrollo',
            'definicion' => 'Se refiere a la posibilidad que el trabajo le brinda al individuo de aplicar, aprender y desarrollar sus habilidades y conocimientos.',
            'campo_puntaje' => 'dim_oportunidades_desarrollo_puntaje',
            'campo_nivel' => 'dim_oportunidades_desarrollo_nivel',
            'forma_A' => true,
            'forma_B' => true,
        ],
        'control_autonomia' => [
            'nombre' => 'Control y Autonomía sobre el Trabajo',
            'dominio' => 'Control sobre el Trabajo',
            'codigo' => 'control_autonomia',
            'definicion' => 'Se refiere al margen de decisión que tiene un individuo sobre aspectos como el orden de las actividades, la cantidad, el ritmo, la forma de trabajar, las pausas durante la jornada y los tiempos de descanso.',
            'campo_puntaje' => 'dim_control_autonomia_puntaje',
            'campo_nivel' => 'dim_control_autonomia_nivel',
            'forma_A' => true,
            'forma_B' => true,
        ],

        // DOMINIO 3: Demandas del Trabajo
        'demandas_ambientales' => [
            'nombre' => 'Demandas Ambientales y de Esfuerzo Físico',
            'dominio' => 'Demandas del Trabajo',
            'codigo' => 'demandas_ambientales',
            'definicion' => 'Hacen referencia a las condiciones del lugar de trabajo y a la carga física que involucran las actividades que se desarrollan, que bajo ciertas circunstancias exigen del individuo un esfuerzo de adaptación.',
            'campo_puntaje' => 'dim_demandas_ambientales_puntaje',
            'campo_nivel' => 'dim_demandas_ambientales_nivel',
            'forma_A' => true,
            'forma_B' => true,
        ],
        'demandas_emocionales' => [
            'nombre' => 'Demandas Emocionales',
            'dominio' => 'Demandas del Trabajo',
            'codigo' => 'demandas_emocionales',
            'definicion' => 'Situaciones afectivas y emocionales propias del contenido de la tarea que tienen el potencial de interferir con los sentimientos y emociones del trabajador.',
            'campo_puntaje' => 'dim_demandas_emocionales_puntaje',
            'campo_nivel' => 'dim_demandas_emocionales_nivel',
            'forma_A' => true,
            'forma_B' => true,
        ],
        'demandas_cuantitativas' => [
            'nombre' => 'Demandas Cuantitativas',
            'dominio' => 'Demandas del Trabajo',
            'codigo' => 'demandas_cuantitativas',
            'definicion' => 'Son las exigencias relativas a la cantidad de trabajo que se debe ejecutar, en relación con el tiempo disponible para hacerlo.',
            'campo_puntaje' => 'dim_demandas_cuantitativas_puntaje',
            'campo_nivel' => 'dim_demandas_cuantitativas_nivel',
            'forma_A' => true,
            'forma_B' => true,
        ],
        'influencia_trabajo_entorno_extralaboral' => [
            'nombre' => 'Influencia del Trabajo sobre el Entorno Extralaboral',
            'dominio' => 'Demandas del Trabajo',
            'codigo' => 'influencia_trabajo_entorno_extralaboral',
            'definicion' => 'Condición que se presenta cuando las exigencias de tiempo y esfuerzo que se hacen a un individuo en su trabajo, impactan su vida extralaboral.',
            'campo_puntaje' => 'dim_influencia_trabajo_entorno_extralaboral_puntaje',
            'campo_nivel' => 'dim_influencia_trabajo_entorno_extralaboral_nivel',
            'forma_A' => true,
            'forma_B' => true,
        ],
        'demandas_responsabilidad' => [
            'nombre' => 'Exigencias de Responsabilidad del Cargo',
            'dominio' => 'Demandas del Trabajo',
            'codigo' => 'demandas_responsabilidad',
            'definicion' => 'Las exigencias de responsabilidad directa en el trabajo hacen alusión al conjunto de obligaciones implícitas en el desempeño de un cargo, cuyos resultados no pueden ser transferidos a otras personas.',
            'campo_puntaje' => 'dim_demandas_responsabilidad_puntaje',
            'campo_nivel' => 'dim_demandas_responsabilidad_nivel',
            'forma_A' => true,
            'forma_B' => false, // SOLO FORMA A
        ],
        'demandas_carga_mental' => [
            'nombre' => 'Demandas de Carga Mental',
            'dominio' => 'Demandas del Trabajo',
            'codigo' => 'demandas_carga_mental',
            'definicion' => 'Las exigencias de carga mental se refieren a las demandas de procesamiento cognitivo que implica la tarea y que involucran procesos mentales superiores de atención, memoria y análisis de información.',
            'campo_puntaje' => 'dim_demandas_carga_mental_puntaje',
            'campo_nivel' => 'dim_demandas_carga_mental_nivel',
            'forma_A' => true,
            'forma_B' => true,
        ],
        'consistencia_rol' => [
            'nombre' => 'Consistencia del Rol',
            'dominio' => 'Demandas del Trabajo',
            'codigo' => 'consistencia_rol',
            'definicion' => 'Se refiere a la compatibilidad o consistencia entre las diversas exigencias relacionadas con los principios de eficiencia, calidad técnica y ética, propios del servicio o producto, que tiene un trabajador en el desempeño de su cargo.',
            'campo_puntaje' => 'dim_consistencia_rol_puntaje',
            'campo_nivel' => 'dim_consistencia_rol_nivel',
            'forma_A' => true,
            'forma_B' => false, // SOLO FORMA A
        ],
        'demandas_jornada_trabajo' => [
            'nombre' => 'Demandas de la Jornada de Trabajo',
            'dominio' => 'Demandas del Trabajo',
            'codigo' => 'demandas_jornada_trabajo',
            'definicion' => 'Las demandas de la jornada de trabajo son las exigencias del tiempo laboral que se hacen al individuo en términos de la duración y el horario de la jornada, así como de los períodos destinados a pausas y descansos periódicos.',
            'campo_puntaje' => 'dim_demandas_jornada_trabajo_puntaje',
            'campo_nivel' => 'dim_demandas_jornada_trabajo_nivel',
            'forma_A' => true,
            'forma_B' => true,
        ],

        // DOMINIO 4: Recompensas
        'recompensas_pertenencia' => [
            'nombre' => 'Recompensas Derivadas de la Pertenencia a la Organización',
            'dominio' => 'Recompensas',
            'codigo' => 'recompensas_pertenencia',
            'definicion' => 'Se refieren al sentimiento de orgullo y a la percepción de estabilidad laboral que experimenta un individuo por estar vinculado a una organización, así como el sentimiento de autorrealización que experimenta por efectuar su trabajo.',
            'campo_puntaje' => 'dim_recompensas_pertenencia_puntaje',
            'campo_nivel' => 'dim_recompensas_pertenencia_nivel',
            'forma_A' => true,
            'forma_B' => true,
        ],
        'reconocimiento_compensacion' => [
            'nombre' => 'Reconocimiento y Compensación',
            'dominio' => 'Recompensas',
            'codigo' => 'reconocimiento_compensacion',
            'definicion' => 'Es el conjunto de retribuciones que la organización le otorga al trabajador en contraprestación al esfuerzo realizado en el trabajo. Estas retribuciones corresponden a reconocimiento, remuneración económica, acceso a los servicios de bienestar y posibilidades de desarrollo.',
            'campo_puntaje' => 'dim_reconocimiento_compensacion_puntaje',
            'campo_nivel' => 'dim_reconocimiento_compensacion_nivel',
            'forma_A' => true,
            'forma_B' => true,
        ],
    ];

    /**
     * Acciones según nivel de riesgo
     */
    protected $focusActions = [
        'sin_riesgo'      => 'Mantener programas actuales de bienestar',
        'riesgo_bajo'     => 'Continuar con programas de prevención',
        'riesgo_medio'    => 'Reforzar programas de intervención',
        'riesgo_alto'     => 'Intervención prioritaria requerida',
        'riesgo_muy_alto' => 'Intervención inmediata obligatoria',
    ];

    protected $resultsFormaA = [];
    protected $resultsFormaB = [];
    protected $gaugeGenerator;

    /**
     * Obtiene el baremo de una dimensión desde las librerías Scoring
     * Esta es la fuente única de verdad para los baremos (Tablas 29 y 30)
     *
     * @param string $dimensionCodigo Código de la dimensión
     * @param string $forma 'A' o 'B'
     * @return array Baremo de la dimensión
     */
    protected function getBaremoDimension($dimensionCodigo, $forma)
    {
        // Mapear código del controlador al código de las librerías
        $codigoLibreria = $this->mapeoCodigosDimensiones[$dimensionCodigo] ?? $dimensionCodigo;

        if ($forma === 'A') {
            return IntralaboralAScoring::getBaremoDimension($codigoLibreria);
        } else {
            return IntralaboralBScoring::getBaremoDimension($codigoLibreria);
        }
    }

    /**
     * Preview HTML de la sección
     */
    public function preview($batteryServiceId)
    {
        $this->initializeData($batteryServiceId);
        $this->loadResults();
        $this->gaugeGenerator = new PdfGaugeGenerator();

        $html = $this->renderIntro();
        $html .= $this->renderAllDimensiones();

        return $this->generatePreview($html, 'Dimensiones Intralaborales - Preview');
    }

    /**
     * Descargar PDF de la sección
     */
    public function download($batteryServiceId)
    {
        $this->initializeData($batteryServiceId);
        $this->loadResults();
        $this->gaugeGenerator = new PdfGaugeGenerator();

        $html = $this->renderIntro();
        $html .= $this->renderAllDimensiones();

        $filename = 'dimensiones_intralaborales_' . $batteryServiceId . '.pdf';
        return $this->generatePdf($html, $filename);
    }

    /**
     * Carga resultados de calculated_results para ambas formas
     */
    protected function loadResults()
    {
        $db = \Config\Database::connect();

        // Forma A
        $queryA = $db->query("
            SELECT cr.*, w.name as worker_name, w.area, w.position
            FROM calculated_results cr
            JOIN workers w ON cr.worker_id = w.id
            WHERE cr.battery_service_id = ?
            AND cr.intralaboral_form_type = 'A'
        ", [$this->batteryServiceId]);
        $this->resultsFormaA = $queryA->getResultArray();

        // Forma B
        $queryB = $db->query("
            SELECT cr.*, w.name as worker_name, w.area, w.position
            FROM calculated_results cr
            JOIN workers w ON cr.worker_id = w.id
            WHERE cr.battery_service_id = ?
            AND cr.intralaboral_form_type = 'B'
        ", [$this->batteryServiceId]);
        $this->resultsFormaB = $queryB->getResultArray();
    }

    /**
     * Renderiza la página introductoria
     * NOTA: No usar display: flex - usar display: table/table-cell
     */
    protected function renderIntro()
    {
        // Contar dimensiones
        $dimFormaA = 0;
        $dimFormaB = 0;
        foreach ($this->dimensiones as $dim) {
            if ($dim['forma_A']) $dimFormaA++;
            if ($dim['forma_B']) $dimFormaB++;
        }
        $totalPaginas = $dimFormaA + $dimFormaB;

        $html = '
<div style="text-align: center; margin-bottom: 20pt;">
    <h1 style="font-size: 16pt; color: #006699; margin: 0 0 10pt 0; border-bottom: 2pt solid #006699; padding-bottom: 8pt;">
        SECCIÓN<br>Dimensiones Intralaborales
    </h1>
</div>

<p style="font-size: 10pt; text-align: justify; margin-bottom: 15pt;">
    Esta sección presenta el análisis detallado de cada dimensión del Cuestionario
    de Factores de Riesgo Psicosocial Intralaboral. Cada dimensión representa un aspecto específico
    del ambiente laboral que puede afectar la salud y el bienestar de los trabajadores.
</p>

<div style="background-color: #f5f5f5; border: 1pt solid #ddd; padding: 10pt; margin-bottom: 15pt;">
    <p style="font-weight: bold; color: #006699; margin: 0 0 8pt 0;">Dimensiones por Dominio:</p>
    <table style="width: 100%; border: none; font-size: 9pt;">
        <tr>
            <td style="border: none; padding: 3pt;">• Liderazgo y Relaciones Sociales:</td>
            <td style="border: none; padding: 3pt;">4 dim. (Forma A) / 3 dim. (Forma B)</td>
        </tr>
        <tr>
            <td style="border: none; padding: 3pt;">• Control sobre el Trabajo:</td>
            <td style="border: none; padding: 3pt;">5 dimensiones</td>
        </tr>
        <tr>
            <td style="border: none; padding: 3pt;">• Demandas del Trabajo:</td>
            <td style="border: none; padding: 3pt;">8 dim. (Forma A) / 6 dim. (Forma B)</td>
        </tr>
        <tr>
            <td style="border: none; padding: 3pt;">• Recompensas:</td>
            <td style="border: none; padding: 3pt;">2 dimensiones</td>
        </tr>
    </table>
</div>

<!-- Resumen usando table-cell en lugar de flex -->
<table style="width: 100%; border-collapse: collapse; margin-bottom: 15pt;">
    <tr>
        <td style="width: 33%; text-align: center; background-color: #006699; color: white; padding: 10pt; border: 1pt solid #004466;">
            <span style="font-size: 20pt; font-weight: bold;">' . $dimFormaA . '</span><br>
            <span style="font-size: 9pt;">Dimensiones Forma A</span>
        </td>
        <td style="width: 33%; text-align: center; background-color: #0088cc; color: white; padding: 10pt; border: 1pt solid #006699;">
            <span style="font-size: 20pt; font-weight: bold;">' . $dimFormaB . '</span><br>
            <span style="font-size: 9pt;">Dimensiones Forma B</span>
        </td>
        <td style="width: 33%; text-align: center; background-color: #00aaee; color: white; padding: 10pt; border: 1pt solid #0088cc;">
            <span style="font-size: 20pt; font-weight: bold;">' . $totalPaginas . '</span><br>
            <span style="font-size: 9pt;">Páginas Total</span>
        </td>
    </tr>
</table>

<div style="background-color: #fff3cd; border: 1pt solid #ffc107; padding: 8pt; margin-top: 15pt;">
    <p style="font-size: 9pt; margin: 0; color: #856404;">
        <strong>Nota:</strong> La Forma A tiene 3 dimensiones exclusivas: Relación con los Colaboradores,
        Exigencias de Responsabilidad del Cargo y Consistencia del Rol, aplicables solo a cargos
        con personal a cargo o funciones de jefatura.
    </p>
</div>

<div class="page-break"></div>';

        return $html;
    }

    /**
     * Renderiza el HTML de la sección (para el Orquestador)
     * Este método es público para ser usado por PdfEjecutivoOrchestrator
     */
    public function render($batteryServiceId)
    {
        if (empty($this->resultsFormaA) && empty($this->resultsFormaB)) {
            $this->initializeData($batteryServiceId);
            $this->loadResults();
            $this->gaugeGenerator = new PdfGaugeGenerator();
        }

        $html = $this->renderIntro();
        $html .= $this->renderAllDimensiones();

        return $html;
    }

    /**
     * Renderiza todas las páginas de dimensiones
     * Por cada dimensión: primero Forma A, luego Forma B (si aplica)
     */
    protected function renderAllDimensiones()
    {
        $html = '';
        $isFirst = true;

        foreach ($this->dimensiones as $codigo => $dimension) {
            // Forma A
            if ($dimension['forma_A']) {
                if (!$isFirst) {
                    $html .= '<div class="page-break"></div>';
                }
                $html .= $this->renderDimension($codigo, 'A');
                $isFirst = false;
            }

            // Forma B (si aplica)
            if ($dimension['forma_B']) {
                $html .= '<div class="page-break"></div>';
                $html .= $this->renderDimension($codigo, 'B');
            }
        }

        return $html;
    }

    /**
     * Renderiza una página de dimensión
     */
    protected function renderDimension($dimensionCodigo, $forma)
    {
        $dimension = $this->dimensiones[$dimensionCodigo];
        $baremo = $this->getBaremoDimension($dimensionCodigo, $forma);

        if ($baremo === null) {
            return $this->renderDimensionSinBaremo($dimension, $forma);
        }

        $results = ($forma === 'A') ? $this->resultsFormaA : $this->resultsFormaB;

        if (empty($results)) {
            return $this->renderDimensionSinDatos($dimension, $forma);
        }

        // Calcular promedio
        $puntajes = array_column($results, $dimension['campo_puntaje']);
        $puntajes = array_filter($puntajes, function($v) { return $v !== null && $v !== ''; });

        if (empty($puntajes)) {
            return $this->renderDimensionSinDatos($dimension, $forma);
        }

        $promedio = round(array_sum($puntajes) / count($puntajes), 2);
        $nivel = $this->getNivelFromPuntaje($promedio, $baremo);
        $nivelNombre = $this->getRiskName($nivel);
        $nivelColor = $this->getRiskColor($nivel);

        // Generar gauge
        $gaugeUri = $this->gaugeGenerator->generate($promedio, $baremo);

        // Calcular distribución
        $distribucion = $this->calculateDistribution($results, $dimension['campo_nivel']);
        $total = count($results);

        // Agrupar para barra: Alto+MuyAlto, Medio, Bajo+SinRiesgo
        $countAlto = $distribucion['riesgo_alto'] + $distribucion['riesgo_muy_alto'];
        $countMedio = $distribucion['riesgo_medio'];
        $countBajo = $distribucion['riesgo_bajo'] + $distribucion['sin_riesgo'];

        $pctAlto = $total > 0 ? round(($countAlto / $total) * 100) : 0;
        $pctMedio = $total > 0 ? round(($countMedio / $total) * 100) : 0;
        $pctBajo = $total > 0 ? round(($countBajo / $total) * 100) : 0;

        // Ajustar para que sume 100%
        $suma = $pctAlto + $pctMedio + $pctBajo;
        if ($suma !== 100 && $suma > 0) {
            $diff = 100 - $suma;
            if ($pctBajo > 0) $pctBajo += $diff;
            elseif ($pctMedio > 0) $pctMedio += $diff;
            else $pctAlto += $diff;
        }

        // Título según forma
        $tipoTrabajador = ($forma === 'A') ? 'Profesionales o de Jefatura' : 'Auxiliares u Operativos';
        $colorBorde = ($forma === 'A') ? '#006699' : '#FF6600';

        // Acción según nivel
        $accionNivel = $this->focusActions[$nivel] ?? 'Evaluación requerida';
        $verboAccion = $this->getVerboAccion($nivel);

        $html = '
<!-- ELEMENTO 1: Título -->
<h1 style="font-size: 13pt; color: #006699; margin: 0 0 3pt 0; padding-bottom: 4pt; border-bottom: 2pt solid ' . $colorBorde . ';">
    Dimensión: ' . esc($dimension['nombre']) . '
</h1>
<p style="font-size: 9pt; color: #666; text-align: center; margin: 0 0 8pt 0;">
    ' . esc($dimension['dominio']) . ' - Forma ' . $forma . ' (' . $tipoTrabajador . ')
</p>

<!-- ELEMENTO 2: Caja de definición -->
<div style="background-color: #f9f9f9; border: 1pt solid #ddd; padding: 6pt; margin-bottom: 8pt;">
    <p style="font-weight: bold; color: #006699; margin: 0 0 3pt 0; font-size: 8pt;">Definición:</p>
    <p style="font-size: 8pt; margin: 0; text-align: justify;">' . esc($dimension['definicion']) . '</p>
</div>

<!-- ELEMENTO 3: Gauge centrado -->
<div style="text-align: center; margin: 5pt 0;">
    <img src="' . $gaugeUri . '" style="width: 160pt; height: auto;" />

    <!-- ELEMENTO 4: Leyenda de convenciones -->
    <div style="font-size: 6pt; color: #666; margin: 2pt 0; line-height: 1.2;">
        SR=Sin Riesgo | RB=Riesgo Bajo | RM=Riesgo Medio | RA=Riesgo Alto | RMA=Riesgo Muy Alto
    </div>
</div>

<!-- ELEMENTO 5: Tabla de baremos -->
<table style="width: 100%; font-size: 7pt; border-collapse: collapse; margin: 5pt 0;">
    <tr>
        <td style="background: #4CAF50; color: white; text-align: center; padding: 2pt; border: 1pt solid #ccc;">Sin Riesgo</td>
        <td style="background: #8BC34A; color: white; text-align: center; padding: 2pt; border: 1pt solid #ccc;">Bajo</td>
        <td style="background: #FFEB3B; color: #333; text-align: center; padding: 2pt; border: 1pt solid #ccc;">Medio</td>
        <td style="background: #FF9800; color: white; text-align: center; padding: 2pt; border: 1pt solid #ccc;">Alto</td>
        <td style="background: #F44336; color: white; text-align: center; padding: 2pt; border: 1pt solid #ccc;">Muy Alto</td>
    </tr>
    <tr>
        <td style="background: #E8F5E9; text-align: center; padding: 2pt; border: 1pt solid #ccc; font-size: 6pt;">' . $baremo['sin_riesgo'][0] . '-' . $baremo['sin_riesgo'][1] . '</td>
        <td style="background: #F1F8E9; text-align: center; padding: 2pt; border: 1pt solid #ccc; font-size: 6pt;">' . $baremo['riesgo_bajo'][0] . '-' . $baremo['riesgo_bajo'][1] . '</td>
        <td style="background: #FFFDE7; text-align: center; padding: 2pt; border: 1pt solid #ccc; font-size: 6pt;">' . $baremo['riesgo_medio'][0] . '-' . $baremo['riesgo_medio'][1] . '</td>
        <td style="background: #FFF3E0; text-align: center; padding: 2pt; border: 1pt solid #ccc; font-size: 6pt;">' . $baremo['riesgo_alto'][0] . '-' . $baremo['riesgo_alto'][1] . '</td>
        <td style="background: #FFEBEE; text-align: center; padding: 2pt; border: 1pt solid #ccc; font-size: 6pt;">' . $baremo['riesgo_muy_alto'][0] . '-' . $baremo['riesgo_muy_alto'][1] . '</td>
    </tr>
</table>

<!-- ELEMENTO 6: Texto interpretativo -->
<p style="font-size: 8pt; text-align: justify; margin: 8pt 0;">
    Para el cuestionario Tipo <strong>' . $forma . '</strong> se evidencia que el nivel de riesgo psicosocial
    se encuentra con un valor de <strong>' . number_format($promedio, 2) . '</strong> denominándose
    <span style="background-color: ' . $nivelColor . '; color: ' . ($nivel === 'riesgo_medio' ? '#333' : '#fff') . '; padding: 1pt 4pt; font-weight: bold;">' . strtoupper($nivelNombre) . '</span>,
    por lo que se debe <strong>' . $verboAccion . '</strong> las intervenciones que se realicen para los
    cargos <strong>' . $tipoTrabajador . '</strong>.
</p>

<!-- ELEMENTO 7: Título subsección distribución -->
<p style="font-size: 9pt; font-weight: bold; color: #006699; margin: 8pt 0 4pt 0;">Porcentajes de distribución por Niveles de Riesgo:</p>

<!-- ELEMENTO 8: Barra de distribución -->
<table style="width: 100%; height: 16pt; border-collapse: collapse; margin-bottom: 4pt;">
    <tr>';

        if ($pctAlto > 0) {
            $html .= '<td style="width: ' . $pctAlto . '%; background-color: #F44336; text-align: center; color: white; font-size: 7pt; font-weight: bold; border: none;">' . $pctAlto . '%</td>';
        }
        if ($pctMedio > 0) {
            $html .= '<td style="width: ' . $pctMedio . '%; background-color: #FFEB3B; text-align: center; color: #333; font-size: 7pt; font-weight: bold; border: none;">' . $pctMedio . '%</td>';
        }
        if ($pctBajo > 0) {
            $html .= '<td style="width: ' . $pctBajo . '%; background-color: #4CAF50; text-align: center; color: white; font-size: 7pt; font-weight: bold; border: none;">' . $pctBajo . '%</td>';
        }

        $html .= '
    </tr>
</table>

<!-- Leyenda de la barra -->
<table style="width: 100%; font-size: 7pt; border: none; margin-bottom: 6pt;">
    <tr>
        <td style="border: none; text-align: left; color: #F44336;">■ Alto + Muy Alto: ' . $pctAlto . '% (' . $countAlto . ')</td>
        <td style="border: none; text-align: center; color: #DAA520;">■ Medio: ' . $pctMedio . '% (' . $countMedio . ')</td>
        <td style="border: none; text-align: right; color: #4CAF50;">■ Bajo + Sin Riesgo: ' . $pctBajo . '% (' . $countBajo . ')</td>
    </tr>
</table>

<!-- ELEMENTO 9: Texto de distribución con íconos -->
<p style="font-size: 8pt; text-align: justify; margin: 5pt 0;">
    Se evidencia que el <strong style="color: #F44336;">' . $pctAlto . '%</strong> de los encuestados con el cuestionario Tipo ' . $forma . '
    están en un nivel de riesgo Alto y Muy Alto, el siguiente <strong style="color: #DAA520;">' . $pctMedio . '%</strong> restante
    están en un nivel de riesgo Medio y el <strong style="color: #4CAF50;">' . $pctBajo . '%</strong> restante está en un riesgo Bajo y Sin Riesgo.
</p>

<!-- ELEMENTO 10: Caja de foco objetivo -->
<div style="border: 1pt solid #006699; background-color: #e8f4fc; padding: 5pt 7pt; margin-top: 6pt;">
    <p style="font-size: 8pt; margin: 0;">
        <span style="font-weight: bold; color: #006699;">Foco Objetivo:</span>
        Cargos ' . $tipoTrabajador . '<br>
        <span style="font-weight: bold; color: #006699;">Acción:</span> ' . $accionNivel . '
    </p>
</div>';

        // ELEMENTO 11: Tabla de áreas con riesgo alto y muy alto
        $areasRiesgo = $this->getAreasEnRiesgoAlto($results, $dimension['campo_nivel']);
        if (!empty($areasRiesgo)) {
            $html .= '
<p style="font-size: 8pt; font-weight: bold; color: #006699; margin: 8pt 0 3pt 0;">Áreas con Riesgo Alto y Muy Alto:</p>
<table style="width: 100%; font-size: 7pt; border-collapse: collapse;">
    <tr>
        <th style="background: #006699; color: white; padding: 3pt; border: 1pt solid #333;">Área</th>
        <th style="background: #006699; color: white; padding: 3pt; border: 1pt solid #333;">Cargo</th>
        <th style="background: #006699; color: white; padding: 3pt; border: 1pt solid #333; width: 50pt;">Participantes</th>
        <th style="background: #006699; color: white; padding: 3pt; border: 1pt solid #333; width: 60pt;">Nivel</th>
    </tr>';

            $maxRows = min(count($areasRiesgo), 5); // Máximo 5 filas para caber en la página
            for ($i = 0; $i < $maxRows; $i++) {
                $area = $areasRiesgo[$i];
                $nivelAreaColor = $this->getRiskColor($area['nivel']);
                $html .= '
    <tr>
        <td style="padding: 2pt; border: 1pt solid #ccc;">' . esc($area['area']) . '</td>
        <td style="padding: 2pt; border: 1pt solid #ccc;">' . esc($area['cargo']) . '</td>
        <td style="padding: 2pt; border: 1pt solid #ccc; text-align: center;">' . $area['count'] . '</td>
        <td style="padding: 2pt; border: 1pt solid #ccc; text-align: center; background-color: ' . $nivelAreaColor . '; color: white;">' . $this->getRiskName($area['nivel']) . '</td>
    </tr>';
            }

            $html .= '</table>';
        }

        // ELEMENTO 12: Texto IA (si existe)
        $textoIA = $this->getTextoIA($dimensionCodigo, $forma);
        if (!empty($textoIA)) {
            $html .= '
<div style="border-left: 2pt solid #2196F3; background-color: #e3f2fd; padding: 5pt 7pt; margin-top: 6pt;">
    <p style="font-weight: bold; color: #1976D2; margin: 0 0 3pt 0; font-size: 8pt;">Análisis del Especialista SST:</p>
    <p style="font-size: 7.5pt; margin: 0; text-align: justify;">' . esc($textoIA) . '</p>
</div>';
        }

        return $html;
    }

    /**
     * Renderiza página cuando no hay datos
     */
    protected function renderDimensionSinDatos($dimension, $forma)
    {
        $tipoTrabajador = ($forma === 'A') ? 'Profesionales o de Jefatura' : 'Auxiliares u Operativos';
        $colorBorde = ($forma === 'A') ? '#006699' : '#FF6600';

        return '
<h1 style="font-size: 13pt; color: #006699; margin: 0 0 3pt 0; padding-bottom: 4pt; border-bottom: 2pt solid ' . $colorBorde . ';">
    Dimensión: ' . esc($dimension['nombre']) . '
</h1>
<p style="font-size: 9pt; color: #666; text-align: center; margin: 0 0 15pt 0;">
    ' . esc($dimension['dominio']) . ' - Forma ' . $forma . ' (' . $tipoTrabajador . ')
</p>

<div style="background-color: #f5f5f5; border: 1pt solid #ddd; padding: 20pt; text-align: center; margin: 40pt 0;">
    <p style="font-size: 12pt; color: #666; margin: 0;">
        No hay datos disponibles para esta dimensión<br>
        en el cuestionario Forma ' . $forma . '
    </p>
</div>';
    }

    /**
     * Renderiza página cuando no hay baremo disponible
     */
    protected function renderDimensionSinBaremo($dimension, $forma)
    {
        $tipoTrabajador = ($forma === 'A') ? 'Profesionales o de Jefatura' : 'Auxiliares u Operativos';

        return '
<h1 style="font-size: 13pt; color: #006699; margin: 0 0 3pt 0; padding-bottom: 4pt; border-bottom: 2pt solid #cc0000;">
    Dimensión: ' . esc($dimension['nombre']) . '
</h1>
<p style="font-size: 9pt; color: #666; text-align: center; margin: 0 0 15pt 0;">
    ' . esc($dimension['dominio']) . ' - Forma ' . $forma . ' (' . $tipoTrabajador . ')
</p>

<div style="background-color: #ffebee; border: 1pt solid #f44336; padding: 20pt; text-align: center; margin: 40pt 0;">
    <p style="font-size: 12pt; color: #c62828; margin: 0;">
        Esta dimensión no aplica para la Forma ' . $forma . '<br>
        <span style="font-size: 9pt;">(Exclusiva de Forma A para cargos con personal a cargo)</span>
    </p>
</div>';
    }

    /**
     * Obtiene áreas/cargos en riesgo alto y muy alto
     */
    protected function getAreasEnRiesgoAlto($results, $campoNivel)
    {
        $areasRiesgo = [];

        foreach ($results as $result) {
            $nivel = $result[$campoNivel] ?? '';
            if ($nivel === 'riesgo_alto' || $nivel === 'riesgo_muy_alto') {
                $key = ($result['area'] ?? 'Sin área') . '|' . ($result['position'] ?? 'Sin cargo') . '|' . $nivel;
                if (!isset($areasRiesgo[$key])) {
                    $areasRiesgo[$key] = [
                        'area' => $result['area'] ?? 'Sin área',
                        'cargo' => $result['position'] ?? 'Sin cargo',
                        'nivel' => $nivel,
                        'count' => 0
                    ];
                }
                $areasRiesgo[$key]['count']++;
            }
        }

        // Ordenar por count descendente
        usort($areasRiesgo, function($a, $b) {
            return $b['count'] - $a['count'];
        });

        return array_values($areasRiesgo);
    }

    /**
     * Obtiene verbo de acción según nivel de riesgo
     */
    protected function getVerboAccion($nivel)
    {
        $verbos = [
            'sin_riesgo'      => 'mantener',
            'riesgo_bajo'     => 'continuar',
            'riesgo_medio'    => 'reforzar',
            'riesgo_alto'     => 'priorizar',
            'riesgo_muy_alto' => 'implementar inmediatamente',
        ];
        return $verbos[$nivel] ?? 'evaluar';
    }

    /**
     * Obtiene texto de análisis IA si existe en report_sections
     */
    protected function getTextoIA($dimensionCodigo, $forma)
    {
        $db = \Config\Database::connect();

        // Primero obtener el report_id para este battery_service
        $reportQuery = $db->query("
            SELECT id FROM reports WHERE battery_service_id = ? LIMIT 1
        ", [$this->batteryServiceId]);
        $report = $reportQuery->getRowArray();

        if (!$report) {
            return null;
        }

        // Buscar el texto IA para esta dimensión
        $query = $db->query("
            SELECT ai_generated_text, consultant_comment
            FROM report_sections
            WHERE report_id = ?
            AND section_level = 'dimension'
            AND dimension_code = ?
            AND form_type = ?
            LIMIT 1
        ", [$report['id'], $dimensionCodigo, $forma]);

        $result = $query->getRowArray();

        // Preferir comentario del consultor, si no, texto IA
        if (!empty($result['consultant_comment'])) {
            return $result['consultant_comment'];
        }
        return $result['ai_generated_text'] ?? null;
    }
}
