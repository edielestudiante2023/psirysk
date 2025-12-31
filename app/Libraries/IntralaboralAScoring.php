<?php

namespace App\Libraries;

/**
 * Librería de calificación para Cuestionario Intralaboral Forma A
 * Basado en los documentos oficiales del Ministerio de la Protección Social de Colombia
 *
 * - Tabla 21: Calificación de ítems Forma A
 * - Tabla 23: Dimensiones e ítems
 * - Tabla 25: Factores de transformación dimensiones Forma A
 * - Tabla 26: Factores de transformación dominios
 * - Tabla 27: Factor de transformación total intralaboral
 * - Tabla 29: Baremos dimensiones Forma A (jefes, profesionales, técnicos)
 * - Tabla 31: Baremos dominios Forma A (jefes, profesionales, técnicos)
 * - Tabla 33: Baremo intralaboral total Forma A (jefes, profesionales, técnicos)
 */
class IntralaboralAScoring
{
    /**
     * Preguntas con calificación NORMAL (Siempre=0, Casi siempre=1, Algunas veces=2, Casi nunca=3, Nunca=4)
     * Grupo 1 según Tabla 21 - Ministerio de la Protección Social
     */
    private static $itemsGrupoNormal = [
        4, 5, 6, 9, 12, 14, 32, 34, 39, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, 53, 54, 55, 56, 57, 58, 59,
        60, 61, 62, 63, 64, 65, 66, 67, 68, 69, 70, 71, 72, 73, 74, 75, 76, 77, 78, 79, 81, 82, 83, 84, 85, 86, 87,
        88, 89, 90, 91, 92, 93, 94, 95, 96, 97, 98, 99, 100, 101, 102, 103, 104, 105
    ];

    /**
     * Preguntas con calificación INVERSA (Siempre=4, Casi siempre=3, Algunas veces=2, Casi nunca=1, Nunca=0)
     * Grupo 2 según Tabla 21 - Ministerio de la Protección Social
     */
    private static $itemsGrupoInverso = [
        1, 2, 3, 7, 8, 10, 11, 13, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 33, 35, 36,
        37, 38, 52, 80, 106, 107, 108, 109, 110, 111, 112, 113, 114, 115, 116, 117, 118, 119, 120, 121, 122, 123
    ];

    /**
     * Mapeo de dimensiones e ítems según Tabla 23
     */
    private static $dimensiones = [
        'caracteristicas_liderazgo' => [63, 64, 65, 66, 67, 68, 69, 70, 71, 72, 73, 74, 75],
        'relaciones_sociales_trabajo' => [76, 77, 78, 79, 80, 81, 82, 83, 84, 85, 86, 87, 88, 89],
        'retroalimentacion_desempeno' => [90, 91, 92, 93, 94],
        'relacion_con_colaboradores' => [115, 116, 117, 118, 119, 120, 121, 122, 123], // Condicional: solo si es_jefe=true
        'claridad_rol' => [53, 54, 55, 56, 57, 58, 59],
        'capacitacion' => [60, 61, 62],
        'participacion_manejo_cambio' => [48, 49, 50, 51],
        'oportunidades_desarrollo' => [39, 40, 41, 42],
        'control_autonomia_trabajo' => [44, 45, 46],
        'demandas_ambientales_esfuerzo_fisico' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
        'demandas_emocionales' => [106, 107, 108, 109, 110, 111, 112, 113, 114], // Condicional: solo si atiende_clientes=true
        'demandas_cuantitativas' => [13, 14, 15, 32, 43, 47],
        'influencia_trabajo_entorno_extralaboral' => [35, 36, 37, 38],
        'exigencias_responsabilidad_cargo' => [19, 22, 23, 24, 25, 26],
        'demandas_carga_mental' => [16, 17, 18, 20, 21],
        'consistencia_rol' => [27, 28, 29, 30, 52],
        'demandas_jornada_trabajo' => [31, 33, 34],
        'recompensas_pertenencia_estabilidad' => [95, 102, 103, 104, 105],
        'reconocimiento_compensacion' => [96, 97, 98, 99, 100, 101]
    ];

