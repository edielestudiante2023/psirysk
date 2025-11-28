<?php

namespace App\Libraries;

/**
 * Librería de calificación para Cuestionario Intralaboral Forma B
 * Basado en los documentos oficiales del Ministerio de la Protección Social de Colombia
 *
 * - Tabla 22: Calificación de ítems Forma B
 * - Tabla 23: Dimensiones e ítems
 * - Tabla 25: Factores de transformación dimensiones Forma B
 * - Tabla 26: Factores de transformación dominios
 * - Tabla 27: Factor de transformación total intralaboral
 * - Tabla 30: Baremos dimensiones Forma B (auxiliares, operarios)
 * - Tabla 32: Baremos dominios Forma B (auxiliares, operarios)
 * - Tabla 33: Baremo intralaboral total Forma B (auxiliares, operarios)
 */
class IntralaboralBScoring
{
    /**
     * Preguntas con calificación NORMAL (Siempre=0, Casi siempre=1, Algunas veces=2, Casi nunca=3, Nunca=4)
     * Grupo 1 según Tabla 22 - Ministerio de la Protección Social
     */
    private static $itemsGrupoNormal = [
        4, 5, 6, 9, 12, 14, 22, 24, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49,
        50, 51, 52, 53, 54, 55, 56, 57, 58, 59, 60, 61, 62, 63, 64, 65, 67, 68, 69, 70, 71, 72, 73, 74, 75, 76, 77,
        78, 79, 80, 81, 82, 83, 84, 85, 86, 87, 88, 97
    ];

    /**
     * Preguntas con calificación INVERSA (Siempre=4, Casi siempre=3, Algunas veces=2, Casi nunca=1, Nunca=0)
     * Grupo 2 según Tabla 22 - Ministerio de la Protección Social
     */
    private static $itemsGrupoInverso = [
        1, 2, 3, 7, 8, 10, 11, 13, 15, 16, 17, 18, 19, 20, 21, 23, 25, 26, 27, 28, 66, 89, 90, 91, 92, 93, 94, 95, 96
    ];

    /**
     * Mapeo de dimensiones e ítems según Tabla 23 (Forma B)
     */
    private static $dimensiones = [
        'caracteristicas_liderazgo' => [49, 50, 51, 52, 53, 54, 55, 56, 57, 58, 59, 60, 61],
        'relaciones_sociales_trabajo' => [62, 63, 64, 65, 66, 67, 68, 69, 70, 71, 72, 73],
        'retroalimentacion_desempeno' => [74, 75, 76, 77, 78],
        'claridad_rol' => [41, 42, 43, 44, 45],
        'capacitacion' => [46, 47, 48],
        'participacion_manejo_cambio' => [38, 39, 40],
        'oportunidades_desarrollo' => [29, 30, 31, 32],
        'control_autonomia_trabajo' => [34, 35, 36],  // Ítems 33 y 37 pertenecen exclusivamente a demandas_jornada_trabajo
        'demandas_ambientales_esfuerzo_fisico' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
        'demandas_emocionales' => [89, 90, 91, 92, 93, 94, 95, 96, 97], // Condicional: solo si atiende_clientes=true
        'demandas_cuantitativas' => [13, 14, 15],
        'influencia_trabajo_entorno_extralaboral' => [25, 26, 27, 28],
        'demandas_carga_mental' => [16, 17, 18, 19, 20],
        'demandas_jornada_trabajo' => [21, 22, 23, 24, 33, 37],
        'recompensas_pertenencia_estabilidad' => [85, 86, 87, 88],
        'reconocimiento_compensacion' => [79, 80, 81, 82, 83, 84]
    ];

