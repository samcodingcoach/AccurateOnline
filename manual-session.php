<?php
// Manual update session berdasarkan token baru

$accessToken = 'c80a1eac-1907-4b03-9ea9-b2bf1d47be76';

echo "<h1>Manual Session Update</h1>";

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
$error = curl_error($ch);
curl_close($ch);

echo "<p><strong>HTTP Code:</strong> $httpCode</p>";
if ($error) {
    echo "<p><strong>cURL Error:</strong> $error</p>";
}
echo "<p><strong>Response:</strong></p>";
echo "<pre>$response</pre>";

if ($httpCode === 200) {
    $data = json_decode($response, true);
    if (isset($data['d']) && !empty($data['d'])) {
        echo "<h3>Available Databases:</h3>";
        foreach ($data['d'] as $db) {
            echo "<p>ID: {$db['id']} - Alias: {$db['alias']}</p>";
        }
        
        // Step 2: Open first database
        $dbId = $data['d'][0]['id'];
        echo "<h2>Step 2: Open Database ID: $dbId</h2>";
        
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
        $error2 = curl_error($ch);
        curl_close($ch);
        
        echo "<p><strong>HTTP Code:</strong> $httpCode2</p>";
        if ($error2) {
            echo "<p><strong>cURL Error:</strong> $error2</p>";
        }
        echo "<p><strong>Response:</strong></p>";
        echo "<pre>$response2</pre>";
        
        if ($httpCode2 === 200) {
            $openData = json_decode($response2, true);
            if (isset($openData['session']) && isset($openData['host'])) {
                $newSession = $openData['session'];
                $newHost = $openData['host'];
                
                echo "<h2>Step 3: New Configuration Values</h2>";
                echo "<p><strong>New Host:</strong> $newHost</p>";
                echo "<p><strong>New Session:</strong> $newSession</p>";
                echo "<p><strong>Database ID:</strong> $dbId</p>";
                
                echo "<h3>Copy these values to config.php:</h3>";
                echo "<pre>";
                echo "define('ACCURATE_API_HOST', '$newHost');\n";
                echo "define('ACCURATE_SESSION_ID', '$newSession');\n";
                echo "define('ACCURATE_DATABASE_ID', '$dbId');\n";
                echo "</pre>";
                
                // Step 3: Test dengan session baru
                echo "<h2>Step 4: Test dengan Session Baru</h2>";
                $ch = curl_init();
                curl_setopt_array($ch, [
                    CURLOPT_URL => "$newHost/accurate/api/item/list.do?sp.pageSize=5&sp.page=1&fields=id,name,no",
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
                $error3 = curl_error($ch);
                curl_close($ch);
                
                echo "<p><strong>Item List HTTP Code:</strong> $httpCode3</p>";
                if ($error3) {
                    echo "<p><strong>cURL Error:</strong> $error3</p>";
                }
                echo "<p><strong>Item List Response:</strong></p>";
                echo "<pre>$response3</pre>";
                
                if ($httpCode3 === 200) {
                    echo "<p><strong>✅ Session is working! You can now update config.php</strong></p>";
                } else {
                    echo "<p><strong>❌ Session test failed</strong></p>";
                }
            }
        }
    } else {
        echo "<p><strong>❌ No databases found in response</strong></p>";
    }
} else {
    echo "<p><strong>❌ Failed to get database list</strong></p>";
}
?>
