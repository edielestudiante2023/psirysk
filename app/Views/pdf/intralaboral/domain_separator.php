<div class="pdf-page domain-separator-page">
    <?= view('pdf/_partials/header', ['company' => $company, 'consultant' => $consultant]) ?>

    <div class="pdf-content" style="display: flex; align-items: center; justify-content: center; min-height: 180mm;">
        <div class="domain-separator-content" style="text-align: center;">
            <!-- Icono del dominio -->
            <div style="margin-bottom: 30px;">
                <svg width="80" height="80" viewBox="0 0 80 80" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="40" cy="40" r="38" fill="#0077B6" stroke="#005A8C" stroke-width="2"/>
                    <text x="40" y="50" text-anchor="middle" fill="white" font-size="40" font-weight="bold">D</text>
                </svg>
            </div>

            <!-- Titulo DOMINIO -->
            <div style="
                background: linear-gradient(135deg, #0077B6 0%, #005A8C 100%);
                color: white;
                padding: 20px 60px;
                border-radius: 10px;
                margin-bottom: 20px;
                box-shadow: 0 4px 15px rgba(0, 119, 182, 0.3);
            ">
                <div style="font-size: 14pt; letter-spacing: 3px; margin-bottom: 10px; opacity: 0.9;">
                    DOMINIO
                </div>
                <h1 style="
                    font-size: 22pt;
                    margin: 0;
                    font-weight: bold;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                ">
                    <?= esc($dominio) ?>
                </h1>
            </div>

            <!-- Descripcion del dominio -->
            <?php
            $descripcionesDominio = [
                'Liderazgo y Relaciones Sociales en el Trabajo' => 'Este dominio evalúa las características del liderazgo, las relaciones sociales, la retroalimentación del desempeño y la relación con los colaboradores.',
                'Control sobre el Trabajo' => 'Este dominio evalúa la claridad del rol, la capacitación, la participación y manejo del cambio, las oportunidades de desarrollo y el control sobre el trabajo.',
                'Demandas del Trabajo' => 'Este dominio evalúa las demandas ambientales, emocionales, cuantitativas, de carga mental, de la jornada y las exigencias de responsabilidad.',
                'Recompensas' => 'Este dominio evalúa las recompensas derivadas de la pertenencia a la organización y el reconocimiento y compensación.',
            ];
            $descripcion = $descripcionesDominio[$dominio] ?? '';
            ?>
            <?php if ($descripcion): ?>
            <p style="
                font-size: 11pt;
                color: #555;
                max-width: 500px;
                margin: 20px auto 0;
                line-height: 1.6;
            ">
                <?= esc($descripcion) ?>
            </p>
            <?php endif; ?>

            <!-- Dimensiones que incluye -->
            <?php
            $dimensionesPorDominio = [
                'Liderazgo y Relaciones Sociales en el Trabajo' => [
                    'Características del Liderazgo',
                    'Relaciones Sociales en el Trabajo',
                    'Retroalimentación del Desempeño',
                    'Relación con los Colaboradores (Solo Forma A)',
                ],
                'Control sobre el Trabajo' => [
                    'Claridad del Rol',
                    'Capacitación',
                    'Participación y Manejo del Cambio',
                    'Oportunidades para el Uso y Desarrollo de Habilidades',
                    'Control y Autonomía sobre el Trabajo',
                ],
                'Demandas del Trabajo' => [
                    'Demandas Ambientales y de Esfuerzo Físico',
                    'Demandas Emocionales',
                    'Demandas Cuantitativas',
                    'Influencia del Trabajo sobre el Entorno Extralaboral',
                    'Exigencias de Responsabilidad del Cargo (Solo Forma A)',
                    'Demandas de Carga Mental',
                    'Consistencia del Rol (Solo Forma A)',
                    'Demandas de la Jornada de Trabajo',
                ],
                'Recompensas' => [
                    'Recompensas Derivadas de la Pertenencia',
                    'Reconocimiento y Compensación',
                ],
            ];
            $dimensiones = $dimensionesPorDominio[$dominio] ?? [];
            ?>
            <?php if (!empty($dimensiones)): ?>
            <div style="margin-top: 30px; text-align: left; max-width: 450px; margin-left: auto; margin-right: auto;">
                <div style="font-size: 10pt; color: #0077B6; font-weight: bold; margin-bottom: 10px;">
                    Dimensiones incluidas:
                </div>
                <ul style="font-size: 9pt; color: #666; list-style: none; padding: 0; margin: 0;">
                    <?php foreach ($dimensiones as $dim): ?>
                    <li style="padding: 5px 0; border-bottom: 1px dotted #ddd;">
                        <span style="color: #0077B6; margin-right: 8px;">&#9654;</span>
                        <?= esc($dim) ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?= view('pdf/_partials/footer') ?>
</div>
