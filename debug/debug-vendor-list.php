<?php
require_once __DIR__ . '/bootstrap.php';

$api = new AccurateAPI();
$result = $api->getVendorList(1, 5); // Ambil 5 vendor pertama

echo "<h2>Debug Data Vendor List</h2>";
echo "<p>Success: " . ($result['success'] ? 'YA' : 'TIDAK') . "</p>";

if ($result['success'] && isset($result['data']['d'])) {
    echo "<h3>Struktur Data Vendor:</h3>";
    
    foreach ($result['data']['d'] as $index => $vendor) {
        echo "<h4>Vendor " . ($index + 1) . " (ID: " . $vendor['id'] . "):</h4>";
        echo "<ul>";
        echo "<li>Nama: " . ($vendor['name'] ?? 'N/A') . "</li>";
        echo "<li>Punya balanceList: " . (isset($vendor['balanceList']) ? 'YA' : 'TIDAK') . "</li>";
        echo "<li>Punya category: " . (isset($vendor['category']) ? 'YA' : 'TIDAK') . "</li>";
        echo "</ul>";
        
        echo "<h5>Semua field yang tersedia:</h5>";
        echo "<pre>" . json_encode($vendor, JSON_PRETTY_PRINT) . "</pre>";
        
        if ($index >= 1) break; // Hanya tampilkan 2 vendor pertama
    }
}
?>
