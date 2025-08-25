<?php
require_once __DIR__ . '/bootstrap.php';

echo "<h1>Debug Token & Session Status</h1>";

echo "<h2>Current Configuration:</h2>";
echo "<pre>";
echo "ACCESS_TOKEN: " . substr(ACCURATE_ACCESS_TOKEN, 0, 20) . "...\n";
echo "REFRESH_TOKEN: " . substr(ACCURATE_REFRESH_TOKEN, 0, 20) . "...\n";
echo "SESSION_ID: " . substr(ACCURATE_SESSION_ID, 0, 20) . "...\n";
echo "DATABASE_ID: " . ACCURATE_DATABASE_ID . "\n";
echo "API_HOST: " . ACCURATE_API_HOST . "\n";
echo "AUTH_HOST: " . ACCURATE_AUTH_HOST . "\n";
echo "</pre>";

$api = new AccurateAPI();

// Test 1: Database list (tidak perlu session)
echo "<h2>Test 1: Database List</h2>";
$dbList = $api->getDatabaseList();
echo "<p>Success: " . ($dbList['success'] ? 'YES' : 'NO') . "</p>";
echo "<p>HTTP Code: " . $dbList['http_code'] . "</p>";
if (!$dbList['success']) {
    echo "<p>Error: " . ($dbList['error'] ?? 'Unknown') . "</p>";
    echo "<pre>Raw response: " . ($dbList['raw_response'] ?? 'No response') . "</pre>";
}

// Test 2: Open database
echo "<h2>Test 2: Open Database</h2>";
$openDb = $api->openDatabase(ACCURATE_DATABASE_ID);
echo "<p>Success: " . ($openDb['success'] ? 'YES' : 'NO') . "</p>";
echo "<p>HTTP Code: " . $openDb['http_code'] . "</p>";
if (!$openDb['success']) {
    echo "<p>Error: " . ($openDb['error'] ?? 'Unknown') . "</p>";
    echo "<pre>Raw response: " . ($openDb['raw_response'] ?? 'No response') . "</pre>";
} else {
    echo "<pre>Response data: " . print_r($openDb['data'], true) . "</pre>";
}

// Test 3: Item list (perlu session)
echo "<h2>Test 3: Item List</h2>";
$itemList = $api->getItemList(5, 1);
echo "<p>Success: " . ($itemList['success'] ? 'YES' : 'NO') . "</p>";
echo "<p>HTTP Code: " . $itemList['http_code'] . "</p>";
if (!$itemList['success']) {
    echo "<p>Error: " . ($itemList['error'] ?? 'Unknown') . "</p>";
    echo "<pre>Raw response: " . ($itemList['raw_response'] ?? 'No response') . "</pre>";
}

// Test 4: Branch list (perlu session)
echo "<h2>Test 4: Branch List</h2>";
$branchList = $api->getBranchList();
echo "<p>Success: " . ($branchList['success'] ? 'YES' : 'NO') . "</p>";
echo "<p>HTTP Code: " . $branchList['http_code'] . "</p>";
if (!$branchList['success']) {
    echo "<p>Error: " . ($branchList['error'] ?? 'Unknown') . "</p>";
    echo "<pre>Raw response: " . ($branchList['raw_response'] ?? 'No response') . "</pre>";
}
?>
