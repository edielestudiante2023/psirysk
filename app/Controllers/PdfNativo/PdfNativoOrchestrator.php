<?php

namespace App\Controllers\PdfNativo;

use App\Models\CalculatedResultModel;
use App\Models\ReportSectionModel;
use App\Libraries\PdfGaugeGenerator;
use App\Libraries\IntralaboralAScoring;
use App\Libraries\IntralaboralBScoring;
use App\Libraries\ExtralaboralScoring;
use App\Libraries\EstresScoring;

/**
 * Orquestador del PDF Nativo completo usando DomPDF
 * Genera el informe completo de batería de riesgo psicosocial
 * Diseñado nativamente para DomPDF con optimizaciones específicas
 *
 * BAREMOS: Migrado a Single Source of Truth - usa librerías autorizadas
 */
class PdfNativoOrchestrator extends PdfNativoBaseController
{
    protected $calculatedResultModel;
    protected $reportSectionModel;
    protected $gaugeGenerator;

    /**
     * Definición de dominios intralaborales
     */
    protected $dominios = [
        'liderazgo' => [
            'nombre' => 'Liderazgo y Relaciones Sociales en el Trabajo',
            'definicion' => 'Agrupa las dimensiones relacionadas con la calidad de las interacciones en el trabajo, la gestión del liderazgo, la retroalimentación del desempeño y las relaciones sociales que se establecen en el contexto laboral.',
            'campo_puntaje' => 'dom_liderazgo_puntaje',
            'campo_nivel' => 'dom_liderazgo_nivel',
            'dimensiones' => [
                'Características del Liderazgo',
                'Relaciones Sociales en el Trabajo',
                'Retroalimentación del Desempeño',
                'Relación con los Colaboradores (Solo Forma A)',
            ],
        ],
        'control' => [
            'nombre' => 'Control sobre el Trabajo',
            'definicion' => 'Evalúa la posibilidad que tiene el trabajador de influir y tomar decisiones sobre su trabajo, incluyendo la claridad de su rol, las oportunidades de desarrollo y participación en los cambios organizacionales.',
            'campo_puntaje' => 'dom_control_puntaje',
            'campo_nivel' => 'dom_control_nivel',
            'dimensiones' => [
                'Claridad del Rol',
                'Capacitación',
                'Participación y Manejo del Cambio',
                'Oportunidades de Desarrollo',
                'Control y Autonomía sobre el Trabajo',
            ],
        ],
        'demandas' => [
            'nombre' => 'Demandas del Trabajo',
            'definicion' => 'Comprende las exigencias físicas, emocionales, cuantitativas y cognitivas que el trabajo impone al individuo, así como las responsabilidades del cargo y las características de la jornada laboral.',
            'campo_puntaje' => 'dom_demandas_puntaje',
            'campo_nivel' => 'dom_demandas_nivel',
            'dimensiones' => [
                'Demandas Ambientales y de Esfuerzo Físico',
                'Demandas Emocionales',
                'Demandas Cuantitativas',
                'Influencia del Trabajo sobre el Entorno Extralaboral',
                'Exigencias de Responsabilidad del Cargo (Solo Forma A)',
                'Demandas de Carga Mental',
                'Consistencia del Rol (Solo Forma A)',
                'Demandas de la Jornada de Trabajo',
            ],
        ],
        'recompensas' => [
            'nombre' => 'Recompensas',
            'definicion' => 'Evalúa la retribución que el trabajador recibe a cambio de su esfuerzo laboral, incluyendo el reconocimiento, la compensación y el sentimiento de pertenencia a la organización.',
            'campo_puntaje' => 'dom_recompensas_puntaje',
            'campo_nivel' => 'dom_recompensas_nivel',
            'dimensiones' => [
                'Recompensas Derivadas de la Pertenencia',
                'Reconocimiento y Compensación',
            ],
        ],
    ];

    /**
     * Definición de dimensiones extralaborales
     * NOTA: Baremos migrados a ExtralaboralScoring (Single Source of Truth)
     */
    protected $dimensionesExtra = [
        'tiempo_fuera' => [
            'nombre' => 'Tiempo Fuera del Trabajo',
            'definicion' => 'Se refiere al tiempo que el individuo dedica a actividades diferentes a las laborales, como descansar, compartir con familia y amigos, atender responsabilidades personales o domésticas, realizar actividades de recreación y ocio.',
            'campo_puntaje' => 'extralaboral_tiempo_fuera_puntaje',
            'campo_nivel' => 'extralaboral_tiempo_fuera_nivel',
        ],
        'relaciones_familiares' => [
            'nombre' => 'Relaciones Familiares',
            'definicion' => 'Propiedades que caracterizan las interacciones del individuo con su núcleo familiar.',
            'campo_puntaje' => 'extralaboral_relaciones_familiares_puntaje',
            'campo_nivel' => 'extralaboral_relaciones_familiares_nivel',
        ],
        'comunicacion' => [
            'nombre' => 'Comunicación y Relaciones Interpersonales',
            'definicion' => 'Cualidades que caracterizan la comunicación e interacciones del individuo con sus allegados y amigos.',
            'campo_puntaje' => 'extralaboral_comunicacion_puntaje',
            'campo_nivel' => 'extralaboral_comunicacion_nivel',
        ],
        'situacion_economica' => [
            'nombre' => 'Situación Económica del Grupo Familiar',
            'definicion' => 'Trata de la disponibilidad de medios económicos para que el trabajador y su grupo familiar atiendan sus gastos básicos.',
            'campo_puntaje' => 'extralaboral_situacion_economica_puntaje',
            'campo_nivel' => 'extralaboral_situacion_economica_nivel',
        ],
        'caracteristicas_vivienda' => [
            'nombre' => 'Características de la Vivienda y de su Entorno',
            'definicion' => 'Se refiere a las condiciones de infraestructura, ubicación y entorno de las instalaciones físicas del lugar habitual de residencia del trabajador y de su grupo familiar.',
            'campo_puntaje' => 'extralaboral_caracteristicas_vivienda_puntaje',
            'campo_nivel' => 'extralaboral_caracteristicas_vivienda_nivel',
        ],
        'influencia_entorno' => [
            'nombre' => 'Influencia del Entorno Extralaboral sobre el Trabajo',
            'definicion' => 'Corresponde al influjo de las exigencias de los roles familiares y personales en el bienestar y en la actividad laboral del trabajador.',
            'campo_puntaje' => 'extralaboral_influencia_entorno_puntaje',
            'campo_nivel' => 'extralaboral_influencia_entorno_nivel',
        ],
        'desplazamiento' => [
            'nombre' => 'Desplazamiento Vivienda – Trabajo – Vivienda',
            'definicion' => 'Son las condiciones en que se realiza el traslado del trabajador desde su sitio de vivienda hasta su lugar de trabajo y viceversa. Comprende la facilidad, la comodidad del transporte y la duración del recorrido.',
            'campo_puntaje' => 'extralaboral_desplazamiento_puntaje',
            'campo_nivel' => 'extralaboral_desplazamiento_nivel',
        ],
    ];

    public function __construct()
    {
        $this->calculatedResultModel = new CalculatedResultModel();
        $this->reportSectionModel = new ReportSectionModel();
        $this->gaugeGenerator = new PdfGaugeGenerator();
    }

    // =========================================================================
    // BAREMOS - Métodos helper que llaman a las librerías (Single Source of Truth)
    // =========================================================================

    /**
     * Obtiene baremo intralaboral total según forma
     */
    protected function getBaremoIntraTotal(string $forma): array
    {
        return ($forma === 'A')
            ? IntralaboralAScoring::getBaremoTotal()
            : IntralaboralBScoring::getBaremoTotal();
    }