    /**
     * Factores de transformación para dimensiones - Tabla 25 (Forma A)
     */
    private static $factoresTransformacionDimensiones = [
        'caracteristicas_liderazgo' => 52,
        'relaciones_sociales_trabajo' => 56,
        'retroalimentacion_desempeno' => 20,
        'relacion_con_colaboradores' => 36,
        'claridad_rol' => 28,
        'capacitacion' => 12,
        'participacion_manejo_cambio' => 16,
        'oportunidades_desarrollo' => 16,
        'control_autonomia_trabajo' => 12,
        'demandas_ambientales_esfuerzo_fisico' => 48,
        'demandas_emocionales' => 36,
        'demandas_cuantitativas' => 24,
        'influencia_trabajo_entorno_extralaboral' => 16,
        'exigencias_responsabilidad_cargo' => 24,
        'demandas_carga_mental' => 20,
        'consistencia_rol' => 20,
        'demandas_jornada_trabajo' => 12,
        'recompensas_pertenencia_estabilidad' => 20,
        'reconocimiento_compensacion' => 24
    ];

    /**
     * Mapeo de dominios y sus dimensiones según Tabla 23
     */
    private static $dominios = [
        'liderazgo_relaciones_sociales' => [
            'caracteristicas_liderazgo',
            'relaciones_sociales_trabajo',
            'retroalimentacion_desempeno',
            'relacion_con_colaboradores'
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
            'exigencias_responsabilidad_cargo',
            'demandas_carga_mental',
            'consistencia_rol',
            'demandas_jornada_trabajo'
        ],
        'recompensas' => [
            'recompensas_pertenencia_estabilidad',
            'reconocimiento_compensacion'
        ]
    ];

    /**
     * Factores de transformación para dominios - Tabla 26 (Forma A)
     */
    private static $factoresTransformacionDominios = [
        'liderazgo_relaciones_sociales' => 164,
        'control' => 84,
        'demandas' => 200,
        'recompensas' => 44
    ];

    /**
     * Factor de transformación total intralaboral - Tabla 27 (Forma A)
     */
    private static $factorTransformacionTotal = 492;