    /**
     * Factores de transformación para dimensiones - Tabla 25 (Forma B)
     * Basado en tabla oficial del Ministerio de la Protección Social
     */
    private static $factoresTransformacionDimensiones = [
        'caracteristicas_liderazgo' => 52,
        'relaciones_sociales_trabajo' => 48,
        'retroalimentacion_desempeno' => 20,
        'claridad_rol' => 20,
        'capacitacion' => 12,
        'participacion_manejo_cambio' => 12,
        'oportunidades_desarrollo' => 16,
        'control_autonomia_trabajo' => 12,  // Tabla 25 Forma B - Corregido 2025-11-25
        'demandas_ambientales_esfuerzo_fisico' => 48,
        'demandas_emocionales' => 36,
        'demandas_cuantitativas' => 12,
        'influencia_trabajo_entorno_extralaboral' => 16,
        'demandas_carga_mental' => 20,
        'demandas_jornada_trabajo' => 24,
        'recompensas_pertenencia_estabilidad' => 16,
        'reconocimiento_compensacion' => 24
    ];

    /**
     * Mapeo de dominios y sus dimensiones según Tabla 23
     */
    private static $dominios = [
        'liderazgo_relaciones_sociales' => [
            'caracteristicas_liderazgo',
            'relaciones_sociales_trabajo',
            'retroalimentacion_desempeno'
        ],
        'control' => [
            'claridad_rol',
            'capacitacion',
            'participacion_manejo_cambio',
            'oportunidades_desarrollo',
            'control_autonomia_trabajo'
        ],
        'demandas' => [
            'demandas_ambientales_esfuerzo_fisico',
            'demandas_emocionales',
            'demandas_cuantitativas',
            'influencia_trabajo_entorno_extralaboral',
            'demandas_carga_mental',
            'demandas_jornada_trabajo'
        ],
        'recompensas' => [
            'recompensas_pertenencia_estabilidad',
            'reconocimiento_compensacion'
        ]
    ];

    /**
     * Factores de transformación para dominios - Tabla 26 (Forma B)
     * CORREGIDO: Control era 80, manual oficial dice 72
     */
    private static $factoresTransformacionDominios = [
        'liderazgo_relaciones_sociales' => 120,
        'control' => 72,  // Tabla 26 Forma B - Manual oficial
        'demandas' => 156,
        'recompensas' => 40
    ];

    /**
     * Factor de transformación total intralaboral - Tabla 27 (Forma B)
     *
     * NOTA TÉCNICA: El manual oficial indica 388, aunque la suma matemática
     * de factores por dimensión da 396. Se usa 388 para cumplir con el
     * estándar oficial del Ministerio de la Protección Social.
     *
     * Ver INVESTIGACION_FACTOR_388_vs_396.md para detalles completos.
     */
    private static $factorTransformacionTotal = 388;

