<?php
require_once 'bootstrap.php';

echo "<h2>Debug Scope Configuration</h2>";

echo "<h3>Current Config Check</h3>";
echo "File exists: " . (file_exists(__DIR__ . '/config/config.php') ? 'YES' : 'NO') . "<br>";
echo "ACCURATE_TOKEN_SCOPE defined: " . (defined('ACCURATE_TOKEN_SCOPE') ? 'YES' : 'NO') . "<br>";
echo "ACCURATE_TOKEN_SCOPE value: " . (defined('ACCURATE_TOKEN_SCOPE') ? '"' . ACCURATE_TOKEN_SCOPE . '"' : 'UNDEFINED') . "<br>";

if (defined('ACCURATE_TOKEN_SCOPE')) {
    $scopes = explode(' ', ACCURATE_TOKEN_SCOPE);
    echo "Parsed scopes (" . count($scopes) . "): <br>";
    foreach ($scopes as $i => $scope) {
        echo "  [$i] = '$scope'<br>";
    }
}

echo "<hr>";

echo "<h3>Test Add Scope to Config</h3>";

$testScope = 'test_scope_' . time();

if (isset($_GET['add_test'])) {
    // Simulate adding a scope
    $configPath = __DIR__ . '/config/config.php';
    $configContent = file_get_contents($configPath);
    
    $currentScopeString = defined('ACCURATE_TOKEN_SCOPE') ? ACCURATE_TOKEN_SCOPE : '';
    $currentScopes = !empty($currentScopeString) ? explode(' ', $currentScopeString) : [];
    $currentScopes[] = $testScope;
    $newScopeString = implode(' ', $currentScopes);
    
    if (strpos($configContent, 'ACCURATE_TOKEN_SCOPE') !== false) {
        $configContent = preg_replace(
            "/define\('ACCURATE_TOKEN_SCOPE', '[^']*'\);/",
            "define('ACCURATE_TOKEN_SCOPE', '{$newScopeString}');",
            $configContent
        );
    } else {
        $newLine = "define('ACCURATE_TOKEN_SCOPE', '{$newScopeString}');\n";
        $configContent = str_replace(
            "define('ACCURATE_REFRESH_TOKEN',", 
            $newLine . "define('ACCURATE_REFRESH_TOKEN',", 
            $configContent
        );
    }
    
    file_put_contents($configPath, $configContent);
    
    echo "<div style='background: #d4edda; padding: 10px; border: 1px solid #c3e6cb; margin: 10px 0;'>";
    echo "✅ Test scope '$testScope' added to config! <a href='debug-scope-config.php'>Refresh to see changes</a>";
    echo "</div>";
    
} else {
    echo "<a href='?add_test=1' style='background: #007cba; color: white; padding: 10px; text-decoration: none;'>Add Test Scope</a>";
}

echo "<hr>";

echo "<h3>Authorization URL Preview</h3>";

$currentScopeString = defined('ACCURATE_TOKEN_SCOPE') ? ACCURATE_TOKEN_SCOPE : '';
$currentScopes = !empty($currentScopeString) ? explode(' ', trim($currentScopeString)) : ['item_view', 'branch_view', 'vendor_view', 'warehouse_view'];

$authUrl = 'https://account.accurate.id/oauth/authorize?' . http_build_query([
    'client_id' => OAUTH_CLIENT_ID,
    'redirect_uri' => OAUTH_REDIRECT_URI,
    'response_type' => 'code',
    'scope' => implode(' ', $currentScopes)
]);

echo "<p><strong>Generated URL:</strong></p>";
echo "<textarea rows='4' cols='100' style='width: 100%; font-size: 12px;'>" . htmlspecialchars($authUrl) . "</textarea>";

echo "<p><strong>Scope part:</strong> " . htmlspecialchars(implode(' ', $currentScopes)) . "</p>";
echo "<p><strong>Scope count:</strong> " . count($currentScopes) . "</p>";

echo "<hr>";
echo "<p><a href='oauth/authorize.php'>Go to OAuth Authorize Page</a></p>";
?>
