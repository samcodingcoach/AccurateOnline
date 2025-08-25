<?php
require_once __DIR__ . '/bootstrap.php';

$api = new AccurateAPI();

// Test dengan vendor ID 500 (dari screenshot Anda)
$vendorId = 500;

echo "<h2>Debug Vendor ID: $vendorId</h2>";

// Test detail API
$detailResult = $api->getVendorDetail($vendorId);
echo "<h3>Detail API Result:</h3>";
echo "<p>Success: " . ($detailResult['success'] ? 'YA' : 'TIDAK') . "</p>";
echo "<p>HTTP Code: " . ($detailResult['http_code'] ?? 'N/A') . "</p>";

if ($detailResult['success']) {
    echo "<h4>Full Response:</h4>";
    echo "<pre>" . json_encode($detailResult['data'], JSON_PRETTY_PRINT) . "</pre>";
    
    // Cek field balance
    echo "<h4>Balance Field Check:</h4>";
    echo "<ul>";
    
    $balanceFields = ['balanceList', 'balance', 'debt', 'receivable', 'currentBalance'];
    foreach ($balanceFields as $field) {
        if (isset($detailResult['data'][$field])) {
            echo "<li>$field: " . json_encode($detailResult['data'][$field]) . "</li>";
        } else {
            echo "<li>$field: TIDAK ADA</li>";
        }
    }
    echo "</ul>";
    
    // Cek semua field yang ada
    echo "<h4>Semua Field yang Tersedia:</h4>";
    $allFields = array_keys($detailResult['data']);
    echo "<p>" . implode(', ', $allFields) . "</p>";
    
} else {
    echo "<p>Error: " . ($detailResult['error'] ?? 'Unknown') . "</p>";
    echo "<h4>Raw Response:</h4>";
    echo "<pre>" . htmlspecialchars($detailResult['raw_response'] ?? 'No raw response') . "</pre>";
}

// Test dengan vendor yang berbeda
echo "<h3>Test dengan Vendor Lain:</h3>";
$listResult = $api->getVendorList(1, 3);
if ($listResult['success']) {
    foreach ($listResult['data']['d'] as $vendor) {
        $id = $vendor['id'];
        $name = $vendor['name'];
        
        echo "<h4>Vendor: $name (ID: $id)</h4>";
        
        $detail = $api->getVendorDetail($id);
        if ($detail['success']) {
            if (isset($detail['data']['balanceList'])) {
                echo "<p>BalanceList: " . json_encode($detail['data']['balanceList']) . "</p>";
            } else {
                echo "<p>BalanceList: TIDAK ADA</p>";
            }
        } else {
            echo "<p>Detail API gagal: " . ($detail['error'] ?? 'Unknown') . "</p>";
        }
    }
}
?>
