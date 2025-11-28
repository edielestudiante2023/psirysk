<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class EstresSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'dimension_code' => 'estres',
                'dimension_name' => 'Estrés',
                'domain_code' => 'estres',
                'questionnaire_type' => 'estres',
                'introduction' => 'La evaluación de los factores de riesgo psicosocial en el lugar de trabajo es crucial para garantizar la salud y el bienestar de los empleados. En Colombia, el marco normativo exige a las empresas realizar una batería de evaluación de factores de riesgo psicosocial para identificar las condiciones laborales que puedan poner en riesgo la salud mental y física de los trabajadores. Uno de los principales riesgos identificados en esta batería es el estrés, que puede afectar significativamente la motivación, el rendimiento y la satisfacción de los empleados.

En el caso de su empresa, después de aplicar la batería de evaluación de factores de riesgo psicosocial, se identificó que la dimensión de estrés se calificó en riesgo alto o muy alto. Esto indica que su empresa debe tomar medidas inmediatas para prevenir o mitigar los efectos del estrés en sus empleados.

Un programa de intervención efectivo para abordar el estrés laboral puede ayudar a los empleados a manejar mejor las demandas del trabajo y mejorar su bienestar psicológico y físico. Al implementar medidas preventivas y promocionales, su empresa puede crear un ambiente de trabajo positivo y saludable para sus empleados.

