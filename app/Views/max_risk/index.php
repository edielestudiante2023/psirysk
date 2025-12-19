<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?> - PsyRisk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
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

        .conclusion-box {
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            border: 2px solid #3182ce;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 1.5rem;
        }
        .conclusion-box h4 {
            color: #2c5282;
            margin-bottom: 1rem;
        }
        .conclusion-text {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            min-height: 200px;
            font-size: 1rem;
            line-height: 1.8;
            white-space: pre-wrap;
        }
        .conclusion-text.empty {
            color: #a0aec0;
            font-style: italic;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .context-box {
            background: #faf5ff;
            border: 2px solid #805ad5;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .context-box h5 {
            color: #553c9a;
            margin-bottom: 0.5rem;
        }

        .risk-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 15px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .risk-badge.muy-alto, .risk-badge.riesgo-muy-alto { background: #fed7d7; color: #c53030; }
        .risk-badge.alto, .risk-badge.riesgo-alto { background: #feebc8; color: #c05621; }
        .risk-badge.medio, .risk-badge.riesgo-medio { background: #fefcbf; color: #975a16; }
        .risk-badge.bajo, .risk-badge.riesgo-bajo { background: #c6f6d5; color: #276749; }
        .risk-badge.sin-riesgo { background: #e2e8f0; color: #4a5568; }

        /* DataTables customization */
        #maxRiskTable thead th {
            background: #2c5282;
            color: white;
            font-weight: 600;
            border: none;
        }
        #maxRiskTable thead .filters th {
            background: #f8f9fa;
            padding: 8px 4px;
        }
        #maxRiskTable thead .filters select {
            font-size: 0.85rem;
            padding: 4px 8px;
        }
        #maxRiskTable tbody tr:hover {
            background-color: #ebf8ff;
        }
        .dataTables_wrapper .dataTables_filter input {
            border-radius: 20px;
            padding: 5px 15px;
        }
        .questionnaire-badge {
            padding: 0.2rem 0.5rem;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: 600;
        }
        .questionnaire-badge.intralaboral { background: #bee3f8; color: #2b6cb0; }
        .questionnaire-badge.extralaboral { background: #c6f6d5; color: #276749; }
        .questionnaire-badge.estres { background: #e9d8fd; color: #553c9a; }
        .type-badge {
            padding: 0.2rem 0.4rem;
            border-radius: 8px;
            font-size: 0.65rem;
            font-weight: 500;
            text-transform: uppercase;
        }
        .type-badge.total { background: #1a365d; color: white; }
        .type-badge.domain { background: #4a5568; color: white; }
        .type-badge.dimension { background: #718096; color: white; }

        .critical-alert {
            background: linear-gradient(135deg, #fff5f5 0%, #fed7d7 100%);
            border-left: 4px solid #c53030;
            padding: 1rem;
            border-radius: 0 10px 10px 0;
            margin-bottom: 1rem;
        }

        .btn-generate {
            background: linear-gradient(135deg, #ed8936, #dd6b20);
            border: none;
            color: white;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(237, 137, 54, 0.4);
        }
        .btn-generate:hover {
            background: linear-gradient(135deg, #dd6b20, #c05621);
            color: white;
            transform: translateY(-2px);
        }

        .accordion-button:not(.collapsed) {
            background: #edf2f7;
            color: #2d3748;
        }
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

        <!-- Estadisticas -->
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

        <!-- Alerta de elementos criticos -->
        <?php if ($stats['critical_count'] > 0): ?>
        <div class="critical-alert">
            <strong><i class="fas fa-exclamation-triangle me-2"></i>Atencion:</strong>
            Se identificaron <strong><?= $stats['critical_count'] ?></strong> elementos en riesgo alto o muy alto que requieren intervencion prioritaria.
        </div>
        <?php endif; ?>

        <!-- SECCION PRINCIPAL: Conclusion Global -->
        <div class="conclusion-box">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4><i class="fas fa-file-alt me-2"></i>Conclusion Total De Aplicacion Bateria De Riesgo Psicosocial</h4>
                <?php if (!empty($batteryService['global_conclusion_generated_at'])): ?>
                <small class="text-muted">
                    Generada: <?= date('d/m/Y H:i', strtotime($batteryService['global_conclusion_generated_at'])) ?>
                </small>
                <?php endif; ?>
            </div>

            <!-- Contexto del Consultor -->
            <div class="context-box mb-4">
                <h5><i class="fas fa-comment-dots me-2"></i>Contexto Complementario para IA</h5>
                <p class="text-muted small mb-2">
                    Agrega informacion que la IA debe considerar (ej: "empresa textil", "mayoria mujeres cabeza de familia", "turnos nocturnos").
                    Este texto NO aparece en el informe, solo guia a la IA.
                </p>
                <textarea id="contextPrompt" class="form-control" rows="3" placeholder="Ej: Es una empresa del sector textil con alta rotacion de personal. La mayoria son mujeres cabeza de familia..."><?= esc($batteryService['global_conclusion_prompt'] ?? '') ?></textarea>
                <button class="btn btn-outline-secondary btn-sm mt-2" onclick="saveContext()">
                    <i class="fas fa-save me-1"></i>Guardar Contexto
                </button>
            </div>

            <!-- Boton Generar -->
            <div class="text-center mb-4">
                <button class="btn btn-generate" onclick="generateConclusion()" id="btnGenerate">
                    <i class="fas fa-robot me-2"></i>Generar Conclusion con IA
                </button>
                <p class="text-muted small mt-2">La IA analizara todos los resultados y generara una conclusion ejecutiva integrada</p>
            </div>

            <!-- Texto de Conclusion -->
            <div class="conclusion-text <?= empty($batteryService['global_conclusion_text']) ? 'empty' : '' ?>" id="conclusionText">
                <?php if (!empty($batteryService['global_conclusion_text'])): ?>
                    <?= nl2br(esc($batteryService['global_conclusion_text'])) ?>
                <?php else: ?>
                    <span>Aun no se ha generado la conclusion. Haz clic en "Generar Conclusion con IA".</span>
                <?php endif; ?>
            </div>

            <?php if (!empty($batteryService['global_conclusion_text'])): ?>
            <div class="mt-3 text-end">
                <button class="btn btn-outline-secondary btn-sm" onclick="editConclusion()">
                    <i class="fas fa-edit me-1"></i>Editar
                </button>
                <button class="btn btn-outline-primary btn-sm" onclick="copyConclusion()">
                    <i class="fas fa-copy me-1"></i>Copiar
                </button>
            </div>
            <?php endif; ?>
        </div>

        <!-- Tabla de Resultados con DataTables -->
        <div class="section-card">
            <h5 class="mb-3"><i class="fas fa-table me-2"></i>Resultados de Maximo Riesgo <span class="badge bg-secondary"><?= count($allResults) ?> elementos</span></h5>

            <div class="table-responsive">
                <table id="maxRiskTable" class="table table-striped table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>Cuestionario</th>
                            <th>Tipo</th>
                            <th>Elemento</th>
                            <th>Puntaje</th>
                            <th>Forma</th>
                            <th>Nivel de Riesgo</th>
                            <th>Forma A</th>
                            <th>Forma B</th>
                        </tr>
                        <tr class="filters">
                            <th>
                                <select class="form-select form-select-sm" id="filterQuestionnaire">
                                    <option value="">Todos</option>
                                    <option value="Intralaboral">Intralaboral</option>
                                    <option value="Extralaboral">Extralaboral</option>
                                    <option value="Estres">Estrés</option>
                                </select>
                            </th>
                            <th>
                                <select class="form-select form-select-sm" id="filterType">
                                    <option value="">Todos</option>
                                    <option value="Total">Total</option>
                                    <option value="Domain">Dominio</option>
                                    <option value="Dimension">Dimensión</option>
                                </select>
                            </th>
                            <th></th>
                            <th></th>
                            <th>
                                <select class="form-select form-select-sm" id="filterForm">
                                    <option value="">Todas</option>
                                    <option value="A">Forma A</option>
                                    <option value="B">Forma B</option>
                                </select>
                            </th>
                            <th>
                                <select class="form-select form-select-sm" id="filterRisk">
                                    <option value="">Todos</option>
                                    <option value="Muy Alto">Muy Alto</option>
                                    <option value="Alto">Alto</option>
                                    <option value="Medio">Medio</option>
                                    <option value="Bajo">Bajo</option>
                                    <option value="Sin Riesgo">Sin Riesgo</option>
                                </select>
                            </th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allResults as $el):
                            $riskClass = str_replace('_', '-', $el['worst_risk_level']);
                            $riskLabel = ucfirst(str_replace('_', ' ', str_replace('riesgo_', '', $el['worst_risk_level'])));
                            $questionnaireLabel = ucfirst($el['questionnaire_type']);
                            if ($questionnaireLabel === 'Estres') $questionnaireLabel = 'Estrés';
                            $typeLabel = ucfirst($el['element_type']);
                        ?>
                        <tr>
                            <td>
                                <span class="questionnaire-badge <?= $el['questionnaire_type'] ?>">
                                    <?= $questionnaireLabel ?>
                                </span>
                            </td>
                            <td>
                                <span class="type-badge <?= $el['element_type'] ?>">
                                    <?= $typeLabel ?>
                                </span>
                            </td>
                            <td><strong><?= esc($el['element_name']) ?></strong></td>
                            <td class="text-center"><strong><?= number_format($el['worst_score'], 1) ?></strong></td>
                            <td class="text-center"><strong><?= $el['worst_form'] ?></strong></td>
                            <td>
                                <span class="risk-badge <?= $riskClass ?>">
                                    <?= $riskLabel ?>
                                </span>
                            </td>
                            <td class="text-center small">
                                <?php if ($el['form_a_score'] !== null): ?>
                                    <?= number_format($el['form_a_score'], 1) ?>
                                    <br><small class="text-muted"><?= $el['form_a_count'] ?? 0 ?> eval.</small>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center small">
                                <?php if ($el['form_b_score'] !== null): ?>
                                    <?= number_format($el['form_b_score'], 1) ?>
                                    <br><small class="text-muted"><?= $el['form_b_count'] ?? 0 ?> eval.</small>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal: Editar Conclusion -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Editar Conclusion</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <textarea id="editConclusionText" class="form-control" rows="15"><?= esc($batteryService['global_conclusion_text'] ?? '') ?></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="saveEditedConclusion()">
                        <i class="fas fa-save me-1"></i>Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Generando -->
    <div class="modal fade" id="generatingModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <div class="spinner-border text-warning mb-3" role="status" style="width: 3rem; height: 3rem;"></div>
                    <h5>Generando conclusion...</h5>
                    <p class="text-muted small mb-0">La IA esta analizando todos los resultados</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script>
        const batteryServiceId = <?= $batteryService['id'] ?>;
        const editModal = new bootstrap.Modal(document.getElementById('editModal'));
        const generatingModal = new bootstrap.Modal(document.getElementById('generatingModal'));

        // Inicializar DataTables
        $(document).ready(function() {
            const table = $('#maxRiskTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },
                order: [[5, 'desc'], [3, 'desc']], // Ordenar por nivel de riesgo y puntaje
                pageLength: 25,
                dom: '<"row"<"col-md-6"l><"col-md-6"f>>rtip',
                columnDefs: [
                    { orderable: true, targets: [0, 1, 2, 3, 4, 5] },
                    { orderable: false, targets: [6, 7] }
                ]
            });

            // Filtros personalizados
            $('#filterQuestionnaire').on('change', function() {
                const val = $(this).val();
                table.column(0).search(val).draw();
            });

            $('#filterType').on('change', function() {
                const val = $(this).val();
                table.column(1).search(val).draw();
            });

            $('#filterForm').on('change', function() {
                const val = $(this).val();
                table.column(4).search(val ? '^' + val + '$' : '', true, false).draw();
            });

            $('#filterRisk').on('change', function() {
                const val = $(this).val();
                table.column(5).search(val).draw();
            });
        });

        async function saveContext() {
            const prompt = document.getElementById('contextPrompt').value;

            try {
                const response = await fetch('/max-risk/save-prompt', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: `battery_service_id=${batteryServiceId}&prompt=${encodeURIComponent(prompt)}`
                });

                const data = await response.json();
                showToast(data.message, data.success ? 'success' : 'error');
            } catch (error) {
                showToast('Error de conexion', 'error');
            }
        }

        async function generateConclusion() {
            generatingModal.show();

            try {
                const response = await fetch('/max-risk/generate-conclusion', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: `battery_service_id=${batteryServiceId}`
                });

                const data = await response.json();
                generatingModal.hide();

                if (data.success) {
                    const conclusionDiv = document.getElementById('conclusionText');
                    conclusionDiv.classList.remove('empty');
                    conclusionDiv.innerHTML = data.conclusion.replace(/\n/g, '<br>');
                    document.getElementById('editConclusionText').value = data.conclusion;
                    showToast('Conclusion generada correctamente', 'success');
                    // Recargar para mostrar botones de editar/copiar
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast(data.message || 'Error al generar', 'error');
                }
            } catch (error) {
                generatingModal.hide();
                showToast('Error de conexion', 'error');
            }
        }

        function editConclusion() {
            editModal.show();
        }

        async function saveEditedConclusion() {
            const conclusion = document.getElementById('editConclusionText').value;

            try {
                const response = await fetch('/max-risk/save-conclusion', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: `battery_service_id=${batteryServiceId}&conclusion=${encodeURIComponent(conclusion)}`
                });

                const data = await response.json();

                if (data.success) {
                    document.getElementById('conclusionText').innerHTML = conclusion.replace(/\n/g, '<br>');
                    editModal.hide();
                    showToast('Conclusion guardada', 'success');
                } else {
                    showToast(data.message || 'Error al guardar', 'error');
                }
            } catch (error) {
                showToast('Error de conexion', 'error');
            }
        }

        function copyConclusion() {
            const text = document.getElementById('conclusionText').innerText;
            navigator.clipboard.writeText(text).then(() => {
                showToast('Conclusion copiada al portapapeles', 'success');
            });
        }

        function showToast(message, type) {
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
