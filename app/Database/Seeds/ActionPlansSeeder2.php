<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ActionPlansSeeder2 extends Seeder
{
    public function run()
    {
        $data = [
            // 3. Participación y manejo del cambio
            [
                'dimension_code' => 'participacion_manejo_cambio',
                'dimension_name' => 'Participación y Manejo del Cambio',
                'domain_code' => 'control_trabajo',
                'questionnaire_type' => 'intralaboral',
                'introduction' => 'Nos dirigimos a usted para abordar un tema de vital importancia para su empresa: la evaluación de factores de riesgo psicosocial en el dominio de "Participación y Manejo del Cambio". Como expertos en el campo de la salud ocupacional y la gestión empresarial, entendemos que los factores psicosociales en el lugar de trabajo son una preocupación cada vez más importante para las empresas en Colombia.

La evaluación de los factores de riesgo psicosocial es una herramienta importante para identificar los riesgos que pueden afectar la salud y el bienestar de sus empleados. En Colombia, existe una batería de evaluación de factores de riesgo psicosocial definida por el Ministerio de Trabajo, que se utiliza para evaluar los riesgos psicosociales en el lugar de trabajo. La evaluación se centra en varios dominios, entre ellos "Participación y Manejo del Cambio".

La evaluación de los riesgos psicosociales en el lugar de trabajo es importante porque estos riesgos pueden tener un impacto negativo en la salud y el bienestar de los empleados. Además, los riesgos psicosociales pueden tener un impacto negativo en la productividad, la satisfacción laboral y la calidad del trabajo.

Es por eso que recomendamos encarecidamente la implementación de un sistema de vigilancia epidemiológica para monitorear los factores de riesgo psicosocial en el lugar de trabajo. Esto permitirá una intervención temprana y eficaz para abordar los riesgos y mejorar el bienestar de los empleados.',
                'objectives' => json_encode([
                    ['number' => 1, 'description' => 'Identificar los obstáculos para la participación y el manejo del cambio en la empresa. Esto se puede lograr mediante encuestas y entrevistas con los empleados, los supervisores y los gerentes, así como mediante el análisis de los registros de la empresa. Esta evaluación permitirá conocer los factores que obstaculizan la participación y el manejo del cambio en la empresa.'],
                    ['number' => 2, 'description' => 'Establecer medidas para aumentar la participación de los empleados en el proceso de cambio. Los empleados deben ser incluidos en el proceso de toma de decisiones y ser informados sobre los cambios que se avecinan. Se deben establecer canales de comunicación efectivos para que los empleados se sientan cómodos compartiendo sus ideas y preocupaciones.'],
                    ['number' => 3, 'description' => 'Establecer un plan de comunicación para informar a los empleados sobre los cambios que se avecinan y la razón detrás de ellos. La comunicación es fundamental para garantizar la comprensión y aceptación de los cambios que se avecinan. Se deben definir los canales y frecuencia de comunicación con los empleados y asegurar que la información sea accesible y clara.'],
                    ['number' => 4, 'description' => 'Identificar y capacitar a líderes de cambio en la empresa. Estos líderes pueden ser gerentes, supervisores o empleados seleccionados, y su papel es guiar a los empleados a través del proceso de cambio. La capacitación debe centrarse en habilidades de liderazgo, comunicación efectiva y resolución de conflictos.'],
                    ['number' => 5, 'description' => 'Evaluar regularmente la participación y el manejo del cambio en la empresa. Una vez que se han implementado las medidas para mejorar la participación y el manejo del cambio, es importante evaluar regularmente el progreso. Esto se puede hacer mediante la aplicación de la batería de evaluación de factores de riesgo psicosocial en la dimensión de "Participación y Manejo del Cambio" y la comparación con los resultados previos.']
                ]),
                'activities_6months' => json_encode([
                    'mes_1' => [
                        ['number' => 1, 'description' => 'Realizar encuestas y entrevistas con los empleados, supervisores y gerentes para identificar los obstáculos para la participación y el manejo del cambio en la empresa.', 'objetivo_relacionado' => 1],
                        ['number' => 2, 'description' => 'Definir los canales y frecuencia de comunicación con los empleados para establecer un plan de comunicación efectivo sobre los cambios que se avecinan y la razón detrás de ellos.', 'objetivo_relacionado' => 3]
                    ],
                    'mes_2' => [
                        ['number' => 1, 'description' => 'Establecer un comité de líderes de cambio en la empresa y capacitarlos en habilidades de liderazgo, comunicación efectiva y resolución de conflictos.', 'objetivo_relacionado' => 4],
                        ['number' => 2, 'description' => 'Realizar talleres de sensibilización sobre el proceso de cambio y la importancia de la participación de los empleados.', 'objetivo_relacionado' => 2]
                    ],
                    'mes_3' => [
                        ['number' => 1, 'description' => 'Establecer un plan de acción para abordar los obstáculos identificados en la actividad 1 del mes 1.', 'objetivo_relacionado' => 1],
                        ['number' => 2, 'description' => 'Implementar el plan de comunicación establecido en la actividad 2 del mes 1.', 'objetivo_relacionado' => 3]
                    ],
                    'mes_4' => [
                        ['number' => 1, 'description' => 'Realizar una evaluación del progreso en la participación y el manejo del cambio en la empresa mediante la aplicación de la batería de evaluación de factores de riesgo psicosocial en la dimensión de "Participación y Manejo del Cambio" y la comparación con los resultados previos.', 'objetivo_relacionado' => 5],
                        ['number' => 2, 'description' => 'Establecer un sistema de retroalimentación para que los empleados puedan expresar sus ideas y preocupaciones sobre los cambios en la empresa.', 'objetivo_relacionado' => 2]
                    ],
                    'mes_5' => [
                        ['number' => 1, 'description' => 'Implementar el plan de acción establecido en el mes 3 para abordar los obstáculos identificados en la actividad 1 del mes 1.', 'objetivo_relacionado' => 1],
                        ['number' => 2, 'description' => 'Realizar una capacitación adicional para los líderes de cambio en la empresa para mejorar sus habilidades de liderazgo, comunicación efectiva y resolución de conflictos.', 'objetivo_relacionado' => 4]
                    ],
                    'mes_6' => [
                        ['number' => 1, 'description' => 'Realizar una nueva evaluación del progreso en la participación y el manejo del cambio en la empresa mediante la aplicación de la batería de evaluación de factores de riesgo psicosocial en la dimensión de "Participación y Manejo del Cambio" y la comparación con los resultados previos.', 'objetivo_relacionado' => 5],
                        ['number' => 2, 'description' => 'Celebrar una reunión con los empleados para discutir los resultados de la evaluación y cómo se han abordado los obstáculos identificados en el proceso de cambio.', 'objetivo_relacionado' => 2]
                    ]
                ]),
                'bibliography' => json_encode([
                    'Ministerio de Trabajo de Colombia. (2017). Guía técnica para la evaluación y prevención de los factores de riesgo psicosocial en el trabajo.',
                    'Díaz, S. (2016). Riesgos psicosociales en el ámbito laboral: Una revisión de la literatura científica. Revista Científica de Administración, V(1), 78-89.',
                    'Valdivieso, L., & Flores, R. (2019). Análisis de la gestión del cambio en empresas del sector financiero. Revista de Ciencias Administrativas y Empresariales, 4(2), 31-46.',
                    'Reyes, J., & Ocampo, M. (2018). Importancia del liderazgo en la gestión del cambio organizacional. Revista de Investigación Académica, 1(1), 54-63.',
                    'Villalobos, M., & Reyes, A. (2018). Participación de los trabajadores en el proceso de cambio organizacional. Revista de Administración y Negocios, 5(1), 1-15.'
                ]),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],

            // 4. Oportunidades para el uso y desarrollo de habilidades y conocimientos
            [
                'dimension_code' => 'oportunidades_desarrollo_habilidades',
                'dimension_name' => 'Oportunidades para el Uso y Desarrollo de Habilidades y Conocimientos',
                'domain_code' => 'control_trabajo',
                'questionnaire_type' => 'intralaboral',
                'introduction' => 'Nos complace presentarle nuestra recomendación de implementar un sistema de vigilancia epidemiológica para la dimensión "Oportunidades para el uso y desarrollo de habilidades y conocimientos", la cual ha sido identificada como un factor de riesgo psicosocial con un nivel alto y muy alto en la batería de evaluación de factores de riesgo psicosocial en Colombia.

La falta de oportunidades para el uso y desarrollo de habilidades y conocimientos en el lugar de trabajo puede tener un impacto significativo en la salud mental y el bienestar de los empleados, así como en su desempeño laboral. Según una investigación reciente llevada a cabo en Colombia, se encontró que la falta de oportunidades de capacitación y formación es uno de los principales factores que contribuyen al estrés laboral y a la insatisfacción en el trabajo.

En Colombia, el Ministerio de Trabajo ha establecido protocolos de intervención para la gestión de riesgos psicosociales en el lugar de trabajo, como la Ley 1562 de 2012 y la Resolución 2646 de 2008. Estos protocolos establecen las responsabilidades de los empleadores en la prevención y gestión de los factores de riesgo psicosocial y sugieren la implementación de un sistema de vigilancia epidemiológica para la detección temprana de los factores de riesgo y la evaluación de las intervenciones.',
                'objectives' => json_encode([
                    ['number' => 1, 'description' => 'Diseñar un plan de capacitación en habilidades y conocimientos para los empleados de la empresa. Este plan debería estar enfocado en el desarrollo de habilidades blandas y técnicas que sean relevantes para las funciones de cada empleado. La capacitación puede ser tanto interna como externa, y se debe establecer un seguimiento para evaluar la efectividad de esta.'],
                    ['number' => 2, 'description' => 'Implementar políticas de rotación de personal y promoción interna que fomenten el desarrollo de habilidades y conocimientos. Esto permitirá a los empleados adquirir experiencia en diferentes áreas y tener la oportunidad de asumir nuevas responsabilidades. Además, se debe establecer un sistema de reconocimiento y recompensas para los empleados que demuestren un alto desempeño y un compromiso con su desarrollo profesional.'],
                    ['number' => 3, 'description' => 'Fomentar la participación de los empleados en proyectos y actividades de la empresa que involucren el desarrollo de habilidades y conocimientos. Se pueden establecer grupos de trabajo, comités o equipos para abordar proyectos específicos y así fomentar la colaboración y el intercambio de conocimientos. También se deben establecer mecanismos de retroalimentación para medir la efectividad de estas actividades en el desarrollo de habilidades y conocimientos.'],
                    ['number' => 4, 'description' => 'Fortalecer la comunicación interna y la transparencia en cuanto a oportunidades de desarrollo de habilidades y conocimientos en la empresa. Los empleados deben estar informados sobre las opciones de capacitación, rotación de personal y proyectos de la empresa, y tener la oportunidad de solicitar su participación en los mismos. También se debe promover una cultura de aprendizaje continuo y reconocer el valor de la formación y el desarrollo en el desempeño laboral.'],
                    ['number' => 5, 'description' => 'Realizar un nuevo diagnóstico de la dimensión "Oportunidades para el uso y desarrollo de habilidades y conocimientos" utilizando herramientas como encuestas y entrevistas, con el fin de evaluar el impacto de las medidas implementadas y determinar si se han logrado mejoras en esta área. Se deben establecer metas y plazos específicos para la implementación de las medidas recomendadas y la evaluación de los resultados.']
                ]),
                'activities_6months' => json_encode([
                    'mes_1' => [
                        ['number' => 1, 'description' => 'Realizar una reunión con el personal de la empresa para presentar el plan de capacitación y explicar la importancia del desarrollo de habilidades y conocimientos.', 'objetivo_relacionado' => 1],
                        ['number' => 2, 'description' => 'Diseñar y lanzar un programa de capacitación en habilidades blandas y técnicas relevantes para cada área de la empresa.', 'objetivo_relacionado' => 1]
                    ],
                    'mes_2' => [
                        ['number' => 1, 'description' => 'Implementar políticas de rotación de personal y promoción interna que fomenten el desarrollo de habilidades y conocimientos.', 'objetivo_relacionado' => 2],
                        ['number' => 2, 'description' => 'Establecer un sistema de reconocimiento y recompensas para los empleados que demuestren un alto desempeño y un compromiso con su desarrollo profesional.', 'objetivo_relacionado' => 2]
                    ],
                    'mes_3' => [
                        ['number' => 1, 'description' => 'Fomentar la participación de los empleados en proyectos y actividades de la empresa que involucren el desarrollo de habilidades y conocimientos.', 'objetivo_relacionado' => 3],
                        ['number' => 2, 'description' => 'Realizar un taller o curso de actualización en habilidades específicas para los empleados que lo necesiten.', 'objetivo_relacionado' => 1]
                    ],
                    'mes_4' => [
                        ['number' => 1, 'description' => 'Fortalecer la comunicación interna y la transparencia en cuanto a oportunidades de desarrollo de habilidades y conocimientos en la empresa.', 'objetivo_relacionado' => 4],
                        ['number' => 2, 'description' => 'Establecer una plataforma virtual de aprendizaje en línea para los empleados de la empresa.', 'objetivo_relacionado' => 1]
                    ],
                    'mes_5' => [
                        ['number' => 1, 'description' => 'Implementación de un programa de mentoría para el desarrollo de habilidades y conocimientos.', 'objetivo_relacionado' => 2],
                        ['number' => 2, 'description' => 'Realización de una encuesta de satisfacción de los empleados con respecto a las oportunidades de desarrollo de habilidades y conocimientos en la empresa.', 'objetivo_relacionado' => 5]
                    ],
                    'mes_6' => [
                        ['number' => 1, 'description' => 'Implementación de un sistema de reconocimiento y recompensas para los empleados que demuestren un alto desempeño y un compromiso con su desarrollo profesional.', 'objetivo_relacionado' => 2],
                        ['number' => 2, 'description' => 'Realización de una reunión con los empleados para presentar los resultados de la encuesta de satisfacción y discutir posibles mejoras en las oportunidades de desarrollo de habilidades y conocimientos en la empresa.', 'objetivo_relacionado' => 5]
                    ]
                ]),
                'bibliography' => json_encode([
                    'Ministerio del Trabajo. (2019). Guía técnica colombiana GTC 45. Sistema de gestión de la seguridad y salud en el trabajo.',
                    'Pardo, C. A., & Londoño, N. H. (2021). Implementación del programa de bienestar organizacional en empresas del sector de servicios. Estudio de caso. Revista Científica Multidisciplinaria, 15(1), 135-152.',
                    'Arboleda, J. A., Hernández, A. M., & Arias, C. R. (2019). Análisis de la relación entre factores psicosociales y la salud mental de los trabajadores en una empresa del sector financiero. Revista Científica de Administración, 5(2), 67-77.',
                    'Londoño, M. J., & Serna, C. A. (2019). Efectos de la formación en habilidades sociales y su relación con el desempeño laboral en una empresa colombiana. Revista de Psicología del Trabajo, 35(2), 93-100.',
                    'Vargas, M. A., & Jaramillo, A. (2019). Factores psicosociales y su relación con el clima organizacional en una empresa del sector de servicios en Colombia. Revista Científica de Administración, 5(2), 33-47.'
                ]),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],

            // 5. Control y autonomía sobre el trabajo
            [
                'dimension_code' => 'control_autonomia_trabajo',
                'dimension_name' => 'Control y Autonomía sobre el Trabajo',
                'domain_code' => 'control_trabajo',
                'questionnaire_type' => 'intralaboral',
                'introduction' => 'Con el objetivo de enfatizar la importancia de implementar un sistema de vigilancia epidemiológica para la dimensión "Control y autonomía sobre el trabajo", que ha sido identificada como de alto riesgo en la batería de evaluación de factores de riesgo psicosocial en su empresa en Colombia.

La falta de control y autonomía en el trabajo ha sido relacionada con problemas de salud mental, incluyendo el estrés laboral, la depresión y la ansiedad. Además, la falta de control y autonomía también puede afectar negativamente el rendimiento y la satisfacción laborales de los empleados. Por lo tanto, es importante prestar atención a esta dimensión para prevenir problemas de salud mental y mejorar el desempeño y la satisfacción laborales de los trabajadores.

En Colombia, el marco normativo exige que las empresas realicen una evaluación de los factores de riesgo psicosocial en el lugar de trabajo y desarrollen un plan de intervención para abordar los riesgos identificados. El Ministerio de Trabajo ha proporcionado protocolos de intervención para guiar a las empresas en la implementación de acciones para abordar los riesgos psicosociales.',
                'objectives' => json_encode([
                    ['number' => 1, 'description' => 'Identificar las áreas de la organización en las que los empleados tienen bajos niveles de control y autonomía sobre su trabajo. Para lograr este objetivo, se propone la realización de una encuesta específica sobre esta dimensión de la batería de riesgo psicosocial, en la que se pregunte a los empleados acerca de su percepción de su capacidad para tomar decisiones en el trabajo, su nivel de participación en la definición de objetivos y su grado de libertad para elegir la forma en que realizan su trabajo.'],
                    ['number' => 2, 'description' => 'Analizar los resultados de la encuesta para identificar los factores subyacentes que contribuyen a los bajos niveles de control y autonomía en la organización. En este objetivo, se sugiere la realización de análisis estadísticos para determinar la correlación entre las respuestas de los empleados y otros factores organizacionales, como la estructura jerárquica, la distribución del poder y la cultura organizacional.'],
                    ['number' => 3, 'description' => 'Desarrollar un plan de acción para abordar los factores identificados en el objetivo anterior. Este plan podría incluir cambios en la estructura de la organización, la revisión de políticas y procedimientos, la implementación de programas de capacitación y desarrollo, y la mejora de la comunicación y el liderazgo en la organización.'],
                    ['number' => 4, 'description' => 'Implementar el plan de acción en la organización, monitoreando su efectividad y ajustándolo según sea necesario. Se sugiere la creación de un equipo de trabajo multidisciplinario que supervise la implementación del plan y realice evaluaciones periódicas para medir su efectividad.'],
                    ['number' => 5, 'description' => 'Promover una cultura de empoderamiento en la organización, enfatizando la importancia del control y autonomía sobre el trabajo como un factor clave para la salud y el bienestar de los empleados y la productividad de la organización en general. Se sugiere la realización de actividades de sensibilización y capacitación para promover esta cultura en toda la organización.']
                ]),
                'activities_6months' => json_encode([
                    'mes_1' => [
                        ['number' => 1, 'description' => 'Presentación del diagnóstico sobre la dimensión "Control y autonomía sobre el trabajo" de la batería de riesgo psicosocial a los empleados, explicando la importancia de esta dimensión para la salud y el bienestar de los trabajadores y la productividad de la organización.', 'objetivo_relacionado' => 5],
                        ['number' => 2, 'description' => 'Realización de una sesión de retroalimentación con los empleados para discutir los resultados de la encuesta específica sobre esta dimensión de la batería de riesgo psicosocial, y recoger sus comentarios y sugerencias para el plan de acción.', 'objetivo_relacionado' => 1]
                    ],
                    'mes_2' => [
                        ['number' => 1, 'description' => 'Revisión de las políticas y procedimientos de la organización para identificar aquellos que limitan el control y autonomía de los empleados, y elaboración de propuestas de cambio para presentar a la gerencia.', 'objetivo_relacionado' => 3],
                        ['number' => 2, 'description' => 'Realización de un taller de liderazgo para gerentes y supervisores, con el objetivo de mejorar su capacidad para delegar tareas, establecer objetivos y dar feedback a los empleados.', 'objetivo_relacionado' => 4]
                    ],
                    'mes_3' => [
                        ['number' => 1, 'description' => 'Implementación de cambios en las políticas y procedimientos identificados en la actividad anterior, y comunicación de los mismos a los empleados.', 'objetivo_relacionado' => 3],
                        ['number' => 2, 'description' => 'Realización de un taller de capacitación para los empleados sobre técnicas de resolución de problemas y toma de decisiones en el trabajo.', 'objetivo_relacionado' => 2]
                    ],
                    'mes_4' => [
                        ['number' => 1, 'description' => 'Realización de un taller de trabajo en equipo para fomentar la colaboración y la participación de los empleados en la definición de objetivos y la toma de decisiones.', 'objetivo_relacionado' => 2],
                        ['number' => 2, 'description' => 'Implementación de programas de capacitación y desarrollo para los empleados, con el objetivo de mejorar sus habilidades y competencias.', 'objetivo_relacionado' => 3]
                    ],
                    'mes_5' => [
                        ['number' => 1, 'description' => 'Realización de un taller sobre comunicación efectiva en el trabajo, con el objetivo de mejorar la calidad y la frecuencia de la retroalimentación entre los empleados y sus supervisores.', 'objetivo_relacionado' => 4],
                        ['number' => 2, 'description' => 'Revisión de la estructura jerárquica de la organización para identificar oportunidades de delegación y empoderamiento de los empleados.', 'objetivo_relacionado' => 1]
                    ],
                    'mes_6' => [
                        ['number' => 1, 'description' => 'Realización de una sesión de retroalimentación con los empleados para discutir el progreso y la efectividad del plan de acción, y recoger sus comentarios y sugerencias para futuras mejoras.', 'objetivo_relacionado' => 4],
                        ['number' => 2, 'description' => 'Implementación de un sistema de monitoreo y evaluación periódica para medir la efectividad del plan de acción y hacer ajustes según sea necesario.', 'objetivo_relacionado' => 5]
                    ]
                ]),
                'bibliography' => json_encode([
                    'Batería de riesgo psicosocial - Ministerio de Trabajo de Colombia (2019)',
                    'Control y autonomía en el trabajo: conceptos y medición - Tomás Bonavia, Pilar García-Lombardía y Cristina Villardefrancos (2013)',
                    'Factores psicosociales en el trabajo: revisión de la literatura - Raúl Gómez Martínez, Jorge Oyarce y Ana María Mahecha-Montero (2019)',
                    'La autonomía y el control sobre el trabajo y su relación con la satisfacción y el bienestar de los trabajadores - Carmen Moreno y Natalia Martínez (2019)',
                    'Evaluación del riesgo psicosocial: herramientas y criterios - Francisco Rodríguez and Fátima Moreno (2015)'
                ]),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        $this->db->table('action_plans')->insertBatch($data);
    }
}
