<?php
// Cari price category yang valid untuk mengganti ID 100
require_once __DIR__ . '/../bootstrap.php';

echo "<h2>Find Valid Price Categories</h2>";

$api = new AccurateAPI();
$result = $api->getPriceCategoryList();

if ($result['success']) {
    $categories = $result['data']['d'] ?? [];
    
    echo "<h3>Active Price Categories:</h3>";
    $activeCategories = [];
    
    foreach ($categories as $category) {
        if (!$category['suspended']) {
            $activeCategories[] = $category;
            echo "<p>ID: <strong>{$category['id']}</strong> - Name: {$category['name']}</p>";
        }
    }
    
    if (count($activeCategories) >= 3) {
        echo "<hr><h3>Suggested IDs for 3 Price Levels:</h3>";
        echo "<p>Level 1: ID <strong>{$activeCategories[0]['id']}</strong> ({$activeCategories[0]['name']})</p>";
        echo "<p>Level 2: ID <strong>{$activeCategories[1]['id']}</strong> ({$activeCategories[1]['name']})</p>";
        echo "<p>Level 3: ID <strong>{$activeCategories[2]['id']}</strong> ({$activeCategories[2]['name']})</p>";
        
        // Test dengan ID yang valid
        echo "<hr><h3>Testing dengan ID yang Valid</h3>";
        
        $validItemNo = '100014';
        $sessionId = $api->getSessionId();
        
        $testIds = [
            $activeCategories[0]['id'],
            $activeCategories[1]['id'], 
            $activeCategories[2]['id']
        ];
        
        foreach ($testIds as $index => $testId) {
            $levelName = "Level " . ($index + 1);
            $price = (($index + 1) * 1000) + 4000; // 5000, 6000, 7000
            
            echo "<h4>Testing {$levelName} (ID: {$testId}) - Price: {$price}</h4>";
            
            $postData = http_build_query([
                'detailItem[0].itemNo' => $validItemNo,
                'detailItem[0].price' => $price,
                'salesAdjustmentType' => 'ITEM_PRICE_TYPE',
                'id' => $testId
            ]);
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => 'http://localhost/nuansa/sellingprice/saveprice.php',
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postData,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/x-www-form-urlencoded',
                    'X-Session-ID: ' . $sessionId
                ]
            ]);
            
            $response = curl_exec($ch);
            curl_close($ch);
            
            $result = json_decode($response, true);
            if ($result && $result['success']) {
                echo "<p style='color: green;'>✅ SUCCESS</p>";
            } else {
                echo "<p style='color: red;'>❌ FAILED: " . ($result['message'] ?? 'Unknown error') . "</p>";
            }
            
            sleep(1); // Delay antar request
        }
    }
} else {
    echo "<p>Error: " . ($result['error'] ?? 'Unknown error') . "</p>";
}
?>
