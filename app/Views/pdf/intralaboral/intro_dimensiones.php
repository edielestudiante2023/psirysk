<div class="pdf-page intro-page">
    <?= view('pdf/_partials/header', ['company' => $company, 'consultant' => $consultant]) ?>

    <div class="pdf-content" style="display: flex; align-items: center; justify-content: center; min-height: 180mm;">
        <div style="text-align: center; max-width: 620px;">
            <!-- Icono principal -->
            <div style="margin-bottom: 25px;">
                <svg width="100" height="100" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="50" cy="50" r="48" fill="#0077B6" stroke="#005A8C" stroke-width="2"/>
                    <rect x="22" y="25" width="18" height="18" rx="2" fill="white"/>
                    <rect x="42" y="25" width="18" height="18" rx="2" fill="white" opacity="0.9"/>
                    <rect x="62" y="25" width="18" height="18" rx="2" fill="white" opacity="0.8"/>
                    <rect x="22" y="45" width="18" height="18" rx="2" fill="white" opacity="0.9"/>
                    <rect x="42" y="45" width="18" height="18" rx="2" fill="white" opacity="0.8"/>
                    <rect x="62" y="45" width="18" height="18" rx="2" fill="white" opacity="0.7"/>
                    <rect x="22" y="65" width="18" height="18" rx="2" fill="white" opacity="0.8"/>
                    <rect x="42" y="65" width="18" height="18" rx="2" fill="white" opacity="0.7"/>
                    <rect x="62" y="65" width="18" height="18" rx="2" fill="white" opacity="0.6"/>
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
                    Dimensiones Intralaborales
                </h1>
            </div>

            <!-- Descripción -->
            <p style="font-size: 11pt; color: #555; line-height: 1.7; margin-bottom: 20px; text-align: justify;">
                Esta sección presenta el análisis detallado de cada <strong>dimensión</strong> del Cuestionario
                de Factores de Riesgo Psicosocial Intralaboral. Cada dimensión representa un aspecto específico
                del ambiente laboral que puede afectar la salud y el bienestar de los trabajadores.
            </p>

            <!-- Resumen de dimensiones por dominio -->
            <div style="background: #f8f9fa; padding: 15px; border-radius: 10px; text-align: left; margin-bottom: 15px;">
                <div style="font-size: 10pt; color: #0077B6; font-weight: bold; margin-bottom: 10px;">
                    Dimensiones por Dominio:
                </div>
                <div style="display: table; width: 100%; font-size: 8pt;">
                    <div style="display: table-row;">
                        <div style="display: table-cell; padding: 8px; border-bottom: 1px dotted #ddd; width: 60%;">
                            <strong>Liderazgo y Relaciones Sociales</strong>
                        </div>
                        <div style="display: table-cell; padding: 8px; border-bottom: 1px dotted #ddd; text-align: center;">
                            4 dim. (Forma A) / 3 dim. (Forma B)
                        </div>
                    </div>
                    <div style="display: table-row;">
                        <div style="display: table-cell; padding: 8px; border-bottom: 1px dotted #ddd;">
                            <strong>Control sobre el Trabajo</strong>
                        </div>
                        <div style="display: table-cell; padding: 8px; border-bottom: 1px dotted #ddd; text-align: center;">
                            5 dimensiones
                        </div>
                    </div>
                    <div style="display: table-row;">
                        <div style="display: table-cell; padding: 8px; border-bottom: 1px dotted #ddd;">
                            <strong>Demandas del Trabajo</strong>
                        </div>
                        <div style="display: table-cell; padding: 8px; border-bottom: 1px dotted #ddd; text-align: center;">
                            8 dim. (Forma A) / 6 dim. (Forma B)
                        </div>
                    </div>
                    <div style="display: table-row;">
                        <div style="display: table-cell; padding: 8px;">
                            <strong>Recompensas</strong>
                        </div>
                        <div style="display: table-cell; padding: 8px; text-align: center;">
                            2 dimensiones
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estructura -->
            <div style="display: flex; gap: 12px; margin-bottom: 15px;">
                <div style="flex: 1; background: #e3f2fd; padding: 12px; border-radius: 8px; text-align: center;">
                    <div style="font-size: 20pt; font-weight: bold; color: #0077B6;">19</div>
                    <div style="font-size: 8pt; color: #666;">Dimensiones<br>Forma A</div>
                </div>
                <div style="flex: 1; background: #fff3e0; padding: 12px; border-radius: 8px; text-align: center;">
                    <div style="font-size: 20pt; font-weight: bold; color: #FF9800;">16</div>
                    <div style="font-size: 8pt; color: #666;">Dimensiones<br>Forma B</div>
                </div>
                <div style="flex: 1; background: #f3e5f5; padding: 12px; border-radius: 8px; text-align: center;">
                    <div style="font-size: 20pt; font-weight: bold; color: #7B1FA2;">35</div>
                    <div style="font-size: 8pt; color: #666;">Páginas<br>Total</div>
                </div>
            </div>

            <!-- Nota -->
            <div style="padding: 10px; background: #e3f2fd; border-left: 3px solid #2196F3; text-align: left; font-size: 8pt; color: #555;">
                <strong>Nota:</strong> La Forma A tiene 3 dimensiones exclusivas: Relación con los Colaboradores,
                Exigencias de Responsabilidad del Cargo y Consistencia del Rol, aplicables solo a cargos
                con personal a cargo o funciones de jefatura.
            </div>
        </div>
    </div>

    <?= view('pdf/_partials/footer') ?>
</div>
