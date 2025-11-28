<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encuesta de Satisfacción - PsyRisk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .survey-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 800px;
            width: 100%;
            padding: 40px;
        }

        .survey-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .survey-header h1 {
            color: #667eea;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .survey-header p {
            color: #6c757d;
            font-size: 1.1rem;
        }

        .question-card {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .question-card:hover {
            border-color: #667eea;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.1);
        }

        .question-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
            font-size: 1.1rem;
        }

        .required {
            color: #dc3545;
            margin-left: 5px;
        }

        .likert-scale {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-top: 15px;
        }

        .likert-option {
            flex: 1;
            text-align: center;
        }

        .likert-option input[type="radio"] {
            display: none;
        }

        .likert-option label {
            display: block;
            padding: 15px 10px;
            border: 2px solid #dee2e6;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
            font-weight: 500;
        }

        .likert-option label:hover {
            border-color: #667eea;
            background: #f8f9ff;
        }

        .likert-option input[type="radio"]:checked + label {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-color: #667eea;
            transform: scale(1.05);
        }

        .emoji {
            font-size: 1.5rem;
            display: block;
            margin-bottom: 5px;
        }

        .scale-label {
            font-size: 0.85rem;
            color: #6c757d;
        }

        .comments-section {
            margin-top: 30px;
        }

        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 15px 40px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 10px;
            width: 100%;
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .alert-info {
            background: #e7f3ff;
            border: 2px solid #b3d9ff;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 25px;
        }

        @media (max-width: 768px) {
            .likert-scale {
                flex-direction: column;
                gap: 10px;
            }

            .emoji {
                font-size: 1.2rem;
            }

            .survey-container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="survey-container">
        <div class="survey-header">
            <h1><i class="bi bi-star-fill"></i> Encuesta de Satisfacción</h1>
            <p>Ayúdanos a mejorar nuestro servicio</p>
            <p class="text-muted small">Servicio: <strong><?= esc($service['service_name']) ?></strong></p>
        </div>

        <div class="alert alert-info">
            <i class="bi bi-info-circle-fill"></i>
            Para descargar sus informes en PDF, por favor complete esta breve encuesta de satisfacción.
            Solo le tomará 2 minutos.
        </div>

        <form id="satisfactionForm">
            <!-- Pregunta 1 -->
            <div class="question-card">
                <div class="question-title">
                    1. ¿Qué tan satisfecho está con el servicio recibido?<span class="required">*</span>
                </div>
                <div class="likert-scale">
                    <div class="likert-option">
                        <input type="radio" name="question_1" id="q1_1" value="1" required>
                        <label for="q1_1">
                            <span class="emoji">😞</span>
                            <span class="scale-label">Muy insatisfecho</span>
                        </label>
                    </div>
                    <div class="likert-option">
                        <input type="radio" name="question_1" id="q1_2" value="2">
                        <label for="q1_2">
                            <span class="emoji">😕</span>
                            <span class="scale-label">Insatisfecho</span>
                        </label>
                    </div>
                    <div class="likert-option">
                        <input type="radio" name="question_1" id="q1_3" value="3">
                        <label for="q1_3">
                            <span class="emoji">😐</span>
                            <span class="scale-label">Neutral</span>
                        </label>
                    </div>
                    <div class="likert-option">
                        <input type="radio" name="question_1" id="q1_4" value="4">
                        <label for="q1_4">
                            <span class="emoji">😊</span>
                            <span class="scale-label">Satisfecho</span>
                        </label>
                    </div>
                    <div class="likert-option">
                        <input type="radio" name="question_1" id="q1_5" value="5">
                        <label for="q1_5">
                            <span class="emoji">😄</span>
                            <span class="scale-label">Muy satisfecho</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Pregunta 2 -->
            <div class="question-card">
                <div class="question-title">
                    2. ¿El consultor fue claro y profesional durante el proceso?<span class="required">*</span>
                </div>
                <div class="likert-scale">
                    <div class="likert-option">
                        <input type="radio" name="question_2" id="q2_1" value="1" required>
                        <label for="q2_1">
                            <span class="emoji">😞</span>
                            <span class="scale-label">Totalmente en desacuerdo</span>
                        </label>
                    </div>
                    <div class="likert-option">
                        <input type="radio" name="question_2" id="q2_2" value="2">
                        <label for="q2_2">
                            <span class="emoji">😕</span>
                            <span class="scale-label">En desacuerdo</span>
                        </label>
                    </div>
                    <div class="likert-option">
                        <input type="radio" name="question_2" id="q2_3" value="3">
                        <label for="q2_3">
                            <span class="emoji">😐</span>
                            <span class="scale-label">Neutral</span>
                        </label>
                    </div>
                    <div class="likert-option">
                        <input type="radio" name="question_2" id="q2_4" value="4">
                        <label for="q2_4">
                            <span class="emoji">😊</span>
                            <span class="scale-label">De acuerdo</span>
                        </label>
                    </div>
                    <div class="likert-option">
                        <input type="radio" name="question_2" id="q2_5" value="5">
                        <label for="q2_5">
                            <span class="emoji">😄</span>
                            <span class="scale-label">Totalmente de acuerdo</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Pregunta 3 -->
            <div class="question-card">
                <div class="question-title">
                    3. ¿Los informes cumplen con sus expectativas?<span class="required">*</span>
                </div>
                <div class="likert-scale">
                    <div class="likert-option">
                        <input type="radio" name="question_3" id="q3_1" value="1" required>
                        <label for="q3_1">
                            <span class="emoji">😞</span>
                            <span class="scale-label">Totalmente en desacuerdo</span>
                        </label>
                    </div>
                    <div class="likert-option">
                        <input type="radio" name="question_3" id="q3_2" value="2">
                        <label for="q3_2">
                            <span class="emoji">😕</span>
                            <span class="scale-label">En desacuerdo</span>
                        </label>
                    </div>
                    <div class="likert-option">
                        <input type="radio" name="question_3" id="q3_3" value="3">
                        <label for="q3_3">
                            <span class="emoji">😐</span>
                            <span class="scale-label">Neutral</span>
                        </label>
                    </div>
                    <div class="likert-option">
                        <input type="radio" name="question_3" id="q3_4" value="4">
                        <label for="q3_4">
                            <span class="emoji">😊</span>
                            <span class="scale-label">De acuerdo</span>
                        </label>
                    </div>
                    <div class="likert-option">
                        <input type="radio" name="question_3" id="q3_5" value="5">
                        <label for="q3_5">
                            <span class="emoji">😄</span>
                            <span class="scale-label">Totalmente de acuerdo</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Pregunta 4 -->
            <div class="question-card">
                <div class="question-title">
                    4. ¿Recomendaría nuestros servicios a otras empresas?<span class="required">*</span>
                </div>
                <div class="likert-scale">
                    <div class="likert-option">
                        <input type="radio" name="question_4" id="q4_1" value="1" required>
                        <label for="q4_1">
                            <span class="emoji">😞</span>
                            <span class="scale-label">Definitivamente no</span>
                        </label>
                    </div>
                    <div class="likert-option">
                        <input type="radio" name="question_4" id="q4_2" value="2">
                        <label for="q4_2">
                            <span class="emoji">😕</span>
                            <span class="scale-label">Probablemente no</span>
                        </label>
                    </div>
                    <div class="likert-option">
                        <input type="radio" name="question_4" id="q4_3" value="3">
                        <label for="q4_3">
                            <span class="emoji">😐</span>
                            <span class="scale-label">No estoy seguro</span>
                        </label>
                    </div>
                    <div class="likert-option">
                        <input type="radio" name="question_4" id="q4_4" value="4">
                        <label for="q4_4">
                            <span class="emoji">😊</span>
                            <span class="scale-label">Probablemente sí</span>
                        </label>
                    </div>
                    <div class="likert-option">
                        <input type="radio" name="question_4" id="q4_5" value="5">
                        <label for="q4_5">
                            <span class="emoji">😄</span>
                            <span class="scale-label">Definitivamente sí</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Pregunta 5 -->
            <div class="question-card">
                <div class="question-title">
                    5. ¿Qué tan fácil fue navegar y entender los resultados?<span class="required">*</span>
                </div>
                <div class="likert-scale">
                    <div class="likert-option">
                        <input type="radio" name="question_5" id="q5_1" value="1" required>
                        <label for="q5_1">
                            <span class="emoji">😞</span>
                            <span class="scale-label">Muy difícil</span>
                        </label>
                    </div>
                    <div class="likert-option">
                        <input type="radio" name="question_5" id="q5_2" value="2">
                        <label for="q5_2">
                            <span class="emoji">😕</span>
                            <span class="scale-label">Difícil</span>
                        </label>
                    </div>
                    <div class="likert-option">
                        <input type="radio" name="question_5" id="q5_3" value="3">
                        <label for="q5_3">
                            <span class="emoji">😐</span>
                            <span class="scale-label">Neutral</span>
                        </label>
                    </div>
                    <div class="likert-option">
                        <input type="radio" name="question_5" id="q5_4" value="4">
                        <label for="q5_4">
                            <span class="emoji">😊</span>
                            <span class="scale-label">Fácil</span>
                        </label>
                    </div>
                    <div class="likert-option">
                        <input type="radio" name="question_5" id="q5_5" value="5">
                        <label for="q5_5">
                            <span class="emoji">😄</span>
                            <span class="scale-label">Muy fácil</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Comentarios -->
            <div class="question-card comments-section">
                <div class="question-title">
                    6. Comentarios o sugerencias (opcional)
                </div>
                <textarea class="form-control" name="comments" id="comments" rows="5"
                    placeholder="Comparta sus comentarios, sugerencias o cualquier otra observación que desee hacernos saber..."
                    maxlength="5000"></textarea>
                <small class="text-muted">Máximo 5000 caracteres</small>
            </div>

            <button type="submit" class="btn btn-primary btn-submit" id="submitBtn">
                <i class="bi bi-send-fill"></i> Enviar Encuesta
            </button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('satisfactionForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const submitBtn = document.getElementById('submitBtn');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enviando...';

            const formData = new FormData(this);

            try {
                const response = await fetch('<?= base_url('/satisfaction/submit/' . $service['id']) ?>', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    // Mostrar mensaje de éxito
                    alert(result.message);
                    // Redirigir a los informes
                    window.location.href = result.redirect;
                } else {
                    alert(result.message || 'Error al enviar la encuesta. Por favor intente nuevamente.');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error de conexión. Por favor intente nuevamente.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
    </script>
</body>
</html>
