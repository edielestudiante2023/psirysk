<?php
$db = new PDO('mysql:host=localhost;dbname=psyrisk', 'root', '');

echo "=== DIMENSIONES EN RIESGO MEDIO/ALTO/MUY ALTO ===\n\n";

$stmt = $db->query("
    SELECT element_code, element_name, element_type, worst_risk_level
    FROM max_risk_results
    WHERE battery_service_id = 9
    AND element_type = 'dimension'
    AND worst_risk_level IN ('riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto', 'medio', 'alto', 'muy_alto')
    ORDER BY worst_risk_level, element_code
");

$elementToPlanMapping = [
    // INTRALABORAL
    'dim_caracteristicas_liderazgo' => 'caracteristicas_liderazgo',
    'dim_relaciones_sociales' => 'relaciones_sociales_trabajo',
    'dim_retroalimentacion' => 'retroalimentacion_desempeno',
    'dim_relacion_colaboradores' => 'relacion_colaboradores',
    'dim_claridad_rol' => 'claridad_rol',
    'dim_capacitacion' => 'capacitacion',
    'dim_participacion_manejo_cambio' => 'participacion_manejo_cambio',
    'dim_oportunidades_desarrollo' => 'oportunidades_desarrollo_habilidades',
    'dim_control_autonomia' => 'control_autonomia_trabajo',
    'dim_demandas_ambientales' => 'demandas_ambientales_esfuerzo_fisico',
    'dim_demandas_emocionales' => 'demandas_emocionales',
    'dim_demandas_cuantitativas' => 'demandas_cuantitativas',
    'dim_influencia_trabajo_entorno' => 'influencia_trabajo_entorno_extralaboral',
    'dim_demandas_responsabilidad' => 'exigencias_responsabilidad_cargo',
    'dim_carga_mental' => 'demandas_carga_mental',
    'dim_consistencia_rol' => 'consistencia_rol',
    'dim_demandas_jornada' => 'demandas_jornada_trabajo',
    'dim_recompensas_pertenencia' => 'recompensas_pertenencia_organizacion',
    'dim_reconocimiento_compensacion' => 'reconocimiento_compensacion',

    // EXTRALABORAL
    'dim_tiempo_fuera' => 'tiempo_fuera_trabajo',
    'dim_relaciones_familiares_extra' => 'relaciones_familiares',
    'dim_comunicacion' => 'comunicacion_relaciones_interpersonales',
    'dim_situacion_economica' => 'situacion_economica_familiar',
    'dim_caracteristicas_vivienda' => 'caracteristicas_vivienda_entorno',
    'dim_influencia_entorno_extra' => 'influencia_entorno_extralaboral',
    'dim_desplazamiento' => 'desplazamiento_vivienda_trabajo',
];

// Get all action plans
$plansStmt = $db->query("SELECT dimension_code FROM action_plans");
$actionPlans = [];
while ($row = $plansStmt->fetch(PDO::FETCH_ASSOC)) {
    $actionPlans[$row['dimension_code']] = true;
}

$count = 0;
$withPlan = 0;
$withoutPlan = 0;

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $count++;
    $elementCode = $row['element_code'];
    $planCode = $elementToPlanMapping[$elementCode] ?? null;
    $hasPlan = $planCode && isset($actionPlans[$planCode]) ? 'YES' : 'NO';

    if ($hasPlan === 'YES') {
        $withPlan++;
    } else {
        $withoutPlan++;
    }

    printf("%2d. %-50s | %-15s | Plan: %-3s | Maps to: %s\n",
        $count,
        $row['element_name'],
        $row['worst_risk_level'],
        $hasPlan,
        $planCode ?? 'NOT MAPPED'
    );
}

echo "\n=== RESUMEN ===\n";
echo "Total dimensiones en riesgo: $count\n";
echo "Con plan de acción: $withPlan\n";
echo "Sin plan de acción: $withoutPlan\n";
