/**
 * INLINE EDITING - Sistema de Auto-guardado con Verificación de Integridad
 * Para cuestionarios de Batería de Riesgo Psicosocial
 *
 * Uso:
 * 1. Incluir este archivo en la vista
 * 2. Llamar setupInlineEditing({ endpoint: 'url', formType: 'tipo' })
 */

const InlineEditing = {
    config: {
        endpoint: null,
        formType: null,
        sessionId: null,
        debugMode: false
    },

    /**
     * Inicializar el sistema de inline editing
     */
    init(options) {
        this.config = { ...this.config, ...options };
        this.config.sessionId = this.config.sessionId || this.generateSessionId();

        console.log('🔍 Inline Editing initialized:', this.config);

        // Wait for DOM to be ready before attaching listeners
        if (document.readyState === 'loading') {
            console.log('⏳ Waiting for DOM to load...');
            document.addEventListener('DOMContentLoaded', () => {
                console.log('✅ DOM loaded, attaching listeners...');
                this.attachListeners();
            });
        } else {
            console.log('✅ DOM already loaded, attaching listeners...');
            this.attachListeners();
        }
    },

    /**
     * Generar session ID único
     */
    generateSessionId() {
        return Array.from(crypto.getRandomValues(new Uint8Array(16)))
            .map(b => b.toString(16).padStart(2, '0'))
            .join('');
    },

    /**
     * Adjuntar event listeners a todos los radio buttons
     */
    attachListeners() {
        // Selector para responses[1], responses[2], etc.
        const radioButtons = document.querySelectorAll('input[type="radio"][name^="responses"]');
        console.log(`🎯 Found ${radioButtons.length} radio buttons`);

        radioButtons.forEach(radio => {
            radio.addEventListener('change', (e) => {
                if (e.target.checked) {
                    const questionNumber = this.extractQuestionNumber(e.target.name);
                    const answerValue = e.target.value;

                    console.log(`🔔 Question ${questionNumber} answered: ${answerValue}`);
                    this.saveQuestion(questionNumber, answerValue);
                }
            });
        });
    },

    /**
     * Extraer número de pregunta del name attribute
     * Formatos soportados: responses[1], q1, question1
     */
    extractQuestionNumber(name) {
        // Intentar formato responses[N]
        let match = name.match(/responses\[(\d+)\]/);
        if (match) return parseInt(match[1]);

        // Intentar formato qN
        match = name.match(/q(\d+)/);
        if (match) return parseInt(match[1]);

        // Intentar formato questionN
        match = name.match(/question(\d+)/);
        if (match) return parseInt(match[1]);

        return null;
    },

    /**
     * Guardar una pregunta individual
     */
    async saveQuestion(questionNumber, answerValue) {
        console.log(`💾 Saving Q${questionNumber} = ${answerValue}`);

        try {
            const formData = new FormData();
            formData.append('question_number', questionNumber);
            formData.append('answer_value', answerValue);
            formData.append('session_id', this.config.sessionId);

            const response = await fetch(this.config.endpoint, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            console.log(`📡 Response status: ${response.status}`);
            const result = await response.json();
            console.log('📦 Result:', result);

            if (result.success && this.config.debugMode && result.debug_enabled && result.debug_verification) {
                // Sweet Alert de verificación - COMENTADO para producción
                // Descomentar la siguiente línea para activar el debug visual:
                // await this.showDebugVerification(result.debug_verification);
                console.log('✅ Debug verification disponible (Sweet Alert desactivado)');
            } else if (result.success) {
                console.log('✅ Saved successfully (no debug)');
            } else {
                console.error('❌ Error:', result.message);
                alert('Error: ' + result.message);
            }

            return result;
        } catch (error) {
            console.error('❌ Save error:', error);
            alert('Error de conexión: ' + error.message);
            return { success: false, message: error.message };
        }
    },

    /**
     * Mostrar Sweet Alert con verificación de integridad
     */
    showDebugVerification(debugData) {
        if (!debugData || debugData.length === 0) return Promise.resolve();

        let tableHTML = '<div style="max-height: 400px; overflow-y: auto;"><table class="table table-sm table-bordered"><thead class="table-dark"><tr><th>Pregunta</th><th>Enviado</th><th>Transformado</th><th>En BD</th><th>Estado</th></tr></thead><tbody>';

        debugData.forEach(item => {
            const match = item.coincide;
            const statusIcon = match ? '✅' : '❌';
            const rowClass = match ? '' : 'table-danger';

            tableHTML += `<tr class="${rowClass}">
                <td><strong>P${item.pregunta}</strong></td>
                <td>${item.valor_enviado}</td>
                <td>${item.valor_transformado}</td>
                <td><strong>${item.valor_en_bd}</strong></td>
                <td>${statusIcon}</td>
            </tr>`;
        });

        tableHTML += '</tbody></table></div>';

        return Swal.fire({
            title: '🔍 Verificación de Integridad (DEBUG)',
            html: tableHTML,
            icon: 'info',
            width: '800px',
            confirmButtonText: 'Continuar',
            footer: '<small>Modo DEBUG activo - Los datos se verificaron leyendo desde la base de datos</small>'
        });
    }
};

// Exponer globalmente para uso en vistas
window.InlineEditing = InlineEditing;
