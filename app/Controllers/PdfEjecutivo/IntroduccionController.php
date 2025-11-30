<?php

namespace App\Controllers\PdfEjecutivo;

/**
 * Controlador de Introducción para el Informe Ejecutivo PDF
 * Incluye: Introducción, Marco Conceptual, Condiciones (Intra/Extra/Individual),
 * Marco Legal, Objetivos y Metodología
 */
class IntroduccionController extends PdfEjecutivoBaseController
{
    /**
     * Preview de la introducción en navegador
     */
    public function preview($batteryServiceId)
    {
        $this->initializeData($batteryServiceId);
        $this->loadWorkerStats($batteryServiceId);
        $html = $this->render($batteryServiceId);

        return $this->generatePreview($html, 'Preview: Introducción');
    }

    /**
     * Descarga PDF de la introducción
     */
    public function download($batteryServiceId)
    {
        $this->initializeData($batteryServiceId);
        $this->loadWorkerStats($batteryServiceId);
        $html = $this->render($batteryServiceId);

        return $this->generatePdf($html, "Introduccion.pdf");
    }

    protected $workerStats = [];

    /**
     * Carga estadísticas de trabajadores
     */
    protected function loadWorkerStats($batteryServiceId)
    {
        $db = \Config\Database::connect();

        // Total de trabajadores que respondieron
        $query = $db->query("
            SELECT
                COUNT(*) as total,
                SUM(CASE WHEN intralaboral_type = 'A' THEN 1 ELSE 0 END) as forma_a,
                SUM(CASE WHEN intralaboral_type = 'B' THEN 1 ELSE 0 END) as forma_b
            FROM workers
            WHERE battery_service_id = ?
            AND status = 'completado'
        ", [$batteryServiceId]);

        $this->workerStats = $query->getRowArray() ?? ['total' => 0, 'forma_a' => 0, 'forma_b' => 0];
    }

    /**
     * Renderiza el HTML de la introducción completa
     */
    public function render($batteryServiceId)
    {
        if (empty($this->companyData)) {
            $this->initializeData($batteryServiceId);
            $this->loadWorkerStats($batteryServiceId);
        }

        $html = $this->renderIntroduccion();
        $html .= $this->renderMarcoConceptual();
        $html .= $this->renderCondicionesIntralaborales();  // Tiene page-break interno
        $html .= $this->renderCondicionesExtralaborales();
        $html .= $this->renderCondicionesIndividuales();
        $html .= $this->renderMarcoLegal();  // Inicia en página nueva
        $html .= $this->renderObjetivos();   // Tiene page-break interno
        $html .= $this->renderMetodologia(); // Tiene page-break interno

        return $html;
    }

    /**
     * Introducción inicial
     */
    protected function renderIntroduccion()
    {
        $company = $this->companyData;
        $total = $this->workerStats['total'] ?? 0;
        $formaA = $this->workerStats['forma_a'] ?? 0;
        $formaB = $this->workerStats['forma_b'] ?? 0;

        return '
<h1 style="font-size: 14pt; margin: 0 0 12pt 0; padding-bottom: 5pt;">Introducción</h1>

<p style="font-size: 9pt; text-align: justify; margin: 0 0 8pt 0;">
Este informe tiene como finalidad identificar y evaluar los factores de riesgo psicosocial en los colaboradores de la empresa <strong>' . esc($company['company_name'] ?? 'EMPRESA') . '</strong>; en la ciudad de ' . esc($company['city'] ?? 'Colombia') . ', en donde <strong>(' . $total . ')</strong> personas han dado respuesta a la Batería de Instrumentos para la Evaluación de Factores de Riesgo Psicosocial avalada por el Ministerio de Trabajo de Colombia junto al Auto reporte de síntomas de estrés las cuales están evalúan alteraciones asociadas a condiciones estresantes del trabajo. Se dividieron en <strong>' . $formaA . ' personas para Forma A</strong> y <strong>' . $formaB . ' personas para Forma B</strong>.
</p>

<p style="font-size: 9pt; text-align: justify; margin: 0 0 8pt 0;">
Lo anterior es una acción de cumplimiento frente a la Resolución 2646 de julio de 2008 declarada por el Ministerio de la Protección Social de Colombia y ante la reciente Resolución 2764 de 2022 emitida por el Ministerio de Trabajo de Colombia, reglamentan las responsabilidades empresariales en cuanto a la identificación, evaluación, prevención, intervención y control permanente a la exposición de los factores de riesgo psicosocial y; estudio y determinación de origen de las patologías presuntamente causadas por estrés ocupacional.
</p>

<p style="font-size: 9pt; text-align: justify; margin: 0 0 5pt 0;">
De acuerdo con el Ministerio de Trabajo, se definió el estrés ocupacional como:
</p>

<p style="font-size: 9pt; font-style: italic; text-align: justify; margin: 0 0 8pt 15pt; padding: 8pt; background-color: #f5f5f5; border-left: 3pt solid #006699;">
"el conjunto de reacciones de carácter psicológico (mentales, emocionales y de comportamiento), que se producen cuando la persona debe enfrentar a demandas derivadas de su interacción con las condiciones de un entorno laboral, entendidas como factores de riesgo psicosocial, ante las cuales su capacidad de afrontamiento es insuficiente, causando una alteración en su bienestar físico, mental y emocional".
</p>

<p style="font-size: 9pt; text-align: justify; margin: 0 0 8pt 0;">
En este orden de ideas, la empresa <strong>' . esc($company['company_name'] ?? 'EMPRESA') . '</strong>, aprovecha este ejercicio diagnóstico para ejecutar una intervención eficiente y oportuna, por medio de la implementación de un Sistema de Vigilancia para el control del estrés y de los factores de riesgo psicosociales, en aras de crear una cultura de salud y seguridad en todos sus colaboradores.
</p>
';
    }

    /**
     * Marco Conceptual
     */
    protected function renderMarcoConceptual()
    {
        return '
<h2 style="font-size: 13pt; color: #006699; margin: 15pt 0 10pt 0; padding-bottom: 3pt; border-bottom: 1pt solid #006699;">Marco Conceptual</h2>

<p style="font-size: 9pt; text-align: justify; margin: 0 0 8pt 0;">
Los factores psicosociales se encuentran definidos como todas aquellas condiciones del trabajo, del entorno, o de la persona, que en una interrelación dinámica generan percepciones y experiencias, que influyen negativamente en su salud y en desempeño laboral de las personas (Ministerio de Trabajo, 2014). Dichas se determinan y comprenden a través de tres tipos de condiciones:
</p>

<ol style="font-size: 9pt; margin: 5pt 0 8pt 20pt; padding: 0;">
    <li style="margin-bottom: 3pt;"><strong>Intralaborales</strong></li>
    <li style="margin-bottom: 3pt;"><strong>Extralaborales</strong></li>
    <li style="margin-bottom: 3pt;"><strong>Individuales</strong></li>
</ol>

<p style="font-size: 9pt; text-align: justify; margin: 0 0 8pt 0;">
El Ministerio de Protección Social en 2010, actualmente el Ministerio de Trabajo en convenio con la Pontificia Universidad Javeriana diseñó una Batería de Instrumentos para la Evaluación de Factores de Riesgo Psicosocial que permiten la valoración de los factores psicosociales para las empresas públicas y privadas en Colombia y evaluando las siguientes condiciones:
</p>
';
    }

    /**
     * Condiciones Intralaborales con tabla de dominios (COMPACTA - debe caber en una página)
     */
    protected function renderCondicionesIntralaborales()
    {
        return '
<div class="page-break"></div>
<h3 style="font-size: 11pt; color: #006699; margin: 0 0 5pt 0; text-decoration: underline;">Condiciones Intralaborales:</h3>

<p style="font-size: 8pt; text-align: justify; margin: 0 0 5pt 0;">
Se entienden como aquellas características del trabajo y de su organización que influyen en la salud y bienestar de los colaboradores. El instrumento establece cuatro dominios que agrupan dimensiones que actúan como posibles fuentes de riesgo:
</p>

<table style="width: 100%; border-collapse: collapse; margin: 3pt 0; font-size: 7pt; line-height: 1.2;">
    <thead>
        <tr>
            <th style="width: 15%; background-color: #006699; color: white; padding: 2pt; border: 1pt solid #333; text-align: center; font-size: 7pt;">CONSTRUCTO</th>
            <th style="width: 20%; background-color: #006699; color: white; padding: 2pt; border: 1pt solid #333; text-align: center; font-size: 7pt;">DOMINIOS</th>
            <th style="width: 65%; background-color: #006699; color: white; padding: 2pt; border: 1pt solid #333; text-align: center; font-size: 7pt;">DIMENSIONES</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td rowspan="4" style="background-color: #e8f4fc; padding: 2pt; border: 1pt solid #333; text-align: center; vertical-align: middle; font-weight: bold; font-size: 6.5pt;">CONDICIONES<br>INTRALABORALES</td>
            <td style="padding: 2pt; border: 1pt solid #333; text-align: center; font-weight: bold; font-size: 6.5pt;">DEMANDAS DEL TRABAJO</td>
            <td style="padding: 2pt; border: 1pt solid #333; font-size: 6.5pt; line-height: 1.1;">
                Demandas cuantitativas | Demandas de carga mental | Demandas emocionales | Exigencias de responsabilidad del cargo | Demandas ambientales y de esfuerzo físico | Demandas de la jornada de trabajo | Consistencia del rol | Influencia del ambiente laboral sobre el extralaboral
            </td>
        </tr>
        <tr>
            <td style="padding: 2pt; border: 1pt solid #333; text-align: center; font-weight: bold; font-size: 6.5pt;">CONTROL</td>
            <td style="padding: 2pt; border: 1pt solid #333; font-size: 6.5pt; line-height: 1.1;">
                Control y autonomía sobre el trabajo | Oportunidades de desarrollo y uso de habilidades y destrezas | Participación y manejo del cambio | Claridad de rol | Capacitación
            </td>
        </tr>
        <tr>
            <td style="padding: 2pt; border: 1pt solid #333; text-align: center; font-weight: bold; font-size: 6.5pt;">LIDERAZGO Y RELACIONES SOCIALES</td>
            <td style="padding: 2pt; border: 1pt solid #333; font-size: 6.5pt; line-height: 1.1;">
                Características del liderazgo | Relaciones sociales en el trabajo | Retroalimentación del desempeño | Relación con los colaboradores (subordinados)
            </td>
        </tr>
        <tr>
            <td style="padding: 2pt; border: 1pt solid #333; text-align: center; font-weight: bold; font-size: 6.5pt;">RECOMPENSA</td>
            <td style="padding: 2pt; border: 1pt solid #333; font-size: 6.5pt; line-height: 1.1;">
                Reconocimiento y compensación | Recompensas derivadas de la pertenencia a la organización y del trabajo que se realiza
            </td>
        </tr>
    </tbody>
</table>

<p style="font-size: 8pt; text-align: justify; margin: 5pt 0 3pt 0;">
<strong>1. Demandas del trabajo:</strong> Exigencias que el trabajo impone al colaborador: cognitivas, mentales, emocionales, de responsabilidad, del ambiente laboral y de jornada de trabajo.
</p>

<p style="font-size: 8pt; text-align: justify; margin: 0 0 3pt 0;">
<strong>2. Control sobre el trabajo:</strong> Posibilidad que el trabajo ofrece para influir y tomar decisiones. Incluye iniciativa, autonomía, uso de habilidades, participación, claridad del rol y capacitación.
</p>

<p style="font-size: 8pt; text-align: justify; margin: 0 0 3pt 0;">
<strong>3. Liderazgo y relaciones sociales:</strong> Relación entre superiores y colaboradores, interacción con otras personas, retroalimentación, trabajo en equipo, apoyo social y cohesión.
</p>

<p style="font-size: 8pt; text-align: justify; margin: 0 0 5pt 0;">
<strong>4. Recompensas:</strong> Retribución por contribuciones laborales: compensación económica, reconocimiento, trato justo, promoción, seguridad, educación, satisfacción e identificación con la organización.
</p>
';
    }

    /**
     * Condiciones Extralaborales
     */
    protected function renderCondicionesExtralaborales()
    {
        return '
<h3 style="font-size: 11pt; color: #006699; margin: 12pt 0 8pt 0; text-decoration: underline;">Condiciones Extralaborales:</h3>

<p style="font-size: 9pt; text-align: justify; margin: 0 0 8pt 0;">
Comprenden todos aquellos aspectos del entorno familiar, social y económico del colaborador. A su vez abarcan las condiciones del lugar de vivienda, que pueden influir en la salud y bienestar de las personas.
</p>

<table style="width: 100%; border-collapse: collapse; margin: 8pt 0; font-size: 8pt;">
    <thead>
        <tr>
            <th style="width: 35%; background-color: #006699; color: white; padding: 5pt; border: 1pt solid #333; text-align: center;">CONSTRUCTO</th>
            <th style="width: 65%; background-color: #006699; color: white; padding: 5pt; border: 1pt solid #333; text-align: center;">DIMENSIONES</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="background-color: #e8f4fc; padding: 6pt; border: 1pt solid #333; text-align: center; font-weight: bold;">CONDICIONES EXTRALABORALES</td>
            <td style="padding: 6pt; border: 1pt solid #333;">
                <span style="color: #006699;">Tiempo fuera del trabajo</span><br>
                <span style="color: #006699;">Relaciones familiares</span><br>
                <span style="color: #006699;">Comunicación y relaciones interpersonales</span><br>
                <span style="color: #006699;">Situación económica del grupo familiar</span><br>
                <span style="color: #006699;">Características de la vivienda y de su entorno</span><br>
                <span style="color: #006699;">Influencia del entorno extralaboral sobre el trabajo</span><br>
                <span style="color: #006699;">Desplazamiento vivienda – trabajo – vivienda</span>
            </td>
        </tr>
    </tbody>
</table>
';
    }

    /**
     * Condiciones Individuales
     */
    protected function renderCondicionesIndividuales()
    {
        return '
<h3 style="font-size: 11pt; color: #006699; margin: 12pt 0 8pt 0; text-decoration: underline;">Condiciones Individuales:</h3>

<p style="font-size: 9pt; text-align: justify; margin: 0 0 8pt 0;">
Aluden una serie de características sociodemográficas de cada colaborador como lo son el sexo, la edad, el estado civil, el nivel educativo, la profesión u oficio, el lugar de residencia, el estrato socioeconómico, el tipo de vivienda y el número de dependientes. Estas características sociodemográficas pueden modular la percepción y el efecto de los factores de riesgo Intralaborales y Extralaborales. Al igual que las características sociodemográficas, existen otros aspectos ocupacionales de los colaboradores que también pueden modular los factores psicosociales tales como la antigüedad en la empresa, el cargo, el tipo de contratación y la modalidad de pago, entre otras, las cuales se indagan con los instrumentos de la batería para la evaluación de los factores psicosociales.
</p>
';
    }

    /**
     * Marco Legal extenso
     */
    protected function renderMarcoLegal()
    {
        return '
<div class="page-break"></div>
<h2 style="font-size: 13pt; color: #006699; margin: 0 0 10pt 0; padding-bottom: 3pt; border-bottom: 1pt solid #006699;">Marco Legal</h2>

<p style="font-size: 9pt; text-align: justify; margin: 0 0 8pt 0;">
A continuación, se relaciona algunas normatividades aplicables para la valoración en salud y factores de riesgo psicosocial en entornos laborales colombianos:
</p>

<ul style="font-size: 9pt; margin: 5pt 0 8pt 15pt; padding: 0; list-style-type: disc;">
    <li style="margin-bottom: 6pt; text-align: justify;">
        <strong>Decreto 614 de 1984</strong> en el literal c del artículo 2 señala como objeto de la salud ocupacional, proteger a la persona contra los riesgos relacionados con agentes físicos, químicos, biológicos, psicosociales, mecánicos, eléctricos y otros derivados de la organización laboral que puedan afectar la salud individual y colectiva en los lugares de trabajo.
    </li>
    <li style="margin-bottom: 6pt; text-align: justify;">
        <strong>Resoluciones 1016 de 1989 y 1075 de 1992</strong> se emite que los empleadores públicos y privados, incluirán dentro de las actividades del subprograma de medicina preventiva, campañas específicas tendientes a fomentar la prevención y el control sustancias psicoactivas, alcoholismo y tabaquismo en sus colaboradores. Y en el numeral 12 del artículo 10, una de las actividades del mismo subprograma es diseñar y ejecutar actividades orientadas a la prevención y control de enfermedades generadas por los riesgos psicosociales.
    </li>
    <li style="margin-bottom: 6pt; text-align: justify;">
        <strong>Ley 1010 de 2006</strong> por medio de la cual se adoptan medidas para prevenir el acoso laboral y otros hostigamientos en el marco de las relaciones de trabajo.
    </li>
    <li style="margin-bottom: 6pt; text-align: justify;">
        <strong>Resolución 2646 de 2008</strong> por la cual se establecen las disposiciones y se definen responsabilidades para la identificación, evaluación, prevención, intervención y monitoreo permanente de la exposición a factores de riesgo psicosocial en el trabajo y para la determinación del origen de las patologías causadas por el estrés ocupacional.
    </li>
    <li style="margin-bottom: 6pt; text-align: justify;">
        <strong>Resolución 652 de 2012</strong> en donde se establece la exigencia de la conformación y funcionamiento del Comité de Convivencia Laboral, el cual pretende prevenir la aparición de acoso laboral el cual se encuentra claramente definido en la Ley 1010 de 2006. En el artículo 3 de la resolución 2646 del 2008, se establecen las disposiciones y se definen responsabilidades para la identificación, evaluación, prevención, intervención y monitoreo permanente de la exposición a factores de riesgo psicosocial en el trabajo y para la determinación del origen de las patologías causadas por el estrés ocupacional y, adopta la definición de Acoso Laboral así:
        <p style="font-size: 8pt; font-style: italic; margin: 5pt 0 5pt 10pt; padding: 5pt; background-color: #f5f5f5; border-left: 2pt solid #006699;">
        "es toda conducta persistente y demostrable, ejercida sobre un empleado, trabajador por parte de un empleador, un jefe o superior jerárquico inmediato o mediato, un compañero de trabajo o un subalterno, encaminada a infundir miedo, intimidación, terror y angustia, a causar perjuicio laboral, generar desmotivación en el trabajo, o inducir la renuncia del mismo, conforme lo establece la Ley 1010 de 2006. (...)".
        </p>
    </li>
    <li style="margin-bottom: 6pt; text-align: justify;">
        <strong>Decreto 472 de 2015</strong> por el cual se reglamenta los criterios de graduación de las multas por infracción a las normas de Seguridad y Salud en el Trabajo y Riesgos Laborales, se señalan normas para la aplicación de la orden de clausura del lugar o cierre definitivo de la empresa y paralización o prohibición inmediata de trabajos o tareas:
        <ul style="margin: 5pt 0 5pt 15pt; font-size: 8pt;">
            <li style="margin-bottom: 4pt; font-style: italic;">Parágrafo 1: las empresas en las cuales se ha identificado un nivel de riesgo medio o bajo deben realizar acciones preventivas y correctivas, y una vez implementadas, realizar la evaluación correspondiente como mínimo cada dos años, para hacer seguimiento a los factores de riesgo y contar con información actualizada.</li>
            <li style="margin-bottom: 4pt; font-style: italic;">Parágrafo 2: el informe de accidente de trabajo o enfermedad laboral se considera una prueba, entre otras, para la determinación del origen por parte de las instancias establecidas por ley. En ningún caso reemplaza el procedimiento establecido para tal determinación ni es requisito para el pago de prestaciones asistenciales o económicas al colaborador, pero una vez radicado en la administradora de riesgos laborales da inicio la asignación de la reserva correspondiente.</li>
        </ul>
    </li>
    <li style="margin-bottom: 6pt; text-align: justify;">
        <strong>Resolución 2764 de 2022</strong> tiene por objeto adoptar como referentes técnicos mínimos obligatorios para la identificación, evaluación, monitoreo permanente e intervención de los factores de riesgo psicosocial, de los Instrumentos de Evaluación y Guías de Intervención dispuestos por el ministerio de trabajo. Esta misma resolución en su artículo 3 decreta que la evaluación de los factores de riesgo psicosocial debe realizarse de forma periódica, de acuerdo con el nivel de riesgo de las empresas.
        <ul style="margin: 5pt 0 5pt 15pt; font-size: 8pt;">
            <li style="margin-bottom: 4pt; font-style: italic;">Parágrafo 1: las empresas en las cuales se ha identificado un nivel de factores psicosociales nocivos evaluados como de alto riesgo o que están causando efectos negativos en la salud, en el bienestar o en el trabajo, deben realizar la evaluación de forma anual, enmarcado dentro del sistema de vigilancia epidemiológica de factores de riesgo psicosociales.</li>
        </ul>
    </li>
</ul>
';
    }

    /**
     * Objetivos
     */
    protected function renderObjetivos()
    {
        $company = $this->companyData;

        return '
<div class="page-break"></div>
<h2 style="font-size: 13pt; color: #006699; margin: 0 0 10pt 0; padding-bottom: 3pt; border-bottom: 1pt solid #006699;">Objetivos</h2>

<h4 style="font-size: 10pt; color: #006699; margin: 8pt 0 5pt 0; text-decoration: underline;">Objetivo General</h4>
<ul style="font-size: 9pt; margin: 3pt 0 8pt 15pt; padding: 0;">
    <li style="text-align: justify;">
        Identificar los factores de riesgo psicosocial en la empresa <strong>' . esc($company['company_name'] ?? 'EMPRESA') . '</strong>, a través de la Batería de Instrumentos para la Evaluación de Factores de Riesgo Psicosocial avalada por el Ministerio de Trabajo de Colombia en las resoluciones 2646 de 2008 y 2764 de 2022 con el fin establecer acciones que disminuyan los riesgos que puedan estar afectando a sus colaboradores.
    </li>
</ul>

<h4 style="font-size: 10pt; color: #006699; margin: 8pt 0 5pt 0; text-decoration: underline;">Objetivos Específicos</h4>
<ul style="font-size: 9pt; margin: 3pt 0 8pt 15pt; padding: 0;">
    <li style="margin-bottom: 4pt; text-align: justify;">Realizar la caracterización sociodemográfica de los colaboradores de manera individual.</li>
    <li style="margin-bottom: 4pt; text-align: justify;">Identificar y evaluar las condiciones de riesgo intralaboral, extralaboral y efectos del estrés percibidos por los colaboradores.</li>
    <li style="margin-bottom: 4pt; text-align: justify;">Generar recomendaciones que permitan la intervención y el mejoramiento de las condiciones de trabajo previniendo y/o aminorando el riesgo de alteraciones físicas, sociales, intelectuales y emocionales de los colaboradores en su lugar de trabajo.</li>
</ul>
';
    }

    /**
     * Metodología con tabla de instrumentos
     */
    protected function renderMetodologia()
    {
        return '
<h2 style="font-size: 13pt; color: #006699; margin: 0 0 10pt 0; padding-bottom: 3pt; border-bottom: 1pt solid #006699;">Metodología</h2>

<h4 style="font-size: 10pt; color: #006699; margin: 8pt 0 5pt 0; text-decoration: underline;">Instrumentos</h4>

<p style="font-size: 9pt; text-align: justify; margin: 0 0 8pt 0;">
Se presenta a continuación los cuestionarios que se usaron en este ejercicio diagnóstico avalados por el Ministerio de Trabajo y elaborados por la Pontificia Universidad Javeriana descritos en la Tabla instrumento - objetivo, con el acompañamiento permanente de un profesional en Psicología, Especialista en Gestión de la Seguridad y Salud en el Trabajo, con licencia vigente.
</p>

<table style="width: 100%; border-collapse: collapse; margin: 8pt 0; font-size: 8pt;">
    <thead>
        <tr>
            <th style="width: 35%; background-color: #006699; color: white; padding: 5pt; border: 1pt solid #333; text-align: center;">Instrumento</th>
            <th style="width: 65%; background-color: #006699; color: white; padding: 5pt; border: 1pt solid #333; text-align: center;">Objetivo</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="padding: 5pt; border: 1pt solid #333; font-style: italic; vertical-align: top;"><strong>Factores de riesgo Intralaboral (Forma A y B)</strong></td>
            <td style="padding: 5pt; border: 1pt solid #333; font-size: 7.5pt;">
                <strong>Objetivo:</strong> identificar los factores de riesgo psicosocial intralaboral y su nivel de riesgo<br>
                <strong>Tipo de instrumento:</strong> cuestionario que recopila información subjetiva del colaborador que lo responde<br>
                <strong>Duración de la aplicación:</strong> entre 28 a 33 minutos<br>
                <strong>Forma A:</strong> orientado a personas que ocupan cargos de jefatura, profesionales y técnicos<br>
                <strong>Forma B:</strong> orientado a personas que ocupan cargos dentro de los grupos auxiliares y operarios
            </td>
        </tr>
        <tr>
            <td style="padding: 5pt; border: 1pt solid #333; font-style: italic; vertical-align: top;"><strong>Cuestionario de Factores Psicosociales Extralaborales</strong></td>
            <td style="padding: 5pt; border: 1pt solid #333; font-size: 7.5pt;">
                <strong>Objetivo:</strong> identificar los factores de riesgo psicosocial extralaboral y sus niveles de riesgo<br>
                <strong>Tipo de instrumento:</strong> cuestionario que recopila información subjetiva del colaborador<br>
                <strong>Duración de la aplicación:</strong> 7 minutos
            </td>
        </tr>
        <tr>
            <td style="padding: 5pt; border: 1pt solid #333; font-style: italic; vertical-align: top;"><strong>Cuestionario para la evaluación del estrés</strong></td>
            <td style="padding: 5pt; border: 1pt solid #333; font-size: 7.5pt;">
                <strong>Objetivos:</strong> identificar los síntomas fisiológicos, de comportamiento social y laboral, intelectuales y psicoemocionales del estrés<br>
                <strong>Tipo de instrumento:</strong> cuestionario que recopila información subjetiva del trabajador que lo responde<br>
                <strong>Duración:</strong> 7 minutos
            </td>
        </tr>
        <tr>
            <td style="padding: 5pt; border: 1pt solid #333; font-style: italic; vertical-align: top;"><strong>Ficha de datos generales</strong></td>
            <td style="padding: 5pt; border: 1pt solid #333; font-size: 7.5pt;">
                <strong>Objetivo:</strong> caracterización de la población de acuerdo con:<br>
                <strong>Información sociodemográfica:</strong> sexo, edad, estado civil, escolaridad, lugar de residencia, estrato socioeconómico, tipo vivienda y número personas a cargo<br>
                <strong>Información ocupacional:</strong> lugar de trabajo, antigüedad, tipo cargo, departamento, sección o área donde trabaja, tipo de contrato, horas diarias de trabajo y modalidad de pago.
            </td>
        </tr>
    </tbody>
</table>

<h4 style="font-size: 10pt; color: #006699; margin: 10pt 0 5pt 0; text-decoration: underline;">Procedimiento</h4>

<ul style="font-size: 9pt; margin: 3pt 0 5pt 15pt; padding: 0;">
    <li style="margin-bottom: 3pt; text-align: justify;">Se definió el plan de intervención y definición de fechas de toma de la encuesta, así mismo el plan de socialización y capacitación de:</li>
</ul>

<p style="font-size: 9pt; margin: 0 0 2pt 20pt;">a. ¿Qué es el riesgo psicosocial?</p>
<p style="font-size: 9pt; margin: 0 0 2pt 20pt;">b. Normatividad legal Riesgo psicosocial</p>
<p style="font-size: 9pt; margin: 0 0 5pt 20pt;">c. Objetivo y metodología de la aplicación.</p>

<ul style="font-size: 9pt; margin: 3pt 0 5pt 15pt; padding: 0;">
    <li style="margin-bottom: 3pt; text-align: justify;">Se solicitó por medio de un consentimiento informado, la autorización de los colaboradores para el procedimiento de evaluación del riesgo psicosocial y cumplimiento con lo establecido en la Ley 1090.</li>
</ul>

<p style="font-size: 9pt; text-align: justify; margin: 5pt 0 8pt 0;">
Posterior a la aplicación, se registra los resultados a un sistema de información y se realiza un análisis generando una calificación de riesgo de acuerdo con la Tabla de niveles de riesgo, con esto se aprovechará los hallazgos para definir recomendaciones generales y específicas asociadas al control del riesgo psicosocial en la organización.
</p>

<table style="width: 100%; border-collapse: collapse; margin: 5pt 0; font-size: 8pt;">
    <thead>
        <tr>
            <th style="width: 20%; background-color: #006699; color: white; padding: 4pt; border: 1pt solid #333; text-align: center;">Nivel de riesgo</th>
            <th style="width: 80%; background-color: #006699; color: white; padding: 4pt; border: 1pt solid #333; text-align: center;">Descripción</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="background-color: #4CAF50; color: white; text-align: center; font-weight: bold; padding: 3pt; border: 1pt solid #333;">Sin riesgo</td>
            <td style="padding: 3pt; border: 1pt solid #333; font-size: 7.5pt;">Ausencia de riesgo o riesgo tan bajo que no amerita desarrollar actividades de Intervención</td>
        </tr>
        <tr>
            <td style="background-color: #8BC34A; color: white; text-align: center; font-weight: bold; padding: 3pt; border: 1pt solid #333;">Riesgo bajo</td>
            <td style="padding: 3pt; border: 1pt solid #333; font-size: 7.5pt;">No se espera que los factores psicosociales que obtengan puntuaciones de este nivel estén relacionados con síntomas o respuestas significativas</td>
        </tr>
        <tr>
            <td style="background-color: #FFEB3B; color: #333; text-align: center; font-weight: bold; padding: 3pt; border: 1pt solid #333;">Riesgo medio</td>
            <td style="padding: 3pt; border: 1pt solid #333; font-size: 7.5pt;">Se esperaría una respuesta de estrés moderada. Las dimensiones y dominios que se encuentren bajo esta categoría ameritan observación y acciones de intervención preventivas</td>
        </tr>
        <tr>
            <td style="background-color: #FF9800; color: white; text-align: center; font-weight: bold; padding: 3pt; border: 1pt solid #333;">Riesgo alto</td>
            <td style="padding: 3pt; border: 1pt solid #333; font-size: 7.5pt;">Tiene una importante posibilidad de asociación con respuestas de estrés alto y por tanto las dimensiones y dominios que se encuentren bajo esta categoría requieren intervención en el marco de un sistema de vigilancia epidemiológica</td>
        </tr>
        <tr>
            <td style="background-color: #F44336; color: white; text-align: center; font-weight: bold; padding: 3pt; border: 1pt solid #333;">Riesgo muy alto</td>
            <td style="padding: 3pt; border: 1pt solid #333; font-size: 7.5pt;">Tiene una amplia posibilidad de asociarse a respuestas muy altas de estrés. Por consiguiente, las dimensiones y dominios que se encuentren bajo esta categoría requieren intervención inmediata en el marco de un sistema de Vigilancia Epidemiológica</td>
        </tr>
    </tbody>
</table>
';
    }
}
