<?php
require_once 'bootstrap.php';
require_once 'classes/AccurateAPI.php';

echo "<h2>Item Category View Debug</h2>";

$api = new AccurateAPI();

echo "<h3>1. Manual API Call Test</h3>";

// Test manual API call
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => 'https://zeus.accurate.id/accurate/api/item-category/list.do?sp.page=1&sp.pageSize=1',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . ACCURATE_ACCESS_TOKEN,
        'X-Session-ID: ' . ACCURATE_SESSION_ID,
        'X-Database-ID: ' . ACCURATE_DATABASE_ID
    ],
    CURLOPT_TIMEOUT => 10
]);

$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
$curlError = curl_error($curl);
curl_close($curl);

echo "HTTP Code: " . $httpCode . "<br>";
if ($curlError) {
    echo "cURL Error: " . $curlError . "<br>";
}

if ($httpCode == 200) {
    echo "✅ Item Category API is accessible<br>";
    $data = json_decode($response, true);
    if ($data) {
        echo "Response sample: <br><pre>" . htmlspecialchars(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) . "</pre>";
    }
} else {
    echo "❌ Item Category API failed<br>";
    echo "Response: " . htmlspecialchars($response) . "<br>";
}

echo "<hr>";

echo "<h3>2. Test Method Call</h3>";
try {
    $result = $api->testItemCategoryView();
    echo "testItemCategoryView() result: " . ($result ? '✅ PASS' : '❌ FAIL') . "<br>";
} catch (Exception $e) {
    echo "❌ Error in testItemCategoryView(): " . $e->getMessage() . "<br>";
}

echo "<hr>";

echo "<h3>3. Test Full getItemCategoryList</h3>";
try {
    $categoryList = $api->getItemCategoryList(1, 5);
    echo "getItemCategoryList() success: " . ($categoryList['success'] ? '✅ YES' : '❌ NO') . "<br>";
    if ($categoryList['success']) {
        echo "Categories found: " . count($categoryList['data'] ?? []) . "<br>";
    } else {
        echo "Error: " . ($categoryList['error'] ?? 'Unknown') . "<br>";
    }
} catch (Exception $e) {
    echo "❌ Error in getItemCategoryList(): " . $e->getMessage() . "<br>";
}

echo "<hr>";

echo "<h3>4. Test All 5 Scopes</h3>";

$scopes = [
    'item_view' => 'testItemView',
    'branch_view' => 'testBranchView', 
    'item_category_view' => 'testItemCategoryView',
    'vendor_view' => 'testVendorView',
    'warehouse_view' => 'testWarehouseView'
];

$working = 0;
foreach ($scopes as $scope => $method) {
    try {
        $result = $api->$method();
        echo ($result ? '✅' : '❌') . " $scope";
        if ($result) $working++;
    } catch (Exception $e) {
        echo "❌ $scope (Error: " . $e->getMessage() . ")";
    }
    echo "<br>";
}

echo "<br><strong>Total working scopes: $working / " . count($scopes) . "</strong><br>";

echo "<hr>";

echo "<h3>5. Check Current Config</h3>";
echo "ACCURATE_TOKEN_SCOPE: " . (defined('ACCURATE_TOKEN_SCOPE') ? ACCURATE_TOKEN_SCOPE : 'NOT SET') . "<br>";

echo "<h3>6. Check getApprovedScopes()</h3>";
$approvedScopes = $api->getApprovedScopes();
echo "Success: " . ($approvedScopes['success'] ? 'YES' : 'NO') . "<br>";
echo "Data type: " . gettype($approvedScopes['data']) . "<br>";
if (is_array($approvedScopes['data'])) {
    echo "Count: " . count($approvedScopes['data']) . "<br>";
    echo "Scopes: ";
    foreach ($approvedScopes['data'] as $scope) {
        echo "$scope ";
    }
    echo "<br>";
}
?>
