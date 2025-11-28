<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plan de Intervención - <?= esc($actionPlan['dimension_name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --riesgo-medio: #ffc107;
            --riesgo-alto: #fd7e14;
            --riesgo-muy-alto: #dc3545;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }

        .header-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
        }

        .risk-badge {
            display: inline-block;
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-weight: bold;
            font-size: 0.9rem;
            text-transform: uppercase;
        }

        .risk-medio { background-color: var(--riesgo-medio); color: #000; }
        .risk-alto { background-color: var(--riesgo-alto); color: #fff; }
        .risk-muy-alto { background-color: var(--riesgo-muy-alto); color: #fff; }

        .content-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .section-title {
            color: #667eea;
            font-weight: 700;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 3px solid #667eea;
        }

        .objective-card {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-radius: 0 10px 10px 0;
        }

        .objective-number {
            background: #667eea;
            color: white;
            min-width: 50px;
            height: 50px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.25rem;
            margin-right: 1rem;
        }

        .month-section {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .month-header {
            background: #667eea;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: bold;
            margin-bottom: 1rem;
        }

        .activity-item {
            background: #f8f9fa;
            padding: 1rem;
            margin-bottom: 0.75rem;
            border-radius: 8px;
            border-left: 3px solid #764ba2;
        }

        .objective-tag {
            display: inline-block;
            background: #764ba2;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            margin-top: 0.5rem;
        }

        .bibliography-item {
            background: #f8f9fa;
            padding: 1rem;
            margin-bottom: 0.5rem;
            border-left: 3px solid #28a745;
            border-radius: 0 8px 8px 0;
            font-size: 0.9rem;
        }

        .disclaimer {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 1rem;
            border-radius: 8px;
            font-size: 0.9rem;
            font-style: italic;
        }

        .print-button {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 1000;
        }

        @media print {
            .print-button, .no-print {
                display: none !important;
            }
            .content-card {
                box-shadow: none;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <div class="header-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-3">
                        <i class="bi bi-clipboard-check"></i>
                        Plan de Intervención
                    </h1>
                    <h3><?= esc($actionPlan['dimension_name']) ?></h3>
                    <p class="mb-0">
                        <strong>Dominio:</strong> <?= ucfirst(str_replace('_', ' ', $actionPlan['domain_code'] ?? 'N/A')) ?>
                        <span class="mx-2">|</span>
                        <strong>Cuestionario:</strong> <?= ucfirst($actionPlan['questionnaire_type']) ?>
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <?php if (isset($workerResults)): ?>
                        <div class="risk-badge risk-muy-alto">
                            RIESGO DETECTADO
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Introduction -->
        <div class="content-card">
            <h2 class="section-title">
                <i class="bi bi-info-circle"></i> Contexto y Justificación
            </h2>
            <div class="introduction-text">
                <?= nl2br(esc($actionPlan['introduction'])) ?>
            </div>
        </div>

        <!-- Objectives -->
        <div class="content-card">
            <h2 class="section-title">
                <i class="bi bi-bullseye"></i> Objetivos Estratégicos
            </h2>
            <p class="text-muted mb-4">
                A continuación se presentan los 5 objetivos clave para mejorar la dimensión
                <strong><?= esc($actionPlan['dimension_name']) ?></strong> en su organización:
            </p>

            <?php foreach ($actionPlan['objectives'] as $objective): ?>
                <div class="objective-card">
                    <div class="d-flex align-items-start">
                        <div class="objective-number">
                            <?= $objective['number'] ?>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-2">Objetivo <?= $objective['number'] ?></h5>
                            <p class="mb-0"><?= esc($objective['description']) ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- 6-Month Activity Plan -->
        <div class="content-card">
            <h2 class="section-title">
                <i class="bi bi-calendar-check"></i> Cronograma de Actividades - 6 Meses
            </h2>
            <p class="text-muted mb-4">
                Plan de acción detallado basado en los protocolos del Ministerio de Trabajo de Colombia.
            </p>

            <?php
            $meses = [
                'mes_1' => 'Mes 1',
                'mes_2' => 'Mes 2',
                'mes_3' => 'Mes 3',
                'mes_4' => 'Mes 4',
                'mes_5' => 'Mes 5',
                'mes_6' => 'Mes 6'
            ];

            foreach ($meses as $key => $label):
                if (isset($actionPlan['activities_6months'][$key])):
            ?>
                <div class="month-section">
                    <div class="month-header">
                        <i class="bi bi-calendar-event"></i> <?= $label ?>
                    </div>

                    <?php foreach ($actionPlan['activities_6months'][$key] as $activity): ?>
                        <div class="activity-item">
                            <div class="d-flex align-items-start">
                                <div class="me-3">
                                    <i class="bi bi-check-circle-fill text-success" style="font-size: 1.5rem;"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <strong>Actividad <?= $activity['number'] ?>:</strong>
                                    <?= esc($activity['description']) ?>
                                    <div class="objective-tag">
                                        <i class="bi bi-arrow-right-circle"></i>
                                        Relacionado con Objetivo <?= $activity['objetivo_relacionado'] ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php
                endif;
            endforeach;
            ?>
        </div>

        <!-- Cycloid Talent Offer -->
        <div class="content-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <h2 class="mb-3" style="color: white; border-color: white;">
                <i class="bi bi-star-fill"></i> Servicios de Consultoría Profesional
            </h2>
            <p class="lead">
                <strong>Cycloid Talent</strong> es una empresa comprometida con el bienestar emocional y la salud mental
                de los trabajadores.
            </p>
            <p>
                Ofrecemos servicios de consultoría profesional en la implementación de sistemas de vigilancia
                epidemiológica en riesgo psicosocial, con el objetivo de identificar y prevenir factores de riesgo
                que puedan afectar negativamente la salud emocional de los trabajadores y, por ende, el desempeño
                y productividad de la empresa.
            </p>
            <p>
                Nuestro equipo de expertos cuenta con una amplia experiencia en la gestión de riesgos psicosociales
                en el entorno laboral, lo que nos permite ofrecer soluciones personalizadas y adaptadas a las
                necesidades específicas de cada empresa.
            </p>
            <div class="text-center mt-4">
                <a href="https://cycloidtalent.com/riesgo-psicosocial" target="_blank" class="btn btn-light btn-lg no-print">
                    <i class="bi bi-question-circle-fill"></i> ¿Necesitas asesoría para tu programa de vigilancia epidemiológica en riesgo psicosocial?
                </a>
            </div>
        </div>

        <!-- Bibliography -->
        <div class="content-card">
            <h2 class="section-title">
                <i class="bi bi-book"></i> Bibliografía y Referencias
            </h2>
            <?php foreach ($actionPlan['bibliography'] as $index => $reference): ?>
                <div class="bibliography-item">
                    <i class="bi bi-journal-text"></i>
                    <?= esc($reference) ?>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Disclaimer -->
        <div class="disclaimer">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <strong>Nota importante:</strong> Este documento no exime, ni reemplaza las responsabilidades del
            empleador frente al Sistema de Gestión de la Seguridad y Salud en el Trabajo SG-SST.
        </div>

        <div class="text-center my-4 no-print">
            <button onclick="window.print()" class="btn btn-primary btn-lg">
                <i class="bi bi-printer"></i> Imprimir Plan de Acción
            </button>
            <a href="javascript:history.back()" class="btn btn-outline-secondary btn-lg">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <!-- Floating Print Button -->
    <button onclick="window.print()" class="btn btn-primary btn-lg rounded-circle print-button no-print"
            style="width: 60px; height: 60px;">
        <i class="bi bi-printer"></i>
    </button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
