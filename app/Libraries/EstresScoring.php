<?php

namespace App\Libraries;

/**
 * Librería de calificación para Cuestionario de Evaluación del Estrés
 * Basado en los documentos oficiales del Ministerio de la Protección Social de Colombia
 * Tercera versión del cuestionario
 *
 * - Tabla 4: Calificación de las opciones de respuesta de los ítems
 * - Tabla 5: Baremos según ocupación (Jefes/profesionales/técnicos vs Auxiliares/operarios)
 * - Tabla 6: Baremos de interpretación del puntaje total transformado
 * - Factor de transformación: 61.16
 */
class EstresScoring
{
    /**
     * Calificación de ítems según Tabla 4
     * Tres grupos diferentes de scoring
     */

    /**
     * Grupo 1: Ítems 1, 2, 3, 9, 13, 14, 15, 23 y 24
     * Siempre=9, Casi siempre=6, A veces=3, Nunca=0
     */
    private static $itemsGrupo1 = [1, 2, 3, 9, 13, 14, 15, 23, 24];
    private static $valoresGrupo1 = [
        'siempre' => 9,
        'casi_siempre' => 6,
        'a_veces' => 3,
        'nunca' => 0
    ];

    /**
     * Grupo 2: Ítems 4, 5, 6, 10, 11, 16, 17, 18, 19, 25, 26, 27 y 28
     * Siempre=6, Casi siempre=4, A veces=2, Nunca=0
     */
    private static $itemsGrupo2 = [4, 5, 6, 10, 11, 16, 17, 18, 19, 25, 26, 27, 28];
    private static $valoresGrupo2 = [
        'siempre' => 6,
        'casi_siempre' => 4,
        'a_veces' => 2,
        'nunca' => 0
    ];

    /**
     * Grupo 3: Ítems 7, 8, 12, 20, 21, 22, 29, 30 y 31
     * Siempre=3, Casi siempre=2, A veces=1, Nunca=0
     */
    private static $itemsGrupo3 = [7, 8, 12, 20, 21, 22, 29, 30, 31];
    private static $valoresGrupo3 = [
        'siempre' => 3,
        'casi_siempre' => 2,
        'a_veces' => 1,
        'nunca' => 0
    ];

    /**
     * Factor de transformación para convertir puntaje bruto a escala 0-100
     * Según documentación oficial y fórmula Excel: Puntaje transformado = (Puntaje bruto total / 61.1666666666666) × 100
     * Este valor es 61 + 1/6 = 367/6
     */
    private static $factorTransformacion = 61.1666666666666;

    /**
     * Baremos según Tabla 6 (Tercera versión)
     * Depende del tipo de ocupación del trabajador
     */

    /**
     * Baremo para Jefes, profesionales y técnicos (Tabla 6)
     */
    private static $baremosJefes = [
        'muy_bajo' => [0.0, 7.8],
        'bajo' => [7.9, 12.6],
        'medio' => [12.7, 17.7],
        'alto' => [17.8, 25.0],
        'muy_alto' => [25.1, 100.0]
    ];

    /**
     * Baremo para Auxiliares y operarios (Tabla 6)
     */
    private static $baremosAuxiliares = [
        'muy_bajo' => [0.0, 6.5],
        'bajo' => [6.6, 11.8],
        'medio' => [11.9, 17.0],
        'alto' => [17.1, 23.4],
        'muy_alto' => [23.5, 100.0]
    ];

