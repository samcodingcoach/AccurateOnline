<?php
require_once __DIR__ . '/bootstrap.php';

$api = new AccurateAPI();

// Test with vendor ID 500 (from your screenshot)
$vendorId = 500;

echo "<h2>Debug Vendor ID: $vendorId</h2>";

$detailResult = $api->getVendorDetail($vendorId);
echo "<p>Success: " . ($detailResult['success'] ? 'YES' : 'NO') . "</p>";
echo "<p>HTTP Code: " . ($detailResult['http_code'] ?? 'N/A') . "</p>";

if ($detailResult['success']) {
    echo "<h3>Full Response:</h3>";
    echo "<pre>" . json_encode($detailResult['data'], JSON_PRETTY_PRINT) . "</pre>";
    
    // Test helper functions
    echo "<h3>Helper Function Tests:</h3>";
    
    // Test formatBalance
    function formatBalance($balanceList) {
        if (!is_array($balanceList) || empty($balanceList)) {
            return 'N/A';
        }
        
        $balance = $balanceList[0]['balance'] ?? 0;
        $balanceCode = $balanceList[0]['balanceCode'] ?? 'Rp';
        
        $formatted = number_format($balance, 0, ',', '.');
        $currency = trim(str_replace('IDR', '', $balanceCode));
        
        return $currency . ' ' . $formatted;
    }
    
    // Test getCategoryName
    function getCategoryName($category) {
        if (!is_array($category) || !isset($category['name'])) {
            return 'N/A';
        }
        
        return $category['name'];
    }
    
    $balanceList = $detailResult['data']['balanceList'] ?? null;
    $category = $detailResult['data']['category'] ?? null;
    
    echo "<p>Balance List: " . json_encode($balanceList) . "</p>";
    echo "<p>Category: " . json_encode($category) . "</p>";
    echo "<p>Formatted Balance: " . formatBalance($balanceList) . "</p>";
    echo "<p>Category Name: " . getCategoryName($category) . "</p>";
    
} else {
    echo "<p>Error: " . ($detailResult['error'] ?? 'Unknown error') . "</p>";
    echo "<p>Full Error Response: " . json_encode($detailResult, JSON_PRETTY_PRINT) . "</p>";
}
?>