    /**
     * Obtiene baremo de dominio intralaboral según forma
     */
    protected function getBaremoDominio(string $domKey, string $forma): array
    {
        // Mapeo de claves cortas a claves de librería
        $mapeo = [
            'liderazgo' => 'liderazgo_relaciones_sociales',
            'control' => 'control',
            'demandas' => 'demandas',
            'recompensas' => 'recompensas',
        ];
        $libKey = $mapeo[$domKey] ?? $domKey;

        return ($forma === 'A')
            ? IntralaboralAScoring::getBaremoDominio($libKey)
            : IntralaboralBScoring::getBaremoDominio($libKey);
    }

    /**
     * Obtiene baremo extralaboral total según forma
     */
    protected function getBaremoExtraTotal(string $forma): array
    {
        return ExtralaboralScoring::getBaremoTotal($forma);
    }

    /**
     * Obtiene baremo estrés según forma
     */
    protected function getBaremoEstres(string $forma): array
    {
        return ($forma === 'A')
            ? EstresScoring::getBaremoA()
            : EstresScoring::getBaremoB();
    }

    /**
     * Obtiene baremo de dimensión extralaboral según forma
     * Nota: Dimensiones extralaborales tienen el mismo baremo para A y B en la mayoría
     */
    protected function getBaremoDimensionExtra(string $dimKey, string $forma = 'A'): array
    {
        // Mapeo de claves del archivo a claves de librería
        $mapeo = [
            'tiempo_fuera' => 'tiempo_fuera_trabajo',
            'relaciones_familiares' => 'relaciones_familiares',
            'comunicacion' => 'comunicacion_relaciones',
            'situacion_economica' => 'situacion_economica',
            'caracteristicas_vivienda' => 'caracteristicas_vivienda',
            'influencia_entorno' => 'influencia_entorno',
            'desplazamiento' => 'desplazamiento',
        ];
        $libKey = $mapeo[$dimKey] ?? $dimKey;

        return ExtralaboralScoring::getBaremoDimension($libKey, $forma) ?? [];
    }

    /**
     * Preview del informe completo
     */
    public function preview($batteryServiceId)
    {
        $this->initializeData($batteryServiceId);
        $html = $this->generateFullHtml($batteryServiceId);

        return view('pdfnativo/preview_wrapper', [
            'content' => $html,
            'css' => $this->getCss(),
            'pageTitle' => 'Preview: Informe Completo Nativo',
            'batteryServiceId' => $batteryServiceId
        ]);
    }

    /**
     * Descarga el PDF completo
     */
    public function download($batteryServiceId)
    {
        $this->initializeData($batteryServiceId);
        $html = $this->generateFullHtml($batteryServiceId);
        $companyName = preg_replace('/[^a-zA-Z0-9]/', '_', $this->companyData['company_name'] ?? 'Empresa');

        return $this->generatePdf($html, "Informe_Bateria_{$companyName}.pdf");
    }

    /**
     * Genera el HTML completo del informe
     */
    private function generateFullHtml($batteryServiceId)
    {
        $html = '';

        // 1. Portada
        $html .= $this->renderPortada();

        // 2. Tabla de Contenido
        $html .= $this->renderContenido();

        // 3. Introducción y Marco Conceptual
        $html .= $this->renderIntroduccion($batteryServiceId);

        // 4. Resultados Intralaboral Total
        $html .= $this->renderIntralaboralTotal($batteryServiceId);

        // 5. Dominios Intralaborales (usando template dominios_intralaborales.php)
        $html .= $this->renderIntralaboralDominios($batteryServiceId);

        // 6. Extralaboral Total y Dimensiones
        $html .= $this->renderExtralaboralTotal($batteryServiceId);
        $html .= $this->renderExtralaboralDimensiones($batteryServiceId);

        // 7. Estrés
        $html .= $this->renderEstres($batteryServiceId);

        // 8. Firma
        $html .= $this->renderFirma();

        return $html;
    }

    /**
     * Renderiza la portada
     */
    private function renderPortada()
    {
        $company = $this->companyData;
        $consultant = $this->consultantData;
        $fecha = $this->formatDate($company['service_date'] ?? date('Y-m-d'));

        return '
<div class="text-center" style="padding-top: 100pt;">
    <div class="portada-titulo">INFORME DE BATERÍA<br>DE RIESGO PSICOSOCIAL</div>

    <div class="portada-empresa mt-20">
        <strong>' . esc($company['company_name'] ?? 'EMPRESA') . '</strong><br>
        NIT: ' . esc($company['nit'] ?? '') . '
    </div>

    <div style="margin-top: 60pt;">
        <p>' . esc($company['city'] ?? 'Colombia') . '</p>
        <p>' . $fecha . '</p>
    </div>

    <div style="margin-top: 80pt;">
        <p class="small">Elaborado por:</p>
        <p><strong>' . esc($consultant['nombre_completo'] ?? 'Consultor') . '</strong></p>
        <p class="small">' . esc($consultant['cargo'] ?? 'Psicólogo Especialista SST') . '</p>
        <p class="small">Licencia SST: ' . esc($consultant['licencia_sst'] ?? '') . '</p>
    </div>
</div>
<div class="page-break"></div>';
    }

    /**
     * Renderiza la tabla de contenido
     */
    private function renderContenido()
    {
        return '
<h1>Tabla de Contenido</h1>

<table style="border: none;">
    <tr><td style="border: none; padding: 3pt 0;">1. Introducción</td></tr>
    <tr><td style="border: none; padding: 3pt 0;">2. Marco Conceptual</td></tr>
    <tr><td style="border: none; padding: 3pt 0;">3. Niveles de Riesgo</td></tr>
    <tr><td style="border: none; padding: 3pt 0;">4. Resultados Intralaboral Total</td></tr>
    <tr><td style="border: none; padding: 3pt 0;">5. Dominios Intralaborales</td></tr>
    <tr><td style="border: none; padding: 3pt 0; padding-left: 20pt;">5.1 Liderazgo y Relaciones Sociales</td></tr>
    <tr><td style="border: none; padding: 3pt 0; padding-left: 20pt;">5.2 Control sobre el Trabajo</td></tr>
    <tr><td style="border: none; padding: 3pt 0; padding-left: 20pt;">5.3 Demandas del Trabajo</td></tr>
    <tr><td style="border: none; padding: 3pt 0; padding-left: 20pt;">5.4 Recompensas</td></tr>
    <tr><td style="border: none; padding: 3pt 0;">6. Resultados Extralaboral</td></tr>
    <tr><td style="border: none; padding: 3pt 0;">7. Dimensiones Extralaborales</td></tr>
    <tr><td style="border: none; padding: 3pt 0;">8. Evaluación del Estrés</td></tr>
    <tr><td style="border: none; padding: 3pt 0;">9. Conclusiones y Recomendaciones</td></tr>
</table>

<div class="page-break"></div>';
    }

