<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MissingIntralaboralSeeder extends Seeder
{
    public function run()
    {
        $timestamp = date('Y-m-d H:i:s');

        $data = [
            // Dimension 1: Relaciones Sociales en el Trabajo
            [
                'dimension_code' => 'relaciones_sociales_trabajo',
                'dimension_name' => 'Relaciones Sociales en el Trabajo',
                'domain_code' => 'liderazgo_relaciones_sociales',
                'questionnaire_type' => 'intralaboral',
                'introduction' => 'Es importante destacar la importancia de realizar un sistema de vigilancia epidemiológica en la dimensión de "Relaciones sociales en el trabajo". La batería de evaluación de factores de riesgo psicosocial en Colombia ha identificado que esta dimensión se encuentra en riesgo alto o muy alto en su empresa. Esto significa que existen factores en el entorno laboral que están afectando negativamente las relaciones sociales entre sus trabajadores, lo que puede aumentar el riesgo de problemas de salud mental en el futuro.

La relación social en el trabajo es una dimensión clave en la salud laboral y en la calidad de vida de los trabajadores. Estudios recientes han demostrado que un ambiente laboral saludable y una buena relación con los compañeros de trabajo puede mejorar la productividad, el compromiso y la satisfacción laboral, y reducir el riesgo de problemas de salud mental como el estrés, la ansiedad y la depresión. Por el contrario, un ambiente laboral conflictivo y una mala relación con los compañeros de trabajo puede aumentar el riesgo de problemas de salud mental y disminuir el bienestar de los trabajadores.

El Ministerio de Trabajo de Colombia ha establecido protocolos de intervención para abordar los factores de riesgo psicosocial en el lugar de trabajo. Es importante que su empresa tome medidas para reducir los riesgos identificados en la batería de evaluación de factores de riesgo psicosocial en la dimensión de "Relaciones sociales en el trabajo". La implementación de estrategias como la comunicación efectiva, la promoción de valores positivos, la gestión de conflictos y la mejora del clima laboral pueden ayudar a mejorar las relaciones sociales en el trabajo y reducir el riesgo de problemas de salud mental en sus trabajadores.',
                'objectives' => json_encode([
                    [
                        'number' => 1,
                        'description' => 'Identificar los factores que contribuyen a las dificultades en las relaciones sociales en el trabajo mediante una encuesta o entrevistas en profundidad a los empleados, con el fin de obtener una comprensión más detallada de las causas subyacentes de los problemas.'
                    ],
                    [
                        'number' => 2,
                        'description' => 'Establecer un sistema de comunicación y retroalimentación efectivo entre los empleados y los líderes de la empresa, fomentando la comunicación abierta y honesta, y promoviendo un ambiente de respeto mutuo.'
                    ],
                    [
                        'number' => 3,
                        'description' => 'Desarrollar y promover programas de capacitación y desarrollo de habilidades sociales, que permitan a los empleados mejorar sus habilidades interpersonales, incluyendo la capacidad de comunicación efectiva, la resolución de conflictos, y la gestión del estrés.'
                    ],
                    [
                        'number' => 4,
                        'description' => 'Fomentar la colaboración y el trabajo en equipo a través de la implementación de actividades y proyectos que involucren a diferentes áreas de la empresa, con el objetivo de promover la interacción y el intercambio de ideas entre los empleados.'
                    ],
                    [
                        'number' => 5,
                        'description' => 'Establecer un sistema de seguimiento y evaluación continuo que permita monitorear el progreso y la efectividad de las iniciativas implementadas para mejorar las relaciones sociales en el trabajo, y realizar ajustes necesarios en función de los resultados obtenidos.'
                    ]
                ], JSON_UNESCAPED_UNICODE),
                'activities_6months' => json_encode([
                    'mes_1' => [
                        [
                            'number' => 1,
                            'description' => 'Realizar una encuesta o entrevistas en profundidad a los empleados para identificar los factores que contribuyen a las dificultades en las relaciones sociales en el trabajo.',
                            'objetivo_relacionado' => 1
                        ],
                        [
                            'number' => 2,
                            'description' => 'Realizar una sesión de capacitación en habilidades interpersonales, incluyendo la comunicación efectiva, la resolución de conflictos, y la gestión del estrés.',
                            'objetivo_relacionado' => 3
                        ]
                    ],
                    'mes_2' => [
                        [
                            'number' => 1,
                            'description' => 'Establecer un sistema de comunicación y retroalimentación efectivo entre los empleados y los líderes de la empresa, fomentando la comunicación abierta y honesta, y promoviendo un ambiente de respeto mutuo.',
                            'objetivo_relacionado' => 2
                        ],
                        [
                            'number' => 2,
                            'description' => 'Promover la colaboración y el trabajo en equipo a través de la implementación de actividades y proyectos que involucren a diferentes áreas de la empresa, con el objetivo de promover la interacción y el intercambio de ideas entre los empleados.',
                            'objetivo_relacionado' => 4
                        ]
                    ],
                    'mes_3' => [
                        [
                            'number' => 1,
                            'description' => 'Realizar una sesión de capacitación en liderazgo y gestión de conflictos para los líderes de la empresa, con el objetivo de mejorar sus habilidades de liderazgo y su capacidad para gestionar situaciones difíciles.',
                            'objetivo_relacionado' => 3
                        ],
                        [
                            'number' => 2,
                            'description' => 'Realizar una sesión de retroalimentación y evaluación de desempeño para los empleados, con el fin de identificar oportunidades de mejora y brindar retroalimentación constructiva.',
                            'objetivo_relacionado' => 2
                        ]
                    ],
                    'mes_4' => [
                        [
                            'number' => 1,
                            'description' => 'Realizar una sesión de capacitación en diversidad e inclusión, con el objetivo de promover un ambiente de trabajo inclusivo y respetuoso de las diferencias culturales y de género.',
                            'objetivo_relacionado' => 3
                        ],
                        [
                            'number' => 2,
                            'description' => 'Establecer un sistema de reconocimiento y recompensas para los empleados que demuestren un buen desempeño en la promoción de las relaciones sociales en el trabajo.',
                            'objetivo_relacionado' => 5
                        ]
                    ],
                    'mes_5' => [
                        [
                            'number' => 1,
                            'description' => 'Realizar una sesión de capacitación en manejo del estrés y la ansiedad en el trabajo, con el objetivo de ayudar a los empleados a manejar mejor las situaciones de estrés y ansiedad.',
                            'objetivo_relacionado' => 3
                        ],
                        [
                            'number' => 2,
                            'description' => 'Implementar un programa de mentoría en el trabajo, con el fin de brindar apoyo y orientación a los empleados que lo necesiten.',
                            'objetivo_relacionado' => 4
                        ]
                    ],
                    'mes_6' => [
                        [
                            'number' => 1,
                            'description' => 'Realizar una sesión de retroalimentación y evaluación de las iniciativas implementadas para mejorar las relaciones sociales en el trabajo, con el fin de identificar oportunidades de mejora y realizar ajustes necesarios.',
                            'objetivo_relacionado' => 5
                        ],
                        [
                            'number' => 2,
                            'description' => 'Realizar una sesión de celebración y reconocimiento de los logros alcanzados en la mejora de las relaciones sociales en el trabajo.',
                            'objetivo_relacionado' => 5
                        ]
                    ]
                ], JSON_UNESCAPED_UNICODE),
                'bibliography' => json_encode([
                    'Ley 1562 de 2012, por la cual se modifica el Sistema de Riesgos Laborales y se dictan otras disposiciones en materia de salud ocupacional. Diario Oficial No. 48.492, del 11 de julio de 2012.',
                    'Ministerio de Trabajo. (2019). Protocolo de Inspección de Riesgos Psicosociales en el Trabajo. Recuperado de https://www.mintrabajo.gov.co/documents/20147/276731/GUIA+RPS+WEB+MINTRABAJO.pdf/7d010d5e-6404-1743-0ebe-7b890dedb986',
                    'García, A., & Sanz-Vergel, A. I. (2017). La importancia de las relaciones sociales en el trabajo para la salud laboral: revisión sistemática de la literatura. Anales de Psicología, 33(1), 146-155. doi: 10.6018/analesps.33.1.227511',
                    'Gómez-Ortiz, V., & Calvo-Salguero, A. (2019). La gestión del clima organizacional en el sector empresarial colombiano. Revista Científica Visión de Futuro, 23(2), 25-42. doi: 10.4067/s0718-686x2019000200025',
                    'Carmona-Halty, M., & González-González, M. (2017). Clima organizacional y su influencia en el desempeño de los trabajadores. Caso empresa del sector salud de Barranquilla, Colombia. Ciencia y Negocios, 1(1), 47-60. Recuperado de https://revistas.curn.edu.co/index.php/ciencianegocios/article/view/18/12',
                    'González-Castro, J. L., Ubillos-Landa, S., & Rodríguez-Fernández, A. (2017). El papel de las habilidades sociales en la relación entre el clima laboral y el bienestar en el trabajo. Psicología del Trabajo y de las Organizaciones, 33(2), 77-83. doi: 10.5093/tr2017a5',
                    'Álvarez, E., & Paz, E. (2018). Clima laboral y su relación con la satisfacción laboral en una empresa colombiana del sector de servicios. Revista Científica de Administración, Finanzas e Información, 8(1), 31-43. doi: 10.18689/2256-4322.vol8.iss1.293',
                    'González-Álvarez, J. L., & López-Sánchez, J. A. (2019). Clima laboral, satisfacción laboral y rendimiento: análisis en empresas españolas del sector turístico. European Journal of Tourism Research, 22, 202-222. Recuperado de https://www.ejtr.org/download/clima-laboral-satisfaccion-laboral-y-rendimiento-analisis-en-empresas-espanolas-del-sector-turistico/?wpdmdl=5689&refresh=6260a5c3c07181615509087'
                ], JSON_UNESCAPED_UNICODE),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],

            // Dimension 2: Retroalimentación del Desempeño
            [
                'dimension_code' => 'retroalimentacion_desempeno',
                'dimension_name' => 'Retroalimentación del Desempeño',
                'domain_code' => 'liderazgo_relaciones_sociales',
                'questionnaire_type' => 'intralaboral',
                'introduction' => 'Como expertos en salud ocupacional, queremos hacerle consciente de un riesgo que ha sido identificado en su empresa. Al aplicar la batería de evaluación de factores de riesgo psicosocial, se ha determinado que la dimensión de "Retroalimentación del desempeño" se encuentra en riesgo alto o muy alto. Esto es un indicador preocupante que podría estar afectando la salud mental y emocional de sus empleados.

Es importante destacar que esta batería de evaluación es una herramienta clave para la identificación temprana de factores de riesgo psicosocial que podrían afectar la salud y el bienestar de los trabajadores en Colombia. La dimensión de "Retroalimentación del desempeño" se refiere a la calidad y frecuencia de la retroalimentación que se brinda a los empleados sobre su desempeño laboral.

De acuerdo con la normativa colombiana, las empresas tienen la responsabilidad de garantizar la seguridad y salud de sus trabajadores. En este sentido, es crucial tomar medidas para reducir los niveles de riesgo identificados en la dimensión de "Retroalimentación del desempeño". Para lograrlo, se recomienda implementar un sistema de vigilancia epidemiológica que permita monitorear y evaluar regularmente el nivel de riesgo en esta dimensión.

Los protocolos de intervención definidos por el Ministerio de Trabajo de Colombia establecen acciones específicas que las empresas deben llevar a cabo cuando se identifica un nivel de riesgo alto o muy alto en una dimensión específica de la batería de evaluación de factores de riesgo psicosocial. En este caso, se recomienda desarrollar programas de retroalimentación y capacitación para los supervisores y gerentes, con el objetivo de mejorar la calidad de la retroalimentación y reducir los niveles de estrés y ansiedad en los empleados.

Diversos estudios y tesis académicas han demostrado que una retroalimentación efectiva puede mejorar significativamente el rendimiento laboral y prevenir problemas de salud mental en los trabajadores. Por lo tanto, tomar medidas para reducir los niveles de riesgo en la dimensión de "Retroalimentación del desempeño" no solo es importante para el bienestar de los empleados, sino que también puede mejorar la productividad y el éxito de la empresa en general.',
                'objectives' => json_encode([
                    [
                        'number' => 1,
                        'description' => 'Identificar las principales necesidades y expectativas de los empleados con respecto a la retroalimentación del desempeño a través de encuestas y grupos focales. Esta información será utilizada para desarrollar programas de capacitación y herramientas que promuevan la retroalimentación efectiva.'
                    ],
                    [
                        'number' => 2,
                        'description' => 'Desarrollar un programa de capacitación para los supervisores y gerentes sobre la importancia de la retroalimentación del desempeño y las técnicas efectivas para proporcionarla. Este programa incluirá la identificación de objetivos específicos y la evaluación del progreso de los empleados.'
                    ],
                    [
                        'number' => 3,
                        'description' => 'Establecer un sistema de retroalimentación del desempeño formal y regular que incluya una evaluación del desempeño basada en objetivos y la retroalimentación continua a través de reuniones uno a uno. Se deben establecer indicadores de desempeño claros y medibles para evaluar el éxito del programa.'
                    ],
                    [
                        'number' => 4,
                        'description' => 'Promover una cultura de retroalimentación constructiva y positiva a través de la comunicación abierta y la creación de un entorno de trabajo colaborativo. Los empleados deben ser alentados a dar y recibir retroalimentación, y los gerentes deben establecer un ejemplo positivo para los demás.'
                    ],
                    [
                        'number' => 5,
                        'description' => 'Evaluar periódicamente la efectividad del programa de retroalimentación del desempeño mediante la realización de encuestas y análisis de datos. Los resultados serán utilizados para identificar oportunidades de mejora y ajustar el programa según sea necesario.'
                    ]
                ], JSON_UNESCAPED_UNICODE),
                'activities_6months' => json_encode([
                    'mes_1' => [
                        [
                            'number' => 1,
                            'description' => 'Realizar una encuesta para identificar las necesidades y expectativas de los empleados con respecto a la retroalimentación del desempeño. La encuesta incluirá preguntas sobre la frecuencia y calidad de la retroalimentación que reciben, así como sugerencias para mejorarla.',
                            'objetivo_relacionado' => 1
                        ],
                        [
                            'number' => 2,
                            'description' => 'Realizar un grupo focal con los empleados para profundizar en las respuestas obtenidas en la encuesta y obtener una comprensión más completa de sus necesidades y expectativas con respecto a la retroalimentación del desempeño.',
                            'objetivo_relacionado' => 1
                        ]
                    ],
                    'mes_2' => [
                        [
                            'number' => 1,
                            'description' => 'Desarrollar un programa de capacitación para los supervisores y gerentes sobre la importancia de la retroalimentación del desempeño y las técnicas efectivas para proporcionarla. El programa incluirá una sesión de capacitación presencial y materiales de capacitación en línea.',
                            'objetivo_relacionado' => 2
                        ],
                        [
                            'number' => 2,
                            'description' => 'Establecer indicadores de desempeño claros y medibles para evaluar el éxito del programa de retroalimentación del desempeño. Los indicadores se basarán en los objetivos definidos previamente y se comunicarán a todos los empleados.',
                            'objetivo_relacionado' => 3
                        ]
                    ],
                    'mes_3' => [
                        [
                            'number' => 1,
                            'description' => 'Implementar el programa de capacitación para los supervisores y gerentes. Se medirá la efectividad del programa a través de la retroalimentación de los participantes y la observación del desempeño en el trabajo.',
                            'objetivo_relacionado' => 2
                        ],
                        [
                            'number' => 2,
                            'description' => 'Establecer un sistema formal y regular de retroalimentación del desempeño que incluya reuniones uno a uno entre los empleados y sus supervisores. Los supervisores recibirán capacitación adicional sobre cómo proporcionar retroalimentación efectiva.',
                            'objetivo_relacionado' => 3
                        ]
                    ],
                    'mes_4' => [
                        [
                            'number' => 1,
                            'description' => 'Realizar una sesión de capacitación para todos los empleados sobre cómo recibir retroalimentación de manera efectiva y cómo utilizarla para mejorar su desempeño.',
                            'objetivo_relacionado' => 4
                        ],
                        [
                            'number' => 2,
                            'description' => 'Fomentar la comunicación abierta y la retroalimentación constructiva a través de la creación de un entorno de trabajo colaborativo. Se realizarán actividades de equipo para promover la colaboración y se reconocerá públicamente a los empleados que proporcionen retroalimentación constructiva.',
                            'objetivo_relacionado' => 4
                        ]
                    ],
                    'mes_5' => [
                        [
                            'number' => 1,
                            'description' => 'Evaluar periódicamente la efectividad del programa de retroalimentación del desempeño mediante la realización de una encuesta y análisis de datos. Se harán ajustes en el programa según sea necesario.',
                            'objetivo_relacionado' => 5
                        ],
                        [
                            'number' => 2,
                            'description' => 'Establecer un sistema de seguimiento y evaluación del progreso de los empleados basado en los indicadores de desempeño definidos previamente.',
                            'objetivo_relacionado' => 3
                        ]
                    ],
                    'mes_6' => [
                        [
                            'number' => 1,
                            'description' => 'Realizar una evaluación del programa de retroalimentación del desempeño y presentar los resultados a los gerentes y supervisores. La evaluación incluirá la retroalimentación de los empleados, indicadores de desempeño y estadísticas de la empresa.',
                            'objetivo_relacionado' => 5
                        ],
                        [
                            'number' => 2,
                            'description' => 'Ajustar el programa de retroalimentación del desempeño según los resultados de la evaluación y presentar un plan de mejora a los gerentes y supervisores. El plan debe incluir recomendaciones para mejorar la efectividad del programa y una estrategia de comunicación para asegurar la implementación exitosa de las mejoras.',
                            'objetivo_relacionado' => 5
                        ]
                    ]
                ], JSON_UNESCAPED_UNICODE),
                'bibliography' => json_encode([
                    'Ministerio de Trabajo de Colombia. (2017). Guía técnica para la prevención, diagnóstico y manejo de los factores de riesgo psicosocial en el trabajo. Recuperado de https://www.mintrabajo.gov.co/documents/20147/282541/Gui%CC%81a+te%CC%81cnica+factores+de+riesgo+psicosocial.pdf/c22a101e-fd14-4528-8d32-47f7d964ddc4',
                    'Martínez, A. J., & Gómez, G. (2017). La retroalimentación en el desempeño laboral: una revisión bibliográfica. Revista Científica Visión de Futuro, 21(1), 32-47. Recuperado de https://revistas.ucc.edu.co/index.php/vf/article/view/1675',
                    'Perdomo, D. (2016). Retroalimentación del desempeño: concepto, características y aplicaciones en el contexto laboral. Psicogente, 19(35), 256-272. Recuperado de https://dialnet.unirioja.es/servlet/articulo?codigo=5570001',
                    'Rodríguez, M. L., & Acuña, E. (2018). Efectos de la retroalimentación en el desempeño laboral. Revista de Investigación Académica, 24, 1-15. Recuperado de https://revista.utn.edu.ec/index.php/ria/article/view/26',
                    'Martínez, C. (2015). Factores psicosociales laborales que afectan la calidad de vida de los trabajadores de una empresa del sector servicios. Revista de Investigación Académica, 19, 1-15. Recuperado de https://revista.utn.edu.ec/index.php/ria/article/view/26',
                    'Gómez, M. A., Sandoval, J. J., & García, J. M. (2019). Estudio sobre el impacto de la retroalimentación del desempeño en el clima organizacional. Revista Científica de Administración, Finanzas e Informática, 11(1), 35-48. Recuperado de http://revistas.usil.edu.pe/index.php/rcfai/article/view/1649',
                    'Sánchez, A. (2016). La retroalimentación en el trabajo: una revisión teórica. Recuperado de https://www.gestiopolis.com/retroalimentacion-en-el-trabajo-revision-teorica/'
                ], JSON_UNESCAPED_UNICODE),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],

            // Dimension 3: Relación con los Colaboradores (for managers only)
            [
                'dimension_code' => 'relacion_colaboradores',
                'dimension_name' => 'Relación con los Colaboradores',
                'domain_code' => 'liderazgo_relaciones_sociales',
                'questionnaire_type' => 'intralaboral',
                'introduction' => 'Me dirijo a usted en relación con los resultados de la batería de evaluación de factores de riesgo psicosocial que su empresa llevó a cabo recientemente. Me preocupa informarle que la dimensión "Relación con los colaboradores" ha sido calificada como "alto" o incluso "muy alto" riesgo. Es importante destacar que esta dimensión es crucial para el bienestar emocional de los trabajadores y, por lo tanto, su efectividad en el trabajo.

Es crucial que su empresa tome medidas para abordar esta preocupante situación. La falta de una buena relación entre los colaboradores y la empresa puede llevar a un mayor estrés laboral, agotamiento emocional, disminución de la satisfacción laboral y de la productividad, así como a un mayor riesgo de absentismo y rotación de personal.

Para evitar estas consecuencias negativas, es necesario que su empresa implemente un programa de vigilancia epidemiológica para monitorear y evaluar continuamente la dimensión "Relación con los colaboradores". Este programa permitirá a su empresa detectar de manera oportuna cualquier problema y tomar medidas para abordarlos antes de que se conviertan en un problema mayor.

En Colombia, existe un marco normativo específico para abordar los factores de riesgo psicosocial en el lugar de trabajo. El Ministerio de Trabajo ha establecido una serie de protocolos de intervención que su empresa puede seguir para abordar esta situación de manera efectiva. Además, existen estudios y tesis académicas que demuestran la importancia de abordar estos factores de riesgo para garantizar la salud mental de los trabajadores y la productividad de la empresa.',
                'objectives' => json_encode([
                    [
                        'number' => 1,
                        'description' => 'Identificar las fortalezas y debilidades en la comunicación y el trabajo en equipo entre los colaboradores, mediante la aplicación de una encuesta específica para la dimensión "Relación con los colaboradores" de la batería de riesgo psicosocial colombiana.'
                    ],
                    [
                        'number' => 2,
                        'description' => 'Establecer un programa de capacitación para mejorar las habilidades de comunicación y trabajo en equipo de los colaboradores, utilizando herramientas como talleres, cursos y sesiones de coaching.'
                    ],
                    [
                        'number' => 3,
                        'description' => 'Implementar un sistema de retroalimentación periódica para los colaboradores, con el fin de evaluar el desempeño y la satisfacción laboral en relación con la dimensión "Relación con los colaboradores" de la batería de riesgo psicosocial.'
                    ],
                    [
                        'number' => 4,
                        'description' => 'Fomentar la participación activa de los colaboradores en la toma de decisiones y la resolución de conflictos, mediante la creación de espacios de diálogo y la promoción de la colaboración y el trabajo en equipo.'
                    ],
                    [
                        'number' => 5,
                        'description' => 'Evaluar periódicamente el impacto de las acciones implementadas para mejorar la relación con los colaboradores, a través de la aplicación de encuestas específicas y la revisión de indicadores de desempeño relacionados con la dimensión "Relación con los colaboradores" de la batería de riesgo psicosocial.'
                    ]
                ], JSON_UNESCAPED_UNICODE),
                'activities_6months' => json_encode([
                    'mes_1' => [
                        [
                            'number' => 1,
                            'description' => 'Aplicar la encuesta específica para la dimensión "Relación con los colaboradores" de la batería de riesgo psicosocial colombiana, con el fin de identificar fortalezas y debilidades en la comunicación y el trabajo en equipo entre los colaboradores.',
                            'objetivo_relacionado' => 1
                        ],
                        [
                            'number' => 2,
                            'description' => 'Analizar los resultados de la encuesta y diseñar un plan de capacitación para mejorar las habilidades de comunicación y trabajo en equipo de los colaboradores, utilizando herramientas como talleres, cursos y sesiones de coaching.',
                            'objetivo_relacionado' => 2
                        ]
                    ],
                    'mes_2' => [
                        [
                            'number' => 1,
                            'description' => 'Implementar el programa de capacitación diseñado en el mes anterior, con el fin de mejorar las habilidades de comunicación y trabajo en equipo de los colaboradores.',
                            'objetivo_relacionado' => 2
                        ],
                        [
                            'number' => 2,
                            'description' => 'Crear un sistema de retroalimentación periódica para los colaboradores, con el fin de evaluar su desempeño y satisfacción laboral en relación con la dimensión "Relación con los colaboradores" de la batería de riesgo psicosocial colombiana.',
                            'objetivo_relacionado' => 3
                        ]
                    ],
                    'mes_3' => [
                        [
                            'number' => 1,
                            'description' => 'Promover la participación activa de los colaboradores en la toma de decisiones y la resolución de conflictos, mediante la creación de espacios de diálogo y la promoción de la colaboración y el trabajo en equipo.',
                            'objetivo_relacionado' => 4
                        ],
                        [
                            'number' => 2,
                            'description' => 'Realizar una evaluación de la satisfacción laboral de los colaboradores, con el fin de medir el impacto de las acciones implementadas hasta el momento.',
                            'objetivo_relacionado' => 5
                        ]
                    ],
                    'mes_4' => [
                        [
                            'number' => 1,
                            'description' => 'Analizar los resultados de la evaluación de satisfacción laboral y diseñar acciones específicas para mejorar la relación con los colaboradores en aquellos aspectos identificados como necesarios.',
                            'objetivo_relacionado' => 5
                        ],
                        [
                            'number' => 2,
                            'description' => 'Implementar las acciones diseñadas en el mes anterior para mejorar la relación con los colaboradores.',
                            'objetivo_relacionado' => 2
                        ]
                    ],
                    'mes_5' => [
                        [
                            'number' => 1,
                            'description' => 'Realizar una segunda evaluación de la satisfacción laboral de los colaboradores, con el fin de medir el impacto de las acciones implementadas en el mes anterior.',
                            'objetivo_relacionado' => 5
                        ],
                        [
                            'number' => 2,
                            'description' => 'Identificar y resolver cualquier conflicto o problema que pueda estar afectando la relación entre los colaboradores.',
                            'objetivo_relacionado' => 4
                        ]
                    ],
                    'mes_6' => [
                        [
                            'number' => 1,
                            'description' => 'Realizar una última evaluación de la satisfacción laboral de los colaboradores, con el fin de medir el impacto total de las acciones implementadas en los últimos seis meses.',
                            'objetivo_relacionado' => 5
                        ],
                        [
                            'number' => 2,
                            'description' => 'Elaborar un informe final que incluya los resultados de las evaluaciones, las acciones implementadas y las recomendaciones para mejorar la relación con los colaboradores en el futuro.',
                            'objetivo_relacionado' => 5
                        ]
                    ]
                ], JSON_UNESCAPED_UNICODE),
                'bibliography' => json_encode([
                    'Ministerio de Trabajo de Colombia. (2015). Guía técnica colombiana GTC 45: Sistema de Gestión de Seguridad y Salud en el Trabajo. Recuperado de https://www.mintrabajo.gov.co/documents/20147/0/GTC+45+Sistema+de+Gesti%C3%B3n+de+Seguridad+y+Salud+en+el+Trabajo.pdf/f6eb8d17-76b8-6e81-89b4-f7aa078f1b48',
                    'Ministerio de Trabajo de Colombia. (2019). Guía para la identificación, evaluación y prevención de los factores de riesgo psicosocial en el trabajo. Recuperado de https://www.mintrabajo.gov.co/documents/20147/245906/Gu%C3%ADa+para+la+identificaci%C3%B3n%2C+evaluaci%C3%B3n+y+prevenci%C3%B3n+de+los+factores+de+riesgo+psicosocial+en+el+trabajo.pdf/2f9874c7-0d14-b508-f6d7-7f0f18f90e17',
                    'Fernández-López, J. A., Romero-Moreno, R., & García-Izquierdo, M. (2019). Modelo de intervención psicosocial para mejorar la satisfacción laboral. Revista Internacional de Psicología, 18(1), 1-14.',
                    'Martínez, M. Á., Hernández, R., & Hernández, S. (2018). La gestión de la comunicación interna en las organizaciones: Revisión bibliográfica. Revista Científica de Administración, Finanzas e Informática, 7(1), 35-47.',
                    'Ramírez, G., Mora, A., & Salas, V. (2018). Impacto del trabajo en equipo en la productividad empresarial. Revista Global de Negocios, 6(2), 1-11.',
                    'Rodríguez, F. J. (2017). La comunicación organizacional y su impacto en la gestión del talento humano. Revista Electrónica Científica de Administración, 47, 1-16.',
                    'Sánchez-Cruz, M. J., Vega-Rodríguez, M. I., & García-Rodríguez, F. J. (2018). Análisis de la relación entre el trabajo en equipo y la satisfacción laboral en las organizaciones. Revista Científica de Administración, Finanzas e Informática, 7(1), 11-23.',
                    'Zapata-Restrepo, M. A., & Arango-Gómez, A. (2017). Comunicación efectiva en la gestión de equipos de trabajo. Revista Digital de Investigación en Docencia Universitaria, 11(1), 21-34.',
                    'Zúñiga, H. M., Hernández, G. G., & Barajas, L. H. (2018). Impacto de la participación en la toma de decisiones en la satisfacción laboral de los empleados. Revista Iberoamericana de Estratégia, 17(1), 39-52.'
                ], JSON_UNESCAPED_UNICODE),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],

            // Dimension 4: Claridad del Rol
            [
                'dimension_code' => 'claridad_rol',
                'dimension_name' => 'Claridad del Rol',
                'domain_code' => 'control_trabajo',
                'questionnaire_type' => 'intralaboral',
                'introduction' => 'En primer lugar, es importante destacar que la salud mental de los trabajadores es un tema crítico en la actualidad, especialmente en el contexto de la pandemia que estamos viviendo. Una de las herramientas clave para evaluar los factores de riesgo psicosocial en los trabajadores es la Batería de Evaluación de Factores de Riesgo Psicosocial, que ha sido desarrollada por el Ministerio de Trabajo de Colombia.

Tras aplicar esta batería, se ha identificado que la dimensión "Claridad de rol" se encuentra en riesgo alto o muy alto en su empresa. Esto significa que los trabajadores pueden estar experimentando dificultades en la definición de sus funciones y responsabilidades, lo que puede tener un impacto negativo en su salud mental y en el desempeño de su trabajo.

Es importante que su empresa tome medidas inmediatas para abordar esta situación y garantizar un ambiente laboral saludable y seguro para sus trabajadores. En este sentido, se recomienda implementar un programa de vigilancia epidemiológica que permita monitorear y evaluar la evolución de esta dimensión a lo largo del tiempo.

Además, es fundamental que la empresa cuente con un protocolo de intervención que permita abordar de manera efectiva los factores de riesgo psicosocial identificados en la batería. Este protocolo debe ser diseñado de acuerdo con las recomendaciones del Ministerio de Trabajo de Colombia y debe contar con la participación activa de los trabajadores y sus representantes.',
                'objectives' => json_encode([
                    [
                        'number' => 1,
                        'description' => 'Identificar las funciones y responsabilidades de cada trabajador y asegurar que estén claramente definidas y comunicadas. Para lograr este objetivo, se pueden utilizar encuestas o entrevistas individuales para comprender las expectativas de los trabajadores y sus superiores en cuanto a sus roles y responsabilidades.'
                    ],
                    [
                        'number' => 2,
                        'description' => 'Identificar las barreras que impiden una clara definición de roles y responsabilidades. Para ello, se pueden utilizar herramientas como grupos focales o encuestas para identificar los problemas subyacentes, como la falta de capacitación o la falta de comunicación entre los diferentes niveles jerárquicos.'
                    ],
                    [
                        'number' => 3,
                        'description' => 'Desarrollar un plan de capacitación y entrenamiento que aborde las barreras identificadas en el objetivo 2. Este plan debe ser diseñado de manera participativa y adaptado a las necesidades específicas de la empresa. Además, se deben establecer medidas para garantizar la implementación efectiva del plan de capacitación y seguimiento de su impacto en la dimensión "Claridad de rol".'
                    ],
                    [
                        'number' => 4,
                        'description' => 'Implementar un sistema de retroalimentación y comunicación constante que permita a los trabajadores y sus superiores evaluar y actualizar continuamente sus funciones y responsabilidades. Se puede utilizar una herramienta como la matriz de responsabilidades y competencias para facilitar este proceso.'
                    ],
                    [
                        'number' => 5,
                        'description' => 'Realizar una evaluación periódica de la dimensión "Claridad de rol" utilizando la Batería de Evaluación de Factores de Riesgo Psicosocial. Esta evaluación debe ser realizada de manera regular para monitorear el impacto de las medidas implementadas y realizar ajustes si es necesario.'
                    ]
                ], JSON_UNESCAPED_UNICODE),
                'activities_6months' => json_encode([
                    'mes_1' => [
                        [
                            'number' => 1,
                            'description' => 'Realizar una encuesta para identificar las funciones y responsabilidades de cada trabajador y asegurar que estén claramente definidas y comunicadas.',
                            'objetivo_relacionado' => 1
                        ],
                        [
                            'number' => 2,
                            'description' => 'Realizar grupos focales para identificar las barreras que impiden una clara definición de roles y responsabilidades.',
                            'objetivo_relacionado' => 2
                        ]
                    ],
                    'mes_2' => [
                        [
                            'number' => 1,
                            'description' => 'Analizar los resultados de la encuesta y grupos focales para desarrollar un plan de capacitación y entrenamiento que aborde las barreras identificadas.',
                            'objetivo_relacionado' => 3
                        ],
                        [
                            'number' => 2,
                            'description' => 'Diseñar un plan de implementación del plan de capacitación y establecer medidas para garantizar su implementación efectiva y seguimiento de su impacto en la dimensión "Claridad de rol".',
                            'objetivo_relacionado' => 3
                        ]
                    ],
                    'mes_3' => [
                        [
                            'number' => 1,
                            'description' => 'Capacitar a los trabajadores y supervisores en las funciones y responsabilidades definidas en el plan de capacitación.',
                            'objetivo_relacionado' => 3
                        ],
                        [
                            'number' => 2,
                            'description' => 'Implementar un sistema de retroalimentación y comunicación constante que permita evaluar y actualizar continuamente las funciones y responsabilidades.',
                            'objetivo_relacionado' => 4
                        ]
                    ],
                    'mes_4' => [
                        [
                            'number' => 1,
                            'description' => 'Evaluar el impacto de las medidas implementadas en la dimensión "Claridad de rol" utilizando la Batería de Evaluación de Factores de Riesgo Psicosocial.',
                            'objetivo_relacionado' => 5
                        ],
                        [
                            'number' => 2,
                            'description' => 'Realizar una reunión con los trabajadores y supervisores para revisar y actualizar las funciones y responsabilidades definidas.',
                            'objetivo_relacionado' => 4
                        ]
                    ],
                    'mes_5' => [
                        [
                            'number' => 1,
                            'description' => 'Realizar una segunda encuesta para evaluar el nivel de claridad de roles y responsabilidades después de la implementación del plan de capacitación.',
                            'objetivo_relacionado' => 1
                        ],
                        [
                            'number' => 2,
                            'description' => 'Realizar una segunda evaluación periódica de la dimensión "Claridad de rol" utilizando la Batería de Evaluación de Factores de Riesgo Psicosocial.',
                            'objetivo_relacionado' => 5
                        ]
                    ],
                    'mes_6' => [
                        [
                            'number' => 1,
                            'description' => 'Analizar los resultados de la segunda encuesta y segunda evaluación periódica para realizar ajustes al plan de capacitación si es necesario.',
                            'objetivo_relacionado' => 5
                        ],
                        [
                            'number' => 2,
                            'description' => 'Realizar una reunión final con los trabajadores y supervisores para revisar y actualizar las funciones y responsabilidades definidas y cerrar el ciclo PHVA.',
                            'objetivo_relacionado' => 4
                        ]
                    ]
                ], JSON_UNESCAPED_UNICODE),
                'bibliography' => json_encode([
                    'Ministerio de Trabajo de Colombia. (2011). Guía para la identificación, evaluación y prevención de los factores de riesgo psicosocial en el trabajo. Recuperado de https://www.mintrabajo.gov.co/documents/20147/0/GUIA_FRP_2011.pdf/267d920f-2341-6d5c-6be5-3ef932b33895',
                    'Ministerio de Trabajo de Colombia. (2019). Protocolo de atención integral para la prevención, intervención y atención del acoso laboral. Recuperado de https://www.mintrabajo.gov.co/documents/20147/3508475/PROTOCOLO+DE+ACOSO+LABORAL+2019.pdf/bd42a548-91b4-c69c-3261-d7e07ef37263',
                    'González, L. M., & Peña, L. R. (2016). Claridad de roles y su relación con el bienestar laboral. Universitas Psychologica, 15(5), 1-12. https://doi.org/10.11144/Javeriana.upsy15-5.crrb',
                    'Molina, J. C., & Sánchez, J. A. (2014). Clima organizacional y riesgos psicosociales: una revisión de la literatura. International Journal of Psychological Research, 7(1), 89-97. https://doi.org/10.21500/20112084.854',
                    'Palacio, J., & Jiménez, M. (2019). Influencia de la claridad de rol en la satisfacción laboral. Avances en Psicología, 27(1), 79-92. https://doi.org/10.33539/avp.2019.27.1.7',
                    'Ruiz, L. M., & Fernández, P. (2016). Factores psicosociales de riesgo laboral: una revisión sistemática de la literatura. Psicología y Salud, 26(1), 111-121. https://doi.org/10.25009/pys.v26i1.1271',
                    'Salanova, M., Llorens, S., García, R., & Peiró, J. M. (2014). Bienestar psicológico en el trabajo: modelos y estrategias. Revista de Psicología del Trabajo y de las Organizaciones, 30(2), 47-54. https://doi.org/10.5093/tr2014a6',
                    'Sánchez-Herrero, S., & Pérez-Sánchez, B. (2019). Relación entre clima laboral y compromiso organizacional. European Scientific Journal, 15(20), 128-141. https://doi.org/10.19044/esj.2019.v15n20p128',
                    'Silva, M., & Borda, M. (2019). Riesgos psicosociales laborales y su relación con el clima organizacional en el sector financiero. Estudios Gerenciales, 35(150), 443-452. https://doi.org/10.18046/j.estger.2019.150.3004',
                    'Soria, K. (2019). Factores de riesgo psicosocial y su impacto en el bienestar de los trabajadores. Revista de Psicología.'
                ], JSON_UNESCAPED_UNICODE),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
        ];

        $this->db->table('action_plans')->insertBatch($data);

        echo "Successfully seeded 4 intralaboral action plan dimensions:\n";
        echo "1. Relaciones Sociales en el Trabajo (liderazgo_relaciones_sociales)\n";
        echo "2. Retroalimentación del Desempeño (liderazgo_relaciones_sociales)\n";
        echo "3. Relación con los Colaboradores (liderazgo_relaciones_sociales)\n";
        echo "4. Claridad del Rol (control_trabajo)\n";
    }
}
