<?php
require_once __DIR__ . '/../bootstrap.php';

$api = new AccurateAPI();
$result = $api->getPurchaseOrderList();

echo "<h2>Purchase Order List API Debug</h2>";
echo "<h3>Raw Response:</h3>";
echo "<pre>";
echo htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT));
echo "</pre>";

if ($result['success'] && isset($result['data']['d'])) {
    echo "<h3>Vendor Data Structure:</h3>";
    foreach ($result['data']['d'] as $index => $po) {
        echo "<h4>Purchase Order #" . ($index + 1) . " (ID: {$po['id']})</h4>";
        echo "<pre>";
        echo "vendor field: " . (isset($po['vendor']) ? json_encode($po['vendor'], JSON_PRETTY_PRINT) : 'NOT SET') . "\n";
        echo "vendorId field: " . (isset($po['vendorId']) ? $po['vendorId'] : 'NOT SET') . "\n";
        echo "All keys: " . implode(', ', array_keys($po)) . "\n";
        echo "</pre>";
        
        // Only show first 2 to avoid too much output
        if ($index >= 1) break;
    }
}
?>
