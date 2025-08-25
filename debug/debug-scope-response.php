<?php
require_once 'bootstrap.php';
require_once 'classes/AccurateAPI.php';

// Buat instance API
$api = new AccurateAPI();

// Test getApprovedScopes dan lihat raw response
echo "<h2>Debug Approved Scopes Response</h2>";

$scopes = $api->getApprovedScopes();

echo "<h3>Returned Scopes:</h3>";
echo "<pre>";
var_dump($scopes);
echo "</pre>";

echo "<h3>Type Check:</h3>";
echo "Is Array: " . (is_array($scopes) ? 'YES' : 'NO') . "<br>";
if (is_array($scopes)) {
    echo "Count: " . count($scopes) . "<br>";
    echo "Keys: ";
    var_dump(array_keys($scopes));
    echo "<br>";
    
    echo "<h4>Individual Elements:</h4>";
    foreach ($scopes as $key => $value) {
        echo "Key: $key, Value: ";
        var_dump($value);
        echo " (Type: " . gettype($value) . ")<br>";
    }
}

echo "<hr>";

// Test raw API call untuk melihat response asli
echo "<h3>Raw API Response Test:</h3>";
// Gunakan token dari constants yang sudah ada
$token = ACCURATE_ACCESS_TOKEN;
if ($token) {
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://account.accurate.id/api/approved-scope.do',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $token
        ]
    ]);
    
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    
    echo "HTTP Code: $httpCode<br>";
    echo "Raw Response:<br>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
    
    if ($response) {
        $data = json_decode($response, true);
        echo "<h4>Decoded JSON:</h4>";
        echo "<pre>";
        var_dump($data);
        echo "</pre>";
    }
} else {
    echo "No token available";
}
?>
