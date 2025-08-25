<?php
require_once __DIR__ . '/bootstrap.php';

echo "<h1>Debug Branch API</h1>";

// Test direct cURL
$url = ACCURATE_API_HOST . '/accurate/api/branch/list.do';

echo "<h2>Direct cURL Test</h2>";
echo "<p><strong>URL:</strong> $url</p>";
echo "<p><strong>Host:</strong> " . ACCURATE_API_HOST . "</p>";
echo "<p><strong>Access Token:</strong> " . ACCURATE_ACCESS_TOKEN . "</p>";
echo "<p><strong>Session ID:</strong> " . ACCURATE_SESSION_ID . "</p>";

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer " . ACCURATE_ACCESS_TOKEN,
        "X-Session-ID: " . ACCURATE_SESSION_ID,
        "Accept: application/json"
    ]
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "<p><strong>HTTP Code:</strong> $httpCode</p>";
if ($error) {
    echo "<p><strong>cURL Error:</strong> $error</p>";
}
echo "<p><strong>Response:</strong></p>";
echo "<pre>$response</pre>";

echo "<h2>Using AccurateAPI Class</h2>";
$api = new AccurateAPI();
$result = $api->getBranchList();
echo "<p><strong>Result:</strong></p>";
echo "<pre>" . json_encode($result, JSON_PRETTY_PRINT) . "</pre>";
?>
