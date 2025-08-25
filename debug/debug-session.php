<?php
require_once __DIR__ . '/bootstrap.php';

$api = new AccurateAPI();

echo "<h1>Debug Session Endpoint</h1>";

// Test session endpoint
$sessionResponse = $api->testEndpoint(ACCURATE_API_HOST . '/accurate/api/session.do', 'GET');

echo "<h2>Session Response:</h2>";
echo "<pre>";
print_r($sessionResponse);
echo "</pre>";

echo "<h2>Session Info:</h2>";
$sessionInfo = $api->getSessionInfo();
echo "<pre>";
print_r($sessionInfo);
echo "</pre>";

echo "<h2>Config:</h2>";
echo "<pre>";
echo "HOST: " . ACCURATE_API_HOST . "\n";
echo "TOKEN: " . substr(ACCURATE_ACCESS_TOKEN, 0, 20) . "...\n";
echo "SESSION_ID: " . substr(ACCURATE_SESSION_ID, 0, 20) . "...\n";
echo "DATABASE_ID: " . ACCURATE_DATABASE_ID . "\n";
echo "</pre>";
?>
