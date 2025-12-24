<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// Ruta principal redirige al login
$routes->get('/', 'AuthController::index');

// Rutas de autenticación
$routes->get('login', 'AuthController::index');
$routes->post('login', 'AuthController::login');
$routes->get('logout', 'AuthController::logout');

// Rutas de recuperación de contraseña
$routes->get('forgot-password', 'PasswordResetController::forgotPassword');
$routes->post('password-reset/send', 'PasswordResetController::sendResetLink');
$routes->get('password-reset/(:segment)', 'PasswordResetController::resetPassword/$1');
$routes->post('password-reset/update', 'PasswordResetController::updatePassword');

// Rutas protegidas (requieren autenticación)
$routes->get('dashboard', 'DashboardController::index');

// Rutas de Usuarios (Solo Admin)
$routes->group('users', function($routes) {
    $routes->get('/', 'UserController::index');
    $routes->get('create', 'UserController::create');
    $routes->post('store', 'UserController::store');
    $routes->get('edit/(:num)', 'UserController::edit/$1');
    $routes->post('update/(:num)', 'UserController::update/$1');
    $routes->post('delete/(:num)', 'UserController::delete/$1');
});

// Rutas de Usuarios de Cliente (Para Consultores)
$routes->group('client-users', function($routes) {
    $routes->get('/', 'ClientUserController::index');
    $routes->get('create', 'ClientUserController::create');
    $routes->post('store', 'ClientUserController::store');
    $routes->get('edit/(:num)', 'ClientUserController::edit/$1');
    $routes->post('update/(:num)', 'ClientUserController::update/$1');
    $routes->post('delete/(:num)', 'ClientUserController::delete/$1');
});

// Rutas de Empresas
$routes->group('companies', function($routes) {
    $routes->get('/', 'CompanyController::index');
    $routes->get('create', 'CompanyController::create');
    $routes->post('store', 'CompanyController::store');
    $routes->get('edit/(:num)', 'CompanyController::edit/$1');
    $routes->post('update/(:num)', 'CompanyController::update/$1');
    $routes->post('delete/(:num)', 'CompanyController::delete/$1');
});

// Rutas de Consultores
$routes->group('consultants', function($routes) {
    $routes->get('/', 'ConsultantsController::index');
    $routes->get('create', 'ConsultantsController::create');
    $routes->post('store', 'ConsultantsController::store');
    $routes->get('(:num)', 'ConsultantsController::show/$1');
    $routes->get('(:num)/edit', 'ConsultantsController::edit/$1');
    $routes->post('(:num)/update', 'ConsultantsController::update/$1');
    $routes->post('(:num)/delete', 'ConsultantsController::delete/$1');
});

// Rutas de Servicios de Batería
$routes->group('battery-services', function($routes) {
    $routes->get('/', 'BatteryServiceController::index');
    $routes->get('create', 'BatteryServiceController::create');
    $routes->post('store', 'BatteryServiceController::store');
    $routes->get('edit/(:num)', 'BatteryServiceController::edit/$1');
    $routes->post('update/(:num)', 'BatteryServiceController::update/$1');
    $routes->post('delete/(:num)', 'BatteryServiceController::delete/$1');
    $routes->get('check-can-finalize/(:num)', 'BatteryServiceController::checkCanFinalize/$1'); // Verificar si puede finalizar
    $routes->get('global-gauges/(:num)', 'BatteryServiceController::globalGauges/$1'); // Gráficos globales
    $routes->get('(:num)', 'BatteryServiceController::view/$1');
});

// Rutas de Recordatorios de Batería Psicosocial
$routes->group('battery-schedules', function($routes) {
    $routes->get('/', 'BatteryScheduleController::index');
    $routes->get('create-from-service/(:num)', 'BatteryScheduleController::createFromService/$1');
    $routes->get('edit/(:num)', 'BatteryScheduleController::edit/$1');
    $routes->post('edit/(:num)', 'BatteryScheduleController::edit/$1');
    $routes->get('cancel/(:num)', 'BatteryScheduleController::cancel/$1');
    $routes->get('complete/(:num)', 'BatteryScheduleController::complete/$1');
    $routes->get('send-manual/(:num)', 'BatteryScheduleController::sendManual/$1');
});

