<?php
require_once __DIR__ . '/../bootstrap.php';

$api = new AccurateAPI();

echo "<h1>Debug Item View Scope</h1>";

// Test session terlebih dahulu
echo "<h2>1. Session Test:</h2>";
$sessionResponse = $api->testEndpoint(ACCURATE_API_HOST . '/accurate/api/session.do', 'GET');
echo "<p>Success: " . ($sessionResponse['success'] ? 'YES' : 'NO') . "</p>";
echo "<p>HTTP Code: " . $sessionResponse['http_code'] . "</p>";
if (!$sessionResponse['success']) {
    echo "<p>Error: " . ($sessionResponse['error'] ?? 'No error message') . "</p>";
    echo "<pre>Raw response: " . print_r($sessionResponse, true) . "</pre>";
}
echo "<hr>";

// Test item endpoint
echo "<h2>2. Item Endpoint Test:</h2>";
$itemResponse = $api->testEndpoint(ACCURATE_API_HOST . '/accurate/api/item/list.do', 'GET');
echo "<p>Success: " . ($itemResponse['success'] ? 'YES' : 'NO') . "</p>";
echo "<p>HTTP Code: " . $itemResponse['http_code'] . "</p>";
if (!$itemResponse['success']) {
    echo "<p>Error: " . ($itemResponse['error'] ?? 'No error message') . "</p>";
    echo "<pre>Raw response: " . print_r($itemResponse, true) . "</pre>";
} else {
    echo "<p>Data available: " . (isset($itemResponse['data']) ? 'YES' : 'NO') . "</p>";
    if (isset($itemResponse['data'])) {
        echo "<p>Data type: " . gettype($itemResponse['data']) . "</p>";
        if (is_array($itemResponse['data'])) {
            echo "<p>Data keys: " . implode(', ', array_keys($itemResponse['data'])) . "</p>";
        }
    }
}
echo "<hr>";

// Test getItemList method
echo "<h2>3. getItemList() Method Test:</h2>";
$itemListResponse = $api->getItemList(5, 1);
echo "<p>Success: " . ($itemListResponse['success'] ? 'YES' : 'NO') . "</p>";
echo "<p>HTTP Code: " . $itemListResponse['http_code'] . "</p>";
if (!$itemListResponse['success']) {
    echo "<p>Error: " . ($itemListResponse['error'] ?? 'No error message') . "</p>";
    echo "<pre>Raw response: " . print_r($itemListResponse, true) . "</pre>";
} else {
    echo "<p>Data available: " . (isset($itemListResponse['data']) ? 'YES' : 'NO') . "</p>";
}
echo "<hr>";

// Test dengan parameter yang berbeda
echo "<h2>4. Item Endpoint dengan Parameter Berbeda:</h2>";
$endpoints = [
    '/accurate/api/item/list.do?sp.pageSize=10&sp.page=1',
    '/accurate/api/item/list.do?limit=10',
    '/accurate/api/item/list.do'
];

foreach ($endpoints as $endpoint) {
    echo "<h3>Testing: $endpoint</h3>";
    $response = $api->testEndpoint(ACCURATE_API_HOST . $endpoint, 'GET');
    echo "<p>Success: " . ($response['success'] ? 'YES' : 'NO') . "</p>";
    echo "<p>HTTP Code: " . $response['http_code'] . "</p>";
    if (!$response['success']) {
        echo "<p>Error: " . ($response['error'] ?? 'No error message') . "</p>";
    }
    echo "<hr>";
}

echo "<h2>5. Current Config:</h2>";
echo "<pre>";
echo "HOST: " . ACCURATE_API_HOST . "\n";
echo "TOKEN: " . substr(ACCURATE_ACCESS_TOKEN, 0, 20) . "...\n";
echo "DATABASE_ID: " . ACCURATE_DATABASE_ID . "\n";
echo "</pre>";
?>