    /**
     * Baremos para dimensiones - Tabla 30 (Forma B - auxiliares, operarios)
     */
    private static $baremosDimensiones = [
        'caracteristicas_liderazgo' => [
            'sin_riesgo' => [0.0, 3.8],
            'riesgo_bajo' => [3.9, 13.5],
            'riesgo_medio' => [13.6, 25.0],
            'riesgo_alto' => [25.1, 38.5],
            'riesgo_muy_alto' => [38.6, 100.0]
        ],
        'relaciones_sociales_trabajo' => [
            'sin_riesgo' => [0.0, 6.3],
            'riesgo_bajo' => [6.4, 14.6],
            'riesgo_medio' => [14.7, 27.1],
            'riesgo_alto' => [27.2, 37.5],
            'riesgo_muy_alto' => [37.6, 100.0]
        ],
        'retroalimentacion_desempeno' => [
            'sin_riesgo' => [0.0, 5.0],
            'riesgo_bajo' => [5.1, 20.0],
            'riesgo_medio' => [20.1, 30.0],
            'riesgo_alto' => [30.1, 50.0],
            'riesgo_muy_alto' => [50.1, 100.0]
        ],
        'claridad_rol' => [
            'sin_riesgo' => [0.0, 0.9],
            'riesgo_bajo' => [1.0, 5.0],
            'riesgo_medio' => [5.1, 15.0],
            'riesgo_alto' => [15.1, 30.0],
            'riesgo_muy_alto' => [30.1, 100.0]
        ],
        'capacitacion' => [
            'sin_riesgo' => [0.0, 0.9],
            'riesgo_bajo' => [1.0, 16.7],
            'riesgo_medio' => [16.8, 25.0],
            'riesgo_alto' => [25.1, 50.0],
            'riesgo_muy_alto' => [50.1, 100.0]
        ],
        'participacion_manejo_cambio' => [
            'sin_riesgo' => [0.0, 16.7],
            'riesgo_bajo' => [16.8, 33.3],
            'riesgo_medio' => [33.4, 41.7],
            'riesgo_alto' => [41.8, 58.3],
            'riesgo_muy_alto' => [58.4, 100.0]
        ],
        'oportunidades_desarrollo' => [
            'sin_riesgo' => [0.0, 12.5],
            'riesgo_bajo' => [12.6, 25.0],
            'riesgo_medio' => [25.1, 37.5],
            'riesgo_alto' => [37.6, 56.3],
            'riesgo_muy_alto' => [56.4, 100.0]
        ],
        'control_autonomia_trabajo' => [
            'sin_riesgo' => [0.0, 33.3],
            'riesgo_bajo' => [33.4, 50.0],
            'riesgo_medio' => [50.1, 66.7],
            'riesgo_alto' => [66.8, 75.0],
            'riesgo_muy_alto' => [75.1, 100.0]
        ],
        'demandas_ambientales_esfuerzo_fisico' => [
            'sin_riesgo' => [0.0, 22.9],
            'riesgo_bajo' => [23.0, 31.3],
            'riesgo_medio' => [31.4, 39.6],
            'riesgo_alto' => [39.7, 47.9],
            'riesgo_muy_alto' => [48.0, 100.0]
        ],
        'demandas_emocionales' => [
            'sin_riesgo' => [0.0, 19.4],
            'riesgo_bajo' => [19.5, 27.8],
            'riesgo_medio' => [27.9, 38.9],
            'riesgo_alto' => [39.0, 47.2],
            'riesgo_muy_alto' => [47.3, 100.0]
        ],
        'demandas_cuantitativas' => [
            'sin_riesgo' => [0.0, 16.7],
            'riesgo_bajo' => [16.8, 33.3],
            'riesgo_medio' => [33.4, 41.7],
            'riesgo_alto' => [41.8, 50.0],
            'riesgo_muy_alto' => [50.1, 100.0]
        ],
        'influencia_trabajo_entorno_extralaboral' => [
            'sin_riesgo' => [0.0, 12.5],
            'riesgo_bajo' => [12.6, 25.0],
            'riesgo_medio' => [25.1, 31.3],
            'riesgo_alto' => [31.4, 50.0],
            'riesgo_muy_alto' => [50.1, 100.0]
        ],
        'demandas_carga_mental' => [
            'sin_riesgo' => [0.0, 50.0],
            'riesgo_bajo' => [50.1, 65.0],
            'riesgo_medio' => [65.1, 75.0],
            'riesgo_alto' => [75.1, 85.0],
            'riesgo_muy_alto' => [85.1, 100.0]
        ],
        'demandas_jornada_trabajo' => [
            'sin_riesgo' => [0.0, 25.0],
            'riesgo_bajo' => [25.1, 37.5],
            'riesgo_medio' => [37.6, 45.8],
            'riesgo_alto' => [45.9, 58.3],
            'riesgo_muy_alto' => [58.4, 100.0]
        ],
        'recompensas_pertenencia_estabilidad' => [
            'sin_riesgo' => [0.0, 0.9],
            'riesgo_bajo' => [1.0, 6.3],
            'riesgo_medio' => [6.4, 12.5],
            'riesgo_alto' => [12.6, 18.8],
            'riesgo_muy_alto' => [18.9, 100.0]
        ],
        'reconocimiento_compensacion' => [
            'sin_riesgo' => [0.0, 0.9],
            'riesgo_bajo' => [1.0, 12.5],
            'riesgo_medio' => [12.6, 25.0],
            'riesgo_alto' => [25.1, 37.5],
            'riesgo_muy_alto' => [37.6, 100.0]
        ]
    ];

