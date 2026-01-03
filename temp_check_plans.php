<?php
$db = new PDO('mysql:host=localhost;dbname=psyrisk', 'root', '');
$stmt = $db->query('SELECT dimension_code FROM action_plans ORDER BY dimension_code');

echo "Available Action Plans:\n";
echo str_repeat("-", 60) . "\n";

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['dimension_code'] . "\n";
}