    /**
     * Renderiza la introducción
     */
    private function renderIntroduccion($batteryServiceId)
    {
        $stats = $this->getParticipationStats($batteryServiceId);
        $company = $this->companyData;

        return '
<h1>Introducción</h1>

<p>
Este informe tiene como finalidad identificar y evaluar los factores de riesgo psicosocial en los
colaboradores de la empresa <strong>' . esc($company['company_name'] ?? 'EMPRESA') . '</strong>, en donde
<strong>' . ($stats['total'] ?? 0) . '</strong> personas han dado respuesta a la Batería de Instrumentos
para la Evaluación de Factores de Riesgo Psicosocial avalada por el Ministerio de Trabajo de Colombia.
Se dividieron en ' . ($stats['forma_a'] ?? 0) . ' personas para Forma A y ' . ($stats['forma_b'] ?? 0) . ' personas para Forma B.
</p>

<p>
Lo anterior es una acción de cumplimiento frente a la Resolución 2646 de julio de 2008 y ante la
Resolución 2764 de 2022 emitida por el Ministerio de Trabajo de Colombia.
</p>

<h2 class="mt-20">Marco Conceptual</h2>

<p>
Los factores psicosociales se encuentran definidos como todas aquellas condiciones del trabajo, del
entorno, o de la persona, que en una interrelación dinámica generan percepciones y experiencias,
que influyen negativamente en su salud y en desempeño laboral de las personas.
</p>

<p>Dichas condiciones se determinan a través de tres tipos:</p>

<ol>
    <li><strong>Condiciones Intralaborales:</strong> Son las características del trabajo y de su organización que influyen en la salud y bienestar del individuo.</li>
    <li><strong>Condiciones Extralaborales:</strong> Comprenden los aspectos del entorno familiar, social y económico del trabajador.</li>
    <li><strong>Condiciones Individuales:</strong> Son las características propias de cada trabajador como la edad, género, estado civil y escolaridad.</li>
</ol>

<h2 class="mt-20">Niveles de Riesgo</h2>

<table>
    <thead>
        <tr>
            <th style="width: 25%;">Nivel</th>
            <th style="width: 75%;">Descripción</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="bg-sin-riesgo text-center bold">Sin Riesgo</td>
            <td>Ausencia de riesgo o riesgo tan bajo que no amerita desarrollar actividades de intervención</td>
        </tr>
        <tr>
            <td class="bg-riesgo-bajo text-center bold">Riesgo Bajo</td>
            <td>No se espera que los factores psicosociales estén relacionados con síntomas significativos de estrés</td>
        </tr>
        <tr>
            <td class="bg-riesgo-medio text-center bold">Riesgo Medio</td>
            <td>Se esperaría una respuesta de estrés moderada. Amerita observación y acciones preventivas</td>
        </tr>
        <tr>
            <td class="bg-riesgo-alto text-center bold">Riesgo Alto</td>
            <td>Importante posibilidad de asociación con respuestas de estrés alto. Requiere intervención</td>
        </tr>
        <tr>
            <td class="bg-riesgo-muy-alto text-center bold">Riesgo Muy Alto</td>
            <td>Amplia posibilidad de asociarse a respuestas muy altas de estrés. Requiere intervención inmediata</td>
        </tr>
    </tbody>
</table>

<div class="page-break"></div>';
    }

    /**
     * Renderiza resultados intralaboral total
     */
    private function renderIntralaboralTotal($batteryServiceId)
    {
        $resultsA = $this->calculatedResultModel
            ->where('battery_service_id', $batteryServiceId)
            ->where('intralaboral_form_type', 'A')
            ->findAll();

        $resultsB = $this->calculatedResultModel
            ->where('battery_service_id', $batteryServiceId)
            ->where('intralaboral_form_type', 'B')
            ->findAll();

        $html = '<h1>Resultados Intralaboral Total</h1>';
        $html .= '<p>Los resultados totales del cuestionario intralaboral reflejan el nivel de riesgo psicosocial general asociado a las condiciones del trabajo.</p>';

        // Forma A - BAREMOS desde Single Source of Truth
        if (!empty($resultsA)) {
            $dataA = $this->calculateTotalData($resultsA, 'A', 'intralaboral_total_puntaje', 'intralaboral_total_nivel', $this->getBaremoIntraTotal('A'));
            $html .= $this->renderTotalSection('Forma A - Jefes, Profesionales y Técnicos', $dataA);
        }

        // Forma B - BAREMOS desde Single Source of Truth
        if (!empty($resultsB)) {
            $dataB = $this->calculateTotalData($resultsB, 'B', 'intralaboral_total_puntaje', 'intralaboral_total_nivel', $this->getBaremoIntraTotal('B'));
            $html .= $this->renderTotalSection('Forma B - Auxiliares y Operarios', $dataB);
        }

        $html .= '<div class="page-break"></div>';

        return $html;
    }

    /**
     * Renderiza los dominios intralaborales usando el template de dominios_intralaborales.php
     */
    private function renderIntralaboralDominios($batteryServiceId)
    {
        $resultsA = $this->calculatedResultModel
            ->where('battery_service_id', $batteryServiceId)
            ->where('intralaboral_form_type', 'A')
            ->findAll();

        $resultsB = $this->calculatedResultModel
            ->where('battery_service_id', $batteryServiceId)
            ->where('intralaboral_form_type', 'B')
            ->findAll();

        $html = '<h1>Dominios Intralaborales</h1>';
        $html .= '<p>Los dominios intralaborales agrupan las dimensiones que evalúan diferentes aspectos de las condiciones de trabajo.</p>';
        $html .= '<div class="page-break"></div>';

        // Para cada dominio
        foreach ($this->dominios as $domKey => $dominio) {
            // Forma A
            if (!empty($resultsA)) {
                $html .= $this->renderDominioPage($resultsA, $domKey, $dominio, 'A', $batteryServiceId);
            }

            // Forma B
            if (!empty($resultsB)) {
                $html .= $this->renderDominioPage($resultsB, $domKey, $dominio, 'B', $batteryServiceId);
            }
        }

        return $html;
    }

