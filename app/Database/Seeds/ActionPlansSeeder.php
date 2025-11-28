<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ActionPlansSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'dimension_code' => 'caracteristicas_liderazgo',
                'dimension_name' => 'Características del Liderazgo',
                'domain_code' => 'liderazgo_relaciones_sociales',
                'questionnaire_type' => 'intralaboral',
                'introduction' => 'En primer lugar, es importante destacar que su empresa ha tomado una decisión valiente y responsable al aplicar la batería de evaluación de factores de riesgo psicosocial en su organización. Como ha podido observar, la dimensión "Características del liderazgo" ha sido calificada como de riesgo Alto o Muy Alto, lo que implica un peligro real para la salud mental y el bienestar de sus trabajadores.

Es necesario tomar en cuenta que la salud mental de los trabajadores es un tema de gran relevancia en Colombia, y que el gobierno ha establecido normativas específicas para protegerla. En este sentido, el Ministerio de Trabajo de Colombia ha establecido un protocolo de intervención para empresas que enfrentan problemas relacionados con el riesgo psicosocial en el lugar de trabajo.

Este protocolo establece que las empresas deben realizar un sistema de vigilancia epidemiológica para evaluar las condiciones de trabajo y determinar los factores de riesgo psicosocial a los que están expuestos los trabajadores. Además, deben implementar medidas preventivas y correctivas para reducir o eliminar estos riesgos, garantizando así un ambiente laboral saludable y seguro.

Es importante que su empresa tome medidas inmediatas para abordar esta situación. La falta de liderazgo positivo y efectivo puede afectar significativamente el bienestar de los trabajadores, aumentar la rotación de personal, disminuir la productividad y aumentar los costos asociados a la atención médica y la compensación por enfermedades profesionales.

