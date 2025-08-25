<?php
require_once __DIR__ . '/bootstrap.php';

$api = new AccurateAPI();

// Test dengan vendor ID 500
$vendorId = 500;

echo "<h2>Debug Vendor Detail Fields</h2>";
echo "<p>Testing vendor ID: $vendorId</p>";

$result = $api->getVendorDetail($vendorId);

if ($result['success']) {
    echo "<h3>Success! Available fields:</h3>";
    
    $vendor = $result['data'];
    
    echo "<h4>All Fields:</h4>";
    echo "<ul>";
    foreach ($vendor as $key => $value) {
        echo "<li><strong>$key:</strong> " . (is_array($value) ? json_encode($value) : $value) . "</li>";
    }
    echo "</ul>";
    
    // Cek field yang mungkin untuk kode vendor
    echo "<h4>Possible Code Fields:</h4>";
    $possibleCodeFields = ['vendorNo', 'no', 'code', 'vendorCode', 'number'];
    foreach ($possibleCodeFields as $field) {
        if (isset($vendor[$field])) {
            echo "<p><strong>$field:</strong> " . $vendor[$field] . "</p>";
        } else {
            echo "<p><strong>$field:</strong> NOT FOUND</p>";
        }
    }
    
    echo "<h4>Raw Response:</h4>";
    echo "<pre>" . json_encode($vendor, JSON_PRETTY_PRINT) . "</pre>";
    
} else {
    echo "<p>Error: " . ($result['error'] ?? 'Unknown error') . "</p>";
    echo "<p>HTTP Code: " . ($result['http_code'] ?? 'N/A') . "</p>";
}
?>
