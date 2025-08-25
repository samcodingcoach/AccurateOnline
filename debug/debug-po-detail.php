<?php
require_once __DIR__ . '/../bootstrap.php';

$api = new AccurateAPI();

// First get list to get an ID
$listResult = $api->getPurchaseOrderList();
$poId = null;

if ($listResult['success'] && isset($listResult['data']['d']) && !empty($listResult['data']['d'])) {
    $poId = $listResult['data']['d'][0]['id'];
}

if ($poId) {
    echo "<h2>Purchase Order Detail API Debug (ID: $poId)</h2>";
    
    $detailResult = $api->getPurchaseOrderDetail($poId);
    
    echo "<h3>Detail Response:</h3>";
    echo "<pre>";
    echo htmlspecialchars(json_encode($detailResult, JSON_PRETTY_PRINT));
    echo "</pre>";
    
    if ($detailResult['success'] && isset($detailResult['data']['d'])) {
        $poDetail = $detailResult['data']['d'];
        echo "<h3>Vendor Data in Detail:</h3>";
        echo "<pre>";
        echo "vendor field: " . (isset($poDetail['vendor']) ? json_encode($poDetail['vendor'], JSON_PRETTY_PRINT) : 'NOT SET') . "\n";
        echo "vendorId field: " . (isset($poDetail['vendorId']) ? $poDetail['vendorId'] : 'NOT SET') . "\n";
        echo "</pre>";
    }
} else {
    echo "<h2>No Purchase Order found in list</h2>";
}
?>
