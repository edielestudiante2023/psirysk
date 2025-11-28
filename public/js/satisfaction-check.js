/**
 * Verificar encuesta de satisfacción antes de descargas PDF/Excel
 * Este script intercepta clics en botones de descarga y verifica si el cliente
 * ha completado la encuesta de satisfacción.
 */

document.addEventListener('DOMContentLoaded', function() {
    // Interceptar todos los botones de descarga
    const downloadButtons = document.querySelectorAll('[data-download-type]');

    downloadButtons.forEach(button => {
        button.addEventListener('click', async function(e) {
            e.preventDefault();

            const serviceId = this.getAttribute('data-service-id');
            const downloadUrl = this.getAttribute('href') || this.getAttribute('data-url');

            if (!serviceId) {
                console.error('Service ID not found in button attributes');
                return;
            }

            // Mostrar indicador de carga
            const originalText = this.innerHTML;
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Verificando...';

            try {
                // Verificar si completó la encuesta
                const baseUrl = window.location.origin + '/psyrisk';
                const fetchUrl = `${baseUrl}/reports/check-survey/${serviceId}`;
                console.log('[SATISFACTION-CHECK] Service ID:', serviceId);
                console.log('[SATISFACTION-CHECK] Download URL:', downloadUrl);
                console.log('[SATISFACTION-CHECK] Fetch URL:', fetchUrl);

                const response = await fetch(fetchUrl);
                console.log('[SATISFACTION-CHECK] Response status:', response.status);
                console.log('[SATISFACTION-CHECK] Response ok:', response.ok);

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const result = await response.json();
                console.log('[SATISFACTION-CHECK] Result:', result);

                if (result.can_download) {
                    // Permitir descarga
                    console.log('[SATISFACTION-CHECK] Download permitido, redirigiendo a:', downloadUrl);
                    if (downloadUrl) {
                        window.location.href = downloadUrl;
                    }
                } else {
                    // Mostrar mensaje y redirigir a encuesta
                    console.log('[SATISFACTION-CHECK] Encuesta requerida');
                    showSurveyModal(result.message, result.survey_url);
                }

            } catch (error) {
                console.error('[SATISFACTION-CHECK] Error completo:', error);
                console.error('[SATISFACTION-CHECK] Error stack:', error.stack);
                alert('Error al verificar permisos de descarga. Por favor intente nuevamente.');
            } finally {
                // Restaurar botón
                this.disabled = false;
                this.innerHTML = originalText;
            }
        });
    });
});

/**
 * Mostrar modal informando que debe completar la encuesta
 */
function showSurveyModal(message, surveyUrl) {
    // Crear modal dinámicamente
    const modalHtml = `
        <div class="modal fade" id="surveyRequiredModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="bi bi-star-fill me-2"></i>
                            Encuesta de Satisfacción Requerida
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center py-4">
                        <div class="mb-3">
                            <i class="bi bi-clipboard-check" style="font-size: 3rem; color: #667eea;"></i>
                        </div>
                        <p class="lead">${message}</p>
                        <p class="text-muted">
                            Solo le tomará 2 minutos completarla y podrá descargar todos sus informes.
                        </p>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                        <a href="${surveyUrl}" class="btn btn-primary">
                            <i class="bi bi-star-fill me-2"></i>
                            Completar Encuesta
                        </a>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Remover modal existente si lo hay
    const existingModal = document.getElementById('surveyRequiredModal');
    if (existingModal) {
        existingModal.remove();
    }

    // Agregar modal al body
    document.body.insertAdjacentHTML('beforeend', modalHtml);

    // Mostrar modal
    const modal = new bootstrap.Modal(document.getElementById('surveyRequiredModal'));
    modal.show();

    // Limpiar modal después de cerrar
    document.getElementById('surveyRequiredModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}
