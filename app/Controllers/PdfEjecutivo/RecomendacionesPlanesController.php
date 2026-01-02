<?php

namespace App\Controllers\PdfEjecutivo;

use App\Models\MaxRiskResultModel;

/**
 * Controlador de Recomendaciones y Planes de Acción para el PDF Ejecutivo
 *
 * Genera páginas dinámicas:
 * - 1 página separador/intro
 * - 1-2 páginas resumen (Forma A y/o Forma B)
 * - N páginas de detalle (1 por cada dimensión en riesgo ALTO o MUY ALTO)
 *
 * Color temático: Naranja #FF6B35
 */
class RecomendacionesPlanesController extends PdfEjecutivoBaseController
{
    protected $maxRiskModel;
    /**
     * Color temático para Recomendaciones (naranja)
     */
    protected $themeColor = '#FF6B35';
    protected $themeColorLight = '#FFF3E0';
    protected $themeColorDark = '#E65100';

    /**
     * Colores por tipo de cuestionario
     */
    protected $typeColors = [
        'intralaboral' => '#0077B6',
        'extralaboral' => '#00A86B',
        'estres'       => '#9C27B0',
    ];

    /**
     * Colores por nivel de riesgo
     */
    protected $riskColors = [
        'riesgo_muy_alto' => '#F44336',
        'riesgo_alto'     => '#FF9800',
        'riesgo_medio'    => '#FFEB3B',
        'riesgo_bajo'     => '#8BC34A',
        'sin_riesgo'      => '#4CAF50',
    ];

    /**
     * Mapeo de dimensiones intralaborales a códigos de action_plans
     */
    protected $dimensionMappingIntra = [
        // Liderazgo
        'caracteristicas_liderazgo'       => 'caracteristicas_liderazgo',
        'relaciones_sociales'             => 'relaciones_sociales_trabajo',
        'retroalimentacion'               => 'retroalimentacion_desempeno',
        'relacion_colaboradores'          => 'relacion_colaboradores',
        // Control
        'claridad_rol'                    => 'claridad_rol',
        'capacitacion'                    => 'capacitacion',
        'participacion_cambio'            => 'participacion_manejo_cambio',
        'oportunidades_desarrollo'        => 'oportunidades_desarrollo_habilidades',
        'control_autonomia'               => 'control_autonomia_trabajo',
        // Demandas
        'demandas_ambientales'            => 'demandas_ambientales_esfuerzo_fisico',
        'demandas_emocionales'            => 'demandas_emocionales',
        'demandas_cuantitativas'          => 'demandas_cuantitativas',
        'influencia_extralaboral'         => 'influencia_trabajo_entorno_extralaboral',
        'exigencias_responsabilidad'      => 'exigencias_responsabilidad_cargo',
        'demandas_carga_mental'           => 'demandas_carga_mental',
        'consistencia_rol'                => 'consistencia_rol',
        'demandas_jornada'                => 'demandas_jornada_trabajo',
        // Recompensas
        'recompensas_pertenencia'         => 'recompensas_pertenencia_organizacion',
        'reconocimiento_compensacion'     => 'reconocimiento_compensacion',
    ];

    /**
     * Mapeo de dimensiones extralaborales
     */
    protected $dimensionMappingExtra = [
        'tiempo_fuera_trabajo'       => 'tiempo_fuera_trabajo',
        'relaciones_familiares'      => 'relaciones_familiares',
        'comunicacion_relaciones'    => 'comunicacion_relaciones_interpersonales',
        'situacion_economica'        => 'situacion_economica_familiar',
        'caracteristicas_vivienda'   => 'caracteristicas_vivienda_entorno',
        'influencia_entorno'         => 'influencia_entorno_extralaboral',
        'desplazamiento'             => 'desplazamiento_vivienda_trabajo',
    ];

    /**
     * Nombres de dimensiones intralaborales
     */
    protected $dimensionNamesIntra = [
        'caracteristicas_liderazgo'   => 'Características del Liderazgo',
        'relaciones_sociales'         => 'Relaciones Sociales en el Trabajo',
        'retroalimentacion'           => 'Retroalimentación del Desempeño',
        'relacion_colaboradores'      => 'Relación con los Colaboradores',
        'claridad_rol'                => 'Claridad de Rol',
        'capacitacion'                => 'Capacitación',
        'participacion_cambio'        => 'Participación y Manejo del Cambio',
        'oportunidades_desarrollo'    => 'Oportunidades de Desarrollo',
        'control_autonomia'           => 'Control y Autonomía sobre el Trabajo',
        'demandas_ambientales'        => 'Demandas Ambientales y Esfuerzo Físico',
        'demandas_emocionales'        => 'Demandas Emocionales',
        'demandas_cuantitativas'      => 'Demandas Cuantitativas',
        'influencia_extralaboral'     => 'Influencia del Trabajo sobre el Entorno Extralaboral',
        'exigencias_responsabilidad'  => 'Exigencias de Responsabilidad del Cargo',
        'demandas_carga_mental'       => 'Demandas de Carga Mental',
        'consistencia_rol'            => 'Consistencia del Rol',
        'demandas_jornada'            => 'Demandas de la Jornada de Trabajo',
        'recompensas_pertenencia'     => 'Recompensas Derivadas de la Pertenencia',
        'reconocimiento_compensacion' => 'Reconocimiento y Compensación',
    ];

