<?php
require_once __DIR__ . '/bootstrap.php';

$api = new AccurateAPI();

echo "<h2>Debug Vendor API Calls</h2>";

// Test vendor list
echo "<h3>1. Testing Vendor List API</h3>";
$listResult = $api->getVendorList(1, 3); // Get first 3 vendors
echo "<p>Success: " . ($listResult['success'] ? 'YES' : 'NO') . "</p>";
echo "<p>HTTP Code: " . ($listResult['http_code'] ?? 'N/A') . "</p>";

if ($listResult['success']) {
    echo "<h4>Vendor List Response:</h4>";
    echo "<pre>" . json_encode($listResult['data'], JSON_PRETTY_PRINT) . "</pre>";
    
    if (isset($listResult['data']['d']) && is_array($listResult['data']['d'])) {
        $firstVendor = $listResult['data']['d'][0];
        $vendorId = $firstVendor['id'];
        
        echo "<h3>2. Testing Vendor Detail API for ID: $vendorId</h3>";
        $detailResult = $api->getVendorDetail($vendorId);
        echo "<p>Success: " . ($detailResult['success'] ? 'YES' : 'NO') . "</p>";
        echo "<p>HTTP Code: " . ($detailResult['http_code'] ?? 'N/A') . "</p>";
        
        if ($detailResult['success']) {
            echo "<h4>Vendor Detail Response:</h4>";
            echo "<pre>" . json_encode($detailResult['data'], JSON_PRETTY_PRINT) . "</pre>";
            
            // Check specific fields
            echo "<h4>Field Analysis:</h4>";
            echo "<ul>";
            echo "<li>Has balanceList: " . (isset($detailResult['data']['balanceList']) ? 'YES' : 'NO') . "</li>";
            echo "<li>Has category: " . (isset($detailResult['data']['category']) ? 'YES' : 'NO') . "</li>";
            
            if (isset($detailResult['data']['balanceList'])) {
                echo "<li>Balance List Content: " . json_encode($detailResult['data']['balanceList']) . "</li>";
            }
            
            if (isset($detailResult['data']['category'])) {
                echo "<li>Category Content: " . json_encode($detailResult['data']['category']) . "</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>Error: " . ($detailResult['error'] ?? 'Unknown error') . "</p>";
        }
    }
} else {
    echo "<p>Error: " . ($listResult['error'] ?? 'Unknown error') . "</p>";
}
?>
