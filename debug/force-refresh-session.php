<?php
/**
 * Force Refresh Session dengan Token Baru
 * Script ini akan memaksa refresh session dan update config.php
 */

$accessToken = '42300d7b-909c-49d8-9d10-064a66215f98';
$configPath = __DIR__ . '/config/config.php';

echo "<h1>Force Refresh Session</h1>";

// Step 1: Get database list
echo "<h2>Step 1: Get Database List</h2>";
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

if ($httpCode === 200) {
    $data = json_decode($response, true);
    if (isset($data['d']) && !empty($data['d'])) {
        $dbId = $data['d'][0]['id'];
        echo "<p>✅ Database found: ID = $dbId</p>";
        
        // Step 2: Open database
        echo "<h2>Step 2: Open Database</h2>";
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
        
        if ($httpCode2 === 200) {
            $openData = json_decode($response2, true);
            if (isset($openData['session']) && isset($openData['host'])) {
                $newSession = $openData['session'];
                $newHost = $openData['host'];
                
                echo "<p>✅ Session opened successfully</p>";
                echo "<p><strong>New Host:</strong> $newHost</p>";
                echo "<p><strong>New Session:</strong> $newSession</p>";
                echo "<p><strong>Database ID:</strong> $dbId</p>";
                
                // Step 3: Update config.php
                echo "<h2>Step 3: Update Config.php</h2>";
                
                $configContent = file_get_contents($configPath);
                
                // Update values
                $configContent = preg_replace(
                    "/define\('ACCURATE_API_HOST', '[^']*'\);/",
                    "define('ACCURATE_API_HOST', '$newHost');",
                    $configContent
                );
                
                $configContent = preg_replace(
                    "/define\('ACCURATE_SESSION_ID', '[^']*'\);/",
                    "define('ACCURATE_SESSION_ID', '$newSession');",
                    $configContent
                );
                
                $configContent = preg_replace(
                    "/define\('ACCURATE_DATABASE_ID', '[^']*'\);/",
                    "define('ACCURATE_DATABASE_ID', '$dbId');",
                    $configContent
                );
                
                if (file_put_contents($configPath, $configContent)) {
                    echo "<p>✅ Config.php updated successfully!</p>";
                    
                    // Step 4: Test vendor API
                    echo "<h2>Step 4: Test Vendor API</h2>";
                    $ch = curl_init();
                    curl_setopt_array($ch, [
                        CURLOPT_URL => "$newHost/accurate/api/vendor/list.do?sp.pageSize=3&sp.page=1&fields=id,name,no",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_HTTPHEADER => [
                            "Authorization: Bearer $accessToken",
                            "X-Session-ID: $newSession",
                            "Accept: application/json"
                        ]
                    ]);
                    
                    $response3 = curl_exec($ch);
                    $httpCode3 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
                    
                    echo "<p><strong>Vendor API HTTP Code:</strong> $httpCode3</p>";
                    echo "<p><strong>Vendor API Response:</strong></p>";
                    echo "<pre>" . htmlspecialchars($response3) . "</pre>";
                    
                    if ($httpCode3 === 200) {
                        echo "<p>✅ <strong>SUCCESS! Vendor API is now working with new session!</strong></p>";
                        echo "<p>✅ <strong>vendor_view scope is now active!</strong></p>";
                    } else {
                        echo "<p>❌ <strong>Vendor API still failing</strong></p>";
                        
                        // Analisa error
                        $responseData = json_decode($response3, true);
                        if (isset($responseData['error'])) {
                            echo "<p><strong>Error:</strong> " . htmlspecialchars($responseData['error']) . "</p>";
                        }
                        if (isset($responseData['message'])) {
                            echo "<p><strong>Message:</strong> " . htmlspecialchars($responseData['message']) . "</p>";
                        }
                    }
                } else {
                    echo "<p>❌ Failed to update config.php</p>";
                }
            } else {
                echo "<p>❌ Invalid response from open-db</p>";
                echo "<pre>" . htmlspecialchars($response2) . "</pre>";
            }
        } else {
            echo "<p>❌ Failed to open database (HTTP $httpCode2)</p>";
            echo "<pre>" . htmlspecialchars($response2) . "</pre>";
        }
    } else {
        echo "<p>❌ No databases found</p>";
    }
} else {
    echo "<p>❌ Failed to get database list (HTTP $httpCode)</p>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
}
?>
