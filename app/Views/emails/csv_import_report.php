<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informe de Importación CSV</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 800px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .email-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            padding: 30px 20px;
            text-align: center;
        }
        .email-header h1 {
            margin: 0 0 10px 0;
            font-size: 28px;
            font-weight: 600;
        }
        .email-header p {
            margin: 0;
            font-size: 16px;
            opacity: 0.9;
        }
        .email-body {
            padding: 30px 20px;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #333;
        }
        .summary-box {
            background-color: #f8f9fa;
            border-left: 4px solid <?= $importData['has_errors'] ? '#ffc107' : '#28a745' ?>;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .summary-box h2 {
            margin: 0 0 15px 0;
            font-size: 20px;
            color: #495057;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        .stat-item {
            background: #fff;
            padding: 15px;
            border-radius: 6px;
            border: 1px solid #e9ecef;
            text-align: center;
        }
        .stat-number {
            font-size: 32px;
            font-weight: 700;
            margin: 5px 0;
        }
        .stat-number.success { color: #28a745; }
        .stat-number.warning { color: #ffc107; }
        .stat-number.info { color: #17a2b8; }
        .stat-label {
            font-size: 14px;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .section {
            margin: 30px 0;
        }
        .section-title {
            font-size: 20px;
            font-weight: 600;
            color: #495057;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
        }
        .error-category {
            margin: 20px 0;
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            overflow: hidden;
        }
        .error-category-header {
            background-color: #f8f9fa;
            padding: 12px 15px;
            font-weight: 600;
            color: #495057;
            border-bottom: 1px solid #dee2e6;
        }
        .error-category-header .count {
            float: right;
            background-color: #dc3545;
            color: #fff;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 14px;
        }
        .error-list {
            max-height: 300px;
            overflow-y: auto;
        }
        .error-item {
            padding: 12px 15px;
            border-bottom: 1px solid #f1f3f5;
        }
        .error-item:last-child {
            border-bottom: none;
        }
        .error-item:hover {
            background-color: #f8f9fa;
        }
        .error-row {
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 4px;
        }
        .error-doc {
            font-weight: 600;
            color: #212529;
        }
        .error-msg {
            color: #dc3545;
            font-size: 14px;
            margin-top: 4px;
        }
        .success-sample {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
        }
        .success-sample p {
            margin: 5px 0;
            color: #155724;
        }
        .alert-info {
            background-color: #d1ecf1;
            border-left: 4px solid #17a2b8;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            color: #0c5460;
        }
        .alert-success {
            background-color: #d4edda;
            border-left: 4px solid #28a745;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            color: #155724;
        }
        .email-footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
            border-top: 1px solid #dee2e6;
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge-success { background-color: #28a745; color: #fff; }
        .badge-warning { background-color: #ffc107; color: #212529; }
        @media (max-width: 600px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <h1>📊 Informe de Importación CSV</h1>
            <p><?= esc($importData['service_name']) ?></p>
        </div>

        <!-- Body -->
        <div class="email-body">
            <div class="greeting">
                Hola <strong><?= esc($consultantName) ?></strong>,
            </div>

            <p>Se ha completado la importación del archivo CSV. A continuación encontrarás el informe detallado:</p>

            <!-- Resumen -->
            <div class="summary-box">
                <h2>
                    <?php if ($importData['has_errors']): ?>
                        ⚠️ Importación Completada con Alertas
                    <?php else: ?>
                        ✅ Importación Completada Exitosamente
                    <?php endif; ?>
                </h2>

                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-number info"><?= $importData['total_processed'] ?></div>
                        <div class="stat-label">Total Procesados</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number success"><?= $importData['total_success'] ?></div>
                        <div class="stat-label">Exitosos</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number warning"><?= $importData['total_failed'] ?></div>
                        <div class="stat-label">No Procesados</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number info"><?= $importData['success_percentage'] ?>%</div>
                        <div class="stat-label">Tasa de Éxito</div>
                    </div>
                </div>
            </div>

            <?php if ($importData['has_errors']): ?>
                <!-- Errores Detallados -->
                <div class="section">
                    <h3 class="section-title">❌ Registros No Procesados - Detalle</h3>

                    <?php
                    $errorTypes = [
                        'worker_not_found' => [
                            'title' => 'Trabajadores No Encontrados',
                            'icon' => '👤',
                            'description' => 'Los siguientes documentos no existen en el servicio. Verifica que los trabajadores estén creados antes de importar sus respuestas.'
                        ],
                        'invalid_value' => [
                            'title' => 'Valores Inválidos',
                            'icon' => '⚠️',
                            'description' => 'Valores de respuesta que no coinciden con las opciones esperadas.'
                        ],
                        'missing_field' => [
                            'title' => 'Campos Faltantes',
                            'icon' => '📝',
                            'description' => 'Registros con campos obligatorios vacíos o faltantes.'
                        ],
                        'other' => [
                            'title' => 'Otros Errores',
                            'icon' => '❗',
                            'description' => 'Otros errores encontrados durante la importación.'
                        ]
                    ];

                    foreach ($errorTypes as $type => $config):
                        if (!empty($importData['errors_by_type'][$type])):
                    ?>
                        <div class="error-category">
                            <div class="error-category-header">
                                <?= $config['icon'] ?> <?= $config['title'] ?>
                                <span class="count"><?= count($importData['errors_by_type'][$type]) ?></span>
                            </div>
                            <div class="alert-info" style="margin: 15px; border-left: none;">
                                <?= $config['description'] ?>
                            </div>
                            <div class="error-list">
                                <?php foreach ($importData['errors_by_type'][$type] as $error): ?>
                                    <div class="error-item">
                                        <div class="error-row">
                                            Fila #<?= $error['row'] ?>
                                        </div>
                                        <div>
                                            <span class="error-doc">Doc: <?= esc($error['document']) ?></span>
                                            <?php if (!empty($error['name'])): ?>
                                                - <?= esc($error['name']) ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="error-msg">
                                            <?= esc($error['error']) ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php
                        endif;
                    endforeach;
                    ?>
                </div>
            <?php else: ?>
                <div class="alert-success">
                    <strong>🎉 ¡Excelente!</strong> Todos los registros se importaron correctamente sin errores.
                </div>
            <?php endif; ?>

            <!-- Muestra de Registros Exitosos -->
            <?php if ($importData['total_success'] > 0): ?>
                <div class="section">
                    <h3 class="section-title">✅ Registros Importados Exitosamente</h3>
                    <p>Se importaron correctamente <strong><?= $importData['total_success'] ?></strong> trabajadores.</p>

                    <?php if (!empty($importData['success_details'])): ?>
                        <div class="success-sample">
                            <p><strong>Muestra de registros exitosos (primeros 5):</strong></p>
                            <?php
                            $sample = array_slice($importData['success_details'], 0, 5);
                            foreach ($sample as $success):
                            ?>
                                <p>• Fila #<?= $success['row'] ?> - Doc: <?= esc($success['document']) ?> - <?= esc($success['name']) ?></p>
                            <?php endforeach; ?>

                            <?php if (count($importData['success_details']) > 5): ?>
                                <p style="margin-top: 10px; font-style: italic;">
                                    ... y <?= count($importData['success_details']) - 5 ?> más
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Recomendaciones -->
            <?php if ($importData['has_errors']): ?>
                <div class="section">
                    <h3 class="section-title">💡 Recomendaciones</h3>
                    <ul style="color: #495057; line-height: 1.8;">
                        <?php if (!empty($importData['errors_by_type']['worker_not_found'])): ?>
                            <li><strong>Trabajadores no encontrados:</strong> Crea los trabajadores faltantes en el sistema antes de volver a importar sus respuestas.</li>
                        <?php endif; ?>
                        <?php if (!empty($importData['errors_by_type']['invalid_value'])): ?>
                            <li><strong>Valores inválidos:</strong> Verifica que las respuestas en el CSV coincidan con las opciones válidas (Siempre, Casi siempre, Algunas veces, Casi nunca, Nunca).</li>
                        <?php endif; ?>
                        <?php if (!empty($importData['errors_by_type']['missing_field'])): ?>
                            <li><strong>Campos faltantes:</strong> Asegúrate de que todas las columnas obligatorias (documento, nombre) estén presentes y con datos.</li>
                        <?php endif; ?>
                        <li>Puedes corregir estos errores y volver a importar solo los registros que fallaron.</li>
                        <li>Los registros exitosos ya están guardados en el sistema y no necesitan ser re-importados.</li>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="alert-info">
                <strong>ℹ️ Nota:</strong> Este informe se generó automáticamente el <?= date('d/m/Y \a \l\a\s H:i') ?>.
                ID de Importación: #<?= $importData['import_id'] ?>
            </div>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <p><strong>PsyRisk</strong> - Sistema de Evaluación de Riesgos Psicosociales</p>
            <p style="margin: 5px 0; font-size: 12px;">
                Este es un correo automático, por favor no respondas a esta dirección.
            </p>
        </div>
    </div>
</body>
</html>