// Rutas de Trabajadores (Workers)
$routes->get('workers', 'WorkerController::listAll'); // Lista global de trabajadores
$routes->group('workers', function($routes) {
    $routes->get('service/(:num)', 'WorkerController::index/$1');
    $routes->get('upload/(:num)', 'WorkerController::upload/$1');
    $routes->post('process-csv/(:num)', 'WorkerController::processCSV/$1');
    $routes->get('create/(:num)', 'WorkerController::create/$1'); // Formulario crear trabajador
    $routes->post('store/(:num)', 'WorkerController::store/$1'); // Guardar nuevo trabajador
    $routes->post('send-email/(:num)', 'WorkerController::sendEmail/$1'); // Enviar email individual
    $routes->post('send-bulk-emails/(:num)', 'WorkerController::sendBulkEmails/$1'); // Envío masivo
    $routes->post('update/(:num)', 'WorkerController::update/$1'); // Actualizar trabajador
    $routes->post('delete/(:num)', 'WorkerController::delete/$1'); // Eliminar trabajador
    $routes->post('mark-no-participo/(:num)', 'WorkerController::markAsNoParticipo/$1'); // Marcar como No Participó (individual)
    $routes->post('mark-all-no-participo/(:num)', 'WorkerController::markAllAsNoParticipo/$1'); // Marcar TODOS como No Participó (masivo)
    $routes->post('calculate-all-results/(:num)', 'WorkerController::calculateAllResults/$1'); // Calcular resultados masivo
    $routes->get('results/(:num)', 'WorkerController::results/$1'); // Ver resultados individuales
    $routes->get('export-responses/(:num)', 'WorkerController::exportResponses/$1'); // Exportar respuestas a Excel

    // Rutas de Gestión de Cierre de Servicio
    $routes->get('service/(:num)/pre-close', 'WorkerController::preClose/$1'); // Vista de pre-cierre
    $routes->post('update-statuses/(:num)', 'WorkerController::updateWorkerStatuses/$1'); // Actualizar estados masivamente
    $routes->post('close-service/(:num)', 'WorkerController::closeService/$1'); // Cerrar servicio definitivamente
});

// Rutas de Importación CSV - Módulo de Contingencia
$routes->group('csv-import', function($routes) {
    $routes->get('/', 'CsvImportController::index'); // Vista principal
    $routes->post('upload', 'CsvImportController::upload'); // Procesar carga
    $routes->delete('delete/(:num)', 'CsvImportController::deleteImport/$1'); // Eliminar importación
    $routes->get('download-template', 'CsvImportController::downloadTemplate'); // Descargar plantilla (legacy)
    $routes->get('download-template-forma-a', 'CsvImportController::downloadTemplateFormaA'); // Plantilla Forma A
    $routes->get('download-template-forma-b', 'CsvImportController::downloadTemplateFormaB'); // Plantilla Forma B
});

// Rutas de Evaluación (Assessment) - Acceso público con token
$routes->group('assessment', function($routes) {
    $routes->get('informed-consent', 'AssessmentController::informedConsent');
    $routes->post('accept-consent', 'AssessmentController::acceptConsent');
    $routes->get('general-data', 'AssessmentController::generalData');
    $routes->post('general-data', 'AssessmentController::saveGeneralData');
    $routes->post('save-field-general-data', 'AssessmentController::saveFieldGeneralData'); // INLINE EDITING: Auto-guardado campo por campo
    $routes->get('intralaboral', 'AssessmentController::intralaboral');
    $routes->post('intralaboral', 'AssessmentController::saveIntralaboral');
    $routes->post('save-question-intralaboral', 'AssessmentController::saveQuestionIntralaboral'); // INLINE EDITING: Auto-guardado pregunta por pregunta
    $routes->get('extralaboral', 'AssessmentController::extralaboral');
    $routes->post('extralaboral', 'AssessmentController::saveExtralaboral');
    $routes->post('save-question-extralaboral', 'AssessmentController::saveQuestionExtralaboral'); // INLINE EDITING: Auto-guardado pregunta por pregunta
    $routes->get('estres', 'AssessmentController::estres');
    $routes->post('estres', 'AssessmentController::saveEstres');
    $routes->post('save-question-estres', 'AssessmentController::saveQuestionEstres'); // INLINE EDITING: Auto-guardado pregunta por pregunta
    $routes->post('saveEstres', 'AssessmentController::saveEstres'); // Ruta explícita para el guardado
    $routes->get('completed', 'AssessmentController::completed');
    $routes->get('progress', 'AssessmentController::getProgress');
    $routes->get('invalid', 'AssessmentController::invalid');
    $routes->get('(:segment)', 'AssessmentController::index/$1'); // Token access - debe ser la última
});

// Rutas de Recomendaciones y Planes de Acción
$routes->group('recommendations', function($routes) {
    $routes->get('dimension/(:segment)', 'RecommendationsController::view/$1');
    $routes->get('dimension/(:segment)/worker/(:num)', 'RecommendationsController::view/$1/$2');
    $routes->get('service/(:num)', 'RecommendationsController::forService/$1');
});