    /**
     * Baremos para dominios - Tabla 32 (Forma B - auxiliares, operarios)
     */
    private static $baremosDominios = [
        'liderazgo_relaciones_sociales' => [
            'sin_riesgo' => [0.0, 8.3],
            'riesgo_bajo' => [8.4, 17.5],
            'riesgo_medio' => [17.6, 26.7],
            'riesgo_alto' => [26.8, 38.3],
            'riesgo_muy_alto' => [38.4, 100.0]
        ],
        'control' => [
            'sin_riesgo' => [0.0, 19.4],
            'riesgo_bajo' => [19.5, 26.4],
            'riesgo_medio' => [26.5, 34.7],
            'riesgo_alto' => [34.8, 43.1],
            'riesgo_muy_alto' => [43.2, 100.0]
        ],
        'demandas' => [
            'sin_riesgo' => [0.0, 26.9],
            'riesgo_bajo' => [27.0, 33.3],
            'riesgo_medio' => [33.4, 37.8],
            'riesgo_alto' => [37.9, 44.2],
            'riesgo_muy_alto' => [44.3, 100.0]
        ],
        'recompensas' => [
            'sin_riesgo' => [0.0, 2.5],
            'riesgo_bajo' => [2.6, 10.0],
            'riesgo_medio' => [10.1, 17.5],
            'riesgo_alto' => [17.6, 27.5],
            'riesgo_muy_alto' => [27.6, 100.0]
        ]
    ];

    /**
     * Baremo intralaboral total - Tabla 33 (Forma B - auxiliares, operarios)
     * CORREGIDO: Rangos actualizados según manual oficial página 12
     */
    private static $baremoTotal = [
        'sin_riesgo' => [0.0, 20.6],
        'riesgo_bajo' => [20.7, 26.0],
        'riesgo_medio' => [26.1, 31.2],
        'riesgo_alto' => [31.3, 38.7],
        'riesgo_muy_alto' => [38.8, 100.0]
    ];

