<!-- PÁGINA: SÍNTESIS GENERAL SOCIODEMOGRÁFICA -->
<?= $this->include('pdf/_partials/header') ?>

<h2 class="section-title" style="color: #006699; text-align: center; text-decoration: underline; margin-bottom: 20px;">
    SÍNTESIS GENERAL
</h2>

<?php if (!empty($sintesisIA)): ?>
<!-- Texto IA de síntesis guardado -->
<div style="text-align: justify; line-height: 1.8; font-size: 10pt;">
    <?= nl2br(esc($sintesisIA)) ?>
</div>
<?php else: ?>
<!-- Mensaje cuando no hay síntesis guardada -->
<p style="text-align: center; color: #999; font-style: italic; padding: 40px 0;">
    No hay síntesis general guardada. Genere y guarde la interpretación IA desde el módulo de Ficha de Datos Generales.
</p>
<?php endif; ?>

<?php if (!empty($sintesisComment)): ?>
<!-- Comentario del consultor integrado al texto -->
<div style="text-align: justify; line-height: 1.8; font-size: 10pt; margin-top: 15px;">
    <?= nl2br(esc($sintesisComment)) ?>
</div>
<?php endif; ?>

<?= $this->include('pdf/_partials/footer') ?>
<div class="page-break"></div>
