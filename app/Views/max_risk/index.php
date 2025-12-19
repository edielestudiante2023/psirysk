<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?> - PsyRisk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #f5f7fa 0%, #e4e8ec 100%); min-height: 100vh; }
        .header-banner {
            background: linear-gradient(135deg, #1a365d 0%, #2c5282 50%, #3182ce 100%);
            color: white;
            padding: 1.5rem 0;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .stats-card {
            border-radius: 10px;
            padding: 1rem;
            text-align: center;
            color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .stats-card.muy-alto { background: linear-gradient(135deg, #c53030, #e53e3e); }
        .stats-card.alto { background: linear-gradient(135deg, #dd6b20, #ed8936); }
        .stats-card.medio { background: linear-gradient(135deg, #d69e2e, #ecc94b); color: #1a202c; }
        .stats-card.bajo { background: linear-gradient(135deg, #38a169, #48bb78); }
        .stats-card h3 { font-size: 2rem; margin: 0; }
        .stats-card small { opacity: 0.9; }

        .section-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
        }
        .section-header {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            font-weight: 600;
        }
        .section-header.intralaboral { background: #ebf8ff; border-left: 4px solid #3182ce; }
        .section-header.extralaboral { background: #f0fff4; border-left: 4px solid #38a169; }
        .section-header.estres { background: #faf5ff; border-left: 4px solid #805ad5; }

        .risk-table { width: 100%; }
        .risk-table th {
            background: #f8f9fa;
            font-weight: 600;
            font-size: 0.85rem;
            padding: 0.75rem;
        }
        .risk-table td {
            padding: 0.75rem;
            vertical-align: middle;
            border-bottom: 1px solid #e2e8f0;
        }
        .risk-badge {
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .risk-badge.muy-alto { background: #fed7d7; color: #c53030; }
        .risk-badge.alto { background: #feebc8; color: #c05621; }
        .risk-badge.medio { background: #fefcbf; color: #975a16; }
        .risk-badge.bajo { background: #c6f6d5; color: #276749; }
        .risk-badge.sin-riesgo { background: #e2e8f0; color: #4a5568; }

        .element-type {
            font-size: 0.7rem;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            text-transform: uppercase;
            font-weight: 600;
        }
        .element-type.total { background: #1a365d; color: white; }
        .element-type.domain { background: #2c5282; color: white; }
        .element-type.dimension { background: #4299e1; color: white; }

        .action-btn {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            font-size: 0.85rem;
        }
        .action-btn.has-content { position: relative; }
        .action-btn.has-content::after {
            content: '';
            position: absolute;
            top: -2px;
            right: -2px;
            width: 8px;
            height: 8px;
            background: #38a169;
            border-radius: 50%;
        }

        .ai-status { font-size: 0.75rem; }
        .ai-status.generated { color: #38a169; }
        .ai-status.pending { color: #718096; }

        .form-indicator {
            font-size: 0.7rem;
            padding: 0.15rem 0.4rem;
            border-radius: 3px;
            font-weight: 600;
        }
        .form-indicator.a { background: #bee3f8; color: #2b6cb0; }
        .form-indicator.b { background: #fbd38d; color: #c05621; }

        .modal-header.context { background: linear-gradient(135deg, #667eea, #764ba2); color: white; }
        .modal-header.comment { background: linear-gradient(135deg, #38a169, #48bb78); color: white; }
        .modal-header.ai { background: linear-gradient(135deg, #ed8936, #dd6b20); color: white; }

        .ai-content {
            background: #f7fafc;
            border-radius: 10px;
            padding: 1rem;
            max-height: 400px;
            overflow-y: auto;
            white-space: pre-wrap;
            font-size: 0.9rem;
            line-height: 1.6;
        }

        .loading-spinner {
            display: none;
            text-align: center;
            padding: 2rem;
        }
        .loading-spinner.active { display: block; }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header-banner">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1"><i class="fas fa-chart-line me-2"></i><?= esc($title) ?></h4>
                    <p class="mb-0 opacity-75">
                        <?= esc($company['name']) ?> - Servicio #<?= $batteryService['id'] ?>
                    </p>
                </div>
                <div>
                    <a href="/battery-services/<?= $batteryService['id'] ?>" class="btn btn-outline-light btn-sm me-2">
                        <i class="fas fa-arrow-left me-1"></i> Volver
                    </a>
                    <a href="/max-risk/<?= $batteryService['id'] ?>/recalculate" class="btn btn-warning btn-sm">
                        <i class="fas fa-sync-alt me-1"></i> Recalcular
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container pb-4">
        <!-- Mensajes flash -->
        <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Estadísticas -->
        <div class="row mb-4">
            <div class="col-md-3 col-6 mb-3">
                <div class="stats-card muy-alto">
                    <h3><?= $stats['muy_alto'] ?></h3>
                    <small>Muy Alto</small>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="stats-card alto">
                    <h3><?= $stats['alto'] ?></h3>
                    <small>Alto</small>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="stats-card medio">
                    <h3><?= $stats['medio'] ?></h3>
                    <small>Medio</small>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="stats-card bajo">
                    <h3><?= $stats['bajo'] + $stats['sin_riesgo'] ?></h3>
                    <small>Bajo / Sin Riesgo</small>
                </div>
            </div>
        </div>

        <!-- Información -->
        <div class="alert alert-info mb-4">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Máximo Riesgo:</strong> Se muestra el peor resultado entre Forma A y Forma B para cada elemento.
            Los elementos en riesgo <strong>Alto</strong> y <strong>Muy Alto</strong> requieren intervención prioritaria.
        </div>

        <!-- INTRALABORAL -->
        <div class="section-card">
            <div class="section-header intralaboral">
                <i class="fas fa-building me-2"></i>
                Factores Intralaborales
                <span class="badge bg-primary ms-auto"><?= count($grouped['intralaboral']['totals']) + count($grouped['intralaboral']['domains']) + count($grouped['intralaboral']['dimensions']) ?> elementos</span>
            </div>

            <div class="table-responsive">
                <table class="risk-table">
                    <thead>
                        <tr>
                            <th style="width: 5%;">Tipo</th>
                            <th style="width: 30%;">Elemento</th>
                            <th style="width: 10%;">Puntaje</th>
                            <th style="width: 10%;">Forma</th>
                            <th style="width: 12%;">Nivel</th>
                            <th style="width: 13%;">Estado IA</th>
                            <th style="width: 20%;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $intraElements = array_merge(
                            $grouped['intralaboral']['totals'],
                            $grouped['intralaboral']['domains'],
                            $grouped['intralaboral']['dimensions']
                        );
                        foreach ($intraElements as $element):
                            $riskClass = str_replace('_', '-', str_replace('riesgo_', '', $element['worst_risk_level']));
                        ?>
                        <tr data-id="<?= $element['id'] ?>">
                            <td>
                                <span class="element-type <?= $element['element_type'] ?>"><?= ucfirst($element['element_type']) ?></span>
                            </td>
                            <td>
                                <strong><?= esc($element['element_name']) ?></strong>
                                <?php if ($element['has_both_forms']): ?>
                                <br><small class="text-muted">
                                    A: <?= $element['form_a_score'] ?> (n=<?= $element['form_a_count'] ?>) |
                                    B: <?= $element['form_b_score'] ?> (n=<?= $element['form_b_count'] ?>)
                                </small>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= $element['worst_score'] ?></strong></td>
                            <td><span class="form-indicator <?= strtolower($element['worst_form']) ?>"><?= $element['worst_form'] ?></span></td>
                            <td><span class="risk-badge <?= $riskClass ?>"><?= ucfirst(str_replace('_', ' ', $element['worst_risk_level'])) ?></span></td>
                            <td>
                                <?php if (!empty($element['ai_analysis'])): ?>
                                <span class="ai-status generated"><i class="fas fa-check-circle me-1"></i>Generado</span>
                                <?php else: ?>
                                <span class="ai-status pending"><i class="fas fa-clock me-1"></i>Pendiente</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-outline-primary action-btn <?= !empty($element['consultant_prompt']) ? 'has-content' : '' ?>"
                                        onclick="openContextModal(<?= $element['id'] ?>)" title="Contexto IA">
                                    <i class="fas fa-comment-dots"></i>
                                </button>
                                <button class="btn btn-outline-success action-btn <?= !empty($element['consultant_comment']) ? 'has-content' : '' ?>"
                                        onclick="openCommentModal(<?= $element['id'] ?>)" title="Comentario">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <button class="btn btn-outline-warning action-btn"
                                        onclick="generateAi(<?= $element['id'] ?>)" title="Generar IA">
                                    <i class="fas fa-robot"></i>
                                </button>
                                <?php if (!empty($element['ai_analysis'])): ?>
                                <button class="btn btn-outline-info action-btn"
                                        onclick="viewAiAnalysis(<?= $element['id'] ?>)" title="Ver análisis">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- EXTRALABORAL -->
        <div class="section-card">
            <div class="section-header extralaboral">
                <i class="fas fa-home me-2"></i>
                Factores Extralaborales
                <span class="badge bg-success ms-auto"><?= count($grouped['extralaboral']['totals']) + count($grouped['extralaboral']['dimensions']) ?> elementos</span>
            </div>

            <div class="table-responsive">
                <table class="risk-table">
                    <thead>
                        <tr>
                            <th style="width: 5%;">Tipo</th>
                            <th style="width: 30%;">Elemento</th>
                            <th style="width: 10%;">Puntaje</th>
                            <th style="width: 10%;">Forma</th>
                            <th style="width: 12%;">Nivel</th>
                            <th style="width: 13%;">Estado IA</th>
                            <th style="width: 20%;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $extraElements = array_merge(
                            $grouped['extralaboral']['totals'],
                            $grouped['extralaboral']['dimensions']
                        );
                        foreach ($extraElements as $element):
                            $riskClass = str_replace('_', '-', str_replace('riesgo_', '', $element['worst_risk_level']));
                        ?>
                        <tr data-id="<?= $element['id'] ?>">
                            <td>
                                <span class="element-type <?= $element['element_type'] ?>"><?= ucfirst($element['element_type']) ?></span>
                            </td>
                            <td><strong><?= esc($element['element_name']) ?></strong></td>
                            <td><strong><?= $element['worst_score'] ?></strong></td>
                            <td><span class="form-indicator <?= strtolower($element['worst_form']) ?>"><?= $element['worst_form'] ?></span></td>
                            <td><span class="risk-badge <?= $riskClass ?>"><?= ucfirst(str_replace('_', ' ', $element['worst_risk_level'])) ?></span></td>
                            <td>
                                <?php if (!empty($element['ai_analysis'])): ?>
                                <span class="ai-status generated"><i class="fas fa-check-circle me-1"></i>Generado</span>
                                <?php else: ?>
                                <span class="ai-status pending"><i class="fas fa-clock me-1"></i>Pendiente</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-outline-primary action-btn <?= !empty($element['consultant_prompt']) ? 'has-content' : '' ?>"
                                        onclick="openContextModal(<?= $element['id'] ?>)" title="Contexto IA">
                                    <i class="fas fa-comment-dots"></i>
                                </button>
                                <button class="btn btn-outline-success action-btn <?= !empty($element['consultant_comment']) ? 'has-content' : '' ?>"
                                        onclick="openCommentModal(<?= $element['id'] ?>)" title="Comentario">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <button class="btn btn-outline-warning action-btn"
                                        onclick="generateAi(<?= $element['id'] ?>)" title="Generar IA">
                                    <i class="fas fa-robot"></i>
                                </button>
                                <?php if (!empty($element['ai_analysis'])): ?>
                                <button class="btn btn-outline-info action-btn"
                                        onclick="viewAiAnalysis(<?= $element['id'] ?>)" title="Ver análisis">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ESTRÉS -->
        <div class="section-card">
            <div class="section-header estres">
                <i class="fas fa-brain me-2"></i>
                Estrés
                <span class="badge bg-purple ms-auto"><?= count($grouped['estres']['totals']) ?> elementos</span>
            </div>

            <div class="table-responsive">
                <table class="risk-table">
                    <thead>
                        <tr>
                            <th style="width: 5%;">Tipo</th>
                            <th style="width: 30%;">Elemento</th>
                            <th style="width: 10%;">Puntaje</th>
                            <th style="width: 10%;">Forma</th>
                            <th style="width: 12%;">Nivel</th>
                            <th style="width: 13%;">Estado IA</th>
                            <th style="width: 20%;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($grouped['estres']['totals'] as $element):
                            $riskClass = str_replace('_', '-', str_replace('riesgo_', '', $element['worst_risk_level']));
                        ?>
                        <tr data-id="<?= $element['id'] ?>">
                            <td>
                                <span class="element-type <?= $element['element_type'] ?>"><?= ucfirst($element['element_type']) ?></span>
                            </td>
                            <td>
                                <strong><?= esc($element['element_name']) ?></strong>
                                <?php if ($element['has_both_forms']): ?>
                                <br><small class="text-muted">
                                    A: <?= $element['form_a_score'] ?> (n=<?= $element['form_a_count'] ?>) |
                                    B: <?= $element['form_b_score'] ?> (n=<?= $element['form_b_count'] ?>)
                                </small>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= $element['worst_score'] ?></strong></td>
                            <td><span class="form-indicator <?= strtolower($element['worst_form']) ?>"><?= $element['worst_form'] ?></span></td>
                            <td><span class="risk-badge <?= $riskClass ?>"><?= ucfirst(str_replace('_', ' ', $element['worst_risk_level'])) ?></span></td>
                            <td>
                                <?php if (!empty($element['ai_analysis'])): ?>
                                <span class="ai-status generated"><i class="fas fa-check-circle me-1"></i>Generado</span>
                                <?php else: ?>
                                <span class="ai-status pending"><i class="fas fa-clock me-1"></i>Pendiente</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-outline-primary action-btn <?= !empty($element['consultant_prompt']) ? 'has-content' : '' ?>"
                                        onclick="openContextModal(<?= $element['id'] ?>)" title="Contexto IA">
                                    <i class="fas fa-comment-dots"></i>
                                </button>
                                <button class="btn btn-outline-success action-btn <?= !empty($element['consultant_comment']) ? 'has-content' : '' ?>"
                                        onclick="openCommentModal(<?= $element['id'] ?>)" title="Comentario">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <button class="btn btn-outline-warning action-btn"
                                        onclick="generateAi(<?= $element['id'] ?>)" title="Generar IA">
                                    <i class="fas fa-robot"></i>
                                </button>
                                <?php if (!empty($element['ai_analysis'])): ?>
                                <button class="btn btn-outline-info action-btn"
                                        onclick="viewAiAnalysis(<?= $element['id'] ?>)" title="Ver análisis">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal: Contexto IA -->
    <div class="modal fade" id="contextModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header context">
                    <h5 class="modal-title"><i class="fas fa-comment-dots me-2"></i>Contexto Complementario para IA</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">
                        Este texto se inyecta en el prompt de IA para dar contexto adicional.
                        <strong>No aparece en el informe final.</strong>
                    </p>
                    <div class="mb-3">
                        <label class="form-label">Elemento:</label>
                        <div id="contextElementName" class="fw-bold"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Instrucciones para IA:</label>
                        <textarea id="contextPrompt" class="form-control" rows="5"
                            placeholder="Ej: Enfoca tu respuesta a una población en mayor medida madres cabeza de familia..."></textarea>
                    </div>
                    <div class="small text-muted">
                        <strong>Ejemplos:</strong><br>
                        - "Es industria textil con ruido de máquinas planas"<br>
                        - "El área administrativa trabaja en turnos nocturnos"<br>
                        - "Hay alto índice de rotación en el último año"
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="saveContext()">
                        <i class="fas fa-save me-1"></i>Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Comentario del Consultor -->
    <div class="modal fade" id="commentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header comment">
                    <h5 class="modal-title"><i class="fas fa-pen me-2"></i>Comentario del Consultor</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">
                        Este comentario <strong>SÍ aparece en el informe final</strong> junto al análisis de IA.
                    </p>
                    <div class="mb-3">
                        <label class="form-label">Elemento:</label>
                        <div id="commentElementName" class="fw-bold"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tu comentario profesional:</label>
                        <textarea id="consultantComment" class="form-control" rows="5"
                            placeholder="Escribe observaciones adicionales que complementen el análisis..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" onclick="saveComment()">
                        <i class="fas fa-save me-1"></i>Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Ver Análisis IA -->
    <div class="modal fade" id="viewAiModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header ai">
                    <h5 class="modal-title"><i class="fas fa-robot me-2"></i>Análisis Generado por IA</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Elemento:</label>
                        <div id="aiElementName" class="fw-bold"></div>
                    </div>
                    <div class="loading-spinner" id="aiLoadingSpinner">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2">Cargando análisis...</p>
                    </div>
                    <div id="aiAnalysisContent" class="ai-content"></div>
                    <div class="mt-3 small text-muted" id="aiMeta"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-warning" onclick="regenerateCurrentAi()">
                        <i class="fas fa-sync-alt me-1"></i>Regenerar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Generando IA -->
    <div class="modal fade" id="generatingModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <div class="spinner-border text-warning mb-3" role="status" style="width: 3rem; height: 3rem;"></div>
                    <h5>Generando análisis con IA...</h5>
                    <p class="text-muted small mb-0">Esto puede tomar unos segundos</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Cache de datos de elementos
        const elementsCache = <?= json_encode(array_column($allResults, null, 'id')) ?>;
        let currentElementId = null;

        // Modales
        const contextModal = new bootstrap.Modal(document.getElementById('contextModal'));
        const commentModal = new bootstrap.Modal(document.getElementById('commentModal'));
        const viewAiModal = new bootstrap.Modal(document.getElementById('viewAiModal'));
        const generatingModal = new bootstrap.Modal(document.getElementById('generatingModal'));

        function openContextModal(id) {
            currentElementId = id;
            const element = elementsCache[id];
            document.getElementById('contextElementName').textContent = element.element_name;
            document.getElementById('contextPrompt').value = element.consultant_prompt || '';
            contextModal.show();
        }

        function openCommentModal(id) {
            currentElementId = id;
            const element = elementsCache[id];
            document.getElementById('commentElementName').textContent = element.element_name;
            document.getElementById('consultantComment').value = element.consultant_comment || '';
            commentModal.show();
        }

        function viewAiAnalysis(id) {
            currentElementId = id;
            const element = elementsCache[id];
            document.getElementById('aiElementName').textContent = element.element_name;
            document.getElementById('aiAnalysisContent').innerHTML = element.ai_analysis || 'Sin análisis generado';
            document.getElementById('aiMeta').innerHTML = element.ai_generated_at
                ? `Generado: ${element.ai_generated_at} | Modelo: ${element.ai_model_version || 'N/A'}`
                : '';
            viewAiModal.show();
        }

        async function saveContext() {
            const prompt = document.getElementById('contextPrompt').value;

            try {
                const response = await fetch('/max-risk/save-prompt', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: `id=${currentElementId}&prompt=${encodeURIComponent(prompt)}`
                });

                const data = await response.json();
                if (data.success) {
                    elementsCache[currentElementId].consultant_prompt = prompt;
                    updateButtonIndicator(currentElementId, 'context', !!prompt);
                    contextModal.hide();
                    showToast('Contexto guardado', 'success');
                } else {
                    showToast(data.message || 'Error al guardar', 'error');
                }
            } catch (error) {
                showToast('Error de conexión', 'error');
            }
        }

        async function saveComment() {
            const comment = document.getElementById('consultantComment').value;

            try {
                const response = await fetch('/max-risk/save-comment', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: `id=${currentElementId}&comment=${encodeURIComponent(comment)}`
                });

                const data = await response.json();
                if (data.success) {
                    elementsCache[currentElementId].consultant_comment = comment;
                    updateButtonIndicator(currentElementId, 'comment', !!comment);
                    commentModal.hide();
                    showToast('Comentario guardado', 'success');
                } else {
                    showToast(data.message || 'Error al guardar', 'error');
                }
            } catch (error) {
                showToast('Error de conexión', 'error');
            }
        }

        async function generateAi(id) {
            currentElementId = id;
            generatingModal.show();

            try {
                const response = await fetch('/max-risk/generate-ai', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: `id=${id}`
                });

                const data = await response.json();
                generatingModal.hide();

                if (data.success) {
                    elementsCache[id].ai_analysis = data.analysis;
                    elementsCache[id].ai_recommendations = data.recommendations;
                    elementsCache[id].ai_generated_at = new Date().toISOString();
                    updateAiStatus(id, true);
                    showToast('Análisis generado correctamente', 'success');

                    // Mostrar el análisis
                    viewAiAnalysis(id);
                } else {
                    showToast(data.message || 'Error al generar análisis', 'error');
                }
            } catch (error) {
                generatingModal.hide();
                showToast('Error de conexión', 'error');
            }
        }

        function regenerateCurrentAi() {
            viewAiModal.hide();
            generateAi(currentElementId);
        }

        function updateButtonIndicator(id, type, hasContent) {
            const row = document.querySelector(`tr[data-id="${id}"]`);
            if (!row) return;

            const btnIndex = type === 'context' ? 0 : 1;
            const btn = row.querySelectorAll('.action-btn')[btnIndex];
            if (btn) {
                btn.classList.toggle('has-content', hasContent);
            }
        }

        function updateAiStatus(id, generated) {
            const row = document.querySelector(`tr[data-id="${id}"]`);
            if (!row) return;

            const statusCell = row.querySelector('.ai-status');
            if (statusCell) {
                if (generated) {
                    statusCell.className = 'ai-status generated';
                    statusCell.innerHTML = '<i class="fas fa-check-circle me-1"></i>Generado';
                } else {
                    statusCell.className = 'ai-status pending';
                    statusCell.innerHTML = '<i class="fas fa-clock me-1"></i>Pendiente';
                }
            }

            // Agregar botón de ver si no existe
            const actionsCell = row.querySelector('td:last-child');
            if (generated && !actionsCell.querySelector('.btn-outline-info')) {
                const viewBtn = document.createElement('button');
                viewBtn.className = 'btn btn-outline-info action-btn';
                viewBtn.setAttribute('onclick', `viewAiAnalysis(${id})`);
                viewBtn.setAttribute('title', 'Ver análisis');
                viewBtn.innerHTML = '<i class="fas fa-eye"></i>';
                actionsCell.appendChild(viewBtn);
            }
        }

        function showToast(message, type) {
            // Simple toast usando alert por ahora (se puede mejorar con librería)
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
            alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 250px;';
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(alertDiv);

            setTimeout(() => alertDiv.remove(), 3000);
        }
    </script>
</body>
</html>