    /**
     * Baremos para dimensiones - Tabla 29 (Forma A - jefes, profesionales, técnicos)
     */
    private static $baremosDimensiones = [
        'caracteristicas_liderazgo' => [
            'sin_riesgo' => [0.0, 3.8],
            'riesgo_bajo' => [3.9, 15.4],
            'riesgo_medio' => [15.5, 30.8],
            'riesgo_alto' => [30.9, 46.2],
            'riesgo_muy_alto' => [46.3, 100.0]
        ],
        'relaciones_sociales_trabajo' => [
            'sin_riesgo' => [0.0, 5.4],
            'riesgo_bajo' => [5.5, 16.1],
            'riesgo_medio' => [16.2, 25.0],
            'riesgo_alto' => [25.1, 37.5],
            'riesgo_muy_alto' => [37.6, 100.0]
        ],
        'retroalimentacion_desempeno' => [
            'sin_riesgo' => [0.0, 10.0],
            'riesgo_bajo' => [10.1, 25.0],
            'riesgo_medio' => [25.1, 40.0],
            'riesgo_alto' => [40.1, 55.0],
            'riesgo_muy_alto' => [55.1, 100.0]
        ],
        'relacion_con_colaboradores' => [
            'sin_riesgo' => [0.0, 13.9],
            'riesgo_bajo' => [14.0, 25.0],
            'riesgo_medio' => [25.1, 33.3],
            'riesgo_alto' => [33.4, 47.2],
            'riesgo_muy_alto' => [47.3, 100.0]
        ],
        'claridad_rol' => [
            'sin_riesgo' => [0.0, 0.9],
            'riesgo_bajo' => [1.0, 10.7],
            'riesgo_medio' => [10.8, 21.4],
            'riesgo_alto' => [21.5, 39.3],
            'riesgo_muy_alto' => [39.4, 100.0]
        ],
        'capacitacion' => [
            'sin_riesgo' => [0.0, 0.9],
            'riesgo_bajo' => [1.0, 16.7],
            'riesgo_medio' => [16.8, 33.3],
            'riesgo_alto' => [33.4, 50.0],
            'riesgo_muy_alto' => [50.1, 100.0]
        ],
        'participacion_manejo_cambio' => [
            'sin_riesgo' => [0.0, 12.5],
            'riesgo_bajo' => [12.6, 25.0],
            'riesgo_medio' => [25.1, 37.5],
            'riesgo_alto' => [37.6, 50.0],
            'riesgo_muy_alto' => [50.1, 100.0]
        ],
        'oportunidades_desarrollo' => [
            'sin_riesgo' => [0.0, 0.9],
            'riesgo_bajo' => [1.0, 6.3],
            'riesgo_medio' => [6.4, 18.8],
            'riesgo_alto' => [18.9, 31.3],
            'riesgo_muy_alto' => [31.4, 100.0]
        ],
        'control_autonomia_trabajo' => [
            'sin_riesgo' => [0.0, 8.3],
            'riesgo_bajo' => [8.4, 25.0],
            'riesgo_medio' => [25.1, 41.7],
            'riesgo_alto' => [41.8, 58.3],
            'riesgo_muy_alto' => [58.4, 100.0]
        ],
        'demandas_ambientales_esfuerzo_fisico' => [
            'sin_riesgo' => [0.0, 14.6],
            'riesgo_bajo' => [14.7, 22.9],
            'riesgo_medio' => [23.0, 31.3],
            'riesgo_alto' => [31.4, 39.6],
            'riesgo_muy_alto' => [39.7, 100.0]
        ],
        'demandas_emocionales' => [
            'sin_riesgo' => [0.0, 16.7],
            'riesgo_bajo' => [16.8, 25.0],
            'riesgo_medio' => [25.1, 33.3],
            'riesgo_alto' => [33.4, 47.2],
            'riesgo_muy_alto' => [47.3, 100.0]
        ],
        'demandas_cuantitativas' => [
            'sin_riesgo' => [0.0, 25.0],
            'riesgo_bajo' => [25.1, 33.3],
            'riesgo_medio' => [33.4, 45.8],
            'riesgo_alto' => [45.9, 54.2],
            'riesgo_muy_alto' => [54.3, 100.0]
        ],
        'influencia_trabajo_entorno_extralaboral' => [
            'sin_riesgo' => [0.0, 18.8],
            'riesgo_bajo' => [18.9, 31.3],
            'riesgo_medio' => [31.4, 43.8],
            'riesgo_alto' => [43.9, 50.0],
            'riesgo_muy_alto' => [50.1, 100.0]
        ],
        'exigencias_responsabilidad_cargo' => [
            'sin_riesgo' => [0.0, 37.5],
            'riesgo_bajo' => [37.6, 54.2],
            'riesgo_medio' => [54.3, 66.7],
            'riesgo_alto' => [66.8, 79.2],
            'riesgo_muy_alto' => [79.3, 100.0]
        ],
        'demandas_carga_mental' => [
            'sin_riesgo' => [0.0, 60.0],
            'riesgo_bajo' => [60.1, 70.0],
            'riesgo_medio' => [70.1, 80.0],
            'riesgo_alto' => [80.1, 90.0],
            'riesgo_muy_alto' => [90.1, 100.0]
        ],
        'consistencia_rol' => [
            'sin_riesgo' => [0.0, 15.0],
            'riesgo_bajo' => [15.1, 25.0],
            'riesgo_medio' => [25.1, 35.0],
            'riesgo_alto' => [35.1, 45.0],
            'riesgo_muy_alto' => [45.1, 100.0]
        ],
        'demandas_jornada_trabajo' => [
            'sin_riesgo' => [0.0, 8.3],
            'riesgo_bajo' => [8.4, 25.0],
            'riesgo_medio' => [25.1, 33.3],
            'riesgo_alto' => [33.4, 50.0],
            'riesgo_muy_alto' => [50.1, 100.0]
        ],
        'recompensas_pertenencia_estabilidad' => [
            'sin_riesgo' => [0.0, 0.9],
            'riesgo_bajo' => [1.0, 5.0],
            'riesgo_medio' => [5.1, 10.0],
            'riesgo_alto' => [10.1, 20.0],
            'riesgo_muy_alto' => [20.1, 100.0]
        ],
        'reconocimiento_compensacion' => [
            'sin_riesgo' => [0.0, 4.2],
            'riesgo_bajo' => [4.3, 16.7],
            'riesgo_medio' => [16.8, 25.0],
            'riesgo_alto' => [25.1, 37.5],
            'riesgo_muy_alto' => [37.6, 100.0]
        ]
    ];

