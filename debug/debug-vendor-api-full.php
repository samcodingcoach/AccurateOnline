<?php
require_once __DIR__ . '/bootstrap.php';

$api = new AccurateAPI();

// Ambil vendor ID yang valid dari list
$listResult = $api->getVendorList(1, 3);

echo "<h2>Debug Vendor API Response</h2>";

if ($listResult['success']) {
    echo "<h3>Vendor List Response:</h3>";
    echo "<pre>" . json_encode($listResult['data'], JSON_PRETTY_PRINT) . "</pre>";
    
    if (isset($listResult['data']['d']) && !empty($listResult['data']['d'])) {
        $firstVendor = $listResult['data']['d'][0];
        $vendorId = $firstVendor['id'];
        
        echo "<h3>Testing Detail for Vendor ID: $vendorId</h3>";
        
        // Test detail API
        $detailResult = $api->getVendorDetail($vendorId);
        
        echo "<h4>Detail API Result:</h4>";
        echo "<p>Success: " . ($detailResult['success'] ? 'YES' : 'NO') . "</p>";
        echo "<p>HTTP Code: " . ($detailResult['http_code'] ?? 'N/A') . "</p>";
        
        if ($detailResult['success']) {
            echo "<h4>Detail Response:</h4>";
            echo "<pre>" . json_encode($detailResult['data'], JSON_PRETTY_PRINT) . "</pre>";
        } else {
            echo "<h4>Detail Error:</h4>";
            echo "<p>" . ($detailResult['error'] ?? 'Unknown error') . "</p>";
            echo "<h4>Raw Response:</h4>";
            echo "<pre>" . htmlspecialchars($detailResult['raw_response'] ?? 'No raw response') . "</pre>";
        }
    }
} else {
    echo "<p>List API Error: " . ($listResult['error'] ?? 'Unknown error') . "</p>";
}
?>
