<div class="pdf-page domain-separator-page">
    <?= view('pdf/_partials/header', ['company' => $company, 'consultant' => $consultant]) ?>

    <div class="pdf-content" style="display: flex; align-items: center; justify-content: center; min-height: 180mm;">
        <div class="domain-separator-content" style="text-align: center;">
            <!-- Icono de recomendaciones -->
            <div style="margin-bottom: 30px;">
                <svg width="80" height="80" viewBox="0 0 80 80" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="40" cy="40" r="38" fill="#FF6B35" stroke="#E55A2B" stroke-width="2"/>
                    <text x="40" y="50" text-anchor="middle" fill="white" font-size="35" font-weight="bold">&#10003;</text>
                </svg>
            </div>

            <!-- Titulo -->
            <div style="
                background: linear-gradient(135deg, #FF6B35 0%, #E55A2B 100%);
                color: white;
                padding: 20px 60px;
                border-radius: 10px;
                margin-bottom: 20px;
                box-shadow: 0 4px 15px rgba(255, 107, 53, 0.3);
            ">
                <div style="font-size: 14pt; letter-spacing: 3px; margin-bottom: 10px; opacity: 0.9;">
                    SECCIÓN
                </div>
                <h1 style="
                    font-size: 18pt;
                    margin: 0;
                    font-weight: bold;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                ">
                    <?= esc($titulo) ?>
                </h1>
            </div>

            <!-- Descripción -->
            <p style="
                font-size: 11pt;
                color: #555;
                max-width: 500px;
                margin: 20px auto 0;
                line-height: 1.6;
            ">
                Esta sección presenta las recomendaciones y planes de acción para las dimensiones
                que presentan niveles de riesgo medio, alto o muy alto, priorizando aquellas
                que requieren intervención inmediata.
            </p>

            <!-- Niveles de prioridad -->
            <div style="margin-top: 30px; text-align: left; max-width: 450px; margin-left: auto; margin-right: auto;">
                <div style="font-size: 10pt; color: #FF6B35; font-weight: bold; margin-bottom: 10px;">
                    Niveles de Prioridad:
                </div>
                <ul style="font-size: 9pt; color: #666; list-style: none; padding: 0; margin: 0;">
                    <li style="padding: 8px 0; border-bottom: 1px dotted #ddd; display: flex; align-items: center;">
                        <span style="background: #F44336; color: white; padding: 3px 10px; border-radius: 3px; margin-right: 15px; font-size: 8pt;">
                            MUY ALTO
                        </span>
                        Intervención inmediata requerida
                    </li>
                    <li style="padding: 8px 0; border-bottom: 1px dotted #ddd; display: flex; align-items: center;">
                        <span style="background: #FF9800; color: white; padding: 3px 10px; border-radius: 3px; margin-right: 15px; font-size: 8pt;">
                            ALTO
                        </span>
                        Intervención prioritaria
                    </li>
                    <li style="padding: 8px 0; border-bottom: 1px dotted #ddd; display: flex; align-items: center;">
                        <span style="background: #FFEB3B; color: #333; padding: 3px 10px; border-radius: 3px; margin-right: 15px; font-size: 8pt;">
                            MEDIO
                        </span>
                        Observación y seguimiento
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <?= view('pdf/_partials/footer') ?>
</div>