    /**
     * Baremos para dominios - Tabla 31 (Forma A - jefes, profesionales, técnicos)
     */
    private static $baremosDominios = [
        'liderazgo_relaciones_sociales' => [
            'sin_riesgo' => [0.0, 9.1],
            'riesgo_bajo' => [9.2, 17.7],
            'riesgo_medio' => [17.8, 25.6],
            'riesgo_alto' => [25.7, 34.8],
            'riesgo_muy_alto' => [34.9, 100.0]
        ],
        'control' => [
            'sin_riesgo' => [0.0, 10.7],
            'riesgo_bajo' => [10.8, 19.0],
            'riesgo_medio' => [19.1, 29.8],
            'riesgo_alto' => [29.9, 40.5],
            'riesgo_muy_alto' => [40.6, 100.0]
        ],
        'demandas' => [
            'sin_riesgo' => [0.0, 28.5],
            'riesgo_bajo' => [28.6, 35.0],
            'riesgo_medio' => [35.1, 41.5],
            'riesgo_alto' => [41.6, 47.5],
            'riesgo_muy_alto' => [47.6, 100.0]
        ],
        'recompensas' => [
            'sin_riesgo' => [0.0, 4.5],
            'riesgo_bajo' => [4.6, 11.4],
            'riesgo_medio' => [11.5, 20.5],
            'riesgo_alto' => [20.6, 29.5],
            'riesgo_muy_alto' => [29.6, 100.0]
        ]
    ];

    /**
     * Baremo intralaboral total - Tabla 33 (Forma A - jefes, profesionales, técnicos)
     * CORREGIDO: Rangos actualizados según manual oficial página 12
     */
    private static $baremoTotal = [
        'sin_riesgo' => [0.0, 19.7],
        'riesgo_bajo' => [19.8, 25.8],
        'riesgo_medio' => [25.9, 31.5],
        'riesgo_alto' => [31.6, 38.0],
        'riesgo_muy_alto' => [38.1, 100.0]
    ];

