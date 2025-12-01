<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ficha de Datos Generales - PsyRisk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 1rem 0;
        }
        .assessment-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        .form-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        /* Optimizaciones para móviles */
        @media (max-width: 768px) {
            body {
                padding: 0.5rem 0;
            }
            .assessment-container {
                padding: 0 0.5rem;
            }
            .form-card {
                border-radius: 15px;
                padding: 1rem;
                margin-bottom: 1rem;
            }
            .form-card h2 {
                font-size: 1.5rem;
            }
            .form-label {
                font-size: 0.95rem;
                font-weight: 500;
            }
            .form-control, .form-select {
                font-size: 16px !important; /* Evita zoom en iOS */
                min-height: 44px; /* Tamaño táctil recomendado */
            }
            .btn {
                min-height: 48px;
                font-size: 1.1rem;
            }
            .progress-bar-custom {
                height: 25px;
                margin-bottom: 1rem;
            }
            .form-section {
                padding: 1rem;
                margin-bottom: 1.5rem;
            }
            .form-check {
                padding: 10px;
                margin-bottom: 0.5rem;
            }
            .form-check-label {
                font-size: 14px;
            }
        }

        /* Mejoras táctiles */
        .form-check-input[type="radio"] {
            min-width: 20px;
            min-height: 20px;
        }

        /* Select2 responsive */
        @media (max-width: 768px) {
            .select2-container .select2-selection--single {
                min-height: 44px !important;
            }
            .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
                line-height: 42px !important;
                font-size: 16px !important;
            }
        }
        .progress-bar-custom {
            height: 30px;
            border-radius: 15px;
            background: #e9ecef;
            overflow: hidden;
            margin-bottom: 2rem;
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
        .form-section {
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: #f8f9fa;
            border-radius: 10px;
        }
        .form-section h5 {
            color: #667eea;
            margin-bottom: 1rem;
            font-weight: 600;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        /* Estilos mejorados para radio buttons */
        .form-check-input[type="radio"] {
            width: 22px !important;
            height: 22px !important;
            border: 2px solid #667eea !important;
            cursor: pointer !important;
            margin-top: 0 !important;
            flex-shrink: 0;
        }

        .form-check-input[type="radio"]:checked {
            background-color: #667eea !important;
            border-color: #667eea !important;
        }

        .form-check-input[type="radio"]:hover {
            border-color: #764ba2 !important;
            box-shadow: 0 0 5px rgba(102, 126, 234, 0.5) !important;
        }

        .form-check-input[type="radio"]:focus {
            border-color: #667eea !important;
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25) !important;
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            background: white;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            transition: all 0.2s;
        }

        .form-check:hover {
            border-color: #667eea;
            background: #f0f3ff;
        }

        .form-check-label {
            cursor: pointer !important;
            margin-bottom: 0 !important;
            font-size: 15px;
            font-weight: 500;
            color: #333;
        }

        /* Estilos personalizados para Select2 */
        .select2-container--bootstrap-5 .select2-selection {
            min-height: 38px !important;
            border: 1px solid #ced4da !important;
            border-radius: 0.375rem !important;
        }

        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            line-height: 36px !important;
            padding-left: 12px !important;
        }

        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
        }

        .select2-container--bootstrap-5.select2-container--focus .select2-selection,
        .select2-container--bootstrap-5.select2-container--open .select2-selection {
            border-color: #667eea !important;
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25) !important;
        }

        .select2-dropdown {
            border: 1px solid #667eea !important;
            border-radius: 0.375rem !important;
        }

        .select2-container--bootstrap-5 .select2-results__option--highlighted {
            background-color: #667eea !important;
        }
    </style>
