<div class="pdf-page intro-page">
    <?= view('pdf/_partials/header', ['company' => $company, 'consultant' => $consultant]) ?>

    <div class="pdf-content" style="display: flex; align-items: center; justify-content: center; min-height: 180mm;">
        <div style="text-align: center; max-width: 600px;">
            <!-- Icono principal -->
            <div style="margin-bottom: 25px;">
                <svg width="100" height="100" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="50" cy="50" r="48" fill="#0077B6" stroke="#005A8C" stroke-width="2"/>
                    <path d="M30 55 L45 70 L70 35" stroke="white" stroke-width="6" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
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
                    Resultados Totales Intralaborales
                </h1>
            </div>

            <!-- Descripción -->
            <p style="font-size: 11pt; color: #555; line-height: 1.7; margin-bottom: 25px; text-align: justify;">
                Esta sección presenta los <strong>puntajes totales</strong> del Cuestionario de Factores de Riesgo
                Psicosocial Intralaboral, consolidando los resultados de todas las dimensiones y dominios evaluados.
                Los resultados se presentan diferenciados por tipo de cuestionario aplicado.
            </p>

            <!-- Contenido de la sección -->
            <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; text-align: left;">
                <div style="font-size: 10pt; color: #0077B6; font-weight: bold; margin-bottom: 12px;">
                    En esta sección encontrará:
                </div>
                <ul style="font-size: 9pt; color: #666; list-style: none; padding: 0; margin: 0;">
                    <li style="padding: 8px 0; border-bottom: 1px dotted #ddd; display: flex; align-items: center;">
                        <span style="background: #0077B6; color: white; width: 22px; height: 22px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-right: 12px; font-size: 8pt; font-weight: bold;">1</span>
                        <span><strong>Total Forma A</strong> - Resultados para Jefes, Profesionales y Técnicos</span>
                    </li>
                    <li style="padding: 8px 0; border-bottom: 1px dotted #ddd; display: flex; align-items: center;">
                        <span style="background: #0077B6; color: white; width: 22px; height: 22px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-right: 12px; font-size: 8pt; font-weight: bold;">2</span>
                        <span><strong>Total Forma B</strong> - Resultados para Auxiliares y Operarios</span>
                    </li>
                    <li style="padding: 8px 0; border-bottom: 1px dotted #ddd; display: flex; align-items: center;">
                        <span style="background: #0077B6; color: white; width: 22px; height: 22px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-right: 12px; font-size: 8pt; font-weight: bold;">3</span>
                        <span><strong>Resumen General Intralaboral</strong> - Consolidado de ambas formas</span>
                    </li>
                    <li style="padding: 8px 0; display: flex; align-items: center;">
                        <span style="background: #6a1b9a; color: white; width: 22px; height: 22px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-right: 12px; font-size: 8pt; font-weight: bold;">4</span>
                        <span><strong>Puntaje Total General Psicosocial</strong> - Intralaboral + Extralaboral (Tabla 34)</span>
                    </li>
                </ul>
            </div>

            <!-- Nota metodológica -->
            <div style="margin-top: 20px; padding: 12px; background: #e3f2fd; border-left: 3px solid #2196F3; text-align: left; font-size: 8pt; color: #555;">
                <strong>Nota metodológica:</strong> Los niveles de riesgo se determinan según los baremos
                establecidos en la Resolución 2404 de 2019 del Ministerio del Trabajo de Colombia.
            </div>
        </div>
    </div>

    <?= view('pdf/_partials/footer') ?>
</div>
