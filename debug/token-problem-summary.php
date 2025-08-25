<?php
require_once 'bootstrap.php';
require_once 'classes/AccurateAPI.php';

echo "<h2>Token Problem Summary & Solutions</h2>";

$api = new AccurateAPI();

echo "<div style='border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
echo "<h3>🔍 Current Problem Analysis</h3>";

// 1. Check basic config
echo "<h4>1. Configuration Check</h4>";
$hasToken = defined('ACCURATE_ACCESS_TOKEN') && !empty(ACCURATE_ACCESS_TOKEN);
$hasRefresh = defined('ACCURATE_REFRESH_TOKEN') && !empty(ACCURATE_REFRESH_TOKEN);
$hasSession = defined('ACCURATE_SESSION_ID') && !empty(ACCURATE_SESSION_ID);
$hasDatabase = defined('ACCURATE_DATABASE_ID') && !empty(ACCURATE_DATABASE_ID);

echo ($hasToken ? '✅' : '❌') . " Access Token<br>";
echo ($hasRefresh ? '✅' : '❌') . " Refresh Token<br>";
echo ($hasSession ? '✅' : '❌') . " Session ID<br>";
echo ($hasDatabase ? '✅' : '❌') . " Database ID<br>";

// 2. Test API connectivity
echo "<h4>2. API Connectivity Test</h4>";
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => 'https://public-api.accurate.id/accurate/api/session.do',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . ACCURATE_ACCESS_TOKEN],
    CURLOPT_TIMEOUT => 5
]);
$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

if ($httpCode == 200) {
    echo "✅ API Connection Working<br>";
} else {
    echo "❌ API Connection Failed (HTTP $httpCode)<br>";
    
    if ($httpCode == 401) {
        echo "&nbsp;&nbsp;&nbsp;&nbsp;→ Token expired or invalid<br>";
    } elseif ($httpCode == 403) {
        echo "&nbsp;&nbsp;&nbsp;&nbsp;→ Access denied / insufficient permissions<br>";
    }
}

// 3. Test token status
echo "<h4>3. Token Status Check</h4>";
try {
    $tokenStatus = $api->checkTokenStatus(['item_view', 'branch_view', 'vendor_view']);
    $workingScopes = count(array_filter($tokenStatus['scopes']));
    $totalScopes = count($tokenStatus['scopes']);
    
    if ($tokenStatus['valid']) {
        echo "✅ Token Status: Valid<br>";
    } else {
        echo "❌ Token Status: Invalid<br>";
    }
    
    echo "Working Scopes: $workingScopes / $totalScopes<br>";
    
    foreach ($tokenStatus['scopes'] as $scope => $working) {
        echo "&nbsp;&nbsp;&nbsp;&nbsp;" . ($working ? '✅' : '❌') . " $scope<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Token Status Check Failed: " . $e->getMessage() . "<br>";
}

echo "</div>";

echo "<div style='border: 1px solid #007cba; padding: 15px; margin: 10px 0; border-radius: 5px; background: #f0f8ff;'>";
echo "<h3>💡 Recommended Solutions</h3>";

if (!$hasToken) {
    echo "<div style='background: #ffe6e6; padding: 10px; margin: 5px 0; border-radius: 3px;'>";
    echo "<strong>🚨 Critical: No Access Token</strong><br>";
    echo "Action: <a href='oauth/authorize.php'>Start OAuth Authorization</a>";
    echo "</div>";
    
} elseif ($httpCode == 401) {
    echo "<div style='background: #fff3cd; padding: 10px; margin: 5px 0; border-radius: 3px;'>";
    echo "<strong>⚠️ Token Expired</strong><br>";
    echo "Action: ";
    if ($hasRefresh) {
        echo "<a href='oauth/force-refresh.php'>Try Refresh Token</a> or ";
    }
    echo "<a href='oauth/authorize.php'>Re-authorize</a>";
    echo "</div>";
    
} elseif (isset($tokenStatus) && !$tokenStatus['valid']) {
    echo "<div style='background: #fff3cd; padding: 10px; margin: 5px 0; border-radius: 3px;'>";
    echo "<strong>⚠️ Some Scopes Not Working</strong><br>";
    echo "Action: <a href='oauth/authorize.php'>Re-authorize with Full Scopes</a>";
    echo "</div>";
    
} else {
    echo "<div style='background: #d4edda; padding: 10px; margin: 5px 0; border-radius: 3px;'>";
    echo "<strong>✅ Token Appears to be Working</strong><br>";
    echo "The issue might be temporary. Try: <a href='oauth/token-status.php'>Check Status Again</a>";
    echo "</div>";
}

echo "<h4>Available Actions:</h4>";
echo "<a href='oauth/token-status.php' style='background: #007cba; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; margin: 5px; display: inline-block;'>Check Status</a>";
echo "<a href='oauth/force-refresh.php' style='background: #28a745; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; margin: 5px; display: inline-block;'>Force Refresh</a>";
echo "<a href='oauth/authorize.php' style='background: #ffc107; color: black; padding: 8px 16px; text-decoration: none; border-radius: 4px; margin: 5px; display: inline-block;'>Re-authorize</a>";
echo "<a href='index.php' style='background: #6c757d; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; margin: 5px; display: inline-block;'>Dashboard</a>";

echo "</div>";
?>