    /**
     * Nombres de dimensiones extralaborales
     */
    protected $dimensionNamesExtra = [
        'tiempo_fuera_trabajo'     => 'Tiempo Fuera del Trabajo',
        'relaciones_familiares'    => 'Relaciones Familiares',
        'comunicacion_relaciones'  => 'Comunicación y Relaciones Interpersonales',
        'situacion_economica'      => 'Situación Económica del Grupo Familiar',
        'caracteristicas_vivienda' => 'Características de la Vivienda y su Entorno',
        'influencia_entorno'       => 'Influencia del Entorno Extralaboral',
        'desplazamiento'           => 'Desplazamiento Vivienda – Trabajo – Vivienda',
    ];

    protected $resultsFormaA = [];
    protected $resultsFormaB = [];
    protected $actionPlans = [];

    /**
     * Constructor - Inicializa el modelo de max_risk_results
     */
    public function __construct()
    {
        parent::__construct();
        $this->maxRiskModel = new MaxRiskResultModel();
    }

    /**
     * Preview HTML de la sección
     */
    public function preview($batteryServiceId)
    {
        $this->initializeData($batteryServiceId);
        $this->loadResults();
        $this->loadActionPlans();

        $html = $this->render($batteryServiceId);

        return $this->generatePreview($html, 'Recomendaciones y Planes de Acción - Preview');
    }

    /**
     * Descargar PDF de la sección
     */
    public function download($batteryServiceId)
    {
        $this->initializeData($batteryServiceId);
        $this->loadResults();
        $this->loadActionPlans();

        $html = $this->render($batteryServiceId);

        $filename = 'recomendaciones_planes_' . $batteryServiceId . '.pdf';
        return $this->generatePdf($html, $filename);
    }

    /**
     * Renderiza el HTML de la sección (para el Orquestador)
     */
    public function render($batteryServiceId)
    {
        if (empty($this->resultsFormaA) && empty($this->resultsFormaB)) {
            $this->initializeData($batteryServiceId);
            $this->loadResults();
            $this->loadActionPlans();
        }

        $html = $this->renderSeparador();

        // Resumen Forma A
        if (!empty($this->resultsFormaA)) {
            $html .= '<div class="page-break"></div>';
            $html .= $this->renderResumen('A');
        }

        // Resumen Forma B
        if (!empty($this->resultsFormaB)) {
            $html .= '<div class="page-break"></div>';
            $html .= $this->renderResumen('B');
        }

        // Páginas de detalle para dimensiones en riesgo alto/muy alto
        $html .= $this->renderPaginasDetalle();

        return $html;
    }