    /**
     * Textos exactos de las preguntas según el Ministerio
     * Extraídos de texto.txt (entre corchetes)
     */
    private static $preguntas = [
        1 => 'Dolores en el cuello y espalda o tensión muscular.',
        2 => 'Problemas gastrointestinales, úlcera péptica, acidez, problemas digestivos o del colon.',
        3 => 'Problemas respiratorios.',
        4 => 'Dolor de cabeza.',
        5 => 'Trastornos del sueño como somnolencia durante el día o desvelo en la noche.',
        6 => 'Palpitaciones en el pecho o problemas cardíacos.',
        7 => 'Cambios fuertes del apetito.',
        8 => 'Problemas relacionados con la función de los órganos genitales (impotencia, frigidez).',
        9 => 'Dificultad en las relaciones familiares.',
        10 => 'Dificultad para permanecer quieto o dificultad para iniciar actividades.',
        11 => 'Dificultad en las relaciones con otras personas.',
        12 => 'Sensación de aislamiento y desinterés.',
        13 => 'Sentimiento de sobrecarga de trabajo.',
        14 => 'Dificultad para concentrarse, olvidos frecuentes.',
        15 => 'Aumento en el número de accidentes de trabajo.',
        16 => 'Sentimiento de frustración, de no haber hecho lo que se quería en la vida.',
        17 => 'Cansancio, tedio o desgano.',
        18 => 'Disminución del rendimiento en el trabajo o poca creatividad.',
        19 => 'Deseo de no asistir al trabajo.',
        20 => 'Bajo compromiso o poco interés con lo que se hace.',
        21 => 'Dificultad para tomar decisiones.',
        22 => 'Deseo de cambiar de empleo.',
        23 => 'Sentimiento de soledad y miedo.',
        24 => 'Sentimiento de irritabilidad, actitudes y pensamientos negativos.',
        25 => 'Sentimiento de angustia, preocupación o tristeza.',
        26 => 'Consumo de drogas para aliviar la tensión o los nervios.',
        27 => 'Sentimientos de que "no vale nada", o " no sirve para nada".',
        28 => 'Consumo de bebidas alcohólicas o café o cigarrillo.',
        29 => 'Sentimiento de que está perdiendo la razón.',
        30 => 'Comportamientos rígidos, obstinación o terquedad.',
        31 => 'Sensación de no poder manejar los problemas de la vida.'
    ];

    /**
     * Dimensiones/categorías de síntomas según documentación
     */
    private static $dimensiones = [
        'sintomas_fisiologicos' => [1, 2, 3, 4, 5, 6, 7, 8], // Fisiológicos
        'sintomas_comportamiento_social' => [9, 10, 11, 12], // Comportamiento social
        'sintomas_laborales' => [13, 14, 15, 16, 17, 18, 19, 20, 21, 22], // Laborales
        'sintomas_psicoemocionales' => [23, 24, 25, 26, 27, 28, 29, 30, 31] // Psicoemocionales
    ];

