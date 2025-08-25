<?php
require_once '../bootstrap.php';
require_once '../classes/AccurateAPI.php';

// Buat instance API
$api = new AccurateAPI();

echo "<h2>Token Diagnostic Tool</h2>";

// 1. Cek apakah token exist
echo "<h3>1. Token Configuration Check</h3>";
echo "ACCURATE_ACCESS_TOKEN: " . (defined('ACCURATE_ACCESS_TOKEN') && !empty(ACCURATE_ACCESS_TOKEN) ? '✅ Configured' : '❌ Missing') . "<br>";
echo "ACCURATE_SESSION_ID: " . (defined('ACCURATE_SESSION_ID') && !empty(ACCURATE_SESSION_ID) ? '✅ Configured' : '❌ Missing') . "<br>";
echo "ACCURATE_DATABASE_ID: " . (defined('ACCURATE_DATABASE_ID') && !empty(ACCURATE_DATABASE_ID) ? '✅ Configured' : '❌ Missing') . "<br>";

echo "<hr>";

// 2. Test basic API connectivity
echo "<h3>2. Basic API Connectivity Test</h3>";
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => 'https://public-api.accurate.id/accurate/api/session.do',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . ACCURATE_ACCESS_TOKEN
    ],
    CURLOPT_TIMEOUT => 10
]);

$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
$curlError = curl_error($curl);
curl_close($curl);

echo "HTTP Code: " . $httpCode . "<br>";
if ($curlError) {
    echo "cURL Error: " . $curlError . "<br>";
}
echo "Response: " . htmlspecialchars(substr($response, 0, 200)) . "...<br>";

echo "<hr>";

// 3. Test approved-scope API specifically
echo "<h3>3. Approved Scope API Test</h3>";
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => 'https://account.accurate.id/api/approved-scope.do',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . ACCURATE_ACCESS_TOKEN
    ],
    CURLOPT_TIMEOUT => 10
]);

$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
$curlError = curl_error($curl);
curl_close($curl);

echo "HTTP Code: " . $httpCode . "<br>";
if ($curlError) {
    echo "cURL Error: " . $curlError . "<br>";
}

if ($response) {
    echo "Raw Response: <br><pre>" . htmlspecialchars($response) . "</pre>";
    
    $data = json_decode($response, true);
    if ($data) {
        echo "Parsed Data: <br><pre>";
        var_dump($data);
        echo "</pre>";
    } else {
        echo "JSON parsing failed<br>";
    }
} else {
    echo "No response received<br>";
}

echo "<hr>";

// 4. Test dengan scope yang lebih simple
echo "<h3>4. Simple Scope Check</h3>";
$requiredScopes = ['item_view']; // Test dengan satu scope saja
$tokenStatus = $api->checkTokenStatus($requiredScopes);
echo "<pre>";
var_dump($tokenStatus);
echo "</pre>";

// 5. Manual test each scope API endpoint
echo "<hr>";
echo "<h3>5. Manual API Endpoint Tests</h3>";

$endpoints = [
    'item_view' => 'https://public-api.accurate.id/accurate/api/item/list.do',
    'branch_view' => 'https://public-api.accurate.id/accurate/api/branch/list.do',
    'vendor_view' => 'https://public-api.accurate.id/accurate/api/vendor/list.do',
    'warehouse_view' => 'https://public-api.accurate.id/accurate/api/warehouse/list.do'
];

foreach ($endpoints as $scope => $endpoint) {
    echo "<h4>Testing {$scope}:</h4>";
    
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $endpoint,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . ACCURATE_ACCESS_TOKEN,
            'X-Session-ID: ' . ACCURATE_SESSION_ID,
            'X-Database-ID: ' . ACCURATE_DATABASE_ID
        ],
        CURLOPT_TIMEOUT => 10
    ]);
    
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $curlError = curl_error($curl);
    curl_close($curl);
    
    echo "HTTP Code: " . $httpCode;
    if ($httpCode == 200) {
        echo " ✅ PASS";
    } else {
        echo " ❌ FAIL";
    }
    echo "<br>";
    
    if ($curlError) {
        echo "cURL Error: " . $curlError . "<br>";
    }
    
    if ($response && strlen($response) < 500) {
        echo "Response: " . htmlspecialchars($response) . "<br>";
    }
    echo "<br>";
}
?>