    /**
     * Carga resultados de calculated_results
     */
    protected function loadResults()
    {
        $db = \Config\Database::connect();

        $queryA = $db->query("
            SELECT cr.*, w.name as worker_name, w.area, w.position
            FROM calculated_results cr
            JOIN workers w ON cr.worker_id = w.id
            WHERE cr.battery_service_id = ?
            AND cr.intralaboral_form_type = 'A'
        ", [$this->batteryServiceId]);
        $this->resultsFormaA = $queryA->getResultArray();

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
     * Carga todos los planes de acción
     */
    protected function loadActionPlans()
    {
        $db = \Config\Database::connect();
        $query = $db->query("SELECT * FROM action_plans");
        $results = $query->getResultArray();

        foreach ($results as $plan) {
            $this->actionPlans[$plan['dimension_code']] = $plan;
        }
    }

    /**
     * Renderiza la página separador/intro
     */
    protected function renderSeparador()
    {
        return '
<div style="text-align: center; margin-bottom: 15pt;">
    <h1 style="font-size: 16pt; color: ' . $this->themeColor . '; margin: 0 0 8pt 0; border-bottom: 2pt solid ' . $this->themeColor . '; padding-bottom: 8pt;">
        SECCIÓN<br>RECOMENDACIONES Y PLANES DE ACCIÓN
    </h1>
</div>

<p style="font-size: 10pt; text-align: justify; margin-bottom: 15pt; line-height: 1.5;">
    Esta sección presenta las recomendaciones y planes de acción para las dimensiones
    que presentan niveles de riesgo medio, alto o muy alto, priorizando aquellas
    que requieren intervención inmediata según la Resolución 2764 de 2022.
</p>

<div style="background-color: #f5f5f5; border: 1pt solid #ddd; padding: 12pt; margin-bottom: 20pt;">
    <p style="font-weight: bold; color: ' . $this->themeColor . '; margin: 0 0 10pt 0; font-size: 11pt;">Niveles de Prioridad:</p>

    <table style="width: 100%; border-collapse: collapse; font-size: 10pt;">
        <tr>
            <td style="padding: 8pt; border: none; width: 30%;">
                <span style="background: #F44336; color: white; padding: 3pt 8pt; font-weight: bold;">MUY ALTO</span>
            </td>
            <td style="padding: 8pt; border: none;">
                Intervención inmediata requerida - Acciones correctivas urgentes
            </td>
        </tr>
        <tr>
            <td style="padding: 8pt; border: none;">
                <span style="background: #FF9800; color: white; padding: 3pt 8pt; font-weight: bold;">ALTO</span>
            </td>
            <td style="padding: 8pt; border: none;">
                Intervención prioritaria - Plan de acción a corto plazo
            </td>
        </tr>
        <tr>
            <td style="padding: 8pt; border: none;">
                <span style="background: #FFEB3B; color: #333; padding: 3pt 8pt; font-weight: bold;">MEDIO</span>
            </td>
            <td style="padding: 8pt; border: none;">
                Observación y seguimiento - Acciones preventivas
            </td>
        </tr>
    </table>
</div>

<!-- Resumen visual usando table-cell -->
<table style="width: 100%; border-collapse: collapse; margin-bottom: 20pt;">
    <tr>
        <td style="width: 33%; text-align: center; background-color: ' . $this->themeColorDark . '; color: white; padding: 12pt; border: 1pt solid ' . $this->themeColor . ';">
            <span style="font-size: 24pt; font-weight: bold;">6</span><br>
            <span style="font-size: 9pt;">Meses de<br>Intervención</span>
        </td>
        <td style="width: 34%; text-align: center; background-color: ' . $this->themeColor . '; color: white; padding: 12pt; border: 1pt solid ' . $this->themeColorDark . ';">
            <span style="font-size: 24pt; font-weight: bold;">N</span><br>
            <span style="font-size: 9pt;">Dimensiones<br>en Riesgo</span>
        </td>
        <td style="width: 33%; text-align: center; background-color: #FF8C5A; color: white; padding: 12pt; border: 1pt solid ' . $this->themeColor . ';">
            <span style="font-size: 24pt; font-weight: bold;">✓</span><br>
            <span style="font-size: 9pt;">Planes de<br>Acción</span>
        </td>
    </tr>
</table>

<div style="background-color: #fff3cd; border: 1pt solid #ffc107; padding: 10pt;">
    <p style="font-size: 9pt; margin: 0; color: #856404;">
        <strong>Nota:</strong> Las siguientes páginas presentan primero un resumen de las dimensiones
        en riesgo por cada forma (A y B), seguido de los planes de acción detallados para cada
        dimensión con nivel ALTO o MUY ALTO.
    </p>
</div>';
    }

    /**
     * Renderiza la página de resumen para una forma
     */
    protected function renderResumen($forma)
    {
        $results = ($forma === 'A') ? $this->resultsFormaA : $this->resultsFormaB;
        $tipoTrabajador = ($forma === 'A') ? 'Profesionales / Jefaturas' : 'Auxiliares / Operativos';
        $badgeColor = ($forma === 'A') ? '#0077B6' : '#FF6B35';

        // Identificar dimensiones en riesgo
        $dimensionesRiesgo = $this->identificarDimensionesEnRiesgo($results, $forma);

        // Contar por tipo de cuestionario
        $countIntra = 0;
        $countExtra = 0;
        $countEstres = 0;
        $countPrioridad = 0;

        foreach ($dimensionesRiesgo as $dim) {
            if ($dim['tipo'] === 'intralaboral') $countIntra++;
            elseif ($dim['tipo'] === 'extralaboral') $countExtra++;
            elseif ($dim['tipo'] === 'estres') $countEstres++;

            if (in_array($dim['nivel'], ['riesgo_alto', 'riesgo_muy_alto'])) {
                $countPrioridad++;
            }
        }

        $html = '
<h1 style="font-size: 14pt; color: ' . $this->themeColor . '; margin: 0 0 5pt 0; padding-bottom: 5pt; border-bottom: 2pt solid ' . $this->themeColor . ';">
    Resumen de Recomendaciones
</h1>
<p style="font-size: 10pt; text-align: center; margin: 0 0 10pt 0;">
    <span style="background: ' . $badgeColor . '; color: white; padding: 3pt 10pt; font-weight: bold;">
        FORMA ' . $forma . ' - ' . $tipoTrabajador . '
    </span>
</p>

<p style="font-size: 9pt; text-align: justify; margin-bottom: 10pt;">
    A continuación se presentan las dimensiones que requieren atención según los niveles de riesgo
    identificados en la evaluación. Las recomendaciones están organizadas por prioridad de intervención.
</p>';

        if (empty($dimensionesRiesgo)) {
            $html .= '
<div style="background-color: #e8f5e9; border: 1pt solid #4CAF50; padding: 20pt; text-align: center; margin: 30pt 0;">
    <p style="font-size: 14pt; color: #2E7D32; margin: 0;">
        <strong>¡Excelente!</strong><br>
        No se identificaron dimensiones con riesgo medio, alto o muy alto<br>
        para la Forma ' . $forma . '.
    </p>
</div>';
            return $html;
        }

        // Tabla de dimensiones en riesgo
        $html .= '
<table style="width: 100%; font-size: 8pt; border-collapse: collapse; margin-bottom: 10pt;">
    <tr>
        <th style="background: ' . $this->themeColor . '; color: white; padding: 4pt; border: 1pt solid #333; width: 20pt;">#</th>
        <th style="background: ' . $this->themeColor . '; color: white; padding: 4pt; border: 1pt solid #333;">Dimensión</th>
        <th style="background: ' . $this->themeColor . '; color: white; padding: 4pt; border: 1pt solid #333; width: 60pt;">Tipo</th>
        <th style="background: ' . $this->themeColor . '; color: white; padding: 4pt; border: 1pt solid #333; width: 60pt;">Puntaje</th>
        <th style="background: ' . $this->themeColor . '; color: white; padding: 4pt; border: 1pt solid #333; width: 60pt;">Evaluados</th>
        <th style="background: ' . $this->themeColor . '; color: white; padding: 4pt; border: 1pt solid #333; width: 55pt;">Nivel</th>
    </tr>';

        $i = 1;
        foreach ($dimensionesRiesgo as $dim) {
            $nivelColor = $this->riskColors[$dim['nivel']] ?? '#999';
            $nivelNombre = $this->getNivelNombre($dim['nivel']);
            $tipoColor = $this->typeColors[$dim['tipo']] ?? '#666';
            $textColor = ($dim['nivel'] === 'riesgo_medio') ? '#333' : 'white';

            // Formatear puntaje con forma de origen (como en heatmap y web)
            $puntajeDisplay = number_format($dim['puntaje'], 2);
            if ($dim['has_both_forms'] ?? false) {
                // Mostrar puntaje con forma de origen
                $otherForm = $dim['worst_form'] === 'A' ? 'B' : 'A';
                $otherScore = $dim['worst_form'] === 'A' ? $dim['form_b_score'] : $dim['form_a_score'];

                $puntajeDisplay = number_format($dim['worst_score'], 2) . ' (' . $dim['worst_form'] . ')';
                if ($otherScore !== null) {
                    $puntajeDisplay .= '<br><span style="font-size: 6pt; color: #666;">' . $otherForm . ': ' . number_format($otherScore, 2) . '</span>';
                }
            }

            // Formatear evaluados con desglose A/B
            $evaluadosDisplay = $dim['worker_count'] ?? 0;
            if ($dim['has_both_forms'] ?? false) {
                $countA = $dim['form_a_count'] ?? 0;
                $countB = $dim['form_b_count'] ?? 0;
                $evaluadosDisplay = 'A: ' . $countA . ' | B: ' . $countB;
            }

            $html .= '
    <tr>
        <td style="padding: 3pt; border: 1pt solid #ccc; text-align: center;">' . $i . '</td>
        <td style="padding: 3pt; border: 1pt solid #ccc;">' . esc($dim['nombre']) . '</td>
        <td style="padding: 3pt; border: 1pt solid #ccc; text-align: center;">
            <span style="background: ' . $tipoColor . '; color: white; padding: 1pt 4pt; font-size: 7pt;">' . ucfirst($dim['tipo']) . '</span>
        </td>
        <td style="padding: 3pt; border: 1pt solid #ccc; text-align: center;">' . $puntajeDisplay . '</td>
        <td style="padding: 3pt; border: 1pt solid #ccc; text-align: center; font-size: 7pt;">' . $evaluadosDisplay . '</td>
        <td style="padding: 3pt; border: 1pt solid #ccc; text-align: center; background: ' . $nivelColor . '; color: ' . $textColor . '; font-weight: bold;">' . $nivelNombre . '</td>
    </tr>';
            $i++;
        }

        $html .= '</table>';

        // Tarjetas resumen por área
        $html .= '
<table style="width: 100%; border-collapse: collapse; margin: 10pt 0;">
    <tr>
        <td style="width: 33%; padding: 5pt; vertical-align: top;">
            <div style="border: 2pt solid #0077B6; padding: 8pt; text-align: center;">
                <p style="font-size: 9pt; font-weight: bold; color: #0077B6; margin: 0 0 5pt 0;">Intralaboral</p>
                <p style="font-size: 16pt; font-weight: bold; margin: 0; color: #0077B6;">' . $countIntra . '</p>
                <p style="font-size: 7pt; color: #666; margin: 3pt 0 0 0;">dimensiones en riesgo</p>
            </div>
        </td>
        <td style="width: 33%; padding: 5pt; vertical-align: top;">
            <div style="border: 2pt solid #00A86B; padding: 8pt; text-align: center;">
                <p style="font-size: 9pt; font-weight: bold; color: #00A86B; margin: 0 0 5pt 0;">Extralaboral</p>
                <p style="font-size: 16pt; font-weight: bold; margin: 0; color: #00A86B;">' . $countExtra . '</p>
                <p style="font-size: 7pt; color: #666; margin: 3pt 0 0 0;">dimensiones en riesgo</p>
            </div>
        </td>
        <td style="width: 33%; padding: 5pt; vertical-align: top;">
            <div style="border: 2pt solid #9C27B0; padding: 8pt; text-align: center;">
                <p style="font-size: 9pt; font-weight: bold; color: #9C27B0; margin: 0 0 5pt 0;">Estrés</p>
                <p style="font-size: 16pt; font-weight: bold; margin: 0; color: #9C27B0;">' . $countEstres . '</p>
                <p style="font-size: 7pt; color: #666; margin: 3pt 0 0 0;">en riesgo</p>
            </div>
        </td>
    </tr>
</table>';

        // Nota final
        $html .= '
<div style="background-color: ' . $this->themeColorLight . '; border: 1pt solid ' . $this->themeColor . '; padding: 8pt; margin-top: 10pt;">
    <p style="font-size: 8pt; margin: 0; color: #333;">
        <strong>Nota:</strong> Las dimensiones con nivel de riesgo ALTO y MUY ALTO requieren intervención prioritaria.
        En las siguientes páginas se detallan los planes de acción específicos para ' . $countPrioridad . ' dimensiones
        que requieren atención inmediata.
    </p>
</div>';

        return $html;
    }

    /**
     * Renderiza las páginas de detalle para dimensiones en riesgo alto/muy alto
     */
    protected function renderPaginasDetalle()
    {
        $html = '';

        // Combinar dimensiones de ambas formas que estén en riesgo alto/muy alto
        $dimensionesDetalle = [];

        if (!empty($this->resultsFormaA)) {
            $dimsA = $this->identificarDimensionesEnRiesgo($this->resultsFormaA, 'A');
            foreach ($dimsA as $dim) {
                if (in_array($dim['nivel'], ['riesgo_alto', 'riesgo_muy_alto'])) {
                    $dim['forma'] = 'A';
                    $dimensionesDetalle[] = $dim;
                }
            }
        }

        if (!empty($this->resultsFormaB)) {
            $dimsB = $this->identificarDimensionesEnRiesgo($this->resultsFormaB, 'B');
            foreach ($dimsB as $dim) {
                if (in_array($dim['nivel'], ['riesgo_alto', 'riesgo_muy_alto'])) {
                    $dim['forma'] = 'B';
                    $dimensionesDetalle[] = $dim;
                }
            }
        }

        // Ordenar por prioridad (muy alto primero)
        usort($dimensionesDetalle, function($a, $b) {
            $order = ['riesgo_muy_alto' => 0, 'riesgo_alto' => 1];
            return ($order[$a['nivel']] ?? 2) - ($order[$b['nivel']] ?? 2);
        });

        // Generar página por cada dimensión
        foreach ($dimensionesDetalle as $dim) {
            $html .= '<div class="page-break"></div>';
            $html .= $this->renderPaginaDetalle($dim);
        }

        return $html;
    }

    /**
     * Renderiza una página de detalle para una dimensión
     */
    protected function renderPaginaDetalle($dim)
    {
        $nivelColor = $this->riskColors[$dim['nivel']] ?? '#999';
        $nivelNombre = strtoupper($this->getNivelNombre($dim['nivel']));
        $tipoColor = $this->typeColors[$dim['tipo']] ?? '#666';
        $tipoTrabajador = ($dim['forma'] === 'A') ? 'Profesionales / Jefaturas' : 'Auxiliares / Operativos';
        $badgeColor = ($dim['forma'] === 'A') ? '#0077B6' : '#FF6B35';

        // Obtener plan de acción
        $planCode = $this->getPlanCode($dim['codigo'], $dim['tipo']);
        $plan = $this->actionPlans[$planCode] ?? null;

        $html = '
<!-- Encabezado con degradado -->
<div style="background-color: ' . $nivelColor . '; color: white; padding: 10pt; margin-bottom: 10pt;">
    <table style="width: 100%; border: none;">
        <tr>
            <td style="border: none; vertical-align: middle;">
                <p style="font-size: 9pt; margin: 0 0 3pt 0; opacity: 0.9;">Plan de Acción - ' . ucfirst($dim['tipo']) . '</p>
                <p style="font-size: 13pt; font-weight: bold; margin: 0;">' . esc($dim['nombre']) . '</p>
            </td>
            <td style="border: none; text-align: right; vertical-align: middle; width: 150pt;">
                <span style="background: rgba(255,255,255,0.2); padding: 3pt 8pt; font-weight: bold; font-size: 9pt;">' . $nivelNombre . '</span><br>
                <span style="font-size: 8pt;">Puntaje: ' . number_format($dim['puntaje'], 1) . ' | ' . $dim['porcentaje_riesgo'] . '% en riesgo</span>
            </td>
        </tr>
    </table>
</div>

<p style="text-align: center; margin: 0 0 10pt 0;">
    <span style="background: ' . $badgeColor . '; color: white; padding: 2pt 8pt; font-size: 8pt;">
        FORMA ' . $dim['forma'] . ' - ' . $tipoTrabajador . '
    </span>
</p>';

        if (!$plan) {
            $html .= '
<div style="background-color: #fff3cd; border: 1pt solid #ffc107; padding: 15pt; text-align: center; margin: 20pt 0;">
    <p style="font-size: 11pt; color: #856404; margin: 0;">
        Plan de acción no disponible para esta dimensión.<br>
        <span style="font-size: 9pt;">Código buscado: ' . esc($planCode) . '</span>
    </p>
</div>';
            return $html;
        }

        // Introducción
        $html .= '
<div style="background-color: #f9f9f9; border: 1pt solid #ddd; padding: 8pt; margin-bottom: 10pt;">
    <p style="font-weight: bold; color: ' . $tipoColor . '; margin: 0 0 5pt 0; font-size: 9pt;">Descripción:</p>
    <p style="font-size: 8pt; margin: 0; text-align: justify; line-height: 1.4;">' . esc($plan['introduction']) . '</p>
</div>';

        // Objetivos
        $objectives = json_decode($plan['objectives'], true);
        if (!empty($objectives)) {
            $html .= '
<p style="font-weight: bold; color: ' . $tipoColor . '; margin: 10pt 0 5pt 0; font-size: 9pt;">Objetivos:</p>
<ul style="font-size: 8pt; margin: 0 0 10pt 15pt; padding: 0; line-height: 1.5;">';
            foreach ($objectives as $obj) {
                $objText = is_array($obj) ? ($obj['description'] ?? $obj['objetivo'] ?? '') : $obj;
                if (!empty($objText)) {
                    $html .= '<li style="margin-bottom: 3pt;">' . esc($objText) . '</li>';
                }
            }
            $html .= '</ul>';
        }

        // Plan de Actividades (6 meses)
        $activities = json_decode($plan['activities_6months'], true);
        if (!empty($activities)) {
            $html .= '
<p style="font-weight: bold; color: ' . $tipoColor . '; margin: 10pt 0 5pt 0; font-size: 9pt;">Plan de Actividades (6 meses):</p>';

            // Verificar si es formato por mes o lista plana
            if (isset($activities['mes_1']) || isset($activities['month_1'])) {
                // Formato por mes
                $html .= $this->renderActividadesPorMes($activities);
            } else {
                // Lista plana
                $html .= $this->renderActividadesLista($activities);
            }
        }

        // Bibliografía - máximo 5 referencias para que quepan en la página
        $bibliography = json_decode($plan['bibliography'] ?? '[]', true);
        if (!empty($bibliography)) {
            $html .= '
<p style="font-weight: bold; color: #666; margin: 8pt 0 4pt 0; font-size: 7pt;">Referencias:</p>
<div style="font-size: 6pt; color: #666; line-height: 1.3; word-wrap: break-word; overflow-wrap: break-word;">';
            $count = 0;
            foreach ($bibliography as $ref) {
                $refText = is_array($ref) ? ($ref['reference'] ?? $ref['referencia'] ?? '') : $ref;
                if (!empty($refText) && $count < 5) {
                    // Truncar referencias muy largas (máximo 200 caracteres)
                    if (strlen($refText) > 200) {
                        $refText = substr($refText, 0, 197) . '...';
                    }
                    $html .= '<p style="margin: 0 0 2pt 0; padding-left: 10pt; text-indent: -10pt;">• ' . esc($refText) . '</p>';
                    $count++;
                }
            }
            $html .= '</div>';
        }

        return $html;
    }

    /**
     * Renderiza actividades en formato tabla por mes
     */
    protected function renderActividadesPorMes($activities)
    {
        $html = '<table style="width: 100%; font-size: 7pt; border-collapse: collapse;">';

        for ($mes = 1; $mes <= 6; $mes++) {
            $key = "mes_$mes";
            $keyAlt = "month_$mes";
            $mesActivities = $activities[$key] ?? $activities[$keyAlt] ?? [];

            if (!empty($mesActivities)) {
                $html .= '
    <tr>
        <td style="background: ' . $this->themeColor . '; color: white; padding: 3pt 5pt; border: 1pt solid #ccc; width: 50pt; font-weight: bold; vertical-align: top;">
            Mes ' . $mes . '
        </td>
        <td style="padding: 3pt 5pt; border: 1pt solid #ccc; vertical-align: top;">';

                foreach ($mesActivities as $act) {
                    $actText = is_array($act) ? ($act['description'] ?? $act['actividad'] ?? '') : $act;
                    if (!empty($actText)) {
                        $html .= '• ' . esc($actText) . '<br>';
                    }
                }

                $html .= '</td>
    </tr>';
            }
        }

        $html .= '</table>';
        return $html;
    }

    /**
     * Renderiza actividades en formato lista
     */
    protected function renderActividadesLista($activities)
    {
        $html = '<table style="width: 100%; font-size: 7pt; border-collapse: collapse;">
    <tr>
        <th style="background: ' . $this->themeColor . '; color: white; padding: 3pt; border: 1pt solid #ccc; width: 20pt;">#</th>
        <th style="background: ' . $this->themeColor . '; color: white; padding: 3pt; border: 1pt solid #ccc;">Actividad</th>
        <th style="background: ' . $this->themeColor . '; color: white; padding: 3pt; border: 1pt solid #ccc; width: 50pt;">Plazo</th>
    </tr>';

        $i = 1;
        foreach ($activities as $act) {
            $actText = is_array($act) ? ($act['description'] ?? $act['actividad'] ?? '') : $act;
            $plazo = is_array($act) ? ($act['plazo'] ?? $act['deadline'] ?? '-') : '-';

            if (!empty($actText)) {
                $html .= '
    <tr>
        <td style="padding: 2pt; border: 1pt solid #ccc; text-align: center;">' . $i . '</td>
        <td style="padding: 2pt; border: 1pt solid #ccc;">' . esc($actText) . '</td>
        <td style="padding: 2pt; border: 1pt solid #ccc; text-align: center;">' . esc($plazo) . '</td>
    </tr>';
                $i++;
            }
        }

        $html .= '</table>';
        return $html;
    }

    /**
     * Identifica dimensiones en riesgo medio, alto o muy alto desde max_risk_results
     * Retorna solo las dimensiones de la forma especificada
     */
    protected function identificarDimensionesEnRiesgo($results, $forma)
    {
        // Obtener dimensiones desde max_risk_results
        $maxRiskResults = $this->maxRiskModel
            ->where('battery_service_id', $this->batteryServiceId)
            ->where('element_type', 'dimension')
            ->whereIn('worst_risk_level', ['riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto', 'medio', 'alto', 'muy_alto'])
            ->findAll();

        if (empty($maxRiskResults)) {
            return [];
        }

        $dimensionesRiesgo = [];

        // Mapeo de element_code a códigos internos
        $elementCodeMapping = [
            // INTRALABORAL
            'dim_caracteristicas_liderazgo' => 'caracteristicas_liderazgo',
            'dim_relaciones_sociales' => 'relaciones_sociales',
            'dim_retroalimentacion' => 'retroalimentacion',
            'dim_relacion_colaboradores' => 'relacion_colaboradores',
            'dim_claridad_rol' => 'claridad_rol',
            'dim_capacitacion' => 'capacitacion',
            'dim_participacion_manejo_cambio' => 'participacion_cambio',
            'dim_oportunidades_desarrollo' => 'oportunidades_desarrollo',
            'dim_control_autonomia' => 'control_autonomia',
            'dim_demandas_ambientales' => 'demandas_ambientales',
            'dim_demandas_emocionales' => 'demandas_emocionales',
            'dim_demandas_cuantitativas' => 'demandas_cuantitativas',
            'dim_influencia_trabajo_entorno_extralaboral' => 'influencia_extralaboral',
            'dim_demandas_responsabilidad' => 'exigencias_responsabilidad',
            'dim_demandas_carga_mental' => 'demandas_carga_mental',
            'dim_consistencia_rol' => 'consistencia_rol',
            'dim_demandas_jornada_trabajo' => 'demandas_jornada',
            'dim_recompensas_pertenencia' => 'recompensas_pertenencia',
            'dim_reconocimiento_compensacion' => 'reconocimiento_compensacion',

            // EXTRALABORAL
            'dim_tiempo_fuera' => 'tiempo_fuera_trabajo',
            'dim_relaciones_familiares_extra' => 'relaciones_familiares',
            'dim_comunicacion' => 'comunicacion_relaciones',
            'dim_situacion_economica' => 'situacion_economica',
            'dim_caracteristicas_vivienda' => 'caracteristicas_vivienda',
            'dim_influencia_entorno_extra' => 'influencia_entorno',
            'dim_desplazamiento' => 'desplazamiento',
        ];

        foreach ($maxRiskResults as $result) {
            // Determinar si esta dimensión tiene datos para la forma especificada
            $hasFormaA = !empty($result['form_a_score']);
            $hasFormaB = !empty($result['form_b_score']);

            // Solo incluir dimensiones que tengan datos para la forma solicitada
            if ($forma === 'A' && !$hasFormaA) continue;
            if ($forma === 'B' && !$hasFormaB) continue;

            $elementCode = $result['element_code'];
            $codigoInterno = $elementCodeMapping[$elementCode] ?? $elementCode;

            // Determinar el tipo de dimensión
            $tipo = 'intralaboral'; // default
            if (strpos($elementCode, 'extralaboral') !== false || strpos($elementCode, 'tiempo_fuera') !== false) {
                $tipo = 'extralaboral';
            } elseif (strpos($elementCode, 'estres') !== false) {
                $tipo = 'estres';
            }

            // Usar el puntaje de la forma específica si está disponible
            $puntaje = $result['worst_score'];
            $nivel = $result['worst_risk_level'];
            $workerCount = ($result['form_a_count'] ?? 0) + ($result['form_b_count'] ?? 0);

            if ($forma === 'A' && $hasFormaA) {
                $puntaje = $result['form_a_score'];
                $nivel = $result['form_a_risk_level'];
                $workerCount = $result['form_a_count'] ?? 0;
            } elseif ($forma === 'B' && $hasFormaB) {
                $puntaje = $result['form_b_score'];
                $nivel = $result['form_b_risk_level'];
                $workerCount = $result['form_b_count'] ?? 0;
            }

            $dimensionesRiesgo[] = [
                'codigo'            => $codigoInterno,
                'nombre'            => $result['element_name'],
                'tipo'              => $tipo,
                'puntaje'           => floatval($puntaje),
                'nivel'             => $nivel,
                'porcentaje_riesgo' => 100, // Ya están filtradas por riesgo
                'worker_count'      => $workerCount,
                'has_both_forms'    => $result['has_both_forms'],
                'worst_score'       => $result['worst_score'],
                'worst_form'        => $result['worst_form'],
                'form_a_score'      => $result['form_a_score'],
                'form_b_score'      => $result['form_b_score'],
                'form_a_count'      => $result['form_a_count'],
                'form_b_count'      => $result['form_b_count'],
            ];
        }

        // Ordenar por nivel de riesgo (muy alto primero)
        usort($dimensionesRiesgo, function($a, $b) {
            $order = ['riesgo_muy_alto' => 0, 'muy_alto' => 0, 'riesgo_alto' => 1, 'alto' => 1, 'riesgo_medio' => 2, 'medio' => 2];
            $orderA = $order[$a['nivel']] ?? 3;
            $orderB = $order[$b['nivel']] ?? 3;
            if ($orderA !== $orderB) return $orderA - $orderB;
            return $b['puntaje'] - $a['puntaje'];
        });

        return $dimensionesRiesgo;
    }

    /**
     * Convierte nivel de estrés a nomenclatura estándar
     */
    protected function convertirNivelEstres($nivel)
    {
        $mapping = [
            'muy_bajo'  => 'sin_riesgo',
            'bajo'      => 'riesgo_bajo',
            'medio'     => 'riesgo_medio',
            'alto'      => 'riesgo_alto',
            'muy_alto'  => 'riesgo_muy_alto',
        ];
        return $mapping[$nivel] ?? $nivel;
    }

    /**
     * Obtiene el nivel más frecuente de un array de niveles
     */
    protected function getNivelMasFrecuente($niveles)
    {
        if (empty($niveles)) return 'sin_riesgo';

        $counts = array_count_values($niveles);

        // Priorizar niveles más altos si hay empate
        $order = ['riesgo_muy_alto', 'riesgo_alto', 'riesgo_medio', 'riesgo_bajo', 'sin_riesgo'];

        foreach ($order as $nivel) {
            if (isset($counts[$nivel]) && $counts[$nivel] > 0) {
                // Si este nivel tiene al menos 30% de frecuencia, es el dominante
                if (($counts[$nivel] / count($niveles)) >= 0.3) {
                    return $nivel;
                }
            }
        }

        // Si no hay nivel dominante, retornar el más frecuente
        arsort($counts);
        return array_key_first($counts);
    }

    /**
     * Obtiene el código del plan de acción para una dimensión
     */
    protected function getPlanCode($dimensionCode, $tipo)
    {
        if ($tipo === 'intralaboral') {
            return $this->dimensionMappingIntra[$dimensionCode] ?? $dimensionCode;
        } elseif ($tipo === 'extralaboral') {
            return $this->dimensionMappingExtra[$dimensionCode] ?? $dimensionCode;
        } elseif ($tipo === 'estres') {
            return 'estres';
        }
        return $dimensionCode;
    }

    /**
     * Obtiene nombre legible del nivel de riesgo
     */
    protected function getNivelNombre($nivel)
    {
        $nombres = [
            'riesgo_muy_alto' => 'Muy Alto',
            'riesgo_alto'     => 'Alto',
            'riesgo_medio'    => 'Medio',
            'riesgo_bajo'     => 'Bajo',
            'sin_riesgo'      => 'Sin Riesgo',
        ];
        return $nombres[$nivel] ?? $nivel;
    }
}