// Rutas del Módulo Comercial (Equipo Gladiator)
$routes->group('commercial', function($routes) {
    $routes->get('/', 'CommercialController::index'); // Dashboard comercial
    $routes->get('orders', 'CommercialController::orders'); // Historial de órdenes
    $routes->get('monthly-stats', 'CommercialController::getMonthlyStatsAjax'); // API estadísticas mensuales
    $routes->get('create', 'CommercialController::create'); // Formulario nueva orden
    $routes->post('store', 'CommercialController::store'); // Guardar orden
    $routes->get('download-pdf/(:num)', 'CommercialController::downloadPdf/$1'); // Descargar PDF
});

// Rutas de Informes con Segmentadores
$routes->group('reports', function($routes) {
    // Mapa de Calor Global
    $routes->get('heatmap/(:num)', 'ReportsController::heatmap/$1');

    // Mapas de Calor Detallados por Forma
    $routes->get('intralaboral-a/(:num)', 'ReportsController::intralaboralFormaA/$1');
    $routes->get('intralaboral-b/(:num)', 'ReportsController::intralaboralFormaB/$1');
    $routes->get('extralaboral-a/(:num)', 'ReportsController::extralaboralFormaA/$1');
    $routes->get('extralaboral-b/(:num)', 'ReportsController::extralaboralFormaB/$1');
    $routes->get('estres-a/(:num)', 'ReportsController::estresFormaA/$1');
    $routes->get('estres-b/(:num)', 'ReportsController::estresFormaB/$1');

    // Dashboards Intralaboral
    $routes->get('intralaboral/(:num)', 'ReportsController::intralaboral/$1');
    $routes->get('intralaboral/executive/(:num)', 'ReportsController::intralaboralExecutive/$1');

    // Dashboards Extralaboral
    $routes->get('extralaboral/(:num)', 'ReportsController::extralaboral/$1');
    $routes->get('extralaboral/executive/(:num)', 'ReportsController::extralaboralExecutive/$1');

    // Dashboards Estrés
    $routes->get('estres/(:num)', 'ReportsController::estres/$1');
    $routes->get('estres/executive/(:num)', 'ReportsController::estresExecutive/$1');

    // Consolidación Grupal
    $routes->get('consolidacion/(:num)', 'ReportsController::consolidacion/$1');

    // Ficha de Datos Generales - Consolidación Demográfica
    $routes->get('ficha-datos-generales/(:num)', 'ReportsController::fichaDatosGenerales/$1');

    // Verificación de encuesta de satisfacción
    $routes->get('check-survey/(:num)', 'ReportsController::checkSurveyCompletion/$1');

    // Exportaciones
    $routes->get('export-excel/(:num)/(:alpha)', 'ReportsController::exportExcel/$1/$2');
    $routes->get('export-pdf/(:num)/(:alpha)', 'ReportsController::exportPDF/$1/$2');
    $routes->get('export-executive-pdf/(:num)/(:alpha)', 'ReportsController::exportExecutivePDF/$1/$2');
});

// Rutas de Encuesta de Satisfacción
$routes->group('satisfaction', function($routes) {
    $routes->get('dashboard', 'SatisfactionController::dashboard'); // Dashboard de análisis (admin/comercial)
    $routes->get('survey/(:num)', 'SatisfactionController::index/$1'); // Formulario para cliente
    $routes->post('submit/(:num)', 'SatisfactionController::submit/$1'); // Envío de encuesta
    $routes->get('view/(:num)', 'SatisfactionController::view/$1'); // Ver detalle (admin/consultor/comercial)
});

// ============================================
// RUTAS PARA CLIENTES (cliente_empresa y cliente_gestor)
// Vista restringida de servicios y trabajadores
// ============================================
$routes->group('client', function($routes) {
    // Vista de detalle del servicio para clientes
    $routes->get('battery-services/(:num)', 'BatteryServiceController::viewClient/$1');

    // Vista de trabajadores para clientes (solo ver resultados)
    $routes->get('workers/service/(:num)', 'WorkerController::indexClient/$1');
});

