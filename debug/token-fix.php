<?php
require_once '../bootstrap.php';
require_once '../classes/AccurateAPI.php';

echo "<h2>Token Fix Tool</h2>";

$api = new AccurateAPI();

echo "<h3>Current Status Check</h3>";
$currentStatus = $api->checkTokenStatus(['item_view', 'branch_view', 'vendor_view']);
echo "Token Valid: " . ($currentStatus['valid'] ? '✅ YES' : '❌ NO') . "<br>";
echo "Available Scopes: " . count(array_filter($currentStatus['scopes'])) . " out of " . count($currentStatus['scopes']) . "<br>";

echo "<h4>Scope Details:</h4>";
foreach ($currentStatus['scopes'] as $scope => $available) {
    echo ($available ? '✅' : '❌') . " {$scope}<br>";
}

echo "<hr>";

echo "<h3>Suggested Actions</h3>";

// Check if we need to renew authorization
$needsAuth = false;
$availableCount = count(array_filter($currentStatus['scopes']));
$totalCount = count($currentStatus['scopes']);

if ($availableCount == 0) {
    echo "🔄 <strong>Token completely invalid</strong> - Need full re-authorization<br>";
    $needsAuth = true;
} else if ($availableCount < $totalCount) {
    echo "⚠️ <strong>Partial scope access</strong> - Some scopes missing<br>";
} else {
    echo "✅ <strong>All scopes working</strong> - No action needed<br>";
}

if ($needsAuth) {
    echo "<div class='mt-4 p-4 bg-yellow-100 border border-yellow-300 rounded'>";
    echo "<h4>Re-authorization Required</h4>";
    echo "<p>Your token appears to be invalid. Please re-authorize:</p>";
    echo "<a href='authorize.php' class='inline-block mt-2 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600'>Authorize Now</a>";
    echo "</div>";
}

echo "<hr>";

echo "<h3>Quick Fix Attempts</h3>";

// Try to refresh session if possible
echo "<h4>1. Session Refresh Test</h4>";
try {
    $sessionInfo = $api->getSessionInfo();
    if ($sessionInfo && isset($sessionInfo['session_id'])) {
        echo "✅ Session ID available: " . substr($sessionInfo['session_id'], 0, 20) . "...<br>";
    } else {
        echo "❌ No valid session found<br>";
    }
} catch (Exception $e) {
    echo "❌ Session check failed: " . $e->getMessage() . "<br>";
}

echo "<h4>2. Individual API Tests</h4>";

$testEndpoints = [
    'item_view' => [
        'url' => 'https://public-api.accurate.id/accurate/api/item/list.do',
        'name' => 'Item List'
    ],
    'branch_view' => [
        'url' => 'https://public-api.accurate.id/accurate/api/branch/list.do', 
        'name' => 'Branch List'
    ],
    'vendor_view' => [
        'url' => 'https://public-api.accurate.id/accurate/api/vendor/list.do',
        'name' => 'Vendor List'  
    ]
];

foreach ($testEndpoints as $scope => $info) {
    echo "<strong>{$info['name']} ({$scope}):</strong> ";
    
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $info['url'],
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
    
    if ($httpCode == 200) {
        echo "✅ Working (HTTP $httpCode)<br>";
    } else {
        echo "❌ Failed (HTTP $httpCode)<br>";
        if ($response) {
            $data = json_decode($response, true);
            if (isset($data['message'])) {
                echo "&nbsp;&nbsp;&nbsp;&nbsp;Error: " . $data['message'] . "<br>";
            }
        }
    }
}

echo "<hr>";

echo "<h3>Actions</h3>";
echo "<a href='token-status.php' class='inline-block px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 mr-2'>Check Status Again</a>";
echo "<a href='authorize.php' class='inline-block px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 mr-2'>Re-authorize</a>";
echo "<a href='../index.php' class='inline-block px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600'>Back to Dashboard</a>";

?>

<style>
body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
h2, h3, h4 { color: #333; }
.mt-4 { margin-top: 1rem; }
.p-4 { padding: 1rem; }
.bg-yellow-100 { background-color: #fef3c7; }
.border { border-width: 1px; }
.border-yellow-300 { border-color: #fcd34d; }
.rounded { border-radius: 0.375rem; }
.inline-block { display: inline-block; }
.px-4 { padding-left: 1rem; padding-right: 1rem; }
.py-2 { padding-top: 0.5rem; padding-bottom: 0.5rem; }
.mr-2 { margin-right: 0.5rem; }
.bg-blue-500 { background-color: #3b82f6; }
.bg-green-500 { background-color: #10b981; }
.bg-gray-500 { background-color: #6b7280; }
.text-white { color: white; }
.hover\:bg-blue-600:hover { background-color: #2563eb; }
.hover\:bg-green-600:hover { background-color: #059669; }
.hover\:bg-gray-600:hover { background-color: #4b5563; }
a { text-decoration: none; }
</style>
