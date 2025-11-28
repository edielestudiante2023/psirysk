<!-- PÁGINA: OBJETIVOS (página separada si se necesita) -->
<?= $this->include('pdf/_partials/header') ?>

<h2 class="section-title" style="color: #006699; text-align: center; margin-bottom: 20px;">Objetivos</h2>

<h3 style="color: #006699; text-decoration: underline; margin-bottom: 15px;">Objetivo General</h3>
<ul style="list-style-type: disc; margin-left: 30px; line-height: 1.8;">
    <li style="text-align: justify;">
        Identificar los factores de riesgo psicosocial en la empresa <?= esc($company['company_name'] ?? 'EMPRESA') ?>, a
        través de la Batería de Instrumentos para la Evaluación de Factores de Riesgo Psicosocial
        avalada por el Ministerio de Trabajo de Colombia en las resoluciones 2646 de 2008 y 2764
        de 2022 con el fin establecer acciones que disminuyan los riesgos que puedan estar
        afectando a sus colaboradores.
    </li>
</ul>

<h3 style="color: #006699; text-decoration: underline; margin-top: 25px; margin-bottom: 15px;">Objetivos Específicos</h3>
<ul style="list-style-type: disc; margin-left: 30px; line-height: 1.8;">
    <li style="margin-bottom: 12px; text-align: justify;">
        Realizar la caracterización sociodemográfica de los colaboradores de manera individual.
    </li>
    <li style="margin-bottom: 12px; text-align: justify;">
        Identificar y evaluar las condiciones de riesgo intralaboral, extralaboral y efectos del estrés
        percibidos por los colaboradores.
    </li>
    <li style="margin-bottom: 12px; text-align: justify;">
        Generar recomendaciones que permitan la intervención y el mejoramiento de las
        condiciones de trabajo previniendo y/o aminorando el riesgo de alteraciones físicas,
        sociales, intelectuales y emocionales de los colaboradores en su lugar de trabajo.
    </li>
</ul>

<?= $this->include('pdf/_partials/footer') ?>
<div class="page-break"></div>
