<?php
require_once __DIR__ . '/../bootstrap.php';

$api = new AccurateAPI();

echo "<h1>Debug Token Status</h1>";

// Test manual each scope
echo "<h2>Manual Scope Testing:</h2>";

// Test item_view
echo "<h3>Testing item_view scope:</h3>";
$itemResponse = $api->testEndpoint(ACCURATE_API_HOST . '/accurate/api/item/list.do', 'GET');
echo "<p>Success: " . ($itemResponse['success'] ? 'YES' : 'NO') . "</p>";
echo "<p>HTTP Code: " . $itemResponse['http_code'] . "</p>";
if (!$itemResponse['success']) {
    echo "<p>Error: " . ($itemResponse['error'] ?? 'No error message') . "</p>";
}
echo "<hr>";

// Test branch_view
echo "<h3>Testing branch_view scope:</h3>";
$branchResponse = $api->testEndpoint(ACCURATE_API_HOST . '/accurate/api/branch/list.do', 'GET');
echo "<p>Success: " . ($branchResponse['success'] ? 'YES' : 'NO') . "</p>";
echo "<p>HTTP Code: " . $branchResponse['http_code'] . "</p>";
if (!$branchResponse['success']) {
    echo "<p>Error: " . ($branchResponse['error'] ?? 'No error message') . "</p>";
}
echo "<hr>";

// Test checkTokenStatus method
echo "<h2>checkTokenStatus() Result:</h2>";
$tokenStatus = $api->checkTokenStatus(['item_view', 'branch_view']);
echo "<pre>";
print_r($tokenStatus);
echo "</pre>";
?>
