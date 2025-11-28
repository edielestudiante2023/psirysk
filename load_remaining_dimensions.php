<?php

// Script directo para cargar las dimensiones restantes sin CodeIgniter
// Esto es más rápido para cargar datos masivos

$host = 'localhost';
$db = 'psyrisk';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Conectado a la base de datos.\n";

    // Contar dimensiones actuales
    $count = $pdo->query("SELECT COUNT(*) FROM action_plans")->fetchColumn();
    echo "Dimensiones actuales: $count\n\n";

    $stmt = $pdo->prepare("
        INSERT INTO action_plans (
            dimension_code, dimension_name, domain_code, questionnaire_type,
            introduction, objectives, activities_6months, bibliography,
            created_at, updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
    ");

    $dimensions = [
        // 9. Influencia del trabajo sobre el entorno extralaboral
        [
            'influencia_trabajo_entorno_extralaboral',
            'Influencia del Trabajo sobre el Entorno Extralaboral',
            'demandas_trabajo',
            'intralaboral',
            'Me dirijo a usted para expresar la importancia de implementar un sistema de vigilancia epidemiológica para la dimensión "Influencia del trabajo sobre el entorno extralaboral" en su empresa. Como ya se ha aplicado la batería de evaluación de factores de riesgo psicosocial y se ha encontrado que esta dimensión presenta un riesgo alto o muy alto, es crucial tomar medidas para prevenir y controlar posibles problemas de salud en sus trabajadores.

La influencia del trabajo sobre el entorno extralaboral se refiere a la capacidad del trabajo para afectar la vida personal y familiar del trabajador, y puede manifestarse de diversas formas como el estrés laboral, la carga emocional, la falta de apoyo social, entre otros. Estos factores pueden generar efectos negativos en la salud y el bienestar de los trabajadores, así como en su rendimiento y productividad laboral.

En Colombia, el marco normativo establece que es responsabilidad de los empleadores garantizar un ambiente laboral seguro y saludable, y que los trabajadores tengan acceso a medidas preventivas y de atención en salud mental. El Ministerio de Trabajo de Colombia ha establecido protocolos de intervención para la prevención y control de los riesgos psicosociales en el lugar de trabajo.

Es importante destacar que la implementación de un sistema de vigilancia epidemiológica permitirá detectar y controlar los factores de riesgo psicosocial, así como evaluar el impacto de las medidas de prevención y control implementadas. Este sistema permitirá una gestión más efectiva de los riesgos psicosociales, reducir el absentismo laboral, aumentar la productividad y mejorar la calidad de vida de los trabajadores.',
            json_encode([
                ['number' => 1, 'description' => 'Identificar los factores de riesgo psicosocial que influyen en la dimensión "Influencia del trabajo sobre el entorno extralaboral". Para ello, se pueden utilizar encuestas o cuestionarios que permitan obtener información precisa sobre los factores que generan estrés en los trabajadores y afectan su vida personal y familiar.'],
                ['number' => 2, 'description' => 'Evaluar el impacto de los factores de riesgo psicosocial identificados en el objetivo anterior. Se pueden utilizar herramientas como encuestas o entrevistas para obtener información sobre el impacto de estos factores en la salud y el bienestar de los trabajadores.'],
                ['number' => 3, 'description' => 'Implementar medidas de prevención y control para reducir los factores de riesgo psicosocial identificados. Estas medidas pueden incluir programas de apoyo psicológico, capacitación en habilidades de afrontamiento y gestión del estrés, entre otros.'],
                ['number' => 4, 'description' => 'Promover la participación activa de los trabajadores en la gestión de los riesgos psicosociales. Se puede utilizar una estrategia participativa que involucre a los trabajadores en la identificación y prevención de los riesgos psicosociales en su lugar de trabajo.'],
                ['number' => 5, 'description' => 'Evaluar periódicamente el impacto de las medidas implementadas y realizar un re diagnóstico de la dimensión "Influencia del trabajo sobre el entorno extralaboral". Es importante monitorear continuamente el impacto de las medidas implementadas para disminuir la exposición y realizar ajustes si es necesario.']
            ]),
            json_encode([
                'mes_1' => [
                    ['number' => 1, 'description' => 'Realizar una encuesta para identificar los factores de riesgo psicosocial que influyen en la dimensión "Influencia del trabajo sobre el entorno extralaboral".', 'objetivo_relacionado' => 1],
                    ['number' => 2, 'description' => 'Realizar entrevistas individuales para profundizar en la información obtenida a través de la encuesta y evaluar el impacto de los factores de riesgo psicosocial identificados.', 'objetivo_relacionado' => 2]
                ],
                'mes_2' => [
                    ['number' => 1, 'description' => 'Analizar la información recopilada en la encuesta y las entrevistas para identificar los factores de riesgo psicosocial más relevantes.', 'objetivo_relacionado' => 1],
                    ['number' => 2, 'description' => 'Realizar una sesión informativa para dar a conocer los resultados de la encuesta y las entrevistas a los trabajadores y promover su participación activa en la gestión de los riesgos psicosociales.', 'objetivo_relacionado' => 4]
                ],
                'mes_3' => [
                    ['number' => 1, 'description' => 'Implementar medidas de prevención y control para reducir los factores de riesgo psicosocial identificados, como la creación de programas de apoyo psicológico y la capacitación en habilidades de afrontamiento y gestión del estrés.', 'objetivo_relacionado' => 3],
                    ['number' => 2, 'description' => 'Realizar una campaña de sensibilización para fomentar la conciencia sobre la importancia de equilibrar el trabajo y la vida personal.', 'objetivo_relacionado' => 3]
                ],
                'mes_4' => [
                    ['number' => 1, 'description' => 'Realizar una evaluación periódica para medir el impacto de las medidas implementadas en la salud y bienestar de los trabajadores.', 'objetivo_relacionado' => 5],
                    ['number' => 2, 'description' => 'Identificar nuevas áreas de riesgo y tomar medidas preventivas.', 'objetivo_relacionado' => 5]
                ],
                'mes_5' => [
                    ['number' => 1, 'description' => 'Realizar una sesión informativa para dar a conocer los resultados de la evaluación periódica a los trabajadores y recibir retroalimentación sobre la efectividad de las medidas implementadas.', 'objetivo_relacionado' => 4],
                    ['number' => 2, 'description' => 'Implementar ajustes en las medidas de prevención y control en función de la retroalimentación recibida.', 'objetivo_relacionado' => 5]
                ],
                'mes_6' => [
                    ['number' => 1, 'description' => 'Realizar una nueva encuesta para evaluar el impacto de los ajustes implementados.', 'objetivo_relacionado' => 5],
                    ['number' => 2, 'description' => 'Elaborar un informe final con los resultados de las evaluaciones y las medidas implementadas para mejorar la Influencia del trabajo sobre el entorno extralaboral.', 'objetivo_relacionado' => 5]
                ]
            ]),
            json_encode([
                'Ministerio de Trabajo de Colombia. (2017). Guía técnica colombiana GTC 45. Sistema de gestión de la seguridad y salud en el trabajo.',
                'Ministerio de Trabajo de Colombia. (2018). Guía de intervención para factores psicosociales.',
                'Ley 1562 de 2012, Por la cual se modifica el Sistema de Riesgos Laborales y se dictan otras disposiciones en materia de Salud Ocupacional.',
                'Salgado, J. F., & Bermúdez, L. M. (2018). Influencia del trabajo en el entorno extralaboral: un reto para la seguridad y salud en el trabajo.',
                'Organización Internacional del Trabajo. (2012). Estrés en el trabajo: un desafío colectivo.'
            ])
        ],

        // Continúa en siguiente mensaje debido a límite de tokens...
    ];

    $added = 0;
    foreach ($dimensions as $dim) {
        try {
            $stmt->execute($dim);
            $added++;
            echo "✓ Agregada: {$dim[1]}\n";
        } catch (Exception $e) {
            echo "✗ Error en {$dim[1]}: " . $e->getMessage() . "\n";
        }
    }

    echo "\n===================\n";
    echo "Dimensiones agregadas: $added\n";
    echo "Total en BD: " . $pdo->query("SELECT COUNT(*) FROM action_plans")->fetchColumn() . "\n";

} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage() . "\n");
}
