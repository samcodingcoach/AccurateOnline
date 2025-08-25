<?php
require_once '../bootstrap.php';
require_once '../classes/AccurateAPI.php';

echo "<h2>Force Token Refresh</h2>";

$api = new AccurateAPI();

echo "<h3>Current Token Info</h3>";
echo "ACCESS_TOKEN: " . substr(ACCURATE_ACCESS_TOKEN, 0, 20) . "...<br>";
echo "REFRESH_TOKEN: " . (defined('ACCURATE_REFRESH_TOKEN') ? substr(ACCURATE_REFRESH_TOKEN, 0, 20) . "..." : 'NOT SET') . "<br>";

if (defined('ACCURATE_REFRESH_TOKEN') && !empty(ACCURATE_REFRESH_TOKEN)) {
    echo "<hr>";
    echo "<h3>Attempting Token Refresh</h3>";
    
    try {
        // Manual refresh token menggunakan refresh token
        $refreshResult = $api->refreshToken(ACCURATE_REFRESH_TOKEN);
        
        if ($refreshResult['success']) {
            echo "✅ Token refresh successful!<br>";
            echo "New access token: " . substr($refreshResult['data']['access_token'], 0, 20) . "...<br>";
            
            // Update config dengan token baru
            require_once '../classes/AutoOAuthHandler.php';
            $autoHandler = new AutoOAuthHandler();
            $updateResult = $autoHandler->updateTokenAndConfig($refreshResult['data']);
            
            if ($updateResult['success']) {
                echo "✅ Config updated successfully!<br>";
                echo "<a href='token-status.php' class='btn'>Check Token Status</a><br>";
            } else {
                echo "❌ Config update failed: " . $updateResult['message'] . "<br>";
            }
            
        } else {
            echo "❌ Token refresh failed: " . ($refreshResult['error'] ?? 'Unknown error') . "<br>";
            echo "You may need to re-authorize completely.<br>";
            echo "<a href='authorize.php' class='btn'>Re-authorize</a><br>";
        }
        
    } catch (Exception $e) {
        echo "❌ Error during refresh: " . $e->getMessage() . "<br>";
        echo "<a href='authorize.php' class='btn'>Re-authorize</a><br>";
    }
    
} else {
    echo "<hr>";
    echo "<h3>No Refresh Token Available</h3>";
    echo "You need to re-authorize to get a new token.<br>";
    echo "<a href='authorize.php' class='btn'>Start Authorization</a><br>";
}

echo "<hr>";
echo "<h3>Quick Actions</h3>";
echo "<a href='token-status.php' class='btn'>Check Status</a> ";
echo "<a href='authorize.php' class='btn'>Re-authorize</a> ";
echo "<a href='../index.php' class='btn'>Dashboard</a>";

?>

<style>
.btn {
    display: inline-block;
    padding: 8px 16px;
    background: #007cba;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    margin: 5px;
}
.btn:hover {
    background: #005a87;
}
</style>