    /**
     * Textos exactos de las preguntas según el Ministerio
     * Extraídos de texto.txt (entre corchetes)
     */
    private static $preguntas = [
        1 => 'El ruido en el lugar donde trabajo es molesto',
        2 => 'El ruido en el lugar donde trabajo es molesto', // Nota: hay duplicado en el archivo original (línea 2)
        3 => 'En el lugar donde trabajo hace mucho calor',
        4 => 'El aire en el lugar donde trabajo es fresco y agradable',
        5 => 'La luz del sitio donde trabajo es agradable',
        6 => 'El espacio donde trabajo es cómodo',
        7 => 'En mi trabajo me preocupa estar expuesto a sustancias químicas que afecten mi salud',
        8 => 'Mi trabajo me exige hacer mucho esfuerzo físico',
        9 => 'Los equipos o herramientas con los que trabajo son cómodos',
        10 => 'En mi trabajo me preocupa estar expuesto a microbios, animales o plantas que afecten mi salud',
        11 => 'Me preocupa accidentarme en mi trabajo',
        12 => 'El lugar donde trabajo es limpio y ordenado',
        13 => 'Por la cantidad de trabajo que tengo debo quedarme tiempo adicional',
        14 => 'Me alcanza el tiempo de trabajo para tener al día mis deberes',
        15 => 'Por la cantidad de trabajo que tengo debo trabajar sin parar',
        16 => 'Mi trabajo me exige hacer mucho esfuerzo mental',
        17 => 'Mi trabajo me exige estar muy concentrado',
        18 => 'Mi trabajo me exige memorizar mucha información',
        19 => 'En mi trabajo tengo que hacer cálculos matemáticos',
        20 => 'Mi trabajo requiere que me fije en pequeños detalles',
        21 => 'Trabajo en horario de noche',
        22 => 'En mi trabajo es posible tomar pausas para descansar',
        23 => 'Mi trabajo me exige laborar en días de descanso, festivos o fines de semana',
        24 => 'En mi trabajo puedo tomar fines de semana o días de descanso al mes',
        25 => 'Cuando estoy en casa sigo pensando en el trabajo',
        26 => 'Discuto con mi familia o amigos por causa de mi trabajo',
        27 => 'Debo atender asuntos de trabajo cuando estoy en casa',
        28 => 'Por mi trabajo el tiempo que paso con mi familia y amigos es muy poco',
        29 => 'En mi trabajo puedo hacer cosas nuevas',
        30 => 'Mi trabajo me permite desarrollar mis habilidades',
        31 => 'Mi trabajo me permite aplicar mis conocimientos',
        32 => 'Mi trabajo me permite aprender nuevas cosas',
        33 => 'Puedo tomar pausas cuando las necesito',
        34 => 'Puedo decidir cuánto trabajo hago en el día',
        35 => 'Puedo decidir la velocidad a la que trabajo',
        36 => 'Puedo cambiar el orden de las actividades en mi trabajo',
        37 => 'Puedo parar un momento mi trabajo para atender algún asunto personal',
        38 => 'Me explican claramente los cambios que ocurren en mi trabajo',
        39 => 'Puedo dar sugerencias sobre los cambios que ocurren en mi trabajo',
        40 => 'Cuando se presentan cambios en mi trabajo se tienen en cuenta mis ideas y sugerencias',
        41 => 'Me informan con claridad cuáles son mis funciones',
        42 => 'Me informan cuáles son las decisiones que puedo tomar en mi trabajo',
        43 => 'Me explican claramente los resultados que debo lograr en mi trabajo',
        44 => 'Me explican claramente los objetivos de mi trabajo',
        45 => 'Me informan claramente con quien puedo resolver los asuntos de trabajo',
        46 => 'La empresa me permite asistir a capacitaciones relacionadas con mi trabajo',
        47 => 'Recibo capacitación útil para hacer mi trabajo',
        48 => 'Recibo capacitación que me ayuda a hacer mejor mi trabajo',
        49 => 'Mi jefe ayuda a organizar mejor el trabajo',
        50 => 'Mi jefe tiene en cuenta mis puntos de vista y opiniones',
        51 => 'Mi jefe me anima para hacer mejor mi trabajo',
        52 => 'Mi jefe distribuye las tareas de forma que me facilita el trabajo',
        53 => 'Mi jefe me comunica a tiempo la información relacionada con el trabajo',
        54 => 'La orientación que me da mi jefe me ayuda a hacer mejor el trabajo',
        55 => 'Mi jefe me ayuda a progresar en el trabajo',
        56 => 'Mi jefe me ayuda a sentirme bien en el trabajo',
        57 => 'Mi jefe ayuda a solucionar los problemas que se presentan en el trabajo',
        58 => 'Mi jefe me trata con respeto',
        59 => 'Siento que puedo confiar en mi jefe',
        60 => 'Mi jefe me escucha cuando tengo problemas de trabajo',
        61 => 'Mi jefe me brinda su apoyo cuando lo necesito',
        62 => 'Me agrada el ambiente de mi grupo de trabajo',
        63 => 'En mi grupo de trabajo me tratan de forma respetuosa',
        64 => 'Siento que puedo confiar en mis compañeros de trabajo',
        65 => 'Me siento a gusto con mis compañeros de trabajo',
        66 => 'En mi grupo de trabajo algunas personas me maltratan',
        67 => 'Entre compañeros solucionamos los problemas de forma respetuosa',
        68 => 'Mi grupo de trabajo es muy unido',
        69 => 'Cuando tenemos que realizar trabajo de grupo los compañeros colaboran',
        70 => 'Es fácil poner de acuerdo al grupo para hacer el trabajo',
        71 => 'Mis compañeros de trabajo me ayudan cuando tengo dificultades',
        72 => 'En mi trabajo las personas nos apoyamos unos a otros',
        73 => 'Algunos compañeros de trabajo me escuchan cuando tengo problemas',
        74 => 'Me informan sobre lo que hago bien en mi trabajo',
        75 => 'Me informan sobre lo que debo mejorar en mi trabajo',
        76 => 'La información que recibo sobre mi rendimiento en el trabajo es clara',
        77 => 'La forma como evalúan mi trabajo en la empresa me ayuda a mejorar',
        78 => 'Me informan a tiempo sobre lo que debo mejorar en el trabajo',
        79 => 'En la empresa me pagan a tiempo mi salario',
        80 => 'El pago que recibo es el que me ofreció la empresa',
        81 => 'El pago que recibo es el que merezco por el trabajo que realizo',
        82 => 'En mi trabajo tengo posibilidades de progresar',
        83 => 'Las personas que hacen bien el trabajo pueden progresar en la empresa',
        84 => 'La empresa se preocupa por el bienestar de los trabajadores',
        85 => 'Mi trabajo en la empresa es estable',
        86 => 'El trabajo que hago me hace sentir bien',
        87 => 'Siento orgullo de trabajar en esta empresa',
        88 => 'Hablo bien de la empresa con otras personas',
        89 => 'Atiendo clientes o usuarios muy enojados',
        90 => 'Atiendo clientes o usuarios muy preocupados',
        91 => 'Atiendo clientes o usuarios muy tristes',
        92 => 'Mi trabajo me exige atender personas muy enfermas',
        93 => 'Mi trabajo me exige atender personas muy necesitadas de ayuda',
        94 => 'Atiendo clientes o usuarios que me maltratan',
        95 => 'Mi trabajo me exige atender situaciones de violencia',
        96 => 'Mi trabajo me exige atender situaciones muy tristes o dolorosas',
        97 => 'Puedo expresar tristeza o enojo frente a las personas que atiendo'
    ];

