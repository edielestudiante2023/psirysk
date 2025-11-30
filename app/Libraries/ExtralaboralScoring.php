<?php

namespace App\Libraries;

/**
 * Sistema de Calificación del Cuestionario Extralaboral
 * Basado en la Batería de Riesgo Psicosocial del Ministerio de la Protección Social
 */
class ExtralaboralScoring
{
    /**
     * Tabla 11: Calificación de las opciones de respuesta
     * Dos grupos de ítems con calificación diferente
     */
    private static $itemsGrupo1 = [1, 4, 5, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 25, 27, 29];
    private static $itemsGrupo2 = [2, 3, 6, 24, 26, 28, 30, 31];

    /**
     * Tabla 12: Ítems que integran cada dimensión
     */
    private static $dimensiones = [
        'tiempo_fuera_trabajo' => [14, 15, 16, 17],
        'relaciones_familiares' => [22, 25, 27],
        'comunicacion_relaciones' => [18, 19, 20, 21, 23],
        'situacion_economica' => [29, 30, 31],
        'caracteristicas_vivienda' => [5, 6, 7, 8, 9, 10, 11, 12, 13],
        'influencia_entorno' => [24, 26, 28],
        'desplazamiento' => [1, 2, 3, 4]
    ];

    /**
     * Tabla 14: Factores de transformación por dimensión
     */
    private static $factoresTransformacion = [
        'tiempo_fuera_trabajo' => 16,
        'relaciones_familiares' => 12,
        'comunicacion_relaciones' => 20,
        'situacion_economica' => 12,
        'caracteristicas_vivienda' => 36,
        'influencia_entorno' => 12,
        'desplazamiento' => 16,
        'total' => 124
    ];

    /**
     * Tabla 17: Baremos para jefes, profesionales y técnicos
     */
    private static $baremosJefes = [
        'tiempo_fuera_trabajo' => [
            'sin_riesgo' => [0.0, 6.3],
            'riesgo_bajo' => [6.4, 25.0],
            'riesgo_medio' => [25.1, 37.5],
            'riesgo_alto' => [37.6, 50.0],
            'riesgo_muy_alto' => [50.1, 100]
        ],
        'relaciones_familiares' => [
            'sin_riesgo' => [0.0, 8.3],  // Tabla 17 - Manual oficial (corregido de 6.3)
            'riesgo_bajo' => [8.4, 25.0],
            'riesgo_medio' => [25.1, 33.3],
            'riesgo_alto' => [33.4, 50.0],
            'riesgo_muy_alto' => [50.1, 100]
        ],
        'comunicacion_relaciones' => [
            'sin_riesgo' => [0.0, 0.9],
            'riesgo_bajo' => [1.0, 10.0],
            'riesgo_medio' => [10.1, 20.0],
            'riesgo_alto' => [20.1, 30.0],
            'riesgo_muy_alto' => [30.1, 100]
        ],
        'situacion_economica' => [
            'sin_riesgo' => [0.0, 8.3],
            'riesgo_bajo' => [8.4, 25.0],
            'riesgo_medio' => [25.1, 33.3],
            'riesgo_alto' => [33.4, 50.0],
            'riesgo_muy_alto' => [50.1, 100]
        ],
        'caracteristicas_vivienda' => [
            'sin_riesgo' => [0.0, 5.6],
            'riesgo_bajo' => [5.7, 11.1],
            'riesgo_medio' => [11.2, 13.9],
            'riesgo_alto' => [14.0, 22.2],
            'riesgo_muy_alto' => [22.3, 100]
        ],
        'influencia_entorno' => [
            'sin_riesgo' => [0.0, 8.3],
            'riesgo_bajo' => [8.4, 16.7],
            'riesgo_medio' => [16.8, 25.0],
            'riesgo_alto' => [25.1, 41.7],
            'riesgo_muy_alto' => [41.8, 100]
        ],
        'desplazamiento' => [
            'sin_riesgo' => [0.0, 0.9],
            'riesgo_bajo' => [1.0, 12.5],
            'riesgo_medio' => [12.6, 25.0],
            'riesgo_alto' => [25.1, 43.8],
            'riesgo_muy_alto' => [43.9, 100]
        ],
        'total' => [
            'sin_riesgo' => [0.0, 11.3],
            'riesgo_bajo' => [11.4, 16.9],
            'riesgo_medio' => [17.0, 22.6],
            'riesgo_alto' => [22.7, 29.0],
            'riesgo_muy_alto' => [29.1, 100]
        ]
    ];

