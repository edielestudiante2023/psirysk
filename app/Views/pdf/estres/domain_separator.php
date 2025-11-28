<div class="pdf-page domain-separator-page">
    <?= view('pdf/_partials/header', ['company' => $company, 'consultant' => $consultant]) ?>

    <div class="pdf-content" style="display: flex; align-items: center; justify-content: center; min-height: 180mm;">
        <div class="domain-separator-content" style="text-align: center;">
            <!-- Icono del dominio -->
            <div style="margin-bottom: 30px;">
                <svg width="80" height="80" viewBox="0 0 80 80" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="40" cy="40" r="38" fill="#9B59B6" stroke="#7D3C98" stroke-width="2"/>
                    <text x="40" y="28" text-anchor="middle" fill="white" font-size="28" font-weight="bold">⚡</text>
                    <text x="40" y="55" text-anchor="middle" fill="white" font-size="16" font-weight="bold">ESTRÉS</text>
                </svg>
            </div>

            <!-- Titulo -->
            <div style="
                background: linear-gradient(135deg, #9B59B6 0%, #7D3C98 100%);
                color: white;
                padding: 20px 60px;
                border-radius: 10px;
                margin-bottom: 20px;
                box-shadow: 0 4px 15px rgba(155, 89, 182, 0.3);
            ">
                <div style="font-size: 14pt; letter-spacing: 3px; margin-bottom: 10px; opacity: 0.9;">
                    CUESTIONARIO INDEPENDIENTE
                </div>
                <h1 style="
                    font-size: 20pt;
                    margin: 0;
                    font-weight: bold;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                ">
                    <?= esc($dominio) ?>
                </h1>
            </div>

            <!-- Descripcion -->
            <p style="
                font-size: 11pt;
                color: #555;
                max-width: 500px;
                margin: 20px auto 0;
                line-height: 1.6;
            ">
                El cuestionario para la evaluación del estrés es un instrumento diseñado para evaluar
                síntomas reveladores de la presencia de reacciones de estrés, distribuidos en cuatro
                categorías principales según el tipo de síntomas.
            </p>

            <!-- Categorías de síntomas -->
            <div style="margin-top: 30px; text-align: left; max-width: 450px; margin-left: auto; margin-right: auto;">
                <div style="font-size: 10pt; color: #9B59B6; font-weight: bold; margin-bottom: 10px;">
                    Categorías de síntomas evaluados:
                </div>
                <ul style="font-size: 9pt; color: #666; list-style: none; padding: 0; margin: 0;">
                    <li style="padding: 8px 0; border-bottom: 1px dotted #ddd;">
                        <span style="color: #9B59B6; margin-right: 8px;">&#9654;</span>
                        <strong>Síntomas fisiológicos</strong><br>
                        <span style="font-size: 8pt; color: #888; margin-left: 20px;">Dolores musculares, problemas gastrointestinales, alteraciones del sueño, etc.</span>
                    </li>
                    <li style="padding: 8px 0; border-bottom: 1px dotted #ddd;">
                        <span style="color: #9B59B6; margin-right: 8px;">&#9654;</span>
                        <strong>Síntomas de comportamiento social</strong><br>
                        <span style="font-size: 8pt; color: #888; margin-left: 20px;">Dificultad en relaciones, aislamiento, conflictos interpersonales, etc.</span>
                    </li>
                    <li style="padding: 8px 0; border-bottom: 1px dotted #ddd;">
                        <span style="color: #9B59B6; margin-right: 8px;">&#9654;</span>
                        <strong>Síntomas intelectuales y laborales</strong><br>
                        <span style="font-size: 8pt; color: #888; margin-left: 20px;">Dificultad de concentración, olvidos, bajo rendimiento, etc.</span>
                    </li>
                    <li style="padding: 8px 0; border-bottom: 1px dotted #ddd;">
                        <span style="color: #9B59B6; margin-right: 8px;">&#9654;</span>
                        <strong>Síntomas psicoemocionales</strong><br>
                        <span style="font-size: 8pt; color: #888; margin-left: 20px;">Ansiedad, depresión, irritabilidad, angustia, etc.</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <?= view('pdf/_partials/footer') ?>
</div>