// Rutas de Secciones de Informe con IA
$routes->group('report-sections', function($routes) {
    $routes->get('(:num)', 'ReportSectionsController::index/$1'); // Vista principal
    $routes->post('generate/(:num)', 'ReportSectionsController::generate/$1'); // Generar estructura de secciones
    $routes->post('generate-ai/(:num)', 'ReportSectionsController::generateAI/$1'); // Generar texto IA para una sección
    $routes->get('generate-all-ai/(:num)', 'ReportSectionsController::generateAllAI/$1'); // Obtener secciones pendientes de IA
    $routes->get('edit/(:num)', 'ReportSectionsController::edit/$1'); // Editar sección
    $routes->post('save-comment/(:num)', 'ReportSectionsController::saveComment/$1'); // Guardar comentario
    $routes->post('save-prompt/(:num)', 'ReportSectionsController::saveConsultantPrompt/$1'); // Guardar prompt consultor
    $routes->post('approve/(:num)', 'ReportSectionsController::approve/$1'); // Aprobar sección
    $routes->post('unapprove/(:num)', 'ReportSectionsController::unapprove/$1'); // Desaprobar sección
    $routes->post('reset/(:num)', 'ReportSectionsController::resetSection/$1'); // Resetear para regenerar IA
    $routes->post('approve-all/(:num)', 'ReportSectionsController::approveAll/$1'); // Aprobar todas
    $routes->get('review/(:num)/(:alpha)', 'ReportSectionsController::review/$1/$2'); // Revisar por nivel
    $routes->get('api/sections/(:num)', 'ReportSectionsController::getSections/$1'); // API: obtener secciones
});

// Rutas de Ficha de Datos Generales con IA (Módulo Independiente)
$routes->group('demographics-report', function($routes) {
    $routes->get('(:num)', 'DemographicsReportController::index/$1'); // Vista principal
    $routes->post('generate/(:num)', 'DemographicsReportController::generate/$1'); // Generar interpretación IA
    $routes->post('save-sections/(:num)', 'DemographicsReportController::saveSections/$1'); // Guardar secciones (botón Guardar)
    $routes->get('data/(:num)', 'DemographicsReportController::getData/$1'); // Obtener datos agregados
    $routes->get('interpretation/(:num)', 'DemographicsReportController::getInterpretation/$1'); // Obtener interpretación guardada
    $routes->get('history/(:num)', 'DemographicsReportController::getHistory/$1'); // Historial de interpretaciones
    $routes->post('save-comment/(:num)', 'DemographicsReportController::saveComment/$1'); // Guardar comentario consultor (síntesis)
    $routes->get('comment/(:num)', 'DemographicsReportController::getComment/$1'); // Obtener comentario
    $routes->post('clear/(:num)', 'DemographicsReportController::clearInterpretation/$1'); // Limpiar interpretación
    // Rutas para contexto IA por sección
    $routes->post('save-prompt/(:num)/(:segment)', 'DemographicsReportController::savePrompt/$1/$2'); // Guardar prompt de contexto
    $routes->get('get-prompt/(:num)/(:segment)', 'DemographicsReportController::getPrompt/$1/$2'); // Obtener prompt guardado
    $routes->post('reset-section/(:num)/(:segment)', 'DemographicsReportController::resetSection/$1/$2'); // Resetear sección
    $routes->post('regenerate-section/(:num)/(:segment)', 'DemographicsReportController::regenerateSection/$1/$2'); // Regenerar sección con IA
});

// Rutas de Prueba para Generación de PDF (PROVISIONAL - ELIMINAR EN PRODUCCIÓN)
$routes->group('test-pdf', function($routes) {
    $routes->get('/', 'TestPdfController::index'); // Índice de pruebas
    $routes->get('demographics/(:num)', 'TestPdfController::testDemographics/$1'); // Prueba demographics_interpretations
    $routes->get('report-sections/(:num)', 'TestPdfController::testReportSections/$1'); // Prueba report_sections
    $routes->get('download-demographics/(:num)', 'TestPdfController::downloadDemographicsPdf/$1'); // Descargar PDF demographics
    $routes->get('download-report-sections/(:num)', 'TestPdfController::downloadReportSectionsPdf/$1'); // Descargar PDF report_sections
});