Es por ello que le recomendamos que implemente medidas para mejorar la gestión del liderazgo en su empresa, como la capacitación y el desarrollo de habilidades de liderazgo en los gerentes y supervisores, el fomento de la comunicación efectiva entre líderes y trabajadores, y la promoción de un ambiente laboral inclusivo y respetuoso.',
                'objectives' => json_encode([
                    [
                        'number' => 1,
                        'description' => 'Identificar las fortalezas y debilidades del liderazgo en la empresa a través de encuestas y otras herramientas de diagnóstico que permitan obtener una perspectiva más detallada de la situación actual.'
                    ],
                    [
                        'number' => 2,
                        'description' => 'Establecer un plan de acción para mejorar las debilidades identificadas, con enfoque en el desarrollo de habilidades de liderazgo y trabajo en equipo, así como la promoción de una cultura organizacional positiva.'
                    ],
                    [
                        'number' => 3,
                        'description' => 'Realizar capacitaciones y talleres para los líderes de la empresa, con el fin de mejorar sus habilidades de liderazgo y manejo de conflictos, fomentando la comunicación efectiva, el trabajo en equipo, la empatía y la escucha activa.'
                    ],
                    [
                        'number' => 4,
                        'description' => 'Evaluar periódicamente el progreso de las acciones implementadas y el impacto que tienen en la dimensión "Características del liderazgo", a través de la aplicación de la batería de riesgo psicosocial y otras herramientas de medición de la satisfacción laboral y bienestar emocional de los trabajadores.'
                    ],
                    [
                        'number' => 5,
                        'description' => 'Mantener una cultura organizacional positiva y de compromiso, mediante el reconocimiento y valoración del trabajo de los empleados, la promoción de la participación de los trabajadores en la toma de decisiones y el fortalecimiento de la comunicación interna y externa de la empresa.'
                    ]
                ]),
                'activities_6months' => json_encode([
                    'mes_1' => [
                        [
                            'number' => 1,
                            'description' => 'Aplicar una encuesta de diagnóstico para evaluar la percepción de los trabajadores acerca del liderazgo en la empresa, identificando fortalezas y debilidades.',
                            'objetivo_relacionado' => 1
                        ],
                        [
                            'number' => 2,
                            'description' => 'Realizar una reunión con los líderes de la empresa para presentar los resultados de la encuesta y establecer un plan de acción para mejorar las debilidades identificadas.',
                            'objetivo_relacionado' => 2
                        ]
                    ],
                    'mes_2' => [
                        [
                            'number' => 1,
                            'description' => 'Realizar una capacitación sobre liderazgo y trabajo en equipo, con enfoque en el desarrollo de habilidades de liderazgo y manejo de conflictos.',
                            'objetivo_relacionado' => 3
                        ],
                        [
                            'number' => 2,
                            'description' => 'Crear un espacio de comunicación para los trabajadores donde puedan expresar sus ideas, necesidades y sugerencias en relación al liderazgo en la empresa.',
                            'objetivo_relacionado' => 5
                        ]
                    ],
                    'mes_3' => [
                        [
                            'number' => 1,
                            'description' => 'Implementar un sistema de reconocimiento y valoración del trabajo de los empleados, con el objetivo de fomentar una cultura organizacional positiva y de compromiso.',
                            'objetivo_relacionado' => 5
                        ],
                        [
                            'number' => 2,
                            'description' => 'Realizar una reunión con los líderes de la empresa para evaluar el progreso de las acciones implementadas y el impacto que tienen en la dimensión "Características del liderazgo".',
                            'objetivo_relacionado' => 4
                        ]
                    ],
                    'mes_4' => [
                        [
                            'number' => 1,
                            'description' => 'Realizar una capacitación sobre comunicación efectiva, empatía y escucha activa para los líderes de la empresa.',
                            'objetivo_relacionado' => 3
                        ],
                        [
                            'number' => 2,
                            'description' => 'Crear un espacio de retroalimentación para los trabajadores donde puedan expresar sus opiniones acerca del desempeño de los líderes y sugerir mejoras.',
                            'objetivo_relacionado' => 5
                        ]
                    ],
                    'mes_5' => [
                        [
                            'number' => 1,
                            'description' => 'Realizar una sesión de coaching para los líderes de la empresa, con el objetivo de fortalecer sus habilidades de liderazgo.',
                            'objetivo_relacionado' => 3
                        ],
                        [
                            'number' => 2,
                            'description' => 'Crear un sistema de seguimiento y evaluación para los líderes de la empresa, con el fin de medir su desempeño en la dimensión "Características del liderazgo".',
                            'objetivo_relacionado' => 4
                        ]
                    ],
                    'mes_6' => [
                        [
                            'number' => 1,
                            'description' => 'Realizar una reunión con los trabajadores para evaluar el progreso de las acciones implementadas y su impacto en la dimensión "Características del liderazgo".',
                            'objetivo_relacionado' => 4
                        ],
                        [
                            'number' => 2,
                            'description' => 'Realizar una capacitación sobre liderazgo transformacional, con el fin de promover una cultura organizacional positiva y de compromiso.',
                            'objetivo_relacionado' => 3
                        ]
                    ]
                ]),
                'bibliography' => json_encode([
                    'Ministerio de Trabajo de Colombia. (2019). Guía técnica para la prevención, intervención y evaluación de los riesgos psicosociales en el trabajo. Recuperado de https://www.mintrabajo.gov.co/documents/20147/82273/Gu%C3%ADa+T%C3%A9cnica+Riesgos+Psicosociales.pdf/7f3af187-17cc-4ba3-a9d2-9c4a37a70fa5',
                    'Organización Internacional del Trabajo. (2021). Liderazgo y gestión de los riesgos psicosociales en el trabajo. Recuperado de https://www.ilo.org/global/topics/safety-and-health-at-work/resources-library/publications/WCMS_321000/lang--es/index.htm',
                    'Salvador, L., & Aragón, A. (2020). La importancia del liderazgo y su influencia en la productividad empresarial. Journal of Business and Management, 3(1), 19-33. doi: 10.32719/jbm.2020.3.1.2',
                    'Sánchez, J. (2017). Liderazgo, clima organizacional y riesgos psicosociales en el trabajo. Revista Ciencias Estratégicas, 25(35), 147-163. doi: 10.18865/risr.v25i35.2429',
                    'Vélez, M. A., & Sánchez, M. A. (2019). Riesgos psicosociales en el trabajo: Una revisión bibliográfica. Psicogente, 22(41), 39-54. doi: 10.17081/psico.22.41.3215'
                ]),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],

            // 2. Capacitación
            [
                'dimension_code' => 'capacitacion',
                'dimension_name' => 'Capacitación',
                'domain_code' => 'control_trabajo',
                'questionnaire_type' => 'intralaboral',
                'introduction' => 'Nos complace brindarle nuestro apoyo y asesoramiento en cuanto a la importancia de realizar un sistema de vigilancia epidemiológica para la dimensión de "Capacitación" dentro de la batería de evaluación de factores de riesgo psicosocial en su empresa. Es comprensible que haya identificado un alto riesgo en esta dimensión y, por lo tanto, es fundamental abordarla de manera oportuna y efectiva.

La capacitación es un elemento clave en el desarrollo del talento humano en una organización. A través de la capacitación, se promueve el aprendizaje continuo y se desarrollan habilidades y competencias necesarias para un desempeño efectivo en el trabajo. Sin embargo, cuando la capacitación no se lleva a cabo de manera adecuada o no se ofrece en cantidad suficiente, puede generar riesgos psicosociales en los trabajadores.

El marco normativo en Colombia, establecido por el Ministerio de Trabajo, exige a las empresas realizar una evaluación de los factores de riesgo psicosocial en el lugar de trabajo y tomar medidas para prevenir y controlar estos riesgos. La dimensión de "Capacitación" es uno de los factores que debe evaluarse, y se debe garantizar que los trabajadores tengan acceso a la capacitación necesaria para realizar sus tareas de manera segura y efectiva.

La literatura más reciente en el campo de la salud ocupacional en Colombia ha demostrado la necesidad de implementar sistemas de vigilancia epidemiológica en las empresas para monitorear la exposición de los trabajadores a los factores de riesgo psicosocial. Esto permitirá identificar oportunamente las situaciones que generan riesgo para la salud mental de los trabajadores y tomar medidas preventivas y de control.',
                'objectives' => json_encode([
                    ['number' => 1, 'description' => 'Identificar las necesidades de capacitación. A través de encuestas y entrevistas con los trabajadores y supervisores, se debe identificar las habilidades y competencias que necesitan fortalecer para realizar sus tareas de manera segura y efectiva. También es importante conocer las expectativas de los trabajadores respecto a la capacitación y los obstáculos que enfrentan para participar en ella.'],
                    ['number' => 2, 'description' => 'Diseñar un plan de capacitación. Con base en los resultados de las encuestas y entrevistas, se debe diseñar un plan de capacitación que incluya objetivos claros, metodologías adecuadas y recursos necesarios. Este plan debe ser flexible para adaptarse a las necesidades cambiantes de los trabajadores y la empresa.'],
                    ['number' => 3, 'description' => 'Implementar la capacitación. Se debe implementar la capacitación de manera efectiva, asegurándose de que se cumplan los objetivos y se promueva el aprendizaje activo y la participación de los trabajadores. Es importante que la capacitación se adapte a diferentes estilos de aprendizaje y se ofrezca en tiempos y lugares adecuados para los trabajadores.'],
                    ['number' => 4, 'description' => 'Evaluar la efectividad de la capacitación. Se debe evaluar la efectividad de la capacitación a través de encuestas y entrevistas posteriores, así como a través de indicadores cuantitativos de desempeño. Esto permitirá identificar oportunidades de mejora y adaptar el plan de capacitación a las necesidades cambiantes de los trabajadores y la empresa.'],
                    ['number' => 5, 'description' => 'Continuar con la mejora continua. La capacitación debe ser un proceso continuo que permita la mejora constante de las habilidades y competencias de los trabajadores. Se debe establecer un sistema de retroalimentación y evaluación periódica para identificar oportunidades de mejora y adaptar el plan de capacitación a las necesidades cambiantes de los trabajadores y la empresa.']
                ]),
                'activities_6months' => json_encode([
                    'mes_1' => [
                        ['number' => 1, 'description' => 'Realizar encuestas y entrevistas con los trabajadores y supervisores para identificar las necesidades de capacitación y obstáculos para participar en ella.', 'objetivo_relacionado' => 1],
                        ['number' => 2, 'description' => 'Diseñar un plan de capacitación que incluya objetivos claros, metodologías adecuadas y recursos necesarios, en base a los resultados obtenidos en la actividad anterior.', 'objetivo_relacionado' => 2]
                    ],
                    'mes_2' => [
                        ['number' => 1, 'description' => 'Implementar una capacitación sobre comunicación efectiva y resolución de conflictos, adaptada a diferentes estilos de aprendizaje y ofrecida en tiempos y lugares adecuados para los trabajadores.', 'objetivo_relacionado' => 3],
                        ['number' => 2, 'description' => 'Evaluar la efectividad de la capacitación mediante indicadores cuantitativos de desempeño y encuestas posteriores.', 'objetivo_relacionado' => 4]
                    ],
                    'mes_3' => [
                        ['number' => 1, 'description' => 'Implementar una capacitación sobre manejo de estrés y autocuidado, adaptada a diferentes estilos de aprendizaje y ofrecida en tiempos y lugares adecuados para los trabajadores.', 'objetivo_relacionado' => 3],
                        ['number' => 2, 'description' => 'Evaluar la efectividad de la capacitación mediante indicadores cuantitativos de desempeño y encuestas posteriores.', 'objetivo_relacionado' => 4]
                    ],
                    'mes_4' => [
                        ['number' => 1, 'description' => 'Implementar una capacitación sobre trabajo en equipo y liderazgo, adaptada a diferentes estilos de aprendizaje y ofrecida en tiempos y lugares adecuados para los trabajadores.', 'objetivo_relacionado' => 3],
                        ['number' => 2, 'description' => 'Evaluar la efectividad de la capacitación mediante indicadores cuantitativos de desempeño y encuestas posteriores.', 'objetivo_relacionado' => 4]
                    ],
                    'mes_5' => [
                        ['number' => 1, 'description' => 'Realizar una capacitación sobre normas de seguridad y prevención de riesgos laborales, adaptada a diferentes estilos de aprendizaje y ofrecida en tiempos y lugares adecuados para los trabajadores.', 'objetivo_relacionado' => 3],
                        ['number' => 2, 'description' => 'Evaluar la efectividad de la capacitación mediante indicadores cuantitativos de desempeño y encuestas posteriores.', 'objetivo_relacionado' => 4]
                    ],
                    'mes_6' => [
                        ['number' => 1, 'description' => 'Realizar una capacitación sobre nuevas tecnologías y herramientas digitales, adaptada a diferentes estilos de aprendizaje y ofrecida en tiempos y lugares adecuados para los trabajadores.', 'objetivo_relacionado' => 3],
                        ['number' => 2, 'description' => 'Evaluar la efectividad de la capacitación mediante indicadores cuantitativos de desempeño y encuestas posteriores.', 'objetivo_relacionado' => 4],
                        ['number' => 3, 'description' => 'Revisar el plan de capacitación en base a los resultados obtenidos en las evaluaciones y retroalimentación recibida de los trabajadores y supervisores, para continuar con la mejora continua del proceso de capacitación.', 'objetivo_relacionado' => 5]
                    ]
                ]),
                'bibliography' => json_encode([
                    'Ministerio de Trabajo de Colombia. (2017). Guía técnica para la evaluación y prevención de los factores de riesgo psicosocial en el trabajo. https://www.minsalud.gov.co/sites/rid/Lists/BibliotecaDigital/RIDE/DE/CA/guia-tecnica-riesgo-psicosocial.pdf',
                    'Oviedo, H. C., & Rodríguez, C. A. (2019). Capacitación laboral y su relación con la productividad en las organizaciones. Revista Internacional de Investigación en Ciencias Sociales, 15(2), 67-80. https://doi.org/10.15381/rinvics.v15i2.16048',
                    'Salgado, J. F., & López-Rojas, L. (2020). Factores de riesgo psicosocial laboral en trabajadores de la salud en Colombia: una revisión sistemática. Salud Uninorte, 36(2), 335-346. https://doi.org/10.14482/sun.36.2.14654',
                    'Torres, M. D., & Valdivieso, C. (2019). La capacitación como herramienta de desarrollo empresarial. Revista Innovación y Ciencia, 6(2), 137-147. https://doi.org/10.17081/inno.6.2.3111',
                    'Vargas, M. A., & Quintero, N. P. (2018). Impacto de la capacitación en el desempeño laboral de los trabajadores de una empresa del sector energético en Colombia. Revista Científica de Administración, Economía y Contabilidad, 5(1), 35-48. https://doi.org/10.26820/recacec/5.1.enero.2018.35-48'
                ]),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        $this->db->table('action_plans')->insertBatch($data);
    }
}
