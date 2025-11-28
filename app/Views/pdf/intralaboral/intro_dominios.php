<div class="pdf-page intro-page">
    <?= view('pdf/_partials/header', ['company' => $company, 'consultant' => $consultant]) ?>

    <div class="pdf-content" style="display: flex; align-items: center; justify-content: center; min-height: 180mm;">
        <div style="text-align: center; max-width: 600px;">
            <!-- Icono principal -->
            <div style="margin-bottom: 25px;">
                <svg width="100" height="100" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="50" cy="50" r="48" fill="#0077B6" stroke="#005A8C" stroke-width="2"/>
                    <rect x="25" y="30" width="50" height="10" rx="2" fill="white"/>
                    <rect x="25" y="45" width="50" height="10" rx="2" fill="white" opacity="0.8"/>
                    <rect x="25" y="60" width="50" height="10" rx="2" fill="white" opacity="0.6"/>
                </svg>
            </div>

            <!-- Título principal -->
            <div style="
                background: linear-gradient(135deg, #0077B6 0%, #005A8C 100%);
                color: white;
                padding: 25px 50px;
                border-radius: 12px;
                margin-bottom: 25px;
                box-shadow: 0 4px 20px rgba(0, 119, 182, 0.3);
            ">
                <div style="font-size: 12pt; letter-spacing: 3px; margin-bottom: 8px; opacity: 0.9;">
                    SECCIÓN
                </div>
                <h1 style="font-size: 20pt; margin: 0; font-weight: bold;">
                    Dominios Intralaborales
                </h1>
            </div>

            <!-- Descripción -->
            <p style="font-size: 11pt; color: #555; line-height: 1.7; margin-bottom: 25px; text-align: justify;">
                Esta sección presenta el análisis por <strong>dominios</strong> del Cuestionario de Factores de
                Riesgo Psicosocial Intralaboral. Los dominios agrupan las dimensiones relacionadas conceptualmente,
                permitiendo identificar áreas críticas de intervención en el ambiente de trabajo.
            </p>

            <!-- Contenido de la sección -->
            <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; text-align: left;">
                <div style="font-size: 10pt; color: #0077B6; font-weight: bold; margin-bottom: 12px;">
                    Los 4 dominios evaluados son:
                </div>
                <ul style="font-size: 9pt; color: #666; list-style: none; padding: 0; margin: 0;">
                    <li style="padding: 10px 0; border-bottom: 1px dotted #ddd;">
                        <div style="display: flex; align-items: flex-start;">
                            <span style="background: #0077B6; color: white; width: 22px; height: 22px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-right: 12px; font-size: 8pt; font-weight: bold; flex-shrink: 0;">1</span>
                            <div>
                                <strong>Liderazgo y Relaciones Sociales en el Trabajo</strong>
                                <div style="font-size: 8pt; color: #888; margin-top: 3px;">Características del liderazgo, relaciones sociales, retroalimentación y relación con colaboradores</div>
                            </div>
                        </div>
                    </li>
                    <li style="padding: 10px 0; border-bottom: 1px dotted #ddd;">
                        <div style="display: flex; align-items: flex-start;">
                            <span style="background: #0077B6; color: white; width: 22px; height: 22px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-right: 12px; font-size: 8pt; font-weight: bold; flex-shrink: 0;">2</span>
                            <div>
                                <strong>Control sobre el Trabajo</strong>
                                <div style="font-size: 8pt; color: #888; margin-top: 3px;">Claridad del rol, capacitación, participación, oportunidades de desarrollo y autonomía</div>
                            </div>
                        </div>
                    </li>
                    <li style="padding: 10px 0; border-bottom: 1px dotted #ddd;">
                        <div style="display: flex; align-items: flex-start;">
                            <span style="background: #0077B6; color: white; width: 22px; height: 22px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-right: 12px; font-size: 8pt; font-weight: bold; flex-shrink: 0;">3</span>
                            <div>
                                <strong>Demandas del Trabajo</strong>
                                <div style="font-size: 8pt; color: #888; margin-top: 3px;">Demandas ambientales, emocionales, cuantitativas, de carga mental, jornada y responsabilidad</div>
                            </div>
                        </div>
                    </li>
                    <li style="padding: 10px 0;">
                        <div style="display: flex; align-items: flex-start;">
                            <span style="background: #0077B6; color: white; width: 22px; height: 22px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-right: 12px; font-size: 8pt; font-weight: bold; flex-shrink: 0;">4</span>
                            <div>
                                <strong>Recompensas</strong>
                                <div style="font-size: 8pt; color: #888; margin-top: 3px;">Recompensas por pertenencia a la organización, reconocimiento y compensación</div>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Estructura -->
            <div style="margin-top: 20px; display: flex; gap: 15px;">
                <div style="flex: 1; background: #e3f2fd; padding: 12px; border-radius: 8px; text-align: center;">
                    <div style="font-size: 18pt; font-weight: bold; color: #0077B6;">4</div>
                    <div style="font-size: 8pt; color: #666;">Dominios<br>Forma A</div>
                </div>
                <div style="flex: 1; background: #fff3e0; padding: 12px; border-radius: 8px; text-align: center;">
                    <div style="font-size: 18pt; font-weight: bold; color: #FF9800;">4</div>
                    <div style="font-size: 8pt; color: #666;">Dominios<br>Forma B</div>
                </div>
                <div style="flex: 1; background: #f3e5f5; padding: 12px; border-radius: 8px; text-align: center;">
                    <div style="font-size: 18pt; font-weight: bold; color: #7B1FA2;">8</div>
                    <div style="font-size: 8pt; color: #666;">Páginas<br>Total</div>
                </div>
            </div>
        </div>
    </div>

    <?= view('pdf/_partials/footer') ?>
</div>
