<?php
require_once __DIR__ . '/../bootstrap.php';

$api = new AccurateAPI();
$result = $api->getPurchaseOrderListWithVendors();

echo "<h2>Purchase Order List dengan Vendor Names - Debug</h2>";

if ($result['success'] && isset($result['data']['d'])) {
    $purchaseOrders = $result['data']['d'];
    
    echo "<h3>Data Purchase Orders:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr>";
    echo "<th>ID</th>";
    echo "<th>Number</th>";
    echo "<th>Vendor ID</th>";
    echo "<th>Vendor Name</th>";
    echo "<th>Status</th>";
    echo "<th>Total Amount</th>";
    echo "<th>% Shipped</th>";
    echo "</tr>";
    
    foreach ($purchaseOrders as $po) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($po['id'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($po['number'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($po['vendorId'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($po['vendor']['name'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($po['statusName'] ?? 'N/A') . "</td>";
        echo "<td>" . number_format($po['totalAmount'] ?? 0, 0, ',', '.') . "</td>";
        echo "<td>" . htmlspecialchars($po['percentShipped'] ?? 0) . "%</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<h3>Vendor Cache Debug:</h3>";
    echo "<p>Jumlah Purchase Orders: " . count($purchaseOrders) . "</p>";
    echo "<p>Vendor IDs yang ditemukan: ";
    $vendorIds = array_unique(array_column($purchaseOrders, 'vendorId'));
    echo implode(', ', $vendorIds);
    echo "</p>";
    
} else {
    echo "<h3>Error:</h3>";
    echo "<pre>";
    echo htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT));
    echo "</pre>";
}
?>
