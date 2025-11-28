<div class="pdf-page domain-separator-page">
    <?= view('pdf/_partials/header', ['company' => $company, 'consultant' => $consultant]) ?>

    <div class="pdf-content" style="display: flex; align-items: center; justify-content: center; min-height: 180mm;">
        <div class="domain-separator-content" style="text-align: center;">
            <!-- Icono del dominio -->
            <div style="margin-bottom: 30px;">
                <svg width="80" height="80" viewBox="0 0 80 80" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="40" cy="40" r="38" fill="#00A86B" stroke="#007A4D" stroke-width="2"/>
                    <text x="40" y="50" text-anchor="middle" fill="white" font-size="40" font-weight="bold">E</text>
                </svg>
            </div>

            <!-- Titulo DOMINIO -->
            <div style="
                background: linear-gradient(135deg, #00A86B 0%, #007A4D 100%);
                color: white;
                padding: 20px 60px;
                border-radius: 10px;
                margin-bottom: 20px;
                box-shadow: 0 4px 15px rgba(0, 168, 107, 0.3);
            ">
                <div style="font-size: 14pt; letter-spacing: 3px; margin-bottom: 10px; opacity: 0.9;">
                    DOMINIO
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

            <!-- Descripcion del dominio -->
            <p style="
                font-size: 11pt;
                color: #555;
                max-width: 500px;
                margin: 20px auto 0;
                line-height: 1.6;
            ">
                Este dominio evalúa los factores psicosociales extralaborales que pueden afectar
                la salud y el bienestar del trabajador fuera de su ambiente laboral.
            </p>

            <!-- Dimensiones que incluye -->
            <div style="margin-top: 30px; text-align: left; max-width: 450px; margin-left: auto; margin-right: auto;">
                <div style="font-size: 10pt; color: #00A86B; font-weight: bold; margin-bottom: 10px;">
                    Dimensiones incluidas (7):
                </div>
                <ul style="font-size: 9pt; color: #666; list-style: none; padding: 0; margin: 0;">
                    <li style="padding: 5px 0; border-bottom: 1px dotted #ddd;">
                        <span style="color: #00A86B; margin-right: 8px;">&#9654;</span>
                        Tiempo Fuera del Trabajo
                    </li>
                    <li style="padding: 5px 0; border-bottom: 1px dotted #ddd;">
                        <span style="color: #00A86B; margin-right: 8px;">&#9654;</span>
                        Relaciones Familiares
                    </li>
                    <li style="padding: 5px 0; border-bottom: 1px dotted #ddd;">
                        <span style="color: #00A86B; margin-right: 8px;">&#9654;</span>
                        Comunicación y Relaciones Interpersonales
                    </li>
                    <li style="padding: 5px 0; border-bottom: 1px dotted #ddd;">
                        <span style="color: #00A86B; margin-right: 8px;">&#9654;</span>
                        Situación Económica del Grupo Familiar
                    </li>
                    <li style="padding: 5px 0; border-bottom: 1px dotted #ddd;">
                        <span style="color: #00A86B; margin-right: 8px;">&#9654;</span>
                        Características de la Vivienda y de su Entorno
                    </li>
                    <li style="padding: 5px 0; border-bottom: 1px dotted #ddd;">
                        <span style="color: #00A86B; margin-right: 8px;">&#9654;</span>
                        Influencia del Entorno Extralaboral sobre el Trabajo
                    </li>
                    <li style="padding: 5px 0; border-bottom: 1px dotted #ddd;">
                        <span style="color: #00A86B; margin-right: 8px;">&#9654;</span>
                        Desplazamiento Vivienda – Trabajo – Vivienda
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <?= view('pdf/_partials/footer') ?>
</div>