    /**
     * Textos exactos de las preguntas según el Ministerio
     * Extraídos de texto.txt (entre corchetes)
     */
    private static $preguntas = [
        1 => 'El ruido en el lugar donde trabajo es molesto',
        2 => 'En el lugar donde trabajo hace mucho frío',
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
        19 => 'En mi trabajo tengo que tomar decisiones difíciles muy rápido',
        20 => 'Mi trabajo me exige atender a muchos asuntos al mismo tiempo',
        21 => 'Mi trabajo requiere que me fije en pequeños detalles',
        22 => 'En mi trabajo respondo por cosas de mucho valor',
        23 => 'En mi trabajo respondo por dinero de la empresa',
        24 => 'Como parte de mis funciones debo responder por la seguridad de otros',
        25 => 'Respondo ante mi jefe por los resultados de toda mi área de trabajo',
        26 => 'Mi trabajo me exige cuidar la salud de otras personas',
        27 => 'En el trabajo me dan órdenes contradictorias',
        28 => 'En mi trabajo me piden hacer cosas innecesarias',
        29 => 'En mi trabajo se presentan situaciones en las que debo pasar por alto normas o procedimientos',
        30 => 'En mi trabajo tengo que hacer cosas que se podrían hacer de una forma más práctica',
        31 => 'Trabajo en horario de noche',
        32 => 'En mi trabajo es posible tomar pausas para descansar',
        33 => 'Mi trabajo me exige laborar en días de descanso, festivos o fines de semana',
        34 => 'En mi trabajo puedo tomar fines de semana o días de descanso al mes',
        35 => 'Cuando estoy en casa sigo pensando en el trabajo',
        36 => 'Discuto con mi familia o amigos por causa de mi trabajo',
        37 => 'Debo atender asuntos de trabajo cuando estoy en casa',
        38 => 'Por mi trabajo el tiempo que paso con mi familia y amigos es muy poco',
        39 => 'Mi trabajo me permite desarrollar mis habilidades',
        40 => 'Mi trabajo me permite aplicar mis conocimientos',
        41 => 'Mi trabajo me permite aprender nuevas cosas',
        42 => 'Me asignan el trabajo teniendo en cuenta mis capacidades.',
        43 => 'Puedo tomar pausas cuando las necesito',
        44 => 'Puedo decidir cuánto trabajo hago en el día',
        45 => 'Puedo decidir la velocidad a la que trabajo',
        46 => 'Puedo cambiar el orden de las actividades en mi trabajo',
        47 => 'Puedo parar un momento mi trabajo para atender algún asunto personal',
        48 => 'Los cambios en mi trabajo han sido beneficiosos',
        49 => 'Me explican claramente los cambios que ocurren en mi trabajo',
        50 => 'Puedo dar sugerencias sobre los cambios que ocurren en mi trabajo',
        51 => 'Cuando se presentan cambios en mi trabajo se tienen en cuenta mis ideas y sugerencias',
        52 => 'Los cambios que se presentan en mi trabajo dificultan mi labor',
        53 => 'Me informan con claridad cuáles son mis funciones',
        54 => 'Me informan cuáles son las decisiones que puedo tomar en mi trabajo',
        55 => 'Me explican claramente los resultados que debo lograr en mi trabajo',
        56 => 'Me explican claramente el efecto de mi trabajo en la empresa',
        57 => 'Me explican claramente los objetivos de mi trabajo',
        58 => 'Me informan claramente quien me puede orientar para hacer mi trabajo',
        59 => 'Me informan claramente con quien puedo resolver los asuntos de trabajo',
        60 => 'La empresa me permite asistir a capacitaciones relacionadas con mi trabajo',
        61 => 'Recibo capacitación útil para hacer mi trabajo',
        62 => 'Recibo capacitación que me ayuda a hacer mejor mi trabajo',
        63 => 'Mi jefe me da instrucciones claras',
        64 => 'Mi jefe ayuda a organizar mejor el trabajo',
        65 => 'Mi jefe tiene en cuenta mis puntos de vista y opiniones',
        66 => 'Mi jefe me anima para hacer mejor mi trabajo',
        67 => 'Mi jefe distribuye las tareas de forma que me facilita el trabajo',
        68 => 'Mi jefe me comunica a tiempo la información relacionada con el trabajo',
        69 => 'La orientación que me da mi jefe me ayuda a hacer mejor el trabajo',
        70 => 'Mi jefe me ayuda a progresar en el trabajo',
        71 => 'Mi jefe me ayuda a sentirme bien en el trabajo',
        72 => 'Mi jefe ayuda a solucionar los problemas que se presentan en el trabajo',
        73 => 'Siento que puedo confiar en mi jefe',
        74 => 'Mi jefe me escucha cuando tengo problemas de trabajo',
        75 => 'Mi jefe me brinda su apoyo cuando lo necesito',
        76 => 'Me agrada el ambiente de mi grupo de trabajo',
        77 => 'En mi grupo de trabajo me tratan de forma respetuosa',
        78 => 'Siento que puedo confiar en mis compañeros de trabajo',
        79 => 'Me siento a gusto con mis compañeros de trabajo',
        80 => 'En mi grupo de trabajo algunas personas me maltratan',
        81 => 'Entre compañeros solucionamos los problemas de forma respetuosa',
        82 => 'Hay integración en mi grupo de trabajo',
        83 => 'Mi grupo de trabajo es muy unido',
        84 => 'Las personas en mi trabajo me hacen sentir parte del grupo',
        85 => 'Cuando tenemos que realizar trabajo de grupo los compañeros colaboran',
        86 => 'Es fácil poner de acuerdo al grupo para hacer el trabajo',
        87 => 'Mis compañeros de trabajo me ayudan cuando tengo dificultades',
        88 => 'En mi trabajo las personas nos apoyamos unos a otros',
        89 => 'Algunos compañeros de trabajo me escuchan cuando tengo problemas',
        90 => 'Me informan sobre lo que hago bien en mi trabajo',
        91 => 'Me informan sobre lo que debo mejorar en mi trabajo',
        92 => 'La información que recibo sobre mi rendimiento en el trabajo es clara',
        93 => 'La forma como evalúan mi trabajo en la empresa me ayuda a mejorar',
        94 => 'Me informan a tiempo sobre lo que debo mejorar en el trabajo',
        95 => 'En la empresa confían en mi trabajo',
        96 => 'En la empresa me pagan a tiempo mi salario',
        97 => 'El pago que recibo es el que me ofreció la empresa',
        98 => 'El pago que recibo es el que merezco por el trabajo que realizo',
        99 => 'En mi trabajo tengo posibilidades de progresar',
        100 => 'Las personas que hacen bien el trabajo pueden progresar en la empresa',
        101 => 'La empresa se preocupa por el bienestar de los trabajadores',
        102 => 'Mi trabajo en la empresa es estable',
        103 => 'El trabajo que hago me hace sentir bien',
        104 => 'Siento orgullo de trabajar en esta empresa',
        105 => 'Hablo bien de la empresa con otras personas',
        106 => 'Atiendo clientes o usuarios muy enojados',
        107 => 'Atiendo clientes o usuarios muy preocupados',
        108 => 'Atiendo clientes o usuarios muy tristes',
        109 => 'Mi trabajo me exige atender personas muy enfermas',
        110 => 'Mi trabajo me exige atender personas muy necesitadas de ayuda',
        111 => 'Atiendo clientes o usuarios que me maltratan',
        112 => 'Para hacer mi trabajo debo demostrar sentimientos distintos a los míos',
        113 => 'Mi trabajo me exige atender situaciones de violencia',
        114 => 'Mi trabajo me exige atender situaciones muy tristes o dolorosas',
        115 => 'Tengo colaboradores que comunican tarde los asuntos de trabajo',
        116 => 'Tengo colaboradores que tienen comportamientos irrespetuosos',
        117 => 'Tengo colaboradores que dificultan la organización del trabajo',
        118 => 'Tengo colaboradores que guardan silencio cuando les piden opiniones',
        119 => 'Tengo colaboradores que dificultan el logro de los resultados del trabajo',
        120 => 'Tengo colaboradores que expresan de forma irrespetuosa sus desacuerdos',
        121 => 'Tengo colaboradores que cooperan poco cuando se necesita',
        122 => 'Tengo colaboradores que me preocupan por su desempeño',
        123 => 'Tengo colaboradores que ignoran las sugerencias para mejorar su trabajo'
    ];

