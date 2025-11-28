<div class="pdf-page intro-page">
    <?= view('pdf/_partials/header', ['company' => $company, 'consultant' => $consultant]) ?>

    <div class="pdf-content" style="display: flex; align-items: center; justify-content: center; min-height: 180mm;">
        <div style="text-align: center; max-width: 600px;">
            <!-- Icono principal -->
            <div style="margin-bottom: 25px;">
                <svg width="100" height="100" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="50" cy="50" r="48" fill="#00A86B" stroke="#007A4D" stroke-width="2"/>
                    <path d="M50 25 L50 40 M50 60 L50 75 M25 50 L40 50 M60 50 L75 50" stroke="white" stroke-width="4" stroke-linecap="round"/>
                    <circle cx="50" cy="50" r="12" fill="white"/>
                    <circle cx="50" cy="50" r="6" fill="#00A86B"/>
                </svg>
            </div>

            <!-- Título principal -->
            <div style="
                background: linear-gradient(135deg, #00A86B 0%, #007A4D 100%);
                color: white;
                padding: 25px 50px;
                border-radius: 12px;
                margin-bottom: 25px;
                box-shadow: 0 4px 20px rgba(0, 168, 107, 0.3);
            ">
                <div style="font-size: 12pt; letter-spacing: 3px; margin-bottom: 8px; opacity: 0.9;">
                    SECCIÓN
                </div>
                <h1 style="font-size: 20pt; margin: 0; font-weight: bold;">
                    Factores de Riesgo Extralaboral
                </h1>
            </div>

            <!-- Descripción -->
            <p style="font-size: 11pt; color: #555; line-height: 1.7; margin-bottom: 25px; text-align: justify;">
                Esta sección presenta el análisis de los <strong>factores psicosociales extralaborales</strong>
                que pueden afectar la salud y el bienestar del trabajador. Estos factores evalúan las condiciones
                del entorno familiar, social y económico fuera del ambiente de trabajo.
            </p>

            <!-- Contenido de la sección -->
            <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; text-align: left;">
                <div style="font-size: 10pt; color: #00A86B; font-weight: bold; margin-bottom: 12px;">
                    Las 7 dimensiones extralaborales son:
                </div>
                <ul style="font-size: 9pt; color: #666; list-style: none; padding: 0; margin: 0;">
                    <li style="padding: 7px 0; border-bottom: 1px dotted #ddd; display: flex; align-items: center;">
                        <span style="background: #00A86B; color: white; width: 22px; height: 22px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-right: 12px; font-size: 8pt; font-weight: bold;">1</span>
                        <span><strong>Tiempo Fuera del Trabajo</strong></span>
                    </li>
                    <li style="padding: 7px 0; border-bottom: 1px dotted #ddd; display: flex; align-items: center;">
                        <span style="background: #00A86B; color: white; width: 22px; height: 22px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-right: 12px; font-size: 8pt; font-weight: bold;">2</span>
                        <span><strong>Relaciones Familiares</strong></span>
                    </li>
                    <li style="padding: 7px 0; border-bottom: 1px dotted #ddd; display: flex; align-items: center;">
                        <span style="background: #00A86B; color: white; width: 22px; height: 22px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-right: 12px; font-size: 8pt; font-weight: bold;">3</span>
                        <span><strong>Comunicación y Relaciones Interpersonales</strong></span>
                    </li>
                    <li style="padding: 7px 0; border-bottom: 1px dotted #ddd; display: flex; align-items: center;">
                        <span style="background: #00A86B; color: white; width: 22px; height: 22px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-right: 12px; font-size: 8pt; font-weight: bold;">4</span>
                        <span><strong>Situación Económica del Grupo Familiar</strong></span>
                    </li>
                    <li style="padding: 7px 0; border-bottom: 1px dotted #ddd; display: flex; align-items: center;">
                        <span style="background: #00A86B; color: white; width: 22px; height: 22px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-right: 12px; font-size: 8pt; font-weight: bold;">5</span>
                        <span><strong>Características de la Vivienda y de su Entorno</strong></span>
                    </li>
                    <li style="padding: 7px 0; border-bottom: 1px dotted #ddd; display: flex; align-items: center;">
                        <span style="background: #00A86B; color: white; width: 22px; height: 22px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-right: 12px; font-size: 8pt; font-weight: bold;">6</span>
                        <span><strong>Influencia del Entorno Extralaboral sobre el Trabajo</strong></span>
                    </li>
                    <li style="padding: 7px 0; display: flex; align-items: center;">
                        <span style="background: #00A86B; color: white; width: 22px; height: 22px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-right: 12px; font-size: 8pt; font-weight: bold;">7</span>
                        <span><strong>Desplazamiento Vivienda - Trabajo - Vivienda</strong></span>
                    </li>
                </ul>
            </div>

            <!-- Estructura -->
            <div style="margin-top: 20px; display: flex; gap: 15px;">
                <div style="flex: 1; background: #e8f5e9; padding: 12px; border-radius: 8px; text-align: center;">
                    <div style="font-size: 18pt; font-weight: bold; color: #00A86B;">7</div>
                    <div style="font-size: 8pt; color: #666;">Dimensiones<br>Forma A</div>
                </div>
                <div style="flex: 1; background: #fff3e0; padding: 12px; border-radius: 8px; text-align: center;">
                    <div style="font-size: 18pt; font-weight: bold; color: #FF9800;">7</div>
                    <div style="font-size: 8pt; color: #666;">Dimensiones<br>Forma B</div>
                </div>
                <div style="flex: 1; background: #f3e5f5; padding: 12px; border-radius: 8px; text-align: center;">
                    <div style="font-size: 18pt; font-weight: bold; color: #7B1FA2;">14</div>
                    <div style="font-size: 8pt; color: #666;">Páginas<br>Total</div>
                </div>
            </div>

            <!-- Nota -->
            <div style="margin-top: 15px; padding: 10px; background: #e8f5e9; border-left: 3px solid #00A86B; text-align: left; font-size: 8pt; color: #555;">
                <strong>Importante:</strong> El cuestionario extralaboral es el mismo para ambas formas (A y B),
                pero los baremos de interpretación pueden variar según el tipo de cargo.
            </div>
        </div>
    </div>

    <?= view('pdf/_partials/footer') ?>
</div>
