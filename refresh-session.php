<?php
require_once __DIR__ . '/bootstrap.php';

echo "<h1>Refresh Session dengan Token Baru</h1>";

// Direct API call untuk mendapatkan database list
$accessToken = ACCURATE_ACCESS_TOKEN;

echo "<h2>1. Getting Database List</h2>";
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'https://account.accurate.id/api/db-list.do',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer $accessToken",
        "Accept: application/json"
    ]
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p>HTTP Code: $httpCode</p>";
echo "<pre>Response: $response</pre>";

if ($httpCode === 200) {
    $data = json_decode($response, true);
    if (isset($data['d']) && !empty($data['d'])) {
        $database = $data['d'][0]; // Ambil database pertama
        $dbId = $database['id'];
        
        echo "<h2>2. Opening Database ID: $dbId</h2>";
        
        // Open database
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => "https://account.accurate.id/api/open-db.do?id=$dbId",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer $accessToken",
                "Accept: application/json"
            ]
        ]);
        
        $response2 = curl_exec($ch);
        $httpCode2 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        echo "<p>HTTP Code: $httpCode2</p>";
        echo "<pre>Response: $response2</pre>";
        
        if ($httpCode2 === 200) {
            $openData = json_decode($response2, true);
            if (isset($openData['session']) && isset($openData['host'])) {
                $newSession = $openData['session'];
                $newHost = $openData['host'];
                
                echo "<h2>3. Update Configuration</h2>";
                echo "<p><strong>New Host:</strong> $newHost</p>";
                echo "<p><strong>New Session:</strong> $newSession</p>";
                echo "<p><strong>Database ID:</strong> $dbId</p>";
                
                // Update config file
                $configFile = __DIR__ . '/config/config.php';
                $configContent = file_get_contents($configFile);
                
                $configContent = preg_replace(
                    "/define\('ACCURATE_API_HOST', '.*?'\);/",
                    "define('ACCURATE_API_HOST', '$newHost');",
                    $configContent
                );
                
                $configContent = preg_replace(
                    "/define\('ACCURATE_SESSION_ID', '.*?'\);/",
                    "define('ACCURATE_SESSION_ID', '$newSession');",
                    $configContent
                );
                
                $configContent = preg_replace(
                    "/define\('ACCURATE_DATABASE_ID', '.*?'\);/",
                    "define('ACCURATE_DATABASE_ID', '$dbId');",
                    $configContent
                );
                
                file_put_contents($configFile, $configContent);
                
                echo "<p><strong>✅ Configuration updated successfully!</strong></p>";
                echo "<p>Please refresh your application to use the new session.</p>";
            }
        }
    }
}
?>