    /**
     * Renderiza una página de dominio siguiendo el template
     */
    private function renderDominioPage($results, $domKey, $dominio, $forma, $batteryServiceId)
    {
        $campoPuntaje = $dominio['campo_puntaje'];
        $campoNivel = $dominio['campo_nivel'];
        // BAREMO desde Single Source of Truth
        $baremo = $this->getBaremoDominio($domKey, $forma);

        // Calcular promedio
        $puntajes = array_filter(array_column($results, $campoPuntaje), fn($v) => $v !== null && $v !== '');
        $promedio = !empty($puntajes) ? array_sum($puntajes) / count($puntajes) : 0;
        $nivel = $this->getNivelFromPuntaje($promedio, $baremo);

        // Calcular distribución
        $distribucion = $this->calculateDistribution($results, $campoNivel);
        $total = count($results);
        $porcentajes = [];
        foreach ($distribucion as $niv => $count) {
            $porcentajes[$niv] = $total > 0 ? round(($count / $total) * 100, 1) : 0;
        }

        // Calcular porcentajes agrupados
        $pctAlto = $porcentajes['riesgo_alto'] + $porcentajes['riesgo_muy_alto'];
        $pctMedio = $porcentajes['riesgo_medio'];
        $pctBajo = $porcentajes['sin_riesgo'] + $porcentajes['riesgo_bajo'];

        // Obtener texto IA
        $textoIA = $this->getAIText($batteryServiceId, 'intralaboral', $forma, 'domain', $domKey);

        // Generar gauge
        $gaugeImage = $this->gaugeGenerator->generate($promedio, $baremo);

        $tipoTrabajadores = $forma === 'A' ? 'Jefes, Profesionales y Técnicos' : 'Auxiliares y Operarios';
        $color = $this->riskColors[$nivel] ?? '#999';
        $nivelLabel = $this->riskNames[$nivel] ?? $nivel;

        $html = '
<!-- Header -->
<table style="width: 100%; border-bottom: 2px solid #006699; margin-bottom: 8px; border: none;">
    <tr>
        <td style="width: 30%; border: none; padding: 4px;">' . esc($this->companyData['company_name'] ?? 'Empresa') . '</td>
        <td style="width: 40%; border: none; padding: 4px; text-align: center;"><strong>Batería de Riesgo Psicosocial</strong></td>
        <td style="width: 30%; border: none; padding: 4px; text-align: right;">Forma ' . $forma . '</td>
    </tr>
</table>

<!-- Título -->
<div class="title-domain">Dominio: ' . esc($dominio['nombre']) . '</div>
<div class="title-sub">' . $tipoTrabajadores . '</div>

<!-- Definición -->
<div class="definition-box">
    <div class="definition-label">Definición:</div>
    <div>' . esc($dominio['definicion']) . '</div>
</div>

<!-- Layout: Gauge + Interpretación -->
<table style="width: 100%; border: none;">
    <tr>
        <td style="width: 33%; text-align: center; vertical-align: top; border: none; padding: 4px;">
            <img src="' . $gaugeImage . '" style="max-width: 150px;" alt="Gauge">

            <!-- Tabla de baremos -->
            <table style="width: 100%; font-size: 6.5pt; margin-top: 4px;">
                <tr>
                    <td colspan="2" class="bg-sin-riesgo" style="text-align: center; padding: 2px;">Sin Riesgo</td>
                    <td class="bg-riesgo-bajo" style="text-align: center; padding: 2px;">Bajo</td>
                    <td class="bg-riesgo-medio" style="text-align: center; padding: 2px;">Medio</td>
                    <td class="bg-riesgo-alto" style="text-align: center; padding: 2px;">Alto</td>
                    <td class="bg-riesgo-muy-alto" style="text-align: center; padding: 2px;">Muy Alto</td>
                </tr>
                <tr>
                    <td style="background:#E8F5E9; text-align: center; padding: 2px;">↓</td>
                    <td style="background:#E8F5E9; text-align: center; padding: 2px;">' . $baremo['sin_riesgo'][0] . '</td>
                    <td style="background:#F1F8E9; text-align: center; padding: 2px;">' . $baremo['riesgo_bajo'][0] . '</td>
                    <td style="background:#FFFDE7; text-align: center; padding: 2px;">' . $baremo['riesgo_medio'][0] . '</td>
                    <td style="background:#FFF3E0; text-align: center; padding: 2px;">' . $baremo['riesgo_alto'][0] . '</td>
                    <td style="background:#FFEBEE; text-align: center; padding: 2px;">' . $baremo['riesgo_muy_alto'][0] . '</td>
                </tr>
                <tr>
                    <td style="background:#E8F5E9; text-align: center; padding: 2px;">↑</td>
                    <td style="background:#E8F5E9; text-align: center; padding: 2px;">' . $baremo['sin_riesgo'][1] . '</td>
                    <td style="background:#F1F8E9; text-align: center; padding: 2px;">' . $baremo['riesgo_bajo'][1] . '</td>
                    <td style="background:#FFFDE7; text-align: center; padding: 2px;">' . $baremo['riesgo_medio'][1] . '</td>
                    <td style="background:#FFF3E0; text-align: center; padding: 2px;">' . $baremo['riesgo_alto'][1] . '</td>
                    <td style="background:#FFEBEE; text-align: center; padding: 2px;">' . $baremo['riesgo_muy_alto'][1] . '</td>
                </tr>
            </table>
        </td>

        <td style="width: 67%; padding-left: 12px; vertical-align: top; border: none;">
            <div style="text-align: justify;">
                <p>
                    El dominio <strong>' . esc($dominio['nombre']) . '</strong>
                    presenta un puntaje promedio de <strong>' . number_format($promedio, 2, ',', '.') . '</strong>,
                    clasificándose como
                    <span style="background-color: ' . $color . '; color: ' . ($nivel == 'riesgo_medio' ? '#333' : 'white') . '; padding: 2px 5px; font-weight: bold;">
                        ' . strtoupper($nivelLabel) . '
                    </span>.
                </p>
                <p>
                    Se evaluaron <strong>' . $total . '</strong> trabajadores.
                </p>
            </div>

            <!-- Dimensiones -->
            <div style="background-color: #f5f5f5; padding: 6px 7px; margin-top: 8px;">
                <div style="font-weight: bold; color: #006699; margin-bottom: 3px; font-size: 7.5pt;">Dimensiones que componen este dominio:</div>
                <ul style="margin: 0; padding-left: 14px; font-size: 7.5pt;">';

        foreach ($dominio['dimensiones'] as $dim) {
            $html .= '<li style="margin-bottom: 1px;">' . esc($dim) . '</li>';
        }

        $html .= '
                </ul>
            </div>
        </td>
    </tr>
</table>

<!-- Distribución -->
<div style="font-size: 9.5pt; font-weight: bold; color: #006699; margin: 10px 0 6px 0;">Distribución por Niveles de Riesgo</div>

<table style="width: 100%; border: none;">
    <tr>
        <td style="width: 60%; border: none; padding: 4px;">
            <!-- Barra horizontal -->
            <div style="width: 100%; height: 18px; border: 1px solid #ccc; margin: 4px 0;">';

        if ($pctAlto > 0) {
            $html .= '<div style="height: 18px; float: left; text-align: center; font-size: 6.5pt; font-weight: bold; color: white; line-height: 18px; width: ' . $pctAlto . '%; background-color: #F44336;">' . ($pctAlto > 10 ? round($pctAlto) . '%' : '') . '</div>';
        }
        if ($pctMedio > 0) {
            $html .= '<div style="height: 18px; float: left; text-align: center; font-size: 6.5pt; font-weight: bold; color: #333; line-height: 18px; width: ' . $pctMedio . '%; background-color: #FFEB3B;">' . ($pctMedio > 10 ? round($pctMedio) . '%' : '') . '</div>';
        }
        if ($pctBajo > 0) {
            $html .= '<div style="height: 18px; float: left; text-align: center; font-size: 6.5pt; font-weight: bold; color: white; line-height: 18px; width: ' . $pctBajo . '%; background-color: #4CAF50;">' . ($pctBajo > 10 ? round($pctBajo) . '%' : '') . '</div>';
        }

        $html .= '
            </div>

            <!-- Leyenda -->
            <table style="width: 100%; font-size: 7.5pt; margin-top: 4px; border: none;">
                <tr>
                    <td style="border: none;"><span style="display:inline-block; width:9px; height:9px; background:#F44336;"></span> Alto/Muy Alto: ' . round($pctAlto, 1) . '%</td>
                    <td style="border: none;"><span style="display:inline-block; width:9px; height:9px; background:#FFEB3B;"></span> Medio: ' . round($pctMedio, 1) . '%</td>
                    <td style="border: none;"><span style="display:inline-block; width:9px; height:9px; background:#4CAF50;"></span> Sin/Bajo: ' . round($pctBajo, 1) . '%</td>
                </tr>
            </table>
        </td>
        <td style="width: 40%; padding-left: 12px; border: none;">
            <div style="font-size: 8.5pt;">
                <strong style="color: #F44336;">' . ($distribucion['riesgo_alto'] + $distribucion['riesgo_muy_alto']) . '</strong> personas en riesgo alto/muy alto<br>
                <strong style="color: #F9A825;">' . $distribucion['riesgo_medio'] . '</strong> personas en riesgo medio
            </div>
        </td>
    </tr>
</table>

<!-- Focus box -->
<div style="border: 1px solid #006699; background-color: #e8f4fc; padding: 6px 7px; margin-top: 8px; font-size: 7.5pt;">
    <div style="font-weight: bold; color: #006699;">Acción Recomendada:</div>
    <div>' . $this->getRiskAction($nivel) . ' según Resolución 2404/2019.</div>
</div>';

        // Texto IA si existe
        if (!empty($textoIA)) {
            $html .= '
<!-- Texto IA -->
<div style="border-left: 2px solid #2196F3; background-color: #e3f2fd; padding: 7px 9px; margin-top: 9px; font-size: 7.5pt; text-align: justify;">
    <div style="font-weight: bold; color: #1976D2; margin-bottom: 4px;">Análisis del Dominio:</div>
    <div>' . nl2br(esc($textoIA)) . '</div>
</div>';
        }

        $html .= '
<div class="page-break"></div>';

        return $html;
    }