    /**
     * Tabla 18: Baremos para auxiliares y operarios
     */
    private static $baremosAuxiliares = [
        'tiempo_fuera_trabajo' => [
            'sin_riesgo' => [0.0, 6.3],
            'riesgo_bajo' => [6.4, 25.0],
            'riesgo_medio' => [25.1, 37.5],
            'riesgo_alto' => [37.6, 50.0],
            'riesgo_muy_alto' => [50.1, 100]
        ],
        'relaciones_familiares' => [
            'sin_riesgo' => [0.0, 8.3],
            'riesgo_bajo' => [8.4, 25.0],
            'riesgo_medio' => [25.1, 33.3],
            'riesgo_alto' => [33.4, 50.0],
            'riesgo_muy_alto' => [50.1, 100]
        ],
        'comunicacion_relaciones' => [
            'sin_riesgo' => [0.0, 5.0],
            'riesgo_bajo' => [5.1, 15.0],
            'riesgo_medio' => [15.1, 25.0],
            'riesgo_alto' => [25.1, 35.0],
            'riesgo_muy_alto' => [35.1, 100]
        ],
        'situacion_economica' => [
            'sin_riesgo' => [0.0, 16.7],
            'riesgo_bajo' => [16.8, 25.0],
            'riesgo_medio' => [25.1, 41.7],
            'riesgo_alto' => [41.8, 50.0],
            'riesgo_muy_alto' => [50.1, 100]
        ],
        'caracteristicas_vivienda' => [
            'sin_riesgo' => [0.0, 5.6],
            'riesgo_bajo' => [5.7, 11.1],
            'riesgo_medio' => [11.2, 16.7],
            'riesgo_alto' => [16.8, 27.8],
            'riesgo_muy_alto' => [27.9, 100]
        ],
        'influencia_entorno' => [
            'sin_riesgo' => [0.0, 0.9],
            'riesgo_bajo' => [1.0, 16.7],
            'riesgo_medio' => [16.8, 25.0],
            'riesgo_alto' => [25.1, 41.7],
            'riesgo_muy_alto' => [41.8, 100]
        ],
        'desplazamiento' => [
            'sin_riesgo' => [0.0, 0.9],
            'riesgo_bajo' => [1.0, 12.5],
            'riesgo_medio' => [12.6, 25.0],
            'riesgo_alto' => [25.1, 43.8],
            'riesgo_muy_alto' => [43.9, 100]
        ],
        'total' => [
            'sin_riesgo' => [0.0, 12.9],
            'riesgo_bajo' => [13.0, 17.7],
            'riesgo_medio' => [17.8, 24.2],
            'riesgo_alto' => [24.3, 32.3],
            'riesgo_muy_alto' => [32.4, 100]
        ]
    ];

    /**
     * Procesa un ítem - El valor YA viene calificado de la BD según Tabla 11
     *
     * IMPORTANTE: Según el manual oficial (Paso 1. Calificación de los ítems),
     * la calificación (inversión) se aplica AL MOMENTO DE GUARDAR en la BD.
     *
     * Este método solo retorna el valor tal cual viene de la BD.
     */
    public static function calificarItem($numeroItem, $respuesta)
    {
        // El valor YA está calificado (0-4) según Tabla 11
        // No se aplica inversión aquí
        return $respuesta;
    }

    /**
     * Paso 2: Calcular puntajes brutos por dimensión
     */
    public static function calcularPuntajesBrutos($respuestas)
    {
        $puntajesBrutos = [];

        foreach (self::$dimensiones as $nombreDimension => $items) {
            $sumaPuntajes = 0;
            $itemsRespondidos = 0;

            foreach ($items as $numeroItem) {
                if (isset($respuestas[$numeroItem]) && $respuestas[$numeroItem] !== null && $respuestas[$numeroItem] !== '') {
                    $puntajeCalificado = self::calificarItem($numeroItem, $respuestas[$numeroItem]);
                    $sumaPuntajes += $puntajeCalificado;
                    $itemsRespondidos++;
                }
            }

            // Validar que se hayan respondido todos los ítems de la dimensión
            if ($itemsRespondidos === count($items)) {
                $puntajesBrutos[$nombreDimension] = $sumaPuntajes;
            } else {
                $puntajesBrutos[$nombreDimension] = null; // Dimensión incompleta
            }
        }

        // Calcular puntaje bruto total
        $puntajeBrutoTotal = 0;
        $dimensionesValidas = 0;

        foreach ($puntajesBrutos as $puntaje) {
            if ($puntaje !== null) {
                $puntajeBrutoTotal += $puntaje;
                $dimensionesValidas++;
            }
        }

        $puntajesBrutos['total'] = ($dimensionesValidas === count(self::$dimensiones)) ? $puntajeBrutoTotal : null;

        return $puntajesBrutos;
    }