</head>
<body>
    <div class="assessment-container">
        <!-- Header -->
        <div class="form-card">
            <div class="text-center mb-4">
                <h2><i class="fas fa-clipboard-list me-2"></i>Ficha de Datos Generales</h2>
                <p class="text-muted">Batería de Riesgo Psicosocial</p>
            </div>

            <!-- Progress Bar -->
            <div class="progress-bar-custom">
                <div class="progress-fill" style="width: 0%">
                    <span>Paso 1 de 4</span>
                </div>
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Bienvenido(a) <?= esc($worker['name']) ?></strong><br>
                Por favor completa los siguientes datos. Esta información es confidencial y será utilizada únicamente para fines estadísticos del estudio.
            </div>
        </div>

        <!-- Form -->
        <form id="generalDataForm">
            <!-- Datos Demográficos -->
            <div class="form-card">
                <div class="form-section">
                    <h5><i class="fas fa-user me-2"></i>Información Demográfica</h5>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sexo *</label>
                            <select class="form-select" name="gender" required>
                                <option value="">Seleccione...</option>
                                <option value="Masculino" <?= isset($demographics['gender']) && $demographics['gender'] === 'Masculino' ? 'selected' : '' ?>>Masculino</option>
                                <option value="Femenino" <?= isset($demographics['gender']) && $demographics['gender'] === 'Femenino' ? 'selected' : '' ?>>Femenino</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Año de Nacimiento *</label>
                            <input type="number" class="form-control" name="birth_year"
                                   min="1940" max="<?= date('Y') ?>"
                                   value="<?= $demographics['birth_year'] ?? '' ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Estado Civil *</label>
                            <select class="form-select" name="marital_status" required>
                                <option value="">Seleccione...</option>
                                <option value="Soltero(a)" <?= isset($demographics['marital_status']) && $demographics['marital_status'] === 'Soltero(a)' ? 'selected' : '' ?>>Soltero(a)</option>
                                <option value="Casado(a)" <?= isset($demographics['marital_status']) && $demographics['marital_status'] === 'Casado(a)' ? 'selected' : '' ?>>Casado(a)</option>
                                <option value="Union_libre" <?= isset($demographics['marital_status']) && $demographics['marital_status'] === 'Union_libre' ? 'selected' : '' ?>>Unión Libre</option>
                                <option value="Separado(a)" <?= isset($demographics['marital_status']) && $demographics['marital_status'] === 'Separado(a)' ? 'selected' : '' ?>>Separado(a)</option>
                                <option value="Divorciado(a)" <?= isset($demographics['marital_status']) && $demographics['marital_status'] === 'Divorciado(a)' ? 'selected' : '' ?>>Divorciado(a)</option>
                                <option value="Viudo(a)" <?= isset($demographics['marital_status']) && $demographics['marital_status'] === 'Viudo(a)' ? 'selected' : '' ?>>Viudo(a)</option>
                                <option value="Religioso(a)" <?= isset($demographics['marital_status']) && $demographics['marital_status'] === 'Religioso(a)' ? 'selected' : '' ?>>Sacerdote / Monja / Religioso(a)</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Último nivel de estudios que alcanzó (marque una sola opción) *</label>
                            <select class="form-select" name="education_level" required>
                                <option value="">Seleccione...</option>
                                <option value="Ninguno" <?= isset($demographics['education_level']) && $demographics['education_level'] === 'Ninguno' ? 'selected' : '' ?>>Ninguno</option>
                                <option value="Primaria_incompleta" <?= isset($demographics['education_level']) && $demographics['education_level'] === 'Primaria_incompleta' ? 'selected' : '' ?>>Primaria incompleta</option>
                                <option value="Primaria_completa" <?= isset($demographics['education_level']) && $demographics['education_level'] === 'Primaria_completa' ? 'selected' : '' ?>>Primaria completa</option>
                                <option value="Bachillerato_incompleto" <?= isset($demographics['education_level']) && $demographics['education_level'] === 'Bachillerato_incompleto' ? 'selected' : '' ?>>Bachillerato incompleto</option>
                                <option value="Bachillerato_completo" <?= isset($demographics['education_level']) && $demographics['education_level'] === 'Bachillerato_completo' ? 'selected' : '' ?>>Bachillerato completo</option>
                                <option value="Tecnico_incompleto" <?= isset($demographics['education_level']) && $demographics['education_level'] === 'Tecnico_incompleto' ? 'selected' : '' ?>>Técnico / tecnológico incompleto</option>
                                <option value="Tecnico_completo" <?= isset($demographics['education_level']) && $demographics['education_level'] === 'Tecnico_completo' ? 'selected' : '' ?>>Técnico / tecnológico completo</option>
                                <option value="Tecnologo_incompleto" <?= isset($demographics['education_level']) && $demographics['education_level'] === 'Tecnologo_incompleto' ? 'selected' : '' ?>>Tecnólogo incompleto</option>
                                <option value="Tecnologo_completo" <?= isset($demographics['education_level']) && $demographics['education_level'] === 'Tecnologo_completo' ? 'selected' : '' ?>>Tecnólogo completo</option>
                                <option value="Profesional_incompleto" <?= isset($demographics['education_level']) && $demographics['education_level'] === 'Profesional_incompleto' ? 'selected' : '' ?>>Profesional incompleto</option>
                                <option value="Profesional_completo" <?= isset($demographics['education_level']) && $demographics['education_level'] === 'Profesional_completo' ? 'selected' : '' ?>>Profesional completo</option>
                                <option value="Postgrado_incompleto" <?= isset($demographics['education_level']) && $demographics['education_level'] === 'Postgrado_incompleto' ? 'selected' : '' ?>>Postgrado incompleto</option>
                                <option value="Postgrado_completo" <?= isset($demographics['education_level']) && $demographics['education_level'] === 'Postgrado_completo' ? 'selected' : '' ?>>Postgrado completo</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">¿Cuál es su ocupación o profesión? *</label>
                        <input type="text" class="form-control" name="occupation"
                               placeholder="Escriba su ocupación o profesión"
                               value="<?= $demographics['occupation'] ?? '' ?>" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Departamento de residencia: *</label>
                            <select class="form-select" id="department-residence-select" name="department_residence" required>
                                <option value="">Seleccione departamento...</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ciudad / Municipio de residencia: *</label>
                            <select class="form-select" id="city-residence-select" name="city_residence" required disabled>
                                <option value="">Primero seleccione un departamento...</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Seleccione y marque el estrato de los servicios públicos de su vivienda *</label>
                        <div class="d-flex flex-wrap gap-2">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="stratum" value="1" id="stratum1"
                                       <?= isset($demographics['stratum']) && $demographics['stratum'] == '1' ? 'checked' : '' ?> required>
                                <label class="form-check-label" for="stratum1">1</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="stratum" value="2" id="stratum2"
                                       <?= isset($demographics['stratum']) && $demographics['stratum'] == '2' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="stratum2">2</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="stratum" value="3" id="stratum3"
                                       <?= isset($demographics['stratum']) && $demographics['stratum'] == '3' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="stratum3">3</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="stratum" value="4" id="stratum4"
                                       <?= isset($demographics['stratum']) && $demographics['stratum'] == '4' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="stratum4">4</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="stratum" value="5" id="stratum5"
                                       <?= isset($demographics['stratum']) && $demographics['stratum'] == '5' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="stratum5">5</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="stratum" value="6" id="stratum6"
                                       <?= isset($demographics['stratum']) && $demographics['stratum'] == '6' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="stratum6">6</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="stratum" value="7" id="stratumFinca"
                                       <?= isset($demographics['stratum']) && $demographics['stratum'] == '7' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="stratumFinca">Finca</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="stratum" value="0" id="stratumNoSe"
                                       <?= isset($demographics['stratum']) && ($demographics['stratum'] == '0' || $demographics['stratum'] === null) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="stratumNoSe">No sé</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tipo de vivienda *</label>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="housing_type" value="Propia" id="housingPropia"
                                           <?= isset($demographics['housing_type']) && $demographics['housing_type'] === 'Propia' ? 'checked' : '' ?> required>
                                    <label class="form-check-label" for="housingPropia">Propia</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="housing_type" value="Arrendada" id="housingArriendo"
                                           <?= isset($demographics['housing_type']) && $demographics['housing_type'] === 'Arrendada' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="housingArriendo">En arriendo</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="housing_type" value="Familiar" id="housingFamiliar"
                                           <?= isset($demographics['housing_type']) && $demographics['housing_type'] === 'Familiar' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="housingFamiliar">Familiar</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Número de personas que dependen económicamente de usted (aunque vivan en otro lugar) *</label>
                        <input type="number" class="form-control" name="dependents"
                               min="0" max="20"
                               placeholder="Ingrese el número de personas"
                               value="<?= $demographics['dependents'] ?? '0' ?>" required>
                    </div>
                </div>

                <!-- Datos Ocupacionales -->
                <div class="form-section">
                    <h5><i class="fas fa-briefcase me-2"></i>Información Ocupacional</h5>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Departamento donde trabaja: *</label>
                            <select class="form-select" id="department-work-select" name="department_work" required>
                                <option value="">Seleccione departamento...</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ciudad / Municipio donde trabaja: *</label>
                            <select class="form-select" id="city-work-select" name="city_work" required disabled>
                                <option value="">Primero seleccione un departamento...</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">¿Hace cuántos años que trabaja en esta empresa? *</label>
                        <div class="d-flex gap-3 align-items-center">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="time_in_company_type" value="less_than_year" id="timeLess"
                                       <?= isset($demographics['time_in_company_type']) && $demographics['time_in_company_type'] === 'less_than_year' ? 'checked' : '' ?> required>
                                <label class="form-check-label" for="timeLess">Si lleva menos de un año marque esta opción</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="time_in_company_type" value="more_than_year" id="timeMore"
                                       <?= isset($demographics['time_in_company_type']) && $demographics['time_in_company_type'] === 'more_than_year' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="timeMore">Si lleva más de un año, anote cuántos años</label>
                            </div>
                            <input type="number" class="form-control" name="time_in_company_years" style="max-width: 100px;"
                                   min="0" max="60"
                                   value="<?= $demographics['time_in_company_years'] ?? '' ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">¿Cuál es el nombre del cargo que ocupa en la empresa? *</label>
                        <select class="form-select" id="position-select" name="position_name" required>
                            <option value="">Seleccione...</option>
                            <?php
                            $selectedPosition = $demographics['position_name'] ?? $worker['position'] ?? '';
                            foreach($positions as $position):
                            ?>
                                <option value="<?= esc($position) ?>" <?= $selectedPosition === $position ? 'selected' : '' ?>>
                                    <?= esc($position) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Seleccione el tipo de cargo que más se parecce al que usted desempeña y señálelo en el cuadro correspondiente de la derecha. Si tiene dudas pida apoyo a la persona que le entregó este cuestionario *</label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="position_type" value="Jefatura" id="posJefatura"
                                           <?= isset($demographics['position_type']) && $demographics['position_type'] === 'Jefatura' ? 'checked' : '' ?> required>
                                    <label class="form-check-label" for="posJefatura">Jefatura - tiene personal a cargo</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="position_type" value="Profesional" id="posProfesional"
                                           <?= isset($demographics['position_type']) && $demographics['position_type'] === 'Profesional' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="posProfesional">Profesional, analista, técnico, tecnólogo</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="position_type" value="Auxiliar" id="posAuxiliar"
                                           <?= isset($demographics['position_type']) && $demographics['position_type'] === 'Auxiliar' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="posAuxiliar">Auxiliar, asistente administrativo, asistente técnico</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="position_type" value="Operario" id="posOperario"
                                           <?= isset($demographics['position_type']) && $demographics['position_type'] === 'Operario' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="posOperario">Operario, operador, ayudante, servicios generales</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">¿Hace cuántos años que desempeña el cargo u oficio actual en esta empresa? *</label>
                        <div class="d-flex gap-3 align-items-center">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="time_in_position_type" value="less_than_year" id="posTimeLess"
                                       <?= isset($demographics['time_in_position_type']) && $demographics['time_in_position_type'] === 'less_than_year' ? 'checked' : '' ?> required>
                                <label class="form-check-label" for="posTimeLess">Si lleva menos de un año marque esta opción</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="time_in_position_type" value="more_than_year" id="posTimeMore"
                                       <?= isset($demographics['time_in_position_type']) && $demographics['time_in_position_type'] === 'more_than_year' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="posTimeMore">Si lleva más de un año, anote cuántos años</label>
                            </div>
                            <input type="number" class="form-control" name="time_in_position_years" style="max-width: 100px;"
                                   min="0" max="60"
                                   value="<?= $demographics['time_in_position_years'] ?? '' ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Escriba el nombre del departamento, área o sección de la empresa en el que trabaja *</label>
                        <select class="form-select" id="department-select" name="department" required>
                            <option value="">Seleccione...</option>
                            <?php
                            $selectedDept = $demographics['department'] ?? $worker['area'] ?? '';
                            foreach($areas as $area):
                            ?>
                                <option value="<?= esc($area) ?>" <?= $selectedDept === $area ? 'selected' : '' ?>>
                                    <?= esc($area) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Seleccione el tipo de contrato que tiene actualmente (marque una sola opción) *</label>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="contract_type" value="Temporal" id="contractTemp1"
                                           <?= isset($demographics['contract_type']) && $demographics['contract_type'] === 'Temporal' ? 'checked' : '' ?> required>
                                    <label class="form-check-label" for="contractTemp1">Temporal de menos de 1 año</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="contract_type" value="Termino_fijo" id="contractTemp2"
                                           <?= isset($demographics['contract_type']) && $demographics['contract_type'] === 'Termino_fijo' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="contractTemp2">Temporal de 1 año o más</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="contract_type" value="Termino_indefinido" id="contractIndef"
                                           <?= isset($demographics['contract_type']) && $demographics['contract_type'] === 'Termino_indefinido' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="contractIndef">Término indefinido</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="contract_type" value="Cooperado" id="contractCoop"
                                           <?= isset($demographics['contract_type']) && $demographics['contract_type'] === 'Cooperado' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="contractCoop">Cooperado (cooperativa)</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="contract_type" value="Prestacion_servicios" id="contractServ"
                                           <?= isset($demographics['contract_type']) && $demographics['contract_type'] === 'Prestacion_servicios' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="contractServ">Prestación de servicios</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="contract_type" value="No_tiene" id="contractNoSe"
                                           <?= isset($demographics['contract_type']) && $demographics['contract_type'] === 'No_tiene' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="contractNoSe">No sé</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Indique cuántas horas diarias de trabajo están establecidas habitualmente por la empresa para su cargo *</label>
                        <div class="input-group" style="max-width: 300px;">
                            <input type="number" class="form-control" name="hours_per_day"
                                   min="1" max="24" step="0.5"
                                   value="<?= $demographics['hours_per_day'] ?? '' ?>" required>
                            <span class="input-group-text">horas de trabajo al día</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Seleccione y marque el tipo de salario que recibe (marque una sola opción) *</label>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="salary_type" value="Fijo" id="salaryFijo"
                                           <?= isset($demographics['salary_type']) && $demographics['salary_type'] === 'Fijo' ? 'checked' : '' ?> required>
                                    <label class="form-check-label" for="salaryFijo">Fijo (diario, semanal, quincenal o mensual)</label>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="salary_type" value="Parte_fija_variable" id="salaryMixto"
                                           <?= isset($demographics['salary_type']) && $demographics['salary_type'] === 'Parte_fija_variable' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="salaryMixto">Una parte fija y otra variable</label>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="salary_type" value="Todo_variable" id="salaryVariable"
                                           <?= isset($demographics['salary_type']) && $demographics['salary_type'] === 'Todo_variable' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="salaryVariable">Todo variable (a destajo, por producción, por comisión)</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-arrow-right me-2"></i>Continuar
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        let colombiaData = null;
        const savedData = {
            departmentResidence: '<?= $demographics['department_residence'] ?? '' ?>',
            cityResidence: '<?= $demographics['city_residence'] ?? '' ?>',
            departmentWork: '<?= $demographics['department_work'] ?? '' ?>',
            cityWork: '<?= $demographics['city_work'] ?? '' ?>'
        };

        // Cargar datos de Colombia
        async function loadColombiaData() {
            try {
                const response = await fetch('<?= base_url('assets/colombia-geo.json') ?>');
                colombiaData = await response.json();
                initializeGeoSelects();
            } catch (error) {
                console.error('Error cargando datos de Colombia:', error);
            }
        }

        function initializeGeoSelects() {
            // Llenar select de departamentos
            const deptos = colombiaData.departamentos.map(d => d.nombre).sort();

            // Departamento residencia
            $('#department-residence-select').empty().append('<option value="">Seleccione departamento...</option>');
            deptos.forEach(dept => {
                $('#department-residence-select').append(`<option value="${dept}">${dept}</option>`);
            });

            // Departamento trabajo
            $('#department-work-select').empty().append('<option value="">Seleccione departamento...</option>');
            deptos.forEach(dept => {
                $('#department-work-select').append(`<option value="${dept}">${dept}</option>`);
            });

            // Inicializar Select2 para geo-selects
            $('#department-residence-select, #city-residence-select').select2({
                theme: 'bootstrap-5',
                width: '100%',
                language: {
                    noResults: () => "No se encontraron resultados",
                    searching: () => "Buscando..."
                }
            });

            $('#department-work-select, #city-work-select').select2({
                theme: 'bootstrap-5',
                width: '100%',
                language: {
                    noResults: () => "No se encontraron resultados",
                    searching: () => "Buscando..."
                }
            });

            // Manejar cambio de departamento de residencia
            $('#department-residence-select').on('change', function() {
                const selectedDept = $(this).val();
                updateCitySelect('residence', selectedDept);
            });

            // Manejar cambio de departamento de trabajo
            $('#department-work-select').on('change', function() {
                const selectedDept = $(this).val();
                updateCitySelect('work', selectedDept);
            });

            // Restaurar valores guardados
            if (savedData.departmentResidence) {
                $('#department-residence-select').val(savedData.departmentResidence).trigger('change');
                setTimeout(() => {
                    if (savedData.cityResidence) {
                        $('#city-residence-select').val(savedData.cityResidence).trigger('change');
                    }
                }, 100);
            }

            if (savedData.departmentWork) {
                $('#department-work-select').val(savedData.departmentWork).trigger('change');
                setTimeout(() => {
                    if (savedData.cityWork) {
                        $('#city-work-select').val(savedData.cityWork).trigger('change');
                    }
                }, 100);
            }
        }

        function updateCitySelect(type, departmentName) {
            const selectId = type === 'residence' ? '#city-residence-select' : '#city-work-select';
            const $select = $(selectId);

            if (!departmentName) {
                $select.empty().append('<option value="">Primero seleccione un departamento...</option>');
                $select.prop('disabled', true).trigger('change');
                return;
            }

            const dept = colombiaData.departamentos.find(d => d.nombre === departmentName);
            if (dept) {
                $select.empty().append('<option value="">Seleccione ciudad...</option>');
                dept.ciudades.forEach(city => {
                    $select.append(`<option value="${city}">${city}</option>`);
                });
                $select.prop('disabled', false).trigger('change');
            }
        }

        // Inicializar Select2 para departamento y cargo de empresa
        $(document).ready(function() {
            loadColombiaData();

            $('#department-select').select2({
                theme: 'bootstrap-5',
                placeholder: 'Seleccione un departamento/área',
                allowClear: false,
                width: '100%',
                language: {
                    noResults: () => "No se encontraron resultados",
                    searching: () => "Buscando..."
                }
            });

            $('#position-select').select2({
                theme: 'bootstrap-5',
                placeholder: 'Seleccione un cargo',
                allowClear: false,
                width: '100%',
                language: {
                    noResults: () => "No se encontraron resultados",
                    searching: () => "Buscando..."
                }
            });
        });

        // Validación condicional para campos de tiempo
        const timeInCompanyRadios = document.querySelectorAll('input[name="time_in_company_type"]');
        const timeInCompanyYearsInput = document.querySelector('input[name="time_in_company_years"]');

        const timeInPositionRadios = document.querySelectorAll('input[name="time_in_position_type"]');
        const timeInPositionYearsInput = document.querySelector('input[name="time_in_position_years"]');

        // Validar tiempo en empresa
        timeInCompanyRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'more_than_year') {
                    timeInCompanyYearsInput.required = true;
                    timeInCompanyYearsInput.style.borderColor = '#667eea';
                } else {
                    timeInCompanyYearsInput.required = false;
                    timeInCompanyYearsInput.value = '';
                    timeInCompanyYearsInput.style.borderColor = '';
                }
            });
        });

        // Validar tiempo en cargo
        timeInPositionRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'more_than_year') {
                    timeInPositionYearsInput.required = true;
                    timeInPositionYearsInput.style.borderColor = '#667eea';
                } else {
                    timeInPositionYearsInput.required = false;
                    timeInPositionYearsInput.value = '';
                    timeInPositionYearsInput.style.borderColor = '';
                }
            });
        });

        // ==========================================
        // INLINE EDITING: Auto-guardado campo por campo
        // ==========================================
        const DEBUG_MODE = <?= (env('DEBUG_SAVE_VERIFICATION') === 'true' || env('DEBUG_SAVE_VERIFICATION') === true) ? 'true' : 'false' ?>;

        console.log('🔍 DEBUG_MODE activado:', DEBUG_MODE);
        console.log('🔍 Valor de env DEBUG_SAVE_VERIFICATION:', '<?= var_export(env('DEBUG_SAVE_VERIFICATION'), true) ?>');
        console.log('🔍 URL de guardado:', '<?= base_url('assessment/save-field-general-data') ?>');

        // Función para guardar un campo individual
        async function saveField(fieldName, fieldValue) {
            console.log('💾 Guardando campo:', fieldName, '=', fieldValue);

            try {
                const formData = new FormData();
                formData.append('field_name', fieldName);
                formData.append('field_value', fieldValue);

                const response = await fetch('<?= base_url('assessment/save-field-general-data') ?>', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                console.log('📡 Response status:', response.status);
                const result = await response.json();
                console.log('📦 Result:', result);
                console.log('🔍 Checking conditions:');
                console.log('  - result.success:', result.success);
                console.log('  - DEBUG_MODE:', DEBUG_MODE);
                console.log('  - result.debug_enabled:', result.debug_enabled);
                console.log('  - result.debug_verification:', result.debug_verification);

                if (result.success && DEBUG_MODE && result.debug_enabled && result.debug_verification) {
                    // Sweet Alert de verificación - COMENTADO para producción
                    // Descomentar la siguiente línea para activar el debug visual:
                    // await showDebugVerification(result.debug_verification);
                    console.log('✅ Debug verification disponible (Sweet Alert desactivado)');
                } else if (result.success) {
                    console.log('✅ Guardado exitoso (sin debug)');
                    if (!DEBUG_MODE) console.log('   Razón: DEBUG_MODE es false');
                    if (!result.debug_enabled) console.log('   Razón: debug_enabled no presente');
                    if (!result.debug_verification) console.log('   Razón: debug_verification no presente');
                } else {
                    console.error('❌ Error en resultado:', result.message);
                    alert('Error: ' + result.message);
                }

                return result;
            } catch (error) {
                console.error('❌ Error al guardar campo:', error);
                alert('Error de conexión: ' + error.message);
                return { success: false, message: 'Error de conexión' };
            }
        }

        // Agregar listeners a todos los campos SELECT (excepto los de Select2)
        const regularSelects = document.querySelectorAll('#generalDataForm select:not(#department-select):not(#position-select)');
        console.log('🎯 SELECT fields encontrados:', regularSelects.length);
        regularSelects.forEach(select => {
            console.log('  - Registrando listener para:', select.getAttribute('name'));
            select.addEventListener('change', function() {
                const fieldName = this.getAttribute('name');
                const fieldValue = this.value;

                console.log('🔔 Change event en SELECT:', fieldName, '=', fieldValue);

                if (fieldValue) {
                    saveField(fieldName, fieldValue);
                }
            });
        });

        // Agregar listeners a campos INPUT de texto y número
        const textInputs = document.querySelectorAll('#generalDataForm input[type="text"], #generalDataForm input[type="number"]');
        textInputs.forEach(input => {
            input.addEventListener('blur', function() {
                const fieldName = this.getAttribute('name');
                const fieldValue = this.value;

                if (fieldValue) {
                    saveField(fieldName, fieldValue);
                }
            });
        });

        // Agregar listeners a campos RADIO
        const radioInputs = document.querySelectorAll('#generalDataForm input[type="radio"]');
        radioInputs.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.checked) {
                    const fieldName = this.getAttribute('name');
                    const fieldValue = this.value;
                    saveField(fieldName, fieldValue);
                }
            });
        });

        // Agregar listeners a Select2 (todos los campos)
        $('#department-select').on('select2:select', function(e) {
            const fieldValue = e.params.data.id;
            saveField('department', fieldValue);
        });

        $('#position-select').on('select2:select', function(e) {
            const fieldValue = e.params.data.id;
            saveField('position_name', fieldValue);
        });

        $('#department-residence-select').on('select2:select', function(e) {
            const fieldValue = e.params.data.id;
            console.log('🔔 Select2 change: department_residence =', fieldValue);
            saveField('department_residence', fieldValue);
        });

        $('#city-residence-select').on('select2:select', function(e) {
            const fieldValue = e.params.data.id;
            console.log('🔔 Select2 change: city_residence =', fieldValue);
            saveField('city_residence', fieldValue);
        });

        $('#department-work-select').on('select2:select', function(e) {
            const fieldValue = e.params.data.id;
            console.log('🔔 Select2 change: department_work =', fieldValue);
            saveField('department_work', fieldValue);
        });

        $('#city-work-select').on('select2:select', function(e) {
            const fieldValue = e.params.data.id;
            console.log('🔔 Select2 change: city_work =', fieldValue);
            saveField('city_work', fieldValue);
        });

        document.getElementById('generalDataForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            // Validación adicional antes de enviar
            const timeInCompanyType = document.querySelector('input[name="time_in_company_type"]:checked');
            const timeInPositionType = document.querySelector('input[name="time_in_position_type"]:checked');

            if (timeInCompanyType && timeInCompanyType.value === 'more_than_year') {
                if (!timeInCompanyYearsInput.value || timeInCompanyYearsInput.value <= 0) {
                    alert('Por favor, indique cuántos años lleva trabajando en esta empresa.');
                    timeInCompanyYearsInput.focus();
                    return;
                }
            }

            if (timeInPositionType && timeInPositionType.value === 'more_than_year') {
                if (!timeInPositionYearsInput.value || timeInPositionYearsInput.value <= 0) {
                    alert('Por favor, indique cuántos años lleva en el cargo actual.');
                    timeInPositionYearsInput.focus();
                    return;
                }
            }

            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Guardando...';

            try {
                const response = await fetch('<?= base_url('assessment/general-data') ?>', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    // Sweet Alert de verificación en submit - COMENTADO para producción
                    // Descomentar las siguientes líneas para activar el debug visual:
                    // if (result.debug_enabled && result.debug_verification) {
                    //     await showDebugVerification(result.debug_verification);
                    // }

                    window.location.href = '<?= base_url('assessment/intralaboral') ?>';
                } else {
                    alert('Error: ' + result.message);
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-arrow-right me-2"></i>Continuar';
                }
            } catch (error) {
                alert('Error al guardar los datos. Por favor, intenta nuevamente.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-arrow-right me-2"></i>Continuar';
            }
        });

        // Show debug verification (Data Integrity Check)
        function showDebugVerification(debugData) {
            if (!debugData || debugData.length === 0) return;

            // Construir tabla HTML con los datos
            let tableHTML = '<div style="max-height: 400px; overflow-y: auto;"><table class="table table-sm table-bordered" style="font-size: 0.85rem;"><thead class="table-dark"><tr><th>Campo</th><th>Enviado</th><th>En BD</th><th>Estado</th></tr></thead><tbody>';

            debugData.forEach(item => {
                const match = item.coincide;
                const statusIcon = match ? '✅' : '❌';
                const rowClass = match ? '' : 'table-danger';

                // Formato más amigable para el nombre del campo
                const fieldName = item.campo.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());

                tableHTML += `<tr class="${rowClass}">
                    <td><strong>${fieldName}</strong></td>
                    <td>${item.valor_enviado || '<em>vacío</em>'}</td>
                    <td><strong>${item.valor_en_bd || '<em>vacío</em>'}</strong></td>
                    <td>${statusIcon}</td>
                </tr>`;
            });

            tableHTML += '</tbody></table></div>';

            // Mostrar Sweet Alert con los datos
            return Swal.fire({
                title: '🔍 Verificación de Integridad (DEBUG)',
                html: tableHTML,
                icon: 'info',
                width: '800px',
                confirmButtonText: 'Continuar',
                footer: '<small>Modo DEBUG activo - Los datos se verificaron leyendo desde la base de datos</small>'
            });
        }
    </script>
</body>
</html>
