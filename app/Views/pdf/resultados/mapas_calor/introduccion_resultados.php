<!-- PÁGINA: INTRODUCCIÓN A RESULTADOS -->
<?= $this->include('pdf/_partials/header') ?>

<h2 class="section-title" style="color: #006699; text-align: center; text-decoration: underline; margin-bottom: 20px;">
    Resultados
</h2>

<div style="text-align: justify; line-height: 1.8; font-size: 10pt;">
    <p>
        Se contó con la participación de <strong><?= $totalParticipantes ?></strong> personas vinculadas a
        <strong><?= esc($companyName) ?></strong>, de los cuales <strong><?= $formaA ?></strong> personas son
        equivalentes al <strong><?= $pctFormaA ?>%</strong> con el cuestionario intralaboral Tipo A y
        <strong><?= $formaB ?></strong> personas fueron evaluados con el formato intralaboral Tipo B,
        equivalente al <strong><?= $pctFormaB ?>%</strong>. Al mismo tiempo, el 100% de los participantes
        diligenciaron el Cuestionario de evaluación riesgo psicosocial Intralaboral, extralaboral, el cuestionario
        de estrés y la ficha de datos generales; así como el consentimiento informado.
    </p>

    <p style="margin-top: 15px;">
        <strong>Los resultados obtenidos se presentan en el siguiente orden:</strong>
    </p>

    <ol style="margin-left: 20px; margin-top: 10px;">
        <li style="margin-bottom: 8px;">Resultados de las condiciones individuales – Información sociodemográfica y ocupacional.</li>
        <li style="margin-bottom: 8px;">Resultados de la evaluación de factores de riesgo psicosocial (intralaboral, extralaboral).</li>
        <li style="margin-bottom: 8px;">Resultados de la evaluación de estrés ocupacional.</li>
    </ol>
</div>

<h3 style="color: #006699; margin-top: 25px; margin-bottom: 15px; font-size: 12pt;">
    Resultados de las condiciones individuales - Información sociodemográfica y ocupacional
</h3>

<div style="text-align: justify; line-height: 1.8; font-size: 10pt;">
    <p>
        Estas hacen referencia a algunas características propias del colaborador como sexo, edad, estado civil,
        nivel educativo, escala socioeconómica, tipo de personas, véase la Tabla de variables sociodemográficas,
        y algunos aspectos ocupacionales como antigüedad de la empresa, el cargo, tipo de contratación y modalidad
        de pago, véase Tabla de resultados ocupacionales.
    </p>
</div>

<?= $this->include('pdf/_partials/footer') ?>
<div class="page-break"></div>
