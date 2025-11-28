<!-- PÁGINA: CONCLUSIONES DE LA BATERÍA -->
<?= $this->include('pdf/_partials/header') ?>

<h2 class="section-title" style="color: #006699; text-align: center; text-decoration: underline; margin-bottom: 20px;">
    Conclusión Total De Aplicación Batería De Riesgo Psicosocial
</h2>

<div style="text-align: justify; line-height: 1.8; font-size: 10pt;">
    <p>
        Los dominios principales de la batería de riesgo psicosocial como sus respectivas dimensiones son calificadas
        a partir de la interpretación del mayor puntaje obtenido, siendo este el factor determinante para establecer
        el panorama de riesgo psicosocial.
    </p>

    <p style="margin-top: 15px;">
        Los resultados obtenidos del nivel de riesgo psicosocial a nivel general en <strong><?= esc($companyName) ?></strong>
        se clasifican en <strong style="color: <?= $colorNivelGeneral ?>;"><?= $nivelRiesgoGeneral ?></strong>
        <?php if (!empty($puntajeFormaA)): ?>
        (Cuestionario Tipo A = Calificación de <?= number_format($puntajeFormaA, 1) ?> catalogado como <?= $nivelFormaA ?>)
        <?php endif; ?>
        <?php if (!empty($puntajeFormaB)): ?>
        / Cuestionario Tipo B con una calificación de <?= number_format($puntajeFormaB, 1) ?> catalogado como <?= $nivelFormaB ?>).
        <?php endif; ?>
    </p>

    <p style="margin-top: 15px;">
        Las dimensiones y dominios que se encuentren bajo esta categoría serán objeto de acciones o programas de
        intervención, a fin de mantenerlos en los niveles de riesgo más bajos posibles.
    </p>

    <p style="margin-top: 15px;">
        De acuerdo con el artículo 3 de la resolución 2764 del 2022, el periodo de la próxima medición se establece
        de acuerdo con el puntaje de la dimensión principal intralaboral observado anteriormente.
        <strong>Asimismo, se debe realizar una nueva medición en un plazo máximo de <?= $periodicidad == 1 ? 'un año' : 'dos años' ?>.</strong>
    </p>
</div>

<h2 class="section-title" style="color: #006699; text-align: center; text-decoration: underline; margin-top: 30px; margin-bottom: 20px;">
    Conclusión Del Profesional
</h2>

<div style="text-align: justify; line-height: 1.8; font-size: 10pt;">
    <p>
        El entorno de análisis de la batería de riesgo psicosocial consta de tres dimensiones principales las cuales
        constantemente interactúan entre sí; Al observar la perspectiva global se denota el nivel de
        <strong style="color: <?= $colorNivelEstres ?>;"><?= $nivelEstresGeneral ?></strong> en la dimensión principal de estrés
        <?php if (!empty($puntajeEstresA)): ?>
        (Cuestionario Tipo A = Calificación de <?= number_format($puntajeEstresA, 1) ?> catalogado como <?= $nivelEstresA ?>
        <?php endif; ?>
        <?php if (!empty($puntajeEstresB)): ?>
        Cuestionario Tipo B = Calificación de <?= number_format($puntajeEstresB, 1) ?> catalogado como <?= $nivelEstresB ?>)
        <?php endif; ?>
    </p>

    <?php if (in_array($nivelEstresGeneral, ['Riesgo Alto', 'Riesgo Muy Alto'])): ?>
    <p style="margin-top: 15px;">
        Se sugiere proceder mediante la <strong>Acción Inmediata del Programa de vigilancia epidemiológico:</strong>
        Concentración elevada de niveles de estrés sobre este grupo poblacional, Diseño de Programas De Vigilancia
        Epidemiológica En Riesgo Psicosocial. El factor de riesgo causa, o podría causar, alteraciones serias en la
        salud del trabajador, aumentando en consecuencia el número de incapacidades laborales.
    </p>
    <?php endif; ?>

    <p style="margin-top: 15px;">
        Se sugiere realizar una nueva medición dentro de <?= $periodicidad == 1 ? 'un año' : 'dos años' ?>
        en función de llevar a cabo un correcto seguimiento de efectividad en la dimensión estrés.
    </p>
</div>

<?= $this->include('pdf/_partials/footer') ?>
<div class="page-break"></div>
