<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ActionPlansSeeder3 extends Seeder
{
    public function run()
    {
        $data = [
            // 6. Demandas ambientales y de esfuerzo físico
            [
                'dimension_code' => 'demandas_ambientales_esfuerzo_fisico',
                'dimension_name' => 'Demandas Ambientales y de Esfuerzo Físico',
                'domain_code' => 'demandas_trabajo',
                'questionnaire_type' => 'intralaboral',
                'introduction' => 'Nos complace tener la oportunidad de trabajar con usted en la implementación de un sistema de vigilancia epidemiológica para la dimensión de demandas ambientales y de esfuerzo físico en su empresa. Al haber identificado esta dimensión como de alto riesgo en la batería de evaluación de factores de riesgo psicosocial, es importante abordarla de manera proactiva y efectiva.

En Colombia, el marco normativo establece la obligación de los empleadores de identificar, evaluar y controlar los factores de riesgo psicosocial en el lugar de trabajo, incluyendo las demandas ambientales y de esfuerzo físico. Además, el Ministerio de Trabajo ha establecido protocolos de intervención para prevenir y controlar estos riesgos, los cuales deben ser seguidos por todas las empresas.

La importancia de abordar estas demandas radica en su impacto en la salud y bienestar de los trabajadores. Las demandas ambientales y de esfuerzo físico pueden causar fatiga, dolor muscular, lesiones y estrés en el cuerpo, lo que puede llevar a una disminución en la calidad de vida laboral y a un aumento en las tasas de ausentismo y rotación de personal.

Es por eso que es crucial implementar un sistema de vigilancia epidemiológica para monitorear continuamente las demandas ambientales y de esfuerzo físico en su lugar de trabajo, y tomar medidas preventivas y correctivas necesarias.',
                'objectives' => json_encode([
                    ['number' => 1, 'description' => 'Identificar las demandas ambientales y de esfuerzo físico específicas que afectan a los trabajadores de la empresa a través de encuestas y otras herramientas de diagnóstico. Esto permitirá comprender las necesidades de los empleados y diseñar medidas preventivas y correctivas específicas.'],
                    ['number' => 2, 'description' => 'Implementar medidas de control de ingeniería para reducir la exposición de los trabajadores a demandas ambientales y de esfuerzo físico, como la instalación de sistemas de ventilación adecuados, la eliminación de obstáculos en las áreas de trabajo y la selección de herramientas ergonómicas.'],
                    ['number' => 3, 'description' => 'Capacitar a los trabajadores sobre los riesgos asociados con las demandas ambientales y de esfuerzo físico, y sobre las medidas preventivas y correctivas específicas implementadas por la empresa. Esto promoverá una cultura de prevención de riesgos y conciencia sobre la importancia de la seguridad y salud en el trabajo.'],
                    ['number' => 4, 'description' => 'Establecer un sistema de vigilancia epidemiológica para monitorear continuamente las demandas ambientales y de esfuerzo físico en el lugar de trabajo, y tomar medidas preventivas y correctivas necesarias. Esto permitirá a la empresa identificar y abordar rápidamente cualquier problema que surja.'],
                    ['number' => 5, 'description' => 'Evaluar periódicamente la efectividad de las medidas preventivas y correctivas implementadas por la empresa, y hacer ajustes necesarios para mejorar continuamente la seguridad y salud en el trabajo.']
                ]),
                'activities_6months' => json_encode([
                    'mes_1' => [
                        ['number' => 1, 'description' => 'Realizar una encuesta de clima laboral que incluya preguntas específicas sobre las demandas ambientales y de esfuerzo físico en la empresa, y analizar los resultados para identificar áreas de mejora.', 'objetivo_relacionado' => 1],
                        ['number' => 2, 'description' => 'Capacitar a los trabajadores sobre los riesgos asociados con las demandas ambientales y de esfuerzo físico, y sobre las medidas preventivas y correctivas específicas implementadas por la empresa.', 'objetivo_relacionado' => 3]
                    ],
                    'mes_2' => [
                        ['number' => 1, 'description' => 'Realizar una inspección en las áreas de trabajo para identificar obstáculos y riesgos asociados con las demandas ambientales y de esfuerzo físico, y diseñar medidas preventivas y correctivas específicas.', 'objetivo_relacionado' => 1],
                        ['number' => 2, 'description' => 'Realizar una evaluación ergonómica de los puestos de trabajo para identificar riesgos asociados con las demandas ambientales y de esfuerzo físico, y diseñar medidas preventivas y correctivas específicas.', 'objetivo_relacionado' => 1]
                    ],
                    'mes_3' => [
                        ['number' => 1, 'description' => 'Realizar una campaña de sensibilización sobre la importancia de la seguridad y salud en el trabajo y sobre los riesgos asociados con las demandas ambientales y de esfuerzo físico.', 'objetivo_relacionado' => 3],
                        ['number' => 2, 'description' => 'Implementar medidas de control de ingeniería para reducir la exposición de los trabajadores a demandas ambientales y de esfuerzo físico, como la instalación de sistemas de ventilación adecuados y la eliminación de obstáculos en las áreas de trabajo.', 'objetivo_relacionado' => 2]
                    ],
                    'mes_4' => [
                        ['number' => 1, 'description' => 'Realizar una evaluación de los riesgos asociados con las demandas ambientales y de esfuerzo físico en el lugar de trabajo y diseñar medidas preventivas y correctivas específicas.', 'objetivo_relacionado' => 1],
                        ['number' => 2, 'description' => 'Capacitar a los trabajadores sobre el uso adecuado de las herramientas ergonómicas y la importancia de la postura y el movimiento correctos para prevenir lesiones asociadas con las demandas ambientales y de esfuerzo físico.', 'objetivo_relacionado' => 3]
                    ],
                    'mes_5' => [
                        ['number' => 1, 'description' => 'Realizar una auditoría interna de la implementación de las medidas preventivas y correctivas específicas diseñadas en los meses anteriores y hacer ajustes necesarios para mejorar su efectividad.', 'objetivo_relacionado' => 5],
                        ['number' => 2, 'description' => 'Realizar una campaña de sensibilización sobre la importancia de la actividad física y la ergonomía para la prevención de lesiones asociadas con las demandas ambientales y de esfuerzo físico.', 'objetivo_relacionado' => 3]
                    ],
                    'mes_6' => [
                        ['number' => 1, 'description' => 'Realizar una evaluación de la efectividad de las medidas preventivas y correctivas implementadas por la empresa para reducir las demandas ambientales y de esfuerzo físico.', 'objetivo_relacionado' => 5],
                        ['number' => 2, 'description' => 'Realizar un taller de retroalimentación con los trabajadores para recopilar sus opiniones y sugerencias sobre las medidas preventivas y correctivas implementadas.', 'objetivo_relacionado' => 3]
                    ]
                ]),
                'bibliography' => json_encode([
                    'Ministerio de Trabajo de Colombia. (2017). Protocolo para la Prevención, Diagnóstico y Control de los Factores de Riesgo Psicosocial en el Trabajo.',
                    'Gómez, V., & Gómez, J. (2018). Factores psicosociales en el trabajo y su relación con la salud de los trabajadores en Colombia. Revista Gerencia y Políticas de Salud, 17(35), 80-95.',
                    'La Rocca, G., & Giraldo, M. (2019). Demanda de esfuerzo físico y síntomas musculoesqueléticos en trabajadores de una fábrica de calzado en Colombia. Revista Salud UIS, 51(2), 109-116.',
                    'Ministerio del Trabajo. (2017). Guía técnica colombiana GTC 45: Sistemas de gestión de la seguridad y salud en el trabajo.',
                    'Organización Internacional del Trabajo. (2011). Carga de trabajo y fatiga: Directrices ergonómicas aplicables a la protección de la salud y la seguridad en el trabajo.'
                ]),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],

            // 7. Demandas emocionales
            [
                'dimension_code' => 'demandas_emocionales',
                'dimension_name' => 'Demandas Emocionales',
                'domain_code' => 'demandas_trabajo',
                'questionnaire_type' => 'intralaboral',
                'introduction' => 'Como empresa comprometida con la salud y el bienestar de sus empleados, es importante que esté al tanto de los factores de riesgo psicosocial presentes en su organización. Uno de estos factores de riesgo es la dimensión de las demandas emocionales, que ha sido identificada como de alto y muy alto riesgo en su empresa.

Las demandas emocionales se refieren a las expectativas que otras personas tienen sobre cómo se deben sentir, expresar o controlar nuestras emociones en determinadas situaciones. Estas demandas pueden ser estresantes y agotadoras, y si no se manejan adecuadamente, pueden contribuir a la aparición de problemas de salud mental como la ansiedad y la depresión.

En Colombia, el Ministerio de Trabajo ha definido protocolos de intervención para abordar los factores de riesgo psicosocial en las empresas. Estos protocolos incluyen la realización de evaluaciones periódicas de los factores de riesgo, la implementación de medidas preventivas y correctivas, y la capacitación de los empleados y directivos en el manejo de estos factores.',
                'objectives' => json_encode([
                    ['number' => 1, 'description' => 'Identificar las fuentes de demandas emocionales presentes en la empresa mediante la realización de encuestas y entrevistas a los empleados y la revisión de los procesos y políticas internas.'],
                    ['number' => 2, 'description' => 'Desarrollar un plan de capacitación para los empleados y directivos en el manejo de las demandas emocionales, incluyendo estrategias para mejorar la comunicación, el manejo del estrés y la resolución de conflictos.'],
                    ['number' => 3, 'description' => 'Implementar medidas preventivas y correctivas para reducir las demandas emocionales en la empresa, tales como la reorganización del trabajo, la flexibilización de horarios y la promoción del bienestar emocional.'],
                    ['number' => 4, 'description' => 'Monitorear periódicamente la presencia de demandas emocionales en la empresa a través de encuestas y otras herramientas de diagnóstico, para evaluar la efectividad de las medidas implementadas y realizar ajustes necesarios.'],
                    ['number' => 5, 'description' => 'Promover una cultura organizacional que fomente el bienestar emocional de los empleados, a través de la implementación de políticas y prácticas que promuevan la comunicación abierta, el trabajo en equipo y el reconocimiento del trabajo bien hecho.']
                ]),
                'activities_6months' => json_encode([
                    'mes_1' => [
                        ['number' => 1, 'description' => 'Realizar una encuesta de clima laboral enfocada en las demandas emocionales presentes en la empresa y su impacto en la salud mental de los empleados.', 'objetivo_relacionado' => 1],
                        ['number' => 2, 'description' => 'Realizar entrevistas individuales con empleados que hayan manifestado niveles altos de demandas emocionales en la encuesta.', 'objetivo_relacionado' => 1]
                    ],
                    'mes_2' => [
                        ['number' => 1, 'description' => 'Diseñar y ejecutar un programa de capacitación para empleados y directivos en el manejo de las demandas emocionales, incluyendo técnicas de comunicación efectiva, manejo del estrés y resolución de conflictos.', 'objetivo_relacionado' => 2],
                        ['number' => 2, 'description' => 'Elaborar una guía de recursos para el bienestar emocional de los empleados.', 'objetivo_relacionado' => 5]
                    ],
                    'mes_3' => [
                        ['number' => 1, 'description' => 'Revisar los procesos y políticas internas que puedan estar contribuyendo a las demandas emocionales de los empleados, y establecer un plan de reorganización.', 'objetivo_relacionado' => 3],
                        ['number' => 2, 'description' => 'Implementar una política de flexibilidad laboral que permita a los empleados manejar sus responsabilidades laborales y personales de manera más efectiva.', 'objetivo_relacionado' => 3]
                    ],
                    'mes_4' => [
                        ['number' => 1, 'description' => 'Monitorear el impacto de las medidas implementadas en la reducción de las demandas emocionales en la empresa, a través de una nueva encuesta de clima laboral.', 'objetivo_relacionado' => 4],
                        ['number' => 2, 'description' => 'Realizar un taller práctico para los empleados sobre técnicas para el manejo del estrés y la promoción del bienestar emocional.', 'objetivo_relacionado' => 2]
                    ],
                    'mes_5' => [
                        ['number' => 1, 'description' => 'Implementar un programa de reconocimiento y agradecimiento a los empleados por su trabajo bien hecho.', 'objetivo_relacionado' => 5],
                        ['number' => 2, 'description' => 'Organizar un evento interno para el Día de la Salud Mental.', 'objetivo_relacionado' => 5]
                    ],
                    'mes_6' => [
                        ['number' => 1, 'description' => 'Realizar una revisión final de las medidas implementadas y su efectividad en la reducción de las demandas emocionales.', 'objetivo_relacionado' => 4],
                        ['number' => 2, 'description' => 'Crear un espacio de retroalimentación y comunicación abierta entre empleados y directivos.', 'objetivo_relacionado' => 5]
                    ]
                ]),
                'bibliography' => json_encode([
                    'Ministerio de Trabajo. (2017). Guía técnica colombiana GTC 45: Sistema de gestión de la seguridad y salud en el trabajo.',
                    'Ministerio de Salud y Protección Social. (2015). Protocolo para la identificación, evaluación y prevención de los factores de riesgo psicosocial en el trabajo.',
                    'Organización Internacional del Trabajo. (2018). Encuesta Mundial sobre el Estrés Laboral.',
                    'Restrepo, D., Ramírez, S., & Londoño, N. (2019). Riesgos psicosociales en el trabajo: caracterización y medidas preventivas.',
                    'Salgado, J. F. (2018). Factores de riesgo psicosocial y su impacto en el bienestar laboral. Revista de Investigación Académica, 11, 1-16.'
                ]),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],

            // 8. Demandas cuantitativas
            [
                'dimension_code' => 'demandas_cuantitativas',
                'dimension_name' => 'Demandas Cuantitativas',
                'domain_code' => 'demandas_trabajo',
                'questionnaire_type' => 'intralaboral',
                'introduction' => 'Con el objetivo de llamar su atención sobre la importancia de realizar un sistema de vigilancia epidemiológica para la dimensión de "demandas cuantitativas" en su empresa, ya que esta dimensión ha sido identificada como un factor de riesgo psicosocial en su batería de evaluación de factores de riesgo psicosocial.

La demanda laboral es uno de los factores que pueden tener un impacto significativo en la salud mental y física de los trabajadores. El exceso de trabajo, la presión por cumplir plazos y la sobrecarga de responsabilidades pueden llevar a situaciones de estrés laboral, que a su vez pueden derivar en trastornos de ansiedad, depresión y otros problemas de salud mental.

En Colombia, el Ministerio de Trabajo ha establecido normas y protocolos para la prevención y el control de los riesgos psicosociales en el lugar de trabajo, como la Resolución 2646 de 2008, que establece las pautas para la identificación, evaluación y prevención de los factores de riesgo psicosocial.',
                'objectives' => json_encode([
                    ['number' => 1, 'description' => 'Identificar las áreas de la empresa que presentan mayores demandas cuantitativas y establecer medidas de intervención específicas para disminuir su impacto en la salud de los trabajadores.'],
                    ['number' => 2, 'description' => 'Diseñar y poner en marcha programas de capacitación y sensibilización para el personal de la empresa sobre las demandas cuantitativas y su impacto en la salud y bienestar de los trabajadores.'],
                    ['number' => 3, 'description' => 'Establecer medidas de control para disminuir la exposición a las demandas cuantitativas en las diferentes áreas de la empresa.'],
                    ['number' => 4, 'description' => 'Implementar un sistema de vigilancia epidemiológica para la identificación temprana de problemas de salud relacionados con las demandas cuantitativas.'],
                    ['number' => 5, 'description' => 'Monitorear continuamente el impacto de las medidas implementadas para disminuir la exposición a las demandas cuantitativas y realizar ajustes si es necesario.']
                ]),
                'activities_6months' => json_encode([
                    'mes_1' => [
                        ['number' => 1, 'description' => 'Realizar un diagnóstico detallado de las diferentes áreas de trabajo para evaluar el grado de exposición a las demandas cuantitativas mediante la aplicación de una encuesta y grupos focales.', 'objetivo_relacionado' => 1],
                        ['number' => 2, 'description' => 'Realizar una sesión de capacitación y sensibilización para los trabajadores sobre las demandas cuantitativas y su impacto en la salud y bienestar.', 'objetivo_relacionado' => 2]
                    ],
                    'mes_2' => [
                        ['number' => 1, 'description' => 'Identificar las áreas críticas en el diagnóstico realizado y diseñar planes de acción específicos para disminuir la exposición a las demandas cuantitativas.', 'objetivo_relacionado' => 3],
                        ['number' => 2, 'description' => 'Realizar una segunda sesión de capacitación y sensibilización para los trabajadores enfocada en las medidas de control establecidas.', 'objetivo_relacionado' => 2]
                    ],
                    'mes_3' => [
                        ['number' => 1, 'description' => 'Implementar las medidas de control establecidas en las áreas críticas de acuerdo con el plan de acción diseñado.', 'objetivo_relacionado' => 3],
                        ['number' => 2, 'description' => 'Realizar una evaluación médica periódica para detectar problemas de salud relacionados con las demandas cuantitativas, como parte del sistema de vigilancia epidemiológica implementado.', 'objetivo_relacionado' => 4]
                    ],
                    'mes_4' => [
                        ['number' => 1, 'description' => 'Realizar una evaluación del impacto de las medidas implementadas en la salud y bienestar de los trabajadores utilizando indicadores de seguimiento y evaluación establecidos.', 'objetivo_relacionado' => 5],
                        ['number' => 2, 'description' => 'Realizar una sesión de retroalimentación con los trabajadores para evaluar el impacto de las medidas implementadas.', 'objetivo_relacionado' => 5]
                    ],
                    'mes_5' => [
                        ['number' => 1, 'description' => 'Realizar ajustes y mejoras en las medidas implementadas en función de la evaluación del impacto y retroalimentación de los trabajadores.', 'objetivo_relacionado' => 5],
                        ['number' => 2, 'description' => 'Realizar una segunda evaluación médica periódica como parte del sistema de vigilancia epidemiológica implementado.', 'objetivo_relacionado' => 4]
                    ],
                    'mes_6' => [
                        ['number' => 1, 'description' => 'Realizar una evaluación final del impacto de las medidas implementadas en la salud y bienestar de los trabajadores.', 'objetivo_relacionado' => 5],
                        ['number' => 2, 'description' => 'Realizar una sesión de cierre y entrega de resultados destacando los logros alcanzados.', 'objetivo_relacionado' => 5]
                    ]
                ]),
                'bibliography' => json_encode([
                    'Ministerio de Trabajo de Colombia. (2017). Guía técnica colombiana GTC 45: Sistema de Gestión de Seguridad y Salud en el Trabajo.',
                    'Ministerio de Trabajo de Colombia. (2015). Resolución 2646 de 2008: Identificación, evaluación, prevención, intervención y monitoreo de factores de riesgo psicosocial.',
                    'Salgado, D., y Salgado, J. (2018). Evaluación del riesgo psicosocial en empresas colombianas: una revisión sistemática. Revista de la Facultad de Medicina, 66(2), 321-329.',
                    'Suárez, G., Medina, L., y Gómez, A. (2016). Riesgos psicosociales laborales: demandas psicológicas y control en trabajadores de la Universidad Nacional de Colombia.',
                    'Triana, K., Medina, L., y Fernández, A. (2015). Intervención en riesgos psicosociales en el trabajo: una experiencia en el sector salud.'
                ]),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        $this->db->table('action_plans')->insertBatch($data);
    }
}