    /**
     * Renderiza resultados extralaboral total
     */
    private function renderExtralaboralTotal($batteryServiceId)
    {
        $resultsA = $this->calculatedResultModel
            ->where('battery_service_id', $batteryServiceId)
            ->where('intralaboral_form_type', 'A')
            ->findAll();

        $resultsB = $this->calculatedResultModel
            ->where('battery_service_id', $batteryServiceId)
            ->where('intralaboral_form_type', 'B')
            ->findAll();

        $html = '<h1>Resultados Extralaboral Total</h1>';
        $html .= '<p>El cuestionario extralaboral evalúa las condiciones externas al trabajo que pueden afectar la salud y el bienestar del trabajador.</p>';

        // Forma A - BAREMOS desde Single Source of Truth
        if (!empty($resultsA)) {
            $dataA = $this->calculateTotalData($resultsA, 'A', 'extralaboral_total_puntaje', 'extralaboral_total_nivel', $this->getBaremoExtraTotal('A'));
            $html .= $this->renderTotalSection('Forma A - Jefes, Profesionales y Técnicos', $dataA);
        }

        // Forma B - BAREMOS desde Single Source of Truth
        if (!empty($resultsB)) {
            $dataB = $this->calculateTotalData($resultsB, 'B', 'extralaboral_total_puntaje', 'extralaboral_total_nivel', $this->getBaremoExtraTotal('B'));
            $html .= $this->renderTotalSection('Forma B - Auxiliares y Operarios', $dataB);
        }

        $html .= '<div class="page-break"></div>';

        return $html;
    }

    /**
     * Renderiza las dimensiones extralaborales
     */
    private function renderExtralaboralDimensiones($batteryServiceId)
    {
        $resultsA = $this->calculatedResultModel
            ->where('battery_service_id', $batteryServiceId)
            ->where('intralaboral_form_type', 'A')
            ->findAll();

        $resultsB = $this->calculatedResultModel
            ->where('battery_service_id', $batteryServiceId)
            ->where('intralaboral_form_type', 'B')
            ->findAll();

        $html = '<h1>Dimensiones Extralaborales</h1>';
        $html .= '<p>Las dimensiones extralaborales evalúan los factores externos al trabajo que pueden afectar la salud y bienestar del trabajador.</p>';
        $html .= '<div class="page-break"></div>';

        // Para cada dimensión extralaboral
        foreach ($this->dimensionesExtra as $dimKey => $dimension) {
            // Forma A
            if (!empty($resultsA)) {
                $html .= $this->renderDimensionExtraPage($resultsA, $dimKey, $dimension, 'A', $batteryServiceId);
            }

            // Forma B
            if (!empty($resultsB)) {
                $html .= $this->renderDimensionExtraPage($resultsB, $dimKey, $dimension, 'B', $batteryServiceId);
            }
        }

        return $html;
    }

    /**
     * Renderiza una página de dimensión extralaboral
     */
    private function renderDimensionExtraPage($results, $dimKey, $dimension, $forma, $batteryServiceId)
    {
        $campoPuntaje = $dimension['campo_puntaje'];
        $campoNivel = $dimension['campo_nivel'];
        // BAREMO desde Single Source of Truth
        $baremo = $this->getBaremoDimensionExtra($dimKey, $forma);

        // Calcular promedio
        $puntajes = array_filter(array_column($results, $campoPuntaje), fn($v) => $v !== null && $v !== '');
        $promedio = !empty($puntajes) ? array_sum($puntajes) / count($puntajes) : 0;
        $nivel = $this->getNivelFromPuntaje($promedio, $baremo);

        // Calcular distribución
        $distribucion = $this->calculateDistribution($results, $campoNivel);
        $total = count($results);
        $porcentajes = [];
        foreach ($distribucion as $niv => $count) {
            $porcentajes[$niv] = $total > 0 ? round(($count / $total) * 100, 1) : 0;
        }

        $pctAlto = $porcentajes['riesgo_alto'] + $porcentajes['riesgo_muy_alto'];
        $pctMedio = $porcentajes['riesgo_medio'];
        $pctBajo = $porcentajes['sin_riesgo'] + $porcentajes['riesgo_bajo'];

        $textoIA = $this->getAIText($batteryServiceId, 'extralaboral', $forma, 'dimension', $dimKey);
        $gaugeImage = $this->gaugeGenerator->generate($promedio, $baremo);

        $tipoTrabajadores = $forma === 'A' ? 'Jefes, Profesionales y Técnicos' : 'Auxiliares y Operarios';
        $color = $this->riskColors[$nivel] ?? '#999';
        $nivelLabel = $this->riskNames[$nivel] ?? $nivel;

        $html = '
<table style="width: 100%; border-bottom: 2px solid #006699; margin-bottom: 8px; border: none;">
    <tr>
        <td style="width: 30%; border: none; padding: 4px;">' . esc($this->companyData['company_name'] ?? 'Empresa') . '</td>
        <td style="width: 40%; border: none; padding: 4px; text-align: center;"><strong>Factores Extralaborales</strong></td>
        <td style="width: 30%; border: none; padding: 4px; text-align: right;">Forma ' . $forma . '</td>
    </tr>
</table>

<div class="title-domain">Dimensión: ' . esc($dimension['nombre']) . '</div>
<div class="title-sub">' . $tipoTrabajadores . '</div>

<div class="definition-box">
    <div class="definition-label">Definición:</div>
    <div>' . esc($dimension['definicion']) . '</div>
</div>

<table style="width: 100%; border: none;">
    <tr>
        <td style="width: 33%; text-align: center; vertical-align: top; border: none; padding: 4px;">
            <img src="' . $gaugeImage . '" style="max-width: 150px;" alt="Gauge">

            <table style="width: 100%; font-size: 6.5pt; margin-top: 4px;">
                <tr>
                    <td colspan="2" class="bg-sin-riesgo" style="text-align: center; padding: 2px;">Sin Riesgo</td>
                    <td class="bg-riesgo-bajo" style="text-align: center; padding: 2px;">Bajo</td>
                    <td class="bg-riesgo-medio" style="text-align: center; padding: 2px;">Medio</td>
                    <td class="bg-riesgo-alto" style="text-align: center; padding: 2px;">Alto</td>
                    <td class="bg-riesgo-muy-alto" style="text-align: center; padding: 2px;">Muy Alto</td>
                </tr>
                <tr>
                    <td style="background:#E8F5E9; text-align: center; padding: 2px;">↓</td>
                    <td style="background:#E8F5E9; text-align: center; padding: 2px;">' . $baremo['sin_riesgo'][0] . '</td>
                    <td style="background:#F1F8E9; text-align: center; padding: 2px;">' . $baremo['riesgo_bajo'][0] . '</td>
                    <td style="background:#FFFDE7; text-align: center; padding: 2px;">' . $baremo['riesgo_medio'][0] . '</td>
                    <td style="background:#FFF3E0; text-align: center; padding: 2px;">' . $baremo['riesgo_alto'][0] . '</td>
                    <td style="background:#FFEBEE; text-align: center; padding: 2px;">' . $baremo['riesgo_muy_alto'][0] . '</td>
                </tr>
                <tr>
                    <td style="background:#E8F5E9; text-align: center; padding: 2px;">↑</td>
                    <td style="background:#E8F5E9; text-align: center; padding: 2px;">' . $baremo['sin_riesgo'][1] . '</td>
                    <td style="background:#F1F8E9; text-align: center; padding: 2px;">' . $baremo['riesgo_bajo'][1] . '</td>
                    <td style="background:#FFFDE7; text-align: center; padding: 2px;">' . $baremo['riesgo_medio'][1] . '</td>
                    <td style="background:#FFF3E0; text-align: center; padding: 2px;">' . $baremo['riesgo_alto'][1] . '</td>
                    <td style="background:#FFEBEE; text-align: center; padding: 2px;">' . $baremo['riesgo_muy_alto'][1] . '</td>
                </tr>
            </table>
        </td>

        <td style="width: 67%; padding-left: 12px; vertical-align: top; border: none;">
            <div style="text-align: justify;">
                <p>
                    La dimensión <strong>' . esc($dimension['nombre']) . '</strong>
                    presenta un puntaje promedio de <strong>' . number_format($promedio, 2, ',', '.') . '</strong>,
                    clasificándose como
                    <span style="background-color: ' . $color . '; color: ' . ($nivel == 'riesgo_medio' ? '#333' : 'white') . '; padding: 2px 5px; font-weight: bold;">
                        ' . strtoupper($nivelLabel) . '
                    </span>.
                </p>
                <p>Se evaluaron <strong>' . $total . '</strong> trabajadores.</p>
            </div>
        </td>
    </tr>
</table>

<div style="font-size: 9.5pt; font-weight: bold; color: #006699; margin: 10px 0 6px 0;">Distribución por Niveles de Riesgo</div>

<table style="width: 100%; border: none;">
    <tr>
        <td style="width: 60%; border: none; padding: 4px;">
            <div style="width: 100%; height: 18px; border: 1px solid #ccc; margin: 4px 0;">';

        if ($pctAlto > 0) {
            $html .= '<div style="height: 18px; float: left; text-align: center; font-size: 6.5pt; font-weight: bold; color: white; line-height: 18px; width: ' . $pctAlto . '%; background-color: #F44336;">' . ($pctAlto > 10 ? round($pctAlto) . '%' : '') . '</div>';
        }
        if ($pctMedio > 0) {
            $html .= '<div style="height: 18px; float: left; text-align: center; font-size: 6.5pt; font-weight: bold; color: #333; line-height: 18px; width: ' . $pctMedio . '%; background-color: #FFEB3B;">' . ($pctMedio > 10 ? round($pctMedio) . '%' : '') . '</div>';
        }
        if ($pctBajo > 0) {
            $html .= '<div style="height: 18px; float: left; text-align: center; font-size: 6.5pt; font-weight: bold; color: white; line-height: 18px; width: ' . $pctBajo . '%; background-color: #4CAF50;">' . ($pctBajo > 10 ? round($pctBajo) . '%' : '') . '</div>';
        }

        $html .= '
            </div>

            <table style="width: 100%; font-size: 7.5pt; margin-top: 4px; border: none;">
                <tr>
                    <td style="border: none;"><span style="display:inline-block; width:9px; height:9px; background:#F44336;"></span> Alto/Muy Alto: ' . round($pctAlto, 1) . '%</td>
                    <td style="border: none;"><span style="display:inline-block; width:9px; height:9px; background:#FFEB3B;"></span> Medio: ' . round($pctMedio, 1) . '%</td>
                    <td style="border: none;"><span style="display:inline-block; width:9px; height:9px; background:#4CAF50;"></span> Sin/Bajo: ' . round($pctBajo, 1) . '%</td>
                </tr>
            </table>
        </td>
        <td style="width: 40%; padding-left: 12px; border: none;">
            <div style="font-size: 8.5pt;">
                <strong style="color: #F44336;">' . ($distribucion['riesgo_alto'] + $distribucion['riesgo_muy_alto']) . '</strong> personas en riesgo alto/muy alto<br>
                <strong style="color: #F9A825;">' . $distribucion['riesgo_medio'] . '</strong> personas en riesgo medio
            </div>
        </td>
    </tr>
</table>

<div style="border: 1px solid #006699; background-color: #e8f4fc; padding: 6px 7px; margin-top: 8px; font-size: 7.5pt;">
    <div style="font-weight: bold; color: #006699;">Acción Recomendada:</div>
    <div>' . $this->getRiskAction($nivel) . ' según Resolución 2404/2019.</div>
</div>';

        if (!empty($textoIA)) {
            $html .= '
<div style="border-left: 2px solid #2196F3; background-color: #e3f2fd; padding: 7px 9px; margin-top: 9px; font-size: 7.5pt; text-align: justify;">
    <div style="font-weight: bold; color: #1976D2; margin-bottom: 4px;">Análisis de la Dimensión:</div>
    <div>' . nl2br(esc($textoIA)) . '</div>
</div>';
        }

        $html .= '<div class="page-break"></div>';

        return $html;
    }

