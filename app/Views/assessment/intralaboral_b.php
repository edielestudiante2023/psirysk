<?php
use Config\IntralaboralB;
$questions = IntralaboralB::getQuestions();
$likertScale = IntralaboralB::getLikertScale();
$sectionHeaders = IntralaboralB::getSectionHeaders();
$conditionalQuestion = IntralaboralB::getConditionalQuestion();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuestionario Intralaboral - Forma B - PsyRisk</title>
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
        .question-card.unanswered {
            background: #ffe5e5;
            border-left: 4px solid #dc3545;
            animation: pulse-red 1s ease-in-out;
        }
        .question-card.unanswered .question-number {
            background: #dc3545;
        }
        .question-card.unanswered .likert-option label {
            border-color: #f5c6cb;
        }
        .question-card.unanswered .likert-option label:hover {
            border-color: #dc3545;
        }
        @keyframes pulse-red {
            0%, 100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
            50% { box-shadow: 0 0 15px 5px rgba(220, 53, 69, 0.3); }
        }
        .conditional-question.unanswered {
            background: #ffe5e5;
            border-left: 4px solid #dc3545;
            animation: pulse-red 1s ease-in-out;
        }
        .conditional-question.unanswered .question-number {
            background: #dc3545;
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
        .btn-save {
            position: fixed;
            top: 30px;
            right: 30px;
            z-index: 1001;
            padding: 15px 30px;
            font-size: 1.1rem;
            border-radius: 50px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .section-divider {
            margin: 3rem 0;
            border-top: 3px solid #667eea;
            padding-top: 2rem;
        }
        .alert-auto-save {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1002;
            display: none;
        }
        .conditional-question .form-check {
            padding: 15px 25px;
            border: 2px solid #dee2e6;
            border-radius: 10px;
            background: white;
            transition: all 0.3s ease;
        }
        .conditional-question .form-check:hover {
            border-color: #667eea;
            background: #f0f3ff;
        }
        .conditional-question .form-check-input {
            width: 24px;
            height: 24px;
            margin-top: 0.15rem;
            cursor: pointer;
        }
        .conditional-question .form-check-input:checked {
            background-color: #667eea;
            border-color: #667eea;
        }
        .conditional-question .form-check-label {
            cursor: pointer;
            margin-left: 10px;
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
            .btn-save { top: 15px; right: 15px; left: 15px; width: calc(100% - 30px); padding: 15px; font-size: 1rem; border-radius: 12px; }
            .section-divider { margin: 2rem 0; padding-top: 1.5rem; }
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
                <h2><i class="fas fa-clipboard-check me-2"></i>Cuestionario Intralaboral - Forma B</h2>
                <p class="text-muted">Factores de Riesgo Psicosocial Intralaboral</p>
            </div>

            <!-- Progress Bar -->
            <div class="progress-bar-custom">
                <div class="progress-fill" id="progressBar" style="width: 25%">
                    <span id="progressText">Paso 2 de 4 - 0/97 respondidas</span>
                </div>
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Instrucciones:</strong><br>
                A continuación encontrará preguntas sobre aspectos de su trabajo. Seleccione la opción de la respuesta que refleje mejor su trabajo.
                Sus respuestas son completamente confidenciales.
            </div>

            <div class="alert alert-success">
                <i class="fas fa-save me-2"></i>
                <strong>Guardado automático:</strong> Sus respuestas se guardan automáticamente cada 10 preguntas.
            </div>
        </div>

        <!-- Auto-save alert -->
        <div class="alert alert-success alert-auto-save" id="autoSaveAlert">
            <i class="fas fa-check-circle me-2"></i>Progreso guardado automáticamente
        </div>

        <!-- Questions Form -->
        <form id="intralaboralForm">
            <?php foreach ($questions as $number => $questionText): ?>
                <?php if (isset($sectionHeaders[$number])): ?>
                    <div class="form-card mb-3">
                        <div class="alert alert-primary mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong><?= esc($sectionHeaders[$number]) ?></strong>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($number == 89): ?>
                    <!-- Pregunta condicional antes de la sección de atención a clientes -->
                    <div class="form-card mb-3">
                        <div class="alert alert-primary mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Las siguientes preguntas están relacionadas con la atención a clientes y usuarios.</strong>
                        </div>
                    </div>

                    <div class="question-card conditional-question" id="conditional-question">
                        <div class="d-flex align-items-start">
                            <div class="question-number"><?= esc($conditionalQuestion['number']) ?></div>
                            <div class="flex-grow-1">
                                <div class="question-text"><?= esc($conditionalQuestion['text']) ?></div>
                                <div class="d-flex gap-3 mt-3">
                                    <?php foreach ($conditionalQuestion['options'] as $option): ?>
                                        <div class="form-check">
                                            <input class="form-check-input"
                                                   type="radio"
                                                   name="attends_clients"
                                                   id="attends_clients_<?= strtolower($option) ?>"
                                                   value="<?= $option ?>"
                                                   <?= isset($responses['attends_clients']) && $responses['attends_clients'] == $option ? 'checked' : '' ?>>
                                            <label class="form-check-label fs-5" for="attends_clients_<?= strtolower($option) ?>">
                                                <strong><?= esc($option) ?></strong>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="question-card <?= in_array($number, $conditionalQuestion['controls_questions']) ? 'client-question' : '' ?>"
                     id="question-<?= $number ?>"
                     data-question="<?= $number ?>"
                     <?= in_array($number, $conditionalQuestion['controls_questions']) ? 'style="display: none;"' : '' ?>>
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
                                               <?= isset($responses[$number]) && $responses[$number] == $value ? 'checked' : '' ?>
                                               <?= in_array($number, $conditionalQuestion['controls_questions']) ? 'data-conditional="true"' : '' ?>>
                                        <label for="q<?= $number ?>_<?= $value ?>">
                                            <strong><?= $value ?></strong><br>
                                            <small><?= esc($label) ?></small>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="text-center mt-4 mb-5">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-arrow-right me-2"></i>Continuar al siguiente cuestionario
                </button>
            </div>
        </form>

        <!-- Floating save button -->
        <button type="button" class="btn btn-success btn-save" id="floatingSaveBtn" style="display: none;">
            <i class="fas fa-save me-2"></i>Guardar progreso
        </button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let answeredQuestions = new Set();
        let autoSaveTimer = null;
        const totalQuestions = <?= count($questions) ?>;
        const sessionId = '<?= bin2hex(random_bytes(16)) ?>';
        const clientQuestions = <?= json_encode($conditionalQuestion['controls_questions']) ?>;
        let attendsClients = null;

        // DEFINIR FUNCIONES PRIMERO (antes de usarlas)
        function handleClientQuestionsVisibility(value) {
            const clientQuestionCards = document.querySelectorAll('.client-question');

            if (value === 'Sí') {
                // Mostrar preguntas 89-97
                clientQuestionCards.forEach(card => {
                    card.style.display = 'block';
                });
            } else if (value === 'No') {
                // Ocultar preguntas 89-97 y limpiar respuestas
                clientQuestionCards.forEach(card => {
                    card.style.display = 'none';
                    const inputs = card.querySelectorAll('input[type="radio"]');
                    inputs.forEach(input => {
                        input.checked = false;
                    });
                    card.classList.remove('answered');
                });

                // Remover de answeredQuestions
                clientQuestions.forEach(num => {
                    answeredQuestions.delete(num);
                });

                updateProgress();
            }
        }

        // Update progress bar
        function updateProgress() {
            const answered = answeredQuestions.size;
            // Ajustar total de preguntas según respuesta condicional
            let adjustedTotal = totalQuestions;
            if (attendsClients === 'No') {
                adjustedTotal = totalQuestions - clientQuestions.length; // 97 - 9 = 88
            }

            const percentage = Math.round((answered / adjustedTotal) * 100);
            const progressBar = document.getElementById('progressBar');
            const progressText = document.getElementById('progressText');

            progressBar.style.width = Math.max(25, percentage) + '%';
            progressText.textContent = `Paso 2 de 4 - ${answered}/${adjustedTotal} respondidas`;

            // Show floating save button if there are unanswered changes
            if (answered > 0 && answered < adjustedTotal) {
                document.getElementById('floatingSaveBtn').style.display = 'block';
            }
        }

        // Handle conditional question for client attendance
        document.querySelectorAll('input[name="attends_clients"]').forEach(radio => {
            radio.addEventListener('change', function() {
                attendsClients = this.value;
                console.log('Pregunta condicional respondida:', attendsClients);
                handleClientQuestionsVisibility(this.value);
                document.getElementById('conditional-question').classList.add('answered');
            });
        });

        // Initialize with saved responses (DESPUÉS de definir las funciones)
        <?php if (!empty($responses)): ?>
            <?php foreach ($responses as $qNum => $value): ?>
                <?php if ($qNum !== 'attends_clients'): ?>
                    answeredQuestions.add(<?= $qNum ?>);
                    {
                        const questionCard = document.getElementById('question-<?= $qNum ?>');
                        if (questionCard) questionCard.classList.add('answered');
                    }
                <?php endif; ?>
            <?php endforeach; ?>

            // Restore conditional question state
            <?php if (isset($responses['attends_clients'])): ?>
                attendsClients = '<?= $responses['attends_clients'] ?>';
                handleClientQuestionsVisibility(attendsClients);
                document.getElementById('conditional-question').classList.add('answered');
            <?php endif; ?>
        <?php endif; ?>

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
            const formData = new FormData(document.getElementById('intralaboralForm'));
            formData.append('session_id', sessionId);

            // Agregar respuesta de atención a clientes
            if (attendsClients !== null) {
                formData.append('atiende_clientes', attendsClients === 'Sí' ? '1' : '0');
            }

            try {
                const response = await fetch('<?= base_url('assessment/intralaboral') ?>', {
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

        // Floating save button
        document.getElementById('floatingSaveBtn').addEventListener('click', function() {
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Guardando...';
            this.disabled = true;

            saveProgress().then(() => {
                this.innerHTML = '<i class="fas fa-check me-2"></i>Guardado';
                setTimeout(() => {
                    this.innerHTML = '<i class="fas fa-save me-2"></i>Guardar progreso';
                    this.disabled = false;
                }, 2000);
            });
        });

        // Función para marcar preguntas sin responder
        function markUnansweredQuestions() {
            // Limpiar marcas anteriores
            document.querySelectorAll('.question-card.unanswered, .conditional-question.unanswered').forEach(card => {
                card.classList.remove('unanswered');
            });

            let firstUnanswered = null;
            let unansweredCount = 0;

            // Verificar pregunta condicional
            if (attendsClients === null) {
                const cq = document.getElementById('conditional-question');
                cq.classList.add('unanswered');
                if (!firstUnanswered) firstUnanswered = cq;
                unansweredCount++;
            }

            // Verificar todas las preguntas regulares
            for (let i = 1; i <= totalQuestions; i++) {
                const questionCard = document.getElementById('question-' + i);
                if (!questionCard) continue;

                // Saltar preguntas ocultas (condicionales)
                if (questionCard.style.display === 'none') continue;

                if (!answeredQuestions.has(i)) {
                    questionCard.classList.add('unanswered');
                    if (!firstUnanswered) firstUnanswered = questionCard;
                    unansweredCount++;
                }
            }

            return { firstUnanswered, unansweredCount };
        }

        // Quitar marca de unanswered cuando se responde
        document.querySelectorAll('input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const questionCard = this.closest('.question-card, .conditional-question');
                if (questionCard) {
                    questionCard.classList.remove('unanswered');
                }
            });
        });

        // Form submission
        document.getElementById('intralaboralForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            // Ajustar total según respuesta condicional
            let adjustedTotal = totalQuestions;
            if (attendsClients === 'No') {
                adjustedTotal = totalQuestions - clientQuestions.length;
            }

            // Marcar preguntas sin responder y obtener información
            const { firstUnanswered, unansweredCount } = markUnansweredQuestions();

            // Validar que se haya respondido la pregunta condicional
            if (attendsClients === null) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Pregunta sin responder',
                    text: 'Por favor responde la pregunta sobre atención a clientes.',
                    confirmButtonColor: '#667eea'
                });
                firstUnanswered.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }

            if (answeredQuestions.size < adjustedTotal) {
                const faltan = adjustedTotal - answeredQuestions.size;
                Swal.fire({
                    icon: 'warning',
                    title: 'Preguntas sin responder',
                    html: `<p>Debes responder <strong>TODAS</strong> las preguntas antes de continuar.</p>
                           <p>Has respondido <strong>${answeredQuestions.size}</strong> de <strong>${adjustedTotal}</strong> preguntas.</p>
                           <p style="color: #dc3545; font-weight: bold;">Faltan ${faltan} pregunta${faltan > 1 ? 's' : ''} por responder.</p>
                           <p><small>Las preguntas sin responder están marcadas en <span style="color: #dc3545;">rojo</span>.</small></p>`,
                    confirmButtonColor: '#667eea',
                    confirmButtonText: 'Ir a completar'
                }).then(() => {
                    if (firstUnanswered) {
                        firstUnanswered.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                });
                return;
            }

            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Guardando y continuando...';

            const formData = new FormData(this);
            formData.append('session_id', sessionId);

            // Agregar respuesta de atención a clientes (igual que en auto-save)
            if (attendsClients !== null) {
                formData.append('atiende_clientes', attendsClients === 'Sí' ? '1' : '0');
            }

            try {
                const response = await fetch('<?= base_url('assessment/intralaboral') ?>', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    window.location.href = '<?= base_url('assessment/extralaboral') ?>';
                } else {
                    alert('Error al guardar: ' + result.message);
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-arrow-right me-2"></i>Continuar al siguiente cuestionario';
                }
            } catch (error) {
                alert('Error al guardar. Por favor, intenta nuevamente.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-arrow-right me-2"></i>Continuar al siguiente cuestionario';
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
        // Inicializar inline editing para Intralaboral B
        InlineEditing.init({
            endpoint: '<?= base_url('assessment/save-question-intralaboral') ?>',
            formType: 'intralaboral_b',
            debugMode: <?= (env('DEBUG_SAVE_VERIFICATION') === 'true' || env('DEBUG_SAVE_VERIFICATION') === true) ? 'true' : 'false' ?>
        });
        console.log('✅ Inline editing initialized for Intralaboral B');
    </script>
</body>
</html>