    /**
     * Califica el cuestionario Intralaboral Forma A
     *
     * @param array $respuestas Array asociativo [numero_pregunta => valor_respuesta]
     *                          Valores: 0=Siempre, 1=Casi siempre, 2=Algunas veces, 3=Casi nunca, 4=Nunca
     * @param bool $atiendeClientes Si el trabajador atiende clientes (para preguntas 106-114)
     * @param bool $esJefe Si el trabajador es jefe (para preguntas 115-123)
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
     *   'atiende_clientes' => bool,
     *   'es_jefe' => bool
     * ]
     */
    public static function calificar($respuestas, $atiendeClientes = false, $esJefe = false)
    {
        // 1. Calificar ítems (aplicar scoring normal o inverso)
        $puntajesItems = self::calificarItems($respuestas);

        // 2. Calcular puntajes brutos de dimensiones
        $puntajesBrutosDimensiones = self::calcularPuntajesBrutosDimensiones(
            $puntajesItems,
            $atiendeClientes,
            $esJefe
        );

        // 3. Transformar puntajes de dimensiones
        $puntajesTransformadosDimensiones = self::transformarPuntajesDimensiones(
            $puntajesBrutosDimensiones,
            $atiendeClientes,
            $esJefe
        );

        // 4. Determinar niveles de riesgo para dimensiones
        $nivelesRiesgoDimensiones = self::determinarNivelesRiesgoDimensiones(
            $puntajesTransformadosDimensiones
        );

        // 5. Calcular puntajes brutos de dominios
        $puntajesBrutosDominios = self::calcularPuntajesBrutosDominios(
            $puntajesBrutosDimensiones,
            $atiendeClientes,
            $esJefe
        );

        // 6. Transformar puntajes de dominios
        $puntajesTransformadosDominios = self::transformarPuntajesDominios(
            $puntajesBrutosDominios,
            $atiendeClientes,
            $esJefe
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
            $atiendeClientes,
            $esJefe
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
            'atiende_clientes' => $atiendeClientes,
            'es_jefe' => $esJefe
        ];
    }