    /**
     * Renderiza la sección de estrés
     */
    private function renderEstres($batteryServiceId)
    {
        $resultsA = $this->calculatedResultModel
            ->where('battery_service_id', $batteryServiceId)
            ->where('intralaboral_form_type', 'A')
            ->findAll();

        $resultsB = $this->calculatedResultModel
            ->where('battery_service_id', $batteryServiceId)
            ->where('intralaboral_form_type', 'B')
            ->findAll();

        $html = '<h1>Evaluación del Estrés</h1>';
        $html .= '<p>El cuestionario para la evaluación del estrés es un instrumento diseñado para evaluar síntomas reveladores de la presencia de reacciones de estrés, distribuidos en cuatro categorías principales.</p>';

        // Forma A
        if (!empty($resultsA)) {
            $html .= $this->renderEstresPage($resultsA, 'A', $batteryServiceId);
        }

        // Forma B
        if (!empty($resultsB)) {
            $html .= $this->renderEstresPage($resultsB, 'B', $batteryServiceId);
        }

        return $html;
    }

    /**
     * Renderiza una página de estrés
     */
    private function renderEstresPage($results, $forma, $batteryServiceId)
    {
        // BAREMO desde Single Source of Truth
        $baremo = $this->getBaremoEstres($forma);

        $puntajes = array_filter(array_column($results, 'estres_total_puntaje'), fn($v) => $v !== null && $v !== '');
        $promedio = !empty($puntajes) ? array_sum($puntajes) / count($puntajes) : 0;
        $nivel = $this->getNivelFromPuntaje($promedio, $baremo);

        $distribucion = $this->calculateDistributionEstres($results, 'estres_total_nivel');
        $total = count($results);
        $porcentajes = [];
        foreach ($distribucion as $niv => $count) {
            $porcentajes[$niv] = $total > 0 ? round(($count / $total) * 100, 1) : 0;
        }

        // Estrés usa nomenclatura diferente: muy_bajo, bajo, medio, alto, muy_alto
        $pctAlto = ($porcentajes['alto'] ?? 0) + ($porcentajes['muy_alto'] ?? 0);
        $pctMedio = $porcentajes['medio'] ?? 0;
        $pctBajo = ($porcentajes['muy_bajo'] ?? 0) + ($porcentajes['bajo'] ?? 0);

        $textoIA = $this->getAIText($batteryServiceId, 'stress', $forma, 'questionnaire', 'estres');
        $gaugeImage = $this->gaugeGenerator->generate($promedio, $baremo);

        $tipoTrabajadores = $forma === 'A' ? 'Jefes, Profesionales y Técnicos' : 'Auxiliares y Operarios';
        $color = $this->riskColors[$nivel] ?? '#999';
        $nivelLabel = $this->riskNames[$nivel] ?? $nivel;

        $html = '
<table style="width: 100%; border-bottom: 2px solid #006699; margin-bottom: 8px; border: none;">
    <tr>
        <td style="width: 30%; border: none; padding: 4px;">' . esc($this->companyData['company_name'] ?? 'Empresa') . '</td>
        <td style="width: 40%; border: none; padding: 4px; text-align: center;"><strong>Cuestionario de Estrés</strong></td>
        <td style="width: 30%; border: none; padding: 4px; text-align: right;">Forma ' . $forma . '</td>
    </tr>
</table>

<div class="title-domain">Evaluación del Estrés</div>
<div class="title-sub">' . $tipoTrabajadores . '</div>

<div class="definition-box">
    <div class="definition-label">Descripción:</div>
    <div>El cuestionario evalúa síntomas de estrés distribuidos en cuatro categorías: síntomas fisiológicos, comportamiento social, intelectuales y laborales, y psicoemocionales.</div>
</div>

<table style="width: 100%; border: none;">
    <tr>
        <td style="width: 33%; text-align: center; vertical-align: top; border: none; padding: 4px;">
            <img src="' . $gaugeImage . '" style="max-width: 150px;" alt="Gauge">

            <table style="width: 100%; font-size: 6.5pt; margin-top: 4px;">
                <tr>
                    <td colspan="2" class="bg-sin-riesgo" style="text-align: center; padding: 2px;">Sin Riesgo</td>
                    <td class="bg-riesgo-bajo" style="text-align: center; padding: 2px;">Bajo</td>
                    <td class="bg-riesgo-medio" style="text-align: center; padding: 2px;">Medio</td>
                    <td class="bg-riesgo-alto" style="text-align: center; padding: 2px;">Alto</td>
                    <td class="bg-riesgo-muy-alto" style="text-align: center; padding: 2px;">Muy Alto</td>
                </tr>
                <tr>
                    <td style="background:#E8F5E9; text-align: center; padding: 2px;">↓</td>
                    <td style="background:#E8F5E9; text-align: center; padding: 2px;">' . $baremo['sin_riesgo'][0] . '</td>
                    <td style="background:#F1F8E9; text-align: center; padding: 2px;">' . $baremo['riesgo_bajo'][0] . '</td>
                    <td style="background:#FFFDE7; text-align: center; padding: 2px;">' . $baremo['riesgo_medio'][0] . '</td>
                    <td style="background:#FFF3E0; text-align: center; padding: 2px;">' . $baremo['riesgo_alto'][0] . '</td>
                    <td style="background:#FFEBEE; text-align: center; padding: 2px;">' . $baremo['riesgo_muy_alto'][0] . '</td>
                </tr>
                <tr>
                    <td style="background:#E8F5E9; text-align: center; padding: 2px;">↑</td>
                    <td style="background:#E8F5E9; text-align: center; padding: 2px;">' . $baremo['sin_riesgo'][1] . '</td>
                    <td style="background:#F1F8E9; text-align: center; padding: 2px;">' . $baremo['riesgo_bajo'][1] . '</td>
                    <td style="background:#FFFDE7; text-align: center; padding: 2px;">' . $baremo['riesgo_medio'][1] . '</td>
                    <td style="background:#FFF3E0; text-align: center; padding: 2px;">' . $baremo['riesgo_alto'][1] . '</td>
                    <td style="background:#FFEBEE; text-align: center; padding: 2px;">' . $baremo['riesgo_muy_alto'][1] . '</td>
                </tr>
            </table>
        </td>

        <td style="width: 67%; padding-left: 12px; vertical-align: top; border: none;">
            <div style="text-align: justify;">
                <p>
                    El <strong>Cuestionario de Estrés</strong>
                    presenta un puntaje promedio de <strong>' . number_format($promedio, 2, ',', '.') . '</strong>,
                    clasificándose como
                    <span style="background-color: ' . $color . '; color: ' . ($nivel == 'riesgo_medio' ? '#333' : 'white') . '; padding: 2px 5px; font-weight: bold;">
                        ' . strtoupper($nivelLabel) . '
                    </span>.
                </p>
                <p>Se evaluaron <strong>' . $total . '</strong> trabajadores.</p>
            </div>

            <div style="background-color: #f5f5f5; padding: 6px 7px; margin-top: 8px;">
                <div style="font-weight: bold; color: #006699; margin-bottom: 3px; font-size: 7.5pt;">Categorías de síntomas evaluadas:</div>
                <ul style="margin: 0; padding-left: 14px; font-size: 7.5pt;">
                    <li style="margin-bottom: 1px;">Síntomas fisiológicos</li>
                    <li style="margin-bottom: 1px;">Síntomas de comportamiento social</li>
                    <li style="margin-bottom: 1px;">Síntomas intelectuales y laborales</li>
                    <li style="margin-bottom: 1px;">Síntomas psicoemocionales</li>
                </ul>
            </div>
        </td>
    </tr>
</table>

<div style="font-size: 9.5pt; font-weight: bold; color: #006699; margin: 10px 0 6px 0;">Distribución por Niveles de Riesgo</div>

<table style="width: 100%; border: none;">
    <tr>
        <td style="width: 60%; border: none; padding: 4px;">
            <div style="width: 100%; height: 18px; border: 1px solid #ccc; margin: 4px 0;">';

        if ($pctAlto > 0) {
            $html .= '<div style="height: 18px; float: left; text-align: center; font-size: 6.5pt; font-weight: bold; color: white; line-height: 18px; width: ' . $pctAlto . '%; background-color: #F44336;">' . ($pctAlto > 10 ? round($pctAlto) . '%' : '') . '</div>';
        }
        if ($pctMedio > 0) {
            $html .= '<div style="height: 18px; float: left; text-align: center; font-size: 6.5pt; font-weight: bold; color: #333; line-height: 18px; width: ' . $pctMedio . '%; background-color: #FFEB3B;">' . ($pctMedio > 10 ? round($pctMedio) . '%' : '') . '</div>';
        }
        if ($pctBajo > 0) {
            $html .= '<div style="height: 18px; float: left; text-align: center; font-size: 6.5pt; font-weight: bold; color: white; line-height: 18px; width: ' . $pctBajo . '%; background-color: #4CAF50;">' . ($pctBajo > 10 ? round($pctBajo) . '%' : '') . '</div>';
        }

        $html .= '
            </div>

            <table style="width: 100%; font-size: 7.5pt; margin-top: 4px; border: none;">
                <tr>
                    <td style="border: none;"><span style="display:inline-block; width:9px; height:9px; background:#F44336;"></span> Alto/Muy Alto: ' . round($pctAlto, 1) . '%</td>
                    <td style="border: none;"><span style="display:inline-block; width:9px; height:9px; background:#FFEB3B;"></span> Medio: ' . round($pctMedio, 1) . '%</td>
                    <td style="border: none;"><span style="display:inline-block; width:9px; height:9px; background:#4CAF50;"></span> Sin/Bajo: ' . round($pctBajo, 1) . '%</td>
                </tr>
            </table>
        </td>
        <td style="width: 40%; padding-left: 12px; border: none;">
            <div style="font-size: 8.5pt;">
                <strong style="color: #F44336;">' . (($distribucion['alto'] ?? 0) + ($distribucion['muy_alto'] ?? 0)) . '</strong> personas en riesgo alto/muy alto<br>
                <strong style="color: #F9A825;">' . ($distribucion['medio'] ?? 0) . '</strong> personas en riesgo medio
            </div>
        </td>
    </tr>
</table>

<div style="border: 1px solid #006699; background-color: #e8f4fc; padding: 6px 7px; margin-top: 8px; font-size: 7.5pt;">
    <div style="font-weight: bold; color: #006699;">Acción Recomendada:</div>
    <div>' . $this->getRiskActionEstres($nivel) . ' según Resolución 2404/2019.</div>
</div>';

        if (!empty($textoIA)) {
            $html .= '
<div style="border-left: 2px solid #2196F3; background-color: #e3f2fd; padding: 7px 9px; margin-top: 9px; font-size: 7.5pt; text-align: justify;">
    <div style="font-weight: bold; color: #1976D2; margin-bottom: 4px;">Análisis del Estrés:</div>
    <div>' . nl2br(esc($textoIA)) . '</div>
</div>';
        }

        $html .= '<div class="page-break"></div>';

        return $html;
    }