    /**
     * Califica el cuestionario de Estrés
     *
     * @param array $respuestas Array asociativo [numero_pregunta => valor_respuesta]
     *                          Valores: 'siempre', 'casi_siempre', 'a_veces', 'nunca'
     * @param string $tipoBaremo 'jefes' o 'auxiliares' (determina qué baremo usar)
     *
     * @return array [
     *   'puntaje_bruto_total' => float,
     *   'puntaje_transformado_total' => float,
     *   'nivel_estres' => string,
     *   'tipo_baremo' => string,
     *   'puntajes_por_dimension' => [],
     *   'error' => string|null
     * ]
     */
    public static function calificar($respuestas, $tipoBaremo = 'jefes')
    {
        // VALIDACIÓN OBLIGATORIA: Verificar que todos los 31 ítems estén respondidos
        // Según manual oficial página 5 (Paso 2 - Obtención del puntaje bruto total):
        // "Si un cuestionario no cuenta con el total de ítems respondidos no debe
        // calcularse su puntaje bruto. De hacerse, el resultado que se obtenga no sería válido."
        $itemsRequeridos = 31;
        $itemsRespondidos = 0;

        for ($i = 1; $i <= $itemsRequeridos; $i++) {
            if (isset($respuestas[$i]) && $respuestas[$i] !== null && $respuestas[$i] !== '') {
                $itemsRespondidos++;
            }
        }

        if ($itemsRespondidos < $itemsRequeridos) {
            return [
                'puntaje_bruto_total' => null,
                'puntaje_transformado_total' => null,
                'nivel_estres' => null,
                'tipo_baremo' => $tipoBaremo,
                'puntajes_por_dimension' => null,
                'subtotales' => null,
                'error' => "Cuestionario incompleto: {$itemsRespondidos}/{$itemsRequeridos} ítems respondidos. " .
                          "Se requieren todos los ítems según manual oficial del Ministerio de la Protección Social."
            ];
        }

        // 1. Calificar cada ítem según su grupo
        $puntajesItems = self::calificarItems($respuestas);

        // 2. Calcular puntaje bruto total (suma de todos los ítems)
        $puntajeBrutoTotal = array_sum($puntajesItems);

        // 3. Calcular promedio de ítems 1 a 8 y multiplicar por 4
        $promedio1a8 = self::calcularPromedioGrupo($puntajesItems, range(1, 8));
        $subtotal1 = $promedio1a8 * 4;

        // 4. Calcular promedio de ítems 9 a 12 y multiplicar por 3
        $promedio9a12 = self::calcularPromedioGrupo($puntajesItems, range(9, 12));
        $subtotal2 = $promedio9a12 * 3;

        // 5. Calcular promedio de ítems 13 a 22 y multiplicar por 2
        $promedio13a22 = self::calcularPromedioGrupo($puntajesItems, range(13, 22));
        $subtotal3 = $promedio13a22 * 2;

        // 6. Calcular promedio de ítems 23 a 31 (NO es suma, es promedio según fórmula oficial Excel)
        $promedio23a31 = self::calcularPromedioGrupo($puntajesItems, range(23, 31));
        $subtotal4 = $promedio23a31;

        // 7. Puntaje bruto total = suma de los 4 subtotales
        $puntajeBrutoTotalCalculado = $subtotal1 + $subtotal2 + $subtotal3 + $subtotal4;

        // 8. Transformar puntaje bruto a escala 0-100
        $puntajeTransformado = self::transformarPuntaje($puntajeBrutoTotalCalculado);

        // 9. Determinar nivel de estrés según baremo
        if (function_exists('log_message')) {
            log_message('error', "🔍 [EstresScoring] Puntaje Transformado: {$puntajeTransformado}, Tipo Baremo: {$tipoBaremo}");
        }
        $nivelEstres = self::determinarNivelEstres($puntajeTransformado, $tipoBaremo);
        if (function_exists('log_message')) {
            log_message('error', "🔍 [EstresScoring] Nivel determinado: {$nivelEstres}");
        }

        // 10. Calcular puntajes por dimensión (para análisis adicional)
        $puntajesPorDimension = self::calcularPuntajesPorDimension($puntajesItems);

        return [
            'puntaje_bruto_total' => round($puntajeBrutoTotalCalculado, 2),
            'puntaje_transformado_total' => round($puntajeTransformado, 1),
            'nivel_estres' => $nivelEstres,
            'tipo_baremo' => $tipoBaremo,
            'puntajes_por_dimension' => $puntajesPorDimension,
            'subtotales' => [
                'subtotal_1_8' => round($subtotal1, 2),
                'subtotal_9_12' => round($subtotal2, 2),
                'subtotal_13_22' => round($subtotal3, 2),
                'subtotal_23_31' => round($subtotal4, 2)
            ]
        ];
    }

    /**
     * Califica cada ítem según su grupo de scoring
     */
    private static function calificarItems($respuestas)
    {
        $puntajes = [];

        foreach ($respuestas as $numPregunta => $valorRespuesta) {
            // IMPORTANTE: Si el valor es numérico, convertirlo a texto según Tabla 4
            // Esto es necesario para soportar datos importados por CSV que vienen como números
            if (is_numeric($valorRespuesta)) {
                $valorRespuesta = self::convertirNumeroATexto($numPregunta, (int)$valorRespuesta);
            }

            // Normalizar la respuesta a minúsculas y sin espacios
            $respuesta = strtolower(str_replace(' ', '_', trim($valorRespuesta)));

            if (in_array($numPregunta, self::$itemsGrupo1)) {
                $puntajes[$numPregunta] = self::$valoresGrupo1[$respuesta] ?? 0;
            } elseif (in_array($numPregunta, self::$itemsGrupo2)) {
                $puntajes[$numPregunta] = self::$valoresGrupo2[$respuesta] ?? 0;
            } elseif (in_array($numPregunta, self::$itemsGrupo3)) {
                $puntajes[$numPregunta] = self::$valoresGrupo3[$respuesta] ?? 0;
            }
        }

        return $puntajes;
    }

