<?php
require_once 'bootstrap.php';
require_once 'classes/AccurateAPI.php';

echo "<h2>Quick Token Debug</h2>";

$api = new AccurateAPI();

echo "<h3>1. Config Values Check</h3>";
echo "ACCESS_TOKEN: " . (defined('ACCURATE_ACCESS_TOKEN') ? substr(ACCURATE_ACCESS_TOKEN, 0, 20) . '...' : 'NOT SET') . "<br>";
echo "SESSION_ID: " . (defined('ACCURATE_SESSION_ID') ? substr(ACCURATE_SESSION_ID, 0, 20) . '...' : 'NOT SET') . "<br>";
echo "DATABASE_ID: " . (defined('ACCURATE_DATABASE_ID') ? ACCURATE_DATABASE_ID : 'NOT SET') . "<br>";
echo "TOKEN_SCOPE: " . (defined('ACCURATE_TOKEN_SCOPE') ? ACCURATE_TOKEN_SCOPE : 'NOT SET') . "<br>";

echo "<hr>";

echo "<h3>2. Basic API Test</h3>";
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => 'https://public-api.accurate.id/accurate/api/session.do',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . ACCURATE_ACCESS_TOKEN
    ],
    CURLOPT_TIMEOUT => 5
]);

$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

echo "Session API HTTP Code: " . $httpCode . "<br>";
if ($httpCode == 200) {
    echo "✅ Session API working<br>";
} else {
    echo "❌ Session API failed<br>";
    echo "Response: " . htmlspecialchars(substr($response, 0, 200)) . "<br>";
}

echo "<hr>";

echo "<h3>3. getApprovedScopes() Test</h3>";
try {
    $scopes = $api->getApprovedScopes();
    echo "Result type: " . gettype($scopes) . "<br>";
    echo "Success: " . (isset($scopes['success']) ? ($scopes['success'] ? 'YES' : 'NO') : 'N/A') . "<br>";
    
    if (isset($scopes['data'])) {
        echo "Data type: " . gettype($scopes['data']) . "<br>";
        if (is_array($scopes['data'])) {
            echo "Data count: " . count($scopes['data']) . "<br>";
            echo "Data: ";
            var_dump($scopes['data']);
        }
    }
    
    echo "<h4>Full Response:</h4>";
    echo "<pre>";
    var_dump($scopes);
    echo "</pre>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<hr>";

echo "<h3>4. checkTokenStatus() Test</h3>";
try {
    $status = $api->checkTokenStatus(['item_view']);
    echo "Valid: " . ($status['valid'] ? 'YES' : 'NO') . "<br>";
    echo "Scopes tested: " . count($status['scopes']) . "<br>";
    
    echo "<h4>Scope Results:</h4>";
    foreach ($status['scopes'] as $scope => $available) {
        echo ($available ? '✅' : '❌') . " $scope<br>";
    }
    
    echo "<h4>Token Scopes:</h4>";
    if (isset($status['token_scopes'])) {
        echo "Type: " . gettype($status['token_scopes']) . "<br>";
        if (is_array($status['token_scopes'])) {
            echo "Count: " . count($status['token_scopes']) . "<br>";
            foreach ($status['token_scopes'] as $scope) {
                echo "- " . $scope . "<br>";
            }
        } else {
            echo "Value: " . $status['token_scopes'] . "<br>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<hr>";

echo "<h3>5. Manual Item API Test</h3>";
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => 'https://public-api.accurate.id/accurate/api/item/list.do',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . ACCURATE_ACCESS_TOKEN,
        'X-Session-ID: ' . ACCURATE_SESSION_ID,
        'X-Database-ID: ' . ACCURATE_DATABASE_ID
    ],
    CURLOPT_TIMEOUT => 5
]);

$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

echo "Item API HTTP Code: " . $httpCode . "<br>";
if ($httpCode == 200) {
    echo "✅ Item API working<br>";
} else {
    echo "❌ Item API failed<br>";
    echo "Response: " . htmlspecialchars(substr($response, 0, 200)) . "<br>";
}
?>