    /**
     * Paso 3: Transformar puntajes brutos
     */
    public static function transformarPuntajes($puntajesBrutos)
    {
        $puntajesTransformados = [];

        foreach ($puntajesBrutos as $dimension => $puntajeBruto) {
            if ($puntajeBruto !== null && isset(self::$factoresTransformacion[$dimension])) {
                $factor = self::$factoresTransformacion[$dimension];
                $transformado = ($puntajeBruto / $factor) * 100;
                // Redondear a un decimal
                $puntajesTransformados[$dimension] = round($transformado, 1);
            } else {
                $puntajesTransformados[$dimension] = null;
            }
        }

        return $puntajesTransformados;
    }

    /**
     * Paso 4: Determinar nivel de riesgo según baremos
     */
    public static function determinarNivelRiesgo($puntajeTransformado, $dimension, $tipoBaremo = 'jefes')
    {
        if ($puntajeTransformado === null) {
            return null;
        }

        $baremos = ($tipoBaremo === 'jefes') ? self::$baremosJefes : self::$baremosAuxiliares;

        if (!isset($baremos[$dimension])) {
            return null;
        }

        $rangosDimension = $baremos[$dimension];

        foreach ($rangosDimension as $nivel => $rango) {
            if ($puntajeTransformado >= $rango[0] && $puntajeTransformado <= $rango[1]) {
                return $nivel;
            }
        }

        return null;
    }

    /**
     * Proceso completo de calificación
     */
    public static function calificar($respuestas, $tipoBaremo = 'jefes')
    {
        // Paso 1 y 2: Calificar ítems y calcular puntajes brutos
        $puntajesBrutos = self::calcularPuntajesBrutos($respuestas);

        // Paso 3: Transformar puntajes
        $puntajesTransformados = self::transformarPuntajes($puntajesBrutos);

        // Paso 4: Determinar niveles de riesgo
        $nivelesRiesgo = [];
        foreach ($puntajesTransformados as $dimension => $puntajeTransformado) {
            $nivelesRiesgo[$dimension] = self::determinarNivelRiesgo($puntajeTransformado, $dimension, $tipoBaremo);
        }

        return [
            'puntajes_brutos' => $puntajesBrutos,
            'puntajes_transformados' => $puntajesTransformados,
            'niveles_riesgo' => $nivelesRiesgo,
            'tipo_baremo' => $tipoBaremo
        ];
    }

    /**
     * Obtener interpretación del nivel de riesgo
     */
    public static function getInterpretacionNivel($nivel)
    {
        $interpretaciones = [
            'sin_riesgo' => 'Sin riesgo o riesgo despreciable: ausencia de riesgo o riesgo tan bajo que no amerita desarrollar actividades de intervención.',
            'riesgo_bajo' => 'Riesgo bajo: no se espera que los factores psicosociales que obtengan puntuaciones de este nivel estén relacionados con síntomas o respuestas de estrés significativas.',
            'riesgo_medio' => 'Riesgo medio: nivel de riesgo en el que se esperaría una respuesta de estrés moderada.',
            'riesgo_alto' => 'Riesgo alto: nivel de riesgo que tiene una importante posibilidad de asociación con respuestas de estrés alto.',
            'riesgo_muy_alto' => 'Riesgo muy alto: nivel de riesgo con amplia posibilidad de asociarse a respuestas muy altas de estrés.'
        ];

        return $interpretaciones[$nivel] ?? '';
    }

    // =============================================
    // MÉTODOS PÚBLICOS PARA ACCESO A BAREMOS
    // Single Source of Truth - Tablas 17 y 18
    // =============================================

    /**
     * Obtiene todos los baremos de dimensiones para Forma A (jefes, profesionales, técnicos)
     * Tabla 17 de la Resolución 2404/2019
     *
     * @return array Baremos de todas las dimensiones
     */
    public static function getBaremosDimensionesA()
    {
        return self::$baremosJefes;
    }

    /**
     * Obtiene todos los baremos de dimensiones para Forma B (auxiliares, operarios)
     * Tabla 18 de la Resolución 2404/2019
     *
     * @return array Baremos de todas las dimensiones
     */
    public static function getBaremosDimensionesB()
    {
        return self::$baremosAuxiliares;
    }

    /**
     * Obtiene el baremo de una dimensión específica según la forma
     *
     * @param string $dimension Código de la dimensión
     * @param string $forma 'A' o 'B'
     * @return array|null Baremo de la dimensión o null si no existe
     */
    public static function getBaremoDimension($dimension, $forma = 'A')
    {
        $baremos = ($forma === 'A') ? self::$baremosJefes : self::$baremosAuxiliares;
        return $baremos[$dimension] ?? null;
    }

    /**
     * Obtiene el baremo total extralaboral según la forma
     *
     * @param string $forma 'A' o 'B'
     * @return array Baremo total
     */
    public static function getBaremoTotal($forma = 'A')
    {
        $baremos = ($forma === 'A') ? self::$baremosJefes : self::$baremosAuxiliares;
        return $baremos['total'] ?? null;
    }
}