    /**
     * Obtiene acción recomendada por nivel de estrés
     */
    private function getRiskActionEstres($nivel)
    {
        $actions = [
            'muy_bajo' => 'Mantener condiciones actuales',
            'bajo' => 'Acciones preventivas de mantenimiento',
            'medio' => 'Observación y acciones preventivas',
            'alto' => 'Intervención en marco de vigilancia epidemiológica',
            'muy_alto' => 'Intervención inmediata en marco de vigilancia epidemiológica',
            // También mapear los nombres estándar por si acaso
            'sin_riesgo' => 'Mantener condiciones actuales',
            'riesgo_bajo' => 'Acciones preventivas de mantenimiento',
            'riesgo_medio' => 'Observación y acciones preventivas',
            'riesgo_alto' => 'Intervención en marco de vigilancia epidemiológica',
            'riesgo_muy_alto' => 'Intervención inmediata en marco de vigilancia epidemiológica',
        ];
        return $actions[$nivel] ?? '';
    }

    /**
     * Renderiza la firma
     */
    private function renderFirma()
    {
        $consultant = $this->consultantData;

        return '
<h1>Cierre del Informe</h1>

<p style="text-align: justify;">
El presente informe ha sido elaborado de acuerdo con los lineamientos establecidos por el Ministerio
de Trabajo de Colombia, en cumplimiento de la Resolución 2646 de 2008 y la Resolución 2764 de 2022.
Los resultados presentados deben ser utilizados como base para el diseño e implementación de
programas de intervención y prevención de riesgos psicosociales.
</p>

<p style="text-align: justify;">
Se recomienda realizar seguimiento periódico de los indicadores y mantener actualizado el programa
de vigilancia epidemiológica según la periodicidad determinada por el nivel de riesgo identificado.
</p>

<div class="firma-container">
    <div class="firma-linea">
        <p class="bold">' . esc($consultant['nombre_completo'] ?? 'Consultor') . '</p>
        <p class="small">' . esc($consultant['cargo'] ?? 'Psicólogo Especialista SST') . '</p>
        <p class="small">Licencia SST: ' . esc($consultant['licencia_sst'] ?? '') . '</p>
    </div>
</div>';
    }