    /**
     * Convierte un valor numérico a su equivalente textual según Tabla 4
     * Soporta datos importados por CSV que vienen como puntajes numéricos
     *
     * @param int $numPregunta Número de la pregunta (1-31)
     * @param int $valor Valor numérico (0, 1, 2, 3, 4, 6, 9)
     * @return string 'siempre', 'casi_siempre', 'a_veces', 'nunca'
     */
    private static function convertirNumeroATexto($numPregunta, $valor)
    {
        // Grupo 1: 1, 2, 3, 9, 13, 14, 15, 23, 24
        // Siempre=9, Casi siempre=6, A veces=3, Nunca=0
        if (in_array($numPregunta, self::$itemsGrupo1)) {
            if ($valor === 9) return 'siempre';
            if ($valor === 6) return 'casi_siempre';
            if ($valor === 3) return 'a_veces';
            if ($valor === 0) return 'nunca';
        }

        // Grupo 2: 4, 5, 6, 10, 11, 16, 17, 18, 19, 25, 26, 27, 28
        // Siempre=6, Casi siempre=4, A veces=2, Nunca=0
        elseif (in_array($numPregunta, self::$itemsGrupo2)) {
            if ($valor === 6) return 'siempre';
            if ($valor === 4) return 'casi_siempre';
            if ($valor === 2) return 'a_veces';
            if ($valor === 0) return 'nunca';
        }

        // Grupo 3: 7, 8, 12, 20, 21, 22, 29, 30, 31
        // Siempre=3, Casi siempre=2, A veces=1, Nunca=0
        elseif (in_array($numPregunta, self::$itemsGrupo3)) {
            if ($valor === 3) return 'siempre';
            if ($valor === 2) return 'casi_siempre';
            if ($valor === 1) return 'a_veces';
            if ($valor === 0) return 'nunca';
        }

        // Si no se reconoce el valor, registrar warning y devolver 'nunca' por defecto
        if (function_exists('log_message')) {
            log_message('warning', "[EstresScoring] Valor numérico no reconocido para pregunta {$numPregunta}: {$valor}");
        }
        return 'nunca';
    }

    /**
     * Calcula el promedio de un grupo de ítems
     */
    private static function calcularPromedioGrupo($puntajes, $items)
    {
        $suma = 0;
        $contador = 0;

        foreach ($items as $item) {
            if (isset($puntajes[$item])) {
                $suma += $puntajes[$item];
                $contador++;
            }
        }

        return $contador > 0 ? $suma / $contador : 0;
    }

    /**
     * Suma los puntajes de un conjunto de ítems
     */
    private static function sumarItems($puntajes, $items)
    {
        $suma = 0;

        foreach ($items as $item) {
            if (isset($puntajes[$item])) {
                $suma += $puntajes[$item];
            }
        }

        return $suma;
    }

    /**
     * Transforma el puntaje bruto a escala 0-100
     * Fórmula: (Puntaje bruto total / 61.16) × 100
     */
    private static function transformarPuntaje($puntajeBruto)
    {
        return ($puntajeBruto / self::$factorTransformacion) * 100;
    }

    /**
     * Determina el nivel de estrés según el baremo correspondiente
     */
    private static function determinarNivelEstres($puntajeTransformado, $tipoBaremo)
    {
        if (function_exists('log_message')) {
            log_message('error', "🔍 [determinarNivelEstres] INICIO - Puntaje: {$puntajeTransformado}, Tipo: {$tipoBaremo}");
        }

        $baremos = ($tipoBaremo === 'auxiliares')
            ? self::$baremosAuxiliares
            : self::$baremosJefes;

        if (function_exists('log_message')) {
            log_message('error', "🔍 [determinarNivelEstres] Baremos seleccionados: " . json_encode($baremos));
        }

        foreach ($baremos as $nivel => $rango) {
            $min = $rango[0];
            $max = $rango[1];

            // Use small epsilon (0.1) for upper bound to handle floating point precision errors
            $cumple = ($puntajeTransformado >= $min && $puntajeTransformado <= ($max + 0.1));

            if (function_exists('log_message')) {
                log_message('error', "🔍 [determinarNivelEstres] Nivel: {$nivel}, Rango: [{$min}, {$max}], Cumple: " . ($cumple ? 'SI' : 'NO'));
            }

            if ($cumple) {
                if (function_exists('log_message')) {
                    log_message('error', "🔍 [determinarNivelEstres] MATCH ENCONTRADO: {$nivel}");
                }
                return $nivel;
            }
        }

        if (function_exists('log_message')) {
            log_message('error', "🔍 [determinarNivelEstres] NO SE ENCONTRÓ MATCH - retornando muy_bajo por defecto");
        }
        return 'muy_bajo'; // Default
    }