En esta guía, proporcionaremos algunas recomendaciones para ayudar a su empresa a diseñar un programa de intervención efectivo para mitigar el estrés laboral. Al aplicar estas estrategias, su empresa puede mejorar la salud y el bienestar de sus empleados, lo que a su vez puede aumentar la productividad y la rentabilidad a largo plazo.',
                'objectives' => json_encode([
                    ['number' => 1, 'description' => 'Realizar encuestas de clima laboral y bienestar emocional para identificar los principales factores que generan estrés en los empleados. Estas encuestas pueden ser diseñadas por profesionales expertos en psicología organizacional y deben ser aplicadas de forma anónima y confidencial para obtener resultados más precisos.'],
                    ['number' => 2, 'description' => 'Crear un programa de gestión del estrés basado en la identificación de los factores estresantes más comunes en la empresa. Este programa debe incluir sesiones de entrenamiento y educación para los empleados sobre cómo manejar el estrés y herramientas prácticas para reducirlo.'],
                    ['number' => 3, 'description' => 'Implementar políticas que fomenten un ambiente de trabajo saludable, como la flexibilidad laboral, horarios de trabajo adecuados, un ambiente físico adecuado y seguro, y una comunicación abierta y efectiva entre los empleados y sus superiores.'],
                    ['number' => 4, 'description' => 'Ofrecer servicios de asesoría y apoyo emocional a los empleados que están pasando por situaciones difíciles en su vida personal o laboral. Estos servicios deben ser confidenciales y accesibles a todos los empleados.'],
                    ['number' => 5, 'description' => 'Evaluar periódicamente los resultados de las iniciativas implementadas para reducir el estrés y mejorar el bienestar emocional de los empleados. Para ello, se pueden diseñar encuestas de seguimiento y análisis de indicadores de desempeño, como la productividad y la rotación de personal.']
                ]),
                'activities_6months' => json_encode([
                    'mes_1' => [
                        ['number' => 1, 'description' => 'Diseñar y aplicar una encuesta de profundización de 200 preguntas para identificar si el estrés está ocasionando efectos sobre, el cuerpo, la psique o la autoestima y energía ante la vida, (Instrumento Cycloid Talent). Esta encuesta se aplicará de manera anónima y confidencial para obtener resultados precisos sobre los principales casos foco de intervención.', 'objetivo_relacionado' => 1],
                        ['number' => 2, 'description' => 'Realizar una reunión informativa con los empleados para presentar los resultados de la encuesta y discutir los principales hallazgos. En esta reunión se explicará el objetivo del programa de gestión del estrés y se brindará información sobre los recursos disponibles para manejar el estrés en el trabajo.', 'objetivo_relacionado' => 2]
                    ],
                    'mes_2' => [
                        ['number' => 1, 'description' => 'Ofrecer sesiones de entrenamiento y educación para los empleados sobre cómo manejar el estrés y herramientas prácticas para reducirlo. Estas sesiones pueden ser impartidas por expertos en psicología o en colaboración con la EPS o la ARL, que ofrecen servicios similares.', 'objetivo_relacionado' => 2],
                        ['number' => 2, 'description' => 'Establecer un grupo de apoyo emocional para los empleados que estén pasando por situaciones difíciles en su vida personal o laboral. Este grupo puede ser coordinado por la EPS o la CCF, que ofrecen servicios similares y pueden brindar recursos de reinversión.', 'objetivo_relacionado' => 4]
                    ],
                    'mes_3' => [
                        ['number' => 1, 'description' => 'Implementar políticas que fomenten un ambiente de trabajo saludable, como la flexibilidad laboral y horarios de trabajo adecuados. Para ello, se pueden establecer acuerdos de trabajo remoto o flexibilidad horaria en colaboración con la EPS o la ARL, que ofrecen servicios similares.', 'objetivo_relacionado' => 3],
                        ['number' => 2, 'description' => 'Realizar una jornada de promoción de la salud mental en la empresa, en colaboración con la EPS o la CCF, que pueden ofrecer recursos para la implementación de actividades lúdicas y educativas sobre el cuidado emocional.', 'objetivo_relacionado' => 3]
                    ],
                    'mes_4' => [
                        ['number' => 1, 'description' => 'Implementar un programa de pausas activas y ejercicios de respiración para los empleados, con el apoyo de la ARL o la EPS, que pueden brindar recursos y asesoría para su implementación.', 'objetivo_relacionado' => 2],
                        ['number' => 2, 'description' => 'Realizar una encuesta de seguimiento para evaluar la efectividad de las iniciativas implementadas hasta el momento. Esta encuesta debe ser aplicada de manera anónima y confidencial para obtener resultados precisos y se puede diseñar con el apoyo de expertos en psicología organizacional.', 'objetivo_relacionado' => 5]
                    ],
                    'mes_5' => [
                        ['number' => 1, 'description' => 'Se realizará una jornada de actividades físicas para los empleados en un lugar externo a la empresa, en la que se fomente el trabajo en equipo y la recreación. Esta actividad será organizada por la EPS y contará con la participación de un grupo de profesionales expertos en actividades deportivas y recreativas.', 'objetivo_relacionado' => 3],
                        ['number' => 2, 'description' => 'Se llevará a cabo una charla sobre técnicas de relajación y meditación para el manejo del estrés, en la que se explicará cómo realizar ejercicios de respiración y meditación. Esta actividad será impartida por un profesional experto en terapias alternativas y podrá ser organizada en conjunto con la ARL.', 'objetivo_relacionado' => 2]
                    ],
                    'mes_6' => [
                        ['number' => 1, 'description' => 'Se realizará una sesión de retroalimentación con los empleados, en la que se evaluarán los resultados de las iniciativas implementadas para reducir el estrés y mejorar el bienestar emocional en la empresa. Esta actividad será coordinada por un profesional experto en psicología organizacional y podrá contar con la participación de representantes de la ARL y la EPS.', 'objetivo_relacionado' => 5],
                        ['number' => 2, 'description' => 'Se ofrecerá una capacitación en habilidades sociales y comunicación efectiva para los empleados, en la que se enseñarán técnicas para la resolución de conflictos y la mejora de la comunicación interpersonal. Esta actividad será coordinada por un profesional experto en habilidades sociales y podrá ser organizada en conjunto con la EPS.', 'objetivo_relacionado' => 3]
                    ]
                ]),
                'bibliography' => json_encode([
                    'Ministerio de Trabajo. (2012). Guía técnica colombiana GTC 45: Sistema de gestión de la seguridad y salud en el trabajo. Bogotá, Colombia: Ministerio de Trabajo.',
                    'Ministerio de Salud y Protección Social. (2015). Resolución 2646 de 2008. Por la cual se establecen disposiciones para la identificación, evaluación, prevención, intervención y monitoreo de la exposición a factores de riesgo psicosocial en el trabajo y para la determinación del origen de las patologías causadas por el estrés ocupacional.',
                    'Organización Internacional del Trabajo. (2017). Encuesta global sobre el estrés en el trabajo.',
                    'Rodríguez, J. M., & Llorens, S. (2015). Estrés laboral y salud: causas, consecuencias y estrategias de afrontamiento. Revista de Psicología del Trabajo y de las Organizaciones, 31(2), 105-112.',
                    'Salanova, M., Llorens, S., & Cifre, E. (2013). El estrés laboral: Una revisión teórica con énfasis en los modelos de demandas y recursos. Anales de Psicología, 29(3), 784-793.',
                    'Torres, M. V., & González-Romá, V. (2014). Clima laboral: una revisión teórica y conceptual. Revista de Psicología del Trabajo y de las Organizaciones, 30(2), 101-108.'
                ]),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        $this->db->table('action_plans')->insertBatch($data);
    }
}