    /**
     * Procesa los ítems - Los valores YA vienen calificados de la BD según Tabla 21
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
            // Aplicar calificación según si el ítem es normal o inverso (Tabla 21)
            // Los valores de respuesta (0-4) representan: 0=Siempre, 1=Casi siempre, 2=Algunas veces, 3=Casi nunca, 4=Nunca
            // Grupo 1 (Normal): Siempre=0, Casi siempre=1, Algunas veces=2, Casi nunca=3, Nunca=4
            // Grupo 2 (Inverso): Siempre=4, Casi siempre=3, Algunas veces=2, Casi nunca=1, Nunca=0

            if (in_array($numPregunta, self::$itemsGrupoInverso)) {
                // Ítem inverso: invertir el valor (4-valor)
                $puntajes[$numPregunta] = 4 - (int)$valorRespuesta;
            } else {
                // Ítem normal: usar el valor directo
                $puntajes[$numPregunta] = (int)$valorRespuesta;
            }
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
     *
     * REGLA ESPECIAL (Cartilla Ministerio):
     * Si el trabajador responde NO a las preguntas filtro:
     * - "En mi trabajo debo brindar servicio a clientes" → Demandas emocionales = 0 puntos brutos
     * - "Soy jefe de otras personas" → Relación con colaboradores = 0 puntos brutos
     * Estas dimensiones ENTRAN al cálculo con valor 0, NO se excluyen.
     */
    private static function calcularPuntajesBrutosDimensiones($puntajesItems, $atiendeClientes, $esJefe)
    {
        $puntajes = [];

        foreach (self::$dimensiones as $dimension => $items) {
            // REGLA CARTILLA: Si NO atiende clientes, demandas_emocionales = 0 (no NULL)
            if ($dimension === 'demandas_emocionales' && !$atiendeClientes) {
                $puntajes[$dimension] = 0;
                continue;
            }
            // REGLA CARTILLA: Si NO es jefe, relacion_con_colaboradores = 0 (no NULL)
            if ($dimension === 'relacion_con_colaboradores' && !$esJefe) {
                $puntajes[$dimension] = 0;
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
    private static function transformarPuntajesDimensiones($puntajesBrutos, $atiendeClientes, $esJefe)
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
    private static function calcularPuntajesBrutosDominios($puntajesDimensiones, $atiendeClientes, $esJefe)
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
     *
     * REGLA CARTILLA: El factor de transformación NO se ajusta.
     * Las dimensiones filtro (demandas_emocionales, relacion_con_colaboradores)
     * entran al cálculo con puntaje bruto = 0, reduciendo el puntaje del dominio.
     */
    private static function transformarPuntajesDominios($puntajesBrutos, $atiendeClientes, $esJefe)
    {
        $transformados = [];

        foreach ($puntajesBrutos as $dominio => $puntajeBruto) {
            $factor = self::$factoresTransformacionDominios[$dominio];
            // NO se ajusta el factor - las dimensiones filtro entran como 0
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
     *
     * REGLA CARTILLA: El factor de transformación NO se ajusta (siempre 492 para Forma A).
     * Las dimensiones filtro entran con puntaje bruto = 0, reduciendo el total.
     */
    private static function transformarPuntajeTotal($puntajeBruto, $atiendeClientes, $esJefe)
    {
        $factor = self::$factorTransformacionTotal;
        // NO se ajusta el factor - las dimensiones filtro entran como 0
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

    // ==========================================
    // MÉTODOS PÚBLICOS PARA ACCESO A BAREMOS
    // ==========================================

    /**
     * Obtiene todos los baremos de dominios (Tabla 31)
     * @return array Baremos por dominio
     */
    public static function getBaremosDominios()
    {
        return self::$baremosDominios;
    }

    /**
     * Obtiene el baremo de un dominio específico
     * @param string $dominio Código del dominio (liderazgo_relaciones_sociales, control, demandas, recompensas)
     * @return array|null Baremo del dominio o null si no existe
     */
    public static function getBaremoDominio($dominio)
    {
        return self::$baremosDominios[$dominio] ?? null;
    }

    /**
     * Obtiene todos los baremos de dimensiones (Tabla 29)
     * @return array Baremos por dimensión
     */
    public static function getBaremosDimensiones()
    {
        return self::$baremosDimensiones;
    }

    /**
     * Obtiene el baremo de una dimensión específica
     * @param string $dimension Código de la dimensión
     * @return array|null Baremo de la dimensión o null si no existe
     */
    public static function getBaremoDimension($dimension)
    {
        return self::$baremosDimensiones[$dimension] ?? null;
    }

    /**
     * Obtiene el baremo total intralaboral (Tabla 33)
     * @return array Baremo total
     */
    public static function getBaremoTotal()
    {
        return self::$baremoTotal;
    }

    /**
     * Obtiene el factor de transformación total intralaboral (Tabla 27 - Forma A)
     * @return int Factor de transformación (492)
     */
    public static function getFactorTransformacionIntralaboral()
    {
        return self::$factorTransformacionTotal;
    }

    /**
     * Obtiene el factor de transformación para evaluación general (Tabla 28 - Forma A)
     * Suma de factores intralaboral (492) + extralaboral (124)
     * @return int Factor de transformación total (616)
     */
    public static function getFactorTransformacionGeneral()
    {
        return 616; // 492 (intralaboral) + 124 (extralaboral) - Tabla 28
    }
}
