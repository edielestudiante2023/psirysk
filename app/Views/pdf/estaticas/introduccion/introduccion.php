<!-- PÁGINA: INTRODUCCIÓN + MARCO CONCEPTUAL -->
<?= $this->include('pdf/_partials/header') ?>

<h2 class="section-title" style="color: #006699; text-align: center; margin-bottom: 20px;">Introducción</h2>

<p style="text-align: justify; line-height: 1.6;">
    Este informe tiene como finalidad identificar y evaluar los factores de riesgo psicosocial en los
    colaboradores de la empresa <strong><?= esc($company['company_name'] ?? 'EMPRESA') ?></strong>; en la ciudad de
    <?= esc($company['city'] ?? 'Bogotá D.C.') ?>, en donde <strong>(<?= $totalParticipantes ?>)</strong>
    personas han dado respuesta a la Batería de Instrumentos para la Evaluación de Factores de Riesgo
    Psicosocial avalada por el Ministerio de Trabajo de Colombia junto al Auto reporte de síntomas de
    estrés las cuales están evalúan alteraciones asociadas a condiciones estresantes del trabajo. Se
    dividieron en <?= $formaA ?> personas para Forma A y <?= $formaB ?> Personas para forma B.
</p>

<p style="text-align: justify; line-height: 1.6;">
    Lo anterior es una acción de cumplimiento frente a la Resolución 2646 de julio de 2008 declarada
    por el Ministerio de la Protección Social de Colombia y ante la reciente Resolución 2764 de 2022
    emitida por el Ministerio de Trabajo de Colombia, reglamentan las responsabilidades empresariales
    en cuanto a la identificación, evaluación, prevención, intervención y control permanente a la
    exposición de los factores de riesgo psicosocial y; estudio y determinación de origen de las
    patologías presuntamente causadas por estrés ocupacional.
</p>

<p style="text-align: justify; line-height: 1.6;">
    De acuerdo con el Ministerio de Trabajo Partiendo definió el estrés ocupacional como:
</p>

<blockquote style="font-style: italic; margin: 15px 30px; padding: 10px 20px; border-left: 3px solid #006699; background-color: #f5f5f5;">
    "el conjunto de reacciones de carácter psicológico (mentales, emocionales y de comportamiento),
    que se producen cuando la persona debe enfrentar a demandas derivadas de su interacción con las
    condiciones de un entorno laboral, entendidas como factores de riesgo psicosocial, ante las cuales
    su capacidad de afrontamiento es insuficiente, causando una alteración en su bienestar físico,
    mental y emocional".
</blockquote>

<p style="text-align: justify; line-height: 1.6;">
    En este orden de ideas, la empresa <strong><?= esc($company['company_name'] ?? 'EMPRESA') ?></strong>,
    aprovecha este ejercicio diagnóstico para ejecutar una intervención eficiente y oportuna, por medio
    de la implementación de un Sistema de Vigilancia para el control del estrés y de los factores de
    riesgo psicosociales, en aras de crear una cultura de salud y seguridad en todos sus colaboradores.
</p>

<h2 class="section-title" style="color: #006699; text-align: center; margin-top: 30px; margin-bottom: 20px;">Marco Conceptual</h2>

<p style="text-align: justify; line-height: 1.6;">
    Los factores psicosociales se encuentran definidos como todas aquellas condiciones del trabajo, del
    entorno, o de la persona, que en una interrelación dinámica generan percepciones y experiencias,
    que influyen negativamente en su salud y en desempeño laboral de las personas (Ministerio de
    Trabajo, 2014). Dichas se determinan y comprenden a través de tres tipos de condiciones:
</p>

<ol style="margin-left: 30px; line-height: 1.8;">
    <li>Intralaborales</li>
    <li>Extralaborales</li>
    <li>Individuales</li>
</ol>

<p style="text-align: justify; line-height: 1.6;">
    El Ministerio de Protección Social en 2010, actualmente el Ministerio de Trabajo en convenio con la
    Pontificia Universidad Javeriana diseño una Batería de Instrumentos para la Evaluación de Factores
    de Riesgo Psicosocial que permiten la valoración de los factores psicosociales para las empresas
    públicas y privadas en Colombia y evaluando las siguientes condiciones:
</p>

<?= $this->include('pdf/_partials/footer') ?>
<div class="page-break"></div>
