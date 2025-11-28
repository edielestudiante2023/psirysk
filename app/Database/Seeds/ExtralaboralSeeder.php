<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ExtralaboralSeeder extends Seeder
{
    public function run()
    {
        $data = [
            // 1. Tiempo fuera del trabajo
            [
                'dimension_code' => 'tiempo_fuera_trabajo',
                'dimension_name' => 'Tiempo fuera del trabajo',
                'domain_code' => 'extralaboral',
                'questionnaire_type' => 'extralaboral',
                'introduction' => 'Nos dirigimos a usted en relación a los resultados obtenidos en la batería de evaluación de factores de riesgo psicosocial aplicada a su empresa. Nos complace saber que han tomado la iniciativa de llevar a cabo esta evaluación, ya que esto demuestra su compromiso con el bienestar emocional y mental de sus empleados.

Lamentablemente, al evaluar la dimensión "Tiempo fuera del trabajo", hemos identificado que su empresa se encuentra en un nivel de riesgo alto o muy alto. Es crucial que se tomen medidas para abordar esta situación y garantizar que sus empleados tengan un equilibrio adecuado entre su vida laboral y personal.

Como expertos en el campo de la salud mental y emocional en el lugar de trabajo, recomendamos la implementación de un programa de intervenciones específico para la dimensión "Tiempo fuera del trabajo". Este programa puede incluir una variedad de iniciativas, como actividades extralaborales que fomenten el bienestar, planes de incentivos para promover un equilibrio adecuado entre el trabajo y la vida personal, y programas de salud y bienestar que aborden los problemas de salud mental y emocional en el lugar de trabajo.

Le recomendamos que busque la orientación de organizaciones relevantes en Colombia, como las Cajas de Compensación y las Entidades Promotoras de Salud, para obtener información sobre los programas de Tiempo fuera del trabajo extralaboral y cómo pueden beneficiar a sus empleados.

Además, es importante tener en cuenta el marco normativo en Colombia, y para ello, sugerimos consultar a las Administradoras de Riesgos Laborales y Profesionales para obtener información sobre cómo cumplir con las regulaciones y garantizar un lugar de trabajo seguro y saludable.

En resumen, es crucial que se tomen medidas inmediatas para abordar la situación de riesgo alto o muy alto en la dimensión "Tiempo fuera del trabajo". Le recomendamos que considere seriamente la implementación de un programa de intervenciones específico para esta dimensión, con el fin de proteger el bienestar emocional y mental de sus empleados y garantizar una cultura de trabajo saludable.

Como expertos en el campo de la salud mental y emocional en el lugar de trabajo, estamos a su disposición para ofrecer nuestra orientación y apoyo en la implementación de medidas preventivas y correctivas. No dude en ponerse en contacto con nosotros si tiene alguna pregunta o desea discutir más a fondo cómo podemos ayudarlo a abordar esta situación.',
                'objectives' => json_encode([
                    [
                        'number' => 1,
                        'description' => 'Implementar programas de bienestar y salud para mejorar el tiempo fuera del trabajo extralaboral de los empleados. Para lograr este objetivo, se pueden realizar encuestas o grupos focales para conocer las necesidades y preferencias de los empleados en cuanto a programas de bienestar y salud, y diseñar programas personalizados según las necesidades identificadas. Se pueden considerar programas como clases de yoga o meditación, terapias de relajación, sesiones de ejercicio físico, entre otros.'
                    ],
                    [
                        'number' => 2,
                        'description' => 'Desarrollar un sistema de incentivos para promover la participación de los empleados en programas de bienestar y salud. Se pueden otorgar incentivos a los empleados que participen en estos programas, como días libres adicionales, bonificaciones económicas, reconocimientos públicos, entre otros. Estos incentivos deben ser adecuados y personalizados según las necesidades y preferencias de los empleados.'
                    ],
                    [
                        'number' => 3,
                        'description' => 'Fortalecer la comunicación y la sensibilización en temas de bienestar y salud en el trabajo. Se pueden implementar campañas de comunicación interna para promover la participación de los empleados en los programas de bienestar y salud, así como sensibilizarlos sobre la importancia de cuidar su salud mental y física fuera del trabajo. Se pueden utilizar diferentes herramientas de comunicación como correos electrónicos, boletines informativos, carteleras, entre otros.'
                    ],
                    [
                        'number' => 4,
                        'description' => 'Fomentar la participación de los empleados en la definición y evaluación de los programas de bienestar y salud. Se puede crear un comité de bienestar y salud conformado por empleados y representantes de la empresa, que se encargue de definir los programas y evaluar su impacto. Este comité debe contar con herramientas y recursos adecuados para llevar a cabo sus funciones de manera efectiva.'
                    ],
                    [
                        'number' => 5,
                        'description' => 'Evaluar periódicamente la efectividad de los programas de bienestar y salud implementados. Se deben realizar evaluaciones periódicas para medir el impacto de los programas implementados en la salud mental y física de los empleados, así como en su productividad y satisfacción laboral. Estas evaluaciones deben ser realizadas por profesionales especializados en salud y bienestar en el trabajo y deben tener en cuenta las necesidades y preferencias de los empleados.'
                    ]
                ]),
                'activities_6months' => json_encode([
                    'mes_1' => [
                        [
                            'number' => 1,
                            'description' => 'Realizar una encuesta o grupo focal para conocer las necesidades y preferencias de los empleados en cuanto a programas de bienestar y salud. Esta actividad será liderada por el comité de bienestar y salud, con el apoyo de una entidad externa como la Caja de Compensación Familiar (CCF).',
                            'objetivo_relacionado' => '1 y 4'
                        ],
                        [
                            'number' => 2,
                            'description' => 'Implementar un programa de meditación guiada para los empleados. Este programa será ofrecido por la EPS con la que la empresa tenga contratado el servicio de salud. Se ofrecerán dos sesiones al mes, una en horario de almuerzo y otra al final del día laboral.',
                            'objetivo_relacionado' => '1'
                        ]
                    ],
                    'mes_2' => [
                        [
                            'number' => 1,
                            'description' => 'Desarrollar un sistema de incentivos para promover la participación de los empleados en programas de bienestar y salud. Se otorgarán días libres adicionales a los empleados que participen en programas de bienestar y salud durante un mes completo. Esta actividad será liderada por el comité de bienestar y salud, con el apoyo de la Administradora de Riesgos Laborales (ARL) y la EPS.',
                            'objetivo_relacionado' => '2 y 4'
                        ],
                        [
                            'number' => 2,
                            'description' => 'Realizar una campaña de sensibilización sobre la importancia de cuidar la salud mental y física fuera del trabajo. Se utilizarán herramientas de comunicación interna como correos electrónicos y carteleras, y se llevará a cabo una charla informativa en el lugar de trabajo. Esta actividad será liderada por el comité de bienestar y salud, con el apoyo de la EPS.',
                            'objetivo_relacionado' => '3'
                        ]
                    ],
                    'mes_3' => [
                        [
                            'number' => 1,
                            'description' => 'Ofrecer un programa de terapia de relajación para los empleados. Este programa será ofrecido por la EPS y se ofrecerán dos sesiones al mes, una en horario de almuerzo y otra al final del día laboral.',
                            'objetivo_relacionado' => '1'
                        ],
                        [
                            'number' => 2,
                            'description' => 'Realizar una evaluación del impacto de los programas de bienestar y salud implementados hasta el momento. Esta evaluación será llevada a cabo por un profesional especializado en salud y bienestar en el trabajo, en colaboración con el comité de bienestar y salud.',
                            'objetivo_relacionado' => '5'
                        ]
                    ],
                    'mes_4' => [
                        [
                            'number' => 1,
                            'description' => 'Desarrollar un programa de ejercicios físicos para los empleados. Este programa será ofrecido por la EPS y se ofrecerán dos sesiones al mes, una en horario de almuerzo y otra al final del día laboral.',
                            'objetivo_relacionado' => '1'
                        ],
                        [
                            'number' => 2,
                            'description' => 'Realizar una campaña de comunicación interna para promover la participación de los empleados en los programas de bienestar y salud. Se utilizarán herramientas de comunicación interna como correos electrónicos y boletines informativos. Esta actividad será liderada por el comité de bienestar y salud, con el apoyo de la CCF.',
                            'objetivo_relacionado' => '3'
                        ]
                    ],
                    'mes_5' => [
                        [
                            'number' => 1,
                            'description' => 'Taller de alimentación saludable en el trabajo, a cargo de la EPS. Este taller se realizará en las instalaciones de la empresa y contará con la participación de un nutricionista de la EPS, quien brindará información y recomendaciones sobre cómo llevar una alimentación saludable durante la jornada laboral.',
                            'objetivo_relacionado' => '1'
                        ],
                        [
                            'number' => 2,
                            'description' => 'Evaluación de la efectividad de los programas de bienestar y salud implementados durante los primeros 5 meses, a cargo de un equipo especializado en salud y bienestar en el trabajo. Se realizará una encuesta a los empleados para medir el impacto de los programas implementados en su salud mental y física, así como en su productividad y satisfacción laboral. Los resultados de esta evaluación servirán para ajustar los programas según las necesidades y preferencias de los empleados.',
                            'objetivo_relacionado' => '5'
                        ]
                    ],
                    'mes_6' => [
                        [
                            'number' => 1,
                            'description' => 'Jornada de evaluación de la efectividad de los programas de bienestar y salud implementados en la empresa. Se llevará a cabo una jornada de evaluación para medir el impacto de los programas implementados en la salud mental y física de los empleados, así como en su productividad y satisfacción laboral. Para ello, se contará con la participación de profesionales especializados en salud y bienestar en el trabajo.',
                            'objetivo_relacionado' => '5'
                        ],
                        [
                            'number' => 2,
                            'description' => 'Implementación de un programa de incentivos personalizados para los empleados que participen en los programas de bienestar y salud. Con base en las necesidades y preferencias de los empleados identificadas en encuestas previas, se diseñará un programa de incentivos que contemple días libres adicionales, bonificaciones económicas, reconocimientos públicos, entre otros.',
                            'objetivo_relacionado' => '2'
                        ]
                    ]
                ]),
                'bibliography' => json_encode([
                    'Ministerio de Trabajo. (2015). Guía técnica colombiana GTC 45: Sistema de gestión de seguridad y salud en el trabajo. Recuperado de https://www.mintrabajo.gov.co/documents/20147/2203699/GTC+45.pdf/a9825f29-767c-b07b-d0c4-7bb13a81c69f',
                    'Ministerio de Trabajo. (2021). Protocolos de bioseguridad para la prevención de la COVID-19 en el trabajo. Recuperado de https://www.minsalud.gov.co/salud/publica/PET/Documents/Protocolo%20COVID-19%20para%20trabajo%20sector%20privado.pdf',
                    'Organización Internacional del Trabajo. (2011). Promoción de la salud en el lugar de trabajo. Recuperado de https://www.ilo.org/wcmsp5/groups/public/---ed_protect/---protrav/---safework/documents/publication/wcms_108624.pdf',
                    'Ríos-Rincón, A. M., Gómez-Mejía, R., & Medina-Ríos, F. J. (2018). Salud ocupacional y psicología positiva: una revisión sistemática de la literatura. Revista Virtual Universidad Católica del Norte, (54), 123-141.',
                    'Universidad del Norte. (2016). Guía para la promoción de la salud en el trabajo. Recuperado de https://www.uninorte.edu.co/documents/3728825/3766742/Guia+promoci%C3%B3n+de+salud+en+el+trabajo.pdf/098f9c3a-9658-4474-98d2-07759ecfae90'
                ]),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],

            // 2. Relaciones familiares
            [
                'dimension_code' => 'relaciones_familiares',
                'dimension_name' => 'Relaciones familiares',
                'domain_code' => 'extralaboral',
                'questionnaire_type' => 'extralaboral',
                'introduction' => 'Quiero llamar su atención sobre los resultados de la reciente evaluación de factores de riesgo psicosocial que se llevó a cabo en su empresa. Uno de los resultados más preocupantes fue el alto o muy alto riesgo identificado en la dimensión de "Relaciones familiares".

Esto significa que los empleados de su empresa están experimentando altos niveles de estrés relacionados con su vida personal, lo que puede afectar negativamente su desempeño en el trabajo, su salud mental y física, y su bienestar general. Como experto en la materia, es mi responsabilidad informarle de la importancia de tomar medidas para abordar este problema.

Es vital que su empresa implemente un programa de intervención efectivo para abordar la dimensión de "Relaciones familiares" en particular. Este programa debe estar diseñado para ayudar a los empleados a mejorar sus habilidades para manejar el estrés en el hogar, así como a encontrar formas de equilibrar sus responsabilidades personales y laborales.

En Colombia, existen varias entidades promotoras de salud, administradoras de riesgos laborales y profesionales, así como cajas de compensación que ofrecen servicios y recursos para ayudar a las empresas a implementar programas de intervención efectivos. Le recomiendo encarecidamente que busque la ayuda de estas entidades para diseñar y ejecutar un programa de intervención que sea adecuado para las necesidades específicas de su empresa.',
                'objectives' => json_encode([
                    [
                        'number' => 1,
                        'description' => 'Identificar los factores de riesgo psicosocial relacionados con las relaciones familiares en la empresa mediante la aplicación de encuestas específicas que permitan evaluar el nivel de satisfacción de los empleados en cuanto a su relación con su familia.'
                    ],
                    [
                        'number' => 2,
                        'description' => 'Diseñar e implementar un programa de capacitación para los empleados sobre la importancia de establecer un equilibrio adecuado entre el trabajo y la familia, que incluya estrategias prácticas para mejorar las relaciones familiares.'
                    ],
                    [
                        'number' => 3,
                        'description' => 'Crear un plan de apoyo y asesoría para los empleados que presenten problemas relacionados con sus relaciones familiares, incluyendo acceso a terapias de pareja, asesoramiento legal en casos de divorcio o separación, y otros servicios similares.'
                    ],
                    [
                        'number' => 4,
                        'description' => 'Establecer un sistema de retroalimentación y seguimiento para evaluar los resultados de las actividades implementadas y determinar su efectividad en la mejora de las relaciones familiares en la empresa.'
                    ],
                    [
                        'number' => 5,
                        'description' => 'Fomentar la creación de espacios de convivencia y recreación para los empleados y sus familias, que permitan fortalecer los lazos familiares y mejorar su calidad de vida, por ejemplo, a través de actividades deportivas, culturales o recreativas.'
                    ]
                ]),
                'activities_6months' => json_encode([
                    'mes_1' => [
                        [
                            'number' => 1,
                            'description' => 'Aplicación de encuestas específicas para evaluar el nivel de satisfacción de los empleados en cuanto a su relación con su familia. La EPS podría asignar recursos por reinversión para proporcionar y aplicar las encuestas.',
                            'objetivo_relacionado' => '1'
                        ],
                        [
                            'number' => 2,
                            'description' => 'Taller de sensibilización sobre la importancia de establecer un equilibrio adecuado entre el trabajo y la familia, dirigido por un experto en el tema. La CCF podría asignar recursos por reinversión para la organización del taller.',
                            'objetivo_relacionado' => '2'
                        ]
                    ],
                    'mes_2' => [
                        [
                            'number' => 1,
                            'description' => 'Sesiones de terapia de pareja para los empleados que presenten problemas relacionados con sus relaciones familiares, a cargo de un psicólogo experto en el tema. La EPS podría asignar recursos por reinversión para brindar el servicio.',
                            'objetivo_relacionado' => '3'
                        ],
                        [
                            'number' => 2,
                            'description' => 'Creación de un grupo de apoyo para los empleados que estén atravesando por situaciones difíciles en sus relaciones familiares, con el fin de que puedan compartir sus experiencias y recibir el apoyo de sus compañeros de trabajo. La ARL podría asignar recursos por reinversión para coordinar la creación del grupo.',
                            'objetivo_relacionado' => '3'
                        ]
                    ],
                    'mes_3' => [
                        [
                            'number' => 1,
                            'description' => 'Capacitación para los empleados en técnicas de comunicación asertiva y resolución de conflictos familiares, impartida por un experto en el tema. La CCF podría asignar recursos por reinversión para organizar la capacitación.',
                            'objetivo_relacionado' => '2'
                        ],
                        [
                            'number' => 2,
                            'description' => 'Implementación de un programa de flexibilidad laboral que permita a los empleados tomar días libres para atender asuntos familiares importantes, sin que esto afecte su remuneración. La empresa podría asignar recursos por reinversión para implementar el programa.',
                            'objetivo_relacionado' => '2'
                        ]
                    ],
                    'mes_4' => [
                        [
                            'number' => 1,
                            'description' => 'Creación de un espacio de recreación para los empleados y sus familias, con juegos y actividades lúdicas que fomenten la convivencia y el fortalecimiento de los lazos familiares. La ARL podría asignar recursos por reinversión para coordinar la creación del espacio.',
                            'objetivo_relacionado' => '5'
                        ],
                        [
                            'number' => 2,
                            'description' => 'Sesiones de asesoramiento legal para los empleados que estén atravesando por situaciones legales relacionadas con sus relaciones familiares, a cargo de un abogado experto en el tema. La ARL podría asignar recursos por reinversión para brindar el servicio.',
                            'objetivo_relacionado' => '3'
                        ]
                    ],
                    'mes_5' => [
                        [
                            'number' => 1,
                            'description' => 'Capacitación para los empleados en habilidades de liderazgo y trabajo en equipo, con el fin de fomentar un ambiente laboral armonioso y colaborativo que favorezca las relaciones familiares. La empresa debe asignar recursos para organizar la capacitación.',
                            'objetivo_relacionado' => '2'
                        ],
                        [
                            'number' => 2,
                            'description' => 'Realización de una feria de la salud y bienestar para los empleados y sus familias, en la que se ofrecerán servicios de salud preventiva, actividades deportivas y culturales, y otros servicios relacionados con la promoción de la salud y el bienestar. La CCF podría asignar recursos por reinversión para coordinar la realización de la feria.',
                            'objetivo_relacionado' => '5'
                        ]
                    ],
                    'mes_6' => [
                        [
                            'number' => 1,
                            'description' => 'Encuentro familiar en la empresa, en donde se invitará a los familiares de los empleados a un evento donde se realizarán actividades lúdicas y deportivas, se compartirá un almuerzo y se brindará información sobre la importancia de la conciliación entre la vida laboral y familiar. Esta actividad será coordinada por la Caja de Compensación Familiar y su objetivo principal es fortalecer los lazos familiares y mejorar la calidad de vida de los empleados y sus familias.',
                            'objetivo_relacionado' => '5'
                        ],
                        [
                            'number' => 2,
                            'description' => 'Evaluación del impacto de las actividades realizadas durante los últimos 6 meses mediante una encuesta que permita medir la percepción de los empleados sobre el impacto de las actividades en la mejora de sus relaciones familiares. Esta encuesta será diseñada por la empresa y aplicada en coordinación con la ARL, y su objetivo principal es evaluar la efectividad de las acciones implementadas para mejorar las relaciones familiares en la empresa.',
                            'objetivo_relacionado' => '4'
                        ]
                    ]
                ]),
                'bibliography' => json_encode([
                    'Ministerio de Trabajo de Colombia. (2019). Guía técnica para la evaluación y prevención de los riesgos psicosociales laborales. Recuperado de https://www.mintrabajo.gov.co/documents/20147/140727/Guia+Tecnica+Riesgos+Psicosociales.pdf/8531cfde-1530-b9e9-e9f6-87a52305c5f6',
                    'Ministerio de Salud y Protección Social de Colombia. (2017). Guía para la promoción de la salud mental en el ámbito laboral. Recuperado de https://www.minsalud.gov.co/sites/rid/Lists/BibliotecaDigital/RIDE/VS/GTH/guia-promocion-salud-mental-laboral.pdf',
                    'González-Romá, V., Peiró, J. M., & Tordera, N. (2016). El riesgo psicosocial en el trabajo: identificación, evaluación y prevención. Pirámide.',
                    'Muñoz, D., Martínez, A., & Castro, L. (2018). Estrategias para el manejo del estrés en el trabajo. Revista Latinoamericana de Psicología, 50(2), 78-86. doi: 10.14349/rlp.v50i2.4004',
                    'Cajas de Compensación Familiar. (s.f.). Servicios para empresas. Recuperado de https://www.cajasdecompensacion.com.co/empresas/servicios-para-empresas/',
                    'Asociación Colombiana de Medicina del Trabajo. (2018). Guía para la implementación del sistema de gestión en seguridad y salud en el trabajo. Recuperado de https://www.acomet.org.co/images/ACOMET/PDF/Guia_para_la_Implementacion_del_SG-SST_ACMT.pdf',
                    'Administradora de Riesgos Laborales SURA. (s.f.). Servicios empresariales. Recuperado de https://www.arsura.com/servicios-empresariales/',
                    'García-Izquierdo, M., & Espinosa-García, R. (2017). La conciliación de la vida laboral, personal y familiar en España y Colombia. Revista Iberoamericana de Psicología: Ciencia y Tecnología, 10(1), 55-64. doi: 10.33898/ript.ct.10.1.476'
                ]),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],

            // 3. Comunicación y relaciones interpersonales
            [
                'dimension_code' => 'comunicacion_relaciones_interpersonales',
                'dimension_name' => 'Comunicación y relaciones interpersonales',
                'domain_code' => 'extralaboral',
                'questionnaire_type' => 'extralaboral',
                'introduction' => 'Como empresa comprometida con el bienestar de sus empleados, es importante que tome en serio los resultados de la batería de evaluación de factores de riesgo psicosocial que se ha aplicado recientemente. En particular, la dimensión de "Comunicación y relaciones interpersonales" ha sido calificada como de Alto o Muy Alto riesgo. Esto significa que su organización puede estar experimentando problemas en este aspecto que podrían afectar la salud mental y emocional de sus colaboradores, así como el desempeño y la productividad en general.

Es por ello que es fundamental que tome medidas inmediatas para abordar esta situación. En Colombia, existen diversas entidades que pueden ayudarle en la implementación de un programa de intervenciones enfocado en mejorar la comunicación y las relaciones interpersonales en su empresa. Entre estas, se encuentran las Cajas de Compensación, las Entidades Promotoras de Salud y las Administradoras de Riesgos Laborales y Profesionales.

Estas organizaciones cuentan con expertos en la materia que pueden asesorarle en la elaboración de estrategias y actividades que mejoren la comunicación y el trabajo en equipo en su empresa. Además, pueden brindarle herramientas y recursos para que sus colaboradores puedan desarrollar habilidades sociales y emocionales que les permitan relacionarse de manera más efectiva entre sí.',
                'objectives' => json_encode([
                    [
                        'number' => 1,
                        'description' => 'Fortalecer las habilidades comunicativas de los colaboradores. Para mejorar la comunicación y las relaciones interpersonales en una empresa, es fundamental contar con colaboradores que tengan habilidades comunicativas sólidas. Por esta razón, uno de los objetivos principales debe ser fortalecer estas habilidades mediante la implementación de programas de formación y capacitación. Estos programas pueden incluir talleres sobre habilidades conversacionales, técnicas de escucha activa, expresión verbal y no verbal, así como entrenamiento en el manejo efectivo de conflictos y en la gestión de emociones. Para lograr este objetivo, se pueden aplicar encuestas y otros instrumentos de diagnóstico para identificar las áreas de oportunidad en las que se debe trabajar para mejorar las habilidades comunicativas de los colaboradores.'
                    ],
                    [
                        'number' => 2,
                        'description' => 'Fomentar la participación y el trabajo en equipo. La comunicación efectiva es fundamental para el trabajo en equipo y la colaboración entre compañeros de trabajo. Para fomentar la participación y el trabajo en equipo, se pueden desarrollar actividades y estrategias que promuevan la comunicación, la cooperación y la resolución de problemas en grupo. Estas actividades pueden incluir la realización de reuniones periódicas, la implementación de herramientas de trabajo en equipo y la realización de actividades extralaborales que fomenten el compañerismo y la colaboración. Para evaluar la efectividad de estas actividades, se pueden aplicar encuestas y otros instrumentos de diagnóstico que permitan medir la percepción de los colaboradores en cuanto al trabajo en equipo y la comunicación dentro de la empresa.'
                    ],
                    [
                        'number' => 3,
                        'description' => 'Implementar canales de comunicación efectiva. Es importante contar con canales de comunicación efectiva que permitan a los colaboradores mantenerse informados sobre las políticas, objetivos y estrategias de la empresa. Estos canales pueden incluir boletines informativos, intranet, reuniones virtuales y otros medios que faciliten la comunicación entre los diferentes niveles jerárquicos y departamentos. Para evaluar la efectividad de estos canales de comunicación, se pueden aplicar encuestas y otros instrumentos de diagnóstico que permitan medir la percepción de los colaboradores sobre el acceso a la información y la claridad de la comunicación en la empresa.'
                    ],
                    [
                        'number' => 4,
                        'description' => 'Fomentar un ambiente laboral positivo. La creación de un ambiente laboral positivo es fundamental para fomentar la comunicación efectiva y las relaciones interpersonales saludables en la empresa. Para lograr este objetivo, se pueden implementar políticas y prácticas que promuevan el respeto, la empatía y la colaboración entre los colaboradores. Para evaluar la efectividad de estas políticas y prácticas, se pueden aplicar encuestas y otros instrumentos de diagnóstico que permitan medir la percepción de los colaboradores sobre el clima laboral en la empresa.'
                    ],
                    [
                        'number' => 5,
                        'description' => 'Evaluar periódicamente los resultados. Es fundamental evaluar periódicamente los resultados de las estrategias implementadas para mejorar la comunicación y las relaciones interpersonales en la empresa. Para ello, se pueden aplicar encuestas y otros instrumentos de diagnóstico que permitan medir la evolución de los indicadores de comunicación y clima laboral en la empresa.'
                    ]
                ]),
                'activities_6months' => json_encode([
                    'mes_1' => [
                        [
                            'number' => 1,
                            'description' => 'Realizar una encuesta de diagnóstico para identificar las áreas de oportunidad en las habilidades comunicativas de los colaboradores. La entidad que puede acompañar mediante recursos de reinversión es la Administradora de riesgos laborales (ARL).',
                            'objetivo_relacionado' => '1'
                        ],
                        [
                            'number' => 2,
                            'description' => 'Implementar un taller sobre técnicas de escucha activa para todos los colaboradores de la empresa. La entidad que puede ofrecer esta actividad es la Caja de compensación familiar (CCF).',
                            'objetivo_relacionado' => '1'
                        ]
                    ],
                    'mes_2' => [
                        [
                            'number' => 1,
                            'description' => 'Realizar una reunión virtual de seguimiento con los colaboradores para evaluar la efectividad de las actividades realizadas en el mes anterior. La entidad que puede acompañar mediante recursos de reinversión es la ARL.',
                            'objetivo_relacionado' => '5'
                        ],
                        [
                            'number' => 2,
                            'description' => 'Realizar una actividad extralaboral para fomentar el compañerismo y la colaboración entre los colaboradores. La entidad que puede ofrecer esta actividad es la CCF.',
                            'objetivo_relacionado' => '2'
                        ]
                    ],
                    'mes_3' => [
                        [
                            'number' => 1,
                            'description' => 'Implementar un programa de formación y capacitación sobre el manejo efectivo de conflictos para los colaboradores. La entidad que puede ofrecer esta actividad es la ARL.',
                            'objetivo_relacionado' => '1'
                        ],
                        [
                            'number' => 2,
                            'description' => 'Realizar una reunión periódica para discutir y resolver problemas en equipo. La entidad que puede acompañar mediante recursos de reinversión es la CCF.',
                            'objetivo_relacionado' => '2'
                        ]
                    ],
                    'mes_4' => [
                        [
                            'number' => 1,
                            'description' => 'Implementar un canal de comunicación efectiva, como una intranet, para mantener a los colaboradores informados sobre las políticas, objetivos y estrategias de la empresa. La entidad que puede ofrecer esta actividad es la EPS.',
                            'objetivo_relacionado' => '3'
                        ],
                        [
                            'number' => 2,
                            'description' => 'Realizar una actividad extralaboral para fomentar el compañerismo y la colaboración entre los colaboradores. La entidad que puede acompañar mediante recursos de reinversión es la CCF.',
                            'objetivo_relacionado' => '2'
                        ]
                    ],
                    'mes_5' => [
                        [
                            'number' => 1,
                            'description' => 'Realizar una encuesta para evaluar la efectividad de los canales de comunicación implementados. La entidad que puede acompañar mediante recursos de reinversión es la EPS.',
                            'objetivo_relacionado' => '5'
                        ],
                        [
                            'number' => 2,
                            'description' => 'Implementar una herramienta de trabajo en equipo, como una plataforma virtual, para fomentar la colaboración entre los colaboradores. La entidad que puede ofrecer esta actividad es la CCF.',
                            'objetivo_relacionado' => '2'
                        ]
                    ],
                    'mes_6' => [
                        [
                            'number' => 1,
                            'description' => 'Realizar una reunión virtual para evaluar los resultados de las estrategias implementadas durante los últimos meses. La entidad que puede acompañar mediante recursos de reinversión es la ARL.',
                            'objetivo_relacionado' => '5'
                        ],
                        [
                            'number' => 2,
                            'description' => 'Implementar una política de reconocimiento y agradecimiento a los colaboradores por su trabajo en equipo y colaboración. La entidad que puede ofrecer esta actividad es la CCF.',
                            'objetivo_relacionado' => '4'
                        ]
                    ]
                ]),
                'bibliography' => json_encode([
                    'Ministerio de Trabajo de Colombia. (2015). Guía técnica colombiana GTC 45: Sistema de Gestión de Seguridad y Salud en el Trabajo. Recuperado de https://www.mintrabajo.gov.co/documents/20147/2472322/GTC45.pdf/1a9f1eb2-8b22-4d7e-8a45-62dcce6585f9',
                    'Ministerio de Salud y Protección Social de Colombia. (2017). Resolución 2404 de 2017. Por la cual se establecen los requisitos y procedimientos para la implementación del Programa de Vigilancia Epidemiológica de Riesgos Psicosociales en el trabajo. Recuperado de https://www.minsalud.gov.co/sites/rid/Lists/BibliotecaDigital/RIDE/DE/DIJ/resolucion-2404-de-2017.pdf',
                    'Reyes, J., Benavides, F. G., & Bolumar, F. (2012). Riesgos psicosociales y salud laboral: conceptos y técnicas para la evaluación e intervención. Archivos de Prevención de Riesgos Laborales, 15(1), 6-15.',
                    'Vargas, C. M., & Gómez, L. F. (2017). Estrategias para el mejoramiento de la comunicación organizacional en las empresas. Revista Internacional de Administración, Finanzas y Economía, 8(1), 127-136.',
                    'De la Rosa, M. C., & Rojas, D. (2019). Evaluación del clima laboral y su relación con la satisfacción laboral en la empresa X. Revista Iberoamericana de Psicología, Salud y Ejercicio, 14(1), 59-68.'
                ]),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],

            // 4. Situación económica del grupo familiar
            [
                'dimension_code' => 'situacion_economica_familiar',
                'dimension_name' => 'Situación económica del grupo familiar',
                'domain_code' => 'extralaboral',
                'questionnaire_type' => 'extralaboral',
                'introduction' => 'Nos complace ofrecerle nuestros servicios de asesoramiento en el diseño e implementación de un programa de intervenciones para abordar la dimensión "Situación económica del grupo familiar" en su empresa. Como usted sabe, recientemente se aplicó la batería de evaluación de factores de riesgo psicosocial en su organización y se determinó que esta dimensión se encuentra en riesgo alto o muy alto.

Es importante destacar que, en Colombia, existe un marco normativo que obliga a las empresas a implementar medidas para prevenir y controlar los factores de riesgo psicosocial en el lugar de trabajo. Este marco normativo incluye las regulaciones establecidas por las Cajas de Compensación, Entidades Promotoras de Salud, Administradoras de Riesgos Laborales y Profesionales.

El hecho de que la situación económica del grupo familiar de sus empleados esté en riesgo alto o muy alto puede tener consecuencias negativas en la salud mental y física de sus trabajadores. Además, puede afectar la productividad y el rendimiento laboral de su organización.

Por esta razón, recomendamos encarecidamente que se implemente un programa de intervenciones que aborde la situación económica del grupo familiar de sus empleados. Este programa puede incluir medidas como la educación en inteligencia financiera, el manejo del dinero, el pago de deudas y otras estrategias para mejorar la situación económica de sus empleados.',
                'objectives' => json_encode([
                    [
                        'number' => 1,
                        'description' => 'Implementar programas de educación financiera para los empleados, con el fin de mejorar su capacidad de manejar y administrar sus ingresos. Esto incluirá la promoción de herramientas financieras como presupuestos y ahorros, la gestión adecuada de tarjetas de crédito y la planificación a largo plazo.'
                    ],
                    [
                        'number' => 2,
                        'description' => 'Proporcionar acceso a servicios de asesoramiento financiero y planes de ahorro a través de las cajas de compensación, las entidades promotoras de salud y las administradoras de riesgos laborales y profesionales. Estos servicios estarán disponibles para todos los empleados y sus familias, y se enfocarán en la mejora de la situación económica del grupo familiar.'
                    ],
                    [
                        'number' => 3,
                        'description' => 'Realizar una encuesta de clima laboral enfocada en la situación económica del grupo familiar, para evaluar el nivel de satisfacción y preocupación de los empleados en relación con sus finanzas personales y familiares. Con base en los resultados de la encuesta, se podrán desarrollar estrategias específicas para abordar las necesidades y preocupaciones de los empleados.'
                    ],
                    [
                        'number' => 4,
                        'description' => 'Establecer alianzas con instituciones financieras y otras entidades para ofrecer préstamos y créditos a tasas de interés preferenciales a los empleados de la empresa. Esto permitirá a los empleados acceder a financiamiento para necesidades de corto plazo, como emergencias médicas o gastos imprevistos, y mejorar su situación económica.'
                    ],
                    [
                        'number' => 5,
                        'description' => 'Fomentar la creación de empresas y el emprendimiento entre los empleados, a través de la oferta de capacitaciones, asesorías y financiamiento para proyectos de emprendimiento. Esto permitirá a los empleados mejorar sus ingresos y diversificar sus fuentes de ingresos, mejorando así su situación económica y la de sus familias.'
                    ]
                ]),
                'activities_6months' => json_encode([
                    'mes_1' => [
                        [
                            'number' => 1,
                            'description' => 'Realizar una capacitación en educación financiera para los empleados, con el objetivo de mejorar su capacidad de manejar y administrar sus ingresos. En esta actividad se promoverán herramientas financieras como presupuestos y ahorros, y se enseñará a los empleados a gestionar adecuadamente sus tarjetas de crédito y a planificar a largo plazo. Esta actividad será ofrecida por la EPS, la CCF y la ARL, quienes tienen programas de capacitación en educación financiera dirigidos a las empresas. Además, se contará con el apoyo de una entidad que puede acompañar mediante recursos de reinversión, como una entidad bancaria o una ONG que trabaje en temas financieros.',
                            'objetivo_relacionado' => '1'
                        ],
                        [
                            'number' => 2,
                            'description' => 'Realizar una encuesta de clima laboral enfocada en la situación económica del grupo familiar de los empleados, con el objetivo de evaluar su nivel de satisfacción y preocupación en relación con sus finanzas personales y familiares. Esta encuesta permitirá identificar las necesidades y preocupaciones de los empleados y desarrollar estrategias específicas para abordarlas. La EPS, la CCF y la ARL podrán ofrecer apoyo en la implementación de la encuesta, ya que cuentan con programas de bienestar para las empresas.',
                            'objetivo_relacionado' => '3'
                        ]
                    ],
                    'mes_2' => [
                        [
                            'number' => 1,
                            'description' => 'Ofrecer servicios de asesoramiento financiero y planes de ahorro a los empleados y sus familias, a través de las EPS, las CCF y las ARL. Estos servicios estarán enfocados en la mejora de la situación económica del grupo familiar y serán impartidos por expertos en finanzas. Se buscará establecer alianzas con instituciones financieras para ofrecer tasas de interés preferenciales a los empleados que deseen acceder a préstamos y créditos.',
                            'objetivo_relacionado' => '2'
                        ],
                        [
                            'number' => 2,
                            'description' => 'Realizar una capacitación en emprendimiento para los empleados interesados en crear su propia empresa o proyecto. En esta actividad se brindará asesoría y financiamiento para proyectos de emprendimiento, con el objetivo de mejorar los ingresos de los empleados y diversificar sus fuentes de ingresos. Esta actividad será ofrecida por las EPS, las CCF y las ARL, quienes tienen programas de fomento al emprendimiento dirigidos a las empresas. Además, se contará con el apoyo de una entidad que puede acompañar mediante recursos de reinversión, como una entidad bancaria o una ONG que trabaje en temas de emprendimiento.',
                            'objetivo_relacionado' => '5'
                        ]
                    ],
                    'mes_3' => [
                        [
                            'number' => 1,
                            'description' => 'Realizar una sesión de asesoramiento financiero a cargo de una entidad financiera aliada. En esta sesión se brindará información sobre temas como la gestión de deudas, el ahorro y la inversión. Se enfocará en proporcionar herramientas prácticas para mejorar la gestión financiera de los empleados y sus familias.',
                            'objetivo_relacionado' => '2'
                        ],
                        [
                            'number' => 2,
                            'description' => 'Realizar una capacitación en emprendimiento a cargo de expertos en el tema, que incluya aspectos como la identificación de oportunidades de negocio, la planificación y gestión de un proyecto de emprendimiento, y la obtención de financiamiento para el mismo. Se busca fomentar la creación de empresas entre los empleados y mejorar su situación económica a largo plazo.',
                            'objetivo_relacionado' => '5'
                        ]
                    ],
                    'mes_4' => [
                        [
                            'number' => 1,
                            'description' => 'Realizar una encuesta de satisfacción a los empleados que hayan participado en las actividades anteriores para medir su percepción sobre los beneficios obtenidos y su impacto en su situación financiera personal y familiar.',
                            'objetivo_relacionado' => '3'
                        ],
                        [
                            'number' => 2,
                            'description' => 'Ofrecer una sesión de capacitación sobre herramientas digitales para la gestión de finanzas personales y familiares, con énfasis en la seguridad y privacidad de la información financiera. Se espera que los empleados puedan adoptar estas herramientas en su vida cotidiana para mejorar su capacidad de gestión financiera.',
                            'objetivo_relacionado' => '1'
                        ]
                    ],
                    'mes_5' => [
                        [
                            'number' => 1,
                            'description' => 'Realizar una jornada de salud mental y emocional para los empleados y sus familias, enfocada en el manejo de estrés y ansiedad relacionados con situaciones financieras difíciles. Se ofrecerá orientación y herramientas para mejorar la salud mental y emocional en el contexto financiero.',
                            'objetivo_relacionado' => '3'
                        ],
                        [
                            'number' => 2,
                            'description' => 'Ofrecer una capacitación en negociación y resolución de conflictos familiares relacionados con las finanzas. La idea es que los empleados puedan adquirir habilidades de negociación y resolución de conflictos para mejorar su situación financiera en el ámbito familiar.',
                            'objetivo_relacionado' => '1'
                        ]
                    ],
                    'mes_6' => [
                        [
                            'number' => 1,
                            'description' => 'Ofrecer una sesión de orientación para el uso adecuado de los beneficios sociales ofrecidos por la empresa y las entidades externas involucradas en la implementación de las actividades. Se explicará cómo acceder a los beneficios y se brindará información sobre las condiciones y requisitos para hacer uso de ellos.',
                            'objetivo_relacionado' => '2'
                        ],
                        [
                            'number' => 2,
                            'description' => 'Realizar una evaluación final de los resultados obtenidos a través de las diferentes actividades implementadas y establecer un plan de seguimiento y mejora continua. Se revisarán los indicadores de éxito y se identificarán oportunidades de mejora para futuras intervenciones.',
                            'objetivo_relacionado' => '3'
                        ]
                    ]
                ]),
                'bibliography' => json_encode([
                    'Ministerio de Trabajo de Colombia. (2017). Guía técnica colombiana GTC 45: Sistema de gestión de la seguridad y salud en el trabajo. Recuperado de https://www.mintrabajo.gov.co/documents/20147/159150/GTC+45.pdf/6624a4ca-9eab-4b2a-9fa3-7f3e3eab3ec4',
                    'Ministerio de Trabajo de Colombia. (2017). Guía de implementación del ciclo PHVA en el sistema de gestión de la seguridad y salud en el trabajo. Recuperado de https://www.mintrabajo.gov.co/documents/20147/0/Gu%C3%ADa+PHVA+SST.pdf/2b934ff5-52db-8e23-1d5f-a8d9f5555b33',
                    'Superintendencia Financiera de Colombia. (2018). Educación financiera para todos: Guía para el ciudadano. Recuperado de https://www.superfinanciera.gov.co/jsp/loader.jsf?lServicio=Publicaciones&lTipo=publicaciones&lFuncion=loadContenidoPublicacion&id=76998',
                    'Banco de la República. (2017). Educación financiera en Colombia: Desafíos y estrategias. Recuperado de https://www.banrep.gov.co/es/educacion-financiera-colombia-desafios-y-estrategias',
                    'García, D. (2019). Educación financiera en Colombia: Una revisión de su evolución, retos y perspectivas. Pensamiento & Gestión, (47), 145-165. Recuperado de https://revistas.uniminuto.edu/index.php/peg/article/view/2051/1933',
                    'Departamento Nacional de Planeación. (2020). Estrategia nacional de inclusión financiera 2020-2022. Recuperado de https://www.dnp.gov.co/Programas/Inclusion-Financiera/Documents/ENIF%202020%20-%202022.pdf'
                ]),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],

            // 5. Características de la vivienda y de su entorno
            [
                'dimension_code' => 'caracteristicas_vivienda_entorno',
                'dimension_name' => 'Características de la vivienda y de su entorno',
                'domain_code' => 'extralaboral',
                'questionnaire_type' => 'extralaboral',
                'introduction' => 'Nos complace informarle que, tras la aplicación de la batería de evaluación de factores de riesgo psicosocial en su empresa, hemos identificado que la dimensión "Características de la vivienda y de su entorno" presenta un riesgo alto o muy alto. Esto significa que es necesario tomar medidas inmediatas para intervenir en esta área y mejorar las condiciones de vida de sus trabajadores.

En Colombia, existen marcos normativos que establecen la importancia de garantizar condiciones de trabajo seguras y saludables para los empleados. Por esta razón, es fundamental que su empresa tome acciones para mejorar las condiciones de vivienda y entorno de sus trabajadores.

En este sentido, podemos ofrecerle un programa de intervenciones que incluya capacitación en construcción y reparación de viviendas, planificación urbana y arquitectura, acceso a servicios básicos, seguridad en el hogar y prevención de desastres, y gestión financiera y ahorro. Estas capacitaciones permitirán a sus trabajadores mejorar su calidad de vida, ahorrar dinero en servicios básicos y proteger su hogar y propiedad en caso de emergencias.',
                'objectives' => json_encode([
                    [
                        'number' => 1,
                        'description' => 'Realizar una encuesta a los empleados para identificar las condiciones actuales de sus viviendas y entornos, con el fin de conocer las principales necesidades y demandas en cuanto a mejoras en la calidad de vida.'
                    ],
                    [
                        'number' => 2,
                        'description' => 'Implementar programas de capacitación en construcción y reparación de viviendas, acceso a servicios básicos, seguridad en el hogar y prevención de desastres, y gestión financiera y ahorro, con el fin de mejorar las condiciones de vida de los empleados y sus familias.'
                    ],
                    [
                        'number' => 3,
                        'description' => 'Establecer alianzas con entidades externas, como cajas de compensación, entidades promotoras de salud, Administradoras de riesgos laborales y profesionales, para obtener recursos y financiamiento que permitan llevar a cabo los programas de capacitación y mejora de viviendas y entornos.'
                    ],
                    [
                        'number' => 4,
                        'description' => 'Realizar un seguimiento periódico a las mejoras implementadas en las viviendas y entornos de los empleados, a través de evaluaciones y encuestas, con el fin de evaluar su efectividad y hacer ajustes necesarios en los programas de capacitación y mejora.'
                    ],
                    [
                        'number' => 5,
                        'description' => 'Crear un cronograma de actividades de seguridad y salud en el trabajo, basado en la información recopilada en la encuesta inicial y en los resultados de la implementación de los programas de capacitación y mejora, con el fin de garantizar un ambiente laboral seguro y saludable para los empleados.'
                    ]
                ]),
                'activities_6months' => json_encode([
                    'mes_1' => [
                        [
                            'number' => 1,
                            'description' => 'Realizar una reunión informativa con los empleados para dar a conocer los objetivos del programa de mejora de viviendas y entornos, los recursos disponibles y las entidades externas que pueden acompañar mediante recursos de reinversión, como las cajas de compensación familiar (CCF) y las entidades promotoras de salud (EPS).',
                            'objetivo_relacionado' => '1'
                        ],
                        [
                            'number' => 2,
                            'description' => 'Realizar una encuesta a los empleados para identificar las condiciones actuales de sus viviendas y entornos, con el fin de conocer las principales necesidades y demandas en cuanto a mejoras en la calidad de vida.',
                            'objetivo_relacionado' => '1'
                        ]
                    ],
                    'mes_2' => [
                        [
                            'number' => 1,
                            'description' => 'Realizar un taller de capacitación sobre construcción y reparación de viviendas, en el cual se enseñen técnicas y recomendaciones para realizar mejoras en la vivienda de manera segura y eficiente. Este taller puede ser ofrecido por entidades externas, como las CCF y las EPS.',
                            'objetivo_relacionado' => '2'
                        ],
                        [
                            'number' => 2,
                            'description' => 'Implementar un plan piloto de mejora de viviendas en el que se realicen mejoras en las viviendas de algunos empleados seleccionados, con el fin de evaluar su efectividad y hacer ajustes necesarios en los programas de capacitación y mejora.',
                            'objetivo_relacionado' => '4'
                        ]
                    ],
                    'mes_3' => [
                        [
                            'number' => 1,
                            'description' => 'Realizar un taller de capacitación sobre acceso a servicios básicos, seguridad en el hogar y prevención de desastres, en el cual se enseñen técnicas y recomendaciones para garantizar la seguridad y el bienestar de los empleados y sus familias. Este taller puede ser ofrecido por entidades externas, como las CCF y las EPS.',
                            'objetivo_relacionado' => '2'
                        ],
                        [
                            'number' => 2,
                            'description' => 'Realizar una evaluación de las mejoras implementadas en las viviendas de los empleados, a través de encuestas y visitas de seguimiento, con el fin de evaluar su efectividad y hacer ajustes necesarios en los programas de capacitación y mejora.',
                            'objetivo_relacionado' => '4'
                        ]
                    ],
                    'mes_4' => [
                        [
                            'number' => 1,
                            'description' => 'Realizar un taller de capacitación sobre gestión financiera y ahorro, en el cual se enseñen técnicas y recomendaciones para mejorar la gestión del dinero y el ahorro en el hogar. Este taller puede ser ofrecido por entidades externas, como las CCF y las EPS.',
                            'objetivo_relacionado' => '2'
                        ],
                        [
                            'number' => 2,
                            'description' => 'Establecer alianzas con Administradoras de riesgos laborales y profesionales (ARL) para obtener recursos y financiamiento que permitan llevar a cabo los programas de capacitación y mejora de viviendas y entornos.',
                            'objetivo_relacionado' => '3'
                        ]
                    ],
                    'mes_5' => [
                        [
                            'number' => 1,
                            'description' => 'Realizar una jornada de sensibilización sobre la importancia de contar con un entorno seguro y saludable en el hogar, en la que se aborden temas como la prevención de accidentes domésticos y la gestión financiera para la mejora de viviendas. Esta actividad puede ser realizada en colaboración con la ARL, que puede ofrecer charlas sobre prevención de accidentes laborales y brindar recursos para la implementación de medidas de seguridad en el hogar.',
                            'objetivo_relacionado' => '2'
                        ],
                        [
                            'number' => 2,
                            'description' => 'Implementar un programa de capacitación en construcción y reparación de viviendas, en el que se brinden herramientas para la realización de mejoras en la infraestructura y condiciones de la vivienda. Este programa puede ser llevado a cabo en colaboración con la CCF, que puede ofrecer cursos y talleres sobre construcción y reparación de viviendas, así como recursos para la adquisición de materiales y herramientas necesarios para estas mejoras.',
                            'objetivo_relacionado' => '2'
                        ]
                    ],
                    'mes_6' => [
                        [
                            'number' => 1,
                            'description' => 'Realizar una jornada de atención médica y psicológica en las instalaciones de la empresa, en la que se brinden servicios de atención primaria en salud y se realicen charlas sobre salud mental y emocional. Esta actividad puede ser realizada en colaboración con la EPS, que puede ofrecer servicios de atención médica y brindar recursos para la realización de charlas sobre temas de salud.',
                            'objetivo_relacionado' => '5'
                        ],
                        [
                            'number' => 2,
                            'description' => 'Implementar un programa de capacitación en gestión financiera y ahorro, en el que se brinden herramientas para la administración de los recursos económicos y el ahorro para la realización de mejoras en la vivienda y el entorno. Este programa puede ser llevado a cabo en colaboración con la CCF, que puede ofrecer cursos y talleres sobre gestión financiera y brindar recursos para la adquisición de materiales y herramientas necesarios para las mejoras en la vivienda y entorno.',
                            'objetivo_relacionado' => '2'
                        ]
                    ]
                ]),
                'bibliography' => json_encode([
                    'Ministerio de Trabajo de Colombia. (2017). Guía técnica colombiana GTC 45: Sistema de gestión de la seguridad y salud en el trabajo. Recuperado de https://www.mintrabajo.gov.co/documents/20147/159150/GTC+45.pdf/6624a4ca-9eab-4b2a-9fa3-7f3e3eab3ec4',
                    'Ministerio de Trabajo de Colombia. (2017). Guía de implementación del ciclo PHVA en el sistema de gestión de la seguridad y salud en el trabajo. Recuperado de https://www.mintrabajo.gov.co/documents/20147/0/Gu%C3%ADa+PHVA+SST.pdf/2b934ff5-52db-8e23-1d5f-a8d9f5555b33',
                    'Superintendencia Financiera de Colombia. (2018). Educación financiera para todos: Guía para el ciudadano. Recuperado de https://www.superfinanciera.gov.co/jsp/loader.jsf?lServicio=Publicaciones&lTipo=publicaciones&lFuncion=loadContenidoPublicacion&id=76998',
                    'Banco de la República. (2017). Educación financiera en Colombia: Desafíos y estrategias. Recuperado de https://www.banrep.gov.co/es/educacion-financiera-colombia-desafios-y-estrategias',
                    'García, D. (2019). Educación financiera en Colombia: Una revisión de su evolución, retos y perspectivas. Pensamiento & Gestión, (47), 145-165. Recuperado de https://revistas.uniminuto.edu/index.php/peg/article/view/2051/1933',
                    'Departamento Nacional de Planeación. (2020). Estrategia nacional de inclusión financiera 2020-2022. Recuperado de https://www.dnp.gov.co/Programas/Inclusion-Financiera/Documents/ENIF%202020%20-%202022.pdf'
                ]),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],

            // 6. Influencia del entorno extralaboral sobre el trabajo
            [
                'dimension_code' => 'influencia_entorno_extralaboral',
                'dimension_name' => 'Influencia del entorno extralaboral sobre el trabajo',
                'domain_code' => 'extralaboral',
                'questionnaire_type' => 'extralaboral',
                'introduction' => 'Nos complace informarle que, tras la aplicación de la batería de evaluación de factores de riesgo psicosocial en su empresa, hemos identificado que la dimensión "Influencia del entorno extralaboral sobre el trabajo" presenta un riesgo alto o muy alto. La evaluación de los factores de riesgo psicosocial en el trabajo es fundamental para garantizar la salud y el bienestar de los empleados en Colombia. La influencia del entorno extralaboral sobre el trabajo puede afectar negativamente la motivación, el rendimiento y la satisfacción de los trabajadores, lo que a su vez puede tener un impacto en la productividad de la empresa. Es por ello que resulta preocupante que tras la aplicación de la batería de evaluación de factores de riesgo psicosocial, la dimensión "Influencia del entorno extralaboral sobre el trabajo" haya sido calificada en riesgo alto o muy alto.

Para abordar esta situación, es necesario implementar un programa de intervenciones que permita reducir los factores de riesgo psicosocial presentes en el entorno laboral y extralaboral. Es importante que la empresa adopte medidas concretas y efectivas para garantizar la salud y el bienestar de sus empleados, y evitar así posibles problemas de salud mental y física.

En este sentido, las cajas de compensación, entidades promotoras de salud, Administradoras de riesgos laborales y profesionales, pueden ser aliados estratégicos para diseñar y llevar a cabo un programa de intervenciones adecuado. Entre las medidas que se pueden tomar para mejorar la influencia del entorno extralaboral sobre el trabajo, se encuentran proporcionar flexibilidad en el horario de trabajo, fomentar la comunicación abierta, ofrecer recursos y apoyo, crear un ambiente de trabajo positivo, ofrecer oportunidades de desarrollo profesional y ser comprensivo y flexible.

En resumen, es importante que la empresa actúe rápidamente para implementar un programa de intervenciones eficaz que permita reducir los factores de riesgo psicosocial presentes en la dimensión "Influencia del entorno extralaboral sobre el trabajo". La colaboración con entidades especializadas puede resultar de gran ayuda para lograr este objetivo. De esta manera, se puede mejorar la salud, el bienestar y la productividad de los empleados, lo que a su vez se traducirá en un beneficio para la empresa.',
                'objectives' => json_encode([
                    [
                        'number' => 1,
                        'description' => 'Realizar una encuesta de satisfacción laboral y calidad de vida para evaluar la percepción de los empleados acerca de su entorno extralaboral y su impacto en su desempeño laboral.'
                    ],
                    [
                        'number' => 2,
                        'description' => 'Implementar programas de bienestar para los empleados, tales como asesoramiento psicológico, servicios de cuidado infantil, clases de yoga y meditación, para mejorar la calidad de vida y reducir el estrés.'
                    ],
                    [
                        'number' => 3,
                        'description' => 'Ofrecer flexibilidad laboral a los empleados, como horarios de trabajo flexibles y días de trabajo remoto, para que puedan equilibrar sus responsabilidades laborales y personales.'
                    ],
                    [
                        'number' => 4,
                        'description' => 'Fomentar un ambiente laboral positivo y colaborativo a través de actividades de integración, capacitaciones en comunicación efectiva y trabajo en equipo.'
                    ],
                    [
                        'number' => 5,
                        'description' => 'Establecer políticas y procedimientos que promuevan el apoyo a los empleados que enfrenten situaciones personales difíciles, como permisos por enfermedad de un familiar cercano o asesoramiento en caso de situaciones de violencia doméstica.'
                    ]
                ]),
                'activities_6months' => json_encode([
                    'mes_1' => [
                        [
                            'number' => 1,
                            'description' => 'Realizar una encuesta de satisfacción laboral y calidad de vida, con el apoyo de la CCF, para evaluar la percepción de los empleados acerca de su entorno extralaboral y su impacto en su desempeño laboral. La entidad promotora de salud puede acompañar mediante recursos de reinversión para el diseño y ejecución de la encuesta.',
                            'objetivo_relacionado' => '1'
                        ],
                        [
                            'number' => 2,
                            'description' => 'Implementar un programa de asesoramiento psicológico, para ofrecer a los empleados herramientas y estrategias para manejar el estrés y mejorar su bienestar emocional. La ARL puede colaborar mediante recursos de reinversión para la capacitación del personal encargado de brindar este servicio.',
                            'objetivo_relacionado' => '2'
                        ]
                    ],
                    'mes_2' => [
                        [
                            'number' => 1,
                            'description' => 'Estudiar la posibilidad de subsidios de cuidado infantil a través de bonos, para apoyar a los empleados que tienen sus hijos pequeños. La EPS puede colaborar mediante recursos de reinversión para la capacitación del manejo del estrés en la paternidad.',
                            'objetivo_relacionado' => '2'
                        ],
                        [
                            'number' => 2,
                            'description' => 'Capacitar a los empleados en yoga y meditación, en colaboración con la EPS, para mejorar su bienestar físico y emocional. La entidad promotora de salud puede colaborar mediante recursos de reinversión para el diseño y ejecución de las capacitaciones.',
                            'objetivo_relacionado' => '2'
                        ]
                    ],
                    'mes_3' => [
                        [
                            'number' => 1,
                            'description' => 'Ofrecer horarios de trabajo flexibles a los empleados, en colaboración con la ARL, para que puedan equilibrar sus responsabilidades laborales y personales. La entidad puede colaborar mediante recursos de reinversión para la elaboración de políticas y procedimientos que permitan la implementación de este beneficio.',
                            'objetivo_relacionado' => '3'
                        ],
                        [
                            'number' => 2,
                            'description' => 'Realizar una actividad de integración, en colaboración con la CCF, para fomentar un ambiente laboral positivo y colaborativo. La entidad puede colaborar mediante recursos de reinversión para la organización de la actividad.',
                            'objetivo_relacionado' => '4'
                        ]
                    ],
                    'mes_4' => [
                        [
                            'number' => 1,
                            'description' => 'Se realizará un taller de resolución de conflictos en el ámbito laboral, en colaboración con la ARL, con el objetivo de promover la comunicación efectiva entre los empleados y mejorar el ambiente laboral.',
                            'objetivo_relacionado' => '4'
                        ],
                        [
                            'number' => 2,
                            'description' => 'En colaboración con la CCF, se llevará a cabo una charla sobre cuidado de la salud mental y estrategias de afrontamiento, con el objetivo de fomentar la prevención del estrés y la ansiedad en el trabajo.',
                            'objetivo_relacionado' => '2'
                        ]
                    ],
                    'mes_5' => [
                        [
                            'number' => 1,
                            'description' => 'Se ofrecerá un curso de primeros auxilios, en colaboración con la ARL, con el objetivo de preparar a los empleados para actuar en situaciones de emergencia.',
                            'objetivo_relacionado' => '1'
                        ],
                        [
                            'number' => 2,
                            'description' => 'En colaboración con la CCF, se realizará una actividad de integración entre los empleados y sus familias, con el objetivo de fomentar la cohesión y el bienestar familiar.',
                            'objetivo_relacionado' => '4'
                        ]
                    ],
                    'mes_6' => [
                        [
                            'number' => 1,
                            'description' => 'Se implementará un programa de flexibilidad laboral, para ofrecer a los empleados opciones de trabajo remoto y horarios flexibles, con el objetivo de mejorar el equilibrio entre su vida laboral y personal.',
                            'objetivo_relacionado' => '3'
                        ],
                        [
                            'number' => 2,
                            'description' => 'En colaboración con la CCF, se ofrecerá un taller de alimentación saludable, con el objetivo de fomentar hábitos saludables entre los empleados y mejorar su calidad de vida.',
                            'objetivo_relacionado' => '2'
                        ]
                    ]
                ]),
                'bibliography' => json_encode([
                    'Ministerio de Trabajo. (2013). Guía técnica colombiana GTC 45: Sistema de gestión de la seguridad y salud en el trabajo. Recuperado de https://www.mintrabajo.gov.co/documents/20147/23143/GTC45+Sistema+de+Gesti%C3%B3n+de+la+Seguridad+y+Salud+en+el+Trabajo/7c8a0e91-3ff5-7d5c-d212-4b165b0af3c5',
                    'Ministerio de Salud y Protección Social. (2015). Resolución 2646 de 2008: Por la cual se establecen las condiciones de seguridad y salud en el trabajo para el desarrollo de actividades económicas en el país. Recuperado de https://www.minsalud.gov.co/Normatividad_Nuevo/Resoluci%C3%B3n%202646%20de%202008.pdf',
                    'Ministerio de Trabajo. (2014). Guía técnica colombiana GTC 93: Identificación, evaluación y prevención de los factores de riesgo psicosocial en el trabajo. Recuperado de https://www.mintrabajo.gov.co/documents/20147/23143/GTC+93+Identificaci%C3%B3n+evaluaci%C3%B3n+y+prevenci%C3%B3n+de+los+factores+de+riesgo+psicosocial+en+el+trabajo/f33b9f9f-67ff-89d7-5ec8-63d13ef6fa15',
                    'Caja de Compensación Familiar. (s.f.). Servicios para empresas. Recuperado de https://www.cajacompensacion.com/servicios-empresas/',
                    'Administradora de Riesgos Laborales. (s.f.). Servicios para empresas. Recuperado de https://www.arlcolombia.com.co/servicios-para-empresas',
                    'Entidad Promotora de Salud. (s.f.). Servicios para empresas. Recuperado de https://www.eps.com.co/empresas/servicios-para-empresas/',
                    'Organización Internacional del Trabajo. (2010). Enciclopedia de Salud y Seguridad en el Trabajo. Recuperado de https://www.ilo.org/wcmsp5/groups/public/---ed_protect/---protrav/---safework/documents/publication/wcms_107896.pdf',
                    'Londoño, N. H. (2017). Bienestar laboral y su relación con el desempeño y la productividad organizacional. Revista Científica de Administración, 45, 44-53. Recuperado de https://www.redalyc.org/pdf/802/80250925005.pdf'
                ]),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],

            // 7. Desplazamiento vivienda trabajo vivienda
            [
                'dimension_code' => 'desplazamiento_vivienda_trabajo',
                'dimension_name' => 'Desplazamiento vivienda trabajo vivienda',
                'domain_code' => 'extralaboral',
                'questionnaire_type' => 'extralaboral',
                'introduction' => 'La evaluación de factores de riesgo psicosocial se ha vuelto una herramienta vital en el ámbito laboral, ya que permite identificar y prevenir posibles situaciones que puedan afectar la salud mental de los empleados. En Colombia, existen diversas normativas que regulan la implementación de estas evaluaciones, con el fin de garantizar un ambiente laboral saludable y seguro.

En este sentido, es importante mencionar que después de aplicar la batería de evaluación de factores de riesgo psicosocial en la dimensión "Desplazamiento vivienda trabajo vivienda", se identificó un riesgo alto o muy alto para esta área en su empresa. Por ello, es fundamental que se tomen medidas para implementar un programa de intervenciones que aborde esta dimensión y reduzca los riesgos identificados.

Las cajas de compensación, entidades promotoras de salud, administradoras de riesgos laborales y profesionales son actores clave en la implementación de programas de intervenciones en el ámbito laboral. Estas entidades cuentan con experiencia y conocimiento en la gestión de riesgos laborales y pueden brindar apoyo a su empresa en la implementación de un programa de intervenciones eficaz para abordar la dimensión de "Desplazamiento vivienda trabajo vivienda".

Es importante tener en cuenta que el desplazamiento vivienda trabajo vivienda puede afectar la motivación, el rendimiento y la satisfacción de los empleados. Por ello, es fundamental ofrecer flexibilidad en el horario de trabajo, fomentar la comunicación abierta, proporcionar recursos y apoyo, crear un ambiente de trabajo positivo, ofrecer oportunidades de desarrollo profesional y ser comprensivo y flexible con los empleados.

En conclusión, es necesario que se tomen medidas para abordar los riesgos identificados en la dimensión de "Desplazamiento vivienda trabajo vivienda". Con el apoyo de las entidades especializadas y la implementación de un programa de intervenciones adecuado, su empresa podrá garantizar un ambiente laboral saludable y seguro para sus empleados, mejorando así su rendimiento y satisfacción en el trabajo.',
                'objectives' => json_encode([
                    [
                        'number' => 1,
                        'description' => 'Realizar una encuesta para identificar los principales problemas de desplazamiento vivienda-trabajo-vivienda de los empleados de la empresa. Esta encuesta permitirá obtener información detallada sobre los tiempos de desplazamiento, los medios de transporte utilizados, los problemas de tráfico y los costos asociados. Además, permitirá conocer las necesidades específicas de los empleados en cuanto a flexibilidad de horarios y apoyo en la gestión de sus responsabilidades familiares y personales.'
                    ],
                    [
                        'number' => 2,
                        'description' => 'Desarrollar un plan de acción para mejorar el desplazamiento vivienda-trabajo-vivienda de los empleados, basado en los resultados de la encuesta y las mejores prácticas identificadas en otras empresas. Este plan de acción incluirá medidas concretas para fomentar el uso de medios de transporte sostenibles, promover la flexibilidad de horarios, facilitar el acceso a servicios de cuidado infantil y de personas mayores, entre otras medidas.'
                    ],
                    [
                        'number' => 3,
                        'description' => 'Capacitar a los empleados y supervisores en la importancia de la gestión del desplazamiento vivienda-trabajo-vivienda y en las medidas propuestas en el plan de acción. Esta capacitación permitirá que los empleados entiendan la relevancia de esta dimensión de la batería de riesgo psicosocial y puedan implementar las medidas propuestas de manera efectiva.'
                    ],
                    [
                        'number' => 4,
                        'description' => 'Establecer alianzas con entidades como cajas de compensación, entidades promotoras de salud, administradoras de riesgos laborales y profesionales para la implementación de programas de bienestar y apoyo para los empleados en el manejo de sus responsabilidades familiares y personales. Estos programas incluirán servicios de asesoramiento, apoyo para el cuidado infantil y de personas mayores, y otros recursos que permitan a los empleados equilibrar mejor su vida laboral y personal.'
                    ],
                    [
                        'number' => 5,
                        'description' => 'Monitorear y evaluar periódicamente la implementación del plan de acción y los resultados obtenidos en términos de mejora en el desplazamiento vivienda-trabajo-vivienda de los empleados. Este monitoreo permitirá hacer ajustes necesarios en las medidas implementadas y asegurar que se están logrando los objetivos propuestos en términos de bienestar y seguridad en el trabajo.'
                    ]
                ]),
                'activities_6months' => json_encode([
                    'mes_1' => [
                        [
                            'number' => 1,
                            'description' => 'Realizar la encuesta para identificar los principales problemas de desplazamiento vivienda-trabajo-vivienda de los empleados de la empresa.',
                            'objetivo_relacionado' => '1'
                        ],
                        [
                            'number' => 2,
                            'description' => 'Realizar una capacitación para los empleados y supervisores sobre la importancia de la gestión del desplazamiento vivienda-trabajo-vivienda y las medidas propuestas en el plan de acción. Se involucrará a la CCF para brindar asesoría y recursos de reinversión en la capacitación.',
                            'objetivo_relacionado' => '3'
                        ]
                    ],
                    'mes_2' => [
                        [
                            'number' => 1,
                            'description' => 'Analizar los resultados de la encuesta y elaborar un informe con los principales hallazgos. Se involucrará a la ARL para obtener asesoría en el análisis de los resultados.',
                            'objetivo_relacionado' => '1'
                        ],
                        [
                            'number' => 2,
                            'description' => 'Revisar con gerencia el cambio de horarios para no desplazarse en hora pico, se tomará encuesta de sondeo, basado en los resultados de la encuesta y las mejores prácticas identificadas en otras empresas la gerencia defina. Se involucrará a la ARL para obtener asesoría en la elaboración del plan de acción.',
                            'objetivo_relacionado' => '2'
                        ]
                    ],
                    'mes_3' => [
                        [
                            'number' => 1,
                            'description' => 'Presentar el plan de acción a los empleados y supervisores y recopilar retroalimentación para realizar ajustes necesarios. Se involucrará a la ARL para brindar asesoría y recursos de reinversión en la presentación del plan de cambio de turnos.',
                            'objetivo_relacionado' => '2'
                        ],
                        [
                            'number' => 2,
                            'description' => 'Establecer alianzas con entidades externas para la implementación de programas de bienestar y apoyo para los empleados en el manejo de sus responsabilidades familiares y personales. Se involucrará a la EPS, CCF y ARL para identificar las actividades que ofrecen a las empresas y su posible acompañamiento mediante recursos de reinversión.',
                            'objetivo_relacionado' => '4'
                        ]
                    ],
                    'mes_4' => [
                        [
                            'number' => 1,
                            'description' => 'Implementar medidas concretas para fomentar el uso de medios de transporte sostenibles y promover la flexibilidad de horarios. Se involucrará a la EPS y la ARL para brindar asesoría y recursos de reinversión en la implementación de las medidas.',
                            'objetivo_relacionado' => '2'
                        ],
                        [
                            'number' => 2,
                            'description' => 'Ofrecer capacitación en mejor aprovechamiento del tiempo durante el transporte público con posible acompañamiento de la CCF mediante recursos de reinversión.',
                            'objetivo_relacionado' => '4'
                        ]
                    ],
                    'mes_5' => [
                        [
                            'number' => 1,
                            'description' => 'Realizar una jornada de sensibilización sobre seguridad vial y medios de transporte sostenibles, en colaboración con la ARL. Esta actividad busca fomentar el uso de medios de transporte alternativos y seguros, y promover la cultura de la movilidad sostenible.',
                            'objetivo_relacionado' => '2'
                        ],
                        [
                            'number' => 2,
                            'description' => 'Organizar una feria de servicios para empleados, en la que se puedan encontrar vehículos eléctricos a crédito como alternativa a su movilidad.',
                            'objetivo_relacionado' => '4'
                        ]
                    ],
                    'mes_6' => [
                        [
                            'number' => 1,
                            'description' => 'Realizar una evaluación de la implementación del plan de acción y los resultados obtenidos hasta el momento. Esta actividad permitirá hacer ajustes necesarios en las medidas implementadas y asegurar que se están logrando los objetivos propuestos en términos de bienestar y seguridad en el trabajo.',
                            'objetivo_relacionado' => '5'
                        ],
                        [
                            'number' => 2,
                            'description' => 'Capacitar a los empleados y supervisores en la gestión del tiempo y el equilibrio entre la vida laboral y personal, en colaboración con la CCF. Esta actividad busca proporcionar herramientas para una mejor gestión del tiempo y apoyo para el equilibrio entre la vida laboral y personal de los empleados.',
                            'objetivo_relacionado' => '3'
                        ]
                    ]
                ]),
                'bibliography' => json_encode([
                    'Ministerio de Trabajo. (2013). Guía técnica colombiana GTC 45: Sistema de gestión de la seguridad y salud en el trabajo. Recuperado de https://www.mintrabajo.gov.co/documents/20147/23143/GTC45+Sistema+de+Gesti%C3%B3n+de+la+Seguridad+y+Salud+en+el+Trabajo/7c8a0e91-3ff5-7d5c-d212-4b165b0af3c5',
                    'Ministerio de Salud y Protección Social. (2015). Resolución 2646 de 2008: Por la cual se establecen las condiciones de seguridad y salud en el trabajo para el desarrollo de actividades económicas en el país. Recuperado de https://www.minsalud.gov.co/Normatividad_Nuevo/Resoluci%C3%B3n%202646%20de%202008.pdf',
                    'Ministerio de Trabajo. (2014). Guía técnica colombiana GTC 93: Identificación, evaluación y prevención de los factores de riesgo psicosocial en el trabajo. Recuperado de https://www.mintrabajo.gov.co/documents/20147/23143/GTC+93+Identificaci%C3%B3n+evaluaci%C3%B3n+y+prevenci%C3%B3n+de+los+factores+de+riesgo+psicosocial+en+el+trabajo/f33b9f9f-67ff-89d7-5ec8-63d13ef6fa15',
                    'Caja de Compensación Familiar. (s.f.). Servicios para empresas. Recuperado de https://www.cajacompensacion.com/servicios-empresas/',
                    'Administradora de Riesgos Laborales. (s.f.). Servicios para empresas. Recuperado de https://www.arlcolombia.com.co/servicios-para-empresas',
                    'Entidad Promotora de Salud. (s.f.). Servicios para empresas. Recuperado de https://www.eps.com.co/empresas/servicios-para-empresas/',
                    'Organización Internacional del Trabajo. (2010). Enciclopedia de Salud y Seguridad en el Trabajo. Recuperado de https://www.ilo.org/wcmsp5/groups/public/---ed_protect/---protrav/---safework/documents/publication/wcms_107896.pdf',
                    'Londoño, N. H. (2017). Bienestar laboral y su relación con el desempeño y la productividad organizacional. Revista Científica de Administración, 45, 44-53. Recuperado de https://www.redalyc.org/pdf/802/80250925005.pdf'
                ]),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        // Insert all data into the action_plans table
        $this->db->table('action_plans')->insertBatch($data);
    }
}