    /**
     * Califica el cuestionario Intralaboral Forma B
     *
     * @param array $respuestas Array asociativo [numero_pregunta => valor_respuesta]
     *                          Valores: 0=Siempre, 1=Casi siempre, 2=Algunas veces, 3=Casi nunca, 4=Nunca
     * @param bool $atiendeClientes Si el trabajador atiende clientes (para preguntas 89-97)
     *
     * @return array [
     *   'puntajes_brutos_dimensiones' => [],
     *   'puntajes_transformados_dimensiones' => [],
     *   'niveles_riesgo_dimensiones' => [],
     *   'puntajes_brutos_dominios' => [],
     *   'puntajes_transformados_dominios' => [],
     *   'niveles_riesgo_dominios' => [],
     *   'puntaje_bruto_total' => float,
     *   'puntaje_transformado_total' => float,
     *   'nivel_riesgo_total' => string,
     *   'atiende_clientes' => bool
     * ]
     */
    public static function calificar($respuestas, $atiendeClientes = false)
    {
        // 1. Calificar ítems (aplicar scoring normal o inverso)
        $puntajesItems = self::calificarItems($respuestas);

        // 2. Calcular puntajes brutos de dimensiones
        $puntajesBrutosDimensiones = self::calcularPuntajesBrutosDimensiones(
            $puntajesItems,
            $atiendeClientes
        );

        // 3. Transformar puntajes de dimensiones
        $puntajesTransformadosDimensiones = self::transformarPuntajesDimensiones(
            $puntajesBrutosDimensiones,
            $atiendeClientes
        );

        // 4. Determinar niveles de riesgo para dimensiones
        $nivelesRiesgoDimensiones = self::determinarNivelesRiesgoDimensiones(
            $puntajesTransformadosDimensiones
        );

        // 5. Calcular puntajes brutos de dominios
        $puntajesBrutosDominios = self::calcularPuntajesBrutosDominios(
            $puntajesBrutosDimensiones,
            $atiendeClientes
        );

        // 6. Transformar puntajes de dominios
        $puntajesTransformadosDominios = self::transformarPuntajesDominios(
            $puntajesBrutosDominios,
            $atiendeClientes
        );

        // 7. Determinar niveles de riesgo para dominios
        $nivelesRiesgoDominios = self::determinarNivelesRiesgoDominios(
            $puntajesTransformadosDominios
        );

        // 8. Calcular puntaje bruto total
        $puntajeBrutoTotal = self::calcularPuntajeBrutoTotal(
            $puntajesBrutosDominios
        );

        // 9. Transformar puntaje total
        $puntajeTransformadoTotal = self::transformarPuntajeTotal(
            $puntajeBrutoTotal,
            $atiendeClientes
        );

        // 10. Determinar nivel de riesgo total
        $nivelRiesgoTotal = self::determinarNivelRiesgoTotal($puntajeTransformadoTotal);

        return [
            'puntajes_brutos_dimensiones' => $puntajesBrutosDimensiones,
            'puntajes_transformados_dimensiones' => $puntajesTransformadosDimensiones,
            'niveles_riesgo_dimensiones' => $nivelesRiesgoDimensiones,
            'puntajes_brutos_dominios' => $puntajesBrutosDominios,
            'puntajes_transformados_dominios' => $puntajesTransformadosDominios,
            'niveles_riesgo_dominios' => $nivelesRiesgoDominios,
            'puntaje_bruto_total' => $puntajeBrutoTotal,
            'puntaje_transformado_total' => $puntajeTransformadoTotal,
            'nivel_riesgo_total' => $nivelRiesgoTotal,
            'atiende_clientes' => $atiendeClientes
        ];
    }

