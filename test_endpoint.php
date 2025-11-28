<?php
// Test endpoint response
require __DIR__ . '/vendor/autoload.php';

// Simular variables de entorno
putenv('DEBUG_SAVE_VERIFICATION=true');

echo "Testing endpoint response...\n\n";

$url = 'http://localhost/psyrisk/assessment/save-field-general-data';
$data = [
    'field_name' => 'gender',
    'field_value' => 'Masculino'
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'X-Requested-With: XMLHttpRequest',
    'Cookie: ' . $_COOKIE['ci_session'] ?? ''
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response:\n";
echo $response . "\n";

$json = json_decode($response, true);
if ($json) {
    echo "\nParsed JSON:\n";
    print_r($json);

    if (isset($json['debug_enabled'])) {
        echo "\n✅ debug_enabled present: " . var_export($json['debug_enabled'], true) . "\n";
    } else {
        echo "\n❌ debug_enabled NOT present\n";
    }

    if (isset($json['debug_verification'])) {
        echo "✅ debug_verification present\n";
        print_r($json['debug_verification']);
    } else {
        echo "❌ debug_verification NOT present\n";
    }
}
