<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Inline Editing</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }
        select {
            width: 100%;
            padding: 10px;
            font-size: 16px;
        }
        .info {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="info">
        <h2>Test: Auto-guardado con Verificación</h2>
        <p>Cuando selecciones un valor del dropdown, se guardará automáticamente y verás un Sweet Alert con la verificación de la base de datos.</p>
    </div>

    <div class="form-group">
        <label>Selecciona el sexo:</label>
        <select id="genderSelect">
            <option value="">Seleccione...</option>
            <option value="Masculino">Masculino</option>
            <option value="Femenino">Femenino</option>
        </select>
    </div>

    <script>
        // Simular sesión de worker 16
        const DEBUG_MODE = true;

        async function saveField(fieldName, fieldValue) {
            try {
                const formData = new FormData();
                formData.append('field_name', fieldName);
                formData.append('field_value', fieldValue);

                console.log('Guardando:', fieldName, '=', fieldValue);

                const response = await fetch('http://localhost/psyrisk/assessment/save-field-general-data', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                console.log('Resultado:', result);

                if (result.success && DEBUG_MODE && result.debug_enabled && result.debug_verification) {
                    await showDebugVerification(result.debug_verification);
                } else if (result.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Guardado',
                        text: 'Campo guardado exitosamente',
                        timer: 1500
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: result.message
                    });
                }

                return result;
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: error.message
                });
            }
        }

        function showDebugVerification(debugData) {
            if (!debugData || debugData.length === 0) return;

            let tableHTML = '<div style="max-height: 400px; overflow-y: auto;"><table style="width: 100%; border-collapse: collapse;"><thead style="background: #333; color: white;"><tr><th style="padding: 10px; border: 1px solid #ddd;">Campo</th><th style="padding: 10px; border: 1px solid #ddd;">Enviado</th><th style="padding: 10px; border: 1px solid #ddd;">En BD</th><th style="padding: 10px; border: 1px solid #ddd;">Estado</th></tr></thead><tbody>';

            debugData.forEach(item => {
                const match = item.coincide;
                const statusIcon = match ? '✅' : '❌';
                const rowStyle = match ? '' : 'background: #ffebee;';
                const fieldName = item.campo.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());

                tableHTML += `<tr style="${rowStyle}">
                    <td style="padding: 10px; border: 1px solid #ddd;"><strong>${fieldName}</strong></td>
                    <td style="padding: 10px; border: 1px solid #ddd;">${item.valor_enviado || '<em>vacío</em>'}</td>
                    <td style="padding: 10px; border: 1px solid #ddd;"><strong>${item.valor_en_bd || '<em>vacío</em>'}</strong></td>
                    <td style="padding: 10px; border: 1px solid #ddd;">${statusIcon}</td>
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

        document.getElementById('genderSelect').addEventListener('change', function() {
            const fieldValue = this.value;
            if (fieldValue) {
                saveField('gender', fieldValue);
            }
        });
    </script>
</body>
</html>