    /**
     * Procesa los ítems - Los valores YA vienen calificados de la BD según Tabla 22
     *
     * IMPORTANTE: Según el manual oficial (Paso 1. Calificación de los ítems),
     * la calificación (inversión) se aplica AL MOMENTO DE GUARDAR en la BD.
     *
     * Este método solo retorna los valores tal cual vienen de la BD.
     */
    private static function calificarItems($respuestas)
    {
        $puntajes = [];

        foreach ($respuestas as $numPregunta => $valorRespuesta) {
            // Los valores YA están calificados (0-4) según Tabla 22
            // No se aplica inversión aquí
            $puntajes[$numPregunta] = $valorRespuesta;
        }

        return $puntajes;
    }

    /**
     * Calcula puntajes brutos para cada dimensión
     *
     * IMPORTANTE: Según manual oficial página 5:
     * "Para calificar una dimensión se requiere que se haya respondido la totalidad
     * de los ítems que la conforman. Si uno o más ítems no fueron contestados,
     * no se podrá obtener el puntaje de esa dimensión"
     */
    private static function calcularPuntajesBrutosDimensiones($puntajesItems, $atiendeClientes)
    {
        $puntajes = [];

        foreach (self::$dimensiones as $dimension => $items) {
            // Omitir dimensiones condicionales si no aplican
            if ($dimension === 'demandas_emocionales' && !$atiendeClientes) {
                $puntajes[$dimension] = null;
                continue;
            }

            // Validar que TODOS los ítems de la dimensión estén respondidos
            $suma = 0;
            $itemsRespondidos = 0;
            foreach ($items as $item) {
                if (isset($puntajesItems[$item]) && $puntajesItems[$item] !== null) {
                    $suma += $puntajesItems[$item];
                    $itemsRespondidos++;
                }
            }

            // Si no se respondieron TODOS los ítems, la dimensión es inválida
            if ($itemsRespondidos < count($items)) {
                $puntajes[$dimension] = null;
            } else {
                $puntajes[$dimension] = $suma;
            }
        }

        return $puntajes;
    }

