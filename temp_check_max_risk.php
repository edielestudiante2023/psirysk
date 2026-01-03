<?php
$db = new PDO('mysql:host=localhost;dbname=psyrisk', 'root', '');
$stmt = $db->query('SELECT element_code, element_type, element_name, worst_risk_level FROM max_risk_results WHERE battery_service_id = 9 ORDER BY element_type, element_code');

echo "Element Type | Element Code | Element Name | Worst Risk Level\n";
echo str_repeat("-", 120) . "\n";

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo str_pad($row['element_type'], 12) . " | " .
         str_pad($row['element_code'], 50) . " | " .
         str_pad($row['element_name'], 40) . " | " .
         $row['worst_risk_level'] . "\n";
}
