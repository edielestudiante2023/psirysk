<?php
use Config\Estres;
$questions = Estres::getQuestions();
$likertScale = Estres::getLikertScale();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuestionario de Estrés - PsyRisk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }
        .assessment-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .form-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .progress-bar-custom {
            height: 30px;
            border-radius: 15px;
            background: #e9ecef;
            overflow: hidden;
            margin-bottom: 2rem;
            position: sticky;
            top: 20px;
            z-index: 1000;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #28a745 0%, #20c997 100%);
            transition: width 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        .question-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }
        .question-card:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }
        .question-card.answered {
            background: #d4edda;
            border-left: 4px solid #28a745;
        }
        .question-number {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 15px;
        }
        .question-text {
            font-size: 1.1rem;
            color: #333;
            margin-bottom: 1rem;
        }
        .likert-options {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .likert-option {
            flex: 1;
            min-width: 120px;
        }
        .likert-option input[type="radio"] {
            display: none;
        }
        .likert-option label {
            display: block;
            padding: 12px;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
        }
        .likert-option label:hover {
            border-color: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(102, 126, 234, 0.2);
        }
        .likert-option input[type="radio"]:checked + label {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-color: #667eea;
        }
        .alert-auto-save {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1002;
            display: none;
        }

        /* OPTIMIZACIONES MÓVILES */
        @media (max-width: 768px) {
            body { padding: 0.5rem 0; }
            .assessment-container { padding: 0 0.5rem; }
            .form-card { border-radius: 15px; padding: 1rem; margin-bottom: 1rem; }
            .form-card h2 { font-size: 1.3rem; }
            .form-card h4 { font-size: 1.1rem; }
            .progress-bar-custom { height: 25px; top: 10px; margin-bottom: 1rem; }
            .progress-fill { font-size: 0.85rem; }
            .question-card { padding: 1rem; margin-bottom: 1rem; }
            .question-number { width: 35px; height: 35px; font-size: 0.9rem; margin-right: 10px; }
            .question-text { font-size: 0.95rem; line-height: 1.4; }
            .likert-options { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
            .likert-option { min-width: unset; }
            .likert-option label { padding: 14px 8px; font-size: 0.85rem; min-height: 50px; display: flex; align-items: center; justify-content: center; }
            .btn-save { bottom: 15px; right: 15px; left: 15px; width: calc(100% - 30px); padding: 15px; font-size: 1rem; border-radius: 12px; }
            .alert-auto-save { top: 10px; right: 10px; left: 10px; width: calc(100% - 20px); }
            .table { font-size: 0.85rem; }
            .table th, .table td { padding: 0.5rem 0.3rem; }
        }
        @media (max-width: 480px) {
            .likert-options { grid-template-columns: 1fr; }
            .likert-option label { padding: 16px 12px; font-size: 0.9rem; }
            .question-card { padding: 0.8rem; }
        }
        @media (hover: none) and (pointer: coarse) {
            .likert-option label { min-height: 48px; }
            .likert-option label:active { transform: scale(0.98); background: #f0f3ff; }
        }
    </style>
</head>
<body>
    <div class="assessment-container">
        <!-- Header -->
        <div class="form-card">
            <div class="text-center mb-4">
                <h2><i class="fas fa-brain me-2"></i>Cuestionario para la Evaluación del Estrés</h2>
                <p class="text-muted">Última etapa de la batería</p>
            </div>

            <!-- Progress Bar -->
            <div class="progress-bar-custom">
                <div class="progress-fill" id="progressBar" style="width: 87.5%">
                    <span id="progressText">Paso 4 de 4 - 0/31 respondidas</span>
                </div>
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Instrucciones:</strong><br>
                A continuación encontrará preguntas sobre las reacciones que ha presentado en los últimos tres meses.
                Seleccione la opción de la respuesta que refleje mejor la frecuencia con la que ha experimentado cada síntoma.
                Sus respuestas son completamente confidenciales.
            </div>

            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Importante:</strong> Este es el último cuestionario. Al finalizar habrá completado toda la batería de evaluación.
            </div>
        </div>

        <!-- Auto-save alert -->
        <div class="alert alert-success alert-auto-save" id="autoSaveAlert">
            <i class="fas fa-check-circle me-2"></i>Progreso guardado automáticamente
        </div>

        <!-- Questions Form -->
        <form id="estresForm">
            <?php foreach ($questions as $number => $questionText): ?>
                <div class="question-card" id="question-<?= $number ?>" data-question="<?= $number ?>">
                    <div class="d-flex align-items-start">
                        <div class="question-number"><?= $number ?></div>
                        <div class="flex-grow-1">
                            <div class="question-text"><?= esc($questionText) ?></div>
                            <div class="likert-options">
                                <?php foreach ($likertScale as $value => $label): ?>
                                    <div class="likert-option">
                                        <input type="radio"
                                               id="q<?= $number ?>_<?= $value ?>"
                                               name="responses[<?= $number ?>]"
                                               value="<?= $value ?>"
                                               <?= isset($responses[$number]) && $responses[$number] == $value ? 'checked' : '' ?>>
                                        <label for="q<?= $number ?>_<?= $value ?>">
                                            <?= esc($label) ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="text-center mt-4 mb-5">
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="fas fa-check-circle me-2"></i>Finalizar Batería
                </button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let answeredQuestions = new Set();
        let autoSaveTimer = null;
        const totalQuestions = <?= count($questions) ?>;
        const sessionId = '<?= bin2hex(random_bytes(16)) ?>';

        // Initialize with saved responses
        <?php if (!empty($responses)): ?>
            <?php foreach ($responses as $qNum => $value): ?>
                answeredQuestions.add(<?= $qNum ?>);
                document.getElementById('question-<?= $qNum ?>').classList.add('answered');
            <?php endforeach; ?>
        <?php endif; ?>

        // Update progress bar
        function updateProgress() {
            const answered = answeredQuestions.size;
            const percentage = 87.5 + Math.round((answered / totalQuestions) * 12.5); // 87.5% to 100%
            const progressBar = document.getElementById('progressBar');
            const progressText = document.getElementById('progressText');

            progressBar.style.width = percentage + '%';
            progressText.textContent = `Paso 4 de 4 - ${answered}/${totalQuestions} respondidas`;
        }

        // Mark question as answered
        document.querySelectorAll('input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const questionNum = parseInt(this.name.match(/\d+/)[0]);
                answeredQuestions.add(questionNum);
                document.getElementById('question-' + questionNum).classList.add('answered');
                updateProgress();

                // Auto-save every 10 questions
                if (answeredQuestions.size % 10 === 0) {
                    saveProgress();
                }

                // Debounced auto-save
                clearTimeout(autoSaveTimer);
                autoSaveTimer = setTimeout(() => saveProgress(), 30000); // 30 seconds
            });
        });

        // Save progress function
        async function saveProgress() {
            const formData = new FormData(document.getElementById('estresForm'));
            formData.append('session_id', sessionId);

            try {
                const response = await fetch('<?= base_url('assessment/saveEstres') ?>', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    showAutoSaveAlert();
                }
            } catch (error) {
                console.error('Error al guardar:', error);
            }
        }

        // Show auto-save alert
        function showAutoSaveAlert() {
            const alert = document.getElementById('autoSaveAlert');
            alert.style.display = 'block';
            setTimeout(() => {
                alert.style.display = 'none';
            }, 3000);
        }

        // Form submission
        document.getElementById('estresForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            if (answeredQuestions.size < totalQuestions) {
                alert(`Debes responder TODAS las preguntas antes de finalizar.\nHas respondido ${answeredQuestions.size} de ${totalQuestions} preguntas.\nFaltan ${totalQuestions - answeredQuestions.size} preguntas por responder.`);
                return;
            }

            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Finalizando batería...';

            const formData = new FormData(this);
            formData.append('session_id', sessionId);

            try {
                const response = await fetch('<?= base_url('assessment/saveEstres') ?>', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    // Show completion animation
                    submitBtn.innerHTML = '<i class="fas fa-check-circle me-2"></i>¡Completado!';
                    setTimeout(() => {
                        window.location.href = result.redirect || '<?= base_url('assessment/completed') ?>';
                    }, 1000);
                } else {
                    alert('Error al guardar: ' + result.message);
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-check-circle me-2"></i>Finalizar Batería';
                }
            } catch (error) {
                alert('Error al guardar. Por favor, intenta nuevamente.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-check-circle me-2"></i>Finalizar Batería';
            }
        });

        // Initialize progress
        updateProgress();

        // Save before unload
        window.addEventListener('beforeunload', function(e) {
            if (answeredQuestions.size > 0 && answeredQuestions.size < totalQuestions) {
                saveProgress();
            }
        });
    </script>

    <!-- INLINE EDITING: Auto-guardado con verificación de integridad -->
    <script src="<?= base_url('js/inline-editing.js') ?>?v=<?= time() ?>"></script>
    <script>
        // Inicializar inline editing para Estrés
        InlineEditing.init({
            endpoint: '<?= base_url('assessment/save-question-estres') ?>',
            formType: 'estres',
            debugMode: <?= (env('DEBUG_SAVE_VERIFICATION') === 'true' || env('DEBUG_SAVE_VERIFICATION') === true) ? 'true' : 'false' ?>
        });
        console.log('✅ Inline editing initialized for Estrés');
    </script>
</body>
</html>