// ============================================
// RUTAS DE PDF EJECUTIVO (Módulo DomPDF Nativo)
// ============================================
$routes->group('pdfejecutivo', ['namespace' => 'App\Controllers\PdfEjecutivo'], function($routes) {

    // Preview de secciones individuales
    $routes->get('preview/portada/(:num)', 'PortadaController::preview/$1');
    $routes->get('preview/contenido/(:num)', 'ContenidoController::preview/$1');
    $routes->get('preview/introduccion/(:num)', 'IntroduccionController::preview/$1');
    $routes->get('preview/sociodemograficos/(:num)', 'SociodemograficosController::preview/$1');
    $routes->get('preview/mapas-calor/(:num)', 'MapasCalorController::preview/$1');
    $routes->get('preview/totales-intralaborales/(:num)', 'TotalesIntralaboralesController::preview/$1');
    $routes->get('preview/dominios-intralaborales/(:num)', 'DominiosIntralaboralesController::preview/$1');
    $routes->get('preview/dimensiones-intralaborales/(:num)', 'DimensionesIntralaboralesController::preview/$1');
    $routes->get('preview/dimensiones-extralaborales/(:num)', 'DimensionesExtralaboralesController::preview/$1');
    $routes->get('preview/estres-ejecutivo/(:num)', 'EstresEjecutivoController::preview/$1');
    $routes->get('preview/intralaboral-total/(:num)', 'IntralaboralTotalController::preview/$1');
    $routes->get('preview/intralaboral-dominios/(:num)', 'IntralaboralDominiosController::preview/$1');
    $routes->get('preview/intralaboral-dimensiones/(:num)', 'IntralaboralDimensionesController::preview/$1');
    $routes->get('preview/extralaboral-total/(:num)', 'ExtralaboralTotalController::preview/$1');
    $routes->get('preview/extralaboral-dimensiones/(:num)', 'ExtralaboralDimensionesController::preview/$1');
    $routes->get('preview/estres-total/(:num)', 'EstresTotalController::preview/$1');
    $routes->get('preview/estres/(:num)', 'EstresController::preview/$1');
    $routes->get('preview/recomendaciones-planes/(:num)', 'RecomendacionesPlanesController::preview/$1');
    $routes->get('preview/firma/(:num)', 'FirmaController::preview/$1');

    // Preview completo (Informe de Batería)
    $routes->get('preview/completo/(:num)', 'PdfEjecutivoOrchestrator::preview/$1');

    // Preview Informe Ejecutivo (Portada + Mapas + Recomendaciones)
    $routes->get('preview/ejecutivo/(:num)', 'InformeEjecutivoOrchestrator::preview/$1');

    // Casos Blanco de Intervención (SOLO CONSULTOR - Confidencial)
    $routes->get('preview/casos-intervencion/(:num)', 'CasosIntervencionController::preview/$1');

    // Descargas PDF
    $routes->get('download/portada/(:num)', 'PortadaController::download/$1');
    $routes->get('download/contenido/(:num)', 'ContenidoController::download/$1');
    $routes->get('download/introduccion/(:num)', 'IntroduccionController::download/$1');
    $routes->get('download/sociodemograficos/(:num)', 'SociodemograficosController::download/$1');
    $routes->get('download/mapas-calor/(:num)', 'MapasCalorController::download/$1');
    $routes->get('download/totales-intralaborales/(:num)', 'TotalesIntralaboralesController::download/$1');
    $routes->get('download/dominios-intralaborales/(:num)', 'DominiosIntralaboralesController::download/$1');
    $routes->get('download/dimensiones-intralaborales/(:num)', 'DimensionesIntralaboralesController::download/$1');
    $routes->get('download/dimensiones-extralaborales/(:num)', 'DimensionesExtralaboralesController::download/$1');
    $routes->get('download/estres-ejecutivo/(:num)', 'EstresEjecutivoController::download/$1');
    $routes->get('download/recomendaciones-planes/(:num)', 'RecomendacionesPlanesController::download/$1');

    // Descarga Informe de Batería completo
    $routes->get('download/(:num)', 'PdfEjecutivoOrchestrator::download/$1');

    // Descarga Informe Ejecutivo (Portada + Mapas + Recomendaciones)
    $routes->get('download/ejecutivo/(:num)', 'InformeEjecutivoOrchestrator::download/$1');

    // Descarga Casos Blanco de Intervención (SOLO CONSULTOR - Confidencial)
    $routes->get('download/casos-intervencion/(:num)', 'CasosIntervencionController::download/$1');
});

// ============================================
// RUTAS DE CONCLUSIÓN TOTAL RPS (MÁXIMO RIESGO)
// Módulo IA para análisis de máximos riesgos
// ============================================
$routes->group('max-risk', function($routes) {
    $routes->get('(:num)', 'MaxRiskController::index/$1');                    // Vista principal
    $routes->get('(:num)/recalculate', 'MaxRiskController::recalculate/$1');  // Recalcular resultados
    $routes->post('save-prompt', 'MaxRiskController::savePrompt');            // Guardar contexto IA global (AJAX)
    $routes->post('save-conclusion', 'MaxRiskController::saveConclusion');    // Guardar conclusión editada (AJAX)
    $routes->post('generate-conclusion', 'MaxRiskController::generateGlobalConclusion'); // Generar conclusión IA (AJAX)
});

// Ruta temporal para calcular y almacenar máximos riesgos (legacy/dev)
$routes->get('dev/calculate-max-risk/(:num)', 'ReportsController::calculateMaxRisk/$1');