    /**
     * Transforma puntajes brutos de dimensiones a escala 0-100
     * Fórmula: (Puntaje bruto / Factor de transformación) × 100
     */
    private static function transformarPuntajesDimensiones($puntajesBrutos, $atiendeClientes)
    {
        $transformados = [];

        foreach ($puntajesBrutos as $dimension => $puntajeBruto) {
            if ($puntajeBruto === null) {
                $transformados[$dimension] = null;
                continue;
            }

            $factor = self::$factoresTransformacionDimensiones[$dimension];
            $transformados[$dimension] = round(($puntajeBruto / $factor) * 100, 1);
        }

        return $transformados;
    }

    /**
     * Determina el nivel de riesgo para cada dimensión según baremos
     */
    private static function determinarNivelesRiesgoDimensiones($puntajesTransformados)
    {
        $niveles = [];

        foreach ($puntajesTransformados as $dimension => $puntaje) {
            if ($puntaje === null) {
                $niveles[$dimension] = null;
                continue;
            }

            $baremos = self::$baremosDimensiones[$dimension];
            $niveles[$dimension] = self::determinarNivel($puntaje, $baremos);
        }

        return $niveles;
    }

    /**
     * Calcula puntajes brutos para cada dominio
     */
    private static function calcularPuntajesBrutosDominios($puntajesDimensiones, $atiendeClientes)
    {
        $puntajes = [];

        foreach (self::$dominios as $dominio => $dimensiones) {
            $suma = 0;
            foreach ($dimensiones as $dimension) {
                $valor = $puntajesDimensiones[$dimension];
                if ($valor !== null) {
                    $suma += $valor;
                }
            }
            $puntajes[$dominio] = $suma;
        }

        return $puntajes;
    }

    /**
     * Transforma puntajes brutos de dominios a escala 0-100
     * Fórmula: (Puntaje bruto / Factor de transformación) × 100
     */
    private static function transformarPuntajesDominios($puntajesBrutos, $atiendeClientes)
    {
        $transformados = [];

        foreach ($puntajesBrutos as $dominio => $puntajeBruto) {
            $factor = self::$factoresTransformacionDominios[$dominio];
            $transformados[$dominio] = round(($puntajeBruto / $factor) * 100, 1);
        }

        return $transformados;
    }

    /**
     * Determina el nivel de riesgo para cada dominio según baremos
     */
    private static function determinarNivelesRiesgoDominios($puntajesTransformados)
    {
        $niveles = [];

        foreach ($puntajesTransformados as $dominio => $puntaje) {
            $baremos = self::$baremosDominios[$dominio];
            $niveles[$dominio] = self::determinarNivel($puntaje, $baremos);
        }

        return $niveles;
    }

    /**
     * Calcula puntaje bruto total sumando todos los dominios
     */
    private static function calcularPuntajeBrutoTotal($puntajesDominios)
    {
        return array_sum($puntajesDominios);
    }

    /**
     * Transforma puntaje total a escala 0-100
     * Fórmula: (Puntaje bruto / Factor de transformación) × 100
     */
    private static function transformarPuntajeTotal($puntajeBruto, $atiendeClientes)
    {
        $factor = self::$factorTransformacionTotal;
        return round(($puntajeBruto / $factor) * 100, 1);
    }

    /**
     * Determina el nivel de riesgo total según baremo
     */
    private static function determinarNivelRiesgoTotal($puntajeTransformado)
    {
        return self::determinarNivel($puntajeTransformado, self::$baremoTotal);
    }

    /**
     * Determina el nivel de riesgo dado un puntaje y un conjunto de baremos
     */
    private static function determinarNivel($puntaje, $baremos)
    {
        foreach ($baremos as $nivel => $rango) {
            if ($puntaje >= $rango[0] && $puntaje <= $rango[1]) {
                return $nivel;
            }
        }
        return 'sin_riesgo'; // Default
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
}