    /**
     * Calcula puntajes por dimensión (para análisis detallado)
     */
    private static function calcularPuntajesPorDimension($puntajesItems)
    {
        $puntajes = [];

        foreach (self::$dimensiones as $dimension => $items) {
            $suma = 0;
            foreach ($items as $item) {
                if (isset($puntajesItems[$item])) {
                    $suma += $puntajesItems[$item];
                }
            }
            $puntajes[$dimension] = $suma;
        }

        return $puntajes;
    }

    /**
     * Obtiene el texto de una pregunta específica
     */
    public static function getPregunta($numero)
    {
        return self::$preguntas[$numero] ?? null;
    }

    /**
     * Obtiene todas las preguntas
     */
    public static function getTodasLasPreguntas()
    {
        return self::$preguntas;
    }

    /**
     * Obtiene el color asociado a un nivel de estrés (para dashboards)
     */
    public static function getColorNivel($nivel)
    {
        $colores = [
            'muy_bajo' => '#28a745',    // Verde oscuro
            'bajo' => '#7dce82',        // Verde claro
            'medio' => '#ffc107',       // Amarillo
            'alto' => '#fd7e14',        // Naranja
            'muy_alto' => '#dc3545'     // Rojo
        ];

        return $colores[$nivel] ?? '#6c757d';
    }

    // =============================================
    // MÉTODOS PÚBLICOS PARA ACCESO A BAREMOS
    // Single Source of Truth - Tabla 6
    // =============================================

    /**
     * Obtiene el baremo de estrés para Forma A (Jefes, Profesionales, Técnicos)
     * Tabla 6 de la documentación oficial
     *
     * @return array Baremo completo
     */
    public static function getBaremoA()
    {
        return self::$baremosJefes;
    }

    /**
     * Obtiene el baremo de estrés para Forma B (Auxiliares, Operarios)
     * Tabla 6 de la documentación oficial
     *
     * @return array Baremo completo
     */
    public static function getBaremoB()
    {
        return self::$baremosAuxiliares;
    }

    /**
     * Obtiene el baremo de estrés según la forma
     *
     * @param string $forma 'A' o 'B'
     * @return array Baremo completo
     */
    public static function getBaremo($forma = 'A')
    {
        return ($forma === 'A') ? self::$baremosJefes : self::$baremosAuxiliares;
    }

    // =========================================================================
    // BAREMOS TABLA 34 - TOTAL GENERAL PSICOSOCIAL
    // Combina los tres cuestionarios (Intralaboral + Extralaboral + Estrés)
    // =========================================================================

    /**
     * Baremos Tabla 34 - Total General Psicosocial
     * Forma A: Jefes, Profesionales, Técnicos
     */
    private static $baremosGeneralA = [
        'sin_riesgo'      => [0.0, 18.8],
        'riesgo_bajo'     => [18.9, 24.4],
        'riesgo_medio'    => [24.5, 29.5],
        'riesgo_alto'     => [29.6, 35.4],
        'riesgo_muy_alto' => [35.5, 100.0],
    ];

    /**
     * Baremos Tabla 34 - Total General Psicosocial
     * Forma B: Auxiliares, Operarios
     */
    private static $baremosGeneralB = [
        'sin_riesgo'      => [0.0, 19.9],
        'riesgo_bajo'     => [20.0, 24.8],
        'riesgo_medio'    => [24.9, 29.5],
        'riesgo_alto'     => [29.6, 35.4],
        'riesgo_muy_alto' => [35.5, 100.0],
    ];

    /**
     * Obtiene el baremo de riesgo general (Tabla 34) según la forma
     * Este baremo se aplica al puntaje combinado de los 3 cuestionarios
     *
     * @param string $forma 'A' o 'B'
     * @return array Baremo de riesgo general
     */
    public static function getBaremoGeneral($forma = 'A')
    {
        return ($forma === 'A') ? self::$baremosGeneralA : self::$baremosGeneralB;
    }
}
