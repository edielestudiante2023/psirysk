<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RemainingIntralaboralSeeder extends Seeder
{
    public function run()
    {
        $data = [
            // 1. Influencia del Trabajo sobre el Entorno Extralaboral
            [
                'dimension_code' => 'influencia_trabajo_entorno_extralaboral',
                'dimension_name' => 'Influencia del Trabajo sobre el Entorno Extralaboral',
                'domain_code' => 'demandas_trabajo',
                'questionnaire_type' => 'intralaboral',
                'introduction' => 'Esta dimensión evalúa las demandas del tiempo destinado al trabajo que interfieren en el espacio de la vida personal y familiar del trabajador. Se refiere a la falta de tiempo para atender los asuntos personales y familiares debido a las exigencias laborales, lo cual puede generar conflicto entre el rol laboral y el personal o familiar.

La influencia del trabajo sobre el entorno extralaboral se considera un riesgo psicosocial cuando las demandas temporales del trabajo son tan elevadas que:
• Impiden al trabajador atender adecuadamente sus responsabilidades familiares
• Limitan significativamente el tiempo de descanso y recreación
• Generan conflictos frecuentes entre la vida laboral y personal
• Producen agotamiento que afecta las relaciones interpersonales fuera del trabajo

Para intervenir esta dimensión es fundamental establecer un equilibrio entre las demandas laborales y el tiempo personal, promover prácticas organizacionales que respeten los espacios de vida extralaboral y desarrollar estrategias para gestionar eficientemente el tiempo de trabajo.',
                'objectives' => json_encode([
                    [
                        'number' => 1,
                        'description' => 'Reducir la interferencia del tiempo de trabajo en las actividades personales y familiares mediante políticas de desconexión laboral y respeto de horarios.'
                    ],
                    [
                        'number' => 2,
                        'description' => 'Implementar estrategias de gestión del tiempo que permitan al trabajador cumplir con sus responsabilidades laborales sin afectar su vida personal.'
                    ],
                    [
                        'number' => 3,
                        'description' => 'Promover una cultura organizacional que valore el equilibrio entre la vida laboral y personal como factor de bienestar y productividad.'
                    ],
                    [
                        'number' => 4,
                        'description' => 'Desarrollar programas de apoyo que faciliten la conciliación entre las demandas del trabajo y las responsabilidades familiares.'
                    ],
                    [
                        'number' => 5,
                        'description' => 'Monitorear y ajustar las cargas de trabajo para prevenir la extensión excesiva de jornadas laborales que invadan el tiempo personal.'
                    ]
                ]),
                'activities_6months' => json_encode([
                    'mes_1' => [
                        [
                            'number' => 1,
                            'description' => 'Realizar diagnóstico sobre la interferencia actual del trabajo en la vida personal mediante encuestas específicas de conciliación vida-trabajo.',
                            'objetivo_relacionado' => 1
                        ],
                        [
                            'number' => 2,
                            'description' => 'Conformar un equipo multidisciplinario para diseñar políticas de equilibrio vida-trabajo que incluya representantes de diferentes niveles jerárquicos.',
                            'objetivo_relacionado' => 3
                        ]
                    ],
                    'mes_2' => [
                        [
                            'number' => 3,
                            'description' => 'Diseñar e implementar una política formal de desconexión laboral que establezca límites claros al contacto fuera del horario de trabajo.',
                            'objetivo_relacionado' => 1
                        ],
                        [
                            'number' => 4,
                            'description' => 'Desarrollar talleres de gestión del tiempo para líderes y colaboradores enfocados en productividad y eficiencia durante la jornada laboral.',
                            'objetivo_relacionado' => 2
                        ]
                    ],
                    'mes_3' => [
                        [
                            'number' => 5,
                            'description' => 'Implementar sistemas de flexibilidad horaria o trabajo remoto donde sea operativamente viable para facilitar la conciliación.',
                            'objetivo_relacionado' => 4
                        ],
                        [
                            'number' => 6,
                            'description' => 'Establecer mecanismos de control y monitoreo de horas extras y extensión de jornadas laborales con alertas preventivas.',
                            'objetivo_relacionado' => 5
                        ]
                    ],
                    'mes_4' => [
                        [
                            'number' => 7,
                            'description' => 'Realizar campañas de sensibilización sobre la importancia del equilibrio vida-trabajo para la salud y el desempeño organizacional.',
                            'objetivo_relacionado' => 3
                        ],
                        [
                            'number' => 8,
                            'description' => 'Crear programas de apoyo familiar (guarderías, permisos flexibles, apoyo en emergencias) que faciliten las responsabilidades extralaborales.',
                            'objetivo_relacionado' => 4
                        ]
                    ],
                    'mes_5' => [
                        [
                            'number' => 9,
                            'description' => 'Capacitar a líderes en la promoción del equilibrio vida-trabajo y en la gestión de equipos que respete los espacios personales.',
                            'objetivo_relacionado' => 3
                        ],
                        [
                            'number' => 10,
                            'description' => 'Revisar y ajustar cargas de trabajo identificadas como excesivas mediante redistribución de tareas o incorporación de recursos.',
                            'objetivo_relacionado' => 5
                        ]
                    ],
                    'mes_6' => [
                        [
                            'number' => 11,
                            'description' => 'Evaluar el impacto de las intervenciones mediante indicadores de conciliación, satisfacción laboral y bienestar personal.',
                            'objetivo_relacionado' => 1
                        ],
                        [
                            'number' => 12,
                            'description' => 'Establecer plan de mejora continua basado en resultados y retroalimentación para fortalecer políticas de equilibrio vida-trabajo.',
                            'objetivo_relacionado' => 3
                        ]
                    ]
                ]),
                'bibliography' => json_encode([
                    'Greenhaus, J. H., & Beutell, N. J. (1985). Sources of conflict between work and family roles. Academy of Management Review, 10(1), 76-88.',
                    'Allen, T. D., Johnson, R. C., Kiburz, K. M., & Shockley, K. M. (2013). Work-family conflict and flexible work arrangements: Deconstructing flexibility. Personnel Psychology, 66(2), 345-376.',
                    'Kossek, E. E., & Lautsch, B. A. (2012). Work-family boundary management styles in organizations: A cross-level model. Organizational Psychology Review, 2(2), 152-171.',
                    'Ministerio de la Protección Social de Colombia (2010). Batería de instrumentos para la evaluación de factores de riesgo psicosocial. Bogotá: Ministerio de la Protección Social.',
                    'Organización Internacional del Trabajo (2016). Workplace stress: A collective challenge. Ginebra: OIT.',
                    'Geurts, S. A., & Demerouti, E. (2003). Work/non-work interface: A review of theories and findings. In M. J. Schabracq, J. A. M. Winnubst, & C. L. Cooper (Eds.), The handbook of work and health psychology (pp. 279-312). Chichester, UK: Wiley.'
                ]),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],

            // 2. Exigencias de Responsabilidad del Cargo
            [
                'dimension_code' => 'exigencias_responsabilidad_cargo',
                'dimension_name' => 'Exigencias de Responsabilidad del Cargo',
                'domain_code' => 'demandas_trabajo',
                'questionnaire_type' => 'intralaboral',
                'introduction' => 'Esta dimensión evalúa el conjunto de obligaciones implícitas en el desempeño de un cargo, cuyos resultados no pueden ser transferidos a otras personas. Las exigencias de responsabilidad se refieren al impacto que tienen los errores o decisiones tomadas en el trabajo sobre la seguridad, la salud o el bienestar de otras personas, así como sobre recursos materiales, financieros o la imagen de la organización.

Las exigencias de responsabilidad del cargo se consideran un factor de riesgo psicosocial cuando:
• El trabajador tiene bajo su responsabilidad la vida, salud o seguridad de otras personas
• Las decisiones del cargo tienen consecuencias significativas sobre recursos importantes
• Existe presión constante por la posibilidad de cometer errores con alto impacto
• No se cuenta con los recursos, apoyo o autoridad suficientes para cumplir con las responsabilidades asignadas
• La rendición de cuentas es desproporcionada respecto al nivel de autonomía y control

Para intervenir adecuadamente esta dimensión es necesario equilibrar las responsabilidades con la autoridad, recursos y apoyo necesarios, establecer sistemas de respaldo y distribución de responsabilidades, y desarrollar competencias para la gestión de la presión asociada a cargos de alta responsabilidad.',
                'objectives' => json_encode([
                    [
                        'number' => 1,
                        'description' => 'Equilibrar las responsabilidades del cargo con la autoridad, autonomía y recursos necesarios para su adecuado desempeño.'
                    ],
                    [
                        'number' => 2,
                        'description' => 'Implementar sistemas de apoyo y respaldo que distribuyan adecuadamente las responsabilidades críticas y eviten la sobrecarga individual.'
                    ],
                    [
                        'number' => 3,
                        'description' => 'Desarrollar competencias en los trabajadores para gestionar la presión asociada a responsabilidades significativas mediante capacitación y entrenamiento.'
                    ],
                    [
                        'number' => 4,
                        'description' => 'Establecer protocolos claros de toma de decisiones y gestión de errores que reduzcan la incertidumbre y la presión psicológica.'
                    ],
                    [
                        'number' => 5,
                        'description' => 'Crear mecanismos de reconocimiento y apoyo psicosocial para trabajadores en cargos de alta responsabilidad que prevengan el desgaste emocional.'
                    ]
                ]),
                'activities_6months' => json_encode([
                    'mes_1' => [
                        [
                            'number' => 1,
                            'description' => 'Realizar análisis de cargos críticos identificando el nivel de responsabilidad, impacto de decisiones y recursos disponibles mediante entrevistas y cuestionarios.',
                            'objetivo_relacionado' => 1
                        ],
                        [
                            'number' => 2,
                            'description' => 'Mapear procesos de toma de decisiones críticas para identificar puntos de alta presión y responsabilidades concentradas en individuos específicos.',
                            'objetivo_relacionado' => 4
                        ]
                    ],
                    'mes_2' => [
                        [
                            'number' => 3,
                            'description' => 'Revisar y actualizar perfiles de cargo asegurando coherencia entre responsabilidades asignadas, autoridad delegada y recursos proporcionados.',
                            'objetivo_relacionado' => 1
                        ],
                        [
                            'number' => 4,
                            'description' => 'Diseñar sistemas de respaldo y distribución de responsabilidades críticas (backup roles) para evitar dependencia exclusiva de una persona.',
                            'objetivo_relacionado' => 2
                        ]
                    ],
                    'mes_3' => [
                        [
                            'number' => 5,
                            'description' => 'Implementar programas de capacitación en toma de decisiones bajo presión, gestión de estrés y manejo de responsabilidades críticas.',
                            'objetivo_relacionado' => 3
                        ],
                        [
                            'number' => 6,
                            'description' => 'Desarrollar protocolos de escalamiento y consulta para decisiones de alto impacto que distribuyan la responsabilidad de manera estructurada.',
                            'objetivo_relacionado' => 4
                        ]
                    ],
                    'mes_4' => [
                        [
                            'number' => 7,
                            'description' => 'Establecer mecanismos de apoyo entre pares (peer support) y mentoría para trabajadores en posiciones de alta responsabilidad.',
                            'objetivo_relacionado' => 5
                        ],
                        [
                            'number' => 8,
                            'description' => 'Implementar reuniones periódicas de revisión de carga de responsabilidad donde los trabajadores puedan expresar dificultades y recibir apoyo.',
                            'objetivo_relacionado' => 2
                        ]
                    ],
                    'mes_5' => [
                        [
                            'number' => 9,
                            'description' => 'Crear protocolos de gestión de errores basados en aprendizaje organizacional (no punitivos) que reduzcan el miedo y la presión psicológica.',
                            'objetivo_relacionado' => 4
                        ],
                        [
                            'number' => 10,
                            'description' => 'Desarrollar programa de reconocimiento específico para trabajadores que gestionan responsabilidades críticas de manera efectiva y sostenida.',
                            'objetivo_relacionado' => 5
                        ]
                    ],
                    'mes_6' => [
                        [
                            'number' => 11,
                            'description' => 'Evaluar el impacto de las intervenciones mediante indicadores de estrés percibido, satisfacción con recursos disponibles y confianza en toma de decisiones.',
                            'objetivo_relacionado' => 1
                        ],
                        [
                            'number' => 12,
                            'description' => 'Ajustar sistemas de apoyo y protocolos basándose en la retroalimentación y establecer planes de seguimiento semestral para cargos críticos.',
                            'objetivo_relacionado' => 2
                        ]
                    ]
                ]),
                'bibliography' => json_encode([
                    'Spector, P. E., & Jex, S. M. (1998). Development of four self-report measures of job stressors and strain: Interpersonal Conflict at Work Scale, Organizational Constraints Scale, Quantitative Workload Inventory, and Physical Symptoms Inventory. Journal of Occupational Health Psychology, 3(4), 356-367.',
                    'Flin, R., O\'Connor, P., & Crichton, M. (2008). Safety at the sharp end: A guide to non-technical skills. Aldershot, UK: Ashgate Publishing.',
                    'Karasek, R. A., & Theorell, T. (1990). Healthy work: Stress, productivity, and the reconstruction of working life. New York: Basic Books.',
                    'Ministerio de la Protección Social de Colombia (2010). Batería de instrumentos para la evaluación de factores de riesgo psicosocial. Bogotá: Ministerio de la Protección Social.',
                    'Reason, J. (2000). Human error: Models and management. BMJ, 320(7237), 768-770.',
                    'Dekker, S. (2014). The field guide to understanding human error (3rd ed.). Aldershot, UK: Ashgate Publishing.',
                    'Semmer, N. K., Jacobshagen, N., Meier, L. L., & Elfering, A. (2007). Occupational stress research: The "stress-as-offense-to-self" perspective. In S. McIntyre & J. Houdmont (Eds.), Occupational health psychology: European perspectives on research, education and practice (Vol. 2, pp. 43-60). Nottingham, UK: Nottingham University Press.'
                ]),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],

            // 3. Demandas de Carga Mental
            [
                'dimension_code' => 'demandas_carga_mental',
                'dimension_name' => 'Demandas de Carga Mental',
                'domain_code' => 'demandas_trabajo',
                'questionnaire_type' => 'intralaboral',
                'introduction' => 'Esta dimensión evalúa las demandas de procesamiento cognitivo que implica la tarea y que involucran procesos mentales superiores de atención, memoria y análisis de información para generar una respuesta. La carga mental está determinada por las características de la información (cantidad, complejidad y detalle) y los tiempos de que se dispone para procesarla.

Las demandas de carga mental se consideran un factor de riesgo psicosocial cuando:
• Se requiere procesar grandes volúmenes de información en tiempos limitados
• La información es compleja, ambigua o contradictoria
• Se exige atención sostenida durante períodos prolongados
• Es necesario realizar múltiples tareas simultáneamente de manera frecuente
• Hay interrupciones constantes que fragmentan la atención
• No se dispone de tiempo suficiente para la recuperación mental

Para intervenir esta dimensión es fundamental diseñar tareas que respeten los límites de la capacidad de procesamiento humana, alternar actividades de alta y baja demanda cognitiva, proporcionar tiempos adecuados de descanso mental y desarrollar estrategias para gestionar eficientemente la información y la atención.',
                'objectives' => json_encode([
                    [
                        'number' => 1,
                        'description' => 'Optimizar la cantidad y complejidad de información a procesar ajustándola a los tiempos disponibles y a la capacidad de procesamiento cognitivo.'
                    ],
                    [
                        'number' => 2,
                        'description' => 'Reducir las interrupciones y fragmentación del trabajo que dificultan la concentración y aumentan la carga mental.'
                    ],
                    [
                        'number' => 3,
                        'description' => 'Establecer períodos de descanso mental y alternancia de tareas para prevenir la fatiga cognitiva y mantener el rendimiento.'
                    ],
                    [
                        'number' => 4,
                        'description' => 'Desarrollar competencias en técnicas de gestión de la atención, organización de información y manejo de la multitarea.'
                    ],
                    [
                        'number' => 5,
                        'description' => 'Implementar herramientas tecnológicas y organizacionales que simplifiquen el procesamiento de información y reduzcan la carga cognitiva.'
                    ]
                ]),
                'activities_6months' => json_encode([
                    'mes_1' => [
                        [
                            'number' => 1,
                            'description' => 'Realizar análisis ergonómico cognitivo de puestos de trabajo identificando demandas de atención, memoria y procesamiento de información.',
                            'objetivo_relacionado' => 1
                        ],
                        [
                            'number' => 2,
                            'description' => 'Mapear fuentes de interrupción y fragmentación del trabajo mediante observación directa y registros de actividades durante jornada laboral.',
                            'objetivo_relacionado' => 2
                        ]
                    ],
                    'mes_2' => [
                        [
                            'number' => 3,
                            'description' => 'Rediseñar procesos de trabajo simplificando información innecesaria, estandarizando formatos y eliminando duplicidades en el procesamiento de datos.',
                            'objetivo_relacionado' => 1
                        ],
                        [
                            'number' => 4,
                            'description' => 'Implementar protocolos de gestión de interrupciones (horarios de disponibilidad, señales de concentración, filtros de comunicación no urgente).',
                            'objetivo_relacionado' => 2
                        ]
                    ],
                    'mes_3' => [
                        [
                            'number' => 5,
                            'description' => 'Establecer pausas activas cognitivas y períodos de descanso mental programados para tareas de alta demanda de concentración.',
                            'objetivo_relacionado' => 3
                        ],
                        [
                            'number' => 6,
                            'description' => 'Diseñar rotación de tareas que alterne actividades de alta demanda mental con otras de menor exigencia cognitiva durante la jornada.',
                            'objetivo_relacionado' => 3
                        ]
                    ],
                    'mes_4' => [
                        [
                            'number' => 7,
                            'description' => 'Capacitar en técnicas de gestión de la atención, manejo efectivo de múltiples tareas y estrategias de organización de información compleja.',
                            'objetivo_relacionado' => 4
                        ],
                        [
                            'number' => 8,
                            'description' => 'Implementar herramientas tecnológicas de apoyo cognitivo (software de gestión, automatización de tareas repetitivas, sistemas de recordatorios).',
                            'objetivo_relacionado' => 5
                        ]
                    ],
                    'mes_5' => [
                        [
                            'number' => 9,
                            'description' => 'Desarrollar ayudas de trabajo (checklists, plantillas, guías rápidas) que reduzcan la carga de memoria y faciliten decisiones rutinarias.',
                            'objetivo_relacionado' => 5
                        ],
                        [
                            'number' => 10,
                            'description' => 'Entrenar en técnicas de mindfulness y atención plena para mejorar capacidad de concentración y recuperación mental durante pausas.',
                            'objetivo_relacionado' => 4
                        ]
                    ],
                    'mes_6' => [
                        [
                            'number' => 11,
                            'description' => 'Evaluar impacto mediante indicadores de fatiga mental, errores cognitivos, capacidad de concentración y satisfacción con demandas del trabajo.',
                            'objetivo_relacionado' => 1
                        ],
                        [
                            'number' => 12,
                            'description' => 'Ajustar estrategias de intervención basándose en resultados y establecer monitoreo continuo de cargas mentales en puestos críticos.',
                            'objetivo_relacionado' => 3
                        ]
                    ]
                ]),
                'bibliography' => json_encode([
                    'Wickens, C. D. (2008). Multiple resources and mental workload. Human Factors, 50(3), 449-455.',
                    'Young, M. S., Brookhuis, K. A., Wickens, C. D., & Hancock, P. A. (2015). State of science: Mental workload in ergonomics. Ergonomics, 58(1), 1-17.',
                    'Longo, L. (Ed.). (2018). Mental workload: Models and applications. Communications in Computer and Information Science, Vol. 726. Cham, Switzerland: Springer.',
                    'Ministerio de la Protección Social de Colombia (2010). Batería de instrumentos para la evaluación de factores de riesgo psicosocial. Bogotá: Ministerio de la Protección Social.',
                    'Hockey, G. R. J. (2013). The psychology of fatigue: Work, effort and control. Cambridge, UK: Cambridge University Press.',
                    'Gopher, D., & Donchin, E. (1986). Workload: An examination of the concept. In K. R. Boff, L. Kaufman, & J. P. Thomas (Eds.), Handbook of perception and human performance, Vol. 2: Cognitive processes and performance (pp. 41-1-41-49). New York: Wiley.'
                ]),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],

            // 4. Consistencia del Rol
            [
                'dimension_code' => 'consistencia_rol',
                'dimension_name' => 'Consistencia del Rol',
                'domain_code' => 'demandas_trabajo',
                'questionnaire_type' => 'intralaboral',
                'introduction' => 'Esta dimensión se refiere a la compatibilidad o consistencia entre las diversas exigencias relacionadas con los principios de eficiencia, calidad técnica y ética propios del servicio o producto que ofrece la empresa. Evalúa si las demandas del trabajo son coherentes entre sí y no entran en conflicto con los valores, principios técnicos o éticos del trabajador o de la organización.

La inconsistencia del rol se considera un factor de riesgo psicosocial cuando:
• Se exige al trabajador cumplir con demandas que entran en conflicto entre sí
• Las presiones por eficiencia o productividad comprometen la calidad técnica del trabajo
• Se requiere actuar en contra de principios éticos o profesionales
• Existe contradicción entre lo que se dice y lo que se hace en la organización
• Las políticas organizacionales son incongruentes con las prácticas reales

Para intervenir esta dimensión es fundamental establecer coherencia entre las demandas organizacionales, alinear las prácticas con los valores declarados, resolver conflictos entre eficiencia y calidad, y crear espacios para que los trabajadores puedan expresar y resolver dilemas éticos o técnicos en su trabajo.',
                'objectives' => json_encode([
                    [
                        'number' => 1,
                        'description' => 'Alinear las demandas de eficiencia, productividad y calidad de manera que no generen conflictos irreconciliables para el trabajador.'
                    ],
                    [
                        'number' => 2,
                        'description' => 'Establecer coherencia entre los valores organizacionales declarados y las prácticas de trabajo requeridas en el día a día.'
                    ],
                    [
                        'number' => 3,
                        'description' => 'Crear espacios seguros para identificar y resolver dilemas éticos o técnicos que surjan en el desempeño del trabajo.'
                    ],
                    [
                        'number' => 4,
                        'description' => 'Fortalecer los criterios de calidad técnica y ética como parte integral de la evaluación del desempeño, no subordinados únicamente a resultados cuantitativos.'
                    ],
                    [
                        'number' => 5,
                        'description' => 'Desarrollar competencias en los trabajadores y líderes para identificar y gestionar conflictos de rol y tomar decisiones éticas.'
                    ]
                ]),
                'activities_6months' => json_encode([
                    'mes_1' => [
                        [
                            'number' => 1,
                            'description' => 'Realizar diagnóstico de conflictos de rol mediante grupos focales y encuestas que identifiquen demandas contradictorias percibidas por los trabajadores.',
                            'objetivo_relacionado' => 1
                        ],
                        [
                            'number' => 2,
                            'description' => 'Revisar políticas organizacionales, procedimientos y prácticas para identificar incongruencias entre valores declarados y demandas reales.',
                            'objetivo_relacionado' => 2
                        ]
                    ],
                    'mes_2' => [
                        [
                            'number' => 3,
                            'description' => 'Rediseñar procesos de trabajo que generen conflictos identificados entre eficiencia, calidad y principios éticos, priorizando soluciones integradas.',
                            'objetivo_relacionado' => 1
                        ],
                        [
                            'number' => 4,
                            'description' => 'Desarrollar código de ética aplicado y protocolos de toma de decisiones éticas específicos para situaciones frecuentes de conflicto.',
                            'objetivo_relacionado' => 3
                        ]
                    ],
                    'mes_3' => [
                        [
                            'number' => 5,
                            'description' => 'Implementar comités de ética o espacios de consulta donde los trabajadores puedan plantear dilemas sin temor a represalias.',
                            'objetivo_relacionado' => 3
                        ],
                        [
                            'number' => 6,
                            'description' => 'Ajustar indicadores de desempeño para incluir criterios de calidad técnica, ética profesional y satisfacción del cliente, no solo eficiencia.',
                            'objetivo_relacionado' => 4
                        ]
                    ],
                    'mes_4' => [
                        [
                            'number' => 7,
                            'description' => 'Capacitar a líderes en la gestión de conflictos de rol y en la promoción de coherencia entre demandas organizacionales.',
                            'objetivo_relacionado' => 5
                        ],
                        [
                            'number' => 8,
                            'description' => 'Desarrollar talleres de toma de decisiones éticas para trabajadores en posiciones que enfrentan frecuentemente dilemas de rol.',
                            'objetivo_relacionado' => 5
                        ]
                    ],
                    'mes_5' => [
                        [
                            'number' => 9,
                            'description' => 'Establecer canales de comunicación ascendente donde se puedan reportar situaciones de conflicto ético o técnico sin consecuencias negativas.',
                            'objetivo_relacionado' => 3
                        ],
                        [
                            'number' => 10,
                            'description' => 'Realizar campaña de comunicación interna que refuerce los valores organizacionales y ejemplifique su aplicación en situaciones reales de trabajo.',
                            'objetivo_relacionado' => 2
                        ]
                    ],
                    'mes_6' => [
                        [
                            'number' => 11,
                            'description' => 'Evaluar el impacto mediante indicadores de conflicto de rol, satisfacción con coherencia organizacional y casos reportados de dilemas éticos.',
                            'objetivo_relacionado' => 1
                        ],
                        [
                            'number' => 12,
                            'description' => 'Ajustar políticas y procedimientos basándose en casos identificados y establecer revisión periódica de consistencia entre valores y prácticas.',
                            'objetivo_relacionado' => 2
                        ]
                    ]
                ]),
                'bibliography' => json_encode([
                    'Kahn, R. L., Wolfe, D. M., Quinn, R. P., Snoek, J. D., & Rosenthal, R. A. (1964). Organizational stress: Studies in role conflict and ambiguity. New York: Wiley.',
                    'Rizzo, J. R., House, R. J., & Lirtzman, S. I. (1970). Role conflict and ambiguity in complex organizations. Administrative Science Quarterly, 15(2), 150-163.',
                    'Treviño, L. K., den Nieuwenboer, N. A., & Kish-Gephart, J. J. (2014). (Un)ethical behavior in organizations. Annual Review of Psychology, 65, 635-660.',
                    'Ministerio de la Protección Social de Colombia (2010). Batería de instrumentos para la evaluación de factores de riesgo psicosocial. Bogotá: Ministerio de la Protección Social.',
                    'Rest, J. R. (1986). Moral development: Advances in research and theory. New York: Praeger.',
                    'Schminke, M., Ambrose, M. L., & Neubaum, D. O. (2005). The effect of leader moral development on ethical climate and employee attitudes. Organizational Behavior and Human Decision Processes, 97(2), 135-151.'
                ]),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],

            // 5. Demandas de la Jornada de Trabajo
            [
                'dimension_code' => 'demandas_jornada_trabajo',
                'dimension_name' => 'Demandas de la Jornada de Trabajo',
                'domain_code' => 'demandas_trabajo',
                'questionnaire_type' => 'intralaboral',
                'introduction' => 'Esta dimensión evalúa las exigencias del tiempo laboral que se hacen al trabajador en términos de la duración y el horario de la jornada, así como los períodos de descanso que el trabajador tiene durante el trabajo. Incluye el número de horas trabajadas, la necesidad de trabajar en horarios nocturnos o rotativos, y la disponibilidad de pausas y descansos.

Las demandas de la jornada de trabajo se consideran un factor de riesgo psicosocial cuando:
• La jornada laboral es excesivamente prolongada de manera habitual
• Se trabaja en turnos nocturnos o rotativos que alteran los ritmos biológicos
• No se respetan los tiempos de descanso entre jornadas
• Las pausas durante la jornada son insuficientes o inexistentes
• Existe imprevisibilidad en los horarios que dificulta la planificación personal

Para intervenir esta dimensión es fundamental establecer jornadas de trabajo que respeten los límites legales y fisiológicos, diseñar sistemas de turnos que minimicen el impacto en la salud, garantizar períodos adecuados de descanso y pausas, y proporcionar previsibilidad en los horarios de trabajo.',
                'objectives' => json_encode([
                    [
                        'number' => 1,
                        'description' => 'Ajustar la duración de las jornadas laborales para que cumplan con la legislación y respeten los límites de fatiga física y mental.'
                    ],
                    [
                        'number' => 2,
                        'description' => 'Diseñar sistemas de turnos y trabajo nocturno que minimicen el impacto negativo sobre la salud y el bienestar de los trabajadores.'
                    ],
                    [
                        'number' => 3,
                        'description' => 'Garantizar períodos adecuados de descanso entre jornadas y pausas suficientes durante la jornada de trabajo para la recuperación.'
                    ],
                    [
                        'number' => 4,
                        'description' => 'Proporcionar previsibilidad y estabilidad en los horarios de trabajo que permita la planificación de la vida personal y familiar.'
                    ],
                    [
                        'number' => 5,
                        'description' => 'Implementar programas de vigilancia de la salud y apoyo específico para trabajadores en jornadas especiales (nocturnas, extendidas, rotativas).'
                    ]
                ]),
                'activities_6months' => json_encode([
                    'mes_1' => [
                        [
                            'number' => 1,
                            'description' => 'Realizar auditoría de jornadas laborales reales (no solo registradas) mediante análisis de registros, entrevistas y observación directa.',
                            'objetivo_relacionado' => 1
                        ],
                        [
                            'number' => 2,
                            'description' => 'Identificar áreas con trabajo nocturno o por turnos y evaluar el diseño actual de estos sistemas respecto a criterios ergonómicos y de salud.',
                            'objetivo_relacionado' => 2
                        ]
                    ],
                    'mes_2' => [
                        [
                            'number' => 3,
                            'description' => 'Rediseñar jornadas excesivas mediante análisis de carga de trabajo, redistribución de tareas o contratación de personal adicional donde sea necesario.',
                            'objetivo_relacionado' => 1
                        ],
                        [
                            'number' => 4,
                            'description' => 'Optimizar sistemas de turnos aplicando principios ergonómicos: rotación en sentido horario, turnos cortos nocturnos, descansos compensatorios adecuados.',
                            'objetivo_relacionado' => 2
                        ]
                    ],
                    'mes_3' => [
                        [
                            'number' => 5,
                            'description' => 'Implementar política formal de pausas y descansos que garantice tiempos mínimos de recuperación durante la jornada y entre jornadas.',
                            'objetivo_relacionado' => 3
                        ],
                        [
                            'number' => 6,
                            'description' => 'Establecer programación de horarios con al menos 2-4 semanas de anticipación para permitir planificación personal y familiar.',
                            'objetivo_relacionado' => 4
                        ]
                    ],
                    'mes_4' => [
                        [
                            'number' => 7,
                            'description' => 'Crear espacios adecuados para el descanso durante pausas (áreas cómodas, alejadas del ruido, con condiciones ambientales apropiadas).',
                            'objetivo_relacionado' => 3
                        ],
                        [
                            'number' => 8,
                            'description' => 'Capacitar a trabajadores en turnos nocturnos sobre higiene del sueño, nutrición adecuada y estrategias de adaptación a horarios especiales.',
                            'objetivo_relacionado' => 5
                        ]
                    ],
                    'mes_5' => [
                        [
                            'number' => 9,
                            'description' => 'Implementar programa de vigilancia de salud específico para trabajadores en jornadas especiales (evaluaciones médicas, monitoreo de fatiga).',
                            'objetivo_relacionado' => 5
                        ],
                        [
                            'number' => 10,
                            'description' => 'Desarrollar sistema de intercambio voluntario de turnos que proporcione flexibilidad respetando la organización del trabajo.',
                            'objetivo_relacionado' => 4
                        ]
                    ],
                    'mes_6' => [
                        [
                            'number' => 11,
                            'description' => 'Evaluar impacto mediante indicadores de cumplimiento de jornadas, fatiga percibida, calidad del sueño y satisfacción con horarios de trabajo.',
                            'objetivo_relacionado' => 1
                        ],
                        [
                            'number' => 12,
                            'description' => 'Ajustar sistemas de jornadas y turnos basándose en resultados y establecer monitoreo continuo de cumplimiento de políticas de descanso.',
                            'objetivo_relacionado' => 3
                        ]
                    ]
                ]),
                'bibliography' => json_encode([
                    'Folkard, S., & Tucker, P. (2003). Shift work, safety and productivity. Occupational Medicine, 53(2), 95-101.',
                    'Kecklund, G., & Axelsson, J. (2016). Health consequences of shift work and insufficient sleep. BMJ, 355, i5210.',
                    'Demerouti, E., Bakker, A. B., Geurts, S. A., & Taris, T. W. (2009). Daily recovery from work-related effort during non-work time. In S. Sonnentag, P. L. Perrewé, & D. C. Ganster (Eds.), Current perspectives on job-stress recovery (pp. 85-123). Bingley, UK: Emerald.',
                    'Ministerio de la Protección Social de Colombia (2010). Batería de instrumentos para la evaluación de factores de riesgo psicosocial. Bogotá: Ministerio de la Protección Social.',
                    'Organización Internacional del Trabajo (2004). Condiciones de trabajo, seguridad y salud. Ginebra: OIT.',
                    'Knauth, P., & Hornberger, S. (2003). Preventive and compensatory measures for shift workers. Occupational Medicine, 53(2), 109-116.',
                    'Bambra, C. L., Whitehead, M. M., Sowden, A. J., Akers, J., & Petticrew, M. P. (2008). Shifting schedules: The health effects of reorganizing shift work. American Journal of Preventive Medicine, 34(5), 427-434.'
                ]),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],

            // 6. Recompensas Derivadas de la Pertenencia a la Organización
            [
                'dimension_code' => 'recompensas_pertenencia_organizacion',
                'dimension_name' => 'Recompensas Derivadas de la Pertenencia a la Organización',
                'domain_code' => 'recompensas',
                'questionnaire_type' => 'intralaboral',
                'introduction' => 'Esta dimensión evalúa el sentimiento de orgullo y percepción de estabilidad laboral que experimenta un trabajador por estar vinculado a una organización, así como el sentimiento de autovaloración y de utilidad que experimenta por realizar su trabajo. Las recompensas de pertenencia organizacional incluyen el reconocimiento que hace la empresa de sus empleados y el orgullo que sienten estos por pertenecer a ella.

Las recompensas derivadas de la pertenencia organizacional se consideran deficientes (factor de riesgo) cuando:
• El trabajador no experimenta orgullo por pertenecer a la organización
• Existe percepción de inestabilidad laboral constante
• No se siente valorado ni reconocido su aporte
• La organización no cumple sus compromisos con los empleados
• No existe sentido de propósito o significado en el trabajo realizado

Para intervenir esta dimensión es fundamental fortalecer la cultura organizacional y los valores compartidos, mejorar la comunicación del valor estratégico de cada rol, garantizar estabilidad laboral dentro de lo posible, reconocer los aportes individuales y colectivos, y generar sentido de pertenencia e identidad organizacional.',
                'objectives' => json_encode([
                    [
                        'number' => 1,
                        'description' => 'Fortalecer el orgullo de pertenencia mediante la consolidación de una cultura organizacional positiva y valores compartidos.'
                    ],
                    [
                        'number' => 2,
                        'description' => 'Mejorar la percepción de estabilidad laboral a través de políticas claras de vinculación, desarrollo de carrera y comunicación transparente.'
                    ],
                    [
                        'number' => 3,
                        'description' => 'Desarrollar sistemas de reconocimiento que valoren el aporte de cada trabajador al logro de los objetivos organizacionales.'
                    ],
                    [
                        'number' => 4,
                        'description' => 'Fortalecer el sentido de propósito comunicando el impacto y significado del trabajo de cada rol en el contexto organizacional y social.'
                    ],
                    [
                        'number' => 5,
                        'description' => 'Generar espacios de participación y pertenencia que fortalezcan la identidad organizacional y el compromiso de los trabajadores.'
                    ]
                ]),
                'activities_6months' => json_encode([
                    'mes_1' => [
                        [
                            'number' => 1,
                            'description' => 'Realizar encuesta de clima organizacional y sentido de pertenencia para identificar brechas entre la identidad deseada y la percibida.',
                            'objetivo_relacionado' => 1
                        ],
                        [
                            'number' => 2,
                            'description' => 'Evaluar percepciones de estabilidad laboral mediante grupos focales que exploren temores, expectativas y experiencias de los trabajadores.',
                            'objetivo_relacionado' => 2
                        ]
                    ],
                    'mes_2' => [
                        [
                            'number' => 3,
                            'description' => 'Diseñar o actualizar el sistema de valores organizacionales mediante proceso participativo que involucre diferentes niveles y áreas.',
                            'objetivo_relacionado' => 1
                        ],
                        [
                            'number' => 4,
                            'description' => 'Desarrollar política de comunicación transparente sobre situación organizacional, planes futuros y seguridad laboral para reducir incertidumbre.',
                            'objetivo_relacionado' => 2
                        ]
                    ],
                    'mes_3' => [
                        [
                            'number' => 5,
                            'description' => 'Implementar programa de reconocimiento formal e informal que celebre aportes individuales y de equipo de manera frecuente y significativa.',
                            'objetivo_relacionado' => 3
                        ],
                        [
                            'number' => 6,
                            'description' => 'Crear narrativas de propósito organizacional que conecten el trabajo diario de cada rol con el impacto social y los resultados organizacionales.',
                            'objetivo_relacionado' => 4
                        ]
                    ],
                    'mes_4' => [
                        [
                            'number' => 7,
                            'description' => 'Desarrollar programa de embajadores de marca empleadora donde trabajadores compartan con orgullo su experiencia laboral interna y externamente.',
                            'objetivo_relacionado' => 1
                        ],
                        [
                            'number' => 8,
                            'description' => 'Establecer planes de desarrollo de carrera claros y accesibles que demuestren compromiso organizacional con el crecimiento de sus empleados.',
                            'objetivo_relacionado' => 2
                        ]
                    ],
                    'mes_5' => [
                        [
                            'number' => 9,
                            'description' => 'Crear espacios de participación (comités, grupos de mejora, proyectos colaborativos) que fortalezcan el sentido de pertenencia activa.',
                            'objetivo_relacionado' => 5
                        ],
                        [
                            'number' => 10,
                            'description' => 'Implementar programa de testimonios internos donde trabajadores compartan historias de impacto positivo de su trabajo en clientes o comunidad.',
                            'objetivo_relacionado' => 4
                        ]
                    ],
                    'mes_6' => [
                        [
                            'number' => 11,
                            'description' => 'Evaluar cambios en indicadores de orgullo organizacional, percepción de estabilidad, reconocimiento percibido y compromiso afectivo.',
                            'objetivo_relacionado' => 3
                        ],
                        [
                            'number' => 12,
                            'description' => 'Ajustar estrategias de fortalecimiento de pertenencia basándose en resultados y establecer plan de sostenibilidad de cultura organizacional.',
                            'objetivo_relacionado' => 1
                        ]
                    ]
                ]),
                'bibliography' => json_encode([
                    'Meyer, J. P., & Allen, N. J. (1991). A three-component conceptualization of organizational commitment. Human Resource Management Review, 1(1), 61-89.',
                    'Ashforth, B. E., Harrison, S. H., & Corley, K. G. (2008). Identification in organizations: An examination of four fundamental questions. Journal of Management, 34(3), 325-374.',
                    'Pratt, M. G., Rockmann, K. W., & Kaufmann, J. B. (2006). Constructing professional identity: The role of work and identity learning cycles in the customization of identity among medical residents. Academy of Management Journal, 49(2), 235-262.',
                    'Ministerio de la Protección Social de Colombia (2010). Batería de instrumentos para la evaluación de factores de riesgo psicosocial. Bogotá: Ministerio de la Protección Social.',
                    'Dutton, J. E., Dukerich, J. M., & Harquail, C. V. (1994). Organizational images and member identification. Administrative Science Quarterly, 39(2), 239-263.',
                    'Steger, M. F., Dik, B. J., & Duffy, R. D. (2012). Measuring meaningful work: The Work and Meaning Inventory (WAMI). Journal of Career Assessment, 20(3), 322-337.'
                ]),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],

            // 7. Reconocimiento y Compensación
            [
                'dimension_code' => 'reconocimiento_compensacion',
                'dimension_name' => 'Reconocimiento y Compensación',
                'domain_code' => 'recompensas',
                'questionnaire_type' => 'intralaboral',
                'introduction' => 'Esta dimensión evalúa el conjunto de retribuciones que la organización le otorga al trabajador en contraprestación al esfuerzo realizado en el desempeño de su labor. Incluye tanto la remuneración económica como las diferentes formas de reconocimiento no monetario por el trabajo realizado, las oportunidades de desarrollo, la promoción laboral y el reconocimiento social del trabajo desempeñado.

El reconocimiento y compensación se consideran deficientes (factor de riesgo) cuando:
• La compensación económica no es justa en relación con el esfuerzo y responsabilidades
• No existe reconocimiento por el buen desempeño o los logros alcanzados
• Las oportunidades de desarrollo y crecimiento son limitadas o inexistentes
• Los procesos de promoción no son transparentes ni equitativos
• El trabajo realizado no recibe el reconocimiento social que merece

Para intervenir esta dimensión es fundamental establecer sistemas de compensación equitativos y competitivos, implementar programas de reconocimiento efectivos, crear oportunidades claras de desarrollo profesional, garantizar transparencia en los procesos de promoción y valorar públicamente el trabajo bien realizado.',
                'objectives' => json_encode([
                    [
                        'number' => 1,
                        'description' => 'Establecer sistemas de compensación económica equitativos, competitivos y alineados con el mercado y la contribución individual.'
                    ],
                    [
                        'number' => 2,
                        'description' => 'Implementar programas de reconocimiento efectivos que valoren el desempeño destacado y los logros significativos de manera oportuna y significativa.'
                    ],
                    [
                        'number' => 3,
                        'description' => 'Crear oportunidades claras y accesibles de desarrollo profesional mediante capacitación, formación y planes de carrera.'
                    ],
                    [
                        'number' => 4,
                        'description' => 'Garantizar transparencia y equidad en los procesos de promoción y ascenso basados en criterios objetivos de desempeño y competencias.'
                    ],
                    [
                        'number' => 5,
                        'description' => 'Fortalecer el reconocimiento social del trabajo mediante la visibilización de los aportes de cada rol a los resultados organizacionales.'
                    ]
                ]),
                'activities_6months' => json_encode([
                    'mes_1' => [
                        [
                            'number' => 1,
                            'description' => 'Realizar estudio de equidad interna y competitividad externa de compensaciones comparando con mercado laboral y analizando brechas salariales.',
                            'objetivo_relacionado' => 1
                        ],
                        [
                            'number' => 2,
                            'description' => 'Evaluar percepción de reconocimiento mediante encuestas y entrevistas que identifiquen brechas entre esfuerzo realizado y reconocimiento recibido.',
                            'objetivo_relacionado' => 2
                        ]
                    ],
                    'mes_2' => [
                        [
                            'number' => 3,
                            'description' => 'Diseñar o actualizar estructura salarial basada en valoración de cargos, desempeño y competencias que garantice equidad interna.',
                            'objetivo_relacionado' => 1
                        ],
                        [
                            'number' => 4,
                            'description' => 'Desarrollar programa integral de reconocimiento que incluya reconocimiento formal, informal, monetario y no monetario de manera sistemática.',
                            'objetivo_relacionado' => 2
                        ]
                    ],
                    'mes_3' => [
                        [
                            'number' => 5,
                            'description' => 'Implementar plan individual de desarrollo (PID) para cada trabajador que identifique brechas de competencias y oportunidades de crecimiento.',
                            'objetivo_relacionado' => 3
                        ],
                        [
                            'number' => 6,
                            'description' => 'Establecer política de promociones transparente con criterios claros, procesos de selección objetivos y comunicación abierta de oportunidades.',
                            'objetivo_relacionado' => 4
                        ]
                    ],
                    'mes_4' => [
                        [
                            'number' => 7,
                            'description' => 'Capacitar a líderes en técnicas de reconocimiento efectivo y retroalimentación positiva que fortalezcan la motivación de sus equipos.',
                            'objetivo_relacionado' => 2
                        ],
                        [
                            'number' => 8,
                            'description' => 'Crear programa de formación continua (cursos, certificaciones, estudios) financiado por la organización según planes de desarrollo.',
                            'objetivo_relacionado' => 3
                        ]
                    ],
                    'mes_5' => [
                        [
                            'number' => 9,
                            'description' => 'Implementar sistema de visibilización de logros mediante comunicación interna que celebre éxitos individuales y de equipo regularmente.',
                            'objetivo_relacionado' => 5
                        ],
                        [
                            'number' => 10,
                            'description' => 'Desarrollar programa de beneficios complementarios (salud, bienestar, flexibilidad) que enriquezcan el paquete de compensación total.',
                            'objetivo_relacionado' => 1
                        ]
                    ],
                    'mes_6' => [
                        [
                            'number' => 11,
                            'description' => 'Evaluar impacto mediante indicadores de satisfacción con compensación, reconocimiento percibido, acceso a desarrollo y equidad de promociones.',
                            'objetivo_relacionado' => 4
                        ],
                        [
                            'number' => 12,
                            'description' => 'Ajustar sistemas de compensación y reconocimiento basándose en resultados y establecer revisión anual de competitividad y equidad.',
                            'objetivo_relacionado' => 1
                        ]
                    ]
                ]),
                'bibliography' => json_encode([
                    'Rynes, S. L., Gerhart, B., & Minette, K. A. (2004). The importance of pay in employee motivation: Discrepancies between what people say and what they do. Human Resource Management, 43(4), 381-394.',
                    'Stajkovic, A. D., & Luthans, F. (2003). Behavioral management and task performance in organizations: Conceptual background, meta-analysis, and test of alternative models. Personnel Psychology, 56(1), 155-194.',
                    'Milkovich, G. T., Newman, J. M., & Gerhart, B. (2013). Compensation (11th ed.). New York: McGraw-Hill.',
                    'Ministerio de la Protección Social de Colombia (2010). Batería de instrumentos para la evaluación de factores de riesgo psicosocial. Bogotá: Ministerio de la Protección Social.',
                    'Becker, B. E., & Huselid, M. A. (2006). Strategic human resources management: Where do we go from here? Journal of Management, 32(6), 898-925.',
                    'Gupta, N., & Shaw, J. D. (2014). Employee compensation: The neglected area of HRM research. Human Resource Management Review, 24(1), 1-4.',
                    'London, M., & Smither, J. W. (1999). Career-related continuous learning: Defining the construct and mapping the process. Research in Personnel and Human Resources Management, 17, 81-121.'
                ]),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        $this->db->table('action_plans')->insertBatch($data);
    }
}