    /**
     * Renderiza una sección de total (intralaboral, extralaboral, etc.)
     */
    private function renderTotalSection($titulo, $data)
    {
        $nivelClass = $this->getRiskClass($data['nivel']);
        $nivelName = $this->riskNames[$data['nivel']] ?? $data['nivel'];

        $html = '
<h2 class="mt-20">' . $titulo . '</h2>

<table>
    <tr>
        <th style="width: 40%;">Indicador</th>
        <th style="width: 60%;">Valor</th>
    </tr>
    <tr>
        <td class="bold">Total Evaluados</td>
        <td>' . $data['total_evaluados'] . ' personas</td>
    </tr>
    <tr>
        <td class="bold">Puntaje Promedio</td>
        <td>' . number_format($data['promedio'], 1) . '</td>
    </tr>
    <tr>
        <td class="bold">Nivel de Riesgo</td>
        <td class="' . $nivelClass . ' bold text-center">' . $nivelName . '</td>
    </tr>
    <tr>
        <td class="bold">Acción Recomendada</td>
        <td>' . $this->getRiskAction($data['nivel']) . '</td>
    </tr>
</table>

<h3>Distribución por Nivel de Riesgo</h3>
<table>
    <tr>
        <th>Nivel</th>
        <th>Cantidad</th>
        <th>Porcentaje</th>
    </tr>';

        foreach ($data['distribucion'] as $nivel => $cantidad) {
            $porcentaje = $data['porcentajes'][$nivel] ?? 0;
            $class = $this->getRiskClass($nivel);
            $name = $this->riskNames[$nivel] ?? $nivel;

            $html .= '
    <tr>
        <td class="' . $class . ' text-center bold">' . $name . '</td>
        <td class="text-center">' . $cantidad . '</td>
        <td class="text-center">' . number_format($porcentaje, 1) . '%</td>
    </tr>';
        }

        $html .= '</table>';

        return $html;
    }

    /**
     * Obtiene estadísticas de participación
     */
    private function getParticipationStats($batteryServiceId)
    {
        $db = \Config\Database::connect();
        $query = $db->query("
            SELECT
                COUNT(*) as total,
                SUM(CASE WHEN intralaboral_form_type = 'A' THEN 1 ELSE 0 END) as forma_a,
                SUM(CASE WHEN intralaboral_form_type = 'B' THEN 1 ELSE 0 END) as forma_b
            FROM calculated_results
            WHERE battery_service_id = ?
        ", [$batteryServiceId]);

        return $query->getRowArray() ?? ['total' => 0, 'forma_a' => 0, 'forma_b' => 0];
    }

    /**
     * Calcula datos de total
     */
    private function calculateTotalData($results, $forma, $campoPuntaje, $campoNivel, $baremo)
    {
        $puntajes = array_filter(array_column($results, $campoPuntaje), fn($v) => $v !== null && $v !== '');
        $promedio = !empty($puntajes) ? array_sum($puntajes) / count($puntajes) : 0;

        $distribucion = $this->calculateDistribution($results, $campoNivel);
        $total = count($results);

        $porcentajes = [];
        foreach ($distribucion as $nivel => $count) {
            $porcentajes[$nivel] = $total > 0 ? round(($count / $total) * 100, 1) : 0;
        }

        $nivelPromedio = $this->getNivelFromPuntaje($promedio, $baremo);

        return [
            'forma' => $forma,
            'promedio' => $promedio,
            'nivel' => $nivelPromedio,
            'total_evaluados' => $total,
            'distribucion' => $distribucion,
            'porcentajes' => $porcentajes,
        ];
    }

    /**
     * Calcula distribución por nivel
     */
    private function calculateDistribution($results, $campoNivel)
    {
        $distribucion = [
            'sin_riesgo' => 0,
            'riesgo_bajo' => 0,
            'riesgo_medio' => 0,
            'riesgo_alto' => 0,
            'riesgo_muy_alto' => 0,
        ];

        foreach ($results as $result) {
            $nivel = $result[$campoNivel] ?? '';
            if (isset($distribucion[$nivel])) {
                $distribucion[$nivel]++;
            }
        }

        return $distribucion;
    }

    /**
     * Calcula distribución por nivel para estrés (usa nomenclatura diferente)
     */
    private function calculateDistributionEstres($results, $campoNivel)
    {
        $distribucion = [
            'muy_bajo' => 0,
            'bajo' => 0,
            'medio' => 0,
            'alto' => 0,
            'muy_alto' => 0,
        ];

        foreach ($results as $result) {
            $nivel = $result[$campoNivel] ?? '';
            if (isset($distribucion[$nivel])) {
                $distribucion[$nivel]++;
            }
        }

        return $distribucion;
    }

    /**
     * Obtiene nivel de riesgo según puntaje
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
     * Obtiene texto generado por IA desde report_sections
     */
    private function getAIText($batteryServiceId, $questionnaireType, $forma, $sectionLevel, $code)
    {
        $db = \Config\Database::connect();

        $report = $db->query("
            SELECT id FROM reports WHERE battery_service_id = ? LIMIT 1
        ", [$batteryServiceId])->getRowArray();

        if (!$report) {
            return null;
        }

        $section = $this->reportSectionModel
            ->where('report_id', $report['id'])
            ->where('questionnaire_type', $questionnaireType)
            ->where('form_type', $forma)
            ->where('section_level', $sectionLevel)
            ->first();

        return $section['ai_generated_text'] ?? null;
    }
}
